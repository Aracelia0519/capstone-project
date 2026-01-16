<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
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
        <button class="px-4 py-2.5 bg-blue-600 text-white rounded-lg font-medium flex items-center gap-2 hover:bg-blue-700 transition-colors duration-200" @click="showAddTransaction = true">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Add Transaction</span>
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="space-y-1.5">
        <label class="block text-sm font-medium text-gray-700">Status</label>
        <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">All Status</option>
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
          <option value="overdue">Overdue</option>
        </select>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-sm font-medium text-gray-700">Type</label>
        <select v-model="filters.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="">All Types</option>
          <option value="payment">Payment</option>
          <option value="refund">Refund</option>
          <option value="expense">Expense</option>
        </select>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-sm font-medium text-gray-700">Date Range</label>
        <select v-model="filters.dateRange" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          <option value="all">All Time</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="quarter">This Quarter</option>
        </select>
      </div>
      
      <div class="space-y-1.5">
        <label class="block text-sm font-medium text-gray-700">Search</label>
        <div class="relative">
          <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input 
            v-model="filters.search" 
            type="text" 
            placeholder="Search by ID, name, amount..."
            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
      </div>
    </div>

    <!-- Stats Banner -->
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

    <!-- Transactions Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payer / Payee</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="transaction in paginatedTransactions" :key="transaction.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="font-mono text-sm text-gray-900">{{ transaction.id }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-700">{{ transaction.date }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <span class="font-medium text-gray-900">{{ transaction.party }}</span>
                  <span class="text-xs text-gray-500">{{ transaction.role }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['font-semibold', transaction.type === 'refund' ? 'text-red-600' : 'text-green-600']">
                  {{ transaction.amount }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium', 
                  transaction.status === 'paid' ? 'bg-green-100 text-green-800' : 
                  transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                  'bg-red-100 text-red-800']">
                  {{ transaction.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium',
                  transaction.type === 'payment' ? 'bg-blue-100 text-blue-800' :
                  transaction.type === 'refund' ? 'bg-purple-100 text-purple-800' :
                  'bg-orange-100 text-orange-800']">
                  {{ transaction.type }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-2">
                  <button class="p-1.5 rounded-md text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors duration-200" @click="viewTransaction(transaction)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button class="p-1.5 rounded-md text-gray-600 hover:text-green-600 hover:bg-green-50 transition-colors duration-200" @click="editTransaction(transaction)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredTransactions.length === 0" class="py-12 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No transactions found</h3>
        <p class="text-gray-500">Try adjusting your filters or add a new transaction</p>
      </div>

      <!-- Pagination -->
      <div v-if="filteredTransactions.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1"
          class="p-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        
        <span class="text-sm text-gray-700">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="p-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Add Transaction Modal -->
    <div v-if="showAddTransaction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click="showAddTransaction = false">
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Add New Transaction</h3>
          <button @click="showAddTransaction = false" class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Transaction Type</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="payment">Payment</option>
                <option value="refund">Refund</option>
                <option value="expense">Expense</option>
              </select>
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Amount (₱)</label>
              <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="0.00">
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Date</label>
              <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Party Type</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="client">Client</option>
                <option value="distributor">Distributor</option>
                <option value="service-provider">Service Provider</option>
              </select>
            </div>
            <div class="md:col-span-2 space-y-2">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-h-[100px]" placeholder="Enter transaction description..."></textarea>
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
          <button @click="showAddTransaction = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Save Transaction
          </button>
        </div>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// State
const showAddTransaction = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10

const filters = ref({
  status: '',
  type: '',
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
    if (filters.value.status && transaction.status !== filters.value.status) {
      return false
    }
    
    if (filters.value.type && transaction.type !== filters.value.type) {
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
/* Animation for modal */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .transaction-table th,
  .transaction-table td {
    padding-left: 12px;
    padding-right: 12px;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 4px;
  }
}
</style>