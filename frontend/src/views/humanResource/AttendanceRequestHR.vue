<template>
  <div class="min-h-screen bg-gray-50/50 p-4 md:p-6">
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Attendance Requests</h1>
      <p class="text-gray-600">Review and manage employee time adjustment and overtime requests</p>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <Card class="border-l-4 border-l-blue-500 shadow-md">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-blue-50 rounded-lg">
                <FileText class="w-6 h-6 text-blue-600" />
              </div>
              <span class="text-sm font-medium text-blue-600 bg-blue-50 px-2 py-1 rounded-full">Total</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ requests.length }}</h3>
            <p class="text-gray-600 text-sm">All Records</p>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-yellow-500 shadow-md">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-yellow-50 rounded-lg">
                <Clock class="w-6 h-6 text-yellow-600" />
              </div>
              <span class="text-sm font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full">Action Needed</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ counts.pending }}</h3>
            <p class="text-gray-600 text-sm">Pending Review</p>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-green-500 shadow-md">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-green-50 rounded-lg">
                <CheckCircle2 class="w-6 h-6 text-green-600" />
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ counts.approved }}</h3>
            <p class="text-gray-600 text-sm">Approved</p>
          </CardContent>
        </Card>

        <Card class="border-l-4 border-l-red-500 shadow-md">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-red-50 rounded-lg">
                <XCircle class="w-6 h-6 text-red-600" />
              </div>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ counts.rejected }}</h3>
            <p class="text-gray-600 text-sm">Rejected Requests</p>
          </CardContent>
        </Card>
      </div>

      <Card class="shadow-md border-0">
        <CardHeader class="border-b bg-white px-6 py-5">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle class="text-xl text-gray-800">Request List</CardTitle>
              <CardDescription class="mt-1">
                Detailed view of all attendance adjustments
              </CardDescription>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
              <div class="relative">
                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" />
                <Input 
                  class="pl-9 w-full sm:w-[250px] bg-gray-50 border-gray-200 focus:bg-white transition-colors" 
                  placeholder="Search employee..." 
                  v-model="searchQuery"
                />
              </div>
              <div class="flex bg-gray-100 p-1 rounded-md">
                <button 
                  v-for="status in ['All', 'Pending', 'Approved', 'Rejected']" 
                  :key="status"
                  @click="filterStatus = status"
                  :class="[
                    'px-3 py-1.5 text-sm font-medium rounded-sm transition-all',
                    filterStatus === status 
                      ? 'bg-white text-gray-900 shadow-sm' 
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200/50'
                  ]"
                >
                  {{ status }}
                </button>
              </div>
            </div>
          </div>
        </CardHeader>
        
        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-gray-50/50">
              <TableRow class="hover:bg-transparent border-gray-100">
                <TableHead class="w-[250px] py-4 pl-6 font-semibold text-gray-600">Employee</TableHead>
                <TableHead class="font-semibold text-gray-600">Request Details</TableHead>
                <TableHead class="font-semibold text-gray-600">Reason</TableHead>
                <TableHead class="font-semibold text-gray-600">Status</TableHead>
                <TableHead class="text-right pr-6 font-semibold text-gray-600">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="filteredRequests.length === 0">
                <TableCell colspan="5" class="h-32 text-center text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <p>No requests found matching your filters.</p>
                    <p class="text-xs mt-2 text-gray-400">Status: {{ loadingStatus }}</p>
                  </div>
                </TableCell>
              </TableRow>
              <TableRow 
                v-for="request in filteredRequests" 
                :key="request.id"
                class="border-gray-100 hover:bg-gray-50/50 transition-colors"
              >
                <TableCell class="font-medium pl-6">
                  <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xs">
                      {{ getInitials(request.employeeName) }}
                    </div>
                    <div>
                      <span class="block text-gray-700">{{ request.employeeName }}</span>
                      <span class="text-xs text-gray-400">ID: {{ request.employeeCode }}</span>
                    </div>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="flex flex-col">
                    <span class="text-sm font-medium text-gray-700">{{ formatDate(request.requested_time) }}</span>
                    <span class="text-xs text-indigo-600 font-medium bg-indigo-50 w-fit px-1.5 py-0.5 rounded mt-0.5">
                      {{ request.type }}
                    </span>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="max-w-[300px]">
                    <p class="text-sm text-gray-600 truncate" :title="request.reason">
                      {{ request.reason }}
                    </p>
                    <button 
                      v-if="request.photo_proof" 
                      @click="openImageModal(request.photo_proof)" 
                      class="text-xs text-blue-500 hover:text-blue-700 hover:underline flex items-center gap-1 mt-1 focus:outline-none"
                    >
                      <FileText class="w-3 h-3" /> View Proof
                    </button>
                  </div>
                </TableCell>

                <TableCell>
                  <Badge 
                    variant="outline" 
                    class="rounded-full px-3 py-0.5 font-normal border-0"
                    :class="getStatusColor(request.status)"
                  >
                    {{ request.status }}
                  </Badge>
                </TableCell>

                <TableCell class="text-right pr-6">
                  <div class="flex justify-end gap-2" v-if="request.status === 'Pending'">
                    <Button 
                      size="sm" 
                      class="h-8 bg-green-50 text-green-700 hover:bg-green-100 border-green-200 shadow-none"
                      variant="outline"
                      :disabled="isProcessing === request.id"
                      @click="handleAction(request.id, 'approve')"
                    >
                      <span v-if="isProcessing === request.id" class="animate-spin mr-1">...</span>
                      <Check v-else class="h-4 w-4 mr-1" /> 
                      Approve
                    </Button>
                    <Button 
                      size="sm" 
                      class="h-8 bg-red-50 text-red-700 hover:bg-red-100 border-red-200 shadow-none"
                      variant="outline"
                      :disabled="isProcessing === request.id"
                      @click="handleAction(request.id, 'reject')"
                    >
                      <X v-if="isProcessing !== request.id" class="h-4 w-4 mr-1" /> 
                      Reject
                    </Button>
                  </div>
                  <div v-else class="text-xs text-gray-400 font-medium px-2">
                    <span v-if="request.admin_remarks" class="block text-gray-500 italic text-[10px]">
                      {{ request.admin_remarks }}
                    </span>
                    {{ request.status === 'Approved' ? 'Completed' : 'Closed' }}
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>

    <Transition 
      enter-active-class="transition ease-out duration-300" 
      enter-from-class="opacity-0" 
      enter-to-class="opacity-100" 
      leave-active-class="transition ease-in duration-200" 
      leave-from-class="opacity-100" 
      leave-to-class="opacity-0"
    >
      <div 
        v-if="showImageModal" 
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
        @click="closeImageModal"
      >
        <div class="relative max-w-5xl w-full max-h-[90vh] flex flex-col items-center justify-center p-2" @click.stop>
          <button 
            @click="closeImageModal" 
            class="absolute -top-10 right-0 md:absolute md:-top-6 md:-right-6 bg-white/10 hover:bg-white/20 text-white rounded-full p-2 transition-colors"
          >
            <X class="w-6 h-6" />
          </button>
          
          <img 
            :src="selectedImageUrl" 
            alt="Proof Document" 
            class="w-auto h-auto max-w-full max-h-[85vh] object-contain rounded-md shadow-2xl border border-white/10"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { toast } from 'vue-sonner'
import { 
  Check, X, FileText, Clock, CheckCircle2, XCircle, Search 
} from 'lucide-vue-next'

// Import shadcn-vue components
import { 
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow 
} from '@/components/ui/table'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'

// 1. SETUP API CLIENT WITH CORRECT BASE URL
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000', // Forces requests to Laravel Backend
  withCredentials: true,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json'
  }
})

// 2. ATTACH TOKEN INTERCEPTOR
api.interceptors.request.use(config => {
  // Check common storage keys for the token
  const token = localStorage.getItem('token') || localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Types
type RequestStatus = 'Pending' | 'Approved' | 'Rejected'

interface Employee {
  first_name: string
  last_name: string
  employee_code?: string
}

interface AttendanceRequest {
  id: string
  employee_id: number
  employee: Employee
  requested_time: string 
  type: string
  reason: string
  photo_proof?: string
  status: RequestStatus
  admin_remarks?: string
  // Computed properties
  employeeName?: string
  employeeCode?: string
}

// State
const requests = ref<AttendanceRequest[]>([])
const isLoading = ref(true)
const isProcessing = ref<string | null>(null)
const searchQuery = ref('')
const filterStatus = ref<'All' | RequestStatus | string>('All')
const loadingStatus = ref('Initializing...')

// Modal State
const showImageModal = ref(false)
const selectedImageUrl = ref('')

// Fetch Data
const fetchRequests = async () => {
  isLoading.value = true
  loadingStatus.value = 'Fetching data...'
  
  try {
    console.log('Fetching from ' + api.defaults.baseURL + '/api/hr/attendance-requests')
    const response = await api.get('/api/hr/attendance-requests')
    
    console.log('API Response:', response.data)

    if (response.data.status === 'success') {
      requests.value = response.data.data.map((req: any) => ({
        ...req,
        employeeName: req.employee ? `${req.employee.first_name} ${req.employee.last_name}` : 'Unknown Employee',
        employeeCode: req.employee?.employee_code || 'N/A'
      }))
      loadingStatus.value = 'Data loaded'
    } else {
      loadingStatus.value = 'Failed: ' + (response.data.message || 'Unknown error')
      toast.error('Failed to load: ' + response.data.message)
    }
  } catch (error: any) {
    console.error('Error fetching requests:', error)
    
    let errorMsg = 'Failed to load requests'
    if (error.response) {
      if (error.response.status === 401) errorMsg = 'Unauthorized: Please login again'
      if (error.response.status === 403) errorMsg = 'Access Denied: ' + (error.response.data.message || '')
      if (error.response.data?.message) errorMsg = error.response.data.message
      
      // If we still get HTML, it means the URL is wrong or server is down
      if (typeof error.response.data === 'string' && error.response.data.includes('<!DOCTYPE html>')) {
        errorMsg = 'Server Error: Received HTML instead of JSON. Check API URL.'
      }
    } else if (error.request) {
      errorMsg = 'No response from server. Is the Laravel backend running on port 8000?'
    }

    loadingStatus.value = 'Error: ' + errorMsg
    toast.error(errorMsg)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchRequests()
})

// Computed Counts
const counts = computed(() => {
  return {
    pending: requests.value.filter(r => r.status === 'Pending').length,
    approved: requests.value.filter(r => r.status === 'Approved').length,
    rejected: requests.value.filter(r => r.status === 'Rejected').length
  }
})

// Computed Filtered List
const filteredRequests = computed(() => {
  return requests.value.filter(req => {
    const name = req.employeeName || ''
    const matchesSearch = name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesStatus = filterStatus.value === 'All' || req.status === filterStatus.value
    return matchesSearch && matchesStatus
  })
})

// Actions
const handleAction = async (id: string, action: 'approve' | 'reject') => {
  isProcessing.value = id
  try {
    const response = await api.post(`/api/hr/attendance-requests/${id}/${action}`, {
      remarks: action === 'approve' ? 'Approved by HR' : 'Rejected by HR'
    })

    if (response.data.status === 'success') {
      toast.success(`Request ${action}d successfully`)
      
      const index = requests.value.findIndex(r => r.id === id)
      if (index !== -1) {
        requests.value[index].status = action === 'approve' ? 'Approved' : 'Rejected'
        requests.value[index].admin_remarks = action === 'approve' ? 'Approved by HR' : 'Rejected by HR'
      }
    }
  } catch (error: any) {
    console.error(`Error ${action}ing request:`, error)
    const msg = error.response?.data?.message || `Failed to ${action} request`
    toast.error(msg)
  } finally {
    isProcessing.value = null
  }
}

// Utilities
const formatDate = (dateString: string) => {
  if (!dateString) return 'Invalid Date'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    hour12: true
  }).format(date)
}

const getInitials = (name?: string) => {
  if (!name) return '??'
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const getStatusColor = (status: RequestStatus) => {
  switch (status) {
    case 'Approved': 
      return 'bg-green-100 text-green-700 hover:bg-green-200'
    case 'Rejected': 
      return 'bg-red-100 text-red-700 hover:bg-red-200'
    case 'Pending': 
      return 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200'
    default: 
      return 'bg-gray-100 text-gray-800'
  }
}

const getImageUrl = (path: string) => {
  // Use the backend URL for images too
  return `http://127.0.0.1:8000/storage/${path}`
}

// Modal Handlers
const openImageModal = (path: string) => {
  selectedImageUrl.value = getImageUrl(path)
  showImageModal.value = true
}

const closeImageModal = () => {
  showImageModal.value = false
  // Optional: clear image after animation to prevent flicker next time
  setTimeout(() => {
    selectedImageUrl.value = ''
  }, 300)
}
</script>