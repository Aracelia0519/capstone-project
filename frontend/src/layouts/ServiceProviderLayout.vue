<template>
  <SidebarProvider>
    <div :class="[
      'flex min-h-screen w-full font-sans overflow-hidden transition-colors duration-300',
      isDarkMode ? 'bg-slate-900 text-slate-100 selection:bg-purple-500/30' : 'bg-slate-50 text-slate-900 selection:bg-purple-200'
    ]">
      <Toaster position="top-right" />
      
      <button 
        @click="toggleTheme"
        class="fixed bottom-6 right-6 z-[100] p-3 rounded-full shadow-xl transition-all duration-300 hover:scale-110 flex items-center justify-center border"
        :class="isDarkMode ? 'bg-slate-800 text-yellow-400 border-slate-700 hover:bg-slate-700' : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-100'"
        title="Toggle Theme"
      >
        <Sun v-if="!isDarkMode" class="w-6 h-6" />
        <Moon v-else class="w-6 h-6" />
      </button>

      <VerificationModal 
        v-if="showVerificationModal" 
        @close="showVerificationModal = false"
        :verification-status="verificationStatus"
        redirect-route="/serviceProvider/ProfileSettingsSP" 
        description-text="To access all service provider features like accepting service requests and managing your portfolio, please complete your professional verification."
      />

      <sideBarServiceProvider 
        :verification-status="verificationStatus"
        @open-verification-modal="showVerificationModal = true"
        @logout-started="handleLogoutStart" 
        @logout-finished="handleLogoutFinish" 
      />

      <SidebarInset :class="[
        'main-content-area border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto',
        isDarkMode ? 'bg-slate-900' : 'bg-slate-50'
      ]">
        
        <transition name="fade">
          <div 
            v-if="isLoggingOut" 
            :class="[
              'fixed inset-0 z-[100] backdrop-blur-sm flex flex-col items-center justify-center transition-colors duration-300',
              isDarkMode ? 'bg-slate-900/95' : 'bg-white/95'
            ]"
          >
            <div class="w-full max-w-md p-8 text-center">
              <div class="relative mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-purple-500/20 to-pink-500/20 flex items-center justify-center mb-4">
                  <LogOut class="w-12 h-12 text-purple-400" />
                </div>
                <div class="absolute -inset-4 bg-purple-500/10 rounded-full animate-pulse" />
              </div>
              
              <h3 :class="['text-2xl font-bold mb-2', isDarkMode ? 'text-white' : 'text-slate-900']">Logging Out</h3>
              <p :class="['mb-8', isDarkMode ? 'text-slate-400' : 'text-slate-500']">Securely terminating your session...</p>
              
              <div class="space-y-4">
                <Progress 
                  :model-value="logoutProgress" 
                  :class="['h-2', isDarkMode ? 'bg-slate-800' : 'bg-slate-200']"
                />
                <p :class="['text-sm', isDarkMode ? 'text-slate-400' : 'text-slate-500']">{{ logoutProgress }}%</p>
              </div>
            </div>
          </div>
        </transition>

        <header :class="[
          'flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 backdrop-blur-md transition-colors duration-300 border-b',
          isDarkMode ? 'bg-slate-900/80 border-slate-800/50' : 'bg-white/80 border-slate-200'
        ]">
          <SidebarTrigger :class="['-ml-1 text-purple-500', isDarkMode ? 'hover:bg-slate-800' : 'hover:bg-slate-100']" />
          <span :class="['text-xs font-bold uppercase tracking-widest', isDarkMode ? 'text-slate-400' : 'text-slate-600']">Service Provider Portal</span>
        </header>

        <main class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1">
          <div :class="['theme-wrapper h-full', isDarkMode ? 'theme-dark' : 'theme-light']">
            <router-view v-slot="{ Component }">
              <transition name="page-slide" mode="out-in">
                <component 
                  :is="Component" 
                  v-if="userData" 
                  :user="userData" 
                  :dashboard-data="dashboardData" 
                  :verification-status="verificationStatus"
                  :is-dark-mode="isDarkMode"
                />
              </transition>
            </router-view>
          </div>
        </main>

        <div :class="[
          'absolute inset-0 opacity-50 pointer-events-none transition-colors duration-500',
          isDarkMode ? 'bg-gradient-to-br from-slate-900 via-slate-900 to-purple-900/10' : 'bg-gradient-to-br from-slate-50 via-slate-100 to-purple-200/30'
        ]" />
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Toaster } from '@/components/ui/sonner'
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import sideBarServiceProvider from './sideBarServiceProvider.vue'
import VerificationModal from './VerificationModal.vue'
import axios from '@/utils/axios'
import { LogOut, Sun, Moon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)

// Theme States
const isDarkMode = ref(true) // Defaults to true

const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value
  
  if (isDarkMode.value) {
    document.documentElement.classList.add('dark')
    localStorage.setItem('theme', 'dark')
  } else {
    document.documentElement.classList.remove('dark')
    localStorage.setItem('theme', 'light')
  }
}

// Verification States
const verificationStatus = ref(null)
const showVerificationModal = ref(false)

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

// Watch for verification required query parameters
watch(() => route.query, (newQuery) => {
  if (newQuery.verification_required === 'true') {
    verificationStatus.value = newQuery.status || 'none'
    showVerificationModal.value = true
  }
}, { immediate: true })

const fetchData = async () => {
  isLoading.value = true
  try {
    const userResponse = await axios.get('/auth/me')
    userData.value = userResponse.data.user
    
    // Fetch verification status for Service Provider
    if (userData.value.role === 'service_provider') {
      const verificationResponse = await axios.get('/service-provider/requirements') 
      if (verificationResponse.data.status === 'success') {
        verificationStatus.value = verificationResponse.data.data?.status || 'none'
      }
    }
    
    const roleEndpoints = {
      service_provider: '/dashboard/service-provider',
      admin: '/dashboard/admin'
    }
    
    if (roleEndpoints[userData.value.role]) {
      const res = await axios.get(roleEndpoints[userData.value.role])
      dashboardData.value = res.data.data
    }
  } catch (error) {
    console.error('Failed to fetch data:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  // Initialize Theme on Mount
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme) {
    isDarkMode.value = savedTheme === 'dark'
  } else {
    isDarkMode.value = true // Default state
  }

  if (isDarkMode.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }

  fetchData()
})

onUnmounted(() => {
  if (progressInterval) clearInterval(progressInterval)
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.page-slide-enter-active, .page-slide-leave-active { transition: all 0.4s ease; }
.page-slide-enter-from { opacity: 0; transform: translateX(20px); filter: blur(4px); }
.page-slide-leave-to { opacity: 0; transform: translateX(-20px); filter: blur(4px); }

/* ==========================================================================
   GLOBAL DEEP CSS OVERRIDES FOR MAIN CONTENT
   ========================================================================== */

/* 1. DARK MODE PAGE BACKGROUNDS */
.theme-dark :deep(.bg-gray-50\/50),
.theme-dark :deep(.bg-gray-50),
.theme-dark :deep(.bg-gray-100),
.theme-dark :deep(.bg-slate-50),
.theme-dark :deep(.bg-slate-100) {
  background-color: #0f172a !important; /* slate-900 */
  border-color: #1e293b !important;
}

/* 2. GLOBAL DARK MODE TEXT (OUTSIDE CARDS) */
.theme-dark :deep(h1), 
.theme-dark :deep(h2), 
.theme-dark :deep(h3), 
.theme-dark :deep(h4), 
.theme-dark :deep(h5), 
.theme-dark :deep(h6),
.theme-dark :deep(p), 
.theme-dark :deep(label),
.theme-dark :deep(strong),
.theme-dark :deep(span:not(.text-white):not(.text-blue-500):not(.text-green-500):not(.text-red-500):not(.text-amber-500):not(.text-blue-600)) {
  color: #f8fafc !important; /* slate-50 */
}

/* 3. PROTECT CARDS (REVERT TO LIGHT MODE) */
/* This prevents cards (bg-white/bg-card) from becoming dark */
.theme-dark :deep(.bg-white),
.theme-dark :deep(.bg-card) {
  background-color: #ffffff !important;
  border-color: #e5e7eb !important;
}

/* 4. REVERT TEXT INSIDE CARDS SO IT REMAINS READABLE ON WHITE BACKGROUND */
/* Because CSS specificity combines classes, this will override the global text rule above */
.theme-dark :deep(.bg-white h1),
.theme-dark :deep(.bg-white h2),
.theme-dark :deep(.bg-white h3),
.theme-dark :deep(.bg-white h4),
.theme-dark :deep(.bg-white h5),
.theme-dark :deep(.bg-white h6),
.theme-dark :deep(.bg-white p),
.theme-dark :deep(.bg-white label),
.theme-dark :deep(.bg-white strong),
.theme-dark :deep(.bg-card h1),
.theme-dark :deep(.bg-card h2),
.theme-dark :deep(.bg-card p),
.theme-dark :deep(.bg-card label),
.theme-dark :deep(.bg-card strong),
.theme-dark :deep(.bg-white span:not(.text-white):not(.text-blue-500):not(.text-blue-600):not(.text-green-500):not(.text-red-500):not(.text-amber-500)),
.theme-dark :deep(.bg-card span:not(.text-white):not(.text-blue-500):not(.text-blue-600):not(.text-green-500):not(.text-red-500):not(.text-amber-500)) {
  color: #0f172a !important; /* slate-900 (Dark text) */
}

.theme-dark :deep(.bg-white .text-gray-400),
.theme-dark :deep(.bg-white .text-gray-500),
.theme-dark :deep(.bg-white .text-gray-600),
.theme-dark :deep(.bg-card .text-muted-foreground) {
  color: #64748b !important; /* slate-500 (Muted dark text) */
}

/* 5. FORMS & INPUTS OUTSIDE CARDS */
.theme-dark :deep(input),
.theme-dark :deep(textarea),
.theme-dark :deep(select) {
  background-color: #1e293b !important;
  color: #f8fafc !important;
  border-color: #334155 !important;
}

/* 6. FORMS & INPUTS INSIDE CARDS (REVERT) */
.theme-dark :deep(.bg-white input),
.theme-dark :deep(.bg-white textarea),
.theme-dark :deep(.bg-white select),
.theme-dark :deep(.bg-card input),
.theme-dark :deep(.bg-card textarea),
.theme-dark :deep(.bg-card select) {
  background-color: #f8fafc !important;
  color: #0f172a !important;
  border-color: #cbd5e1 !important;
}

/* 7. TABLES INSIDE CARDS (REVERT) */
.theme-dark :deep(.bg-white table th),
.theme-dark :deep(.bg-card table th) {
  background-color: #f8fafc !important;
  color: #0f172a !important;
  border-color: #e2e8f0 !important;
}
.theme-dark :deep(.bg-white table td),
.theme-dark :deep(.bg-card table td) {
  background-color: #ffffff !important;
  color: #334155 !important;
  border-color: #e2e8f0 !important;
}

/* ========================================================================== */

/* LIGHT MODE OVERRIDES (In case dark mode components exist natively) */
.theme-light :deep(.bg-slate-900),
.theme-light :deep(.bg-gray-900) {
  background-color: #ffffff !important;
  color: #0f172a !important;
}

.theme-light :deep(.text-white:not(.bg-blue-600):not(.bg-green-600):not(.bg-red-600):not(.bg-amber-600)) {
  color: #0f172a !important;
}
</style>