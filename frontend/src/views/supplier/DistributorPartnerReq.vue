<template>
  <div class="flex flex-col gap-6 p-8 bg-slate-50/50 min-h-screen">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Distributor Partnerships</h1>
        <p class="text-slate-500 mt-2 text-sm">
          Manage incoming partnership proposals from distributors who want to sell your products.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="fetchRequests">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4 mr-2" />
          Refresh Requests
        </Button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Proposals</CardTitle>
          <Briefcase class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ requests.length }}</div>
          <p class="text-xs text-muted-foreground">Awaiting your decision</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active Partners</CardTitle>
          <Building2 class="h-4 w-4 text-emerald-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">--</div>
          <p class="text-xs text-muted-foreground">Active in network</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">New This Week</CardTitle>
          <CalendarRange class="h-4 w-4 text-orange-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ requests.length }}</div>
          <p class="text-xs text-muted-foreground">Recent partnership inquiries</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Response Rate</CardTitle>
          <Clock class="h-4 w-4 text-slate-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">100%</div>
          <p class="text-xs text-muted-foreground">Response performance</p>
        </CardContent>
      </Card>
    </div>

    <Card class="col-span-4 border-slate-200 shadow-sm">
      <CardHeader>
        <div class="flex items-center justify-between">
          <div>
            <CardTitle>Incoming Requests</CardTitle>
            <CardDescription class="mt-1">
              Review details of distributors requesting to carry your brand.
            </CardDescription>
          </div>
          <div class="relative w-72">
            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input v-model="searchQuery" placeholder="Search distributor company..." class="pl-8" />
          </div>
        </div>
      </CardHeader>
      
      <CardContent>
        <div v-if="loading" class="flex flex-col items-center justify-center py-12 space-y-4">
          <Loader2 class="w-10 h-10 animate-spin text-primary" />
          <p class="text-sm text-muted-foreground">Fetching proposals...</p>
        </div>

        <div v-else-if="filteredRequests.length === 0" class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed rounded-lg border-slate-100">
          <div class="bg-slate-50 p-3 rounded-full mb-4">
            <Inbox class="w-8 h-8 text-slate-300" />
          </div>
          <h3 class="text-lg font-semibold text-slate-900">No Pending Requests</h3>
          <p class="text-slate-500 text-sm max-w-sm mt-1">
            You have no pending partnership proposals at the moment.
          </p>
        </div>

        <div v-else class="rounded-md border border-slate-200">
          <Table>
            <TableHeader class="bg-slate-50/50">
              <TableRow>
                <TableHead class="w-[300px]">Distributor Profile</TableHead>
                <TableHead>Location</TableHead>
                <TableHead>Request Date</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="req in filteredRequests" :key="req.id" class="hover:bg-slate-50/50 transition-colors">
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm">
                      {{ req.company_name ? req.company_name.substring(0, 2).toUpperCase() : 'NA' }}
                    </div>
                    <div class="flex flex-col">
                      <span class="font-semibold text-slate-900">{{ req.company_name }}</span>
                      <span class="text-xs text-slate-500">{{ req.contact_person }}</span>
                    </div>
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="flex flex-col text-sm text-slate-600">
                    <span v-if="req.city !== 'N/A'">{{ req.city }}, {{ req.province }}</span>
                    <span v-else class="text-slate-400 italic">No address verified</span>
                    <span class="text-xs text-slate-400">Distributor ID: {{ req.distributor_id }}</span>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="flex items-center gap-2 text-sm text-slate-600">
                    <Calendar class="w-3.5 h-3.5 text-slate-400" />
                    {{ req.date_requested }}
                  </div>
                </TableCell>

                <TableCell>
                  <Badge variant="secondary" class="bg-amber-100 text-amber-700 hover:bg-amber-100 border-amber-200">
                    Pending Review
                  </Badge>
                </TableCell>

                <TableCell class="text-right">
                  <Button 
                    variant="outline" 
                    size="sm"
                    class="h-8 gap-2 text-slate-700 hover:bg-slate-100 hover:text-slate-900"
                    @click="openViewDialog(req)"
                  >
                    <Eye class="h-4 w-4" />
                    View Proposal
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewDialog" @update:open="showViewDialog = $event">
      <DialogContent class="sm:max-w-[650px]">
        <DialogHeader>
          <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-50 rounded-lg">
              <Store class="h-5 w-5 text-blue-600" />
            </div>
            <div>
              <DialogTitle>Distributor Proposal</DialogTitle>
              <DialogDescription>
                Review the distributor's business details and message.
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div v-if="selectedRequest" class="grid gap-6 py-4">
          <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-4">
            <div class="grid grid-cols-2 gap-y-4 gap-x-8 text-sm">
              <div class="col-span-2 sm:col-span-1 space-y-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Company Name</span>
                <p class="font-medium text-slate-900 text-base">{{ selectedRequest.company_name }}</p>
              </div>
              <div class="col-span-2 sm:col-span-1 space-y-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Business Reg. No</span>
                <p class="font-mono text-slate-700">{{ selectedRequest.reg_number }}</p>
              </div>
              <div class="col-span-2 sm:col-span-1 space-y-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Representative</span>
                <p class="font-medium text-slate-900">{{ selectedRequest.contact_person }}</p>
              </div>
              <div class="col-span-2 sm:col-span-1 space-y-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact Info</span>
                <p class="text-slate-700">{{ selectedRequest.email }}</p>
                <p class="text-slate-700">{{ selectedRequest.phone }}</p>
              </div>
               <div class="col-span-2 space-y-1">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Business Address</span>
                <p class="text-slate-700">{{ selectedRequest.full_address }}</p>
              </div>
            </div>
          </div>

          <div class="space-y-3">
            <h4 class="text-sm font-medium leading-none text-slate-900">Proposal Message</h4>
            <div class="p-4 bg-white rounded-md border border-slate-200 text-sm text-slate-600 italic leading-relaxed">
              "{{ selectedRequest.message }}"
            </div>
          </div>
          
          <div v-if="selectedRequest.distributor_req_id" class="flex items-center gap-2 text-xs text-emerald-600 bg-emerald-50 w-fit px-3 py-1.5 rounded-full border border-emerald-100">
            <CheckCircle2 class="h-3.5 w-3.5" />
            <span>Business Documents Verified by Admin</span>
          </div>
          <div v-else class="flex items-center gap-2 text-xs text-amber-600 bg-amber-50 w-fit px-3 py-1.5 rounded-full border border-amber-100">
            <ShieldAlert class="h-3.5 w-3.5" />
            <span>Documents Not Yet Verified</span>
          </div>
        </div>

        <DialogFooter class="flex gap-2 sm:justify-between items-center pt-2">
           <Button variant="ghost" size="sm" class="text-slate-400" @click="showViewDialog = false">
             Close Preview
           </Button>
           <div class="flex gap-2 w-full sm:w-auto">
              <Button 
                variant="destructive" 
                class="flex-1 sm:flex-none"
                @click="initiateReject"
              >
                <X class="mr-2 h-4 w-4" />
                Decline
              </Button>
              <Button 
                class="flex-1 sm:flex-none bg-emerald-600 hover:bg-emerald-700 text-white" 
                @click="initiateApprove"
              >
                <Check class="mr-2 h-4 w-4" />
                Accept Partnership
              </Button>
           </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showApproveAlert" @update:open="showApproveAlert = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Accept Partnership?</AlertDialogTitle>
          <AlertDialogDescription>
            You are about to accept <span class="font-bold text-slate-900">{{ selectedRequest?.company_name }}</span> as an authorized distributor. 
            <br/><br/>
            They will gain access to your product catalog and wholesale pricing. This will officially create a record in your active partners list.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showApproveAlert = false">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="confirmApprove" 
            class="bg-emerald-600 hover:bg-emerald-700 text-white"
          >
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            Confirm Partnership
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showRejectDialog" @update:open="showRejectDialog = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <div class="flex items-center gap-4 mb-2">
            <div class="p-2 bg-red-50 rounded-full">
              <ShieldAlert class="h-6 w-6 text-red-600" />
            </div>
            <div>
              <DialogTitle>Decline Proposal</DialogTitle>
              <DialogDescription>
                Please state why you are declining this distributor.
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>
        
        <div class="py-4 space-y-4">
          <div class="space-y-2">
            <Label for="reason" class="text-xs font-semibold uppercase tracking-wider text-slate-500">Reason</Label>
            <Textarea 
              id="reason" 
              v-model="rejectReason" 
              placeholder="e.g., Region already saturated, Requirements not met..."
              class="resize-none focus-visible:ring-red-500"
              rows="4"
            />
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="showRejectDialog = false">Back</Button>
          <Button 
            variant="destructive"
            @click="submitReject" 
            :disabled="isProcessing"
          >
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            Decline Proposal
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios' // Updated to use your real axios instance
import { toast } from 'vue-sonner'
import { 
  Loader2, 
  RefreshCw, 
  Search, 
  Eye,
  Check,
  X,
  Briefcase,
  Building2,
  CalendarRange,
  Clock,
  Inbox,
  Calendar,
  Store,
  CheckCircle2,
  ShieldAlert
} from 'lucide-vue-next'

// Shadcn Components
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'

// State
const loading = ref(false)
const searchQuery = ref('')
const isProcessing = ref(false)

// Dialog Visibility
const showViewDialog = ref(false)
const showApproveAlert = ref(false)
const showRejectDialog = ref(false)

// Data Selection
const selectedRequest = ref(null)
const rejectReason = ref('')
const requests = ref([]) // Empty initially

// Computed Properties
const filteredRequests = computed(() => {
  if (!searchQuery.value) return requests.value
  const query = searchQuery.value.toLowerCase()
  return requests.value.filter(req => 
    (req.company_name && req.company_name.toLowerCase().includes(query)) ||
    (req.contact_person && req.contact_person.toLowerCase().includes(query)) ||
    (req.email && req.email.toLowerCase().includes(query))
  )
})

// --- API Methods ---

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/supplier/distributor-requests')
    if (response.data.success) {
        requests.value = response.data.data
        toast.success('Synced', { description: 'Latest proposals loaded.' })
    }
  } catch (error) {
    console.error(error)
    toast.error('Error fetching data', {
        description: error.response?.data?.message || 'Check your connection'
    })
  } finally {
    loading.value = false
  }
}

// 1. View Details
const openViewDialog = (req) => {
  selectedRequest.value = req
  showViewDialog.value = true
}

// 2. Approve Workflow
const initiateApprove = () => {
  showViewDialog.value = false
  showApproveAlert.value = true
}

const confirmApprove = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true
  
  try {
    const response = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/approve`)
    
    if (response.data.success) {
        // Remove from list
        requests.value = requests.value.filter(r => r.id !== selectedRequest.value.id)
        showApproveAlert.value = false
        selectedRequest.value = null
        
        toast.success('Partnership Activated', {
            description: 'Distributor added to official partners.',
            class: 'bg-emerald-50 border-emerald-200 text-emerald-800'
        })
    }
  } catch (error) {
    console.error(error)
    toast.error('Approval Failed', {
        description: error.response?.data?.message
    })
  } finally {
    isProcessing.value = false
  }
}

// 3. Reject Workflow
const initiateReject = () => {
  rejectReason.value = ''
  showViewDialog.value = false
  showRejectDialog.value = true
}

const submitReject = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true

  try {
    const response = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/reject`, {
        reason: rejectReason.value
    })
    
    if (response.data.success) {
        requests.value = requests.value.filter(r => r.id !== selectedRequest.value.id)
        showRejectDialog.value = false
        selectedRequest.value = null
        
        toast.info('Proposal Declined', {
            description: 'The distributor has been notified.',
        })
    }
  } catch (error) {
    console.error(error)
    toast.error('Rejection Failed', {
        description: error.response?.data?.message
    })
  } finally {
    isProcessing.value = false
  }
}

onMounted(() => {
  fetchRequests()
})
</script>