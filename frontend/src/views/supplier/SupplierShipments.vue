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
          <CardTitle class="text-sm font-medium">Delivery History</CardTitle>
          <Truck class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ shippedOrders.length }}</div>
        </CardContent>
      </Card>
    </div>

    <Tabs default-value="prepared" class="w-full">
      <TabsList>
        <TabsTrigger value="prepared">Prepared ({{ preparedOrders.length }})</TabsTrigger>
        <TabsTrigger value="shipped">Delivery History</TabsTrigger>
      </TabsList>

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
                  <span class="text-muted-foreground">Details:</span>
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
                    :disabled="!order.fileObject || !order.selectedPersonnelId || isSubmitting" 
                  >
                    <Truck class="mr-2 h-4 w-4" />
                    {{ isSubmitting ? 'Processing...' : (order.fileObject && order.selectedPersonnelId ? 'Set Delivery' : 'Assign & Upload Proof') }}
                  </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Confirm Assignment & Shipment</AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to assign 
                      <strong>{{ getPersonnelName(order.selectedPersonnelId) }}</strong> to deliver {{ order.type === 'return' ? 'replacement' : 'order' }} <strong>{{ order.display_id }}</strong>?
                      <br><br>
                      This will notify the distributor and mark the {{ order.type === 'return' ? 'replacement' : 'order' }} as in transit.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="submitShipment(order.unique_id)">Continue to Ship</AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </CardFooter>
          </Card>
        </div>
      </TabsContent>

      <TabsContent value="shipped">
        <Card>
          <CardHeader>
            <CardTitle>Delivery History</CardTitle>
            <CardDescription>Recent shipments and their transit status.</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="shippedOrders.length === 0" class="text-center py-4 text-muted-foreground">
                No delivery history yet.
            </div>
            <div v-else class="space-y-4">
              <div v-for="order in shippedOrders" :key="order.unique_id" class="flex items-center justify-between p-4 border rounded-lg bg-gray-50/50">
                <div class="flex items-center gap-4">
                  <div :class="[
                      'h-10 w-10 rounded-full flex items-center justify-center', 
                      order.status === 'Delivered' ? 'bg-emerald-100' : 'bg-blue-100'
                    ]"
                  >
                    <Check v-if="order.status === 'Delivered'" class="h-5 w-5 text-emerald-600" />
                    <Truck v-else class="h-5 w-5 text-blue-600" />
                  </div>
                  <div>
                    <div class="flex items-center gap-2">
                      <p class="font-medium">{{ order.display_id }}</p>
                      <Badge v-if="order.type === 'return'" variant="destructive" class="text-[10px] h-4 py-0">Replacement</Badge>
                    </div>
                    <p class="text-sm text-muted-foreground">To: {{ order.customer }}</p>
                    <p class="text-xs text-muted-foreground">Items: {{ order.items }}</p>
                  </div>
                </div>
                <div class="text-right flex flex-col items-end">
                    <Badge 
                      variant="secondary" 
                      class="mb-1"
                      :class="order.status === 'Delivered' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-100' : 'bg-blue-100 text-blue-800 hover:bg-blue-100'"
                    >
                      {{ order.status }}
                    </Badge>
                    <p class="text-xs text-muted-foreground whitespace-nowrap">{{ order.shipped_at }}</p>
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
import { ref, onMounted } from 'vue'
import axios from '@/utils/axios' 
import { toast } from 'vue-sonner' 
import { 
  Package, 
  Truck, 
  UploadCloud, 
  X, 
  Check, 
  PackageOpen,
  AlertTriangle 
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
const loading = ref(true)
const isSubmitting = ref(false)

// Helper to get name for the confirmation dialog
const getPersonnelName = (id) => {
  const person = deliveryPersonnelList.value.find(p => p.id === id)
  return person ? person.name : 'Unknown Personnel'
}

// Fetch Data from Backend
const fetchShipments = async () => {
  loading.value = true
  try {
    const response = await axios.get('/supplier/shipments')
    preparedOrders.value = response.data.prepared_orders.map(order => ({
        ...order,
        previewImage: null,
        fileObject: null,
        selectedPersonnelId: '' 
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

// Initial Fetch
onMounted(() => {
    fetchShipments()
})

// Handle File Selection locally
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

// Remove selected image locally
const removeImage = (uniqueId) => {
  const orderIndex = preparedOrders.value.findIndex(o => o.unique_id === uniqueId)
  if (orderIndex !== -1) {
    preparedOrders.value[orderIndex].previewImage = null
    preparedOrders.value[orderIndex].fileObject = null
    toast.info("Image removed.")
  }
}

// Send to Backend
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

  isSubmitting.value = true
  const toastId = toast.loading('Assigning personnel and dispatching shipment...')

  const formData = new FormData()
  formData.append('image', order.fileObject)
  formData.append('delivery_personnel_id', order.selectedPersonnelId) 
  formData.append('type', order.type)
  formData.append('_method', 'POST') 

  try {
    await axios.post(`/supplier/shipments/${order.id}/ship`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    
    await fetchShipments()
    
    toast.success(`${order.type === 'return' ? 'Replacement' : 'Order'} ${order.display_id} is now In Transit!`, {
      id: toastId
    })

  } catch (error) {
    console.error("Error submitting shipment:", error)
    toast.error("Failed to update shipment status.", {
      id: toastId
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>