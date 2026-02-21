<template>
  <div class="ecommerce-categories p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Product Categories</h1>
          <p class="text-gray-300">Organize products for better customer navigation</p>
        </div>
        <Button 
          @click="showAddModal = true" 
          class="mt-4 md:mt-0 bg-gradient-to-r from-emerald-500 to-teal-500 text-white hover:opacity-90 border-0"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Category
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">{{ categories.length }}</CardTitle>
          <CardDescription class="text-gray-300">Total Categories</CardDescription>
        </CardHeader>
      </Card>
      
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">{{ activeCategories }}</CardTitle>
          <CardDescription class="text-gray-300">Active</CardDescription>
        </CardHeader>
      </Card>

      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">{{ totalProducts }}</CardTitle>
          <CardDescription class="text-gray-300">Total Products</CardDescription>
        </CardHeader>
      </Card>

      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">{{ averageProducts }}</CardTitle>
          <CardDescription class="text-gray-300">Avg Products/Category</CardDescription>
        </CardHeader>
      </Card>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white"></div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="category in categories" :key="category.id" 
           class="bg-gray-900/50 backdrop-blur-sm border-gray-800 hover:border-gray-700 transition-colors text-white">
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center">
              <div :class="[
                'w-12 h-12 rounded-xl flex items-center justify-center mr-4',
                category.colorClass
              ]">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-white mb-1">{{ category.name }}</h3>
                <div class="flex items-center">
                  <Badge variant="outline" :class="[
                    'rounded-full border-0',
                    category.status === 'Active' ? 'bg-green-500/20 text-green-300' : 'bg-red-500/20 text-red-300'
                  ]">
                    {{ category.status }}
                  </Badge>
                  <span class="text-sm text-gray-400 ml-3">{{ category.productCount }} products</span>
                </div>
              </div>
            </div>
            
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon" class="text-gray-400 hover:text-white hover:bg-gray-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-48 bg-gray-800 border-gray-700 text-gray-300">
                <DropdownMenuItem @click="editCategory(category)" class="focus:bg-gray-700 focus:text-white cursor-pointer">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit Category
                </DropdownMenuItem>
                <DropdownMenuItem @click="toggleStatus(category)" class="focus:bg-gray-700 focus:text-white cursor-pointer">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  {{ category.status === 'Active' ? 'Disable' : 'Enable' }}
                </DropdownMenuItem>
                <DropdownMenuItem @click="deleteCategory(category)" class="text-red-400 focus:bg-red-500/10 focus:text-red-400 cursor-pointer">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Delete
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
          
          <p class="text-gray-400 mb-4">{{ category.description }}</p>
          
          <div class="mb-4">
            <div class="flex items-center justify-between text-sm text-gray-300 mb-2">
              <span>Products in this category:</span>
              <span class="font-medium">{{ category.productCount }}</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
              <Badge v-for="product in category.sampleProducts" :key="product" variant="secondary"
                   class="bg-gray-800 text-gray-300 hover:bg-gray-700 justify-center truncate border-0">
                {{ product }}
              </Badge>
              <span v-if="!category.sampleProducts || category.sampleProducts.length === 0" class="text-sm text-gray-500 italic">No products</span>
            </div>
          </div>
        </CardContent>
        <CardFooter class="flex items-center justify-between pt-0 pb-6 px-6 border-t border-gray-800 pt-4">
          <span class="text-sm text-gray-400">Created: {{ category.createdDate }}</span>
          <Button variant="link" @click="viewProducts(category)" class="text-indigo-400 hover:text-indigo-300 p-0 h-auto">
            View Products →
          </Button>
        </CardFooter>
      </Card>
    </div>

    <Dialog v-model:open="showAddModal" @update:open="(val) => !val && closeModal()">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-md">
        <DialogHeader>
          <DialogTitle>{{ editingCategory ? 'Edit Category' : 'Add New Category' }}</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="saveCategory" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label class="text-gray-300">Category Name *</Label>
            <Input v-model="categoryForm.name" required class="bg-gray-800 border-gray-700 text-white" />
          </div>
          
          <div class="space-y-2">
            <Label class="text-gray-300">Description</Label>
            <Textarea v-model="categoryForm.description" rows="3" class="bg-gray-800 border-gray-700 text-white" />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label class="text-gray-300">Status</Label>
              <Select v-model="categoryForm.status">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="Active">Active</SelectItem>
                  <SelectItem value="Inactive">Inactive</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Color Theme</Label>
              <Select v-model="categoryForm.colorClass">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select color" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="bg-gradient-to-br from-blue-500 to-cyan-500">Blue</SelectItem>
                  <SelectItem value="bg-gradient-to-br from-purple-500 to-pink-500">Purple</SelectItem>
                  <SelectItem value="bg-gradient-to-br from-emerald-500 to-teal-500">Green</SelectItem>
                  <SelectItem value="bg-gradient-to-br from-amber-500 to-yellow-500">Yellow</SelectItem>
                  <SelectItem value="bg-gradient-to-br from-red-500 to-orange-500">Red</SelectItem>
                  <SelectItem value="bg-gradient-to-br from-indigo-500 to-violet-500">Indigo</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          
          <div class="pt-4 border-t border-gray-800 flex justify-end space-x-3">
            <Button type="button" variant="outline" @click="closeModal" 
                   class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
              Cancel
            </Button>
            <Button type="submit" class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white hover:opacity-90 border-0">
              {{ editingCategory ? 'Update Category' : 'Add Category' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showProductsModal" @update:open="(val) => !val && (showProductsModal = false)">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle class="text-xl">Products in {{ selectedCategoryForProducts?.name }}</DialogTitle>
          <DialogDescription class="text-gray-400">
            A detailed list of all products grouped under this category.
          </DialogDescription>
        </DialogHeader>
        
        <div class="py-4">
          <div v-if="isLoadingProducts" class="flex justify-center items-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-500"></div>
          </div>

          <div v-else-if="categoryProducts.length === 0" class="text-center text-gray-400 py-8 bg-gray-800/50 rounded-lg border border-gray-800">
            <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            No products found in this category.
            <div class="mt-2 text-xs text-gray-500">Check browser console if you expect items here.</div>
          </div>

          <div v-else class="space-y-3 max-h-[60vh] overflow-y-auto pr-2 custom-scrollbar">
            <div v-for="product in categoryProducts" :key="product.id" 
                 class="flex items-center justify-between bg-gray-800/50 p-4 rounded-xl border border-gray-700 hover:border-gray-600 transition-colors">
              <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-gray-900 rounded-lg object-cover flex items-center justify-center overflow-hidden border border-gray-700">
                  <img v-if="product.image_url" :src="`http://localhost:8000/storage/${product.image_url}`" :alt="product.name" class="w-full h-full object-cover" />
                  <svg v-else class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <h4 class="font-bold text-white text-lg leading-tight">{{ product.name }}</h4>
                  <div class="flex items-center text-xs text-gray-400 mt-1 space-x-2">
                    <span>SKU: <span class="text-gray-300">{{ product.sku_code || 'N/A' }}</span></span>
                    <span>•</span>
                    <span>Size: <span class="text-gray-300">{{ product.size }}</span></span>
                    <span>•</span>
                    <span v-if="product.type" class="bg-gray-700 px-2 py-0.5 rounded text-gray-300">{{ product.type }}</span>
                  </div>
                </div>
              </div>
              <div class="text-right flex flex-col items-end justify-center">
                <div class="font-bold text-emerald-400 text-lg mb-1">₱{{ Number(product.price || 0).toLocaleString('en-US', {minimumFractionDigits: 2}) }}</div>
                <Badge variant="outline" :class="[
                  'text-xs font-medium border-0',
                  product.is_active || product.is_active === 1 ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'
                ]">
                  {{ (product.is_active || product.is_active === 1) ? 'Active' : 'Inactive' }}
                </Badge>
              </div>
            </div>
          </div>
        </div>
        
        <div class="pt-4 border-t border-gray-800 flex justify-end">
          <Button type="button" variant="outline" @click="showProductsModal = false" 
                 class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
            Close
          </Button>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios' 
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const showAddModal = ref(false)
const showProductsModal = ref(false)
const editingCategory = ref(null)
const categories = ref([])
const isLoading = ref(true)

// State for Products Modal
const selectedCategoryForProducts = ref(null)
const categoryProducts = ref([])
const isLoadingProducts = ref(false)

const categoryForm = ref({
  name: '',
  description: '',
  status: 'Active',
  colorClass: 'bg-gradient-to-br from-blue-500 to-cyan-500'
})

// Fetch Data from API
const fetchCategories = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/operation-distributor/categories')
    categories.value = response.data.categories
  } catch (error) {
    console.error('Failed to fetch categories:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchCategories()
})

const activeCategories = computed(() => {
  return categories.value.filter(c => c.status === 'Active').length
})

const totalProducts = computed(() => {
  return categories.value.reduce((sum, cat) => sum + cat.productCount, 0)
})

const averageProducts = computed(() => {
  if (categories.value.length === 0) return 0
  return Math.round(totalProducts.value / categories.value.length)
})

const editCategory = (category) => {
  editingCategory.value = category
  categoryForm.value = { ...category }
  showAddModal.value = true
}

const toggleStatus = (category) => {
  category.status = category.status === 'Active' ? 'Inactive' : 'Active'
}

const deleteCategory = (category) => {
  if (confirm(`Are you sure you want to remove the category "${category.name}" from view? Note: Real deletion requires re-assigning associated products in the database.`)) {
    categories.value = categories.value.filter(c => c.id !== category.id)
  }
}

// Open modal and fetch products for the selected category using the new endpoint
const viewProducts = async (category) => {
  selectedCategoryForProducts.value = category
  showProductsModal.value = true
  isLoadingProducts.value = true
  categoryProducts.value = []
  
  try {
    // Calling the newly created endpoint and sending the category name directly
    const response = await api.get(`/operation-distributor/categories/products?category=${encodeURIComponent(category.name)}`)
    
    console.log("Raw API Response for Products:", response.data)
    
    // Extract the payload
    if (response.data && response.data.data) {
      categoryProducts.value = response.data.data
    } else if (Array.isArray(response.data)) {
      categoryProducts.value = response.data
    }
  } catch (error) {
    console.error('Failed to fetch products for category:', error)
  } finally {
    isLoadingProducts.value = false
  }
}

const saveCategory = () => {
  // NOTE: Categories are dynamic based on products. 
  // To truly save this to the DB, you would need a standalone "categories" table schema.
  // For now, it updates the UI state.
  if (editingCategory.value) {
    const index = categories.value.findIndex(c => c.id === editingCategory.value.id)
    if (index !== -1) {
      categories.value[index] = { ...categoryForm.value, id: editingCategory.value.id }
    }
  } else {
    const newId = categories.value.length > 0 ? Math.max(...categories.value.map(c => c.id)) + 1 : 1
    categories.value.push({
      ...categoryForm.value,
      id: newId,
      productCount: 0,
      createdDate: new Date().toISOString().split('T')[0],
      sampleProducts: []
    })
  }
  
  closeModal()
}

const closeModal = () => {
  showAddModal.value = false
  editingCategory.value = null
  categoryForm.value = {
    name: '',
    description: '',
    status: 'Active',
    colorClass: 'bg-gradient-to-br from-blue-500 to-cyan-500'
  }
}
</script>

<style scoped>
.ecommerce-categories {
  min-height: 100vh;
}

/* Custom Scrollbar for the Products Modal */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
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

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-categories {
    padding: 1rem;
  }
}
</style>