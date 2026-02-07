<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
        <Button class="bg-teal-600 hover:bg-teal-700 text-white gap-2" @click="generateReport">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Generate Report</span>
        </Button>
      </div>
    </div>

    <div class="mb-8">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Report Types</h2>
        <p class="text-sm text-gray-600">Select report type to generate</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card 
          v-for="type in reportTypes" 
          :key="type.id"
          class="border-2 cursor-pointer transition-all duration-200 hover:border-teal-300 hover:shadow-md"
          :class="{ 'border-teal-500 bg-teal-50': selectedType === type.id, 'border-gray-200': selectedType !== type.id }"
          @click="selectedType = type.id"
        >
          <div class="p-6">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
              :class="selectedType === type.id ? 'bg-teal-200 text-teal-700' : 'bg-teal-100 text-teal-600'">
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
        </Card>
      </div>
    </div>

    <Card class="mb-8 border-gray-200">
      <div class="p-6">
        <div class="mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Report Parameters</h2>
          <p class="text-sm text-gray-600">Configure report settings</p>
        </div>
        
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Period</label>
              <Select v-model="parameters.period">
                <SelectTrigger class="focus:ring-teal-500">
                  <SelectValue placeholder="Select Period" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="daily">Daily</SelectItem>
                  <SelectItem value="weekly">Weekly</SelectItem>
                  <SelectItem value="monthly">Monthly</SelectItem>
                  <SelectItem value="quarterly">Quarterly</SelectItem>
                  <SelectItem value="yearly">Yearly</SelectItem>
                  <SelectItem value="custom">Custom Range</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div v-if="parameters.period === 'custom'" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Start Date</label>
              <Input type="date" v-model="parameters.startDate" class="focus-visible:ring-teal-500" />
            </div>
            
            <div v-if="parameters.period === 'custom'" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">End Date</label>
              <Input type="date" v-model="parameters.endDate" class="focus-visible:ring-teal-500" />
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Format</label>
              <Select v-model="parameters.format">
                <SelectTrigger class="focus:ring-teal-500">
                  <SelectValue placeholder="Select Format" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pdf">PDF</SelectItem>
                  <SelectItem value="csv">CSV</SelectItem>
                  <SelectItem value="excel">Excel</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Group By</label>
              <Select v-model="parameters.groupBy">
                <SelectTrigger class="focus:ring-teal-500">
                  <SelectValue placeholder="Group By" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="day">Day</SelectItem>
                  <SelectItem value="week">Week</SelectItem>
                  <SelectItem value="month">Month</SelectItem>
                  <SelectItem value="category">Category</SelectItem>
                  <SelectItem value="payment_method">Payment Method</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          
          <div class="flex flex-wrap gap-3 justify-end">
            <Button variant="outline" @click="resetParameters">
              Reset
            </Button>
            <Button class="bg-teal-600 hover:bg-teal-700 text-white" @click="previewReport">
              Preview Report
            </Button>
            <Button class="bg-green-600 hover:bg-green-700 text-white" @click="exportReport">
              Export Report
            </Button>
          </div>
        </div>
      </div>
    </Card>

    <Dialog v-model:open="showPreview">
      <DialogContent class="max-w-6xl max-h-[90vh] flex flex-col p-0 gap-0 overflow-hidden">
        <DialogHeader class="p-6 border-b border-gray-200">
          <DialogTitle>Report Preview</DialogTitle>
        </DialogHeader>
        
        <div class="p-6 overflow-y-auto report-preview-content flex-1">
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
          
          <div class="space-y-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Top Revenue Sources</h3>
            <Table>
              <TableHeader class="bg-gray-50">
                <TableRow>
                  <TableHead>Source</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Percentage</TableHead>
                  <TableHead>Growth</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow>
                  <TableCell class="font-medium text-gray-700">Paint Sales</TableCell>
                  <TableCell class="text-gray-700">₱589,200</TableCell>
                  <TableCell class="text-gray-700">47.3%</TableCell>
                  <TableCell class="font-medium text-green-600">↑ 12.5%</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell class="font-medium text-gray-700">Service Fees</TableCell>
                  <TableCell class="text-gray-700">₱356,800</TableCell>
                  <TableCell class="text-gray-700">28.6%</TableCell>
                  <TableCell class="font-medium text-green-600">↑ 8.2%</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell class="font-medium text-gray-700">Equipment Rental</TableCell>
                  <TableCell class="text-gray-700">₱189,500</TableCell>
                  <TableCell class="text-gray-700">15.2%</TableCell>
                  <TableCell class="font-medium text-green-600">↑ 15.7%</TableCell>
                </TableRow>
                <TableRow>
                  <TableCell class="font-medium text-gray-700">Other Income</TableCell>
                  <TableCell class="text-gray-700">₱110,300</TableCell>
                  <TableCell class="text-gray-700">8.9%</TableCell>
                  <TableCell class="font-medium text-red-600">↓ 2.1%</TableCell>
                </TableRow>
              </TableBody>
            </Table>
            
            <h3 class="text-lg font-semibold text-gray-900 mb-3">Outstanding Invoices</h3>
            <Table>
              <TableHeader class="bg-gray-50">
                <TableRow>
                  <TableHead>Invoice #</TableHead>
                  <TableHead>Client</TableHead>
                  <TableHead>Due Date</TableHead>
                  <TableHead>Amount</TableHead>
                  <TableHead>Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow>
                  <TableCell class="text-gray-700">INV-2024-003</TableCell>
                  <TableCell class="text-gray-700">Maria Santos</TableCell>
                  <TableCell class="text-gray-700">2024-01-27</TableCell>
                  <TableCell class="text-gray-700">₱3,000</TableCell>
                  <TableCell>
                    <Badge variant="destructive" class="bg-red-100 text-red-800 hover:bg-red-100 shadow-none">Overdue</Badge>
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell class="text-gray-700">INV-2024-006</TableCell>
                  <TableCell class="text-gray-700">Service Pro Inc.</TableCell>
                  <TableCell class="text-gray-700">2024-01-24</TableCell>
                  <TableCell class="text-gray-700">₱4,300</TableCell>
                  <TableCell>
                    <Badge variant="destructive" class="bg-red-100 text-red-800 hover:bg-red-100 shadow-none">Overdue</Badge>
                  </TableCell>
                </TableRow>
                <TableRow>
                  <TableCell class="text-gray-700">INV-2024-010</TableCell>
                  <TableCell class="text-gray-700">Sarah Wilson</TableCell>
                  <TableCell class="text-gray-700">2024-01-20</TableCell>
                  <TableCell class="text-gray-700">₱3,100</TableCell>
                  <TableCell>
                    <Badge variant="destructive" class="bg-red-100 text-red-800 hover:bg-red-100 shadow-none">Overdue</Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
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
        
        <DialogFooter class="p-6 border-t border-gray-200 bg-white">
          <Button variant="outline" @click="showPreview = false">
            Close Preview
          </Button>
          <Button class="bg-teal-600 hover:bg-teal-700 text-white" @click="exportReport">
            Export Now
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <div class="mb-8">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Recently Generated Reports</h2>
        <p class="text-sm text-gray-600">Access your previously generated reports</p>
      </div>
      
      <div class="space-y-4">
        <Card v-for="report in recentReports" :key="report.id" class="p-6 border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between">
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
              <Button variant="outline" class="gap-2 text-gray-700" size="sm" @click="viewReport(report)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                View
              </Button>
              <Button class="bg-teal-600 hover:bg-teal-700 text-white gap-2" size="sm" @click="downloadReport(report)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download
              </Button>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

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

@media print {
  .report-preview {
    position: static;
    background: white;
  }
}
</style>