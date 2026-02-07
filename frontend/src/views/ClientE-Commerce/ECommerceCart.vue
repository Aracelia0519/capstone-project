<template>
  <div class="min-h-screen bg-gradient-to-b">
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Shopping Cart</h1>
        <p class="text-gray-600 mt-2">Review your selected items</p>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <Card v-if="cartItems.length === 0" class="p-8 text-center border-gray-200 shadow-lg rounded-2xl">
            <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
              <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Your cart is empty</h3>
            <p class="text-gray-500 mb-6">Add some paint products or services to get started</p>
            <router-link to="/ecommerce/shop">
              <Button class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold h-12 px-6">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Start Shopping
              </Button>
            </router-link>
          </Card>

          <div v-else class="space-y-6">
            <Card v-if="productItems.length > 0" class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
              <CardHeader class="p-4 md:p-6 border-b border-gray-200 bg-white">
                <CardTitle class="text-xl font-bold text-gray-900 flex items-center">
                  <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                  </svg>
                  Paint Products
                </CardTitle>
              </CardHeader>
              
              <CardContent class="p-0">
                <div class="divide-y divide-gray-100">
                  <div
                    v-for="item in productItems"
                    :key="item.id"
                    class="p-4 md:p-6 hover:bg-gray-50 transition-colors bg-white"
                  >
                    <div class="flex flex-col sm:flex-row items-start">
                      <div class="w-full sm:w-24 h-48 sm:h-24 rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center mb-4 sm:mb-0 sm:mr-4 shrink-0">
                        <div class="w-24 h-24 sm:w-16 sm:h-16 rounded-full" :style="{ backgroundColor: item.color }"></div>
                      </div>
                      
                      <div class="flex-1 w-full">
                        <div class="flex flex-col sm:flex-row justify-between">
                          <div class="mb-4 sm:mb-0">
                            <h3 class="font-semibold text-gray-900 text-lg sm:text-base">{{ item.name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ item.brand }} • {{ item.type }} • {{ item.finish }}</p>
                            <div class="flex items-center mt-2">
                              <span class="text-sm font-medium text-gray-900">₱{{ item.price.toLocaleString() }}</span>
                              <span class="mx-2 text-gray-300">•</span>
                              <span class="text-sm text-gray-500">per {{ item.unit }}</span>
                            </div>
                          </div>
                          
                          <div class="flex flex-row sm:flex-col justify-between items-center sm:items-end sm:text-right">
                            <div class="text-lg font-bold text-gray-900">₱{{ (item.price * item.quantity).toLocaleString() }}</div>
                            
                            <div class="flex items-center mt-0 sm:mt-3">
                              <div class="flex items-center border border-gray-300 rounded-lg bg-white">
                                <Button
                                  variant="ghost"
                                  size="icon"
                                  @click="updateQuantity(item.id, item.quantity - 1)"
                                  class="h-8 w-8 rounded-none text-gray-600 hover:text-gray-900"
                                  :disabled="item.quantity <= 1"
                                >
                                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                  </svg>
                                </Button>
                                <span class="px-3 py-1 text-gray-900 font-medium min-w-[2rem] text-center">{{ item.quantity }}</span>
                                <Button
                                  variant="ghost"
                                  size="icon"
                                  @click="updateQuantity(item.id, item.quantity + 1)"
                                  class="h-8 w-8 rounded-none text-gray-600 hover:text-gray-900"
                                  :disabled="item.quantity >= 10"
                                >
                                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                  </svg>
                                </Button>
                              </div>
                              
                              <Button
                                variant="ghost"
                                size="icon"
                                @click="removeItem(item.id)"
                                class="ml-4 text-gray-400 hover:text-red-500 hover:bg-red-50"
                              >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                              </Button>
                            </div>
                          </div>
                        </div>
                        
                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                          <div class="flex items-center">
                            <svg :class="[
                              'w-4 h-4 mr-2',
                              item.stock > 10 ? 'text-green-500' : 'text-yellow-500'
                            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-sm" :class="item.stock > 10 ? 'text-green-600' : 'text-yellow-600'">
                              {{ item.stock > 10 ? `${item.stock} available` : `Only ${item.stock} left` }}
                            </span>
                          </div>
                          <div class="text-sm text-gray-500 hidden sm:block">
                            Subtotal: <span class="font-semibold text-gray-900">₱{{ (item.price * item.quantity).toLocaleString() }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card v-if="serviceItems.length > 0" class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
              <CardHeader class="p-4 md:p-6 border-b border-gray-200 bg-white">
                <CardTitle class="text-xl font-bold text-gray-900 flex items-center">
                  <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                  </svg>
                  Services
                </CardTitle>
              </CardHeader>
              
              <CardContent class="p-0">
                <div class="divide-y divide-gray-100">
                  <div
                    v-for="item in serviceItems"
                    :key="item.id"
                    class="p-4 md:p-6 hover:bg-gray-50 transition-colors bg-white"
                  >
                    <div class="flex flex-col sm:flex-row items-start">
                      <div class="w-full sm:w-16 h-24 sm:h-16 rounded-lg bg-gradient-to-br from-purple-50 to-pink-50 flex items-center justify-center mb-4 sm:mb-0 sm:mr-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                        </svg>
                      </div>
                      
                      <div class="flex-1 w-full">
                        <div class="flex flex-col sm:flex-row justify-between">
                          <div class="mb-2 sm:mb-0">
                            <h3 class="font-semibold text-gray-900">{{ item.name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ item.description }}</p>
                            <div class="flex flex-wrap items-center mt-2 gap-4">
                              <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ item.duration }}
                              </div>
                              <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ item.scheduledDate }}
                              </div>
                            </div>
                          </div>
                          
                          <div class="flex flex-row sm:flex-col justify-between items-center sm:items-end sm:text-right mt-2 sm:mt-0">
                            <div class="text-lg font-bold text-gray-900">₱{{ item.price.toLocaleString() }}</div>
                            
                            <div class="mt-0 sm:mt-3">
                              <Button
                                variant="ghost"
                                size="sm"
                                @click="removeItem(item.id)"
                                class="text-red-600 hover:text-red-800 hover:bg-red-50 p-0 sm:p-2 h-auto sm:h-9"
                              >
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="sm:inline">Remove</span>
                              </Button>
                            </div>
                          </div>
                        </div>
                        
                        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                              <span class="text-xs text-gray-500">Location</span>
                              <p class="text-sm text-gray-900 break-words">{{ item.location }}</p>
                            </div>
                            <div>
                              <span class="text-xs text-gray-500">Contact</span>
                              <p class="text-sm text-gray-900">{{ item.contact }}</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-4">
              <router-link to="/ecommerce/shop" class="w-full sm:w-auto">
                <Button variant="link" class="text-blue-600 hover:text-blue-800 p-0 h-auto w-full sm:w-auto justify-center sm:justify-start">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                  </svg>
                  Continue Shopping
                </Button>
              </router-link>
              
              <Button
                variant="ghost"
                @click="clearCart"
                class="text-red-600 hover:text-red-800 hover:bg-red-50 w-full sm:w-auto"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Clear Cart
              </Button>
            </div>
          </div>
        </div>

        <div class="lg:col-span-1">
          <div class="sticky top-24">
            <Card class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
              <CardHeader class="p-6 border-b border-gray-200 bg-white">
                <CardTitle class="text-xl font-bold text-gray-900">Order Summary</CardTitle>
              </CardHeader>
              
              <CardContent class="p-6 bg-white">
                <div class="space-y-4">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal ({{ totalItems }} items)</span>
                    <span class="font-medium text-gray-900">₱{{ subtotal.toLocaleString() }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-gray-600">Services Total</span>
                    <span class="font-medium text-gray-900">₱{{ servicesTotal.toLocaleString() }}</span>
                  </div>
                  
                  <div v-if="deliveryFee > 0" class="flex justify-between">
                    <span class="text-gray-600">Delivery Fee</span>
                    <span class="font-medium text-gray-900">₱{{ deliveryFee.toLocaleString() }}</span>
                  </div>
                  
                  <Separator class="my-4" />
                  
                  <div>
                    <div class="flex justify-between">
                      <span class="text-lg font-semibold text-gray-900">Total</span>
                      <span class="text-2xl font-bold text-gray-900">₱{{ totalAmount.toLocaleString() }}</span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">Inclusive of VAT</p>
                  </div>
                </div>
                
                <Button
                  @click="proceedToCheckout"
                  :disabled="cartItems.length === 0"
                  class="w-full h-14 mt-6 rounded-lg font-semibold text-lg bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white shadow-md hover:shadow-xl transition-all duration-300"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                  </svg>
                  Proceed to Checkout
                </Button>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                  <h3 class="text-sm font-medium text-gray-700 mb-3">We Accept</h3>
                  <div class="grid grid-cols-3 gap-2">
                    <div class="h-10 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                      <span class="text-xs font-semibold text-gray-700">COD</span>
                    </div>
                    <div class="h-10 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                      <span class="text-xs font-semibold text-gray-700">GCash</span>
                    </div>
                    <div class="h-10 bg-gray-100 rounded-lg flex items-center justify-center border border-gray-200">
                      <span class="text-xs font-semibold text-gray-700">Card</span>
                    </div>
                  </div>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-200">
                  <div class="flex items-center text-sm text-gray-600">
                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <span>Secure SSL Encryption</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">Your payment information is securely processed</p>
                </div>
              </CardContent>
            </Card>
            
            <Card class="mt-4 bg-blue-50 border-0 rounded-2xl">
              <CardContent class="p-4">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <div>
                    <h4 class="font-medium text-gray-900">Need help?</h4>
                    <p class="text-sm text-gray-600 mt-1">Call us at <span class="font-medium">+63 912 345 6789</span></p>
                    <p class="text-xs text-gray-500 mt-1">Available 8AM-8PM daily</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'

const router = useRouter()

// Mock Cart Data
const cartItems = ref([
  {
    id: 1,
    type: 'product',
    name: 'Premium Interior White',
    brand: 'CaviteGo',
    typeDesc: 'Interior',
    finish: 'Matte',
    price: 1250,
    quantity: 2,
    unit: 'gallon',
    stock: 45,
    color: '#ffffff'
  },
  {
    id: 2,
    type: 'product',
    name: 'WeatherGuard Exterior',
    brand: 'CaviteGo',
    typeDesc: 'Exterior',
    finish: 'Gloss',
    price: 1850,
    quantity: 1,
    unit: 'gallon',
    stock: 8,
    color: '#4a90e2'
  },
  {
    id: 3,
    type: 'service',
    name: 'Interior Painting Service',
    description: 'Living room painting - 20 sqm',
    price: 5000,
    duration: '3-5 days',
    scheduledDate: 'Dec 15, 2024',
    location: '123 Main St, Cavite',
    contact: '+63 912 345 6789',
    icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
  },
  {
    id: 4,
    type: 'service',
    name: 'Color Consultation',
    description: 'House color scheme planning',
    price: 2500,
    duration: '2 hours',
    scheduledDate: 'Dec 20, 2024',
    location: '123 Main St, Cavite',
    contact: '+63 912 345 6789',
    icon: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
  }
])

// Computed Values
const productItems = computed(() => cartItems.value.filter(item => item.type === 'product'))
const serviceItems = computed(() => cartItems.value.filter(item => item.type === 'service'))
const totalItems = computed(() => cartItems.value.reduce((sum, item) => sum + (item.quantity || 1), 0))
const subtotal = computed(() => productItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0))
const servicesTotal = computed(() => serviceItems.value.reduce((sum, item) => sum + item.price, 0))
const deliveryFee = computed(() => {
  const totalProducts = productItems.value.reduce((sum, item) => sum + item.quantity, 0)
  return totalProducts >= 5 ? 0 : 250 // Free delivery for 5+ gallons
})
const totalAmount = computed(() => subtotal.value + servicesTotal.value + deliveryFee.value)

// Methods
const updateQuantity = (itemId, newQuantity) => {
  if (newQuantity < 1) newQuantity = 1
  if (newQuantity > 10) newQuantity = 10
  
  const item = cartItems.value.find(item => item.id === itemId)
  if (item && item.type === 'product') {
    item.quantity = newQuantity
  }
}

const removeItem = (itemId) => {
  cartItems.value = cartItems.value.filter(item => item.id !== itemId)
}

const clearCart = () => {
  if (confirm('Are you sure you want to clear your cart?')) {
    cartItems.value = []
  }
}

const proceedToCheckout = () => {
  if (cartItems.value.length === 0) {
    alert('Your cart is empty. Add some items to proceed.')
    return
  }
  
  // Store cart data for checkout page
  localStorage.setItem('cartCheckoutData', JSON.stringify({
    items: cartItems.value,
    subtotal: subtotal.value,
    servicesTotal: servicesTotal.value,
    deliveryFee: deliveryFee.value,
    totalAmount: totalAmount.value
  }))
  
  router.push('/EcommerceClient/EccommerceCheckOut')
}
</script>

<style scoped>
/* Smooth transitions for hover effects */
.hover\:shadow-xl {
  transition: box-shadow 0.3s ease;
}

/* Custom scrollbar for cart items */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>