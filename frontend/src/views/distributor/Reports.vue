<template>
  <div class="p-6">
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Business Reports</h1>
          <p class="text-gray-600 mt-2">Monitor and analyze your paint distribution business performance</p>
        </div>
        
        <!-- Report Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <!-- Report Period Selector -->
          <div class="relative">
            <button @click="togglePeriodPicker"
                    class="flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-700 hover:bg-gray-50 transition-colors">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <span class="text-sm font-medium">{{ selectedPeriod.label }}</span>
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            
            <!-- Period Picker Dropdown -->
            <div v-if="showPeriodPicker" class="absolute top-full mt-1 left-0 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-10 p-3">
              <div class="space-y-1">
                <button v-for="period in reportPeriods" 
                        :key="period.value"
                        @click="selectReportPeriod(period.value)"
                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-gray-100 transition-colors flex items-center justify-between"
                        :class="{'bg-blue-50 text-blue-600': selectedPeriod.value === period.value}">
                  <span>{{ period.label }}</span>
                  <svg v-if="selectedPeriod.value === period.value" class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Export Menu -->
          <div class="relative">
            <button @click="toggleExportMenu"
                    class="flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 text-white border border-green-600 rounded-lg hover:bg-green-700 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              <span class="text-sm font-medium">Export</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            
            <!-- Export Options Dropdown -->
            <div v-if="showExportMenu" class="absolute top-full mt-1 right-0 w-56 bg-white border border-gray-200 rounded-xl shadow-lg z-10 p-2">
              <div class="space-y-1">
                <button @click="exportReport('pdf')"
                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-red-50 text-red-600 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                  </svg>
                  Export as PDF
                </button>
                <button @click="exportReport('excel')"
                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-green-50 text-green-600 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Export as Excel
                </button>
                <button @click="exportReport('csv')"
                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-blue-50 text-blue-600 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                  Export as CSV
                </button>
                <button @click="printReport"
                        class="w-full text-left px-3 py-2.5 text-sm rounded-lg hover:bg-gray-100 text-gray-700 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                  </svg>
                  Print Report
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Report Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <!-- Total Revenue -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-5 text-white">
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
        </div>
        
        <!-- Total Quantity Sold -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-5 text-white">
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
        </div>
        
        <!-- Total Orders -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-5 text-white">
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
        </div>
        
        <!-- Average Order Value -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-5 text-white">
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
        </div>
      </div>
    </div>
    
    <!-- Report Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Monthly Sales Chart -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-800">Monthly Sales Summary</h2>
            <p class="text-gray-600 text-sm mt-1">Revenue trends over the last 12 months</p>
          </div>
          <div class="flex items-center gap-2 mt-2 sm:mt-0">
            <button v-for="chartType in chartTypes" 
                    :key="chartType.value"
                    @click="selectedChart = chartType.value"
                    class="px-3 py-1.5 text-sm rounded-lg transition-colors"
                    :class="selectedChart === chartType.value 
                           ? 'bg-blue-50 text-blue-600 font-medium' 
                           : 'text-gray-600 hover:bg-gray-100'">
              {{ chartType.label }}
            </button>
          </div>
        </div>
        
        <!-- Chart Container -->
        <div class="h-72 relative min-h-[300px]">
          <!-- Bar Chart -->
          <div v-if="selectedChart === 'bar'" class="h-full flex items-end justify-between pt-4 pb-8">
            <div v-for="month in monthlyChartData" :key="month.month" class="flex-1 mx-1 flex flex-col items-center">
              <div class="w-full flex justify-center">
                <div class="w-8 bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg transition-all hover:opacity-80"
                     :style="{ height: `${Math.min((month.revenue / maxRevenue) * 100, 95)}%` }"
                     :title="`₱${formatCurrency(month.revenue)}`">
                </div>
              </div>
              <span class="text-xs text-gray-500 mt-2">{{ month.month }}</span>
            </div>
          </div>
          
          <!-- Line Chart -->
          <div v-if="selectedChart === 'line'" class="h-full pt-4 pb-8">
            <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="xMidYMid meet">
              <!-- Grid Lines -->
              <line x1="0" y1="0" x2="400" y2="0" stroke="#e5e7eb" stroke-width="1" />
              <line x1="0" y1="50" x2="400" y2="50" stroke="#e5e7eb" stroke-width="1" />
              <line x1="0" y1="100" x2="400" y2="100" stroke="#e5e7eb" stroke-width="1" />
              <line x1="0" y1="150" x2="400" y2="150" stroke="#e5e7eb" stroke-width="1" />
              
              <!-- Line Path -->
              <path :d="linePath" fill="none" stroke="#3b82f6" stroke-width="2" />
              
              <!-- Data Points -->
              <circle v-for="(point, index) in linePoints" 
                      :key="index"
                      :cx="point.x" 
                      :cy="point.y" 
                      r="3" 
                      fill="#3b82f6" 
                      stroke="white" 
                      stroke-width="2" />
            </svg>
          </div>
          
          <!-- Revenue Values -->
          <div class="absolute bottom-2 left-0 right-0 flex justify-between text-xs text-gray-500 px-2">
            <span>₱{{ formatCurrency(minRevenue) }}</span>
            <span>₱{{ formatCurrency((minRevenue + maxRevenue) / 2) }}</span>
            <span>₱{{ formatCurrency(maxRevenue) }}</span>
          </div>
        </div>
        
        <!-- Chart Legend -->
        <div class="flex items-center justify-center gap-6 mt-4 pt-4 border-t border-gray-100">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
            <span class="text-sm text-gray-600">Monthly Revenue</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
            <span class="text-sm text-gray-600">Target</span>
          </div>
        </div>
      </div>
      
      <!-- Top-Selling Paints -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-800">Top-Selling Paints</h2>
            <p class="text-gray-600 text-sm mt-1">Best performing products this period</p>
          </div>
          <button @click="viewAllProducts"
                  class="mt-2 sm:mt-0 text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
            View All
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <div v-for="(product, index) in topSellingPaints" :key="product.id" class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors">
            <!-- Rank Badge -->
            <div class="flex-shrink-0">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                   :class="getRankColor(index)">
                <span class="text-sm font-bold text-white">{{ index + 1 }}</span>
              </div>
            </div>
            
            <!-- Product Info -->
            <div class="flex-1">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="font-medium text-gray-900">{{ product.name }}</h3>
                  <p class="text-sm text-gray-500">{{ product.brand }} • {{ product.finish }}</p>
                </div>
                <div class="text-right">
                  <p class="font-bold text-gray-900">₱{{ formatCurrency(product.revenue) }}</p>
                  <p class="text-sm text-gray-500">{{ product.quantity }} gallons</p>
                </div>
              </div>
              
              <!-- Progress Bar -->
              <div class="mt-2">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                  <span>Market share</span>
                  <span>{{ product.marketShare }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                  <div class="h-1.5 rounded-full max-w-full" 
                       :style="{ width: `${Math.min(product.marketShare, 100)}%`, backgroundColor: product.color }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Quick Stats -->
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
      </div>
    </div>
    
    <!-- Stock Movement Report -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
      <!-- Table Header -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-lg font-semibold text-gray-800">Stock Movement Report</h2>
            <p class="text-gray-600 text-sm mt-1">Inventory changes and restocking needs</p>
          </div>
          <div class="flex items-center gap-3">
            <div class="relative">
              <select v-model="stockFilter" 
                      class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none cursor-pointer text-sm">
                <option value="all">All Products</option>
                <option value="low">Low Stock</option>
                <option value="critical">Critical</option>
                <option value="overstock">Overstock</option>
              </select>
              <div class="absolute left-3 top-1/2 transform -translate-y-1/2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
              </div>
            </div>
            
            <button @click="generateStockReport"
                    class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="text-sm font-medium">Generate Report</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Mobile View -->
      <div class="md:hidden divide-y divide-gray-200">
        <div v-for="item in filteredStockMovement" :key="item.id" class="p-4">
          <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-1">
                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: item.color }"></div>
                <h3 class="font-medium text-gray-900">{{ item.name }}</h3>
              </div>
              <p class="text-sm text-gray-500">{{ item.brand }} • {{ item.finish }}</p>
            </div>
            <span :class="getStockStatusClasses(item.status)" class="px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap">
              {{ item.status }}
            </span>
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
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div class="h-2 rounded-full max-w-full" 
                   :class="getStockPercentageColor(item.stockPercentage)"
                   :style="{ width: `${Math.min(item.stockPercentage, 100)}%` }"></div>
            </div>
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
      
      <!-- Desktop Table -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Paint Product</th>
              <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Current Stock</th>
              <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Min. Required</th>
              <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
              <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Last Movement</th>
              <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Stock Level</th>
              <th class="py-3.5 px-6 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="item in filteredStockMovement" :key="item.id" class="hover:bg-gray-50 transition-colors">
              <!-- Paint Product -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg flex-shrink-0" :style="{ backgroundColor: item.color }"></div>
                  <div>
                    <div class="font-medium text-gray-900">{{ item.name }}</div>
                    <div class="text-sm text-gray-500">{{ item.brand }} • {{ item.finish }}</div>
                    <div class="text-xs text-gray-400 mt-1">SKU: {{ item.sku }}</div>
                  </div>
                </div>
              </td>
              
              <!-- Current Stock -->
              <td class="py-4 px-6">
                <div class="text-center">
                  <div class="font-bold text-gray-900 text-lg">{{ item.currentStock }}</div>
                  <div class="text-xs text-gray-500">gallons</div>
                </div>
              </td>
              
              <!-- Minimum Required -->
              <td class="py-4 px-6">
                <div class="font-medium text-gray-900">{{ item.minRequired }} gallons</div>
                <div class="text-xs text-gray-500 mt-1">Safety stock</div>
              </td>
              
              <!-- Status -->
              <td class="py-4 px-6">
                <span :class="getStockStatusClasses(item.status)" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                  <span class="w-2 h-2 rounded-full mr-2" :class="getStockStatusDotClasses(item.status)"></span>
                  {{ item.status }}
                </span>
              </td>
              
              <!-- Last Movement -->
              <td class="py-4 px-6">
                <div>
                  <div class="font-medium text-gray-900">{{ item.lastMovement.type }}</div>
                  <div class="text-sm text-gray-500">{{ item.lastMovement.quantity }} gallons</div>
                  <div class="text-xs text-gray-400 mt-1">{{ item.lastMovement.date }}</div>
                </div>
              </td>
              
              <!-- Stock Level -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-32">
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                      <div class="h-2 rounded-full max-w-full" 
                           :class="getStockPercentageColor(item.stockPercentage)"
                           :style="{ width: `${Math.min(item.stockPercentage, 100)}%` }"></div>
                    </div>
                  </div>
                  <div class="text-right min-w-[80px]">
                    <div class="font-medium text-gray-900">{{ Math.min(item.stockPercentage, 100) }}%</div>
                    <div class="text-xs" :class="getStockTextColor(item.stockPercentage)">
                      {{ getStockText(item.stockPercentage) }}
                    </div>
                  </div>
                </div>
              </td>
              
              <!-- Action -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <button v-if="item.status === 'Low Stock' || item.status === 'Critical'"
                          @click="restockProduct(item)"
                          class="px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg text-sm font-medium transition-colors">
                    Restock
                  </button>
                  <button @click="viewProductDetails(item)"
                          class="px-3 py-1.5 bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg text-sm font-medium transition-colors">
                    Details
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Empty State -->
      <div v-if="filteredStockMovement.length === 0" class="text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 text-gray-400">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No stock items found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Try adjusting your filters to find what you're looking for.</p>
      </div>
    </div>
    
    <!-- Report Summary -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 rounded-xl p-6 text-white">
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
          <button @click="shareReport"
                  class="p-3 bg-white/10 hover:bg-white/20 rounded-xl transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Key Insights -->
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
    </div>
  </div>
</template>

<script>
export default {
  name: 'BusinessReports',
  data() {
    return {
      showPeriodPicker: false,
      showExportMenu: false,
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
    togglePeriodPicker() {
      this.showPeriodPicker = !this.showPeriodPicker
      this.showExportMenu = false
    },
    toggleExportMenu() {
      this.showExportMenu = !this.showExportMenu
      this.showPeriodPicker = false
    },
    selectReportPeriod(value) {
      this.selectedPeriod = this.reportPeriods.find(p => p.value === value)
      this.showPeriodPicker = false
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
      this.showExportMenu = false
      // In a real app, this would generate and download the report
    },
    printReport() {
      console.log('Printing report...')
      this.showExportMenu = false
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
  
  .overflow-x-auto::-webkit-scrollbar {
    height: 4px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
  }
}

/* Smooth transitions for interactive elements */
button, select, input {
  transition: all 0.2s ease-in-out;
}

/* Hover effects for table rows */
.hover\\:bg-gray-50:hover {
  transition: background-color 0.2s ease;
}

/* Custom focus styles */
select:focus, input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
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

/* Animation for fade-in effects */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

tbody tr {
  animation: fadeIn 0.3s ease-out;
}

/* Custom color dots for paint samples */
.w-10.h-10.rounded-lg {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.w-3.h-3.rounded-full {
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  :deep(.text-2xl) {
    font-size: 1.5rem;
  }
  
  :deep(.text-3xl) {
    font-size: 1.75rem;
  }
}

/* Card hover effects */
.bg-white {
  transition: all 0.2s ease-in-out;
}

.bg-white:hover {
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.05);
}

/* Smooth chart animations */
.bg-gradient-to-t {
  transition: height 0.3s ease;
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

/* Smooth progress bar animations */
.h-2.rounded-full {
  transition: width 0.5s ease;
}

/* Custom dropdown shadow */
.shadow-lg {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Responsive chart adjustments */
@media (max-width: 1024px) {
  .h-72 {
    height: 16rem;
  }
}

@media (max-width: 640px) {
  .h-72 {
    height: 12rem;
  }
}

/* Fixed overflow issues */
.max-w-full {
  max-width: 100%;
}

.overflow-hidden {
  overflow: hidden;
}

/* Better spacing for mobile views */
@media (max-width: 640px) {
  .flex-wrap {
    flex-wrap: wrap;
  }
  
  .gap-2 {
    gap: 0.5rem;
  }
}

/* Prevent content overlap in charts */
.min-h-\[300px\] {
  min-height: 300px;
}

/* Ensure proper spacing in chart container */
.pb-8 {
  padding-bottom: 2rem;
}
</style>