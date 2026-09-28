<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans text-slate-900">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 md:px-6 h-20 flex items-center justify-between">
        <div class="flex flex-col">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-600 rounded-lg text-white">
              <Package2 class="w-6 h-6" />
            </div>
            <h1 class="text-xl font-bold tracking-tight text-slate-800">{{ supplierName }}</h1>
          </div>
          <p class="text-sm text-slate-500 mt-0.5 ml-11">Raw Materials & Products Catalog</p>
        </div>
        
        <div class="flex items-center gap-6">
          <div class="relative hidden md:block w-80">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <Input 
              type="text" 
              v-model="searchQuery"
              placeholder="Search materials..." 
              class="pl-10 bg-slate-50 border-slate-200 focus-visible:ring-blue-500"
            />
          </div>
          
          <div class="hidden lg:flex items-center gap-6 bg-slate-50 px-4 py-2 rounded-lg border border-slate-100">
            <div class="flex flex-col items-center">
              <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Groups</span>
              <span class="text-lg font-bold text-slate-800">{{ groupedProducts.length }}</span>
            </div>
            <Separator orientation="vertical" class="h-8 bg-slate-200" />
            <div class="flex flex-col items-center">
              <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Variants</span>
              <span class="text-lg font-bold text-slate-800">{{ totalProducts }}</span>
            </div>
            <Separator orientation="vertical" class="h-8 bg-slate-200" />
            <div class="flex flex-col items-center">
              <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Expiring ≤30d</span>
              <span class="text-lg font-bold"
                    :class="expiringSoonCount > 0 ? 'text-orange-600' : 'text-slate-800'">
                {{ expiringSoonCount }}
              </span>
            </div>
            <Separator orientation="vertical" class="h-8 bg-slate-200" />
            <div class="flex flex-col items-center">
              <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Expired</span>
              <span class="text-lg font-bold"
                    :class="expiredProductCount > 0 ? 'text-red-600' : 'text-slate-800'">
                {{ expiredProductCount }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- ── Expiry alert bar ──────────────────────────────────────────
         Shown whenever something in the catalogue has already lapsed.
         Expired stock is not procurable, so the only way out is to archive
         it and add a fresh batch. -->
    <div v-if="expiredBatchCount > 0"
         class="bg-red-50 border-b border-red-200 px-4 md:px-6 py-3">
      <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-start gap-3">
          <AlertTriangle class="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
          <div>
            <p class="text-sm font-semibold text-red-800">
              {{ expiredBatchCount }} expired {{ expiredBatchCount === 1 ? 'batch' : 'batches' }} in your catalogue
            </p>
            <p class="text-xs text-red-700 mt-0.5">
              Expired stock cannot be procured. Move it to the archive and add a fresh batch to keep selling.
            </p>
          </div>
        </div>
        <Button
          size="sm"
          variant="destructive"
          class="shrink-0"
          :disabled="isArchivingAll"
          @click="confirmArchiveAll"
        >
          <Loader2 v-if="isArchivingAll" class="w-4 h-4 mr-2 animate-spin" />
          <Archive v-else class="w-4 h-4 mr-2" />
          Move All Expired to Archive
        </Button>
      </div>
    </div>

    <div v-else-if="expiringSoonCount > 0"
         class="bg-orange-50 border-b border-orange-200 px-4 md:px-6 py-3">
      <div class="max-w-7xl mx-auto flex items-start gap-3">
        <Clock class="w-5 h-5 text-orange-600 shrink-0 mt-0.5" />
        <p class="text-sm text-orange-800">
          <strong>{{ expiringSoonCount }}</strong>
          {{ expiringSoonCount === 1 ? 'batch expires' : 'batches expire' }}
          within 30 days. Use up the oldest lots first — stock is issued oldest-expiry-first automatically.
        </p>
      </div>
    </div>

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
      <aside class="hidden lg:block lg:col-span-3 space-y-6">
        <Card class="border-slate-200 shadow-sm">
          <CardHeader class="pb-3 border-b border-slate-100">
            <CardTitle class="flex items-center gap-2 text-base font-semibold">
              <Filter class="w-4 h-4" />
              Filters
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-6 pt-6">
            <div class="space-y-3">
              <h4 class="text-sm font-medium text-slate-700">Category</h4>
              <div class="space-y-2 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                <div v-for="category in categories" :key="category.value" class="flex items-center justify-between group">
                  <div class="flex items-center space-x-2">
                    <Checkbox 
                      :id="category.value" 
                      :value="category.value"
                      :model-value="selectedCategories.includes(category.value)"
                      @update:model-value="(checked) => handleCategoryCheck(checked, category.value)"
                    />
                    <label 
                      :for="category.value" 
                      class="text-sm text-slate-600 font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70 cursor-pointer flex items-center gap-2"
                    >
                      <span class="w-2 h-2 rounded-full" :class="getCategoryDotClass(category.value)"></span>
                      {{ category.label }}
                    </label>
                  </div>
                  <span class="text-xs text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded-full">{{ category.count }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <h4 class="text-sm font-medium text-slate-700">Product Type</h4>
              <Select v-model="selectedType">
                <SelectTrigger>
                  <SelectValue placeholder="All Types" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem v-for="type in availableTypes" :key="type" :value="type">
                    {{ type }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-3">
              <h4 class="text-sm font-medium text-slate-700">Size</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="size in allSizes" 
                  :key="size"
                  variant="outline"
                  class="cursor-pointer transition-all hover:border-blue-400 hover:text-blue-600"
                  :class="{ 'bg-blue-50 border-blue-200 text-blue-700': selectedSizes.includes(size) }"
                  @click="toggleSize(size)"
                >
                  {{ size }}
                </Badge>
              </div>
            </div>

            <div v-if="hasColorCategorySelected" class="space-y-3">
              <h4 class="text-sm font-medium text-slate-700">Color</h4>
              <div class="grid grid-cols-5 gap-2">
                <button 
                  v-for="color in colorOptions" 
                  :key="color.value"
                  class="w-8 h-8 rounded-full border border-slate-200 shadow-sm transition-transform hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-blue-500"
                  :class="{ 'ring-2 ring-offset-1 ring-slate-400': selectedColor === color.value }"
                  :style="{ backgroundColor: color.value }"
                  :title="color.name"
                  @click="selectedColor = selectedColor === color.value ? '' : color.value"
                ></button>
              </div>
            </div>

            <Button 
              variant="outline" 
              class="w-full mt-4 text-slate-500 hover:text-slate-700"
              @click="clearFilters"
            >
              <X class="w-4 h-4 mr-2" />
              Clear All Filters
            </Button>
          </CardContent>
        </Card>
        
        <Card class="bg-blue-600 text-white border-none shadow-md overflow-hidden relative">
          <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
          <CardHeader class="pb-2">
            <CardTitle class="flex items-center gap-2 text-white">
              <Settings class="w-4 h-4" />
              Management
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button 
              variant="secondary" 
              class="w-full bg-white text-blue-700 hover:bg-blue-50 font-semibold shadow-sm border-none"
              @click="openAddModal"
            >
              Add New Material
            </Button>
            <Button 
              variant="ghost" 
              class="w-full text-blue-100 hover:text-white hover:bg-blue-500"
              @click="exportCatalog"
            >
              Export Catalog
            </Button>
          </CardContent>
        </Card>
      </aside>

      <div class="col-span-1 lg:col-span-9 space-y-6">
        <div class="lg:hidden flex gap-4 mb-4">
          <div class="relative flex-1">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" />
            <Input 
              type="text" 
              v-model="searchQuery"
              placeholder="Search products..." 
              class="pl-10"
            />
          </div>
          <Button variant="outline" @click="showMobileFilters = true">
            <Filter class="w-4 h-4" />
          </Button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-4 rounded-lg border border-slate-200 shadow-sm">
          <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
            Available Materials 
            <Badge variant="secondary" class="ml-1">{{ groupedProducts.length }} groups</Badge>
          </h2>
          <div class="flex items-center gap-2">
            <span class="text-sm text-slate-500 whitespace-nowrap">Sort by:</span>
            <Select v-model="sortOption">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="Sort order" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="newest">Newest First</SelectItem>
                <SelectItem value="name">Name (A-Z)</SelectItem>
                <SelectItem value="price_low">Price: Low to High</SelectItem>
                <SelectItem value="price_high">Price: High to Low</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
        
        <div v-if="isLoading" class="flex justify-center items-center py-20">
            <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
        </div>

        <div v-else-if="groupedProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div 
            v-for="group in sortedGroups" 
            :key="group.key"
            class="group bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col overflow-hidden"
          >
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
              <img 
                v-if="group.representativeImage" 
                :src="getFullImageUrl(group.representativeImage)" 
                :alt="group.name"
                @error="handleImageError"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              >
              <div 
                v-else 
                class="w-full h-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105"
                :style="{ backgroundColor: group.representativeColor ? `${group.representativeColor}20` : '#f1f5f9' }"
              >
                <div 
                  class="w-16 h-16 rounded-lg flex items-center justify-center shadow-inner border"
                  :style="{ backgroundColor: group.representativeColor || '#cbd5e1' }"
                >
                  <Package class="w-8 h-8 text-white/80 drop-shadow-md" />
                </div>
              </div>
              
              <div class="absolute top-3 left-3 flex flex-col gap-2">
                <Badge :class="getCategoryBadgeClass(group.category)" class="shadow-sm border-none">
                  {{ getCategoryShortName(group.category) }}
                </Badge>
                <Badge variant="secondary" class="shadow-sm border-none bg-black/50 text-white hover:bg-black/60">
                  {{ group.variants.length }} variants
                </Badge>
              </div>

              <div class="absolute bottom-3 right-3 flex gap-1">
                <span class="bg-black/50 text-white text-xs px-2 py-1 rounded-full backdrop-blur-sm">
                  {{ group.variants.length }} sizes
                </span>
              </div>
            </div>
            
            <div class="p-4 flex-1 flex flex-col">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-bold text-slate-800 line-clamp-1 text-base group-hover:text-blue-600 transition-colors">{{ group.name }}</h3>
                <span class="font-bold text-blue-700 bg-blue-50 px-2 py-1 rounded text-sm">
                  ₱{{ formatPrice(group.minPrice) }} 
                  <span v-if="group.minPrice !== group.maxPrice"> - ₱{{ formatPrice(group.maxPrice) }}</span>
                </span>
              </div>
              
              <div class="flex items-center gap-2 mb-3 text-xs text-slate-500">
                <span class="bg-slate-100 px-2 py-0.5 rounded">{{ group.type }}</span>
                <span>•</span>
                <span>{{ group.category }}</span>
              </div>
              
              <p v-if="group.description" class="text-sm text-slate-600 line-clamp-2 mb-4 h-10">
                {{ truncateDescription(group.description) }}
              </p>
              
              <div class="mt-auto flex gap-2 pt-2 border-t border-slate-100">
                <Button 
                  variant="outline" 
                  size="sm" 
                  class="flex-1 border-blue-200 text-blue-600 hover:bg-blue-50 hover:text-blue-700"
                  @click="addVariant(group.variants[0])"
                >
                  <Plus class="w-3.5 h-3.5 mr-1" />
                  Variant
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  class="flex-1 border-slate-200 hover:bg-slate-50 hover:text-blue-600"
                  @click="toggleGroupExpand(group.key)"
                >
                  <ChevronDown class="w-3.5 h-3.5 mr-1" :class="{'rotate-180': expandedGroups.includes(group.key)}" />
                  Variants
                </Button>
              </div>

              <!-- "Move to Archive" per group. Only rendered when the group
                   actually holds lapsed stock, so it never becomes a button
                   whose only answer is "nothing to do". -->
              <div
                v-if="expiredBatchesInGroup(group).length > 0"
                class="mt-2 flex items-center justify-between gap-2 rounded-lg bg-red-50 border border-red-100 px-2.5 py-2"
              >
                <span class="text-[11px] text-red-700 font-medium">
                  {{ expiredBatchesInGroup(group).length }} expired
                  {{ expiredBatchesInGroup(group).length === 1 ? 'batch' : 'batches' }}
                </span>
                <Button
                  size="sm"
                  variant="outline"
                  class="h-6 px-2 text-[11px] border-red-200 text-red-700 hover:bg-red-100"
                  :disabled="isArchivingGroup === group.key"
                  @click="archiveGroupExpired(group)"
                >
                  <Loader2 v-if="isArchivingGroup === group.key" class="w-3 h-3 mr-1 animate-spin" />
                  <Archive v-else class="w-3 h-3 mr-1" />
                  Move to Archive
                </Button>
              </div>

              <div v-if="expandedGroups.includes(group.key)" class="mt-3 pt-3 border-t border-slate-200 space-y-2">
                <div 
                  v-for="variant in group.variants" 
                  :key="variant.id"
                  class="flex flex-col bg-slate-50 p-2 rounded-lg text-sm gap-1.5"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                      <span class="font-mono text-xs text-slate-500">{{ variant.size }}</span>
                      <span class="text-slate-700 truncate">{{ variant.sku_code || 'No SKU' }}</span>
                      <span v-if="variant.color_code" class="w-3 h-3 rounded-full border border-slate-300 shrink-0" :style="{ backgroundColor: variant.color_code }"></span>
                      <span class="font-bold text-blue-600 ml-auto">₱{{ formatPrice(variant.price) }}</span>
                    </div>
                    <div class="flex gap-0.5 ml-1">
                      <Button variant="ghost" size="icon" class="h-7 w-7" :title="`Batches of ${variant.name}`" @click="openBatchPanel(variant)">
                        <Layers class="w-3 h-3" />
                      </Button>
                      <Button variant="ghost" size="icon" class="h-7 w-7" :title="`Add stock to ${variant.name}`" @click="openRestock(variant)">
                        <PackagePlus class="w-3 h-3" />
                      </Button>
                      <Button variant="ghost" size="icon" class="h-7 w-7" @click="editProduct(variant)">
                        <Pencil class="w-3 h-3" />
                      </Button>
                      <Button variant="ghost" size="icon" class="h-7 w-7 text-red-500 hover:text-red-700" @click="deleteProduct(variant.id)">
                        <Trash2 class="w-3 h-3" />
                      </Button>
                    </div>
                  </div>

                  <!-- ── Batch status line ──────────────────────────────
                       `quantity` alone would hide the problem: 100 units on
                       hand can be unsellable if every lot has lapsed. Showing
                       available, reserved and the next expiry side by side
                       keeps that visible. -->
                  <div class="flex items-center gap-2 flex-wrap pl-1">
                    <span
                      class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded border text-[11px] font-medium"
                      :class="expiryBadge(variant.expiration_date).classes"
                    >
                      <span class="w-1.5 h-1.5 rounded-full" :class="expiryBadge(variant.expiration_date).dot"></span>
                      {{ expiryBadge(variant.expiration_date).label }}
                    </span>

                    <span class="text-[11px] text-slate-600">
                      <span class="font-semibold text-slate-800">{{ availableOf(variant) }}</span>
                      available
                    </span>

                    <span v-if="variant.reserved_quantity > 0" class="text-[11px] text-amber-700">
                      · {{ variant.reserved_quantity }} reserved
                    </span>

                    <span class="text-[11px] text-slate-500">
                      · {{ liveBatchesOf(variant).length }}
                      {{ liveBatchesOf(variant).length === 1 ? 'batch' : 'batches' }}
                    </span>
                  </div>

                  <div v-if="variant.is_expired" class="pl-1">
                    <span class="text-[11px] text-red-700 inline-flex items-center gap-1">
                      <AlertTriangle class="w-3 h-3" />
                      Not procurable — no stock within date
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="flex flex-col items-center justify-center py-20 px-4 text-center bg-white rounded-xl border border-dashed border-slate-300">
          <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
            <SearchX class="w-10 h-10 text-slate-400" />
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">No materials found</h3>
          <p class="text-slate-500 max-w-sm mb-6">We couldn't find any materials matching your filters. Try adjusting your search or filters.</p>
          <Button @click="clearFilters">Clear All Filters</Button>
        </div>
      </div>
    </main>

    <Dialog :open="showAddModal" @update:open="closeModal">
      <DialogContent class="sm:max-w-[600px] p-0 gap-0 overflow-hidden max-h-[90vh] flex flex-col">
        <DialogHeader class="p-6 pb-2">
          <DialogTitle class="flex items-center gap-2 text-xl">
            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
              <PackagePlus v-if="!isEditing && !isVariant" class="w-5 h-5" />
              <CopyPlus v-else-if="isVariant" class="w-5 h-5" />
              <Pencil v-else class="w-5 h-5" />
            </div>
            {{ modalTitle }}
          </DialogTitle>
        </DialogHeader>
        
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
          <div class="relative flex justify-between">
            <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-200 -z-0 -translate-y-1/2 rounded"></div>
            <div 
              class="absolute top-1/2 left-0 h-0.5 bg-blue-600 -z-0 -translate-y-1/2 rounded transition-all duration-300"
              :style="{ width: `${((currentStep - 1) / (wizardSteps.length - 1)) * 100}%` }"
            ></div>

            <div 
              v-for="(step, index) in wizardSteps" 
              :key="index"
              class="relative z-10 flex flex-col items-center gap-2 group cursor-default"
              @click="currentStep > index + 1 ? currentStep = index + 1 : null"
            >
              <div 
                class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 border-2"
                :class="[
                  currentStep === index + 1 ? 'bg-blue-600 border-blue-600 text-white scale-110' : 
                  currentStep > index + 1 ? 'bg-blue-600 border-blue-600 text-white' : 
                  'bg-white border-slate-300 text-slate-400'
                ]"
              >
                <Check v-if="currentStep > index + 1" class="w-4 h-4" />
                <span v-else>{{ index + 1 }}</span>
              </div>
              <span 
                class="text-[10px] font-medium uppercase tracking-wider absolute -bottom-6 w-32 text-center transition-colors duration-300"
                :class="currentStep >= index + 1 ? 'text-blue-700' : 'text-slate-400'"
              >
                {{ step.label }}
              </span>
            </div>
          </div>
          <div class="h-4"></div> 
        </div>

        <div class="flex-1 overflow-y-auto p-6">
          <form @submit.prevent="handleSubmit" id="productForm">
            
            <div v-if="currentStep === 1" class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="category" class="text-slate-700">Category <span class="text-red-500">*</span></Label>
                  <Select 
                    v-model="newProduct.category" 
                    @update:modelValue="onCategoryChange"
                    :disabled="isVariant"
                  >
                    <SelectTrigger id="category" :class="{'border-red-300': !newProduct.category && showValidation}">
                      <SelectValue placeholder="Select Category" />
                    </SelectTrigger>
                    <SelectContent class="max-h-[300px]">
                      <SelectGroup v-for="(group, label) in groupedCategories" :key="label">
                        <SelectLabel>{{ label }}</SelectLabel>
                        <SelectItem v-for="cat in group" :key="cat.value" :value="cat.value">
                          {{ cat.label }}
                        </SelectItem>
                      </SelectGroup>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-2">
                  <Label for="type" class="text-slate-700">Type <span class="text-red-500">*</span></Label>
                  <Select 
                    v-model="newProduct.type" 
                    :disabled="!newProduct.category || isVariant"
                  >
                    <SelectTrigger id="type" :class="{'border-red-300': !newProduct.type && showValidation}">
                      <SelectValue placeholder="Select Type" />
                    </SelectTrigger>
                    <SelectContent class="max-h-[300px]">
                      <SelectItem v-for="type in filteredTypes" :key="type" :value="type">
                        {{ type }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="name" class="text-slate-700">Material Name <span class="text-red-500">*</span></Label>
                <Input 
                  id="name" 
                  v-model="newProduct.name" 
                  placeholder="e.g. Premium Interior Latex" 
                  :class="{'border-red-300': !newProduct.name && showValidation}"
                  :disabled="isVariant"
                />
              </div>

              <div class="grid grid-cols-3 gap-4">
                <div class="space-y-2 col-span-1">
                  <Label for="sku" class="text-slate-700">SKU Code</Label>
                  <Input id="sku" v-model="newProduct.sku_code" placeholder="Optional" />
                </div>

                <div class="space-y-2 col-span-1">
                  <Label for="size" class="text-slate-700">Size <span class="text-red-500">*</span></Label>
                  <Select v-model="newProduct.size">
                    <SelectTrigger id="size" :class="{'border-red-300': !newProduct.size && showValidation}">
                      <SelectValue placeholder="Select Size" />
                    </SelectTrigger>
                    <SelectContent class="max-h-[300px]">
                      <SelectGroup v-if="sizeOptions.length">
                         <SelectItem v-for="size in sizeOptions" :key="size" :value="size">{{ size }}</SelectItem>
                      </SelectGroup>
                      <div v-else class="p-2 text-sm text-slate-500">Select category first</div>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-2 col-span-1">
                  <Label for="weight" class="text-slate-700">Weight (kg) <span class="text-red-500">*</span></Label>
                  <Input 
                    id="weight" 
                    type="number" 
                    v-model="newProduct.weight" 
                    placeholder="10" 
                    min="0" 
                    step="0.01"
                    :class="{'border-red-300': !newProduct.weight && showValidation}" 
                  />
                </div>
              </div>

              <div v-if="showColorField" class="space-y-2">
                <Label for="color_code" class="text-slate-700">Color (Hex)</Label>
                <div class="flex gap-3">
                  <Input 
                    id="color_code" 
                    v-model="newProduct.color_code" 
                    placeholder="#FFFFFF" 
                    class="font-mono"
                  />
                  <div 
                    class="w-10 h-10 rounded border border-slate-200 shadow-sm shrink-0"
                    :style="{ backgroundColor: newProduct.color_code || 'transparent' }"
                  ></div>
                </div>
              </div>
            </div>

            <div v-else-if="currentStep === 2" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                  <Label for="price" class="text-slate-700">Selling Price (₱) <span class="text-red-500">*</span></Label>
                  <Input 
                    id="price" 
                    type="number" 
                    v-model="newProduct.price" 
                    placeholder="0.00" 
                    min="0" 
                    step="0.01"
                    :class="{'border-red-300': !newProduct.price && showValidation}"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="min_order" class="text-slate-700">Min Order Qty</Label>
                  <Input 
                    id="min_order" 
                    type="number" 
                    v-model="newProduct.min_order" 
                    placeholder="e.g. 1" 
                    min="1" 
                  />
                </div>
                <div class="space-y-2">
                  <Label for="max_order" class="text-slate-700">Max Order Qty</Label>
                  <Input 
                    id="max_order" 
                    type="number" 
                    v-model="newProduct.max_order" 
                    placeholder="e.g. 1000" 
                    min="1" 
                  />
                </div>
              </div>

              <div class="space-y-2">
                <Label for="description" class="text-slate-700">Description</Label>
                <Textarea
                  id="description"
                  v-model="newProduct.description"
                  placeholder="Describe the material details..."
                  class="resize-none h-32"
                />
              </div>

              <!-- Batch details. Hidden while EDITING on purpose: stock only
                   moves through a restock, which creates a new dated batch.
                   Letting a plain edit rewrite the total would desync it from
                   the lots actually on hand. -->
              <template v-if="!isEditing">
                <Separator />

                <div class="flex items-start gap-3 rounded-lg bg-blue-50 border border-blue-100 p-3">
                  <Layers class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                  <p class="text-xs text-blue-700 leading-relaxed">
                    Stock is tracked by <strong>batch</strong> — every delivery carries its own
                    quantity and expiration date, and the oldest lot is always issued first.
                  </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label for="quantity" class="text-slate-700">
                      Quantity <span class="text-red-500">*</span>
                    </Label>
                    <Input
                      id="quantity"
                      type="number"
                      v-model="newProduct.quantity"
                      placeholder="e.g. 100"
                      min="1"
                      step="1"
                      :class="{ 'border-red-300': quantityError && !newProduct.quantity }"
                      @input="quantityError = ''"
                    />
                    <p v-if="quantityError" class="text-xs text-red-600">{{ quantityError }}</p>
                  </div>

                  <div class="space-y-2">
                    <Label for="expiration_date" class="text-slate-700">
                      Expiration Date
                      <span v-if="requiresExpiration" class="text-red-500">*</span>
                      <span v-else class="text-slate-400 font-normal">(optional)</span>
                    </Label>
                    <Input
                      id="expiration_date"
                      type="date"
                      v-model="newProduct.expiration_date"
                      :min="minimumExpirationDate"
                      :class="{
                        'border-red-300': expirationError,
                        'bg-slate-50': !requiresExpiration && !newProduct.expiration_date
                      }"
                      @input="expirationError = ''"
                    />
                    <p v-if="expirationError" class="text-xs text-red-600">{{ expirationError }}</p>
                    <p v-else-if="requiresExpiration" class="text-xs text-slate-500">
                      Must be {{ formatDate(minimumExpirationDate) }} or later — 1 year from today.
                    </p>
                    <p v-else class="text-xs text-slate-500">
                      Optional for {{ newProduct.category || 'this category' }} — leave blank for
                      non-perishable goods like tools, accessories and packaging.
                    </p>
                  </div>
                </div>
              </template>
            </div>

            <div v-else-if="currentStep === 3" class="space-y-6">
              <div class="space-y-2">
                <Label class="text-slate-700">Material Image</Label>
                <div 
                  @click="triggerFileInput"
                  @dragover.prevent 
                  @drop.prevent="handleFileDrop"
                  class="border-2 border-dashed rounded-xl p-8 flex flex-col items-center justify-center text-center cursor-pointer transition-all duration-200 group"
                  :class="imagePreview ? 'border-green-300 bg-green-50/50' : 'border-slate-300 hover:border-blue-400 hover:bg-blue-50/50'"
                >
                  <input type="file" ref="fileInput" class="hidden" @change="handleImageUpload" accept="image/*" />
                  
                  <div v-if="imagePreview || newProduct.image_url" class="relative w-full max-w-[200px] aspect-square mb-4 group-hover:scale-105 transition-transform">
                    <img 
                      :src="imagePreview || getFullImageUrl(newProduct.image_url)" 
                      class="w-full h-full object-cover rounded-lg shadow-md" 
                      alt="Preview"
                    />
                    <Button 
                      type="button"
                      variant="destructive" 
                      size="icon" 
                      class="absolute -top-2 -right-2 h-7 w-7 rounded-full shadow-sm"
                      @click.stop="removeImage"
                    >
                      <X class="w-3 h-3" />
                    </Button>
                  </div>

                  <div v-else class="flex flex-col items-center">
                    <div class="p-4 bg-slate-100 rounded-full mb-3 group-hover:bg-blue-100 transition-colors">
                      <UploadCloud class="w-8 h-8 text-slate-400 group-hover:text-blue-600" />
                    </div>
                    <p class="text-sm font-semibold text-slate-700 mb-1">Click to upload or drag and drop</p>
                    <p class="text-xs text-slate-500">PNG, JPG up to 2MB</p>
                  </div>
                </div>
              </div>
              
              <div class="bg-blue-50 p-4 rounded-lg flex items-start gap-3">
                <Info class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" />
                <p class="text-sm text-blue-700 leading-relaxed">
                  Images help identify materials quickly. If you don't upload one, we'll generate a placeholder based on the material's color or category.
                </p>
              </div>
            </div>

            <div v-else-if="currentStep === 4" class="space-y-6">
              <div class="bg-slate-50 rounded-lg p-5 border border-slate-100 space-y-4">
                <h4 class="font-semibold text-slate-800 flex items-center gap-2 border-b border-slate-200 pb-2">
                  <ClipboardCheck class="w-4 h-4 text-slate-500" />
                  Material Summary
                </h4>
                
                <div class="grid grid-cols-2 gap-y-4 text-sm">
                   <div><span class="text-slate-500 block text-xs uppercase tracking-wide">Name</span> <span class="font-medium text-slate-800">{{ newProduct.name }}</span></div>
                   <div><span class="text-slate-500 block text-xs uppercase tracking-wide">Category</span> <span class="font-medium text-slate-800">{{ newProduct.category }}</span></div>
                   
                   <div><span class="text-slate-500 block text-xs uppercase tracking-wide">Type</span> <span class="font-medium text-slate-800">{{ newProduct.type }}</span></div>
                   <div><span class="text-slate-500 block text-xs uppercase tracking-wide">Size</span> <span class="font-medium text-slate-800">{{ newProduct.size }}</span></div>
                   
                   <div><span class="text-slate-500 block text-xs uppercase tracking-wide">Weight</span> <span class="font-medium text-slate-800">{{ newProduct.weight }} kg</span></div>
                   <div><span class="text-slate-500 block text-xs uppercase tracking-wide">Selling Price</span> <span class="font-bold text-green-600">₱{{ formatPrice(newProduct.price) }}</span></div>
                   
                   <div class="col-span-2"><span class="text-slate-500 block text-xs uppercase tracking-wide">Limits (Min/Max)</span> <span class="font-medium text-slate-800">{{ newProduct.min_order || 1 }} - {{ newProduct.max_order || 'No Max' }}</span></div>

                   <!-- Opening batch. Shown only when creating, because an edit
                        never touches stock. -->
                   <template v-if="!isEditing">
                     <div>
                       <span class="text-slate-500 block text-xs uppercase tracking-wide">Opening Quantity</span>
                       <span class="font-bold text-slate-800">{{ newProduct.quantity || 0 }} units</span>
                     </div>
                     <div>
                       <span class="text-slate-500 block text-xs uppercase tracking-wide">Expires</span>
                       <span v-if="newProduct.expiration_date" class="font-medium text-slate-800">
                         {{ formatDate(newProduct.expiration_date) }}
                       </span>
                       <span v-else class="font-medium text-slate-400 italic">
                         {{ requiresExpiration ? 'Required — not set' : 'No expiry (non-perishable)' }}
                       </span>
                     </div>
                   </template>

                   <div v-if="newProduct.color_code" class="col-span-2">
                     <span class="text-slate-500 block text-xs uppercase tracking-wide mb-1">Color</span> 
                     <div class="flex items-center gap-2">
                       <div class="w-4 h-4 rounded-full border border-slate-300" :style="{ backgroundColor: newProduct.color_code }"></div>
                       <span class="font-mono text-slate-700">{{ newProduct.color_code }}</span>
                     </div>
                   </div>
                </div>
              </div>

              <div class="p-4 rounded-lg bg-blue-50 border border-blue-100">
                <p class="text-sm text-blue-800 text-center">
                  Almost done! Please verify the information above before clicking 
                  <strong>{{ isEditing ? 'Update Material' : (isVariant ? 'Add Variant' : 'Add Material') }}</strong>.
                </p>
              </div>
            </div>

          </form>
        </div>

        <div class="p-6 pt-2 bg-white border-t border-slate-100 flex justify-between items-center mt-auto">
          <Button 
            type="button" 
            variant="ghost" 
            @click="prevStep" 
            :disabled="currentStep === 1"
            class="text-slate-500 hover:text-slate-800"
          >
            <ChevronLeft class="w-4 h-4 mr-1" /> Back
          </Button>

          <Button 
            v-if="currentStep < 4" 
            type="button" 
            @click="nextStep"
            class="bg-slate-900 hover:bg-slate-800 text-white"
          >
            Next Step <ChevronRight class="w-4 h-4 ml-1" />
          </Button>

          <Button 
            v-else 
            @click="handleSubmit"
            :disabled="isSubmitting"
            class="bg-blue-600 hover:bg-blue-700 text-white min-w-[140px]"
          >
            <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
            {{ isEditing ? 'Update Material' : (isVariant ? 'Add Variant' : 'Add Material') }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showMobileFilters" @update:open="showMobileFilters = $event">
      <DialogContent class="h-full max-h-screen w-full sm:max-w-md overflow-y-auto rounded-none">
        <DialogHeader>
          <DialogTitle>Filters</DialogTitle>
        </DialogHeader>
        <div class="space-y-6 py-4">
             <div class="space-y-3">
              <h4 class="text-sm font-medium text-slate-700">Category</h4>
              <div class="space-y-2">
                <div v-for="category in categories" :key="category.value" class="flex items-center justify-between">
                  <div class="flex items-center space-x-2">
                    <Checkbox 
                      :id="`m-${category.value}`" 
                      :value="category.value"
                      :model-value="selectedCategories.includes(category.value)"
                      @update:model-value="(checked) => handleCategoryCheck(checked, category.value)"
                    />
                    <label :for="`m-${category.value}`" class="text-sm text-slate-600">{{ category.label }}</label>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="space-y-3">
              <h4 class="text-sm font-medium text-slate-700">Size</h4>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="size in allSizes" 
                  :key="size"
                  variant="outline"
                  class="cursor-pointer"
                  :class="{ 'bg-blue-50 border-blue-200 text-blue-700': selectedSizes.includes(size) }"
                  @click="toggleSize(size)"
                >
                  {{ size }}
                </Badge>
              </div>
            </div>

            <Button class="w-full" @click="showMobileFilters = false">View Results</Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- ── Batch panel ──────────────────────────────────────────────
         Every lot behind one product, soonest expiry first, matching the
         order stock is actually issued in. -->
    <Dialog :open="showBatchPanel" @update:open="closeBatchPanel">
      <DialogContent class="sm:max-w-[640px] max-h-[85vh] overflow-hidden flex flex-col p-0 gap-0">
        <DialogHeader class="p-6 pb-3">
          <DialogTitle class="flex items-center gap-2">
            <div class="p-2 bg-blue-100 rounded-lg text-blue-600">
              <Layers class="w-5 h-5" />
            </div>
            <div class="min-w-0">
              <p class="truncate">{{ batchProduct?.name || 'Batches' }}</p>
              <p v-if="batchProduct" class="text-xs font-normal text-slate-500 mt-0.5">
                {{ batchProduct.category }}
                <span v-if="batchProduct.size"> · {{ batchProduct.size }}</span>
              </p>
            </div>
          </DialogTitle>
        </DialogHeader>

        <div class="px-6 pb-3">
          <div v-if="batchSummary" class="grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg bg-slate-50 border border-slate-200 py-2">
              <p class="text-lg font-bold text-slate-800">{{ batchSummary.available }}</p>
              <p class="text-[10px] uppercase tracking-wide text-slate-500">Available</p>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 py-2">
              <p class="text-lg font-bold text-slate-800">{{ batchSummary.reserved }}</p>
              <p class="text-[10px] uppercase tracking-wide text-slate-500">Reserved</p>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 py-2">
              <p class="text-lg font-bold"
                 :class="batchSummary.expired > 0 ? 'text-red-600' : 'text-slate-800'">
                {{ batchSummary.expired }}
              </p>
              <p class="text-[10px] uppercase tracking-wide text-slate-500">Expired</p>
            </div>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto px-6 pb-4">
          <div v-if="isLoadingBatches" class="flex justify-center items-center py-12">
            <Loader2 class="w-6 h-6 animate-spin text-blue-600" />
          </div>

          <div v-else-if="batchRows.length === 0"
               class="py-10 text-center text-sm text-slate-500">
            No batches yet. Add stock to start selling this product.
          </div>

          <div v-else class="space-y-2">
            <div
              v-for="row in batchRows"
              :key="row.batch.id"
              class="rounded-lg border p-3 flex items-center gap-3"
              :class="row.expired
                ? 'border-red-200 bg-red-50/60'
                : 'border-slate-200 bg-white'"
            >
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="font-mono text-xs text-slate-600">{{ row.batch.batch_code }}</span>
                  <span
                    class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded border text-[10px] font-medium"
                    :class="expiryBadge(row.batch.expiration_date).classes"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="expiryBadge(row.batch.expiration_date).dot"></span>
                    {{ expiryBadge(row.batch.expiration_date).label }}
                  </span>
                  <span
                    v-if="row.reserved > 0"
                    class="text-[10px] text-amber-700 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded"
                  >
                    {{ row.reserved }} reserved
                  </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                  Added {{ formatDate(row.batch.created_at) }}
                  <span v-if="row.batch.expiration_date">
                    · expires {{ formatDate(row.batch.expiration_date) }}
                  </span>
                </p>
                <p v-if="row.batch.archive_reason" class="text-[11px] text-slate-400 mt-0.5">
                  {{ row.batch.archive_reason }}
                </p>
              </div>

              <div class="text-right shrink-0">
                <p class="font-bold text-slate-800">{{ row.batch.quantity }}</p>
                <p class="text-[10px] text-slate-500 uppercase tracking-wide">units</p>
              </div>

              <Button
                v-if="row.expired"
                size="sm"
                variant="outline"
                class="h-7 px-2 text-[11px] border-red-200 text-red-700 hover:bg-red-100 shrink-0"
                :disabled="archivingBatchId === row.batch.id"
                @click="archiveBatch(row.batch)"
              >
                <Loader2 v-if="archivingBatchId === row.batch.id" class="w-3 h-3 mr-1 animate-spin" />
                <Archive v-else class="w-3 h-3 mr-1" />
                Archive
              </Button>
            </div>
          </div>
        </div>

        <div class="p-4 border-t border-slate-100 flex justify-between items-center gap-3">
          <p class="text-xs text-slate-500">
            {{ batchRows.length }} {{ batchRows.length === 1 ? 'lot' : 'lots' }}, oldest expiry first
          </p>
          <div class="flex gap-2">
            <Button variant="outline" size="sm" @click="closeBatchPanel">Close</Button>
            <Button size="sm" @click="openRestockFromPanel">
              <PackagePlus class="w-4 h-4 mr-1" />
              Add Stock
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- ── Restock ───────────────────────────────────────────────────
         Adding stock to an existing product always opens a NEW batch, so
         this form asks for the quantity and the expiration date of the
         delivery being booked in. -->
    <Dialog :open="showRestockModal" @update:open="showRestockModal = $event">
      <DialogContent class="sm:max-w-[480px]">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
            <div class="p-2 bg-emerald-100 rounded-lg text-emerald-600">
              <PackagePlus class="w-5 h-5" />
            </div>
            Add Stock
          </DialogTitle>
          <p v-if="restockTarget" class="text-sm text-slate-500">
            {{ restockTarget.name }}
            <span v-if="restockTarget.size"> · {{ restockTarget.size }}</span>
            — currently {{ availableOf(restockTarget) }} available
          </p>
        </DialogHeader>

        <div class="space-y-4 py-2">
          <div class="space-y-2">
            <Label for="restock_quantity" class="text-slate-700">
              Quantity Received <span class="text-red-500">*</span>
            </Label>
            <Input
              id="restock_quantity"
              type="number"
              v-model="restockForm.quantity"
              placeholder="e.g. 50"
              min="1"
              step="1"
              :class="{ 'border-red-300': restockErrors.quantity }"
            />
            <p v-if="restockErrors.quantity" class="text-xs text-red-600">{{ restockErrors.quantity }}</p>
          </div>

          <div class="space-y-2">
            <Label for="restock_expiration" class="text-slate-700">
              Expiration Date of this Batch
              <span v-if="restockRequiresExpiration" class="text-red-500">*</span>
              <span v-else class="text-slate-400 font-normal">(optional)</span>
            </Label>
            <Input
              id="restock_expiration"
              type="date"
              v-model="restockForm.expiration_date"
              :min="minimumExpirationDate"
              :class="{ 'border-red-300': restockErrors.expiration_date }"
            />
            <p v-if="restockErrors.expiration_date" class="text-xs text-red-600">
              {{ restockErrors.expiration_date }}
            </p>
            <p v-else-if="restockRequiresExpiration" class="text-xs text-slate-500">
              Must be {{ formatDate(minimumExpirationDate) }} or later — 1 year from today.
            </p>
            <p v-else class="text-xs text-slate-500">
              Optional for {{ restockTarget?.category }} — tools, accessories and packaging
              do not need a shelf life.
            </p>
          </div>

          <div class="space-y-2">
            <Label for="restock_batch_code" class="text-slate-700">Batch Reference</Label>
            <Input
              id="restock_batch_code"
              v-model="restockForm.batch_code"
              placeholder="Optional — generated if blank"
              class="font-mono"
            />
            <p class="text-xs text-slate-500">
              Leave blank to have one generated. Reusing a reference that already exists
              will add to that same lot.
            </p>
          </div>

          <div class="flex items-start gap-3 rounded-lg bg-slate-50 border border-slate-200 p-3">
            <Info class="w-4 h-4 text-slate-500 shrink-0 mt-0.5" />
            <p class="text-xs text-slate-600 leading-relaxed">
              This creates a new batch alongside the existing ones. The running total
              grows by {{ restockForm.quantity || 0 }} and existing batches keep their
              own expiration dates.
            </p>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <Button variant="outline" @click="showRestockModal = false">Cancel</Button>
          <Button
            @click="submitRestock"
            :disabled="isRestocking"
            class="bg-emerald-600 hover:bg-emerald-700 text-white"
          >
            <Loader2 v-if="isRestocking" class="w-4 h-4 mr-2 animate-spin" />
            Add to Inventory
          </Button>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { toast } from 'vue-sonner';
import api from '@/utils/axios';
import { 
  Package2, Search, Filter, Settings, Package, Pencil, Trash2, 
  SearchX, PackagePlus, Check, ChevronLeft, ChevronRight, 
  Loader2, UploadCloud, X, Info, ClipboardCheck, Plus, CopyPlus, ChevronDown,
  Layers, Archive, AlertTriangle, Clock
} from 'lucide-vue-next';

// Batch rules. The server owns the real rulebook; this module only mirrors it so
// the form can react before submit. Every rule is enforced again server-side.
import {
  loadBatchRules,
  isExpirationOptional,
  validateExpiration,
  localMinimumDate,
  expiryBadge,
  daysUntil,
  formatDate,
} from '@/composables/useBatchInventory';

// Shadcn Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectGroup, SelectItem, SelectLabel, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

// State
const supplierName = ref('Supplier Catalog');
const searchQuery = ref('');
const selectedCategories = ref([]);
const selectedType = ref('all');
const selectedSizes = ref([]);
const selectedColor = ref('');
const sortOption = ref('newest');
const products = ref([]);
const isLoading = ref(false);
const expandedGroups = ref([]); // store group keys that are expanded

const showAddModal = ref(false);
const showMobileFilters = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const isVariant = ref(false);
const isSubmitting = ref(false);
const currentStep = ref(1);
const showValidation = ref(false);

const imagePreview = ref('');
const uploadedImage = ref(null);
const fileInput = ref(null);

// Wizard Steps
const wizardSteps = [
  { label: 'Basic Info' },
  { label: 'Pricing & Inventory' },
  { label: 'Image Upload' },
  { label: 'Review & Submit' }
];

// Form Data
const newProduct = reactive({
  category: '',
  type: '',
  name: '',
  sku_code: '',
  size: '',
  weight: '',
  color_code: '',
  price: '',
  min_order: '',
  max_order: '',
  description: '',
  image_url: '',
  // Batch fields — only submitted when creating, never on a plain edit.
  quantity: '',
  expiration_date: '',
  batch_code: ''
});

// Field-level messages so the user sees which input is at fault, not just a toast.
const quantityError = ref('');
const expirationError = ref('');

// The rulebook, fetched once on mount. `minimumExpirationDate` doubles as the
// `min` on the date input so the browser blocks an impossible date outright.
const batchRules = ref(null);
const minimumExpirationDate = computed(
  () => batchRules.value?.minimumExpirationDate || localMinimumDate()
);

/** Does the selected category insist on an expiration date? */
const requiresExpiration = computed(() => !isExpirationOptional(newProduct.category));

// ── Batch panel ────────────────────────────────────────────────────
const showBatchPanel = ref(false);
const batchProduct = ref(null);
const batchList = ref([]);
const isLoadingBatches = ref(false);
const archivingBatchId = ref(null);

// ── Restock ────────────────────────────────────────────────────────
const showRestockModal = ref(false);
const restockTarget = ref(null);
const isRestocking = ref(false);
const restockErrors = reactive({ quantity: '', expiration_date: '' });
const restockForm = reactive({ quantity: '', expiration_date: '', batch_code: '' });

/** Restocking inherits the category rules of the product being topped up. */
const restockRequiresExpiration = computed(
  () => !isExpirationOptional(restockTarget.value?.category)
);

// ── Archive ────────────────────────────────────────────────────────
const isArchivingAll = ref(false);
const isArchivingGroup = ref(null);

// Data Constants
const categories = ref([
  { value: 'Interior Paints', label: 'Interior Paints', count: 0 },
  { value: 'Exterior Paints', label: 'Exterior Paints', count: 0 },
  { value: 'Industrial & Protective Paints', label: 'Industrial Paints', count: 0 },
  { value: 'Specialty Paints', label: 'Specialty Paints', count: 0 },
  { value: 'Spray Paints', label: 'Spray Paints', count: 0 },
  { value: 'Primers & Sealers', label: 'Primers & Sealers', count: 0 },
  { value: 'Solvents & Thinners', label: 'Solvents', count: 0 },
  { value: 'Application Tools', label: 'Application Tools', count: 0 },
  { value: 'Surface Preparation', label: 'Surface Prep', count: 0 },
  { value: 'Safety Equipment', label: 'Safety Equipment', count: 0 },
  { value: 'Packaging & Containers', label: 'Packaging', count: 0 }
]);

const groupedCategories = {
  '🎨 PAINT PRODUCTS': [
    { value: 'Interior Paints', label: '🏠 Interior Paints' },
    { value: 'Exterior Paints', label: '🌦️ Exterior Paints' },
    { value: 'Industrial & Protective Paints', label: '🏭 Industrial & Protective Paints' },
    { value: 'Specialty Paints', label: '🎭 Specialty Paints' },
    { value: 'Spray Paints', label: '🎯 Spray Paints' }
  ],
  '🧪 COATINGS & CHEMICALS': [
    { value: 'Primers & Sealers', label: '🧴 Primers & Sealers' }
  ],
  '🛢️ SOLVENTS & THINNERS': [
    { value: 'Solvents & Thinners', label: '🛢️ Solvents & Thinners' }
  ],
  '🧰 TOOLS & ACCESSORIES': [
    { value: 'Application Tools', label: '🎨 Application Tools' },
    { value: 'Surface Preparation', label: '🧱 Surface Preparation' },
    { value: 'Safety Equipment', label: '🦺 Safety Equipment' }
  ],
  '📦 PACKAGING': [
    { value: 'Packaging & Containers', label: '📦 Packaging & Containers' }
  ]
};

const productTypes = {
  'Interior Paints': ['Latex / Acrylic', 'Water-based', 'Low-VOC', 'Anti-mold', 'Washable interior paint'],
  'Exterior Paints': ['Weather-resistant', 'Waterproof', 'UV-resistant', 'Elastomeric'],
  'Industrial & Protective Paints': ['Epoxy (Part A & Part B)', 'Enamel', 'Anti-rust', 'Heat-resistant', 'Chemical-resistant coating'],
  'Specialty Paints': ['Chalk paint', 'Textured paint', 'Metallic paint', 'Fire-retardant', 'Anti-graffiti'],
  'Spray Paints': ['General spray paint', 'Decorative spray paint', 'Industrial spray paint', 'Protective spray coating'],
  'Primers & Sealers': ['Wall primer', 'Metal primer', 'Wood primer', 'Concrete sealer', 'Varnish', 'Lacquer', 'Clear coat', 'Waterproofing solution'],
  'Solvents & Thinners': ['Paint thinner', 'Mineral spirits', 'Turpentine', 'Degreasers', 'Cleaning solvents'],
  'Application Tools': ['Paint Brushes', 'Paint Rollers', 'Roller Covers', 'Spray Guns', 'Paint trays', 'Mixing sticks'],
  'Surface Preparation': ['Sandpaper', 'Scrapers', 'Putty knives', 'Wire brushes'],
  'Safety Equipment': ['Gloves', 'Face masks', 'Respirators', 'Safety goggles', 'Coveralls'],
  'Packaging & Containers': ['Paint cans', 'Plastic buckets', 'Steel drums', 'Spray cans', 'Mixing containers']
};

const sizesByCat = {
  paint: ['250 ml', '500 ml', '1 Liter', '4 Liters', '10 Liters', '16 Liters', '20 Liters'],
  spray: ['200 ml', '300 ml', '400 ml'],
  solvent: ['250 ml', '500 ml', '1 Liter', '4 Liters', '20 Liters'],
  tool: ['1 inch', '1.5 inch', '2 inch', '2.5 inch', '3 inch', '4 inch', 'Small', 'Medium', 'Large', '4"', '6"', '7"', '9"', '12"'],
  packaging: ['250 ml', '500 ml', '1 Liter', '4 Liters', '10 Liters', '16 Liters', '20 Liters', '200 Liters'],
  sandpaper: ['80 grit', '120 grit', '180 grit', '220 grit', '320 grit', '400 grit']
};

const colorOptions = [
  { name: 'White', value: '#FFFFFF' },
  { name: 'Beige', value: '#F5F5DC' },
  { name: 'Light Blue', value: '#B0C4DE' },
  { name: 'Gray', value: '#708090' },
  { name: 'Red', value: '#8B0000' },
  { name: 'Blue', value: '#0000FF' },
  { name: 'Green', value: '#008000' },
  { name: 'Yellow', value: '#FFFF00' },
  { name: 'Black', value: '#000000' },
  { name: 'Brown', value: '#8B4513' }
];

// Computed
const totalProducts = computed(() => products.value.length);
const uniqueCategories = computed(() => new Set(products.value.map(p => p.category)).size);

/**
 * Lots for a product, in the order the API returned them (oldest expiry first).
 * `live_batches` is what the index endpoint eager-loads; products created before
 * batch tracking existed simply have none.
 */
const liveBatchesOf = (product) => product?.live_batches || [];

/**
 * Units a distributor can actually buy right now.
 *
 * `available_quantity` is appended server-side, but a cached or older response
 * may not carry it, so fall back to the raw total rather than render
 * "undefined" in the middle of a stock figure.
 */
const availableOf = (product) => {
  if (!product) return 0;
  if (typeof product.available_quantity === 'number') return product.available_quantity;
  return Number(product.quantity) || 0;
};

/** Lots that have already lapsed, still in the active supply chain. */
const expiredBatchesOf = (product) => liveBatchesOf(product).filter((b) => isBatchExpired(b));

/**
 * Has this lot passed its expiration date?
 *
 * Delegates to the shared `daysUntil` so the browser and the server cannot drift
 * apart on the boundary. Note the `< 0`: a lot dated today has zero days left and
 * is still sellable, which is exactly what the server's `isExpired()` does —
 * a batch dies at the end of its last day, not at midnight on it.
 */
function isBatchExpired(batch) {
  const days = daysUntil(batch?.expiration_date);
  return days !== null && days < 0;
}

/** Expired lots across the whole catalogue — drives the red alert bar. */
const expiredBatchCount = computed(
  () => products.value.reduce((sum, p) => sum + expiredBatchesOf(p).length, 0)
);

/** Products with no lot left inside its date, so distributors cannot buy them. */
const expiredProductCount = computed(
  () => products.value.filter((p) => p.is_expired).length
);

/** Lots expiring within 30 days and still sellable. */
const expiringSoonCount = computed(
  () => products.value.reduce((sum, p) => {
    return sum + liveBatchesOf(p).filter((b) => {
      const days = daysUntil(b.expiration_date);
      // `null` means non-perishable, which never counts as "expiring soon".
      return days !== null && days >= 0 && days <= 30;
    }).length;
  }, 0)
);

/** Expired lots inside one card group, so the group can offer an archive button. */
const expiredBatchesInGroup = (group) =>
  (group?.variants || []).flatMap(expiredBatchesOf);

/** Header figures for the batch panel: what is sellable, what is held, what is dead. */
const batchSummary = computed(() => {
  const rows = batchList.value;
  return {
    available: rows
      .filter((b) => !b.is_archived && !isBatchExpired(b))
      .reduce((sum, b) => sum + (b.quantity - (b.reserved_quantity || 0)), 0),
    reserved: rows.reduce((sum, b) => sum + (b.reserved_quantity || 0), 0),
    expired: rows.filter((b) => !b.is_archived && isBatchExpired(b))
      .reduce((sum, b) => sum + b.quantity, 0),
  };
});

/**
 * Batch rows for the panel, newest-facing ordering already applied server-side.
 * Archived lots are kept but greyed so the supplier can see the full history.
 */
const batchRows = computed(() =>
  batchList.value.map((b) => ({
    batch: b,
    expired: !b.is_archived && isBatchExpired(b),
    reserved: b.reserved_quantity || 0,
  }))
);

const availableTypes = computed(() => {
  const types = new Set();
  products.value.forEach(p => { if (p.type) types.add(p.type); });
  return Array.from(types).sort();
});

const allSizes = computed(() => {
  const all = Object.values(sizesByCat).flat();
  return [...new Set(all)].sort();
});

// Grouping logic
const groupedProducts = computed(() => {
  const map = new Map();
  products.value.forEach(p => {
    const key = `${p.name}|${p.category}|${p.type}`;
    if (!map.has(key)) {
      map.set(key, {
        key,
        name: p.name,
        category: p.category,
        type: p.type,
        description: p.description || '',
        variants: [],
        representativeImage: p.image_url || null,
        representativeColor: p.color_code || null,
        minPrice: Infinity,
        maxPrice: -Infinity,
      });
    }
    const group = map.get(key);
    group.variants.push(p);
    // Update price range
    const price = parseFloat(p.price) || 0;
    if (price < group.minPrice) group.minPrice = price;
    if (price > group.maxPrice) group.maxPrice = price;
    // Update representative image if not set
    if (!group.representativeImage && p.image_url) group.representativeImage = p.image_url;
    if (!group.representativeColor && p.color_code) group.representativeColor = p.color_code;
    // Keep description from first variant (or longest?)
    if (!group.description && p.description) group.description = p.description;
  });
  // Convert to array and compute counts
  const groups = Array.from(map.values()).map(g => {
    if (g.minPrice === Infinity) g.minPrice = 0;
    if (g.maxPrice === -Infinity) g.maxPrice = 0;
    return g;
  });
  return groups;
});

// Filtered groups based on search and filters
const filteredGroups = computed(() => {
  let groups = groupedProducts.value;

  // Search query
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    groups = groups.filter(g => 
      g.name.toLowerCase().includes(q) ||
      g.type.toLowerCase().includes(q) ||
      g.variants.some(v => v.sku_code?.toLowerCase().includes(q))
    );
  }

  // Category filter
  if (selectedCategories.value.length) {
    groups = groups.filter(g => selectedCategories.value.includes(g.category));
  }

  // Type filter
  if (selectedType.value && selectedType.value !== 'all') {
    groups = groups.filter(g => g.type === selectedType.value);
  }

  // Size filter
  if (selectedSizes.value.length) {
    groups = groups.filter(g => 
      g.variants.some(v => selectedSizes.value.includes(v.size))
    );
  }

  // Color filter
  if (selectedColor.value) {
    groups = groups.filter(g =>
      g.variants.some(v => v.color_code?.toLowerCase() === selectedColor.value.toLowerCase())
    );
  }

  return groups;
});

// Sorted groups
const sortedGroups = computed(() => {
  const res = [...filteredGroups.value];
  if (sortOption.value === 'name') return res.sort((a,b) => a.name.localeCompare(b.name));
  if (sortOption.value === 'price_low') return res.sort((a,b) => a.minPrice - b.minPrice);
  if (sortOption.value === 'price_high') return res.sort((a,b) => b.maxPrice - a.maxPrice);
  // newest: sort by highest id among variants
  return res.sort((a,b) => {
    const maxA = Math.max(...a.variants.map(v => v.id || 0));
    const maxB = Math.max(...b.variants.map(v => v.id || 0));
    return maxB - maxA;
  });
});

// Update category counts based on filtered products (not groups)
const updateCategoryCounts = () => {
  categories.value.forEach(c => {
    c.count = products.value.filter(p => p.category === c.value).length;
  });
};

// Reuse existing computed
const filteredTypes = computed(() => {
  return productTypes[newProduct.category] || [];
});

const sizeOptions = computed(() => {
  const c = newProduct.category;
  if (!c) return [];
  if (c.includes('Paint')) return sizesByCat.paint;
  if (c.includes('Spray')) return sizesByCat.spray;
  if (c.includes('Solvent')) return sizesByCat.solvent;
  if (c.includes('Tools') || c.includes('Safety') || c.includes('Preparation')) return sizesByCat.tool; 
  if (c.includes('Packaging')) return sizesByCat.packaging;
  if (c.includes('Surface')) return sizesByCat.sandpaper;
  return sizesByCat.paint; // Fallback
});

const showColorField = computed(() => {
  const paints = ['Interior Paints', 'Exterior Paints', 'Specialty Paints', 'Spray Paints'];
  return paints.includes(newProduct.category);
});

const hasColorCategorySelected = computed(() => {
  if (selectedCategories.value.length === 0) return false;
  return selectedCategories.value.some(c => ['Interior Paints', 'Exterior Paints', 'Specialty Paints', 'Spray Paints'].includes(c));
});

const modalTitle = computed(() => {
  if (isVariant.value) return `Add Variant of "${newProduct.name}"`;
  if (isEditing.value) return 'Edit Material';
  return 'Add New Material';
});

// Methods
const loadProducts = async () => {
  isLoading.value = true;
  try {
    const response = await api.get('/supplier/raw-materials');
    products.value = response.data;
    updateCategoryCounts();
  } catch (error) {
    toast.error('Failed to load raw materials');
    console.error(error);
  } finally {
    isLoading.value = false;
  }
};

const handleCategoryCheck = (checked, value) => {
  if (checked) {
    selectedCategories.value.push(value);
  } else {
    selectedCategories.value = selectedCategories.value.filter(c => c !== value);
  }
};

const toggleSize = (size) => {
  if (selectedSizes.value.includes(size)) {
    selectedSizes.value = selectedSizes.value.filter(s => s !== size);
  } else {
    selectedSizes.value.push(size);
  }
};

const clearFilters = () => {
  selectedCategories.value = [];
  selectedType.value = 'all';
  selectedSizes.value = [];
  selectedColor = '';
  searchQuery.value = '';
};

const toggleGroupExpand = (key) => {
  if (expandedGroups.value.includes(key)) {
    expandedGroups.value = expandedGroups.value.filter(k => k !== key);
  } else {
    expandedGroups.value.push(key);
  }
};

// Wizard / Modal Logic
const openAddModal = () => {
  resetForm();
  isEditing.value = false;
  isVariant.value = false;
  showAddModal.value = true;
};

const closeModal = (val) => {
  if (val === false) {
    showAddModal.value = false;
    resetForm();
  }
};

const resetForm = () => {
  Object.assign(newProduct, {
    category: '', type: '', name: '', sku_code: '', size: '', weight: '',
    color_code: '', price: '', min_order: '', max_order: '', description: '', image_url: '',
    quantity: '', expiration_date: '', batch_code: ''
  });
  quantityError.value = '';
  expirationError.value = '';
  imagePreview.value = '';
  uploadedImage.value = null;
  currentStep.value = 1;
  showValidation.value = false;
  if (fileInput.value) fileInput.value.value = '';
};

const addVariant = (product) => {
  Object.assign(newProduct, {
    category: product.category,
    type: product.type,
    name: product.name,
    sku_code: '',
    size: '',
    weight: '',
    color_code: '',
    price: '',
    min_order: product.min_order || '',
    max_order: product.max_order || '',
    description: product.description || '',
    image_url: ''
  });
  imagePreview.value = '';
  uploadedImage.value = null;
  isEditing.value = false;
  isVariant.value = true;
  editingId.value = null;
  currentStep.value = 1;
  showValidation.value = false;
  showAddModal.value = true;
};

const onCategoryChange = () => {
  newProduct.type = '';
  newProduct.size = '';

  // Switching from a perishable to a tools/packaging category makes the
  // expiration date optional, and a date the user typed for the previous
  // category may not be the right one for this one. Clear it rather than
  // carrying a stale value into a new product.
  if (isExpirationOptional(newProduct.category) && newProduct.expiration_date) {
    newProduct.expiration_date = '';
    expirationError.value = '';
  }
};

const validateStep = () => {
  showValidation.value = true;
  if (currentStep.value === 1) {
    if (!newProduct.category || !newProduct.type || !newProduct.name || !newProduct.size || !newProduct.weight) return false;
  }
  if (currentStep.value === 2) {
    if (!newProduct.price || parseFloat(newProduct.price) <= 0) return false;
    if (newProduct.min_order && newProduct.max_order) {
        if (parseInt(newProduct.min_order) > parseInt(newProduct.max_order)) {
            toast.error("Max order quantity must be greater than or equal to Min order quantity");
            return false;
        }
    }

    // Batch fields apply to the opening delivery only. On an edit the running
    // total is a rollup the batches own, so there is nothing to validate here.
    if (!isEditing.value) {
      quantityError.value = '';
      expirationError.value = '';

      const qty = parseInt(newProduct.quantity, 10);
      if (!newProduct.quantity || Number.isNaN(qty) || qty < 1) {
        quantityError.value = 'Enter how many units you are adding.';
        toast.error(quantityError.value);
        return false;
      }

      const expiryError = validateExpiration(
        newProduct.category,
        newProduct.expiration_date
      );
      if (expiryError) {
        expirationError.value = expiryError;
        toast.error(expiryError);
        return false;
      }
    }
  }
  showValidation.value = false;
  return true;
};

const nextStep = () => {
  if (validateStep()) {
    currentStep.value++;
  } else if (currentStep.value !== 2 || !showValidation.value) {
    toast.error('Please fill in all required fields');
  }
};

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--;
};

// Image Handling
const triggerFileInput = () => fileInput.value?.click();

const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (file) processFile(file);
};

const handleFileDrop = (e) => {
  const file = e.dataTransfer.files[0];
  if (file) processFile(file);
};

const processFile = (file) => {
  if (!file.type.startsWith('image/')) return toast.error('Must be an image file');
  if (file.size > 2 * 1024 * 1024) return toast.error('Image max size is 2MB');
  
  uploadedImage.value = file;
  const reader = new FileReader();
  reader.onload = (e) => imagePreview.value = e.target.result;
  reader.readAsDataURL(file);
};

const removeImage = () => {
  imagePreview.value = '';
  uploadedImage.value = null;
  newProduct.image_url = '';
  if (fileInput.value) fileInput.value.value = '';
};

// API Integration
const handleSubmit = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  
  try {
    const formData = new FormData();
    
    Object.keys(newProduct).forEach(key => {
      if (newProduct[key] !== null && newProduct[key] !== '' && key !== 'image_url') {
        formData.append(key, newProduct[key]);
      }
    });

    if (uploadedImage.value) {
      formData.append('image', uploadedImage.value);
    }

    if (isEditing.value) {
      // Stock is not editable here. The batch fields would be silently ignored
      // by the API anyway, and sending them invites the belief that they were
      // applied. Restock is the only way to change a quantity.
      ['quantity', 'expiration_date', 'batch_code'].forEach(k => formData.delete(k));

      await api.post(`/supplier/raw-materials/${editingId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Material updated successfully');
    } else {
      await api.post('/supplier/raw-materials', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success(
        `${isVariant.value ? 'Variant' : 'Material'} added — ${newProduct.quantity} units booked in`
      );
    }
    
    await loadProducts();
    closeModal(false);
  } catch (error) {
    // Point the failure at the field the server complained about, so the user
    // is not left guessing which input was wrong.
    const fieldErrors = error.response?.data?.errors || {};
    if (fieldErrors.expiration_date) {
      expirationError.value = Array.isArray(fieldErrors.expiration_date)
        ? fieldErrors.expiration_date[0]
        : fieldErrors.expiration_date;
      currentStep.value = 2;
    }
    if (fieldErrors.quantity) {
      quantityError.value = Array.isArray(fieldErrors.quantity)
        ? fieldErrors.quantity[0]
        : fieldErrors.quantity;
      currentStep.value = 2;
    }

    toast.error(error.response?.data?.message || 'Something went wrong');
    console.error(error);
  } finally {
    isSubmitting.value = false;
  }
};

const editProduct = (product) => {
  Object.assign(newProduct, product);
  // Default to 10 if editing old database records that have null weight values
  if (newProduct.weight === null || newProduct.weight === '') {
    newProduct.weight = 10;
  }
  // Stock is not part of an edit. Leaving the batch fields populated would make
  // it look as though saving would change the quantity, which it will not.
  newProduct.quantity = '';
  newProduct.expiration_date = '';
  newProduct.batch_code = '';
  quantityError.value = '';
  expirationError.value = '';
  isEditing.value = true;
  isVariant.value = false;
  editingId.value = product.id;
  imagePreview.value = product.image_url ? getFullImageUrl(product.image_url) : '';
  currentStep.value = 1;
  showAddModal.value = true;
};

const deleteProduct = async (id) => {
  if (!confirm('Are you sure you want to delete this material?')) return;
  
  try {
    await api.delete(`/supplier/raw-materials/${id}`);
    toast.success('Material deleted');
    await loadProducts();
  } catch (error) {
    toast.error('Failed to delete material');
    console.error(error);
  }
};

// ===================================================================
// BATCH MANAGEMENT
// ===================================================================

/** Open the per-product batch panel, pulling the authoritative list. */
const openBatchPanel = async (product) => {
  batchProduct.value = product;
  batchList.value = liveBatchesOf(product);
  showBatchPanel.value = true;
  await refreshBatches();
};

/**
 * Re-read the lots for the open product.
 *
 * The index endpoint only eager-loads unarchived batches, so once a lot has been
 * archived it vanishes from the card view. This fetch returns everything, which
 * is why the panel can still show a lot after archiving it.
 */
const refreshBatches = async () => {
  if (!batchProduct.value) return;

  isLoadingBatches.value = true;
  try {
    const { data } = await api.get(`/supplier/raw-materials/${batchProduct.value.id}/batches`);
    batchList.value = data.batches || [];
  } catch (error) {
    toast.error('Failed to load batches');
    console.error(error);
  } finally {
    isLoadingBatches.value = false;
  }
};

const closeBatchPanel = () => {
  showBatchPanel.value = false;
  batchProduct.value = null;
  batchList.value = [];
};

/** Jump from the batch panel straight into a restock for the same product. */
const openRestockFromPanel = () => {
  const target = batchProduct.value;
  closeBatchPanel();
  if (target) openRestock(target);
};

/**
 * Restock an existing product.
 *
 * Always a NEW batch — the previous lots keep their own expiration dates, so
 * topping up with fresh paint does not silently extend the life of paint that
 * has been sitting since last year.
 */
const openRestock = (product) => {
  restockTarget.value = product;
  Object.assign(restockForm, { quantity: '', expiration_date: '', batch_code: '' });
  restockErrors.quantity = '';
  restockErrors.expiration_date = '';
  showRestockModal.value = true;
};

const submitRestock = async () => {
  if (isRestocking.value || !restockTarget.value) return;

  restockErrors.quantity = '';
  restockErrors.expiration_date = '';

  // Same rule the server applies, checked here so the failure is immediate
  // rather than a round-trip 422.
  const qty = parseInt(restockForm.quantity, 10);
  if (!restockForm.quantity || Number.isNaN(qty) || qty < 1) {
    restockErrors.quantity = 'Enter at least 1 unit.';
    return;
  }

  const expiryError = validateExpiration(
    restockTarget.value.category,
    restockForm.expiration_date
  );
  if (expiryError) {
    restockErrors.expiration_date = expiryError;
    return;
  }

  isRestocking.value = true;
  try {
    const payload = { quantity: qty };
    if (restockForm.expiration_date) payload.expiration_date = restockForm.expiration_date;
    if (restockForm.batch_code) payload.batch_code = restockForm.batch_code;

    const { data } = await api.post(
      `/supplier/raw-materials/${restockTarget.value.id}/restock`,
      payload
    );

    toast.success(data.message || 'Stock added');
    showRestockModal.value = false;

    // The cached rollup and expiry on the card have both moved.
    await loadProducts();

    if (batchProduct.value?.id === restockTarget.value.id) {
      await refreshBatches();
    }
  } catch (error) {
    const fieldErrors = error.response?.data?.errors || {};
    if (fieldErrors.expiration_date) {
      restockErrors.expiration_date = Array.isArray(fieldErrors.expiration_date)
        ? fieldErrors.expiration_date[0]
        : fieldErrors.expiration_date;
    }
    if (fieldErrors.quantity) {
      restockErrors.quantity = Array.isArray(fieldErrors.quantity)
        ? fieldErrors.quantity[0]
        : fieldErrors.quantity;
    }
    toast.error(error.response?.data?.message || 'Failed to add stock');
    console.error(error);
  } finally {
    isRestocking.value = false;
  }
};

/** "Move to Archive" on a single lot. */
const archiveBatch = async (batch) => {
  archivingBatchId.value = batch.id;

  try {
    const { data } = await api.post(
      `/supplier/raw-materials/batches/${batch.id}/archive`,
      { reason: 'Expired — moved to archive by supplier' }
    );

    toast.success(data.message || 'Batch moved to archive');
    await refreshBatches();
    await loadProducts();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to archive batch');
    console.error(error);
  } finally {
    archivingBatchId.value = null;
  }
};

/**
 * "Move to Archive" for one card group.
 *
 * Calls the per-product endpoint once per variant that actually has a lapsed
 * lot, so the audit trail records which product each lot belonged to.
 */
const archiveGroupExpired = async (group) => {
  const targets = (group.variants || []).filter(
    (v) => expiredBatchesOf(v).length > 0
  );

  if (targets.length === 0) return;

  if (!window.confirm(
    `Move ${group.name} expired stock to the archive?\n\n` +
    'Expired stock cannot be procured. Add a fresh batch to keep this product sellable.'
  )) return;

  isArchivingGroup.value = group.key;

  let archived = 0;
  let lastError = null;

  for (const target of targets) {
    try {
      const { data } = await api.post(
        `/supplier/raw-materials/${target.id}/archive-expired`,
        { reason: 'Expired — moved to archive by supplier' }
      );
      archived += data.archived || 0;
    } catch (error) {
      lastError = error;
    }
  }

  if (lastError) {
    toast.error(lastError.response?.data?.message || 'Failed to archive expired stock');
    console.error(lastError);
  } else if (archived === 0) {
    toast.info('No expired batches left to archive.');
  } else {
    toast.success(
      `${archived} expired ${archived === 1 ? 'batch' : 'batches'} moved to archive.`
    );
  }

  isArchivingGroup.value = null;
  await loadProducts();
};

/** Catalogue-wide sweep behind the red alert bar. */
const confirmArchiveAll = async () => {
  if (!window.confirm(
    'Move every expired batch in your catalogue to the archive?\n\n' +
    'This affects every product. Expired stock is already not procurable — ' +
    'archiving just removes it from your active supply chain.'
  )) return;

  isArchivingAll.value = true;
  try {
    const { data } = await api.post('/supplier/raw-materials/archive-expired-all', {
      reason: 'Expired — catalogue sweep',
    });

    if (data.archived > 0) {
      toast.success(
        `${data.archived} expired ${data.archived === 1 ? 'batch' : 'batches'} moved to archive.`
      );
    } else {
      toast.info(data.message || 'Nothing in your catalogue has expired.');
    }
    await loadProducts();
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to archive expired stock');
    console.error(error);
  } finally {
    isArchivingAll.value = false;
  }
};

const getFullImageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    
    // Extract the base URL from the axios instance and remove the '/api' suffix
    const baseUrl = api.defaults.baseURL.replace(/\/api\/?$/, '');
    
    return `${baseUrl}/storage/${path}`;
};

const handleImageError = (e) => e.target.style.display = 'none';

const formatPrice = (p) => {
  if (!p) return '0.00';
  return parseFloat(p).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

const truncateDescription = (d) => {
  if (!d) return '';
  return d.length > 60 ? d.substring(0, 60) + '...' : d;
};

const getCategoryDotClass = (cat) => {
  const map = {
    'Interior Paints': 'bg-blue-500', 'Exterior Paints': 'bg-green-500', 
    'Industrial & Protective Paints': 'bg-orange-500', 'Spray Paints': 'bg-red-500'
  };
  return map[cat] || 'bg-slate-400';
};

const getCategoryBadgeClass = (cat) => {
  const map = {
    'Interior Paints': 'bg-blue-100 text-blue-700 hover:bg-blue-200',
    'Exterior Paints': 'bg-green-100 text-green-700 hover:bg-green-200',
    'Industrial & Protective Paints': 'bg-orange-100 text-orange-700 hover:bg-orange-200',
    'Spray Paints': 'bg-red-100 text-red-700 hover:bg-red-200'
  };
  return map[cat] || 'bg-slate-100 text-slate-700 hover:bg-slate-200';
};

const getCategoryShortName = (cat) => {
  const map = {
    'Industrial & Protective Paints': 'Industrial',
    'Packaging & Containers': 'Packaging',
    'Solvents & Thinners': 'Solvents'
  };
  return map[cat] || cat?.split(' ')[0] || cat;
};

const exportCatalog = () => {
  if (!products.value.length) return toast.warning('No materials to export');
  
  // Batch columns come last so the existing layout is unchanged for anyone
  // already importing this file.
  const headers = [
    'Name', 'Category', 'Type', 'SKU', 'Size', 'Weight (kg)', 'Color',
    'Selling Price', 'Min Order', 'Max Order', 'Description',
    'Available Qty', 'Reserved Qty', 'Next Expiry', 'Batch Count', 'Expired Qty',
  ];
  const csv = [
    headers.join(','),
    ...products.value.map(p => {
      const batches = liveBatchesOf(p);
      return [
        p.name, p.category, p.type, p.sku_code || '', p.size, p.weight || 10,
        p.color_code || '', p.price, p.min_order || '', p.max_order || '',
        p.description || '',
        availableOf(p),
        p.reserved_quantity || 0,
        p.expiration_date || '',
        batches.length,
        expiredBatchesOf(p).reduce((sum, b) => sum + b.quantity, 0),
      ]
        .map((v) => `"${String(v ?? '').replace(/"/g, '""')}"`)
        .join(',');
    })
  ].join('\n');
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `catalog-${Date.now()}.csv`;
  link.click();
  toast.success('Catalog exported');
};

onMounted(async () => {
  // The rulebook drives the date input's `min` and the category-conditional
  // requirement, so it has to be in hand before the form is used. A failure here
  // is not fatal: the composable falls back to the same one-year arithmetic.
  batchRules.value = await loadBatchRules();
  await loadProducts();
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f5f9; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1; 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8; 
}
</style>