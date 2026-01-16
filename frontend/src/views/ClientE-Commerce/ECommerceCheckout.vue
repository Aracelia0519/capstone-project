<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
            <p class="text-gray-600 mt-1">Complete your purchase</p>
          </div>
          <router-link
            to="/ecommerce/cart"
            class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Cart
          </router-link>
        </div>
      </div>
    </div>

    <!-- Checkout Progress -->
    <div class="bg-white border-b border-gray-200">
      <div class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-center">
          <div class="flex items-center space-x-8">
            <!-- Step 1: Cart -->
            <div class="flex items-center">
              <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <div class="ml-2">
                <span class="text-sm font-medium text-gray-900">Cart</span>
              </div>
            </div>

            <!-- Step 2: Checkout -->
            <div class="h-0.5 w-16 bg-green-500"></div>
            
            <div class="flex items-center">
              <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold">2</span>
              </div>
              <div class="ml-2">
                <span class="text-sm font-semibold text-blue-600">Checkout</span>
              </div>
            </div>

            <!-- Step 3: Confirm -->
            <div class="h-0.5 w-16 bg-gray-300"></div>
            
            <div class="flex items-center">
              <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                <span class="text-gray-600 font-semibold">3</span>
              </div>
              <div class="ml-2">
                <span class="text-sm font-medium text-gray-500">Confirm</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Checkout Content -->
    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Forms -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Shipping Information -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-bold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Shipping Information
              </h2>
            </div>
            
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- First Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                  <input
                    v-model="shippingInfo.firstName"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>
                
                <!-- Last Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                  <input
                    v-model="shippingInfo.lastName"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>
                
                <!-- Email -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                  <input
                    v-model="shippingInfo.email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>
                
                <!-- Phone -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                  <input
                    v-model="shippingInfo.phone"
                    type="tel"
                    placeholder="+63 912 345 6789"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>
                
                <!-- Address -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                  <input
                    v-model="shippingInfo.address"
                    type="text"
                    placeholder="Street name, building, house number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>
                
                <!-- City -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">City/Municipality</label>
                  <select
                    v-model="shippingInfo.city"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                    <option value="">Select City</option>
                    <option value="bacoor">Bacoor</option>
                    <option value="imus">Imus</option>
                    <option value="dasmarinas">Dasmarinas</option>
                    <option value="tanza">Tanza</option>
                    <option value="trece-martires">Trece Martires</option>
                    <option value="general-trias">General Trias</option>
                  </select>
                </div>
                
                <!-- Barangay -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                  <input
                    v-model="shippingInfo.barangay"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>
                
                <!-- ZIP Code -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                  <input
                    v-model="shippingInfo.zipCode"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                  >
                </div>
                
                <!-- Special Instructions -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Special Instructions (Optional)</label>
                  <textarea
                    v-model="shippingInfo.instructions"
                    rows="3"
                    placeholder="Delivery notes, gate codes, etc."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  ></textarea>
                </div>
              </div>
              
              <!-- Save Address -->
              <div class="mt-6 flex items-center">
                <input
                  v-model="shippingInfo.saveAddress"
                  type="checkbox"
                  id="saveAddress"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="saveAddress" class="ml-2 text-sm text-gray-700">
                  Save this address for future purchases
                </label>
              </div>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-bold text-gray-900 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Payment Method
              </h2>
            </div>
            
            <div class="p-6">
              <div class="space-y-4">
                <!-- Cash on Delivery -->
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
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3">
                        <div v-if="paymentMethod === 'cod'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                      </div>
                      <div>
                        <h3 class="font-medium text-gray-900">Cash on Delivery</h3>
                        <p class="text-sm text-gray-600">Pay when you receive the items</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">Available</span>
                    </div>
                  </div>
                </div>

                <!-- GCash -->
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
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3">
                        <div v-if="paymentMethod === 'gcash'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                      </div>
                      <div>
                        <h3 class="font-medium text-gray-900">GCash</h3>
                        <p class="text-sm text-gray-600">Pay via GCash mobile app</p>
                      </div>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">Recommended</span>
                    </div>
                  </div>
                  
                  <!-- GCash Details -->
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
                        <div class="w-48 h-48 bg-white p-4 rounded-lg">
                          <!-- QR Code Placeholder -->
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

                <!-- Credit/Debit Card -->
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
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3">
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
                  
                  <!-- Card Details -->
                  <div v-if="paymentMethod === 'card'" class="mt-4 pt-4 border-t border-gray-200">
                    <div class="space-y-4">
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                        <input
                          v-model="cardInfo.number"
                          type="text"
                          placeholder="1234 5678 9012 3456"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                      </div>
                      
                      <div class="grid grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                          <input
                            v-model="cardInfo.expiry"
                            type="text"
                            placeholder="MM/YY"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          >
                        </div>
                        
                        <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                          <input
                            v-model="cardInfo.cvv"
                            type="text"
                            placeholder="123"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          >
                        </div>
                      </div>
                      
                      <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cardholder Name</label>
                        <input
                          v-model="cardInfo.name"
                          type="text"
                          placeholder="As shown on card"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                      </div>
                      
                      <div class="flex items-center">
                        <input
                          v-model="cardInfo.saveCard"
                          type="checkbox"
                          id="saveCard"
                          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        >
                        <label for="saveCard" class="ml-2 text-sm text-gray-700">
                          Save card for future purchases
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Bank Transfer -->
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
                      <div class="w-6 h-6 rounded-full border flex items-center justify-center mr-3">
                        <div v-if="paymentMethod === 'bank'" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                      </div>
                      <div>
                        <h3 class="font-medium text-gray-900">Bank Transfer</h3>
                        <p class="text-sm text-gray-600">Pay via bank deposit or transfer</p>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Bank Details -->
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
            </div>
          </div>
        </div>

        <!-- Right Column - Order Summary -->
        <div class="lg:col-span-1">
          <div class="sticky top-24">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
              <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Order Summary</h2>
              </div>
              
              <!-- Order Items -->
              <div class="max-h-96 overflow-y-auto">
                <div v-for="item in orderItems" :key="item.id" class="p-4 border-b border-gray-100">
                  <div class="flex items-start">
                    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-50 to-purple-50 flex items-center justify-center mr-3">
                      <div v-if="item.type === 'product'" class="w-8 h-8 rounded-full" :style="{ backgroundColor: item.color }"></div>
                      <svg v-else class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                      </svg>
                    </div>
                    <div class="flex-1">
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
              </div>
              
              <!-- Price Breakdown -->
              <div class="p-6">
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
                  
                  <div class="pt-3 border-t border-gray-200">
                    <div class="flex justify-between">
                      <span class="text-lg font-semibold text-gray-900">Total</span>
                      <span class="text-2xl font-bold text-gray-900">₱{{ totalAmount.toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
                
                <!-- Terms & Conditions -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                  <div class="flex items-start">
                    <input
                      v-model="acceptTerms"
                      type="checkbox"
                      id="acceptTerms"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1"
                    >
                    <label for="acceptTerms" class="ml-2 text-sm text-gray-700">
                      I agree to the 
                      <a href="#" class="text-blue-600 hover:text-blue-800">Terms of Service</a>
                      and 
                      <a href="#" class="text-blue-600 hover:text-blue-800">Privacy Policy</a>
                    </label>
                  </div>
                  
                  <div class="mt-4">
                    <button
                      @click="placeOrder"
                      :disabled="!acceptTerms || !isFormValid"
                      :class="[
                        'w-full py-4 rounded-lg font-semibold transition-all duration-300 flex items-center justify-center space-x-2',
                        !acceptTerms || !isFormValid
                          ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                          : 'bg-gradient-to-r from-blue-500 to-purple-500 text-white hover:from-blue-600 hover:to-purple-600 hover:shadow-xl'
                      ]"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                      </svg>
                      <span class="text-lg">Place Order</span>
                    </button>
                    
                    <p class="text-xs text-gray-500 text-center mt-3">
                      By placing your order, you agree to our terms
                    </p>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Need Help -->
            <div class="mt-4 bg-blue-50 rounded-2xl p-4">
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
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

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
/* Custom scrollbar for order items */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

/* Smooth transitions for payment method selection */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom checkbox styling */
input[type="checkbox"]:checked {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

/* Focus styles for inputs */
input:focus, select:focus, textarea:focus {
  outline: none;
  ring-width: 2px;
  ring-color: #3b82f6;
}
</style>