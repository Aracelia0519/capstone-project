<template>
  <div class="p-4 md:p-6">
    <!-- Page Header with Actions -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Paint Inventory Management</h1>
          <p class="text-gray-600 mt-2">Manage your paint products and stock levels</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button @click="exportInventory" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center text-sm md:text-base">
            <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export
          </button>
          <button @click="openAddProductModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center text-sm md:text-base">
            <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-blue-700">Total Products</p>
            <p class="text-2xl font-bold text-blue-900 mt-1">{{ inventoryStats.totalProducts }}</p>
          </div>
          <div class="p-2 bg-blue-200 rounded-lg">
            <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-green-700">In Stock Items</p>
            <p class="text-2xl font-bold text-green-900 mt-1">{{ inventoryStats.inStockItems }}</p>
          </div>
          <div class="p-2 bg-green-200 rounded-lg">
            <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-yellow-700">Low Stock Items</p>
            <p class="text-2xl font-bold text-yellow-900 mt-1">{{ inventoryStats.lowStockItems }}</p>
          </div>
          <div class="p-2 bg-yellow-200 rounded-lg">
            <svg class="w-6 h-6 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.286 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-xl p-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-red-700">Out of Stock</p>
            <p class="text-2xl font-bold text-red-900 mt-1">{{ inventoryStats.outOfStockItems }}</p>
          </div>
          <div class="p-2 bg-red-200 rounded-lg">
            <svg class="w-6 h-6 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search paint products by name, brand, or color..."
              class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
        </div>
        <div class="flex gap-3">
          <select v-model="selectedBrand" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Brands</option>
            <option v-for="brand in uniqueBrands" :key="brand" :value="brand">{{ brand }}</option>
          </select>
          <select v-model="stockFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="all">All Stock</option>
            <option value="in-stock">In Stock</option>
            <option value="low-stock">Low Stock</option>
            <option value="out-of-stock">Out of Stock</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700">Product</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700 hidden md:table-cell">Brand</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700">Color Base</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700">Quantity</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700 hidden lg:table-cell">Price</th>
              <th class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50">
              <td class="py-3 px-4 md:px-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0 w-10 h-10 rounded-md mr-3" :style="{ backgroundColor: product.colorHex }"></div>
                  <div>
                    <p class="font-medium text-gray-900">{{ product.name }}</p>
                    <p class="text-sm text-gray-500 md:hidden">{{ product.brand }}</p>
                  </div>
                </div>
              </td>
              <td class="py-3 px-4 md:px-6 text-gray-600 hidden md:table-cell">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ product.brand }}
                </span>
              </td>
              <td class="py-3 px-4 md:px-6">
                <div class="flex items-center">
                  <div class="w-6 h-6 rounded-full border border-gray-300 mr-2" :style="{ backgroundColor: product.colorHex }"></div>
                  <span class="text-gray-700">{{ product.colorBase }}</span>
                </div>
              </td>
              <td class="py-3 px-4 md:px-6">
                <div class="flex items-center">
                  <span :class="getStockStatusClass(product.quantity)" class="font-medium">
                    {{ product.quantity }}
                  </span>
                  <span class="text-gray-500 ml-1">units</span>
                  <span v-if="isLowStock(product.quantity)" class="ml-2">
                    <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.286 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                  </span>
                </div>
              </td>
              <td class="py-3 px-4 md:px-6 text-gray-700 hidden lg:table-cell">
                ₱{{ formatPrice(product.price) }}
              </td>
              <td class="py-3 px-4 md:px-6">
                <div class="flex items-center space-x-2">
                  <button @click="openAddStockModal(product)" class="p-1.5 text-green-600 hover:text-green-800 hover:bg-green-50 rounded" title="Add Stock">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                  </button>
                  <button @click="openUpdateQuantityModal(product)" class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Update Quantity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </button>
                  <button @click="toggleAvailability(product)" :class="getAvailabilityButtonClass(product)" class="p-1.5 rounded" :title="product.quantity > 0 ? 'Mark as Unavailable' : 'Mark as Available'">
                    <svg v-if="product.quantity > 0" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredProducts.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
        <p class="text-gray-500 mb-4">Try adjusting your search or filter</p>
        <button @click="resetFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Reset Filters
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="filteredProducts.length > 0" class="px-4 md:px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <p class="text-sm text-gray-700 mb-4 sm:mb-0">
            Showing <span class="font-medium">{{ filteredProducts.length }}</span> of <span class="font-medium">{{ products.length }}</span> products
          </p>
          <div class="flex items-center space-x-2">
            <button @click="previousPage" :disabled="currentPage === 1" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Previous
            </button>
            <span class="text-sm text-gray-700">
              Page {{ currentPage }}
            </span>
            <button @click="nextPage" :disabled="currentPage * itemsPerPage >= filteredProducts.length" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Product Modal -->
    <div v-if="showAddProductModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-lg max-w-md w-full">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Add New Paint Product</h3>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
              <input v-model="newProduct.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
              <select v-model="newProduct.brand" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Brand</option>
                <option v-for="brand in uniqueBrands" :key="brand" :value="brand">{{ brand }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Color Base</label>
                <input v-model="newProduct.colorBase" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Color Hex</label>
                <div class="flex items-center">
                  <input v-model="newProduct.colorHex" type="color" class="w-8 h-8 border-0 rounded cursor-pointer">
                  <input v-model="newProduct.colorHex" type="text" class="ml-2 flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Initial Quantity</label>
                <input v-model.number="newProduct.quantity" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price per Unit</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">₱</span>
                  <input v-model.number="newProduct.price" type="number" min="0" step="0.01" class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button @click="closeAddProductModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="addNewProduct" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Add Product
          </button>
        </div>
      </div>
    </div>

    <!-- Add Stock Modal -->
    <div v-if="showAddStockModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-lg max-w-sm w-full">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Add Stock to {{ selectedProduct?.name }}</h3>
        </div>
        <div class="px-6 py-4">
          <p class="text-gray-600 mb-4">Current stock: {{ selectedProduct?.quantity }} units</p>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Add</label>
            <input v-model.number="stockToAdd" type="number" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter quantity">
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
          <button @click="closeAddStockModal" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
            Cancel
          </button>
          <button @click="confirmAddStock" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
            Add Stock
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaintInventory',
  data() {
    return {
      searchQuery: '',
      selectedBrand: '',
      stockFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      showAddProductModal: false,
      showAddStockModal: false,
      selectedProduct: null,
      stockToAdd: 10,
      newProduct: {
        name: '',
        brand: '',
        colorBase: '',
        colorHex: '#3B82F6',
        quantity: 0,
        price: 0
      },
      products: [
        {
          id: 1,
          name: 'Premium White Paint',
          brand: 'Boysen',
          colorBase: 'White',
          colorHex: '#FFFFFF',
          quantity: 150,
          price: 450.00
        },
        {
          id: 2,
          name: 'Ocean Blue Interior',
          brand: 'Davies',
          colorBase: 'Blue',
          colorHex: '#1E40AF',
          quantity: 85,
          price: 520.00
        },
        {
          id: 3,
          name: 'Sunset Yellow Gloss',
          brand: 'Columbia',
          colorBase: 'Yellow',
          colorHex: '#F59E0B',
          quantity: 25,
          price: 480.00
        },
        {
          id: 4,
          name: 'Royal Red Matte',
          brand: 'Boysen',
          colorBase: 'Red',
          colorHex: '#DC2626',
          quantity: 0,
          price: 550.00
        },
        {
          id: 5,
          name: 'Forest Green Satin',
          brand: 'Davies',
          colorBase: 'Green',
          colorHex: '#059669',
          quantity: 45,
          price: 510.00
        },
        {
          id: 6,
          name: 'Charcoal Gray Exterior',
          brand: 'Columbia',
          colorBase: 'Gray',
          colorHex: '#4B5563',
          quantity: 120,
          price: 490.00
        },
        {
          id: 7,
          name: 'Sky Blue Latex',
          brand: 'Boysen',
          colorBase: 'Blue',
          colorHex: '#0EA5E9',
          quantity: 5,
          price: 470.00
        },
        {
          id: 8,
          name: 'Coral Pink Interior',
          brand: 'Davies',
          colorBase: 'Pink',
          colorHex: '#EC4899',
          quantity: 65,
          price: 530.00
        },
        {
          id: 9,
          name: 'Chocolate Brown Gloss',
          brand: 'Columbia',
          colorBase: 'Brown',
          colorHex: '#78350F',
          quantity: 30,
          price: 500.00
        },
        {
          id: 10,
          name: 'Violet Dream',
          brand: 'Boysen',
          colorBase: 'Purple',
          colorHex: '#7C3AED',
          quantity: 95,
          price: 540.00
        }
      ]
    }
  },
  computed: {
    filteredProducts() {
      return this.products.filter(product => {
        // Search filter
        const matchesSearch = !this.searchQuery || 
          product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          product.brand.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          product.colorBase.toLowerCase().includes(this.searchQuery.toLowerCase())
        
        // Brand filter
        const matchesBrand = !this.selectedBrand || product.brand === this.selectedBrand
        
        // Stock filter
        let matchesStock = true
        switch(this.stockFilter) {
          case 'in-stock':
            matchesStock = product.quantity > 20
            break
          case 'low-stock':
            matchesStock = product.quantity > 0 && product.quantity <= 20
            break
          case 'out-of-stock':
            matchesStock = product.quantity === 0
            break
        }
        
        return matchesSearch && matchesBrand && matchesStock
      })
    },
    uniqueBrands() {
      return [...new Set(this.products.map(p => p.brand))]
    },
    inventoryStats() {
      return {
        totalProducts: this.products.length,
        inStockItems: this.products.filter(p => p.quantity > 20).length,
        lowStockItems: this.products.filter(p => p.quantity > 0 && p.quantity <= 20).length,
        outOfStockItems: this.products.filter(p => p.quantity === 0).length
      }
    }
  },
  methods: {
    formatPrice(price) {
      return price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
    },
    getStockStatusClass(quantity) {
      if (quantity === 0) return 'text-red-600'
      if (quantity <= 20) return 'text-yellow-600'
      return 'text-green-600'
    },
    isLowStock(quantity) {
      return quantity > 0 && quantity <= 20
    },
    getAvailabilityButtonClass(product) {
      return product.quantity > 0 
        ? 'text-red-600 hover:text-red-800 hover:bg-red-50' 
        : 'text-green-600 hover:text-green-800 hover:bg-green-50'
    },
    openAddProductModal() {
      this.newProduct = {
        name: '',
        brand: '',
        colorBase: '',
        colorHex: '#3B82F6',
        quantity: 0,
        price: 0
      }
      this.showAddProductModal = true
    },
    closeAddProductModal() {
      this.showAddProductModal = false
    },
    addNewProduct() {
      if (!this.newProduct.name || !this.newProduct.brand || !this.newProduct.colorBase) {
        alert('Please fill in all required fields')
        return
      }
      
      const newId = Math.max(...this.products.map(p => p.id)) + 1
      this.products.unshift({
        id: newId,
        ...this.newProduct
      })
      
      this.showAddProductModal = false
      alert('Product added successfully!')
    },
    openAddStockModal(product) {
      this.selectedProduct = product
      this.stockToAdd = 10
      this.showAddStockModal = true
    },
    closeAddStockModal() {
      this.showAddStockModal = false
      this.selectedProduct = null
    },
    confirmAddStock() {
      if (this.stockToAdd <= 0) {
        alert('Please enter a valid quantity')
        return
      }
      
      const index = this.products.findIndex(p => p.id === this.selectedProduct.id)
      if (index !== -1) {
        this.products[index].quantity += this.stockToAdd
      }
      
      this.showAddStockModal = false
      this.selectedProduct = null
      alert('Stock added successfully!')
    },
    openUpdateQuantityModal(product) {
      const newQuantity = prompt(`Enter new quantity for ${product.name}:`, product.quantity)
      if (newQuantity !== null && !isNaN(newQuantity)) {
        const index = this.products.findIndex(p => p.id === product.id)
        if (index !== -1) {
          this.products[index].quantity = parseInt(newQuantity)
        }
      }
    },
    toggleAvailability(product) {
      const index = this.products.findIndex(p => p.id === product.id)
      if (index !== -1) {
        if (product.quantity > 0) {
          // Mark as unavailable (out of stock)
          this.products[index].quantity = 0
          alert(`${product.name} marked as out of stock`)
        } else {
          // Mark as available - ask for initial quantity
          const initialQuantity = prompt(`Enter initial quantity for ${product.name}:`, '50')
          if (initialQuantity !== null && !isNaN(initialQuantity)) {
            this.products[index].quantity = parseInt(initialQuantity)
          }
        }
      }
    },
    exportInventory() {
      // In a real app, this would generate and download a CSV/Excel file
      const inventoryData = this.filteredProducts.map(p => ({
        'Product Name': p.name,
        Brand: p.brand,
        'Color Base': p.colorBase,
        Quantity: p.quantity,
        'Price per Unit': `₱${p.price.toFixed(2)}`,
        Status: p.quantity === 0 ? 'Out of Stock' : p.quantity <= 20 ? 'Low Stock' : 'In Stock'
      }))
      
      console.log('Exporting inventory:', inventoryData)
      alert('Inventory data exported (check console for data structure)')
    },
    resetFilters() {
      this.searchQuery = ''
      this.selectedBrand = ''
      this.stockFilter = 'all'
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--
      }
    },
    nextPage() {
      if (this.currentPage * this.itemsPerPage < this.filteredProducts.length) {
        this.currentPage++
      }
    }
  }
}
</script>

<style scoped>
/* Enhanced scrollbar for mobile */
@media (max-width: 768px) {
  .overflow-x-auto {
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
  }
  
  .overflow-x-auto::-webkit-scrollbar {
    height: 6px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
}

/* Gradient text effects for stats */
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient 3s ease infinite;
}

@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Modal animations */
.fixed {
  animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Color swatch styling */
[type="color"] {
  -webkit-appearance: none;
  border: none;
  padding: 0;
}

[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

[type="color"]::-webkit-color-swatch {
  border: 2px solid #e5e7eb;
  border-radius: 0.375rem;
}

/* Hover effects for buttons */
button:not(:disabled) {
  transition: all 0.2s ease;
}

button:not(:disabled):hover {
  transform: translateY(-1px);
}

/* Table row highlight animation */
tr {
  transition: background-color 0.2s ease;
}

tr:hover {
  background-color: #f9fafb;
}

/* Responsive typography */
@media (max-width: 640px) {
  :deep(h1) {
    font-size: 1.5rem;
  }
  
  :deep(.text-2xl) {
    font-size: 1.25rem;
  }
  
  :deep(.text-lg) {
    font-size: 1rem;
  }
}

/* Loading skeleton animation */
@keyframes shimmer {
  0% {
    background-position: -468px 0;
  }
  100% {
    background-position: 468px 0;
  }
}

.shimmer {
  background: linear-gradient(to right, #f6f7f8 8%, #edeef1 18%, #f6f7f8 33%);
  background-size: 800px 104px;
  animation: shimmer 1.5s infinite linear;
}

/* Custom focus styles */
:focus {
  outline: none;
}

:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Print styles */
@media print {
  .no-print {
    display: none !important;
  }
  
  .bg-white {
    background: white !important;
  }
}
</style>