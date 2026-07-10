<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Toaster, toast } from 'vue-sonner' 
import api from '@/utils/axios' 
import echo from '@/utils/websocket'
import { 
  Check, 
  Clock, 
  Truck, 
  Package, 
  FileText, 
  User, 
  AlertCircle,
  RefreshCw,
  Menu,
  MapPin,
  Upload,
  X,
  Loader2,
  Image as ImageIcon,
  Play
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Label } from '@/components/ui/label'
import {
  Card,
  CardContent,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Sheet,
  SheetContent,
  SheetTrigger,
} from '@/components/ui/sheet'
import {
  Tabs,
  TabsContent,
  TabsList,
  TabsTrigger,
} from '@/components/ui/tabs'

// --- Interfaces ---
interface OrderItem {
  id: number
  name: string
  category: string
  quantity: number
  unit_price: number
  total: number
  weight: number
}

interface PreparedOrder {
  id: number
  order_type: string
  order_number: string
  status: string
  order_date: string
  total_amount: number
  shipping_fee: number
  grand_total: number
  payment_method: string
  delivery_address: string
  client_name: string
  client_phone: string
  items: OrderItem[]
  rejection_reason?: string
}

interface ShipmentOrder {
  id: number
  delivery_id: number
  order_type: string
  order_number: string
  status: string
  customer: string
  items: string
  is_ready_to_go: boolean
  vehicle_name: string
  shipped_at: string
  updated_at: string
  delivery_status: string
}

interface DeliveryMan {
  id: number
  name: string
}

interface Vehicle {
  id: number
  name: string
  capacity: number
  max_capacity: number
}

// --- State ---
const deliveryPersonnel = ref<DeliveryMan[]>([])
const vehicles = ref<Vehicle[]>([])
const preparedOrders = ref<PreparedOrder[]>([])
const shippedOrders = ref<ShipmentOrder[]>([])
const pendingReadyOrders = ref<ShipmentOrder[]>([])
const selectedOrderId = ref<number | null>(null)
const isLoading = ref(false)
const showMobileSheet = ref(false)
const isSubmitting = ref(false)

// Permissions
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// Form State
const selectedDeliveryMan = ref<string>('')
const selectedVehicleId = ref<string>('')
const proofFile = ref<File | null>(null)
const proofPreview = ref<string | null>(null)

// WebSocket
const activeDistributorId = ref<number | null>(null)
const isAdminUser = ref(false)

// Computed
const selectedOrder = computed(() => 
  preparedOrders.value.find(o => o.id === selectedOrderId.value)
)

const isPickUp = computed(() => {
  const method = selectedOrder.value?.payment_method?.toLowerCase() || ''
  return method === 'pick-up' || method === 'pickup'
})

const selectedOrderTotalWeight = computed(() => {
  if (!selectedOrder.value) return 0
  return selectedOrder.value.items.reduce((sum, item) => {
    const weight = item.weight ?? 10.00
    return sum + (item.quantity * weight)
  }, 0)
})

const isOverweight = computed(() => {
  if (!selectedVehicleId.value) return false
  const vehicle = vehicles.value.find(v => v.id === Number(selectedVehicleId.value))
  if (!vehicle) return false
  return selectedOrderTotalWeight.value > vehicle.capacity
})

const canSubmit = computed(() => {
  if (isPickUp.value) {
    return proofFile.value !== null && selectedVehicleId.value !== '' && !isOverweight.value
  }
  return selectedDeliveryMan.value !== '' && proofFile.value !== null && selectedVehicleId.value !== '' && !isOverweight.value
})

// Lifecycle
onMounted(() => {
  fetchData()
})

onUnmounted(() => {
  if (isAdminUser.value) {
    echo.leave(`admin.orders`)
  } else if (activeDistributorId.value) {
    echo.leave(`distributor.${activeDistributorId.value}.orders`)
  }
})

// --- API ---
const fetchData = async (isBackground = false) => {
  if (!isBackground) isLoading.value = true
  try {
    const res = await api.get('/operation-distributor/prepare-orders')
    if (res.data.success) {
      preparedOrders.value = res.data.prepared_orders
      shippedOrders.value = res.data.shipped_orders
      pendingReadyOrders.value = res.data.pending_ready_orders
      deliveryPersonnel.value = res.data.delivery_personnel
      vehicles.value = res.data.vehicles
      permissions.value = res.data.permissions
      activeDistributorId.value = res.data.distributor_id
      isAdminUser.value = res.data.is_admin

      if (!isBackground) setupWebSocket()
    } else {
      preparedOrders.value = res.data
    }
    if (!selectedOrderId.value && preparedOrders.value.length > 0) {
      selectedOrderId.value = preparedOrders.value[0].id
    } else if (preparedOrders.value.length === 0) {
      selectedOrderId.value = null
    }
  } catch (error: any) {
    console.error(error)
    if (!isBackground) toast.error('Failed to fetch orders')
  } finally {
    if (!isBackground) isLoading.value = false
  }
}

const setupWebSocket = () => {
  const channel = isAdminUser.value ? 'admin.orders' : `distributor.${activeDistributorId.value}.orders`
  echo.private(channel)
    .listen('.order.placed', () => {
      fetchData(true)
      toast.success('New Order Received!')
    })
    .listen('.delivery.updated', () => {
      fetchData(true)
      toast.info('Delivery Assignment Updated')
    })
}

const signalReadyToGo = async (deliveryId: number) => {
  try {
    await api.post(`/operation-distributor/prepare-orders/${deliveryId}/ready`)
    toast.success('Ready signal dispatched to driver!')
    await fetchData(true)
  } catch (error) {
    toast.error('Failed to signal ready.')
  }
}

const handleProofUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    proofFile.value = target.files[0]
    proofPreview.value = URL.createObjectURL(target.files[0])
  }
}

const removeProof = () => {
  proofFile.value = null
  if (proofPreview.value) {
    URL.revokeObjectURL(proofPreview.value)
    proofPreview.value = null
  }
}

const submitPreparation = async () => {
  if (!canSubmit.value || !selectedOrder.value) return
  
  isSubmitting.value = true
  const formData = new FormData()
  formData.append('order_type', selectedOrder.value.order_type)
  if (!isPickUp.value) {
    formData.append('delivery_personnel_id', selectedDeliveryMan.value)
  }
  formData.append('vehicle_id', selectedVehicleId.value)
  if (proofFile.value) {
    formData.append('proof_file', proofFile.value)
  }

  try {
    await api.post(`/operation-distributor/prepare-orders/${selectedOrder.value.id}/dispatch`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    toast.success(`Order ${selectedOrder.value?.order_number} assigned to vehicle.`)
    selectedDeliveryMan.value = ''
    selectedVehicleId.value = ''
    removeProof()
    await fetchData()
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to dispatch.')
  } finally {
    isSubmitting.value = false
  }
}

// Helpers
const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const requirePermission = (action: string, callback: Function) => {
  const permKey = `can_${action}` as keyof typeof permissions.value
  if (!permissions.value[permKey]) {
    toast.error(`Access Denied`, { description: `You do not have permission to ${action}.` })
    return
  }
  if (callback) callback()
}

const selectOrderAndCloseSheet = (orderId: number) => {
  selectedOrderId.value = orderId
  showMobileSheet.value = false
  selectedDeliveryMan.value = ''
  selectedVehicleId.value = ''
  removeProof()
}
</script>

<template>
  <div class="flex h-full w-full flex-col text-gray-100 relative">
    <!-- Desktop View -->
    <div class="hidden md:flex h-full w-full overflow-hidden">
      <!-- Left Sidebar -->
      <div class="w-80 lg:w-96 border-r border-gray-800 flex flex-col h-full">
        <div class="p-4 border-b border-gray-800 flex items-center justify-between sticky top-0 z-10 backdrop-blur-sm">
          <h2 class="font-semibold text-lg flex items-center gap-2 text-white">
            <Package class="h-5 w-5 text-blue-400" /> Prepare Orders
            <Badge v-if="preparedOrders.length > 0" class="ml-2 bg-blue-500/20 text-blue-400 border-0">
              {{ preparedOrders.length }}
            </Badge>
          </h2>
          <Button variant="ghost" size="icon" @click="fetchData" :disabled="isLoading" class="text-gray-400 hover:text-white hover:bg-gray-800/50">
            <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
          </Button>
        </div>

        <ScrollArea class="flex-1">
          <div class="flex flex-col gap-2 p-4">
            <div v-if="preparedOrders.length === 0 && !isLoading" class="text-center text-gray-500 py-10">
              <Check class="h-12 w-12 mx-auto mb-3 opacity-20 text-green-400" />
              <p class="font-medium text-gray-400">All caught up!</p>
              <p class="text-sm">No confirmed orders left to prepare.</p>
            </div>

            <button
              v-for="order in preparedOrders"
              :key="order.id"
              @click="selectOrderAndCloseSheet(order.id)"
              class="flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all"
              :class="selectedOrderId === order.id ? 'bg-gray-800/80 border-blue-500 ring-1 ring-blue-500' : 'bg-transparent border-gray-800 hover:bg-gray-800/30'"
            >
              <div class="flex w-full flex-col gap-1">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="font-semibold text-gray-100">{{ order.order_number }}</span>
                    <Badge v-if="order.order_type === 'sp'" class="text-[9px] px-1.5 py-0 h-4 bg-purple-500/20 text-purple-400 border-0">SP Order</Badge>
                    <Badge v-else class="text-[9px] px-1.5 py-0 h-4 bg-emerald-500/20 text-emerald-400 border-0">Client</Badge>
                  </div>
                  <div class="text-xs text-gray-500">{{ formatDate(order.order_date).split(',')[0] }}</div>
                </div>
                <div class="text-xs font-medium text-gray-400">{{ order.client_name }}</div>
              </div>
              <div class="flex w-full items-center justify-between gap-2">
                <Badge class="capitalize text-[10px] px-2 py-0 h-5 bg-blue-500/20 text-blue-400 border-0">
                  {{ order.status }}
                </Badge>
                <span class="font-semibold text-blue-400">{{ formatCurrency(order.grand_total) }}</span>
              </div>
            </button>
          </div>
        </ScrollArea>
      </div>

      <!-- Main Content with Tabs -->
      <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="flex items-center justify-between border-b border-gray-800 px-6 py-4">
          <div>
            <h1 class="text-xl font-bold text-white">Order Management</h1>
            <p class="text-sm text-gray-400">Prepare, assign, and monitor deliveries</p>
          </div>
        </header>

        <Tabs default-value="prepared" class="flex-1 flex flex-col overflow-hidden">
          <TabsList class="bg-transparent border-b border-gray-800 rounded-none px-6 justify-start h-auto py-1">
            <TabsTrigger value="prepared" class="data-[state=active]:bg-gray-800/50 data-[state=active]:text-white text-gray-400">
              Prepared ({{ preparedOrders.length }})
            </TabsTrigger>
            <TabsTrigger value="pending" class="data-[state=active]:bg-gray-800/50 data-[state=active]:text-white text-gray-400">
              Pending Ready ({{ pendingReadyOrders.length }})
            </TabsTrigger>
            <TabsTrigger value="history" class="data-[state=active]:bg-gray-800/50 data-[state=active]:text-white text-gray-400">
              Delivery History
            </TabsTrigger>
          </TabsList>

          <!-- Prepared Tab -->
          <TabsContent value="prepared" class="flex-1 overflow-hidden">
            <ScrollArea class="h-full p-6">
              <div v-if="selectedOrder" class="space-y-6">
                <!-- Order Details Cards -->
                <div class="grid gap-6 md:grid-cols-2">
                  <Card class="bg-gray-900/40 border-gray-800 text-white shadow-none">
                    <CardHeader class="pb-3 border-b border-gray-800/50">
                      <CardTitle class="text-md flex items-center gap-2 text-gray-200">
                        <User class="h-4 w-4 text-blue-400" /> Customer
                      </CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm space-y-3 pt-4">
                      <div class="flex justify-between">
                        <span class="text-gray-400">Name:</span>
                        <span class="font-medium text-gray-200">{{ selectedOrder.client_name }}</span>
                      </div>
                      <div class="flex justify-between">
                        <span class="text-gray-400">Contact:</span>
                        <span class="text-gray-200">{{ selectedOrder.client_phone }}</span>
                      </div>
                      <Separator class="my-3 bg-gray-800" />
                      <div class="flex justify-between items-center">
                        <span class="text-gray-400">Payment:</span>
                        <Badge class="bg-gray-800/80 text-gray-300 border-gray-700 uppercase">{{ selectedOrder.payment_method }}</Badge>
                      </div>
                    </CardContent>
                  </Card>

                  <Card class="bg-gray-900/40 border-gray-800 text-white shadow-none">
                    <CardHeader class="pb-3 border-b border-gray-800/50">
                      <CardTitle class="text-md flex items-center gap-2 text-gray-200">
                        <MapPin class="h-4 w-4 text-blue-400" /> Delivery Address
                      </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-4">
                      <p class="text-sm text-gray-300">{{ selectedOrder.delivery_address || 'No address provided.' }}</p>
                    </CardContent>
                  </Card>
                </div>

                <!-- Items Table -->
                <Card class="bg-gray-900/40 border-gray-800 shadow-none overflow-hidden">
                  <CardHeader class="pb-3 border-b border-gray-800 bg-transparent">
                    <CardTitle class="text-md text-white">Items</CardTitle>
                  </CardHeader>
                  <CardContent class="p-0">
                    <Table>
                      <TableHeader class="bg-transparent border-b border-gray-800">
                        <TableRow class="border-gray-800 hover:bg-transparent">
                          <TableHead class="text-white">Product</TableHead>
                          <TableHead class="text-right text-white">Qty</TableHead>
                          <TableHead class="text-right text-white">Weight</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        <TableRow v-for="item in selectedOrder.items" :key="item.id" class="border-gray-800 hover:bg-gray-800/30">
                          <TableCell>
                            <div class="font-medium text-gray-200">{{ item.name }}</div>
                            <div class="text-xs text-gray-500">{{ item.category }}</div>
                          </TableCell>
                          <TableCell class="text-right font-bold text-gray-200 text-lg">{{ item.quantity }}</TableCell>
                          <TableCell class="text-right text-gray-300">{{ ((item.weight ?? 10) * item.quantity).toFixed(1) }} kg</TableCell>
                        </TableRow>
                      </TableBody>
                    </Table>
                  </CardContent>
                </Card>

                <!-- Assignment Form -->
                <Card class="bg-gray-900/40 border-gray-700 shadow-sm">
                  <CardHeader class="pb-3 border-b border-gray-800 bg-transparent">
                    <CardTitle class="text-lg flex items-center gap-2 text-white">
                      <Truck class="h-5 w-5 text-blue-400" /> Assign to Vehicle
                    </CardTitle>
                  </CardHeader>
                  <CardContent class="space-y-6 pt-6">
                    <!-- Vehicle Selection -->
                    <div class="space-y-2">
                      <Label class="text-gray-300 font-semibold">Select Vehicle <span class="text-red-400">*</span></Label>
                      <div class="relative">
                        <select 
                          v-model="selectedVehicleId" 
                          class="w-full appearance-none rounded-md border border-gray-700 bg-transparent px-4 py-3 text-sm text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                          <option value="" disabled class="bg-gray-900">Select a vehicle...</option>
                          <option v-for="v in vehicles" :key="v.id" :value="v.id" class="bg-gray-900">
                            {{ v.name }} (Available: {{ v.capacity }} kg / Max: {{ v.max_capacity }} kg)
                          </option>
                        </select>
                      </div>
                      <div v-if="selectedVehicleId && selectedOrder" class="mt-3 p-3 bg-gray-800/30 rounded-lg border border-gray-700/50">
                        <div class="flex justify-between text-sm">
                          <span class="text-gray-400">Order Total Weight:</span>
                          <span class="font-medium text-white">{{ selectedOrderTotalWeight.toFixed(1) }} kg</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                          <span class="text-gray-400">Vehicle Remaining Capacity:</span>
                          <span class="font-medium" :class="isOverweight ? 'text-red-500' : 'text-green-400'">
                            {{ vehicles.find(v => v.id === Number(selectedVehicleId))?.capacity ?? 0 }} kg
                          </span>
                        </div>
                        <div v-if="isOverweight" class="mt-2 p-2 bg-red-900/30 border border-red-700 rounded text-red-300 text-xs flex items-start gap-2">
                          <AlertCircle class="h-4 w-4 shrink-0 mt-0.5" />
                          <span>Order weight exceeds capacity. Select a larger vehicle or split.</span>
                        </div>
                      </div>
                    </div>

                    <!-- Personnel -->
                    <div v-if="!isPickUp" class="space-y-2">
                      <Label class="text-gray-300 font-semibold">Assign Delivery Personnel <span class="text-red-400">*</span></Label>
                      <select 
                        v-model="selectedDeliveryMan" 
                        class="w-full appearance-none rounded-md border border-gray-700 bg-transparent px-4 py-3 text-sm text-white focus:border-blue-500 focus:outline-none"
                      >
                        <option value="" disabled class="bg-gray-900">Select a delivery personnel...</option>
                        <option v-for="dm in deliveryPersonnel" :key="dm.id" :value="dm.id" class="bg-gray-900">
                          {{ dm.name }}
                        </option>
                      </select>
                    </div>

                    <Separator class="bg-gray-800" />

                    <!-- Proof Upload -->
                    <div class="space-y-2">
                      <Label class="text-white font-semibold">Proof of Prepared Order <span class="text-red-400">*</span></Label>
                      <div 
                        class="mt-2 border-2 border-dashed border-gray-700 rounded-lg p-6 flex flex-col items-center justify-center transition-colors relative"
                        :class="proofPreview ? 'bg-transparent' : 'bg-gray-900/20 hover:bg-gray-800/30 hover:border-gray-600'"
                      >
                        <input 
                          v-if="!proofPreview"
                          type="file" 
                          accept="image/*" 
                          class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                          @change="handleProofUpload" 
                        />
                        <div v-if="!proofPreview" class="text-center pointer-events-none">
                          <div class="rounded-full bg-gray-800/50 p-3 mx-auto w-max mb-3">
                            <ImageIcon class="h-6 w-6 text-gray-400" />
                          </div>
                          <p class="text-sm font-medium text-gray-300">Click or drag image to upload</p>
                          <p class="text-xs text-gray-500 mt-1">PNG, JPG up to 5MB</p>
                        </div>
                        <div v-else class="relative w-full flex justify-center">
                          <img :src="proofPreview" class="max-h-64 rounded-md border border-gray-700 object-contain shadow-sm" />
                          <Button 
                            size="icon" 
                            variant="destructive" 
                            class="absolute -top-3 -right-3 h-8 w-8 rounded-full shadow-md" 
                            @click.prevent.stop="removeProof"
                          >
                            <X class="h-4 w-4" />
                          </Button>
                        </div>
                      </div>
                    </div>
                  </CardContent>
                  <CardFooter class="bg-transparent p-5 border-t border-gray-800 flex justify-end">
                    <Button 
                      @click="requirePermission('manage', submitPreparation)" 
                      :disabled="!canSubmit || isSubmitting" 
                      class="w-full sm:w-auto px-8 bg-blue-600 hover:bg-blue-700 text-white transition-all"
                      size="lg"
                    >
                      <Loader2 v-if="isSubmitting" class="mr-2 h-5 w-5 animate-spin" />
                      <Truck v-else class="mr-2 h-5 w-5" />
                      {{ isSubmitting ? 'Processing...' : 'Assign to Vehicle' }}
                    </Button>
                  </CardFooter>
                </Card>
              </div>
              <div v-else class="flex h-full flex-col items-center justify-center text-gray-500 pb-20">
                <Package class="h-16 w-16 mb-4 opacity-20 text-gray-400" />
                <p class="text-lg">No orders selected</p>
                <p class="text-sm">Click an order from the list</p>
              </div>
            </ScrollArea>
          </TabsContent>

          <!-- Pending Ready Tab -->
          <TabsContent value="pending" class="flex-1 overflow-hidden">
            <ScrollArea class="h-full p-6">
              <div v-if="pendingReadyOrders.length === 0" class="text-center py-12">
                <Check class="h-12 w-12 text-green-500/50 mx-auto mb-3" />
                <p class="text-gray-400 text-lg">All shipments are ready</p>
                <p class="text-gray-500 text-sm">No shipments pending the "Ready to Go" signal.</p>
              </div>
              <div v-else class="space-y-4">
                <div v-for="order in pendingReadyOrders" :key="order.delivery_id" class="flex flex-col md:flex-row md:items-center justify-between p-4 border rounded-lg bg-yellow-50/10 border-yellow-700/50 gap-4">
                  <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-full bg-yellow-100/10 flex items-center justify-center shrink-0 border border-yellow-700/30">
                      <Truck class="h-5 w-5 text-yellow-400" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-200">{{ order.order_number }}</p>
                      <p class="text-sm text-gray-400">To: {{ order.customer }}</p>
                      <p class="text-xs text-gray-500">{{ order.items }} · {{ order.vehicle_name }}</p>
                    </div>
                  </div>
                  <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                    <Badge class="bg-yellow-500/20 text-yellow-400 border-0 uppercase text-xs">Waiting for Ready</Badge>
                    <Button 
                      size="sm" 
                      class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold h-8" 
                      @click="signalReadyToGo(order.delivery_id)"
                    >
                      <Play class="w-3.5 h-3.5 mr-1" /> Signal "Ready to Go"
                    </Button>
                  </div>
                </div>
              </div>
            </ScrollArea>
          </TabsContent>

          <!-- Delivery History Tab -->
          <TabsContent value="history" class="flex-1 overflow-hidden">
            <ScrollArea class="h-full p-6">
              <div v-if="shippedOrders.length === 0" class="text-center py-12">
                <Clock class="h-12 w-12 text-gray-500/50 mx-auto mb-3" />
                <p class="text-gray-400 text-lg">No delivery history</p>
              </div>
              <div v-else class="space-y-4">
                <div v-for="order in shippedOrders" :key="order.delivery_id" class="flex flex-col md:flex-row md:items-center justify-between p-4 border rounded-lg bg-gray-800/20 border-gray-700/50 gap-4">
                  <div class="flex items-center gap-4">
                    <div :class="[
                      'h-10 w-10 rounded-full flex items-center justify-center shrink-0',
                      order.delivery_status === 'completed' || order.delivery_status === 'delivered' ? 'bg-emerald-500/20' : 'bg-blue-500/20'
                    ]">
                      <Check v-if="order.delivery_status === 'completed' || order.delivery_status === 'delivered'" class="h-5 w-5 text-emerald-400" />
                      <Truck v-else class="h-5 w-5 text-blue-400" />
                    </div>
                    <div>
                      <p class="font-medium text-gray-200">{{ order.order_number }}</p>
                      <p class="text-sm text-gray-400">To: {{ order.customer }}</p>
                      <p class="text-xs text-gray-500">{{ order.items }} · {{ order.vehicle_name }}</p>
                    </div>
                  </div>
                  <div class="flex flex-col md:items-end gap-1">
                    <Badge :class="[
                      'border-0 uppercase text-xs',
                      order.delivery_status === 'completed' || order.delivery_status === 'delivered' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-blue-500/20 text-blue-400'
                    ]">
                      {{ order.delivery_status }}
                    </Badge>
                    <p class="text-xs text-gray-500">{{ formatDate(order.shipped_at) }}</p>
                    <div v-if="order.delivery_status === 'assigned' && !order.is_ready_to_go" class="mt-1">
                      <Button 
                        size="sm" 
                        class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold h-7 text-xs" 
                        @click="signalReadyToGo(order.delivery_id)"
                      >
                        <Play class="w-3 h-3 mr-1" /> Signal Ready
                      </Button>
                    </div>
                    <div v-else-if="order.delivery_status === 'assigned' && order.is_ready_to_go" class="text-xs text-green-400">
                      Ready signal sent
                    </div>
                  </div>
                </div>
              </div>
            </ScrollArea>
          </TabsContent>
        </Tabs>
      </div>
    </div>

    <!-- Mobile View (simplified) -->
    <div class="flex md:hidden flex-col h-full w-full">
      <header class="flex items-center justify-between border-b border-gray-800 px-4 py-3 sticky top-0 z-20 backdrop-blur-sm">
        <div class="flex items-center gap-3">
          <Sheet v-model:open="showMobileSheet">
            <SheetTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9 text-gray-300 hover:bg-gray-800/50 hover:text-white">
                <Menu class="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-[85%] sm:w-80 p-0 bg-gray-900 border-gray-800 text-white">
              <div class="flex flex-col h-full">
                <div class="p-4 border-b border-gray-800 flex items-center justify-between sticky top-0 bg-transparent z-10">
                  <h2 class="font-semibold text-lg flex items-center gap-2">
                    <Package class="h-5 w-5 text-blue-400" /> Prepare Orders
                    <Badge v-if="preparedOrders.length > 0" class="ml-2 bg-blue-500/20 text-blue-400 border-0">
                      {{ preparedOrders.length }}
                    </Badge>
                  </h2>
                </div>
                <ScrollArea class="flex-1">
                  <div class="flex flex-col gap-2 p-4">
                    <div v-if="preparedOrders.length === 0" class="text-center text-gray-500 py-10">
                      <Check class="h-12 w-12 mx-auto mb-3 opacity-20 text-green-400" />
                      <p class="font-medium">All caught up!</p>
                    </div>
                    <button
                      v-for="order in preparedOrders"
                      :key="order.id"
                      @click="selectOrderAndCloseSheet(order.id)"
                      class="w-full flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all"
                      :class="selectedOrderId === order.id ? 'bg-gray-800/80 border-blue-500 ring-1 ring-blue-500' : 'bg-transparent border-gray-800 hover:bg-gray-800/50'"
                    >
                      <div class="flex w-full flex-col gap-1">
                        <div class="flex items-center justify-between">
                          <span class="font-semibold text-gray-100">{{ order.order_number }}</span>
                          <div class="text-xs text-gray-500">{{ formatDate(order.order_date).split(',')[0] }}</div>
                        </div>
                        <div class="text-xs font-medium text-gray-400">{{ order.client_name }}</div>
                      </div>
                      <div class="flex w-full items-center justify-between gap-2">
                        <Badge class="capitalize text-[10px] px-2 py-0 h-5 bg-blue-500/20 text-blue-400 border-0">{{ order.status }}</Badge>
                      </div>
                    </button>
                  </div>
                </ScrollArea>
              </div>
            </SheetContent>
          </Sheet>
          <div>
            <h1 class="font-semibold text-base text-white">{{ selectedOrder ? selectedOrder.order_number : 'Prepare Orders' }}</h1>
            <p v-if="selectedOrder" class="text-xs text-gray-400">{{ selectedOrder.client_name }}</p>
          </div>
        </div>
      </header>

      <!-- Mobile Tabs -->
      <Tabs default-value="prepared" class="flex-1 flex flex-col overflow-hidden">
        <TabsList class="bg-transparent border-b border-gray-800 rounded-none px-4 justify-start h-auto py-1">
          <TabsTrigger value="prepared" class="data-[state=active]:bg-gray-800/50 data-[state=active]:text-white text-gray-400 text-xs">Prepared</TabsTrigger>
          <TabsTrigger value="pending" class="data-[state=active]:bg-gray-800/50 data-[state=active]:text-white text-gray-400 text-xs">Pending ({{ pendingReadyOrders.length }})</TabsTrigger>
          <TabsTrigger value="history" class="data-[state=active]:bg-gray-800/50 data-[state=active]:text-white text-gray-400 text-xs">History</TabsTrigger>
        </TabsList>

        <TabsContent value="prepared" class="flex-1 overflow-hidden">
          <ScrollArea class="h-full p-4">
            <div v-if="selectedOrder" class="space-y-4">
              <!-- Customer & Address -->
              <Card class="bg-gray-900/40 border-gray-800 shadow-none text-white">
                <CardHeader class="pb-2"><CardTitle class="text-sm text-gray-200">Customer</CardTitle></CardHeader>
                <CardContent class="text-sm space-y-1">
                  <div class="flex justify-between"><span class="text-gray-400">Name:</span><span class="font-medium">{{ selectedOrder.client_name }}</span></div>
                  <div class="flex justify-between"><span class="text-gray-400">Contact:</span><span>{{ selectedOrder.client_phone }}</span></div>
                  <div class="flex justify-between"><span class="text-gray-400">Payment:</span><Badge class="bg-gray-800/80 text-gray-300 border-gray-700 uppercase">{{ selectedOrder.payment_method }}</Badge></div>
                  <div class="mt-2"><span class="text-gray-400">Address:</span><p class="text-gray-300 text-sm mt-1">{{ selectedOrder.delivery_address || 'N/A' }}</p></div>
                </CardContent>
              </Card>

              <!-- Items -->
              <Card class="bg-gray-900/40 border-gray-800 shadow-none text-white overflow-hidden">
                <CardHeader class="pb-2 border-b border-gray-800"><CardTitle class="text-sm text-gray-200">Items</CardTitle></CardHeader>
                <CardContent class="p-0 divide-y divide-gray-800">
                  <div v-for="item in selectedOrder.items" :key="item.id" class="p-3 flex justify-between">
                    <div><p class="font-medium text-sm">{{ item.name }}</p><p class="text-xs text-gray-500">{{ item.category }}</p></div>
                    <div class="text-right"><p class="font-bold text-blue-400">{{ item.quantity }}</p><p class="text-xs text-gray-400">{{ ((item.weight ?? 10) * item.quantity).toFixed(1) }} kg</p></div>
                  </div>
                </CardContent>
              </Card>

              <!-- Assignment -->
              <Card class="bg-gray-900/40 border-gray-700 shadow-sm">
                <CardHeader class="pb-2 border-b border-gray-800"><CardTitle class="text-sm text-white flex items-center gap-2"><Truck class="h-4 w-4" /> Assign</CardTitle></CardHeader>
                <CardContent class="space-y-4 pt-4">
                  <div class="space-y-2">
                    <Label class="text-gray-300 text-xs">Vehicle *</Label>
                    <select v-model="selectedVehicleId" class="w-full rounded-md border border-gray-700 bg-transparent px-3 py-2 text-sm text-white focus:border-blue-500 focus:outline-none">
                      <option value="" disabled class="bg-gray-900">Select...</option>
                      <option v-for="v in vehicles" :key="v.id" :value="v.id" class="bg-gray-900">{{ v.name }} ({{ v.capacity }}kg)</option>
                    </select>
                    <div v-if="selectedVehicleId && selectedOrder" class="text-xs flex justify-between text-gray-400">
                      <span>Order: {{ selectedOrderTotalWeight.toFixed(1) }} kg</span>
                      <span :class="isOverweight ? 'text-red-500' : 'text-green-400'">Avail: {{ vehicles.find(v => v.id === Number(selectedVehicleId))?.capacity ?? 0 }} kg</span>
                    </div>
                    <div v-if="isOverweight" class="text-red-400 text-xs flex items-center gap-1"><AlertCircle class="h-3 w-3" /> Overweight</div>
                  </div>
                  <div v-if="!isPickUp" class="space-y-2">
                    <Label class="text-gray-300 text-xs">Personnel *</Label>
                    <select v-model="selectedDeliveryMan" class="w-full rounded-md border border-gray-700 bg-transparent px-3 py-2 text-sm text-white focus:border-blue-500 focus:outline-none">
                      <option value="" disabled class="bg-gray-900">Select...</option>
                      <option v-for="dm in deliveryPersonnel" :key="dm.id" :value="dm.id" class="bg-gray-900">{{ dm.name }}</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <Label class="text-gray-300 text-xs">Proof *</Label>
                    <div class="border border-dashed border-gray-700 rounded-lg p-4 flex flex-col items-center justify-center relative" :class="proofPreview ? 'bg-transparent' : 'bg-gray-900/20'">
                      <input v-if="!proofPreview" type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handleProofUpload" />
                      <div v-if="!proofPreview" class="text-center pointer-events-none"><Upload class="h-5 w-5 text-gray-400 mx-auto mb-1" /><p class="text-xs text-gray-400">Tap to upload</p></div>
                      <div v-else class="relative w-full flex justify-center">
                        <img :src="proofPreview" class="max-h-48 rounded-md border border-gray-700 object-contain" />
                        <Button size="icon" variant="destructive" class="absolute -top-2 -right-2 h-7 w-7 rounded-full" @click.prevent.stop="removeProof"><X class="h-3 w-3" /></Button>
                      </div>
                    </div>
                  </div>
                </CardContent>
                <CardFooter class="p-4 border-t border-gray-800">
                  <Button @click="requirePermission('manage', submitPreparation)" :disabled="!canSubmit || isSubmitting" class="w-full bg-blue-600 hover:bg-blue-700 text-white">
                    <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                    <Truck v-else class="mr-2 h-4 w-4" />
                    {{ isSubmitting ? 'Processing...' : 'Assign' }}
                  </Button>
                </CardFooter>
              </Card>
            </div>
            <div v-else class="flex h-full flex-col items-center justify-center text-gray-500 pb-20"><Package class="h-16 w-16 mb-4 opacity-20" /><p class="text-lg">No order selected</p><p class="text-sm">Tap menu to select</p></div>
          </ScrollArea>
        </TabsContent>

        <TabsContent value="pending" class="flex-1 overflow-hidden">
          <ScrollArea class="h-full p-4">
            <div v-if="pendingReadyOrders.length === 0" class="text-center py-12"><Check class="h-12 w-12 text-green-500/50 mx-auto mb-3" /><p class="text-gray-400">All ready</p></div>
            <div v-else class="space-y-4">
              <div v-for="order in pendingReadyOrders" :key="order.delivery_id" class="p-4 border rounded-lg bg-yellow-50/10 border-yellow-700/50">
                <div class="flex justify-between items-start">
                  <div><p class="font-medium text-gray-200">{{ order.order_number }}</p><p class="text-sm text-gray-400">{{ order.customer }}</p><p class="text-xs text-gray-500">{{ order.vehicle_name }}</p></div>
                  <Badge class="bg-yellow-500/20 text-yellow-400 border-0">Waiting</Badge>
                </div>
                <Button class="w-full mt-3 bg-green-600 hover:bg-green-700 text-white h-8 text-sm" @click="signalReadyToGo(order.delivery_id)"><Play class="w-3 h-3 mr-1" /> Signal Ready</Button>
              </div>
            </div>
          </ScrollArea>
        </TabsContent>

        <TabsContent value="history" class="flex-1 overflow-hidden">
          <ScrollArea class="h-full p-4">
            <div v-if="shippedOrders.length === 0" class="text-center py-12"><Clock class="h-12 w-12 text-gray-500/50 mx-auto mb-3" /><p class="text-gray-400">No history</p></div>
            <div v-else class="space-y-4">
              <div v-for="order in shippedOrders" :key="order.delivery_id" class="p-4 border rounded-lg bg-gray-800/20 border-gray-700/50">
                <div class="flex justify-between items-start">
                  <div><p class="font-medium text-gray-200">{{ order.order_number }}</p><p class="text-sm text-gray-400">{{ order.customer }}</p><p class="text-xs text-gray-500">{{ order.vehicle_name }}</p></div>
                  <Badge :class="['border-0', order.delivery_status === 'completed' || order.delivery_status === 'delivered' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-blue-500/20 text-blue-400']">{{ order.delivery_status }}</Badge>
                </div>
                <div v-if="order.delivery_status === 'assigned' && !order.is_ready_to_go" class="mt-2">
                  <Button class="w-full bg-green-600 hover:bg-green-700 text-white h-8 text-sm" @click="signalReadyToGo(order.delivery_id)"><Play class="w-3 h-3 mr-1" /> Signal Ready</Button>
                </div>
                <div v-else-if="order.delivery_status === 'assigned' && order.is_ready_to_go" class="text-xs text-green-400 mt-1">Ready signal sent</div>
              </div>
            </div>
          </ScrollArea>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>

<style scoped>
:deep(button), :deep(input), :deep(.bg-background) {
  border-color: #1f2937 !important;
}
</style>