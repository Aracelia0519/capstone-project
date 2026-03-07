<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Toaster, toast } from 'vue-sonner' // Added Toaster for rendering notifications
import api from '@/utils/axios' 
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
  Image as ImageIcon
} from 'lucide-vue-next'

// UI Components
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

// --- Interfaces ---
interface OrderItem {
  id: number
  name: string
  category: string
  quantity: number
  unit_price: number
  total: number
}

interface Order {
  id: number
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
}

interface DeliveryMan {
  id: number
  name: string
  contact: string
  vehicle: string
}

// --- State ---
const mockDeliveryMen = ref<DeliveryMan[]>([])
const mockOrders = ref<Order[]>([])
const selectedOrderId = ref<number | null>(null)
const isLoading = ref(false)
const showMobileSheet = ref(false)

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_create: false,
  can_update: false,
  can_delete: false
})

// RBAC Action Interceptor (Matches ProcurementRequests/Orders paradigm)
const requirePermission = (action: string, callback: Function) => {
  const permKey = `can_${action}` as keyof typeof permissions.value;
  
  if (!permissions.value[permKey]) {
    toast.error(`Access Denied`, {
        description: `You do not have permission to ${action} prepare orders.`,
        duration: 5000
    });
    return;
  }
  if (callback) callback();
}

// Form State
const selectedDeliveryMan = ref<string>('')
const proofFile = ref<File | null>(null)
const proofPreview = ref<string | null>(null)
const isSubmitting = ref(false)

// --- Lifecycle ---
onMounted(() => {
  fetchOrders()
  fetchDeliveryPersonnel()
})

// --- Computed ---
// Show only 'confirmed' orders to be prepared
const confirmedOrders = computed(() => 
  mockOrders.value.filter(order => order.status === 'confirmed')
)

const selectedOrder = computed(() => 
  mockOrders.value.find(o => o.id === selectedOrderId.value)
)

const canSubmit = computed(() => {
  return selectedDeliveryMan.value !== '' && proofFile.value !== null
})

const statusIcon = (status: string) => {
  switch(status.toLowerCase()) {
    case 'pending': return Package
    case 'confirmed': return Check
    case 'prepared': return Package
    case 'shipped': return Truck
    default: return Package
  }
}

// --- Actions ---

const fetchOrders = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/operation-distributor/prepare-orders')
    
    // Handle the standardized response payload to extract both data and permissions
    if (response.data.success) {
        mockOrders.value = response.data.data
        if (response.data.permissions) {
            permissions.value = response.data.permissions
        }
    } else {
        mockOrders.value = response.data
    }

    if (!selectedOrderId.value && confirmedOrders.value.length > 0) {
      selectedOrderId.value = confirmedOrders.value[0].id
    } else if (confirmedOrders.value.length === 0) {
      selectedOrderId.value = null
    }
  } catch (error: any) {
    console.error(error)
    if (error.response?.status === 403) {
        toast.error('Access Denied', { description: error.response.data.message || 'You lack permissions to view these orders.' })
    } else {
        toast.error('Failed to fetch orders')
    }
  } finally {
    isLoading.value = false
  }
}

const fetchDeliveryPersonnel = async () => {
  try {
    const response = await api.get('/operation-distributor/prepare-orders/personnel')
    mockDeliveryMen.value = response.data
  } catch (error: any) {
    console.error(error)
    if (error.response?.status !== 403) {
      toast.error('Failed to fetch delivery personnel')
    }
  }
}

const handleProofUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    const file = target.files[0]
    proofFile.value = file
    proofPreview.value = URL.createObjectURL(file)
  }
}

const removeProof = () => {
  proofFile.value = null
  if (proofPreview.value) {
    URL.revokeObjectURL(proofPreview.value)
    proofPreview.value = null
  }
}

const selectOrderAndCloseSheet = (orderId: number) => {
  selectedOrderId.value = orderId
  showMobileSheet.value = false
  // Reset form when switching orders
  selectedDeliveryMan.value = ''
  removeProof()
}

const refreshOrders = async () => {
  await fetchOrders()
  await fetchDeliveryPersonnel()
  toast.success('Orders refreshed')
}

const submitPreparation = async () => {
  if (!canSubmit.value || !selectedOrder.value) return
  
  isSubmitting.value = true
  
  const formData = new FormData()
  formData.append('delivery_personnel_id', selectedDeliveryMan.value)
  if (proofFile.value) {
    formData.append('proof_file', proofFile.value)
  }

  try {
    await api.post(`/operation-distributor/prepare-orders/${selectedOrder.value.id}/dispatch`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    toast.success('Order processed successfully!', {
      description: `Order ${selectedOrder.value?.order_number} is now prepared and assigned for delivery.`
    })
    
    // Refresh Data smoothly to move the card to processed column
    await fetchOrders()
    
    // Reset form
    selectedDeliveryMan.value = ''
    removeProof()
  } catch (error: any) {
    console.error(error)
    if (error.response?.status === 403) {
      toast.error('Action Restricted', { description: error.response.data.message || 'You do not have permission to dispatch orders.' })
    } else {
      toast.error('Failed to process and dispatch the order.')
    }
  } finally {
    isSubmitting.value = false
  }
}

// Helpers
const formatCurrency = (val: number | string) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(num)
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
</script>

<template>
  <div class="flex h-full w-full flex-col text-gray-100 relative">
    <Toaster richColors position="top-right" expand />

    <div class="hidden md:flex h-full w-full overflow-hidden">
      <div class="w-80 lg:w-96 border-r border-gray-800 flex flex-col h-full">
        <div class="p-4 border-b border-gray-800 flex items-center justify-between sticky top-0 z-10 backdrop-blur-sm">
          <h2 class="font-semibold text-lg flex items-center gap-2 text-white">
            <Package class="h-5 w-5 text-blue-400" /> Prepare Orders
            <Badge v-if="confirmedOrders.length > 0" class="ml-2 bg-blue-500/20 text-blue-400 border-0">
              {{ confirmedOrders.length }}
            </Badge>
          </h2>
          <Button variant="ghost" size="icon" @click="refreshOrders" :disabled="isLoading" class="text-gray-400 hover:text-white hover:bg-gray-800/50">
            <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
          </Button>
        </div>

        <ScrollArea class="flex-1">
          <div class="flex flex-col gap-2 p-4">
            <div v-if="confirmedOrders.length === 0 && !isLoading" class="text-center text-gray-500 py-10">
              <Check class="h-12 w-12 mx-auto mb-3 opacity-20 text-green-400" />
              <p class="font-medium text-gray-400">All caught up!</p>
              <p class="text-sm">No confirmed orders left to prepare.</p>
            </div>

            <button
              v-for="order in confirmedOrders"
              :key="order.id"
              @click="selectOrderAndCloseSheet(order.id)"
              class="flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all"
              :class="selectedOrderId === order.id ? 'bg-gray-800/80 border-blue-500 ring-1 ring-blue-500' : 'bg-transparent border-gray-800 hover:bg-gray-800/30'"
            >
              <div class="flex w-full flex-col gap-1">
                <div class="flex items-center justify-between">
                  <span class="font-semibold text-gray-100">{{ order.order_number }}</span>
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
            
            <div v-if="mockOrders.some(o => o.status !== 'confirmed')" class="mt-4 pt-4 border-t border-gray-800">
              <p class="text-xs text-gray-500 mb-2 flex items-center gap-1 font-medium">
                <Clock class="h-3 w-3" /> Processed ({{ mockOrders.filter(o => o.status !== 'confirmed').length }})
              </p>
              <div class="space-y-2 opacity-80">
                <div
                  v-for="order in mockOrders.filter(o => o.status !== 'confirmed')"
                  :key="order.id"
                  class="w-full flex items-center justify-between rounded-lg border border-gray-800 p-2 text-xs bg-transparent"
                >
                  <div class="flex items-center gap-2">
                    <Badge class="capitalize text-[8px] px-1 py-0 h-4 bg-gray-800 text-gray-300 border-gray-700">
                      {{ order.status }}
                    </Badge>
                    <span class="text-gray-300">{{ order.order_number }}</span>
                  </div>
                  <span class="font-medium text-gray-400">{{ formatCurrency(order.grand_total) }}</span>
                </div>
              </div>
            </div>
          </div>
        </ScrollArea>
      </div>

      <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="flex items-center justify-between border-b border-gray-800 px-6 py-4">
          <div v-if="selectedOrder">
            <h1 class="text-xl font-bold flex items-center gap-2 text-white">
               <FileText class="h-5 w-5 text-blue-400" />
               Prepare Order Details
            </h1>
            <p class="text-sm text-gray-400">
              Processing order for {{ selectedOrder.client_name }}
            </p>
          </div>
          <div v-else>
             <h1 class="text-xl font-bold text-white">Select an Order</h1>
             <p class="text-sm text-gray-400">Choose a confirmed order to start preparation</p>
          </div>
        </header>

        <ScrollArea class="flex-1 p-6">
          <div v-if="selectedOrder" :key="selectedOrder.id" class="mx-auto max-w-4xl space-y-6 pb-10">
            
            <div class="grid gap-6 md:grid-cols-2">
              <Card class="bg-gray-900/40 border-gray-800 text-white shadow-none">
                <CardHeader class="pb-3 border-b border-gray-800/50">
                  <CardTitle class="text-md flex items-center gap-2 text-gray-200">
                      <User class="h-4 w-4 text-blue-400" /> Client Information
                  </CardTitle>
                </CardHeader>
                <CardContent class="text-sm space-y-3 pt-4">
                  <div class="flex justify-between">
                      <span class="text-white-400">Name:</span>
                      <span class="font-medium text-gray-200">{{ selectedOrder.client_name }}</span>
                  </div>
                  <div class="flex justify-between">
                      <span class="text-white-400">Contact:</span>
                      <span class="text-gray-200">{{ selectedOrder.client_phone }}</span>
                  </div>
                  <Separator class="my-3 bg-gray-800" />
                   <div class="flex justify-between items-center">
                      <span class="text-white-400">Payment Method:</span>
                      <Badge class="bg-gray-800/80 text-gray-300 border-gray-700 uppercase">{{ selectedOrder.payment_method }}</Badge>
                  </div>
                </CardContent>
              </Card>

              <Card class="bg-gray-900/40 border-gray-800 text-white shadow-none">
                 <CardHeader class="pb-3 border-b border-gray-800/50">
                  <CardTitle class="text-md flex items-center gap-2 text-gray-200">
                      <MapPin class="h-4 w-4 text-blue-400" /> Delivery Details
                  </CardTitle>
                </CardHeader>
                <CardContent class="pt-4">
                  <p class="text-sm text-gray-300 leading-relaxed font-medium">
                      {{ selectedOrder.delivery_address || 'No address provided.' }}
                  </p>
                </CardContent>
              </Card>
            </div>

            <Card class="bg-gray-900/40 border-gray-800 shadow-none overflow-hidden">
              <CardHeader class="pb-3 border-b border-gray-800 bg-transparent">
                 <CardTitle class="text-md text-white">Items to Pack</CardTitle>
              </CardHeader>
              <CardContent class="p-0">
                 <Table>
                    <TableHeader class="bg-transparent border-b border-gray-800">
                       <TableRow class="border-gray-800 hover:bg-transparent">
                          <TableHead class="text-white">Product</TableHead>
                          <TableHead class="text-right text-white">Qty</TableHead>
                       </TableRow>
                    </TableHeader>
                    <TableBody>
                       <TableRow v-for="item in selectedOrder.items" :key="item.id" class="border-gray-800 hover:bg-gray-800/30">
                          <TableCell>
                             <div class="font-medium text-gray-200">{{ item.name }}</div>
                             <div class="text-xs text-gray-500">{{ item.category }}</div>
                          </TableCell>
                          <TableCell class="text-right font-bold text-gray-200 text-lg">{{ item.quantity }}</TableCell>
                       </TableRow>
                    </TableBody>
                 </Table>
              </CardContent>
            </Card>

            <Card class="bg-gray-900/40 border-gray-700 shadow-sm mt-8">
               <CardHeader class="pb-3 border-b border-gray-800 bg-transparent">
                 <CardTitle class="text-lg flex items-center gap-2 text-white">
                    <Truck class="h-5 w-5 text-blue-400" /> Dispatch Processing
                 </CardTitle>
               </CardHeader>
               <CardContent class="space-y-6 pt-6">
                  
                  <div class="space-y-2">
                     <Label class="text-gray-300 font-semibold">Assign Delivery Personnel <span class="text-red-400">*</span></Label>
                     <p class="text-xs text-gray-500">Select the person who will deliver this order to the client.</p>
                     <div class="relative">
                        <select 
                          v-model="selectedDeliveryMan" 
                          class="w-full appearance-none rounded-md border border-gray-700 bg-transparent px-4 py-3 text-sm text-white focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition-colors"
                        >
                          <option value="" disabled class="bg-gray-900">Select a delivery personnel...</option>
                          <option v-for="dm in mockDeliveryMen" :key="dm.id" :value="dm.id" class="bg-gray-900">
                            {{ dm.name }}
                          </option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                          <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/>
                          </svg>
                        </div>
                     </div>
                  </div>

                  <Separator class="bg-gray-800" />

                  <div class="space-y-2">
                     <Label class="text-gray-300 font-semibold">Proof of Prepared Order <span class="text-red-400">*</span></Label>
                     <p class="text-xs text-gray-500">Upload a photo of the packed items ready for dispatch.</p>
                     
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
                    @click="requirePermission('update', submitPreparation)" 
                    :disabled="!canSubmit || isSubmitting" 
                    class="w-full sm:w-auto px-8 bg-blue-600 hover:bg-blue-700 text-white transition-all"
                    size="lg"
                  >
                     <Loader2 v-if="isSubmitting" class="mr-2 h-5 w-5 animate-spin" />
                     <Truck v-else class="mr-2 h-5 w-5" />
                     {{ isSubmitting ? 'Processing Order...' : 'Dispatch Order' }}
                  </Button>
               </CardFooter>
            </Card>

          </div>
          <div v-else class="flex h-full flex-col items-center justify-center text-gray-500 pb-20">
             <Package class="h-16 w-16 mb-4 opacity-20 text-gray-400" />
             <p class="text-lg">No orders selected to prepare</p>
             <p class="text-sm">Click a confirmed order from the list to view its details</p>
          </div>
        </ScrollArea>
      </div>
    </div>

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
                    <Badge v-if="confirmedOrders.length > 0" class="ml-2 bg-blue-500/20 text-blue-400 border-0">
                      {{ confirmedOrders.length }}
                    </Badge>
                  </h2>
                </div>

                <ScrollArea class="flex-1">
                  <div class="flex flex-col gap-2 p-4">
                    <div v-if="confirmedOrders.length === 0" class="text-center text-gray-500 py-10">
                      <Check class="h-12 w-12 mx-auto mb-3 opacity-20 text-green-400" />
                      <p class="font-medium">All caught up!</p>
                    </div>

                    <div v-for="order in confirmedOrders" :key="order.id" class="space-y-1">
                      <button
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
                            <Badge class="capitalize text-[10px] px-2 py-0 h-5 bg-blue-500/20 text-blue-400 border-0">
                                {{ order.status }}
                            </Badge>
                        </div>
                      </button>
                    </div>
                  </div>
                </ScrollArea>
              </div>
            </SheetContent>
          </Sheet>
          
          <div>
            <h1 class="font-semibold text-base text-white">
              {{ selectedOrder ? selectedOrder.order_number : 'Prepare Orders' }}
            </h1>
            <p v-if="selectedOrder" class="text-xs text-gray-400">
              {{ selectedOrder.client_name }}
            </p>
          </div>
        </div>
      </header>

      <ScrollArea class="flex-1">
        <div v-if="selectedOrder" class="p-4 space-y-4 pb-8">
          
          <Card class="bg-gray-900/40 border-gray-800 shadow-none text-white">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm flex items-center gap-2 text-gray-200">
                <User class="h-4 w-4 text-blue-400" /> Client
              </CardTitle>
            </CardHeader>
            <CardContent class="text-sm space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-400">Name:</span>
                <span class="font-medium">{{ selectedOrder.client_name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Contact:</span>
                <span>{{ selectedOrder.client_phone }}</span>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-gray-900/40 border-gray-800 shadow-none text-white">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm flex items-center gap-2 text-gray-200">
                <MapPin class="h-4 w-4 text-blue-400" /> Delivery Address
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm text-gray-300">
                {{ selectedOrder.delivery_address || 'No address provided.' }}
              </p>
            </CardContent>
          </Card>

          <Card class="bg-gray-900/40 border-gray-800 shadow-none text-white overflow-hidden">
            <CardHeader class="pb-2 bg-transparent border-b border-gray-800">
              <CardTitle class="text-sm text-gray-200">Items to Pack</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div class="divide-y divide-gray-800">
                <div v-for="item in selectedOrder.items" :key="item.id" class="p-3">
                  <div class="flex justify-between items-center mb-1">
                    <div>
                      <p class="font-medium text-sm text-gray-200">{{ item.name }}</p>
                      <p class="text-xs text-gray-500">{{ item.category }}</p>
                    </div>
                    <div class="text-right">
                      <span class="text-xs text-gray-400">Qty</span>
                      <p class="font-bold text-lg text-blue-400">{{ item.quantity }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-gray-900/40 border-gray-700 shadow-sm mt-6">
             <CardHeader class="pb-2 border-b border-gray-800 bg-transparent">
               <CardTitle class="text-sm flex items-center gap-2 text-white">
                  <Truck class="h-4 w-4 text-blue-400" /> Dispatch
               </CardTitle>
             </CardHeader>
             <CardContent class="space-y-4 pt-4">
                
                <div class="space-y-2">
                   <Label class="text-gray-300 text-xs">Assign Delivery Personnel *</Label>
                   <div class="relative">
                      <select 
                        v-model="selectedDeliveryMan" 
                        class="w-full appearance-none rounded-md border border-gray-700 bg-transparent px-3 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-none"
                      >
                        <option value="" disabled class="bg-gray-900">Select personnel...</option>
                        <option v-for="dm in mockDeliveryMen" :key="dm.id" :value="dm.id" class="bg-gray-900">
                          {{ dm.name }}
                        </option>
                      </select>
                   </div>
                </div>

                <div class="space-y-2">
                   <Label class="text-gray-300 text-xs">Proof of Prepared Order *</Label>
                   <div 
                      class="mt-1 border border-dashed border-gray-700 rounded-lg p-4 flex flex-col items-center justify-center relative"
                      :class="proofPreview ? 'bg-transparent' : 'bg-gray-900/20'"
                   >
                      <input 
                         v-if="!proofPreview"
                         type="file" 
                         accept="image/*" 
                         class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                         @change="handleProofUpload" 
                      />
                      <div v-if="!proofPreview" class="text-center pointer-events-none">
                         <Upload class="h-5 w-5 text-gray-400 mx-auto mb-1" />
                         <p class="text-xs text-gray-400">Tap to upload photo</p>
                      </div>
                      <div v-else class="relative w-full flex justify-center">
                         <img :src="proofPreview" class="max-h-48 rounded-md border border-gray-700 object-contain" />
                         <Button size="icon" variant="destructive" class="absolute -top-2 -right-2 h-7 w-7 rounded-full" @click.prevent.stop="removeProof">
                            <X class="h-3 w-3" />
                         </Button>
                      </div>
                   </div>
                </div>

             </CardContent>
             <CardFooter class="bg-transparent p-4 border-t border-gray-800">
                <Button 
                  @click="requirePermission('update', submitPreparation)" 
                  :disabled="!canSubmit || isSubmitting" 
                  class="w-full bg-blue-600 hover:bg-blue-700 text-white"
                  size="lg"
                >
                   <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                   <Truck v-else class="mr-2 h-4 w-4" />
                   {{ isSubmitting ? 'Processing...' : 'Dispatch Order' }}
                </Button>
             </CardFooter>
          </Card>

        </div>

        <div v-else class="flex h-full flex-col items-center justify-center text-gray-500 py-20 px-4">
          <Package class="h-16 w-16 mb-4 opacity-20 text-gray-400" />
          <p class="font-medium text-center text-gray-300">No order selected</p>
          <p class="text-sm text-center">Tap the menu icon to select an order</p>
        </div>
      </ScrollArea>
    </div>
  </div>
</template>

<style scoped>
/* Ensure dark aesthetics take over the components safely */
:deep(button), :deep(input), :deep(.bg-background) {
  border-color: #1f2937 !important; /* Tailwind's gray-800 hex value */
}
</style>