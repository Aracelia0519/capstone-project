<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-gray-900">Paint Shop</h1>
        <p class="text-gray-600 mt-2">Browse our premium collection of paints</p>
      </div>
    </div>

    <div class="bg-white border-b border-gray-200">
      <div class="container mx-auto px-4 py-4">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <Input
                type="text"
                v-model="searchQuery"
                placeholder="Search paints..."
                class="pl-10 pr-4 py-3 h-12 w-full"
              />
            </div>
          </div>

          <div class="flex gap-2 overflow-x-auto pb-2 lg:pb-0">
            <Button
              v-for="filter in quickFilters"
              :key="filter.id"
              @click="toggleFilter(filter.id)"
              :variant="activeFilters.includes(filter.id) ? 'default' : 'secondary'"
              :class="[
                'whitespace-nowrap flex items-center space-x-2',
                activeFilters.includes(filter.id)
                  ? 'bg-blue-100 text-blue-700 border-blue-300 hover:bg-blue-200'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="filter.icon"></path>
              </svg>
              <span>{{ filter.label }}</span>
            </Button>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Brand</Label>
            <Select v-model="selectedBrand">
              <SelectTrigger>
                <SelectValue placeholder="All Brands" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_brands_reset">All Brands</SelectItem>
                <SelectItem v-for="brand in brands" :key="brand" :value="brand">{{ brand }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Type</Label>
            <Select v-model="selectedType">
              <SelectTrigger>
                <SelectValue placeholder="All Types" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_types_reset">All Types</SelectItem>
                <SelectItem v-for="type in types" :key="type" :value="type">{{ type }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Finish</Label>
            <Select v-model="selectedFinish">
              <SelectTrigger>
                <SelectValue placeholder="All Finishes" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_finishes_reset">All Finishes</SelectItem>
                <SelectItem v-for="finish in finishes" :key="finish" :value="finish">{{ finish }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-sm font-medium text-gray-700 mb-1">Price Range</Label>
            <Select v-model="selectedPrice">
              <SelectTrigger>
                <SelectValue placeholder="All Prices" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_prices_reset">All Prices</SelectItem>
                <SelectItem v-for="price in priceRanges" :key="price" :value="price">{{ price }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="flex justify-between items-center mb-6">
        <p class="text-gray-600">{{ filteredProducts.length }} products found</p>
        <div class="flex items-center space-x-4">
          <span class="text-gray-600">Sort by:</span>
          <div class="w-[180px]">
            <Select v-model="sortBy">
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="name">Name</SelectItem>
                <SelectItem value="price-low">Price: Low to High</SelectItem>
                <SelectItem value="price-high">Price: High to Low</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <Card
          v-for="product in filteredProducts"
          :key="product.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden group"
        >
          <div class="h-48 bg-gradient-to-br from-blue-50 to-purple-50 relative overflow-hidden">
            <div class="absolute top-3 left-3">
              <Badge :class="[
                'border-0',
                product.stock > 10 ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'
              ]">
                {{ product.stock > 10 ? 'In Stock' : 'Low Stock' }}
              </Badge>
            </div>
            <div class="h-full flex items-center justify-center">
              <div class="w-32 h-32 rounded-full" :style="{ backgroundColor: product.color }"></div>
            </div>
          </div>

          <CardContent class="p-4 pb-2">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ product.name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ product.brand }} • {{ product.type }}</p>
              </div>
              <button class="text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
              </button>
            </div>

            <div class="mt-3 flex justify-between items-center">
              <div>
                <span class="text-xl font-bold text-gray-900">₱{{ product.price.toLocaleString() }}</span>
                <span class="text-sm text-gray-500 ml-2">/gallon</span>
              </div>
              <div class="flex items-center">
                <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                <span class="ml-1 text-sm text-gray-600">{{ product.rating }}</span>
              </div>
            </div>
          </CardContent>

          <CardFooter class="mt-4 pt-4 border-t border-gray-100 p-4">
            <div class="flex justify-between items-center w-full">
              <span class="text-sm text-gray-500">{{ product.stock }} in stock</span>
              <Button
                @click="addToCart(product)"
                :disabled="product.stock === 0"
                :class="[
                  product.stock === 0
                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed hover:bg-gray-100'
                    : 'bg-blue-600 text-white hover:bg-blue-700'
                ]"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                <span>{{ product.stock === 0 ? 'Out of Stock' : 'Add to Cart' }}</span>
              </Button>
            </div>
          </CardFooter>
        </Card>
      </div>

      <div v-if="filteredProducts.length === 0" class="text-center py-16">
        <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No products found</h3>
        <p class="text-gray-500">Try adjusting your filters or search terms</p>
        <Button
          @click="clearFilters"
          class="mt-4 bg-blue-600 hover:bg-blue-700 text-white"
        >
          Clear All Filters
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
// shadcn components
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

// Mock Data
const products = ref([
  { id: 1, name: 'Premium Interior White', brand: 'CaviteGo', type: 'Interior', finish: 'Matte', price: 1250, stock: 45, rating: 4.5, color: '#ffffff' },
  { id: 2, name: 'WeatherGuard Exterior', brand: 'CaviteGo', type: 'Exterior', finish: 'Gloss', price: 1850, stock: 28, rating: 4.7, color: '#4a90e2' },
  { id: 3, name: 'Eco-Friendly Green', brand: 'EcoPaint', type: 'Interior', finish: 'Satin', price: 1650, stock: 15, rating: 4.8, color: '#50c878' },
  { id: 4, name: 'Bathroom Moisture', brand: 'CaviteGo', type: 'Interior', finish: 'Semi-Gloss', price: 1950, stock: 32, rating: 4.6, color: '#a7d8de' },
  { id: 5, name: 'Premium Red Accent', brand: 'ColorMax', type: 'Interior', finish: 'Matte', price: 1450, stock: 8, rating: 4.4, color: '#ff6b6b' },
  { id: 6, name: 'Metal Primer', brand: 'CaviteGo', type: 'Primer', finish: 'Flat', price: 950, stock: 50, rating: 4.3, color: '#8b7355' },
  { id: 7, name: 'Wood Varnish', brand: 'WoodPro', type: 'Exterior', finish: 'Gloss', price: 2250, stock: 22, rating: 4.9, color: '#d2b48c' },
  { id: 8, name: 'Clear Coat Finish', brand: 'CaviteGo', type: 'Top Coat', finish: 'Gloss', price: 1750, stock: 18, rating: 4.5, color: '#f5f5dc' },
])

const quickFilters = ref([
  { id: 'interior', label: 'Interior', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  { id: 'exterior', label: 'Exterior', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064' },
  { id: 'low-price', label: 'Under ₱1,500', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { id: 'eco', label: 'Eco-Friendly', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
])

const brands = ref(['CaviteGo', 'EcoPaint', 'ColorMax', 'WoodPro', 'PremiumCo'])
const types = ref(['Interior', 'Exterior', 'Primer', 'Top Coat'])
const finishes = ref(['Matte', 'Gloss', 'Satin', 'Semi-Gloss', 'Flat'])
const priceRanges = ref(['Under ₱1,000', '₱1,000 - ₱2,000', '₱2,000 - ₱3,000', 'Over ₱3,000'])

// State
const searchQuery = ref('')
const activeFilters = ref([])
const selectedBrand = ref('')
const selectedType = ref('')
const selectedFinish = ref('')
const selectedPrice = ref('')
const sortBy = ref('name')

// Watchers for Select resets (shadcn select doesn't natively support clearing unless value is null, but our logic used empty string)
// We add "reset" values in the template options to handle this, or handle empty string mapping.
watch([selectedBrand, selectedType, selectedFinish, selectedPrice], ([newBrand, newType, newFinish, newPrice]) => {
    if (newBrand === 'all_brands_reset') selectedBrand.value = ''
    if (newType === 'all_types_reset') selectedType.value = ''
    if (newFinish === 'all_finishes_reset') selectedFinish.value = ''
    if (newPrice === 'all_prices_reset') selectedPrice.value = ''
})

// Computed
const filteredProducts = computed(() => {
  let filtered = [...products.value]

  // Search
  if (searchQuery.value) {
    filtered = filtered.filter(p => 
      p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.brand.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Quick filters
  if (activeFilters.value.includes('interior')) {
    filtered = filtered.filter(p => p.type === 'Interior')
  }
  if (activeFilters.value.includes('exterior')) {
    filtered = filtered.filter(p => p.type === 'Exterior')
  }
  if (activeFilters.value.includes('low-price')) {
    filtered = filtered.filter(p => p.price < 1500)
  }
  if (activeFilters.value.includes('eco')) {
    filtered = filtered.filter(p => p.brand === 'EcoPaint')
  }

  // Advanced filters
  if (selectedBrand.value && selectedBrand.value !== 'all_brands_reset') {
    filtered = filtered.filter(p => p.brand === selectedBrand.value)
  }
  if (selectedType.value && selectedType.value !== 'all_types_reset') {
    filtered = filtered.filter(p => p.type === selectedType.value)
  }
  if (selectedFinish.value && selectedFinish.value !== 'all_finishes_reset') {
    filtered = filtered.filter(p => p.finish === selectedFinish.value)
  }
  if (selectedPrice.value && selectedPrice.value !== 'all_prices_reset') {
    if (selectedPrice.value === 'Under ₱1,000') {
      filtered = filtered.filter(p => p.price < 1000)
    } else if (selectedPrice.value === '₱1,000 - ₱2,000') {
      filtered = filtered.filter(p => p.price >= 1000 && p.price <= 2000)
    } else if (selectedPrice.value === '₱2,000 - ₱3,000') {
      filtered = filtered.filter(p => p.price >= 2000 && p.price <= 3000)
    } else if (selectedPrice.value === 'Over ₱3,000') {
      filtered = filtered.filter(p => p.price > 3000)
    }
  }

  // Sorting
  if (sortBy.value === 'name') {
    filtered.sort((a, b) => a.name.localeCompare(b.name))
  } else if (sortBy.value === 'price-low') {
    filtered.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-high') {
    filtered.sort((a, b) => b.price - a.price)
  }

  return filtered
})

// Methods
const toggleFilter = (filterId) => {
  const index = activeFilters.value.indexOf(filterId)
  if (index > -1) {
    activeFilters.value.splice(index, 1)
  } else {
    activeFilters.value.push(filterId)
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  activeFilters.value = []
  selectedBrand.value = ''
  selectedType.value = ''
  selectedFinish.value = ''
  selectedPrice.value = ''
  sortBy.value = 'name'
}

const addToCart = (product) => {
  if (product.stock > 0) {
    console.log('Added to cart:', product)
    // In real app, this would dispatch to Vuex store
  }
}
</script>