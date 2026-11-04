<script setup>
defineProps({
  before: { type: String, default: null },
  after: { type: String, required: true },
  aspectClass: { type: String, default: 'aspect-[4/3]' },
  imageClass: { type: String, default: 'object-cover' }
})
</script>

<template>
  <div class="relative overflow-hidden bg-slate-900 group/before-after" :class="aspectClass">
    <div class="grid grid-cols-2 h-full">
      <div class="relative h-full overflow-hidden animate-in fade-in slide-in-from-left-4 duration-700">
        <img
          v-if="before"
          :src="before"
          alt="Before"
          :class="['h-full w-full transition-transform duration-1000 group-hover/before-after:scale-105', imageClass]"
          loading="lazy"
        />
        <div v-else class="flex h-full w-full flex-col items-center justify-center gap-1 bg-slate-900 px-2 text-center text-slate-500">
          <span class="text-[10px] font-bold uppercase tracking-wider">No before photo</span>
        </div>

        <span class="absolute bottom-2 left-2 rounded-full border border-white/20 bg-slate-950/75 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-white backdrop-blur-md">
          Before
        </span>
      </div>

      <div class="relative h-full overflow-hidden border-l border-white/20 animate-in fade-in slide-in-from-right-4 duration-700">
        <img
          :src="after"
          alt="After"
          :class="['h-full w-full transition-transform duration-1000 group-hover/before-after:scale-105', imageClass]"
          loading="lazy"
        />

        <span class="absolute bottom-2 right-2 rounded-full border border-white/20 bg-slate-950/75 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-white backdrop-blur-md">
          After
        </span>
      </div>
    </div>

    <div class="pointer-events-none absolute inset-y-0 left-1/2 w-px -translate-x-1/2 bg-white/40"></div>
  </div>
</template>
