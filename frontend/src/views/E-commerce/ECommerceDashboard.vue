<template>
  <div class="ecommerce-dashboard p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">E-Commerce Dashboard</h1>
      <p class="text-gray-300">Real-time overview of sales and performance metrics</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-0 shadow-lg dashboard-card">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-300">Total Orders</p>
              <h3 class="text-2xl md:text-3xl font-bold text-white mt-1">156</h3>
            </div>
            <div class="p-3 rounded-xl bg-indigo-500/30">
              <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <p class="text-xs text-gray-400">↑ 12% from last month</p>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-0 shadow-lg dashboard-card">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-300">Total Revenue</p>
              <h3 class="text-2xl md:text-3xl font-bold text-white mt-1">₱124,567</h3>
            </div>
            <div class="p-3 rounded-xl bg-emerald-500/30">
              <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <p class="text-xs text-gray-400">↑ 18% from last month</p>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-0 shadow-lg dashboard-card">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-300">Pending Orders</p>
              <h3 class="text-2xl md:text-3xl font-bold text-white mt-1">24</h3>
            </div>
            <div class="p-3 rounded-xl bg-amber-500/30">
              <svg class="w-6 h-6 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <p class="text-xs text-gray-400">↓ 5% from yesterday</p>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 border-0 shadow-lg dashboard-card">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-300">Completed Orders</p>
              <h3 class="text-2xl md:text-3xl font-bold text-white mt-1">128</h3>
            </div>
            <div class="p-3 rounded-xl bg-green-500/30">
              <svg class="w-6 h-6 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <p class="text-xs text-gray-400">↑ 22% from last month</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <Card class="dashboard-card bg-[#1e293b] border-gray-800 text-white">
        <CardContent class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Sales Overview</h3>
            <Select default-value="Last 7 Days">
              <SelectTrigger class="w-[140px] bg-gray-800 border-gray-700 text-gray-300 h-8">
                <SelectValue placeholder="Period" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-gray-300">
                <SelectItem value="Last 7 Days">Last 7 Days</SelectItem>
                <SelectItem value="Last 30 Days">Last 30 Days</SelectItem>
                <SelectItem value="Last 3 Months">Last 3 Months</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="h-64 flex items-end justify-between space-x-1">
            <div v-for="day in salesData" :key="day.day" class="flex flex-col items-center">
              <div class="text-xs text-gray-400 mb-2">{{ day.day }}</div>
              <div 
                class="w-8 md:w-10 bg-gradient-to-t from-indigo-500 to-purple-500 rounded-t-lg transition-all hover:opacity-80"
                :style="{ height: day.value * 2 + 'px' }"
                :title="'₱' + day.amount"
              ></div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-white/10">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-300">Total Sales: ₱45,230</span>
              <span class="text-sm text-green-400">+18% growth</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="dashboard-card bg-[#1e293b] border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold text-white mb-6">Order Status</h3>
          <div class="space-y-4">
            <div v-for="status in orderStatus" :key="status.name" class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 rounded-full mr-3" :class="status.color"></div>
                <span class="text-gray-300">{{ status.name }}</span>
              </div>
              <div class="flex items-center">
                <span class="text-white font-medium mr-2">{{ status.count }}</span>
                <span class="text-gray-400 text-sm">{{ status.percentage }}%</span>
              </div>
            </div>
          </div>
          <div class="mt-6 pt-6 border-t border-white/10">
            <div class="h-2 w-full bg-gray-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full" :style="{
                  background: 'linear-gradient(90deg, #10b981 0%, #3b82f6 33%, #f59e0b 66%, #ef4444 100%)',
                  width: '100%'
                }"></div>
              </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <Card class="dashboard-card bg-[#1e293b] border-gray-800 text-white">
        <CardContent class="p-0">
          <div class="flex items-center justify-between p-6">
            <h3 class="text-lg font-semibold text-white">Recent Transactions</h3>
            <Button variant="link" class="text-sm text-indigo-300 hover:text-indigo-200 h-auto p-0">View All</Button>
          </div>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow class="border-b border-white/10 hover:bg-transparent">
                  <TableHead class="text-gray-400">Order ID</TableHead>
                  <TableHead class="text-gray-400">Client</TableHead>
                  <TableHead class="text-gray-400">Amount</TableHead>
                  <TableHead class="text-gray-400">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="transaction in recentTransactions" :key="transaction.id" class="border-b border-white/5 hover:bg-white/5">
                  <TableCell class="text-white font-mono text-sm py-4">{{ transaction.orderId }}</TableCell>
                  <TableCell class="py-4">
                    <div class="flex items-center">
                      <Avatar class="w-8 h-8 mr-3">
                        <AvatarFallback class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-xs">
                          {{ transaction.client.charAt(0) }}
                        </AvatarFallback>
                      </Avatar>
                      <span class="text-gray-300">{{ transaction.client }}</span>
                    </div>
                  </TableCell>
                  <TableCell class="text-white py-4">₱{{ transaction.amount }}</TableCell>
                  <TableCell class="py-4">
                    <Badge variant="outline" :class="[
                      'rounded-full border-0',
                      transaction.status === 'Completed' ? 'bg-green-500/20 text-green-300' :
                      transaction.status === 'Pending' ? 'bg-yellow-500/20 text-yellow-300' :
                      'bg-blue-500/20 text-blue-300'
                    ]">
                      {{ transaction.status }}
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      <Card class="dashboard-card bg-[#1e293b] border-gray-800 text-white">
        <CardContent class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Best Selling Products</h3>
            <Button variant="link" class="text-sm text-indigo-300 hover:text-indigo-200 h-auto p-0">View All</Button>
          </div>
          <div class="space-y-4">
            <div v-for="product in bestSellingProducts" :key="product.id" class="flex items-center justify-between p-3 rounded-lg hover:bg-white/5 transition-colors">
              <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-white font-medium">{{ product.name }}</h4>
                  <p class="text-sm text-gray-400">{{ product.category }}</p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-white font-medium">₱{{ product.sales }}</div>
                <div class="text-sm text-gray-400">{{ product.sold }} sold</div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
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

const salesData = ref([
  { day: 'Mon', value: 40, amount: '8,450' },
  { day: 'Tue', value: 60, amount: '12,670' },
  { day: 'Wed', value: 75, amount: '15,890' },
  { day: 'Thu', value: 55, amount: '11,230' },
  { day: 'Fri', value: 80, amount: '16,980' },
  { day: 'Sat', value: 90, amount: '19,120' },
  { day: 'Sun', value: 70, amount: '14,890' }
])

const orderStatus = ref([
  { name: 'Completed', count: 128, percentage: 82, color: 'bg-green-500' },
  { name: 'Pending', count: 24, percentage: 15, color: 'bg-yellow-500' },
  { name: 'Processing', count: 8, percentage: 5, color: 'bg-blue-500' },
  { name: 'Cancelled', count: 4, percentage: 3, color: 'bg-red-500' }
])

const recentTransactions = ref([
  { id: 1, orderId: 'ORD-7890', client: 'Juan Dela Cruz', amount: '4,567', status: 'Completed' },
  { id: 2, orderId: 'ORD-7891', client: 'Maria Santos', amount: '2,890', status: 'Pending' },
  { id: 3, orderId: 'ORD-7892', client: 'Pedro Reyes', amount: '6,123', status: 'Processing' },
  { id: 4, orderId: 'ORD-7893', client: 'Ana Lopez', amount: '3,456', status: 'Completed' },
  { id: 5, orderId: 'ORD-7894', client: 'Luis Garcia', amount: '5,789', status: 'Completed' }
])

const bestSellingProducts = ref([
  { id: 1, name: 'Premium White Paint', category: 'Interior', sales: '45,670', sold: 156 },
  { id: 2, name: 'Weatherproof Exterior', category: 'Exterior', sales: '38,920', sold: 128 },
  { id: 3, name: 'Quick Dry Primer', category: 'Primer', sales: '28,450', sold: 95 },
  { id: 4, name: 'Gloss Finish Paint', category: 'Interior', sales: '22,180', sold: 74 },
  { id: 5, name: 'Eco-Friendly Paint', category: 'Interior', sales: '18,950', sold: 63 }
])
</script>

<style scoped>
.ecommerce-dashboard {
  min-height: 100vh;
}

.dashboard-card {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-dashboard {
    padding: 1rem;
  }
  
  .dashboard-card {
    padding: 1rem;
  }
}
</style>