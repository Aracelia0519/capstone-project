<template>
  <div class="leaves-employee">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Leave Management</h1>
          <p class="text-gray-500 mt-1">Request and track your leaves</p>
        </div>
        <button @click="showRequestModal = true"
                class="mt-4 md:mt-0 px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors shadow-lg flex items-center">
          <i class="fas fa-plus mr-2"></i>
          File Leave Request
        </button>
      </div>
    </div>

    <!-- Leave Balances -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div v-for="balance in leaveBalances" :key="balance.type"
           class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ balance.type }}</h3>
            <div class="mt-2">
              <span class="text-2xl font-bold text-gray-800">{{ balance.available }}</span>
              <span class="text-sm text-gray-500 ml-2">of {{ balance.total }} days</span>
            </div>
          </div>
          <div :class="['p-3 rounded-lg', balance.colorClass]">
            <i :class="[balance.icon, 'text-xl']"></i>
          </div>
        </div>
        <div class="mt-4">
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div :class="['h-2 rounded-full', balance.progressColor]"
                 :style="{ width: balance.percentage + '%' }"></div>
          </div>
          <p class="text-xs text-gray-500 mt-2">{{ balance.percentage }}% available</p>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="mb-8">
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8 overflow-x-auto">
          <button v-for="tab in tabs" 
                  :key="tab.id"
                  @click="activeTab = tab.id"
                  :class="[
                    'py-3 px-1 font-medium text-sm border-b-2 transition-colors whitespace-nowrap',
                    activeTab === tab.id 
                      ? 'border-indigo-600 text-indigo-600' 
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                  ]">
            <i :class="tab.icon + ' mr-2'"></i>
            {{ tab.label }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Content Area -->
    <div class="bg-white rounded-xl shadow-lg p-6">
      <!-- Leave History -->
      <div v-if="activeTab === 'history'">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">Leave History</h3>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50">
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date Filed</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Leave Type</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Duration</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Reason</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="leave in leaveHistory" :key="leave.id">
                <td class="py-3 px-4">{{ leave.dateFiled }}</td>
                <td class="py-3 px-4">
                  <span :class="[
                    'px-2 py-1 rounded-full text-xs font-semibold',
                    leave.typeClass
                  ]">
                    {{ leave.type }}
                  </span>
                </td>
                <td class="py-3 px-4">{{ leave.duration }}</td>
                <td class="py-3 px-4">{{ leave.reason }}</td>
                <td class="py-3 px-4">
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-semibold',
                    leave.status === 'Approved' ? 'bg-green-100 text-green-800' :
                    leave.status === 'Pending' ? 'bg-amber-100 text-amber-800' :
                    'bg-red-100 text-red-800'
                  ]">
                    {{ leave.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Request Form (Placeholder) -->
      <div v-if="activeTab === 'request'">
        <h3 class="text-lg font-semibold text-gray-800 mb-6">File Leave Request</h3>
        <div class="max-w-2xl space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Leave Type</label>
              <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option>Vacation Leave</option>
                <option>Sick Leave</option>
                <option>Emergency Leave</option>
                <option>Maternity Leave</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Days)</label>
              <input type="number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter number of days">
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
              <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
              <input type="date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
            <textarea rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Briefly describe the reason for leave"></textarea>
          </div>
          
          <div class="flex justify-end space-x-4">
            <button class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <button class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
              Submit Request
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showRequestModal = ref(false)
const activeTab = ref('history')

const tabs = [
  { id: 'history', label: 'Leave History', icon: 'fas fa-history' },
  { id: 'request', label: 'File Request', icon: 'fas fa-plus-circle' },
]

const leaveBalances = [
  { type: 'Vacation Leave', available: 8, total: 15, percentage: 53, icon: 'fas fa-umbrella-beach text-blue-600', colorClass: 'bg-blue-50', progressColor: 'bg-blue-500' },
  { type: 'Sick Leave', available: 12, total: 15, percentage: 80, icon: 'fas fa-heartbeat text-red-600', colorClass: 'bg-red-50', progressColor: 'bg-red-500' },
  { type: 'Emergency Leave', available: 2, total: 5, percentage: 40, icon: 'fas fa-exclamation-triangle text-amber-600', colorClass: 'bg-amber-50', progressColor: 'bg-amber-500' },
  { type: 'Maternity Leave', available: 105, total: 105, percentage: 100, icon: 'fas fa-baby text-purple-600', colorClass: 'bg-purple-50', progressColor: 'bg-purple-500' },
]

const leaveHistory = [
  { id: 1, dateFiled: '2023-11-20', type: 'Vacation Leave', typeClass: 'bg-blue-100 text-blue-800', duration: '3 days', reason: 'Family vacation', status: 'Approved' },
  { id: 2, dateFiled: '2023-10-15', type: 'Sick Leave', typeClass: 'bg-red-100 text-red-800', duration: '2 days', reason: 'Flu', status: 'Approved' },
  { id: 3, dateFiled: '2023-09-05', type: 'Emergency Leave', typeClass: 'bg-amber-100 text-amber-800', duration: '1 day', reason: 'Family emergency', status: 'Approved' },
  { id: 4, dateFiled: '2023-12-01', type: 'Vacation Leave', typeClass: 'bg-blue-100 text-blue-800', duration: '5 days', reason: 'Christmas holiday', status: 'Pending' },
  { id: 5, dateFiled: '2023-08-20', type: 'Sick Leave', typeClass: 'bg-red-100 text-red-800', duration: '1 day', reason: 'Doctor appointment', status: 'Approved' },
]
</script>

<style scoped>
.leaves-employee {
  max-width: 1400px;
  margin: 0 auto;
}
</style>