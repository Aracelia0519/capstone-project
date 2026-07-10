<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Supplier Shipments</h1>
      <p class="text-muted-foreground">Manage prepared orders and assign delivery personnel.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Ready for Delivery</CardTitle>
          <Package class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ preparedOrders.length }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Ready</CardTitle>
          <Truck class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ pendingReadyCount }}</div>
        </CardContent>
      </Card>
    </div>

    <Tabs default-value="prepared" class="w-full">
      <TabsList>
        <TabsTrigger value="prepared">Prepared ({{ preparedOrders.length }})</TabsTrigger>
        <TabsTrigger value="shipped">Delivery History</TabsTrigger>
        <TabsTrigger value="pending">Pending Ready ({{ pendingReadyCount }})</TabsTrigger>
      </TabsList>

      <!-- PREPARED TAB -->
      <TabsContent value="prepared" class="space-y-4">
        <div v-if="loading" class="py-10 text-center">
            <p class="text-muted-foreground">Loading shipments...</p>
        </div>
        
        <div v-else-if="preparedOrders.length === 0" class="flex flex-col items-center justify-center py-10 text-center border rounded-lg border-dashed">
          <PackageOpen class="h-10 w-10 text-muted-foreground mb-4" />
          <p class="text-lg font-medium">No prepared orders</p>
          <p class="text-sm text-muted-foreground">Wait for orders to be prepared before shipping.</p>
        </div>

        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
          <Card v-for="order in preparedOrders" :key="order.unique_id" class="flex flex-col">
            <CardHeader>
              <div class="flex justify-between items-start">
                <div>
                  <CardTitle class="flex items-center gap-2">
                    {{ order.display_id }}
                    <Badge v-if="order.type === 'return'" variant="destructive" class="text-[10px] bg-red-500">Replacement</Badge>
                  </CardTitle>
                  <CardDescription>{{ order.customer }}</CardDescription>
                </div>
                <Badge variant="outline" class="bg-yellow-100 text-yellow-800 hover:bg-yellow-100 border-yellow-200">
                  {{ order.status }}
                </Badge>
              </div>
            </CardHeader>
            <CardContent class="flex-1">
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Items:</span>
                  <span class="font-medium">{{ order.items }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Units:</span>
                  <span class="font-medium">{{ order.weight }}</span>
                </div>
                <div class="flex flex-col mt-2 p-2 bg-gray-50 rounded text-xs text-muted-foreground">
                    <span class="font-semibold mb-1">Delivery Address:</span>
                    <span>{{ order.delivery_address || 'No address provided' }}</span>
                </div>

                <div v-if="order.rejection_reason" class="mt-3 p-3 bg-red-50 border border-red-200 rounded-md text-xs text-red-700">
                    <div class="flex items-center gap-1.5 font-bold mb-1 text-red-800">
                        <AlertTriangle class="h-4 w-4" />
                        Delivery Rejected
                    </div>
                    <p class="pl-5 text-[11px] leading-relaxed">{{ order.rejection_reason }}</p>
                </div>

                <div class="mt-4 pt-4 border-t">
                  <Label class="mb-2 block">Assign Vehicle</Label>
                  <select 
                    v-model="order.selectedVehicleId" 
                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:opacity-50"
                  >
                    <option value="" disabled selected>Select Available Vehicle</option>
                    <option v-for="veh in vehiclesList" :key="veh.id" :value="veh.id">
                      {{ veh.name }} (Avail: {{ veh.capacity }}kg / Max: {{ veh.max_capacity }}kg)
                    </option>
                  </select>
                  
                  <div v-if="order.selectedVehicleId" class="mt-2 text-xs">
                     <span :class="isOverweight(order) ? 'text-red-600 font-bold' : 'text-green-600 font-medium'">
                        Calculated Load: {{ order.totalWeight }} kg / {{ getVehicleCapacity(order.selectedVehicleId) }} kg Avail.
                     </span>
                     <p v-if="isOverweight(order)" class="text-red-600 mt-1 font-semibold text-[11px]">Warning: Weight exceeds remaining vehicle capacity. Assignment disabled.</p>
                  </div>
                </div>

                <div class="mt-4 pt-4 border-t">
                  <Label class="mb-2 block">Assign Delivery Personnel</Label>
                  <select 
                    v-model="order.selectedPersonnelId" 
                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="" disabled selected>Select Delivery Man</option>
                    <option v-for="person in deliveryPersonnelList" :key="person.id" :value="person.id">
                      {{ person.name }}
                    </option>
                  </select>
                </div>

                <div class="mt-4 pt-4 border-t">
                  <Label class="mb-2 block">Proof of Readiness (Image)</Label>
                  
                  <div v-if="!order.previewImage" class="flex items-center justify-center w-full">
                    <label :for="'file-upload-' + order.unique_id" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                      <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <UploadCloud class="w-8 h-8 mb-2 text-gray-400" />
                        <p class="text-xs text-gray-500">Click to upload image</p>
                      </div>
                      <input 
                        :id="'file-upload-' + order.unique_id" 
                        type="file" 
                        class="hidden" 
                        accept="image/*"
                        @change="(e) => handleImageSelect(e, order.unique_id)"
                      />
                    </label>
                  </div>

                  <div v-else class="relative w-full h-40 rounded-md overflow-hidden border">
                    <img :src="order.previewImage" alt="Proof" class="w-full h-full object-cover" />
                    <Button 
                      variant="destructive" 
                      size="icon" 
                      class="absolute top-2 right-2 h-6 w-6 shadow-sm"
                      @click="removeImage(order.unique_id)"
                    >
                      <X class="h-4 w-4" />
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
            <CardFooter>
              <AlertDialog>
                <AlertDialogTrigger as-child>
                  <Button 
                    class="w-full" 
                    :disabled="!order.fileObject || !order.selectedPersonnelId || !order.selectedVehicleId || isOverweight(order) || isSubmitting" 
                  >
                    <Truck class="mr-2 h-4 w-4" />
                    {{ isSubmitting ? 'Processing...' : 'Assign & Add to Truck' }}
                  </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Confirm Assignment to Truck</AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to assign 
                      <strong>{{ getPersonnelName(order.selectedPersonnelId) }}</strong> and <strong>Vehicle: {{ getVehicleName(order.selectedVehicleId) }}</strong> to deliver {{ order.type === 'return' ? 'replacement' : 'order' }} <strong>{{ order.display_id }}</strong>?
                      <br><br>
                      This will place the delivery in the truck's staging bay. You will need to explicitly mark it "Ready to go" in the Delivery History tab once fully loaded.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="submitShipment(order.unique_id)">Continue Allocation</AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </CardFooter>
          </Card>
        </div>
      </TabsContent>

      <!-- DELIVERY HISTORY TAB (ALL SHIPPED/ASSIGNED) -->
      <TabsContent value="shipped">
        <Card>
          <CardHeader>
            <CardTitle>Delivery History & Dispatch Terminal</CardTitle>
            <CardDescription>All shipments, including those already dispatched.</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="shippedOrders.length === 0" class="text-center py-4 text-muted-foreground">
                No delivery history yet.
            </div>
            <div v-else class="space-y-4">
              <div v-for="order in shippedOrders" :key="order.unique_id" class="flex flex-col md:flex-row md:items-center justify-between p-4 border rounded-lg bg-gray-50/50 gap-4">
                <div class="flex items-center gap-4">
                  <div :class="[
                      'h-10 w-10 rounded-full flex items-center justify-center shrink-0', 
                      order.status === 'Delivered' ? 'bg-emerald-100' : 'bg-blue-100'
                    ]"
                  >
                    <Check v-if="order.status === 'Delivered'" class="h-5 w-5 text-emerald-600" />
                    <Truck v-else class="h-5 w-5 text-blue-600" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-medium text-slate-800">{{ order.display_id }}</p>
                      <Badge v-if="order.type === 'return'" variant="destructive" class="text-[10px] h-4 py-0">Replacement</Badge>
                    </div>
                    <p class="text-sm text-slate-500">To: {{ order.customer }}</p>
                    <p class="text-xs text-slate-500">Items: {{ order.items }}</p>
                    <p class="text-xs font-semibold text-slate-400 mt-1">Carrier: {{ order.vehicle_name }}</p>
                  </div>
                </div>
                
                <div class="flex flex-col md:items-end justify-between items-start gap-3 w-full md:w-auto mt-2 md:mt-0 pt-3 md:pt-0 border-t md:border-t-0 border-slate-200">
                    <Badge 
                      variant="secondary" 
                      class="mb-1 text-sm font-semibold uppercase"
                      :class="order.status === 'Delivered' ? 'bg-emerald-100 text-emerald-800 border-0' : 'bg-blue-100 text-blue-800 border-0'"
                    >
                      {{ order.status }}
                    </Badge>

                    <!-- Button appears only if status is 'shipped' or 'assigned' (case‑insensitive) and not ready -->
                    <div v-if="isPendingReady(order)" class="w-full">
                       <Button size="sm" class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold h-8" @click="signalReadyToGo(order.delivery_id)">
                         <Play class="w-3.5 h-3.5 mr-1" /> Signal "Ready to Go"
                       </Button>
                    </div>
                    <div v-else-if="isShippedOrAssigned(order) && order.is_ready_to_go">
                       <Badge variant="outline" class="bg-green-50 text-green-700 border-green-200 uppercase tracking-widest text-[10px]">
                         Driver Notified
                       </Badge>
                    </div>

                    <p class="text-xs text-slate-400 font-mono text-right w-full whitespace-nowrap">{{ order.shipped_at }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- PENDING READY TAB (ONLY SHIPMENTS NEEDING THE SIGNAL) -->
      <TabsContent value="pending">
        <Card>
          <CardHeader>
            <CardTitle>Pending “Ready to Go” Signals</CardTitle>
            <CardDescription>Shipments that have been assigned but not yet marked ready for the driver.</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="pendingReadyOrders.length === 0" class="text-center py-4 text-muted-foreground">
                All shipments have been signaled ready.
            </div>
            <div v-else class="space-y-4">
              <div v-for="order in pendingReadyOrders" :key="order.unique_id" class="flex flex-col md:flex-row md:items-center justify-between p-4 border rounded-lg bg-yellow-50/50 border-yellow-200 gap-4">
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center shrink-0">
                    <Truck class="h-5 w-5 text-yellow-600" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-medium text-slate-800">{{ order.display_id }}</p>
                      <Badge v-if="order.type === 'return'" variant="destructive" class="text-[10px] h-4 py-0">Replacement</Badge>
                    </div>
                    <p class="text-sm text-slate-500">To: {{ order.customer }}</p>
                    <p class="text-xs text-slate-500">Items: {{ order.items }}</p>
                    <p class="text-xs font-semibold text-slate-400 mt-1">Carrier: {{ order.vehicle_name }}</p>
                  </div>
                </div>
                <div class="flex flex-col md:items-end gap-2 w-full md:w-auto">
                  <Badge class="bg-yellow-200 text-yellow-800 border-0 uppercase">Waiting for Ready</Badge>
                  <Button size="sm" class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold h-8" @click="signalReadyToGo(order.delivery_id)">
                    <Play class="w-3.5 h-3.5 mr-1" /> Signal "Ready to Go"
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </TabsContent>
    </Tabs>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '@/utils/axios' 
import { toast } from 'vue-sonner' 
import { 
  Package, 
  Truck, 
  UploadCloud, 
  X, 
  Check, 
  PackageOpen,
  AlertTriangle,
  Play
} from 'lucide-vue-next'

import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Label } from '@/components/ui/label'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

const preparedOrders = ref([])
const shippedOrders = ref([])
const deliveryPersonnelList = ref([]) 
const vehiclesList = ref([]) 

const loading = ref(true)
const isSubmitting = ref(false)

// Computed: pending ready orders (shipped or assigned and not ready)
const pendingReadyOrders = computed(() => 
  shippedOrders.value.filter(order => isPendingReady(order))
)

const pendingReadyCount = computed(() => pendingReadyOrders.value.length)

// Helper functions for status checks (case‑insensitive)
const isShippedOrAssigned = (order) => {
  if (!order.status) return false
  const lower = order.status.toLowerCase()
  return lower === 'shipped' || lower === 'assigned'
}

const isPendingReady = (order) => {
  return isShippedOrAssigned(order) && !order.is_ready_to_go
}

const getPersonnelName = (id) => {
  const person = deliveryPersonnelList.value.find(p => p.id === id)
  return person ? person.name : 'Unknown Personnel'
}

const getVehicleName = (id) => {
  const v = vehiclesList.value.find(veh => veh.id === id)
  return v ? v.name : 'Unknown Vehicle'
}

const getVehicleCapacity = (id) => {
  const v = vehiclesList.value.find(veh => veh.id === id)
  return v ? v.capacity : 0
}

const isOverweight = (order) => {
  if (!order.selectedVehicleId) return false
  return order.totalWeight > getVehicleCapacity(order.selectedVehicleId)
}

const fetchShipments = async () => {
  loading.value = true
  try {
    const response = await axios.get('/supplier/shipments')
    vehiclesList.value = response.data.vehicles

    preparedOrders.value = response.data.prepared_orders.map(order => ({
        ...order,
        previewImage: null,
        fileObject: null,
        selectedPersonnelId: '',
        selectedVehicleId: ''
    }))
    
    shippedOrders.value = response.data.shipped_orders
    deliveryPersonnelList.value = response.data.delivery_personnel 
  } catch (error) {
    console.error("Error fetching shipments:", error)
    toast.error('Failed to load shipments. Please try again.')
  } finally {
    loading.value = false
  }
}

onMounted(() => {
    fetchShipments()
})

const handleImageSelect = (event, uniqueId) => {
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

  const imageUrl = URL.createObjectURL(file)
  
  const orderIndex = preparedOrders.value.findIndex(o => o.unique_id === uniqueId)
  if (orderIndex !== -1) {
    preparedOrders.value[orderIndex].previewImage = imageUrl
    preparedOrders.value[orderIndex].fileObject = file
    toast.success("Proof image attached successfully!")
  }
}

const removeImage = (uniqueId) => {
  const orderIndex = preparedOrders.value.findIndex(o => o.unique_id === uniqueId)
  if (orderIndex !== -1) {
    preparedOrders.value[orderIndex].previewImage = null
    preparedOrders.value[orderIndex].fileObject = null
    toast.info("Image removed.")
  }
}

const submitShipment = async (uniqueId) => {
  const order = preparedOrders.value.find(o => o.unique_id === uniqueId)
  
  if (!order || !order.fileObject) {
    toast.error("Please upload a proof image before shipping.")
    return
  }
  if (!order.selectedPersonnelId) {
    toast.error("Please assign a delivery personnel first.")
    return
  }
  if (!order.selectedVehicleId) {
    toast.error("Please assign a vehicle to this dispatch load.")
    return
  }
  if (isOverweight(order)) {
    toast.error("Weight violation! Selected vehicle cannot hold this payload.")
    return
  }

  isSubmitting.value = true
  const toastId = toast.loading('Adding package to the vehicle...')

  const formData = new FormData()
  formData.append('image', order.fileObject)
  formData.append('delivery_personnel_id', order.selectedPersonnelId) 
  formData.append('vehicle_id', order.selectedVehicleId) 
  formData.append('type', order.type)
  formData.append('_method', 'POST') 

  try {
    await axios.post(`/supplier/shipments/${order.id}/ship`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    
    await fetchShipments()
    
    toast.success(`${order.type === 'return' ? 'Replacement' : 'Order'} ${order.display_id} is loaded. Pending 'Ready to Go' flag.`, {
      id: toastId
    })

  } catch (error) {
    console.error("Error submitting shipment:", error)
    toast.error(error.response?.data?.message || "Failed to update shipment status.", {
      id: toastId
    })
  } finally {
    isSubmitting.value = false
  }
}

const signalReadyToGo = async (deliveryId) => {
  if (!deliveryId) return;
  const tId = toast.loading('Sending Ready signal...')
  try {
    await axios.post(`/supplier/shipments/${deliveryId}/ready`)
    toast.success('Ready signal successfully dispatched to Driver Application!', { id: tId })
    await fetchShipments()
  } catch (error) {
    toast.error('Failed to dispatch ready signal to terminal.', { id: tId })
  }
}
</script>