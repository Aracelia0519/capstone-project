<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { 
  Check, 
  X, 
  FileText, 
  Calendar as CalendarIcon,
  AlertCircle,
  CheckCircle2,
  XCircle,
  Eye,
  Clock,
  Filter,
  DollarSign // Added Icon
} from 'lucide-vue-next'
import { Toaster, toast } from 'vue-sonner'

// UI Components
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox' // Make sure you have this component or use standard input
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
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
const requests = ref([])
const isLoading = ref(false)

// Modal & Action State
const isDetailsOpen = ref(false)
const selectedRequest = ref(null)
const isRejecting = ref(false)
const rejectionReasonInput = ref('')
const isPaidLeave = ref(true) // State for Paid Leave toggle

// Alert Dialog State
const isConfirmOpen = ref(false)
const pendingAction = ref(null) // { type: 'Approved' | 'Rejected' | 'Cancelled' }

// --- Computed Properties ---

// 1. Dashboard Stats
const totalRequests = computed(() => requests.value.length)
const pendingRequestsCount = computed(() => requests.value.filter(r => r.status === 'Pending').length)
const approvedRequestsCount = computed(() => requests.value.filter(r => r.status === 'Approved').length)
const rejectedRequestsCount = computed(() => requests.value.filter(r => r.status === 'Rejected' || r.status === 'Cancelled').length)

// 2. Table Data
const pendingTableData = computed(() => {
  return requests.value.filter(r => r.status === 'Pending')
})

// Helpers
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getStatusVariant = (status) => {
  switch (status) {
    case 'Approved': return 'default'
    case 'Rejected': return 'destructive'
    case 'Cancelled': return 'secondary'
    case 'Pending': return 'outline'
    default: return 'outline'
  }
}

const getStatusColorClass = (status) => {
    switch (status) {
        case 'Pending': return 'text-orange-600 border-orange-200 bg-orange-50'
        case 'Cancelled': return 'text-gray-600 border-gray-200 bg-gray-50'
        default: return ''
    }
}

// Fetch Data
const fetchRequests = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/hr/leaves')
    requests.value = response.data
  } catch (error) {
    console.error(error)
    toast.error('Failed to load leave requests.')
  } finally {
    isLoading.value = false
  }
}

// Open Details Modal
const openDetails = (req) => {
  selectedRequest.value = req
  isRejecting.value = false
  rejectionReasonInput.value = ''
  isPaidLeave.value = true // Reset to default true (Paid)
  isDetailsOpen.value = true
}

// Prepare Action (opens Alert Dialog)
const initiateAction = (type) => {
  if (type === 'Rejected' && !rejectionReasonInput.value.trim()) {
    toast.error('Please provide a reason for rejection.')
    return
  }
  
  pendingAction.value = { type }
  isConfirmOpen.value = true
}

// Execute Action (called after Alert Dialog confirmation)
const confirmAction = async () => {
  if (!pendingAction.value || !selectedRequest.value) return

  const { type } = pendingAction.value
  const id = selectedRequest.value.id
  const reason = type === 'Rejected' ? rejectionReasonInput.value : null

  try {
    const payload = { 
        status: type,
        is_paid: isPaidLeave.value // Pass the Paid/Unpaid selection
    }
    if (reason) payload.rejection_reason = reason

    const response = await api.put(`/hr/leaves/${id}/status`, payload)
    
    // Update local state
    const req = requests.value.find(r => r.id === id)
    if (req) {
        req.status = type
        if (reason) req.rejectionReason = reason
        req.isPaid = isPaidLeave.value
    }
    
    // SONNER SUCCESS MESSAGES
    if (type === 'Approved') {
        const payStatus = isPaidLeave.value ? 'Paid' : 'Unpaid'
        toast.success(`Leave request for ${req.employee.name} has been Approved (${payStatus}).`)
    } else if (type === 'Rejected') {
        toast.success(`Leave request rejected successfully.`)
    } else if (type === 'Cancelled') {
        toast.success(`Leave request cancelled.`)
    }
    
    // Close dialogs
    isConfirmOpen.value = false
    isDetailsOpen.value = false 

  } catch (error) {
    console.error(error)
    toast.error('Failed to update request status. Please try again.')
  }
}

// Initial Load
onMounted(() => {
  fetchRequests()
})
</script>

<template>
  <div class="w-full p-4 md:p-8 space-y-6">
    <Toaster position="top-center" />
    
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-3xl font-bold tracking-tight">Leave Management</h2>
        <p class="text-muted-foreground">Manage pending approvals and view leave statistics.</p>
      </div>
      <Button variant="outline" @click="fetchRequests" :disabled="isLoading">
        Refresh
      </Button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
          <FileText class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ totalRequests }}</div>
          <p class="text-xs text-muted-foreground">All time records</p>
        </CardContent>
      </Card>
      
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Review</CardTitle>
          <AlertCircle class="h-4 w-4 text-orange-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ pendingRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Action required</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Approved</CardTitle>
          <CheckCircle2 class="h-4 w-4 text-green-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ approvedRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Scheduled leaves</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected/Cancelled</CardTitle>
          <XCircle class="h-4 w-4 text-red-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ rejectedRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Denied or revoked</p>
        </CardContent>
      </Card>
    </div>

    <div class="flex items-center justify-between mt-6">
        <h3 class="text-xl font-semibold tracking-tight">Pending Requests</h3>
    </div>

    <div v-if="isLoading && requests.length === 0" class="flex justify-center py-8">
        <span class="text-muted-foreground">Loading requests...</span>
    </div>

    <div v-else-if="pendingTableData.length === 0" class="flex flex-col items-center justify-center py-12 text-center border rounded-md border-dashed bg-slate-50">
        <CheckCircle2 class="h-10 w-10 text-green-500 mb-4" />
        <h3 class="text-lg font-semibold">All caught up!</h3>
        <p class="text-muted-foreground text-sm max-w-sm">
            There are no pending leave requests requiring your attention right now.
        </p>
    </div>

    <div v-else class="hidden md:block rounded-md border">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Employee</TableHead>
            <TableHead>Type</TableHead>
            <TableHead>Duration</TableHead>
            <TableHead>Reason</TableHead>
            <TableHead>Status</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="req in pendingTableData" :key="req.id">
            <TableCell class="font-medium">
              <div class="flex items-center gap-3">
                <Avatar>
                  <AvatarImage :src="req.employee.avatar" />
                  <AvatarFallback>{{ req.employee.initials }}</AvatarFallback>
                </Avatar>
                <div>
                  <div class="font-bold">{{ req.employee.name }}</div>
                  <div class="text-xs text-muted-foreground">{{ req.employee.department }}</div>
                </div>
              </div>
            </TableCell>
            <TableCell>{{ req.type }}</TableCell>
            <TableCell>
              <div class="flex flex-col text-sm">
                <span>{{ formatDate(req.startDate) }} - {{ formatDate(req.endDate) }}</span>
                <span class="text-xs text-muted-foreground">({{ req.days }} days)</span>
              </div>
            </TableCell>
            <TableCell class="max-w-[200px] truncate" :title="req.reason">
              {{ req.reason }}
            </TableCell>
            <TableCell>
              <Badge :variant="getStatusVariant(req.status)" :class="getStatusColorClass(req.status)">
                {{ req.status }}
              </Badge>
            </TableCell>
            <TableCell class="text-right">
              <Button variant="outline" size="sm" @click="openDetails(req)">
                <Eye class="mr-2 h-3.5 w-3.5" />
                View Details
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div v-if="pendingTableData.length > 0" class="grid grid-cols-1 gap-4 md:hidden">
      <Card v-for="req in pendingTableData" :key="req.id">
        <CardHeader class="pb-3">
          <div class="flex justify-between items-start">
            <div class="flex items-center gap-3">
              <Avatar>
                <AvatarFallback>{{ req.employee.initials }}</AvatarFallback>
              </Avatar>
              <div>
                <CardTitle class="text-base">{{ req.employee.name }}</CardTitle>
                <CardDescription>{{ req.employee.department }}</CardDescription>
              </div>
            </div>
            <Badge :variant="getStatusVariant(req.status)" :class="getStatusColorClass(req.status)">{{ req.status }}</Badge>
          </div>
        </CardHeader>
        <CardContent class="pb-3 grid gap-2 text-sm">
          <div class="flex items-center gap-2 text-muted-foreground">
            <CalendarIcon class="h-4 w-4" />
            <span>{{ formatDate(req.startDate) }} - {{ formatDate(req.endDate) }}</span>
          </div>
          <div class="flex flex-col gap-1">
            <div class="flex items-start gap-2">
                <FileText class="h-4 w-4 mt-0.5 text-muted-foreground" />
                <span>{{ req.reason }}</span>
            </div>
          </div>
        </CardContent>
        <CardFooter class="justify-end gap-2 pt-0">
          <Button variant="outline" size="sm" class="w-full" @click="openDetails(req)">
            View Details
          </Button>
        </CardFooter>
      </Card>
    </div>

    <Dialog :open="isDetailsOpen" @update:open="isDetailsOpen = $event">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Leave Request Details</DialogTitle>
          <DialogDescription>
            Review the complete details of the leave application.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="grid gap-4 py-4">
          <div class="flex items-center gap-4 p-3 bg-muted/50 rounded-lg">
             <Avatar class="h-12 w-12">
                <AvatarImage :src="selectedRequest.employee.avatar" />
                <AvatarFallback>{{ selectedRequest.employee.initials }}</AvatarFallback>
             </Avatar>
             <div>
                <h4 class="font-semibold">{{ selectedRequest.employee.name }}</h4>
                <p class="text-sm text-muted-foreground">{{ selectedRequest.employee.department }}</p>
             </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
             <div class="space-y-1">
                <span class="text-xs text-muted-foreground flex items-center gap-1"><Clock class="h-3 w-3" /> Status</span>
                <div>
                   <Badge :variant="getStatusVariant(selectedRequest.status)" :class="getStatusColorClass(selectedRequest.status)">
                      {{ selectedRequest.status }}
                   </Badge>
                </div>
             </div>
             <div class="space-y-1">
                <span class="text-xs text-muted-foreground flex items-center gap-1"><FileText class="h-3 w-3" /> Type</span>
                <div class="font-medium text-sm">{{ selectedRequest.type }}</div>
             </div>
             <div class="space-y-1 col-span-2">
                <span class="text-xs text-muted-foreground flex items-center gap-1"><CalendarIcon class="h-3 w-3" /> Duration</span>
                <div class="font-medium text-sm">
                   {{ formatDate(selectedRequest.startDate) }} - {{ formatDate(selectedRequest.endDate) }}
                   <span class="text-muted-foreground ml-1">({{ selectedRequest.days }} days)</span>
                </div>
             </div>
             <div class="space-y-1 col-span-2">
                <span class="text-xs text-muted-foreground">Reason</span>
                <p class="text-sm bg-muted/30 p-2 rounded-md border">{{ selectedRequest.reason }}</p>
             </div>
             
             <div v-if="selectedRequest.status !== 'Pending'" class="space-y-1 col-span-2">
                <span class="text-xs text-muted-foreground flex items-center gap-1"><DollarSign class="h-3 w-3" /> Payment Status</span>
                <div>
                   <Badge :variant="selectedRequest.isPaid ? 'success' : 'secondary'" class="text-xs">
                      {{ selectedRequest.isPaid ? 'Paid Leave' : 'Unpaid Leave' }}
                   </Badge>
                </div>
             </div>

             <div v-if="selectedRequest.status === 'Rejected' && selectedRequest.rejectionReason" class="space-y-1 col-span-2">
                <span class="text-xs text-red-500 font-medium">Rejection Reason</span>
                <p class="text-sm text-red-600 bg-red-50 p-2 rounded-md border border-red-100">{{ selectedRequest.rejectionReason }}</p>
             </div>
          </div>

          <div v-if="selectedRequest.status === 'Pending' && !isRejecting" class="flex items-center space-x-2 pt-2 border-t mt-2">
             <Checkbox id="paidLeave" :checked="isPaidLeave" @update:checked="isPaidLeave = $event" />
             <Label for="paidLeave" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                Mark as Paid Leave
             </Label>
             <p class="text-xs text-muted-foreground ml-6">
                If unchecked, attendance will be marked with 0 working hours.
             </p>
          </div>

          <div v-if="isRejecting" class="space-y-2 pt-2 border-t mt-2 animate-in fade-in slide-in-from-top-2">
             <Label for="rejectReason" class="text-red-600">Reason for Rejection *</Label>
             <Textarea 
                id="rejectReason" 
                v-model="rejectionReasonInput" 
                placeholder="Please explain why this request is being rejected..."
                class="min-h-[80px]"
             />
          </div>
        </div>

        <DialogFooter class="flex gap-2 sm:justify-end">
          <template v-if="selectedRequest">
             <template v-if="isRejecting">
                <Button variant="ghost" @click="isRejecting = false">Cancel</Button>
                <Button variant="destructive" @click="initiateAction('Rejected')">Confirm Rejection</Button>
             </template>

             <template v-else>
                <Button variant="secondary" @click="isDetailsOpen = false" v-if="selectedRequest.status !== 'Pending'">
                    Close
                </Button>

                <template v-if="selectedRequest.status === 'Pending'">
                    <Button variant="outline" @click="isDetailsOpen = false">Close</Button>
                    <Button variant="outline" class="border-red-200 text-red-600 hover:bg-red-50" @click="isRejecting = true">
                       Reject
                    </Button>
                    <Button class="bg-green-600 hover:bg-green-700 text-white" @click="initiateAction('Approved')">
                       Approve
                    </Button>
                </template>
             </template>
          </template>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="isConfirmOpen" @update:open="isConfirmOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
          <AlertDialogDescription>
            This action will update the leave request status to 
            <span class="font-bold" :class="{
                'text-green-600': pendingAction?.type === 'Approved',
                'text-red-600': pendingAction?.type === 'Rejected' || pendingAction?.type === 'Cancelled'
            }">{{ pendingAction?.type }}</span>.
            
            <template v-if="pendingAction?.type === 'Approved'">
                <br>The leave will be marked as <span class="font-bold">{{ isPaidLeave ? 'Paid (8 hours/day)' : 'Unpaid (0 hours/day)' }}</span>.
            </template>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="confirmAction"
            :class="{
                'bg-green-600 hover:bg-green-700': pendingAction?.type === 'Approved',
                'bg-red-600 hover:bg-red-700': pendingAction?.type === 'Rejected' || pendingAction?.type === 'Cancelled'
            }"
          >
            Confirm
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>