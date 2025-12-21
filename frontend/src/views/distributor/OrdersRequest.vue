<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Orders & Requests</h1>
          <p class="text-gray-600 mt-2">Manage incoming paint distribution requests</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Filter Dropdown -->
          <div class="relative">
            <select v-model="statusFilter" 
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer">
              <option value="all">All Status</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="fulfilled">Fulfilled</option>
              <option value="rejected">Rejected</option>
            </select>
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
              </svg>
            </div>
          </div>

          <!-- Search -->
          <div class="relative">
            <input type="text" 
                   v-model="searchQuery"
                   placeholder="Search requests..." 
                   class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Total Requests</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ totalRequests }}</p>
            </div>
            <div class="p-2 bg-blue-50 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Pending</p>
              <p class="text-2xl font-bold text-yellow-600 mt-1">{{ pendingCount }}</p>
            </div>
            <div class="p-2 bg-yellow-50 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Approved</p>
              <p class="text-2xl font-bold text-green-600 mt-1">{{ approvedCount }}</p>
            </div>
            <div class="p-2 bg-green-50 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-600">Fulfilled</p>
              <p class="text-2xl font-bold text-purple-600 mt-1">{{ fulfilledCount }}</p>
            </div>
            <div class="p-2 bg-purple-50 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <h2 class="text-lg font-semibold text-gray-800">Incoming Requests</h2>
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-600">{{ filteredRequests.length }} requests</span>
            <button @click="refreshData" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile View -->
      <div class="md:hidden">
        <div v-for="request in filteredRequests" :key="request.id" class="p-4 border-b border-gray-200">
          <div class="flex justify-between items-start mb-3">
            <div>
              <span class="font-medium text-gray-900">#{{ request.id }}</span>
              <span :class="statusClasses(request.status)" class="ml-2 px-2 py-1 rounded-full text-xs font-medium">
                {{ request.status }}
              </span>
            </div>
            <div class="text-right">
              <p class="font-semibold text-gray-900">₱{{ formatCurrency(request.totalAmount) }}</p>
              <p class="text-sm text-gray-500">{{ request.date }}</p>
            </div>
          </div>

          <div class="space-y-3">
            <div>
              <p class="text-sm text-gray-600">Service Provider</p>
              <p class="font-medium text-gray-900">{{ request.serviceProvider }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Client</p>
              <p class="font-medium text-gray-900">{{ request.clientName }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Paint Requested</p>
              <div class="mt-1">
                <div class="flex items-center gap-2">
                  <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: request.paintColor }"></div>
                  <span class="font-medium text-gray-900">{{ request.paintType }}</span>
                  <span class="text-gray-600">({{ request.quantity }} gallons)</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">{{ request.paintBrand }} • {{ request.paintFinish }}</p>
              </div>
            </div>
          </div>

          <!-- Mobile Actions -->
          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="flex justify-between gap-2">
              <button v-if="request.status === 'pending'" 
                      @click="approveRequest(request.id)"
                      class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Approve
              </button>
              <button v-if="request.status === 'pending'" 
                      @click="rejectRequest(request.id)"
                      class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-red-50 text-red-700 hover:bg-red-100 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reject
              </button>
              <button v-if="request.status === 'approved'" 
                      @click="markAsFulfilled(request.id)"
                      class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Fulfill
              </button>
              <button @click="viewDetails(request)"
                      class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-gray-50 text-gray-700 hover:bg-gray-100 rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Details
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Request ID</th>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Service Provider</th>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Client</th>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Paint Details</th>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Quantity</th>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Status</th>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
              <th class="py-3 px-6 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="request in filteredRequests" :key="request.id" class="hover:bg-gray-50 transition-colors">
              <td class="py-4 px-6">
                <div class="font-medium text-gray-900">#{{ request.id }}</div>
                <div class="text-sm text-gray-500">₱{{ formatCurrency(request.totalAmount) }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ request.serviceProvider }}</div>
                    <div class="text-sm text-gray-500">{{ request.providerContact }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="font-medium text-gray-900">{{ request.clientName }}</div>
                <div class="text-sm text-gray-500">{{ request.clientLocation }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg" :style="{ backgroundColor: request.paintColor }"></div>
                  <div>
                    <div class="font-medium text-gray-900">{{ request.paintType }}</div>
                    <div class="text-sm text-gray-500">{{ request.paintBrand }} • {{ request.paintFinish }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="font-medium text-gray-900">{{ request.quantity }} gallons</div>
                <div class="text-sm text-gray-500">{{ request.weight }} kg total</div>
              </td>
              <td class="py-4 px-6">
                <span :class="statusClasses(request.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                  <span class="w-2 h-2 rounded-full mr-2" :class="statusDotClasses(request.status)"></span>
                  {{ request.status }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="text-gray-900">{{ request.date }}</div>
                <div class="text-sm text-gray-500">{{ request.time }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <button v-if="request.status === 'pending'" 
                          @click="approveRequest(request.id)"
                          class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="Approve Request">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                  </button>
                  <button v-if="request.status === 'pending'" 
                          @click="rejectRequest(request.id)"
                          class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                          title="Reject Request">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </button>
                  <button v-if="request.status === 'approved'" 
                          @click="markAsFulfilled(request.id)"
                          class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                          title="Mark as Fulfilled">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </button>
                  <button @click="viewDetails(request)"
                          class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                          title="View Details">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredRequests.length === 0" class="text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 text-gray-400">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No requests found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Try adjusting your search or filter to find what you're looking for.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="filteredRequests.length > 0" class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        Showing <span class="font-medium">{{ Math.min((currentPage - 1) * itemsPerPage + 1, filteredRequests.length) }}</span>
        to <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredRequests.length) }}</span>
        of <span class="font-medium">{{ filteredRequests.length }}</span> requests
      </div>
      <div class="flex items-center gap-2">
        <button @click="prevPage" :disabled="currentPage === 1" 
                class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg font-medium">{{ currentPage }}</span>
        <button @click="nextPage" :disabled="currentPage * itemsPerPage >= filteredRequests.length" 
                class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OrdersRequests',
  data() {
    return {
      searchQuery: '',
      statusFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      orders: [
        {
          id: 'REQ-00124',
          serviceProvider: 'Cavite Paint Services',
          providerContact: '0917-123-4567',
          clientName: 'Juan Dela Cruz',
          clientLocation: 'Imus, Cavite',
          paintType: 'Premium White Latex',
          paintBrand: 'Boysen',
          paintFinish: 'Satin',
          paintColor: '#F8FAFC',
          quantity: 15,
          weight: 68,
          status: 'pending',
          totalAmount: 24500,
          date: '2024-01-15',
          time: '10:30 AM',
          priority: 'high'
        },
        {
          id: 'REQ-00123',
          serviceProvider: 'Imus Paint Center',
          providerContact: '0918-234-5678',
          clientName: 'Maria Santos',
          clientLocation: 'Bacoor, Cavite',
          paintType: 'Weatherproof Exterior',
          paintBrand: 'Davies',
          paintFinish: 'Gloss',
          paintColor: '#3B82F6',
          quantity: 8,
          weight: 36,
          status: 'approved',
          totalAmount: 15600,
          date: '2024-01-14',
          time: '02:15 PM',
          priority: 'medium'
        },
        {
          id: 'REQ-00122',
          serviceProvider: 'Bacoor Construction',
          providerContact: '0919-345-6789',
          clientName: 'Robert Lim Construction',
          clientLocation: 'Dasmarinas, Cavite',
          paintType: 'Anti-Mold Bathroom',
          paintBrand: 'Island',
          paintFinish: 'Semi-Gloss',
          paintColor: '#10B981',
          quantity: 25,
          weight: 113,
          status: 'fulfilled',
          totalAmount: 42150,
          date: '2024-01-13',
          time: '09:45 AM',
          priority: 'high'
        },
        {
          id: 'REQ-00121',
          serviceProvider: 'Dasmarinas Painters',
          providerContact: '0920-456-7890',
          clientName: 'Sarah Gomez',
          clientLocation: 'Trece Martires',
          paintType: 'Quick Dry Enamel',
          paintBrand: 'Toyo',
          paintFinish: 'Matte',
          paintColor: '#EF4444',
          quantity: 5,
          weight: 23,
          status: 'pending',
          totalAmount: 8900,
          date: '2024-01-12',
          time: '04:20 PM',
          priority: 'low'
        },
        {
          id: 'REQ-00120',
          serviceProvider: 'Trece Martires Co.',
          providerContact: '0921-567-8901',
          clientName: 'James Wilson',
          clientLocation: 'General Trias',
          paintType: 'Eco-Friendly Interior',
          paintBrand: 'Boysen',
          paintFinish: 'Flat',
          paintColor: '#8B5CF6',
          quantity: 12,
          weight: 54,
          status: 'rejected',
          totalAmount: 18700,
          date: '2024-01-11',
          time: '11:00 AM',
          priority: 'medium'
        },
        {
          id: 'REQ-00119',
          serviceProvider: 'General Trias Paint',
          providerContact: '0922-678-9012',
          clientName: 'Andrea Torres',
          clientLocation: 'Silang, Cavite',
          paintType: 'Heat Reflective Roof',
          paintBrand: 'Davies',
          paintFinish: 'Textured',
          paintColor: '#F59E0B',
          quantity: 18,
          weight: 81,
          status: 'approved',
          totalAmount: 32400,
          date: '2024-01-10',
          time: '03:45 PM',
          priority: 'high'
        },
        {
          id: 'REQ-00118',
          serviceProvider: 'Silang Hardware',
          providerContact: '0923-789-0123',
          clientName: 'Michael Tan',
          clientLocation: 'Tagaytay City',
          paintType: 'Marine Grade',
          paintBrand: 'Island',
          paintFinish: 'High Gloss',
          paintColor: '#06B6D4',
          quantity: 7,
          weight: 32,
          status: 'fulfilled',
          totalAmount: 13450,
          date: '2024-01-09',
          time: '01:30 PM',
          priority: 'medium'
        }
      ]
    }
  },
  computed: {
    filteredRequests() {
      let filtered = this.orders

      // Apply status filter
      if (this.statusFilter !== 'all') {
        filtered = filtered.filter(request => 
          request.status.toLowerCase() === this.statusFilter.toLowerCase()
        )
      }

      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(request =>
          request.id.toLowerCase().includes(query) ||
          request.serviceProvider.toLowerCase().includes(query) ||
          request.clientName.toLowerCase().includes(query) ||
          request.paintType.toLowerCase().includes(query)
        )
      }

      return filtered
    },
    paginatedRequests() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredRequests.slice(start, end)
    },
    totalRequests() {
      return this.orders.length
    },
    pendingCount() {
      return this.orders.filter(r => r.status === 'pending').length
    },
    approvedCount() {
      return this.orders.filter(r => r.status === 'approved').length
    },
    fulfilledCount() {
      return this.orders.filter(r => r.status === 'fulfilled').length
    }
  },
  methods: {
    statusClasses(status) {
      const classes = {
        'pending': 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'approved': 'bg-green-50 text-green-700 border border-green-200',
        'fulfilled': 'bg-purple-50 text-purple-700 border border-purple-200',
        'rejected': 'bg-red-50 text-red-700 border border-red-200'
      }
      return classes[status] || 'bg-gray-50 text-gray-700 border border-gray-200'
    },
    statusDotClasses(status) {
      const classes = {
        'pending': 'bg-yellow-500',
        'approved': 'bg-green-500',
        'fulfilled': 'bg-purple-500',
        'rejected': 'bg-red-500'
      }
      return classes[status] || 'bg-gray-500'
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH').format(amount)
    },
    approveRequest(id) {
      const request = this.orders.find(r => r.id === id)
      if (request && request.status === 'pending') {
        request.status = 'approved'
        this.showNotification(`Request ${id} approved successfully`, 'success')
      }
    },
    rejectRequest(id) {
      const request = this.orders.find(r => r.id === id)
      if (request && request.status === 'pending') {
        request.status = 'rejected'
        this.showNotification(`Request ${id} rejected`, 'warning')
      }
    },
    markAsFulfilled(id) {
      const request = this.orders.find(r => r.id === id)
      if (request && request.status === 'approved') {
        request.status = 'fulfilled'
        this.showNotification(`Request ${id} marked as fulfilled`, 'success')
      }
    },
    viewDetails(request) {
      this.showNotification(`Viewing details for ${request.id}`, 'info')
      // In a real app, this would open a modal or navigate to details page
      console.log('Request details:', request)
    },
    refreshData() {
      this.showNotification('Refreshing data...', 'info')
      // In a real app, this would fetch fresh data from API
    },
    nextPage() {
      if (this.currentPage * this.itemsPerPage < this.filteredRequests.length) {
        this.currentPage++
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--
      }
    },
    showNotification(message, type = 'info') {
      // This would be connected to a notification system
      console.log(`${type.toUpperCase()}: ${message}`)
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for better mobile experience */
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
  
  .overflow-x-auto::-webkit-scrollbar {
    height: 4px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
  }
}

/* Smooth transitions for interactive elements */
button, select, input {
  transition: all 0.2s ease-in-out;
}

/* Hover effects for table rows */
.hover\\:bg-gray-50:hover {
  transition: background-color 0.2s ease;
}

/* Custom focus styles */
select:focus, input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Responsive table adjustments */
@media (max-width: 767px) {
  :deep(.text-2xl) {
    font-size: 1.5rem;
  }
  
  :deep(.text-3xl) {
    font-size: 1.75rem;
  }
}

/* Animation for status changes */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

tbody tr {
  animation: fadeIn 0.3s ease-out;
}

/* Custom color dots for paint samples */
.w-4.h-4.rounded-full {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.w-8.h-8.rounded-lg {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>