<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
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
  ArrowLeft,
  Menu
} from 'lucide-vue-next'

// UI Components
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { ScrollArea } from '@/components/ui/scroll-area'
import {
  Card,
  CardContent,
  CardDescription,
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
  request_code: string
  status: string
  order_date: string
  required_date: string
  total_amount: number
  notes: string
  distributor_name: string
  distributor_contact: string
  payment_terms: string
  shipping_method: string
  items: OrderItem[]
}

// --- State ---
const orders = ref<Order[]>([])
const selectedOrderId = ref<number | null>(null)
const isLoading = ref(false)
const isProcessing = ref(false)
const error = ref('')
const showConfirmDialog = ref(false)
const orderToConfirm = ref<Order | null>(null)
const showMobileList = ref(false)
const showMobileSheet = ref(false)

// --- Computed ---
// Filter orders to show only those that are 'ready' (incoming)
const incomingOrders = computed(() => 
  orders.value.filter(order => order.status.toLowerCase() === 'ready')
)

const selectedOrder = computed(() => 
  orders.value.find(o => o.id === selectedOrderId.value)
)

const statusVariant = (status: string) => {
  switch(status.toLowerCase()) {
    case 'ready': return 'default'
    case 'processing': return 'secondary'
    case 'shipped': return 'outline'
    default: return 'outline'
  }
}

const statusIcon = (status: string) => {
  switch(status.toLowerCase()) {
    case 'ready': return Package
    case 'processing': return Clock
    case 'shipped': return Truck
    default: return Package
  }
}

// --- Actions ---
const fetchOrders = async () => {
  isLoading.value = true
  error.value = ''
  
  const toastId = toast.loading('Loading orders...', {
    description: 'Please wait while we fetch your incoming orders.'
  })
  
  try {
    const response = await api.get('/supplier/orders')
    orders.value = response.data
    
    // Select first incoming order by default if exists
    if (incomingOrders.value.length > 0 && !selectedOrderId.value) {
      selectedOrderId.value = incomingOrders.value[0].id
    } else if (incomingOrders.value.length === 0) {
      // If no incoming orders, clear selection
      selectedOrderId.value = null
    }
    
    toast.success('Orders loaded successfully', {
      id: toastId,
      description: `Found ${incomingOrders.value.length} incoming order(s)`
    })
  } catch (err: any) {
    console.error('Failed to fetch orders', err)
    error.value = 'Could not load orders. Please try again.'
    
    toast.error('Failed to load orders', {
      id: toastId,
      description: err.response?.data?.message || 'An error occurred while fetching orders'
    })
  } finally {
    isLoading.value = false
  }
}

const openConfirmDialog = (order: Order) => {
  orderToConfirm.value = order
  showConfirmDialog.value = true
  // Close mobile sheet if open
  showMobileSheet.value = false
}

const confirmOrder = async () => {
  if (!orderToConfirm.value) return
  
  isProcessing.value = true
  showConfirmDialog.value = false
  
  const toastId = toast.loading('Confirming order...', {
    description: `Processing order ${orderToConfirm.value.request_code}`
  })
  
  try {
    await api.post(`/supplier/orders/${orderToConfirm.value.id}/confirm`)
    
    // Update local state to reflect change immediately
    const orderIndex = orders.value.findIndex(o => o.id === orderToConfirm.value?.id)
    if (orderIndex !== -1) {
      // Create a new object to ensure reactivity
      const updatedOrder = { 
        ...orders.value[orderIndex], 
        status: 'processing' 
      }
      orders.value.splice(orderIndex, 1, updatedOrder)
    }
    
    // If the confirmed order was selected, clear selection or select next available
    if (selectedOrderId.value === orderToConfirm.value?.id) {
      // Find next incoming order to select
      const nextIncomingOrder = incomingOrders.value[0]
      if (nextIncomingOrder) {
        selectedOrderId.value = nextIncomingOrder.id
      } else {
        selectedOrderId.value = null
      }
    }
    
    toast.success('Order confirmed successfully!', {
      id: toastId,
      description: `Order ${orderToConfirm.value.request_code} is now being processed`,
      duration: 5000
    })
    
  } catch (err: any) {
    console.error('Failed to confirm', err)
    
    toast.error('Failed to confirm order', {
      id: toastId,
      description: err.response?.data?.message || 'An error occurred while confirming the order',
      duration: 5000
    })
  } finally {
    isProcessing.value = false
    orderToConfirm.value = null
  }
}

const cancelConfirm = () => {
  showConfirmDialog.value = false
  orderToConfirm.value = null
  
  toast.info('Order confirmation cancelled', {
    description: 'No changes were made to the order',
    duration: 3000
  })
}

const selectOrderAndCloseSheet = (orderId: number) => {
  selectedOrderId.value = orderId
  showMobileSheet.value = false
}

const goBackToList = () => {
  showMobileList.value = true
}

// Helper for currency
const formatCurrency = (val: number | string) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(num)
}

// Helper for date formatting
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  fetchOrders()
})
</script>

<template>
  <div class="flex h-screen w-full flex-col bg-muted/20">
    
    <!-- Alert Dialog for Confirmation -->
    <AlertDialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
      <AlertDialogContent class="w-[95%] max-w-md mx-auto rounded-lg">
        <AlertDialogHeader>
          <AlertDialogTitle class="flex items-center gap-2 text-base sm:text-lg">
            <AlertTriangle class="h-5 w-5 text-yellow-500 flex-shrink-0" />
            <span>Confirm Order</span>
          </AlertDialogTitle>
          <AlertDialogDescription class="space-y-2">
            <p class="text-sm">Are you sure you want to confirm this order?</p>
            <div v-if="orderToConfirm" class="mt-4 rounded-lg bg-muted p-3 sm:p-4 text-xs sm:text-sm">
              <div class="grid grid-cols-2 gap-2">
                <span class="font-medium">Order Code:</span>
                <span class="text-right">{{ orderToConfirm.request_code }}</span>
                
                <span class="font-medium">Distributor:</span>
                <span class="text-right">{{ orderToConfirm.distributor_name }}</span>
                
                <span class="font-medium">Total Amount:</span>
                <span class="font-bold text-primary text-right">{{ formatCurrency(orderToConfirm.total_amount) }}</span>
                
                <span class="font-medium">Required Date:</span>
                <span class="text-right">{{ formatDate(orderToConfirm.required_date) }}</span>
              </div>
            </div>
            <p class="mt-4 text-xs sm:text-sm text-muted-foreground">
              This action will change the order status to "processing" and notify the distributor.
            </p>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex-col sm:flex-row gap-2">
          <AlertDialogCancel @click="cancelConfirm" :disabled="isProcessing" class="w-full sm:w-auto">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="confirmOrder" :disabled="isProcessing" class="w-full sm:w-auto bg-primary hover:bg-primary/90">
            <Check v-if="!isProcessing" class="mr-2 h-4 w-4" />
            <span v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin">◌</span>
            {{ isProcessing ? 'Confirming...' : 'Confirm Order' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Desktop View (md and up) -->
    <div class="hidden md:flex h-full w-full">
      <!-- Left Sidebar - Incoming Orders List (Filtered) -->
      <div class="w-80 lg:w-96 border-r bg-background flex flex-col h-full">
        <div class="p-4 border-b flex items-center justify-between sticky top-0 bg-background z-10">
          <h2 class="font-semibold text-lg flex items-center gap-2">
            <Package class="h-5 w-5" /> Incoming Orders
            <Badge v-if="incomingOrders.length > 0" variant="secondary" class="ml-2">
              {{ incomingOrders.length }}
            </Badge>
          </h2>
          <Button variant="ghost" size="icon" @click="fetchOrders" :disabled="isLoading">
            <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
          </Button>
        </div>

        <div v-if="error" class="p-4">
          <div class="rounded-lg bg-destructive/10 p-3 text-sm text-destructive">
            <div class="flex items-center gap-2">
              <AlertCircle class="h-4 w-4 flex-shrink-0" />
              <span class="font-medium">{{ error }}</span>
            </div>
          </div>
        </div>

        <ScrollArea class="flex-1">
          <div class="flex flex-col gap-2 p-4">
            <div v-if="incomingOrders.length === 0 && !isLoading" class="text-center text-muted-foreground py-10">
              <Package class="h-12 w-12 mx-auto mb-3 opacity-20" />
              <p class="font-medium">No incoming orders</p>
              <p class="text-sm">All orders have been processed</p>
            </div>

            <button
              v-for="order in incomingOrders"
              :key="order.id"
              @click="selectedOrderId = order.id"
              class="flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all hover:bg-accent"
              :class="selectedOrderId === order.id ? 'bg-accent border-primary ring-1 ring-primary' : 'bg-card'"
            >
              <div class="flex w-full flex-col gap-1">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="font-semibold">{{ order.request_code }}</span>
                  </div>
                  <div class="text-xs text-muted-foreground">{{ formatDate(order.order_date) }}</div>
                </div>
                <div class="text-xs font-medium">{{ order.distributor_name }}</div>
              </div>
              <div class="flex w-full items-center justify-between gap-2">
                  <Badge :variant="statusVariant(order.status)" class="capitalize text-[10px] px-1 py-0 h-5">
                      {{ order.status }}
                  </Badge>
                  <span class="font-semibold text-primary">{{ formatCurrency(order.total_amount) }}</span>
              </div>
            </button>
            
            <!-- Show separator if there are processed orders -->
            <div v-if="orders.some(o => o.status.toLowerCase() !== 'ready')" class="mt-4 pt-4 border-t">
              <p class="text-xs text-muted-foreground mb-2 flex items-center gap-1">
                <Clock class="h-3 w-3" /> Processed ({{ orders.filter(o => o.status.toLowerCase() !== 'ready').length }})
              </p>
              <div class="space-y-2 opacity-60">
                <div
                  v-for="order in orders.filter(o => o.status.toLowerCase() !== 'ready').slice(0, 3)"
                  :key="order.id"
                  class="flex items-center justify-between rounded-lg border border-muted bg-muted/20 p-2 text-xs"
                >
                  <div class="flex items-center gap-2">
                    <Badge :variant="statusVariant(order.status)" class="capitalize text-[8px] px-1 py-0 h-4">
                      {{ order.status }}
                    </Badge>
                    <span>{{ order.request_code }}</span>
                  </div>
                  <span class="font-medium">{{ formatCurrency(order.total_amount) }}</span>
                </div>
                <p v-if="orders.filter(o => o.status.toLowerCase() !== 'ready').length > 3" class="text-xs text-center text-muted-foreground">
                  +{{ orders.filter(o => o.status.toLowerCase() !== 'ready').length - 3 }} more
                </p>
              </div>
            </div>
          </div>
        </ScrollArea>
      </div>

      <!-- Right Side - Order Details -->
      <div class="flex-1 flex flex-col h-full overflow-hidden">
        <header class="flex items-center justify-between border-b bg-background px-6 py-4">
          <div v-if="selectedOrder">
            <h1 class="text-xl font-bold flex items-center gap-2">
               <FileText class="h-5 w-5 text-muted-foreground" />
               Order Details
            </h1>
            <p class="text-sm text-muted-foreground">
              Viewing request from {{ selectedOrder.distributor_name }}
            </p>
          </div>
          <div v-else>
             <h1 class="text-xl font-bold">Select an Order</h1>
             <p class="text-sm text-muted-foreground">Choose an incoming order to view details</p>
          </div>
          
          <div v-if="selectedOrder" class="flex gap-2">
              <Button 
                  v-if="selectedOrder.status === 'ready'"
                  @click="openConfirmDialog(selectedOrder)" 
                  :disabled="isProcessing"
              >
                  <Check class="mr-2 h-4 w-4" />
                  {{ isProcessing ? 'Processing...' : 'Confirm' }}
              </Button>
              <Button 
                  v-if="selectedOrder.status === 'processing'"
                  variant="secondary"
                  disabled
              >
                  <Clock class="mr-2 h-4 w-4" />
                  Processing
              </Button>
              <Button 
                  v-if="selectedOrder.status === 'shipped'"
                  variant="outline"
                  disabled
              >
                  <Truck class="mr-2 h-4 w-4" />
                  Shipped
              </Button>
          </div>
        </header>

        <ScrollArea class="flex-1 p-6">
          <div v-if="selectedOrder" :key="selectedOrder.id" class="mx-auto max-w-4xl space-y-6 pb-10">
            
            <!-- Status Banner -->
            <div class="flex items-center justify-between rounded-lg border bg-card p-4 shadow-sm">
               <div class="flex items-center gap-4">
                  <div class="rounded-full bg-primary/10 p-2 text-primary">
                     <Truck v-if="selectedOrder.status === 'shipped'" class="h-6 w-6" />
                     <Clock v-else-if="selectedOrder.status === 'processing'" class="h-6 w-6" />
                     <Package v-else class="h-6 w-6" />
                  </div>
                  <div>
                     <p class="text-sm font-medium leading-none">Order Status</p>
                     <p class="text-2xl font-bold capitalize text-primary mt-1">{{ selectedOrder.status }}</p>
                  </div>
               </div>
               <div class="text-right">
                  <p class="text-sm text-muted-foreground">Required Delivery</p>
                  <p class="font-medium">{{ formatDate(selectedOrder.required_date) }}</p>
               </div>
            </div>

            <!-- Distributor Info and Notes -->
            <div class="grid gap-6 md:grid-cols-2">
              <Card>
                <CardHeader class="pb-3">
                  <CardTitle class="text-md flex items-center gap-2">
                      <User class="h-4 w-4" /> Distributor Information
                  </CardTitle>
                </CardHeader>
                <CardContent class="text-sm space-y-2">
                  <div class="flex justify-between">
                      <span class="text-muted-foreground">Name:</span>
                      <span class="font-medium">{{ selectedOrder.distributor_name }}</span>
                  </div>
                  <div class="flex justify-between">
                      <span class="text-muted-foreground">Contact:</span>
                      <span>{{ selectedOrder.distributor_contact }}</span>
                  </div>
                  <Separator class="my-2" />
                   <div class="flex justify-between">
                      <span class="text-muted-foreground">Payment Terms:</span>
                      <Badge variant="outline">{{ selectedOrder.payment_terms }}</Badge>
                  </div>
                   <div class="flex justify-between">
                      <span class="text-muted-foreground">Shipping:</span>
                      <span class="capitalize">{{ selectedOrder.shipping_method }}</span>
                  </div>
                </CardContent>
              </Card>

              <Card>
                 <CardHeader class="pb-3">
                  <CardTitle class="text-md flex items-center gap-2">
                      <FileText class="h-4 w-4" /> Instructions / Notes
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <p class="text-sm text-muted-foreground leading-relaxed">
                      {{ selectedOrder.notes || 'No special instructions provided.' }}
                  </p>
                </CardContent>
              </Card>
            </div>

            <!-- Order Items Table -->
            <Card>
              <CardHeader class="pb-3">
                 <CardTitle class="text-md">Order Items</CardTitle>
              </CardHeader>
              <CardContent class="p-0">
                 <Table>
                    <TableHeader>
                       <TableRow>
                          <TableHead>Product</TableHead>
                          <TableHead class="text-right">Qty</TableHead>
                          <TableHead class="text-right">Price</TableHead>
                          <TableHead class="text-right">Total</TableHead>
                       </TableRow>
                    </TableHeader>
                    <TableBody>
                       <TableRow v-for="item in selectedOrder.items" :key="item.id">
                          <TableCell>
                             <div class="font-medium">{{ item.name }}</div>
                             <div class="text-xs text-muted-foreground">{{ item.category }}</div>
                          </TableCell>
                          <TableCell class="text-right">{{ item.quantity }}</TableCell>
                          <TableCell class="text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
                          <TableCell class="text-right font-bold">{{ formatCurrency(item.total) }}</TableCell>
                       </TableRow>
                    </TableBody>
                 </Table>
              </CardContent>
              <CardFooter class="bg-muted/50 p-4 flex justify-end">
                 <div class="flex items-center gap-4 text-base sm:text-lg font-bold">
                    <span>Total:</span>
                    <span class="text-primary">{{ formatCurrency(selectedOrder.total_amount) }}</span>
                 </div>
              </CardFooter>
            </Card>
          </div>
          <div v-else class="flex h-full flex-col items-center justify-center text-muted-foreground pb-20">
             <Package class="h-16 w-16 mb-4 opacity-20" />
             <p>No incoming orders to display</p>
             <p class="text-sm">All orders have been processed</p>
          </div>
        </ScrollArea>
      </div>
    </div>

    <!-- Mobile View (below md) -->
    <div class="flex md:hidden flex-col h-full w-full">
      <!-- Mobile Header -->
      <header class="flex items-center justify-between border-b bg-background px-4 py-3 sticky top-0 z-20">
        <div class="flex items-center gap-3">
          <Sheet v-model:open="showMobileSheet">
            <SheetTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9">
                <Menu class="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-[85%] sm:w-80 p-0">
              <!-- Mobile Orders List -->
              <div class="flex flex-col h-full">
                <div class="p-4 border-b flex items-center justify-between sticky top-0 bg-background z-10">
                  <h2 class="font-semibold text-lg flex items-center gap-2">
                    <Package class="h-5 w-5" /> Orders
                    <Badge v-if="incomingOrders.length > 0" variant="secondary" class="ml-2">
                      {{ incomingOrders.length }}
                    </Badge>
                  </h2>
                  <Button variant="ghost" size="icon" @click="fetchOrders" :disabled="isLoading">
                    <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
                  </Button>
                </div>

                <ScrollArea class="flex-1">
                  <div class="flex flex-col gap-2 p-4">
                    <div v-if="incomingOrders.length === 0 && !isLoading" class="text-center text-muted-foreground py-10">
                      <Package class="h-12 w-12 mx-auto mb-3 opacity-20" />
                      <p class="font-medium">No incoming orders</p>
                    </div>

                    <div v-for="order in orders" :key="order.id" class="space-y-1">
                      <button
                        @click="selectOrderAndCloseSheet(order.id)"
                        class="w-full flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all hover:bg-accent"
                        :class="selectedOrderId === order.id ? 'bg-accent border-primary ring-1 ring-primary' : 'bg-card'"
                      >
                        <div class="flex w-full flex-col gap-1">
                          <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                              <span class="font-semibold">{{ order.request_code }}</span>
                            </div>
                            <div class="text-xs text-muted-foreground">{{ formatDate(order.order_date) }}</div>
                          </div>
                          <div class="text-xs font-medium">{{ order.distributor_name }}</div>
                        </div>
                        <div class="flex w-full items-center justify-between gap-2">
                            <Badge :variant="statusVariant(order.status)" class="capitalize text-[10px] px-1 py-0 h-5">
                                {{ order.status }}
                            </Badge>
                            <span class="font-semibold" :class="order.status === 'ready' ? 'text-primary' : 'text-muted-foreground'">
                              {{ formatCurrency(order.total_amount) }}
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
            <h1 class="font-semibold text-base">
              {{ selectedOrder ? `Order #${selectedOrder.request_code}` : 'Incoming Orders' }}
            </h1>
            <p v-if="selectedOrder" class="text-xs text-muted-foreground">
              {{ selectedOrder.distributor_name }}
            </p>
            <p v-else class="text-xs text-muted-foreground">
              {{ incomingOrders.length }} pending order(s)
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <Button variant="ghost" size="icon" @click="fetchOrders" :disabled="isLoading" class="h-9 w-9">
            <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
          </Button>
        </div>
      </header>

      <!-- Mobile Content -->
      <ScrollArea class="flex-1">
        <div v-if="error" class="p-4">
          <div class="rounded-lg bg-destructive/10 p-3 text-sm text-destructive">
            <div class="flex items-center gap-2">
              <AlertCircle class="h-4 w-4 flex-shrink-0" />
              <span class="font-medium">{{ error }}</span>
            </div>
          </div>
        </div>

        <div v-if="selectedOrder" class="p-4 space-y-4 pb-8">
          <!-- Status Card -->
          <Card>
            <CardContent class="p-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="rounded-full bg-primary/10 p-2 text-primary">
                    <component :is="statusIcon(selectedOrder.status)" class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="text-xs text-muted-foreground">Status</p>
                    <p class="font-semibold capitalize">{{ selectedOrder.status }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-xs text-muted-foreground">Required</p>
                  <p class="text-sm font-medium">{{ formatDate(selectedOrder.required_date) }}</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Action Button -->
          <div v-if="selectedOrder.status === 'ready'" class="sticky top-0 z-10 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 py-2">
            <Button 
              @click="openConfirmDialog(selectedOrder)" 
              :disabled="isProcessing"
              class="w-full"
              size="lg"
            >
              <Check class="mr-2 h-5 w-5" />
              {{ isProcessing ? 'Processing...' : 'Confirm Order' }}
            </Button>
          </div>

          <!-- Distributor Info -->
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm flex items-center gap-2">
                <User class="h-4 w-4" /> Distributor
              </CardTitle>
            </CardHeader>
            <CardContent class="text-sm space-y-2">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Name:</span>
                <span class="font-medium">{{ selectedOrder.distributor_name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Contact:</span>
                <span>{{ selectedOrder.distributor_contact }}</span>
              </div>
              <Separator class="my-2" />
              <div class="flex justify-between">
                <span class="text-muted-foreground">Payment:</span>
                <Badge variant="outline">{{ selectedOrder.payment_terms }}</Badge>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Shipping:</span>
                <span class="capitalize">{{ selectedOrder.shipping_method }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Notes -->
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm flex items-center gap-2">
                <FileText class="h-4 w-4" /> Notes
              </CardTitle>
            </CardHeader>
            <CardContent>
              <p class="text-sm text-muted-foreground">
                {{ selectedOrder.notes || 'No special instructions provided.' }}
              </p>
            </CardContent>
          </Card>

          <!-- Items -->
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-sm">Items</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div class="divide-y">
                <div v-for="item in selectedOrder.items" :key="item.id" class="p-3">
                  <div class="flex justify-between items-start mb-1">
                    <div>
                      <p class="font-medium text-sm">{{ item.name }}</p>
                      <p class="text-xs text-muted-foreground">{{ item.category }}</p>
                    </div>
                    <p class="font-bold text-sm">{{ formatCurrency(item.total) }}</p>
                  </div>
                  <div class="flex justify-between text-xs text-muted-foreground">
                    <span>Qty: {{ item.quantity }}</span>
                    <span>@ {{ formatCurrency(item.unit_price) }}</span>
                  </div>
                </div>
              </div>
            </CardContent>
            <CardFooter class="bg-muted/50 p-3">
              <div class="flex w-full justify-between items-center font-bold">
                <span>Total</span>
                <span class="text-primary">{{ formatCurrency(selectedOrder.total_amount) }}</span>
              </div>
            </CardFooter>
          </Card>
        </div>

        <!-- No Order Selected -->
        <div v-else class="flex flex-col items-center justify-center text-muted-foreground py-20 px-4">
          <Package class="h-16 w-16 mb-4 opacity-20" />
          <p class="font-medium text-center">No order selected</p>
          <p class="text-sm text-center">Tap the menu icon to select an order</p>
        </div>
      </ScrollArea>
    </div>
  </div>
</template>

<style scoped>
/* Mobile optimizations */
@media (max-width: 768px) {
  .mobile-sticky-button {
    position: sticky;
    bottom: 0;
    background: linear-gradient(to top, hsl(var(--background)) 50%, transparent);
  }
}
</style>