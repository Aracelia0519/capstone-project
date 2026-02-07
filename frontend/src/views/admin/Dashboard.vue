<template>
  <div class="relative min-h-screen w-full overflow-hidden bg-white p-4 md:p-8 font-sans">
    
    <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-blue-500/5 blur-[80px]"></div>
    <div class="pointer-events-none absolute -bottom-32 -left-32 h-[500px] w-[500px] rounded-full bg-purple-500/5 blur-[80px]"></div>

    <div class="relative z-10 mx-auto w-full max-w-[1400px] space-y-8">
      
      <header class="flex flex-col gap-4 rounded-xl bg-white p-5 border shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:gap-4">
          <div class="flex items-center gap-3">
            <i class="fas fa-tachometer-alt text-2xl text-[#4A90E2]"></i>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800">System Dashboard</h1>
          </div>
          <div v-if="isMobile" class="flex items-center gap-2 rounded-lg border bg-slate-50 px-3 py-1.5 text-sm text-slate-600">
            <i class="far fa-calendar-alt text-[#4A90E2]"></i> {{ shortDate }}
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <Button 
            @click="refreshData" 
            class="bg-gradient-to-r from-[#4A90E2] to-[#357ABD] text-white hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
          >
            <i class="fas fa-sync-alt mr-2"></i>
            <span class="hidden sm:inline">Refresh</span>
          </Button>
          
          <div v-if="!isMobile" class="flex items-center gap-2 rounded-md border bg-white px-5 py-2.5 text-sm font-medium text-slate-600 shadow-sm">
            <i class="far fa-calendar-alt text-[#4A90E2]"></i> {{ currentDate }}
          </div>
        </div>
      </header>

      <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
        
        <Card class="border-l-4 border-l-[#4A90E2] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg bg-white border-t border-r border-b">
          <div class="p-6 flex gap-5">
            <div class="flex h-[70px] w-[70px] shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#4A90E2] to-[#357ABD] text-white shadow-md">
              <i class="fas fa-users text-2xl"></i>
            </div>
            <div class="w-full space-y-2">
              <h3 class="text-sm font-semibold text-slate-500">Total Users</h3>
              <div class="text-4xl font-bold text-slate-800">{{ stats.totalUsers }}</div>
              <div class="flex flex-wrap gap-2 pt-1">
                <Badge variant="secondary" class="bg-blue-50 text-blue-700 hover:bg-blue-100">Admins: {{ stats.usersByRole.admin }}</Badge>
                <Badge variant="secondary" class="bg-green-50 text-green-700 hover:bg-green-100">Dist: {{ stats.usersByRole.distributor }}</Badge>
                <Badge variant="secondary" class="bg-orange-50 text-orange-700 hover:bg-orange-100">Service: {{ stats.usersByRole.serviceProvider }}</Badge>
                <Badge variant="secondary" class="bg-purple-50 text-purple-700 hover:bg-purple-100">Clients: {{ stats.usersByRole.client }}</Badge>
              </div>
            </div>
          </div>
        </Card>

        <Card class="border-l-4 border-l-[#51C16B] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg bg-white border-t border-r border-b">
          <div class="p-6 flex gap-5">
            <div class="flex h-[70px] w-[70px] shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#51C16B] to-[#3DA857] text-white shadow-md">
              <i class="fas fa-truck text-2xl"></i>
            </div>
            <div class="w-full space-y-1">
              <h3 class="text-sm font-semibold text-slate-500">Active Distributors</h3>
              <div class="text-4xl font-bold text-slate-800">{{ stats.totalDistributors }}</div>
              <div class="text-sm font-medium text-[#51C16B] flex items-center gap-1">
                <i class="fas fa-arrow-up text-xs"></i> 12% this month
              </div>
              <div class="flex items-end gap-1 h-5 mt-3">
                <div class="w-full rounded-sm bg-[#51C16B]/70 h-[70%]"></div>
                <div class="w-full rounded-sm bg-[#51C16B]/70 h-[85%]"></div>
                <div class="w-full rounded-sm bg-[#51C16B]/70 h-[60%]"></div>
              </div>
            </div>
          </div>
        </Card>

        <Card class="border-l-4 border-l-[#FF6B6B] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg bg-white border-t border-r border-b">
          <div class="p-6 flex gap-5">
            <div class="flex h-[70px] w-[70px] shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#FF6B6B] to-[#E05555] text-white shadow-md">
              <i class="fas fa-tools text-2xl"></i>
            </div>
            <div class="w-full space-y-1">
              <h3 class="text-sm font-semibold text-slate-500">Service Providers</h3>
              <div class="text-4xl font-bold text-slate-800">{{ stats.totalServiceProviders }}</div>
              <p class="text-sm text-slate-500"><span class="font-bold text-[#FF6B6B]">{{ stats.activeServiceProviders }} Active</span></p>
              <div class="space-y-1 pt-2">
                <Progress :model-value="stats.serviceProviderActivity" class="h-2 bg-slate-100" indicator-class="bg-[#FF6B6B]" />
                <div class="flex justify-end text-xs text-slate-400">Activity: {{ stats.serviceProviderActivity }}%</div>
              </div>
            </div>
          </div>
        </Card>

        <Card class="border-l-4 border-l-[#9B59B6] shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg bg-white border-t border-r border-b">
          <div class="p-6 flex gap-5">
            <div class="flex h-[70px] w-[70px] shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-[#9B59B6] to-[#8E44AD] text-white shadow-md">
              <i class="fas fa-palette text-2xl"></i>
            </div>
            <div class="w-full space-y-1">
              <h3 class="text-sm font-semibold text-slate-500">Color Mixes</h3>
              <div class="text-4xl font-bold text-slate-800">{{ stats.totalColorCustomizations }}</div>
              <p class="text-sm text-slate-400">From Unity System</p>
              
              <div class="flex items-center -space-x-2 pt-3">
                <TooltipProvider v-for="color in recentColors" :key="color.id">
                  <Tooltip>
                    <TooltipTrigger>
                      <div 
                        class="h-7 w-7 rounded-full border-2 border-white shadow-sm transition-transform hover:scale-110 hover:z-10 cursor-help"
                        :style="{ backgroundColor: color.hex }"
                      ></div>
                    </TooltipTrigger>
                    <TooltipContent>
                      <p>{{ color.name }}</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                <span class="pl-4 text-sm text-slate-500 font-medium">+{{ recentColors.length }} more</span>
              </div>
            </div>
          </div>
        </Card>
      </div>

      <Card class="overflow-hidden border shadow-sm bg-white">
        <div class="flex flex-wrap items-center justify-between p-6 gap-4">
          <div class="flex items-center gap-2">
            <i class="fas fa-history text-[#4A90E2] text-xl"></i>
            <h2 class="text-xl font-bold text-slate-800">Recent Activity</h2>
          </div>
          <Button variant="outline" class="border-[#4A90E2] text-[#4A90E2] hover:bg-[#4A90E2] hover:text-white transition-colors" @click="viewAllActivity">
            View All <i class="fas fa-arrow-right ml-2"></i>
          </Button>
        </div>
        
        <div class="overflow-x-auto">
          <Table>
            <TableHeader class="bg-slate-50">
              <TableRow>
                <TableHead class="w-[250px]">User</TableHead>
                <TableHead>Action</TableHead>
                <TableHead>Details</TableHead>
                <TableHead>Time</TableHead>
                <TableHead>Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="activity in recentActivities" :key="activity.id" class="hover:bg-slate-50/50">
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-[#4A90E2] to-[#357ABD] text-white">
                      <i :class="activity.userIcon"></i>
                    </div>
                    <div class="flex flex-col">
                      <span class="font-semibold text-slate-800">{{ activity.userName }}</span>
                      <span class="text-xs text-slate-500">{{ activity.userRole }}</span>
                    </div>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge :class="getActionBadgeClass(activity.actionType)" variant="secondary" class="font-medium rounded-full px-3 py-1">
                    <i :class="activity.actionIcon" class="mr-1.5 text-xs"></i> {{ activity.action }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-2">
                    <span class="truncate max-w-[200px] text-slate-600">{{ activity.details }}</span>
                    <div v-if="activity.color" class="h-5 w-5 shrink-0 rounded border border-white shadow-sm" :style="{ backgroundColor: activity.color }"></div>
                  </div>
                </TableCell>
                <TableCell class="text-slate-500 whitespace-nowrap">
                  <i class="far fa-clock mr-1"></i> {{ activity.time }}
                </TableCell>
                <TableCell>
                  <Badge :variant="activity.status === 'completed' ? 'default' : activity.status === 'pending' ? 'secondary' : 'destructive'" 
                         :class="getStatusBadgeClass(activity.status)">
                    {{ activity.status }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </Card>

      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 pb-8">
        <Card v-for="(stat, index) in quickStats" :key="index" class="hover:-translate-y-1 hover:shadow-md transition-all duration-300 bg-white border">
          <div class="p-5 flex items-center gap-4">
            <div :class="`flex h-[60px] w-[60px] shrink-0 items-center justify-center rounded-xl text-white text-xl shadow-sm ${stat.gradient}`">
              <i :class="stat.icon"></i>
            </div>
            <div>
              <h4 class="text-sm font-semibold text-slate-700">{{ stat.title }}</h4>
              <p :class="`text-lg font-bold ${stat.textColor}`">{{ stat.value }}</p>
              <div v-if="stat.isHealth" class="flex items-center gap-2 mt-1">
                 <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-[#51C16B]"></span>
                </span>
                <span class="text-xs text-slate-500">All Systems Operational</span>
              </div>
              <p v-else class="text-xs text-slate-400">{{ stat.subtext }}</p>
            </div>
          </div>
        </Card>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Progress } from '@/components/ui/progress';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

// Reactive State
const isMobile = ref(false);
const currentDate = ref(new Date().toLocaleDateString('en-US', { 
  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
}));
const shortDate = ref(new Date().toLocaleDateString('en-US', { 
  month: 'short', day: 'numeric', year: 'numeric'
}));

const stats = ref({
  totalUsers: 142,
  usersByRole: { admin: 3, distributor: 12, serviceProvider: 28, client: 99 },
  totalDistributors: 12,
  totalServiceProviders: 28,
  activeServiceProviders: 22,
  serviceProviderActivity: 78,
  totalColorCustomizations: 347,
  dailyColorMixes: 24,
  lowStockItems: 8,
  pendingRequests: 15
});

const recentColors = ref([
  { id: 1, name: 'Ocean Blue', hex: '#4A90E2' },
  { id: 2, name: 'Sunset Orange', hex: '#FF6B6B' },
  { id: 3, name: 'Meadow Green', hex: '#51C16B' },
  { id: 4, name: 'Lavender', hex: '#9B59B6' },
  { id: 5, name: 'Sunshine Yellow', hex: '#F1C40F' }
]);

const recentActivities = ref([
  { id: 1, userName: 'Maria Santos', userRole: 'Service Provider', userIcon: 'fas fa-user-tie', action: 'Color Mix', actionIcon: 'fas fa-palette', actionType: 'color-mix', details: 'Created "Ocean Breeze" color palette', color: '#4A90E2', time: '2 minutes ago', status: 'completed' },
  { id: 2, userName: 'Juan Dela Cruz', userRole: 'Distributor', userIcon: 'fas fa-truck', action: 'Inventory Update', actionIcon: 'fas fa-boxes', actionType: 'inventory', details: 'Added 50 units of Premium White', time: '15 minutes ago', status: 'completed' },
  { id: 3, userName: 'System Admin', userRole: 'Administrator', userIcon: 'fas fa-user-shield', action: 'User Added', actionIcon: 'fas fa-user-plus', actionType: 'user', details: 'Added new service provider', time: '1 hour ago', status: 'completed' },
  { id: 4, userName: 'Anna Reyes', userRole: 'Client', userIcon: 'fas fa-user', action: 'Color Request', actionIcon: 'fas fa-paint-brush', actionType: 'request', details: 'Requested custom color consultation', time: '2 hours ago', status: 'pending' },
  { id: 5, userName: 'Carlos Lim', userRole: 'Service Provider', userIcon: 'fas fa-user-tie', action: 'Job Completed', actionIcon: 'fas fa-check-circle', actionType: 'service', details: 'Completed paint job for Client #234', time: '3 hours ago', status: 'completed' }
]);

// Computed Quick Stats configuration to keep template clean
const quickStats = computed(() => [
  { 
    title: 'Daily Color Mixes', 
    value: stats.value.dailyColorMixes, 
    subtext: 'today',
    icon: 'fas fa-chart-line', 
    gradient: 'bg-gradient-to-br from-[#FF8E53] to-[#FF6B6B]',
    textColor: 'text-[#4A90E2]'
  },
  { 
    title: 'Low Stock Items', 
    value: stats.value.lowStockItems, 
    subtext: 'products',
    icon: 'fas fa-box-open', 
    gradient: 'bg-gradient-to-br from-[#4A90E2] to-[#357ABD]',
    textColor: 'text-[#4A90E2]'
  },
  { 
    title: 'Pending Requests', 
    value: stats.value.pendingRequests, 
    subtext: 'awaiting action',
    icon: 'fas fa-bell', 
    gradient: 'bg-gradient-to-br from-[#51C16B] to-[#3DA857]',
    textColor: 'text-[#4A90E2]'
  },
  { 
    title: 'System Health', 
    value: '', 
    isHealth: true,
    icon: 'fas fa-cog', 
    gradient: 'bg-gradient-to-br from-[#9B59B6] to-[#8E44AD]',
    textColor: 'text-[#4A90E2]'
  }
]);

// Methods
const refreshData = () => {
  stats.value.totalColorCustomizations += 1;
  stats.value.dailyColorMixes += 1;
  
  const newActivity = {
    id: Date.now(),
    userName: 'System',
    userRole: 'Auto',
    userIcon: 'fas fa-robot',
    action: 'System Update',
    actionIcon: 'fas fa-sync-alt',
    actionType: 'system',
    details: 'Dashboard data refreshed',
    time: 'Just now',
    status: 'completed'
  };
  
  recentActivities.value.unshift(newActivity);
  // Toast would go here (requires shadcn toast component setup)
  console.log('Dashboard data refreshed successfully!');
};

const viewAllActivity = () => {
  alert('Redirecting to full activity log...');
};

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
};

// Helper methods for styling
const getActionBadgeClass = (type) => {
  const map = {
    'color-mix': 'bg-purple-50 text-purple-700 hover:bg-purple-100',
    'inventory': 'bg-green-50 text-green-700 hover:bg-green-100',
    'user': 'bg-blue-50 text-blue-700 hover:bg-blue-100',
    'request': 'bg-orange-50 text-orange-700 hover:bg-orange-100',
    'service': 'bg-green-50 text-green-700 hover:bg-green-100',
    'system': 'bg-slate-100 text-slate-600 hover:bg-slate-200'
  };
  return map[type] || 'bg-slate-100';
};

const getStatusBadgeClass = (status) => {
  if (status === 'completed') return 'bg-[#E8F5E9] text-[#51C16B] hover:bg-green-100 border-none';
  if (status === 'pending') return 'bg-[#FFF3E0] text-[#FF8E53] hover:bg-orange-100 border-none';
  return 'bg-[#FFEBEE] text-[#F44336] hover:bg-red-100 border-none';
};

// Lifecycle
onMounted(() => {
  checkMobile();
  window.addEventListener('resize', checkMobile);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkMobile);
});
</script>

<style scoped>
/* Only necessary style not handled by Tailwind */
.relative::-webkit-scrollbar {
  display: none;
}
.relative {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>