<template>
  <div class="min-h-screen relative pb-20">
    
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
      <div class="container mx-auto px-4 py-4 md:py-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Button @click="router.back()" variant="ghost" class="rounded-full h-10 w-10 p-0 text-gray-500 hover:text-gray-900 bg-gray-50 hover:bg-gray-100">
            <ArrowLeft class="w-5 h-5" />
          </Button>
          <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">Product Details</h1>
          </div>
        </div>
        <div class="hidden md:flex items-center gap-3">
           <Button @click="router.push('/ECommerceClient/EccommerceCart')" variant="outline" class="rounded-xl border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors font-medium">
            <ShoppingCart class="w-4 h-4 mr-2" />
            Cart
          </Button>
           <Button @click="router.push('/ECommerceClient/EccommerceOrders')" variant="outline" class="rounded-xl border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors font-medium">
            <Package class="w-4 h-4 mr-2" />
            My Orders
          </Button>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-6"></div>
        <h3 class="text-xl font-bold text-gray-900">Loading product details...</h3>
    </div>

    <div v-else-if="!selectedProduct" class="text-center py-32">
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Product Not Found</h3>
        <p class="text-gray-500 mb-6">The product you are looking for may have been removed or is unavailable.</p>
        <Button @click="router.push('/ECommerceClient/EccommerceShop')" class="bg-blue-600 text-white hover:bg-blue-700">Back to Shop</Button>
    </div>

    <div v-else class="container mx-auto px-4 py-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-7 space-y-8">
          
          <div class="bg-white rounded-3xl p-2 border border-gray-100 shadow-sm overflow-hidden">
            <div class="relative h-[300px] md:h-[450px] w-full rounded-2xl overflow-hidden bg-gray-50 flex items-center justify-center group/slider" :style="selectedProduct.image_url || (selectedProduct.image_paths && selectedProduct.image_paths.length) ? {} : { backgroundColor: selectedProduct.color }">
              
              <div v-if="selectedProduct.image_paths && selectedProduct.image_paths.length > 0" class="w-full h-full">
                <img 
                  :src="getImageUrl(selectedProduct.image_paths[currentImageIndex])" 
                  alt="Product Image" 
                  class="object-cover w-full h-full transition-transform duration-500 hover:scale-105"
                  @error="handleImageError"
                />
                
                <div v-if="selectedProduct.image_paths.length > 1" class="absolute inset-0 flex items-center justify-between px-4 opacity-0 group-hover/slider:opacity-100 transition-opacity duration-300">
                  <button @click.stop="prevImage" class="w-10 h-10 rounded-full bg-white/80 hover:bg-white text-gray-900 flex items-center justify-center shadow-lg transition-colors">
                    <ChevronLeft class="w-6 h-6" />
                  </button>
                  <button @click.stop="nextImage" class="w-10 h-10 rounded-full bg-white/80 hover:bg-white text-gray-900 flex items-center justify-center shadow-lg transition-colors">
                    <ChevronRight class="w-6 h-6" />
                  </button>
                </div>
              </div>
              
              <img v-else-if="selectedProduct.image_url" :src="selectedProduct.image_url" alt="Product Image" class="object-cover w-full h-full transition-transform duration-500 hover:scale-105" />
              
              <div v-else class="w-40 h-40 rounded-full border-4 border-white shadow-lg" :style="{ backgroundColor: selectedProduct.color }"></div>
              
              <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
                <Badge :class="[
                  'border-0 shadow-sm backdrop-blur-md bg-white/90 text-sm px-3 py-1 font-semibold',
                  selectedProduct.stock > 10 ? 'text-green-700' : 
                  (selectedProduct.stock > 0 ? 'text-amber-600' : 'text-red-600 bg-red-50/90 font-bold')
                ]">
                  {{ selectedProduct.stock > 10 ? 'In Stock' : (selectedProduct.stock > 0 ? 'Low Stock' : 'Out of Stock') }}
                </Badge>
              </div>
            </div>

            <div v-if="selectedProduct.image_paths && selectedProduct.image_paths.length > 1" class="flex gap-2 mt-2 overflow-x-auto hide-scrollbar p-1">
               <button 
                  v-for="(img, index) in selectedProduct.image_paths" 
                  :key="index"
                  @click="currentImageIndex = index"
                  class="w-20 h-20 rounded-xl overflow-hidden shrink-0 border-2 transition-all duration-200"
                  :class="currentImageIndex === index ? 'border-blue-500 shadow-md scale-100' : 'border-transparent opacity-60 hover:opacity-100 scale-95'"
               >
                  <img :src="getImageUrl(img)" class="w-full h-full object-cover" />
               </button>
            </div>
          </div>

          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
             <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <FileText class="w-5 h-5 text-blue-500" />
                About This Product
             </h2>
             
             <div class="grid grid-cols-2 gap-4 mb-4 bg-gray-50 p-4 rounded-xl">
                 <div><span class="text-xs text-gray-500 uppercase font-bold block mb-1">Brand</span><span class="font-medium">{{ selectedProduct.distributor_name || selectedProduct.brand }}</span></div>
                 <div><span class="text-xs text-gray-500 uppercase font-bold block mb-1">Type</span><span class="font-medium">{{ selectedProduct.type }}</span></div>
                 <div><span class="text-xs text-gray-500 uppercase font-bold block mb-1">Stock Available</span><span class="font-medium" :class="selectedProduct.stock <= 0 ? 'text-red-500' : ''">{{ selectedProduct.stock }} Units</span></div>
             </div>

             <p class="text-gray-600 leading-relaxed whitespace-pre-line text-[15px]">
                {{ selectedProduct.description || 'Premium quality paint suitable for various applications. Long-lasting, durable, and excellent coverage.' }}
             </p>
          </div>

          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
            <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
              <Star class="w-6 h-6 text-amber-500 fill-amber-500" /> 
              Customer Reviews ({{ selectedProduct.review_count || 0 }})
            </h3>
            
            <div v-if="!selectedProduct.reviews || selectedProduct.reviews.length === 0" class="text-center py-10 bg-gray-50 rounded-2xl border border-gray-100">
              <MessageSquare class="w-10 h-10 text-gray-300 mx-auto mb-3" />
              <p class="text-base font-medium text-gray-500">There are no reviews for this product yet.</p>
            </div>
            
            <div v-else class="space-y-6">
              <div v-for="review in selectedProduct.reviews" :key="review.id" class="bg-gray-50/50 p-5 rounded-2xl border border-gray-100">
                <div class="flex justify-between items-start mb-3">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-500 flex items-center justify-center text-white font-bold shadow-inner">
                      {{ review.clientInitials }}
                    </div>
                    <div>
                      <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-bold text-gray-900 text-sm">{{ review.client }}</p>
                        <Badge variant="outline" class="text-[9px] uppercase tracking-wider py-0 px-1.5 h-4 border-gray-200 text-gray-500" :class="review.reviewerType === 'Service Provider' ? 'bg-indigo-500/10 text-indigo-600 border-indigo-500/30' : 'bg-blue-500/10 text-blue-600 border-blue-500/30'">
                          {{ review.reviewerType }}
                        </Badge>
                      </div>
                      <div class="flex items-center mt-0.5">
                        <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-200'" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                      </div>
                    </div>
                  </div>
                  <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">{{ review.date }}</span>
                </div>
                
                <p v-if="review.comment" class="text-[15px] text-gray-700 leading-relaxed whitespace-pre-wrap mt-2">
                  {{ review.comment }}
                </p>

                <div v-if="review.response" class="mt-4 bg-white border border-blue-100 rounded-xl p-4 relative ml-4 shadow-sm">
                  <div class="absolute -left-4 top-4 text-blue-200"><CornerDownRight class="w-6 h-6" /></div>
                  <div class="flex items-center justify-between mb-1.5">
                      <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider flex items-center gap-1">
                        <ShieldCheck class="w-3.5 h-3.5" /> Response from Store
                      </p>
                      <span class="text-[10px] text-gray-400 font-medium">{{ review.response_date }}</span>
                  </div>
                  <p class="text-sm text-gray-700 leading-relaxed">{{ review.response }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-5">
          <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-xl sticky top-24">
            
            <div class="mb-6 border-b border-gray-100 pb-6">
               <p class="text-xs font-bold tracking-wider text-blue-600 uppercase mb-1">{{ selectedProduct.distributor_name || selectedProduct.brand }}</p>
               <h1 class="text-2xl font-black text-gray-900 tracking-tight leading-tight mb-3">{{ selectedProduct.name }}</h1>
               <div class="flex items-center gap-4">
                  <div class="flex items-center bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-100 text-amber-600">
                    <Star class="w-4 h-4 fill-amber-500 mr-1" />
                    <span class="font-bold">{{ selectedProduct.rating > 0 ? selectedProduct.rating : 'New' }}</span>
                  </div>
               </div>
            </div>

            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
               <div>
                  <p class="text-xs font-bold text-blue-600/70 uppercase tracking-wider mb-1">Price per unit</p>
                  <div class="flex items-baseline text-blue-600">
                      <span class="text-3xl font-black tracking-tight">₱{{ formatCurrency(selectedProduct.price) }}</span>
                  </div>
                  <div v-if="selectedProduct.promotion && selectedProduct.original_price > selectedProduct.price" class="text-sm text-gray-400 line-through mt-0.5">
                    ₱{{ formatCurrency(selectedProduct.original_price) }}
                  </div>
               </div>
               
               <div class="mt-2 sm:mt-0" v-if="selectedProduct.promotion">
                  <Badge v-if="selectedProduct.promotion.type === 'free_shipping'" class="border-0 shadow-sm bg-blue-600 text-white font-bold">Free Shipping</Badge>
                  <Badge v-else-if="selectedProduct.promotion.type === 'percentage_discount'" class="border-0 shadow-sm bg-red-600 text-white font-bold">-{{ selectedProduct.promotion.discount_value }}% OFF</Badge>
                  <Badge v-else-if="(selectedProduct.promotion.type === 'fixed_discount' || selectedProduct.promotion.type === 'fixed_amount')" class="border-0 shadow-sm bg-red-600 text-white font-bold">₱{{ selectedProduct.promotion.discount_value }} OFF</Badge>
               </div>
            </div>

            <div class="space-y-6">
                <div>
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Quantity</Label>
                    <div class="flex items-center p-1 bg-gray-50 border border-gray-200 rounded-xl w-max overflow-hidden shadow-inner">
                      <button @click="decrementQuantity" class="w-12 h-10 flex items-center justify-center bg-white rounded-lg shadow-sm hover:bg-gray-50 text-gray-600 transition-colors font-bold text-lg" :disabled="orderQuantity <= 1" :class="orderQuantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''">-</button>
                      <div class="w-16 font-bold text-center text-gray-900">{{ orderQuantity }}</div>
                      <button @click="incrementQuantity" class="w-12 h-10 flex items-center justify-center bg-white rounded-lg shadow-sm hover:bg-gray-50 text-gray-600 transition-colors font-bold text-lg" :disabled="orderQuantity >= selectedProduct?.stock" :class="orderQuantity >= selectedProduct?.stock ? 'opacity-50 cursor-not-allowed text-red-500' : ''">+</button>
                    </div>
                </div>

                <div>
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Payment Method</Label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex flex-col items-start gap-2 p-3 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="paymentMethod === 'cod' ? 'border-green-500 bg-green-50/30' : 'border-gray-100 hover:border-gray-200'">
                      <input type="radio" v-model="paymentMethod" value="cod" class="hidden" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-gray-900 flex items-center gap-1 text-sm">COD</span>
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'cod' ? 'border-green-500' : 'border-gray-300'">
                          <div v-if="paymentMethod === 'cod'" class="w-2 h-2 bg-green-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-3 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[
                        !selectedProduct?.distributor_gcash_enabled ? 'opacity-50 cursor-not-allowed border-gray-100 bg-gray-50 grayscale' : (paymentMethod === 'gcash' ? 'border-blue-500 bg-blue-50/30 cursor-pointer' : 'border-gray-100 hover:border-gray-200 cursor-pointer')
                      ]">
                      <input type="radio" v-model="paymentMethod" value="gcash" class="hidden" :disabled="!selectedProduct?.distributor_gcash_enabled" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-gray-900 flex items-center gap-1 text-sm">GCash</span>
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'gcash' ? 'border-blue-500' : 'border-gray-300'">
                          <div v-if="paymentMethod === 'gcash'" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-3 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[
                        !selectedProduct?.distributor_pickup_enabled ? 'opacity-50 cursor-not-allowed border-gray-100 bg-gray-50 grayscale' : (paymentMethod === 'pick-up' ? 'border-amber-500 bg-amber-50/30 cursor-pointer' : 'border-gray-100 hover:border-gray-200 cursor-pointer')
                      ]">
                      <input type="radio" v-model="paymentMethod" value="pick-up" class="hidden" :disabled="!selectedProduct?.distributor_pickup_enabled" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-gray-900 flex items-center gap-1 text-sm">Pick-Up</span>
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'pick-up' ? 'border-amber-500' : 'border-gray-300'">
                          <div v-if="paymentMethod === 'pick-up'" class="w-2 h-2 bg-amber-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>

                <div v-if="paymentMethod !== 'pick-up'">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Delivery Address</Label>
                  <div class="space-y-3">
                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all duration-200" :class="addressMode === 'default' ? 'border-blue-500 bg-blue-50/30' : 'border-gray-200 bg-gray-50'">
                      <input type="radio" v-model="addressMode" value="default" class="hidden" />
                      <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="addressMode === 'default' ? 'border-blue-500' : 'border-gray-300'">
                        <div v-if="addressMode === 'default'" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                      </div>
                      <span class="text-sm font-bold text-gray-900">Profile Default Address</span>
                    </label>

                    <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-all duration-200" :class="addressMode === 'custom' ? 'border-blue-500 bg-blue-50/30' : 'border-gray-200 bg-gray-50'">
                      <input type="radio" v-model="addressMode" value="custom" class="hidden" />
                      <div class="mt-1 w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0" :class="addressMode === 'custom' ? 'border-blue-500' : 'border-gray-300'">
                        <div v-if="addressMode === 'custom'" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                      </div>
                      <div class="w-full">
                        <span class="text-sm font-bold text-gray-900 block mb-2">Custom Address</span>
                        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-32">
                          <Textarea 
                            v-if="addressMode === 'custom'" 
                            v-model="customAddress" 
                            @click.stop
                            placeholder="Complete address details..." 
                            class="w-full p-3 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white resize-none min-h-[70px]"
                          />
                        </transition>
                      </div>
                    </label>
                  </div>
                </div>

                <div class="bg-gray-900 p-5 rounded-2xl text-white shadow-lg relative overflow-hidden mt-6">
                  <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
                  <h4 class="font-bold text-gray-200 mb-4 border-b border-gray-700/50 pb-3 text-sm uppercase tracking-wider">Order Summary</h4>
                  <div class="space-y-3 relative z-10">
                    <div class="flex justify-between text-sm text-gray-300 font-medium">
                      <span>Subtotal ({{ orderQuantity }} items)</span>
                      <span>₱{{ formatCurrency(selectedProduct?.price * orderQuantity) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-300 font-medium">
                      <span>Estimated Shipping</span>
                      <span>
                        <span v-if="paymentMethod === 'pick-up'" class="text-amber-400 font-bold bg-amber-400/10 px-2 py-0.5 rounded">WAIVED</span>
                        <span v-else-if="isCalculatingShipping" class="text-gray-400 italic text-xs animate-pulse">Calculating...</span>
                        <span v-else-if="shippingFeeEst === 0" class="text-green-400 font-bold bg-green-400/10 px-2 py-0.5 rounded">FREE</span>
                        <span v-else>₱{{ formatCurrency(shippingFeeEst) }}</span>
                      </span>
                    </div>

                    <div class="pt-3 border-t border-gray-700/50 space-y-1">
                      <div class="flex justify-between text-xs text-gray-400">
                        <span>VATable Sales</span>
                        <span v-if="!isCalculatingShipping">₱{{ formatCurrency(getVatableSales((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst))) }}</span>
                        <span v-else>--</span>
                      </div>
                      <div class="flex justify-between text-xs text-gray-400">
                        <span>VAT Amount (12%)</span>
                        <span v-if="!isCalculatingShipping">₱{{ formatCurrency(getVatAmount((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst))) }}</span>
                        <span v-else>--</span>
                      </div>
                    </div>

                    <div class="flex justify-between items-end pt-3 border-t border-gray-600">
                      <span class="text-sm font-medium text-gray-300">Grand Total</span>
                      <span class="text-3xl font-black text-white">₱{{ formatCurrency((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst)) }}</span>
                    </div>
                  </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <Button 
                       variant="outline" 
                       @click="handleCartSubmit" 
                       :disabled="selectedProduct.stock <= 0" 
                       class="flex-1 rounded-xl h-14 border-gray-200 text-gray-700 font-bold hover:bg-gray-50 text-base"
                    >
                       Add to Cart
                    </Button>
                    <Button 
                       @click="handleOrderSubmit" 
                       :disabled="selectedProduct.stock <= 0 || isCalculatingShipping || (addressMode === 'custom' && !customAddress.trim() && paymentMethod !== 'pick-up')" 
                       class="flex-[2] rounded-xl h-14 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-base shadow-lg shadow-blue-600/20 border-0 transition-all"
                    >
                       Checkout Now
                    </Button>
                </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div v-if="otherProducts.length > 0" class="container mx-auto px-4 mt-16 pt-10 border-t border-gray-200">
      <div class="flex items-center justify-between mb-8">
         <h2 class="text-2xl font-black text-gray-900 tracking-tight">Other Products Available</h2>
         <Button variant="ghost" @click="router.push('/ECommerceClient/EccommerceShop')" class="text-blue-600 font-bold hover:bg-blue-50 rounded-xl">View All <ArrowRight class="w-4 h-4 ml-2" /></Button>
      </div>
      
      <div class="flex gap-6 overflow-x-auto pb-6 hide-scrollbar">
         <Card
            v-for="product in otherProducts"
            :key="product.id"
            @click="router.push(`/ECommerceClient/ProductDetails/${product.id}`)"
            class="min-w-[280px] w-[280px] md:min-w-[320px] md:w-[320px] bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-xl hover:border-blue-400 hover:-translate-y-1 transition-all duration-300 overflow-hidden group cursor-pointer shrink-0"
          >
            <div class="h-40 relative overflow-hidden bg-gray-50 flex items-center justify-center shrink-0" :style="product.image_url ? {} : { backgroundColor: product.color }">
              <img 
                 v-if="product.image_url"
                 :src="product.image_url" 
                 class="object-cover w-full h-full transition-transform duration-700 group-hover:scale-105"
              />
              <div v-else class="w-20 h-20 rounded-full border-4 border-white shadow-lg" :style="{ backgroundColor: product.color }"></div>
              
              <Badge class="absolute top-2 left-2 border-0 shadow-sm bg-white/90 text-gray-800 font-bold text-xs py-0.5 backdrop-blur-md">
                  {{ product.stock > 0 ? 'In Stock' : 'Out of Stock' }}
              </Badge>
            </div>
            <CardContent class="p-4">
               <p class="text-[10px] font-bold tracking-wider text-blue-600 uppercase mb-1">{{ product.distributor_name || product.brand }}</p>
               <h3 class="font-bold text-base leading-tight text-gray-900 mb-1 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ product.name }}</h3>
               <div class="flex items-center mb-2">
                  <Star class="w-3.5 h-3.5 text-amber-500 fill-amber-500 mr-1" />
                  <span class="text-xs font-bold text-gray-800">{{ product.rating > 0 ? product.rating : 'New' }}</span>
               </div>
               <div class="flex items-baseline text-blue-600 mt-2">
                  <span class="text-lg font-black tracking-tight">₱{{ formatCurrency(product.price) }}</span>
               </div>
            </CardContent>
          </Card>
      </div>
    </div>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isCartAlertOpen || isOrderAlertOpen || isAuthAlertOpen" class="fixed inset-0 z-[9999] bg-gray-900/60 backdrop-blur-md pointer-events-none"></div>
      </transition>

      <AlertDialog :open="isAuthAlertOpen" @update:open="isAuthAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Authentication Required
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3">
              You must be logged in to make a purchase or add items to your cart. Please log in or create an account to continue.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isAuthAlertOpen = false" class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="router.push('/Landing/logIn')" class="rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white h-11 px-6 shadow-md shadow-blue-600/20">
              Log In
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <AlertDialog :open="isCartAlertOpen" @update:open="isCartAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold">Add to Cart</AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-2">
              Are you sure you want to add <strong class="text-gray-900">{{ orderQuantity }}x {{ selectedProduct?.name }}</strong> to your cart?
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isCartAlertOpen = false" class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmAddToCart" :disabled="isProcessing" class="rounded-xl font-bold bg-gray-900 hover:bg-black text-white h-11 px-6 shadow-md">
              {{ isProcessing ? 'Adding...' : 'Yes, Add to Cart' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <AlertDialog :open="isOrderAlertOpen" @update:open="isOrderAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <svg v-if="paymentMethod === 'cod'" class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <svg v-else-if="paymentMethod === 'gcash'" class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
              <svg v-else class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
              Confirm {{ paymentMethod === 'cod' ? 'COD' : (paymentMethod === 'gcash' ? 'GCash' : 'Pick-Up') }} Order
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3 leading-relaxed">
              You are placing an order for <strong class="text-gray-900">{{ orderQuantity }} items</strong>.
              <br/><br/>
              The total amount is <strong class="text-gray-900 text-lg">₱{{ formatCurrency((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst)) }}</strong>. 
              <br/>
              <span v-if="paymentMethod === 'gcash'" class="text-blue-600 text-sm font-semibold mt-2 block">
                You will be redirected to complete your GCash payment securely.
              </span>
              <span v-else-if="paymentMethod === 'pick-up'" class="text-amber-600 text-sm mt-2 block font-semibold">
                You will pay for and pick up your items at the physical store.
              </span>
              <span v-else class="text-sm mt-2 block">
                This will be collected upon delivery.
              </span>
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isOrderAlertOpen = false" class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmOrderNow" :disabled="isProcessing" class="rounded-xl font-bold text-white h-11 px-6 shadow-md" :class="paymentMethod === 'cod' ? 'bg-green-600 hover:bg-green-700 shadow-green-600/20' : (paymentMethod === 'pick-up' ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20' : 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20')">
              {{ isProcessing ? 'Processing...' : (paymentMethod === 'gcash' ? 'Proceed to GCash' : 'Place Order') }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, defineProps, watch } from 'vue' 
import { useRoute, useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { 
  ArrowLeft,
  ArrowRight,
  ShoppingCart,
  Package,
  Star,
  MessageSquare,
  CornerDownRight,
  ShieldCheck,
  FileText,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
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

const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const route = useRoute()
const router = useRouter() 

const isLoading = ref(true)
const selectedProduct = ref(null)
const otherProducts = ref([])
const currentImageIndex = ref(0)
const isProcessing = ref(false)

// Alert Dialog States
const isAuthAlertOpen = ref(false)
const isCartAlertOpen = ref(false)
const isOrderAlertOpen = ref(false)

// Form States
const orderQuantity = ref(1)
const addressMode = ref('default')
const customAddress = ref('')
const paymentMethod = ref('cod')
const shippingFeeEst = ref(0)
const isCalculatingShipping = ref(false)
let shippingCalcTimeout = null

// FIX: Robust Dynamic Image URL Generator
const getImageUrl = (path) => {
  if (!path) return '';
  const baseUrl = import.meta.env.VITE_API_URL 
      ? import.meta.env.VITE_API_URL.replace('/api', '') 
      : 'http://localhost:8000';
  if (path.includes('localhost:8000')) {
      path = path.replace('http://localhost:8000', baseUrl);
  }
  if (path.startsWith('http')) return path;
  const cleanPath = path.startsWith('storage/') ? path.replace('storage/', '') : path;
  return `${baseUrl}/storage/${cleanPath}`;
}

const handleImageError = (e) => {
  e.target.style.display = 'none'
}

const formatCurrency = (value) => {
  return Number(value || 0).toLocaleString('en-PH', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  });
}

const getVatableSales = (total) => {
  return total / 1.12;
}

const getVatAmount = (total) => {
  return total - getVatableSales(total);
}

// Fetch main product details and the list of other products
const fetchPageData = async (id) => {
  try {
    isLoading.value = true
    currentImageIndex.value = 0
    
    // We fetch all products to find the current one and populate "Others"
    const response = await api.get('/client/shop/products')
    
    if (response.data.success) {
      const allProducts = response.data.data
      
      // Find the specific product by route ID
      selectedProduct.value = allProducts.find(p => p.id == id) || null
      
      if (selectedProduct.value) {
         // Default states
         orderQuantity.value = 1;
         paymentMethod.value = 'cod';
         addressMode.value = 'default';
         customAddress.value = '';
         
         // Filter out the current product to show "Other Products"
         otherProducts.value = allProducts.filter(p => p.id != id).slice(0, 8) 
         calculateLiveShipping();
      }
    }
  } catch (error) {
    toast.error('Failed to load product details')
    console.error('Error fetching products:', error)
  } finally {
    isLoading.value = false
  }
}

// Image Navigation
const nextImage = () => {
  if (selectedProduct.value && selectedProduct.value.image_paths && selectedProduct.value.image_paths.length > 1) {
    currentImageIndex.value = (currentImageIndex.value + 1) % selectedProduct.value.image_paths.length
  }
}

const prevImage = () => {
  if (selectedProduct.value && selectedProduct.value.image_paths && selectedProduct.value.image_paths.length > 1) {
    currentImageIndex.value = currentImageIndex.value === 0 
      ? selectedProduct.value.image_paths.length - 1 
      : currentImageIndex.value - 1
  }
}

// Logic interactions
watch(selectedProduct, (newProd) => {
  if (newProd && !newProd.distributor_gcash_enabled && paymentMethod.value === 'gcash') {
    paymentMethod.value = 'cod'
  }
})

const handleCartSubmit = () => {
  if (!props.user) {
    isAuthAlertOpen.value = true;
    return;
  }
  isCartAlertOpen.value = true
}

const handleOrderSubmit = () => {
  if (!props.user) {
    isAuthAlertOpen.value = true;
    return;
  }
  isOrderAlertOpen.value = true
}

const incrementQuantity = () => {
  if (selectedProduct.value && orderQuantity.value < selectedProduct.value.stock) {
    orderQuantity.value++
  } else if (selectedProduct.value && orderQuantity.value >= selectedProduct.value.stock) {
    toast.warning('Maximum available stock reached');
  }
}
const decrementQuantity = () => {
  if (orderQuantity.value > 1) {
    orderQuantity.value--
  }
}

watch(orderQuantity, () => {
  calculateLiveShipping()
})

const calculateLiveShipping = () => {
  if (!selectedProduct.value) return
  
  clearTimeout(shippingCalcTimeout)
  isCalculatingShipping.value = true

  shippingCalcTimeout = setTimeout(async () => {
    try {
      const response = await api.post('/client/shop/shipping-fee', {
        cart_items: [{
          product_id: selectedProduct.value.id,
          distributor_id: selectedProduct.value.distributor_id,
          price: selectedProduct.value.price,
          quantity: orderQuantity.value,
          distributor_lat: selectedProduct.value.distributor_lat,
          distributor_lng: selectedProduct.value.distributor_lng
        }]
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

const confirmAddToCart = async () => {
  try {
    isProcessing.value = true
    const response = await api.post('/client/shop/cart', {
      product_id: selectedProduct.value.id,
      distributor_id: selectedProduct.value.distributor_id,
      quantity: orderQuantity.value
    })

    if (response.data.success) {
      toast.success(`${orderQuantity.value}x ${selectedProduct.value.name} added to cart!`)
      isCartAlertOpen.value = false 
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to add to cart')
  } finally {
    isProcessing.value = false
  }
}

const downloadReceipt = (receiptData) => {
  const content = `
    <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/1999/xhtml'>
    <head>
      <meta charset="utf-8">
      <title>Order Receipt - ${receiptData.order_number}</title>
      <style>
        body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 11pt; margin: 1.5cm; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #000; padding-bottom: 15px; }
        .company-name { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .sub-header { color: #555; font-size: 10pt; margin-top: 5px; }
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
        <div class="company-name">${receiptData.distributor_name || 'E-COMMERCE CHECKOUT'}</div>
        <br/>
        <div style="font-size: 14pt; font-weight: bold; letter-spacing: 2px;">OFFICIAL RECEIPT</div>
      </div>
      <table class="meta-table">
        <tr>
          <td width="60%">
            <strong>BILL TO:</strong><br/>${receiptData.client_name}<br/>
            Payment Method: ${receiptData.payment_method}<br/>Status: ${receiptData.status}
          </td>
          <td width="40%" style="text-align: right;">
            <strong>ORDER #:</strong> ${receiptData.order_number}<br/>
            <strong>DATE:</strong> ${receiptData.date}<br/>
          </td>
        </tr>
      </table>
      <table class="items-table">
        <thead>
          <tr><th>Item Description</th><th style="text-align: right;">Qty</th><th style="text-align: right;">Unit Price</th><th style="text-align: right;">Amount</th></tr>
        </thead>
        <tbody>
          <tr>
            <td>${receiptData.product_name || 'Multiple Items'}</td>
            <td style="text-align: right;">${receiptData.quantity || ''}</td>
            <td style="text-align: right;">${formatCurrency(receiptData.price || 0)}</td>
            <td style="text-align: right;">${formatCurrency((receiptData.price || 0) * (receiptData.quantity || 1))}</td>
          </tr>
          <tr><td>Shipping Fee</td><td></td><td></td><td style="text-align: right;">${formatCurrency(receiptData.shipping_fee)}</td></tr>
        </tbody>
      </table>
      <table style="width: 100%; margin-top: 20px;">
        <tr>
          <td width="50%"><p style="font-size: 9pt; color: #555;">Notes/Remarks: Thank you for your purchase. This document serves as your official e-receipt.</p></td>
          <td width="50%">
            <table style="width: 100%; border-top: 2px solid #000; padding-top: 5px;">
              <tr><td style="padding: 3px 0; color: #444;">VATable Sales:</td><td style="padding: 3px 0; text-align: right;">${formatCurrency(receiptData.vatable_sales)}</td></tr>
              <tr><td style="padding: 3px 0; color: #444;">VAT Amount (12%):</td><td style="padding: 3px 0; text-align: right; border-bottom: 1px solid #ccc;">${formatCurrency(receiptData.vat_amount)}</td></tr>
              <tr><td style="font-weight: bold; font-size: 14pt; padding-top: 10px;">TOTAL AMOUNT:</td><td style="font-weight: bold; font-size: 14pt; text-align: right; padding-top: 10px;">${formatCurrency(receiptData.grand_total)}</td></tr>
            </table>
          </td>
        </tr>
      </table>
      <div class="footer">This is a system-generated receipt.<br/>Generated on ${new Date().toLocaleString()}</div>
    </body>
    </html>
  `
  const blob = new Blob(['\ufeff', content], { type: 'application/msword' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `Receipt_${receiptData.order_number}.doc`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

const confirmOrderNow = async () => {
  try {
    isProcessing.value = true
    
    const payload = {
      product_id: selectedProduct.value.id,
      distributor_id: selectedProduct.value.distributor_id,
      quantity: orderQuantity.value,
      distributor_lat: selectedProduct.value.distributor_lat,
      distributor_lng: selectedProduct.value.distributor_lng,
      payment_method: paymentMethod.value
    }

    if (addressMode.value === 'custom') {
      payload.custom_address = customAddress.value
    }

    const response = await api.post('/client/shop/order-now', payload)

    if (response.data.success) {
      if (response.data.checkout_url && paymentMethod.value === 'gcash') {
        toast.success('Redirecting for GCash checkout...')
        setTimeout(() => {
          window.location.href = response.data.checkout_url
        }, 1500)
      } else {
        toast.success('Order placed successfully! (' + (paymentMethod.value === 'pick-up' ? 'Store Pick-Up' : 'Cash on Delivery') + ')')
        if (response.data.receipt_data) {
          downloadReceipt(response.data.receipt_data);
        }
        isOrderAlertOpen.value = false 
        
        setTimeout(() => {
           router.push('/ECommerceClient/EccommerceOrders')
        }, 1500)
      }
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to place order. Ensure your profile address is configured.')
  } finally {
    isProcessing.value = false
  }
}

const verifyGcashPayment = async (orderNumber) => {
  isLoading.value = true
  toast.info('Verifying GCash Payment... Please wait.')
  
  await new Promise(resolve => setTimeout(resolve, 2500));
  
  try {
    const response = await api.post('/client/shop/verify-gcash', { order_number: orderNumber }) 
    if (response.data.success) {
      toast.success('Payment Confirmed!', { description: 'Your order has been recorded successfully.' })
      
      if (response.data.receipt_data) {
        downloadReceipt(response.data.receipt_data)
      }
      
      router.replace({ query: {} }) // Strip the params from the URL
      
      setTimeout(() => {
           router.push('/ECommerceClient/EccommerceOrders')
      }, 1500)
    }
  } catch (error) {
    toast.error('Payment Verification Failed', { description: error.response?.data?.message || 'The payment session could not be verified or was already processed.' })
    router.replace({ query: {} })
  } finally {
    fetchPageData(route.params.id)
  }
}

// Re-fetch if they click a product from the "Other Products" carousel
watch(() => route.params.id, (newId) => {
  if (newId) {
    // Scroll to top when changing services
    window.scrollTo({ top: 0, behavior: 'smooth' });
    fetchPageData(newId);
  }
})

onMounted(() => {
  if (route.query.order_number) { 
    verifyGcashPayment(route.query.order_number) 
  } else if (route.params.id) {
    fetchPageData(route.params.id)
  }
})
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>