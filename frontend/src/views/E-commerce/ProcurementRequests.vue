<template>
  <div class="procurement-requests p-4 md:p-6">

    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Procurement Requests</h1>
          <p class="text-gray-300">Request bulk products directly from your partnered suppliers</p>
        </div>
        <div class="flex items-center space-x-4 mt-4 md:mt-0">
          <button @click="fetchStatistics" class="px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Refresh Stats
          </button>
          <button @click="requirePermission('create', openRequestModal)" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
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
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium w-1/3">Product Details</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Supplier</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Qty</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Cost</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Priority</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="requests.length === 0" class="border-t border-gray-800">
               <td colspan="8" class="py-8 text-center text-gray-500">No procurement requests found.</td>
            </tr>
            <tr v-else v-for="request in requests" :key="request.id" class="border-t border-gray-800 hover:bg-white/5">
              <td class="py-4 px-6">
                <span class="text-indigo-300 font-mono text-sm">{{ request.request_code }}</span>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center space-x-4">
                  <div class="w-14 h-14 rounded-lg bg-gray-800 border border-gray-700 overflow-hidden shrink-0 flex items-center justify-center">
                    <img v-if="request.raw_material_details?.image_url" :src="getImageUrl(request.raw_material_details.image_url)" class="w-full h-full object-cover" />
                    <svg v-else class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  </div>
                  <div>
                    <h4 class="text-white font-medium">{{ request.product_name }}</h4>
                    <p class="text-xs text-gray-400 mt-1">
                      {{ request.category }} 
                      <span v-if="request.raw_material_details">
                        | {{ request.raw_material_details.type }} | {{ request.raw_material_details.size }}
                      </span>
                    </p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6 text-gray-300">{{ request.supplier }}</td>
              <td class="py-4 px-6 text-white font-medium">{{ request.quantity }}</td>
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
              <td class="py-4 px-6 text-gray-300 text-sm">{{ formatDate(request.request_date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div v-if="pagination" class="flex items-center justify-between p-4 border-t border-gray-800">
        <div class="text-sm text-gray-400">
          Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} requests
        </div>
        <div class="flex space-x-2">
          <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800 disabled:opacity-50">Previous</button>
          <span class="px-3 py-1 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg">{{ pagination.current_page || 1 }}</span>
          <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800 disabled:opacity-50">Next</button>
        </div>
      </div>
    </div>

    <div v-if="showRequestModal" class="fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between shrink-0">
          <text class="text-xl font-bold text-white">Bulk Procurement Request</text>
          <button @click="closeModal" class="text-gray-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <div class="wizard-progress p-6 border-b border-gray-800 shrink-0">
          <div v-for="(step, index) in wizardSteps" :key="index" :class="['wizard-step', { 'active': currentStep === index + 1, 'completed': currentStep > index + 1 }]">
            <div class="wizard-step-circle"><span v-if="currentStep <= index + 1">{{ index + 1 }}</span><svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
            <span class="wizard-step-label">{{ step.label }}</span>
          </div>
        </div>
        
        <div class="p-6 overflow-y-auto grow">
          <form @submit.prevent="submitRequest">
            <div v-if="submitError" class="mb-6 bg-red-900/20 border border-red-800 rounded-lg p-4 flex items-start gap-3">
              <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-red-300 font-medium">{{ submitError }}</p>
            </div>
            
            <div class="wizard-form-content">
              
              <div v-if="currentStep === 1" class="wizard-form-step space-y-6">
                <text class="text-lg font-semibold text-white">Select a Supplier</text>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div v-for="supplier in suppliers" :key="supplier.id" @click="selectSupplierFromWizard(supplier)" :class="['p-4 border rounded-xl cursor-pointer transition-all flex items-center', requestForm.supplier_id === supplier.id ? 'border-indigo-500 bg-indigo-500/10' : 'border-gray-700 bg-gray-800 hover:bg-gray-700']">
                    <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold mr-4">{{ supplier.name.charAt(0) }}</div>
                    <div>
                      <text class="text-white font-medium">{{ supplier.name }}</text>
                    </div>
                  </div>
                  <div v-if="suppliers.length === 0" class="col-span-full text-gray-400 py-4">You have no active supplier partnerships.</div>
                </div>
              </div>

              <div v-else-if="currentStep === 2" class="wizard-form-step space-y-6">
                <div class="flex justify-between items-center mb-4">
                  <text class="text-lg font-semibold text-white">Select Products from {{ requestForm.supplier }}</text>
                  <div class="bg-gray-800 px-4 py-2 rounded-lg border border-gray-700 flex items-center">
                    <span class="text-gray-400 text-sm mr-2">Cart Total:</span>
                    <span class="text-indigo-400 font-bold">₱{{ formatCurrency(calculatedCartTotal) }}</span>
                  </div>
                </div>
                
                <div v-if="productsLoading" class="text-center py-8">
                  <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                  <p class="text-gray-400 mt-2">Fetching supplier catalog...</p>
                </div>
                
                <div v-else-if="supplierProducts.length === 0" class="text-center py-8 text-gray-400">
                  This supplier currently has no active products available.
                </div>
                
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div v-for="product in supplierProducts" :key="product.id" class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex flex-col justify-between hover:border-indigo-500/50 transition-colors">
                    
                    <div class="w-full h-32 mb-4 rounded-lg overflow-hidden bg-gray-900 border border-gray-700 relative">
                        <img v-if="product.image_url" :src="getImageUrl(product.image_url)" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                        <span class="absolute top-2 right-2 bg-indigo-500 text-white text-xs font-bold px-2 py-1 rounded">₱{{ formatCurrency(product.price) }}</span>
                    </div>

                    <div>
                      <text class="text-white font-medium line-clamp-1 mb-1" :title="product.name">{{ product.name }}</text>
                      <p class="text-xs text-gray-400 mb-2">{{ product.category }} | {{ product.type }} | {{ product.size }}</p>
                      
                      <p class="text-xs font-semibold text-indigo-400 mb-4">
                        Min Order: {{ product.min_order || 1 }} 
                        <span v-if="product.max_order">| Max: {{ product.max_order }}</span>
                      </p>
                    </div>
                    
                    <div class="flex items-center justify-between bg-gray-900 rounded-lg p-1 border border-gray-700 mt-auto">
                      <button type="button" @click="updateCart(product, -1)" class="w-8 h-8 rounded text-gray-400 hover:text-white hover:bg-gray-800 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>
                      </button>
                      <span class="text-white font-medium w-12 text-center">{{ getProductQty(product.id) }}</span>
                      <button type="button" @click="updateCart(product, 1)" class="w-8 h-8 rounded text-gray-400 hover:text-white hover:bg-gray-800 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-else-if="currentStep === 3" class="wizard-form-step space-y-6">
                <text class="text-lg font-semibold text-white mb-4">Delivery & Logistics</text>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="wizard-form-group">
                    <label class="block text-sm text-gray-300 mb-2">Delivery Address <span class="text-red-400">*</span></label>
                    <select v-if="distributorAddresses.length > 0" @change="onAddressSelect($event)" class="w-full mb-2 px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none text-sm">
                      <option value="">Select Registered Address...</option>
                      <option v-for="addr in distributorAddresses" :key="addr.id" :value="formatAddress(addr)">{{ addr.barangay }}, {{ addr.city }}</option>
                    </select>
                    <textarea v-model="requestForm.delivery_address" required rows="3" placeholder="Enter complete delivery address" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
                  </div>
                  
                  <div class="wizard-form-group">
                    <label class="block text-sm text-gray-300 mb-2">Required By Date</label>
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
                    <label class="block text-sm text-gray-300 mb-2">Payment Terms <span class="text-red-400">*</span></label>
                    <select v-model="requestForm.payment_terms" class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white appearance-none">
                      <option v-if="selectedSupplierSettings.is_cod_enabled" value="cod">Cash on Delivery</option>
                      <option v-if="selectedSupplierSettings.is_gcash_enabled" value="gcash">GCash</option>
                      <option v-if="!selectedSupplierSettings.is_cod_enabled && !selectedSupplierSettings.is_gcash_enabled" value="" disabled>No Payment Methods Available</option>
                    </select>
                  </div>
                </div>
                
                <div class="wizard-form-group">
                  <label class="block text-sm text-gray-300 mb-2">Special Instructions</label>
                  <textarea v-model="requestForm.instructions" rows="2" placeholder="Any special requirements..." class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
                </div>
              </div>

              <div v-else-if="currentStep === 4" class="wizard-form-step space-y-6">
                <text class="text-lg font-semibold text-white mb-4">Final Review</text>
                
                <div class="wizard-form-group mb-6">
                  <label class="block text-sm text-gray-300 mb-2">Priority Level <span class="text-red-400">*</span></label>
                  <div class="grid grid-cols-3 gap-4">
                    <button type="button" @click="requestForm.priority = 'low'" :class="['p-3 rounded-lg border transition-colors', requestForm.priority === 'low' ? 'bg-blue-500/20 border-blue-500 text-blue-300' : 'bg-gray-800 border-gray-700 text-gray-300']">Low</button>
                    <button type="button" @click="requestForm.priority = 'medium'" :class="['p-3 rounded-lg border transition-colors', requestForm.priority === 'medium' ? 'bg-yellow-500/20 border-yellow-500 text-yellow-300' : 'bg-gray-800 border-gray-700 text-gray-300']">Medium</button>
                    <button type="button" @click="requestForm.priority = 'high'" :class="['p-3 rounded-lg border transition-colors', requestForm.priority === 'high' ? 'bg-red-500/20 border-red-500 text-red-300' : 'bg-gray-800 border-gray-700 text-gray-300']">High</button>
                  </div>
                </div>
                
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                  <text class="text-white font-semibold mb-4 border-b border-gray-700 pb-2">Order Items</text>
                  <div class="space-y-3 mb-6">
                    <div v-for="item in cart" :key="item.id" class="flex justify-between text-sm">
                      <span class="text-gray-300">{{ item.name }} (x{{ item.quantity }})</span>
                      <span class="text-white">₱{{ formatCurrency(item.price * item.quantity) }}</span>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm border-t border-gray-700 pt-4">
                    <div class="space-y-3">
                      <div class="flex justify-between"><span class="text-gray-400">Supplier:</span><span class="text-white">{{ requestForm.supplier }}</span></div>
                      <div class="flex justify-between"><span class="text-gray-400">Delivery:</span><span class="text-white capitalize">{{ requestForm.shipping_method }}</span></div>
                    </div>
                    <div class="space-y-3">
                      <div class="flex justify-between"><span class="text-gray-400">Total Cost:</span><span class="text-indigo-400 font-bold text-lg">₱{{ formatCurrency(calculatedCartTotal) }}</span></div>
                      <div class="flex justify-between"><span class="text-gray-400">Req. Date:</span><span class="text-white">{{ requestForm.required_by_date || 'N/A' }}</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-gray-800 shrink-0 mt-6">
              <button type="button" @click="prevStep" :disabled="currentStep === 1" class="px-6 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 disabled:opacity-50 transition-colors">Previous</button>
              
              <button v-if="currentStep < wizardSteps.length" type="button" @click="nextStep" :disabled="!validateCurrentStep" class="px-6 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 text-white disabled:opacity-50 transition-opacity">Next</button>
              
              <button v-else type="submit" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity">
                {{ submitting ? 'Submitting...' : 'Submit Bulk Request' }}
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
import { toast } from 'vue-sonner'
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
const suppliers = ref([])
const distributorAddresses = ref([])
const supplierProducts = ref([])
const cart = ref([])
const statistics = ref(null)
const pagination = ref(null)
const currentPage = ref(1)

const currentStep = ref(1)
const wizardSteps = ref([
  { label: 'Select Supplier', completed: false },
  { label: 'Add to Cart', completed: false },
  { label: 'Logistics', completed: false },
  { label: 'Review', completed: false }
])

const requestForm = ref({
  supplier_id: '',
  supplier: '',
  priority: 'medium',
  delivery_address: '',
  shipping_method: 'standard',
  payment_terms: '', 
  instructions: '',
  required_by_date: ''
})

// Supplier Settings Computed
const selectedSupplierSettings = computed(() => {
  const supplier = suppliers.value.find(s => s.id === requestForm.value.supplier_id)
  return supplier?.payment_settings || { is_cod_enabled: true, is_gcash_enabled: false }
})

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_create: false,
  can_update: false,
  can_delete: false
})

// RBAC Action Interceptor
const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied: You do not have permission to ${action} procurement requests.`);
    return;
  }
  if (callback) callback();
}

const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000';
  return `${baseUrl}/storage/${path}`;
}

const calculatedCartTotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0)
})

const validateCurrentStep = computed(() => {
  switch (currentStep.value) {
    case 1: return requestForm.value.supplier_id !== ''
    case 2: return cart.value.length > 0
    case 3: return requestForm.value.delivery_address !== '' && requestForm.value.payment_terms !== ''
    case 4: return requestForm.value.priority !== ''
    default: return false
  }
})

const nextStep = () => {
  if (currentStep.value < wizardSteps.value.length && validateCurrentStep.value) {
    wizardSteps.value[currentStep.value - 1].completed = true
    currentStep.value++
  } else {
    let errorMessage = ''
    if (currentStep.value === 1) errorMessage = 'Please select a supplier.'
    else if (currentStep.value === 2) errorMessage = 'Please add at least one product to the cart.'
    else if (currentStep.value === 3) {
      if (!requestForm.value.delivery_address) errorMessage = 'Please provide a delivery address.'
      else if (!requestForm.value.payment_terms) errorMessage = 'Please select a valid payment term.'
    }
    
    if (errorMessage) showToast(errorMessage, 'warning')
  }
}

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--
}

const fetchRequests = async () => {
  try {
    loading.value = true
    error.value = ''
    
    const params = { 
      page: currentPage.value, 
      per_page: 10 
    }
    
    if (searchQuery.value) params.search = searchQuery.value
    if (selectedStatus.value) params.status = selectedStatus.value
    if (selectedPriority.value) params.priority = selectedPriority.value

    const response = await api.get('/procurement/requests', { params })
    
    if (response.data.success) {
      requests.value = response.data.data.data
      pagination.value = response.data.data
      
      if (response.data.permissions) {
          permissions.value = response.data.permissions
      }
    }
  } catch (err) {
    if (err.response?.status === 403) {
      toast.error('Unauthorized: Access to procurement requests is restricted.')
    } else {
      error.value = 'Failed to load requests.'
    }
  } finally {
    loading.value = false
  }
}

const fetchFormOptions = async () => {
  try {
    const response = await api.get('/procurement/form-options');
    if (response.data.success) {
      suppliers.value = response.data.data.suppliers;
      distributorAddresses.value = response.data.data.addresses;
    }
  } catch (err) {
    console.error(err);
  }
}

const fetchStatistics = async () => {
  try {
    const response = await api.get('/procurement/statistics')
    if (response.data.success) statistics.value = response.data.data
  } catch (err) {
    console.error(err)
  }
}

const fetchSupplierProducts = async (supplierId) => {
  try {
    productsLoading.value = true
    supplierProducts.value = []
    cart.value = [] 
    
    const response = await api.get(`/procurement/supplier-products/${supplierId}`)
    if (response.data.success) {
      supplierProducts.value = response.data.data
    }
  } catch (err) {
    showToast('Failed to fetch supplier products', 'error')
  } finally {
    productsLoading.value = false
  }
}

const selectSupplierFromWizard = (supplier) => {
  requestForm.value.supplier_id = supplier.id
  requestForm.value.supplier = supplier.name

  // Auto-select the first available payment method for this supplier
  if (supplier.payment_settings?.is_cod_enabled) {
    requestForm.value.payment_terms = 'cod'
  } else if (supplier.payment_settings?.is_gcash_enabled) {
    requestForm.value.payment_terms = 'gcash'
  } else {
    requestForm.value.payment_terms = ''
  }

  fetchSupplierProducts(supplier.id)
}

const startRequestWithSupplier = (supplier) => {
  openRequestModal()
  selectSupplierFromWizard(supplier)
  nextStep() 
}

const updateCart = (product, change) => {
  const index = cart.value.findIndex(p => p.id === product.id)
  const minOrder = product.min_order || 1
  const maxOrder = product.max_order || 5000 
  
  if (index > -1) {
    const currentQty = cart.value[index].quantity
    const newQty = currentQty + change
    
    if (change < 0 && newQty < minOrder) {
      cart.value.splice(index, 1)
    } else if (newQty > maxOrder) {
      showToast(`Maximum order limit for ${product.name} is ${maxOrder}`, 'warning')
    } else {
      cart.value[index].quantity = newQty
    }
  } else if (change > 0) {
    cart.value.push({ ...product, quantity: minOrder })
  }
}

const getProductQty = (productId) => {
  const item = cart.value.find(p => p.id === productId)
  return item ? item.quantity : 0
}

const onAddressSelect = (event) => {
  requestForm.value.delivery_address = event.target.value
}

const formatAddress = (addr) => `${addr.block_address}, ${addr.barangay}, ${addr.city}, ${addr.province}`

const submitRequest = async () => {
  try {
    submitting.value = true
    submitError.value = ''
    
    const payload = {
      ...requestForm.value,
      items: cart.value.map(item => ({ id: item.id, quantity: item.quantity }))
    }
    
    const response = await api.post('/procurement/requests', payload)
    
    if (response.data.success) {
      showToast('Bulk Procurement requests generated successfully!', 'success')
      closeModal()
      fetchRequests()
      fetchStatistics()
    } else {
      submitError.value = response.data.message
    }
  } catch (err) {
    // The explicit error message generated by our budget check in the backend
    // will be caught and displayed right here
    submitError.value = err.response?.data?.message || 'Failed to submit'
  } finally {
    submitting.value = false
  }
}

const openRequestModal = () => {
  showRequestModal.value = true
  fetchFormOptions()
}

const closeModal = () => {
  showRequestModal.value = false
  submitError.value = ''
  currentStep.value = 1
  wizardSteps.value.forEach(s => s.completed = false)
  cart.value = []
  requestForm.value = { supplier_id: '', supplier: '', priority: 'medium', delivery_address: '', shipping_method: 'standard', payment_terms: '', instructions: '', required_by_date: '' }
}

const changePage = (page) => {
  currentPage.value = page
  fetchRequests()
}

const resetFilters = () => {
  searchQuery.value = ''; selectedStatus.value = ''; selectedPriority.value = ''; currentPage.value = 1
  fetchRequests()
}

const debounce = (func, wait) => {
  let timeout; return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func(...args), wait); }
}
let debouncedFetchRequests = () => {}

const formatCurrency = (val) => val ? parseFloat(val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00'
const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : ''

const showToast = (message, type = 'info') => {
  if (type === 'success') toast.success(message)
  else if (type === 'error') toast.error(message)
  else if (type === 'warning') toast.warning(message)
  else toast.info(message)
}

onMounted(() => {
  fetchRequests()
  fetchStatistics()
  debouncedFetchRequests = debounce(fetchRequests, 500)
})
</script>

<style scoped>
.procurement-requests { min-height: 100vh; }
.wizard-progress { display: flex; justify-content: space-between; position: relative; }
.wizard-progress::before { content: ''; position: absolute; top: 24px; left: 0; right: 0; height: 2px; background-color: #374151; z-index: 1; }
.wizard-step { display: flex; flex-direction: column; align-items: center; position: relative; z-index: 2; flex: 1; }
.wizard-step-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; background-color: #374151; color: #9ca3af; border: 2px solid #374151; transition: all 0.3s ease; margin-bottom: 8px; }
.wizard-step.active .wizard-step-circle { background-color: #4f46e5; border-color: #4f46e5; color: white; }
.wizard-step.completed .wizard-step-circle { background-color: #10b981; border-color: #10b981; color: white; }
.wizard-step-label { font-size: 0.875rem; color: #9ca3af; text-align: center; }
.wizard-step.active .wizard-step-label { color: white; font-weight: 500; }
.wizard-step.completed .wizard-step-label { color: #10b981; }
.wizard-form-step { animation: fadeIn 0.3s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.wizard-form-group { margin-bottom: 1rem; }
@media (max-width: 768px) { .wizard-progress { display: none; } }
</style>