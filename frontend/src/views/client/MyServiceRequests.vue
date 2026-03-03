<template>
  <div class="min-h-screen bg-gradient-to-br p-3 sm:p-4 md:p-6 text-slate-200">
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
              <SelectItem value="verifying">Verifying</SelectItem>
              <SelectItem value="ongoing">Ongoing</SelectItem>
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

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-cyan-500 mb-4"></div>
      <p class="text-gray-400 text-sm">Fetching your service requests...</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4 sm:gap-6">
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
            
            <div class="flex justify-end sm:justify-start mt-3 sm:mt-0 sm:self-center gap-2">
              <Button 
                v-if="request.status === 'pending' || request.status === 'completed' || request.status === 'rejected'"
                variant="outline" 
                size="sm"
                @click="viewDetails(request)"
                class="bg-gray-700/50 hover:bg-gray-700 text-gray-300 border-gray-600 hover:border-cyan-500/50 rounded-xl gap-2"
              >
                <Eye class="w-4 h-4" />
                Details
              </Button>
              
              <Button 
                v-if="request.status === 'verifying' || request.status === 'ongoing'"
                variant="default" 
                size="sm"
                @click="goToChat"
                class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl gap-2 shadow-lg shadow-blue-900/20"
              >
                <MessageSquare class="w-4 h-4" />
                Messages
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

    <div v-if="!isLoading && filteredRequests.length === 0" class="text-center py-12 sm:py-24">
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

    <Dialog v-model:open="isModalOpen">
      <DialogContent class="bg-gray-900 border-gray-800 text-slate-200 sm:max-w-xl rounded-2xl max-h-[90vh] overflow-y-auto custom-scrollbar p-0">
        <div class="sticky top-0 z-10 bg-gray-900/95 backdrop-blur-sm border-b border-gray-800 px-6 py-5">
          <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
             <ClipboardList class="w-5 h-5 text-cyan-400" />
             Service Request Details
          </DialogTitle>
        </div>
        
        <div v-if="selectedRequest" class="px-6 py-5 space-y-6">
           
           <div v-if="selectedRequest.raw.service_offering" class="bg-gray-800/40 rounded-2xl p-5 border border-gray-700/50 shadow-inner">
             <h4 class="text-sm font-bold text-cyan-400 mb-4 flex items-center gap-2 uppercase tracking-wider">
               <Briefcase class="w-4 h-4" />
               Service Package Info
             </h4>
             <div class="space-y-4">
               <div>
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Service Title</p>
                 <p class="font-semibold text-base text-white">{{ selectedRequest.raw.service_offering.title }}</p>
               </div>
               
               <div class="grid grid-cols-2 md:grid-cols-3 gap-4 bg-gray-900/50 p-3 rounded-xl border border-gray-800">
                 <div>
                   <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Category</p>
                   <p class="text-xs text-gray-200 font-medium">{{ selectedRequest.raw.service_offering.category }}</p>
                 </div>
                 <div>
                   <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Base Price</p>
                   <p class="text-xs text-emerald-400 font-bold">
                     ₱{{ Number(selectedRequest.raw.service_offering.price).toLocaleString() }} 
                     <span class="text-gray-400 font-normal uppercase text-[9px]">/ {{ selectedRequest.raw.service_offering.price_type.replace('-', ' ') }}</span>
                   </p>
                 </div>
                 <div class="col-span-2 md:col-span-1">
                   <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Est. Duration</p>
                   <p class="text-xs text-gray-200 font-medium">{{ selectedRequest.raw.service_offering.duration }}</p>
                 </div>
               </div>

               <div v-if="selectedRequest.raw.service_offering.description">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1.5">Package Description</p>
                 <p class="text-xs text-gray-400 leading-relaxed">{{ selectedRequest.raw.service_offering.description }}</p>
               </div>
             </div>
           </div>

           <div v-else class="bg-gray-800/40 rounded-2xl p-5 border border-gray-700/50">
              <p class="text-sm text-gray-400 italic">Custom Service Request (No predefined package selected)</p>
           </div>

           <div class="space-y-5 px-1">
             <h4 class="text-sm font-bold text-white flex items-center gap-2 border-b border-gray-800 pb-2 uppercase tracking-wider">
               <User class="w-4 h-4 text-blue-400" />
               My Request Specifics
             </h4>

             <div class="space-y-1.5">
               <p class="text-[10px] text-gray-500 uppercase tracking-wider">My Notes / Instructions</p>
               <p class="text-sm bg-gray-950 border border-gray-800 p-4 rounded-xl text-gray-300 leading-relaxed italic">
                 "{{ selectedRequest.description || 'No additional notes provided.' }}"
               </p>
             </div>

             <div class="grid grid-cols-2 gap-x-4 gap-y-5">
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><Calendar class="w-3 h-3 text-gray-400"/> Preferred Date</p>
                 <p class="text-sm text-gray-200 font-semibold">{{ selectedRequest.requestedDate }}</p>
               </div>
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><Clock class="w-3 h-3 text-gray-400"/> Arrival Time</p>
                 <p class="text-sm text-gray-200 font-semibold">{{ selectedRequest.raw.time_preference || 'Flexible' }}</p>
               </div>
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><Phone class="w-3 h-3 text-gray-400"/> Contact</p>
                 <p class="text-sm text-gray-200 font-semibold">{{ selectedRequest.raw.contact_number || 'N/A' }}</p>
               </div>
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider">Status</p>
                 <Badge :class="getStatusClasses(selectedRequest.status)" class="mt-0.5">{{ selectedRequest.statusLabel }}</Badge>
               </div>
             </div>

             <div class="space-y-1.5 pt-3 border-t border-gray-800">
               <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><MapPin class="w-3 h-3 text-gray-400"/> Complete Address</p>
               <p class="text-sm text-gray-300">{{ selectedRequest.raw.address || 'N/A' }}</p>
             </div>
           </div>
           
           <div class="h-2"></div>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'

import { Card, CardContent } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Progress } from '@/components/ui/progress'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { 
  Filter, ClipboardList, Zap, Clock, CheckCircle2, User, Eye, MessageSquare, Briefcase, MapPin, Calendar, Phone
} from 'lucide-vue-next'

const router = useRouter()

// --- State Management ---
const activeFilter = ref('all')
const serviceRequests = ref([])
const isLoading = ref(true)

// Modal State
const isModalOpen = ref(false)
const selectedRequest = ref(null)

// --- Data Fetching ---
const fetchRequests = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/client/services/my-requests')
    if (response.data.success) {
      serviceRequests.value = response.data.data.map(req => {
        
        // Progress Logic Setup
        let progress = 10;
        if (req.status === 'verifying') progress = 30;
        else if (req.status === 'approved') progress = 50;
        else if (req.status === 'ongoing') progress = 70;
        else if (req.status === 'completed' || req.status === 'rejected') progress = 100;

        const cId = req.id
        const generatedColor = ['#3B82F6', '#10B981', '#6B7280', '#F59E0B', '#8B5CF6', '#059669', '#ec4899'][cId % 7]

        return {
          id: req.id,
          projectName: req.service_offering ? req.service_offering.title : 'Custom Service Request',
          description: req.description,
          serviceProvider: req.provider ? `${req.provider.first_name} ${req.provider.last_name}` : 'Pending Assignment',
          color: generatedColor,
          colorCode: generatedColor,
          status: req.status,
          statusLabel: req.status.charAt(0).toUpperCase() + req.status.slice(1).replace('-', ' '),
          date: new Date(req.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
          requestedDate: req.preferred_date || 'N/A',
          currentStageDate: new Date(req.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
          progress: progress,
          raw: req // Keep raw payload to dump in Modal
        }
      })
    }
  } catch (error) {
    console.error('Failed to load requests:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchRequests()
})

// --- Computeds ---
const statusCounts = computed(() => {
  return serviceRequests.value.reduce((acc, request) => {
    acc[request.status] = (acc[request.status] || 0) + 1
    return acc
  }, {})
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
    value: statusCounts.value['ongoing'] || 0, 
    colorClass: 'text-cyan-300', 
    bgClass: 'bg-cyan-500/10', 
    iconClass: 'text-cyan-400', 
    icon: Zap 
  },
  { 
    label: 'Pending/Verifying', 
    value: (statusCounts.value['pending'] || 0) + (statusCounts.value['verifying'] || 0), 
    colorClass: 'text-amber-300', 
    bgClass: 'bg-amber-500/10', 
    iconClass: 'text-amber-400', 
    icon: Clock 
  },
  { 
    label: 'Completed', 
    value: statusCounts.value['completed'] || 0, 
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
    'verifying': 'bg-blue-500/10 text-blue-400 border-blue-500/30',
    'ongoing': 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
    'completed': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
    'rejected': 'bg-red-500/10 text-red-400 border-red-500/30'
  }
  return classes[status] || 'bg-gray-500/10 text-gray-400 border-gray-500/30'
}

const getIndicatorColor = (status) => {
  if (status === 'pending') return 'bg-amber-500'
  if (status === 'verifying') return 'bg-blue-500'
  if (status === 'ongoing') return 'bg-cyan-500'
  if (status === 'rejected') return 'bg-red-500'
  return 'bg-emerald-500'
}

const getStatusTextColor = (status) => {
  if (status === 'pending') return 'text-amber-400'
  if (status === 'verifying') return 'text-blue-400'
  if (status === 'ongoing') return 'text-cyan-400'
  if (status === 'rejected') return 'text-red-400'
  return 'text-emerald-400'
}

const getProgressBarTheme = (status) => {
  if (status === 'pending') return '[&>div]:bg-amber-500'
  if (status === 'verifying') return '[&>div]:bg-blue-500'
  if (status === 'ongoing') return '[&>div]:bg-cyan-500'
  if (status === 'rejected') return '[&>div]:bg-red-500'
  return '[&>div]:bg-emerald-500'
}

const getStatusMessage = (status) => {
  const messages = {
    'pending': 'Awaiting confirmation',
    'verifying': 'Awaiting official deal',
    'ongoing': 'Ongoing project',
    'completed': 'Successfully completed',
    'rejected': 'Request declined'
  }
  return messages[status] || 'Status unknown'
}

// Actions
const viewDetails = (request) => {
  selectedRequest.value = request
  isModalOpen.value = true
}

const goToChat = () => {
  router.push('/Clients/ClientChat')
}
</script>

<style scoped>
/* Smooth transitions */
* {
  transition: all 0.2s ease-in-out;
}

/* Re-applying your custom scrollbar */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
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