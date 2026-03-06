<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-900 font-sans text-slate-100 overflow-hidden">
      <SideBarSpecialRBAC 
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
                <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                  <div class="w-16 h-16 border-4 border-white/20 border-t-white rounded-full animate-spin"></div>
                </div>
              </div>
              <h2 class="text-2xl font-bold text-white mb-2">Logging Out</h2>
              <p class="text-slate-400">Securely closing your session...</p>
            </div>
          </div>
        </transition>

        <div class="flex-1 w-full bg-slate-50 text-slate-900 relative">
          <router-view v-slot="{ Component }">
            <transition name="page" mode="out-in">
              <component :is="Component" />
            </transition>
          </router-view>
        </div>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref } from 'vue'
import { SidebarProvider, SidebarInset } from '@/components/ui/sidebar'
import SideBarSpecialRBAC from '@/layouts/sideBarSpecialRBAC.vue'

const isLoggingOut = ref(false)

const handleLogoutStart = () => {
  isLoggingOut.value = true
}

const handleLogoutFinish = () => {
  // Logic after backend success, before redirect
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { 
  transition: all 0.5s ease; 
}
.fade-enter-from, .fade-leave-to { 
  opacity: 0; 
  backdrop-filter: blur(0px); 
}

.page-enter-active, .page-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.page-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>