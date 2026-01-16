<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
      <div class="container mx-auto px-4 py-4">
        <nav class="flex items-center space-x-2 text-sm">
          <router-link to="/ecommerce/shop" class="text-blue-600 hover:text-blue-800 transition-colors">Shop</router-link>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
          <span class="text-gray-600">{{ product.category }}</span>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
          <span class="text-gray-900 font-medium">{{ product.name }}</span>
        </nav>
      </div>
    </div>

    <!-- Product Detail -->
    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Images -->
        <div>
          <!-- Main Image -->
          <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl h-96 mb-4 flex items-center justify-center relative">
            <div class="w-64 h-64 rounded-full shadow-lg" :style="{ backgroundColor: product.color }"></div>
            <div class="absolute top-4 right-4">
              <button class="p-2 bg-white rounded-full shadow-md hover:shadow-lg transition-shadow">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Thumbnails -->
          <div class="grid grid-cols-4 gap-3">
            <div
              v-for="(color, index) in product.colors"
              :key="index"
              class="h-20 rounded-lg border-2 hover:border-blue-500 transition-colors cursor-pointer"
              :class="{ 'border-blue-500': selectedColor === color }"
              :style="{ backgroundColor: color }"
              @click="selectedColor = color"
            ></div>
          </div>
        </div>

        <!-- Product Info -->
        <div>
          <!-- Header -->
          <div class="mb-6">
            <div class="flex justify-between items-start">
              <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.name }}</h1>
                <div class="flex items-center space-x-4">
                  <span class="text-sm text-gray-500">Brand: {{ product.brand }}</span>
                  <span class="text-sm text-gray-500">SKU: {{ product.sku }}</span>
                </div>
              </div>
              <div class="text-right">
                <div class="text-4xl font-bold text-gray-900">₱{{ product.price.toLocaleString() }}</div>
                <div class="text-sm text-gray-500">per gallon</div>
              </div>
            </div>

            <!-- Rating -->
            <div class="flex items-center mt-4">
              <div class="flex">
                <svg v-for="star in 5" :key="star" class="w-5 h-5" :class="star <= product.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
              </div>
              <span class="ml-2 text-gray-600">{{ product.rating }} ({{ product.reviewCount }} reviews)</span>
              <span class="mx-3 text-gray-300">•</span>
              <span :class="[
                'px-2 py-1 rounded-full text-xs font-semibold',
                product.stock > 10 ? 'bg-green-100 text-green-800' : product.stock > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
              ]">
                {{ product.stock > 10 ? 'In Stock' : product.stock > 0 ? 'Low Stock' : 'Out of Stock' }}
              </span>
            </div>
          </div>

          <!-- Color Selection -->
          <div class="mb-6">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Select Color</h3>
            <div class="flex flex-wrap gap-2">
              <div
                v-for="(color, index) in product.colors"
                :key="index"
                class="w-10 h-10 rounded-full border-2 hover:border-blue-500 transition-colors cursor-pointer"
                :class="{ 'border-blue-500': selectedColor === color }"
                :style="{ backgroundColor: color }"
                @click="selectedColor = color"
              ></div>
            </div>
          </div>

          <!-- Quantity & Add to Cart -->
          <div class="mb-6">
            <div class="flex items-center space-x-4">
              <div>
                <label class="text-sm font-medium text-gray-700 mb-2 block">Quantity</label>
                <div class="flex items-center border border-gray-300 rounded-lg">
                  <button
                    @click="quantity > 1 ? quantity-- : null"
                    class="px-4 py-2 text-gray-600 hover:text-gray-900 disabled:text-gray-300"
                    :disabled="quantity === 1"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                  </button>
                  <span class="px-4 py-2 text-lg font-medium">{{ quantity }}</span>
                  <button
                    @click="quantity < product.stock ? quantity++ : null"
                    class="px-4 py-2 text-gray-600 hover:text-gray-900 disabled:text-gray-300"
                    :disabled="quantity >= product.stock"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ product.stock }} available</p>
              </div>

              <div class="flex-1">
                <button
                  @click="addToCart"
                  :disabled="product.stock === 0"
                  :class="[
                    'w-full py-3 rounded-lg font-semibold transition-all flex items-center justify-center space-x-2',
                    product.stock === 0
                      ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                      : 'bg-blue-600 text-white hover:bg-blue-700'
                  ]"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  <span>{{ product.stock === 0 ? 'Out of Stock' : 'Add to Cart' }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Specifications -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Specifications</h3>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <span class="text-sm text-gray-500">Type</span>
                <p class="font-medium">{{ product.type }}</p>
              </div>
              <div>
                <span class="text-sm text-gray-500">Finish</span>
                <p class="font-medium">{{ product.finish }}</p>
              </div>
              <div>
                <span class="text-sm text-gray-500">Coverage</span>
                <p class="font-medium">{{ product.coverage }} sqm/gallon</p>
              </div>
              <div>
                <span class="text-sm text-gray-500">Drying Time</span>
                <p class="font-medium">{{ product.dryingTime }}</p>
              </div>
              <div>
                <span class="text-sm text-gray-500">VOC Content</span>
                <p class="font-medium">{{ product.voc }}</p>
              </div>
              <div>
                <span class="text-sm text-gray-500">Warranty</span>
                <p class="font-medium">{{ product.warranty }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mt-12">
        <div class="border-b border-gray-200">
          <nav class="flex space-x-8">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="py-8">
          <!-- Description -->
          <div v-if="activeTab === 'description'" class="prose max-w-none">
            <p class="text-gray-600">{{ product.description }}</p>
            <ul class="mt-4 space-y-2">
              <li v-for="(feature, index) in product.features" :key="index" class="flex items-start">
                <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ feature }}
              </li>
            </ul>
          </div>

          <!-- How to Use -->
          <div v-else-if="activeTab === 'how-to-use'" class="space-y-4">
            <div v-for="(step, index) in product.usageSteps" :key="index" class="flex items-start">
              <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-semibold mr-4">
                {{ index + 1 }}
              </div>
              <div>
                <h4 class="font-medium text-gray-900">{{ step.title }}</h4>
                <p class="text-gray-600 mt-1">{{ step.description }}</p>
              </div>
            </div>
          </div>

          <!-- Reviews -->
          <div v-else-if="activeTab === 'reviews'" class="space-y-6">
            <div v-for="review in product.reviews" :key="review.id" class="border-b border-gray-100 pb-6">
              <div class="flex justify-between items-start mb-2">
                <div>
                  <h4 class="font-medium text-gray-900">{{ review.user }}</h4>
                  <div class="flex items-center mt-1">
                    <div class="flex">
                      <svg v-for="star in 5" :key="star" class="w-4 h-4" :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                      </svg>
                    </div>
                    <span class="ml-2 text-sm text-gray-500">{{ review.date }}</span>
                  </div>
                </div>
              </div>
              <p class="text-gray-600">{{ review.comment }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const product = ref({
  id: 1,
  name: 'Premium Interior White Paint',
  brand: 'CaviteGo',
  sku: 'CG-PINT-WH01',
  price: 1250,
  stock: 45,
  rating: 4.5,
  reviewCount: 128,
  type: 'Interior',
  finish: 'Matte',
  coverage: '35-40',
  dryingTime: '2-4 hours',
  voc: 'Low VOC (<50 g/L)',
  warranty: '5 years',
  category: 'Interior Paints',
  color: '#ffffff',
  colors: ['#ffffff', '#f8f8f8', '#f0f0f0', '#e8e8e8'],
  description: 'Premium interior white paint with superior coverage and durability. Perfect for residential and commercial spaces.',
  features: [
    'Superior coverage and hiding power',
    'Low odor and low VOC formula',
    'Washable and scrub-resistant',
    'Mold and mildew resistant',
    'Fast drying time',
    'Easy application with roller or brush'
  ],
  usageSteps: [
    { title: 'Surface Preparation', description: 'Clean and repair surfaces. Remove old peeling paint and sand smooth.' },
    { title: 'Priming', description: 'Apply primer if needed, especially on new or repaired surfaces.' },
    { title: 'Stirring', description: 'Stir paint thoroughly before use. Do not thin unless necessary.' },
    { title: 'Application', description: 'Apply with brush, roller, or spray. Use even strokes in one direction.' },
    { title: 'Drying', description: 'Allow 2-4 hours between coats. Ensure proper ventilation.' }
  ],
  reviews: [
    { id: 1, user: 'Juan Dela Cruz', rating: 5, date: '2 weeks ago', comment: 'Excellent coverage! One coat was enough for my walls.' },
    { id: 2, user: 'Maria Santos', rating: 4, date: '1 month ago', comment: 'Good quality paint. Easy to apply and dries quickly.' },
    { id: 3, user: 'Carlos Reyes', rating: 5, date: '3 months ago', comment: 'Perfect for my living room. No strong odor!' }
  ]
})

const selectedColor = ref('#ffffff')
const quantity = ref(1)
const activeTab = ref('description')
const tabs = ref([
  { id: 'description', label: 'Description' },
  { id: 'how-to-use', label: 'How to Use' },
  { id: 'specifications', label: 'Specifications' },
  { id: 'reviews', label: 'Reviews' }
])

const addToCart = () => {
  if (product.value.stock > 0) {
    console.log('Added to cart:', {
      product: product.value.name,
      color: selectedColor.value,
      quantity: quantity.value,
      price: product.value.price * quantity.value
    })
    // In real app, this would dispatch to Vuex store
  }
}
</script>

<style scoped>
/* Custom styles for better UX */
.prose ul li {
  margin-bottom: 0.5rem;
}
</style>