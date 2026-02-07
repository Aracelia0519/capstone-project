<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 to-slate-950 p-4 sm:p-6 text-slate-200">
    <header class="text-center mb-8 sm:mb-12">
      <div class="inline-flex items-center gap-3 mb-4">
        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center animate-pulse shadow-[0_0_20px_rgba(168,85,247,0.4)]">
          <Beaker class="w-6 h-6 text-white" />
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-300 to-rose-300">
          Color Mixing Lab
        </h1>
      </div>
      <p class="text-gray-400 text-lg">Create and explore color combinations with advanced visualization tools</p>
    </header>

    <main class="max-w-6xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
          
          <Card class="bg-slate-900/40 border-slate-700/50 backdrop-blur-xl border">
            <CardHeader>
              <CardTitle class="text-xl sm:text-2xl font-bold text-white flex items-center gap-2">
                <Settings2 class="w-5 h-5 text-purple-400" />
                Color Mixer Controls
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
                <div v-for="(color, index) in colors" :key="color.id" 
                     :class="['group relative bg-slate-800/30 rounded-xl p-4 border-2 transition-all duration-300', 
                              color.active ? getBorderClass(index) : 'border-slate-700/50 opacity-60']">
                  
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-white text-lg">Color {{ index + 1 }}</h3>
                    <Switch :checked="color.active" @update:checked="color.active = $event" />
                  </div>

                  <div class="space-y-4">
                    <div class="h-32 rounded-lg shadow-inner relative overflow-hidden group-hover:scale-[1.02] transition-transform duration-300" 
                         :style="{ backgroundColor: color.hex }">
                      <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                      <Badge variant="outline" class="absolute bottom-2 left-1/2 -translate-x-1/2 bg-black/60 text-xs font-mono border-white/10 text-white">
                        {{ color.hex }}
                      </Badge>
                    </div>

                    <div class="space-y-4">
                      <div>
                        <Label class="text-xs text-slate-400 mb-2 block uppercase tracking-tighter">Hex Selection</Label>
                        <div class="flex gap-2">
                          <Input type="color" v-model="color.hex" @input="onColorChange(index, $event)" class="w-12 h-10 p-1 bg-slate-950 border-slate-700 cursor-pointer" />
                          <Input v-model="color.hex" class="h-10 bg-slate-950/50 border-slate-700 text-xs font-mono" readonly />
                        </div>
                      </div>

                      <div>
                        <div class="flex justify-between mb-2">
                          <Label class="text-xs text-slate-400 uppercase tracking-tighter">Strength</Label>
                          <span class="text-xs font-bold text-white">{{ color.weight }}%</span>
                        </div>
                        <Slider 
                          :model-value="[color.weight]" 
                          @update:model-value="(v) => color.weight = v[0]" 
                          :max="100" 
                          :step="1" 
                          :disabled="!color.active" 
                          :class="getSliderClass(index)"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex flex-wrap gap-3 justify-center border-t border-slate-700/50 pt-6">
                <Button @click="randomizeColors" variant="secondary" class="rounded-full bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border-purple-500/30">
                  <RefreshCcw class="w-4 h-4 mr-2" /> Randomize
                </Button>
                <Button @click="balanceWeights" variant="secondary" class="rounded-full bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 border-blue-500/30">
                  <Scale class="w-4 h-4 mr-2" /> Equalize
                </Button>
                <Button @click="resetColors" variant="ghost" class="rounded-full text-slate-400 hover:text-white">
                  <Undo2 class="w-4 h-4 mr-2" /> Reset
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-slate-900/40 border-slate-700/50 backdrop-blur-xl overflow-hidden border">
            <CardHeader>
              <CardTitle class="text-xl font-bold text-white flex items-center gap-2">
                <Sparkles class="w-5 h-5 text-pink-400" />
                Mixed Color Result
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-6">
                  <div class="relative h-64 rounded-2xl overflow-hidden bg-slate-950 border border-white/5 shadow-2xl">
                    <div class="absolute inset-0 opacity-10 bg-[size:20px_20px] bg-[linear-gradient(to_right,#808080_1px,transparent_1px),linear-gradient(to_bottom,#808080_1px,transparent_1px)]"></div>
                    
                    <div class="absolute inset-0 flex items-center justify-center">
                      <div class="absolute w-56 h-56 rounded-full opacity-20 blur-xl animate-spin-slow"
                           :style="{ background: `conic-gradient(${colors[0].active ? colors[0].hex : 'transparent'}, ${colors[1].active ? colors[1].hex : 'transparent'}, ${colors[2].active ? colors[2].hex : 'transparent'}, transparent)` }"></div>
                      
                      <div class="relative w-40 h-40 rounded-full animate-float shadow-2xl transition-all duration-700"
                           :style="{ 
                             background: `radial-gradient(circle at 30% 30%, ${mixedColor.hex}, ${mixedColor.hex}99, #000)`,
                             boxShadow: `0 0 60px ${mixedColor.hex}40`
                           }">
                        <div class="absolute inset-0 rounded-full border border-white/20"></div>
                      </div>

                      <div v-for="(color, i) in colors" :key="i" v-show="color.active"
                           class="absolute rounded-full border border-white/10 animate-orbit"
                           :style="{ 
                             width: 180 + i*40 + 'px', 
                             height: 180 + i*40 + 'px',
                             animationDuration: 5 + i*2 + 's' 
                           }">
                        <div class="w-3 h-3 rounded-full absolute -top-1.5 left-1/2" :style="{ backgroundColor: color.hex, boxShadow: `0 0 15px ${color.hex}` }"></div>
                      </div>
                    </div>

                    <div class="absolute bottom-4 left-4 right-4 bg-black/60 backdrop-blur-md p-3 rounded-xl border border-white/10 flex items-center justify-between">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg shadow-lg border border-white/20" :style="{ backgroundColor: mixedColor.hex }"></div>
                        <div>
                          <div class="text-xs font-bold text-white uppercase">{{ mixedColor.name }}</div>
                          <div class="text-[10px] font-mono text-slate-400">{{ mixedColor.hex }}</div>
                        </div>
                      </div>
                      <Badge class="bg-emerald-500/10 text-emerald-400 border-emerald-500/20 text-[10px]">Active</Badge>
                    </div>
                  </div>

                  <div class="space-y-4 bg-black/20 p-4 rounded-xl border border-white/5">
                    <h4 class="text-xs font-bold text-slate-500 uppercase tracking-widest flex items-center gap-2">
                      <Zap class="w-3 h-3" /> Influence Analysis
                    </h4>
                    <div v-for="(color, i) in colors" :key="i" v-show="color.active" class="space-y-1">
                      <div class="flex justify-between text-[11px]">
                        <span class="text-slate-300">Color {{ i+1 }}</span>
                        <span class="text-white">{{ color.weight }}%</span>
                      </div>
                      <Progress :model-value="color.weight" :class="getProgressBarTheme(i)" class="h-1.5 bg-slate-800" />
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="grid grid-cols-2 gap-3">
                    <div class="bg-black/40 p-3 rounded-xl border border-white/5 text-center">
                      <span class="text-[10px] text-slate-500 uppercase block mb-1">HEX</span>
                      <span class="text-sm font-mono font-bold text-white tracking-widest">{{ mixedColor.hex }}</span>
                    </div>
                    <div class="bg-black/40 p-3 rounded-xl border border-white/5 text-center">
                      <span class="text-[10px] text-slate-500 uppercase block mb-1">RGB</span>
                      <span class="text-sm font-mono font-bold text-white">{{ mixedColor.rgb }}</span>
                    </div>
                  </div>

                  <div class="bg-black/40 p-4 rounded-xl border border-white/5">
                    <span class="text-[10px] text-slate-500 uppercase block mb-2">HSL Spectrum</span>
                    <div class="flex items-center justify-between text-center">
                      <div class="flex-1"><div class="text-lg font-bold text-white">{{ mixedColor.hue }}°</div><div class="text-[9px] text-slate-500 uppercase">Hue</div></div>
                      <Separator orientation="vertical" class="h-8 bg-slate-700" />
                      <div class="flex-1"><div class="text-lg font-bold text-white">{{ mixedColor.saturation }}%</div><div class="text-[9px] text-slate-500 uppercase">Sat</div></div>
                      <Separator orientation="vertical" class="h-8 bg-slate-700" />
                      <div class="flex-1"><div class="text-lg font-bold text-white">{{ mixedColor.lightness }}%</div><div class="text-[9px] text-slate-500 uppercase">Lum</div></div>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-800/40 p-3 rounded-xl border border-white/5 flex items-center justify-between">
                      <span class="text-[10px] text-slate-400">Temp</span>
                      <Badge variant="outline" :class="mixedColor.temperature.includes('Warm') ? 'text-orange-400 border-orange-400/20' : 'text-blue-400 border-blue-400/20'">
                        {{ mixedColor.temperature }}
                      </Badge>
                    </div>
                    <div class="bg-slate-800/40 p-3 rounded-xl border border-white/5 flex items-center justify-between">
                      <span class="text-[10px] text-slate-400">Family</span>
                      <span class="text-xs font-bold text-white">{{ mixedColor.family }}</span>
                    </div>
                  </div>

                  <Button @click="saveMixedColor" :disabled="isSaveDisabled" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white shadow-lg">
                    <Save class="w-4 h-4 mr-2" /> Save to Collection
                  </Button>

                  <div class="p-3 bg-blue-500/5 rounded-lg border border-blue-500/20 flex items-start gap-3">
                    <Info class="w-4 h-4 text-blue-400 mt-0.5 shrink-0" />
                    <p class="text-[10px] text-blue-300 leading-relaxed">
                      Luminance: <strong>{{ mixedColor.luminance }}%</strong>. Readability: 
                      <span :class="mixedColor.accessibility === 'Good' ? 'text-green-400' : 'text-red-400'">{{ mixedColor.accessibility }} ({{ mixedColor.contrast }}:1)</span>.
                    </p>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <aside class="space-y-6 sm:space-y-8">
          <Card class="bg-slate-900/40 border-slate-700/50 backdrop-blur-xl border">
            <CardHeader><CardTitle class="text-lg font-bold text-white">Suggestions</CardTitle></CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-2">
                <button v-for="s in colorSuggestions" :key="s.name" @click="applySuggestion(s)"
                        class="h-16 rounded-lg border border-white/10 transition-all hover:scale-105 relative group overflow-hidden"
                        :style="{ backgroundColor: s.hex }">
                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                    <Plus class="w-5 h-5 text-white" />
                  </div>
                  <span class="absolute bottom-1 left-1 text-[9px] font-bold text-white drop-shadow-md">{{ s.name }}</span>
                </button>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-slate-900/40 border-slate-700/50 backdrop-blur-xl border">
            <CardHeader>
              <CardTitle class="text-lg font-bold text-white flex items-center gap-2">
                <Palette class="w-5 h-5 text-cyan-400" /> Harmony Palettes
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-5">
              <div v-for="palette in palettes" :key="palette.label" class="space-y-2">
                <span class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">{{ palette.label }}</span>
                <div class="flex h-10 rounded-lg overflow-hidden border border-white/5">
                  <TooltipProvider v-for="(color, i) in palette.colors" :key="i">
                    <Tooltip>
                      <TooltipTrigger as-child>
                        <div class="flex-1 cursor-pointer hover:opacity-80 transition-opacity" 
                             :style="{ backgroundColor: color }" @click="copyColor(color)"></div>
                      </TooltipTrigger>
                      <TooltipContent class="bg-slate-950 border-slate-700 text-[10px] font-mono">{{ color }}</TooltipContent>
                    </Tooltip>
                  </TooltipProvider>
                </div>
              </div>
            </CardContent>
          </Card>

          <div class="bg-gradient-to-br from-purple-900/20 to-pink-900/20 border border-purple-500/30 p-6 rounded-2xl space-y-4">
            <div class="flex items-center gap-3">
              <Lightbulb class="w-5 h-5 text-purple-400" />
              <h4 class="font-bold text-purple-100">Mixing Insights</h4>
            </div>
            <p class="text-sm text-purple-200/70 leading-relaxed italic">"{{ tips[currentTip] }}"</p>
            <Button @click="nextTip" variant="link" size="sm" class="p-0 text-purple-400 hover:text-purple-300 h-auto">
              Show Another Tip <ChevronRight class="w-4 h-4 ml-1" />
            </Button>
          </div>
        </aside>
      </div>
    </main>

    <div v-if="showCopyNotification || showSaveNotification" 
         class="fixed bottom-6 right-6 z-50 animate-in slide-in-from-bottom-5 fade-in duration-300">
      <div class="bg-slate-900 border border-emerald-500/30 px-6 py-3 rounded-2xl shadow-2xl flex items-center gap-3">
        <CheckCircle2 class="w-5 h-5 text-emerald-500" />
        <span class="text-sm font-bold text-white">{{ notificationText }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Switch } from '@/components/ui/switch'
import { Slider } from '@/components/ui/slider'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Separator } from '@/components/ui/separator'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { 
  Beaker, Settings2, Sparkles, RefreshCcw, Scale, Undo2, 
  Plus, Save, Info, Lightbulb, ChevronRight, Palette, CheckCircle2, Zap 
} from 'lucide-vue-next'
import api from '@/utils/axios'

// --- State ---
const colors = ref([
  { id: 1, name: 'Vibrant Red', hex: '#FF3636', rgb: '255, 54, 54', active: true, weight: 40 },
  { id: 2, name: 'Sunshine Yellow', hex: '#FFE436', rgb: '255, 228, 54', active: true, weight: 35 },
  { id: 3, name: 'Ocean Blue', hex: '#00C8FF', rgb: '0, 200, 255', active: true, weight: 25 }
])

const mixedColor = ref({
  name: 'Mixed Blend', hex: '#FF6B6B', rgb: '255, 107, 107', 
  hsl: '0, 100%, 71%', hue: 0, saturation: 100, lightness: 71, 
  contrast: 4.5, luminance: 65, temperature: 'Warm', 
  accessibility: 'Good', family: 'Red', stability: 'Stable',
  frequency: '432', quantumState: 'Mixed'
})

const colorSuggestions = [
  { name: 'Vibrant Red', hex: '#FF3636' }, { name: 'Sunshine Yellow', hex: '#FFE436' },
  { name: 'Ocean Blue', hex: '#00C8FF' }, { name: 'Electric Purple', hex: '#9E00F6' },
  { name: 'Mint Green', hex: '#00FF9D' }, { name: 'Coral Pink', hex: '#FF7F83' }
]

const tips = [
  "Color blending uses weighted mixing for accurate results",
  "Adjust Strength sliders to control color dominance",
  "Higher saturation creates more vibrant results",
  "Try complementary colors for maximum impact",
  "Analogous colors create the smoothest transitions"
]

const currentTip = ref(0)
const showCopyNotification = ref(false)
const showSaveNotification = ref(false)
const notificationText = ref('')

// --- Computeds ---
const activeColorsCount = computed(() => colors.value.filter(c => c.active).length)
const isSaveDisabled = computed(() => activeColorsCount.value === 0)

const palettes = computed(() => [
  { label: 'Monochromatic', colors: generateMonochromatic() },
  { label: 'Analogous', colors: generateAnalogous() },
  { label: 'Complementary', colors: generateComplementary() }
])

// --- Helpers ---
const getBorderClass = (i) => ['border-purple-500', 'border-pink-500', 'border-cyan-500'][i]
const getSliderClass = (i) => ['[&>span:last-child>span]:bg-purple-500', '[&>span:last-child>span]:bg-pink-500', '[&>span:last-child>span]:bg-cyan-500'][i]
const getProgressBarTheme = (i) => ['[&>div]:bg-purple-500', '[&>div]:bg-pink-500', '[&>div]:bg-cyan-500'][i]

// --- Proper Naming Convention Logic ---
const getColorName = (h, s, l) => {
  let tone = "";
  if (l < 25) tone = "Deep ";
  else if (l < 45) tone = "Muted ";
  else if (l > 75) tone = "Pale ";
  else if (l > 85) tone = "Bright ";

  if (s < 10) return tone + "Gray";
  if (s < 30) tone = "Soft ";

  let hueName = "";
  if (h < 15 || h >= 345) hueName = "Crimson";
  else if (h < 45) hueName = "Amber";
  else if (h < 75) hueName = "Gold";
  else if (h < 155) hueName = "Emerald";
  else if (h < 195) hueName = "Cyan";
  else if (h < 255) hueName = "Azure";
  else if (h < 300) hueName = "Violet";
  else hueName = "Magenta";

  return `${tone}${hueName} Mix`;
}

// --- Logic ---
const rgbToHSL = (r, g, b) => {
  r /= 255; g /= 255; b /= 255;
  const max = Math.max(r, g, b), min = Math.min(r, g, b);
  let h, s, l = (max + min) / 2;
  if (max === min) h = s = 0;
  else {
    const d = max - min;
    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
    if (max === r) h = (g - b) / d + (g < b ? 6 : 0);
    else if (max === g) h = (b - r) / d + 2;
    else h = (r - g) / d + 4;
    h /= 6;
  }
  return { h: h * 360, s: s, l: l };
}

const hslToHex = (h, s, l) => {
  h /= 360; s /= 100; l /= 100;
  let r, g, b;
  const hue2rgb = (p, q, t) => {
    if (t < 0) t += 1; if (t > 1) t -= 1;
    if (t < 1/6) return p + (q - p) * 6 * t;
    if (t < 1/2) return q;
    if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
    return p;
  };
  const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
  const p = 2 * l - q;
  r = hue2rgb(p, q, h + 1/3); g = hue2rgb(p, q, h); b = hue2rgb(p, q, h - 1/3);
  const toHex = x => Math.round(x * 255).toString(16).padStart(2, '0');
  return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase();
}

const calculateMixedColor = () => {
  const active = colors.value.filter(c => c.active);
  if (!active.length) return;

  let tr = 0, tg = 0, tb = 0, tw = 0;
  active.forEach(c => {
    const [r, g, b] = c.rgb.split(',').map(Number);
    tr += r * c.weight; tg += g * c.weight; tb += b * c.weight; tw += c.weight;
  });

  const r = Math.round(tr / tw), g = Math.round(tg / tw), b = Math.round(tb / tw);
  const hex = `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase()}`;
  const hsl = rgbToHSL(r, g, b);

  const hVal = Math.round(hsl.h);
  const sVal = Math.round(hsl.s * 100);
  const lVal = Math.round(hsl.l * 100);

  mixedColor.value = {
    hex, rgb: `${r}, ${g}, ${b}`,
    hue: hVal, saturation: sVal, lightness: lVal,
    hsl: `${hVal}, ${sVal}%, ${lVal}%`,
    luminance: Math.round((r*0.299 + g*0.587 + b*0.114) / 2.55),
    temperature: hsl.h < 60 || hsl.h > 300 ? 'Warm' : 'Cool',
    family: (hsl.h < 30 || hsl.h > 330) ? 'Red' : (hsl.h < 90 ? 'Yellow' : (hsl.h < 150 ? 'Green' : (hsl.h < 210 ? 'Cyan' : (hsl.h < 270 ? 'Blue' : 'Purple')))),
    accessibility: (r+g+b)/3 > 128 ? 'Poor' : 'Good',
    contrast: Math.min(100, Math.round(((r*299 + g*587 + b*114)/1000) * 10) / 10),
    stability: hsl.s > 0.6 ? 'Dynamic' : 'Stable',
    frequency: (432 + Math.round(hsl.s * 50)).toString(),
    quantumState: active.length + ' Color Blend',
    name: getColorName(hVal, sVal, lVal)
  };
}

const generateMonochromatic = () => {
  const { h, s } = rgbToHSL(...mixedColor.value.rgb.split(',').map(Number));
  return [20, 40, 60, 80, 95].map(l => hslToHex(h, s*100, l));
}

const generateAnalogous = () => {
  const { h, s, l } = rgbToHSL(...mixedColor.value.rgb.split(',').map(Number));
  return [-30, -15, 0, 15, 30].map(offset => hslToHex((h + offset + 360) % 360, s*100, l*100));
}

const generateComplementary = () => {
  const { h, s, l } = rgbToHSL(...mixedColor.value.rgb.split(',').map(Number));
  return [h, (h + 180) % 360].map(hue => hslToHex(hue, s*100, l*100));
}

// --- Methods ---
const onColorChange = (i, e) => {
  const hex = e.target.value.toUpperCase();
  const r = parseInt(hex.slice(1,3), 16), g = parseInt(hex.slice(3,5), 16), b = parseInt(hex.slice(5,7), 16);
  colors.value[i].rgb = `${r}, ${g}, ${b}`;
  calculateMixedColor();
}

const randomizeColors = () => {
  colors.value.forEach(c => {
    c.hex = '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0').toUpperCase();
    const r = parseInt(c.hex.slice(1,3), 16), g = parseInt(c.hex.slice(3,5), 16), b = parseInt(c.hex.slice(5,7), 16);
    c.rgb = `${r}, ${g}, ${b}`;
    c.active = true;
  });
}

const balanceWeights = () => colors.value.forEach(c => c.weight = 33)
const resetColors = () => location.reload()

const copyColor = (hex) => {
  navigator.clipboard.writeText(hex);
  notificationText.value = 'Color Copied!';
  showCopyNotification.value = true;
  setTimeout(() => showCopyNotification.value = false, 2000);
}

const saveMixedColor = async () => {
  try {
    const payload = {
      name: mixedColor.value.name,
      hex: mixedColor.value.hex,
      rgb: mixedColor.value.rgb,
      hsl: mixedColor.value.hsl,
      hue: mixedColor.value.hue,
      saturation: mixedColor.value.saturation,
      lightness: mixedColor.value.lightness,
      contrast: mixedColor.value.contrast,
      luminance: mixedColor.value.luminance,
      temperature: mixedColor.value.temperature,
      accessibility: mixedColor.value.accessibility,
      family: mixedColor.value.family,
      stability: mixedColor.value.stability,
      frequency: mixedColor.value.frequency,
      quantumState: mixedColor.value.quantumState
    };

    await api.post('/client/save-color', payload);
    notificationText.value = 'Saved to Collection!';
    showSaveNotification.value = true;
    setTimeout(() => showSaveNotification.value = false, 2000);
  } catch (error) {
    console.error('Validation Error Details:', error.response?.data?.errors);
    notificationText.value = 'Save Failed - Check Console';
    showSaveNotification.value = true;
    setTimeout(() => showSaveNotification.value = false, 2000);
  }
}

const applySuggestion = (s) => {
  const target = colors.value.find(c => !c.active) || colors.value[0];
  target.hex = s.hex;
  target.active = true;
  const r = parseInt(s.hex.slice(1,3), 16), g = parseInt(s.hex.slice(3,5), 16), b = parseInt(s.hex.slice(5,7), 16);
  target.rgb = `${r}, ${g}, ${b}`;
}

const nextTip = () => currentTip.value = (currentTip.value + 1) % tips.length

watch(colors, calculateMixedColor, { deep: true })
onMounted(calculateMixedColor)
</script>

<style scoped>
@keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes float { 0%, 100% { transform: translateY(0px) scale(1); } 50% { transform: translateY(-10px) scale(1.05); } }
@keyframes orbit { from { transform: rotate(0deg) translateX(40px) rotate(0deg); } to { transform: rotate(360deg) translateX(40px) rotate(-360deg); } }

.animate-spin-slow { animation: spin-slow 20s linear infinite; }
.animate-float { animation: float 4s ease-in-out infinite; }
.animate-orbit { animation: orbit linear infinite; }

::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: rgba(30, 41, 59, 0.3); border-radius: 4px; }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #8b5cf6, #ec4899); border-radius: 4px; }
</style>