<!-- ECommerceProducts.vue -->
<template>
  <div class="ecommerce-products p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Product Management</h1>
          <p class="text-gray-300">Manage paint products and related services</p>
        </div>
        <button @click="showAddModal = true" class="mt-4 md:mt-0 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add New Product
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm text-gray-300 mb-2">Search</label>
          <input type="text" v-model="searchQuery" placeholder="Search products..." class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500">
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Category</label>
          <select v-model="selectedCategory" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Categories</option>
            <option value="interior">Interior Paints</option>
            <option value="exterior">Exterior Paints</option>
            <option value="primer">Primers</option>
            <option value="coating">Coatings</option>
            <option value="service">Painting Services</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Status</label>
          <select v-model="selectedStatus" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="out_of_stock">Out of Stock</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-white transition-colors">
            Reset Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="bg-gray-900/80">
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Product</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Type</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Price</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Stock</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Distributor</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
              <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in filteredProducts" :key="product.id" class="border-t border-gray-800 hover:bg-white/5">
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-white font-medium">{{ product.name }}</h4>
                    <p class="text-sm text-gray-400">{{ product.category }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  product.type === 'Paint' ? 'bg-blue-500/20 text-blue-300' :
                  product.type === 'Service' ? 'bg-purple-500/20 text-purple-300' :
                  'bg-amber-500/20 text-amber-300'
                ]">
                  {{ product.type }}
                </span>
              </td>
              <td class="py-4 px-6 text-white font-medium">₱{{ product.price }}</td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <span class="text-white mr-2">{{ product.stock }}</span>
                  <div class="w-16 h-2 bg-gray-700 rounded-full overflow-hidden">
                    <div :class="[
                      'h-full rounded-full',
                      product.stock > 50 ? 'bg-green-500' :
                      product.stock > 20 ? 'bg-yellow-500' : 'bg-red-500'
                    ]" :style="{ width: Math.min(product.stock, 100) + '%' }"></div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs mr-2">
                    {{ product.distributor.charAt(0) }}
                  </div>
                  <span class="text-gray-300">{{ product.distributor }}</span>
                </div>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-3 py-1 rounded-full text-xs font-medium',
                  product.status === 'Active' ? 'bg-green-500/20 text-green-300' :
                  'bg-red-500/20 text-red-300'
                ]">
                  {{ product.status }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="flex space-x-2">
                  <button @click="editProduct(product)" class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="deleteProduct(product)" class="p-2 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">{{ editingProduct ? 'Edit Product' : 'Add New Product' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <form @submit.prevent="saveProduct" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Product Name -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Product Name *</label>
                <input type="text" v-model="productForm.name" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
              </div>
              
              <!-- Product Type -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Product Type *</label>
                <select v-model="productForm.type" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="Paint">Paint</option>
                  <option value="Service">Service</option>
                  <option value="Tools">Tools</option>
                </select>
              </div>
              
              <!-- Price -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Price *</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">₱</span>
                  <input type="number" v-model="productForm.price" required class="w-full pl-8 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                </div>
              </div>
              
              <!-- Stock -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Stock Quantity *</label>
                <input type="number" v-model="productForm.stock" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
              </div>
              
              <!-- Category -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Category *</label>
                <select v-model="productForm.category" required class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="Interior Paints">Interior Paints</option>
                  <option value="Exterior Paints">Exterior Paints</option>
                  <option value="Primers">Primers</option>
                  <option value="Coatings">Coatings</option>
                  <option value="Painting Services">Painting Services</option>
                </select>
              </div>
              
              <!-- Paint Type -->
              <div v-if="productForm.type === 'Paint'">
                <label class="block text-sm text-gray-300 mb-2">Paint Type</label>
                <select v-model="productForm.paintType" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="oil">Oil-based</option>
                  <option value="latex">Latex</option>
                  <option value="acrylic">Acrylic</option>
                  <option value="water">Water-based</option>
                </select>
              </div>
              
              <!-- Unit Size -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Unit Size</label>
                <select v-model="productForm.unitSize" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="1L">1 Liter</option>
                  <option value="4L">4 Liters</option>
                  <option value="1G">1 Gallon</option>
                  <option value="5G">5 Gallons</option>
                  <option value="bucket">Bucket</option>
                </select>
              </div>
              
              <!-- Distributor -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Assign Distributor</label>
                <select v-model="productForm.distributor" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="CaviteGo Distributors">CaviteGo Distributors</option>
                  <option value="Premium Paint Supply">Premium Paint Supply</option>
                  <option value="Metro Paint Hub">Metro Paint Hub</option>
                  <option value="South Paint Distributors">South Paint Distributors</option>
                </select>
              </div>
              
              <!-- Status -->
              <div>
                <label class="block text-sm text-gray-300 mb-2">Status</label>
                <select v-model="productForm.status" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="Active">Active</option>
                  <option value="Out of Stock">Out of Stock</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>
            
            <!-- Description -->
            <div>
              <label class="block text-sm text-gray-300 mb-2">Description</label>
              <textarea v-model="productForm.description" rows="3" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
            </div>
            
            <!-- Color Variants -->
            <div v-if="productForm.type === 'Paint'">
              <label class="block text-sm text-gray-300 mb-2">Color Variants</label>
              <div class="flex flex-wrap gap-2">
                <div v-for="color in productForm.colorVariants" :key="color" class="flex items-center space-x-2">
                  <div class="w-6 h-6 rounded-full border border-gray-700" :style="{ backgroundColor: color }"></div>
                  <span class="text-gray-300 text-sm">{{ color }}</span>
                  <button type="button" @click="removeColor(color)" class="text-red-400 hover:text-red-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                <button type="button" @click="addColor" class="px-3 py-1 border border-dashed border-gray-600 text-gray-400 rounded-lg hover:text-white hover:border-gray-500">
                  + Add Color
                </button>
              </div>
            </div>
            
            <!-- Image Upload -->
            <div>
              <label class="block text-sm text-gray-300 mb-2">Product Images</label>
              <div class="border-2 border-dashed border-gray-700 rounded-lg p-6 text-center hover:border-gray-600 transition-colors">
                <svg class="w-12 h-12 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-gray-400">Drag & drop product images or click to browse</p>
                <p class="text-sm text-gray-500 mt-1">Supports: JPG, PNG, WebP (Max 5MB)</p>
              </div>
            </div>
            
            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
              <button type="button" @click="closeModal" class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
                Cancel
              </button>
              <button type="submit" class="px-6 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                {{ editingProduct ? 'Update Product' : 'Add Product' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')
const showAddModal = ref(false)
const editingProduct = ref(null)

const products = ref([
  { id: 1, name: 'Premium White Interior Paint', type: 'Paint', price: 2450, stock: 78, distributor: 'CaviteGo Distributors', category: 'Interior Paints', status: 'Active' },
  { id: 2, name: 'Weatherproof Exterior Paint', type: 'Paint', price: 3250, stock: 45, distributor: 'Premium Paint Supply', category: 'Exterior Paints', status: 'Active' },
  { id: 3, name: 'Quick Dry Primer', type: 'Paint', price: 1280, stock: 120, distributor: 'Metro Paint Hub', category: 'Primers', status: 'Active' },
  { id: 4, name: 'Wall Painting Service', type: 'Service', price: 8500, stock: 0, distributor: 'CaviteGo Distributors', category: 'Painting Services', status: 'Out of Stock' },
  { id: 5, name: 'Gloss Finish Coating', type: 'Paint', price: 2980, stock: 32, distributor: 'South Paint Distributors', category: 'Coatings', status: 'Active' },
  { id: 6, name: 'Eco-Friendly Interior Paint', type: 'Paint', price: 2750, stock: 56, distributor: 'Premium Paint Supply', category: 'Interior Paints', status: 'Active' },
  { id: 7, name: 'Ceiling Painting Service', type: 'Service', price: 6500, stock: 0, distributor: 'CaviteGo Distributors', category: 'Painting Services', status: 'Inactive' },
  { id: 8, name: 'Anti-Mold Exterior Paint', type: 'Paint', price: 3850, stock: 24, distributor: 'Metro Paint Hub', category: 'Exterior Paints', status: 'Active' }
])

const productForm = ref({
  name: '',
  type: 'Paint',
  price: '',
  stock: '',
  category: 'Interior Paints',
  paintType: 'latex',
  unitSize: '4L',
  distributor: 'CaviteGo Distributors',
  status: 'Active',
  description: '',
  colorVariants: ['#ffffff', '#f1f1f1', '#e6e6e6']
})

const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesSearch = searchQuery.value === '' || 
      product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      product.category.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesCategory = selectedCategory.value === '' || 
      product.category.toLowerCase().includes(selectedCategory.value)
    
    const matchesStatus = selectedStatus.value === '' || 
      product.status.toLowerCase().replace(' ', '_') === selectedStatus.value
    
    return matchesSearch && matchesCategory && matchesStatus
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  selectedStatus.value = ''
}

const editProduct = (product) => {
  editingProduct.value = product
  productForm.value = { ...product }
  showAddModal.value = true
}

const deleteProduct = (product) => {
  if (confirm(`Are you sure you want to delete "${product.name}"?`)) {
    products.value = products.value.filter(p => p.id !== product.id)
  }
}

const addColor = () => {
  const color = prompt('Enter color hex code (e.g., #ff0000):')
  if (color && /^#[0-9A-F]{6}$/i.test(color)) {
    productForm.value.colorVariants.push(color)
  }
}

const removeColor = (color) => {
  productForm.value.colorVariants = productForm.value.colorVariants.filter(c => c !== color)
}

const saveProduct = () => {
  if (editingProduct.value) {
    // Update existing product
    const index = products.value.findIndex(p => p.id === editingProduct.value.id)
    if (index !== -1) {
      products.value[index] = { ...productForm.value, id: editingProduct.value.id }
    }
  } else {
    // Add new product
    const newId = Math.max(...products.value.map(p => p.id)) + 1
    products.value.push({ ...productForm.value, id: newId })
  }
  
  closeModal()
}

const closeModal = () => {
  showAddModal.value = false
  editingProduct.value = null
  productForm.value = {
    name: '',
    type: 'Paint',
    price: '',
    stock: '',
    category: 'Interior Paints',
    paintType: 'latex',
    unitSize: '4L',
    distributor: 'CaviteGo Distributors',
    status: 'Active',
    description: '',
    colorVariants: ['#ffffff', '#f1f1f1', '#e6e6e6']
  }
}
</script>

<style scoped>
.ecommerce-products {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-products {
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>