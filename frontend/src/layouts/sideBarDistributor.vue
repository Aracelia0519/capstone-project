<template>
  <Sidebar collapsible="icon" class="border-r border-slate-800/50 bg-slate-900 transition-all duration-500 ease-in-out">
    <SidebarHeader class="h-auto py-6 border-b border-slate-800/50 bg-slate-900 px-4">
      <div class="flex items-center gap-3 w-full">
        <div class="relative shrink-0">
          <Avatar class="w-12 h-12 ring-2 ring-blue-500/30 ring-offset-2 ring-offset-slate-900">
            <div :class="['w-full h-full flex items-center justify-center bg-gradient-to-br', isVerified ? 'from-green-400 to-emerald-400' : 'from-blue-500 via-purple-500 to-pink-500']">
              <User class="w-6 h-6 text-white" />
            </div>
          </Avatar>
          <div v-if="isVerified" class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-slate-900" />
        </div>
        
        <div v-if="state === 'expanded' || isMobile" class="flex flex-col min-w-0 flex-1 nav-text-clip">
          <h2 class="text-sm font-bold text-slate-100 truncate">{{ userName }}</h2>
          <p class="text-[10px] font-semibold uppercase tracking-widest text-blue-400">
            {{ isVerified ? 'Verified Distributor' : 'Verification Required' }}
          </p>
        </div>
      </div>

      <div v-if="(state === 'expanded' || isMobile) && isVerified" class="mt-4 grid grid-cols-3 gap-1 p-2 bg-slate-800/40 rounded-xl border border-slate-700/50 nav-text-clip">
        <div class="text-center"><p class="text-[9px] text-slate-500 uppercase">Orders</p><p class="text-xs font-bold text-blue-300">12</p></div>
        <div class="text-center border-x border-slate-700"><p class="text-[9px] text-slate-500 uppercase">Stock</p><p class="text-xs font-bold text-purple-300">85%</p></div>
        <div class="text-center"><p class="text-[9px] text-slate-500 uppercase">Low</p><p class="text-xs font-bold text-pink-300">3</p></div>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3 py-4 space-y-4 bg-slate-900 overflow-x-hidden">
      <SidebarGroup v-for="section in navigation" :key="section.title" class="p-0">
        <SidebarGroupLabel v-if="state === 'expanded' || isMobile" class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2 nav-text-clip">
          {{ section.title }}
        </SidebarGroupLabel>
        
        <SidebarMenu>
          <SidebarMenuItem v-for="item in section.items" :key="item.name">
            <SidebarMenuButton 
              as-child 
              :tooltip="item.name" 
              :disabled="item.requiresVerify && !isVerified"
              class="h-11 w-full rounded-xl transition-all duration-300 text-white/70 hover:text-white hover:bg-slate-800/50"
              active-class="bg-gradient-to-r from-blue-500/20 to-purple-500/10 !text-white ring-1 ring-blue-500/30"
            >
              <router-link v-if="!item.requiresVerify || isVerified" :to="item.path" class="flex items-center w-full px-2">
                <div class="shrink-0 flex items-center justify-center w-6 h-6">
                  <div class="relative">
                    <component :is="item.icon" class="w-5 h-5" :class="item.color" />
                    <div v-if="item.isAi" class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-purple-400 rounded-full border border-slate-900 text-[5px] flex items-center justify-center font-bold">AI</div>
                  </div>
                </div>
                <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">{{ item.name }}</span>
                <Badge v-if="(state === 'expanded' || isMobile) && item.badge" :class="['ml-auto text-[9px] border-none', item.badgeColor || 'bg-slate-800 text-slate-400']">
                  {{ item.badge }}
                </Badge>
              </router-link>
              <div v-else @click="emit('open-verification-modal')" class="flex items-center w-full px-2 opacity-50 cursor-not-allowed">
                <div class="shrink-0 flex items-center justify-center w-6 h-6">
                  <component :is="item.icon" class="w-5 h-5 text-slate-500" />
                </div>
                <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">Locked</span>
                <Lock v-if="state === 'expanded' || isMobile" class="ml-auto w-3 h-3 text-slate-600" />
              </div>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter class="px-3 py-4 border-t border-slate-800/50 bg-slate-900 space-y-1">
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton as-child tooltip="Profile" class="h-11 w-full rounded-xl text-white/70 hover:text-white hover:bg-slate-800/50">
            <router-link to="/distributor/ProfileSettings" class="flex items-center w-full px-2">
              <div class="shrink-0 flex items-center justify-center w-6 h-6">
                <Settings class="w-5 h-5 text-slate-400" />
              </div>
              <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">Profile / Settings</span>
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
          <div class="w-20 h-20 rounded-full bg-blue-500/10 flex items-center justify-center mb-4 relative">
            <div class="absolute inset-0 bg-blue-500/10 rounded-full animate-ping opacity-75" />
            <LogOut class="text-blue-400 w-10 h-10 relative z-10" />
          </div>
          <DialogTitle class="text-2xl font-black bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">End Session?</DialogTitle>
          <p class="text-slate-400 mt-2 text-center">Your distributor session will be securely terminated.</p>
          <div class="flex w-full gap-3 mt-8">
            <Button variant="outline" class="flex-1 rounded-xl border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-white" @click="showLogoutModal = false">Cancel</Button>
            <Button class="flex-1 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 hover:opacity-90 transition-opacity" @click="confirmLogout" :disabled="isLoggingOut">
              <Loader2 v-if="isLoggingOut" class="mr-2 h-4 w-4 animate-spin" />
              {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </Sidebar>
</template>

<script setup>
import { ref, computed, onMounted, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import { 
  LayoutDashboard, Package, ClipboardList, TrendingUp, Users, 
  Settings, LogOut, Lock, User, CheckCircle, Box, LineChart, 
  History, Building2, UserCog, Wallet, FileBarChart, Lightbulb, Loader2
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

const props = defineProps({ verificationStatus: String })
const emit = defineEmits(['open-verification-modal', 'logout-started', 'logout-finished'])
const { state, isMobile } = useSidebar()
const router = useRouter()

const isLoggingOut = ref(false)
const showLogoutModal = ref(false)
const userName = ref('Distributor Hub')
const isVerified = computed(() => props.verificationStatus === 'approved')

const navigation = [
  {
    title: 'Distributor Hub',
    items: [
      { name: 'Dashboard', path: '/distributor/distributordashboard', icon: LayoutDashboard, color: 'text-blue-400', badge: 'Overview', requiresVerify: true }
    ]
  },
  {
    title: 'Inventory',
    items: [
      { name: 'Product Available', path: '/distributor/ProductAvailable', icon: Box, color: 'text-teal-400', badge: 'New', badgeColor: 'bg-teal-500/20 text-teal-300', requiresVerify: true },
      { name: 'Paint Inventory', path: '/distributor/PaintInventory', icon: Package, color: 'text-emerald-400', badge: '3 Low', badgeColor: 'bg-red-500/20 text-red-300', requiresVerify: true }
    ]
  },
  {
    title: 'Orders & Requests',
    items: [
      { name: 'Orders / Requests', path: '/distributor/OrdersRequest', icon: ClipboardList, color: 'text-orange-400', badge: '12', badgeColor: 'bg-orange-500/20 text-orange-300', requiresVerify: true }
    ]
  },
  {
    title: 'Analytics',
    items: [
      { name: 'Color Demand Insights', path: '/distributor/ColorDemandInsights', icon: Lightbulb, color: 'text-purple-400', isAi: true, badge: 'ML', badgeColor: 'bg-purple-500/20 text-purple-300', requiresVerify: true },
      { name: 'Sales History', path: '/distributor/SalesHistory', icon: History, color: 'text-indigo-400', requiresVerify: true }
    ]
  },
  {
    title: 'Network',
    items: [
      { name: 'Service Providers', path: '/distributor/ServiceProviders', icon: Users, color: 'text-teal-400', badge: '24', requiresVerify: true },
      { name: 'Operational Distributors', path: '/distributor/OperationalDistributorD', icon: Building2, color: 'text-indigo-400', requiresVerify: true },
      { name: 'HR Managers', path: '/distributor/HRmanagerD', icon: UserCog, color: 'text-green-400', requiresVerify: true },
      { name: 'Finance Managers', path: '/distributor/FinanceManagerD', icon: Wallet, color: 'text-purple-400', requiresVerify: true }
    ]
  },
  {
    title: 'Reports',
    items: [
      { name: 'Reports', path: '/distributor/ReportsD', icon: FileBarChart, color: 'text-pink-400', requiresVerify: true }
    ]
  }
]

const confirmLogout = async () => {
  isLoggingOut.value = true
  showLogoutModal.value = false
  emit('logout-started')

  try {
    await new Promise(resolve => setTimeout(resolve, 1500)) // Visual delay
    const response = await api.post('/auth/logout')
    
    if (response.data.status === 'success') {
      emit('logout-finished')
      setTimeout(() => {
        localStorage.clear()
        router.push('/Landing/logIn')
      }, 1000)
    }
  } catch (e) {
    emit('logout-finished')
    setTimeout(() => {
      localStorage.clear()
      router.push('/Landing/logIn')
    }, 1000)
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