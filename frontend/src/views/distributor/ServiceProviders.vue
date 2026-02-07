<template>
  <div class="p-4 md:p-6 min-h-screen bg-background">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">Service Providers</h1>
          <p class="text-gray-500 mt-2">Manage relationships with paint service providers in Cavite</p>
        </div>
        <div class="flex items-center space-x-3">
          <div class="relative w-full lg:w-64">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
            <Input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search providers..."
              class="pl-10 border-gray-300 focus-visible:ring-blue-500"
            />
          </div>
          <div class="hidden md:flex items-center space-x-2">
            <Button variant="outline" @click="exportProviders" class="text-gray-700 hover:bg-gray-50 border-gray-300">
              <Download class="w-4 h-4 mr-2" />
              Export
            </Button>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <Card class="border-gray-200 shadow-sm">
        <CardContent class="p-5 flex items-center">
          <div class="p-3 bg-blue-50 rounded-lg mr-4">
            <Users class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500 font-medium">Total Providers</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.totalProviders }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 shadow-sm">
        <CardContent class="p-5 flex items-center">
          <div class="p-3 bg-green-50 rounded-lg mr-4">
            <Activity class="w-6 h-6 text-green-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500 font-medium">Active Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.activeOrders }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 shadow-sm">
        <CardContent class="p-5 flex items-center">
          <div class="p-3 bg-yellow-50 rounded-lg mr-4">
            <Clock class="w-6 h-6 text-yellow-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500 font-medium">Avg. Response Time</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.avgResponseTime }}h</p>
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 shadow-sm">
        <CardContent class="p-5 flex items-center">
          <div class="p-3 bg-purple-50 rounded-lg mr-4">
            <Award class="w-6 h-6 text-purple-600" />
          </div>
          <div>
            <p class="text-sm text-gray-500 font-medium">Satisfaction Score</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.satisfactionScore }}%</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 border-gray-200 shadow-sm">
      <CardContent class="p-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div class="flex flex-wrap gap-3">
            <Button 
              v-for="filter in statusFilters" 
              :key="filter.id"
              @click="setStatusFilter(filter.id)"
              :variant="activeStatusFilter === filter.id ? 'secondary' : 'outline'"
              :class="[
                activeStatusFilter === filter.id 
                  ? 'bg-blue-100 text-blue-700 hover:bg-blue-200 border-blue-200' 
                  : 'text-gray-700 border-gray-300 hover:bg-gray-50'
              ]"
              class="h-9 px-4 text-sm font-medium transition-colors"
            >
              <LayoutGrid v-if="filter.id === 'all'" class="w-4 h-4 mr-2" />
              <CheckCircle2 v-else-if="filter.id === 'active'" class="w-4 h-4 mr-2" />
              <CircleSlash v-else-if="filter.id === 'inactive'" class="w-4 h-4 mr-2" />
              <Clock v-else class="w-4 h-4 mr-2" />
              {{ filter.label }}
            </Button>
          </div>
          <div class="flex items-center space-x-3 w-full md:w-auto">
            <Select v-model="sortBy">
              <SelectTrigger class="w-full md:w-[180px] border-gray-300">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="name">Sort by Name</SelectItem>
                <SelectItem value="orders">Sort by Orders</SelectItem>
                <SelectItem value="rating">Sort by Rating</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="border-gray-200 shadow-sm overflow-hidden">
      <div class="hidden lg:block overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Service Provider</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Contact Info</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Total Orders</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Activity Status</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Rating</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="provider in filteredProviders" :key="provider.id" class="hover:bg-gray-50">
              <TableCell class="py-4 px-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center border border-blue-200">
                    <span class="text-lg font-semibold text-blue-700">{{ getInitials(provider.name) }}</span>
                  </div>
                  <div class="ml-4">
                    <h3 class="font-semibold text-gray-900">{{ provider.name }}</h3>
                    <p class="text-sm text-gray-600">{{ provider.specialization }}</p>
                    <div class="flex items-center mt-1">
                      <MapPin class="w-3 h-3 text-gray-400 mr-1" />
                      <span class="text-xs text-gray-500">{{ provider.location }}</span>
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="py-4 px-6">
                <div class="space-y-2">
                  <div class="flex items-center">
                    <Mail class="w-3.5 h-3.5 text-gray-400 mr-2" />
                    <a :href="`mailto:${provider.email}`" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                      {{ provider.email }}
                    </a>
                  </div>
                  <div class="flex items-center">
                    <Phone class="w-3.5 h-3.5 text-gray-400 mr-2" />
                    <span class="text-sm text-gray-700">{{ provider.phone }}</span>
                  </div>
                  <div v-if="provider.website" class="flex items-center">
                    <Globe class="w-3.5 h-3.5 text-gray-400 mr-2" />
                    <a :href="provider.website" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                      {{ provider.website }}
                    </a>
                  </div>
                </div>
              </TableCell>
              <TableCell class="py-4 px-6">
                <div class="flex items-baseline">
                  <span class="text-2xl font-bold text-gray-900">{{ provider.totalOrders }}</span>
                  <span class="text-sm text-gray-500 ml-1">orders</span>
                </div>
                <div class="mt-2">
                  <div class="flex items-center text-sm">
                    <TrendingUp class="w-3.5 h-3.5 text-green-500 mr-1" />
                    <span class="text-gray-600">₱{{ formatCurrency(provider.totalValue) }}</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ provider.activeOrders }} active
                  </div>
                </div>
              </TableCell>
              <TableCell class="py-4 px-6">
                <div class="flex items-center">
                  <div class="relative flex h-3 w-3 mr-2">
                    <span v-if="provider.status === 'active'" class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75" :class="getStatusColor(provider.status)"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3" :class="getStatusColor(provider.status)"></span>
                  </div>
                  <span class="text-sm font-medium" :class="getStatusTextClass(provider.status)">
                    {{ provider.status.charAt(0).toUpperCase() + provider.status.slice(1) }}
                  </span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                  Last active: {{ formatDate(provider.lastActive) }}
                </div>
              </TableCell>
              <TableCell class="py-4 px-6">
                <div class="flex items-center">
                  <div class="flex items-center">
                    <Star v-for="n in 5" :key="n" 
                      class="w-4 h-4" 
                      :class="n <= Math.floor(provider.rating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" 
                    />
                  </div>
                  <span class="ml-2 text-sm font-medium text-gray-900">{{ provider.rating.toFixed(1) }}</span>
                  <span class="text-xs text-gray-500 ml-1">({{ provider.reviews }})</span>
                </div>
                <div class="mt-2 w-full">
                   <Progress :model-value="(provider.rating / 5) * 100" class="h-1.5 w-24 [&>div]:bg-green-500 bg-gray-200" />
                </div>
              </TableCell>
              <TableCell class="py-4 px-6">
                <div class="flex items-center space-x-2">
                  <Button variant="ghost" size="icon" @click="viewDetails(provider)" class="text-gray-600 hover:text-blue-600 hover:bg-blue-50">
                    <Eye class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="icon" @click="contactProvider(provider)" class="text-gray-600 hover:text-green-600 hover:bg-green-50">
                    <MessageSquare class="w-4 h-4" />
                  </Button>
                  <Button variant="ghost" size="icon" @click="viewOrders(provider)" class="text-gray-600 hover:text-purple-600 hover:bg-purple-50">
                    <FileText class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="lg:hidden">
        <div class="p-4 space-y-4">
          <Card v-for="provider in filteredProviders" :key="provider.id" class="hover:shadow-md transition-shadow border-gray-200">
            <CardContent class="p-4">
              <div class="flex items-start justify-between">
                <div class="flex items-start">
                  <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center border border-blue-200">
                    <span class="text-lg font-semibold text-blue-700">{{ getInitials(provider.name) }}</span>
                  </div>
                  <div class="ml-4">
                    <h3 class="font-semibold text-gray-900 text-lg">{{ provider.name }}</h3>
                    <p class="text-sm text-gray-600">{{ provider.specialization }}</p>
                    <div class="flex items-center mt-1">
                      <div class="flex items-center mr-3">
                        <div class="w-2 h-2 rounded-full mr-1" :class="getStatusColor(provider.status)"></div>
                        <span class="text-xs font-medium" :class="getStatusTextClass(provider.status)">
                          {{ provider.status }}
                        </span>
                      </div>
                      <div class="flex items-center">
                        <MapPin class="w-3 h-3 text-gray-400 mr-1" />
                        <span class="text-xs text-gray-500">{{ provider.location }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-1">
                  <Button variant="ghost" size="icon" @click="viewDetails(provider)" class="h-8 w-8 text-gray-400 hover:text-blue-600">
                    <Eye class="w-4 h-4" />
                  </Button>
                </div>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-4">
                <div>
                  <p class="text-xs text-gray-500">Contact</p>
                  <div class="mt-1 space-y-1">
                    <a :href="`mailto:${provider.email}`" class="text-sm text-blue-600 hover:text-blue-800 hover:underline block truncate">
                      {{ provider.email }}
                    </a>
                    <span class="text-sm text-gray-700 block">{{ provider.phone }}</span>
                  </div>
                </div>
                <div>
                  <p class="text-xs text-gray-500">Orders</p>
                  <div class="mt-1">
                    <div class="flex items-baseline">
                      <span class="text-xl font-bold text-gray-900">{{ provider.totalOrders }}</span>
                      <span class="text-sm text-gray-500 ml-1">total</span>
                    </div>
                    <div class="text-xs text-gray-600 mt-1">{{ provider.activeOrders }} active orders</div>
                  </div>
                </div>
              </div>

              <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex items-center mr-3">
                      <Star v-for="n in 5" :key="n" 
                        class="w-4 h-4" 
                        :class="n <= Math.floor(provider.rating) ? 'text-yellow-400 fill-yellow-400' : 'text-gray-300'" 
                      />
                    </div>
                    <span class="text-sm font-medium text-gray-900">{{ provider.rating.toFixed(1) }}</span>
                  </div>
                  <div class="flex space-x-2">
                    <Button size="sm" @click="contactProvider(provider)" class="bg-blue-600 hover:bg-blue-700 text-white">
                      Contact
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <div v-if="filteredProviders.length === 0" class="text-center py-12">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <Search class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No service providers found</h3>
        <p class="text-gray-500 mb-4">Try adjusting your search or filter</p>
        <Button @click="resetFilters" class="bg-blue-600 hover:bg-blue-700 text-white">
          Reset Filters
        </Button>
      </div>
    </Card>

    <div v-if="filteredProviders.length > 0" class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div class="text-sm text-gray-700 mb-4 sm:mb-0">
        Showing <span class="font-medium">{{ filteredProviders.length }}</span> of <span class="font-medium">{{ providers.length }}</span> service providers
      </div>
      <div class="flex items-center space-x-2">
        <Button 
            variant="outline" 
            size="sm"
            @click="previousPage" 
            :disabled="currentPage === 1"
            class="border-gray-300 text-gray-700"
        >
          <ChevronLeft class="w-4 h-4 mr-1" />
          Previous
        </Button>
        <span class="text-sm text-gray-700 px-3">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <Button 
            variant="outline" 
            size="sm"
            @click="nextPage" 
            :disabled="currentPage === totalPages"
            class="border-gray-300 text-gray-700"
        >
          Next
          <ChevronRight class="w-4 h-4 ml-1" />
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  Search, Download, Users, Activity, Clock, Award, 
  LayoutGrid, CheckCircle2, CircleSlash, MapPin, 
  Mail, Phone, Globe, TrendingUp, Star, Eye, 
  MessageSquare, FileText, ChevronLeft, ChevronRight 
} from 'lucide-vue-next'

// Shadcn Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Progress } from '@/components/ui/progress'

// Data
const searchQuery = ref('')
const activeStatusFilter = ref('all')
const sortBy = ref('name')
const currentPage = ref(1)
const itemsPerPage = 10

const statusFilters = [
  { id: 'all', label: 'All Providers' },
  { id: 'active', label: 'Active' },
  { id: 'inactive', label: 'Inactive' },
  { id: 'busy', label: 'Busy' }
]

const statistics = ref({
  totalProviders: 24,
  activeOrders: 156,
  avgResponseTime: '2.5',
  satisfactionScore: '94.2'
})

const providers = ref([
  {
    id: 1,
    name: 'Cavite Paint Masters',
    specialization: 'Interior & Exterior Painting',
    location: 'Imus, Cavite',
    email: 'info@cavitepaintmasters.ph',
    phone: '+63 912 345 6789',
    website: 'www.cavitepaintmasters.ph',
    totalOrders: 245,
    activeOrders: 12,
    totalValue: 1250000,
    status: 'active',
    lastActive: '2024-01-15',
    rating: 4.8,
    reviews: 89
  },
  {
    id: 2,
    name: 'Imus Color Experts',
    specialization: 'Residential Painting',
    location: 'Imus, Cavite',
    email: 'contact@imuscolorexperts.ph',
    phone: '+63 917 654 3210',
    website: null,
    totalOrders: 189,
    activeOrders: 8,
    totalValue: 980000,
    status: 'busy',
    lastActive: '2024-01-14',
    rating: 4.6,
    reviews: 67
  },
  {
    id: 3,
    name: 'Bacoor Painting Services',
    specialization: 'Commercial Painting',
    location: 'Bacoor, Cavite',
    email: 'service@bacoorpainters.ph',
    phone: '+63 918 765 4321',
    website: 'www.bacoorpaintingservices.ph',
    totalOrders: 312,
    activeOrders: 15,
    totalValue: 1850000,
    status: 'active',
    lastActive: '2024-01-15',
    rating: 4.9,
    reviews: 124
  },
  {
    id: 4,
    name: 'Dasmarinas Paint Pros',
    specialization: 'Industrial Coating',
    location: 'Dasmarinas, Cavite',
    email: 'pros@dasmarinaspaint.ph',
    phone: '+63 919 876 5432',
    website: null,
    totalOrders: 156,
    activeOrders: 6,
    totalValue: 850000,
    status: 'inactive',
    lastActive: '2024-01-10',
    rating: 4.4,
    reviews: 45
  },
  {
    id: 5,
    name: 'Trece Martires Contractors',
    specialization: 'Government Projects',
    location: 'Trece Martires, Cavite',
    email: 'contracts@trecemartires.ph',
    phone: '+63 920 987 6543',
    website: 'www.trececontractors.ph',
    totalOrders: 278,
    activeOrders: 18,
    totalValue: 2100000,
    status: 'active',
    lastActive: '2024-01-15',
    rating: 4.7,
    reviews: 92
  },
  {
    id: 6,
    name: 'Silang Painting Specialists',
    specialization: 'Heritage Restoration',
    location: 'Silang, Cavite',
    email: 'restore@silangpaint.ph',
    phone: '+63 921 234 5678',
    website: null,
    totalOrders: 134,
    activeOrders: 4,
    totalValue: 720000,
    status: 'busy',
    lastActive: '2024-01-13',
    rating: 4.5,
    reviews: 56
  },
  {
    id: 7,
    name: 'General Trias Paint Works',
    specialization: 'New Construction',
    location: 'General Trias, Cavite',
    email: 'works@gentripaint.ph',
    phone: '+63 922 345 6789',
    website: 'www.gentripaintworks.ph',
    totalOrders: 201,
    activeOrders: 11,
    totalValue: 1150000,
    status: 'active',
    lastActive: '2024-01-14',
    rating: 4.8,
    reviews: 78
  },
  {
    id: 8,
    name: 'Kawit Color Lab',
    specialization: 'Color Consultation',
    location: 'Kawit, Cavite',
    email: 'lab@kawitcolor.ph',
    phone: '+63 923 456 7890',
    website: null,
    totalOrders: 98,
    activeOrders: 3,
    totalValue: 450000,
    status: 'inactive',
    lastActive: '2024-01-08',
    rating: 4.3,
    reviews: 34
  },
  {
    id: 9,
    name: 'Naic Painting Co.',
    specialization: 'Waterproofing & Sealants',
    location: 'Naic, Cavite',
    email: 'co@naicpainting.ph',
    phone: '+63 924 567 8901',
    website: 'www.naicpaintingco.ph',
    totalOrders: 167,
    activeOrders: 9,
    totalValue: 920000,
    status: 'active',
    lastActive: '2024-01-15',
    rating: 4.6,
    reviews: 61
  },
  {
    id: 10,
    name: 'Tagaytay Premium Painters',
    specialization: 'Luxury Properties',
    location: 'Tagaytay, Cavite',
    email: 'premium@tagaytaypaint.ph',
    phone: '+63 925 678 9012',
    website: 'www.tagaytaypainters.ph',
    totalOrders: 123,
    activeOrders: 5,
    totalValue: 680000,
    status: 'busy',
    lastActive: '2024-01-14',
    rating: 4.9,
    reviews: 42
  }
])

// Computed
const filteredProviders = computed(() => {
  let filtered = providers.value.filter(provider => {
    // Search filter
    const matchesSearch = !searchQuery.value || 
      provider.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      provider.specialization.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      provider.location.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    // Status filter
    const matchesStatus = activeStatusFilter.value === 'all' || 
      provider.status === activeStatusFilter.value
    
    return matchesSearch && matchesStatus
  })
  
  // Sort
  filtered.sort((a, b) => {
    if (sortBy.value === 'name') {
      return a.name.localeCompare(b.name)
    } else if (sortBy.value === 'orders') {
      return b.totalOrders - a.totalOrders
    } else if (sortBy.value === 'rating') {
      return b.rating - a.rating
    }
    return 0
  })
  
  // Pagination
  const startIndex = (currentPage.value - 1) * itemsPerPage
  return filtered.slice(startIndex, startIndex + itemsPerPage)
})

const totalPages = computed(() => {
  const totalFiltered = providers.value.filter(provider => {
    const matchesSearch = !searchQuery.value || 
      provider.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesStatus = activeStatusFilter.value === 'all' || 
      provider.status === activeStatusFilter.value
    return matchesSearch && matchesStatus
  }).length
  
  return Math.ceil(totalFiltered / itemsPerPage)
})

// Methods
const getInitials = (name) => {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const getStatusColor = (status) => {
  const colors = {
    active: 'bg-green-500',
    inactive: 'bg-gray-400',
    busy: 'bg-yellow-500'
  }
  return colors[status] || 'bg-gray-400'
}

const getStatusTextClass = (status) => {
  const classes = {
    active: 'text-green-700',
    inactive: 'text-gray-700',
    busy: 'text-yellow-700'
  }
  return classes[status] || 'text-gray-700'
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) return 'Today'
  if (diffDays === 1) return 'Yesterday'
  if (diffDays < 7) return `${diffDays} days ago`
  
  return date.toLocaleDateString('en-PH', {
    month: 'short',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  if (amount >= 1000000) {
    return `₱${(amount / 1000000).toFixed(1)}M`
  } else if (amount >= 1000) {
    return `₱${(amount / 1000).toFixed(0)}K`
  }
  return `₱${amount.toLocaleString()}`
}

const setStatusFilter = (status) => {
  activeStatusFilter.value = status
  currentPage.value = 1
}

const resetFilters = () => {
  searchQuery.value = ''
  activeStatusFilter.value = 'all'
  sortBy.value = 'name'
  currentPage.value = 1
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const viewDetails = (provider) => {
  console.log('Viewing details for:', provider.name)
  alert(`Viewing details for ${provider.name}\nSpecialization: ${provider.specialization}\nLocation: ${provider.location}`)
}

const contactProvider = (provider) => {
  console.log('Contacting provider:', provider.name)
  alert(`Initiating contact with ${provider.name}\nEmail: ${provider.email}\nPhone: ${provider.phone}`)
}

const viewOrders = (provider) => {
  console.log('Viewing orders for:', provider.name)
  alert(`Showing orders for ${provider.name}\nTotal Orders: ${provider.totalOrders}\nActive Orders: ${provider.activeOrders}`)
}

const exportProviders = () => {
  const data = {
    exportDate: new Date().toISOString(),
    filters: {
      search: searchQuery.value,
      status: activeStatusFilter.value,
      sortBy: sortBy.value
    },
    providers: filteredProviders.value,
    statistics: statistics.value
  }
  console.log('Exporting providers data:', data)
  alert('Service providers data exported (check console for data structure)')
}
</script>

<style scoped>
/* Custom scrollbar */
.overflow-x-auto {
  -webkit-overflow-scrolling: touch;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Avatar gradient animation */
@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.bg-gradient-to-br {
  background-size: 200% 200%;
}

/* Status indicator animation */
@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Print styles */
@media print {
  button, select, input {
    display: none !important;
  }
}
</style>