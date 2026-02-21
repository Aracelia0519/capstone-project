<template>
  <div class="attendance-employee p-4 md:p-6">
    <Teleport to="body">
  <Toaster
    position="top-right"
    :expand="false"
    :rich-colors="false"
    :close-button="true"
    :theme="'light'"
    :visible-toasts="1"
    :container-style="{
      position: 'fixed',
      top: '50%',
      left: '50%',
      transform: 'translate(-50%, -50%)',
      zIndex: 9999999,
      pointerEvents: 'none',
    }"
    :toast-options="{
      style: {
        background: 'white',
        color: 'black',
        border: 'none',
        boxShadow: '0 4px 15px rgba(0, 0, 0, 0.18)',
        padding: '16px 20px',          // slightly smaller padding
        fontSize: '15px',              // slightly smaller font
        minWidth: '280px',             // smaller width
        maxWidth: '400px',
        borderRadius: '10px',          // slightly smaller rounding
        pointerEvents: 'auto',
      },
    }"
  />
</Teleport>
    
    <AlertDialog :open="alertOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>{{ alertState.title }}</AlertDialogTitle>
          <AlertDialogDescription>
            {{ alertState.description }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="resolveAlert(false)">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="resolveAlert(true)">Continue</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Time Tracking</h1>
          <p class="text-gray-500 mt-1 text-sm md:text-base">Monitor your attendance and working hours</p>
          <div v-if="todaySchedule" class="mt-2 text-sm text-indigo-600 font-medium bg-indigo-50 inline-block px-3 py-1 rounded-md">
            <i class="fas fa-clock mr-1"></i>
            <span v-if="todaySchedule.is_open">
               Shift Today: {{ formatTime(todaySchedule.start_time) }} - {{ formatTime(todaySchedule.end_time) }}
            </span>
            <span v-else class="text-red-500">
               Rest Day (Office Closed)
            </span>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <Button 
            v-if="!todayAttendance?.time_in"
            @click="handleClockIn" 
            :disabled="loadingLoc || loadingAction"
            class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg h-12 px-6 text-base w-full sm:w-auto justify-center"
          >
            <Loader2 v-if="loadingLoc || loadingAction" class="w-4 h-4 mr-2 animate-spin" />
            <i v-else class="fas fa-sign-in-alt mr-2"></i>
            Clock In
          </Button>

          <Button 
            v-else-if="!todayAttendance?.time_out"
            @click="handleClockOut" 
            :disabled="loadingLoc || loadingAction"
            class="bg-red-600 hover:bg-red-700 text-white shadow-lg h-12 px-6 text-base w-full sm:w-auto justify-center"
          >
             <Loader2 v-if="loadingLoc || loadingAction" class="w-4 h-4 mr-2 animate-spin" />
             <i v-else class="fas fa-sign-out-alt mr-2"></i>
            Clock Out
          </Button>

          <Button v-else disabled variant="outline" class="border-gray-300 text-gray-500 h-12 px-6 text-base w-full sm:w-auto justify-center">
            <i class="fas fa-check mr-2"></i>
            Shift Complete
          </Button>

          <Button 
            @click="openRequestModal('Manual')"
            variant="outline" 
            class="border-indigo-600 text-indigo-600 hover:bg-indigo-50 h-12 px-6 text-base w-full sm:w-auto justify-center"
          >
            <i class="fas fa-edit mr-2"></i>
            Request Adjustment
          </Button>
        </div>
      </div>
    </div>

    <Dialog :open="showRequestModal" @update:open="showRequestModal = $event">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Request Attendance Adjustment</DialogTitle>
          <DialogDescription>
            Use this if you cannot clock in/out normally due to location issues.
          </DialogDescription>
        </DialogHeader>
        
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label>Type</Label>
            <Select v-model="requestForm.type" :disabled="isAutoRequest">
                <SelectTrigger>
                    <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="Clock In">Clock In</SelectItem>
                    <SelectItem value="Clock Out">Clock Out</SelectItem>
                </SelectContent>
            </Select>
          </div>

          <div class="grid gap-2">
            <Label>Proof of Location (Photo)</Label>
            <div class="flex items-center gap-2">
                <Input type="file" accept="image/*" @change="handleFileChange" class="cursor-pointer" />
            </div>
            <p class="text-xs text-gray-500">Take a selfie or photo of your surroundings.</p>
          </div>

          <div class="grid gap-2">
            <Label>Reason</Label>
            <Textarea v-model="requestForm.reason" placeholder="e.g. GPS not working, working off-site..." />
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showRequestModal = false">Cancel</Button>
          <Button @click="submitRequest" :disabled="loadingRequest">
            <Loader2 v-if="loadingRequest" class="w-4 h-4 mr-2 animate-spin" />
            Submit Request
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Card class="bg-gradient-to-r from-indigo-500 to-purple-600 shadow-lg border-0 mb-8 text-white">
      <CardContent class="p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <h3 class="text-xl font-semibold mb-2">Today's Attendance</h3>
            <div class="flex items-center">
              <div 
                class="w-3 h-3 rounded-full mr-3" 
                :class="todayAttendance?.time_in && !todayAttendance?.time_out ? 'bg-green-400 animate-pulse' : 'bg-gray-300'"
              ></div>
              <span class="text-lg">
                {{ 
                  todayAttendance?.time_out ? 'Clocked Out' : 
                  todayAttendance?.time_in ? 'Currently Clocked In' : 
                  'Not Clocked In' 
                }}
              </span>
            </div>
            <p v-if="todayAttendance?.time_in" class="mt-2 opacity-90 text-sm md:text-base">
               Started at: {{ formatTime(todayAttendance.time_in) }}
               <span v-if="attendanceStatus" class="ml-2 font-semibold" :class="attendanceStatus === 'Late' ? 'text-red-300' : 'text-green-300'">
                 ({{ attendanceStatus }})
               </span>
            </p>
          </div>
          <div class="text-left md:text-center bg-white/10 md:bg-transparent p-4 md:p-0 rounded-lg">
            <div class="text-3xl md:text-4xl font-bold mb-1">{{ currentTime }}</div>
            <p class="opacity-90 text-sm">{{ currentDate }}</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Tabs v-model="activeTab" class="w-full">
      <div class="mb-6 md:mb-8 border-b border-gray-200">
        <div class="overflow-x-auto pb-1 -mb-1 w-full">
          <TabsList class="bg-transparent h-auto p-0 flex w-max min-w-full md:w-full space-x-2 md:space-x-8 justify-start">
            <TabsTrigger 
              v-for="tab in tabs" 
              :key="tab.id" 
              :value="tab.id"
              class="py-3 px-3 md:px-1 rounded-none border-b-2 border-transparent data-[state=active]:border-indigo-600 data-[state=active]:text-indigo-600 text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition-colors shadow-none whitespace-nowrap"
            >
              <i :class="tab.icon + ' mr-2'"></i>
              {{ tab.label }}
            </TabsTrigger>
          </TabsList>
        </div>
      </div>

      <Card class="shadow-lg border-0">
        <CardContent class="p-4 md:p-6">
          
          <TabsContent value="daily" class="mt-0">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Time Record</h3>
            <div class="rounded-md border border-gray-100 overflow-hidden">
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader class="bg-gray-50">
                    <TableRow>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Date</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Time In</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Time Out</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Hours</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="record in dailyRecords" :key="record.id">
                      <TableCell class="font-medium whitespace-nowrap">{{ record.date }}</TableCell>
                      <TableCell class="whitespace-nowrap">{{ formatTime(record.time_in) }}</TableCell>
                      <TableCell class="whitespace-nowrap">{{ formatTime(record.time_out) || '--:--' }}</TableCell>
                      <TableCell>{{ record.hours_worked || '-' }}</TableCell>
                      <TableCell>
                        <Badge :class="[
                          'rounded-full px-2 py-1 font-semibold shadow-none',
                          record.status === 'Present' ? 'bg-green-100 text-green-800' :
                          record.status === 'Late' ? 'bg-amber-100 text-amber-800' :
                          'bg-red-100 text-red-800'
                        ]">
                          {{ record.status }}
                        </Badge>
                      </TableCell>
                    </TableRow>
                    <TableRow v-if="dailyRecords.length === 0">
                        <TableCell colspan="5" class="text-center py-4 text-gray-500">No attendance records found.</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </TabsContent>
          
           <TabsContent value="monthly" class="mt-0">
               <div class="text-center p-8 text-gray-500">Monthly Summary data coming soon</div>
           </TabsContent>
           <TabsContent value="overtime" class="mt-0">
               <div class="text-center p-8 text-gray-500">Overtime records data coming soon</div>
           </TabsContent>

        </CardContent>
      </Card>
    </Tabs>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from '@/utils/axios' 
import { toast, Toaster } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { 
  AlertDialog, 
  AlertDialogAction, 
  AlertDialogCancel, 
  AlertDialogContent, 
  AlertDialogDescription, 
  AlertDialogFooter, 
  AlertDialogHeader, 
  AlertDialogTitle 
} from '@/components/ui/alert-dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Loader2 } from 'lucide-vue-next'

const activeTab = ref('daily')
const loadingLoc = ref(false)
const loadingAction = ref(false)
const dailyRecords = ref([])
const todayAttendance = ref(null)
const todaySchedule = ref(null) 

// Request Modal State
const showRequestModal = ref(false)
const loadingRequest = ref(false)
const isAutoRequest = ref(false)
const requestForm = ref({
    type: 'Clock In',
    reason: '',
    photo: null,
    lat: null,
    long: null
})

// Alert Dialog State
const alertOpen = ref(false)
const alertState = ref({
    title: '',
    description: ''
})
let alertResolve = null

const currentTime = ref('')
const currentDate = ref('')
let timer = null

const tabs = [
  { id: 'daily', label: 'Daily Time Record', icon: 'fas fa-calendar-day' },
  { id: 'monthly', label: 'Monthly Summary', icon: 'fas fa-calendar-alt' },
  { id: 'overtime', label: 'Overtime Records', icon: 'fas fa-business-time' },
]

const attendanceStatus = computed(() => {
    if (!todayAttendance.value) return null;
    return todayAttendance.value.status;
});

// --- Alert Helper ---
// Replaces window.confirm with Shadcn Alert Dialog via Promise
const showConfirmDialog = (title, description) => {
    alertState.value = { title, description }
    alertOpen.value = true
    return new Promise((resolve) => {
        alertResolve = resolve
    })
}

const resolveAlert = (value) => {
    alertOpen.value = false
    if (alertResolve) {
        alertResolve(value)
        alertResolve = null
    }
}

// --- Modal Helpers ---
const openRequestModal = (triggerType = 'Manual') => {
    isAutoRequest.value = triggerType === 'Auto';
    
    // Auto-select type based on current status
    if(!todayAttendance.value?.time_in) requestForm.value.type = 'Clock In';
    else if(!todayAttendance.value?.time_out) requestForm.value.type = 'Clock Out';
    
    showRequestModal.value = true;
}

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        requestForm.value.photo = file;
    }
}

const submitRequest = async () => {
    if(!requestForm.value.photo) {
        toast.error("Please attach a photo proof.");
        return;
    }
    if(!requestForm.value.reason) {
        toast.error("Please provide a reason.");
        return;
    }

    loadingRequest.value = true;

    // Try to get coords anyway, but don't block if failed
    try {
        const coords = await getCoordinates();
        requestForm.value.lat = coords.latitude;
        requestForm.value.long = coords.longitude;
    } catch(e) {
        // ignore location error for manual request
        console.log("Loc failed for request, proceeding without coords");
    }

    const formData = new FormData();
    formData.append('type', requestForm.value.type);
    formData.append('reason', requestForm.value.reason);
    formData.append('photo', requestForm.value.photo);
    if(requestForm.value.lat) formData.append('latitude', requestForm.value.lat);
    if(requestForm.value.long) formData.append('longitude', requestForm.value.long);

    try {
        const response = await axios.post('/employee/attendance/request', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        
        if(response.data.status === 'success') {
            toast.success("Request submitted successfully!");
            showRequestModal.value = false;
            // Reset form
            requestForm.value = { type: 'Clock In', reason: '', photo: null, lat: null, long: null };
        }
    } catch (error) {
         toast.error(error.response?.data?.message || "Failed to submit request.");
    } finally {
        loadingRequest.value = false;
    }
}


// --- Geolocation Logic ---
const getCoordinates = () => {
    return new Promise((resolve, reject) => {
        if (!navigator.geolocation) {
            reject(new Error("Geolocation is not supported by your browser"));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve({
                    latitude: position.coords.latitude,
                    longitude: position.coords.longitude
                });
            },
            (error) => {
                reject(error);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });
};

// --- Time Helpers for Validations ---
const getDateTimeFromTimeString = (timeStr) => {
    if (!timeStr) return null;
    const now = new Date();
    const [hours, minutes, seconds] = timeStr.split(':').map(Number);
    const date = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hours, minutes, seconds || 0);
    return date;
};

// --- API Calls ---

const fetchSchedule = async () => {
    try {
        const response = await axios.get('/employee/schedule');
        if(response.data.status === 'success') {
            todaySchedule.value = response.data.data;
        }
    } catch (error) {
        console.warn("Could not fetch schedule.", error);
    }
}

const fetchAttendance = async () => {
    try {
        const response = await axios.get('/employee/attendance');
        if(response.data.status === 'success') {
            dailyRecords.value = response.data.data.history;
            todayAttendance.value = response.data.data.today;
        }
    } catch (error) {
        console.error("Error fetching attendance", error);
    }
}

// --- Validation Logic ---

const handleClockIn = async () => {
    if (todaySchedule.value && !todaySchedule.value.is_open) {
        const confirmed = await showConfirmDialog(
            "Rest Day Warning", 
            "Today is marked as a Rest Day (Closed). Do you still want to clock in?"
        );
        if (!confirmed) return;
    }

    if (todaySchedule.value && todaySchedule.value.start_time) {
        const startTime = getDateTimeFromTimeString(todaySchedule.value.start_time);
        const now = new Date();
        const graceTime = new Date(startTime.getTime() + 15 * 60000); 

        if (now > graceTime) {
            toast.warning("Note: You are clocking in LATE based on the schedule.");
        }
    }

    loadingLoc.value = true;
    try {
        toast.info("Acquiring location...");
        const coords = await getCoordinates();
        
        loadingLoc.value = false;
        loadingAction.value = true;
        
        const response = await axios.post('/employee/attendance/clock-in', coords);
        
        if(response.data.status === 'success') {
            toast.success("Clocked in successfully!");
            todayAttendance.value = response.data.data;
            await fetchAttendance(); 
        }
    } catch (error) {
        loadingLoc.value = false;
        loadingAction.value = false;
        
        // --- NEW: Handle Location Error ---
        if (error.response && error.response.data.code === 'LOCATION_INVALID') {
            toast.error("Location validation failed.");
            
            const confirmed = await showConfirmDialog(
                "Location Check Failed",
                "You are too far or GPS is inaccurate. Would you like to submit a Manual Request with photo proof instead?"
            );

            if(confirmed) {
                openRequestModal('Auto');
            }
        } else {
             if (error.response) {
                toast.error(error.response.data.message || "Failed to clock in.");
            } else {
                toast.error(error.message || "An unexpected error occurred.");
            }
        }
    } finally {
        loadingAction.value = false;
    }
}

const handleClockOut = async () => {
    if (todaySchedule.value && todaySchedule.value.end_time) {
        const endTime = getDateTimeFromTimeString(todaySchedule.value.end_time);
        const now = new Date();
        
        if (now < endTime) {
            const diffMs = endTime - now;
            const diffMins = Math.round(diffMs / 60000); 
            const hrs = Math.floor(diffMins / 60);
            const mins = diffMins % 60;
            
            let timeStr = "";
            if(hrs > 0) timeStr += `${hrs} hr${hrs > 1 ? 's' : ''} `;
            if(mins > 0) timeStr += `${mins} min${mins > 1 ? 's' : ''}`;

            const confirmed = await showConfirmDialog(
                "Early Clock Out",
                `You are attempting to clock out EARLY by approx ${timeStr}. Are you sure?`
            );

            if (!confirmed) return; 
        }
    }

    loadingLoc.value = true;
    try {
        toast.info("Verifying location...");
        const coords = await getCoordinates();
        
        loadingLoc.value = false;
        loadingAction.value = true;

        const response = await axios.post('/employee/attendance/clock-out', coords);
        
        if(response.data.status === 'success') {
            toast.success("Clocked out successfully!");
            todayAttendance.value = response.data.data;
            await fetchAttendance(); 
        }
    } catch (error) {
        loadingLoc.value = false;
        loadingAction.value = false;
        
        // --- NEW: Handle Location Error ---
        if (error.response && error.response.data.code === 'LOCATION_INVALID') {
             toast.error("Location validation failed.");

            const confirmed = await showConfirmDialog(
                "Location Check Failed",
                "You are too far or GPS is inaccurate. Would you like to submit a Manual Request with photo proof instead?"
            );

            if(confirmed) {
                openRequestModal('Auto');
            }
        } else {
            if (error.response) {
                toast.error(error.response.data.message || "Failed to clock out.");
            } else {
                toast.error(error.message || "An unexpected error occurred.");
            }
        }
    } finally {
        loadingAction.value = false;
    }
}

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    if (timeStr instanceof Date) {
         return timeStr.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    const today = new Date().toISOString().slice(0, 10);
    const date = new Date(`${today}T${timeStr}`);
    if (isNaN(date.getTime())) return timeStr; 
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
}

const updateClock = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
    currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
}

onMounted(() => {
    fetchSchedule();
    fetchAttendance();
    updateClock();
    timer = setInterval(updateClock, 1000);
})

onUnmounted(() => {
    if(timer) clearInterval(timer);
})
</script>

<style scoped>
.attendance-employee {
  max-width: 1600px;
  margin: 0 auto;
}
</style>