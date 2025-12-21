<template>
  <div class="p-4 md:p-6">
    <!-- Page Header -->
    <div class="mb-6 md:mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Distributor Dashboard</h1>
      <p class="text-gray-600 mt-2">Overview of your paint distribution operations</p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
      <!-- Total Paint Products Card -->
      <div class="bg-white rounded-xl shadow-sm p-4 md:p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <div class="p-2 bg-blue-50 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          </div>
          <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">
            +2.5%
          </span>
        </div>
        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Paint Products</h3>
        <div class="flex items-baseline">
          <span class="text-2xl md:text-3xl font-bold text-gray-800">{{ totalProducts }}</span>
          <span class="text-gray-500 ml-2">products</span>
        </div>
      </div>

      <!-- Current Inventory Card -->
      <div class="bg-white rounded-xl shadow-sm p-4 md:p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <div class="p-2 bg-green-50 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-gray-500 mb-1">Current Inventory Level</h3>
        <div class="flex items-baseline">
          <span class="text-2xl md:text-3xl font-bold text-gray-800">{{ formatNumber(inventoryLevel) }}</span>
          <span class="text-gray-500 ml-2">units</span>
        </div>
        <div class="mt-3">
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-green-500 h-2 rounded-full" :style="{ width: `${inventoryPercentage}%` }"></div>
          </div>
          <p class="text-xs text-gray-500 mt-1">Capacity: {{ inventoryPercentage }}%</p>
        </div>
      </div>

      <!-- Low Stock Alerts Card -->
      <div class="bg-white rounded-xl shadow-sm p-4 md:p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <div class="p-2 bg-red-50 rounded-lg">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.286 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
        </div>
        <h3 class="text-sm font-medium text-gray-500 mb-1">Low Stock Alerts</h3>
        <div class="flex items-baseline">
          <span class="text-2xl md:text-3xl font-bold text-gray-800">{{ lowStockAlerts }}</span>
          <span class="text-gray-500 ml-2">items</span>
        </div>
        <p class="text-sm text-red-600 mt-2" v-if="lowStockAlerts > 0">
          {{ getLowStockMessage() }}
        </p>
        <p class="text-sm text-green-600 mt-2" v-else>
          All items are sufficiently stocked
        </p>
      </div>

      <!-- Total Orders Card -->
      <div class="bg-white rounded-xl shadow-sm p-4 md:p-5 border border-gray-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <div class="p-2 bg-purple-50 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
          </div>
          <span class="text-sm font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">
            +{{ recentOrders }}
          </span>
        </div>
        <h3 class="text-sm font-medium text-gray-500 mb-1">Total Orders / Requests</h3>
        <div class="flex items-baseline">
          <span class="text-2xl md:text-3xl font-bold text-gray-800">{{ formatNumber(totalOrders) }}</span>
          <span class="text-gray-500 ml-2">orders</span>
        </div>
        <p class="text-sm text-gray-600 mt-2">{{ recentOrders }} new this week</p>
      </div>
    </div>

    <!-- Recent Transactions Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="px-4 md:px-6 py-4 border-b border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between">
          <h2 class="text-lg md:text-xl font-semibold text-gray-800">Recent Transactions</h2>
          <button @click="viewAllTransactions" class="mt-2 sm:mt-0 text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
            View All
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </div>
        <p class="text-gray-600 text-sm mt-1">Last 5 transactions from your distributors</p>
      </div>

      <!-- Transactions Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-medium text-gray-700">Order ID</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-medium text-gray-700">Service Provider</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-medium text-gray-700 hidden sm:table-cell">Date</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-medium text-gray-700">Amount</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-medium text-gray-700">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="transaction in recentTransactions" :key="transaction.id" class="hover:bg-gray-50">
              <td class="py-3 px-4 md:px-6">
                <span class="font-medium text-gray-900">#{{ transaction.id }}</span>
              </td>
              <td class="py-3 px-4 md:px-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ transaction.provider }}</p>
                    <p class="text-sm text-gray-500">{{ transaction.items }} items</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 md:px-6 text-gray-600 hidden sm:table-cell">
                {{ formatDate(transaction.date) }}
              </td>
              <td class="py-3 px-4 md:px-6">
                <span class="font-medium text-gray-900">₱{{ formatNumber(transaction.amount) }}</span>
              </td>
              <td class="py-3 px-4 md:px-6">
                <span :class="getStatusClasses(transaction.status)" class="px-3 py-1 rounded-full text-xs font-medium">
                  {{ transaction.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="recentTransactions.length === 0" class="text-center py-8">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        <p class="text-gray-500">No recent transactions found</p>
      </div>
    </div>

    <!-- Quick Actions (Mobile Only) -->
    <div class="mt-6 sm:hidden">
      <div class="grid grid-cols-2 gap-3">
        <button @click="refreshData" class="bg-white border border-gray-300 rounded-lg p-3 flex items-center justify-center hover:bg-gray-50">
          <svg class="w-5 h-5 text-gray-700 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Refresh
        </button>
        <button @click="viewInventory" class="bg-blue-600 text-white rounded-lg p-3 flex items-center justify-center hover:bg-blue-700">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
          View Inventory
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'DistributorDashboard',
  data() {
    return {
      totalProducts: 156,
      inventoryLevel: 12450,
      inventoryCapacity: 20000,
      lowStockAlerts: 8,
      totalOrders: 2347,
      recentOrders: 12,
      recentTransactions: [
        {
          id: 'ORD-00124',
          provider: 'Cavite Paint Services',
          items: 5,
          date: '2024-01-15',
          amount: 24500,
          status: 'Completed'
        },
        {
          id: 'ORD-00123',
          provider: 'Imus Paint Center',
          items: 3,
          date: '2024-01-14',
          amount: 15600,
          status: 'Processing'
        },
        {
          id: 'ORD-00122',
          provider: 'Bacoor Construction',
          items: 8,
          date: '2024-01-13',
          amount: 42150,
          status: 'Completed'
        },
        {
          id: 'ORD-00121',
          provider: 'Dasmarinas Painters',
          items: 2,
          date: '2024-01-12',
          amount: 8900,
          status: 'Pending'
        },
        {
          id: 'ORD-00120',
          provider: 'Trece Martires Co.',
          items: 4,
          date: '2024-01-11',
          amount: 18700,
          status: 'Completed'
        }
      ]
    }
  },
  computed: {
    inventoryPercentage() {
      return Math.round((this.inventoryLevel / this.inventoryCapacity) * 100)
    }
  },
  methods: {
    formatNumber(num) {
      return new Intl.NumberFormat('en-PH').format(num)
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    },
    getStatusClasses(status) {
      const classes = {
        'Completed': 'bg-green-100 text-green-800',
        'Processing': 'bg-yellow-100 text-yellow-800',
        'Pending': 'bg-blue-100 text-blue-800',
        'Cancelled': 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
    getLowStockMessage() {
      if (this.lowStockAlerts === 1) {
        return '1 item needs restocking'
      } else if (this.lowStockAlerts <= 3) {
        return `${this.lowStockAlerts} items need attention`
      } else {
        return `Urgent: ${this.lowStockAlerts} items need restocking`
      }
    },
    viewAllTransactions() {
      this.$emit('view-transactions')
      console.log('View all transactions clicked')
    },
    refreshData() {
      console.log('Refreshing dashboard data...')
      // In a real app, this would fetch fresh data from API
    },
    viewInventory() {
      this.$emit('view-inventory')
      console.log('View inventory clicked')
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for better mobile experience */
@media (max-width: 640px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
  
  .overflow-x-auto::-webkit-scrollbar {
    height: 4px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
  }
}

/* Smooth hover transitions */
.bg-white {
  transition: all 0.2s ease-in-out;
}

/* Responsive text sizing */
@media (max-width: 640px) {
  :deep(h1) {
    font-size: 1.5rem;
  }
  
  :deep(.text-2xl) {
    font-size: 1.25rem;
  }
  
  :deep(.text-3xl) {
    font-size: 1.5rem;
  }
}

/* Card hover effects */
.hover\\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Loading skeleton animation (if needed later) */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>