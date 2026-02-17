<template>
  <div class="flex flex-col gap-6 p-4 sm:p-8 min-h-screen">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">My Suppliers</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage your active supply chain network and monitor performance.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white gap-2 shadow-sm" @click="refreshData">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4" />
          Refresh
        </Button>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
      <div class="relative w-full lg:w-96">
        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
        <Input 
          v-model="searchQuery" 
          placeholder="Search by company, category..." 
          class="pl-9 border-slate-200 focus-visible:ring-indigo-500 w-full"
        />
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-2 w-full lg:w-auto">
        <Select v-model="statusFilter">
          <SelectTrigger class="w-full sm:w-[150px]">
            <SelectValue placeholder="Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Statuses</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="suspended">Suspended</SelectItem>
            <SelectItem value="terminated">Terminated</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card v-for="i in 3" :key="i" class="h-64 animate-pulse bg-slate-100 border-slate-200">
        <CardContent></CardContent>
      </Card>
    </div>

    <div v-else-if="filteredSuppliers.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card 
        v-for="supplier in filteredSuppliers" 
        :key="supplier.id" 
        class="group flex flex-col hover:shadow-md transition-all duration-300 border-slate-200"
      >
        <CardContent class="p-6 flex-grow">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div 
                class="h-14 w-14 rounded-xl flex items-center justify-center text-lg font-bold shadow-sm"
                :class="getAvatarColor(supplier.company_name)"
              >
                {{ getInitials(supplier.company_name) }}
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight group-hover:text-indigo-600 transition-colors">
                  {{ supplier.company_name }}
                </h3>
                <span class="text-xs text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-full mt-1 inline-block">
                  {{ supplier.category }}
                </span>
              </div>
            </div>
            
            <Badge 
              :variant="supplier.status === 'active' ? 'outline' : 'secondary'"
              class="capitalize"
              :class="getStatusColor(supplier.status)"
            >
              {{ supplier.status }}
            </Badge>
          </div>

          <div class="grid grid-cols-2 gap-4 py-4 border-t border-b border-slate-50 mb-4">
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Performance</span>
              <div class="flex items-center gap-2">
                <div class="flex items-center text-amber-500">
                  <Star class="h-3.5 w-3.5 fill-current" />
                  <span class="text-sm font-bold ml-1 text-slate-700">{{ supplier.rating }}</span>
                </div>
                <span class="text-xs text-slate-400">/ 5.0</span>
              </div>
            </div>
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Fulfillment</span>
              <div class="flex items-center gap-2">
                <Progress :model-value="supplier.fulfillment_rate" class="h-1.5 w-16" />
                <span class="text-xs font-bold text-slate-700">{{ supplier.fulfillment_rate }}%</span>
              </div>
            </div>
          </div>

          <div class="space-y-2.5">
            <div class="flex items-start gap-3">
              <MapPin class="h-4 w-4 text-slate-400 mt-0.5 shrink-0" />
              <span class="text-sm text-slate-600 leading-tight line-clamp-1" :title="supplier.location">
                {{ supplier.location }}
              </span>
            </div>
            <div class="flex items-center gap-3">
              <User class="h-4 w-4 text-slate-400 shrink-0" />
              <span class="text-sm text-slate-600">{{ supplier.contact_person }}</span>
            </div>
            <div class="flex items-center gap-3">
              <Phone class="h-4 w-4 text-slate-400 shrink-0" />
              <span class="text-sm text-slate-600">{{ supplier.phone }}</span>
            </div>
          </div>
        </CardContent>

        <CardFooter class="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-3">
          <Button 
            variant="outline" 
            class="flex-1 border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50" 
            @click="openDetails(supplier)"
          >
            View Details
          </Button>
          
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9 text-slate-400 hover:text-slate-700">
                <MoreHorizontal class="h-5 w-5" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
              <DropdownMenuItem>
                <Mail class="mr-2 h-4 w-4" /> Message
              </DropdownMenuItem>
              <DropdownMenuItem>
                <FileText class="mr-2 h-4 w-4" /> View Contracts
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem class="text-red-600 focus:text-red-600 focus:bg-red-50">
                <Ban class="mr-2 h-4 w-4" /> Terminate
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </CardFooter>
      </Card>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-16 sm:py-24 text-center bg-white rounded-xl border border-dashed border-slate-300 mx-4 sm:mx-0">
      <div class="bg-slate-50 p-4 rounded-full mb-4">
        <Store class="w-10 h-10 text-slate-400" />
      </div>
      <h3 class="text-lg font-medium text-slate-900">No suppliers found</h3>
      <p class="text-slate-500 max-w-sm mt-2 px-4">
        You don't have any active partnerships yet. Use the "Link New Supplier" button to find partners.
      </p>
      <Button variant="outline" class="mt-6" @click="resetFilters">
        Clear Filters
      </Button>
    </div>

    <Sheet :open="isSheetOpen" @update:open="isSheetOpen = $event">
      <SheetContent class="w-full sm:max-w-[540px] overflow-y-auto p-0 gap-0 border-l border-slate-200 [&>button]:hidden">
        
        <div class="bg-slate-900 text-white p-6 relative">
           <Button 
            variant="ghost" 
            size="icon" 
            class="absolute right-4 top-4 z-50 text-slate-400 hover:text-white hover:bg-white/10 rounded-full"
            @click="isSheetOpen = false"
           >
             <X class="h-5 w-5" />
           </Button>
           
           <div class="flex items-center gap-4 mt-2">
              <div 
                class="h-16 w-16 rounded-xl flex items-center justify-center text-xl font-bold bg-white/10 text-white backdrop-blur-sm shadow-inner"
              >
                {{ selectedSupplier ? getInitials(selectedSupplier.company_name) : '' }}
              </div>
              <div class="pr-8">
                <h2 class="text-xl font-bold tracking-tight leading-snug">{{ selectedSupplier?.company_name }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-2 text-slate-300 text-sm">
                  <Badge variant="outline" class="text-xs border-slate-600 text-slate-300 font-normal">
                    {{ selectedSupplier?.category }}
                  </Badge>
                  <span class="text-slate-500">•</span>
                  <span class="text-xs font-mono opacity-80">Reg ID: {{ selectedSupplier?.id_number }}</span>
                </div>
              </div>
           </div>
        </div>

        <div class="p-6 space-y-8 bg-white min-h-full" v-if="selectedSupplier">
          
          <div class="grid grid-cols-2 gap-3">
            <Button class="bg-indigo-600 hover:bg-indigo-700 text-white border-0 shadow-sm w-full">
              <MessageSquare class="mr-2 h-4 w-4" /> Message
            </Button>
            <Button variant="outline" class="bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-sm w-full">
              <FileClock class="mr-2 h-4 w-4" /> History
            </Button>
          </div>

          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Performance Metrics</h4>
            <div class="grid grid-cols-3 gap-3">
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-slate-900">{{ selectedSupplier.total_orders }}</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">Orders</div>
              </div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-slate-900">₱{{ selectedSupplier.total_spend }}</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">Spend</div>
              </div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-emerald-600">{{ selectedSupplier.on_time_rate }}%</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">On-Time</div>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact Information</h4>
            <div class="bg-white border border-slate-200 rounded-lg divide-y divide-slate-100 shadow-sm">
              <div class="p-3.5 flex justify-between items-center">
                <div class="flex items-center gap-3 text-sm text-slate-500">
                  <User class="h-4 w-4 shrink-0" />
                  <span>Representative</span>
                </div>
                <span class="text-sm font-medium text-slate-900 text-right">{{ selectedSupplier.contact_person }}</span>
              </div>
              <div class="p-3.5 flex justify-between items-center">
                <div class="flex items-center gap-3 text-sm text-slate-500">
                  <Mail class="h-4 w-4 shrink-0" />
                  <span>Email</span>
                </div>
                <span class="text-sm font-medium text-slate-900 text-right truncate max-w-[180px]" :title="selectedSupplier.email">{{ selectedSupplier.email }}</span>
              </div>
              <div class="p-3.5 flex justify-between items-center">
                <div class="flex items-center gap-3 text-sm text-slate-500">
                  <Phone class="h-4 w-4 shrink-0" />
                  <span>Mobile</span>
                </div>
                <span class="text-sm font-medium text-slate-900 text-right">{{ selectedSupplier.phone }}</span>
              </div>
              <div class="p-3.5">
                <div class="flex items-center gap-3 text-sm text-slate-500 mb-1.5">
                  <MapPin class="h-4 w-4 shrink-0" />
                  <span>Address</span>
                </div>
                <p class="text-sm font-medium text-slate-900 pl-7 leading-relaxed">{{ selectedSupplier.address }}</p>
              </div>
            </div>
          </div>

          <div>
             <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Agreements</h4>
             <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors cursor-pointer group bg-white shadow-sm">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-indigo-50 rounded text-indigo-600 group-hover:bg-indigo-100">
                    <FileBadge class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-slate-900">Partnership Agreement</p>
                    <p class="text-xs text-slate-500">Started: {{ selectedSupplier.partnership_date }}</p>
                  </div>
                </div>
                <Download class="h-4 w-4 text-slate-400 group-hover:text-indigo-600" />
             </div>
           </div>

        </div>
      </SheetContent>
    </Sheet>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { 
  Search, 
  PlusCircle, 
  MapPin, 
  Star, 
  MoreHorizontal, 
  User, 
  Mail,
  Phone,
  Store,
  FileBadge,
  Download,
  X,
  MessageSquare,
  FileClock,
  Ban,
  FileText,
  RefreshCw
} from 'lucide-vue-next'

// Shadcn UI Imports
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Sheet,
  SheetContent,
} from '@/components/ui/sheet'

// --- Data ---
const suppliers = ref([])
const loading = ref(true)

// State
const searchQuery = ref('')
const statusFilter = ref('all')
const categoryFilter = ref('all') // Not currently used by backend, but kept for UI
const isSheetOpen = ref(false)
const selectedSupplier = ref(null)

// --- Helpers ---

// Generate initials from company name
const getInitials = (name) => {
  if (!name) return '??'
  return name
    .split(' ')
    .map(n => n[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

// Assign a consistent color class based on the first letter of the name
const getAvatarColor = (name) => {
  if (!name) return 'bg-slate-100 text-slate-700'
  const colors = [
    'bg-blue-100 text-blue-700',
    'bg-emerald-100 text-emerald-700',
    'bg-violet-100 text-violet-700',
    'bg-amber-100 text-amber-700',
    'bg-rose-100 text-rose-700',
    'bg-cyan-100 text-cyan-700'
  ]
  const index = name.charCodeAt(0) % colors.length
  return colors[index]
}

const getStatusColor = (status) => {
  switch(status) {
    case 'active': return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
    case 'suspended': return 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
    case 'terminated': return 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'
    default: return 'bg-slate-100 text-slate-700'
  }
}

// --- Fetch Data ---
const fetchSuppliers = async () => {
  loading.value = true
  try {
    const response = await api.get('/distributor/partnered-suppliers')
    if (response.data.success) {
      suppliers.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching suppliers:', error)
    toast.error('Failed to load suppliers', {
      description: 'Please check your connection and try again.'
    })
  } finally {
    loading.value = false
  }
}

// --- Computed & Methods ---

const filteredSuppliers = computed(() => {
  return suppliers.value.filter(s => {
    // Search
    const searchLower = searchQuery.value.toLowerCase()
    const matchesSearch = s.company_name.toLowerCase().includes(searchLower) || 
                          s.contact_person.toLowerCase().includes(searchLower)
    
    // Status Filter
    const matchesStatus = statusFilter.value === 'all' || s.status === statusFilter.value

    // Category Filter (Placeholder logic since backend sets all to General currently)
    const matchesCategory = categoryFilter.value === 'all' || s.category === categoryFilter.value
    
    return matchesSearch && matchesStatus && matchesCategory
  })
})

const openDetails = (supplier) => {
  selectedSupplier.value = supplier
  isSheetOpen.value = true
}

const refreshData = () => {
  fetchSuppliers()
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
  categoryFilter.value = 'all'
}

onMounted(() => {
  fetchSuppliers()
})
</script>