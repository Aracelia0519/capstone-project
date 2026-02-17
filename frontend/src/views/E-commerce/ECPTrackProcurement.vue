<template>
  <div class="procurement-tracking min-h-screen p-4 md:p-6 text-gray-100 font-sans">
    <Toaster position="top-right" theme="dark" />

    <div class="mb-6 md:mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Procurement Tracking</h1>
        <p class="text-gray-400 text-sm md:text-base mt-1">Monitor the status of your purchase requests and logistics.</p>
      </div>
      <Button variant="outline" @click="refreshData" class="w-full md:w-auto bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800 hover:text-white transition-all">
        <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
        Refresh Data
      </Button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-8">
      <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-xs uppercase font-semibold tracking-wider">Total Requests</span>
            <FileText class="w-4 h-4 text-blue-500" />
          </div>
          <div class="text-2xl md:text-3xl font-bold text-white">{{ stats.total_requests || 0 }}</div>
        </CardContent>
      </Card>

      <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-xs uppercase font-semibold tracking-wider">Total Spend</span>
            <DollarSign class="w-4 h-4 text-emerald-500" />
          </div>
          <div class="text-2xl md:text-3xl font-bold text-white">₱{{ formatNumber(stats.total_cost || 0) }}</div>
        </CardContent>
      </Card>

      <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-xs uppercase font-semibold tracking-wider">Pending</span>
            <Clock class="w-4 h-4 text-amber-500" />
          </div>
          <div class="text-2xl md:text-3xl font-bold text-white">{{ stats.status_counts?.pending || 0 }}</div>
        </CardContent>
      </Card>

      <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-gray-400 text-xs uppercase font-semibold tracking-wider">Completed</span>
            <CheckCircle2 class="w-4 h-4 text-purple-500" />
          </div>
          <div class="text-2xl md:text-3xl font-bold text-white">{{ stats.status_counts?.delivered || 0 }}</div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 bg-gray-900 border-gray-800 shadow-xl">
      <CardContent class="p-4 md:p-5">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-500" />
            <Input 
              v-model="filters.search" 
              placeholder="Search Request ID, Product, or Supplier..." 
              class="pl-9 bg-gray-950 border-gray-800 text-white placeholder:text-gray-600 focus-visible:ring-blue-600"
              @keyup.enter="fetchRequests(1)"
            />
          </div>
          <div class="flex gap-2 w-full md:w-auto">
            <Select v-model="filters.status" @update:modelValue="fetchRequests(1)">
              <SelectTrigger class="w-full md:w-[180px] bg-gray-950 border-gray-800 text-white">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent class="bg-gray-900 border-gray-800 text-white">
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="approved">Approved</SelectItem>
                <SelectItem value="processing">Processing</SelectItem>
                <SelectItem value="shipped">Shipped</SelectItem>
                <SelectItem value="delivered">Delivered</SelectItem>
                <SelectItem value="rejected">Rejected</SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="filters.priority" @update:modelValue="fetchRequests(1)">
              <SelectTrigger class="w-full md:w-[160px] bg-gray-950 border-gray-800 text-white">
                <SelectValue placeholder="All Priority" />
              </SelectTrigger>
              <SelectContent class="bg-gray-900 border-gray-800 text-white">
                <SelectItem value="all">All Priority</SelectItem>
                <SelectItem value="high">High</SelectItem>
                <SelectItem value="medium">Medium</SelectItem>
                <SelectItem value="low">Low</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="bg-gray-900 border-gray-800 overflow-hidden shadow-xl mb-6">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-950/50">
            <TableRow class="hover:bg-transparent border-gray-800">
              <TableHead class="text-gray-400 font-medium">Request Code</TableHead>
              <TableHead class="text-gray-400 font-medium">Product Details</TableHead>
              <TableHead class="text-gray-400 font-medium">Supplier</TableHead>
              <TableHead class="text-gray-400 font-medium">Status</TableHead>
              <TableHead class="text-gray-400 font-medium text-right">Cost</TableHead>
              <TableHead class="text-gray-400 font-medium text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading" class="border-gray-800">
              <TableCell colspan="6" class="h-32 text-center text-gray-500">
                <div class="flex items-center justify-center gap-2">
                  <RefreshCw class="w-4 h-4 animate-spin" /> Loading records...
                </div>
              </TableCell>
            </TableRow>
            
            <TableRow v-else-if="requests.length === 0" class="border-gray-800">
              <TableCell colspan="6" class="h-32 text-center text-gray-500">
                No procurement records found.
              </TableCell>
            </TableRow>

            <TableRow 
              v-else 
              v-for="req in requests" 
              :key="req.id" 
              class="border-gray-800 hover:bg-gray-800/30 transition-colors group"
            >
              <TableCell>
                <div class="font-mono text-white font-medium">{{ req.request_code }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ formatDate(req.created_at) }}</div>
              </TableCell>
              
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded bg-gray-800 flex items-center justify-center text-gray-400">
                    <Package class="w-4 h-4" />
                  </div>
                  <div>
                    <div class="text-white font-medium text-sm">{{ req.product_name }}</div>
                    <div class="text-xs text-gray-500">{{ req.category }} • Qty: {{ req.quantity }}</div>
                  </div>
                </div>
              </TableCell>

              <TableCell>
                <div class="flex items-center gap-2 text-gray-300 text-sm">
                  <Building2 class="w-3 h-3 text-gray-500" /> {{ req.supplier }}
                </div>
              </TableCell>

              <TableCell>
                <div class="flex flex-col items-start gap-1.5">
                  <Badge :class="getStatusColor(req.status)" class="rounded-md capitalize border-0 px-2 py-0.5">
                    {{ req.status }}
                  </Badge>
                  <div v-if="req.priority === 'high'" class="flex items-center text-xs text-red-400 font-medium">
                    <AlertCircle class="w-3 h-3 mr-1" /> High Priority
                  </div>
                </div>
              </TableCell>

              <TableCell class="text-right">
                <div class="text-emerald-400 font-medium">₱{{ formatNumber(req.total_cost) }}</div>
                <div class="text-xs text-gray-500">₱{{ formatNumber(req.unit_price) }} / unit</div>
              </TableCell>

              <TableCell class="text-right">
                <Button variant="ghost" size="sm" class="h-8 w-8 p-0 text-gray-400 hover:text-white hover:bg-gray-800" @click="viewDetails(req)">
                  <Eye class="w-4 h-4" />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="p-4 border-t border-gray-800 flex items-center justify-between">
        <div class="text-xs text-gray-500">
          Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
        </div>
        <div class="flex gap-2">
          <Button 
            variant="outline" 
            size="sm" 
            :disabled="!pagination.prev_page_url" 
            @click="fetchRequests(pagination.current_page - 1)"
            class="bg-transparent border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800"
          >
            Previous
          </Button>
          <Button 
            variant="outline" 
            size="sm" 
            :disabled="!pagination.next_page_url" 
            @click="fetchRequests(pagination.current_page + 1)"
            class="bg-transparent border-gray-800 text-gray-400 hover:text-white hover:bg-gray-800"
          >
            Next
          </Button>
        </div>
      </div>
    </Card>

    <Dialog :open="!!selectedRequest" @update:open="(val) => !val && closeDetails()">
      <DialogContent class="bg-gray-950 border-gray-800 text-gray-100 sm:max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader class="border-b border-gray-800 pb-4">
          <DialogTitle class="flex items-center gap-3 text-xl">
            Request Details
            <Badge variant="outline" class="font-mono text-xs border-gray-700 text-gray-400">
              {{ selectedRequest?.request_code }}
            </Badge>
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Full information about this procurement request.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="mt-6 space-y-6">
          <div class="flex items-center justify-between bg-gray-900 p-4 rounded-lg border border-gray-800">
             <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Current Status</p>
                <Badge :class="getStatusColor(selectedRequest.status)" class="text-sm px-3 py-1 capitalize border-0">
                  {{ selectedRequest.status }}
                </Badge>
             </div>
             <div class="text-right">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Total Cost</p>
                <p class="text-xl font-bold text-white">₱{{ formatNumber(selectedRequest.total_cost) }}</p>
             </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <h3 class="text-sm font-semibold text-gray-200 flex items-center gap-2">
                <Package class="w-4 h-4 text-blue-500" /> Product Information
              </h3>
              <div class="bg-gray-900/50 rounded-lg p-3 space-y-2 text-sm border border-gray-800">
                <div class="flex justify-between">
                  <span class="text-gray-500">Name:</span>
                  <span class="text-gray-300 font-medium">{{ selectedRequest.product_name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Category:</span>
                  <span class="text-gray-300">{{ selectedRequest.category }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Quantity:</span>
                  <span class="text-gray-300">{{ selectedRequest.quantity }} units</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Required By:</span>
                  <span class="text-gray-300">{{ selectedRequest.required_by_date || 'N/A' }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <h3 class="text-sm font-semibold text-gray-200 flex items-center gap-2">
                <Truck class="w-4 h-4 text-purple-500" /> Logistics
              </h3>
              <div class="bg-gray-900/50 rounded-lg p-3 space-y-2 text-sm border border-gray-800">
                <div class="flex justify-between">
                  <span class="text-gray-500">Shipping:</span>
                  <span class="text-gray-300 capitalize">{{ selectedRequest.shipping_method }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Terms:</span>
                  <span class="text-gray-300 capitalize">{{ selectedRequest.payment_terms }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Supplier:</span>
                  <span class="text-gray-300">{{ selectedRequest.supplier }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-2">
             <h3 class="text-sm font-semibold text-gray-200 flex items-center gap-2">
                <MapPin class="w-4 h-4 text-red-500" /> Delivery Address
             </h3>
             <div class="bg-gray-900/50 rounded-lg p-3 text-sm text-gray-300 border border-gray-800 leading-relaxed">
               {{ selectedRequest.delivery_address }}
             </div>
          </div>

          <div v-if="selectedRequest.instructions" class="space-y-2">
             <h3 class="text-sm font-semibold text-gray-200 flex items-center gap-2">
                <FileText class="w-4 h-4 text-gray-500" /> Special Instructions
             </h3>
             <div class="bg-gray-900/50 rounded-lg p-3 text-sm text-gray-400 italic border border-gray-800">
               "{{ selectedRequest.instructions }}"
             </div>
          </div>

          <div v-if="selectedRequest.status === 'rejected' && selectedRequest.rejection_reason" class="mt-4 p-3 bg-red-950/30 border border-red-900/50 rounded-lg">
             <p class="text-xs text-red-400 font-bold uppercase mb-1">Rejection Reason</p>
             <p class="text-sm text-red-200">{{ selectedRequest.rejection_reason }}</p>
          </div>
        </div>

        <DialogFooter class="mt-6 pt-4 border-t border-gray-800 sm:justify-end">
          <Button @click="closeDetails" class="w-full sm:w-auto bg-gray-800 text-white hover:bg-gray-700">
            Close
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import api from '@/utils/axios' // Assuming this path based on your prompt
import { 
  Search, RefreshCw, Filter, Eye, FileText, DollarSign, 
  Clock, CheckCircle2, Package, Building2, Truck, MapPin, AlertCircle 
} from 'lucide-vue-next'
import { Toaster, toast } from 'vue-sonner'

// Shadcn Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { 
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow 
} from '@/components/ui/table'
import { 
  Select, SelectContent, SelectItem, SelectTrigger, SelectValue 
} from '@/components/ui/select'
import {
  Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
} from '@/components/ui/dialog'

// State
const loading = ref(false)
const requests = ref([])
const stats = ref({})
const selectedRequest = ref(null)

const filters = reactive({
  search: '',
  status: 'all',
  priority: 'all'
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  from: 0,
  to: 0,
  prev_page_url: null,
  next_page_url: null
})

// --- API Calls ---

const fetchRequests = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: 10,
      search: filters.search,
      sort_by: 'created_at',
      sort_order: 'desc'
    }

    if (filters.status !== 'all') params.status = filters.status
    if (filters.priority !== 'all') params.priority = filters.priority

    // Calling the endpoint defined in api.php: Route::prefix('procurement')->get('/requests', ...)
    const response = await api.get('/procurement/requests', { params })

    if (response.data.success) {
      const data = response.data.data
      requests.value = data.data
      
      // Update pagination state
      pagination.current_page = data.current_page
      pagination.last_page = data.last_page
      pagination.total = data.total
      pagination.from = data.from
      pagination.to = data.to
      pagination.prev_page_url = data.prev_page_url
      pagination.next_page_url = data.next_page_url
    }
  } catch (error) {
    console.error('Error fetching requests:', error)
    toast.error('Failed to load procurement records')
  } finally {
    loading.value = false
  }
}

const fetchStats = async () => {
  try {
    // Calling endpoint: Route::prefix('procurement')->get('/statistics', ...)
    const response = await api.get('/procurement/statistics')
    if (response.data.success) {
      stats.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
  }
}

// --- Helpers ---

const refreshData = () => {
  fetchRequests(1)
  fetchStats()
  toast.success('Data refreshed')
}

const viewDetails = (req) => {
  selectedRequest.value = req
}

const closeDetails = () => {
  selectedRequest.value = null
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric'
  })
}

const formatNumber = (num) => {
  return Number(num).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const getStatusColor = (status) => {
  const map = {
    pending: 'bg-amber-500/20 text-amber-300',
    approved: 'bg-blue-500/20 text-blue-300',
    processing: 'bg-indigo-500/20 text-indigo-300',
    shipped: 'bg-purple-500/20 text-purple-300',
    delivered: 'bg-emerald-500/20 text-emerald-300',
    rejected: 'bg-red-500/20 text-red-300',
    cancelled: 'bg-gray-500/20 text-gray-400'
  }
  return map[status?.toLowerCase()] || 'bg-gray-800 text-gray-300'
}

// --- Lifecycle ---

onMounted(() => {
  fetchRequests()
  fetchStats()
})

// Debounce search slightly (optional optimization) or just rely on Enter key
watch(() => filters.search, (newVal) => {
  if (newVal === '') fetchRequests(1)
})
</script>

<style scoped>
/* Custom scrollbar for table container if needed */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}
.overflow-x-auto::-webkit-scrollbar-track {
  background: #111827;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #374151;
  border-radius: 4px;
}
</style>