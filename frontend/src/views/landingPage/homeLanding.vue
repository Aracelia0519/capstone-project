<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Hero Section -->
    <section class="relative overflow-hidden py-12 md:py-20 px-4 sm:px-6 lg:px-8">
      <!-- Background Elements -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-0 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl"></div>
      </div>
      
      <div class="relative max-w-7xl mx-auto">
        <!-- Main Content -->
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Left Column - Text Content -->
          <div class="space-y-8">
            <!-- Title and Badge -->
            <div class="space-y-4">
              <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20">
                <svg class="w-4 h-4 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span class="text-sm font-medium text-blue-300">Integrated Management System</span>
              </div>
              
              <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight">
                <span class="block bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
                  CaviteGo Paint
                </span>
                <span class="block text-white mt-2">Color Intelligence Platform</span>
              </h1>
              
              <p class="text-xl text-gray-300 leading-relaxed">
                A comprehensive platform integrating virtual paint visualization with intelligent distribution management for Cavite's paint industry.
              </p>
            </div>

            <!-- Key Features -->
            <div class="grid grid-cols-2 gap-4">
              <div class="flex items-center space-x-3 p-4 rounded-xl bg-gray-800/50 backdrop-blur-sm border border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                  </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">AI Color Matching</span>
              </div>
              
              <div class="flex items-center space-x-3 p-4 rounded-xl bg-gray-800/50 backdrop-blur-sm border border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center">
                  <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">Virtual Try-On</span>
              </div>
              
              <div class="flex items-center space-x-3 p-4 rounded-xl bg-gray-800/50 backdrop-blur-sm border border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-pink-500/10 flex items-center justify-center">
                  <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">Real-time Analytics</span>
              </div>
              
              <div class="flex items-center space-x-3 p-4 rounded-xl bg-gray-800/50 backdrop-blur-sm border border-gray-700">
                <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center">
                  <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <span class="text-sm font-medium text-gray-200">24/7 Support</span>
              </div>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 pt-4">
              <button 
                @click="$router.push('/explore-colors')"
                class="group px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl font-semibold shadow-xl hover:shadow-2xl transition-all duration-300 hover:scale-105 flex items-center justify-center space-x-3"
              >
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                <span>Explore Paint Colors</span>
              </button>
              
              <div class="flex gap-3">
                <button 
                  @click="$router.push('/login')"
                  class="flex-1 px-6 py-4 border-2 border-gray-700 text-gray-300 rounded-xl font-semibold hover:bg-gray-800/50 transition-all duration-300 flex items-center justify-center space-x-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                  </svg>
                  <span>Login</span>
                </button>
                
                <button 
                  @click="$router.push('/register')"
                  class="flex-1 px-6 py-4 bg-gray-800 text-gray-300 rounded-xl font-semibold hover:bg-gray-700 transition-all duration-300 flex items-center justify-center space-x-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                  </svg>
                  <span>Register</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Right Column - Visualization -->
          <div class="relative">
            <!-- House Preview -->
            <div class="relative rounded-2xl overflow-hidden shadow-2xl border border-gray-700 bg-gradient-to-br from-gray-800 to-gray-900">
              <div class="p-6">
                <!-- House Structure -->
                <div class="relative h-64 md:h-80 w-full mx-auto">
                  <!-- Roof -->
                  <div class="absolute top-0 left-1/4 w-1/2 h-12 bg-gradient-to-r from-gray-700 to-gray-800 rounded-t-lg transform -translate-x-1/2"></div>
                  
                  <!-- House Body -->
                  <div class="absolute top-12 left-1/2 transform -translate-x-1/2 w-3/4 h-48 bg-gradient-to-b from-gray-800 to-gray-900 rounded-lg border border-gray-700">
                    <!-- Wall Sections with Color Preview -->
                    <div class="absolute top-4 left-4 w-16 h-32 rounded bg-gradient-to-br from-blue-500/20 to-blue-700/20 border border-blue-500/30">
                      <div class="absolute inset-0 bg-gradient-to-b from-blue-400/40 to-transparent"></div>
                    </div>
                    <div class="absolute top-4 right-4 w-16 h-32 rounded bg-gradient-to-br from-purple-500/20 to-purple-700/20 border border-purple-500/30">
                      <div class="absolute inset-0 bg-gradient-to-b from-purple-400/40 to-transparent"></div>
                    </div>
                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-24 h-16 rounded bg-gradient-to-br from-pink-500/20 to-pink-700/20 border border-pink-500/30">
                      <div class="absolute inset-0 bg-gradient-to-b from-pink-400/40 to-transparent"></div>
                    </div>
                  </div>
                  
                  <!-- Windows -->
                  <div class="absolute top-20 left-1/4 w-6 h-8 bg-blue-400/20 rounded border border-blue-400/30"></div>
                  <div class="absolute top-20 right-1/4 w-6 h-8 bg-blue-400/20 rounded border border-blue-400/30"></div>
                  
                  <!-- Door -->
                  <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2 w-12 h-20 bg-gradient-to-b from-gray-700 to-gray-800 rounded-t-lg"></div>
                </div>
                
                <!-- Color Picker Controls -->
                <div class="mt-8 space-y-4">
                  <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-300">Wall Color Preview</span>
                    <div class="flex items-center space-x-2">
                      <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                      <div class="w-3 h-3 rounded-full bg-purple-500"></div>
                      <div class="w-3 h-3 rounded-full bg-pink-500"></div>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-3 gap-2">
                    <div class="text-center">
                      <div class="w-full h-2 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 mb-2"></div>
                      <span class="text-xs text-blue-300">Blue Tones</span>
                    </div>
                    <div class="text-center">
                      <div class="w-full h-2 rounded-full bg-gradient-to-r from-purple-400 to-purple-600 mb-2"></div>
                      <span class="text-xs text-purple-300">Purple Tones</span>
                    </div>
                    <div class="text-center">
                      <div class="w-full h-2 rounded-full bg-gradient-to-r from-pink-400 to-pink-600 mb-2"></div>
                      <span class="text-xs text-pink-300">Pink Tones</span>
                    </div>
                  </div>
                  
                  <button 
                    @click="$router.push('/virtual-try-on')"
                    class="w-full py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center space-x-2"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Launch Virtual Try-On</span>
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Floating Stats Cards -->
            <div class="absolute -bottom-6 left-0 right-0 flex justify-center gap-4">
              <div class="px-4 py-3 bg-gradient-to-r from-blue-500/10 to-blue-600/10 backdrop-blur-sm rounded-xl border border-blue-500/20 shadow-lg">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <span class="text-sm font-medium text-blue-300">50+ Distributors</span>
                </div>
              </div>
              <div class="px-4 py-3 bg-gradient-to-r from-purple-500/10 to-purple-600/10 backdrop-blur-sm rounded-xl border border-purple-500/20 shadow-lg">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                  <span class="text-sm font-medium text-purple-300">1000+ Colors</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Quick Stats Section -->
    <section class="py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Stats Card 1 -->
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700 hover:border-blue-500/30 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-gray-400">Active Service Providers</p>
                <p class="text-3xl font-bold text-white mt-2">128</p>
                <div class="flex items-center mt-2">
                  <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                  <span class="text-sm text-green-400 ml-1">+12% this month</span>
                </div>
              </div>
              <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Stats Card 2 -->
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700 hover:border-purple-500/30 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-gray-400">Color Mixes Generated</p>
                <p class="text-3xl font-bold text-white mt-2">2,847</p>
                <div class="flex items-center mt-2">
                  <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                  <span class="text-sm text-green-400 ml-1">+24% this month</span>
                </div>
              </div>
              <div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
              </div>
            </div>
          </div>
          
          <!-- Stats Card 3 -->
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700 hover:border-pink-500/30 transition-all duration-300">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-gray-400">Recommendations</p>
                <p class="text-3xl font-bold text-white mt-2">5,623</p>
                <div class="flex items-center mt-2">
                  <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                  <span class="text-sm text-green-400 ml-1">+18% this month</span>
                </div>
              </div>
              <div class="w-12 h-12 rounded-xl bg-pink-500/10 flex items-center justify-center">
                <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

// You can add any reactive data or methods here if needed
// For example, animation triggers or data fetching

const features = [
  {
    title: 'Virtual Paint Mixing',
    description: 'Real-time color visualization using Unity WebGL',
    icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
  },
  {
    title: 'Decision Support System',
    description: 'AI-powered color recommendations and analytics',
    icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'
  },
  {
    title: 'Mobile Integration',
    description: 'Cross-platform mobile app using Capacitor',
    icon: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'
  }
]
</script>

<style scoped>
/* Custom animations */
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

/* Gradient text animation */
.gradient-text {
  background-size: 200% 200%;
  animation: gradient 3s ease infinite;
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

/* Glass morphism effect */
.glass-effect {
  background: rgba(30, 41, 59, 0.4);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Hover effects */
.hover-lift {
  transition: transform 0.3s ease;
}

.hover-lift:hover {
  transform: translateY(-4px);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.4);
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}
</style>