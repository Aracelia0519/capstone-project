<script setup lang="ts">
/**
 * One confirmation dialog, for every action in the renewal flow that a user or an
 * administrator would want a second look at before taking.
 *
 * This exists instead of hand-written AlertDialog blocks because the interesting
 * parts are not the markup, they are the rules, and the rules are easy to get
 * subtly wrong once per call site:
 *
 *  - The confirm button is a plain button, not reka's AlertDialogAction. Pressing
 *    an action closes the dialog before the handler that does the work runs, and a
 *    caller that clears its pending state on close then has nothing left to act on.
 *    Closing is therefore entirely the caller's decision, and it makes that decision
 *    once the request has actually succeeded -- so a failure leaves the user where
 *    they were, with anything they typed still in place.
 *  - Escape, an outside click and Cancel are refused while `busy`. A request in
 *    flight cannot be taken back, and a dismissal mid-request is how a half-applied
 *    decision gets lost.
 *  - The confirm button is disabled while `busy`, so a slow request cannot be fired
 *    twice by an impatient double-click.
 *
 * `tone` carries the consequence so the danger is visible before the click, not
 * after it.
 *
 * The caller owns `open`. Set it when asking, clear it when the action succeeds.
 */
import { computed } from 'vue'
import { AlertTriangle, CheckCircle2, HelpCircle, Loader2 } from 'lucide-vue-next'

import {
  AlertDialog,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { buttonVariants } from '@/components/ui/button'
import { cn } from '@/lib/utils'

const props = withDefaults(
  defineProps<{
    open: boolean
    title: string
    /** Prose above any extra content. Kept short; the detail belongs in the slot. */
    description?: string
    confirmLabel?: string
    cancelLabel?: string
    /** 'default' is neutral, 'success' completes something, 'danger' destroys it. */
    tone?: 'default' | 'success' | 'danger'
    /**
     * Blocks confirming because a precondition is unmet, such as a required reason
     * not typed yet. Distinct from `busy`: this says the action is not ready, where
     * `busy` says one is already running. The confirmation still closes on Cancel
     * and Escape, so a dialog can be open but not yet actionable.
     */
    confirmDisabled?: boolean
    /** True while the confirmed action is running. Locks the dialog open. */
    busy?: boolean
  }>(),
  {
    description: '',
    confirmLabel: 'Confirm',
    cancelLabel: 'Cancel',
    tone: 'default',
    confirmDisabled: false,
    busy: false,
  }
)

const emit = defineEmits<{
  'update:open': [boolean]
  confirm: []
}>()

const TONES = {
  danger: {
    title: 'text-red-600',
    confirm: 'bg-red-600 hover:bg-red-700 shadow-red-600/20',
    icon: AlertTriangle,
  },
  success: {
    title: 'text-emerald-600',
    confirm: 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20',
    icon: CheckCircle2,
  },
  default: {
    title: 'text-slate-900',
    confirm: 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/20',
    icon: HelpCircle,
  },
} as const

const tone = computed(() => TONES[props.tone] ?? TONES.default)

/**
 * reka-ui's AlertDialogAction closes the dialog on click. Ignored while busy so
 * the dialog survives the request that click started.
 */
function onOpenChange(next: boolean) {
  if (props.busy) return

  emit('update:open', next)
}
</script>

<template>
  <AlertDialog :open="open" @update:open="onOpenChange">
    <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10005]">
      <AlertDialogHeader>
        <AlertDialogTitle
          class="text-xl font-bold flex items-center gap-2"
          :class="tone.title"
        >
          <component :is="tone.icon" class="w-6 h-6 shrink-0" />
          {{ title }}
        </AlertDialogTitle>

        <AlertDialogDescription
          v-if="description"
          class="text-slate-500 font-medium text-base mt-3 leading-relaxed"
        >
          {{ description }}
        </AlertDialogDescription>

        <!--
          A list of what is about to be sent, or a textarea for a reason. The
          point of a confirmation is that it says what will happen, and a sentence
          is not always enough for that.
        -->
        <slot name="body" />
      </AlertDialogHeader>

      <AlertDialogFooter class="mt-6 sm:space-x-3">
        <AlertDialogCancel
          class="rounded-xl font-bold border-slate-200 text-slate-600 hover:bg-slate-50 h-11"
          :disabled="busy"
        >
          {{ cancelLabel }}
        </AlertDialogCancel>

        <!--
          Deliberately a plain <button>, not AlertDialogAction.

          reka's action is a DialogClose, and DialogClose renders its own onClick
          ahead of any handler passed to it, so pressing Confirm emits
          update:open(false) *before* the handler that does the work gets to run.
          The caller sees that close, clears the state holding the pending action,
          and the handler then finds nothing to act on and returns: the dialog
          blinks shut and no request is ever made.

          So nothing in here closes the dialog except the caller, once the request
          it started has actually succeeded. Escape, an outside click and Cancel all
          still close it, because those go through the root and the caller wants
          them to. The classes are the same composition AlertDialogAction uses, so
          it looks identical to every other confirmation in the app.
        -->
        <button
          type="button"
          :disabled="busy || confirmDisabled"
          :class="cn(
            buttonVariants(),
            'rounded-xl font-bold border-0 text-white h-11 px-6 shadow-md transition-transform hover:scale-105',
            tone.confirm
          )"
          @click="emit('confirm')"
        >
          <Loader2 v-if="busy" class="w-4 h-4 animate-spin mr-2" />
          {{ confirmLabel }}
        </button>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
