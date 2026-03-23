<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'
import { Toaster, toast } from 'vue-sonner' 
import { 
  Check, 
  Clock, 
  Truck, 
  Package, 
  FileText, 
  User, 
  AlertCircle,
  AlertTriangle,
  RefreshCw,
  Menu,
  MapPin
} from 'lucide-vue-next'

// UI Components
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { ScrollArea } from '@/components/ui/scroll-area'
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
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
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

// --- State ---
const router = useRouter()
const orders = ref<Order[]>([])
const selectedOrderId = ref<number | null>(null)
const isLoading = ref(false)
const isProcessing = ref(false)
const error = ref('')
const showConfirmDialog = ref(false)
const orderToConfirm = ref<Order | null>(null)
const showMobileSheet = ref(false)

// Updated to the new Level-Based Permissions
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// RBAC Action Interceptor
const requirePermission = (action: string, callback: Function) => {
  // Use keyof to satisfy TS typing
  const permKey = `can_${action}` as keyof typeof permissions.value;
  
  if (!permissions.value[permKey]) {
    toast.error(`Access Denied`, {
        description: `You do not have permission to ${action} orders.`,
        duration: 5000
    });
    return;
  }
  if (callback) callback();
}

// --- Computed ---
const incomingOrders = computed(() => 
  orders.value.filter(order => order.status.toLowerCase() === 'pending')
)

const selectedOrder = computed(() => 
  orders.value.find(o => o.id === selectedOrderId.value)
)

const statusIcon = (status: string) => {
  switch(status.toLowerCase()) {
    case 'pending': return Package
    case 'confirmed': return Clock
    case 'shipped': return Truck
    case 'delivered': return Check
    default: return Package
  }
}

// --- Actions ---
const fetchOrders = async () => {
  isLoading.value = true
  error.value = ''
  
  try {
    const response = await api.get('/operation-distributor/ecommerce-orders')
    
    // Handle standardized wrapper response
    if (response.data.success) {
      orders.value = response.data.data
      
      // Inject permissions for RBAC
      if (response.data.permissions) {
          permissions.value = response.data.permissions
      }
    } else {
      orders.value = response.data // Fallback just in case
    }
    
    // Select first incoming order by default if exists
    if (incomingOrders.value.length > 0 && !selectedOrderId.value) {
      selectedOrderId.value = incomingOrders.value[0].id
    } else if (incomingOrders.value.length === 0) {
      selectedOrderId.value = null
    }
    
  } catch (err: any) {
    console.error('Failed to fetch orders', err)
    
    if (err.response?.status === 403) {
      error.value = 'Access Denied: You lack permissions to view orders.'
      toast.error('Access Denied', {
        description: err.response.data.message || 'You do not have permission to view this page.',
        duration: 5000
      })
    } else {
      error.value = 'Could not load orders. Please try again.'
      toast.error('Error', {
        description: 'An error occurred while fetching orders'
      })
    }
  } finally {
    isLoading.value = false
  }
}

const openConfirmDialog = (order: Order) => {
  orderToConfirm.value = order
  showConfirmDialog.value = true
  showMobileSheet.value = false
}

const confirmOrder = async () => {
  if (!orderToConfirm.value) return
  
  isProcessing.value = true
  showConfirmDialog.value = false
  
  const toastId = toast.loading('Confirming order...', {
    description: `Processing order ${orderToConfirm.value.order_number}`
  })
  
  try {
    await api.post(`/operation-distributor/ecommerce-orders/${orderToConfirm.value.id}/confirm`)
    
    const orderIndex = orders.value.findIndex(o => o.id === orderToConfirm.value?.id)
    if (orderIndex !== -1) {
      const updatedOrder = { 
        ...orders.value[orderIndex], 
        status: 'confirmed' 
      }
      orders.value.splice(orderIndex, 1, updatedOrder)
    }
    
    if (selectedOrderId.value === orderToConfirm.value?.id) {
      const nextIncomingOrder = incomingOrders.value[0]
      if (nextIncomingOrder) {
        selectedOrderId.value = nextIncomingOrder.id
      } else {
        selectedOrderId.value = null
      }
    }
    
    toast.success('Order confirmed successfully!', {
      id: toastId,
      description: `Order ${orderToConfirm.value.order_number} is now confirmed`,
      duration: 5000
    })
    
  } catch (err: any) {
    console.error('Failed to confirm', err)
    
    if (err.response?.status === 403) {
      toast.dismiss(toastId) 
      toast.error('Action Restricted', {
        description: err.response.data.message || 'You do not have permission to confirm orders.',
        duration: 5000
      })
    } else {
      toast.error('Failed to confirm order', {
        id: toastId,
        description: err.response?.data?.message || 'An error occurred while confirming the order',
        duration: 5000
      })
    }
  } finally {
    isProcessing.value = false
    orderToConfirm.value = null
  }
}

const cancelConfirm = () => {
  showConfirmDialog.value = false
  orderToConfirm.value = null
}

const selectOrderAndCloseSheet = (orderId: number) => {
  selectedOrderId.value = orderId
  showMobileSheet.value = false
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

onMounted(() => {
  fetchOrders()
})
</script>

<template>
  <div class="flex h-full w-full flex-col text-gray-100 relative">
    <Toaster richColors position="top-right" expand />
    
    <AlertDialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
      <AlertDialogContent class="w-[95%] max-w-md mx-auto rounded-lg bg-gray-900 border-gray-800 text-white">
        <AlertDialogHeader>
          <AlertDialogTitle class="flex items-center gap-2 text-base sm:text-lg">
            <AlertTriangle class="h-5 w-5 text-yellow-500 flex-shrink-0" />
            <span>Confirm Client Order</span>
          </AlertDialogTitle>
          <AlertDialogDescription class="space-y-2 text-gray-400">
            <p class="text-sm">Are you sure you want to confirm this order?</p>
            <div v-if="orderToConfirm" class="mt-4 rounded-lg bg-gray-800 p-3 sm:p-4 text-xs sm:text-sm text-gray-200">
              <div class="grid grid-cols-2 gap-2">
                <span class="font-medium text-gray-400">Order Code:</span>
                <span class="text-right">{{ orderToConfirm.order_number }}</span>
                
                <span class="font-medium text-gray-400">Client:</span>
                <span class="text-right">{{ orderToConfirm.client_name }}</span>
                
                <span class="font-medium text-gray-400">Grand Total:</span>
                <span class="font-bold text-blue-400 text-right">{{ formatCurrency(orderToConfirm.grand_total) }}</span>
              </div>
            </div>
            <p class="mt-4 text-xs sm:text-sm text-gray-500">
              This action will change the order status to "confirmed" to proceed with fulfillment.
            </p>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex-col sm:flex-row gap-2">
          <AlertDialogCancel @click="cancelConfirm" :disabled="isProcessing" class="w-full sm:w-auto bg-transparent border-gray-700 hover:bg-gray-800 text-white">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="confirmOrder" :disabled="isProcessing" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white">
            <Check v-if="!isProcessing" class="mr-2 h-4 w-4" />
            <span v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin">◌</span>
            {{ isProcessing ? 'Confirming...' : 'Confirm Order' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <div class="hidden md:flex h-full w-full overflow-hidden">
      <div class="w-80 lg:w-96 border-r border-gray-800 flex flex-col h-full">
        <div class="p-4 border-b border-gray-800 flex items-center justify-between sticky top-0 z-10 backdrop-blur-sm">
          <h2 class="font-semibold text-lg flex items-center gap-2 text-white">
            <Package class="h-5 w-5 text-blue-400" /> Client Orders
            <Badge v-if="incomingOrders.length > 0" class="ml-2 bg-blue-500/20 text-blue-400 border-0">
              {{ incomingOrders.length }}
            </Badge>
          </h2>
          <Button variant="ghost" size="icon" @click="fetchOrders" :disabled="isLoading" class="text-gray-400 hover:text-white hover:bg-gray-800/50">
            <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
          </Button>
        </div>

        <div v-if="error" class="p-4">
          <div class="rounded-lg bg-red-500/10 p-3 text-sm text-red-400 border border-red-500/20">
            <div class="flex items-center gap-2">
              <AlertCircle class="h-4 w-4 flex-shrink-0" />
              <span class="font-medium">{{ error }}</span>
            </div>
          </div>
        </div>

        <ScrollArea class="flex-1">
          <div class="flex flex-col gap-2 p-4">
            <div v-if="incomingOrders.length === 0 && !isLoading" class="text-center text-gray-500 py-10">
              <Package class="h-12 w-12 mx-auto mb-3 opacity-20 text-gray-400" />
              <p class="font-medium text-gray-400">No pending orders</p>
              <p class="text-sm">All orders are confirmed or processed.</p>
            </div>

            <button
              v-for="order in incomingOrders"
              :key="order.id"
              @click="selectedOrderId = order.id"
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
                  <Badge class="capitalize text-[10px] px-2 py-0 h-5 bg-amber-500/20 text-amber-400 border-0">
                      {{ order.status }}
                  </Badge>
                  <span class="font-semibold text-blue-400">{{ formatCurrency(order.grand_total) }}</span>
              </div>
            </button>
            
            <div v-if="orders.some(o => o.status.toLowerCase() !== 'pending')" class="mt-4 pt-4 border-t border-gray-800">
              <p class="text-xs text-gray-500 mb-2 flex items-center gap-1 font-medium">
                <Clock class="h-3 w-3" /> History ({{ orders.filter(o => o.status.toLowerCase() !== 'pending').length }})
              </p>
              <div class="space-y-2 opacity-80">
                <button
                  v-for="order in orders.filter(o => o.status.toLowerCase() !== 'pending')"
                  :key="order.id"
                  @click="selectedOrderId = order.id"
                  class="w-full flex items-center justify-between rounded-lg border border-gray-800 p-2 text-xs transition-all"
                  :class="selectedOrderId === order.id ? 'bg-gray-800/80 border-blue-500 ring-1 ring-blue-500' : 'bg-transparent hover:bg-gray-800/30'"
                >
                  <div class="flex items-center gap-2">
                    <Badge class="capitalize text-[8px] px-1 py-0 h-4 bg-gray-800 text-gray-300 border-gray-700">
                      {{ order.status }}
                    </Badge>
                    <h2 class="text-gray-300">{{ order.order_number }}</h2>
                  </div>
                  <span class="font-medium text-gray-400">{{ formatCurrency(order.grand_total) }}</span>
                </button>
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
               Order Details
            </h1>
            <p class="text-sm text-gray-400">
              Viewing order from {{ selectedOrder.client_name }}
            </p>
          </div>
          <div v-else>
             <h1 class="text-xl font-bold text-white">Select an Order</h1>
             <p class="text-sm text-gray-400">Choose an incoming order to view details</p>
          </div>
          
          <div v-if="selectedOrder" class="flex gap-2">
              <Button 
                  variant="outline"
                  class="bg-gray-800/50 border-gray-700 text-gray-300 cursor-default hover:bg-gray-800/50 hover:text-gray-300"
              >
                  <component :is="statusIcon(selectedOrder.status)" class="mr-2 h-4 w-4" />
                  <span class="capitalize">{{ selectedOrder.status }}</span>
              </Button>
          </div>
        </header>

        <ScrollArea class="flex-1 p-6">
          <div v-if="selectedOrder" :key="selectedOrder.id" class="mx-auto max-w-4xl space-y-6 pb-10">
            
            <div class="flex items-center justify-between rounded-lg border border-gray-800 bg-gray-900/40 p-4 shadow-sm">
               <div class="flex items-center gap-4">
                  <div class="rounded-full bg-blue-500/10 p-3 text-blue-400 border border-blue-500/20">
                     <component :is="statusIcon(selectedOrder.status)" class="h-6 w-6" />
                  </div>
                  <div>
                     <p class="text-sm font-medium leading-none text-gray-400">Order Status</p>
                     <p class="text-2xl font-bold capitalize text-white mt-1">{{ selectedOrder.status }}</p>
                  </div>
               </div>
               <div class="text-right">
                  <p class="text-sm text-gray-400">Date Placed</p>
                  <p class="font-medium text-gray-200">{{ formatDate(selectedOrder.order_date) }}</p>
               </div>
            </div>

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
                      <span class="font-medium text-white-200">{{ selectedOrder.client_name }}</span>
                  </div>
                  <div class="flex justify-between">
                      <span class="text-white-400">Contact:</span>
                      <span class="text-white-200">{{ selectedOrder.client_phone }}</span>
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
                  <h2 class="text-sm text-gray-300 leading-relaxed font-medium">
                      {{ selectedOrder.delivery_address || 'No address provided.' }}
                  </h2>
                </CardContent>
              </Card>
            </div>

            <Card class="bg-gray-900/40 border-gray-800 shadow-none overflow-hidden">
              <CardHeader class="pb-3 border-b border-gray-800 bg-transparent">
                 <CardTitle class="text-md text-white">Order Items</CardTitle>
              </CardHeader>
              <CardContent class="p-0">
                 <Table>
                    <TableHeader class="bg-transparent border-b border-gray-800">
                       <TableRow class="border-gray-800 hover:bg-transparent">
                          <TableHead class="text-white">Product</TableHead>
                          <TableHead class="text-right text-white">Qty</TableHead>
                          <TableHead class="text-right text-white">Unit Price</TableHead>
                          <TableHead class="text-right text-white">Total</TableHead>
                       </TableRow>
                    </TableHeader>
                    <TableBody>
                       <TableRow v-for="item in selectedOrder.items" :key="item.id" class="border-gray-800 hover:bg-gray-800/30">
                          <TableCell>
                             <div class="font-medium text-gray-200">{{ item.name }}</div>
                             <div class="text-xs text-white">{{ item.category }}</div>
                          </TableCell>
                          <TableCell class="text-right text-white">{{ item.quantity }}</TableCell>
                          <TableCell class="text-right text-white">{{ formatCurrency(item.unit_price) }}</TableCell>
                          <TableCell class="text-right font-medium text-white">{{ formatCurrency(item.total) }}</TableCell>
                       </TableRow>
                    </TableBody>
                 </Table>
              </CardContent>
              <CardFooter class="bg-transparent p-5 flex flex-col items-end gap-2 border-t border-gray-800">
                 <div class="flex items-center gap-4 text-sm text-gray-400 w-full md:w-64 justify-between">
                    <span class="text-white">Subtotal:</span>
                    <span class="text-white">{{ formatCurrency(selectedOrder.total_amount) }}</span>
                 </div>
                 <div class="flex items-center gap-4 text-sm text-gray-400 w-full md:w-64 justify-between">
                    <span class="text-white">Shipping Fee:</span>
                    <span class="text-white">{{ formatCurrency(selectedOrder.shipping_fee) }}</span>
                 </div>
                 <Separator class="bg-gray-800 w-full md:w-64 my-1" />
                 <div class="flex w-full justify-between items-center font-bold text-lg mt-1">
                    <span class="text-white">Total</span>
                    <h2 class="text-blue-400">{{ formatCurrency(selectedOrder.grand_total) }}</h2>
                 </div>
              </CardFooter>
            </Card>

            <div v-if="selectedOrder.status === 'pending'" class="mt-8 flex justify-end">
              <Button 
                @click="requirePermission('approve', () => openConfirmDialog(selectedOrder))" 
                :disabled="isProcessing"
                class="bg-blue-600 hover:bg-blue-700 text-white w-full sm:w-auto px-8"
                size="lg"
              >
                <Check class="mr-2 h-5 w-5" />
                {{ isProcessing ? 'Processing...' : 'Confirm Order' }}
              </Button>
            </div>

          </div>
          <div v-else class="flex h-full flex-col items-center justify-center text-gray-500 pb-20">
             <Package class="h-16 w-16 mb-4 opacity-20 text-gray-400" />
             <p class="text-lg">No orders selected to display</p>
             <p class="text-sm">Click an order from the list to view its details</p>
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
                <div class="p-4 border-b border-gray-800 flex items-center justify-between sticky top-0 bg-gray-900 z-10">
                  <h2 class="font-semibold text-lg flex items-center gap-2">
                    <Package class="h-5 w-5 text-blue-400" /> Orders
                    <Badge v-if="incomingOrders.length > 0" class="ml-2 bg-blue-500/20 text-blue-400 border-0">
                      {{ incomingOrders.length }}
                    </Badge>
                  </h2>
                  <Button variant="ghost" size="icon" @click="fetchOrders" :disabled="isLoading" class="text-gray-400 hover:text-white hover:bg-gray-800/50">
                    <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
                  </Button>
                </div>

                <ScrollArea class="flex-1">
                  <div class="flex flex-col gap-2 p-4">
                    <div v-if="incomingOrders.length === 0 && !isLoading" class="text-center text-gray-500 py-10">
                      <Package class="h-12 w-12 mx-auto mb-3 opacity-20 text-gray-400" />
                      <p class="font-medium">No pending orders</p>
                    </div>

                    <div v-for="order in orders" :key="order.id" class="space-y-1">
                      <button
                        @click="selectOrderAndCloseSheet(order.id)"
                        class="w-full flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all"
                        :class="selectedOrderId === order.id ? 'bg-gray-800 border-blue-500 ring-1 ring-blue-500' : 'bg-transparent border-gray-800 hover:bg-gray-800/50'"
                      >
                        <div class="flex w-full flex-col gap-1">
                          <div class="flex items-center justify-between">
                            <span class="font-semibold text-gray-100">{{ order.order_number }}</span>
                            <div class="text-xs text-gray-500">{{ formatDate(order.order_date).split(',')[0] }}</div>
                          </div>
                          <div class="text-xs font-medium text-gray-400">{{ order.client_name }}</div>
                        </div>
                        <div class="flex w-full items-center justify-between gap-2">
                            <Badge class="capitalize text-[10px] px-2 py-0 h-5" :class="order.status === 'pending' ? 'bg-amber-500/20 text-amber-400 border-0' : 'bg-gray-800 text-gray-300 border-gray-700'">
                                {{ order.status }}
                            </Badge>
                            <span class="font-semibold" :class="order.status === 'pending' ? 'text-blue-400' : 'text-gray-400'">
                              {{ formatCurrency(order.grand_total) }}
                            </span>
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
              {{ selectedOrder ? selectedOrder.order_number : 'Orders' }}
            </h1>
            <p v-if="selectedOrder" class="text-xs text-gray-400">
              {{ selectedOrder.client_name }}
            </p>
            <p v-else class="text-xs text-gray-400">
              {{ incomingOrders.length }} pending
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <Button variant="ghost" size="icon" @click="fetchOrders" :disabled="isLoading" class="h-9 w-9 text-gray-300 hover:bg-gray-800/50 hover:text-white">
            <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
          </Button>
        </div>
      </header>

      <ScrollArea class="flex-1">
        <div v-if="error" class="p-4">
          <div class="rounded-lg bg-red-500/10 p-3 text-sm text-red-400 border border-red-500/20">
            <div class="flex items-center gap-2">
              <AlertCircle class="h-4 w-4 flex-shrink-0" />
              <span class="font-medium">{{ error }}</span>
            </div>
          </div>
        </div>

        <div v-if="selectedOrder" class="p-4 space-y-4 pb-8">
          <Card class="bg-gray-900/40 border-gray-800 shadow-none">
            <CardContent class="p-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="rounded-full bg-blue-500/10 p-2 text-blue-400 border border-blue-500/20">
                    <component :is="statusIcon(selectedOrder.status)" class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="text-xs text-gray-400">Status</p>
                    <p class="font-semibold capitalize text-white">{{ selectedOrder.status }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-xs text-gray-400">Date Placed</p>
                  <p class="text-sm font-medium text-gray-200">{{ formatDate(selectedOrder.order_date).split(',')[0] }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

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
              <Separator class="my-2 bg-gray-800" />
              <div class="flex justify-between items-center">
                <span class="text-gray-400">Payment:</span>
                <Badge class="bg-gray-800/80 text-gray-300 border-gray-700 uppercase">{{ selectedOrder.payment_method }}</Badge>
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
              <CardTitle class="text-sm text-gray-200">Items</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div class="divide-y divide-gray-800">
                <div v-for="item in selectedOrder.items" :key="item.id" class="p-3">
                  <div class="flex justify-between items-start mb-1">
                    <div>
                      <p class="font-medium text-sm text-gray-200">{{ item.name }}</p>
                      <p class="text-xs text-gray-500">{{ item.category }}</p>
                    </div>
                    <p class="font-bold text-sm text-blue-400">{{ formatCurrency(item.total) }}</p>
                  </div>
                  <div class="flex justify-between text-xs text-gray-400 mt-2">
                    <span>Qty: {{ item.quantity }}</span>
                    <span>@ {{ formatCurrency(item.unit_price) }}</span>
                  </div>
                </div>
              </div>
            </CardContent>
            <CardFooter class="bg-transparent p-4 border-t border-gray-800 flex flex-col gap-1">
              <div class="flex w-full justify-between items-center text-sm text-gray-400">
                <span>Subtotal</span>
                <span>{{ formatCurrency(selectedOrder.total_amount) }}</span>
              </div>
              <div class="flex w-full justify-between items-center text-sm text-gray-400">
                <span>Shipping</span>
                <span>{{ formatCurrency(selectedOrder.shipping_fee) }}</span>
              </div>
              <Separator class="my-1 bg-gray-800" />
              <div class="flex w-full justify-between items-center font-bold text-lg mt-1">
                <span class="text-white">Total</span>
                <span class="text-blue-400">{{ formatCurrency(selectedOrder.grand_total) }}</span>
              </div>
            </CardFooter>
          </Card>

          <div v-if="selectedOrder.status === 'pending'" class="mt-6">
            <Button 
              @click="requirePermission('approve', () => openConfirmDialog(selectedOrder))" 
              :disabled="isProcessing"
              class="w-full bg-blue-600 hover:bg-blue-700 text-white"
              size="lg"
            >
              <Check class="mr-2 h-5 w-5" />
              {{ isProcessing ? 'Processing...' : 'Confirm Order' }}
            </Button>
          </div>

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
/* Ensure dark aesthetics take over the entire container */
:deep(button), :deep(input), :deep(.bg-background) {
  border-color: #1f2937 !important; /* Tailwind's gray-800 hex value */
}
</style>