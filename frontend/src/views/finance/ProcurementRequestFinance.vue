<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header Section -->
    <header class="mb-8">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <div class="p-2 bg-indigo-100 rounded-lg mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Finance Approval Portal</h1>
        </div>
        <div class="text-sm bg-gray-100 px-3 py-1 rounded-full flex items-center">
          <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
          <span class="text-gray-700">Finance Department</span>
        </div>
      </div>
      <p class="text-gray-600">Review and approve or reject procurement requests from various departments.</p>
    </header>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      <p class="mt-2 text-gray-600">Loading procurement requests...</p>
    </div>

    <!-- Main Content -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Request List -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
              <div>
                <h2 class="text-lg font-semibold text-gray-800">Pending Requests</h2>
                <p class="text-sm text-gray-500 mt-1">Click on a request to review details</p>
              </div>
              <div class="flex space-x-2">
                <button 
                  @click="refreshData"
                  class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-md flex items-center"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  Refresh
                </button>
              </div>
            </div>
          </div>
          
          <!-- Request List -->
          <div v-if="pendingRequests.length > 0" class="divide-y divide-gray-100">
            <!-- Request Item -->
            <div 
              v-for="request in pendingRequests" 
              :key="request.id"
              @click="selectRequest(request.id)"
              :class="['p-4 hover:bg-gray-50 cursor-pointer transition-colors', selectedRequestId === request.id ? 'bg-blue-50' : '']"
            >
              <div class="flex justify-between items-start">
                <div>
                  <div class="flex items-center mb-2">
                    <span class="text-sm font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 mr-3">{{ request.request_code }}</span>
                    <span class="text-sm font-medium px-2.5 py-0.5 rounded-full" :class="getCategoryClass(request.category)">{{ request.category }}</span>
                  </div>
                  <h3 class="font-medium text-gray-900 mb-1">{{ request.product_name }}</h3>
                  <p class="text-sm text-gray-600 mb-2">Submitted by {{ request.requester?.full_name }} • {{ request.department || 'No department' }}</p>
                  <div class="flex items-center text-sm text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="mr-4">Submitted: {{ formatDate(request.request_date) }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>₱{{ formatCurrency(request.total_cost) }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="flex items-center justify-end mb-2">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                      <span class="text-xs font-medium text-blue-800">{{ getInitials(request.requester?.full_name) }}</span>
                    </div>
                    <span class="text-sm text-gray-600">{{ request.requester?.full_name }}</span>
                  </div>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ request.formatted_status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Empty State -->
          <div v-else class="p-8 text-center">
            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Requests</h3>
            <p class="text-gray-500">All procurement requests have been processed.</p>
          </div>
        </div>

        <!-- Recently Processed -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Recently Processed</h2>
            <p class="text-sm text-gray-500 mt-1">Requests approved or rejected in the last 7 days</p>
          </div>
          <div class="p-4">
            <div v-if="recentlyProcessed.length > 0" class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="text-left text-sm text-gray-500 border-b border-gray-200">
                    <th class="pb-3">Request ID</th>
                    <th class="pb-3">Item</th>
                    <th class="pb-3">Amount</th>
                    <th class="pb-3">Status</th>
                    <th class="pb-3">Processed Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="request in recentlyProcessed" :key="request.id" class="text-sm border-b border-gray-100 hover:bg-gray-50">
                    <td class="py-3">{{ request.request_code }}</td>
                    <td class="py-3">{{ request.product_name }}</td>
                    <td class="py-3">₱{{ formatCurrency(request.total_cost) }}</td>
                    <td class="py-3">
                      <span v-if="request.status === 'approved'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approved
                      </span>
                      <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Rejected
                      </span>
                    </td>
                    <td class="py-3">{{ formatDate(request.updated_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="p-4 text-center text-gray-500">
              No recently processed requests.
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Request Details & Actions -->
      <div>
        <!-- Selected Request Details -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6" v-if="selectedRequest && selectedRequestId">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Request Details</h2>
            <p class="text-sm text-gray-500 mt-1">Review request information before making a decision</p>
          </div>
          
          <div class="p-6">
            <!-- Request Header -->
            <div class="mb-6 pb-4 border-b border-gray-200">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <h3 class="text-xl font-bold text-gray-900 mb-2">{{ selectedRequest.product_name }}</h3>
                  <div class="flex items-center mb-3">
                    <span class="text-sm font-medium px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 mr-2">{{ selectedRequest.request_code }}</span>
                    <span class="text-sm font-medium px-2.5 py-0.5 rounded-full" :class="getCategoryClass(selectedRequest.category)">{{ selectedRequest.category }}</span>
                  </div>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ selectedRequest.formatted_status }}
                </span>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Submitted By</p>
                  <p class="font-medium">{{ selectedRequest.requester?.full_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Department</p>
                  <p class="font-medium">{{ selectedRequest.department || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Submission Date</p>
                  <p class="font-medium">{{ formatDate(selectedRequest.request_date) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Total Amount</p>
                  <p class="font-medium text-lg text-gray-900">₱{{ formatCurrency(selectedRequest.total_cost) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Supplier</p>
                  <p class="font-medium">{{ selectedRequest.supplier }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Priority</p>
                  <p class="font-medium capitalize">{{ selectedRequest.priority }}</p>
                </div>
              </div>
            </div>

            <!-- Request Description -->
            <div class="mb-6" v-if="selectedRequest.instructions">
              <h4 class="font-medium text-gray-900 mb-2">Instructions/Special Notes</h4>
              <p class="text-gray-700">{{ selectedRequest.instructions }}</p>
            </div>

            <!-- Items List -->
            <div class="mb-6">
              <h4 class="font-medium text-gray-900 mb-3">Items Requested</h4>
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="mb-3">
                  <div class="flex justify-between items-center">
                    <div>
                      <p class="font-medium">{{ selectedRequest.product_name }}</p>
                      <p class="text-sm text-gray-600">Quantity: {{ selectedRequest.quantity }} • Unit Price: ₱{{ formatCurrency(selectedRequest.unit_price) }}</p>
                    </div>
                    <p class="font-medium">₱{{ formatCurrency(selectedRequest.total_cost) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Budget Information -->
            <div class="mb-8">
              <h4 class="font-medium text-gray-900 mb-3">Budget Information</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <p class="text-sm text-gray-500 mb-1">Department Budget</p>
                  <p class="text-lg font-medium text-gray-900">₱{{ formatCurrency(budgetInfo.department_budget) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                  <p class="text-sm text-gray-500 mb-1">Remaining Balance</p>
                  <p class="text-lg font-medium text-gray-900">₱{{ formatCurrency(budgetInfo.remaining_balance) }}</p>
                </div>
              </div>
              <div v-if="budgetInfo.can_afford === false" class="mt-2 p-2 bg-red-50 border border-red-200 rounded-md">
                <p class="text-sm text-red-600 flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  This request exceeds the available budget.
                </p>
              </div>
            </div>

            <!-- Approval Actions -->
            <div class="border-t border-gray-200 pt-6">
              <h4 class="font-medium text-gray-900 mb-4">Finance Decision</h4>
              <div class="space-y-4">
                <div>
                  <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comments (Optional)</label>
                  <textarea 
                    id="comments" 
                    rows="3" 
                    v-model="comments"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Add any comments regarding your decision..."
                  ></textarea>
                </div>
                
                <div class="flex space-x-4">
                  <button 
                    @click="approveRequest"
                    :disabled="processing"
                    :class="['flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-md flex items-center justify-center transition-colors', processing ? 'opacity-50 cursor-not-allowed' : '']"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ processing ? 'Processing...' : 'Approve Request' }}
                  </button>
                  <button 
                    @click="rejectRequest"
                    :disabled="processing"
                    :class="['flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-md flex items-center justify-center transition-colors', processing ? 'opacity-50 cursor-not-allowed' : '']"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    {{ processing ? 'Processing...' : 'Reject Request' }}
                  </button>
                </div>
                
                <div class="text-center">
                  <button 
                    @click="selectRequest(null)"
                    class="text-sm text-gray-600 hover:text-gray-900"
                  >
                    Cancel and return to list
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- No Request Selected -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden" v-else>
          <div class="p-8 text-center">
            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Request Selected</h3>
            <p class="text-gray-500 mb-6">Select a request from the list to review details and make a decision.</p>
            <div class="text-sm text-gray-500">
              <p class="mb-1">Use the buttons below to:</p>
              <ul class="text-left inline-block">
                <li class="flex items-center mb-1">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Approve requests that meet budget requirements
                </li>
                <li class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Reject requests that don't comply with policies
                </li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Stats Summary -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mt-6">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Monthly Summary</h2>
            <p class="text-sm text-gray-500 mt-1">{{ currentMonth }}</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div>
                <div class="flex justify-between mb-1">
                  <span class="text-sm text-gray-600">Approved</span>
                  <span class="text-sm font-medium text-gray-900">{{ statistics.approved }} requests</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-green-600 h-2 rounded-full" 
                    :style="{ width: getPercentage(statistics.approved, statistics.total_requests) + '%' }"
                  ></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between mb-1">
                  <span class="text-sm text-gray-600">Rejected</span>
                  <span class="text-sm font-medium text-gray-900">{{ statistics.rejected }} requests</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-red-600 h-2 rounded-full" 
                    :style="{ width: getPercentage(statistics.rejected, statistics.total_requests) + '%' }"
                  ></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between mb-1">
                  <span class="text-sm text-gray-600">Pending</span>
                  <span class="text-sm font-medium text-gray-900">{{ statistics.pending }} requests</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-yellow-500 h-2 rounded-full" 
                    :style="{ width: getPercentage(statistics.pending, statistics.total_requests) + '%' }"
                  ></div>
                </div>
              </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-200">
              <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                  <p class="text-2xl font-bold text-gray-900">₱{{ formatCurrency(statistics.approved_amount) }}</p>
                  <p class="text-xs text-gray-500">Approved Amount</p>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-900">₱{{ formatCurrency(statistics.rejected_amount) }}</p>
                  <p class="text-xs text-gray-500">Rejected Amount</p>
                </div>
                <div>
                  <p class="text-2xl font-bold text-gray-900">₱{{ formatCurrency(statistics.pending_amount) }}</p>
                  <p class="text-xs text-gray-500">Pending Amount</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Notification Toast -->
    <div v-if="showNotification" class="fixed bottom-4 right-4 z-50">
      <div class="bg-white rounded-lg shadow-lg p-4 flex items-center border-l-4" :class="notificationType === 'success' ? 'border-green-500' : 'border-red-500'">
        <div class="mr-3" :class="notificationType === 'success' ? 'text-green-500' : 'text-red-500'">
          <svg v-if="notificationType === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <p class="font-medium text-gray-900">{{ notificationMessage }}</p>
          <p class="text-sm text-gray-600">{{ notificationSubtitle }}</p>
        </div>
        <button @click="showNotification = false" class="ml-4 text-gray-400 hover:text-gray-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/utils/axios.js'; // Adjust the path to your axios.js file

export default {
  name: 'ProcurementRequestFinance',
  data() {
    return {
      selectedRequestId: null,
      selectedRequest: null,
      comments: '',
      showNotification: false,
      notificationMessage: '',
      notificationSubtitle: '',
      notificationType: 'success',
      pendingRequests: [],
      recentlyProcessed: [],
      statistics: {
        approved: 0,
        rejected: 0,
        pending: 0,
        approved_amount: 0,
        rejected_amount: 0,
        pending_amount: 0,
        total_requests: 0
      },
      budgetInfo: {
        department_budget: 0,
        remaining_balance: 0,
        budget_utilization: '0%',
        can_afford: true
      },
      loading: false,
      processing: false,
      currentMonth: new Date().toLocaleString('default', { month: 'long', year: 'numeric' })
    }
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        const response = await api.get('/finance/procurement/requests');
        
        if (response.data.success) {
          this.pendingRequests = response.data.data.pending_requests.data || response.data.data.pending_requests;
          this.recentlyProcessed = response.data.data.recently_processed;
          this.statistics = response.data.data.statistics;
          this.statistics.total_requests = this.statistics.approved + this.statistics.rejected + this.statistics.pending;
        }
      } catch (error) {
        console.error('Error fetching procurement requests:', error);
        this.showNotificationMessage('Error', 'Failed to load procurement requests.', 'error');
      } finally {
        this.loading = false;
      }
    },
    
    async selectRequest(id) {
      if (!id) {
        this.selectedRequestId = null;
        this.selectedRequest = null;
        return;
      }
      
      this.loading = true;
      try {
        const response = await api.get(`/finance/procurement/requests/${id}`);
        
        if (response.data.success) {
          this.selectedRequestId = id;
          this.selectedRequest = response.data.data.request;
          this.budgetInfo = response.data.data.budget_info;
        }
      } catch (error) {
        console.error('Error fetching request details:', error);
        this.showNotificationMessage('Error', 'Failed to load request details.', 'error');
      } finally {
        this.loading = false;
      }
    },
    
    async approveRequest() {
      if (!this.selectedRequestId) return;
      
      this.processing = true;
      try {
        const response = await api.post(`/finance/procurement/requests/${this.selectedRequestId}/approve`, {
          comments: this.comments
        });
        
        if (response.data.success) {
          this.showNotificationMessage(
            'Request Approved',
            `Request ${this.selectedRequest.request_code} has been approved successfully.`,
            'success'
          );
          
          // Refresh data and clear selection
          this.fetchData();
          this.selectRequest(null);
          this.comments = '';
        }
      } catch (error) {
        console.error('Error approving request:', error);
        this.showNotificationMessage('Error', 'Failed to approve request.', 'error');
      } finally {
        this.processing = false;
      }
    },
    
    async rejectRequest() {
      if (!this.selectedRequestId) return;
      
      if (!this.comments.trim()) {
        this.showNotificationMessage('Warning', 'Please provide a reason for rejection.', 'error');
        return;
      }
      
      this.processing = true;
      try {
        const response = await api.post(`/finance/procurement/requests/${this.selectedRequestId}/reject`, {
          comments: this.comments
        });
        
        if (response.data.success) {
          this.showNotificationMessage(
            'Request Rejected',
            `Request ${this.selectedRequest.request_code} has been rejected.`,
            'error'
          );
          
          // Refresh data and clear selection
          this.fetchData();
          this.selectRequest(null);
          this.comments = '';
        }
      } catch (error) {
        console.error('Error rejecting request:', error);
        this.showNotificationMessage('Error', 'Failed to reject request.', 'error');
      } finally {
        this.processing = false;
      }
    },
    
    async refreshData() {
      await this.fetchData();
      this.showNotificationMessage('Success', 'Data refreshed successfully.', 'success');
    },
    
    getCategoryClass(category) {
      const classes = {
        'Hardware': 'bg-amber-100 text-amber-800',
        'Software': 'bg-emerald-100 text-emerald-800',
        'Office Supplies': 'bg-purple-100 text-purple-800',
        'Interior Paints': 'bg-blue-100 text-blue-800',
        'Exterior Paints': 'bg-red-100 text-red-800',
        'Specialty Paints': 'bg-pink-100 text-pink-800'
      };
      return classes[category] || 'bg-gray-100 text-gray-800';
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      });
    },
    
    formatCurrency(amount) {
      if (!amount) return '0.00';
      return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    getInitials(name) {
      if (!name) return '?';
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    },
    
    getPercentage(value, total) {
      if (!total || total === 0) return 0;
      return ((value / total) * 100).toFixed(1);
    },
    
    showNotificationMessage(message, subtitle, type = 'success') {
      this.notificationMessage = message;
      this.notificationSubtitle = subtitle;
      this.notificationType = type;
      this.showNotification = true;
      
      // Auto-hide after 5 seconds
      setTimeout(() => {
        this.showNotification = false;
      }, 5000);
    }
  }
}
</script>