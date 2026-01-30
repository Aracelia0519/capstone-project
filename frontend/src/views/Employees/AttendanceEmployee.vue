<template>
  <div class="attendance-employee">
    <!-- Header with Action Buttons -->
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Time Tracking</h1>
          <p class="text-gray-500 mt-1">Monitor your attendance and working hours</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
          <button class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors shadow-lg">
            <i class="fas fa-clock mr-2"></i>
            Clock In/Out
          </button>
          <button class="px-6 py-3 bg-white text-indigo-600 border border-indigo-600 rounded-lg font-semibold hover:bg-indigo-50 transition-colors">
            <i class="fas fa-edit mr-2"></i>
            Request Correction
          </button>
        </div>
      </div>
    </div>

    <!-- Current Status Card -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h3 class="text-xl font-semibold mb-2">Today's Attendance</h3>
          <div class="flex items-center">
            <div class="w-3 h-3 bg-green-400 rounded-full mr-3"></div>
            <span class="text-lg">Currently Clocked In</span>
          </div>
          <p class="mt-2 opacity-90">Duration: 6 hours 30 minutes</p>
        </div>
        <div class="mt-4 md:mt-0 text-center">
          <div class="text-4xl font-bold mb-1">8:30 AM</div>
          <p class="opacity-90">Time In</p>
        </div>
      </div>
    </div>

    <!-- Tabs Navigation -->
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

    <!-- Tab Content -->
    <div class="bg-white rounded-xl shadow-lg p-6">
      <!-- Daily Time Record -->
      <div v-if="activeTab === 'daily'">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Time Record</h3>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50">
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Time In</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Time Out</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hours</th>
                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="record in dailyRecords" :key="record.date">
                <td class="py-3 px-4">{{ record.date }}</td>
                <td class="py-3 px-4">{{ record.timeIn }}</td>
                <td class="py-3 px-4">{{ record.timeOut }}</td>
                <td class="py-3 px-4">{{ record.hours }}</td>
                <td class="py-3 px-4">
                  <span :class="[
                    'px-2 py-1 rounded-full text-xs font-semibold',
                    record.status === 'Present' ? 'bg-green-100 text-green-800' :
                    record.status === 'Late' ? 'bg-amber-100 text-amber-800' :
                    'bg-red-100 text-red-800'
                  ]">
                    {{ record.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Monthly Summary -->
      <div v-if="activeTab === 'monthly'">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Attendance Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-gradient-to-r from-green-50 to-emerald-100 rounded-lg p-6">
            <div class="flex items-center">
              <div class="p-3 bg-green-100 rounded-lg mr-4">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
              </div>
              <div>
                <p class="text-sm text-gray-600">Days Present</p>
                <p class="text-2xl font-bold text-gray-800">21</p>
              </div>
            </div>
          </div>
          <div class="bg-gradient-to-r from-amber-50 to-yellow-100 rounded-lg p-6">
            <div class="flex items-center">
              <div class="p-3 bg-amber-100 rounded-lg mr-4">
                <i class="fas fa-clock text-amber-600 text-xl"></i>
              </div>
              <div>
                <p class="text-sm text-gray-600">Late Arrivals</p>
                <p class="text-2xl font-bold text-gray-800">2</p>
              </div>
            </div>
          </div>
          <div class="bg-gradient-to-r from-blue-50 to-cyan-100 rounded-lg p-6">
            <div class="flex items-center">
              <div class="p-3 bg-blue-100 rounded-lg mr-4">
                <i class="fas fa-business-time text-blue-600 text-xl"></i>
              </div>
              <div>
                <p class="text-sm text-gray-600">Overtime Hours</p>
                <p class="text-2xl font-bold text-gray-800">15.5</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Overtime Records -->
      <div v-if="activeTab === 'overtime'">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Overtime Records</h3>
        <div class="space-y-4">
          <div v-for="ot in overtimeRecords" :key="ot.date" class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
              <div>
                <p class="font-medium text-gray-800">{{ ot.date }}</p>
                <p class="text-sm text-gray-600">{{ ot.time }} • {{ ot.duration }} hours</p>
                <p class="text-sm text-gray-600 mt-1">{{ ot.reason }}</p>
              </div>
              <div class="mt-2 md:mt-0">
                <span :class="[
                  'px-3 py-1 rounded-full text-sm font-semibold',
                  ot.status === 'Approved' ? 'bg-green-100 text-green-800' :
                  ot.status === 'Pending' ? 'bg-amber-100 text-amber-800' :
                  'bg-red-100 text-red-800'
                ]">
                  {{ ot.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const activeTab = ref('daily')

const tabs = [
  { id: 'daily', label: 'Daily Time Record', icon: 'fas fa-calendar-day' },
  { id: 'monthly', label: 'Monthly Summary', icon: 'fas fa-calendar-alt' },
  { id: 'overtime', label: 'Overtime Records', icon: 'fas fa-business-time' },
]

const dailyRecords = [
  { date: '2023-12-10', timeIn: '8:30 AM', timeOut: '5:30 PM', hours: '9.0', status: 'Present' },
  { date: '2023-12-09', timeIn: '9:15 AM', timeOut: '6:00 PM', hours: '8.75', status: 'Late' },
  { date: '2023-12-08', timeIn: '8:45 AM', timeOut: '5:45 PM', hours: '9.0', status: 'Present' },
  { date: '2023-12-07', timeIn: '8:30 AM', timeOut: '7:30 PM', hours: '11.0', status: 'Present' },
  { date: '2023-12-06', timeIn: '8:30 AM', timeOut: '5:30 PM', hours: '9.0', status: 'Present' },
]

const overtimeRecords = [
  { date: '2023-12-07', time: '5:30 PM - 7:30 PM', duration: '2.0', reason: 'Project deadline', status: 'Approved' },
  { date: '2023-12-05', time: '5:30 PM - 8:00 PM', duration: '2.5', reason: 'Team meeting', status: 'Pending' },
  { date: '2023-11-30', time: '6:00 PM - 9:00 PM', duration: '3.0', reason: 'Client presentation', status: 'Approved' },
]
</script>

<style scoped>
.attendance-employee {
  max-width: 1400px;
  margin: 0 auto;
}
</style>