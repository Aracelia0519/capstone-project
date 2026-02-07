<template>
  <div class="p-6">
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Sales History</h1>
          <p class="text-gray-600 mt-2">Comprehensive record of all paint sales transactions</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
          <Select :model-value="selectedDateRangeValue" @update:model-value="selectDateRange">
            <SelectTrigger class="w-[180px] bg-white">
              <SelectValue :placeholder="selectedDateRange" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem v-for="range in dateRanges" :key="range.value" :value="range.value">
                {{ range.label }}
              </SelectItem>
            </SelectContent>
          </Select>
          
          <Button @click="exportData" variant="outline" class="bg-green-50 text-green-700 border-green-200 hover:bg-green-100 gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-sm font-medium">Export CSV</span>
          </Button>
        </div>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
        <Card class="bg-white border-gray-200 shadow-sm">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Total Sales</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">₱{{ formatCurrency(totalSales) }}</p>
                <div class="flex items-center mt-1">
                  <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                  </svg>
                  <span class="text-xs text-green-600 font-medium">+12.5% vs last period</span>
                </div>
              </div>
              <div class="p-3 bg-blue-50 rounded-xl">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white border-gray-200 shadow-sm">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Quantity Sold</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatNumber(totalQuantity) }}</p>
                <p class="text-xs text-gray-500 mt-1">gallons</p>
              </div>
              <div class="p-3 bg-green-50 rounded-xl">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white border-gray-200 shadow-sm">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Avg. Order Value</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">₱{{ formatCurrency(averageOrderValue) }}</p>
                <div class="flex items-center mt-1">
                  <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                  </svg>
                  <span class="text-xs text-green-600 font-medium">+8.2%</span>
                </div>
              </div>
              <div class="p-3 bg-purple-50 rounded-xl">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white border-gray-200 shadow-sm">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600">Active Providers</p>
                <p class="text-2xl font-bold text-gray-800 mt-2">{{ activeProviders }}</p>
                <p class="text-xs text-gray-500 mt-1">this month</p>
              </div>
              <div class="p-3 bg-orange-50 rounded-xl">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
    
    <Card class="border-gray-200 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-4">
            <h2 class="text-lg font-semibold text-gray-800">Sales Transactions</h2>
            <Badge variant="secondary" class="bg-blue-50 text-blue-600">
              {{ filteredSales.length }} records
            </Badge>
          </div>
          
          <div class="flex items-center gap-3">
            <div class="relative w-full sm:w-64">
              <Input 
                type="text" 
                v-model="searchQuery"
                placeholder="Search transactions..." 
                class="pl-10 pr-4 w-full bg-white border-gray-300"
              />
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
            </div>
            
            <Button variant="ghost" size="icon" @click="refreshData" class="text-gray-500 hover:text-gray-700 hover:bg-gray-100" title="Refresh Data">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
            </Button>
          </div>
        </div>
      </div>
      
      <div class="md:hidden divide-y divide-gray-200">
        <div v-for="sale in paginatedSales" :key="sale.id" class="p-4">
          <div class="flex justify-between items-start mb-3">
            <div>
              <span class="font-medium text-gray-900">#{{ sale.id }}</span>
              <span class="ml-2 text-sm text-gray-500">{{ formatDate(sale.date) }}</span>
            </div>
            <Badge class="bg-green-50 text-green-700 border-0 hover:bg-green-100">
              ₱{{ formatCurrency(sale.totalAmount) }}
            </Badge>
          </div>
          
          <div class="space-y-3">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-lg flex-shrink-0" :style="{ backgroundColor: sale.paintColor }"></div>
              <div class="flex-1">
                <p class="font-medium text-gray-900">{{ sale.paintProduct }}</p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-sm text-gray-600">{{ sale.paintBrand }}</span>
                  <span class="text-xs text-gray-500">•</span>
                  <span class="text-sm text-gray-600">{{ sale.paintFinish }}</span>
                </div>
                <div class="flex items-center gap-4 mt-2">
                  <span class="text-sm font-medium text-gray-900">{{ sale.quantity }} gallons</span>
                  <span class="text-sm text-gray-500">•</span>
                  <span class="text-sm text-gray-600">₱{{ formatCurrency(sale.unitPrice) }}/gallon</span>
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-gray-100">
              <div>
                <p class="text-xs text-gray-500 mb-1">Service Provider</p>
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-900 truncate">{{ sale.serviceProvider }}</p>
                </div>
              </div>
              
              <div v-if="sale.clientName">
                <p class="text-xs text-gray-500 mb-1">Client</p>
                <div class="flex items-center gap-2">
                  <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-900 truncate">{{ sale.clientName }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="hidden md:block overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <span>Transaction ID</span>
                  <button @click="sortBy('id')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                  </button>
                </div>
              </TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <span>Date</span>
                  <button @click="sortBy('date')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                  </button>
                </div>
              </TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Paint Product</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Quantity</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Service Provider</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Client</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Amount</TableHead>
              <TableHead class="py-3.5 px-6 text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody class="divide-y divide-gray-200">
            <TableRow v-for="sale in paginatedSales" :key="sale.id" class="hover:bg-gray-50 transition-colors">
              <TableCell class="py-4 px-6">
                <div class="font-medium text-gray-900">#{{ sale.id }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ sale.time }}</div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ formatDate(sale.date) }}</div>
                    <div class="text-sm text-gray-500">{{ sale.dayOfWeek }}</div>
                  </div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg flex-shrink-0" :style="{ backgroundColor: sale.paintColor }"></div>
                  <div>
                    <div class="font-medium text-gray-900">{{ sale.paintProduct }}</div>
                    <div class="text-sm text-gray-500">{{ sale.paintBrand }} • {{ sale.paintFinish }}</div>
                    <div class="text-xs text-gray-400 mt-1">₱{{ formatCurrency(sale.unitPrice) }}/gallon</div>
                  </div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="text-center">
                    <div class="font-bold text-gray-900 text-lg">{{ sale.quantity }}</div>
                    <div class="text-xs text-gray-500">gallons</div>
                  </div>
                  <div class="w-20">
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                      <div class="bg-green-500 h-1.5 rounded-full" :style="{ width: `${Math.min(sale.quantity / 5, 100)}%` }"></div>
                    </div>
                  </div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ sale.serviceProvider }}</div>
                    <div class="text-sm text-gray-500">{{ sale.providerLocation }}</div>
                  </div>
                </div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div v-if="sale.clientName" class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ sale.clientName }}</div>
                    <div v-if="sale.clientType" class="text-xs text-gray-500">{{ sale.clientType }}</div>
                  </div>
                </div>
                <div v-else class="text-gray-400 italic">Not specified</div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="font-bold text-gray-900">₱{{ formatCurrency(sale.totalAmount) }}</div>
                <div class="text-xs text-green-600 font-medium mt-1">Completed</div>
              </TableCell>
              
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-1">
                  <Button @click="viewDetails(sale)" variant="ghost" size="icon" class="text-gray-600 hover:bg-gray-100" title="View Details">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Button>
                  <Button @click="printInvoice(sale)" variant="ghost" size="icon" class="text-blue-600 hover:bg-blue-50 hover:text-blue-700" title="Print Invoice">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                  </Button>
                  <Button @click="duplicateTransaction(sale)" variant="ghost" size="icon" class="text-green-600 hover:bg-green-50 hover:text-green-700" title="Duplicate">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      
      <div v-if="filteredSales.length === 0" class="text-center py-12">
        <div class="w-16 h-16 mx-auto mb-4 text-gray-400">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No sales records found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">No sales transactions match your current filters.</p>
      </div>
      
      <div v-if="loading" class="py-12 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Loading sales data...</p>
      </div>
    </Card>
    
    <div v-if="filteredSales.length > 0" class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <div class="text-sm text-gray-700">
        Showing <span class="font-medium">{{ Math.min((currentPage - 1) * itemsPerPage + 1, filteredSales.length) }}</span>
        to <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredSales.length) }}</span>
        of <span class="font-medium">{{ filteredSales.length }}</span> transactions
      </div>
      <div class="flex items-center gap-2">
        <Button 
          variant="outline" 
          size="icon" 
          @click="prevPage" 
          :disabled="currentPage === 1" 
          class="h-8 w-8"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </Button>
        <div class="flex items-center gap-1">
          <Button 
            v-for="page in visiblePages" 
            :key="page"
            @click="goToPage(page)"
            size="sm"
            :variant="page === currentPage ? 'default' : 'ghost'"
            class="h-9 w-9 p-0"
            :class="page === currentPage ? 'bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 font-medium' : 'text-gray-600'"
          >
            {{ page }}
          </Button>
        </div>
        <Button 
          variant="outline" 
          size="icon" 
          @click="nextPage" 
          :disabled="currentPage * itemsPerPage >= filteredSales.length" 
          class="h-8 w-8"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </Button>
      </div>
    </div>
  </div>
</template>

<script>
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

export default {
  name: 'SalesHistory',
  components: {
    Card,
    CardContent,
    Button,
    Input,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    Badge
  },
  data() {
    return {
      searchQuery: '',
      selectedDateRangeValue: 'month',
      sortField: 'date',
      sortDirection: 'desc',
      currentPage: 1,
      itemsPerPage: 10,
      loading: false,
      salesData: [
        {
          id: 'SALE-00124',
          date: '2024-01-15',
          time: '10:30 AM',
          dayOfWeek: 'Monday',
          paintProduct: 'Premium White Latex',
          paintBrand: 'Boysen',
          paintFinish: 'Satin',
          paintColor: '#F8FAFC',
          quantity: 15,
          unitPrice: 1633,
          totalAmount: 24500,
          serviceProvider: 'Cavite Paint Services',
          providerLocation: 'Imus, Cavite',
          clientName: 'Juan Dela Cruz',
          clientType: 'Residential',
          paymentMethod: 'Bank Transfer'
        },
        {
          id: 'SALE-00123',
          date: '2024-01-14',
          time: '02:15 PM',
          dayOfWeek: 'Sunday',
          paintProduct: 'Weatherproof Exterior',
          paintBrand: 'Davies',
          paintFinish: 'Gloss',
          paintColor: '#3B82F6',
          quantity: 8,
          unitPrice: 1950,
          totalAmount: 15600,
          serviceProvider: 'Imus Paint Center',
          providerLocation: 'Bacoor, Cavite',
          clientName: 'Maria Santos',
          clientType: 'Commercial',
          paymentMethod: 'Cash'
        },
        {
          id: 'SALE-00122',
          date: '2024-01-13',
          time: '09:45 AM',
          dayOfWeek: 'Saturday',
          paintProduct: 'Anti-Mold Bathroom',
          paintBrand: 'Island',
          paintFinish: 'Semi-Gloss',
          paintColor: '#10B981',
          quantity: 25,
          unitPrice: 1686,
          totalAmount: 42150,
          serviceProvider: 'Bacoor Construction',
          providerLocation: 'Dasmarinas, Cavite',
          clientName: 'Robert Lim Construction',
          clientType: 'Contractor',
          paymentMethod: 'Bank Transfer'
        },
        {
          id: 'SALE-00121',
          date: '2024-01-12',
          time: '04:20 PM',
          dayOfWeek: 'Friday',
          paintProduct: 'Quick Dry Enamel',
          paintBrand: 'Toyo',
          paintFinish: 'Matte',
          paintColor: '#EF4444',
          quantity: 5,
          unitPrice: 1780,
          totalAmount: 8900,
          serviceProvider: 'Dasmarinas Painters',
          providerLocation: 'Trece Martires',
          clientName: 'Sarah Gomez',
          clientType: 'Residential',
          paymentMethod: 'GCash'
        },
        {
          id: 'SALE-00120',
          date: '2024-01-11',
          time: '11:00 AM',
          dayOfWeek: 'Thursday',
          paintProduct: 'Eco-Friendly Interior',
          paintBrand: 'Boysen',
          paintFinish: 'Flat',
          paintColor: '#8B5CF6',
          quantity: 12,
          unitPrice: 1558,
          totalAmount: 18700,
          serviceProvider: 'Trece Martires Co.',
          providerLocation: 'General Trias',
          clientName: 'James Wilson',
          clientType: 'Commercial',
          paymentMethod: 'Credit Card'
        },
        {
          id: 'SALE-00119',
          date: '2024-01-10',
          time: '03:45 PM',
          dayOfWeek: 'Wednesday',
          paintProduct: 'Heat Reflective Roof',
          paintBrand: 'Davies',
          paintFinish: 'Textured',
          paintColor: '#F59E0B',
          quantity: 18,
          unitPrice: 1800,
          totalAmount: 32400,
          serviceProvider: 'General Trias Paint',
          providerLocation: 'Silang, Cavite',
          clientName: 'Andrea Torres',
          clientType: 'Residential',
          paymentMethod: 'Bank Transfer'
        },
        {
          id: 'SALE-00118',
          date: '2024-01-09',
          time: '01:30 PM',
          dayOfWeek: 'Tuesday',
          paintProduct: 'Marine Grade',
          paintBrand: 'Island',
          paintFinish: 'High Gloss',
          paintColor: '#06B6D4',
          quantity: 7,
          unitPrice: 1921,
          totalAmount: 13450,
          serviceProvider: 'Silang Hardware',
          providerLocation: 'Tagaytay City',
          clientName: 'Michael Tan',
          clientType: 'Commercial',
          paymentMethod: 'Cash'
        },
        {
          id: 'SALE-00117',
          date: '2024-01-08',
          time: '10:15 AM',
          dayOfWeek: 'Monday',
          paintProduct: 'Metal Primer',
          paintBrand: 'Boysen',
          paintFinish: 'Flat',
          paintColor: '#6B7280',
          quantity: 20,
          unitPrice: 1450,
          totalAmount: 29000,
          serviceProvider: 'Cavite Industrial Paint',
          providerLocation: 'Kawit, Cavite',
          clientName: null,
          clientType: null,
          paymentMethod: 'Bank Transfer'
        },
        {
          id: 'SALE-00116',
          date: '2024-01-07',
          time: '02:45 PM',
          dayOfWeek: 'Sunday',
          paintProduct: 'Wood Stain',
          paintBrand: 'Toyo',
          paintFinish: 'Satin',
          paintColor: '#92400E',
          quantity: 10,
          unitPrice: 2100,
          totalAmount: 21000,
          serviceProvider: 'Cavite Paint Depot',
          providerLocation: 'Naic, Cavite',
          clientName: 'Luis Furniture Co.',
          clientType: 'Manufacturer',
          paymentMethod: 'Credit Card'
        },
        {
          id: 'SALE-00115',
          date: '2024-01-06',
          time: '11:30 AM',
          dayOfWeek: 'Saturday',
          paintProduct: 'Epoxy Floor Coating',
          paintBrand: 'Davies',
          paintFinish: 'Gloss',
          paintColor: '#1F2937',
          quantity: 30,
          unitPrice: 2500,
          totalAmount: 75000,
          serviceProvider: 'Cavite Construction Supply',
          providerLocation: 'Carmona, Cavite',
          clientName: 'ABC Manufacturing',
          clientType: 'Industrial',
          paymentMethod: 'Bank Transfer'
        }
      ]
    }
  },
  computed: {
    dateRanges() {
      return [
        { label: 'Today', value: 'today' },
        { label: 'Yesterday', value: 'yesterday' },
        { label: 'Last 7 days', value: 'week' },
        { label: 'Last 30 days', value: 'month' },
        { label: 'Last quarter', value: 'quarter' },
        { label: 'This year', value: 'year' },
        { label: 'All time', value: 'all' }
      ]
    },
    selectedDateRange() {
      const range = this.dateRanges.find(r => r.value === this.selectedDateRangeValue)
      return range ? range.label : 'Select Date Range'
    },
    filteredSales() {
      let filtered = [...this.salesData]
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(sale =>
          sale.id.toLowerCase().includes(query) ||
          sale.paintProduct.toLowerCase().includes(query) ||
          sale.serviceProvider.toLowerCase().includes(query) ||
          (sale.clientName && sale.clientName.toLowerCase().includes(query))
        )
      }
      
      // Apply sorting
      filtered.sort((a, b) => {
        let aValue = a[this.sortField]
        let bValue = b[this.sortField]
        
        if (this.sortField === 'date') {
          aValue = new Date(a.date)
          bValue = new Date(b.date)
        }
        
        if (this.sortDirection === 'asc') {
          return aValue > bValue ? 1 : -1
        } else {
          return aValue < bValue ? 1 : -1
        }
      })
      
      return filtered
    },
    paginatedSales() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredSales.slice(start, end)
    },
    totalSales() {
      return this.filteredSales.reduce((sum, sale) => sum + sale.totalAmount, 0)
    },
    totalQuantity() {
      return this.filteredSales.reduce((sum, sale) => sum + sale.quantity, 0)
    },
    averageOrderValue() {
      return this.filteredSales.length > 0 ? Math.round(this.totalSales / this.filteredSales.length) : 0
    },
    activeProviders() {
      const uniqueProviders = new Set(this.filteredSales.map(sale => sale.serviceProvider))
      return uniqueProviders.size
    },
    visiblePages() {
      const totalPages = Math.ceil(this.filteredSales.length / this.itemsPerPage)
      const pages = []
      const maxVisible = 5
      let start = Math.max(1, this.currentPage - 2)
      let end = Math.min(totalPages, start + maxVisible - 1)
      
      if (end - start < maxVisible - 1) {
        start = Math.max(1, end - maxVisible + 1)
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    }
  },
  methods: {
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH').format(amount)
    },
    formatNumber(num) {
      return new Intl.NumberFormat('en-PH').format(num)
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    },
    selectDateRange(value) {
      this.selectedDateRangeValue = value
      this.currentPage = 1
    },
    sortBy(field) {
      if (this.sortField === field) {
        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
      } else {
        this.sortField = field
        this.sortDirection = 'desc'
      }
    },
    viewDetails(sale) {
      console.log('Viewing details:', sale)
      // In a real app, this would open a modal with detailed information
    },
    printInvoice(sale) {
      console.log('Printing invoice for:', sale.id)
      // In a real app, this would generate and print an invoice
    },
    duplicateTransaction(sale) {
      console.log('Duplicating transaction:', sale.id)
      // In a real app, this would duplicate the transaction
    },
    exportData() {
      console.log('Exporting data as CSV')
      // In a real app, this would export the filtered data as CSV
    },
    refreshData() {
      this.loading = true
      // Simulate API call
      setTimeout(() => {
        this.loading = false
        console.log('Data refreshed')
      }, 1000)
    },
    nextPage() {
      if (this.currentPage * this.itemsPerPage < this.filteredSales.length) {
        this.currentPage++
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--
      }
    },
    goToPage(page) {
      this.currentPage = page
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

/* Animation for loading spinner */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Custom color dots for paint samples */
.w-10.h-10.rounded-lg {
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.w-8.h-8.rounded-lg {
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
</style>