<template>
  <div class="min-h-screen bg-gradient-to-b">
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My Orders</h1>
            <p class="text-gray-600 mt-2">Track your purchases and service requests</p>
          </div>
          
          <div class="flex items-center space-x-4 w-full md:w-auto">
            <div class="flex-1 md:w-[180px]">
              <Select v-model="statusFilter">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Orders</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="processing">Processing</SelectItem>
                  <SelectItem value="shipped">Shipped</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <Button
              variant="outline"
              size="icon"
              @click="refreshOrders"
              class="text-gray-600 hover:text-blue-600 hover:bg-gray-100 shrink-0"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <Card class="bg-white rounded-xl shadow-lg border-gray-200">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Orders</p>
              <p class="text-2xl font-bold text-gray-900">{{ orders.length }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white rounded-xl shadow-lg border-gray-200">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Pending</p>
              <p class="text-2xl font-bold text-yellow-600">{{ pendingCount }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center shrink-0">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white rounded-xl shadow-lg border-gray-200">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Processing</p>
              <p class="text-2xl font-bold text-blue-600">{{ processingCount }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center shrink-0">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white rounded-xl shadow-lg border-gray-200">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Completed</p>
              <p class="text-2xl font-bold text-green-600">{{ completedCount }}</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center shrink-0">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="space-y-6">
        <Card v-if="filteredOrders.length === 0" class="bg-white rounded-2xl shadow-lg border-gray-200 p-8 md:p-12 text-center">
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
          <router-link to="/ecommerce/shop">
            <Button class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
              </svg>
              Start Shopping
            </Button>
          </router-link>
        </Card>

        <Card v-for="order in filteredOrders" :key="order.id" class="bg-white rounded-2xl shadow-lg border-gray-200 overflow-hidden">
          <div class="p-4 md:p-6 border-b border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                <h3 class="text-lg font-bold text-gray-900">Order #{{ order.id }}</h3>
                <div class="flex items-center justify-between sm:justify-start gap-3">
                  <Badge :class="[getStatusClass(order.status), 'px-3 py-1 rounded-full text-sm font-semibold border-0']">
                    {{ getStatusText(order.status) }}
                  </Badge>
                  <p class="text-sm text-gray-500 sm:hidden">
                    {{ formatDate(order.date) }}
                  </p>
                </div>
                <p class="text-sm text-gray-500 hidden sm:block">
                  Placed on {{ formatDate(order.date) }}
                </p>
              </div>
              
              <div class="flex justify-between items-center md:block md:text-right border-t md:border-t-0 pt-4 md:pt-0">
                <p class="text-sm text-gray-500 md:hidden">Total Amount</p>
                <div>
                  <div class="text-2xl font-bold text-gray-900">₱{{ order.totalAmount.toLocaleString() }}</div>
                  <p class="text-sm text-gray-500 text-right">{{ order.items.length }} items</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="p-4 md:p-6">
            <div class="space-y-4">
              <div
                v-for="item in order.items.slice(0, 2)"
                :key="item.id"
                class="flex items-center"
              >
                <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center mr-4 shrink-0">
                  <div v-if="item.type === 'product'" class="w-10 h-10 rounded-full" :style="{ backgroundColor: item.color }"></div>
                  <svg v-else class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                  </svg>
                </div>
                
                <div class="flex-1 min-w-0">
                  <h4 class="font-medium text-gray-900 truncate">{{ item.name }}</h4>
                  <div class="flex flex-col sm:flex-row sm:justify-between mt-1">
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
              
              <div v-if="order.items.length > 2" class="pt-4 border-t border-gray-100">
                <Button
                  variant="ghost"
                  @click="toggleOrderDetails(order.id)"
                  class="text-blue-600 hover:text-blue-800 text-sm font-medium p-0 h-auto hover:bg-transparent"
                >
                  <span>{{ order.expanded ? 'Show less' : `+${order.items.length - 2} more items` }}</span>
                  <svg :class="[
                    'w-4 h-4 ml-1 transition-transform',
                    order.expanded ? 'rotate-180' : ''
                  ]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                  </svg>
                </Button>
                
                <div v-if="order.expanded" class="mt-4 space-y-4">
                  <div
                    v-for="item in order.items.slice(2)"
                    :key="item.id"
                    class="flex items-center"
                  >
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-gray-50 to-slate-50 flex items-center justify-center mr-3 shrink-0">
                      <div v-if="item.type === 'product'" class="w-8 h-8 rounded-full" :style="{ backgroundColor: item.color }"></div>
                      <svg v-else class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                      </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</h4>
                      <div class="flex flex-col sm:flex-row sm:justify-between mt-1">
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
            
            <div class="mt-6 pt-6 border-t border-gray-200">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-6 text-sm">
                  <div>
                    <span class="text-gray-500">Payment:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ order.payment.method.toUpperCase() }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500">Delivery:</span>
                    <span class="ml-1 font-medium text-gray-900">{{ order.delivery.date }}</span>
                  </div>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-3">
                  <Button
                    variant="outline"
                    @click="viewOrderDetails(order.id)"
                    class="border-blue-600 text-blue-600 hover:bg-blue-50 w-full sm:w-auto"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Details
                  </Button>
                  
                  <Button
                    v-if="order.status === 'pending' || order.status === 'processing'"
                    variant="outline"
                    @click="cancelOrder(order.id)"
                    class="border-red-600 text-red-600 hover:bg-red-50 w-full sm:w-auto"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel Order
                  </Button>
                  
                  <Button
                    v-if="order.status === 'completed'"
                    @click="reorderItems(order.id)"
                    class="bg-blue-600 hover:bg-blue-700 text-white w-full sm:w-auto"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Reorder
                  </Button>
                  
                  <Button
                    v-if="order.status === 'shipped'"
                    @click="trackOrder(order.id)"
                    class="bg-green-600 hover:bg-green-700 text-white w-full sm:w-auto"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Track Order
                  </Button>
                </div>
              </div>
              
              <div v-if="order.status === 'processing' || order.status === 'shipped'" class="mt-6">
                <div class="mb-2 flex justify-between text-sm">
                  <span class="font-medium text-gray-700">Order Progress</span>
                  <span class="text-gray-600">{{ getProgressText(order.status) }}</span>
                </div>
                <Progress 
                  :model-value="order.status === 'processing' ? 50 : 75" 
                  :class="['h-2', order.status === 'processing' ? '[&>div]:bg-blue-600' : '[&>div]:bg-green-600']" 
                />
                
                <div class="flex justify-between mt-2 text-xs text-gray-500">
                  <span>Ordered</span>
                  <span>Processing</span>
                  <span>Shipped</span>
                  <span>Delivered</span>
                </div>
              </div>
            </div>
          </div>
        </Card>
      </div>

      <div v-if="filteredOrders.length > 0" class="mt-8 text-center">
        <Button
          @click="loadMoreOrders"
          v-if="displayedOrders < filteredOrders.length"
          variant="outline"
          class="border-blue-600 text-blue-600 hover:bg-blue-50 w-full sm:w-auto"
        >
          Load More Orders
        </Button>
        <p v-else class="text-gray-500">No more orders to load</p>
      </div>
    </div>

    <Dialog :open="!!selectedOrder" @update:open="(val) => !val && (selectedOrder = null)">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto w-[95vw] p-0 gap-0 rounded-xl">
        <div class="p-4 md:p-6">
          <DialogHeader class="flex flex-col sm:flex-row justify-between items-start mb-6 space-y-2 sm:space-y-0">
            <div>
              <DialogTitle class="text-xl md:text-2xl font-bold text-gray-900">Order #{{ selectedOrder?.id }}</DialogTitle>
              <DialogDescription class="flex flex-wrap items-center mt-2 gap-2">
                <Badge :class="[getStatusClass(selectedOrder?.status), 'px-3 py-1 rounded-full text-sm font-semibold border-0']">
                  {{ getStatusText(selectedOrder?.status) }}
                </Badge>
                <span class="text-sm text-gray-500">Placed on {{ formatDate(selectedOrder?.date) }}</span>
              </DialogDescription>
            </div>
          </DialogHeader>

          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
              <Card class="bg-gray-50 border-0 mb-6">
                <CardContent class="p-4 md:p-6">
                  <h4 class="font-semibold text-gray-900 mb-4">Order Items</h4>
                  <div class="space-y-4">
                    <div
                      v-for="item in selectedOrder?.items"
                      :key="item.id"
                      class="flex items-start md:items-center"
                    >
                      <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center mr-4 shrink-0">
                        <div v-if="item.type === 'product'" class="w-10 h-10 rounded-full" :style="{ backgroundColor: item.color }"></div>
                        <svg v-else class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                        </svg>
                      </div>
                      
                      <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-gray-900 truncate">{{ item.name }}</h4>
                        <div class="flex flex-col sm:flex-row sm:justify-between mt-2">
                          <div>
                            <p class="text-sm text-gray-500">
                              <span v-if="item.type === 'product'">Qty: {{ item.quantity }} • Unit: {{ item.unit }}</span>
                              <span v-else>{{ item.date }}</span>
                            </p>
                            <p class="text-sm text-gray-500" v-if="item.type === 'product'">
                              Stock: {{ item.stock }} • Color: <span class="inline-block w-3 h-3 rounded-full align-middle" :style="{ backgroundColor: item.color }"></span>
                            </p>
                          </div>
                          <p class="text-lg font-bold text-gray-900 mt-1 sm:mt-0">
                            ₱{{ (item.price * (item.quantity || 1)).toLocaleString() }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card class="bg-gray-50 border-0">
                <CardContent class="p-4 md:p-6">
                  <h4 class="font-semibold text-gray-900 mb-4">Shipping Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <span class="text-sm text-gray-500">Recipient</span>
                      <p class="font-medium text-gray-900">{{ selectedOrder?.shipping.name }}</p>
                    </div>
                    <div>
                      <span class="text-sm text-gray-500">Phone</span>
                      <p class="font-medium text-gray-900">{{ selectedOrder?.shipping.phone }}</p>
                    </div>
                    <div class="md:col-span-2">
                      <span class="text-sm text-gray-500">Address</span>
                      <p class="font-medium text-gray-900 break-words">{{ selectedOrder?.shipping.address }}</p>
                      <p class="text-gray-600">
                        {{ selectedOrder?.shipping.city }}, {{ selectedOrder?.shipping.barangay }}, {{ selectedOrder?.shipping.zipCode }}
                      </p>
                    </div>
                    <div>
                      <span class="text-sm text-gray-500">Delivery Date</span>
                      <p class="font-medium text-gray-900">{{ selectedOrder?.delivery.date }}</p>
                    </div>
                    <div>
                      <span class="text-sm text-gray-500">Delivery Time</span>
                      <p class="font-medium text-gray-900">{{ selectedOrder?.delivery.time }}</p>
                    </div>
                    <div v-if="selectedOrder?.shipping.instructions" class="md:col-span-2">
                      <span class="text-sm text-gray-500">Special Instructions</span>
                      <p class="text-gray-600">{{ selectedOrder?.shipping.instructions }}</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            <div class="lg:col-span-1">
              <Card class="bg-white border-gray-200">
                <CardContent class="p-4 md:p-6">
                  <h4 class="font-semibold text-gray-900 mb-4">Order Summary</h4>
                  <div class="space-y-3">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Items Total</span>
                      <span class="font-medium text-gray-900">₱{{ selectedOrder?.items.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0).toLocaleString() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                      <span class="text-gray-600">Delivery Fee</span>
                      <span class="font-medium text-gray-900">₱{{ selectedOrder?.delivery.fee.toLocaleString() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                      <span class="text-gray-600">Tax</span>
                      <span class="font-medium text-gray-900">₱{{ selectedOrder?.tax.toLocaleString() }}</span>
                    </div>
                    
                    <div v-if="selectedOrder?.payment.method === 'cod'" class="flex justify-between">
                      <span class="text-gray-600">COD Fee</span>
                      <span class="font-medium text-gray-900">₱50</span>
                    </div>
                    
                    <div class="pt-3 border-t border-gray-200">
                      <div class="flex justify-between">
                        <span class="text-lg font-semibold text-gray-900">Total</span>
                        <span class="text-2xl font-bold text-gray-900">₱{{ selectedOrder?.totalAmount.toLocaleString() }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mt-6 pt-6 border-t border-gray-200">
                    <h4 class="font-semibold text-gray-900 mb-3">Payment Information</h4>
                    <div class="space-y-2">
                      <div class="flex justify-between">
                        <span class="text-gray-600">Method</span>
                        <span class="font-medium text-gray-900">{{ selectedOrder?.payment.method.toUpperCase() }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Status</span>
                        <span class="font-medium text-green-600">Paid</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-600">Transaction ID</span>
                        <span class="font-medium text-gray-900 text-right truncate ml-4">{{ selectedOrder?.payment.transactionId }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <Button
                    @click="downloadInvoice"
                    class="w-full mt-6 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
                  >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Download Invoice</span>
                  </Button>
                </CardContent>
              </Card>
              
              <Card class="mt-4 bg-blue-50 border-0">
                <CardContent class="p-4">
                  <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <div>
                      <h4 class="font-medium text-gray-900">Need help with this order?</h4>
                      <p class="text-sm text-gray-600 mt-1">Contact support at <span class="font-medium">support@cavitegopaint.com</span></p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
// shadcn components
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

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