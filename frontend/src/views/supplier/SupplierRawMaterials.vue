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
              <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Items</span>
              <span class="text-lg font-bold text-slate-800">{{ totalProducts }}</span>
            </div>
            <Separator orientation="vertical" class="h-8 bg-slate-200" />
            <div class="flex flex-col items-center">
              <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Categories</span>
              <span class="text-lg font-bold text-slate-800">{{ uniqueCategories }}</span>
            </div>
          </div>
        </div>
      </div>
    </header>

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
            <Badge variant="secondary" class="ml-1">{{ filteredProducts.length }}</Badge>
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

        <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
          <div 
            v-for="product in sortedProducts" 
            :key="product.id"
            class="group bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col overflow-hidden"
          >
            <div class="relative aspect-[4/3] overflow-hidden bg-slate-100">
              <img 
                v-if="product.image_url" 
                :src="getFullImageUrl(product.image_url)" 
                :alt="product.name"
                @error="handleImageError"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
              >
              <div 
                v-else 
                class="w-full h-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105"
                :style="{ backgroundColor: product.color_code ? `${product.color_code}20` : '#f1f5f9' }"
              >
                <div 
                  class="w-16 h-16 rounded-lg flex items-center justify-center shadow-inner border"
                  :style="{ backgroundColor: product.color_code || '#cbd5e1' }"
                >
                  <Package class="w-8 h-8 text-white/80 drop-shadow-md" />
                </div>
              </div>
              
              <div class="absolute top-3 left-3 flex flex-col gap-2">
                <Badge :class="getCategoryBadgeClass(product.category)" class="shadow-sm border-none">
                  {{ getCategoryShortName(product.category) }}
                </Badge>
              </div>

              <div 
                v-if="product.color_code" 
                class="absolute bottom-3 right-3 w-6 h-6 rounded-full border-2 border-white shadow-sm"
                :style="{ backgroundColor: product.color_code }"
                :title="product.color_code"
              ></div>
            </div>
            
            <div class="p-4 flex-1 flex flex-col">
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-bold text-slate-800 line-clamp-1 text-base group-hover:text-blue-600 transition-colors">{{ product.name }}</h3>
                <span class="font-bold text-blue-700 bg-blue-50 px-2 py-1 rounded text-sm">₱{{ formatPrice(product.price) }}</span>
              </div>
              
              <div class="flex items-center gap-2 mb-3 text-xs text-slate-500">
                <span class="bg-slate-100 px-2 py-0.5 rounded">{{ product.type }}</span>
                <span>•</span>
                <span>{{ product.size }}</span>
              </div>
              
              <p v-if="product.description" class="text-sm text-slate-600 line-clamp-2 mb-4 h-10">
                {{ truncateDescription(product.description) }}
              </p>
              
              <div class="grid grid-cols-1 gap-y-1.5 bg-slate-50 p-3 rounded-lg text-xs mb-4">
                <div v-if="product.sku_code" class="flex justify-between items-center">
                  <span class="text-slate-500">SKU:</span>
                  <span class="font-medium text-slate-700 font-mono">{{ product.sku_code }}</span>
                </div>
              </div>
              
              <div class="mt-auto flex gap-2 pt-2 border-t border-slate-100">
                <Button 
                  variant="outline" 
                  size="sm" 
                  class="flex-1 border-slate-200 hover:bg-slate-50 hover:text-blue-600"
                  @click="editProduct(product)"
                >
                  <Pencil class="w-3.5 h-3.5 mr-2" />
                  Edit
                </Button>
                
                <Button 
                  variant="outline" 
                  size="sm" 
                  class="flex-1 border-red-100 text-red-600 hover:bg-red-50 hover:text-red-700 hover:border-red-200"
                  @click="deleteProduct(product.id)"
                >
                  <Trash2 class="w-3.5 h-3.5 mr-2" />
                  Delete
                </Button>
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
              <PackagePlus v-if="!isEditing" class="w-5 h-5" />
              <Pencil v-else class="w-5 h-5" />
            </div>
            {{ isEditing ? 'Edit Material' : 'Add New Material' }}
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
                  <Select v-model="newProduct.category" @update:modelValue="onCategoryChange">
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
                  <Select v-model="newProduct.type" :disabled="!newProduct.category">
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
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label for="sku" class="text-slate-700">SKU Code</Label>
                  <Input id="sku" v-model="newProduct.sku_code" placeholder="Optional" />
                </div>

                <div class="space-y-2">
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
              <div class="grid grid-cols-1 gap-4">
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
                   
                   <div><span class="text-slate-500 block text-xs uppercase tracking-wide">Selling Price</span> <span class="font-bold text-green-600">₱{{ formatPrice(newProduct.price) }}</span></div>

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
                  <strong>{{ isEditing ? 'Update Material' : 'Add Material' }}</strong>.
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
            {{ isEditing ? 'Update Material' : 'Add Material' }}
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

  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import { toast } from 'vue-sonner';
// Use your custom axios instance
import api from '@/utils/axios';
import { 
  Package2, Search, Filter, Settings, Package, Pencil, Trash2, 
  SearchX, PackagePlus, Check, ChevronLeft, ChevronRight, 
  Loader2, UploadCloud, X, Info, ClipboardCheck 
} from 'lucide-vue-next';

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

const showAddModal = ref(false);
const showMobileFilters = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
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
  color_code: '',
  price: '',
  description: '',
  image_url: ''
});

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

const availableTypes = computed(() => {
  const types = new Set();
  products.value.forEach(p => { if (p.type) types.add(p.type); });
  return Array.from(types).sort();
});

const allSizes = computed(() => {
  const all = Object.values(sizesByCat).flat();
  return [...new Set(all)].sort();
});

const filteredProducts = computed(() => {
  let res = products.value;
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    res = res.filter(p => 
      p.name?.toLowerCase().includes(q) || 
      p.type?.toLowerCase().includes(q) || 
      p.sku_code?.toLowerCase().includes(q)
    );
  }

  if (selectedCategories.value.length) {
    res = res.filter(p => selectedCategories.value.includes(p.category));
  }

  if (selectedType.value && selectedType.value !== 'all') {
    res = res.filter(p => p.type === selectedType.value);
  }

  if (selectedSizes.value.length) {
    res = res.filter(p => selectedSizes.value.includes(p.size));
  }

  if (selectedColor.value) {
    res = res.filter(p => p.color_code?.toLowerCase() === selectedColor.value.toLowerCase());
  }

  return res;
});

const sortedProducts = computed(() => {
  const res = [...filteredProducts.value];
  if (sortOption.value === 'name') return res.sort((a,b) => a.name.localeCompare(b.name));
  if (sortOption.value === 'price_low') return res.sort((a,b) => (a.price || 0) - (b.price || 0));
  if (sortOption.value === 'price_high') return res.sort((a,b) => (b.price || 0) - (a.price || 0));
  return res.sort((a,b) => (b.id || 0) - (a.id || 0));
});

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

// Logic
const loadProducts = async () => {
  isLoading.value = true;
  try {
    // Note: removed `/api` prefix here to utilize the custom axios configuration accurately
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

const updateCategoryCounts = () => {
  categories.value.forEach(c => {
    c.count = products.value.filter(p => p.category === c.value).length;
  });
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
  selectedColor.value = '';
  searchQuery.value = '';
};

// Wizard / Modal Logic
const openAddModal = () => {
  resetForm();
  isEditing.value = false;
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
    category: '', type: '', name: '', sku_code: '', size: '', 
    color_code: '', price: '', description: '', image_url: ''
  });
  imagePreview.value = '';
  uploadedImage.value = null;
  currentStep.value = 1;
  showValidation.value = false;
  if (fileInput.value) fileInput.value.value = '';
};

const onCategoryChange = () => {
  newProduct.type = '';
  newProduct.size = '';
};

const validateStep = () => {
  showValidation.value = true;
  if (currentStep.value === 1) {
    if (!newProduct.category || !newProduct.type || !newProduct.name || !newProduct.size) return false;
  }
  if (currentStep.value === 2) {
    if (!newProduct.price || parseFloat(newProduct.price) <= 0) return false;
  }
  showValidation.value = false;
  return true;
};

const nextStep = () => {
  if (validateStep()) {
    currentStep.value++;
  } else {
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
    
    // Append all string/number fields
    Object.keys(newProduct).forEach(key => {
      if (newProduct[key] !== null && newProduct[key] !== '' && key !== 'image_url') {
        formData.append(key, newProduct[key]);
      }
    });

    // Append file if it exists
    if (uploadedImage.value) {
      formData.append('image', uploadedImage.value);
    }

    if (isEditing.value) {
      // Laravel needs PUT mapping for multipart form data edits
      formData.append('_method', 'PUT');
      await api.post(`/supplier/raw-materials/${editingId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Material updated successfully');
    } else {
      await api.post('/supplier/raw-materials', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      toast.success('Material added successfully');
    }
    
    await loadProducts();
    closeModal(false);
  } catch (error) {
    toast.error(error.response?.data?.message || 'Something went wrong');
    console.error(error);
  } finally {
    isSubmitting.value = false;
  }
};

const editProduct = (product) => {
  Object.assign(newProduct, product);
  isEditing.value = true;
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

// Helpers
// Using Laravel storage URL format
const getFullImageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    return `http://localhost:8000/storage/${path}`; // Ensuring the exact base matches your local environment optionally, otherwise fallback to '/storage/${path}'
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
  
  const headers = ['Name', 'Category', 'Type', 'SKU', 'Size', 'Color', 'Selling Price', 'Description'];
  const csv = [
    headers.join(','),
    ...products.value.map(p => 
      `"${p.name}","${p.category}","${p.type}","${p.sku_code || ''}","${p.size}", "${p.color_code || ''}","${p.price}","${p.description || ''}"`
    )
  ].join('\n');
  
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `catalog-${Date.now()}.csv`;
  link.click();
  toast.success('Catalog exported');
};

onMounted(() => {
  loadProducts();
});
</script>

<style scoped>
/* Custom Scrollbar for filter lists */
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