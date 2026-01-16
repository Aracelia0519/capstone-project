<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 md:w-8 md:h-8 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Follow-ups & Tasks
          </h1>
          <p class="text-gray-600 mt-2">Track pending actions for relationships</p>
        </div>
        <button @click="showNewTask = true" 
                class="mt-4 md:mt-0 bg-red-600 hover:bg-red-700 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg flex items-center transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add New Task
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-red-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Pending Tasks</p>
            <h3 class="text-2xl font-bold text-gray-800">24</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Completed Today</p>
            <h3 class="text-2xl font-bold text-gray-800">8</h3>
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
            <p class="text-sm text-gray-500">Due This Week</p>
            <h3 class="text-2xl font-bold text-gray-800">12</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Assigned to Me</p>
            <h3 class="text-2xl font-bold text-gray-800">7</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="p-4 md:p-6 border-b">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="flex space-x-4 mb-4 md:mb-0">
            <button :class="`px-4 py-2 rounded-lg font-medium ${activeFilter === 'all' ? 'bg-red-100 text-red-700' : 'text-gray-600 hover:bg-gray-100'}`"
                    @click="activeFilter = 'all'">
              All Tasks
            </button>
            <button :class="`px-4 py-2 rounded-lg font-medium ${activeFilter === 'pending' ? 'bg-red-100 text-red-700' : 'text-gray-600 hover:bg-gray-100'}`"
                    @click="activeFilter = 'pending'">
              Pending
            </button>
            <button :class="`px-4 py-2 rounded-lg font-medium ${activeFilter === 'completed' ? 'bg-red-100 text-red-700' : 'text-gray-600 hover:bg-gray-100'}`"
                    @click="activeFilter = 'completed'">
              Completed
            </button>
          </div>
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" placeholder="Search tasks..." 
                   class="w-full md:w-64 pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Task</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Assigned To</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Related To</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Deadline</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Status</th>
              <th class="py-4 px-4 md:px-6 text-left font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="task in filteredTasks" :key="task.id" class="hover:bg-gray-50">
              <td class="py-4 px-4 md:px-6">
                <div class="flex items-start">
                  <input type="checkbox" :checked="task.status === 'Completed'" 
                         @change="toggleTask(task.id)"
                         class="mt-1 rounded text-red-600 focus:ring-red-500">
                  <div class="ml-3">
                    <div class="font-medium text-gray-900">{{ task.name }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ task.description }}</div>
                    <div v-if="task.priority === 'High'" class="mt-1">
                      <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-red-100 text-red-800">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.502 0L4.404 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        High Priority
                      </span>
                    </div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <span class="text-blue-600 font-medium text-sm">{{ task.assignedTo.charAt(0) }}</span>
                  </div>
                  <span class="font-medium">{{ task.assignedTo }}</span>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex items-center">
                  <div :class="`w-8 h-8 rounded-full flex items-center justify-center mr-3 ${getRelatedBg(task.relatedType)}`">
                    <span :class="getRelatedText(task.relatedType)" class="text-sm font-medium">{{ task.relatedTo.charAt(0) }}</span>
                  </div>
                  <div>
                    <div class="font-medium">{{ task.relatedTo }}</div>
                    <div class="text-xs text-gray-500">{{ task.relatedType }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="text-sm">
                  <div :class="`font-medium ${getDeadlineClass(task.deadline)}`">{{ task.deadline }}</div>
                  <div class="text-gray-500">{{ task.daysLeft }}</div>
                </div>
              </td>
              <td class="py-4 px-4 md:px-6">
                <span :class="`px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(task.status)}`">
                  {{ task.status }}
                </span>
              </td>
              <td class="py-4 px-4 md:px-6">
                <div class="flex space-x-2">
                  <button class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
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

    <!-- New Task Modal -->
    <div v-if="showNewTask" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <!-- Modal Header -->
          <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Create New Task</h2>
            <button @click="showNewTask = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Form -->
          <div class="space-y-6">
            <!-- Task Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Task Name</label>
              <input type="text" placeholder="Enter task name..." 
                     class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" 
                        rows="3" placeholder="Enter task description..."></textarea>
            </div>

            <!-- Assignment -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign To</label>
                <select class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                  <option value="">Select team member...</option>
                  <option value="john">John Doe</option>
                  <option value="jane">Jane Smith</option>
                  <option value="mike">Mike Johnson</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
            </div>

            <!-- Related To -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Related To</label>
                <select class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                  <option value="">Select contact...</option>
                  <option value="client">John Smith (Client)</option>
                  <option value="distributor">Robert Chen (Distributor)</option>
                  <option value="provider">David Wilson (Provider)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Deadline</label>
                <input type="date" class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t">
              <button @click="showNewTask = false" 
                      class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </button>
              <button class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                Create Task
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
  name: 'FollowUps',
  data() {
    return {
      showNewTask: false,
      activeFilter: 'all',
      tasks: [
        { id: 1, name: 'Follow up on quotation', description: 'Send follow-up email to John Smith about the quotation', assignedTo: 'John Doe', relatedTo: 'John Smith', relatedType: 'Client', deadline: 'Mar 18, 2024', daysLeft: '2 days left', status: 'Pending', priority: 'High' },
        { id: 2, name: 'Review distributor documents', description: 'Verify and approve uploaded documents from Global Supply', assignedTo: 'Jane Smith', relatedTo: 'Robert Chen', relatedType: 'Distributor', deadline: 'Mar 20, 2024', daysLeft: '4 days left', status: 'Pending', priority: 'Medium' },
        { id: 3, name: 'Schedule project meeting', description: 'Coordinate meeting with service provider for new project', assignedTo: 'Mike Johnson', relatedTo: 'David Wilson', relatedType: 'Service Provider', deadline: 'Mar 16, 2024', daysLeft: 'Today', status: 'Pending', priority: 'High' },
        { id: 4, name: 'Update client information', description: 'Update contact details for Sarah Johnson', assignedTo: 'John Doe', relatedTo: 'Sarah Johnson', relatedType: 'Client', deadline: 'Mar 14, 2024', daysLeft: 'Completed', status: 'Completed', priority: 'Low' }
      ]
    }
  },
  computed: {
    filteredTasks() {
      if (this.activeFilter === 'all') return this.tasks
      return this.tasks.filter(task => task.status === this.activeFilter)
    }
  },
  methods: {
    toggleTask(taskId) {
      const task = this.tasks.find(t => t.id === taskId)
      if (task) {
        task.status = task.status === 'Pending' ? 'Completed' : 'Pending'
      }
    },
    getRelatedBg(type) {
      const classes = {
        'Client': 'bg-blue-100',
        'Distributor': 'bg-green-100',
        'Service Provider': 'bg-purple-100'
      }
      return classes[type] || 'bg-gray-100'
    },
    getRelatedText(type) {
      const classes = {
        'Client': 'text-blue-600',
        'Distributor': 'text-green-600',
        'Service Provider': 'text-purple-600'
      }
      return classes[type] || 'text-gray-600'
    },
    getDeadlineClass(deadline) {
      if (deadline.includes('Today')) return 'text-red-600'
      if (deadline.includes('Completed')) return 'text-green-600'
      return 'text-gray-900'
    },
    getStatusClass(status) {
      const classes = {
        'Pending': 'bg-yellow-100 text-yellow-800',
        'Completed': 'bg-green-100 text-green-800',
        'Overdue': 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }
  }
}
</script>

<style scoped>
/* Custom checkbox styling */
input[type="checkbox"] {
  width: 20px;
  height: 20px;
}

/* Smooth transitions */
button, select, input, textarea {
  transition: all 0.2s ease;
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

/* Table row hover effect */
tr {
  transition: background-color 0.2s ease;
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