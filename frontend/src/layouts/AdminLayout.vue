<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-900 font-sans text-slate-100 selection:bg-blue-500/30 overflow-hidden">
      <Toaster position="top-right" />

      <sideBarAdmin @logout-started="handleLogoutStart" @logout-finished="handleLogoutFinish" />

      <SidebarInset class="main-content-area bg-white border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto">
        
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

        <header class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/50">
          <SidebarTrigger class="-ml-1 text-blue-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Admin Panel</span>
        </header>

        <main class="relative z-10 w-full p-4 md:p-8 pt-20 md:pt-8 flex-1 text-slate-900">
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Toaster } from '@/components/ui/sonner'
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
import { Progress } from '@/components/ui/progress'
import sideBarAdmin from '@/layouts/sideBarAdmin.vue'
import axios from '@/utils/axios'
import { LogOut } from 'lucide-vue-next'

const router = useRouter()
const userData = ref(null)
const dashboardData = ref(null)
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
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.page-slide-enter-active, .page-slide-leave-active { transition: all 0.4s ease; }
.page-slide-enter-from { opacity: 0; transform: translateX(20px); }
.page-slide-leave-to { opacity: 0; transform: translateX(-20px); }
</style>