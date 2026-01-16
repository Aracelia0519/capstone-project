<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 md:w-8 md:h-8 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Reports
          </h1>
          <p class="text-gray-600 mt-2">Generate CRM reports for analysis</p>
        </div>
        <button class="mt-4 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg flex items-center transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Export All Reports
        </button>
      </div>
    </div>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
      <!-- Client Acquisition Report -->
      <div class="bg-white rounded-xl shadow hover-lift overflow-hidden">
        <div class="p-4 md:p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="bg-blue-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-7.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
            </div>
            <span class="text-sm text-gray-500">Last 6 Months</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Client Acquisition</h3>
          <p class="text-gray-600 text-sm mb-4">Track new client sign-ups and growth trends</p>
          <div class="flex items-center justify-between">
            <div>
              <div class="text-2xl font-bold text-gray-800">+245</div>
              <div class="text-sm text-gray-500">New clients</div>
            </div>
            <div class="flex space-x-2">
              <button class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors">
                View
              </button>
              <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                Export
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Interaction Report -->
      <div class="bg-white rounded-xl shadow hover-lift overflow-hidden">
        <div class="p-4 md:p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="bg-orange-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
              </svg>
            </div>
            <span class="text-sm text-gray-500">Last Month</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Interaction History</h3>
          <p class="text-gray-600 text-sm mb-4">Communication patterns and engagement metrics</p>
          <div class="flex items-center justify-between">
            <div>
              <div class="text-2xl font-bold text-gray-800">1,248</div>
              <div class="text-sm text-gray-500">Total interactions</div>
            </div>
            <div class="flex space-x-2">
              <button class="px-3 py-1 bg-orange-50 text-orange-600 rounded-lg text-sm font-medium hover:bg-orange-100 transition-colors">
                View
              </button>
              <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                Export
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Task Report -->
      <div class="bg-white rounded-xl shadow hover-lift overflow-hidden">
        <div class="p-4 md:p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="bg-red-100 p-3 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
            </div>
            <span class="text-sm text-gray-500">This Month</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-800 mb-2">Task Performance</h3>
          <p class="text-gray-600 text-sm mb-4">Follow-up completion rates and efficiency</p>
          <div class="flex items-center justify-between">
            <div>
              <div class="text-2xl font-bold text-gray-800">87%</div>
              <div class="text-sm text-gray-500">Completion rate</div>
            </div>
            <div class="flex space-x-2">
              <button class="px-3 py-1 bg-red-50 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition-colors">
                View
              </button>
              <button class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                Export
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-xl shadow p-4 md:p-6 mb-6">
      <h3 class="text-lg font-semibold text-gray-800 mb-4">Generate Custom Report</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Select report type</option>
            <option value="acquisition">Client Acquisition</option>
            <option value="interaction">Interaction History</option>
            <option value="tasks">Task Performance</option>
            <option value="distributor">Distributor Performance</option>
            <option value="provider">Service Provider Stats</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="week">Last Week</option>
            <option value="month">Last Month</option>
            <option value="quarter">Last Quarter</option>
            <option value="year">Last Year</option>
            <option value="custom">Custom Range</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Users</option>
            <option value="client">Clients</option>
            <option value="distributor">Distributors</option>
            <option value="provider">Service Providers</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
          <select class="w-full px-4 py-2 md:py-3 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="pending">Pending</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end mt-4">
        <button class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
          Generate Report
        </button>
      </div>
    </div>

    <!-- Report Preview -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="p-4 md:p-6 border-b">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-800">Client Acquisition Report - Q1 2024</h3>
          <div class="flex space-x-2 mt-2 md:mt-0">
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
              </svg>
              CSV
            </button>
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              PDF
            </button>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Export
            </button>
          </div>
        </div>
      </div>

      <div class="p-4 md:p-6">
        <!-- Chart -->
        <div class="mb-6">
          <div class="h-64 md:h-80">
            <div class="w-full h-full flex items-end space-x-2 md:space-x-4">
              <div v-for="(month, index) in reportData" :key="index" 
                   class="flex-1 flex flex-col items-center">
                <div class="text-xs text-gray-500 mb-1">{{ month.name }}</div>
                <div class="w-8 md:w-12 flex flex-col h-full justify-end">
                  <div :style="{ height: month.newClients + '%' }" 
                       class="bg-gradient-to-t from-blue-500 to-blue-300 rounded-t-lg mb-1"></div>
                  <div :style="{ height: month.totalClients + '%' }" 
                       class="bg-gradient-to-t from-indigo-500 to-indigo-300 rounded-t-lg"></div>
                </div>
                <div class="text-xs font-medium mt-2">
                  <div class="text-blue-600">{{ month.newClientsLabel }}</div>
                  <div class="text-indigo-600">{{ month.totalLabel }}</div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex justify-center space-x-6 md:space-x-12 mt-4">
            <div class="flex items-center">
              <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
              <span class="text-sm">New Clients</span>
            </div>
            <div class="flex items-center">
              <div class="w-3 h-3 bg-indigo-500 rounded-full mr-2"></div>
              <span class="text-sm">Total Clients</span>
            </div>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-500">Total Clients</div>
            <div class="text-2xl font-bold text-gray-800">1,248</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-500">New This Quarter</div>
            <div class="text-2xl font-bold text-gray-800">245</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-500">Growth Rate</div>
            <div class="text-2xl font-bold text-gray-800">18.5%</div>
          </div>
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="text-sm text-gray-500">Retention Rate</div>
            <div class="text-2xl font-bold text-gray-800">92.3%</div>
          </div>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50">
                <th class="py-3 px-4 text-left font-semibold text-gray-700">Month</th>
                <th class="py-3 px-4 text-left font-semibold text-gray-700">New Clients</th>
                <th class="py-3 px-4 text-left font-semibold text-gray-700">Total Clients</th>
                <th class="py-3 px-4 text-left font-semibold text-gray-700">Growth</th>
                <th class="py-3 px-4 text-left font-semibold text-gray-700">Retention</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="month in reportData" :key="month.name">
                <td class="py-3 px-4 font-medium">{{ month.name }}</td>
                <td class="py-3 px-4">{{ month.newClientsValue }}</td>
                <td class="py-3 px-4">{{ month.totalValue }}</td>
                <td class="py-3 px-4">
                  <span :class="`px-2 py-1 rounded text-xs ${month.growth >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`">
                    {{ month.growth >= 0 ? '+' : '' }}{{ month.growth }}%
                  </span>
                </td>
                <td class="py-3 px-4">{{ month.retention }}%</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script>
export default {
  name: 'Reports',
  data() {
    return {
      reportData: [
        { name: 'Jan', newClients: 60, totalClients: 80, newClientsLabel: '45', totalLabel: '1,045', newClientsValue: 45, totalValue: '1,045', growth: 12.5, retention: 93.2 },
        { name: 'Feb', newClients: 70, totalClients: 85, newClientsLabel: '52', totalLabel: '1,097', newClientsValue: 52, totalValue: '1,097', growth: 15.8, retention: 94.1 },
        { name: 'Mar', newClients: 75, totalClients: 90, newClientsLabel: '65', totalLabel: '1,162', newClientsValue: 65, totalValue: '1,162', growth: 18.2, retention: 92.8 },
        { name: 'Apr', newClients: 80, totalClients: 95, newClientsLabel: '58', totalLabel: '1,220', newClientsValue: 58, totalValue: '1,220', growth: 14.7, retention: 93.5 },
        { name: 'May', newClients: 85, totalClients: 100, newClientsLabel: '72', totalLabel: '1,292', newClientsValue: 72, totalValue: '1,292', growth: 20.3, retention: 94.0 },
        { name: 'Jun', newClients: 95, totalClients: 105, newClientsLabel: '68', totalLabel: '1,360', newClientsValue: 68, totalValue: '1,360', growth: 18.9, retention: 92.3 }
      ]
    }
  }
}
</script>

<style scoped>
/* Custom hover effect for report cards */
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions for all interactive elements */
button, select, input {
  transition: all 0.2s ease;
}

/* Custom scrollbar for tables */
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

/* Gradient chart bars */
.bg-gradient-to-t {
  background-size: 100% 200%;
  background-position: 0% 0%;
  transition: background-position 0.3s ease;
}

.bg-gradient-to-t:hover {
  background-position: 0% 100%;
}
</style>