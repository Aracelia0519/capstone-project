<template>
  <div class="service-requests p-6">
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Service Requests</h1>
          <p class="text-gray-600">Monitor and track service provider activities and job status</p>
        </div>
        <div class="mt-4 md:mt-0">
          <Button 
            @click="refreshData"
            class="bg-blue-600 hover:bg-blue-700 text-white"
          >
            <i class="fas fa-sync-alt mr-2"></i>
            Refresh Data
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <Card class="shadow-md border-l-4 border-l-blue-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Requests</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ serviceRequests.length }}</h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
              <i class="fas fa-tasks text-blue-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="shadow-md border-l-4 border-l-yellow-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Pending</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statusCounts.pending }}</h3>
            </div>
            <div class="p-3 bg-yellow-50 rounded-lg">
              <i class="fas fa-clock text-yellow-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="shadow-md border-l-4 border-l-green-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">In Progress</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statusCounts.inProgress }}</h3>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
              <i class="fas fa-spinner text-green-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="shadow-md border-l-4 border-l-purple-500">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Completed</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ statusCounts.completed }}</h3>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg">
              <i class="fas fa-check-circle text-purple-500 text-xl"></i>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <Card class="shadow-sm mb-6">
      <CardContent class="p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex flex-col md:flex-row gap-4 flex-1">
            <div class="relative flex-1 md:max-w-md">
              <i class="fas fa-search absolute left-3 top-3 text-gray-400 z-10"></i>
              <Input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search service provider, client, or color..."
                class="pl-10 pr-4 w-full border-gray-300 focus-visible:ring-blue-500"
              />
            </div>
            
            <div class="flex flex-wrap gap-3">
              <Select v-model="selectedStatus">
                <SelectTrigger class="w-[180px] border-gray-300">
                  <SelectValue placeholder="All Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="in-progress">In Progress</SelectItem>
                  <SelectItem value="completed">Completed</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
              </Select>
              
              <Select v-model="selectedProvider">
                <SelectTrigger class="w-[220px] border-gray-300">
                  <SelectValue placeholder="All Service Providers" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Service Providers</SelectItem>
                  <SelectItem v-for="provider in uniqueProviders" :key="provider" :value="provider">
                    {{ provider }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="shadow overflow-hidden bg-white">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-id-badge"></i>
                  <span>Request ID</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-user-hard-hat"></i>
                  <span>Service Provider</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-user"></i>
                  <span>Client</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-palette"></i>
                  <span>Selected Color</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-flag"></i>
                  <span>Status</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-calendar"></i>
                  <span>Date</span>
                </div>
              </TableHead>
              <TableHead class="px-6 py-3">
                <div class="flex items-center gap-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <i class="fas fa-cogs"></i>
                  <span>Actions</span>
                </div>
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody class="bg-white divide-y divide-gray-200">
            <TableRow 
              v-for="request in filteredRequests" 
              :key="request.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-mono font-medium text-gray-900">SR-{{ request.id.toString().padStart(3, '0') }}</div>
                <div class="text-xs text-gray-500">Job Request</div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-hard-hat text-blue-600"></i>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ request.serviceProvider }}</div>
                    <div class="text-xs text-gray-500">{{ request.providerContact }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-tie text-green-600"></i>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ request.clientName }}</div>
                    <div class="text-xs text-gray-500">{{ request.clientLocation }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div 
                    class="w-8 h-8 rounded-md mr-3 border border-gray-300 shadow-sm" 
                    :style="{ backgroundColor: request.colorHex }"
                    :title="request.colorName"
                  ></div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ request.colorName }}</div>
                    <div class="text-xs text-gray-500">{{ request.colorHex }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col items-start">
                  <Badge 
                    class="px-3 py-1 text-xs font-semibold rounded-full border-0 shadow-none mb-1"
                    :class="getStatusClasses(request.status)"
                  >
                    <i :class="getStatusIcon(request.status)" class="mr-1"></i>
                    {{ formatStatus(request.status) }}
                  </Badge>
                  <div class="text-xs text-gray-500 mt-1">
                    <i class="fas fa-clock mr-1"></i>
                    {{ request.duration }}
                  </div>
                </div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ request.date }}</div>
                <div class="text-xs text-gray-500">{{ request.time }}</div>
              </TableCell>
              <TableCell class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <Button 
                    variant="ghost" 
                    size="icon"
                    @click="viewDetails(request)"
                    class="text-blue-600 hover:text-blue-900 hover:bg-blue-50"
                    title="View Details"
                  >
                    <i class="fas fa-eye"></i>
                  </Button>
                  <Button 
                    v-if="request.status === 'pending' || request.status === 'in-progress'"
                    variant="ghost" 
                    size="icon"
                    @click="updateStatus(request)"
                    class="text-green-600 hover:text-green-900 hover:bg-green-50"
                    title="Update Status"
                  >
                    <i class="fas fa-edit"></i>
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="icon"
                    @click="sendReminder(request)"
                    class="text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50"
                    title="Send Reminder"
                  >
                    <i class="fas fa-bell"></i>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      
      <div v-if="filteredRequests.length === 0" class="text-center py-16">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <i class="fas fa-clipboard-list text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No service requests found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Try adjusting your search or filter criteria.</p>
      </div>
      
      <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="text-sm text-gray-500 mb-2 md:mb-0">
            Showing <span class="font-medium">{{ filteredRequests.length }}</span> of 
            <span class="font-medium">{{ serviceRequests.length }}</span> service requests
          </div>
          <div class="flex items-center gap-4">
            <Button 
              variant="outline"
              @click="exportToCSV"
              class="border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
            >
              <i class="fas fa-download mr-2"></i>
              Export CSV
            </Button>
            <div class="text-xs text-gray-500">
              <i class="fas fa-info-circle mr-1"></i>
              Admin view only
            </div>
          </div>
        </div>
      </div>
    </Card>

    <Card class="mt-6 shadow">
      <CardContent class="p-4">
        <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
          <i class="fas fa-info-circle mr-2 text-blue-500"></i>
          Status Legend
        </h3>
        <div class="flex flex-wrap gap-4">
          <div class="flex items-center">
            <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
            <span class="text-sm text-gray-600">Pending - Awaiting assignment</span>
          </div>
          <div class="flex items-center">
            <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
            <span class="text-sm text-gray-600">In Progress - Work ongoing</span>
          </div>
          <div class="flex items-center">
            <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
            <span class="text-sm text-gray-600">Completed - Job finished</span>
          </div>
          <div class="flex items-center">
            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
            <span class="text-sm text-gray-600">Cancelled - Request cancelled</span>
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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

// Service requests data
const serviceRequests = ref([
  {
    id: 1,
    serviceProvider: 'John Painters Inc.',
    providerContact: 'john.painters@email.com',
    clientName: 'Maria Santos',
    clientLocation: 'Imus, Cavite',
    colorName: 'Ocean Blue',
    colorHex: '#4A90E2',
    status: 'pending',
    date: '2024-01-15',
    time: '10:30 AM',
    duration: '2 days pending'
  },
  {
    id: 2,
    serviceProvider: 'ColorCraft Services',
    providerContact: 'info@colorcraft.com',
    clientName: 'Robert Lim',
    clientLocation: 'Dasmarinas, Cavite',
    colorName: 'Sunset Orange',
    colorHex: '#FF6B6B',
    status: 'in-progress',
    date: '2024-01-14',
    time: '2:15 PM',
    duration: 'In progress for 3 hours'
  },
  {
    id: 3,
    serviceProvider: 'Premium Paint Pros',
    providerContact: 'service@premiumpaint.ph',
    clientName: 'Susan Tan',
    clientLocation: 'Bacoor, Cavite',
    colorName: 'Forest Green',
    colorHex: '#2ECC71',
    status: 'completed',
    date: '2024-01-13',
    time: '9:00 AM',
    duration: 'Completed in 2 days'
  },
  {
    id: 4,
    serviceProvider: 'Brush Masters Co.',
    providerContact: 'brush.masters@email.com',
    clientName: 'Carlos Reyes',
    clientLocation: 'General Trias, Cavite',
    colorName: 'Royal Purple',
    colorHex: '#9B59B6',
    status: 'pending',
    date: '2024-01-12',
    time: '4:45 PM',
    duration: '1 day pending'
  },
  {
    id: 5,
    serviceProvider: 'John Painters Inc.',
    providerContact: 'john.painters@email.com',
    clientName: 'Anna Cruz',
    clientLocation: 'Trece Martires, Cavite',
    colorName: 'Slate Gray',
    colorHex: '#7F8C8D',
    status: 'in-progress',
    date: '2024-01-12',
    time: '11:00 AM',
    duration: 'In progress for 1 day'
  },
  {
    id: 6,
    serviceProvider: 'Tint & Finish Experts',
    providerContact: 'expert@tintfinish.ph',
    clientName: 'Michael Ong',
    clientLocation: 'Silang, Cavite',
    colorName: 'Canary Yellow',
    colorHex: '#F1C40F',
    status: 'completed',
    date: '2024-01-10',
    time: '3:30 PM',
    duration: 'Completed in 3 days'
  },
  {
    id: 7,
    serviceProvider: 'ColorCraft Services',
    providerContact: 'info@colorcraft.com',
    clientName: 'Lisa Gomez',
    clientLocation: 'Kawit, Cavite',
    colorName: 'Crimson Red',
    colorHex: '#E74C3C',
    status: 'cancelled',
    date: '2024-01-09',
    time: '1:20 PM',
    duration: 'Cancelled by client'
  },
  {
    id: 8,
    serviceProvider: 'Premium Paint Pros',
    providerContact: 'service@premiumpaint.ph',
    clientName: 'David Lee',
    clientLocation: 'Tagaytay, Cavite',
    colorName: 'Sky Blue',
    colorHex: '#3498DB',
    status: 'pending',
    date: '2024-01-08',
    time: '8:45 AM',
    duration: '4 days pending'
  }
])

// Filters
const searchQuery = ref('')
const selectedStatus = ref('all')
const selectedProvider = ref('all')

// Computed properties
const uniqueProviders = computed(() => {
  const providers = new Set(serviceRequests.value.map(req => req.serviceProvider))
  return Array.from(providers)
})

const statusCounts = computed(() => {
  const counts = {
    pending: 0,
    inProgress: 0,
    completed: 0,
    cancelled: 0
  }
  
  serviceRequests.value.forEach(request => {
    if (request.status === 'pending') counts.pending++
    else if (request.status === 'in-progress') counts.inProgress++
    else if (request.status === 'completed') counts.completed++
    else if (request.status === 'cancelled') counts.cancelled++
  })
  
  return counts
})

const filteredRequests = computed(() => {
  let filtered = serviceRequests.value
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(request => 
      request.serviceProvider.toLowerCase().includes(query) ||
      request.clientName.toLowerCase().includes(query) ||
      request.colorName.toLowerCase().includes(query) ||
      request.clientLocation.toLowerCase().includes(query)
    )
  }
  
  // Apply status filter
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(request => request.status === selectedStatus.value)
  }
  
  // Apply provider filter
  if (selectedProvider.value !== 'all') {
    filtered = filtered.filter(request => request.serviceProvider === selectedProvider.value)
  }
  
  return filtered
})

// Helper functions
const getStatusClasses = (status) => {
  const classes = {
    'pending': 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100',
    'in-progress': 'bg-blue-100 text-blue-800 hover:bg-blue-100',
    'completed': 'bg-green-100 text-green-800 hover:bg-green-100',
    'cancelled': 'bg-red-100 text-red-800 hover:bg-red-100'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusIcon = (status) => {
  const icons = {
    'pending': 'fas fa-clock',
    'in-progress': 'fas fa-spinner fa-spin',
    'completed': 'fas fa-check-circle',
    'cancelled': 'fas fa-times-circle'
  }
  return icons[status] || 'fas fa-question-circle'
}

const formatStatus = (status) => {
  const statusMap = {
    'pending': 'Pending',
    'in-progress': 'In Progress',
    'completed': 'Completed',
    'cancelled': 'Cancelled'
  }
  return statusMap[status] || status
}

// Methods
const refreshData = () => {
  console.log('Refreshing service requests data...')
  // In real app, fetch from API
}

const viewDetails = (request) => {
  console.log('Viewing details for request:', request.id)
  // In real app: navigate to details page or show modal
  alert(`Viewing details for Service Request SR-${request.id.toString().padStart(3, '0')}`)
}

const updateStatus = (request) => {
  console.log('Updating status for request:', request.id)
  // In real app: show status update modal
  alert(`Update status for SR-${request.id.toString().padStart(3, '0')}`)
}

const sendReminder = (request) => {
  console.log('Sending reminder for request:', request.id)
  // In real app: trigger email/notification
  alert(`Reminder sent to ${request.serviceProvider} for SR-${request.id.toString().padStart(3, '0')}`)
}

const exportToCSV = () => {
  const headers = ['ID', 'Service Provider', 'Client', 'Color', 'Status', 'Date', 'Duration']
  const csvContent = [
    headers.join(','),
    ...filteredRequests.value.map(request => [
      `SR-${request.id.toString().padStart(3, '0')}`,
      request.serviceProvider,
      request.clientName,
      request.colorName,
      formatStatus(request.status),
      request.date,
      request.duration
    ].join(','))
  ].join('\n')
  
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `service-requests-${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

onMounted(() => {
  console.log('Service Requests page mounted')
})
</script>

<style scoped>
/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .text-3xl {
    font-size: 1.75rem;
  }
  
  .flex-wrap {
    flex-wrap: wrap;
  }
}
</style>