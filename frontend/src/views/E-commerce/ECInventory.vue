<template>
  <div class="inventory-container p-4 md:p-6">
    

    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Inventory Management</h1>
          <h2 class="text-gray-300">Manage your available stock and deploy products to the E-commerce store.</h2>
        </div>
        <div class="flex flex-wrap items-center gap-3 mt-4 md:mt-0">
          
          <Button 
            @click="showDssModal = true"
            class="bg-gradient-to-r from-amber-500 to-orange-500 text-white hover:opacity-90 border-0 shadow-lg relative"
          >
            <Lightbulb class="w-4 h-4 mr-2" />
            Smart Insights
            <span v-if="lowStockCount > 0" class="absolute -top-2 -right-2 bg-red-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full animate-bounce shadow-md border border-red-400">
              {{ lowStockCount }} Alerts
            </span>
          </Button>

          <Button 
            variant="outline" 
            class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent"
            @click="() => refreshData(false)"
            :disabled="isLoading"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 mr-2 animate-spin" />
            <FileDown v-else class="w-4 h-4 mr-2" />
            Refresh Data
          </Button>
          <Button 
            @click="requirePermission('manage', handleAddProduct)"
            class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white hover:opacity-90 border-0"
          >
            <Plus class="w-5 h-5 mr-2" />
            Add New Product
          </Button>
        </div>
      </div>
    </div>

    <div class="flex space-x-2 mb-6 bg-gray-900/50 p-1.5 rounded-lg border border-gray-800 w-fit">
      <button 
        @click="activeTab = 'active'" 
        :class="['px-5 py-2 text-sm font-medium rounded-md transition-all', activeTab === 'active' ? 'bg-indigo-600 text-white shadow-sm' : 'text-white hover:text-white hover:bg-gray-800']"
      >
        Available for selling ({{ inventoryItems.length }})
      </button>
      <button 
        @click="activeTab = 'inactive'" 
        :class="['px-5 py-2 text-sm font-medium rounded-md transition-all', activeTab === 'inactive' ? 'bg-red-600 text-white shadow-sm' : 'text-white hover:text-white hover:bg-gray-800']"
      >
        Unavailable Products ({{ inactiveItems.length }})
      </button>
      <button 
        @click="activeTab = 'batches'" 
        :class="['px-5 py-2 text-sm font-medium rounded-md transition-all flex items-center gap-2', activeTab === 'batches' ? 'bg-emerald-600 text-white shadow-sm' : 'text-white hover:text-white hover:bg-gray-800']"
      >
        Batches &amp; Expiry
        <span
          v-if="expiredBatches.length > 0"
          class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-red-600 text-white"
        >{{ expiredBatches.length }}</span>
      </button>
    </div>

    <!-- ── Expired stock alert ──────────────────────────────────────
         Expired lots sit in the warehouse taking up space and inflating the
         on-hand figure while being impossible to sell. They are excluded from
         what the store can buy, so the count is surfaced separately rather than
         folded into "Total Units in Stock". -->
    <div 
      v-if="expiredBatches.length > 0" 
      class="mb-6 rounded-xl border border-red-800/60 bg-red-950/40 p-4"
    >
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
          <AlertTriangle class="w-5 h-5 text-red-400 shrink-0 mt-0.5" />
          <div>
            <p class="font-bold text-red-200">
              {{ expiredBatches.length }} expired {{ expiredBatches.length === 1 ? 'batch' : 'batches' }} · {{ totalExpiredUnits }} units
            </p>
            <p class="text-sm text-red-300/80 mt-0.5">
              These are excluded from what customers can buy. Move them to the archive to clear
              them out of the active supply chain.
            </p>
          </div>
        </div>
        <Button
          variant="destructive"
          :disabled="isArchivingAll || !permissions.can_manage"
          @click="archiveAllExpired"
          class="shrink-0"
        >
          <Loader2 v-if="isArchivingAll" class="w-4 h-4 mr-2 animate-spin" />
          <Archive v-else class="w-4 h-4 mr-2" />
          Move All Expired to Archive
        </Button>
      </div>
    </div>

    <div v-if="activeTab === 'active'" class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ inventoryItems.length }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Total Unique Products</h2></CardDescription>
        </CardHeader>
      </Card>
      
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ totalStock }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Total Units in Stock</h2></CardDescription>
        </CardHeader>
      </Card>

      <!-- On-hand minus expired. The gap between this and "Total Units" is
           exactly the stock that looks available but cannot be sold. -->
      <Card class="bg-gradient-to-br from-teal-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ totalSellable }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Sellable Units</h2></CardDescription>
        </CardHeader>
      </Card>

      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ lowStockCount }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Low Stock Alerts</h2></CardDescription>
        </CardHeader>
      </Card>

      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ deployedCount }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Deployed to E-commerce</h2></CardDescription>
        </CardHeader>
      </Card>

      <Card :class="['border-gray-800 text-white', expiredBatches.length > 0 ? 'bg-gradient-to-br from-red-500/25 to-orange-500/20' : 'bg-gradient-to-br from-gray-700/30 to-gray-600/20']">
        <CardHeader class="p-4">
          <CardTitle :class="['text-2xl font-bold mb-1', expiredBatches.length > 0 ? 'text-red-300' : '']">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ totalExpiredUnits }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Expired Units</h2></CardDescription>
        </CardHeader>
      </Card>
    </div>

    <!-- ── Batches tab ──────────────────────────────────────────────
         Every lot the distributor holds, oldest expiry first — the same order
         stock is issued in. This is where a distributor answers "which batch
         am I about to sell, and what is about to die". -->
    <Card v-if="activeTab === 'batches'" class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white overflow-hidden">
      <CardContent class="p-0">
        <div class="p-5 border-b border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="relative w-full max-w-md">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              v-model="batchSearch"
              type="text"
              placeholder="Search by product, SKU or batch code..."
              class="pl-10 h-10 bg-gray-800 border-gray-700 text-white placeholder:text-gray-500 focus-visible:ring-emerald-500/50"
            />
          </div>
          <div class="flex items-center gap-2">
            <Badge variant="outline" class="bg-gray-800 border-gray-700 text-gray-300">
              {{ filteredBatches.length }} {{ filteredBatches.length === 1 ? 'lot' : 'lots' }}
            </Badge>
            <Button
              variant="outline"
              :disabled="isLoadingBatches"
              @click="fetchBatches"
              class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent"
            >
              <Loader2 v-if="isLoadingBatches" class="w-4 h-4 mr-2 animate-spin" />
              <RefreshCw v-else class="w-4 h-4 mr-2" />
              Refresh
            </Button>
          </div>
        </div>

        <div v-if="isLoadingBatches" class="py-20 text-center">
          <Loader2 class="w-8 h-8 animate-spin text-emerald-500 mx-auto" />
        </div>

        <div v-else-if="filteredBatches.length === 0" class="py-20 text-center text-gray-400">
          <PackageX class="w-10 h-10 text-gray-600 mx-auto mb-3" />
          <p>{{ batchSearch ? 'No batches match your search.' : 'No batches on record.' }}</p>
        </div>

        <div v-else class="overflow-x-auto custom-scrollbar">
          <Table>
            <TableHeader class="bg-gray-900/80 border-b border-gray-800">
              <TableRow class="border-0 hover:bg-transparent">
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Product</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Batch Code</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Quantity</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Expiration</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-center">Status</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="batch in filteredBatches"
                :key="batch.id"
                class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
                :class="batch.is_expired ? 'bg-red-950/20' : ''"
              >
                <TableCell>
                  <span class="font-medium text-white">{{ batch.product_name }}</span>
                  <span class="block text-xs text-gray-400 font-mono">{{ batch.sku_code }}</span>
                </TableCell>
                <TableCell>
                  <span class="font-mono text-xs text-gray-300">{{ batch.batch_code }}</span>
                </TableCell>
                <TableCell class="text-right font-bold text-white">{{ batch.quantity }}</TableCell>
                <TableCell>
                  <span v-if="!batch.expiration_date" class="text-gray-500 text-xs">No expiry</span>
                  <span v-else :class="[
                    'font-medium',
                    batch.is_expired ? 'text-red-400' :
                    batch.days_until_expiry <= 30 ? 'text-orange-400' : 'text-gray-200'
                  ]">
                    {{ formatDate(batch.expiration_date) }}
                    <span class="block text-[10px] text-gray-500">
                      {{ batch.is_expired ? `${batch.days_expired}d ago` : `${batch.days_until_expiry}d left` }}
                    </span>
                  </span>
                </TableCell>
                <TableCell class="text-center">
                  <Badge :class="[
                    'rounded-full border-0 font-medium',
                    batch.is_expired ? 'bg-red-500/20 text-red-300' :
                    batch.is_archived ? 'bg-gray-700 text-gray-400' :
                    'bg-emerald-500/20 text-emerald-300'
                  ]">
                    {{ batch.is_archived ? 'Archived' : (batch.is_expired ? 'Expired' : 'Sellable') }}
                  </Badge>
                </TableCell>
                <TableCell class="text-right">
                  <Button
                    v-if="batch.is_expired && !batch.is_archived"
                    :disabled="!permissions.can_manage || archivingBatchId === batch.id"
                    @click="archiveBatch(batch)"
                    size="sm"
                    variant="outline"
                    class="border-red-800 text-red-300 hover:bg-red-900/40 hover:text-red-200 bg-transparent"
                  >
                    <Loader2 v-if="archivingBatchId === batch.id" class="w-4 h-4 mr-2 animate-spin" />
                    <Archive v-else class="w-4 h-4 mr-2" />
                    Move to Archive
                  </Button>
                  <span v-else-if="batch.is_archived" class="text-xs text-gray-500">Removed</span>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <Card v-else class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white overflow-hidden">
      <CardContent class="p-0">
        
        <div class="p-5 border-b border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="relative w-full max-w-md">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search by Product Name, SKU, or Category..." 
              class="pl-10 h-10 bg-gray-800 border-gray-700 text-white placeholder:text-gray-500 focus-visible:ring-emerald-500/50" 
            />
          </div>
          
          <div v-if="activeTab === 'active'" class="flex items-center gap-2">
            <Badge variant="outline" class="bg-gray-800 border-gray-700 text-gray-300 cursor-pointer hover:bg-gray-700">All Items</Badge>
            <Badge variant="outline" class="bg-transparent border-gray-700 text-gray-500 cursor-pointer hover:text-gray-300"><h2>Low Stock</h2></Badge>
          </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
          <Table>
            <TableHeader class="bg-gray-900/80 border-b border-gray-800">
              <TableRow class="border-0 hover:bg-transparent">
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Product Info</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Category</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Stock Level</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Price</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-center">Status</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isLoading" class="border-0 hover:bg-transparent">
                <TableCell colspan="6" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <Loader2 class="w-8 h-8 animate-spin text-emerald-500 mb-2" />
                    <span>Loading data...</span>
                  </div>
                </TableCell>
              </TableRow>

              <TableRow v-else-if="currentFilteredItems.length === 0" class="border-0 hover:bg-transparent">
                <TableCell colspan="6" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <PackageX class="w-8 h-8 text-gray-600 mb-2" />
                    <span>No products found in {{ activeTab === 'active' ? 'inventory' : 'inactive list' }}.</span>
                  </div>
                </TableCell>
              </TableRow>
              
              <TableRow 
                v-else
                v-for="item in currentFilteredItems" 
                :key="item.id"
                class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors group"
              >
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-md bg-gray-800 border-gray-700 overflow-hidden shrink-0 flex items-center justify-center">
                      <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover" />
                      <Package v-else class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="flex flex-col">
                      <span class="font-bold text-white">{{ item.name }}</span>
                      <h2 class="text-xs text-gray-400 mt-0.5">SKU: {{ item.sku_code }}</h2>
                    </div>
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="flex flex-col">
                    <span class="text-gray-200">{{ item.category }}</span>
                    <span class="text-xs text-white-500">{{ item.type }}</span>
                  </div>
                </TableCell>
                
                <TableCell class="text-right">
                  <div class="flex flex-col items-end gap-1">
                    <span :class="[
                      'font-bold text-lg',
                      (activeTab === 'active' && sellableOf(item) <= item.min_stock_level) ? 'text-amber-400' : 'text-emerald-400'
                    ]">
                      {{ sellableOf(item) }}
                    </span>
                    
                    <!-- When expired stock is inflating the total, say so rather
                         than letting 100 sit next to 60 without explanation. -->
                    <span v-if="expiredOf(item) > 0" class="text-[10px] text-red-400">
                      +{{ expiredOf(item) }} expired (not sellable)
                    </span>
                    <span v-else-if="activeTab === 'active'" class="text-[10px] text-white-500 uppercase tracking-wider">
                      Min: {{ item.min_stock_level }}
                    </span>
                    <span
                      v-if="activeTab === 'active' && item.earliest_expiration"
                      class="text-[10px] uppercase tracking-wider"
                      :class="expiryTone(item.earliest_expiration)"
                    >
                      {{ formatDate(item.earliest_expiration) }}
                    </span>
                  </div>
                </TableCell>
                
                <TableCell class="text-right font-medium text-gray-200">
                  ₱{{ Number(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                </TableCell>
                
                <TableCell class="text-center">
                  <Badge v-if="item.ecommerce_status === 'inactive'" class="rounded-full border-0 font-medium bg-red-500/20 text-red-300">
                    Inactive
                  </Badge>
                  <Badge v-else-if="sellableOf(item) === 0 && item.quantity > 0"
                         class="rounded-full border-0 font-medium bg-red-500/20 text-red-300">
                    All Stock Expired
                  </Badge>
                  <Badge v-else :class="[
                    'rounded-full border-0 font-medium',
                    item.ecommerce_status === 'deployed' ? 'bg-indigo-500/20 text-indigo-300' : 
                    item.ecommerce_status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 
                    'bg-gray-700 text-gray-300'
                  ]">
                    {{ 
                      item.ecommerce_status === 'deployed' ? 'Live on Store' : 
                      item.ecommerce_status === 'pending' ? 'Pending Approval' : 
                      'Not Deployed' 
                    }}
                  </Badge>
                </TableCell>
                
                <TableCell class="text-right">
                  <Button 
                    @click="requirePermission('view', () => openViewModal(item))"
                    variant="ghost" 
                    size="sm" 
                    class="text-blue-400 hover:text-white hover:bg-blue-600/20"
                  >
                    <h2><Eye class="w-4 h-4 mr-2" /></h2>
                    <h2>View Details</h2>
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div class="p-4 border-t border-gray-800 flex items-center justify-between text-sm text-gray-400">
          <h2>
            Showing <span class="text-white font-medium">{{ currentFilteredItems.length > 0 ? 1 : 0 }}</span> to <span class="text-white font-medium">{{ currentFilteredItems.length }}</span> of <span class="text-white font-medium">{{ activeTab === 'active' ? inventoryItems.length : inactiveItems.length }}</span> items
          </h2>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewModal" @update:open="(val) => !val && closeViewModal()">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-3xl custom-scrollbar max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex items-center gap-2">
            {{ activeTab === 'active' ? 'Active Inventory Item' : 'Inactive Product Details' }}
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Review product specifications, current stock levels, and store visibility status.
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedItem" class="space-y-6 py-4">
          
          <div class="bg-gray-800/50 rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-4 flex flex-col sm:flex-row gap-6">
              <div class="w-full sm:w-1/3 aspect-square bg-gray-950 rounded-lg border border-gray-700 overflow-hidden flex items-center justify-center shrink-0">
                <img 
                  v-if="selectedItem.image_url" 
                  :src="selectedItem.image_url" 
                  alt="Product Image" 
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-gray-600 flex flex-col items-center">
                  <ImageOff class="w-8 h-8 mb-2 opacity-50" />
                  <span class="text-xs">No Image Available</span>
                </div>
              </div>

              <div class="w-full sm:w-2/3 grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <div class="flex items-start justify-between">
                    <div>
                      <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Product Name</p>
                      <p class="font-bold text-2xl text-white leading-tight">{{ selectedItem.name }}</p>
                    </div>
                    <Badge v-if="selectedItem.ecommerce_status === 'inactive'" class="rounded-full border-0 font-medium ml-2 shrink-0 bg-red-500/20 text-red-300">
                      Inactive
                    </Badge>
                    <Badge v-else :class="[
                      'rounded-full border-0 font-medium ml-2 shrink-0',
                      selectedItem.ecommerce_status === 'deployed' ? 'bg-indigo-500/20 text-indigo-300' : 
                      selectedItem.ecommerce_status === 'pending' ? 'bg-amber-500/20 text-amber-300' : 
                      'bg-gray-700 text-gray-300'
                    ]">
                      {{ 
                        selectedItem.ecommerce_status === 'deployed' ? 'Deployed Online' : 
                        selectedItem.ecommerce_status === 'pending' ? 'Pending Approval' : 
                        'Not Deployed' 
                      }}
                    </Badge>
                  </div>
                  <p class="text-sm text-gray-400 mt-2">{{ selectedItem.description || 'No detailed description available for this product.' }}</p>
                </div>
                
                <div class="mt-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">SKU Code</p>
                  <p class="font-mono text-emerald-400">{{ selectedItem.sku_code }}</p>
                </div>
                <div class="mt-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Retail Price</p>
                  <p class="font-bold text-white text-lg">₱{{ Number(selectedItem.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</p>
                </div>

                <div class="col-span-2 border-t border-gray-700 my-2"></div>

                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Category & Type</p>
                  <p class="font-medium text-gray-200">{{ selectedItem.category }} / {{ selectedItem.type }}</p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Unit Size</p>
                  <p class="font-medium text-gray-200">{{ selectedItem.size }}</p>
                </div>
              </div>
            </div>
          </div>

          <div :class="[
              'grid gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-800',
              activeTab === 'active' ? 'grid-cols-4' : 'grid-cols-1'
            ]">
            <div :class="['text-center', activeTab === 'active' ? 'border-r border-gray-700' : '']">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Sellable Now</p>
              <p :class="[
                'font-bold text-3xl',
                (activeTab === 'active' && sellableOf(selectedItem) <= selectedItem.min_stock_level) ? 'text-amber-400' : 'text-emerald-400'
              ]">{{ sellableOf(selectedItem) }}</p>
              <p v-if="expiredOf(selectedItem) > 0" class="text-[10px] text-red-400 mt-0.5">
                of {{ selectedItem.quantity }} on hand
              </p>
            </div>
            <div v-if="activeTab === 'active'" class="text-center border-r border-gray-700">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Min. Threshold</p>
              <p class="font-bold text-2xl text-gray-300">{{ selectedItem.min_stock_level }}</p>
            </div>
            <div v-if="activeTab === 'active'" class="text-center border-r border-gray-700">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Max Capacity</p>
              <p class="font-bold text-2xl text-gray-300">{{ selectedItem.max_stock_level }}</p>
            </div>
            <div v-if="activeTab === 'active'" class="text-center">
              <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Active Lots</p>
              <p class="font-bold text-2xl text-gray-300">{{ (selectedItem.batches || []).length }}</p>
            </div>
          </div>

          <!-- ── Batch breakdown ───────────────────────────────────────
               Without this the distributor cannot tell WHICH stock expires
               when, which is the whole point of tracking by batch. Ordered
               oldest-first, matching how stock is issued. -->
          <div v-if="activeTab === 'active' && (selectedItem.batches || []).length > 0" class="rounded-xl border border-gray-800 overflow-hidden">
            <div class="px-4 py-3 bg-gray-800/50 border-b border-gray-800 flex items-center justify-between">
              <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <Layers class="w-4 h-4 text-emerald-400" />
                Batch Breakdown
              </h3>
              <span class="text-[10px] text-gray-500 uppercase tracking-wider">Oldest expiry first</span>
            </div>
            <Table>
              <TableHeader class="bg-gray-900/60 border-b border-gray-800">
                <TableRow class="border-0 hover:bg-transparent">
                  <TableHead class="h-9 text-xs text-gray-400">Batch</TableHead>
                  <TableHead class="h-9 text-xs text-gray-400 text-right">Qty</TableHead>
                  <TableHead class="h-9 text-xs text-gray-400">Expires</TableHead>
                  <TableHead class="h-9 text-xs text-gray-400 text-center">Status</TableHead>
                  <TableHead class="h-9 text-xs text-gray-400 text-right">Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow
                  v-for="batch in selectedItem.batches"
                  :key="batch.id"
                  class="border-b border-gray-800/60 hover:bg-gray-800/40"
                  :class="batch.is_expired ? 'bg-red-950/20' : ''"
                >
                  <TableCell class="font-mono text-xs text-gray-300">{{ batch.batch_code }}</TableCell>
                  <TableCell class="text-right font-bold text-white">{{ batch.quantity }}</TableCell>
                  <TableCell>
                    <span v-if="!batch.expiration_date" class="text-gray-500 text-xs">No expiry</span>
                    <span v-else :class="[
                      'text-sm',
                      batch.is_expired ? 'text-red-400' :
                      batch.days_until_expiry <= 30 ? 'text-orange-400' : 'text-gray-200'
                    ]">
                      {{ formatDate(batch.expiration_date) }}
                      <span class="block text-[10px] text-gray-500">
                        {{ batch.is_expired ? `${Math.abs(batch.days_until_expiry)}d ago` : `${batch.days_until_expiry}d left` }}
                      </span>
                    </span>
                  </TableCell>
                  <TableCell class="text-center">
                    <Badge :class="[
                      'rounded-full border-0 font-medium text-[10px]',
                      batch.is_archived ? 'bg-gray-700 text-gray-400' :
                      batch.is_expired ? 'bg-red-500/20 text-red-300' :
                      'bg-emerald-500/20 text-emerald-300'
                    ]">
                      {{ batch.is_archived ? 'Archived' : (batch.is_expired ? 'Expired' : 'Sellable') }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <Button
                      v-if="batch.is_expired && !batch.is_archived"
                      :disabled="!permissions.can_manage || archivingBatchId === batch.id"
                      @click="archiveBatch(batch)"
                      size="sm"
                      variant="outline"
                      class="border-red-800 text-red-300 hover:bg-red-900/40 hover:text-red-200 bg-transparent"
                    >
                      <Loader2 v-if="archivingBatchId === batch.id" class="w-3.5 h-3.5 mr-1 animate-spin" />
                      <Archive v-else class="w-3.5 h-3.5 mr-1" />
                      Archive
                    </Button>
                    <span v-else class="text-xs text-gray-600">—</span>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-800 mt-2">
          
          <Button 
            variant="outline" 
            @click="closeViewModal" 
            class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent"
          >
            Close
          </Button>

          <Button 
            v-if="activeTab === 'inactive' && selectedItem"
            :disabled="isProcessing"
            @click="requirePermission('manage', () => { actionQuantity = selectedItem.quantity; isReactivateConfirmOpen = true; })"
            class="bg-emerald-600 hover:bg-emerald-700 text-white border-0 shadow-lg"
          >
            <Loader2 v-if="isProcessing" class="w-4 h-4 mr-2 animate-spin" />
            <Package class="w-4 h-4 mr-2" v-else />
            Make Available for Selling
          </Button>

          <template v-else-if="selectedItem">
            <Button 
              :disabled="isProcessing"
              @click="requirePermission('manage', () => { actionQuantity = selectedItem.quantity; isDeactivateConfirmOpen = true; })"
              class="bg-red-600/80 hover:bg-red-600 text-white border-0 mr-auto"
            >
              <PackageX class="w-4 h-4 mr-2" />
              Mark Unavailable for selling
            </Button>

            <Button 
              v-if="selectedItem.ecommerce_status === 'not_deployed'"
              :disabled="isProcessing"
              @click="requirePermission('manage', () => isDeployConfirmOpen = true)"
              class="bg-gradient-to-r from-indigo-500 to-blue-600 text-white hover:opacity-90 border-0 shadow-lg shadow-indigo-500/20"
            >
              <Loader2 v-if="isProcessing" class="w-4 h-4 mr-2 animate-spin" />
              <Store class="w-4 h-4 mr-2" v-else />
              Request for Deployment
            </Button>
            
            <Button 
              v-else-if="selectedItem.ecommerce_status === 'pending'"
              disabled
              class="bg-amber-600/50 text-white border-0"
            >
              <Loader2 class="w-4 h-4 mr-2 animate-spin" />
              Pending Approval
            </Button>
          </template>

        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showDssModal" @update:open="(val) => !val && (showDssModal = false)">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-4xl custom-scrollbar max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex items-center gap-2 text-amber-400">
            <Activity class="w-6 h-6" />
            Decision Support System (DSS)
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Automated inventory predictions, trend alerts, and restocking recommendations based on your database.
          </DialogDescription>
        </DialogHeader>

        <div v-if="dssAlerts.length === 0" class="py-12 text-center text-gray-500">
          <Lightbulb class="w-16 h-16 mx-auto mb-4 opacity-20 text-emerald-500" />
          <p class="text-lg font-bold text-gray-300">Inventory is stable.</p>
          <p class="text-sm mt-1">No critical shortages or negative trends detected at the moment.</p>
        </div>

        <div v-else class="space-y-4 py-4">
          <div v-for="(alert, index) in dssAlerts" :key="index" class="bg-gray-800/40 border border-gray-700 rounded-xl p-4 md:p-5 relative overflow-hidden transition-all hover:bg-gray-800/60">
             <div class="absolute left-0 top-0 bottom-0 w-1" :class="alert.severity === 'Critical' ? 'bg-red-500' : 'bg-amber-500'"></div>
             
             <div class="flex flex-col md:flex-row gap-5 justify-between">
                <div class="flex-1">
                   <div class="flex items-center flex-wrap gap-2 mb-2">
                      <h3 class="font-bold text-lg text-white">{{ alert.item.name }}</h3>
                      <Badge :class="alert.severity === 'Critical' ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/20 text-amber-400'" class="border-0 font-bold uppercase tracking-wider text-[10px]">
                         {{ alert.severity }} Shortage
                      </Badge>
                   </div>
                   <p class="text-sm text-gray-400 mb-4">SKU: <span class="text-gray-300 font-mono">{{ alert.item.sku_code }}</span> | Sellable: <span class="font-bold" :class="alert.severity === 'Critical' ? 'text-red-400' : 'text-amber-400'">{{ sellableOf(alert.item) }}</span> / {{ alert.item.quantity }} on hand (Min threshold: {{ alert.item.min_stock_level }})</p>
                   
                   <div class="space-y-2 bg-gray-900/60 rounded-lg p-3 md:p-4 border border-gray-800">
                      <div class="flex items-start gap-3">
                         <TrendingDown class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" />
                         <p class="text-sm text-gray-300 leading-relaxed"><span class="font-bold text-blue-300 uppercase tracking-wider text-xs block mb-0.5">Prediction</span> {{ alert.trendText }}</p>
                      </div>
                      <div class="w-full h-px bg-gray-800 my-1"></div>
                      <div class="flex items-start gap-3">
                         <Lightbulb class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" />
                         <p class="text-sm text-gray-300 leading-relaxed"><span class="font-bold text-amber-300 uppercase tracking-wider text-xs block mb-0.5">Recommendation</span> {{ alert.suggestion }}</p>
                      </div>
                   </div>
                </div>

                <div class="flex flex-col justify-end shrink-0 min-w-[220px] mt-2 md:mt-0">
                   <Button 
                      v-if="alert.action === 'reactivate'"
                      @click="handleDssReactivate(alert.inactiveMatch)"
                      class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-11"
                   >
                      Make Available for Selling
                      <ArrowRight class="w-4 h-4 ml-2" />
                   </Button>
                   <Button 
                      v-else
                      @click="goToProcurement(alert.item)"
                      class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold h-11"
                   >
                      Request Procurement
                      <ArrowRight class="w-4 h-4 ml-2" />
                   </Button>
                </div>
             </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="isDeployConfirmOpen" @update:open="isDeployConfirmOpen = $event">
      <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white z-50">
        <AlertDialogHeader>
          <AlertDialogTitle>Deploy to E-Commerce?</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Are you sure you want to request making <span class="text-white font-bold">{{ selectedItem?.name }}</span> visible on the e-commerce store? The business owner will need to approve this.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="isDeployConfirmOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="requestDeployment" class="bg-indigo-600 hover:bg-indigo-700 text-white border-0">Yes, Request Deployment</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog :open="isDeactivateConfirmOpen" @update:open="isDeactivateConfirmOpen = $event">
      <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white z-50">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-400">Mark Quantity as Unavailable for selling</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Select the number of units of <span class="text-white font-bold">{{ selectedItem?.name }}</span> you want to move to inactive storage.
          </AlertDialogDescription>
        </AlertDialogHeader>
        
        <div class="py-2 space-y-2">
          <label class="text-sm font-medium text-gray-300">Quantity (Max: {{ selectedItem?.quantity }})</label>
          <Input 
            type="number" 
            v-model="actionQuantity" 
            :max="selectedItem?.quantity" 
            min="1" 
            @keydown="['e', 'E', '+', '-', '.'].includes($event.key) && $event.preventDefault()"
            class="bg-gray-950 border-gray-700 text-white focus-visible:ring-red-500" 
          />
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel @click="isDeactivateConfirmOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="!isActionQuantityValid" @click="deactivateItem" class="bg-red-600 hover:bg-red-700 text-white border-0 disabled:opacity-50">Yes, Mark Unavailable</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog :open="isReactivateConfirmOpen" @update:open="isReactivateConfirmOpen = $event">
      <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white z-50">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-emerald-400">Reactivate Product Quantity</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Select the number of units of <span class="text-white font-bold">{{ selectedItem?.name }}</span> you want to restore to the active inventory.
          </AlertDialogDescription>
        </AlertDialogHeader>

        <div class="py-2 space-y-2">
          <label class="text-sm font-medium text-gray-300">Quantity (Max: {{ selectedItem?.quantity }})</label>
          <Input 
            type="number" 
            v-model="actionQuantity" 
            :max="selectedItem?.quantity" 
            min="1" 
            @keydown="['e', 'E', '+', '-', '.'].includes($event.key) && $event.preventDefault()"
            class="bg-gray-950 border-gray-700 text-white focus-visible:ring-emerald-500" 
          />
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel @click="isReactivateConfirmOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">Cancel</AlertDialogCancel>
          <AlertDialogAction :disabled="!isActionQuantityValid" @click="reactivateItem" class="bg-emerald-600 hover:bg-emerald-700 text-white border-0 disabled:opacity-50">Yes, Reactivate</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios' 
import echo from '@/utils/websocket' // Import echo for real-time updates
import { toast, Toaster } from 'vue-sonner'
import { 
  Search, FileDown, Eye, PackageX, Loader2, Package, ImageOff, Plus, Store, AlertTriangle, Lightbulb, Activity, TrendingDown, ArrowRight,
  Archive, Layers, RefreshCw
} from 'lucide-vue-next'

import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

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

// State
const activeTab = ref('active')
const searchQuery = ref('')
const showViewModal = ref(false)
const showDssModal = ref(false)
const selectedItem = ref<any>(null)
const isProcessing = ref(false)
const isLoading = ref(true)

const inventoryItems = ref<any[]>([])
const inactiveItems = ref<any[]>([])

// ── Batches & expiry ───────────────────────────────────────────────
// Lot-level view of the warehouse. Kept separate from `inventoryItems`
// because the batch endpoints return a different shape and can be fetched
// on demand without reloading the whole catalogue.
const allBatches = ref<any[]>([])
const isLoadingBatches = ref(false)
const batchSearch = ref('')
const archivingBatchId = ref<number | null>(null)
const isArchivingAll = ref(false)

const actionQuantity = ref<number | ''>('')
const isDeployConfirmOpen = ref(false)
const isDeactivateConfirmOpen = ref(false)
const isReactivateConfirmOpen = ref(false)

// WebSocket State Variables
const activeDistributorId = ref<number | null>(null);
const isAdminUser = ref(false);

// User Permissions setup via Level-Based RBAC
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// RBAC Action Interceptor
const requirePermission = (action: string, callback: Function) => {
  if (!(permissions.value as any)[`can_${action}`]) {
    toast.error(`Access Denied: You do not have permission to ${action} inventory items.`);
    return;
  }
  if (callback) callback();
}

// Fetch API Data
const refreshData = async (isBackground = false) => {
  if (!isBackground) isLoading.value = true
  await Promise.all([
    fetchInventory(isBackground),
    fetchInactiveInventory(isBackground),
    // Lots are always loaded because the expiry alert bar sits above the tabs
    // and must be correct regardless of which tab is open.
    isBackground ? fetchBatches(true) : fetchBatches(),
  ])
  if (!isBackground) isLoading.value = false
}

/** Every lot the distributor holds, oldest expiry first. */
const fetchBatches = async (isBackground = false) => {
  if (!isBackground) isLoadingBatches.value = true
  try {
    const response = await api.get('/operation-distributor/ec-inventory/batches')
    if (response.data.success) {
      allBatches.value = response.data.data || []
    }
  } catch (error) {
    // Not fatal: the catalogue still works without lot detail, and the
    // expiry banner simply will not appear.
    if (!isBackground) console.error('Failed to load batches', error)
  } finally {
    if (!isBackground) isLoadingBatches.value = false
  }
}

const fetchInventory = async (isBackground = false) => {
  try {
    const response = await api.get('/operation-distributor/ec-inventory')
    if (response.data.success) {
      inventoryItems.value = response.data.data
      if (response.data.permissions) permissions.value = response.data.permissions
      
      // Update variables for WebSockets safely
      activeDistributorId.value = response.data.distributor_id;
      isAdminUser.value = response.data.is_admin;
      
      // Update the viewedRequest live if the modal is currently open
      if (selectedItem.value) {
        const updatedItem = inventoryItems.value.find(i => i.id === selectedItem.value.id);
        if (updatedItem) selectedItem.value = updatedItem;
      }

      if (!isBackground) {
        setupWebSocket();
      }
    }
  } catch (error: any) {
    if (error.response?.status === 403) {
      if (!isBackground) toast.error('Unauthorized access to inventory.')
    }
  }
}

const fetchInactiveInventory = async (isBackground = false) => {
  try {
    const response = await api.get('/operation-distributor/ec-inventory/inactive')
    if (response.data.success) {
      inactiveItems.value = response.data.data
      
      // Update the viewedRequest live if the modal is currently open
      if (selectedItem.value) {
        const updatedItem = inactiveItems.value.find(i => i.id === selectedItem.value.id);
        if (updatedItem) selectedItem.value = updatedItem;
      }
    }
  } catch (error) {
    console.error('Failed to load inactive inventory', error)
  }
}

// =========================================================================
// WEBSOCKET LOGIC FOR REAL-TIME UPDATES
// =========================================================================
const setupWebSocket = () => {
    if (isAdminUser.value) {
        echo.private(`admin.inventory`)
            .listen('.inventory.updated', (e: any) => {
                handleInventoryUpdate();
            });
    } else if (activeDistributorId.value) {
        echo.private(`distributor.${activeDistributorId.value}.inventory`)
            .listen('.inventory.updated', (e: any) => {
                handleInventoryUpdate();
            });
    }
}

const handleInventoryUpdate = () => {
    // Re-fetch data silently in the background when the backend fires the event
    refreshData(true);
}

// Lifecycle
onMounted(() => {
  refreshData()
})

onUnmounted(() => {
    if (isAdminUser.value) {
        echo.leave(`admin.inventory`);
    } else if (activeDistributorId.value) {
        echo.leave(`distributor.${activeDistributorId.value}.inventory`);
    }
})

// Computeds for Summary Cards
const totalStock = computed(() => inventoryItems.value.reduce((sum, item) => sum + item.quantity, 0))

/**
 * Units a customer can actually buy.
 *
 * `quantity` is the on-hand total and still includes expired lots, so using it
 * for a sellable figure would let the UI promise stock that checkout refuses.
 * The server sends `available_quantity`; the fallback keeps older payloads sane.
 */
const sellableOf = (item: any) => {
  if (!item) return 0;
  if (typeof item.available_quantity === 'number') return item.available_quantity;
  return Number(item.quantity) || 0;
}

const expiredOf = (item: any) => (item ? Number(item.expired_quantity) || 0 : 0);

const totalSellable = computed(() => inventoryItems.value.reduce((sum, item) => sum + sellableOf(item), 0))

/** Lots that have lapsed and are still sitting in the active supply chain. */
const expiredBatches = computed(() =>
  allBatches.value.filter((b) => b.is_expired && !b.is_archived)
)

const totalExpiredUnits = computed(() =>
  inventoryItems.value.reduce((sum, item) => sum + expiredOf(item), 0)
)

const lowStockCount = computed(() => inventoryItems.value.filter(item => sellableOf(item) <= item.min_stock_level).length)
const deployedCount = computed(() => inventoryItems.value.filter(item => item.ecommerce_status === 'deployed').length)

const filteredBatches = computed(() => {
  if (!batchSearch.value) return allBatches.value
  const query = batchSearch.value.toLowerCase()
  return allBatches.value.filter(b =>
    (b.product_name || '').toLowerCase().includes(query) ||
    (b.sku_code || '').toLowerCase().includes(query) ||
    (b.batch_code || '').toLowerCase().includes(query)
  )
})

/** Colour an expiry date by urgency, shared by the table and the modal. */
const expiryTone = (date: string) => {
  if (!date) return 'text-gray-500'
  const days = daysUntil(date)
  if (days === null) return 'text-gray-500'
  if (days < 0) return 'text-red-400'
  if (days <= 30) return 'text-orange-400'
  if (days <= 90) return 'text-amber-400'
  return 'text-gray-500'
}

const daysUntil = (date: string) => {
  if (!date) return null
  const target = new Date(`${String(date).slice(0, 10)}T00:00:00`)
  if (isNaN(target.getTime())) return null
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return Math.round((target.getTime() - today.getTime()) / 86400000)
}

const formatDate = (value: string) => {
  if (!value) return '—'
  const d = new Date(`${String(value).slice(0, 10)}T00:00:00`)
  if (isNaN(d.getTime())) return String(value)
  return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' })
}

// =========================================================================
// ARCHIVING EXPIRED STOCK
// =========================================================================

/** "Move to Archive" on a single lot. */
const archiveBatch = async (batch: any) => {
  if (!window.confirm(
    `Move batch ${batch.batch_code} (${batch.quantity} units) to the archive?\n\n` +
    'This removes it from your active supply chain. Expired stock cannot be sold anyway, ' +
    'so nothing sellable is lost.'
  )) return;

  archivingBatchId.value = batch.id;
  try {
    const { data } = await api.post(
      `/operation-distributor/ec-inventory/batches/${batch.id}/archive`,
      { reason: 'Expired — moved to archive by distributor' }
    );
    toast.success(data.message || 'Batch moved to archive');
    await refreshData(true);
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to archive batch');
    console.error(error);
  } finally {
    archivingBatchId.value = null;
  }
}

/** Catalogue-wide sweep behind the red alert bar. */
const archiveAllExpired = async () => {
  if (!window.confirm(
    'Move every expired batch in your warehouse to the archive?\n\n' +
    'Expired stock is already excluded from what customers can buy, so nothing ' +
    'sellable is lost.'
  )) return;

  isArchivingAll.value = true;
  try {
    const { data } = await api.post('/operation-distributor/ec-inventory/archive-expired', {
      reason: 'Expired — warehouse sweep',
    });

    if (data.archived > 0) {
      toast.success(
        `${data.archived} expired ${data.archived === 1 ? 'batch' : 'batches'} moved to archive.`
      );
    } else {
      toast.info(data.message || 'No expired stock found.');
    }
    await refreshData(true);
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to archive expired stock');
    console.error(error);
  } finally {
    isArchivingAll.value = false;
  }
}

const currentFilteredItems = computed(() => {
  const list = activeTab.value === 'active' ? inventoryItems.value : inactiveItems.value
  if (!searchQuery.value) return list
  
  const query = searchQuery.value.toLowerCase()
  return list.filter(item => 
    item.name.toLowerCase().includes(query) ||
    item.sku_code.toLowerCase().includes(query) ||
    item.category.toLowerCase().includes(query)
  )
})

const isActionQuantityValid = computed(() => {
  if (!selectedItem.value || actionQuantity.value === '') return false;
  return actionQuantity.value > 0 && actionQuantity.value <= selectedItem.value.quantity;
})

// =========================================================================
// DSS (DECISION SUPPORT SYSTEM) LOGIC
// =========================================================================
const dssAlerts = computed(() => {
  const alerts: any[] = [];
  
  // Shortage is measured against SELLABLE units, not the raw on-hand total.
  // Counting expired lots as available would suppress a restock alert for a
  // product that has, in practice, nothing left to sell.
  const lowStockProducts = inventoryItems.value.filter(item => sellableOf(item) <= item.min_stock_level);
  
  lowStockProducts.forEach(item => {
    // Check if we have backup stock in the inactive inventory list matching the product_id
    const inactiveMatch = inactiveItems.value.find(inc => inc.product_id === item.product_id);
    const inactiveQty = inactiveMatch ? inactiveMatch.quantity : 0;
    
    // Calculate severity and trend text
    let severity = sellableOf(item) === 0 ? 'Critical' : 'Warning';
    let trendText = sellableOf(item) === 0 
      ? 'Stockout Detected: Immediate replenishment required. Trend indicates zero availability blocking sales.'
      : (sellableOf(item) <= item.min_stock_level / 2 
          ? 'High Shortage Risk: Stock is depleting rapidly past safety threshold.' 
          : 'Moderate Shortage Risk: Inventory is approaching minimum safety levels.');
          
    // Determine the smart suggestion and the actionable route
    let suggestion = '';
    let action = '';
    
    if (inactiveQty > 0) {
      suggestion = `Found ${inactiveQty} unit(s) of this item in your inactive (unavailable) storage. Reactivate them to active inventory to quickly resolve the shortage.`;
      action = 'reactivate';
    } else {
      suggestion = `No inactive backup stock available in the database. Request procurement from suppliers immediately to maintain operations.`;
      action = 'procure';
    }
    
    alerts.push({
      item,
      inactiveMatch,
      severity,
      trendText,
      suggestion,
      action
    });
  });
  
  // Sort Critical to top
  return alerts.sort((a, b) => a.severity === 'Critical' ? -1 : 1);
});

// DSS Action Handlers
const handleDssReactivate = (inactiveMatch: any) => {
  requirePermission('manage', () => {
    selectedItem.value = inactiveMatch;
    actionQuantity.value = inactiveMatch.quantity;
    showDssModal.value = false;
    isReactivateConfirmOpen.value = true;
  });
}

/**
 * Hand off to the procurement wizard, already aimed at this product.
 *
 * The product id travels in the query so the wizard can resolve which partner
 * supplier carries it and queue that supplier's minimum order. Without it the
 * user lands on an empty wizard and has to find the supplier by eye.
 */
const goToProcurement = (item: any) => {
  requirePermission('manage', () => {
    showDssModal.value = false;

    // Fall back to a plain visit when the alert has no product behind it, rather
    // than sending a useless query.
    const productId = item?.product_id;

    toast.info('Redirecting to Procurement Module...');
    router.push({
      path: '/ECommerce/ECProcurement',
      query: productId ? { procure: String(productId) } : undefined
    });
  });
}
// =========================================================================

// Actions
const handleAddProduct = () => toast.info("Add new product feature coming soon.")

const openViewModal = (item: any) => {
  selectedItem.value = item
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  setTimeout(() => {
    selectedItem.value = null;
    actionQuantity.value = '';
  }, 300)
}

const requestDeployment = async () => {
  if (!selectedItem.value) return
  isDeployConfirmOpen.value = false
  isProcessing.value = true
  const toastId = toast.loading('Sending deployment request...')

  try {
    const response = await api.post(`/operation-distributor/ec-inventory/${selectedItem.value.id}/request-deployment`)
    if (response.data.success) {
      const index = inventoryItems.value.findIndex(i => i.id === selectedItem.value.id)
      if (index !== -1) {
        inventoryItems.value[index].ecommerce_status = 'pending'
        selectedItem.value.ecommerce_status = 'pending'
      }
      toast.success('Request Submitted!', { id: toastId, description: `Deployment requested for ${selectedItem.value.name}.` })
      setTimeout(() => closeViewModal(), 1000)
    } else {
      toast.error('Request failed', { id: toastId, description: response.data.message })
    }
  } catch (error: any) {
    if (error.response?.status === 403) toast.error('Unauthorized', { id: toastId, description: 'No permission to request deployment.' })
    else toast.error('Error', { id: toastId, description: 'Could not submit deployment request.' })
  } finally {
    isProcessing.value = false
  }
}

const deactivateItem = async () => {
  if (!selectedItem.value || !isActionQuantityValid.value) return
  isDeactivateConfirmOpen.value = false
  isProcessing.value = true
  const toastId = toast.loading('Moving quantity to inactive...')

  try {
    const response = await api.post(`/operation-distributor/ec-inventory/${selectedItem.value.id}/deactivate`, {
      quantity: actionQuantity.value
    })
    
    if (response.data.success) {
      toast.success('Product Quantity Deactivated', { id: toastId, description: `${actionQuantity.value} units moved to inactive list.` })
      await refreshData()
      closeViewModal()
    } else {
      toast.error('Operation failed', { id: toastId, description: response.data.message })
    }
  } catch (error) {
    toast.error('Failed to deactivate product quantity.', { id: toastId })
  } finally {
    isProcessing.value = false
  }
}

const reactivateItem = async () => {
  if (!selectedItem.value || !isActionQuantityValid.value) return
  isReactivateConfirmOpen.value = false
  isProcessing.value = true
  const toastId = toast.loading('Reactivating quantity...')

  try {
    const response = await api.post(`/operation-distributor/ec-inventory/inactive/${selectedItem.value.id}/reactivate`, {
      quantity: actionQuantity.value
    })
    
    if (response.data.success) {
      toast.success('Product Quantity Reactivated', { id: toastId, description: `${actionQuantity.value} units back in active inventory.` })
      await refreshData()
      closeViewModal()
    } else {
      toast.error('Operation failed', { id: toastId, description: response.data.message })
    }
  } catch (error) {
    toast.error('Failed to reactivate product quantity.', { id: toastId })
  } finally {
    isProcessing.value = false
  }
}
</script>

<style scoped>
.inventory-container { min-height: 100vh; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(31, 41, 55, 0.5); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(75, 85, 99, 0.8); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(107, 114, 128, 1); }
</style>

<style>
/* Unscoped global override to force Sonner Toaster to the very top */
[data-sonner-toaster] {
  z-index: 2147483647 !important; /* Maximum z-index possible */
  position: fixed !important;
}
</style>