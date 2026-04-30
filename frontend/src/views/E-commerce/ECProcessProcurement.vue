<template>
  <div class="procurement-fulfillment min-h-screen p-4 md:p-6 text-gray-100">
    
    
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Procurement Fulfillment</h1>
          <p class="text-gray-400 text-sm md:text-base">Manage finance-approved requests and warehouse logistics</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <Button variant="outline" class="w-full sm:w-auto bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800 hover:text-white" @click="requirePermission('view', handleExport)">
            <Download class="w-4 h-4 mr-2" />
            Export Manifest
          </Button>
          <Button @click="fetchRequests" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white border-0">
            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
            Refresh
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      
      
      <div class="lg:col-span-1 space-y-4">
        
        <div class="relative w-full">
          <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-500" />
          <Input 
            placeholder="Search request ID, product..." 
            class="pl-9 bg-gray-900 border-gray-800 text-white focus:ring-blue-500"
            v-model="searchQuery"
          />
        </div>

        
        <div class="flex gap-2 pb-2 overflow-x-auto hide-scrollbar">
          <Badge 
            v-for="filter in statusFilters" 
            :key="filter.value"
            @click="currentStatusFilter = filter.value"
            class="cursor-pointer whitespace-nowrap px-3 py-1.5 transition-all"
            :class="currentStatusFilter === filter.value ? 'bg-blue-600 hover:bg-blue-500 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
            variant="secondary"
          >
            {{ filter.label }}
          </Badge>
        </div>

        
        <div class="space-y-3 h-[calc(100vh-280px)] overflow-y-auto pr-2 custom-scrollbar">
          <div v-if="loading && requests.length === 0" class="space-y-3">
            <Card v-for="i in 4" :key="i" class="bg-gray-900 border-gray-800 animate-pulse h-28"></Card>
          </div>
          
          <div v-else-if="filteredRequests.length === 0" class="text-center py-10">
            <PackageX class="w-12 h-12 text-gray-600 mx-auto mb-3" />
            <p class="text-gray-400 font-medium">No requests found</p>
          </div>

          <Card 
            v-else
            v-for="req in filteredRequests" 
            :key="req.id"
            @click="selectRequest(req)"
            class="bg-gray-900 border-gray-800 cursor-pointer transition-all hover:bg-gray-800 hover:border-gray-700 overflow-hidden"
            :class="{ 'ring-2 ring-blue-500 bg-gray-800': selectedRequest?.id === req.id }"
          >
            <div class="p-4 border-l-4" :class="getStatusBorderColor(req.status)">
              <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-mono text-gray-400">{{ req.id }}</span>
                <Badge :class="getStatusBadgeClass(req.status)" class="text-[10px] uppercase font-bold">{{ formatStatus(req.status) }}</Badge>
              </div>
              <h3 class="text-sm font-semibold text-white line-clamp-1 mb-1" :title="req.product">{{ req.product }}</h3>
              <p class="text-xs text-gray-500 mb-3">{{ req.supplier }}</p>
              
              <div class="flex justify-between items-center mt-2 pt-3 border-t border-gray-800">
                <span class="text-xs text-gray-400">{{ formatDate(req.date) }}</span>
                <span class="text-sm font-bold text-gray-200">{{ formatCurrency(req.totalValue) }}</span>
              </div>
            </div>
          </Card>
        </div>
      </div>

      
      <div class="lg:col-span-3">
        <Card v-if="selectedRequest" class="bg-gray-900 border-gray-800 h-full flex flex-col">
          
          
          <div class="p-5 md:p-6 border-b border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-900/50">
            <div>
              <div class="flex items-center gap-3 mb-1">
                <Badge :class="getStatusBadgeClass(selectedRequest.status)" class="uppercase">{{ formatStatus(selectedRequest.status) }}</Badge>
                <span class="text-sm font-mono text-gray-400">Ref: {{ selectedRequest.id }}</span>
              </div>
              <h2 class="text-xl md:text-2xl font-bold text-white">{{ selectedRequest.product }}</h2>
            </div>
            
            <div class="flex gap-2">
              <Button v-if="selectedRequest.status === 'approved'" @click="requirePermission('approve', () => promptAction('reject'))" variant="destructive" class="bg-red-900/80 text-red-100 hover:bg-red-800 border-red-800">
                <XCircle class="w-4 h-4 mr-2" /> Reject
              </Button>
              <Button v-if="selectedRequest.status === 'approved'" @click="requirePermission('approve', () => promptAction('op_approve'))" class="bg-emerald-600 hover:bg-emerald-500 text-white">
                <CheckCircle2 class="w-4 h-4 mr-2" /> Op. Approve
              </Button>
              
            </div>
          </div>

          <div class="flex-grow p-5 md:p-6 overflow-y-auto custom-scrollbar space-y-8">
            
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <div class="space-y-4">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center">
                  <Package class="w-4 h-4 mr-2 text-blue-500" /> Order Details
                </h3>
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-800 space-y-3">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Supplier</span>
                    <span class="text-sm font-medium text-white">{{ selectedRequest.supplier }}</span>
                  </div>
                  <Separator class="bg-gray-800" />
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Category</span>
                    <span class="text-sm font-medium text-white">{{ selectedRequest.category }}</span>
                  </div>
                  <Separator class="bg-gray-800" />
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Quantity</span>
                    <span class="text-sm font-medium text-white">{{ selectedRequest.quantity }} units</span>
                  </div>
                  <Separator class="bg-gray-800" />
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Total Value</span>
                    <span class="text-sm font-bold text-blue-400">{{ formatCurrency(selectedRequest.totalValue) }}</span>
                  </div>
                </div>
              </div>

              
              <div class="space-y-4">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center">
                  <Truck class="w-4 h-4 mr-2 text-indigo-500" /> Logistics
                </h3>
                <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-800 space-y-3">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Requested Date</span>
                    <span class="text-sm font-medium text-white">{{ formatDate(selectedRequest.date) }}</span>
                  </div>
                  <Separator class="bg-gray-800" />
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Required By</span>
                    <span class="text-sm font-medium text-white">{{ formatDate(selectedRequest.requiredBy) }}</span>
                  </div>
                  <Separator class="bg-gray-800" />
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Shipping</span>
                    <span class="text-sm font-medium text-white capitalize">{{ selectedRequest.shippingMethod }}</span>
                  </div>
                  <Separator class="bg-gray-800" />
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-400">Payment</span>
                    <span class="text-sm font-medium text-white uppercase">{{ selectedRequest.paymentTerms }}</span>
                  </div>
                </div>
              </div>
            </div>

            
            <div class="space-y-4">
              <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center">
                <MapPin class="w-4 h-4 mr-2 text-rose-500" /> Delivery Address
              </h3>
              <div class="bg-gray-800/50 rounded-xl p-4 border border-gray-800 flex items-start gap-3">
                <div class="mt-1 bg-gray-800 p-2 rounded-lg">
                  <Building2 class="w-5 h-5 text-gray-400" />
                </div>
                <div>
                  <p class="text-sm text-gray-300 leading-relaxed">{{ selectedRequest.deliveryAddress }}</p>
                </div>
              </div>
            </div>

            
            <div class="space-y-4">
              <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider flex items-center">
                <FileText class="w-4 h-4 mr-2 text-amber-500" /> Fulfillment Proofs / Notes
              </h3>
              
              <div v-if="!selectedRequest.fulfillment" class="bg-gray-800/30 border border-gray-800 border-dashed rounded-xl p-8 text-center">
                <FileQuestion class="w-8 h-8 text-gray-600 mx-auto mb-3" />
                <p class="text-sm text-gray-400">No fulfillment records attached yet. The supplier has not provided updates or proofs for this order.</p>
              </div>

              <div v-else class="space-y-4">
                
                <div v-if="selectedRequest.fulfillment.supplier_notes" class="bg-gray-800/50 rounded-xl p-4 border border-gray-800">
                  <h4 class="text-xs font-semibold text-gray-500 mb-2">Supplier Notes</h4>
                  <p class="text-sm text-gray-300">{{ selectedRequest.fulfillment.supplier_notes }}</p>
                </div>

                
                <div v-if="selectedRequest.fulfillment.attachments && selectedRequest.fulfillment.attachments.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                  <div v-for="attachment in selectedRequest.fulfillment.attachments" :key="attachment.id" class="group relative aspect-square rounded-xl overflow-hidden bg-gray-800 border border-gray-700">
                    <img v-if="isImage(attachment.file_type)" :src="getStorageUrl(attachment.file_path)" class="w-full h-full object-cover transition-transform group-hover:scale-110" />
                    <div v-else class="w-full h-full flex flex-col items-center justify-center p-4">
                      <File class="w-8 h-8 text-gray-500 mb-2" />
                      <span class="text-xs text-center text-gray-400 truncate w-full">{{ attachment.file_name }}</span>
                    </div>
                    
                    <a :href="getStorageUrl(attachment.file_path)" target="_blank" class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                      <ExternalLink class="w-6 h-6 text-white" />
                    </a>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </Card>

        
        <Card v-else class="bg-gray-900 border-gray-800 border-dashed h-full min-h-[500px] flex flex-col items-center justify-center text-center p-6">
          <div class="bg-gray-800 w-20 h-20 rounded-full flex items-center justify-center mb-6 shadow-inner">
            <ClipboardList class="w-10 h-10 text-gray-500" />
          </div>
          <h2 class="text-xl font-bold text-white mb-2">No Request Selected</h2>
          <p class="text-gray-400 max-w-md">Select a procurement request from the sidebar to view full details, manage fulfillment, and export manifests.</p>
        </Card>
      </div>
    </div>

    
    <Dialog :open="alertOpen" @update:open="alertOpen = false">
      <DialogContent class="bg-gray-900 border border-gray-800 text-white sm:max-w-md">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold">{{ actionTitle }}</DialogTitle>
          <DialogDescription class="text-gray-400 mt-2">
            {{ actionDescription }}
          </DialogDescription>
        </DialogHeader>

        <div v-if="pendingAction === 'reject'" class="py-4">
          <label class="text-sm text-gray-400 mb-2 block">Reason for Rejection <span class="text-red-500">*</span></label>
          <Textarea 
            v-model="rejectReason" 
            placeholder="Please specify why this approved request cannot be fulfilled operationally..."
            class="bg-gray-800 border-gray-700 text-white min-h-[100px] focus:ring-red-500"
          />
        </div>

        <DialogFooter class="mt-6 flex gap-3 sm:justify-end">
          <Button variant="outline" class="bg-transparent border-gray-700 text-gray-300 hover:bg-gray-800" @click="closeModal">Cancel</Button>
          <Button 
            @click="executeAction" 
            :class="actionButtonClass"
            :disabled="pendingAction === 'reject' && !rejectReason.trim()"
          >
            Confirm Action
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue' 
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import echo from '@/utils/websocket.js' 

import { 
  Search, PackageX, CheckCircle2, XCircle, Truck, MapPin, 
  Package, FileText, ClipboardList, RefreshCw, Download, 
  Building2, FileQuestion, Image as ImageIcon, File, ExternalLink 
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { Textarea } from '@/components/ui/textarea'
import { Toaster } from '@/components/ui/sonner'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const requests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const currentStatusFilter = ref('all')
const selectedRequest = ref(null)

const alertOpen = ref(false)
const pendingAction = ref(null)
const rejectReason = ref('')

// WebSocket State
const activeChannel = ref(null) 

const statusFilters = [
  { label: 'All Active', value: 'all' },
  { label: 'Fin. Approved', value: 'approved' },
  { label: 'Op. Approved', value: 'op-approved' },
  { label: 'Shipped', value: 'shipped' },
  { label: 'Delivered', value: 'delivered' }
]

const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied: You do not have permission to ${action} records.`)
    return
  }
  if (callback) callback()
}

const filteredRequests = computed(() => {
  let result = requests.value

  if (currentStatusFilter.value !== 'all') {
    result = result.filter(r => r.status === currentStatusFilter.value)
  }

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(r => 
      r.id.toLowerCase().includes(q) || 
      r.product.toLowerCase().includes(q) ||
      r.supplier.toLowerCase().includes(q)
    )
  }

  return result
})

const getStorageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    const baseUrl = import.meta.env.VITE_API_URL ? import.meta.env.VITE_API_URL.replace('/api', '') : 'http://localhost:8000';
    return `${baseUrl}/storage/${path.replace('public/', '')}`;
}

const isImage = (mimeType) => {
    return mimeType && mimeType.startsWith('image/');
}

// ------------------------------------------------------------------
// WEBSOCKET INITIALIZATION FUNCTION
// ------------------------------------------------------------------
const setupWebsocket = (distributorId) => {
  if (activeChannel.value) return; 

  let channelName = distributorId === null ? 'admin.procurement' : `distributor.${distributorId}.procurement`;
  activeChannel.value = channelName;

  echo.private(channelName)
    .listen('.procurement.created', (e) => {
        fetchRequests(true); // Fetch silently on creation
    })
    .listen('.procurement.updated', (e) => {
        toast.info('Procurement status updated by Finance/System!', { description: 'Syncing latest records.' });
        fetchRequests(true); // Fetch silently on updates
    });
}
// ------------------------------------------------------------------

const fetchRequests = async (silent = false) => {
  try {
    if (!silent) loading.value = true
    
    // FETCH DATA
    const response = await api.get('/distributor/procurement-fulfillment', {
      params: { per_page: 50, status: 'all' }
    })
    
    if (response.data.permissions) {
      permissions.value = response.data.permissions
    }

    requests.value = response.data.data.data.map(req => ({
      db_id: req.id,
      id: req.request_code,
      product: req.product_name,
      supplier: req.supplier,
      category: req.category,
      quantity: req.quantity,
      totalValue: req.total_cost,
      status: req.status,
      date: req.request_date,
      requiredBy: req.required_by_date,
      shippingMethod: req.shipping_method,
      paymentTerms: req.payment_terms,
      deliveryAddress: req.delivery_address,
      fulfillment: req.fulfillment
    }))

    if (selectedRequest.value) {
      const updatedMatch = requests.value.find(r => r.id === selectedRequest.value.id)
      if (updatedMatch) selectedRequest.value = updatedMatch
    }

    // Automatically setup WebSockets using the returned distributor scope
    if (response.data.distributor_id !== undefined && !activeChannel.value) {
        setupWebsocket(response.data.distributor_id);
    }

  } catch (error) {
    if (error.response?.status === 403) {
      toast.error("Unauthorized: You do not have access to view this.")
    } else {
      console.error(error)
      toast.error("Failed to fetch procurement records")
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchRequests()
})

// Unsubscribe from WebSocket when the component unmounts
onUnmounted(() => {
  if (activeChannel.value) {
    echo.leave(activeChannel.value);
  }
})

const selectRequest = (req) => {
  selectedRequest.value = req
}

const getStatusBorderColor = (status) => {
  const colors = {
    'approved': 'border-l-blue-500',
    'op-approved': 'border-l-emerald-500',
    'shipped': 'border-l-indigo-500',
    'delivered': 'border-l-purple-500',
    'completed': 'border-l-gray-500'
  }
  return colors[status] || 'border-l-gray-700'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    'approved': 'bg-blue-500/10 text-blue-400 border-blue-500/20',
    'op-approved': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    'shipped': 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
    'delivered': 'bg-purple-500/10 text-purple-400 border-purple-500/20',
    'completed': 'bg-gray-500/10 text-gray-400 border-gray-500/20'
  }
  return classes[status] || 'bg-gray-800 text-gray-400'
}

const formatStatus = (status) => {
  if (status === 'approved') return 'Fin. Approved'
  if (status === 'op-approved') return 'Op. Approved'
  return status
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val || 0)
}

const formatDate = (dateString) => {
  if (!dateString) return 'Not specified'
  return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const handleExport = () => {
  toast.success("Manifest exported successfully (Mocked)")
}

const promptAction = (actionType) => {
  pendingAction.value = actionType
  rejectReason.value = ''
  alertOpen.value = true
}

const closeModal = () => {
  alertOpen.value = false
  setTimeout(() => {
    pendingAction.value = null
    rejectReason.value = ''
  }, 300)
}

const actionTitle = computed(() => {
  if (pendingAction.value === 'op_approve') return 'Operationally Approve Request?'
  if (pendingAction.value === 'reject') return 'Reject Procurement Request?'
  return ''
})

const actionDescription = computed(() => {
  if (pendingAction.value === 'op_approve') return 'This will officially accept the finance-approved request for logistics and warehouse tracking. Suppliers will be notified.'
  if (pendingAction.value === 'reject') return 'This will decline the request entirely and halt the procurement pipeline.'
  return ''
})

const actionButtonClass = computed(() => {
  if (pendingAction.value === 'op_approve') return 'bg-emerald-600 hover:bg-emerald-500 text-white'
  if (pendingAction.value === 'reject') return 'bg-red-600 hover:bg-red-500 text-white'
  return ''
})

const executeAction = async () => {
  if (pendingAction.value === 'op_approve') {
    await markAsOpApprove()
  } else if (pendingAction.value === 'reject') {
    await confirmReject()
  }
  alertOpen.value = false
}

const markAsOpApprove = async () => {
  if (!selectedRequest.value) return

  try {
    await api.post(`/distributor/procurement-fulfillment/${selectedRequest.value.db_id}/op-approve`)
    
    selectedRequest.value.status = 'op-approved'
    
    toast.success(`Request ${selectedRequest.value.id} marked as Op. Approved`)
    closeModal()
    fetchRequests(true) // Fetch silently
  } catch (error) {
    console.error(error)
    toast.error("Failed to process operational approval")
  }
}

const confirmReject = async () => {
  if (!selectedRequest.value || !rejectReason.value) return

  try {
    await api.post(`/distributor/procurement-fulfillment/${selectedRequest.value.db_id}/reject`, {
      reason: rejectReason.value
    })
    
    toast.success(`Request ${selectedRequest.value.id} has been rejected`)
    closeModal()
    fetchRequests(true) // Fetch silently
  } catch (error) {
    console.error(error)
    toast.error("Failed to reject request")
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5); 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #4b5563; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #6b7280; 
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;  
  scrollbar-width: none;  
}
</style>