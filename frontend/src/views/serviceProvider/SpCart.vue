<template>
  <div class="min-h-screen relative  text-slate-200">
    <div class="bg-slate-900 shadow-sm border-b border-slate-800">
      <div class="container mx-auto px-4 py-6 md:py-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-black text-white tracking-tight">Procurement Cart</h1>
          <p class="text-slate-400 mt-1 font-medium">Review your selected items</p>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mb-4"></div>
        <p class="text-slate-400 font-medium">Processing your request...</p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-2">
          
          <Card v-if="cartItems.length === 0" class="p-12 text-center border border-slate-800 shadow-lg rounded-3xl bg-slate-900 flex flex-col items-center justify-center min-h-[400px]">
            <div class="w-24 h-24 mb-6 bg-slate-800 rounded-full flex items-center justify-center text-slate-500 shadow-inner">
              <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Your cart is empty</h3>
            <p class="text-slate-400 mb-8 font-medium">Visit a partnered distributor to procure materials.</p>
            <router-link to="/serviceProvider/Distributors">
              <Button class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white font-bold h-12 px-8 rounded-xl shadow-lg shadow-indigo-900/20 transition-all border-0">
                Browse Distributors
              </Button>
            </router-link>
          </Card>

          <div v-else class="space-y-6">
            <Card v-if="productItems.length > 0" class="border border-slate-700/60 shadow-xl rounded-3xl overflow-hidden bg-slate-900 ring-1 ring-white/5">
              <CardHeader class="p-5 md:p-6 border-b border-slate-800 bg-slate-900 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center">
                  <label class="flex items-center cursor-pointer mr-4 group">
                    <input 
                      type="checkbox" 
                      v-model="isAllSelected" 
                      class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900 cursor-pointer transition-colors"
                      :disabled="isUpdating"
                    />
                    <span class="ml-2 text-sm font-bold text-slate-300 group-hover:text-indigo-400 transition-colors">Select All</span>
                  </label>
                  <CardTitle class="text-lg font-black text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    Procurement Items
                  </CardTitle>
                </div>
                <span class="bg-indigo-500/20 text-indigo-300 text-xs font-bold px-3 py-1 rounded-full border border-indigo-500/30">{{ selectedItems.length }} of {{ productItems.length }} selected</span>
              </CardHeader>
              
              <CardContent class="p-0">
                <div class="divide-y divide-slate-800">
                  <div
                    v-for="item in productItems"
                    :key="item.id"
                    class="p-5 md:p-6 hover:bg-slate-800/50 transition-colors bg-transparent group flex items-center"
                  >
                    <div class="mr-4 sm:mr-6 shrink-0">
                      <input 
                        type="checkbox" 
                        :value="item.id" 
                        v-model="selectedItemIds" 
                        class="w-5 h-5 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900 cursor-pointer transition-colors"
                        :disabled="isUpdating"
                      />
                    </div>

                    <div class="flex flex-col sm:flex-row items-start gap-5 w-full">
                      <div class="w-full sm:w-28 h-48 sm:h-28 rounded-2xl bg-slate-800 flex items-center justify-center shrink-0 border border-slate-700 shadow-sm overflow-hidden" :style="item.image_url ? {} : { backgroundColor: item.color }">
                        <img v-if="item.image_url" :src="item.image_url" alt="Product" class="w-full h-full object-cover opacity-90 group-hover:scale-105 transition-transform duration-500" />
                      </div>
                      
                      <div class="flex-1 w-full flex flex-col justify-between h-full">
                        <div class="flex flex-col sm:flex-row justify-between">
                          <div>
                            <p class="text-xs font-bold tracking-wider text-indigo-400 uppercase mb-1">{{ item.distributor_name || item.brand }}</p>
                            <h3 class="font-bold text-white text-lg leading-tight">{{ item.name }}</h3>
                            <p class="text-sm text-slate-400 mt-1 font-medium">{{ item.typeDesc }} • {{ item.finish }}</p>
                          </div>
                          
                          <div class="flex flex-row sm:flex-col justify-between items-center sm:items-end sm:text-right mt-4 sm:mt-0">
                            <div class="text-xl font-black text-white">₱{{ (item.price * item.quantity).toLocaleString() }}</div>
                            <div class="text-xs font-medium text-slate-500 mt-1">₱{{ item.price.toLocaleString() }} / {{ item.unit }}</div>
                          </div>
                        </div>
                        
                        <div class="mt-5 flex flex-wrap items-center justify-between gap-4">
                          
                          <div class="flex items-center p-1 bg-slate-900 border border-slate-700 rounded-xl shadow-inner w-max">
                            <button
                              @click="updateQuantity(item.id, item.quantity - 1, item.stock)"
                              class="w-8 h-8 flex items-center justify-center bg-slate-800 rounded-lg shadow-sm text-slate-300 hover:text-white hover:bg-slate-700 disabled:opacity-50 transition-colors font-bold"
                              :disabled="item.quantity <= 1 || isUpdating"
                            >
                              -
                            </button>
                            <span class="w-10 text-center font-bold text-white">{{ item.quantity }}</span>
                            <button
                              @click="updateQuantity(item.id, item.quantity + 1, item.stock)"
                              class="w-8 h-8 flex items-center justify-center bg-slate-800 rounded-lg shadow-sm text-slate-300 hover:text-white hover:bg-slate-700 disabled:opacity-50 transition-colors font-bold"
                              :disabled="item.quantity >= item.stock || isUpdating"
                            >
                              +
                            </button>
                          </div>

                          <div class="flex items-center gap-4">
                            <div class="flex items-center">
                              <svg :class="['w-4 h-4 mr-1.5', item.stock > 10 ? 'text-green-500' : 'text-amber-500']" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                              </svg>
                              <span :class="['text-xs font-bold', item.stock > 10 ? 'text-green-400' : 'text-amber-400']">
                                {{ item.stock > 10 ? `${item.stock} available` : `Only ${item.stock} left` }}
                              </span>
                            </div>

                            <button
                              @click="removeItem(item.id)"
                              :disabled="isUpdating"
                              class="text-slate-500 hover:text-red-400 bg-slate-800/50 hover:bg-slate-800 w-8 h-8 flex items-center justify-center rounded-lg transition-colors border border-transparent hover:border-slate-700"
                            >
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                              </svg>
                            </button>
                          </div>

                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <div class="flex flex-col-reverse sm:flex-row justify-between items-center gap-4 mt-8">
              <router-link to="/serviceProvider/Distributors" class="w-full sm:w-auto">
                <Button variant="outline" class="w-full sm:w-auto h-12 rounded-xl font-bold border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                  </svg>
                  Back to Partners
                </Button>
              </router-link>
              
              <Button
                variant="ghost"
                @click="clearCart"
                :disabled="isUpdating"
                class="text-red-400 hover:text-red-300 hover:bg-red-900/20 font-bold h-12 px-6 rounded-xl w-full sm:w-auto transition-colors"
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
            <Card class="border border-slate-700/60 shadow-2xl rounded-3xl overflow-hidden bg-[#0f172a] text-slate-200 relative">
              <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>

              <CardHeader class="p-6 border-b border-slate-800/80 relative z-10">
                <CardTitle class="text-lg font-bold text-white uppercase tracking-widest">Order Summary</CardTitle>
              </CardHeader>
              
              <CardContent class="p-6 relative z-10">
                <div class="space-y-4 font-medium">
                  <div class="flex justify-between items-center text-slate-400 text-sm">
                    <span>Subtotal ({{ totalItems }} items)</span>
                    <span class="text-white">₱{{ subtotal.toLocaleString() }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center text-slate-400 text-sm">
                    <span>Est. Delivery Fee</span>
                    <span>
                      <span v-if="paymentMethod === 'pick-up'" class="text-amber-400 font-bold bg-amber-400/10 px-2 py-0.5 rounded ml-2">WAIVED</span>
                      <span v-else-if="isCalculatingShipping" class="text-slate-500 italic text-xs animate-pulse">Calculating...</span>
                      <span v-else-if="shippingFeeEst === 0" class="text-green-400 font-bold bg-green-400/10 px-2 py-0.5 rounded ml-2">FREE</span>
                      <span v-else class="text-white">₱{{ shippingFeeEst.toLocaleString() }}</span>
                    </span>
                  </div>
                  
                  <div class="py-3 border-t border-slate-800/80">
                     <span class="text-[10px] uppercase font-bold text-slate-500 tracking-widest block mb-1">Projected Status</span>
                     <span v-if="totalItems <= 30" class="text-xs bg-green-500/20 text-green-400 px-2 py-0.5 rounded border border-green-500/30 block w-max">Confirmed Automatically</span>
                     <span v-else class="text-xs bg-yellow-500/20 text-yellow-400 px-2 py-0.5 rounded border border-yellow-500/30 block w-max">Requires Manual Confirmation (Bulk)</span>
                  </div>

                  <div class="pt-2 border-t border-slate-800/80 space-y-1">
                    <div class="flex justify-between text-xs text-slate-500">
                      <span>VATable Sales</span>
                      <span v-if="!isCalculatingShipping">₱{{ (totalAmount / 1.12).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}</span>
                      <span v-else>--</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                      <span>VAT Amount (12%)</span>
                      <span v-if="!isCalculatingShipping">₱{{ (totalAmount - (totalAmount / 1.12)).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}</span>
                      <span v-else>--</span>
                    </div>
                  </div>
                  
                  <div class="my-6 border-t border-slate-800/80 pt-6">
                    <div class="flex justify-between items-end">
                      <span class="text-sm text-slate-400 font-medium">Grand Total</span>
                      <span class="text-3xl font-black text-white">₱{{ totalAmount.toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
                
                <Button
                  @click="openCheckoutModal"
                  :disabled="selectedItems.length === 0 || isUpdating || isCalculatingShipping"
                  class="w-full h-14 mt-6 rounded-xl font-bold text-base bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white shadow-lg shadow-indigo-900/30 border-0 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Proceed to Checkout
                  <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                  </svg>
                </Button>
                
                <div class="mt-8 pt-6 border-t border-slate-800/80">
                  <h3 class="text-xs font-bold text-slate-500 mb-3 uppercase tracking-wider text-center">Accepted Payment Methods</h3>
                  <div class="grid grid-cols-3 gap-2">
                    <div class="h-10 bg-slate-800 rounded-lg flex items-center justify-center border border-slate-700 hover:bg-slate-700 transition-colors">
                      <span class="text-[10px] sm:text-xs font-bold text-slate-300">COD</span>
                    </div>
                    <div class="h-10 bg-slate-800 rounded-lg flex items-center justify-center border border-slate-700 hover:bg-slate-700 transition-colors">
                      <span class="text-[10px] sm:text-xs font-bold text-slate-300">GCash</span>
                    </div>
                    <div class="h-10 bg-slate-800 rounded-lg flex items-center justify-center border border-slate-700 hover:bg-slate-700 transition-colors">
                      <span class="text-[10px] sm:text-xs font-bold text-slate-300">Pick-Up</span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isCheckoutModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
          <transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-8 scale-95">
            <div v-if="isCheckoutModalOpen" class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-white/10">
              <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center bg-slate-900 z-10">
                <h2 class="text-2xl font-black text-white tracking-tight">Checkout</h2>
                <button @click="closeModals" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 hover:text-white transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
              
              <div class="px-6 py-4 overflow-y-auto flex-1 custom-scrollbar">
                
                <div class="flex items-center gap-4 mb-8 bg-slate-800/50 p-4 rounded-2xl border border-slate-700 shadow-sm">
                  <div class="w-16 h-16 rounded-xl bg-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0 border border-indigo-500/30">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <h3 class="font-bold text-white text-lg">Your Selected Cart ({{ totalItems }} items)</h3>
                    <p class="text-indigo-400 font-black">₱{{ subtotal.toLocaleString() }}</p>
                  </div>
                </div>

                <div class="mb-8">
                  <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Payment Method</Label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    
                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="paymentMethod === 'cod' ? 'border-green-500 bg-green-900/20 shadow-sm' : 'border-slate-700 bg-slate-800 hover:border-slate-600'">
                      <input type="radio" v-model="paymentMethod" value="cod" class="hidden" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white flex items-center gap-2">
                          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                          COD
                        </span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'cod' ? 'border-green-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'cod'" class="w-2.5 h-2.5 bg-green-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[
                        !isGcashAvailable ? 'opacity-50 cursor-not-allowed border-slate-800 bg-slate-900 grayscale' : (paymentMethod === 'gcash' ? 'border-indigo-500 bg-indigo-900/20 shadow-sm cursor-pointer' : 'border-slate-700 bg-slate-800 hover:border-slate-600 cursor-pointer')
                      ]">
                      <input type="radio" v-model="paymentMethod" value="gcash" class="hidden" :disabled="!isGcashAvailable" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white flex items-center gap-2">
                          <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                          GCash
                        </span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'gcash' ? 'border-indigo-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'gcash'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                        </div>
                      </div>
                      <div v-if="!isGcashAvailable" class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg uppercase">
                        Unavailable
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[
                        !isPickupAvailable ? 'opacity-50 cursor-not-allowed border-slate-800 bg-slate-900 grayscale' : (paymentMethod === 'pick-up' ? 'border-amber-500 bg-amber-900/20 shadow-sm cursor-pointer' : 'border-slate-700 bg-slate-800 hover:border-slate-600 cursor-pointer')
                      ]">
                      <input type="radio" v-model="paymentMethod" value="pick-up" class="hidden" :disabled="!isPickupAvailable" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white flex items-center gap-2">
                          <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                          Pick-Up
                        </span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'pick-up' ? 'border-amber-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'pick-up'" class="w-2.5 h-2.5 bg-amber-500 rounded-full"></div>
                        </div>
                      </div>
                      <div v-if="!isPickupAvailable" class="absolute top-0 right-0 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg uppercase">
                        Unavailable
                      </div>
                    </label>

                  </div>
                  <p v-if="!isGcashAvailable || !isPickupAvailable" class="text-xs text-amber-500 mt-2 italic font-medium">
                    * Some payment/delivery methods are disabled because one or more selected partners do not support them.
                  </p>
                </div>

                <div class="mb-8" v-if="paymentMethod !== 'pick-up'">
                  <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Delivery Address</Label>
                  <div class="space-y-3">
                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'default' ? 'border-indigo-500 bg-indigo-900/20 shadow-sm' : 'border-slate-700 bg-slate-800 hover:border-slate-600'">
                      <input type="radio" v-model="addressMode" value="default" class="hidden" />
                      
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex shrink-0 items-center justify-center" :class="addressMode === 'default' ? 'border-indigo-500' : 'border-slate-600'">
                        <div v-if="addressMode === 'default'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                      </div>
                      <div>
                        <span class="block font-bold text-white mb-0.5">Profile Default Address</span>
                        <span class="text-sm text-slate-400 font-medium">Use the saved address from your account settings.</span>
                      </div>
                    </label>

                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'custom' ? 'border-indigo-500 bg-indigo-900/20 shadow-sm' : 'border-slate-700 bg-slate-800 hover:border-slate-600'">
                      <input type="radio" v-model="addressMode" value="custom" class="hidden" />
                      
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex shrink-0 items-center justify-center" :class="addressMode === 'custom' ? 'border-indigo-500' : 'border-slate-600'">
                        <div v-if="addressMode === 'custom'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                      </div>
                      <div class="w-full">
                        <span class="block font-bold text-white mb-1">Custom Address</span>
                        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-32">
                          <textarea 
                            v-if="addressMode === 'custom'" 
                            v-model="customAddress" 
                            @click.stop
                            placeholder="Enter complete block, street, barangay, city, province..." 
                            class="mt-2 w-full p-3 border border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-900 text-white shadow-inner resize-none placeholder-slate-500"
                            rows="2"
                          ></textarea>
                        </transition>
                      </div>
                    </label>
                  </div>
                </div>

              </div>

              <div class="p-6 bg-slate-900 border-t border-slate-800 flex gap-3 z-10">
                <Button variant="outline" @click="closeModals" class="flex-1 rounded-xl h-14 border-slate-700 text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white font-bold text-base">Cancel</Button>
                <Button @click="handleOrderSubmit" :disabled="isCalculatingShipping || (addressMode === 'custom' && !customAddress.trim() && paymentMethod !== 'pick-up')" class="flex-[2] rounded-xl h-14 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-bold text-base shadow-lg shadow-indigo-900/30 border-0 transition-all">
                  Confirm Purchase
                </Button>
              </div>
            </div>
          </transition>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isCheckoutAlertOpen" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-md pointer-events-none"></div>
      </transition>

      <AlertDialog :open="isCheckoutAlertOpen" @update:open="isCheckoutAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10000] bg-slate-900 text-white">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2 text-white">
              <svg v-if="paymentMethod === 'cod'" class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <svg v-else-if="paymentMethod === 'gcash'" class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
              <svg v-else class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
              Confirm {{ paymentMethod === 'cod' ? 'COD' : (paymentMethod === 'gcash' ? 'GCash' : 'Pick-Up') }} Order
            </AlertDialogTitle>
            <AlertDialogDescription class="text-slate-400 font-medium text-base mt-3 leading-relaxed">
              You are placing a procurement order for <strong class="text-white">{{ totalItems }} items</strong>.
              <br/><br/>
              The total amount is <strong class="text-white text-lg">₱{{ totalAmount.toLocaleString() }}</strong>.
              <br/>
              <span v-if="paymentMethod === 'gcash'" class="text-indigo-400 text-sm font-semibold mt-2 block">
                You will be redirected to complete your GCash payment securely.
              </span>
              <span v-else-if="paymentMethod === 'pick-up'" class="text-amber-500 text-sm mt-2 block font-semibold">
                You will pay for and pick up your items at the physical store.
              </span>
              <span v-else class="text-sm mt-2 block">
                This will be collected upon delivery.
              </span>
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isCheckoutAlertOpen = false" class="rounded-xl font-bold border-slate-700 bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white h-11">Go Back</AlertDialogCancel>
            <AlertDialogAction @click="confirmCheckout" :disabled="isProcessing" class="rounded-xl font-bold text-white h-11 px-6 shadow-md border-0" :class="paymentMethod === 'cod' ? 'bg-green-600 hover:bg-green-500 shadow-green-900/20' : (paymentMethod === 'pick-up' ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-900/20' : 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-900/20')">
              {{ isProcessing ? 'Processing...' : (paymentMethod === 'gcash' ? 'Proceed to GCash' : 'Place Order') }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'

const router = useRouter()
const route = useRoute()

// State
const cartItems = ref([])
const selectedItemIds = ref([]) 
const isLoading = ref(true)
const isUpdating = ref(false) 
const isProcessing = ref(false)

// Modal & Form States
const isCheckoutModalOpen = ref(false)
const isCheckoutAlertOpen = ref(false)
const addressMode = ref('default')
const customAddress = ref('')
const paymentMethod = ref('cod')

// Shipping States
const shippingFeeEst = ref(0)
const isCalculatingShipping = ref(false)
let shippingCalcTimeout = null

// Computed Values
const productItems = computed(() => cartItems.value.filter(item => item.type === 'product' || !item.type))
const selectedItems = computed(() => productItems.value.filter(item => selectedItemIds.value.includes(item.id)))

// Select all Toggle
const isAllSelected = computed({
  get() {
    return productItems.value.length > 0 && selectedItemIds.value.length === productItems.value.length
  },
  set(val) {
    if (val) {
      selectedItemIds.value = productItems.value.map(item => item.id)
    } else {
      selectedItemIds.value = []
    }
  }
})

const totalItems = computed(() => selectedItems.value.reduce((sum, item) => sum + (item.quantity || 1), 0))
const subtotal = computed(() => selectedItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0))
const totalAmount = computed(() => subtotal.value + (paymentMethod.value === 'pick-up' ? 0 : shippingFeeEst.value))

const isGcashAvailable = computed(() => {
  if (selectedItems.value.length === 0) return false;
  return selectedItems.value.every(item => item.distributor_gcash_enabled);
})

const isPickupAvailable = computed(() => {
  if (selectedItems.value.length === 0) return false;
  return selectedItems.value.every(item => item.distributor_pickup_enabled);
})

watch(isGcashAvailable, (avail) => {
  if (!avail && paymentMethod.value === 'gcash') {
    paymentMethod.value = 'cod'
  }
})

watch(isPickupAvailable, (avail) => {
  if (!avail && paymentMethod.value === 'pick-up') {
    paymentMethod.value = 'cod'
  }
})

// Dynamic Live Shipping Calculator
const calculateLiveShipping = () => {
  if (selectedItems.value.length === 0) {
    shippingFeeEst.value = 0
    return
  }
  
  clearTimeout(shippingCalcTimeout)
  isCalculatingShipping.value = true

  shippingCalcTimeout = setTimeout(async () => {
    try {
      const payloadItems = selectedItems.value.map(item => ({
        product_id: item.product_id || item.product?.id || item.id,
        distributor_id: item.distributor_id,
        price: item.price,
        quantity: item.quantity,
        distributor_lat: item.distributor_lat,
        distributor_lng: item.distributor_lng
      }))

      // Same calculation logic endpoint mapped to SP
      const response = await api.post('/service-provider/shop/cart-items/calculate-shipping', {
        cart_items: payloadItems
      })

      if (response.data.success) {
        shippingFeeEst.value = response.data.data.calculated_shipping_fee
      }
    } catch (error) {
      console.error('Error calculating shipping', error)
      shippingFeeEst.value = 0 
    } finally {
      isCalculatingShipping.value = false
    }
  }, 500)
}

watch([cartItems, selectedItemIds], () => {
  calculateLiveShipping()
}, { deep: true })

// Data Fetching
const fetchCartItems = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/service-provider/shop/cart-items')
    if (response.data.success) {
      cartItems.value = response.data.data
      selectedItemIds.value = cartItems.value.map(item => item.id)
    }
  } catch (error) {
    console.error('Error fetching cart:', error)
    toast.error('Failed to load cart items')
  } finally {
    isLoading.value = false
  }
}

// Item Adjustments
const updateQuantity = async (itemId, newQuantity, maxStock) => {
  if (newQuantity < 1) return
  if (newQuantity > maxStock) {
    toast.warning(`Only ${maxStock} items available in stock`)
    return
  }
  
  isUpdating.value = true
  const item = cartItems.value.find(i => i.id === itemId)
  const oldQuantity = item.quantity
  if (item) item.quantity = newQuantity

  try {
    const response = await api.put(`/service-provider/shop/cart-items/${itemId}`, { quantity: newQuantity })
    if (!response.data.success) throw new Error()
  } catch (error) {
    if (item) item.quantity = oldQuantity
    toast.error('Failed to update quantity')
  } finally {
    isUpdating.value = false
  }
}

const removeItem = async (itemId) => {
  isUpdating.value = true
  try {
    const response = await api.delete(`/service-provider/shop/cart-items/${itemId}`)
    if (response.data.success) {
      cartItems.value = cartItems.value.filter(item => item.id !== itemId)
      selectedItemIds.value = selectedItemIds.value.filter(id => id !== itemId)
      toast.success('Item removed from cart')
    }
  } catch (error) {
    toast.error('Failed to remove item')
  } finally {
    isUpdating.value = false
  }
}

const clearCart = async () => {
  if (!confirm('Are you sure you want to completely empty your cart?')) return

  isUpdating.value = true
  try {
    const response = await api.delete('/service-provider/shop/cart-items')
    if (response.data.success) {
      cartItems.value = []
      selectedItemIds.value = []
      toast.success('Cart cleared successfully')
    }
  } catch (error) {
    toast.error('Failed to clear cart')
  } finally {
    isUpdating.value = false
  }
}

// Checkout Flow
const openCheckoutModal = () => {
  if (selectedItems.value.length === 0) {
    toast.error('No items selected. Select at least one item to proceed.')
    return
  }
  addressMode.value = 'default'
  customAddress.value = ''
  paymentMethod.value = 'cod'
  isCheckoutModalOpen.value = true
}

const closeModals = () => {
  isCheckoutModalOpen.value = false
}

const handleOrderSubmit = () => {
  isCheckoutAlertOpen.value = true
}

// Receipt Logic
const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val)
}

const downloadReceipt = (receiptData) => {
  const itemsHtml = receiptData.items.map(item => `
    <tr>
      <td>${item.name} <br/><small style="color:gray;">Supplier: ${item.distributor_name}</small></td>
      <td style="text-align: right;">${item.quantity}</td>
      <td style="text-align: right;">${formatCurrency(item.price)}</td>
      <td style="text-align: right;">${formatCurrency(item.total)}</td>
    </tr>
  `).join('');

  const content = `
    <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/1999/xhtml'>
    <head>
      <meta charset="utf-8">
      <title>Procurement Receipt - ${receiptData.order_number}</title>
      <style>
        body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 11pt; margin: 1.5cm; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #000; padding-bottom: 15px; }
        .company-name { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .meta-table { width: 100%; margin-bottom: 20px; border: none; }
        .meta-table td { padding: 5px; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { border-bottom: 2px solid #000; border-top: 2px solid #000; padding: 8px 5px; text-align: left; background-color: #f9f9f9; font-weight: bold; }
        .items-table td { border-bottom: 1px dashed #ddd; padding: 8px 5px; }
        .footer { margin-top: 50px; text-align: center; font-size: 9pt; color: #888; border-top: 1px solid #eee; padding-top: 10px; }
      </style>
    </head>
    <body>
      <div class="header">
        <div class="company-name">SERVICE PROVIDER PROCUREMENT RECEIPT</div>
        <br/>
        <div style="font-size: 14pt; font-weight: bold; letter-spacing: 2px;">OFFICIAL RECEIPT</div>
      </div>

      <table class="meta-table">
        <tr>
          <td width="60%">
            <strong>BILL TO:</strong><br/>
            ${receiptData.client_name}<br/>
            Payment Method: ${receiptData.payment_method}<br/>
            Status: ${receiptData.status}
          </td>
          <td width="40%" style="text-align: right;">
            <strong>ORDER #:</strong> ${receiptData.order_number}<br/>
            <strong>DATE:</strong> ${receiptData.date}<br/>
          </td>
        </tr>
      </table>

      <table class="items-table">
        <thead>
          <tr>
            <th>Item Description</th>
            <th style="text-align: right;">Qty</th>
            <th style="text-align: right;">Unit Price</th>
            <th style="text-align: right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          ${itemsHtml}
          <tr>
            <td>Shipping Fee</td>
            <td></td>
            <td></td>
            <td style="text-align: right;">${formatCurrency(receiptData.shipping_fee)}</td>
          </tr>
        </tbody>
      </table>

      <table style="width: 100%; margin-top: 20px;">
        <tr>
          <td width="50%">
            <p style="font-size: 9pt; color: #555;">Notes/Remarks: Thank you for your procurement. This document serves as your official e-receipt.</p>
          </td>
          <td width="50%">
            <table style="width: 100%; border-top: 2px solid #000; padding-top: 5px;">
              <tr>
                <td style="padding: 3px 0; color: #444;">VATable Sales:</td>
                <td style="padding: 3px 0; text-align: right;">${formatCurrency(receiptData.vatable_sales)}</td>
              </tr>
              <tr>
                <td style="padding: 3px 0; color: #444;">VAT Amount (12%):</td>
                <td style="padding: 3px 0; text-align: right; border-bottom: 1px solid #ccc;">${formatCurrency(receiptData.vat_amount)}</td>
              </tr>
              <tr>
                <td style="font-weight: bold; font-size: 14pt; padding-top: 10px;">TOTAL AMOUNT:</td>
                <td style="font-weight: bold; font-size: 14pt; text-align: right; padding-top: 10px;">${formatCurrency(receiptData.grand_total)}</td>
              </tr>
            </table>
          </td>
        </tr>
      </table>

      <div class="footer">
        This is a system-generated receipt.<br/>
        Generated on ${new Date().toLocaleString()}
      </div>
    </body>
    </html>
  `
  
  const blob = new Blob(['\ufeff', content], { type: 'application/msword' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `Procurement_Receipt_${receiptData.order_number}.doc`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

const confirmCheckout = async () => {
  try {
    isProcessing.value = true
    
    const payload = {
      payment_method: paymentMethod.value,
      selected_items: selectedItemIds.value
    }
    if (addressMode.value === 'custom') {
      payload.custom_address = customAddress.value
    }

    const response = await api.post('/service-provider/shop/cart-items/checkout', payload)

    if (response.data.success) {
      if (response.data.checkout_url && paymentMethod.value === 'gcash') {
        toast.success('Redirecting for GCash checkout...')
        
        setTimeout(() => {
          window.location.href = response.data.checkout_url
        }, 1500)
      } else {
        toast.success('Cart checked out successfully! (' + (paymentMethod.value === 'pick-up' ? 'Store Pick-Up' : 'Cash on Delivery') + ')')
        if (response.data.receipt_data) { downloadReceipt(response.data.receipt_data); }
        
        isCheckoutAlertOpen.value = false
        closeModals()

        cartItems.value = cartItems.value.filter(item => !selectedItemIds.value.includes(item.id))
        selectedItemIds.value = []
      }
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to place order. Ensure your profile address is configured and stock is available.')
  } finally {
    isProcessing.value = false
  }
}

// ==========================================
// GCASH CART SESSION VERIFIER HOOK
// ==========================================
const verifyGcashPayment = async (orderNumber) => {
  isLoading.value = true
  toast.info('Verifying GCash Payment... Please wait.')
  
  await new Promise(resolve => setTimeout(resolve, 2500));
  
  try {
    const response = await api.post('/service-provider/shop/cart-items/verify-gcash', { order_number: orderNumber }) 
    if (response.data.success) {
      toast.success('Payment Confirmed!', { description: 'Your procurement order has been recorded successfully.' })
      
      if (response.data.receipt_data) {
        downloadReceipt(response.data.receipt_data)
      }
      
      router.replace({ query: {} })
    }
  } catch (error) {
    toast.error('Payment Verification Failed', { description: error.response?.data?.message || 'The payment session could not be verified or was already processed.' })
    router.replace({ query: {} })
  } finally {
    fetchCartItems()
  }
}

onMounted(() => {
  if (route.query.order_number) { 
    verifyGcashPayment(route.query.order_number)
  } else {
    fetchCartItems()
  }
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #334155;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #475569;
}

input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>