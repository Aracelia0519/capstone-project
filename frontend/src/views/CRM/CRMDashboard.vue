<template>
  <div>
    <!-- Header -->
    <div class="mb-6 lg:mb-8">
      <h1 class="text-2xl lg:text-3xl font-bold text-gray-800">Dashboard</h1>
      <p class="text-gray-600 mt-2">Quick summary of CRM activity</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mb-6 lg:mb-8">
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Total Clients</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">1,248</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-lg">
            <UserGroupIcon class="w-6 h-6 text-blue-600" />
          </div>
        </div>
        <div class="mt-4">
          <div class="flex items-center text-sm text-green-600">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            +12% from last month
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Distributors</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">56</p>
          </div>
          <div class="p-3 bg-green-100 rounded-lg">
            <TruckIcon class="w-6 h-6 text-green-600" />
          </div>
        </div>
        <div class="mt-4">
          <div class="flex items-center text-sm text-green-600">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            +8% from last month
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">Service Providers</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">89</p>
          </div>
          <div class="p-3 bg-purple-100 rounded-lg">
            <WrenchScrewdriverIcon class="w-6 h-6 text-purple-600" />
          </div>
        </div>
        <div class="mt-4">
          <div class="flex items-center text-sm text-green-600">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            +15% from last month
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 mb-6 lg:mb-8">
      <!-- New Clients Chart -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">New Clients per Month</h2>
        <div class="h-64 flex items-end space-x-2">
          <div v-for="(value, index) in [65, 80, 75, 90, 85, 95, 100, 110, 105, 120, 130, 125]" 
               :key="index"
               class="flex-1 relative group">
            <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-6 bg-blue-500 rounded-t"
                 :style="{ height: value / 2 + 'px' }">
            </div>
            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 
                       bg-gray-800 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
              {{ value }} clients
            </div>
          </div>
        </div>
        <div class="flex justify-between text-xs text-gray-500 mt-2">
          <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span><span>May</span><span>Jun</span>
          <span>Jul</span><span>Aug</span><span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
        </div>
      </div>

      <!-- Task Completion Chart -->
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Pending vs Completed Tasks</h2>
        <div class="h-64 flex items-center justify-center">
          <div class="relative w-48 h-48">
            <div class="absolute inset-0 flex items-center justify-center">
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-800">78%</div>
                <div class="text-sm text-gray-500">Completion Rate</div>
              </div>
            </div>
            <svg class="w-full h-full" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="40" fill="none" stroke="#e5e7eb" stroke-width="10"/>
              <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="10"
                      stroke-dasharray="251.2" stroke-dashoffset="55.264" stroke-linecap="round"
                      transform="rotate(-90 50 50)"/>
            </svg>
          </div>
        </div>
        <div class="flex justify-center space-x-6 mt-4">
          <div class="flex items-center">
            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
            <span class="text-sm text-gray-600">Completed (78%)</span>
          </div>
          <div class="flex items-center">
            <div class="w-3 h-3 bg-gray-200 rounded-full mr-2"></div>
            <span class="text-sm text-gray-600">Pending (22%)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Interactions -->
    <div class="bg-white rounded-xl shadow p-6">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Recent Interactions</h2>
        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
          View All
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="text-left text-gray-500 text-sm border-b">
              <th class="pb-3 font-medium">Date/Time</th>
              <th class="pb-3 font-medium">User</th>
              <th class="pb-3 font-medium">Type</th>
              <th class="pb-3 font-medium">Summary</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="interaction in recentInteractions" :key="interaction.id" class="border-b hover:bg-gray-50">
              <td class="py-4 text-sm">{{ interaction.date }}</td>
              <td class="py-4">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                    <span class="text-xs font-medium">{{ interaction.user.charAt(0) }}</span>
                  </div>
                  <span class="text-sm">{{ interaction.user }}</span>
                </div>
              </td>
              <td class="py-4">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  interaction.type === 'Email' ? 'bg-blue-100 text-blue-800' :
                  interaction.type === 'Call' ? 'bg-green-100 text-green-800' :
                  'bg-purple-100 text-purple-800'
                ]">
                  {{ interaction.type }}
                </span>
              </td>
              <td class="py-4 text-sm text-gray-600">{{ interaction.summary }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script setup>
import { UserGroupIcon, TruckIcon, WrenchScrewdriverIcon, ChartBarIcon } from '@heroicons/vue/24/outline'

const recentInteractions = [
  { id: 1, date: 'Today, 10:30 AM', user: 'John Smith', type: 'Email', summary: 'Follow-up on project proposal' },
  { id: 2, date: 'Today, 09:15 AM', user: 'Sarah Johnson', type: 'Call', summary: 'Product inquiry discussion' },
  { id: 3, date: 'Yesterday, 02:45 PM', user: 'Mike Wilson', type: 'Meeting', summary: 'Quarterly review meeting' },
  { id: 4, date: 'Yesterday, 11:20 AM', user: 'Emily Davis', type: 'Email', summary: 'Service agreement confirmation' },
  { id: 5, date: 'Dec 10, 04:30 PM', user: 'Robert Brown', type: 'Call', summary: 'Payment follow-up' }
]
</script>

<style scoped>
/* Custom chart animation */
@keyframes growBar {
  from { height: 0; }
  to { height: var(--target-height); }
}

.bg-blue-500 {
  animation: growBar 0.8s ease-out;
}
</style>