<template>
  <div class="min-h-screen  p-4 md:p-8 font-sans text-slate-50 selection:bg-indigo-500/30 overflow-x-hidden">
    
    <!-- Header -->
    <div class="mb-8 md:mb-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-slate-400 tracking-tight mb-2">
            Analytics & Reports
          </h1>
          <p class="text-sm md:text-base text-slate-400 font-medium">Analyze e-commerce data, product performance, and procurement</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
          <Button @click="triggerExport('full')" 
                  class="w-full sm:w-auto bg-gradient-to-r from-emerald-500 to-teal-600 text-white border-0 hover:opacity-90 shadow-lg shadow-emerald-900/20 justify-center transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="truncate font-semibold">Generate Full Report</span>
          </Button>
          <Button @click="showReportBuilder = true" 
                  class="w-full sm:w-auto bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 shadow-lg justify-center transition-all hover:-translate-y-0.5">
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="truncate font-semibold">Custom Report</span>
          </Button>
        </div>
      </div>
    </div>

    <!-- Filter Controls -->
    <Card class="mb-8 bg-slate-900/50 backdrop-blur-xl border-slate-800 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
      <CardContent class="p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-5">
          <div class="space-y-2">
            <Label class="text-slate-400 font-semibold text-xs uppercase tracking-wider">Report Type</Label>
            <Select v-model="reportType">
              <SelectTrigger class="bg-slate-950 border-slate-800 text-white w-full rounded-lg h-10">
                <SelectValue placeholder="Select report type" />
              </SelectTrigger>
              <SelectContent class="bg-slate-950 border-slate-800 text-white rounded-lg shadow-2xl">
                <SelectItem value="sales" class="focus:bg-slate-800 focus:text-white">Overall Report</SelectItem>
                <SelectItem value="products" class="focus:bg-slate-800 focus:text-white">Product Performance</SelectItem>
                <SelectItem value="procurement" class="focus:bg-slate-800 focus:text-white">Procurement Data</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-slate-400 font-semibold text-xs uppercase tracking-wider">Date Range</Label>
            <Select v-model="dateRange">
              <SelectTrigger class="bg-slate-950 border-slate-800 text-white w-full rounded-lg h-10">
                <SelectValue placeholder="Select date range" />
              </SelectTrigger>
              <SelectContent class="bg-slate-950 border-slate-800 text-white rounded-lg shadow-2xl">
                <SelectItem value="today" class="focus:bg-slate-800 focus:text-white">Today</SelectItem>
                <SelectItem value="week" class="focus:bg-slate-800 focus:text-white">This Week</SelectItem>
                <SelectItem value="month" class="focus:bg-slate-800 focus:text-white">This Month</SelectItem>
                <SelectItem value="quarter" class="focus:bg-slate-800 focus:text-white">This Quarter</SelectItem>
                <SelectItem value="year" class="focus:bg-slate-800 focus:text-white">This Year (12 Months)</SelectItem>
                <SelectItem value="custom" class="focus:bg-slate-800 focus:text-white">Custom Range</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div v-if="dateRange === 'custom'" class="space-y-2 animate-in fade-in">
            <Label class="text-slate-400 font-semibold text-xs uppercase tracking-wider">From Date</Label>
            <Input type="date" v-model="fromDate" class="bg-slate-950 border-slate-800 text-white w-full h-10 rounded-lg" />
          </div>
          <div v-if="dateRange === 'custom'" class="space-y-2 animate-in fade-in">
            <Label class="text-slate-400 font-semibold text-xs uppercase tracking-wider">To Date</Label>
            <Input type="date" v-model="toDate" class="bg-slate-950 border-slate-800 text-white w-full h-10 rounded-lg" />
          </div>
          <div class="flex items-end sm:col-span-2 md:col-span-1 pt-2 sm:pt-0">
            <Button @click="fetchReportData" :disabled="isLoading"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white border-0 shadow-lg shadow-indigo-900/20 h-10 rounded-lg font-semibold">
              <span v-if="isLoading" class="flex items-center gap-2">
                <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Loading
              </span>
              <span v-else>Apply Filters</span>
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 md:gap-6 mb-8 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200">
      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 group hover:border-indigo-500/30 transition-all duration-300 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-5 relative z-10 flex flex-col justify-center h-full">
          <div class="text-xs font-semibold text-slate-400 tracking-wide uppercase mb-1">Total Sales</div>
          <div class="text-2xl font-bold text-white truncate" :title="'₱' + formatNumber(keyMetrics.totalSales)">₱{{ formatNumber(keyMetrics.totalSales) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 group hover:border-emerald-500/30 transition-all duration-300 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-5 relative z-10 flex flex-col justify-center h-full">
          <div class="text-xs font-semibold text-slate-400 tracking-wide uppercase mb-1">Total Orders</div>
          <div class="text-2xl font-bold text-white">{{ formatInt(keyMetrics.totalOrders) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 group hover:border-amber-500/30 transition-all duration-300 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-5 relative z-10 flex flex-col justify-center h-full">
          <div class="text-xs font-semibold text-slate-400 tracking-wide uppercase mb-1">Avg Order Value</div>
          <div class="text-2xl font-bold text-white truncate" :title="'₱' + formatNumber(keyMetrics.averageOrderValue)">₱{{ formatNumber(keyMetrics.averageOrderValue) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 group hover:border-cyan-500/30 transition-all duration-300 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-5 relative z-10 flex flex-col justify-center h-full">
          <div class="text-xs font-semibold text-slate-400 tracking-wide uppercase mb-1">Items Sold</div>
          <div class="text-2xl font-bold text-white">{{ formatInt(keyMetrics.totalItemsSold) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 group hover:border-rose-500/30 transition-all duration-300 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-5 relative z-10 flex flex-col justify-center h-full">
          <div class="text-xs font-semibold text-slate-400 tracking-wide uppercase mb-1">Procurement Spent</div>
          <div class="text-2xl font-bold text-white truncate" :title="'₱' + formatNumber(keyMetrics.totalProcurementCost)">₱{{ formatNumber(keyMetrics.totalProcurementCost) }}</div>
        </CardContent>
      </Card>
      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 group hover:border-slate-500/30 transition-all duration-300 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-5 relative z-10 flex flex-col justify-center h-full">
          <div class="text-xs font-semibold text-slate-400 tracking-wide uppercase mb-1">Procurement Requests</div>
          <div class="text-2xl font-bold text-white">{{ formatInt(keyMetrics.totalProcurementRequests) }}</div>
        </CardContent>
      </Card>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8 animate-in fade-in slide-in-from-bottom-10 duration-700 delay-300">
      
      <!-- Shadcn-Style Area Chart for Sales Trend -->
      <Card class="xl:col-span-2 bg-slate-900/50 border border-slate-800 text-white flex flex-col relative overflow-hidden">
        <CardContent class="p-6 flex-1 flex flex-col relative z-10">
          <div class="flex items-center justify-between mb-8">
            <div>
              <h3 class="text-lg font-bold text-white">Sales Trend</h3>
              <p class="text-sm text-slate-400 mt-1">Revenue timeline for selected period</p>
            </div>
            <div class="text-right">
              <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Period Total</span>
              <div class="text-xl font-bold text-emerald-400">₱{{ formatNumber(keyMetrics.totalSales) }}</div>
            </div>
          </div>
          
          <div class="h-64 relative min-h-[250px] w-full flex-1 group">
            <!-- Background Grid -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none border-b border-slate-800">
              <div class="w-full border-t border-slate-800/40 h-0"></div>
              <div class="w-full border-t border-slate-800/40 h-0"></div>
              <div class="w-full border-t border-slate-800/40 h-0"></div>
              <div class="w-full border-t border-slate-800/40 h-0"></div>
            </div>

            <!-- SVG Line & Area -->
            <svg v-if="salesData.length > 0" viewBox="0 0 800 250" preserveAspectRatio="none" class="absolute inset-0 w-full h-full overflow-visible">
              <defs>
                <linearGradient id="colorSalesTrend" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stop-color="#10b981" stop-opacity="0.3"/>
                  <stop offset="95%" stop-color="#10b981" stop-opacity="0"/>
                </linearGradient>
              </defs>
              <!-- Filled Area -->
              <path :d="areaPath" fill="url(#colorSalesTrend)" class="transition-all duration-500 ease-in-out" />
              <!-- Stroke Line -->
              <path :d="linePath" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-in-out drop-shadow-md" />
              <!-- Interactive Data Points -->
              <circle v-for="(point, index) in chartPoints" :key="'point-'+index"
                      :cx="point.x" :cy="point.y" r="4" 
                      fill="#slate-950" stroke="#10b981" stroke-width="2" 
                      class="transition-all duration-300"
                      :class="hoveredPoint === index ? 'r-6 fill-[#10b981] stroke-white stroke-[3px]' : 'fill-slate-900 opacity-0 group-hover:opacity-100'"
              />
            </svg>

            <!-- Interactive Hover Overlay System -->
            <div class="absolute inset-0 flex" v-if="salesData.length > 0">
              <div v-for="(point, index) in chartPoints" :key="'overlay-'+index" 
                   class="flex-1 h-full relative group/col cursor-pointer"
                   @mouseenter="hoveredPoint = index"
                   @mouseleave="hoveredPoint = null">
                   
                <!-- Vertical Dashed Line -->
                <div class="absolute top-0 bottom-0 left-1/2 -translate-x-1/2 w-px border-l border-dashed border-slate-500/50 opacity-0 transition-opacity"
                     :class="{'opacity-100': hoveredPoint === index}"></div>

                <!-- Tooltip -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-4 px-3 py-2 bg-slate-800 text-white text-xs rounded-md shadow-xl border border-slate-700 pointer-events-none opacity-0 transition-all z-20 whitespace-nowrap"
                     :class="{'opacity-100 -translate-y-2': hoveredPoint === index}">
                  <div class="font-bold text-sm text-emerald-400">₱{{ formatNumber(point.value) }}</div>
                  <div class="text-slate-400 mt-0.5">{{ point.label }}</div>
                </div>
              </div>
            </div>

            <!-- Empty State Fallback -->
            <div v-if="!salesData.length" class="absolute inset-0 flex items-center justify-center text-slate-500 font-medium">
              No sales data for selected period.
            </div>
          </div>
          
          <!-- X-Axis Labels (Responsive hiding to prevent overlap) -->
          <div class="w-full flex justify-between mt-3 text-[10px] sm:text-xs font-medium text-slate-500 relative z-10" v-if="salesData.length > 0">
            <span v-for="(point, index) in chartPoints" :key="'label-'+point.label" 
                  class="flex-1 text-center truncate px-1"
                  :class="{'opacity-0 sm:opacity-100': salesData.length > 15 && index % 2 !== 0, 'hidden md:block': salesData.length > 30 && index % 3 !== 0}">
              {{ point.label }}
            </span>
          </div>
        </CardContent>
      </Card>

      <!-- Shadcn-Style Donut Chart for Top Products -->
      <Card class="bg-slate-900/50 border border-slate-800 text-white flex flex-col h-full">
        <CardContent class="p-6 flex-1 flex flex-col">
          <div class="mb-6">
            <h3 class="text-lg font-bold text-white mb-1">Top Selling Products</h3>
            <p class="text-sm text-slate-400">Revenue breakdown by product</p>
          </div>
          
          <div class="flex flex-col items-center justify-center gap-6 flex-1">
            <!-- Donut SVG -->
            <div class="relative w-44 h-44 flex-shrink-0" v-if="donutData.length > 0">
              <svg viewBox="0 0 42 42" class="w-full h-full -rotate-90 filter drop-shadow-xl">
                <!-- Inner Track -->
                <circle cx="21" cy="21" r="15.915494309189533" fill="transparent" stroke="#1e293b" stroke-width="6"></circle>
                <!-- Dynamic Slices -->
                <circle v-for="(slice, index) in donutData" :key="index"
                        cx="21" cy="21" r="15.915494309189533" fill="transparent"
                        :stroke="slice.color" stroke-width="6"
                        :stroke-dasharray="slice.strokeDasharray"
                        :stroke-dashoffset="slice.strokeDashoffset"
                        class="transition-all duration-300 ease-out cursor-pointer hover:stroke-[7]"
                        :class="{'opacity-100': activeSlice === index || activeSlice === null, 'opacity-20': activeSlice !== null && activeSlice !== index}"
                        @mouseenter="activeSlice = index"
                        @mouseleave="activeSlice = null"
                ></circle>
              </svg>
              <!-- Center Text Content -->
              <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest mb-0.5">Top Sales</span>
                <span class="text-xl font-bold text-white">₱{{ formatShortCurrency(totalTopProductsRevenue) }}</span>
              </div>
            </div>

            <!-- List / Legend -->
            <div class="w-full space-y-2 overflow-y-auto max-h-[160px] pr-2 custom-scrollbar" v-if="donutData.length > 0">
              <div v-for="(product, index) in donutData" :key="product.id" 
                   class="flex items-center justify-between p-2.5 rounded-lg hover:bg-slate-800/50 transition-colors cursor-pointer group"
                   @mouseenter="activeSlice = index"
                   @mouseleave="activeSlice = null">
                <div class="flex items-center gap-3 min-w-0">
                  <div class="w-3 h-3 flex-shrink-0 rounded-full shadow-[0_0_8px_rgba(0,0,0,0.5)] transition-transform group-hover:scale-125" :style="{ backgroundColor: product.color }"></div>
                  <h4 class="text-slate-300 font-medium text-sm truncate group-hover:text-white transition-colors" :title="product.name">
                    {{ product.name }}
                  </h4>
                </div>
                <div class="text-right flex-shrink-0 ml-2">
                  <div class="text-white font-bold text-sm">₱{{ formatNumber(product.revenue) }}</div>
                  <div class="text-[10px] font-medium text-slate-500">{{ product.marketShare }}% share</div>
                </div>
              </div>
            </div>
            
            <!-- Empty State Fallback -->
            <div v-if="donutData.length === 0" class="flex flex-col items-center justify-center w-full py-8 text-slate-500">
               <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
               <span>No product data found.</span>
            </div>

          </div>
        </CardContent>
      </Card>
    </div>

    <!-- DSS Table (Paginated) -->
    <div class="mb-8 animate-in fade-in slide-in-from-bottom-10 duration-700 delay-400">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4 md:mb-6">
        <div class="flex items-center">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mr-3 md:mr-4 shadow-lg shadow-purple-500/20 flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
          </div>
          <div>
            <h3 class="text-lg md:text-xl font-bold text-white">Procurement Decision Support System</h3>
            <p class="text-xs md:text-sm text-slate-400">Dynamic restock recommendations based on low stock alerts and sales velocity</p>
          </div>
        </div>
      </div>
      <Card class="bg-slate-900/50 backdrop-blur-xl border-slate-800 overflow-hidden shadow-xl">
        <div class="overflow-x-auto w-full">
          <Table>
            <TableHeader class="bg-slate-900 border-b border-slate-800">
              <TableRow class="hover:bg-transparent border-slate-800">
                <TableHead class="text-slate-400 font-semibold h-12 whitespace-nowrap">Product Analysis</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 text-center whitespace-nowrap">Items Sold</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 text-center whitespace-nowrap">Current Stock</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 text-center whitespace-nowrap">Action Status</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 text-center whitespace-nowrap">Suggested Procure Qty</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="paginatedDSS.length === 0">
                <TableCell colspan="5" class="text-center text-slate-500 py-10 font-medium">
                  No critical procurement recommendations at this time. All products are well-stocked.
                </TableCell>
              </TableRow>
              <TableRow v-for="item in paginatedDSS" :key="item.id" class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                <TableCell class="py-4">
                  <div class="text-white font-bold whitespace-nowrap">{{ item.name }}</div>
                  <div class="text-xs text-slate-400 mt-1 max-w-[250px] whitespace-normal">{{ item.reason }}</div>
                </TableCell>
                <TableCell class="text-center py-4">
                  <span class="text-slate-300 font-medium">{{ formatInt(item.sold_in_period) }}</span>
                </TableCell>
                <TableCell class="text-center py-4">
                  <span :class="['font-extrabold text-lg', item.current_stock === 0 ? 'text-rose-400' : 'text-white']">
                    {{ formatInt(item.current_stock) }}
                  </span>
                </TableCell>
                <TableCell class="text-center py-4">
                  <span :class="[
                    'px-3 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold border whitespace-nowrap inline-flex items-center gap-1.5', 
                    item.priority === 'High' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 
                    (item.priority === 'Medium' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20')
                  ]">
                    <div class="w-1.5 h-1.5 rounded-full" :class="item.priority === 'High' ? 'bg-rose-400 animate-pulse' : (item.priority === 'Medium' ? 'bg-amber-400' : 'bg-emerald-400')"></div>
                    {{ item.priority }} Priority
                  </span>
                </TableCell>
                <TableCell class="text-center py-4">
                  <div class="flex items-center justify-center">
                    <span v-if="item.suggested_quantity > 0" class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400 font-extrabold text-xl tracking-tight">
                      +{{ formatInt(item.suggested_quantity) }}
                    </span>
                    <span v-else class="text-slate-600 font-bold">-</span>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        <!-- Pagination Footer -->
        <div class="flex justify-between items-center px-6 py-4 border-t border-slate-800 bg-slate-900/30" v-if="dssTotalPages > 1">
           <span class="text-sm font-medium text-slate-400">Page {{ dssCurrentPage }} of {{ dssTotalPages }}</span>
           <div class="space-x-2">
             <Button variant="outline" size="sm" class="bg-slate-950 border-slate-800 text-slate-300 hover:text-white" @click="dssCurrentPage--" :disabled="dssCurrentPage === 1">Previous</Button>
             <Button variant="outline" size="sm" class="bg-slate-950 border-slate-800 text-slate-300 hover:text-white" @click="dssCurrentPage++" :disabled="dssCurrentPage === dssTotalPages">Next</Button>
           </div>
        </div>
      </Card>
    </div>

    <!-- Procurement History (Paginated) -->
    <div class="mb-8 animate-in fade-in slide-in-from-bottom-12 duration-700 delay-500">
      <h3 class="text-lg md:text-xl font-bold text-white mb-4 md:mb-6">Procurement History</h3>
      <Card class="bg-slate-900/50 backdrop-blur-xl border-slate-800 overflow-hidden w-full shadow-xl">
        <div class="overflow-x-auto w-full">
          <Table>
            <TableHeader class="bg-slate-900 border-b border-slate-800">
              <TableRow class="hover:bg-transparent border-slate-800">
                <TableHead class="text-slate-400 font-semibold h-12 whitespace-nowrap">Req Code</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 whitespace-nowrap">Product</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 whitespace-nowrap text-right">Quantity</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 whitespace-nowrap text-right">Total Cost</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 whitespace-nowrap text-center">Status</TableHead>
                <TableHead class="text-slate-400 font-semibold h-12 whitespace-nowrap text-right">Date</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="paginatedProcurements.length === 0">
                <TableCell colspan="6" class="text-center text-slate-500 py-10 font-medium">No procurement data found for selected period.</TableCell>
              </TableRow>
              <TableRow v-for="proc in paginatedProcurements" :key="proc.request_code" class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                <TableCell class="py-4"><span class="text-slate-300 font-mono text-sm whitespace-nowrap">{{ proc.request_code }}</span></TableCell>
                <TableCell class="py-4">
                  <span class="text-white font-medium block max-w-[200px] truncate" :title="proc.product_name">
                    {{ proc.product_name }}
                  </span>
                </TableCell>
                <TableCell class="py-4 text-right"><span class="text-slate-300 font-medium">{{ formatInt(proc.quantity) }}</span></TableCell>
                <TableCell class="py-4 text-right"><span class="text-white font-bold whitespace-nowrap tracking-tight">₱{{ formatNumber(proc.total_cost) }}</span></TableCell>
                <TableCell class="py-4 text-center">
                  <span :class="['px-2.5 py-1 rounded-md text-[10px] uppercase tracking-wider font-bold whitespace-nowrap', proc.status === 'delivered' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400']">
                    {{ proc.status }}
                  </span>
                </TableCell>
                <TableCell class="py-4 text-right"><span class="text-slate-400 text-sm whitespace-nowrap font-medium">{{ formatDate(proc.created_at) }}</span></TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        <!-- Pagination Footer -->
        <div class="flex justify-between items-center px-6 py-4 border-t border-slate-800 bg-slate-900/30" v-if="procTotalPages > 1">
           <span class="text-sm font-medium text-slate-400">Page {{ procCurrentPage }} of {{ procTotalPages }}</span>
           <div class="space-x-2">
             <Button variant="outline" size="sm" class="bg-slate-950 border-slate-800 text-slate-300 hover:text-white" @click="procCurrentPage--" :disabled="procCurrentPage === 1">Previous</Button>
             <Button variant="outline" size="sm" class="bg-slate-950 border-slate-800 text-slate-300 hover:text-white" @click="procCurrentPage++" :disabled="procCurrentPage === procTotalPages">Next</Button>
           </div>
        </div>
      </Card>
    </div>

    <!-- Export Action -->
    <Card class="bg-slate-900/50 backdrop-blur-xl border-slate-800 text-white mb-8 animate-in fade-in slide-in-from-bottom-14 duration-700 delay-500">
      <CardContent class="p-6">
        <h3 class="text-lg font-bold text-white mb-4">Export Options</h3>
        <div class="space-y-4">
          <Button @click="triggerExport('full')" variant="outline" 
                 class="w-full h-auto p-5 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border-emerald-500/20 hover:from-emerald-500/20 hover:to-teal-500/20 hover:text-white hover:border-emerald-500/40 justify-between flex-col sm:flex-row gap-4 sm:gap-0 items-start sm:items-center transition-all duration-300 rounded-xl">
            <div class="flex flex-col sm:flex-row items-start sm:items-center text-left w-full">
              <div class="p-3 bg-emerald-500/20 rounded-lg mr-4 mb-3 sm:mb-0 flex-shrink-0">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <div class="text-white font-bold text-lg">Export Complete Data (CSV)</div>
                <div class="text-sm text-slate-400 mt-1">Downloads metrics, products, and procurements securely to your device.</div>
              </div>
            </div>
            <svg class="w-6 h-6 text-emerald-400 self-end sm:self-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Dialogs -->
    <Dialog v-model:open="showReportBuilder">
      <DialogContent class="bg-slate-900 border-slate-800 text-white w-[95vw] sm:max-w-2xl max-h-[90vh] overflow-y-auto rounded-xl shadow-2xl">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold tracking-tight">Custom Report Builder</DialogTitle>
        </DialogHeader>
        <div class="space-y-4 pt-4">
          <p class="text-slate-400 text-sm mb-6">Select the specific datasets you want to include in your customized CSV export.</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="flex items-center space-x-3 p-4 bg-slate-950/50 rounded-xl border border-slate-800 cursor-pointer hover:bg-slate-800 hover:border-indigo-500/50 transition-all group">
              <input type="checkbox" v-model="customConfig.metrics" class="w-5 h-5 rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-900" />
              <span class="text-white font-medium group-hover:text-indigo-400 transition-colors">Key Summary Metrics</span>
            </label>
            <label class="flex items-center space-x-3 p-4 bg-slate-950/50 rounded-xl border border-slate-800 cursor-pointer hover:bg-slate-800 hover:border-indigo-500/50 transition-all group">
              <input type="checkbox" v-model="customConfig.sales" class="w-5 h-5 rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-900" />
              <span class="text-white font-medium group-hover:text-indigo-400 transition-colors">Sales Trend Data</span>
            </label>
            <label class="flex items-center space-x-3 p-4 bg-slate-950/50 rounded-xl border border-slate-800 cursor-pointer hover:bg-slate-800 hover:border-indigo-500/50 transition-all group">
              <input type="checkbox" v-model="customConfig.products" class="w-5 h-5 rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-900" />
              <span class="text-white font-medium group-hover:text-indigo-400 transition-colors">Top Selling Products</span>
            </label>
            <label class="flex items-center space-x-3 p-4 bg-slate-950/50 rounded-xl border border-slate-800 cursor-pointer hover:bg-slate-800 hover:border-indigo-500/50 transition-all group">
              <input type="checkbox" v-model="customConfig.dss" class="w-5 h-5 rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-900" />
              <span class="text-white font-medium group-hover:text-indigo-400 transition-colors">DSS Recommendations</span>
            </label>
            <label class="flex items-center space-x-3 p-4 bg-slate-950/50 rounded-xl border border-slate-800 cursor-pointer hover:bg-slate-800 hover:border-indigo-500/50 transition-all group md:col-span-2">
              <input type="checkbox" v-model="customConfig.procurements" class="w-5 h-5 rounded border-slate-600 text-indigo-500 focus:ring-indigo-500 bg-slate-900" />
              <span class="text-white font-medium group-hover:text-indigo-400 transition-colors">Procurement History</span>
            </label>
          </div>
          
          <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-8 pt-6 border-t border-slate-800">
            <Button @click="showReportBuilder = false" variant="outline" class="w-full sm:w-auto bg-slate-950 border-slate-800 text-slate-300 hover:bg-slate-800 hover:text-white h-10 rounded-lg">Cancel</Button>
            <Button @click="triggerExport('custom')" :disabled="!hasCustomSelection" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white border-0 shadow-lg shadow-indigo-900/20 disabled:opacity-50 h-10 rounded-lg font-semibold">
              Generate Custom Report
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showConfirmDialog">
      <DialogContent class="bg-slate-900 border-slate-800 text-white w-[90vw] sm:max-w-md rounded-xl shadow-2xl">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex items-center text-emerald-400">
            <svg class="w-6 h-6 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            Confirm Export
          </DialogTitle>
        </DialogHeader>
        <div class="py-4">
          <p class="text-slate-300 leading-relaxed">Are you sure you want to generate and download the <strong class="text-white">{{ exportType === 'full' ? 'Full' : 'Custom' }} Report</strong>? This will compile the selected data for the period of <strong class="text-white uppercase tracking-wider">{{ dateRange }}</strong>.</p>
        </div>
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-6">
          <Button variant="outline" @click="showConfirmDialog = false" class="w-full sm:w-auto bg-slate-950 border-slate-800 text-slate-300 hover:bg-slate-800 hover:text-white h-10 rounded-lg">Cancel</Button>
          <Button @click="confirmExport" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white h-10 rounded-lg font-semibold shadow-lg shadow-emerald-900/20">Yes, Download CSV</Button>
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
const dateRange = ref('year') // Defaults to "This Year (12 Months)"[cite: 8]

const today = new Date()
const startOfYear = new Date(today.getFullYear(), 0, 1)
const fromDate = ref(startOfYear.toISOString().split('T')[0])
const toDate = ref(today.toISOString().split('T')[0])

// Pagination States
const dssCurrentPage = ref(1)
const dssItemsPerPage = ref(5)
const procCurrentPage = ref(1)
const procItemsPerPage = ref(5)

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

// Area Chart State & Config
const hoveredPoint = ref(null)

// Donut Chart State & Config
const activeSlice = ref(null)
const pieColors = ['#6366f1', '#10b981', '#f59e0b', '#06b6d4', '#ec4899', '#8b5cf6']

// === Computations === //

const hasCustomSelection = computed(() => {
  return customConfig.value.metrics || 
         customConfig.value.sales || 
         customConfig.value.products || 
         customConfig.value.dss || 
         customConfig.value.procurements
})

// Table Pagination Logic
const paginatedDSS = computed(() => {
  const start = (dssCurrentPage.value - 1) * dssItemsPerPage.value;
  return dssRecommendations.value.slice(start, start + dssItemsPerPage.value);
})
const dssTotalPages = computed(() => Math.ceil(dssRecommendations.value.length / dssItemsPerPage.value) || 1)

const paginatedProcurements = computed(() => {
  const start = (procCurrentPage.value - 1) * procItemsPerPage.value;
  return procurements.value.slice(start, start + procItemsPerPage.value);
})
const procTotalPages = computed(() => Math.ceil(procurements.value.length / procItemsPerPage.value) || 1)

// Area Chart Computations
const maxSalesValue = computed(() => {
  if (salesData.value.length === 0) return 1
  return Math.max(...salesData.value.map(d => parseFloat(d.value) || 0))
})

const chartPoints = computed(() => {
  if (!salesData.value.length) return [];
  const svgWidth = 800;
  const svgHeight = 250;
  const padding = 20;
  const max = maxSalesValue.value;
  const step = salesData.value.length > 1 ? svgWidth / (salesData.value.length - 1) : svgWidth / 2;
  
  return salesData.value.map((d, i) => {
    const val = parseFloat(d.value) || 0;
    const y = svgHeight - ((val / max) * (svgHeight - padding)); 
    const x = salesData.value.length > 1 ? (i * step) : step;
    return { x, y, value: val, label: d.label };
  });
})

const linePath = computed(() => {
  const pts = chartPoints.value;
  if (!pts.length) return '';
  return pts.map((p, i) => (i === 0 ? `M ${p.x},${p.y}` : `L ${p.x},${p.y}`)).join(' ');
})

const areaPath = computed(() => {
  const pts = chartPoints.value;
  if (!pts.length) return '';
  const line = linePath.value;
  return `${line} L 800,250 L 0,250 Z`;
})

// Pie Chart Computations
const totalTopProductsRevenue = computed(() => {
  if (!topProducts.value) return 0;
  return topProducts.value.reduce((acc, curr) => acc + (parseFloat(curr.revenue) || 0), 0);
})

const donutData = computed(() => {
  if (!topProducts.value || !topProducts.value.length) return [];
  const total = totalTopProductsRevenue.value;
  if (total === 0) return [];

  let currentOffset = 0;
  return topProducts.value.map((product, index) => {
    const value = parseFloat(product.revenue) || 0;
    const percentage = (value / total) * 100;
    const array = `${percentage} ${100 - percentage}`;
    const offset = 100 - currentOffset;
    
    currentOffset += percentage;

    return {
      ...product,
      percentage,
      strokeDasharray: array,
      strokeDashoffset: offset === 100 ? 0 : offset,
      color: pieColors[index % pieColors.length]
    };
  });
})

// Utility formatters
const formatNumber = (num) => {
  if (!num) return '0.00'
  return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const formatInt = (num) => {
  if (!num) return '0'
  return parseInt(num, 10).toLocaleString('en-US')
}

const formatShortCurrency = (val) => {
  if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M'
  if (val >= 1000) return (val / 1000).toFixed(1) + 'K'
  return val.toString()
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
    
    // Reset pagination
    dssCurrentPage.value = 1
    procCurrentPage.value = 1
    
    const params = {
      dateRange: dateRange.value, // Passes "year" for the default Last 12 months view[cite: 8]
    }
    
    if (dateRange.value === 'custom') {
      params.fromDate = fromDate.value
      params.toDate = toDate.value
    }

    const response = await api.get('/operation-distributor/reports', { params }) // Correctly hits ECommerceReportController.php[cite: 7, 8]
    
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

// Generate CSV Report Logic
const generateCSV = (type) => {
  let csvContent = "\uFEFF" // Add BOM for Excel UTF-8 rendering

  if (type === 'full' || (type === 'custom' && customConfig.value.metrics)) {
    csvContent += "REPORT SUMMARY\n"
    csvContent += `Report Range,${dateRange.value.toUpperCase()}\n`
    csvContent += `Total Sales,${keyMetrics.value.totalSales}\n`
    csvContent += `Total Orders,${keyMetrics.value.totalOrders}\n`
    csvContent += `Total Items Sold,${keyMetrics.value.totalItemsSold}\n`
    csvContent += `Total Procurement Spent,${keyMetrics.value.totalProcurementCost}\n`
    csvContent += `Total Procurement Requests,${keyMetrics.value.totalProcurementRequests}\n\n`
  }

  if (type === 'full' || (type === 'custom' && customConfig.value.sales)) {
    csvContent += "SALES TREND\n"
    csvContent += "Date,Revenue\n"
    salesData.value?.forEach(s => {
      csvContent += `"${s.label}",${s.value}\n`
    })
    csvContent += "\n"
  }

  if (type === 'full' || (type === 'custom' && customConfig.value.products)) {
    csvContent += "TOP PRODUCTS\n"
    csvContent += "Product Name,Revenue,Items Sold,Market Share (%)\n"
    topProducts.value?.forEach(p => {
      csvContent += `"${p.name}",${p.revenue},${p.orders},${p.marketShare}\n`
    })
    csvContent += "\n"
  }

  if (type === 'full' || (type === 'custom' && customConfig.value.dss)) {
    csvContent += "DECISION SUPPORT SYSTEM (PROCUREMENT RECOMMENDATIONS)\n"
    csvContent += "Product Name,Period Sold,Current Stock,Suggested Qty,Priority\n"
    dssRecommendations.value?.forEach(r => {
      csvContent += `"${r.name}",${r.sold_in_period},${r.current_stock},${r.suggested_quantity},"${r.priority}"\n`
    })
    csvContent += "\n"
  }

  if (type === 'full' || (type === 'custom' && customConfig.value.procurements)) {
    csvContent += "PROCUREMENT HISTORY\n"
    csvContent += "Request Code,Product Name,Quantity,Total Cost,Status,Date Created\n"
    procurements.value?.forEach(proc => {
      const d = new Date(proc.created_at).toISOString().split('T')[0]
      csvContent += `"${proc.request_code}","${proc.product_name}",${proc.quantity},${proc.total_cost},"${proc.status}",${d}\n`
    })
  }

  // Blob Download (Original preserved logic)[cite: 8]
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
/* Scoped minimal. Custom Scrollbar for list container */
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}
</style>