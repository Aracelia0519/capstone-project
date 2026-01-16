<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-teal-100 text-teal-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </span>
          Financial Reports
        </h1>
        <p class="text-sm text-gray-600">Summarize and analyze financial data</p>
      </div>
      <div class="flex gap-3">
        <button class="px-4 py-2.5 bg-teal-600 text-white rounded-lg font-medium flex items-center gap-2 hover:bg-teal-700 transition-colors duration-200" @click="generateReport">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Generate Report</span>
        </button>
      </div>
    </div>

    <!-- Report Types -->
    <div class="mb-8">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Report Types</h2>
        <p class="text-sm text-gray-600">Select report type to generate</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div 
          v-for="type in reportTypes" 
          :key="type.id"
          class="bg-white rounded-xl shadow-sm border-2 border-gray-200 p-6 cursor-pointer transition-all duration-200 hover:border-teal-300 hover:shadow-md"
          :class="{ 'border-teal-500 bg-teal-50': selectedType === type.id }"
          @click="selectedType = type.id"
        >
          <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-600 flex items-center justify-center mb-4"
            :class="{ 'bg-teal-200 text-teal-700': selectedType === type.id }">
            <component :is="type.icon" class="w-6 h-6" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ type.name }}</h3>
          <p class="text-sm text-gray-600 mb-4">{{ type.description }}</p>
          <div class="flex items-center gap-2 text-xs text-gray-500">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Last generated: {{ type.lastGenerated }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Parameters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Report Parameters</h2>
        <p class="text-sm text-gray-600">Configure report settings</p>
      </div>
      
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Period</label>
            <select v-model="parameters.period" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
              <option value="monthly">Monthly</option>
              <option value="quarterly">Quarterly</option>
              <option value="yearly">Yearly</option>
              <option value="custom">Custom Range</option>
            </select>
          </div>
          
          <div v-if="parameters.period === 'custom'" class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Start Date</label>
            <input type="date" v-model="parameters.startDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
          </div>
          
          <div v-if="parameters.period === 'custom'" class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">End Date</label>
            <input type="date" v-model="parameters.endDate" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Format</label>
            <select v-model="parameters.format" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
              <option value="pdf">PDF</option>
              <option value="csv">CSV</option>
              <option value="excel">Excel</option>
            </select>
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Group By</label>
            <select v-model="parameters.groupBy" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
              <option value="day">Day</option>
              <option value="week">Week</option>
              <option value="month">Month</option>
              <option value="category">Category</option>
              <option value="payment_method">Payment Method</option>
            </select>
          </div>
        </div>
        
        <div class="flex flex-wrap gap-3 justify-end">
          <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50" @click="resetParameters">
            Reset
          </button>
          <button class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700" @click="previewReport">
            Preview Report
          </button>
          <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" @click="exportReport">
            Export Report
          </button>
        </div>
      </div>
    </div>

    <!-- Report Preview -->
    <div v-if="showPreview" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 backdrop-filter blur-sm">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-white">
          <h2 class="text-lg font-semibold text-gray-900">Report Preview</h2>
          <button @click="showPreview = false" class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="bg-white p-6 max-h-[70vh] overflow-y-auto">
          <!-- Report Header -->
          <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-6 border-b border-gray-200">
            <div>
              <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ selectedReportType?.name }} Report</h2>
              <p class="text-sm text-gray-600">Period: {{ getPeriodText() }}</p>
            </div>
            <div class="text-sm text-gray-600 space-y-1 mt-2 md:mt-0">
              <p>Generated: {{ new Date().toLocaleDateString() }}</p>
              <p>Format: {{ parameters.format.toUpperCase() }}</p>
            </div>
          </div>
          
          <!-- Summary Statistics -->
          <div class="mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <div class="bg-gradient-to-r from-teal-50 to-blue-50 border border-teal-100 rounded-xl p-4 text-center">
                <span class="block text-sm text-gray-600 mb-2">Total Revenue</span>
                <span class="block text-xl font-bold text-gray-900">₱1,245,800</span>
              </div>
              <div class="bg-gradient-to-r from-teal-50 to-blue-50 border border-teal-100 rounded-xl p-4 text-center">
                <span class="block text-sm text-gray-600 mb-2">Total Expenses</span>
                <span class="block text-xl font-bold text-gray-900">₱589,300</span>
              </div>
              <div class="bg-gradient-to-r from-teal-50 to-blue-50 border border-teal-100 rounded-xl p-4 text-center">
                <span class="block text-sm text-gray-600 mb-2">Net Profit</span>
                <span class="block text-xl font-bold text-gray-900">₱656,500</span>
              </div>
              <div class="bg-gradient-to-r from-teal-50 to-blue-50 border border-teal-100 rounded-xl p-4 text-center">
                <span class="block text-sm text-gray-600 mb-2">Profit Margin</span>
                <span class="block text-xl font-bold text-gray-900">52.7%</span>
              </div>
            </div>
          </div>
          
          <!-- Charts Section -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white border border-gray-200 rounded-xl p-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend</h3>
              <div class="h-64">
                <div class="h-full flex flex-col justify-end">
                  <div class="flex items-end justify-between w-full h-5/6 px-4">
                    <div class="w-8 bg-gradient-to-t from-teal-400 to-teal-200 rounded-t-lg" style="height: 60%"></div>
                    <div class="w-8 bg-gradient-to-t from-teal-400 to-teal-200 rounded-t-lg" style="height: 75%"></div>
                    <div class="w-8 bg-gradient-to-t from-teal-400 to-teal-200 rounded-t-lg" style="height: 85%"></div>
                    <div class="w-8 bg-gradient-to-t from-teal-400 to-teal-200 rounded-t-lg" style="height: 70%"></div>
                    <div class="w-8 bg-gradient-to-t from-teal-400 to-teal-200 rounded-t-lg" style="height: 90%"></div>
                    <div class="w-8 bg-gradient-to-t from-teal-400 to-teal-200 rounded-t-lg" style="height: 95%"></div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-xl p-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">Expense Breakdown</h3>
              <div class="h-64">
                <div class="h-full flex flex-col lg:flex-row items-center justify-center gap-8">
                  <div class="relative w-32 h-32 rounded-full bg-gray-100 overflow-hidden">
                    <div class="absolute inset-0 origin-center bg-blue-400" style="transform: rotate(0deg) skewY(60deg)"></div>
                    <div class="absolute inset-0 origin-center bg-green-400" style="transform: rotate(162deg) skewY(60deg)"></div>
                    <div class="absolute inset-0 origin-center bg-yellow-400" style="transform: rotate(270deg) skewY(60deg)"></div>
                    <div class="absolute inset-0 origin-center bg-red-400" style="transform: rotate(324deg) skewY(60deg)"></div>
                  </div>
                  <div class="space-y-2">
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                      <span class="w-3 h-3 rounded-full bg-blue-400"></span>
                      <span>Supplies (45%)</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                      <span class="w-3 h-3 rounded-full bg-green-400"></span>
                      <span>Payroll (30%)</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                      <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                      <span>Utilities (15%)</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-700">
                      <span class="w-3 h-3 rounded-full bg-red-400"></span>
                      <span>Other (10%)</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Data Tables -->
          <div class="space-y-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Top Revenue Sources</h3>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Growth</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">Paint Sales</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱589,200</td>
                  <td class="px-4 py-3 text-sm text-gray-700">47.3%</td>
                  <td class="px-4 py-3 text-sm font-medium text-green-600">↑ 12.5%</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">Service Fees</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱356,800</td>
                  <td class="px-4 py-3 text-sm text-gray-700">28.6%</td>
                  <td class="px-4 py-3 text-sm font-medium text-green-600">↑ 8.2%</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">Equipment Rental</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱189,500</td>
                  <td class="px-4 py-3 text-sm text-gray-700">15.2%</td>
                  <td class="px-4 py-3 text-sm font-medium text-green-600">↑ 15.7%</td>
                </tr>
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">Other Income</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱110,300</td>
                  <td class="px-4 py-3 text-sm text-gray-700">8.9%</td>
                  <td class="px-4 py-3 text-sm font-medium text-red-600">↓ 2.1%</td>
                </tr>
              </tbody>
            </table>
            
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Outstanding Invoices</h3>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">INV-2024-003</td>
                  <td class="px-4 py-3 text-sm text-gray-700">Maria Santos</td>
                  <td class="px-4 py-3 text-sm text-gray-700">2024-01-27</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱3,000</td>
                  <td class="px-4 py-3 text-sm">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Overdue</span>
                  </td>
                </tr>
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">INV-2024-006</td>
                  <td class="px-4 py-3 text-sm text-gray-700">Service Pro Inc.</td>
                  <td class="px-4 py-3 text-sm text-gray-700">2024-01-24</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱4,300</td>
                  <td class="px-4 py-3 text-sm">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Overdue</span>
                  </td>
                </tr>
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">INV-2024-010</td>
                  <td class="px-4 py-3 text-sm text-gray-700">Sarah Wilson</td>
                  <td class="px-4 py-3 text-sm text-gray-700">2024-01-20</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱3,100</td>
                  <td class="px-4 py-3 text-sm">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Overdue</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Report Footer -->
          <div class="border-t border-gray-200 pt-6 mt-6">
            <div class="flex flex-wrap justify-between text-sm text-gray-600">
              <p><strong>Report Generated By:</strong> Finance System</p>
              <p><strong>Date:</strong> {{ new Date().toLocaleDateString() }}</p>
              <p><strong>Page:</strong> 1 of 1</p>
            </div>
            <div class="text-center text-gray-400 mt-4">
              <span>CaviteGo Paint - Confidential</span>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-white">
          <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50" @click="showPreview = false">
            Close Preview
          </button>
          <button class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700" @click="exportReport">
            Export Now
          </button>
        </div>
      </div>
    </div>

    <!-- Recent Reports -->
    <div class="mb-8">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Recently Generated Reports</h2>
        <p class="text-sm text-gray-600">Access your previously generated reports</p>
      </div>
      
      <div class="space-y-4">
        <div v-for="report in recentReports" :key="report.id" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col sm:flex-row sm:items-center justify-between">
          <div class="flex items-center gap-4 mb-4 sm:mb-0">
            <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center flex-shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ report.name }}</h3>
              <p class="text-sm text-gray-600">
                Generated {{ report.generated }} • {{ report.period }} • {{ report.format }}
              </p>
            </div>
          </div>
          <div class="flex gap-2">
            <button class="px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors duration-200 flex items-center gap-2" @click="viewReport(report)">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              View
            </button>
            <button class="px-3 py-1.5 bg-teal-600 text-white rounded-lg text-sm font-medium hover:bg-teal-700 transition-colors duration-200 flex items-center gap-2" @click="downloadReport(report)">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download
            </button>
          </div>
        </div>
      </div>
    </div>

    
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// Define icon components inline
const RevenueIcon = {
  template: `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  `
}

const CollectionIcon = {
  template: `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
  `
}

const OutstandingIcon = {
  template: `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
    </svg>
  `
}

const ProfitIcon = {
  template: `
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
    </svg>
  `
}

// State
const selectedType = ref('revenue')
const showPreview = ref(false)
const parameters = ref({
  period: 'monthly',
  startDate: new Date().toISOString().split('T')[0],
  endDate: new Date().toISOString().split('T')[0],
  format: 'pdf',
  groupBy: 'month'
})

// Report types data
const reportTypes = ref([
  {
    id: 'revenue',
    name: 'Revenue Report',
    description: 'Detailed revenue analysis',
    icon: RevenueIcon,
    lastGenerated: '2 days ago'
  },
  {
    id: 'collection',
    name: 'Payment Collection',
    description: 'Payment collection performance',
    icon: CollectionIcon,
    lastGenerated: '1 week ago'
  },
  {
    id: 'outstanding',
    name: 'Outstanding Invoices',
    description: 'Pending and overdue invoices',
    icon: OutstandingIcon,
    lastGenerated: 'Today'
  },
  {
    id: 'profit',
    name: 'Profit & Loss',
    description: 'Comprehensive P&L statement',
    icon: ProfitIcon,
    lastGenerated: '3 days ago'
  }
])

// Recent reports
const recentReports = ref([
  {
    id: 1,
    name: 'Monthly Revenue Report - January 2024',
    generated: '2 days ago',
    period: 'January 2024',
    format: 'PDF',
    size: '2.4 MB'
  },
  {
    id: 2,
    name: 'Q4 2023 Collection Report',
    generated: '1 week ago',
    period: 'Q4 2023',
    format: 'Excel',
    size: '1.8 MB'
  },
  {
    id: 3,
    name: 'Outstanding Invoices - Weekly',
    generated: 'Today',
    period: 'This Week',
    format: 'CSV',
    size: '0.8 MB'
  },
  {
    id: 4,
    name: 'Annual Profit & Loss 2023',
    generated: '3 days ago',
    period: '2023',
    format: 'PDF',
    size: '3.2 MB'
  }
])

// Computed properties
const selectedReportType = computed(() => {
  return reportTypes.value.find(type => type.id === selectedType.value)
})

// Methods
const getPeriodText = () => {
  const periodMap = {
    daily: 'Daily',
    weekly: 'Weekly',
    monthly: 'Monthly',
    quarterly: 'Quarterly',
    yearly: 'Yearly',
    custom: `Custom (${parameters.value.startDate} to ${parameters.value.endDate})`
  }
  return periodMap[parameters.value.period] || parameters.value.period
}

const generateReport = () => {
  showPreview.value = true
}

const previewReport = () => {
  showPreview.value = true
}

const exportReport = () => {
  alert(`Exporting ${selectedReportType.value.name} as ${parameters.value.format.toUpperCase()}`)
}

const resetParameters = () => {
  parameters.value = {
    period: 'monthly',
    startDate: new Date().toISOString().split('T')[0],
    endDate: new Date().toISOString().split('T')[0],
    format: 'pdf',
    groupBy: 'month'
  }
}

const viewReport = (report) => {
  alert(`Viewing report: ${report.name}`)
}

const downloadReport = (report) => {
  alert(`Downloading report: ${report.name}`)
}
</script>

<style scoped>
/* Modal backdrop blur */
.backdrop-filter {
  backdrop-filter: blur(4px);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Report preview scrollbar */
.report-preview-content::-webkit-scrollbar {
  width: 8px;
}

.report-preview-content::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.report-preview-content::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 4px;
}

.report-preview-content::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .report-type-card {
    padding: 16px;
  }
  
  .report-preview .modal-content {
    margin: 0;
    border-radius: 0;
    max-height: 100vh;
  }
  
  .report-actions {
    flex-direction: column;
    width: 100%;
  }
  
  .report-actions button {
    width: 100%;
  }
}

/* Print styles for reports */
@media print {
  .report-preview {
    position: static;
    background: white;
  }
  
  .report-preview .modal-header,
  .report-preview .modal-footer {
    display: none;
  }
  
  .report-preview .modal-content {
    max-height: none;
    overflow: visible;
    box-shadow: none;
  }
}
</style>