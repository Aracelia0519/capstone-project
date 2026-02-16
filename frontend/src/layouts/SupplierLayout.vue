<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-900 font-sans text-slate-100 selection:bg-emerald-500/30 overflow-hidden">
      <Toaster position="top-right" />
      
      <VerificationModal 
        v-if="showVerificationModal" 
        @close="showVerificationModal = false"
        :verification-status="verificationStatus"
      />

      <SideBarSupplier 
        :verification-status="verificationStatus"
        @open-verification-modal="showVerificationModal = true"
        @logout-started="handleLogoutStart" 
        @logout-finished="handleLogoutFinish"
      />

      <SidebarInset class="main-content-area bg-white border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto">
        
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

        <header class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/50">
          <SidebarTrigger class="-ml-1 text-emerald-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Supplier Portal</span>
        </header>

        <main class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1 text-slate-900">
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
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import SideBarSupplier from './sideBarSupplier.vue'
import VerificationModal from './VerificationModal.vue' // Ensure this file exists
import axios from '@/utils/axios'
import { LogOut } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)
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
    
    // Check role and fetch specific requirements
    if (userData.value.role === 'supplier') {
      const verificationResponse = await axios.get('/supplier/requirements')
      if (verificationResponse.data.status === 'success') {
        // According to your SupplierRequirementController, it returns 'data' which contains 'status'
        verificationStatus.value = verificationResponse.data.data?.status || 'none'
      }
    }
    
    // Fetch Dashboard Data if applicable
    // Assuming you might have a supplier dashboard endpoint similar to distributor
    /* const roleEndpoints = { supplier: '/dashboard/supplier' }
    if (roleEndpoints[userData.value.role]) {
      const res = await axios.get(roleEndpoints[userData.value.role])
      dashboardData.value = res.data.data
    } 
    */
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
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.page-slide-enter-active, .page-slide-leave-active { transition: all 0.4s ease; }
.page-slide-enter-from { opacity: 0; transform: translateX(20px); }
.page-slide-leave-to { opacity: 0; transform: translateX(-20px); }
</style>