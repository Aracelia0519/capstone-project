<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 md:w-8 md:h-8 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-7.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
            Clients
          </h1>
          <p class="text-gray-600 mt-2">Manage client accounts and track their activities</p>
        </div>
        <button class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg flex items-center transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add New Client
        </button>
      </div>
    </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-xl shadow p-4 md:p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" placeholder="Search by name, email, or phone..." 
                   class="w-full pl-10 pr-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>
        <div>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Locations</option>
            <option value="ny">New York</option>
            <option value="la">Los Angeles</option>
            <option value="chi">Chicago</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Clients Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="py-4 px-4 md:px-6 text-left">
                <div class="flex items-center">
                  <input type="checkbox" class="rounded text-blue-600">
                  <span class="ml-3 font-semibold text-gray-700">Name</span>
                </div>
              </th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Contact</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Status</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Orders</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Last Interaction</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="client in clients" :key="client.id" class="hover:bg-gray-50">
              <td class="py-4 px-4 md:px-6">
                <div class="flex items-center">
                  <input type="checkbox" class="rounded text-blue-600">
                  <div class="ml-3">
                    <div class="flex items-center">
                      <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                        <span class="text-blue-600 font-semibold">{{ client.name.charAt(0) }}</span>
                      </div>
                      <div>
                        <div class="font-medium text-gray-900">{{ client.name }}</div>
                        <div class="text-sm text-gray-500">ID: {{ client.id }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="text-sm">
                  <div class="text-gray-900">{{ client.email }}</div>
                  <div class="text-gray-500">{{ client.phone }}</div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <span :class="`px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(client.status)}`">
                  {{ client.status }}
                </span>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="text-gray-900 font-medium">{{ client.orders }}</div>
                <div class="text-sm text-gray-500">${{ client.value.toLocaleString() }}</div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="text-sm">
                  <div class="text-gray-900">{{ client.lastInteraction }}</div>
                  <div class="text-gray-500">{{ client.daysAgo }} days ago</div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex space-x-2">
                  <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
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
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Client Profile Modal -->
    <div v-if="selectedClient" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <!-- Modal Header -->
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Client Profile</h2>
            <button @click="selectedClient = null" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Profile Content -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="md:col-span-1">
              <div class="bg-gray-50 rounded-xl p-6">
                <div class="flex flex-col items-center">
                  <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                    <span class="text-3xl text-blue-600 font-bold">{{ selectedClient.name.charAt(0) }}</span>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">{{ selectedClient.name }}</h3>
                  <div class="text-gray-600 mt-1">{{ selectedClient.company }}</div>
                  <div :class="`mt-4 px-4 py-1 rounded-full text-sm font-medium ${getStatusClass(selectedClient.status)}`">
                    {{ selectedClient.status }}
                  </div>
                </div>

                <div class="mt-6 space-y-4">
                  <div class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ selectedClient.email }}
                  </div>
                  <div class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    {{ selectedClient.phone }}
                  </div>
                  <div class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ selectedClient.location }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="md:col-span-2">
              <!-- Purchase History -->
              <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Purchase History</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="space-y-3">
                    <div v-for="purchase in selectedClient.purchases" :key="purchase.id" 
                         class="flex justify-between items-center p-3 bg-white rounded-lg">
                      <div>
                        <div class="font-medium">{{ purchase.product }}</div>
                        <div class="text-sm text-gray-500">{{ purchase.date }}</div>
                      </div>
                      <div class="text-right">
                        <div class="font-bold text-gray-800">${{ purchase.amount }}</div>
                        <div :class="`text-xs px-2 py-1 rounded ${purchase.status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`">
                          {{ purchase.status }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Notes & Interactions</h3>
                <div class="space-y-4">
                  <textarea class="w-full border rounded-lg p-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            rows="4" placeholder="Add a note about this client..."></textarea>
                  <div class="flex justify-end">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                      Add Note
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script>
export default {
  name: 'Clients',
  data() {
    return {
      selectedClient: null,
      clients: [
        { id: 'CL-001', name: 'John Smith', email: 'john@example.com', phone: '+1 (555) 123-4567', company: 'Tech Corp', status: 'Active', orders: 12, value: 24500, lastInteraction: 'Mar 15, 2024', daysAgo: 2, location: 'New York', purchases: [
          { id: 1, product: 'Premium Package', amount: '2,500', date: 'Mar 10, 2024', status: 'Completed' },
          { id: 2, product: 'Basic Package', amount: '1,200', date: 'Feb 28, 2024', status: 'Completed' }
        ]},
        { id: 'CL-002', name: 'Sarah Johnson', email: 'sarah@example.com', phone: '+1 (555) 987-6543', company: 'Design Studio', status: 'Pending', orders: 5, value: 8900, lastInteraction: 'Mar 14, 2024', daysAgo: 3, location: 'Los Angeles', purchases: [
          { id: 1, product: 'Design Package', amount: '3,500', date: 'Mar 5, 2024', status: 'Pending' }
        ]},
        { id: 'CL-003', name: 'Mike Wilson', email: 'mike@example.com', phone: '+1 (555) 456-7890', company: 'Marketing Pro', status: 'Active', orders: 8, value: 15600, lastInteraction: 'Mar 12, 2024', daysAgo: 5, location: 'Chicago', purchases: [
          { id: 1, product: 'Marketing Suite', amount: '4,800', date: 'Mar 1, 2024', status: 'Completed' }
        ]}
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
    viewProfile(client) {
      this.selectedClient = client
    }
  }
}
</script>

<style scoped>
/* Custom modal animation */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Table row hover effect */
tr {
  transition: background-color 0.2s ease;
}

/* Custom checkbox */
input[type="checkbox"] {
  width: 18px;
  height: 18px;
}

/* Scrollbar for modal */
.modal-content {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.modal-content::-webkit-scrollbar {
  width: 8px;
}

.modal-content::-webkit-scrollbar-track {
  background: #f7fafc;
  border-radius: 4px;
}

.modal-content::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 4px;
}
</style>