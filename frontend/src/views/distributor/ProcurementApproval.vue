<template>
  <div class="procurement-approval min-h-screen p-4 md:p-6 text-slate-900 ">
    
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900 mb-1">Final Procurement Approval</h1>
          <p class="text-slate-500 text-sm md:text-base">Review and approve operationally prepared requests</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <Button @click="fetchRequests" variant="outline" class="w-full sm:w-auto bg-white hover:bg-slate-50 border-slate-200 text-slate-700 shadow-sm">
            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
            Refresh List
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <Card class="bg-white border-slate-200 shadow-sm">
        <CardContent class="p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
            <FileText class="w-6 h-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Total Requests</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ requests.length }}</h3>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-white border-slate-200 shadow-sm">
        <CardContent class="p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
            <Clock class="w-6 h-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Pending Review</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ pendingRequests.length }}</h3>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-white border-slate-200 shadow-sm">
        <CardContent class="p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
            <CheckCircle2 class="w-6 h-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-slate-500">Approved Today</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ approvedRequests.length }}</h3>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <div class="lg:col-span-1 flex flex-col h-[calc(100vh-280px)]">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-slate-800">Review Queue</h2>
          <Badge variant="secondary" class="bg-slate-100 text-slate-600 hover:bg-slate-200 border-slate-200">{{ pendingRequests.length }} Items</Badge>
        </div>

        <div class="overflow-y-auto pr-2 space-y-3 custom-scrollbar flex-1">
          <div v-if="loading && requests.length === 0" class="space-y-3">
            <Card v-for="i in 4" :key="i" class="bg-white border-slate-100 animate-pulse h-28"></Card>
          </div>
          
          <div v-else-if="requests.length === 0" class="text-center py-12 px-4 bg-white rounded-xl border border-dashed border-slate-200 shadow-sm">
            <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
              <ShieldCheck class="w-8 h-8 text-slate-400" />
            </div>
            <p class="text-slate-600 font-medium">All caught up!</p>
            <p class="text-sm text-slate-400 mt-1">No requests currently require your approval.</p>
          </div>

          <Card 
            v-else
            v-for="req in requests" 
            :key="req.id"
            @click="selectRequest(req)"
            class="bg-white border transition-all duration-200 cursor-pointer overflow-hidden group shadow-sm hover:shadow-md"
            :class="[
              selectedRequest?.id === req.id 
                ? 'border-blue-500 ring-1 ring-blue-500/20' 
                : 'border-slate-200 hover:border-blue-300'
            ]"
          >
            <div class="p-4 relative">
              <div class="absolute left-0 top-0 bottom-0 w-1" :class="req.status === 'op-approved' ? 'bg-amber-400' : 'bg-emerald-400'"></div>
              
              <div class="flex justify-between items-start mb-2 pl-2">
                <span class="text-xs font-mono font-medium text-slate-500 bg-slate-100 px-2 py-0.5 rounded">{{ req.id }}</span>
                <Badge :class="statusClasses[req.status]" class="text-[10px] font-semibold uppercase tracking-wider">{{ statusLabels[req.status] || req.status }}</Badge>
              </div>
              
              <div class="pl-2">
                <h3 class="text-sm font-semibold text-slate-800 line-clamp-1 mb-1 group-hover:text-blue-700 transition-colors">{{ req.items[0]?.name || 'Multiple Items' }}</h3>
                <p class="text-xs text-slate-500 mb-3 flex items-center">
                  <span class="truncate">{{ req.department }} Dept</span>
                  <span class="mx-1.5 text-slate-300">•</span>
                  <span class="truncate">{{ req.requester }}</span>
                </p>
                
                <div class="flex justify-between items-center mt-2 pt-3 border-t border-slate-50">
                  <span class="text-xs text-slate-400 font-medium">{{ req.date }}</span>
                  <span class="text-sm font-bold text-slate-900">{{ formatCurrency(req.totalAmount) }}</span>
                </div>
              </div>
            </div>
          </Card>
        </div>
      </div>

      <div class="lg:col-span-2">
        <Card v-if="selectedRequest" class="bg-white border-slate-200 shadow-sm h-full flex flex-col overflow-hidden">
          
          <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div>
              <div class="flex items-center gap-3 mb-2">
                <Badge :class="statusClasses[selectedRequest.status]" class="uppercase tracking-wider text-xs">{{ statusLabels[selectedRequest.status] }}</Badge>
                <span class="text-sm font-mono font-medium text-slate-500">Ref: {{ selectedRequest.id }}</span>
              </div>
              <h2 class="text-xl font-bold text-slate-900">{{ selectedRequest.items[0]?.name || 'Multiple Items Request' }}</h2>
            </div>
            
            <div class="flex gap-2">
              <Button 
                v-if="selectedRequest.status === 'op-approved'"
                @click="promptAction('reject')" 
                variant="outline" 
                class="border-rose-200 text-rose-600 hover:bg-rose-50 hover:text-rose-700"
              >
                Reject
              </Button>
              <Button 
                v-if="selectedRequest.status === 'op-approved'"
                @click="promptAction('approve')" 
                class="bg-blue-600 hover:bg-blue-700 text-white shadow-sm"
              >
                Approve Order
              </Button>
            </div>
          </div>

          <div class="flex-grow p-6 overflow-y-auto custom-scrollbar space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center">
                  Order Details
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Requester</span>
                    <span class="text-sm font-medium text-slate-900">{{ selectedRequest.requester }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Department</span>
                    <span class="text-sm font-medium text-slate-900">{{ selectedRequest.department }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Priority</span>
                    <Badge variant="outline" :class="{
                      'bg-rose-50 text-rose-700 border-rose-200': selectedRequest.priority === 'High',
                      'bg-amber-50 text-amber-700 border-amber-200': selectedRequest.priority === 'Medium',
                      'bg-emerald-50 text-emerald-700 border-emerald-200': selectedRequest.priority === 'Low'
                    }">{{ selectedRequest.priority }}</Badge>
                  </div>
                  <div class="flex justify-between items-center pt-2 border-t border-slate-200">
                    <span class="text-sm text-slate-500">Total Value</span>
                    <span class="text-base font-bold text-blue-600">{{ formatCurrency(selectedRequest.totalAmount) }}</span>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center">
                  Logistics Route
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Date Logged</span>
                    <span class="text-sm font-medium text-slate-900">{{ selectedRequest.date }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-slate-500">Courier / Method</span>
                    <span class="text-sm font-medium text-slate-900 capitalize">{{ selectedRequest.courier || 'Standard' }}</span>
                  </div>
                  <div class="pt-2 border-t border-slate-200">
                    <span class="text-sm text-slate-500 block mb-1">Destination</span>
                    <div class="flex items-start gap-2">
                      <MapPin class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" />
                      <span class="text-sm font-medium text-slate-900 leading-tight">{{ selectedRequest.location }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Item Breakdown</h3>
              <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm">
                <Table>
                  <TableHeader class="bg-slate-50">
                    <TableRow class="hover:bg-slate-50">
                      <TableHead class="text-slate-600 font-semibold h-10">Product Specification</TableHead>
                      <TableHead class="text-right text-slate-600 font-semibold h-10">Qty</TableHead>
                      <TableHead class="text-right text-slate-600 font-semibold h-10">Unit Price</TableHead>
                      <TableHead class="text-right text-slate-600 font-semibold h-10">Total</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(item, idx) in selectedRequest.items" :key="idx" class="hover:bg-slate-50/50">
                      <TableCell class="font-medium text-slate-900">{{ item.name }}</TableCell>
                      <TableCell class="text-right text-slate-600">{{ item.quantity }}</TableCell>
                      <TableCell class="text-right text-slate-600">{{ formatCurrency(item.unitPrice) }}</TableCell>
                      <TableCell class="text-right font-semibold text-slate-900">{{ formatCurrency(item.quantity * item.unitPrice) }}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
            
            <div v-if="selectedRequest.status === 'op-approved'" class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex gap-3">
              <AlertCircle class="w-5 h-5 text-blue-600 flex-shrink-0" />
              <div>
                <h4 class="text-sm font-semibold text-blue-900">Distributor Final Approval Required</h4>
                <p class="text-sm text-blue-700 mt-1">This request has passed Finance and Operational reviews. Your final approval will trigger actual budget release and purchase movement.</p>
              </div>
            </div>

          </div>
        </Card>

        <Card v-else class="bg-slate-50/50 border-slate-200 border-dashed h-full min-h-[500px] flex flex-col items-center justify-center text-center p-6 shadow-sm">
          <div class="bg-white w-20 h-20 rounded-full flex items-center justify-center mb-6 shadow-sm border border-slate-100">
            <Building2 class="w-10 h-10 text-slate-300" />
          </div>
          <h2 class="text-xl font-bold text-slate-700 mb-2">Select a Request to Review</h2>
          <p class="text-slate-500 max-w-md text-sm">Choose an operationally prepared request from the queue to review financial allocations and provide final distributor sign-off.</p>
        </Card>
      </div>
    </div>

    <AlertDialog :open="alertOpen" @update:open="alertOpen = false">
      <AlertDialogContent class="sm:max-w-md bg-white border-slate-200">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-slate-900">{{ alertConfig.title }}</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500">
            {{ alertConfig.description }}
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div v-if="pendingAction === 'reject'" class="py-2">
          <Label class="text-slate-700 mb-2 block font-medium">Rejection Reason <span class="text-rose-500">*</span></Label>
          <Input 
            v-model="rejectReason" 
            placeholder="E.g., Duplicate order, budget reallocation..."
            class="bg-white border-slate-200 text-slate-900 focus-visible:ring-blue-500"
          />
        </div>

        <AlertDialogFooter class="mt-4">
          <AlertDialogCancel @click="closeModal" class="border-slate-200 text-slate-600 hover:bg-slate-50">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="executeAction" 
            :class="alertConfig.confirmClass"
            :disabled="pendingAction === 'reject' && !rejectReason.trim()"
          >
            {{ alertConfig.confirmText }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import api from '@/utils/axios'
import echo from '@/utils/websocket.js'
import { 
  RefreshCw, MapPin, AlertCircle, Building2,
  ShieldCheck, FileText, Clock, CheckCircle2
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Toaster, toast } from 'vue-sonner' 
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
const loading = ref(false)
const activeChannel = ref(null)
const requests = ref([])
const selectedRequest = ref(null)

// Modal actions state
const isRejecting = ref(false)
const rejectReason = ref('')

// Alert Dialog state
const alertOpen = ref(false)
const pendingAction = ref(null) // 'approve' | 'reject'
const alertConfig = ref({
  title: '',
  description: '',
  confirmText: 'Continue',
  confirmClass: ''
})

// UI Mappings for Light Theme Badges
const statusClasses = {
  'op-approved': 'bg-amber-100 text-amber-700 border border-amber-200', 
  'd-approved': 'bg-emerald-100 text-emerald-700 border border-emerald-200' 
}

const statusLabels = {
  'op-approved': 'Pending Final',
  'd-approved': 'Fully Approved'
}

// Computed specific lists
const pendingRequests = computed(() => requests.value.filter(r => r.status === 'op-approved'))
const approvedRequests = computed(() => requests.value.filter(r => r.status === 'd-approved'))


// WebSocket Initialization
const setupWebsocket = (distributorId) => {
  if (activeChannel.value) return; 

  let channelName = distributorId === null ? 'admin.procurement' : `distributor.${distributorId}.procurement`;
  activeChannel.value = channelName;

  echo.private(channelName)
    .listen('.procurement.created', (e) => {
        fetchRequests(true); // Fetch silently on creation
    })
    .listen('.procurement.updated', (e) => {
        toast.info('Procurement status updated!', { description: 'Syncing latest records.' });
        fetchRequests(true); // Fetch silently on updates
    });
};

const fetchRequests = async (silent = false) => {
  if (!silent) loading.value = true
  try {
    const response = await api.get('/distributor/procurement-approvals')
    requests.value = response.data.data || response.data // Handle array vs object depending on backend
    
    if (response.data.distributor_id !== undefined && !activeChannel.value) {
      setupWebsocket(response.data.distributor_id)
    }

    if (selectedRequest.value) {
      const updatedMatch = requests.value.find(r => r.id === selectedRequest.value.id)
      if (updatedMatch) selectedRequest.value = updatedMatch
    }
  } catch (error) {
    console.error("Failed to fetch approvals", error)
    toast.error("Failed to load procurement requests from server")
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchRequests()
})

onUnmounted(() => {
  if (activeChannel.value) {
    echo.leave(activeChannel.value);
  }
})

const selectRequest = (req) => {
  selectedRequest.value = req
}

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val || 0)
}

// Modal Handlers
const promptAction = (action) => {
  pendingAction.value = action
  rejectReason.value = ''
  
  if (action === 'approve') {
    alertConfig.value = {
      title: 'Approve Request?',
      description: 'Are you sure you want to provide final authorization for this procurement request? This will initiate the purchase.',
      confirmText: 'Approve',
      confirmClass: 'bg-blue-600 text-white hover:bg-blue-700'
    }
  } else {
    alertConfig.value = {
      title: 'Reject Request?',
      description: 'This will decline the request and stop the procurement process entirely.',
      confirmText: 'Reject',
      confirmClass: 'bg-rose-600 text-white hover:bg-rose-700'
    }
  }
  
  alertOpen.value = true
}

const closeModal = () => {
  alertOpen.value = false
  setTimeout(() => {
    pendingAction.value = null
    rejectReason.value = ''
  }, 300)
}

const executeAction = async () => {
  if (pendingAction.value === 'approve') {
    await markAsApproved()
  } else if (pendingAction.value === 'reject') {
    await confirmReject()
  }
  alertOpen.value = false
}

// Submissions
const markAsApproved = async () => {
  if (!selectedRequest.value) return

  try {
    const promise = api.post(`/distributor/procurement-approvals/${selectedRequest.value.db_id}/approve`)
    
    toast.promise(promise, {
      loading: 'Processing approval...',
      success: () => {
        selectedRequest.value.status = 'd-approved'
        fetchRequests(true) // Refresh list in background
        setTimeout(closeModal, 1500) // close modal after brief delay
        return `Request ${selectedRequest.value.id} successfully approved!`
      },
      error: 'Failed to approve request'
    })
  } catch (error) {
    console.error(error)
  }
}

const confirmReject = async () => {
  if (!selectedRequest.value || !rejectReason.value) return

  try {
    const promise = api.post(`/distributor/procurement-approvals/${selectedRequest.value.db_id}/reject`, {
      reason: rejectReason.value
    })
    
    toast.promise(promise, {
      loading: 'Rejecting request...',
      success: () => {
        fetchRequests(true) // Refresh to remove it from the list
        setTimeout(closeModal, 1500)
        return `Request ${selectedRequest.value.id} has been rejected`
      },
      error: 'Failed to reject request'
    })
  } catch (error) {
    console.error(error)
  }
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
  background: #cbd5e1; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}
</style>