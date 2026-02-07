<template>
  <div class="min-h-screen bg-white font-sans text-slate-900 selection:bg-blue-500/30">
    <Toaster position="top-right" />

    <TopBar />

    <main class="flex-1 w-full bg-white relative min-h-[calc(100vh-64px)]">
      <div class="container mx-auto">
        <Suspense>
          <template #default>
            <router-view v-slot="{ Component }">
              <transition name="page-slide" mode="out-in">
                <component 
                  :is="Component" 
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
import { Toaster } from '@/components/ui/sonner'
import TopBar from '../layouts/topBarECommerceClient.vue'
import { Loader2, Paintbrush } from 'lucide-vue-next'

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
</style>