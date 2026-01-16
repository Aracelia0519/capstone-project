<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 md:w-8 md:h-8 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Distributors
          </h1>
          <p class="text-gray-600 mt-2">Manage distributor accounts and monitor their performance</p>
        </div>
        <button class="mt-4 md:mt-0 bg-green-600 hover:bg-green-700 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg flex items-center transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add New Distributor
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Distributors</p>
            <h3 class="text-2xl font-bold text-gray-800">56</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Orders This Month</p>
            <h3 class="text-2xl font-bold text-gray-800">248</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-yellow-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Pending Requirements</p>
            <h3 class="text-2xl font-bold text-gray-800">12</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-purple-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Verified</p>
            <h3 class="text-2xl font-bold text-gray-800">44</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Distributors Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="p-4 md:p-6 border-b">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <div class="relative">
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input type="text" placeholder="Search distributors..." 
                     class="w-full pl-10 pr-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
          </div>
          <div>
            <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="pending">Pending</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div>
            <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
              <option value="">Sort By</option>
              <option value="orders">Orders Supplied</option>
              <option value="recent">Most Recent</option>
              <option value="name">Name</option>
            </select>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Distributor</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Business Details</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Performance</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Status</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="distributor in distributors" :key="distributor.id" class="hover:bg-gray-50">
              <td class="py-4 px-4 md:px-6">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center mr-3">
                    <span class="text-green-600 font-semibold">{{ distributor.name.charAt(0) }}</span>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ distributor.name }}</div>
                    <div class="text-sm text-gray-500">{{ distributor.email }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="text-sm">
                  <div class="font-medium">{{ distributor.businessName }}</div>
                  <div class="text-gray-500">{{ distributor.location }}</div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div>
                  <div class="font-medium text-gray-900">{{ distributor.ordersSupplied }} orders</div>
                  <div class="text-sm text-gray-500">Last: {{ distributor.lastOrder }}</div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex flex-col space-y-2">
                  <span :class="`px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(distributor.status)}`">
                    {{ distributor.status }}
                  </span>
                  <span :class="`px-3 py-1 rounded-full text-xs font-medium ${getVerificationClass(distributor.verification)}`">
                    {{ distributor.verification }}
                  </span>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex space-x-2">
                  <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" @click="viewProfile(distributor)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                  <button class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script>
export default {
  name: 'Distributors',
  data() {
    return {
      distributors: [
        { id: 'D-001', name: 'Robert Chen', email: 'robert@supplychain.com', phone: '+1 (555) 234-5678', businessName: 'Global Supply Inc.', location: 'New York, NY', status: 'Active', verification: 'Verified', ordersSupplied: 156, lastOrder: 'Mar 14, 2024', documents: ['Business License', 'Tax ID', 'Insurance'] },
        { id: 'D-002', name: 'Maria Garcia', email: 'maria@distropro.com', phone: '+1 (555) 345-6789', businessName: 'Distro Pro Solutions', location: 'Miami, FL', status: 'Pending', verification: 'Pending Review', ordersSupplied: 89, lastOrder: 'Mar 10, 2024', documents: ['Business License'] },
        { id: 'D-003', name: 'James Wilson', email: 'james@logistics.com', phone: '+1 (555) 456-7890', businessName: 'Wilson Logistics', location: 'Chicago, IL', status: 'Active', verification: 'Verified', ordersSupplied: 203, lastOrder: 'Mar 15, 2024', documents: ['Business License', 'Tax ID', 'Insurance', 'Permit'] }
      ]
    }
  },
  methods: {
    getStatusClass(status) {
      const classes = {
        'Active': 'bg-green-100 text-green-800',
        'Pending': 'bg-yellow-100 text-yellow-800',
        'Inactive': 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
    getVerificationClass(verification) {
      const classes = {
        'Verified': 'bg-blue-100 text-blue-800',
        'Pending Review': 'bg-yellow-100 text-yellow-800',
        'Not Verified': 'bg-red-100 text-red-800'
      }
      return classes[verification] || 'bg-gray-100 text-gray-800'
    },
    viewProfile(distributor) {
      // Implement profile view
      console.log('View profile:', distributor)
    }
  }
}
</script>

<style scoped>
/* Custom hover effects */
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions for status badges */
.status-badge {
  transition: all 0.2s ease;
}

/* Custom table styles */
table {
  border-collapse: separate;
  border-spacing: 0;
}

th {
  position: sticky;
  top: 0;
  background-color: #f9fafb;
  z-index: 10;
}

/* Custom scrollbar for table */
.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: #d1d5db #f3f4f6;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f3f4f6;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background-color: #d1d5db;
  border-radius: 3px;
}
</style>