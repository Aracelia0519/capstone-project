<!-- ECommercePayments.vue -->
<template>
  <div class="ecommerce-payments p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Payment Management</h1>
          <p class="text-gray-300">Manage customer payments securely and efficiently</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <button class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Report
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">₱{{ totalRevenue.toLocaleString() }}</div>
        <div class="text-sm text-gray-300">Total Revenue</div>
      </div>
      <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ completedPayments }}</div>
        <div class="text-sm text-gray-300">Completed</div>
      </div>
      <div class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ pendingPayments }}</div>
        <div class="text-sm text-gray-300">Pending</div>
      </div>
      <div class="bg-gradient-to-br from-red-500/20 to-pink-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ refundedPayments }}</div>
        <div class="text-sm text-gray-300">Refunded</div>
      </div>
    </div>

    <!-- Payment Method Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <div class="lg:col-span-2 bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Payment Method Distribution</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div v-for="method in paymentMethods" :key="method.name" 
               class="bg-gray-800/50 rounded-xl p-4 text-center hover:bg-gray-800 transition-colors">
            <div :class="[
              'w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center',
              method.color
            ]">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="method.name === 'GCash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                <path v-else-if="method.name === 'PayMaya'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                <path v-else-if="method.name === 'COD'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div class="text-white font-medium mb-1">{{ method.name }}</div>
            <div class="text-sm text-gray-400">{{ method.count }} payments</div>
            <div class="text-sm text-emerald-400 mt-1">₱{{ method.amount.toLocaleString() }}</div>
          </div>
        </div>
      </div>
      
      <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Payment Status Overview</h3>
        <div class="space-y-4">
          <div v-for="status in paymentStatus" :key="status.name" class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full mr-3" :class="status.color"></div>
              <span class="text-gray-300">{{ status.name }}</span>
            </div>
            <div class="flex items-center">
              <span class="text-white font-medium mr-2">{{ status.count }}</span>
              <span class="text-gray-400 text-sm">{{ status.percentage }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-2">Order ID</label>
          <input type="text" v-model="searchOrderId" placeholder="Search order ID..." 
                 class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500">
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Payment Method</label>
          <select v-model="selectedMethod" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Methods</option>
            <option value="GCash">GCash</option>
            <option value="PayMaya">PayMaya</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="COD">COD</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Status</label>
          <select v-model="selectedStatus" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Status</option>
            <option value="Completed">Completed</option>
            <option value="Pending">Pending</option>
            <option value="Failed">Failed</option>
            <option value="Refunded">Refunded</option>
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

    <!-- Payments Table -->
    <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-900/80">
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Payment ID</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Order ID</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Client</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Payment Method</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Amount</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Date</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="payment in filteredPayments" :key="payment.id" 
                class="border-t border-gray-800 hover:bg-white/5">
              <td class="py-4 px-6">
                <div class="font-mono text-white font-medium">{{ payment.paymentId }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="font-mono text-gray-300">{{ payment.orderId }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-xs mr-3">
                    {{ payment.client.charAt(0) }}
                  </div>
                  <span class="text-gray-300">{{ payment.client }}</span>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div :class="[
                    'w-8 h-8 rounded-lg mr-3 flex items-center justify-center',
                    methodColors[payment.method]
                  ]">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="payment.method === 'GCash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      <path v-else-if="payment.method === 'PayMaya'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      <path v-else-if="payment.method === 'COD'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                  </div>
                  <span class="text-gray-300">{{ payment.method }}</span>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="text-white font-medium">₱{{ payment.amount.toLocaleString() }}</div>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  statusClasses[payment.status]
                ]">
                  {{ payment.status }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="text-gray-300">{{ payment.date }}</div>
                <div class="text-sm text-gray-400">{{ payment.time }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex space-x-2">
                  <button @click="viewPayment(payment)" 
                         class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button v-if="payment.status === 'Pending'" @click="confirmPayment(payment)" 
                         class="p-2 text-green-400 hover:text-green-300 hover:bg-green-500/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  <button v-if="payment.status === 'Completed'" @click="processRefund(payment)" 
                         class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="p-4 border-t border-gray-800 flex items-center justify-between">
        <div class="text-sm text-gray-400">
          Showing {{ filteredPayments.length }} of {{ payments.length }} payments
        </div>
        <div class="flex space-x-2">
          <button class="px-3 py-1 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700">
            Previous
          </button>
          <button class="px-3 py-1 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg">
            1
          </button>
          <button class="px-3 py-1 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700">
            2
          </button>
          <button class="px-3 py-1 bg-gray-800 text-gray-300 rounded-lg hover:bg-gray-700">
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Payment Details Modal -->
    <div v-if="selectedPayment" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-xl font-bold text-white">Payment Details</h3>
              <p class="text-gray-400">{{ selectedPayment.paymentId }}</p>
            </div>
            <button @click="selectedPayment = null" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div class="space-y-6">
            <!-- Payment Summary -->
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Payment Information</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-xs text-gray-400 mb-1">Order ID</div>
                  <div class="text-white font-medium">{{ selectedPayment.orderId }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Client</div>
                  <div class="text-white">{{ selectedPayment.client }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Payment Method</div>
                  <div class="flex items-center">
                    <div :class="[
                      'w-6 h-6 rounded mr-2 flex items-center justify-center',
                      methodColors[selectedPayment.method]
                    ]">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="selectedPayment.method === 'GCash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                      </svg>
                    </div>
                    <span class="text-white">{{ selectedPayment.method }}</span>
                  </div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Status</div>
                  <span :class="[
                    'px-2 py-1 rounded-full text-xs font-medium',
                    statusClasses[selectedPayment.status]
                  ]">
                    {{ selectedPayment.status }}
                  </span>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Date & Time</div>
                  <div class="text-white">{{ selectedPayment.date }} {{ selectedPayment.time }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Amount</div>
                  <div class="text-xl font-bold text-white">₱{{ selectedPayment.amount.toLocaleString() }}</div>
                </div>
              </div>
            </div>
            
            <!-- Receipt Information -->
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Receipt Information</h4>
              <div v-if="selectedPayment.receipt" class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-gray-400">Reference Number:</span>
                  <span class="text-white font-mono">{{ selectedPayment.referenceNumber }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-400">Transaction ID:</span>
                  <span class="text-white font-mono">{{ selectedPayment.transactionId }}</span>
                </div>
                <div class="border border-dashed border-gray-700 rounded-lg p-4 text-center hover:border-gray-600 transition-colors cursor-pointer">
                  <svg class="w-12 h-12 text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="text-gray-400">Payment Receipt Available</p>
                  <button class="mt-2 text-sm text-indigo-400 hover:text-indigo-300">
                    Download Receipt
                  </button>
                </div>
              </div>
              <div v-else class="text-center py-6">
                <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-400">No receipt uploaded</p>
              </div>
            </div>
            
            <!-- Refund Information -->
            <div v-if="selectedPayment.status === 'Refunded'" class="bg-red-500/10 rounded-xl p-4 border border-red-500/20">
              <h4 class="text-sm text-red-300 mb-3">Refund Information</h4>
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-400">Refund Date:</span>
                  <span class="text-white">{{ selectedPayment.refundDate }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Refund Amount:</span>
                  <span class="text-white">₱{{ selectedPayment.amount.toLocaleString() }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Refund Method:</span>
                  <span class="text-white">{{ selectedPayment.refundMethod }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-400">Reason:</span>
                  <span class="text-white">{{ selectedPayment.refundReason }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-800">
            <button @click="selectedPayment = null" 
                   class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
              Close
            </button>
            <button v-if="selectedPayment.status === 'Pending'" @click="confirmPayment(selectedPayment)" 
                   class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:opacity-90 transition-opacity">
              Confirm Payment
            </button>
            <button v-if="selectedPayment.status === 'Completed'" @click="processRefund(selectedPayment)" 
                   class="px-6 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg hover:opacity-90 transition-opacity">
              Process Refund
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchOrderId = ref('')
const selectedMethod = ref('')
const selectedStatus = ref('')
const dateRange = ref('')
const selectedPayment = ref(null)

const statusClasses = {
  'Completed': 'bg-green-500/20 text-green-300',
  'Pending': 'bg-amber-500/20 text-amber-300',
  'Failed': 'bg-red-500/20 text-red-300',
  'Refunded': 'bg-red-500/20 text-red-300'
}

const methodColors = {
  'GCash': 'bg-gradient-to-r from-emerald-500 to-teal-500',
  'PayMaya': 'bg-gradient-to-r from-blue-500 to-cyan-500',
  'Bank Transfer': 'bg-gradient-to-r from-indigo-500 to-purple-500',
  'COD': 'bg-gradient-to-r from-gray-600 to-gray-700'
}

const payments = ref([
  { 
    id: 1, 
    paymentId: 'PAY-2024-001', 
    orderId: 'ORD-2024-001', 
    client: 'Juan Dela Cruz',
    method: 'GCash', 
    amount: 12450, 
    status: 'Completed',
    date: '2024-03-15', 
    time: '14:35',
    referenceNumber: 'GCASH123456789',
    transactionId: 'TX1234567890',
    receipt: true
  },
  { 
    id: 2, 
    paymentId: 'PAY-2024-002', 
    orderId: 'ORD-2024-002', 
    client: 'Maria Santos',
    method: 'PayMaya', 
    amount: 8900, 
    status: 'Completed',
    date: '2024-03-14', 
    time: '10:20',
    referenceNumber: 'PMYA987654321',
    transactionId: 'TX9876543210',
    receipt: true
  },
  { 
    id: 3, 
    paymentId: 'PAY-2024-003', 
    orderId: 'ORD-2024-003', 
    client: 'Pedro Reyes',
    method: 'Bank Transfer', 
    amount: 15600, 
    status: 'Pending',
    date: '2024-03-13', 
    time: '16:50',
    referenceNumber: '',
    transactionId: '',
    receipt: false
  },
  { 
    id: 4, 
    paymentId: 'PAY-2024-004', 
    orderId: 'ORD-2024-004', 
    client: 'Ana Lopez',
    method: 'COD', 
    amount: 6750, 
    status: 'Pending',
    date: '2024-03-12', 
    time: '09:25',
    referenceNumber: '',
    transactionId: '',
    receipt: false
  },
  { 
    id: 5, 
    paymentId: 'PAY-2024-005', 
    orderId: 'ORD-2024-005', 
    client: 'Luis Garcia',
    method: 'GCash', 
    amount: 11200, 
    status: 'Refunded',
    date: '2024-03-11', 
    time: '11:15',
    referenceNumber: 'GCASH555666777',
    transactionId: 'TX5556667770',
    receipt: true,
    refundDate: '2024-03-12',
    refundMethod: 'GCash',
    refundReason: 'Product damaged upon delivery'
  },
  { 
    id: 6, 
    paymentId: 'PAY-2024-006', 
    orderId: 'ORD-2024-006', 
    client: 'Carla Lim',
    method: 'PayMaya', 
    amount: 8900, 
    status: 'Failed',
    date: '2024-03-10', 
    time: '13:40',
    referenceNumber: '',
    transactionId: '',
    receipt: false
  }
])

const paymentMethods = computed(() => {
  const methods = {}
  payments.value.forEach(payment => {
    if (!methods[payment.method]) {
      methods[payment.method] = { name: payment.method, count: 0, amount: 0 }
    }
    methods[payment.method].count++
    methods[payment.method].amount += payment.amount
  })
  
  return Object.values(methods).map(method => ({
    ...method,
    color: methodColors[method.name] || 'bg-gradient-to-r from-gray-500 to-gray-600'
  }))
})

const paymentStatus = computed(() => {
  const statuses = {}
  payments.value.forEach(payment => {
    if (!statuses[payment.status]) {
      statuses[payment.status] = { name: payment.status, count: 0 }
    }
    statuses[payment.status].count++
  })
  
  const total = payments.value.length
  return Object.values(statuses).map(status => ({
    ...status,
    percentage: Math.round((status.count / total) * 100),
    color: status.name === 'Completed' ? 'bg-green-500' :
           status.name === 'Pending' ? 'bg-amber-500' :
           status.name === 'Refunded' ? 'bg-red-500' : 'bg-gray-500'
  }))
})

const totalRevenue = computed(() => {
  return payments.value
    .filter(p => p.status === 'Completed')
    .reduce((sum, p) => sum + p.amount, 0)
})

const completedPayments = computed(() => {
  return payments.value.filter(p => p.status === 'Completed').length
})

const pendingPayments = computed(() => {
  return payments.value.filter(p => p.status === 'Pending').length
})

const refundedPayments = computed(() => {
  return payments.value.filter(p => p.status === 'Refunded').length
})

const filteredPayments = computed(() => {
  return payments.value.filter(payment => {
    const matchesOrderId = searchOrderId.value === '' || 
      payment.orderId.toLowerCase().includes(searchOrderId.value.toLowerCase()) ||
      payment.paymentId.toLowerCase().includes(searchOrderId.value.toLowerCase())
    
    const matchesMethod = selectedMethod.value === '' || 
      payment.method === selectedMethod.value
    
    const matchesStatus = selectedStatus.value === '' || 
      payment.status === selectedStatus.value
    
    return matchesOrderId && matchesMethod && matchesStatus
  })
})

const resetFilters = () => {
  searchOrderId.value = ''
  selectedMethod.value = ''
  selectedStatus.value = ''
  dateRange.value = ''
}

const viewPayment = (payment) => {
  selectedPayment.value = payment
}

const confirmPayment = (payment) => {
  if (confirm(`Confirm payment of ₱${payment.amount.toLocaleString()} from ${payment.client}?`)) {
    payment.status = 'Completed'
    if (selectedPayment.value) {
      selectedPayment.value.status = 'Completed'
    }
  }
}

const processRefund = (payment) => {
  const reason = prompt('Enter refund reason:')
  if (reason) {
    payment.status = 'Refunded'
    payment.refundDate = new Date().toISOString().split('T')[0]
    payment.refundMethod = payment.method
    payment.refundReason = reason
    if (selectedPayment.value) {
      selectedPayment.value.status = 'Refunded'
      selectedPayment.value.refundDate = payment.refundDate
      selectedPayment.value.refundMethod = payment.refundMethod
      selectedPayment.value.refundReason = payment.refundReason
    }
  }
}
</script>

<style scoped>
.ecommerce-payments {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-payments {
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>