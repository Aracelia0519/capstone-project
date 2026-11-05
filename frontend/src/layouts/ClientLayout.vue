<template>
  <SidebarProvider>
    <div
      class="client-layout-root flex min-h-screen w-full font-sans selection:bg-sky-500/30 overflow-hidden transition-colors duration-300"
      :class="theme === 'dark' ? 'bg-slate-900 text-slate-100' : 'bg-slate-100 text-slate-900'"
    >
      <Toaster position="top-right" />

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
        redirect-route="/Clients/profileC"
        description-text="To access all client features like color previews and service requests, please complete your identity verification."
      />

      <SideBarClient 
        :verification-status="verificationStatus"
        @open-verification-modal="showVerificationModal = true"
        @logout-started="handleLogoutStart" 
        @logout-finished="handleLogoutFinish" 
      />

      <SidebarInset class="main-content-area bg-slate-900 border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto">
        <transition name="fade">
          <div 
            v-if="isLoggingOut" 
            class="fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-sm flex flex-col items-center justify-center"
          >
            <div class="w-full max-w-md p-8 text-center">
              <div class="relative mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-sky-500/20 to-cyan-500/20 flex items-center justify-center mb-4">
                  <LogOut class="w-12 h-12 text-sky-400" />
                </div>
                <div class="absolute -inset-4 bg-sky-500/10 rounded-full animate-pulse" />
              </div>
              
              <h3 class="text-2xl font-bold text-white mb-2">Logging Out</h3>
              <p class="text-slate-400 mb-8">Please wait while we end your session...</p>
              
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

        <header class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/50">
          <SidebarTrigger class="-ml-1 text-sky-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Client Portal</span>
        </header>

        <main class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1">
          <Suspense>
            <template #default>
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
            </template>
            <template #fallback>
              <div class="flex items-center justify-center min-h-[50vh]">
                <div class="w-12 h-12 border-3 border-sky-500/30 border-t-sky-500 rounded-full animate-spin" />
              </div>
            </template>
          </Suspense>
        </main>

        <div
          class="absolute inset-0 bg-gradient-to-br pointer-events-none transition-opacity duration-300"
          :class="theme === 'dark'
            ? 'from-slate-900 via-slate-900 to-sky-900/10 opacity-50'
            : 'from-slate-100 via-slate-100 to-sky-100/40 opacity-40'"
        />
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Toaster, toast } from 'vue-sonner' // Added toast
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import SideBarClient from './sideBarClient.vue'
import VerificationModal from './VerificationModal.vue'
import axios from '@/utils/axios'
import echo from '@/utils/websocket' // Added Echo import
import { getCurrentUser } from '@/utils/auth'
import { LogOut, Sun, Moon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)
const verificationStatus = ref(null)
const showVerificationModal = ref(false)

// Theme: dark is the default for the client portal
const theme = ref('dark')
const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

// Keep the light-mode override class on <body> so teleported content
// (mobile sidebar, dialogs, toasts) is themed too.
watch(theme, (value) => {
  document.body.classList.toggle('client-light-mode', value === 'light')
  document.body.classList.toggle('client-dark-mode', value === 'dark')
}, { immediate: true })

const isLoggingOut = ref(false)
const logoutProgress = ref(0)
let progressInterval = null

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

watch(() => route.query, (newQuery) => {
  if (newQuery.verification_required === 'true') {
    verificationStatus.value = newQuery.status || 'none'
    showVerificationModal.value = true
  }
}, { immediate: true })

const fetchVerificationStatus = async () => {
  if (!userData.value || userData.value.role !== 'client') return
  try {
    const res = await axios.get('/client/requirements')
    if (res.data.status === 'success') {
      verificationStatus.value = res.data.id_verification?.status || 'none'
    }
  } catch (e) {
    console.error('Failed to fetch verification status:', e)
  }
}

const initializeUserData = () => {
  const stored = localStorage.getItem('user_data')
  if (stored) userData.value = JSON.parse(stored)
}

const fetchDashboardData = async () => {
  if (!userData.value) return
  const now = Date.now()
  const lastFetch = localStorage.getItem(`last_dashboard_fetch_${userData.value.role}`)
  
  if (lastFetch && dashboardData.value && (now - parseInt(lastFetch)) < 120000) return

  isLoading.value = true
  try {
    const endpoints = { 
      admin: '/dashboard/admin', 
      distributor: '/dashboard/distributor', 
      service_provider: '/dashboard/service-provider', 
      client: '/dashboard/client' 
    }
    if (endpoints[userData.value.role]) {
      const res = await axios.get(endpoints[userData.value.role])
      dashboardData.value = res.data.data
      localStorage.setItem(`last_dashboard_fetch_${userData.value.role}`, now.toString())
    }
  } catch (e) {
    console.error('Dashboard fetch error:', e)
  } finally {
    setTimeout(() => { isLoading.value = false }, 500)
  }
}

// Set up WebSocket listener specifically for requirement status changes
const setupWebsocketListener = () => {
  if (!userData.value || !userData.value.id) return
  
  echo.private(`user.${userData.value.id}.requirements`)
    .listen('.RequirementStatusUpdated', (e) => {
      // Instantly react to update
      verificationStatus.value = e.status
      
      if (e.status === 'approved' || e.status === 'verified') {
        showVerificationModal.value = false
        toast.success('Your identity verification has been approved! You now have full access.')
      } else if (e.status === 'rejected') {
        toast.error('Your identity verification was rejected. Reason: ' + (e.reason || 'Please check your profile for details.'))
      }
    })
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
  
  // Attach Websocket listener after user is validated
  setupWebsocketListener()
})

watch(() => router.currentRoute.value.path, (path, oldPath) => {
  if (path.includes('dashboard') && !oldPath.includes('dashboard')) {
    fetchDashboardData()
  }
})

onUnmounted(() => {
  if (progressInterval) clearInterval(progressInterval)

  document.body.classList.remove('client-light-mode', 'client-dark-mode')
  
  // Cleanup websocket listener to prevent memory leaks
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
@keyframes pulse-slow { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.05); } }
.animate-pulse-slow { animation: pulse-slow 8s ease-in-out infinite; }
</style>

<!--
  Light mode overrides. Scoped to body.client-light-mode so dark mode (the
  default) keeps every existing style exactly as it was. This also reaches
  teleported content (mobile sidebar, dialogs, toasts).
-->
<style>
body.client-light-mode {
  background-color: #f8fafc;
  color-scheme: light;
}

body.client-dark-mode {
  color-scheme: dark;
}

body.client-light-mode .client-layout-root {
  background-color: #f8fafc !important;
  color: #0f172a !important;
}

body.client-light-mode [data-slot="sidebar-inset"] {
  background-color: #f1f5f9 !important;
}

/* ----- Dark surfaces become light surfaces ----- */
body.client-light-mode [class~="bg-slate-950"],
body.client-light-mode [class^="bg-slate-950/"],
body.client-light-mode [class*=" bg-slate-950/"],
body.client-light-mode [class~="bg-slate-900"],
body.client-light-mode [class^="bg-slate-900/"],
body.client-light-mode [class*=" bg-slate-900/"],
body.client-light-mode [class~="bg-gray-950"],
body.client-light-mode [class^="bg-gray-950/"],
body.client-light-mode [class*=" bg-gray-950/"],
body.client-light-mode [class~="bg-gray-900"],
body.client-light-mode [class^="bg-gray-900/"],
body.client-light-mode [class*=" bg-gray-900/"] {
  background-color: #ffffff !important;
}

body.client-light-mode [class~="bg-slate-800"],
body.client-light-mode [class^="bg-slate-800/"],
body.client-light-mode [class*=" bg-slate-800/"],
body.client-light-mode [class~="bg-gray-800"],
body.client-light-mode [class^="bg-gray-800/"],
body.client-light-mode [class*=" bg-gray-800/"] {
  background-color: #f1f5f9 !important;
}

body.client-light-mode [class~="bg-slate-700"],
body.client-light-mode [class^="bg-slate-700/"],
body.client-light-mode [class*=" bg-slate-700/"],
body.client-light-mode [class~="bg-gray-700"],
body.client-light-mode [class^="bg-gray-700/"],
body.client-light-mode [class*=" bg-gray-700/"],
body.client-light-mode [class~="bg-slate-500"],
body.client-light-mode [class^="bg-slate-500/"],
body.client-light-mode [class*=" bg-slate-500/"] {
  background-color: #e2e8f0 !important;
}

body.client-light-mode [class~="bg-slate-600"],
body.client-light-mode [class^="bg-slate-600/"],
body.client-light-mode [class*=" bg-slate-600/"],
body.client-light-mode [class~="bg-gray-600"],
body.client-light-mode [class^="bg-gray-600/"],
body.client-light-mode [class*=" bg-gray-600/"] {
  background-color: #cbd5e1 !important;
}

/* ----- Light text becomes dark text ----- */
body.client-light-mode [class~="text-white"],
body.client-light-mode [class^="text-white/"],
body.client-light-mode [class*=" text-white/"],
body.client-light-mode [class~="text-slate-50"],
body.client-light-mode [class~="text-slate-100"],
body.client-light-mode [class~="text-slate-200"],
body.client-light-mode [class~="text-gray-50"],
body.client-light-mode [class~="text-gray-100"],
body.client-light-mode [class~="text-gray-200"] {
  color: #0f172a !important;
}

body.client-light-mode [class~="text-slate-300"],
body.client-light-mode [class~="text-gray-300"] {
  color: #1e293b !important;
}

body.client-light-mode [class~="text-slate-400"],
body.client-light-mode [class~="text-slate-500"],
body.client-light-mode [class~="text-gray-400"],
body.client-light-mode [class~="text-gray-500"] {
  color: #64748b !important;
}

/* Keep white text readable on solid/gradient accent surfaces */
body.client-light-mode [class*="text-white"][class~="bg-sky-500"],
body.client-light-mode [class*="text-white"][class~="bg-sky-600"],
body.client-light-mode [class*="text-white"][class~="bg-blue-500"],
body.client-light-mode [class*="text-white"][class~="bg-blue-600"],
body.client-light-mode [class*="text-white"][class~="bg-indigo-500"],
body.client-light-mode [class*="text-white"][class~="bg-indigo-600"],
body.client-light-mode [class*="text-white"][class~="bg-purple-500"],
body.client-light-mode [class*="text-white"][class~="bg-purple-600"],
body.client-light-mode [class*="text-white"][class~="bg-pink-500"],
body.client-light-mode [class*="text-white"][class~="bg-pink-600"],
body.client-light-mode [class*="text-white"][class~="bg-emerald-500"],
body.client-light-mode [class*="text-white"][class~="bg-emerald-600"],
body.client-light-mode [class*="text-white"][class~="bg-green-500"],
body.client-light-mode [class*="text-white"][class~="bg-green-600"],
body.client-light-mode [class*="text-white"][class~="bg-teal-500"],
body.client-light-mode [class*="text-white"][class~="bg-teal-600"],
body.client-light-mode [class*="text-white"][class~="bg-cyan-500"],
body.client-light-mode [class*="text-white"][class~="bg-cyan-600"],
body.client-light-mode [class*="text-white"][class~="bg-red-500"],
body.client-light-mode [class*="text-white"][class~="bg-red-600"],
body.client-light-mode [class*="text-white"][class~="bg-rose-500"],
body.client-light-mode [class*="text-white"][class~="bg-rose-600"],
body.client-light-mode [class*="text-white"][class~="bg-orange-500"],
body.client-light-mode [class*="text-white"][class~="bg-orange-600"],
body.client-light-mode [class*="text-white"][class~="bg-amber-500"],
body.client-light-mode [class*="text-white"][class~="bg-amber-600"],
body.client-light-mode [class*="text-white"][class*="bg-gradient-to-r"],
body.client-light-mode [class*="text-white"][class*="bg-gradient-to-br"],
body.client-light-mode [class*="text-white"][class*="bg-gradient-to-b"],
body.client-light-mode [class*="text-white"][class*="bg-gradient-to-t"],
body.client-light-mode [class*="text-white"][class*="bg-black"] {
  color: #ffffff !important;
}

/* Active sidebar item: white text does not work on the light gradient */
body.client-light-mode [data-slot="sidebar"] [class*="from-sky-500"] {
  color: #0369a1 !important;
}

/* ----- Borders and dividers ----- */
body.client-light-mode [class~="border-slate-950"],
body.client-light-mode [class^="border-slate-950/"],
body.client-light-mode [class*=" border-slate-950/"],
body.client-light-mode [class~="border-slate-900"],
body.client-light-mode [class^="border-slate-900/"],
body.client-light-mode [class*=" border-slate-900/"],
body.client-light-mode [class~="border-slate-800"],
body.client-light-mode [class^="border-slate-800/"],
body.client-light-mode [class*=" border-slate-800/"],
body.client-light-mode [class~="border-slate-700"],
body.client-light-mode [class^="border-slate-700/"],
body.client-light-mode [class*=" border-slate-700/"],
body.client-light-mode [class~="border-gray-800"],
body.client-light-mode [class^="border-gray-800/"],
body.client-light-mode [class*=" border-gray-800/"],
body.client-light-mode [class~="border-gray-700"],
body.client-light-mode [class^="border-gray-700/"],
body.client-light-mode [class*=" border-gray-700/"] {
  border-color: #e2e8f0 !important;
}

body.client-light-mode [class~="border-slate-600"],
body.client-light-mode [class^="border-slate-600/"],
body.client-light-mode [class*=" border-slate-600/"],
body.client-light-mode [class~="border-gray-600"],
body.client-light-mode [class^="border-gray-600/"],
body.client-light-mode [class*=" border-gray-600/"] {
  border-color: #cbd5e1 !important;
}

body.client-light-mode [class~="divide-slate-800"],
body.client-light-mode [class^="divide-slate-800/"],
body.client-light-mode [class*=" divide-slate-800/"],
body.client-light-mode [class~="divide-slate-700"],
body.client-light-mode [class^="divide-slate-700/"],
body.client-light-mode [class*=" divide-slate-700/"] {
  border-color: #e2e8f0 !important;
}

body.client-light-mode [class~="ring-offset-slate-900"] {
  --tw-ring-offset-color: #ffffff !important;
}

/* ----- Hover states ----- */
body.client-light-mode [class*="hover:bg-slate-900"]:hover,
body.client-light-mode [class*="hover:bg-gray-900"]:hover {
  background-color: #f1f5f9 !important;
}

body.client-light-mode [class*="hover:bg-slate-800"]:hover,
body.client-light-mode [class*="hover:bg-slate-700"]:hover,
body.client-light-mode [class*="hover:bg-gray-800"]:hover,
body.client-light-mode [class*="hover:bg-gray-700"]:hover {
  background-color: #e2e8f0 !important;
}

body.client-light-mode [class*="hover:text-white"]:hover {
  color: #0f172a !important;
}

/* ----- Dark gradients become light gradients ----- */
body.client-light-mode [class*="bg-gradient-to"][class*="from-slate-9"],
body.client-light-mode [class*="bg-gradient-to"][class*="from-slate-8"],
body.client-light-mode [class*="bg-gradient-to"][class*="from-gray-9"],
body.client-light-mode [class*="bg-gradient-to"][class*="from-gray-8"] {
  background-image: linear-gradient(135deg, #ffffff 0%, #f1f5f9 55%, #e2e8f0 100%) !important;
}
</style>