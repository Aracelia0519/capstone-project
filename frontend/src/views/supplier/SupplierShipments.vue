<template>
  <div class="p-6 space-y-6">
    <div>
      <h1 class="text-3xl font-bold tracking-tight">Supplier Shipments</h1>
      <p class="text-muted-foreground">Manage prepared orders and update delivery status.</p>
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
          <CardTitle class="text-sm font-medium">Shipped History</CardTitle>
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
        <TabsTrigger value="shipped">Shipped History</TabsTrigger>
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
          <Card v-for="order in preparedOrders" :key="order.id" class="flex flex-col">
            <CardHeader>
              <div class="flex justify-between items-start">
                <div>
                  <CardTitle>{{ order.display_id }}</CardTitle>
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

                <div class="mt-4 pt-4 border-t">
                  <Label class="mb-2 block">Proof of Readiness (Image)</Label>
                  
                  <div v-if="!order.previewImage" class="flex items-center justify-center w-full">
                    <label :for="'file-upload-' + order.id" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                      <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <UploadCloud class="w-8 h-8 mb-2 text-gray-400" />
                        <p class="text-xs text-gray-500">Click to upload image</p>
                      </div>
                      <input 
                        :id="'file-upload-' + order.id" 
                        type="file" 
                        class="hidden" 
                        accept="image/*"
                        @change="(e) => handleImageSelect(e, order.id)"
                      />
                    </label>
                  </div>

                  <div v-else class="relative w-full h-40 rounded-md overflow-hidden border">
                    <img :src="order.previewImage" alt="Proof" class="w-full h-full object-cover" />
                    <Button 
                      variant="destructive" 
                      size="icon" 
                      class="absolute top-2 right-2 h-6 w-6 shadow-sm"
                      @click="removeImage(order.id)"
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
                    :disabled="!order.fileObject || isSubmitting" 
                  >
                    <Truck class="mr-2 h-4 w-4" />
                    {{ isSubmitting ? 'Processing...' : (order.fileObject ? 'Start Delivery' : 'Upload Proof to Ship') }}
                  </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Confirm Shipment</AlertDialogTitle>
                    <AlertDialogDescription>
                      Are you sure you want to mark order <strong>{{ order.display_id }}</strong> as shipped? 
                      <br><br>
                      This will notify the distributor and cannot be undone.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction @click="submitShipment(order.id)">Continue to Ship</AlertDialogAction>
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
            <CardTitle>Shipped Orders</CardTitle>
            <CardDescription>Recent shipments managed by you.</CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="shippedOrders.length === 0" class="text-center py-4 text-muted-foreground">
                No shipped orders yet.
            </div>
            <div v-else class="space-y-4">
              <div v-for="order in shippedOrders" :key="order.id" class="flex items-center justify-between p-4 border rounded-lg bg-gray-50/50">
                <div class="flex items-center gap-4">
                  <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                    <Check class="h-5 w-5 text-green-600" />
                  </div>
                  <div>
                    <p class="font-medium">{{ order.display_id }}</p>
                    <p class="text-sm text-muted-foreground">To: {{ order.customer }}</p>
                    <p class="text-xs text-muted-foreground">Items: {{ order.items }}</p>
                  </div>
                </div>
                <div class="text-right">
                    <Badge variant="secondary" class="bg-green-100 text-green-800 hover:bg-green-100 mb-1">
                    {{ order.status }}
                    </Badge>
                    <p class="text-xs text-muted-foreground">{{ order.shipped_at }}</p>
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
import { toast } from 'vue-sonner' // Sonner Toast
import { 
  Package, 
  Truck, 
  UploadCloud, 
  X, 
  Check, 
  PackageOpen 
} from 'lucide-vue-next'

// Import Shadcn UI components 
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Label } from '@/components/ui/label'
// Import Alert Dialog Components
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
const loading = ref(true)
const isSubmitting = ref(false)

// Fetch Data from Backend
const fetchShipments = async () => {
  loading.value = true
  try {
    const response = await axios.get('/supplier/shipments')
    preparedOrders.value = response.data.prepared_orders.map(order => ({
        ...order,
        previewImage: null,
        fileObject: null
    }))
    shippedOrders.value = response.data.shipped_orders
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
const handleImageSelect = (event, orderId) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate File Type
  if (!file.type.match('image.*')) {
    toast.error("Please upload an image file (PNG, JPG).")
    return
  }
  
  // Validate File Size (e.g., 5MB)
  if (file.size > 5 * 1024 * 1024) {
    toast.error("File size too large. Max 5MB allowed.")
    return
  }

  // Create preview URL
  const imageUrl = URL.createObjectURL(file)
  
  const orderIndex = preparedOrders.value.findIndex(o => o.id === orderId)
  if (orderIndex !== -1) {
    preparedOrders.value[orderIndex].previewImage = imageUrl
    preparedOrders.value[orderIndex].fileObject = file
    toast.success("Proof image attached successfully!")
  }
}

// Remove selected image locally
const removeImage = (orderId) => {
  const orderIndex = preparedOrders.value.findIndex(o => o.id === orderId)
  if (orderIndex !== -1) {
    preparedOrders.value[orderIndex].previewImage = null
    preparedOrders.value[orderIndex].fileObject = null
    toast.info("Image removed.")
  }
}

// Send to Backend
const submitShipment = async (orderId) => {
  const order = preparedOrders.value.find(o => o.id === orderId)
  if (!order || !order.fileObject) {
    toast.error("Please upload a proof image before shipping.")
    return
  }

  isSubmitting.value = true
  // Show loading toast and get its ID to update later
  const toastId = toast.loading('Processing shipment...')

  const formData = new FormData()
  formData.append('image', order.fileObject)
  formData.append('_method', 'POST') 

  try {
    await axios.post(`/supplier/shipments/${orderId}/ship`, formData, {
        headers: {
            'Content-Type': 'multipart/form-data'
        }
    })
    
    // Refresh list and Show Success
    await fetchShipments()
    
    // Update loading toast to success
    toast.success(`Order ${order.display_id} marked as shipped!`, {
      id: toastId
    })

  } catch (error) {
    console.error("Error submitting shipment:", error)
    // Update loading toast to error
    toast.error("Failed to update shipment status.", {
      id: toastId
    })
  } finally {
    isSubmitting.value = false
  }
}
</script>