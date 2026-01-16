<template>
  <div class="hr-dashboard p-4 md:p-6">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">HR Dashboard</h1>
      <p class="text-gray-600">Overview of workforce information and statistics</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Total Employees -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-blue-50 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-1 rounded-full">+12%</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-1">156</h3>
        <p class="text-gray-600 text-sm">Total Employees</p>
      </div>

      <!-- Active Employees -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-green-50 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-1">142</h3>
        <p class="text-gray-600 text-sm">Active Employees</p>
      </div>

      <!-- New Hires This Month -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-purple-50 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
          </div>
          <span class="text-sm font-medium text-purple-600 bg-purple-50 px-2 py-1 rounded-full">New</span>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-1">8</h3>
        <p class="text-gray-600 text-sm">New Hires (This Month)</p>
      </div>

      <!-- Pending Reviews -->
      <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between mb-4">
          <div class="p-3 bg-yellow-50 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <h3 class="text-2xl font-bold text-gray-800 mb-1">5</h3>
        <p class="text-gray-600 text-sm">Pending Reviews</p>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <!-- Department Distribution -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold text-gray-800">Department Distribution</h2>
          <button class="text-sm text-blue-600 hover:text-blue-800 font-medium">
            View All
          </button>
        </div>
        <div class="space-y-4">
          <div v-for="dept in departments" :key="dept.name" class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3" :class="dept.color">
                <span class="text-xs font-medium">{{ dept.initials }}</span>
              </div>
              <span class="text-gray-700">{{ dept.name }}</span>
            </div>
            <div class="flex items-center">
              <span class="text-gray-800 font-medium mr-3">{{ dept.count }}</span>
              <div class="w-32 bg-gray-200 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" :style="{ width: dept.percentage + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Employment Status -->
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-lg font-semibold text-gray-800">Employment Status</h2>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div v-for="status in employmentStatus" :key="status.name" class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">{{ status.name }}</span>
              <span class="text-xs px-2 py-1 rounded-full" :class="status.badgeClass">{{ status.count }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="h-2 rounded-full" :class="status.barColor" :style="{ width: status.percentage + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-md p-6">
      <h2 class="text-lg font-semibold text-gray-800 mb-6">Quick Actions</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <button @click="$router.push('/hr/employees')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors">
          <div class="p-3 bg-blue-100 rounded-lg mb-3">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.832.055-1.68.113-2.5.113-4.97 0-9-2.239-9-5s4.03-5 9-5c1.72 0 3.32.404 4.786 1.09" />
            </svg>
          </div>
          <span class="font-medium text-gray-800">View Employees</span>
          <span class="text-sm text-gray-600 mt-1">Manage team</span>
        </button>

        <button @click="$router.push('/hr/employees/add')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors">
          <div class="p-3 bg-green-100 rounded-lg mb-3">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
          </div>
          <span class="font-medium text-gray-800">Add Employee</span>
          <span class="text-sm text-gray-600 mt-1">New hire</span>
        </button>

        <button @click="$router.push('/hr/reports')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-colors">
          <div class="p-3 bg-purple-100 rounded-lg mb-3">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <span class="font-medium text-gray-800">Generate Reports</span>
          <span class="text-sm text-gray-600 mt-1">Export data</span>
        </button>

        <button @click="$router.push('/hr/employment-status')" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-yellow-300 hover:bg-yellow-50 transition-colors">
          <div class="p-3 bg-yellow-100 rounded-lg mb-3">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <span class="font-medium text-gray-800">Update Status</span>
          <span class="text-sm text-gray-600 mt-1">Track employment</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const departments = ref([
  { name: 'Administration', initials: 'AD', count: 24, percentage: 15, color: 'bg-blue-100 text-blue-800' },
  { name: 'Human Resources', initials: 'HR', count: 18, percentage: 12, color: 'bg-green-100 text-green-800' },
  { name: 'Finance', initials: 'FN', count: 32, percentage: 21, color: 'bg-purple-100 text-purple-800' },
  { name: 'Logistics', initials: 'LG', count: 45, percentage: 29, color: 'bg-yellow-100 text-yellow-800' },
  { name: 'Operations', initials: 'OP', count: 37, percentage: 23, color: 'bg-red-100 text-red-800' },
])

const employmentStatus = ref([
  { name: 'Active', count: 142, percentage: 91, badgeClass: 'bg-green-100 text-green-800', barColor: 'bg-green-500' },
  { name: 'Inactive', count: 14, percentage: 9, badgeClass: 'bg-gray-100 text-gray-800', barColor: 'bg-gray-500' },
  { name: 'Probationary', count: 8, percentage: 5, badgeClass: 'bg-blue-100 text-blue-800', barColor: 'bg-blue-500' },
  { name: 'Resigned', count: 6, percentage: 4, badgeClass: 'bg-yellow-100 text-yellow-800', barColor: 'bg-yellow-500' },
])
</script>

<style scoped>
.hr-dashboard {
  min-height: calc(100vh - 80px);
}
</style>