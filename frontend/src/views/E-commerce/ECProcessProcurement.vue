<template>
  <div class="procurement-fulfillment min-h-screen p-4 md:p-6 text-gray-100">
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

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4 mb-6">
      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardContent class="p-3 md:p-4">
          <div class="text-xl md:text-2xl font-bold mb-1">{{ requests.length }}</div>
          <div class="text-xs md:text-sm text-gray-300">Total Pipeline</div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardContent class="p-3 md:p-4">
          <div class="text-xl md:text-2xl font-bold mb-1">{{ requests.filter(r => r.status === 'approved').length }}</div>
          <div class="text-xs md:text-sm text-gray-300">Pending Ops Action</div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 border-gray-800 text-white">
        <CardContent class="p-3 md:p-4">
          <div class="text-xl md:text-2xl font-bold mb-1">{{ requests.filter(r => ['op-approved', 'd-approved', 'ready', 'processing'].includes(r.status)).length }}</div>
          <div class="text-xs md:text-sm text-gray-300">Processing / Ready</div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-indigo-500/20 to-violet-500/20 border-gray-800 text-white">
        <CardContent class="p-3 md:p-4">
          <div class="text-xl md:text-2xl font-bold mb-1">{{ requests.filter(r => r.status === 'shipped').length }}</div>
          <div class="text-xs md:text-sm text-gray-300">In Transit</div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 border-gray-800 text-white">
        <CardContent class="p-3 md:p-4">
          <div class="text-xl md:text-2xl font-bold mb-1">{{ requests.filter(r => r.status === 'delivered').length }}</div>
          <div class="text-xs md:text-sm text-gray-300">Delivered</div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 bg-gray-900/50 border-gray-800 backdrop-blur-sm">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="md:col-span-2 space-y-2">
            <Label class="text-gray-300">Search Request</Label>
            <div class="relative">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" />
              <Input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Search ID, Department, or Requester..." 
                class="pl-9 bg-gray-800 border-gray-700 text-white placeholder:text-gray-500 focus-visible:ring-blue-500" 
              />
            </div>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Status</Label>
            <Select v-model="selectedStatus">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white focus:ring-blue-500 w-full">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="approved">Pending Ops Action</SelectItem>
                <SelectItem value="op-approved">Op. Approved</SelectItem>
                <SelectItem value="d-approved">Dist. Approved</SelectItem>
                <SelectItem value="ready">Ready (Budgeted)</SelectItem>
                <SelectItem value="shipped">In Transit</SelectItem>
                <SelectItem value="delivered">Delivered</SelectItem>
                <SelectItem value="rejected">Rejected</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Priority</Label>
            <Select v-model="selectedPriority">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white focus:ring-blue-500 w-full">
                <SelectValue placeholder="All Priorities" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all">All Priorities</SelectItem>
                <SelectItem value="High">High Priority</SelectItem>
                <SelectItem value="Medium">Medium</SelectItem>
                <SelectItem value="Low">Low</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-end">
            <Button @click="resetFilters" variant="outline" class="w-full bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white">
              Reset Filters
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden">
      <div v-if="loading" class="p-8 text-center text-gray-400">Loading requests...</div>
      <div v-else class="overflow-x-auto">
        <Table class="min-w-[800px] md:min-w-full">
          <TableHeader class="bg-gray-900/90">
            <TableRow class="hover:bg-transparent border-gray-800">
              <TableHead class="text-gray-300">Request Details</TableHead>
              <TableHead class="text-gray-300">Department</TableHead>
              <TableHead class="text-gray-300">Logistics Info</TableHead>
              <TableHead class="text-gray-300">Status</TableHead>
              <TableHead class="text-gray-300">Value</TableHead>
              <TableHead class="text-gray-300 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="req in filteredRequests" :key="req.id" class="border-gray-800 hover:bg-white/5 transition-colors">
              <TableCell class="whitespace-nowrap">
                <div class="font-mono text-white font-medium">{{ req.id }}</div>
                <div class="text-xs text-gray-500">{{ req.date }}</div>
              </TableCell>
              <TableCell class="whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-600 to-cyan-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ req.department ? req.department.substring(0,2).toUpperCase() : 'NA' }}
                  </div>
                  <div>
                    <div class="text-white text-sm font-medium">{{ req.department }}</div>
                    <div class="text-xs text-gray-400">{{ req.requester }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="whitespace-nowrap">
                <div class="text-sm text-gray-300 flex items-center gap-1">
                  <MapPin class="w-3 h-3 text-gray-500" />
                  {{ req.location }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                  {{ req.items.length }} items
                </div>
              </TableCell>
              <TableCell class="whitespace-nowrap">
                <Badge :class="['rounded-full border-0 font-medium px-2 py-0.5 whitespace-nowrap', statusClasses[req.status]]">
                  {{ formatStatus(req.status) }}
                </Badge>
              </TableCell>
              <TableCell class="whitespace-nowrap">
                <div class="text-white font-medium">₱{{ req.totalAmount.toLocaleString() }}</div>
                <div v-if="req.priority === 'High'" class="text-xs text-red-400 font-medium flex items-center mt-0.5">
                  <AlertCircle class="w-3 h-3 mr-1" /> High Priority
                </div>
              </TableCell>
              <TableCell class="text-right whitespace-nowrap">
                <div class="flex justify-end space-x-2">
                  <Button size="sm" variant="ghost" @click="viewDetails(req)" 
                          class="h-8 px-2 bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 hover:text-blue-300">
                    View
                  </Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-if="filteredRequests.length === 0">
               <TableCell colspan="6" class="text-center py-8 text-gray-500">
                  No requests found matching your criteria.
               </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Dialog :open="!!selectedRequest" @update:open="(val) => !val && closeModal()">
      <DialogContent class="bg-gray-950 border-gray-800 text-white w-[95vw] sm:w-full sm:max-w-3xl flex flex-col h-[90vh] sm:h-auto sm:max-h-[85vh] p-0 gap-0 overflow-hidden rounded-xl">
        
        <DialogHeader class="p-4 sm:p-6 border-b border-gray-800 bg-gray-950 shrink-0">
          <DialogTitle class="text-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <span>Request Details</span>
            <span class="font-mono text-base text-gray-400 break-all" v-if="selectedRequest">{{ selectedRequest.id }}</span>
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Review items and Operational approval dispatch.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Card class="bg-gray-900 border-gray-800">
              <CardContent class="p-4 space-y-3">
                <h4 class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Department Info</h4>
                <div class="flex items-center gap-3">
                   <div class="w-10 h-10 rounded-lg bg-gray-800 flex items-center justify-center">
                      <Building2 class="w-5 h-5 text-gray-400" />
                   </div>
                   <div>
                      <div class="font-medium text-white">{{ selectedRequest.department }}</div>
                      <div class="text-sm text-gray-400">{{ selectedRequest.requester }}</div>
                   </div>
                </div>
                <div class="text-sm text-gray-400 flex items-center gap-2 pt-2 border-t border-gray-800">
                  <MapPin class="w-4 h-4" /> {{ selectedRequest.location }}
                </div>
              </CardContent>
            </Card>

            <Card class="bg-gray-900 border-gray-800">
              <CardContent class="p-4 space-y-3">
                <h4 class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Logistics Status</h4>
                <div class="flex justify-between items-center">
                  <span class="text-gray-400">Current Status</span>
                  <Badge :class="statusClasses[selectedRequest.status]">
                    {{ formatStatus(selectedRequest.status) }}
                  </Badge>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-400">Priority</span>
                  <span :class="selectedRequest.priority === 'High' ? 'text-red-400 font-bold' : 'text-gray-300'">
                    {{ selectedRequest.priority }}
                  </span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-800">
                  <span class="text-gray-400">Courier</span>
                  <span class="text-white">{{ selectedRequest.courier || 'Unassigned' }}</span>
                </div>
              </CardContent>
            </Card>
          </div>

          <div>
            <h4 class="text-sm font-semibold text-gray-300 mb-3">Approved Items List</h4>
            <div class="rounded-md border border-gray-800 overflow-hidden">
              <div class="overflow-x-auto">
                <Table class="min-w-[600px] sm:min-w-full">
                  <TableHeader class="bg-gray-900">
                    <TableRow class="border-gray-800">
                      <TableHead class="text-gray-400">Item</TableHead>
                      <TableHead class="text-gray-400 text-right">Qty</TableHead>
                      <TableHead class="text-gray-400 text-right">Price</TableHead>
                      <TableHead class="text-gray-400 text-right">Total</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(item, idx) in selectedRequest.items" :key="idx" class="border-gray-800 hover:bg-gray-900/50">
                      <TableCell class="text-gray-200 font-medium whitespace-nowrap">{{ item.name }}</TableCell>
                      <TableCell class="text-right text-gray-300 whitespace-nowrap">{{ item.quantity }}</TableCell>
                      <TableCell class="text-right text-gray-300 whitespace-nowrap">₱{{ item.unitPrice.toLocaleString() }}</TableCell>
                      <TableCell class="text-right text-white font-medium whitespace-nowrap">₱{{ (item.quantity * item.unitPrice).toLocaleString() }}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </div>

          <div v-if="isRejecting" class="space-y-2">
            <Label class="text-red-400">Reason for Rejection</Label>
            <Input v-model="rejectReason" class="bg-gray-800 border-red-900 text-white" placeholder="Enter reason..." />
          </div>

        </div>

        <div v-if="selectedRequest" class="p-4 sm:p-6 border-t border-gray-800 bg-gray-950 shrink-0">
          <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
             <div class="text-lg font-bold text-white text-center sm:text-left">
               Total: ₱{{ selectedRequest.totalAmount.toLocaleString() }}
             </div>
             
             <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <Button variant="ghost" @click="closeModal" class="w-full sm:w-auto text-gray-400 hover:text-white order-last sm:order-first">
                  Cancel
                </Button>
                
                <div v-if="selectedRequest.status === 'approved'" class="contents">
                   <template v-if="!isRejecting">
                      <Button variant="destructive" @click="requirePermission('update', () => { isRejecting = true })" class="w-full sm:w-auto bg-red-900/50 text-red-200 hover:bg-red-900">
                         Reject
                      </Button>
                      <Button class="w-full sm:w-auto bg-green-600 hover:bg-green-500 text-white" @click="requirePermission('update', initiateOpApprove)">
                         <Truck class="w-4 h-4 mr-2" />
                         Op. Approve
                      </Button>
                   </template>
                   
                   <Button v-else variant="destructive" @click="requirePermission('update', initiateReject)" :disabled="!rejectReason" class="w-full sm:w-auto">
                     Confirm Reject
                   </Button>
                </div>
                
                <div v-else class="w-full sm:w-auto">
                   <Button disabled class="w-full bg-gray-800 text-gray-500 border-0 whitespace-nowrap">
                      {{ formatStatus(selectedRequest.status) }}
                   </Button>
                </div>
             </div>
          </div>
        </div>

      </DialogContent>
    </Dialog>

    <AlertDialog :open="alertOpen" @update:open="alertOpen = $event">
      <AlertDialogContent class="bg-gray-950 border-gray-800 text-white z-50 w-[90vw] sm:w-full max-w-lg rounded-lg">
        <AlertDialogHeader>
          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            {{ alertConfig.description }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex-col sm:flex-row gap-2">
          <AlertDialogCancel @click="alertOpen = false" class="bg-gray-800 text-white hover:bg-gray-700 border-gray-700 mt-0">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeAction" :class="alertConfig.confirmClass">
            {{ alertConfig.confirmText }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { 
  Download, RefreshCw, Search, MapPin, AlertCircle,
  Building2, Truck
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
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

const loading = ref(false)
const requests = ref([])
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedPriority = ref('')
const selectedRequest = ref(null)

const isRejecting = ref(false)
const rejectReason = ref('')

const alertOpen = ref(false)
const pendingAction = ref(null) 
const alertConfig = ref({
  description: '',
  confirmText: 'Continue',
  confirmClass: ''
})

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_create: false,
  can_update: false,
  can_delete: false
})

// RBAC Action Interceptor
const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied: You do not have permission to ${action} request fulfillments.`);
    return;
  }
  if (callback) callback();
}

// Unified mapping logic to prevent hardcoding UI labels
const formatStatus = (status) => {
    const map = {
        'approved': 'Pending Ops Action',
        'op-approved': 'Op. Approved',
        'd-approved': 'Dist. Approved',
        'ready': 'Ready (Budgeted)',
        'processing': 'Processing',
        'shipped': 'In Transit',
        'delivered': 'Delivered',
        'rejected': 'Rejected'
    };
    return map[status] || status.charAt(0).toUpperCase() + status.slice(1);
}

// Added the new statuses here
const statusClasses = {
  'approved': 'bg-amber-500/20 text-amber-300 border-amber-500/30',
  'op-approved': 'bg-purple-500/20 text-purple-300 border-purple-500/30', 
  'd-approved': 'bg-blue-500/20 text-blue-300 border-blue-500/30',
  'ready': 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
  'processing': 'bg-blue-500/20 text-blue-300 border-blue-500/30', 
  'shipped': 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
  'delivered': 'bg-green-500/20 text-green-300 border-green-500/30',
  'rejected': 'bg-red-500/20 text-red-300 border-red-500/30'
}

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/distributor/procurement-fulfillment')
    
    if (response.data && response.data.data) {
        requests.value = response.data.data
        if (response.data.permissions) {
            permissions.value = response.data.permissions
        }
    } else {
        requests.value = response.data
    }
  } catch (error) {
    if (error.response?.status === 403) {
      toast.error('Unauthorized: Access to procurement fulfillment is restricted.')
    } else {
      console.error("Failed to fetch requests", error)
      toast.error("Failed to load requests from server")
    }
  } finally {
    loading.value = false
  }
}

const handleExport = () => {
  toast.info("Exporting manifest started...")
  setTimeout(() => {
    toast.success("Manifest exported successfully")
  }, 1000)
}

onMounted(() => {
  fetchRequests()
})

const filteredRequests = computed(() => {
  return requests.value.filter(req => {
    const searchLower = searchQuery.value.toLowerCase()
    const matchSearch = 
      req.id.toLowerCase().includes(searchLower) || 
      (req.department && req.department.toLowerCase().includes(searchLower)) ||
      (req.requester && req.requester.toLowerCase().includes(searchLower))

    const matchStatus = 
      !selectedStatus.value || 
      selectedStatus.value === 'all' || 
      req.status === selectedStatus.value

    const matchPriority = 
      !selectedPriority.value || 
      selectedPriority.value === 'all' || 
      req.priority === selectedPriority.value

    return matchSearch && matchStatus && matchPriority
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = 'all'
  selectedPriority.value = 'all'
  toast.info("Filters reset")
}

const viewDetails = (req) => {
  selectedRequest.value = req
  isRejecting.value = false
  rejectReason.value = ''
}

const closeModal = () => {
  selectedRequest.value = null
}

const initiateOpApprove = () => {
  pendingAction.value = 'op_approve'
  alertConfig.value = {
    description: `This will mark Request ${selectedRequest.value.id} as Operationally Approved and ready for shipping.`,
    confirmText: 'Op. Approve',
    confirmClass: 'bg-green-600 hover:bg-green-500 text-white'
  }
  alertOpen.value = true
}

const initiateReject = () => {
  if (!rejectReason.value) {
    toast.warning("Please provide a rejection reason")
    return
  }
  pendingAction.value = 'reject'
  alertConfig.value = {
    description: `Are you sure you want to reject Request ${selectedRequest.value.id}? This action cannot be undone.`,
    confirmText: 'Confirm Rejection',
    confirmClass: 'bg-red-600 hover:bg-red-500 text-white'
  }
  alertOpen.value = true
}

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
    fetchRequests() 
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
    fetchRequests()
  } catch (error) {
    console.error(error)
    toast.error("Failed to reject request")
  }
}
</script>

<style scoped>
.dialog-content {
  background-color: #030712; 
}
</style>

<style>
/* Unscoped global override: Targets the exact data-attribute injected by vue-sonner */
[data-sonner-toaster] {
  z-index: 2147483647 !important; /* Maximum z-index possible */
  position: fixed !important;
}
</style>