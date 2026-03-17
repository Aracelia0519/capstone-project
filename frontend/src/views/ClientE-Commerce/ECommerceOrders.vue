<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix for default Leaflet icon paths in Vue
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow
})

// Shadcn UI Components
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'

// Icons
import { 
  ShoppingBag, Clock, Package, CheckCircle2, RefreshCw, Search, ChevronDown, ChevronUp, Image as ImageIcon, Truck, XCircle, FileText, AlertCircle, MapPin, CreditCard, Loader2, X, Star, Camera, Upload, Navigation, AlertTriangle, Banknote, Menu, Info,
  RotateCcw, Send, Paperclip
} from 'lucide-vue-next'

// Import Echo and Pusher
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

const router = useRouter()

const props = defineProps({
  user: { type: Object, default: null }
})

// --- State ---
const orders = ref([])
const isLoading = ref(true)
const statusFilter = ref('all')
const displayedOrders = ref(5)
const selectedOrder = ref(null)
const isDetailsModalOpen = ref(false)
const isRefreshing = ref(false)
const isAuthAlertOpen = ref(false)

const isReviewModalOpen = ref(false)
const isSubmittingReview = ref(false)
const reviewForm = ref({ order_id: null, product_id: null, product_name: '', rating: 5, comment: '' })

// Pick-Up Logic 
const isPickUpTrackingActive = ref(false)
const isFetchingPickUp = ref(false)
const isSubmittingPickUp = ref(false)
const isMapDrawerOpen = ref(true)
const trackedOrder = ref(null)
const locationGranted = ref(false)
const locationError = ref('')
const currentPosition = ref(null)
let watchId = null
let leafletMap = null
let userMarker = null
let targetMarker = null
let routeLine = null
let lastRoutedPosition = null
const pickUpDetails = ref({ preparation_proof: null, distributor_lat: null, distributor_lng: null, distributor_address: null })
const pickupProofFile = ref(null)
const pickupProofPreview = ref(null)
const paymentProofFile = ref(null)
const paymentProofPreview = ref(null)

// --- Return Chat State ---
const isReturnChatOpen = ref(false)
const activeReturnItem = ref(null)
const returnRequest = ref(null)
const returnMessages = ref([])
const selectedReturnReason = ref('')
const customReturnReason = ref('')
const returnProofFile = ref(null)
const returnProofPreview = ref(null)
const isSubmittingReturn = ref(false)
const chatMessage = ref('')
const chatImageFile = ref(null)
const chatImagePreview = ref(null)
const isSendingMessage = ref(false)
const trackingNumber = ref('')
const trackingProofFile = ref(null)
const isSubmittingTracking = ref(false)

// --- Initialize WebSockets ---
const initWebSockets = (userId) => {
  window.Pusher = Pusher
  const token = localStorage.getItem('auth_token')
  
  const apiUrl = import.meta.env?.VITE_APP_API_URL || 'http://127.0.0.1:8000/api'

  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'fade6ce6ed8705f2ace4',
    cluster: 'ap1',
    forceTLS: true,
    authEndpoint: `${apiUrl}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    }
  })

  // Listen to private channel for return messages
  window.Echo.private(`return.chat.${userId}`)
    .listen('.ReturnMessageSent', (e) => {
      // If return chat is open and matches the current return request
      if (isReturnChatOpen.value && returnRequest.value && e.message.return_request_id === returnRequest.value.id) {
        returnMessages.value.push(e.message)
        scrollToBottom()
      } else {
        // Show a toast notification for new messages when chat is closed
        toast.info('New message in return chat')
      }
    })
}

// --- Fetch Data ---
const fetchOrders = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/client/orders')
    orders.value = response.data.map(order => ({ ...order, expanded: false }))
  } catch (error) {
    toast.error('Failed to load orders. Please try again.')
  } finally {
    isLoading.value = false
    isRefreshing.value = false
  }
}

onMounted(() => {
  fetchOrders()
  if (props.user?.id) {
    initWebSockets(props.user.id)
  }
})

onUnmounted(() => {
  if (watchId !== null) navigator.geolocation.clearWatch(watchId)
  if (leafletMap) leafletMap.remove()
  if (window.Echo && props.user) {
    window.Echo.leave(`return.chat.${props.user.id}`)
  }
})

// --- Computed Stats & Filters ---
const pendingCount = computed(() => orders.value.filter(o => o.status === 'pending').length)
const processingCount = computed(() => orders.value.filter(o => ['confirmed', 'prepared', 'ready_for_pickup'].includes(o.status)).length)
const completedCount = computed(() => orders.value.filter(o => o.status === 'delivered').length)

const filteredOrders = computed(() => {
  let filtered = orders.value
  if (statusFilter.value !== 'all') {
    if (statusFilter.value === 'processing') {
      filtered = filtered.filter(order => ['confirmed', 'prepared', 'ready_for_pickup'].includes(order.status))
    } else {
      filtered = filtered.filter(order => order.status === statusFilter.value)
    }
  }
  return filtered.slice(0, displayedOrders.value)
})

// --- Pick-Up Functions ---
const distanceToTarget = computed(() => {
  if (!currentPosition.value || !pickUpDetails.value.distributor_lat || !pickUpDetails.value.distributor_lng) return null
  return calculateDistance(currentPosition.value.lat, currentPosition.value.lng, pickUpDetails.value.distributor_lat, pickUpDetails.value.distributor_lng)
})
const isWithinRange = computed(() => distanceToTarget.value !== null && distanceToTarget.value <= 500)
const currentCoordsDisplay = computed(() => currentPosition.value ? `${currentPosition.value.lat.toFixed(5)}, ${currentPosition.value.lng.toFixed(5)}` : 'Locating...')

const calculateDistance = (lat1, lon1, lat2, lon2) => {
  const R = 6371e3
  const p1 = lat1 * Math.PI / 180
  const p2 = lat2 * Math.PI / 180
  const dp = (lat2 - lat1) * Math.PI / 180
  const dl = (lon2 - lon1) * Math.PI / 180
  const a = Math.sin(dp/2) * Math.sin(dp/2) + Math.cos(p1) * Math.cos(p2) * Math.sin(dl/2) * Math.sin(dl/2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))
  return R * c
}

const openPickUpTracking = async (order) => {
  if (!props.user) { isAuthAlertOpen.value = true; return; }
  trackedOrder.value = order
  isPickUpTrackingActive.value = true
  isFetchingPickUp.value = true
  isMapDrawerOpen.value = true
  pickUpDetails.value = { preparation_proof: null, distributor_lat: null, distributor_lng: null, distributor_address: null }
  removePickupProof()
  removePaymentProof()
  try {
    const response = await api.get(`/client/orders/${order.id}/pickup-details`)
    if (response.data.success) {
      pickUpDetails.value = response.data
      requestLocationAndStartTracking()
    }
  } catch (error) {
    toast.error('Failed to fetch pick-up details.')
  } finally {
    isFetchingPickUp.value = false
  }
}

const closePickUpTracking = () => {
  isPickUpTrackingActive.value = false
  trackedOrder.value = null
  locationGranted.value = false
  if (watchId !== null) {
    navigator.geolocation.clearWatch(watchId)
    watchId = null
  }
  if (leafletMap) {
    leafletMap.remove()
    leafletMap = null
  }
}

const requestLocationAndStartTracking = () => {
  locationError.value = ''
  if (!navigator.geolocation) {
    locationError.value = "Geolocation is not supported by your browser."
    return
  }
  navigator.geolocation.getCurrentPosition(
    (position) => {
      locationGranted.value = true
      currentPosition.value = { lat: position.coords.latitude, lng: position.coords.longitude }
      nextTick(() => {
        initMap()
        startTracking()
      })
    },
    (error) => {
      locationError.value = "Location access denied. Please enable location to verify pickup."
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  )
}

const startTracking = () => {
  let isFirstLoad = true
  watchId = navigator.geolocation.watchPosition(
    (position) => {
      const newPos = { lat: position.coords.latitude, lng: position.coords.longitude }
      currentPosition.value = newPos
      if (userMarker) {
        userMarker.setLatLng([newPos.lat, newPos.lng])
        drawRoute(false)
        if (isFirstLoad) {
          if(leafletMap && pickUpDetails.value.distributor_lat) {
            const bounds = L.latLngBounds([newPos.lat, newPos.lng], [pickUpDetails.value.distributor_lat, pickUpDetails.value.distributor_lng])
            leafletMap.fitBounds(bounds, { padding: [50, 50] })
          }
          isFirstLoad = false
        }
      }
    },
    (error) => console.error(error),
    { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
  )
}

const initMap = () => {
  if (!currentPosition.value || leafletMap) return
  leafletMap = L.map('pickup-map', { zoomControl: false }).setView([currentPosition.value.lat, currentPosition.value.lng], 15)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(leafletMap)
  
  const userIcon = L.divIcon({
    html: `<div class="h-10 w-10 bg-white rounded-full border-2 border-blue-600 flex items-center justify-center shadow-[0_0_15px_rgba(37,99,235,0.7)] relative">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600">
        <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>
      </svg>
      <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[8px] border-t-blue-600"></div>
    </div>`,
    className: 'bg-transparent',
    iconSize: [40, 48],
    iconAnchor: [20, 48]
  })
  userMarker = L.marker([currentPosition.value.lat, currentPosition.value.lng], { icon: userIcon }).addTo(leafletMap)

  if (pickUpDetails.value.distributor_lat && pickUpDetails.value.distributor_lng) {
    const targetIcon = L.divIcon({
      html: `<div class="h-8 w-8 bg-green-500 rounded-full border-2 border-white flex items-center justify-center shadow-[0_0_20px_rgba(34,197,94,0.6)]">
        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>`,
      className: 'bg-transparent',
      iconSize: [32, 32],
      iconAnchor: [16, 16]
    })
    targetMarker = L.marker([pickUpDetails.value.distributor_lat, pickUpDetails.value.distributor_lng], { icon: targetIcon }).addTo(leafletMap)
    targetMarker.bindPopup(`<b class="text-gray-900">Distributor Shop</b><br><span class="text-gray-600">${pickUpDetails.value.distributor_address}</span>`)
  }
  drawRoute(true)
}

const drawRoute = async (force = false) => {
  if (!leafletMap || !currentPosition.value || !pickUpDetails.value.distributor_lat || !pickUpDetails.value.distributor_lng) return
  if (!force && lastRoutedPosition && calculateDistance(currentPosition.value.lat, currentPosition.value.lng, lastRoutedPosition.lat, lastRoutedPosition.lng) < 20) return
  lastRoutedPosition = { lat: currentPosition.value.lat, lng: currentPosition.value.lng }
  const color = '#22c55e'
  try {
    const res = await fetch(`https://router.project-osrm.org/route/v1/driving/${currentPosition.value.lng},${currentPosition.value.lat};${pickUpDetails.value.distributor_lng},${pickUpDetails.value.distributor_lat}?overview=full&geometries=geojson`)
    const data = await res.json()
    if (data.code === 'Ok' && data.routes.length > 0) {
      if (routeLine) leafletMap.removeLayer(routeLine)
      routeLine = L.polyline(data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]), {
        color: color,
        weight: 5,
        opacity: 0.8,
        lineCap: 'round',
        lineJoin: 'round'
      }).addTo(leafletMap)
    } else drawFallbackRoute(color)
  } catch {
    drawFallbackRoute(color)
  }
}

const drawFallbackRoute = (color) => {
  if (routeLine && leafletMap) leafletMap.removeLayer(routeLine)
  if (leafletMap && currentPosition.value && pickUpDetails.value.distributor_lat) {
    routeLine = L.polyline([[currentPosition.value.lat, currentPosition.value.lng], [pickUpDetails.value.distributor_lat, pickUpDetails.value.distributor_lng]], {
      color: color,
      weight: 4,
      dashArray: '10, 10',
      opacity: 0.8,
      lineCap: 'round',
      lineJoin: 'round'
    }).addTo(leafletMap)
  }
}

const submitPickUp = async () => {
  if (!trackedOrder.value || !pickupProofFile.value || !paymentProofFile.value) {
    toast.error('Both Proof of Pick-Up and Proof of Payment are required.')
    return
  }
  isSubmittingPickUp.value = true
  const formData = new FormData()
  formData.append('proof_of_pickup', pickupProofFile.value)
  formData.append('proof_of_payment', paymentProofFile.value)
  try {
    const response = await api.post(`/client/orders/${trackedOrder.value.id}/pickup-submit`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (response.data.success) {
      toast.success('Pick-up confirmed successfully!')
      closePickUpTracking()
      fetchOrders()
    }
  } catch (error) {
    toast.error('Failed to submit pick-up. Please try again.')
  } finally {
    isSubmittingPickUp.value = false
  }
}

// --- Utils ---
const formatCurrency = (amount) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(amount))

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return isNaN(date.getTime()) ? '' : new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit'
  }).format(date)
}

const getStatusConfig = (status) => {
  const configs = {
    pending: { color: 'bg-yellow-100 text-yellow-800', text: 'Pending' },
    confirmed: { color: 'bg-blue-100 text-blue-800', text: 'Confirmed' },
    prepared: { color: 'bg-indigo-100 text-indigo-800', text: 'Prepared' },
    ready_for_pickup: { color: 'bg-teal-100 text-teal-800', text: 'Ready For Pick-Up' },
    shipped: { color: 'bg-purple-100 text-purple-800', text: 'Shipped' },
    delivered: { color: 'bg-green-100 text-green-800', text: 'Delivered' },
    cancelled: { color: 'bg-red-100 text-red-800', text: 'Cancelled' }
  }
  return configs[status.toLowerCase()] || { color: 'bg-gray-100 text-gray-800', text: status }
}

const getProgressData = (status) => {
  switch (status.toLowerCase()) {
    case 'pending': return { value: 25, text: 'Order Placed', color: '[&>div]:bg-yellow-500' }
    case 'confirmed':
    case 'prepared': return { value: 50, text: 'Preparing your order', color: '[&>div]:bg-blue-500' }
    case 'ready_for_pickup': return { value: 75, text: 'Waiting for pick-up', color: '[&>div]:bg-teal-500' }
    case 'shipped': return { value: 75, text: 'On the way to you', color: '[&>div]:bg-purple-500' }
    case 'delivered': return { value: 100, text: 'Delivered successfully', color: '[&>div]:bg-green-500' }
    default: return { value: 0, text: '', color: '[&>div]:bg-gray-500' }
  }
}

const getFullImageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http') || path.startsWith('data:')) return path
  const baseUrl = api.defaults.baseURL ? api.defaults.baseURL.replace(/\/api\/?$/, '') : 'http://127.0.0.1:8000'
  return `${baseUrl}/${path.startsWith('/') ? path.substring(1) : path}`
}

const openImageInNewTab = (url) => {
  if (url) window.open(url, '_blank')
}

const refreshOrders = () => {
  isRefreshing.value = true
  fetchOrders()
}

const loadMoreOrders = () => {
  displayedOrders.value += 5
}

const toggleOrderDetails = (orderId) => {
  const order = orders.value.find(o => o.id === orderId)
  if (order) order.expanded = !order.expanded
}

const viewOrderDetails = (order) => {
  selectedOrder.value = order
  isDetailsModalOpen.value = true
}

const cancelOrder = (orderId) => {
  if (!props.user) { isAuthAlertOpen.value = true; return }
  toast.info('Cancellation requested.')
}

const reorderItems = (orderId) => {
  if (!props.user) { isAuthAlertOpen.value = true; return }
  toast.success('Items added to cart!')
}

const trackOrder = (orderId) => {
  if (!props.user) { isAuthAlertOpen.value = true; return }
  toast.info(`Tracking Order #${orderId}...`)
}

const downloadInvoice = () => {
  if (!props.user) { isAuthAlertOpen.value = true; return }
  toast.info('Generating PDF invoice...')
}

// --- Reviews Logic ---
const openReviewModal = (orderId, item) => {
  if (!props.user) { isAuthAlertOpen.value = true; return }
  reviewForm.value = {
    order_id: orderId,
    product_id: item.product?.id || null,
    product_name: item.product?.name || 'Product',
    rating: item.review_rating || 5,
    comment: item.review_comment || ''
  }
  isReviewModalOpen.value = true
}

const setRating = (rating) => {
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
    toast.error('Failed to submit review.')
  } finally {
    isSubmittingReview.value = false
  }
}

// --- Return Chat Helper Functions ---
const handleReturnProofUpload = (e) => {
  const target = e.target
  if (target.files && target.files[0]) {
    returnProofFile.value = target.files[0]
    returnProofPreview.value = URL.createObjectURL(target.files[0])
  }
}

const removeReturnProof = () => {
  returnProofFile.value = null
  if (returnProofPreview.value) {
    URL.revokeObjectURL(returnProofPreview.value)
    returnProofPreview.value = null
  }
}

const handleTrackingProofUpload = (e) => {
  const target = e.target
  if (target.files && target.files[0]) trackingProofFile.value = target.files[0]
}

const handlePickupUpload = (e) => {
  const target = e.target
  if (target.files && target.files[0]) {
    pickupProofFile.value = target.files[0]
    pickupProofPreview.value = URL.createObjectURL(target.files[0])
  }
}

const removePickupProof = () => {
  pickupProofFile.value = null
  if (pickupProofPreview.value) {
    URL.revokeObjectURL(pickupProofPreview.value)
    pickupProofPreview.value = null
  }
}

const handlePaymentUpload = (e) => {
  const target = e.target
  if (target.files && target.files[0]) {
    paymentProofFile.value = target.files[0]
    paymentProofPreview.value = URL.createObjectURL(target.files[0])
  }
}

const removePaymentProof = () => {
  paymentProofFile.value = null
  if (paymentProofPreview.value) {
    URL.revokeObjectURL(paymentProofPreview.value)
    paymentProofPreview.value = null
  }
}

const handleChatImageUpload = (e) => {
  const target = e.target
  if (target.files && target.files[0]) {
    chatImageFile.value = target.files[0]
    chatImagePreview.value = URL.createObjectURL(target.files[0])
  }
}

const removeChatImage = () => {
  chatImageFile.value = null
  if (chatImagePreview.value) {
    URL.revokeObjectURL(chatImagePreview.value)
    chatImagePreview.value = null
  }
  const fileInput = document.getElementById('chatImg')
  if (fileInput) fileInput.value = ''
}

const scrollToBottom = () => {
  nextTick(() => {
    const container = document.getElementById('returnChatContainer')
    if (container) container.scrollTop = container.scrollHeight
  })
}

const openReturnChat = async (item) => {
  if (!props.user) { isAuthAlertOpen.value = true; return }
  isDetailsModalOpen.value = false

  activeReturnItem.value = item
  isReturnChatOpen.value = true
  returnRequest.value = null
  returnMessages.value = []
  selectedReturnReason.value = ''
  customReturnReason.value = ''
  removeReturnProof()
  removeChatImage()

  try {
    const res = await api.get(`/client/orders/items/${item.id}/return-chat`)
    if (res.data.success && res.data.request) {
      returnRequest.value = res.data.request
      returnMessages.value = res.data.messages || []
      scrollToBottom()
    }
  } catch (error) {
    console.error(error)
  }
}

const closeReturnChat = () => {
  isReturnChatOpen.value = false
}

const submitInitialReturn = async () => {
  const finalReason = selectedReturnReason.value === 'Others' ? customReturnReason.value : selectedReturnReason.value

  if (!activeReturnItem.value || !finalReason || !returnProofFile.value) return
  isSubmittingReturn.value = true
  const fd = new FormData()
  fd.append('order_item_id', activeReturnItem.value.id.toString())
  fd.append('reason', finalReason)
  fd.append('proof_image', returnProofFile.value)

  try {
    const res = await api.post('/client/orders/return-request', fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data.success) {
      toast.success('Return request submitted')
      returnRequest.value = res.data.request
      const chatRes = await api.get(`/client/orders/items/${activeReturnItem.value.id}/return-chat`)
      if (chatRes.data.success) {
        returnMessages.value = chatRes.data.messages || []
        scrollToBottom()
      }
    }
  } catch (error) {
    toast.error('Failed to submit return request')
  } finally {
    isSubmittingReturn.value = false
  }
}

const sendChatMessage = async () => {
  if (!returnRequest.value || (!chatMessage.value.trim() && !chatImageFile.value)) return
  isSendingMessage.value = true
  const fd = new FormData()
  if (chatMessage.value.trim()) fd.append('message', chatMessage.value)
  if (chatImageFile.value) fd.append('image', chatImageFile.value)

  try {
    const res = await api.post(`/client/orders/returns/${returnRequest.value.id}/message`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data.success) {
      returnMessages.value.push(res.data.data)
      chatMessage.value = ''
      removeChatImage()
      scrollToBottom()
    }
  } catch (error) {
    toast.error('Failed to send message')
  } finally {
    isSendingMessage.value = false
  }
}

const submitTrackingInfo = async () => {
  if (!returnRequest.value || !trackingNumber.value || !trackingProofFile.value) return
  isSubmittingTracking.value = true
  const fd = new FormData()
  fd.append('tracking_number', trackingNumber.value)
  fd.append('tracking_proof', trackingProofFile.value)
  try {
    const res = await api.post(`/client/orders/returns/${returnRequest.value.id}/tracking`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if (res.data.success) {
      toast.success('Tracking info submitted')
      returnRequest.value = res.data.request
      returnMessages.value.push(res.data.message)
      trackingNumber.value = ''
      trackingProofFile.value = null
      scrollToBottom()
    }
  } catch (error) {
    toast.error('Failed to submit tracking')
  } finally {
    isSubmittingTracking.value = false
  }
}
</script>

<template>
  <div class="min-h-screen">
    <div class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-800 sticky top-0 z-10">
      <div class="container mx-auto px-4 md:px-8 py-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">My Orders</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Track your purchases and service requests</p>
          </div>
          
          <div class="flex items-center space-x-3 w-full md:w-auto">
            <div class="flex-1 md:w-50">
              <Select v-model="statusFilter">
                <SelectTrigger class="w-full bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700">
                  <SelectValue placeholder="Filter by status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Orders</SelectItem>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="processing">Processing (Confirmed/Prepared/Pick-Up)</SelectItem>
                  <SelectItem value="shipped">Shipped</SelectItem>
                  <SelectItem value="delivered">Delivered</SelectItem>
                  <SelectItem value="cancelled">Cancelled</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <Button variant="outline" size="icon" @click="refreshOrders" :disabled="isRefreshing" class="border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:text-blue-600 shrink-0">
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
            <Button v-if="statusFilter !== 'all'" @click="statusFilter = 'all'" variant="outline">Clear Filters</Button>
            <router-link v-if="user" to="/ECommerceClient/EccommerceShop">
              <Button class="bg-blue-600 hover:bg-blue-700 text-white">Start Shopping</Button>
            </router-link>
            <Button v-else @click="isAuthAlertOpen = true" class="bg-blue-600 hover:bg-blue-700 text-white">Log In</Button>
          </div>
        </Card>

        <Card v-else v-for="order in filteredOrders" :key="order.id" class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border-gray-200 dark:border-gray-700 overflow-hidden transition-all hover:shadow-md">
          <div class="p-5 md:p-6 border-b border-gray-100 dark:border-gray-700/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                  Order <span class="text-blue-600 dark:text-blue-400">{{ order.order_number }}</span>
                </h3>
                <div class="flex items-center gap-3">
                  <Badge :class="[getStatusConfig(order.status).color, 'px-3 py-1 rounded-full text-xs font-semibold border-0']">
                    {{ getStatusConfig(order.status).text }}
                  </Badge>
                  <p class="text-sm text-gray-500 dark:text-gray-400 sm:hidden">{{ formatDate(order.created_at) }}</p>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 hidden sm:block">Placed on {{ formatDate(order.created_at) }}</p>
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
                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ formatCurrency(Number(item.price) * item.quantity) }}</p>
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
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-200">{{ formatCurrency(Number(item.price) * item.quantity) }}</p>
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
                    <span class="font-medium text-gray-900 dark:text-gray-200 max-w-37.5 truncate">{{ order.delivery_address }}</span>
                  </div>
                </div>
                <div class="flex flex-wrap gap-2">
                  <Button variant="outline" @click="viewOrderDetails(order)" class="border-blue-200 text-blue-700 hover:bg-blue-50 dark:border-blue-900 dark:text-blue-400 flex-1 sm:flex-none">
                    <FileText class="w-4 h-4 mr-2" /> Details
                  </Button>
                  <Button v-if="order.status === 'pending'" variant="outline" @click="cancelOrder(order.id)" class="border-red-200 text-red-700 hover:bg-red-50 flex-1 sm:flex-none">
                    <XCircle class="w-4 h-4 mr-2" /> Cancel
                  </Button>
                  <Button v-if="order.status === 'delivered'" @click="reorderItems(order.id)" class="bg-blue-600 hover:bg-blue-700 text-white flex-1 sm:flex-none">
                    <RefreshCw class="w-4 h-4 mr-2" /> Reorder
                  </Button>
                  <Button v-if="(order.payment_method === 'pick-up' || order.payment_method === 'pickup') && (order.status === 'ready_for_pickup' || order.status === 'prepared')" @click="openPickUpTracking(order)" class="bg-teal-600 hover:bg-teal-700 text-white flex-1 sm:flex-none">
                    <MapPin class="w-4 h-4 mr-2" /> View Pick-Up
                  </Button>
                  <Button v-else-if="order.status === 'shipped'" @click="trackOrder(order.id)" class="bg-purple-600 hover:bg-purple-700 text-white flex-1 sm:flex-none">
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
                <Progress :model-value="getProgressData(order.status).value" :class="['h-2.5', getProgressData(order.status).color]" />
                <div class="flex justify-between mt-3 text-xs font-medium text-gray-400 dark:text-gray-500">
                  <span :class="{'text-blue-600 dark:text-blue-400': getProgressData(order.status).value >= 25}">Placed</span>
                  <span :class="{'text-blue-600 dark:text-blue-400': getProgressData(order.status).value >= 50}">Processing</span>
                  <span :class="{
                    'text-teal-600 dark:text-teal-400': getProgressData(order.status).value >= 75 && (order.payment_method === 'pick-up' || order.payment_method === 'pickup'),
                    'text-purple-600 dark:text-purple-400': getProgressData(order.status).value >= 75 && order.payment_method !== 'pick-up' && order.payment_method !== 'pickup'
                  }">
                    {{ (order.payment_method === 'pick-up' || order.payment_method === 'pickup') ? 'Ready' : 'Shipped' }}
                  </span>
                  <span :class="{'text-green-600 dark:text-green-400': getProgressData(order.status).value === 100}">Delivered</span>
                </div>
              </div>
            </div>
          </div>
        </Card>
        <div v-if="filteredOrders.length > 0 && displayedOrders < orders.length" class="mt-8 text-center">
          <Button @click="loadMoreOrders" variant="outline" class="w-full sm:w-auto px-8">Load More Orders</Button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
        <div v-if="isPickUpTrackingActive" class="fixed inset-0 z-9999 flex flex-col overflow-hidden bg-gray-900 text-gray-100 font-sans">
          <div v-if="!locationGranted" class="absolute inset-0 z-50 flex items-center justify-center backdrop-blur-md bg-gray-900/60 p-4">
            <div class="flex flex-col items-center justify-center flex-1 w-full max-w-md mx-auto text-center space-y-6">
              <Loader2 v-if="isFetchingPickUp" class="w-16 h-16 text-blue-500 animate-spin" />
              <div v-else class="relative">
                <div class="absolute -inset-4 bg-teal-500/20 rounded-full animate-pulse blur-xl"></div>
                <div class="bg-gray-900/40 p-6 rounded-full relative shadow-inner border border-teal-500/30">
                  <MapPin class="w-16 h-16 text-teal-400" />
                </div>
              </div>
              <div class="space-y-2">
                <h1 class="text-3xl font-black tracking-tight text-white">{{ isFetchingPickUp ? 'Loading Tracker' : 'Location Required' }}</h1>
                <p class="text-gray-400 text-sm md:text-base px-4">To trace your path to the shop and validate your pick-up, GPS access is needed.</p>
              </div>
              <Alert v-if="locationError" variant="destructive" class="text-left w-full bg-red-900/20 border-red-900/50 text-red-300">
                <AlertTriangle class="h-4 w-4" />
                <AlertTitle>Access Denied</AlertTitle>
                <AlertDescription>{{ locationError }}</AlertDescription>
              </Alert>
              <div class="flex gap-4 w-full sm:w-auto">
                <Button @click="closePickUpTracking" variant="outline" size="lg" class="bg-gray-800 text-white hover:bg-gray-700 border-gray-700">Cancel</Button>
                <Button @click="requestLocationAndStartTracking" size="lg" :disabled="isFetchingPickUp" class="flex-1 bg-teal-600 hover:bg-teal-700 text-white shadow-lg shadow-teal-900/20">
                  <Navigation class="mr-2 h-5 w-5" /> Enable GPS Access
                </Button>
              </div>
            </div>
          </div>
          <div id="pickup-map" class="absolute inset-0 z-0"></div>
          <div v-if="locationGranted" class="absolute inset-0 z-10 pointer-events-none flex flex-col justify-between">
            <div class="p-4 md:p-6 pt-12 md:pt-6 flex justify-between items-start w-full">
              <div class="flex flex-col gap-2 pointer-events-auto max-w-[65%] sm:max-w-sm">
                <div class="bg-gray-900/80 backdrop-blur-md border border-gray-800 rounded-full py-1.5 px-3 shadow-lg flex items-center gap-2 w-max">
                  <div class="relative flex h-2.5 w-2.5 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-teal-500"></span>
                  </div>
                  <span class="text-xs font-medium text-gray-200 truncate font-mono">{{ currentCoordsDisplay }}</span>
                </div>
                <div class="bg-gray-900/80 backdrop-blur-md border border-gray-800 rounded-xl p-3 shadow-lg flex items-center gap-3">
                  <div class="bg-teal-500/20 p-2 rounded-lg shrink-0">
                    <Package class="h-5 w-5 text-teal-400" />
                  </div>
                  <div class="min-w-0">
                    <h1 class="font-bold text-white text-sm md:text-base truncate">Pick-Up Order</h1>
                    <p class="text-xs text-gray-400 truncate">{{ trackedOrder?.order_number }}</p>
                  </div>
                </div>
              </div>
              <div class="pointer-events-auto flex flex-col gap-2 items-end">
                <Button @click="closePickUpTracking" size="icon" variant="outline" class="bg-gray-900/90 backdrop-blur-md border-gray-700 text-white hover:bg-gray-800 shadow-xl h-12 w-12 rounded-full mb-2">
                  <X class="w-6 h-6" />
                </Button>
                <Button @click="isMapDrawerOpen = !isMapDrawerOpen" size="icon" variant="outline" class="bg-gray-900/90 backdrop-blur-md border-gray-700 text-white hover:bg-gray-800 shadow-xl h-12 w-12 rounded-full">
                  <Menu v-if="!isMapDrawerOpen" class="w-6 h-6" />
                  <ChevronDown v-else class="w-6 h-6" />
                </Button>
              </div>
            </div>
            <div class="bg-gray-900/95 border-t border-gray-800 backdrop-blur-xl pointer-events-auto w-full transition-transform duration-300 ease-in-out flex flex-col rounded-t-3xl shadow-[0_-20px_50px_rgba(0,0,0,0.5)] absolute bottom-0 left-0 right-0 z-20 h-[85vh] md:h-[70vh]" :style="{ transform: isMapDrawerOpen ? 'translateY(0)' : 'translateY(100%)' }">
              <div class="pt-6 pb-2 px-4 flex justify-between items-center border-b border-gray-800 mx-4 md:mx-6 shrink-0">
                <h2 class="text-lg font-bold text-white tracking-tight flex items-center gap-2">
                  <MapPin class="h-5 w-5 text-teal-500" /> Shop Location & Handover
                </h2>
                <Button variant="ghost" size="icon" @click="isMapDrawerOpen = false" class="text-gray-400 hover:text-white hover:bg-gray-800 rounded-full h-8 w-8">
                  <X class="w-4 h-4" />
                </Button>
              </div>
              <ScrollArea class="flex-1 min-h-0 px-4 md:px-6 py-4 w-full max-w-4xl mx-auto">
                <div class="space-y-6 pb-8">
                  <div class="flex justify-between items-start">
                    <div>
                      <h2 class="text-2xl font-black text-white leading-tight">Distributor Shop</h2>
                      <p class="text-sm text-gray-400 mt-1 max-w-sm">{{ pickUpDetails.distributor_address || 'Address unavailable' }}</p>
                    </div>
                    <Badge v-if="distanceToTarget !== null" :class="isWithinRange ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'" class="border-0 px-3 py-1 text-sm shrink-0">
                      {{ distanceToTarget < 1000 ? Math.round(distanceToTarget) + 'm' : (distanceToTarget/1000).toFixed(1) + 'km' }} away
                    </Badge>
                  </div>
                  <Alert v-if="!isWithinRange" variant="destructive" class="bg-yellow-900/20 border-yellow-700/50 text-yellow-300 py-3">
                    <AlertTriangle class="h-5 w-5" />
                    <AlertDescription class="text-sm ml-2 leading-tight">You must be within 500 meters of the shop to complete this pick-up.</AlertDescription>
                  </Alert>
                  <div class="bg-gray-800/40 rounded-2xl p-4 border border-gray-700/50">
                    <p class="text-sm font-bold text-gray-300 mb-3 flex items-center gap-2">
                      <Package class="h-4 w-4 text-indigo-400" /> Your Prepared Order
                    </p>
                    <div class="rounded-xl overflow-hidden bg-gray-900 h-40 relative flex items-center justify-center border border-gray-800">
                      <img v-if="pickUpDetails.preparation_proof" :src="pickUpDetails.preparation_proof" class="w-full h-full object-cover" />
                      <div v-else class="text-gray-500 text-sm flex flex-col items-center">
                        <ImageIcon class="h-8 w-8 mb-2 opacity-50" /> No preparation image.
                      </div>
                    </div>
                  </div>
                  <div class="space-y-5">
                    <div>
                      <label class="text-sm font-semibold text-gray-300 mb-2 block">
                        Upload Proof of Received Goods <span class="text-red-400">*</span>
                      </label>
                      <div class="border-2 border-dashed border-gray-700 rounded-2xl flex flex-col items-center justify-center p-6 transition-colors relative" :class="pickupProofPreview ? 'bg-transparent' : 'bg-gray-800/30 hover:bg-gray-800'">
                        <input v-if="!pickupProofPreview" type="file" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handlePickupUpload" :disabled="!isWithinRange" />
                        <div v-if="!pickupProofPreview" class="text-center pointer-events-none">
                          <Camera class="h-8 w-8 text-gray-500 mx-auto mb-3" />
                          <p class="text-sm font-medium text-gray-300">Tap to snap Package</p>
                        </div>
                        <div v-else class="relative w-full flex justify-center">
                          <img :src="pickupProofPreview" class="max-h-56 rounded-xl border border-gray-700 object-cover shadow-lg" />
                          <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 h-8 w-8 rounded-full shadow-lg" @click.prevent.stop="removePickupProof">
                            <X class="h-4 w-4" />
                          </Button>
                        </div>
                      </div>
                    </div>
                    <div>
                      <label class="text-sm font-semibold text-green-400 mb-2 block">
                        Upload Proof of Payment <span class="text-red-400">*</span>
                      </label>
                      <div class="border-2 border-dashed border-green-800/50 rounded-2xl flex flex-col items-center justify-center p-6 transition-colors relative" :class="paymentProofPreview ? 'bg-transparent' : 'bg-green-900/10 hover:bg-green-900/20'">
                        <input v-if="!paymentProofPreview" type="file" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handlePaymentUpload" :disabled="!isWithinRange" />
                        <div v-if="!paymentProofPreview" class="text-center pointer-events-none">
                          <Banknote class="h-8 w-8 text-green-600/50 mx-auto mb-3" />
                          <p class="text-sm font-medium text-green-400">Tap to snap Receipt or Cash Handover</p>
                        </div>
                        <div v-else class="relative w-full flex justify-center">
                          <img :src="paymentProofPreview" class="max-h-56 rounded-xl border border-green-800/50 object-cover shadow-lg" />
                          <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 h-8 w-8 rounded-full shadow-lg" @click.prevent.stop="removePaymentProof">
                            <X class="w-4 h-4" />
                          </Button>
                        </div>
                      </div>
                    </div>
                    <Button @click="submitPickUp" :disabled="!isWithinRange || !pickupProofFile || !paymentProofFile || isSubmittingPickUp" class="w-full bg-teal-600 hover:bg-teal-700 text-white shadow-lg shadow-teal-900/20 mt-4 rounded-xl h-14 text-lg font-semibold" size="lg">
                      <Loader2 v-if="isSubmittingPickUp" class="mr-2 h-5 w-5 animate-spin" />
                      <CheckCircle2 v-else class="mr-2 h-5 w-5" /> Confirm Item Picked Up
                    </Button>
                  </div>
                </div>
              </ScrollArea>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Dialog v-model:open="isDetailsModalOpen">
      <DialogContent class="sm:max-w-200 md:max-w-237.5 lg:max-w-275 max-h-[90vh] overflow-y-auto w-[95vw] p-0 gap-0 rounded-2xl bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800">
        <div class="bg-white dark:bg-gray-800 p-6 md:p-8 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10 flex justify-between items-start gap-4">
          <DialogHeader class="flex flex-col sm:flex-row justify-between items-start gap-4">
            <div>
              <DialogTitle class="text-2xl font-black text-gray-900 dark:text-white">
                Order <span class="text-blue-600 dark:text-blue-400">{{ selectedOrder?.order_number }}</span>
              </DialogTitle>
              <DialogDescription class="flex flex-wrap items-center mt-3 gap-3">
                <Badge :class="[getStatusConfig(selectedOrder?.status || '').color, 'px-3 py-1 rounded-full text-xs font-bold border-0']">
                  {{ getStatusConfig(selectedOrder?.status || '').text }}
                </Badge>
                <span class="text-sm font-medium text-gray-500 flex items-center gap-1.5">
                  <Clock class="w-4 h-4" /> Placed on {{ formatDate(selectedOrder?.created_at || '') }}
                </span>
              </DialogDescription>
            </div>
          </DialogHeader>
          <Button variant="ghost" size="icon" @click="isDetailsModalOpen = false" class="shrink-0 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400">
            <X class="w-5 h-5" />
          </Button>
        </div>
        <div class="p-6 md:p-8">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                  <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <Package class="w-5 h-5" /> Order Items
                  </h4>
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
                        <p class="text-lg font-black text-gray-900 dark:text-white">{{ formatCurrency(Number(item.price) * item.quantity) }}</p>
                      </div>

                      <div v-if="selectedOrder?.status === 'delivered'" class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                          <Button v-if="!item.is_reviewed" @click="openReviewModal(selectedOrder.id, item)" size="sm" variant="outline" class="text-amber-600 border-amber-200 hover:bg-amber-50 dark:hover:bg-amber-900/20 flex-1">
                            <Star class="w-4 h-4 mr-1.5" /> Write a Review
                          </Button>
                          <div v-else class="flex flex-col gap-1.5 bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700 flex-1">
                            <div class="flex items-center justify-between">
                              <div class="flex items-center text-amber-500">
                                <Star v-for="i in 5" :key="i" :class="['w-4 h-4', i <= (item.review_rating || 0) ? 'fill-current' : 'text-gray-300 dark:text-gray-600']" />
                                <span class="text-xs text-gray-500 ml-2 font-bold uppercase tracking-wider">Reviewed</span>
                              </div>
                              <Button variant="link" size="sm" class="text-xs text-blue-500 h-auto p-0 m-0" @click="openReviewModal(selectedOrder.id, item)">Edit</Button>
                            </div>
                            <p v-if="item.review_comment" class="text-sm text-gray-600 dark:text-gray-300 italic mt-1">"{{ item.review_comment }}"</p>
                          </div>

                          <Button variant="outline" size="sm" @click="openReturnChat(item)" class="text-red-600 border-red-200 hover:bg-red-50 dark:hover:bg-red-900/20 shrink-0 h-10 px-4">
                            <RotateCcw class="w-4 h-4 mr-1.5" /> Return
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">
                <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
                  <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <MapPin class="w-5 h-5" /> Shipping Information
                  </h4>
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
      <DialogContent class="sm:max-w-125 p-6 rounded-2xl bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800">
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
              <button v-for="star in 5" :key="star" @click="setRating(star)" class="focus:outline-none transition-transform hover:scale-110 active:scale-95">
                <Star :class="['w-8 h-8 drop-shadow-sm', star <= reviewForm.rating ? 'text-amber-400 fill-current' : 'text-gray-300 dark:text-gray-600']" />
              </button>
            </div>
          </div>
          <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-2">Write your comment (Optional)</label>
            <textarea v-model="reviewForm.comment" rows="4" placeholder="Share your thoughts, what you liked or disliked..." class="w-full p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none transition-all shadow-inner"></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
          <Button variant="outline" @click="isReviewModalOpen = false" class="border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 font-bold rounded-xl">Cancel</Button>
          <Button @click="submitReview" :disabled="isSubmittingReview" class="bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/20 px-6">
            <Loader2 v-if="isSubmittingReview" class="w-4 h-4 mr-2 animate-spin" />
            {{ isSubmittingReview ? 'Submitting...' : 'Submit Review' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <Teleport to="body">
      <div v-if="isReturnChatOpen" class="fixed inset-0 flex justify-end pointer-events-auto" style="z-index: 99999;">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click.stop="closeReturnChat"></div>

        <transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition-transform duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
          <div v-if="isReturnChatOpen" class="relative w-full md:w-112.5 bg-white dark:bg-gray-900 h-full shadow-2xl flex flex-col z-10">
            <div class="p-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
              <div>
                <h3 class="font-bold text-lg text-gray-900 dark:text-white">Return Item</h3>
                <p class="text-xs text-gray-500">{{ activeReturnItem?.product?.name }}</p>
              </div>
              <Button variant="ghost" size="icon" @click.stop.prevent="closeReturnChat">
                <X class="w-5 h-5" />
              </Button>
            </div>

            <div v-if="!returnRequest" class="p-6 overflow-y-auto flex-1 space-y-5">
              <Alert class="bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300">
                <Info class="w-4 h-4" />
                <AlertDescription>Please provide a valid reason and clear photo proof to request a return.</AlertDescription>
              </Alert>
              <div>
                <label class="block text-sm font-bold mb-2">Reason for Return</label>
                <Select v-model="selectedReturnReason">
                  <SelectTrigger class="w-full bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 rounded-xl h-12">
                    <SelectValue placeholder="Select a reason" />
                  </SelectTrigger>
                  <SelectContent class="z-100000">
                    <SelectItem value="Damaged Item">Damaged Item</SelectItem>
                    <SelectItem value="Wrong Item">Wrong Item</SelectItem>
                    <SelectItem value="Not as Described">Not as Described</SelectItem>
                    <SelectItem value="Changed Mind">Changed Mind</SelectItem>
                    <SelectItem value="Defective">Defective</SelectItem>
                    <SelectItem value="Others">Others</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div v-if="selectedReturnReason === 'Others'">
                <label class="block text-sm font-bold mb-2">Please specify</label>
                <textarea v-model="customReturnReason" rows="3" class="w-full p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 resize-none outline-none focus:ring-2 focus:ring-blue-500" placeholder="Type your reason here..."></textarea>
              </div>

              <div>
                <label class="block text-sm font-bold mb-2">Photo Proof</label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl p-6 text-center hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer relative" :class="{'bg-transparent': returnProofPreview}">
                  <input type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handleReturnProofUpload" />
                  <div v-if="!returnProofPreview">
                    <Camera class="mx-auto h-8 w-8 text-gray-400 mb-2" />
                    <p class="text-sm text-gray-500">Tap to upload image</p>
                  </div>
                  <div v-else class="relative inline-block">
                    <img :src="returnProofPreview" class="max-h-40 rounded-lg shadow-sm" />
                    <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 w-6 h-6 rounded-full" @click.stop.prevent="removeReturnProof">
                      <X class="w-3 h-3" />
                    </Button>
                  </div>
                </div>
              </div>
              <Button @click="submitInitialReturn" :disabled="isSubmittingReturn || !selectedReturnReason || (selectedReturnReason === 'Others' && !customReturnReason) || !returnProofFile" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold h-12 rounded-xl">
                <Loader2 v-if="isSubmittingReturn" class="w-5 h-5 mr-2 animate-spin" /> Submit Return Request
              </Button>
            </div>

            <div v-else class="flex flex-col flex-1 overflow-hidden bg-gray-50 dark:bg-gray-900">
              <div class="p-3 text-center text-sm font-semibold text-white shadow-sm shrink-0" :class="{ 'bg-yellow-500': returnRequest.status === 'pending', 'bg-green-500': returnRequest.status === 'approved', 'bg-red-500': returnRequest.status === 'rejected', 'bg-purple-500': returnRequest.status === 'shipped', 'bg-teal-500': returnRequest.status === 'completed' }">
                Status: {{ returnRequest.status.toUpperCase() }}
              </div>

              <div class="flex-1 overflow-y-auto p-4 space-y-4" id="returnChatContainer">
                <div v-for="msg in returnMessages" :key="msg.id" class="flex flex-col" :class="msg.sender_id === user?.id ? 'items-end' : 'items-start'">
                  <div v-if="msg.type === 'system'" class="w-full text-center my-4">
                    <span class="bg-gray-200 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs px-3 py-1 rounded-full">{{ msg.message }}</span>
                    <img v-if="msg.file_path" :src="getFullImageUrl(msg.file_path)" class="max-w-50 mt-2 rounded-lg mx-auto border border-gray-200 dark:border-gray-700 shadow-sm" />
                  </div>
                  <div v-else class="max-w-[80%] rounded-2xl p-3 shadow-sm" :class="msg.sender_id === user?.id ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white rounded-bl-none'">
                    <p v-if="msg.type === 'text'" class="text-sm whitespace-pre-wrap">{{ msg.message }}</p>
                    <div v-else-if="msg.type === 'image'">
                      <img :src="getFullImageUrl(msg.file_path)" class="rounded-lg max-w-full cursor-pointer hover:opacity-90" @click="openImageInNewTab(getFullImageUrl(msg.file_path))" />
                      <p v-if="msg.message" class="text-sm mt-2">{{ msg.message }}</p>
                    </div>
                  </div>
                  <span class="text-[10px] text-gray-400 mt-1">{{ formatDate(msg.created_at) }}</span>
                </div>
              </div>

              <div v-if="returnRequest.status === 'approved'" class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shrink-0 space-y-3 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
                <p class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-wider">Submit Tracking Info</p>
                <input v-model="trackingNumber" type="text" placeholder="Tracking Number" class="w-full text-sm p-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 outline-none focus:border-blue-500" />
                <div class="flex items-center gap-2">
                  <input type="file" id="trackImg" accept="image/*" class="hidden" @change="handleTrackingProofUpload" />
                  <label for="trackImg" class="cursor-pointer px-3 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg text-sm font-medium flex-1 text-center truncate border border-gray-200 dark:border-gray-600">
                    {{ trackingProofFile ? trackingProofFile.name : 'Upload Receipt Photo' }}
                  </label>
                  <Button @click="submitTrackingInfo" :disabled="!trackingNumber || !trackingProofFile || isSubmittingTracking" class="bg-green-600 hover:bg-green-700 text-white shrink-0">
                    <Send class="w-4 h-4" />
                  </Button>
                </div>
              </div>

              <div v-if="chatImagePreview" class="px-4 py-3 bg-gray-100 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 shrink-0">
                <div class="relative inline-block">
                  <img :src="chatImagePreview" class="h-20 w-20 object-cover rounded-xl shadow-md border border-gray-300 dark:border-gray-600" />
                  <button @click="removeChatImage" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg transition-transform hover:scale-110">
                    <X class="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>

              <div v-if="['pending', 'approved', 'shipped'].includes(returnRequest.status)" class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shrink-0 flex items-end gap-2">
                <input type="file" id="chatImg" accept="image/*" class="hidden" @change="handleChatImageUpload" />
                <label for="chatImg" class="cursor-pointer p-3 rounded-full text-gray-500 hover:bg-gray-100 dark:bg-gray-700 shrink-0 transition-colors" :class="{'text-blue-500 bg-blue-50 dark:bg-blue-900/30': chatImageFile}">
                  <Paperclip class="w-5 h-5" />
                </label>
                <div class="flex-1 bg-gray-100 dark:bg-gray-900 rounded-2xl border border-transparent focus-within:border-blue-500 flex items-center overflow-hidden transition-colors">
                <input v-model="chatMessage" @keyup.enter="sendChatMessage" type="text" placeholder="Type a message..." class="w-full bg-transparent border-none focus:ring-0 text-sm px-4 py-3 outline-none" />
                </div>
                <Button @click="sendChatMessage" :disabled="isSendingMessage || (!chatMessage.trim() && !chatImageFile)" size="icon" class="rounded-full h-11 w-11 shrink-0 bg-blue-600 hover:bg-blue-700 text-white shadow-md">
                  <Send class="w-4 h-4 ml-0.5" />
                </Button>
              </div>
              <div v-else class="p-3 text-center text-xs text-gray-500 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 shrink-0">
                This return request is {{ returnRequest.status }}. Chat is disabled.
              </div>
            </div>
          </div>
        </transition>
      </div>
    </Teleport>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isAuthAlertOpen" class="fixed inset-0 z-10000 bg-gray-900/60 backdrop-blur-md pointer-events-none"></div>
      </transition>
      <AlertDialog :open="isAuthAlertOpen" @update:open="isAuthAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-10000">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <AlertCircle class="w-6 h-6 text-blue-500" /> Authentication Required
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3">
              You must be logged in to view and manage your orders.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isAuthAlertOpen = false" class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="router.push('/Landing/logIn')" class="rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white h-11 px-6 shadow-md shadow-blue-600/20">Log In</AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Teleport>
  </div>
</template>

<style scoped>
:deep(.leaflet-control-container) { display: none; }
</style>