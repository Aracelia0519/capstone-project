<template>
  <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-blue-100 text-blue-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
          </span>
          Transactions
        </h1>
        <p class="text-sm text-gray-600">Record and monitor all financial transactions</p>
      </div>
      <div class="flex gap-3">
        <Button class="bg-blue-600 hover:bg-blue-700 text-white gap-2" @click="showAddTransaction = true">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Add Transaction</span>
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
            <SelectItem value="paid">Paid</SelectItem>
            <SelectItem value="pending">Pending</SelectItem>
            <SelectItem value="overdue">Overdue</SelectItem>
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
            <SelectItem value="payment">Payment</SelectItem>
            <SelectItem value="refund">Refund</SelectItem>
            <SelectItem value="expense">Expense</SelectItem>
          </SelectContent>
        </Select>
      </div>
      
      <div class="space-y-1.5">
        <Label>Date Range</Label>
        <Select v-model="filters.dateRange">
          <SelectTrigger>
            <SelectValue placeholder="Select Range" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Time</SelectItem>
            <SelectItem value="today">Today</SelectItem>
            <SelectItem value="week">This Week</SelectItem>
            <SelectItem value="month">This Month</SelectItem>
            <SelectItem value="quarter">This Quarter</SelectItem>
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
        <span class="block text-sm text-gray-600 mb-1">Total Transactions</span>
        <span class="text-xl font-bold text-gray-900">{{ filteredTransactions.length }}</span>
      </div>
      <div class="text-center">
        <span class="block text-sm text-gray-600 mb-1">Total Amount</span>
        <span class="text-xl font-bold text-gray-900">₱{{ totalAmount.toLocaleString() }}</span>
      </div>
      <div class="text-center">
        <span class="block text-sm text-gray-600 mb-1">Average per Transaction</span>
        <span class="text-xl font-bold text-gray-900">₱{{ averageAmount.toLocaleString() }}</span>
      </div>
    </div>

    <Card class="mb-8 overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead>Transaction ID</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Payer / Payee</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="transaction in paginatedTransactions" :key="transaction.id">
              <TableCell class="font-mono text-sm text-gray-900">{{ transaction.id }}</TableCell>
              <TableCell class="text-sm text-gray-700">{{ transaction.date }}</TableCell>
              <TableCell>
                <div class="flex flex-col">
                  <span class="font-medium text-gray-900">{{ transaction.party }}</span>
                  <span class="text-xs text-gray-500">{{ transaction.role }}</span>
                </div>
              </TableCell>
              <TableCell>
                <span :class="['font-semibold', transaction.type === 'refund' ? 'text-red-600' : 'text-green-600']">
                  {{ transaction.amount }}
                </span>
              </TableCell>
              <TableCell>
                <Badge :class="[
                  'rounded-full font-medium shadow-none hover:bg-opacity-100', 
                  transaction.status === 'paid' ? 'bg-green-100 text-green-800 hover:bg-green-100' : 
                  transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100' : 
                  'bg-red-100 text-red-800 hover:bg-red-100']">
                  {{ transaction.status }}
                </Badge>
              </TableCell>
              <TableCell>
                <Badge :class="[
                  'rounded-full font-medium shadow-none hover:bg-opacity-100',
                  transaction.type === 'payment' ? 'bg-blue-100 text-blue-800 hover:bg-blue-100' :
                  transaction.type === 'refund' ? 'bg-purple-100 text-purple-800 hover:bg-purple-100' :
                  'bg-orange-100 text-orange-800 hover:bg-orange-100']">
                  {{ transaction.type }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex gap-2">
                  <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-600 hover:text-blue-600 hover:bg-blue-50" @click="viewTransaction(transaction)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </Button>
                  <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-600 hover:text-green-600 hover:bg-green-50" @click="editTransaction(transaction)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="filteredTransactions.length === 0" class="py-12 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No transactions found</h3>
        <p class="text-gray-500">Try adjusting your filters or add a new transaction</p>
      </div>

      <div v-if="filteredTransactions.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
        <Button 
          variant="outline"
          size="icon"
          @click="prevPage" 
          :disabled="currentPage === 1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Button>
        
        <span class="text-sm text-gray-700">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        
        <Button 
          variant="outline"
          size="icon"
          @click="nextPage" 
          :disabled="currentPage === totalPages"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Button>
      </div>
    </Card>

    <Dialog v-model:open="showAddTransaction">
      <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Add New Transaction</DialogTitle>
        </DialogHeader>

        <div class="py-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label>Transaction Type</Label>
              <Select>
                <SelectTrigger>
                  <SelectValue placeholder="Select Type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="payment">Payment</SelectItem>
                  <SelectItem value="refund">Refund</SelectItem>
                  <SelectItem value="expense">Expense</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label>Amount (₱)</Label>
              <Input type="number" placeholder="0.00" />
            </div>
            <div class="space-y-2">
              <Label>Date</Label>
              <Input type="date" />
            </div>
            <div class="space-y-2">
              <Label>Party Type</Label>
              <Select>
                <SelectTrigger>
                  <SelectValue placeholder="Select Party" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="client">Client</SelectItem>
                  <SelectItem value="distributor">Distributor</SelectItem>
                  <SelectItem value="service-provider">Service Provider</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="md:col-span-2 space-y-2">
              <Label>Description</Label>
              <Textarea placeholder="Enter transaction description..." />
            </div>
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="showAddTransaction = false">Cancel</Button>
          <Button class="bg-blue-600 hover:bg-blue-700 text-white">Save Transaction</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Card } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

// State
const showAddTransaction = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10

const filters = ref({
  status: 'all', // Changed from '' to 'all' for Select compatibility
  type: 'all',   // Changed from '' to 'all' for Select compatibility
  dateRange: 'all',
  search: ''
})

// Mock data
const transactions = ref([
  { id: 'TRX-001', date: '2024-01-15', party: 'John Smith', role: 'Client', amount: '₱5,000', status: 'paid', type: 'payment' },
  { id: 'TRX-002', date: '2024-01-15', party: 'Paint Supplies Co.', role: 'Supplier', amount: '₱2,500', status: 'pending', type: 'expense' },
  { id: 'TRX-003', date: '2024-01-14', party: 'Maria Santos', role: 'Service Provider', amount: '₱3,000', status: 'paid', type: 'payment' },
  { id: 'TRX-004', date: '2024-01-14', party: 'ABC Distributors', role: 'Distributor', amount: '₱8,500', status: 'overdue', type: 'payment' },
  { id: 'TRX-005', date: '2024-01-13', party: 'Mark Johnson', role: 'Client', amount: '₱1,200', status: 'paid', type: 'refund' },
  { id: 'TRX-006', date: '2024-01-13', party: 'Service Pro Inc.', role: 'Service Provider', amount: '₱4,300', status: 'pending', type: 'payment' },
  { id: 'TRX-007', date: '2024-01-12', party: 'Jane Doe', role: 'Client', amount: '₱6,700', status: 'paid', type: 'payment' },
  { id: 'TRX-008', date: '2024-01-12', party: 'XYZ Supplies', role: 'Supplier', amount: '₱3,800', status: 'paid', type: 'expense' },
  { id: 'TRX-009', date: '2024-01-11', party: 'Robert Brown', role: 'Distributor', amount: '₱9,500', status: 'pending', type: 'payment' },
  { id: 'TRX-010', date: '2024-01-11', party: 'Sarah Wilson', role: 'Client', amount: '₱2,100', status: 'paid', type: 'refund' },
  { id: 'TRX-011', date: '2024-01-10', party: 'Global Paint', role: 'Supplier', amount: '₱7,400', status: 'paid', type: 'expense' },
  { id: 'TRX-012', date: '2024-01-10', party: 'Mike Taylor', role: 'Service Provider', amount: '₱5,600', status: 'overdue', type: 'payment' },
])

// Computed properties
const filteredTransactions = computed(() => {
  return transactions.value.filter(transaction => {
    // Check for 'all' or empty string
    if (filters.value.status && filters.value.status !== 'all' && transaction.status !== filters.value.status) {
      return false
    }
    
    if (filters.value.type && filters.value.type !== 'all' && transaction.type !== filters.value.type) {
      return false
    }
    
    if (filters.value.search) {
      const searchTerm = filters.value.search.toLowerCase()
      return (
        transaction.id.toLowerCase().includes(searchTerm) ||
        transaction.party.toLowerCase().includes(searchTerm) ||
        transaction.amount.toLowerCase().includes(searchTerm)
      )
    }
    
    return true
  })
})

const totalAmount = computed(() => {
  return filteredTransactions.value.reduce((sum, transaction) => {
    const amount = parseFloat(transaction.amount.replace('₱', '').replace(',', ''))
    return sum + (isNaN(amount) ? 0 : amount)
  }, 0)
})

const averageAmount = computed(() => {
  return filteredTransactions.value.length > 0 
    ? Math.round(totalAmount.value / filteredTransactions.value.length)
    : 0
})

const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredTransactions.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredTransactions.value.length / itemsPerPage)
})

// Methods
const viewTransaction = (transaction) => {
  alert(`Viewing transaction: ${transaction.id}`)
}

const editTransaction = (transaction) => {
  alert(`Editing transaction: ${transaction.id}`)
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

// Lifecycle
onMounted(() => {
  console.log('Transactions component mounted')
})
</script>

<style scoped>
/* Responsive adjustments */
@media (max-width: 640px) {
  .transaction-table th,
  .transaction-table td {
    padding-left: 12px;
    padding-right: 12px;
  }
}
</style>