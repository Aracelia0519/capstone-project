<template>
  <Sidebar collapsible="icon" class="border-r border-slate-200 bg-slate-900 transition-all duration-500 ease-in-out">
    <SidebarHeader class="h-24 border-b border-slate-800/50 flex flex-row items-center px-4 overflow-hidden bg-slate-900">
      <div class="flex items-center gap-3 w-full">
        <div class="relative shrink-0 flex items-center justify-center">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-emerald-500 flex items-center justify-center shadow-lg">
            <Users class="w-5 h-5 text-white" />
          </div>
        </div>
        <div v-if="state === 'expanded' || isMobile" class="flex flex-col min-w-0 flex-1">
          <h2 class="text-sm font-bold text-slate-100 truncate tracking-tight">Human Resource</h2>
          <p class="text-[10px] font-semibold text-emerald-400/80 uppercase tracking-widest">Portal Access</p>
        </div>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3 py-4 space-y-6 bg-slate-900 overflow-x-hidden">
      <SidebarGroup class="p-0">
        <SidebarGroupLabel v-if="state === 'expanded' || isMobile" class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2">
          Management
        </SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem v-for="item in accessibleHrNavItems" :key="item.id">
            <SidebarMenuButton 
              as-child 
              :tooltip="item.text"
              :disabled="!item.enabled"
              class="h-11 w-full rounded-xl transition-all duration-300 text-white/70 hover:text-white hover:bg-slate-800/50"
              active-class="bg-gradient-to-r from-blue-500/20 to-emerald-500/10 !text-white ring-1 ring-blue-500/30"
            >
              <router-link :to="item.enabled ? item.route : '#'" class="flex items-center w-full px-2" @click="handleNavClick">
                <div class="shrink-0 flex items-center justify-center w-6 h-6">
                  <component :is="getIcon(item.id)" class="w-5 h-5" :class="item.enabled ? 'text-blue-400' : 'text-slate-600'" />
                </div>
                <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium">{{ item.text }}</span>
                <Lock v-if="(state === 'expanded' || isMobile) && !item.enabled" class="ml-auto w-3 h-3 text-slate-600" />
              </router-link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>

      <SidebarGroup v-if="accessibleToolsNavItems.length > 0" class="p-0">
        <SidebarGroupLabel v-if="state === 'expanded' || isMobile" class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2">
          Tools
        </SidebarGroupLabel>
        <SidebarMenu>
          <SidebarMenuItem v-for="item in accessibleToolsNavItems" :key="item.id">
            <SidebarMenuButton as-child :tooltip="item.text" class="h-11 w-full rounded-xl text-white/70 hover:text-white hover:bg-slate-800/50">
              <router-link :to="item.route" class="flex items-center w-full px-2" @click="handleNavClick">
                <div class="shrink-0 flex items-center justify-center w-6 h-6">
                  <component :is="getIcon(item.id)" class="w-5 h-5 text-emerald-400" />
                </div>
                <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium">{{ item.text }}</span>
              </router-link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter class="px-3 py-4 border-t border-slate-800/50 bg-slate-900 space-y-1">
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton 
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
              <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium">
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
          <DialogTitle class="text-2xl font-black bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">End Session?</DialogTitle>
          <p class="text-slate-400 mt-2 text-center">Are you sure you want to logout?</p>
          <div class="flex w-full gap-3 mt-8">
            <Button variant="outline" class="flex-1 rounded-xl border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-white" @click="showLogoutModal = false">Cancel</Button>
            <Button class="flex-1 rounded-xl bg-gradient-to-r from-blue-500 to-emerald-500 hover:opacity-90 transition-opacity" @click="confirmLogout" :disabled="isLoggingOut">
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
import { ref, onMounted, computed, defineEmits } from 'vue'
import { useRouter } from 'vue-router'
import { 
  LayoutDashboard, Users, ShieldCheck, Building2, ClipboardCheck, 
  FileStack, Banknote, BarChart3, CalendarCheck, Settings, LogOut, Loader2, Lock 
} from 'lucide-vue-next'
import { 
  Sidebar, SidebarHeader, SidebarContent, SidebarFooter, 
  SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuItem, 
  SidebarMenuButton, SidebarTrigger, useSidebar 
} from '@/components/ui/sidebar'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import api from '@/utils/axios'

const { state, isMobile } = useSidebar()
const router = useRouter()
const emit = defineEmits(['logout-started', 'logout-finished', 'toggle'])

const isLoggingOut = ref(false)
const showLogoutModal = ref(false)
const userRole = ref('')
const userAccessibility = ref([])

const iconMap = {
  dashboard: LayoutDashboard,
  employees: Users,
  positions: ShieldCheck,
  departments: Building2,
  status: ClipboardCheck,
  recruitment: FileStack,
  payroll: Banknote,
  reports: BarChart3,
  settings: Settings,
  attendanceRequest: CalendarCheck
}
const getIcon = (id) => iconMap[id] || Settings

const hrNavItems = ref([
  { id: 'dashboard', text: 'Dashboard', route: '/HR/HRdashboard', permissionKey: 'dashboard' },
  { id: 'employees', text: 'Employee List', route: '/HR/employeesListHR', permissionKey: 'employee_list' },
  { id: 'positions', text: 'Positions & Roles', route: '/HR/positionRolesHR', permissionKey: 'positions_roles' },
  { id: 'departments', text: 'Departments', route: '/HR/departmentsHR', permissionKey: 'departments' },
  { id: 'status', text: 'Employment Status', route: '/HR/employmentStatusHR', permissionKey: 'employment_status' },
  { id: 'recruitment', text: 'Recruitment Application', route: '/HR/recruitmentApplication', permissionKey: 'recruitment' },
  { id: 'payroll', text: 'Payroll Management', route: '/HR/payrollHR', permissionKey: 'payroll_management' },
  { id: 'attendanceRequest', text: 'attendanceRequest', route: '/HR/attendanceRequestHR', permissionKey: 'attendance_request' }, 
])

const toolsNavItems = ref([
  { id: 'reports', text: 'HR Reports', route: '/HR/reportsHR', permissionKey: 'reports' },
  { id: 'settings', text: 'HR Settings', route: '/HR/settings', permissionKey: 'settings' },
])

const isItemAccessible = (permissionKey) => {
  if (userRole.value === 'hr_manager') return true
  return userAccessibility.value.includes(permissionKey)
}

const accessibleHrNavItems = computed(() => {
  return hrNavItems.value.map(item => ({ ...item, enabled: isItemAccessible(item.permissionKey) }))
})

const accessibleToolsNavItems = computed(() => {
  return toolsNavItems.value.filter(item => userRole.value === 'hr_manager' || isItemAccessible(item.permissionKey))
})

const handleNavClick = () => { if (isMobile.value) emit('toggle') }

const confirmLogout = async () => {
  isLoggingOut.value = true
  showLogoutModal.value = false
  emit('logout-started')
  
  try {
    await new Promise(resolve => setTimeout(resolve, 1500))
    const response = await api.post('/auth/logout')
    if (response.data.status === 'success') {
      emit('logout-finished')
      setTimeout(() => {
        localStorage.clear()
        router.push('/Landing/logIn')
      }, 1000)
    }
  } catch (error) {
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
    userRole.value = localStorage.getItem('user_role') || user.role
    userAccessibility.value = user.employee_data?.accessibility_keys || []
  }
})
</script>