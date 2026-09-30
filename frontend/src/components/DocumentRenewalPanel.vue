<template>
  <div
    v-if="isVisible"
    class="document-card bg-linear-to-br from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-5"
  >
    <div class="flex items-start justify-between gap-4 mb-1">
      <div class="flex items-center">
        <CalendarClock class="w-5 h-5 mr-2 text-amber-600 icon-hover" />
        <h4 class="font-bold text-gray-800">Renew Business Documents</h4>
      </div>
      <Badge
        v-if="isTerminated"
        variant="outline"
        class="text-red-700 bg-red-50 border-red-200"
      >
        Account terminated
      </Badge>
    </div>

    <p class="text-xs text-gray-600 mb-4">
      {{
        isTerminated
          ? 'Your account was terminated because a document expired. Upload the renewed document below and an administrator will restore your access.'
          : 'Upload a replacement before a document expires. Each document is reviewed on its own, so a new Mayor\'s Permit is not held up by a DTI Certificate you did not send.'
      }}
    </p>

    <!-- Why a document is missing from the form, so a shorter form is not read as a bug. -->
    <p
      v-if="setAside.length"
      class="text-xs text-amber-800 bg-amber-100/60 border border-amber-200 rounded-lg px-3 py-2 mb-4"
    >
      Already approved and still valid, so not shown here:
      {{ setAside.join('; ') }}.
    </p>

    <!-- Current status, so the user knows what they are replacing. -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-5">
      <div
        v-for="doc in tracked"
        :key="doc.key"
        class="rounded-lg border bg-white p-3"
        :class="statusBorder(doc)"
      >
        <div class="flex items-center justify-between gap-2 mb-1">
          <p class="text-sm font-semibold text-gray-900">{{ doc.label }}</p>
          <div class="flex items-center gap-1.5">
            <Badge
              v-if="reviewFor(doc.key)"
              :class="reviewBadge(reviewFor(doc.key)!.status)"
            >
              {{ reviewLabel(reviewFor(doc.key)!.status) }}
            </Badge>
            <Badge :class="statusBadge(doc)">{{ statusLabel(doc) }}</Badge>
          </div>
        </div>
        <p class="text-xs text-gray-600">
          <template v-if="doc.expiration_at">
            Expires {{ formatDate(doc.expiration_at) }}
            <span class="text-gray-400">({{ countdown(doc.days_remaining) }})</span>
          </template>
          <template v-else>No expiration date on record</template>
        </p>
        <a
          v-if="photoUrls[`${doc.key}_photo`]"
          :href="photoUrls[`${doc.key}_photo`]"
          target="_blank"
          rel="noopener"
          class="text-xs font-semibold text-indigo-600 hover:underline"
        >
          View current file
        </a>

        <!--
          The admin's decision, on the card for the document it concerns.

          Shown on every card that has one rather than in a separate list, because
          a rejection is per document: "the Mayor's Permit was rejected" has to sit
          next to the Mayor's Permit, or the user re-uploads the wrong one.
        -->
        <div
          v-if="reviewFor(doc.key)?.status === 'rejected'"
          class="mt-2 rounded-md border border-red-200 bg-red-50 p-2"
        >
          <p class="text-xs font-semibold text-red-800">
            Rejected{{ reviewFor(doc.key)!.reviewed_at ? ` on ${formatDate(reviewFor(doc.key)!.reviewed_at)}` : '' }}
          </p>
          <p class="text-xs text-red-700 mt-0.5">
            {{ reviewFor(doc.key)!.rejection_reason }}
          </p>
          <p class="text-xs text-red-600 mt-1">Upload a replacement below.</p>
        </div>
        <p
          v-else-if="reviewFor(doc.key)?.status === 'pending'"
          class="mt-2 rounded-md border border-amber-200 bg-amber-50 p-2 text-xs text-amber-800"
        >
          Submitted — waiting for an administrator to check it.
        </p>
        <p
          v-else-if="reviewFor(doc.key)?.status === 'approved'"
          class="mt-2 text-xs text-emerald-700"
        >
          Verified and approved.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div
        v-for="doc in tracked"
        :key="`form-${doc.key}`"
        class="rounded-lg border border-gray-200 bg-white p-4"
      >
        <label
          :for="`renew-file-${doc.key}`"
          class="block text-sm font-medium text-gray-700 mb-1"
        >
          New {{ doc.label }} file
        </label>
        <input
          :id="`renew-file-${doc.key}`"
          type="file"
          accept=".jpg,.jpeg,.png,.pdf"
          class="w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
          @change="onFileChange($event, doc.key)"
        >

        <label
          :for="`renew-date-${doc.key}`"
          class="block text-sm font-medium text-gray-700 mt-3 mb-1"
        >
          New {{ doc.label }} expiration
        </label>
        <input
          :id="`renew-date-${doc.key}`"
          v-model="form[doc.key]"
          type="date"
          :min="todayDate"
          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
        <p class="text-xs text-gray-500 mt-1">
          Leave both blank to keep the current {{ doc.label }}.
        </p>
      </div>
    </div>

    <!-- Extra documents, same shape as the first submission. -->
    <div class="mt-5 rounded-lg border border-gray-200 bg-white p-4">
      <div class="flex items-start justify-between gap-4 mb-1">
        <div class="flex items-center">
          <FileText class="w-5 h-5 mr-2 text-blue-500 icon-hover" />
          <h5 class="font-bold text-gray-800">
            Related Documents
            <span class="text-xs font-normal text-gray-500">optional</span>
          </h5>
        </div>
        <button
          type="button"
          @click="addRelatedDocument"
          class="shrink-0 rounded-lg border border-blue-300 px-3 py-1.5 text-sm font-semibold text-blue-700 hover:bg-blue-50 transition"
        >
          + Add Document
        </button>
      </div>

      <p
        v-if="relatedDocuments.length === 0"
        class="text-sm text-gray-500 text-center py-3 border border-dashed border-gray-300 rounded-lg"
      >
        No new related documents.
      </p>

      <div v-else class="space-y-3">
        <div
          v-for="row in relatedDocuments"
          :key="row.key"
          class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start rounded-lg border border-gray-200 p-3"
        >
          <div class="md:col-span-4">
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Document Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="row.document_name"
              type="text"
              placeholder="e.g. SEC Registration"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          <div class="md:col-span-4">
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Document File <span class="text-red-500">*</span>
            </label>
            <input
              type="file"
              accept=".jpg,.jpeg,.png,.pdf"
              class="w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
              @change="onRelatedFileChange($event, row)"
            >
          </div>
          <div class="md:col-span-3">
            <label class="block text-xs font-medium text-gray-700 mb-1">
              Expiration <span class="text-gray-400">optional</span>
            </label>
            <input
              v-model="row.expiration_date"
              type="date"
              :min="todayDate"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
          <div class="md:col-span-1 flex items-start">
            <button
              type="button"
              @click="removeRelatedDocument(row.key)"
              class="rounded-lg p-2 text-red-600 hover:bg-red-50 transition"
              :aria-label="`Remove ${row.document_name || 'document'}`"
            >
              <Trash2 class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Documents already on file, with a way to withdraw one. -->
    <div v-if="documents.related_documents?.length" class="mt-5">
      <p class="text-xs font-medium text-gray-700 mb-2">On file</p>
      <div class="space-y-2">
        <div
          v-for="doc in documents.related_documents"
          :key="doc.id"
          class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2"
        >
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ doc.document_name }}</p>
            <p v-if="doc.expiration_at" class="text-xs text-gray-500">
              Expires {{ formatDate(doc.expiration_at) }}
            </p>
          </div>
          <div class="flex items-center gap-3">
            <a
              v-if="doc.file_url"
              :href="doc.file_url"
              target="_blank"
              rel="noopener"
              class="text-xs font-semibold text-indigo-600 hover:underline"
            >
              Open
            </a>
            <button
              type="button"
              :disabled="removingId === doc.id || submitting"
              @click="askRemoveRelatedDocument(doc)"
              class="text-xs font-semibold text-red-600 hover:underline disabled:opacity-50"
            >
              {{ removingId === doc.id ? 'Removing...' : 'Remove' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="notice"
      class="mt-4 rounded-lg border px-3 py-2 text-xs"
      :class="noticeClass"
    >
      {{ notice }}
    </div>

    <div class="mt-5 flex justify-end">
      <button
        type="button"
        :disabled="!hasAnythingToRenew || submitting"
        @click="askSubmitRenewal"
        class="inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-300 shadow-md touch-friendly"
        :class="hasAnythingToRenew && !submitting
          ? 'bg-linear-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white shadow-amber-500/25'
          : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
      >
        <Loader2 v-if="submitting" class="w-4 h-4 mr-2 animate-spin" />
        <CalendarClock v-else class="w-4 h-4 mr-2" />
        {{ submitting ? 'Uploading...' : 'Submit Renewal' }}
      </button>
    </div>
  </div>

  <!--
    In place of the form, not just nothing. A valid account that once had this
    form has no other way to learn it still exists or when it comes back, and an
    unexplained disappearance reads as a broken screen.
  -->
  <p
    v-else-if="hiddenSummary"
    class="document-card rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-600"
  >
    {{ hiddenSummary }}
  </p>

  <!--
    Confirmation for the batch. Names every document about to be sent and the date
    typed for each, because "Submit Renewal" on its own gives the user no way to
    catch a date entered into the wrong field -- and the date is the part an
    administrator judges the file against.
  -->
  <ConfirmDialog
    :open="pendingSubmit !== null"
    title="Send this renewal for review?"
    :description="pendingSubmit
      ? 'Your current documents stay exactly as they are until an administrator approves these. Your account is not changed by sending them.'
      : ''"
    confirm-label="Send for review"
    :busy="submitting"
    @update:open="pendingSubmit = $event ? pendingSubmit : null"
    @confirm="submitRenewal"
  >
    <template #body>
      <ul
        v-if="pendingSubmit"
        class="mt-3 divide-y divide-amber-200/70 rounded-lg border border-amber-200 bg-amber-50/70 text-left text-sm"
      >
        <li
          v-for="doc in pendingSubmit.documents"
          :key="doc.label"
          class="flex flex-wrap items-baseline justify-between gap-2 px-3 py-2"
        >
          <span class="font-semibold text-gray-900">{{ doc.label }}</span>
          <span class="text-gray-600">expires {{ formatDate(doc.date) }}</span>
        </li>
        <li
          v-for="name in pendingSubmit.related"
          :key="name"
          class="flex flex-wrap items-baseline justify-between gap-2 px-3 py-2"
        >
          <span class="font-semibold text-gray-900">{{ name }}</span>
          <span class="text-gray-600">extra document</span>
        </li>
      </ul>

      <p
        v-if="submitSupersedes.length"
        class="mt-2 rounded-lg bg-amber-100/70 border border-amber-300 px-3 py-2 text-left text-xs text-amber-900"
      >
        <strong>{{ submitSupersedes.join(' and ') }}</strong>
        {{ submitSupersedes.length === 1 ? 'is' : 'are' }} already waiting for a decision.
        Sending these replaces {{ submitSupersedes.length === 1 ? 'it' : 'them' }}, and only the
        new upload will be reviewed.
      </p>
    </template>
  </ConfirmDialog>

  <ConfirmDialog
    :open="pendingRemove !== null"
    title="Remove this document?"
    :description="pendingRemove
      ? `'${pendingRemove.document_name}' will be taken off your account. If it was one an administrator is relying on, tell them before removing it.`
      : ''"
    confirm-label="Remove document"
    tone="danger"
    :busy="removingId !== null"
    @update:open="pendingRemove = $event ? pendingRemove : null"
    @confirm="removeRelatedDocumentOnServer"
  />
</template>

<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { toast } from 'vue-sonner'
import { CalendarClock, FileText, Loader2, Trash2 } from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import axios from '@/utils/axios'

/**
 * Lets an already-approved (or terminated) supplier/distributor replace an
 * expiring document and its expiration date.
 *
 * This exists because the verification wizard cannot: store() refuses an account
 * that has already submitted, which left no way to renew at all — and no way for
 * an admin to revoke a termination, since that action requires documents newer
 * than the termination.
 *
 * Every rule here is a convenience. The server re-derives the window, re-checks
 * the role, and re-validates the pairs; nothing on this screen is a gate.
 */

interface DocumentSummary {
  key: string
  label: string
  expiration_at: string | null
  days_remaining: number | null
  state: 'ok' | 'warning' | 'critical' | 'expired' | 'missing'
  /** The server's own answer to "is this inside the renewal window". */
  needs_renewal: boolean
}

interface RelatedDocumentRow {
  key: string
  document_name: string
  file: File | null
  expiration_date: string
}

/**
 * The admin's decision on one renewed document.
 *
 * One entry per tracked document, keyed by document key, holding the latest
 * decision. A document that was never renewed has no entry.
 */
interface DocumentReview {
  document_key: string
  status: 'pending' | 'approved' | 'rejected' | 'superseded'
  rejection_reason: string | null
  reviewed_at: string | null
}

interface DocumentsPayload {
  dti_certificate: DocumentSummary
  mayor_permit: DocumentSummary
  related_documents: Array<{
    id: number
    document_name: string
    file_url: string | null
    expiration_at: string | null
  }>
  documents_verified_at: string | null
  documents_verified_by: number | null
  reviews?: Record<string, DocumentReview | null>
  /** How many replacements are still waiting on an administrator. */
  pending_reviews?: number
}

const props = defineProps<{
  /** 'supplier' or 'distributor' — decides the endpoint. */
  role: 'supplier' | 'distributor'
  /** The documents payload from GET /{role}/requirements. */
  documents: DocumentsPayload | null | undefined
  /** Field-name -> absolute URL, from the same response's `photos` map. */
  photoUrls: Record<string, string>
  /** The account's user status, so a terminated user gets the right wording. */
  accountStatus?: string
}>()

const emit = defineEmits<{ (e: 'renewed', documents: DocumentsPayload): void }>()

const RENEWAL_WINDOW_DAYS = 30

const submitting = ref(false)
const removingId = ref<number | null>(null)
const notice = ref('')
const noticeClass = ref('')

/**
 * What the user is about to do, held until they confirm it.
 *
 * Two kinds of action here are worth a second look, and both are irreversible in
 * the sense that matters: a submitted renewal opens a review that supersedes any
 * earlier one still waiting, and a withdrawn document is gone. Each holds the
 * detail the confirmation needs, captured at the moment the button was pressed --
 * the list of documents, the dates typed into them -- so the dialog can name what
 * is about to be sent rather than saying "your renewal".
 *
 * Null means no confirmation is open, which is the whole open/closed state.
 */
const pendingSubmit = ref<{
  documents: Array<{ key: string; label: string; date: string }>
  related: string[]
} | null>(null)

const pendingRemove = ref<{ id: number; document_name: string } | null>(null)

const form = reactive<Record<string, string>>({
  dti_certificate: '',
  mayor_permit: '',
})

const files = reactive<Record<string, File | null>>({
  dti_certificate: null,
  mayor_permit: null,
})

const relatedDocuments = ref<RelatedDocumentRow[]>([])
let relatedKey = 0

// The local calendar date, not toISOString(): that converts to UTC, so a user
// east of Greenwich would see yesterday as the floor and be unable to pick today.
const todayDate = computed(() => {
  const now = new Date()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const day = String(now.getDate()).padStart(2, '0')
  return `${now.getFullYear()}-${month}-${day}`
})

/**
 * Every tracked document, before the per-document decision about showing it.
 */
const allDocuments = computed<DocumentSummary[]>(() => {
  if (!props.documents) return []

  return [props.documents.dti_certificate, props.documents.mayor_permit].filter(Boolean)
})

/**
 * Does this document still need something from the user?
 *
 * Three ways a document earns a place on the form, and one that does not:
 *
 * - It is inside the renewal window (`needs_renewal`, the server's own verdict on
 *   the 30-day rule).
 * - Its last renewal was rejected. The user was asked for a valid replacement and
 *   has not sent one; a form that quietly stopped offering the document would
 *   leave them with a rejection notice and nowhere to answer it.
 * - Its last renewal is still awaiting a decision. Shown so the panel does not
 *   disappear the instant a renewal is submitted.
 *
 * A document whose renewal was approved and which is comfortably outside the
 * window is the one that does not: it is done. This is what the user asked for --
 * when a DTI Certificate is approved and the Mayor's Permit is rejected, only the
 * Mayor's Permit is offered, because the approved one is valid and re-uploading
 * it would queue a review of a document nobody needs to change.
 */
function needsAction(doc: DocumentSummary): boolean {
  if (doc.needs_renewal) return true

  const status = props.documents?.reviews?.[doc.key]?.status

  return status === 'rejected' || status === 'pending'
}

const isTerminated = computed(() => props.accountStatus === 'inactive')

/** Only the documents that still need something. Drives both the form and its gate. */
const tracked = computed<DocumentSummary[]>(() =>
  isTerminated.value ? allDocuments.value : allDocuments.value.filter(needsAction)
)

/**
 * Documents deliberately left off the form, with why.
 *
 * A form that quietly loses half its fields reads as a bug, and the user's first
 * instinct would be to wonder whether their approved renewal went through. Naming
 * what is set aside turns that into the answer it actually is.
 */
const setAside = computed(() => {
  if (isTerminated.value) return []

  return allDocuments.value
    .filter((doc) => !needsAction(doc))
    .map((doc) => `${doc.label} (approved, valid until ${formatDate(doc.expiration_at)})`)
})

/**
 * Whether the renewal form should be on screen at all.
 *
 * The rule the user asked for: only inside the renewal window, so an account
 * whose documents were just approved does not keep being told to renew them.
 * `needs_renewal` is the server's own verdict on the 30-day window, so this
 * composes flags rather than redoing the date arithmetic in the browser -- two
 * implementations of "is it close enough" would eventually disagree, and the one
 * that loses is whichever screen the user happens to be looking at.
 *
 * `tracked` is already the "still needs attention" set, so this only has to ask
 * whether that set is empty.
 *
 * Two cases override the window, and both are about not stranding someone:
 *
 * - A terminated account always sees the form, with every document on it.
 *   Termination is triggered by an expired document, and restoring access requires
 *   documents newer than the termination. Hiding the only upload path would make
 *   the account unrecoverable short of a database edit.
 * - A submission awaiting review keeps the panel visible, because a pending
 *   document counts as needing action. A strict window test would otherwise make
 *   the panel vanish the instant the user pressed submit -- and the user, looking
 *   at a form that disappeared and no new date on their document, would conclude
 *   the upload failed and try again.
 */
const isVisible = computed(() => Boolean(props.documents) && tracked.value.length > 0)

/**
 * A short line in place of the form, so a valid account learns the form exists
 * and when it will come back. Without it the panel just vanishes and the feature
 * looks broken.
 *
 * Read from every document rather than from `tracked`. This only renders when the
 * form is hidden, which is exactly when `tracked` is empty -- asking the
 * "still needs attention" set which document comes next would always answer
 * nothing and the note would never appear.
 */
const hiddenSummary = computed(() => {
  if (!props.documents) return null

  const next = allDocuments.value
    .filter((doc) => !doc.needs_renewal && doc.expiration_at)
    .map((doc) => ({ label: doc.label, days: doc.days_remaining }))
    .sort((a, b) => (a.days ?? 0) - (b.days ?? 0))[0]

  if (!next) return null

  return `Your ${next.label} is valid for another ${countdown(next.days).toLowerCase()}. ` +
    `This form returns ${RENEWAL_WINDOW_DAYS} days before it expires.`
})

/** The latest admin decision on a document, or null if it was never renewed. */
function reviewFor(key: string): DocumentReview | null {
  return props.documents?.reviews?.[key] ?? null
}

/**
 * Anything worth submitting. A half-filled pair (a file with no date, or a date
 * with no file) is not: the server would reject it with an error keyed on a
 * field the user cannot see on this panel, so it is caught here first.
 */
const hasAnythingToRenew = computed(() => {
  for (const doc of tracked.value) {
    const hasFile = files[doc.key] !== null
    const hasDate = Boolean(form[doc.key])
    if (hasFile !== hasDate) return false
    if (hasFile && hasDate) return true
  }

  return relatedDocumentsValid.value && relatedDocuments.value.length > 0
})

// A row the user chose to add has to be complete, for the same reason.
const relatedDocumentsValid = computed(() =>
  relatedDocuments.value.every((row) => row.document_name.trim() !== '' && row.file !== null)
)

/**
 * Documents in this batch that already have a renewal waiting for a decision.
 *
 * Sending again does not queue a second review alongside the first -- it marks the
 * earlier one superseded, so the only version the administrator will ever see is
 * this one. That is the right behaviour, but it is not what "Send" suggests, and
 * it is the one irreversible thing about pressing the button, so the confirmation
 * says it out loud instead of letting it be discovered afterwards.
 */
const submitSupersedes = computed(() => {
  if (!pendingSubmit.value) return []

  return pendingSubmit.value.documents
    .filter((doc) => reviewFor(doc.key)?.status === 'pending')
    .map((doc) => doc.label)
})

// Clears the form when the documents prop changes, which is what a successful
// submit does -- the panel is handed the server's new state and empties itself.
//
// The notice is deliberately left alone. A submit sets one, then immediately
// emits, and clearing it here would wipe the confirmation (or the partial-failure
// warning) before it was ever read. It is cleared on the next edit instead, in
// onFileChange and the date handlers.
/** Empty the renewal form. One definition, called from the watcher and on submit. */
function resetForm() {
  form.dti_certificate = ''
  form.mayor_permit = ''
  files.dti_certificate = null
  files.mayor_permit = null
  relatedDocuments.value = []
}

watch(
  () => props.documents,
  () => {
    // Not while a submission is in flight.
    //
    // props.documents changes for reasons that have nothing to do with this form
    // being finished: the DocumentsRenewed broadcast is pushed on the user's own
    // channel, so the account's own first upload replaces the prop here, and a
    // DocumentReviewDecided from an admin's decision on some other document does
    // the same. Emptying the form on any of those discards what the user is
    // part-way through typing -- which is exactly what made a two-document
    // renewal submit only the first one.
    //
    // submitRenewal() calls resetForm() itself once the last request returns, so
    // nothing is left behind by skipping this.
    if (submitting.value) return

    resetForm()
  }
)

function onFileChange(event: Event, key: string) {
  files[key] = (event.target as HTMLInputElement).files?.[0] || null
  notice.value = ''
  noticeClass.value = ''
}

function addRelatedDocument() {
  relatedDocuments.value.push({
    key: `renew-related-${++relatedKey}`,
    document_name: '',
    file: null,
    expiration_date: '',
  })
}

function removeRelatedDocument(key: string) {
  relatedDocuments.value = relatedDocuments.value.filter((row) => row.key !== key)
}

function onRelatedFileChange(event: Event, row: RelatedDocumentRow) {
  row.file = (event.target as HTMLInputElement).files?.[0] || null
}

/**
 * Ask before withdrawing a document that is already on file.
 *
 * Separate from the handler that does the removing: the rows are stored records,
 * not form state, so there is nothing to warn about locally beyond the loss.
 *
 * This replaces a window.confirm: the native dialog cannot be styled, cannot show
 * what the document actually is, and on some browsers it renders as an unlabelled
 * bar that reads as a browser error rather than a question.
 */
function askRemoveRelatedDocument(doc: { id: number; document_name: string }) {
  if (removingId.value !== null || submitting.value) return

  pendingRemove.value = doc
}

/**
 * Withdraw the document the confirmation named.
 *
 * Closes the dialog only once the server has confirmed the delete, so a failure
 * leaves the row on screen and the user can try again.
 */
async function removeRelatedDocumentOnServer() {
  const doc = pendingRemove.value
  if (!doc || removingId.value !== null) return

  removingId.value = doc.id

  try {
    await axios.delete(`/${props.role}/requirements/related-documents/${doc.id}`)
    toast.success(`"${doc.document_name}" removed.`)
    pendingRemove.value = null
    emit('renewed', await refreshDocuments())
  } catch (error: any) {
    toast.error(readError(error, 'The document could not be removed.'))
  } finally {
    removingId.value = null
  }
}

/**
 * Collect what is about to be sent and ask about it.
 *
 * The two validation checks live here rather than in submitRenewal because they
 * decide whether there is anything to confirm. A half-filled pair would be
 * rejected by the server with an error keyed on a field this panel does not show,
 * so it is caught before the user is asked to confirm a submission that cannot
 * happen.
 *
 * Only documents that are actually complete are named in the dialog. Listing all
 * of them would invite the question "why is the Mayor's Permit not in this list?"
 * when the honest answer is that nothing was chosen for it.
 */
function askSubmitRenewal() {
  if (!hasAnythingToRenew.value || submitting.value) return

  if (!relatedDocumentsValid.value) {
    notice.value = 'Every related document needs both a name and a file.'
    noticeClass.value = 'border-red-200 bg-red-50 text-red-700'
    return
  }

  const documents = tracked.value
    .filter((doc) => files[doc.key] && form[doc.key])
    // `key` alongside the label: the confirmation needs the label to show, and
    // the key to look up whether that document already has a renewal waiting.
    .map((doc) => ({ key: doc.key, label: doc.label, date: form[doc.key] as string }))

  const related = relatedDocuments.value
    .filter((row) => row.document_name.trim() !== '' && row.file !== null)
    .map((row) => row.document_name.trim())

  if (!documents.length && !related.length) return

  pendingSubmit.value = { documents, related }
}

/**
 * Send the renewal the confirmation named.
 *
 * One request per document, because the server treats a document as its own
 * resource with its own review. The old single POST put both documents in one
 * multipart body, so a half-filled pair had to be caught in the browser and a
 * typo in one field name could silently renew the wrong document.
 *
 * The dialog stays open for the whole batch and only closes if the submission
 * went through, so a failure on the second document does not lose the first one's
 * success notice or make the user re-send a file the server already has.
 */
async function submitRenewal() {
  if (!pendingSubmit.value || !hasAnythingToRenew.value || submitting.value) return

  if (!relatedDocumentsValid.value) {
    notice.value = 'Every related document needs both a name and a file.'
    noticeClass.value = 'border-red-200 bg-red-50 text-red-700'
    return
  }

  submitting.value = true
  notice.value = ''

  const submitted: string[] = []
  let latest: DocumentsPayload | null = null

  // Snapshot what is about to be sent, before any await.
  //
  // The loops below are the only place these values are read, and the first
  // successful request changes props.documents: the server broadcasts
  // DocumentsRenewed on this user's own channel, the parent hands the panel a new
  // documents object, and that trips the watcher which empties `files` and `form`.
  // Reading them live meant the second document in the batch found itself cleared
  // and hit `continue` -- one document submitted, no error anywhere, and a success
  // message saying the renewal had gone through. A batch is decided when the user
  // presses Submit, not while it is in flight.
  const queue = tracked.value
    .map((doc) => ({ key: doc.key, label: doc.label, file: files[doc.key], date: form[doc.key] }))
    .filter((item) => Boolean(item.file && item.date))

  const relatedQueue = relatedDocuments.value
    .filter((row) => row.document_name.trim() !== '' && row.file !== null)
    .map((row) => ({
      name: row.document_name.trim(),
      file: row.file as File,
      expiration: row.expiration_date,
    }))

  // Sequential, not parallel: every response carries the whole document block, so
  // concurrent requests would leave the last writer's payload missing the updates
  // the others made.
  try {
    for (const item of queue) {
      const body = new FormData()
      body.append('file', item.file as File)
      body.append('expiration_date', item.date as string)

      // POST, and no Content-Type is set here. Both details are load bearing, and
      // the wrong choice fails silently with a 422 claiming no file was attached:
      //
      // 1. Not PUT or PATCH. PHP only runs its multipart parser for POST, so a PUT
      //    carrying multipart/form-data leaves $_POST and $_FILES empty and the
      //    body unread in php://input, so every field reads as missing.
      //    Measured, not assumed.
      // 2. The header has to be absent. The shared instance declares a default
      //    of Content-Type: application/json, and against a FormData body axios
      //    answers that with JSON.stringify(formDataToJSON(data)) -- text fields
      //    survive, the file is dropped without a word. Pinning multipart/form-data
      //    by hand is no better: that string carries no boundary, so the server
      //    cannot delimit the parts at all. The interceptor in src/utils/axios.js
      //    deletes the header for any FormData body, leaving the browser to
      //    generate one carrying the boundary it actually chose.
      const { data } = await axios.post(`/${props.role}/requirements/documents/${item.key}`, body)

      if (data?.data?.documents) latest = data.data.documents
      submitted.push(item.label)
    }

    for (const row of relatedQueue) {
      const body = new FormData()
      body.append('document_name', row.name)
      body.append('file', row.file)
      if (row.expiration) body.append('expiration_date', row.expiration)

      // POST with the header left to the browser -- see the note above.
      const { data } = await axios.post(`/${props.role}/requirements/related-documents`, body)

      if (data?.data?.documents) latest = data.data.documents
      submitted.push(row.name)
    }

    // The form empties itself here rather than waiting for the watcher, which now
    // stands aside while a submission is in flight. Without this the inputs would
    // still hold a file and a date that have already been sent, and the user
    // would be invited to send the same one again.
    if (submitted.length) {
      resetForm()
    }

    if (submitted.length) {
      notice.value = 'Uploaded. An administrator will confirm the new dates.'
      noticeClass.value = 'border-emerald-200 bg-emerald-50 text-emerald-700'
      toast.success(
        submitted.length === 1
          ? `${submitted[0]} sent for review.`
          : `${submitted.length} documents sent for review.`
      )
    }

    // Every response carried the whole document block, so the panel can redraw
    // from what the server actually stored rather than from what we meant to
    // send. The refetch is the fallback for a response that did not include it.
    emit('renewed', latest ?? (await refreshDocuments()))

    // The renewal is queued, so the confirmation has done its job. Cleared here
    // rather than in `finally` so a failure below leaves the dialog open with the
    // user's own files still in the form behind it.
    pendingSubmit.value = null
  } catch (error: any) {
    // Partial success is a real possibility here, so say what landed rather than
    // reporting the whole submission as failed. Telling a user their renewal
    // did not upload when one of two documents did is how they re-upload a
    // document that is already in the queue and queue a second review for it.
    if (submitted.length) {
      notice.value = `${submitted.join(', ')} uploaded, but the rest failed. Check each document and resubmit.`
      noticeClass.value = 'border-amber-200 bg-amber-50 text-amber-800'
      emit('renewed', await refreshDocuments())
    }

    if (error.response?.status === 401) {
      toast.error('Session expired. Please log in again.')
    } else {
      toast.error(readError(error, 'The renewal could not be uploaded. Please try again.'))
    }
  } finally {
    submitting.value = false
  }
}

/** Re-read the documents so the panel reflects the server, not our optimism. */
async function refreshDocuments(): Promise<DocumentsPayload> {
  const { data } = await axios.get(`/${props.role}/requirements`)

  return data?.data?.documents
}

/* Presentation only. */

function countdown(days: number | null) {
  if (days === null || days === undefined) return '—'
  if (days < 0) return `${Math.abs(days)} day(s) ago`
  if (days === 0) return 'today'
  if (days === 1) return 'tomorrow'
  return `${days} days from now`
}

// Split on the hyphen rather than handing the string to new Date(), which
// parses "Y-m-d" as UTC midnight and renders the previous day west of Greenwich.
function formatDate(value: string | null) {
  if (!value) return '—'

  const [year, month, day] = value.slice(0, 10).split('-')
  if (!year || !month || !day) return value

  return `${month}/${day}/${year}`
}

function statusLabel(doc: DocumentSummary) {
  switch (doc.state) {
    case 'expired': return 'Expired'
    case 'critical': return 'Urgent'
    case 'warning': return 'Nearing expiry'
    case 'missing': return 'No date'
    default: return 'Valid'
  }
}

function statusBadge(doc: DocumentSummary) {
  switch (doc.state) {
    case 'expired': return 'bg-red-100 text-red-700 border border-red-200'
    case 'critical': return 'bg-red-50 text-red-700 border border-red-200'
    case 'warning': return 'bg-amber-100 text-amber-800 border border-amber-200'
    case 'missing': return 'bg-slate-100 text-slate-600 border border-slate-200'
    default: return 'bg-emerald-100 text-emerald-700 border border-emerald-200'
  }
}

function reviewLabel(status: DocumentReview['status']) {
  switch (status) {
    case 'approved': return 'Approved'
    case 'rejected': return 'Rejected'
    case 'pending': return 'Under review'
    default: return 'Resubmitted'
  }
}

function reviewBadge(status: DocumentReview['status']) {
  switch (status) {
    case 'approved': return 'bg-emerald-100 text-emerald-700 border border-emerald-200'
    case 'rejected': return 'bg-red-100 text-red-700 border border-red-200'
    default: return 'bg-amber-100 text-amber-800 border border-amber-200'
  }
}

function statusBorder(doc: DocumentSummary) {
  return doc.days_remaining !== null && doc.days_remaining <= RENEWAL_WINDOW_DAYS
    ? 'border-amber-300'
    : 'border-gray-200'
}

function readError(error: any, fallback: string) {
  const errors = error?.response?.data?.errors

  if (errors) {
    const flattened = Object.values(errors).flat() as string[]
    if (flattened.length) return flattened.join(', ')
  }

  return error?.response?.data?.message || fallback
}
</script>
