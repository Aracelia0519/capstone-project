<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
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
  Loader2
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Separator } from '@/components/ui/separator'
import { ScrollArea } from '@/components/ui/scroll-area'

// Interfaces
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
  total_amount: number
  payment_method: string
  items: DeliveryItem[]
}

// State
const deliveries = ref<Delivery[]>([])
const activeDeliveryId = ref<number | null>(null)
const locationGranted = ref(false)
const locationError = ref('')
const currentPosition = ref<{ lat: number; lng: number } | null>(null)
const isLoading = ref(false)
const isProcessing = ref(false)

// Proof File State
const proofFile = ref<File | null>(null)
const proofPreview = ref<string | null>(null)

// Map Variables
let leafletMap: L.Map | null = null
let userMarker: L.Marker | null = null
let watchId: number | null = null
const targetMarkers: L.Marker[] = []

// Computed
const activeDelivery = computed(() => 
  deliveries.value.find(d => d.id === activeDeliveryId.value)
)

const pendingDeliveries = computed(() => 
  deliveries.value.filter(d => ['assigned', 'in_transit'].includes(d.status))
)

// Calculate distance in meters
const distanceToActive = computed(() => {
  if (!activeDelivery.value || !currentPosition.value) return null;
  const targetLat = activeDelivery.value.target_lat;
  const targetLng = activeDelivery.value.target_lng;
  
  if (!targetLat || !targetLng) return null; // No coordinates set for target

  return calculateDistance(
    currentPosition.value.lat, 
    currentPosition.value.lng, 
    targetLat, 
    targetLng
  );
})

const isWithinRange = computed(() => {
  if (distanceToActive.value === null) return true; // If no coords, bypass distance strictly on frontend (backend still validates if it exists)
  return distanceToActive.value <= 500; // within 500 meters
})

// Haversine formula (returns meters)
const calculateDistance = (lat1: number, lon1: number, lat2: number, lon2: number) => {
  const R = 6371e3; 
  const p1 = lat1 * Math.PI/180;
  const p2 = lat2 * Math.PI/180;
  const dp = (lat2-lat1) * Math.PI/180;
  const dl = (lon2-lon1) * Math.PI/180;

  const a = Math.sin(dp/2) * Math.sin(dp/2) + Math.cos(p1) * Math.cos(p2) * Math.sin(dl/2) * Math.sin(dl/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  return R * c; 
}

// Map Initialization
const initMap = () => {
  if (!currentPosition.value || leafletMap) return

  leafletMap = L.map('delivery-map', { zoomControl: false }).setView([currentPosition.value.lat, currentPosition.value.lng], 15)
  
  // Normal Standard OpenStreetMap with performance fixes to stop lag
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
    keepBuffer: 4,         
    updateWhenIdle: true,  
    updateWhenZooming: false
  }).addTo(leafletMap)

  const userIcon = L.divIcon({
    html: `<div class="h-4 w-4 bg-blue-500 rounded-full border-2 border-white shadow-[0_0_15px_rgba(59,130,246,0.5)]"></div>`,
    className: '',
    iconSize: [16, 16],
    iconAnchor: [8, 8]
  })

  userMarker = L.marker([currentPosition.value.lat, currentPosition.value.lng], { icon: userIcon }).addTo(leafletMap)
  updateMapMarkers()
}

const updateMapMarkers = () => {
  if (!leafletMap) return

  targetMarkers.forEach(m => m.remove())
  targetMarkers.length = 0

  pendingDeliveries.value.forEach(delivery => {
    if (delivery.target_lat && delivery.target_lng) {
      const isTarget = activeDeliveryId.value === delivery.id
      const iconHtml = isTarget 
        ? `<div class="h-6 w-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center shadow-[0_0_20px_rgba(34,197,94,0.6)]"><svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></div>`
        : `<div class="h-4 w-4 bg-red-500 rounded-full border-2 border-white shadow-md"></div>`

      const marker = L.marker([delivery.target_lat, delivery.target_lng], {
        icon: L.divIcon({ html: iconHtml, className: '', iconSize: isTarget ? [24,24] : [16,16], iconAnchor: isTarget ? [12,12] : [8,8] })
      }).addTo(leafletMap)
      
      marker.bindPopup(`<b class="text-gray-900">${delivery.client_name}</b><br><span class="text-gray-600">${delivery.order_number}</span>`)
      targetMarkers.push(marker)
    }
  })
}

// Watchers / Trackers
const startTracking = () => {
  let isFirstLoad = true; 

  watchId = navigator.geolocation.watchPosition(
    (position) => {
      const newPos = { lat: position.coords.latitude, lng: position.coords.longitude }
      currentPosition.value = newPos
      
      if (!leafletMap) {
        initMap()
      } else if (userMarker) {
        userMarker.setLatLng([newPos.lat, newPos.lng])
        
        // ONLY center the map if it's the first time getting a location
        if (isFirstLoad && !activeDeliveryId.value) {
          leafletMap.setView([newPos.lat, newPos.lng], 15)
          isFirstLoad = false;
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

// API Calls
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

  isProcessing.value = true
  const formData = new FormData()
  formData.append('latitude', currentPosition.value.lat.toString())
  formData.append('longitude', currentPosition.value.lng.toString())
  formData.append('proof_file', proofFile.value)

  try {
    await api.post(`/distributor-delivery/${activeDelivery.value.id}/arrive`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    toast.success('Delivery Completed Successfully!')
    removeProof()
    activeDeliveryId.value = null
    await fetchDeliveries()
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

// Helpers
const focusDelivery = (id: number) => {
  activeDeliveryId.value = id
  updateMapMarkers()
  
  const delivery = deliveries.value.find(d => d.id === id)
  if (leafletMap && delivery && delivery.target_lat && delivery.target_lng) {
    // Pan to bounds containing user and target
    if (currentPosition.value) {
      const bounds = L.latLngBounds(
        [currentPosition.value.lat, currentPosition.value.lng],
        [delivery.target_lat, delivery.target_lng]
      )
      leafletMap.fitBounds(bounds, { padding: [50, 50] })
    } else {
      leafletMap.setView([delivery.target_lat, delivery.target_lng], 15)
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

onMounted(() => {
  requestLocation() // Auto-request on load
})

onUnmounted(() => {
  if (watchId !== null) navigator.geolocation.clearWatch(watchId)
  if (leafletMap) leafletMap.remove()
})
</script>

<template>
  <div class="absolute inset-0 flex flex-col overflow-hidden bg-transparent text-gray-100">
    
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
    
    <div v-if="locationGranted" class="relative z-10 flex flex-col h-full pointer-events-none">
      
      <div class="p-4 md:p-6 flex justify-between items-start pointer-events-auto">
        <div class="bg-gray-900/80 backdrop-blur-md border border-gray-800 rounded-xl p-3 shadow-lg flex items-center gap-3">
          <div class="bg-blue-500/20 p-2 rounded-lg">
            <Truck class="h-5 w-5 text-blue-400" />
          </div>
          <div>
             <h1 class="font-bold text-white text-sm md:text-base">My Deliveries</h1>
             <p class="text-xs text-gray-400">{{ pendingDeliveries.length }} Pending Stops</p>
          </div>
        </div>
        
        <div v-if="activeDeliveryId" class="pointer-events-auto">
           <Button @click="activeDeliveryId = null" size="sm" variant="outline" class="bg-gray-900/80 border-gray-700 text-white hover:bg-gray-800 shadow-md">
              Show All
           </Button>
        </div>
      </div>

      <div class="flex-1"></div>

      <div class="bg-gray-900/90 border-t border-gray-800 backdrop-blur-xl pointer-events-auto w-full max-h-[60vh] rounded-t-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.5)] transition-all flex flex-col">
        
        <div class="w-12 h-1.5 bg-gray-700 rounded-full mx-auto my-3"></div>

        <ScrollArea class="flex-1 px-4 md:px-6 pb-6 w-full max-w-4xl mx-auto">
          
          <div v-if="!activeDeliveryId" class="space-y-3 pb-6">
            <h2 class="text-lg font-semibold text-white mb-4 px-1">Assigned Routes</h2>
            
            <div v-if="pendingDeliveries.length === 0" class="text-center py-8">
              <CheckCircle2 class="h-12 w-12 text-green-500/50 mx-auto mb-3" />
              <p class="text-gray-400">You have no active deliveries assigned.</p>
            </div>

            <button
              v-for="delivery in pendingDeliveries"
              :key="delivery.id"
              @click="focusDelivery(delivery.id)"
              class="w-full text-left bg-gray-800/50 border border-gray-700 hover:border-blue-500/50 rounded-xl p-4 transition-all"
            >
              <div class="flex justify-between items-start mb-2">
                <div>
                  <h3 class="font-semibold text-gray-200">{{ delivery.client_name }}</h3>
                  <p class="text-xs text-gray-500">{{ delivery.order_number }}</p>
                </div>
                <Badge :class="delivery.status === 'in_transit' ? 'bg-blue-500/20 text-blue-400 border-0' : 'bg-gray-700 text-gray-300 border-0'">
                  {{ delivery.status === 'in_transit' ? 'In Transit' : 'Assigned' }}
                </Badge>
              </div>
              <p class="text-sm text-gray-400 truncate flex items-center gap-1.5">
                <MapPin class="h-3.5 w-3.5" /> {{ delivery.delivery_address }}
              </p>
            </button>
          </div>

          <div v-else-if="activeDelivery" class="space-y-6 pb-6">
            
            <div class="flex justify-between items-start">
               <div>
                  <h2 class="text-xl font-bold text-white">{{ activeDelivery.client_name }}</h2>
                  <p class="text-sm text-gray-400 font-mono">{{ activeDelivery.order_number }}</p>
               </div>
               <Badge v-if="distanceToActive !== null" :class="isWithinRange ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'" class="border-0">
                  {{ distanceToActive < 1000 ? Math.round(distanceToActive) + 'm' : (distanceToActive/1000).toFixed(1) + 'km' }} away
               </Badge>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <p class="text-xs text-gray-500 mb-1 flex items-center gap-1"><Phone class="h-3 w-3"/> Contact</p>
                <p class="text-sm font-medium text-gray-200">{{ activeDelivery.client_phone }}</p>
              </div>
              <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <p class="text-xs text-gray-500 mb-1 flex items-center gap-1"><Package class="h-3 w-3"/> Items</p>
                <p class="text-sm font-medium text-gray-200">{{ activeDelivery.items.length }} Package(s)</p>
              </div>
            </div>

            <div class="bg-gray-800/50 rounded-lg p-3 border border-gray-700">
                <p class="text-xs text-gray-500 mb-1 flex items-center gap-1"><MapPin class="h-3 w-3"/> Delivery Address</p>
                <p class="text-sm font-medium text-gray-200 leading-relaxed">{{ activeDelivery.delivery_address }}</p>
            </div>

            <div v-if="activeDelivery.status === 'assigned'" class="pt-2">
                <Button @click="startDelivery(activeDelivery.id)" :disabled="isProcessing" class="w-full bg-blue-600 hover:bg-blue-700 text-white" size="lg">
                    <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" />
                    <Navigation v-else class="mr-2 h-5 w-5" /> Start Trip
                </Button>
            </div>

            <div v-if="activeDelivery.status === 'in_transit'" class="space-y-4 pt-2">
                
                <Alert v-if="!isWithinRange" variant="destructive" class="bg-yellow-900/20 border-yellow-700/50 text-yellow-300 py-2">
                   <AlertTriangle class="h-4 w-4" />
                   <AlertDescription class="text-xs ml-2">You are too far from the address to complete this delivery.</AlertDescription>
                </Alert>

                <div>
                   <label class="text-xs font-semibold text-gray-400 mb-2 block">Upload Proof of Delivery *</label>
                   <div 
                      class="border border-dashed border-gray-700 rounded-xl flex flex-col items-center justify-center p-4 transition-colors relative"
                      :class="proofPreview ? 'bg-transparent' : 'bg-gray-800/30 hover:bg-gray-800'"
                   >
                      <input 
                         v-if="!proofPreview" type="file" accept="image/*" capture="environment"
                         class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                         @change="handleProofUpload" 
                      />
                      <div v-if="!proofPreview" class="text-center pointer-events-none">
                         <ImageIcon class="h-6 w-6 text-gray-500 mx-auto mb-2" />
                         <p class="text-sm font-medium text-gray-300">Tap to open Camera</p>
                      </div>
                      <div v-else class="relative w-full flex justify-center">
                         <img :src="proofPreview" class="max-h-48 rounded-lg border border-gray-700 object-cover shadow-lg" />
                         <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 h-8 w-8 rounded-full shadow-lg" @click.prevent.stop="removeProof">
                            <X class="h-4 w-4" />
                         </Button>
                      </div>
                   </div>
                </div>

                <Button 
                  @click="arriveAndComplete" 
                  :disabled="!isWithinRange || !proofFile || isProcessing" 
                  class="w-full bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-900/20" 
                  size="lg"
                >
                    <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" />
                    <CheckCircle2 v-else class="mr-2 h-5 w-5" /> Complete Delivery
                </Button>
            </div>

          </div>
        </ScrollArea>
      </div>

    </div>
  </div>
</template>

<style scoped>
/* Remove Leaflet Zoom Controls for cleaner UI */
:deep(.leaflet-control-container) {
  display: none; 
}
</style>