<template>
  <div class="p-4 md:p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 md:mb-8 gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Distributor Dashboard</h1>
        <p class="text-gray-600 mt-2">Combined Overview: Finance, HR, and Operations</p>
      </div>
      <div class="flex flex-wrap items-center gap-3">
        <Button @click="showDepositDialog = true" class="bg-emerald-600 hover:bg-emerald-700 text-white gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Deposit Funds
        </Button>
        <Button @click="confirmRefresh" class="bg-blue-600 hover:bg-blue-700 text-white gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh Data
        </Button>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
        
        <Card class="bg-white hover:shadow-md transition-shadow border-gray-100 border-l-3 border-l-green-500">
          <CardContent class="p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
              <div class="p-2 bg-green-50 rounded-lg">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <span class="text-sm font-medium" :class="dashboardData.finance.salesChangePercent >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ dashboardData.finance.salesChangePercent >= 0 ? '↑' : '↓' }} {{ Math.abs(dashboardData.finance.salesChangePercent) }}%
              </span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 mb-1">Period Gross Sales</p>
              <h3 class="text-2xl font-bold text-gray-900">₱{{ formatCurrency(dashboardData.finance.totalSales) }}</h3>
            </div>
          </CardContent>
        </Card>

        

        <Card class="bg-white hover:shadow-md transition-shadow border-gray-100 border-l-3 border-l-purple-500">
          <CardContent class="p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
              <div class="p-2 bg-purple-50 rounded-lg">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-yellow-600 bg-yellow-50 px-2 py-1 rounded-full">
                {{ dashboardData.ecommerce.pendingOrders }} Pending
              </span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 mb-1">Total Orders</p>
              <h3 class="text-2xl font-bold text-gray-900">{{ dashboardData.ecommerce.totalOrders }}</h3>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-white hover:shadow-md transition-shadow border-gray-100 border-l-3 border-l-orange-500">
          <CardContent class="p-4 md:p-5">
            <div class="flex items-center justify-between mb-3">
              <div class="p-2 bg-orange-50 rounded-lg">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <span class="text-sm font-medium text-green-600">
                {{ dashboardData.hr.activeEmployees }} Active
              </span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500 mb-1">Total Employees</p>
              <h3 class="text-2xl font-bold text-gray-900">{{ dashboardData.hr.totalEmployees }}</h3>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <Card class="border-gray-200 shadow-sm">
          <CardContent class="p-4 sm:p-6">
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-800">Top Products Sales Volume</h2>
              <p class="text-gray-600 text-sm mt-1">Highest revenue generating products</p>
            </div>
            
            <div class="h-60 sm:h-72 relative min-h-[250px]">
              <div class="h-full flex items-end justify-around pt-4 pb-8 border-b border-gray-100">
                
                <div v-for="product in dashboardData.ecommerce.bestSellingProducts" :key="'chart-'+product.id" class="w-12 sm:w-16 h-full flex flex-col justify-end items-center group relative">
                  
                  <div class="w-full bg-gradient-to-t from-purple-600 to-purple-400 rounded-t-md transition-all duration-500 hover:opacity-80"
                       :style="{ height: `${Math.max((parseCurrency(product.sales) / maxProductSales) * 100, 2)}%` }">
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                      ₱{{ product.sales }}
                    </div>
                  </div>

                  <span class="text-[10px] sm:text-xs text-gray-500 mt-2 truncate w-full text-center" :title="product.name">
                    {{ product.name.split(' ')[0] }}
                  </span>
                </div>
                
                <div v-if="dashboardData.ecommerce.bestSellingProducts.length === 0" class="absolute inset-0 flex items-center justify-center text-gray-400">
                  No sales data available yet
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 shadow-sm">
          <CardContent class="p-4 sm:p-6">
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-800">Revenue Trend</h2>
              <p class="text-gray-600 text-sm mt-1">Gross sales over the last 6 months</p>
            </div>
            
            <div class="h-60 sm:h-72 relative min-h-[250px]">
              <div class="h-full flex items-end justify-around pt-4 pb-8 border-b border-gray-100">
                
                <div v-for="month in dashboardData.finance.monthlyRevenue" :key="'rev-'+month.label" class="w-10 sm:w-14 h-full flex flex-col justify-end items-center group relative">
                  
                  <div class="w-full bg-gradient-to-t from-blue-600 to-blue-400 rounded-t-md transition-all duration-500 hover:opacity-80"
                       :style="{ height: `${Math.max((parseCurrency(month.revenue) / maxMonthlyRevenue) * 100, 2)}%` }">
                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                      ₱{{ formatCurrency(month.revenue) }}
                    </div>
                  </div>

                  <span class="text-[10px] sm:text-xs text-gray-500 mt-2 truncate w-full text-center">
                    {{ month.label }}
                  </span>
                </div>
                
                <div v-if="dashboardData.finance.monthlyRevenue.length === 0" class="absolute inset-0 flex items-center justify-center text-gray-400">
                  No revenue data available
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <Card class="border-gray-200 shadow-sm col-span-1">
          <CardContent class="p-4 sm:p-6">
            <div class="mb-6">
              <h2 class="text-lg font-semibold text-gray-800">Workforce Status</h2>
              <p class="text-gray-600 text-sm mt-1">Latest attendance distribution</p>
            </div>
            
            <div class="space-y-6 pt-2">
              <div v-for="stat in dashboardData.hr.attendanceData" :key="'graph-'+stat.name" class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                  <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-700">{{ stat.name }}</span>
                  </div>
                  <span class="font-bold text-gray-900">{{ stat.value }} ({{ getAttendancePercentage(stat.value) }}%)</span>
                </div>
                <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-500" 
                       :style="{ width: `${getAttendancePercentage(stat.value)}%`, backgroundColor: stat.color }">
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 shadow-sm overflow-hidden col-span-1 lg:col-span-2">
          <div class="px-4 sm:px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-800">Recent Transactions</h2>
            <p class="text-gray-600 text-sm mt-1">Latest E-Commerce and SP Orders</p>
          </div>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50/50">
                <TableRow>
                  <TableHead>Order ID</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Date</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="text-right font-semibold">Amount</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="order in dashboardData.ecommerce.recentOrders" :key="'order-'+order.order_id" class="hover:bg-gray-50">
                  <TableCell class="font-medium text-gray-900">ORD-{{ order.order_id }}</TableCell>
                  <TableCell class="text-gray-600">{{ order.type }}</TableCell>
                  <TableCell class="text-gray-500">{{ formatDate(order.created_at) }}</TableCell>
                  <TableCell>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="getStatusColor(order.status)">
                      {{ order.status.replace('_', ' ') }}
                    </span>
                  </TableCell>
                  <TableCell class="text-right text-gray-900 font-semibold">₱{{ formatCurrency(order.amount) }}</TableCell>
                </TableRow>
                <TableRow v-if="dashboardData.ecommerce.recentOrders.length === 0">
                  <TableCell colspan="5" class="text-center py-8 text-gray-500">
                    No recent transactions found.
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </Card>
      </div>
    </div>

    <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full mx-4">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Confirm Action</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to refresh the dashboard data?</p>
        <div class="flex justify-end gap-3">
          <Button @click="showConfirmDialog = false" variant="outline" class="border-gray-300 text-gray-700 hover:bg-gray-50">
            Cancel
          </Button>
          <Button @click="executeRefresh" class="bg-blue-600 text-white hover:bg-blue-700">
            Confirm
          </Button>
        </div>
      </div>
    </div>

    <Dialog :open="showDepositDialog" @update:open="showDepositDialog = $event">
      <DialogContent class="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Deposit Funds</DialogTitle>
          <DialogDescription>
            Add money directly to your overall sales revenue.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <div class="grid gap-2">
            <Label for="amount">Deposit Amount (₱)</Label>
            <Input 
              id="amount" 
              type="number" 
              v-model="depositAmount" 
              placeholder="0.00" 
              min="1"
              @keydown="preventMathSymbols"
            />
          </div>
          <div class="bg-blue-50 border border-blue-100 rounded-md p-3 text-sm text-blue-700 flex flex-col gap-1">
            <p><strong>Note:</strong></p>
            <p>• Amounts <strong>≤ ₱10,000</strong> will be processed via GCash.</p>
            <p>• Amounts <strong>> ₱10,000</strong> will be processed via Bank Transfer.</p>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showDepositDialog = false" :disabled="isDepositing">Cancel</Button>
          <Button @click="executeDeposit" :disabled="!depositAmount || depositAmount <= 0 || isDepositing" class="bg-emerald-600 hover:bg-emerald-700 text-white">
            <span v-if="isDepositing" class="mr-2 animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
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

const router = useRouter()
const route = useRoute()

const isLoading = ref(true)
const showConfirmDialog = ref(false)
const showDepositDialog = ref(false)
const depositAmount = ref('')
const isDepositing = ref(false)

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
  // Prevent 'e', 'E', '+', and '-' from being typed into the number input
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

const maxProductSales = computed(() => {
  if (!dashboardData.value.ecommerce.bestSellingProducts.length) return 1
  const max = Math.max(...dashboardData.value.ecommerce.bestSellingProducts.map(p => parseCurrency(p.sales)))
  return max > 0 ? max : 1 
})

const maxMonthlyRevenue = computed(() => {
  if (!dashboardData.value.finance.monthlyRevenue.length) return 1
  const max = Math.max(...dashboardData.value.finance.monthlyRevenue.map(m => parseCurrency(m.revenue)))
  return max > 0 ? max : 1
})

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
    'completed': 'bg-green-100 text-green-800',
    'delivered': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-blue-100 text-blue-800',
    'shipped': 'bg-purple-100 text-purple-800',
    'cancelled': 'bg-red-100 text-red-800'
  }
  return map[status?.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

// === Data Fetching ===
const fetchDashboardData = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/distributor/combined-dashboard')
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
    
    // Display the formal message from the backend properly
    toast.error('Deposit Unavailable', {
      description: error.response?.data?.message || 'An error occurred while initiating the deposit. Please try again later.',
      duration: 6000 
    })
    
    isDepositing.value = false
    showDepositDialog.value = false // Optionally close the dialog when configuration is missing
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
    router.replace({ query: {} }) // Clear URL parameters
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
