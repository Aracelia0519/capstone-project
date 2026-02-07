<template>
  <div class="min-h-screen text-slate-200">
    <header class="sticky top-0 z-40  border-b border-slate-800">
      <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl shadow-xl shadow-blue-500/20">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
              </div>
              Paint Products Catalog
            </h1>
            <p class="text-slate-400 mt-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              Reference catalog - Editing disabled
            </p>
          </div>
          
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <Card class="bg-slate-900/50 backdrop-blur-sm border-slate-700">
              <CardContent class="p-3">
                <div class="text-sm text-slate-400">Total Products</div>
                <div class="text-xl font-bold text-white">{{ products.length }}</div>
              </CardContent>
            </Card>
            <Card class="bg-slate-900/50 backdrop-blur-sm border-slate-700">
              <CardContent class="p-3">
                <div class="text-sm text-slate-400">Avg Price</div>
                <div class="text-xl font-bold text-emerald-400">${{ avgPrice.toFixed(2) }}</div>
              </CardContent>
            </Card>
            <Card class="bg-slate-900/50 backdrop-blur-sm border-slate-700">
              <CardContent class="p-3">
                <div class="text-sm text-slate-400">Brands</div>
                <div class="text-xl font-bold text-cyan-400">{{ uniqueBrands }}</div>
              </CardContent>
            </Card>
            <Card class="bg-slate-900/50 backdrop-blur-sm border-slate-700">
              <CardContent class="p-3">
                <div class="text-sm text-slate-400">In Stock</div>
                <div class="text-xl font-bold text-green-400">{{ totalStock }}</div>
              </CardContent>
            </Card>
          </div>
        </div>
        
        <div class="mt-6 flex flex-col sm:flex-row gap-4">
          <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="relative">
              <Input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search paints..." 
                class="pl-12 bg-slate-900 border-slate-700 text-white placeholder:text-slate-500 focus-visible:ring-blue-500"
              />
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            
            <Select v-model="selectedBrand">
              <SelectTrigger class="bg-slate-900 border-slate-700 text-white"><SelectValue placeholder="All Brands" /></SelectTrigger>
              <SelectContent class="bg-slate-900 border-slate-700 text-white">
                <SelectItem value="all">All Brands</SelectItem>
                <SelectItem v-for="brand in brands" :key="brand" :value="brand">{{ brand }}</SelectItem>
              </SelectContent>
            </Select>
            
            <Select v-model="sortBy">
              <SelectTrigger class="bg-slate-900 border-slate-700 text-white"><SelectValue placeholder="Sort by" /></SelectTrigger>
              <SelectContent class="bg-slate-900 border-slate-700 text-white">
                <SelectItem value="name">Sort by Name</SelectItem>
                <SelectItem value="price-asc">Price: Low to High</SelectItem>
                <SelectItem value="price-desc">Price: High to Low</SelectItem>
                <SelectItem value="stock">Stock Available</SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <Button 
            disabled
            variant="outline"
            class="bg-slate-900 border-slate-700 text-slate-400 cursor-not-allowed hover:bg-slate-900 h-10 px-6"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export (Read Only)
          </Button>
        </div>
      </div>
    </header>

    <main class="px-4 sm:px-6 lg:px-8 py-6">
      <div v-if="filteredProducts.length === 0" class="text-center py-16">
        <div class="w-24 h-24 mx-auto mb-6 bg-slate-900 rounded-full flex items-center justify-center text-slate-600">
          <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <h3 class="text-xl font-semibold text-slate-300 mb-2">No products found</h3>
        <p class="text-slate-500">Try adjusting your search or filter criteria</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <Card 
          v-for="product in filteredProducts" 
          :key="product.id"
          class="group relative bg-gradient-to-br from-slate-900 to-slate-950 border-slate-800 transition-all duration-300 hover:border-blue-500/50 hover:shadow-2xl hover:shadow-blue-900/10 hover:-translate-y-1 overflow-hidden"
        >
          <CardContent class="p-5">
            <Badge variant="secondary" class="absolute top-4 right-4 bg-slate-950/90 backdrop-blur-sm border-slate-700 text-slate-400 gap-1.5 shadow-sm">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              Read Only
            </Badge>

            <div 
              class="w-16 h-16 rounded-2xl mb-4 border-4 border-slate-800 shadow-lg"
              :style="{ backgroundColor: product.color }"
            ></div>

            <div class="space-y-4">
              <div>
                <h3 class="text-lg font-bold text-white group-hover:text-blue-300 transition-colors">
                  {{ product.name }}
                </h3>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-sm text-slate-400">{{ product.brand }}</span>
                  <span class="w-1 h-1 bg-slate-600 rounded-full"></span>
                  <span class="text-sm font-medium" :style="{ color: product.color }">
                    {{ product.baseColor }}
                  </span>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <div class="text-2xl font-bold text-white">${{ product.price }}</div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide">per gallon</div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-semibold" :class="product.stock > 10 ? 'text-green-400' : product.stock > 0 ? 'text-yellow-400' : 'text-red-400'">
                    {{ product.stock }} units
                  </div>
                  <div class="text-xs text-slate-500 uppercase tracking-wide">in stock</div>
                </div>
              </div>

              <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2 flex items-center gap-2">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                  Available at
                </div>
                <div class="flex flex-wrap gap-2">
                  <Badge 
                    v-for="distributor in product.distributors" 
                    :key="distributor"
                    variant="outline"
                    class="bg-slate-900/50 backdrop-blur-sm border-slate-700 text-slate-300 hover:border-slate-600"
                  >
                    {{ distributor }}
                  </Badge>
                </div>
              </div>

              <div class="pt-4 border-t border-slate-800 flex gap-2">
                <Button disabled variant="outline" class="flex-1 bg-slate-900 border-slate-700 text-slate-500">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                  Edit
                </Button>
                <Button disabled variant="outline" class="flex-1 bg-slate-900 border-slate-700 text-slate-500">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" /></svg>
                  Save
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <div v-if="filteredProducts.length > 0" class="mt-8 flex items-center justify-between border-t border-slate-800 pt-6">
        <div class="text-slate-400 text-sm">
          Showing <span class="text-white font-medium">{{ filteredProducts.length }}</span> of <span class="text-white font-medium">{{ products.length }}</span> products
        </div>
        <div class="flex items-center gap-2">
          <Button disabled variant="outline" class="bg-slate-900 border-slate-800 text-slate-500">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Previous
          </Button>
          <Button disabled variant="outline" class="bg-slate-900 border-slate-800 text-slate-500">
            Next
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
          </Button>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'

export default {
  name: 'PaintProductsPage',
  components: {
    Card, CardContent, Button, Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Badge
  },
  data() {
    return {
      searchQuery: '',
      selectedBrand: 'all', // changed default to match select value
      sortBy: 'name',
      products: [
        { id: 1, name: 'Midnight Blue', brand: 'Sherwin-Williams', baseColor: 'Dark Blue', color: '#1e3a8a', price: 42.99, stock: 24, distributors: ['Home Depot', 'Lowe\'s', 'Ace Hardware'] },
        { id: 2, name: 'Emerald Matte', brand: 'Benjamin Moore', baseColor: 'Deep Green', color: '#047857', price: 56.50, stock: 15, distributors: ['Benjamin Moore Stores', 'Local Distributors'] },
        { id: 3, name: 'Crimson Red', brand: 'Behr', baseColor: 'Bright Red', color: '#dc2626', price: 34.99, stock: 8, distributors: ['Home Depot'] },
        { id: 4, name: 'Sunshine Yellow', brand: 'Valspar', baseColor: 'Vibrant Yellow', color: '#f59e0b', price: 38.75, stock: 32, distributors: ['Lowe\'s', 'Ace Hardware'] },
        { id: 5, name: 'Arctic White', brand: 'Dulux', baseColor: 'Pure White', color: '#f8fafc', price: 29.99, stock: 45, distributors: ['Home Depot', 'Lowe\'s', 'Local Stores'] },
        { id: 6, name: 'Deep Purple', brand: 'Sherwin-Williams', baseColor: 'Rich Purple', color: '#7c3aed', price: 48.25, stock: 12, distributors: ['Sherwin-Williams Stores', 'Home Depot'] },
        { id: 7, name: 'Charcoal Gray', brand: 'Benjamin Moore', baseColor: 'Dark Gray', color: '#374151', price: 44.80, stock: 18, distributors: ['Benjamin Moore Stores'] },
        { id: 8, name: 'Ocean Teal', brand: 'Behr', baseColor: 'Aqua Blue', color: '#0d9488', price: 39.99, stock: 22, distributors: ['Home Depot', 'Ace Hardware'] },
        { id: 9, name: 'Terracotta', brand: 'Valspar', baseColor: 'Earth Orange', color: '#92400e', price: 41.50, stock: 7, distributors: ['Lowe\'s'] },
        { id: 10, name: 'Rose Quartz', brand: 'Dulux', baseColor: 'Soft Pink', color: '#f472b6', price: 36.25, stock: 14, distributors: ['Local Stores', 'Specialty Retailers'] },
        { id: 11, name: 'Forest Green', brand: 'Sherwin-Williams', baseColor: 'Nature Green', color: '#166534', price: 45.99, stock: 19, distributors: ['Home Depot', 'Sherwin-Williams Stores'] },
        { id: 12, name: 'Goldenrod', brand: 'Benjamin Moore', baseColor: 'Warm Yellow', color: '#ca8a04', price: 52.00, stock: 11, distributors: ['Benjamin Moore Stores'] }
      ]
    }
  },
  computed: {
    filteredProducts() {
      let filtered = [...this.products]
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(p => 
          p.name.toLowerCase().includes(query) ||
          p.brand.toLowerCase().includes(query) ||
          p.baseColor.toLowerCase().includes(query)
        )
      }
      
      if (this.selectedBrand && this.selectedBrand !== 'all') {
        filtered = filtered.filter(p => p.brand === this.selectedBrand)
      }
      
      switch (this.sortBy) {
        case 'name': filtered.sort((a, b) => a.name.localeCompare(b.name)); break
        case 'price-asc': filtered.sort((a, b) => a.price - b.price); break
        case 'price-desc': filtered.sort((a, b) => b.price - a.price); break
        case 'stock': filtered.sort((a, b) => b.stock - a.stock); break
      }
      return filtered
    },
    brands() { return [...new Set(this.products.map(p => p.brand))] },
    avgPrice() { return this.products.reduce((sum, p) => sum + p.price, 0) / this.products.length },
    uniqueBrands() { return this.brands.length },
    totalStock() { return this.products.reduce((sum, p) => sum + p.stock, 0) }
  }
}
</script>

<style scoped>
/* Scroll behavior */
.min-h-screen { scroll-behavior: smooth; }

/* Custom selection color */
::selection { background: rgba(59, 130, 246, 0.3); color: #fff; }

/* Animation class for cards on load */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.group { animation: fadeInUp 0.5s ease-out forwards; }
</style>