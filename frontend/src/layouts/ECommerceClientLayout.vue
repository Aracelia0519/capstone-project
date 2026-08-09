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
      :is-terminated="isTerminated"
      @logout-started="handleLogoutStart" 
      @logout-finished="handleLogoutFinish" 
    />

    <main class="flex-1 w-full bg-white relative min-h-[calc(100vh-64px)]">
      <div class="container mx-auto">
        <Suspense>
          <template #default>
            <transition name="page-slide" mode="out-in">
              <!-- Loading State -->
              <div v-if="isCheckingAuth" class="flex items-center justify-center min-h-[50vh]">
                <Loader2 class="w-12 h-12 text-blue-600 animate-spin" />
              </div>

              <!-- Terminated Restriction Banner - Blocks all router views -->
              <div v-else-if="isTerminated" class="flex flex-col items-center justify-center min-h-[60vh] text-center p-8 bg-red-50/50 rounded-3xl mt-8 border border-red-100 shadow-sm mx-4">
                <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
                  <AlertTriangle class="w-12 h-12 text-red-500 animate-pulse" />
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-4 tracking-tight">Account Restricted</h2>
                <p class="text-slate-600 max-w-lg mx-auto text-lg leading-relaxed">
                  Your account access has been temporarily suspended or permanently terminated due to a policy violation. You are currently restricted from making purchases or browsing the store.
                </p>
                <div class="mt-8 flex gap-4">
                  <Button as-child variant="outline" class="h-12 px-6 rounded-xl border-slate-300 font-bold hover:bg-slate-50 transition-all">
                     <router-link to="/Clients/notificationsC">Check Notifications Dashboard</router-link>
                  </Button>
                </div>
              </div>

              <!-- Main Application View -->
              <router-view v-else v-slot="{ Component }">
                <component 
                  :is="Component" 
                  :user="userData"
                />
              </router-view>
            </transition>
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
import api from '@/utils/axios'
import echo from '@/utils/websocket' 
import { toast } from 'vue-sonner'
import { Toaster } from '@/components/ui/sonner'
import { Progress } from '@/components/ui/progress'
import { Button } from '@/components/ui/button'
import TopBar from '../layouts/topBarECommerceClient.vue'
import { Loader2, Paintbrush, LogOut, AlertTriangle } from 'lucide-vue-next'
import { getCurrentUser } from '@/utils/auth'

const router = useRouter()
const userData = ref(null)
const isCheckingAuth = ref(true)
const isTerminated = ref(false)

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

const checkAccountStatus = async () => {
  if (!userData.value) return;
  try {
    const res = await api.get('/client/shop/account-status')
    if (res.data.success) {
      isTerminated.value = res.data.is_terminated
    }
  } catch (err) {
    console.error('Failed to check account status', err)
  }
}

const setupWebsockets = () => {
  if (!userData.value || !userData.value.id) return

  echo.private(`account.status.${userData.value.id}`)
    .listen('.AccountStatusUpdated', (e) => {
      isTerminated.value = e.status === 'terminated'
      
      if (isTerminated.value) {
        toast.error('Account Restricted', { description: 'Your account has been limited by an administrator.' })
      } else {
        toast.success('Account Restored', { description: 'Your standard account access has been fully restored.' })
      }
    })
}

onMounted(async () => {
  initializeUserData()
  
  try {
    const user = await getCurrentUser()
    if (user) {
      userData.value = user
      localStorage.setItem('user_data', JSON.stringify(userData.value))
      
      // Perform security checks
      await checkAccountStatus()
      setupWebsockets()
    } else {
      userData.value = null // Explicitly handle guest state
    }
  } catch (error) {
    console.warn("Continuing as guest user.")
    userData.value = null
  } finally {
    isCheckingAuth.value = false
  }
})

onUnmounted(() => {
  if (progressInterval) clearInterval(progressInterval)
  if (echo && userData.value) {
    echo.leave(`account.status.${userData.value.id}`)
  }
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