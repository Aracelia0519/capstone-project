<template>
  <div class="inventory-container p-4 md:p-6">
    <Teleport to="body">
      <Toaster position="top-right" theme="dark" class="!z-[999999]" style="z-index: 999999;" />
    </Teleport>

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
            @click="refreshData"
            :disabled="isLoading"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 mr-2 animate-spin" />
            <FileDown v-else class="w-4 h-4 mr-2" />
            Refresh Data
          </Button>
          <Button 
            @click="requirePermission('create', handleAddProduct)"
            class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white hover:opacity-90 border-0"
          >
            <Plus class="w-5 h-5 mr-2" />
            Add New Product
          </Button>
        </div>
      </div>
    </div>

    <div class="flex space-x-2 mb-6 bg-gray-900/50 p-1.5 rounded-lg border border-gray-800 w-fit">
      <button 
        @click="activeTab = 'active'" 
        :class="['px-5 py-2 text-sm font-medium rounded-md transition-all', activeTab === 'active' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800']"
      >
        Active Inventory ({{ inventoryItems.length }})
      </button>
      <button 
        @click="activeTab = 'inactive'" 
        :class="['px-5 py-2 text-sm font-medium rounded-md transition-all', activeTab === 'inactive' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-400 hover:text-white hover:bg-gray-800']"
      >
        Inactive Products ({{ inactiveItems.length }})
      </button>
    </div>

    <div v-if="activeTab === 'active'" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
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
          
          <div v-if="activeTab === 'active'" class="flex items-center gap-2">
            <Badge variant="outline" class="bg-gray-800 border-gray-700 text-gray-300 cursor-pointer hover:bg-gray-700">All Items</Badge>
            <Badge variant="outline" class="bg-transparent border-gray-700 text-gray-500 cursor-pointer hover:text-gray-300">Low Stock</Badge>
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
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-center">Status</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isLoading" class="border-0 hover:bg-transparent">
                <TableCell colspan="6" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <Loader2 class="w-8 h-8 animate-spin text-emerald-500 mb-2" />
                    <span>Loading data...</span>
                  </div>
                </TableCell>
              </TableRow>

              <TableRow v-else-if="currentFilteredItems.length === 0" class="border-0 hover:bg-transparent">
                <TableCell colspan="6" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <PackageX class="w-8 h-8 text-gray-600 mb-2" />
                    <span>No products found in {{ activeTab === 'active' ? 'inventory' : 'inactive list' }}.</span>
                  </div>
                </TableCell>
              </TableRow>
              
              <TableRow 
                v-else
                v-for="item in currentFilteredItems" 
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
                    <span class="text-xs text-white-500">{{ item.type }}</span>
                  </div>
                </TableCell>
                
                <TableCell class="text-right">
                  <div class="flex flex-col items-end">
                    <span :class="[
                      'font-bold text-lg',
                      (activeTab === 'active' && item.quantity <= item.min_stock_level) ? 'text-amber-400' : 'text-emerald-400'
                    ]">
                      {{ item.quantity }}
                    </span>
                    <span v-if="activeTab === 'active'" class="text-[10px] text-white-500 uppercase tracking-wider">
                      Min: {{ item.min_stock_level }}
                    </span>
                  </div>
                </TableCell>
                
                <TableCell class="text-right font-medium text-gray-200">
                  ₱{{ Number(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </TableCell>
                
                <TableCell class="text-center">
                  <Badge v-if="item.ecommerce_status === 'inactive'" class="rounded-full border-0 font-medium bg-red-500/20 text-red-300">
                    Inactive
                  </Badge>
                  <Badge v-else :class="[
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
                    @click="requirePermission('view', () => openViewModal(item))"
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
            Showing <span class="text-white font-medium">{{ currentFilteredItems.length > 0 ? 1 : 0 }}</span> to <span class="text-white font-medium">{{ currentFilteredItems.length }}</span> of <span class="text-white font-medium">{{ activeTab === 'active' ? inventoryItems.length : inactiveItems.length }}</span> items
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewModal" @update:open="(val) => !val && closeViewModal()">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-3xl custom-scrollbar max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex items-center gap-2">
            {{ activeTab === 'active' ? 'Active Inventory Item' : 'Inactive Product Details' }}
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Review product specifications, current stock levels, and store visibility status.
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
                    <Badge v-if="selectedItem.ecommerce_status === 'inactive'" class="rounded-full border-0 font-medium ml-2 shrink-0 bg-red-500/20 text-red-300">
                      Inactive
                    </Badge>
                    <Badge v-else :class="[
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
              </div>
            </div>
          </div>

          <div :class="[
              'grid gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-800',
              activeTab === 'active' ? 'grid-cols-3' : 'grid-cols-1'
            ]">
            <div :class="['text-center', activeTab === 'active' ? 'border-r border-gray-700' : '']">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Current Stock</p>
              <p :class="[
                'font-bold text-3xl',
                (activeTab === 'active' && selectedItem.quantity <= selectedItem.min_stock_level) ? 'text-amber-400' : 'text-emerald-400'
              ]">{{ selectedItem.quantity }}</p>
            </div>
            <div v-if="activeTab === 'active'" class="text-center border-r border-gray-700">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Min. Threshold</p>
              <p class="font-bold text-2xl text-gray-300">{{ selectedItem.min_stock_level }}</p>
            </div>
            <div v-if="activeTab === 'active'" class="text-center">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Max Capacity</p>
              <p class="font-bold text-2xl text-gray-300">{{ selectedItem.max_stock_level }}</p>
            </div>
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

          <Button 
            v-if="activeTab === 'inactive' && selectedItem"
            :disabled="isProcessing"
            @click="requirePermission('update', () => { actionQuantity = selectedItem.quantity; isReactivateConfirmOpen = true; })"
            class="bg-emerald-600 hover:bg-emerald-700 text-white border-0 shadow-lg"
          >
            <Loader2 v-if="isProcessing" class="w-4 h-4 mr-2 animate-spin" />
            <Package class="w-4 h-4 mr-2" v-else />
            Restore to Active
          </Button>

          <template v-else-if="selectedItem">
            <Button 
              :disabled="isProcessing"
              @click="requirePermission('update', () => { actionQuantity = selectedItem.quantity; isDeactivateConfirmOpen = true; })"
              class="bg-red-600/80 hover:bg-red-600 text-white border-0 mr-auto"
            >
              <PackageX class="w-4 h-4 mr-2" />
              Mark Inactive
            </Button>

            <Button 
              v-if="selectedItem.ecommerce_status === 'not_deployed'"
              :disabled="isProcessing"
              @click="requirePermission('update', () => isDeployConfirmOpen = true)"
              class="bg-gradient-to-r from-indigo-500 to-blue-600 text-white hover:opacity-90 border-0 shadow-lg shadow-indigo-500/20"
            >
              <Loader2 v-if="isProcessing" class="w-4 h-4 mr-2 animate-spin" />
              <Store class="w-4 h-4 mr-2" v-else />
              Request for Deployment
            </Button>
            
            <Button 
              v-else-if="selectedItem.ecommerce_status === 'pending'"
              disabled
              class="bg-amber-600/50 text-white border-0"
            >
              <Loader2 class="w-4 h-4 mr-2 animate-spin" />
              Pending Approval
            </Button>
          </template>

        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="isDeployConfirmOpen" @update:open="isDeployConfirmOpen = $event">
      <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white z-50">
        <AlertDialogHeader>
          <AlertDialogTitle>Deploy to E-Commerce?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Are you sure you want to request making <span class="text-white font-bold">{{ selectedItem?.name }}</span> visible on the e-commerce store? The business owner will need to approve this.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="isDeployConfirmOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="requestDeployment" class="bg-indigo-600 hover:bg-indigo-700 text-white border-0">Yes, Request Deployment</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog :open="isDeactivateConfirmOpen" @update:open="isDeactivateConfirmOpen = $event">
      <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white z-50">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-400">Mark Quantity as Inactive</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Select the number of units of <span class="text-white font-bold">{{ selectedItem?.name }}</span> you want to move to inactive storage.
          </AlertDialogDescription>
        </AlertDialogHeader>
        
        <div class="py-2 space-y-2">
          <label class="text-sm font-medium text-gray-300">Quantity (Max: {{ selectedItem?.quantity }})</label>
          <Input 
            type="number" 
            v-model="actionQuantity" 
            :max="selectedItem?.quantity" 
            min="1" 
            @keydown="['e', 'E', '+', '-', '.'].includes($event.key) && $event.preventDefault()"
            class="bg-gray-950 border-gray-700 text-white focus-visible:ring-red-500" 
          />
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel @click="isDeactivateConfirmOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="!isActionQuantityValid" @click="deactivateItem" class="bg-red-600 hover:bg-red-700 text-white border-0 disabled:opacity-50">Yes, Mark Inactive</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog :open="isReactivateConfirmOpen" @update:open="isReactivateConfirmOpen = $event">
      <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white z-50">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-emerald-400">Reactivate Product Quantity</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Select the number of units of <span class="text-white font-bold">{{ selectedItem?.name }}</span> you want to restore to the active inventory.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div class="py-2 space-y-2">
          <label class="text-sm font-medium text-gray-300">Quantity (Max: {{ selectedItem?.quantity }})</label>
          <Input 
            type="number" 
            v-model="actionQuantity" 
            :max="selectedItem?.quantity" 
            min="1" 
            @keydown="['e', 'E', '+', '-', '.'].includes($event.key) && $event.preventDefault()"
            class="bg-gray-950 border-gray-700 text-white focus-visible:ring-emerald-500" 
          />
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel @click="isReactivateConfirmOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="!isActionQuantityValid" @click="reactivateItem" class="bg-emerald-600 hover:bg-emerald-700 text-white border-0 disabled:opacity-50">Yes, Reactivate</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios' 
import { toast, Toaster } from 'vue-sonner'
import { 
  Search, FileDown, Eye, PackageX, Loader2, Package, ImageOff, Plus, Store, AlertTriangle 
} from 'lucide-vue-next'

import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

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
const activeTab = ref('active')
const searchQuery = ref('')
const showViewModal = ref(false)
const selectedItem = ref<any>(null)
const isProcessing = ref(false)
const isLoading = ref(true)

const inventoryItems = ref<any[]>([])
const inactiveItems = ref<any[]>([])

const actionQuantity = ref<number | ''>('')
const isDeployConfirmOpen = ref(false)
const isDeactivateConfirmOpen = ref(false)
const isReactivateConfirmOpen = ref(false)

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
    toast.error(`Access Denied: You do not have permission to ${action} inventory items.`);
    return;
  }
  if (callback) callback();
}

// Fetch API Data
const refreshData = async () => {
  isLoading.value = true
  await Promise.all([fetchInventory(), fetchInactiveInventory()])
  isLoading.value = false
}

const fetchInventory = async () => {
  try {
    const response = await api.get('/operation-distributor/ec-inventory')
    if (response.data.success) {
      inventoryItems.value = response.data.data
      if (response.data.permissions) permissions.value = response.data.permissions
    }
  } catch (error: any) {
    if (error.response?.status === 403) toast.error('Unauthorized access to inventory.')
  }
}

const fetchInactiveInventory = async () => {
  try {
    const response = await api.get('/operation-distributor/ec-inventory/inactive')
    if (response.data.success) {
      inactiveItems.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to load inactive inventory', error)
  }
}

// Lifecycle
onMounted(() => {
  refreshData()
})

// Computeds for Summary Cards
const totalStock = computed(() => inventoryItems.value.reduce((sum, item) => sum + item.quantity, 0))
const lowStockCount = computed(() => inventoryItems.value.filter(item => item.quantity <= item.min_stock_level).length)
const deployedCount = computed(() => inventoryItems.value.filter(item => item.ecommerce_status === 'deployed').length)

const currentFilteredItems = computed(() => {
  const list = activeTab.value === 'active' ? inventoryItems.value : inactiveItems.value
  if (!searchQuery.value) return list
  
  const query = searchQuery.value.toLowerCase()
  return list.filter(item => 
    item.name.toLowerCase().includes(query) ||
    item.sku_code.toLowerCase().includes(query) ||
    item.category.toLowerCase().includes(query)
  )
})

const isActionQuantityValid = computed(() => {
  if (!selectedItem.value || actionQuantity.value === '') return false;
  return actionQuantity.value > 0 && actionQuantity.value <= selectedItem.value.quantity;
})

// Actions
const handleAddProduct = () => toast.info("Add new product feature coming soon.")

const openViewModal = (item: any) => {
  selectedItem.value = item
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  setTimeout(() => {
    selectedItem.value = null;
    actionQuantity.value = '';
  }, 300)
}

const requestDeployment = async () => {
  if (!selectedItem.value) return
  isDeployConfirmOpen.value = false
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
      toast.success('Request Submitted!', { id: toastId, description: `Deployment requested for ${selectedItem.value.name}.` })
      setTimeout(() => closeViewModal(), 1000)
    } else {
      toast.error('Request failed', { id: toastId, description: response.data.message })
    }
  } catch (error: any) {
    if (error.response?.status === 403) toast.error('Unauthorized', { id: toastId, description: 'No permission to request deployment.' })
    else toast.error('Error', { id: toastId, description: 'Could not submit deployment request.' })
  } finally {
    isProcessing.value = false
  }
}

const deactivateItem = async () => {
  if (!selectedItem.value || !isActionQuantityValid.value) return
  isDeactivateConfirmOpen.value = false
  isProcessing.value = true
  const toastId = toast.loading('Moving quantity to inactive...')

  try {
    const response = await api.post(`/operation-distributor/ec-inventory/${selectedItem.value.id}/deactivate`, {
      quantity: actionQuantity.value
    })
    
    if (response.data.success) {
      toast.success('Product Quantity Deactivated', { id: toastId, description: `${actionQuantity.value} units moved to inactive list.` })
      await refreshData()
      closeViewModal()
    } else {
      toast.error('Operation failed', { id: toastId, description: response.data.message })
    }
  } catch (error) {
    toast.error('Failed to deactivate product quantity.', { id: toastId })
  } finally {
    isProcessing.value = false
  }
}

const reactivateItem = async () => {
  if (!selectedItem.value || !isActionQuantityValid.value) return
  isReactivateConfirmOpen.value = false
  isProcessing.value = true
  const toastId = toast.loading('Reactivating quantity...')

  try {
    const response = await api.post(`/operation-distributor/ec-inventory/inactive/${selectedItem.value.id}/reactivate`, {
      quantity: actionQuantity.value
    })
    
    if (response.data.success) {
      toast.success('Product Quantity Reactivated', { id: toastId, description: `${actionQuantity.value} units back in active inventory.` })
      await refreshData()
      closeViewModal()
    } else {
      toast.error('Operation failed', { id: toastId, description: response.data.message })
    }
  } catch (error) {
    toast.error('Failed to reactivate product quantity.', { id: toastId })
  } finally {
    isProcessing.value = false
  }
}
</script>

<style scoped>
.inventory-container { min-height: 100vh; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(31, 41, 55, 0.5); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(75, 85, 99, 0.8); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(107, 114, 128, 1); }
</style>

<style>
/* Unscoped global override to force Sonner Toaster to the very top */
[data-sonner-toaster] {
  z-index: 2147483647 !important; /* Maximum z-index possible */
  position: fixed !important;
}
</style>