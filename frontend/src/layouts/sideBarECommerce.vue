<template>
  <Sidebar collapsible="icon" class="border-r border-slate-800/50 bg-slate-900 transition-all duration-500 ease-in-out">
    <SidebarHeader class="h-24 border-b border-slate-800/50 flex flex-row items-center px-4 overflow-hidden bg-slate-900">
      <div class="flex items-center gap-3 w-full">
        <div class="relative shrink-0 flex items-center justify-center">
          <Avatar class="w-10 h-10 ring-2 ring-indigo-500/30 ring-offset-2 ring-offset-slate-900">
            <div v-if="!isTerminated" class="w-full h-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center">
              <ShoppingBag class="w-5 h-5 text-white" />
            </div>
            <div v-else class="w-full h-full bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center">
              <AlertTriangle class="w-5 h-5 text-white" />
            </div>
          </Avatar>
        </div>
        
        <div v-if="state === 'expanded' || isMobile" class="flex flex-col min-w-0 nav-text-clip flex-1">
          <h2 class="text-sm font-bold text-slate-100 truncate tracking-tight">{{ userName }}</h2>
          <p v-if="!isTerminated" class="text-[10px] font-semibold text-indigo-400 uppercase tracking-widest">Operational Hub</p>
          <p v-else class="text-[10px] font-black text-red-400 uppercase tracking-widest">Restricted</p>
        </div>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3 py-4 space-y-6 bg-slate-900 overflow-x-hidden">
      <div v-if="isLoadingAccess" class="flex justify-center p-4">
        <Loader2 class="w-6 h-6 animate-spin text-indigo-400" />
      </div>

      <template v-else>
        <!-- Termination Alert Banner -->
        <div v-if="isTerminated && (state === 'expanded' || isMobile)" class="p-4 bg-red-500/10 border border-red-500/20 rounded-xl mb-4 transition-all">
          <div class="flex items-center gap-2 text-red-400 mb-2">
            <AlertTriangle class="w-4 h-4 shrink-0" />
            <span class="text-xs font-bold uppercase tracking-wider">Access Restricted</span>
          </div>
          <p class="text-[11px] text-slate-400 leading-relaxed mb-4">
            Operational access to this hub has been restricted due to a policy action.
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
      </template>
    </SidebarContent>

    <SidebarFooter class="px-3 py-4 border-t border-slate-800/50 bg-slate-900 space-y-1">
      <SidebarMenu>
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
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 shadow-2xl max-w-2xl sm:rounded-3xl p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex flex-col items-center text-center">
          
          <div class="w-24 h-24 rounded-full bg-red-500/10 flex items-center justify-center mb-6 relative">
            <div class="absolute inset-0 bg-red-500/20 rounded-full animate-ping opacity-75" />
            <AlertTriangle class="text-red-500 w-12 h-12 relative z-10" />
          </div>
          
          <DialogTitle class="text-3xl font-black text-white mb-2 tracking-tight">Account Restricted</DialogTitle>
          <p class="text-slate-400 mb-8 max-w-md">Your operational access to the E-Commerce Hub has been temporarily suspended or terminated.</p>
          
          <div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 w-full text-left space-y-6 shadow-inner">
            
            <div class="flex items-center justify-between border-b border-slate-700/50 pb-4">
              <div>
                <h4 class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Current Status</h4>
                <Badge variant="destructive" class="bg-red-500/10 text-red-400 border border-red-500/20 text-sm py-1 px-3">Restricted Access</Badge>
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

          <div class="mt-8 bg-indigo-500/10 border border-indigo-500/20 rounded-2xl p-5 text-left w-full flex items-start gap-4">
             <div class="mt-1 shrink-0 p-2 bg-indigo-500/20 rounded-full text-indigo-400">
               <Users class="w-5 h-5" />
             </div>
             <div>
                <h4 class="text-sm font-bold text-indigo-100 mb-1">Need Clarification?</h4>
                <p class="text-xs text-indigo-200/70 leading-relaxed">
                  If you believe this is an administrative error or require further instructions, kindly contact your <strong class="text-white">Main Distributor</strong> regarding this restriction. Operations remain locked until the dispute is resolved.
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
import { ref, computed, onMounted, onUnmounted, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import echo from '@/utils/websocket'
import { 
  LayoutDashboard, ShoppingBag, Box, Tag, ClipboardList, CreditCard, 
  Truck, Undo2, Star, Percent, BarChart3, LogOut, Loader2, Handshake,
  UserCircle, PackageSearch, Briefcase, PackageCheck, Boxes, MessageCircle,
  CheckCircle, Car, AlertTriangle, Users, Bug
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
const showTerminationModal = ref(false)
const userName = ref('Distributor')
const userId = ref(null)
const parentId = ref(null) // Holds Main Distributor ID

// Navigation Roles & Termination State
const isLoadingAccess = ref(true)
const hasFullAccess = ref(false)
const accessKeys = ref([])
const isTerminated = ref(false)
const terminationDetails = ref(null)

// Ensure IDs match `permission_key` from position_accessibilities exactly
const navigation = [
  {
    title: 'Overview',
    items: [
      { id: 'ec_dashboard', name: 'Dashboard', path: '/ECommerce/ECDashboard', icon: LayoutDashboard, color: 'text-indigo-400', badge: 'Live' }
    ]
  },
  {
    title: 'Catalog & Inventory',
    items: [
      { id: 'ec_procurement', name: 'Procurement', path: '/ECommerce/ECProcurement', icon: PackageSearch, color: 'text-blue-400', badge: 'Req' },
      { id: 'ec_categories', name: 'Categories', path: '/ECommerce/ECCategories', icon: Tag, color: 'text-emerald-400' },
      { id: 'ec_process_procurement', name: 'Process Request', path: '/ECommerce/ECProcessProcurement', icon: ClipboardList, color: 'text-emerald-400', badge: 'Head' },
      { id: 'ec_track_procurement', name: 'Track Procurement', path: '/ECommerce/ECPTrackProcurement', icon: ClipboardList, color: 'text-emerald-400' },
      { id: 'ec_arrived_item', name: 'Arrived Item', path: '/ECommerce/ECArrivedItem', icon: PackageCheck, color: 'text-green-400' },
      { id: 'ec_inventory', name: 'Inventory', path: '/ECommerce/ECInventory', icon: Boxes, color: 'text-purple-400' },
    ]
  },
  {
    title: 'Sales Operations',
    items: [
      { id: 'ec_orders', name: 'Orders', path: '/ECommerce/ECOrders', icon: ClipboardList, color: 'text-amber-400' },
      { id: 'ec_prepare_order', name: 'Prepare Order', path: '/ECommerce/ECPrepareOrder', icon: PackageCheck, color: 'text-blue-400' },
      { id: 'ec_payment', name: 'Payments', path: '/ECommerce/ECPayment', icon: CreditCard, color: 'text-green-400' },
      { id: 'ec_delivery', name: 'Delivery', path: '/ECommerce/ECDelivery', icon: Truck, color: 'text-cyan-400' },
      { id: 'ec_vehicles', name: 'Vehicles', path: '/ECommerce/ECVehicles', icon: Car, color: 'text-teal-400' },
      { id: 'ec_returns', name: 'Returns', path: '/ECommerce/ECReturns', icon: Undo2, color: 'text-red-400' },
      { id: 'ec_promo_approval', name: 'Promo Approval', path: '/ECommerce/ECPromoApproval', badge: "head", icon: CheckCircle, color: 'text-purple-400' }
    ]
  },
  {
    title: 'Network',
    items: [
      { id: 'ec_partner_supplier', name: 'Partner Supplier', path: '/ECommerce/ECPartnerSupplier', icon: Handshake, color: 'text-indigo-400', badge: 'New' },
      { id: 'ec_service_provider', name: 'Service Provider', path: '/ECommerce/ECServiceProvider', icon: Briefcase, color: 'text-green-400' }
    ]
  },
  {
    title: 'Analytics & UX',
    items: [
      { id: 'ec_reviews', name: 'Reviews', path: '/ECommerce/ECReviews', icon: Star, color: 'text-violet-400' },
      { id: 'ec_promotions', name: 'Promotions', path: '/ECommerce/ECPromotions', icon: Percent, color: 'text-orange-400' },
      { id: 'ec_reports', name: 'Reports', path: '/ECommerce/ECreports', icon: BarChart3, color: 'text-sky-400' },
      { id: 'ec_messages', name: 'Messages', path: '/ECommerce/ECMessages', icon: MessageCircle, color: 'text-green-400', badge: 'new' },
      { name: 'Technical Reports', path: '/ECommerce/TechnicalReportsOpe', icon: Bug, color: 'text-orange-400'},
    ]
  }
]

// Computed property to seamlessly lock down menus when terminated, or use standard RBAC logic.
const filteredNavigation = computed(() => {
  let navs = navigation;

  // STRICT LOCKDOWN: If terminated, return ONLY Dashboard, Orders, and Prepare Order
  if (isTerminated.value) {
    return navs.map(section => {
      const allowedItems = section.items.filter(item => 
        ['ec_dashboard', 'ec_orders', 'ec_prepare_order'].includes(item.id)
      );
      if (allowedItems.length === 0) return null;
      return {
        ...section,
        items: allowedItems
      };
    }).filter(Boolean);
  }

  // STANDARD RBAC FILTERING
  if (!hasFullAccess.value) {
    return navs.map(section => {
      return {
        ...section,
        items: section.items.filter(item => accessKeys.value.includes(item.id))
      }
    }).filter(section => section.items.length > 0)
  }

  return navs;
})

const fetchSidebarAccess = async () => {
  try {
    const response = await api.get('/operation-distributor/sidebar-access')
    if (response.data.status === 'success') {
      hasFullAccess.value = response.data.has_full_access
      accessKeys.value = response.data.access_keys || []
    }
  } catch (error) {
    console.error('Failed to fetch sidebar access rules:', error)
  } finally {
    isLoadingAccess.value = false
  }
}

const checkAccountStatus = async () => {
    try {
        const res = await api.get('/operation-distributor/account-status')
        if (res.data.success) {
            isTerminated.value = res.data.is_terminated
            terminationDetails.value = res.data.termination_details || null
            parentId.value = res.data.parent_id || null

            if(isTerminated.value) {
                const allowedPaths = ['/ECommerce/ECDashboard', '/ECommerce/ECOrders', '/ECommerce/ECPrepareOrder'];
                if(!allowedPaths.includes(router.currentRoute.value.path)) {
                    router.push('/ECommerce/ECDashboard')
                }
            }
        }
    } catch (e) {
        console.error("Status Check Error:", e)
    }
}

const handleStatusUpdate = (e) => {
    isTerminated.value = e.status === 'terminated'
    terminationDetails.value = e.terminationData || null
    
    if (isTerminated.value) {
        toast.error('Account Restricted', { description: 'Operational capabilities have been suspended.' })
        const allowedPaths = ['/ECommerce/ECDashboard', '/ECommerce/ECOrders', '/ECommerce/ECPrepareOrder'];
        if(!allowedPaths.includes(router.currentRoute.value.path)) {
            router.push('/ECommerce/ECDashboard')
        }
    } else {
        toast.success('Account Restored', { description: 'Standard operational access has been granted.' })
        showTerminationModal.value = false
    }
}

const setupWebsockets = () => {
    // Listen to personal operational account status
    if (userId.value) {
        echo.private(`account.status.${userId.value}`)
            .listen('.AccountStatusUpdated', handleStatusUpdate)
    }

    // Listen to Main Distributor's (Parent) account status
    if (parentId.value) {
        echo.private(`account.status.${parentId.value}`)
            .listen('.AccountStatusUpdated', handleStatusUpdate)
    }
}

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

onMounted(async () => {
  const data = localStorage.getItem('user_data')
  if (data) {
    const user = JSON.parse(data)
    userName.value = user.name || `${user.first_name} ${user.last_name}`
    userId.value = user.id
  }

  // Fetch access & check status concurrently
  await Promise.all([
    fetchSidebarAccess(),
    checkAccountStatus()
  ])
  
  // Establish socket listeners after checking initial status
  setupWebsockets()
})

onUnmounted(() => {
  if (echo && userId.value) echo.leave(`account.status.${userId.value}`);
  if (echo && parentId.value) echo.leave(`account.status.${parentId.value}`);
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