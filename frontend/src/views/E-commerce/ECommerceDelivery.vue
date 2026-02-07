<template>
  <div class="ecommerce-delivery p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Delivery & Logistics</h1>
          <p class="text-gray-300">Handle order shipping and service scheduling</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <Button @click="showScheduleModal = true" 
                  class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0 hover:opacity-90">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Schedule Delivery
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ deliveries.length }}</div>
          <div class="text-sm text-gray-300">Total Deliveries</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ pendingDeliveries }}</div>
          <div class="text-sm text-gray-300">Pending</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ inTransitDeliveries }}</div>
          <div class="text-sm text-gray-300">In Transit</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ deliveredOrders }}</div>
          <div class="text-sm text-gray-300">Delivered</div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 bg-gray-900/50 border-gray-800">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="md:col-span-2 space-y-2">
            <Label class="text-gray-300">Search</Label>
            <Input type="text" v-model="searchQuery" placeholder="Search by order ID or address..." 
                   class="bg-gray-800 border-gray-700 text-white placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Status</Label>
            <Select v-model="selectedStatus">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all_status_placeholder">All Status</SelectItem> <SelectItem value="Scheduled">Scheduled</SelectItem>
                <SelectItem value="Processing">Processing</SelectItem>
                <SelectItem value="In Transit">In Transit</SelectItem>
                <SelectItem value="Out for Delivery">Out for Delivery</SelectItem>
                <SelectItem value="Delivered">Delivered</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Courier</Label>
            <Select v-model="selectedCourier">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="All Couriers" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all_courier_placeholder">All Couriers</SelectItem>
                <SelectItem value="LBC">LBC</SelectItem>
                <SelectItem value="J&T Express">J&T Express</SelectItem>
                <SelectItem value="Grab Express">Grab Express</SelectItem>
                <SelectItem value="In-house">In-house</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-end">
            <Button @click="resetFilters" variant="outline" class="w-full bg-gray-800 border-gray-700 text-white hover:bg-gray-700 hover:text-white">
              Reset
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-900/80">
            <TableRow class="hover:bg-transparent border-gray-800">
              <TableHead class="text-gray-300">Order ID</TableHead>
              <TableHead class="text-gray-300">Delivery Address</TableHead>
              <TableHead class="text-gray-300">Status</TableHead>
              <TableHead class="text-gray-300">Courier/Provider</TableHead>
              <TableHead class="text-gray-300">Est. Delivery</TableHead>
              <TableHead class="text-gray-300">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="delivery in filteredDeliveries" :key="delivery.id" 
                class="border-gray-800 hover:bg-white/5">
              <TableCell>
                <div class="font-mono text-white font-medium">{{ delivery.orderId }}</div>
                <div class="text-sm text-gray-400">{{ delivery.client }}</div>
              </TableCell>
              <TableCell>
                <div class="text-gray-300">{{ delivery.address }}</div>
                <div class="text-sm text-gray-400">{{ delivery.city }}</div>
              </TableCell>
              <TableCell>
                <Badge :class="[
                  'rounded-full border-0 font-medium',
                  statusClasses[delivery.status]
                ]">
                  {{ delivery.status }}
                </Badge>
                <div v-if="delivery.status === 'In Transit'" class="mt-2 w-24">
                  <Progress :model-value="70" class="h-1 bg-gray-700" indicator-class="bg-blue-500" />
                  <div class="text-xs text-gray-400 mt-1">70% completed</div>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs mr-3">
                    {{ delivery.courier.charAt(0) }}
                  </div>
                  <div>
                    <div class="text-white">{{ delivery.courier }}</div>
                    <div class="text-sm text-gray-400">{{ delivery.contact }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <div class="text-white">{{ delivery.estimatedDate }}</div>
                <div class="text-sm" :class="{
                  'text-green-400': delivery.estimatedDate >= '2024-03-18',
                  'text-amber-400': delivery.estimatedDate === '2024-03-17',
                  'text-red-400': delivery.estimatedDate < '2024-03-17'
                }">
                  {{ delivery.estimatedTime }}
                </div>
              </TableCell>
              <TableCell>
                <div class="flex space-x-2">
                  <Button size="sm" variant="outline" @click="updateStatus(delivery)" 
                         class="bg-gray-800 text-gray-300 border-gray-700 hover:bg-gray-700 hover:text-white">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Update
                  </Button>
                  <Button size="sm" variant="ghost" @click="uploadProof(delivery)" 
                         class="bg-blue-500/20 text-blue-300 hover:bg-blue-500/30 hover:text-blue-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Proof
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <div class="mt-8">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white">Service Schedule</h3>
        <Button @click="showServiceSchedule = true" 
                class="bg-gradient-to-r from-purple-500 to-pink-500 text-white border-0 hover:opacity-90">
          View Calendar
        </Button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card v-for="service in serviceSchedules" :key="service.id" 
             class="bg-gray-900/50 backdrop-blur-sm border-gray-800 hover:border-gray-700 transition-colors text-white">
          <CardContent class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div>
                <h4 class="text-lg font-semibold text-white mb-2">{{ service.serviceType }}</h4>
                <div class="flex items-center text-sm text-gray-400 mb-1">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  {{ service.client }}
                </div>
                <div class="flex items-center text-sm text-gray-400">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  {{ service.address }}
                </div>
              </div>
              <Badge :class="[
                'rounded-full border-0',
                service.status === 'Scheduled' ? 'bg-blue-500/20 text-blue-300' :
                service.status === 'In Progress' ? 'bg-amber-500/20 text-amber-300' :
                'bg-green-500/20 text-green-300'
              ]">
                {{ service.status }}
              </Badge>
            </div>
            
            <div class="space-y-3">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-400">Schedule Date:</span>
                <span class="text-white font-medium">{{ service.scheduleDate }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-400">Time:</span>
                <span class="text-white font-medium">{{ service.scheduleTime }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-400">Service Provider:</span>
                <div class="flex items-center">
                  <div class="w-6 h-6 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center text-white text-xs mr-2">
                    {{ service.provider.charAt(0) }}
                  </div>
                  <span class="text-white">{{ service.provider }}</span>
                </div>
              </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-800 flex justify-between">
              <Button size="sm" variant="outline" @click="rescheduleService(service)" 
                     class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
                Reschedule
              </Button>
              <Button size="sm" @click="completeService(service)" 
                     class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-0 hover:opacity-90">
                Complete
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <Dialog v-model:open="showScheduleModal">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle>Schedule Delivery</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="scheduleDelivery" class="space-y-6 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label class="text-gray-300">Order ID *</Label>
              <Select v-model="deliveryForm.orderId">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select Order" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="ORD-2024-007">ORD-2024-007 - Juan Dela Cruz</SelectItem>
                  <SelectItem value="ORD-2024-008">ORD-2024-008 - Maria Santos</SelectItem>
                  <SelectItem value="ORD-2024-009">ORD-2024-009 - Pedro Reyes</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Delivery Date *</Label>
              <Input type="date" v-model="deliveryForm.deliveryDate" required 
                     class="bg-gray-800 border-gray-700 text-white" />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Estimated Time *</Label>
              <Select v-model="deliveryForm.deliveryTime">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select Time" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="09:00-12:00">09:00 AM - 12:00 PM</SelectItem>
                  <SelectItem value="13:00-16:00">01:00 PM - 04:00 PM</SelectItem>
                  <SelectItem value="16:00-19:00">04:00 PM - 07:00 PM</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Assign Courier *</Label>
              <Select v-model="deliveryForm.courier">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select Courier" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="LBC">LBC Express</SelectItem>
                  <SelectItem value="J&T Express">J&T Express</SelectItem>
                  <SelectItem value="Grab Express">Grab Express</SelectItem>
                  <SelectItem value="In-house">In-house Delivery</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="md:col-span-2 space-y-2">
              <Label class="text-gray-300">Delivery Instructions</Label>
              <Textarea v-model="deliveryForm.instructions" rows="3" 
                       class="bg-gray-800 border-gray-700 text-white" />
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
            <Button type="button" variant="outline" @click="showScheduleModal = false" 
                   class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
              Cancel
            </Button>
            <Button type="submit" 
                   class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0 hover:opacity-90">
              Schedule Delivery
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showServiceSchedule">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-4xl">
        <DialogHeader>
          <DialogTitle>Service Schedule Calendar</DialogTitle>
        </DialogHeader>
        
        <div class="pt-4">
          <Card class="bg-gray-800/50 border-0 mb-6 text-white">
            <CardContent class="p-4">
              <div class="grid grid-cols-7 gap-2 mb-4">
                <div class="text-center text-sm text-gray-400 p-2">Sun</div>
                <div class="text-center text-sm text-gray-400 p-2">Mon</div>
                <div class="text-center text-sm text-gray-400 p-2">Tue</div>
                <div class="text-center text-sm text-gray-400 p-2">Wed</div>
                <div class="text-center text-sm text-gray-400 p-2">Thu</div>
                <div class="text-center text-sm text-gray-400 p-2">Fri</div>
                <div class="text-center text-sm text-gray-400 p-2">Sat</div>
                
                <div v-for="day in calendarDays" :key="day.date" 
                     class="text-center p-3 rounded-lg hover:bg-gray-700/50 cursor-pointer transition-colors"
                     :class="{
                       'bg-blue-500/20': day.hasService,
                       'bg-gray-900': !day.hasService
                     }">
                  <div class="text-white mb-1">{{ day.day }}</div>
                  <div v-if="day.hasService" class="text-xs text-blue-300">
                    {{ day.serviceCount }} service{{ day.serviceCount > 1 ? 's' : '' }}
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <div class="space-y-4">
            <h4 class="text-lg font-semibold text-white mb-4">Today's Services</h4>
            <div v-for="service in todaysServices" :key="service.id" 
                 class="bg-gray-800/50 rounded-xl p-4 border border-gray-700">
              <div class="flex items-center justify-between">
                <div>
                  <h5 class="text-white font-medium">{{ service.serviceType }}</h5>
                  <p class="text-sm text-gray-400">{{ service.client }}</p>
                </div>
                <div class="text-right">
                  <div class="text-white">{{ service.scheduleTime }}</div>
                  <div class="text-sm text-gray-400">{{ service.provider }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Textarea } from '@/components/ui/textarea'
import { Progress } from '@/components/ui/progress'

const searchQuery = ref('')
// Use undefined or specific placeholder for select triggers in Shadcn if needing 'all' logic
const selectedStatus = ref('') 
const selectedCourier = ref('')
const showScheduleModal = ref(false)
const showServiceSchedule = ref(false)

const statusClasses = {
  'Scheduled': 'bg-blue-500/20 text-blue-300',
  'Processing': 'bg-amber-500/20 text-amber-300',
  'In Transit': 'bg-purple-500/20 text-purple-300',
  'Out for Delivery': 'bg-indigo-500/20 text-indigo-300',
  'Delivered': 'bg-green-500/20 text-green-300'
}

const deliveries = ref([
  { 
    id: 1, 
    orderId: 'ORD-2024-001', 
    client: 'Juan Dela Cruz',
    address: '123 Main St', 
    city: 'Manila',
    status: 'In Transit', 
    courier: 'LBC',
    contact: '0917-123-4567',
    estimatedDate: '2024-03-17',
    estimatedTime: '13:00-16:00'
  },
  { 
    id: 2, 
    orderId: 'ORD-2024-002', 
    client: 'Maria Santos',
    address: '456 Oak Ave', 
    city: 'Quezon City',
    status: 'Out for Delivery', 
    courier: 'J&T Express',
    contact: '0922-888-9999',
    estimatedDate: '2024-03-17',
    estimatedTime: '09:00-12:00'
  },
  { 
    id: 3, 
    orderId: 'ORD-2024-003', 
    client: 'Pedro Reyes',
    address: '789 Pine St', 
    city: 'Makati',
    status: 'Scheduled', 
    courier: 'Grab Express',
    contact: '0916-111-2222',
    estimatedDate: '2024-03-18',
    estimatedTime: '16:00-19:00'
  },
  { 
    id: 4, 
    orderId: 'ORD-2024-004', 
    client: 'Ana Lopez',
    address: '321 Elm St', 
    city: 'Taguig',
    status: 'Delivered', 
    courier: 'In-house',
    contact: '0933-444-5555',
    estimatedDate: '2024-03-16',
    estimatedTime: '14:30'
  }
])

const serviceSchedules = ref([
  { 
    id: 1, 
    serviceType: 'Wall Painting Service', 
    client: 'Juan Dela Cruz',
    address: '123 Main St, Manila',
    status: 'Scheduled',
    scheduleDate: '2024-03-20',
    scheduleTime: '09:00 AM',
    provider: 'PaintPro Services'
  },
  { 
    id: 2, 
    serviceType: 'Ceiling Painting', 
    client: 'Maria Santos',
    address: '456 Oak Ave, QC',
    status: 'In Progress',
    scheduleDate: '2024-03-18',
    scheduleTime: '10:00 AM',
    provider: 'Perfect Painters'
  },
  { 
    id: 3, 
    serviceType: 'Exterior Painting', 
    client: 'Pedro Reyes',
    address: '789 Pine St, Makati',
    status: 'Completed',
    scheduleDate: '2024-03-15',
    scheduleTime: '13:00 PM',
    provider: 'Elite Painting Co.'
  }
])

const deliveryForm = ref({
  orderId: '',
  deliveryDate: '',
  deliveryTime: '09:00-12:00',
  courier: 'LBC',
  instructions: ''
})

const pendingDeliveries = computed(() => {
  return deliveries.value.filter(d => d.status === 'Scheduled' || d.status === 'Processing').length
})

const inTransitDeliveries = computed(() => {
  return deliveries.value.filter(d => d.status === 'In Transit' || d.status === 'Out for Delivery').length
})

const deliveredOrders = computed(() => {
  return deliveries.value.filter(d => d.status === 'Delivered').length
})

const filteredDeliveries = computed(() => {
  return deliveries.value.filter(delivery => {
    const matchesSearch = searchQuery.value === '' || 
      delivery.orderId.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      delivery.address.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      delivery.client.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    // Check against placeholder values from Shadcn Select
    const matchesStatus = selectedStatus.value === '' || selectedStatus.value === 'all_status_placeholder' ||
      delivery.status === selectedStatus.value
    
    const matchesCourier = selectedCourier.value === '' || selectedCourier.value === 'all_courier_placeholder' ||
      delivery.courier === selectedCourier.value
    
    return matchesSearch && matchesStatus && matchesCourier
  })
})

const calendarDays = ref([
  { day: 1, date: '2024-03-01', hasService: false },
  { day: 2, date: '2024-03-02', hasService: false },
  { day: 3, date: '2024-03-03', hasService: false },
  { day: 4, date: '2024-03-04', hasService: true, serviceCount: 1 },
  { day: 5, date: '2024-03-05', hasService: false },
  { day: 6, date: '2024-03-06', hasService: false },
  { day: 7, date: '2024-03-07', hasService: false },
  { day: 8, date: '2024-03-08', hasService: false },
  { day: 9, date: '2024-03-09', hasService: false },
  { day: 10, date: '2024-03-10', hasService: false },
  { day: 11, date: '2024-03-11', hasService: true, serviceCount: 2 },
  { day: 12, date: '2024-03-12', hasService: false },
  { day: 13, date: '2024-03-13', hasService: true, serviceCount: 1 },
  { day: 14, date: '2024-03-14', hasService: false },
  { day: 15, date: '2024-03-15', hasService: true, serviceCount: 1 },
  { day: 16, date: '2024-03-16', hasService: false },
  { day: 17, date: '2024-03-17', hasService: false },
  { day: 18, date: '2024-03-18', hasService: true, serviceCount: 1 },
  { day: 19, date: '2024-03-19', hasService: false },
  { day: 20, date: '2024-03-20', hasService: true, serviceCount: 1 },
  { day: 21, date: '2024-03-21', hasService: false },
  { day: 22, date: '2024-03-22', hasService: false },
  { day: 23, date: '2024-03-23', hasService: false },
  { day: 24, date: '2024-03-24', hasService: false },
  { day: 25, date: '2024-03-25', hasService: false },
  { day: 26, date: '2024-03-26', hasService: false },
  { day: 27, date: '2024-03-27', hasService: false },
  { day: 28, date: '2024-03-28', hasService: false },
  { day: 29, date: '2024-03-29', hasService: false },
  { day: 30, date: '2024-03-30', hasService: false },
  { day: 31, date: '2024-03-31', hasService: false }
])

const todaysServices = computed(() => {
  const today = '2024-03-18'
  return serviceSchedules.value.filter(service => service.scheduleDate === today)
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  selectedCourier.value = ''
}

const updateStatus = (delivery) => {
  const statuses = ['Scheduled', 'Processing', 'In Transit', 'Out for Delivery', 'Delivered']
  const currentIndex = statuses.indexOf(delivery.status)
  const nextIndex = (currentIndex + 1) % statuses.length
  delivery.status = statuses[nextIndex]
}

const uploadProof = (delivery) => {
  alert(`Upload proof of delivery for ${delivery.orderId}`)
}

const scheduleDelivery = () => {
  const newId = Math.max(...deliveries.value.map(d => d.id)) + 1
  deliveries.value.push({
    id: newId,
    orderId: deliveryForm.value.orderId,
    client: 'New Client',
    address: 'New Address',
    city: 'City',
    status: 'Scheduled',
    courier: deliveryForm.value.courier,
    contact: '000-000-0000',
    estimatedDate: deliveryForm.value.deliveryDate,
    estimatedTime: deliveryForm.value.deliveryTime
  })
  
  showScheduleModal.value = false
  deliveryForm.value = {
    orderId: '',
    deliveryDate: '',
    deliveryTime: '09:00-12:00',
    courier: 'LBC',
    instructions: ''
  }
}

const rescheduleService = (service) => {
  const newDate = prompt('Enter new schedule date (YYYY-MM-DD):', service.scheduleDate)
  const newTime = prompt('Enter new schedule time:', service.scheduleTime)
  
  if (newDate && newTime) {
    service.scheduleDate = newDate
    service.scheduleTime = newTime
  }
}

const completeService = (service) => {
  service.status = 'Completed'
}
</script>

<style scoped>
.ecommerce-delivery {
  min-height: 100vh;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-delivery {
    padding: 1rem;
  }
}
</style>