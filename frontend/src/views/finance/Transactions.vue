<template>
  <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </span>
          Transactions & Refunds
        </h1>
        <p class="text-sm text-gray-600">Review and process refunds and financial transactions.</p>
      </div>
      <div class="flex gap-3">
        <Button @click="fetchTransactions" variant="outline" class="w-full sm:w-auto bg-white hover:bg-slate-50 border-slate-200 text-slate-700 shadow-sm">
          <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh List
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="space-y-1.5">
        <Label>Status</Label>
        <Select v-model="filters.status">
          <SelectTrigger>
            <SelectValue placeholder="All Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Status</SelectItem>
            <SelectItem value="completed">Completed</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="rejected">Rejected</SelectItem>
          </SelectContent>
        </Select>
      </div>
      
      <div class="space-y-1.5">
        <Label>Type</Label>
        <Select v-model="filters.type">
          <SelectTrigger>
            <SelectValue placeholder="All Types" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Types</SelectItem>
            <SelectItem value="refund">Refund</SelectItem>
            <SelectItem value="payment">Payment</SelectItem>
          </SelectContent>
        </Select>
      </div>
      
      <div class="space-y-1.5">
        <Label>Search</Label>
        <div class="relative">
          <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <Input 
            v-model="filters.search" 
            type="text" 
            placeholder="Search by ID, name, amount..."
            class="pl-10"
          />
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
      <div class="text-center">
        <span class="block text-sm text-gray-600 mb-1">Total Records</span>
        <span class="text-xl font-bold text-gray-900">{{ filteredTransactions.length }}</span>
      </div>
      <div class="text-center">
        <span class="block text-sm text-gray-600 mb-1">Total Amount Selected</span>
        <span class="text-xl font-bold text-gray-900">₱{{ totalAmount.toLocaleString() }}</span>
      </div>
      <div class="text-center">
        <span class="block text-sm text-gray-600 mb-1">Pending Amount</span>
        <span class="text-xl font-bold text-amber-600">₱{{ pendingAmount.toLocaleString() }}</span>
      </div>
    </div>

    <Card class="mb-8 overflow-hidden border-slate-200">
      <div v-if="loading" class="p-12 text-center text-slate-500 flex flex-col items-center">
        <svg class="w-8 h-8 animate-spin text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        <p>Loading transactions...</p>
      </div>
      <div v-else class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50 border-b border-gray-200">
            <TableRow>
              <TableHead class="font-semibold text-gray-700">Transaction ID</TableHead>
              <TableHead class="font-semibold text-gray-700">Date</TableHead>
              <TableHead class="font-semibold text-gray-700">Payer / Payee</TableHead>
              <TableHead class="font-semibold text-gray-700 text-right">Amount</TableHead>
              <TableHead class="font-semibold text-gray-700 text-center">Status</TableHead>
              <TableHead class="font-semibold text-gray-700 text-center">Type</TableHead>
              <TableHead class="font-semibold text-gray-700 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="transaction in paginatedTransactions" :key="transaction.id" class="hover:bg-slate-50">
              <TableCell class="font-mono text-sm text-gray-900">{{ transaction.id }}</TableCell>
              <TableCell class="text-sm text-gray-700">{{ transaction.date }}</TableCell>
              <TableCell>
                <div class="flex flex-col">
                  <span class="font-medium text-gray-900">{{ transaction.party }}</span>
                  <span class="text-xs text-gray-500">{{ transaction.role }}</span>
                </div>
              </TableCell>
              <TableCell class="text-right">
                <span class="font-bold text-gray-900">
                  ₱{{ transaction.amount.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}
                </span>
              </TableCell>
              <TableCell class="text-center">
                <Badge :class="[
                  'rounded-full font-medium shadow-none px-2.5 py-0.5', 
                  transaction.status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 
                  transaction.status === 'pending' ? 'bg-amber-100 text-amber-700' : 
                  'bg-red-100 text-red-700']">
                  {{ transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) }}
                </Badge>
              </TableCell>
              <TableCell class="text-center">
                <Badge class="rounded-full font-medium shadow-none px-2.5 py-0.5 bg-purple-100 text-purple-700">
                  {{ transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1) }}
                </Badge>
              </TableCell>
              <TableCell class="text-right">
                <Button 
                  size="sm" 
                  variant="outline" 
                  @click="viewTransaction(transaction)"
                  class="h-8 px-4 bg-white hover:bg-blue-50 border-slate-200 text-blue-600 shadow-sm">
                  Review
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="filteredTransactions.length === 0 && !loading" class="py-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No transactions found</h3>
        <p class="text-gray-500">Try adjusting your filters.</p>
      </div>

      <div v-if="filteredTransactions.length > 0 && !loading" class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
        <Button variant="outline" size="icon" @click="prevPage" :disabled="currentPage === 1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
        </Button>
        <span class="text-sm text-gray-700">Page {{ currentPage }} of {{ totalPages || 1 }}</span>
        <Button variant="outline" size="icon" @click="nextPage" :disabled="currentPage >= totalPages">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
        </Button>
      </div>
    </Card>

    <Dialog :open="!!selectedTransaction" @update:open="(val) => !val && closeModal()">
      <DialogContent class="bg-white border-none shadow-2xl text-slate-900 w-[95vw] sm:max-w-xl rounded-2xl overflow-hidden p-0">
        <DialogHeader class="p-5 border-b border-slate-100 bg-white">
          <DialogTitle class="text-xl flex items-center justify-between">
            <span class="font-bold tracking-tight">Process Transaction</span>
            <span class="font-mono text-sm px-2 py-1 bg-slate-100 text-slate-600 rounded-md border border-slate-200" v-if="selectedTransaction">{{ selectedTransaction.id }}</span>
          </DialogTitle>
          <DialogDescription class="text-slate-500 mt-1">
            Review transaction details and release funds via PayMongo.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedTransaction" class="p-6 bg-slate-50 space-y-5">
          <div class="grid grid-cols-2 gap-4 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div>
              <span class="text-xs text-slate-500 block mb-1">Payee (Client)</span>
              <span class="font-semibold text-slate-900">{{ selectedTransaction.party }}</span>
            </div>
            <div>
              <span class="text-xs text-slate-500 block mb-1">Transaction Type</span>
              <span class="font-medium text-slate-700 capitalize">{{ selectedTransaction.type }}</span>
            </div>
            <div>
              <span class="text-xs text-slate-500 block mb-1">GCash Account Number</span>
              <span class="font-mono text-blue-600 font-medium">{{ selectedTransaction.gcash_number }}</span>
            </div>
            <div>
              <span class="text-xs text-slate-500 block mb-1">Total Amount</span>
              <span class="font-bold text-emerald-600 text-lg">₱{{ selectedTransaction.amount.toLocaleString(undefined, { minimumFractionDigits: 2 }) }}</span>
            </div>
          </div>

          <div v-if="selectedTransaction.status === 'pending'" class="p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start gap-3 text-blue-800 text-sm">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div>
              <p class="font-bold mb-1">Ready for Processing</p>
              <p>Approving this will deduct the amount directly from overall revenue logs and redirect you to PayMongo to complete the transfer.</p>
            </div>
          </div>
        </div>
        
        <div v-if="selectedTransaction" class="p-5 border-t border-slate-100 bg-white flex justify-end gap-3">
          <Button variant="outline" @click="closeModal" class="border-slate-300 text-slate-700">Close</Button>
          
          <template v-if="selectedTransaction.status === 'pending'">
            <Button variant="outline" @click="requirePermission('update', initiateReject)" class="text-red-600 border-red-200 hover:bg-red-50">Reject</Button>
            <Button @click="requirePermission('update', initiateProcess)" class="bg-blue-600 hover:bg-blue-700 text-white" :disabled="isProcessing">
              <span v-if="isProcessing" class="mr-2 animate-spin">◷</span>
              Proceed to GCash
            </Button>
          </template>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="alertOpen" @update:open="alertOpen = $event">
      <AlertDialogContent class="bg-white border-slate-200 rounded-2xl shadow-xl">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-xl font-bold">{{ alertConfig.title }}</AlertDialogTitle>
          <AlertDialogDescription class="text-base mt-2">
            {{ alertConfig.description }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-6">
          <AlertDialogCancel @click="alertOpen = false" class="border-slate-300">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeAction" :disabled="isProcessing" :class="alertConfig.confirmClass">
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
import { Toaster, toast } from 'vue-sonner' 
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Card } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'

const router = useRouter()
const route = useRoute()

// State
const loading = ref(false)
const isProcessing = ref(false)
const transactions = ref([])
const selectedTransaction = ref(null)

const currentPage = ref(1)
const itemsPerPage = 10

const filters = ref({
  status: 'all',
  type: 'all',
  search: ''
})

const permissions = ref({
  can_view: false,
  can_create: false,
  can_update: false,
  can_delete: false
})

const alertOpen = ref(false)
const pendingAction = ref(null)
const alertConfig = ref({
  title: '',
  description: '',
  confirmText: 'Continue',
  confirmClass: ''
})

// RBAC
const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied`, { description: `You do not have permission to ${action} transactions.` });
    return;
  }
  if (callback) callback();
}

// Fetch Logic
const fetchTransactions = async () => {
  loading.value = true
  try {
    const response = await api.get('/finance/transactions')
    if (response.data.success !== undefined) {
      transactions.value = response.data.data
      if (response.data.permissions) {
        permissions.value = response.data.permissions
      }
    }
  } catch (error) {
    if (error.response?.status === 403) {
      toast.error("Unauthorized", { description: "You do not have permission to view transactions." })
    } else {
      toast.error("Failed to load transactions from server")
    }
  } finally {
    loading.value = false
  }
}

// PayMongo Redirect Verification
const verifyGcashPayment = async (transactionCode) => {
  loading.value = true
  toast.info('Verifying Transaction... Please wait.')
  
  await new Promise(resolve => setTimeout(resolve, 2500));
  
  try {
    const response = await api.post('/finance/transactions/refund/verify-gcash', { transaction_code: transactionCode })
    if (response.data.success) {
      toast.success('Transaction Completed!', { description: 'Funds have been transferred and logged successfully.' })
      router.replace({ query: {} }) 
    }
  } catch (error) {
    toast.error('Verification Failed', { description: error.response?.data?.message || 'The transaction session could not be verified.' })
    router.replace({ query: {} })
  } finally {
    fetchTransactions()
  }
}

onMounted(() => {
  if (route.query.transaction_code) {
    verifyGcashPayment(route.query.transaction_code)
  } else {
    fetchTransactions()
  }
})

// Computed properties
const filteredTransactions = computed(() => {
  return transactions.value.filter(transaction => {
    if (filters.value.status && filters.value.status !== 'all' && transaction.status !== filters.value.status) return false
    if (filters.value.type && filters.value.type !== 'all' && transaction.type !== filters.value.type) return false
    
    if (filters.value.search) {
      const searchTerm = filters.value.search.toLowerCase()
      return (
        transaction.id.toLowerCase().includes(searchTerm) ||
        transaction.party.toLowerCase().includes(searchTerm) ||
        transaction.amount.toString().includes(searchTerm)
      )
    }
    return true
  })
})

const totalAmount = computed(() => {
  return filteredTransactions.value.reduce((sum, transaction) => sum + transaction.amount, 0)
})

const pendingAmount = computed(() => {
  return filteredTransactions.value
    .filter(t => t.status === 'pending')
    .reduce((sum, transaction) => sum + transaction.amount, 0)
})

const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredTransactions.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredTransactions.value.length / itemsPerPage)
})

// Handlers
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const viewTransaction = (transaction) => {
  selectedTransaction.value = transaction
}

const closeModal = () => {
  selectedTransaction.value = null
}

const initiateProcess = () => {
  pendingAction.value = 'process'
  alertConfig.value = {
    title: 'Proceed to PayMongo',
    description: `You are about to transfer ₱${selectedTransaction.value.amount.toLocaleString()} to ${selectedTransaction.value.party} via GCash.`,
    confirmText: 'Continue to Checkout',
    confirmClass: 'bg-blue-600 hover:bg-blue-700 text-white'
  }
  alertOpen.value = true
}

const initiateReject = () => {
  pendingAction.value = 'reject'
  alertConfig.value = {
    title: 'Reject Transaction',
    description: `Are you sure you want to reject this transaction request?`,
    confirmText: 'Yes, Reject',
    confirmClass: 'bg-red-600 hover:bg-red-700 text-white border-0'
  }
  alertOpen.value = true
}

const executeAction = async () => {
  if (pendingAction.value === 'process') await processTransaction()
  else if (pendingAction.value === 'reject') await rejectTransaction()
  if (!isProcessing.value) alertOpen.value = false
}

const processTransaction = async () => {
  if (!selectedTransaction.value) return

  try {
    isProcessing.value = true
    const currentPath = window.location.origin + route.path
    
    const response = await api.post(`/finance/transactions/refund/${selectedTransaction.value.db_id}/process`, {
      return_url: currentPath
    })
    
    if (response.data.checkout_url) {
        toast.success('Redirecting to PayMongo...')
        setTimeout(() => { window.location.href = response.data.checkout_url }, 1500)
    }
  } catch (error) {
    if (error.response?.status === 400) {
      toast.error('Cannot process', { description: error.response?.data?.message })
    } else {
      toast.error('Error', { description: 'Failed to initiate transaction.' })
    }
    alertOpen.value = false
  } finally {
    isProcessing.value = false
  }
}

const rejectTransaction = async () => {
  if (!selectedTransaction.value) return

  try {
    isProcessing.value = true
    const promise = api.post(`/finance/transactions/refund/${selectedTransaction.value.db_id}/reject`)
    
    toast.promise(promise, {
      loading: 'Rejecting request...',
      success: () => {
        fetchTransactions()
        closeModal()
        return `Transaction has been rejected.`
      },
      error: 'Failed to reject transaction.'
    })
  } catch (error) {
    console.error(error)
  } finally {
    isProcessing.value = false
  }
}
</script>

<style scoped>
@media (max-width: 640px) {
  table th, table td {
    padding-left: 12px;
    padding-right: 12px;
  }
}
</style>