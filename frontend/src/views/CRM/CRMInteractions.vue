<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 md:w-8 md:h-8 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            Interactions
          </h1>
          <p class="text-gray-600 mt-2">Log communications with all system users</p>
        </div>
        <button class="mt-4 md:mt-0 bg-orange-600 hover:bg-orange-700 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg flex items-center transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Log New Interaction
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow p-4 md:p-6 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" placeholder="Search interactions..." 
                   class="w-full pl-10 pr-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
          </div>
        </div>
        <div>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            <option value="">All Types</option>
            <option value="email">Email</option>
            <option value="call">Call</option>
            <option value="meeting">Meeting</option>
            <option value="support">Support Ticket</option>
          </select>
        </div>
        <div>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
            <option value="">All Users</option>
            <option value="client">Clients</option>
            <option value="distributor">Distributors</option>
            <option value="provider">Service Providers</option>
          </select>
        </div>
        <div>
          <input type="date" class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
        </div>
      </div>
    </div>

    <!-- Interactions Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Date / Time</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">User</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Type</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Summary</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Attachments</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="interaction in interactions" :key="interaction.id" class="hover:bg-gray-50">
              <td class="py-4 px-4 md:px-6">
                <div class="text-sm">
                  <div class="font-medium text-gray-900">{{ interaction.date }}</div>
                  <div class="text-gray-500">{{ interaction.time }}</div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex items-center">
                  <div :class="`w-8 h-8 rounded-full flex items-center justify-center mr-3 ${getUserBg(interaction.userType)}`">
                    <span :class="getUserText(interaction.userType)">{{ interaction.user.charAt(0) }}</span>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ interaction.user }}</div>
                    <div class="text-xs text-gray-500">{{ interaction.userType }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex items-center">
                  <div :class="`p-2 rounded-lg mr-3 ${getTypeBg(interaction.type)}`">
                    <svg :class="`w-4 h-4 ${getTypeIcon(interaction.type)}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="interaction.type === 'Email'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                      <path v-else-if="interaction.type === 'Call'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                      <path v-else-if="interaction.type === 'Meeting'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <span class="font-medium">{{ interaction.type }}</span>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="text-sm">
                  <div class="text-gray-900 font-medium line-clamp-2">{{ interaction.summary }}</div>
                  <div v-if="interaction.followUp" class="mt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800">
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      Follow-up needed
                    </span>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div v-if="interaction.attachments" class="flex space-x-2">
                  <div v-for="(attachment, idx) in interaction.attachments" :key="idx" 
                       class="flex items-center px-3 py-1 bg-gray-100 rounded-lg text-xs text-gray-600">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    {{ attachment }}
                  </div>
                </div>
                <div v-else class="text-gray-400 text-sm">None</div>
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

    <!-- New Interaction Modal -->
    <div v-if="showNewInteraction" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <!-- Modal Header -->
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Log New Interaction</h2>
            <button @click="showNewInteraction = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Form -->
          <div class="space-y-6">
            <!-- User Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
              <select class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <option value="">Choose a user...</option>
                <option value="client">John Smith (Client)</option>
                <option value="distributor">Robert Chen (Distributor)</option>
                <option value="provider">David Wilson (Service Provider)</option>
              </select>
            </div>

            <!-- Interaction Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Interaction Type</label>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <button v-for="type in interactionTypes" :key="type.name" 
                        :class="`flex flex-col items-center justify-center p-4 border rounded-lg transition-all ${selectedType === type.name ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300'}`"
                        @click="selectedType = type.name">
                  <div :class="`p-3 rounded-lg mb-2 ${type.bgColor}`">
                    <svg :class="`w-6 h-6 ${type.iconColor}`" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="type.icon"/>
                    </svg>
                  </div>
                  <span class="text-sm font-medium">{{ type.name }}</span>
                </button>
              </div>
            </div>

            <!-- Date and Time -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input type="date" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Time</label>
                <input type="time" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
              </div>
            </div>

            <!-- Summary -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Summary / Notes</label>
              <textarea class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500" 
                        rows="4" placeholder="Enter details about the interaction..."></textarea>
            </div>

            <!-- Attachments -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Attachments</label>
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-orange-400 transition-colors">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="text-gray-600 mb-2">Drag & drop files here, or click to browse</p>
                <p class="text-sm text-gray-500">Max file size: 10MB</p>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
              <button @click="showNewInteraction = false" 
                      class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </button>
              <button class="px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                Save Interaction
              </button>
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
  name: 'Interactions',
  data() {
    return {
      showNewInteraction: false,
      selectedType: 'Email',
      interactionTypes: [
        { name: 'Email', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', bgColor: 'bg-blue-100', iconColor: 'text-blue-600' },
        { name: 'Call', icon: 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', bgColor: 'bg-green-100', iconColor: 'text-green-600' },
        { name: 'Meeting', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', bgColor: 'bg-purple-100', iconColor: 'text-purple-600' },
        { name: 'Support', icon: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z', bgColor: 'bg-yellow-100', iconColor: 'text-yellow-600' }
      ],
      interactions: [
        { id: 1, date: 'Mar 15, 2024', time: '10:30 AM', user: 'John Smith', userType: 'Client', type: 'Call', summary: 'Discussed new project requirements and timeline. Client requested additional features.', followUp: true, attachments: ['quote.pdf'] },
        { id: 2, date: 'Mar 14, 2024', time: '3:45 PM', user: 'Robert Chen', userType: 'Distributor', type: 'Email', summary: 'Order confirmation and shipping details for batch #2345', followUp: false, attachments: ['order.pdf', 'shipping.pdf'] },
        { id: 3, date: 'Mar 14, 2024', time: '11:20 AM', user: 'David Wilson', userType: 'Service Provider', type: 'Meeting', summary: 'Project kickoff meeting for commercial painting project', followUp: true, attachments: ['meeting_notes.pdf'] },
        { id: 4, date: 'Mar 13, 2024', time: '2:15 PM', user: 'Sarah Johnson', userType: 'Client', type: 'Support', summary: 'Technical support for dashboard access issues', followUp: false, attachments: [] }
      ]
    }
  },
  methods: {
    getUserBg(type) {
      const classes = {
        'Client': 'bg-blue-100',
        'Distributor': 'bg-green-100',
        'Service Provider': 'bg-purple-100'
      }
      return classes[type] || 'bg-gray-100'
    },
    getUserText(type) {
      const classes = {
        'Client': 'text-blue-600 font-medium',
        'Distributor': 'text-green-600 font-medium',
        'Service Provider': 'text-purple-600 font-medium'
      }
      return classes[type] || 'text-gray-600'
    },
    getTypeBg(type) {
      const classes = {
        'Email': 'bg-blue-100',
        'Call': 'bg-green-100',
        'Meeting': 'bg-purple-100',
        'Support': 'bg-yellow-100'
      }
      return classes[type] || 'bg-gray-100'
    },
    getTypeIcon(type) {
      const classes = {
        'Email': 'text-blue-600',
        'Call': 'text-green-600',
        'Meeting': 'text-purple-600',
        'Support': 'text-yellow-600'
      }
      return classes[type] || 'text-gray-600'
    }
  }
}
</script>

<style scoped>
/* Line clamp for text truncation */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom modal animation */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Smooth transitions for interactive elements */
button, select, input, textarea {
  transition: all 0.2s ease;
}

/* Custom scrollbar for modal */
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