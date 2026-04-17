<template>
  <div class="p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Paint Inventory Management</h1>
          <p class="text-gray-600 mt-2">View your paint products and stock levels</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <Button variant="outline" @click="exportInventory" class="border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center text-sm md:text-base h-10">
            <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export
          </Button>
          <Button @click="fetchInventoryData" class="bg-blue-600 text-white hover:bg-blue-700 flex items-center text-sm md:text-base h-10">
            <svg class="w-4 h-4 md:w-5 md:h-5 mr-2" :class="{'animate-spin': isLoading}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </Button>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <Card class="bg-gradient-to-r from-blue-50 to-blue-100 border-blue-200">
          <CardContent class="p-4">
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
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-r from-green-50 to-green-100 border-green-200">
          <CardContent class="p-4">
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
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-r from-yellow-50 to-yellow-100 border-yellow-200">
          <CardContent class="p-4">
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
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-r from-red-50 to-red-100 border-red-200">
          <CardContent class="p-4">
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
          </CardContent>
        </Card>
      </div>

      <Card class="mb-6 shadow-sm border border-gray-200">
        <CardContent class="p-4">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <Input 
                  v-model="searchQuery"
                  type="text" 
                  placeholder="Search paint products by name, brand, or color..."
                  class="pl-10 w-full focus-visible:ring-blue-500"
                />
              </div>
            </div>
            <div class="flex gap-3">
              <Select v-model="selectedBrand">
                <SelectTrigger class="w-[180px] border-gray-300 focus:ring-blue-500">
                  <SelectValue placeholder="All Brands" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="All Brands" class="cursor-pointer">All Brands</SelectItem>
                  <SelectItem v-for="brand in uniqueBrands" :key="brand" :value="brand">
                    {{ brand }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <Select v-model="stockFilter">
                <SelectTrigger class="w-[180px] border-gray-300 focus:ring-blue-500">
                  <SelectValue placeholder="All Stock" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Stock</SelectItem>
                  <SelectItem value="in-stock">In Stock</SelectItem>
                  <SelectItem value="low-stock">Low Stock</SelectItem>
                  <SelectItem value="out-of-stock">Out of Stock</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <Table>
            <TableHeader class="bg-gray-50">
              <TableRow>
                <TableHead class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700">Product</TableHead>
                <TableHead class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700 hidden md:table-cell">Category / Brand</TableHead>
                <TableHead class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700">Color Base</TableHead>
                <TableHead class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700">Quantity</TableHead>
                <TableHead class="text-left py-3 px-4 md:px-6 text-sm font-semibold text-gray-700 hidden lg:table-cell">Price</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody class="divide-y divide-gray-200">
              <TableRow v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50">
                <TableCell class="py-3 px-4 md:px-6">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 w-10 h-10 rounded-md mr-3 border border-gray-200" :style="{ backgroundColor: product.colorHex }"></div>
                    <div>
                      <p class="font-medium text-gray-900">{{ product.name }}</p>
                      <p class="text-sm text-gray-500 md:hidden">{{ product.brand }}</p>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="py-3 px-4 md:px-6 text-gray-600 hidden md:table-cell">
                  <Badge variant="secondary" class="font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                    {{ product.brand }}
                  </Badge>
                </TableCell>
                <TableCell class="py-3 px-4 md:px-6">
                  <div class="flex items-center">
                    <div class="w-6 h-6 rounded-full border border-gray-300 mr-2" :style="{ backgroundColor: product.colorHex }"></div>
                    <span class="text-gray-700">{{ product.colorBase }}</span>
                  </div>
                </TableCell>
                <TableCell class="py-3 px-4 md:px-6">
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
                </TableCell>
                <TableCell class="py-3 px-4 md:px-6 text-gray-700 hidden lg:table-cell">
                  ₱{{ formatPrice(product.price) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div v-if="filteredProducts.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
          <p class="text-gray-500 mb-4">Try adjusting your search or filter</p>
          <Button @click="resetFilters" class="bg-blue-600 text-white hover:bg-blue-700">
            Reset Filters
          </Button>
        </div>

        <div v-if="filteredProducts.length > 0" class="px-4 md:px-6 py-4 border-t border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm text-gray-700 mb-4 sm:mb-0">
              Showing <span class="font-medium">{{ filteredProducts.length }}</span> of <span class="font-medium">{{ products.length }}</span> products
            </p>
            <div class="flex items-center space-x-2">
              <Button variant="outline" @click="previousPage" :disabled="currentPage === 1" class="h-8 px-3 text-sm">
                Previous
              </Button>
              <span class="text-sm text-gray-700">
                Page {{ currentPage }}
              </span>
              <Button variant="outline" @click="nextPage" :disabled="currentPage * itemsPerPage >= filteredProducts.length" class="h-8 px-3 text-sm">
                Next
              </Button>
            </div>
          </div>
        </div>
      </Card>
    </div>

  </div>
</template>

<script>
import api from '@/utils/axios'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

export default {
  name: 'PaintInventory',
  components: {
    Card,
    CardContent,
    Button,
    Input,
    Badge,
    Label,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
  },
  data() {
    return {
      isLoading: true,
      searchQuery: '',
      selectedBrand: 'All Brands', 
      stockFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      products: []
    }
  },
  computed: {
    filteredProducts() {
      return this.products.filter(product => {
        const matchesSearch = !this.searchQuery || 
          product.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          product.brand.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          product.colorBase.toLowerCase().includes(this.searchQuery.toLowerCase())
        
        const matchesBrand = this.selectedBrand === 'All Brands' || product.brand === this.selectedBrand
        
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
  mounted() {
    this.fetchInventoryData();
  },
  methods: {
    async fetchInventoryData() {
      this.isLoading = true;
      try {
        const response = await api.get('/distributor/paint-inventory');
        if (response.data && response.data.success) {
          this.products = response.data.data;
        }
      } catch (error) {
        console.error("Failed to fetch inventory:", error);
      } finally {
        this.isLoading = false;
      }
    },
    formatPrice(price) {
      if (!price) return '0.00';
      return Number(price).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
    },
    getStockStatusClass(quantity) {
      if (quantity === 0) return 'text-red-600'
      if (quantity <= 20) return 'text-yellow-600'
      return 'text-green-600'
    },
    isLowStock(quantity) {
      return quantity > 0 && quantity <= 20
    },
    exportInventory() {
      const inventoryData = this.filteredProducts.map(p => ({
        'Product Name': p.name,
        'Category/Brand': p.brand,
        'Color Base': p.colorBase,
        'Quantity': p.quantity,
        'Price per Unit': `₱${this.formatPrice(p.price)}`,
        'Status': p.quantity === 0 ? 'Out of Stock' : p.quantity <= 20 ? 'Low Stock' : 'In Stock'
      }))
      
      console.log('Exporting inventory:', inventoryData)
      alert('Inventory data exported (check console for data structure)')
    },
    resetFilters() {
      this.searchQuery = ''
      this.selectedBrand = 'All Brands' 
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
.bg-gradient-to-r {
  background-size: 200% 200%;
  animation: gradient 3s ease infinite;
}

@keyframes gradient {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@media (max-width: 640px) {
  :deep(h1) { font-size: 1.5rem; }
  :deep(.text-2xl) { font-size: 1.25rem; }
  :deep(.text-lg) { font-size: 1rem; }
}
</style>