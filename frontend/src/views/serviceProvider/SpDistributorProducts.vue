<template>
  <div class="min-h-screen font-sans bg-[#0f172a] text-slate-200 p-4 sm:p-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
          <div class="p-2.5 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg shadow-indigo-500/20 shrink-0">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
          </div>
          <span>Products from <span class="text-indigo-400">{{ distributorName }}</span></span>
        </h1>
        <p class="text-slate-400 mt-1 text-sm">Browse all available products and their details.</p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="goBack" class="bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
          Back
        </Button>
        <Button size="sm" @click="fetchProducts" class="bg-indigo-600 hover:bg-indigo-500 text-white">
          <RefreshCw :class="{ 'animate-spin': loading }" class="w-4 h-4 mr-2" /> Refresh
        </Button>
      </div>
    </div>

    <!-- Search Bar -->
    <div class="relative max-w-md mb-6">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <Search class="h-4 w-4 text-slate-500" />
      </div>
      <Input v-model="searchQuery" placeholder="Search products by name, category..." class="pl-10 bg-slate-900/50 border-slate-700/60 text-white placeholder:text-slate-500 focus-visible:ring-indigo-500" />
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <Loader2 class="w-10 h-10 animate-spin text-indigo-500" />
      <p class="mt-4 text-slate-400">Loading products...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredProducts.length === 0" class="flex flex-col items-center justify-center py-20 border-2 border-dashed border-slate-700 rounded-2xl bg-slate-800/20 text-center">
      <Package class="w-16 h-16 text-slate-600 mb-4" />
      <h3 class="text-xl font-bold text-white">No Products Found</h3>
      <p class="text-slate-400 max-w-md mt-2">
        {{ searchQuery ? `No products match "${searchQuery}". Try a different search.` : 'This distributor has no products available yet.' }}
      </p>
      <Button v-if="searchQuery" variant="outline" @click="searchQuery = ''" class="mt-4 border-slate-600 text-slate-300 hover:bg-slate-800">Clear Search</Button>
    </div>

    <!-- Product Grid (Read-Only) -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <Card v-for="product in filteredProducts" :key="product.id" class="bg-slate-800/40 border-slate-700/60 transition-all duration-300 overflow-hidden">
        <div class="relative h-48 bg-slate-900/50 flex items-center justify-center p-4">
          <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="h-full w-full object-contain" />
          <div v-else class="text-slate-500 flex flex-col items-center">
            <Package class="w-12 h-12" />
            <span class="text-xs mt-1">No image</span>
          </div>
          <!-- Only show "in stock" badge when stock > 0 -->
          <Badge v-if="product.inventory_quantity !== undefined && product.inventory_quantity > 0" class="absolute top-2 right-2 bg-emerald-500/80 text-white border-emerald-400">
            {{ product.inventory_quantity }} in stock
          </Badge>
          <!-- "Out of stock" badge removed -->
        </div>
        <CardContent class="p-4 space-y-2">
          <div class="flex justify-between items-start">
            <h3 class="font-semibold text-white truncate">{{ product.name }}</h3>
            <span class="text-sm font-bold text-indigo-400">₱{{ product.price }}</span>
          </div>
          <div class="flex flex-wrap gap-1 text-xs text-slate-400">
            <Badge variant="outline" class="border-slate-600 text-slate-300">{{ product.category }}</Badge>
            <Badge variant="outline" class="border-slate-600 text-slate-300">{{ product.type }}</Badge>
            <Badge variant="outline" class="border-slate-600 text-slate-300">{{ product.size }}</Badge>
          </div>
          <p v-if="product.description" class="text-sm text-slate-400 line-clamp-2">{{ product.description }}</p>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Loader2, RefreshCw, Search, Package } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const distributorId = route.params.id

const distributorName = ref('Distributor')
const products = ref([])
const loading = ref(true)
const searchQuery = ref('')

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value
  const q = searchQuery.value.toLowerCase()
  return products.value.filter(p => 
    p.name?.toLowerCase().includes(q) ||
    p.category?.toLowerCase().includes(q) ||
    p.type?.toLowerCase().includes(q) ||
    p.description?.toLowerCase().includes(q)
  )
})

const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await api.get(`/service-provider/shop/products/${distributorId}`)
    if (response.data.success) {
      products.value = response.data.data
      if (products.value.length > 0) {
        distributorName.value = products.value[0].distributor_name || 'Distributor'
      }
    }
  } catch (error) {
    toast.error('Failed to load products', { description: error.response?.data?.message || 'Please try again later.' })
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.push('/serviceProvider/Distributors')
}

onMounted(() => {
  fetchProducts()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>