/**
 * Shared client-side mirror of the server's batch rules.
 *
 * The backend in App\Support\Inventory\BatchRules is the single source of truth.
 * This file exists so the form can:
 *   - show the right fields for the selected category,
 *   - set the `min` on the date input so the browser itself blocks a bad date,
 *   - explain the rule before submit rather than after a 422.
 *
 * Every rule is ALSO enforced server-side. This is convenience, not security.
 */

import { computed, ref } from 'vue'
import api from '@/utils/axios'

/** Categories whose expiration date may be left blank. */
export const NON_PERISHABLE_CATEGORIES = [
    'tool',
    'accessor',
    'packaging',
    'safety equipment',
    'surface preparation',
]

/** Minimum shelf life, in years, required on every incoming batch. */
export const MINIMUM_SHELF_LIFE_YEARS = 1

/** Live copy of the server rulebook, fetched once. */
const rules = ref(null)
const rulesLoaded = ref(false)

/**
 * Ask the server for the authoritative rules.
 * Safe to call repeatedly; only the first call hits the network.
 */
export async function loadBatchRules(force = false) {
    if (rulesLoaded.value && !force) return rules.value

    try {
        const { data } = await api.get('/supplier/raw-materials/rules')
        rules.value = {
            minimumShelfLifeYears: data.minimum_shelf_life_years ?? MINIMUM_SHELF_LIFE_YEARS,
            minimumExpirationDate: data.minimum_expiration_date,
            message: data.message,
        }
    } catch {
        // The form must still work if this one call fails; fall back to the
        // same arithmetic the server does.
        rules.value = {
            minimumShelfLifeYears: MINIMUM_SHELF_LIFE_YEARS,
            minimumExpirationDate: localMinimumDate(),
            message:
                'The expiration date must be at least 1 year from today ' +
                `(${localMinimumDate()} or later).`,
        }
    }

    rulesLoaded.value = true
    return rules.value
}

/**
 * Today + 1 year, as YYYY-MM-DD. Mirrors BatchRules::minimumExpirationDate().
 *
 * Written the long way round rather than with setFullYear(+1) because the two
 * disagree on 29 February: JavaScript's setFullYear rolls a leap day forward to
 * 1 March, while the server's Carbon addYear() clamps it back to 28 February.
 * Only the offline fallback path uses this, but a client that advertises a
 * looser minimum than the server enforces just produces a confusing 422.
 */
export function localMinimumDate(from = new Date()) {
    const d = new Date(from)
    const targetYear = d.getFullYear() + MINIMUM_SHELF_LIFE_YEARS

    // Last day of the target month, so 29 Feb clamps to the 28th.
    const lastDayOfTargetMonth = new Date(targetYear, d.getMonth() + 1, 0).getDate()

    return toInputDate(
        new Date(
            targetYear,
            d.getMonth(),
            Math.min(d.getDate(), lastDayOfTargetMonth)
        )
    )
}

/** Format a Date as YYYY-MM-DD without timezone drift. */
export function toInputDate(date) {
    if (!date) return ''
    const d = date instanceof Date ? date : new Date(date)
    if (Number.isNaN(d.getTime())) return ''

    const m = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${d.getFullYear()}-${m}-${day}`
}

/** Is the expiration date optional for this category? */
export function isExpirationOptional(category) {
    if (!category || !String(category).trim()) return false

    const needle = String(category).trim().toLowerCase()
    return NON_PERISHABLE_CATEGORIES.some((token) => needle.includes(token))
}

/**
 * Validate an incoming batch's expiration date.
 * @returns {string|null} An error message, or null when the date is acceptable.
 */
export function validateExpiration(category, expirationDate) {
    const blank =
        expirationDate === null ||
        expirationDate === undefined ||
        String(expirationDate).trim() === ''

    if (blank) {
        return isExpirationOptional(category)
            ? null
            : 'An expiration date is required. Tools, Accessories and Packaging are the only categories where it can be left blank.'
    }

    if (!/^\d{4}-\d{2}-\d{2}$/.test(String(expirationDate).trim())) {
        return 'Enter a valid date.'
    }

    const minimum = rules.value?.minimumExpirationDate || localMinimumDate()
    if (String(expirationDate).trim() < minimum) {
        return `The expiration date must be at least 1 year from today (${minimum} or later).`
    }

    return null
}

/**
 * Reusable reactive helpers for a batch form.
 */
export function useBatchExpiration(categoryRef) {
    const expirationDate = ref('')
    const expirationError = ref('')
    const touched = ref(false)

    const optional = computed(() => isExpirationOptional(categoryRef?.value))

    const minimumDate = computed(
        () => rules.value?.minimumExpirationDate || localMinimumDate()
    )

    const ruleMessage = computed(
        () =>
            rules.value?.message ||
            `The expiration date must be at least 1 year from today (${minimumDate.value} or later).`
    )

    /** Re-check on every keystroke once the field has been touched. */
    function validate() {
        if (!touched.value) return null

        const error = validateExpiration(
            categoryRef?.value,
            expirationDate.value
        )
        expirationError.value = error || ''
        return error
    }

    /** Called when the category changes: clear a date that is no longer allowed. */
    function onCategoryChange() {
        const error = validateExpiration(
            categoryRef?.value,
            expirationDate.value
        )

        if (error) {
            expirationDate.value = ''
            expirationError.value = ''
        }
    }

    function reset(value = '', wasTouched = false) {
        expirationDate.value = value
        expirationError.value = ''
        touched.value = wasTouched
    }

    return {
        expirationDate,
        expirationError,
        touched,
        optional,
        minimumDate,
        ruleMessage,
        validate,
        onCategoryChange,
        reset,
    }
}

/**
 * Colour a batch row consistently everywhere in the UI.
 * @returns {{label: string, classes: string, dot: string}}
 */
export function expiryBadge(expirationDate) {
    if (!expirationDate) {
        return {
            label: 'No expiry',
            classes: 'bg-slate-100 text-slate-600 border-slate-200',
            dot: 'bg-slate-400',
        }
    }

    const days = daysUntil(expirationDate)

    if (days < 0) {
        return {
            label: `Expired ${Math.abs(days)}d ago`,
            classes: 'bg-red-100 text-red-700 border-red-200',
            dot: 'bg-red-500',
        }
    }
    if (days <= 30) {
        return {
            label: `${days}d left`,
            classes: 'bg-orange-100 text-orange-700 border-orange-200',
            dot: 'bg-orange-500',
        }
    }
    if (days <= 90) {
        return {
            label: `${days}d left`,
            classes: 'bg-amber-100 text-amber-700 border-amber-200',
            dot: 'bg-amber-500',
        }
    }

    return {
        label: formatDate(expirationDate),
        classes: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        dot: 'bg-emerald-500',
    }
}

/** Whole days from today until the given date. Negative once past. */
export function daysUntil(expirationDate) {
    if (!expirationDate) return null

    const target = new Date(`${String(expirationDate).slice(0, 10)}T00:00:00`)
    if (Number.isNaN(target.getTime())) return null

    const today = new Date()
    today.setHours(0, 0, 0, 0)

    return Math.round((target - today) / 86400000)
}

/**
 * Render a date for display.
 *
 * Handles the two shapes the API mixes, which must NOT be treated the same way:
 *
 *  - A DATE column, serialised as `2029-05-05`. This is a calendar day with no
 *    time and no zone, so it is read back as local midnight and printed as that
 *    same day. Parsing it any other way is what made dates appear to shift.
 *  - A DATETIME column, serialised as `2026-09-27T20:00:00.000000Z`. This is an
 *    instant, and the `Z` is meaningful: the server keeps UTC+8, so an instant
 *    late in the UTC day is already the *next* day locally. It is rendered in the
 *    viewer's own zone rather than truncated to the string's first ten
 *    characters, which would report yesterday for anything filed in the
 *    evening — about a third of all records.
 */
export function formatDate(value) {
    if (!value) return '—'

    const raw = String(value)
    const isInstant = raw.includes('T')

    const d = isInstant
        ? new Date(raw)
        : new Date(`${raw.slice(0, 10)}T00:00:00`)

    if (Number.isNaN(d.getTime())) return raw

    return d.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    })
}
