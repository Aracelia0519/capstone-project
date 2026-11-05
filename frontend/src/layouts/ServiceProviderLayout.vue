<template>
  <SidebarProvider>
    <div
      class="sp-layout-root flex min-h-screen w-full font-sans selection:bg-purple-500/30 overflow-hidden transition-colors duration-300"
      :class="theme === 'dark' ? 'bg-slate-900 text-slate-100' : 'bg-slate-100 text-slate-900'"
    >
      <Toaster position="top-right" richColors />

      <!-- Dark / Light mode toggle (default: dark) -->
      <button
        type="button"
        class="theme-toggle fixed top-20 right-4 z-50 inline-flex h-10 w-10 items-center justify-center rounded-full border shadow-lg transition-all duration-300 md:top-6 md:right-6"
        :class="theme === 'dark'
          ? 'border-slate-700 bg-slate-800/90 text-amber-300 hover:bg-slate-700'
          : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-100'"
        :aria-label="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        :title="theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
        @click="toggleTheme"
      >
        <Sun v-if="theme === 'dark'" class="h-5 w-5" />
        <Moon v-else class="h-5 w-5" />
      </button>
      
      <VerificationModal 
        v-if="showVerificationModal" 
        @close="showVerificationModal = false"
        :verification-status="verificationStatus"
        redirect-route="/serviceProvider/ProfileSettingsSP" 
        description-text="To access all service provider features like accepting service requests and managing your portfolio, please complete your professional verification."
      />

      <sideBarServiceProvider 
        :key="verificationStatus" 
        :verification-status="verificationStatus"
        @open-verification-modal="showVerificationModal = true"
        @logout-started="handleLogoutStart" 
        @logout-finished="handleLogoutFinish" 
      />

      <SidebarInset 
        class="main-content-area border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto"
        :class="theme === 'dark' ? 'bg-slate-900' : 'bg-slate-100'"
      >
        
        <transition name="fade">
          <div 
            v-if="isLoggingOut" 
            class="fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-sm flex flex-col items-center justify-center"
          >
            <div class="w-full max-w-md p-8 text-center">
              <div class="relative mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-purple-500/20 to-pink-500/20 flex items-center justify-center mb-4">
                  <LogOut class="w-12 h-12 text-purple-400" />
                </div>
                <div class="absolute -inset-4 bg-purple-500/10 rounded-full animate-pulse" />
              </div>
              
              <h3 class="text-2xl font-bold text-white mb-2">Logging Out</h3>
              <p class="text-slate-400 mb-8">Securely terminating your session...</p>
              
              <div class="space-y-4">
                <Progress 
                  :model-value="logoutProgress" 
                  class="h-2 bg-slate-800"
                />
                <p class="text-sm text-slate-400">{{ logoutProgress }}%</p>
              </div>
            </div>
          </div>
        </transition>

        <header 
          class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 backdrop-blur-md border-b"
          :class="theme === 'dark' ? 'bg-slate-900/80 border-slate-800/50' : 'bg-slate-100/80 border-slate-200'"
        >
          <SidebarTrigger 
            class="-ml-1" 
            :class="theme === 'dark' ? 'text-purple-500 hover:bg-slate-800' : 'text-purple-600 hover:bg-slate-200'" 
          />
          <span 
            class="text-xs font-bold uppercase tracking-widest"
            :class="theme === 'dark' ? 'text-slate-400' : 'text-slate-500'"
          >
            Service Provider Portal
          </span>
        </header>

        <main class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1">
          <router-view v-slot="{ Component }">
            <transition name="page-slide" mode="out-in">
              <component 
                :is="Component" 
                v-if="userData" 
                :user="userData" 
                :dashboard-data="dashboardData" 
                :verification-status="verificationStatus"
              />
            </transition>
          </router-view>
        </main>

        <div
          class="absolute inset-0 bg-gradient-to-br pointer-events-none transition-opacity duration-300"
          :class="theme === 'dark'
            ? 'from-slate-900 via-slate-900 to-purple-900/10 opacity-50'
            : 'from-slate-100 via-slate-100 to-purple-100/40 opacity-40'"
        />
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Toaster, toast } from 'vue-sonner' 
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import sideBarServiceProvider from './sideBarServiceProvider.vue'
import VerificationModal from './VerificationModal.vue'
import axios from '@/utils/axios'
import echo from '@/utils/websocket' // WebSockets
import { LogOut, Sun, Moon } from 'lucide-vue-next'
import { getCurrentUser } from '@/utils/auth' // Auth exactly like ClientLayout

const route = useRoute()
const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)

// Verification States
const verificationStatus = ref(null)
const showVerificationModal = ref(false)

// Theme setup - dark is the default for the service provider portal
const theme = ref('dark')
const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

// Keep the light-mode override class on <body> so teleported content
// (mobile sidebar, dialogs, toasts) is themed too.
watch(theme, (value) => {
  document.body.classList.toggle('sp-light-mode', value === 'light')
  document.body.classList.toggle('sp-dark-mode', value === 'dark')
}, { immediate: true })

// Logout States
const isLoggingOut = ref(false)
const logoutProgress = ref(0)
let progressInterval = null

// Real-Time Notification Helper
const showNotification = (message, type = 'info') => {
  if (type === 'success') toast.success(message)
  else if (type === 'error') toast.error(message)
  else if (type === 'warning') toast.warning(message)
  else toast.info(message)
}

const handleLogoutStart = () => {
  isLoggingOut.value = true
  logoutProgress.value = 0
  progressInterval = setInterval(() => {
    if (logoutProgress.value < 90) {
      logoutProgress.value += 10
    }
  }, 300)
}

const handleLogoutFinish = () => {
  logoutProgress.value = 100
  setTimeout(() => {
    if (progressInterval) clearInterval(progressInterval)
    isLoggingOut.value = false
    logoutProgress.value = 0
  }, 500)
}

// Watch for verification required query parameters
watch(() => route.query, (newQuery) => {
  if (newQuery.verification_required === 'true') {
    verificationStatus.value = newQuery.status || 'none'
    showVerificationModal.value = true
  }
}, { immediate: true })

const fetchVerificationStatus = async () => {
  try {
    const response = await axios.get('/service-provider/requirements') 
    if (response.data.status === 'success') {
      verificationStatus.value = response.data.data?.status || 'none'
    }
  } catch (error) {
    console.error('Error fetching verification status', error)
  }
}

const fetchDashboardData = async () => {
  try {
    const roleEndpoints = {
      service_provider: '/dashboard/service-provider',
      admin: '/dashboard/admin'
    }
    
    if (userData.value && roleEndpoints[userData.value.role]) {
      const res = await axios.get(roleEndpoints[userData.value.role])
      dashboardData.value = res.data.data
    }
  } catch (error) {
    console.error('Error fetching dashboard', error)
  }
}

// WEBSOCKET LOGIC
const setupWebsocketListener = () => {
  if (!userData.value || !userData.value.id) return
  
  // Prevent duplicate listeners
  echo.leave(`user.${userData.value.id}.requirements`)
  
  echo.private(`user.${userData.value.id}.requirements`)
    .listen('.RequirementStatusUpdated', (e) => {
      // 1. Alert the User
      if (e.status === 'verified' || e.status === 'approved') {
        showNotification('Congratulations! Your professional verification has been approved.', 'success')
        showVerificationModal.value = false
        // 2. Reactively pushes to Sidebar
        verificationStatus.value = 'verified' 
        fetchDashboardData()
      } else if (e.status === 'rejected') {
        showNotification(`Verification Rejected: ${e.reason || 'Please update and resubmit your documents.'}`, 'error')
        showVerificationModal.value = true
        verificationStatus.value = 'rejected'
      } else {
        showNotification(`Verification Status Update: ${e.status.toUpperCase()}`, 'info')
        verificationStatus.value = e.status
      }
    })
}

// Initialization Logic
const initializeUserData = () => {
  const data = localStorage.getItem('user_data')
  if (data) {
    userData.value = JSON.parse(data)
  }
}

onMounted(async () => {
  initializeUserData()
  if (!userData.value) {
    try {
      userData.value = await getCurrentUser()
      localStorage.setItem('user_data', JSON.stringify(userData.value))
    } catch {
      router.push('/Landing/logIn')
      return 
    }
  }
  
  await fetchVerificationStatus() 
  fetchDashboardData()
  
  // Attach Websocket listener safely AFTER user is validated
  setupWebsocketListener()
})

watch(() => router.currentRoute.value.path, (path, oldPath) => {
  if (path.includes('dashboard') && !oldPath.includes('dashboard')) {
    fetchDashboardData()
  }
})

onUnmounted(() => {
  if (progressInterval) clearInterval(progressInterval)
  
  document.body.classList.remove('sp-light-mode', 'sp-dark-mode')

  if (userData.value && userData.value.id) {
    echo.leave(`user.${userData.value.id}.requirements`)
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.page-slide-enter-active, .page-slide-leave-active { transition: all 0.4s ease; }
.page-slide-enter-from { opacity: 0; transform: translateX(20px); filter: blur(4px); }
.page-slide-leave-to { opacity: 0; transform: translateX(-20px); filter: blur(4px); }
</style>

<!--
  Light mode overrides. Scoped to body.sp-light-mode so dark mode (the
  default) keeps every existing style exactly as it was. This also reaches
  teleported content (mobile sidebar, dialogs, toasts).
-->
<style>
body.sp-light-mode {
  background-color: #f8fafc;
  color-scheme: light;
}

body.sp-dark-mode {
  color-scheme: dark;
}

body.sp-light-mode .sp-layout-root {
  background-color: #f8fafc !important;
  color: #0f172a !important;
}

body.sp-light-mode [data-slot="sidebar-inset"] {
  background-color: #f1f5f9 !important;
}

/* ----- Dark surfaces become light surfaces ----- */
body.sp-light-mode [class~="bg-slate-950"],
body.sp-light-mode [class^="bg-slate-950/"],
body.sp-light-mode [class*=" bg-slate-950/"],
body.sp-light-mode [class~="bg-slate-900"],
body.sp-light-mode [class^="bg-slate-900/"],
body.sp-light-mode [class*=" bg-slate-900/"],
body.sp-light-mode [class~="bg-gray-950"],
body.sp-light-mode [class^="bg-gray-950/"],
body.sp-light-mode [class*=" bg-gray-950/"],
body.sp-light-mode [class~="bg-gray-900"],
body.sp-light-mode [class^="bg-gray-900/"],
body.sp-light-mode [class*=" bg-gray-900/"] {
  background-color: #ffffff !important;
}

body.sp-light-mode [class~="bg-slate-800"],
body.sp-light-mode [class^="bg-slate-800/"],
body.sp-light-mode [class*=" bg-slate-800/"],
body.sp-light-mode [class~="bg-gray-800"],
body.sp-light-mode [class^="bg-gray-800/"],
body.sp-light-mode [class*=" bg-gray-800/"] {
  background-color: #f1f5f9 !important;
}

body.sp-light-mode [class~="bg-slate-700"],
body.sp-light-mode [class^="bg-slate-700/"],
body.sp-light-mode [class*=" bg-slate-700/"],
body.sp-light-mode [class~="bg-gray-700"],
body.sp-light-mode [class^="bg-gray-700/"],
body.sp-light-mode [class*=" bg-gray-700/"],
body.sp-light-mode [class~="bg-slate-500"],
body.sp-light-mode [class^="bg-slate-500/"],
body.sp-light-mode [class*=" bg-slate-500/"] {
  background-color: #e2e8f0 !important;
}

body.sp-light-mode [class~="bg-slate-600"],
body.sp-light-mode [class^="bg-slate-600/"],
body.sp-light-mode [class*=" bg-slate-600/"],
body.sp-light-mode [class~="bg-gray-600"],
body.sp-light-mode [class^="bg-gray-600/"],
body.sp-light-mode [class*=" bg-gray-600/"] {
  background-color: #cbd5e1 !important;
}

/* ----- Light text becomes dark text ----- */
body.sp-light-mode [class~="text-white"],
body.sp-light-mode [class^="text-white/"],
body.sp-light-mode [class*=" text-white/"],
body.sp-light-mode [class~="text-slate-50"],
body.sp-light-mode [class~="text-slate-100"],
body.sp-light-mode [class~="text-slate-200"],
body.sp-light-mode [class~="text-gray-50"],
body.sp-light-mode [class~="text-gray-100"],
body.sp-light-mode [class~="text-gray-200"] {
  color: #0f172a !important;
}

body.sp-light-mode [class~="text-slate-300"],
body.sp-light-mode [class~="text-gray-300"] {
  color: #1e293b !important;
}

body.sp-light-mode [class~="text-slate-400"],
body.sp-light-mode [class~="text-slate-500"],
body.sp-light-mode [class~="text-gray-400"],
body.sp-light-mode [class~="text-gray-500"] {
  color: #64748b !important;
}

/* Keep white text readable on solid/gradient accent surfaces */
body.sp-light-mode [class*="text-white"][class~="bg-purple-500"],
body.sp-light-mode [class*="text-white"][class~="bg-purple-600"],
body.sp-light-mode [class*="text-white"][class~="bg-sky-500"],
body.sp-light-mode [class*="text-white"][class~="bg-sky-600"],
body.sp-light-mode [class*="text-white"][class~="bg-blue-500"],
body.sp-light-mode [class*="text-white"][class~="bg-blue-600"],
body.sp-light-mode [class*="text-white"][class~="bg-indigo-500"],
body.sp-light-mode [class*="text-white"][class~="bg-indigo-600"],
body.sp-light-mode [class*="text-white"][class~="bg-pink-500"],
body.sp-light-mode [class*="text-white"][class~="bg-pink-600"],
body.sp-light-mode [class*="text-white"][class~="bg-emerald-500"],
body.sp-light-mode [class*="text-white"][class~="bg-emerald-600"],
body.sp-light-mode [class*="text-white"][class~="bg-green-500"],
body.sp-light-mode [class*="text-white"][class~="bg-green-600"],
body.sp-light-mode [class*="text-white"][class~="bg-teal-500"],
body.sp-light-mode [class*="text-white"][class~="bg-teal-600"],
body.sp-light-mode [class*="text-white"][class~="bg-cyan-500"],
body.sp-light-mode [class*="text-white"][class~="bg-cyan-600"],
body.sp-light-mode [class*="text-white"][class~="bg-red-500"],
body.sp-light-mode [class*="text-white"][class~="bg-red-600"],
body.sp-light-mode [class*="text-white"][class~="bg-rose-500"],
body.sp-light-mode [class*="text-white"][class~="bg-rose-600"],
body.sp-light-mode [class*="text-white"][class~="bg-orange-500"],
body.sp-light-mode [class*="text-white"][class~="bg-orange-600"],
body.sp-light-mode [class*="text-white"][class~="bg-amber-500"],
body.sp-light-mode [class*="text-white"][class~="bg-amber-600"],
body.sp-light-mode [class*="text-white"][class*="bg-gradient-to-r"],
body.sp-light-mode [class*="text-white"][class*="bg-gradient-to-br"],
body.sp-light-mode [class*="text-white"][class*="bg-gradient-to-b"],
body.sp-light-mode [class*="text-white"][class*="bg-gradient-to-t"],
body.sp-light-mode [class*="text-white"][class*="bg-black"] {
  color: #ffffff !important;
}

/* Active sidebar item: white text does not work on the light gradient */
body.sp-light-mode [data-slot="sidebar"] [class*="from-purple-500"] {
  color: #7e22ce !important; /* purple-700 */
}

/* ----- Borders and dividers ----- */
body.sp-light-mode [class~="border-slate-950"],
body.sp-light-mode [class^="border-slate-950/"],
body.sp-light-mode [class*=" border-slate-950/"],
body.sp-light-mode [class~="border-slate-900"],
body.sp-light-mode [class^="border-slate-900/"],
body.sp-light-mode [class*=" border-slate-900/"],
body.sp-light-mode [class~="border-slate-800"],
body.sp-light-mode [class^="border-slate-800/"],
body.sp-light-mode [class*=" border-slate-800/"],
body.sp-light-mode [class~="border-slate-700"],
body.sp-light-mode [class^="border-slate-700/"],
body.sp-light-mode [class*=" border-slate-700/"],
body.sp-light-mode [class~="border-gray-800"],
body.sp-light-mode [class^="border-gray-800/"],
body.sp-light-mode [class*=" border-gray-800/"],
body.sp-light-mode [class~="border-gray-700"],
body.sp-light-mode [class^="border-gray-700/"],
body.sp-light-mode [class*=" border-gray-700/"] {
  border-color: #e2e8f0 !important;
}

body.sp-light-mode [class~="border-slate-600"],
body.sp-light-mode [class^="border-slate-600/"],
body.sp-light-mode [class*=" border-slate-600/"],
body.sp-light-mode [class~="border-gray-600"],
body.sp-light-mode [class^="border-gray-600/"],
body.sp-light-mode [class*=" border-gray-600/"] {
  border-color: #cbd5e1 !important;
}

body.sp-light-mode [class~="divide-slate-800"],
body.sp-light-mode [class^="divide-slate-800/"],
body.sp-light-mode [class*=" divide-slate-800/"],
body.sp-light-mode [class~="divide-slate-700"],
body.sp-light-mode [class^="divide-slate-700/"],
body.sp-light-mode [class*=" divide-slate-700/"] {
  border-color: #e2e8f0 !important;
}

body.sp-light-mode [class~="ring-offset-slate-900"] {
  --tw-ring-offset-color: #ffffff !important;
}

/* ----- Hover states ----- */
body.sp-light-mode [class*="hover:bg-slate-900"]:hover,
body.sp-light-mode [class*="hover:bg-gray-900"]:hover {
  background-color: #f1f5f9 !important;
}

body.sp-light-mode [class*="hover:bg-slate-800"]:hover,
body.sp-light-mode [class*="hover:bg-slate-700"]:hover,
body.sp-light-mode [class*="hover:bg-gray-800"]:hover,
body.sp-light-mode [class*="hover:bg-gray-700"]:hover {
  background-color: #e2e8f0 !important;
}

body.sp-light-mode [class*="hover:text-white"]:hover {
  color: #0f172a !important;
}

/* ----- Dark gradients become light gradients ----- */
body.sp-light-mode [class*="bg-gradient-to"][class*="from-slate-9"],
body.sp-light-mode [class*="bg-gradient-to"][class*="from-slate-8"],
body.sp-light-mode [class*="bg-gradient-to"][class*="from-gray-9"],
body.sp-light-mode [class*="bg-gradient-to"][class*="from-gray-8"] {
  background-image: linear-gradient(135deg, #ffffff 0%, #f1f5f9 55%, #e2e8f0 100%) !important;
}
</style>