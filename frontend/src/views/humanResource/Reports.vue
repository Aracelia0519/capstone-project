<template>
  <div class="reports p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">HR Reports</h1>
        <p class="text-gray-600">Generate and export HR reports for analysis</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <button @click="generateReport" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Generate Report
        </button>
        <button @click="exportAll" class="flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Export All
        </button>
      </div>
    </div>

    <!-- Report Types Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <div v-for="report in reportTypes" :key="report.id" 
           class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:border-blue-200 transition-colors cursor-pointer"
           @click="selectReport(report)">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-lg mr-4" :class="report.bgClass">
            <component :is="report.icon" class="w-6 h-6" :class="report.iconClass" />
          </div>
          <span class="text-xs font-medium px-2 py-1 rounded-full bg-gray-100 text-gray-600">{{ report.type }}</span>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ report.title }}</h3>
        <p class="text-sm text-gray-600 mb-4">{{ report.description }}</p>
        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
          <span class="text-xs text-gray-500">Last generated: {{ report.lastGenerated }}</span>
          <button @click.stop="generateSpecificReport(report)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            Generate
          </button>
        </div>
      </div>
    </div>

    <!-- Report Configuration -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-6">Report Configuration</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Report Type Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
          <select v-model="selectedReportType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option v-for="report in reportTypes" :key="report.id" :value="report.id">{{ report.title }}</option>
          </select>
        </div>
        
        <!-- Date Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
          <div class="flex space-x-2">
            <input v-model="dateRange.start" type="date" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <input v-model="dateRange.end" type="date" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>
        
        <!-- Format Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Export Format</label>
          <div class="flex space-x-2">
            <button @click="exportFormat = 'PDF'" :class="exportFormat === 'PDF' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="flex-1 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
              PDF
            </button>
            <button @click="exportFormat = 'CSV'" :class="exportFormat === 'CSV' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="flex-1 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
              CSV
            </button>
            <button @click="exportFormat = 'Excel'" :class="exportFormat === 'Excel' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="flex-1 px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
              Excel
            </button>
          </div>
        </div>
      </div>
      
      <!-- Filters -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
          <select v-model="filters.department" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status</label>
          <select v-model="filters.status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Status</option>
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Include Data</label>
          <div class="flex space-x-4">
            <label class="flex items-center">
              <input v-model="filters.includeInactive" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">Inactive</span>
            </label>
            <label class="flex items-center">
              <input v-model="filters.includeHistory" type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
              <span class="ml-2 text-sm text-gray-700">History</span>
            </label>
          </div>
        </div>
      </div>
      
      <!-- Generate Button -->
      <div class="mt-8 pt-6 border-t border-gray-200">
        <button @click="generateCustomReport" class="w-full flex items-center justify-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Generate Custom Report
        </button>
      </div>
    </div>

    <!-- Report Preview -->
    <div v-if="showPreview" class="bg-white rounded-xl shadow-md p-6 mb-8">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Report Preview</h2>
        <div class="flex space-x-3">
          <button @click="downloadReport" class="flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download {{ exportFormat }}
          </button>
          <button @click="showPreview = false" class="flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Close
          </button>
        </div>
      </div>
      
      <!-- Preview Content -->
      <div class="border border-gray-200 rounded-lg overflow-hidden">
        <!-- Report Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-gray-800">{{ previewData.title }}</h3>
              <p class="text-sm text-gray-600">Generated on {{ previewData.generatedDate }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium text-gray-800">Total Records: {{ previewData.totalRecords }}</p>
              <p class="text-xs text-gray-600">Date Range: {{ previewData.dateRange }}</p>
            </div>
          </div>
        </div>
        
        <!-- Report Body -->
        <div class="p-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th v-for="column in previewData.columns" :key="column" 
                      class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    {{ column }}
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="(row, index) in previewData.rows" :key="index" class="hover:bg-gray-50">
                  <td v-for="(cell, cellIndex) in row" :key="cellIndex" 
                      class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                    {{ cell }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Summary -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Summary Statistics</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="stat in previewData.summary" :key="stat.label" class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-600 mb-1">{{ stat.label }}</p>
                <p class="text-lg font-semibold text-gray-800">{{ stat.value }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Reports -->
    <div class="bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Recent Reports</h2>
        <button @click="viewReportHistory" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
          View History
        </button>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Name</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="report in recentReports" :key="report.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="p-2 rounded-lg mr-3" :class="getReportTypeColor(report.type)">
                    <component :is="getReportIcon(report.type)" class="w-4 h-4" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-gray-800">{{ report.name }}</p>
                    <p class="text-xs text-gray-500">{{ report.description }}</p>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700">{{ report.type }}</span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ formatDate(report.generatedDate) }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <span class="text-sm text-gray-700">{{ report.format }}</span>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ report.size }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex space-x-3">
                  <button @click="downloadExistingReport(report)" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>
                  <button @click="deleteReport(report.id)" class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Note Section -->
    
  </div>
</template>

<script setup>
import { ref, defineComponent } from 'vue'

// Define SVG icon components
const UsersIcon = defineComponent({
  template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.832.055-1.68.113-2.5.113-4.97 0-9-2.239-9-5s4.03-5 9-5c1.72 0 3.32.404 4.786 1.09" /></svg>`
})

const BuildingIcon = defineComponent({
  template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>`
})

const ChartBarIcon = defineComponent({
  template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>`
})

const CalendarIcon = defineComponent({
  template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`
})

const DocumentTextIcon = defineComponent({
  template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`
})

const ClockIcon = defineComponent({
  template: `<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
})

const selectedReportType = ref(1)
const exportFormat = ref('PDF')
const showPreview = ref(false)

const dateRange = ref({
  start: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  end: new Date().toISOString().split('T')[0]
})

const filters = ref({
  department: '',
  status: '',
  includeInactive: true,
  includeHistory: false
})

const departments = ['Administration', 'Human Resources', 'Finance', 'Logistics', 'Operations']
const statusOptions = ['Active', 'Inactive', 'Probationary', 'Resigned', 'Terminated']

const reportTypes = ref([
  { id: 1, type: 'Employee', title: 'Employee Count by Department', description: 'Total number of employees grouped by department', 
    icon: UsersIcon, bgClass: 'bg-blue-100', iconClass: 'text-blue-600', lastGenerated: 'Today' },
  { id: 2, type: 'Status', title: 'Active vs Inactive Employees', description: 'Comparison of active and inactive workforce', 
    icon: ChartBarIcon, bgClass: 'bg-green-100', iconClass: 'text-green-600', lastGenerated: 'Yesterday' },
  { id: 3, type: 'Hiring', title: 'New Hires by Date Range', description: 'Employees hired within specified period', 
    icon: CalendarIcon, bgClass: 'bg-purple-100', iconClass: 'text-purple-600', lastGenerated: 'This week' },
  { id: 4, type: 'Department', title: 'Department Overview Report', description: 'Detailed analysis per department', 
    icon: BuildingIcon, bgClass: 'bg-yellow-100', iconClass: 'text-yellow-600', lastGenerated: 'Last week' },
  { id: 5, type: 'Status', title: 'Employment Status Report', description: 'Distribution of employment status types', 
    icon: DocumentTextIcon, bgClass: 'bg-red-100', iconClass: 'text-red-600', lastGenerated: '2 days ago' },
  { id: 6, type: 'History', title: 'Status Change History', description: 'Track employment status changes over time', 
    icon: ClockIcon, bgClass: 'bg-indigo-100', iconClass: 'text-indigo-600', lastGenerated: 'Last month' },
])

const recentReports = ref([
  { id: 1, name: 'Q4 Employee Report', description: 'Year-end employee analysis', type: 'Employee', generatedDate: '2024-01-15', format: 'PDF', size: '2.4 MB' },
  { id: 2, name: 'Department Headcount', description: 'Monthly department statistics', type: 'Department', generatedDate: '2024-01-10', format: 'Excel', size: '1.8 MB' },
  { id: 3, name: 'New Hires - December', description: 'December hiring summary', type: 'Hiring', generatedDate: '2024-01-05', format: 'CSV', size: '1.2 MB' },
  { id: 4, name: 'Status Distribution', description: 'Employment status overview', type: 'Status', generatedDate: '2024-01-02', format: 'PDF', size: '1.5 MB' },
])

const previewData = ref({
  title: '',
  generatedDate: '',
  totalRecords: 0,
  dateRange: '',
  columns: [],
  rows: [],
  summary: []
})

const selectReport = (report) => {
  selectedReportType.value = report.id
}

const generateSpecificReport = (report) => {
  // Generate sample data based on report type
  const today = new Date().toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
  
  previewData.value.title = report.title
  previewData.value.generatedDate = today
  previewData.value.dateRange = `${dateRange.value.start} to ${dateRange.value.end}`
  
  switch(report.type) {
    case 'Employee':
      previewData.value.columns = ['Department', 'Employee Count', 'Active', 'Inactive', 'Percentage']
      previewData.value.rows = [
        ['Administration', '24', '22', '2', '15%'],
        ['Human Resources', '18', '17', '1', '12%'],
        ['Finance', '32', '30', '2', '21%'],
        ['Logistics', '45', '42', '3', '29%'],
        ['Operations', '37', '35', '2', '23%']
      ]
      previewData.value.summary = [
        { label: 'Total Employees', value: '156' },
        { label: 'Active Employees', value: '146' },
        { label: 'Inactive Employees', value: '10' },
        { label: 'Departments', value: '5' }
      ]
      break
      
    case 'Status':
      previewData.value.columns = ['Status', 'Count', 'Percentage', 'Department', 'Last Updated']
      previewData.value.rows = [
        ['Active', '142', '91%', 'All', today],
        ['Inactive', '6', '4%', 'All', today],
        ['Probationary', '8', '5%', 'All', today],
        ['Resigned', '4', '3%', 'All', today],
        ['Terminated', '2', '1%', 'All', today]
      ]
      previewData.value.summary = [
        { label: 'Total Status Types', value: '5' },
        { label: 'Most Common', value: 'Active' },
        { label: 'Active Rate', value: '91%' },
        { label: 'Turnover', value: '4%' }
      ]
      break
      
    case 'Hiring':
      previewData.value.columns = ['Name', 'Employee ID', 'Department', 'Position', 'Hire Date', 'Status']
      previewData.value.rows = [
        ['Mike Wilson', 'EMP-003', 'Logistics', 'Logistics Staff', '2024-01-05', 'Probationary'],
        ['Jennifer Lee', 'EMP-008', 'Finance', 'Finance Officer', '2023-12-15', 'Active'],
        ['Thomas Clark', 'EMP-009', 'Logistics', 'Logistics Staff', '2023-11-20', 'Active'],
        ['Amanda White', 'EMP-010', 'Administration', 'Admin', '2023-11-01', 'Resigned']
      ]
      previewData.value.summary = [
        { label: 'Total New Hires', value: '4' },
        { label: 'Average Hire Date', value: 'Nov-Dec 2023' },
        { label: 'Most Hires Department', value: 'Logistics' },
        { label: 'Retention Rate', value: '75%' }
      ]
      break
  }
  
  previewData.value.totalRecords = previewData.value.rows.length
  showPreview.value = true
}

const generateCustomReport = () => {
  const selectedReport = reportTypes.value.find(r => r.id === selectedReportType.value)
  if (selectedReport) {
    generateSpecificReport(selectedReport)
  }
}

const generateReport = () => {
  // Default to first report type
  generateSpecificReport(reportTypes.value[0])
}

const downloadReport = () => {
  console.log(`Downloading report as ${exportFormat.value}...`)
  alert(`Report downloaded successfully as ${exportFormat.value}!`)
}

const exportAll = () => {
  console.log('Exporting all reports...')
  alert('All reports exported successfully!')
}

const downloadExistingReport = (report) => {
  console.log(`Downloading report: ${report.name}`)
  alert(`Downloading ${report.name} as ${report.format}...`)
}

const deleteReport = (reportId) => {
  if (confirm('Are you sure you want to delete this report?')) {
    recentReports.value = recentReports.value.filter(r => r.id !== reportId)
  }
}

const viewReportHistory = () => {
  console.log('Viewing report history...')
  // In a real app, navigate to full history page
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getReportTypeColor = (type) => {
  const colors = {
    'Employee': 'bg-blue-100 text-blue-600',
    'Department': 'bg-yellow-100 text-yellow-600',
    'Status': 'bg-green-100 text-green-600',
    'Hiring': 'bg-purple-100 text-purple-600',
    'History': 'bg-indigo-100 text-indigo-600'
  }
  return colors[type] || 'bg-gray-100 text-gray-600'
}

const getReportIcon = (type) => {
  const icons = {
    'Employee': UsersIcon,
    'Department': BuildingIcon,
    'Status': ChartBarIcon,
    'Hiring': CalendarIcon,
    'History': ClockIcon
  }
  return icons[type] || DocumentTextIcon
}
</script>

<style scoped>
.reports {
  min-height: calc(100vh - 80px);
}
</style>