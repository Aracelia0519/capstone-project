<template>
  <div class="inventory-container p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Inventory Management</h1>
          <p class="text-gray-300">Manage your available stock and deploy products to the E-commerce store.</p>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
          <Button 
            variant="outline" 
            class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent"
            @click="fetchInventory"
            :disabled="isLoading"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 mr-2 animate-spin" />
            <FileDown v-else class="w-4 h-4 mr-2" />
            Refresh Inventory
          </Button>
          <Button 
            class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white hover:opacity-90 border-0"
          >
            <Plus class="w-5 h-5 mr-2" />
            Add New Product
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ inventoryItems.length }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300">Total Unique Products</CardDescription>
        </CardHeader>
      </Card>
      
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ totalStock }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300">Total Units in Stock</CardDescription>
        </CardHeader>
      </Card>

      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ lowStockCount }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300">Low Stock Alerts</CardDescription>
        </CardHeader>
      </Card>

      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ deployedCount }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300">Deployed to E-commerce</CardDescription>
        </CardHeader>
      </Card>
    </div>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white overflow-hidden">
      <CardContent class="p-0">
        
        <div class="p-5 border-b border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="relative w-full max-w-md">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search by Product Name, SKU, or Category..." 
              class="pl-10 h-10 bg-gray-800 border-gray-700 text-white placeholder:text-gray-500 focus-visible:ring-emerald-500/50" 
            />
          </div>
          
          <div class="flex items-center gap-2">
            <Badge variant="outline" class="bg-gray-800 border-gray-700 text-gray-300 cursor-pointer hover:bg-gray-700">
              All Items
            </Badge>
            <Badge variant="outline" class="bg-transparent border-gray-700 text-gray-500 cursor-pointer hover:text-gray-300">
              Low Stock
            </Badge>
            <Badge variant="outline" class="bg-transparent border-gray-700 text-gray-500 cursor-pointer hover:text-gray-300">
              Not Deployed
            </Badge>
          </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
          <Table>
            <TableHeader class="bg-gray-900/80 border-b border-gray-800">
              <TableRow class="border-0 hover:bg-transparent">
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Product Info</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Category</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Stock Level</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Price</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-center">Deployment</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isLoading" class="border-0 hover:bg-transparent">
                <TableCell colspan="6" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <Loader2 class="w-8 h-8 animate-spin text-emerald-500 mb-2" />
                    <span>Loading inventory...</span>
                  </div>
                </TableCell>
              </TableRow>

              <TableRow v-else-if="filteredItems.length === 0" class="border-0 hover:bg-transparent">
                <TableCell colspan="6" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <PackageX class="w-8 h-8 text-gray-600 mb-2" />
                    <span>No products found in inventory.</span>
                  </div>
                </TableCell>
              </TableRow>
              
              <TableRow 
                v-else
                v-for="item in filteredItems" 
                :key="item.id"
                class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors group"
              >
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md bg-gray-800 border border-gray-700 overflow-hidden shrink-0 flex items-center justify-center">
                      <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover" />
                      <Package v-else class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="flex flex-col">
                      <span class="font-bold text-white">{{ item.name }}</span>
                      <span class="text-xs text-gray-400 mt-0.5">SKU: {{ item.sku_code }}</span>
                    </div>
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="flex flex-col">
                    <span class="text-gray-200">{{ item.category }}</span>
                    <span class="text-xs text-gray-500">{{ item.type }}</span>
                  </div>
                </TableCell>
                
                <TableCell class="text-right">
                  <div class="flex flex-col items-end">
                    <span :class="[
                      'font-bold text-lg',
                      item.quantity <= item.min_stock_level ? 'text-amber-400' : 'text-emerald-400'
                    ]">
                      {{ item.quantity }}
                    </span>
                    <span class="text-[10px] text-gray-500 uppercase tracking-wider">
                      Min: {{ item.min_stock_level }}
                    </span>
                  </div>
                </TableCell>
                
                <TableCell class="text-right font-medium text-gray-200">
                  ₱{{ Number(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </TableCell>
                
                <TableCell class="text-center">
                  <Badge :class="[
                    'rounded-full border-0 font-medium',
                    item.ecommerce_status === 'deployed' ? 'bg-indigo-500/20 text-indigo-300' : 
                    item.ecommerce_status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 
                    'bg-gray-700 text-gray-300'
                  ]">
                    {{ 
                      item.ecommerce_status === 'deployed' ? 'Live on Store' : 
                      item.ecommerce_status === 'pending' ? 'Pending Approval' : 
                      'Not Deployed' 
                    }}
                  </Badge>
                </TableCell>
                
                <TableCell class="text-right">
                  <Button 
                    @click="openViewModal(item)"
                    variant="ghost" 
                    size="sm" 
                    class="text-blue-400 hover:text-white hover:bg-blue-600/20"
                  >
                    <Eye class="w-4 h-4 mr-2" />
                    View Details
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div class="p-4 border-t border-gray-800 flex items-center justify-between text-sm text-gray-400">
          <div>
            Showing <span class="text-white font-medium">{{ filteredItems.length > 0 ? 1 : 0 }}</span> to <span class="text-white font-medium">{{ filteredItems.length }}</span> of <span class="text-white font-medium">{{ inventoryItems.length }}</span> items
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewModal" @update:open="(val) => !val && closeViewModal()">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-3xl custom-scrollbar max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex items-center gap-2">
            Inventory Item Details
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Review product specifications, current stock levels, and deployment status.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedItem" class="space-y-6 py-4">
          
          <div class="bg-gray-800/50 rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-4 flex flex-col sm:flex-row gap-6">
              <div class="w-full sm:w-1/3 aspect-square bg-gray-950 rounded-lg border border-gray-700 overflow-hidden flex items-center justify-center shrink-0">
                <img 
                  v-if="selectedItem.image_url" 
                  :src="selectedItem.image_url" 
                  alt="Product Image" 
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-gray-600 flex flex-col items-center">
                  <ImageOff class="w-8 h-8 mb-2 opacity-50" />
                  <span class="text-xs">No Image Available</span>
                </div>
              </div>

              <div class="w-full sm:w-2/3 grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Product Name</p>
                      <p class="font-bold text-2xl text-white leading-tight">{{ selectedItem.name }}</p>
                    </div>
                    <Badge :class="[
                      'rounded-full border-0 font-medium ml-2 shrink-0',
                      selectedItem.ecommerce_status === 'deployed' ? 'bg-indigo-500/20 text-indigo-300' : 
                      selectedItem.ecommerce_status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 
                      'bg-gray-700 text-gray-300'
                    ]">
                      {{ 
                        selectedItem.ecommerce_status === 'deployed' ? 'Deployed Online' : 
                        selectedItem.ecommerce_status === 'pending' ? 'Pending Approval' : 
                        'Not Deployed' 
                      }}
                    </Badge>
                  </div>
                  <p class="text-sm text-gray-400 mt-2">{{ selectedItem.description || 'No detailed description available for this product.' }}</p>
                </div>
                
                <div class="mt-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">SKU Code</p>
                  <p class="font-mono text-emerald-400">{{ selectedItem.sku_code }}</p>
                </div>
                <div class="mt-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Retail Price</p>
                  <p class="font-bold text-white text-lg">₱{{ Number(selectedItem.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
                </div>

                <div class="col-span-2 border-t border-gray-700 my-2"></div>

                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Category & Type</p>
                  <p class="font-medium text-gray-200">{{ selectedItem.category }} / {{ selectedItem.type }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Unit Size</p>
                  <p class="font-medium text-gray-200">{{ selectedItem.size }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Color Code</p>
                  <div class="flex items-center gap-2 mt-1">
                    <div 
                      v-if="selectedItem.color_code" 
                      class="w-5 h-5 rounded-md border border-gray-600 shadow-inner" 
                      :style="{ backgroundColor: selectedItem.color_code }"
                    ></div>
                    <p class="font-medium text-gray-200">{{ selectedItem.color_code || 'N/A' }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-800">
            <div class="text-center border-r border-gray-700">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Current Stock</p>
              <p :class="[
                'font-bold text-3xl',
                selectedItem.quantity <= selectedItem.min_stock_level ? 'text-amber-400' : 'text-emerald-400'
              ]">{{ selectedItem.quantity }}</p>
            </div>
            <div class="text-center border-r border-gray-700">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Min. Threshold</p>
              <p class="font-bold text-2xl text-gray-300">{{ selectedItem.min_stock_level }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Max Capacity</p>
              <p class="font-bold text-2xl text-gray-300">{{ selectedItem.max_stock_level }}</p>
            </div>
          </div>
          
          <div v-if="selectedItem.quantity <= selectedItem.min_stock_level" class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-start gap-3 text-amber-300 text-sm">
            <AlertTriangle class="w-5 h-5 shrink-0" />
            <p><strong>Low Stock Alert:</strong> This item is at or below its minimum stock threshold. Consider replenishing inventory before deploying to the e-commerce store.</p>
          </div>

        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-800 mt-2">
          <Button 
            variant="outline" 
            @click="closeViewModal" 
            class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent"
          >
            Close
          </Button>

          <AlertDialog v-if="selectedItem && selectedItem.ecommerce_status === 'not_deployed'">
            <AlertDialogTrigger as-child>
              <Button 
                :disabled="isProcessing"
                class="bg-gradient-to-r from-indigo-500 to-blue-600 text-white hover:opacity-90 border-0 shadow-lg shadow-indigo-500/20"
              >
                <Loader2 v-if="isProcessing" class="w-4 h-4 mr-2 animate-spin" />
                <Store class="w-4 h-4 mr-2" v-else />
                Request for Deployment
              </Button>
            </AlertDialogTrigger>
            <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white">
              <AlertDialogHeader>
                <AlertDialogTitle>Deploy to E-Commerce?</AlertDialogTitle>
                <AlertDialogDescription class="text-gray-400">
                  Are you sure you want to request making <span class="text-white font-bold">{{ selectedItem?.name }}</span> visible on the e-commerce store? The business owner will need to approve this.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <AlertDialogFooter>
                <AlertDialogCancel class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
                  Cancel
                </AlertDialogCancel>
                <AlertDialogAction @click="requestDeployment" class="bg-indigo-600 hover:bg-indigo-700 text-white border-0">
                  Yes, Request Deployment
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
          
          <Button 
            v-else-if="selectedItem && selectedItem.ecommerce_status === 'pending'"
            disabled
            class="bg-amber-600/50 text-white border-0"
          >
            <Loader2 class="w-4 h-4 mr-2 animate-spin" />
            Pending Approval
          </Button>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios' 
import { toast } from 'vue-sonner'
import { 
  Search, FileDown, Eye, PackageX, Loader2, Package, ImageOff, Plus, Store, AlertTriangle 
} from 'lucide-vue-next'

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
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

// State
const searchQuery = ref('')
const showViewModal = ref(false)
const selectedItem = ref<any>(null)
const isProcessing = ref(false)
const isLoading = ref(true)
const inventoryItems = ref<any[]>([])

// Fetch API Data
const fetchInventory = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/operation-distributor/ec-inventory');

    if (response.data.success) {
      inventoryItems.value = response.data.data
    } else {
      toast.error('Failed to load inventory data.')
    }
  } catch (error) {
    console.error('API Error:', error)
    toast.error('An error occurred while fetching inventory.')
  } finally {
    isLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchInventory()
})

// Computeds for Summary Cards
const totalStock = computed(() => {
  return inventoryItems.value.reduce((sum, item) => sum + item.quantity, 0)
})

const lowStockCount = computed(() => {
  return inventoryItems.value.filter(item => item.quantity <= item.min_stock_level).length
})

const deployedCount = computed(() => {
  return inventoryItems.value.filter(item => item.ecommerce_status === 'deployed').length
})

const filteredItems = computed(() => {
  if (!searchQuery.value) return inventoryItems.value
  
  const query = searchQuery.value.toLowerCase()
  return inventoryItems.value.filter(item => 
    item.name.toLowerCase().includes(query) ||
    item.sku_code.toLowerCase().includes(query) ||
    item.category.toLowerCase().includes(query)
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

const requestDeployment = async () => {
  if (!selectedItem.value) return

  isProcessing.value = true
  const toastId = toast.loading('Sending deployment request...')

  try {
    const response = await api.post(`/operation-distributor/ec-inventory/${selectedItem.value.id}/request-deployment`)

    if (response.data.success) {
      const index = inventoryItems.value.findIndex(i => i.id === selectedItem.value.id)
      if (index !== -1) {
        inventoryItems.value[index].ecommerce_status = 'pending'
        selectedItem.value.ecommerce_status = 'pending'
      }

      toast.success('Request Submitted!', {
        id: toastId,
        description: `Deployment request for ${selectedItem.value.name} is now pending approval.`
      })
      
      setTimeout(() => {
        closeViewModal()
      }, 1000)
    } else {
      toast.error('Request failed', { id: toastId, description: response.data.message })
    }
  } catch (error) {
    console.error('Deployment Error:', error)
    toast.error('Error', { id: toastId, description: 'Could not submit deployment request.' })
  } finally {
    isProcessing.value = false
  }
}
</script>

<style scoped>
.inventory-container {
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