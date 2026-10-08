<template>
  <div class="min-h-screen relative flex items-center justify-center p-4 overflow-hidden bg-[#070a14]">

    <!-- ===== STATIC BACKGROUND (painted once, no blur / no animation) ===== -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat opacity-45"
         style="background-image: url('/hero-paint-store.jpg');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-[#070a14]/80 via-[#070a14]/92 to-[#070a14]"></div>
    <div class="absolute inset-0"
         style="background: radial-gradient(560px 360px at 18% 18%, rgba(59,130,246,0.18), transparent 70%), radial-gradient(560px 360px at 82% 82%, rgba(168,85,247,0.15), transparent 70%);"></div>

    <!-- ===== MAIN CARD ===== -->
    <div class="relative w-full max-w-5xl z-10">
      <div class="auth-card relative bg-gray-900/95 border border-white/10 rounded-2xl shadow-2xl overflow-hidden">

        <!-- Accent bar -->
        <div class="h-1.5 bg-gradient-to-r from-blue-500 via-violet-500 to-pink-500"></div>

        <div class="flex flex-col lg:flex-row">

          <!-- ===== LEFT SIDE: BRANDING ===== -->
          <div class="lg:w-2/5 p-6 sm:p-8 bg-white/[0.03] border-b lg:border-b-0 lg:border-r border-white/10 flex flex-col justify-center items-center text-center">
            <img src="/favicon.svg" class="w-16 h-16" alt="CaviteGo Paint" />
            <h1 class="text-2xl font-bold text-white mt-4 tracking-tight">CaviteGo Paint</h1>
            <p class="text-slate-400 text-sm mt-1">Color Your World Beautifully</p>

            <div class="hidden lg:block w-full max-w-xs mt-6 space-y-3 text-left">
              <div v-for="(feature, index) in features" :key="index"
                   class="flex items-start gap-3 p-3 rounded-xl bg-white/[0.03] border border-white/10">
                <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                </svg>
                <div>
                  <p class="text-sm font-medium text-slate-200">{{ feature.title }}</p>
                  <p class="text-xs text-slate-400">{{ feature.subtitle }}</p>
                </div>
              </div>
            </div>

            <p class="hidden lg:block mt-6 text-xs text-slate-500 italic">"Where Every Color Tells a Story"</p>
          </div>

          <!-- ===== RIGHT SIDE: LOGIN FORM ===== -->
          <div class="lg:w-3/5 p-6 sm:p-8 lg:p-10">
            <div class="text-center mb-8">
              <h2 class="text-2xl font-bold text-white tracking-tight">Welcome Back</h2>
              <p class="text-slate-400 text-sm mt-1">Sign in to your account</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-5">
              <!-- Email -->
              <div>
                <label for="login-email" class="block text-sm font-medium text-slate-300 mb-2">Email Address</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                  </span>
                  <input
                    id="login-email"
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="your@email.com"
                    class="w-full pl-10 pr-4 py-2.5 bg-white/5 border rounded-lg text-white text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500/60 transition-colors"
                    :class="validationErrors.email ? 'border-red-500/60' : 'border-white/10'"
                  />
                </div>
                <p v-if="validationErrors.email" class="text-xs text-red-400 mt-1.5">{{ validationErrors.email }}</p>
              </div>

              <!-- Password -->
              <div>
                <label for="login-password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path>
                    </svg>
                  </span>
                  <input
                    id="login-password"
                    v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    required
                    placeholder="••••••••"
                    class="w-full pl-10 pr-11 py-2.5 bg-white/5 border rounded-lg text-white text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500/60 transition-colors"
                    :class="validationErrors.password ? 'border-red-500/60' : 'border-white/10'"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition-colors"
                  >
                    <svg v-if="showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                </div>
                <p v-if="validationErrors.password" class="text-xs text-red-400 mt-1.5">{{ validationErrors.password }}</p>
              </div>

              <!-- Remember & Forgot -->
              <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="form.remember"
                    type="checkbox"
                    class="w-4 h-4 rounded border-white/20 bg-white/5 text-blue-500 focus:ring-blue-500/40 focus:ring-offset-0"
                  />
                  <span class="text-sm text-slate-400">Remember me</span>
                </label>
                <button
                  type="button"
                  @click="handleForgotPassword"
                  class="text-sm text-blue-400 hover:text-blue-300 font-medium transition-colors"
                >
                  Forgot password?
                </button>
              </div>

              <!-- Submit -->
              <button
                type="submit"
                :disabled="isLoading"
                class="w-full py-2.5 bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 text-white text-sm font-semibold rounded-lg shadow-lg shadow-blue-600/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <span v-if="isLoading" class="flex items-center gap-2">
                  <span class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                  Authenticating...
                </span>
                <span v-else>Sign In to Account</span>
              </button>

              <!-- Divider -->
              <div class="flex items-center gap-3">
                <div class="flex-1 h-px bg-white/10"></div>
                <span class="text-slate-500 text-xs">Don't have an account?</span>
                <div class="flex-1 h-px bg-white/10"></div>
              </div>

              <!-- Register -->
              <button
                type="button"
                @click="handleRegister"
                class="w-full py-2.5 text-slate-300 text-sm font-medium rounded-lg border border-white/10 hover:bg-white/5 hover:border-white/20 transition-colors"
              >
                Create New Account
              </button>
            </form>

            <!-- Footer -->
            <div class="mt-6 pt-4 border-t border-white/10 text-center">
              <p class="text-slate-500 text-xs">© 2026 CaviteGo Paint · Secure authentication powered by Laravel Sanctum</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== OTP CHOICES MODAL ===== -->
    <transition
      enter-active-class="transition-opacity duration-200 ease-out"
      leave-active-class="transition-opacity duration-150 ease-in"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div v-if="requiresOtpChoice" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8">
          <div class="w-12 h-12 rounded-xl bg-blue-500/15 border border-blue-500/25 text-blue-400 flex items-center justify-center mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white">2-Step Verification</h3>
          <p class="text-slate-400 text-sm mt-1 mb-5">{{ otpMessage }}</p>

          <form @submit.prevent="handleSendOtp">
            <div class="space-y-3 mb-6">
              <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-colors"
                     :class="selectedOtpTarget === 'primary' ? 'border-blue-500/60 bg-blue-500/10' : 'border-white/10 bg-white/[0.03] hover:border-white/20'">
                <input type="radio" v-model="selectedOtpTarget" value="primary"
                       class="w-4 h-4 text-blue-500 bg-transparent border-white/30 focus:ring-blue-500/40" />
                <div class="ml-3">
                  <span class="block text-white font-medium text-sm">Primary Email</span>
                  <span class="block text-slate-400 text-sm">{{ otpEmails.primary }}</span>
                </div>
              </label>

              <label v-if="otpEmails.recovery" class="flex items-center p-4 border rounded-xl cursor-pointer transition-colors"
                     :class="selectedOtpTarget === 'recovery' ? 'border-blue-500/60 bg-blue-500/10' : 'border-white/10 bg-white/[0.03] hover:border-white/20'">
                <input type="radio" v-model="selectedOtpTarget" value="recovery"
                       class="w-4 h-4 text-blue-500 bg-transparent border-white/30 focus:ring-blue-500/40" />
                <div class="ml-3">
                  <span class="block text-white font-medium text-sm">Recovery Email</span>
                  <span class="block text-slate-400 text-sm">{{ otpEmails.recovery }}</span>
                </div>
              </label>
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="requiresOtpChoice = false"
                      class="px-5 py-2.5 text-sm text-slate-400 hover:text-white transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="sendingOtp"
                      class="px-5 py-2.5 text-sm bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2">
                <span v-if="sendingOtp" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                Send Code
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- ===== OTP INPUT MODAL ===== -->
    <transition
      enter-active-class="transition-opacity duration-200 ease-out"
      leave-active-class="transition-opacity duration-150 ease-in"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div v-if="requiresOtpInput" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8">
          <div class="w-12 h-12 rounded-xl bg-violet-500/15 border border-violet-500/25 text-violet-400 flex items-center justify-center mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white">Enter Verification Code</h3>
          <p class="text-slate-400 text-sm mt-1 mb-5">
            Enter the 6-digit code we just sent to
            <strong class="text-slate-200">{{ selectedOtpTarget === 'primary' ? otpEmails.primary : otpEmails.recovery }}</strong>.
          </p>

          <form @submit.prevent="handleVerifyOtp">
            <div class="mb-6">
              <input
                v-model="otpCode"
                type="text"
                inputmode="numeric"
                maxlength="6"
                required
                placeholder="••••••"
                class="w-full px-4 py-3.5 bg-white/5 border border-white/10 rounded-xl text-white text-center text-2xl tracking-[0.4em] focus:outline-none focus:ring-2 focus:ring-violet-500/40 focus:border-violet-500/60 transition-colors placeholder:text-slate-600"
              />
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="requiresOtpInput = false; requiresOtpChoice = true"
                      class="px-5 py-2.5 text-sm text-slate-400 hover:text-white transition-colors">
                Back
              </button>
              <button type="submit" :disabled="verifyingOtp || otpCode.length < 6"
                      class="px-5 py-2.5 text-sm bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2">
                <span v-if="verifyingOtp" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                Verify Code
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- ===== SECURITY QUESTIONS MODAL ===== -->
    <transition
      enter-active-class="transition-opacity duration-200 ease-out"
      leave-active-class="transition-opacity duration-150 ease-in"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div v-if="requiresSecurityCheck" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl w-full max-w-md p-6 sm:p-8">
          <div class="w-12 h-12 rounded-xl bg-blue-500/15 border border-blue-500/25 text-blue-400 flex items-center justify-center mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white">Security Verification</h3>
          <p class="text-slate-400 text-sm mt-1 mb-5">Unrecognized device detected. To protect your account, please answer your security question.</p>

          <form @submit.prevent="handleSecurityVerification">
            <div class="mb-6">
              <label class="block text-sm font-semibold text-blue-400 mb-2">{{ securityQuestion.text }}</label>
              <input
                v-model="securityAnswer"
                type="text"
                required
                placeholder="Type your secret answer"
                class="w-full px-4 py-2.5 bg-white/5 border border-white/10 rounded-lg text-white text-sm placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500/60 transition-colors"
              />
            </div>

            <div class="flex justify-end gap-3">
              <button type="button" @click="requiresSecurityCheck = false"
                      class="px-5 py-2.5 text-sm text-slate-400 hover:text-white transition-colors">
                Cancel
              </button>
              <button type="submit" :disabled="verifyingSecurity"
                      class="px-5 py-2.5 text-sm bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 flex items-center gap-2">
                <span v-if="verifyingSecurity" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                Verify &amp; Login
              </button>
            </div>
          </form>
        </div>
      </div>
    </transition>

    <!-- ===== TOAST NOTIFICATION ===== -->
    <transition
      enter-active-class="transition-opacity duration-200 ease-out"
      leave-active-class="transition-opacity duration-150 ease-in"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showToast"
        class="fixed top-6 right-6 left-6 sm:left-auto bg-gray-900 shadow-2xl rounded-lg border border-white/10 p-4 max-w-sm z-50"
      >
        <div class="flex items-start gap-3">
          <div :class="[
            'w-8 h-8 shrink-0 rounded-full flex items-center justify-center',
            toastType === 'success' ? 'bg-emerald-500/15 text-emerald-400' :
            toastType === 'error' ? 'bg-red-500/15 text-red-400' :
            'bg-amber-500/15 text-amber-400'
          ]">
            <svg v-if="toastType === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else-if="toastType === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="flex-1">
            <h5 class="text-sm font-semibold text-white">{{ toastTitle }}</h5>
            <p class="text-xs text-slate-300">{{ toastMessage }}</p>
          </div>
          <button @click="showToast = false" class="text-slate-500 hover:text-slate-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </transition>

    <!-- ===== CLIENT OPTIONS MODAL ===== -->
    <transition
      enter-active-class="transition-opacity duration-200 ease-out"
      leave-active-class="transition-opacity duration-150 ease-in"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div v-if="showClientOptions" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl max-w-md w-full p-6">
          <div class="text-center mb-6">
            <div class="w-14 h-14 bg-blue-500/15 border border-blue-500/25 rounded-full flex items-center justify-center mx-auto mb-3 text-blue-400">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-white">Welcome, Client!</h3>
            <p class="text-slate-400 text-sm mt-1">Where would you like to go?</p>
          </div>

          <div class="grid grid-cols-1 gap-3">
            <button
              @click="navigateToClientRoute('/ECommerceClient/EccommerceShop')"
              class="flex items-center justify-between p-4 rounded-xl border border-blue-500/30 bg-blue-500/10 hover:bg-blue-500/15 transition-colors text-left"
            >
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-blue-500/15 text-blue-300">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-white font-semibold text-sm">E-Commerce</h4>
                  <p class="text-sm text-slate-400">Shop for products</p>
                </div>
              </div>
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>

            <button
              @click="navigateToClientRoute('/Clients/dashboardC')"
              class="flex items-center justify-between p-4 rounded-xl border border-violet-500/30 bg-violet-500/10 hover:bg-violet-500/15 transition-colors text-left"
            >
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-violet-500/15 text-violet-300">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                  </svg>
                </div>
                <div>
                  <h4 class="text-white font-semibold text-sm">Management</h4>
                  <p class="text-sm text-slate-400">Manage your account</p>
                </div>
              </div>
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios'

const router = useRouter()

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
</script>

<style scoped>
/* One-shot entrance animation (no infinite loops, no filters) */
@keyframes fade-up {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}
.auth-card { animation: fade-up 0.4s ease-out both; }

@media (prefers-reduced-motion: reduce) {
  .auth-card { animation: none; }
}
</style>
