<template>
  <div class="arrived-items-container p-4 md:p-6 text-gray-100">
    <Teleport to="body">
      <Toaster
        position="top-right"
        :expand="false"
        :rich-colors="false"
        :close-button="true"
        :theme="'light'"
        :visible-toasts="1"
        :container-style="{
          position: 'fixed',
          top: '50%',
          left: '50%',
          transform: 'translate(-50%, -50%)',
          zIndex: 9999999,
          pointerEvents: 'none',
        }"
        :toast-options="{
          style: {
            background: 'white',
            color: 'black',
            border: 'none',
            boxShadow: '0 4px 15px rgba(0, 0, 0, 0.18)',
            padding: '16px 20px',
            fontSize: '15px',
            minWidth: '280px',
            maxWidth: '400px',
            borderRadius: '10px',
            pointerEvents: 'auto',
          },
        }"
      />
    </Teleport>
    
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Inventory Processing</h1>
          <h2 class="text-gray-300">Review recent deliveries and manage products waiting for supplier return.</h2>
        </div>
        <div class="flex items-center gap-3 mt-4 md:mt-0">
          <Button 
            variant="outline" 
            @click="requirePermission('view', handleExport)"
            class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent w-full sm:w-auto"
          >
            <FileDown class="w-4 h-4 mr-2" />
            Export CSV
          </Button>
        </div>
      </div>
    </div>

    <div class="flex space-x-2 mb-6 bg-gray-900/50 p-1.5 rounded-lg border border-gray-800 w-fit">
      <button 
        @click="activeTab = 'arrived'" 
        :class="['px-5 py-2 text-sm font-medium rounded-md transition-all', activeTab === 'arrived' ? 'bg-indigo-600 text-white shadow-sm' : 'text-white hover:text-white hover:bg-gray-800']"
      >
        Arrived Items ({{ arrivedItems.length }})
      </button>
      <button 
        @click="activeTab = 'returns'" 
        :class="['px-5 py-2 text-sm font-medium rounded-md transition-all', activeTab === 'returns' ? 'bg-red-600 text-white shadow-sm' : 'text-white hover:text-white hover:bg-gray-800']"
      >
        Returns & Replacements ({{ returnItems.length }})
      </button>
    </div>

    <div v-if="activeTab === 'arrived'" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ arrivedItems.length }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Pending Inventory Check</h2></CardDescription>
        </CardHeader>
      </Card>
      
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardHeader class="p-4">
          <CardTitle class="text-2xl font-bold mb-1">
            <Loader2 v-if="isLoading" class="w-6 h-6 animate-spin text-gray-400" />
            <span v-else>{{ totalQuantityArrived }}</span>
          </CardTitle>
          <CardDescription class="text-gray-300"><h2>Total Units Received</h2></CardDescription>
        </CardHeader>
      </Card>
    </div>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white overflow-hidden">
      <CardContent class="p-0">
        
        <div class="p-5 border-b border-gray-800 flex items-center justify-between">
          <div class="relative w-full max-w-md">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Search..." 
              class="pl-10 h-10 bg-gray-800 border-gray-700 text-white placeholder:text-gray-500 focus-visible:ring-indigo-500/50" 
            />
          </div>
        </div>

        <div v-if="activeTab === 'arrived'" class="overflow-x-auto custom-scrollbar">
          <Table>
            <TableHeader class="bg-gray-900/80 border-b border-gray-800">
              <TableRow class="border-0 hover:bg-transparent">
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Request Code</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Date Arrived</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Product Details</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Supplier</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Quantity</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-center">Status</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isLoading" class="border-0 hover:bg-transparent">
                <TableCell colspan="7" class="h-32 text-center">
                  <Loader2 class="w-8 h-8 animate-spin mx-auto text-indigo-500 mb-2" />
                  <span class="text-gray-400">Fetching arrived items...</span>
                </TableCell>
              </TableRow>

              <TableRow v-else-if="filteredItems.length === 0" class="border-0 hover:bg-transparent">
                <TableCell colspan="7" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <PackageX class="w-8 h-8 text-gray-600 mb-2" />
                    <span>No arrived items found.</span>
                  </div>
                </TableCell>
              </TableRow>
              
              <TableRow 
                v-else
                v-for="item in filteredItems" 
                :key="item.id"
                class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors group"
              >
                <TableCell class="font-medium text-white">{{ item.request_code }}</TableCell>
                <TableCell class="text-gray-300">{{ formatDate(item.delivered_at) }}</TableCell>
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div v-if="getImageUrl(item)" class="w-10 h-10 rounded-md bg-gray-800 border border-gray-700 overflow-hidden shrink-0">
                      <img :src="getImageUrl(item)" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-10 h-10 rounded-md bg-gray-800 border border-gray-700 flex items-center justify-center shrink-0">
                      <ImageOff class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="flex flex-col">
                      <span class="font-bold text-white">{{ item.product?.name || item.raw_material_details?.name || item.product_name }}</span>
                      <div class="flex items-center gap-2 text-xs text-gray-400 mt-0.5">
                        <span v-if="item.product?.sku_code || item.raw_material_details?.sku_code">SKU: {{ item.product?.sku_code || item.raw_material_details?.sku_code }}</span>
                        <span v-if="item.product?.size || item.raw_material_details?.size">• Size: {{ item.product?.size || item.raw_material_details?.size }}</span>
                        <span v-if="item.product?.type || item.raw_material_details?.type">• {{ item.product?.type || item.raw_material_details?.type }}</span>
                      </div>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="text-gray-300">{{ item.supplier }}</TableCell>
                <TableCell class="text-right"><span class="font-bold text-emerald-400">{{ item.quantity }}</span></TableCell>
                <TableCell class="text-center">
                  <Badge class="rounded-full border-0 font-medium bg-emerald-500/20 text-emerald-300">Delivered</Badge>
                </TableCell>
                <TableCell class="text-right">
                  <Button @click="openViewModal(item, false)" variant="ghost" size="sm" class="text-indigo-400 hover:text-white hover:bg-indigo-600/20">
                    <h2><Eye class="w-4 h-4 mr-2" /></h2>
                    <h2>View Details</h2>
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div v-if="activeTab === 'returns'" class="overflow-x-auto custom-scrollbar">
          <Table>
            <TableHeader class="bg-gray-900/80 border-b border-gray-800">
              <TableRow class="border-0 hover:bg-transparent">
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Request Code</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Return Date</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400">Product Details</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-center">Supplier</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Return Qty</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-center">Status</TableHead>
                <TableHead class="h-12 text-xs font-medium uppercase tracking-wider text-gray-400 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isReturnsLoading" class="border-0 hover:bg-transparent">
                <TableCell colspan="7" class="h-32 text-center">
                  <Loader2 class="w-8 h-8 animate-spin mx-auto text-red-500 mb-2" />
                  <span class="text-gray-400">Fetching return items...</span>
                </TableCell>
              </TableRow>

              <TableRow v-else-if="filteredReturns.length === 0" class="border-0 hover:bg-transparent">
                <TableCell colspan="7" class="h-32 text-center text-gray-400">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <PackageX class="w-8 h-8 text-gray-600 mb-2" />
                    <span>No items are currently flagged for return.</span>
                  </div>
                </TableCell>
              </TableRow>
              
              <TableRow 
                v-else
                v-for="ret in filteredReturns" 
                :key="ret.id"
                class="border-b border-gray-800 hover:bg-gray-800/50 transition-colors"
              >
                <TableCell class="font-medium text-white">{{ ret.procurement_request?.request_code || 'N/A' }}</TableCell>
                <TableCell class="text-gray-300">{{ formatDate(ret.created_at) }}</TableCell>
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div v-if="getImageUrl(ret.procurement_request)" class="w-10 h-10 rounded-md bg-gray-800 border border-gray-700 overflow-hidden shrink-0">
                      <img :src="getImageUrl(ret.procurement_request)" class="w-full h-full object-cover" />
                    </div>
                    <div v-else class="w-10 h-10 rounded-md bg-gray-800 border border-gray-700 flex items-center justify-center shrink-0">
                      <ImageOff class="w-5 h-5 text-gray-500" />
                    </div>
                    <div class="flex flex-col">
                      <span class="font-bold text-white">{{ ret.procurement_request?.product_name || 'Product' }}</span>
                      <p class="text-xs text-white mt-0.5 max-w-50 truncate" :title="ret.reason">Reason: {{ ret.reason }}</p>
                    </div>
                  </div>
                </TableCell>
                <TableCell class="text-white text-center">{{ ret.procurement_request?.supplier || 'N/A' }}</TableCell>
                <TableCell class="text-right"><h2 class="font-bold text-red-400">{{ ret.quantity_returned }}</h2></TableCell>
                <TableCell class="text-center">
                  <Badge class="rounded-full border-0 font-medium capitalize" :class="statusColor(ret.status)">
                    {{ formatStatus(ret.status) }}
                  </Badge>
                </TableCell>
                <TableCell class="text-right">
                  <Button @click="openViewModal(ret, true)" variant="ghost" size="sm" class="text-indigo-400 hover:text-white hover:bg-indigo-600/20">
                    <h2><Eye class="w-4 h-4 mr-2" /></h2>
                    <h2>View Details</h2>
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <div class="p-4 border-t border-gray-800 flex items-center justify-between text-sm text-gray-400">
          <div v-if="activeTab === 'arrived'">
            Showing <span class="text-white font-medium">{{ filteredItems.length > 0 ? 1 : 0 }}</span> to <span class="text-white font-medium">{{ filteredItems.length }}</span> of <span class="text-white font-medium">{{ arrivedItems.length }}</span> entries
          </div>
          <div v-else>
            Showing <span class="text-white font-medium">{{ filteredReturns.length > 0 ? 1 : 0 }}</span> to <span class="text-white font-medium">{{ filteredReturns.length }}</span> of <span class="text-white font-medium">{{ returnItems.length }}</span> return items
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewModal" @update:open="(val) => !val && closeViewModal()">
      <DialogContent class="bg-gray-900 border-gray-800 text-white w-[95vw] sm:max-w-3xl custom-scrollbar max-h-[90vh] overflow-y-auto p-4 sm:p-6">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex flex-wrap items-center gap-2">
            <template v-if="!isViewingReturn">
              Delivery Receipt: <span class="text-emerald-400 break-all">{{ selectedItem?.request_code }}</span>
            </template>
            <template v-else>
              Return Details: <span class="text-red-400 break-all">{{ selectedItem?.procurement_request?.request_code }}</span>
            </template>
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            <template v-if="!isViewingReturn">
              Review the delivered product details and delivery proof before adding to your inventory.
            </template>
            <template v-else>
              Review the details of your return request and the supplier's response.
            </template>
          </DialogDescription>
        </DialogHeader>

        <div v-if="selectedItem" class="space-y-6 py-2">
          
          <div class="bg-gray-800/50 rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-4 border-b border-gray-700 bg-gray-800/80">
              <h3 class="text-sm font-semibold text-white flex items-center gap-2" :class="isViewingReturn ? 'text-red-400' : 'text-emerald-400'">
                <Package class="w-4 h-4" />
                Product Information
              </h3>
            </div>
            <div class="p-4 flex flex-col sm:flex-row gap-6">
              <div class="w-full sm:w-1/3 aspect-square bg-gray-950 rounded-lg border border-gray-700 overflow-hidden flex items-center justify-center shrink-0">
                <img 
                  v-if="getImageUrl(isViewingReturn ? selectedItem.procurement_request : selectedItem)" 
                  :src="getImageUrl(isViewingReturn ? selectedItem.procurement_request : selectedItem)" 
                  alt="Product Image" 
                  class="w-full h-full object-cover"
                />
                <div v-else class="text-gray-600 flex flex-col items-center">
                  <ImageOff class="w-8 h-8 mb-2 opacity-50" />
                  <span class="text-xs">No Image</span>
                </div>
              </div>

              <div class="w-full sm:w-2/3 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="col-span-1 sm:col-span-2">
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Product Name</p>
                  <p class="font-bold text-lg text-white break-words">
                    <template v-if="!isViewingReturn">
                      {{ selectedItem.product?.name || selectedItem.raw_material_details?.name || selectedItem.product_name }}
                    </template>
                    <template v-else>
                      {{ selectedItem.procurement_request?.product_name || 'N/A' }}
                    </template>
                  </p>
                </div>
                
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Supplier</p>
                  <p class="font-medium text-gray-200 break-words">
                    <template v-if="!isViewingReturn">{{ selectedItem.supplier }}</template>
                    <template v-else>{{ selectedItem.procurement_request?.supplier }}</template>
                  </p>
                </div>
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Category</p>
                  <p class="font-medium text-gray-200 break-words">
                    <template v-if="!isViewingReturn">{{ selectedItem.category }}</template>
                    <template v-else>{{ selectedItem.procurement_request?.category }}</template>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <template v-if="!isViewingReturn">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 p-4 bg-gray-800/30 rounded-xl border border-gray-800">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Supplier</p>
                <p class="font-medium text-white break-words">{{ selectedItem.supplier }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Unit Price</p>
                <p class="font-medium text-white">₱{{ Number(selectedItem.unit_price).toLocaleString() }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Quantity Received</p>
                <p class="font-bold text-emerald-400">{{ selectedItem.quantity }} Units</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Arrival Date</p>
                <p class="font-medium text-white">{{ formatDate(selectedItem.delivered_at) }}</p>
              </div>
            </div>

            <div class="space-y-3">
              <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <Camera class="w-4 h-4 text-emerald-400" />
                Proof of Delivery
              </h3>
              <div class="w-full bg-gray-950 border border-gray-800 rounded-xl overflow-hidden flex items-center justify-center min-h-64 sm:min-h-75 p-2">
                <img 
                  v-if="selectedItem.arrival_proof_path" 
                  :src="getProofUrl(selectedItem.arrival_proof_path)" 
                  alt="Arrival Proof" 
                  class="max-w-full max-h-100 object-contain rounded-lg"
                />
                <div v-else class="text-gray-500 flex flex-col items-center py-10">
                  <ImageOff class="w-10 h-10 mb-2 opacity-50" />
                  <p>No proof image available.</p>
                </div>
              </div>
            </div>
          </template>

          <template v-else>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-800/50 border border-gray-700 rounded-xl">
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Quantity Returned</p>
                <p class="font-bold text-xl text-red-400">{{ selectedItem.quantity_returned }} Units</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Return Status</p>
                <Badge class="capitalize text-xs px-2 py-1 w-fit" :class="statusColor(selectedItem.status)">
                  {{ formatStatus(selectedItem.status) }}
                </Badge>
              </div>
              <div class="col-span-1 sm:col-span-2">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Your Reason</p>
                <p class="text-sm italic bg-gray-950 p-3 rounded border border-gray-800 text-gray-300 break-words">"{{ selectedItem.reason }}"</p>
              </div>
              <div v-if="selectedItem.status === 'rejected'" class="col-span-1 sm:col-span-2">
                <p class="text-xs text-red-500 uppercase tracking-wider font-semibold mb-1">Supplier Rejection Reason</p>
                <p class="text-sm italic bg-red-900/30 p-3 rounded border border-red-500/50 text-white font-medium break-words">"{{ selectedItem.rejection_reason }}"</p>
              </div>
            </div>

            <div class="space-y-3">
              <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                <Camera class="w-4 h-4 text-red-400" />
                Your Proof of Return
              </h3>
              <div class="w-full bg-gray-950 border border-gray-800 rounded-xl overflow-hidden flex items-center justify-center p-4">
                <img 
                  v-if="selectedItem.proof_image_path" 
                  :src="getImageUrl(selectedItem.proof_image_path)" 
                  alt="Return Proof" 
                  class="max-w-full max-h-64 object-contain rounded-lg"
                />
              </div>
            </div>

            <div v-if="['prepared', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'completed'].includes(selectedItem.status)" class="space-y-4">
              <div class="flex items-center gap-4 mt-6">
                <Separator class="flex-1 border-gray-800" />
                <span class="text-[10px] font-bold text-gray-500 uppercase tracking-widest text-center">Supplier's Replacement Details</span>
                <Separator class="flex-1 border-gray-800" />
              </div>

              <Card v-if="selectedItem.replacement_receipt_path" class="bg-emerald-950/20 border-emerald-900/50 text-white">
                <CardHeader class="pb-2">
                  <CardTitle class="text-sm text-emerald-400 flex items-center gap-2">
                    <FileText class="h-4 w-4" /> Official Replacement Receipt
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-gray-950 p-3 rounded-lg border border-gray-800">
                    <div class="flex items-center gap-3">
                      <FileCheck class="h-6 w-6 text-emerald-500 shrink-0" />
                      <div>
                        <p class="text-sm font-medium text-white">Replacement Document</p>
                        <p class="text-xs text-gray-500">Generated by Supplier</p>
                      </div>
                    </div>
                    <a :href="getProofUrl(selectedItem.replacement_receipt_path)" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center rounded-md text-sm font-medium border border-gray-700 bg-gray-800 hover:bg-gray-700 hover:text-white h-9 px-4 py-2">
                      <FileText class="h-4 w-4 mr-2" /> View Document
                    </a>
                  </div>
                </CardContent>
              </Card>

              <Card v-if="selectedItem.replacement_proof_path" class="bg-emerald-950/20 border-emerald-900/50 text-white">
                <CardHeader class="pb-2">
                  <CardTitle class="text-sm text-emerald-400 flex items-center gap-2">
                    <Camera class="h-4 w-4" /> Proof of Replacement Preparation
                  </CardTitle>
                </CardHeader>
                <CardContent class="flex justify-center bg-gray-950 p-4 rounded-lg border border-gray-800 mx-0 sm:mx-6 mb-2 sm:mb-6">
                  <img :src="getProofUrl(selectedItem.replacement_proof_path)" class="max-w-full max-h-64 object-contain rounded-lg" />
                </CardContent>
              </Card>

              <Card v-if="selectedItem.replacement_arrival_proof_path && ['delivered', 'completed'].includes(selectedItem.status)" class="bg-emerald-950/20 border-emerald-900/50 text-white mt-4">
                <CardHeader class="pb-2">
                  <CardTitle class="text-sm text-emerald-400 flex items-center gap-2">
                    <Camera class="h-4 w-4" /> Proof of Replacement Delivery
                  </CardTitle>
                </CardHeader>
                <CardContent class="flex justify-center bg-gray-950 p-4 rounded-lg border border-gray-800 mx-0 sm:mx-6 mb-2 sm:mb-6">
                  <img :src="getProofUrl(selectedItem.replacement_arrival_proof_path)" class="max-w-full max-h-64 object-contain rounded-lg" />
                </CardContent>
              </Card>

            </div>
          </template>

        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-gray-800 mt-4">
          <template v-if="!isViewingReturn">
            <Button 
              variant="outline" 
              @click="requirePermission('manage', openReturnModal)"
              class="border-red-900/50 text-red-400 hover:bg-red-900/30 hover:text-red-300 bg-transparent w-full sm:w-auto sm:mr-auto"
            >
              <AlertTriangle class="w-4 h-4 mr-2" />
              Return Items
            </Button>

            <Button 
              variant="ghost" 
              @click="closeViewModal" 
              :disabled="isMoving"
              class="text-gray-400 hover:text-white hover:bg-gray-800 w-full sm:w-auto"
            >
              Cancel
            </Button>

            <AlertDialog>
              <AlertDialogTrigger as-child>
                <Button 
                  :disabled="isMoving"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white border-0 w-full sm:w-auto"
                >
                  <Loader2 v-if="isMoving" class="w-4 h-4 mr-2 animate-spin" />
                  <CheckCircle2 v-else class="w-4 h-4 mr-2" />
                  Move to Inventory
                </Button>
              </AlertDialogTrigger>
              <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white w-[95vw] sm:max-w-md">
                <AlertDialogHeader>
                  <AlertDialogTitle>Confirm Move to Inventory</AlertDialogTitle>
                  <AlertDialogDescription class="text-gray-400">
                    Are you sure you want to officially move <span class="text-emerald-400 font-bold">{{ selectedItem?.quantity }}</span> units of <span class="font-bold text-gray-200">{{ selectedItem?.product?.name || selectedItem?.raw_material_details?.name || selectedItem?.product_name }}</span> to your inventory? This action will update your stock availability and cannot be undone.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="flex-col-reverse sm:flex-row gap-2 sm:gap-0 mt-4">
                  <AlertDialogCancel class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent mt-0">
                    Cancel
                  </AlertDialogCancel>
                  <AlertDialogAction @click="requirePermission('manage', moveToInventory)" class="bg-emerald-600 hover:bg-emerald-700 text-white border-0">
                    Yes, Move to Inventory
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </template>

          <template v-else>
            <Button variant="ghost" @click="closeViewModal" class="text-gray-400 hover:text-white hover:bg-gray-800 w-full sm:w-auto" :disabled="isMoving">
              Close Details
            </Button>

            <AlertDialog v-if="selectedItem?.status === 'delivered'">
              <AlertDialogTrigger as-child>
                <Button 
                  :disabled="isMoving"
                  class="bg-emerald-600 hover:bg-emerald-700 text-white border-0 w-full sm:w-auto whitespace-normal h-auto py-2.5 sm:py-2 text-center"
                >
                  <Loader2 v-if="isMoving" class="w-4 h-4 mr-2 shrink-0 animate-spin" />
                  <CheckCircle2 v-else class="w-4 h-4 mr-2 shrink-0" />
                  <span>Move Replacement to Inventory</span>
                </Button>
              </AlertDialogTrigger>
              <AlertDialogContent class="bg-gray-900 border border-gray-800 text-white w-[95vw] sm:max-w-md">
                <AlertDialogHeader>
                  <AlertDialogTitle>Confirm Move to Inventory</AlertDialogTitle>
                  <AlertDialogDescription class="text-gray-400">
                    Are you sure you want to add the <span class="text-emerald-400 font-bold">{{ selectedItem?.quantity_returned }}</span> replacement units of <span class="font-bold text-gray-200">{{ selectedItem?.procurement_request?.product_name }}</span> to your inventory? This will mark the return process as complete.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter class="flex-col-reverse sm:flex-row gap-2 sm:gap-0 mt-4">
                  <AlertDialogCancel class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent mt-0">
                    Cancel
                  </AlertDialogCancel>
                  <AlertDialogAction @click="requirePermission('manage', moveReplacementToInventory)" class="bg-emerald-600 hover:bg-emerald-700 text-white border-0">
                    Yes, Move to Inventory
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </template>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showReturnModal" @update:open="(val) => !val && closeReturnModal()">
      <DialogContent class="bg-gray-900 border-gray-800 text-white w-[95vw] sm:max-w-md">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold flex items-center gap-2 text-red-400">
            <AlertTriangle class="w-5 h-5" />
            Process Return
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Specify how many items you are returning and provide a reason.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">Quantity to Return (Max: {{ selectedItem?.quantity }})</label>
            <Input 
              type="number" 
              v-model="returnQuantity" 
              min="1" 
              :max="selectedItem?.quantity"
              class="bg-gray-950 border-gray-700 text-white focus-visible:ring-red-500"
            />
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">Reason for Return</label>
            <textarea 
              v-model="returnReason" 
              rows="3"
              class="w-full rounded-md border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-red-500 disabled:cursor-not-allowed disabled:opacity-50"
              placeholder="e.g. Damaged upon arrival, incorrect item..."
            ></textarea>
          </div>

          <div class="space-y-2">
            <label class="text-sm font-medium text-gray-300">Proof Image</label>
            <div class="flex items-center gap-2">
              <label class="flex h-10 w-full cursor-pointer items-center justify-center rounded-md border border-dashed border-gray-600 bg-gray-950 px-4 py-2 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <Upload class="mr-2 h-4 w-4" />
                <span class="truncate max-w-[200px]">{{ returnProofFile ? returnProofFile.name : 'Upload Photo' }}</span>
                <input type="file" class="hidden" accept="image/*" @change="handleReturnFileChange" />
              </label>
              <Button v-if="returnProofFile" variant="ghost" size="icon" @click="removeReturnFile" class="text-gray-400 hover:text-red-400 shrink-0">
                <X class="h-4 w-4" />
              </Button>
            </div>
            
            <div v-if="returnProofPreview" class="mt-2 rounded-lg overflow-hidden border border-gray-700 max-h-40 flex justify-center bg-gray-950">
              <img :src="returnProofPreview" class="object-contain" />
            </div>
          </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-4 border-t border-gray-800 mt-2">
          <Button variant="ghost" @click="closeReturnModal" :disabled="isReturning" class="text-gray-400 hover:text-white hover:bg-gray-800 w-full sm:w-auto">
            Cancel
          </Button>
          <Button @click="requirePermission('manage', submitReturn)" :disabled="isReturning || !isReturnValid" class="bg-red-600 hover:bg-red-700 text-white border-0 w-full sm:w-auto">
            <Loader2 v-if="isReturning" class="w-4 h-4 mr-2 animate-spin" />
            Submit Return
          </Button>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { Search, FileDown, Eye, PackageX, Loader2, CheckCircle2, Package, ImageOff, Camera, AlertTriangle, Upload, X, FileText, FileCheck } from 'lucide-vue-next'
import { toast } from 'vue-sonner'

// Standard UI Components
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Separator } from '@/components/ui/separator'

// Alert Dialog Components
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'

// Tabs State
const activeTab = ref('arrived')

// Data State
const arrivedItems = ref<any[]>([])
const returnItems = ref<any[]>([])
const isLoading = ref(true)
const isReturnsLoading = ref(true)
const searchQuery = ref('')

const showViewModal = ref(false)
const selectedItem = ref<any>(null)
const isMoving = ref(false)
const isViewingReturn = ref(false)

// Return State
const showReturnModal = ref(false)
const returnQuantity = ref<number | ''>('')
const returnReason = ref('')
const returnProofFile = ref<File | null>(null)
const returnProofPreview = ref<string | null>(null)
const isReturning = ref(false)

// Updated to the new Level-Based framework
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// RBAC Action Interceptor
const requirePermission = (action: string, callback: Function | null = null) => {
  if (!(permissions.value as any)[`can_${action}`]) {
    toast.error(`Access Denied`, { description: `You do not have permission to ${action} arrived items.` });
    return false;
  }
  if (callback) callback();
  return true;
}

// Dynamic URL Helpers
const buildStorageUrl = (path: string) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  
  const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
  
  // @ts-ignore - Suppress TS error for import.meta in older module targets
  let baseUrl = ((import.meta as any).env?.VITE_API_URL as string) || (isLocalhost ? 'http://localhost:8000' : 'https://api.capstone001.com');
  
  baseUrl = baseUrl.replace(/\/+$/, '');
  const cleanPath = path.replace(/^\/+/, '');
  
  if (cleanPath.startsWith('storage/')) {
    return `${baseUrl}/${cleanPath}`;
  }
  return `${baseUrl}/storage/${cleanPath}`;
};

const getImageUrl = (item: any) => {
  if (!item) return null;
  
  if (typeof item === 'string') {
    return buildStorageUrl(item);
  }

  let path = null;
  if (item.product?.image_url) {
    path = item.product.image_url;
  } else if (item.raw_material_details?.image_url) {
    path = item.raw_material_details.image_url;
  }
  
  if (!path) return null;
  return buildStorageUrl(path);
}

const getProofUrl = (path: string) => {
  if (!path) return null;
  return buildStorageUrl(path);
}

// Fetch Arrived Items
const fetchArrivedItems = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/operation-distributor/arrived-items')
    let responseData = response.data;
    if (typeof responseData === 'string') {
      try { responseData = JSON.parse(responseData); } catch (e) {}
    }
    if (responseData && Array.isArray(responseData.data)) {
      arrivedItems.value = responseData.data;
      if (responseData.permissions) {
          permissions.value = responseData.permissions;
      }
    } else if (Array.isArray(responseData)) {
      arrivedItems.value = responseData;
    } else {
      arrivedItems.value = [];
    }
  } catch (error) {
    toast.error('Failed to load arrived items')
  } finally {
    isLoading.value = false
  }
}

// Fetch Return Items
const fetchReturnItems = async () => {
  try {
    isReturnsLoading.value = true
    const response = await api.get('/operation-distributor/arrived-items/returns')
    if (response.data.success) {
      returnItems.value = response.data.data
    }
  } catch (error) {
    toast.error('Failed to load return items')
  } finally {
    isReturnsLoading.value = false
  }
}

const handleExport = () => {
  toast.info("Exporting CSV started...")
  setTimeout(() => {
    toast.success("CSV exported successfully")
  }, 1000)
}

onMounted(() => {
  fetchArrivedItems()
  fetchReturnItems()
})

// Computeds
const totalQuantityArrived = computed(() => {
  if (!Array.isArray(arrivedItems.value)) return 0;
  return arrivedItems.value.reduce((sum, item) => sum + Number(item.quantity), 0)
})

const filteredItems = computed(() => {
  if (!Array.isArray(arrivedItems.value)) return [];
  if (!searchQuery.value) return arrivedItems.value
  const query = searchQuery.value.toLowerCase()
  return arrivedItems.value.filter(item => 
    item.product_name?.toLowerCase().includes(query) ||
    item.product?.name?.toLowerCase().includes(query) ||
    item.raw_material_details?.name?.toLowerCase().includes(query) ||
    item.supplier?.toLowerCase().includes(query) ||
    item.request_code?.toLowerCase().includes(query)
  )
})

const filteredReturns = computed(() => {
  if (!Array.isArray(returnItems.value)) return [];
  if (!searchQuery.value) return returnItems.value
  const query = searchQuery.value.toLowerCase()
  return returnItems.value.filter(ret => 
    ret.procurement_request?.request_code?.toLowerCase().includes(query) ||
    ret.procurement_request?.product_name?.toLowerCase().includes(query) ||
    ret.procurement_request?.supplier?.toLowerCase().includes(query)
  )
})

// Dynamic Formatting Helpers
const statusColor = (status: string) => {
  switch (status) {
    case 'pending': return 'bg-amber-500/20 text-amber-300'
    case 'processing': return 'bg-blue-500/20 text-blue-300'
    case 'prepared': return 'bg-indigo-500/20 text-indigo-300'
    case 'shipped': 
    case 'out_for_delivery':
    case 'in_transit': return 'bg-purple-500/20 text-purple-300'
    case 'delivered': return 'bg-emerald-500/20 text-emerald-400'
    case 'completed': return 'bg-teal-500/20 text-teal-300'
    case 'rejected': return 'bg-red-500/20 text-red-300'
    default: return 'bg-gray-500/20 text-gray-300'
  }
}

const formatStatus = (status: string) => {
  if (status === 'in_transit') return 'In Transit'
  if (status === 'out_for_delivery') return 'Out for Delivery'
  if (status === 'prepared') return 'Replacement Prepared'
  if (status === 'delivered') return 'Replacement Delivered'
  return status
}

// Actions
const openViewModal = (item: any, isReturn: boolean = false) => {
  selectedItem.value = item
  isViewingReturn.value = isReturn
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  setTimeout(() => { 
    selectedItem.value = null
    isViewingReturn.value = false 
  }, 300)
}

const moveToInventory = async () => {
  if (!selectedItem.value) return

  const toastId = toast.loading('Moving items to inventory...')
  try {
    isMoving.value = true
    await api.post(`/operation-distributor/arrived-items/${selectedItem.value.id}/move-to-inventory`)
    
    arrivedItems.value = arrivedItems.value.filter(i => i.id !== selectedItem.value.id)
    
    toast.success('Successfully moved to inventory!', { id: toastId, description: `${selectedItem.value.quantity} units added.` })
    closeViewModal()
  } catch (error: any) {
    toast.error('Operation Failed', { id: toastId, description: error.response?.data?.message || "An error occurred." })
  } finally {
    isMoving.value = false
  }
}

const moveReplacementToInventory = async () => {
  if (!selectedItem.value) return

  const toastId = toast.loading('Moving replacement to inventory...')
  try {
    isMoving.value = true
    await api.post(`/operation-distributor/arrived-items/returns/${selectedItem.value.id}/move-to-inventory`)
    
    toast.success('Inventory Updated!', { id: toastId, description: `${selectedItem.value.quantity_returned} replacement units added.` })
    await fetchReturnItems()
    closeViewModal()
  } catch (error: any) {
    toast.error('Operation Failed', { id: toastId, description: error.response?.data?.message || "An error occurred." })
  } finally {
    isMoving.value = false
  }
}

// Return Methods
const isReturnValid = computed(() => {
  return returnQuantity.value !== '' 
      && Number(returnQuantity.value) > 0 
      && Number(returnQuantity.value) <= (selectedItem.value?.quantity || 0)
      && returnReason.value.trim() !== ''
      && returnProofFile.value !== null
})

const openReturnModal = () => {
  returnQuantity.value = ''
  returnReason.value = ''
  returnProofFile.value = null
  returnProofPreview.value = null
  showReturnModal.value = true
}

const closeReturnModal = () => {
  showReturnModal.value = false
}

const handleReturnFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    returnProofFile.value = file
    const reader = new FileReader()
    reader.onload = (ev) => {
      returnProofPreview.value = ev.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeReturnFile = () => {
  returnProofFile.value = null
  returnProofPreview.value = null
}

const submitReturn = async () => {
  if (!isReturnValid.value || !selectedItem.value) return

  const toastId = toast.loading('Processing return...')
  try {
    isReturning.value = true
    const formData = new FormData()
    formData.append('quantity_returned', String(returnQuantity.value))
    formData.append('reason', returnReason.value)
    if (returnProofFile.value) {
      formData.append('proof_image', returnProofFile.value)
    }

    const response = await api.post(`/operation-distributor/arrived-items/${selectedItem.value.id}/return`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    toast.success('Return Processed', { id: toastId, description: 'The return request has been submitted.' })
    
    // Refresh the Returns Tab list
    await fetchReturnItems()
    
    // Update local Arrived Items list
    const remainingQty = response.data.remaining_quantity
    if (remainingQty === 0) {
      arrivedItems.value = arrivedItems.value.filter(i => i.id !== selectedItem.value.id)
    } else {
      const idx = arrivedItems.value.findIndex(i => i.id === selectedItem.value.id)
      if (idx !== -1) arrivedItems.value[idx].quantity = remainingQty
    }

    closeReturnModal()
    closeViewModal()

  } catch (error: any) {
    toast.error('Return Failed', { id: toastId, description: error.response?.data?.message || 'Failed to submit return.' })
  } finally {
    isReturning.value = false
  }
}

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date)
}
</script>

<style scoped>
.arrived-items-container { min-height: 100vh; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(31, 41, 55, 0.5); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(75, 85, 99, 0.8); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(107, 114, 128, 1); }
</style>