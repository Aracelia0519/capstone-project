<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4 overflow-hidden relative">
    <!-- Interactive Particle Canvas -->
    <canvas ref="particleCanvas" class="absolute inset-0 w-full h-full pointer-events-none"></canvas>
    
    <!-- Enhanced Paint Background -->
    <div class="absolute inset-0 overflow-hidden">
      <!-- Paint splatter effects with continuous animation -->
      <div class="absolute top-1/4 left-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-blue-500/20 to-cyan-400/20 rounded-full blur-3xl animate-morph-slow"></div>
        <div class="absolute top-12 left-12 w-32 h-32 bg-gradient-to-r from-purple-500/15 to-pink-500/15 rounded-full blur-2xl animate-morph-medium"></div>
        <div class="absolute bottom-0 right-0 w-16 h-16 bg-gradient-to-r from-blue-400/10 to-cyan-300/10 rounded-full blur-xl animate-pulse-delay"></div>
      </div>
      
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-emerald-500/15 to-teal-400/15 rounded-full blur-3xl animate-morph-slow-reverse"></div>
        <div class="absolute bottom-12 right-12 w-32 h-32 bg-gradient-to-r from-amber-500/10 to-yellow-400/10 rounded-full blur-2xl animate-morph-medium-delay"></div>
      </div>

      <!-- Smooth brush strokes with trail effect -->
      <div class="absolute top-1/3 right-1/3 w-64 h-96 opacity-10">
        <div class="absolute w-8 h-40 bg-gradient-to-b from-blue-400 to-transparent rounded-full transform rotate-45 animate-brush-trail"></div>
      </div>
      
      <div class="absolute bottom-1/3 left-1/3 w-64 h-96 opacity-10">
        <div class="absolute w-8 h-32 bg-gradient-to-t from-purple-400 to-transparent rounded-full transform -rotate-12 animate-brush-trail-delay"></div>
      </div>

      <!-- Floating paint droplets with parabolic motion -->
      <div class="absolute top-20 left-20 animate-parabolic-blue">
        <div class="w-6 h-6 bg-gradient-to-r from-blue-400/40 to-cyan-300/40 rounded-full blur-sm"></div>
      </div>
      <div class="absolute top-40 right-40 animate-parabolic-purple">
        <div class="w-5 h-5 bg-gradient-to-r from-purple-400/40 to-pink-300/40 rounded-full blur-sm"></div>
      </div>
      <div class="absolute bottom-20 left-32 animate-parabolic-green">
        <div class="w-7 h-7 bg-gradient-to-r from-emerald-400/40 to-teal-300/40 rounded-full blur-sm"></div>
      </div>
      <div class="absolute bottom-40 right-20 animate-parabolic-yellow">
        <div class="w-5 h-5 bg-gradient-to-r from-amber-400/40 to-yellow-300/40 rounded-full blur-sm"></div>
      </div>

      <!-- Animated gradient orbs -->
      <div class="absolute top-1/2 left-1/4 w-32 h-32 animate-orb-float">
        <div class="w-full h-full bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-full blur-2xl"></div>
      </div>
      
      <div class="absolute bottom-1/3 right-1/4 w-40 h-40 animate-orb-float-delay">
        <div class="w-full h-full bg-gradient-to-r from-pink-500/5 to-yellow-500/5 rounded-full blur-2xl"></div>
      </div>

      <!-- Color ripple effect -->
      <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-1 h-1 animate-ripple"></div>
      </div>
    </div>

    <!-- Enhanced Login Card -->
    <div class="relative w-full max-w-4xl z-10 animate-gentle-float">
      <div class="flex flex-col lg:flex-row bg-gray-800/20 backdrop-blur-2xl rounded-3xl shadow-2xl border border-gray-700/30 overflow-hidden transform transition-all duration-1000 hover:scale-[1.01] hover:shadow-[0_20px_60px_rgba(0,0,0,0.3)]">
        
        <!-- Left Side - Enhanced Visuals -->
        <div class="lg:w-2/5 p-8 lg:p-12 bg-gradient-to-br from-blue-900/15 via-purple-900/15 to-pink-900/15 flex flex-col justify-center items-center relative overflow-hidden group">
          <!-- Dynamic gradient overlay -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5 animate-gradient-shift"></div>
          
          <!-- Animated particles container -->
          <div class="absolute inset-0 pointer-events-none">
            <div class="absolute inset-0 animate-particle-field"></div>
          </div>
          
          <!-- Enhanced Logo Animation -->
          <div class="relative mb-6 z-30 transform transition-all duration-700 group-hover:scale-105">
            <div class="relative">
              <!-- Outer glow ring -->
              <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/30 via-purple-500/30 to-pink-500/30 rounded-3xl blur-xl animate-pulse-glow"></div>
              
              <!-- Logo container -->
              <div class="relative w-24 h-24 rounded-3xl bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 shadow-2xl flex items-center justify-center transform transition-all duration-500 hover:rotate-[360deg] hover:scale-110 animate-soft-spin">
                <svg class="w-12 h-12 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                
                <!-- Paint drip effect -->
                <div class="absolute bottom-0 w-2 h-4 bg-gradient-to-t from-blue-400 to-transparent rounded-b-full animate-drip"></div>
              </div>
            </div>
            
            <div class="mt-6">
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 bg-clip-text text-transparent text-center animate-gradient-text">
                CaviteGo Paint
              </h1>
              <p class="text-gray-300/80 text-center mt-2 animate-fade-in-up">Color Your World Beautifully</p>
            </div>
          </div>

          <!-- Enhanced Feature Cards -->
          <div class="space-y-4 mt-6 w-full max-w-xs">
            <div v-for="(feature, index) in features" :key="index"
                 class="relative overflow-hidden group/feature">
              <!-- Background animation -->
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent translate-x-[-100%] group-hover/feature:translate-x-[100%] transition-transform duration-1000"></div>
              
              <div class="relative flex items-center space-x-3 p-3 rounded-xl bg-gray-900/30 backdrop-blur-sm hover:bg-gray-900/50 transition-all duration-500 cursor-pointer"
                   :style="`animation-delay: ${0.9 + index * 0.2}s`">
                <div class="p-2 rounded-lg bg-gradient-to-r from-blue-500/20 to-cyan-400/20 transform transition-all duration-500 group-hover/feature:scale-110 group-hover/feature:rotate-12">
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-200 transition-all duration-300 group-hover/feature:translate-x-2">{{ feature.title }}</p>
                  <p class="text-xs text-gray-400 transition-all duration-300 group-hover/feature:translate-x-2">{{ feature.subtitle }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Animated Tagline -->
          <div class="mt-8 text-center relative">
            <div class="absolute -inset-x-4 -inset-y-2 bg-gradient-to-r from-transparent via-blue-500/10 to-transparent blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <p class="text-gray-400/70 text-sm italic relative animate-typewriter">"Where Every Color Tells a Story"</p>
          </div>
        </div>

        <!-- Right Side - Enhanced Login Form -->
        <div class="lg:w-3/5 p-8 lg:p-12 relative">
          <!-- Form Header with Animation -->
          <div class="text-center mb-8 relative">
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 w-24 h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-full animate-expand-width"></div>
            <h2 class="text-2xl font-bold text-white mb-2 animate-fade-in-up">Welcome Back</h2>
            <p class="text-gray-400 animate-fade-in-up-delay">Sign in to your account</p>
          </div>

          <!-- Login Form -->
          <form @submit.prevent="handleLogin" class="space-y-6">
            <!-- Email & Password -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <div class="group animate-input-slide-left">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-400 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="group-hover:text-blue-300 transition-colors duration-300">Email Address</span>
                  </div>
                </label>
                <div class="relative">
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="your@email.com"
                    class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500/50 transition-all duration-500 group-hover:border-blue-500/30"
                    :class="validationErrors.email ? 'animate-shake border-red-500/50 focus:ring-red-500/30' : ''"
                  />
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-blue-400 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                  </div>
                </div>
              </div>

              <div class="group animate-input-slide-right">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-purple-400 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path>
                    </svg>
                    <span class="group-hover:text-purple-300 transition-colors duration-300">Password</span>
                  </div>
                </label>
                <div class="relative">
                  <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-purple-500/30 focus:border-purple-500/50 transition-all duration-500 group-hover:border-purple-500/30"
                    :class="validationErrors.password ? 'animate-shake border-red-500/50 focus:ring-red-500/30' : ''"
                  />
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-gray-500 group-hover:text-purple-400 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                  </div>
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-pink-400 transition-all duration-300 hover:scale-110"
                  >
                    <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Remember & Forgot -->
            <div class="flex items-center justify-between animate-fade-in-up">
              <label class="flex items-center space-x-3 cursor-pointer group">
                <div class="relative">
                  <input
                    v-model="form.remember"
                    type="checkbox"
                    class="sr-only"
                  />
                  <div class="w-5 h-5 border-2 border-gray-600 rounded-lg bg-gray-900/50 flex items-center justify-center transition-all duration-300 group-hover:border-blue-400 group-hover:scale-110 group-active:scale-90"
                       :class="form.remember ? 'animate-bounce-check border-blue-400 bg-blue-500/20 scale-110' : ''">
                    <svg v-if="form.remember" class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <span class="text-sm text-gray-300 group-hover:text-blue-300 transition-colors duration-300">Remember me</span>
              </label>
              <button
                type="button"
                @click="handleForgotPassword"
                class="text-sm bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent font-medium hover:from-blue-300 hover:to-purple-300 transition-all duration-500 flex items-center space-x-2 hover:scale-105 group"
              >
                <svg class="w-4 h-4 transform transition-transform duration-500 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                </svg>
                <span class="relative">
                  Forgot password?
                  <span class="absolute bottom-0 left-0 w-0 h-px bg-gradient-to-r from-blue-400 to-purple-400 transition-all duration-300 group-hover:w-full"></span>
                </span>
              </button>
            </div>

            <!-- Enhanced Submit Button -->
            <button
              type="submit"
              :disabled="isLoading"
              class="relative w-full py-3.5 text-white font-semibold rounded-xl shadow-2xl overflow-hidden group transition-all duration-500 hover:scale-[1.02] active:scale-[0.98] animate-button-pulse"
              :class="isLoading ? 'opacity-50 cursor-not-allowed' : ''"
            >
              <!-- Gradient background -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 group-hover:from-blue-600 group-hover:via-purple-600 group-hover:to-pink-600 transition-all duration-500"></div>
              
              <!-- Animated shine -->
              <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-0 left-0 w-12 h-full bg-white/20 skew-x-12 animate-shine"></div>
              </div>
              
              <!-- Ripple effect -->
              <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                <div class="absolute inset-0 animate-ripple-button"></div>
              </div>
              
              <!-- Button content -->
              <div class="relative flex items-center justify-center space-x-3">
                <svg v-if="isLoading" class="w-5 h-5 animate-spin-slow" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span class="transform transition-all duration-500 group-hover:scale-105">
                  {{ isLoading ? 'Signing In...' : 'Sign In to Account' }}
                </span>
              </div>
            </button>

            <!-- Animated Divider -->
            <div class="relative animate-expand-width-delay flex justify-center">
              <div class="w-full max-w-md">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-600/50 to-transparent"></div>
                </div>
                <div class="relative flex justify-center">
                  <span class="px-4 bg-gray-800/80 text-gray-400/80 text-sm animate-bounce-soft">Don't have an account?</span>
                </div>
              </div>
            </div>

            <!-- Register Button -->
            <button
              type="button"
              @click="handleRegister"
              class="relative w-full py-3 text-gray-300 font-medium rounded-xl border-2 border-transparent group overflow-hidden transition-all duration-500 hover:scale-[1.02] active:scale-[0.98] animate-fade-in-up-delay"
            >
              <!-- Gradient border animation -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
              <div class="absolute inset-[2px] bg-gray-800/80 rounded-lg transition-all duration-500 group-hover:bg-gray-800/60"></div>
              
              <!-- Content -->
              <div class="relative flex items-center justify-center space-x-2 group-hover:text-white transition-colors duration-500">
                <svg class="w-5 h-5 transform transition-all duration-500 group-hover:scale-110 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                <span class="transform transition-all duration-500 group-hover:scale-105">Create New Account</span>
              </div>
            </button>
          </form>

          <!-- Footer -->
          <div class="mt-6 pt-4 border-t border-gray-700/30 text-center animate-fade-in-up-delay">
            <p class="text-gray-400/70 text-xs">
              © 2026 CaviteGo Paint • Secure authentication powered by Laravel Sanctum
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Toast Notification -->
    <transition
      enter-active-class="transition-all duration-500 ease-out"
      leave-active-class="transition-all duration-300 ease-in"
      enter-from-class="opacity-0 translate-y-4 scale-95"
      leave-to-class="opacity-0 translate-y-4 scale-95"
    >
      <div
        v-if="showToast"
        class="fixed top-6 right-6 bg-gray-800/90 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-4 max-w-sm z-50 animate-toast-slide"
      >
        <div class="flex items-start space-x-4">
          <div :class="[
            'p-3 rounded-xl transform transition-all duration-500',
            toastType === 'success' ? 'animate-bounce-check bg-gradient-to-br from-emerald-500/20 to-teal-400/20 border border-emerald-500/30' : 
            toastType === 'error' ? 'animate-shake bg-gradient-to-br from-red-500/20 to-pink-400/20 border border-red-500/30' :
            'animate-pulse-glow bg-gradient-to-br from-amber-500/20 to-yellow-400/20 border border-amber-500/30'
          ]">
            <svg v-if="toastType === 'success'" class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else-if="toastType === 'error'" class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <svg v-else class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="flex-1">
            <h5 class="text-sm font-semibold text-white mb-2 animate-fade-in-up">{{ toastTitle }}</h5>
            <p class="text-xs text-gray-300 animate-fade-in-up-delay">{{ toastMessage }}</p>
          </div>
          <button @click="showToast = false" class="text-gray-500 hover:text-gray-300 transition-colors duration-300 transform hover:rotate-90">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios'

const router = useRouter()
const particleCanvas = ref(null)

// Feature data
const features = [
  {
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    title: 'Secure Authentication',
    subtitle: 'Laravel Sanctum powered'
  },
  {
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
    title: 'Role-Based Access',
    subtitle: 'Admin, Distributor, Service, Client'
  },
  {
    icon: 'M13 10V3L4 14h7v7l9-11h-7z',
    title: 'Fast Performance',
    subtitle: 'Instant response times'
  }
]

// Form state
const form = reactive({
  email: '',
  password: '',
  remember: false
})

// UI state
const showPassword = ref(false)
const isLoading = ref(false)
const showToast = ref(false)
const toastTitle = ref('')
const toastMessage = ref('')
const toastType = ref('success')
const validationErrors = reactive({
  email: '',
  password: ''
})

// Enhanced Role-based redirect routes with employee department-based routing
// Enhanced Role-based redirect routes with employee department-based routing
const getRedirectRoute = (user) => {
  const { role, employee_data } = user;
  
  console.log('User data for routing:', user); // Debug log
  
  // Handle hr_manager role (from your database)
  if (role === 'hr_manager') {
    return '/HR/HRdashboard';
  }
  
  // Employee routing based on department
  if (role === 'employee' && employee_data) {
    const department = employee_data.department?.toLowerCase() || '';
    const position = employee_data.position?.toLowerCase() || '';
    
    console.log('Employee department/position:', { department, position }); // Debug log
    
    // Route based on department
    if (department.includes('human resource') || department.includes('hr')) {
      return '/HR/HRdashboard';
    } 
    else if (department.includes('finance') || department.includes('accounting')) {
      return '/finance/financeDashboard';
    }
    else if (department.includes('distributor') || position.includes('distributor assistant')) {
      return '/distributor/distributordashboard';
    }
    else if (department.includes('operational') || position.includes('operational distributor')) {
      return '/ECommerce/ECDashboard';
    }
    // Default employee route
    return '/employee/dashboard';
  }
  
  // Existing role-based routing
  const roleRoutes = {
    admin: '/admin/dashboard',
    distributor: '/distributor/distributordashboard',
    service_provider: '/serviceProvider/dashboardSP',
    client: '/Clients/dashboardC',
    operational_distributor: '/ECommerce/ECDashboard',
    finance_manager: '/finance/financeDashboard',
    hr_manager: '/HR/HRdashboard', // Added this line
    employee: '/employee/dashboard' // Default employee route
  };
  
  console.log('Route for role', role, ':', roleRoutes[role] || '/'); // Debug log
  return roleRoutes[role] || '/';
}

// Particle system
let particles = []
let animationId = null

const initParticleSystem = () => {
  const canvas = particleCanvas.value
  if (!canvas) return
  
  const ctx = canvas.getContext('2d')
  
  // Set canvas size
  const resizeCanvas = () => {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
  }
  
  resizeCanvas()
  window.addEventListener('resize', resizeCanvas)
  
  // Create particles
  const colors = [
    'rgba(59, 130, 246, 0.3)',
    'rgba(139, 92, 246, 0.3)',
    'rgba(236, 72, 153, 0.3)',
    'rgba(34, 197, 94, 0.3)',
    'rgba(245, 158, 11, 0.3)'
  ]
  
  particles = Array.from({ length: 30 }, () => ({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    size: Math.random() * 3 + 1,
    speedX: Math.random() * 0.5 - 0.25,
    speedY: Math.random() * 0.5 - 0.25,
    color: colors[Math.floor(Math.random() * colors.length)],
    opacity: Math.random() * 0.3 + 0.1
  }))
  
  // Animation loop
  const animateParticles = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    
    particles.forEach(particle => {
      particle.x += particle.speedX
      particle.y += particle.speedY
      
      // Bounce off edges
      if (particle.x <= 0 || particle.x >= canvas.width) particle.speedX *= -1
      if (particle.y <= 0 || particle.y >= canvas.height) particle.speedY *= -1
      
      // Draw particle
      ctx.beginPath()
      ctx.fillStyle = particle.color.replace('0.3', particle.opacity)
      ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2)
      ctx.fill()
      
      // Draw glow
      ctx.beginPath()
      ctx.fillStyle = particle.color.replace('0.3', '0.05')
      ctx.arc(particle.x, particle.y, particle.size * 4, 0, Math.PI * 2)
      ctx.fill()
    })
    
    animationId = requestAnimationFrame(animateParticles)
  }
  
  animateParticles()
  
  // Cleanup function
  return () => {
    window.removeEventListener('resize', resizeCanvas)
    if (animationId) cancelAnimationFrame(animationId)
  }
}

// Validation rules
const validateForm = () => {
  let isValid = true
  
  validationErrors.email = ''
  validationErrors.password = ''

  if (!form.email) {
    validationErrors.email = 'Email is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    validationErrors.email = 'Please enter a valid email address'
    isValid = false
  }

  if (!form.password) {
    validationErrors.password = 'Password is required'
    isValid = false
  }

  return isValid
}

// Show notification
const showNotification = (title, message, type = 'success') => {
  toastTitle.value = title
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 5000)
}

// Handle login
const handleLogin = async () => {
  if (!validateForm()) {
    showNotification('Validation Error', 'Please check your inputs', 'error')
    return
  }

  isLoading.value = true

  try {
    const response = await axios.post('/auth/login', {
      email: form.email,
      password: form.password,
      remember: form.remember
    })

    if (response.data.status === 'success') {
      // Store token and user data
      localStorage.setItem('auth_token', response.data.token)
      localStorage.setItem('user_data', JSON.stringify(response.data.user))
      
      showNotification('Success!', 'Redirecting to dashboard...', 'success')
      
      setTimeout(() => {
        const user = response.data.user
        const redirectRoute = getRedirectRoute(user)
        
        // Handle unknown roles or departments
        if (!redirectRoute) {
          console.warn(`No route found for role: ${user.role}, department: ${user.employee_data?.department}`)
          showNotification('Routing Error', 'Could not determine your dashboard route. Contact support.', 'error')
          return
        }
        
        router.push(redirectRoute)
      }, 1500)
    } else {
      showNotification('Login Failed', response.data.message || 'Login failed', 'error')
    }

  } catch (error) {
    if (error.response && error.response.data) {
      const { data } = error.response
      showNotification('Login Failed', data.message || 'Login failed', 'error')
    } else {
      showNotification('Error', 'Network error. Please try again.', 'error')
    }
  } finally {
    isLoading.value = false
  }
}

// Handle forgot password
const handleForgotPassword = () => {
  showNotification('Feature Coming Soon', 'Password recovery feature is under development', 'info')
}

// Handle register
const handleRegister = () => {
  router.push('/Landing/signUp')
}

// Initialize animations and particle system
onMounted(() => {
  const cleanup = initParticleSystem()
  
  // Trigger animations
  setTimeout(() => {
    const animations = [
      '.animate-gentle-float',
      '.animate-input-slide-left',
      '.animate-input-slide-right',
      '.animate-fade-in-up',
      '.animate-fade-in-up-delay',
      '.animate-button-pulse',
      '.animate-expand-width',
      '.animate-expand-width-delay'
    ];
    
    animations.forEach(selector => {
      const elements = document.querySelectorAll(selector);
      elements.forEach(el => {
        el.classList.add('animation-complete');
      });
    });
  }, 100);
  
  onUnmounted(() => {
    if (cleanup) cleanup()
  })
})

</script>

<style scoped>
  @import "../landingPage/styleFolder/logIn.css";
</style>