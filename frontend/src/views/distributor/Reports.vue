<template>
  <div class="min-h-screen p-4 md:p-8 font-sans">
    <div class="mb-8 md:mb-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
      
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-8">
        <div class="space-y-1">
          <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 via-blue-800 to-slate-900 tracking-tight">Business Reports</h1>
          <p class="text-slate-500 font-medium tracking-wide text-sm">Monitor and analyze your distributor business performance</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <Select v-model="selectedPeriod.value" @update:model-value="selectReportPeriod">
            <SelectTrigger class="w-full sm:w-[200px] bg-white border-slate-200 rounded-xl shadow-sm focus:ring-blue-500 font-medium text-slate-700">
              <SelectValue :placeholder="selectedPeriod.label" />
            </SelectTrigger>
            <SelectContent class="rounded-xl border-slate-100 shadow-xl">
              <SelectItem v-for="period in reportPeriods" :key="period.value" :value="period.value" class="cursor-pointer hover:bg-slate-50">
                {{ period.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button class="bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/30 transition-all hover:-translate-y-0.5 gap-2 w-full sm:w-auto rounded-xl px-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export
                <svg class="w-4 h-4 ml-1 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-56 bg-white rounded-xl border-slate-100 shadow-2xl p-2">
              <DropdownMenuItem @click="exportReport('pdf')" class="text-rose-600 focus:bg-rose-50 focus:text-rose-700 cursor-pointer rounded-lg p-2.5">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="font-medium">Export as PDF</span>
              </DropdownMenuItem>
              <DropdownMenuItem @click="exportReport('excel')" class="text-emerald-600 focus:bg-emerald-50 focus:text-emerald-700 cursor-pointer rounded-lg p-2.5">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">Export as Excel</span>
              </DropdownMenuItem>
              <DropdownMenuItem @click="exportReport('csv')" class="text-blue-600 focus:bg-blue-50 focus:text-blue-700 cursor-pointer rounded-lg p-2.5">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span class="font-medium">Export as CSV</span>
              </DropdownMenuItem>
              <DropdownMenuSeparator class="my-1 bg-slate-100" />
              <DropdownMenuItem @click="printReport" class="cursor-pointer text-slate-700 focus:bg-slate-50 focus:text-slate-900 rounded-lg p-2.5">
                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span class="font-medium">Print Report</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
      
      <!-- Loading State -->
      <div v-if="isLoading" class="flex flex-col justify-center items-center py-32 space-y-4">
        <div class="relative w-16 h-16">
          <div class="absolute inset-0 rounded-full border-t-2 border-blue-500 animate-spin"></div>
          <div class="absolute inset-2 rounded-full border-r-2 border-indigo-400 animate-spin animation-delay-150"></div>
        </div>
        <p class="text-slate-400 font-medium animate-pulse">Compiling reports...</p>
      </div>

      <div v-else>
        <!-- Top Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-6 mb-8">
          <Card class="relative overflow-hidden bg-gradient-to-br from-blue-600 to-indigo-700 border-0 text-white shadow-xl shadow-blue-900/10 group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <CardContent class="p-6 relative z-10">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-blue-100 uppercase tracking-wider">Total Revenue</p>
                  <p class="text-3xl font-extrabold mt-2 tracking-tight">₱{{ formatCurrency(monthlySummary.totalRevenue) }}</p>
                  <div class="flex items-center mt-3 bg-white/10 w-fit px-2.5 py-1 rounded-full backdrop-blur-sm border border-white/10">
                    <svg class="w-4 h-4 mr-1.5" :class="monthlySummary.revenueGrowth >= 0 ? 'text-emerald-300' : 'text-rose-300 transform rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-xs font-semibold">{{ Math.abs(monthlySummary.revenueGrowth) }}% vs prev.</span>
                  </div>
                </div>
                <div class="p-3.5 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-teal-600 border-0 text-white shadow-xl shadow-emerald-900/10 group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <CardContent class="p-6 relative z-10">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-emerald-100 uppercase tracking-wider">Quantity Sold</p>
                  <p class="text-3xl font-extrabold mt-2 tracking-tight">{{ formatNumber(monthlySummary.totalQuantity) }}</p>
                  <p class="text-xs font-medium text-emerald-100 mt-3 bg-white/10 w-fit px-2.5 py-1 rounded-full backdrop-blur-sm border border-white/10">items/gallons</p>
                </div>
                <div class="p-3.5 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card class="relative overflow-hidden bg-gradient-to-br from-purple-600 to-fuchsia-600 border-0 text-white shadow-xl shadow-purple-900/10 group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <CardContent class="p-6 relative z-10">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-purple-100 uppercase tracking-wider">Total Orders</p>
                  <p class="text-3xl font-extrabold mt-2 tracking-tight">{{ monthlySummary.totalOrders }}</p>
                  <div class="flex items-center mt-3 bg-white/10 w-fit px-2.5 py-1 rounded-full backdrop-blur-sm border border-white/10">
                    <svg class="w-4 h-4 mr-1.5" :class="monthlySummary.orderGrowth >= 0 ? 'text-emerald-300' : 'text-rose-300 transform rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-xs font-semibold">{{ Math.abs(monthlySummary.orderGrowth) }}% vs prev.</span>
                  </div>
                </div>
                <div class="p-3.5 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-orange-600 border-0 text-white shadow-xl shadow-orange-900/10 group hover:-translate-y-1 transition-all duration-300">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
            <CardContent class="p-6 relative z-10">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-amber-100 uppercase tracking-wider">Avg. Order Value</p>
                  <p class="text-3xl font-extrabold mt-2 tracking-tight">₱{{ formatCurrency(monthlySummary.averageOrderValue) }}</p>
                  <p class="text-xs font-medium text-amber-100 mt-3 bg-white/10 w-fit px-2.5 py-1 rounded-full backdrop-blur-sm border border-white/10">per transaction</p>
                </div>
                <div class="p-3.5 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20 shadow-inner">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      
        <!-- Charts & Products -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
          
          <!-- SVG Area Chart for Sales Summary -->
          <Card class="border-0 ring-1 ring-slate-100 shadow-md bg-white lg:col-span-2 flex flex-col relative overflow-hidden">
            <CardContent class="p-6 sm:p-8 flex-1 flex flex-col relative z-10">
              <div class="flex items-center justify-between mb-8">
                <div>
                  <h2 class="text-xl font-bold text-slate-800 tracking-tight">Sales Trend Summary</h2>
                  <p class="text-slate-500 text-sm mt-1">Revenue timeline over the selected period</p>
                </div>
              </div>
              
              <div class="h-64 sm:h-80 relative min-h-[250px] w-full flex-1 group">
                <!-- Background Grid -->
                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none border-b border-slate-100">
                  <div class="w-full border-t border-slate-100 h-0"></div>
                  <div class="w-full border-t border-slate-100 h-0"></div>
                  <div class="w-full border-t border-slate-100 h-0"></div>
                  <div class="w-full border-t border-slate-100 h-0"></div>
                </div>

                <!-- SVG Line & Area -->
                <svg v-if="monthlyChartData.length > 0" viewBox="0 0 800 250" preserveAspectRatio="none" class="absolute inset-0 w-full h-full overflow-visible">
                  <defs>
                    <linearGradient id="colorSalesTrend" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stop-color="#3b82f6" stop-opacity="0.2"/>
                      <stop offset="95%" stop-color="#3b82f6" stop-opacity="0"/>
                    </linearGradient>
                  </defs>
                  <!-- Filled Area -->
                  <path :d="areaPath" fill="url(#colorSalesTrend)" class="transition-all duration-500 ease-in-out" />
                  <!-- Stroke Line -->
                  <path :d="linePath" fill="none" stroke="#3b82f6" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-in-out drop-shadow-md" />
                  <!-- Interactive Data Points -->
                  <circle v-for="(point, index) in chartPoints" :key="'point-'+index"
                          :cx="point.x" :cy="point.y" r="4" 
                          fill="#ffffff" stroke="#3b82f6" stroke-width="2" 
                          class="transition-all duration-300"
                          :class="hoveredPoint === index ? 'r-6 fill-[#3b82f6] stroke-white stroke-[3px] drop-shadow-md' : 'fill-white opacity-0 group-hover:opacity-100'"
                  />
                </svg>

                <!-- Interactive Hover Overlay System -->
                <div class="absolute inset-0 flex" v-if="monthlyChartData.length > 0">
                  <div v-for="(point, index) in chartPoints" :key="'overlay-'+index" 
                       class="flex-1 h-full relative group/col cursor-pointer"
                       @mouseenter="hoveredPoint = index"
                       @mouseleave="hoveredPoint = null">
                       
                    <!-- Vertical Dashed Line -->
                    <div class="absolute top-0 bottom-0 left-1/2 -translate-x-1/2 w-px border-l border-dashed border-blue-300 opacity-0 transition-opacity"
                         :class="{'opacity-100': hoveredPoint === index}"></div>

                    <!-- Tooltip -->
                    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-4 px-3 py-2 bg-slate-800 text-white text-xs rounded-md shadow-xl border border-slate-700 pointer-events-none opacity-0 transition-all z-20 whitespace-nowrap"
                         :class="{'opacity-100 -translate-y-2': hoveredPoint === index}">
                      <div class="font-bold text-sm text-blue-400">₱{{ formatCurrency(point.value) }}</div>
                      <div class="text-slate-300 mt-0.5 font-medium">{{ point.label }}</div>
                    </div>
                  </div>
                </div>

                <!-- Empty State Fallback -->
                <div v-if="monthlyChartData.length === 0" class="absolute inset-0 flex items-center justify-center text-slate-500 font-medium border-2 border-dashed border-slate-100 rounded-2xl">
                  No sales data for selected period.
                </div>
              </div>
              
              <!-- X-Axis Labels (Responsive hiding) -->
              <div class="w-full flex justify-between mt-4 text-[10px] sm:text-xs font-semibold text-slate-400 relative z-10" v-if="monthlyChartData.length > 0">
                <span v-for="(point, index) in chartPoints" :key="'label-'+point.label" 
                      class="flex-1 text-center truncate px-1"
                      :class="{'opacity-0 sm:opacity-100': chartPoints.length > 15 && index % 2 !== 0, 'hidden md:block': chartPoints.length > 30 && index % 3 !== 0}">
                  {{ point.label }}
                </span>
              </div>
            </CardContent>
          </Card>
          
          <!-- Shadcn-Style Donut Chart for Top Products -->
          <Card class="border-0 ring-1 ring-slate-100 shadow-md bg-white lg:col-span-1 flex flex-col h-full">
            <CardContent class="p-6 sm:p-8 flex-1 flex flex-col">
              <div class="mb-6">
                <h3 class="text-xl font-bold text-slate-800 tracking-tight">Top-Selling Products</h3>
                <p class="text-slate-500 text-sm mt-1">Revenue distribution</p>
              </div>
              
              <div class="flex flex-col items-center justify-center gap-6 flex-1">
                <!-- Donut SVG -->
                <div class="relative w-48 h-48 sm:w-56 sm:h-56 flex-shrink-0" v-if="donutData.length > 0">
                  <svg viewBox="0 0 42 42" class="w-full h-full -rotate-90 filter drop-shadow-md">
                    <!-- Inner Track -->
                    <circle cx="21" cy="21" r="15.915494309189533" fill="transparent" stroke="#f1f5f9" stroke-width="6"></circle>
                    <!-- Dynamic Slices -->
                    <circle v-for="(slice, index) in donutData" :key="index"
                            cx="21" cy="21" r="15.915494309189533" fill="transparent"
                            :stroke="slice.color" stroke-width="6"
                            :stroke-dasharray="slice.strokeDasharray"
                            :stroke-dashoffset="slice.strokeDashoffset"
                            class="transition-all duration-300 ease-out cursor-pointer hover:stroke-[7]"
                            :class="{'opacity-100': activeSlice === index || activeSlice === null, 'opacity-30': activeSlice !== null && activeSlice !== index}"
                            @mouseenter="activeSlice = index"
                            @mouseleave="activeSlice = null"
                    ></circle>
                  </svg>
                  <!-- Center Text Content -->
                  <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Top Sales</span>
                    <span class="text-xl sm:text-2xl font-extrabold text-slate-800">₱{{ formatShortCurrency(totalTopProductsRevenue) }}</span>
                  </div>
                </div>

                <!-- List / Legend -->
                <div class="w-full space-y-2 mt-2 overflow-y-auto max-h-[180px] pr-2 custom-scrollbar" v-if="donutData.length > 0">
                  <div v-for="(product, index) in donutData" :key="product.id" 
                       class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer group border border-transparent hover:border-slate-100"
                       @mouseenter="activeSlice = index"
                       @mouseleave="activeSlice = null">
                    <div class="flex items-center gap-3 min-w-0">
                      <div class="w-3.5 h-3.5 flex-shrink-0 rounded-full shadow-sm transition-transform group-hover:scale-125" :style="{ backgroundColor: product.color }"></div>
                      <div>
                        <h4 class="text-slate-600 font-semibold text-sm truncate group-hover:text-indigo-600 transition-colors" :title="product.name">
                          {{ product.name }}
                        </h4>
                        <div class="text-[10px] font-medium text-slate-400">{{ product.marketShare }}% market share</div>
                      </div>
                    </div>
                    <div class="text-right flex-shrink-0 ml-2">
                      <div class="text-slate-800 font-bold text-sm">₱{{ formatCurrency(product.revenue) }}</div>
                    </div>
                  </div>
                </div>
                
                <!-- Empty State Fallback -->
                <div v-if="donutData.length === 0" class="flex flex-col items-center justify-center w-full py-10 text-slate-400 border-2 border-dashed border-slate-100 rounded-2xl">
                   <svg class="w-10 h-10 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                   <span class="font-medium">No products sold in this period.</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
        
        <!-- Stock Movement with Pagination -->
        <Card class="border-0 ring-1 ring-slate-100 shadow-md overflow-hidden mb-8 bg-white flex flex-col">
          <div class="px-6 py-5 border-b border-slate-100 bg-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Stock Movement Report</h2>
                <p class="text-slate-500 text-sm mt-1">Inventory changes and restocking needs</p>
              </div>
              <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <Select v-model="stockFilter">
                  <SelectTrigger class="w-full sm:w-[180px] bg-white border-slate-200 rounded-xl shadow-sm focus:ring-blue-500 font-medium">
                    <SelectValue placeholder="All Products" />
                  </SelectTrigger>
                  <SelectContent class="rounded-xl shadow-xl border-slate-100">
                    <SelectItem value="all">All Products</SelectItem>
                    <SelectItem value="low">Low Stock</SelectItem>
                    <SelectItem value="critical">Critical</SelectItem>
                    <SelectItem value="overstock">Overstock</SelectItem>
                  </SelectContent>
                </Select>
                
                <Button @click="generateStockReport" class="bg-slate-800 hover:bg-slate-900 text-white shadow-lg shadow-slate-900/20 gap-2 w-full sm:w-auto rounded-xl">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  Generate Report
                </Button>
              </div>
            </div>
          </div>
          
          <!-- Mobile List -->
          <div class="md:hidden divide-y divide-slate-100 flex-1">
            <div v-for="item in paginatedStockMovement" :key="item.id" class="p-5 hover:bg-slate-50 transition-colors">
              <div class="flex items-start justify-between mb-4">
                <div class="flex-1 min-w-0 mr-3">
                  <div class="flex items-center gap-2.5 mb-1.5">
                    <div class="w-4 h-4 rounded-full flex-shrink-0 shadow-sm border border-black/5" :style="{ backgroundColor: item.color }"></div>
                    <h3 class="font-bold text-slate-800 truncate">{{ item.name }}</h3>
                  </div>
                  <p class="text-sm font-medium text-slate-500 truncate">{{ item.brand }} • {{ item.finish }}</p>
                </div>
                <Badge :class="getStockStatusClasses(item.status)" class="whitespace-nowrap px-3 py-1 font-bold tracking-wide uppercase text-[10px] rounded-full border">
                  {{ item.status }}
                </Badge>
              </div>
              
              <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100">
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Current Stock</p>
                  <p class="font-extrabold text-slate-800">{{ item.currentStock }} <span class="text-xs font-medium text-slate-500">items</span></p>
                </div>
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Min. Required</p>
                  <p class="font-bold text-slate-700">{{ item.minRequired }} <span class="text-xs font-medium text-slate-500">items</span></p>
                </div>
              </div>
              
              <div class="mt-4">
                <div class="flex items-center justify-between text-xs font-bold text-slate-500 mb-2">
                  <span class="uppercase tracking-wider text-[10px]">Stock Level</span>
                  <span :class="getStockTextColor(item.stockPercentage)">{{ Math.min(item.stockPercentage, 100) }}%</span>
                </div>
                <Progress :model-value="item.stockPercentage" class="h-2.5 bg-slate-100 shadow-inner" :indicator-class="getStockPercentageColor(item.stockPercentage)" />
              </div>
            </div>
          </div>
          
          <!-- Desktop Table -->
          <div class="hidden md:block overflow-x-auto flex-1">
            <Table>
              <TableHeader class="bg-slate-50/80">
                <TableRow class="hover:bg-transparent border-slate-100">
                  <TableHead class="py-4 px-6 text-xs font-extrabold text-slate-500 uppercase tracking-wider">Product</TableHead>
                  <TableHead class="py-4 px-6 text-xs font-extrabold text-slate-500 uppercase tracking-wider text-center">Current Stock</TableHead>
                  <TableHead class="py-4 px-6 text-xs font-extrabold text-slate-500 uppercase tracking-wider text-center">Min. Required</TableHead>
                  <TableHead class="py-4 px-6 text-xs font-extrabold text-slate-500 uppercase tracking-wider text-center">Status</TableHead>
                  <TableHead class="py-4 px-6 text-xs font-extrabold text-slate-500 uppercase tracking-wider text-right">Stock Level</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody class="divide-y divide-slate-100">
                <TableRow v-for="item in paginatedStockMovement" :key="item.id" class="hover:bg-slate-50/80 transition-colors group">
                  <TableCell class="py-5 px-6">
                    <div class="flex items-center gap-4">
                      <div class="w-10 h-10 rounded-xl flex-shrink-0 shadow-sm border border-black/5" :style="{ backgroundColor: item.color }"></div>
                      <div>
                        <div class="font-bold text-slate-800 text-base group-hover:text-blue-600 transition-colors">{{ item.name }}</div>
                        <div class="text-sm font-medium text-slate-500">{{ item.brand }} • {{ item.finish }}</div>
                        <div class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wide">SKU: {{ item.sku }}</div>
                      </div>
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-5 px-6 text-center">
                    <div>
                      <div class="font-extrabold text-slate-800 text-xl">{{ item.currentStock }}</div>
                      <div class="text-xs font-medium text-slate-500 uppercase tracking-wide">items</div>
                    </div>
                  </TableCell>
                  
                  <TableCell class="py-5 px-6 text-center">
                    <div class="font-bold text-slate-700 text-base">{{ item.minRequired }} <span class="text-sm font-medium text-slate-500">items</span></div>
                    <div class="text-xs font-semibold text-slate-400 mt-1 uppercase tracking-wide">Safety stock</div>
                  </TableCell>
                  
                  <TableCell class="py-5 px-6 text-center">
                    <Badge :class="getStockStatusClasses(item.status)" class="px-3 py-1 rounded-full font-bold uppercase tracking-wider text-[10px] inline-flex items-center gap-2 border">
                      <span class="w-2 h-2 rounded-full animate-pulse" :class="getStockStatusDotClasses(item.status)"></span>
                      {{ item.status }}
                    </Badge>
                  </TableCell>
                  
                  <TableCell class="py-5 px-6">
                    <div class="flex items-center justify-end gap-4">
                      <div class="w-32">
                        <Progress :model-value="item.stockPercentage" class="h-2.5 bg-slate-100 shadow-inner" :indicator-class="getStockPercentageColor(item.stockPercentage)" />
                      </div>
                      <div class="text-right min-w-[50px]">
                        <div class="font-extrabold text-slate-800 text-base">{{ Math.min(item.stockPercentage, 100) }}%</div>
                      </div>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <div v-if="filteredStockMovement.length === 0" class="text-center py-16">
            <div class="w-16 h-16 mx-auto mb-4 text-slate-300">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
            <h3 class="text-lg font-bold text-slate-700 mb-2">No stock items found</h3>
            <p class="text-slate-500 max-w-sm mx-auto font-medium">Try adjusting your filters to find what you're looking for.</p>
          </div>

          <!-- Pagination Footer -->
          <div class="flex justify-between items-center px-6 py-4 border-t border-slate-100 bg-slate-50/50" v-if="stockTotalPages > 1">
             <span class="text-sm font-semibold text-slate-500">Page {{ stockCurrentPage }} of {{ stockTotalPages }}</span>
             <div class="space-x-2">
               <Button variant="outline" size="sm" class="border-slate-200 text-slate-600 bg-white hover:bg-slate-50 shadow-sm" @click="stockCurrentPage--" :disabled="stockCurrentPage === 1">Previous</Button>
               <Button variant="outline" size="sm" class="border-slate-200 text-slate-600 bg-white hover:bg-slate-50 shadow-sm" @click="stockCurrentPage++" :disabled="stockCurrentPage === stockTotalPages">Next</Button>
             </div>
          </div>
        </Card>
        
        <!-- Bottom Summary Card -->
        <Card class="relative overflow-hidden bg-slate-900 border-0 text-white shadow-2xl">
          <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiM5QzkyQUMiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PHBhdGggZD0iTTM2IDM0djIwaDJWMzRoLTJ6TTM2IDBoMnYyMGgtMlYwem0xNiAzNHYyMGgyVjM0aC0yem0xNiAwSDIwdjIwaDQyVjM0em0tMTYgMEgyMHYyMGgyNlYzNHoiLz48L2c+PC9nPjwvc3ZnPg==')] opacity-20 mix-blend-overlay"></div>
          <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl"></div>
          <div class="absolute -right-20 -top-20 w-64 h-64 bg-purple-500/20 rounded-full blur-3xl"></div>
          
          <CardContent class="p-8 relative z-10">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
              <div>
                <h2 class="text-2xl font-extrabold mb-3 tracking-tight text-white">Report Summary</h2>
                <p class="text-slate-400 font-medium max-w-2xl text-sm leading-relaxed">This combined report provides a comprehensive overview of your business performance for <span class="text-white font-bold">{{ selectedPeriod.label.toLowerCase() }}</span>. Key insights include revenue, product stock tracking, and active employee metrics.</p>
              </div>
              <div class="flex items-center gap-6">
                <div class="text-right">
                  <div class="text-2xl font-extrabold text-white tracking-tight">{{ reportGeneratedTime.split(',')[0] }}</div>
                  <div class="text-sm font-medium text-slate-400 mt-1">{{ reportGeneratedTime.split(',')[1] }}</div>
                </div>
                <div class="h-12 w-px bg-white/10"></div>
                <Button @click="shareReport" variant="ghost" class="h-14 w-14 p-0 bg-white/5 hover:bg-white/15 rounded-2xl transition-all duration-300 text-white border border-white/10 hover:scale-105 hover:shadow-lg shadow-black/20">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                </Button>
              </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-8 pt-8 border-t border-white/10">
              <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 hover:bg-white/10 transition-colors group">
                <div class="flex items-center gap-3.5 mb-3">
                  <div class="p-2.5 bg-emerald-500/20 text-emerald-400 rounded-xl group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                  </div>
                  <h3 class="font-bold text-white tracking-wide">Revenue Growth</h3>
                </div>
                <p class="text-sm font-medium text-slate-400 leading-relaxed">Revenue is at <span class="text-white font-bold">{{ monthlySummary.revenueGrowth }}%</span> compared to the previous period.</p>
              </div>
              
              <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 hover:bg-white/10 transition-colors group">
                <div class="flex items-center gap-3.5 mb-3">
                  <div class="p-2.5 bg-blue-500/20 text-blue-400 rounded-xl group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                  </div>
                  <h3 class="font-bold text-white tracking-wide">Top Product</h3>
                </div>
                <p class="text-sm font-medium text-slate-400 leading-relaxed" v-if="topSellingPaints.length > 0"><span class="text-white font-bold">{{ topSellingPaints[0].name }}</span> led sales with ₱{{ formatCurrency(topSellingPaints[0].revenue) }}.</p>
                <p class="text-sm font-medium text-slate-400 leading-relaxed" v-else>No products sold in this period.</p>
              </div>
              
              <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 hover:bg-white/10 transition-colors group">
                <div class="flex items-center gap-3.5 mb-3">
                  <div class="p-2.5 bg-amber-500/20 text-amber-400 rounded-xl group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.286 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                  </div>
                  <h3 class="font-bold text-white tracking-wide">Stock Alert</h3>
                </div>
                <p class="text-sm font-medium text-slate-400 leading-relaxed"><span class="text-rose-400 font-bold">{{ lowStockCount }} products</span> require immediate restocking to avoid shortages.</p>
              </div>

              <div class="bg-white/5 backdrop-blur-md p-5 rounded-2xl border border-white/10 hover:bg-white/10 transition-colors group">
                <div class="flex items-center gap-3.5 mb-3">
                  <div class="p-2.5 bg-purple-500/20 text-purple-400 rounded-xl group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                  </div>
                  <h3 class="font-bold text-white tracking-wide">HR Active Summary</h3>
                </div>
                <p class="text-sm font-medium text-slate-400 leading-relaxed"><span class="text-white font-bold">{{ hrSummary.totalEmployees }} Total Employees.</span> {{ hrSummary.activeAttendances }} attendance logs for this period.</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script>
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import api from '@/utils/axios'

export default {
  name: 'BusinessReports',
  components: {
    Card, CardContent, Button, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, 
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger,
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow, Badge, Progress
  },
  data() {
    return {
      isLoading: true,
      selectedPeriod: { label: 'Last 12 Months', value: 'year' }, 
      stockFilter: 'all',
      hoveredPoint: null,
      activeSlice: null,
      stockCurrentPage: 1,
      stockItemsPerPage: 5,
      pieColors: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4'],
      reportPeriods: [
        { label: 'Today', value: 'today' },
        { label: 'Yesterday', value: 'yesterday' },
        { label: 'Last 7 Days', value: 'week' },
        { label: 'This Month', value: 'month' },
        { label: 'Last Month', value: 'last_month' },
        { label: 'This Quarter', value: 'quarter' },
        { label: 'Last 12 Months', value: 'year' }, // FIX: Corrected label[cite: 12]
      ],
      monthlySummary: {
        totalRevenue: 0,
        totalQuantity: 0,
        totalOrders: 0,
        averageOrderValue: 0,
        revenueGrowth: 0,
        orderGrowth: 0
      },
      hrSummary: {
        totalEmployees: 0,
        activeAttendances: 0
      },
      monthlyChartData: [],
      topSellingPaints: [],
      stockMovement: []
    }
  },
  computed: {
    maxRevenue() {
      if (this.monthlyChartData.length === 0) return 1
      return Math.max(...this.monthlyChartData.map(m => parseFloat(m.revenue) || 0), 1)
    },
    minRevenue() {
      if (this.monthlyChartData.length === 0) return 0
      return Math.min(...this.monthlyChartData.map(m => parseFloat(m.revenue) || 0))
    },
    // Area Chart Calculations
    chartPoints() {
      if (!this.monthlyChartData.length) return [];
      const svgWidth = 800;
      const svgHeight = 250;
      const padding = 20;
      const max = this.maxRevenue;
      const step = this.monthlyChartData.length > 1 ? svgWidth / (this.monthlyChartData.length - 1) : svgWidth / 2;
      
      return this.monthlyChartData.map((d, i) => {
        const val = parseFloat(d.revenue) || 0;
        const y = svgHeight - ((val / max) * (svgHeight - padding)); 
        const x = this.monthlyChartData.length > 1 ? (i * step) : step;
        return { x, y, value: val, label: d.month };
      });
    },
    linePath() {
      const pts = this.chartPoints;
      if (!pts.length) return '';
      return pts.map((p, i) => (i === 0 ? `M ${p.x},${p.y}` : `L ${p.x},${p.y}`)).join(' ');
    },
    areaPath() {
      const pts = this.chartPoints;
      if (!pts.length) return '';
      return `${this.linePath} L 800,250 L 0,250 Z`;
    },
    // Pie Chart Calculations
    totalTopProductsRevenue() {
      if (!this.topSellingPaints) return 0;
      return this.topSellingPaints.reduce((acc, curr) => acc + (parseFloat(curr.revenue) || 0), 0);
    },
    donutData() {
      if (!this.topSellingPaints || !this.topSellingPaints.length) return [];
      const total = this.totalTopProductsRevenue;
      if (total === 0) return [];

      let currentOffset = 0;
      return this.topSellingPaints.map((product, index) => {
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
          color: this.pieColors[index % this.pieColors.length]
        };
      });
    },
    // Pagination & Filters
    filteredStockMovement() {
      let filtered = this.stockMovement
      if (this.stockFilter === 'low') {
        filtered = filtered.filter(item => item.status === 'Low Stock')
      } else if (this.stockFilter === 'critical') {
        filtered = filtered.filter(item => item.status === 'Critical')
      } else if (this.stockFilter === 'overstock') {
        filtered = filtered.filter(item => item.status === 'Overstock')
      }
      return filtered
    },
    paginatedStockMovement() {
      const start = (this.stockCurrentPage - 1) * this.stockItemsPerPage;
      return this.filteredStockMovement.slice(start, start + this.stockItemsPerPage);
    },
    stockTotalPages() {
      return Math.ceil(this.filteredStockMovement.length / this.stockItemsPerPage) || 1;
    },
    totalProducts() {
      return this.stockMovement.length
    },
    averagePaintPrice() {
      const totalRevenue = this.topSellingPaints.reduce((sum, paint) => sum + parseFloat(paint.revenue || 0), 0)
      const totalQuantity = this.topSellingPaints.reduce((sum, paint) => sum + paint.quantity, 0)
      return totalQuantity > 0 ? Math.round(totalRevenue / totalQuantity) : 0
    },
    lowStockCount() {
      return this.stockMovement.filter(item => item.status === 'Low Stock' || item.status === 'Critical').length
    },
    reportGeneratedTime() {
      return new Date().toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
    }
  },
  watch: {
    'selectedPeriod.value': function(newVal) {
      this.fetchReportData(newVal)
    },
    'stockFilter': function() {
      this.stockCurrentPage = 1
    }
  },
  mounted() {
    this.fetchReportData(this.selectedPeriod.value)
  },
  methods: {
    async fetchReportData(periodValue) {
      this.isLoading = true
      try {
        const response = await api.get(`/distributor/combined-reports?period=${periodValue}`)
        if (response.data && response.data.success) {
          const data = response.data.data
          this.monthlySummary = data.monthlySummary
          this.hrSummary = data.hrSummary
          this.monthlyChartData = data.monthlyChartData
          this.topSellingPaints = data.topSellingPaints
          this.stockMovement = data.stockMovement
          this.stockCurrentPage = 1 // reset pagination
        }
      } catch (error) {
        console.error("Failed to load combined report data", error)
      } finally {
        this.isLoading = false
      }
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
    },
    formatShortCurrency(val) {
      if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M'
      if (val >= 1000) return (val / 1000).toFixed(1) + 'K'
      return val.toString()
    },
    formatNumber(num) {
      return new Intl.NumberFormat('en-PH').format(num || 0)
    },
    selectReportPeriod(value) {
      const period = this.reportPeriods.find(p => p.value === value)
      if (period) {
        this.selectedPeriod = period
      }
    },
    getRankColor(index) {
      const colors = [
        'bg-gradient-to-br from-yellow-300 via-yellow-500 to-yellow-600 ring-1 ring-yellow-400/50', 
        'bg-gradient-to-br from-slate-300 via-slate-400 to-slate-500 ring-1 ring-slate-400/50',   
        'bg-gradient-to-br from-orange-300 via-amber-600 to-amber-700 ring-1 ring-amber-500/50',  
        'bg-slate-100 text-slate-500 ring-1 ring-slate-200',                                      
        'bg-slate-50 text-slate-400 ring-1 ring-slate-100'                                        
      ]
      return colors[index] || 'bg-slate-50 text-slate-400 ring-1 ring-slate-100'
    },
    getStockStatusClasses(status) {
      const classes = {
        'Adequate': 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'Low Stock': 'bg-amber-50 text-amber-700 border-amber-200',
        'Critical': 'bg-rose-50 text-rose-700 border-rose-200',
        'Overstock': 'bg-blue-50 text-blue-700 border-blue-200'
      }
      return classes[status] || 'bg-slate-50 text-slate-700 border-slate-200'
    },
    getStockStatusDotClasses(status) {
      const classes = { 'Adequate': 'bg-emerald-500', 'Low Stock': 'bg-amber-500', 'Critical': 'bg-rose-500', 'Overstock': 'bg-blue-500' }
      return classes[status] || 'bg-slate-500'
    },
    getStockPercentageColor(percentage) {
      if (percentage < 50) return 'bg-rose-500'
      if (percentage < 80) return 'bg-amber-500'
      if (percentage <= 120) return 'bg-emerald-500'
      return 'bg-blue-500'
    },
    getStockTextColor(percentage) {
      if (percentage < 50) return 'text-rose-600'
      if (percentage < 80) return 'text-amber-600'
      if (percentage <= 120) return 'text-emerald-600'
      return 'text-blue-600'
    },
    getStockText(percentage) {
      if (percentage < 50) return 'Critical'
      if (percentage < 80) return 'Low'
      if (percentage <= 120) return 'Adequate'
      return 'Overstock'
    },
    exportReport(format) { console.log(`Exporting report as ${format.toUpperCase()}...`) },
    printReport() { window.print() },
    generateStockReport() { console.log('Generating stock report...') },
    shareReport() { console.log('Sharing report...') }
  }
}
</script>

<style scoped>
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>