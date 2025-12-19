<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Paint Products Catalog</h1>
        <p class="text-gray-600 mt-2">Manage your paint inventory and color catalog</p>
      </div>
      <button 
        @click="openAddModal"
        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Add New Paint</span>
      </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Products</p>
            <p class="text-2xl font-bold text-gray-800">{{ paints.length }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Avg Price</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ averagePrice }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Brands</p>
            <p class="text-2xl font-bold text-gray-800">{{ uniqueBrands }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Active Colors</p>
            <p class="text-2xl font-bold text-gray-800">{{ activePaints }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search paint name, brand, or color..."
              class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
            >
            <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
        </div>
        <div class="flex gap-2">
          <select 
            v-model="filterBrand"
            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
          >
            <option value="">All Brands</option>
            <option v-for="brand in brands" :key="brand" :value="brand">{{ brand }}</option>
          </select>
          <select 
            v-model="filterStatus"
            class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
          >
            <option value="all">All Status</option>
            <option value="active">Active Only</option>
            <option value="archived">Archived</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Paint Products Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Paint Details</th>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Brand & Finish</th>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Price</th>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Status</th>
              <th class="py-4 px-6 text-left text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr 
              v-for="paint in filteredPaints" 
              :key="paint.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td class="py-4 px-6">
                <div class="flex items-center gap-4">
                  <div 
                    class="w-12 h-12 rounded-lg border border-gray-200"
                    :style="{ backgroundColor: paint.baseColor }"
                  ></div>
                  <div>
                    <h3 class="font-semibold text-gray-800">{{ paint.name }}</h3>
                    <p class="text-sm text-gray-500">{{ paint.baseColor }}</p>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div>
                  <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    {{ paint.brand }}
                  </span>
                  <p class="mt-2 text-gray-600">{{ paint.finishType }}</p>
                </div>
              </td>
              <td class="py-4 px-6">
                <span class="text-xl font-bold text-gray-800">₱{{ paint.price.toLocaleString() }}</span>
                <p class="text-sm text-gray-500">per gallon</p>
              </td>
              <td class="py-4 px-6">
                <span 
                  :class="[
                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                    paint.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  <span class="w-2 h-2 rounded-full mr-2" :class="paint.status === 'active' ? 'bg-green-500' : 'bg-gray-500'"></span>
                  {{ paint.status === 'active' ? 'Active' : 'Archived' }}
                </span>
              </td>
              <td class="py-4 px-6">
                <div class="flex gap-2">
                  <button 
                    @click="openEditModal(paint)"
                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                    title="Edit"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button 
                    @click="toggleArchive(paint)"
                    :class="[
                      'p-2 rounded-lg transition-colors',
                      paint.status === 'active' ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50'
                    ]"
                    :title="paint.status === 'active' ? 'Archive' : 'Activate'"
                  >
                    <svg v-if="paint.status === 'active'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                  </button>
                  <button 
                    @click="openDeleteModal(paint)"
                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                    title="Delete"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredPaints.length === 0" class="py-16 text-center">
        <div class="flex justify-center mb-4">
          <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No paints found</h3>
        <p class="text-gray-500">Try adjusting your search or add a new paint product.</p>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">
              {{ editingPaint ? 'Edit Paint Product' : 'Add New Paint' }}
            </h2>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600 text-2xl">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Paint Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Paint Name *</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
                placeholder="e.g., Ocean Blue Premium"
              >
            </div>

            <!-- Brand -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Brand *</label>
              <select
                v-model="form.brand"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
              >
                <option value="">Select Brand</option>
                <option value="Boysen">Boysen</option>
                <option value="Davies">Davies</option>
                <option value="Nippon">Nippon</option>
                <option value="Coatings">Coatings Philippines</option>
                <option value="Asian">Asian Paints</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <!-- Base Color -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Base Color *</label>
              <div class="flex gap-2">
                <input
                  v-model="form.baseColor"
                  type="color"
                  class="w-16 h-16 cursor-pointer"
                >
                <input
                  v-model="form.baseColor"
                  type="text"
                  class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                  placeholder="#RRGGBB"
                >
              </div>
            </div>

            <!-- Finish Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Finish Type</label>
              <select
                v-model="form.finishType"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
              >
                <option value="">Select Finish</option>
                <option value="Matte">Matte</option>
                <option value="Gloss">Gloss</option>
                <option value="Semi-Gloss">Semi-Gloss</option>
                <option value="Satin">Satin</option>
                <option value="Eggshell">Eggshell</option>
              </select>
            </div>

            <!-- Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Price (₱) *</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
                <input
                  v-model="form.price"
                  type="number"
                  min="0"
                  step="0.01"
                  class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                  placeholder="0.00"
                >
              </div>
            </div>

            <!-- Status -->
            <div v-if="editingPaint">
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="form.status"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
              >
                <option value="active">Active</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>

          <!-- Description -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none"
              placeholder="Optional description about this paint..."
            ></textarea>
          </div>

          <!-- Unity Integration Note -->
          <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
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
        </div>

        <div class="p-6 border-t border-gray-200 flex justify-end gap-4">
          <button
            @click="closeModal"
            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="savePaint"
            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg"
          >
            {{ editingPaint ? 'Update Paint' : 'Add Paint' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl w-full max-w-md">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-800">Delete Paint</h2>
        </div>
        <div class="p-6">
          <div class="text-center mb-6">
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <p class="text-gray-700">
              Are you sure you want to delete <strong>"{{ paintToDelete?.name }}"</strong>?
            </p>
            <p class="text-sm text-gray-500 mt-2">
              This action cannot be undone. This paint will be removed from the catalog and Unity system.
            </p>
          </div>
        </div>
        <div class="p-6 border-t border-gray-200 flex justify-end gap-4">
          <button
            @click="showDeleteModal = false"
            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            @click="confirmDelete"
            class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
          >
            Delete Permanently
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// State
const paints = ref([])
const searchQuery = ref('')
const filterBrand = ref('')
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
    
    const matchesBrand = !filterBrand.value || paint.brand === filterBrand.value
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
/* Custom scrollbar for table */
.table-container::-webkit-scrollbar {
  height: 8px;
}

.table-container::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Modal animations */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-content-enter-active,
.modal-content-leave-active {
  transition: all 0.3s ease;
}

.modal-content-enter-from {
  opacity: 0;
  transform: scale(0.9);
}

.modal-content-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

/* Color input styling */
input[type="color"] {
  -webkit-appearance: none;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 0;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 6px;
}

/* Responsive table adjustments */
@media (max-width: 768px) {
  table {
    display: block;
  }
  
  thead {
    display: none;
  }
  
  tr {
    display: block;
    margin-bottom: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1rem;
  }
  
  td {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
  }
  
  td:last-child {
    border-bottom: none;
  }
  
  td::before {
    content: attr(data-label);
    font-weight: 600;
    color: #6b7280;
    margin-right: 1rem;
  }
}
</style>