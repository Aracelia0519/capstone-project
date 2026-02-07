<template>
  <div class="ecommerce-reports p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Analytics & Reports</h1>
          <p class="text-gray-300">Analyze e-commerce data for business decisions</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <Button @click="generateReport" 
                  class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-0 hover:opacity-90">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Generate Report
          </Button>
          <Button @click="showReportBuilder = true" 
                  class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0 hover:opacity-90">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Custom Report
          </Button>
        </div>
      </div>
    </div>

    <Card class="mb-6 bg-gray-900/50 border-gray-800">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="space-y-2">
            <Label class="text-gray-300">Report Type</Label>
            <Select v-model="reportType">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="Select report type" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="sales">Sales Report</SelectItem>
                <SelectItem value="products">Product Performance</SelectItem>
                <SelectItem value="distributors">Revenue by Distributor</SelectItem>
                <SelectItem value="orders">Order Fulfillment</SelectItem>
                <SelectItem value="customers">Customer Analytics</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Date Range</Label>
            <Select v-model="dateRange">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
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
            <Input type="date" v-model="fromDate" 
                   class="bg-gray-800 border-gray-700 text-white" />
          </div>
          <div v-if="dateRange === 'custom'" class="space-y-2">
            <Label class="text-gray-300">To Date</Label>
            <Input type="date" v-model="toDate" 
                   class="bg-gray-800 border-gray-700 text-white" />
          </div>
          <div class="flex items-end">
            <Button @click="applyFilters" 
                    class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white border-0 hover:opacity-90">
              Apply Filters
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">₱{{ keyMetrics.totalSales.toLocaleString() }}</div>
          <div class="text-sm text-gray-300">Total Sales</div>
          <div class="text-xs text-green-400 mt-2">↑ 18% from last period</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ keyMetrics.totalOrders }}</div>
          <div class="text-sm text-gray-300">Total Orders</div>
          <div class="text-xs text-green-400 mt-2">↑ 12% from last period</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ keyMetrics.averageOrderValue.toLocaleString() }}</div>
          <div class="text-sm text-gray-300">Avg Order Value</div>
          <div class="text-xs text-green-400 mt-2">↑ 8% from last period</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ keyMetrics.conversionRate }}%</div>
          <div class="text-sm text-gray-300">Conversion Rate</div>
          <div class="text-xs text-green-400 mt-2">↑ 2.5% from last period</div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Sales Trend</h3>
            <Select v-model="salesChartPeriod">
              <SelectTrigger class="w-[120px] bg-gray-800 border-gray-700 text-gray-300 h-8">
                <SelectValue placeholder="Period" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="weekly">Weekly</SelectItem>
                <SelectItem value="monthly">Monthly</SelectItem>
                <SelectItem value="quarterly">Quarterly</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="h-64">
            <div class="flex items-end justify-between h-full space-x-1">
              <div v-for="point in salesData" :key="point.label" class="flex flex-col items-center flex-1">
                <div class="text-xs text-gray-400 mb-2">{{ point.label }}</div>
                <div class="relative w-full flex justify-center">
                  <div class="w-3/4 bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-lg transition-all hover:opacity-80"
                       :style="{ height: (point.value / maxSalesValue) * 100 + '%' }"
                       :title="'₱' + point.value.toLocaleString()"></div>
                </div>
              </div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-gray-700/50">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-300">Total: ₱{{ totalSalesValue.toLocaleString() }}</span>
              <span class="text-sm text-green-400">+{{ salesGrowth }}% growth</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold text-white mb-6">Top Selling Products</h3>
          <div class="space-y-4">
            <div v-for="product in topProducts" :key="product.name" class="space-y-2">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <span class="text-gray-300">{{ product.name }}</span>
                </div>
                <span class="text-white font-medium">₱{{ product.revenue.toLocaleString() }}</span>
              </div>
              <Progress :model-value="product.marketShare" 
                       class="h-2 bg-gray-700" 
                       indicator-class="bg-gradient-to-r from-emerald-500 to-teal-500" />
              <div class="flex justify-between text-xs text-gray-400">
                <span>{{ product.orders }} orders</span>
                <span>{{ product.marketShare }}% market share</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="mb-8">
      <h3 class="text-xl font-bold text-white mb-6">Revenue by Distributor</h3>
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
          <Table>
            <TableHeader class="bg-gray-900/80">
              <TableRow class="hover:bg-transparent border-gray-800">
                <TableHead class="text-gray-300">Distributor</TableHead>
                <TableHead class="text-gray-300">Total Revenue</TableHead>
                <TableHead class="text-gray-300">Orders</TableHead>
                <TableHead class="text-gray-300">Avg. Order Value</TableHead>
                <TableHead class="text-gray-300">Growth</TableHead>
                <TableHead class="text-gray-300">Performance</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="distributor in distributorPerformance" :key="distributor.name" 
                  class="border-gray-800 hover:bg-white/5">
                <TableCell>
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs mr-3">
                      {{ distributor.name.charAt(0) }}
                    </div>
                    <span class="text-gray-300">{{ distributor.name }}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="text-white font-medium">₱{{ distributor.revenue.toLocaleString() }}</div>
                </TableCell>
                <TableCell>
                  <div class="text-gray-300">{{ distributor.orders }}</div>
                </TableCell>
                <TableCell>
                  <div class="text-gray-300">₱{{ distributor.avgOrderValue.toLocaleString() }}</div>
                </TableCell>
                <TableCell>
                  <div :class="[
                    'flex items-center',
                    distributor.growth >= 0 ? 'text-green-400' : 'text-red-400'
                  ]">
                    <svg v-if="distributor.growth >= 0" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                    {{ Math.abs(distributor.growth) }}%
                  </div>
                </TableCell>
                <TableCell>
                  <div class="w-24">
                    <Progress :model-value="distributor.performance" 
                             class="h-2 bg-gray-700" 
                             :indicator-class="distributor.performance >= 80 ? 'bg-green-500' : 
                                             distributor.performance >= 60 ? 'bg-amber-500' : 'bg-red-500'" />
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold text-white mb-6">Order Fulfillment Timeline</h3>
          <div class="space-y-6">
            <div v-for="stage in fulfillmentStages" :key="stage.name" class="flex items-center">
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center mr-4',
                stage.completed ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gray-700'
              ]">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="stage.name === 'Order Placed'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  <path v-if="stage.name === 'Payment Received'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  <path v-if="stage.name === 'Processing'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  <path v-if="stage.name === 'Shipped'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                  <path v-if="stage.name === 'Delivered'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div class="flex-1">
                <div class="flex justify-between mb-1">
                  <span class="text-white">{{ stage.name }}</span>
                  <span class="text-gray-400">{{ stage.time }}</span>
                </div>
                <Progress :model-value="stage.progress" 
                         class="h-1 bg-gray-700" 
                         :indicator-class="stage.completed ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gray-600'" />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold text-white mb-6">Export Options</h3>
          <div class="space-y-4">
            <Button @click="exportReport('pdf')" variant="outline" 
                   class="w-full h-auto p-4 bg-gradient-to-r from-red-500/20 to-pink-500/20 border-red-500/30 hover:from-red-500/30 hover:to-pink-500/30 hover:text-white justify-between">
              <div class="flex items-center text-left">
                <svg class="w-6 h-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div>
                  <div class="text-white font-medium">Export as PDF</div>
                  <div class="text-sm text-gray-400">High-quality document format</div>
                </div>
              </div>
              <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </Button>
            
            <Button @click="exportReport('csv')" variant="outline" 
                   class="w-full h-auto p-4 bg-gradient-to-r from-green-500/20 to-emerald-500/20 border-green-500/30 hover:from-green-500/30 hover:to-emerald-500/30 hover:text-white justify-between">
              <div class="flex items-center text-left">
                <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <div>
                  <div class="text-white font-medium">Export as CSV</div>
                  <div class="text-sm text-gray-400">Spreadsheet compatible format</div>
                </div>
              </div>
              <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </Button>
            
            <Button @click="exportReport('excel')" variant="outline" 
                   class="w-full h-auto p-4 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 border-blue-500/30 hover:from-blue-500/30 hover:to-cyan-500/30 hover:text-white justify-between">
              <div class="flex items-center text-left">
                <svg class="w-6 h-6 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <div>
                  <div class="text-white font-medium">Export as Excel</div>
                  <div class="text-sm text-gray-400">Advanced spreadsheet format</div>
                </div>
              </div>
              <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <Dialog v-model:open="showReportBuilder">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-4xl">
        <DialogHeader>
          <DialogTitle>Custom Report Builder</DialogTitle>
        </DialogHeader>
        
        <div class="space-y-6 pt-4">
          <div>
            <h4 class="text-lg font-semibold text-white mb-4">Select Metrics</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div v-for="metric in availableMetrics" :key="metric.id" 
                   class="flex items-center">
                <Checkbox :id="'metric-' + metric.id" 
                       :checked="selectedMetrics.includes(metric.id)"
                       @update:checked="(checked) => {
                         if (checked) selectedMetrics.push(metric.id)
                         else selectedMetrics = selectedMetrics.filter(id => id !== metric.id)
                       }"
                       class="border-gray-500 data-[state=checked]:bg-blue-500 data-[state=checked]:border-blue-500" />
                <label :for="'metric-' + metric.id" class="ml-2 text-gray-300 text-sm cursor-pointer">
                  {{ metric.name }}
                </label>
              </div>
            </div>
          </div>
          
          <div>
            <h4 class="text-lg font-semibold text-white mb-4">Chart Type</h4>
            <div class="grid grid-cols-4 gap-4">
              <button v-for="chart in chartTypes" :key="chart.type" 
                     @click="selectedChart = chart.type"
                     :class="[
                       'p-4 rounded-xl border flex flex-col items-center justify-center transition-all',
                       selectedChart === chart.type ? 
                       'bg-blue-500/20 border-blue-500/50' : 
                       'bg-gray-800/50 border-gray-700 hover:border-gray-600'
                     ]">
                <svg class="w-8 h-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="chart.type === 'bar'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  <path v-if="chart.type === 'line'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                  <path v-if="chart.type === 'pie'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                  <path v-if="chart.type === 'table'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="text-white text-sm">{{ chart.name }}</span>
              </button>
            </div>
          </div>
          
          <div>
            <h4 class="text-lg font-semibold text-white mb-4">Report Preview</h4>
            <div class="bg-gray-800/50 rounded-xl p-6 h-64 flex items-center justify-center border border-gray-700">
              <div class="text-center">
                <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <p class="text-gray-400">Report preview will appear here</p>
                <p class="text-sm text-gray-500 mt-1">Selected metrics: {{ selectedMetrics.length }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
          <Button variant="outline" @click="showReportBuilder = false" 
                 class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
            Cancel
          </Button>
          <Button @click="generateCustomReport" 
                 class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0 hover:opacity-90">
            Generate Custom Report
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
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

const reportType = ref('sales')
const dateRange = ref('month')
const fromDate = ref('2024-03-01')
const toDate = ref('2024-03-31')
const salesChartPeriod = ref('weekly')
const showReportBuilder = ref(false)
const selectedMetrics = ref([])
const selectedChart = ref('bar')

const keyMetrics = ref({
  totalSales: 124567,
  totalOrders: 156,
  averageOrderValue: 7985,
  conversionRate: 12.5
})

const salesData = ref([
  { label: 'Mon', value: 8450 },
  { label: 'Tue', value: 12670 },
  { label: 'Wed', value: 15890 },
  { label: 'Thu', value: 11230 },
  { label: 'Fri', value: 16980 },
  { label: 'Sat', value: 19120 },
  { label: 'Sun', value: 14890 }
])

const topProducts = ref([
  { name: 'Premium White Paint', revenue: 45670, orders: 156, marketShare: 32 },
  { name: 'Weatherproof Exterior', revenue: 38920, orders: 128, marketShare: 28 },
  { name: 'Eco-Friendly Paint', revenue: 28450, orders: 95, marketShare: 20 },
  { name: 'Quick Dry Primer', revenue: 18950, orders: 63, marketShare: 13 },
  { name: 'Gloss Finish Coating', revenue: 11230, orders: 38, marketShare: 7 }
])

const distributorPerformance = ref([
  { name: 'CaviteGo Distributors', revenue: 56780, orders: 45, avgOrderValue: 12617, growth: 15, performance: 85 },
  { name: 'Premium Paint Supply', revenue: 42340, orders: 38, avgOrderValue: 11142, growth: 12, performance: 78 },
  { name: 'Metro Paint Hub', revenue: 31250, orders: 28, avgOrderValue: 11160, growth: 8, performance: 65 },
  { name: 'South Paint Distributors', revenue: 26780, orders: 25, avgOrderValue: 10712, growth: -2, performance: 55 }
])

const fulfillmentStages = ref([
  { name: 'Order Placed', time: '2 min', completed: true, progress: 100 },
  { name: 'Payment Received', time: '5 min', completed: true, progress: 100 },
  { name: 'Processing', time: '15 min', completed: true, progress: 100 },
  { name: 'Shipped', time: '2 hours', completed: true, progress: 100 },
  { name: 'Delivered', time: '1-2 days', completed: false, progress: 80 }
])

const availableMetrics = ref([
  { id: 'sales', name: 'Total Sales' },
  { id: 'orders', name: 'Number of Orders' },
  { id: 'avg_order', name: 'Average Order Value' },
  { id: 'conversion', name: 'Conversion Rate' },
  { id: 'customers', name: 'New Customers' },
  { id: 'returns', name: 'Return Rate' },
  { id: 'revenue', name: 'Revenue by Category' },
  { id: 'products', name: 'Top Products' }
])

const chartTypes = ref([
  { type: 'bar', name: 'Bar Chart' },
  { type: 'line', name: 'Line Chart' },
  { type: 'pie', name: 'Pie Chart' },
  { type: 'table', name: 'Data Table' }
])

const maxSalesValue = computed(() => {
  return Math.max(...salesData.value.map(d => d.value))
})

const totalSalesValue = computed(() => {
  return salesData.value.reduce((sum, d) => sum + d.value, 0)
})

const salesGrowth = computed(() => {
  // Simplified growth calculation
  return 18
})

const applyFilters = () => {
  alert(`Applying filters: ${reportType.value} for ${dateRange.value}`)
}

const generateReport = () => {
  alert(`Generating ${reportType.value} report for ${dateRange.value}`)
}

const exportReport = (format) => {
  alert(`Exporting report as ${format.toUpperCase()}`)
}

const generateCustomReport = () => {
  if (selectedMetrics.value.length === 0) {
    alert('Please select at least one metric')
    return
  }
  
  alert(`Generating custom report with ${selectedMetrics.value.length} metrics as ${selectedChart} chart`)
  showReportBuilder.value = false
  selectedMetrics.value = []
  selectedChart.value = 'bar'
}
</script>

<style scoped>
.ecommerce-reports {
  min-height: 100vh;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-reports {
    padding: 1rem;
  }
}
</style>