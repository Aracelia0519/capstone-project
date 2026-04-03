<template>
  <div class="min-h-screen relative text-slate-200 pb-24">
    <div class="sticky top-0 z-30 backdrop-blur-xl border-b border-slate-800/60 bg-slate-900/75">
      <div class="container mx-auto px-4 py-4 flex items-center gap-4">
        <Button @click="router.back()" variant="ghost" class="rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 p-2 h-auto">
          <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          Back to Shop
        </Button>
      </div>
    </div>

    <div v-if="isLoading" class="container mx-auto px-4 py-20 flex flex-col items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mb-4"></div>
      <p class="text-slate-400 font-medium">Loading product details...</p>
    </div>

    <div v-else-if="product" class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
        
        <div class="space-y-4">
          <div class="aspect-square rounded-3xl overflow-hidden bg-slate-900 border border-slate-800 flex items-center justify-center relative shadow-2xl shadow-black/20" :style="product.image_url ? {} : { backgroundColor: product.color }">
            <img v-if="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover" />
            <div v-else class="w-48 h-48 rounded-full border-4 border-slate-800 shadow-lg" :style="{ backgroundColor: product.color }"></div>
            
            <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
              <Badge :class="['border-0 shadow-sm backdrop-blur-md bg-slate-900/80', product.stock > 10 ? 'text-emerald-400' : (product.stock > 0 ? 'text-amber-400' : 'text-red-400 font-bold')]">
                {{ product.stock > 10 ? 'In Stock' : (product.stock > 0 ? 'Low Stock' : 'Out of Stock') }}
              </Badge>
              <Badge v-if="product.promotion" class="border-0 shadow-sm backdrop-blur-md bg-rose-500/90 text-white font-bold">
                Promo Active
              </Badge>
            </div>
          </div>
        </div>

        <div class="flex flex-col">
          <div class="mb-6">
            <p class="text-indigo-400 font-bold tracking-wider text-sm uppercase mb-2">{{ product.distributor_name || product.brand }}</p>
            <h1 class="text-3xl md:text-4xl font-black text-white mb-3 leading-tight">{{ product.name }}</h1>
            
            <div class="flex items-center gap-4 text-sm font-medium">
              <div class="flex items-center text-amber-400 bg-amber-400/10 px-2.5 py-1 rounded-lg">
                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                {{ product.rating > 0 ? product.rating : 'No Ratings' }}
              </div>
              <span class="text-slate-500 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                {{ product.review_count }} Reviews
              </span>
              <span class="text-slate-500 flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                {{ product.stock }} Available
              </span>
            </div>
          </div>

          <div class="mb-8 p-6 bg-slate-900 border border-slate-800 rounded-3xl shadow-sm">
            <div class="flex items-end gap-3 mb-2">
              <span class="text-4xl font-black text-indigo-400 tracking-tight">₱{{ formatCurrency(product.price) }}</span>
              <span class="text-slate-500 font-medium pb-1">/ unit</span>
            </div>
            <div v-if="product.promotion && product.original_price > product.price" class="flex items-center gap-3 mt-1">
              <span class="text-lg text-slate-500 line-through decoration-slate-600 decoration-2">₱{{ formatCurrency(product.original_price) }}</span>
              <Badge class="bg-rose-500/10 text-rose-400 border-rose-500/20 font-bold px-2 py-0.5">
                Discount Applied
              </Badge>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 mb-8">
            <div class="bg-slate-800/50 p-4 rounded-2xl border border-slate-700/50">
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Type</p>
              <p class="font-semibold text-white">{{ product.type || 'N/A' }}</p>
            </div>
            <div class="bg-slate-800/50 p-4 rounded-2xl border border-slate-700/50">
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Category</p>
              <p class="font-semibold text-white">{{ product.category || 'N/A' }}</p>
            </div>
            <div class="bg-slate-800/50 p-4 rounded-2xl border border-slate-700/50">
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Color Code</p>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full border border-slate-600" :style="{ backgroundColor: product.color }"></div>
                <p class="font-semibold text-white uppercase">{{ product.color }}</p>
              </div>
            </div>
            <div class="bg-slate-800/50 p-4 rounded-2xl border border-slate-700/50">
              <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Payment Support</p>
              <div class="flex gap-1">
                <span v-if="product.distributor_gcash_enabled" class="text-[10px] font-bold bg-indigo-500/20 text-indigo-400 px-2 py-0.5 rounded">GCASH</span>
                <span v-if="product.distributor_pickup_enabled" class="text-[10px] font-bold bg-amber-500/20 text-amber-400 px-2 py-0.5 rounded">PICK-UP</span>
                <span class="text-[10px] font-bold bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded">COD</span>
              </div>
            </div>
          </div>

          <div class="flex gap-4 mt-auto pt-6 border-t border-slate-800">
            <Button @click="openCartModal" :disabled="product.stock <= 0" variant="outline" class="flex-1 h-14 rounded-2xl border-slate-600 bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white font-bold text-base transition-all">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
              Add to Cart
            </Button>
            <Button @click="openOrderModal" :disabled="product.stock <= 0" class="flex-[2] h-14 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-base shadow-xl shadow-indigo-500/20 border-0 transition-all">
              {{ product.stock <= 0 ? 'Out of Stock' : 'Order Now' }}
            </Button>
          </div>
        </div>
      </div>

      <div class="mt-16 border-t border-slate-800 pt-10">
        <div class="flex gap-8 border-b border-slate-800 mb-8">
          <button @click="activeTab = 'reviews'" :class="['pb-4 text-lg font-bold border-b-2 transition-all', activeTab === 'reviews' ? 'border-indigo-500 text-white' : 'border-transparent text-slate-500 hover:text-slate-300']">
            Customer Reviews <span class="ml-2 bg-slate-800 text-xs px-2 py-0.5 rounded-full">{{ product.review_count }}</span>
          </button>
        </div>

        <div v-if="activeTab === 'reviews'" class="max-w-4xl">
          <div v-if="!product.reviews || product.reviews.length === 0" class="text-center py-16 bg-slate-900/50 rounded-3xl border border-slate-800">
            <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
            <p class="text-lg font-bold text-white mb-1">No reviews yet</p>
            <p class="text-slate-400">Be the first to review this product after purchase!</p>
          </div>

          <div v-else class="space-y-6">
            <div v-for="review in product.reviews" :key="review.id" class="bg-slate-900 p-6 rounded-3xl border border-slate-800">
              <div class="flex justify-between items-start mb-4">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white font-bold text-lg shadow-inner">
                    {{ review.clientInitials }}
                  </div>
                  <div>
                    <p class="font-bold text-white text-base">{{ review.client }}</p>
                    <div class="flex items-center mt-1">
                      <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-amber-400' : 'text-slate-700'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>
                  </div>
                </div>
                <span class="text-sm text-slate-500 font-medium">{{ review.date }}</span>
              </div>
              
              <p v-if="review.comment" class="text-slate-300 leading-relaxed whitespace-pre-wrap mt-2 text-base">
                {{ review.comment }}
              </p>

              <div v-if="review.response" class="mt-6 bg-slate-800/50 rounded-2xl p-5 border border-slate-700/50">
                <div class="flex items-center text-xs font-bold text-indigo-400 mb-2 uppercase tracking-wider">
                  <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                  Response from {{ product.distributor_name }}
                  <span class="text-slate-500 font-medium ml-auto lowercase normal-case text-xs">{{ review.response_date }}</span>
                </div>
                <p class="text-slate-300 whitespace-pre-wrap">{{ review.response }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isCartModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" @click="closeModals">
          <div @click.stop class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-700 transform transition-all">
            <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-900">
              <h2 class="text-xl font-bold text-white">Add to Cart</h2>
              <button @click="closeModals" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
            
            <div class="p-6">
              <div class="flex items-center gap-5 mb-8 bg-slate-800 rounded-2xl p-4 border border-slate-700">
                <div class="w-20 h-20 rounded-xl bg-slate-900 shadow-sm overflow-hidden flex-shrink-0" :style="product?.image_url ? {} : { backgroundColor: product?.color }">
                  <img v-if="product?.image_url" :src="product?.image_url" class="w-full h-full object-cover" />
                </div>
                <div>
                  <h3 class="font-bold text-white text-lg leading-tight mb-1">{{ product?.name }}</h3>
                  <p class="text-indigo-400 font-black text-xl">₱{{ formatCurrency(product?.price) }}</p>
                </div>
              </div>

              <div class="mb-4">
                <Label class="text-sm font-bold text-slate-400 uppercase tracking-wider block mb-3">Quantity</Label>
                <div class="flex items-center p-1 bg-slate-800 border border-slate-700 rounded-2xl w-max overflow-hidden shadow-inner">
                  <button @click="decrementQuantity" class="w-12 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-white font-bold" :disabled="orderQuantity <= 1">-</button>
                  <div class="w-16 font-bold text-lg text-center text-white">{{ orderQuantity }}</div>
                  <button @click="incrementQuantity" class="w-12 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-white font-bold" :disabled="orderQuantity >= product?.stock">+</button>
                </div>
              </div>
            </div>

            <div class="p-6 pt-2 bg-slate-900 flex gap-3">
              <Button variant="outline" @click="closeModals" class="flex-1 rounded-xl h-12 border-slate-700 text-slate-300 font-bold hover:bg-slate-800">Cancel</Button>
              <Button @click="handleCartSubmit" class="flex-1 rounded-xl h-12 bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg border-0" :disabled="isProcessing">
                {{ isProcessing ? 'Adding...' : 'Confirm' }}
              </Button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isOrderModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm" @click="closeModals">
          <div @click.stop class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] border border-slate-700">
            <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center bg-slate-900">
              <h2 class="text-2xl font-black text-white">Checkout</h2>
              <button @click="closeModals" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
            
            <div class="px-6 py-4 overflow-y-auto flex-1 custom-scrollbar">
               <div class="flex items-center gap-4 mb-8 bg-slate-800 p-4 rounded-2xl border border-slate-700 shadow-sm">
                  <div class="w-16 h-16 rounded-xl bg-slate-900 overflow-hidden flex-shrink-0 border border-slate-700" :style="product?.image_url ? {} : { backgroundColor: product?.color }">
                    <img v-if="product?.image_url" :src="product?.image_url" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1">
                    <h3 class="font-bold text-white text-lg">{{ product?.name }}</h3>
                    <p class="text-indigo-400 font-black">₱{{ formatCurrency(product?.price) }}</p>
                  </div>
                </div>

                <div class="mb-8">
                    <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Order Quantity</Label>
                    <div class="flex items-center p-1 bg-slate-800 border border-slate-700 rounded-2xl w-max overflow-hidden shadow-inner">
                      <button @click="decrementQuantity" class="w-10 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-white font-bold" :disabled="orderQuantity <= 1">-</button>
                      <div class="w-12 font-bold text-center text-white">{{ orderQuantity }}</div>
                      <button @click="incrementQuantity" class="w-10 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-white font-bold" :disabled="orderQuantity >= product?.stock">+</button>
                    </div>
                </div>

                <div class="mb-8">
                  <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Payment Method</Label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="paymentMethod === 'cod' ? 'border-emerald-500 bg-emerald-500/10 shadow-sm' : 'border-slate-700 bg-slate-800'">
                      <input type="radio" v-model="paymentMethod" value="cod" class="hidden" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white">COD</span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'cod' ? 'border-emerald-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'cod'" class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[!product?.distributor_gcash_enabled ? 'opacity-50 cursor-not-allowed border-slate-700 bg-slate-800/50' : (paymentMethod === 'gcash' ? 'border-indigo-500 bg-indigo-500/10 shadow-sm cursor-pointer' : 'border-slate-700 bg-slate-800 cursor-pointer')]">
                      <input type="radio" v-model="paymentMethod" value="gcash" class="hidden" :disabled="!product?.distributor_gcash_enabled" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white">GCash</span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'gcash' ? 'border-indigo-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'gcash'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[!product?.distributor_pickup_enabled ? 'opacity-50 cursor-not-allowed border-slate-700 bg-slate-800/50' : (paymentMethod === 'pick-up' ? 'border-amber-500 bg-amber-500/10 shadow-sm cursor-pointer' : 'border-slate-700 bg-slate-800 cursor-pointer')]">
                      <input type="radio" v-model="paymentMethod" value="pick-up" class="hidden" :disabled="!product?.distributor_pickup_enabled" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white">Pick-Up</span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'pick-up' ? 'border-amber-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'pick-up'" class="w-2.5 h-2.5 bg-amber-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>

                <div class="mb-8" v-if="paymentMethod !== 'pick-up'">
                  <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Delivery Address</Label>
                  <div class="space-y-3">
                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'default' ? 'border-indigo-500 bg-indigo-500/10 shadow-sm' : 'border-slate-700 bg-slate-800'">
                      <input type="radio" v-model="addressMode" value="default" class="hidden" />
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'default' ? 'border-indigo-500' : 'border-slate-600'">
                        <div v-if="addressMode === 'default'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                      </div>
                      <div>
                        <span class="block font-bold text-white mb-0.5">Profile Default Address</span>
                      </div>
                    </label>

                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'custom' ? 'border-indigo-500 bg-indigo-500/10 shadow-sm' : 'border-slate-700 bg-slate-800'">
                      <input type="radio" v-model="addressMode" value="custom" class="hidden" />
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'custom' ? 'border-indigo-500' : 'border-slate-600'">
                        <div v-if="addressMode === 'custom'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                      </div>
                      <div class="w-full">
                        <span class="block font-bold text-white mb-1">Custom Address</span>
                        <textarea v-if="addressMode === 'custom'" v-model="customAddress" @click.stop placeholder="Enter complete address..." class="mt-2 w-full p-3 border border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 bg-slate-700 text-white resize-none" rows="2"></textarea>
                      </div>
                    </label>
                  </div>
                </div>

                <div class="bg-slate-950 p-5 rounded-2xl text-white shadow-lg relative overflow-hidden border border-slate-800">
                  <h4 class="font-bold text-slate-300 mb-4 border-b border-slate-800 pb-3 text-sm uppercase tracking-wider">Order Summary</h4>
                  <div class="space-y-3 relative z-10">
                    <div class="flex justify-between text-sm text-slate-300 font-medium">
                      <span>Subtotal ({{ orderQuantity }} items)</span>
                      <span>₱{{ formatCurrency(product?.price * orderQuantity) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-300 font-medium">
                      <span>Estimated Shipping</span>
                      <span>
                        <span v-if="paymentMethod === 'pick-up'" class="text-amber-400 font-bold">WAIVED</span>
                        <span v-else-if="isCalculatingShipping" class="text-slate-500 italic text-xs animate-pulse">Calculating...</span>
                        <span v-else-if="shippingFeeEst === 0" class="text-emerald-400 font-bold">FREE</span>
                        <span v-else>₱{{ formatCurrency(shippingFeeEst) }}</span>
                      </span>
                    </div>
                    <div class="pt-3 border-t border-slate-800/80 space-y-1">
                      <div class="flex justify-between text-xs text-slate-400">
                        <span>VATable Sales</span>
                        <span v-if="!isCalculatingShipping">₱{{ formatCurrency(getVatableSales((product?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst))) }}</span>
                      </div>
                      <div class="flex justify-between text-xs text-slate-400">
                        <span>VAT Amount (12%)</span>
                        <span v-if="!isCalculatingShipping">₱{{ formatCurrency(getVatAmount((product?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst))) }}</span>
                      </div>
                    </div>
                    <div class="flex justify-between items-end pt-3 border-t border-slate-700">
                      <span class="text-sm font-medium text-slate-300">Grand Total</span>
                      <span class="text-3xl font-black text-white">₱{{ formatCurrency((product?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst)) }}</span>
                    </div>
                  </div>
                </div>
            </div>

            <div class="p-6 bg-slate-900 border-t border-slate-800 flex gap-3 z-10">
              <Button variant="outline" @click="closeModals" class="flex-1 rounded-xl h-14 border-slate-700 text-slate-300 font-bold hover:bg-slate-800 hover:text-white text-base">Cancel</Button>
              <Button @click="handleOrderSubmit" :disabled="isCalculatingShipping || (addressMode === 'custom' && !customAddress.trim() && paymentMethod !== 'pick-up') || isProcessing" class="flex-[2] rounded-xl h-14 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 text-white font-bold text-base shadow-lg border-0 transition-all">
                {{ isProcessing ? 'Processing...' : 'Place Order' }}
              </Button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'

const route = useRoute()
const router = useRouter()
const productId = route.params.id

const product = ref(null)
const isLoading = ref(true)
const isProcessing = ref(false)

const activeTab = ref('reviews')
const orderQuantity = ref(1)
const paymentMethod = ref('cod')
const addressMode = ref('default')
const customAddress = ref('')
const shippingFeeEst = ref(0)
const isCalculatingShipping = ref(false)
let shippingCalcTimeout = null

const isCartModalOpen = ref(false)
const isOrderModalOpen = ref(false)

const formatCurrency = (value) => Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
const getVatableSales = (total) => total / 1.12
const getVatAmount = (total) => total - getVatableSales(total)

const fetchProductDetails = async () => {
  isLoading.value = true
  try {
    const res = await api.get(`/service-provider/shop/product/${productId}`)
    if (res.data.success) {
      product.value = res.data.data
    } else {
      toast.error('Product not found')
      router.back()
    }
  } catch (error) {
    toast.error('Failed to load product details')
    router.back()
  } finally {
    isLoading.value = false
  }
}

const openCartModal = () => { isCartModalOpen.value = true }
const openOrderModal = () => { 
  orderQuantity.value = 1
  paymentMethod.value = 'cod'
  isOrderModalOpen.value = true
  calculateLiveShipping()
}
const closeModals = () => {
  isCartModalOpen.value = false
  isOrderModalOpen.value = false
}

const incrementQuantity = () => {
  if (orderQuantity.value < product.value.stock) orderQuantity.value++
}
const decrementQuantity = () => {
  if (orderQuantity.value > 1) orderQuantity.value--
}

watch(orderQuantity, () => {
  if (isOrderModalOpen.value) calculateLiveShipping()
})

const calculateLiveShipping = () => {
  if (!product.value) return
  clearTimeout(shippingCalcTimeout)
  isCalculatingShipping.value = true

  shippingCalcTimeout = setTimeout(async () => {
    try {
      const res = await api.post('/service-provider/shop/shipping-fee', {
        distributor_id: product.value.distributor_id,
        distributor_lat: product.value.distributor_lat,
        distributor_lng: product.value.distributor_lng,
        quantity: orderQuantity.value,
        total_amount: product.value.price * orderQuantity.value
      })
      if (res.data.success) {
        shippingFeeEst.value = res.data.data.calculated_shipping_fee
      }
    } catch (error) {
      shippingFeeEst.value = 0 
    } finally {
      isCalculatingShipping.value = false
    }
  }, 500)
}

const handleCartSubmit = async () => {
  isProcessing.value = true
  try {
    const res = await api.post('/service-provider/shop/cart', {
      product_id: product.value.id,
      distributor_id: product.value.distributor_id,
      quantity: orderQuantity.value
    })
    if (res.data.success) {
      toast.success(`${orderQuantity.value}x added to cart!`)
      closeModals()
    }
  } catch (error) {
    toast.error('Failed to add to cart')
  } finally {
    isProcessing.value = false
  }
}

const handleOrderSubmit = async () => {
  isProcessing.value = true
  try {
    const payload = {
      product_id: product.value.id,
      distributor_id: product.value.distributor_id,
      quantity: orderQuantity.value,
      distributor_lat: product.value.distributor_lat,
      distributor_lng: product.value.distributor_lng,
      payment_method: paymentMethod.value
    }
    if (addressMode.value === 'custom') payload.custom_address = customAddress.value

    const res = await api.post('/service-provider/shop/order-now', payload)

    if (res.data.success) {
      if (res.data.checkout_url && paymentMethod.value === 'gcash') {
        window.location.href = res.data.checkout_url
      } else {
        toast.success('Order placed successfully!')
        closeModals()
        fetchProductDetails()
      }
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to place order')
  } finally {
    isProcessing.value = false
  }
}

onMounted(() => {
  fetchProductDetails()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>