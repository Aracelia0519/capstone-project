<template>
  <div class="space-y-3">
    <div v-if="!items || items.length === 0" class="text-xs text-slate-500 italic bg-slate-900/60 border border-slate-800 rounded-lg p-3">
      No work progress recorded yet.
    </div>

    <div v-for="item in items" :key="item.id" class="space-y-1.5">
      <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
          <p class="text-sm font-medium text-white truncate">{{ item.title }}</p>
          <p class="text-[10px] text-slate-500">Updated {{ formatDate(item.updated_at) }}</p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
          <span class="text-xs font-bold" :class="labelClass(item.percentage)">{{ item.percentage }}%</span>
          <template v-if="editable">
            <input
              v-model.number="drafts[item.id]"
              type="number"
              :min="Number(item.percentage) || 0"
              max="100"
              step="1"
              @change="normalize(item)"
              class="w-16 bg-slate-950 border border-slate-700 rounded-md px-2 py-1 text-xs text-white focus:border-cyan-500 focus:ring-cyan-500 outline-none"
            />
            <button
              type="button"
              @click="save(item)"
              :disabled="busy || Number(drafts[item.id]) === Number(item.percentage)"
              class="text-[10px] font-bold h-7 px-2 rounded-md bg-cyan-600/20 text-cyan-400 border border-cyan-600/40 hover:bg-cyan-600/30 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            >
              Save
            </button>
          </template>
        </div>
      </div>
      <div class="h-3 w-full rounded-full bg-slate-800/80 overflow-hidden">
        <div
          class="h-full rounded-full transition-all duration-500"
          :class="barClass(item.percentage)"
          :style="{ width: Math.min(100, Math.max(0, Number(item.percentage) || 0)) + '%' }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, watch } from 'vue'

const props = defineProps({
  items: { type: Array, default: () => [] },
  editable: { type: Boolean, default: false },
  busy: { type: Boolean, default: false }
})

const emit = defineEmits(['update'])

const drafts = reactive({})

watch(() => props.items, (items) => {
  const next = {}
  for (const item of items || []) next[item.id] = Number(item.percentage)
  Object.keys(drafts).forEach((key) => {
    if (!(key in next)) delete drafts[key]
  })
  Object.assign(drafts, next)
}, { immediate: true, deep: true })

const save = (item) => {
  const value = Number(drafts[item.id])
  if (!Number.isInteger(value) || value < 0 || value > 100) return
  emit('update', { id: item.id, percentage: value })
}

const normalize = (item) => {
  const current = Number(item.percentage) || 0
  let value = Number(drafts[item.id])
  if (!Number.isInteger(value)) value = current
  if (value < current) value = current
  if (value > 100) value = 100
  drafts[item.id] = value
}

const barClass = (percentage) => {
  const value = Number(percentage) || 0
  if (value >= 100) return 'bg-gradient-to-r from-emerald-500 to-emerald-400'
  if (value >= 50) return 'bg-gradient-to-r from-cyan-500 to-blue-500'
  return 'bg-gradient-to-r from-amber-500 to-orange-500'
}

const labelClass = (percentage) => {
  const value = Number(percentage) || 0
  if (value >= 100) return 'text-emerald-400'
  if (value >= 50) return 'text-cyan-400'
  return 'text-amber-400'
}

const formatDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '—'
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>
