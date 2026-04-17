<template>
  <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 py-6">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-green-100 text-green-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </span>
          Financial Dashboard
        </h1>
        <p class="text-sm text-gray-600">Quick summary of financial health</p>
      </div>
      <div class="date-selector">
        <div class="px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm text-sm text-gray-700">
          {{ currentDate }}
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <Card class="hover:shadow-md transition-shadow duration-300 border-l-4 border-l-green-500">
          <CardContent class="p-6">
            <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500 mb-1">Period Gross Sales</h3>
              <p class="text-2xl font-bold text-gray-900 mb-2">₱{{ formatNumber(dashboardData.totalSales) }}</p>
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium" :class="dashboardData.salesChangePercent >= 0 ? 'text-green-600' : 'text-red-600'">
                  {{ dashboardData.salesChangePercent >= 0 ? '↑' : '↓' }} {{ Math.abs(dashboardData.salesChangePercent) }}%
                </span>
                <span class="text-xs text-gray-500">from last month</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="hover:shadow-md transition-shadow duration-300 border-l-4 border-l-yellow-500">
          <CardContent class="p-6">
            <div class="w-12 h-12 rounded-xl bg-yellow-100 text-yellow-600 flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500 mb-1">Pending Procurements</h3>
              <p class="text-2xl font-bold text-gray-900 mb-2">{{ dashboardData.pendingBudgetCount }}</p>
              <p class="text-sm text-gray-600">Total: ₱{{ formatNumber(dashboardData.pendingBudgetAmount) }}</p>
            </div>
          </CardContent>
        </Card>

        <Card class="hover:shadow-md transition-shadow duration-300 border-l-4 border-l-red-500">
          <CardContent class="p-6">
            <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center mb-4">
              <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-500 mb-1">Period Total Expenses</h3>
              <p class="text-2xl font-bold text-gray-900 mb-2">₱{{ formatNumber(dashboardData.totalExpenses) }}</p>
              <p class="text-sm text-gray-600">VAT, Payroll, Procurements, Refunds</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <Card>
          <CardContent class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Revenue Per Month</h3>
                <p class="text-sm text-gray-600">Last 6 months performance</p>
              </div>
              <div class="mt-2 sm:mt-0 w-[160px]">
                <Select defaultValue="6months">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Range" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="6months">Last 6 Months</SelectItem>
                    <SelectItem value="year" disabled>Last Year</SelectItem>
                    <SelectItem value="ytd" disabled>YTD</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div class="h-64">
              <div class="h-full flex flex-col justify-end">
                <div class="flex items-end justify-between h-5/6 px-4">
                  <div v-for="(month, index) in dashboardData.monthlyRevenue" :key="index" class="w-10 bg-gradient-to-t from-green-400 to-green-200 rounded-t-lg hover:opacity-80 transition-all duration-300 relative group" :style="{ height: `${month.percentage}%` }">
                    <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
                      ₱{{ formatNumber(month.value) }}
                    </div>
                  </div>
                </div>
                <div class="flex justify-between px-4 pt-2 text-sm text-gray-600 border-t border-gray-200 mt-2">
                  <span v-for="(month, index) in dashboardData.monthlyRevenue" :key="'label-'+index">{{ month.label }}</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Order Status Distribution</h3>
                <p class="text-sm text-gray-600">Current month status</p>
              </div>
              <div class="mt-2 sm:mt-0 w-[160px]">
                <Select defaultValue="thisMonth">
                  <SelectTrigger>
                    <SelectValue placeholder="Select Period" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="thisMonth">This Month</SelectItem>
                    <SelectItem value="lastMonth" disabled>Last Month</SelectItem>
                    <SelectItem value="quarterly" disabled>Quarterly</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            <div class="h-64">
              <div class="h-full flex flex-col lg:flex-row items-center justify-center gap-8">
                <div class="relative w-40 h-40 rounded-full bg-gray-100 overflow-hidden" :style="{ background: pieChartStyle }">
                  </div>
                <div class="space-y-3">
                  <div class="flex items-center gap-3 text-sm text-gray-700">
                    <span class="w-3 h-3 rounded-full bg-green-400"></span>
                    <span>Completed ({{ dashboardData.orderDistribution.completed }}%)</span>
                  </div>
                  <div class="flex items-center gap-3 text-sm text-gray-700">
                    <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                    <span>Pending ({{ dashboardData.orderDistribution.pending }}%)</span>
                  </div>
                  <div class="flex items-center gap-3 text-sm text-gray-700">
                    <span class="w-3 h-3 rounded-full bg-red-400"></span>
                    <span>Cancelled ({{ dashboardData.orderDistribution.cancelled }}%)</span>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { Card, CardContent } from '@/components/ui/card'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const currentDate = ref(new Date().toLocaleDateString('en-US', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric'
}))

const isLoading = ref(true)
const dashboardData = ref({
  totalSales: 0,
  salesChangePercent: 0,
  pendingBudgetCount: 0,
  pendingBudgetAmount: 0,
  totalExpenses: 0,
  monthlyRevenue: [],
  orderDistribution: {
    completed: 0,
    pending: 0,
    cancelled: 0
  }
})

// Utility function to format numbers to 2 decimal places with commas
const formatNumber = (num) => {
  if (!num && num !== 0) return '0.00'
  return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

// Compute Pie Chart CSS dynamically using conic-gradient
const pieChartStyle = computed(() => {
  const dist = dashboardData.value.orderDistribution
  // If no data, render a gray circle
  if (dist.completed === 0 && dist.pending === 0 && dist.cancelled === 0) {
    return 'conic-gradient(#e5e7eb 0deg 360deg)'
  }

  const completedDeg = (dist.completed / 100) * 360
  const pendingDeg = completedDeg + ((dist.pending / 100) * 360)
  
  return `conic-gradient(
    #4ade80 0deg ${completedDeg}deg, 
    #facc15 ${completedDeg}deg ${pendingDeg}deg, 
    #f87171 ${pendingDeg}deg 360deg
  )`
})

// Fetch Data from the newly created Backend Endpoints
const fetchDashboardData = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/finance/dashboard')
    
    if (response.data.success) {
      dashboardData.value = response.data.data
    }
  } catch (error) {
    console.error("Error fetching finance dashboard data:", error)
    alert("Unauthorized or failed to fetch data. Check your RBAC permissions.")
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchDashboardData()
})
</script>

<style scoped>
.date-selector {
  min-width: 200px;
}

@media (max-width: 640px) {
  .date-selector {
    min-width: 100%;
  }
}
</style>