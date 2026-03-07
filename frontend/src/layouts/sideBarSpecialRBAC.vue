<template>
  <Sidebar collapsible="icon" class="border-r border-slate-800/50 bg-slate-900 transition-all duration-500 ease-in-out">
    <SidebarHeader class="h-auto py-6 border-b border-slate-800/50 bg-slate-900 px-4 shrink-0">
      <div class="flex items-center gap-3 w-full">
        <div class="relative shrink-0">
          <Avatar class="w-12 h-12 ring-2 ring-indigo-500/30 ring-offset-2 ring-offset-slate-900">
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
              <ShieldCheck class="w-6 h-6 text-white" />
            </div>
          </Avatar>
          <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-slate-900" />
        </div>
        
        <div v-if="state === 'expanded' || isMobile" class="flex flex-col min-w-0 flex-1 nav-text-clip">
          <h2 class="text-sm font-bold text-slate-100 truncate">{{ userDetails.name || 'Loading...' }}</h2>
          <div class="flex items-center gap-1.5 mt-0.5">
            <Badge variant="outline" class="h-5 px-1.5 text-[10px] font-medium bg-indigo-500/10 text-indigo-400 border-indigo-500/20 truncate max-w-[120px]">
              {{ userDetails.position || 'Special RBAC' }}
            </Badge>
          </div>
        </div>
      </div>
    </SidebarHeader>

    <SidebarContent class="bg-slate-900 flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar">
      <div v-if="isLoading" class="p-6 flex justify-center">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-500"></div>
      </div>

      <div v-else-if="navigationData.length === 0" class="p-6 text-center text-slate-400 text-sm">
        No modules accessible.
      </div>

      <SidebarGroup v-else v-for="(group, idx) in navigationData" :key="idx" class="px-2 py-4">
        <SidebarGroupLabel v-if="state === 'expanded' || isMobile" class="px-3 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2 nav-text-clip">
          {{ group.title }}
        </SidebarGroupLabel>
        
        <SidebarMenu>
          <SidebarMenuItem v-for="item in group.items" :key="item.permissionKey">
            <SidebarMenuButton asChild :tooltip="item.name" class="h-auto">
              <router-link 
                :to="item.path"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 transition-all duration-300 hover:bg-slate-800 hover:text-white group"
                active-class="bg-indigo-500/10 text-indigo-400 font-medium relative after:absolute after:left-0 after:top-1/2 after:-translate-y-1/2 after:w-1 after:h-8 after:bg-indigo-500 after:rounded-r-full"
              >
                <div class="shrink-0 flex items-center justify-center w-5 h-5">
                  <component 
                    :is="iconMap[item.icon] || fallbackIcon" 
                    class="w-5 h-5 transition-colors duration-300"
                    :class="[$route.path.includes(item.path) ? 'text-indigo-400' : 'text-slate-400 group-hover:text-white']"
                  />
                </div>
                <span v-if="state === 'expanded' || isMobile" class="truncate nav-text-clip">{{ item.name }}</span>
              </router-link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter class="border-t border-slate-800/50 bg-slate-900 p-3 shrink-0">
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton 
            @click="showLogoutModal = true"
            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-slate-300 transition-all duration-300 hover:bg-red-500/10 hover:text-red-400 group h-auto"
          >
            <div class="shrink-0 flex items-center justify-center w-5 h-5">
              <LogOut class="w-5 h-5 text-slate-400 group-hover:text-red-400" />
            </div>
            <span v-if="state === 'expanded' || isMobile" class="font-medium nav-text-clip">Log out</span>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>

      <div v-if="!isMobile" class="flex justify-center pt-2">
        <SidebarTrigger class="text-slate-500 hover:text-slate-300 hover:bg-slate-800/50 transition-colors" />
      </div>
    </SidebarFooter>

    <Dialog :open="showLogoutModal" @update:open="showLogoutModal = $event">
      <DialogContent class="sm:max-w-md bg-slate-900 border-slate-800">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
            <LogOut class="w-5 h-5 text-red-500" />
            Confirm Logout
          </DialogTitle>
          <DialogDescription class="text-slate-400 mt-2">
            Are you sure you want to log out of your session?
          </DialogDescription>
        </DialogHeader>
        <div class="flex justify-end gap-3 mt-6">
          <Button variant="outline" @click="showLogoutModal = false" class="bg-slate-800 text-slate-300 border-slate-700 hover:bg-slate-700 hover:text-white">
            Cancel
          </Button>
          <Button @click="confirmLogout" class="bg-red-500 hover:bg-red-600 text-white border-0">
            Yes, Log out
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </Sidebar>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from '@/utils/axios'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarGroup,
  SidebarGroupLabel,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarTrigger,
  useSidebar,
} from '@/components/ui/sidebar'
import { Avatar } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'

// Import all potential icons here
import { 
  ShieldCheck, LayoutDashboard, Users, Briefcase, Building2, UserCheck, 
  UserPlus, Banknote, Clock, CalendarX, FileBarChart, PieChart, ArrowLeftRight, 
  CreditCard, FileText, BarChart3, ShoppingCart, Wallet, CheckCircle, 
  Activity, PackagePlus, Boxes, ShoppingBag, Truck, LogOut, Circle
} from 'lucide-vue-next'

const emit = defineEmits(['logout-started', 'logout-finished'])

// Create a mapping object for string-to-component conversion
const iconMap = {
  LayoutDashboard, Users, Briefcase, Building2, UserCheck, UserPlus, Banknote, 
  Clock, CalendarX, FileBarChart, PieChart, ArrowLeftRight, CreditCard, 
  FileText, BarChart3, ShoppingCart, Wallet, CheckCircle, Activity, 
  PackagePlus, Boxes, ShoppingBag, Truck
}
const fallbackIcon = Circle // Fallback if icon string from backend isn't mapped

const router = useRouter()
const route = useRoute()
const { state, isMobile } = useSidebar()

const navigationData = ref([])
const userDetails = ref({})
const isLoading = ref(true)
const showLogoutModal = ref(false)

const fetchDynamicSidebar = async () => {
  try {
    const response = await axios.get('/special-rbac/sidebar')
    if (response.data.success) {
      navigationData.value = response.data.data.navigation
      userDetails.value = response.data.data.user_details
    }
  } catch (error) {
    console.error('Failed to load RBAC sidebar:', error)
  } finally {
    isLoading.value = false
  }
}

const confirmLogout = async () => {
  showLogoutModal.value = false
  emit('logout-started')

  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    const response = await axios.post('/auth/logout')
    
    if (response.data.status === 'success') {
      emit('logout-finished')
      setTimeout(() => {
        localStorage.clear()
        router.push('/Landing/logIn')
      }, 500)
    }
  } catch (e) {
    emit('logout-finished')
    setTimeout(() => {
      localStorage.clear()
      router.push('/Landing/logIn')
    }, 500)
  }
}

onMounted(() => {
  fetchDynamicSidebar()
})
</script>

<style scoped>
.nav-text-clip {
  white-space: nowrap;
  overflow: hidden;
  display: inline-block;
  transition: opacity 0.2s ease;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}
</style>