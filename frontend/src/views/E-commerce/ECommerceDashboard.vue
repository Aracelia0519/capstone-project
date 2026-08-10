<template>
  <div class="min-h-screen  p-4 md:p-8 font-sans text-slate-50 selection:bg-indigo-500/30">
    
    <!-- Header -->
    <div class="mb-8 md:mb-10 animate-in fade-in slide-in-from-bottom-4 duration-700">
      <h1 class="text-3xl md:text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-slate-400 tracking-tight mb-2">
        E-Commerce Dashboard
      </h1>
      <p class="text-slate-400 font-medium">Real-time overview of sales and performance metrics</p>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-in fade-in slide-in-from-bottom-6 duration-700 delay-100">
      
      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 hover:border-indigo-500/30 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-6 relative z-10">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-400 tracking-wide uppercase">Total Orders</p>
              <h3 class="text-3xl font-bold text-white mt-2">{{ stats.totalOrders }}</h3>
            </div>
            <div class="p-3 rounded-lg bg-indigo-500/10 border border-indigo-500/20 group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-slate-800">
            <p class="text-xs text-slate-500">All-time specific orders</p>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 hover:border-emerald-500/30 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-6 relative z-10">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-400 tracking-wide uppercase">Total Sales</p>
              <h3 class="text-3xl font-bold text-white mt-2">₱{{ stats.totalSales }}</h3>
            </div>
            <div class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-slate-800">
            <p class="text-xs text-slate-500">From all valid orders</p>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 hover:border-amber-500/30 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-6 relative z-10">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-400 tracking-wide uppercase">Pending Orders</p>
              <h3 class="text-3xl font-bold text-white mt-2">{{ stats.pendingOrders }}</h3>
            </div>
            <div class="p-3 rounded-lg bg-amber-500/10 border border-amber-500/20 group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-slate-800">
            <p class="text-xs text-slate-500">Awaiting processing</p>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-slate-900/50 backdrop-blur-xl border border-slate-800 hover:border-cyan-500/30 transition-all duration-300 group overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
        <CardContent class="p-6 relative z-10">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-slate-400 tracking-wide uppercase">Completed Orders</p>
              <h3 class="text-3xl font-bold text-white mt-2">{{ stats.completedOrders }}</h3>
            </div>
            <div class="p-3 rounded-lg bg-cyan-500/10 border border-cyan-500/20 group-hover:scale-110 transition-transform">
              <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-slate-800">
            <p class="text-xs text-slate-500">Successfully delivered</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8 animate-in fade-in slide-in-from-bottom-8 duration-700 delay-200">
      
      <!-- Shadcn-style AREA CHART -->
      <Card class="lg:col-span-2 bg-slate-900/50 border border-slate-800 text-white flex flex-col relative overflow-hidden">
        <CardContent class="p-6 flex-1 flex flex-col relative z-10">
          <div class="flex items-center justify-between mb-8">
            <div>
              <h3 class="text-lg font-bold text-white">Sales Overview</h3>
              <p class="text-sm text-slate-400 mt-1">Revenue over the selected period</p>
            </div>
            <Select v-model="selectedPeriod" @update:model-value="fetchDashboardData">
              <SelectTrigger class="w-[140px] bg-slate-950 border-slate-800 text-slate-300 h-9 rounded-lg shadow-sm">
                <SelectValue placeholder="Period" />
              </SelectTrigger>
              <SelectContent class="bg-slate-950 border-slate-800 text-slate-300 rounded-lg shadow-2xl">
                <SelectItem value="7d" class="focus:bg-slate-800 focus:text-white">Last 7 Days</SelectItem>
                <SelectItem value="30d" class="focus:bg-slate-800 focus:text-white">Last 30 Days</SelectItem>
                <SelectItem value="3m" class="focus:bg-slate-800 focus:text-white">Last 3 Months</SelectItem>
                <SelectItem value="12m" class="focus:bg-slate-800 focus:text-white">Last 12 Months</SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <div class="h-64 relative min-h-[250px] w-full flex-1 group">
            
            <!-- Horizontal Grid Lines -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none border-b border-slate-800">
              <div class="w-full border-t border-slate-800/40 h-0"></div>
              <div class="w-full border-t border-slate-800/40 h-0"></div>
              <div class="w-full border-t border-slate-800/40 h-0"></div>
              <div class="w-full border-t border-slate-800/40 h-0"></div>
            </div>

            <!-- SVG Line & Area -->
            <svg v-if="salesData.length > 0" viewBox="0 0 800 250" preserveAspectRatio="none" class="absolute inset-0 w-full h-full overflow-visible">
              <defs>
                <linearGradient id="colorSales" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stop-color="#6366f1" stop-opacity="0.3"/>
                  <stop offset="95%" stop-color="#6366f1" stop-opacity="0"/>
                </linearGradient>
              </defs>
              <!-- Filled Area -->
              <path :d="areaPath" fill="url(#colorSales)" class="transition-all duration-500 ease-in-out" />
              <!-- Stroke Line -->
              <path :d="linePath" fill="none" stroke="#6366f1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="transition-all duration-500 ease-in-out drop-shadow-md" />
              <!-- Interactive Data Points -->
              <circle v-for="(point, index) in chartPoints" :key="'point-'+index"
                      :cx="point.x" :cy="point.y" r="4" 
                      fill="#slate-950" stroke="#6366f1" stroke-width="2" 
                      class="transition-all duration-300"
                      :class="hoveredPoint === index ? 'r-6 fill-[#6366f1] stroke-white stroke-[3px]' : 'fill-slate-900 opacity-0 group-hover:opacity-100'"
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
                  <div class="font-bold text-sm text-indigo-400">₱{{ point.amount }}</div>
                  <div class="text-slate-400 mt-0.5">{{ point.day }}</div>
                </div>
              </div>
            </div>

            <!-- Empty State Fallback -->
            <div v-if="!salesData.length" class="absolute inset-0 flex items-center justify-center text-slate-500">
              No sales data available.
            </div>
          </div>
          
          <!-- X-Axis Labels (With logic to hide some labels if there are too many, preventing overlap) -->
          <div class="w-full flex justify-between mt-3 text-xs font-medium text-slate-500 relative z-10" v-if="salesData.length > 0">
            <span v-for="(point, index) in chartPoints" :key="'label-'+point.day" 
                  class="flex-1 text-center truncate px-1"
                  :class="{'opacity-0 sm:opacity-100': salesData.length > 15 && index % 2 !== 0}">
              {{ point.day }}
            </span>
          </div>
        </CardContent>
      </Card>

      <!-- Best Selling Products (Shadcn Pie Chart View - MOVED TO TOP ROW) -->
      <Card class="bg-slate-900/50 border border-slate-800 text-white lg:col-span-1 flex flex-col h-full">
        <CardContent class="p-6 flex-1 flex flex-col">
          <div class="mb-6">
            <h3 class="text-lg font-bold text-white mb-2">Top Products</h3>
            <p class="text-sm text-slate-400">Best selling items this period</p>
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
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-widest mb-0.5">Revenue</span>
                <span class="text-xl font-bold text-white">₱{{ formatShortCurrency(totalProductSales) }}</span>
              </div>
            </div>

            <!-- List / Legend -->
            <div class="w-full space-y-2" v-if="donutData.length > 0">
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
                  <div class="text-white font-bold text-sm">₱{{ product.sales }}</div>
                </div>
              </div>
            </div>
            
            <!-- Empty State Fallback -->
            <div v-if="donutData.length === 0" class="flex flex-col items-center justify-center w-full py-8 text-slate-500">
               <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
               <span>No products sold yet.</span>
            </div>

          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Data Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-in fade-in slide-in-from-bottom-10 duration-700 delay-300">
      
      <!-- Recent Transactions -->
      <Card class="bg-slate-900/50 border border-slate-800 text-white h-full">
        <div class="flex items-center justify-between p-6 border-b border-slate-800">
          <div>
            <h3 class="text-lg font-bold text-white">Recent Transactions</h3>
            <p class="text-sm text-slate-400 mt-1">Latest processed orders</p>
          </div>
          <Button variant="outline" class="text-sm bg-slate-950 border-slate-800 text-slate-300 hover:bg-slate-800 hover:text-white rounded-lg">View All</Button>
        </div>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow class="border-b border-slate-800 hover:bg-transparent">
                  <TableHead class="text-slate-400 font-medium h-12 px-6">Order ID</TableHead>
                  <TableHead class="text-slate-400 font-medium h-12 px-6">Client</TableHead>
                  <TableHead class="text-slate-400 font-medium h-12 px-6">Amount</TableHead>
                  <TableHead class="text-slate-400 font-medium h-12 px-6 text-right">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="transaction in recentTransactions" :key="transaction.id" class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                  <TableCell class="text-slate-300 font-mono text-sm py-4 px-6">{{ transaction.orderId }}</TableCell>
                  <TableCell class="py-4 px-6">
                    <div class="flex items-center">
                      <Avatar class="w-8 h-8 mr-3 ring-2 ring-slate-800">
                        <AvatarFallback class="bg-slate-800 text-slate-300 text-xs font-semibold">
                          {{ transaction.client ? transaction.client.charAt(0) : 'U' }}
                        </AvatarFallback>
                      </Avatar>
                      <span class="text-slate-200 font-medium">{{ transaction.client || 'Unknown Client' }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-white font-semibold py-4 px-6">₱{{ transaction.amount }}</TableCell>
                  <TableCell class="py-4 px-6 text-right">
                    <Badge variant="outline" :class="[
                      'rounded-md border-0 px-2.5 py-0.5 text-xs font-semibold',
                      transaction.status === 'Completed' ? 'bg-emerald-500/10 text-emerald-400' :
                      transaction.status === 'Pending' ? 'bg-amber-500/10 text-amber-400' :
                      'bg-indigo-500/10 text-indigo-400'
                    ]">
                      {{ transaction.status }}
                    </Badge>
                  </TableCell>
                </TableRow>
                <TableRow v-if="!recentTransactions.length">
                  <TableCell colspan="4" class="text-center py-10 text-slate-500">No recent transactions found.</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <!-- Shadcn-style Progress Metrics (Order Status) -->
      <Card class="bg-slate-900/50 border border-slate-800 text-white h-full">
        <div class="flex items-center justify-between p-6 border-b border-slate-800">
          <div>
            <h3 class="text-lg font-bold text-white">Order Status</h3>
            <p class="text-sm text-slate-400 mt-1">Distribution of all recorded orders</p>
          </div>
        </div>
        <CardContent class="p-6">
          <div class="grid grid-cols-1 xl:grid-cols-2 gap-x-8 gap-y-8">
            <div v-for="status in orderStatus" :key="status.name" class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                  <div class="w-2.5 h-2.5 rounded-full shadow-[0_0_8px_rgba(0,0,0,0.5)]" :class="status.color"></div>
                  <span class="font-medium text-slate-300">{{ status.name }}</span>
                </div>
                <div class="flex gap-3 text-sm">
                  <span class="text-slate-400">{{ status.count }} items</span>
                  <span class="font-bold text-white">{{ status.percentage }}%</span>
                </div>
              </div>
              <!-- Progress Bar -->
              <div class="h-2 w-full bg-slate-950 rounded-full overflow-hidden border border-slate-800">
                <div class="h-full rounded-full transition-all duration-1000 ease-out" 
                     :class="status.color"
                     :style="{ width: `${status.percentage}%` }">
                </div>
              </div>
            </div>
            
            <div v-if="!orderStatus.length" class="col-span-full flex flex-col items-center justify-center py-8 text-slate-500">
               <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
               <span>No order status data available.</span>
            </div>
          </div>
        </CardContent>
      </Card>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

// Reactive state
const selectedPeriod = ref('12m') // Changed default to '12m'
const hoveredPoint = ref(null) // Tracks which data point the user is hovering over in the Area Chart
const stats = ref({
  totalOrders: 0,
  totalSales: '0.00',
  pendingOrders: 0,
  completedOrders: 0
})

const salesData = ref([])
const orderStatus = ref([])
const recentTransactions = ref([])
const bestSellingProducts = ref([])
const activeSlice = ref(null)

// Shadcn UI style color palette for the pie chart
const pieColors = ['#6366f1', '#10b981', '#f59e0b', '#06b6d4', '#ec4899', '#8b5cf6']

// Utility to safely parse string amounts (e.g. "1,500.00" -> 1500)
const parseAmount = (val) => {
  if (!val) return 0;
  return Number(val.toString().replace(/[^0-9.-]+/g, "")) || 0;
}

// Utility to shorten large currency values (e.g. 15000 -> 15K) for the Donut center
const formatShortCurrency = (val) => {
  if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M'
  if (val >= 1000) return (val / 1000).toFixed(1) + 'K'
  return val.toString()
}

// === Area Chart Computations ===
// 1. Calculate Maximum Y Value
const maxSales = computed(() => {
  if (!salesData.value || salesData.value.length === 0) return 1;
  const max = Math.max(...salesData.value.map(d => parseAmount(d.amount)));
  return max > 0 ? max : 1;
})

// 2. Map data to physical X,Y coordinates for the 800x250 SVG viewport
const chartPoints = computed(() => {
  if (!salesData.value.length) return [];
  const svgWidth = 800;
  const svgHeight = 250;
  const padding = 20; // keeps lines from touching the absolute roof
  const max = maxSales.value;
  // Prevent division by zero if there's only 1 point
  const step = salesData.value.length > 1 ? svgWidth / (salesData.value.length - 1) : svgWidth / 2;
  
  return salesData.value.map((d, i) => {
    const val = parseAmount(d.amount);
    // Y starts at top (0), so we subtract from svgHeight. Add padding so top values aren't clipped.
    const y = svgHeight - ((val / max) * (svgHeight - padding)); 
    // X is spaced evenly across the 800px width
    const x = salesData.value.length > 1 ? (i * step) : step;
    
    return { x, y, amount: d.amount, day: d.day };
  });
})

// 3. Draw the solid stroke line
const linePath = computed(() => {
  const pts = chartPoints.value;
  if (!pts.length) return '';
  // Map points to SVG coordinates: M = move to, L = line to
  return pts.map((p, i) => (i === 0 ? `M ${p.x},${p.y}` : `L ${p.x},${p.y}`)).join(' ');
})

// 4. Draw the filled gradient area (draws the line, drops down to bottom corners, and closes shape)
const areaPath = computed(() => {
  const pts = chartPoints.value;
  if (!pts.length) return '';
  const line = linePath.value;
  // Z closes the path
  return `${line} L 800,250 L 0,250 Z`;
})

// === Pie Chart Computations ===
// 1. Get total sales count for the center of the pie
const totalProductSales = computed(() => {
  if (!bestSellingProducts.value) return 0;
  return bestSellingProducts.value.reduce((acc, curr) => acc + parseAmount(curr.sales), 0);
})

// 2. Build dynamic SVG Donut logic mimicking Recharts/Shadcn mathematical circle tracking
const donutData = computed(() => {
  if (!bestSellingProducts.value || !bestSellingProducts.value.length) return [];
  
  const total = totalProductSales.value;
  if (total === 0) return [];

  let currentOffset = 0;
  
  return bestSellingProducts.value.map((product, index) => {
    const value = parseAmount(product.sales);
    const percentage = (value / total) * 100;
    
    // Circumference of a circle with r=15.9155 is exactly 100.
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

// === Data Fetching ===
// Fetch the Dashboard Data (Updates when dropdown changes)
const fetchDashboardData = async () => {
  try {
    const response = await api.get(`/operation-distributor/ecommerce-dashboard?period=${selectedPeriod.value}`)
    
    // Bind backend data directly to refs
    stats.value = response.data.stats || stats.value
    salesData.value = response.data.salesData || []
    orderStatus.value = response.data.orderStatus || []
    recentTransactions.value = response.data.recentTransactions || []
    bestSellingProducts.value = response.data.bestSellingProducts || []

  } catch (error) {
    console.error('Failed to load dashboard data:', error)
  }
}

// Load data on mount
onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
/* Scoped styles are intentionally minimal. Tailwind handles the rest. */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>