<template>
  <SidebarProvider>
    <div class="flex min-h-screen w-full bg-slate-900 font-sans overflow-hidden">
      <SideBarSpecialRBAC 
        @logout-started="handleLogoutStart" 
        @logout-finished="handleLogoutFinish"
      />

      <SidebarInset class="main-content-area bg-white border-none transition-all duration-500 ease-in-out relative min-h-screen flex flex-col overflow-y-auto text-slate-900">
        
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

        <header class="flex h-16 shrink-0 items-center gap-2 px-4 md:hidden fixed top-0 left-0 right-0 z-40 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/50">
          <SidebarTrigger class="-ml-1 text-indigo-500 hover:bg-slate-800" />
          <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Special RBAC</span>
        </header>

        <main class="flex-1 w-full bg-slate-50 relative z-10 p-4 md:p-8 pt-20 md:pt-8 text-slate-900">
          <router-view v-slot="{ Component, route }">
            <transition name="page" mode="out-in">
              <component :is="Component" :key="route.path" />
            </transition>
          </router-view>
        </main>
      </SidebarInset>
    </div>
  </SidebarProvider>
</template>

<script setup>
import { ref } from 'vue'
import { SidebarProvider, SidebarInset, SidebarTrigger } from '@/components/ui/sidebar'
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
/* Preserved smooth fade animation for logout */
.fade-enter-active, .fade-leave-active { 
  transition: all 0.5s ease; 
}
.fade-enter-from, .fade-leave-to { 
  opacity: 0; 
  backdrop-filter: blur(0px); 
}

/* Preserved smooth slide-in/out animation for page routing */
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

/* DEEP OVERRIDE: Forces all headers inside the main content to be black */
:deep(main h1),
:deep(main h2),
:deep(main h3),
:deep(main h4),
:deep(main h5),
:deep(main h6) {
  color: #0f172a !important; /* Tailwind slate-900 */
}
</style>