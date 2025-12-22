<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 to-slate-950 p-3 sm:p-4 md:p-6">
    <!-- Page Header -->
    <div class="mb-4 sm:mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div class="max-w-full overflow-hidden">
          <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2">
            <div class="p-2 sm:p-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-purple-500/10 to-pink-500/10 backdrop-blur-sm border border-purple-500/20">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 via-pink-300 to-rose-300 leading-tight sm:leading-normal">
              Color Visualization
            </h1>
          </div>
          <p class="text-gray-400 text-sm sm:text-base">Interactive 3D preview of your selected wall colors</p>
        </div>
        
        <!-- Unity Badge -->
        <div class="mt-3 sm:mt-0">
          <div class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-1.5 sm:px-4 sm:py-2 rounded-full bg-gradient-to-r from-purple-600/20 to-pink-600/20 backdrop-blur-sm border border-purple-500/30">
            <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center">
              <span class="text-xs sm:text-sm font-bold text-white">U</span>
            </div>
            <span class="text-xs sm:text-sm font-semibold text-purple-300">UNITY 3D</span>
            <span class="text-xs text-gray-400 hidden sm:inline">• View Only</span>
          </div>
        </div>
      </div>
      
      <!-- Quick Info Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center gap-2 sm:gap-3">
            <div class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-blue-500/10">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm">Active Preview</p>
              <p class="text-lg sm:text-xl font-bold text-white truncate">Living Room Wall</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center gap-2 sm:gap-3">
            <div class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-purple-500/10">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm">Selected Color</p>
              <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded-md border border-gray-600" :style="{ backgroundColor: activeColor.hex }"></div>
                <p class="text-lg sm:text-xl font-bold text-white truncate">{{ activeColor.name }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center gap-2 sm:gap-3">
            <div class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-cyan-500/10">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm">View Mode</p>
              <p class="text-lg sm:text-xl font-bold text-cyan-300 truncate">Read Only</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-xl sm:rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center gap-2 sm:gap-3">
            <div class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-emerald-500/10">
              <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm">Last Updated</p>
              <p class="text-lg sm:text-xl font-bold text-emerald-300 truncate">Today, 10:30 AM</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
      <!-- Unity Preview Container (2/3 width on desktop) -->
      <div class="lg:col-span-2">
        <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-2xl border border-gray-700/50 backdrop-blur-sm overflow-hidden h-[400px] sm:h-[500px] md:h-[600px] relative">
          <!-- Unity WebGL Loading/Placeholder -->
          <div class="absolute inset-0 flex flex-col items-center justify-center bg-gradient-to-br from-gray-900/80 to-slate-900/80">
            <div class="text-center p-4 sm:p-6">
              <!-- Unity Logo Animation -->
              <div class="relative mx-auto mb-4 sm:mb-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 rounded-2xl bg-gradient-to-br from-purple-600 via-pink-500 to-rose-500 flex items-center justify-center animate-pulse mx-auto">
                  <span class="text-2xl sm:text-3xl md:text-4xl font-bold text-white">U</span>
                </div>
                <div class="absolute inset-0 rounded-2xl border-2 border-purple-400/30 animate-ping"></div>
              </div>
              
              <h3 class="text-lg sm:text-xl md:text-2xl font-bold text-white mb-2">3D Color Visualization</h3>
              <p class="text-gray-300 text-sm sm:text-base mb-4 max-w-md mx-auto">
                Interactive Unity WebGL preview of your selected wall color
              </p>
              
              <!-- Loading Animation -->
              <div class="flex items-center justify-center gap-2 mb-6">
                <div class="w-2 h-2 rounded-full bg-purple-500 animate-bounce" style="animation-delay: 0s"></div>
                <div class="w-2 h-2 rounded-full bg-pink-500 animate-bounce" style="animation-delay: 0.2s"></div>
                <div class="w-2 h-2 rounded-full bg-rose-500 animate-bounce" style="animation-delay: 0.4s"></div>
              </div>
              
              <!-- View Controls (Read Only) -->
              <div class="flex flex-wrap gap-2 justify-center">
                <button class="px-3 py-1.5 bg-gray-700/50 text-gray-300 rounded-lg text-xs sm:text-sm border border-gray-600 flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  <span>View Only</span>
                </button>
                <button class="px-3 py-1.5 bg-gray-700/50 text-gray-300 rounded-lg text-xs sm:text-sm border border-gray-600 flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                  </svg>
                  <span>Orbit Camera</span>
                </button>
                <button class="px-3 py-1.5 bg-gray-700/50 text-gray-300 rounded-lg text-xs sm:text-sm border border-gray-600 flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <span>Zoom</span>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Unity Canvas Placeholder -->
          <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-slate-900 opacity-50"></div>
          
          <!-- Color Preview Overlay -->
          <div class="absolute bottom-4 left-4 right-4">
            <div class="bg-gray-900/80 backdrop-blur-sm rounded-lg sm:rounded-xl p-3 sm:p-4 border border-gray-700/50">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg" :style="{ backgroundColor: activeColor.hex }"></div>
                  <div>
                    <p class="text-sm sm:text-base font-semibold text-white">{{ activeColor.name }}</p>
                    <p class="text-xs sm:text-sm text-gray-300">{{ activeColor.hex }} • RGB({{ activeColor.rgb }})</p>
                  </div>
                </div>
                <div class="text-xs sm:text-sm text-gray-400">
                  <span class="inline-flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Preview Mode: View Only
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Scene Controls -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
          <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-lg p-3 border border-gray-700/50 backdrop-blur-sm text-center">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">Lighting</div>
            <div class="flex items-center justify-center gap-2">
              <div class="w-6 h-6 rounded-full bg-gradient-to-r from-amber-400 to-yellow-300 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z" />
                </svg>
              </div>
              <span class="text-sm sm:text-base font-medium text-white">Daylight</span>
            </div>
          </div>
          
          <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-lg p-3 border border-gray-700/50 backdrop-blur-sm text-center">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">View Angle</div>
            <div class="flex items-center justify-center gap-2">
              <div class="w-6 h-6 rounded-full bg-gradient-to-r from-blue-400 to-cyan-300 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
              </div>
              <span class="text-sm sm:text-base font-medium text-white">Front View</span>
            </div>
          </div>
          
          <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-lg p-3 border border-gray-700/50 backdrop-blur-sm text-center">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">Quality</div>
            <div class="flex items-center justify-center gap-2">
              <div class="w-6 h-6 rounded-full bg-gradient-to-r from-emerald-400 to-green-300 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
              </div>
              <span class="text-sm sm:text-base font-medium text-white">High</span>
            </div>
          </div>
          
          <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-lg p-3 border border-gray-700/50 backdrop-blur-sm text-center">
            <div class="text-xs sm:text-sm text-gray-400 mb-1">Scale</div>
            <div class="flex items-center justify-center gap-2">
              <div class="w-6 h-6 rounded-full bg-gradient-to-r from-purple-400 to-pink-300 flex items-center justify-center">
                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                </svg>
              </div>
              <span class="text-sm sm:text-base font-medium text-white">100%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Color Information Panel (1/3 width on desktop) -->
      <div class="space-y-4 sm:space-y-6">
        <!-- Selected Color Details -->
        <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
            <h3 class="text-lg sm:text-xl font-bold text-white">Selected Color Details</h3>
          </div>
          
          <div class="space-y-3 sm:space-y-4">
            <!-- Color Preview -->
            <div class="flex items-center gap-3 sm:gap-4">
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl border-2 border-gray-600" :style="{ backgroundColor: activeColor.hex }"></div>
              <div class="flex-1 min-w-0">
                <h4 class="text-base sm:text-lg font-semibold text-white mb-1 truncate">{{ activeColor.name }}</h4>
                <p class="text-sm text-gray-300 mb-2">{{ activeColor.description }}</p>
                <div class="flex flex-wrap gap-2">
                  <span class="px-2 py-1 bg-gray-800/50 rounded text-xs font-mono text-gray-300">{{ activeColor.hex }}</span>
                  <span class="px-2 py-1 bg-gray-800/50 rounded text-xs font-mono text-gray-300">RGB({{ activeColor.rgb }})</span>
                  <span class="px-2 py-1 bg-gray-800/50 rounded text-xs font-mono text-gray-300">CMYK({{ activeColor.cmyk }})</span>
                </div>
              </div>
            </div>
            
            <!-- Color Properties -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3">
              <div class="bg-gray-800/30 rounded-lg p-2 sm:p-3">
                <p class="text-xs sm:text-sm text-gray-400 mb-1">Color Family</p>
                <p class="text-sm sm:text-base font-medium text-white">{{ activeColor.family }}</p>
              </div>
              <div class="bg-gray-800/30 rounded-lg p-2 sm:p-3">
                <p class="text-xs sm:text-sm text-gray-400 mb-1">Finish Type</p>
                <p class="text-sm sm:text-base font-medium text-white">{{ activeColor.finish }}</p>
              </div>
              <div class="bg-gray-800/30 rounded-lg p-2 sm:p-3">
                <p class="text-xs sm:text-sm text-gray-400 mb-1">Light Reflectance</p>
                <p class="text-sm sm:text-base font-medium text-white">{{ activeColor.lrv }}%</p>
              </div>
              <div class="bg-gray-800/30 rounded-lg p-2 sm:p-3">
                <p class="text-xs sm:text-sm text-gray-400 mb-1">Opacity</p>
                <p class="text-sm sm:text-base font-medium text-white">{{ activeColor.opacity }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Alternative Colors -->
        <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
            </svg>
            <h3 class="text-lg sm:text-xl font-bold text-white">Suggested Alternatives</h3>
          </div>
          
          <div class="space-y-3">
            <div v-for="color in alternativeColors" :key="color.id" 
                 class="flex items-center gap-3 p-2 sm:p-3 rounded-lg hover:bg-gray-700/30 transition-colors cursor-pointer"
                 @click="selectAlternative(color)">
              <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-lg border border-gray-600" :style="{ backgroundColor: color.hex }"></div>
              <div class="flex-1 min-w-0">
                <h4 class="text-sm sm:text-base font-medium text-white truncate">{{ color.name }}</h4>
                <p class="text-xs text-gray-400 truncate">{{ color.hex }}</p>
              </div>
              <div class="text-xs text-gray-500">
                {{ color.similarity }}% match
              </div>
            </div>
          </div>
        </div>

        <!-- Visualization Notes -->
        <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center gap-2 sm:gap-3 mb-3 sm:mb-4">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg sm:text-xl font-bold text-white">Visualization Notes</h3>
          </div>
          
          <div class="space-y-3">
            <div class="text-sm text-gray-300">
              <p class="mb-2">This is a <span class="text-cyan-300 font-semibold">read-only visualization</span> of your selected wall color in a 3D environment.</p>
              <ul class="space-y-2 text-sm">
                <li class="flex items-start gap-2">
                  <svg class="w-4 h-4 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Colors are displayed in <span class="text-white font-medium">realistic lighting</span> conditions</span>
                </li>
                <li class="flex items-start gap-2">
                  <svg class="w-4 h-4 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span><span class="text-white font-medium">Interactive camera controls</span> allow different viewing angles</span>
                </li>
                <li class="flex items-start gap-2">
                  <svg class="w-4 h-4 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span>Scene lighting can be adjusted to simulate <span class="text-white font-medium">different times of day</span></span>
                </li>
              </ul>
            </div>
            
            <div class="pt-3 border-t border-gray-700/50">
              <div class="text-xs text-gray-400 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>This is a view-only preview. Contact your service provider for color modifications.</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile View Instructions -->
    <div class="lg:hidden mt-4 sm:mt-6">
      <div class="bg-gradient-to-br from-blue-900/20 to-cyan-900/20 rounded-xl sm:rounded-2xl p-4 border border-cyan-500/30 backdrop-blur-sm">
        <div class="flex items-start gap-3">
          <svg class="w-5 h-5 text-cyan-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <div class="flex-1">
            <h4 class="text-sm sm:text-base font-semibold text-white mb-1">Mobile View Instructions</h4>
            <p class="text-xs sm:text-sm text-gray-300">
              For best experience, rotate your device to landscape mode. Use pinch to zoom and drag to rotate the 3D view.
              Unity WebGL may have limited functionality on some mobile browsers.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ColorPreview',
  data() {
    return {
      activeColor: {
        id: 1,
        name: 'Ocean Blue',
        hex: '#3B82F6',
        rgb: '59, 130, 246',
        cmyk: '76, 47, 0, 4',
        family: 'Blue',
        finish: 'Matte',
        lrv: '45',
        opacity: '98',
        description: 'A calming blue shade perfect for living spaces'
      },
      alternativeColors: [
        { id: 1, name: 'Sky Blue', hex: '#60A5FA', similarity: 85 },
        { id: 2, name: 'Royal Blue', hex: '#1D4ED8', similarity: 75 },
        { id: 3, name: 'Teal Accent', hex: '#0D9488', similarity: 60 },
        { id: 4, name: 'Steel Blue', hex: '#475569', similarity: 55 }
      ]
    }
  },
  methods: {
    selectAlternative(color) {
      console.log('Selected alternative color:', color)
      // In a real app, this would update the Unity visualization
      // For demo purposes, we'll just show a notification
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #8b5cf6, #ec4899);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #7c3aed, #db2777);
}

/* Smooth transitions */
* {
  transition: all 0.2s ease-in-out;
}

/* Card hover effects */
.bg-gradient-to-br {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.bg-gradient-to-br:hover {
  transform: translateY(-1px);
}

/* Unity logo animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.animate-pulse {
  animation: pulse 2s infinite;
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-5px);
  }
}

.animate-bounce {
  animation: bounce 1s infinite;
}

/* Backdrop blur for glass effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Gradient text effects */
.text-transparent {
  background-clip: text;
  -webkit-background-clip: text;
}

/* Smooth border transitions */
.border {
  transition: border-color 0.3s ease;
}

/* Text truncation */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Extra small devices (phones, less than 400px) */
@media (max-width: 399px) {
  .min-h-screen {
    padding: 0.75rem;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .h-\[400px\] {
    height: 300px;
  }
}

/* Small devices (phones, 400px to 639px) */
@media (min-width: 400px) and (max-width: 639px) {
  .min-h-screen {
    padding: 1rem;
  }
  
  .text-2xl {
    font-size: 1.75rem;
  }
  
  .h-\[400px\] {
    height: 350px;
  }
}

/* Medium devices (tablets, 640px to 767px) */
@media (min-width: 640px) and (max-width: 767px) {
  .min-h-screen {
    padding: 1.5rem;
  }
  
  .h-\[400px\] {
    height: 400px;
  }
}

/* Large devices (desktops, 768px and up) */
@media (min-width: 768px) {
  .min-h-screen {
    padding: 1.5rem;
  }
}

/* Extra large devices (large desktops, 1024px and up) */
@media (min-width: 1024px) {
  .min-h-screen {
    padding: 2rem;
  }
}

/* Safe area insets for modern mobile devices */
@supports (padding: max(0px)) {
  .min-h-screen {
    padding-left: max(0.75rem, env(safe-area-inset-left));
    padding-right: max(0.75rem, env(safe-area-inset-right));
    padding-top: max(0.75rem, env(safe-area-inset-top));
    padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
  }
}

/* Touch-friendly tap targets */
button {
  min-height: 36px;
  min-width: 36px;
}

/* Prevent text selection on interactive elements */
button {
  user-select: none;
  -webkit-user-select: none;
}

/* Smooth scrolling for the entire page */
html {
  scroll-behavior: smooth;
}

/* Optimize for reduced motion */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .bg-gradient-to-br {
    border-width: 2px;
  }
  
  .text-gray-400 {
    color: #4b5563 !important;
  }
}

/* Container queries for responsive text */
@container (max-width: 400px) {
  .text-xs {
    font-size: 0.7rem;
  }
  
  .text-sm {
    font-size: 0.8rem;
  }
}

/* Responsive font sizes for better readability */
@media (max-width: 380px) {
  .text-lg {
    font-size: 1rem;
  }
  
  .text-xl {
    font-size: 1.125rem;
  }
  
  .text-2xl {
    font-size: 1.375rem;
  }
}

/* Improve touch targets on very small screens */
@media (max-width: 320px) {
  button {
    min-height: 40px;
    min-width: 40px;
  }
  
  .gap-1 {
    gap: 0.25rem;
  }
  
  .gap-2 {
    gap: 0.5rem;
  }
}

/* Landscape mode optimizations */
@media (max-height: 500px) and (orientation: landscape) {
  .min-h-screen {
    padding: 1rem;
  }
  
  .h-\[400px\] {
    height: 250px;
  }
  
  .lg\:col-span-2 {
    height: 60vh;
  }
}

/* Dark mode optimizations */
@media (prefers-color-scheme: dark) {
  .bg-gradient-to-br {
    background-image: linear-gradient(to bottom right, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.5));
  }
}

/* Print styles */
@media print {
  .bg-gradient-to-br {
    background: white !important;
    border: 1px solid #e5e7eb !important;
  }
  
  .text-white {
    color: #1f2937 !important;
  }
  
  .text-gray-400 {
    color: #6b7280 !important;
  }
}

/* Custom color swatch hover effect */
[style*="background-color:"] {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

[style*="background-color:"]:hover {
  transform: scale(1.05);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

/* Unity container specific styles */
.relative > .absolute {
  pointer-events: none;
}

.relative > .absolute + .absolute {
  pointer-events: auto;
}

/* Mobile-specific optimization for 3D container */
@media (max-width: 767px) {
  .h-\[400px\] {
    height: 300px;
  }
  
  .grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Tablet-specific adjustments */
@media (min-width: 768px) and (max-width: 1023px) {
  .lg\:col-span-2 {
    height: 500px;
  }
}
</style>