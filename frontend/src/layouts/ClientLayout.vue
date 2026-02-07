<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-900 font-sans text-slate-100 selection:bg-sky-500/30 overflow-hidden">
      <Toaster position="top-right" />

      <SideBarClient @logout-started="handleLogoutStart" @logout-finished="handleLogoutFinish" />

      <SidebarInset class="main-content-area bg-slate-900 border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto">
        <!-- Logout Overlay with Progress -->
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

        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-900 to-sky-900/10 opacity-50 pointer-events-none" />
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Toaster } from '@/components/ui/sonner'
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import SideBarClient from './sideBarClient.vue'
import axios from '@/utils/axios'
import { getCurrentUser } from '@/utils/auth'
import { LogOut } from 'lucide-vue-next'

const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)
const isLoggingOut = ref(false)
const logoutProgress = ref(0)
let progressInterval = null

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
  // Complete the progress
  logoutProgress.value = 100
  setTimeout(() => {
    if (progressInterval) clearInterval(progressInterval)
    isLoggingOut.value = false
    logoutProgress.value = 0
  }, 500)
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
.page-slide-enter-active, .page-slide-leave-active { transition: all 0.4s ease; }
.page-slide-enter-from { opacity: 0; transform: translateX(20px); filter: blur(4px); }
.page-slide-leave-to { opacity: 0; transform: translateX(-20px); filter: blur(4px); }
@keyframes pulse-slow { 0%, 100% { opacity: 0.3; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.05); } }
.animate-pulse-slow { animation: pulse-slow 8s ease-in-out infinite; }
</style>