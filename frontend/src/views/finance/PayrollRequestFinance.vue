<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { 
  Search, 
  Filter, 
  Eye, 
  MoreHorizontal, 
  FileText 
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import axios from '@/utils/axios'

// UI Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
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
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Separator } from '@/components/ui/separator'

// State
const requests = ref([])
const isLoading = ref(false)
const searchQuery = ref('')
const statusFilter = ref('pending') 
const selectedRequest = ref(null)
const isDetailsOpen = ref(false)
const isProcessing = ref(false)

// Alert Dialog State
const isAlertOpen = ref(false)
const alertAction = ref(null) // 'approve' | 'reject'
const alertTargetId = ref(null)

// Fetch Data
const fetchRequests = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('finance/payroll-requests', {
      params: {
        status: statusFilter.value,
        search: searchQuery.value
      }
    })
    requests.value = response.data.data
  } catch (error) {
    console.error(error)
    toast.error("Failed to load payroll requests.")
  } finally {
    isLoading.value = false
  }
}

watch([statusFilter, searchQuery], () => {
  fetchRequests()
})

onMounted(() => {
  fetchRequests()
})

// Format Currency (PHP)
const formatCurrency = (amount) => {
  const val = parseFloat(amount) || 0
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
  }).format(val)
}

// Format Date
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Actions
const openDetails = (request) => {
  selectedRequest.value = request
  isDetailsOpen.value = true
}

// 1. Trigger Alert for Approval
const initiateApprove = (id) => {
  alertTargetId.value = id
  alertAction.value = 'approve'
  isAlertOpen.value = true
}

// 2. Trigger Alert for Rejection
const initiateReject = (id) => {
  alertTargetId.value = id
  alertAction.value = 'reject'
  isAlertOpen.value = true
}

// 3. Confirm and Execute Action
const confirmAction = async () => {
  if (!alertTargetId.value || !alertAction.value) return

  isProcessing.value = true
  const id = alertTargetId.value
  const action = alertAction.value

  try {
    if (action === 'approve') {
      await axios.post(`finance/payroll-requests/${id}/approve`)
      toast.success(`Payroll request approved successfully.`)
    } else if (action === 'reject') {
      await axios.post(`finance/payroll-requests/${id}/reject`)
      toast.info(`Payroll request has been rejected.`)
    }
    
    // Close dialogs and refresh
    isAlertOpen.value = false
    isDetailsOpen.value = false
    fetchRequests() 

  } catch (error) {
    console.error(error)
    toast.error(`Failed to ${action} request.`)
  } finally {
    isProcessing.value = false
    alertTargetId.value = null
    alertAction.value = null
  }
}

// Helper for badge colors
const getStatusVariant = (status) => {
  switch (status) {
    case 'approved': return 'default'
    case 'rejected': 
    case 'cancelled': return 'destructive'
    case 'pending': return 'secondary'
    default: return 'outline'
  }
}
</script>

<template>
  <div class="p-6 space-y-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Payroll Approvals</h1>
        <p class="text-muted-foreground">Review and approve payroll requests submitted by HR and Managers.</p>
      </div>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Request Queue</CardTitle>
        <CardDescription>
          Manage employee compensation requests.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div class="flex flex-col md:flex-row gap-4 mb-6">
          <div class="relative w-full md:w-80">
            <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input 
              v-model="searchQuery" 
              placeholder="Search code or name..." 
              class="pl-8" 
              @keyup.enter="fetchRequests"
            />
          </div>
          <Select v-model="statusFilter">
            <SelectTrigger class="w-full md:w-[180px]">
              <div class="flex items-center gap-2">
                <Filter class="h-4 w-4" />
                <SelectValue placeholder="Filter by Status" />
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="All">All Statuses</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="approved">Approved</SelectItem>
              <SelectItem value="rejected">Rejected</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Request Code</TableHead>
                <TableHead>Employee</TableHead>
                <TableHead>Pay Period</TableHead>
                <TableHead>Created By</TableHead>
                <TableHead class="text-right">Net Pay</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="isLoading">
                <TableRow>
                   <TableCell colspan="7" class="h-24 text-center">Loading requests...</TableCell>
                </TableRow>
              </template>
              
              <template v-else-if="requests.length === 0">
                 <TableRow>
                   <TableCell colspan="7" class="h-24 text-center">No requests found.</TableCell>
                 </TableRow>
              </template>

              <TableRow v-else v-for="req in requests" :key="req.id">
                <TableCell class="font-medium text-xs">{{ req.payroll_code }}</TableCell>
                <TableCell>
                  <div class="flex items-center gap-3">
                    <Avatar class="h-8 w-8">
                      <AvatarImage :src="req.user?.avatar || ''" />
                      <AvatarFallback>{{ req.user?.first_name?.charAt(0) || 'U' }}</AvatarFallback>
                    </Avatar>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ req.user?.full_name || 'Unknown' }}</span>
                      <span class="text-xs text-muted-foreground">{{ req.position }}</span>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="text-xs">
                    {{ formatDate(req.period_start) }} - {{ formatDate(req.period_end) }}
                </TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span class="text-sm">{{ req.creator?.full_name || 'System' }}</span>
                    <span class="text-xs text-muted-foreground">{{ formatDate(req.created_at) }}</span>
                  </div>
                </TableCell>
                <TableCell class="text-right font-bold font-mono">
                  {{ formatCurrency(req.net_pay) }}
                </TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(req.status)" class="capitalize">
                    {{ req.status }}
                  </Badge>
                </TableCell>
                <TableCell class="text-right">
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" class="h-8 w-8 p-0">
                        <span class="sr-only">Open menu</span>
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuLabel>Actions</DropdownMenuLabel>
                      <DropdownMenuItem @click="openDetails(req)">
                        <Eye class="mr-2 h-4 w-4" />
                        View Details
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <Dialog v-model:open="isDetailsOpen">
      <DialogContent class="sm:max-w-[500px]">
        <DialogHeader>
          <DialogTitle>Payroll Request Details</DialogTitle>
          <DialogDescription>
            Review breakdown for {{ selectedRequest?.payroll_code }}
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="grid gap-4 py-4">
          <div class="flex items-center gap-4 bg-muted/50 p-3 rounded-lg">
            <Avatar class="h-12 w-12 border">
              <AvatarFallback>{{ selectedRequest.user?.first_name?.charAt(0) }}</AvatarFallback>
            </Avatar>
            <div>
              <h3 class="font-semibold">{{ selectedRequest.user?.full_name }}</h3>
              <p class="text-sm text-muted-foreground">{{ selectedRequest.position }}</p>
            </div>
            <div class="ml-auto">
               <Badge :variant="getStatusVariant(selectedRequest.status)">
                 {{ selectedRequest.status }}
               </Badge>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <span class="text-muted-foreground">Period:</span>
              <p class="font-medium">
                  {{ formatDate(selectedRequest.period_start) }} - {{ formatDate(selectedRequest.period_end) }}
              </p>
            </div>
            <div>
              <span class="text-muted-foreground">Submitted By:</span>
              <p class="font-medium">{{ selectedRequest.creator?.full_name || 'System' }}</p>
            </div>
          </div>

          <Separator />

          <div class="space-y-3">
            <h4 class="font-semibold flex items-center gap-2">
              <FileText class="h-4 w-4" /> Breakdown
            </h4>
            
            <div class="flex justify-between">
              <span class="text-muted-foreground">Basic Salary</span>
              <span>{{ formatCurrency(selectedRequest.basic_salary) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-muted-foreground">Gross Pay</span>
              <span>{{ formatCurrency(selectedRequest.gross_pay) }}</span>
            </div>

            <div class="flex justify-between text-red-500 text-sm">
              <span>SSS</span>
              <span>- {{ formatCurrency(selectedRequest.sss_contribution) }}</span>
            </div>
             <div class="flex justify-between text-red-500 text-sm">
              <span>PhilHealth</span>
              <span>- {{ formatCurrency(selectedRequest.philhealth_contribution) }}</span>
            </div>
             <div class="flex justify-between text-red-500 text-sm">
              <span>Pag-IBIG</span>
              <span>- {{ formatCurrency(selectedRequest.pagibig_contribution) }}</span>
            </div>
            
            <div class="flex justify-between text-red-500 font-medium">
              <span>Total Deductions</span>
              <span>- {{ formatCurrency(selectedRequest.total_deductions) }}</span>
            </div>
            
            <Separator class="my-2" />
            
            <div class="flex justify-between font-bold text-lg">
              <span>Net Pay</span>
              <span>{{ formatCurrency(selectedRequest.net_pay) }}</span>
            </div>
          </div>
        </div>

        <DialogFooter v-if="selectedRequest?.status === 'pending'">
          <Button variant="outline" @click="initiateReject(selectedRequest.id)" :disabled="isProcessing">
            Reject
          </Button>
          <Button @click="initiateApprove(selectedRequest.id)" :disabled="isProcessing">
            Approve Request
          </Button>
        </DialogFooter>
        <DialogFooter v-else>
          <Button variant="secondary" @click="isDetailsOpen = false">Close</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog v-model:open="isAlertOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            <span v-if="alertAction === 'approve'">
              This will approve the payroll request and mark it as ready for payment processing. This action cannot be easily undone.
            </span>
            <span v-else>
              This will reject the payroll request. You may need to provide a reason to the HR department offline.
            </span>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="isAlertOpen = false" :disabled="isProcessing">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click.prevent="confirmAction" 
            :disabled="isProcessing"
            :class="alertAction === 'reject' ? 'bg-destructive hover:bg-destructive/90' : ''"
          >
            <span v-if="isProcessing">Processing...</span>
            <span v-else>
              {{ alertAction === 'approve' ? 'Yes, Approve' : 'Yes, Reject' }}
            </span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>