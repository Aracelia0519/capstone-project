<template>
  <div class="flex flex-col gap-6 p-8 min-h-screen">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Partnership Approvals</h1>
        <p class="text-slate-500 mt-2 text-sm">
          Overview of internal partnership requests awaiting your authorization.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="fetchRequests">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4 mr-2" />
          Refresh Data
        </Button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Approvals</CardTitle>
          <Clock class="h-4 w-4 text-orange-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ requests.length }}</div>
          <p class="text-xs text-muted-foreground">Requires immediate attention</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Internal Requesters</CardTitle>
          <Users class="h-4 w-4 text-blue-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ uniqueRequesters }}</div>
          <p class="text-xs text-muted-foreground">Unique staff members initiating</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Latest Request</CardTitle>
          <CalendarDays class="h-4 w-4 text-slate-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-sm truncate" :title="latestRequestDate">
            {{ latestRequestDate }}
          </div>
          <p class="text-xs text-muted-foreground">Date of most recent submission</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Approval Rate</CardTitle>
          <Activity class="h-4 w-4 text-green-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">100%</div>
          <p class="text-xs text-muted-foreground">Response rate this session</p>
        </CardContent>
      </Card>
    </div>

    <Card class="col-span-4 border-slate-200 shadow-sm">
      <CardHeader>
        <div class="flex items-center justify-between">
          <div>
            <CardTitle>Request Queue</CardTitle>
            <CardDescription class="mt-1">
              Manage and review supplier connection requests from your team.
            </CardDescription>
          </div>
          <div class="relative w-64">
            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input v-model="searchQuery" placeholder="Search supplier or requester..." class="pl-8" />
          </div>
        </div>
      </CardHeader>
      
      <CardContent>
        <div v-if="loading" class="flex flex-col items-center justify-center py-12 space-y-4">
          <Loader2 class="w-10 h-10 animate-spin text-primary" />
          <p class="text-sm text-muted-foreground">Syncing dashboard data...</p>
        </div>

        <div v-else-if="filteredRequests.length === 0" class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed rounded-lg border-slate-100">
          <div class="bg-slate-50 p-3 rounded-full mb-4">
            <CheckCircle2 class="w-8 h-8 text-slate-300" />
          </div>
          <h3 class="text-lg font-semibold text-slate-900">All Caught Up</h3>
          <p class="text-slate-500 text-sm max-w-sm mt-1">
            There are no pending requests matching your criteria.
          </p>
        </div>

        <div v-else class="rounded-md border border-slate-200">
          <Table>
            <TableHeader class="bg-slate-50/50">
              <TableRow>
                <TableHead class="w-[300px]">Supplier Details</TableHead>
                <TableHead>Requested By</TableHead>
                <TableHead>Submission Date</TableHead>
                <TableHead>Internal Note</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="req in filteredRequests" :key="req.id" class="hover:bg-slate-50/50 transition-colors">
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm">
                      {{ req.supplier_name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex flex-col">
                      <span class="font-semibold text-slate-900">{{ req.supplier_name }}</span>
                      <span class="text-xs text-slate-500">{{ req.supplier_email }}</span>
                    </div>
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="flex flex-col items-start gap-1">
                    <span class="text-sm font-medium text-slate-700">{{ req.requested_by }}</span>
                    <Badge variant="secondary" class="text-[10px] font-normal px-2 bg-slate-100 text-slate-600 hover:bg-slate-200">
                      {{ req.requested_by_role }}
                    </Badge>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="text-sm text-slate-600 flex items-center gap-2">
                    <Calendar class="w-3.5 h-3.5 text-slate-400" />
                    {{ req.date }}
                  </div>
                </TableCell>

                <TableCell>
                  <div class="max-w-[250px] p-2 bg-slate-50 rounded text-xs text-slate-600 italic border border-slate-100 truncate">
                    "{{ req.message }}"
                  </div>
                </TableCell>

                <TableCell class="text-right">
                  <Button 
                    variant="outline" 
                    size="sm"
                    class="h-8 gap-2 text-slate-700"
                    @click="openViewDialog(req)"
                  >
                    <Eye class="h-4 w-4" />
                    View Details
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewDialog" @update:open="showViewDialog = $event">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Request Details</DialogTitle>
          <DialogDescription>
            Review the full details of this partnership request before taking action.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="grid gap-6 py-4">
          <div class="space-y-3">
            <h4 class="text-sm font-medium leading-none border-b pb-2 text-slate-900">Supplier Information</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="space-y-1">
                <span class="text-slate-500 text-xs uppercase font-semibold">Company Name</span>
                <p class="font-medium">{{ selectedRequest.supplier_name }}</p>
              </div>
              <div class="space-y-1">
                <span class="text-slate-500 text-xs uppercase font-semibold">Email Address</span>
                <p class="font-medium">{{ selectedRequest.supplier_email }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-3">
            <h4 class="text-sm font-medium leading-none border-b pb-2 text-slate-900">Request Information</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="space-y-1">
                <span class="text-slate-500 text-xs uppercase font-semibold">Requested By</span>
                <div class="flex items-center gap-2">
                  <p class="font-medium">{{ selectedRequest.requested_by }}</p>
                  <Badge variant="secondary" class="text-[10px] h-5">{{ selectedRequest.requested_by_role }}</Badge>
                </div>
              </div>
              <div class="space-y-1">
                <span class="text-slate-500 text-xs uppercase font-semibold">Submission Date</span>
                <p class="font-medium">{{ selectedRequest.date }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-2 bg-slate-50 p-4 rounded-md border border-slate-100">
             <span class="text-slate-500 text-xs uppercase font-semibold">Internal Note / Message</span>
             <p class="text-sm text-slate-700 italic">"{{ selectedRequest.message }}"</p>
          </div>
        </div>

        <DialogFooter class="flex gap-2 sm:justify-end">
          <Button variant="outline" @click="showViewDialog = false">Close</Button>
          <div class="flex gap-2 w-full sm:w-auto">
             <Button 
                variant="destructive" 
                class="flex-1 sm:flex-none"
                @click="initiateReject"
              >
                <X class="mr-2 h-4 w-4" />
                Reject
              </Button>
              <Button 
                class="flex-1 sm:flex-none bg-slate-900 text-white" 
                @click="initiateApprove"
              >
                <Check class="mr-2 h-4 w-4" />
                Approve
              </Button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog :open="showRejectDialog" @update:open="showRejectDialog = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <div class="flex items-center gap-4 mb-2">
            <div class="p-2 bg-red-100 rounded-full">
              <AlertTriangle class="h-6 w-6 text-red-600" />
            </div>
            <div>
              <DialogTitle>Reject Partnership</DialogTitle>
              <DialogDescription>
                This will decline the internal request. Please provide a reason.
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>
        
        <div class="py-4 space-y-4">
          <div class="space-y-2">
            <Label for="reason" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reason for Rejection</Label>
            <Textarea 
              id="reason" 
              v-model="rejectReason" 
              placeholder="e.g., Duplicate request, Supplier not qualified..."
              class="resize-none focus-visible:ring-red-500"
              rows="4"
            />
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showRejectDialog = false">Cancel</Button>
          <Button 
            class="bg-red-600 hover:bg-red-700 text-white" 
            @click="submitReject" 
            :disabled="isRejecting"
          >
            <Loader2 v-if="isRejecting" class="mr-2 h-4 w-4 animate-spin" />
            Confirm Rejection
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showApproveAlert" @update:open="showApproveAlert = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Approve Partnership Request?</AlertDialogTitle>
          <AlertDialogDescription>
            This action will send a formal connection request to 
            <span class="font-semibold text-slate-900">{{ selectedRequest?.supplier_name }}</span>. 
            Once approved by the supplier, you will be able to transact with them.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showApproveAlert = false">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="confirmApprove" 
            class="bg-green-600 hover:bg-green-700"
          >
            <Loader2 v-if="isApproving" class="mr-2 h-4 w-4 animate-spin" />
            Yes, Approve Request
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { 
  Loader2, 
  RefreshCw, 
  CheckCircle2, 
  Clock,
  Users,
  Activity,
  CalendarDays,
  Search,
  Calendar,
  X,
  Check,
  AlertTriangle,
  Eye
} from 'lucide-vue-next'

// Shadcn UI Components
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { 
  AlertDialog, 
  AlertDialogAction, 
  AlertDialogCancel, 
  AlertDialogContent, 
  AlertDialogDescription, 
  AlertDialogFooter, 
  AlertDialogHeader, 
  AlertDialogTitle 
} from '@/components/ui/alert-dialog'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'

// State
const requests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const selectedRequest = ref(null)

// Dialog States
const showViewDialog = ref(false)
const showRejectDialog = ref(false)
const showApproveAlert = ref(false)

// Action Loading States
const isRejecting = ref(false)
const isApproving = ref(false)

// Reject Reason
const rejectReason = ref('')

// Computed Metrics
const uniqueRequesters = computed(() => {
  const requesters = new Set(requests.value.map(r => r.requested_by))
  return requesters.size
})

const latestRequestDate = computed(() => {
  if (requests.value.length === 0) return 'N/A'
  return requests.value[0].date.split(' ')[0] + ' ' + requests.value[0].date.split(' ')[1]
})

const filteredRequests = computed(() => {
  if (!searchQuery.value) return requests.value
  const lowerQuery = searchQuery.value.toLowerCase()
  return requests.value.filter(req => 
    req.supplier_name.toLowerCase().includes(lowerQuery) ||
    req.requested_by.toLowerCase().includes(lowerQuery) || 
    req.supplier_email.toLowerCase().includes(lowerQuery)
  )
})

// Fetch Data
const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/distributor/partner-requests')
    if (response.data.success) {
      requests.value = response.data.data
    }
  } catch (error) {
    console.error(error)
    toast.error('Sync Error', {
      description: 'Could not retrieve latest partnership requests.'
    })
  } finally {
    loading.value = false
  }
}

// --- ACTIONS FLOW ---

// 1. View Details
const openViewDialog = (req) => {
  selectedRequest.value = req
  showViewDialog.value = true
}

// 2. Approve Flow
const initiateApprove = () => {
  // Keep View Dialog open or close? Let's hide View Dialog to show Alert clearly, 
  // or keep it if Alert is on top. Usually, hiding the view modal for the confirmation is cleaner.
  showViewDialog.value = false 
  showApproveAlert.value = true
}

const confirmApprove = async () => {
  if (!selectedRequest.value) return

  isApproving.value = true
  // Prevent closing dialog automatically if using async inside @click, 
  // but Shadcn AlertDialogAction closes by default. 
  // We handle the API call then update UI.
  
  try {
    const response = await api.post(`/distributor/partner-requests/${selectedRequest.value.id}/approve`)
    
    if (response.data.success) {
      requests.value = requests.value.filter(req => req.id !== selectedRequest.value.id)
      
      toast.success('Partnership Approved', {
        description: 'Request forwarded to supplier successfully.',
        duration: 3000,
        class: 'bg-green-50 border-green-200'
      })
      showApproveAlert.value = false
      selectedRequest.value = null
    }
  } catch (error) {
    console.error(error)
    toast.error('Action Failed', {
      description: error.response?.data?.message || 'Server error occurred.'
    })
    // Re-open view dialog if it failed?
    showViewDialog.value = true
  } finally {
    isApproving.value = false
  }
}

// 3. Reject Flow
const initiateReject = () => {
  rejectReason.value = ''
  showViewDialog.value = false
  showRejectDialog.value = true
}

const submitReject = async () => {
  if (!selectedRequest.value) return
  
  isRejecting.value = true
  try {
    const response = await api.post(`/distributor/partner-requests/${selectedRequest.value.id}/reject`, {
      reason: rejectReason.value
    })
    
    if (response.data.success) {
      requests.value = requests.value.filter(req => req.id !== selectedRequest.value.id)
      showRejectDialog.value = false
      selectedRequest.value = null
      
      toast.info('Request Rejected', {
        description: 'Internal request has been declined.',
      })
    }
  } catch (error) {
    console.error(error)
    toast.error('Action Failed', {
      description: error.response?.data?.message
    })
    // showViewDialog.value = true // Optional: go back to details
  } finally {
    isRejecting.value = false
  }
}

onMounted(() => {
  fetchRequests()
})
</script>