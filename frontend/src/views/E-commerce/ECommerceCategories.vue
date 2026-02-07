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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
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
const editingCategory = ref(null)

const categories = ref([
  { 
    id: 1, 
    name: 'Interior Paints', 
    description: 'High-quality paints for interior walls and ceilings',
    status: 'Active', 
    productCount: 42,
    createdDate: '2024-01-15',
    colorClass: 'bg-gradient-to-br from-blue-500 to-cyan-500',
    sampleProducts: ['White Gloss', 'Matte Finish', 'Premium White', 'Eco-Friendly']
  },
  { 
    id: 2, 
    name: 'Exterior Paints', 
    description: 'Weather-resistant paints for exterior surfaces',
    status: 'Active', 
    productCount: 28,
    createdDate: '2024-01-20',
    colorClass: 'bg-gradient-to-br from-emerald-500 to-teal-500',
    sampleProducts: ['Weatherproof', 'Anti-Mold', 'UV Protection', 'Heat Resistant']
  },
  { 
    id: 3, 
    name: 'Primers', 
    description: 'Base coat preparations for optimal paint adhesion',
    status: 'Active', 
    productCount: 18,
    createdDate: '2024-02-05',
    colorClass: 'bg-gradient-to-br from-amber-500 to-yellow-500',
    sampleProducts: ['Quick Dry', 'Multi-Surface', 'Stain Blocking', 'High Build']
  },
  { 
    id: 4, 
    name: 'Coatings', 
    description: 'Specialized protective and decorative coatings',
    status: 'Active', 
    productCount: 15,
    createdDate: '2024-02-10',
    colorClass: 'bg-gradient-to-br from-purple-500 to-pink-500',
    sampleProducts: ['Gloss Finish', 'Matte Coating', 'Textured', 'Clear Coat']
  },
  { 
    id: 5, 
    name: 'Painting Services', 
    description: 'Professional painting and finishing services',
    status: 'Inactive', 
    productCount: 8,
    createdDate: '2024-02-15',
    colorClass: 'bg-gradient-to-br from-red-500 to-orange-500',
    sampleProducts: ['Wall Painting', 'Ceiling Work', 'Exterior Service', 'Industrial']
  }
])

const categoryForm = ref({
  name: '',
  description: '',
  status: 'Active',
  colorClass: 'bg-gradient-to-br from-blue-500 to-cyan-500'
})

const activeCategories = computed(() => {
  return categories.value.filter(c => c.status === 'Active').length
})

const totalProducts = computed(() => {
  return categories.value.reduce((sum, cat) => sum + cat.productCount, 0)
})

const averageProducts = computed(() => {
  return Math.round(totalProducts.value / categories.value.length)
})

// Dropped toggleActions as Shadcn Dropdown handles it internally
// Kept logic for data manipulation

const editCategory = (category) => {
  editingCategory.value = category
  categoryForm.value = { ...category }
  showAddModal.value = true
}

const toggleStatus = (category) => {
  category.status = category.status === 'Active' ? 'Inactive' : 'Active'
}

const deleteCategory = (category) => {
  if (confirm(`Are you sure you want to delete "${category.name}"?`)) {
    categories.value = categories.value.filter(c => c.id !== category.id)
  }
}

const viewProducts = (category) => {
  alert(`Viewing products in ${category.name}`)
}

const saveCategory = () => {
  if (editingCategory.value) {
    const index = categories.value.findIndex(c => c.id === editingCategory.value.id)
    if (index !== -1) {
      categories.value[index] = { ...categoryForm.value, id: editingCategory.value.id }
    }
  } else {
    const newId = Math.max(...categories.value.map(c => c.id)) + 1
    categories.value.push({
      ...categoryForm.value,
      id: newId,
      productCount: 0,
      createdDate: new Date().toISOString().split('T')[0],
      sampleProducts: ['Sample Product 1', 'Sample Product 2', 'Sample Product 3']
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

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-categories {
    padding: 1rem;
  }
}
</style>