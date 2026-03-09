<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix for default Leaflet icon paths in Vue (TypeScript bypass)
// @ts-ignore
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
// @ts-ignore
import markerIcon from 'leaflet/dist/images/marker-icon.png'
// @ts-ignore
import markerShadow from 'leaflet/dist/images/marker-shadow.png'

delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow
})

import { 
  MapPin, 
  AlertTriangle, 
  Navigation, 
  CheckCircle2, 
  Truck, 
  Package, 
  Phone, 
  Image as ImageIcon,
  X,
  Loader2,
  Banknote,
  UploadCloud,
  Menu,
  Info
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { ScrollArea } from '@/components/ui/scroll-area'

// --- Interfaces ---
interface DeliveryItem {
  name: string
  quantity: number
}

interface Delivery {
  id: number
  order_number: string
  status: string
  client_name: string
  client_phone: string
  delivery_address: string
  target_lat: number | null
  target_lng: number | null
  distributor_lat: number | null
  distributor_lng: number | null
  total_amount: number
  payment_method: string
  items: DeliveryItem[]
}

// --- State ---
const deliveries = ref<Delivery[]>([])
const activeDeliveryId = ref<number | null>(null)
const locationGranted = ref(false)
const locationError = ref('')
const currentPosition = ref<{ lat: number; lng: number } | null>(null)
const isLoading = ref(false)
const isProcessing = ref(false)
const isDrawerOpen = ref(true)

// Proof File States
const proofFile = ref<File | null>(null)
const proofPreview = ref<string | null>(null)

const paymentFile = ref<File | null>(null)
const paymentPreview = ref<string | null>(null)

const remittanceFile = ref<File | null>(null)
const remittancePreview = ref<string | null>(null)

// Map Variables
let leafletMap: L.Map | null = null
let userMarker: L.Marker | null = null
let routeLine: L.Polyline | null = null 
let watchId: number | null = null
let lastRoutedPosition: { lat: number; lng: number } | null = null
const targetMarkers: L.Marker[] = []

// --- Computed ---
const pendingDeliveries = computed(() => 
  deliveries.value.filter(d => ['assigned', 'in_transit', 'remitting'].includes(d.status))
)

const activeDelivery = computed(() => 
  deliveries.value.find(d => d.id === activeDeliveryId.value)
)

const currentCoordsDisplay = computed(() => {
  if (!currentPosition.value) return 'Locating...'
  return `${currentPosition.value.lat.toFixed(5)}, ${currentPosition.value.lng.toFixed(5)}`
})

const activeTargetLat = computed(() => {
  if (!activeDelivery.value) return null
  return activeDelivery.value.status === 'remitting' ? activeDelivery.value.distributor_lat : activeDelivery.value.target_lat
})

const activeTargetLng = computed(() => {
  if (!activeDelivery.value) return null
  return activeDelivery.value.status === 'remitting' ? activeDelivery.value.distributor_lng : activeDelivery.value.target_lng
})

// Calculate distance in meters
const distanceToActive = computed(() => {
  if (!activeDelivery.value || !currentPosition.value) return null
  
  const targetLat = activeTargetLat.value
  const targetLng = activeTargetLng.value
  
  if (!targetLat || !targetLng) return null

  return calculateDistance(
    currentPosition.value.lat, 
    currentPosition.value.lng, 
    targetLat, 
    targetLng
  )
})

const isWithinRange = computed(() => {
  if (distanceToActive.value === null) return true 
  return distanceToActive.value <= 500 
})

// Haversine formula (returns meters)
const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number) => {
  const R = 6371e3 
  const p1 = lat1 * Math.PI/180
  const p2 = lat2 * Math.PI/180
  const dp = (lat2-lat1) * Math.PI/180
  const dl = (lon2-lon1) * Math.PI/180

  const a = Math.sin(dp/2) * Math.sin(dp/2) + Math.cos(p1) * Math.cos(p2) * Math.sin(dl/2) * Math.sin(dl/2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))
  return R * c 
}

// --- Map Initialization ---
const initMap = () => {
  if (!currentPosition.value || leafletMap) return

  leafletMap = L.map('delivery-map', { zoomControl: false }).setView([currentPosition.value.lat, currentPosition.value.lng], 15)
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
    keepBuffer: 4,         
    updateWhenIdle: true,  
    updateWhenZooming: false
  }).addTo(leafletMap)

  const userIcon = L.divIcon({
    html: `
      <div class="h-10 w-10 bg-white rounded-full border-2 border-blue-600 flex items-center justify-center shadow-[0_0_15px_rgba(37,99,235,0.7)] relative">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600">
            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/>
            <path d="M15 18H9"/>
            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/>
            <circle cx="17" cy="18" r="2"/>
            <circle cx="7" cy="18" r="2"/>
        </svg>
        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-0 h-0 border-l-[6px] border-l-transparent border-r-[6px] border-r-transparent border-t-[8px] border-t-blue-600"></div>
      </div>
    `,
    className: 'bg-transparent',
    iconSize: [40, 48],
    iconAnchor: [20, 48]
  })

  userMarker = L.marker([currentPosition.value.lat, currentPosition.value.lng], { icon: userIcon }).addTo(leafletMap)
  updateMapMarkers()
}

// Dynamically draws the road route from driver to destination using OSRM
const drawRoute = async (force = false) => {
  if (!leafletMap) return
  
  if (!activeDeliveryId.value || !currentPosition.value || !activeTargetLat.value || !activeTargetLng.value) {
    if (routeLine) {
      leafletMap.removeLayer(routeLine)
      routeLine = null
    }
    return
  }

  if (!force && lastRoutedPosition) {
    const distMoved = calculateDistance(
      currentPosition.value.lat, currentPosition.value.lng,
      lastRoutedPosition.lat, lastRoutedPosition.lng
    )
    if (distMoved < 20) return 
  }

  lastRoutedPosition = { lat: currentPosition.value.lat, lng: currentPosition.value.lng }

  const isRemitting = activeDelivery.value?.status === 'remitting'
  const color = isRemitting ? '#a855f7' : '#3b82f6'

  try {
    const startLng = currentPosition.value.lng
    const startLat = currentPosition.value.lat
    const endLng = activeTargetLng.value
    const endLat = activeTargetLat.value

    const response = await fetch(`https://router.project-osrm.org/route/v1/driving/${startLng},${startLat};${endLng},${endLat}?overview=full&geometries=geojson`)
    const data = await response.json()

    if (data.code === 'Ok' && data.routes.length > 0) {
      if (routeLine) {
        leafletMap.removeLayer(routeLine)
      }
      
      const coords = data.routes[0].geometry.coordinates.map((c: any) => [c[1], c[0]])
      
      routeLine = L.polyline(coords, {
        color: color,
        weight: 5,
        opacity: 0.8,
        lineCap: 'round',
        lineJoin: 'round'
      }).addTo(leafletMap)
    } else {
      drawFallbackRoute(color)
    }
  } catch (error) {
    console.error("Routing error:", error)
    drawFallbackRoute(color)
  }
}

// Fallback to straight line if OSRM is unreachable
const drawFallbackRoute = (color: string) => {
  if (routeLine && leafletMap) leafletMap.removeLayer(routeLine)
  if (!leafletMap || !currentPosition.value || !activeTargetLat.value || !activeTargetLng.value) return
  
  routeLine = L.polyline(
    [
      [currentPosition.value.lat, currentPosition.value.lng],
      [activeTargetLat.value, activeTargetLng.value]
    ], 
    {
      color: color,
      weight: 4,
      dashArray: '10, 10',
      opacity: 0.8,
      lineCap: 'round',
      lineJoin: 'round'
    }
  ).addTo(leafletMap)
}

const updateMapMarkers = () => {
  if (!leafletMap) return

  targetMarkers.forEach(m => m.remove())
  targetMarkers.length = 0

  pendingDeliveries.value.forEach(delivery => {
    let tLat = delivery.target_lat
    let tLng = delivery.target_lng

    if (delivery.status === 'remitting') {
      tLat = delivery.distributor_lat
      tLng = delivery.distributor_lng
    }

    if (tLat && tLng) {
      const isTarget = activeDeliveryId.value === delivery.id
      
      let iconColor = 'bg-red-500'
      if (delivery.status === 'remitting') iconColor = 'bg-purple-500'
      if (isTarget) iconColor = 'bg-green-500'

      const iconHtml = isTarget 
        ? `<div class="h-6 w-6 ${iconColor} rounded-full border-2 border-white flex items-center justify-center shadow-[0_0_20px_rgba(34,197,94,0.6)]"><svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>`
        : `<div class="h-4 w-4 ${iconColor} rounded-full border-2 border-white shadow-md"></div>`

      const marker = L.marker([tLat, tLng], {
        icon: L.divIcon({ html: iconHtml, className: '', iconSize: isTarget ? [24,24] : [16,16], iconAnchor: isTarget ? [12,12] : [8,8] })
      }).addTo(leafletMap)
      
      marker.bindPopup(`<b class="text-gray-900">${delivery.status === 'remitting' ? 'Return to HQ' : delivery.client_name}</b><br><span class="text-gray-600">${delivery.order_number}</span>`)
      targetMarkers.push(marker)
    }
  })
}

// --- Watchers / Trackers ---
const startTracking = () => {
  let isFirstLoad = true

  watchId = navigator.geolocation.watchPosition(
    (position) => {
      const newPos = { lat: position.coords.latitude, lng: position.coords.longitude }
      currentPosition.value = newPos
      
      if (!leafletMap) {
        initMap()
      } else if (userMarker) {
        userMarker.setLatLng([newPos.lat, newPos.lng])
        drawRoute(false) 
        
        if (isFirstLoad && !activeDeliveryId.value) {
          leafletMap.setView([newPos.lat, newPos.lng], 15)
          isFirstLoad = false
        }
      }
    },
    (error) => console.error("Tracking error:", error),
    { 
      enableHighAccuracy: true, 
      maximumAge: 10000, 
      timeout: 5000 
    }
  )
}

const requestLocation = () => {
  isLoading.value = true
  locationError.value = ''
  
  if (!navigator.geolocation) {
    locationError.value = "Geolocation is not supported by your browser."
    isLoading.value = false
    return
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      locationGranted.value = true
      currentPosition.value = { lat: position.coords.latitude, lng: position.coords.longitude }
      fetchDeliveries()
      startTracking()
    },
    (error) => {
      isLoading.value = false
      locationError.value = "Location access denied. Please enable location to track deliveries."
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  )
}

// --- API Calls ---
const fetchDeliveries = async () => {
  try {
    const res = await api.get('/distributor-delivery')
    deliveries.value = res.data
    updateMapMarkers()
  } catch (error) {
    console.error(error)
    toast.error('Failed to load deliveries')
  } finally {
    isLoading.value = false
  }
}

const startDelivery = async (id: number) => {
  isProcessing.value = true
  try {
    await api.post(`/distributor-delivery/${id}/start`)
    toast.success('Trip started!')
    await fetchDeliveries()
    focusDelivery(id)
  } catch (error) {
    toast.error('Failed to start delivery')
  } finally {
    isProcessing.value = false
  }
}

const arriveAndComplete = async () => {
  if (!activeDelivery.value || !currentPosition.value) return
  
  if (!proofFile.value) {
    toast.error('Proof of delivery is required.')
    return
  }
  if (activeDelivery.value.payment_method.toLowerCase() === 'cod' && !paymentFile.value) {
    toast.error('Proof of payment received is required for COD orders.')
    return
  }

  isProcessing.value = true
  const formData = new FormData()
  formData.append('latitude', currentPosition.value.lat.toString())
  formData.append('longitude', currentPosition.value.lng.toString())
  formData.append('proof_file', proofFile.value)
  
  if (activeDelivery.value.payment_method.toLowerCase() === 'cod' && paymentFile.value) {
    formData.append('payment_file', paymentFile.value)
  }

  try {
    await api.post(`/distributor-delivery/${activeDelivery.value.id}/arrive`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (activeDelivery.value.payment_method.toLowerCase() === 'cod') {
       toast.success('Delivered! Please return to HQ to remit collected funds.')
    } else {
       toast.success('Delivery Completed Successfully!')
       clearActiveDelivery()
    }

    removeProof()
    removePayment()
    await fetchDeliveries()
    if (activeDeliveryId.value) focusDelivery(activeDeliveryId.value)
  } catch (error: any) {
    if(error.response && error.response.status === 400) {
       toast.error(error.response.data.message || 'Distance validation failed.')
    } else {
       toast.error('Failed to complete delivery.')
    }
  } finally {
    isProcessing.value = false
  }
}

const remitAndComplete = async () => {
  if (!activeDelivery.value || !currentPosition.value) return
  if (!remittanceFile.value) {
    toast.error('Proof of remittance handover is required.')
    return
  }

  isProcessing.value = true
  const formData = new FormData()
  formData.append('latitude', currentPosition.value.lat.toString())
  formData.append('longitude', currentPosition.value.lng.toString())
  formData.append('remittance_file', remittanceFile.value)

  try {
    await api.post(`/distributor-delivery/${activeDelivery.value.id}/remit`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    toast.success('Funds successfully remitted. Delivery cycle complete!')
    removeRemittance()
    clearActiveDelivery()
    await fetchDeliveries()
  } catch (error: any) {
    if(error.response && error.response.status === 400) {
       toast.error(error.response.data.message || 'Distance validation failed.')
    } else {
       toast.error('Failed to remit funds.')
    }
  } finally {
    isProcessing.value = false
  }
}

// --- Helpers ---
const focusDelivery = (id: number) => {
  activeDeliveryId.value = id
  isDrawerOpen.value = true 
  updateMapMarkers()
  drawRoute(true) 
  
  nextTick(() => {
    const delivery = deliveries.value.find(d => d.id === id)
    const tLat = delivery?.status === 'remitting' ? delivery.distributor_lat : delivery?.target_lat
    const tLng = delivery?.status === 'remitting' ? delivery.distributor_lng : delivery?.target_lng

    if (leafletMap && tLat && tLng) {
      if (currentPosition.value) {
        const bounds = L.latLngBounds(
          [currentPosition.value.lat, currentPosition.value.lng],
          [tLat, tLng]
        )
        leafletMap.fitBounds(bounds, { padding: [50, 50] })
      } else {
        leafletMap.setView([tLat, tLng], 15)
      }
    }
  })
}

const clearActiveDelivery = () => {
  activeDeliveryId.value = null
  isDrawerOpen.value = true
  lastRoutedPosition = null
  updateMapMarkers()
  drawRoute(true) 
  
  if (leafletMap && currentPosition.value) {
     leafletMap.setView([currentPosition.value.lat, currentPosition.value.lng], 15)
  }
}

const toggleDrawer = () => {
  isDrawerOpen.value = !isDrawerOpen.value
}

// --- File Handlers ---
const handleProofUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    proofFile.value = target.files[0]
    proofPreview.value = URL.createObjectURL(target.files[0])
  }
}
const removeProof = () => {
  proofFile.value = null
  if (proofPreview.value) { URL.revokeObjectURL(proofPreview.value); proofPreview.value = null }
}

const handlePaymentUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    paymentFile.value = target.files[0]
    paymentPreview.value = URL.createObjectURL(target.files[0])
  }
}
const removePayment = () => {
  paymentFile.value = null
  if (paymentPreview.value) { URL.revokeObjectURL(paymentPreview.value); paymentPreview.value = null }
}

const handleRemittanceUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    remittanceFile.value = target.files[0]
    remittancePreview.value = URL.createObjectURL(target.files[0])
  }
}
const removeRemittance = () => {
  remittanceFile.value = null
  if (remittancePreview.value) { URL.revokeObjectURL(remittancePreview.value); remittancePreview.value = null }
}

onMounted(() => {
  requestLocation()
})

onUnmounted(() => {
  if (watchId !== null) navigator.geolocation.clearWatch(watchId)
  if (leafletMap) leafletMap.remove()
})
</script>

<template>
  <div class="absolute inset-0 flex flex-col overflow-hidden bg-transparent text-gray-100 font-sans">
    
    <div v-if="!locationGranted" class="absolute inset-0 z-50 flex items-center justify-center backdrop-blur-md bg-gray-900/60 p-4">
      <div class="flex flex-col items-center justify-center flex-1 w-full max-w-md mx-auto text-center space-y-6">
        <div class="relative">
          <div class="absolute -inset-4 bg-blue-500/20 rounded-full animate-pulse blur-xl"></div>
          <div class="bg-blue-900/40 p-6 rounded-full relative shadow-inner border border-blue-500/30">
            <MapPin class="w-16 h-16 text-blue-400" />
          </div>
        </div>
        
        <div class="space-y-2">
          <h1 class="text-3xl font-black tracking-tight text-white">Location Required</h1>
          <p class="text-gray-400 text-sm md:text-base px-4">
            To track deliveries and validate completion distances, you must enable location services.
          </p>
        </div>

        <Alert v-if="locationError" variant="destructive" class="text-left w-full bg-red-900/20 border-red-900/50 text-red-300">
          <AlertTriangle class="h-4 w-4" />
          <AlertTitle>Access Denied</AlertTitle>
          <AlertDescription>{{ locationError }}</AlertDescription>
        </Alert>

        <Button @click="requestLocation" size="lg" :disabled="isLoading" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-900/20">
          <Loader2 v-if="isLoading" class="mr-2 h-5 w-5 animate-spin" />
          <Navigation v-else class="mr-2 h-5 w-5" />
          Enable GPS Access
        </Button>
      </div>
    </div>

    <div id="delivery-map" class="absolute inset-0 z-0"></div>
    
    <div v-if="locationGranted" class="absolute inset-0 z-10 pointer-events-none flex flex-col justify-between">
      
      <div class="p-4 md:p-6 pt-24 md:pt-6 flex justify-between items-start w-full">
        
        <div class="flex flex-col gap-2 pointer-events-auto max-w-[65%] sm:max-w-sm">
          <div class="bg-gray-900/80 backdrop-blur-md border border-gray-800 rounded-full py-1.5 px-3 shadow-lg flex items-center gap-2 w-max">
            <div class="relative flex h-2.5 w-2.5 shrink-0">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-blue-500"></span>
            </div>
            <span class="text-xs font-medium text-gray-200 truncate font-mono">{{ currentCoordsDisplay }}</span>
          </div>

          <div class="bg-gray-900/80 backdrop-blur-md border border-gray-800 rounded-xl p-3 shadow-lg flex items-center gap-3">
            <div class="bg-blue-500/20 p-2 rounded-lg shrink-0">
              <Truck class="h-5 w-5 text-blue-400" />
            </div>
            <div class="min-w-0">
               <h1 class="font-bold text-white text-sm md:text-base truncate">Distributor Routing</h1>
               <p class="text-xs text-gray-400 truncate">{{ pendingDeliveries.length }} Pending Stops</p>
            </div>
          </div>
          
          <div v-if="activeDeliveryId" class="mt-1">
             <Button @click="clearActiveDelivery" size="sm" variant="outline" class="bg-gray-900/80 border-gray-700 text-white hover:bg-gray-800 shadow-md">
                Show All Routes
             </Button>
          </div>
        </div>
        
        <div class="pointer-events-auto flex flex-col gap-2 items-end">
          <Button 
            @click="toggleDrawer" 
            size="icon" 
            variant="outline" 
            class="bg-gray-900/90 backdrop-blur-md border-gray-700 text-white hover:bg-gray-800 shadow-xl h-12 w-12 rounded-full"
          >
            <Menu v-if="!isDrawerOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
          </Button>
        </div>
      </div>

      <div 
        class="bg-gray-900/95 border-t border-gray-800 backdrop-blur-xl pointer-events-auto w-full transition-transform duration-300 ease-in-out flex flex-col rounded-t-3xl shadow-[0_-20px_50px_rgba(0,0,0,0.5)] absolute bottom-0 left-0 right-0 z-20 h-[80vh] md:h-[65vh]"
        :style="{ transform: isDrawerOpen ? 'translateY(0)' : 'translateY(100%)' }"
      >
        <div class="pt-6 pb-2 px-4 flex justify-between items-center border-b border-gray-800 mx-4 md:mx-6 shrink-0">
           <h2 class="text-lg font-bold text-white tracking-tight">
              {{ activeDeliveryId ? 'Delivery Actions' : 'Assigned Routes' }}
           </h2>
           <Button variant="ghost" size="icon" @click="isDrawerOpen = false" class="text-gray-400 hover:text-white hover:bg-gray-800 rounded-full h-8 w-8">
             <X class="w-4 h-4" />
           </Button>
        </div>

        <ScrollArea class="flex-1 min-h-0 px-4 md:px-6 py-4 w-full max-w-4xl mx-auto">
          
          <div v-if="!activeDeliveryId" class="space-y-3 pb-8">
            <div v-if="pendingDeliveries.length === 0" class="text-center py-12">
              <CheckCircle2 class="h-12 w-12 text-green-500/50 mx-auto mb-3" />
              <p class="text-gray-400 font-medium text-lg">You're all caught up!</p>
              <p class="text-gray-500 text-sm mt-1">No active deliveries assigned at the moment.</p>
            </div>

            <button
              v-for="delivery in pendingDeliveries"
              :key="delivery.id"
              @click="focusDelivery(delivery.id)"
              class="w-full text-left bg-gray-800/40 border border-gray-700/50 hover:bg-gray-800 hover:border-blue-500/50 rounded-2xl p-4 transition-all"
            >
              <div class="flex justify-between items-start mb-2">
                <div class="pr-2">
                  <h3 class="font-bold text-gray-100 text-lg truncate">{{ delivery.status === 'remitting' ? 'Return to HQ' : delivery.client_name }}</h3>
                  <p class="text-xs text-gray-500 font-mono mt-0.5">{{ delivery.order_number }}</p>
                </div>
                <Badge :class="{
                  'bg-purple-500/20 text-purple-400 border-0': delivery.status === 'remitting',
                  'bg-blue-500/20 text-blue-400 border-0': delivery.status === 'in_transit',
                  'bg-amber-500/20 text-amber-400 border-0': delivery.status === 'assigned'
                }">
                  {{ delivery.status === 'remitting' ? 'Remitting' : (delivery.status === 'in_transit' ? 'In Transit' : 'Assigned') }}
                </Badge>
              </div>
              <p class="text-sm text-gray-400 mt-2 flex items-start gap-2">
                <MapPin class="h-4 w-4 shrink-0 mt-0.5" :class="delivery.status === 'remitting' ? 'text-purple-400' : 'text-gray-500'" /> 
                <span class="line-clamp-2">{{ delivery.status === 'remitting' ? 'Return collected funds to Distributor Base' : delivery.delivery_address }}</span>
              </p>
            </button>
          </div>

          <div v-else-if="activeDelivery" class="space-y-5 pb-8">
            
            <div class="flex justify-between items-start">
               <div>
                  <h2 class="text-2xl font-black text-white leading-tight">{{ activeDelivery.status === 'remitting' ? 'HQ Turnover' : activeDelivery.client_name }}</h2>
                  <p class="text-sm text-gray-400 font-mono mt-1">{{ activeDelivery.order_number }}</p>
               </div>
               <Badge v-if="distanceToActive !== null" :class="isWithinRange ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'" class="border-0 px-3 py-1">
                  {{ distanceToActive < 1000 ? Math.round(distanceToActive) + 'm' : (distanceToActive/1000).toFixed(1) + 'km' }} away
               </Badge>
            </div>

            <div v-if="activeDelivery.status !== 'remitting'" class="grid grid-cols-2 gap-3">
              <div class="bg-gray-800/40 rounded-xl p-3 border border-gray-700/50">
                <p class="text-xs text-gray-500 mb-1 flex items-center gap-1"><Phone class="h-3 w-3"/> Contact</p>
                <p class="text-sm font-medium text-gray-200">{{ activeDelivery.client_phone }}</p>
              </div>
              <div class="bg-gray-800/40 rounded-xl p-3 border border-gray-700/50">
                <p class="text-xs text-gray-500 mb-1 flex items-center gap-1"><Package class="h-3 w-3"/> Items</p>
                <p class="text-sm font-medium text-gray-200">{{ activeDelivery.items?.length || 0 }} Package(s)</p>
              </div>
            </div>

            <div class="bg-gray-800/40 rounded-xl p-3.5 border border-gray-700/50">
                <p class="text-xs text-gray-500 mb-1.5 flex items-center gap-1">
                  <MapPin class="h-3.5 w-3.5" :class="activeDelivery.status === 'remitting' ? 'text-purple-400' : ''"/> 
                  {{ activeDelivery.status === 'remitting' ? 'Distributor Headquarters' : 'Delivery Destination' }}
                </p>
                <p class="text-sm font-medium text-gray-200 leading-relaxed">
                  {{ activeDelivery.status === 'remitting' ? 'Please return to base to remit collected COD.' : activeDelivery.delivery_address }}
                </p>
            </div>

            <div v-if="activeDelivery.payment_method.toLowerCase() === 'cod' && activeDelivery.status !== 'remitting'" class="bg-green-900/20 rounded-xl p-4 border border-green-800/50">
               <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                 <span class="text-sm font-semibold text-green-400 flex items-center gap-2">
                    <Banknote class="h-4 w-4"/> Cash on Delivery (COD)
                 </span>
                 <span class="text-2xl font-black text-white tracking-tight">₱{{ Number(activeDelivery.total_amount).toLocaleString() }}</span>
               </div>
            </div>

            <div v-if="activeDelivery.status === 'assigned'" class="pt-4">
                <Button @click="startDelivery(activeDelivery.id)" :disabled="isProcessing" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl h-14 text-lg font-semibold" size="lg">
                    <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" />
                    <Navigation v-else class="mr-2 h-5 w-5" /> Start Trip
                </Button>
            </div>

            <div v-if="activeDelivery.status === 'in_transit'" class="space-y-5 pt-2">
                
                <Alert v-if="!isWithinRange" variant="destructive" class="bg-yellow-900/20 border-yellow-700/50 text-yellow-300 py-3">
                   <AlertTriangle class="h-5 w-5" />
                   <AlertDescription class="text-sm ml-2 leading-tight">You are too far from the destination to complete this delivery.</AlertDescription>
                </Alert>

                <div>
                   <label class="text-sm font-semibold text-gray-300 mb-2 block">Upload Proof of Goods Delivered <span class="text-red-400">*</span></label>
                   <div class="border-2 border-dashed border-gray-700 rounded-2xl flex flex-col items-center justify-center p-6 transition-colors relative" :class="proofPreview ? 'bg-transparent' : 'bg-gray-800/30 hover:bg-gray-800'">
                      <input v-if="!proofPreview" type="file" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handleProofUpload" />
                      <div v-if="!proofPreview" class="text-center pointer-events-none">
                         <ImageIcon class="h-8 w-8 text-gray-500 mx-auto mb-3" />
                         <p class="text-sm font-medium text-gray-300">Tap to snap Package</p>
                      </div>
                      <div v-else class="relative w-full flex justify-center">
                         <img :src="proofPreview" class="max-h-56 rounded-xl border border-gray-700 object-cover shadow-lg" />
                         <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 h-8 w-8 rounded-full shadow-lg" @click.prevent.stop="removeProof">
                            <X class="h-4 w-4" />
                         </Button>
                      </div>
                   </div>
                </div>

                <div v-if="activeDelivery.payment_method.toLowerCase() === 'cod'">
                   <label class="text-sm font-semibold text-green-400 mb-2 block">Upload Proof of Payment Received <span class="text-red-400">*</span></label>
                   <div class="border-2 border-dashed border-green-800/50 rounded-2xl flex flex-col items-center justify-center p-6 transition-colors relative" :class="paymentPreview ? 'bg-transparent' : 'bg-green-900/10 hover:bg-green-900/20'">
                      <input v-if="!paymentPreview" type="file" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handlePaymentUpload" />
                      <div v-if="!paymentPreview" class="text-center pointer-events-none">
                         <Banknote class="h-8 w-8 text-green-600/50 mx-auto mb-3" />
                         <p class="text-sm font-medium text-green-400">Tap to snap Cash received</p>
                      </div>
                      <div v-else class="relative w-full flex justify-center">
                         <img :src="paymentPreview" class="max-h-56 rounded-xl border border-green-800/50 object-cover shadow-lg" />
                         <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 h-8 w-8 rounded-full shadow-lg" @click.prevent.stop="removePayment">
                            <X class="h-4 w-4" />
                         </Button>
                      </div>
                   </div>
                </div>

                <Button 
                  @click="arriveAndComplete" 
                  :disabled="!isWithinRange || !proofFile || (activeDelivery.payment_method.toLowerCase() === 'cod' && !paymentFile) || isProcessing" 
                  class="w-full bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-900/20 mt-4 rounded-xl h-14 text-lg font-semibold" 
                  size="lg"
                >
                    <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" />
                    <CheckCircle2 v-else class="mr-2 h-5 w-5" /> 
                    {{ activeDelivery.payment_method.toLowerCase() === 'cod' ? 'Confirm Arrival & Payment' : 'Complete Delivery' }}
                </Button>
            </div>

            <div v-if="activeDelivery.status === 'remitting'" class="space-y-5 pt-2">
                <Alert v-if="!isWithinRange" variant="destructive" class="bg-yellow-900/20 border-yellow-700/50 text-yellow-300 py-3">
                   <AlertTriangle class="h-5 w-5" />
                   <AlertDescription class="text-sm ml-2 leading-tight">You are too far from the HQ to remit funds.</AlertDescription>
                </Alert>

                <div>
                   <label class="text-sm font-semibold text-purple-400 mb-2 block">Upload Proof of HQ Handover <span class="text-red-400">*</span></label>
                   <div class="border-2 border-dashed border-purple-800/50 rounded-2xl flex flex-col items-center justify-center p-6 transition-colors relative" :class="remittancePreview ? 'bg-transparent' : 'bg-purple-900/10 hover:bg-purple-900/20'">
                      <input v-if="!remittancePreview" type="file" accept="image/*" capture="environment" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handleRemittanceUpload" />
                      <div v-if="!remittancePreview" class="text-center pointer-events-none">
                         <UploadCloud class="h-8 w-8 text-purple-600/50 mx-auto mb-3" />
                         <p class="text-sm font-medium text-purple-400">Tap to snap Turnover Receipt</p>
                      </div>
                      <div v-else class="relative w-full flex justify-center">
                         <img :src="remittancePreview" class="max-h-56 rounded-xl border border-purple-800/50 object-cover shadow-lg" />
                         <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 h-8 w-8 rounded-full shadow-lg" @click.prevent.stop="removeRemittance">
                            <X class="h-4 w-4" />
                         </Button>
                      </div>
                   </div>
                </div>

                <Button 
                  @click="remitAndComplete" 
                  :disabled="!isWithinRange || !remittanceFile || isProcessing" 
                  class="w-full bg-purple-600 hover:bg-purple-700 text-white shadow-lg shadow-purple-900/20 mt-4 rounded-xl h-14 text-lg font-semibold" 
                  size="lg"
                >
                    <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" />
                    <Banknote v-else class="mr-2 h-5 w-5" /> Complete HQ Turnover
                </Button>
            </div>

          </div>
        </ScrollArea>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* Hides default Leaflet UI controls for cleaner overlay */
:deep(.leaflet-control-container) {
  display: none; 
}
</style>