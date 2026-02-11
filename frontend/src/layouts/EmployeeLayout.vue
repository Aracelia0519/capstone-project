<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-50 font-sans text-slate-900 selection:bg-indigo-500/30 overflow-hidden">
      <SideBarEmployee 
        :user="userData"
        @logout-started="handleLogoutStart" 
        @logout-finished="handleLogoutFinish" 
        @toggle="toggleSidebarMobile" 
      />

      <SidebarInset class="main-content-area bg-white border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto">
        <transition name="fade">
          <div 
            v-if="isLoggingOut" 
            class="fixed inset-0 z-[100] bg-indigo-950/95 backdrop-blur-sm flex flex-col items-center justify-center"
          >
            <div class="w-full max-w-md p-8 text-center">
              <div class="relative mb-8">
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-indigo-500/20 to-purple-500/20 flex items-center justify-center mb-4">
                  <LogOut class="w-12 h-12 text-indigo-400" />
                </div>
                <div class="absolute -inset-4 bg-indigo-500/10 rounded-full animate-pulse" />
              </div>
              
              <h3 class="text-2xl font-bold text-white mb-2">Signing Out</h3>
              <p class="text-indigo-200 mb-8">Please wait while we end your session...</p>
              
              <div class="space-y-4">
                <Progress 
                  :model-value="logoutProgress" 
                  class="h-2 bg-indigo-900"
                />
                <p class="text-sm text-indigo-300">{{ logoutProgress }}%</p>
              </div>
            </div>
          </div>
        </transition>

        <header class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200">
          <SidebarTrigger class="-ml-1 text-indigo-600 hover:bg-slate-100" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-500">
            {{ userRoleDisplay }} Portal
          </span>
        </header>

        <main class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1">
          <Suspense>
            <template #default>
              <router-view v-slot="{ Component }">
                <transition name="page-fade" mode="out-in">
                  <component 
                    :is="Component" 
                    v-if="userData" 
                    :user="userData" 
                    :dashboard-data="dashboardData" 
                  />
                </transition>
              </router-view>
            </template>
            <template #fallback>
              <div class="flex items-center justify-center min-h-[50vh]">
                <div class="w-12 h-12 border-3 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin" />
              </div>
            </template>
          </Suspense>
        </main>

        <div class="absolute inset-0 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:20px_20px] opacity-30 pointer-events-none" />
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import SideBarEmployee from './SideBarEmployee.vue'
import { LogOut } from 'lucide-vue-next'
import axios from '@/utils/axios'
import { getCurrentUser } from '@/utils/auth'

const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)
const isLoggingOut = ref(false)
const logoutProgress = ref(0)
let progressInterval = null

// Display logic for the mobile header
const userRoleDisplay = computed(() => {
  if (!userData.value) return 'Employee'
  const role = userData.value.role
  if (role === 'hr_manager') return 'HR Manager'
  if (role === 'finance_manager') return 'Finance'
  if (role === 'operational_distributor') return 'Operations'
  return 'Employee'
})

const handleLogoutStart = () => {
  isLoggingOut.value = true
  logoutProgress.value = 0
  
  // Simulate progress animation
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

const sidebarMobileVisible = ref(false)
const toggleSidebarMobile = () => {
  sidebarMobileVisible.value = !sidebarMobileVisible.value
}

// -- Backend Integration Logic --

const initializeUserData = () => {
  const stored = localStorage.getItem('user_data')
  if (stored) userData.value = JSON.parse(stored)
}

const fetchDashboardData = async () => {
  if (!userData.value) return
  const now = Date.now()
  const lastFetch = localStorage.getItem(`last_dashboard_fetch_${userData.value.role}`)
  
  // Cache for 2 minutes
  if (lastFetch && dashboardData.value && (now - parseInt(lastFetch)) < 120000) return

  isLoading.value = true
  
}

onMounted(async () => {
  initializeUserData()
  if (!userData.value) {
    try {
      userData.value = await getCurrentUser()
      localStorage.setItem('user_data', JSON.stringify(userData.value))
    } catch {
      router.push('/Landing/logIn')
    }
  }
  fetchDashboardData()
})

watch(() => router.currentRoute.value.path, (path, oldPath) => {
  if (path.includes('dashboard') && !oldPath.includes('dashboard')) {
    fetchDashboardData()
  }
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.page-fade-enter-active, .page-fade-leave-active { 
  transition: opacity 0.3s ease, transform 0.3s ease; 
}
.page-fade-enter-from { 
  opacity: 0; 
  transform: translateY(8px); 
}
.page-fade-leave-to { 
  opacity: 0; 
  transform: translateY(-8px); 
}
</style>