<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <header class="mb-8">
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <div class="p-2 bg-indigo-100 rounded-lg mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Finance Approval Portal</h1>
        </div>
        <div class="text-sm bg-gray-100 px-3 py-1 rounded-full flex items-center">
          <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
          <span class="text-gray-700">Finance Department</span>
        </div>
      </div>
      <p class="text-gray-600">Review and approve or reject procurement requests from various departments.</p>
    </header>

    <div v-if="loading && !pendingRequests.length" class="text-center py-8 space-y-4">
       <div class="flex items-center justify-center space-x-4">
          <Skeleton class="h-12 w-12 rounded-full" />
          <div class="space-y-2">
            <Skeleton class="h-4 w-[250px]" />
            <Skeleton class="h-4 w-[200px]" />
          </div>
        </div>
      <p class="mt-2 text-gray-600">Loading procurement requests...</p>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <Card class="overflow-hidden flex flex-col max-h-[800px]">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white z-10">
            <div>
              <h2 class="text-lg font-semibold text-gray-800">Pending Requests</h2>
              <p class="text-sm text-gray-500 mt-1">Click on a request to review details</p>
            </div>
            <Button variant="outline" size="sm" @click="refreshData" class="flex items-center">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
              Refresh
            </Button>
          </div>
          
          <div class="overflow-y-auto flex-1 relative">
            <div v-if="pendingRequests.length > 0" class="divide-y divide-gray-100">
              <div 
                v-for="request in pendingRequests" 
                :key="request.id"
                @click="selectRequest(request.id)"
                :class="['p-4 hover:bg-gray-50 cursor-pointer transition-colors', selectedRequestId === request.id ? 'bg-blue-50' : '']"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <div class="flex items-center mb-2 gap-2">
                      <Badge variant="secondary" class="bg-blue-100 text-blue-800 hover:bg-blue-100">{{ request.request_code }}</Badge>
                      <Badge variant="outline" :class="getCategoryClass(request.category)">{{ request.category }}</Badge>
                    </div>
                    <h3 class="font-medium text-gray-900 mb-1">{{ request.product_name }}</h3>
                    <p class="text-sm text-gray-600 mb-2">Submitted by {{ request.requester?.full_name }} • {{ request.department || 'No department' }}</p>
                    <div class="flex items-center text-sm text-gray-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="mr-4">Submitted: {{ formatDate(request.request_date) }}</span>
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <span>₱{{ formatCurrency(request.total_cost) }}</span>
                    </div>
                  </div>
                  <div class="text-right flex flex-col items-end gap-2">
                    <div class="flex items-center justify-end">
                      <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-2">
                        <span class="text-xs font-medium text-blue-800">{{ getInitials(request.requester?.full_name) }}</span>
                      </div>
                      <span class="text-sm text-gray-600">{{ request.requester?.full_name }}</span>
                    </div>
                    <Badge variant="secondary" class="bg-yellow-100 text-yellow-800 hover:bg-yellow-100 flex w-fit items-center gap-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {{ request.formatted_status }}
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="p-8 text-center">
              <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No Pending Requests</h3>
              <p class="text-gray-500">All procurement requests have been processed.</p>
            </div>

            <div v-if="pendingRequests.length > 0" class="p-4 flex justify-center items-center border-t border-gray-100 bg-gray-50">
                <Button 
                  v-if="hasMore" 
                  variant="outline" 
                  @click="loadMore" 
                  :disabled="loadingMore"
                  class="w-full text-indigo-600 border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                >
                  <span v-if="!loadingMore">Load More Requests</span>
                  <div v-else class="flex items-center gap-2">
                     <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Loading...
                  </div>
                </Button>
                <p v-else class="text-xs text-gray-400 italic">No more requests to load</p>
            </div>
          </div>
        </Card>

        <Card class="overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Recently Processed</h2>
            <p class="text-sm text-gray-500 mt-1">Requests approved or rejected in the last 7 days</p>
          </div>
          <div class="p-4">
            <div v-if="recentlyProcessed.length > 0" class="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Request ID</TableHead>
                    <TableHead>Item</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Processed Date</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="request in recentlyProcessed" :key="request.id">
                    <TableCell class="font-medium">{{ request.request_code }}</TableCell>
                    <TableCell>{{ request.product_name }}</TableCell>
                    <TableCell>₱{{ formatCurrency(request.total_cost) }}</TableCell>
                    <TableCell>
                      <Badge v-if="request.status === 'approved'" class="bg-green-100 text-green-800 hover:bg-green-100 border-0">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Approved
                      </Badge>
                       <Badge v-else class="bg-red-100 text-red-800 hover:bg-red-100 border-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Rejected
                      </Badge>
                    </TableCell>
                    <TableCell>{{ formatDate(request.updated_at) }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            <div v-else class="p-4 text-center text-gray-500">
              No recently processed requests.
            </div>
          </div>
        </Card>
      </div>

      <div class="space-y-6">
        <Card v-if="detailsLoading" class="overflow-hidden h-fit">
           <div class="px-6 py-4 border-b border-gray-200">
            <Skeleton class="h-6 w-1/3" />
            <Skeleton class="h-4 w-1/2 mt-2" />
          </div>
          <div class="p-6 space-y-6">
            <div class="pb-4 border-b border-gray-200 space-y-4">
              <Skeleton class="h-8 w-2/3" />
              <div class="flex gap-2">
                 <Skeleton class="h-6 w-20 rounded-full" />
                 <Skeleton class="h-6 w-24 rounded-full" />
              </div>
              <div class="grid grid-cols-2 gap-4 pt-2">
                <div class="space-y-2" v-for="i in 4" :key="i">
                  <Skeleton class="h-4 w-20" />
                  <Skeleton class="h-5 w-32" />
                </div>
              </div>
            </div>
            <div class="space-y-2">
               <Skeleton class="h-5 w-40" />
               <Skeleton class="h-16 w-full" />
            </div>
            <div class="space-y-2">
               <Skeleton class="h-5 w-40" />
               <Skeleton class="h-12 w-full" />
            </div>
            <div class="pt-6 border-t border-gray-200 space-y-4">
              <Skeleton class="h-5 w-32" />
              <Skeleton class="h-20 w-full" />
              <div class="flex gap-4">
                <Skeleton class="h-10 w-full" />
                <Skeleton class="h-10 w-full" />
              </div>
            </div>
          </div>
        </Card>

        <Card v-else-if="selectedRequest && selectedRequestId" class="overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Request Details</h2>
            <p class="text-sm text-gray-500 mt-1">Review request information before making a decision</p>
          </div>
          
          <div class="p-6 space-y-6">
            <div class="pb-4 border-b border-gray-200">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <h3 class="text-xl font-bold text-gray-900 mb-2">{{ selectedRequest.product_name }}</h3>
                  <div class="flex items-center gap-2 mb-3">
                     <Badge variant="secondary" class="bg-blue-100 text-blue-800 hover:bg-blue-100">{{ selectedRequest.request_code }}</Badge>
                     <Badge variant="outline" :class="getCategoryClass(selectedRequest.category)">{{ selectedRequest.category }}</Badge>
                  </div>
                </div>
                 <Badge variant="secondary" class="bg-yellow-100 text-yellow-800 hover:bg-yellow-100 flex items-center gap-1">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ selectedRequest.formatted_status }}
                </Badge>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-sm text-gray-500">Submitted By</p>
                  <p class="font-medium">{{ selectedRequest.requester?.full_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Department</p>
                  <p class="font-medium">{{ selectedRequest.department || 'N/A' }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Submission Date</p>
                  <p class="font-medium">{{ formatDate(selectedRequest.request_date) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Total Amount</p>
                  <p class="font-medium text-lg text-gray-900">₱{{ formatCurrency(selectedRequest.total_cost) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Supplier</p>
                  <p class="font-medium">{{ selectedRequest.supplier }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Priority</p>
                  <p class="font-medium capitalize">{{ selectedRequest.priority }}</p>
                </div>
              </div>
            </div>

            <div v-if="selectedRequest.instructions">
              <h4 class="font-medium text-gray-900 mb-2">Instructions/Special Notes</h4>
              <p class="text-gray-700 text-sm">{{ selectedRequest.instructions }}</p>
            </div>

            <div>
              <h4 class="font-medium text-gray-900 mb-3">Items Requested</h4>
              <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="font-medium">{{ selectedRequest.product_name }}</p>
                    <p class="text-sm text-gray-600">Quantity: {{ selectedRequest.quantity }} • Unit Price: ₱{{ formatCurrency(selectedRequest.unit_price) }}</p>
                  </div>
                  <p class="font-medium">₱{{ formatCurrency(selectedRequest.total_cost) }}</p>
                </div>
              </div>
            </div>

            <div>
              <h4 class="font-medium text-gray-900 mb-3">Budget Information</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                  <p class="text-sm text-gray-500 mb-1">Department Budget</p>
                  <p class="text-lg font-medium text-gray-900">₱{{ formatCurrency(budgetInfo.department_budget) }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                  <p class="text-sm text-gray-500 mb-1">Remaining Balance</p>
                  <p class="text-lg font-medium text-gray-900">₱{{ formatCurrency(budgetInfo.remaining_balance) }}</p>
                </div>
              </div>
              <div v-if="budgetInfo.can_afford === false" class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md flex items-start gap-2">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                <p class="text-sm text-red-600 font-medium">
                  This request exceeds the available budget.
                </p>
              </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
              <h4 class="font-medium text-gray-900 mb-4">Finance Decision</h4>
              <div class="space-y-4">
                <div class="space-y-2">
                  <label for="comments" class="text-sm font-medium text-gray-700">Comments (Optional)</label>
                  <Textarea 
                    id="comments" 
                    v-model="comments"
                    placeholder="Add any comments regarding your decision..."
                    class="resize-none"
                    rows="3"
                  />
                </div>
                
                <div class="flex space-x-4">
                  <Button 
                    @click="approveRequest"
                    :disabled="processing"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white"
                  >
                    <svg v-if="!processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                     <svg v-else class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    {{ processing ? 'Processing...' : 'Approve Request' }}
                  </Button>
                  <Button 
                    @click="rejectRequest"
                    :disabled="processing"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white"
                  >
                    <svg v-if="!processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                     <svg v-else class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                    {{ processing ? 'Processing...' : 'Reject Request' }}
                  </Button>
                </div>
                
                <div class="text-center">
                  <Button 
                    variant="link"
                    @click="selectRequest(null)"
                    class="text-gray-600 hover:text-gray-900"
                  >
                    Cancel and return to list
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <Card v-else class="overflow-hidden h-fit">
          <div class="p-8 text-center">
            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Request Selected</h3>
            <p class="text-gray-500 mb-6">Select a request from the list to review details and make a decision.</p>
            <div class="text-sm text-gray-500 flex flex-col items-center">
              <p class="mb-2">Use the buttons below to:</p>
              <ul class="text-left inline-block space-y-1">
                <li class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Approve requests that meet budget requirements
                </li>
                <li class="flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                  Reject requests that don't comply with policies
                </li>
              </ul>
            </div>
          </div>
        </Card>

        <Card class="overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-800">Monthly Summary</h2>
            <p class="text-sm text-gray-500 mt-1">{{ currentMonth }}</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div>
                <div class="flex justify-between mb-1">
                  <span class="text-sm text-gray-600">Approved</span>
                  <span class="text-sm font-medium text-gray-900">{{ statistics.approved }} requests</span>
                </div>
                 <Progress :model-value="Number(getPercentage(statistics.approved, statistics.total_requests))" class="h-2 [&>div]:bg-green-600 bg-gray-200" />
              </div>
              <div>
                <div class="flex justify-between mb-1">
                  <span class="text-sm text-gray-600">Rejected</span>
                  <span class="text-sm font-medium text-gray-900">{{ statistics.rejected }} requests</span>
                </div>
                 <Progress :model-value="Number(getPercentage(statistics.rejected, statistics.total_requests))" class="h-2 [&>div]:bg-red-600 bg-gray-200" />
              </div>
              <div>
                <div class="flex justify-between mb-1">
                  <span class="text-sm text-gray-600">Pending</span>
                  <span class="text-sm font-medium text-gray-900">{{ statistics.pending }} requests</span>
                </div>
                 <Progress :model-value="Number(getPercentage(statistics.pending, statistics.total_requests))" class="h-2 [&>div]:bg-yellow-500 bg-gray-200" />
              </div>
            </div>
            
            <Separator class="my-6" />

            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <p class="text-2xl font-bold text-gray-900">₱{{ formatCurrency(statistics.approved_amount) }}</p>
                <p class="text-xs text-gray-500">Approved Amount</p>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">₱{{ formatCurrency(statistics.rejected_amount) }}</p>
                <p class="text-xs text-gray-500">Rejected Amount</p>
              </div>
              <div>
                <p class="text-2xl font-bold text-gray-900">₱{{ formatCurrency(statistics.pending_amount) }}</p>
                <p class="text-xs text-gray-500">Pending Amount</p>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
     <Toaster />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/utils/axios.js'; // Adjust the path to your axios.js file
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
const budgetInfo = ref({
  department_budget: 0,
  remaining_balance: 0,
  budget_utilization: '0%',
  can_afford: true
});
const loading = ref(false);
const processing = ref(false);
const currentMonth = ref(new Date().toLocaleString('default', { month: 'long', year: 'numeric' }));

// Lazy Loading State
const detailsLoading = ref(false);
const page = ref(1);
const hasMore = ref(true);
const loadingMore = ref(false);

// Functions
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

      // Check if we have more pages (Simple check: if we got no data, assume end)
      if (newRequests.length === 0) {
          hasMore.value = false;
      }
    }
  } catch (error) {
    console.error('Error fetching procurement requests:', error);
    toast.error('Error', { description: 'Failed to load procurement requests.' });
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

const selectRequest = async (id) => {
  if (!id) {
    selectedRequestId.value = null;
    selectedRequest.value = null;
    return;
  }
  
  // Activate Visual Lazy Loading for Details Pane
  detailsLoading.value = true;
  // Ensure the old data is cleared so the Skeleton shows
  selectedRequest.value = null; 
  
  try {
    const response = await api.get(`/finance/procurement/requests/${id}`);
    
    if (response.data.success) {
      selectedRequestId.value = id;
      selectedRequest.value = response.data.data.request;
      budgetInfo.value = response.data.data.budget_info;
    }
  } catch (error) {
    console.error('Error fetching request details:', error);
    toast.error('Error', { description: 'Failed to load request details.' });
  } finally {
    detailsLoading.value = false;
  }
};

const approveRequest = async () => {
  if (!selectedRequestId.value) return;
  
  processing.value = true;
  try {
    const response = await api.post(`/finance/procurement/requests/${selectedRequestId.value}/approve`, {
      comments: comments.value
    });
    
    if (response.data.success) {
      toast.success('Request Approved', {
        description: `Request ${selectedRequest.value.request_code} has been approved successfully.`
      });
      
      // Refresh data and clear selection
      fetchData();
      selectRequest(null);
      comments.value = '';
    }
  } catch (error) {
    console.error('Error approving request:', error);
     toast.error('Error', { description: 'Failed to approve request.' });
  } finally {
    processing.value = false;
  }
};

const rejectRequest = async () => {
  if (!selectedRequestId.value) return;
  
  if (!comments.value.trim()) {
    toast.warning('Warning', { description: 'Please provide a reason for rejection.' });
    return;
  }
  
  processing.value = true;
  try {
    const response = await api.post(`/finance/procurement/requests/${selectedRequestId.value}/reject`, {
      comments: comments.value
    });
    
    if (response.data.success) {
      toast.error('Request Rejected', {
          description: `Request ${selectedRequest.value.request_code} has been rejected.`
      });
      
      // Refresh data and clear selection
      fetchData();
      selectRequest(null);
      comments.value = '';
    }
  } catch (error) {
    console.error('Error rejecting request:', error);
    toast.error('Error', { description: 'Failed to reject request.' });
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
  return ((value / total) * 100).toFixed(1);
};

// Lifecycle Hooks
onMounted(() => {
  fetchData();
});
</script>