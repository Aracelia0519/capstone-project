<template>
  <div class="budget-release min-h-screen p-4 md:p-6 text-slate-900 ">
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
            padding: '16px 20px',
            fontSize: '15px',
            minWidth: '280px',
            maxWidth: '400px',
            borderRadius: '10px',
            pointerEvents: 'auto',
          },
        }"
      />
    </Teleport>
    
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-900 mb-1">Procurement Budget Release</h1>
          <p class="text-slate-500 text-sm md:text-base">Review Dist. Approved requests and release funds for procurement</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <Button @click="fetchRequests" variant="outline" class="w-full sm:w-auto bg-white hover:bg-slate-50 border-slate-200 text-slate-700 shadow-sm">
            <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
            Refresh List
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <Card class="bg-white border-emerald-200 shadow-sm ring-1 ring-emerald-50">
        <CardContent class="p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center border border-emerald-200">
            <Coins class="w-6 h-6 text-emerald-700" />
          </div>
          <div>
            <div class="text-xl lg:text-2xl font-bold text-emerald-700">₱{{ availableBudget.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
            <div class="text-sm font-medium text-emerald-600/80">Available Budget</div>
          </div>
        </CardContent>
      </Card>

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
            <Wallet class="w-6 h-6 text-amber-600" />
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-900">{{ requests.filter(r => r.status === 'd-approved').length }}</div>
            <div class="text-sm font-medium text-slate-500">Pending Budget Release</div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-white border-slate-200 shadow-sm">
        <CardContent class="p-5 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center border border-emerald-100">
            <CheckCircle2 class="w-6 h-6 text-emerald-600" />
          </div>
          <div>
            <div class="text-2xl font-bold text-slate-900">{{ requests.filter(r => r.status === 'ready').length }}</div>
            <div class="text-sm font-medium text-slate-500">Budget Released</div>
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
              <TableHead class="text-slate-600 font-semibold h-12">Distributor</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12">Requester</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12">Status</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12 text-right">Required Budget</TableHead>
              <TableHead class="text-slate-600 font-semibold h-12 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="req in sortedRequests" :key="req.id" class="border-b border-slate-100 hover:bg-slate-50 transition-colors cursor-pointer" @click="viewDetails(req)">
              <TableCell class="py-4 whitespace-nowrap">
                <div class="font-mono text-slate-900 font-semibold">{{ req.id }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ req.date }}</div>
              </TableCell>
              <TableCell class="py-4 whitespace-nowrap">
                <div class="text-slate-900 text-sm font-medium">{{ req.distributor }}</div>
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
                <Badge :class="['rounded-full font-medium px-2.5 py-0.5 shadow-sm', statusClasses[req.status]]">
                  {{ req.status === 'd-approved' ? 'Dist. Approved' : (req.status === 'ready' ? 'Ready (Budget Released)' : req.status) }}
                </Badge>
              </TableCell>
              <TableCell class="py-4 text-right whitespace-nowrap">
                <div class="text-slate-900 font-bold text-lg">₱{{ req.totalAmount.toLocaleString() }}</div>
                <div v-if="req.priority === 'High'" class="text-xs text-red-600 font-medium flex items-center justify-end mt-1">
                  <AlertCircle class="w-3 h-3 mr-1" /> High Priority
                </div>
              </TableCell>
              <TableCell class="py-4 text-right whitespace-nowrap" @click.stop>
                <Button size="sm" variant="outline" @click="viewDetails(req)" 
                        class="h-8 px-4 bg-white hover:bg-emerald-50 border-slate-200 text-emerald-600 hover:text-emerald-700 transition-colors shadow-sm">
                  Review & Release
                </Button>
              </TableCell>
            </TableRow>
            <TableRow v-if="sortedRequests.length === 0">
               <TableCell colspan="6" class="text-center py-12 text-slate-500">
                  <div class="flex flex-col items-center justify-center">
                    <CheckCircle2 class="w-10 h-10 text-slate-300 mb-3" />
                    <p>No requests require budget release at this time.</p>
                  </div>
               </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Dialog :open="!!selectedRequest" @update:open="(val) => !val && closeModal()">
      <DialogContent class="bg-white border-none shadow-2xl text-slate-900 w-[95vw] sm:w-full sm:max-w-4xl flex flex-col h-[90vh] sm:h-auto sm:max-h-[85vh] p-0 gap-0 overflow-hidden rounded-2xl">
        
        <DialogHeader class="p-5 sm:p-6 border-b border-slate-100 bg-white shrink-0">
          <DialogTitle class="text-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-slate-900">
            <span class="font-bold tracking-tight">Budget Release Verification</span>
            <span class="font-mono text-sm px-2 py-1 bg-slate-100 text-slate-600 rounded-md border border-slate-200" v-if="selectedRequest">{{ selectedRequest.id }}</span>
          </DialogTitle>
          <DialogDescription class="text-slate-500 mt-1">
            Review procured products and confirm the deduction of funds to set the order to Ready.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6 bg-slate-50/50">
          
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
            <Card class="bg-white border-slate-200 shadow-sm lg:col-span-2">
              <CardContent class="p-5">
                <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-4">Request Information</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <span class="text-xs text-slate-500 block mb-1">Distributor</span>
                    <span class="font-semibold text-slate-900">{{ selectedRequest.distributor }}</span>
                  </div>
                  <div>
                    <span class="text-xs text-slate-500 block mb-1">Requester</span>
                    <span class="font-semibold text-slate-900">{{ selectedRequest.requester }} ({{ selectedRequest.department }})</span>
                  </div>
                  <div>
                    <span class="text-xs text-slate-500 block mb-1">Delivery Location</span>
                    <span class="text-sm text-slate-700 flex items-center gap-1"><MapPin class="w-3.5 h-3.5" /> {{ selectedRequest.location }}</span>
                  </div>
                  <div>
                    <span class="text-xs text-slate-500 block mb-1">Priority</span>
                    <span :class="selectedRequest.priority === 'High' ? 'text-red-600 font-bold' : 'text-slate-700 font-medium'">{{ selectedRequest.priority }}</span>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="bg-white border-slate-200 shadow-sm flex flex-col">
              <CardContent class="p-5 flex flex-col justify-center flex-1">
                <h4 class="text-xs uppercase tracking-wider text-slate-400 font-bold mb-2">Total Budget Required</h4>
                <div class="text-3xl font-black text-emerald-600 mb-1">
                  ₱{{ selectedRequest.totalAmount.toLocaleString() }}
                </div>
                <div class="text-sm font-semibold mb-3 flex items-center gap-1" :class="selectedRequest.payment_terms === 'gcash' ? 'text-blue-600' : 'text-slate-600'">
                  <CreditCard v-if="selectedRequest.payment_terms === 'gcash'" class="w-4 h-4" /> 
                  <Wallet v-else class="w-4 h-4" /> 
                  Via {{ selectedRequest.payment_terms === 'gcash' ? 'GCash' : 'COD / Offline' }}
                </div>
                <Badge :class="['w-fit mt-auto', statusClasses[selectedRequest.status]]">
                  {{ selectedRequest.status === 'd-approved' ? 'Dist. Approved (Pending Release)' : (selectedRequest.status === 'ready' ? 'Funds Released' : selectedRequest.status) }}
                </Badge>
              </CardContent>
            </Card>
          </div>

          <div v-if="selectedRequest.status === 'd-approved' && selectedRequest.totalAmount > availableBudget" class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-start gap-3 text-red-700 shadow-sm">
            <AlertCircle class="w-5 h-5 shrink-0 mt-0.5" />
            <div>
              <p class="font-bold text-sm">Insufficient Available Budget</p>
              <p class="text-sm mt-1">This request requires <span class="font-bold">₱{{ selectedRequest.totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>, but the business only has <span class="font-bold">₱{{ availableBudget.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span> available.</p>
            </div>
          </div>

          <div>
            <h4 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
              <ShoppingCart class="w-4 h-4 text-slate-400" />
              Procured Products Breakdown
            </h4>
            <div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-sm">
              <div class="overflow-x-auto">
                <Table class="min-w-[600px] sm:min-w-full">
                  <TableHeader class="bg-slate-50 border-b border-slate-200">
                    <TableRow class="hover:bg-slate-50 border-none">
                      <TableHead class="text-slate-600 font-semibold h-10">Product Name</TableHead>
                      <TableHead class="text-slate-600 font-semibold h-10 text-right">Quantity</TableHead>
                      <TableHead class="text-slate-600 font-semibold h-10 text-right">Unit Price</TableHead>
                      <TableHead class="text-slate-600 font-semibold h-10 text-right">Subtotal</TableHead>
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
              <AlertCircle class="w-4 h-4" /> Please provide a reason for rejecting the budget release
            </Label>
            <Input v-model="rejectReason" class="bg-white border-red-200 text-slate-900 focus-visible:ring-red-500 placeholder:text-slate-400" placeholder="e.g. Insufficient funds, pricing mismatch..." />
          </div>

        </div>

        <div v-if="selectedRequest" class="p-5 sm:p-6 border-t border-slate-200 bg-slate-50 shrink-0">
          <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
             <div class="text-sm text-slate-500 hidden sm:block">
               Verify funds before approving this deduction log.
             </div>
             
             <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <Button variant="outline" @click="closeModal" class="w-full sm:w-auto text-slate-600 border-slate-300 hover:bg-slate-100 bg-white order-last sm:order-first shadow-sm">
                  Cancel
                </Button>
                
                <div v-if="selectedRequest.status === 'd-approved'" class="contents">
                   <template v-if="!isRejecting">
                      <Button variant="outline" @click="requirePermission('update', () => isRejecting = true)" class="w-full sm:w-auto bg-white hover:bg-red-50 text-red-600 border-red-200 hover:border-red-300 shadow-sm">
                         Reject Request
                      </Button>
                      <Button :class="['w-full sm:w-auto shadow-sm text-white', selectedRequest.payment_terms === 'gcash' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-600 hover:bg-emerald-700', {'opacity-50 cursor-not-allowed': selectedRequest.totalAmount > availableBudget}]" 
                              @click="requirePermission('update', initiateApprove)"
                              :disabled="selectedRequest.totalAmount > availableBudget">
                         <CreditCard v-if="selectedRequest.payment_terms === 'gcash'" class="w-4 h-4 mr-2" />
                         <Coins v-else class="w-4 h-4 mr-2" />
                         {{ selectedRequest.payment_terms === 'gcash' ? 'Proceed to GCash' : 'Release Budget & Approve' }}
                      </Button>
                   </template>
                   
                   <Button v-else variant="destructive" @click="requirePermission('update', initiateReject)" :disabled="!rejectReason || isProcessing" class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white shadow-sm flex items-center justify-center">
                     <span v-if="isProcessing" class="mr-2 animate-spin">
                       <RefreshCw class="w-4 h-4" />
                     </span>
                     {{ isProcessing ? 'Processing...' : 'Confirm Rejection' }}
                   </Button>
                </div>
                
                <div v-else class="w-full sm:w-auto">
                   <Button disabled variant="outline" class="w-full bg-emerald-50 text-emerald-700 border-emerald-200 opacity-100">
                      <CheckCircle2 class="w-4 h-4 mr-2" />
                      Budget Logged & Released
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
          <AlertDialogAction @click="executeAction" :disabled="isProcessing" :class="[alertConfig.confirmClass, 'shadow-sm font-medium flex items-center justify-center']">
            <span v-if="isProcessing" class="mr-2 animate-spin">
              <RefreshCw class="w-4 h-4" />
            </span>
            {{ isProcessing ? 'Processing...' : alertConfig.confirmText }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/utils/axios'
import { 
  RefreshCw, MapPin, AlertCircle, Building2,
  FileText, Clock, CheckCircle2, Wallet, Coins, ShoppingCart, CreditCard
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

const router = useRouter()
const route = useRoute()

// State
const loading = ref(false)
const isProcessing = ref(false)
const requests = ref([])
const availableBudget = ref(0)
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

// RBAC Permissions ref state
const permissions = ref({
  can_view: false,
  can_create: false,
  can_update: false,
  can_delete: false
})

// Validation interceptor for operations
const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied`, {
      description: `You do not have permission to ${action} budget releases.`
    });
    return;
  }
  if (callback) callback();
}

// UI Mappings for Light Theme Badges
const statusClasses = {
  'd-approved': 'bg-amber-100 text-amber-700 border border-amber-200', 
  'ready': 'bg-emerald-100 text-emerald-700 border border-emerald-200',
  'rejected': 'bg-red-100 text-red-700 border border-red-200'
}

// Computed property to sort 'd-approved' requests to the top
const sortedRequests = computed(() => {
  return [...requests.value].sort((a, b) => {
    const getWeight = (status) => {
      if (status === 'd-approved') return 1;
      if (status === 'ready') return 2;
      return 3;
    };
    
    const weightA = getWeight(a.status);
    const weightB = getWeight(b.status);
    
    if (weightA !== weightB) {
      return weightA - weightB;
    }
    
    return b.db_id - a.db_id; 
  });
});

// Fetch API
const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/finance/budget-release')
    
    // Process updated response wrapper for RBAC
    if (response.data.success !== undefined) {
      requests.value = response.data.data
      
      if (response.data.permissions) {
        permissions.value = response.data.permissions
      }
      
      // Load available budget returned from the new backend query
      if (response.data.available_budget !== undefined) {
        availableBudget.value = response.data.available_budget
      }
    } else {
      requests.value = response.data // Fallback in case of old endpoint signature
    }

  } catch (error) {
    if (error.response?.status === 403) {
      toast.error("Unauthorized", { description: "You do not have permission to view budget releases." })
    } else {
      console.error("Failed to fetch approvals", error)
      toast.error("Failed to load budget release requests from server")
    }
  } finally {
    loading.value = false
  }
}

// Intercept GCash Redirects
const verifyGcashPayment = async (requestCode) => {
  loading.value = true
  toast.info('Verifying GCash Payment... Please wait.')
  
  // 2.5 second buffer to let PayMongo's test server sync
  await new Promise(resolve => setTimeout(resolve, 2500));
  
  try {
    const response = await api.post('/finance/budget-release/verify-gcash', { request_code: requestCode })
    if (response.data.success) {
      toast.success('Payment Confirmed!', { description: 'Budget released successfully via GCash.' })
      router.replace({ query: {} }) // Clear URL
    }
  } catch (error) {
    toast.error('Payment Verification Failed', { description: error.response?.data?.message || 'The payment session could not be verified.' })
    router.replace({ query: {} })
  } finally {
    fetchRequests()
  }
}

onMounted(() => {
  if (route.query.request_code) {
    verifyGcashPayment(route.query.request_code)
  } else {
    fetchRequests()
  }
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
  // Extra layer of validation
  if (selectedRequest.value.totalAmount > availableBudget.value) {
    toast.error('Insufficient available budget to release these funds.')
    return
  }

  pendingAction.value = 'approve'
  const isGcash = selectedRequest.value.payment_terms === 'gcash'
  
  alertConfig.value = {
    title: isGcash ? 'Proceed to GCash Payment' : 'Confirm Budget Release',
    description: isGcash 
      ? `You are about to transfer ₱${selectedRequest.value.totalAmount.toLocaleString()} to the supplier via GCash. You will be redirected to PayMongo securely.`
      : `You are about to release ₱${selectedRequest.value.totalAmount.toLocaleString()} for Request ${selectedRequest.value.id}. This action will log a budget deduction and set the request to Ready. Do you wish to proceed?`,
    confirmText: isGcash ? 'Proceed to GCash' : 'Yes, Release Funds',
    confirmClass: isGcash ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'
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
    description: `Are you completely sure you want to reject this budget release for Request ${selectedRequest.value.id}? This will halt the procurement process.`,
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
  if (!isProcessing.value) {
     alertOpen.value = false
  }
}

const markAsApproved = async () => {
  if (!selectedRequest.value) return

  try {
    isProcessing.value = true
    
    // 1. Get the current exact URL path dynamically (works for both /finance and /special-rbac)
    const currentPath = window.location.origin + route.path

    // 2. Send the return_url to the backend
    const response = await api.post(`/finance/budget-release/${selectedRequest.value.db_id}/approve`, {
      return_url: currentPath
    })
    
    if (response.data.checkout_url) {
        toast.success('Redirecting to PayMongo for GCash checkout...')
        setTimeout(() => {
            window.location.href = response.data.checkout_url
        }, 1500)
    } else {
        selectedRequest.value.status = 'ready'
        fetchRequests() // Refresh list in background
        toast.success(`Funds released! Request ${selectedRequest.value.id} is now Ready.`)
        setTimeout(closeModal, 1500) 
        alertOpen.value = false
    }
  } catch (error) {
    if (error.response?.status === 403) {
      toast.error('Access denied', { description: 'You cannot approve budget releases.' })
    } else if (error.response?.status === 400) {
      toast.error('Cannot approve', { description: error.response?.data?.message || 'Insufficient budget.' })
    } else {
      toast.error('Error', { description: error.response?.data?.message || 'Failed to release budget.' })
    }
    alertOpen.value = false
  } finally {
    isProcessing.value = false
  }
}

const confirmReject = async () => {
  if (!selectedRequest.value || !rejectReason.value) return

  try {
    isProcessing.value = true // Keep the loading state active
    
    // Capture the request ID before doing anything that could wipe it out
    const reqId = selectedRequest.value.id;

    const requestPromise = api.post(`/finance/budget-release/${selectedRequest.value.db_id}/reject`, {
      reason: rejectReason.value
    })

    // This creates the lazy loading / processing notification
    toast.promise(requestPromise, {
      loading: 'Rejecting request and sending emails... Please wait.',
      success: () => {
        fetchRequests()
        closeModal() // This sets selectedRequest.value to null
        return `Request ${reqId} has been rejected.` // Safely using the captured ID!
      },
      error: (error) => {
        console.error("Rejection Error:", error)
        return error.response?.status === 403 
          ? 'Access denied: You cannot reject budget releases.' 
          : 'Failed to reject request.'
      }
    })

    // Explicitly AWAIT the API call so the UI waits for the email to finish sending
    await requestPromise
    
  } catch (error) {
    // The error is already visually handled by the toast.promise block above
  } finally {
    isProcessing.value = false // Modal will only close AFTER the process is done
  }
}
</script>