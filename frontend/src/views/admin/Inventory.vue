<template>
  <div class="inventory-overview p-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Inventory Overview</h1>
      <p class="text-gray-600">Monitor paint stock levels across all distributors</p>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
        <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-blue-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Distributors</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ distributors.length }}</h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
              <i class="fas fa-warehouse text-blue-500 text-xl"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-green-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Products in Stock</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ totalProducts }}</h3>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
              <i class="fas fa-palette text-green-500 text-xl"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-red-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Low Stock Items</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ lowStockCount }}</h3>
            </div>
            <div class="p-3 bg-red-50 rounded-lg">
              <i class="fas fa-exclamation-triangle text-red-500 text-xl"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl p-4 shadow-sm mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="relative">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search distributor or product..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-64"
            >
          </div>
          
          <select 
            v-model="selectedStatus"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="all">All Stock Levels</option>
            <option value="low">Low Stock Only</option>
            <option value="normal">Normal Stock</option>
          </select>
        </div>
        
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <i class="fas fa-info-circle"></i>
          <span>Read-only view for administrators</span>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-user-tie"></i>
                  <span>Distributor</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-paint-roller"></i>
                  <span>Paint Product</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-boxes"></i>
                  <span>Available Quantity</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-chart-line"></i>
                  <span>Stock Level</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-exclamation-circle"></i>
                  <span>Status</span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="item in filteredInventory" 
              :key="item.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-warehouse text-blue-600"></i>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ item.distributorName }}</div>
                    <div class="text-sm text-gray-500">{{ item.distributorCode }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div 
                    class="w-8 h-8 rounded-md mr-3 border" 
                    :style="{ backgroundColor: item.colorCode }"
                  ></div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ item.productName }}</div>
                    <div class="text-sm text-gray-500">{{ item.category }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-lg font-semibold">{{ item.quantity }}</div>
                <div class="text-sm text-gray-500">units</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="w-32 bg-gray-200 rounded-full h-2.5">
                  <div 
                    class="h-2.5 rounded-full"
                    :class="{
                      'bg-red-500': item.percentage <= 25,
                      'bg-yellow-500': item.percentage > 25 && item.percentage <= 50,
                      'bg-green-500': item.percentage > 50
                    }"
                    :style="{ width: `${item.percentage}%` }"
                  ></div>
                </div>
                <div class="text-sm text-gray-500 mt-1">{{ item.percentage }}% of max</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="{
                    'bg-red-100 text-red-800': item.status === 'Low Stock',
                    'bg-green-100 text-green-800': item.status === 'In Stock',
                    'bg-yellow-100 text-yellow-800': item.status === 'Warning'
                  }"
                >
                  <i 
                    class="mr-1"
                    :class="{
                      'fas fa-exclamation-triangle': item.status === 'Low Stock',
                      'fas fa-check-circle': item.status === 'In Stock',
                      'fas fa-exclamation-circle': item.status === 'Warning'
                    }"
                  ></i>
                  {{ item.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Empty State -->
      <div v-if="filteredInventory.length === 0" class="text-center py-12">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <i class="fas fa-box-open text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No inventory items found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Try adjusting your search or filter to find what you're looking for.</p>
      </div>
      
      <!-- Table Footer -->
      <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="text-sm text-gray-500 mb-2 md:mb-0">
            Showing <span class="font-medium">{{ filteredInventory.length }}</span> of 
            <span class="font-medium">{{ inventory.length }}</span> items
          </div>
          <div class="flex items-center gap-4">
            <button 
              @click="exportToCSV"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              <i class="fas fa-download mr-2"></i>
              Export CSV
            </button>
            <button 
              @click="refreshData"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700"
            >
              <i class="fas fa-sync-alt mr-2"></i>
              Refresh Data
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Information Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
      <div class="flex items-start">
        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
        <div>
          <h4 class="font-medium text-blue-900">Administrator View Only</h4>
          <p class="text-blue-700 text-sm mt-1">
            This page provides read-only access to distributor inventory data. 
            Administrators can monitor stock levels but cannot modify quantities directly.
            Contact individual distributors for inventory adjustments.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Inventory data
const inventory = ref([
  {
    id: 1,
    distributorName: 'BrightColors Inc.',
    distributorCode: 'BCI-001',
    productName: 'Premium White Interior',
    category: 'Interior Paint',
    colorCode: '#FFFFFF',
    quantity: 450,
    maxQuantity: 1000,
    percentage: 45,
    status: 'In Stock'
  },
  {
    id: 2,
    distributorName: 'ColorMasters Co.',
    distributorCode: 'CMC-002',
    productName: 'Ocean Blue Gloss',
    category: 'Exterior Paint',
    colorCode: '#4A90E2',
    quantity: 120,
    maxQuantity: 500,
    percentage: 24,
    status: 'Warning'
  },
  {
    id: 3,
    distributorName: 'PaintPros Distributors',
    distributorCode: 'PPD-003',
    productName: 'Forest Green Matte',
    category: 'Exterior Paint',
    colorCode: '#2ECC71',
    quantity: 30,
    maxQuantity: 200,
    percentage: 15,
    status: 'Low Stock'
  },
  {
    id: 4,
    distributorName: 'HueCraft Supplies',
    distributorCode: 'HCS-004',
    productName: 'Sunset Orange Satin',
    category: 'Interior Paint',
    colorCode: '#FF6B6B',
    quantity: 280,
    maxQuantity: 600,
    percentage: 47,
    status: 'In Stock'
  },
  {
    id: 5,
    distributorName: 'TintWorks Ltd.',
    distributorCode: 'TWL-005',
    productName: 'Slate Gray Semi-Gloss',
    category: 'Interior Paint',
    colorCode: '#7F8C8D',
    quantity: 75,
    maxQuantity: 300,
    percentage: 25,
    status: 'Warning'
  },
  {
    id: 6,
    distributorName: 'Brush & Bucket Co.',
    distributorCode: 'BBC-006',
    productName: 'Royal Purple Eggshell',
    category: 'Interior Paint',
    colorCode: '#9B59B6',
    quantity: 420,
    maxQuantity: 800,
    percentage: 53,
    status: 'In Stock'
  },
  {
    id: 7,
    distributorName: 'Coatings Unlimited',
    distributorCode: 'CUL-007',
    productName: 'Brick Red Primer',
    category: 'Primer',
    colorCode: '#E74C3C',
    quantity: 15,
    maxQuantity: 150,
    percentage: 10,
    status: 'Low Stock'
  },
  {
    id: 8,
    distributorName: 'FinishLine Distributors',
    distributorCode: 'FLD-008',
    productName: 'Canary Yellow High-Gloss',
    category: 'Exterior Paint',
    colorCode: '#F1C40F',
    quantity: 180,
    maxQuantity: 400,
    percentage: 45,
    status: 'In Stock'
  }
])

const distributors = computed(() => {
  const uniqueDistributors = new Set()
  inventory.value.forEach(item => uniqueDistributors.add(item.distributorName))
  return Array.from(uniqueDistributors)
})

const totalProducts = computed(() => {
  return inventory.value.length
})

const lowStockCount = computed(() => {
  return inventory.value.filter(item => item.status === 'Low Stock').length
})

// Filters
const searchQuery = ref('')
const selectedStatus = ref('all')

// Computed properties
const filteredInventory = computed(() => {
  let filtered = inventory.value
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(item => 
      item.distributorName.toLowerCase().includes(query) ||
      item.productName.toLowerCase().includes(query) ||
      item.distributorCode.toLowerCase().includes(query)
    )
  }
  
  // Apply status filter
  if (selectedStatus.value !== 'all') {
    if (selectedStatus.value === 'low') {
      filtered = filtered.filter(item => item.status === 'Low Stock')
    } else if (selectedStatus.value === 'normal') {
      filtered = filtered.filter(item => item.status !== 'Low Stock')
    }
  }
  
  return filtered
})

// Methods
const refreshData = () => {
  // Simulate data refresh
  console.log('Refreshing inventory data...')
  // In a real app, you would fetch new data from API
}

const exportToCSV = () => {
  const headers = ['Distributor', 'Code', 'Product', 'Category', 'Quantity', 'Status']
  const csvContent = [
    headers.join(','),
    ...filteredInventory.value.map(item => [
      item.distributorName,
      item.distributorCode,
      item.productName,
      item.category,
      item.quantity,
      item.status
    ].join(','))
  ].join('\n')
  
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'inventory-overview.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

onMounted(() => {
  // Simulate API call
  console.log('Inventory Overview mounted')
})
</script>

<style scoped>
/* Custom styles for the color swatches */
.w-8 {
  width: 2rem;
}
.h-8 {
  height: 2rem;
}
</style>