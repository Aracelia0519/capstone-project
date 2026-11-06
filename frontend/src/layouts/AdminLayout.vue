<template>
  <SidebarProvider>
    <div
      class="admin-layout-root flex min-h-screen w-full font-sans selection:bg-blue-500/30 overflow-hidden transition-colors duration-300"
      :class="theme === 'dark' ? 'bg-slate-900 text-slate-100' : 'bg-slate-100 text-slate-900'"
    >
      <Toaster position="top-right" />

      <!-- Dark / Light mode toggle (default: light) -->
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

      <sideBarAdmin @logout-started="handleLogoutStart" @logout-finished="handleLogoutFinish" />

      <SidebarInset
        class="main-content-area border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto"
        :class="theme === 'dark' ? 'bg-slate-900' : 'bg-white'"
      >
        
        <transition name="fade">
          <div 
            v-if="isLoggingOut" 
            class="fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-sm flex flex-col items-center justify-center"
          >
            <div class="w-full max-w-md p-8 text-center">
              <div class="relative mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-blue-500/20 to-purple-500/20 flex items-center justify-center mb-4">
                  <LogOut class="w-12 h-12 text-blue-400" />
                </div>
                <div class="absolute -inset-4 bg-blue-500/10 rounded-full animate-pulse" />
              </div>
              
              <h3 class="text-2xl font-bold text-white mb-2">Ending Admin Session</h3>
              <p class="text-slate-400 mb-8">Please wait while we securely terminate your session...</p>
              
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
          class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 backdrop-blur-md border-b transition-colors duration-300"
          :class="theme === 'dark' ? 'bg-slate-900/80 border-slate-800/50' : 'bg-white/80 border-slate-200'"
        >
          <SidebarTrigger class="-ml-1 text-blue-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Admin Panel</span>
        </header>

        <main
          class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1 transition-colors duration-300"
          :class="theme === 'dark' ? 'text-slate-100' : 'text-slate-900'"
        >
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
import sideBarAdmin from '@/layouts/sideBarAdmin.vue'
import axios from '@/utils/axios'
import { LogOut, Sun, Moon } from 'lucide-vue-next'

const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)

// Theme: light is the default for the admin panel
const theme = ref('light')
const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

// Keep the theme override class on <body> so teleported content
// (mobile sidebar, dialogs, toasts) is themed too.
watch(theme, (value) => {
  document.body.classList.toggle('admin-light-mode', value === 'light')
  document.body.classList.toggle('admin-dark-mode', value === 'dark')
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
    
    if (userData.value.role === 'admin') {
      const res = await axios.get('/dashboard/admin')
      dashboardData.value = res.data.data
    }
  } catch (error) {
    console.error('Failed to fetch data:', error)
    router.push('/Landing/logIn')
  }
}

onMounted(() => {
  fetchData()
})

onUnmounted(() => {
  document.body.classList.remove('admin-light-mode', 'admin-dark-mode')
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.page-slide-enter-active, .page-slide-leave-active { transition: all 0.4s ease; }
.page-slide-enter-from { opacity: 0; transform: translateX(20px); }
.page-slide-leave-to { opacity: 0; transform: translateX(-20px); }
</style>

<!--
  Light mode overrides. Scoped to body.admin-light-mode so dark mode keeps the
  original styles. Light is the default for the admin panel, so this converts
  the dark sidebar (and its teleported logout dialog) to light. The main
  content is already light-themed, so it is left untouched.
-->
<style>
body.admin-light-mode {
  background-color: #f8fafc;
  color-scheme: light;
}

body.admin-dark-mode {
  color-scheme: dark;
}

body.admin-light-mode .admin-layout-root {
  background-color: #f8fafc !important;
  color: #0f172a !important;
}

/* ----- Dark sidebar surfaces become light ----- */
body.admin-light-mode [data-slot="sidebar"] [class~="bg-slate-950"],
body.admin-light-mode [data-slot="sidebar"] [class^="bg-slate-950/"],
body.admin-light-mode [data-slot="sidebar"] [class*=" bg-slate-950/"],
body.admin-light-mode [data-slot="sidebar"] [class~="bg-slate-900"],
body.admin-light-mode [data-slot="sidebar"] [class^="bg-slate-900/"],
body.admin-light-mode [data-slot="sidebar"] [class*=" bg-slate-900/"],
body.admin-light-mode [data-slot="sidebar"] [class~="bg-gray-950"],
body.admin-light-mode [data-slot="sidebar"] [class~="bg-gray-900"] {
  background-color: #ffffff !important;
}

body.admin-light-mode [data-slot="sidebar"] [class~="bg-slate-800"],
body.admin-light-mode [data-slot="sidebar"] [class^="bg-slate-800/"],
body.admin-light-mode [data-slot="sidebar"] [class*=" bg-slate-800/"],
body.admin-light-mode [data-slot="sidebar"] [class~="bg-gray-800"] {
  background-color: #f1f5f9 !important;
}

body.admin-light-mode [data-slot="sidebar"] [class~="bg-slate-700"],
body.admin-light-mode [data-slot="sidebar"] [class^="bg-slate-700/"],
body.admin-light-mode [data-slot="sidebar"] [class*=" bg-slate-700/"],
body.admin-light-mode [data-slot="sidebar"] [class~="bg-gray-700"],
body.admin-light-mode [data-slot="sidebar"] [class~="bg-slate-500"] {
  background-color: #e2e8f0 !important;
}

body.admin-light-mode [data-slot="sidebar"] [class~="bg-slate-600"],
body.admin-light-mode [data-slot="sidebar"] [class~="bg-gray-600"] {
  background-color: #cbd5e1 !important;
}

/* ----- Light sidebar text becomes dark ----- */
body.admin-light-mode [data-slot="sidebar"] [class~="text-white"],
body.admin-light-mode [data-slot="sidebar"] [class^="text-white/"],
body.admin-light-mode [data-slot="sidebar"] [class*=" text-white/"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-slate-50"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-slate-100"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-slate-200"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-gray-50"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-gray-100"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-gray-200"] {
  color: #0f172a !important;
}

body.admin-light-mode [data-slot="sidebar"] [class~="text-slate-300"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-gray-300"] {
  color: #1e293b !important;
}

body.admin-light-mode [data-slot="sidebar"] [class~="text-slate-400"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-slate-500"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-gray-400"],
body.admin-light-mode [data-slot="sidebar"] [class~="text-gray-500"] {
  color: #64748b !important;
}

/* Keep white icons readable on the coloured avatar gradient */
body.admin-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"] [class~="text-white"] {
  color: #ffffff !important;
}

/* Active sidebar item: white text does not work on the light gradient */
body.admin-light-mode [data-slot="sidebar"] [class*="from-blue-500/"] {
  color: #1d4ed8 !important;
}

/* ----- Borders and dividers ----- */
body.admin-light-mode [data-slot="sidebar"] [class~="border-slate-950"],
body.admin-light-mode [data-slot="sidebar"] [class^="border-slate-950/"],
body.admin-light-mode [data-slot="sidebar"] [class~="border-slate-900"],
body.admin-light-mode [data-slot="sidebar"] [class~="border-slate-800"],
body.admin-light-mode [data-slot="sidebar"] [class^="border-slate-800/"],
body.admin-light-mode [data-slot="sidebar"] [class*=" border-slate-800/"],
body.admin-light-mode [data-slot="sidebar"] [class~="border-slate-700"],
body.admin-light-mode [data-slot="sidebar"] [class^="border-slate-700/"],
body.admin-light-mode [data-slot="sidebar"] [class*=" border-slate-700/"],
body.admin-light-mode [data-slot="sidebar"] [class~="border-gray-800"],
body.admin-light-mode [data-slot="sidebar"] [class~="border-gray-700"] {
  border-color: #e2e8f0 !important;
}

body.admin-light-mode [data-slot="sidebar"] [class~="border-slate-600"],
body.admin-light-mode [data-slot="sidebar"] [class~="border-gray-600"] {
  border-color: #cbd5e1 !important;
}

body.admin-light-mode [data-slot="sidebar"] [class~="ring-offset-slate-900"] {
  --tw-ring-offset-color: #ffffff !important;
}

/* ----- Sidebar hover states ----- */
body.admin-light-mode [data-slot="sidebar"] [class*="hover:bg-slate-900"]:hover,
body.admin-light-mode [data-slot="sidebar"] [class*="hover:bg-gray-900"]:hover {
  background-color: #f1f5f9 !important;
}

body.admin-light-mode [data-slot="sidebar"] [class*="hover:bg-slate-800"]:hover,
body.admin-light-mode [data-slot="sidebar"] [class*="hover:bg-slate-700"]:hover,
body.admin-light-mode [data-slot="sidebar"] [class*="hover:bg-gray-800"]:hover,
body.admin-light-mode [data-slot="sidebar"] [class*="hover:bg-gray-700"]:hover {
  background-color: #e2e8f0 !important;
}

body.admin-light-mode [data-slot="sidebar"] [class*="hover:text-white"]:hover {
  color: #0f172a !important;
}

/* ----- Dark sidebar gradients become light gradients ----- */
body.admin-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-slate-9"],
body.admin-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-slate-8"],
body.admin-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-gray-9"],
body.admin-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-gray-8"] {
  background-image: linear-gradient(135deg, #ffffff 0%, #f1f5f9 55%, #e2e8f0 100%) !important;
}

/* ----- Teleported sidebar logout dialog ----- */
body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] {
  background-color: #ffffff !important;
  color: #0f172a !important;
  border-color: #e2e8f0 !important;
}

body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-white"],
body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-slate-200"] {
  color: #0f172a !important;
}

body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-slate-400"] {
  color: #64748b !important;
}

body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="border-slate-700"],
body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="border-slate-800"] {
  border-color: #e2e8f0 !important;
}

body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="bg-slate-800"],
body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="bg-slate-800/50"],
body.admin-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="bg-slate-700"] {
  background-color: #f1f5f9 !important;
}
</style>

<!--
  Dark mode overrides. Scoped to body.admin-dark-mode so light mode (the
  default) keeps every existing style exactly as it was. The admin pages are
  light-themed, so this converts their light surfaces, text and borders to dark
  equivalents while keeping accent colours intact.
-->
<style>
body.admin-dark-mode {
  background-color: #0f172a;
}

body.admin-dark-mode .admin-layout-root {
  background-color: #0f172a !important;
  color: #f1f5f9 !important;
}

body.admin-dark-mode [data-slot="dialog-content"],
body.admin-dark-mode [data-slot="alert-dialog-content"],
body.admin-dark-mode [data-slot="sheet-content"],
body.admin-dark-mode [data-slot="select-content"],
body.admin-dark-mode [data-slot="dropdown-menu-content"],
body.admin-dark-mode [data-slot="popover-content"] {
  background-color: #0f172a !important;
  color: #e2e8f0 !important;
  border-color: #334155 !important;
}

/* ----- Light surfaces become dark surfaces ----- */
body.admin-dark-mode [class~="bg-white"],
body.admin-dark-mode [class^="bg-white/"],
body.admin-dark-mode [class*=" bg-white/"] {
  background-color: #0f172a !important;
}

body.admin-dark-mode [class~="bg-slate-50"],
body.admin-dark-mode [class^="bg-slate-50/"],
body.admin-dark-mode [class*=" bg-slate-50/"],
body.admin-dark-mode [class~="bg-gray-50"],
body.admin-dark-mode [class^="bg-gray-50/"],
body.admin-dark-mode [class*=" bg-gray-50/"] {
  background-color: #1e293b !important;
}

body.admin-dark-mode [class~="bg-slate-100"],
body.admin-dark-mode [class^="bg-slate-100/"],
body.admin-dark-mode [class*=" bg-slate-100/"],
body.admin-dark-mode [class~="bg-gray-100"],
body.admin-dark-mode [class^="bg-gray-100/"],
body.admin-dark-mode [class*=" bg-gray-100/"] {
  background-color: #1e293b !important;
}

body.admin-dark-mode [class~="bg-slate-200"],
body.admin-dark-mode [class^="bg-slate-200/"],
body.admin-dark-mode [class*=" bg-slate-200/"],
body.admin-dark-mode [class~="bg-gray-200"],
body.admin-dark-mode [class^="bg-gray-200/"],
body.admin-dark-mode [class*=" bg-gray-200/"] {
  background-color: #334155 !important;
}

/* ----- Dark text becomes light text ----- */
body.admin-dark-mode [class~="text-gray-900"],
body.admin-dark-mode [class~="text-slate-900"],
body.admin-dark-mode [class~="text-gray-800"],
body.admin-dark-mode [class~="text-slate-800"] {
  color: #f1f5f9 !important;
}

body.admin-dark-mode [class~="text-gray-700"],
body.admin-dark-mode [class~="text-slate-700"] {
  color: #e2e8f0 !important;
}

body.admin-dark-mode [class~="text-gray-600"],
body.admin-dark-mode [class~="text-slate-600"] {
  color: #cbd5e1 !important;
}

body.admin-dark-mode [class~="text-gray-500"],
body.admin-dark-mode [class~="text-slate-500"],
body.admin-dark-mode [class~="text-gray-400"],
body.admin-dark-mode [class~="text-slate-400"] {
  color: #94a3b8 !important;
}

body.admin-dark-mode [class~="text-gray-300"],
body.admin-dark-mode [class~="text-slate-300"] {
  color: #cbd5e1 !important;
}

/* ----- Light borders and dividers become dark ----- */
body.admin-dark-mode [class~="border-slate-100"],
body.admin-dark-mode [class~="border-gray-100"],
body.admin-dark-mode [class~="divide-slate-100"],
body.admin-dark-mode [class~="divide-gray-200"] {
  border-color: #1e293b !important;
}

body.admin-dark-mode [class~="border-slate-200"],
body.admin-dark-mode [class~="border-gray-200"] {
  border-color: #334155 !important;
}

body.admin-dark-mode [class~="border-slate-300"],
body.admin-dark-mode [class~="border-gray-300"] {
  border-color: #475569 !important;
}

/* ----- Hover states ----- */
body.admin-dark-mode [class*="hover:bg-slate-50"]:hover,
body.admin-dark-mode [class*="hover:bg-gray-50"]:hover {
  background-color: #1e293b !important;
}

body.admin-dark-mode [class*="hover:bg-slate-100"]:hover,
body.admin-dark-mode [class*="hover:bg-gray-100"]:hover {
  background-color: #334155 !important;
}

body.admin-dark-mode [class*="hover:bg-slate-200"]:hover,
body.admin-dark-mode [class*="hover:bg-gray-200"]:hover {
  background-color: #475569 !important;
}

body.admin-dark-mode [class*="hover:text-slate-900"]:hover,
body.admin-dark-mode [class*="hover:text-gray-900"]:hover,
body.admin-dark-mode [class*="hover:text-slate-800"]:hover,
body.admin-dark-mode [class*="hover:text-slate-700"]:hover {
  color: #f1f5f9 !important;
}

body.admin-dark-mode input::placeholder,
body.admin-dark-mode textarea::placeholder {
  color: #64748b;
}
</style>