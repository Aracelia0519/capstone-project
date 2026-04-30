<template>
  <div class="min-h-screen p-4 md:p-6">
    <header class="mb-8">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <div class="p-2 bg-indigo-100 rounded-lg mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Finance Approval Portal</h1>
        </div>
        <div class="text-sm bg-gray-100 px-3 py-1 rounded-full flex items-center">
          <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
          <span class="font-medium text-gray-600">Active Session</span>
        </div>
      </div>

      <div v-if="!loading || statistics.total_requests > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        
        <Card class="bg-white shadow-sm border-slate-200">
          <div class="p-5 flex items-center justify-between">
            <div>
              <h3 class="text-slate-500 text-sm font-medium mb-1">Pending Review</h3>
              <div class="text-2xl font-bold text-amber-600">{{ statistics.pending }}</div>
            </div>
            <div class="p-3 bg-amber-50 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="px-5 pb-4">
            <div class="text-xs font-medium text-slate-500 mb-1">Value: ₱{{ formatCurrency(statistics.pending_amount) }}</div>
            <Progress :model-value="getPercentage(statistics.pending, statistics.total_requests)" class="h-1.5 bg-slate-100" />
          </div>
        </Card>

        <Card class="bg-white shadow-sm border-slate-200">
          <div class="p-5 flex items-center justify-between">
            <div>
              <h3 class="text-slate-500 text-sm font-medium mb-1">Approved This Month</h3>
              <div class="text-2xl font-bold text-emerald-600">{{ statistics.approved }}</div>
            </div>
            <div class="p-3 bg-emerald-50 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="px-5 pb-4">
            <div class="text-xs font-medium text-slate-500 mb-1">Allocated: ₱{{ formatCurrency(statistics.approved_amount) }}</div>
            <Progress :model-value="getPercentage(statistics.approved, statistics.total_requests)" class="h-1.5 bg-slate-100" />
          </div>
        </Card>

        <Card class="bg-white shadow-sm border-slate-200">
          <div class="p-5 flex items-center justify-between">
            <div>
              <h3 class="text-slate-500 text-sm font-medium mb-1">Rejected This Month</h3>
              <div class="text-2xl font-bold text-rose-600">{{ statistics.rejected }}</div>
            </div>
            <div class="p-3 bg-rose-50 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="px-5 pb-4">
            <div class="text-xs font-medium text-slate-500 mb-1">Saved: ₱{{ formatCurrency(statistics.rejected_amount) }}</div>
            <Progress :model-value="getPercentage(statistics.rejected, statistics.total_requests)" class="h-1.5 bg-slate-100" />
          </div>
        </Card>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <Skeleton class="h-32 w-full rounded-xl" v-for="i in 3" :key="i" />
      </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <div class="lg:col-span-1 space-y-4">
        <div class="flex items-center justify-between mb-2">
          <h2 class="text-lg font-semibold text-gray-800 flex items-center">
            Pending Requests 
            <Badge variant="outline" class="ml-2 bg-amber-50 text-amber-700 border-amber-200">{{ pendingRequests.length }}</Badge>
          </h2>
          <Button variant="ghost" size="sm" @click="refreshData" class="text-slate-500 h-8 px-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </Button>
        </div>

        <div class="space-y-3 pr-1 max-h-[calc(100vh-280px)] overflow-y-auto overflow-x-hidden custom-scrollbar">
          
          <template v-if="loading && pendingRequests.length === 0">
            <Card v-for="i in 5" :key="i" class="p-4 shadow-sm border-slate-100">
              <div class="flex justify-between mb-3">
                <Skeleton class="h-5 w-32" />
                <Skeleton class="h-5 w-16" />
              </div>
              <Skeleton class="h-4 w-full mb-2" />
              <Skeleton class="h-4 w-2/3" />
            </Card>
          </template>

          <template v-else-if="pendingRequests.length > 0">
            <Card 
              v-for="request in pendingRequests" 
              :key="request.id"
              class="p-4 cursor-pointer transition-all duration-200 border-l-4 hover:shadow-md"
              :class="[
                selectedRequestId === request.id 
                  ? 'border-l-indigo-500 bg-indigo-50/30 shadow-md ring-1 ring-indigo-500/20' 
                  : 'border-l-amber-400 hover:border-l-amber-500 border-slate-200 hover:bg-slate-50'
              ]"
              @click="selectRequest(request)"
            >
              <div class="flex justify-between items-start mb-2">
                <div>
                  <span class="text-xs font-mono font-medium text-slate-500">{{ request.request_code }}</span>
                  <h3 class="font-semibold text-slate-800 line-clamp-1 mt-0.5" :title="request.product_name">
                    {{ request.product_name }}
                  </h3>
                </div>
                <Badge :class="getCategoryClass(request.category)" variant="outline" class="whitespace-nowrap shrink-0 text-[10px] px-1.5 py-0">
                  {{ request.category }}
                </Badge>
              </div>
              
              <div class="flex items-center text-xs text-slate-500 mb-3">
                <span class="mr-2">By: <span class="font-medium text-slate-700">{{ request.requester?.first_name }} {{ request.requester?.last_name }}</span></span>
              </div>
              
              <div class="flex items-end justify-between mt-2 pt-3 border-t border-slate-100">
                <div class="text-xs text-slate-500">
                  <div>Req: {{ formatDate(request.request_date) }}</div>
                </div>
                <div class="text-right">
                  <div class="text-xs text-slate-500">Total Amount</div>
                  <div class="font-bold text-slate-800">
                    ₱{{ formatCurrency(request.total_cost) }}
                  </div>
                </div>
              </div>
            </Card>
            
            <div class="pt-2 pb-6 flex justify-center">
              <Button 
                v-if="hasMore" 
                variant="outline" 
                class="w-full text-slate-500 border-dashed" 
                @click="loadMore" 
                :disabled="loadingMore"
              >
                <template v-if="loadingMore">
                  <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  Loading more...
                </template>
                <template v-else>Load More Requests</template>
              </Button>
              <span v-else-if="pendingRequests.length > 5" class="text-xs text-slate-400 italic">No more pending requests</span>
            </div>
          </template>

          <div v-else class="text-center py-12 px-4 bg-slate-50 rounded-xl border border-dashed border-slate-200">
            <div class="bg-slate-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <h3 class="text-slate-700 font-medium mb-1">All caught up!</h3>
            <p class="text-sm text-slate-500">There are no pending requests waiting for your approval.</p>
          </div>

        </div>
      </div>

      <div class="lg:col-span-2">
        <Card v-if="selectedRequest" class="shadow-md border-slate-200 h-full flex flex-col relative overflow-hidden">
          
          <div v-if="detailsLoading" class="absolute inset-0 z-50 bg-white/60 backdrop-blur-sm flex items-center justify-center">
            <div class="flex flex-col items-center">
              <svg class="animate-spin h-8 w-8 text-indigo-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              <span class="text-sm font-medium text-slate-600">Loading details...</span>
            </div>
          </div>

          <div class="p-6 border-b border-slate-100 flex-none bg-slate-50/50">
            <div class="flex justify-between items-start mb-4">
              <div>
                <div class="flex items-center mb-1">
                  <Badge variant="outline" class="mr-2 bg-amber-50 text-amber-700 border-amber-200">Pending Finance Review</Badge>
                  <span class="text-sm font-mono text-slate-500">{{ selectedRequest.request_code }}</span>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">{{ selectedRequest.product_name }}</h2>
              </div>
              <div class="text-right">
                <div class="text-sm font-medium text-slate-500 mb-1">Requested Amount</div>
                <div class="text-3xl font-bold text-slate-800">
                  ₱{{ formatCurrency(selectedRequest.total_cost) }}
                </div>
              </div>
            </div>
          </div>

          <div class="flex-grow overflow-y-auto p-6 space-y-8 custom-scrollbar">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div>
                <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                  Requisition Information
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold mr-3">
                      {{ getInitials(selectedRequest.requester?.first_name + ' ' + selectedRequest.requester?.last_name) }}
                    </div>
                    <div>
                      <div class="text-sm font-medium text-slate-800">{{ selectedRequest.requester?.first_name }} {{ selectedRequest.requester?.last_name }}</div>
                      <div class="text-xs text-slate-500">Requesting Employee</div>
                    </div>
                  </div>
                  <Separator class="bg-slate-200" />
                  <div class="grid grid-cols-2 gap-y-3 gap-x-2">
                    <div>
                      <div class="text-xs text-slate-500 mb-0.5">Supplier Name</div>
                      <div class="text-sm font-medium text-slate-800">{{ selectedRequest.supplier || 'N/A' }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-slate-500 mb-0.5">Requested Date</div>
                      <div class="text-sm font-medium text-slate-800">{{ formatDate(selectedRequest.request_date) }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-slate-500 mb-0.5">Required By</div>
                      <div class="text-sm font-medium text-slate-800">{{ formatDate(selectedRequest.required_by_date) }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-slate-500 mb-0.5">Priority Level</div>
                      <Badge variant="outline" :class="{
                        'bg-rose-50 text-rose-700 border-rose-200': selectedRequest.priority === 'high',
                        'bg-amber-50 text-amber-700 border-amber-200': selectedRequest.priority === 'medium',
                        'bg-emerald-50 text-emerald-700 border-emerald-200': selectedRequest.priority === 'low'
                      }">
                        <span class="capitalize text-xs">{{ selectedRequest.priority || 'Normal' }}</span>
                      </Badge>
                    </div>
                  </div>
                </div>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" /></svg>
                  Logistics & Payment
                </h3>
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                  <div class="grid grid-cols-2 gap-y-3 gap-x-2">
                    <div>
                      <div class="text-xs text-slate-500 mb-0.5">Payment Terms</div>
                      <div class="text-sm font-medium text-slate-800 uppercase tracking-wider">{{ selectedRequest.payment_terms || 'COD' }}</div>
                    </div>
                    <div>
                      <div class="text-xs text-slate-500 mb-0.5">Shipping Method</div>
                      <div class="text-sm font-medium text-slate-800 capitalize">{{ selectedRequest.shipping_method || 'Standard' }}</div>
                    </div>
                    <div class="col-span-2">
                      <div class="text-xs text-slate-500 mb-0.5">Delivery Address</div>
                      <div class="text-sm font-medium text-slate-800 leading-snug">{{ selectedRequest.delivery_address || 'Main Warehouse' }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                Financial Breakdown
              </h3>
              <div class="border border-slate-200 rounded-xl overflow-hidden">
                <Table>
                  <TableHeader class="bg-slate-50/80">
                    <TableRow>
                      <TableHead class="text-slate-600 font-semibold">Item Description</TableHead>
                      <TableHead class="text-right text-slate-600 font-semibold">Unit Price</TableHead>
                      <TableHead class="text-right text-slate-600 font-semibold">Qty</TableHead>
                      <TableHead class="text-right text-slate-600 font-semibold">Line Total</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow>
                      <TableCell class="font-medium">
                        {{ selectedRequest.product_name }}
                        <div class="text-xs text-slate-500 mt-0.5 font-normal">{{ selectedRequest.category }}</div>
                      </TableCell>
                      <TableCell class="text-right">₱{{ formatCurrency(selectedRequest.unit_price) }}</TableCell>
                      <TableCell class="text-right">{{ selectedRequest.quantity }}</TableCell>
                      <TableCell class="text-right font-medium">₱{{ formatCurrency(selectedRequest.total_cost) }}</TableCell>
                    </TableRow>
                    <TableRow v-if="selectedRequest.instructions">
                      <TableCell colspan="4" class="bg-slate-50/50">
                        <div class="flex items-start text-xs text-slate-600">
                          <span class="font-semibold mr-2 mt-0.5">Note:</span>
                          <span class="italic">{{ selectedRequest.instructions }}</span>
                        </div>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
                <div class="bg-slate-800 text-white p-4 flex justify-between items-center">
                  <span class="font-medium text-sm text-slate-300">Total Approved Value</span>
                  <span class="text-xl font-bold">₱{{ formatCurrency(selectedRequest.total_cost) }}</span>
                </div>
              </div>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-800 mb-3">Finance Review Comments</h3>
              <Textarea 
                v-model="comments" 
                placeholder="Add justification or reasoning for approval/rejection (Optional for Approval, Required for Rejection)"
                class="resize-none min-h-[100px] border-slate-200 focus-visible:ring-indigo-500"
              />
            </div>
            
          </div>

          <div class="p-4 border-t border-slate-200 bg-slate-50 flex items-center justify-between mt-auto flex-none">
            <Button variant="outline" class="text-slate-600" @click="selectedRequest = null" :disabled="processing">
              Cancel Review
            </Button>
            <div class="space-x-3">
              <Button 
                variant="destructive" 
                class="bg-rose-600 hover:bg-rose-700 font-medium px-6" 
                @click="requirePermission('approve', rejectRequest)"
                :disabled="processing || !comments.trim()"
              >
                <template v-if="processing">Processing...</template>
                <template v-else>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                  Reject Request
                </template>
              </Button>
              <Button 
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-6" 
                @click="requirePermission('approve', approveRequest)"
                :disabled="processing"
              >
                <template v-if="processing">Processing...</template>
                <template v-else>
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  Approve Funds
                </template>
              </Button>
            </div>
          </div>
        </Card>

        <Card v-else class="shadow-sm border-slate-200 border-dashed h-full min-h-[500px] flex flex-col items-center justify-center bg-slate-50/50">
          <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-slate-700 mb-1">No Request Selected</h3>
          <p class="text-slate-500 text-sm max-w-sm text-center">Select a pending procurement request from the list to review details and process financial approval.</p>
        </Card>
      </div>
    </div>

    <div class="mt-8">
      <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        Recently Processed
      </h2>
      
      <div v-if="loading && recentlyProcessed.length === 0" class="flex space-x-4 overflow-x-auto pb-4 custom-scrollbar">
        <Skeleton v-for="i in 4" :key="i" class="h-24 w-72 flex-shrink-0 rounded-xl" />
      </div>
      
      <div v-else-if="recentlyProcessed.length > 0" class="flex space-x-4 overflow-x-auto pb-4 custom-scrollbar snap-x">
        <Card 
          v-for="item in recentlyProcessed" 
          :key="item.id"
          class="w-72 flex-shrink-0 p-4 border-slate-200 snap-start bg-white"
        >
          <div class="flex justify-between items-center mb-2">
            <span class="text-xs font-mono text-slate-500">{{ item.request_code }}</span>
            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full"
              :class="item.status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
            >
              {{ item.status }}
            </span>
          </div>
          <h4 class="text-sm font-medium text-slate-800 line-clamp-1 mb-2">{{ item.product_name }}</h4>
          <div class="flex justify-between items-end mt-auto pt-2 border-t border-slate-100">
            <span class="text-xs text-slate-500">{{ formatDate(item.finance_approved_at || item.finance_rejected_at) }}</span>
            <span class="text-sm font-bold text-slate-700">₱{{ formatCurrency(item.total_cost) }}</span>
          </div>
        </Card>
      </div>

      <div v-else class="text-sm text-slate-500 italic p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center">
        No recent finance processing activity recorded.
      </div>
    </div>
    
    <Toaster position="top-right" rich-colors />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '@/utils/axios.js';
import echo from '@/utils/websocket.js';
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Textarea } from '@/components/ui/textarea'
import { Separator } from '@/components/ui/separator'
import { Toaster } from '@/components/ui/sonner'
import { Skeleton } from '@/components/ui/skeleton'
import { Progress } from '@/components/ui/progress'
import { toast } from 'vue-sonner'

// Data Refs
const selectedRequestId = ref(null);
const selectedRequest = ref(null);
const comments = ref('');
const pendingRequests = ref([]);
const recentlyProcessed = ref([]);
const statistics = ref({
  approved: 0,
  rejected: 0,
  pending: 0,
  approved_amount: 0,
  rejected_amount: 0,
  pending_amount: 0,
  total_requests: 0
});

const loading = ref(false);
const processing = ref(false);
const currentMonth = ref(new Date().toLocaleString('default', { month: 'long', year: 'numeric' }));
const activeChannel = ref(null);

// Lazy Loading State
const detailsLoading = ref(false);
const page = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);

// Updated Level-Based Permissions
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
});

// RBAC Action Interceptor
const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied`, {
      description: `You do not have permission to ${action} finance requests.`
    });
    return;
  }
  if (callback) callback();
};

// Functions

// WebSocket Initialization
const setupWebsocket = (distributorId) => {
  if (activeChannel.value) return; 

  let channelName = distributorId === null ? 'admin.procurement' : `distributor.${distributorId}.procurement`;
  activeChannel.value = channelName;

  echo.private(channelName)
    .listen('.procurement.created', (e) => {
        toast.info('New Procurement Request', { description: 'A new request is pending for finance review!' });
        fetchData(false); // Refresh list
    })
    .listen('.procurement.updated', (e) => {
        fetchData(false); // Refresh list to keep multiple active finance sessions in sync
    });
};

const fetchData = async (append = false) => {
  if (append) {
      if (loadingMore.value || !hasMore.value) return;
      loadingMore.value = true;
  } else {
      loading.value = true;
      page.value = 1;
      hasMore.value = true;
  }

  try {
    const response = await api.get('/finance/procurement/requests', {
        params: { page: page.value }
    });
    
    if (response.data.success) {
      const newRequests = response.data.data.pending_requests.data || response.data.data.pending_requests;
      
      if (append) {
          pendingRequests.value = [...pendingRequests.value, ...newRequests];
      } else {
          pendingRequests.value = newRequests;
      }
      
      recentlyProcessed.value = response.data.data.recently_processed;
      statistics.value = response.data.data.statistics;
      statistics.value.total_requests = statistics.value.approved + statistics.value.rejected + statistics.value.pending;

      if (response.data.permissions) {
          permissions.value = response.data.permissions;
      }

      if (response.data.distributor_id !== undefined && !activeChannel.value) {
          setupWebsocket(response.data.distributor_id);
      }

      // Check if we have more pages (Simple check: if we got no data, assume end)
      if (newRequests.length === 0) {
          hasMore.value = false;
      }
    }
  } catch (error) {
    if (error.response?.status === 403) {
      toast.error('Unauthorized', { description: 'Access to finance procurement is restricted.' });
    } else {
      console.error('Error fetching procurement requests:', error);
      toast.error('Error', { description: 'Failed to load procurement requests.' });
    }
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
};

// "Click" to load more list items
const loadMore = () => {
    page.value++;
    fetchData(true);
};

const selectRequest = async (request) => {
  selectedRequestId.value = request.id;
  comments.value = '';
  detailsLoading.value = true;
  
  try {
    const response = await api.get(`/finance/procurement/requests/${request.id}`);
    if (response.data.success) {
      selectedRequest.value = response.data.data;
    } else {
      selectedRequest.value = request; // fallback to basic data
    }
  } catch (error) {
    console.error('Error fetching details:', error);
    toast.error('Error', { description: 'Failed to load complete request details.' });
    selectedRequest.value = request; // fallback to basic data
  } finally {
    detailsLoading.value = false;
  }
};

const approveRequest = async () => {
  if (!selectedRequest.value) return;
  
  processing.value = true;
  
  try {
    const response = await api.post(`/finance/procurement/requests/${selectedRequest.value.id}/approve`, {
      comments: comments.value
    });
    
    if (response.data.success) {
      toast.success('Funds Approved', { 
        description: `₱${formatCurrency(selectedRequest.value.total_cost)} has been allocated successfully.` 
      });
      selectedRequest.value = null;
      selectedRequestId.value = null;
      comments.value = '';
      
      // Refresh Data entirely
      await fetchData();
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to approve request.';
    toast.error('Approval Failed', { description: msg });
  } finally {
    processing.value = false;
  }
};

const rejectRequest = async () => {
  if (!selectedRequest.value) return;
  
  if (!comments.value.trim()) {
    toast.error('Comment Required', { description: 'Please provide a reason for rejecting the funds.' });
    return;
  }
  
  processing.value = true;
  
  try {
    const response = await api.post(`/finance/procurement/requests/${selectedRequest.value.id}/reject`, {
      reason: comments.value // The API expects 'reason' based on controller logic
    });
    
    if (response.data.success) {
      toast.success('Request Rejected', { 
        description: `The operations team will be notified of the rejection.` 
      });
      selectedRequest.value = null;
      selectedRequestId.value = null;
      comments.value = '';
      
      // Refresh Data entirely
      await fetchData();
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to reject request.';
    if (error.response?.status === 422) {
       toast.error('Validation Error', { description: 'Please check your rejection reason length.' });
    } else {
       toast.error('Rejection Failed', { description: msg });
    }
  } finally {
    processing.value = false;
  }
};

const refreshData = async () => {
  await fetchData();
  toast.success('Success', { description: 'Data refreshed successfully.' });
};

const getCategoryClass = (category) => {
  const classes = {
    'Hardware': 'bg-amber-100 text-amber-800 hover:bg-amber-100 border-amber-200',
    'Software': 'bg-emerald-100 text-emerald-800 hover:bg-emerald-100 border-emerald-200',
    'Office Supplies': 'bg-purple-100 text-purple-800 hover:bg-purple-100 border-purple-200',
    'Interior Paints': 'bg-blue-100 text-blue-800 hover:bg-blue-100 border-blue-200',
    'Exterior Paints': 'bg-red-100 text-red-800 hover:bg-red-100 border-red-200',
    'Specialty Paints': 'bg-pink-100 text-pink-800 hover:bg-pink-100 border-pink-200'
  };
  return classes[category] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  });
};

const formatCurrency = (amount) => {
  if (!amount) return '0.00';
  return parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
};

const getInitials = (name) => {
  if (!name) return '?';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const getPercentage = (value, total) => {
  if (!total || total === 0) return 0;
  return Number(((value / total) * 100).toFixed(1));
};

// Lifecycle Hooks
onMounted(() => {
  fetchData();
});

onUnmounted(() => {
  if (activeChannel.value) {
    echo.leave(activeChannel.value);
  }
});
</script>

<style scoped>
/* Custom subtle scrollbar for the long lists */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background-color: #94a3b8;
}
</style>