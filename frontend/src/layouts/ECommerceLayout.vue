<template>
  <SidebarProvider>
    <div
      class="ecommerce-layout-root flex min-h-screen w-full font-sans selection:bg-indigo-500/30 overflow-hidden transition-colors duration-300"
      :class="theme === 'dark' ? 'bg-slate-900 text-slate-100' : 'bg-slate-100 text-slate-900'"
    >
    <Toaster richColors position="top-right" expand />

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

      <SideBarECommerce @logout-started="handleLogoutStart" @logout-finished="handleLogoutFinish" />

      <SidebarInset class="main-content-area bg-slate-900 border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto">
        
        <transition name="fade">
          <div 
            v-if="isLoggingOut" 
            class="fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-sm flex flex-col items-center justify-center"
          >
            <div class="w-full max-w-md p-8 text-center">
              <div class="relative mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4">
                  <LogOut class="w-12 h-12 text-indigo-400" />
                </div>
                <div class="absolute -inset-4 bg-indigo-500/10 rounded-full animate-pulse" />
              </div>
              
              <h3 class="text-2xl font-bold text-white mb-2">Ending Session</h3>
              <p class="text-slate-400 mb-8">Please wait while we securely end your distributor session...</p>
              
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
          <SidebarTrigger class="-ml-1 text-indigo-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">E-Commerce Hub</span>
        </header>

        <main class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1 text-slate-100">
          <router-view v-slot="{ Component }">
            <transition name="page-slide" mode="out-in">
              <component 
                :is="Component" 
                v-if="userData" 
                :user="userData" 
                :dashboard-data="dashboardData" 
              />
            </transition>
          </router-view>
        </main>
        
        <div
          class="absolute inset-0 bg-gradient-to-br pointer-events-none transition-opacity duration-300"
          :class="theme === 'dark'
            ? 'from-indigo-500/5 via-transparent to-transparent'
            : 'from-indigo-500/10 via-transparent to-transparent'"
        />
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Toaster } from '@/components/ui/sonner'
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import SideBarECommerce from './sideBarECommerce.vue'
import axios from '@/utils/axios'
import { LogOut, Sun, Moon } from 'lucide-vue-next'

const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)

// Theme: dark is the default for the operational hub
const theme = ref('dark')
const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

// Keep the light-mode override class on <body> so teleported content
// (mobile sidebar, dialogs, toasts) is themed too.
watch(theme, (value) => {
  document.body.classList.toggle('ecom-light-mode', value === 'light')
  document.body.classList.toggle('ecom-dark-mode', value === 'dark')
}, { immediate: true })

const isLoggingOut = ref(false)
const logoutProgress = ref(0)
let progressInterval = null

const handleLogoutStart = () => {
  isLoggingOut.value = true
  logoutProgress.value = 0
  progressInterval = setInterval(() => {
    if (logoutProgress.value < 90) logoutProgress.value += 10
  }, 300)
}

const handleLogoutFinish = () => {
  logoutProgress.value = 100
  setTimeout(() => {
    if (progressInterval) clearInterval(progressInterval)
    isLoggingOut.value = false
  }, 500)
}

const fetchData = async () => {
  try {
    const userResponse = await axios.get('/auth/me')
    userData.value = userResponse.data.user
    
    
  } catch (error) {
    console.error('Failed to fetch user data:', error)
    const stored = localStorage.getItem('user_data')
    if (stored) userData.value = JSON.parse(stored)
  }
}

onMounted(() => {
  fetchData()
})

onUnmounted(() => {
  document.body.classList.remove('ecom-light-mode', 'ecom-dark-mode')
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
  Light mode overrides. Scoped to body.ecom-light-mode so dark mode (the
  default) keeps every existing style exactly as it was. This also reaches
  teleported content (mobile sidebar, dialogs, toasts).
-->
<style>
body.ecom-light-mode {
  background-color: #f8fafc;
  color-scheme: light;
}

body.ecom-dark-mode {
  color-scheme: dark;
}

body.ecom-light-mode .ecommerce-layout-root {
  background-color: #f8fafc !important;
  color: #0f172a !important;
}

body.ecom-light-mode [data-slot="sidebar-inset"] {
  background-color: #f1f5f9 !important;
}

/* ----- Dark surfaces become light surfaces ----- */
body.ecom-light-mode [class~="bg-slate-950"],
body.ecom-light-mode [class^="bg-slate-950/"],
body.ecom-light-mode [class*=" bg-slate-950/"],
body.ecom-light-mode [class~="bg-slate-900"],
body.ecom-light-mode [class^="bg-slate-900/"],
body.ecom-light-mode [class*=" bg-slate-900/"],
body.ecom-light-mode [class~="bg-gray-950"],
body.ecom-light-mode [class^="bg-gray-950/"],
body.ecom-light-mode [class*=" bg-gray-950/"],
body.ecom-light-mode [class~="bg-gray-900"],
body.ecom-light-mode [class^="bg-gray-900/"],
body.ecom-light-mode [class*=" bg-gray-900/"] {
  background-color: #ffffff !important;
}

body.ecom-light-mode [class~="bg-slate-800"],
body.ecom-light-mode [class^="bg-slate-800/"],
body.ecom-light-mode [class*=" bg-slate-800/"],
body.ecom-light-mode [class~="bg-gray-800"],
body.ecom-light-mode [class^="bg-gray-800/"],
body.ecom-light-mode [class*=" bg-gray-800/"] {
  background-color: #f1f5f9 !important;
}

body.ecom-light-mode [class~="bg-slate-700"],
body.ecom-light-mode [class^="bg-slate-700/"],
body.ecom-light-mode [class*=" bg-slate-700/"],
body.ecom-light-mode [class~="bg-gray-700"],
body.ecom-light-mode [class^="bg-gray-700/"],
body.ecom-light-mode [class*=" bg-gray-700/"],
body.ecom-light-mode [class~="bg-slate-500"],
body.ecom-light-mode [class^="bg-slate-500/"],
body.ecom-light-mode [class*=" bg-slate-500/"] {
  background-color: #e2e8f0 !important;
}

body.ecom-light-mode [class~="bg-slate-600"],
body.ecom-light-mode [class^="bg-slate-600/"],
body.ecom-light-mode [class*=" bg-slate-600/"],
body.ecom-light-mode [class~="bg-gray-600"],
body.ecom-light-mode [class^="bg-gray-600/"],
body.ecom-light-mode [class*=" bg-gray-600/"] {
  background-color: #cbd5e1 !important;
}

/* ----- Light text becomes dark text ----- */
body.ecom-light-mode [class~="text-white"],
body.ecom-light-mode [class^="text-white/"],
body.ecom-light-mode [class*=" text-white/"],
body.ecom-light-mode [class~="text-slate-50"],
body.ecom-light-mode [class~="text-slate-100"],
body.ecom-light-mode [class~="text-slate-200"],
body.ecom-light-mode [class~="text-gray-50"],
body.ecom-light-mode [class~="text-gray-100"],
body.ecom-light-mode [class~="text-gray-200"] {
  color: #0f172a !important;
}

body.ecom-light-mode [class~="text-slate-300"],
body.ecom-light-mode [class~="text-gray-300"] {
  color: #1e293b !important;
}

body.ecom-light-mode [class~="text-slate-400"],
body.ecom-light-mode [class~="text-slate-500"],
body.ecom-light-mode [class~="text-gray-400"],
body.ecom-light-mode [class~="text-gray-500"] {
  color: #64748b !important;
}

/* Keep white text readable on solid/gradient accent surfaces */
body.ecom-light-mode [class*="text-white"][class~="bg-sky-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-sky-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-blue-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-blue-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-indigo-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-indigo-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-purple-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-purple-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-pink-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-pink-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-emerald-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-emerald-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-green-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-green-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-teal-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-teal-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-cyan-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-cyan-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-red-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-red-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-rose-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-rose-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-orange-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-orange-600"],
body.ecom-light-mode [class*="text-white"][class~="bg-amber-500"],
body.ecom-light-mode [class*="text-white"][class~="bg-amber-600"],
body.ecom-light-mode [class*="text-white"][class*="bg-gradient-to-r"],
body.ecom-light-mode [class*="text-white"][class*="bg-gradient-to-br"],
body.ecom-light-mode [class*="text-white"][class*="bg-gradient-to-b"],
body.ecom-light-mode [class*="text-white"][class*="bg-gradient-to-t"],
body.ecom-light-mode [class*="text-white"][class*="bg-black"] {
  color: #ffffff !important;
}

/* Active sidebar item: white text does not work on the light gradient */
body.ecom-light-mode [data-slot="sidebar"] [class*="from-indigo-500"] {
  color: #4338ca !important;
}

/* ----- Borders and dividers ----- */
body.ecom-light-mode [class~="border-slate-950"],
body.ecom-light-mode [class^="border-slate-950/"],
body.ecom-light-mode [class*=" border-slate-950/"],
body.ecom-light-mode [class~="border-slate-900"],
body.ecom-light-mode [class^="border-slate-900/"],
body.ecom-light-mode [class*=" border-slate-900/"],
body.ecom-light-mode [class~="border-slate-800"],
body.ecom-light-mode [class^="border-slate-800/"],
body.ecom-light-mode [class*=" border-slate-800/"],
body.ecom-light-mode [class~="border-slate-700"],
body.ecom-light-mode [class^="border-slate-700/"],
body.ecom-light-mode [class*=" border-slate-700/"],
body.ecom-light-mode [class~="border-gray-800"],
body.ecom-light-mode [class^="border-gray-800/"],
body.ecom-light-mode [class*=" border-gray-800/"],
body.ecom-light-mode [class~="border-gray-700"],
body.ecom-light-mode [class^="border-gray-700/"],
body.ecom-light-mode [class*=" border-gray-700/"] {
  border-color: #e2e8f0 !important;
}

body.ecom-light-mode [class~="border-slate-600"],
body.ecom-light-mode [class^="border-slate-600/"],
body.ecom-light-mode [class*=" border-slate-600/"],
body.ecom-light-mode [class~="border-gray-600"],
body.ecom-light-mode [class^="border-gray-600/"],
body.ecom-light-mode [class*=" border-gray-600/"] {
  border-color: #cbd5e1 !important;
}

body.ecom-light-mode [class~="divide-slate-800"],
body.ecom-light-mode [class^="divide-slate-800/"],
body.ecom-light-mode [class*=" divide-slate-800/"],
body.ecom-light-mode [class~="divide-slate-700"],
body.ecom-light-mode [class^="divide-slate-700/"],
body.ecom-light-mode [class*=" divide-slate-700/"] {
  border-color: #e2e8f0 !important;
}

body.ecom-light-mode [class~="ring-offset-slate-900"] {
  --tw-ring-offset-color: #ffffff !important;
}

/* ----- Hover states ----- */
body.ecom-light-mode [class*="hover:bg-slate-900"]:hover,
body.ecom-light-mode [class*="hover:bg-gray-900"]:hover {
  background-color: #f1f5f9 !important;
}

body.ecom-light-mode [class*="hover:bg-slate-800"]:hover,
body.ecom-light-mode [class*="hover:bg-slate-700"]:hover,
body.ecom-light-mode [class*="hover:bg-gray-800"]:hover,
body.ecom-light-mode [class*="hover:bg-gray-700"]:hover {
  background-color: #e2e8f0 !important;
}

body.ecom-light-mode [class*="hover:text-white"]:hover {
  color: #0f172a !important;
}

/* ----- Dark gradients become light gradients ----- */
body.ecom-light-mode [class*="bg-gradient-to"][class*="from-slate-9"],
body.ecom-light-mode [class*="bg-gradient-to"][class*="from-slate-8"],
body.ecom-light-mode [class*="bg-gradient-to"][class*="from-gray-9"],
body.ecom-light-mode [class*="bg-gradient-to"][class*="from-gray-8"] {
  background-image: linear-gradient(135deg, #ffffff 0%, #f1f5f9 55%, #e2e8f0 100%) !important;
}
</style>