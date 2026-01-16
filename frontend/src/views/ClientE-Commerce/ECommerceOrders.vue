<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
            <p class="text-gray-600 mt-2">Track your purchases and service requests</p>
          </div>
          
          <!-- Order Filters -->
          <div class="mt-4 md:mt-0 flex items-center space-x-4">
            <div class="relative">
              <select
                v-model="statusFilter"
                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white"
              >
                <option value="all">All Orders</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
              </div>
            </div>
            
            <button
              @click="refreshOrders"
              class="p-2 text-gray-600 hover:text-blue-600 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Content -->
    <div class="container mx-auto px-4 py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Orders</p>
              <p class="text-2xl font-bold text-gray-900">{{ orders.length }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Pending</p>
              <p class="text-2xl font-bold text-yellow-600">{{ pendingCount }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Processing</p>
              <p class="text-2xl font-bold text-blue-600">{{ processingCount }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Completed</p>
              <p class="text-2xl font-bold text-green-600">{{ completedCount }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders List -->
      <div class="space-y-6">
        <!-- Empty State -->
        <div v-if="filteredOrders.length === 0" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-12 text-center">
          <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-700 mb-2">No orders found</h3>
          <p class="text-gray-500 mb-6">
            {{
              statusFilter === 'all' 
                ? "You haven't placed any orders yet"
                : `No ${statusFilter} orders found`
            }}
          </p>
          <router-link
            to="/ecommerce/shop"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            Start Shopping
          </router-link>
        </div>

        <!-- Orders -->
        <div v-for="order in filteredOrders" :key="order.id" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
          <!-- Order Header -->
          <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
              <div class="mb-4 md:mb-0">
                <div class="flex items-center">
                  <h3 class="text-lg font-bold text-gray-900 mr-3">Order #{{ order.id }}</h3>
                  <span :class="[
                    'px-3 py-1 rounded-full text-sm font-semibold',
                    getStatusClass(order.status)
                  ]">
                    {{ getStatusText(order.status) }}
                  </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                  Placed on {{ formatDate(order.date) }}
                </p>
              </div>
              
              <div class="text-right">
                <div class="text-2xl font-bold text-gray-900">₱{{ order.totalAmount.toLocaleString() }}</div>
                <p class="text-sm text-gray-500">{{ order.items.length }} items</p>
              </div>
            </div>
          </div>
          
          <!-- Order Items -->
          <div class="p-6">
            <div class="space-y-4">
              <div
                v-for="item in order.items.slice(0, 2)"
                :key="item.id"
                class="flex items-center"
              >
                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center mr-4">
                  <div v-if="item.type === 'product'" class="w-10 h-10 rounded-full" :style="{ backgroundColor: item.color }"></div>
                  <svg v-else class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                  </svg>
                </div>
                
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900">{{ item.name }}</h4>
                  <div class="flex justify-between mt-1">
                    <p class="text-sm text-gray-500">
                      <span v-if="item.type === 'product'">Qty: {{ item.quantity }}</span>
                      <span v-else>{{ item.date }}</span>
                    </p>
                    <p class="text-sm font-medium text-gray-900">
                      ₱{{ (item.price * (item.quantity || 1)).toLocaleString() }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Show More Items -->
              <div v-if="order.items.length > 2" class="pt-4 border-t border-gray-100">
                <button
                  @click="toggleOrderDetails(order.id)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
                >
                  <span>{{ order.expanded ? 'Show less' : `+${order.items.length - 2} more items` }}</span>
                  <svg :class="[
                    'w-4 h-4 ml-1 transition-transform',
                    order.expanded ? 'rotate-180' : ''
                  ]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </button>
                
                <!-- Additional Items -->
                <div v-if="order.expanded" class="mt-4 space-y-4">
                  <div
                    v-for="item in order.items.slice(2)"
                    :key="item.id"
                    class="flex items-center"
                  >
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-gray-50 to-slate-50 flex items-center justify-center mr-3">
                      <div v-if="item.type === 'product'" class="w-8 h-8 rounded-full" :style="{ backgroundColor: item.color }"></div>
                      <svg v-else class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                      </svg>
                    </div>
                    
                    <div class="flex-1">
                      <h4 class="text-sm font-medium text-gray-900">{{ item.name }}</h4>
                      <div class="flex justify-between mt-1">
                        <p class="text-xs text-gray-500">
                          <span v-if="item.type === 'product'">Qty: {{ item.quantity }}</span>
                          <span v-else>{{ item.date }}</span>
                        </p>
                        <p class="text-sm font-medium text-gray-900">
                          ₱{{ (item.price * (item.quantity || 1)).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Order Actions -->
            <div class="mt-6 pt-6 border-t border-gray-200">
              <div class="flex flex-col md:flex-row md:items-center justify-between">
                <!-- Order Info -->
                <div class="mb-4 md:mb-0">
                  <div class="flex items-center space-x-6 text-sm">
                    <div>
                      <span class="text-gray-500">Payment:</span>
                      <span class="ml-1 font-medium text-gray-900">{{ order.payment.method.toUpperCase() }}</span>
                    </div>
                    <div>
                      <span class="text-gray-500">Delivery:</span>
                      <span class="ml-1 font-medium text-gray-900">{{ order.delivery.date }}</span>
                    </div>
                  </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3">
                  <button
                    @click="viewOrderDetails(order.id)"
                    class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium flex items-center"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Details
                  </button>
                  
                  <button
                    v-if="order.status === 'pending' || order.status === 'processing'"
                    @click="cancelOrder(order.id)"
                    class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors font-medium flex items-center"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel Order
                  </button>
                  
                  <button
                    v-if="order.status === 'completed'"
                    @click="reorderItems(order.id)"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Reorder
                  </button>
                  
                  <button
                    v-if="order.status === 'shipped'"
                    @click="trackOrder(order.id)"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium flex items-center"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Track Order
                  </button>
                </div>
              </div>
              
              <!-- Progress Bar for Processing Orders -->
              <div v-if="order.status === 'processing' || order.status === 'shipped'" class="mt-6">
                <div class="mb-2 flex justify-between text-sm">
                  <span class="font-medium text-gray-700">Order Progress</span>
                  <span class="text-gray-600">{{ getProgressText(order.status) }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    :class="[
                      'h-2 rounded-full transition-all duration-500',
                      order.status === 'processing' ? 'bg-blue-600 w-1/2' : 'bg-green-600 w-3/4'
                    ]"
                  ></div>
                </div>
                <div class="flex justify-between mt-2 text-xs text-gray-500">
                  <span>Ordered</span>
                  <span>Processing</span>
                  <span>Shipped</span>
                  <span>Delivered</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Load More -->
      <div v-if="filteredOrders.length > 0" class="mt-8 text-center">
        <button
          @click="loadMoreOrders"
          v-if="displayedOrders < filteredOrders.length"
          class="px-6 py-3 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium"
        >
          Load More Orders
        </button>
        <p v-else class="text-gray-500">No more orders to load</p>
      </div>
    </div>

    <!-- Order Details Modal -->
    <div v-if="selectedOrder" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <!-- Modal Header -->
          <div class="flex justify-between items-start mb-6">
            <div>
              <h3 class="text-2xl font-bold text-gray-900">Order #{{ selectedOrder.id }}</h3>
              <div class="flex items-center mt-2">
                <span :class="[
                  'px-3 py-1 rounded-full text-sm font-semibold mr-3',
                  getStatusClass(selectedOrder.status)
                ]">
                  {{ getStatusText(selectedOrder.status) }}
                </span>
                <span class="text-sm text-gray-500">Placed on {{ formatDate(selectedOrder.date) }}</span>
              </div>
            </div>
            <button @click="selectedOrder = null" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Order Details Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2">
              <!-- Items -->
              <div class="bg-gray-50 rounded-xl p-6 mb-6">
                <h4 class="font-semibold text-gray-900 mb-4">Order Items</h4>
                <div class="space-y-4">
                  <div
                    v-for="item in selectedOrder.items"
                    :key="item.id"
                    class="flex items-center"
                  >
                    <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center mr-4">
                      <div v-if="item.type === 'product'" class="w-10 h-10 rounded-full" :style="{ backgroundColor: item.color }"></div>
                      <svg v-else class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                      </svg>
                    </div>
                    
                    <div class="flex-1">
                      <h4 class="font-medium text-gray-900">{{ item.name }}</h4>
                      <div class="flex justify-between mt-2">
                        <div>
                          <p class="text-sm text-gray-500">
                            <span v-if="item.type === 'product'">Qty: {{ item.quantity }} • Unit: {{ item.unit }}</span>
                            <span v-else>{{ item.date }}</span>
                          </p>
                          <p class="text-sm text-gray-500" v-if="item.type === 'product'">
                            Stock: {{ item.stock }} • Color: <span class="inline-block w-3 h-3 rounded-full align-middle" :style="{ backgroundColor: item.color }"></span>
                          </p>
                        </div>
                        <p class="text-lg font-bold text-gray-900">
                          ₱{{ (item.price * (item.quantity || 1)).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Shipping Info -->
              <div class="bg-gray-50 rounded-xl p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Shipping Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <span class="text-sm text-gray-500">Recipient</span>
                    <p class="font-medium text-gray-900">{{ selectedOrder.shipping.name }}</p>
                  </div>
                  <div>
                    <span class="text-sm text-gray-500">Phone</span>
                    <p class="font-medium text-gray-900">{{ selectedOrder.shipping.phone }}</p>
                  </div>
                  <div class="md:col-span-2">
                    <span class="text-sm text-gray-500">Address</span>
                    <p class="font-medium text-gray-900">{{ selectedOrder.shipping.address }}</p>
                    <p class="text-gray-600">
                      {{ selectedOrder.shipping.city }}, {{ selectedOrder.shipping.barangay }}, {{ selectedOrder.shipping.zipCode }}
                    </p>
                  </div>
                  <div>
                    <span class="text-sm text-gray-500">Delivery Date</span>
                    <p class="font-medium text-gray-900">{{ selectedOrder.delivery.date }}</p>
                  </div>
                  <div>
                    <span class="text-sm text-gray-500">Delivery Time</span>
                    <p class="font-medium text-gray-900">{{ selectedOrder.delivery.time }}</p>
                  </div>
                  <div v-if="selectedOrder.shipping.instructions" class="md:col-span-2">
                    <span class="text-sm text-gray-500">Special Instructions</span>
                    <p class="text-gray-600">{{ selectedOrder.shipping.instructions }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-1">
              <!-- Order Summary -->
              <div class="bg-white border border-gray-200 rounded-xl p-6">
                <h4 class="font-semibold text-gray-900 mb-4">Order Summary</h4>
                <div class="space-y-3">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Items Total</span>
                    <span class="font-medium text-gray-900">₱{{ selectedOrder.items.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0).toLocaleString() }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-gray-600">Delivery Fee</span>
                    <span class="font-medium text-gray-900">₱{{ selectedOrder.delivery.fee.toLocaleString() }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-gray-600">Tax</span>
                    <span class="font-medium text-gray-900">₱{{ selectedOrder.tax.toLocaleString() }}</span>
                  </div>
                  
                  <div v-if="selectedOrder.payment.method === 'cod'" class="flex justify-between">
                    <span class="text-gray-600">COD Fee</span>
                    <span class="font-medium text-gray-900">₱50</span>
                  </div>
                  
                  <div class="pt-3 border-t border-gray-200">
                    <div class="flex justify-between">
                      <span class="text-lg font-semibold text-gray-900">Total</span>
                      <span class="text-2xl font-bold text-gray-900">₱{{ selectedOrder.totalAmount.toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
                
                <!-- Payment Info -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                  <h4 class="font-semibold text-gray-900 mb-3">Payment Information</h4>
                  <div class="space-y-2">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Method</span>
                      <span class="font-medium text-gray-900">{{ selectedOrder.payment.method.toUpperCase() }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Status</span>
                      <span class="font-medium text-green-600">Paid</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Transaction ID</span>
                      <span class="font-medium text-gray-900">{{ selectedOrder.payment.transactionId }}</span>
                    </div>
                  </div>
                </div>
                
                <!-- Download Invoice -->
                <button
                  @click="downloadInvoice"
                  class="w-full mt-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold flex items-center justify-center space-x-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <span>Download Invoice</span>
                </button>
              </div>
              
              <!-- Need Help -->
              <div class="mt-4 bg-blue-50 rounded-xl p-4">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                  </svg>
                  <div>
                    <h4 class="font-medium text-gray-900">Need help with this order?</h4>
                    <p class="text-sm text-gray-600 mt-1">Contact support at <span class="font-medium">support@cavitegopaint.com</span></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Mock Orders Data
const orders = ref([
  {
    id: 'ORD001234',
    date: '2024-12-01T10:30:00Z',
    status: 'completed',
    items: [
      {
        id: 1,
        type: 'product',
        name: 'Premium Interior White',
        price: 1250,
        quantity: 2,
        unit: 'gallon',
        stock: 45,
        color: '#ffffff'
      },
      {
        id: 2,
        type: 'product',
        name: 'WeatherGuard Exterior',
        price: 1850,
        quantity: 1,
        unit: 'gallon',
        stock: 8,
        color: '#4a90e2'
      }
    ],
    shipping: {
      name: 'Juan Dela Cruz',
      phone: '+63 912 345 6789',
      address: '123 Main Street',
      city: 'Dasmarinas',
      barangay: 'San Miguel',
      zipCode: '4114',
      instructions: 'Leave at guard house if not home'
    },
    payment: {
      method: 'cod',
      transactionId: 'TXN789012'
    },
    delivery: {
      date: 'Dec 3, 2024',
      time: '2:00 PM - 4:00 PM',
      fee: 250
    },
    tax: 372,
    totalAmount: 3972,
    expanded: false
  },
  {
    id: 'ORD001235',
    date: '2024-12-05T14:45:00Z',
    status: 'processing',
    items: [
      {
        id: 3,
        type: 'service',
        name: 'Interior Painting Service',
        price: 5000,
        date: 'Dec 15, 2024',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
      },
      {
        id: 4,
        type: 'service',
        name: 'Color Consultation',
        price: 2500,
        date: 'Dec 10, 2024',
        icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
      }
    ],
    shipping: {
      name: 'Maria Santos',
      phone: '+63 917 890 1234',
      address: '456 Oak Avenue',
      city: 'Imus',
      barangay: 'Bucandala',
      zipCode: '4103',
      instructions: ''
    },
    payment: {
      method: 'gcash',
      transactionId: 'TXN789013'
    },
    delivery: {
      date: 'Not applicable',
      time: '',
      fee: 0
    },
    tax: 900,
    totalAmount: 8400,
    expanded: false
  },
  {
    id: 'ORD001236',
    date: '2024-12-07T09:15:00Z',
    status: 'pending',
    items: [
      {
        id: 5,
        type: 'product',
        name: 'Eco-Friendly Green',
        price: 1650,
        quantity: 3,
        unit: 'gallon',
        stock: 15,
        color: '#50c878'
      }
    ],
    shipping: {
      name: 'Carlos Reyes',
      phone: '+63 918 765 4321',
      address: '789 Pine Street',
      city: 'Bacoor',
      barangay: 'Molino',
      zipCode: '4102',
      instructions: 'Call before delivery'
    },
    payment: {
      method: 'bank',
      transactionId: 'TXN789014'
    },
    delivery: {
      date: 'Dec 9, 2024',
      time: '10:00 AM - 12:00 PM',
      fee: 0
    },
    tax: 594,
    totalAmount: 5544,
    expanded: false
  },
  {
    id: 'ORD001237',
    date: '2024-12-08T16:20:00Z',
    status: 'shipped',
    items: [
      {
        id: 6,
        type: 'product',
        name: 'Premium Red Accent',
        price: 1450,
        quantity: 1,
        unit: 'gallon',
        stock: 8,
        color: '#ff6b6b'
      },
      {
        id: 7,
        type: 'product',
        name: 'Clear Coat Finish',
        price: 1750,
        quantity: 2,
        unit: 'gallon',
        stock: 18,
        color: '#f5f5dc'
      }
    ],
    shipping: {
      name: 'Ana Lopez',
      phone: '+63 919 876 5432',
      address: '321 Elm Street',
      city: 'General Trias',
      barangay: 'Javalera',
      zipCode: '4107',
      instructions: ''
    },
    payment: {
      method: 'card',
      transactionId: 'TXN789015'
    },
    delivery: {
      date: 'Dec 10, 2024',
      time: '3:00 PM - 5:00 PM',
      fee: 250
    },
    tax: 783,
    totalAmount: 7233,
    expanded: false
  },
  {
    id: 'ORD001238',
    date: '2024-12-10T11:10:00Z',
    status: 'cancelled',
    items: [
      {
        id: 8,
        type: 'product',
        name: 'Metal Primer',
        price: 950,
        quantity: 1,
        unit: 'gallon',
        stock: 50,
        color: '#8b7355'
      }
    ],
    shipping: {
      name: 'Pedro Cruz',
      phone: '+63 920 123 4567',
      address: '654 Cedar Street',
      city: 'Tanza',
      barangay: 'Amaya',
      zipCode: '4108',
      instructions: ''
    },
    payment: {
      method: 'cod',
      transactionId: 'TXN789016'
    },
    delivery: {
      date: 'Cancelled',
      time: '',
      fee: 250
    },
    tax: 144,
    totalAmount: 1344,
    expanded: false
  }
])

// State
const statusFilter = ref('all')
const displayedOrders = ref(3)
const selectedOrder = ref(null)

// Computed
const filteredOrders = computed(() => {
  let filtered = orders.value
  
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(order => order.status === statusFilter.value)
  }
  
  return filtered.slice(0, displayedOrders.value)
})

const pendingCount = computed(() => orders.value.filter(o => o.status === 'pending').length)
const processingCount = computed(() => orders.value.filter(o => o.status === 'processing').length)
const completedCount = computed(() => orders.value.filter(o => o.status === 'completed').length)

// Methods
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    pending: 'Pending',
    processing: 'Processing',
    shipped: 'Shipped',
    completed: 'Completed',
    cancelled: 'Cancelled'
  }
  return texts[status] || status
}

const getProgressText = (status) => {
  const texts = {
    processing: 'Preparing your order',
    shipped: 'On the way to you'
  }
  return texts[status] || ''
}

const toggleOrderDetails = (orderId) => {
  const order = orders.value.find(o => o.id === orderId)
  if (order) {
    order.expanded = !order.expanded
  }
}

const viewOrderDetails = (orderId) => {
  selectedOrder.value = orders.value.find(o => o.id === orderId)
}

const cancelOrder = (orderId) => {
  if (confirm('Are you sure you want to cancel this order?')) {
    const order = orders.value.find(o => o.id === orderId)
    if (order) {
      order.status = 'cancelled'
    }
  }
}

const reorderItems = (orderId) => {
  const order = orders.value.find(o => o.id === orderId)
  if (order) {
    console.log('Reordering items:', order.items)
    alert('Items have been added to your cart!')
    // In real app, this would add items to cart
  }
}

const trackOrder = (orderId) => {
  alert(`Tracking order #${orderId}\nThis would open tracking page in real app.`)
}

const refreshOrders = () => {
  // Simulate refreshing orders
  console.log('Refreshing orders...')
  // In real app, this would fetch from API
}

const loadMoreOrders = () => {
  displayedOrders.value += 3
}

const downloadInvoice = () => {
  alert('Invoice download would start here.\nIn real app, this would generate and download PDF.')
}

// Load last order from localStorage
onMounted(() => {
  const lastOrder = localStorage.getItem('lastOrder')
  if (lastOrder) {
    const parsedOrder = JSON.parse(lastOrder)
    // Add the new order to the beginning
    orders.value.unshift({
      id: parsedOrder.orderId,
      date: parsedOrder.date,
      status: 'pending',
      items: parsedOrder.items,
      shipping: parsedOrder.shipping,
      payment: parsedOrder.payment,
      delivery: {
        date: 'Dec 12, 2024',
        time: '1:00 PM - 3:00 PM',
        fee: parsedOrder.deliveryFee || 0
      },
      tax: Math.round(parsedOrder.totalAmount * 0.12),
      totalAmount: parsedOrder.totalAmount,
      expanded: false
    })
    
    // Clear localStorage
    localStorage.removeItem('lastOrder')
  }
})
</script>

<style scoped>
/* Custom scrollbar for modals */
.max-h-\[90vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[90vh\]::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

/* Custom select dropdown arrow */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

/* Smooth transitions */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>