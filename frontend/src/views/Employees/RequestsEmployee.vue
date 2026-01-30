<template>
  <div class="requests-employee">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Requests Center</h1>
      <p class="text-gray-500 mt-1">Submit and track your requests</p>
    </div>

    <!-- Request Type Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <button v-for="type in requestTypes" 
              :key="type.id"
              @click="showRequestForm(type.id)"
              class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow border border-gray-200 hover:border-indigo-300">
        <div class="flex items-center">
          <div :class="['p-3 rounded-lg mr-4', type.colorClass]">
            <i :class="[type.icon, type.iconColor, 'text-xl']"></i>
          </div>
          <div class="text-left">
            <h3 class="font-semibold text-gray-800">{{ type.label }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ type.description }}</p>
          </div>
        </div>
      </button>
    </div>

    <!-- Active Requests -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Active Requests</h3>
        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
          {{ activeRequests.length }} requests
        </span>
      </div>

      <div class="space-y-4">
        <div v-for="request in activeRequests" 
             :key="request.id"
             class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors">
          <div class="flex flex-col md:flex-row md:items-center justify-between">
            <div class="flex items-center">
              <div :class="['p-2 rounded-lg mr-4', getRequestType(request.type).colorClass]">
                <i :class="[getRequestType(request.type).icon, getRequestType(request.type).iconColor]"></i>
              </div>
              <div>
                <p class="font-medium text-gray-800">{{ request.title }}</p>
                <p class="text-sm text-gray-600">{{ request.date }}</p>
              </div>
            </div>
            <div class="mt-3 md:mt-0 flex items-center space-x-4">
              <span :class="[
                'px-3 py-1 rounded-full text-sm font-semibold',
                request.status === 'Approved' ? 'bg-green-100 text-green-800' :
                request.status === 'Pending' ? 'bg-amber-100 text-amber-800' :
                'bg-red-100 text-red-800'
              ]">
                {{ request.status }}
              </span>
              <button class="p-2 text-gray-500 hover:text-indigo-600 transition-colors">
                <i class="fas fa-ellipsis-v"></i>
              </button>
            </div>
          </div>
          <div class="mt-3 text-sm text-gray-600">
            {{ request.description }}
          </div>
        </div>
      </div>
    </div>

    <!-- Request History -->
    <div class="bg-white rounded-xl shadow-lg p-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-6">Request History</h3>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-50">
              <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
              <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Request Type</th>
              <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Details</th>
              <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
              <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in requestHistory" :key="item.id">
              <td class="py-3 px-4">{{ item.date }}</td>
              <td class="py-3 px-4">
                <div class="flex items-center">
                  <div :class="['p-1 rounded mr-2', getRequestType(item.type).colorClass]">
                    <i :class="[getRequestType(item.type).icon, getRequestType(item.type).iconColor, 'text-xs']"></i>
                  </div>
                  <span>{{ item.typeLabel }}</span>
                </div>
              </td>
              <td class="py-3 px-4">{{ item.details }}</td>
              <td class="py-3 px-4">
                <span :class="[
                  'px-2 py-1 rounded-full text-xs font-semibold',
                  item.status === 'Approved' ? 'bg-green-100 text-green-800' :
                  item.status === 'Pending' ? 'bg-amber-100 text-amber-800' :
                  'bg-red-100 text-red-800'
                ]">
                  {{ item.status }}
                </span>
              </td>
              <td class="py-3 px-4">
                <div class="flex space-x-2">
                  <button class="p-1 text-gray-500 hover:text-indigo-600 transition-colors">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button v-if="item.status === 'Pending'" class="p-1 text-gray-500 hover:text-red-600 transition-colors">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const requestTypes = [
  { id: 'attendance', label: 'Attendance Correction', icon: 'fas fa-clock', description: 'Fix time records', colorClass: 'bg-blue-50', iconColor: 'text-blue-600' },
  { id: 'overtime', label: 'Overtime Request', icon: 'fas fa-business-time', description: 'Request overtime hours', colorClass: 'bg-purple-50', iconColor: 'text-purple-600' },
  { id: 'payslip', label: 'Payslip Request', icon: 'fas fa-file-invoice-dollar', description: 'Request payslip copy', colorClass: 'bg-green-50', iconColor: 'text-green-600' },
  { id: 'certificate', label: 'Employment Certificate', icon: 'fas fa-file-certificate', description: 'Request employment proof', colorClass: 'bg-amber-50', iconColor: 'text-amber-600' },
]

const activeRequests = [
  { id: 1, type: 'attendance', title: 'Time Correction: Dec 5', date: 'Submitted Dec 6, 2023', status: 'Pending', description: 'Request to adjust time out from 5:30 PM to 6:30 PM' },
  { id: 2, type: 'overtime', title: 'Overtime Request: Dec 7', date: 'Submitted Dec 5, 2023', status: 'Pending', description: 'Overtime request for project completion - 3 hours' },
  { id: 3, type: 'payslip', title: 'November Payslip Copy', date: 'Submitted Dec 4, 2023', status: 'Approved', description: 'Request for additional payslip copy for loan application' },
]

const requestHistory = [
  { id: 1, date: '2023-12-01', type: 'certificate', typeLabel: 'Employment Certificate', details: 'For visa application', status: 'Approved' },
  { id: 2, date: '2023-11-25', type: 'attendance', typeLabel: 'Attendance Correction', details: 'Time in correction - Nov 20', status: 'Approved' },
  { id: 3, date: '2023-11-18', type: 'overtime', typeLabel: 'Overtime Request', details: 'Weekend work - 5 hours', status: 'Approved' },
  { id: 4, date: '2023-11-10', type: 'payslip', typeLabel: 'Payslip Request', details: 'October payslip copy', status: 'Approved' },
  { id: 5, date: '2023-10-30', type: 'certificate', typeLabel: 'Employment Certificate', details: 'Bank loan requirement', status: 'Approved' },
]

const showRequestForm = (type) => {
  console.log('Show request form for:', type)
  // Implement modal or form display
}

const getRequestType = (typeId) => {
  return requestTypes.find(t => t.id === typeId) || requestTypes[0]
}
</script>

<style scoped>
.requests-employee {
  max-width: 1400px;
  margin: 0 auto;
}
</style>