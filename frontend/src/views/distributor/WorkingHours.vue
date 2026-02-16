<template>
  <div class="page-container p-3 md:p-6 max-w-8xl mx-auto min-h-screen">
    <Toaster position="top-right" />
    <div class="mb-6 md:mb-10 space-y-2">
      <div class="flex items-center gap-3">
        <div class="p-2.5 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg flex-shrink-0">
          <Clock class="w-5 h-5 md:w-6 md:h-6 text-white" />
        </div>
        <h1 class="text-xl md:text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
          Working Hours
        </h1>
      </div>
      <p class="text-sm md:text-base text-gray-500 leading-relaxed max-w-2xl">
        Configure your business operating schedule. These settings will determine when employees can clock in without being marked as overtime.
      </p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      
      <div class="xl:col-span-2 space-y-4 md:space-y-6">
        <Card class="border-0 shadow-lg ring-1 ring-gray-100 overflow-hidden bg-white">
          <CardHeader class="bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 py-4 px-4 md:px-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <CardTitle class="text-base md:text-lg font-bold text-gray-800">Weekly Schedule</CardTitle>
                <CardDescription class="text-xs md:text-sm">Set your standard opening and closing times.</CardDescription>
              </div>
              
              <Button 
                variant="outline" 
                size="sm" 
                @click="copyMondayToAll" 
                class="hidden sm:flex bg-white hover:bg-blue-50 text-blue-600 border-blue-200 hover:border-blue-300 transition-all text-xs md:text-sm h-9"
              >
                <Copy class="w-3.5 h-3.5 mr-2" />
                Copy Mon to All Days
              </Button>
            </div>
          </CardHeader>
          
          <CardContent class="p-0">
            <div v-if="loading" class="p-8 text-center text-gray-500">
               <Loader2 class="w-8 h-8 animate-spin mx-auto mb-2 text-blue-500" />
               <p>Loading schedule...</p>
            </div>

            <div v-else class="divide-y divide-gray-100">
              <div 
                v-for="(day, index) in schedule" 
                :key="day.day" 
                class="group transition-all duration-200 p-4 md:px-6 md:py-5 hover:bg-gray-50/80"
                :class="{'bg-gray-50/50': !day.isOpen}"
              >
                <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
                  
                  <div class="flex items-center justify-between w-full md:w-40">
                    <div class="flex items-center gap-3">
                      <button
                        :id="`day-${index}`"
                        @click="toggleDayOpen(index)"
                        :disabled="day.day === currentDayName"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        :class="[
                          day.isOpen ? 'bg-green-500' : 'bg-gray-200',
                          day.day === currentDayName ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                      >
                        <span class="sr-only">{{ day.isOpen ? 'Close' : 'Open' }} {{ day.day }}</span>
                        <span
                          class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                          :class="day.isOpen ? 'translate-x-6' : 'translate-x-1'"
                        />
                      </button>
                      <Label 
                        :for="`day-${index}`" 
                        class="font-semibold text-gray-700 cursor-pointer text-base md:text-sm select-none"
                        :class="{'text-gray-400': !day.isOpen}"
                      >
                        {{ day.day }}
                      </Label>
                    </div>
                    <span 
                      class="md:hidden text-xs font-medium px-2 py-1 rounded-full"
                      :class="day.isOpen ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                    >
                      {{ day.isOpen ? 'Open' : 'Closed' }}
                    </span>
                  </div>

                  <div 
                    class="flex-1 transition-all duration-300 grid grid-cols-2 md:flex md:items-center gap-3 md:gap-4"
                    :class="day.isOpen ? 'opacity-100' : 'opacity-40 pointer-events-none grayscale'"
                  >
                    <div class="relative w-full md:w-auto">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] uppercase font-bold text-gray-400 tracking-wider">In</span>
                      <Input 
                        type="time" 
                        v-model="day.start"
                        class="pl-9 h-12 md:h-10 w-full md:w-36 bg-white border-gray-200 focus:border-blue-500 rounded-lg text-sm md:text-base shadow-sm"
                      />
                    </div>
                    
                    <span class="hidden md:block text-gray-300 font-medium">to</span>

                    <div class="relative w-full md:w-auto">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] uppercase font-bold text-gray-400 tracking-wider">Out</span>
                      <Input 
                        type="time" 
                        v-model="day.end"
                        class="pl-10 h-12 md:h-10 w-full md:w-36 bg-white border-gray-200 focus:border-blue-500 rounded-lg text-sm md:text-base shadow-sm"
                      />
                    </div>
                  </div>
                  
                  <div class="hidden md:flex justify-end w-24">
                    <Badge 
                      variant="outline"
                      :class="[
                        'border-0 font-medium px-3 py-1',
                        day.isOpen 
                          ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' 
                          : 'bg-gray-50 text-gray-500 ring-1 ring-inset ring-gray-500/10'
                      ]"
                    >
                      {{ day.isOpen ? 'Open' : 'Closed' }}
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
          
          <div class="p-4 bg-gray-50 border-t border-gray-100 sm:hidden">
            <Button variant="outline" class="w-full bg-white text-blue-600 border-blue-200" @click="copyMondayToAll">
              <Copy class="w-4 h-4 mr-2" />
              Copy Monday to All Days
            </Button>
          </div>
        </Card>
      </div>

      <div class="space-y-6">
        
        <Card class="border-0 shadow-lg bg-gradient-to-br from-indigo-600 to-blue-700 text-white overflow-hidden relative">
          <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
          
          <CardContent class="p-5 md:p-6 relative z-10">
            <h3 class="text-base md:text-lg font-bold mb-4 flex items-center text-white">
              <Sparkles class="w-4 h-4 md:w-5 md:h-5 mr-2 text-yellow-300" />
              Schedule Overview
            </h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-white/10 rounded-lg p-3 backdrop-blur-sm">
                <span class="text-xs text-blue-100 block mb-1">Active Days</span>
                <span class="font-bold text-2xl md:text-3xl">{{ operationalDaysCount }}</span>
              </div>
              <div class="bg-white/10 rounded-lg p-3 backdrop-blur-sm">
                <span class="text-xs text-blue-100 block mb-1">Total Hours</span>
                <span class="font-bold text-xl md:text-2xl">{{ totalWeeklyHours }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 shadow-md">
          <CardHeader class="pb-3">
            <CardTitle class="text-base font-bold text-gray-800">Preferences</CardTitle>
          </CardHeader>
          <CardContent class="space-y-5">
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label class="text-sm font-medium text-gray-700">Auto-approve</Label>
                <p class="text-xs text-gray-500">Approve attendance automatically</p>
              </div>
              <button
                @click="toggleAutoApprove"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                :class="autoApprove ? 'bg-blue-500' : 'bg-gray-200'"
              >
                <span class="sr-only">Toggle auto-approve</span>
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="autoApprove ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
            </div>
            
            <div class="flex items-center justify-between">
              <div class="space-y-0.5">
                <Label class="text-sm font-medium text-gray-700">Strict Late Policy</Label>
                <p class="text-xs text-gray-500">Mark late if >1 min past start</p>
              </div>
              <button
                @click="toggleLatePolicy"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                :class="strictLatePolicy ? 'bg-blue-500' : 'bg-gray-200'"
              >
                <span class="sr-only">Toggle late policy</span>
                <span
                  class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                  :class="strictLatePolicy ? 'translate-x-6' : 'translate-x-1'"
                />
              </button>
            </div>

            <div class="pt-4 mt-2 border-t border-gray-100 flex flex-col gap-3">
              
              <AlertDialog>
                <AlertDialogTrigger as-child>
                  <Button 
                    class="w-full h-12 text-base font-medium shadow-lg shadow-blue-500/20 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-all duration-300"
                    :disabled="saving || loading"
                  >
                    <Loader2 v-if="saving" class="w-5 h-5 mr-2 animate-spin" />
                    <Save v-else class="w-5 h-5 mr-2" />
                    {{ saving ? 'Saving...' : 'Save Schedule' }}
                  </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Save Working Hours?</AlertDialogTitle>
                    <AlertDialogDescription>
                      This will update your business operating schedule. Employees will see these changes immediately. Are you sure you want to proceed?
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="saveWorkingHours">Save Changes</AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
              
              <Button 
                variant="ghost" 
                class="w-full text-gray-500 hover:text-gray-700 hover:bg-gray-50"
                @click="resetSchedule"
                :disabled="saving || loading"
              >
                Reset to Default
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import axios from 'axios'
import { toast, Toaster } from 'vue-sonner'
import { 
  Clock, Save, Copy, Loader2, Sparkles 
} from 'lucide-vue-next'

// UI Components
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'

// Alert Dialog Components
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

// --- AUTHENTICATION CONFIG ---
// 1. Get the token from LocalStorage
const token = localStorage.getItem('auth_token');

// 2. Configure Axios with Base URL and Headers
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000',
  headers: {
    'Authorization': `Bearer ${token}`, 
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

const saving = ref(false)
const loading = ref(true)
const autoApprove = ref(true)
const strictLatePolicy = ref(false)

const defaultSchedule = [
  { day: 'Monday', isOpen: true, start: '09:00', end: '18:00' },
  { day: 'Tuesday', isOpen: true, start: '09:00', end: '18:00' },
  { day: 'Wednesday', isOpen: true, start: '09:00', end: '18:00' },
  { day: 'Thursday', isOpen: true, start: '09:00', end: '18:00' },
  { day: 'Friday', isOpen: true, start: '09:00', end: '18:00' },
  { day: 'Saturday', isOpen: false, start: '09:00', end: '18:00' },
  { day: 'Sunday', isOpen: false, start: '09:00', end: '18:00' },
]

// Initialize with reactive objects
const schedule = ref([])

// --- LIFECYCLE HOOKS ---
onMounted(async () => {
  if (!token) {
    toast.error('You are not logged in. Please log in first.');
    loading.value = false;
    return;
  }
  await fetchSchedule()
})

// --- HELPER FUNCTIONS ---

// Determine current day name (e.g., "Monday")
const currentDayName = computed(() => {
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  return days[new Date().getDay()]
})

const toggleDayOpen = (index) => {
  const dayToToggle = schedule.value[index]
  
  // Validation: Prevent toggling the current day
  if (dayToToggle.day === currentDayName.value) {
    toast.error(`You cannot change the status for today (${dayToToggle.day}).`)
    return
  }

  // Create a new object to trigger reactivity
  const updatedSchedule = [...schedule.value]
  updatedSchedule[index] = {
    ...updatedSchedule[index],
    isOpen: !updatedSchedule[index].isOpen
  }
  schedule.value = updatedSchedule
  
  // Ensure time fields are available when switching on
  if (updatedSchedule[index].isOpen && (!updatedSchedule[index].start || !updatedSchedule[index].end)) {
    const monday = schedule.value.find(d => d.day === 'Monday')
    if (monday) {
      updatedSchedule[index].start = monday.start || '09:00'
      updatedSchedule[index].end = monday.end || '18:00'
      schedule.value = updatedSchedule
    }
  }
}

const toggleAutoApprove = () => {
  autoApprove.value = !autoApprove.value
}

const toggleLatePolicy = () => {
  strictLatePolicy.value = !strictLatePolicy.value
}

// --- API ACTIONS ---
const fetchSchedule = async () => {
  loading.value = true
  try {
    const response = await api.get('/api/distributor/working-hours')
    
    console.log('API Response:', response.data)
    
    if (response.data && response.data.schedule && Array.isArray(response.data.schedule)) {
      // Map the API response directly
      schedule.value = response.data.schedule.map(day => ({
        day: day.day,
        isOpen: Boolean(day.isOpen),
        start: day.start,
        end: day.end
      }))
      
      console.log('Mapped schedule:', schedule.value)
    } else {
      // Fallback to default schedule
      schedule.value = JSON.parse(JSON.stringify(defaultSchedule))
    }
  } catch (error) {
    console.error('Error fetching schedule:', error)
    
    if (error.response) {
      if (error.response.status === 401) {
        toast.error('Session expired. Please log in again.')
      } else if (error.response.status === 404) {
        // If no schedule exists yet, use default
        schedule.value = JSON.parse(JSON.stringify(defaultSchedule))
        toast.info('No schedule found. Using default schedule.')
      } else {
        toast.error('Failed to load schedule settings')
      }
    } else {
      toast.error('Network error. Please check your connection.')
    }
  } finally {
    loading.value = false
  }
}

const saveWorkingHours = async () => {
  saving.value = true
  try {
    // Prepare data for API
    const scheduleToSend = schedule.value.map(day => ({
      day: day.day,
      isOpen: day.isOpen,
      start: day.start,
      end: day.end
    }))
    
    console.log('Sending schedule:', scheduleToSend)
    
    const response = await api.put('/api/distributor/working-hours', {
      schedule: scheduleToSend
    })
    
    console.log('Save response:', response.data)
    
    toast.success('Working hours updated successfully!')
  } catch (error) {
    console.error('Error saving schedule:', error)
    
    if (error.response) {
      if (error.response.status === 401) {
        toast.error('Session expired. Please log in again.')
      } else {
        toast.error(error.response?.data?.message || 'Failed to update working hours')
      }
    } else {
      toast.error('Network error. Please check your connection.')
    }
  } finally {
    saving.value = false
  }
}

// --- COMPUTED PROPERTIES ---
const operationalDaysCount = computed(() => {
  return schedule.value.filter(d => d.isOpen).length
})

const totalWeeklyHours = computed(() => {
  let totalMinutes = 0
  schedule.value.forEach(day => {
    if (day.isOpen && day.start && day.end) {
      const start = new Date(`1970-01-01T${day.start}:00`)
      const end = new Date(`1970-01-01T${day.end}:00`)
      if (end > start) {
        totalMinutes += (end - start) / 1000 / 60
      }
    }
  })
  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60
  
  if (minutes > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${hours}h`
})

// --- HELPER METHODS ---
const copyMondayToAll = () => {
  const monday = schedule.value.find(d => d.day === 'Monday')
  if (!monday) return

  let skippedToday = false

  const updatedSchedule = schedule.value.map(day => {
    if (day.day !== 'Monday') {
      // Validation: Skip current day during copy
      if (day.day === currentDayName.value) {
        skippedToday = true
        return day
      }

      return {
        ...day,
        isOpen: monday.isOpen,
        start: monday.start,
        end: monday.end
      }
    }
    return day
  })
  
  schedule.value = updatedSchedule
  
  if (skippedToday) {
    toast.success(`Monday schedule copied! (Skipped today: ${currentDayName.value})`)
  } else {
    toast.success('Monday schedule copied to all days!')
  }
}

const resetSchedule = () => {
  if (confirm('Are you sure you want to reset all changes to default?')) {
    schedule.value = JSON.parse(JSON.stringify(defaultSchedule))
    toast.info('Schedule reset to default (unsaved)')
  }
}
</script>

<style scoped>
/* Ensure smooth interactions on mobile touch */
input[type="time"] {
  -webkit-appearance: none;
  min-height: 2.5rem; /* Touch friendly height */
}

/* Fix for grid layout transitions */
.grid-cols-2 {
  transition: opacity 0.3s ease;
}

/* Custom switch styling */
button[role="switch"] {
  cursor: pointer;
  transition: background-color 0.2s ease;
}

button[role="switch"]:focus {
  outline: none;
  ring: 2px;
  ring-color: #3b82f6;
  ring-offset: 2px;
}
</style>