<template>
  <div class="ecommerce-reports p-3 sm:p-4 md:p-6 overflow-x-hidden">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-1 md:mb-2">Analytics & Reports</h1>
          <p class="text-sm md:text-base text-gray-300">Analyze e-commerce data and procurement requests</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
          <Button @click="triggerExport('full')" 
                  class="w-full sm:w-auto bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-0 hover:opacity-90 justify-center">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="truncate">Generate Full Report</span>
          </Button>
          <Button @click="showReportBuilder = true" 
                  class="w-full sm:w-auto bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0 hover:opacity-90 justify-center">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="truncate">Custom Report</span>
          </Button>
        </div>
      </div>
    </div>

    <Card class="mb-6 bg-gray-900/50 border-gray-800">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
          <div class="space-y-2">
            <Label class="text-gray-300">Report Type</Label>
            <Select v-model="reportType">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white w-full">
                <SelectValue placeholder="Select report type" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="sales">Overall Report</SelectItem>
                <SelectItem value="products">Product Performance</SelectItem>
                <SelectItem value="procurement">Procurement Data</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Date Range</Label>
            <Select v-model="dateRange">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white w-full">
                <SelectValue placeholder="Select date range" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="today">Today</SelectItem>
                <SelectItem value="week">This Week</SelectItem>
                <SelectItem value="month">This Month</SelectItem>
                <SelectItem value="quarter">This Quarter</SelectItem>
                <SelectItem value="year">This Year</SelectItem>
                <SelectItem value="custom">Custom Range</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div v-if="dateRange === 'custom'" class="space-y-2">
            <Label class="text-gray-300">From Date</Label>
            <Input type="date" v-model="fromDate" class="bg-gray-800 border-gray-700 text-white w-full" />
          </div>
          <div v-if="dateRange === 'custom'" class="space-y-2">
            <Label class="text-gray-300">To Date</Label>
            <Input type="date" v-model="toDate" class="bg-gray-800 border-gray-700 text-white w-full" />
          </div>
          <div class="flex items-end sm:col-span-2 md:col-span-1 pt-2 sm:pt-0">
            <Button @click="fetchReportData" :disabled="isLoading"
                    class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0 hover:opacity-90">
              <span v-if="isLoading">Loading...</span>
              <span v-else>Apply Filters</span>
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 md:gap-4 mb-8">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-gray-800 text-white">
        <CardContent class="p-4 flex flex-row lg:flex-col justify-between items-center lg:items-start">
          <div class="text-xs md:text-sm text-gray-300 order-2 lg:order-1 lg:mt-1">Total Sales</div>
          <div class="text-lg md:text-2xl font-bold order-1 lg:order-2 truncate max-w-full" :title="'₱' + formatNumber(keyMetrics.totalSales)">₱{{ formatNumber(keyMetrics.totalSales) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardContent class="p-4 flex flex-row lg:flex-col justify-between items-center lg:items-start">
          <div class="text-xs md:text-sm text-gray-300 order-2 lg:order-1 lg:mt-1">Total Orders</div>
          <div class="text-lg md:text-2xl font-bold order-1 lg:order-2">{{ formatInt(keyMetrics.totalOrders) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardContent class="p-4 flex flex-row lg:flex-col justify-between items-center lg:items-start">
          <div class="text-xs md:text-sm text-gray-300 order-2 lg:order-1 lg:mt-1">Avg Order Value</div>
          <div class="text-lg md:text-2xl font-bold order-1 lg:order-2 truncate max-w-full" :title="'₱' + formatNumber(keyMetrics.averageOrderValue)">₱{{ formatNumber(keyMetrics.averageOrderValue) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardContent class="p-4 flex flex-row lg:flex-col justify-between items-center lg:items-start">
          <div class="text-xs md:text-sm text-gray-300 order-2 lg:order-1 lg:mt-1">Items Sold</div>
          <div class="text-lg md:text-2xl font-bold order-1 lg:order-2">{{ formatInt(keyMetrics.totalItemsSold) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-red-500/20 to-pink-500/20 border-gray-800 text-white">
        <CardContent class="p-4 flex flex-row lg:flex-col justify-between items-center lg:items-start">
          <div class="text-xs md:text-sm text-gray-300 order-2 lg:order-1 lg:mt-1">Procurement Spent</div>
          <div class="text-lg md:text-2xl font-bold order-1 lg:order-2 truncate max-w-full" :title="'₱' + formatNumber(keyMetrics.totalProcurementCost)">₱{{ formatNumber(keyMetrics.totalProcurementCost) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-gray-500/20 to-slate-500/20 border-gray-800 text-white">
        <CardContent class="p-4 flex flex-row lg:flex-col justify-between items-center lg:items-start">
          <div class="text-xs md:text-sm text-gray-300 order-2 lg:order-1 lg:mt-1">Procurement Requests</div>
          <div class="text-lg md:text-2xl font-bold order-1 lg:order-2">{{ formatInt(keyMetrics.totalProcurementRequests) }}</div>
        </CardContent>
      </Card>
    </div>

    <div class="mb-8">
      <div class="flex items-center mb-4 md:mb-6">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 flex items-center justify-center mr-3 md:mr-4 shadow-lg shadow-purple-500/20 flex-shrink-0">
          <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </div>
        <div>
          <h3 class="text-lg md:text-xl font-bold text-white">Procurement Decision Support System</h3>
          <p class="text-xs md:text-sm text-gray-400">Dynamic restock recommendations based on low stock alerts and sales velocity</p>
        </div>
      </div>
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden">
        <div class="overflow-x-auto w-full">
          <div class="min-w-[800px]">
            <Table>
              <TableHeader class="bg-gray-900/80 sticky top-0">
                <TableRow class="hover:bg-transparent border-gray-800">
                  <TableHead class="text-gray-300 whitespace-nowrap">Product Analysis</TableHead>
                  <TableHead class="text-gray-300 text-center whitespace-nowrap">Items Sold (Period)</TableHead>
                  <TableHead class="text-gray-300 text-center whitespace-nowrap">Current Stock</TableHead>
                  <TableHead class="text-gray-300 text-center whitespace-nowrap">Action Status</TableHead>
                  <TableHead class="text-gray-300 text-center whitespace-nowrap">Suggested Procure Qty</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="dssRecommendations.length === 0">
                  <TableCell colspan="5" class="text-center text-gray-500 py-8">
                    No critical procurement recommendations at this time. All products are well-stocked.
                  </TableCell>
                </TableRow>
                <TableRow v-for="item in dssRecommendations" :key="item.id" class="border-gray-800 hover:bg-white/5">
                  <TableCell>
                    <div class="text-white font-medium whitespace-nowrap">{{ item.name }}</div>
                    <div class="text-xs text-gray-400 mt-1 max-w-[250px] whitespace-normal">{{ item.reason }}</div>
                  </TableCell>
                  <TableCell class="text-center">
                    <span class="text-gray-300">{{ formatInt(item.sold_in_period) }}</span>
                  </TableCell>
                  <TableCell class="text-center">
                    <span :class="['font-bold text-lg', item.current_stock === 0 ? 'text-red-400' : 'text-white']">
                      {{ formatInt(item.current_stock) }}
                    </span>
                  </TableCell>
                  <TableCell class="text-center">
                    <span :class="[
                      'px-3 py-1 rounded-full text-xs font-semibold border whitespace-nowrap', 
                      item.priority === 'High' ? 'bg-red-500/10 text-red-400 border-red-500/30' : 
                      (item.priority === 'Medium' ? 'bg-amber-500/10 text-amber-400 border-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30')
                    ]">
                      {{ item.priority }} Priority
                    </span>
                  </TableCell>
                  <TableCell class="text-center">
                    <div class="flex items-center justify-center">
                      <span v-if="item.suggested_quantity > 0" class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400 font-bold text-xl">
                        +{{ formatInt(item.suggested_quantity) }}
                      </span>
                      <span v-else class="text-gray-600">-</span>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </Card>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white w-full overflow-hidden">
        <CardContent class="p-4 md:p-6 w-full">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Sales Trend</h3>
          </div>
          <div class="overflow-x-auto w-full pb-2">
            <div class="h-64 min-w-[500px]">
              <div v-if="salesData.length === 0" class="flex h-full items-center justify-center text-gray-500 w-full">
                No sales data for selected period.
              </div>
              <div v-else class="flex items-end justify-between h-full space-x-2">
                <div v-for="point in salesData" :key="point.label" class="flex flex-col items-center flex-1">
                  <div class="text-[10px] md:text-xs text-gray-400 mb-2 truncate w-full text-center" :title="point.label">{{ point.label }}</div>
                  <div class="relative w-full flex justify-center">
                    <div class="w-full max-w-[40px] bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-sm md:rounded-t-lg transition-all hover:opacity-80"
                         :style="{ height: (point.value / maxSalesValue) * 100 + '%' }"
                         :title="'₱' + formatNumber(point.value)"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-700/50">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-300">Total Selection: ₱{{ formatNumber(keyMetrics.totalSales) }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white w-full">
        <CardContent class="p-4 md:p-6">
          <h3 class="text-lg font-semibold text-white mb-6">Top Selling Products</h3>
          <div v-if="topProducts.length === 0" class="text-gray-500 text-center mt-10">
            No product data found for selected period.
          </div>
          <div v-else class="space-y-4 overflow-y-auto max-h-[300px] pr-2">
            <div v-for="product in topProducts" :key="product.name" class="space-y-3">
              <div class="flex items-start sm:items-center justify-between flex-col sm:flex-row gap-2 sm:gap-0">
                <div class="flex items-center w-full sm:w-auto">
                  <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <span class="text-gray-300 truncate max-w-xs sm:max-w-[200px]" :title="product.name">{{ product.name }}</span>
                </div>
                <span class="text-white font-medium self-end sm:self-auto">₱{{ formatNumber(product.revenue) }}</span>
              </div>
              <Progress :model-value="product.marketShare" 
                       class="h-2 bg-gray-700" 
                       indicator-class="bg-gradient-to-r from-emerald-500 to-teal-500" />
              <div class="flex justify-between text-xs text-gray-400">
                <span>{{ formatInt(product.orders) }} items sold</span>
                <span>{{ product.marketShare }}% sales share</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="mb-8">
      <h3 class="text-lg md:text-xl font-bold text-white mb-4 md:mb-6">Procurement History</h3>
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
          <div class="min-w-[800px]">
            <Table>
              <TableHeader class="bg-gray-900/80 sticky top-0">
                <TableRow class="hover:bg-transparent border-gray-800">
                  <TableHead class="text-gray-300 whitespace-nowrap">Req Code</TableHead>
                  <TableHead class="text-gray-300 whitespace-nowrap">Product</TableHead>
                  <TableHead class="text-gray-300 whitespace-nowrap">Quantity</TableHead>
                  <TableHead class="text-gray-300 whitespace-nowrap">Total Cost</TableHead>
                  <TableHead class="text-gray-300 whitespace-nowrap">Status</TableHead>
                  <TableHead class="text-gray-300 whitespace-nowrap">Date</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="procurements.length === 0">
                  <TableCell colspan="6" class="text-center text-gray-500 py-6">No procurement data found for selected period.</TableCell>
                </TableRow>
                <TableRow v-for="proc in procurements" :key="proc.request_code" class="border-gray-800 hover:bg-white/5">
                  <TableCell><span class="text-gray-300 whitespace-nowrap">{{ proc.request_code }}</span></TableCell>
                  <TableCell>
                    <span class="text-gray-300 block max-w-[200px] truncate" :title="proc.product_name">
                      {{ proc.product_name }}
                    </span>
                  </TableCell>
                  <TableCell><span class="text-gray-300">{{ formatInt(proc.quantity) }}</span></TableCell>
                  <TableCell><span class="text-white font-medium whitespace-nowrap">₱{{ formatNumber(proc.total_cost) }}</span></TableCell>
                  <TableCell>
                    <span :class="['px-2 py-1 rounded text-xs whitespace-nowrap', proc.status === 'delivered' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400']">
                      {{ proc.status }}
                    </span>
                  </TableCell>
                  <TableCell><span class="text-gray-400 text-sm whitespace-nowrap">{{ formatDate(proc.created_at) }}</span></TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </Card>
    </div>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white mb-8">
      <CardContent class="p-4 md:p-6">
        <h3 class="text-lg font-semibold text-white mb-4 md:mb-6">Export Options</h3>
        <div class="space-y-4">
          <Button @click="triggerExport('full')" variant="outline" 
                 class="w-full h-auto p-4 bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-green-500/30 hover:from-green-500/30 hover:to-emerald-500/30 hover:text-white justify-between flex-col sm:flex-row gap-3 sm:gap-0 items-start sm:items-center">
            <div class="flex flex-col sm:flex-row items-start sm:items-center text-left w-full">
              <svg class="w-6 h-6 text-green-400 mb-2 sm:mb-0 mr-0 sm:mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <div>
                <div class="text-white font-medium">Export Complete Data (CSV)</div>
                <div class="text-xs md:text-sm text-gray-400 pr-2">Downloads metrics, products, and procurements</div>
              </div>
            </div>
            <svg class="w-5 h-5 text-green-400 self-end sm:self-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </Button>
        </div>
      </CardContent>
    </Card>

    <Dialog v-model:open="showReportBuilder">
      <DialogContent class="bg-gray-900 border-gray-800 text-white w-[95vw] sm:max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-lg md:text-xl">Custom Report Builder</DialogTitle>
        </DialogHeader>
        <div class="space-y-4 pt-2 md:pt-4">
          <p class="text-gray-400 text-sm mb-4">Select the specific datasets you want to include in your customized CSV export.</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-800/50 rounded-lg border border-gray-700 cursor-pointer hover:bg-gray-800 transition-colors">
              <input type="checkbox" v-model="customConfig.metrics" class="w-5 h-5 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700" />
              <span class="text-white font-medium text-sm md:text-base">Key Summary Metrics</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-800/50 rounded-lg border border-gray-700 cursor-pointer hover:bg-gray-800 transition-colors">
              <input type="checkbox" v-model="customConfig.sales" class="w-5 h-5 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700" />
              <span class="text-white font-medium text-sm md:text-base">Sales Trend Data</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-800/50 rounded-lg border border-gray-700 cursor-pointer hover:bg-gray-800 transition-colors">
              <input type="checkbox" v-model="customConfig.products" class="w-5 h-5 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700" />
              <span class="text-white font-medium text-sm md:text-base">Top Selling Products</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-800/50 rounded-lg border border-gray-700 cursor-pointer hover:bg-gray-800 transition-colors">
              <input type="checkbox" v-model="customConfig.dss" class="w-5 h-5 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700" />
              <span class="text-white font-medium text-sm md:text-base">DSS Recommendations</span>
            </label>
            <label class="flex items-center space-x-3 p-3 md:p-4 bg-gray-800/50 rounded-lg border border-gray-700 cursor-pointer hover:bg-gray-800 transition-colors md:col-span-2">
              <input type="checkbox" v-model="customConfig.procurements" class="w-5 h-5 rounded border-gray-600 text-blue-500 focus:ring-blue-500 bg-gray-700" />
              <span class="text-white font-medium text-sm md:text-base">Procurement History</span>
            </label>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-6 pt-4 border-t border-gray-800">
            <Button @click="showReportBuilder = false" variant="outline" class="w-full sm:w-auto border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white">Cancel</Button>
            <Button @click="triggerExport('custom')" :disabled="!hasCustomSelection" class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0 hover:opacity-90 disabled:opacity-50">
              Generate Custom Report
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showConfirmDialog">
      <DialogContent class="bg-gray-900 border-gray-800 text-white w-[90vw] sm:max-w-md">
        <DialogHeader>
          <DialogTitle class="text-lg md:text-xl flex items-center text-amber-400">
            <svg class="w-6 h-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Confirm Export
          </DialogTitle>
        </DialogHeader>
        <div class="py-2 md:py-4">
          <p class="text-sm md:text-base text-gray-300">Are you sure you want to generate and download the <strong class="text-white">{{ exportType === 'full' ? 'Full' : 'Custom' }} Report</strong>? This will compile the selected data for the period of <strong>{{ dateRange.toUpperCase() }}</strong>.</p>
        </div>
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-4">
          <Button variant="outline" @click="showConfirmDialog = false" class="w-full sm:w-auto border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white">Cancel</Button>
          <Button @click="confirmExport" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white">Yes, Download CSV</Button>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios' 
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
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

// State variables
const isLoading = ref(false)
const reportType = ref('sales')
const dateRange = ref('year')

const today = new Date()
const startOfYear = new Date(today.getFullYear(), 0, 1)
const fromDate = ref(startOfYear.toISOString().split('T')[0])
const toDate = ref(today.toISOString().split('T')[0])

// Dialog States
const showReportBuilder = ref(false)
const showConfirmDialog = ref(false)
const exportType = ref('full') 

// Custom Report Configuration
const customConfig = ref({
  metrics: true,
  sales: false,
  products: true,
  dss: false,
  procurements: true
})

// Reactive Data from API
const keyMetrics = ref({
  totalSales: 0,
  totalOrders: 0,
  averageOrderValue: 0,
  totalItemsSold: 0,
  totalProcurementCost: 0,
  totalProcurementRequests: 0
})

const salesData = ref([])
const topProducts = ref([])
const procurements = ref([])
const dssRecommendations = ref([])

// Computed properties
const maxSalesValue = computed(() => {
  if (salesData.value.length === 0) return 1
  return Math.max(...salesData.value.map(d => d.value))
})

const hasCustomSelection = computed(() => {
  return customConfig.value.metrics || 
         customConfig.value.sales || 
         customConfig.value.products || 
         customConfig.value.dss || 
         customConfig.value.procurements
})

// Utility formatters
const formatNumber = (num) => {
  if (!num) return '0.00'
  return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// FIX: New function for integer metrics without decimals
const formatInt = (num) => {
  if (!num) return '0'
  return parseInt(num, 10).toLocaleString('en-US')
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

// Fetch Data
const fetchReportData = async () => {
  try {
    isLoading.value = true
    
    const params = {
      dateRange: dateRange.value,
    }
    
    if (dateRange.value === 'custom') {
      params.fromDate = fromDate.value
      params.toDate = toDate.value
    }

    const response = await api.get('/operation-distributor/reports', { params })
    
    if (response.data.success) {
      keyMetrics.value = response.data.keyMetrics
      salesData.value = response.data.salesData
      topProducts.value = response.data.topProducts
      procurements.value = response.data.procurements
      dssRecommendations.value = response.data.dssRecommendations
    }
  } catch (error) {
    console.error("Error fetching report data:", error)
    alert("Failed to fetch report data. Check permissions or network connection.")
  } finally {
    isLoading.value = false
  }
}

// Dialog Triggers
const triggerExport = (type) => {
  exportType.value = type
  showConfirmDialog.value = true
}

const confirmExport = () => {
  showConfirmDialog.value = false
  generateCSV(exportType.value)
}

// Generate CSV Report Logic (Fixed using Blob)
const generateCSV = (type) => {
  let csvContent = "\uFEFF" // Add BOM for Excel UTF-8 rendering

  // 1. Add Summary Metrics
  if (type === 'full' || (type === 'custom' && customConfig.value.metrics)) {
    csvContent += "REPORT SUMMARY\n"
    csvContent += `Report Range,${dateRange.value.toUpperCase()}\n`
    csvContent += `Total Sales,${keyMetrics.value.totalSales}\n`
    csvContent += `Total Orders,${keyMetrics.value.totalOrders}\n`
    csvContent += `Total Items Sold,${keyMetrics.value.totalItemsSold}\n`
    csvContent += `Total Procurement Spent,${keyMetrics.value.totalProcurementCost}\n`
    csvContent += `Total Procurement Requests,${keyMetrics.value.totalProcurementRequests}\n\n`
  }

  // 2. Add Sales Trend
  if (type === 'full' || (type === 'custom' && customConfig.value.sales)) {
    csvContent += "SALES TREND\n"
    csvContent += "Date,Revenue\n"
    salesData.value?.forEach(s => {
      csvContent += `"${s.label}",${s.value}\n`
    })
    csvContent += "\n"
  }

  // 3. Add Top Products
  if (type === 'full' || (type === 'custom' && customConfig.value.products)) {
    csvContent += "TOP PRODUCTS\n"
    csvContent += "Product Name,Revenue,Items Sold,Market Share (%)\n"
    topProducts.value?.forEach(p => {
      csvContent += `"${p.name}",${p.revenue},${p.orders},${p.marketShare}\n`
    })
    csvContent += "\n"
  }

  // 4. Add DSS Recommendations
  if (type === 'full' || (type === 'custom' && customConfig.value.dss)) {
    csvContent += "DECISION SUPPORT SYSTEM (PROCUREMENT RECOMMENDATIONS)\n"
    csvContent += "Product Name,Period Sold,Current Stock,Suggested Qty,Priority\n"
    dssRecommendations.value?.forEach(r => {
      csvContent += `"${r.name}",${r.sold_in_period},${r.current_stock},${r.suggested_quantity},"${r.priority}"\n`
    })
    csvContent += "\n"
  }

  // 5. Add Procurement Data
  if (type === 'full' || (type === 'custom' && customConfig.value.procurements)) {
    csvContent += "PROCUREMENT HISTORY\n"
    csvContent += "Request Code,Product Name,Quantity,Total Cost,Status,Date Created\n"
    procurements.value?.forEach(proc => {
      const d = new Date(proc.created_at).toISOString().split('T')[0]
      csvContent += `"${proc.request_code}","${proc.product_name}",${proc.quantity},${proc.total_cost},"${proc.status}",${d}\n`
    })
  }

  // Generate Download using Blob (Bypasses URL string limits)
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement("a")
  link.setAttribute("href", url)
  
  const filePrefix = type === 'custom' ? 'Custom' : 'Full'
  link.setAttribute("download", `Ecommerce_${filePrefix}_Report_${new Date().getTime()}.csv`)
  
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)

  // Clean up UI
  if (type === 'custom') {
    showReportBuilder.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchReportData()
})
</script>

<style scoped>
.ecommerce-reports {
  min-height: 100vh;
}
</style>