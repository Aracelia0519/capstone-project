<template>
  <div class="min-h-screen bg-white font-sans text-slate-900 selection:bg-blue-500/30 relative">
    <Toaster position="top-right" />

    <transition name="fade">
      <div 
        v-if="isLoggingOut" 
        class="fixed inset-0 z-[100] bg-slate-900/95 backdrop-blur-sm flex flex-col items-center justify-center"
      >
        <div class="w-full max-w-md p-8 text-center">
          <div class="relative mb-8">
            <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-blue-500/20 to-indigo-500/20 flex items-center justify-center mb-4">
              <LogOut class="w-12 h-12 text-blue-500" />
            </div>
            <div class="absolute -inset-4 bg-blue-500/10 rounded-full animate-pulse" />
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

    <TopBar 
      :user="userData"
      @logout-started="handleLogoutStart" 
      @logout-finished="handleLogoutFinish" 
    />

    <main class="flex-1 w-full bg-white relative min-h-[calc(100vh-64px)]">
      <div class="container mx-auto">
        <Suspense>
          <template #default>
            <router-view v-slot="{ Component }">
              <transition name="page-slide" mode="out-in">
                <component 
                  :is="Component" 
                  v-if="userData"
                  :user="userData"
                />
              </transition>
            </router-view>
          </template>
          <template #fallback>
            <div class="flex items-center justify-center min-h-[50vh]">
              <Loader2 class="w-12 h-12 text-blue-600 animate-spin" />
            </div>
          </template>
        </Suspense>
      </div>
    </main>

    <footer class="bg-slate-900 text-white border-t border-slate-800">
      <div class="container mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
          <div class="space-y-4">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center">
                <Paintbrush class="w-6 h-6 text-white" />
              </div>
              <h2 class="text-xl font-bold">CaviteGo Paint</h2>
            </div>
            <p class="text-slate-400 text-sm leading-relaxed">
              Premium destination for quality paints and professional painting services in Cavite.
            </p>
          </div>
          
          <div v-for="col in footerLinks" :key="col.title">
            <h3 class="font-bold mb-4 text-slate-200">{{ col.title }}</h3>
            <ul class="space-y-2">
              <li v-for="link in col.links" :key="link.name">
                <a href="#" class="text-slate-400 hover:text-blue-400 text-sm transition-colors">{{ link.name }}</a>
              </li>
            </ul>
          </div>
        </div>
        <div class="mt-12 pt-8 border-t border-slate-800 flex flex-col md:row justify-between items-center gap-4">
          <p class="text-slate-500 text-xs">© 2026 CaviteGo Paint. All rights reserved.</p>
          <div class="flex gap-6">
            <a href="#" class="text-slate-500 hover:text-white text-xs">Privacy</a>
            <a href="#" class="text-slate-500 hover:text-white text-xs">Terms</a>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { Toaster } from '@/components/ui/sonner'
import { Progress } from '@/components/ui/progress'
import TopBar from '../layouts/topBarECommerceClient.vue'
import { Loader2, Paintbrush, LogOut } from 'lucide-vue-next'
import { getCurrentUser } from '@/utils/auth'

const router = useRouter()
const userData = ref(null)

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

const initializeUserData = () => {
  const stored = localStorage.getItem('user_data')
  if (stored) userData.value = JSON.parse(stored)
}

onMounted(async () => {
  initializeUserData()
  if (!userData.value) {
    try {
      userData.value = await getCurrentUser()
      if (!userData.value) throw new Error("Not authenticated")
      localStorage.setItem('user_data', JSON.stringify(userData.value))
    } catch {
      router.push('/Landing/logIn')
      return 
    }
  }
})

onUnmounted(() => {
  if (progressInterval) clearInterval(progressInterval)
})

const footerLinks = [
  {
    title: 'Quick Links',
    links: [{ name: 'Shop' }, { name: 'Services' }, { name: 'Orders' }, { name: 'Profile' }]
  },
  {
    title: 'Our Services',
    links: [{ name: 'Interior' }, { name: 'Exterior' }, { name: 'Consultation' }, { name: 'Maintenance' }]
  },
  {
    title: 'Contact',
    links: [{ name: 'Cavite, Philippines' }, { name: 'info@cavitegopaint.com' }, { name: '+63 912 345 6789' }]
  }
]
</script>

<style scoped>
.page-slide-enter-active, .page-slide-leave-active { transition: all 0.4s ease; }
.page-slide-enter-from { opacity: 0; transform: translateY(10px); }
.page-slide-leave-to { opacity: 0; transform: translateY(-10px); }

.fade-enter-active, .fade-leave-active { transition: all 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>