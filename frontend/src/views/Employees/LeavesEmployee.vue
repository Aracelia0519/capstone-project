<template>
  <div class="leaves-employee p-4 md:p-6">
    <Toaster position="top-center" />

    <AlertDialog v-model:open="showConfirmDialog">
      <AlertDialogContent class="z-[200]">
        <AlertDialogHeader>
          <AlertDialogTitle>Submit Leave Request?</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to submit this leave request for {{ form.duration }} day(s)?
            This action cannot be undone immediately.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showConfirmDialog = false">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="submitRequest" class="bg-indigo-600 hover:bg-indigo-700 text-white">
            Confirm Submit
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog v-model:open="showDetailsModal">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle class="text-xl font-semibold">Leave Request Details</DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedLeave" class="space-y-4 py-4">
          <div class="flex justify-between items-start border-b pb-4">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Status</p>
              <Badge :class="['rounded-full px-3 py-1 font-semibold shadow-none', getStatusClass(selectedLeave.status)]">
                {{ selectedLeave.status }}
              </Badge>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Payment Status</p>
              <Badge :class="selectedLeave.is_paid ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                {{ selectedLeave.is_paid ? 'Paid Leave' : 'Unpaid Leave' }}
              </Badge>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label class="text-xs text-gray-500 uppercase">Leave Type</Label>
              <p class="font-medium text-gray-800">{{ formatType(selectedLeave.type) }}</p>
            </div>
            <div>
              <Label class="text-xs text-gray-500 uppercase">Duration</Label>
              <p class="font-medium text-gray-800">{{ selectedLeave.duration }} Day(s)</p>
            </div>
            <div>
              <Label class="text-xs text-gray-500 uppercase">Start Date</Label>
              <p class="text-sm">{{ formatDate(selectedLeave.start_date) }}</p>
            </div>
            <div>
              <Label class="text-xs text-gray-500 uppercase">End Date</Label>
              <p class="text-sm">{{ formatDate(selectedLeave.end_date) }}</p>
            </div>
            <div>
              <Label class="text-xs text-gray-500 uppercase">Date Filed</Label>
              <p class="text-sm">{{ formatDate(selectedLeave.created_at) }}</p>
            </div>
          </div>

          <div class="bg-gray-50 p-3 rounded-md">
            <Label class="text-xs text-gray-500 uppercase mb-1 block">Reason for Leave</Label>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ selectedLeave.reason }}</p>
          </div>

          <div v-if="selectedLeave.status === 'Rejected' && selectedLeave.rejection_reason" class="bg-red-50 p-3 rounded-md border border-red-100">
            <Label class="text-xs text-red-600 uppercase mb-1 block">Rejection Reason</Label>
            <p class="text-sm text-red-700">{{ selectedLeave.rejection_reason }}</p>
          </div>
        </div>

        <div class="flex justify-end pt-2">
          <Button variant="outline" @click="showDetailsModal = false">Close</Button>
        </div>
      </DialogContent>
    </Dialog>

    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Leave Management</h1>
          <p class="text-gray-500 mt-1">Request and track your leaves</p>
        </div>
        
        <Dialog v-model:open="showRequestModal">
          <DialogTrigger as-child>
            <Button class="mt-4 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg h-12 px-6 text-base">
              <i class="fas fa-plus mr-2"></i>
              File Leave Request
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
              <DialogTitle class="text-xl font-semibold">File Leave Request</DialogTitle>
            </DialogHeader>
            <div class="space-y-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Leave Type</Label>
                  <Select v-model="form.type">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="Select type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="vacation">Vacation Leave</SelectItem>
                      <SelectItem value="sick">Sick Leave</SelectItem>
                      <SelectItem value="emergency">Emergency Leave</SelectItem>
                      <SelectItem value="maternity">Maternity Leave</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Duration (Days)</Label>
                  <Input type="number" step="0.5" v-model="form.duration" placeholder="Auto-calculated" readonly />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Start Date</Label>
                  <Input type="date" v-model="form.start_date" :min="minDate" />
                  <p class="text-xs text-gray-500 mt-1">Must be at least tomorrow</p>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">End Date</Label>
                  <Input type="date" v-model="form.end_date" :min="form.start_date || minDate" />
                </div>
              </div>
              
              <div>
                <Label class="text-sm font-medium text-gray-700 mb-2">Reason</Label>
                <Textarea rows="4" v-model="form.reason" placeholder="Briefly describe the reason for leave" />
              </div>

              <div v-if="errorMessage" class="text-red-500 text-sm p-2 bg-red-50 rounded">
                {{ errorMessage }}
              </div>
              
              <div class="flex justify-end space-x-4 pt-2">
                <Button variant="outline" @click="showRequestModal = false" :disabled="isSubmitting">
                  Cancel
                </Button>
                <Button class="bg-indigo-600 hover:bg-indigo-700 text-white" @click="confirmSubmit" :disabled="isSubmitting">
                  <span v-if="isSubmitting">Submitting...</span>
                  <span v-else>Submit Request</span>
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Card v-for="balance in leaveBalances" :key="balance.type" class="shadow-lg border-0">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ balance.type }}</h3>
                <div class="mt-2">
                  <span class="text-2xl font-bold text-gray-800">{{ balance.available }}</span>
                  <span class="text-sm text-gray-500 ml-2">of {{ balance.total }} days</span>
                </div>
              </div>
              <div :class="['p-3 rounded-lg', balance.colorClass]">
                <i :class="[balance.icon, 'text-xl']"></i>
              </div>
            </div>
            <div class="mt-4">
              <Progress :model-value="balance.percentage" class="h-2 bg-gray-200" :class="[balance.progressClass]" />
              <p class="text-xs text-gray-500 mt-2">{{ balance.percentage }}% available</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <Tabs v-model="activeTab" class="w-full">
        <div class="mb-8 border-b border-gray-200">
          <TabsList class="bg-transparent h-auto p-0 space-x-8 justify-start">
            <TabsTrigger 
              v-for="tab in tabs" 
              :key="tab.id" 
              :value="tab.id"
              class="py-3 px-1 rounded-none border-b-2 border-transparent data-[state=active]:border-indigo-600 data-[state=active]:text-indigo-600 text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition-colors shadow-none"
            >
              <i :class="tab.icon + ' mr-2'"></i>
              {{ tab.label }}
            </TabsTrigger>
          </TabsList>
        </div>

        <Card class="shadow-lg border-0">
          <CardContent class="p-6">
            <TabsContent value="history" class="mt-0">
              <h3 class="text-lg font-semibold text-gray-800 mb-6">Leave History</h3>
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader class="bg-gray-50">
                    <TableRow>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Date Filed</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Leave Type</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Dates</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Duration</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Status</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider text-right">Actions</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-if="leaveHistory.length === 0">
                      <TableCell colspan="6" class="text-center py-4 text-gray-500">No leave requests found.</TableCell>
                    </TableRow>
                    <TableRow v-for="leave in leaveHistory" :key="leave.id">
                      <TableCell>{{ formatDate(leave.created_at) }}</TableCell>
                      <TableCell>
                        <Badge :class="['rounded-full px-2 py-1 font-semibold shadow-none', getTypeClass(leave.type)]">
                          {{ formatType(leave.type) }}
                        </Badge>
                      </TableCell>
                      <TableCell>{{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}</TableCell>
                      <TableCell>{{ leave.duration }} day(s)</TableCell>
                      <TableCell>
                        <Badge :class="[
                          'rounded-full px-3 py-1 font-semibold shadow-none',
                          getStatusClass(leave.status)
                        ]">
                          {{ leave.status }}
                        </Badge>
                      </TableCell>
                      <TableCell class="text-right">
                         <Button variant="ghost" size="sm" @click="viewDetails(leave)" class="text-gray-500 hover:text-indigo-600 hover:bg-indigo-50">
                            <i class="fas fa-eye mr-1"></i> Details
                         </Button>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </TabsContent>

            <TabsContent value="request" class="mt-0">
              <h3 class="text-lg font-semibold text-gray-800 mb-6">File Leave Request</h3>
              <div class="max-w-2xl space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <Label class="text-sm font-medium text-gray-700 mb-2">Leave Type</Label>
                    <Select v-model="form.type">
                        <SelectTrigger class="w-full">
                        <SelectValue placeholder="Select type" />
                        </SelectTrigger>
                        <SelectContent>
                        <SelectItem value="vacation">Vacation Leave</SelectItem>
                        <SelectItem value="sick">Sick Leave</SelectItem>
                        <SelectItem value="emergency">Emergency Leave</SelectItem>
                        <SelectItem value="maternity">Maternity Leave</SelectItem>
                        </SelectContent>
                    </Select>
                  </div>
                  <div>
                    <Label class="text-sm font-medium text-gray-700 mb-2">Duration (Days)</Label>
                    <Input type="number" step="0.5" v-model="form.duration" placeholder="Auto-calculated" readonly />
                  </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <Label class="text-sm font-medium text-gray-700 mb-2">Start Date</Label>
                    <Input type="date" v-model="form.start_date" :min="minDate" />
                    <p class="text-xs text-gray-500 mt-1">Must be at least tomorrow (Manila Time)</p>
                  </div>
                  <div>
                    <Label class="text-sm font-medium text-gray-700 mb-2">End Date</Label>
                    <Input type="date" v-model="form.end_date" :min="form.start_date || minDate" />
                  </div>
                </div>
                
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Reason</Label>
                  <Textarea rows="4" v-model="form.reason" placeholder="Briefly describe the reason for leave" />
                </div>

                <div v-if="errorMessage" class="text-red-500 text-sm p-2 bg-red-50 rounded">
                  {{ errorMessage }}
                </div>
                
                <div class="flex justify-end space-x-4">
                  <Button variant="outline" @click="resetForm">Cancel</Button>
                  <Button class="bg-indigo-600 hover:bg-indigo-700 text-white" @click="confirmSubmit" :disabled="isSubmitting">
                    <span v-if="isSubmitting">Submitting...</span>
                    <span v-else>Submit Request</span>
                  </Button>
                </div>
              </div>
            </TabsContent>
          </CardContent>
        </Card>
      </Tabs>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, watch, computed } from 'vue'
import api from '@/utils/axios' 
import { Toaster, toast } from 'vue-sonner'

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'

// State
const showRequestModal = ref(false)
const showDetailsModal = ref(false) // NEW: State for details modal
const showConfirmDialog = ref(false)
const activeTab = ref('history')
const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const selectedLeave = ref(null) // NEW: Store selected leave for details

const tabs = [
  { id: 'history', label: 'Leave History', icon: 'fas fa-history' },
  { id: 'request', label: 'File Request', icon: 'fas fa-plus-circle' },
]

const leaveBalances = ref([])
const leaveHistory = ref([])

const form = reactive({
  type: '',
  duration: '',
  start_date: '',
  end_date: '',
  reason: ''
})

// --- Date Validation Logic (Manila Time) ---
const minDate = computed(() => {
  // Create date object for current time
  const now = new Date();
  
  // Format it specifically to Manila time
  const options = { timeZone: 'Asia/Manila', year: 'numeric', month: 'numeric', day: 'numeric' };
  const manilaDateString = now.toLocaleDateString('en-US', options);
  
  // Create a new Date object from the Manila string to perform arithmetic
  const manilaDate = new Date(manilaDateString);
  
  // Add 1 day
  manilaDate.setDate(manilaDate.getDate() + 1);
  
  // Return in YYYY-MM-DD format for the HTML input
  const year = manilaDate.getFullYear();
  const month = String(manilaDate.getMonth() + 1).padStart(2, '0');
  const day = String(manilaDate.getDate()).padStart(2, '0');
  
  return `${year}-${month}-${day}`;
});

// --- Auto-fill Duration Logic ---
watch([() => form.start_date, () => form.end_date], ([newStart, newEnd]) => {
  if (newStart && newEnd) {
    const start = new Date(newStart)
    const end = new Date(newEnd)
    
    if (end >= start) {
      const diffTime = Math.abs(end - start)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1 
      form.duration = diffDays
    } else {
      form.duration = 0 
    }
  }
})

// Helpers
const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString()
}

const formatType = (type) => {
  if (!type) return ''
  return type.charAt(0).toUpperCase() + type.slice(1) + ' Leave'
}

const getTypeClass = (type) => {
  const classes = {
    vacation: 'bg-blue-100 text-blue-800 hover:bg-blue-100',
    sick: 'bg-red-100 text-red-800 hover:bg-red-100',
    emergency: 'bg-amber-100 text-amber-800 hover:bg-amber-100',
    maternity: 'bg-purple-100 text-purple-800 hover:bg-purple-100'
  }
  return classes[type] || 'bg-gray-100'
}

const getStatusClass = (status) => {
  if (status === 'Approved') return 'bg-green-100 text-green-800 hover:bg-green-100'
  if (status === 'Pending') return 'bg-amber-100 text-amber-800 hover:bg-amber-100'
  if (status === 'Rejected') return 'bg-red-100 text-red-800 hover:bg-red-100'
  return 'bg-gray-100'
}

// NEW: Function to open details modal
const viewDetails = (leave) => {
  selectedLeave.value = leave
  showDetailsModal.value = true
}

const resetForm = () => {
  form.type = ''
  form.duration = ''
  form.start_date = ''
  form.end_date = ''
  form.reason = ''
  errorMessage.value = ''
}

// API Calls
const fetchData = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/employee/leaves')
    leaveHistory.value = response.data.history
    leaveBalances.value = response.data.balances
  } catch (error) {
    console.error('Error fetching data:', error)
    toast.error('Failed to load leave data')
  } finally {
    isLoading.value = false
  }
}

// Validation before showing confirmation
const confirmSubmit = () => {
  errorMessage.value = ''
  if (!form.type || !form.duration || !form.start_date || !form.end_date || !form.reason) {
    errorMessage.value = 'Please fill in all fields'
    toast.error('Please fill in all fields')
    return
  }
  
  // Validate Manila Date Requirement
  if (form.start_date < minDate.value) {
    errorMessage.value = 'Start date must be tomorrow or later (Manila Time)'
    toast.error('Start date must be tomorrow or later')
    return
  }

  if (form.duration <= 0) {
    errorMessage.value = 'Invalid duration'
    toast.error('Invalid date range')
    return
  }

  // Show dialog
  showConfirmDialog.value = true
}

const submitRequest = async () => {
  showConfirmDialog.value = false
  errorMessage.value = ''

  try {
    isSubmitting.value = true
    await api.post('/employee/leaves', form)
    
    toast.success('Leave request submitted successfully')
    await fetchData()
    
    showRequestModal.value = false
    activeTab.value = 'history'
    resetForm()
    
  } catch (error) {
    console.error('Error submitting request:', error)
    let msg = 'An error occurred while submitting your request.'
    
    if (error.response && error.response.data && error.response.data.message) {
        msg = error.response.data.message
    }
    
    errorMessage.value = msg
    toast.error(msg)
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.leaves-employee {
  max-width: 1600px;
  margin: 0 auto;
}
</style>