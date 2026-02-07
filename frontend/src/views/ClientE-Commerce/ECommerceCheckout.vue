<template>
  <div class="min-h-screen bg-gradient-to-b">
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-6">
        <div class="flex flex-col sm:flex-row items-center justify-between">
          <div class="text-center sm:text-left mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-600 mt-1">Complete your purchase</p>
          </div>
          <router-link to="/ecommerce/cart">
            <Button variant="link" class="text-blue-600 hover:text-blue-800 p-0 h-auto">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              Back to Cart
            </Button>
          </router-link>
        </div>
      </div>
    </div>

    <div class="bg-white border-b border-gray-200">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-center">
          <div class="flex items-center space-x-2 sm:space-x-8">
            <div class="flex items-center">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <div class="ml-2 hidden sm:block">
                <span class="text-sm font-medium text-gray-900">Cart</span>
              </div>
            </div>

            <div class="h-0.5 w-8 sm:w-16 bg-green-500"></div>
            
            <div class="flex items-center">
              <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center shrink-0">
                <span class="text-white font-semibold">2</span>
              </div>
              <div class="ml-2 hidden sm:block">
                <span class="text-sm font-semibold text-blue-600">Checkout</span>
              </div>
            </div>

            <div class="h-0.5 w-8 sm:w-16 bg-gray-300"></div>
            
            <div class="flex items-center">
              <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center shrink-0">
                <span class="text-gray-600 font-semibold">3</span>
              </div>
              <div class="ml-2 hidden sm:block">
                <span class="text-sm font-medium text-gray-500">Confirm</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-8">
          <Card class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
            <CardHeader class="p-6 border-b border-gray-200 bg-white">
              <CardTitle class="text-xl font-bold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Shipping Information
              </CardTitle>
            </CardHeader>
            
            <CardContent class="p-6 bg-white">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="mb-2 block">First Name</Label>
                  <Input v-model="shippingInfo.firstName" required class="w-full" />
                </div>
                
                <div>
                  <Label class="mb-2 block">Last Name</Label>
                  <Input v-model="shippingInfo.lastName" required class="w-full" />
                </div>
                
                <div class="md:col-span-2">
                  <Label class="mb-2 block">Email Address</Label>
                  <Input v-model="shippingInfo.email" type="email" required class="w-full" />
                </div>
                
                <div class="md:col-span-2">
                  <Label class="mb-2 block">Phone Number</Label>
                  <Input v-model="shippingInfo.phone" type="tel" placeholder="+63 912 345 6789" required class="w-full" />
                </div>
                
                <div class="md:col-span-2">
                  <Label class="mb-2 block">Street Address</Label>
                  <Input v-model="shippingInfo.address" placeholder="Street name, building, house number" required class="w-full" />
                </div>
                
                <div>
                  <Label class="mb-2 block">City/Municipality</Label>
                  <Select v-model="shippingInfo.city">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="Select City" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="bacoor">Bacoor</SelectItem>
                      <SelectItem value="imus">Imus</SelectItem>
                      <SelectItem value="dasmarinas">Dasmarinas</SelectItem>
                      <SelectItem value="tanza">Tanza</SelectItem>
                      <SelectItem value="trece-martires">Trece Martires</SelectItem>
                      <SelectItem value="general-trias">General Trias</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                
                <div>
                  <Label class="mb-2 block">Barangay</Label>
                  <Input v-model="shippingInfo.barangay" required class="w-full" />
                </div>
                
                <div>
                  <Label class="mb-2 block">ZIP Code</Label>
                  <Input v-model="shippingInfo.zipCode" required class="w-full" />
                </div>
                
                <div class="md:col-span-2">
                  <Label class="mb-2 block">Special Instructions (Optional)</Label>
                  <Textarea
                    v-model="shippingInfo.instructions"
                    placeholder="Delivery notes, gate codes, etc."
                    class="resize-none w-full"
                    rows="3"
                  />
                </div>
              </div>
              
              <div class="mt-6 flex items-center space-x-2">
                <Checkbox id="saveAddress" :checked="shippingInfo.saveAddress" @update:checked="val => shippingInfo.saveAddress = val" />
                <Label for="saveAddress" class="text-sm text-gray-700 font-normal cursor-pointer">
                  Save this address for future purchases
                </Label>
              </div>
            </CardContent>
          </Card>

          <Card class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
            <CardHeader class="p-6 border-b border-gray-200 bg-white">
              <CardTitle class="text-xl font-bold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Payment Method
              </CardTitle>
            </CardHeader>
            
            <CardContent class="p-6 bg-white">
              <div class="space-y-4">
                <div
                  @click="paymentMethod = 'cod'"
                  :class="[
                    'p-4 border rounded-lg cursor-pointer transition-all',
                    paymentMethod === 'cod'
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-300 hover:border-blue-400'
                  ]"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3 bg-white shrink-0">
                        <div v-if="paymentMethod === 'cod'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                      </div>
                      <div>
                        <h3 class="font-medium text-gray-900">Cash on Delivery</h3>
                        <p class="text-sm text-gray-600">Pay when you receive the items</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded whitespace-nowrap">Available</span>
                    </div>
                  </div>
                </div>

                <div
                  @click="paymentMethod = 'gcash'"
                  :class="[
                    'p-4 border rounded-lg cursor-pointer transition-all',
                    paymentMethod === 'gcash'
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-300 hover:border-blue-400'
                  ]"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3 bg-white shrink-0">
                        <div v-if="paymentMethod === 'gcash'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                      </div>
                      <div>
                        <h3 class="font-medium text-gray-900">GCash</h3>
                        <p class="text-sm text-gray-600">Pay via GCash mobile app</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded whitespace-nowrap">Recommended</span>
                    </div>
                  </div>
                  
                  <div v-if="paymentMethod === 'gcash'" class="mt-4 pt-4 border-t border-gray-200">
                    <div class="bg-blue-50 rounded-lg p-4">
                      <div class="flex items-center mb-3">
                        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium text-gray-900">GCash Payment Instructions</span>
                      </div>
                      <ol class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start">
                          <span class="font-semibold mr-2">1.</span>
                          <span>Open GCash app and go to "Pay QR"</span>
                        </li>
                        <li class="flex items-start">
                          <span class="font-semibold mr-2">2.</span>
                          <span>Scan the QR code below or enter our GCash number: <span class="font-semibold">0912 345 6789</span></span>
                        </li>
                        <li class="flex items-start">
                          <span class="font-semibold mr-2">3.</span>
                          <span>Enter amount: <span class="font-semibold">₱{{ totalAmount.toLocaleString() }}</span></span>
                        </li>
                        <li class="flex items-start">
                          <span class="font-semibold mr-2">4.</span>
                          <span>Add reference: <span class="font-semibold">PAINT-{{ orderId }}</span></span>
                        </li>
                      </ol>
                      <div class="mt-4 flex justify-center">
                        <div class="w-48 h-48 bg-white p-4 rounded-lg shadow-sm">
                          <div class="w-full h-full bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center rounded">
                            <div class="text-center">
                              <div class="w-32 h-32 mx-auto mb-2 bg-white rounded"></div>
                              <p class="text-xs text-gray-600">Scan to pay with GCash</p>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  @click="paymentMethod = 'card'"
                  :class="[
                    'p-4 border rounded-lg cursor-pointer transition-all',
                    paymentMethod === 'card'
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-300 hover:border-blue-400'
                  ]"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3 bg-white shrink-0">
                        <div v-if="paymentMethod === 'card'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                      </div>
                      <div>
                        <h3 class="font-medium text-gray-900">Credit/Debit Card</h3>
                        <p class="text-sm text-gray-600">Pay with Visa, Mastercard, or JCB</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <svg class="w-8 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 2a8 8 0 100 16 8 8 0 000-16z"></path>
                      </svg>
                      <svg class="w-8 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 2a8 8 0 100 16 8 8 0 000-16z"></path>
                      </svg>
                    </div>
                  </div>
                  
                  <div v-if="paymentMethod === 'card'" class="mt-4 pt-4 border-t border-gray-200">
                    <div class="space-y-4">
                      <div>
                        <Label class="mb-2 block">Card Number</Label>
                        <Input v-model="cardInfo.number" placeholder="1234 5678 9012 3456" class="w-full" />
                      </div>
                      
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <Label class="mb-2 block">Expiry Date</Label>
                          <Input v-model="cardInfo.expiry" placeholder="MM/YY" class="w-full" />
                        </div>
                        
                        <div>
                          <Label class="mb-2 block">CVV</Label>
                          <Input v-model="cardInfo.cvv" placeholder="123" class="w-full" />
                        </div>
                      </div>
                      
                      <div>
                        <Label class="mb-2 block">Cardholder Name</Label>
                        <Input v-model="cardInfo.name" placeholder="As shown on card" class="w-full" />
                      </div>
                      
                      <div class="flex items-center space-x-2">
                        <Checkbox id="saveCard" :checked="cardInfo.saveCard" @update:checked="val => cardInfo.saveCard = val" />
                        <Label for="saveCard" class="text-sm text-gray-700 font-normal cursor-pointer">
                          Save card for future purchases
                        </Label>
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  @click="paymentMethod = 'bank'"
                  :class="[
                    'p-4 border rounded-lg cursor-pointer transition-all',
                    paymentMethod === 'bank'
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-300 hover:border-blue-400'
                  ]"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center">
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3 bg-white shrink-0">
                        <div v-if="paymentMethod === 'bank'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                      </div>
                      <div>
                        <h3 class="font-medium text-gray-900">Bank Transfer</h3>
                        <p class="text-sm text-gray-600">Pay via bank deposit or transfer</p>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="paymentMethod === 'bank'" class="mt-4 pt-4 border-t border-gray-200">
                    <div class="bg-gray-50 rounded-lg p-4">
                      <div class="space-y-3">
                        <div>
                          <span class="text-sm text-gray-500">Bank Name</span>
                          <p class="font-medium text-gray-900">Bank of the Philippine Islands (BPI)</p>
                        </div>
                        <div>
                          <span class="text-sm text-gray-500">Account Name</span>
                          <p class="font-medium text-gray-900">CaviteGo Paint Enterprises</p>
                        </div>
                        <div>
                          <span class="text-sm text-gray-500">Account Number</span>
                          <p class="font-medium text-gray-900">1234-5678-90</p>
                        </div>
                        <div>
                          <span class="text-sm text-gray-500">Reference Code</span>
                          <p class="font-medium text-gray-900">PAINT-{{ orderId }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <div class="lg:col-span-1">
          <div class="sticky top-24">
            <Card class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
              <CardHeader class="p-6 border-b border-gray-200 bg-white">
                <CardTitle class="text-xl font-bold text-gray-900">Order Summary</CardTitle>
              </CardHeader>
              
              <ScrollArea class="h-96">
                <div class="p-4 space-y-4">
                   <div v-for="item in orderItems" :key="item.id" class="flex items-start pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center mr-3 shrink-0">
                      <div v-if="item.type === 'product'" class="w-8 h-8 rounded-full" :style="{ backgroundColor: item.color }"></div>
                      <svg v-else class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                      </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-medium text-gray-900 truncate">{{ item.name }}</h4>
                      <div class="flex justify-between items-center mt-1">
                        <p class="text-xs text-gray-500">
                          <span v-if="item.type === 'product'">Qty: {{ item.quantity }}</span>
                          <span v-else>{{ item.date }}</span>
                        </p>
                        <p class="text-sm font-medium text-gray-900">
                          ₱{{ (item.price * (item.quantity || 1)).toLocaleString() }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              </ScrollArea>
              
              <CardContent class="p-6 bg-white border-t border-gray-100">
                <div class="space-y-3">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium text-gray-900">₱{{ subtotal.toLocaleString() }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-gray-600">Services</span>
                    <span class="font-medium text-gray-900">₱{{ servicesTotal.toLocaleString() }}</span>
                  </div>
                  
                  <div class="flex justify-between">
                    <span class="text-gray-600">Delivery Fee</span>
                    <span class="font-medium text-gray-900">₱{{ deliveryFee.toLocaleString() }}</span>
                  </div>
                  
                  <div v-if="paymentMethod === 'cod'" class="flex justify-between">
                    <span class="text-gray-600">COD Fee</span>
                    <span class="font-medium text-gray-900">₱{{ codFee.toLocaleString() }}</span>
                  </div>
                  
                  <Separator class="my-3" />
                  
                  <div>
                    <div class="flex justify-between">
                      <span class="text-lg font-semibold text-gray-900">Total</span>
                      <span class="text-2xl font-bold text-gray-900">₱{{ totalAmount.toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                  <div class="flex items-start space-x-2">
                    <Checkbox id="acceptTerms" :checked="acceptTerms" @update:checked="val => acceptTerms = val" class="mt-1" />
                    <Label for="acceptTerms" class="text-sm text-gray-700 font-normal leading-normal">
                      I agree to the 
                      <a href="#" class="text-blue-600 hover:text-blue-800">Terms of Service</a>
                      and 
                      <a href="#" class="text-blue-600 hover:text-blue-800">Privacy Policy</a>
                    </Label>
                  </div>
                  
                  <div class="mt-4">
                    <Button
                      @click="placeOrder"
                      :disabled="!acceptTerms || !isFormValid"
                      class="w-full h-14 rounded-lg font-semibold text-lg bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white shadow-md hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                      Place Order
                    </Button>
                    
                    <p class="text-xs text-gray-500 text-center mt-3">
                      By placing your order, you agree to our terms
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
            
            <Card class="mt-4 bg-blue-50 border-0 rounded-2xl">
              <CardContent class="p-4">
                <div class="flex items-start">
                  <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                  </svg>
                  <div>
                    <h4 class="font-medium text-gray-900">Need help with checkout?</h4>
                    <p class="text-sm text-gray-600 mt-1">Call us at <span class="font-medium">+63 912 345 6789</span></p>
                    <p class="text-xs text-gray-500 mt-1">or email <span class="font-medium">support@cavitegopaint.com</span></p>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Separator } from '@/components/ui/separator'
import { ScrollArea } from '@/components/ui/scroll-area'

const router = useRouter()

// Order Data
const orderId = ref(`ORD${Math.floor(Math.random() * 1000000).toString().padStart(6, '0')}`)
const orderItems = ref([])

// Shipping Information
const shippingInfo = ref({
  firstName: 'Juan',
  lastName: 'Dela Cruz',
  email: 'juan.delacruz@email.com',
  phone: '+63 912 345 6789',
  address: '123 Main Street',
  city: 'dasmarinas',
  barangay: 'San Miguel',
  zipCode: '4114',
  instructions: '',
  saveAddress: true
})

// Payment Method
const paymentMethod = ref('cod')
const acceptTerms = ref(false)

// Card Information
const cardInfo = ref({
  number: '',
  expiry: '',
  cvv: '',
  name: '',
  saveCard: false
})

// Load cart data from localStorage
onMounted(() => {
  const cartData = localStorage.getItem('cartCheckoutData')
  if (cartData) {
    const parsedData = JSON.parse(cartData)
    orderItems.value = parsedData.items || []
    
    // Set computed values
    subtotal.value = parsedData.subtotal || 0
    servicesTotal.value = parsedData.servicesTotal || 0
    deliveryFee.value = parsedData.deliveryFee || 0
    totalAmount.value = parsedData.totalAmount || 0
  }
})

// Computed Values
const subtotal = ref(0)
const servicesTotal = ref(0)
const deliveryFee = ref(0)
const codFee = computed(() => paymentMethod.value === 'cod' ? 50 : 0)
const totalAmount = computed(() => subtotal.value + servicesTotal.value + deliveryFee.value + codFee.value)

const isFormValid = computed(() => {
  const { firstName, lastName, email, phone, address, city, barangay, zipCode } = shippingInfo.value
  return firstName && lastName && email && phone && address && city && barangay && zipCode
})

// Methods
const placeOrder = () => {
  if (!acceptTerms.value) {
    alert('Please accept the terms and conditions')
    return
  }
  
  if (!isFormValid.value) {
    alert('Please fill in all required shipping information')
    return
  }
  
  const orderData = {
    orderId: orderId.value,
    items: orderItems.value,
    shipping: shippingInfo.value,
    payment: {
      method: paymentMethod.value,
      details: paymentMethod.value === 'card' ? cardInfo.value : null
    },
    totalAmount: totalAmount.value,
    date: new Date().toISOString()
  }
  
  console.log('Order placed:', orderData)
  
  // Clear cart data
  localStorage.removeItem('cartCheckoutData')
  
  // Store order data for confirmation page
  localStorage.setItem('lastOrder', JSON.stringify(orderData))
  
  // Redirect to confirmation page (or orders page)
  router.push('/ecommerce/orders')
  
  // In real app, this would send data to backend
  alert(`Order #${orderId.value} placed successfully!`)
}
</script>

<style scoped>
/* Smooth transitions for payment method selection */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>