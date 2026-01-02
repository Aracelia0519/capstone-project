<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-100 p-4 md:p-6">
    <!-- Header Section -->
    <div class="max-w-7xl mx-auto mb-8 md:mb-12">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="flex items-center space-x-3 mb-2">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
              </svg>
            </div>
            <div>
              <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                Virtual Paint Color Mixing System
              </h1>
              <p class="text-gray-400 text-sm md:text-base">Preview Mode - Experience the power of color visualization</p>
            </div>
          </div>
          
          <!-- Restrictions Info -->
          <div class="mt-4 p-4 bg-gray-800/50 backdrop-blur-sm rounded-xl border border-gray-700/50">
            <div class="flex flex-wrap items-center gap-4">
              <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm text-gray-300">Preview Only</span>
              </div>
              <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
                <span class="text-sm text-gray-300">No Saving</span>
              </div>
              <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                <span class="text-sm text-gray-300">No Account Access</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Login to Save Button -->
        <button
          @click="navigateToLogin"
          class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-2xl transition-all duration-300 hover:scale-105 hover:from-blue-700 hover:to-purple-700 flex items-center justify-center space-x-2 group self-start md:self-center"
        >
          <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
          </svg>
          <span>Login to Save Color</span>
        </button>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
      <!-- Left Column: Color Mixing Interface -->
      <div class="lg:col-span-2 space-y-6 md:space-y-8">
        <!-- Color Mixing Panel -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/50 shadow-2xl">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-white flex items-center space-x-2">
              <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
              </svg>
              <span>Color Mixing Laboratory</span>
            </h2>
            <span class="px-3 py-1 bg-blue-900/30 text-blue-400 text-sm rounded-full border border-blue-800/50">
              Real-time Preview
            </span>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Color Pickers -->
            <div class="space-y-4">
              <div class="space-y-2">
                <label class="text-gray-300 font-medium">Primary Color</label>
                <div class="relative">
                  <input 
                    type="color" 
                    v-model="primaryColor" 
                    class="w-full h-12 rounded-lg cursor-pointer border-2 border-gray-700"
                  >
                  <div class="absolute inset-0 rounded-lg pointer-events-none" :style="{ backgroundColor: primaryColor }"></div>
                </div>
                <div class="flex justify-between text-sm text-gray-400">
                  <span>Hex: {{ primaryColor }}</span>
                  <span>RGB: {{ hexToRgb(primaryColor) }}</span>
                </div>
              </div>
              
              <div class="space-y-2">
                <label class="text-gray-300 font-medium">Secondary Color</label>
                <div class="relative">
                  <input 
                    type="color" 
                    v-model="secondaryColor" 
                    class="w-full h-12 rounded-lg cursor-pointer border-2 border-gray-700"
                  >
                  <div class="absolute inset-0 rounded-lg pointer-events-none" :style="{ backgroundColor: secondaryColor }"></div>
                </div>
                <div class="flex justify-between text-sm text-gray-400">
                  <span>Hex: {{ secondaryColor }}</span>
                  <span>RGB: {{ hexToRgb(secondaryColor) }}</span>
                </div>
              </div>
            </div>
            
            <!-- Mix Controls -->
            <div class="space-y-4">
              <div class="space-y-2">
                <label class="text-gray-300 font-medium flex justify-between">
                  <span>Mix Ratio</span>
                  <span class="text-blue-400">{{ mixRatio }}% Primary</span>
                </label>
                <input 
                  type="range" 
                  v-model="mixRatio" 
                  min="0" 
                  max="100" 
                  step="1"
                  class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-blue-500"
                >
                <div class="flex justify-between text-sm text-gray-400">
                  <span>0% (Pure Secondary)</span>
                  <span>100% (Pure Primary)</span>
                </div>
              </div>
              
              <div class="space-y-2">
                <label class="text-gray-300 font-medium">Brightness</label>
                <input 
                  type="range" 
                  v-model="brightness" 
                  min="0" 
                  max="100" 
                  step="1"
                  class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-4 [&::-webkit-slider-thumb]:w-4 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-purple-500"
                >
              </div>
              
              <!-- Mixed Color Display -->
              <div class="mt-6 p-4 bg-gray-900/50 rounded-xl">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-semibold text-gray-300">Mixed Color Result</span>
                  <button 
                    @click="resetColors"
                    class="px-3 py-1 text-sm bg-gray-700 hover:bg-gray-600 rounded-lg transition-colors"
                  >
                    Reset
                  </button>
                </div>
                <div 
                  class="h-20 rounded-lg mb-3 border-2 border-gray-700 transition-all duration-300"
                  :style="{ backgroundColor: mixedColor }"
                ></div>
                <div class="text-sm text-gray-400">
                  <div class="grid grid-cols-2 gap-2">
                    <div>Hex: <span class="text-white">{{ mixedColor }}</span></div>
                    <div>RGB: <span class="text-white">{{ hexToRgb(mixedColor) }}</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Visualization Panel -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/50 shadow-2xl">
          <h2 class="text-xl md:text-2xl font-bold text-white mb-6 flex items-center space-x-2">
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span>Wall Visualization</span>
          </h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Sample Wall -->
            <div class="relative overflow-hidden rounded-xl bg-gray-900 p-6">
              <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background: linear-gradient(45deg, transparent 25%, rgba(255,255,255,0.05) 25%, rgba(255,255,255,0.05) 50%, transparent 50%, transparent 75%, rgba(255,255,255,0.05) 75%)"></div>
              </div>
              <div class="relative">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-semibold text-gray-300">Modern Living Room</h3>
                  <span class="px-2 py-1 bg-gray-800 text-gray-400 text-xs rounded">Sample</span>
                </div>
                <div class="grid grid-cols-3 gap-2 mb-4">
                  <div 
                    v-for="(wall, index) in walls" 
                    :key="index"
                    class="h-24 rounded-lg transition-all duration-500 border-2 border-gray-700"
                    :style="{ backgroundColor: wall === 'accent' ? mixedColor : '#1f2937' }"
                  ></div>
                </div>
                <div class="flex items-center space-x-2 text-sm text-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                  </svg>
                  <span>Accent wall shows mixed color</span>
                </div>
              </div>
            </div>
            
            <!-- House Visualization -->
            <div class="relative overflow-hidden rounded-xl bg-gray-900 p-6">
              <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0" style="background: radial-gradient(circle at 30% 30%, rgba(255,255,255,0.1) 0%, transparent 50%)"></div>
              </div>
              <div class="relative">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-semibold text-gray-300">House Exterior</h3>
                  <span class="px-2 py-1 bg-gray-800 text-gray-400 text-xs rounded">Preview</span>
                </div>
                <div class="relative h-40">
                  <!-- Simple house illustration -->
                  <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-32 h-32" :style="{ backgroundColor: mixedColor }"></div>
                  <div class="absolute bottom-32 left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-[60px] border-r-[60px] border-b-[40px] border-transparent" :style="{ borderBottomColor: mixedColor }"></div>
                  <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-16 h-24 bg-gray-800 rounded"></div>
                </div>
                <div class="mt-4 text-sm text-gray-400">
                  <p>Exterior paint visualization using your mixed color</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Info & Controls -->
      <div class="space-y-6 md:space-y-8">
        <!-- Preview Info -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/50 shadow-2xl">
          <h2 class="text-xl font-bold text-white mb-4 flex items-center space-x-2">
            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>Preview Information</span>
          </h2>
          
          <div class="space-y-4">
            <div class="p-4 bg-gray-900/50 rounded-lg">
              <div class="flex items-center space-x-2 mb-2">
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span class="font-medium text-gray-300">What You Can Do</span>
              </div>
              <ul class="space-y-2 text-sm text-gray-400">
                <li class="flex items-start space-x-2">
                  <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span>Mix unlimited color combinations</span>
                </li>
                <li class="flex items-start space-x-2">
                  <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span>Visualize on sample walls and houses</span>
                </li>
                <li class="flex items-start space-x-2">
                  <svg class="w-4 h-4 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span>Adjust brightness and mix ratios</span>
                </li>
              </ul>
            </div>
            
            <div class="p-4 bg-gray-900/50 rounded-lg">
              <div class="flex items-center space-x-2 mb-2">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                </svg>
                <span class="font-medium text-gray-300">Preview Restrictions</span>
              </div>
              <ul class="space-y-2 text-sm text-gray-400">
                <li class="flex items-start space-x-2">
                  <svg class="w-4 h-4 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  <span>No color saving or exporting</span>
                </li>
                <li class="flex items-start space-x-2">
                  <svg class="w-4 h-4 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  <span>No access to saved palettes</span>
                </li>
                <li class="flex items-start space-x-2">
                  <svg class="w-4 h-4 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                  <span>No backend data storage</span>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Quick Color Presets -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700/50 shadow-2xl">
          <h2 class="text-xl font-bold text-white mb-4">Popular Color Presets</h2>
          <div class="grid grid-cols-3 gap-3">
            <button
              v-for="preset in colorPresets"
              :key="preset.name"
              @click="applyPreset(preset)"
              class="h-16 rounded-lg border-2 border-gray-700 hover:scale-105 transition-all duration-300 group relative overflow-hidden"
              :style="{ backgroundColor: preset.primary }"
            >
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="w-1/2 h-full" :style="{ backgroundColor: preset.secondary }"></div>
              </div>
              <div class="absolute bottom-1 left-1 right-1 text-xs text-white font-medium bg-black/50 backdrop-blur-sm rounded px-1 py-0.5 truncate">
                {{ preset.name }}
              </div>
            </button>
          </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-gradient-to-r from-blue-900/30 to-purple-900/30 backdrop-blur-sm rounded-2xl p-6 border border-blue-800/30 shadow-2xl">
          <div class="text-center">
            <svg class="w-12 h-12 text-blue-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <h3 class="text-lg font-bold text-white mb-2">Unlock Full Features</h3>
            <p class="text-gray-300 text-sm mb-4">
              Create an account to save your colors, create palettes, and access exclusive features
            </p>
            <button
              @click="navigateToRegister"
              class="w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-2xl transition-all duration-300 hover:scale-105"
            >
              Get Started Free
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const primaryColor = ref('#3B82F6')
const secondaryColor = ref('#8B5CF6')
const mixRatio = ref(50)
const brightness = ref(50)

// Color presets
const colorPresets = ref([
  { name: 'Ocean Breeze', primary: '#3B82F6', secondary: '#06B6D4' },
  { name: 'Sunset Glow', primary: '#EF4444', secondary: '#F59E0B' },
  { name: 'Forest Mist', primary: '#10B981', secondary: '#84CC16' },
  { name: 'Royal Velvet', primary: '#8B5CF6', secondary: '#EC4899' },
  { name: 'Desert Sand', primary: '#F59E0B', secondary: '#F97316' },
  { name: 'Twilight', primary: '#6366F1', secondary: '#1E293B' }
])

// Walls for visualization
const walls = ref(['accent', 'normal', 'normal', 'normal', 'normal', 'accent'])

// Computed mixed color
const mixedColor = computed(() => {
  const p = hexToRgb(primaryColor.value)
  const s = hexToRgb(secondaryColor.value)
  
  // Mix based on ratio
  const ratio = mixRatio.value / 100
  const mixed = [
    Math.round(p[0] * ratio + s[0] * (1 - ratio)),
    Math.round(p[1] * ratio + s[1] * (1 - ratio)),
    Math.round(p[2] * ratio + s[2] * (1 - ratio))
  ]
  
  // Apply brightness
  const bright = brightness.value / 100
  mixed[0] = Math.min(255, Math.round(mixed[0] * bright))
  mixed[1] = Math.min(255, Math.round(mixed[1] * bright))
  mixed[2] = Math.min(255, Math.round(mixed[2] * bright))
  
  return rgbToHex(...mixed)
})

// Methods
const hexToRgb = (hex) => {
  const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex)
  return result ? [
    parseInt(result[1], 16),
    parseInt(result[2], 16),
    parseInt(result[3], 16)
  ] : [0, 0, 0]
}

const rgbToHex = (r, g, b) => {
  return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase()}`
}

const applyPreset = (preset) => {
  primaryColor.value = preset.primary
  secondaryColor.value = preset.secondary
  mixRatio.value = 50
  brightness.value = 50
}

const resetColors = () => {
  primaryColor.value = '#3B82F6'
  secondaryColor.value = '#8B5CF6'
  mixRatio.value = 50
  brightness.value = 50
}

const navigateToLogin = () => {
  router.push('/login')
}

const navigateToRegister = () => {
  router.push('/register')
}
</script>

<style scoped>
/* Custom range slider styling */
input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  height: 16px;
  width: 16px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
}

input[type="range"]::-moz-range-thumb {
  height: 16px;
  width: 16px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
  border: none;
}

/* Custom color input styling */
input[type="color"] {
  -webkit-appearance: none;
  border: none;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 8px;
}

/* Smooth transitions */
* {
  transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
}

/* Gradient text animation */
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient 15s ease infinite;
}

@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Glow effects */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .grid-cols-3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .grid-cols-3 {
    grid-template-columns: repeat(1, 1fr);
  }
}
</style>