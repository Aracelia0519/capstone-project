<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 to-slate-950 p-3 sm:p-4 md:p-6 text-slate-200">
    <div class="mb-6 sm:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="max-w-full overflow-hidden">
          <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-300 to-teal-300 leading-tight sm:leading-normal">
            My Service Requests
          </h1>
          <p class="text-gray-400 mt-1 sm:mt-2 text-sm sm:text-base">Track and manage all your painting service jobs</p>
        </div>
        
        <div class="flex items-center space-x-2 mt-2 sm:mt-0">
          <Select v-model="activeFilter">
            <SelectTrigger class="w-[180px] bg-gray-800/50 border-gray-700 text-gray-300 rounded-xl focus:ring-cyan-500/30 backdrop-blur-sm">
              <div class="flex items-center gap-2">
                <Filter class="w-4 h-4 text-cyan-400" />
                <SelectValue placeholder="Filter by status" />
              </div>
            </SelectTrigger>
            <SelectContent class="bg-gray-800 border-gray-700 text-gray-300">
              <SelectItem value="all">All Requests</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="in-progress">In Progress</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
      
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
        <Card v-for="stat in statsCards" :key="stat.label" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 border-gray-700/50 backdrop-blur-sm shadow-none">
          <CardContent class="p-3 sm:p-4 flex items-center justify-between">
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm truncate">{{ stat.label }}</p>
              <p :class="['text-xl sm:text-2xl font-bold mt-1 truncate', stat.colorClass]">
                {{ stat.value }}
              </p>
            </div>
            <div :class="['p-2 sm:p-3 rounded-xl flex-shrink-0 ml-2', stat.bgClass]">
              <component :is="stat.icon" :class="['w-5 h-5 sm:w-6 sm:h-6', stat.iconClass]" />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:gap-6">
      <Card 
        v-for="request in filteredRequests"
        :key="request.id"
        class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 border-gray-700/50 backdrop-blur-sm hover:border-cyan-500/30 transition-all duration-300 group shadow-none overflow-hidden"
      >
        <CardContent class="p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
            <div class="flex flex-col xs:flex-row items-start gap-3 sm:gap-4 w-full sm:w-auto">
              <div class="relative flex-shrink-0">
                <div 
                  class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl shadow-lg sm:shadow-2xl transition-transform duration-300 group-hover:scale-105"
                  :style="{ backgroundColor: request.color }"
                ></div>
                <Badge variant="secondary" class="absolute -bottom-1 -right-1 px-2 py-0.5 bg-gray-900/90 text-[10px] font-mono border-gray-700">
                  {{ request.colorCode }}
                </Badge>
              </div>
              
              <div class="flex-1 min-w-0">
                <div class="flex flex-col xs:flex-row xs:items-center gap-2 xs:gap-3 mb-2 sm:mb-3">
                  <Badge :class="['font-semibold border uppercase tracking-wider text-[10px]', getStatusClasses(request.status)]">
                    {{ request.statusLabel }}
                  </Badge>
                  <span class="text-xs sm:text-sm text-gray-400">{{ request.date }}</span>
                </div>
                
                <h3 class="text-base sm:text-lg font-semibold text-white mb-1 sm:mb-2 truncate">{{ request.projectName }}</h3>
                <p class="text-gray-400 text-xs sm:text-sm mb-3 sm:mb-4 line-clamp-2 leading-relaxed">{{ request.description }}</p>
                
                <div class="flex items-center gap-2 sm:gap-3">
                  <Avatar class="w-7 h-7 sm:w-8 sm:h-8 border border-cyan-500/30">
                    <AvatarImage src="" />
                    <AvatarFallback class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white text-[10px]">
                      <User class="w-3 h-3 sm:w-4 sm:h-4" />
                    </AvatarFallback>
                  </Avatar>
                  <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-white truncate">{{ request.serviceProvider }}</p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-tighter">Assigned Professional</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end sm:justify-start mt-3 sm:mt-0 sm:self-center">
              <Button 
                variant="outline" 
                size="sm"
                @click="viewDetails(request)"
                class="bg-gray-700/50 hover:bg-gray-700 text-gray-300 border-gray-600 hover:border-cyan-500/50 rounded-xl gap-2"
              >
                <Eye class="w-4 h-4" />
                Details
              </Button>
            </div>
          </div>
          
          <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-700/50">
            <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 mb-3">
              <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></div>
                  <span class="text-[11px] text-gray-400">Requested: <span class="text-gray-200">{{ request.requestedDate }}</span></span>
                </div>
                <div class="flex items-center gap-2">
                  <div :class="['w-2 h-2 rounded-full', getIndicatorColor(request.status)]"></div>
                  <span class="text-[11px] text-gray-400">Last Update: <span class="text-gray-200">{{ request.currentStageDate }}</span></span>
                </div>
              </div>
              
              <div :class="['text-xs font-bold uppercase tracking-widest', getStatusTextColor(request.status)]">
                {{ getStatusMessage(request.status) }}
              </div>
            </div>
            
            <Progress 
              :model-value="request.progress" 
              :class="['h-2 bg-gray-800', getProgressBarTheme(request.status)]" 
            />
          </div>
        </CardContent>
      </Card>
    </div>

    <div v-if="filteredRequests.length === 0" class="text-center py-12 sm:py-24">
      <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-6 bg-gray-800/50 rounded-3xl flex items-center justify-center border border-gray-700/50">
        <ClipboardList class="w-10 h-10 text-gray-600" />
      </div>
      <h3 class="text-lg sm:text-xl font-semibold text-gray-300 mb-2">No service requests found</h3>
      <p class="text-gray-500 max-w-md mx-auto text-sm px-4">
        {{ activeFilter === 'all' 
          ? "You haven't created any service requests yet." 
          : `You don't have any ${activeFilter} service requests.` 
        }}
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  Card, CardContent 
} from '@/components/ui/card'
import { 
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue 
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Progress } from '@/components/ui/progress'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { 
  Filter, ClipboardList, Zap, Clock, CheckCircle2, User, Eye 
} from 'lucide-vue-next'

// --- State Management ---
const activeFilter = ref('all')

const serviceRequests = ref([
  {
    id: 1,
    projectName: 'Living Room Wall Painting',
    description: 'Complete wall painting with accent wall in blue',
    serviceProvider: 'John PaintMaster',
    color: '#3B82F6',
    colorCode: '#3B82F6',
    status: 'in-progress',
    statusLabel: 'In Progress',
    date: 'Oct 15, 2023',
    requestedDate: 'Oct 10, 2023',
    currentStageDate: 'Oct 20, 2023',
    progress: 60
  },
  {
    id: 2,
    projectName: 'Bedroom Ceiling & Walls',
    description: 'Full bedroom renovation with premium matte finish',
    serviceProvider: 'Sarah ColorExpert',
    color: '#10B981',
    colorCode: '#10B981',
    status: 'pending',
    statusLabel: 'Pending',
    date: 'Nov 5, 2023',
    requestedDate: 'Nov 5, 2023',
    currentStageDate: 'Nov 12, 2023',
    progress: 20
  },
  {
    id: 3,
    projectName: 'Kitchen Cabinet Refinishing',
    description: 'Cabinet refinishing with modern gray finish',
    serviceProvider: 'Mike BrushPro',
    color: '#6B7280',
    colorCode: '#6B7280',
    status: 'completed',
    statusLabel: 'Completed',
    date: 'Sep 28, 2023',
    requestedDate: 'Sep 15, 2023',
    currentStageDate: 'Sep 28, 2023',
    progress: 100
  },
  {
    id: 4,
    projectName: 'Exterior House Painting',
    description: 'Complete exterior painting with weather-resistant paint',
    serviceProvider: 'Alex SurfacePro',
    color: '#F59E0B',
    colorCode: '#F59E0B',
    status: 'in-progress',
    statusLabel: 'In Progress',
    date: 'Oct 25, 2023',
    requestedDate: 'Oct 20, 2023',
    currentStageDate: 'Oct 30, 2023',
    progress: 40
  },
  {
    id: 5,
    projectName: 'Bathroom Wall Tiling',
    description: 'Wall tiling with waterproof blue ceramic tiles',
    serviceProvider: 'Emma TileMaster',
    color: '#8B5CF6',
    colorCode: '#8B5CF6',
    status: 'pending',
    statusLabel: 'Pending',
    date: 'Nov 8, 2023',
    requestedDate: 'Nov 8, 2023',
    currentStageDate: 'Nov 15, 2023',
    progress: 10
  },
  {
    id: 6,
    projectName: 'Office Space Painting',
    description: 'Professional office space with calming green tones',
    serviceProvider: 'David OfficePro',
    color: '#059669',
    colorCode: '#059669',
    status: 'completed',
    statusLabel: 'Completed',
    date: 'Aug 15, 2023',
    requestedDate: 'Aug 1, 2023',
    currentStageDate: 'Aug 15, 2023',
    progress: 100
  }
])

// --- Computeds ---
const statusCounts = computed(() => {
  return serviceRequests.value.reduce((acc, request) => {
    acc[request.status] = (acc[request.status] || 0) + 1
    return acc
  }, { pending: 0, 'in-progress': 0, completed: 0 })
})

const filteredRequests = computed(() => {
  if (activeFilter.value === 'all') return serviceRequests.value
  return serviceRequests.value.filter(request => request.status === activeFilter.value)
})

const statsCards = computed(() => [
  { 
    label: 'Total Requests', 
    value: serviceRequests.value.length, 
    colorClass: 'text-white', 
    bgClass: 'bg-blue-500/10', 
    iconClass: 'text-blue-400', 
    icon: ClipboardList 
  },
  { 
    label: 'Active', 
    value: statusCounts.value['in-progress'], 
    colorClass: 'text-cyan-300', 
    bgClass: 'bg-cyan-500/10', 
    iconClass: 'text-cyan-400', 
    icon: Zap 
  },
  { 
    label: 'Pending', 
    value: statusCounts.value.pending, 
    colorClass: 'text-amber-300', 
    bgClass: 'bg-amber-500/10', 
    iconClass: 'text-amber-400', 
    icon: Clock 
  },
  { 
    label: 'Completed', 
    value: statusCounts.value.completed, 
    colorClass: 'text-emerald-300', 
    bgClass: 'bg-emerald-500/10', 
    iconClass: 'text-emerald-400', 
    icon: CheckCircle2 
  }
])

// --- Helper Methods ---
const getStatusClasses = (status) => {
  const classes = {
    'pending': 'bg-amber-500/10 text-amber-400 border-amber-500/30',
    'in-progress': 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
    'completed': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
  }
  return classes[status] || 'bg-gray-500/10 text-gray-400 border-gray-500/30'
}

const getIndicatorColor = (status) => {
  if (status === 'pending') return 'bg-amber-500'
  if (status === 'in-progress') return 'bg-cyan-500'
  return 'bg-emerald-500'
}

const getStatusTextColor = (status) => {
  if (status === 'pending') return 'text-amber-400'
  if (status === 'in-progress') return 'text-cyan-400'
  return 'text-emerald-400'
}

const getProgressBarTheme = (status) => {
  // We use custom CSS variables for the Progress component indicator
  if (status === 'pending') return '[&>div]:bg-amber-500'
  if (status === 'in-progress') return '[&>div]:bg-cyan-500'
  return '[&>div]:bg-emerald-500'
}

const getStatusMessage = (status) => {
  const messages = {
    'pending': 'Awaiting confirmation',
    'in-progress': 'Work in progress',
    'completed': 'Successfully completed'
  }
  return messages[status] || 'Status unknown'
}

const viewDetails = (request) => {
  console.log('Viewing details for:', request)
}
</script>

<style scoped>
/* Smooth transitions */
* {
  transition: all 0.2s ease-in-out;
}

/* Re-applying your custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 3px;
}
::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #38bdf8, #0ea5e9);
  border-radius: 3px;
}

/* Custom indicator animations */
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: .5; }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>