<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0a0c14] via-[#0a0f1f] to-[#05070f] p-4 sm:p-6 text-slate-200 relative overflow-x-hidden">
    <!-- Animated background elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-600/20 rounded-full blur-3xl animate-pulse"></div>
      <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl animate-pulse delay-1000"></div>
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-cyan-500/5 rounded-full blur-3xl"></div>
    </div>

    <header class="max-w-7xl mx-auto text-center mb-8 sm:mb-12 relative z-10">
      <div class="inline-flex flex-col md:flex-row items-center gap-4 mb-4">
        <div class="relative group">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur-xl opacity-50 group-hover:opacity-75 transition-opacity"></div>
          <img src="/favicon.svg" class="w-15 h-15" alt="icon" />
        </div>
        <div class="text-center md:text-left">
          <h1 class="text-3xl sm:text-5xl md:text-6xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 animate-gradient-x">
            Color Mixing Studio
          </h1>
        </div>
      </div>
      
    </header>

    <main class="max-w-7xl mx-auto relative z-10">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
          <!-- Mixer Controls - Premium Card -->
          <div class="group relative bg-slate-900/30 backdrop-blur-2xl rounded-3xl border border-white/10 shadow-2xl overflow-hidden transition-all duration-500 hover:shadow-[0_0_40px_rgba(0,0,0,0.5)]">
            <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative p-6">
              <h2 class="text-xl font-bold text-white flex items-center gap-2 mb-6">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center shadow-lg">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                </div>
                Spectral Mixer
              </h2>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
                <div v-for="(color, index) in colors" :key="color.id" 
                     :class="['relative rounded-2xl p-4 transition-all duration-500 cursor-pointer', 
                              color.active ? 'bg-gradient-to-br from-white/10 to-white/5 border-2' : 'bg-slate-800/20 border border-white/5 opacity-60',
                              color.active ? getBorderClass(index) : '']"
                     @click="toggleCardActive(index)">
                  
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-white text-lg">{{ color.name }}</h3>
                    <div class="relative">
                      <input type="checkbox" v-model="color.active" @change="toggleActive(index, $event.target.checked)" 
                             class="w-5 h-5 rounded-full border-2 border-white/30 bg-transparent checked:bg-gradient-to-r checked:from-blue-500 checked:to-purple-500 checked:border-transparent focus:ring-2 focus:ring-blue-500">
                    </div>
                  </div>

                  <div class="space-y-4">
                    <div class="relative h-28 rounded-xl shadow-2xl overflow-hidden group-hover:scale-[1.02] transition-all duration-300" :style="{ backgroundColor: color.hex }">
                      <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 bg-white/10 transition-opacity"></div>
                      <span class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/80 backdrop-blur-sm text-[10px] px-2 py-0.5 rounded-lg font-mono text-white border border-white/20">
                        {{ color.hex }}
                      </span>
                    </div>

                    <div class="space-y-4">
                      <div class="flex gap-2">
                        <input type="color" v-model="color.hex" @input="onColorChange(index, $event)" 
                               class="w-10 h-10 p-0 bg-transparent border-2 border-white/20 rounded-xl cursor-pointer hover:scale-110 transition-transform">
                        <input v-model="color.hex" class="flex-1 h-10 bg-black/40 backdrop-blur-sm border border-white/10 rounded-xl px-3 text-xs font-mono text-white" readonly />
                      </div>

                      <div>
                        <div class="flex justify-between mb-1.5">
                          <label class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Intensity</label>
                          <span class="text-xs font-bold text-white bg-white/10 px-2 py-0.5 rounded-full">{{ color.weight }}%</span>
                        </div>
                        <input type="range" :value="color.weight" @input="(e) => updateWeight(index, parseInt(e.target.value))" 
                               :disabled="!color.active" min="0" max="100"
                               class="w-full h-1.5 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-white">
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex flex-wrap gap-3 justify-center border-t border-white/10 pt-6">
                <button @click="randomizeColors" class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-full text-xs font-bold transition-all duration-300 hover:scale-105">
                  🎲 Randomize
                </button>
                <button @click="balanceWeights" class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-full text-xs font-bold transition-all duration-300 hover:scale-105">
                  ⚖️ Equalize
                </button>
                <button @click="resetColors" class="px-5 py-2 text-slate-400 hover:text-white text-xs font-medium transition-all">
                  ⟳ Reset
                </button>
              </div>
            </div>
          </div>

          <!-- Mixed Result - Enhanced -->
          <div class="relative bg-slate-900/30 backdrop-blur-2xl rounded-3xl border border-white/10 shadow-2xl p-6 overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-purple-600/10 to-transparent rounded-full blur-3xl"></div>
            <h2 class="text-xl font-bold text-white flex items-center gap-2 mb-6">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center shadow-lg">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
              </div>
              Alchemical Blend
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div class="space-y-6">
                <div class="relative h-64 rounded-2xl overflow-hidden bg-gradient-to-br from-slate-950 to-black border border-white/10 shadow-inner flex items-center justify-center">
                  <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/10 via-transparent to-transparent"></div>
                  
                  <div class="absolute w-56 h-56 rounded-full opacity-30 blur-xl animate-spin-slow"
                       :style="{ background: `conic-gradient(${colors[0].active ? colors[0].hex : 'transparent'}, ${colors[1].active ? colors[1].hex : 'transparent'}, ${colors[2].active ? colors[2].hex : 'transparent'}, transparent)` }"></div>
                  
                  <div class="relative w-36 h-36 rounded-full animate-float shadow-2xl transition-all duration-700 border-2 border-white/30"
                       :style="{ 
                         background: `radial-gradient(circle at 35% 35%, ${mixedColor.hex}, ${mixedColor.hex}cc, #000)`,
                         boxShadow: `0 0 80px ${mixedColor.hex}80`
                       }">
                  </div>

                  <div v-for="(color, i) in colors" :key="i" v-show="color.active"
                       class="absolute rounded-full border border-white/20 animate-orbit"
                       :style="{ width: 180 + i*40 + 'px', height: 180 + i*40 + 'px', animationDuration: 6 + i*2 + 's' }">
                    <div class="w-3 h-3 rounded-full absolute -top-2 left-1/2 -translate-x-1/2" :style="{ backgroundColor: color.hex, boxShadow: `0 0 20px ${color.hex}` }"></div>
                  </div>

                  <div class="absolute bottom-4 left-4 right-4 bg-black/70 backdrop-blur-xl p-3 rounded-xl border border-white/20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl border-2 border-white/30 shadow-lg transition-all duration-300" :style="{ backgroundColor: mixedColor.hex }"></div>
                      <div>
                        <div class="text-xs font-bold text-white uppercase tracking-wider">{{ mixedColor.name }}</div>
                        <div class="text-[10px] font-mono text-slate-300">{{ mixedColor.hex }}</div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-[10px] text-slate-400">Current Blend</div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4 bg-black/30 p-4 rounded-xl border border-white/10 backdrop-blur-sm">
                  <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM5.884 6.607a1 1 0 01-.226 1.396l-.867.65a1 1 0 11-1.2-1.6l.867-.65a1 1 0 011.42.204zM19 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM7 10a1 1 0 01-1 1H5a1 1 0 110-2h1a1 1 0 011 1zM11 15a1 1 0 10-2 0v1a1 1 0 102 0v-1zM17.116 13.393a1 1 0 01.226 1.396l-.867.65a1 1 0 11-1.2-1.6l.867-.65a1 1 0 011.42-.204zM12.894 6.223a1 1 0 011.396.226l.65.867a1 1 0 11-1.6 1.2l-.65-.867a1 1 0 01.204-1.42zM3.963 14.354a1 1 0 01.082 1.411l-.708.708a1 1 0 11-1.414-1.414l.708-.708a1 1 0 011.414.003z"></path></svg>
                    Influence Analysis
                  </h4>
                  <div v-for="(color, i) in colors" :key="i" v-show="color.active" class="space-y-1">
                    <div class="flex justify-between text-[11px]">
                      <span class="text-slate-300">Color {{ i+1 }}</span>
                      <span class="text-white font-bold">{{ color.weight }}%</span>
                    </div>
                    <div class="h-2 bg-slate-800 rounded-full overflow-hidden">
                      <div class="h-full transition-all duration-700 rounded-full" :style="{ width: color.weight + '%', backgroundColor: color.hex }"></div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <div class="grid grid-cols-2 gap-3">
                  <div class="bg-black/40 backdrop-blur-sm p-3 rounded-xl border border-white/10 text-center hover:border-white/30 transition-all">
                    <span class="text-[10px] text-slate-500 uppercase block mb-1">HEX Code</span>
                    <span class="text-sm font-mono font-bold text-white tracking-widest">{{ mixedColor.hex }}</span>
                  </div>
                  <div class="bg-black/40 backdrop-blur-sm p-3 rounded-xl border border-white/10 text-center hover:border-white/30 transition-all">
                    <span class="text-[10px] text-slate-500 uppercase block mb-1">RGB Value</span>
                    <span class="text-sm font-mono font-bold text-white">{{ mixedColor.rgb }}</span>
                  </div>
                </div>

                <div class="bg-gradient-to-br from-black/40 to-black/20 backdrop-blur-sm p-4 rounded-xl border border-white/10">
                  <span class="text-[10px] text-slate-500 uppercase block mb-2 text-center">HSL Spectrum</span>
                  <div class="flex items-center justify-between text-center">
                    <div class="flex-1"><div class="text-2xl font-bold text-white">{{ mixedColor.hue }}°</div><div class="text-[9px] text-slate-500 uppercase">Hue</div></div>
                    <div class="w-px h-10 bg-gradient-to-b from-transparent via-white/20 to-transparent"></div>
                    <div class="flex-1"><div class="text-2xl font-bold text-white">{{ mixedColor.saturation }}%</div><div class="text-[9px] text-slate-500 uppercase">Saturation</div></div>
                    <div class="w-px h-10 bg-gradient-to-b from-transparent via-white/20 to-transparent"></div>
                    <div class="flex-1"><div class="text-2xl font-bold text-white">{{ mixedColor.lightness }}%</div><div class="text-[9px] text-slate-500 uppercase">Lightness</div></div>
                  </div>
                </div>

                <button @click="saveMixedColor" class="group relative w-full py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl font-bold shadow-xl transition-all duration-300 transform hover:scale-[1.02] active:scale-95 overflow-hidden">
                  <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                  <span class="relative flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Save to Collection
                  </span>
                </button>

                <div class="p-3 bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-lg border border-blue-500/20 flex items-start gap-3">
                  <svg class="w-4 h-4 text-blue-400 mt-0.5 shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <p class="text-[10px] text-blue-300 leading-relaxed">
                    Luminance: <strong class="text-white">{{ mixedColor.luminance }}%</strong> · Accessibility: 
                    <span :class="mixedColor.accessibility === 'Good' ? 'text-green-400' : 'text-yellow-400'" class="font-bold">{{ mixedColor.accessibility }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar - Enhanced -->
        <aside class="space-y-6 sm:space-y-8">
          <div class="relative bg-slate-900/30 backdrop-blur-2xl rounded-3xl border border-white/10 p-6 overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-cyan-500/10 to-transparent rounded-full blur-2xl"></div>
            <h2 class="text-lg font-bold text-white flex items-center gap-2 mb-6">
              <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-500 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
              </div>
              Harmony Palettes
            </h2>
            <div class="space-y-5">
              <div v-for="palette in palettes" :key="palette.label" class="space-y-2">
                <span class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">{{ palette.label }}</span>
                <div class="flex h-12 rounded-xl overflow-hidden border border-white/10 shadow-lg">
                  <div v-for="(color, i) in palette.colors" :key="i" 
                       class="flex-1 cursor-pointer hover:opacity-90 transition-all duration-300 hover:scale-105" 
                       :style="{ backgroundColor: color }" @click="copyColor(color)"
                       :title="color"></div>
                </div>
              </div>
            </div>
          </div>

          <div class="relative bg-slate-900/30 backdrop-blur-2xl rounded-3xl border border-white/10 p-6">
            <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
              <div class="w-6 h-6 rounded-lg bg-gradient-to-br from-pink-500 to-rose-500 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              </div>
              Quick Pigments
            </h2>
            <div class="grid grid-cols-2 gap-2.5">
              <button v-for="s in colorSuggestions" :key="s.name" @click="applySuggestion(s)"
                      class="h-16 rounded-xl border border-white/10 transition-all duration-300 hover:scale-105 relative group overflow-hidden shadow-lg"
                      :style="{ backgroundColor: s.hex }">
                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity backdrop-blur-sm">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <span class="absolute bottom-1 left-1 text-[9px] font-bold text-white drop-shadow-md">{{ s.name }}</span>
              </button>
            </div>
          </div>
        </aside>
      </div>
    </main>

    <div v-if="notificationText" class="fixed bottom-6 right-6 z-50 animate-slide-up">
      <div class="bg-slate-900/90 backdrop-blur-xl border border-emerald-500/50 px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <span class="text-sm font-bold text-white">{{ notificationText }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const colors = ref([
  { id: 1, name: 'Ruby', hex: '#FF3636', rgb: '255, 54, 54', active: true, weight: 34 },
  { id: 2, name: 'Amber', hex: '#FFE436', rgb: '255, 228, 54', active: true, weight: 33 },
  { id: 3, name: 'Sapphire', hex: '#00C8FF', rgb: '0, 200, 255', active: true, weight: 33 }
])

const mixedColor = ref({
  name: 'Mixed Blend', hex: '#FF6B6B', rgb: '255, 107, 107', 
  hue: 0, saturation: 100, lightness: 71, 
  luminance: 65, accessibility: 'Good'
})

const colorSuggestions = [
  { name: 'Crimson', hex: '#DC2626' }, { name: 'Gold', hex: '#F59E0B' },
  { name: 'Emerald', hex: '#10B981' }, { name: 'Electric', hex: '#8B5CF6' },
  { name: 'Cyan', hex: '#06B6D4' }, { name: 'Fuchsia', hex: '#D946EF' }
]

const notificationText = ref('')

const palettes = computed(() => [
  { label: 'Monochromatic', colors: generateMonochromatic() },
  { label: 'Analogous', colors: generateAnalogous() },
  { label: 'Complementary', colors: generateComplementary() }
])

const getBorderClass = (i) => {
  const borders = ['border-red-500/50', 'border-amber-500/50', 'border-cyan-500/50']
  return borders[i]
}

const toggleCardActive = (index) => {
  colors.value[index].active = !colors.value[index].active
  balanceWeights()
}

const getColorName = (h, s, l) => {
  let tone = l < 25 ? "Deep " : l < 45 ? "Muted " : l > 75 ? "Pale " : ""
  if (s < 10) return tone + "Gray"
  let hueName = h < 15 || h >= 345 ? "Crimson" : h < 45 ? "Amber" : h < 75 ? "Gold" : h < 155 ? "Emerald" : h < 195 ? "Cyan" : h < 255 ? "Azure" : h < 300 ? "Violet" : "Magenta"
  return `${tone}${hueName}`
}

const rgbToHSL = (r, g, b) => {
  r /= 255; g /= 255; b /= 255
  const max = Math.max(r, g, b), min = Math.min(r, g, b)
  let h, s, l = (max + min) / 2
  if (max === min) h = s = 0
  else {
    const d = max - min
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min)
    if (max === r) h = (g - b) / d + (g < b ? 6 : 0)
    else if (max === g) h = (b - r) / d + 2
    else h = (r - g) / d + 4
    h /= 6
  }
  return { h: h * 360, s: s, l: l }
}

const hslToHex = (h, s, l) => {
  h /= 360; s /= 100; l /= 100
  const hue2rgb = (p, q, t) => {
    if (t < 0) t += 1; if (t > 1) t -= 1
    if (t < 1/6) return p + (q - p) * 6 * t
    if (t < 1/2) return q
    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6
    return p
  }
  const q = l < 0.5 ? l * (1 + s) : l + s - l * s
  const p = 2 * l - q
  const r = hue2rgb(p, q, h + 1/3), g = hue2rgb(p, q, h), b = hue2rgb(p, q, h - 1/3)
  const toHex = x => Math.round(x * 255).toString(16).padStart(2, '0')
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase()
}

const calculateMixedColor = () => {
  const active = colors.value.filter(c => c.active)
  if (!active.length) return
  let tr = 0, tg = 0, tb = 0, tw = 0
  active.forEach(c => {
    const [r, g, b] = c.rgb.split(',').map(Number)
    tr += r * c.weight; tg += g * c.weight; tb += b * c.weight; tw += c.weight
  })
  tw = tw || 1
  const r = Math.round(tr / tw), g = Math.round(tg / tw), b = Math.round(tb / tw)
  const hex = `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase()}`
  const hsl = rgbToHSL(r, g, b)
  mixedColor.value = {
    hex, rgb: `${r}, ${g}, ${b}`,
    hue: Math.round(hsl.h), saturation: Math.round(hsl.s * 100), lightness: Math.round(hsl.l * 100),
    luminance: Math.round((r*0.299 + g*0.587 + b*0.114) / 2.55),
    accessibility: (r+g+b)/3 > 128 ? 'Soft' : 'Excellent',
    name: getColorName(hsl.h, hsl.s*100, hsl.l*100)
  }
}

const updateWeight = (index, newVal) => {
  const color = colors.value[index]
  if (!color.active) return
  const others = colors.value.filter((c, i) => c.active && i !== index)
  if (!others.length) { color.weight = 100; return }
  color.weight = Math.max(0, Math.min(100, newVal))
  const remaining = 100 - color.weight
  const currentOthersSum = others.reduce((sum, c) => sum + c.weight, 0)
  if (currentOthersSum === 0) {
    others.forEach(c => c.weight = Math.floor(remaining / others.length))
  } else {
    let distributed = 0
    others.forEach((c, i) => {
      const w = i === others.length - 1 ? remaining - distributed : Math.round((c.weight / currentOthersSum) * remaining)
      c.weight = w
      distributed += w
    })
  }
}

const toggleActive = (index, isActive) => {
  colors.value[index].active = isActive
  balanceWeights()
}

const balanceWeights = () => {
  const active = colors.value.filter(c => c.active)
  if (!active.length) return
  const base = Math.floor(100 / active.length)
  let rem = 100 % active.length
  colors.value.forEach(c => {
    if (c.active) {
      c.weight = base + (rem > 0 ? 1 : 0)
      rem--
    }
  })
}

const randomizeColors = () => {
  colors.value.forEach(c => {
    if (c.active) {
      c.hex = '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0').toUpperCase()
      const [r, g, b] = [parseInt(c.hex.slice(1,3), 16), parseInt(c.hex.slice(3,5), 16), parseInt(c.hex.slice(5,7), 16)]
      c.rgb = `${r}, ${g}, ${b}`
    }
  })
}

const resetColors = () => {
  colors.value = [
    { id: 1, name: 'Ruby', hex: '#FF3636', rgb: '255, 54, 54', active: true, weight: 34 },
    { id: 2, name: 'Amber', hex: '#FFE436', rgb: '255, 228, 54', active: true, weight: 33 },
    { id: 3, name: 'Sapphire', hex: '#00C8FF', rgb: '0, 200, 255', active: true, weight: 33 }
  ]
  calculateMixedColor()
}

const generateMonochromatic = () => {
  const { h, s } = rgbToHSL(...mixedColor.value.rgb.split(',').map(Number))
  return [20, 40, 60, 80, 95].map(l => hslToHex(h, s*100, l))
}

const generateAnalogous = () => {
  const { h, s, l } = rgbToHSL(...mixedColor.value.rgb.split(',').map(Number))
  return [-30, -15, 0, 15, 30].map(offset => hslToHex((h + offset + 360) % 360, s*100, l*100))
}

const generateComplementary = () => {
  const { h, s, l } = rgbToHSL(...mixedColor.value.rgb.split(',').map(Number))
  return [h, (h + 180) % 360].map(hue => hslToHex(hue, s*100, l*100))
}

const onColorChange = (i, e) => {
  const hex = e.target.value.toUpperCase()
  const [r, g, b] = [parseInt(hex.slice(1,3), 16), parseInt(hex.slice(3,5), 16), parseInt(hex.slice(5,7), 16)]
  colors.value[i].rgb = `${r}, ${g}, ${b}`
  calculateMixedColor()
}

const applySuggestion = (s) => {
  const target = colors.value.find(c => !c.active) || colors.value[0]
  target.hex = s.hex
  if (!target.active) { target.active = true; balanceWeights() }
  const [r, g, b] = [parseInt(s.hex.slice(1,3), 16), parseInt(s.hex.slice(3,5), 16), parseInt(s.hex.slice(5,7), 16)]
  target.rgb = `${r}, ${g}, ${b}`
}

const saveMixedColor = () => {
  notificationText.value = '🔒 Login required to save colors!'
  setTimeout(() => {
    router.push('/Landing/logIn')
  }, 1500)
}

const navigateToLogin = () => router.push('/Landing/logIn')

const copyColor = (hex) => {
  navigator.clipboard.writeText(hex)
  notificationText.value = '📋 Color copied to clipboard!'
  setTimeout(() => notificationText.value = '', 2000)
}

watch(colors, calculateMixedColor, { deep: true })
onMounted(() => { balanceWeights(); calculateMixedColor() })
</script>

<style scoped>
@keyframes spin-slow { 
  from { transform: rotate(0deg); } 
  to { transform: rotate(360deg); } 
}

@keyframes float { 
  0%, 100% { transform: translateY(0px) scale(1); } 
  50% { transform: translateY(-12px) scale(1.05); } 
}

@keyframes orbit { 
  from { transform: rotate(0deg) translateX(45px) rotate(0deg); } 
  to { transform: rotate(360deg) translateX(45px) rotate(-360deg); } 
}

@keyframes gradient-x {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@keyframes slide-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-spin-slow { 
  animation: spin-slow 20s linear infinite; 
}

.animate-float { 
  animation: float 5s ease-in-out infinite; 
}

.animate-orbit { 
  animation: orbit linear infinite; 
}

.animate-gradient-x {
  background-size: 200% 200%;
  animation: gradient-x 3s ease infinite;
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 18px;
  width: 18px;
  border-radius: 50%;
  background: white;
  cursor: pointer;
  box-shadow: 0 0 15px rgba(255, 255, 255, 0.6);
  border: 2px solid rgba(255,255,255,0.3);
}

input[type="range"]::-webkit-slider-thumb:hover {
  transform: scale(1.2);
}

input[type="color"] {
  -webkit-appearance: none;
  border: none;
  cursor: pointer;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: 2px solid rgba(255,255,255,0.2);
  border-radius: 12px;
}

.delay-1000 {
  animation-delay: 1s;
}
</style>