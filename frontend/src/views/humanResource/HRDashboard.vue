<template>
  <div class="hr-dashboard p-4 md:p-6 overflow-x-hidden  min-h-screen">
    <div class="mb-6 md:mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">HR Dashboard</h1>
      <p class="text-gray-500 text-sm md:text-base">Overview of your organization's human resources</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <Card v-for="stat in computedStats" :key="stat.title" class="bg-white border border-gray-200 shadow-sm">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">{{ stat.title }}</p>
              <h3 class="text-2xl font-bold text-gray-900 mt-2">{{ stat.value }}</h3>
            </div>
            <div :class="['p-3 rounded-xl', stat.bg]">
              <component :is="stat.icon" class="w-6 h-6" :class="stat.color" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <Card class="bg-white border border-gray-200 shadow-sm lg:col-span-1">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Today's Attendance</h3>
          
          <div class="relative h-48 w-full flex items-center justify-center mb-6">
            <div class="w-40 h-40 rounded-full border-[16px] border-gray-100 relative">
              <div v-for="(item, index) in attendanceData" :key="item.name"
                   class="absolute inset-0 rounded-full border-[16px] border-transparent"
                   :style="getDonutSegmentStyle(index)">
              </div>
              <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-2xl font-bold text-gray-900">{{ totalAttendance }}</span>
                <span class="text-xs text-gray-500">Total</span>
              </div>
            </div>
          </div>

          <div class="space-y-3">
            <div v-for="item in attendanceData" :key="item.name" class="flex items-center justify-between">
              <div class="flex items-center">
                <div :class="['w-3 h-3 rounded-full mr-3', item.color]"></div>
                <span class="text-sm text-gray-600">{{ item.name }}</span>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ item.value }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-white border border-gray-200 shadow-sm lg:col-span-2">
        <CardContent class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Department Distribution</h3>
            <Button variant="ghost" size="sm" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50">View Details</Button>
          </div>
          
          <div class="space-y-4 h-[280px] overflow-y-auto pr-2">
            <div v-if="departmentData.length === 0" class="text-center text-gray-500 py-10">No department data found</div>
            
            <div v-for="dept in departmentData" :key="dept.name" class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-700">{{ dept.name }}</span>
                <span class="text-gray-500">{{ dept.count }} Employees</span>
              </div>
              <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500" 
                     :class="dept.color"
                     :style="{ width: `${(dept.count / maxDeptCount) * 100}%` }">
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <Card class="bg-white border border-gray-200 shadow-sm">
        <CardContent class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
            <Button variant="ghost" size="sm" class="text-gray-500 hover:text-gray-900">View All</Button>
          </div>
          
          <div v-if="recentActivities.length === 0" class="text-center text-gray-500 py-10">No recent activity</div>

          <div class="space-y-6">
            <div v-for="activity in recentActivities" :key="activity.id" class="flex">
              <div class="flex-shrink-0 mr-4">
                <div :class="['w-10 h-10 rounded-full flex items-center justify-center', activity.iconBg]">
                  <component :is="getIconComponent(activity.iconType)" class="w-5 h-5" />
                </div>
              </div>
              <div class="flex-1 pb-6 border-b border-gray-100 last:border-0 last:pb-0">
                <p class="text-sm font-medium text-gray-900 mb-1">{{ activity.action }}</p>
                <p class="text-sm text-gray-600">{{ activity.details }}</p>
                <p class="text-xs text-gray-400 mt-2">{{ activity.time }}</p>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-white border border-gray-200 shadow-sm flex items-center justify-center min-h-[300px]">
        <CardContent class="text-center text-gray-400">
           <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
           </svg>
           <p>Additional HR Metrics Module Space</p>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { 
  UsersIcon, 
  UserCheckIcon, 
  CalendarOffIcon, 
  UserPlusIcon,
  CalendarIcon
} from 'lucide-vue-next'

const rawStats = ref({
  totalEmployees: 0,
  activeEmployees: 0,
  onLeave: 0,
  newHires: 0
})

const attendanceData = ref([])
const departmentData = ref([])
const recentActivities = ref([])

// Adjusted colors for light theme visibility
const computedStats = computed(() => [
  { title: 'Total Employees', value: rawStats.value.totalEmployees, icon: UsersIcon, color: 'text-indigo-600', bg: 'bg-indigo-100' },
  { title: 'Active Employees', value: rawStats.value.activeEmployees, icon: UserCheckIcon, color: 'text-emerald-600', bg: 'bg-emerald-100' },
  { title: 'On Leave', value: rawStats.value.onLeave, icon: CalendarOffIcon, color: 'text-amber-600', bg: 'bg-amber-100' },
  { title: 'New Hires', value: rawStats.value.newHires, icon: UserPlusIcon, color: 'text-blue-600', bg: 'bg-blue-100' }
])

const totalAttendance = computed(() => {
  return attendanceData.value.reduce((acc, curr) => acc + curr.value, 0)
})

const maxDeptCount = computed(() => {
  if (departmentData.value.length === 0) return 1
  return Math.max(...departmentData.value.map(d => d.count))
})

const fetchDashboardData = async () => {
  try {
    const response = await api.get('/hr/dashboard')
    
    if (response.data.success) {
      rawStats.value = response.data.stats
      attendanceData.value = response.data.attendanceData
      departmentData.value = response.data.departmentData
      recentActivities.value = response.data.recentActivities
    }
  } catch (error) {
    console.error("Failed to load HR Dashboard data", error)
  }
}

const getIconComponent = (iconType) => {
  const map = {
    'user-plus': UserPlusIcon,
    'calendar': CalendarIcon,
  }
  return map[iconType] || UsersIcon
}

const getDonutSegmentStyle = (index) => {
  if (totalAttendance.value === 0) return {}
  
  let previousPercentages = 0
  for (let i = 0; i < index; i++) {
    previousPercentages += (attendanceData.value[i].value / totalAttendance.value) * 100
  }
  
  const currentPercentage = (attendanceData.value[index].value / totalAttendance.value) * 100
  const color = getComputedStyle(document.documentElement).getPropertyValue(`--color-${attendanceData.value[index].color.split('-')[1]}-500`) || '#3b82f6'
  
  return {
    borderColor: color,
    clipPath: `polygon(50% 50%, 100% 0, 100% 100%, 0 100%, 0 0, 50% 0)`,
    transform: `rotate(${(previousPercentages / 100) * 360}deg)`,
    transformOrigin: '50% 50%'
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
/* Lighter scrollbar styling for lists */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: rgba(243, 244, 246, 0.5); 
  border-radius: 4px;
}
::-webkit-scrollbar-thumb {
  background: rgba(209, 213, 219, 0.8); 
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 1); 
}
</style>