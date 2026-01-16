<!-- ECommerceReturns.vue -->
<template>
  <div class="ecommerce-returns p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Returns & Refunds</h1>
          <p class="text-gray-300">Manage product returns and refund requests</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <button class="px-4 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Report
          </button>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-gradient-to-br from-red-500/20 to-pink-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ returnRequests.length }}</div>
        <div class="text-sm text-gray-300">Total Returns</div>
      </div>
      <div class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ pendingReturns }}</div>
        <div class="text-sm text-gray-300">Pending Review</div>
      </div>
      <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ approvedReturns }}</div>
        <div class="text-sm text-gray-300">Approved</div>
      </div>
      <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">₱{{ totalRefunded.toLocaleString() }}</div>
        <div class="text-sm text-gray-300">Total Refunded</div>
      </div>
    </div>

    <!-- Return Reasons Chart -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <div class="lg:col-span-2 bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Return Reasons</h3>
        <div class="space-y-4">
          <div v-for="reason in returnReasons" :key="reason.name" class="space-y-2">
            <div class="flex items-center justify-between">
              <span class="text-gray-300">{{ reason.name }}</span>
              <span class="text-white font-medium">{{ reason.count }}</span>
            </div>
            <div class="w-full bg-gray-700 h-2 rounded-full overflow-hidden">
              <div class="h-full rounded-full" :class="reason.color" :style="{ width: reason.percentage + '%' }"></div>
            </div>
            <div class="text-xs text-gray-400">{{ reason.percentage }}% of total returns</div>
          </div>
        </div>
      </div>
      
      <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Quick Actions</h3>
        <div class="space-y-4">
          <button @click="showReturnForm = true" 
                 class="w-full p-4 bg-gradient-to-r from-red-500/20 to-pink-500/20 border border-red-500/30 rounded-xl hover:from-red-500/30 hover:to-pink-500/30 transition-all flex items-center justify-center">
            <svg class="w-6 h-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span class="text-white">Process New Return</span>
          </button>
          <button @click="viewReturnHistory" 
                 class="w-full p-4 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 border border-blue-500/30 rounded-xl hover:from-blue-500/30 hover:to-cyan-500/30 transition-all flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="text-white">View Return History</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm text-gray-300 mb-2">Search</label>
          <input type="text" v-model="searchQuery" placeholder="Search by order ID or client..." 
                 class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500">
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Status</label>
          <select v-model="selectedStatus" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Status</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Rejected">Rejected</option>
            <option value="Refunded">Refunded</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Reason</label>
          <select v-model="selectedReason" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Reasons</option>
            <option value="Damaged Item">Damaged Item</option>
            <option value="Wrong Item">Wrong Item</option>
            <option value="Not as Described">Not as Described</option>
            <option value="Changed Mind">Changed Mind</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-white transition-colors">
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Returns Table -->
    <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-900/80">
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Return ID</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Order Details</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Reason</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Refund Amount</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="returnReq in filteredReturns" :key="returnReq.id" 
                class="border-t border-gray-800 hover:bg-white/5">
              <td class="py-4 px-6">
                <div class="font-mono text-white font-medium">{{ returnReq.returnId }}</div>
                <div class="text-sm text-gray-400">{{ returnReq.date }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-white">{{ returnReq.product }}</div>
                    <div class="text-sm text-gray-400">Order: {{ returnReq.orderId }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="text-gray-300">{{ returnReq.reason }}</div>
                <div v-if="returnReq.details" class="text-sm text-gray-400">{{ returnReq.details }}</div>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  statusClasses[returnReq.status]
                ]">
                  {{ returnReq.status }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div v-if="returnReq.status !== 'Rejected'" class="text-white font-medium">
                  ₱{{ returnReq.refundAmount.toLocaleString() }}
                </div>
                <div v-else class="text-gray-400">-</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex space-x-2">
                  <button @click="reviewReturn(returnReq)" 
                         class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-lg hover:bg-blue-500/30 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Review
                  </button>
                  <button v-if="returnReq.status === 'Approved'" @click="processRefund(returnReq)" 
                         class="px-3 py-1 bg-green-500/20 text-green-300 rounded-lg hover:bg-green-500/30 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Refund
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Process Return Modal -->
    <div v-if="showReturnForm" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Process Return Request</h3>
            <button @click="showReturnForm = false" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <form @submit.prevent="submitReturn" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm text-gray-300 mb-2">Order ID *</label>
                <select v-model="returnForm.orderId" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="">Select Order</option>
                  <option value="ORD-2024-001">ORD-2024-001 - Juan Dela Cruz</option>
                  <option value="ORD-2024-002">ORD-2024-002 - Maria Santos</option>
                  <option value="ORD-2024-003">ORD-2024-003 - Pedro Reyes</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Return Reason *</label>
                <select v-model="returnForm.reason" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="Damaged Item">Damaged Item</option>
                  <option value="Wrong Item">Wrong Item</option>
                  <option value="Not as Described">Not as Described</option>
                  <option value="Changed Mind">Changed Mind</option>
                  <option value="Defective">Defective Product</option>
                </select>
              </div>
              
              <div class="md:col-span-2">
                <label class="block text-sm text-gray-300 mb-2">Return Details *</label>
                <textarea v-model="returnForm.details" rows="3" required 
                         class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"
                         placeholder="Describe the issue..."></textarea>
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Requested Refund Amount *</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">₱</span>
                  <input type="number" v-model="returnForm.refundAmount" required 
                         class="w-full pl-8 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                </div>
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Refund Method</label>
                <select v-model="returnForm.refundMethod" 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="Original Payment">Original Payment Method</option>
                  <option value="GCash">GCash</option>
                  <option value="Bank Transfer">Bank Transfer</option>
                  <option value="Store Credit">Store Credit</option>
                </select>
              </div>
            </div>
            
            <!-- Proof Upload -->
            <div>
              <label class="block text-sm text-gray-300 mb-2">Upload Proof</label>
              <div class="border-2 border-dashed border-gray-700 rounded-lg p-6 text-center hover:border-gray-600 transition-colors cursor-pointer">
                <svg class="w-12 h-12 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-400">Drag & drop images or click to browse</p>
                <p class="text-sm text-gray-500 mt-1">Upload photos of damaged/wrong items</p>
              </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
              <button type="button" @click="showReturnForm = false" 
                     class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
                Cancel
              </button>
              <button type="submit" 
                     class="px-6 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                Submit Return
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Review Return Modal -->
    <div v-if="selectedReturn" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Review Return Request</h3>
            <button @click="selectedReturn = null" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div class="space-y-6">
            <!-- Return Details -->
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Return Details</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-xs text-gray-400 mb-1">Return ID</div>
                  <div class="text-white font-medium">{{ selectedReturn.returnId }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Order ID</div>
                  <div class="text-white">{{ selectedReturn.orderId }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Reason</div>
                  <div class="text-white">{{ selectedReturn.reason }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Requested Amount</div>
                  <div class="text-white">₱{{ selectedReturn.refundAmount.toLocaleString() }}</div>
                </div>
              </div>
              <div class="mt-4">
                <div class="text-xs text-gray-400 mb-1">Description</div>
                <div class="text-gray-300">{{ selectedReturn.details }}</div>
              </div>
            </div>
            
            <!-- Client Information -->
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Client Information</h4>
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-lg mr-4">
                  {{ selectedReturn.client.charAt(0) }}
                </div>
                <div>
                  <div class="text-white font-medium">{{ selectedReturn.client }}</div>
                  <div class="text-sm text-gray-400">{{ selectedReturn.email }}</div>
                </div>
              </div>
            </div>
            
            <!-- Proof Images -->
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Proof Images</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="border border-gray-700 rounded-lg p-4 text-center">
                  <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm text-gray-400">Damaged Product</p>
                </div>
                <div class="border border-gray-700 rounded-lg p-4 text-center">
                  <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm text-gray-400">Package Condition</p>
                </div>
              </div>
            </div>
            
            <!-- Decision -->
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Review Decision</h4>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm text-gray-300 mb-2">Approved Refund Amount</label>
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">₱</span>
                    <input type="number" v-model="reviewForm.approvedAmount" 
                           class="w-full pl-8 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"
                           :placeholder="selectedReturn.refundAmount.toString()">
                  </div>
                </div>
                <div>
                  <label class="block text-sm text-gray-300 mb-2">Decision Notes</label>
                  <textarea v-model="reviewForm.notes" rows="2" 
                           class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div class="flex justify-between mt-6 pt-6 border-t border-gray-800">
            <button @click="rejectReturn(selectedReturn)" 
                   class="px-6 py-2 bg-red-500/20 text-red-300 border border-red-500/30 rounded-lg hover:bg-red-500/30 transition-colors">
              Reject
            </button>
            <div class="flex space-x-3">
              <button @click="selectedReturn = null" 
                     class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
                Cancel
              </button>
              <button @click="approveReturn(selectedReturn)" 
                     class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                Approve
              </button>
            </div>
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
const selectedReason = ref('')
const showReturnForm = ref(false)
const selectedReturn = ref(null)

const statusClasses = {
  'Pending': 'bg-amber-500/20 text-amber-300',
  'Approved': 'bg-green-500/20 text-green-300',
  'Rejected': 'bg-red-500/20 text-red-300',
  'Refunded': 'bg-blue-500/20 text-blue-300'
}

const returnRequests = ref([
  { 
    id: 1, 
    returnId: 'RET-2024-001', 
    orderId: 'ORD-2024-005', 
    product: 'Premium White Paint',
    reason: 'Damaged Item', 
    details: 'Can arrived dented, paint leaking',
    status: 'Approved',
    date: '2024-03-12',
    refundAmount: 2450,
    client: 'Luis Garcia',
    email: 'luis.garcia@email.com'
  },
  { 
    id: 2, 
    returnId: 'RET-2024-002', 
    orderId: 'ORD-2024-004', 
    product: 'Anti-Mold Exterior Paint',
    reason: 'Wrong Item', 
    details: 'Received interior paint instead of exterior',
    status: 'Pending',
    date: '2024-03-14',
    refundAmount: 3850,
    client: 'Ana Lopez',
    email: 'ana.lopez@email.com'
  },
  { 
    id: 3, 
    returnId: 'RET-2024-003', 
    orderId: 'ORD-2024-003', 
    product: 'Gloss Finish Coating',
    reason: 'Not as Described', 
    details: 'Color does not match swatch',
    status: 'Rejected',
    date: '2024-03-10',
    refundAmount: 2980,
    client: 'Pedro Reyes',
    email: 'pedro.reyes@email.com'
  },
  { 
    id: 4, 
    returnId: 'RET-2024-004', 
    orderId: 'ORD-2024-002', 
    product: 'Weatherproof Exterior Paint',
    reason: 'Defective', 
    details: 'Paint separates after mixing',
    status: 'Refunded',
    date: '2024-03-08',
    refundAmount: 3250,
    client: 'Maria Santos',
    email: 'maria.santos@email.com'
  }
])

const returnForm = ref({
  orderId: '',
  reason: 'Damaged Item',
  details: '',
  refundAmount: '',
  refundMethod: 'Original Payment'
})

const reviewForm = ref({
  approvedAmount: '',
  notes: ''
})

const returnReasons = ref([
  { name: 'Damaged Item', count: 8, percentage: 40, color: 'bg-red-500' },
  { name: 'Wrong Item', count: 5, percentage: 25, color: 'bg-amber-500' },
  { name: 'Not as Described', count: 4, percentage: 20, color: 'bg-blue-500' },
  { name: 'Changed Mind', count: 2, percentage: 10, color: 'bg-purple-500' },
  { name: 'Defective', count: 1, percentage: 5, color: 'bg-gray-500' }
])

const pendingReturns = computed(() => {
  return returnRequests.value.filter(r => r.status === 'Pending').length
})

const approvedReturns = computed(() => {
  return returnRequests.value.filter(r => r.status === 'Approved' || r.status === 'Refunded').length
})

const totalRefunded = computed(() => {
  return returnRequests.value
    .filter(r => r.status === 'Refunded')
    .reduce((sum, r) => sum + r.refundAmount, 0)
})

const filteredReturns = computed(() => {
  return returnRequests.value.filter(returnReq => {
    const matchesSearch = searchQuery.value === '' || 
      returnReq.returnId.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      returnReq.orderId.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      returnReq.client.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesStatus = selectedStatus.value === '' || 
      returnReq.status === selectedStatus.value
    
    const matchesReason = selectedReason.value === '' || 
      returnReq.reason === selectedReason.value
    
    return matchesSearch && matchesStatus && matchesReason
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  selectedReason.value = ''
}

const reviewReturn = (returnReq) => {
  selectedReturn.value = returnReq
  reviewForm.value.approvedAmount = returnReq.refundAmount
}

const submitReturn = () => {
  const newId = Math.max(...returnRequests.value.map(r => r.id)) + 1
  returnRequests.value.push({
    id: newId,
    returnId: `RET-2024-00${newId}`,
    orderId: returnForm.value.orderId,
    product: 'New Product',
    reason: returnForm.value.reason,
    details: returnForm.value.details,
    status: 'Pending',
    date: new Date().toISOString().split('T')[0],
    refundAmount: parseInt(returnForm.value.refundAmount) || 0,
    client: 'New Client',
    email: 'new@email.com'
  })
  
  showReturnForm.value = false
  returnForm.value = {
    orderId: '',
    reason: 'Damaged Item',
    details: '',
    refundAmount: '',
    refundMethod: 'Original Payment'
  }
}

const approveReturn = (returnReq) => {
  returnReq.status = 'Approved'
  if (reviewForm.value.approvedAmount) {
    returnReq.refundAmount = parseInt(reviewForm.value.approvedAmount)
  }
  selectedReturn.value = null
}

const rejectReturn = (returnReq) => {
  returnReq.status = 'Rejected'
  selectedReturn.value = null
}

const processRefund = (returnReq) => {
  returnReq.status = 'Refunded'
}

const viewReturnHistory = () => {
  alert('Showing return history...')
}
</script>

<style scoped>
.ecommerce-returns {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-returns {
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>