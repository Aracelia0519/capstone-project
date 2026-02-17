<template>
  <div class="procurement-requests p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Procurement Requests</h1>
          <p class="text-gray-300">Request products from available suppliers and distributors</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 md:mt-0">
          <button @click="fetchStatistics" class="px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Refresh Stats
          </button>
          <button @click="openRequestModal" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Request
          </button>
        </div>
      </div>
    </div>

    <div v-if="statistics" class="mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400">Total Requests</p>
              <h3 class="text-2xl font-bold text-white">{{ statistics.total_requests }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400">Total Cost</p>
              <h3 class="text-2xl font-bold text-white">₱{{ formatCurrency(statistics.total_cost) }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400">Pending</p>
              <h3 class="text-2xl font-bold text-yellow-300">{{ statistics.status_counts?.pending || 0 }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-yellow-500 to-orange-500 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400">Approved</p>
              <h3 class="text-2xl font-bold text-green-300">{{ statistics.status_counts?.approved || 0 }}</h3>
            </div>
            <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-green-500 to-teal-500 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-2">Search</label>
          <input type="text" v-model="searchQuery" @input="debouncedFetchRequests" placeholder="Search requests..." class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500">
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Status</label>
          <select v-model="selectedStatus" @change="fetchRequests" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Priority</label>
          <select v-model="selectedPriority" @change="fetchRequests" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none">
            <option value="">All Priorities</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
          </select>
        </div>
        <div class="flex items-end space-x-2">
          <button @click="resetFilters" class="flex-1 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-white transition-colors">
            Reset Filters
          </button>
          <button @click="fetchRequests" class="flex-1 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity">
            Apply
          </button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-8">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
      <p class="text-gray-400 mt-2">Loading requests...</p>
    </div>

    <div v-else-if="error" class="bg-red-900/20 border border-red-800 rounded-xl p-4 text-center">
      <p class="text-red-300">{{ error }}</p>
      <button @click="fetchRequests" class="mt-2 px-4 py-2 bg-red-800 hover:bg-red-700 text-white rounded-lg transition-colors">
        Retry
      </button>
    </div>

    <div v-else class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-900/80">
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Request ID</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Product</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Supplier</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Quantity</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Total Cost</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Priority</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Request Date</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="request in requests" :key="request.id" class="border-t border-gray-800 hover:bg-white/5">
              <td class="py-4 px-6">
                <span class="text-indigo-300 font-mono text-sm">{{ request.request_code }}</span>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-white font-medium">{{ request.product_name }}</h4>
                    <p class="text-sm text-gray-400">{{ request.category }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs mr-2">
                    {{ request.supplier?.charAt(0) || 'S' }}
                  </div>
                  <span class="text-gray-300">{{ request.supplier }}</span>
                </div>
              </td>
              <td class="py-4 px-6 text-white font-medium">{{ request.quantity }} units</td>
              <td class="py-4 px-6 text-white font-medium">₱{{ formatCurrency(request.total_cost) }}</td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  request.priority === 'high' ? 'bg-red-500/20 text-red-300' :
                  request.priority === 'medium' ? 'bg-yellow-500/20 text-yellow-300' :
                  'bg-blue-500/20 text-blue-300'
                ]">
                  {{ request.priority.charAt(0).toUpperCase() + request.priority.slice(1) }}
                </span>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  request.status === 'approved' ? 'bg-green-500/20 text-green-300' :
                  request.status === 'pending' ? 'bg-yellow-500/20 text-yellow-300' :
                  request.status === 'rejected' ? 'bg-red-500/20 text-red-300' :
                  request.status === 'shipped' ? 'bg-blue-500/20 text-blue-300' :
                  request.status === 'delivered' ? 'bg-purple-500/20 text-purple-300' :
                  'bg-gray-500/20 text-gray-300'
                ]">
                  {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                </span>
              </td>
              <td class="py-4 px-6 text-gray-300">{{ formatDate(request.request_date) }}</td>
              <td class="py-4 px-6">
                <div class="flex space-x-2">
                  <button @click="viewRequest(request)" class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="cancelRequest(request)" v-if="['pending', 'approved'].includes(request.status)" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div v-if="pagination" class="flex items-center justify-between p-4 border-t border-gray-800">
        <div class="text-sm text-gray-400">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} requests
        </div>
        <div class="flex space-x-2">
          <button 
            @click="changePage(pagination.current_page - 1)" 
            :disabled="pagination.current_page === 1"
            :class="['px-3 py-1 rounded-lg border', pagination.current_page === 1 ? 'border-gray-800 text-gray-600 cursor-not-allowed' : 'border-gray-700 text-gray-300 hover:bg-gray-800']"
          >
            Previous
          </button>
          <span class="px-3 py-1 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg">
            {{ pagination.current_page }}
          </span>
          <button 
            @click="changePage(pagination.current_page + 1)" 
            :disabled="pagination.current_page === pagination.last_page"
            :class="['px-3 py-1 rounded-lg border', pagination.current_page === pagination.last_page ? 'border-gray-800 text-gray-600 cursor-not-allowed' : 'border-gray-700 text-gray-300 hover:bg-gray-800']"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <div class="mt-8">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-white">Available Products for Request</h2>
        <button @click="fetchAvailableProducts" class="px-3 py-1 text-sm bg-gray-800 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-700 transition-colors">
          Refresh Products
        </button>
      </div>
      
      <div v-if="productsLoading" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-500"></div>
        <p class="text-gray-400 mt-2">Loading products...</p>
      </div>
      
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div v-for="product in availableProducts" :key="product.id" class="bg-gray-900/50 border border-gray-800 rounded-xl p-4 hover:border-purple-500 transition-colors">
          <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-300">{{ product.stock_level || 'In stock' }}</span>
          </div>
          <h4 class="text-white font-medium mb-1">{{ product.name }}</h4>
          <p class="text-sm text-gray-400 mb-2">{{ product.category }} - {{ product.type }}</p>
          <div class="flex items-center justify-between mb-3">
            <div>
              <span class="text-white font-bold">Cost: ₱{{ formatCurrency(product.cost || product.price) }}</span>
              <p class="text-xs text-gray-400">Selling: ₱{{ formatCurrency(product.price) }}</p>
            </div>
            <span class="text-xs text-gray-400">{{ product.distributor?.first_name }}</span>
          </div>
          <button @click="requestProduct(product)" class="w-full py-2 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 text-indigo-300 rounded-lg hover:bg-indigo-500/30 transition-colors">
            Request Product
          </button>
        </div>
      </div>
    </div>

    <div v-if="showRequestModal" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">New Procurement Request</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="wizard-progress p-6 border-b border-gray-800">
          <div 
            v-for="(step, index) in wizardSteps" 
            :key="index"
            :class="['wizard-step', 
                     { 'active': currentStep === index + 1, 
                       'completed': currentStep > index + 1 }]"
          >
            <div class="wizard-step-circle">
              <span v-if="currentStep <= index + 1">{{ index + 1 }}</span>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </div>
            <span class="wizard-step-label">{{ step.label }}</span>
          </div>
        </div>
        
        <div class="wizard-step-indicator p-3 bg-gray-800/50 text-center text-gray-300 text-sm">
          Step {{ currentStep }} of {{ wizardSteps.length }}: {{ wizardSteps[currentStep - 1].label }}
        </div>
        
        <div class="p-6">
          <form @submit.prevent="submitRequest">
            <div v-if="submitError" class="mb-6 bg-red-900/20 border border-red-800 rounded-lg p-4">
              <p class="text-red-300">{{ submitError }}</p>
            </div>
            
            <div class="wizard-form-content">
              <div v-if="currentStep === 1" class="wizard-form-step space-y-6">
                <div>
                  <h4 class="text-lg font-semibold text-white mb-4">Select Product</h4>
                  <div class="wizard-form-group">
                    <label class="block text-sm text-gray-300 mb-2">
                      <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                      </svg>
                      Product <span class="text-red-400">*</span>
                    </label>
                    <select v-model="requestForm.product_id" required @change="onProductSelect" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none">
                      <option value="">Choose a product</option>
                      <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                        {{ product.name }} - Cost: ₱{{ formatCurrency(product.cost || product.price) }} ({{ product.distributor?.first_name || 'Unknown' }})
                      </option>
                    </select>
                    <div v-if="!requestForm.product_id" class="wizard-step-validation mt-2 text-sm text-yellow-400 flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                      </svg>
                      Please select a product
                    </div>
                  </div>
                </div>
                
                <div v-if="selectedProduct" class="bg-gray-800/50 p-4 rounded-lg">
                  <h5 class="text-white font-medium mb-2">Selected Product Details</h5>
                  <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="text-gray-400">Name:</div>
                    <div class="text-white">{{ selectedProduct.name }}</div>
                    <div class="text-gray-400">Category:</div>
                    <div class="text-white">{{ selectedProduct.category }}</div>
                    <div class="text-gray-400">Type:</div>
                    <div class="text-white">{{ selectedProduct.type }}</div>
                    <div class="text-gray-400">Unit Cost:</div>
                    <div class="text-white font-medium">₱{{ formatCurrency(selectedProduct.cost || selectedProduct.price) }}</div>
                    <div class="text-gray-400">Selling Price:</div>
                    <div class="text-white">₱{{ formatCurrency(selectedProduct.price) }}</div>
                    <div class="text-gray-400">Available From:</div>
                    <div class="text-white">{{ selectedProduct.distributor?.first_name || 'Unknown Distributor' }}</div>
                  </div>
                </div>
              </div>

              <div v-else-if="currentStep === 2" class="wizard-form-step space-y-6">
                <div>
                  <h4 class="text-lg font-semibold text-white mb-4">Quantity & Pricing</h4>
                  
                  <div class="wizard-form-group">
                    <label class="block text-sm text-gray-300 mb-2">
                      <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Quantity <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                      <input type="number" v-model="requestForm.quantity" required min="1" max="1000" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white">
                      <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">units</span>
                    </div>
                    <div v-if="requestForm.quantity < 1 || requestForm.quantity > 1000" class="wizard-step-validation mt-2 text-sm text-yellow-400 flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                      </svg>
                      Quantity must be between 1 and 1000 units
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div class="bg-gray-800/50 p-4 rounded-lg">
                      <h5 class="text-white font-medium mb-3">Cost Breakdown</h5>
                      <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                          <span class="text-gray-400">Unit Cost:</span>
                          <span class="text-white">₱{{ formatCurrency(selectedProduct?.cost || selectedProduct?.price || 0) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                          <span class="text-gray-400">Quantity:</span>
                          <span class="text-white">{{ requestForm.quantity }} units</span>
                        </div>
                        <div class="pt-2 border-t border-gray-700">
                          <div class="flex justify-between">
                            <span class="text-gray-300 font-medium">Subtotal:</span>
                            <span class="text-white font-bold">₱{{ calculatedTotal.toLocaleString() }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="bg-gray-800/50 p-4 rounded-lg">
                      <h5 class="text-white font-medium mb-3">Supplier Information</h5>
                      <div class="wizard-form-group">
                        <label class="block text-sm text-gray-300 mb-2">Partnered Supplier <span class="text-red-400">*</span></label>
                        <select v-model="requestForm.supplier_id" required @change="onSupplierSelect" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none">
                          <option value="">Select a partner supplier</option>
                          <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                            {{ supplier.name }}
                          </option>
                        </select>
                        <div v-if="suppliers.length === 0" class="mt-2 text-xs text-yellow-500">
                          No active supplier partners found.
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else-if="currentStep === 3" class="wizard-form-step space-y-6">
                <div>
                  <h4 class="text-lg font-semibold text-white mb-4">Delivery & Payment</h4>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="wizard-form-group">
                      <label class="block text-sm text-gray-300 mb-2">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Delivery Address <span class="text-red-400">*</span>
                      </label>
                      
                      <select v-if="distributorAddresses.length > 0" @change="onAddressSelect($event)" class="w-full mb-2 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white text-sm appearance-none">
                        <option value="">Select Registered Address...</option>
                        <option v-for="addr in distributorAddresses" :key="addr.id" :value="formatAddress(addr)">
                          {{ addr.barangay }}, {{ addr.city }} ({{ addr.province }})
                        </option>
                      </select>

                      <textarea v-model="requestForm.delivery_address" required rows="3" placeholder="Enter complete delivery address" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
                    </div>
                    
                    <div class="wizard-form-group">
                      <label class="block text-sm text-gray-300 mb-2">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Required By Date
                      </label>
                      <input type="date" v-model="requestForm.required_by_date" :min="new Date().toISOString().split('T')[0]" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white">
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="wizard-form-group">
                      <label class="block text-sm text-gray-300 mb-2">Shipping Method</label>
                      <select v-model="requestForm.shipping_method" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none">
                        <option value="standard">Standard Shipping (5-7 days)</option>
                        <option value="express">Express Delivery (2-3 days)</option>
                        <option value="pickup">Supplier Pickup</option>
                      </select>
                    </div>
                    
                    <div class="wizard-form-group">
                      <label class="block text-sm text-gray-300 mb-2">Payment Terms</label>
                      <select v-model="requestForm.payment_terms" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none">
                        <option value="cod">Cash on Delivery</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="wizard-form-group">
                    <label class="block text-sm text-gray-300 mb-2">Special Instructions</label>
                    <textarea v-model="requestForm.instructions" rows="3" placeholder="Any special requirements or instructions..." class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
                  </div>
                </div>
              </div>

              <div v-else-if="currentStep === 4" class="wizard-form-step space-y-6">
                <div>
                  <h4 class="text-lg font-semibold text-white mb-4">Priority & Review</h4>
                  
                  <div class="wizard-form-group mb-6">
                    <label class="block text-sm text-gray-300 mb-2">Priority Level <span class="text-red-400">*</span></label>
                    <div class="grid grid-cols-3 gap-4">
                      <button 
                        type="button"
                        @click="requestForm.priority = 'low'"
                        :class="['p-4 rounded-lg border transition-colors', requestForm.priority === 'low' ? 'bg-blue-500/20 border-blue-500 text-blue-300' : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700']"
                      >
                        <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                        </svg>
                        <span class="block text-center font-medium">Low</span>
                      </button>
                      <button 
                        type="button"
                        @click="requestForm.priority = 'medium'"
                        :class="['p-4 rounded-lg border transition-colors', requestForm.priority === 'medium' ? 'bg-yellow-500/20 border-yellow-500 text-yellow-300' : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700']"
                      >
                        <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <span class="block text-center font-medium">Medium</span>
                      </button>
                      <button 
                        type="button"
                        @click="requestForm.priority = 'high'"
                        :class="['p-4 rounded-lg border transition-colors', requestForm.priority === 'high' ? 'bg-red-500/20 border-red-500 text-red-300' : 'bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700']"
                      >
                        <svg class="w-6 h-6 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.698-.833-2.464 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <span class="block text-center font-medium">High</span>
                      </button>
                    </div>
                  </div>
                  
                  <div class="bg-gray-800/50 rounded-xl p-6">
                    <h5 class="text-white font-semibold text-lg mb-4">Request Summary</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                      <div class="space-y-3">
                        <div class="flex justify-between">
                          <span class="text-gray-400">Product:</span>
                          <span class="text-white">{{ selectedProduct?.name || 'Not selected' }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-400">Quantity:</span>
                          <span class="text-white">{{ requestForm.quantity }} units</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-400">Supplier:</span>
                          <span class="text-white">{{ requestForm.supplier || 'Not selected' }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-400">Priority:</span>
                          <span :class="[
                            'px-2 py-1 rounded-full text-xs font-medium',
                            requestForm.priority === 'high' ? 'bg-red-500/20 text-red-300' :
                            requestForm.priority === 'medium' ? 'bg-yellow-500/20 text-yellow-300' :
                            'bg-blue-500/20 text-blue-300'
                          ]">
                            {{ requestForm.priority ? requestForm.priority.charAt(0).toUpperCase() + requestForm.priority.slice(1) : 'Not set' }}
                          </span>
                        </div>
                      </div>
                      <div class="space-y-3">
                        <div class="flex justify-between">
                          <span class="text-gray-400">Total Cost:</span>
                          <span class="text-white font-bold text-lg">₱{{ calculatedTotal.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-400">Shipping:</span>
                          <span class="text-white capitalize">{{ requestForm.shipping_method?.replace('_', ' ') || 'Standard' }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-400">Payment Terms:</span>
                          <span class="text-white">{{ requestForm.payment_terms ? requestForm.payment_terms.toUpperCase() : 'Net 30' }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-gray-400">Required By:</span>
                          <span class="text-white">{{ requestForm.required_by_date || 'Not specified' }}</span>
                        </div>
                      </div>
                    </div>
                    
                    <div v-if="requestForm.delivery_address" class="mt-4 pt-4 border-t border-gray-700">
                      <div class="text-sm">
                        <span class="text-gray-400">Delivery Address:</span>
                        <p class="text-white mt-1">{{ requestForm.delivery_address }}</p>
                      </div>
                    </div>
                    
                    <div v-if="requestForm.instructions" class="mt-4 pt-4 border-t border-gray-700">
                      <div class="text-sm">
                        <span class="text-gray-400">Special Instructions:</span>
                        <p class="text-white mt-1">{{ requestForm.instructions }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                    <p class="text-blue-300 text-sm">
                      <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Review all information carefully. Once submitted, the request will be sent for approval.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-gray-800">
              <button 
                type="button" 
                @click="prevStep" 
                :disabled="currentStep === 1"
                :class="['px-6 py-2 rounded-lg flex items-center transition-colors', 
                  currentStep === 1 
                    ? 'bg-gray-800 text-gray-600 cursor-not-allowed' 
                    : 'bg-gray-800 hover:bg-gray-700 text-gray-300 border border-gray-700']"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Previous
              </button>
              
              <button 
                v-if="currentStep < wizardSteps.length"
                type="button" 
                @click="nextStep" 
                :disabled="!validateCurrentStep"
                :class="['px-6 py-2 rounded-lg flex items-center transition-colors', 
                  !validateCurrentStep 
                    ? 'bg-gray-800 text-gray-600 cursor-not-allowed' 
                    : 'bg-gradient-to-r from-indigo-500 to-purple-500 text-white hover:opacity-90']"
              >
                Next
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
              </button>
              
              <button 
                v-else
                type="submit" 
                :disabled="submitting"
                class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="submitting" class="flex items-center">
                  <svg class="animate-spin w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                    <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" class="opacity-75"></path>
                  </svg>
                  Submitting...
                </span>
                <span v-else class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Submit Request
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

const searchQuery = ref('')
const selectedStatus = ref('')
const selectedPriority = ref('')
const showRequestModal = ref(false)
const loading = ref(false)
const productsLoading = ref(false)
const submitting = ref(false)
const submitError = ref('')
const error = ref('')

const requests = ref([])
const availableProducts = ref([])
const suppliers = ref([])
const distributorAddresses = ref([])
const statistics = ref(null)
const pagination = ref(null)
const currentPage = ref(1)

// Wizard state
const currentStep = ref(1)
const wizardSteps = ref([
  { label: 'Product Selection', completed: false },
  { label: 'Quantity & Pricing', completed: false },
  { label: 'Delivery & Payment', completed: false },
  { label: 'Priority & Review', completed: false }
])

const requestForm = ref({
  product_id: '',
  quantity: 1,
  supplier: '',
  supplier_id: '', // Added supplier_id
  priority: 'medium',
  delivery_address: '',
  shipping_method: 'standard',
  payment_terms: 'net30',
  instructions: '',
  required_by_date: ''
})

const selectedProduct = computed(() => {
  return availableProducts.value.find(p => p.id === requestForm.value.product_id)
})

const calculatedTotal = computed(() => {
  if (selectedProduct.value && requestForm.value.quantity) {
    return (selectedProduct.value.cost || selectedProduct.value.price) * requestForm.value.quantity
  }
  return 0
})

// Wizard validation
const validateCurrentStep = computed(() => {
  switch (currentStep.value) {
    case 1:
      return requestForm.value.product_id && selectedProduct.value
    case 2:
      // Validated using supplier_id now
      return requestForm.value.quantity >= 1 && 
             requestForm.value.quantity <= 1000 &&
             requestForm.value.supplier_id
    case 3:
      return requestForm.value.delivery_address
    case 4:
      return requestForm.value.priority
    default:
      return false
  }
})

// Wizard navigation
const nextStep = () => {
  if (currentStep.value < wizardSteps.value.length && validateCurrentStep.value) {
    wizardSteps.value[currentStep.value - 1].completed = true
    currentStep.value++
  } else {
    showValidationError()
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const showValidationError = () => {
  let errorMessage = ''
  switch (currentStep.value) {
    case 1:
      errorMessage = 'Please select a product'
      break
    case 2:
      if (!requestForm.value.supplier_id) {
        errorMessage = 'Please select a supplier'
      } else if (requestForm.value.quantity < 1 || requestForm.value.quantity > 1000) {
        errorMessage = 'Quantity must be between 1 and 1000 units'
      }
      break
    case 3:
      errorMessage = 'Please enter a delivery address'
      break
    case 4:
      errorMessage = 'Please select a priority level'
      break
  }
  
  if (errorMessage) {
    showToast(errorMessage, 'warning')
  }
}

// Toast notifications
const showToast = (message, type = 'info') => {
  const config = {
    text: message,
    duration: 3000,
    gravity: "top",
    position: "right",
    style: {
      borderRadius: "8px",
      padding: "12px 16px",
      fontSize: "14px",
      fontWeight: "500",
      boxShadow: "0 4px 12px rgba(0, 0, 0, 0.2)"
    }
  }

  switch (type) {
    case 'success':
      config.style.background = "linear-gradient(to right, #10b981, #059669)"
      break
    case 'error':
      config.style.background = "linear-gradient(to right, #ef4444, #dc2626)"
      break
    case 'warning':
      config.style.background = "linear-gradient(to right, #f59e0b, #d97706)"
      break
    case 'info':
      config.style.background = "linear-gradient(to right, #3b82f6, #1d4ed8)"
      break
  }

  Toastify(config).showToast()
}

// Format currency
const formatCurrency = (value) => {
  if (!value) return '0.00'
  return parseFloat(value).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Debounce function for search
const debounce = (func, wait) => {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

// Fetch procurement requests
const fetchRequests = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const params = {
      page: currentPage.value,
      per_page: 10
    }
    
    if (searchQuery.value) {
      params.search = searchQuery.value
    }
    
    if (selectedStatus.value) {
      params.status = selectedStatus.value
    }
    
    if (selectedPriority.value) {
      params.priority = selectedPriority.value
    }
    
    const response = await api.get('/procurement/requests', { params })
    
    if (response.data.success) {
      requests.value = response.data.data.data
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        from: response.data.data.from,
        to: response.data.data.to,
        total: response.data.data.total
      }
    } else {
      error.value = response.data.message || 'Failed to fetch requests'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load procurement requests'
    console.error('Error fetching requests:', err)
  } finally {
    loading.value = false
  }
}

// Fetch available products
const fetchAvailableProducts = async () => {
  try {
    productsLoading.value = true
    const response = await api.get('/procurement/available-products', {
      params: { per_page: 8 }
    })
    
    if (response.data.success) {
      availableProducts.value = response.data.data.data
    }
  } catch (err) {
    console.error('Error fetching products:', err)
  } finally {
    productsLoading.value = false
  }
}

// Fetch Form Options (Suppliers and Addresses)
const fetchFormOptions = async () => {
  try {
    const response = await api.get('/procurement/form-options');
    if (response.data.success) {
      suppliers.value = response.data.data.suppliers;
      distributorAddresses.value = response.data.data.addresses;
    }
  } catch (err) {
    console.error('Error fetching form options:', err);
  }
}

// Handle Supplier Selection to auto-fill name
const onSupplierSelect = () => {
  const selectedSupplier = suppliers.value.find(s => s.id === requestForm.value.supplier_id);
  if (selectedSupplier) {
    requestForm.value.supplier = selectedSupplier.name;
  } else {
    requestForm.value.supplier = '';
  }
}

// Helper to format address for dropdown
const formatAddress = (addr) => {
  return `${addr.block_address}, ${addr.barangay}, ${addr.city}, ${addr.province}`;
}

const onAddressSelect = (event) => {
  requestForm.value.delivery_address = event.target.value;
}

// Fetch statistics
const fetchStatistics = async () => {
  try {
    const response = await api.get('/procurement/statistics')
    
    if (response.data.success) {
      statistics.value = response.data.data
      showToast('Statistics updated successfully!', 'success')
    }
  } catch (err) {
    console.error('Error fetching statistics:', err)
    showToast('Failed to update statistics', 'error')
  }
}

// Change page
const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  currentPage.value = page
  fetchRequests()
}

// Reset filters
const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  selectedPriority.value = ''
  currentPage.value = 1
  fetchRequests()
  showToast('Filters have been reset', 'info')
}

// View request details
const viewRequest = async (request) => {
  try {
    const response = await api.get(`/procurement/requests/${request.id}`)
    
    if (response.data.success) {
      const req = response.data.data
      showToast(`Viewing request ${req.request_code}`, 'info')
    }
  } catch (err) {
    console.error('Error viewing request:', err)
    showToast('Failed to load request details', 'error')
  }
}

// Cancel request
const cancelRequest = async (request) => {
  if (!confirm(`Are you sure you want to cancel request ${request.request_code}?`)) {
    return
  }
  
  try {
    const response = await api.post(`/procurement/requests/${request.id}/cancel`)
    
    if (response.data.success) {
      showToast('Request cancelled successfully!', 'success')
      fetchRequests()
      fetchStatistics()
    } else {
      showToast(response.data.message || 'Failed to cancel request', 'error')
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to cancel request', 'error')
  }
}

// Request product
const requestProduct = (product) => {
  requestForm.value.product_id = product.id
  // We don't auto-set supplier here anymore since we want to pick from partner list
  requestForm.value.supplier = '' 
  requestForm.value.supplier_id = '' // Reset ID
  requestForm.value.quantity = 1
  currentStep.value = 1
  wizardSteps.value.forEach(step => step.completed = false)
  openRequestModal()
  showToast(`Starting request for ${product.name}`, 'info')
}

const openRequestModal = () => {
  showRequestModal.value = true;
  fetchFormOptions(); // Fetch updated options when modal opens
}

// Handle product selection
const onProductSelect = () => {
  if (selectedProduct.value && requestForm.value.quantity > 1000) {
    requestForm.value.quantity = 1
  }
}

// Submit request
const submitRequest = async () => {
  try {
    submitting.value = true
    submitError.value = ''
    
    if (!validateCurrentStep.value) {
      showValidationError()
      return
    }
    
    // Validate quantity
    if (requestForm.value.quantity < 1) {
      submitError.value = 'Quantity must be at least 1'
      showToast(submitError.value, 'warning')
      return
    }
    
    if (requestForm.value.quantity > 1000) {
      submitError.value = 'Quantity cannot exceed 1000 units'
      showToast(submitError.value, 'warning')
      return
    }
    
    const payload = {
      ...requestForm.value,
      quantity: parseInt(requestForm.value.quantity)
    }
    
    const response = await api.post('/procurement/requests', payload)
    
    if (response.data.success) {
      showToast('Procurement request submitted successfully!', 'success')
      closeModal()
      fetchRequests()
      fetchStatistics()
    } else {
      submitError.value = response.data.message || 'Failed to submit request'
      showToast(submitError.value, 'error')
    }
  } catch (err) {
    submitError.value = err.response?.data?.message || 'Failed to submit procurement request'
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      submitError.value = errors.join(', ')
    }
    showToast(submitError.value, 'error')
  } finally {
    submitting.value = false
  }
}

// Close modal
const closeModal = () => {
  showRequestModal.value = false
  submitError.value = ''
  currentStep.value = 1
  wizardSteps.value.forEach(step => step.completed = false)
  requestForm.value = {
    product_id: '',
    quantity: 1,
    supplier: '',
    supplier_id: '', // Reset ID
    priority: 'medium',
    delivery_address: '',
    shipping_method: 'standard',
    payment_terms: 'net30',
    instructions: '',
    required_by_date: ''
  }
}

// Initialize on mount
onMounted(() => {
  fetchRequests()
  fetchAvailableProducts()
  fetchStatistics()
  
  // Set debounced search
  debouncedFetchRequests = debounce(fetchRequests, 500)
})

// Debounced fetch function
let debouncedFetchRequests = () => {}
</script>

<style scoped>
.procurement-requests {
  min-height: 100vh;
}

/* Wizard Progress */
.wizard-progress {
  display: flex;
  justify-content: space-between;
  position: relative;
}

.wizard-progress::before {
  content: '';
  position: absolute;
  top: 24px;
  left: 0;
  right: 0;
  height: 2px;
  background-color: #374151;
  z-index: 1;
}

.wizard-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  z-index: 2;
  flex: 1;
}

.wizard-step-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  background-color: #374151;
  color: #9ca3af;
  border: 2px solid #374151;
  transition: all 0.3s ease;
  margin-bottom: 8px;
}

.wizard-step.active .wizard-step-circle {
  background-color: #4f46e5;
  border-color: #4f46e5;
  color: white;
}

.wizard-step.completed .wizard-step-circle {
  background-color: #10b981;
  border-color: #10b981;
  color: white;
}

.wizard-step-label {
  font-size: 0.875rem;
  color: #9ca3af;
  text-align: center;
  max-width: 100px;
}

.wizard-step.active .wizard-step-label {
  color: white;
  font-weight: 500;
}

.wizard-step.completed .wizard-step-label {
  color: #10b981;
}

/* Wizard Step Indicator for Mobile */
.wizard-step-indicator {
  display: none;
}

@media (max-width: 768px) {
  .wizard-progress {
    display: none;
  }
  
  .wizard-step-indicator {
    display: block;
  }
}

/* Wizard Form Content */
.wizard-form-step {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.wizard-step-validation {
  color: #fbbf24;
  font-size: 0.875rem;
}

/* Form Styles */
.wizard-form-group {
  margin-bottom: 1rem;
}

.wizard-form-group label {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
}

.wizard-form-group label svg {
  margin-right: 0.5rem;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .procurement-requests {
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
  
  .wizard-step-circle {
    width: 40px;
    height: 40px;
    font-size: 0.875rem;
  }
  
  .wizard-step-label {
    font-size: 0.75rem;
  }
}
</style>