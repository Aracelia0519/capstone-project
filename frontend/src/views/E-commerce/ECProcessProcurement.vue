<template>
  <div class="procurement-fulfillment min-h-screen p-4 md:p-6 text-gray-100">
    
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Procurement Fulfillment</h1>
          <p class="text-gray-400">Manage finance-approved requests and warehouse logistics</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <Button variant="outline" class="bg-gray-900 border-gray-800 text-gray-300 hover:bg-gray-800 hover:text-white">
            <Download class="w-4 h-4 mr-2" />
            Export Manifest
          </Button>
          <Button class="bg-blue-600 hover:bg-blue-500 text-white border-0">
            <RefreshCw class="w-4 h-4 mr-2" />
            Refresh
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ requests.length }}</div>
          <div class="text-sm text-gray-300">Total Requests</div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ requests.filter(r => r.status === 'Approved').length }}</div>
          <div class="text-sm text-gray-300">Ready to Pack</div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ requests.filter(r => r.status === 'Processing').length }}</div>
          <div class="text-sm text-gray-300">Processing</div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-indigo-500/20 to-violet-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ requests.filter(r => r.status === 'In Transit').length }}</div>
          <div class="text-sm text-gray-300">In Transit</div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ requests.filter(r => r.status === 'Delivered').length }}</div>
          <div class="text-sm text-gray-300">Delivered</div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 bg-gray-900/50 border-gray-800 backdrop-blur-sm">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="md:col-span-2 space-y-2">
            <Label class="text-gray-300">Search Request</Label>
            <div class="relative">
              <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500" />
              <Input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Search ID, Department, or Requester..." 
                class="pl-9 bg-gray-800 border-gray-700 text-white placeholder:text-gray-500 focus-visible:ring-blue-500" 
              />
            </div>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Status</Label>
            <Select v-model="selectedStatus">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white focus:ring-blue-500">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="Approved">Ready to Pack</SelectItem>
                <SelectItem value="Processing">Processing</SelectItem>
                <SelectItem value="In Transit">In Transit</SelectItem>
                <SelectItem value="Delivered">Delivered</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Priority</Label>
            <Select v-model="selectedPriority">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white focus:ring-blue-500">
                <SelectValue placeholder="All Priorities" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all">All Priorities</SelectItem>
                <SelectItem value="High">High Priority</SelectItem>
                <SelectItem value="Normal">Normal</SelectItem>
                <SelectItem value="Low">Low</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-end">
            <Button @click="resetFilters" variant="outline" class="w-full bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white">
              Reset Filters
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-900/90">
            <TableRow class="hover:bg-transparent border-gray-800">
              <TableHead class="text-gray-300">Request Details</TableHead>
              <TableHead class="text-gray-300">Department</TableHead>
              <TableHead class="text-gray-300">Logistics Info</TableHead>
              <TableHead class="text-gray-300">Status</TableHead>
              <TableHead class="text-gray-300">Value</TableHead>
              <TableHead class="text-gray-300 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="req in filteredRequests" :key="req.id" class="border-gray-800 hover:bg-white/5 transition-colors">
              <TableCell>
                <div class="font-mono text-white font-medium">{{ req.id }}</div>
                <div class="text-xs text-gray-500">{{ req.date }}</div>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-600 to-cyan-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ req.department.substring(0,2).toUpperCase() }}
                  </div>
                  <div>
                    <div class="text-white text-sm font-medium">{{ req.department }}</div>
                    <div class="text-xs text-gray-400">{{ req.requester }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <div class="text-sm text-gray-300 flex items-center gap-1">
                  <MapPin class="w-3 h-3 text-gray-500" />
                  {{ req.location }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                  {{ req.items.length }} items to pack
                </div>
              </TableCell>
              <TableCell>
                <Badge :class="['rounded-full border-0 font-medium px-2 py-0.5', statusClasses[req.status]]">
                  {{ req.status === 'Approved' ? 'Ready' : req.status }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="text-white font-medium">₱{{ req.totalAmount.toLocaleString() }}</div>
                <div v-if="req.priority === 'High'" class="text-xs text-red-400 font-medium flex items-center mt-0.5">
                  <AlertCircle class="w-3 h-3 mr-1" /> High Priority
                </div>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end space-x-2">
                  <Button size="sm" variant="ghost" @click="viewDetails(req)" 
                          class="h-8 px-2 bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 hover:text-blue-300">
                    View
                  </Button>
                  <Button size="sm" variant="outline" @click="advanceStatus(req)"
                          class="h-8 px-2 bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white">
                    <ArrowRight class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Dialog :open="!!selectedRequest" @update:open="(val) => !val && (selectedRequest = null)">
      <DialogContent class="bg-gray-950 border-gray-800 text-white sm:max-w-3xl max-h-[90vh] overflow-y-auto">
        <DialogHeader class="border-b border-gray-800 pb-4">
          <DialogTitle class="text-xl flex items-center justify-between">
            <span>Request Details</span>
            <span class="font-mono text-base text-gray-400" v-if="selectedRequest">{{ selectedRequest.id }}</span>
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Review items and assign courier for dispatch.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedRequest" class="pt-6 space-y-6">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <Card class="bg-gray-900 border-gray-800">
              <CardContent class="p-4 space-y-3">
                <h4 class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Department Info</h4>
                <div class="flex items-center gap-3">
                   <div class="w-10 h-10 rounded-lg bg-gray-800 flex items-center justify-center">
                      <Building2 class="w-5 h-5 text-gray-400" />
                   </div>
                   <div>
                      <div class="font-medium text-white">{{ selectedRequest.department }}</div>
                      <div class="text-sm text-gray-400">{{ selectedRequest.requester }}</div>
                   </div>
                </div>
                <div class="text-sm text-gray-400 flex items-center gap-2 pt-2 border-t border-gray-800">
                  <MapPin class="w-4 h-4" /> {{ selectedRequest.location }}
                </div>
              </CardContent>
            </Card>

            <Card class="bg-gray-900 border-gray-800">
              <CardContent class="p-4 space-y-3">
                <h4 class="text-xs uppercase tracking-wider text-gray-500 font-semibold">Logistics Status</h4>
                <div class="flex justify-between items-center">
                  <span class="text-gray-400">Current Status</span>
                  <Badge :class="statusClasses[selectedRequest.status]">{{ selectedRequest.status }}</Badge>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-400">Priority</span>
                  <span :class="selectedRequest.priority === 'High' ? 'text-red-400 font-bold' : 'text-gray-300'">
                    {{ selectedRequest.priority }}
                  </span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-800">
                  <span class="text-gray-400">Courier</span>
                  <span class="text-white">{{ selectedRequest.courier || 'Unassigned' }}</span>
                </div>
              </CardContent>
            </Card>
          </div>

          <div>
            <h4 class="text-sm font-semibold text-gray-300 mb-3">Approved Items List</h4>
            <div class="rounded-md border border-gray-800 overflow-hidden">
              <Table>
                <TableHeader class="bg-gray-900">
                  <TableRow class="border-gray-800">
                    <TableHead class="text-gray-400">Item</TableHead>
                    <TableHead class="text-gray-400 text-right">Qty</TableHead>
                    <TableHead class="text-gray-400 text-right">Price</TableHead>
                    <TableHead class="text-gray-400 text-right">Total</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="(item, idx) in selectedRequest.items" :key="idx" class="border-gray-800 hover:bg-gray-900/50">
                    <TableCell class="text-gray-200">{{ item.name }}</TableCell>
                    <TableCell class="text-right text-gray-300">{{ item.quantity }}</TableCell>
                    <TableCell class="text-right text-gray-300">₱{{ item.unitPrice.toLocaleString() }}</TableCell>
                    <TableCell class="text-right text-white font-medium">₱{{ (item.quantity * item.unitPrice).toLocaleString() }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>

          <div class="flex justify-between items-center pt-4 border-t border-gray-800">
             <div class="text-lg font-bold text-white">
               Total: ₱{{ selectedRequest.totalAmount.toLocaleString() }}
             </div>
             <div class="flex gap-2">
                <Button variant="outline" @click="selectedRequest = null" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white">
                  Close
                </Button>
                <Button class="bg-blue-600 hover:bg-blue-500 text-white" @click="advanceStatus(selectedRequest)">
                  <Truck class="w-4 h-4 mr-2" />
                  Update Status
                </Button>
             </div>
          </div>

        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
  Download, 
  RefreshCw, 
  Search, 
  MapPin, 
  AlertCircle,
  Building2,
  Truck,
  ArrowRight
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

// State
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedPriority = ref('')
const selectedRequest = ref(null)

// Style Maps
const statusClasses = {
  'Approved': 'bg-amber-500/20 text-amber-300 border-amber-500/30', // Finance Approved / Pending Dispatch
  'Processing': 'bg-purple-500/20 text-purple-300 border-purple-500/30',
  'In Transit': 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30',
  'Delivered': 'bg-green-500/20 text-green-300 border-green-500/30'
}

// Mock Data: Operational Distributor Manager View
const requests = ref([
  {
    id: 'PR-2024-881',
    department: 'IT Infrastructure',
    requester: 'Alice Guo',
    date: '2026-02-15',
    location: 'Makati HQ, 5th Floor',
    status: 'Approved',
    priority: 'High',
    totalAmount: 450000,
    courier: null,
    items: [
      { name: 'High-Perf Server Racks', quantity: 2, unitPrice: 150000 },
      { name: 'Cat6 Cables (Box)', quantity: 10, unitPrice: 15000 }
    ]
  },
  {
    id: 'PR-2024-882',
    department: 'Human Resources',
    requester: 'Ben Manaloto',
    date: '2026-02-14',
    location: 'BGC Office, Tower 1',
    status: 'Processing',
    priority: 'Normal',
    totalAmount: 25000,
    courier: 'Lalamove',
    items: [
      { name: 'Ergonomic Chairs', quantity: 5, unitPrice: 5000 }
    ]
  },
  {
    id: 'PR-2024-885',
    department: 'Operations',
    requester: 'Sarah Lee',
    date: '2026-02-12',
    location: 'Cavite Warehouse',
    status: 'In Transit',
    priority: 'Normal',
    totalAmount: 120000,
    courier: 'Transportify',
    items: [
       { name: 'Industrial Fans', quantity: 10, unitPrice: 12000 }
    ]
  },
  {
    id: 'PR-2024-890',
    department: 'Marketing',
    requester: 'Gary Val',
    date: '2026-02-10',
    location: 'Makati HQ, 3rd Floor',
    status: 'Delivered',
    priority: 'Low',
    totalAmount: 15000,
    courier: 'In-house Driver',
    items: [
       { name: 'Event Banners', quantity: 3, unitPrice: 5000 }
    ]
  },
  {
    id: 'PR-2024-892',
    department: 'Finance',
    requester: 'Karen Davila',
    date: '2026-02-16',
    location: 'Makati HQ, 8th Floor',
    status: 'Approved',
    priority: 'High',
    totalAmount: 85000,
    courier: null,
    items: [
       { name: 'Safe Vault', quantity: 1, unitPrice: 85000 }
    ]
  }
])

// Computed Logic
const filteredRequests = computed(() => {
  return requests.value.filter(req => {
    // Search
    const searchLower = searchQuery.value.toLowerCase()
    const matchSearch = 
      req.id.toLowerCase().includes(searchLower) || 
      req.department.toLowerCase().includes(searchLower) ||
      req.requester.toLowerCase().includes(searchLower)

    // Status Filter
    const matchStatus = 
      !selectedStatus.value || 
      selectedStatus.value === 'all' || 
      req.status === selectedStatus.value

    // Priority Filter
    const matchPriority = 
      !selectedPriority.value || 
      selectedPriority.value === 'all' || 
      req.priority === selectedPriority.value

    return matchSearch && matchStatus && matchPriority
  })
})

// Actions
const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = 'all'
  selectedPriority.value = 'all'
}

const viewDetails = (req) => {
  selectedRequest.value = req
}

const advanceStatus = (req) => {
  const flow = ['Approved', 'Processing', 'In Transit', 'Delivered']
  const idx = flow.indexOf(req.status)
  if (idx < flow.length - 1) {
    req.status = flow[idx + 1]
    // If opening via dialog, sync update is automatic since it references the object
    if(req.status === 'Processing' && !req.courier) {
      req.courier = 'Pending Assignment' // Auto assign logic placeholder
    }
  }
}
</script>

<style scoped>
/* Scoped overrides for specific shadcn components if tailwind utility isn't enough */
.dialog-content {
  background-color: #030712; /* gray-950 */
}
</style>