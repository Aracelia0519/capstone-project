<template>
  <div class="reports p-6">
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Reports & Analytics</h1>
          <p class="text-gray-600">Decision support foundation for strategic planning</p>
        </div>
        <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
          <div class="relative">
            <Input 
              type="month" 
              v-model="selectedMonth"
              class="w-auto border-gray-300 focus-visible:ring-blue-500"
            />
          </div>
          <Button 
            @click="generateReport"
            class="bg-blue-600 hover:bg-blue-700 text-white"
          >
            <i class="fas fa-chart-line mr-2"></i>
            Generate Report
          </Button>
          <Button 
            variant="outline"
            @click="exportAllReports"
            class="border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
          >
            <i class="fas fa-download mr-2"></i>
            Export All
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <Card class="shadow-md border-l-4 border-l-purple-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Colors Used</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ summaryStats.totalColors }}</h3>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg">
              <i class="fas fa-palette text-purple-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="shadow-md border-l-4 border-l-green-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Active Distributors</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ summaryStats.activeDistributors }}</h3>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
              <i class="fas fa-warehouse text-green-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="shadow-md border-l-4 border-l-blue-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Monthly Requests</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ summaryStats.monthlyRequests }}</h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
              <i class="fas fa-tasks text-blue-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="shadow-md border-l-4 border-l-yellow-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Avg. Completion</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ summaryStats.avgCompletion }}%</h3>
            </div>
            <div class="p-3 bg-yellow-50 rounded-lg">
              <i class="fas fa-chart-pie text-yellow-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <Card class="shadow overflow-hidden bg-white">
        <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="fas fa-chart-bar text-purple-500 text-lg mr-3"></i>
              <h3 class="text-lg font-semibold text-gray-800">Most Used Colors</h3>
            </div>
            <span class="text-xs font-medium text-purple-600 bg-purple-100 px-2 py-1 rounded">
              Top 5
            </span>
          </div>
          <p class="text-sm text-gray-500 mt-1">Popular color selections for decision support</p>
        </div>
        <CardContent class="p-6">
          <div class="space-y-4">
            <div 
              v-for="color in topColors" 
              :key="color.id"
              class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div class="flex items-center">
                <div 
                  class="w-10 h-10 rounded-md mr-4 border shadow-sm" 
                  :style="{ backgroundColor: color.hex }"
                ></div>
                <div>
                  <div class="font-medium text-gray-900">{{ color.name }}</div>
                  <div class="text-sm text-gray-500">{{ color.usageCount }} uses</div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-semibold text-gray-900">{{ color.percentage }}%</div>
                <div class="text-xs text-gray-500">of total</div>
              </div>
            </div>
          </div>
          
          <div class="mt-6 pt-6 border-t border-gray-100">
            <div class="text-sm font-medium text-gray-700 mb-3 flex items-center">
              <i class="fas fa-chart-simple mr-2 text-gray-500"></i>
              Usage Distribution
            </div>
            <div class="space-y-2">
              <div 
                v-for="color in topColors" 
                :key="'bar-' + color.id"
                class="flex items-center"
              >
                <div class="text-xs text-gray-500 w-24 truncate">{{ color.name }}</div>
                <div class="flex-1 ml-2">
                  <div class="bg-gray-200 rounded-full h-2">
                    <div 
                      class="h-2 rounded-full"
                      :style="{ 
                        backgroundColor: color.hex,
                        width: color.percentage + '%' 
                      }"
                    ></div>
                  </div>
                </div>
                <div class="text-xs font-medium text-gray-700 w-8 text-right">{{ color.percentage }}%</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow overflow-hidden bg-white">
        <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="fas fa-trophy text-yellow-500 text-lg mr-3"></i>
              <h3 class="text-lg font-semibold text-gray-800">Top Distributors</h3>
            </div>
            <span class="text-xs font-medium text-yellow-600 bg-yellow-100 px-2 py-1 rounded">
              By Demand
            </span>
          </div>
          <p class="text-sm text-gray-500 mt-1">Highest performing paint distributors</p>
        </div>
        <CardContent class="p-6">
          <div class="space-y-4">
            <div 
              v-for="distributor in topDistributors" 
              :key="distributor.id"
              class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-warehouse text-blue-600"></i>
                </div>
                <div class="ml-4">
                  <div class="font-medium text-gray-900">{{ distributor.name }}</div>
                  <div class="text-sm text-gray-500">{{ distributor.location }}</div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-semibold text-gray-900">{{ distributor.salesVolume.toLocaleString() }}</div>
                <div class="text-xs text-gray-500">gallons sold</div>
              </div>
            </div>
          </div>
          
          <div class="mt-6 pt-6 border-t border-gray-100">
            <div class="text-sm font-medium text-gray-700 mb-3 flex items-center">
              <i class="fas fa-trend-up mr-2 text-gray-500"></i>
              Performance Metrics
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="bg-gray-50 p-3 rounded-lg">
                <div class="text-xs text-gray-500 mb-1">Avg. Inventory Turnover</div>
                <div class="font-semibold text-gray-900">4.2x</div>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg">
                <div class="text-xs text-gray-500 mb-1">Customer Satisfaction</div>
                <div class="font-semibold text-gray-900">92%</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow overflow-hidden bg-white">
        <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <i class="fas fa-calendar-alt text-green-500 text-lg mr-3"></i>
              <h3 class="text-lg font-semibold text-gray-800">Monthly Activity</h3>
            </div>
            <span class="text-xs font-medium text-green-600 bg-green-100 px-2 py-1 rounded">
              {{ currentMonth }}
            </span>
          </div>
          <p class="text-sm text-gray-500 mt-1">Service request summary and trends</p>
        </div>
        <CardContent class="p-6">
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex items-center mb-2">
                <i class="fas fa-play-circle text-blue-500 mr-2"></i>
                <div class="text-sm font-medium text-blue-700">Requests Started</div>
              </div>
              <div class="text-2xl font-bold text-blue-900">{{ monthlyActivity.started }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <div class="flex items-center mb-2">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <div class="text-sm font-medium text-green-700">Requests Completed</div>
              </div>
              <div class="text-2xl font-bold text-green-900">{{ monthlyActivity.completed }}</div>
            </div>
          </div>
          
          <div class="space-y-3">
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center">
                <i class="fas fa-clock text-yellow-500 mr-2"></i>
                <span class="text-gray-600">Pending Requests</span>
              </div>
              <span class="font-medium text-gray-900">{{ monthlyActivity.pending }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center">
                <i class="fas fa-spinner text-blue-500 mr-2"></i>
                <span class="text-gray-600">In Progress</span>
              </div>
              <span class="font-medium text-gray-900">{{ monthlyActivity.inProgress }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center">
                <i class="fas fa-times-circle text-red-500 mr-2"></i>
                <span class="text-gray-600">Cancelled</span>
              </div>
              <span class="font-medium text-gray-900">{{ monthlyActivity.cancelled }}</span>
            </div>
          </div>
          
          <div class="mt-6 pt-6 border-t border-gray-100">
            <div class="flex items-center justify-between mb-2">
              <div class="text-sm font-medium text-gray-700">Monthly Trend</div>
              <div class="flex items-center" :class="trendClass">
                <i :class="trendIcon" class="mr-1"></i>
                <span class="text-xs font-medium">{{ monthlyActivity.trend }}%</span>
              </div>
            </div>
            <div class="text-xs text-gray-500">
              Compared to previous month
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="shadow overflow-hidden mb-8 bg-white">
      <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
        <div class="flex items-center">
          <i class="fas fa-table text-gray-500 text-lg mr-3"></i>
          <h3 class="text-lg font-semibold text-gray-800">Detailed Activity Report</h3>
        </div>
        <p class="text-sm text-gray-500 mt-1">Comprehensive monthly data for analysis</p>
      </div>
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-calendar"></i>
                  <span>Week</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-paint-roller"></i>
                  <span>Top Color</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-building"></i>
                  <span>Top Distributor</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-tasks"></i>
                  <span>Requests</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-percentage"></i>
                  <span>Completion Rate</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-chart-line"></i>
                  <span>Trend</span>
                </div>
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody class="bg-white divide-y divide-gray-200">
            <TableRow 
              v-for="week in weeklyReports" 
              :key="week.week"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-gray-900">Week {{ week.week }}</div>
                <div class="text-sm text-gray-500">{{ week.dates }}</div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div 
                    class="w-6 h-6 rounded mr-2 border" 
                    :style="{ backgroundColor: week.topColor.hex }"
                  ></div>
                  <span class="text-sm font-medium text-gray-900">{{ week.topColor.name }}</span>
                </div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ week.topDistributor }}</div>
                <div class="text-xs text-gray-500">{{ week.distributorSales }} gallons</div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ week.totalRequests }}</div>
                <div class="text-xs text-gray-500">{{ week.completedRequests }} completed</div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div 
                      class="h-2 rounded-full"
                      :class="{
                        'bg-red-500': week.completionRate < 70,
                        'bg-yellow-500': week.completionRate >= 70 && week.completionRate < 90,
                        'bg-green-500': week.completionRate >= 90
                      }"
                      :style="{ width: week.completionRate + '%' }"
                    ></div>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ week.completionRate }}%</span>
                </div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center" :class="getTrendClass(week.trend)">
                  <i :class="getTrendIcon(week.trend)" class="mr-1"></i>
                  <span class="text-sm font-medium">{{ Math.abs(week.trend) }}%</span>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Card class="bg-blue-50 border border-blue-200">
      <CardContent class="p-6">
        <div class="flex items-start mb-4">
          <div class="flex-shrink-0">
            <i class="fas fa-lightbulb text-blue-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <h4 class="font-bold text-blue-900 text-lg">Decision Support Insights</h4>
            <p class="text-blue-700 mt-2">
              Based on the current reports, here are key insights for strategic planning:
            </p>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
          <div class="bg-white p-4 rounded-lg border border-blue-100">
            <div class="flex items-center mb-2">
              <i class="fas fa-palette text-purple-500 mr-2"></i>
              <h5 class="font-semibold text-gray-800">Color Strategy</h5>
            </div>
            <p class="text-sm text-gray-600">
              {{ topColors[0].name }} accounts for {{ topColors[0].percentage }}% of all color selections. 
              Consider increasing inventory for this popular shade.
            </p>
          </div>
          <div class="bg-white p-4 rounded-lg border border-blue-100">
            <div class="flex items-center mb-2">
              <i class="fas fa-warehouse text-yellow-500 mr-2"></i>
              <h5 class="font-semibold text-gray-800">Distributor Performance</h5>
            </div>
            <p class="text-sm text-gray-600">
              Top distributor {{ topDistributors[0].name }} outperforms others by {{ getPerformanceDifference() }}%.
              Consider implementing their best practices across the network.
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

// Current date for reports
const selectedMonth = ref(new Date().toISOString().slice(0, 7))
const currentMonth = computed(() => {
  const date = new Date(selectedMonth.value + '-01')
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

// Summary Statistics
const summaryStats = ref({
  totalColors: 42,
  activeDistributors: 8,
  monthlyRequests: 156,
  avgCompletion: 87
})

// Top Colors Data
const topColors = ref([
  { id: 1, name: 'Ocean Blue', hex: '#4A90E2', usageCount: 45, percentage: 28 },
  { id: 2, name: 'Forest Green', hex: '#2ECC71', usageCount: 32, percentage: 20 },
  { id: 3, name: 'Sunset Orange', hex: '#FF6B6B', usageCount: 25, percentage: 16 },
  { id: 4, name: 'Slate Gray', hex: '#7F8C8D', usageCount: 18, percentage: 11 },
  { id: 5, name: 'Royal Purple', hex: '#9B59B6', usageCount: 15, percentage: 9 }
])

// Top Distributors Data
const topDistributors = ref([
  { id: 1, name: 'ColorCraft Supplies', location: 'Dasmarinas', salesVolume: 12500 },
  { id: 2, name: 'Premium Paint Pros', location: 'Bacoor', salesVolume: 9800 },
  { id: 3, name: 'Brush Masters Co.', location: 'Imus', salesVolume: 8750 },
  { id: 4, name: 'Tint & Finish Experts', location: 'General Trias', salesVolume: 7200 },
  { id: 5, name: 'HueCraft Distributors', location: 'Silang', salesVolume: 6800 }
])

// Monthly Activity Data
const monthlyActivity = ref({
  started: 42,
  completed: 36,
  pending: 8,
  inProgress: 12,
  cancelled: 4,
  trend: 12.5  // Percentage change from previous month
})

// Weekly Reports Data
const weeklyReports = ref([
  { 
    week: 1, 
    dates: 'Jan 1-7', 
    topColor: { name: 'Ocean Blue', hex: '#4A90E2' },
    topDistributor: 'ColorCraft Supplies',
    distributorSales: 3200,
    totalRequests: 38,
    completedRequests: 32,
    completionRate: 84,
    trend: 8
  },
  { 
    week: 2, 
    dates: 'Jan 8-14', 
    topColor: { name: 'Forest Green', hex: '#2ECC71' },
    topDistributor: 'Premium Paint Pros',
    distributorSales: 2850,
    totalRequests: 42,
    completedRequests: 38,
    completionRate: 90,
    trend: 15
  },
  { 
    week: 3, 
    dates: 'Jan 15-21', 
    topColor: { name: 'Sunset Orange', hex: '#FF6B6B' },
    topDistributor: 'Brush Masters Co.',
    distributorSales: 2650,
    totalRequests: 40,
    completedRequests: 35,
    completionRate: 88,
    trend: 5
  },
  { 
    week: 4, 
    dates: 'Jan 22-31', 
    topColor: { name: 'Ocean Blue', hex: '#4A90E2' },
    topDistributor: 'ColorCraft Supplies',
    distributorSales: 3800,
    totalRequests: 46,
    completedRequests: 41,
    completionRate: 89,
    trend: 12
  }
])

// Computed Properties
const trendClass = computed(() => {
  return monthlyActivity.value.trend >= 0 
    ? 'text-green-600' 
    : 'text-red-600'
})

const trendIcon = computed(() => {
  return monthlyActivity.value.trend >= 0 
    ? 'fas fa-arrow-up' 
    : 'fas fa-arrow-down'
})

// Helper Functions
const getTrendClass = (trend) => {
  return trend >= 0 ? 'text-green-600' : 'text-red-600'
}

const getTrendIcon = (trend) => {
  return trend >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'
}

const getPerformanceDifference = () => {
  if (topDistributors.value.length < 2) return 0
  const top = topDistributors.value[0].salesVolume
  const second = topDistributors.value[1].salesVolume
  return Math.round(((top - second) / second) * 100)
}

// Methods
const generateReport = () => {
  console.log('Generating report for:', selectedMonth.value)
  // In real app: fetch data for selected month from API
  alert(`Generating report for ${currentMonth.value}`)
}

const exportAllReports = () => {
  const reportData = {
    summaryStats: summaryStats.value,
    topColors: topColors.value,
    topDistributors: topDistributors.value,
    monthlyActivity: monthlyActivity.value,
    weeklyReports: weeklyReports.value
  }
  
  const dataStr = JSON.stringify(reportData, null, 2)
  const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr)
  
  const exportFileDefaultName = `paint-reports-${selectedMonth.value}.json`
  
  const linkElement = document.createElement('a')
  linkElement.setAttribute('href', dataUri)
  linkElement.setAttribute('download', exportFileDefaultName)
  linkElement.click()
}

onMounted(() => {
  console.log('Reports page loaded')
})
</script>

<style scoped>
/* Custom styles for color swatches and progress bars */
.w-6 {
  width: 1.5rem;
}
.h-6 {
  height: 1.5rem;
}
.w-10 {
  width: 2.5rem;
}
.h-10 {
  height: 2.5rem;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .text-3xl {
    font-size: 1.75rem;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .lg\:grid-cols-3 {
    grid-template-columns: 1fr;
  }
  
  .md\:grid-cols-2 {
    grid-template-columns: 1fr;
  }
}
</style>