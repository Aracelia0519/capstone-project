<template>
  <div class="min-h-screen relative">
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-6 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Paint Shop</h1>
          <p class="text-gray-500 mt-1 text-sm">Browse our premium collection of paints</p>
        </div>
        
      </div>
    </div>

    <div class="bg-white border-b border-gray-100 shadow-sm">
      <div class="container mx-auto px-4 py-4">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1">
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <Input
                type="text"
                v-model="searchQuery"
                placeholder="Search paints..."
                class="pl-10 pr-4 py-3 h-12 w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 focus:bg-white transition-all"
              />
            </div>
          </div>

          <div class="flex gap-2 overflow-x-auto pb-2 lg:pb-0 hide-scrollbar">
            <Button
              v-for="filter in quickFilters"
              :key="filter.id"
              @click="toggleFilter(filter.id)"
              :variant="activeFilters.includes(filter.id) ? 'default' : 'secondary'"
              :class="[
                'whitespace-nowrap flex items-center space-x-2 rounded-xl transition-all',
                activeFilters.includes(filter.id)
                  ? 'bg-blue-600 text-white shadow-md hover:bg-blue-700'
                  : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50'
              ]"
            >
              <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="filter.icon"></path>
              </svg>
              <span>{{ filter.label }}</span>
            </Button>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <Label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Brand</Label>
            <Select v-model="selectedBrand">
              <SelectTrigger class="rounded-xl bg-gray-50 border-transparent hover:bg-gray-100 transition-colors">
                <SelectValue placeholder="All Brands" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_brands_reset">All Brands</SelectItem>
                <SelectItem v-for="brand in brands" :key="brand" :value="brand">{{ brand }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Type</Label>
            <Select v-model="selectedType">
              <SelectTrigger class="rounded-xl bg-gray-50 border-transparent hover:bg-gray-100 transition-colors">
                <SelectValue placeholder="All Types" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_types_reset">All Types</SelectItem>
                <SelectItem v-for="type in types" :key="type" :value="type">{{ type }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Finish</Label>
            <Select v-model="selectedFinish">
              <SelectTrigger class="rounded-xl bg-gray-50 border-transparent hover:bg-gray-100 transition-colors">
                <SelectValue placeholder="All Finishes" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_finishes_reset">All Finishes</SelectItem>
                <SelectItem v-for="finish in finishes" :key="finish" :value="finish">{{ finish }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Price Range</Label>
            <Select v-model="selectedPrice">
              <SelectTrigger class="rounded-xl bg-gray-50 border-transparent hover:bg-gray-100 transition-colors">
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

    <div class="container mx-auto px-4 py-10">
      <div v-if="isLoading" class="text-center py-20 flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-500 font-medium">Loading products from inventory...</p>
      </div>

      <div v-else>
        <div class="flex justify-between items-center mb-6">
          <p class="text-gray-500 font-medium">{{ filteredProducts.length }} products found</p>
          <div class="flex items-center space-x-3">
            <span class="text-sm font-medium text-gray-500">Sort by:</span>
            <div class="w-[180px]">
              <Select v-model="sortBy">
                <SelectTrigger class="rounded-xl bg-white border-gray-200">
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
            class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden group flex flex-col"
          >
            <div class="h-56 relative overflow-hidden flex items-center justify-center bg-gray-50" :style="product.image_url ? {} : { backgroundColor: product.color }">
              <img v-if="product.image_url" :src="product.image_url" alt="Product Image" 
                   :class="['object-cover w-full h-full group-hover:scale-105 transition-transform duration-500', product.stock <= 0 ? 'grayscale opacity-60' : '']" />
              <div v-else class="w-32 h-32 rounded-full border-4 border-white shadow-lg" :style="{ backgroundColor: product.color }"></div>
              
              <div class="absolute top-3 left-3 flex gap-2">
                <Badge :class="[
                  'border-0 shadow-sm backdrop-blur-md bg-white/90',
                  product.stock > 10 ? 'text-green-700' : 
                  (product.stock > 0 ? 'text-amber-600' : 'text-red-600 bg-red-50/90 font-bold')
                ]">
                  {{ product.stock > 10 ? 'In Stock' : (product.stock > 0 ? 'Low Stock' : 'Out of Stock') }}
                </Badge>
              </div>
            </div>

            <CardContent class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <p class="text-xs font-bold tracking-wider text-blue-600 uppercase mb-1">{{ product.brand }}</p>
                <h3 :class="['font-bold text-lg leading-tight mb-1', product.stock <= 0 ? 'text-gray-400' : 'text-gray-900']">{{ product.name }}</h3>
                <p class="text-sm text-gray-500 line-clamp-1">{{ product.type }} • {{ product.finish }}</p>
              </div>

              <div class="mt-4 flex justify-between items-end">
                <div>
                  <span :class="['text-2xl font-black tracking-tight', product.stock <= 0 ? 'text-gray-400' : 'text-gray-900']">₱{{ product.price.toLocaleString() }}</span>
                  <span class="text-xs text-gray-400 font-medium ml-1">/unit</span>
                </div>
                <div class="flex items-center bg-gray-50 px-2 py-1 rounded-lg">
                  <svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  <span class="ml-1 text-xs font-bold text-gray-700">{{ product.rating }}</span>
                </div>
              </div>
            </CardContent>

            <CardFooter class="p-5 pt-0 mt-auto flex flex-col gap-3">
              <div class="flex justify-between items-center w-full">
                <span :class="['text-xs font-medium', product.stock <= 0 ? 'text-red-500 font-bold' : 'text-gray-400']">
                  {{ product.stock > 0 ? `${product.stock} units left` : '0 units left' }}
                </span>
              </div>
              
              <div class="flex gap-2 w-full">
                <Button
                  @click="openCartModal(product)"
                  :disabled="product.stock <= 0"
                  variant="outline"
                  :class="[
                    'flex-1 rounded-xl transition-all',
                    product.stock <= 0 ? 'opacity-50 cursor-not-allowed bg-gray-50 text-gray-400 border-gray-100' : 'border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold'
                  ]"
                >
                  <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Cart
                </Button>
                
                <Button
                  @click="openOrderModal(product)"
                  :disabled="product.stock <= 0"
                  :class="[
                    'flex-1 rounded-xl font-semibold transition-all border-0',
                    product.stock <= 0
                      ? 'bg-gray-200 text-gray-400 cursor-not-allowed hover:bg-gray-200'
                      : 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 shadow-md hover:shadow-lg'
                  ]"
                >
                  {{ product.stock <= 0 ? 'Out of Stock' : 'Order Now' }}
                </Button>
              </div>
            </CardFooter>
          </Card>
        </div>

        <div v-if="filteredProducts.length === 0" class="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-sm mt-6">
          <div class="w-20 h-20 mx-auto mb-4 bg-gray-50 rounded-full flex items-center justify-center text-gray-400">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">No products found</h3>
          <p class="text-gray-500 mb-6">Try adjusting your filters or search terms</p>
          <Button
            @click="clearFilters"
            class="rounded-xl bg-gray-900 hover:bg-gray-800 text-white px-8"
          >
            Clear All Filters
          </Button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isCartModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm">
          <transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-8 scale-95">
            <div v-if="isCartModalOpen" class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden ring-1 ring-black/5">
              <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-white/50 backdrop-blur-md">
                <h2 class="text-xl font-bold text-gray-900">Add to Cart</h2>
                <button @click="closeModals" class="p-2 bg-gray-50 hover:bg-gray-100 rounded-full text-gray-400 hover:text-gray-600 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
              
              <div class="p-6">
                <div class="flex items-center gap-5 mb-8 bg-gray-50 rounded-2xl p-4 border border-gray-100">
                  <div class="w-20 h-20 rounded-xl bg-white shadow-sm overflow-hidden flex-shrink-0 border border-gray-100" :style="selectedProduct?.image_url ? {} : { backgroundColor: selectedProduct?.color }">
                    <img v-if="selectedProduct?.image_url" :src="selectedProduct?.image_url" class="w-full h-full object-cover" />
                  </div>
                  <div>
                    <h3 class="font-bold text-gray-900 text-lg leading-tight mb-1">{{ selectedProduct?.name }}</h3>
                    <p class="text-blue-600 font-black text-xl">₱{{ selectedProduct?.price.toLocaleString() }}</p>
                  </div>
                </div>

                <div class="mb-4">
                  <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-3">Select Quantity</Label>
                  <div class="flex items-center p-1 bg-gray-50 border border-gray-200 rounded-2xl w-max overflow-hidden shadow-inner">
                    <button @click="decrementQuantity" class="w-12 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm hover:bg-gray-50 text-gray-600 transition-colors font-bold text-lg" :disabled="orderQuantity <= 1" :class="orderQuantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''">-</button>
                    <div class="w-16 font-bold text-lg text-center text-gray-900">{{ orderQuantity }}</div>
                    <button @click="incrementQuantity" class="w-12 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm hover:bg-gray-50 text-gray-600 transition-colors font-bold text-lg" :disabled="orderQuantity >= selectedProduct?.stock" :class="orderQuantity >= selectedProduct?.stock ? 'opacity-50 cursor-not-allowed text-red-500' : ''">+</button>
                  </div>
                  <p :class="['text-xs font-medium mt-3', orderQuantity >= selectedProduct?.stock ? 'text-red-500' : 'text-gray-400']">
                    {{ orderQuantity >= selectedProduct?.stock ? 'Maximum available stock reached' : `${selectedProduct?.stock} items available in inventory` }}
                  </p>
                </div>
              </div>

              <div class="p-6 pt-2 bg-white flex gap-3">
                <Button variant="outline" @click="closeModals" class="flex-1 rounded-xl h-12 border-gray-200 text-gray-600 font-bold hover:bg-gray-50">Cancel</Button>
                <Button @click="handleCartSubmit" class="flex-1 rounded-xl h-12 bg-gray-900 hover:bg-black text-white font-bold shadow-lg shadow-gray-900/20">
                  Proceed
                </Button>
              </div>
            </div>
          </transition>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isOrderModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm">
          <transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-8 scale-95">
            <div v-if="isOrderModalOpen" class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-black/5">
              <div class="px-6 py-5 border-b border-gray-50 flex justify-between items-center bg-white z-10">
                <h2 class="text-2xl font-black text-gray-900 tracking-tight">Checkout</h2>
                <button @click="closeModals" class="p-2 bg-gray-50 hover:bg-gray-100 rounded-full text-gray-400 hover:text-gray-600 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
              
              <div class="px-6 py-4 overflow-y-auto flex-1 custom-scrollbar">
                <div class="flex items-center gap-4 mb-8 bg-gray-50 p-4 rounded-2xl border border-gray-100 shadow-sm">
                  <div class="w-16 h-16 rounded-xl bg-white overflow-hidden flex-shrink-0 border border-gray-100 shadow-sm" :style="selectedProduct?.image_url ? {} : { backgroundColor: selectedProduct?.color }">
                    <img v-if="selectedProduct?.image_url" :src="selectedProduct?.image_url" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1">
                    <h3 class="font-bold text-gray-900 text-lg">{{ selectedProduct?.name }}</h3>
                    <p class="text-blue-600 font-black">₱{{ selectedProduct?.price.toLocaleString() }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                  <div>
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Order Quantity</Label>
                    <div class="flex items-center p-1 bg-gray-50 border border-gray-200 rounded-2xl w-max overflow-hidden shadow-inner">
                      <button @click="decrementQuantity" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm hover:bg-gray-50 text-gray-600 transition-colors font-bold" :disabled="orderQuantity <= 1" :class="orderQuantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''">-</button>
                      <div class="w-12 font-bold text-center text-gray-900">{{ orderQuantity }}</div>
                      <button @click="incrementQuantity" class="w-10 h-10 flex items-center justify-center bg-white rounded-xl shadow-sm hover:bg-gray-50 text-gray-600 transition-colors font-bold" :disabled="orderQuantity >= selectedProduct?.stock" :class="orderQuantity >= selectedProduct?.stock ? 'opacity-50 cursor-not-allowed text-red-500' : ''">+</button>
                    </div>
                    <p :class="['text-xs font-medium mt-2', orderQuantity >= selectedProduct?.stock ? 'text-red-500' : 'text-gray-400']">
                      {{ orderQuantity >= selectedProduct?.stock ? 'Max stock reached' : `${selectedProduct?.stock} units available` }}
                    </p>
                  </div>

                  <div>
                    <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Payment Method</Label>
                    <div class="border border-green-200 bg-green-50 p-3 rounded-2xl flex items-center gap-3 shadow-sm h-12">
                      <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                        <div class="w-2 h-2 rounded-full bg-white"></div>
                      </div>
                      <span class="font-bold text-green-800 text-sm">Cash on Delivery</span>
                    </div>
                  </div>
                </div>

                <div class="mb-8">
                  <Label class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Delivery Address</Label>
                  <div class="space-y-3">
                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'default' ? 'border-blue-500 bg-blue-50/50 shadow-sm' : 'border-gray-100 hover:border-gray-200'">
                      <input type="radio" v-model="addressMode" value="default" class="hidden" />
                      
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'default' ? 'border-blue-500' : 'border-gray-300'">
                        <div v-if="addressMode === 'default'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                      </div>
                      <div>
                        <span class="block font-bold text-gray-900 mb-0.5">Profile Default Address</span>
                        <span class="text-sm text-gray-500 font-medium">Use the saved address from your account settings.</span>
                      </div>
                    </label>

                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'custom' ? 'border-blue-500 bg-blue-50/50 shadow-sm' : 'border-gray-100 hover:border-gray-200'">
                      <input type="radio" v-model="addressMode" value="custom" class="hidden" />
                      
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'custom' ? 'border-blue-500' : 'border-gray-300'">
                        <div v-if="addressMode === 'custom'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                      </div>
                      <div class="w-full">
                        <span class="block font-bold text-gray-900 mb-1">Custom Address</span>
                        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-32">
                          <textarea 
                            v-if="addressMode === 'custom'" 
                            v-model="customAddress" 
                            @click.stop
                            placeholder="Enter complete block, street, barangay, city, province..." 
                            class="mt-2 w-full p-3 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-inner resize-none"
                            rows="2"
                          ></textarea>
                        </transition>
                      </div>
                    </label>
                  </div>
                </div>

                <div class="bg-gray-900 p-5 rounded-2xl text-white shadow-lg relative overflow-hidden">
                  <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
                  <h4 class="font-bold text-gray-200 mb-4 border-b border-gray-700/50 pb-3 text-sm uppercase tracking-wider">Order Summary</h4>
                  <div class="space-y-3 relative z-10">
                    <div class="flex justify-between text-sm text-gray-300 font-medium">
                      <span>Subtotal ({{ orderQuantity }} items)</span>
                      <span>₱{{ (selectedProduct?.price * orderQuantity).toLocaleString() }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-300 font-medium pb-4 border-b border-gray-700/50">
                      <span>Estimated Shipping</span>
                      <span>
                        <span v-if="isCalculatingShipping" class="text-gray-400 italic text-xs animate-pulse">Calculating...</span>
                        <span v-else-if="shippingFeeEst === 0" class="text-green-400 font-bold bg-green-400/10 px-2 py-0.5 rounded">FREE</span>
                        <span v-else>₱{{ shippingFeeEst.toLocaleString() }}</span>
                      </span>
                    </div>
                    <div class="flex justify-between items-end pt-2">
                      <span class="text-sm font-medium text-gray-400">Total to Pay</span>
                      <span class="text-3xl font-black text-white">₱{{ ((selectedProduct?.price * orderQuantity) + shippingFeeEst).toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="p-6 bg-white border-t border-gray-50 flex gap-3 z-10">
                <Button variant="outline" @click="closeModals" class="flex-1 rounded-xl h-14 border-gray-200 text-gray-600 font-bold hover:bg-gray-50 text-base">Cancel</Button>
                <Button @click="handleOrderSubmit" :disabled="isCalculatingShipping || (addressMode === 'custom' && !customAddress.trim())" class="flex-[2] rounded-xl h-14 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-base shadow-lg shadow-blue-600/20 border-0 transition-all">
                  Review Order
                </Button>
              </div>
            </div>
          </transition>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isCartAlertOpen || isOrderAlertOpen" class="fixed inset-0 z-[9999] bg-gray-900/60 backdrop-blur-md pointer-events-none"></div>
      </transition>

      <AlertDialog :open="isCartAlertOpen" @update:open="isCartAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold">Add to Cart</AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-2">
              Are you sure you want to add <strong class="text-gray-900">{{ orderQuantity }}x {{ selectedProduct?.name }}</strong> to your cart?
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Go Back</AlertDialogCancel>
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
              <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              Confirm COD Order
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3 leading-relaxed">
              You are placing an order for <strong class="text-gray-900">{{ orderQuantity }} items</strong>.
              <br/><br/>
              The total amount of <strong class="text-gray-900 text-lg">₱{{ ((selectedProduct?.price * orderQuantity) + shippingFeeEst).toLocaleString() }}</strong> will be collected upon delivery via Cash on Delivery. Do you want to finalize this purchase?
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmOrderNow" :disabled="isProcessing" class="rounded-xl font-bold bg-green-600 hover:bg-green-700 text-white h-11 px-6 shadow-md shadow-green-600/20">
              {{ isProcessing ? 'Processing...' : 'Place Order' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'

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

// API Data
const products = ref([])
const isLoading = ref(true)
const isProcessing = ref(false)

// Custom Modal States
const isCartModalOpen = ref(false)
const isOrderModalOpen = ref(false)
const selectedProduct = ref(null)

// Alert Dialog States (Final step)
const isCartAlertOpen = ref(false)
const isOrderAlertOpen = ref(false)

// Form States
const orderQuantity = ref(1)
const addressMode = ref('default')
const customAddress = ref('')
const shippingFeeEst = ref(0)
const isCalculatingShipping = ref(false)
let shippingCalcTimeout = null

const fetchProducts = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/client/shop/products')
    if (response.data.success) {
      products.value = response.data.data
    }
  } catch (error) {
    toast.error('Error fetching products')
    console.error('Error fetching products:', error)
  } finally {
    isLoading.value = false
  }
}

// Modal Handlers
const openCartModal = (product) => {
  if (product.stock <= 0) {
    toast.error('Cannot add to cart. This product is out of stock.');
    return;
  }
  selectedProduct.value = product
  orderQuantity.value = 1
  isCartModalOpen.value = true
}

const openOrderModal = (product) => {
  if (product.stock <= 0) {
    toast.error('Cannot order. This product is out of stock.');
    return;
  }
  selectedProduct.value = product
  orderQuantity.value = 1
  addressMode.value = 'default'
  customAddress.value = ''
  isOrderModalOpen.value = true
  calculateLiveShipping() // Calculate initial shipping
}

const closeModals = () => {
  isCartModalOpen.value = false
  isOrderModalOpen.value = false
  setTimeout(() => {
    selectedProduct.value = null
  }, 300) // Clear after animation
}

// Intermediary Handlers (Trigger Alerts)
const handleCartSubmit = () => {
  isCartAlertOpen.value = true
}

const handleOrderSubmit = () => {
  isOrderAlertOpen.value = true
}

// Quantity Logic
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

// Watchers
watch(orderQuantity, () => {
  if (isOrderModalOpen.value) {
    calculateLiveShipping()
  }
})

// Live Shipping Calculation
const calculateLiveShipping = () => {
  if (!selectedProduct.value) return
  
  clearTimeout(shippingCalcTimeout)
  isCalculatingShipping.value = true

  shippingCalcTimeout = setTimeout(async () => {
    try {
      const response = await api.post('/client/shop/shipping-fee', {
        cart_items: [{
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
      shippingFeeEst.value = 0 // fallback
    } finally {
      isCalculatingShipping.value = false
    }
  }, 500) // Debounce for 500ms
}

// Final Submissions
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
      isCartAlertOpen.value = false // Close alert
      closeModals() // Close main modal
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to add to cart')
  } finally {
    isProcessing.value = false
  }
}

const confirmOrderNow = async () => {
  try {
    isProcessing.value = true
    
    const payload = {
      product_id: selectedProduct.value.id,
      distributor_id: selectedProduct.value.distributor_id,
      quantity: orderQuantity.value,
      distributor_lat: selectedProduct.value.distributor_lat,
      distributor_lng: selectedProduct.value.distributor_lng
    }

    if (addressMode.value === 'custom') {
      payload.custom_address = customAddress.value
    }

    const response = await api.post('/client/shop/order-now', payload)

    if (response.data.success) {
      toast.success('Order placed successfully! (Cash on Delivery)')
      isOrderAlertOpen.value = false // Close alert
      closeModals() // Close main modal
      await fetchProducts() // Refresh inventory
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to place order. Ensure your profile address is configured.')
  } finally {
    isProcessing.value = false
  }
}

// Filters logic
const quickFilters = ref([
  { id: 'interior', label: 'Interior', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
  { id: 'exterior', label: 'Exterior', icon: 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064' },
  { id: 'low-price', label: 'Under ₱1,500', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
  { id: 'eco', label: 'Eco-Friendly', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
])

const brands = ref(['Distributor Brand', 'CaviteGo', 'EcoPaint', 'ColorMax'])
const types = ref(['Latex / Acrylic', 'Water-based', 'Waterproof', 'Interior', 'Exterior', 'Primer', 'Top Coat'])
const finishes = ref(['Standard', 'Matte', 'Gloss', 'Satin', 'Semi-Gloss', 'Flat'])
const priceRanges = ref(['Under ₱1,000', '₱1,000 - ₱2,000', '₱2,000 - ₱3,000', 'Over ₱3,000'])

const searchQuery = ref('')
const activeFilters = ref([])
const selectedBrand = ref('')
const selectedType = ref('')
const selectedFinish = ref('')
const selectedPrice = ref('')
const sortBy = ref('name')

watch([selectedBrand, selectedType, selectedFinish, selectedPrice], ([newBrand, newType, newFinish, newPrice]) => {
    if (newBrand === 'all_brands_reset') selectedBrand.value = ''
    if (newType === 'all_types_reset') selectedType.value = ''
    if (newFinish === 'all_finishes_reset') selectedFinish.value = ''
    if (newPrice === 'all_prices_reset') selectedPrice.value = ''
})

const filteredProducts = computed(() => {
  let filtered = [...products.value]

  if (searchQuery.value) {
    filtered = filtered.filter(p => 
      (p.name && p.name.toLowerCase().includes(searchQuery.value.toLowerCase())) ||
      (p.brand && p.brand.toLowerCase().includes(searchQuery.value.toLowerCase()))
    )
  }

  if (activeFilters.value.includes('interior')) {
    filtered = filtered.filter(p => p.type && p.type.toLowerCase().includes('interior'))
  }
  if (activeFilters.value.includes('exterior')) {
    filtered = filtered.filter(p => p.type && p.type.toLowerCase().includes('exterior'))
  }
  if (activeFilters.value.includes('low-price')) {
    filtered = filtered.filter(p => p.price < 1500)
  }
  if (activeFilters.value.includes('eco')) {
    filtered = filtered.filter(p => p.brand === 'EcoPaint')
  }

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

  if (sortBy.value === 'name') {
    filtered.sort((a, b) => a.name.localeCompare(b.name))
  } else if (sortBy.value === 'price-low') {
    filtered.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-high') {
    filtered.sort((a, b) => b.price - a.price)
  }

  return filtered
})

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

onMounted(() => {
  fetchProducts()
})
</script>

<style scoped>
/* Custom scrollbar hiding utility for horizontal filters */
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Custom vertical scrollbar for modals */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #d1d5db;
}
</style>