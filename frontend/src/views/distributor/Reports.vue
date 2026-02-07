<template>
  <div class="p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Business Reports</h1>
          <p class="text-gray-600 mt-2">Monitor and analyze your paint distribution business performance</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <Select :model-value="selectedPeriod.value" @update:model-value="selectReportPeriod">
            <SelectTrigger class="w-full sm:w-[180px] bg-white border-gray-300">
              <SelectValue :placeholder="selectedPeriod.label" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="period in reportPeriods" :key="period.value" :value="period.value">
                {{ period.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button class="bg-green-600 hover:bg-green-700 text-white gap-2 w-full sm:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-56 bg-white">
              <DropdownMenuItem @click="exportReport('pdf')" class="text-red-600 focus:bg-red-50 focus:text-red-700 cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export as PDF
              </DropdownMenuItem>
              <DropdownMenuItem @click="exportReport('excel')" class="text-green-600 focus:bg-green-50 focus:text-green-700 cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Export as Excel
              </DropdownMenuItem>
              <DropdownMenuItem @click="exportReport('csv')" class="text-blue-600 focus:bg-blue-50 focus:text-blue-700 cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export as CSV
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="printReport" class="cursor-pointer">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Report
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6 mb-6">
        <Card class="bg-gradient-to-r from-blue-500 to-blue-600 border-0 text-white shadow-md">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Total Revenue</p>
                <p class="text-2xl font-bold mt-2">₱{{ formatCurrency(monthlySummary.totalRevenue) }}</p>
                <div class="flex items-center mt-2">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                  </svg>
                  <span class="text-xs opacity-90">{{ monthlySummary.revenueGrowth }}% vs last month</span>
                </div>
              </div>
              <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-gradient-to-r from-green-500 to-green-600 border-0 text-white shadow-md">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Quantity Sold</p>
                <p class="text-2xl font-bold mt-2">{{ formatNumber(monthlySummary.totalQuantity) }}</p>
                <p class="text-xs opacity-90 mt-2">gallons</p>
              </div>
              <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-gradient-to-r from-purple-500 to-purple-600 border-0 text-white shadow-md">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Total Orders</p>
                <p class="text-2xl font-bold mt-2">{{ monthlySummary.totalOrders }}</p>
                <div class="flex items-center mt-2">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                  </svg>
                  <span class="text-xs opacity-90">{{ monthlySummary.orderGrowth }}% growth</span>
                </div>
              </div>
              <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-gradient-to-r from-orange-500 to-orange-600 border-0 text-white shadow-md">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm opacity-90">Avg. Order Value</p>
                <p class="text-2xl font-bold mt-2">₱{{ formatCurrency(monthlySummary.averageOrderValue) }}</p>
                <p class="text-xs opacity-90 mt-2">per transaction</p>
              </div>
              <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      
      <Card class="border-gray-200 shadow-sm">
        <CardContent class="p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">Monthly Sales Summary</h2>
              <p class="text-gray-600 text-sm mt-1">Revenue trends over the last 12 months</p>
            </div>
            <div class="flex items-center gap-2">
              <Button v-for="chartType in chartTypes" 
                      :key="chartType.value"
                      @click="selectedChart = chartType.value"
                      size="sm"
                      :variant="selectedChart === chartType.value ? 'default' : 'ghost'"
                      :class="selectedChart === chartType.value ? 'bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 font-medium' : 'text-gray-600'">
                {{ chartType.label }}
              </Button>
            </div>
          </div>
          
          <div class="h-60 sm:h-72 relative min-h-[250px] sm:min-h-[300px]">
            <div v-if="selectedChart === 'bar'" class="h-full flex items-end justify-between pt-4 pb-8">
              <div v-for="month in monthlyChartData" :key="month.month" class="flex-1 mx-0.5 sm:mx-1 flex flex-col items-center group">
                <div class="w-full flex justify-center h-full items-end">
                  <div class="w-full max-w-[2rem] bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-sm sm:rounded-t-lg transition-all hover:opacity-80 relative"
                       :style="{ height: `${Math.min((month.revenue / maxRevenue) * 100, 95)}%` }">
                       <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 bg-gray-800 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10 hidden sm:block">
                         ₱{{ formatCurrency(month.revenue) }}
                       </div>
                  </div>
                </div>
                <span class="text-[10px] sm:text-xs text-gray-500 mt-2 truncate w-full text-center">{{ month.month }}</span>
              </div>
            </div>
            
            <div v-if="selectedChart === 'line'" class="h-full pt-4 pb-8">
              <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="none">
                <line x1="0" y1="0" x2="400" y2="0" stroke="#e5e7eb" stroke-width="1" />
                <line x1="0" y1="50" x2="400" y2="50" stroke="#e5e7eb" stroke-width="1" />
                <line x1="0" y1="100" x2="400" y2="100" stroke="#e5e7eb" stroke-width="1" />
                <line x1="0" y1="150" x2="400" y2="150" stroke="#e5e7eb" stroke-width="1" />
                
                <path :d="linePath" fill="none" stroke="#3b82f6" stroke-width="2" vector-effect="non-scaling-stroke" />
                
                <circle v-for="(point, index) in linePoints" 
                        :key="index"
                        :cx="point.x" 
                        :cy="point.y" 
                        r="3" 
                        fill="#3b82f6" 
                        stroke="white" 
                        stroke-width="2" 
                        vector-effect="non-scaling-stroke"/>
              </svg>
            </div>
            
            <div class="absolute bottom-0 left-0 right-0 flex justify-between text-[10px] sm:text-xs text-gray-500 px-1">
              <span>₱{{ formatCurrency(minRevenue) }}</span>
              <span>₱{{ formatCurrency((minRevenue + maxRevenue) / 2) }}</span>
              <span>₱{{ formatCurrency(maxRevenue) }}</span>
            </div>
          </div>
          
          <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 mt-4 pt-4 border-t border-gray-100">
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
              <span class="text-sm text-gray-600">Monthly Revenue</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="text-sm text-gray-600">Target</span>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="border-gray-200 shadow-sm">
        <CardContent class="p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">Top-Selling Paints</h2>
              <p class="text-gray-600 text-sm mt-1">Best performing products this period</p>
            </div>
            <Button variant="ghost" @click="viewAllProducts" class="self-start sm:self-auto text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 font-medium h-auto p-0">
              View All
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </Button>
          </div>
          
          <div class="space-y-4">
            <div v-for="(product, index) in topSellingPaints" :key="product.id" class="flex items-center gap-3 sm:gap-4 p-2 sm:p-3 hover:bg-gray-50 rounded-lg transition-colors">
              <div class="flex-shrink-0">
                <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center"
                     :class="getRankColor(index)">
                  <span class="text-xs sm:text-sm font-bold text-white">{{ index + 1 }}</span>
                </div>
              </div>
              
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                  <div class="min-w-0 flex-1">
                    <h3 class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ product.name }}</h3>
                    <p class="text-xs sm:text-sm text-gray-500 truncate">{{ product.brand }} • {{ product.finish }}</p>
                  </div>
                  <div class="text-right flex-shrink-0">
                    <p class="font-bold text-gray-900 text-sm sm:text-base">₱{{ formatCurrency(product.revenue) }}</p>
                    <p class="text-xs sm:text-sm text-gray-500">{{ product.quantity }} gal</p>
                  </div>
                </div>
                
                <div class="mt-2">
                  <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>Market share</span>
                    <span>{{ product.marketShare }}%</span>
                  </div>
                  <div class="h-1.5 rounded-full bg-gray-200 overflow-hidden w-full">
                     <div class="h-full rounded-full transition-all duration-500" :style="{ width: `${product.marketShare}%`, backgroundColor: product.color }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="mt-6 pt-4 border-t border-gray-100 grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-500">Total Products</p>
              <p class="font-medium text-gray-900">{{ totalProducts }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Avg. Price/Gallon</p>
              <p class="font-medium text-gray-900">₱{{ formatCurrency(averagePaintPrice) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
    
    <Card class="border-gray-200 shadow-sm overflow-hidden mb-6">
      <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-gray-800">Stock Movement Report</h2>
            <p class="text-gray-600 text-sm mt-1">Inventory changes and restocking needs</p>
          </div>
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <Select v-model="stockFilter">
              <SelectTrigger class="w-full sm:w-[160px] bg-white border-gray-300">
                <SelectValue placeholder="All Products" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Products</SelectItem>
                <SelectItem value="low">Low Stock</SelectItem>
                <SelectItem value="critical">Critical</SelectItem>
                <SelectItem value="overstock">Overstock</SelectItem>
              </SelectContent>
            </Select>
            
            <Button @click="generateStockReport" class="bg-blue-600 hover:bg-blue-700 text-white gap-2 w-full sm:w-auto">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Generate Report
            </Button>
          </div>
        </div>
      </div>
      
      <div class="md:hidden divide-y divide-gray-200">
        <div v-for="item in filteredStockMovement" :key="item.id" class="p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1 min-w-0 mr-2">
              <div class="flex items-center gap-2 mb-1">
                <div class="w-3 h-3 rounded-full flex-shrink-0" :style="{ backgroundColor: item.color }"></div>
                <h3 class="font-medium text-gray-900 truncate">{{ item.name }}</h3>
              </div>
              <p class="text-sm text-gray-500 truncate">{{ item.brand }} • {{ item.finish }}</p>
            </div>
            <Badge :class="getStockStatusClasses(item.status)" class="whitespace-nowrap border border-current bg-opacity-10">
              {{ item.status }}
            </Badge>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-xs text-gray-500">Current Stock</p>
              <p class="font-bold text-gray-900">{{ item.currentStock }} gallons</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Min. Required</p>
              <p class="font-medium text-gray-900">{{ item.minRequired }} gallons</p>
            </div>
          </div>
          
          <div class="mt-3">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
              <span>Stock Level</span>
              <span>{{ Math.min(item.stockPercentage, 100) }}%</span>
            </div>
            <Progress :model-value="item.stockPercentage" class="h-2 bg-gray-200" :indicator-class="getStockPercentageColor(item.stockPercentage)" />
          </div>
          
          <div class="mt-4 pt-3 border-t border-gray-100">
            <p class="text-xs text-gray-500 mb-2">Last Movement</p>
            <div class="flex items-center justify-between flex-wrap gap-2">
              <span class="text-sm text-gray-900">{{ item.lastMovement.type }}</span>
              <span class="text-sm text-gray-500">{{ item.lastMovement.quantity }} gallons</span>
              <span class="text-sm text-gray-500">{{ item.lastMovement.date }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="hidden md:block overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Paint Product</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Current Stock</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Min. Required</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Last Movement</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Stock Level</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody class="divide-y divide-gray-200">
            <TableRow v-for="item in filteredStockMovement" :key="item.id" class="hover:bg-gray-50 transition-colors">
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg flex-shrink-0" :style="{ backgroundColor: item.color }"></div>
                  <div>
                    <div class="font-medium text-gray-900">{{ item.name }}</div>
                    <div class="text-sm text-gray-500">{{ item.brand }} • {{ item.finish }}</div>
                    <div class="text-xs text-gray-400 mt-1">SKU: {{ item.sku }}</div>
                  </div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="text-center">
                  <div class="font-bold text-gray-900 text-lg">{{ item.currentStock }}</div>
                  <div class="text-xs text-gray-500">gallons</div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="font-medium text-gray-900">{{ item.minRequired }} gallons</div>
                <div class="text-xs text-gray-500 mt-1">Safety stock</div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <Badge :class="getStockStatusClasses(item.status)" class="border-0 bg-opacity-20 inline-flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full" :class="getStockStatusDotClasses(item.status)"></span>
                  {{ item.status }}
                </Badge>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div>
                  <div class="font-medium text-gray-900">{{ item.lastMovement.type }}</div>
                  <div class="text-sm text-gray-500">{{ item.lastMovement.quantity }} gallons</div>
                  <div class="text-xs text-gray-400 mt-1">{{ item.lastMovement.date }}</div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-32">
                    <Progress :model-value="item.stockPercentage" class="h-2 bg-gray-200" :indicator-class="getStockPercentageColor(item.stockPercentage)" />
                  </div>
                  <div class="text-right min-w-[80px]">
                    <div class="font-medium text-gray-900">{{ Math.min(item.stockPercentage, 100) }}%</div>
                    <div class="text-xs" :class="getStockTextColor(item.stockPercentage)">
                      {{ getStockText(item.stockPercentage) }}
                    </div>
                  </div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <Button v-if="item.status === 'Low Stock' || item.status === 'Critical'"
                          @click="restockProduct(item)"
                          size="sm"
                          class="bg-green-50 text-green-700 hover:bg-green-100 border-0 h-8">
                    Restock
                  </Button>
                  <Button @click="viewProductDetails(item)"
                          variant="secondary"
                          size="sm"
                          class="h-8">
                    Details
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      
      <div v-if="filteredStockMovement.length === 0" class="text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 text-gray-400">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No stock items found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Try adjusting your filters to find what you're looking for.</p>
      </div>
    </Card>
    
    <Card class="bg-gradient-to-r from-gray-800 to-gray-900 border-0 text-white shadow-lg">
      <CardContent class="p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <h2 class="text-xl font-bold mb-2">Report Summary</h2>
            <p class="opacity-90 max-w-2xl">This report provides a comprehensive overview of your paint distribution business performance for {{ selectedPeriod.label.toLowerCase() }}. Key insights include revenue growth, top-performing products, and inventory management recommendations.</p>
          </div>
          <div class="flex items-center gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold">{{ reportGeneratedTime }}</div>
              <div class="text-sm opacity-80">Generated on</div>
            </div>
            <Button @click="shareReport" variant="ghost" class="h-12 w-12 p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors text-white">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
              </svg>
            </Button>
          </div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6 pt-6 border-t border-white/20">
          <div class="bg-white/5 p-4 rounded-lg">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-green-500/20 rounded-lg">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
              </div>
              <h3 class="font-semibold">Revenue Growth</h3>
            </div>
            <p class="text-sm opacity-90">Revenue increased by {{ monthlySummary.revenueGrowth }}% compared to previous period.</p>
          </div>
          
          <div class="bg-white/5 p-4 rounded-lg">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-blue-500/20 rounded-lg">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
              </div>
              <h3 class="font-semibold">Top Product</h3>
            </div>
            <p class="text-sm opacity-90">{{ topSellingPaints[0].name }} generated ₱{{ formatCurrency(topSellingPaints[0].revenue) }} in revenue.</p>
          </div>
          
          <div class="bg-white/5 p-4 rounded-lg">
            <div class="flex items-center gap-3 mb-2">
              <div class="p-2 bg-yellow-500/20 rounded-lg">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.286 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
              </div>
              <h3 class="font-semibold">Stock Alert</h3>
            </div>
            <p class="text-sm opacity-90">{{ lowStockCount }} products require immediate restocking to avoid shortages.</p>
          </div>
        </div>
      </CardContent>
    </Card>
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

export default {
  name: 'BusinessReports',
  components: {
    Card,
    CardContent,
    Button,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    Badge,
    Progress
  },
  data() {
    return {
      selectedPeriod: { label: 'This Month', value: 'month' },
      selectedChart: 'bar',
      stockFilter: 'all',
      reportPeriods: [
        { label: 'Today', value: 'today' },
        { label: 'Yesterday', value: 'yesterday' },
        { label: 'Last 7 Days', value: 'week' },
        { label: 'This Month', value: 'month' },
        { label: 'Last Month', value: 'last_month' },
        { label: 'This Quarter', value: 'quarter' },
        { label: 'This Year', value: 'year' },
        { label: 'Custom Range', value: 'custom' }
      ],
      chartTypes: [
        { label: 'Bar', value: 'bar' },
        { label: 'Line', value: 'line' }
      ],
      monthlySummary: {
        totalRevenue: 245600,
        totalQuantity: 1560,
        totalOrders: 124,
        averageOrderValue: 1981,
        revenueGrowth: 12.5,
        orderGrowth: 8.2
      },
      monthlyChartData: [
        { month: 'Jan', revenue: 21500 },
        { month: 'Feb', revenue: 18900 },
        { month: 'Mar', revenue: 23400 },
        { month: 'Apr', revenue: 27800 },
        { month: 'May', revenue: 31200 },
        { month: 'Jun', revenue: 28700 },
        { month: 'Jul', revenue: 32400 },
        { month: 'Aug', revenue: 29800 },
        { month: 'Sep', revenue: 35600 },
        { month: 'Oct', revenue: 41200 },
        { month: 'Nov', revenue: 38900 },
        { month: 'Dec', revenue: 45600 }
      ],
      topSellingPaints: [
        {
          id: 1,
          name: 'Premium White Latex',
          brand: 'Boysen',
          finish: 'Satin',
          color: '#3B82F6',
          revenue: 85600,
          quantity: 425,
          marketShare: 18.5
        },
        {
          id: 2,
          name: 'Weatherproof Exterior',
          brand: 'Davies',
          finish: 'Gloss',
          color: '#10B981',
          revenue: 72100,
          quantity: 380,
          marketShare: 15.6
        },
        {
          id: 3,
          name: 'Anti-Mold Bathroom',
          brand: 'Island',
          finish: 'Semi-Gloss',
          color: '#8B5CF6',
          revenue: 64300,
          quantity: 320,
          marketShare: 13.9
        },
        {
          id: 4,
          name: 'Quick Dry Enamel',
          brand: 'Toyo',
          finish: 'Matte',
          color: '#EF4444',
          revenue: 52100,
          quantity: 275,
          marketShare: 11.2
        },
        {
          id: 5,
          name: 'Eco-Friendly Interior',
          brand: 'Boysen',
          finish: 'Flat',
          color: '#F59E0B',
          revenue: 43800,
          quantity: 245,
          marketShare: 9.5
        }
      ],
      stockMovement: [
        {
          id: 'STK-001',
          name: 'Premium White Latex',
          brand: 'Boysen',
          finish: 'Satin',
          sku: 'BYN-PWL-001',
          color: '#3B82F6',
          currentStock: 45,
          minRequired: 100,
          status: 'Low Stock',
          stockPercentage: 45,
          lastMovement: {
            type: 'Sale',
            quantity: -15,
            date: '2024-01-15'
          }
        },
        {
          id: 'STK-002',
          name: 'Weatherproof Exterior',
          brand: 'Davies',
          finish: 'Gloss',
          sku: 'DAV-WE-002',
          color: '#10B981',
          currentStock: 25,
          minRequired: 80,
          status: 'Critical',
          stockPercentage: 31,
          lastMovement: {
            type: 'Sale',
            quantity: -20,
            date: '2024-01-14'
          }
        },
        {
          id: 'STK-003',
          name: 'Anti-Mold Bathroom',
          brand: 'Island',
          finish: 'Semi-Gloss',
          sku: 'ISL-AMB-003',
          color: '#8B5CF6',
          currentStock: 85,
          minRequired: 60,
          status: 'Adequate',
          stockPercentage: 142,
          lastMovement: {
            type: 'Restock',
            quantity: +50,
            date: '2024-01-12'
          }
        },
        {
          id: 'STK-004',
          name: 'Quick Dry Enamel',
          brand: 'Toyo',
          finish: 'Matte',
          sku: 'TOY-QDE-004',
          color: '#EF4444',
          currentStock: 120,
          minRequired: 70,
          status: 'Overstock',
          stockPercentage: 171,
          lastMovement: {
            type: 'Restock',
            quantity: +40,
            date: '2024-01-10'
          }
        },
        {
          id: 'STK-005',
          name: 'Eco-Friendly Interior',
          brand: 'Boysen',
          finish: 'Flat',
          sku: 'BYN-EFI-005',
          color: '#F59E0B',
          currentStock: 65,
          minRequired: 50,
          status: 'Adequate',
          stockPercentage: 130,
          lastMovement: {
            type: 'Sale',
            quantity: -12,
            date: '2024-01-13'
          }
        },
        {
          id: 'STK-006',
          name: 'Heat Reflective Roof',
          brand: 'Davies',
          finish: 'Textured',
          sku: 'DAV-HRR-006',
          color: '#06B6D4',
          currentStock: 40,
          minRequired: 60,
          status: 'Low Stock',
          stockPercentage: 67,
          lastMovement: {
            type: 'Sale',
            quantity: -18,
            date: '2024-01-11'
          }
        }
      ]
    }
  },
  computed: {
    maxRevenue() {
      return Math.max(...this.monthlyChartData.map(m => m.revenue))
    },
    minRevenue() {
      return Math.min(...this.monthlyChartData.map(m => m.revenue))
    },
    linePoints() {
      const width = 400
      const height = 200
      const points = this.monthlyChartData.map((month, index) => {
        const x = (index / (this.monthlyChartData.length - 1)) * width
        const y = height - ((month.revenue - this.minRevenue) / (this.maxRevenue - this.minRevenue)) * height
        return { x, y }
      })
      return points
    },
    linePath() {
      if (this.linePoints.length === 0) return ''
      
      let path = `M ${this.linePoints[0].x} ${this.linePoints[0].y}`
      
      for (let i = 1; i < this.linePoints.length; i++) {
        path += ` L ${this.linePoints[i].x} ${this.linePoints[i].y}`
      }
      
      return path
    },
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
    totalProducts() {
      return this.stockMovement.length
    },
    averagePaintPrice() {
      const totalRevenue = this.topSellingPaints.reduce((sum, paint) => sum + paint.revenue, 0)
      const totalQuantity = this.topSellingPaints.reduce((sum, paint) => sum + paint.quantity, 0)
      return totalQuantity > 0 ? Math.round(totalRevenue / totalQuantity) : 0
    },
    lowStockCount() {
      return this.stockMovement.filter(item => item.status === 'Low Stock' || item.status === 'Critical').length
    },
    reportGeneratedTime() {
      return new Date().toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  },
  methods: {
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH').format(amount)
    },
    formatNumber(num) {
      return new Intl.NumberFormat('en-PH').format(num)
    },
    selectReportPeriod(value) {
      const period = this.reportPeriods.find(p => p.value === value)
      if (period) {
        this.selectedPeriod = period
      }
    },
    getRankColor(index) {
      const colors = [
        'bg-gradient-to-r from-yellow-500 to-yellow-600',
        'bg-gradient-to-r from-gray-400 to-gray-500',
        'bg-gradient-to-r from-amber-600 to-amber-700',
        'bg-gradient-to-r from-gray-500 to-gray-600',
        'bg-gradient-to-r from-gray-600 to-gray-700'
      ]
      return colors[index] || 'bg-gradient-to-r from-gray-300 to-gray-400'
    },
    getStockStatusClasses(status) {
      const classes = {
        'Adequate': 'bg-green-50 text-green-700 border border-green-200',
        'Low Stock': 'bg-yellow-50 text-yellow-700 border border-yellow-200',
        'Critical': 'bg-red-50 text-red-700 border border-red-200',
        'Overstock': 'bg-blue-50 text-blue-700 border border-blue-200'
      }
      return classes[status] || 'bg-gray-50 text-gray-700 border border-gray-200'
    },
    getStockStatusDotClasses(status) {
      const classes = {
        'Adequate': 'bg-green-500',
        'Low Stock': 'bg-yellow-500',
        'Critical': 'bg-red-500',
        'Overstock': 'bg-blue-500'
      }
      return classes[status] || 'bg-gray-500'
    },
    getStockPercentageColor(percentage) {
      if (percentage < 50) return 'bg-red-500'
      if (percentage < 80) return 'bg-yellow-500'
      if (percentage <= 120) return 'bg-green-500'
      return 'bg-blue-500'
    },
    getStockTextColor(percentage) {
      if (percentage < 50) return 'text-red-600'
      if (percentage < 80) return 'text-yellow-600'
      if (percentage <= 120) return 'text-green-600'
      return 'text-blue-600'
    },
    getStockText(percentage) {
      if (percentage < 50) return 'Critical'
      if (percentage < 80) return 'Low'
      if (percentage <= 120) return 'Adequate'
      return 'Overstock'
    },
    exportReport(format) {
      console.log(`Exporting report as ${format.toUpperCase()}...`)
      // In a real app, this would generate and download the report
    },
    printReport() {
      console.log('Printing report...')
      // In a real app, this would open print dialog
    },
    viewAllProducts() {
      console.log('Viewing all products...')
      // In a real app, this would navigate to products page
    },
    generateStockReport() {
      console.log('Generating stock report...')
      // In a real app, this would generate a detailed stock report
    },
    restockProduct(product) {
      console.log(`Restocking ${product.name}...`)
      // In a real app, this would open restocking modal
    },
    viewProductDetails(product) {
      console.log(`Viewing details for ${product.name}...`)
      // In a real app, this would open product details modal
    },
    shareReport() {
      console.log('Sharing report...')
      // In a real app, this would open share dialog
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for better mobile experience */
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}

/* Gradient text animation */
@keyframes gradientShift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradientShift 3s ease infinite;
}

/* Status indicator animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.bg-red-50 {
  animation: pulse 2s infinite;
}

/* Glass morphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
}
</style>