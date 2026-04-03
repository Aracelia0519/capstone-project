<template>
  <div class="min-h-screen relative text-slate-200">
    <div class="sticky top-0 z-30 backdrop-blur-xl border-b border-slate-800/60 bg-slate-900/75">
      <div class="container mx-auto px-4 py-4 md:py-6 flex justify-between items-center gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Partner Shop</h1>
          <p class="text-slate-400 mt-1 text-sm hidden sm:block">Procure paints from your partnered distributor</p>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <Button 
            @click="showDssModal = true"
            class="bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:opacity-90 border-0 shadow-lg shadow-amber-500/20 rounded-xl font-medium"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
            Smart Picks
          </Button>

          <Button @click="router.push('/serviceProvider/cart')" variant="outline" class="rounded-xl border-indigo-500/30 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500/20 hover:text-indigo-300 transition-colors font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            Cart
          </Button>
          <Button @click="router.push('/serviceProvider/Distributors')" variant="outline" class="rounded-xl border-slate-700 bg-slate-800/50 text-slate-300 hover:bg-slate-700 hover:text-white transition-colors font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Partners
          </Button>
        </div>
      </div>
    </div>

    <div class="bg-slate-900/50 border-b border-slate-800/60 shadow-sm relative z-20">
      <div class="container mx-auto px-4 py-4">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1">
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <svg class="h-5 w-5 text-slate-500 group-focus-within:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <Input
                type="text"
                v-model="searchQuery"
                placeholder="Search paints..."
                class="pl-10 pr-4 py-3 h-12 w-full rounded-xl border-slate-700 focus:border-indigo-500 focus:ring-indigo-500 bg-slate-800 focus:bg-slate-800 text-white transition-all placeholder:text-slate-500"
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
                  ? 'bg-indigo-600 text-white shadow-md hover:bg-indigo-500 border-indigo-500'
                  : 'bg-slate-800 border border-slate-700 text-slate-300 hover:bg-slate-700 hover:text-white'
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
            <Label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Brand</Label>
            <Select v-model="selectedBrand">
              <SelectTrigger class="rounded-xl bg-slate-800 border-slate-700 text-white hover:bg-slate-700 transition-colors">
                <SelectValue placeholder="All Brands" />
              </SelectTrigger>
              <SelectContent class="bg-slate-800 border-slate-700 text-white z-[10000]">
                <SelectItem value="all_brands_reset" class="focus:bg-slate-700 focus:text-white">All Brands</SelectItem>
                <SelectItem v-for="brand in brands" :key="brand" :value="brand" class="focus:bg-slate-700 focus:text-white">{{ brand }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Type</Label>
            <Select v-model="selectedType">
              <SelectTrigger class="rounded-xl bg-slate-800 border-slate-700 text-white hover:bg-slate-700 transition-colors">
                <SelectValue placeholder="All Types" />
              </SelectTrigger>
              <SelectContent class="bg-slate-800 border-slate-700 text-white z-[10000]">
                <SelectItem value="all_types_reset" class="focus:bg-slate-700 focus:text-white">All Types</SelectItem>
                <SelectItem v-for="type in types" :key="type" :value="type" class="focus:bg-slate-700 focus:text-white">{{ type }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Finish</Label>
            <Select v-model="selectedFinish">
              <SelectTrigger class="rounded-xl bg-slate-800 border-slate-700 text-white hover:bg-slate-700 transition-colors">
                <SelectValue placeholder="All Finishes" />
              </SelectTrigger>
              <SelectContent class="bg-slate-800 border-slate-700 text-white z-[10000]">
                <SelectItem value="all_finishes_reset" class="focus:bg-slate-700 focus:text-white">All Finishes</SelectItem>
                <SelectItem v-for="finish in finishes" :key="finish" :value="finish" class="focus:bg-slate-700 focus:text-white">{{ finish }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Price Range</Label>
            <Select v-model="selectedPrice">
              <SelectTrigger class="rounded-xl bg-slate-800 border-slate-700 text-white hover:bg-slate-700 transition-colors">
                <SelectValue placeholder="All Prices" />
              </SelectTrigger>
              <SelectContent class="bg-slate-800 border-slate-700 text-white z-[10000]">
                <SelectItem value="all_prices_reset" class="focus:bg-slate-700 focus:text-white">All Prices</SelectItem>
                <SelectItem v-for="price in priceRanges" :key="price" :value="price" class="focus:bg-slate-700 focus:text-white">{{ price }}</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-10">
      <div v-if="isLoading" class="text-center py-20 flex flex-col items-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-500 mb-4"></div>
        <p class="text-slate-400 font-medium">Processing your request...</p>
      </div>

      <div v-else>
        <div class="flex justify-between items-center mb-6">
          <p class="text-slate-400 font-medium">{{ filteredProducts.length }} products found</p>
          <div class="flex items-center space-x-3">
            <span class="text-sm font-medium text-slate-400">Sort by:</span>
            <div class="w-[180px]">
              <Select v-model="sortBy">
                <SelectTrigger class="rounded-xl bg-slate-800 border-slate-700 text-white">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent class="bg-slate-800 border-slate-700 text-white z-[10000]">
                  <SelectItem value="name" class="focus:bg-slate-700 focus:text-white">Name</SelectItem>
                  <SelectItem value="price-low" class="focus:bg-slate-700 focus:text-white">Price: Low to High</SelectItem>
                  <SelectItem value="price-high" class="focus:bg-slate-700 focus:text-white">Price: High to Low</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <Card
            v-for="product in filteredProducts"
            :key="product.id"
            @click="goToProductDetails(product.id)"
            class="bg-slate-800/40 rounded-2xl shadow-sm border border-slate-700/60 hover:shadow-xl hover:shadow-indigo-500/10 hover:border-indigo-500/50 hover:-translate-y-1 transition-all duration-300 overflow-hidden group flex flex-col cursor-pointer"
          >
            <div class="h-56 relative overflow-hidden flex items-center justify-center bg-slate-900/50" :style="product.image_url ? {} : { backgroundColor: product.color }">
              <img v-if="product.image_url" :src="product.image_url" alt="Product Image" 
                   :class="['object-cover w-full h-full group-hover:scale-105 transition-transform duration-500', product.stock <= 0 ? 'grayscale opacity-60' : '']" />
              <div v-else class="w-32 h-32 rounded-full border-4 border-slate-800 shadow-lg" :style="{ backgroundColor: product.color }"></div>
              
              <div class="absolute top-3 left-3 flex gap-2 flex-col items-start z-10">
                <Badge :class="[
                  'border-0 shadow-sm backdrop-blur-md bg-slate-900/80',
                  product.stock > 10 ? 'text-emerald-400' : 
                  (product.stock > 0 ? 'text-amber-400' : 'text-red-400 font-bold')
                ]">
                  {{ product.stock > 10 ? 'In Stock' : (product.stock > 0 ? 'Low Stock' : 'Out of Stock') }}
                </Badge>

                <Badge v-if="product.promotion && product.promotion.type === 'free_shipping'" class="border-0 shadow-sm backdrop-blur-md bg-indigo-500/90 text-white font-bold">
                  Free Shipping
                </Badge>
                <Badge v-else-if="product.promotion && product.promotion.type === 'percentage_discount'" class="border-0 shadow-sm backdrop-blur-md bg-rose-500/90 text-white font-bold">
                  -{{ product.promotion.discount_value }}% OFF
                </Badge>
                <Badge v-else-if="product.promotion && (product.promotion.type === 'fixed_discount' || product.promotion.type === 'fixed_amount')" class="border-0 shadow-sm backdrop-blur-md bg-rose-500/90 text-white font-bold">
                  ₱{{ product.promotion.discount_value }} OFF
                </Badge>
              </div>
            </div>

            <CardContent class="p-5 flex-1 flex flex-col justify-between relative z-10">
              <div>
                <p class="text-xs font-bold tracking-wider text-indigo-400 uppercase mb-1">{{ product.distributor_name || product.brand }}</p>
                <h3 :class="['font-bold text-lg leading-tight mb-1 group-hover:text-indigo-400 transition-colors', product.stock <= 0 ? 'text-slate-500' : 'text-white']">{{ product.name }}</h3>
                <p class="text-sm text-slate-400 line-clamp-1">{{ product.type }} </p>
              </div>

              <div class="mt-4 flex justify-between items-end">
                <div>
                  <div v-if="product.promotion && product.original_price > product.price" class="text-xs text-slate-500 line-through mb-0.5">
                    ₱{{ formatCurrency(product.original_price) }}
                  </div>
                  <span :class="['text-2xl font-black tracking-tight', product.stock <= 0 ? 'text-slate-500' : 'text-white']">₱{{ formatCurrency(product.price) }}</span>
                  <span class="text-xs text-slate-500 font-medium ml-1">/unit</span>
                </div>
                
                <div @click.stop="openReviewsModal(product)" class="flex items-center bg-slate-900/50 px-2.5 py-1.5 rounded-lg cursor-pointer hover:bg-slate-800 transition-colors border border-transparent hover:border-slate-700">
                  <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  <span class="ml-1.5 text-xs font-bold text-slate-300">{{ product.rating > 0 ? product.rating : 'New' }}</span>
                  <span v-if="product.review_count > 0" class="ml-1 text-[10px] font-medium text-slate-500">({{ product.review_count }})</span>
                </div>
              </div>
            </CardContent>

            <CardFooter class="p-5 pt-0 mt-auto flex flex-col gap-3 relative z-10">
              <div class="flex justify-between items-center w-full">
                <span :class="['text-xs font-medium', product.stock <= 0 ? 'text-red-400 font-bold' : 'text-slate-400']">
                  {{ product.stock > 0 ? `${product.stock} units left` : '0 units left' }}
                </span>
              </div>
              
              <div class="flex gap-2 w-full">
                <Button
                  @click.stop="openCartModal(product)"
                  :disabled="product.stock <= 0"
                  variant="outline"
                  :class="[
                    'flex-1 rounded-xl transition-all',
                    product.stock <= 0 ? 'opacity-50 cursor-not-allowed bg-slate-800 text-slate-500 border-slate-700' : 'border-slate-600 bg-slate-800 text-slate-200 hover:bg-slate-700 hover:text-white font-semibold'
                  ]"
                >
                  <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                  </svg>
                  Cart
                </Button>
                
                <Button
                  @click.stop="openOrderModal(product)"
                  :disabled="product.stock <= 0"
                  :class="[
                    'flex-1 rounded-xl font-semibold transition-all border-0',
                    product.stock <= 0
                      ? 'bg-slate-800 text-slate-500 cursor-not-allowed hover:bg-slate-800'
                      : 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white hover:from-indigo-500 hover:to-violet-500 shadow-md hover:shadow-lg'
                  ]"
                >
                  {{ product.stock <= 0 ? 'Out of Stock' : 'Order Now' }}
                </Button>
              </div>
            </CardFooter>
          </Card>
        </div>

        <div v-if="filteredProducts.length === 0" class="text-center py-20 bg-slate-800/30 rounded-3xl border border-slate-700/50 shadow-sm mt-6">
          <div class="w-20 h-20 mx-auto mb-4 bg-slate-800 rounded-full flex items-center justify-center text-slate-500 border border-slate-700">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2-2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
          </div>
          <h3 class="text-xl font-bold text-white mb-2">No products found</h3>
          <p class="text-slate-400 mb-6">Try adjusting your filters or search terms</p>
          <Button @click="clearFilters" class="rounded-xl bg-slate-700 hover:bg-slate-600 text-white px-8">
            Clear All Filters
          </Button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <Dialog :open="showDssModal" @update:open="(val) => !val && (showDssModal = false)">
        <DialogContent class="bg-slate-900 border border-slate-700 shadow-2xl rounded-3xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-black/50 p-0 z-[10001]">
          <div class="px-6 py-6 border-b border-slate-800 bg-slate-900 sticky top-0 z-20 flex justify-between items-start">
            <div>
              <DialogTitle class="text-2xl font-black text-white tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                Smart Recommendations
              </DialogTitle>
              <DialogDescription class="text-slate-400 font-medium mt-1">
                 Our system analyzed user ratings and trends to find the best products for you.
              </DialogDescription>
            </div>
            <button @click="showDssModal = false" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 hover:text-slate-200 transition-colors">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>

          <div class="px-6 py-6 overflow-y-auto flex-1 custom-scrollbar bg-slate-900/50">
             <div v-if="dssRecommendations.topRated.length === 0 && dssRecommendations.trending.length === 0" class="text-center py-16">
                <div class="w-20 h-20 mx-auto mb-4 bg-slate-800 rounded-full flex items-center justify-center text-slate-600 shadow-sm border border-slate-700">
                  <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">Needs More Data</h3>
                <p class="text-slate-400 max-w-sm mx-auto">We need a few more customer reviews to accurately predict trends and generate smart recommendations.</p>
             </div>

             <div v-if="dssRecommendations.topRated.length > 0" class="mb-10">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                   <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                   Highest Rated Products
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                   <Card 
                      v-for="prod in dssRecommendations.topRated" 
                      :key="prod.id"
                      @click="goToProductDetails(prod.id); showDssModal = false;"
                      class="bg-slate-800 rounded-2xl shadow-sm border border-slate-700 hover:shadow-md hover:border-amber-400 transition-all cursor-pointer flex flex-row overflow-hidden"
                   >
                      <div class="w-28 h-auto bg-slate-900 flex items-center justify-center shrink-0">
                         <img v-if="prod.image_url" :src="prod.image_url" class="object-cover w-full h-full" />
                         <div v-else class="w-12 h-12 rounded-full border-2 border-slate-800 shadow" :style="{ backgroundColor: prod.color }"></div>
                      </div>
                      <div class="p-3 flex flex-col justify-center flex-1">
                         <p class="text-[10px] font-bold tracking-wider text-amber-500 uppercase mb-0.5">Top Pick</p>
                         <h4 class="font-bold text-sm text-white line-clamp-1">{{ prod.name }}</h4>
                         <div class="flex items-center gap-1 mt-1 text-xs font-bold text-slate-300">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg> 
                            {{ prod.rating }} <span class="text-slate-500 font-medium ml-1">({{ prod.review_count }} reviews)</span>
                         </div>
                      </div>
                   </Card>
                </div>
             </div>

             <div v-if="dssRecommendations.trending.length > 0">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                   <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                   Trending Now (Most Discussed)
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                   <Card 
                      v-for="prod in dssRecommendations.trending" 
                      :key="prod.id"
                      @click="goToProductDetails(prod.id); showDssModal = false;"
                      class="bg-slate-800 rounded-2xl shadow-sm border border-slate-700 hover:shadow-md hover:border-indigo-400 transition-all cursor-pointer flex flex-row overflow-hidden"
                   >
                      <div class="w-28 h-auto bg-slate-900 flex items-center justify-center shrink-0">
                         <img v-if="prod.image_url" :src="prod.image_url" class="object-cover w-full h-full" />
                         <div v-else class="w-12 h-12 rounded-full border-2 border-slate-800 shadow" :style="{ backgroundColor: prod.color }"></div>
                      </div>
                      <div class="p-3 flex flex-col justify-center flex-1">
                         <p class="text-[10px] font-bold tracking-wider text-indigo-400 uppercase mb-0.5">Popular</p>
                         <h4 class="font-bold text-sm text-white line-clamp-1">{{ prod.name }}</h4>
                         <div class="flex items-center gap-1 mt-1 text-xs font-bold text-slate-300">
                            <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg> 
                            {{ prod.review_count }} reviews
                         </div>
                      </div>
                   </Card>
                </div>
             </div>

          </div>
        </DialogContent>
      </Dialog>
    </Teleport>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isReviewsModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click="closeModals">
          <transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-8 scale-95">
            <div v-if="isReviewsModalOpen" @click.stop class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[85vh] border border-slate-700">
              
              <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center bg-slate-900 z-10">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                  Customer Reviews
                  <span class="bg-slate-800 text-slate-300 text-xs py-0.5 px-2 rounded-full font-semibold">{{ selectedProduct?.review_count || 0 }}</span>
                </h2>
                <button @click="closeModals" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 hover:text-slate-200 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
              
              <div class="px-6 py-4 overflow-y-auto flex-1 custom-scrollbar bg-slate-900/50">
                
                <div class="flex items-center gap-5 mb-8 bg-slate-800 p-4 rounded-2xl border border-slate-700 shadow-sm">
                  <div class="w-16 h-16 rounded-xl overflow-hidden flex-shrink-0 border border-slate-700 shadow-sm" :style="selectedProduct?.image_url ? {} : { backgroundColor: selectedProduct?.color }">
                    <img v-if="selectedProduct?.image_url" :src="selectedProduct?.image_url" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1">
                    <h3 class="font-bold text-white text-lg leading-tight">{{ selectedProduct?.name }}</h3>
                    <div class="flex items-center mt-1">
                      <svg class="w-4 h-4 text-amber-400 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                      <span class="font-bold text-slate-200">{{ selectedProduct?.rating > 0 ? selectedProduct.rating : 'No ratings yet' }}</span>
                    </div>
                  </div>
                </div>

                <div v-if="!selectedProduct?.reviews || selectedProduct.reviews.length === 0" class="text-center py-12">
                  <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3 border border-slate-700">
                    <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                  </div>
                  <p class="text-slate-400 font-medium">There are no reviews for this product yet.</p>
                </div>

                <div v-else class="space-y-4">
                  <div v-for="review in selectedProduct.reviews" :key="review.id" class="bg-slate-800 p-5 rounded-2xl border border-slate-700 shadow-sm">
                    <div class="flex justify-between items-start mb-3">
                      <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-500 flex items-center justify-center text-white font-bold shadow-inner">
                          {{ review.clientInitials }}
                        </div>
                        <div>
                          <p class="font-bold text-white text-sm">{{ review.client }}</p>
                          <div class="flex items-center mt-0.5">
                            <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5" :class="i <= review.rating ? 'text-amber-400' : 'text-slate-600'" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                          </div>
                        </div>
                      </div>
                      <span class="text-xs text-slate-500 font-medium">{{ review.date }}</span>
                    </div>
                    
                    <p v-if="review.comment" class="text-sm text-slate-300 leading-relaxed whitespace-pre-wrap break-words mt-2">
                      {{ review.comment }}
                    </p>

                    <div v-if="review.response" class="mt-4 bg-slate-900/50 rounded-xl p-3.5 border border-slate-700/50">
                      <div class="flex items-center text-xs font-bold text-indigo-400 mb-1.5 uppercase tracking-wider">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                        Response from Store
                        <span class="text-slate-500 font-medium ml-auto lowercase normal-case text-[10px]">{{ review.response_date }}</span>
                      </div>
                      <p class="text-sm text-slate-400 whitespace-pre-wrap break-words">{{ review.response }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </transition>
        </div>
      </transition>
    </Teleport>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isCartModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click="closeModals">
          <transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-8 scale-95">
            <div v-if="isCartModalOpen" @click.stop class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden border border-slate-700">
              <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-900">
                <h2 class="text-xl font-bold text-white">Add to Cart</h2>
                <button @click="closeModals" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 hover:text-slate-200 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
              
              <div class="p-6">
                <div class="flex items-center gap-5 mb-8 bg-slate-800 rounded-2xl p-4 border border-slate-700">
                  <div class="w-20 h-20 rounded-xl bg-slate-900 shadow-sm overflow-hidden flex-shrink-0 border border-slate-700" :style="selectedProduct?.image_url ? {} : { backgroundColor: selectedProduct?.color }">
                    <img v-if="selectedProduct?.image_url" :src="selectedProduct?.image_url" class="w-full h-full object-cover" />
                  </div>
                  <div>
                    <h3 class="font-bold text-white text-lg leading-tight mb-1">{{ selectedProduct?.name }}</h3>
                    <p class="text-indigo-400 font-black text-xl">₱{{ formatCurrency(selectedProduct?.price) }}</p>
                    <p v-if="selectedProduct?.promotion && selectedProduct?.original_price > selectedProduct?.price" class="text-sm text-slate-500 line-through">₱{{ formatCurrency(selectedProduct?.original_price) }}</p>
                  </div>
                </div>

                <div class="mb-4">
                  <Label class="text-sm font-bold text-slate-400 uppercase tracking-wider block mb-3">Select Quantity</Label>
                  <div class="flex items-center p-1 bg-slate-800 border border-slate-700 rounded-2xl w-max overflow-hidden shadow-inner">
                    <button @click="decrementQuantity" class="w-12 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-slate-200 transition-colors font-bold text-lg" :disabled="orderQuantity <= 1" :class="orderQuantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''">-</button>
                    <div class="w-16 font-bold text-lg text-center text-white">{{ orderQuantity }}</div>
                    <button @click="incrementQuantity" class="w-12 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-slate-200 transition-colors font-bold text-lg" :disabled="orderQuantity >= selectedProduct?.stock" :class="orderQuantity >= selectedProduct?.stock ? 'opacity-50 cursor-not-allowed text-red-400' : ''">+</button>
                  </div>
                  <p :class="['text-xs font-medium mt-3', orderQuantity >= selectedProduct?.stock ? 'text-red-400' : 'text-slate-500']">
                    {{ orderQuantity >= selectedProduct?.stock ? 'Maximum available stock reached' : `${selectedProduct?.stock} items available in inventory` }}
                  </p>
                </div>
              </div>

              <div class="p-6 pt-2 bg-slate-900 flex gap-3">
                <Button variant="outline" @click="closeModals" class="flex-1 rounded-xl h-12 border-slate-700 text-slate-300 font-bold hover:bg-slate-800 hover:text-white">Cancel</Button>
                <Button @click="handleCartSubmit" class="flex-1 rounded-xl h-12 bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg shadow-indigo-500/20 border-0">
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
        <div v-if="isOrderModalOpen" class="fixed inset-0 z-[9990] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click="closeModals">
          <transition enter-active-class="transition duration-300 ease-out delay-75" enter-from-class="opacity-0 translate-y-8 scale-95" enter-to-class="opacity-100 translate-y-0 scale-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100 translate-y-0 scale-100" leave-to-class="opacity-0 translate-y-8 scale-95">
            <div v-if="isOrderModalOpen" @click.stop class="bg-slate-900 rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[90vh] border border-slate-700">
              <div class="px-6 py-5 border-b border-slate-800 flex justify-between items-center bg-slate-900 z-10">
                <h2 class="text-2xl font-black text-white tracking-tight">Checkout</h2>
                <button @click="closeModals" class="p-2 bg-slate-800 hover:bg-slate-700 rounded-full text-slate-400 hover:text-slate-200 transition-colors">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
              </div>
              
              <div class="px-6 py-4 overflow-y-auto flex-1 custom-scrollbar">
                <div class="flex items-center gap-4 mb-8 bg-slate-800 p-4 rounded-2xl border border-slate-700 shadow-sm">
                  <div class="w-16 h-16 rounded-xl bg-slate-900 overflow-hidden flex-shrink-0 border border-slate-700 shadow-sm" :style="selectedProduct?.image_url ? {} : { backgroundColor: selectedProduct?.color }">
                    <img v-if="selectedProduct?.image_url" :src="selectedProduct?.image_url" class="w-full h-full object-cover" />
                  </div>
                  <div class="flex-1">
                    <h3 class="font-bold text-white text-lg">{{ selectedProduct?.name }}</h3>
                    <p class="text-indigo-400 font-black">₱{{ formatCurrency(selectedProduct?.price) }}</p>
                    <p v-if="selectedProduct?.promotion && selectedProduct?.original_price > selectedProduct?.price" class="text-sm text-slate-500 line-through">₱{{ formatCurrency(selectedProduct?.original_price) }}</p>
                  </div>
                </div>

                <div class="mb-8">
                    <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Order Quantity</Label>
                    <div class="flex items-center p-1 bg-slate-800 border border-slate-700 rounded-2xl w-max overflow-hidden shadow-inner">
                      <button @click="decrementQuantity" class="w-10 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-slate-200 transition-colors font-bold" :disabled="orderQuantity <= 1" :class="orderQuantity <= 1 ? 'opacity-50 cursor-not-allowed' : ''">-</button>
                      <div class="w-12 font-bold text-center text-white">{{ orderQuantity }}</div>
                      <button @click="incrementQuantity" class="w-10 h-10 flex items-center justify-center bg-slate-700 rounded-xl hover:bg-slate-600 text-slate-200 transition-colors font-bold" :disabled="orderQuantity >= selectedProduct?.stock" :class="orderQuantity >= selectedProduct?.stock ? 'opacity-50 cursor-not-allowed text-red-400' : ''">+</button>
                    </div>
                    <p :class="['text-xs font-medium mt-2', orderQuantity >= selectedProduct?.stock ? 'text-red-400' : 'text-slate-500']">
                      {{ orderQuantity >= selectedProduct?.stock ? 'Max stock reached' : `${selectedProduct?.stock} units available` }}
                    </p>
                </div>

                <div class="mb-8">
                  <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Payment Method</Label>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    
                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200" :class="paymentMethod === 'cod' ? 'border-emerald-500 bg-emerald-500/10 shadow-sm' : 'border-slate-700 hover:border-slate-600 bg-slate-800'">
                      <input type="radio" v-model="paymentMethod" value="cod" class="hidden" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white flex items-center gap-2">
                          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                          COD
                        </span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'cod' ? 'border-emerald-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'cod'" class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></div>
                        </div>
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[
                        !selectedProduct?.distributor_gcash_enabled ? 'opacity-50 cursor-not-allowed border-slate-700 bg-slate-800/50 grayscale' : (paymentMethod === 'gcash' ? 'border-indigo-500 bg-indigo-500/10 shadow-sm cursor-pointer' : 'border-slate-700 hover:border-slate-600 bg-slate-800 cursor-pointer')
                      ]">
                      <input type="radio" v-model="paymentMethod" value="gcash" class="hidden" :disabled="!selectedProduct?.distributor_gcash_enabled" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white flex items-center gap-2">
                          <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                          GCash
                        </span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'gcash' ? 'border-indigo-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'gcash'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                        </div>
                      </div>
                      <div v-if="!selectedProduct?.distributor_gcash_enabled" class="absolute top-0 right-0 bg-red-500/80 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg uppercase">
                        Unavailable
                      </div>
                    </label>

                    <label class="flex flex-col items-start gap-2 p-4 border-2 rounded-xl transition-all duration-200 relative overflow-hidden" :class="[
                        !selectedProduct?.distributor_pickup_enabled ? 'opacity-50 cursor-not-allowed border-slate-700 bg-slate-800/50 grayscale' : (paymentMethod === 'pick-up' ? 'border-amber-500 bg-amber-500/10 shadow-sm cursor-pointer' : 'border-slate-700 hover:border-slate-600 bg-slate-800 cursor-pointer')
                      ]">
                      <input type="radio" v-model="paymentMethod" value="pick-up" class="hidden" :disabled="!selectedProduct?.distributor_pickup_enabled" />
                      <div class="flex items-center justify-between w-full">
                        <span class="font-bold text-white flex items-center gap-2">
                          <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                          Pick-Up
                        </span>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center" :class="paymentMethod === 'pick-up' ? 'border-amber-500' : 'border-slate-600'">
                          <div v-if="paymentMethod === 'pick-up'" class="w-2.5 h-2.5 bg-amber-500 rounded-full"></div>
                        </div>
                      </div>
                      <div v-if="!selectedProduct?.distributor_pickup_enabled" class="absolute top-0 right-0 bg-red-500/80 text-white text-[10px] font-bold px-2 py-0.5 rounded-bl-lg uppercase">
                        Unavailable
                      </div>
                    </label>

                  </div>
                </div>

                <div class="mb-8" v-if="paymentMethod !== 'pick-up'">
                  <Label class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-3">Delivery Address</Label>
                  <div class="space-y-3">
                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'default' ? 'border-indigo-500 bg-indigo-500/10 shadow-sm' : 'border-slate-700 hover:border-slate-600 bg-slate-800'">
                      <input type="radio" v-model="addressMode" value="default" class="hidden" />
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'default' ? 'border-indigo-500' : 'border-slate-600'">
                        <div v-if="addressMode === 'default'" class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></div>
                      </div>
                      <div>
                        <span class="block font-bold text-white mb-0.5">Profile Default Address</span>
                        <span class="text-sm text-slate-400 font-medium">Use the saved address from your account settings.</span>
                      </div>
                    </label>

                    <label class="flex items-start gap-4 p-4 border-2 rounded-2xl cursor-pointer transition-all duration-200" :class="addressMode === 'custom' ? 'border-indigo-500 bg-indigo-500/10 shadow-sm' : 'border-slate-700 hover:border-slate-600 bg-slate-800'">
                      <input type="radio" v-model="addressMode" value="custom" class="hidden" />
                      <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'custom' ? 'border-indigo-500' : 'border-slate-600'">
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
                            class="mt-2 w-full p-3 border border-slate-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-700 text-white shadow-inner resize-none placeholder:text-slate-400"
                            rows="2"
                          ></textarea>
                        </transition>
                      </div>
                    </label>
                  </div>
                </div>

                <div class="bg-slate-950 p-5 rounded-2xl text-white shadow-lg relative overflow-hidden border border-slate-800">
                  <h4 class="font-bold text-slate-300 mb-4 border-b border-slate-800 pb-3 text-sm uppercase tracking-wider">Order Summary</h4>
                  <div class="space-y-3 relative z-10">
                    <div class="flex justify-between text-sm text-slate-300 font-medium">
                      <span>Subtotal ({{ orderQuantity }} items)</span>
                      <span>₱{{ formatCurrency(selectedProduct?.price * orderQuantity) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-slate-300 font-medium">
                      <span>Estimated Shipping</span>
                      <span>
                        <span v-if="paymentMethod === 'pick-up'" class="text-amber-400 font-bold bg-amber-400/10 px-2 py-0.5 rounded">WAIVED</span>
                        <span v-else-if="isCalculatingShipping" class="text-slate-500 italic text-xs animate-pulse">Calculating...</span>
                        <span v-else-if="shippingFeeEst === 0" class="text-emerald-400 font-bold bg-emerald-400/10 px-2 py-0.5 rounded">FREE</span>
                        <span v-else>₱{{ formatCurrency(shippingFeeEst) }}</span>
                      </span>
                    </div>
                    
                    <div class="py-2 border-t border-slate-800/80">
                       <span class="text-[10px] uppercase font-bold text-slate-500 tracking-widest block mb-1">Projected Status</span>
                       <span v-if="orderQuantity <= 30" class="text-xs bg-emerald-500/20 text-emerald-300 px-2 py-0.5 rounded border border-emerald-500/30">Confirmed Automatically</span>
                       <span v-else class="text-xs bg-amber-500/20 text-amber-300 px-2 py-0.5 rounded border border-amber-500/30">Requires Manual Confirmation (Bulk)</span>
                    </div>

                    <div class="pt-3 border-t border-slate-800/80 space-y-1">
                      <div class="flex justify-between text-xs text-slate-400">
                        <span>VATable Sales</span>
                        <span v-if="!isCalculatingShipping">₱{{ formatCurrency(getVatableSales((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst))) }}</span>
                        <span v-else>--</span>
                      </div>
                      <div class="flex justify-between text-xs text-slate-400">
                        <span>VAT Amount (12%)</span>
                        <span v-if="!isCalculatingShipping">₱{{ formatCurrency(getVatAmount((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst))) }}</span>
                        <span v-else>--</span>
                      </div>
                    </div>

                    <div class="flex justify-between items-end pt-3 border-t border-slate-700">
                      <span class="text-sm font-medium text-slate-300">Grand Total</span>
                      <span class="text-3xl font-black text-white">₱{{ formatCurrency((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst)) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="p-6 bg-slate-900 border-t border-slate-800 flex gap-3 z-10">
                <Button variant="outline" @click="closeModals" class="flex-1 rounded-xl h-14 border-slate-700 text-slate-300 font-bold hover:bg-slate-800 hover:text-white text-base">Cancel</Button>
                <Button @click="handleOrderSubmit" :disabled="isCalculatingShipping || (addressMode === 'custom' && !customAddress.trim() && paymentMethod !== 'pick-up')" class="flex-[2] rounded-xl h-14 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-bold text-base shadow-lg shadow-indigo-600/20 border-0 transition-all">
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
        <div v-if="isCartAlertOpen || isOrderAlertOpen" class="fixed inset-0 z-[9999] bg-slate-950/80 backdrop-blur-md pointer-events-none"></div>
      </transition>

      <AlertDialog :open="isCartAlertOpen" @update:open="isCartAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold text-white">Add to Cart</AlertDialogTitle>
            <AlertDialogDescription class="text-slate-400 font-medium text-base mt-2">
              Are you sure you want to add <strong class="text-white">{{ orderQuantity }}x {{ selectedProduct?.name }}</strong> to your cart?
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isCartAlertOpen = false" class="rounded-xl font-bold border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white h-11">Go Back</AlertDialogCancel>
            <AlertDialogAction @click="confirmAddToCart" :disabled="isProcessing" class="rounded-xl font-bold bg-indigo-600 hover:bg-indigo-500 text-white h-11 px-6 shadow-md border-0">
              {{ isProcessing ? 'Adding...' : 'Yes, Add to Cart' }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <AlertDialog :open="isOrderAlertOpen" @update:open="isOrderAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl max-w-md z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold text-white flex items-center gap-2">
              <svg v-if="paymentMethod === 'cod'" class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              <svg v-else-if="paymentMethod === 'gcash'" class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
              <svg v-else class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
              Confirm {{ paymentMethod === 'cod' ? 'COD' : (paymentMethod === 'gcash' ? 'GCash' : 'Pick-Up') }} Order
            </AlertDialogTitle>
            <AlertDialogDescription class="text-slate-400 font-medium text-base mt-3 leading-relaxed">
              You are placing an order for <strong class="text-white">{{ orderQuantity }} items</strong>.
              <br/><br/>
              The total amount is <strong class="text-white text-lg">₱{{ formatCurrency((selectedProduct?.price * orderQuantity) + (paymentMethod === 'pick-up' ? 0 : shippingFeeEst)) }}</strong>. 
              <br/>
              <span v-if="paymentMethod === 'gcash'" class="text-indigo-400 text-sm font-semibold mt-2 block">
                You will be redirected to complete your GCash payment securely.
              </span>
              <span v-else-if="paymentMethod === 'pick-up'" class="text-amber-500 text-sm mt-2 block font-semibold">
                You will pay for and pick up your items at the physical store.
              </span>
              <span v-else class="text-sm mt-2 block text-emerald-400">
                This will be collected upon delivery.
              </span>
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isOrderAlertOpen = false" class="rounded-xl font-bold border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="confirmOrderNow" :disabled="isProcessing" class="border-0 rounded-xl font-bold text-white h-11 px-6 shadow-md" :class="paymentMethod === 'cod' ? 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/20' : (paymentMethod === 'pick-up' ? 'bg-amber-600 hover:bg-amber-500 shadow-amber-600/20' : 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/20')">
              {{ isProcessing ? 'Processing...' : (paymentMethod === 'gcash' ? 'Proceed to GCash' : 'Place Order') }}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'

const router = useRouter()
const route = useRoute()
const distributorId = route.params.distributor_id

const products = ref([])
const isLoading = ref(true)
const isProcessing = ref(false)

const isCartModalOpen = ref(false)
const isOrderModalOpen = ref(false)
const isReviewsModalOpen = ref(false)
const showDssModal = ref(false)
const selectedProduct = ref(null)

const isCartAlertOpen = ref(false)
const isOrderAlertOpen = ref(false)
const showMobileMenu = ref(false)

const orderQuantity = ref(1)
const addressMode = ref('default')
const customAddress = ref('')
const paymentMethod = ref('cod')
const shippingFeeEst = ref(0)
const isCalculatingShipping = ref(false)
let shippingCalcTimeout = null

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

const fetchProducts = async () => {
  isLoading.value = true
  try {
    const response = await api.get(`/service-provider/shop/products/${distributorId}`)
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

// DSS (DECISION SUPPORT SYSTEM) COMPUTED LOGIC
const dssRecommendations = computed(() => {
  const ratedProducts = products.value.filter(p => p.review_count > 0 && p.stock > 0);

  const topRated = [...ratedProducts]
    .sort((a, b) => b.rating - a.rating || b.review_count - a.review_count)
    .slice(0, 4);

  const trending = [...ratedProducts]
    .sort((a, b) => b.review_count - a.review_count)
    .filter(p => !topRated.find(t => t.id === p.id))
    .slice(0, 4);

  return { topRated, trending };
});

const goToProductDetails = (id) => {
  router.push(`/serviceProvider/ProductDetails/${id}`)
}

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
  paymentMethod.value = 'cod'
  isOrderModalOpen.value = true
  calculateLiveShipping()
}

watch(selectedProduct, (newProd) => {
  if (newProd && !newProd.distributor_gcash_enabled && paymentMethod.value === 'gcash') {
    paymentMethod.value = 'cod'
  }
})

const openReviewsModal = (product) => {
  selectedProduct.value = product
  isReviewsModalOpen.value = true
}

const closeModals = () => {
  isCartModalOpen.value = false
  isOrderModalOpen.value = false
  isReviewsModalOpen.value = false
  setTimeout(() => {
    selectedProduct.value = null
  }, 300)
}

const handleCartSubmit = () => {
  isCartAlertOpen.value = true
}

const handleOrderSubmit = () => {
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
  if (isOrderModalOpen.value) {
    calculateLiveShipping()
  }
})

const calculateLiveShipping = () => {
  if (!selectedProduct.value) return
  
  clearTimeout(shippingCalcTimeout)
  isCalculatingShipping.value = true

  shippingCalcTimeout = setTimeout(async () => {
    try {
      const response = await api.post('/service-provider/shop/shipping-fee', {
        distributor_id: selectedProduct.value.distributor_id,
        distributor_lat: selectedProduct.value.distributor_lat,
        distributor_lng: selectedProduct.value.distributor_lng,
        quantity: orderQuantity.value,
        total_amount: selectedProduct.value.price * orderQuantity.value
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
    const response = await api.post('/service-provider/shop/cart', {
      product_id: selectedProduct.value.id,
      distributor_id: selectedProduct.value.distributor_id,
      quantity: orderQuantity.value
    })

    if (response.data.success) {
      toast.success(`${orderQuantity.value}x ${selectedProduct.value.name} added to cart!`)
      isCartAlertOpen.value = false 
      closeModals() 
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
        <div class="company-name">${receiptData.items[0]?.distributor_name || 'E-COMMERCE CHECKOUT'}</div>
        <br/>
        <div style="font-size: 14pt; font-weight: bold; letter-spacing: 2px;">OFFICIAL RECEIPT</div>
      </div>
      <table class="meta-table">
        <tr>
          <td width="60%">
            <strong>BILL TO:</strong><br/>${receiptData.sp_name}<br/>
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
            <td>${receiptData.items[0]?.product_name || 'Multiple Items'}</td>
            <td style="text-align: right;">${receiptData.items[0]?.quantity || ''}</td>
            <td style="text-align: right;">${formatCurrency(receiptData.items[0]?.price || 0)}</td>
            <td style="text-align: right;">${formatCurrency(receiptData.items[0]?.total || 0)}</td>
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

    const response = await api.post('/service-provider/shop/order-now', payload)

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
        closeModals() 
        await fetchProducts() 
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
    const response = await api.post('/service-provider/shop/verify-gcash', { order_number: orderNumber }) 
    if (response.data.success) {
      toast.success('Payment Confirmed!', { description: 'Your order has been recorded successfully.' })
      
      if (response.data.receipt_data) {
        downloadReceipt(response.data.receipt_data)
      }
      
      router.replace({ query: {} })
    }
  } catch (error) {
    toast.error('Payment Verification Failed', { description: error.response?.data?.message || 'The payment session could not be verified or was already processed.' })
    router.replace({ query: {} })
  } finally {
    fetchProducts()
  }
}

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
  if (route.query.order_number) { 
    verifyGcashPayment(route.query.order_number) 
  } else {
    fetchProducts()
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
</style>