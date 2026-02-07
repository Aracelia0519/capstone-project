<template>
  <Sidebar collapsible="icon" class="border-r border-slate-800/50 bg-slate-900 transition-all duration-500 ease-in-out">
    <SidebarHeader class="h-24 border-b border-slate-800/50 flex flex-row items-center px-4 overflow-hidden bg-slate-900">
      <div class="flex items-center gap-3 w-full">
        <div class="relative shrink-0 flex items-center justify-center">
          <Avatar class="w-10 h-10 ring-2 ring-indigo-500/30 ring-offset-2 ring-offset-slate-900">
            <div class="w-full h-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center">
              <ShoppingBag class="w-5 h-5 text-white" />
            </div>
          </Avatar>
        </div>
        
        <div v-if="state === 'expanded' || isMobile" class="flex flex-col min-w-0 nav-text-clip flex-1">
          <h2 class="text-sm font-bold text-slate-100 truncate tracking-tight">{{ userName }}</h2>
          <p class="text-[10px] font-semibold text-indigo-400 uppercase tracking-widest">Operational Hub</p>
        </div>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3 py-4 space-y-6 bg-slate-900 overflow-x-hidden">
      <SidebarGroup v-for="section in navigation" :key="section.title" class="p-0">
        <SidebarGroupLabel v-if="state === 'expanded' || isMobile" class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 nav-text-clip">
          {{ section.title }}
        </SidebarGroupLabel>
        
        <SidebarMenu>
          <SidebarMenuItem v-for="item in section.items" :key="item.path">
            <SidebarMenuButton 
              as-child 
              :tooltip="item.name" 
              class="h-11 w-full rounded-xl transition-all duration-300 text-white/70 hover:text-white hover:bg-slate-800/50 flex items-center"
              active-class="bg-gradient-to-r from-indigo-500/20 to-purple-500/10 !text-white ring-1 ring-indigo-500/30"
            >
              <router-link :to="item.path" class="flex items-center w-full px-2">
                <div class="shrink-0 flex items-center justify-center w-6 h-6">
                  <component :is="item.icon" class="w-5 h-5" :class="item.color" />
                </div>
                <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">{{ item.name }}</span>
                
                <Badge 
                  v-if="(state === 'expanded' || isMobile) && item.badge" 
                  variant="outline" 
                  class="ml-auto text-[9px] bg-indigo-500/20 text-indigo-300 border-indigo-500/30 font-bold px-1.5 h-4"
                >
                  {{ item.badge }}
                </Badge>
              </router-link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter class="px-3 py-4 border-t border-slate-800/50 bg-slate-900 space-y-1">
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton as-child tooltip="Profile" class="h-11 w-full rounded-xl text-white/70 hover:text-white hover:bg-slate-800/50">
            <router-link to="/ECommerce/profile" class="flex items-center w-full px-2">
              <div class="shrink-0 flex items-center justify-center w-6 h-6">
                <UserCircle class="w-5 h-5 text-slate-400" />
              </div>
              <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">Profile</span>
            </router-link>
          </SidebarMenuButton>
        </SidebarMenuItem>

        <SidebarMenuItem>
          <SidebarMenuButton 
            as-child
            tooltip="Logout" 
            class="h-11 w-full rounded-xl transition-all duration-300 text-white hover:text-red-400 hover:bg-red-500/10 p-0"
          >
            <Button 
              variant="ghost" 
              class="w-full h-full justify-start px-2 font-normal hover:bg-transparent text-white" 
              @click="showLogoutModal = true"
              :disabled="isLoggingOut"
            >
              <div class="shrink-0 flex items-center justify-center w-6 h-6">
                <LogOut v-if="!isLoggingOut" class="w-5 h-5" />
                <Loader2 v-else class="w-5 h-5 animate-spin" />
              </div>
              <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">
                {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
              </span>
            </Button>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>

      <div v-if="!isMobile" class="flex justify-center pt-2">
        <SidebarTrigger class="text-slate-500 hover:text-slate-300 hover:bg-slate-800/50 transition-colors" />
      </div>
    </SidebarFooter>

    <Dialog :open="showLogoutModal && !isLoggingOut" @update:open="showLogoutModal = $event">
      <DialogContent class="bg-slate-900/95 backdrop-blur-sm border-slate-800/50 text-white shadow-2xl max-w-[400px] sm:rounded-3xl p-6">
        <div class="flex flex-col items-center">
          <div class="w-20 h-20 rounded-full bg-indigo-500/10 flex items-center justify-center mb-4 relative">
            <div class="absolute inset-0 bg-indigo-500/10 rounded-full animate-ping opacity-75" />
            <LogOut class="text-indigo-400 w-10 h-10 relative z-10" />
          </div>
          <DialogTitle class="text-2xl font-black bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">End Session?</DialogTitle>
          <p class="text-slate-400 mt-2 text-center">Are you sure you want to exit the Hub?</p>
          <div class="flex w-full gap-3 mt-8">
            <Button variant="outline" class="flex-1 rounded-xl border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-white" @click="showLogoutModal = false">Cancel</Button>
            <Button class="flex-1 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:opacity-90 transition-opacity" @click="confirmLogout" :disabled="isLoggingOut">
              <Loader2 v-if="isLoggingOut" class="mr-2 h-4 w-4 animate-spin" />
              Logout
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </Sidebar>
</template>

<script setup>
import { ref, onMounted, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import { 
  LayoutDashboard, ShoppingBag, Box, Tag, ClipboardList, CreditCard, 
  Truck, Undo2, Star, Percent, BarChart3, LogOut, Loader2, 
  UserCircle, PackageSearch
} from 'lucide-vue-next'
import { 
  Sidebar, SidebarHeader, SidebarContent, SidebarFooter, 
  SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuItem, 
  SidebarMenuButton, SidebarTrigger, useSidebar 
} from '@/components/ui/sidebar'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar } from '@/components/ui/avatar'
import api from '@/utils/axios'

const { state, isMobile } = useSidebar()
const router = useRouter()
const emit = defineEmits(['logout-started', 'logout-finished'])

const isLoggingOut = ref(false)
const showLogoutModal = ref(false)
const userName = ref('Distributor')

const navigation = [
  {
    title: 'Overview',
    items: [
      { name: 'Dashboard', path: '/ECommerce/ECDashboard', icon: LayoutDashboard, color: 'text-indigo-400', badge: 'Live' }
    ]
  },
  {
    title: 'Catalog & Inventory',
    items: [
      { name: 'Procurement', path: '/ECommerce/ECProcurement', icon: PackageSearch, color: 'text-blue-400', badge: 'Req' },
      { name: 'Categories', path: '/ECommerce/ECCategories', icon: Tag, color: 'text-emerald-400' }
    ]
  },
  {
    title: 'Sales Operations',
    items: [
      { name: 'Orders', path: '/ECommerce/ECOrders', icon: ClipboardList, color: 'text-amber-400' },
      { name: 'Payments', path: '/ECommerce/ECPayment', icon: CreditCard, color: 'text-green-400' },
      { name: 'Delivery', path: '/ECommerce/ECDelivery', icon: Truck, color: 'text-cyan-400' },
      { name: 'Returns', path: '/ECommerce/ECReturns', icon: Undo2, color: 'text-red-400' }
    ]
  },
  {
    title: 'Analytics & UX',
    items: [
      { name: 'Reviews', path: '/ECommerce/ECReviews', icon: Star, color: 'text-violet-400' },
      { name: 'Promotions', path: '/ECommerce/ECPromotions', icon: Percent, color: 'text-orange-400' },
      { name: 'Reports', path: '/ECommerce/ECreports', icon: BarChart3, color: 'text-sky-400' }
    ]
  }
]

const confirmLogout = async () => {
  isLoggingOut.value = true
  showLogoutModal.value = false
  emit('logout-started')
  
  try {
    await api.post('/auth/logout')
    emit('logout-finished')
    setTimeout(() => {
      localStorage.clear()
      router.push('/Landing/logIn')
    }, 1000)
  } catch (error) {
    emit('logout-finished')
    localStorage.clear()
    router.push('/Landing/logIn')
  }
}

onMounted(() => {
  const data = localStorage.getItem('user_data')
  if (data) {
    const user = JSON.parse(data)
    userName.value = user.name || `${user.first_name} ${user.last_name}`
  }
})
</script>

<style scoped>
.nav-text-clip {
  white-space: nowrap;
  overflow: hidden;
  display: inline-block;
  transition: opacity 0.2s ease;
}
</style>