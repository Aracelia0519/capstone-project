<template>
  <div class="min-h-screen relative flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat overflow-hidden"
       style="background-image: url('/hero-paint-store.jpg');">

    <!-- ===== CANVAS PARTICLE BACKGROUND ===== -->
    <canvas ref="particleCanvas" class="absolute inset-0 w-full h-full pointer-events-none z-0"></canvas>

    <!-- ===== DARK OVERLAY ===== -->
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm z-0"></div>

    <!-- ===== AMBIENT GLOW ORBS ===== -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
      <div class="absolute top-1/4 left-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-blue-500/15 to-cyan-400/15 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute top-12 left-12 w-32 h-32 bg-gradient-to-r from-purple-500/10 to-pink-500/10 rounded-full blur-2xl animate-float-medium"></div>
      </div>
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-emerald-500/10 to-teal-400/10 rounded-full blur-3xl animate-float-slow delay-1000"></div>
        <div class="absolute bottom-12 right-12 w-32 h-32 bg-gradient-to-r from-amber-500/8 to-yellow-400/8 rounded-full blur-2xl animate-float-medium delay-500"></div>
      </div>
      <div class="absolute top-1/2 left-3/4 w-64 h-64">
        <div class="absolute w-40 h-40 bg-gradient-to-r from-indigo-500/8 to-blue-400/8 rounded-full blur-3xl animate-float-slow delay-1500"></div>
      </div>
    </div>

    <!-- ===== CURSOR GLOW ===== -->
    <div ref="cursorGlow"
         class="fixed pointer-events-none z-0 w-80 h-80 rounded-full bg-gradient-to-r from-blue-500/10 via-purple-500/10 to-pink-500/10 blur-3xl transition-all duration-300"
         style="transform: translate(-50%, -50%); opacity: 0;"></div>

    <!-- ===== MAIN CARD WITH 3D TILT ===== -->
    <div ref="signupCard" class="relative w-full max-w-5xl z-10 [perspective:1200px]">
      <div ref="card3d" class="relative transition-transform duration-200 ease-out [transform-style:preserve-3d] will-change-transform">

        <!-- ===== ANIMATED GRADIENT BORDER ===== -->
        <div class="absolute -inset-[2px] rounded-2xl bg-gradient-to-r from-blue-500/60 via-purple-500/60 to-pink-500/60 animate-border-rotate blur-sm"></div>
        <div class="absolute -inset-[2px] rounded-2xl bg-gradient-to-r from-blue-500/30 via-purple-500/30 to-pink-500/30 animate-border-rotate blur-xl opacity-70"></div>

        <!-- ===== CARD BODY ===== -->
        <div class="relative flex flex-col lg:flex-row bg-gray-900/90 backdrop-blur-2xl rounded-2xl shadow-2xl overflow-hidden border border-gray-700/30 [transform:translateZ(0)]">

          <!-- ===== GLASS REFLECTION SHIMMER ===== -->
          <div class="absolute inset-0 pointer-events-none overflow-hidden rounded-2xl bg-gradient-to-br from-white/5 via-transparent to-transparent opacity-0 hover:opacity-100 transition-opacity duration-1000">
            <div class="absolute -top-1/2 -left-1/2 w-[200%] h-[200%] bg-gradient-to-br from-white/8 via-transparent to-transparent rotate-12 animate-shimmer"></div>
          </div>

          <!-- ===== LEFT SIDE: BRANDING ===== -->
          <div class="lg:w-2/5 p-8 bg-gradient-to-br from-gray-800/80 to-gray-900/80 flex flex-col justify-center items-center relative overflow-hidden">
            <!-- Decorative floating icons -->
            <div class="absolute top-4 right-4 opacity-20 animate-float-slow">
              <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            <div class="absolute bottom-4 left-4 opacity-20 animate-float-medium delay-700">
              <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>

            <div class="relative mb-6">
              <div class="relative">
                <img src="/favicon.svg" class="w-20 h-20 mx-auto" alt="icon" />
                <div class="absolute -inset-4 rounded-full bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 blur-xl animate-pulse"></div>
              </div>
              <h1 class="text-3xl font-bold text-white text-center mt-4 bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 bg-clip-text text-transparent">
                CaviteGo Paint
              </h1>
              <p class="text-gray-400 text-center mt-2">Color Your World Beautifully</p>
            </div>

            <div class="space-y-4 mt-6 w-full max-w-xs">
              <div v-for="(feature, index) in features" :key="index"
                   class="flex items-center space-x-3 p-3 rounded-xl bg-gray-800/60 shadow-sm hover:shadow-md transition-all duration-300 hover:scale-[1.02] hover:bg-gray-800/80 border border-gray-700/30 group">
                <div class="p-2 rounded-lg bg-blue-900/40 text-blue-400 group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-200 group-hover:text-white transition-colors">{{ feature.title }}</p>
                  <p class="text-xs text-gray-400">{{ feature.subtitle }}</p>
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-gradient-to-r from-blue-400 to-purple-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              </div>
            </div>

            <div class="mt-8 text-center">
              <p class="text-gray-500 text-sm italic">"Where Every Color Tells a Story"</p>
            </div>
          </div>

          <!-- ===== RIGHT SIDE: LOGIN FORM ===== -->
          <div class="lg:w-3/5 p-8 lg:p-10 bg-gray-900/90 relative">
            <!-- Decorative corner accent -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-500/5 to-transparent rounded-full blur-2xl"></div>

            <div class="text-center mb-8">
              <h2 class="text-2xl font-bold text-white mb-2 flex items-center justify-center gap-2">
                <span class="inline-block w-1.5 h-1.5 rounded-full bg-gradient-to-r from-blue-400 to-purple-400 animate-pulse"></span>
                Welcome Back
              </h2>
              <p class="text-gray-400">Sign in to your account</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-6">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>Email Address</span>
                  </div>
                </label>
                <div class="relative group">
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="your@email.com"
                    class="w-full px-4 py-3 pl-11 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-200 group-hover:border-gray-600"
                    :class="validationErrors.email ? 'border-red-500 focus:ring-red-500/50' : ''"
                  />
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 group-focus-within:text-blue-400 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                  </div>
                  <!-- Validation icon -->
                  <div v-if="form.email && !validationErrors.email" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-green-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <p v-if="validationErrors.email" class="text-xs text-red-400 mt-1 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  {{ validationErrors.email }}
                </p>
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path>
                    </svg>
                    <span>Password</span>
                  </div>
                </label>
                <div class="relative group">
                  <input
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="••••••••"
                    class="w-full px-4 py-3 pl-11 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-200 group-hover:border-gray-600"
                    :class="validationErrors.password ? 'border-red-500 focus:ring-red-500/50' : ''"
                  />
                  <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 group-focus-within:text-purple-400 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                  </div>
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors duration-200"
                  >
                    <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <!-- Validation icon -->
                  <div v-if="form.password && !validationErrors.password" class="absolute right-12 top-1/2 transform -translate-y-1/2 text-green-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>
                <p v-if="validationErrors.password" class="text-xs text-red-400 mt-1 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                  {{ validationErrors.password }}
                </p>
              </div>

              <!-- Remember & Forgot -->
              <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 cursor-pointer group">
                  <input
                    v-model="form.remember"
                    type="checkbox"
                    class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-blue-500 focus:ring-blue-500 focus:ring-offset-0 transition-all duration-200 group-hover:border-gray-400"
                  />
                  <span class="text-sm text-gray-400 group-hover:text-gray-300 transition-colors">Remember me</span>
                </label>
                <button
                  type="button"
                  @click="handleForgotPassword"
                  class="text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors duration-200 hover:underline"
                >
                  Forgot password?
                </button>
              </div>

              <!-- Submit -->
              <button
                type="submit"
                :disabled="isLoading"
                class="relative overflow-hidden w-full py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center group"
              >
                <div class="absolute inset-0 bg-white/20 skew-x-12 -translate-x-full group-hover:translate-x-[200%] transition-all duration-700"></div>
                <span v-if="isLoading" class="flex items-center">
                  <span class="animate-spin w-5 h-5 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                  Authenticating...
                </span>
                <span v-else class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                  </svg>
                  Sign In to Account
                </span>
              </button>

              <!-- Divider -->
              <div class="relative text-center">
                <div class="absolute inset-0 flex items-center">
                  <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
                </div>
                <div class="relative flex justify-center">
                  <span class="px-4 bg-gray-900 text-gray-400 text-sm">Don't have an account?</span>
                </div>
              </div>

              <!-- Register -->
              <button
                type="button"
                @click="handleRegister"
                class="w-full py-3 text-gray-300 font-medium rounded-lg border border-gray-700 hover:bg-gray-800 hover:border-gray-600 transition-all duration-300 group"
              >
                <span class="flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 text-gray-500 group-hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                  </svg>
                  Create New Account
                </span>
              </button>
            </form>

            <!-- Footer -->
            <div class="mt-6 pt-4 border-t border-gray-800 text-center">
              <p class="text-gray-500 text-xs flex items-center justify-center gap-2">
                <span class="w-1 h-1 rounded-full bg-blue-400/50"></span>
                © 2026 CaviteGo Paint
                <span class="w-1 h-1 rounded-full bg-purple-400/50"></span>
                Secure authentication powered by Laravel Sanctum
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== OTP CHOICES MODAL ===== -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 scale-95"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="requiresOtpChoice" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-md p-8 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5"></div>
          <div class="relative">
            <div class="w-14 h-14 bg-indigo-900/30 text-indigo-400 rounded-full flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(99,102,241,0.15)]">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">2-Step Verification</h3>
            <p class="text-gray-400 text-sm mb-6">{{ otpMessage }}</p>

            <form @submit.prevent="handleSendOtp">
              <div class="space-y-4 mb-6">
                <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-all duration-300"
                       :class="selectedOtpTarget === 'primary' ? 'border-indigo-500 bg-indigo-900/20 shadow-[0_0_20px_rgba(99,102,241,0.1)]' : 'border-gray-700 bg-gray-800 hover:bg-gray-700/50'">
                  <input type="radio" v-model="selectedOtpTarget" value="primary"
                         class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900" />
                  <div class="ml-3">
                    <span class="block text-white font-medium">Primary Email</span>
                    <span class="block text-gray-400 text-sm">{{ otpEmails.primary }}</span>
                  </div>
                  <div v-if="selectedOtpTarget === 'primary'" class="ml-auto w-2 h-2 rounded-full bg-indigo-400 animate-pulse shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
                </label>

                <label v-if="otpEmails.recovery" class="flex items-center p-4 border rounded-xl cursor-pointer transition-all duration-300"
                       :class="selectedOtpTarget === 'recovery' ? 'border-indigo-500 bg-indigo-900/20 shadow-[0_0_20px_rgba(99,102,241,0.1)]' : 'border-gray-700 bg-gray-800 hover:bg-gray-700/50'">
                  <input type="radio" v-model="selectedOtpTarget" value="recovery"
                         class="w-4 h-4 text-indigo-600 bg-gray-700 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900" />
                  <div class="ml-3">
                    <span class="block text-white font-medium">Recovery Email</span>
                    <span class="block text-gray-400 text-sm">{{ otpEmails.recovery }}</span>
                  </div>
                  <div v-if="selectedOtpTarget === 'recovery'" class="ml-auto w-2 h-2 rounded-full bg-indigo-400 animate-pulse shadow-[0_0_10px_rgba(99,102,241,0.5)]"></div>
                </label>
              </div>

              <div class="flex justify-end space-x-3">
                <button type="button" @click="requiresOtpChoice = false"
                        class="px-5 py-2.5 text-gray-400 hover:text-white transition-colors duration-200">
                  Cancel
                </button>
                <button type="submit" :disabled="sendingOtp"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-300 disabled:opacity-50 flex items-center shadow-lg shadow-indigo-500/20">
                  <span v-if="sendingOtp" class="animate-spin w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                  Send Code
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- ===== OTP INPUT MODAL ===== -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 scale-95"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="requiresOtpInput" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-md p-8 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-purple-500/5 to-pink-500/5"></div>
          <div class="relative">
            <div class="w-14 h-14 bg-indigo-900/30 text-indigo-400 rounded-full flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(99,102,241,0.15)]">
              <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Enter Verification Code</h3>
            <p class="text-gray-400 text-sm mb-6">
              Enter the 6-digit code we just sent to
              <strong class="text-gray-200">{{ selectedOtpTarget === 'primary' ? otpEmails.primary : otpEmails.recovery }}</strong>.
            </p>

            <form @submit.prevent="handleVerifyOtp">
              <div class="mb-6">
                <input
                  v-model="otpCode"
                  type="text"
                  maxlength="6"
                  required
                  placeholder="••••••"
                  class="w-full px-4 py-4 bg-gray-800 border border-gray-700 rounded-xl text-white text-center text-3xl tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition placeholder:text-gray-600"
                />
              </div>

              <div class="flex justify-end space-x-3">
                <button type="button" @click="requiresOtpInput = false; requiresOtpChoice = true"
                        class="px-5 py-2.5 text-gray-400 hover:text-white transition-colors duration-200">
                  Back
                </button>
                <button type="submit" :disabled="verifyingOtp || otpCode.length < 6"
                        class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-medium rounded-lg transition-all duration-300 disabled:opacity-50 flex items-center shadow-lg shadow-indigo-500/20">
                  <span v-if="verifyingOtp" class="animate-spin w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                  Verify Code
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- ===== SECURITY QUESTIONS MODAL ===== -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 scale-95"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="requiresSecurityCheck" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-md p-8 relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5"></div>
          <div class="relative">
            <div class="w-14 h-14 bg-blue-900/30 text-blue-400 rounded-full flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(59,130,246,0.15)]">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Security Verification</h3>
            <p class="text-gray-400 text-sm mb-6">Unrecognized device detected. To protect your account, please answer your security question.</p>

            <form @submit.prevent="handleSecurityVerification">
              <div class="mb-6">
                <label class="block text-sm font-semibold text-blue-400 mb-2">{{ securityQuestion.text }}</label>
                <input
                  v-model="securityAnswer"
                  type="text"
                  required
                  placeholder="Type your secret answer"
                  class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>

              <div class="flex justify-end space-x-3">
                <button type="button" @click="requiresSecurityCheck = false"
                        class="px-5 py-2.5 text-gray-400 hover:text-white transition-colors duration-200">
                  Cancel
                </button>
                <button type="submit" :disabled="verifyingSecurity"
                        class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg transition-all duration-300 disabled:opacity-50 flex items-center shadow-lg shadow-blue-500/20">
                  <span v-if="verifyingSecurity" class="animate-spin w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                  Verify & Login
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </transition>

    <!-- ===== TOAST NOTIFICATION ===== -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 translate-y-4 scale-95"
      leave-to-class="opacity-0 translate-y-4 scale-95"
    >
      <div
        v-if="showToast"
        class="fixed top-6 right-6 bg-gray-800/90 backdrop-blur-xl shadow-2xl rounded-lg border border-gray-700 p-4 max-w-sm z-50"
      >
        <div class="flex items-start space-x-3">
          <div :class="[
            'p-2 rounded-full',
            toastType === 'success' ? 'bg-green-900/50 text-green-400' :
            toastType === 'error' ? 'bg-red-900/50 text-red-400' :
            'bg-amber-900/50 text-amber-400'
          ]">
            <svg v-if="toastType === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else-if="toastType === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="flex-1">
            <h5 class="text-sm font-semibold text-white">{{ toastTitle }}</h5>
            <p class="text-xs text-gray-300">{{ toastMessage }}</p>
          </div>
          <button @click="showToast = false" class="text-gray-500 hover:text-gray-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </transition>

    <!-- ===== CLIENT OPTIONS MODAL ===== -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 scale-95"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="showClientOptions" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 relative border border-gray-700 overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5"></div>
          <div class="relative">
            <div class="text-center mb-6">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500/20 to-purple-500/20 rounded-full flex items-center justify-center mx-auto mb-3 shadow-[0_0_30px_rgba(59,130,246,0.1)]">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-white">Welcome, Client!</h3>
              <p class="text-gray-400 text-sm">Where would you like to go?</p>
            </div>

            <div class="grid grid-cols-1 gap-4">
              <button
                @click="navigateToClientRoute('/ECommerceClient/EccommerceShop')"
                class="flex items-center justify-between p-4 rounded-xl border border-blue-800 bg-blue-900/30 hover:bg-blue-900/50 transition-all duration-300 group hover:scale-[1.02] hover:shadow-lg hover:shadow-blue-500/10"
              >
                <div class="flex items-center space-x-3">
                  <div class="p-2 bg-blue-800/50 rounded-lg text-blue-300 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                  </div>
                  <div class="text-left">
                    <h4 class="text-white font-semibold">E-Commerce</h4>
                    <p class="text-sm text-gray-400">Shop for products</p>
                  </div>
                </div>
                <svg class="w-4 h-4 text-gray-500 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>

              <button
                @click="navigateToClientRoute('/Clients/dashboardC')"
                class="flex items-center justify-between p-4 rounded-xl border border-purple-800 bg-purple-900/30 hover:bg-purple-900/50 transition-all duration-300 group hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/10"
              >
                <div class="flex items-center space-x-3">
                  <div class="p-2 bg-purple-800/50 rounded-lg text-purple-300 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                  <div class="text-left">
                    <h4 class="text-white font-semibold">Management</h4>
                    <p class="text-sm text-gray-400">Manage your account</p>
                  </div>
                </div>
                <svg class="w-4 h-4 text-gray-500 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios'

const router = useRouter()

// ===== REFS =====
const particleCanvas = ref(null)
const cursorGlow = ref(null)
const signupCard = ref(null)
const card3d = ref(null)

// UI Security states
const requiresSecurityCheck = ref(false)
const verifyingSecurity = ref(false)
const securityQuestion = reactive({ key: '', text: '' })
const securityAnswer = ref('')

// OTP Security States
const requiresOtpChoice = ref(false)
const requiresOtpInput = ref(false)
const sendingOtp = ref(false)
const verifyingOtp = ref(false)
const otpEmails = reactive({ primary: '', recovery: '' })
const selectedOtpTarget = ref('primary')
const otpCode = ref('')
const otpMessage = ref('Unrecognized device detected. Choose where we should send your verification code.')

// Client modal state
const showClientOptions = ref(false)

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

// ===== PARTICLE SYSTEM =====
let particles = []
let particleAnimationId = null

const initParticles = () => {
  const canvas = particleCanvas.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  const resize = () => {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
  }
  window.addEventListener('resize', resize)
  resize()

  const count = 70
  particles = []
  for (let i = 0; i < count; i++) {
    particles.push({
      x: Math.random() * canvas.width,
      y: Math.random() * canvas.height,
      vx: (Math.random() - 0.5) * 0.25,
      vy: (Math.random() - 0.5) * 0.25,
      r: Math.random() * 2 + 0.5,
      alpha: Math.random() * 0.35 + 0.05,
      color: ['rgba(59,130,246,', 'rgba(168,85,247,', 'rgba(236,72,153,', 'rgba(16,185,129,', 'rgba(245,158,11,'][Math.floor(Math.random() * 5)]
    })
  }

  let mouseX = canvas.width / 2
  let mouseY = canvas.height / 2

  const animate = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    for (const p of particles) {
      p.x += p.vx
      p.y += p.vy
      if (p.x < 0 || p.x > canvas.width) p.vx *= -1
      if (p.y < 0 || p.y > canvas.height) p.vy *= -1
      const dx = p.x - mouseX
      const dy = p.y - mouseY
      const dist = Math.sqrt(dx * dx + dy * dy)
      if (dist < 150) {
        const force = (150 - dist) / 150 * 0.4
        p.x += (dx / dist) * force
        p.y += (dy / dist) * force
      }
      ctx.beginPath()
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2)
      ctx.fillStyle = p.color + p.alpha + ')'
      ctx.fill()
    }
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x
        const dy = particles[i].y - particles[j].y
        const dist = Math.sqrt(dx * dx + dy * dy)
        if (dist < 100) {
          ctx.beginPath()
          ctx.moveTo(particles[i].x, particles[i].y)
          ctx.lineTo(particles[j].x, particles[j].y)
          ctx.strokeStyle = `rgba(255,255,255,${0.04 * (1 - dist / 100)})`
          ctx.lineWidth = 0.5
          ctx.stroke()
        }
      }
    }
    particleAnimationId = requestAnimationFrame(animate)
  }
  animate()

  const onMouseMove = (e) => {
    mouseX = e.clientX
    mouseY = e.clientY
  }
  window.addEventListener('mousemove', onMouseMove)
  return () => {
    window.removeEventListener('resize', resize)
    window.removeEventListener('mousemove', onMouseMove)
    if (particleAnimationId) cancelAnimationFrame(particleAnimationId)
  }
}

// ===== 3D CARD TILT =====
const init3DTilt = () => {
  const card = card3d.value
  if (!card) return
  let isHovering = false

  const onMouseMove = (e) => {
    if (!isHovering) return
    const rect = card.getBoundingClientRect()
    const x = (e.clientX - rect.left) / rect.width - 0.5
    const y = (e.clientY - rect.top) / rect.height - 0.5
    const rotateX = -y * 4
    const rotateY = x * 4
    card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`
    if (cursorGlow.value) {
      cursorGlow.value.style.left = e.clientX + 'px'
      cursorGlow.value.style.top = e.clientY + 'px'
      cursorGlow.value.style.opacity = '1'
    }
  }

  const onMouseEnter = () => { isHovering = true }
  const onMouseLeave = () => {
    isHovering = false
    card.style.transform = 'rotateX(0deg) rotateY(0deg)'
    if (cursorGlow.value) cursorGlow.value.style.opacity = '0'
  }

  card.addEventListener('mousemove', onMouseMove)
  card.addEventListener('mouseenter', onMouseEnter)
  card.addEventListener('mouseleave', onMouseLeave)
  return () => {
    card.removeEventListener('mousemove', onMouseMove)
    card.removeEventListener('mouseenter', onMouseEnter)
    card.removeEventListener('mouseleave', onMouseLeave)
  }
}

// ===== ROLE-BASED ROUTING =====
const getRedirectRoute = (user) => {
  const { role, employee_data } = user;
  if (role === 'hr_manager') return '/HR/HRdashboard';
  if (role === 'employee' && employee_data) {
    const department = employee_data.department?.toLowerCase() || '';
    const position = employee_data.position?.toLowerCase() || '';
    if (department.includes('special rbac') || department.includes('special')) return '/special-rbac/dashboard';
    else if (department.includes('human resource') || department.includes('hr')) return '/HR/HRdashboard';
    else if (department.includes('finance') || department.includes('accounting')) return '/finance/financeDashboard';
    else if (department.includes('operational') || position.includes('operational distributor')) return '/ECommerce/ECDashboard';
    else if (department.includes('distributor') || position.includes('distributor assistant')) return '/distributor/distributordashboard';
    return '/employee/dashboard';
  }
  const roleRoutes = {
    admin: '/admin/dashboard',
    distributor: '/distributor/distributordashboard',
    service_provider: '/serviceProvider/dashboardSP',
    client: '/Clients/dashboardC',
    operational_distributor: '/ECommerce/ECDashboard',
    finance_manager: '/finance/financeDashboard',
    hr_manager: '/HR/HRdashboard',
    employee: '/employee/dashboard',
    supplier: '/Supplier/SupplierDashboard',
    personnel_officer: '/Supplier/SupplierDashboard',
    supplier_employee: '/Supplier/SupplierDashboard',
    personnel_employee: '/Supplier/SupplierDashboard'
  };
  return roleRoutes[role] || '/';
}

// ===== VALIDATION =====
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

// ===== NOTIFICATIONS =====
const showNotification = (title, message, type = 'success') => {
  toastTitle.value = title
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 5000)
}

// ===== CLIENT ROUTING =====
const navigateToClientRoute = (route) => {
  showClientOptions.value = false
  router.push(route)
}

const proceedWithLoginSuccess = (user, token) => {
  localStorage.setItem('auth_token', token)
  localStorage.setItem('user_data', JSON.stringify(user))
  if (user.role === 'client') {
    showNotification('Success!', 'Login successful. Please choose your destination.', 'success')
    setTimeout(() => { showClientOptions.value = true }, 1000)
  } else {
    showNotification('Success!', 'Redirecting to dashboard...', 'success')
    setTimeout(() => {
      const redirectRoute = getRedirectRoute(user)
      if (!redirectRoute) {
        showNotification('Routing Error', 'Could not determine your dashboard route. Contact support.', 'error')
        return
      }
      router.push(redirectRoute)
    }, 1500)
  }
}

// ===== API HANDLERS =====
const handleLogin = async () => {
  if (!validateForm()) {
    showNotification('Validation Error', 'Please check your inputs', 'error')
    return
  }
  isLoading.value = true
  requiresSecurityCheck.value = false
  requiresOtpChoice.value = false
  requiresOtpInput.value = false
  otpCode.value = ''

  try {
    const payload = { email: form.email, password: form.password, remember: form.remember }
    const response = await axios.post('/auth/login', payload)

    if (response.data.status === 'requires_otp') {
      otpEmails.primary = response.data.emails.primary
      otpEmails.recovery = response.data.emails.recovery
      if (response.data.message) otpMessage.value = response.data.message
      selectedOtpTarget.value = 'primary'
      requiresOtpChoice.value = true
      return
    }

    if (response.data.status === 'requires_security_questions') {
      securityQuestion.key = response.data.question_key
      securityQuestion.text = response.data.question_text
      requiresSecurityCheck.value = true
      return
    }

    if (response.data.status === 'success') {
      proceedWithLoginSuccess(response.data.user, response.data.token)
    } else {
      showNotification('Login Failed', response.data.message || 'Login failed', 'error')
    }
  } catch (error) {
    if (error.response && error.response.data) {
      showNotification('Login Failed', error.response.data.message || 'Invalid credentials or verification failed', 'error')
    } else {
      showNotification('Error', 'Network error. Please try again.', 'error')
    }
  } finally {
    isLoading.value = false
  }
}

const handleSendOtp = async () => {
  sendingOtp.value = true
  try {
    const payload = { email: form.email, password: form.password, target_type: selectedOtpTarget.value }
    const response = await axios.post('/auth/send-login-otp', payload)
    if (response.data.status === 'success') {
      requiresOtpChoice.value = false
      requiresOtpInput.value = true
      showNotification('OTP Sent', response.data.message, 'success')
    } else {
      showNotification('Failed', response.data.message, 'error')
    }
  } catch (error) {
    showNotification('Failed', error.response?.data?.message || 'Failed to send OTP.', 'error')
  } finally {
    sendingOtp.value = false
  }
}

const handleVerifyOtp = async () => {
  if (otpCode.value.length < 6) return
  verifyingOtp.value = true
  try {
    const payload = { email: form.email, password: form.password, otp: otpCode.value, remember: form.remember }
    const response = await axios.post('/auth/verify-login-otp', payload)
    if (response.data.status === 'requires_security_questions') {
      requiresOtpInput.value = false
      otpCode.value = ''
      securityQuestion.key = response.data.question_key
      securityQuestion.text = response.data.question_text
      requiresSecurityCheck.value = true
      showNotification('OTP Verified', response.data.message, 'success')
      return
    }
    if (response.data.status === 'success') {
      requiresOtpInput.value = false
      otpCode.value = ''
      proceedWithLoginSuccess(response.data.user, response.data.token)
    } else {
      showNotification('Verification Failed', response.data.message || 'Incorrect OTP.', 'error')
    }
  } catch (error) {
    showNotification('Verification Failed', error.response?.data?.message || 'Failed to verify OTP.', 'error')
  } finally {
    verifyingOtp.value = false
  }
}

const handleSecurityVerification = async () => {
  if (!securityAnswer.value.trim()) return
  verifyingSecurity.value = true
  try {
    const payload = { email: form.email, password: form.password, question_key: securityQuestion.key, answer: securityAnswer.value, remember: form.remember }
    const response = await axios.post('/auth/verify-security-questions', payload)
    if (response.data.status === 'success') {
      requiresSecurityCheck.value = false
      securityAnswer.value = ''
      proceedWithLoginSuccess(response.data.user, response.data.token)
    } else {
      showNotification('Verification Failed', response.data.message || 'Incorrect answer', 'error')
    }
  } catch (error) {
    if (error.response && error.response.data) {
      showNotification('Verification Failed', error.response.data.message || 'Incorrect security answer.', 'error')
    } else {
      showNotification('Error', 'Network error. Please try again.', 'error')
    }
  } finally {
    verifyingSecurity.value = false
  }
}

const handleForgotPassword = () => router.push('/Landing/forgotPassword')
const handleRegister = () => router.push('/Landing/signUp')

// ===== MOUNT =====
onMounted(async () => {
  await nextTick()
  const cleanupParticles = initParticles()
  const cleanupTilt = init3DTilt()
  window.__cleanupParticles = cleanupParticles
  window.__cleanupTilt = cleanupTilt
})

onUnmounted(() => {
  if (window.__cleanupParticles) window.__cleanupParticles()
  if (window.__cleanupTilt) window.__cleanupTilt()
})
</script>

<style scoped>
/* ===== KEYFRAMES ===== */
@keyframes float-slow {
  0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
  50% { transform: translateY(-20px) rotate(5deg) scale(1.05); }
}
@keyframes float-medium {
  0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
  50% { transform: translateY(-14px) rotate(-3deg) scale(1.08); }
}
@keyframes border-rotate {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
@keyframes shimmer {
  0% { transform: translateX(-100%) rotate(12deg); }
  100% { transform: translateX(100%) rotate(12deg); }
}
@keyframes pulse-slow {
  0%, 100% { opacity: 0.6; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.05); }
}

/* ===== ANIMATION CLASSES ===== */
.animate-float-slow { animation: float-slow 8s ease-in-out infinite; }
.animate-float-medium { animation: float-medium 6s ease-in-out infinite; }
.animate-border-rotate { background-size: 200% 200%; animation: border-rotate 4s ease-in-out infinite; }
.animate-shimmer { animation: shimmer 6s ease-in-out infinite; }
.animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }

/* ===== TRANSITIONS ===== */
input, button { transition: all 0.2s ease; }
input:focus { box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #1f2937; }
::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: #6b7280; }

/* ===== PERSPECTIVE ===== */
[perspective] { perspective: 1200px; }
[transform-style="preserve-3d"] { transform-style: preserve-3d; }
.will-change-transform { will-change: transform; }
</style>