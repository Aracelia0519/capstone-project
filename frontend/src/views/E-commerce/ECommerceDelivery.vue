<!-- ECommerceDelivery.vue -->
<template>
  <div class="ecommerce-delivery p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Delivery & Logistics</h1>
          <p class="text-gray-300">Handle order shipping and service scheduling</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <button @click="showScheduleModal = true" 
                  class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Schedule Delivery
          </button>
        </div>
      </div>
    </div>

    <!-- Delivery Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ deliveries.length }}</div>
        <div class="text-sm text-gray-300">Total Deliveries</div>
      </div>
      <div class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ pendingDeliveries }}</div>
        <div class="text-sm text-gray-300">Pending</div>
      </div>
      <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ inTransitDeliveries }}</div>
        <div class="text-sm text-gray-300">In Transit</div>
      </div>
      <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ deliveredOrders }}</div>
        <div class="text-sm text-gray-300">Delivered</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm text-gray-300 mb-2">Search</label>
          <input type="text" v-model="searchQuery" placeholder="Search by order ID or address..." 
                 class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500">
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Status</label>
          <select v-model="selectedStatus" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Status</option>
            <option value="Scheduled">Scheduled</option>
            <option value="Processing">Processing</option>
            <option value="In Transit">In Transit</option>
            <option value="Out for Delivery">Out for Delivery</option>
            <option value="Delivered">Delivered</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Courier</label>
          <select v-model="selectedCourier" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Couriers</option>
            <option value="LBC">LBC</option>
            <option value="J&T Express">J&T Express</option>
            <option value="Grab Express">Grab Express</option>
            <option value="In-house">In-house</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-white transition-colors">
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Deliveries Table -->
    <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-900/80">
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Order ID</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Delivery Address</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Courier/Provider</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Est. Delivery</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="delivery in filteredDeliveries" :key="delivery.id" 
                class="border-t border-gray-800 hover:bg-white/5">
              <td class="py-4 px-6">
                <div class="font-mono text-white font-medium">{{ delivery.orderId }}</div>
                <div class="text-sm text-gray-400">{{ delivery.client }}</div>
              </td>
              <td class="py-4 px-6">
                <div class="text-gray-300">{{ delivery.address }}</div>
                <div class="text-sm text-gray-400">{{ delivery.city }}</div>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  statusClasses[delivery.status]
                ]">
                  {{ delivery.status }}
                </span>
                <div v-if="delivery.status === 'In Transit'" class="mt-2">
                  <div class="w-full bg-gray-700 h-1 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500" style="width: 70%"></div>
                  </div>
                  <div class="text-xs text-gray-400 mt-1">70% completed</div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs mr-3">
                    {{ delivery.courier.charAt(0) }}
                  </div>
                  <div>
                    <div class="text-white">{{ delivery.courier }}</div>
                    <div class="text-sm text-gray-400">{{ delivery.contact }}</div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="text-white">{{ delivery.estimatedDate }}</div>
                <div class="text-sm" :class="{
                  'text-green-400': delivery.estimatedDate >= '2024-03-18',
                  'text-amber-400': delivery.estimatedDate === '2024-03-17',
                  'text-red-400': delivery.estimatedDate < '2024-03-17'
                }">
                  {{ delivery.estimatedTime }}
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex space-x-2">
                  <button @click="updateStatus(delivery)" 
                         class="px-3 py-1 bg-gray-800 text-gray-300 border border-gray-700 rounded-lg hover:bg-gray-700 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Update
                  </button>
                  <button @click="uploadProof(delivery)" 
                         class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-lg hover:bg-blue-500/30 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Proof
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Service Schedule Section -->
    <div class="mt-8">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white">Service Schedule</h3>
        <button @click="showServiceSchedule = true" 
                class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:opacity-90 transition-opacity text-sm">
          View Calendar
        </button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="service in serviceSchedules" :key="service.id" 
             class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6 hover:border-gray-700 transition-colors">
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
            <span :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              service.status === 'Scheduled' ? 'bg-blue-500/20 text-blue-300' :
              service.status === 'In Progress' ? 'bg-amber-500/20 text-amber-300' :
              'bg-green-500/20 text-green-300'
            ]">
              {{ service.status }}
            </span>
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
          
          <div class="mt-6 pt-6 border-t border-gray-800">
            <div class="flex justify-between">
              <button @click="rescheduleService(service)" 
                     class="px-4 py-2 text-sm border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
                Reschedule
              </button>
              <button @click="completeService(service)" 
                     class="px-4 py-2 text-sm bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                Complete
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Schedule Delivery Modal -->
    <div v-if="showScheduleModal" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Schedule Delivery</h3>
            <button @click="showScheduleModal = false" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <form @submit.prevent="scheduleDelivery" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm text-gray-300 mb-2">Order ID *</label>
                <select v-model="deliveryForm.orderId" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="">Select Order</option>
                  <option value="ORD-2024-007">ORD-2024-007 - Juan Dela Cruz</option>
                  <option value="ORD-2024-008">ORD-2024-008 - Maria Santos</option>
                  <option value="ORD-2024-009">ORD-2024-009 - Pedro Reyes</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Delivery Date *</label>
                <input type="date" v-model="deliveryForm.deliveryDate" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Estimated Time *</label>
                <select v-model="deliveryForm.deliveryTime" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="09:00-12:00">09:00 AM - 12:00 PM</option>
                  <option value="13:00-16:00">01:00 PM - 04:00 PM</option>
                  <option value="16:00-19:00">04:00 PM - 07:00 PM</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Assign Courier *</label>
                <select v-model="deliveryForm.courier" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="LBC">LBC Express</option>
                  <option value="J&T Express">J&T Express</option>
                  <option value="Grab Express">Grab Express</option>
                  <option value="In-house">In-house Delivery</option>
                </select>
              </div>
              
              <div class="md:col-span-2">
                <label class="block text-sm text-gray-300 mb-2">Delivery Instructions</label>
                <textarea v-model="deliveryForm.instructions" rows="3" 
                         class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
              <button type="button" @click="showScheduleModal = false" 
                     class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
                Cancel
              </button>
              <button type="submit" 
                     class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                Schedule Delivery
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Service Calendar Modal -->
    <div v-if="showServiceSchedule" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-4xl">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Service Schedule Calendar</h3>
            <button @click="showServiceSchedule = false" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div class="bg-gray-800/50 rounded-xl p-4 mb-6">
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
          </div>
          
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
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
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
    
    const matchesStatus = selectedStatus.value === '' || 
      delivery.status === selectedStatus.value
    
    const matchesCourier = selectedCourier.value === '' || 
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
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-delivery {
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>