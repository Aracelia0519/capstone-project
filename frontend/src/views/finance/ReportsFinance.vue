<template>
  <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-6 overflow-x-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-teal-100 text-teal-600 rounded-lg shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </span>
          Financial Reports
        </h1>
        <p class="text-sm text-gray-600">Summarize and analyze financial data</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <button @click="triggerExport('full')" 
                class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-gradient-to-r from-teal-600 to-emerald-600 text-white rounded-lg shadow-sm hover:opacity-90 transition-opacity">
          <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Export Full CSV
        </button>
        <button @click="showReportBuilder = true" 
                class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg shadow-sm hover:opacity-90 transition-opacity">
          <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Custom Report
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8 overflow-hidden">
      <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50/50">
        <h2 class="text-base font-semibold text-gray-800">Report Parameters</h2>
      </div>
      <div class="p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
          
          <div class="space-y-2">
            <Label class="block text-sm font-medium text-gray-700">Period Filter</Label>
            <Select v-model="parameters.period">
              <SelectTrigger class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm h-[42px] border bg-white text-gray-700">
                <SelectValue placeholder="Select period" />
              </SelectTrigger>
              <SelectContent class="bg-white border border-gray-200 shadow-md">
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
            <Label class="block text-sm font-medium text-gray-700">Start Date</Label>
            <Input type="date" v-model="parameters.startDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm h-[42px] px-3 border bg-white text-gray-700" />
          </div>
          
          <div v-if="parameters.period === 'custom'" class="space-y-2">
            <Label class="block text-sm font-medium text-gray-700">End Date</Label>
            <Input type="date" v-model="parameters.endDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm h-[42px] px-3 border bg-white text-gray-700" />
          </div>

          <div class="pt-2 sm:col-span-2 md:col-span-1 md:ml-auto w-full">
            <button @click="fetchData" :disabled="isLoading" class="w-full h-[42px] inline-flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 transition-colors">
              <span v-if="isLoading">Generating...</span>
              <span v-else>Generate Report</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-8">
      <h2 class="text-lg font-bold text-gray-900 mb-4">Core Financial Overview</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
        
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl p-5 shadow-sm text-white">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium opacity-90 text-sm">Lifetime Available Funds</h3>
            <svg class="w-6 h-6 opacity-75 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="text-2xl md:text-3xl font-bold truncate" :title="'₱' + formatNumber(reportData.overallSalesLifetime)">₱{{ formatNumber(reportData.overallSalesLifetime) }}</div>
          <div class="text-xs opacity-80 mt-2">Overall money collected (All-Time)</div>
        </div>

        <div class="bg-gradient-to-br from-teal-500 to-emerald-600 rounded-xl p-5 shadow-sm text-white">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium opacity-90 text-sm">Period Gross Sales</h3>
            <svg class="w-6 h-6 opacity-75 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <div class="text-2xl md:text-3xl font-bold truncate" :title="'₱' + formatNumber(reportData.totalSales)">₱{{ formatNumber(reportData.totalSales) }}</div>
          <div class="text-xs opacity-80 mt-2">Client & SP Orders during selected period</div>
        </div>

      </div>
    </div>

    <div class="mb-8">
      <h2 class="text-lg font-bold text-gray-900 mb-4">Period Expenses & Deductions Breakdown</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
          <div class="text-sm font-medium text-gray-500 mb-1">VAT Remitted</div>
          <div class="text-xl md:text-2xl font-bold text-gray-900 truncate">₱{{ formatNumber(reportData.totalVat) }}</div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
          <div class="text-sm font-medium text-gray-500 mb-1">Procurement Costs (Requests)</div>
          <div class="text-xl md:text-2xl font-bold text-gray-900 truncate">₱{{ formatNumber(reportData.procurementBudgetReleased) }}</div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
          <div class="text-sm font-medium text-gray-500 mb-1">Payroll Disbursed</div>
          <div class="text-xl md:text-2xl font-bold text-gray-900 truncate">₱{{ formatNumber(reportData.totalPayrollDisbursed) }}</div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200">
          <div class="text-sm font-medium text-gray-500 mb-1">Refunds Processed</div>
          <div class="text-xl md:text-2xl font-bold text-gray-900 truncate">₱{{ formatNumber(reportData.refundsProcessed) }}</div>
        </div>

      </div>
    </div>

    <div class="space-y-8">
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
          <h3 class="text-base font-bold text-gray-900">Sales Transactions</h3>
          <span class="bg-teal-100 text-teal-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ reportData.salesTransactions.length }} Records</span>
        </div>
        <div class="overflow-x-auto max-h-[300px] w-full">
          <Table class="min-w-full">
            <TableHeader class="bg-white sticky top-0 z-10 shadow-sm">
              <TableRow>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Reference</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Type</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Gross Amount</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">VAT Deducted</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="reportData.salesTransactions.length === 0">
                <TableCell colspan="5" class="text-center text-gray-500 py-6">No sales data found for selected period.</TableCell>
              </TableRow>
              <TableRow v-for="(item, index) in reportData.salesTransactions" :key="'sale-'+index" class="hover:bg-gray-50">
                <TableCell class="font-medium text-gray-900 whitespace-nowrap">{{ item.reference }}</TableCell>
                <TableCell class="text-gray-600 whitespace-nowrap">{{ item.type }}</TableCell>
                <TableCell class="text-right font-medium text-emerald-600 whitespace-nowrap">₱{{ formatNumber(item.amount) }}</TableCell>
                <TableCell class="text-right text-amber-600 whitespace-nowrap">- ₱{{ formatNumber(item.vat) }}</TableCell>
                <TableCell class="text-right text-gray-500 whitespace-nowrap">{{ formatDate(item.date) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
          <h3 class="text-base font-bold text-gray-900">Procurement Expenditures (From Requests)</h3>
          <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ reportData.procurementTransactions.length }} Records</span>
        </div>
        <div class="overflow-x-auto max-h-[300px] w-full">
          <Table class="min-w-full">
            <TableHeader class="bg-white sticky top-0 z-10 shadow-sm">
              <TableRow>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Req Code</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Product</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Status</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Total Cost</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="reportData.procurementTransactions.length === 0">
                <TableCell colspan="5" class="text-center text-gray-500 py-6">No released procurement data found for selected period.</TableCell>
              </TableRow>
              <TableRow v-for="(item, index) in reportData.procurementTransactions" :key="'proc-'+index" class="hover:bg-gray-50">
                <TableCell class="font-medium text-gray-900 whitespace-nowrap">{{ item.reference }}</TableCell>
                <TableCell class="text-gray-600 truncate max-w-xs" :title="item.description">{{ item.description }}</TableCell>
                <TableCell>
                  <span class="px-2 py-1 rounded text-xs whitespace-nowrap bg-blue-100 text-blue-800 border border-blue-200">{{ item.status }}</span>
                </TableCell>
                <TableCell class="text-right font-medium text-red-600 whitespace-nowrap">- ₱{{ formatNumber(item.amount) }}</TableCell>
                <TableCell class="text-right text-gray-500 whitespace-nowrap">{{ formatDate(item.date) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
          <h3 class="text-base font-bold text-gray-900">Budget Deduction Logs</h3>
          <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ reportData.budgetDeductionLogs.length }} Records</span>
        </div>
        <div class="overflow-x-auto max-h-[300px] w-full">
          <Table class="min-w-full">
            <TableHeader class="bg-white sticky top-0 z-10 shadow-sm">
              <TableRow>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Log ID</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Description</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Deducted Amount</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Date Logged</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="reportData.budgetDeductionLogs.length === 0">
                <TableCell colspan="4" class="text-center text-gray-500 py-6">No deduction logs found for selected period.</TableCell>
              </TableRow>
              <TableRow v-for="(item, index) in reportData.budgetDeductionLogs" :key="'log-'+index" class="hover:bg-gray-50">
                <TableCell class="font-medium text-gray-900 whitespace-nowrap">LOG-{{ item.reference }}</TableCell>
                <TableCell class="text-gray-600 truncate max-w-sm" :title="item.description">{{ item.description }}</TableCell>
                <TableCell class="text-right font-medium text-indigo-600 whitespace-nowrap">₱{{ formatNumber(item.amount) }}</TableCell>
                <TableCell class="text-right text-gray-500 whitespace-nowrap">{{ formatDate(item.date) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
          <h3 class="text-base font-bold text-gray-900">Payroll Disbursements</h3>
          <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ reportData.payrollTransactions.length }} Records</span>
        </div>
        <div class="overflow-x-auto max-h-[300px] w-full">
          <Table class="min-w-full">
            <TableHeader class="bg-white sticky top-0 z-10 shadow-sm">
              <TableRow>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Payment Ref</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Employee Name</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Net Pay</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Date Paid</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="reportData.payrollTransactions.length === 0">
                <TableCell colspan="4" class="text-center text-gray-500 py-6">No payroll data found for selected period.</TableCell>
              </TableRow>
              <TableRow v-for="(item, index) in reportData.payrollTransactions" :key="'pay-'+index" class="hover:bg-gray-50">
                <TableCell class="font-medium text-gray-900 whitespace-nowrap">{{ item.reference }}</TableCell>
                <TableCell class="text-gray-600 whitespace-nowrap">{{ item.description }}</TableCell>
                <TableCell class="text-right font-medium text-red-600 whitespace-nowrap">- ₱{{ formatNumber(item.amount) }}</TableCell>
                <TableCell class="text-right text-gray-500 whitespace-nowrap">{{ formatDate(item.date) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
          <h3 class="text-base font-bold text-gray-900">Refunds Processed</h3>
          <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded">{{ reportData.refundTransactions.length }} Records</span>
        </div>
        <div class="overflow-x-auto max-h-[300px] w-full">
          <Table class="min-w-full">
            <TableHeader class="bg-white sticky top-0 z-10 shadow-sm">
              <TableRow>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Refund ID</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700">Reason</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Amount Refunded</TableHead>
                <TableHead class="whitespace-nowrap font-semibold text-gray-700 text-right">Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="reportData.refundTransactions.length === 0">
                <TableCell colspan="4" class="text-center text-gray-500 py-6">No refunds processed during selected period.</TableCell>
              </TableRow>
              <TableRow v-for="(item, index) in reportData.refundTransactions" :key="'ref-'+index" class="hover:bg-gray-50">
                <TableCell class="font-medium text-gray-900 whitespace-nowrap">REF-{{ item.reference }}</TableCell>
                <TableCell class="text-gray-600 truncate max-w-xs" :title="item.description">{{ item.description }}</TableCell>
                <TableCell class="text-right font-medium text-red-600 whitespace-nowrap">- ₱{{ formatNumber(item.amount) }}</TableCell>
                <TableCell class="text-right text-gray-500 whitespace-nowrap">{{ formatDate(item.date) }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

    </div>

    <Dialog v-model:open="showReportBuilder">
      <DialogContent class="bg-white border-gray-200 text-gray-900 w-[95vw] sm:max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl">
        <DialogHeader>
          <DialogTitle class="text-lg md:text-xl font-bold">Custom Finance Report</DialogTitle>
        </DialogHeader>
        <div class="space-y-4 pt-2 md:pt-4">
          <p class="text-gray-600 text-sm mb-4">Select the specific datasets you want to include in your customized CSV export.</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" v-model="customConfig.metrics" class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 bg-white" />
              <span class="text-gray-800 font-medium text-sm md:text-base">Core Financial Overview</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" v-model="customConfig.sales" class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 bg-white" />
              <span class="text-gray-800 font-medium text-sm md:text-base">Sales Transactions</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" v-model="customConfig.procurement" class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 bg-white" />
              <span class="text-gray-800 font-medium text-sm md:text-base">Procurement Expenditures</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" v-model="customConfig.budgetLogs" class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 bg-white" />
              <span class="text-gray-800 font-medium text-sm md:text-base">Budget Deduction Logs</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" v-model="customConfig.payroll" class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 bg-white" />
              <span class="text-gray-800 font-medium text-sm md:text-base">Payroll Disbursements</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-100 transition-colors">
              <input type="checkbox" v-model="customConfig.refunds" class="w-5 h-5 rounded border-gray-300 text-teal-600 focus:ring-teal-500 bg-white" />
              <span class="text-gray-800 font-medium text-sm md:text-base">Refunds Processed</span>
            </label>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-6 pt-4 border-t border-gray-200">
            <button @click="showReportBuilder = false" class="w-full sm:w-auto px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Cancel</button>
            <button @click="triggerExport('custom')" :disabled="!hasCustomSelection" class="w-full sm:w-auto px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium shadow-sm transition-colors disabled:opacity-50">
              Generate Custom Report
            </button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showConfirmDialog">
      <DialogContent class="bg-white border-gray-200 text-gray-900 w-[90vw] sm:max-w-md shadow-xl rounded-xl">
        <DialogHeader>
          <DialogTitle class="text-lg md:text-xl flex items-center text-teal-700 font-bold">
            <svg class="w-6 h-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Confirm CSV Export
          </DialogTitle>
        </DialogHeader>
        <div class="py-2 md:py-4">
          <p class="text-sm md:text-base text-gray-600">Are you sure you want to generate and download the <strong class="text-gray-900">{{ exportType === 'full' ? 'Full' : 'Custom' }} Financial Report</strong>? This will compile the selected data for the <strong>{{ parameters.period.toUpperCase() }}</strong> period into a CSV file.</p>
        </div>
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-4">
          <button @click="showConfirmDialog = false" class="w-full sm:w-auto px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Cancel</button>
          <button @click="exportCSV" class="w-full sm:w-auto px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-medium shadow-sm transition-colors">Yes, Download CSV</button>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// State Variables
const isLoading = ref(false)
const showConfirmDialog = ref(false)
const showReportBuilder = ref(false)
const exportType = ref('full')

const parameters = ref({
  period: 'yearly',
  startDate: new Date(new Date().getFullYear(), 0, 1).toISOString().split('T')[0],
  endDate: new Date().toISOString().split('T')[0],
})

// Custom Report Configuration
const customConfig = ref({
  metrics: true,
  sales: true,
  procurement: true,
  budgetLogs: true,
  payroll: true,
  refunds: true
})

const hasCustomSelection = computed(() => {
  return customConfig.value.metrics || 
         customConfig.value.sales || 
         customConfig.value.procurement || 
         customConfig.value.budgetLogs ||
         customConfig.value.payroll || 
         customConfig.value.refunds
})

// Data payload returned from API
const reportData = ref({
  overallSalesLifetime: 0,
  totalSales: 0,
  totalVat: 0,
  procurementBudgetReleased: 0,
  refundsProcessed: 0,
  totalPayrollDisbursed: 0,
  
  // Data Tables Arrays
  salesTransactions: [],
  procurementTransactions: [],
  budgetDeductionLogs: [],
  payrollTransactions: [],
  refundTransactions: []
})

// Utilities
const formatNumber = (num) => {
  if (!num) return '0.00'
  return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' })
}

// Fetch Logic
const fetchData = async () => {
  try {
    isLoading.value = true
    
    const params = {
      period: parameters.value.period,
    }
    
    if (parameters.value.period === 'custom') {
      params.startDate = parameters.value.startDate
      params.endDate = parameters.value.endDate
    }

    const response = await api.get('/finance/reports', { params })
    
    if (response.data.success) {
      reportData.value = response.data.data
    }
  } catch (error) {
    console.error("Error fetching financial data:", error)
    alert("Failed to generate financial report. Check permissions or connection.")
  } finally {
    isLoading.value = false
  }
}

// Dialog Triggers
const triggerExport = (type) => {
  exportType.value = type
  showConfirmDialog.value = true
}

// CSV Export Logic (BUG FIX: using Blob instead of encodeURI to support huge tables!)
const exportCSV = () => {
  showConfirmDialog.value = false
  const type = exportType.value

  let csvContent = "\uFEFF" // Add BOM for Excel UTF-8 rendering

  // 1. Title & Summary Metrics
  if (type === 'full' || (type === 'custom' && customConfig.value.metrics)) {
    csvContent += "FINANCIAL REPORT SUMMARY\n"
    csvContent += `Period,${parameters.value.period.toUpperCase()}\n`
    if (parameters.value.period === 'custom') {
      csvContent += `Range,${parameters.value.startDate} to ${parameters.value.endDate}\n`
    }
    csvContent += "\n"

    csvContent += "Metric,Value (PHP)\n"
    csvContent += `Lifetime Available Funds (All-Time),${reportData.value.overallSalesLifetime}\n`
    csvContent += `Period Gross Sales,${reportData.value.totalSales}\n`
    csvContent += `Period VAT Deductions,${reportData.value.totalVat}\n`
    csvContent += `Procurement Expenditures (From Requests),${reportData.value.procurementBudgetReleased}\n`
    csvContent += `Payroll Disbursed,${reportData.value.totalPayrollDisbursed}\n`
    csvContent += `Refunds Processed,${reportData.value.refundsProcessed}\n\n`
  }

  // 2. Sales Transactions
  if (type === 'full' || (type === 'custom' && customConfig.value.sales)) {
    csvContent += "SALES TRANSACTIONS\n"
    csvContent += "Reference,Type,Gross Amount,VAT Deducted,Date\n"
    reportData.value.salesTransactions?.forEach(item => {
      csvContent += `"${item.reference}","${item.type}",${item.amount},${item.vat},"${item.date}"\n`
    })
    csvContent += "\n"
  }

  // 3. Procurement Expenditures
  if (type === 'full' || (type === 'custom' && customConfig.value.procurement)) {
    csvContent += "PROCUREMENT EXPENDITURES (REQUESTS)\n"
    csvContent += "Req Code,Product,Status,Total Cost,Date\n"
    reportData.value.procurementTransactions?.forEach(item => {
      csvContent += `"${item.reference}","${item.description}","${item.status}",${item.amount},"${item.date}"\n`
    })
    csvContent += "\n"
  }

  // 4. Budget Deduction Logs
  if (type === 'full' || (type === 'custom' && customConfig.value.budgetLogs)) {
    csvContent += "BUDGET DEDUCTION LOGS\n"
    csvContent += "Log ID,Description,Deducted Amount,Date Logged\n"
    reportData.value.budgetDeductionLogs?.forEach(item => {
      csvContent += `"LOG-${item.reference}","${item.description}",${item.amount},"${item.date}"\n`
    })
    csvContent += "\n"
  }

  // 5. Payroll Disbursements
  if (type === 'full' || (type === 'custom' && customConfig.value.payroll)) {
    csvContent += "PAYROLL DISBURSEMENTS\n"
    csvContent += "Payment Ref,Employee Name,Net Pay,Date Paid\n"
    reportData.value.payrollTransactions?.forEach(item => {
      csvContent += `"${item.reference}","${item.description}",${item.amount},"${item.date}"\n`
    })
    csvContent += "\n"
  }

  // 6. Refunds Processed
  if (type === 'full' || (type === 'custom' && customConfig.value.refunds)) {
    csvContent += "REFUNDS PROCESSED\n"
    csvContent += "Refund ID,Reason,Amount Refunded,Date\n"
    reportData.value.refundTransactions?.forEach(item => {
      csvContent += `"REF-${item.reference}","${item.description}",${item.amount},"${item.date}"\n`
    })
  }

  // Generate Download using Blob (Bypasses URL string limits)
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement("a")
  link.setAttribute("href", url)
  
  const filePrefix = type === 'custom' ? 'Custom' : 'Full'
  link.setAttribute("download", `Detailed_Finance_${filePrefix}_Report_${new Date().getTime()}.csv`)
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url) // Clean up memory

  if (type === 'custom') {
    showReportBuilder.value = false
  }
}

// Init
onMounted(() => {
  fetchData()
})
</script>