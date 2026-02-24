<template>
  <div class="min-h-screen w-full p-4 md:p-8 flex flex-col">
    
    <div v-if="!locationGranted" class="flex flex-col items-center justify-center flex-1 w-full max-w-md mx-auto text-center space-y-6">
      <div class="relative">
        <div class="absolute -inset-4 bg-blue-500/20 rounded-full animate-pulse blur-xl"></div>
        <div class="bg-blue-100 p-6 rounded-full relative shadow-inner border border-blue-200">
          <MapPin class="w-16 h-16 text-blue-600" />
        </div>
      </div>
      
      <div class="space-y-2">
        <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100">Location Required</h1>
        <p class="text-muted-foreground text-sm md:text-base px-4">
          To track your active deliveries and provide accurate updates to the distributor, you must enable location services.
        </p>
      </div>

      <Alert v-if="locationError" variant="destructive" class="text-left w-full shadow-sm">
        <AlertTriangle class="h-4 w-4" />
        <AlertTitle>Access Denied</AlertTitle>
        <AlertDescription>
          {{ locationError }} Please check your browser settings and try again.
        </AlertDescription>
      </Alert>

      <Button 
        size="lg" 
        class="w-full sm:w-auto px-8 py-6 text-lg font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all"
        @click="requestLocation"
        :disabled="isLoadingLocation"
      >
        <Loader2 v-if="isLoadingLocation" class="w-5 h-5 mr-2 animate-spin" />
        <Navigation v-else class="w-5 h-5 mr-2" />
        {{ isLoadingLocation ? 'Acquiring Signal...' : 'Enable Location Services' }}
      </Button>
    </div>

    <div v-else class="w-full flex-1 flex flex-col space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
      
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold tracking-tight flex items-center gap-3">
            My Deliveries
            <Badge variant="secondary" class="bg-blue-100 text-blue-700 hover:bg-blue-100 border-blue-200">
              <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse mr-2"></span>
              GPS Active
            </Badge>
          </h1>
          <p class="text-muted-foreground mt-1">Manage and track your assigned shipments.</p>
        </div>
        <div class="flex gap-2 text-sm text-muted-foreground bg-background px-4 py-2 rounded-lg border shadow-sm shrink-0">
          <Map class="w-4 h-4 text-emerald-500" />
          <span>Lat: {{ currentPosition?.lat.toFixed(4) }}</span>
          <span class="mx-1">|</span>
          <span>Lng: {{ currentPosition?.lng.toFixed(4) }}</span>
        </div>
      </div>

      <div class="grid gap-4 md:grid-cols-3 w-full">
        <Card class="border-l-4 border-l-amber-500 shadow-sm w-full">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Pending Assignment</CardTitle>
            <Package class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ pendingCount }}</div>
          </CardContent>
        </Card>
        <Card class="border-l-4 border-l-blue-500 shadow-sm w-full">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Active Transit</CardTitle>
            <Truck class="h-4 w-4 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-blue-600">{{ inTransitCount }}</div>
          </CardContent>
        </Card>
        <Card class="border-l-4 border-l-emerald-500 shadow-sm w-full">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Completed</CardTitle>
            <CheckCircle class="h-4 w-4 text-emerald-500" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold text-emerald-600">{{ completedCount }}</div>
          </CardContent>
        </Card>
      </div>

      <Tabs defaultValue="active" class="w-full flex-1 flex flex-col">
        <TabsList class="grid w-full grid-cols-2 max-w-[400px]">
          <TabsTrigger value="active">Active & Pending</TabsTrigger>
          <TabsTrigger value="completed">Completed History</TabsTrigger>
        </TabsList>

        <TabsContent value="active" class="mt-6 flex-1">
          <div v-if="isLoadingData" class="flex flex-col items-center justify-center py-20 w-full h-full">
            <Loader2 class="w-10 h-10 animate-spin text-primary opacity-50 mb-4" />
            <p class="text-muted-foreground">Loading your deliveries...</p>
          </div>

          <div v-else-if="activeDeliveries.length === 0" class="text-center py-20 border-2 border-dashed rounded-2xl bg-background/50 w-full h-full flex flex-col items-center justify-center">
            <CheckCircle class="w-12 h-12 text-muted-foreground mx-auto mb-4 opacity-50" />
            <h3 class="text-lg font-medium text-slate-900 dark:text-slate-100">You're all caught up!</h3>
            <p class="text-muted-foreground">No active deliveries assigned to you right now.</p>
          </div>

          <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 w-full">
            <Card v-for="delivery in activeDeliveries" :key="delivery.id" class="flex flex-col relative overflow-hidden transition-all hover:shadow-md border-t-0 border-x-0 border-b-0 shadow-sm ring-1 ring-slate-200 dark:ring-slate-800">
              
              <div class="h-1.5 w-full absolute top-0 left-0" :class="delivery.status === 'in_transit' ? 'bg-blue-500' : (delivery.status === 'remitting' ? 'bg-purple-500' : 'bg-amber-400')"></div>

              <CardHeader class="pb-3 pt-5">
                <div class="flex justify-between items-start mb-2">
                  <Badge 
                    :variant="delivery.status === 'assigned' ? 'outline' : 'default'" 
                    :class="{
                      'bg-blue-600 hover:bg-blue-700 text-white': delivery.status === 'in_transit',
                      'bg-amber-100 text-amber-800 border-amber-200': delivery.status === 'assigned',
                      'bg-purple-600 hover:bg-purple-700 text-white': delivery.status === 'remitting'
                    }"
                  >
                    {{ getStatusText(delivery.status) }}
                  </Badge>
                  <span class="text-xs font-mono font-semibold text-muted-foreground">{{ delivery.code }}</span>
                </div>
                <CardTitle class="text-lg truncate" :title="delivery.customer">
                  {{ delivery.status === 'remitting' ? 'Return to Supplier HQ' : delivery.customer }}
                </CardTitle>
                <CardDescription class="flex items-start gap-1.5 mt-1 text-slate-600 dark:text-slate-300">
                  <MapPin class="w-4 h-4 shrink-0 mt-0.5" :class="delivery.status === 'remitting' ? 'text-purple-500' : 'text-red-500'" />
                  <span class="line-clamp-2 leading-tight">
                    {{ delivery.status === 'remitting' ? 'Return collected COD funds to HQ' : delivery.address }}
                  </span>
                </CardDescription>
              </CardHeader>
              
              <CardContent class="flex-1 pb-4">
                <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-3 text-sm space-y-2 border border-slate-100 dark:border-slate-800">
                  <div class="flex justify-between">
                    <span class="text-muted-foreground font-medium flex items-center gap-1.5">
                      <Banknote v-if="delivery.paymentTerms === 'COD'" class="w-3.5 h-3.5 text-green-600" />
                      <CreditCard v-else class="w-3.5 h-3.5" /> 
                      Payment
                    </span>
                    <Badge variant="outline" :class="delivery.paymentTerms === 'COD' ? 'border-green-300 text-green-700 bg-green-50' : ''">
                      {{ delivery.paymentTerms }}
                    </Badge>
                  </div>
                  <div v-if="delivery.paymentTerms === 'COD' && delivery.status !== 'remitting'" class="flex justify-between">
                    <span class="text-muted-foreground font-medium">To Collect</span>
                    <span class="font-bold text-green-600">₱{{ parseFloat(delivery.totalAmount).toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-muted-foreground font-medium flex items-center gap-1.5">
                      <Package class="w-3.5 h-3.5" /> Items
                    </span>
                    <span class="font-semibold">{{ delivery.itemCount }} units</span>
                  </div>
                  <div v-if="delivery.status !== 'remitting'" class="flex justify-between">
                    <span class="text-muted-foreground font-medium flex items-center gap-1.5">
                      <Info class="w-3.5 h-3.5" /> Details
                    </span>
                    <span class="text-right truncate w-32" :title="delivery.productName">{{ delivery.productName }}</span>
                  </div>
                  
                  <div v-if="(delivery.status === 'in_transit' && delivery.latitude) || (delivery.status === 'remitting' && delivery.supplierLatitude)" class="flex justify-between border-t border-slate-200 pt-2 mt-2">
                    <span class="text-muted-foreground font-medium">Distance</span>
                    <span class="font-semibold" :class="calculateDistance(delivery) <= 500 ? 'text-emerald-600' : ''">
                      {{ (calculateDistance(delivery) / 1000).toFixed(2) }} km away
                    </span>
                  </div>
                </div>
              </CardContent>

              <CardFooter class="pt-0 pb-5 px-6 gap-3 flex-col sm:flex-row">
                <Button 
                  v-if="delivery.status === 'assigned'" 
                  class="w-full bg-slate-900 hover:bg-slate-800 text-white shadow-md"
                  @click="startDelivery(delivery.id)"
                  :disabled="isSubmitting === delivery.id"
                >
                  <Loader2 v-if="isSubmitting === delivery.id" class="w-4 h-4 mr-2 animate-spin" />
                  <Truck v-else class="w-4 h-4 mr-2" /> Start Delivery
                </Button>

                <div v-else-if="delivery.status === 'in_transit'" class="w-full flex gap-2">
                  <Button variant="outline" class="flex-1 border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100" @click="openMap(delivery)">
                    <Navigation class="w-4 h-4 mr-2" /> Map
                  </Button>
                  <Button 
                    class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-md disabled:opacity-50" 
                    @click="openArrivalModal(delivery)"
                    :disabled="isSubmitting === delivery.id || !isNearLocation(delivery)"
                    :title="!isNearLocation(delivery) ? 'You must be within 500 meters to arrive.' : 'Complete Delivery'"
                  >
                    <CheckCircle class="w-4 h-4 mr-2" /> Arrived
                  </Button>
                </div>

                <div v-else-if="delivery.status === 'remitting'" class="w-full flex gap-2">
                  <Button variant="outline" class="flex-1 border-purple-200 bg-purple-50 text-purple-700 hover:bg-purple-100" @click="openMap(delivery)">
                    <Navigation class="w-4 h-4 mr-2" /> Route
                  </Button>
                  <Button 
                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white shadow-md disabled:opacity-50" 
                    @click="openRemitModal(delivery)"
                    :disabled="isSubmitting === delivery.id || !isNearLocation(delivery)"
                    :title="!isNearLocation(delivery) ? 'You must be within 500m of Supplier HQ to remit.' : 'Remit Funds'"
                  >
                    <Banknote class="w-4 h-4 mr-2" /> Remit Funds
                  </Button>
                </div>
              </CardFooter>
            </Card>
          </div>
        </TabsContent>

        <TabsContent value="completed" class="mt-6 flex-1">
          <Card class="w-full h-full border-none shadow-none bg-transparent">
            <CardHeader class="px-0">
              <CardTitle>Delivery History</CardTitle>
              <CardDescription>Shipments you have successfully delivered and remitted.</CardDescription>
            </CardHeader>
            <CardContent class="px-0">
              <div v-if="completedDeliveries.length === 0" class="text-center py-20 border-2 border-dashed rounded-2xl bg-background/50">
                <p class="text-muted-foreground">No deliveries completed yet.</p>
              </div>
              <div v-else class="space-y-4">
                <div v-for="delivery in completedDeliveries" :key="delivery.id" class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border rounded-xl bg-background shadow-sm hover:shadow transition-shadow">
                  <div class="flex items-start gap-4">
                    <div class="h-10 w-10 shrink-0 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center mt-1 sm:mt-0">
                      <CheckCircle class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                      <p class="font-bold text-slate-900 dark:text-slate-100">{{ delivery.customer }}</p>
                      <p class="text-sm text-muted-foreground font-mono mt-0.5">{{ delivery.code }} • {{ delivery.itemCount }} units</p>
                      <p class="text-xs text-muted-foreground mt-1 line-clamp-1 max-w-2xl">{{ delivery.address }}</p>
                    </div>
                  </div>
                  <div class="mt-4 sm:mt-0 flex items-center gap-4 sm:text-right border-t sm:border-t-0 pt-3 sm:pt-0 border-slate-200 shrink-0">
                    <img v-if="delivery.arrivalProof" :src="delivery.arrivalProof" alt="Proof" class="w-12 h-12 object-cover rounded-md border" />
                    <div>
                      <Badge variant="secondary" class="bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border-none mb-1">
                        Completed
                      </Badge>
                      <p class="text-xs text-muted-foreground block">{{ delivery.timestamp }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>

      <Dialog v-model:open="showMapModal">
        <DialogContent class="max-w-4xl h-[80vh] flex flex-col p-0 overflow-hidden">
          <DialogHeader class="p-4 border-b bg-white z-10">
            <DialogTitle>
              Route to {{ selectedMapDelivery?.status === 'remitting' ? 'Supplier HQ' : selectedMapDelivery?.customer }}
            </DialogTitle>
          </DialogHeader>
          <div id="delivery-map" class="flex-1 w-full z-0 bg-slate-100"></div>
        </DialogContent>
      </Dialog>

      <Dialog v-model:open="showArrivalModal">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Confirm Arrival</DialogTitle>
            <DialogDescription>
              Provide proof of delivery. <span v-if="selectedDeliveryIsCOD" class="font-bold text-amber-600">This is a COD order. You must also upload proof of payment received.</span>
            </DialogDescription>
          </DialogHeader>
          
          <ScrollArea class="max-h-[60vh] pr-4">
            <div class="grid gap-6 py-4">
              <div class="space-y-2">
                <Label class="font-semibold">Proof of Goods Delivery <span class="text-red-500">*</span></Label>
                <div v-if="!arrivalProofPreview" class="flex items-center justify-center w-full">
                  <label for="arrival-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-slate-500">
                      <UploadCloud class="w-8 h-8 mb-2" />
                      <p class="text-xs font-semibold">Upload Photo of Goods</p>
                    </div>
                    <input id="arrival-file" type="file" class="hidden" accept="image/*" @change="(e) => handleImageSelect(e, 'arrival')" />
                  </label>
                </div>
                <div v-else class="relative w-full h-32 rounded-lg overflow-hidden border">
                  <img :src="arrivalProofPreview" class="w-full h-full object-cover" />
                  <Button variant="destructive" size="icon" class="absolute top-2 right-2 h-6 w-6" @click="removeImage('arrival')">
                    <X class="h-3 w-3" />
                  </Button>
                </div>
              </div>

              <div v-if="selectedDeliveryIsCOD" class="space-y-2 border-t pt-4">
                <Label class="font-semibold text-green-700">Proof of Payment Received <span class="text-red-500">*</span></Label>
                <div class="text-xs text-muted-foreground mb-2">Collect <span class="font-bold">₱{{ parseFloat(selectedArrivalDelivery?.totalAmount).toLocaleString() }}</span> from distributor.</div>
                
                <div v-if="!paymentProofPreview" class="flex items-center justify-center w-full">
                  <label for="payment-file" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-green-300 rounded-lg cursor-pointer bg-green-50 hover:bg-green-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-green-600">
                      <Banknote class="w-8 h-8 mb-2" />
                      <p class="text-xs font-semibold">Upload Photo of Cash</p>
                    </div>
                    <input id="payment-file" type="file" class="hidden" accept="image/*" @change="(e) => handleImageSelect(e, 'payment')" />
                  </label>
                </div>
                <div v-else class="relative w-full h-32 rounded-lg overflow-hidden border">
                  <img :src="paymentProofPreview" class="w-full h-full object-cover" />
                  <Button variant="destructive" size="icon" class="absolute top-2 right-2 h-6 w-6" @click="removeImage('payment')">
                    <X class="h-3 w-3" />
                  </Button>
                </div>
              </div>
            </div>
          </ScrollArea>

          <DialogFooter>
            <Button variant="outline" @click="showArrivalModal = false" :disabled="isUploading">Cancel</Button>
            <Button @click="submitArrival" :disabled="!canSubmitArrival || isUploading" class="bg-emerald-600 hover:bg-emerald-700 text-white">
              <Loader2 v-if="isUploading" class="w-4 h-4 mr-2 animate-spin" />
              <CheckCircle v-else class="w-4 h-4 mr-2" /> 
              {{ isUploading ? 'Processing...' : (selectedDeliveryIsCOD ? 'Confirm Arrival & Payment' : 'Confirm Delivery') }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <Dialog v-model:open="showRemitModal">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Remit Funds to HQ</DialogTitle>
            <DialogDescription>
              Upload a photo showing the cash handed over to the Supplier HQ staff.
            </DialogDescription>
          </DialogHeader>
          <div class="grid gap-4 py-4">
            <div v-if="!remittanceProofPreview" class="flex items-center justify-center w-full">
              <label for="remit-file" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-purple-300 rounded-lg cursor-pointer bg-purple-50 hover:bg-purple-100">
                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-purple-600">
                  <UploadCloud class="w-10 h-10 mb-3" />
                  <p class="mb-2 text-sm"><span class="font-semibold">Click to upload money handover</span></p>
                  <p class="text-xs">PNG, JPG or JPEG (MAX. 5MB)</p>
                </div>
                <input id="remit-file" type="file" class="hidden" accept="image/*" @change="(e) => handleImageSelect(e, 'remittance')" />
              </label>
            </div>
            <div v-else class="relative w-full h-48 rounded-lg overflow-hidden border">
              <img :src="remittanceProofPreview" class="w-full h-full object-cover" />
              <Button variant="destructive" size="icon" class="absolute top-2 right-2 h-8 w-8 shadow-sm" @click="removeImage('remittance')">
                <X class="h-4 w-4" />
              </Button>
            </div>
          </div>
          <DialogFooter>
            <Button variant="outline" @click="showRemitModal = false" :disabled="isUploading">Cancel</Button>
            <Button @click="submitRemittance" :disabled="!remittanceProofFile || isUploading" class="bg-purple-600 hover:bg-purple-700 text-white">
              <Loader2 v-if="isUploading" class="w-4 h-4 mr-2 animate-spin" />
              <Banknote v-else class="w-4 h-4 mr-2" /> 
              {{ isUploading ? 'Submitting...' : 'Complete Turnover' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onUnmounted } from 'vue'
import { 
  MapPin, Navigation, Map, Truck, Package, CheckCircle, 
  AlertTriangle, Loader2, Info, UploadCloud, X, Banknote, CreditCard
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Alert, AlertTitle, AlertDescription } from '@/components/ui/alert'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { ScrollArea } from '@/components/ui/scroll-area'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'

// Leaflet Imports
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

// --- State ---
const deliveries = ref([])
const locationGranted = ref(false)
const isLoadingLocation = ref(false)
const isLoadingData = ref(true)
const isSubmitting = ref(null)
const locationError = ref(null)
const currentPosition = ref(null)
let watchId = null

// Map Modal State
const showMapModal = ref(false)
const selectedMapDelivery = ref(null)
let leafletMap = null

// Upload Modals State
const showArrivalModal = ref(false)
const showRemitModal = ref(false)
const selectedArrivalDelivery = ref(null)
const selectedRemitDelivery = ref(null)

// Files
const arrivalProofFile = ref(null)
const arrivalProofPreview = ref(null)
const paymentProofFile = ref(null)
const paymentProofPreview = ref(null)
const remittanceProofFile = ref(null)
const remittanceProofPreview = ref(null)

const isUploading = ref(false)

// --- Computed ---
const activeDeliveries = computed(() => deliveries.value.filter(d => d.status !== 'completed' && d.status !== 'delivered')) 
// We treat completed as the only end-state for driver dashboard. If it's non-COD, it jumps to completed.
const completedDeliveries = computed(() => deliveries.value.filter(d => d.status === 'completed' || d.status === 'delivered'))

const pendingCount = computed(() => deliveries.value.filter(d => d.status === 'assigned').length)
const inTransitCount = computed(() => deliveries.value.filter(d => d.status === 'in_transit' || d.status === 'remitting').length)
const completedCount = computed(() => completedDeliveries.value.length)

const selectedDeliveryIsCOD = computed(() => {
  return selectedArrivalDelivery.value?.paymentTerms === 'COD'
})

const canSubmitArrival = computed(() => {
  if (!arrivalProofFile.value) return false
  if (selectedDeliveryIsCOD.value && !paymentProofFile.value) return false
  return true
})

// --- Helpers ---
const getStatusText = (status) => {
  if (status === 'assigned') return 'Ready for Pickup'
  if (status === 'in_transit') return 'On the Way'
  if (status === 'remitting') return 'Returning Funds'
  return 'Completed'
}

// --- Backend Requests ---
const fetchDeliveries = async () => {
  isLoadingData.value = true
  try {
    const response = await api.get('/supplier-delivery')
    deliveries.value = response.data
  } catch (error) {
    toast.error('Failed to load deliveries')
    console.error(error)
  } finally {
    isLoadingData.value = false
  }
}

const startDelivery = async (id) => {
  isSubmitting.value = id
  try {
    await api.post(`/supplier-delivery/${id}/start`)
    toast.success('Delivery started. Drive safely!')
    await fetchDeliveries()
  } catch (error) {
    toast.error('Failed to start delivery')
  } finally {
    isSubmitting.value = null
  }
}

// --- Upload Logic ---
const openArrivalModal = (delivery) => {
  selectedArrivalDelivery.value = delivery
  arrivalProofFile.value = null
  arrivalProofPreview.value = null
  paymentProofFile.value = null
  paymentProofPreview.value = null
  showArrivalModal.value = true
}

const openRemitModal = (delivery) => {
  selectedRemitDelivery.value = delivery
  remittanceProofFile.value = null
  remittanceProofPreview.value = null
  showRemitModal.value = true
}

const handleImageSelect = (event, type) => {
  const file = event.target.files[0]
  if (!file) return

  if (!file.type.match('image.*')) {
    toast.error("Please upload an image file (PNG, JPG).")
    return
  }
  if (file.size > 5 * 1024 * 1024) {
    toast.error("File size too large. Max 5MB allowed.")
    return
  }

  const url = URL.createObjectURL(file)
  if (type === 'arrival') {
    arrivalProofFile.value = file; arrivalProofPreview.value = url;
  } else if (type === 'payment') {
    paymentProofFile.value = file; paymentProofPreview.value = url;
  } else if (type === 'remittance') {
    remittanceProofFile.value = file; remittanceProofPreview.value = url;
  }
}

const removeImage = (type) => {
  if (type === 'arrival') { arrivalProofFile.value = null; arrivalProofPreview.value = null; }
  if (type === 'payment') { paymentProofFile.value = null; paymentProofPreview.value = null; }
  if (type === 'remittance') { remittanceProofFile.value = null; remittanceProofPreview.value = null; }
}

const submitArrival = async () => {
  if (!canSubmitArrival.value) return

  isUploading.value = true
  const formData = new FormData()
  formData.append('latitude', currentPosition.value.lat)
  formData.append('longitude', currentPosition.value.lng)
  formData.append('proof_image', arrivalProofFile.value)

  if (selectedDeliveryIsCOD.value) {
    formData.append('payment_image', paymentProofFile.value)
  }

  try {
    await api.post(`/supplier-delivery/${selectedArrivalDelivery.value.id}/arrive`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (selectedDeliveryIsCOD.value) {
      toast.success('Distributor delivery complete! Return to HQ to remit funds.')
    } else {
      toast.success('Delivery cycle completed successfully!')
    }
    showArrivalModal.value = false
    await fetchDeliveries()
  } catch (error) {
    if (error.response?.status === 400) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to process arrival')
    }
  } finally {
    isUploading.value = false
  }
}

const submitRemittance = async () => {
  if (!selectedRemitDelivery.value || !remittanceProofFile.value) return

  isUploading.value = true
  const formData = new FormData()
  formData.append('latitude', currentPosition.value.lat)
  formData.append('longitude', currentPosition.value.lng)
  formData.append('remittance_image', remittanceProofFile.value)

  try {
    await api.post(`/supplier-delivery/${selectedRemitDelivery.value.id}/remit`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    toast.success('Funds successfully remitted. Delivery completed!')
    showRemitModal.value = false
    await fetchDeliveries()
  } catch (error) {
    if (error.response?.status === 400) {
      toast.error(error.response.data.message)
    } else {
      toast.error('Failed to submit remittance')
    }
  } finally {
    isUploading.value = false
  }
}

// --- Distance & Geospatial Logic ---
const calculateDistance = (delivery) => {
  if (!currentPosition.value) return 0

  // Target coordinates depend on the status
  let targetLat, targetLng;
  if (delivery.status === 'remitting') {
    targetLat = delivery.supplierLatitude
    targetLng = delivery.supplierLongitude
  } else {
    targetLat = delivery.latitude
    targetLng = delivery.longitude
  }

  if (!targetLat || !targetLng) return 0
  
  const R = 6371e3 
  const lat1 = currentPosition.value.lat * Math.PI/180
  const lat2 = targetLat * Math.PI/180
  const deltaLat = (targetLat - currentPosition.value.lat) * Math.PI/180
  const deltaLon = (targetLng - currentPosition.value.lng) * Math.PI/180

  const a = Math.sin(deltaLat/2) * Math.sin(deltaLat/2) +
            Math.cos(lat1) * Math.cos(lat2) *
            Math.sin(deltaLon/2) * Math.sin(deltaLon/2)
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))

  return R * c 
}

const isNearLocation = (delivery) => {
  if (!currentPosition.value) return false
  
  // Choose which coords to validate based on status
  let targetLat, targetLng;
  if (delivery.status === 'remitting') {
    targetLat = delivery.supplierLatitude
    targetLng = delivery.supplierLongitude
  } else {
    targetLat = delivery.latitude
    targetLng = delivery.longitude
  }

  // If no coords exist in DB, bypass lock
  if (!targetLat || !targetLng) return true 
  
  return calculateDistance(delivery) <= 500 
}

// --- Map Logic ---
const openMap = (delivery) => {
  selectedMapDelivery.value = delivery
  showMapModal.value = true
  
  nextTick(() => {
    if (leafletMap) { leafletMap.remove() }
    
    leafletMap = L.map('delivery-map').setView([currentPosition.value.lat, currentPosition.value.lng], 14)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(leafletMap)

    const currentIcon = L.divIcon({
      html: `<div class="w-4 h-4 bg-blue-500 rounded-full border-2 border-white shadow-[0_0_10px_rgba(59,130,246,0.8)]"></div>`,
      className: 'bg-transparent'
    })
    L.marker([currentPosition.value.lat, currentPosition.value.lng], { icon: currentIcon })
      .addTo(leafletMap).bindPopup('You are here.').openPopup()

    let targetLat, targetLng, targetName, targetAddr;
    if (delivery.status === 'remitting') {
      targetLat = delivery.supplierLatitude
      targetLng = delivery.supplierLongitude
      targetName = "Supplier HQ"
      targetAddr = "Return to remit COD funds"
    } else {
      targetLat = delivery.latitude
      targetLng = delivery.longitude
      targetName = delivery.customer
      targetAddr = delivery.address
    }

    if (targetLat && targetLng) {
      const destIcon = L.divIcon({
        html: `<div class="w-5 h-5 ${delivery.status === 'remitting' ? 'bg-purple-500' : 'bg-red-500'} rounded-full border-2 border-white shadow-md"></div>`,
        className: 'bg-transparent'
      })
      L.marker([targetLat, targetLng], { icon: destIcon })
        .addTo(leafletMap)
        .bindPopup(`<b>${targetName}</b><br>${targetAddr}`)
      
      const bounds = L.latLngBounds([
        [currentPosition.value.lat, currentPosition.value.lng],
        [targetLat, targetLng]
      ])
      leafletMap.fitBounds(bounds, { padding: [50, 50] })
    }
  })
}

// --- Geolocation Tracking ---
const requestLocation = () => {
  isLoadingLocation.value = true
  locationError.value = null

  if (!navigator.geolocation) {
    locationError.value = "Geolocation is not supported by your browser."
    isLoadingLocation.value = false
    return
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      locationGranted.value = true
      currentPosition.value = { lat: position.coords.latitude, lng: position.coords.longitude }
      isLoadingLocation.value = false
      toast.success("Location connected successfully!")
      
      fetchDeliveries() 
      startTracking()   
    },
    (error) => {
      isLoadingLocation.value = false
      switch(error.code) {
        case error.PERMISSION_DENIED: locationError.value = "You denied the request for Geolocation."; break;
        case error.POSITION_UNAVAILABLE: locationError.value = "Location information is unavailable."; break;
        case error.TIMEOUT: locationError.value = "The request to get user location timed out."; break;
        default: locationError.value = "An unknown error occurred."; break;
      }
    },
    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
  )
}

const startTracking = () => {
  watchId = navigator.geolocation.watchPosition(
    (position) => {
      currentPosition.value = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
      }
    },
    (error) => console.error("Tracking error:", error),
    { enableHighAccuracy: true }
  )
}

onUnmounted(() => {
  if (watchId !== null) navigator.geolocation.clearWatch(watchId)
  if (leafletMap) leafletMap.remove()
})
</script>

<style scoped>
@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes slide-in-from-bottom-4 { from { transform: translateY(1rem); } to { transform: translateY(0); } }
.animate-in { animation-fill-mode: both; }
.fade-in { animation-name: fade-in; }
.slide-in-from-bottom-4 { animation-name: slide-in-from-bottom-4; }
.duration-500 { animation-duration: 500ms; }
</style>