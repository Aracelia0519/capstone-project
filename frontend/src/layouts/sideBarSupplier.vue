<template>
  <Sidebar collapsible="icon" class="border-r border-slate-800/50 bg-slate-900 transition-all duration-500 ease-in-out">
    <SidebarHeader class="h-auto py-6 border-b border-slate-800/50 bg-slate-900 px-4">
      <div class="flex items-center gap-3 w-full">
        <div class="relative shrink-0">
          <Avatar class="w-12 h-12 ring-2 ring-emerald-500/30 ring-offset-2 ring-offset-slate-900">
            <div :class="['w-full h-full flex items-center justify-center bg-gradient-to-br', isVerified && !isTerminated ? 'from-green-400 to-emerald-400' : (isTerminated ? 'from-red-500 to-rose-600' : 'from-emerald-500 via-teal-500 to-cyan-500')]">
              <Factory class="w-6 h-6 text-white" />
            </div>
          </Avatar>
          <div v-if="isVerified && !isTerminated" class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-slate-900" />
          <div v-if="isTerminated" class="absolute -bottom-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-slate-900 flex items-center justify-center">
            <AlertTriangle class="w-2.5 h-2.5 text-white" />
          </div>
        </div>
        
        <div v-if="state === 'expanded' || isMobile" class="flex flex-col min-w-0 flex-1 nav-text-clip">
          <h2 class="text-sm font-bold text-slate-100 truncate">{{ supplierName }}</h2>
          <p v-if="!isTerminated" class="text-[10px] font-semibold uppercase tracking-widest text-emerald-400">
            {{ isVerified ? 'Verified Supplier' : 'Verification Required' }}
          </p>
          <p v-else class="text-[10px] font-bold uppercase tracking-widest text-red-400">
            Restricted Account
          </p>
        </div>
      </div>

      <div v-if="(state === 'expanded' || isMobile) && isVerified && !isTerminated" class="mt-4 grid grid-cols-3 gap-1 p-2 bg-slate-800/40 rounded-xl border border-slate-700/50 nav-text-clip">
        <div class="text-center"><p class="text-[9px] text-slate-500 uppercase">New POs</p><p class="text-xs font-bold text-emerald-300">5</p></div>
        <div class="text-center border-x border-slate-700"><p class="text-[9px] text-slate-500 uppercase">Pending</p><p class="text-xs font-bold text-yellow-300">2</p></div>
        <div class="text-center"><p class="text-[9px] text-slate-500 uppercase">Rating</p><p class="text-xs font-bold text-blue-300">4.9</p></div>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3 py-4 space-y-4 bg-slate-900 overflow-x-hidden">
      
      <!-- Termination Alert Banner -->
      <div v-if="isTerminated && (state === 'expanded' || isMobile)" class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl mb-4 transition-all">
        <div class="flex items-center gap-2 text-red-400 mb-2">
          <AlertTriangle class="w-4 h-4 shrink-0" />
          <span class="text-xs font-bold uppercase tracking-wider">Access Restricted</span>
        </div>
        <p class="text-[11px] text-slate-400 leading-relaxed mb-4">
          Your account access has been restricted due to a system policy action.
        </p>
        <Button 
          variant="outline" 
          size="sm" 
          class="w-full h-8 text-[10px] bg-transparent border-red-500/30 text-red-400 hover:bg-red-500/20 hover:text-red-300 transition-colors uppercase tracking-widest font-black" 
          @click="showTerminationModal = true"
        >
          View Full Notice
        </Button>
      </div>

      <SidebarGroup v-for="section in filteredNavigation" :key="section.title" class="p-0">
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
              active-class="bg-gradient-to-r from-emerald-500/20 to-teal-500/10 !text-white ring-1 ring-emerald-500/30"
            >
              <router-link v-if="!item.requiresVerify || isVerified" :to="item.path" class="flex items-center w-full px-2">
                <div class="shrink-0 flex items-center justify-center w-6 h-6">
                  <component :is="item.icon" class="w-5 h-5" :class="item.color" />
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
          <SidebarMenuButton as-child tooltip="Settings" class="h-11 w-full rounded-xl text-white/70 hover:text-white hover:bg-slate-800/50">
            <router-link to="/Supplier/SupplierSettings" class="flex items-center w-full px-2">
              <div class="shrink-0 flex items-center justify-center w-6 h-6">
                <Settings class="w-5 h-5 text-slate-400" />
              </div>
              <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">Settings</span>
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

    <!-- Detailed Restriction Modal (Fullscreen/Centered) -->
    <Dialog :open="showTerminationModal" @update:open="showTerminationModal = $event">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 shadow-2xl max-w-2xl sm:rounded-3xl p-8 max-h-[90vh] overflow-y-auto z-[10005]">
        <div class="flex flex-col items-center text-center">
          
          <div class="w-24 h-24 rounded-full bg-red-500/10 flex items-center justify-center mb-6 relative">
            <div class="absolute inset-0 bg-red-500/20 rounded-full animate-ping opacity-75" />
            <AlertTriangle class="text-red-500 w-12 h-12 relative z-10" />
          </div>
          
          <DialogTitle class="text-3xl font-black text-white mb-2 tracking-tight">Account Restricted</DialogTitle>
          <p class="text-slate-400 mb-8 max-w-md">Your operational access to the Supplier Portal has been temporarily suspended or terminated.</p>
          
          <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 w-full text-left space-y-6 shadow-inner">
            
            <div class="flex items-center justify-between border-b border-slate-700/50 pb-4">
              <div>
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Current Status</h4>
                <Badge variant="destructive" class="bg-red-500/10 text-red-400 border border-red-500/20 text-sm py-1 px-3">Terminated</Badge>
              </div>
              <div class="text-right" v-if="terminationDetails?.terminated_at">
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Date of Action</h4>
                <p class="text-sm font-medium text-slate-300">{{ new Date(terminationDetails.terminated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
              </div>
            </div>
            
            <div v-if="terminationDetails?.termination_type">
              <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1 flex items-center gap-2">
                 Violation Categorization
              </h4>
              <p class="text-base font-bold text-slate-200 capitalize bg-slate-900/50 p-3 rounded-xl border border-slate-700/50">
                {{ terminationDetails.termination_type.replace(/_/g, ' ') }}
              </p>
            </div>

            <div v-if="terminationDetails?.reason">
              <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Official Reason for Action</h4>
              <p class="text-sm text-slate-300 leading-relaxed bg-slate-900/50 p-4 rounded-xl border border-slate-700/50 whitespace-pre-line">
                {{ terminationDetails.reason }}
              </p>
            </div>
          </div>

          <div class="mt-8 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-5 text-left w-full flex items-start gap-4">
             <div class="mt-1 shrink-0 p-2 bg-emerald-500/20 rounded-full text-emerald-400">
               <Bell class="w-5 h-5" />
             </div>
             <div>
                <h4 class="text-sm font-bold text-emerald-100 mb-1">Need to Appeal?</h4>
                <p class="text-xs text-emerald-200/70 leading-relaxed">
                  If you believe this is an administrative error or require further clarification, kindly contact our team via 
                  <strong class="text-white">Settings > Admin Support</strong> for immediate assistance. Our dispute resolution team operates 24/7.
                </p>
             </div>
          </div>

          <div class="w-full mt-8 pt-6 border-t border-slate-800 flex justify-center">
            <Button variant="outline" class="w-full max-w-sm h-12 rounded-xl border-slate-700 bg-slate-800 hover:bg-slate-700 text-white font-bold transition-all hover:scale-105" @click="showTerminationModal = false">
              Acknowledge & Close
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showLogoutModal && !isLoggingOut" @update:open="showLogoutModal = $event">
      <DialogContent class="bg-slate-900/95 backdrop-blur-sm border-slate-800/50 text-white shadow-2xl max-w-[400px] sm:rounded-3xl p-6">
        <div class="flex flex-col items-center">
          <div class="w-20 h-20 rounded-full bg-emerald-500/10 flex items-center justify-center mb-4 relative">
            <div class="absolute inset-0 bg-emerald-500/10 rounded-full animate-ping opacity-75" />
            <LogOut class="text-emerald-400 w-10 h-10 relative z-10" />
          </div>
          <DialogTitle class="text-2xl font-black bg-gradient-to-r from-emerald-400 to-teal-400 bg-clip-text text-transparent">End Session?</DialogTitle>
          <p class="text-slate-400 mt-2 text-center">You will be returned to the login screen.</p>
          <div class="flex w-full gap-3 mt-8">
            <Button variant="outline" class="flex-1 rounded-xl border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-white" @click="showLogoutModal = false">Cancel</Button>
            <Button class="flex-1 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:opacity-90 transition-opacity" @click="confirmLogout" :disabled="isLoggingOut">
              <Loader2 v-if="isLoggingOut" class="mr-2 h-4 w-4 animate-spin" />
              {{ isLoggingOut ? 'Signing out...' : 'Logout' }}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </Sidebar>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, defineEmits, defineProps } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import echo from '@/utils/websocket'
import { 
  LayoutDashboard, Package, ShoppingCart, Truck, Factory,
  Settings, LogOut, FileText, Wallet, Calendar, Lock,
  Loader2, ScrollText, Container, Handshake, ClipboardList, 
  PackageCheck, Users, UserPlus, ShieldCheck, RotateCcw, Car,
  Bell, AlertTriangle, Bug
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

// Added fullAccess and accessibilities props
const props = defineProps({ 
  verificationStatus: String,
  fullAccess: {
    type: Boolean,
    default: false
  },
  accessibilities: {
    type: Array,
    default: () => []
  }
})
const emit = defineEmits(['open-verification-modal', 'logout-started', 'logout-finished'])
const { state, isMobile } = useSidebar()
const router = useRouter()

const isLoggingOut = ref(false)
const showLogoutModal = ref(false)
const showTerminationModal = ref(false)

const supplierName = ref('Supplier Portal')
const userId = ref(null)

const isTerminated = ref(false)
const terminationDetails = ref(null)

const isVerified = computed(() => props.verificationStatus === 'approved')

// Base Navigation Tree
const navigation = [
  {
    title: 'Overview',
    items: [
      { name: 'Dashboard', path: '/supplier/SupplierDashboard', icon: LayoutDashboard, color: 'text-emerald-400', badge: 'Live', requiresVerify: true }
    ]
  },
  {
    title: 'Network',
    hideOnTerminate: true,
    items: [
      { 
        name: 'Distributor Partner',
        path: '/supplier/DistributorPartnerReq', 
        icon: Handshake, 
        color: 'text-indigo-400', 
        badge: 'req',
        badgeColor: 'bg-amber-500/20 text-amber-300',
        requiresVerify: true,
        hideOnTerminate: true
      },
      { 
        name: 'Personnel Officer',
        path: '/supplier/PersonnelOfficer',
        icon: Users,
        color: 'text-emerald-400',
        badgeColor: 'bg-emerald-500/20 text-emerald-300',
        requiresVerify: true,
        hideOnTerminate: true
      },
      { 
        name: 'Add Personnel',
        path: '/supplier/AddPersonnel',
        icon: UserPlus,
        color: 'text-cyan-400',
        badge: 'new',
        badgeColor: 'bg-cyan-500/20 text-cyan-300',
        requiresVerify: true,
        hideOnTerminate: true
      },
      { 
        name: 'Role Activation',
        path: '/supplier/RoleActivation',
        icon: ShieldCheck,
        color: 'text-purple-400',
        badge: 'pending',
        badgeColor: 'bg-purple-500/20 text-purple-300',
        requiresVerify: true,
        hideOnTerminate: true
      },
      { 
        name: 'Vehicles', 
        path: '/supplier/SupplierVehicles', 
        icon: Car,
        color: 'text-amber-400', 
        requiresVerify: true,
        hideOnTerminate: true 
      }
    ]
  },
  {
    title: 'Order Management',
    items: [
      { 
        name: 'Order Request', 
        path: '/supplier/SupplierOrderRequest', 
        icon: ClipboardList, 
        color: 'text-emerald-400', 
        badgeColor: 'bg-emerald-500/20 text-emerald-300',
        requiresVerify: true 
      },
      { 
        name: 'Process Orders', 
        path: '/supplier/SupplierProcessOrders', 
        icon: PackageCheck, 
        color: 'text-amber-400', 
        badge: 'Pending',
        badgeColor: 'bg-amber-500/20 text-amber-300',
        requiresVerify: true 
      },
      { 
        name: 'Order History', 
        path: '/supplier/OrderHistory', 
        icon: ScrollText, 
        color: 'text-indigo-400', 
        requiresVerify: true,
        hideOnTerminate: true 
      },
      { 
        name: 'Returns', 
        path: '/supplier/SupplierReturns', 
        icon: RotateCcw, 
        color: 'text-rose-400', 
        requiresVerify: true,
        hideOnTerminate: true 
      }
    ]
  },
  {
    title: 'Inventory & Materials',
    hideOnTerminate: true,
    items: [
      { name: 'Raw Materials', path: '/supplier/SupplierRawMaterials', icon: Container, color: 'text-amber-400', requiresVerify: true, hideOnTerminate: true },
    ]
  },
  {
    title: 'Logistics',
    items: [
      { 
        name: 'Shipments', 
        path: '/supplier/SupplierShipments', 
        icon: Package, 
        color: 'text-purple-400', 
        requiresVerify: true 
      },
      { 
        name: 'Delivery', 
        path: '/supplier/SupplierDelivery', 
        icon: Truck,
        color: 'text-green-400', 
        requiresVerify: true,
        hideOnTerminate: true 
      },
    ]
  },
  {
    title: 'Financials',
    hideOnTerminate: true,
    items: [
      { name: 'Invoices', path: '/supplier/Invoices', icon: FileText, color: 'text-cyan-400', requiresVerify: true, hideOnTerminate: true },
      { name: 'Payments', path: '/supplier/SupplierPayments', icon: Wallet, color: 'text-green-400', requiresVerify: true, hideOnTerminate: true }
    ]
  },
  {
    title: 'System',
    items: [
      { 
        name: 'Technical Reports', 
        path: '/supplier/TechnicalReportsSup', 
        icon: Bug, 
        color: 'text-orange-400', 
        requiresVerify: true,
        hideOnTerminate: true
      },
      {
        name: 'Security Settings',
        path: '/supplier/securitySettingsSup',
        icon: ShieldCheck,
        color: 'text-emerald-400',
        requiresVerify: true,
        hideOnTerminate: true
      },
      { 
        name: 'Notifications', 
        path: '/supplier/notificationsSup', 
        icon: Bell, 
        color: 'text-blue-400', 
        requiresVerify: true
      },
      
    ]
  },
]

// Filter logic: Handles termination first, then falls back to personnel accessibilities
const filteredNavigation = computed(() => {
  let activeNav = navigation;

  // 1. If terminated, strip everything with hideOnTerminate
  if (isTerminated.value) {
    activeNav = activeNav.map(section => {
      if (section.hideOnTerminate) return null;
      const filteredItems = section.items.filter(item => !item.hideOnTerminate);
      if (filteredItems.length === 0) return null;
      return { ...section, items: filteredItems };
    }).filter(Boolean);
  }

  // 2. If fullAccess (Supplier Owner) return the stripped/full nav
  if (props.fullAccess) {
    return activeNav;
  }

  // 3. For Personnel Officer, filter by their DB accessibilities
  return activeNav.map(section => {
    const filteredItems = section.items.filter(item => {
      const itemPath = item.path.toLowerCase().replace(/^\/|\/$/g, '');
      const itemName = item.name.toLowerCase();

      return props.accessibilities.some(acc => {
        const dbPath = acc.path ? acc.path.toLowerCase().replace(/^\/|\/$/g, '') : '';
        const dbName = acc.name ? acc.name.toLowerCase() : '';
        return itemPath.includes(dbPath) || dbPath.includes(itemPath) || itemName === dbName;
      });
    });

    return { ...section, items: filteredItems };
  }).filter(section => section.items.length > 0);
});

const checkAccountStatus = async () => {
    try {
        const res = await api.get('/supplier/account-status')
        if (res.data.success) {
            isTerminated.value = res.data.is_terminated
            terminationDetails.value = res.data.termination_details || null

            if(isTerminated.value) {
                const allowedPaths = [
                    '/supplier/SupplierDashboard', 
                    '/supplier/SupplierOrderRequest', 
                    '/supplier/SupplierProcessOrders', 
                    '/supplier/SupplierShipments', 
                    '/supplier/notificationsSup',
                    '/Supplier/SupplierSettings'
                ]
                if(!allowedPaths.includes(router.currentRoute.value.path)){
                    router.push('/supplier/SupplierDashboard')
                }
            }
        }
    } catch (e) {
        console.error("Status Check Error:", e)
    }
}

const setupWebsockets = () => {
    if (!userId.value) return

    echo.private(`account.status.${userId.value}`)
        .listen('.AccountStatusUpdated', (e) => {
            isTerminated.value = e.status === 'terminated'
            terminationDetails.value = e.terminationData || null
            
            if (isTerminated.value) {
                toast.error('Account Restricted', { description: 'Your account has been limited by an administrator.' })
                const allowedPaths = [
                    '/supplier/SupplierDashboard', 
                    '/supplier/SupplierOrderRequest', 
                    '/supplier/SupplierProcessOrders', 
                    '/supplier/SupplierShipments', 
                    '/supplier/notificationsSup',
                    '/Supplier/SupplierSettings'
                ]
                if(!allowedPaths.includes(router.currentRoute.value.path)){
                    router.push('/supplier/SupplierDashboard')
                }
            } else {
                toast.success('Account Restored', { description: 'Your standard account access has been fully restored.' })
                showTerminationModal.value = false 
            }
        })
}

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
    supplierName.value = user.name || `${user.first_name} ${user.last_name}`
    userId.value = user.id

    checkAccountStatus()
    setupWebsockets()
  }
})

onUnmounted(() => {
  if (echo && userId.value) {
    echo.leave(`account.status.${userId.value}`)
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