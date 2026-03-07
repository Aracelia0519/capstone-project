<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'

// Shadcn UI Components
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
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
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
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

// Icons
import { 
  ShoppingBag, 
  Clock, 
  Package, 
  CheckCircle2, 
  RefreshCw, 
  Search, 
  ChevronDown, 
  ChevronUp,
  Image as ImageIcon,
  Truck,
  XCircle,
  FileText,
  AlertCircle,
  MapPin,
  CreditCard,
  Loader2,
  X,
  Star 
} from 'lucide-vue-next'

const router = useRouter()

// Inject guest/user properties
const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

// --- Interfaces ---
interface Product {
  id: number
  name: string
  category: string
  image_url: string | null
}

interface OrderItem {
  id: number
  product_id: number
  quantity: number
  price: string
  product?: Product
  is_reviewed?: boolean
  review_rating?: number
  review_comment?: string
}

interface Order {
  id: number
  order_number: string
  total_amount: string
  shipping_fee: string
  grand_total: string
  payment_method: string
  status: string
  delivery_address: string
  created_at: string
  items: OrderItem[]
  expanded?: boolean
}

// --- State ---
const orders = ref<Order[]>([])
const isLoading = ref(true)
const statusFilter = ref('all')
const displayedOrders = ref(5)
const selectedOrder = ref<Order | null>(null)
const isRefreshing = ref(false)
const isAuthAlertOpen = ref(false)

// --- Review Modal State ---
const isReviewModalOpen = ref(false)
const isSubmittingReview = ref(false)
const reviewForm = ref({
  order_id: null as number | null,
  product_id: null as number | null,
  product_name: '',
  rating: 5,
  comment: ''
})

// --- Dialog State Management ---
const isModalOpen = computed({
  get: () => selectedOrder.value !== null,
  set: (isOpen) => {
    if (!isOpen) {
      selectedOrder.value = null
    }
  }
})

// --- Fetch Data ---
const fetchOrders = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/client/orders')
    orders.value = response.data.map((order: Order) => ({ ...order, expanded: false }))
  } catch (error) {
    console.error('Error fetching orders:', error)
    toast.error('Failed to load orders. Please try again.')
  } finally {
    isLoading.value = false
    isRefreshing.value = false
  }
}

onMounted(() => {
  fetchOrders()
})

// --- Computed Stats ---
const pendingCount = computed(() => orders.value.filter(o => o.status === 'pending').length)
const processingCount = computed(() => orders.value.filter(o => ['confirmed', 'prepared'].includes(o.status)).length)
const completedCount = computed(() => orders.value.filter(o => o.status === 'delivered').length)

// --- Computed Filters ---
const filteredOrders = computed(() => {
  let filtered = orders.value

  if (statusFilter.value !== 'all') {
    if (statusFilter.value === 'processing') {
      filtered = filtered.filter(order => ['confirmed', 'prepared'].includes(order.status))
    } else {
      filtered = filtered.filter(order => order.status === statusFilter.value)
    }
  }

  return filtered.slice(0, displayedOrders.value)
})

// --- Methods ---
const formatCurrency = (amount: string | number) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(Number(amount))
}

const formatDate = (dateString: string) => {
  if (!dateString) return '' 
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '' 
  
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  }).format(date)
}

const getStatusConfig = (status: string) => {
  const configs: Record<string, { color: string, text: string }> = {
    pending: { color: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400', text: 'Pending' },
    confirmed: { color: 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400', text: 'Confirmed' },
    prepared: { color: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400', text: 'Prepared' },
    shipped: { color: 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400', text: 'Shipped' },
    delivered: { color: 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400', text: 'Delivered' },
    cancelled: { color: 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400', text: 'Cancelled' },
  }
  return configs[status.toLowerCase()] || { color: 'bg-gray-100 text-gray-800', text: status }
}

const getProgressData = (status: string) => {
  switch (status.toLowerCase()) {
    case 'pending': return { value: 25, text: 'Order Placed', color: '[&>div]:bg-yellow-500' }
    case 'confirmed':
    case 'prepared': return { value: 50, text: 'Preparing your order', color: '[&>div]:bg-blue-500' }
    case 'shipped': return { value: 75, text: 'On the way to you', color: '[&>div]:bg-purple-500' }
    case 'delivered': return { value: 100, text: 'Delivered successfully', color: '[&>div]:bg-green-500' }
    default: return { value: 0, text: '', color: '[&>div]:bg-gray-500' }
  }
}

const refreshOrders = () => {
  isRefreshing.value = true
  fetchOrders()
}

const loadMoreOrders = () => {
  displayedOrders.value += 5
}

const toggleOrderDetails = (orderId: number) => {
  const order = orders.value.find(o => o.id === orderId)
  if (order) order.expanded = !order.expanded
}

const viewOrderDetails = (order: Order) => {
  selectedOrder.value = order
}

const cancelOrder = (orderId: number) => {
  if (!props.user) { isAuthAlertOpen.value = true; return; }
  toast.info('Cancellation requested. This feature is coming soon.')
}

const reorderItems = (orderId: number) => {
  if (!props.user) { isAuthAlertOpen.value = true; return; }
  toast.success('Items added to cart! (Demo)')
}

const trackOrder = (orderId: number) => {
  if (!props.user) { isAuthAlertOpen.value = true; return; }
  toast.info(`Tracking Order #${orderId}...`)
}

const downloadInvoice = () => {
  if (!props.user) { isAuthAlertOpen.value = true; return; }
  toast.info('Generating PDF invoice...')
}

// --- Reviews Logic ---
const openReviewModal = (orderId: number, item: OrderItem) => {
  if (!props.user) { isAuthAlertOpen.value = true; return; }

  reviewForm.value = {
    order_id: orderId,
    product_id: item.product?.id || null,
    product_name: item.product?.name || 'Product',
    rating: item.review_rating || 5,
    comment: item.review_comment || ''
  }
  isReviewModalOpen.value = true
}

const setRating = (rating: number) => {
  reviewForm.value.rating = rating
}

const submitReview = async () => {
  if (!reviewForm.value.order_id || !reviewForm.value.product_id) return

  isSubmittingReview.value = true
  try {
    const response = await api.post('/client/orders/reviews', {
      order_id: reviewForm.value.order_id,
      product_id: reviewForm.value.product_id,
      rating: reviewForm.value.rating,
      comment: reviewForm.value.comment
    })

    if (response.data.success) {
      toast.success('Review submitted successfully!')
      isReviewModalOpen.value = false
      
      if (selectedOrder.value) {
        const itemToUpdate = selectedOrder.value.items.find(i => i.product?.id === reviewForm.value.product_id)
        if (itemToUpdate) {
          itemToUpdate.is_reviewed = true
          itemToUpdate.review_rating = reviewForm.value.rating
          itemToUpdate.review_comment = reviewForm.value.comment
        }
      }
      
      fetchOrders() 
    }
  } catch (error) {
    console.error('Error submitting review:', error)
    toast.error('Failed to submit review. Please try again.')
  } finally {
    isSubmittingReview.value = false
  }
}
</script>

<template>
  <div class="min-h-screen ">
    <div class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10">
      <div class="container mx-auto px-4 md:px-8 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">My Orders</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Track your purchases and service requests</p>
          </div>
          
          <div class="flex items-center space-x-3 w-full md:w-auto">
            <div class="flex-1 md:w-[200px]">
              <Select v-model="statusFilter">
                <SelectTrigger class="w-full bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700">
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Orders</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="processing">Processing (Confirmed/Prepared)</SelectItem>
                  <SelectItem value="shipped">Shipped</SelectItem>
                  <SelectItem value="delivered">Delivered</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <Button
              variant="outline"
              size="icon"
              @click="refreshOrders"
              :disabled="isRefreshing"
              class="border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 shrink-0"
            >
              <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 md:px-8 py-8 max-w-7xl">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <Card class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-gray-200 dark:border-gray-700 overflow-hidden">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Orders</p>
              <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ orders.length }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-50 dark:bg-blue-900/20 rounded-full flex items-center justify-center shrink-0">
              <ShoppingBag class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-gray-200 dark:border-gray-700 overflow-hidden">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending</p>
              <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-500 mt-1">{{ pendingCount }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-50 dark:bg-yellow-900/20 rounded-full flex items-center justify-center shrink-0">
              <Clock class="w-6 h-6 text-yellow-600 dark:text-yellow-500" />
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-gray-200 dark:border-gray-700 overflow-hidden">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Processing</p>
              <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ processingCount }}</p>
            </div>
            <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-900/20 rounded-full flex items-center justify-center shrink-0">
              <Package class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-gray-200 dark:border-gray-700 overflow-hidden">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
              <p class="text-3xl font-bold text-green-600 dark:text-green-500 mt-1">{{ completedCount }}</p>
            </div>
            <div class="w-12 h-12 bg-green-50 dark:bg-green-900/20 rounded-full flex items-center justify-center shrink-0">
              <CheckCircle2 class="w-6 h-6 text-green-600 dark:text-green-500" />
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="space-y-6">
        
        <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
          <Loader2 class="w-10 h-10 animate-spin text-blue-500 mb-4" />
          <p class="text-gray-500">Loading your orders...</p>
        </div>

        <Card v-else-if="filteredOrders.length === 0" class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border-gray-200 dark:border-gray-700 p-12 text-center">
          <div class="w-20 h-20 mx-auto mb-6 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center text-gray-400">
            <ShoppingBag class="w-10 h-10" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No orders found</h3>
          <p class="text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-8">
            {{ !user ? 'Please log in to view and track your orders.' : (statusFilter === 'all' ? "Looks like you haven't placed any orders yet." : `No orders found matching the '${statusFilter}' status.`) }}
          </p>
          <div class="flex items-center justify-center gap-4">
            <Button v-if="statusFilter !== 'all'" @click="statusFilter = 'all'" variant="outline">
              Clear Filters
            </Button>
            <router-link v-if="user" to="/ECommerceClient/EccommerceShop">
              <Button class="bg-blue-600 hover:bg-blue-700 text-white">
                Start Shopping
              </Button>
            </router-link>
            <Button v-else @click="isAuthAlertOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white">
              Log In
            </Button>
          </div>
        </Card>

        <Card v-else v-for="order in filteredOrders" :key="order.id" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:shadow-md">
          
          <div class="p-5 md:p-6 border-b border-gray-100 dark:border-gray-700/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Order <span class="text-blue-600 dark:text-blue-400">{{ order.order_number }}</span></h3>
                <div class="flex items-center gap-3">
                  <Badge :class="[getStatusConfig(order.status).color, 'px-3 py-1 rounded-full text-xs font-semibold border-0']">
                    {{ getStatusConfig(order.status).text }}
                  </Badge>
                  <p class="text-sm text-gray-500 dark:text-gray-400 sm:hidden">
                    {{ formatDate(order.created_at) }}
                  </p>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">
                  Placed on {{ formatDate(order.created_at) }}
                </p>
              </div>
              
              <div class="flex justify-between items-center md:block md:text-right border-t border-gray-100 dark:border-gray-700/50 md:border-t-0 pt-4 md:pt-0">
                <p class="text-sm text-gray-500 dark:text-gray-400 md:hidden">Total Amount</p>
                <div>
                  <div class="text-2xl font-black text-gray-900 dark:text-white">{{ formatCurrency(order.grand_total) }}</div>
                  <p class="text-sm text-gray-500 dark:text-gray-400 text-right">{{ order.items.length }} item(s)</p>
                </div>
              </div>
            </div>
          </div>
          
          <div class="p-5 md:p-6">
            <div class="space-y-4">
              
              <div v-for="item in order.items.slice(0, 2)" :key="item.id" class="flex items-center">
                <div class="w-16 h-16 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center mr-4 shrink-0 overflow-hidden">
                  <img v-if="item.product?.image_url" :src="item.product.image_url" class="w-full h-full object-cover" />
                  <ImageIcon v-else class="w-6 h-6 text-gray-400" />
                </div>
                
                <div class="flex-1 min-w-0">
                  <h4 class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ item.product?.name || 'Unknown Product' }}</h4>
                  <div class="flex flex-col sm:flex-row sm:justify-between mt-1">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Qty: {{ item.quantity }}</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                      {{ formatCurrency(Number(item.price) * item.quantity) }}
                    </p>
                  </div>
                </div>
              </div>
              
              <div v-if="order.items.length > 2" class="pt-2">
                <Button variant="ghost" @click="toggleOrderDetails(order.id)" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium p-0 h-auto hover:bg-transparent">
                  <span>{{ order.expanded ? 'Hide extra items' : `+${order.items.length - 2} more items` }}</span>
                  <ChevronUp v-if="order.expanded" class="w-4 h-4 ml-1" />
                  <ChevronDown v-else class="w-4 h-4 ml-1" />
                </Button>
                
                <div v-if="order.expanded" class="mt-4 space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                  <div v-for="item in order.items.slice(2)" :key="item.id" class="flex items-center">
                    <div class="w-14 h-14 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 flex items-center justify-center mr-4 shrink-0 overflow-hidden">
                      <img v-if="item.product?.image_url" :src="item.product.image_url" class="w-full h-full object-cover" />
                      <ImageIcon v-else class="w-5 h-5 text-gray-400" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ item.product?.name || 'Unknown Product' }}</h4>
                      <div class="flex flex-col sm:flex-row sm:justify-between mt-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Qty: {{ item.quantity }}</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                          {{ formatCurrency(Number(item.price) * item.quantity) }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700/50">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <div class="flex flex-wrap gap-4 text-sm">
                  <div class="bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-lg flex items-center gap-2 border border-gray-100 dark:border-gray-800">
                    <CreditCard class="w-4 h-4 text-gray-500" />
                    <span class="font-medium text-gray-900 dark:text-gray-200 uppercase">{{ order.payment_method }}</span>
                  </div>
                  <div class="bg-gray-50 dark:bg-gray-900 px-3 py-1.5 rounded-lg flex items-center gap-2 border border-gray-100 dark:border-gray-800">
                    <MapPin class="w-4 h-4 text-gray-500" />
                    <span class="font-medium text-gray-900 dark:text-gray-200 max-w-[150px] truncate">{{ order.delivery_address }}</span>
                  </div>
                </div>
                
                <div class="flex flex-wrap gap-2">
                  <Button variant="outline" @click="viewOrderDetails(order)" class="border-blue-200 text-blue-700 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-400 dark:hover:bg-blue-900/30 flex-1 sm:flex-none">
                    <FileText class="w-4 h-4 mr-2" /> Details
                  </Button>
                  
                  <Button v-if="order.status === 'pending'" variant="outline" @click="cancelOrder(order.id)" class="border-red-200 text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-900/30 flex-1 sm:flex-none">
                    <XCircle class="w-4 h-4 mr-2" /> Cancel
                  </Button>
                  
                  <Button v-if="order.status === 'delivered'" @click="reorderItems(order.id)" class="bg-blue-600 hover:bg-blue-700 text-white flex-1 sm:flex-none">
                    <RefreshCw class="w-4 h-4 mr-2" /> Reorder
                  </Button>
                  
                  <Button v-if="order.status === 'shipped'" @click="trackOrder(order.id)" class="bg-purple-600 hover:bg-purple-700 text-white flex-1 sm:flex-none">
                    <Truck class="w-4 h-4 mr-2" /> Track
                  </Button>
                </div>
              </div>
              
              <div v-if="order.status !== 'cancelled'" class="mt-6 bg-gray-50 dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <div class="mb-2 flex justify-between text-sm">
                  <span class="font-medium text-gray-700 dark:text-gray-300 flex items-center gap-2">
                    <AlertCircle class="w-4 h-4 text-gray-400" /> Status
                  </span>
                  <span class="font-semibold text-gray-900 dark:text-white">{{ getProgressData(order.status).text }}</span>
                </div>
                <Progress 
                  :model-value="getProgressData(order.status).value" 
                  :class="['h-2.5', getProgressData(order.status).color]" 
                />
                <div class="flex justify-between mt-3 text-xs font-medium text-gray-400 dark:text-gray-500">
                  <span :class="{'text-blue-600 dark:text-blue-400': getProgressData(order.status).value >= 25}">Placed</span>
                  <span :class="{'text-blue-600 dark:text-blue-400': getProgressData(order.status).value >= 50}">Processing</span>
                  <span :class="{'text-blue-600 dark:text-blue-400': getProgressData(order.status).value >= 75}">Shipped</span>
                  <span :class="{'text-green-600 dark:text-green-400': getProgressData(order.status).value === 100}">Delivered</span>
                </div>
              </div>
            </div>
            
          </div>
        </Card>

        <div v-if="filteredOrders.length > 0 && displayedOrders < orders.length" class="mt-8 text-center">
          <Button @click="loadMoreOrders" variant="outline" class="border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 w-full sm:w-auto px-8">
            Load More Orders
          </Button>
        </div>
      </div>
    </div>

    <Dialog v-model:open="isModalOpen">
      <DialogContent class="sm:max-w-[800px] md:max-w-[950px] lg:max-w-[1100px] max-h-[90vh] overflow-y-auto w-[95vw] p-0 gap-0 rounded-2xl bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800" style="max-width: 1100px;">
        
        <div class="bg-white dark:bg-gray-800 p-6 md:p-8 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10 flex justify-between items-start gap-4">
          <DialogHeader class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
              <DialogTitle class="text-2xl font-black text-gray-900 dark:text-white">Order <span class="text-blue-600 dark:text-blue-400">{{ selectedOrder?.order_number }}</span></DialogTitle>
              <DialogDescription class="flex flex-wrap items-center mt-3 gap-3">
                <Badge :class="[getStatusConfig(selectedOrder?.status || '').color, 'px-3 py-1 rounded-full text-xs font-bold border-0']">
                  {{ getStatusConfig(selectedOrder?.status || '').text }}
                </Badge>
                <span class="text-sm font-medium text-gray-500 flex items-center gap-1.5"><Clock class="w-4 h-4"/> Placed on {{ formatDate(selectedOrder?.created_at || '') }}</span>
              </DialogDescription>
            </div>
          </DialogHeader>
          
          <Button 
            variant="ghost" 
            size="icon" 
            @click="isModalOpen = false" 
            class="shrink-0 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400"
          >
            <X class="w-5 h-5" />
          </Button>
        </div>

        <div class="p-6 md:p-8">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-6">
              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                  <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2"><Package class="w-5 h-5"/> Order Items</h4>
                </div>
                <div class="p-4 md:p-6 space-y-4">
                  <div v-for="item in selectedOrder?.items" :key="item.id" class="flex flex-col sm:flex-row sm:items-center gap-4 p-4 rounded-xl border border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/20">
                    <div class="w-20 h-20 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 flex items-center justify-center shrink-0 overflow-hidden shadow-sm">
                      <img v-if="item.product?.image_url" :src="item.product.image_url" class="w-full h-full object-cover" />
                      <ImageIcon v-else class="w-8 h-8 text-gray-300" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <h4 class="font-bold text-gray-900 dark:text-white truncate">{{ item.product?.name || 'Unknown Product' }}</h4>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 uppercase tracking-wider font-semibold">{{ item.product?.category || 'General' }}</p>
                      
                      <div class="flex items-end justify-between mt-3">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                          Qty: <span class="text-gray-900 dark:text-white">{{ item.quantity }}</span> × {{ formatCurrency(item.price) }}
                        </p>
                        <p class="text-lg font-black text-gray-900 dark:text-white">
                          {{ formatCurrency(Number(item.price) * item.quantity) }}
                        </p>
                      </div>

                      <div v-if="selectedOrder?.status === 'delivered'" class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <Button
                          v-if="!item.is_reviewed"
                          @click="openReviewModal(selectedOrder.id, item)"
                          size="sm"
                          variant="outline"
                          class="text-amber-600 border-amber-200 hover:bg-amber-50 dark:hover:bg-amber-900/20"
                        >
                          <Star class="w-4 h-4 mr-1.5" /> Write a Review
                        </Button>
                        <div v-else class="flex flex-col gap-1.5 bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                          <div class="flex items-center justify-between">
                            <div class="flex items-center text-amber-500">
                              <Star v-for="i in 5" :key="i" :class="['w-4 h-4', i <= (item.review_rating || 0) ? 'fill-current' : 'text-gray-300 dark:text-gray-600']" />
                              <span class="text-xs text-gray-500 ml-2 font-bold uppercase tracking-wider">Reviewed</span>
                            </div>
                            <Button variant="link" size="sm" class="text-xs text-blue-500 h-auto p-0 m-0" @click="openReviewModal(selectedOrder.id, item)">Edit</Button>
                          </div>
                          <p v-if="item.review_comment" class="text-sm text-gray-600 dark:text-gray-300 italic mt-1">"{{ item.review_comment }}"</p>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                  <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2"><MapPin class="w-5 h-5"/> Shipping Information</h4>
                </div>
                <div class="p-4 md:p-6">
                  <p class="text-gray-700 dark:text-gray-300 leading-relaxed font-medium bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    {{ selectedOrder?.delivery_address }}
                  </p>
                </div>
              </div>
            </div>

            <div class="lg:col-span-1 space-y-6">
              
              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                  <h4 class="font-bold text-gray-900 dark:text-white">Order Summary</h4>
                </div>
                <div class="p-5 space-y-4 text-sm font-medium">
                  <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span class="text-gray-900 dark:text-gray-200">{{ formatCurrency(selectedOrder?.total_amount || 0) }}</span>
                  </div>
                  <div class="flex justify-between text-gray-600 dark:text-gray-400">
                    <span>Shipping Fee</span>
                    <span class="text-gray-900 dark:text-gray-200">{{ formatCurrency(selectedOrder?.shipping_fee || 0) }}</span>
                  </div>
                  
                  <div class="pt-4 border-t border-dashed border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-end">
                      <span class="text-base font-bold text-gray-900 dark:text-white">Grand Total</span>
                      <span class="text-2xl font-black text-blue-600 dark:text-blue-400">{{ formatCurrency(selectedOrder?.grand_total || 0) }}</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                  <h4 class="font-bold text-gray-900 dark:text-white">Payment Method</h4>
                </div>
                <div class="p-5">
                  <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                    <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                      <CreditCard class="w-6 h-6 text-gray-600 dark:text-gray-300" />
                    </div>
                    <div>
                      <p class="font-bold text-gray-900 dark:text-white uppercase tracking-wider">{{ selectedOrder?.payment_method }}</p>
                      <p class="text-xs text-green-600 dark:text-green-400 font-semibold mt-0.5">Payment Verified</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <Button @click="downloadInvoice" class="w-full bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-900/20 py-6 text-base font-bold rounded-xl">
                <FileText class="w-5 h-5 mr-2" /> Download Invoice
              </Button>
            </div>

          </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="isReviewModalOpen">
      <DialogContent class="sm:max-w-[500px] p-6 rounded-2xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800">
        <DialogHeader class="mb-4">
          <DialogTitle class="text-xl font-bold text-gray-900 dark:text-white">Review Product</DialogTitle>
          <DialogDescription class="text-gray-500 dark:text-gray-400 mt-2">
            How was your experience with <span class="font-bold text-gray-800 dark:text-gray-200">{{ reviewForm.product_name }}</span>?
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-6">
          <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">Overall Rating</label>
            <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 w-max">
              <button
                v-for="star in 5"
                :key="star"
                @click="setRating(star)"
                class="focus:outline-none transition-transform hover:scale-110 active:scale-95"
              >
                <Star
                  :class="['w-8 h-8 drop-shadow-sm', star <= reviewForm.rating ? 'text-amber-400 fill-current' : 'text-gray-300 dark:text-gray-600']"
                />
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Write your comment (Optional)</label>
            <textarea
              v-model="reviewForm.comment"
              rows="4"
              placeholder="Share your thoughts, what you liked or disliked..."
              class="w-full p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none transition-all shadow-inner"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
          <Button variant="outline" @click="isReviewModalOpen = false" class="border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 font-bold rounded-xl">Cancel</Button>
          <Button @click="submitReview" :disabled="isSubmittingReview" class="bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/20 px-6">
            <Loader2 v-if="isSubmittingReview" class="w-4 h-4 mr-2 animate-spin" />
            {{ isSubmittingReview ? 'Submitting...' : 'Submit Review' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isAuthAlertOpen" class="fixed inset-0 z-[9999] bg-gray-900/60 backdrop-blur-md pointer-events-none"></div>
      </transition>
      
      <AlertDialog :open="isAuthAlertOpen" @update:open="isAuthAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Authentication Required
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3">
              You must be logged in to view and manage your orders. Please log in or create an account to continue.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isAuthAlertOpen = false" class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="router.push('/Landing/logIn')" class="rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white h-11 px-6 shadow-md shadow-blue-600/20">
              Log In
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Teleport>

  </div>
</template>