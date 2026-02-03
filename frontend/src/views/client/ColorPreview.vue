<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 to-slate-950 p-4 sm:p-6">
    <!-- Header -->
    <div class="text-center mb-8 sm:mb-12">
      <div class="inline-flex items-center gap-3 mb-4">
        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center animate-pulse">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
          </svg>
        </div>
        <h1 class="text-3xl sm:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-300 to-rose-300">
          Color Mixing Lab
        </h1>
      </div>
      <p class="text-gray-300 text-lg">Create and explore color combinations with advanced visualization tools</p>
    </div>

    <!-- Main Content -->
    <div class="max-w-6xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <!-- Left Column: Color Inputs -->
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
          <!-- Color Mixer -->
          <div class="bg-gradient-to-br from-gray-800/40 to-gray-900/40 rounded-2xl p-6 border border-gray-700/50 backdrop-blur-sm">
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-6">Color Mixer Controls</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
              <!-- Color 1 -->
              <div v-if="colors[0]" class="bg-gray-800/30 rounded-xl p-4 border-2" :class="colors[0].active ? 'border-purple-500' : 'border-gray-700'">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-lg font-semibold text-white">Color 1</h3>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="colors[0].active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                  </label>
                </div>
                
                <div class="space-y-4">
                  <div class="h-32 rounded-lg relative overflow-hidden">
                    <div class="absolute inset-0" :style="{ backgroundColor: colors[0].hex }"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute bottom-3 left-3 right-3">
                      <div class="text-center">
                        <p class="text-sm font-semibold text-white">Color Code: {{ colors[0].hex }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="space-y-3">
                    <div>
                      <label class="text-sm text-gray-300 mb-2 block">Select Your Color</label>
                      <input type="color" v-model="colors[0].hex" @input="onColorChange(0, $event)" class="w-full h-10 rounded-lg cursor-pointer">
                    </div>
                    
                    <div>
                      <label class="text-sm text-gray-300 mb-2 block">Blending Strength: {{ colors[0].weight }}%</label>
                      <input type="range" v-model="colors[0].weight" min="0" max="100" 
                             class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-6 [&::-webkit-slider-thumb]:w-6 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-purple-500">
                      <p class="text-xs text-gray-400 mt-1">Higher percentage gives this color more influence in the mix</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Color 2 -->
              <div v-if="colors[1]" class="bg-gray-800/30 rounded-xl p-4 border-2" :class="colors[1].active ? 'border-pink-500' : 'border-gray-700'">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-lg font-semibold text-white">Color 2</h3>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="colors[1].active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"></div>
                  </label>
                </div>
                
                <div class="space-y-4">
                  <div class="h-32 rounded-lg relative overflow-hidden">
                    <div class="absolute inset-0" :style="{ backgroundColor: colors[1].hex }"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute bottom-3 left-3 right-3">
                      <div class="text-center">
                        <p class="text-sm font-semibold text-white">Color Code: {{ colors[1].hex }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="space-y-3">
                    <div>
                      <label class="text-sm text-gray-300 mb-2 block">Select Your Color</label>
                      <input type="color" v-model="colors[1].hex" @input="onColorChange(1, $event)" class="w-full h-10 rounded-lg cursor-pointer">
                    </div>
                    
                    <div>
                      <label class="text-sm text-gray-300 mb-2 block">Blending Strength: {{ colors[1].weight }}%</label>
                      <input type="range" v-model="colors[1].weight" min="0" max="100" 
                             class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-6 [&::-webkit-slider-thumb]:w-6 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-pink-500">
                      <p class="text-xs text-gray-400 mt-1">Higher percentage gives this color more influence in the mix</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Color 3 -->
              <div v-if="colors[2]" class="bg-gray-800/30 rounded-xl p-4 border-2" :class="colors[2].active ? 'border-cyan-500' : 'border-gray-700'">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-lg font-semibold text-white">Color 3</h3>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="colors[2].active" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-cyan-600"></div>
                  </label>
                </div>
                
                <div class="space-y-4">
                  <div class="h-32 rounded-lg relative overflow-hidden">
                    <div class="absolute inset-0" :style="{ backgroundColor: colors[2].hex }"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
                    <div class="absolute bottom-3 left-3 right-3">
                      <div class="text-center">
                        <p class="text-sm font-semibold text-white">Color Code: {{ colors[2].hex }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="space-y-3">
                    <div>
                      <label class="text-sm text-gray-300 mb-2 block">Select Your Color</label>
                      <input type="color" v-model="colors[2].hex" @input="onColorChange(2, $event)" class="w-full h-10 rounded-lg cursor-pointer">
                    </div>
                    
                    <div>
                      <label class="text-sm text-gray-300 mb-2 block">Blending Strength: {{ colors[2].weight }}%</label>
                      <input type="range" v-model="colors[2].weight" min="0" max="100" 
                             class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:h-6 [&::-webkit-slider-thumb]:w-6 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-cyan-500">
                      <p class="text-xs text-gray-400 mt-1">Higher percentage gives this color more influence in the mix</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3 justify-center">
              <button @click="randomizeColors" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-full font-semibold hover:scale-105 transition-transform flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Random Colors
              </button>
              <button @click="balanceWeights" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-full font-semibold hover:scale-105 transition-transform flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Equal Blending
              </button>
              <button @click="resetColors" class="px-5 py-2.5 bg-gray-700 text-white rounded-full font-semibold hover:bg-gray-600 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reset All Colors
              </button>
            </div>
          </div>
          
          <!-- COLOR MIX RESULT VISUALIZATION -->
          <div class="bg-gradient-to-br from-gray-800/40 to-gray-900/40 rounded-2xl p-6 border border-gray-700/50 backdrop-blur-sm">
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-6">Mixed Color Result</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Color Visualization -->
              <div class="space-y-4">
                <!-- Animated Color Sphere -->
                <div class="relative h-48 sm:h-64 rounded-2xl overflow-hidden bg-gradient-to-br from-gray-900 to-black">
                  <!-- Background Grid -->
                  <div class="absolute inset-0 opacity-20">
                    <div class="absolute inset-0 bg-grid-white/[0.02] bg-[size:20px_20px]"></div>
                  </div>
                  
                  <!-- 3D Sphere Container -->
                  <div class="absolute inset-0 flex items-center justify-center">
                    <!-- Outer Color Ring -->
                    <div class="absolute w-48 h-48 rounded-full animate-spin-slow" 
                         :style="{
                           background: `conic-gradient(from 0deg, transparent, ${colors[0] && colors[0].active ? colors[0].hex : '#666'}, ${colors[1] && colors[1].active ? colors[1].hex : '#666'}, ${colors[2] && colors[2].active ? colors[2].hex : '#666'}, transparent)`
                         }">
                    </div>
                    
                    <!-- Main Color Sphere -->
                    <div class="relative w-40 h-40">
                      <!-- 3D Sphere -->
                      <div class="absolute inset-0 rounded-full animate-float" 
                           :style="{
                             background: `radial-gradient(circle at 30% 30%, ${mixedColor.hex}, ${mixedColor.hex}80, transparent 70%)`,
                             boxShadow: `0 0 60px ${mixedColor.hex}40, inset 0 0 40px ${mixedColor.hex}20`
                           }">
                      </div>
                      
                      <!-- Color Orbit Rings -->
                      <div v-for="(color, index) in colors" :key="index" 
                           v-if="color && color.active"
                           class="absolute rounded-full animate-orbit"
                           :style="{
                             width: `${40 + (index * 20)}px`,
                             height: `${40 + (index * 20)}px`,
                             border: `1px solid ${color.hex}80`,
                             boxShadow: `0 0 20px ${color.hex}40`,
                             animationDuration: `${3 + index}s`,
                             top: `calc(50% - ${20 + (index * 10)}px)`,
                             left: `calc(50% - ${20 + (index * 10)}px)`
                           }">
                      </div>
                      
                      <!-- Color Particles -->
                      <div v-for="i in 8" :key="`particle-${i}`"
                           class="absolute w-1 h-1 rounded-full animate-pulse"
                           :style="{
                             backgroundColor: mixedColor.hex,
                             boxShadow: `0 0 10px ${mixedColor.hex}`,
                             top: `${Math.cos((i * 45) * Math.PI / 180) * 40 + 50}%`,
                             left: `${Math.sin((i * 45) * Math.PI / 180) * 40 + 50}%`,
                             animationDelay: `${i * 0.2}s`
                           }">
                      </div>
                    </div>
                    
                    <!-- Color Information Display -->
                    <div class="absolute bottom-4 left-4 right-4">
                      <div class="bg-black/60 backdrop-blur-sm rounded-lg p-3 border border-white/10">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg" :style="{ backgroundColor: mixedColor.hex }"></div>
                            <div>
                              <p class="text-sm font-semibold text-white">{{ mixedColor.name }}</p>
                              <p class="text-xs text-gray-300">Color Code: {{ mixedColor.hex }}</p>
                            </div>
                          </div>
                          <div class="text-xs text-gray-400">
                            <span class="inline-flex items-center gap-1">
                              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                              </svg>
                              Color Mixing Active
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Color Blending Analysis -->
                <div class="bg-black/40 rounded-xl p-4 border border-white/10">
                  <h4 class="text-lg font-semibold text-white mb-3">Color Blending Breakdown</h4>
                  <div class="space-y-2">
                    <div v-for="(color, index) in colors" :key="index" v-if="color && color.active"
                         class="flex items-center gap-3">
                      <div class="w-6 h-6 rounded-full" :style="{ backgroundColor: color.hex }"></div>
                      <div class="flex-1">
                        <div class="flex justify-between text-sm">
                          <span class="text-gray-300">Source Color {{ index + 1 }}</span>
                          <span class="text-white font-bold">{{ color.weight }}% influence</span>
                        </div>
                        <div class="w-full h-1.5 rounded-full overflow-hidden bg-gray-800">
                          <div class="h-full rounded-full transition-all duration-300" 
                               :style="{ 
                                 width: `${color.weight}%`, 
                                 background: `linear-gradient(90deg, ${color.hex}, ${color.hex}80)` 
                               }"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Color Technical Details -->
              <div class="space-y-4">
                <!-- Color Codes -->
                <div class="bg-black/40 rounded-xl p-4 border border-white/10">
                  <h4 class="text-lg font-semibold text-white mb-3">Color Technical Details</h4>
                  <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                      <div class="text-center">
                        <div class="bg-black/60 rounded-lg p-3">
                          <p class="text-xs text-gray-400 mb-1">HEX COLOR CODE</p>
                          <p class="text-lg font-mono font-bold text-white animate-pulse" 
                             :style="{ textShadow: `0 0 10px ${mixedColor.hex}` }">
                            {{ mixedColor.hex }}
                          </p>
                        </div>
                      </div>
                      <div class="text-center">
                        <div class="bg-black/60 rounded-lg p-3">
                          <p class="text-xs text-gray-400 mb-1">RGB VALUES</p>
                          <p class="text-lg font-mono font-bold text-white">{{ mixedColor.rgb }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <div class="bg-black/60 rounded-lg p-3">
                      <p class="text-xs text-gray-400 mb-1">HSL VALUES (Hue, Saturation, Lightness)</p>
                      <p class="text-sm font-mono text-white">{{ mixedColor.hsl }}</p>
                    </div>

                    <!-- Save Color Button -->
                    <div class="mt-4">
                      <button 
                        @click="saveMixedColor" 
                        :disabled="isSaveDisabled"
                        class="w-full px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-lg font-semibold hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save This Color
                      </button>
                      <p class="text-xs text-gray-400 mt-2 text-center">
                        Save this color blend to your collection
                      </p>
                    </div>
                  </div>
                </div>
                
                <!-- Color Properties -->
                <div class="grid grid-cols-2 gap-3">
                  <div class="bg-black/40 rounded-xl p-4 text-center border border-white/10">
                    <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center"
                         :style="{ 
                           background: `conic-gradient(from 0deg, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000)`
                         }">
                      <span class="text-sm font-bold text-white bg-black/50 rounded-full w-6 h-6 flex items-center justify-center">
                        {{ mixedColor.hue }}°
                      </span>
                    </div>
                    <p class="text-xs text-gray-300">Color Hue Angle</p>
                  </div>
                  
                  <div class="bg-black/40 rounded-xl p-4 text-center border border-white/10">
                    <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center"
                         :style="{ 
                           background: `conic-gradient(from 0deg, ${mixedColor.hex}00, ${mixedColor.hex}ff)`
                         }">
                      <span class="text-sm font-bold text-white bg-black/50 rounded-full w-6 h-6 flex items-center justify-center">
                        {{ mixedColor.saturation }}%
                      </span>
                    </div>
                    <p class="text-xs text-gray-300">Color Intensity</p>
                  </div>
                  
                  <div class="bg-black/40 rounded-xl p-4 text-center border border-white/10">
                    <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center"
                         :style="{ 
                           background: `linear-gradient(to bottom, white, black)`
                         }">
                      <span class="text-sm font-bold text-white bg-black/50 rounded-full w-6 h-6 flex items-center justify-center"
                            :style="{ transform: `translateY(${50 - mixedColor.lightness/2}%)` }">
                        {{ mixedColor.lightness }}%
                      </span>
                    </div>
                    <p class="text-xs text-gray-300">Brightness Level</p>
                  </div>
                  
                  <div class="bg-black/40 rounded-xl p-4 text-center border border-white/10">
                    <div class="w-10 h-10 rounded-full mx-auto mb-2 flex items-center justify-center"
                         :style="{ 
                           background: mixedColor.hex,
                           boxShadow: `0 0 20px ${mixedColor.hex}`
                         }">
                      <span class="text-xs font-bold text-white">
                        {{ mixedColor.family }}
                      </span>
                    </div>
                    <p class="text-xs text-gray-300">Color Family</p>
                  </div>
                </div>
                
                <!-- Color Characteristics -->
                <div class="bg-black/40 rounded-xl p-4 border border-white/10">
                  <h4 class="text-lg font-semibold text-white mb-3">Color Characteristics</h4>
                  <div class="space-y-2">
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-300">Color Temperature</span>
                      <span class="text-sm font-semibold" :class="mixedColor.temperature === 'Warm' ? 'text-orange-400' : mixedColor.temperature === 'Cool' ? 'text-blue-400' : 'text-gray-400'">
                        {{ mixedColor.temperature }}
                        <span class="inline-block w-2 h-2 rounded-full ml-1 animate-pulse"
                              :class="mixedColor.temperature === 'Warm' ? 'bg-orange-400' : mixedColor.temperature === 'Cool' ? 'bg-blue-400' : 'bg-gray-400'"></span>
                      </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-300">Brightness</span>
                      <span class="text-sm font-semibold text-white">
                        {{ mixedColor.luminance }}%
                        <div class="inline-block w-16 h-1.5 bg-gray-800 rounded-full ml-2 overflow-hidden">
                          <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-blue-500"
                               :style="{ width: `${mixedColor.luminance}%` }"></div>
                        </div>
                      </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-300">Text Readability</span>
                      <span class="text-sm font-semibold text-white">
                        Rating: {{ mixedColor.contrast }}:1
                        <span class="text-xs ml-1" :class="mixedColor.accessibility === 'Good' ? 'text-green-400' : mixedColor.accessibility === 'Poor' ? 'text-red-400' : 'text-yellow-400'">
                          ({{ mixedColor.accessibility }})
                        </span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Right Column: Color Palettes & Suggestions -->
        <div class="space-y-6 sm:space-y-8">
          <!-- Quick Color Suggestions -->
          <div class="bg-gradient-to-br from-gray-800/40 to-gray-900/40 rounded-2xl p-6 border border-gray-700/50 backdrop-blur-sm">
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-6">Color Suggestions</h2>
            
            <div class="grid grid-cols-2 gap-3">
              <button v-for="suggestion in colorSuggestions" :key="suggestion.name"
                      @click="applySuggestion(suggestion)"
                      class="group relative overflow-hidden rounded-xl p-4 text-left transition-all hover:scale-105">
                <div class="absolute inset-0" :style="{ backgroundColor: suggestion.hex }"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="relative z-10">
                  <p class="text-sm font-semibold text-white">{{ suggestion.name }}</p>
                  <p class="text-xs text-white/80 mt-1">Code: {{ suggestion.hex }}</p>
                </div>
              </button>
            </div>
          </div>
          
          <!-- Color Harmony Palettes -->
          <div class="bg-gradient-to-br from-gray-800/40 to-gray-900/40 rounded-2xl p-6 border border-gray-700/50 backdrop-blur-sm">
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-6">Color Harmony Palettes</h2>
            
            <div class="space-y-4">
              <!-- Monochromatic -->
              <div>
                <p class="text-sm text-gray-300 mb-2">Same Color Family (Monochromatic)</p>
                <div class="flex h-10 rounded-lg overflow-hidden border border-white/10">
                  <div v-for="(shade, i) in monochromaticPalette" :key="i" 
                       class="flex-1 group relative cursor-pointer" :style="{ backgroundColor: shade }"
                       @click="copyColor(shade)">
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Five shades from light to dark of your mixed color</p>
              </div>
              
              <!-- Analogous -->
              <div>
                <p class="text-sm text-gray-300 mb-2">Adjacent Colors (Analogous)</p>
                <div class="flex h-10 rounded-lg overflow-hidden border border-white/10">
                  <div v-for="(color, i) in analogousPalette" :key="i" 
                       class="flex-1 group relative cursor-pointer" :style="{ backgroundColor: color }"
                       @click="copyColor(color)">
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Colors that are next to each other on the color wheel</p>
              </div>
              
              <!-- Complementary -->
              <div>
                <p class="text-sm text-gray-300 mb-2">Opposite Colors (Complementary)</p>
                <div class="flex h-10 rounded-lg overflow-hidden border border-white/10">
                  <div v-for="(color, i) in complementaryPalette" :key="i" 
                       class="flex-1 group relative cursor-pointer" :style="{ backgroundColor: color }"
                       @click="copyColor(color)">
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                  </div>
                </div>
                <p class="text-xs text-gray-400 mt-1">Your color plus its opposite for high contrast</p>
              </div>
            </div>
          </div>
          
          <!-- Color Statistics -->
          <div class="bg-gradient-to-br from-gray-800/40 to-gray-900/40 rounded-2xl p-6 border border-gray-700/50 backdrop-blur-sm">
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-6">Color Statistics</h2>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-gray-300">Active Color Sources</span>
                <div class="flex items-center gap-2">
                  <div class="w-16 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full"
                         :style="{ width: `${activeColorsCount * 33}%` }"></div>
                  </div>
                  <span class="text-sm font-semibold text-white">{{ activeColorsCount }} out of 3</span>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-300">Color Balance</span>
                <span class="font-semibold text-green-400">
                  {{ mixedColor.stability }}
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-300">Visual Vibrancy</span>
                <span class="font-semibold text-cyan-400">
                  {{ mixedColor.frequency }} (scale)
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-300">Blending Complexity</span>
                <span class="font-semibold text-purple-400">
                  {{ mixedColor.quantumState }}
                </span>
              </div>
            </div>
          </div>
          
          <!-- Color Tips -->
          <div class="bg-gradient-to-br from-purple-900/20 to-pink-900/20 rounded-2xl p-6 border border-purple-500/30 backdrop-blur-sm">
            <h3 class="text-lg font-bold text-white mb-3">💡 Color Mixing Tips</h3>
            <p class="text-gray-200 text-sm">
              {{ tips[currentTip] }}
            </p>
            <div class="flex justify-center mt-4">
              <button @click="nextTip" class="text-sm text-purple-300 hover:text-purple-200 flex items-center gap-1">
                Show Another Tip
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Copy Notification -->
      <div v-if="showCopyNotification" class="fixed bottom-4 right-4 bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-2 rounded-lg shadow-lg animate-slide-up backdrop-blur-sm">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Color code copied to clipboard!
        </div>
      </div>

      <!-- Save Success Notification -->
      <div v-if="showSaveNotification" class="fixed bottom-4 right-4 bg-gradient-to-r from-emerald-500 to-green-500 text-white px-4 py-2 rounded-lg shadow-lg animate-slide-up backdrop-blur-sm z-50">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Color saved to your collection!
        </div>
      </div>

      <!-- Save Error Notification -->
      <div v-if="showErrorNotification" class="fixed bottom-4 right-4 bg-gradient-to-r from-red-500 to-rose-500 text-white px-4 py-2 rounded-lg shadow-lg animate-slide-up backdrop-blur-sm z-50">
        <div class="flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Failed to save color. Please try again.
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/utils/axios'

export default {
  name: 'ColorMixer',
  data() {
    return {
      colors: [
        {
          id: 1,
          name: 'Vibrant Red',
          hex: '#FF3636',
          rgb: '255, 54, 54',
          active: true,
          weight: 40
        },
        {
          id: 2,
          name: 'Sunshine Yellow',
          hex: '#FFE436',
          rgb: '255, 228, 54',
          active: true,
          weight: 35
        },
        {
          id: 3,
          name: 'Ocean Blue',
          hex: '#00C8FF',
          rgb: '0, 200, 255',
          active: true,
          weight: 25
        }
      ],
      mixedColor: {
        name: 'Blended Color Result',
        hex: '#FF6B6B',
        rgb: '255, 107, 107',
        hsl: '0°, 100%, 71%',
        hue: 0,
        saturation: 100,
        lightness: 71,
        contrast: 4.5,
        luminance: 65,
        temperature: 'Warm',
        accessibility: 'Good',
        family: 'Red',
        stability: 'Stable',
        frequency: '432',
        quantumState: 'Two Colors Blended'
      },
      colorSuggestions: [
        { name: 'Vibrant Red', hex: '#FF3636' },
        { name: 'Sunshine Yellow', hex: '#FFE436' },
        { name: 'Ocean Blue', hex: '#00C8FF' },
        { name: 'Electric Purple', hex: '#9E00F6' },
        { name: 'Mint Green', hex: '#00FF9D' },
        { name: 'Coral Pink', hex: '#FF7F83' }
      ],
      tips: [
        "Color blending uses weighted mixing for accurate results",
        "Adjust the 'Blending Strength' sliders to control how much each color influences the mix",
        "Higher saturation creates more vibrant colors",
        "Try complementary colors for maximum visual impact",
        "Adjust lightness to make colors brighter or darker",
        "Monochromatic palettes create harmonious color schemes",
        "Use analogous colors for smooth transitions",
        "Complementary colors work well for contrast and emphasis"
      ],
      currentTip: 0,
      showCopyNotification: false,
      showSaveNotification: false,
      showErrorNotification: false
    }
  },
  computed: {
    activeColorsCount() {
      return this.colors.filter(c => c && c.active).length;
    },
    monochromaticPalette() {
      return [
        this.lightenColor(this.mixedColor.hex, 40),
        this.lightenColor(this.mixedColor.hex, 20),
        this.mixedColor.hex,
        this.darkenColor(this.mixedColor.hex, 20),
        this.darkenColor(this.mixedColor.hex, 40)
      ];
    },
    analogousPalette() {
      const baseHue = this.mixedColor.hue;
      return [
        this.hslToHex((baseHue - 30 + 360) % 360, 80, 60),
        this.hslToHex((baseHue - 15 + 360) % 360, 90, 65),
        this.mixedColor.hex,
        this.hslToHex((baseHue + 15) % 360, 90, 65),
        this.hslToHex((baseHue + 30) % 360, 80, 60)
      ];
    },
    complementaryPalette() {
      const baseHue = this.mixedColor.hue;
      const compHue = (baseHue + 180) % 360;
      return [
        this.mixedColor.hex,
        this.lightenColor(this.mixedColor.hex, 30),
        this.hslToHex(compHue, 80, 60),
        this.darkenColor(this.hslToHex(compHue, 80, 60), 30),
        '#FFFFFF'
      ];
    },
    isSaveDisabled() {
      return this.activeColorsCount === 0;
    }
  },
  watch: {
    colors: {
      deep: true,
      handler() {
        this.calculateMixedColor();
      }
    }
  },
  mounted() {
    this.calculateMixedColor();
  },
  methods: {
    async saveMixedColor() {
      try {
        const colorData = {
          name: String(this.mixedColor.name || 'Unnamed Color').substring(0, 255),
          hex: String(this.mixedColor.hex || '#000000').toUpperCase(),
          rgb: String(this.mixedColor.rgb || '0, 0, 0'),
          hsl: String(this.mixedColor.hsl || '0°, 0%, 0%'),
          hue: parseInt(this.mixedColor.hue) || 0,
          saturation: parseInt(this.mixedColor.saturation) || 0,
          lightness: parseInt(this.mixedColor.lightness) || 0,
          contrast: parseFloat(this.mixedColor.contrast) || 4.5,
          luminance: parseInt(this.mixedColor.luminance) || 0,
          temperature: String(this.mixedColor.temperature || 'Neutral').substring(0, 50),
          accessibility: String(this.mixedColor.accessibility || 'Good').substring(0, 50),
          family: String(this.mixedColor.family || 'Unknown').substring(0, 50),
          stability: String(this.mixedColor.stability || 'Stable').substring(0, 50),
          frequency: String(this.mixedColor.frequency || '0').substring(0, 50),
          quantumState: String(this.mixedColor.quantumState || 'Mixed Colors').substring(0, 50),
          sourceColors: this.colors.filter(c => c && c.active).map(color => ({
            hex: color.hex,
            weight: color.weight,
            active: color.active
          })),
          palettes: {
            monochromatic: this.monochromaticPalette || [],
            analogous: this.analogousPalette || [],
            complementary: this.complementaryPalette || []
          },
          isFavorite: false
        };

        if (colorData.sourceColors.length === 0) {
          colorData.sourceColors = null;
        }

        const response = await api.post('/client/save-color', colorData);
        
        if (response.data.success) {
          this.showSaveNotification = true;
          setTimeout(() => {
            this.showSaveNotification = false;
          }, 3000);
          
          this.$emit('color-saved', response.data.data);
        } else {
          this.showErrorNotification = true;
          setTimeout(() => {
            this.showErrorNotification = false;
          }, 3000);
        }
      } catch (error) {
        this.showErrorNotification = true;
        setTimeout(() => {
          this.showErrorNotification = false;
        }, 3000);
        
        if (error.response) {
          if (error.response.status === 401) {
            this.$router.push('/Landing/logIn');
          }
        }
      }
    },
    
    onColorChange(index, event) {
      this.colors[index].hex = event.target.value.toUpperCase();
      this.updateRGBFromHex(index);
      this.calculateMixedColor();
    },
    
    updateRGBFromHex(index) {
      if (!this.colors[index] || !this.colors[index].hex) return;
      
      const hex = this.colors[index].hex.replace('#', '');
      if (hex.length === 6) {
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);
        this.colors[index].rgb = `${r}, ${g}, ${b}`;
      }
    },
    
    calculateMixedColor() {
      const activeColors = this.colors.filter(c => c && c.active);
      
      if (activeColors.length === 0) {
        this.mixedColor = {
          name: 'No Colors Selected',
          hex: '#666666',
          rgb: '102, 102, 102',
          hsl: '0°, 0%, 40%',
          hue: 0,
          saturation: 0,
          lightness: 40,
          contrast: 4.5,
          luminance: 40,
          temperature: 'Neutral',
          accessibility: 'Good',
          family: 'Gray',
          stability: 'Inactive',
          frequency: '0',
          quantumState: 'No Colors'
        };
        return;
      }
      
      if (activeColors.length === 1) {
        const color = activeColors[0];
        const [r, g, b] = color.rgb.split(',').map(num => parseInt(num.trim()));
        const hsl = this.rgbToHSL(r, g, b);
        
        this.mixedColor = {
          name: `Pure ${this.getColorFamily(hsl.h)}`,
          hex: color.hex,
          rgb: color.rgb,
          hsl: `${Math.round(hsl.h)}°, ${Math.round(hsl.s * 100)}%, ${Math.round(hsl.l * 100)}%`,
          hue: Math.round(hsl.h),
          saturation: Math.round(hsl.s * 100),
          lightness: Math.round(hsl.l * 100),
          contrast: this.calculateContrast(color.hex),
          luminance: this.calculateLuminance(r, g, b),
          temperature: this.getColorTemperature(hsl.h, hsl.l),
          accessibility: this.calculateAccessibility(color.hex),
          family: this.getColorFamily(hsl.h),
          stability: 'Single Color',
          frequency: Math.round(432 + (hsl.s * 100 * 0.5)),
          quantumState: 'Single Color'
        };
        return;
      }
      
      let totalWeight = activeColors.reduce((sum, c) => sum + c.weight, 0);
      if (totalWeight === 0) totalWeight = 1;
      
      let linearR = 0, linearG = 0, linearB = 0;
      
      activeColors.forEach(color => {
        const [r, g, b] = color.rgb.split(',').map(num => parseInt(num.trim()));
        const weight = color.weight / totalWeight;
        
        const linearize = (val) => {
          val = val / 255;
          return val <= 0.04045 ? val / 12.92 : Math.pow((val + 0.055) / 1.055, 2.4);
        };
        
        linearR += linearize(r) * weight;
        linearG += linearize(g) * weight;
        linearB += linearize(b) * weight;
      });
      
      const delinearize = (val) => {
        return val <= 0.0031308 ? 12.92 * val : 1.055 * Math.pow(val, 1/2.4) - 0.055;
      };
      
      let r = Math.round(delinearize(linearR) * 255);
      let g = Math.round(delinearize(linearG) * 255);
      let b = Math.round(delinearize(linearB) * 255);
      
      r = Math.max(0, Math.min(255, r));
      g = Math.max(0, Math.min(255, g));
      b = Math.max(0, Math.min(255, b));
      
      const hex = `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase()}`;
      const hsl = this.rgbToHSL(r, g, b);
      const hue = Math.round(hsl.h);
      const saturation = Math.round(hsl.s * 100);
      const lightness = Math.round(hsl.l * 100);
      
      const stability = this.calculateStability(hsl.s, hsl.l);
      const frequency = Math.round(432 + (saturation * 0.3) + (lightness * 0.2));
      const quantumState = this.getQuantumState(activeColors.length);
      const temperature = this.getColorTemperature(hue, lightness);
      const family = this.getColorFamily(hue);
      const name = this.generateColorName(family, saturation, lightness, activeColors.length);
      
      this.mixedColor = {
        name: name,
        hex: hex,
        rgb: `${r}, ${g}, ${b}`,
        hsl: `${hue}°, ${saturation}%, ${lightness}%`,
        hue: hue,
        saturation: saturation,
        lightness: lightness,
        contrast: this.calculateContrast(hex),
        luminance: this.calculateLuminance(r, g, b),
        temperature: temperature,
        accessibility: this.calculateAccessibility(hex),
        family: family,
        stability: stability,
        frequency: frequency,
        quantumState: quantumState
      };
    },
    
    rgbToHSL(r, g, b) {
      r /= 255;
      g /= 255;
      b /= 255;
      
      const max = Math.max(r, g, b);
      const min = Math.min(r, g, b);
      let h, s, l = (max + min) / 2;
      
      if (max === min) {
        h = s = 0;
      } else {
        const d = max - min;
        s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
        
        switch (max) {
          case r: h = (g - b) / d + (g < b ? 6 : 0); break;
          case g: h = (b - r) / d + 2; break;
          case b: h = (r - g) / d + 4; break;
        }
        h /= 6;
      }
      
      return { h: h * 360, s: s, l: l };
    },
    
    hslToHex(h, s, l) {
      h /= 360;
      s /= 100;
      l /= 100;
      
      let r, g, b;
      
      if (s === 0) {
        r = g = b = l;
      } else {
        const hue2rgb = (p, q, t) => {
          if (t < 0) t += 1;
          if (t > 1) t -= 1;
          if (t < 1/6) return p + (q - p) * 6 * t;
          if (t < 1/2) return q;
          if (t < 2/3) return p + (q - p) * (2/3 - t) * 6;
          return p;
        };
        
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        
        r = hue2rgb(p, q, h + 1/3);
        g = hue2rgb(p, q, h);
        b = hue2rgb(p, q, h - 1/3);
      }
      
      const toHex = x => {
        const hex = Math.round(x * 255).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
      };
      
      return `#${toHex(r)}${toHex(g)}${toHex(b)}`.toUpperCase();
    },
    
    lightenColor(hex, percent) {
      if (!hex || !hex.startsWith('#')) return '#FFFFFF';
      const [r, g, b] = hex.match(/\w\w/g).map(x => parseInt(x, 16));
      
      const hsl = this.rgbToHSL(r, g, b);
      hsl.l = Math.min(1, hsl.l + (percent / 100));
      
      return this.hslToHex(hsl.h, hsl.s * 100, hsl.l * 100);
    },
    
    darkenColor(hex, percent) {
      if (!hex || !hex.startsWith('#')) return '#000000';
      const [r, g, b] = hex.match(/\w\w/g).map(x => parseInt(x, 16));
      
      const hsl = this.rgbToHSL(r, g, b);
      hsl.l = Math.max(0, hsl.l - (percent / 100));
      
      return this.hslToHex(hsl.h, hsl.s * 100, hsl.l * 100);
    },
    
    calculateLuminance(r, g, b) {
      const a = [r, g, b].map(v => {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
      });
      return Math.round((a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722) * 100);
    },
    
    calculateContrast(hex) {
      if (!hex || !hex.startsWith('#')) return 4.5;
      const [r, g, b] = hex.match(/\w\w/g).map(x => parseInt(x, 16));
      const luminance = this.calculateLuminance(r, g, b) / 100;
      const whiteLuminance = 1;
      const contrast = (Math.max(whiteLuminance, luminance) + 0.05) / (Math.min(whiteLuminance, luminance) + 0.05);
      return Math.round(contrast * 10) / 10;
    },
    
    calculateAccessibility(hex) {
      const contrast = this.calculateContrast(hex);
      return contrast >= 7 ? 'Excellent' : contrast >= 4.5 ? 'Good' : 'Poor';
    },
    
    getColorFamily(hue) {
      if (hue >= 0 && hue < 15) return 'Red';
      if (hue >= 15 && hue < 45) return 'Orange';
      if (hue >= 45 && hue < 75) return 'Yellow';
      if (hue >= 75 && hue < 165) return 'Green';
      if (hue >= 165 && hue < 195) return 'Cyan';
      if (hue >= 195 && hue < 255) return 'Blue';
      if (hue >= 255 && hue < 285) return 'Purple';
      if (hue >= 285 && hue < 315) return 'Magenta';
      if (hue >= 315 && hue < 345) return 'Pink';
      return 'Red';
    },
    
    getColorTemperature(hue, lightness) {
      if ((hue >= 0 && hue < 45) || (hue >= 315 && hue <= 360)) {
        return lightness > 60 ? 'Warm' : 'Deep Warm';
      }
      if (hue >= 45 && hue < 315) {
        return lightness > 60 ? 'Cool' : 'Deep Cool';
      }
      return 'Neutral';
    },
    
    calculateStability(saturation, lightness) {
      if (saturation < 0.3) return 'Very Stable';
      if (saturation < 0.6) return 'Stable';
      if (saturation < 0.8) return 'Dynamic';
      return 'Highly Vibrant';
    },
    
    getQuantumState(activeCount) {
      switch(activeCount) {
        case 1: return 'Single Color';
        case 2: return 'Two Colors Blended';
        case 3: return 'Three Colors Blended';
        default: return 'Mixed Colors';
      }
    },
    
    generateColorName(family, saturation, lightness, activeCount) {
      const prefixes = ['Blended', 'Mixed', 'Harmonic', 'Balanced', 'Created'];
      const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
      
      let descriptor = '';
      if (saturation > 80) descriptor = 'Vibrant ';
      else if (saturation > 60) descriptor = 'Rich ';
      else if (saturation > 40) descriptor = 'Muted ';
      else descriptor = 'Soft ';
      
      if (lightness > 80) descriptor += 'Light ';
      else if (lightness < 30) descriptor += 'Dark ';
      
      const suffix = activeCount > 1 ? ' Blend' : ' Color';
      
      return `${prefix} ${descriptor}${family}${suffix}`;
    },
    
    randomizeColors() {
      const hues = [0, 30, 60, 120, 180, 240, 300, 330];
      
      this.colors.forEach((color, index) => {
        const hue = hues[Math.floor(Math.random() * hues.length)];
        const saturation = 70 + Math.random() * 30;
        const lightness = 40 + Math.random() * 40;
        
        color.hex = this.hslToHex(hue, saturation, lightness);
        color.weight = Math.floor(Math.random() * 30) + 20;
        color.active = true;
        this.updateRGBFromHex(index);
      });
      
      this.calculateMixedColor();
    },
    
    resetColors() {
      this.colors = [
        {
          id: 1,
          name: 'Vibrant Red',
          hex: '#FF3636',
          rgb: '255, 54, 54',
          active: true,
          weight: 40
        },
        {
          id: 2,
          name: 'Sunshine Yellow',
          hex: '#FFE436',
          rgb: '255, 228, 54',
          active: true,
          weight: 35
        },
        {
          id: 3,
          name: 'Ocean Blue',
          hex: '#00C8FF',
          rgb: '0, 200, 255',
          active: true,
          weight: 25
        }
      ];
      this.calculateMixedColor();
    },
    
    balanceWeights() {
      const activeCount = this.colors.filter(c => c && c.active).length;
      if (activeCount === 0) return;
      
      const equalWeight = Math.floor(100 / activeCount);
      this.colors.forEach(color => {
        if (color && color.active) {
          color.weight = equalWeight;
        }
      });
      
      this.calculateMixedColor();
    },
    
    applySuggestion(suggestion) {
      for (let i = 0; i < this.colors.length; i++) {
        if (!this.colors[i].active) {
          this.colors[i].hex = suggestion.hex;
          this.colors[i].active = true;
          this.updateRGBFromHex(i);
          this.calculateMixedColor();
          break;
        }
      }
    },
    
    copyColor(color) {
      navigator.clipboard.writeText(color).then(() => {
        this.showCopyNotification = true;
        setTimeout(() => {
          this.showCopyNotification = false;
        }, 2000);
      });
    },
    
    nextTip() {
      this.currentTip = (this.currentTip + 1) % this.tips.length;
    }
  }
}
</script>

<style scoped>
/* Futuristic Animations */
@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.animate-spin-slow {
  animation: spin-slow 20s linear infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) scale(1); }
  50% { transform: translateY(-10px) scale(1.05); }
}

.animate-float {
  animation: float 4s ease-in-out infinite;
}

@keyframes orbit {
  0% { transform: rotate(0deg) translateX(40px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(40px) rotate(-360deg); }
}

.animate-orbit {
  animation: orbit linear infinite;
}

@keyframes slide-up {
  0% {
    transform: translateY(100%);
    opacity: 0;
  }
  100% {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

@keyframes pulse-glow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.animate-pulse {
  animation: pulse-glow 2s ease-in-out infinite;
}

/* Futuristic Grid Background */
.bg-grid-white\/\[0\.02\] {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.05)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
}

/* Cyberpunk Glow Effects */
.cyber-glow {
  filter: drop-shadow(0 0 20px currentColor);
}

/* Glass Morphism */
.backdrop-blur-sm {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

/* Gradient Text */
.text-transparent {
  background-clip: text;
  -webkit-background-clip: text;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #8b5cf6, #ec4899);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #7c3aed, #db2777);
}

/* Range Slider Futuristic */
input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  background: transparent;
  cursor: pointer;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 10px currentColor;
  cursor: pointer;
  background: transparent;
}

input[type="range"]::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 10px currentColor;
  cursor: pointer;
  background: transparent;
}

/* Color Input Styling */
input[type="color"] {
  -webkit-appearance: none;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  height: 40px;
  border: 2px solid rgba(255, 255, 255, 0.1);
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
  border-radius: 6px;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 6px;
}

/* Holographic Effects */
.hologram {
  background: linear-gradient(
    45deg,
    transparent 25%,
    rgba(255, 255, 255, 0.05) 25%,
    rgba(255, 255, 255, 0.05) 50%,
    transparent 50%,
    transparent 75%,
    rgba(255, 255, 255, 0.05) 75%
  );
  background-size: 20px 20px;
}

/* Quantum Particle Effects */
.quantum-particle {
  position: absolute;
  pointer-events: none;
  background: radial-gradient(circle at center, currentColor, transparent 70%);
}

/* Responsive Design */
@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: repeat(1, 1fr);
  }
  
  .text-3xl {
    font-size: 2rem;
  }
  
  .p-6 {
    padding: 1.5rem;
  }
  
  .h-48 {
    height: 12rem;
  }
}

/* Dark Mode Enhancements */
@media (prefers-color-scheme: dark) {
  .bg-gradient-to-br {
    background-image: linear-gradient(
      to bottom right,
      rgba(30, 41, 59, 0.6),
      rgba(15, 23, 42, 0.6)
    );
  }
}

/* Reduced Motion */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Neon Border Effect */
.neon-border {
  border: 1px solid;
  border-image: linear-gradient(45deg, #8b5cf6, #ec4899, #00c8ff) 1;
  box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
}

/* Data Stream Effect */
.data-stream {
  position: relative;
  overflow: hidden;
}

.data-stream::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(
    to bottom right,
    transparent 30%,
    rgba(255, 255, 255, 0.1) 50%,
    transparent 70%
  );
  animation: data-stream-flow 3s linear infinite;
}

@keyframes data-stream-flow {
  0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
  100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

/* Cyberpunk Button Effects */
button {
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
}

button::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: linear-gradient(
    to bottom right,
    transparent 30%,
    rgba(255, 255, 255, 0.2) 50%,
    transparent 70%
  );
  transform: rotate(45deg);
  transition: all 0.5s ease;
  opacity: 0;
}

button:hover::after {
  opacity: 1;
  animation: button-glow 1.5s linear infinite;
}

@keyframes button-glow {
  0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
  100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
}

/* Quantum Wave Effect */
.quantum-wave {
  position: relative;
}

.quantum-wave::before {
  content: '';
  position: absolute;
  inset: 0;
  background: conic-gradient(
    from 0deg,
    transparent 0deg,
    currentColor 90deg,
    transparent 180deg,
    currentColor 270deg,
    transparent 360deg
  );
  opacity: 0.1;
  animation: quantum-spin 10s linear infinite;
}

@keyframes quantum-spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Holographic Text */
.holographic-text {
  background: linear-gradient(45deg, #8b5cf6, #ec4899, #00c8ff);
  background-size: 200% 200%;
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  animation: holographic-shift 3s ease infinite;
}

@keyframes holographic-shift {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
</style>