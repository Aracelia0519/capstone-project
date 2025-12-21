<template>
  <div class="paint-products-page min-h-screen bg-gray-900">
    <!-- Header Section -->
    <header class="sticky top-0 z-40 bg-gray-900/95 backdrop-blur-lg border-b border-gray-800">
      <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl shadow-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              Paint Products Catalog
            </h1>
            <p class="text-gray-400 mt-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Reference catalog - Editing disabled
            </p>
          </div>
          
          <!-- Stats Cards -->
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 border border-gray-700">
              <div class="text-sm text-gray-400">Total Products</div>
              <div class="text-xl font-bold text-white">{{ products.length }}</div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 border border-gray-700">
              <div class="text-sm text-gray-400">Avg Price</div>
              <div class="text-xl font-bold text-emerald-400">${{ avgPrice.toFixed(2) }}</div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 border border-gray-700">
              <div class="text-sm text-gray-400">Brands</div>
              <div class="text-xl font-bold text-cyan-400">{{ uniqueBrands }}</div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 border border-gray-700">
              <div class="text-sm text-gray-400">In Stock</div>
              <div class="text-xl font-bold text-green-400">{{ totalStock }}</div>
            </div>
          </div>
        </div>
        
        <!-- Filters -->
        <div class="mt-6 flex flex-col sm:flex-row gap-4">
          <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="relative">
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search paints..." 
                class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
              >
              <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            
            <select 
              v-model="selectedBrand"
              class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent focus:outline-none appearance-none"
            >
              <option value="">All Brands</option>
              <option v-for="brand in brands" :key="brand" :value="brand">{{ brand }}</option>
            </select>
            
            <select 
              v-model="sortBy"
              class="bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white focus:ring-2 focus:ring-cyan-500 focus:border-transparent focus:outline-none appearance-none"
            >
              <option value="name">Sort by Name</option>
              <option value="price-asc">Price: Low to High</option>
              <option value="price-desc">Price: High to Low</option>
              <option value="stock">Stock Available</option>
            </select>
          </div>
          
          <!-- Export Button (Disabled) -->
          <button 
            disabled
            class="px-6 py-3 bg-gray-800 border border-gray-700 rounded-xl text-gray-400 cursor-not-allowed flex items-center justify-center gap-2 hover:bg-gray-800 transition-all"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export (Read Only)
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="px-4 sm:px-6 lg:px-8 py-6">
      <!-- Empty State -->
      <div v-if="filteredProducts.length === 0" class="text-center py-16">
        <div class="w-24 h-24 mx-auto mb-6 bg-gray-800 rounded-full flex items-center justify-center">
          <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-300 mb-2">No products found</h3>
        <p class="text-gray-500">Try adjusting your search or filter criteria</p>
      </div>

      <!-- Products Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div 
          v-for="product in filteredProducts" 
          :key="product.id"
          class="group relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 p-5 transition-all duration-300 hover:border-blue-500/50 hover:shadow-2xl hover:shadow-blue-900/10 hover:-translate-y-1"
        >
          <!-- Read Only Badge -->
          <div class="absolute top-4 right-4 px-3 py-1 bg-gray-900/90 backdrop-blur-sm border border-gray-700 rounded-full text-xs text-gray-400 flex items-center gap-1.5">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Read Only
          </div>

          <!-- Color Indicator -->
          <div 
            class="w-16 h-16 rounded-2xl mb-4 border-4 border-gray-700 shadow-lg"
            :style="{ backgroundColor: product.color }"
          ></div>

          <!-- Product Info -->
          <div class="space-y-4">
            <div>
              <h3 class="text-lg font-bold text-white group-hover:text-blue-300 transition-colors">
                {{ product.name }}
              </h3>
              <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400">{{ product.brand }}</span>
                <span class="w-1 h-1 bg-gray-600 rounded-full"></span>
                <span class="text-sm" :style="{ color: product.color }">
                  {{ product.baseColor }}
                </span>
              </div>
            </div>

            <!-- Price & Stock -->
            <div class="flex items-center justify-between">
              <div>
                <div class="text-2xl font-bold text-white">${{ product.price }}</div>
                <div class="text-sm text-gray-400">per gallon</div>
              </div>
              <div class="text-right">
                <div class="text-lg font-semibold" :class="product.stock > 10 ? 'text-green-400' : product.stock > 0 ? 'text-yellow-400' : 'text-red-400'">
                  {{ product.stock }} units
                </div>
                <div class="text-xs text-gray-500">in stock</div>
              </div>
            </div>

            <!-- Distributors -->
            <div>
              <div class="text-sm text-gray-400 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Available at
              </div>
              <div class="flex flex-wrap gap-2">
                <span 
                  v-for="distributor in product.distributors" 
                  :key="distributor"
                  class="px-3 py-1.5 bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-lg text-sm text-gray-300 hover:border-gray-600 transition-all"
                >
                  {{ distributor }}
                </span>
              </div>
            </div>

            <!-- Action Buttons (Disabled) -->
            <div class="pt-4 border-t border-gray-800">
              <div class="flex gap-2">
                <button 
                  disabled
                  class="flex-1 px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-gray-400 cursor-not-allowed flex items-center justify-center gap-2 text-sm transition-all hover:bg-gray-800"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                  Edit
                </button>
                <button 
                  disabled
                  class="flex-1 px-4 py-2.5 bg-gray-800 border border-gray-700 rounded-xl text-gray-400 cursor-not-allowed flex items-center justify-center gap-2 text-sm transition-all hover:bg-gray-800"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                  </svg>
                  Save
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredProducts.length > 0" class="mt-8 flex items-center justify-between">
        <div class="text-gray-400 text-sm">
          Showing {{ filteredProducts.length }} of {{ products.length }} products
        </div>
        <div class="flex items-center gap-2">
          <button 
            disabled
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-xl text-gray-400 cursor-not-allowed flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Previous
          </button>
          <button 
            disabled
            class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-xl text-gray-400 cursor-not-allowed flex items-center gap-2"
          >
            Next
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'PaintProductsPage',
  data() {
    return {
      searchQuery: '',
      selectedBrand: '',
      sortBy: 'name',
      products: [
        {
          id: 1,
          name: 'Midnight Blue',
          brand: 'Sherwin-Williams',
          baseColor: 'Dark Blue',
          color: '#1e3a8a',
          price: 42.99,
          stock: 24,
          distributors: ['Home Depot', 'Lowe\'s', 'Ace Hardware']
        },
        {
          id: 2,
          name: 'Emerald Matte',
          brand: 'Benjamin Moore',
          baseColor: 'Deep Green',
          color: '#047857',
          price: 56.50,
          stock: 15,
          distributors: ['Benjamin Moore Stores', 'Local Distributors']
        },
        {
          id: 3,
          name: 'Crimson Red',
          brand: 'Behr',
          baseColor: 'Bright Red',
          color: '#dc2626',
          price: 34.99,
          stock: 8,
          distributors: ['Home Depot']
        },
        {
          id: 4,
          name: 'Sunshine Yellow',
          brand: 'Valspar',
          baseColor: 'Vibrant Yellow',
          color: '#f59e0b',
          price: 38.75,
          stock: 32,
          distributors: ['Lowe\'s', 'Ace Hardware']
        },
        {
          id: 5,
          name: 'Arctic White',
          brand: 'Dulux',
          baseColor: 'Pure White',
          color: '#f8fafc',
          price: 29.99,
          stock: 45,
          distributors: ['Home Depot', 'Lowe\'s', 'Local Stores']
        },
        {
          id: 6,
          name: 'Deep Purple',
          brand: 'Sherwin-Williams',
          baseColor: 'Rich Purple',
          color: '#7c3aed',
          price: 48.25,
          stock: 12,
          distributors: ['Sherwin-Williams Stores', 'Home Depot']
        },
        {
          id: 7,
          name: 'Charcoal Gray',
          brand: 'Benjamin Moore',
          baseColor: 'Dark Gray',
          color: '#374151',
          price: 44.80,
          stock: 18,
          distributors: ['Benjamin Moore Stores']
        },
        {
          id: 8,
          name: 'Ocean Teal',
          brand: 'Behr',
          baseColor: 'Aqua Blue',
          color: '#0d9488',
          price: 39.99,
          stock: 22,
          distributors: ['Home Depot', 'Ace Hardware']
        },
        {
          id: 9,
          name: 'Terracotta',
          brand: 'Valspar',
          baseColor: 'Earth Orange',
          color: '#92400e',
          price: 41.50,
          stock: 7,
          distributors: ['Lowe\'s']
        },
        {
          id: 10,
          name: 'Rose Quartz',
          brand: 'Dulux',
          baseColor: 'Soft Pink',
          color: '#f472b6',
          price: 36.25,
          stock: 14,
          distributors: ['Local Stores', 'Specialty Retailers']
        },
        {
          id: 11,
          name: 'Forest Green',
          brand: 'Sherwin-Williams',
          baseColor: 'Nature Green',
          color: '#166534',
          price: 45.99,
          stock: 19,
          distributors: ['Home Depot', 'Sherwin-Williams Stores']
        },
        {
          id: 12,
          name: 'Goldenrod',
          brand: 'Benjamin Moore',
          baseColor: 'Warm Yellow',
          color: '#ca8a04',
          price: 52.00,
          stock: 11,
          distributors: ['Benjamin Moore Stores']
        }
      ]
    }
  },
  computed: {
    filteredProducts() {
      let filtered = [...this.products]
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(p => 
          p.name.toLowerCase().includes(query) ||
          p.brand.toLowerCase().includes(query) ||
          p.baseColor.toLowerCase().includes(query)
        )
      }
      
      // Apply brand filter
      if (this.selectedBrand) {
        filtered = filtered.filter(p => p.brand === this.selectedBrand)
      }
      
      // Apply sorting
      switch (this.sortBy) {
        case 'name':
          filtered.sort((a, b) => a.name.localeCompare(b.name))
          break
        case 'price-asc':
          filtered.sort((a, b) => a.price - b.price)
          break
        case 'price-desc':
          filtered.sort((a, b) => b.price - a.price)
          break
        case 'stock':
          filtered.sort((a, b) => b.stock - a.stock)
          break
      }
      
      return filtered
    },
    brands() {
      return [...new Set(this.products.map(p => p.brand))]
    },
    avgPrice() {
      const total = this.products.reduce((sum, p) => sum + p.price, 0)
      return total / this.products.length
    },
    uniqueBrands() {
      return this.brands.length
    },
    totalStock() {
      return this.products.reduce((sum, p) => sum + p.stock, 0)
    }
  },
  mounted() {
    // Simulate loading animation
    setTimeout(() => {
      const cards = document.querySelectorAll('.group')
      cards.forEach((card, index) => {
        card.style.opacity = '0'
        card.style.transform = 'translateY(20px)'
        setTimeout(() => {
          card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
          card.style.opacity = '1'
          card.style.transform = 'translateY(0)'
        }, index * 100)
      })
    }, 100)
  }
}
</script>

<style scoped>
/* Custom scrollbar for the page */
.paint-products-page {
  scroll-behavior: smooth;
}

/* Selection style */
::selection {
  background: rgba(59, 130, 246, 0.3);
  color: #fff;
}

/* Input focus styles */
input:focus, select:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

/* Color indicator glow effect */
.group:hover div[style*="background-color"] {
  box-shadow: 0 0 20px rgba(var(--color), 0.3);
}

/* Distributor tags hover effect */
span[class*="bg-gray-800"]:hover {
  transform: translateY(-1px);
  border-color: rgba(59, 130, 246, 0.5);
}

/* Smooth transitions for all interactive elements */
button, input, select, .group {
  transition: all 0.2s ease-in-out;
}

/* Responsive design enhancements */
@media (max-width: 640px) {
  .grid-cols-1 {
    grid-template-columns: 1fr;
  }
  
  .flex-col {
    flex-direction: column;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .paint-products-page {
    background: #111827;
  }
}

/* Loading animation */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.group {
  animation: fadeInUp 0.5s ease-out forwards;
}
</style>