<template>
  <div class="procurement-approval min-h-screen p-4 md:p-6 text-slate-900 bg-slate-50/50">
    <Toaster position="top-right" />
    
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
          <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center border border-blue-100">
            <FileText class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-900">{{ requests.length }}</div>
            <div class="text-sm font-medium text-slate-500">Total Requests</div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-white border-slate-200 shadow-sm">
        <CardContent class="p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center border border-amber-100">
            <Clock class="w-6 h-6 text-amber-600" />
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-900">{{ requests.filter(r => r.status === 'op-approved').length }}</div>
            <div class="text-sm font-medium text-slate-500">Pending Approval</div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-white border-slate-200 shadow-sm">
        <CardContent class="p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center border border-emerald-100">
            <CheckCircle2 class="w-6 h-6 text-emerald-600" />
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-900">{{ requests.filter(r => r.status === 'd-approved').length }}</div>
            <div class="text-sm font-medium text-slate-500">Recently Approved</div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="bg-white border-slate-200 shadow-sm overflow-hidden rounded-xl">
      <div v-if="loading" class="p-12 text-center text-slate-500 flex flex-col items-center">
        <RefreshCw class="w-8 h-8 animate-spin text-slate-400 mb-3" />
        <p>Loading procurement requests...</p>
      </div>
      <div v-else class="overflow-x-auto">
        <Table class="min-w-[800px] md:min-w-full">
          <TableHeader class="bg-slate-50/80 border-b border-slate-200">
            <TableRow class="hover:bg-transparent border-none">
              <TableHead class="text-slate-600 font-semibold h-12">Request Details</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12">Requester</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12">Logistics Info</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12">Status</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12">Total Value</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="req in sortedRequests" :key="req.id" class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
              <TableCell class="py-4 whitespace-nowrap">
                <div class="font-mono text-slate-900 font-semibold">{{ req.id }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ req.date }}</div>
              </TableCell>
              <TableCell class="py-4 whitespace-nowrap">
                <div class="flex items-center gap-3">
                  <div class="w-9 h-9 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-700 text-xs font-bold shadow-sm">
                    {{ req.department ? req.department.substring(0,2).toUpperCase() : 'NA' }}
                  </div>
                  <div>
                    <div class="text-slate-900 text-sm font-medium">{{ req.requester }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ req.department }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="py-4 whitespace-nowrap">
                <div class="text-sm text-slate-600 flex items-center gap-1.5">
                  <MapPin class="w-3.5 h-3.5 text-slate-400" />
                  {{ req.location }}
                </div>
                <div class="text-xs text-slate-500 mt-1">
                  {{ req.items.length }} line items
                </div>
              </TableCell>
              <TableCell class="py-4 whitespace-nowrap">
                <Badge :class="['rounded-full font-medium px-2.5 py-0.5 shadow-sm', statusClasses[req.status]]">
                  {{ req.status === 'op-approved' ? 'Op. Approved' : (req.status === 'd-approved' ? 'Dist. Approved' : req.status) }}
                </Badge>
              </TableCell>
              <TableCell class="py-4 whitespace-nowrap">
                <div class="text-slate-900 font-semibold">₱{{ req.totalAmount.toLocaleString() }}</div>
                <div v-if="req.priority === 'High'" class="text-xs text-red-600 font-medium flex items-center mt-1">
                  <AlertCircle class="w-3 h-3 mr-1" /> High Priority
                </div>
              </TableCell>
              <TableCell class="py-4 text-right whitespace-nowrap">
                <Button size="sm" variant="outline" @click="viewDetails(req)" 
                        class="h-8 px-4 bg-white hover:bg-blue-50 border-slate-200 text-blue-600 hover:text-blue-700 transition-colors shadow-sm">
                  Review Details
                </Button>
              </TableCell>
            </TableRow>
            <TableRow v-if="sortedRequests.length === 0">
               <TableCell colspan="6" class="text-center py-12 text-slate-500">
                  <div class="flex flex-col items-center justify-center">
                    <CheckCircle2 class="w-10 h-10 text-slate-300 mb-3" />
                    <p>No pending operational requests require your approval at this time.</p>
                  </div>
               </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Dialog :open="!!selectedRequest" @update:open="(val) => !val && closeModal()">
      <DialogContent class="bg-white border-none shadow-2xl text-slate-900 w-[95vw] sm:w-full sm:max-w-3xl flex flex-col h-[90vh] sm:h-auto sm:max-h-[85vh] p-0 gap-0 overflow-hidden rounded-2xl">
        
        <DialogHeader class="p-5 sm:p-6 border-b border-slate-100 bg-white shrink-0">
          <DialogTitle class="text-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-slate-900">
            <span class="font-bold tracking-tight">Procurement Final Review</span>
            <span class="font-mono text-sm px-2 py-1 bg-slate-100 text-slate-600 rounded-md border border-slate-200" v-if="selectedRequest">{{ selectedRequest.id }}</span>
          </DialogTitle>
          <DialogDescription class="text-slate-500 mt-1">
            Review the operationally verified items before granting final authorization.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6 bg-slate-50/50">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <Card class="bg-white border-slate-200 shadow-sm">
              <CardContent class="p-5 space-y-4">
                <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold">Requester Profile</h4>
                <div class="flex items-center gap-3">
                   <div class="w-10 h-10 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center">
                      <Building2 class="w-5 h-5 text-blue-600" />
                   </div>
                   <div>
                      <div class="font-semibold text-slate-900">{{ selectedRequest.requester }}</div>
                      <div class="text-sm text-slate-500">{{ selectedRequest.department }}</div>
                   </div>
                </div>
                <div class="text-sm text-slate-600 flex items-center gap-2 pt-3 border-t border-slate-100">
                  <MapPin class="w-4 h-4 text-slate-400" /> {{ selectedRequest.location }}
                </div>
              </CardContent>
            </Card>

            <Card class="bg-white border-slate-200 shadow-sm">
              <CardContent class="p-5 space-y-4">
                <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold">Order Parameters</h4>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-slate-600">Current Status</span>
                  <Badge :class="statusClasses[selectedRequest.status]">
                    {{ selectedRequest.status === 'op-approved' ? 'Op. Approved' : (selectedRequest.status === 'd-approved' ? 'Dist. Approved' : selectedRequest.status) }}
                  </Badge>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-slate-600">Priority Level</span>
                  <span :class="selectedRequest.priority === 'High' ? 'text-red-600 font-bold' : 'text-slate-700 font-medium'">
                    {{ selectedRequest.priority }}
                  </span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                  <span class="text-sm text-slate-600">Proposed Courier</span>
                  <span class="text-slate-900 font-medium">{{ selectedRequest.courier || 'Standard Logistics' }}</span>
                </div>
              </CardContent>
            </Card>
          </div>

          <div>
            <h4 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
              <FileText class="w-4 h-4 text-slate-400" />
              Manifest Summary
            </h4>
            <div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-sm">
              <div class="overflow-x-auto">
                <Table class="min-w-[600px] sm:min-w-full">
                  <TableHeader class="bg-slate-50 border-b border-slate-200">
                    <TableRow class="hover:bg-slate-50 border-none">
                      <TableHead class="text-slate-600 font-semibold h-10">Item Description</TableHead>
                      <TableHead class="text-slate-600 font-semibold h-10 text-right">Quantity</TableHead>
                      <TableHead class="text-slate-600 font-semibold h-10 text-right">Unit Price</TableHead>
                      <TableHead class="text-slate-600 font-semibold h-10 text-right">Line Total</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(item, idx) in selectedRequest.items" :key="idx" class="border-b border-slate-100 hover:bg-slate-50/50">
                      <TableCell class="text-slate-900 font-medium whitespace-nowrap">{{ item.name }}</TableCell>
                      <TableCell class="text-right text-slate-600 whitespace-nowrap">{{ item.quantity }}</TableCell>
                      <TableCell class="text-right text-slate-600 whitespace-nowrap">₱{{ item.unitPrice.toLocaleString() }}</TableCell>
                      <TableCell class="text-right text-slate-900 font-bold whitespace-nowrap">₱{{ (item.quantity * item.unitPrice).toLocaleString() }}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </div>

          <div v-if="isRejecting" class="space-y-3 bg-red-50 p-5 rounded-xl border border-red-100">
            <Label class="text-red-700 font-semibold flex items-center gap-2">
              <AlertCircle class="w-4 h-4" /> Please provide a reason for rejection
            </Label>
            <Input v-model="rejectReason" class="bg-white border-red-200 text-slate-900 focus-visible:ring-red-500 placeholder:text-slate-400" placeholder="e.g. Budget exceeded, missing info, unapproved vendor..." />
          </div>

        </div>

        <div v-if="selectedRequest" class="p-5 sm:p-6 border-t border-slate-200 bg-slate-50 shrink-0">
          <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
             <div class="text-xl font-bold text-slate-900 text-center sm:text-left">
               Grand Total: <span class="text-blue-600">₱{{ selectedRequest.totalAmount.toLocaleString() }}</span>
             </div>
             
             <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <Button variant="outline" @click="closeModal" class="w-full sm:w-auto text-slate-600 border-slate-300 hover:bg-slate-100 bg-white order-last sm:order-first shadow-sm">
                  Cancel
                </Button>
                
                <div v-if="selectedRequest.status === 'op-approved'" class="contents">
                   <template v-if="!isRejecting">
                      <Button variant="outline" @click="isRejecting = true" class="w-full sm:w-auto bg-white hover:bg-red-50 text-red-600 border-red-200 hover:border-red-300 shadow-sm">
                         Reject Request
                      </Button>
                      <Button class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white shadow-sm" @click="initiateApprove">
                         <ShieldCheck class="w-4 h-4 mr-2" />
                         Finalize Approval
                      </Button>
                   </template>
                   
                   <Button v-else variant="destructive" @click="initiateReject" :disabled="!rejectReason" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white shadow-sm">
                     Confirm Rejection
                   </Button>
                </div>
                
                <div v-else class="w-full sm:w-auto">
                   <Button disabled variant="outline" class="w-full bg-green-50 text-green-700 border-green-200 opacity-100">
                      <CheckCircle2 class="w-4 h-4 mr-2" />
                      Approved
                   </Button>
                </div>
             </div>
          </div>
        </div>

      </DialogContent>
    </Dialog>

    <AlertDialog :open="alertOpen" @update:open="alertOpen = $event">
      <AlertDialogContent class="bg-white border-slate-200 text-slate-900 z-[100] w-[90vw] sm:w-full max-w-lg rounded-2xl shadow-xl">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-xl text-slate-900 font-bold">{{ alertConfig.title }}</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500 text-base mt-2 leading-relaxed">
            {{ alertConfig.description }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex-col sm:flex-row gap-3 mt-6">
          <AlertDialogCancel @click="alertOpen = false" class="bg-white text-slate-700 hover:bg-slate-100 border-slate-300 mt-0 shadow-sm font-medium">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeAction" :class="[alertConfig.confirmClass, 'shadow-sm font-medium']">
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
  'd-approved': 'bg-emerald-100 text-emerald-700 border border-emerald-200',
  'rejected': 'bg-red-100 text-red-700 border border-red-200'
}

// Computed property to sort 'op-approved' requests to the top
const sortedRequests = computed(() => {
  return [...requests.value].sort((a, b) => {
    // Assign numerical weights for sorting. Lower number = higher priority
    const getWeight = (status) => {
      if (status === 'op-approved') return 1;
      if (status === 'd-approved') return 2;
      return 3;
    };
    
    const weightA = getWeight(a.status);
    const weightB = getWeight(b.status);
    
    // Sort by status weight first
    if (weightA !== weightB) {
      return weightA - weightB;
    }
    
    // If statuses are equal, sort by newest ID (or date if preferred)
    return b.db_id - a.db_id; 
  });
});

// Fetch API
const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/distributor/procurement-approvals')
    requests.value = response.data
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

// Handlers
const viewDetails = (req) => {
  selectedRequest.value = req
  isRejecting.value = false
  rejectReason.value = ''
}

const closeModal = () => {
  selectedRequest.value = null
}

const initiateApprove = () => {
  pendingAction.value = 'approve'
  alertConfig.value = {
    title: 'Confirm Approval',
    description: `You are about to finalize approval for Request ${selectedRequest.value.id}. This will authorize the purchase and move it to processing. Do you wish to proceed?`,
    confirmText: 'Yes, Approve Request',
    confirmClass: 'bg-blue-600 hover:bg-blue-700 text-white'
  }
  alertOpen.value = true
}

const initiateReject = () => {
  if (!rejectReason.value) {
    toast.error("Please provide a reason for rejection")
    return
  }
  pendingAction.value = 'reject'
  alertConfig.value = {
    title: 'Confirm Rejection',
    description: `Are you completely sure you want to reject Request ${selectedRequest.value.id}? This will cancel the procurement process entirely.`,
    confirmText: 'Yes, Reject Request',
    confirmClass: 'bg-red-600 hover:bg-red-700 text-white border-0'
  }
  alertOpen.value = true
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
        fetchRequests() // Refresh list in background
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
        fetchRequests() // Refresh to remove it from the screen
        closeModal()
        return `Request ${selectedRequest.value.id} has been rejected.`
      },
      error: 'Failed to reject request'
    })
  } catch (error) {
    console.error(error)
  }
}
</script>