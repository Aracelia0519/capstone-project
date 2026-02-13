<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/utils/axios' 
import { 
  Search, 
  Filter, 
  CheckCircle2, 
  DollarSign, 
  MoreHorizontal,
  Loader2,
  AlertTriangle,
  CreditCard,
  Wallet
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'

// UI Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
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
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import { Textarea } from '@/components/ui/textarea' 

// --- State ---
const payrollRequests = ref([])
const searchQuery = ref('')
const statusFilter = ref('Approved') 
const isLoading = ref(false)
const isProcessing = ref(false)
const selectedPayroll = ref(null)

// Dialog States
const isConfirmOpen = ref(false) // The input form dialog
const isAlertOpen = ref(false)   // The "Are you sure?" safety dialog

// Payment Form State
const paymentForm = ref({
  notes: ''
})

// --- API Calls ---
const fetchPayrolls = async () => {
  isLoading.value = true
  try {
    const statusParam = statusFilter.value === 'All' ? '' : statusFilter.value

    const response = await api.get('/finance/disbursement', {
      params: {
        status: statusParam,
        search: searchQuery.value
      }
    })
    
    payrollRequests.value = response.data.data
  } catch (error) {
    console.error("Fetch Error:", error)
    toast.error('Failed to fetch payroll requests')
  } finally {
    isLoading.value = false
  }
}

watch([statusFilter, searchQuery], () => {
  fetchPayrolls()
})

// --- Computed ---
const totalPendingAmount = computed(() => {
  return payrollRequests.value
    .filter(p => p.status.toLowerCase() === 'approved')
    .reduce((sum, p) => sum + parseFloat(p.net_pay || 0), 0)
})

const totalProcessedCount = computed(() => {
  return payrollRequests.value.filter(p => p.status.toLowerCase() === 'paid').length
})

const detectedPaymentMethod = computed(() => {
  if (!selectedPayroll.value?.bank_name) return 'Unknown';
  const bn = selectedPayroll.value.bank_name.toLowerCase();
  if (bn.includes('gcash') || bn.includes('maya') || bn.includes('grab') || bn.includes('paypal')) {
    return 'E-Wallet';
  } else if (bn.includes('cash')) {
    return 'Cash';
  }
  return 'Bank Transfer';
})

// --- Actions ---
const formatCurrency = (value) => {
  const val = parseFloat(value)
  if (isNaN(val)) return '₱0.00'
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const openPaymentDialog = (payroll) => {
  selectedPayroll.value = payroll
  paymentForm.value.notes = ''
  isConfirmOpen.value = true
}

// Step 1: User clicks "Confirm Payment" in the form -> Opens Alert
const confirmInitialAction = () => {
  if (!selectedPayroll.value) return
  isAlertOpen.value = true
}

// Step 2: User clicks "Continue" in the Alert -> Calls API
const executePayment = async () => {
  if (!selectedPayroll.value) return

  isProcessing.value = true
  
  try {
    await api.post(`/finance/disbursement/${selectedPayroll.value.id}/pay`, {
      admin_notes: paymentForm.value.notes
    })

    toast.success(`Payroll Paid Successfully`, {
      description: `Funds marked as transferred via ${detectedPaymentMethod.value}.`,
    })

    // Reset all states
    isAlertOpen.value = false
    isConfirmOpen.value = false
    selectedPayroll.value = null
    fetchPayrolls() 

  } catch (error) {
    console.error(error)
    toast.error('Payment Processing Failed', {
      description: error.response?.data?.message || 'An error occurred while communicating with the server.'
    })
    isAlertOpen.value = false // Close alert so they can try again or fix input
  } finally {
    isProcessing.value = false
  }
}

onMounted(() => {
  fetchPayrolls()
})
</script>

<template>
  <div class="flex flex-col gap-6 p-6 bg-slate-50/50 min-h-screen">
    
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Payroll Disbursement</h1>
        <p class="text-slate-500">Manage approved payroll requests and process payouts.</p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" @click="fetchPayrolls">
          <Loader2 v-if="isLoading" class="mr-2 h-4 w-4 animate-spin" />
          Refresh
        </Button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Payouts</CardTitle>
          <DollarSign class="h-4 w-4 text-amber-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ formatCurrency(totalPendingAmount) }}</div>
          <p class="text-xs text-muted-foreground">Total approved & waiting</p>
        </CardContent>
      </Card>
      
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Processed (Visible)</CardTitle>
          <CheckCircle2 class="h-4 w-4 text-emerald-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ totalProcessedCount }} Employees</div>
          <p class="text-xs text-muted-foreground">Successfully paid</p>
        </CardContent>
      </Card>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Payroll Requests</CardTitle>
        <CardDescription>Review approved requests and authorize fund transfers.</CardDescription>
      </CardHeader>
      <CardContent>
        
        <div class="flex items-center justify-between py-4">
          <div class="flex items-center gap-2 flex-1 max-w-sm">
            <div class="relative w-full">
              <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
              <Input
                placeholder="Search employee or code..."
                v-model="searchQuery"
                class="pl-8"
              />
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <Select v-model="statusFilter">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Filter by Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="All">All Requests</SelectItem>
                <SelectItem value="Approved">To Pay (Approved)</SelectItem>
                <SelectItem value="Paid">Completed (Paid)</SelectItem>
              </SelectContent>
            </Select>
            <Button variant="outline" size="icon" @click="fetchPayrolls">
              <Filter class="h-4 w-4" />
            </Button>
          </div>
        </div>

        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Reference ID</TableHead>
                <TableHead>Employee</TableHead>
                <TableHead>Period</TableHead>
                <TableHead>Bank Details</TableHead>
                <TableHead class="text-right">Net Amount</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isLoading">
                 <TableCell colspan="7" class="h-24 text-center">
                    <div class="flex items-center justify-center gap-2">
                      <Loader2 class="h-6 w-6 animate-spin text-slate-500" />
                      <span>Loading payrolls...</span>
                    </div>
                 </TableCell>
              </TableRow>

              <TableRow v-else-if="payrollRequests.length === 0">
                <TableCell colspan="7" class="h-24 text-center">
                  No payroll requests found.
                </TableCell>
              </TableRow>
              
              <TableRow v-for="payroll in payrollRequests" :key="payroll.id">
                <TableCell class="font-medium text-xs">{{ payroll.payroll_code }}</TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span class="font-medium">{{ payroll.first_name }} {{ payroll.last_name }}</span>
                    <span class="text-xs text-muted-foreground">{{ payroll.department }}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="flex flex-col text-xs">
                    <span>{{ formatDate(payroll.period_start) }} -</span>
                    <span>{{ formatDate(payroll.period_end) }}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="flex flex-col gap-0.5">
                    <div class="flex items-center gap-1">
                      <Wallet v-if="['gcash', 'maya', 'paymaya'].some(x => payroll.bank_name?.toLowerCase().includes(x))" class="h-3 w-3 text-blue-500"/>
                      <CreditCard v-else class="h-3 w-3 text-slate-500"/>
                      <span class="font-semibold text-xs bg-slate-100 px-1 rounded">
                        {{ payroll.bank_name || 'N/A' }}
                      </span>
                    </div>
                    <span class="text-xs text-muted-foreground ml-4">
                      {{ payroll.account_number || payroll.employee_account_number || 'N/A' }}
                    </span>
                    <span class="text-[10px] text-slate-400 ml-4 italic" v-if="payroll.account_name || payroll.employee_account_name">
                      {{ payroll.account_name || payroll.employee_account_name }}
                    </span>
                  </div>
                </TableCell>
                <TableCell class="text-right font-bold text-slate-700">
                  {{ formatCurrency(payroll.net_pay) }}
                </TableCell>
                <TableCell>
                  <Badge 
                    :class="payroll.status.toLowerCase() === 'paid' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-100 text-amber-700 hover:bg-amber-200'"
                  >
                    {{ payroll.status.toUpperCase() }}
                  </Badge>
                  <div v-if="payroll.status.toLowerCase() === 'paid'" class="text-[10px] text-muted-foreground mt-1">
                     Method: {{ payroll.payment_method || 'Bank Transfer' }}
                  </div>
                </TableCell>
                <TableCell class="text-right">
                  
                  <Button 
                    v-if="payroll.status.toLowerCase() === 'approved'" 
                    size="sm" 
                    class="bg-blue-600 hover:bg-blue-700 text-white shadow-sm"
                    @click="openPaymentDialog(payroll)"
                  >
                    Pay Now
                  </Button>

                  <DropdownMenu v-else>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" class="h-8 w-8 p-0">
                        <span class="sr-only">Open menu</span>
                        <MoreHorizontal class="h-4 w-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                      <DropdownMenuLabel>Actions</DropdownMenuLabel>
                      <DropdownMenuItem>View Details</DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                  
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <Dialog v-model:open="isConfirmOpen">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Confirm Fund Transfer</DialogTitle>
          <DialogDescription>
            Verify bank details for {{ selectedPayroll?.first_name }} {{ selectedPayroll?.last_name }} before proceeding.
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="selectedPayroll" class="grid gap-4 py-4">
          <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg border">
            <span class="text-sm text-slate-500">Net Amount</span>
            <span class="text-xl font-bold text-slate-900">{{ formatCurrency(selectedPayroll.net_pay) }}</span>
          </div>
          
          <div class="space-y-2">
            <h4 class="text-sm font-medium leading-none flex items-center justify-between">
                <span>Bank Details</span>
                <Badge variant="outline" class="text-xs font-normal">
                    Method: {{ detectedPaymentMethod }}
                </Badge>
            </h4>
            <div class="grid grid-cols-2 gap-4 text-sm bg-slate-50 p-3 rounded border">
              <div class="flex flex-col">
                <span class="text-muted-foreground text-xs">Bank / Provider</span>
                <span class="font-medium">{{ selectedPayroll.bank_name || 'N/A' }}</span>
              </div>
              <div class="flex flex-col">
                <span class="text-muted-foreground text-xs">Account Number</span>
                <span class="font-mono">{{ selectedPayroll.employee_account_number || 'N/A' }}</span>
              </div>
              <div class="col-span-2 flex flex-col pt-2 border-t">
                <span class="text-muted-foreground text-xs">Account Name</span>
                <span class="font-medium">{{ selectedPayroll.employee_account_name || 'N/A' }}</span>
              </div>
            </div>
          </div>
          
          <Separator />
          
          
        </div>

        <DialogFooter>
          <Button variant="outline" @click="isConfirmOpen = false">
            Cancel
          </Button>
          <Button 
            @click="confirmInitialAction" 
            class="bg-emerald-600 hover:bg-emerald-700 text-white"
          >
            Proceed to Payment
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog v-model:open="isAlertOpen">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle class="flex items-center gap-2">
            <AlertTriangle class="h-5 w-5 text-amber-500" />
            Are you absolutely sure?
          </AlertDialogTitle>
          <AlertDialogDescription>
            This action cannot be undone. This will permanently mark this payroll request as 
            <span class="font-bold text-slate-900">PAID</span> via <span class="font-bold text-slate-900">{{ detectedPaymentMethod }}</span> and upload the account details to the database.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="isAlertOpen = false">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="executePayment"
            class="bg-emerald-600 hover:bg-emerald-700 text-white"
            :disabled="isProcessing"
          >
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            {{ isProcessing ? 'Processing...' : 'Yes, Release Funds' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>