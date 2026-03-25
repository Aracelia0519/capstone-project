<template>
  <div class="min-h-screen relative flex items-center justify-center p-4 bg-cover bg-center bg-no-repeat"
       style="background-image: url('/hero-paint-store.jpg');">
    <!-- Dark overlay for readability -->
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    <!-- Main Card -->
    <div class="relative w-full max-w-5xl z-10">
      <div class="flex flex-col lg:flex-row bg-gray-900/90 backdrop-blur-md rounded-2xl shadow-2xl overflow-hidden border border-gray-700">
        
        <!-- Left Side: Branding & Features -->
        <div class="lg:w-2/5 p-8 bg-gradient-to-br from-gray-800/80 to-gray-900/80 flex flex-col justify-center items-center">
          <div class="mb-6">
            <div class="relative">
              <img src="/favicon.svg" class="w-20 h-20 mx-auto" alt="icon" />
            </div>
            <h1 class="text-3xl font-bold text-white text-center mt-4">
              CaviteGo Paint
            </h1>
            <p class="text-gray-400 text-center mt-2">Color Your World Beautifully</p>
          </div>

          <div class="space-y-4 mt-6 w-full max-w-xs">
            <div v-for="(feature, index) in features" :key="index"
                 class="flex items-center space-x-3 p-3 rounded-xl bg-gray-800/60 shadow-sm hover:shadow-md transition-shadow">
              <div class="p-2 rounded-lg bg-blue-900/40 text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-200">{{ feature.title }}</p>
                <p class="text-xs text-gray-400">{{ feature.subtitle }}</p>
              </div>
            </div>
          </div>

          <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm italic">"Where Every Color Tells a Story"</p>
          </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="lg:w-3/5 p-8 lg:p-10 bg-gray-900/90">
          <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Welcome Back</h2>
            <p class="text-gray-400">Sign in to your account</p>
          </div>

          <form @submit.prevent="handleLogin" class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                  <span>Email Address</span>
                </div>
              </label>
              <div class="relative">
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="your@email.com"
                  class="w-full px-4 py-3 pl-11 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                  :class="validationErrors.email ? 'border-red-500' : ''"
                />
                <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                  <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                  </svg>
                </div>
              </div>
              <p v-if="validationErrors.email" class="text-xs text-red-400 mt-1">{{ validationErrors.email }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-300 mb-2">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 118 0v4h8z"></path>
                  </svg>
                  <span>Password</span>
                </div>
              </label>
              <div class="relative">
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  placeholder="••••••••"
                  class="w-full px-4 py-3 pl-11 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition"
                  :class="validationErrors.password ? 'border-red-500' : ''"
                />
                <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                  <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                  </svg>
                </div>
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-300"
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
              <p v-if="validationErrors.password" class="text-xs text-red-400 mt-1">{{ validationErrors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                  v-model="form.remember"
                  type="checkbox"
                  class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-blue-500 focus:ring-blue-500 focus:ring-offset-0"
                />
                <span class="text-sm text-gray-400">Remember me</span>
              </label>
              <button
                type="button"
                @click="handleForgotPassword"
                class="text-sm text-blue-400 hover:text-blue-300 font-medium"
              >
                Forgot password?
              </button>
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isLoading ? 'Signing In...' : 'Sign In to Account' }}
            </button>

            <div class="relative text-center">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full h-px bg-gray-700"></div>
              </div>
              <div class="relative flex justify-center">
                <span class="px-4 bg-gray-900 text-gray-400 text-sm">Don't have an account?</span>
              </div>
            </div>

            <button
              type="button"
              @click="handleRegister"
              class="w-full py-3 text-gray-300 font-medium rounded-lg border border-gray-700 hover:bg-gray-800 transition"
            >
              Create New Account
            </button>
          </form>

          <div class="mt-6 pt-4 border-t border-gray-800 text-center">
            <p class="text-gray-500 text-xs">
              © 2026 CaviteGo Paint • Secure authentication powered by Laravel Sanctum
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification (dark theme) -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 translate-y-4"
      leave-to-class="opacity-0 translate-y-4"
    >
      <div
        v-if="showToast"
        class="fixed top-6 right-6 bg-gray-800 shadow-lg rounded-lg border border-gray-700 p-4 max-w-sm z-50"
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
          <button @click="showToast = false" class="text-gray-500 hover:text-gray-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </transition>

    <!-- Client Options Modal (dark theme) -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 scale-95"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="showClientOptions" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
        <div class="bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-6 relative border border-gray-700">
          <h3 class="text-xl font-bold text-white text-center mb-2">Welcome, Client!</h3>
          <p class="text-gray-400 text-center mb-6">Where would you like to go?</p>
          
          <div class="grid grid-cols-1 gap-4">
            <button 
              @click="navigateToClientRoute('/ECommerceClient/EccommerceShop')"
              class="flex items-center justify-between p-4 rounded-xl border border-blue-800 bg-blue-900/30 hover:bg-blue-900/50 transition"
            >
              <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-800/50 rounded-lg text-blue-300">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
                <div class="text-left">
                  <h4 class="text-white font-semibold">E-Commerce</h4>
                  <p class="text-sm text-gray-400">Shop for products</p>
                </div>
              </div>
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <button 
              @click="navigateToClientRoute('/Clients/dashboardC')"
              class="flex items-center justify-between p-4 rounded-xl border border-purple-800 bg-purple-900/30 hover:bg-purple-900/50 transition"
            >
              <div class="flex items-center space-x-3">
                <div class="p-2 bg-purple-800/50 rounded-lg text-purple-300">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div class="text-left">
                  <h4 class="text-white font-semibold">Management</h4>
                  <p class="text-sm text-gray-400">Manage your account</p>
                </div>
              </div>
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
          </div>
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

// Enhanced Role-based redirect routes with employee department-based routing
const getRedirectRoute = (user) => {
  const { role, employee_data } = user;
  
  if (role === 'hr_manager') {
    return '/HR/HRdashboard';
  }
  
  if (role === 'employee' && employee_data) {
    const department = employee_data.department?.toLowerCase() || '';
    const position = employee_data.position?.toLowerCase() || '';
    
    // Explicit route check for Special RBAC department
    if (department.includes('special rbac') || department.includes('special')) {
      return '/special-rbac/dashboard'; 
    }
    // Route based on department
    else if (department.includes('human resource') || department.includes('hr')) {
      return '/HR/HRdashboard';
    } 
    else if (department.includes('finance') || department.includes('accounting')) {
      return '/finance/financeDashboard';
    }
    // Check Operational BEFORE general Distributor since Operational Distributor contains "distributor"
    else if (department.includes('operational') || position.includes('operational distributor')) {
      return '/ECommerce/ECDashboard';
    }
    else if (department.includes('distributor') || position.includes('distributor assistant')) {
      return '/distributor/distributordashboard';
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
    hr_manager: '/HR/HRdashboard', 
    employee: '/employee/dashboard', 
    supplier: '/Supplier/SupplierDashboard', 
    personnel_officer: '/Supplier/SupplierDashboard',
    supplier_employee: '/Supplier/SupplierDashboard', 
    personnel_employee: '/Supplier/SupplierDashboard' 
  };
  
  return roleRoutes[role] || '/';
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

// Client routing function
const navigateToClientRoute = (route) => {
  showClientOptions.value = false
  router.push(route)
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
      
      const user = response.data.user
      
      if (user.role === 'client') {
        showNotification('Success!', 'Login successful. Please choose your destination.', 'success')
        
        // Trigger modal for client
        setTimeout(() => {
          showClientOptions.value = true
        }, 1000)
      } else {
        showNotification('Success!', 'Redirecting to dashboard...', 'success')
        
        setTimeout(() => {
          const redirectRoute = getRedirectRoute(user)
          
          // Handle unknown roles or departments
          if (!redirectRoute) {
            showNotification('Routing Error', 'Could not determine your dashboard route. Contact support.', 'error')
            return
          }
          
          router.push(redirectRoute)
        }, 1500)
      }
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

// No particle system – removed for performance
onMounted(() => {
  // No animations to set up
})

// No cleanup needed
onUnmounted(() => {})
</script>

<style scoped>
/* Simple transitions and scrollbar (no heavy animations) */
input, button {
  transition: all 0.2s ease;
}
input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: #1f2937;
}
::-webkit-scrollbar-thumb {
  background: #4b5563;
  border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>