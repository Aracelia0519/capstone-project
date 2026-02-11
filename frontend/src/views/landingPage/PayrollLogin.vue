<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 flex items-center justify-center p-4 overflow-hidden relative font-sans">
    <canvas ref="particleCanvas" class="absolute inset-0 w-full h-full pointer-events-none z-0"></canvas>
    
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
      <div class="absolute top-1/4 left-1/4 w-96 h-96 opacity-40">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-emerald-500/30 to-teal-400/30 rounded-full blur-3xl animate-morph-slow"></div>
        <div class="absolute top-12 left-12 w-32 h-32 bg-gradient-to-r from-teal-500/20 to-cyan-500/20 rounded-full blur-2xl animate-morph-medium"></div>
      </div>
      
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 opacity-40">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-blue-600/20 to-indigo-500/20 rounded-full blur-3xl animate-morph-slow-reverse"></div>
        <div class="absolute bottom-12 right-12 w-32 h-32 bg-gradient-to-r from-amber-500/10 to-yellow-400/10 rounded-full blur-2xl animate-morph-medium-delay"></div>
      </div>

      <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-1 h-1 animate-ripple"></div>
      </div>
    </div>

    <div class="relative w-full max-w-4xl z-10 animate-gentle-float">
      <div class="flex flex-col lg:flex-row bg-slate-900/40 backdrop-blur-xl rounded-3xl shadow-2xl border border-slate-700/50 overflow-hidden">
        
        <div class="lg:w-5/12 p-8 lg:p-12 bg-gradient-to-br from-emerald-900/20 via-teal-900/20 to-slate-900/20 flex flex-col justify-center items-center relative overflow-hidden group">
          <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-teal-500/5 to-cyan-500/5 animate-gradient-shift"></div>
          
          <div class="relative mb-8 z-20 transform transition-all duration-700 group-hover:scale-105">
            <div class="relative">
              <div class="absolute -inset-4 bg-gradient-to-r from-emerald-500/30 via-teal-500/30 to-cyan-500/30 rounded-3xl blur-xl animate-pulse-glow"></div>
              <div class="relative w-24 h-24 rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-600 to-cyan-600 shadow-2xl flex items-center justify-center transform transition-all duration-500 hover:rotate-12">
                <svg class="w-12 h-12 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            
            <div class="mt-6 text-center">
              <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-300 via-teal-200 to-cyan-300 bg-clip-text text-transparent">
                Payroll Portal
              </h1>
              <p class="text-slate-400 text-sm mt-2">Secure Salary Management</p>
            </div>
          </div>

          <div class="space-y-4 w-full max-w-[260px] relative z-20">
             <div v-for="(feature, index) in features" :key="index" class="flex items-center gap-3 p-3 rounded-xl bg-slate-800/40 border border-slate-700/30 backdrop-blur-sm transition-all duration-300 hover:bg-slate-800/60 hover:translate-x-1">
                <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-400">
                  <component :is="feature.icon" class="w-4 h-4" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-200">{{ feature.title }}</p>
                  <p class="text-xs text-slate-500">{{ feature.subtitle }}</p>
                </div>
             </div>
          </div>
        </div>

        <div class="lg:w-7/12 p-8 lg:p-12 bg-slate-950/30 relative flex flex-col justify-center">
          <div class="max-w-md mx-auto w-full">
            <div class="text-center mb-8">
              <h2 class="text-2xl font-bold text-white mb-2">Employee Login</h2>
              <p class="text-slate-400 text-sm">Access your payslips and tax documents</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-6">
              <div class="space-y-2 group">
                <Label for="email" class="text-slate-300 group-hover:text-emerald-400 transition-colors">Work Email</Label>
                <div class="relative">
                  <Input 
                    id="email" 
                    v-model="form.email" 
                    type="email" 
                    placeholder="employee@cavitego.com"
                    class="pl-10 bg-slate-900/50 border-slate-700 hover:border-emerald-500/50 focus:border-emerald-500 transition-all duration-300 h-11 text-slate-200 placeholder:text-slate-600"
                    :class="{'border-red-500 focus:border-red-500': validationErrors.email}"
                  />
                  <div class="absolute left-3 top-3 text-slate-500 group-hover:text-emerald-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                  </div>
                </div>
                <span v-if="validationErrors.email" class="text-xs text-red-400">{{ validationErrors.email }}</span>
              </div>

              <div class="space-y-2 group">
                <Label for="password" class="text-slate-300 group-hover:text-emerald-400 transition-colors">Password</Label>
                <div class="relative">
                  <Input 
                    id="password" 
                    v-model="form.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    placeholder="••••••••"
                    class="pl-10 pr-10 bg-slate-900/50 border-slate-700 hover:border-emerald-500/50 focus:border-emerald-500 transition-all duration-300 h-11 text-slate-200 placeholder:text-slate-600"
                    :class="{'border-red-500 focus:border-red-500': validationErrors.password}"
                  />
                  <div class="absolute left-3 top-3 text-slate-500 group-hover:text-emerald-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-lock"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </div>
                  <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-3 text-slate-500 hover:text-slate-300 transition-colors z-10">
                    <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-off"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  </button>
                </div>
                <span v-if="validationErrors.password" class="text-xs text-red-400">{{ validationErrors.password }}</span>
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <Checkbox id="remember" :checked="form.remember" @update:checked="(val) => form.remember = val" class="border-slate-600 data-[state=checked]:bg-emerald-500 data-[state=checked]:border-emerald-500" />
                  <label for="remember" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 text-slate-400">
                    Remember me
                  </label>
                </div>
                <a href="#" class="text-sm font-medium text-emerald-400 hover:text-emerald-300 transition-colors hover:underline">
                  Forgot password?
                </a>
              </div>

              <button
                type="submit"
                :disabled="isLoading"
                class="w-full h-12 flex items-center justify-center bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-semibold rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg shadow-emerald-900/20 disabled:opacity-70 disabled:cursor-not-allowed"
              >
                <div v-if="isLoading" class="flex items-center justify-center">
                  <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  Authenticating...
                </div>
                <span v-else>Access Dashboard</span>
              </button>

              <div class="relative mt-8">
                <div class="absolute inset-0 flex items-center">
                  <span class="w-full border-t border-slate-800"></span>
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                  <span class="bg-slate-900 px-2 text-slate-500">Login your Employee Account</span>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    
    <Toaster position="top-right" theme="dark" closeButton />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'
import { ShieldCheck, Clock, FileText } from 'lucide-vue-next'

const router = useRouter()
const particleCanvas = ref(null)

// Features data tailored for Payroll
const features = [
  {
    icon: ShieldCheck,
    title: 'Bank-Grade Security',
    subtitle: 'End-to-end encrypted data'
  },
  {
    icon: Clock,
    title: 'Real-time Processing',
    subtitle: 'Instant salary computations'
  },
  {
    icon: FileText,
    title: 'Digital Payslips',
    subtitle: 'Automated tax generation'
  }
]

// Allowed Roles for Payroll Portal
const ALLOWED_ROLES = [
  'employee', 
  'operational_distributor', 
  'hr_manager', 
  'finance_manager'
]

// Form State
const form = reactive({
  email: '',
  password: '',
  remember: false
})

const showPassword = ref(false)
const isLoading = ref(false)
const validationErrors = reactive({
  email: '',
  password: ''
})

// Validation Logic
const validateForm = () => {
  let isValid = true
  validationErrors.email = ''
  validationErrors.password = ''

  if (!form.email) {
    validationErrors.email = 'Email is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    validationErrors.email = 'Please enter a valid work email'
    isValid = false
  }

  if (!form.password) {
    validationErrors.password = 'Password is required'
    isValid = false
  }

  return isValid
}

// Login Handler - Logic aligned with LogIn.vue process
const handleLogin = async () => {
  if (!validateForm()) {
    toast.error('Validation Error', {
      description: 'Please check your credentials and try again.'
    })
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
      const user = response.data.user
      
      // Role Restriction Check for Payroll Portal
      if (ALLOWED_ROLES.includes(user.role)) {
        
        // --- PROCESS COPY FROM LogIn.vue ---
        // Store token and user data exactly as LogIn.vue does
        localStorage.setItem('auth_token', response.data.token)
        localStorage.setItem('user_data', JSON.stringify(response.data.user))
        
        toast.success('Access Granted', {
          description: 'Redirecting to your payroll dashboard...',
          duration: 2000,
        })
        
        // Route specifically to Employees Dashboard
        setTimeout(() => {
          router.push('/Employees/DashboardEmployee')
        }, 1500)
        // -----------------------------------
        
      } else {
        // If role is not allowed, ensure we don't leave a lingering session
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user_data')

        toast.error('Unauthorized Access', {
          description: `User role '${user.role}' is not allowed to access the Payroll Portal.`
        })
      }

    } else {
      toast.error('Login Failed', {
        description: response.data.message || 'Invalid credentials'
      })
    }

  } catch (error) {
    let errorMessage = 'Network error. Please try again.'
    
    if (error.response && error.response.data) {
      errorMessage = error.response.data.message || errorMessage
    }
    
    toast.error('Authentication Error', {
      description: errorMessage
    })
  } finally {
    isLoading.value = false
  }
}

// Particle System
let particles = []
let animationId = null

const initParticleSystem = () => {
  const canvas = particleCanvas.value
  if (!canvas) return
  const ctx = canvas.getContext('2d')
  
  const resizeCanvas = () => {
    canvas.width = window.innerWidth
    canvas.height = window.innerHeight
  }
  
  resizeCanvas()
  window.addEventListener('resize', resizeCanvas)
  
  const colors = [
    'rgba(16, 185, 129, 0.2)', // Emerald
    'rgba(20, 184, 166, 0.2)', // Teal
    'rgba(6, 182, 212, 0.2)'   // Cyan
  ]
  
  particles = Array.from({ length: 25 }, () => ({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    size: Math.random() * 2 + 1,
    speedX: Math.random() * 0.4 - 0.2,
    speedY: Math.random() * 0.4 - 0.2,
    color: colors[Math.floor(Math.random() * colors.length)]
  }))
  
  const animateParticles = () => {
    ctx.clearRect(0, 0, canvas.width, canvas.height)
    particles.forEach(p => {
      p.x += p.speedX
      p.y += p.speedY
      if (p.x <= 0 || p.x >= canvas.width) p.speedX *= -1
      if (p.y <= 0 || p.y >= canvas.height) p.speedY *= -1
      
      ctx.beginPath()
      ctx.fillStyle = p.color
      ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2)
      ctx.fill()
    })
    animationId = requestAnimationFrame(animateParticles)
  }
  
  animateParticles()
  return () => {
    window.removeEventListener('resize', resizeCanvas)
    if (animationId) cancelAnimationFrame(animationId)
  }
}

onMounted(() => {
  const cleanup = initParticleSystem()
  onUnmounted(() => {
    if (cleanup) cleanup()
  })
})
</script>

<style scoped>
/* Keyframes */
@keyframes morph-slow {
  0% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; transform: translate(0, 0) rotate(0deg); }
  50% { border-radius: 30% 60% 70% 40% / 50% 60% 30% 60%; transform: translate(20px, -20px) rotate(120deg); }
  100% { border-radius: 60% 40% 30% 70% / 60% 30% 70% 40%; transform: translate(0, 0) rotate(0deg); }
}

@keyframes morph-slow-reverse {
  0% { border-radius: 40% 60% 70% 30% / 40% 70% 30% 60%; transform: translate(0, 0) rotate(0deg); }
  50% { border-radius: 60% 30% 30% 70% / 60% 40% 60% 30%; transform: translate(-20px, 20px) rotate(-120deg); }
  100% { border-radius: 40% 60% 70% 30% / 40% 70% 30% 60%; transform: translate(0, 0) rotate(0deg); }
}

@keyframes ripple {
  0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.1); }
  100% { box-shadow: 0 0 0 500px rgba(16, 185, 129, 0); }
}

@keyframes gentle-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

@keyframes pulse-glow {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.05); }
}

.animate-morph-slow { animation: morph-slow 15s infinite ease-in-out; }
.animate-morph-slow-reverse { animation: morph-slow-reverse 15s infinite ease-in-out; }
.animate-ripple { animation: ripple 15s infinite linear; }
.animate-gentle-float { animation: gentle-float 6s ease-in-out infinite; }
.animate-pulse-glow { animation: pulse-glow 4s ease-in-out infinite; }
</style>