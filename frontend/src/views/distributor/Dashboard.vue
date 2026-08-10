<template>
  <div class="min-h-screen p-4 md:p-8 font-sans selection:bg-indigo-500/30">
    
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 md:mb-10 gap-6">
      <div class="space-y-1">
        <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-slate-900 via-indigo-800 to-slate-900 tracking-tight">
          Distributor Dashboard
        </h1>
        <p class="text-slate-500 font-medium tracking-wide text-sm">Combined Overview: Finance, HR, and Operations</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <Button @click="showDepositDialog = true" class="bg-emerald-500 hover:bg-emerald-600 text-white shadow-lg shadow-emerald-500/30 transition-all hover:-translate-y-0.5 gap-2 rounded-full px-6">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Deposit Funds
        </Button>
        <Button @click="confirmRefresh" variant="outline" class="bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-sm transition-all hover:-translate-y-0.5 gap-2 rounded-full px-6">
          <svg class="w-4 h-4" :class="{'animate-spin text-indigo-600': isLoading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh Data
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col justify-center items-center py-32 space-y-4">
      <div class="relative w-16 h-16">
        <div class="absolute inset-0 rounded-full border-t-2 border-indigo-500 animate-spin"></div>
        <div class="absolute inset-2 rounded-full border-r-2 border-emerald-400 animate-spin animation-delay-150"></div>
      </div>
      <p class="text-slate-400 font-medium animate-pulse">Syncing your metrics...</p>
    </div>

    <!-- Main Dashboard -->
    <div v-else class="animate-in fade-in slide-in-from-bottom-4 duration-700">
      
      <!-- Top Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <!-- Sales Card -->
        <Card class="relative overflow-hidden bg-white group hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1 transition-all duration-300 border-0 ring-1 ring-slate-100">
          <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full blur-3xl -mr-16 -mt-16 transition-transform group-hover:scale-150"></div>
          <CardContent class="p-6 relative z-10">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl ring-1 ring-indigo-100 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="flex items-center text-sm font-bold px-2.5 py-1 rounded-full" 
                    :class="dashboardData.finance.salesChangePercent >= 0 ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50'">
                {{ dashboardData.finance.salesChangePercent >= 0 ? '↑' : '↓' }} {{ Math.abs(dashboardData.finance.salesChangePercent) }}%
              </span>
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-400 mb-1 uppercase tracking-wider">Period Gross Sales</p>
              <h3 class="text-3xl font-extrabold text-slate-800 tracking-tight">₱{{ formatCurrency(dashboardData.finance.totalSales) }}</h3>
            </div>
          </CardContent>
        </Card>

        <!-- Orders Card -->
        <Card class="relative overflow-hidden bg-white group hover:shadow-2xl hover:shadow-purple-500/10 hover:-translate-y-1 transition-all duration-300 border-0 ring-1 ring-slate-100">
          <div class="absolute top-0 right-0 w-32 h-32 bg-purple-50 rounded-full blur-3xl -mr-16 -mt-16 transition-transform group-hover:scale-150"></div>
          <CardContent class="p-6 relative z-10">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-purple-50 text-purple-600 rounded-xl ring-1 ring-purple-100 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <span class="text-sm font-bold text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200/50">
                {{ dashboardData.ecommerce.pendingOrders }} Pending
              </span>
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-400 mb-1 uppercase tracking-wider">Total Orders</p>
              <h3 class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ dashboardData.ecommerce.totalOrders }}</h3>
            </div>
          </CardContent>
        </Card>

        <!-- HR Card -->
        <Card class="relative overflow-hidden bg-white group hover:shadow-2xl hover:shadow-orange-500/10 hover:-translate-y-1 transition-all duration-300 border-0 ring-1 ring-slate-100">
          <div class="absolute top-0 right-0 w-32 h-32 bg-orange-50 rounded-full blur-3xl -mr-16 -mt-16 transition-transform group-hover:scale-150"></div>
          <CardContent class="p-6 relative z-10">
            <div class="flex items-center justify-between mb-4">
              <div class="p-3 bg-orange-50 text-orange-600 rounded-xl ring-1 ring-orange-100 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <span class="text-sm font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200/50">
                {{ dashboardData.hr.activeEmployees }} Active
              </span>
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-400 mb-1 uppercase tracking-wider">Total Employees</p>
              <h3 class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ dashboardData.hr.totalEmployees }}</h3>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Shadcn-style AREA CHART for Revenue Trend -->
        <Card class="border-0 ring-1 ring-slate-100 shadow-md bg-white lg:col-span-2 flex flex-col relative overflow-hidden">
          <CardContent class="p-6 sm:p-8 flex-1 flex flex-col relative z-10">
            <div class="flex items-center justify-between mb-8">
              <div>
                <h2 class="text-xl font-bold text-slate-800 tracking-tight">Revenue Trend</h2>
                <p class="text-slate-500 text-sm mt-1">Gross sales over the selected period</p>
              </div>
              <!-- Added Dropdown for Period Selection -->
              <Select v-model="selectedPeriod" @update:model-value="fetchDashboardData">
                <SelectTrigger class="w-[140px] bg-white border-slate-200 text-slate-700 h-9 rounded-lg shadow-sm font-medium">
                  <SelectValue placeholder="Period" />
                </SelectTrigger>
                <SelectContent class="bg-white border-slate-200 text-slate-700 rounded-lg shadow-xl">
                  <SelectItem value="7d" class="focus:bg-slate-50">Last 7 Days</SelectItem>
                  <SelectItem value="30d" class="focus:bg-slate-50">Last 30 Days</SelectItem>
                  <SelectItem value="3m" class="focus:bg-slate-50">Last 3 Months</SelectItem>
                  <SelectItem value="12m" class="focus:bg-slate-50">Last 12 Months</SelectItem>
                </SelectContent>
              </Select>
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
              <svg v-if="dashboardData.finance.monthlyRevenue.length > 0" viewBox="0 0 800 250" preserveAspectRatio="none" class="absolute inset-0 w-full h-full overflow-visible">
                <defs>
                  <linearGradient id="colorRevenue" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="5%" stop-color="#6366f1" stop-opacity="0.2"/>
                    <stop offset="95%" stop-color="#6366f1" stop-opacity="0"/>
                  </linearGradient>
                </defs>
                <!-- Filled Area -->
                <path :d="areaPath" fill="url(#colorRevenue)" class="transition-all duration-500 ease-in-out" />
                <!-- Stroke Line -->
                <path :d="linePath" fill="none" stroke="#6366f1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-in-out" />
                <!-- Interactive Data Points -->
                <circle v-for="(point, index) in chartPoints" :key="'point-'+index"
                        :cx="point.x" :cy="point.y" r="4" 
                        fill="#ffffff" stroke="#6366f1" stroke-width="2" 
                        class="transition-all duration-300"
                        :class="hoveredPoint === index ? 'r-6 fill-[#6366f1] stroke-white stroke-[3px] drop-shadow-md' : 'fill-white opacity-0 group-hover:opacity-100'"
                />
              </svg>

              <!-- Interactive Hover Overlay System -->
              <div class="absolute inset-0 flex" v-if="dashboardData.finance.monthlyRevenue.length > 0">
                <div v-for="(point, index) in chartPoints" :key="'overlay-'+index" 
                     class="flex-1 h-full relative group/col cursor-pointer"
                     @mouseenter="hoveredPoint = index"
                     @mouseleave="hoveredPoint = null">
                     
                  <!-- Vertical Dashed Line -->
                  <div class="absolute top-0 bottom-0 left-1/2 -translate-x-1/2 w-px border-l border-dashed border-indigo-300 opacity-0 transition-opacity"
                       :class="{'opacity-100': hoveredPoint === index}"></div>

                  <!-- Tooltip -->
                  <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-4 px-3 py-2 bg-slate-800 text-white text-xs rounded-md shadow-xl border border-slate-700 pointer-events-none opacity-0 transition-all z-20 whitespace-nowrap"
                       :class="{'opacity-100 -translate-y-2': hoveredPoint === index}">
                    <div class="font-bold text-sm">₱{{ formatCurrency(point.value) }}</div>
                    <div class="text-slate-300 mt-0.5 font-medium">{{ point.label }}</div>
                  </div>
                </div>
              </div>

              <!-- Empty State Fallback -->
              <div v-if="dashboardData.finance.monthlyRevenue.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 border-2 border-dashed border-slate-100 rounded-2xl">
                <svg class="w-10 h-10 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                No revenue data available
              </div>
            </div>
            
            <!-- X-Axis Labels -->
            <div class="w-full flex justify-between mt-4 text-[10px] sm:text-xs font-semibold text-slate-400 relative z-10" v-if="dashboardData.finance.monthlyRevenue.length > 0">
              <span v-for="(point, index) in chartPoints" :key="'label-'+point.label" 
                    class="flex-1 text-center truncate px-1"
                    :class="{'opacity-0 sm:opacity-100': chartPoints.length > 15 && index % 2 !== 0}">
                {{ point.label }}
              </span>
            </div>
          </CardContent>
        </Card>

        <!-- Shadcn-style DONUT CHART for Products -->
        <Card class="border-0 ring-1 ring-slate-100 shadow-md bg-white lg:col-span-1 flex flex-col h-full">
          <CardContent class="p-6 sm:p-8 flex-1 flex flex-col">
            <div class="mb-6">
              <h3 class="text-xl font-bold text-slate-800 tracking-tight">Top Products</h3>
              <p class="text-slate-500 text-sm mt-1">Best selling items distribution</p>
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
                  <span class="text-xl sm:text-2xl font-extrabold text-slate-800">₱{{ formatShortCurrency(totalProductSales) }}</span>
                </div>
              </div>

              <!-- List / Legend -->
              <div class="w-full space-y-2 mt-2" v-if="donutData.length > 0">
                <div v-for="(product, index) in donutData" :key="product.id" 
                     class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer group border border-transparent hover:border-slate-100"
                     @mouseenter="activeSlice = index"
                     @mouseleave="activeSlice = null">
                  <div class="flex items-center gap-3 min-w-0">
                    <div class="w-3.5 h-3.5 flex-shrink-0 rounded-full shadow-sm transition-transform group-hover:scale-125" :style="{ backgroundColor: product.color }"></div>
                    <h4 class="text-slate-600 font-semibold text-sm truncate group-hover:text-indigo-600 transition-colors" :title="product.name">
                      {{ product.name }}
                    </h4>
                  </div>
                  <div class="text-right flex-shrink-0 ml-2">
                    <div class="text-slate-800 font-bold text-sm">₱{{ product.sales }}</div>
                  </div>
                </div>
              </div>
              
              <!-- Empty State Fallback -->
              <div v-if="donutData.length === 0" class="flex flex-col items-center justify-center w-full py-10 text-slate-400 border-2 border-dashed border-slate-100 rounded-2xl">
                 <svg class="w-10 h-10 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                 <span class="font-medium">No products sold yet.</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Bottom Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-6">
        
        <!-- Workforce -->
        <Card class="border-0 ring-1 ring-slate-100 shadow-md bg-white col-span-1">
          <CardContent class="p-6 sm:p-8">
            <div class="mb-8">
              <h2 class="text-xl font-bold text-slate-800 tracking-tight">Workforce Status</h2>
              <p class="text-slate-500 text-sm mt-1">Latest attendance distribution</p>
            </div>
            
            <div class="space-y-6 pt-2">
              <div v-for="stat in dashboardData.hr.attendanceData" :key="'graph-'+stat.name" class="space-y-3 group">
                <div class="flex items-center justify-between text-sm">
                  <span class="font-semibold text-slate-600">{{ stat.name }}</span>
                  <span class="font-extrabold text-slate-800">{{ stat.value }} <span class="text-slate-400 font-medium">({{ getAttendancePercentage(stat.value) }}%)</span></span>
                </div>
                <div class="h-4 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                  <div class="h-full rounded-full transition-all duration-1000 ease-out group-hover:brightness-110" 
                       :style="{ width: `${getAttendancePercentage(stat.value)}%`, backgroundColor: stat.color }">
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Transactions -->
        <Card class="border-0 ring-1 ring-slate-100 shadow-md bg-white overflow-hidden col-span-1 lg:col-span-2">
          <div class="px-6 py-5 border-b border-slate-100 bg-white flex justify-between items-center">
            <div>
              <h2 class="text-xl font-bold text-slate-800 tracking-tight">Recent Transactions</h2>
              <p class="text-slate-500 text-sm mt-1">Latest E-Commerce and SP Orders</p>
            </div>
          </div>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-slate-50/80">
                <TableRow class="hover:bg-transparent">
                  <TableHead class="font-semibold text-slate-600">Order ID</TableHead>
                  <TableHead class="font-semibold text-slate-600">Type</TableHead>
                  <TableHead class="font-semibold text-slate-600">Date</TableHead>
                  <TableHead class="font-semibold text-slate-600">Status</TableHead>
                  <TableHead class="text-right font-semibold text-slate-600">Amount</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="order in dashboardData.ecommerce.recentOrders" :key="'order-'+order.order_id" class="hover:bg-slate-50/50 transition-colors">
                  <TableCell class="font-bold text-slate-800">ORD-{{ order.order_id }}</TableCell>
                  <TableCell class="text-slate-600 font-medium">{{ order.type }}</TableCell>
                  <TableCell class="text-slate-500">{{ formatDate(order.created_at) }}</TableCell>
                  <TableCell>
                    <span class="px-3 py-1 rounded-full text-xs font-bold capitalize tracking-wide ring-1 ring-inset" :class="getStatusColor(order.status)">
                      {{ order.status.replace('_', ' ') }}
                    </span>
                  </TableCell>
                  <TableCell class="text-right text-slate-800 font-extrabold">₱{{ formatCurrency(order.amount) }}</TableCell>
                </TableRow>
                <TableRow v-if="dashboardData.ecommerce.recentOrders.length === 0">
                  <TableCell colspan="5" class="text-center py-12 text-slate-400 font-medium">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                      No recent transactions found.
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </Card>
      </div>
    </div>

    <!-- Modals -->
    <Transition name="fade">
      <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-sm w-full mx-4 transform transition-all">
          <h3 class="text-xl font-bold text-slate-800 mb-2">Refresh Dashboard</h3>
          <p class="text-slate-500 mb-8">Fetch the latest data from the server?</p>
          <div class="flex justify-end gap-3">
            <Button @click="showConfirmDialog = false" variant="outline" class="border-slate-200 text-slate-600 hover:bg-slate-50 rounded-xl">
              Cancel
            </Button>
            <Button @click="executeRefresh" class="bg-indigo-600 text-white hover:bg-indigo-700 rounded-xl shadow-lg shadow-indigo-500/30">
              Refresh Now
            </Button>
          </div>
        </div>
      </div>
    </Transition>

    <Dialog :open="showDepositDialog" @update:open="showDepositDialog = $event">
      <DialogContent class="sm:max-w-[425px] rounded-2xl">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold">Deposit Funds</DialogTitle>
          <DialogDescription class="text-slate-500">
            Add money directly to your overall sales revenue.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-5 py-4">
          <div class="grid gap-2 relative">
            <Label for="amount" class="font-semibold text-slate-700">Amount to Deposit</Label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold">₱</span>
              <Input 
                id="amount" 
                type="number" 
                v-model="depositAmount" 
                placeholder="0.00" 
                min="1"
                @keydown="preventMathSymbols"
                class="pl-8 text-lg font-bold rounded-xl border-slate-200 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
          </div>
          <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-sm text-indigo-800 flex flex-col gap-1.5 shadow-inner">
            <p class="font-bold flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              Payment Routing
            </p>
            <p class="opacity-90">• Amounts <strong>≤ ₱10,000</strong> are routed via GCash.</p>
            <p class="opacity-90">• Amounts <strong>> ₱10,000</strong> are routed via Bank Transfer.</p>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDepositDialog = false" :disabled="isDepositing" class="rounded-xl border-slate-200">Cancel</Button>
          <Button @click="executeDeposit" :disabled="!depositAmount || depositAmount <= 0 || isDepositing" class="bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-lg shadow-emerald-500/30">
            <span v-if="isDepositing" class="mr-2 animate-spin rounded-full h-4 w-4 border-t-2 border-r-2 border-white"></span>
            {{ isDepositing ? 'Processing...' : 'Proceed to Payment' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
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

const router = useRouter()
const route = useRoute()

const isLoading = ref(true)
const showConfirmDialog = ref(false)
const showDepositDialog = ref(false)
const depositAmount = ref('')
const isDepositing = ref(false)

// Added state for period filter
const selectedPeriod = ref('12m')
const hoveredPoint = ref(null)
const activeSlice = ref(null)

const pieColors = ['#6366f1', '#10b981', '#f59e0b', '#06b6d4', '#ec4899', '#8b5cf6']

const dashboardData = ref({
  finance: {
    totalSales: 0,
    salesChangePercent: 0,
    netCashFlow: 0,
    totalExpenses: 0,
    monthlyRevenue: []
  },
  hr: {
    totalEmployees: 0,
    activeEmployees: 0,
    attendanceData: []
  },
  ecommerce: {
    totalOrders: 0,
    pendingOrders: 0,
    bestSellingProducts: [],
    recentOrders: []
  }
})

// === Prevent Math Symbols on Input ===
const preventMathSymbols = (event) => {
  if (['e', 'E', '+', '-'].includes(event.key)) {
    event.preventDefault()
  }
}

// === Graph & Math Computations ===
const parseCurrency = (val) => {
  if (!val && val !== 0) return 0
  const cleanVal = val.toString().replace(/[^0-9.-]+/g, "")
  return parseFloat(cleanVal) || 0
}

const formatShortCurrency = (val) => {
  if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M'
  if (val >= 1000) return (val / 1000).toFixed(1) + 'K'
  return val.toString()
}

// 1. Area Chart (Revenue Trend) Math
const maxMonthlyRevenue = computed(() => {
  if (!dashboardData.value.finance.monthlyRevenue.length) return 1
  const max = Math.max(...dashboardData.value.finance.monthlyRevenue.map(m => parseCurrency(m.revenue)))
  return max > 0 ? max : 1
})

const chartPoints = computed(() => {
  const data = dashboardData.value.finance.monthlyRevenue;
  if (!data.length) return [];
  const svgWidth = 800;
  const svgHeight = 250;
  const padding = 20;
  const max = maxMonthlyRevenue.value;
  const step = data.length > 1 ? svgWidth / (data.length - 1) : svgWidth / 2;
  
  return data.map((d, i) => {
    const val = parseCurrency(d.revenue);
    const y = svgHeight - ((val / max) * (svgHeight - padding)); 
    const x = data.length > 1 ? (i * step) : step;
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

// 2. Donut Chart (Products) Math
const totalProductSales = computed(() => {
  if (!dashboardData.value.ecommerce.bestSellingProducts.length) return 0;
  return dashboardData.value.ecommerce.bestSellingProducts.reduce((acc, curr) => acc + parseCurrency(curr.sales), 0);
})

const donutData = computed(() => {
  const products = dashboardData.value.ecommerce.bestSellingProducts;
  if (!products || !products.length) return [];
  
  const total = totalProductSales.value;
  if (total === 0) return [];

  let currentOffset = 0;
  return products.map((product, index) => {
    const value = parseCurrency(product.sales);
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

// 3. HR Attendance
const totalAttendance = computed(() => {
  const total = dashboardData.value.hr.attendanceData.reduce((acc, curr) => acc + curr.value, 0)
  return total > 0 ? total : 1 
})

const getAttendancePercentage = (val) => {
  return Math.round((val / totalAttendance.value) * 100)
}

// === Utility Functions ===
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
}

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-PH').format(num || 0)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getStatusColor = (status) => {
  const map = {
    'completed': 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    'delivered': 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
    'pending': 'bg-amber-50 text-amber-700 ring-amber-600/20',
    'confirmed': 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    'shipped': 'bg-purple-50 text-purple-700 ring-purple-600/20',
    'cancelled': 'bg-rose-50 text-rose-700 ring-rose-600/20'
  }
  return map[status?.toLowerCase()] || 'bg-slate-50 text-slate-700 ring-slate-600/20'
}

// === Data Fetching ===
const fetchDashboardData = async () => {
  isLoading.value = true
  try {
    // Append period to query parameters
    const response = await api.get(`/distributor/combined-dashboard?period=${selectedPeriod.value}`)
    if (response.data && response.data.success) {
      dashboardData.value = response.data
    }
  } catch (error) {
    console.error("Error fetching combined dashboard data:", error)
  } finally {
    isLoading.value = false
  }
}

// === Dialog Actions ===
const confirmRefresh = () => {
  showConfirmDialog.value = true
}

const executeRefresh = () => {
  showConfirmDialog.value = false
  fetchDashboardData()
}

// === Deposit Logic ===
const executeDeposit = async () => {
  if (!depositAmount.value || isNaN(depositAmount.value) || Number(depositAmount.value) <= 0) {
    toast.error('Please enter a valid deposit amount.')
    return
  }

  isDepositing.value = true
  try {
    const currentPath = window.location.origin + route.path
    const response = await api.post('/distributor/dashboard/deposit', {
      amount: depositAmount.value,
      return_url: currentPath
    })

    if (response.data && response.data.checkout_url) {
      toast.success('Redirecting to payment gateway...')
      setTimeout(() => {
        window.location.href = response.data.checkout_url
      }, 1500)
    } else {
      toast.error('Failed to generate checkout session.')
      isDepositing.value = false
    }
  } catch (error) {
    console.error("Deposit error:", error)
    
    toast.error('Deposit Unavailable', {
      description: error.response?.data?.message || 'An error occurred while initiating the deposit. Please try again later.',
      duration: 6000 
    })
    
    isDepositing.value = false
    showDepositDialog.value = false 
  }
}

const verifyDepositPayment = async (referenceCode) => {
  isLoading.value = true
  toast.info('Verifying Deposit... Please wait.')

  try {
    const response = await api.post('/distributor/dashboard/deposit/verify', { reference_number: referenceCode })
    if (response.data.success) {
      toast.success('Deposit Confirmed!', { description: 'Funds have been added to your overall revenue.' })
    }
  } catch (error) {
    toast.error('Deposit Verification Failed', { 
      description: error.response?.data?.message || 'Could not verify the transaction.' 
    })
  } finally {
    router.replace({ query: {} }) 
    fetchDashboardData()
  }
}

onMounted(() => {
  if (route.query.deposit_ref) {
    verifyDepositPayment(route.query.deposit_ref)
  } else {
    fetchDashboardData()
  }
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>