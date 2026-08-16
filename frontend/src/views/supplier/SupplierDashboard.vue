<script setup>
import { ref, shallowRef, onMounted, onBeforeUnmount } from 'vue';
import api from '@/utils/axios.js';
import { 
  DollarSign, 
  ShoppingCart, 
  Package, 
  Users, 
  Download,
  AlertCircle,
  MoreHorizontal,
  Calendar
} from 'lucide-vue-next';

// Chart JS Imports
import { Line, Bar, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const isComponentMounted = ref(false);
const isLoading = ref(true);

const stats = ref([
  { title: "Total Revenue", value: "₱0.00", change: "Overall accumulated", icon: DollarSign },
  { title: "Total Orders", value: "0", change: "Overall history", icon: ShoppingCart },
  { title: "Active Products", value: "0", change: "Currently listed", icon: Package },
  { title: "Active Partners", value: "0", change: "Distributor connections", icon: Users }
]);

const recentOrders = ref([]);
const pendingFulfillments = ref([]);

// Chart Variables (Using shallowRef to prevent deep-proxying by Vue)
const revenueChartData = shallowRef({ labels: [], datasets: [] });
const categoryChartData = shallowRef({ labels: [], datasets: [] });
const topProductsChartData = shallowRef({ labels: [], datasets: [] });
const orderStatusChartData = shallowRef({ labels: [], datasets: [] });

// Premium Color Palettes & Styles (Matches Dashboard.vue)
const palettes = {
  primary: '#4f46e5', // Indigo 600
  secondary: '#0ea5e9', // Sky 500
  accent1: '#f43f5e', // Rose 500
  accent2: '#f59e0b', // Amber 500
  accent3: '#10b981', // Emerald 500
  accent4: '#8b5cf6', // Violet 500
  grid: '#f1f5f9', // Slate 100
  text: '#64748b' // Slate 500
};

const premiumTooltip = {
  backgroundColor: 'rgba(255, 255, 255, 0.9)',
  titleColor: '#0f172a',
  bodyColor: '#475569',
  borderColor: '#e2e8f0',
  borderWidth: 1,
  padding: 12,
  boxPadding: 6,
  usePointStyle: true,
  titleFont: { size: 14, family: 'Inter, sans-serif', weight: 'bold' },
  bodyFont: { size: 13, family: 'Inter, sans-serif' },
  caretSize: 6,
  cornerRadius: 8,
  boxWidth: 10,
  boxHeight: 10
};

const createGradient = (ctx, chartArea, colorStart, colorEnd) => {
  if (!chartArea) return colorStart;
  const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
  gradient.addColorStop(0, colorStart);
  gradient.addColorStop(1, colorEnd);
  return gradient;
};

// Chart Configurations
const lineOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false }, tooltip: premiumTooltip },
  scales: {
    y: { border: { display: false }, grid: { color: palettes.grid, drawTicks: false }, ticks: { color: palettes.text, padding: 10 } },
    x: { grid: { display: false }, ticks: { color: palettes.text, padding: 10 } }
  },
  interaction: { mode: 'index', intersect: false },
  elements: {
    line: { tension: 0.4, borderWidth: 3 },
    point: { radius: 0, hitRadius: 20, hoverRadius: 6, backgroundColor: palettes.primary, borderWidth: 2, borderColor: '#fff' }
  }
};

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false }, tooltip: premiumTooltip },
  scales: {
    y: { border: { display: false }, grid: { color: palettes.grid, drawTicks: false }, ticks: { color: palettes.text, padding: 10 } },
    x: { grid: { display: false }, ticks: { color: palettes.text, padding: 10 } }
  },
  borderRadius: 6,
  barThickness: 32
};

const barOptionsHorizontal = {
  ...barOptions,
  indexAxis: 'y',
  scales: {
    x: { border: { display: false }, grid: { color: palettes.grid, drawTicks: false }, ticks: { color: palettes.text, padding: 10 } },
    y: { grid: { display: false }, ticks: { color: palettes.text, padding: 10 } }
  },
  barThickness: 24
};

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '80%',
  layout: { padding: 10 },
  plugins: {
    legend: { position: 'right', labels: { usePointStyle: true, color: palettes.text, padding: 25, font: { size: 13, family: 'Inter' } } },
    tooltip: premiumTooltip
  }
};

// Fetch Dashboard Data
const fetchDashboardData = async () => {
    if (!isComponentMounted.value) return; 
    isLoading.value = true;
    
    try {
        const response = await api.get('/supplier/dashboard');
        if (!isComponentMounted.value) return; 
        
        const { data } = response;

        // Populate KPIs
        stats.value[0].value = `₱${data.stats.revenue}`;
        stats.value[1].value = data.stats.orders.toString();
        stats.value[2].value = data.stats.products.toString();
        stats.value[3].value = data.stats.partners.toString();

        // Populate Lists
        recentOrders.value = data.recentOrders;
        pendingFulfillments.value = data.pendingFulfillments;

        // Populate Charts
        revenueChartData.value = {
            labels: data.charts.revenue_trajectory.labels,
            datasets: [{
                label: 'Revenue (₱)',
                data: data.charts.revenue_trajectory.data,
                borderColor: palettes.primary,
                backgroundColor: (context) => {
                    const chart = context.chart;
                    const {ctx, chartArea} = chart;
                    return createGradient(ctx, chartArea, 'rgba(79, 70, 229, 0.4)', 'rgba(79, 70, 229, 0.0)');
                },
                fill: true,
            }]
        };

        categoryChartData.value = {
            labels: data.charts.category_distribution.labels,
            datasets: [{
                data: data.charts.category_distribution.data,
                backgroundColor: [palettes.primary, palettes.secondary, palettes.accent3, palettes.accent4, palettes.accent2, palettes.accent1],
                borderWidth: 0,
                hoverOffset: 4
            }]
        };

        topProductsChartData.value = {
            labels: data.charts.top_products.labels,
            datasets: [{
                label: 'Units Sold',
                data: data.charts.top_products.data,
                backgroundColor: palettes.accent3,
                hoverBackgroundColor: '#059669' // Emerald 600
            }]
        };

        orderStatusChartData.value = {
            labels: data.charts.order_status.labels,
            datasets: [{
                label: 'Orders',
                data: data.charts.order_status.data,
                backgroundColor: palettes.secondary,
                hoverBackgroundColor: '#0284c7' // Sky 600
            }]
        };

    } catch (error) {
        console.error("Failed to load supplier dashboard:", error);
    } finally {
        if (isComponentMounted.value) isLoading.value = false;
    }
};

const getStatusClass = (status) => {
    switch(status?.toLowerCase()) {
        case 'delivered':
        case 'completed':
            return 'border-transparent bg-green-100 text-green-700';
        case 'shipped':
        case 'in_transit':
            return 'border-transparent bg-blue-100 text-blue-700';
        case 'pending':
        case 'processing':
            return 'border-transparent bg-yellow-100 text-yellow-700';
        default:
            return 'border-transparent bg-slate-100 text-slate-700';
    }
};

const formatStatus = (status) => {
    if (!status) return 'Unknown';
    return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
};

onMounted(() => {
    isComponentMounted.value = true;
    fetchDashboardData();
});

onBeforeUnmount(() => {
    isComponentMounted.value = false;
    revenueChartData.value = { labels: [], datasets: [] };
    categoryChartData.value = { labels: [], datasets: [] };
    topProductsChartData.value = { labels: [], datasets: [] };
    orderStatusChartData.value = { labels: [], datasets: [] };
});
</script>

<template>
  <div class="min-h-screen w-full font-sans p-6 md:p-8 overflow-x-hidden bg-slate-50/50">
    <div class="max-w-[1600px] mx-auto space-y-8">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-4 border-b border-slate-200/60">
        <div class="space-y-1">
          <div class="inline-flex items-center rounded-full border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 mb-3">
            <span class="flex h-2 w-2 rounded-full bg-indigo-500 mr-2"></span>
            Supplier Live
          </div>
          <h2 class="text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900">
            Supplier Dashboard
          </h2>
          <p class="text-sm font-medium text-slate-500">Real-time overview of sales, orders, and product reports.</p>
        </div>
        
        <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0">
          <button class="inline-flex items-center justify-start whitespace-nowrap rounded-md text-sm font-medium border border-slate-200 bg-white shadow-sm hover:bg-slate-100 h-11 px-4 py-2 text-left">
            <Calendar class="mr-2 h-4 w-4 text-slate-500" />
            <span class="text-slate-600">Sync Live Data</span>
          </button>
          
          <button @click="fetchDashboardData" class="inline-flex items-center justify-center rounded-xl text-sm font-medium bg-slate-900 text-white shadow-md hover:bg-slate-800 h-11 px-6 py-2">
             <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
             <Download v-else class="mr-2 h-4 w-4" />
             Refresh
          </button>
        </div>
      </div>

      <!-- KPIs (Bento Grid) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="(stat, i) in stats" :key="i" class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
          <div class="flex flex-row items-center justify-between pb-4 relative z-10">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-50 text-indigo-600 shadow-inner">
                 <component :is="stat.icon" class="h-5 w-5" />
              </div>
              <h3 class="font-semibold text-slate-600">{{ stat.title }}</h3>
            </div>
          </div>
          <div class="flex items-baseline gap-2 relative z-10">
            <div class="text-4xl font-black tracking-tight text-slate-900">{{ stat.value }}</div>
          </div>
          <p class="text-sm font-medium text-slate-400 mt-2 relative z-10">{{ stat.change }}</p>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
        
        <!-- Main Line Chart: Revenue Trajectory -->
        <div class="lg:col-span-4 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Revenue Trajectory</h3>
            <p class="text-sm font-medium text-slate-500">Gross sales across all distributors over 12 months.</p>
          </div>
          <div class="p-6 h-[380px] w-full flex-grow relative">
            <div v-if="isLoading" class="absolute inset-0 p-6 flex items-end justify-between gap-2 animate-pulse">
              <div v-for="i in 6" :key="i" class="w-full bg-slate-100 rounded-t-md" :style="{ height: `${Math.random() * 80 + 20}%` }"></div>
            </div>
            <Line v-else-if="revenueChartData.datasets?.length > 0" :data="revenueChartData" :options="lineOptions" />
          </div>
        </div>

        <!-- Doughnut Chart: Category Distribution -->
        <div class="lg:col-span-3 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Product Categories</h3>
            <p class="text-sm font-medium text-slate-500">Live composition of active listed products.</p>
          </div>
          <div class="p-6 h-[380px] flex items-center justify-center relative">
             <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center animate-pulse">
                <div class="h-48 w-48 rounded-full border-[16px] border-slate-100"></div>
             </div>
            <Doughnut v-else-if="categoryChartData.datasets?.length > 0" :data="categoryChartData" :options="doughnutOptions" />
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Bar Chart: Top Performing Products -->
        <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Top Performing Products</h3>
            <p class="text-sm font-medium text-slate-500">Highest volume of raw materials ordered.</p>
          </div>
          <div class="p-6 h-[350px] relative">
            <div v-if="isLoading" class="absolute inset-0 p-6 flex items-end justify-between gap-4 animate-pulse">
              <div v-for="i in 5" :key="i" class="w-full bg-slate-100 rounded-t-md" :style="{ height: `${Math.random() * 60 + 20}%` }"></div>
            </div>
            <Bar v-else-if="topProductsChartData.datasets?.length > 0" :data="topProductsChartData" :options="barOptions" />
          </div>
        </div>

        <!-- Horizontal Bar Chart: Order Status Heatmap -->
        <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Order Status Heatmap</h3>
            <p class="text-sm font-medium text-slate-500">Volume of procurement requests mapped by lifecycle phase.</p>
          </div>
          <div class="p-6 h-[350px] relative">
            <div v-if="isLoading" class="absolute inset-0 p-6 flex flex-col items-start justify-between gap-4 animate-pulse">
              <div v-for="i in 5" :key="i" class="h-full bg-slate-100 rounded-r-md" :style="{ width: `${Math.random() * 60 + 20}%` }"></div>
            </div>
            <Bar v-else-if="orderStatusChartData.datasets?.length > 0" :data="orderStatusChartData" :options="barOptionsHorizontal" />
          </div>
        </div>
      </div>

      <!-- Lists Row 3 -->
      <div class="grid gap-6 grid-cols-1 lg:grid-cols-7 pb-10">
        
        <!-- Recent Orders Table -->
        <div class="col-span-1 lg:col-span-4 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden">
          <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="grid gap-1">
              <h3 class="font-bold text-lg text-slate-900">Recent Distributor Orders</h3>
              <p class="text-sm font-medium text-slate-500">Your latest procurement requests from partners.</p>
            </div>
          </div>
          
          <div class="p-0">
            <div class="relative w-full overflow-x-auto">
              <table class="w-full text-sm text-left">
                <thead class="bg-slate-50/50 border-b border-slate-100">
                  <tr>
                    <th class="h-12 px-6 font-semibold text-slate-600">Code</th>
                    <th class="h-12 px-6 font-semibold text-slate-600">Distributor</th>
                    <th class="h-12 px-6 font-semibold text-slate-600 hidden sm:table-cell">Status</th>
                    <th class="h-12 px-6 font-semibold text-slate-600 text-right">Amount</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  <tr v-if="isLoading">
                     <td colspan="4" class="p-6 text-center text-slate-400">Loading data...</td>
                  </tr>
                  <tr v-else-if="recentOrders.length === 0">
                    <td colspan="4" class="p-8 text-center text-slate-500">No recent orders found.</td>
                  </tr>
                  <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-slate-50/80 transition-colors">
                    <td class="p-6 font-semibold text-slate-900">{{ order.id }}</td>
                    <td class="p-6">
                      <div class="font-medium text-slate-900 whitespace-nowrap">{{ order.customer }}</div>
                      <div class="text-xs text-slate-500 hidden md:block mt-0.5">{{ order.product }}</div>
                    </td>
                    <td class="p-6 hidden sm:table-cell">
                      <div :class="['inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold', getStatusClass(order.status)]">
                        {{ formatStatus(order.status) }}
                      </div>
                    </td>
                    <td class="p-6 text-right font-bold text-slate-900">₱{{ parseFloat(order.amount).toFixed(2) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Pending Fulfillments -->
        <div class="col-span-1 lg:col-span-3 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="p-6 border-b border-slate-100 pb-4">
             <h3 class="font-bold text-lg text-slate-900 flex items-center gap-2">
               <AlertCircle class="h-5 w-5 text-amber-500" />
               Pending Fulfillments
             </h3>
          </div>
          <div class="p-6 grid gap-4">
            <p v-if="isLoading" class="text-sm text-slate-400">Loading...</p>
            <p v-else-if="pendingFulfillments.length === 0" class="text-sm text-slate-500">No pending items.</p>
            
            <div v-for="(item, i) in pendingFulfillments" :key="i" class="flex items-center justify-between border-b border-slate-100 pb-4 last:border-0 last:pb-0">
               <div class="space-y-1 min-w-0">
                  <p class="text-sm font-semibold text-slate-900 leading-none truncate pr-2">{{ item.name }}</p>
                  <p class="text-xs font-medium text-slate-500">{{ item.code }}</p>
               </div>
               <div class="flex items-center gap-3 shrink-0">
                  <span class="text-sm font-bold text-orange-600 bg-orange-50 px-2 py-1 rounded-md">
                    Qty: {{ item.quantity }}
                  </span>
                  <button class="h-8 w-8 inline-flex items-center justify-center rounded-md border border-slate-200 bg-white hover:bg-slate-50 text-slate-500 transition-colors">
                    <MoreHorizontal class="h-4 w-4" />
                  </button>
               </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>