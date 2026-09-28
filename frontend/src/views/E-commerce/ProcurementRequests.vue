<template>
  <div class="procurement-requests p-4 md:p-6">

    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Procurement Requests</h1>
          <h2 class="text-gray-300">Request bulk products directly from your partnered suppliers</h2>
        </div>
        <div class="flex items-center space-x-4 mt-4 md:mt-0">
          <button @click="fetchStatistics" class="px-4 py-2 bg-gray-800 border border-gray-700 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Refresh Stats
          </button>
          <button @click="requirePermission('manage', openRequestModal)" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Request
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
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

    <!-- Filter Bar -->
    <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-4 mb-6 flex flex-wrap items-center gap-4">
      <div class="flex-1 min-w-[200px]">
        <input type="text" v-model="searchQuery" placeholder="Search by product, SKU, or supplier..." class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <div class="flex gap-2">
        <select v-model="selectedStatus" class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
        </select>
        <select v-model="selectedPriority" class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
          <option value="">All Priority</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
        <button @click="resetFilters" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">Clear</button>
      </div>
    </div>

    <!-- Main List (Grouped by Product) -->
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

    <div v-else-if="groupedRequests.length === 0" class="bg-gray-900/50 border border-gray-800 rounded-xl p-8 text-center text-gray-400">
      No procurement requests found matching your filters.
    </div>

    <div v-else class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <div class="divide-y divide-gray-800">
          <div v-for="group in groupedRequests" :key="group.key" class="group-item">
            <!-- Group Header -->
            <div class="flex items-center justify-between p-4 hover:bg-gray-800/50 cursor-pointer transition" @click="toggleGroup(group.key)">
              <div class="flex items-center gap-4 flex-1 min-w-0">
                <span class="text-gray-400">
                  <svg class="w-5 h-5 transition-transform" :class="{'rotate-180': expandedGroups.includes(group.key)}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </span>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-3">
                    <span class="text-white font-medium truncate">{{ group.product_name }}</span>
                    <span class="text-xs bg-gray-700 px-2 py-0.5 rounded text-gray-300">{{ group.category }}</span>
                    <span class="text-xs text-gray-500">({{ group.requests.length }} requests)</span>
                  </div>
                  <div class="text-sm text-gray-400">
                    Total Qty: <span class="text-white font-medium">{{ group.total_quantity }}</span>
                    &nbsp;|&nbsp; Total Cost: <span class="text-indigo-400 font-medium">₱{{ formatCurrency(group.total_cost) }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-3 text-sm text-gray-400">
                <span class="hidden sm:inline">Supplier: {{ group.supplier }}</span>
                <span class="bg-gray-700 px-2 py-1 rounded text-xs">{{ group.requests.length }} items</span>
              </div>
            </div>

            <!-- Expanded Group Details -->
            <div v-if="expandedGroups.includes(group.key)" class="bg-gray-900/30 p-2 overflow-x-auto">
              <table class="w-full text-sm">
                <thead>
                  <tr class="text-gray-400 border-b border-gray-700">
                    <th class="text-left py-2 px-3">SKU / Size</th>
                    <th class="text-left py-2 px-3">Qty</th>
                    <th class="text-left py-2 px-3">Unit Price</th>
                    <th class="text-left py-2 px-3">Cost</th>
                    <th class="text-left py-2 px-3">Priority</th>
                    <th class="text-left py-2 px-3">Status</th>
                    <th class="text-left py-2 px-3">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="req in group.requests" :key="req.id" class="border-b border-gray-800/50 hover:bg-gray-800/30">
                    <td class="py-2 px-3">
                      <span class="text-gray-300 font-mono text-xs">{{ req.sku_code || 'N/A' }}</span>
                      <span class="text-gray-500 text-xs ml-2">{{ req.size || '' }}</span>
                    </td>
                    <td class="py-2 px-3 text-white">{{ req.quantity }}</td>
                    <td class="py-2 px-3 text-white">₱{{ formatCurrency(req.unit_price) }}</td>
                    <td class="py-2 px-3 text-white font-medium">₱{{ formatCurrency(req.total_cost) }}</td>
                    <td class="py-2 px-3">
                      <span :class="[
                        'px-2 py-0.5 rounded-full text-xs font-medium',
                        req.priority === 'high' ? 'bg-red-500/20 text-red-300' :
                        req.priority === 'medium' ? 'bg-yellow-500/20 text-yellow-300' :
                        'bg-blue-500/20 text-blue-300'
                      ]">
                        {{ req.priority.charAt(0).toUpperCase() + req.priority.slice(1) }}
                      </span>
                    </td>
                    <td class="py-2 px-3">
                      <span :class="[
                        'px-2 py-0.5 rounded-full text-xs font-medium',
                        req.status === 'approved' ? 'bg-green-500/20 text-green-300' :
                        req.status === 'pending' ? 'bg-yellow-500/20 text-yellow-300' :
                        req.status === 'rejected' ? 'bg-red-500/20 text-red-300' :
                        req.status === 'shipped' ? 'bg-blue-500/20 text-blue-300' :
                        req.status === 'delivered' ? 'bg-purple-500/20 text-purple-300' :
                        'bg-gray-500/20 text-gray-300'
                      ]">
                        {{ req.status.charAt(0).toUpperCase() + req.status.slice(1) }}
                      </span>
                    </td>
                    <td class="py-2 px-3 text-gray-400 text-xs">{{ formatDate(req.request_date) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
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

    <!-- =========================================================== -->
    <!-- NEW REQUEST MODAL – WITH COLOR SWATCHES & CLICKABLE IMAGES   -->
    <!-- =========================================================== -->
    <div v-if="showRequestModal" class="fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden shadow-2xl">
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-800 flex items-center justify-between shrink-0">
          <text class="text-xl font-bold text-white">Bulk Procurement Request</text>
          <button @click="closeModal" class="text-gray-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <!-- Wizard Progress -->
        <div class="wizard-progress p-6 border-b border-gray-800 shrink-0">
          <div v-for="(step, index) in wizardSteps" :key="index" :class="['wizard-step', { 'active': currentStep === index + 1, 'completed': currentStep > index + 1 }]">
            <div class="wizard-step-circle"><span v-if="currentStep <= index + 1">{{ index + 1 }}</span><svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
            <span class="wizard-step-label">{{ step.label }}</span>
          </div>
        </div>
        
        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6">
          <form @submit.prevent="submitRequest" id="procurementForm">
            <div v-if="submitError" class="mb-6 bg-red-900/20 border border-red-800 rounded-lg p-4 flex items-start gap-3">
              <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-red-300 font-medium">{{ submitError }}</p>
            </div>
            
            <div class="wizard-form-content">
              <!-- STEP 1: Select Supplier -->
              <div v-if="currentStep === 1" class="wizard-form-step space-y-6">
                <text class="text-lg font-semibold text-white">Select a Supplier</text>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div v-for="supplier in suppliers" :key="supplier.id" @click="selectSupplierFromWizard(supplier)" :class="['p-4 border rounded-xl cursor-pointer transition-all flex items-center', requestForm.supplier_id === supplier.id ? 'border-indigo-500 bg-indigo-500/10' : 'border-gray-700 bg-gray-800 hover:bg-gray-700']">
                    <div class="w-10 h-10 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 font-bold mr-4">{{ supplier.name.charAt(0) }}</div>
                    <div>
                      <text class="text-white font-medium">{{ supplier.name }}</text>
                    </div>
                  </div>
                  <div v-if="suppliers.length === 0" class="col-span-full text-gray-400 py-4">You have no active supplier partnerships or your partners are currently restricted.</div>
                </div>
              </div>

              <!-- STEP 2: Select Products – with color swatches and clickable images -->
              <div v-else-if="currentStep === 2" class="wizard-form-step space-y-6">
                <div class="flex justify-between items-center mb-4">
                  <text class="text-lg font-semibold text-white">Select Products from {{ requestForm.supplier }}</text>
                  <div class="bg-gray-800 px-4 py-2 rounded-lg border border-gray-700 flex items-center gap-4">
                    <div>
                      <span class="text-gray-400 text-sm mr-2">Cart Total:</span>
                      <span class="font-bold" :class="calculatedCartTotal > availableBudget ? 'text-red-400' : 'text-indigo-400'">₱{{ formatCurrency(calculatedCartTotal) }}</span>
                    </div>
                  </div>
                </div>

                <p class="text-xs text-gray-500 -mt-2">
                  Quantities are limited to what the supplier currently has in stock. Expired and
                  already-reserved units are not included.
                </p>
                
                <div v-if="productsLoading" class="text-center py-8">
                  <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-500"></div>
                  <p class="text-gray-400 mt-2">Fetching supplier catalog...</p>
                </div>
                
                <div v-else-if="groupedSupplierProducts.length === 0" class="text-center py-8 text-gray-400">
                  This supplier currently has no active products available.
                </div>
                
                <div v-else class="space-y-4">
                  <div v-for="group in groupedSupplierProducts" :key="group.key" class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
                    <!-- Group Header -->
                    <div class="bg-gray-800 px-4 py-3 border-b border-gray-700 flex items-center justify-between">
                      <div>
                        <h4 class="text-white font-semibold">{{ group.name }}</h4>
                        <div class="flex items-center gap-3 text-xs text-gray-400 mt-0.5">
                          <span>{{ group.category }}</span>
                          <span>•</span>
                          <span>{{ group.type }}</span>
                          <span>•</span>
                          <span class="text-indigo-400">{{ group.variants.length }} variant{{ group.variants.length > 1 ? 's' : '' }}</span>
                        </div>
                      </div>
                      <div class="text-sm text-gray-400">
                        Selected: <span class="text-white font-medium">{{ getGroupSelectedQty(group.key) }}</span>
                      </div>
                    </div>

                    <!-- Variant List with Images, Color Swatches, and Clickable Images -->
                    <div class="divide-y divide-gray-700/50">
                      <div
                        v-for="variant in group.variants"
                        :key="variant.id"
                        class="flex items-center justify-between p-3 transition"
                        :class="stockOf(variant) <= 0 ? 'bg-red-950/20 opacity-60' : 'hover:bg-gray-700/30'"
                      >
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                          <!-- Clickable Image -->
                          <div 
                            class="w-12 h-12 rounded-lg overflow-hidden bg-gray-700 shrink-0 border border-gray-600 cursor-pointer"
                            @click="openLightbox(variant.image_url)"
                          >
                            <img v-if="variant.image_url" :src="variant.image_url" class="w-full h-full object-cover" />
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-500 text-xs">No img</div>
                          </div>
                          <!-- Color Swatch -->
                          <div v-if="variant.color_code" class="w-4 h-4 rounded-full border border-gray-600 shrink-0" :style="{ backgroundColor: variant.color_code }"></div>
                          <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 flex-wrap">
                              <span class="text-sm text-white">{{ variant.size }}</span>
                              <span class="text-xs text-gray-400 font-mono">{{ variant.sku_code || 'No SKU' }}</span>
                              <span class="text-xs text-gray-500">Min: {{ variant.min_order || 1 }}</span>
                              <span v-if="variant.max_order" class="text-xs text-gray-500">Max: {{ variant.max_order }}</span>
                              <!-- What the supplier can actually promise right now. -->
                              <span
                                class="text-xs font-medium px-1.5 py-0.5 rounded"
                                :class="stockChipClass(variant)"
                              >{{ stockLabel(variant) }}</span>
                            </div>
                          </div>
                          <span class="text-sm font-bold text-indigo-400 shrink-0">₱{{ formatCurrency(variant.price) }}</span>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                          <button
                            type="button"
                            @click="updateCart(variant, -1)"
                            :disabled="getProductQty(variant.id) === 0"
                            class="w-8 h-8 rounded bg-gray-700 hover:bg-gray-600 text-white disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center transition"
                          >−</button>
                          <span class="w-8 text-center text-white font-medium">{{ getProductQty(variant.id) }}</span>
                          <button
                            type="button"
                            @click="updateCart(variant, 1)"
                            :disabled="!canAddProduct(variant)"
                            :title="stockOf(variant) > 0 ? '' : 'Out of stock'"
                            class="w-8 h-8 rounded bg-indigo-600 hover:bg-indigo-500 text-white disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center transition"
                          >+</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- STEP 3: Delivery & Logistics -->
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
                      <option v-if="selectedSupplierSettings.is_gcash_enabled" value="gcash">GCash</option>
                      <option v-if="selectedSupplierSettings.is_bank_enabled" value="bank">Bank Transfer</option>
                      <option v-if="!selectedSupplierSettings.is_cod_enabled && !selectedSupplierSettings.is_gcash_enabled && !selectedSupplierSettings.is_bank_enabled" value="" disabled>No Payment Methods Available</option>
                    </select>

                    <div v-if="calculatedCartTotal > 10000" class="mt-3 p-3 bg-blue-900/30 border border-blue-700/50 rounded-lg flex items-start gap-3 transition-all duration-300">
                      <div class="mt-0.5 text-blue-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                      </div>
                      <div>
                        <h4 class="text-sm font-semibold text-blue-300">Suggestion</h4>
                        <p class="text-xs text-blue-200 mt-1">
                          Since your total order cost is <strong class="text-white">₱{{ formatCurrency(calculatedCartTotal) }}</strong> (above ₱10,000), our system highly recommends using <strong>Bank Transfer</strong> for a safer and more reliable transaction limit.
                        </p>
                        <button v-if="selectedSupplierSettings.is_bank_enabled && requestForm.payment_terms !== 'bank'" type="button" @click="requestForm.payment_terms = 'bank'" class="mt-3 text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white px-3 py-1.5 rounded transition-colors shadow-sm">
                          Apply Bank Transfer
                        </button>
                        <p v-else-if="!selectedSupplierSettings.is_bank_enabled" class="text-xs text-red-300 mt-2 italic">
                          Note: The selected supplier does not currently support Bank Transfers.
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="wizard-form-group">
                  <label class="block text-sm text-gray-300 mb-2">Special Instructions</label>
                  <textarea v-model="requestForm.instructions" rows="2" placeholder="Any special requirements..." class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
                </div>
              </div>

              <!-- STEP 4: Review -->
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
                      <span class="text-gray-300">
                        {{ item.name }} ({{ item.size }}) x{{ item.quantity }}
                        <span class="text-xs text-gray-500">· {{ stockOf(item) }} in stock</span>
                      </span>
                      <span class="text-white">₱{{ formatCurrency(item.price * item.quantity) }}</span>
                    </div>
                    <div v-if="cart.length === 0" class="text-gray-400 text-sm">No items selected.</div>
                  </div>

                  <!--
                    Stock is reserved the instant any distributor raises a request,
                    so the figure captured when the supplier was picked can be
                    overtaken while this wizard is open. Say so here rather than
                    letting the submit fail.
                  -->
                  <div
                    v-if="stockShortfall.length > 0"
                    class="mb-6 p-3 rounded-lg bg-red-500/10 border border-red-500/30"
                  >
                    <p class="text-red-300 text-sm font-medium mb-1">Not enough stock to complete this request</p>
                    <p v-for="line in stockShortfall" :key="line" class="text-red-400/90 text-xs">{{ line }}</p>
                    <p class="text-gray-400 text-xs mt-2">Go back a step and lower the quantity, or pick a different product.</p>
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
          </form>
        </div>

        <!-- Fixed Footer -->
        <div class="p-6 border-t border-gray-800 bg-gray-900 shrink-0 flex justify-between items-center">
          <button type="button" @click="prevStep" :disabled="currentStep === 1" class="px-6 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 disabled:opacity-50 transition-colors">
            Previous
          </button>
          
          <button v-if="currentStep < wizardSteps.length" type="button" @click="nextStep" :disabled="!validateCurrentStep" class="px-6 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-500 text-white disabled:opacity-50 transition-opacity">
            Next
          </button>
          
          <button v-else type="submit" form="procurementForm" :disabled="submitting" class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:opacity-90 disabled:opacity-50 transition-opacity">
            {{ submitting ? 'Submitting...' : 'Submit Bulk Request' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Lightbox Overlay -->
    <div v-if="lightboxImage" class="fixed inset-0 z-[60] bg-black/90 flex items-center justify-center p-4" @click="closeLightbox">
      <img :src="lightboxImage" class="max-w-full max-h-full object-contain" @click.stop />
      <button @click="closeLightbox" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 transition">&times;</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import echo from '@/utils/websocket.js'

const route = useRoute()

// Navigation lives on the router instance. useRoute() only exposes the reactive
// route properties (path, query, params, ...), so route.replace is undefined --
// calling it here threw during setup and took the whole view down with it.
const router = useRouter()

// --- State ---
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

const availableBudget = ref(0)
const expandedGroups = ref([]) // for main list expansion
const lightboxImage = ref(null) // for full-size image preview

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

// --- WebSocket ---
const activeChannel = ref(null)

// --- Permissions ---
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// --- Computed ---
const selectedSupplierSettings = computed(() => {
  const supplier = suppliers.value.find(s => s.id === requestForm.value.supplier_id)
  return supplier?.payment_settings || { is_cod_enabled: true, is_gcash_enabled: false, is_bank_enabled: false }
})

const calculatedCartTotal = computed(() => {
  return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0)
})

// ── Stock limits ────────────────────────────────────────────────────
// A request can never take more than the supplier actually holds, so every
// quantity control here is bounded by that. The server enforces the same rule in
// `ProcurementController::store()`; this is so the operator finds out while
// choosing, not after filling in the whole form.

/**
 * Units the supplier can still sell of this product.
 *
 * `available_quantity` is the server's own figure and already accounts for
 * everything that should not be promised: expired lots, archived lots, and units
 * held against another distributor's in-flight request. It falls back to the raw
 * `quantity` only when the field is absent, so an older payload degrades to the
 * previous behaviour instead of blocking every line.
 */
const stockOf = (product) => {
  if (typeof product?.available_quantity === 'number') return product.available_quantity
  return Number(product?.quantity) || 0
}

/** The supplier's own per-order ceiling, or null when there is none. */
const orderCeilingOf = (product) => {
  const max = Number(product?.max_order)
  return Number.isFinite(max) && max > 0 ? max : null
}

/** Smallest order the supplier accepts. Always at least 1. */
const minOrderOf = (product) => Math.max(1, Number(product?.min_order) || 1)

/**
 * The most of this product that can go on a single request: the smaller of what
 * is in stock and the supplier's own max_order, whichever binds first.
 */
const purchasableLimitOf = (product) => {
  const stock = stockOf(product)
  const ceiling = orderCeilingOf(product)
  return ceiling === null ? stock : Math.min(ceiling, stock)
}

const stockLabel = (product) => {
  const stock = stockOf(product)
  if (stock <= 0) return 'Out of stock'
  return `${stock} in stock`
}

const stockChipClass = (product) => {
  const stock = stockOf(product)
  if (stock <= 0) return 'bg-red-500/20 text-red-300'
  if (stock <= 5) return 'bg-amber-500/20 text-amber-300'
  return 'bg-emerald-500/20 text-emerald-300'
}

/**
 * Cart lines that ask for more than is on the shelf.
 *
 * Normally empty, because the +/- controls stop at the limit. It exists because
 * the figure is only a snapshot: stock is reserved the moment any distributor
 * raises a request, so between picking a quantity and pressing submit another
 * request can eat into it. This is what stops the review step from presenting an
 * order that the server is going to reject.
 */
const stockShortfall = computed(() =>
  cart.value
    .filter((item) => item.quantity > stockOf(item))
    .map(
      (item) =>
        `${item.name} (${item.size}): ${item.quantity} requested but only ${stockOf(item)} in stock.`
    )
)

const canAddProduct = (product) => {
  const currentQty = getProductQty(product.id)
  const minOrder = minOrderOf(product)

  // A first add jumps straight to the minimum order quantity, so that is the
  // figure the ceiling has to be tested against -- not the single unit the
  // button nominally adds.
  const tentative = currentQty > 0 ? currentQty + 1 : minOrder

  if (tentative > purchasableLimitOf(product)) return false

  const costToAdd = parseFloat(product.price) * (currentQty > 0 ? 1 : minOrder)
  return calculatedCartTotal.value + costToAdd <= availableBudget.value
}

const validateCurrentStep = computed(() => {
  switch (currentStep.value) {
    case 1: return requestForm.value.supplier_id !== ''
    case 2: return cart.value.length > 0 && stockShortfall.value.length === 0
    case 3: return requestForm.value.delivery_address !== '' && requestForm.value.payment_terms !== ''
    case 4: return requestForm.value.priority !== '' && stockShortfall.value.length === 0
    default: return false
  }
})

// --- Grouping for Main List ---
const groupedRequests = computed(() => {
  const groups = {}
  requests.value.forEach(req => {
    const key = `${req.product_name}|${req.category}`
    if (!groups[key]) {
      groups[key] = {
        key,
        product_name: req.product_name,
        category: req.category,
        supplier: req.supplier,
        requests: [],
        total_quantity: 0,
        total_cost: 0
      }
    }
    groups[key].requests.push(req)
    groups[key].total_quantity += req.quantity
    groups[key].total_cost += parseFloat(req.total_cost)
  })
  return Object.values(groups).sort((a, b) => a.product_name.localeCompare(b.product_name))
})

// --- Grouping for Modal Product Selection (by name + category) ---
const groupedSupplierProducts = computed(() => {
  const groups = {}
  supplierProducts.value.forEach(prod => {
    const key = `${prod.name}|${prod.category}`
    if (!groups[key]) {
      groups[key] = {
        key,
        name: prod.name,
        category: prod.category,
        type: prod.type,
        variants: []
      }
    }
    groups[key].variants.push(prod)
  })
  return Object.values(groups).sort((a, b) => a.name.localeCompare(b.name))
})

// --- Helper to get total quantity selected for a group (for display) ---
const getGroupSelectedQty = (groupKey) => {
  const group = groupedSupplierProducts.value.find(g => g.key === groupKey)
  if (!group) return 0
  return group.variants.reduce((sum, v) => sum + getProductQty(v.id), 0)
}

// --- Lightbox helpers ---
const openLightbox = (imageUrl) => {
  if (imageUrl) {
    lightboxImage.value = imageUrl
  }
}
const closeLightbox = () => {
  lightboxImage.value = null
}

// --- Other Methods ---
const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied: You do not have permission to ${action} procurement requests.`);
    return;
  }
  if (callback) callback();
}

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

// --- WebSocket ---
const setupWebsocket = (distributorId) => {
  if (activeChannel.value) return;
  let channelName = distributorId === null ? 'admin.procurement' : `distributor.${distributorId}.procurement`;
  activeChannel.value = channelName;
  echo.private(channelName)
    .listen('.procurement.created', (e) => {
        showToast('A new procurement request has been detected!', 'info');
        fetchRequests(true);
        fetchStatistics();
    });
}

// --- Data Fetching ---
const fetchRequests = async (silent = false) => {
  try {
    if (!silent) loading.value = true
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
      if (!activeChannel.value) {
          setupWebsocket(response.data.distributor_id);
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
      availableBudget.value = response.data.data.available_budget || 0;
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
    if (err.response?.status === 403) {
      submitError.value = err.response.data.message || 'Supplier restricted.';
      showToast('Cannot fetch products from restricted supplier.', 'error');
    } else {
      showToast('Failed to fetch supplier products', 'error');
    }
  } finally {
    productsLoading.value = false
  }
}

// --- UI Helpers ---
const toggleGroup = (key) => {
  const index = expandedGroups.value.indexOf(key)
  if (index > -1) {
    expandedGroups.value.splice(index, 1)
  } else {
    expandedGroups.value.push(key)
  }
}

/**
 * Pre-select a payment term the supplier actually accepts.
 *
 * Left blank when none are on file, so step 3 makes the user pick rather than
 * quietly defaulting to a term that may not be available.
 */
const applyPaymentTerms = (supplier) => {
  const settings = supplier.payment_settings
  if (settings?.is_cod_enabled) requestForm.value.payment_terms = 'cod'
  else if (settings?.is_gcash_enabled) requestForm.value.payment_terms = 'gcash'
  else if (settings?.is_bank_enabled) requestForm.value.payment_terms = 'bank'
  else requestForm.value.payment_terms = ''
}

const selectSupplierFromWizard = (supplier) => {
  requestForm.value.supplier_id = supplier.id
  requestForm.value.supplier = supplier.name
  applyPaymentTerms(supplier)
  fetchSupplierProducts(supplier.id)
}

/**
 * Open the wizard already aimed at one distributor product.
 *
 * Arrives from the DSS "Request Procurement" shortcut as a `?procure=<productId>`
 * query. Which partner supplier can supply the product, and on what terms, is
 * resolved by the server -- the distributor and supplier catalogues are separate
 * and the two have to be matched, so that rule lives in one place rather than
 * being reimplemented here.
 *
 * Anything that cannot be resolved deliberately leaves the user on step 1 with an
 * explanation, rather than opening a wizard they have to unpick.
 */
const applyProcurePrefill = async (productId) => {
  showRequestModal.value = true
  await fetchFormOptions()

  let resolution
  try {
    const response = await api.get(`/procurement/suppliers-for-product/${productId}`)
    if (response.data.success) resolution = response.data.data
  } catch (err) {
    submitError.value = err?.response?.data?.message || 'Could not look up which suppliers carry this product.'
    showToast('Could not look up which suppliers carry this product.', 'error')
    return
  }

  const productName = resolution?.product?.name

  if (!resolution?.matches?.length) {
    showToast(
      productName
        ? `No partner supplier currently lists ${productName}. Select a supplier to browse their catalogue.`
        : 'No partner supplier currently lists this product. Select a supplier to browse their catalogue.',
      'warning'
    )
    return
  }

  // Already sorted server-side: best evidence of a match first, then whoever has
  // the most to sell.
  const match = resolution.matches[0]
  const supplier = suppliers.value.find((s) => s.id === match.supplier_id)

  if (!supplier) {
    showToast(`${match.supplier_name} is not in the selectable partner list. Select a supplier manually.`, 'warning')
    return
  }

  requestForm.value.supplier_id = supplier.id
  requestForm.value.supplier = supplier.name
  applyPaymentTerms(supplier)

  await fetchSupplierProducts(supplier.id)

  // Take the product from the loaded catalogue rather than from the resolver's
  // copy of it, so the queued line carries the same price and the same live stock
  // figure the list renders and the +/- controls validate against.
  const product = supplierProducts.value.find((p) => p.id === match.raw_material_id)

  if (!product) {
    showToast(`${match.raw_material_name} is no longer in this supplier's catalogue.`, 'warning')
    return
  }

  if (stockOf(product) <= 0) {
    showToast(
      `${supplier.name} has no unexpired ${match.raw_material_name} left to procure. Select a supplier to browse their catalogue.`,
      'warning'
    )
    return
  }

  // A first add lands the supplier's own minimum order and refuses, with the
  // reason, when the shelf cannot cover it. Going through updateCart keeps the
  // automatic path and the manual one on identical rules.
  updateCart(product, 1)

  if (cart.value.length > 0) {
    wizardSteps.value[0].completed = true
    currentStep.value = 2

    if (match.match_tier === 2) {
      showToast(`Matched on product name only -- check the size before submitting.`, 'warning')
    }
  }
}

// The ?procure watcher is registered at the end of this block, after every
// function it can reach is declared.

const updateCart = (product, change) => {
  const index = cart.value.findIndex(p => p.id === product.id)
  const currentQty = index > -1 ? cart.value[index].quantity : 0
  const minOrder = minOrderOf(product)

  // Dropping below the minimum order takes the whole line out, as before.
  if (currentQty > 0 && currentQty + change < minOrder) {
    cart.value.splice(index, 1)
    return
  }

  if (change > 0) {
    const stock = stockOf(product)

    if (stock <= 0) {
      showToast(`${product.name} is out of stock and cannot be procured.`, 'warning')
      return
    }

    // The ceiling binds on the quantity the line would END UP at, which for a
    // first add is the minimum order rather than the one unit clicked.
    const tentative = currentQty > 0 ? currentQty + change : minOrder
    const ceiling = orderCeilingOf(product)
    const limit = ceiling === null ? stock : Math.min(ceiling, stock)

    if (tentative > limit) {
      // A minimum order the shelf cannot satisfy is its own situation. Reporting
      // only the stock figure ("only 10 in stock") leaves the operator staring at
      // a number that does not explain why a single click placed nothing.
      if (currentQty === 0 && minOrder > limit) {
        showToast(
          `Minimum order for ${product.name} is ${minOrder}, but only ${stock} unit(s) are in stock.`,
          'warning'
        )
        return
      }

      // Otherwise say which limit actually stopped it -- "in stock" and "max
      // order" call for completely different fixes.
      showToast(
        limit === stock
          ? `Only ${stock} unit(s) of ${product.name} are in stock.`
          : `Maximum order limit for ${product.name} is ${limit}.`,
        'warning'
      )
      return
    }

    const costToAdd = parseFloat(product.price) * (currentQty > 0 ? change : minOrder)
    if (calculatedCartTotal.value + costToAdd > availableBudget.value) {
      showToast(
        `Cannot add ${product.name}. Cart total would exceed the allocated business budget.`,
        'warning'
      )
      return
    }
  }

  if (index > -1) {
    cart.value[index].quantity = currentQty + change
  } else {
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

/**
 * The server rejects an over-order with a 422 whose `message` is the useless
 * string "Validation failed" and whose real explanation lives in `errors.items`.
 * Reading only `message` is how a distributor ends up staring at "Validation
 * failed" instead of "Only 9 unit(s) of Amber Diaz can be procured right now".
 */
const readSubmitError = (err) => {
  const data = err?.response?.data
  if (!data) return 'Failed to submit'

  const fieldErrors = Object.values(data.errors || {}).flat().filter(Boolean)
  if (fieldErrors.length) return fieldErrors.join(' ')

  return data.message || 'Failed to submit'
}

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
    submitError.value = readSubmitError(err)
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

// Consume ?procure=<id> from the DSS "Request Procurement" shortcut.
//
// Registered last, and deliberately: `immediate: true` runs the callback
// synchronously during setup, so anything applyProcurePrefill touches must already
// be initialised. Declaring this next to that function left it running against a
// half-built component -- it happened to work only because applyProcurePrefill
// suspends at its first await before reaching updateCart.
//
// `immediate: true` covers arriving from another view; the watcher itself covers
// re-navigating to the same route, which does not remount the component.
watch(
  () => route.query?.procure,
  (value) => {
    const productId = Number(value)
    if (!Number.isInteger(productId) || productId <= 0) return

    // Clear the query before doing any work: a browser refresh should not silently
    // re-open a wizard, and coming back must not replay it.
    router.replace({ path: route.path, query: {} })

    applyProcurePrefill(productId)
  },
  { immediate: true }
)

// --- Lifecycle ---
onMounted(() => {
  fetchRequests()
  fetchStatistics()
  debouncedFetchRequests = debounce(fetchRequests, 500)
})

onUnmounted(() => {
  if (activeChannel.value) {
    echo.leave(activeChannel.value);
  }
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