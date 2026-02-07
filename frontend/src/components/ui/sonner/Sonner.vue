<script setup>
import {
  CircleCheckIcon,
  InfoIcon,
  Loader2Icon,
  OctagonXIcon,
  TriangleAlertIcon,
  XIcon,
} from "lucide-vue-next";
import { Toaster as Sonner } from "vue-sonner";
// Import the necessary styles for positioning
import "vue-sonner/style.css"; 
import { cn } from "@/lib/utils";

const props = defineProps({
  id: { type: String, required: false },
  invert: { type: Boolean, required: false },
  theme: { type: String, required: false },
  // Default set to 'bottom-right'
  position: { type: String, required: false, default: 'top-right' },
  closeButtonPosition: { type: String, required: false },
  hotkey: { type: Array, required: false },
  richColors: { type: Boolean, required: false },
  expand: { type: Boolean, required: false },
  duration: { type: Number, required: false },
  gap: { type: Number, required: false },
  visibleToasts: { type: Number, required: false },
  closeButton: { type: Boolean, required: false },
  toastOptions: { type: Object, required: false },
  class: { type: String, required: false },
  style: { type: Object, required: false },
  offset: { type: [Object, String, Number], required: false },
  mobileOffset: { type: [Object, String, Number], required: false },
  dir: { type: String, required: false },
  swipeDirections: { type: Array, required: false },
  icons: { type: Object, required: false },
  containerAriaLabel: { type: String, required: false },
});
</script>

<template>
  <Sonner
    :class="cn('toaster group', props.class)"
    v-bind="props"
    :position="props.position"
    :toast-options="{
      class: cn(
        'group toast group-[.toaster]:bg-background group-[.toaster]:text-foreground group-[.toaster]:border-border group-[.toaster]:shadow-lg',
        'group-[.toaster]:rounded-xl group-[.toaster]:border group-[.toaster]:p-4 group-[.toaster]:font-sans',
        // Dark mode specific adjustments
        'dark:group-[.toaster]:bg-slate-900 dark:group-[.toaster]:text-slate-50 dark:group-[.toaster]:border-slate-800',
        props.toastOptions?.class
      ),
      descriptionClass: cn(
        'group-[.toast]:text-muted-foreground text-xs mt-1', 
        'dark:group-[.toast]:text-slate-400', 
        props.toastOptions?.descriptionClass
      ),
      actionButtonClass: cn(
        'group-[.toast]:bg-primary group-[.toast]:text-primary-foreground', 
        props.toastOptions?.actionButtonClass
      ),
      cancelButtonClass: cn(
        'group-[.toast]:bg-muted group-[.toast]:text-muted-foreground', 
        props.toastOptions?.cancelButtonClass
      ),
    }"
  >
    <template #success-icon>
      <CircleCheckIcon class="size-5 text-emerald-500" />
    </template>
    <template #info-icon>
      <InfoIcon class="size-5 text-blue-500" />
    </template>
    <template #warning-icon>
      <TriangleAlertIcon class="size-5 text-amber-500" />
    </template>
    <template #error-icon>
      <OctagonXIcon class="size-5 text-red-500" />
    </template>
    <template #loading-icon>
      <div>
        <Loader2Icon class="size-5 animate-spin text-slate-500" />
      </div>
    </template>
    <template #close-icon>
      <XIcon class="size-4 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-50 transition-colors" />
    </template>
  </Sonner>
</template>