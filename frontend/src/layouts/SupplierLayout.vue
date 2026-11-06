<template>
  <SidebarProvider>
    <div
      class="supplier-layout-root flex min-h-screen w-full font-sans selection:bg-emerald-500/30 overflow-hidden transition-colors duration-300"
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
      
      <VerificationModal 
        v-if="showVerificationModal" 
        @close="showVerificationModal = false"
        :verification-status="verificationStatus"
        redirect-route="/Supplier/SupplierSettings"
        description-text="To access all client features like color previews and service requests, please complete your identity verification."
      />

      <SideBarSupplier 
        :verification-status="verificationStatus"
        :full-access="hasFullAccess"
        :accessibilities="userAccessibilities"
        @open-verification-modal="showVerificationModal = true"
        @logout-started="handleLogoutStart" 
        @logout-finished="handleLogoutFinish"
      />

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
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-emerald-500/20 to-teal-500/20 flex items-center justify-center mb-4">
                  <LogOut class="w-12 h-12 text-emerald-400" />
                </div>
                <div class="absolute -inset-4 bg-emerald-500/10 rounded-full animate-pulse" />
              </div>
              
              <h3 class="text-2xl font-bold text-white mb-2">Signing Off</h3>
              <p class="text-slate-400 mb-8">Securely closing your supplier portal...</p>
              
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
          <SidebarTrigger class="-ml-1 text-emerald-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Supplier Portal</span>
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
                :verification-status="verificationStatus"
              />
            </transition>
          </router-view>
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner' // <-- Added for notifications
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import SideBarSupplier from './sideBarSupplier.vue'
import VerificationModal from './VerificationModal.vue'
import axios from '@/utils/axios'
import { LogOut, Sun, Moon } from 'lucide-vue-next'
import echo from '@/utils/websocket' // <-- Added Websocket Import

const route = useRoute()
const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)
const verificationStatus = ref(null)
const showVerificationModal = ref(false)

// Theme: light is the default for the supplier portal
const theme = ref('light')
const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

// Keep the theme override class on <body> so teleported content
// (mobile sidebar, dialogs, toasts) is themed too.
watch(theme, (value) => {
  document.body.classList.toggle('supplier-light-mode', value === 'light')
  document.body.classList.toggle('supplier-dark-mode', value === 'dark')
}, { immediate: true })

// Role-based Access State variables
const hasFullAccess = ref(false)
const userAccessibilities = ref([])

// Logout States
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

// --- WEBSOCKET LISTENER SETUP ---
const setupWebSocket = () => {
  if (userData.value && userData.value.id) {
    echo.private(`user.${userData.value.id}.requirements`)
      .listen('.RequirementStatusUpdated', async (e) => {
        if (e.status === 'approved') {
          verificationStatus.value = 'approved';
          toast.success('Your business verification has been approved! Features are now unlocked.', { duration: 5000 });
          // Fetch updated sidebar accessibilities
          await fetchData();
        } else if (e.status === 'rejected') {
          verificationStatus.value = 'rejected';
          toast.error(`Your business verification was rejected. Reason: ${e.reason}`, { duration: 7000 });
          await fetchData();
        }
      });
  }
}

const fetchData = async () => {
  isLoading.value = true
  try {
    const userResponse = await axios.get('/auth/me')
    const isFirstLoad = !userData.value; // Track if this is initial load
    userData.value = userResponse.data.user
    
    // Fetch Sidebar Accessibilities specifically for this user's role
    const accessResponse = await axios.get('/supplier/sidebar-access')
    if (accessResponse.data.status === 'success') {
      hasFullAccess.value = accessResponse.data.full_access
      userAccessibilities.value = accessResponse.data.accessibilities
    }

    // Check role and fetch specific requirements
    if (userData.value.role === 'supplier') {
      const verificationResponse = await axios.get('/supplier/requirements')
      if (verificationResponse.data.status === 'success') {
        verificationStatus.value = verificationResponse.data.data?.status || 'none'
      }
    }
    else if (userData.value.role === 'personnel_officer') {
      verificationStatus.value = 'approved'
    }
    else if (userData.value.role === 'supplier_employee') {
      // Typically supplier employees bypass requirement locks because the parent supplier is already verified
      verificationStatus.value = 'approved' 
    }

    // Initialize Websocket after establishing user ID
    if (isFirstLoad) {
      setupWebSocket();
    }

  } catch (error) {
    console.error('Failed to fetch data:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchData()
})

onUnmounted(() => {
  if (progressInterval) clearInterval(progressInterval)

  document.body.classList.remove('supplier-light-mode', 'supplier-dark-mode')
  
  // Cleanup Websocket Connection
  if (userData.value && userData.value.id) {
    echo.leave(`user.${userData.value.id}.requirements`);
  }
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
  Light mode overrides. Scoped to body.supplier-light-mode so dark mode keeps
  the original styles. Light is the default for the supplier portal, so this
  converts the dark sidebar (and its teleported dialogs) to light. The main
  content is already light-themed, so it is left untouched.
-->
<style>
body.supplier-light-mode {
  background-color: #f8fafc;
  color-scheme: light;
}

body.supplier-dark-mode {
  color-scheme: dark;
}

body.supplier-light-mode .supplier-layout-root {
  background-color: #f8fafc !important;
  color: #0f172a !important;
}

/* ----- Dark sidebar surfaces become light ----- */
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-slate-950"],
body.supplier-light-mode [data-slot="sidebar"] [class^="bg-slate-950/"],
body.supplier-light-mode [data-slot="sidebar"] [class*=" bg-slate-950/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-slate-900"],
body.supplier-light-mode [data-slot="sidebar"] [class^="bg-slate-900/"],
body.supplier-light-mode [data-slot="sidebar"] [class*=" bg-slate-900/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-gray-950"],
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-gray-900"] {
  background-color: #ffffff !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class~="bg-slate-800"],
body.supplier-light-mode [data-slot="sidebar"] [class^="bg-slate-800/"],
body.supplier-light-mode [data-slot="sidebar"] [class*=" bg-slate-800/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-gray-800"] {
  background-color: #f1f5f9 !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class~="bg-slate-700"],
body.supplier-light-mode [data-slot="sidebar"] [class^="bg-slate-700/"],
body.supplier-light-mode [data-slot="sidebar"] [class*=" bg-slate-700/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-gray-700"],
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-slate-500"] {
  background-color: #e2e8f0 !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class~="bg-slate-600"],
body.supplier-light-mode [data-slot="sidebar"] [class~="bg-gray-600"] {
  background-color: #cbd5e1 !important;
}

/* ----- Light sidebar text becomes dark ----- */
body.supplier-light-mode [data-slot="sidebar"] [class~="text-white"],
body.supplier-light-mode [data-slot="sidebar"] [class^="text-white/"],
body.supplier-light-mode [data-slot="sidebar"] [class*=" text-white/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-slate-50"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-slate-100"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-slate-200"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-gray-50"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-gray-100"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-gray-200"] {
  color: #0f172a !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class~="text-slate-300"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-gray-300"] {
  color: #1e293b !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class~="text-slate-400"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-slate-500"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-gray-400"],
body.supplier-light-mode [data-slot="sidebar"] [class~="text-gray-500"] {
  color: #64748b !important;
}

/* Keep white icons readable on the coloured avatar gradient */
body.supplier-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"] [class~="text-white"] {
  color: #ffffff !important;
}

/* Active sidebar item: white text does not work on the light gradient */
body.supplier-light-mode [data-slot="sidebar"] [class*="from-emerald-500/"] {
  color: #065f46 !important;
}

/* ----- Borders and dividers ----- */
body.supplier-light-mode [data-slot="sidebar"] [class~="border-slate-950"],
body.supplier-light-mode [data-slot="sidebar"] [class^="border-slate-950/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="border-slate-900"],
body.supplier-light-mode [data-slot="sidebar"] [class~="border-slate-800"],
body.supplier-light-mode [data-slot="sidebar"] [class^="border-slate-800/"],
body.supplier-light-mode [data-slot="sidebar"] [class*=" border-slate-800/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="border-slate-700"],
body.supplier-light-mode [data-slot="sidebar"] [class^="border-slate-700/"],
body.supplier-light-mode [data-slot="sidebar"] [class*=" border-slate-700/"],
body.supplier-light-mode [data-slot="sidebar"] [class~="border-gray-800"],
body.supplier-light-mode [data-slot="sidebar"] [class~="border-gray-700"] {
  border-color: #e2e8f0 !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class~="border-slate-600"],
body.supplier-light-mode [data-slot="sidebar"] [class~="border-gray-600"] {
  border-color: #cbd5e1 !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class~="ring-offset-slate-900"] {
  --tw-ring-offset-color: #ffffff !important;
}

/* ----- Sidebar hover states ----- */
body.supplier-light-mode [data-slot="sidebar"] [class*="hover:bg-slate-900"]:hover,
body.supplier-light-mode [data-slot="sidebar"] [class*="hover:bg-gray-900"]:hover {
  background-color: #f1f5f9 !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class*="hover:bg-slate-800"]:hover,
body.supplier-light-mode [data-slot="sidebar"] [class*="hover:bg-slate-700"]:hover,
body.supplier-light-mode [data-slot="sidebar"] [class*="hover:bg-gray-800"]:hover,
body.supplier-light-mode [data-slot="sidebar"] [class*="hover:bg-gray-700"]:hover {
  background-color: #e2e8f0 !important;
}

body.supplier-light-mode [data-slot="sidebar"] [class*="hover:text-white"]:hover {
  color: #0f172a !important;
}

/* ----- Dark sidebar gradients become light gradients ----- */
body.supplier-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-slate-9"],
body.supplier-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-slate-8"],
body.supplier-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-gray-9"],
body.supplier-light-mode [data-slot="sidebar"] [class*="bg-gradient-to"][class*="from-gray-8"] {
  background-image: linear-gradient(135deg, #ffffff 0%, #f1f5f9 55%, #e2e8f0 100%) !important;
}

/* ----- Teleported sidebar dialogs ----- */
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] {
  background-color: #ffffff !important;
  color: #0f172a !important;
  border-color: #e2e8f0 !important;
}

body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-white"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-slate-200"] {
  color: #0f172a !important;
}

body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-slate-300"] {
  color: #334155 !important;
}

body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-slate-400"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="text-slate-500"] {
  color: #64748b !important;
}

body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="border-slate-700"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class^="border-slate-700/"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class*=" border-slate-700/"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="border-slate-800"] {
  border-color: #e2e8f0 !important;
}

body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="bg-slate-900"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class^="bg-slate-900/"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class*=" bg-slate-900/"] {
  background-color: #f1f5f9 !important;
}

body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="bg-slate-800"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="bg-slate-800/50"],
body.supplier-light-mode [data-slot="dialog-content"][class*="bg-slate-900"] [class~="bg-slate-700"] {
  background-color: #f1f5f9 !important;
}
</style>

<!--
  Dark mode overrides. Scoped to body.supplier-dark-mode so light mode (the
  default) keeps every existing style exactly as it was. The supplier pages
  are light-themed, so this converts their light surfaces, text and borders to
  dark equivalents while keeping accent colours intact.
-->
<style>
body.supplier-dark-mode {
  background-color: #0f172a;
}

body.supplier-dark-mode .supplier-layout-root {
  background-color: #0f172a !important;
  color: #f1f5f9 !important;
}

body.supplier-dark-mode [data-slot="dialog-content"],
body.supplier-dark-mode [data-slot="alert-dialog-content"],
body.supplier-dark-mode [data-slot="sheet-content"],
body.supplier-dark-mode [data-slot="select-content"],
body.supplier-dark-mode [data-slot="dropdown-menu-content"],
body.supplier-dark-mode [data-slot="popover-content"] {
  background-color: #0f172a !important;
  color: #e2e8f0 !important;
  border-color: #334155 !important;
}

/* ----- Light surfaces become dark surfaces ----- */
body.supplier-dark-mode [class~="bg-white"],
body.supplier-dark-mode [class^="bg-white/"],
body.supplier-dark-mode [class*=" bg-white/"] {
  background-color: #0f172a !important;
}

body.supplier-dark-mode [class~="bg-slate-50"],
body.supplier-dark-mode [class^="bg-slate-50/"],
body.supplier-dark-mode [class*=" bg-slate-50/"],
body.supplier-dark-mode [class~="bg-gray-50"],
body.supplier-dark-mode [class^="bg-gray-50/"],
body.supplier-dark-mode [class*=" bg-gray-50/"] {
  background-color: #1e293b !important;
}

body.supplier-dark-mode [class~="bg-slate-100"],
body.supplier-dark-mode [class^="bg-slate-100/"],
body.supplier-dark-mode [class*=" bg-slate-100/"],
body.supplier-dark-mode [class~="bg-gray-100"],
body.supplier-dark-mode [class^="bg-gray-100/"],
body.supplier-dark-mode [class*=" bg-gray-100/"] {
  background-color: #1e293b !important;
}

body.supplier-dark-mode [class~="bg-slate-200"],
body.supplier-dark-mode [class^="bg-slate-200/"],
body.supplier-dark-mode [class*=" bg-slate-200/"],
body.supplier-dark-mode [class~="bg-gray-200"],
body.supplier-dark-mode [class^="bg-gray-200/"],
body.supplier-dark-mode [class*=" bg-gray-200/"] {
  background-color: #334155 !important;
}

body.supplier-dark-mode [class~="bg-gray-300"],
body.supplier-dark-mode [class~="bg-slate-300"] {
  background-color: #475569 !important;
}

/* ----- Dark text becomes light text ----- */
body.supplier-dark-mode [class~="text-gray-900"],
body.supplier-dark-mode [class~="text-slate-900"],
body.supplier-dark-mode [class~="text-gray-800"],
body.supplier-dark-mode [class~="text-slate-800"] {
  color: #f1f5f9 !important;
}

body.supplier-dark-mode [class~="text-gray-700"],
body.supplier-dark-mode [class~="text-slate-700"] {
  color: #e2e8f0 !important;
}

body.supplier-dark-mode [class~="text-gray-600"],
body.supplier-dark-mode [class~="text-slate-600"] {
  color: #cbd5e1 !important;
}

body.supplier-dark-mode [class~="text-gray-500"],
body.supplier-dark-mode [class~="text-slate-500"],
body.supplier-dark-mode [class~="text-gray-400"],
body.supplier-dark-mode [class~="text-slate-400"] {
  color: #94a3b8 !important;
}

body.supplier-dark-mode [class~="text-gray-300"],
body.supplier-dark-mode [class~="text-slate-300"] {
  color: #cbd5e1 !important;
}

/* ----- Light borders and dividers become dark ----- */
body.supplier-dark-mode [class~="border-slate-100"],
body.supplier-dark-mode [class~="border-gray-100"],
body.supplier-dark-mode [class~="border-slate-50"],
body.supplier-dark-mode [class~="divide-slate-100"],
body.supplier-dark-mode [class~="divide-gray-100"] {
  border-color: #1e293b !important;
}

body.supplier-dark-mode [class~="border-slate-200"],
body.supplier-dark-mode [class~="border-gray-200"],
body.supplier-dark-mode [class~="divide-gray-200"] {
  border-color: #334155 !important;
}

body.supplier-dark-mode [class~="border-slate-300"],
body.supplier-dark-mode [class~="border-gray-300"] {
  border-color: #475569 !important;
}

/* ----- Hover states ----- */
body.supplier-dark-mode [class*="hover:bg-slate-50"]:hover,
body.supplier-dark-mode [class*="hover:bg-gray-50"]:hover {
  background-color: #1e293b !important;
}

body.supplier-dark-mode [class*="hover:bg-slate-100"]:hover,
body.supplier-dark-mode [class*="hover:bg-gray-100"]:hover {
  background-color: #334155 !important;
}

body.supplier-dark-mode [class*="hover:bg-slate-200"]:hover,
body.supplier-dark-mode [class*="hover:bg-gray-200"]:hover {
  background-color: #475569 !important;
}

body.supplier-dark-mode [class*="hover:bg-gray-300"]:hover {
  background-color: #475569 !important;
}

body.supplier-dark-mode [class*="hover:text-slate-900"]:hover,
body.supplier-dark-mode [class*="hover:text-gray-900"]:hover,
body.supplier-dark-mode [class*="hover:text-slate-800"]:hover,
body.supplier-dark-mode [class*="hover:text-slate-700"]:hover,
body.supplier-dark-mode [class*="hover:text-gray-700"]:hover {
  color: #f1f5f9 !important;
}

body.supplier-dark-mode [class*="hover:text-gray-600"]:hover,
body.supplier-dark-mode [class*="hover:text-slate-600"]:hover {
  color: #cbd5e1 !important;
}

body.supplier-dark-mode input::placeholder,
body.supplier-dark-mode textarea::placeholder {
  color: #64748b;
}
</style>