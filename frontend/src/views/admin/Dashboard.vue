<template>
  <div class="min-h-screen w-full  font-sans p-6 md:p-8">
    
    <div class="max-w-[1600px] mx-auto space-y-8">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-4 border-b border-slate-200/60">
        <div class="space-y-1">
          <div class="inline-flex items-center rounded-full border border-indigo-500/30 bg-indigo-500/10 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 mb-3">
            <span class="flex h-2 w-2 rounded-full bg-indigo-500 mr-2"></span>
            System Live
          </div>
          <h2 class="text-4xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900">
            Platform Analytics
          </h2>
          <p class="text-sm font-medium text-slate-500">Real-time overview of network health, user metrics, and global reports.</p>
        </div>
        
        <button 
          @click="fetchAnalytics(false)" 
          class="relative inline-flex items-center justify-center rounded-xl text-sm font-medium focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 bg-slate-900 text-white shadow-md hover:bg-slate-800 h-11 px-6 py-2"
        >
          <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
          <span>Sync Data</span>
        </button>
      </div>

      <!-- KPIs (Bento Grid Style) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- KPI 1 -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
          <div class="flex flex-row items-center justify-between pb-4 relative z-10">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
              <h3 class="font-semibold text-slate-600">Total Network</h3>
            </div>
          </div>
          <div class="flex items-baseline gap-2 relative z-10">
            <div class="text-4xl font-black tracking-tight text-slate-900">{{ kpis.total_users }}</div>
            <span class="text-sm font-medium text-emerald-500 flex items-center bg-emerald-50 px-2 py-0.5 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="mr-1"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
              {{ kpis.users_this_month }} new
            </span>
          </div>
        </div>

        <!-- KPI 2 -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
          <div class="flex flex-row items-center justify-between pb-4 relative z-10">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
              <h3 class="font-semibold text-slate-600">Active Sessions</h3>
            </div>
          </div>
          <div class="text-4xl font-black tracking-tight text-slate-900 relative z-10">{{ kpis.active_users }}</div>
          <p class="text-sm font-medium text-slate-400 mt-2 relative z-10">Verified & active accounts</p>
        </div>

        <!-- KPI 3 -->
        <div :class="['relative overflow-hidden rounded-2xl border bg-white p-6 shadow-sm', isPulseTech ? 'border-amber-400 shadow-amber-500/20' : 'border-slate-200/60']">
          <div class="flex flex-row items-center justify-between pb-4 relative z-10">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-amber-600 shadow-inner relative">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/></svg>
              </div>
              <h3 class="font-semibold text-slate-600">Tech Reports</h3>
            </div>
          </div>
          <div class="text-4xl font-black tracking-tight text-slate-900 relative z-10">
            {{ kpis.pending_tech_reports }}
          </div>
          <p class="text-sm font-medium text-slate-400 mt-2 relative z-10">Pending bugs / errors</p>
        </div>

        <!-- KPI 4 -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-sm">
          <div class="flex flex-row items-center justify-between pb-4 relative z-10">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-rose-50 text-rose-600 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
              </div>
              <h3 class="font-semibold text-slate-600">User Reports</h3>
            </div>
          </div>
          <div class="text-4xl font-black tracking-tight text-slate-900 relative z-10">{{ kpis.pending_user_reports }}</div>
          <p class="text-sm font-medium text-slate-400 mt-2 relative z-10">Pending moderation</p>
        </div>
      </div>

      <!-- Charts Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-7 gap-6">
        <!-- Main Line Chart -->
        <div class="lg:col-span-4 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">User Acquisition Trajectory</h3>
            <p class="text-sm font-medium text-slate-500">Historical velocity of new accounts over 6 months.</p>
          </div>
          <div class="p-6 h-[380px] w-full flex-grow relative">
            <div v-if="isLoading" class="absolute inset-0 p-6 flex items-end justify-between gap-2 animate-pulse">
              <div v-for="i in 6" :key="i" class="w-full bg-slate-100 rounded-t-md" :style="{ height: `${Math.random() * 80 + 20}%` }"></div>
            </div>
            <!-- Ensure chart only mounts when datasets array is populated -->
            <Line v-else-if="userGrowthData.datasets?.length > 0" :data="userGrowthData" :options="lineOptions" />
          </div>
        </div>

        <!-- Doughnut Chart -->
        <div class="lg:col-span-3 rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Ecosystem Distribution</h3>
            <p class="text-sm font-medium text-slate-500">Live composition of platform roles.</p>
          </div>
          <div class="p-6 h-[380px] flex items-center justify-center relative">
             <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center animate-pulse">
                <div class="h-48 w-48 rounded-full border-[16px] border-slate-100"></div>
             </div>
            <Doughnut v-else-if="usersRoleData.datasets?.length > 0" :data="usersRoleData" :options="doughnutOptions" />
          </div>
        </div>
      </div>

      <!-- Charts Row 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-10">
        <!-- Bar Chart: Tech Reports -->
        <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">System Anomaly Index</h3>
            <p class="text-sm font-medium text-slate-500">Categorized classification of reported bugs and errors.</p>
          </div>
          <div class="p-6 h-[350px] relative">
            <div v-if="isLoading" class="absolute inset-0 p-6 flex items-end justify-between gap-4 animate-pulse">
              <div v-for="i in 5" :key="i" class="w-full bg-slate-100 rounded-t-md" :style="{ height: `${Math.random() * 60 + 20}%` }"></div>
            </div>
            <Bar v-else-if="techReportsData.datasets?.length > 0" :data="techReportsData" :options="barOptions" />
          </div>
        </div>

        <!-- Bar Chart: User Reports -->
        <div class="rounded-2xl border border-slate-200/60 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex flex-col space-y-1 p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-900">Moderation Heatmap</h3>
            <p class="text-sm font-medium text-slate-500">Volume of user-filed reports segregated by infraction type.</p>
          </div>
          <div class="p-6 h-[350px] relative">
            <div v-if="isLoading" class="absolute inset-0 p-6 flex flex-col items-start justify-between gap-4 animate-pulse">
              <div v-for="i in 5" :key="i" class="h-full bg-slate-100 rounded-r-md" :style="{ width: `${Math.random() * 60 + 20}%` }"></div>
            </div>
            <Bar v-else-if="userReportsData.datasets?.length > 0" :data="userReportsData" :options="barOptionsHorizontal" />
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, shallowRef, onMounted, onBeforeUnmount } from 'vue';
import api from '@/utils/axios.js';
import echo from '@/utils/websocket.js';
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

// Safety killswitch to prevent chart crashes during Vue Router navigation
const isComponentMounted = ref(false);

const isLoading = ref(true);
const isPulseTech = ref(false);

const kpis = ref({
  total_users: 0,
  active_users: 0,
  users_this_month: 0,
  pending_tech_reports: 0,
  pending_user_reports: 0
});

// shallowRef MUST be used here so Vue doesn't deep-proxy the Chart.js objects
const userGrowthData = shallowRef({ labels: [], datasets: [] });
const usersRoleData = shallowRef({ labels: [], datasets: [] });
const techReportsData = shallowRef({ labels: [], datasets: [] });
const userReportsData = shallowRef({ labels: [], datasets: [] });

// Enhanced Chart Palettes
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

// Premium Tooltip configuration
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

// Helper function to create canvas gradients safely
const createGradient = (ctx, chartArea, colorStart, colorEnd) => {
  if (!chartArea) return colorStart;
  const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
  gradient.addColorStop(0, colorStart);
  gradient.addColorStop(1, colorEnd);
  return gradient;
};

// Line Chart with Dynamic Canvas Gradient Fill
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

// Vertical Bar Chart
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

// Horizontal Bar Chart
const barOptionsHorizontal = {
  ...barOptions,
  indexAxis: 'y',
  scales: {
    x: { border: { display: false }, grid: { color: palettes.grid, drawTicks: false }, ticks: { color: palettes.text, padding: 10 } },
    y: { grid: { display: false }, ticks: { color: palettes.text, padding: 10 } }
  },
  barThickness: 24
};

// Doughnut Chart
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

// Fetch Data safely
const fetchAnalytics = async (isBackgroundRefresh = false) => {
  if (!isComponentMounted.value) return; 

  if (!isBackgroundRefresh) isLoading.value = true;
  
  try {
    const response = await api.get('/admin/analytics');
    
    // Final check before rendering data to canvas
    if (!isComponentMounted.value) return; 
    
    if (response.data && response.data.data) {
      const { data } = response.data;
      
      kpis.value = data.kpis;
      
      userGrowthData.value = {
        labels: data.charts.user_growth.labels,
        datasets: [{
          label: 'New Users',
          data: data.charts.user_growth.data,
          borderColor: palettes.primary,
          backgroundColor: (context) => {
            const chart = context.chart;
            const {ctx, chartArea} = chart;
            return createGradient(ctx, chartArea, 'rgba(79, 70, 229, 0.4)', 'rgba(79, 70, 229, 0.0)');
          },
          fill: true,
        }]
      };

      usersRoleData.value = {
        labels: data.charts.users_by_role.labels,
        datasets: [{
          data: data.charts.users_by_role.data,
          backgroundColor: [palettes.primary, palettes.secondary, palettes.accent3, palettes.accent4, palettes.accent2, palettes.accent1],
          borderWidth: 0,
          hoverOffset: 4
        }]
      };

      techReportsData.value = {
        labels: data.charts.tech_reports.labels,
        datasets: [{
          label: 'Reports Filed',
          data: data.charts.tech_reports.data,
          backgroundColor: palettes.accent2,
          hoverBackgroundColor: '#d97706'
        }]
      };

      userReportsData.value = {
        labels: data.charts.user_reports.labels,
        datasets: [{
          label: 'Infractions',
          data: data.charts.user_reports.data,
          backgroundColor: palettes.accent1,
          hoverBackgroundColor: '#e11d48'
        }]
      };
    }
  } catch (error) {
    if (isComponentMounted.value) console.error("Analytics fetch failed:", error);
  } finally {
    if (isComponentMounted.value && !isBackgroundRefresh) {
      isLoading.value = false;
    }
  }
};

// Lifecycle Hooks
onMounted(() => {
  isComponentMounted.value = true;
  fetchAnalytics(false);

  // Safely initialize WebSockets
  if (echo) {
    try {
      echo.private('admin.technical_reports')
        .listen('.report.submitted', () => {
          if (!isComponentMounted.value) return;
          isPulseTech.value = true;
          setTimeout(() => { if (isComponentMounted.value) isPulseTech.value = false; }, 1500);
          kpis.value.pending_tech_reports++;
          fetchAnalytics(true); 
        })
        .listen('.report.updated', () => {
          if (!isComponentMounted.value) return;
          fetchAnalytics(true); 
        });
    } catch (e) {
      console.warn("WebSocket listener initialization skipped:", e);
    }
  }
});

onBeforeUnmount(() => {
  // 1. Flip killswitch so APIs die silently
  isComponentMounted.value = false;

  // 2. Clear chart variables so they drop from the DOM gracefully
  userGrowthData.value = { labels: [], datasets: [] };
  usersRoleData.value = { labels: [], datasets: [] };
  techReportsData.value = { labels: [], datasets: [] };
  userReportsData.value = { labels: [], datasets: [] };
  
  // 3. Unbind sockets
  try {
    if (echo) echo.leave('admin.technical_reports');
  } catch (e) {
    console.warn("Skipped websocket cleanup during unmount");
  }
});
</script>