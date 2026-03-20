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
            <h3 class="text-sm font-medium text-gray-400">Total Requests</h3>
            <Package class="w-4 h-4 text-indigo-400" />
          </div>
          <div class="text-2xl font-bold text-white">{{ stats.total }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-400">Pending Approval</h3>
            <Clock class="w-4 h-4 text-amber-400" />
          </div>
          <div class="text-2xl font-bold text-white">{{ stats.pending }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-400">In Transit</h3>
            <Truck class="w-4 h-4 text-blue-400" />
          </div>
          <div class="text-2xl font-bold text-white">{{ stats.inTransit }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-medium text-gray-400">Completed</h3>
            <CheckCircle2 class="w-4 h-4 text-emerald-400" />
          </div>
          <div class="text-2xl font-bold text-white">{{ stats.completed }}</div>
        </CardContent>
      </Card>
    </div>

    <Card class="bg-gray-900/50 border-gray-800 backdrop-blur-sm overflow-hidden">
      <div class="p-4 border-b border-gray-800 bg-gray-900/80 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <div class="relative w-full sm:w-72">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" />
          <Input 
            v-model="searchQuery" 
            placeholder="Search by Request Code or Supplier..." 
            class="pl-9 bg-gray-950 border-gray-800 text-sm focus-visible:ring-indigo-500 h-10"
          />
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
          <Button variant="outline" size="sm" class="bg-gray-950 border-gray-800 hover:bg-gray-800 text-gray-300 w-full sm:w-auto h-10">
            <FileDown class="w-4 h-4 mr-2" />
            Export
          </Button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-900">
            <TableRow class="border-gray-800 hover:bg-transparent">
              <TableHead class="text-gray-400 font-medium py-4">Request Code</TableHead>
              <TableHead class="text-gray-400 font-medium py-4">Supplier</TableHead>
              <TableHead class="text-gray-400 font-medium py-4">Total Items</TableHead>
              <TableHead class="text-gray-400 font-medium py-4">Cost (PHP)</TableHead>
              <TableHead class="text-gray-400 font-medium py-4">Date</TableHead>
              <TableHead class="text-gray-400 font-medium py-4">Status</TableHead>
              <TableHead class="text-right text-gray-400 font-medium py-4">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading && requests.length === 0" class="border-b border-gray-800">
              <TableCell colspan="7" class="py-12 text-center text-gray-500">
                <RefreshCw class="w-6 h-6 animate-spin mx-auto mb-2 opacity-50" />
                Loading requests...
              </TableCell>
            </TableRow>
            <TableRow v-else-if="filteredRequests.length === 0" class="border-b border-gray-800">
              <TableCell colspan="7" class="py-12 text-center text-gray-500">
                No procurement requests found.
              </TableCell>
            </TableRow>
            <TableRow v-for="req in filteredRequests" :key="req.id" class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors">
              <TableCell class="font-medium text-indigo-400 py-4">{{ req.request_code }}</TableCell>
              <TableCell class="text-gray-300 py-4">{{ req.supplier }}</TableCell>
              <TableCell class="text-gray-300 py-4">{{ req.quantity }} Units</TableCell>
              <TableCell class="text-gray-300 py-4">₱{{ formatNumber(req.total_cost) }}</TableCell>
              <TableCell class="text-gray-400 py-4 text-sm">{{ formatDate(req.request_date) }}</TableCell>
              <TableCell class="py-4">
                <Badge :class="getStatusColor(req.status)" class="rounded-full px-2.5 py-0.5 border-0 font-medium capitalize">
                  {{ req.status }}
                </Badge>
              </TableCell>
              <TableCell class="text-right py-4">
                <Button variant="ghost" size="icon" @click="viewDetails(req)" class="hover:bg-gray-800 text-gray-400 hover:text-indigo-400 transition-colors">
                  <Eye class="w-4 h-4" />
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      
      <div class="p-4 border-t border-gray-800 flex items-center justify-between text-sm text-gray-500 bg-gray-900/80">
        <span>Showing {{ filteredRequests.length }} of {{ requests.length }} requests</span>
      </div>
    </Card>

    <div v-if="selectedRequest" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[999] transition-opacity" @click="closeDetails"></div>
    <div v-if="selectedRequest" class="fixed inset-y-0 right-0 w-full md:w-[480px] bg-gray-900 border-l border-gray-800 shadow-2xl flex flex-col z-[999] transform transition-transform duration-300">
      
      <div class="flex items-center justify-between p-4 md:p-6 border-b border-gray-800 bg-gray-900 shrink-0">
        <div>
          <h2 class="text-lg font-bold text-white">{{ selectedRequest.request_code }}</h2>
          <p class="text-sm text-gray-400 mt-0.5">Tracking Details</p>
        </div>
        <button @click="closeDetails" class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto custom-scrollbar">
        <div class="p-4 md:p-6 border-b border-gray-800 bg-gray-950/50">
          <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-6">Status Timeline</h3>
          <div class="relative pl-3 space-y-6">
            <div class="absolute left-4 top-2 bottom-2 w-0.5 bg-gray-800"></div>
            <div v-for="(status, index) in statusHistory" :key="index" class="relative flex items-start gap-4">
              <div class="relative z-10 flex items-center justify-center w-3 h-3 rounded-full mt-1.5" :class="status.completed ? 'bg-indigo-500 ring-4 ring-gray-950' : 'bg-gray-800 ring-4 ring-gray-950'"></div>
              <div>
                <p class="text-sm font-medium" :class="status.completed ? 'text-white' : 'text-gray-500'">{{ status.label }}</p>
                <p v-if="status.date" class="text-xs text-gray-500 mt-1">{{ status.date }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 md:p-6 border-b border-gray-800">
          <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Order Information</h3>
          <div class="space-y-3 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Supplier</span>
              <span class="text-gray-200 font-medium">{{ selectedRequest.supplier }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Order Date</span>
              <span class="text-gray-200">{{ formatDate(selectedRequest.request_date) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Total Items</span>
              <span class="text-gray-200">{{ selectedRequest.quantity }} Units</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Total Cost</span>
              <span class="text-indigo-400 font-semibold">₱{{ formatNumber(selectedRequest.total_cost) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Payment Terms</span>
              <span class="text-gray-200 uppercase">{{ selectedRequest.payment_terms || 'COD' }}</span>
            </div>
          </div>
        </div>

        <div class="p-4 md:p-6 border-b border-gray-800">
          <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Delivery Details</h3>
          <div class="bg-gray-950 rounded-xl p-4 border border-gray-800">
            <div class="flex items-start gap-3">
              <MapPin class="w-5 h-5 text-gray-500 shrink-0 mt-0.5" />
              <div>
                <p class="text-sm text-gray-300 leading-relaxed">{{ selectedRequest.delivery_address || 'No address provided' }}</p>
              </div>
            </div>
            <div v-if="selectedRequest.instructions" class="mt-4 pt-4 border-t border-gray-800">
              <p class="text-xs text-gray-500 mb-1">Instructions / Notes</p>
              <p class="text-sm text-gray-300">{{ selectedRequest.instructions }}</p>
            </div>
          </div>
        </div>

        <div v-if="selectedRequest.fulfillment && (selectedRequest.fulfillment.receipt_file_path || selectedRequest.fulfillment.proof_file_path)" class="p-4 md:p-6 border-b border-gray-800 bg-gray-900/30">
          <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4 flex items-center">
            <FileText class="w-4 h-4 mr-2 text-indigo-400" />
            Supplier Documents
          </h3>
          <div class="space-y-4">
            
            <div v-if="selectedRequest.fulfillment.receipt_file_path">
              <p class="text-xs text-gray-400 mb-2 font-medium">Official Receipt</p>
              
              <div v-if="isDocument(selectedRequest.fulfillment.receipt_file_path)" class="bg-gray-950 p-4 rounded-xl border border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <ExternalLink class="w-6 h-6 text-indigo-400" />
                  <div>
                    <p class="text-sm font-medium text-gray-200">Document File</p>
                    <p class="text-xs text-gray-500">HTML or PDF Document</p>
                  </div>
                </div>
                <a :href="getImageUrl(selectedRequest.fulfillment.receipt_file_path)" target="_blank" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                  Open File
                </a>
              </div>

              <div v-else class="rounded-xl overflow-hidden border border-gray-700 bg-gray-950 flex items-center justify-center p-2 hover:border-indigo-500/50 transition-colors">
                <img :src="getImageUrl(selectedRequest.fulfillment.receipt_file_path)" alt="Receipt" class="max-w-full max-h-64 object-contain rounded-lg" />
              </div>
            </div>

            <div v-if="selectedRequest.fulfillment.proof_file_path">
              <p class="text-xs text-gray-400 mb-2 font-medium">Proof of Preparation/Shipment</p>
              
              <div v-if="isDocument(selectedRequest.fulfillment.proof_file_path)" class="bg-gray-950 p-4 rounded-xl border border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <ExternalLink class="w-6 h-6 text-indigo-400" />
                  <div>
                    <p class="text-sm font-medium text-gray-200">Document File</p>
                    <p class="text-xs text-gray-500">HTML or PDF Document</p>
                  </div>
                </div>
                <a :href="getImageUrl(selectedRequest.fulfillment.proof_file_path)" target="_blank" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded-lg transition-colors">
                  Open File
                </a>
              </div>

              <div v-else class="rounded-xl overflow-hidden border border-gray-700 bg-gray-950 flex items-center justify-center p-2 hover:border-indigo-500/50 transition-colors">
                <img :src="getImageUrl(selectedRequest.fulfillment.proof_file_path)" alt="Proof" class="max-w-full max-h-64 object-contain rounded-lg" />
              </div>
            </div>

            <div v-if="selectedRequest.fulfillment.notes">
              <p class="text-xs text-gray-400 mb-1 font-medium">Supplier Notes</p>
              <p class="text-sm text-gray-300 bg-gray-800/80 p-3 rounded-lg border border-gray-700">{{ selectedRequest.fulfillment.notes }}</p>
            </div>

          </div>
        </div>

        <div class="p-4 md:p-6">
          <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider mb-4">Product Overview</h3>
          <div class="flex items-center gap-4 bg-gray-950 p-3 rounded-xl border border-gray-800">
             <div class="w-12 h-12 rounded-lg bg-gray-800 border border-gray-700 flex items-center justify-center shrink-0 overflow-hidden">
               <img v-if="getProductImage(selectedRequest)" :src="getImageUrl(getProductImage(selectedRequest))" class="w-full h-full object-cover" />
               <Package v-else class="w-6 h-6 text-gray-500" />
             </div>
             <div>
               <p class="text-sm font-medium text-white">{{ selectedRequest.product_name }}</p>
               <p class="text-xs text-gray-500 mt-0.5">{{ selectedRequest.category }}</p>
             </div>
          </div>
        </div>
      </div>

      <div class="p-4 border-t border-gray-800 bg-gray-900 md:hidden shrink-0 mt-auto">
        <Button @click="closeDetails" variant="outline" class="w-full bg-gray-800 border-gray-700 text-white hover:bg-gray-700 hover:text-white">
          Close Details
        </Button>
      </div>

    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { Search, MapPin, Package, Truck, CheckCircle2, Clock, FileDown, Eye, RefreshCw, FileText, ExternalLink } from 'lucide-vue-next'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

const loading = ref(false)
const requests = ref([])
const searchQuery = ref('')
const selectedRequest = ref(null)

const stats = computed(() => {
  return {
    total: requests.value.length,
    pending: requests.value.filter(r => ['pending', 'd-approved', 'ready'].includes(r.status?.toLowerCase())).length,
    inTransit: requests.value.filter(r => ['processing', 'prepared', 'shipped'].includes(r.status?.toLowerCase())).length,
    completed: requests.value.filter(r => ['delivered', 'approved'].includes(r.status?.toLowerCase())).length
  }
})

const filteredRequests = computed(() => {
  if (!searchQuery.value) return requests.value
  const q = searchQuery.value.toLowerCase()
  return requests.value.filter(r => 
    (r.request_code && r.request_code.toLowerCase().includes(q)) || 
    (r.supplier && r.supplier.toLowerCase().includes(q))
  )
})

const statusHistory = computed(() => {
  if (!selectedRequest.value) return []
  const req = selectedRequest.value
  const currentStatus = req.status?.toLowerCase()
  
  // Simplified linear logic for UI presentation
  let level = 0;
  if (currentStatus === 'pending') level = 1
  else if (currentStatus === 'd-approved') level = 2
  else if (currentStatus === 'ready') level = 3
  else if (currentStatus === 'processing' || currentStatus === 'prepared') level = 4
  else if (currentStatus === 'shipped') level = 5
  else if (currentStatus === 'delivered' || currentStatus === 'approved') level = 6

  return [
    { label: 'Request Submitted', completed: level >= 1, date: req.created_at ? formatDate(req.created_at) : null },
    { label: 'Distributor Approval', completed: level >= 2, date: null },
    { label: 'Budget Released', completed: level >= 3, date: null },
    { label: 'Supplier Processing', completed: level >= 4, date: null },
    { label: 'In Transit', completed: level >= 5, date: req.shipped_at ? formatDate(req.shipped_at) : null },
    { label: 'Delivered', completed: level >= 6, date: req.delivered_at ? formatDate(req.delivered_at) : null }
  ]
})

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/procurement/requests')
    if (response.data.success) {
      requests.value = response.data.data.data || response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch tracking data:', error)
    toast.error('Failed to load tracking data.')
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  fetchRequests()
}

onMounted(() => {
  fetchRequests()
})

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
    'd-approved': 'bg-orange-500/20 text-orange-300',
    ready: 'bg-blue-500/20 text-blue-300',
    processing: 'bg-indigo-500/20 text-indigo-300',
    prepared: 'bg-indigo-500/20 text-indigo-300',
    shipped: 'bg-purple-500/20 text-purple-300',
    delivered: 'bg-emerald-500/20 text-emerald-300',
    approved: 'bg-emerald-500/20 text-emerald-300',
    rejected: 'bg-red-500/20 text-red-300',
    cancelled: 'bg-gray-500/20 text-gray-400'
  }
  return map[status?.toLowerCase()] || 'bg-gray-800 text-gray-300'
}

// Global Image URL Formatter - Intelligently switches between local and prod
const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  
  // Detect if we are running on local development
  const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
  
  // Use VITE_API_URL if available, otherwise intelligently guess
  let baseUrl = import.meta.env.VITE_API_URL;
  
  if (!baseUrl) {
    baseUrl = isLocalhost ? 'http://localhost:8000' : 'https://api.capstone001.com';
  }
  
  // Remove trailing slashes from base URL and leading slashes from path to prevent //
  baseUrl = baseUrl.replace(/\/+$/, '');
  const cleanPath = path.replace(/^\/+/, '');
  
  return `${baseUrl}/storage/${cleanPath}`;
}

// Checker for HTML/PDF documents vs Images
const isDocument = (path) => {
  if (!path) return false;
  const lowerPath = path.toLowerCase();
  return lowerPath.endsWith('.html') || lowerPath.endsWith('.pdf') || lowerPath.endsWith('.doc');
}

// Extract product image safely from snapshot
const getProductImage = (req) => {
  if (!req) return null;
  // If the backend has already decoded raw_material_details to an object
  if (req.raw_material_details && typeof req.raw_material_details === 'object') {
    return req.raw_material_details.image_url || null;
  }
  // If it's still a JSON string, try parsing it
  if (typeof req.raw_material_details === 'string') {
    try {
      const parsed = JSON.parse(req.raw_material_details);
      return parsed.image_url || null;
    } catch(e) {
      return null;
    }
  }
  return null;
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #374151;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #4b5563;
}
</style>