<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-900 font-sans text-slate-100 selection:bg-purple-500/30 overflow-hidden">
      <Toaster position="top-right" richColors />
      
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

      <SidebarInset class="main-content-area bg-slate-900 border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto">
        
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

        <header class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/50">
          <SidebarTrigger class="-ml-1 text-purple-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Service Provider Portal</span>
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

        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-900 to-purple-900/10 opacity-50 pointer-events-none" />
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
import { LogOut } from 'lucide-vue-next'
import { getCurrentUser } from '@/utils/auth' // Auth exactly like ClientLayout

const route = useRoute()
const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)

// Verification States
const verificationStatus = ref(null)
const showVerificationModal = ref(false)

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