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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <Card class="hover:shadow-md transition-shadow duration-300 border-l-4 border-l-green-500">
        <CardContent class="p-6">
          <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 mb-1">Total Revenue</h3>
            <p class="text-2xl font-bold text-gray-900 mb-2">₱1,245,800</p>
            <div class="flex items-center gap-2">
              <span class="text-sm font-medium text-green-600">↑ 12.5%</span>
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
            <h3 class="text-sm font-medium text-gray-500 mb-1">Pending Payments</h3>
            <p class="text-2xl font-bold text-gray-900 mb-2">24</p>
            <p class="text-sm text-gray-600">Total: ₱185,000</p>
          </div>
        </CardContent>
      </Card>

      <Card class="hover:shadow-md transition-shadow duration-300 border-l-4 border-l-blue-500">
        <CardContent class="p-6">
          <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-500 mb-1">Paid Invoices</h3>
            <p class="text-2xl font-bold text-gray-900 mb-2">156</p>
            <p class="text-sm text-gray-600">Total: ₱890,500</p>
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
            <h3 class="text-sm font-medium text-gray-500 mb-1">Overdue Invoices</h3>
            <p class="text-2xl font-bold text-gray-900 mb-2">8</p>
            <p class="text-sm text-gray-600">Total: ₱45,200</p>
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
                  <SelectItem value="year">Last Year</SelectItem>
                  <SelectItem value="ytd">YTD</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <div class="h-64">
            <div class="h-full flex flex-col justify-end">
              <div class="flex items-end justify-between h-5/6 px-4">
                <div class="w-10 bg-gradient-to-t from-green-400 to-green-200 rounded-t-lg hover:opacity-80 transition-all duration-300" style="height: 70%"></div>
                <div class="w-10 bg-gradient-to-t from-green-400 to-green-200 rounded-t-lg hover:opacity-80 transition-all duration-300" style="height: 85%"></div>
                <div class="w-10 bg-gradient-to-t from-green-400 to-green-200 rounded-t-lg hover:opacity-80 transition-all duration-300" style="height: 60%"></div>
                <div class="w-10 bg-gradient-to-t from-green-400 to-green-200 rounded-t-lg hover:opacity-80 transition-all duration-300" style="height: 90%"></div>
                <div class="w-10 bg-gradient-to-t from-green-400 to-green-200 rounded-t-lg hover:opacity-80 transition-all duration-300" style="height: 95%"></div>
                <div class="w-10 bg-gradient-to-t from-green-400 to-green-200 rounded-t-lg hover:opacity-80 transition-all duration-300" style="height: 75%"></div>
              </div>
              <div class="flex justify-between px-4 pt-2 text-sm text-gray-600 border-t border-gray-200">
                <span>Jan</span>
                <span>Feb</span>
                <span>Mar</span>
                <span>Apr</span>
                <span>May</span>
                <span>Jun</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardContent class="p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-900 mb-1">Pending vs Completed</h3>
              <p class="text-sm text-gray-600">Current month status</p>
            </div>
            <div class="mt-2 sm:mt-0 w-[160px]">
              <Select defaultValue="thisMonth">
                <SelectTrigger>
                  <SelectValue placeholder="Select Period" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="thisMonth">This Month</SelectItem>
                  <SelectItem value="lastMonth">Last Month</SelectItem>
                  <SelectItem value="quarterly">Quarterly</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          <div class="h-64">
            <div class="h-full flex flex-col lg:flex-row items-center justify-center gap-8">
              <div class="relative w-40 h-40 rounded-full bg-gray-100 overflow-hidden">
                <div class="absolute inset-0 origin-center bg-green-400" style="transform: rotate(0deg) skewY(60deg)"></div>
                <div class="absolute inset-0 origin-center bg-yellow-400" style="transform: rotate(234deg) skewY(60deg)"></div>
                <div class="absolute inset-0 origin-center bg-red-400" style="transform: rotate(324deg) skewY(60deg)"></div>
              </div>
              <div class="space-y-3">
                <div class="flex items-center gap-3 text-sm text-gray-700">
                  <span class="w-3 h-3 rounded-full bg-green-400"></span>
                  <span>Completed (65%)</span>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-700">
                  <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                  <span>Pending (25%)</span>
                </div>
                <div class="flex items-center gap-3 text-sm text-gray-700">
                  <span class="w-3 h-3 rounded-full bg-red-400"></span>
                  <span>Overdue (10%)</span>
                </div>
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