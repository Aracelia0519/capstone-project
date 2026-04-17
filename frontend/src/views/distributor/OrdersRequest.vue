<template>
  <div class="p-6">
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Orders & Requests</h1>
          <p class="text-gray-600 mt-2">View incoming client and service provider orders</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="relative w-[180px]">
             <Select v-model="statusFilter">
              <SelectTrigger class="w-full bg-white border-gray-300">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="confirmed">Confirmed</SelectItem>
                <SelectItem value="prepared">Prepared</SelectItem>
                <SelectItem value="shipped">Shipped</SelectItem>
                <SelectItem value="delivered">Delivered</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="relative w-full sm:w-64">
            <Input 
              type="text" 
              v-model="searchQuery"
              placeholder="Search orders..." 
              class="pl-10 pr-4 w-full border-gray-300"
            />
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>

          <Button @click="fetchOrders" variant="outline" class="border-gray-300 text-gray-700 bg-white hover:bg-gray-50 flex items-center justify-center">
            <svg class="w-4 h-4 mr-2" :class="{'animate-spin': isLoading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </Button>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50/80 border-b border-gray-200">
            <TableRow>
              <TableHead class="font-semibold text-gray-700 py-4 px-6 w-24">Order ID</TableHead>
              <TableHead class="font-semibold text-gray-700 py-4 px-6">Customer / SP</TableHead>
              <TableHead class="font-semibold text-gray-700 py-4 px-6">Type</TableHead>
              <TableHead class="font-semibold text-gray-700 py-4 px-6">Date</TableHead>
              <TableHead class="font-semibold text-gray-700 py-4 px-6">Total Amount</TableHead>
              <TableHead class="font-semibold text-gray-700 py-4 px-6 w-32">Status</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow 
              v-for="order in paginatedRequests" 
              :key="'order-'+order.type+'-'+order.id"
              class="hover:bg-gray-50 transition-colors border-b border-gray-100 last:border-0"
            >
              <TableCell class="font-medium text-gray-900 py-4 px-6">
                ORD-{{ order.id }}
              </TableCell>
              <TableCell class="py-4 px-6 text-gray-700">
                {{ order.customer_name }}
              </TableCell>
              <TableCell class="py-4 px-6">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                      :class="order.type === 'Client' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'">
                  {{ order.type }}
                </span>
              </TableCell>
              <TableCell class="py-4 px-6 text-gray-500">
                {{ formatDate(order.created_at) }}
              </TableCell>
              <TableCell class="py-4 px-6 text-gray-900 font-medium">
                ₱{{ formatCurrency(order.total_amount) }}
              </TableCell>
              <TableCell class="py-4 px-6">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize" :class="getStatusColor(order.status)">
                  {{ order.status.replace('_', ' ') }}
                </span>
              </TableCell>
            </TableRow>

            <TableRow v-if="filteredRequests.length === 0">
              <TableCell colspan="6" class="h-32 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  No orders found matching your criteria.
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="filteredRequests.length > 0" class="border-t border-gray-200 bg-gray-50/50 px-6 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-600">
          Showing <span class="font-medium text-gray-900">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to 
          <span class="font-medium text-gray-900">{{ Math.min(currentPage * itemsPerPage, filteredRequests.length) }}</span> of 
          <span class="font-medium text-gray-900">{{ filteredRequests.length }}</span> orders
        </p>
        <div class="flex items-center gap-2">
          <Button 
            variant="outline" 
            size="sm"
            @click="prevPage" 
            :disabled="currentPage === 1"
            class="bg-white"
          >
            Previous
          </Button>
          <div class="flex items-center gap-1 px-2 text-sm font-medium text-gray-600">
            Page {{ currentPage }} of {{ totalPages }}
          </div>
          <Button 
            variant="outline" 
            size="sm"
            @click="nextPage" 
            :disabled="currentPage >= totalPages"
            class="bg-white"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/utils/axios'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

export default {
  name: 'OrdersRequest',
  components: {
    Input,
    Button,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
  },
  data() {
    return {
      isLoading: true,
      searchQuery: '',
      statusFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      orders: []
    }
  },
  computed: {
    filteredRequests() {
      return this.orders.filter(order => {
        const matchesSearch = 
          (order.customer_name && order.customer_name.toLowerCase().includes(this.searchQuery.toLowerCase())) ||
          (order.id && order.id.toString().includes(this.searchQuery))
        
        const matchesStatus = this.statusFilter === 'all' || order.status.toLowerCase() === this.statusFilter.toLowerCase()
        
        return matchesSearch && matchesStatus
      })
    },
    totalPages() {
      return Math.ceil(this.filteredRequests.length / this.itemsPerPage) || 1
    },
    paginatedRequests() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredRequests.slice(start, end)
    }
  },
  watch: {
    searchQuery() {
      this.currentPage = 1
    },
    statusFilter() {
      this.currentPage = 1
    }
  },
  mounted() {
    this.fetchOrders()
  },
  methods: {
    async fetchOrders() {
      this.isLoading = true
      try {
        const response = await api.get('/distributor/orders-requests')
        if (response.data && response.data.success) {
          this.orders = response.data.data
        }
      } catch (error) {
        console.error('Failed to fetch orders:', error)
      } finally {
        this.isLoading = false
      }
    },
    formatDate(dateString) {
      if (!dateString) return ''
      return new Date(dateString).toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
      })
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH', { 
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2 
      }).format(amount || 0)
    },
    getStatusColor(status) {
      const colors = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'confirmed': 'bg-blue-100 text-blue-800',
        'prepared': 'bg-indigo-100 text-indigo-800',
        'ready_for_pickup': 'bg-orange-100 text-orange-800',
        'shipped': 'bg-purple-100 text-purple-800',
        'delivered': 'bg-teal-100 text-teal-800',
        'completed': 'bg-green-100 text-green-800',
        'cancelled': 'bg-red-100 text-red-800'
      }
      return colors[status?.toLowerCase()] || 'bg-gray-100 text-gray-800'
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--
      }
    }
  }
}
</script>

<style scoped>
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
  }
}
</style>