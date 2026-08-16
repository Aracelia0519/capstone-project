<template>
  <div class="min-h-screen relative flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat"
       style="background-image: url('/hero-paint-store.jpg');">
    <!-- Dark overlay for readability -->
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    <!-- Main Card -->
    <div class="relative w-full max-w-5xl z-10">
      <div class="flex flex-col lg:flex-row bg-gray-900/90 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden border border-gray-700 min-h-[500px]">
        
        <!-- Left Side: Branding -->
        <div class="hidden lg:flex lg:w-2/5 p-8 bg-gradient-to-br from-gray-800/80 to-gray-900/80 flex-col justify-center items-center">
          <div class="mb-6">
            <div class="relative">
              <img src="/favicon.svg" class="w-20 h-20 mx-auto" alt="icon" />
            </div>
            <h1 class="text-3xl font-bold text-white text-center mt-4">
              CaviteGo Paint
            </h1>
            <p class="text-gray-400 text-center mt-2">Account Recovery</p>
          </div>

          <div class="space-y-4 mt-6 w-full max-w-xs">
            <div class="flex items-center space-x-3 p-3 rounded-xl bg-gray-800/60 shadow-sm">
              <div class="p-2 rounded-lg bg-blue-900/40 text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path></svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-200">Secure Reset</p>
                <p class="text-xs text-gray-400">Protected by OTP verification</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side: Forms -->
        <div class="w-full lg:w-3/5 p-8 lg:p-10 bg-gray-900/90 relative">
          
          <!-- STEP 1: ENTER EMAIL -->
          <transition name="fade" mode="out-in">
            <div v-if="step === 1" key="step1">
              <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Forgot Password?</h2>
                <p class="text-gray-400">Enter your email address to begin the recovery process.</p>
              </div>

              <form @submit.prevent="handleCheckEmail" class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">Email Address</label>
                  <div class="relative">
                    <input
                      v-model="email"
                      type="email"
                      required
                      placeholder="your@email.com"
                      class="w-full px-4 py-3 pl-11 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                    />
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                      <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                    </div>
                  </div>
                </div>

                <button type="submit" :disabled="isLoading" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition disabled:opacity-50 flex items-center justify-center">
                  <span v-if="isLoading" class="animate-spin w-5 h-5 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                  {{ isLoading ? 'Checking...' : 'Continue' }}
                </button>
              </form>

              <div class="mt-8 text-center">
                <button @click="router.push('/Landing/logIn')" class="text-gray-400 hover:text-white transition text-sm">Back to Login</button>
              </div>
            </div>

            <!-- STEP 2: CHOOSE OTP DESTINATION -->
            <div v-else-if="step === 2" key="step2">
              <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Account Found</h2>
                <p class="text-gray-400">Where should we send your verification code?</p>
              </div>

              <form @submit.prevent="handleSendOtp" class="space-y-6">
                <div class="space-y-4">
                  <label class="flex items-center p-4 border rounded-xl cursor-pointer transition-colors"
                    :class="selectedOtpTarget === 'primary' ? 'border-blue-500 bg-blue-900/20' : 'border-gray-700 bg-gray-800 hover:bg-gray-700/50'">
                    <input type="radio" v-model="selectedOtpTarget" value="primary" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 focus:ring-blue-500 focus:ring-offset-gray-900" />
                    <div class="ml-3">
                      <span class="block text-white font-medium">Primary Email</span>
                      <span class="block text-gray-400 text-sm">{{ maskedEmails.primary }}</span>
                    </div>
                  </label>

                  <label v-if="maskedEmails.recovery" class="flex items-center p-4 border rounded-xl cursor-pointer transition-colors"
                    :class="selectedOtpTarget === 'recovery' ? 'border-blue-500 bg-blue-900/20' : 'border-gray-700 bg-gray-800 hover:bg-gray-700/50'">
                    <input type="radio" v-model="selectedOtpTarget" value="recovery" class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 focus:ring-blue-500 focus:ring-offset-gray-900" />
                    <div class="ml-3">
                      <span class="block text-white font-medium">Recovery Email</span>
                      <span class="block text-gray-400 text-sm">{{ maskedEmails.recovery }}</span>
                    </div>
                  </label>
                </div>

                <div class="flex space-x-3">
                  <button type="button" @click="step = 1" class="w-1/3 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-800 transition">Back</button>
                  <button type="submit" :disabled="isLoading" class="w-2/3 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition disabled:opacity-50 flex items-center justify-center">
                    <span v-if="isLoading" class="animate-spin w-5 h-5 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                    {{ isLoading ? 'Sending...' : 'Send OTP' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- STEP 3: VERIFY OTP -->
            <div v-else-if="step === 3" key="step3">
              <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Verify OTP</h2>
                <p class="text-gray-400">Enter the 6-digit code we sent to <br/><strong class="text-white">{{ selectedOtpTarget === 'primary' ? maskedEmails.primary : maskedEmails.recovery }}</strong></p>
              </div>

              <form @submit.prevent="handleVerifyOtp" class="space-y-6">
                <div>
                  <input 
                    v-model="otpCode" 
                    type="text" 
                    maxlength="6"
                    required 
                    placeholder="••••••" 
                    class="w-full px-4 py-4 bg-gray-800 border border-gray-700 rounded-xl text-white text-center text-3xl tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-blue-500 transition placeholder:text-gray-600" 
                  />
                </div>

                <div class="flex space-x-3">
                  <button type="button" @click="step = 2" class="w-1/3 py-3 border border-gray-600 text-gray-300 rounded-lg hover:bg-gray-800 transition">Back</button>
                  <button type="submit" :disabled="isLoading || otpCode.length < 6" class="w-2/3 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition disabled:opacity-50 flex items-center justify-center">
                    <span v-if="isLoading" class="animate-spin w-5 h-5 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                    {{ isLoading ? 'Verifying...' : 'Verify Code' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- STEP 4: RESET PASSWORD -->
            <div v-else-if="step === 4" key="step4">
              <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-white mb-2">Create New Password</h2>
                <p class="text-gray-400">Please enter your new password below.</p>
              </div>

              <form @submit.prevent="handleResetPassword" class="space-y-6">
                <!-- New Password -->
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                  <div class="relative">
                    <input
                      v-model="password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      placeholder="••••••••"
                      class="w-full px-4 py-3 pl-11 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                    />
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                      <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path></svg>
                    </div>
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-300">
                      <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                  </div>
                </div>

                <!-- Confirm Password -->
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-2">Confirm Password</label>
                  <div class="relative">
                    <input
                      v-model="password_confirmation"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      placeholder="••••••••"
                      class="w-full px-4 py-3 pl-11 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                    />
                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                      <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path></svg>
                    </div>
                  </div>
                </div>

                <button type="submit" :disabled="isLoading || password.length < 8" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition disabled:opacity-50 flex items-center justify-center">
                  <span v-if="isLoading" class="animate-spin w-5 h-5 mr-2 border-2 border-white border-t-transparent rounded-full"></span>
                  {{ isLoading ? 'Updating...' : 'Update Password' }}
                </button>
              </form>
            </div>
          </transition>

        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 translate-y-4"
      leave-to-class="opacity-0 translate-y-4"
    >
      <div v-if="showToast" class="fixed top-6 right-6 bg-gray-800 shadow-lg rounded-lg border border-gray-700 p-4 max-w-sm z-50">
        <div class="flex items-start space-x-3">
          <div :class="['p-2 rounded-full', toastType === 'success' ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400']">
            <svg v-if="toastType === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </div>
          <div class="flex-1">
            <h5 class="text-sm font-semibold text-white">{{ toastTitle }}</h5>
            <p class="text-xs text-gray-300">{{ toastMessage }}</p>
          </div>
          <button @click="showToast = false" class="text-gray-500 hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
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

// UI State
const step = ref(1)
const isLoading = ref(false)
const showPassword = ref(false)

// Form State
const email = ref('')
const selectedOtpTarget = ref('primary')
const otpCode = ref('')
const password = ref('')
const password_confirmation = ref('')
const resetToken = ref('')

// Data State
const maskedEmails = reactive({ primary: '', recovery: null })

// Toast State
const showToast = ref(false)
const toastTitle = ref('')
const toastMessage = ref('')
const toastType = ref('success')

const showNotification = (title, message, type = 'success') => {
  toastTitle.value = title
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 5000)
}

// ==========================================
// API Handlers
// ==========================================

const handleCheckEmail = async () => {
  isLoading.value = true
  try {
    const response = await axios.post('/auth/forgot-password/check', { email: email.value })
    if (response.data.status === 'success') {
      maskedEmails.primary = response.data.emails.primary
      maskedEmails.recovery = response.data.emails.recovery
      
      // If no recovery email exists, auto-select primary.
      if (!maskedEmails.recovery) {
        selectedOtpTarget.value = 'primary'
      }
      
      step.value = 2
    }
  } catch (error) {
    showNotification('Error', error.response?.data?.message || 'Email not found.', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleSendOtp = async () => {
  isLoading.value = true
  try {
    const response = await axios.post('/auth/forgot-password/send-otp', {
      email: email.value,
      target_type: selectedOtpTarget.value
    })
    
    if (response.data.status === 'success') {
      showNotification('Sent', response.data.message, 'success')
      step.value = 3
    }
  } catch (error) {
    showNotification('Failed', error.response?.data?.message || 'Failed to send OTP.', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleVerifyOtp = async () => {
  if (otpCode.value.length < 6) return
  isLoading.value = true
  try {
    const response = await axios.post('/auth/forgot-password/verify-otp', {
      email: email.value,
      otp: otpCode.value
    })
    
    if (response.data.status === 'success') {
      resetToken.value = response.data.reset_token
      step.value = 4
    }
  } catch (error) {
    showNotification('Error', error.response?.data?.message || 'Invalid code.', 'error')
  } finally {
    isLoading.value = false
  }
}

const handleResetPassword = async () => {
  if (password.value !== password_confirmation.value) {
    showNotification('Error', 'Passwords do not match.', 'error')
    return
  }
  if (password.value.length < 8) {
    showNotification('Error', 'Password must be at least 8 characters.', 'error')
    return
  }

  isLoading.value = true
  try {
    const response = await axios.post('/auth/forgot-password/reset', {
      email: email.value,
      reset_token: resetToken.value,
      password: password.value,
      password_confirmation: password_confirmation.value
    })
    
    if (response.data.status === 'success') {
      showNotification('Success', 'Password has been reset successfully.', 'success')
      setTimeout(() => {
        router.push('/Landing/logIn')
      }, 1500)
    }
  } catch (error) {
    showNotification('Error', error.response?.data?.message || 'Failed to reset password.', 'error')
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
/* Fade transition for steps */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

input, button {
  transition: all 0.2s ease;
}
input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}
</style>