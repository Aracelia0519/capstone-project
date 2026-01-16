<!-- ECommerceOrders.vue -->
<template>
  <div class="ecommerce-orders p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Order Management</h1>
          <p class="text-gray-300">Track customer orders from placement to fulfillment</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-white transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export
          </button>
        </div>
      </div>
    </div>

    <!-- Order Stats -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ totalOrders }}</div>
        <div class="text-sm text-gray-300">Total Orders</div>
      </div>
      <div class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ orders.filter(o => o.status === 'Pending').length }}</div>
        <div class="text-sm text-gray-300">Pending</div>
      </div>
      <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ orders.filter(o => o.status === 'Paid').length }}</div>
        <div class="text-sm text-gray-300">Paid</div>
      </div>
      <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ orders.filter(o => o.status === 'Shipped').length }}</div>
        <div class="text-sm text-gray-300">Shipped</div>
      </div>
      <div class="bg-gradient-to-br from-indigo-500/20 to-violet-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ orders.filter(o => o.status === 'Delivered').length }}</div>
        <div class="text-sm text-gray-300">Delivered</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm text-gray-300 mb-2">Search Order</label>
          <input type="text" v-model="searchQuery" placeholder="Search by order ID or client..." 
                 class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500">
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Status</label>
          <select v-model="selectedStatus" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Paid">Paid</option>
            <option value="Shipped">Shipped</option>
            <option value="Delivered">Delivered</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Date Range</label>
          <select v-model="dateRange" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-white transition-colors">
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-900/80">
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Order ID</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Client</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Order Date</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Total Amount</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id" 
                class="border-t border-gray-800 hover:bg-white/5">
              <td class="py-4 px-6">
                <div class="font-mono text-white font-medium">{{ order.orderId }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-xs mr-3">
                    {{ order.client.charAt(0) }}
                  </div>
                  <div>
                    <div class="text-white">{{ order.client }}</div>
                    <div class="text-sm text-gray-400">{{ order.phone }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="text-gray-300">{{ order.orderDate }}</div>
                <div class="text-sm text-gray-400">{{ order.orderTime }}</div>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  statusClasses[order.status]
                ]">
                  {{ order.status }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="text-white font-medium">₱{{ order.totalAmount.toLocaleString() }}</div>
                <div class="text-sm text-gray-400">{{ order.items }} items</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex space-x-2">
                  <button @click="viewOrder(order)" 
                         class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-lg hover:bg-blue-500/30 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    View
                  </button>
                  <button @click="updateStatus(order)" 
                         class="px-3 py-1 bg-gray-800 text-gray-300 border border-gray-700 rounded-lg hover:bg-gray-700 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Update
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xl font-bold text-white">Order Details</h3>
              <p class="text-gray-400">{{ selectedOrder.orderId }}</p>
            </div>
            <button @click="selectedOrder = null" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <!-- Order Summary -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Client Information</h4>
              <div class="space-y-2">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-xs mr-3">
                    {{ selectedOrder.client.charAt(0) }}
                  </div>
                  <div>
                    <div class="text-white">{{ selectedOrder.client }}</div>
                    <div class="text-sm text-gray-400">{{ selectedOrder.phone }}</div>
                  </div>
                </div>
                <div class="text-sm text-gray-400 mt-3">
                  {{ selectedOrder.address }}
                </div>
              </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Order Information</h4>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-400">Order Date:</span>
                  <span class="text-white">{{ selectedOrder.orderDate }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Status:</span>
                  <span :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    statusClasses[selectedOrder.status]
                  ]">
                    {{ selectedOrder.status }}
                  </span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Payment Method:</span>
                  <span class="text-white">{{ selectedOrder.paymentMethod }}</span>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Summary</h4>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-400">Subtotal:</span>
                  <span class="text-white">₱{{ (selectedOrder.totalAmount * 0.9).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Shipping:</span>
                  <span class="text-white">₱{{ (selectedOrder.totalAmount * 0.1).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-700">
                  <span class="text-white">Total:</span>
                  <span class="text-white">₱{{ selectedOrder.totalAmount.toLocaleString() }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Order Items -->
          <div class="mb-8">
            <h4 class="text-lg font-semibold text-white mb-4">Order Items</h4>
            <div class="bg-gray-800/50 rounded-xl overflow-hidden">
              <table class="w-full">
                <thead>
                  <tr class="bg-gray-900/80">
                    <th class="text-left py-3 px-6 text-sm text-gray-300 font-medium">Product</th>
                    <th class="text-left py-3 px-6 text-sm text-gray-300 font-medium">Quantity</th>
                    <th class="text-left py-3 px-6 text-sm text-gray-300 font-medium">Unit Price</th>
                    <th class="text-left py-3 px-6 text-sm text-gray-300 font-medium">Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder.items" :key="item.id" 
                      class="border-t border-gray-700/50">
                    <td class="py-3 px-6">
                      <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                          </svg>
                        </div>
                        <div>
                          <div class="text-white">{{ item.name }}</div>
                          <div class="text-sm text-gray-400">{{ item.category }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-6 text-gray-300">{{ item.quantity }}</td>
                    <td class="py-3 px-6 text-gray-300">₱{{ item.unitPrice.toLocaleString() }}</td>
                    <td class="py-3 px-6 text-white font-medium">₱{{ (item.quantity * item.unitPrice).toLocaleString() }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Distributor & Service Provider -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Assigned Distributor</h4>
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-lg mr-4">
                  {{ selectedOrder.distributor.charAt(0) }}
                </div>
                <div>
                  <div class="text-white font-medium">{{ selectedOrder.distributor }}</div>
                  <div class="text-sm text-gray-400">{{ selectedOrder.distributorContact }}</div>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Assigned Service Provider</h4>
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white text-lg mr-4">
                  {{ selectedOrder.serviceProvider.charAt(0) }}
                </div>
                <div>
                  <div class="text-white font-medium">{{ selectedOrder.serviceProvider }}</div>
                  <div class="text-sm text-gray-400">{{ selectedOrder.serviceProviderContact }}</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
            <button @click="selectedOrder = null" 
                   class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
              Close
            </button>
            <button @click="updateStatus(selectedOrder)" 
                   class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity">
              Update Status
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedStatus = ref('')
const dateRange = ref('')
const selectedOrder = ref(null)

const statusClasses = {
  'Pending': 'bg-amber-500/20 text-amber-300',
  'Paid': 'bg-green-500/20 text-green-300',
  'Shipped': 'bg-purple-500/20 text-purple-300',
  'Delivered': 'bg-indigo-500/20 text-indigo-300',
  'Cancelled': 'bg-red-500/20 text-red-300'
}

const orders = ref([
  { 
    id: 1, 
    orderId: 'ORD-2024-001', 
    client: 'Juan Dela Cruz', 
    phone: '0917-123-4567',
    address: '123 Main St, Manila',
    orderDate: '2024-03-15', 
    orderTime: '14:30',
    status: 'Pending', 
    totalAmount: 12450,
    items: 3,
    paymentMethod: 'GCash',
    items: [
      { id: 1, name: 'Premium White Interior Paint', category: 'Interior', quantity: 2, unitPrice: 2450 },
      { id: 2, name: 'Quick Dry Primer', category: 'Primer', quantity: 1, unitPrice: 1280 },
      { id: 3, name: 'Painting Tools Set', category: 'Tools', quantity: 1, unitPrice: 3450 }
    ],
    distributor: 'CaviteGo Distributors',
    distributorContact: '0918-987-6543',
    serviceProvider: 'PaintPro Services',
    serviceProviderContact: '0927-555-1234'
  },
  { 
    id: 2, 
    orderId: 'ORD-2024-002', 
    client: 'Maria Santos', 
    phone: '0922-888-9999',
    address: '456 Oak Ave, Quezon City',
    orderDate: '2024-03-14', 
    orderTime: '10:15',
    status: 'Paid', 
    totalAmount: 8900,
    items: 2,
    paymentMethod: 'PayMaya',
    items: [
      { id: 4, name: 'Weatherproof Exterior Paint', category: 'Exterior', quantity: 1, unitPrice: 3250 },
      { id: 5, name: 'Wall Painting Service', category: 'Service', quantity: 1, unitPrice: 5650 }
    ],
    distributor: 'Premium Paint Supply',
    distributorContact: '0917-777-8888',
    serviceProvider: 'Perfect Painters',
    serviceProviderContact: '0932-444-5555'
  },
  { 
    id: 3, 
    orderId: 'ORD-2024-003', 
    client: 'Pedro Reyes', 
    phone: '0916-111-2222',
    address: '789 Pine St, Makati',
    orderDate: '2024-03-13', 
    orderTime: '16:45',
    status: 'Shipped', 
    totalAmount: 15600,
    items: 4,
    paymentMethod: 'Bank Transfer',
    items: [
      { id: 6, name: 'Gloss Finish Coating', category: 'Coating', quantity: 2, unitPrice: 2980 },
      { id: 7, name: 'Eco-Friendly Paint', category: 'Interior', quantity: 1, unitPrice: 2750 },
      { id: 8, name: 'Paint Brushes Set', category: 'Tools', quantity: 1, unitPrice: 1890 }
    ],
    distributor: 'Metro Paint Hub',
    distributorContact: '0923-333-4444',
    serviceProvider: 'Elite Painting Co.',
    serviceProviderContact: '0945-666-7777'
  },
  { 
    id: 4, 
    orderId: 'ORD-2024-004', 
    client: 'Ana Lopez', 
    phone: '0933-444-5555',
    address: '321 Elm St, Taguig',
    orderDate: '2024-03-12', 
    orderTime: '09:20',
    status: 'Delivered', 
    totalAmount: 6750,
    items: 1,
    paymentMethod: 'COD',
    items: [
      { id: 9, name: 'Anti-Mold Exterior Paint', category: 'Exterior', quantity: 1, unitPrice: 3850 },
      { id: 10, name: 'Protective Gloves', category: 'Tools', quantity: 2, unitPrice: 1450 }
    ],
    distributor: 'South Paint Distributors',
    distributorContact: '0919-222-3333',
    serviceProvider: 'Quality Paint Services',
    serviceProviderContact: '0928-777-8888'
  }
])

const totalOrders = computed(() => orders.value.length)

const filteredOrders = computed(() => {
  return orders.value.filter(order => {
    const matchesSearch = searchQuery.value === '' || 
      order.orderId.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      order.client.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesStatus = selectedStatus.value === '' || 
      order.status === selectedStatus.value
    
    return matchesSearch && matchesStatus
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  dateRange.value = ''
}

const viewOrder = (order) => {
  selectedOrder.value = order
}

const updateStatus = (order) => {
  const statuses = ['Pending', 'Paid', 'Shipped', 'Delivered', 'Cancelled']
  const currentIndex = statuses.indexOf(order.status)
  const nextIndex = (currentIndex + 1) % statuses.length
  order.status = statuses[nextIndex]
  
  if (selectedOrder.value) {
    selectedOrder.value.status = statuses[nextIndex]
  }
}
</script>

<style scoped>
.ecommerce-orders {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-orders {
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>