<template>
  <Sidebar collapsible="icon" class="border-r border-indigo-900/20 bg-indigo-950 transition-all duration-500 ease-in-out">
    <SidebarHeader class="h-24 border-b border-indigo-900/50 flex flex-row items-center px-4 overflow-hidden bg-indigo-950">
      <div class="flex items-center gap-3 w-full">
        <div class="relative shrink-0 flex items-center justify-center">
          <Avatar class="w-10 h-10 ring-2 ring-indigo-500/30 ring-offset-2 ring-offset-indigo-950">
            <div class="w-full h-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center">
              <component :is="roleIcon" class="w-5 h-5 text-white" />
            </div>
          </Avatar>
        </div>
        
        <div v-if="state === 'expanded' || isMobile" class="flex flex-col min-w-0 nav-text-clip flex-1">
          <h2 class="text-sm font-bold text-slate-100 truncate tracking-tight">{{ userName }}</h2>
          <p class="text-[10px] font-semibold text-indigo-400/80 uppercase tracking-widest">{{ userRoleLabel }}</p>
        </div>
      </div>
    </SidebarHeader>

    <SidebarContent class="px-3 py-4 space-y-6 bg-indigo-950 overflow-x-hidden">
      <SidebarGroup class="p-0">
        <SidebarGroupLabel v-if="state === 'expanded' || isMobile" class="px-3 text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-2 nav-text-clip">
          {{ userRoleLabel.toUpperCase() }}
        </SidebarGroupLabel>
        
        <SidebarMenu>
          <SidebarMenuItem v-for="item in navItems" :key="item.id">
            <SidebarMenuButton 
              as-child 
              :tooltip="item.text" 
              class="h-11 w-full rounded-xl transition-all duration-300 text-slate-300 hover:text-white hover:bg-white/10 flex items-center"
              active-class="bg-gradient-to-r from-indigo-500/20 to-purple-500/10 !text-white ring-1 ring-indigo-500/30"
            >
              <router-link :to="item.route" class="flex items-center w-full px-2" @click="handleNavClick">
                <div class="shrink-0 flex items-center justify-center w-6 h-6">
                  <component :is="getIcon(item.id)" class="w-5 h-5" :class="getIconColor(item.id)" />
                </div>
                <span v-if="state === 'expanded' || isMobile" class="ml-3 text-sm font-medium nav-text-clip">{{ item.text }}</span>
                
                <Badge 
                  v-if="(state === 'expanded' || isMobile) && item.badge" 
                  variant="outline" 
                  class="ml-auto text-[9px] bg-indigo-900/50 text-indigo-300 border-indigo-700 font-bold px-1.5 h-4"
                >
                  {{ item.badge }}
                </Badge>
              </router-link>
            </SidebarMenuButton>
          </SidebarMenuItem>
        </SidebarMenu>
      </SidebarGroup>
    </SidebarContent>

    <SidebarFooter class="px-3 py-4 border-t border-indigo-900/50 bg-indigo-950 space-y-1">
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton 
            as-child
            tooltip="Sign Out" 
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
                {{ isLoggingOut ? 'Signing out...' : 'Sign Out' }}
              </span>
            </Button>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>

      <div v-if="!isMobile" class="flex justify-center pt-2">
        <SidebarTrigger class="text-indigo-400 hover:text-indigo-200 hover:bg-white/10 transition-colors" />
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
          <p class="text-slate-400 mt-2 text-center">Are you sure you want to sign out?</p>
          <div class="flex w-full gap-3 mt-8">
            <Button variant="outline" class="flex-1 rounded-xl border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-white" @click="showLogoutModal = false">Cancel</Button>
            <Button class="flex-1 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:opacity-90 transition-opacity" @click="confirmLogout" :disabled="isLoggingOut">
              <Loader2 v-if="isLoggingOut" class="mr-2 h-4 w-4 animate-spin" />
              {{ isLoggingOut ? 'Signing out...' : 'Yes, Sign Out' }}
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </Sidebar>
</template>

<script setup>
import { ref, onMounted, defineEmits, defineProps, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  LayoutDashboard, Clock, Banknote, CalendarDays, 
  FileText, User, Bell, LogOut, Loader2, UserCheck, 
  Briefcase, Calculator, Users 
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

// Define Props to receive user data
const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const { state, isMobile } = useSidebar()
const router = useRouter()
const emit = defineEmits(['logout-started', 'logout-finished', 'toggle'])

const isLoggingOut = ref(false)
const showLogoutModal = ref(false)
const userName = ref('Employee Name')

// Dynamic Role Label
const userRoleLabel = computed(() => {
  if (!props.user) return 'Employee Portal'
  const role = props.user.role
  if (role === 'hr_manager') return 'HR Manager'
  if (role === 'finance_manager') return 'Finance Manager'
  if (role === 'operational_distributor') return 'Operational Dist.'
  return 'Employee Portal'
})

// Dynamic Header Icon
const roleIcon = computed(() => {
  if (!props.user) return UserCheck
  const role = props.user.role
  if (role === 'hr_manager') return Users
  if (role === 'finance_manager') return Calculator
  if (role === 'operational_distributor') return Briefcase
  return UserCheck
})

// Update user name when props change
watch(() => props.user, (newUser) => {
  if (newUser) {
    userName.value = newUser.full_name || `${newUser.first_name} ${newUser.last_name}`
  }
}, { immediate: true })

const navItems = ref([
  { id: 'dashboard', text: 'Dashboard', route: '/Employees/DashboardEmployee' },
  { id: 'attendance', text: 'Attendance', route: '/Employees/AttendanceEmployee', badge: '2' },
  { id: 'payroll', text: 'Payroll', route: '/Employees/PayrollEmployee', badge: 'New' },
  { id: 'leaves', text: 'Leaves', route: '/Employees/LeavesEmployee', badge: '3' },
  { id: 'requests', text: 'Requests', route: '/Employees/RequestEmployee', badge: '5' },
  { id: 'my-profile', text: 'My Profile', route: '/Employees/ProfileEmployee' },
  { id: 'notifications', text: 'Notifications', route: '/Employees/NotificationEmployee', badge: '12' }
])

const getIcon = (id) => {
  const icons = {
    dashboard: LayoutDashboard,
    attendance: Clock,
    payroll: Banknote,
    leaves: CalendarDays,
    requests: FileText,
    'my-profile': User,
    notifications: Bell
  }
  return icons[id] || FileText
}

const getIconColor = (id) => {
  const colors = {
    dashboard: 'text-sky-400',
    attendance: 'text-emerald-400',
    payroll: 'text-amber-400',
    leaves: 'text-purple-400',
    requests: 'text-indigo-400',
    'my-profile': 'text-teal-400',
    notifications: 'text-rose-400'
  }
  return colors[id] || 'text-slate-400'
}

const handleNavClick = () => {
  if (isMobile.value) emit('toggle')
}

// Integrated Backend Logout Logic
const confirmLogout = async () => {
  isLoggingOut.value = true
  showLogoutModal.value = false
  
  // Emit start event to parent (EmployeeLayout) to trigger animation
  emit('logout-started')
  
  try {
    // Simulate API call delay for smoother UX
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // Call the actual backend logout endpoint
    const response = await api.post('/auth/logout')
    
    if (response.data.status === 'success') {
      emit('logout-finished')
      
      // Clear local storage and redirect
      setTimeout(() => {
        localStorage.clear()
        router.push('/Landing/logIn')
      }, 1000)
    }
  } catch (error) {
    console.error('Logout failed:', error)
    // Force logout on error
    emit('logout-finished')
    setTimeout(() => {
      localStorage.clear()
      router.push('/Landing/logIn')
    }, 1000)
  }
}

onMounted(() => {
  // Fallback if props aren't ready yet (double check localStorage)
  const data = localStorage.getItem('user_data')
  if (data) {
    const user = JSON.parse(data)
    userName.value = user.full_name || `${user.first_name} ${user.last_name}`
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