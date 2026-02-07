<template>
  <div class="p-6 bg-white min-h-screen">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Paint Products Catalog</h1>
        <p class="text-gray-600 mt-2">Manage your paint inventory and color catalog</p>
      </div>
      <Button 
        @click="openAddModal"
        class="px-6 py-6 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl gap-2 border-0"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span class="text-base font-medium">Add New Paint</span>
      </Button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <Card class="shadow-sm border-gray-100">
        <CardContent class="p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Products</p>
            <p class="text-2xl font-bold text-gray-800">{{ paints.length }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow-sm border-gray-100">
        <CardContent class="p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Avg Price</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ averagePrice }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow-sm border-gray-100">
        <CardContent class="p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Brands</p>
            <p class="text-2xl font-bold text-gray-800">{{ uniqueBrands }}</p>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow-sm border-gray-100">
        <CardContent class="p-6 flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Active Colors</p>
            <p class="text-2xl font-bold text-gray-800">{{ activePaints }}</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="shadow-sm border-gray-100 mb-6">
      <CardContent class="p-4">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1 relative">
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 z-10">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <Input
              v-model="searchQuery"
              type="text"
              placeholder="Search paint name, brand, or color..."
              class="w-full pl-10 h-[46px] border-gray-300 focus-visible:ring-blue-500"
            />
          </div>
          <div class="flex gap-2 w-full md:w-auto">
             <Select v-model="filterBrand">
              <SelectTrigger class="w-full md:w-[180px] h-[46px] border-gray-300">
                <SelectValue placeholder="All Brands" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="ALL_BRANDS">All Brands</SelectItem>
                <SelectItem v-for="brand in brands" :key="brand" :value="brand">
                  {{ brand }}
                </SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="filterStatus">
              <SelectTrigger class="w-full md:w-[180px] h-[46px] border-gray-300">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="active">Active Only</SelectItem>
                <SelectItem value="archived">Archived</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="shadow-sm border-gray-100 overflow-hidden bg-white">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold w-[300px]">Paint Details</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Brand & Finish</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Price</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold">Status</TableHead>
              <TableHead class="py-4 px-6 text-gray-700 font-semibold text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow 
              v-for="paint in filteredPaints" 
              :key="paint.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-4">
                  <div 
                    class="w-12 h-12 rounded-lg border border-gray-200 shadow-sm"
                    :style="{ backgroundColor: paint.baseColor }"
                  ></div>
                  <div>
                    <h3 class="font-semibold text-gray-800">{{ paint.name }}</h3>
                    <p class="text-sm text-gray-500">{{ paint.baseColor }}</p>
                  </div>
                </div>
              </TableCell>
              <TableCell class="py-4 px-6">
                <div class="flex flex-col items-start gap-2">
                  <Badge variant="secondary" class="bg-blue-100 text-blue-800 hover:bg-blue-200 border-none font-medium">
                    {{ paint.brand }}
                  </Badge>
                  <p class="text-sm text-gray-600">{{ paint.finishType }}</p>
                </div>
              </TableCell>
              <TableCell class="py-4 px-6">
                <span class="text-xl font-bold text-gray-800">₱{{ paint.price.toLocaleString() }}</span>
                <p class="text-sm text-gray-500">per gallon</p>
              </TableCell>
              <TableCell class="py-4 px-6">
                <Badge 
                  :class="[
                    'font-medium px-3 py-1 rounded-full border-none shadow-none',
                    paint.status === 'active' ? 'bg-green-100 text-green-800 hover:bg-green-100' : 'bg-gray-100 text-gray-800 hover:bg-gray-100'
                  ]"
                >
                  <span class="w-2 h-2 rounded-full mr-2 inline-block" :class="paint.status === 'active' ? 'bg-green-500' : 'bg-gray-500'"></span>
                  {{ paint.status === 'active' ? 'Active' : 'Archived' }}
                </Badge>
              </TableCell>
              <TableCell class="py-4 px-6 text-right">
                <div class="flex gap-1 justify-end">
                  <Button variant="ghost" size="icon" @click="openEditModal(paint)" class="text-blue-600 hover:bg-blue-50 hover:text-blue-700" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </Button>
                  <Button 
                    variant="ghost" 
                    size="icon" 
                    @click="toggleArchive(paint)"
                    :class="[
                      paint.status === 'active' ? 'text-yellow-600 hover:bg-yellow-50 hover:text-yellow-700' : 'text-green-600 hover:bg-green-50 hover:text-green-700'
                    ]"
                    :title="paint.status === 'active' ? 'Archive' : 'Activate'"
                  >
                     <svg v-if="paint.status === 'active'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </Button>
                  <Button variant="ghost" size="icon" @click="openDeleteModal(paint)" class="text-red-600 hover:bg-red-50 hover:text-red-700" title="Delete">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="filteredPaints.length === 0" class="py-16 text-center">
        <div class="flex justify-center mb-4">
          <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No paints found</h3>
        <p class="text-gray-500">Try adjusting your search or add a new paint product.</p>
      </div>
    </Card>

    <Dialog :open="showModal" @update:open="val => !val && closeModal()">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-2xl font-bold text-gray-800">
             {{ editingPaint ? 'Edit Paint Product' : 'Add New Paint' }}
          </DialogTitle>
        </DialogHeader>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-4">
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Paint Name *</label>
            <Input
              v-model="form.name"
              type="text"
              class="h-12 border-gray-300 focus-visible:ring-blue-500"
              placeholder="e.g., Ocean Blue Premium"
            />
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Brand *</label>
            <Select v-model="form.brand">
              <SelectTrigger class="h-12 border-gray-300">
                <SelectValue placeholder="Select Brand" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Boysen">Boysen</SelectItem>
                <SelectItem value="Davies">Davies</SelectItem>
                <SelectItem value="Nippon">Nippon</SelectItem>
                <SelectItem value="Coatings">Coatings Philippines</SelectItem>
                <SelectItem value="Asian">Asian Paints</SelectItem>
                <SelectItem value="Other">Other</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Base Color *</label>
            <div class="flex gap-2">
              <div class="relative w-16 h-12">
                 <input
                  v-model="form.baseColor"
                  type="color"
                  class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                />
                 <div 
                  class="w-full h-full rounded-lg border-2 border-gray-200" 
                  :style="{ backgroundColor: form.baseColor }"
                ></div>
              </div>
              <Input
                v-model="form.baseColor"
                type="text"
                class="flex-1 h-12 border-gray-300 focus-visible:ring-blue-500"
                placeholder="#RRGGBB"
              />
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Finish Type</label>
            <Select v-model="form.finishType">
              <SelectTrigger class="h-12 border-gray-300">
                <SelectValue placeholder="Select Finish" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="Matte">Matte</SelectItem>
                <SelectItem value="Gloss">Gloss</SelectItem>
                <SelectItem value="Semi-Gloss">Semi-Gloss</SelectItem>
                <SelectItem value="Satin">Satin</SelectItem>
                <SelectItem value="Eggshell">Eggshell</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Price (₱) *</label>
            <div class="relative">
              <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
              <Input
                v-model="form.price"
                type="number"
                min="0"
                step="0.01"
                class="pl-10 h-12 border-gray-300 focus-visible:ring-blue-500"
                placeholder="0.00"
              />
            </div>
          </div>

          <div v-if="editingPaint" class="space-y-2">
            <label class="text-sm font-medium text-gray-700">Status</label>
            <Select v-model="form.status">
              <SelectTrigger class="h-12 border-gray-300">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="archived">Archived</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700">Description</label>
          <Textarea
            v-model="form.description"
            rows="3"
            class="border-gray-300 focus-visible:ring-blue-500 resize-none min-h-[80px]"
            placeholder="Optional description about this paint..."
          />
        </div>

        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
          <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
            </svg>
            <div>
              <p class="font-medium text-blue-800">Unity Integration Ready</p>
              <p class="text-sm text-blue-600 mt-1">
                This paint will be available in the Virtual Paint Color Mixing System.
                Colors are sent to Unity via API for real-time visualization.
              </p>
            </div>
          </div>
        </div>

        <DialogFooter class="flex gap-4 pt-4 border-t mt-4">
          <Button variant="outline" @click="closeModal" class="h-12 px-6 border-gray-300 text-gray-700 hover:bg-gray-50">
            Cancel
          </Button>
          <Button 
            @click="savePaint"
            class="h-12 px-6 bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700 border-0 shadow-md"
          >
             {{ editingPaint ? 'Update Paint' : 'Add Paint' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog :open="showDeleteModal" @update:open="val => showDeleteModal = val">
      <DialogContent class="max-w-md">
        <DialogHeader>
          <DialogTitle class="text-2xl font-bold text-gray-800">Delete Paint</DialogTitle>
        </DialogHeader>
        
        <div class="text-center py-4">
          <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <p class="text-gray-700 text-lg">
            Are you sure you want to delete <strong>"{{ paintToDelete?.name }}"</strong>?
          </p>
          <p class="text-sm text-gray-500 mt-2">
            This action cannot be undone. This paint will be removed from the catalog and Unity system.
          </p>
        </div>

        <DialogFooter class="flex gap-3 sm:justify-end">
          <Button variant="outline" @click="showDeleteModal = false" class="h-11 px-6 border-gray-300">
            Cancel
          </Button>
          <Button variant="destructive" @click="confirmDelete" class="h-11 px-6 bg-red-600 hover:bg-red-700">
            Delete Permanently
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'

// State
const paints = ref([])
const searchQuery = ref('')
const filterBrand = ref('ALL_BRANDS') // Changed default to match a specific SelectItem value
const filterStatus = ref('all')
const showModal = ref(false)
const showDeleteModal = ref(false)
const editingPaint = ref(null)
const paintToDelete = ref(null)

// Form data
const form = ref({
  name: '',
  brand: '',
  baseColor: '#4A90E2',
  finishType: '',
  price: 0,
  status: 'active',
  description: ''
})

// Computed properties
const filteredPaints = computed(() => {
  return paints.value.filter(paint => {
    const matchesSearch = !searchQuery.value || 
      paint.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      paint.brand.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      paint.baseColor.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    // Logic updated to handle Shadcn select value
    const matchesBrand = filterBrand.value === 'ALL_BRANDS' || paint.brand === filterBrand.value
    const matchesStatus = filterStatus.value === 'all' || paint.status === filterStatus.value
    
    return matchesSearch && matchesBrand && matchesStatus
  })
})

const averagePrice = computed(() => {
  if (paints.value.length === 0) return '0'
  const total = paints.value.reduce((sum, paint) => sum + paint.price, 0)
  return (total / paints.value.length).toFixed(2)
})

const uniqueBrands = computed(() => {
  const brands = new Set(paints.value.map(paint => paint.brand))
  return brands.size
})

const activePaints = computed(() => {
  return paints.value.filter(paint => paint.status === 'active').length
})

const brands = computed(() => {
  const unique = [...new Set(paints.value.map(paint => paint.brand))]
  return unique.sort()
})

// Methods
const openAddModal = () => {
  editingPaint.value = null
  resetForm()
  showModal.value = true
}

const openEditModal = (paint) => {
  editingPaint.value = paint
  form.value = { ...paint }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  resetForm()
}

const resetForm = () => {
  form.value = {
    name: '',
    brand: '',
    baseColor: '#4A90E2',
    finishType: '',
    price: 0,
    status: 'active',
    description: ''
  }
}

const savePaint = () => {
  if (!form.value.name || !form.value.brand || !form.value.baseColor || !form.value.price) {
    alert('Please fill in all required fields')
    return
  }

  if (editingPaint.value) {
    // Update existing paint
    const index = paints.value.findIndex(p => p.id === editingPaint.value.id)
    if (index !== -1) {
      paints.value[index] = { ...form.value, id: editingPaint.value.id }
    }
  } else {
    // Add new paint
    const newPaint = {
      ...form.value,
      id: Date.now(), // In real app, this would come from backend
      createdAt: new Date().toISOString()
    }
    paints.value.unshift(newPaint)
  }

  closeModal()
}

const toggleArchive = (paint) => {
  paint.status = paint.status === 'active' ? 'archived' : 'active'
}

const openDeleteModal = (paint) => {
  paintToDelete.value = paint
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (paintToDelete.value) {
    const index = paints.value.findIndex(p => p.id === paintToDelete.value.id)
    if (index !== -1) {
      paints.value.splice(index, 1)
    }
  }
  showDeleteModal.value = false
  paintToDelete.value = null
}

// Initialize with sample data
onMounted(() => {
  paints.value = [
    {
      id: 1,
      name: 'Ocean Blue Premium',
      brand: 'Boysen',
      baseColor: '#4A90E2',
      finishType: 'Gloss',
      price: 1250.00,
      status: 'active',
      description: 'Premium marine-grade blue paint with UV protection'
    },
    {
      id: 2,
      name: 'Sunset Orange',
      brand: 'Davies',
      baseColor: '#FF6B6B',
      finishType: 'Matte',
      price: 980.50,
      status: 'active',
      description: 'Warm orange perfect for accent walls'
    },
    {
      id: 3,
      name: 'Meadow Green',
      brand: 'Nippon',
      baseColor: '#51C16B',
      finishType: 'Satin',
      price: 1100.00,
      status: 'active',
      description: 'Eco-friendly green with low VOC'
    },
    {
      id: 4,
      name: 'Lavender Dream',
      brand: 'Coatings',
      baseColor: '#9B59B6',
      finishType: 'Semi-Gloss',
      price: 1350.75,
      status: 'active',
      description: 'Soft lavender for bedrooms and nurseries'
    },
    {
      id: 5,
      name: 'Classic White',
      brand: 'Asian',
      baseColor: '#FFFFFF',
      finishType: 'Eggshell',
      price: 850.00,
      status: 'archived',
      description: 'Discontinued - replaced by Pearl White'
    }
  ]
})
</script>

<style scoped>
/* Keeping custom styling for color input wrapper as it handles specific UI logic */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>