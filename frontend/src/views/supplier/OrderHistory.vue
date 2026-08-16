<script setup>
import { ref, shallowRef, onMounted, onBeforeUnmount } from 'vue';
import api from '@/utils/axios.js';
import { 
  PackageCheck, 
  DollarSign, 
  Layers, 
  Download, 
  ExternalLink,
  FileText,
  Image as ImageIcon
} from 'lucide-vue-next';

// ChartJS Imports
import { Line, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
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
  ArcElement,
  Title,
  Tooltip,
  Legend,
  Filler
);

const isComponentMounted = ref(false);
const isLoading = ref(true);

const kpis = ref({
  total_delivered: 0,
  total_revenue: '0.00',
  total_items: 0
});

// Table & Pagination State
const orders = ref([]);
const currentPage = ref(1);
const lastPage = ref(1);
const totalOrders = ref(0);

// Chart Refs
const revenueChartData = shallowRef({ labels: [], datasets: [] });
const categoryChartData = shallowRef({ labels: [], datasets: [] });

// Modal Viewer State for proofs & receipts
const viewerModal = ref({
  isOpen: false,
  url: '',
  title: ''
});

const palettes = {
  primary: '#4f46e5',
  secondary: '#0ea5e9',
  accent3: '#10b981',
  accent4: '#8b5cf6',
  grid: '#f1f5f9',
  text: '#64748b'
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
  cornerRadius: 8
};

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

const fetchOrderHistory = async (page = 1) => {
    if (!isComponentMounted.value) return;
    isLoading.value = true;
    try {
        const response = await api.get(`/supplier/order-history?page=${page}`);
        if (!isComponentMounted.value) return;

        const data = response.data;
        kpis.value = data.kpis;
        
        // Handle Laravel's paginated object
        orders.value = data.orders.data;
        currentPage.value = data.orders.current_page;
        lastPage.value = data.orders.last_page;
        totalOrders.value = data.orders.total;

        revenueChartData.value = {
            labels: data.charts.monthly_revenue.labels,
            datasets: [{
                label: 'Delivered Revenue (₱)',
                data: data.charts.monthly_revenue.data,
                borderColor: palettes.primary,
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
            }]
        };

        categoryChartData.value = {
            labels: data.charts.categories.labels,
            datasets: [{
                data: data.charts.categories.data,
                backgroundColor: [palettes.primary, palettes.secondary, palettes.accent3, palettes.accent4],
                borderWidth: 0,
                hoverOffset: 4
            }]
        };

    } catch (error) {
        console.error("Failed to fetch order history:", error);
    } finally {
        if (isComponentMounted.value) isLoading.value = false;
    }
};

// Intelligently maps the DB path using the configured axios baseURL
const getImageUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  
  // Extract the base URL from the axios instance configuration
  let baseUrl = api.defaults.baseURL || import.meta.env.VITE_API_URL || '';
  
  // Remove '/api' from the end of the URL if it exists, to point directly to the public root
  baseUrl = baseUrl.replace(/\/api\/?$/i, '');
  // Clean any lingering trailing slashes
  baseUrl = baseUrl.replace(/\/+$/, '');
  
  const cleanPath = path.replace(/^\/+/, '');
  
  if (cleanPath.startsWith('storage/')) {
     return `${baseUrl}/${cleanPath}`;
  }
  return `${baseUrl}/storage/${cleanPath}`;
};

const openAttachment = (path, title) => {
    if (!path) return;
    viewerModal.value = {
        isOpen: true,
        url: getImageUrl(path),
        title: title
    };
};

const closeModal = () => {
    viewerModal.value.isOpen = false;
    viewerModal.value.url = '';
};

onMounted(() => {
    isComponentMounted.value = true;
    fetchOrderHistory();
});

onBeforeUnmount(() => {
    isComponentMounted.value = false;
});
</script>

<template>
  <div class="min-h-screen w-full font-sans p-6 md:p-8 overflow-x-hidden bg-slate-50/50">
    <div class="max-w-[1600px] mx-auto space-y-8">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-4 border-b border-slate-200/60">
        <div class="space-y-1">
          <div class="inline-flex items-center rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-600 mb-3">
            <span class="flex h-2 w-2 rounded-full bg-emerald-500 mr-2"></span>
            Delivered Records Live
          </div>
          <h2 class="text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900">
            Procurement Order History
          </h2>
          <p class="text-sm font-medium text-slate-500">Comprehensive ledger of delivered orders, receipts, and proofs of delivery from partnered distributors.</p>
        </div>
        
        <button @click="fetchOrderHistory(1)" class="inline-flex items-center justify-center rounded-xl text-sm font-medium bg-slate-900 text-white shadow-md hover:bg-slate-800 h-11 px-6 py-2">
          <Download class="mr-2 h-4 w-4" />
          Sync Records
        </button>
      </div>

      <!-- KPIs Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
          <div class="flex items-center gap-3 pb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 shadow-inner">
              <PackageCheck class="h-5 w-5" />
            </div>
            <h3 class="font-semibold text-slate-600">Total Delivered Orders</h3>
          </div>
          <div class="text-4xl font-black tracking-tight text-slate-900">{{ kpis.total_delivered }}</div>
          <p class="text-sm font-medium text-slate-400 mt-2">Successfully fulfilled requests</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
          <div class="flex items-center gap-3 pb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 shadow-inner">
              <DollarSign class="h-5 w-5" />
            </div>
            <h3 class="font-semibold text-slate-600">Fulfilled Revenue</h3>
          </div>
          <div class="text-4xl font-black tracking-tight text-slate-900">₱{{ kpis.total_revenue }}</div>
          <p class="text-sm font-medium text-slate-400 mt-2">Accumulated delivered billing</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
          <div class="flex items-center gap-3 pb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-50 text-purple-600 shadow-inner">
              <Layers class="h-5 w-5" />
            </div>
            <h3 class="font-semibold text-slate-600">Total Units Distributed</h3>
          </div>
          <div class="text-4xl font-black tracking-tight text-slate-900">{{ kpis.total_items }}</div>
          <p class="text-sm font-medium text-slate-400 mt-2">Volume of raw materials delivered</p>
        </div>
      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
        <div class="lg:col-span-4 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Delivered Revenue Trajectory</h3>
            <p class="text-sm font-medium text-slate-500">Monthly breakdown of finalized deliveries over 12 months.</p>
          </div>
          <div class="p-6 h-[350px] w-full relative">
            <Line v-if="revenueChartData.datasets?.length > 0" :data="revenueChartData" :options="lineOptions" />
          </div>
        </div>

        <div class="lg:col-span-3 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Delivered Categories</h3>
            <p class="text-sm font-medium text-slate-500">Proportion of product categories shipped.</p>
          </div>
          <div class="p-6 h-[350px] flex items-center justify-center relative">
            <Doughnut v-if="categoryChartData.datasets?.length > 0" :data="categoryChartData" :options="doughnutOptions" />
          </div>
        </div>
      </div>

      <!-- Delivered Orders Table with Proofs & Receipts -->
      <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden mb-10 flex flex-col">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
          <div>
            <h3 class="font-bold text-lg text-slate-900">Delivered Orders History</h3>
            <p class="text-sm font-medium text-slate-500">Review complete fulfillment documentation, delivery proofs, and receipts.</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50/50 border-b border-slate-100">
              <tr>
                <th class="h-12 px-6 font-semibold text-slate-600">Request Code</th>
                <th class="h-12 px-6 font-semibold text-slate-600">Distributor</th>
                <th class="h-12 px-6 font-semibold text-slate-600">Product Name</th>
                <th class="h-12 px-6 font-semibold text-slate-600">Qty / Total</th>
                <th class="h-12 px-6 font-semibold text-slate-600">Delivered Date</th>
                <th class="h-12 px-6 font-semibold text-slate-600 text-center">Receipt</th>
                <th class="h-12 px-6 font-semibold text-slate-600 text-center">Proof of Delivery</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading">
                <td colspan="7" class="p-8 text-center text-slate-400">Loading order history records...</td>
              </tr>
              <tr v-else-if="orders.length === 0">
                <td colspan="7" class="p-8 text-center text-slate-500">No completed/delivered order history found.</td>
              </tr>
              <tr v-for="order in orders" :key="order.id" class="hover:bg-slate-50/80 transition-colors">
                <td class="p-6 font-semibold text-slate-900">{{ order.code }}</td>
                <td class="p-6 font-medium text-slate-800">{{ order.distributor_name }}</td>
                <td class="p-6">
                  <div class="font-medium text-slate-900">{{ order.product_name }}</div>
                  <div class="text-xs text-slate-500">{{ order.category }}</div>
                </td>
                <td class="p-6">
                  <div class="font-bold text-slate-900">{{ order.quantity }} units</div>
                  <div class="text-xs font-semibold text-emerald-600">₱{{ parseFloat(order.total_cost).toFixed(2) }}</div>
                </td>
                <td class="p-6 text-slate-600 whitespace-nowrap">
                  {{ order.delivered_at ? new Date(order.delivered_at).toLocaleDateString() : 'N/A' }}
                </td>
                
                <!-- Receipt Attachment -->
                <td class="p-6 text-center">
                  <button v-if="order.receipt_file_path" @click="openAttachment(order.receipt_file_path, `Receipt - ${order.code}`)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-50 text-indigo-600 font-medium text-xs hover:bg-indigo-100 transition-colors">
                    <FileText class="h-3.5 w-3.5" /> View Receipt
                  </button>
                  <span v-else class="text-xs text-slate-400">None</span>
                </td>
                
                <!-- Proof of Delivery Attachment mapping to arrival_proof_path -->
                <td class="p-6 text-center">
                  <button v-if="order.arrival_proof_path" @click="openAttachment(order.arrival_proof_path, `Proof of Delivery - ${order.code}`)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 font-medium text-xs hover:bg-emerald-100 transition-colors">
                    <ImageIcon class="h-3.5 w-3.5" /> View Proof
                  </button>
                  <span v-else class="text-xs text-slate-400">None</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Footer -->
        <div v-if="!isLoading && totalOrders > 0" class="p-4 border-t border-slate-100 flex items-center justify-between text-sm bg-slate-50 mt-auto">
          <span class="text-slate-500">Showing page <span class="font-semibold text-slate-700">{{ currentPage }}</span> of <span class="font-semibold text-slate-700">{{ lastPage }}</span> ({{ totalOrders }} total orders)</span>
          <div class="flex items-center gap-2">
            <button 
                @click="fetchOrderHistory(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors"
            >
                Previous
            </button>
            <button 
                @click="fetchOrderHistory(currentPage + 1)" 
                :disabled="currentPage === lastPage"
                class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors"
            >
                Next
            </button>
          </div>
        </div>

      </div>

    </div>

    <!-- Attachment Viewer Modal -->
    <div v-if="viewerModal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
      <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full overflow-hidden border border-slate-200">
        <div class="flex items-center justify-between p-6 border-b border-slate-100">
          <h3 class="font-bold text-lg text-slate-900">{{ viewerModal.title }}</h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
        </div>
        <div class="p-6 flex items-center justify-center bg-slate-50 min-h-[50vh] max-h-[75vh] overflow-auto">
          <img v-if="viewerModal.url.match(/\.(jpg|jpeg|png|jfif|webp|gif)$/i)" :src="viewerModal.url" alt="Attachment Preview" class="max-h-[65vh] rounded-lg object-contain shadow-sm" />
          <iframe v-else :src="viewerModal.url" class="w-full h-[65vh] rounded-lg border bg-white shadow-sm"></iframe>
        </div>
        <div class="flex justify-end p-4 border-t border-slate-100 bg-white">
          <a v-if="!viewerModal.url.match(/\.(jpg|jpeg|png|jfif|webp|gif)$/i)" :href="viewerModal.url" target="_blank" class="mr-auto inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">
             <ExternalLink class="h-4 w-4 mr-1.5"/> Open in new tab
          </a>
          <button @click="closeModal" class="px-6 py-2 rounded-xl text-sm font-medium bg-slate-900 text-white hover:bg-slate-800">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>