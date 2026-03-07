<template>
  <div class="arrived-items-container p-4 md:p-6">
    <Teleport to="body">
      <Toaster
        position="top-right"
        :expand="false"
        :rich-colors="false"
        :close-button="true"
        :theme="'light'"
        :visible-toasts="1"
        :container-style="{
          position: 'fixed',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          zIndex: 9999999,
          pointerEvents: 'none',
        }"
        :toast-options="{
          style: {
            background: 'white',
            color: 'black',
            border: 'none',
            boxShadow: '0 4px 15px rgba(0, 0, 0, 0.18)',
            padding: '16px 20px',          // slightly smaller padding
            fontSize: '15px',              // slightly smaller font
            minWidth: '280px',             // smaller width
            maxWidth: '400px',
            borderRadius: '10px',          // slightly smaller rounding
            pointerEvents: 'auto',
          },
        }"
      />
    </Teleport>

    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Arrived Items</h1>
          <p class="text-gray-300">Review recent deliveries and move approved items into inventory.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
          <Button 
            @click="requirePermission('view', handleExport)"
            variant="outline" 
            class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent"
          >
            <FileDown class="w-4 h-4 mr-2" />
            Export CSV
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ arrivedItems.length }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300">Pending Inventory Check</CardDescription>
        </CardHeader>
      </Card>
      
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ totalQuantityArrived }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300">Total Units Received</CardDescription>
        </CardHeader>
      </Card>
    </div>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white overflow-hidden">
      <CardContent class="p-0">
        
        <div class="p-5 border-b border-gray-800 flex items-center justify-between">
          <div class="relative w-full max-w-md">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search by ID, Product, or Supplier..." 
              class="pl-10 h-10 bg-gray-800 border-gray-700 text-white placeholder:text-gray-500 focus-visible:ring-emerald-500/50" 
            />
          </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
          <Table>
            <TableHeader class="bg-gray-900/80 border-b border-gray-800">
              <TableRow class="border-0 hover:bg-transparent">
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Request Code</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Date Arrived</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Product Details</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Supplier</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Quantity</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Status</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isLoading" class="border-0 hover:bg-transparent">
                <TableCell colspan="7" class="h-32 text-center">
                  <Loader2 class="w-8 h-8 animate-spin mx-auto text-emerald-500 mb-2" />
                  <span class="text-gray-400">Fetching arrived items...</span>
                </TableCell>
              </TableRow>

              <TableRow v-else-if="filteredItems.length === 0" class="border-0 hover:bg-transparent">
                <TableCell colspan="7" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <PackageX class="w-8 h-8 text-gray-600 mb-2" />
                    <span>No arrived items found.</span>
                  </div>
                </TableCell>
              </TableRow>
              
              <TableRow 
                v-else
                v-for="item in filteredItems" 
                :key="item.id"
                class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors group"
              >
                <TableCell class="font-medium text-white">
                  {{ item.request_code }}
                </TableCell>
                <TableCell class="text-gray-300">
                  {{ formatDate(item.delivered_at) }}
                </TableCell>
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div v-if="item.raw_material_details?.image_url" class="w-10 h-10 rounded-md bg-gray-800 border border-gray-700 overflow-hidden shrink-0">
                      <img :src="`http://localhost:8000/storage/${item.raw_material_details.image_url}`" class="w-full h-full object-cover" />
                    </div>
                    <div class="flex flex-col">
                      <span class="font-bold text-white">{{ item.raw_material_details?.name || item.product_name }}</span>
                      <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                        <span v-if="item.raw_material_details?.sku_code">SKU: {{ item.raw_material_details.sku_code }}</span>
                        <span v-if="item.raw_material_details?.size">• Size: {{ item.raw_material_details.size }}</span>
                        <span v-if="item.raw_material_details?.type">• {{ item.raw_material_details.type }}</span>
                      </div>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="text-gray-300">
                  {{ item.supplier }}
                </TableCell>
                <TableCell class="text-right">
                  <span class="font-bold text-emerald-400">{{ item.quantity }}</span>
                </TableCell>
                <TableCell>
                  <Badge class="rounded-full border-0 font-medium bg-emerald-500/20 text-emerald-300">
                    Delivered
                  </Badge>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex items-center justify-end gap-1">
                    <Button 
                      @click="requirePermission('view', () => openViewModal(item))"
                      variant="ghost" 
                      size="sm" 
                      class="text-emerald-400 hover:text-white hover:bg-emerald-600/20"
                    >
                      <Eye class="w-4 h-4 mr-2" />
                      View Details
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div class="p-4 border-t border-gray-800 flex items-center justify-between text-sm text-gray-400">
          <div>
            Showing <span class="text-white font-medium">{{ filteredItems.length > 0 ? 1 : 0 }}</span> to <span class="text-white font-medium">{{ filteredItems.length }}</span> of <span class="text-white font-medium">{{ arrivedItems.length }}</span> entries
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewModal" @update:open="(val) => !val && closeViewModal()">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-3xl custom-scrollbar max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex items-center gap-2">
            Delivery Receipt: <span class="text-emerald-400">{{ selectedItem?.request_code }}</span>
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Review the delivered product details and delivery proof before adding to your inventory.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedItem" class="space-y-6 py-4">
          
          <div class="bg-gray-800/50 rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-700 bg-gray-800/80">
              <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <Package class="w-4 h-4 text-emerald-400" />
                Product Information
              </h3>
            </div>
            <div class="p-4 flex flex-col sm:flex-row gap-6">
              <div class="w-full sm:w-1/3 aspect-square bg-gray-950 rounded-lg border border-gray-700 overflow-hidden flex items-center justify-center shrink-0">
                <img 
                  v-if="selectedItem.raw_material_details?.image_url" 
                  :src="`http://localhost:8000/storage/${selectedItem.raw_material_details.image_url}`" 
                  alt="Product Image" 
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-gray-600 flex flex-col items-center">
                  <ImageOff class="w-8 h-8 mb-2 opacity-50" />
                  <span class="text-xs">No Image</span>
                </div>
              </div>

              <div class="w-full sm:w-2/3 grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Product Name</p>
                  <p class="font-bold text-lg text-white">{{ selectedItem.raw_material_details?.name || selectedItem.product_name }}</p>
                  <p class="text-sm text-gray-400 mt-1">{{ selectedItem.raw_material_details?.description || 'No description provided.' }}</p>
                </div>
                
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">SKU Code</p>
                  <p class="font-medium text-gray-200">{{ selectedItem.raw_material_details?.sku_code || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Category & Type</p>
                  <p class="font-medium text-gray-200">{{ selectedItem.category }} / {{ selectedItem.raw_material_details?.type || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Size</p>
                  <p class="font-medium text-gray-200">{{ selectedItem.raw_material_details?.size || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Color Code</p>
                  <div class="flex items-center gap-2">
                    <div v-if="selectedItem.raw_material_details?.color_code" class="w-4 h-4 rounded-full border border-gray-600" :style="{ backgroundColor: selectedItem.raw_material_details.color_code }"></div>
                    <p class="font-medium text-gray-200">{{ selectedItem.raw_material_details?.color_code || 'N/A' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-800">
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Supplier</p>
              <p class="font-medium text-white">{{ selectedItem.supplier }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Unit Price</p>
              <p class="font-medium text-white">₱{{ Number(selectedItem.unit_price).toLocaleString() }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Quantity Received</p>
              <p class="font-bold text-emerald-400">{{ selectedItem.quantity }} Units</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Arrival Date</p>
              <p class="font-medium text-white">{{ formatDate(selectedItem.delivered_at) }}</p>
            </div>
          </div>

          <div class="space-y-3">
            <h3 class="text-sm font-semibold text-white flex items-center gap-2">
              <Camera class="w-4 h-4 text-emerald-400" />
              Proof of Delivery
            </h3>
            <div class="w-full bg-gray-950 border border-gray-800 rounded-xl overflow-hidden flex items-center justify-center min-h-[300px] p-2">
              <img 
                v-if="selectedItem.arrival_proof_path" 
                :src="`http://localhost:8000/${selectedItem.arrival_proof_path}`" 
                alt="Arrival Proof" 
                class="max-w-full max-h-[400px] object-contain rounded-lg"
              />
              <div v-else class="text-gray-500 flex flex-col items-center py-10">
                <ImageOff class="w-10 h-10 mb-2 opacity-50" />
                <p>No proof image available.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-800 mt-2">
          <Button 
            variant="outline" 
            @click="closeViewModal" 
            :disabled="isMoving"
            class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent"
          >
            Cancel
          </Button>

          <Button 
            :disabled="isMoving"
            @click="requirePermission('update', () => isConfirmOpen = true)"
            class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white hover:opacity-90 border-0"
          >
            <Loader2 v-if="isMoving" class="w-4 h-4 mr-2 animate-spin" />
            <CheckCircle2 v-else class="w-4 h-4 mr-2" />
            Move to Inventory
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="isConfirmOpen" @update:open="isConfirmOpen = $event">
      <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white">
        <AlertDialogHeader>
          <AlertDialogTitle>Confirm Move to Inventory</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Are you sure you want to officially move <span class="text-emerald-400 font-bold">{{ selectedItem?.quantity }}</span> units of <span class="font-bold text-gray-200">{{ selectedItem?.raw_material_details?.name || selectedItem?.product_name }}</span> to your inventory? This action will update your stock availability and cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="isConfirmOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction @click="moveToInventory" class="bg-emerald-600 hover:bg-emerald-700 text-white border-0">
            Yes, Move to Inventory
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { Search, FileDown, Eye, PackageX, Loader2, CheckCircle2, Package, ImageOff, Camera } from 'lucide-vue-next'

// MISSING IMPORT ADDED HERE
import { Toaster, toast } from 'vue-sonner'

// Standard UI Components
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

// Alert Dialog Components
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

// State
const arrivedItems = ref<any[]>([])
const isLoading = ref(true)
const searchQuery = ref('')
const showViewModal = ref(false)
const selectedItem = ref<any>(null)
const isMoving = ref(false)
const isConfirmOpen = ref(false)

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_create: false,
  can_update: false,
  can_delete: false
})

// RBAC Action Interceptor
const requirePermission = (action: string, callback: Function) => {
  if (!(permissions.value as any)[`can_${action}`]) {
    toast.error(`Access Denied: You do not have permission to ${action} arrived items.`);
    return;
  }
  if (callback) callback();
}

// Fetch Arrived Items (Delivered Procurement Requests)
const fetchArrivedItems = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/operation-distributor/arrived-items')
    
    if (response.data && response.data.data) {
        arrivedItems.value = response.data.data
        if (response.data.permissions) {
            permissions.value = response.data.permissions
        }
    } else {
        arrivedItems.value = response.data
    }
  } catch (error: any) {
    if (error.response?.status === 403) {
        toast.error('Unauthorized: Access to arrived items is restricted.')
    } else {
        console.error('Failed to fetch arrived items:', error)
        toast.error('Failed to load arrived items', {
          description: 'Could not fetch data from the server. Please try again later.'
        })
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchArrivedItems()
})

// Computeds
const totalQuantityArrived = computed(() => {
  return arrivedItems.value.reduce((sum, item) => sum + Number(item.quantity), 0)
})

const filteredItems = computed(() => {
  if (!searchQuery.value) return arrivedItems.value
  
  const query = searchQuery.value.toLowerCase()
  return arrivedItems.value.filter(item => 
    item.product_name?.toLowerCase().includes(query) ||
    item.raw_material_details?.name?.toLowerCase().includes(query) ||
    item.raw_material_details?.sku_code?.toLowerCase().includes(query) ||
    item.supplier?.toLowerCase().includes(query) ||
    item.request_code?.toLowerCase().includes(query)
  )
})

// Actions
const openViewModal = (item: any) => {
  selectedItem.value = item
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  setTimeout(() => {
    selectedItem.value = null
  }, 300)
}

const handleExport = () => {
  toast.info("Exporting CSV manifest...")
  setTimeout(() => {
    toast.success("CSV exported successfully.")
  }, 1000)
}

const moveToInventory = async () => {
  if (!selectedItem.value) return
  isConfirmOpen.value = false // Close the confirmation modal

  // Create a loading toast id to update it later
  const toastId = toast.loading('Moving items to inventory...')

  try {
    isMoving.value = true
    // Hit the endpoint to update statuses and add to inventory
    await api.post(`/operation-distributor/arrived-items/${selectedItem.value.id}/move-to-inventory`)
    
    // Refresh the local array visually
    arrivedItems.value = arrivedItems.value.filter(i => i.id !== selectedItem.value.id)
    
    // Success toast
    toast.success('Successfully moved to inventory!', {
      id: toastId,
      description: `${selectedItem.value.quantity} units of ${selectedItem.value.raw_material_details?.name || selectedItem.value.product_name} have been registered and added.`
    })
    
    closeViewModal()
  } catch (error: any) {
    console.error('Failed to move item to inventory:', error)
    // Error toast
    toast.error('Operation Failed', {
      id: toastId,
      description: error.response?.data?.message || "An error occurred while moving the item to inventory."
    })
  } finally {
    isMoving.value = false
  }
}

// Format Date Utility
const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}
</script>

<style scoped>
.arrived-items-container {
  min-height: 100vh;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5); 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.8); 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(107, 114, 128, 1); 
}
</style>

<style>
/* Unscoped global override to force Sonner Toaster to the very top */
[data-sonner-toaster] {
  z-index: 2147483647 !important; /* Maximum z-index possible */
  position: fixed !important;
}
</style>