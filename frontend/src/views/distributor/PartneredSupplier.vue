<template>
  <div class="flex flex-col gap-6 p-4 sm:p-8 min-h-screen">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">My Suppliers</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage your active supply chain network and monitor performance.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white gap-2 shadow-sm" @click="refreshData">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4" />
          Refresh
        </Button>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
      <div class="relative w-full lg:w-96">
        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
        <Input 
          v-model="searchQuery" 
          placeholder="Search by company, category..." 
          class="pl-9 border-slate-200 focus-visible:ring-indigo-500 w-full"
        />
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-2 w-full lg:w-auto">
        <Select v-model="statusFilter">
          <SelectTrigger class="w-full sm:w-[190px]">
            <SelectValue placeholder="Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Statuses</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="pending_supplier">Pending Supplier</SelectItem>
            <SelectItem value="pending_reactivation">Pending Reactivation</SelectItem>
            <SelectItem value="pending_termination">Pending Termination</SelectItem>
            <SelectItem value="suspended">Suspended</SelectItem>
            <SelectItem value="terminated">Terminated</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card v-for="i in 3" :key="i" class="h-64 animate-pulse bg-slate-100 border-slate-200">
        <CardContent></CardContent>
      </Card>
    </div>

    <div v-else-if="filteredSuppliers.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card 
        v-for="supplier in filteredSuppliers" 
        :key="supplier.id" 
        class="group flex flex-col hover:shadow-md transition-all duration-300 border-slate-200"
      >
        <CardContent class="p-6 flex-grow">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div 
                class="h-14 w-14 rounded-xl flex items-center justify-center text-lg font-bold shadow-sm"
                :class="getAvatarColor(supplier.company_name)"
              >
                {{ getInitials(supplier.company_name) }}
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight group-hover:text-indigo-600 transition-colors">
                  {{ supplier.company_name }}
                </h3>
                <span class="text-xs text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-full mt-1 inline-block">
                  {{ supplier.category }}
                </span>
              </div>
            </div>
            
            <Badge 
              :variant="['active', 'pending_reactivation'].includes(supplier.status) ? 'outline' : 'secondary'"
              class="capitalize"
              :class="getStatusColor(supplier.status)"
            >
              {{ 
                supplier.status === 'pending_supplier' ? 'Waiting on Supplier' : 
                (supplier.status === 'pending_termination' ? 'Terminating' : 
                (supplier.status === 'pending_reactivation' ? 'Pending Reactivation' : supplier.status)) 
              }}
            </Badge>
          </div>

          <div class="grid grid-cols-2 gap-4 py-4 border-t border-b border-slate-50 mb-4">
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Performance</span>
              <div class="flex items-center gap-2">
                <div class="flex items-center text-amber-500">
                  <Star class="h-3.5 w-3.5 fill-current" />
                  <span class="text-sm font-bold ml-1 text-slate-700">{{ supplier.rating }}</span>
                </div>
                <span class="text-xs text-slate-400">/ 5.0</span>
              </div>
            </div>
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Fulfillment</span>
              <div class="flex items-center gap-2">
                <Progress :model-value="supplier.fulfillment_rate" class="h-1.5 w-16" />
                <span class="text-xs font-bold text-slate-700">{{ supplier.fulfillment_rate }}%</span>
              </div>
            </div>
          </div>

          <div class="space-y-2.5">
            <div class="flex items-start gap-3">
              <MapPin class="h-4 w-4 text-slate-400 mt-0.5 shrink-0" />
              <span class="text-sm text-slate-600 leading-tight line-clamp-1" :title="supplier.location">
                {{ supplier.location }}
              </span>
            </div>
            <div class="flex items-center gap-3">
              <User class="h-4 w-4 text-slate-400 shrink-0" />
              <span class="text-sm text-slate-600">{{ supplier.contact_person }}</span>
            </div>
            <div class="flex items-center gap-3">
              <Phone class="h-4 w-4 text-slate-400 shrink-0" />
              <span class="text-sm text-slate-600">{{ supplier.phone }}</span>
            </div>
          </div>
        </CardContent>

        <CardFooter class="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-3">
          <Button 
            variant="outline" 
            class="flex-1 border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50" 
            @click="openDetails(supplier)"
          >
            View Details
          </Button>
          
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9 text-slate-400 hover:text-slate-700">
                <MoreHorizontal class="h-5 w-5" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
              <DropdownMenuItem>
                <Mail class="mr-2 h-4 w-4" /> Message
              </DropdownMenuItem>
              <DropdownMenuItem @click="viewAgreement(supplier)">
                <FileText class="mr-2 h-4 w-4" /> View Contracts
              </DropdownMenuItem>
              
              <DropdownMenuSeparator />

              <DropdownMenuItem 
                v-if="['pending_termination', 'terminated', 'disconnected'].includes(supplier.status)"
                @click="viewTerminationDocument(supplier)"
                class="text-red-600 focus:text-red-600 focus:bg-red-50"
              >
                <FileText class="mr-2 h-4 w-4" /> View Termination Doc
              </DropdownMenuItem>

              <DropdownMenuItem 
                v-if="['terminated', 'disconnected'].includes(supplier.status)"
                @click="initiateReactivation(supplier)"
                class="text-blue-600 focus:text-blue-600 focus:bg-blue-50"
              >
                <RefreshCcw class="mr-2 h-4 w-4" /> Reactivate Partnership
              </DropdownMenuItem>

              <DropdownMenuItem 
                v-if="!['pending_termination', 'terminated', 'disconnected', 'pending_reactivation'].includes(supplier.status)"
                class="text-red-600 focus:text-red-600 focus:bg-red-50" 
                @click="confirmTerminationAction(supplier)"
              >
                <Ban class="mr-2 h-4 w-4" /> Terminate
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </CardFooter>
      </Card>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-16 sm:py-24 text-center bg-white rounded-xl border border-dashed border-slate-300 mx-4 sm:mx-0">
      <div class="bg-slate-50 p-4 rounded-full mb-4">
        <Store class="w-10 h-10 text-slate-400" />
      </div>
      <h3 class="text-lg font-medium text-slate-900">No suppliers found</h3>
      <p class="text-slate-500 max-w-sm mt-2 px-4">
        You don't have any active partnerships matching the criteria. Use the "Link New Supplier" button to find partners.
      </p>
      <Button variant="outline" class="mt-6" @click="resetFilters">
        Clear Filters
      </Button>
    </div>

    <Sheet :open="isSheetOpen" @update:open="isSheetOpen = $event">
      <SheetContent class="w-full sm:max-w-[540px] overflow-y-auto p-0 gap-0 border-l border-slate-200 [&>button]:hidden">
        
        <div class="bg-slate-900 text-white p-6 relative">
           <Button 
            variant="ghost" 
            size="icon" 
            class="absolute right-4 top-4 z-50 text-slate-400 hover:text-white hover:bg-white/10 rounded-full"
            @click="isSheetOpen = false"
           >
             <X class="h-5 w-5" />
           </Button>
           
           <div class="flex items-center gap-4 mt-2">
              <div 
                class="h-16 w-16 rounded-xl flex items-center justify-center text-xl font-bold bg-white/10 text-white backdrop-blur-sm shadow-inner"
              >
                {{ selectedSupplier ? getInitials(selectedSupplier.company_name) : '' }}
              </div>
              <div class="pr-8">
                <h2 class="text-xl font-bold tracking-tight leading-snug">{{ selectedSupplier?.company_name }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-2 text-slate-300 text-sm">
                  <Badge variant="outline" class="text-xs border-slate-600 text-slate-300 font-normal">
                    {{ selectedSupplier?.category }}
                  </Badge>
                  <span class="text-slate-500">•</span>
                  <span class="text-xs font-mono opacity-80">Reg ID: {{ selectedSupplier?.id_number }}</span>
                </div>
              </div>
           </div>
        </div>

        <div class="p-6 space-y-8 bg-white min-h-full" v-if="selectedSupplier">
          
          <div class="grid grid-cols-2 gap-3">
            <Button class="bg-indigo-600 hover:bg-indigo-700 text-white border-0 shadow-sm w-full">
              <MessageSquare class="mr-2 h-4 w-4" /> Message
            </Button>
            <Button variant="outline" class="bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-sm w-full">
              <FileClock class="mr-2 h-4 w-4" /> History
            </Button>
          </div>

          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Performance Metrics</h4>
            <div class="grid grid-cols-3 gap-3">
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-slate-900">{{ selectedSupplier.total_orders }}</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">Orders</div>
              </div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-slate-900">₱{{ selectedSupplier.total_spend }}</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">Spend</div>
              </div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-emerald-600">{{ selectedSupplier.on_time_rate }}%</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">On-Time</div>
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact Information</h4>
            <div class="bg-white border border-slate-200 rounded-lg divide-y divide-slate-100 shadow-sm">
              <div class="p-3.5 flex justify-between items-center">
                <div class="flex items-center gap-3 text-sm text-slate-500">
                  <User class="h-4 w-4 shrink-0" />
                  <span>Representative</span>
                </div>
                <span class="text-sm font-medium text-slate-900 text-right">{{ selectedSupplier.contact_person }}</span>
              </div>
              <div class="p-3.5 flex justify-between items-center">
                <div class="flex items-center gap-3 text-sm text-slate-500">
                  <Mail class="h-4 w-4 shrink-0" />
                  <span>Email</span>
                </div>
                <span class="text-sm font-medium text-slate-900 text-right truncate max-w-[180px]" :title="selectedSupplier.email">{{ selectedSupplier.email }}</span>
              </div>
              <div class="p-3.5 flex justify-between items-center">
                <div class="flex items-center gap-3 text-sm text-slate-500">
                  <Phone class="h-4 w-4 shrink-0" />
                  <span>Mobile</span>
                </div>
                <span class="text-sm font-medium text-slate-900 text-right">{{ selectedSupplier.phone }}</span>
              </div>
              <div class="p-3.5">
                <div class="flex items-center gap-3 text-sm text-slate-500 mb-1.5">
                  <MapPin class="h-4 w-4 shrink-0" />
                  <span>Address</span>
                </div>
                <p class="text-sm font-medium text-slate-900 pl-7 leading-relaxed">{{ selectedSupplier.address }}</p>
              </div>
            </div>
          </div>

          <div>
             <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Agreements</h4>
             <div 
               @click="viewAgreement(selectedSupplier)"
               class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors cursor-pointer group bg-white shadow-sm"
              >
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-indigo-50 rounded text-indigo-600 group-hover:bg-indigo-100 transition-colors">
                    <FileBadge class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-slate-900">Partnership Agreement</p>
                    <p class="text-xs mt-0.5">
                      <span v-if="selectedSupplier.is_signed" class="text-emerald-600 font-semibold flex items-center gap-1">
                        <CheckCircle class="h-3 w-3" /> Document Signed
                      </span>
                      <span v-else class="text-amber-500 font-semibold flex items-center gap-1">
                        <AlertCircle class="h-3 w-3" /> Needs Signature
                      </span>
                    </p>
                  </div>
                </div>
                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-full">
                  <Eye class="h-4 w-4 text-slate-400 group-hover:text-indigo-600" />
                </Button>
             </div>
           </div>

        </div>
      </SheetContent>
    </Sheet>

    <AlertDialog :open="showConfirmTerminateDialog" @update:open="showConfirmTerminateDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-600 flex items-center gap-2">
            <AlertCircle class="h-5 w-5" /> Confirm Termination Request
          </AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to initiate the termination process with <strong>{{ selectedSupplier?.company_name }}</strong>?
            <br><br>
            The system will first verify that all transactions and orders are completed. If clear, an official termination document will be generated for your signature to officially sever the partnership.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isCheckingTermination">Cancel</AlertDialogCancel>
          <Button variant="destructive" @click="proceedWithTermination" :disabled="isCheckingTermination">
            <Loader2 v-if="isCheckingTermination" class="mr-2 h-4 w-4 animate-spin" />
            {{ isCheckingTermination ? 'Verifying...' : 'Proceed' }}
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showAgreementDialog" @update:open="showAgreementDialog = $event">
      <DialogContent class="w-full h-[100dvh] max-w-none sm:max-w-5xl sm:h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl rounded-none border-0 sm:border gap-0">
        <DialogHeader class="p-3 sm:p-4 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex flex-col sm:flex-row sm:items-center justify-between w-full pr-6 gap-3">
            <span class="flex items-center gap-2 text-base sm:text-lg">
                <FileText class="h-4 w-4 sm:h-5 sm:w-5 text-indigo-600 shrink-0" />
                <span class="truncate">Partnership Agreement - {{ selectedSupplier?.company_name }}</span>
            </span>
            <Button v-if="selectedSupplier?.is_signed" variant="outline" size="sm" @click="downloadDocument" class="w-full sm:w-auto text-indigo-600 hover:text-indigo-700 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 shrink-0">
                <Download class="h-4 w-4 mr-2" /> Download Document
            </Button>
          </DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-2 sm:p-4 relative bg-slate-100">
            <iframe 
              ref="printIframe"
              v-if="selectedSupplier?.agreement_url" 
              :src="selectedSupplier.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
            <div v-else class="flex flex-col h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>No digital agreement document was found.</p>
            </div>
        </div>

        <div v-if="selectedSupplier?.agreement_url && !selectedSupplier?.is_signed" class="p-3 sm:p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] overflow-y-auto max-h-[40vh] sm:max-h-none">
            <div class="flex flex-col gap-3 sm:gap-4 max-w-4xl mx-auto">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-3 w-3 sm:h-4 sm:w-4 text-indigo-600" />
                            Draw Your Signature
                        </label>
                        <Button variant="ghost" size="sm" class="h-6 sm:h-7 text-xs text-slate-500 hover:text-red-600 px-2" @click="clearSignature">
                            Clear Pad
                        </Button>
                    </div>
                    
                    <div class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 overflow-hidden relative" ref="canvasContainer">
                        <canvas 
                            ref="signatureCanvas" 
                            class="w-full h-[160px] sm:h-[200px] cursor-crosshair touch-none"
                            @mousedown="startDrawing"
                            @mousemove="draw"
                            @mouseup="stopDrawing"
                            @mouseleave="stopDrawing"
                            @touchstart.prevent="startDrawingTouch"
                            @touchmove.prevent="drawTouch"
                            @touchend.prevent="stopDrawing"
                        ></canvas>
                        
                        <div v-if="!hasDrawn" class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-40">
                            <span class="text-slate-400 text-sm sm:text-base font-medium select-none">Sign Here</span>
                        </div>
                    </div>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-2 text-center">
                        By signing and submitting, you legally bind your business to the agreement.
                    </p>
                </div>

                <div class="flex justify-end gap-2 sm:gap-3 pt-1">
                    <Button variant="outline" class="flex-1 sm:flex-none" @click="showAgreementDialog = false">Cancel</Button>
                    <Button 
                      :disabled="!hasDrawn || signing" 
                      @click="submitSignature" 
                      class="flex-1 sm:flex-none bg-indigo-600 text-white hover:bg-indigo-700 min-w-[140px]"
                    >
                        <Loader2 v-if="signing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ signing ? 'Applying...' : 'Sign & Submit' }}
                    </Button>
                </div>
            </div>
        </div>
        
        <div v-else-if="selectedSupplier?.is_signed" class="p-4 sm:p-6 border-t bg-white shrink-0 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)] z-10 overflow-y-auto max-h-[40vh] sm:max-h-none">
            <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-3 sm:mb-4">Official Signatures</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 max-w-4xl mx-auto">
               <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 border border-indigo-200">Distributor</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedSupplier?.distributor_signature_url" :src="selectedSupplier.distributor_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else class="text-xs sm:text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800">Your Business</p>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle class="h-3 w-3 text-emerald-500" /> {{ selectedSupplier?.signed_at || 'N/A' }}
                    </p>
                </div>

                <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-blue-100 text-blue-700 border border-blue-200">Supplier</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedSupplier?.supplier_signature_url" :src="selectedSupplier.supplier_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else-if="selectedSupplier?.status === 'pending_supplier'" class="text-xs sm:text-sm text-amber-500 italic flex items-center gap-1"><Clock class="h-3 w-3"/> Awaiting...</span>
                        <span v-else-if="selectedSupplier?.status === 'pending_reactivation'" class="text-xs sm:text-sm text-purple-500 italic flex items-center gap-1"><Clock class="h-3 w-3"/> Reactivation Pending...</span>
                        <span v-else class="text-xs sm:text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800 truncate max-w-[150px]">{{ selectedSupplier?.company_name }}</p>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle v-if="selectedSupplier?.supplier_signed_at" class="h-3 w-3 text-emerald-500" /> 
                        <Clock v-else class="h-3 w-3 text-amber-500" />
                        {{ selectedSupplier?.supplier_signed_at ? selectedSupplier.supplier_signed_at : 'Pending...' }}
                    </p>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showReactivateDialog" @update:open="showReactivateDialog = $event">
      <DialogContent class="w-full h-[100dvh] max-w-none sm:max-w-5xl sm:h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl rounded-none border-0 sm:border gap-0">
        <DialogHeader class="p-3 sm:p-4 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex items-center gap-2 text-base sm:text-lg text-indigo-600">
            <RefreshCcw class="h-4 w-4 sm:h-5 sm:w-5 shrink-0" />
            <span class="truncate">Reactivate Partnership - {{ selectedSupplier?.company_name }}</span>
          </DialogTitle>
          <DialogDescription class="text-xs sm:text-sm mt-1 text-slate-500">
             To reactivate this previously terminated partnership, please sign the original agreement below to confirm acceptance of the terms.
          </DialogDescription>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-2 sm:p-4 relative bg-slate-100">
            <iframe 
              v-if="selectedSupplier?.agreement_url" 
              :src="selectedSupplier.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
            <div v-else class="flex flex-col h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>No original agreement document was found to reactivate.</p>
            </div>
        </div>

        <div v-if="selectedSupplier?.agreement_url" class="p-3 sm:p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] overflow-y-auto max-h-[40vh] sm:max-h-none">
            <div class="flex flex-col gap-3 sm:gap-4 max-w-4xl mx-auto">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-3 w-3 sm:h-4 sm:w-4 text-indigo-600" />
                            Sign to Initiate Reactivation
                        </label>
                        <Button variant="ghost" size="sm" class="h-6 sm:h-7 text-xs text-slate-500 hover:text-red-600 px-2" @click="clearReactivateSignature">
                            Clear Pad
                        </Button>
                    </div>
                    
                    <div class="border-2 border-dashed border-indigo-200 rounded-lg bg-slate-50 overflow-hidden relative" ref="reactivateCanvasContainer">
                        <canvas 
                            ref="reactivateSignatureCanvas" 
                            class="w-full h-[160px] sm:h-[200px] cursor-crosshair touch-none"
                            @mousedown="startDrawingReactivate"
                            @mousemove="drawReactivate"
                            @mouseup="stopDrawingReactivate"
                            @mouseleave="stopDrawingReactivate"
                            @touchstart.prevent="startDrawingReactivateTouch"
                            @touchmove.prevent="drawReactivateTouch"
                            @touchend.prevent="stopDrawingReactivate"
                        ></canvas>
                        
                        <div v-if="!hasDrawnReactivate" class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-40">
                            <span class="text-indigo-400 text-sm sm:text-base font-medium select-none">Distributor Sign Here</span>
                        </div>
                    </div>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-2 text-center">
                        This action signifies your intent to reactivate the partnership under the same terms. The supplier will review and countersign to finalize.
                    </p>
                </div>

                <div class="flex justify-end gap-2 sm:gap-3 pt-1">
                    <Button variant="outline" class="flex-1 sm:flex-none" @click="showReactivateDialog = false">Cancel</Button>
                    <Button 
                      :disabled="!hasDrawnReactivate || reactivating" 
                      @click="submitReactivation" 
                      class="flex-1 sm:flex-none bg-indigo-600 text-white hover:bg-indigo-700 min-w-[140px]"
                    >
                        <Loader2 v-if="reactivating" class="mr-2 h-4 w-4 animate-spin" />
                        {{ reactivating ? 'Processing...' : 'Sign & Reactivate' }}
                    </Button>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showTerminationDialog" @update:open="showTerminationDialog = $event">
      <DialogContent class="w-full h-[100dvh] max-w-none sm:max-w-5xl sm:h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl rounded-none border-0 sm:border gap-0">
        <DialogHeader class="p-3 sm:p-4 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex items-center gap-2 text-base sm:text-lg text-red-600">
            <Ban class="h-4 w-4 sm:h-5 sm:w-5 shrink-0" />
            <span class="truncate">Terminate Partnership - {{ selectedSupplier?.company_name }}</span>
          </DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-2 sm:p-4 relative bg-slate-100 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-sm border border-slate-200 h-full overflow-y-auto p-4" v-html="terminationHtml"></div>
        </div>

        <div class="p-3 sm:p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] overflow-y-auto max-h-[40vh] sm:max-h-none">
            <div class="flex flex-col gap-3 sm:gap-4 max-w-4xl mx-auto">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs sm:text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-3 w-3 sm:h-4 sm:w-4 text-red-600" />
                            Sign to Initiate Termination
                        </label>
                        <Button variant="ghost" size="sm" class="h-6 sm:h-7 text-xs text-slate-500 hover:text-red-600 px-2" @click="clearTermSignature">
                            Clear Pad
                        </Button>
                    </div>
                    
                    <div class="border-2 border-dashed border-red-200 rounded-lg bg-slate-50 overflow-hidden relative" ref="termCanvasContainer">
                        <canvas 
                            ref="termSignatureCanvas" 
                            class="w-full h-[160px] sm:h-[200px] cursor-crosshair touch-none"
                            @mousedown="startDrawingTerm"
                            @mousemove="drawTerm"
                            @mouseup="stopDrawingTerm"
                            @mouseleave="stopDrawingTerm"
                            @touchstart.prevent="startDrawingTermTouch"
                            @touchmove.prevent="drawTermTouch"
                            @touchend.prevent="stopDrawingTerm"
                        ></canvas>
                        
                        <div v-if="!hasDrawnTerm" class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-40">
                            <span class="text-red-400 text-sm sm:text-base font-medium select-none">Distributor Sign Here</span>
                        </div>
                    </div>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-2 text-center">
                        This action signifies your intent to terminate the partnership. It will be sent to the supplier for final verification.
                    </p>
                </div>

                <div class="flex justify-end gap-2 sm:gap-3 pt-1">
                    <Button variant="outline" class="flex-1 sm:flex-none" @click="showTerminationDialog = false">Cancel</Button>
                    <Button 
                      :disabled="!hasDrawnTerm || terminating" 
                      @click="submitTermination" 
                      class="flex-1 sm:flex-none bg-red-600 text-white hover:bg-red-700 min-w-[140px]"
                    >
                        <Loader2 v-if="terminating" class="mr-2 h-4 w-4 animate-spin" />
                        {{ terminating ? 'Processing...' : 'Sign & Submit' }}
                    </Button>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showViewTerminationDialog" @update:open="showViewTerminationDialog = $event">
      <DialogContent class="w-full h-[100dvh] max-w-none sm:max-w-5xl sm:h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl rounded-none border-0 sm:border gap-0">
        <DialogHeader class="p-3 sm:p-4 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex flex-col sm:flex-row sm:items-center justify-between w-full pr-6 gap-3">
            <span class="flex items-center gap-2 text-base sm:text-lg text-red-600">
                <FileText class="h-4 w-4 sm:h-5 sm:w-5 shrink-0" />
                <span class="truncate">Termination Document - {{ selectedSupplier?.company_name }}</span>
            </span>
            <Button variant="outline" size="sm" @click="downloadTerminationDocument" class="w-full sm:w-auto text-indigo-600 hover:text-indigo-700 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 shrink-0">
                <Download class="h-4 w-4 mr-2" /> Download Document
            </Button>
          </DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-2 sm:p-4 relative bg-slate-100">
            <iframe 
              v-if="selectedSupplier?.termination_url" 
              :src="selectedSupplier.termination_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
            <div v-else class="flex flex-col h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>No digital termination document was found.</p>
            </div>
        </div>

        <div class="p-4 sm:p-6 border-t bg-white shrink-0 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)] z-10 overflow-y-auto max-h-[40vh] sm:max-h-none">
            <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-3 sm:mb-4">Official Signatures</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 max-w-4xl mx-auto">
               <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 border border-indigo-200">Distributor (Initiator)</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedSupplier?.distributor_termination_signature_url" :src="selectedSupplier.distributor_termination_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else class="text-xs sm:text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800">Your Business</p>
                </div>

                <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-blue-100 text-blue-700 border border-blue-200">Supplier (Acknowledged)</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedSupplier?.supplier_termination_signature_url" :src="selectedSupplier.supplier_termination_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else-if="selectedSupplier?.status === 'pending_termination'" class="text-xs sm:text-sm text-amber-500 italic flex items-center gap-1"><Clock class="h-3 w-3"/> Pending Approval...</span>
                        <span v-else class="text-xs sm:text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800 truncate max-w-[150px]">{{ selectedSupplier?.company_name }}</p>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showOfficialDocDialog" @update:open="showOfficialDocDialog = $event">
      <DialogContent class="max-w-5xl h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-100 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex items-center gap-2 text-slate-800">
            <FileBadge class="h-5 w-5 text-emerald-600" />
            Official Partnership Agreement
          </DialogTitle>
        </DialogHeader>
        <div class="flex-1 overflow-hidden p-4 relative">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-300"
            ></iframe>
        </div>
        <div class="p-6 border-t bg-white shrink-0 z-10 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
            <div class="flex justify-end mt-4">
                <Button variant="outline" @click="showOfficialDocDialog = false">Close Document</Button>
            </div>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { 
  Search, 
  MapPin, 
  Star, 
  MoreHorizontal, 
  User, 
  Mail,
  Phone,
  Store,
  FileBadge,
  Eye,
  X,
  MessageSquare,
  FileClock,
  Ban,
  FileText,
  FileX,
  RefreshCw,
  RefreshCcw,
  CheckCircle,
  AlertCircle,
  Loader2,
  PenTool,
  Download,
  Clock
} from 'lucide-vue-next'

// Shadcn UI Imports
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { Separator } from '@/components/ui/separator'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Sheet, SheetContent } from '@/components/ui/sheet'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'

// --- Data ---
const suppliers = ref([])
const loading = ref(true)

// State
const searchQuery = ref('')
const statusFilter = ref('all')
const categoryFilter = ref('all')
const isSheetOpen = ref(false)
const selectedSupplier = ref(null)

// Agreement Signature State
const showAgreementDialog = ref(false)
const signing = ref(false)
const hasDrawn = ref(false)

// Reactivation Signature State
const showReactivateDialog = ref(false)
const reactivating = ref(false)
const hasDrawnReactivate = ref(false)

// Termination Confirmation & State
const showConfirmTerminateDialog = ref(false)
const isCheckingTermination = ref(false)
const showTerminationDialog = ref(false)
const showViewTerminationDialog = ref(false)
const terminating = ref(false)
const terminationHtml = ref('')
const hasDrawnTerm = ref(false)

// Canvas States
const signatureCanvas = ref(null)
const canvasContainer = ref(null)
let ctx = null
let isDrawing = false

const reactivateSignatureCanvas = ref(null)
const reactivateCanvasContainer = ref(null)
let reactivateCtx = null
let isDrawingReactivate = false

const termSignatureCanvas = ref(null)
const termCanvasContainer = ref(null)
let termCtx = null
let isDrawingTerm = false

const printIframe = ref(null)

// --- Helpers ---

const getInitials = (name) => {
  if (!name) return '??'
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
}

const getAvatarColor = (name) => {
  if (!name) return 'bg-slate-100 text-slate-700'
  const colors = [
    'bg-blue-100 text-blue-700',
    'bg-emerald-100 text-emerald-700',
    'bg-violet-100 text-violet-700',
    'bg-amber-100 text-amber-700',
    'bg-rose-100 text-rose-700',
    'bg-cyan-100 text-cyan-700'
  ]
  const index = name.charCodeAt(0) % colors.length
  return colors[index]
}

const getStatusColor = (status) => {
  switch(status) {
    case 'active': return 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
    case 'pending_supplier': return 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'
    case 'pending_reactivation': return 'bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100'
    case 'pending_termination': return 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
    case 'suspended': return 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
    case 'terminated': return 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'
    case 'disconnected': return 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200'
    default: return 'bg-slate-100 text-slate-700'
  }
}

// --- Fetch Data ---
const fetchSuppliers = async () => {
  loading.value = true
  try {
    const response = await api.get('/distributor/partnered-suppliers')
    if (response.data.success) {
      suppliers.value = response.data.data
      
      if (selectedSupplier.value) {
         const updated = suppliers.value.find(s => s.id === selectedSupplier.value.id)
         if (updated) selectedSupplier.value = updated
      }
    }
  } catch (error) {
    console.error('Error fetching suppliers:', error)
    toast.error('Failed to load suppliers', {
      description: 'Please check your connection and try again.'
    })
  } finally {
    loading.value = false
  }
}

// --- Computed & Methods ---

const filteredSuppliers = computed(() => {
  return suppliers.value.filter(s => {
    const searchLower = searchQuery.value.toLowerCase()
    const matchesSearch = s.company_name.toLowerCase().includes(searchLower) || 
                          s.contact_person.toLowerCase().includes(searchLower)
    const matchesStatus = statusFilter.value === 'all' || s.status === statusFilter.value
    const matchesCategory = categoryFilter.value === 'all' || s.category === categoryFilter.value
    
    return matchesSearch && matchesStatus && matchesCategory
  })
})

const openDetails = (supplier) => {
  selectedSupplier.value = supplier
  isSheetOpen.value = true
}

const refreshData = () => {
  fetchSuppliers()
}

const resetFilters = () => {
  searchQuery.value = ''
  statusFilter.value = 'all'
  categoryFilter.value = 'all'
}

// --- Document Generation & Download (Agreements) ---
const downloadDocument = async () => {
    if (!selectedSupplier.value?.agreement_url) return;
    
    const toastId = toast.loading('Preparing Official Document...', { description: 'Merging cryptographic signatures...' });

    try {
        const response = await api.get(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/agreement-raw`);
        
        if (!response.data.success) {
            throw new Error("Failed to load document content.");
        }

        let htmlContent = response.data.html;

        let sigHtml = `
        <div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #cbd5e1; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; page-break-inside: avoid;">
            <h3 style="text-align: center; color: #1e3a8a; text-transform: uppercase; font-size: 1.1rem; letter-spacing: 1px;">Official Cryptographic Signatures</h3>
            <div style="display: flex; justify-content: space-around; margin-top: 40px;">
        `;

        if (selectedSupplier.value.distributor_signature_url) {
            sigHtml += `
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #333; font-size: 1.1rem;">Distributor (Buyer)</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <img src="${selectedSupplier.value.distributor_signature_url}" style="max-height: 100px; max-width: 100%; object-fit: contain;" />
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                    <p style="font-size: 0.9rem; color: #475569; margin: 0;"><strong>Signed:</strong> ${selectedSupplier.value.signed_at || 'N/A'}</p>
                </div>
            `;
        }

        if (selectedSupplier.value.supplier_signature_url) {
            sigHtml += `
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #333; font-size: 1.1rem;">Supplier (Seller)</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <img src="${selectedSupplier.value.supplier_signature_url}" style="max-height: 100px; max-width: 100%; object-fit: contain;" />
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                    <p style="font-size: 0.9rem; color: #475569; margin: 0;"><strong>Signed:</strong> ${selectedSupplier.value.supplier_signed_at || 'N/A'}</p>
                </div>
            `;
        } else {
             sigHtml += `
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #333; font-size: 1.1rem;">Supplier (Seller)</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #94a3b8; font-style: italic;">Pending Signature...</span>
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                    <p style="font-size: 0.9rem; color: #475569; margin: 0;"><strong>Signed:</strong> N/A</p>
                </div>
            `;
        }

        sigHtml += `
            </div>
        </div>
        `;

        if (htmlContent.includes('</body>')) {
            htmlContent = htmlContent.replace('</body>', sigHtml + '</body>');
        } else {
            htmlContent += sigHtml;
        }

        const blob = new Blob([htmlContent], { type: 'text/html' });
        const blobUrl = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = `Official_Agreement_${selectedSupplier.value.company_name.replace(/\s+/g, '_')}.html`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);

        toast.success('Document Downloaded', { 
            id: toastId, 
            description: 'The finalized agreement containing all signatures has been saved.' 
        });

    } catch (e) {
        console.error('Failed to generate document for download', e);
        toast.error('Download Failed', {
            id: toastId,
            description: 'Could not generate the document with signatures. Please try again.'
        });
    }
}


// --- Termination Methods ---

const viewTerminationDocument = (supplier) => {
    selectedSupplier.value = supplier;
    isSheetOpen.value = false;
    showViewTerminationDialog.value = true;
}

const downloadTerminationDocument = async () => {
    if (!selectedSupplier.value?.termination_url) return;

    const toastId = toast.loading('Preparing Official Document...', { description: 'Merging cryptographic signatures...' });

    try {
        const response = await api.get(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/termination-raw`);

        if (!response.data.success) {
            throw new Error("Failed to load document content.");
        }

        let htmlContent = response.data.html;

        let sigHtml = `
        <div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #cbd5e1; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; page-break-inside: avoid;">
            <h3 style="text-align: center; color: #dc2626; text-transform: uppercase; font-size: 1.1rem; letter-spacing: 1px;">Official Cryptographic Signatures</h3>
            <div style="display: flex; justify-content: space-around; margin-top: 40px;">
        `;

        if (selectedSupplier.value.distributor_termination_signature_url) {
            sigHtml += `
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #333; font-size: 1.1rem;">Distributor (Initiator)</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <img src="${selectedSupplier.value.distributor_termination_signature_url}" style="max-height: 100px; max-width: 100%; object-fit: contain;" />
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                </div>
            `;
        }

        if (selectedSupplier.value.supplier_termination_signature_url) {
            sigHtml += `
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #333; font-size: 1.1rem;">Supplier (Acknowledged)</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <img src="${selectedSupplier.value.supplier_termination_signature_url}" style="max-height: 100px; max-width: 100%; object-fit: contain;" />
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                </div>
            `;
        } else {
             sigHtml += `
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px; color: #333; font-size: 1.1rem;">Supplier (Acknowledged)</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <span style="color: #94a3b8; font-style: italic;">Pending Signature...</span>
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                </div>
            `;
        }

        sigHtml += `
            </div>
        </div>
        `;

        if (htmlContent.includes('</body>')) {
            htmlContent = htmlContent.replace('</body>', sigHtml + '</body>');
        } else {
            htmlContent += sigHtml;
        }

        const blob = new Blob([htmlContent], { type: 'text/html' });
        const blobUrl = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = `Termination_Document_${selectedSupplier.value.company_name.replace(/\s+/g, '_')}.html`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);

        toast.success('Document Downloaded', {
            id: toastId,
            description: 'The termination agreement containing all signatures has been saved.'
        });

    } catch (e) {
        console.error('Failed to generate document for download', e);
        toast.error('Download Failed', {
            id: toastId,
            description: 'Could not generate the document with signatures. Please try again.'
        });
    }
}

const confirmTerminationAction = (supplier) => {
    selectedSupplier.value = supplier
    isSheetOpen.value = false
    showConfirmTerminateDialog.value = true
}

const proceedWithTermination = async () => {
    isCheckingTermination.value = true
    const toastId = toast.loading('Verifying transactions...')
    
    try {
        const response = await api.get(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/termination-raw`)
        
        if (response.data.success) {
            toast.dismiss(toastId)
            terminationHtml.value = response.data.html
            showConfirmTerminateDialog.value = false
            showTerminationDialog.value = true
        }
    } catch (error) {
        toast.error('Unable to Terminate', {
            id: toastId,
            description: error.response?.data?.message || 'A server error occurred. Please try again.'
        })
        showConfirmTerminateDialog.value = false
    } finally {
        isCheckingTermination.value = false
    }
}

const submitTermination = async () => {
    if (!selectedSupplier.value || !hasDrawnTerm.value || !termSignatureCanvas.value) return
    terminating.value = true
    
    const signatureBase64 = termSignatureCanvas.value.toDataURL('image/png')
    
    try {
        const response = await api.post(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/terminate`, {
            signature_image: signatureBase64
        })
        
        if (response.data.success) {
            toast.success('Termination Submitted', {
                description: 'The termination document has been signed and emailed to the supplier.'
            })
            
            selectedSupplier.value.status = 'pending_termination'
            showTerminationDialog.value = false
            
            await fetchSuppliers()
        }
    } catch (error) {
        console.error(error)
        toast.error('Termination Failed', {
            description: error.response?.data?.message || 'Failed to submit termination request.'
        })
    } finally {
        terminating.value = false
    }
}

// --- Reactivation Methods ---
const initiateReactivation = (supplier) => {
  selectedSupplier.value = supplier
  showReactivateDialog.value = true
}

const submitReactivation = async () => {
  if (!selectedSupplier.value || !hasDrawnReactivate.value || !reactivateSignatureCanvas.value) return
  
  reactivating.value = true
  const signatureBase64 = reactivateSignatureCanvas.value.toDataURL('image/png')
  
  try {
    const response = await api.post(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/reactivate`, {
        signature_image: signatureBase64
    })
    
    if (response.data.success) {
      toast.success('Reactivation Requested', {
        description: 'The supplier has been notified to counter-sign the agreement.'
      })
      selectedSupplier.value.status = 'pending_reactivation'
      showReactivateDialog.value = false
      await fetchSuppliers()
    }
  } catch (error) {
    console.error('Error reactivating:', error)
    toast.error('Failed to reactivate', {
      description: error.response?.data?.message || 'An error occurred while saving your signature.'
    })
  } finally {
    reactivating.value = false
  }
}

// --- Signature Pad Core Methods (Agreement) ---

const initCanvas = () => {
  if (!signatureCanvas.value || !canvasContainer.value) return
  
  signatureCanvas.value.width = canvasContainer.value.clientWidth
  signatureCanvas.value.height = canvasContainer.value.clientHeight
  
  ctx = signatureCanvas.value.getContext('2d')
  ctx.lineWidth = 3
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  ctx.strokeStyle = '#1e1b4b' // Dark indigo ink
}

const getCoordinates = (e, canvas) => {
  if (!canvas) return { x: 0, y: 0 }
  const rect = canvas.getBoundingClientRect()
  
  const clientX = e.touches && e.touches.length > 0 ? e.touches[0].clientX : e.clientX
  const clientY = e.touches && e.touches.length > 0 ? e.touches[0].clientY : e.clientY
  
  return {
    x: clientX - rect.left,
    y: clientY - rect.top
  }
}

const startDrawing = (e) => {
  isDrawing = true
  hasDrawn.value = true
  const coords = getCoordinates(e, signatureCanvas.value)
  ctx.beginPath()
  ctx.moveTo(coords.x, coords.y)
}

const draw = (e) => {
  if (!isDrawing) return
  const coords = getCoordinates(e, signatureCanvas.value)
  ctx.lineTo(coords.x, coords.y)
  ctx.stroke()
}

const stopDrawing = () => {
  if (!isDrawing) return
  isDrawing = false
  ctx.closePath()
}

const startDrawingTouch = (e) => startDrawing(e)
const drawTouch = (e) => draw(e)

const clearSignature = () => {
  if (!ctx || !signatureCanvas.value) return
  ctx.clearRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height)
  hasDrawn.value = false
}

// --- Signature Pad Core Methods (Reactivation) ---

const initReactivateCanvas = () => {
  if (!reactivateSignatureCanvas.value || !reactivateCanvasContainer.value) return
  
  reactivateSignatureCanvas.value.width = reactivateCanvasContainer.value.clientWidth
  reactivateSignatureCanvas.value.height = reactivateCanvasContainer.value.clientHeight
  
  reactivateCtx = reactivateSignatureCanvas.value.getContext('2d')
  reactivateCtx.lineWidth = 3
  reactivateCtx.lineCap = 'round'
  reactivateCtx.lineJoin = 'round'
  reactivateCtx.strokeStyle = '#4f46e5' // Indigo for reactivation
}

const startDrawingReactivate = (e) => {
  isDrawingReactivate = true
  hasDrawnReactivate.value = true
  const coords = getCoordinates(e, reactivateSignatureCanvas.value)
  reactivateCtx.beginPath()
  reactivateCtx.moveTo(coords.x, coords.y)
}

const drawReactivate = (e) => {
  if (!isDrawingReactivate) return
  const coords = getCoordinates(e, reactivateSignatureCanvas.value)
  reactivateCtx.lineTo(coords.x, coords.y)
  reactivateCtx.stroke()
}

const stopDrawingReactivate = () => {
  if (!isDrawingReactivate) return
  isDrawingReactivate = false
  if (reactivateCtx) reactivateCtx.closePath()
}

const startDrawingReactivateTouch = (e) => startDrawingReactivate(e)
const drawReactivateTouch = (e) => drawReactivate(e)

const clearReactivateSignature = () => {
  if (!reactivateCtx || !reactivateSignatureCanvas.value) return
  reactivateCtx.clearRect(0, 0, reactivateSignatureCanvas.value.width, reactivateSignatureCanvas.value.height)
  hasDrawnReactivate.value = false
}

// --- Signature Pad Core Methods (Termination) ---

const initTermCanvas = () => {
  if (!termSignatureCanvas.value || !termCanvasContainer.value) return
  
  termSignatureCanvas.value.width = termCanvasContainer.value.clientWidth
  termSignatureCanvas.value.height = termCanvasContainer.value.clientHeight
  
  termCtx = termSignatureCanvas.value.getContext('2d')
  termCtx.lineWidth = 3
  termCtx.lineCap = 'round'
  termCtx.lineJoin = 'round'
  termCtx.strokeStyle = '#dc2626' // Red ink for termination intent
}

const startDrawingTerm = (e) => {
  isDrawingTerm = true
  hasDrawnTerm.value = true
  const coords = getCoordinates(e, termSignatureCanvas.value)
  termCtx.beginPath()
  termCtx.moveTo(coords.x, coords.y)
}

const drawTerm = (e) => {
  if (!isDrawingTerm) return
  const coords = getCoordinates(e, termSignatureCanvas.value)
  termCtx.lineTo(coords.x, coords.y)
  termCtx.stroke()
}

const stopDrawingTerm = () => {
  if (!isDrawingTerm) return
  isDrawingTerm = false
  termCtx.closePath()
}

const startDrawingTermTouch = (e) => startDrawingTerm(e)
const drawTermTouch = (e) => drawTerm(e)

const clearTermSignature = () => {
  if (!termCtx || !termSignatureCanvas.value) return
  termCtx.clearRect(0, 0, termSignatureCanvas.value.width, termSignatureCanvas.value.height)
  hasDrawnTerm.value = false
}


// --- Watchers & Initializers ---

watch(showAgreementDialog, async (newVal) => {
  if (newVal && selectedSupplier.value && !selectedSupplier.value.is_signed) {
    hasDrawn.value = false
    await nextTick()
    setTimeout(initCanvas, 50) 
  }
})

watch(showReactivateDialog, async (newVal) => {
  if (newVal) {
    hasDrawnReactivate.value = false
    await nextTick()
    setTimeout(initReactivateCanvas, 50) 
  }
})

watch(showTerminationDialog, async (newVal) => {
  if (newVal) {
    hasDrawnTerm.value = false
    await nextTick()
    setTimeout(initTermCanvas, 50) 
  }
})

const viewAgreement = (supplier) => {
  selectedSupplier.value = supplier
  hasDrawn.value = false
  showAgreementDialog.value = true
}

const submitSignature = async () => {
  if (!selectedSupplier.value || !hasDrawn.value || !signatureCanvas.value) return
  
  signing.value = true
  const signatureBase64 = signatureCanvas.value.toDataURL('image/png')
  
  try {
    const response = await api.post(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/sign`, {
        signature_image: signatureBase64
    })
    
    if (response.data.success) {
      toast.success('Agreement Signed', {
        description: 'Your signature has been permanently affixed to the document.'
      })
      
      selectedSupplier.value.is_signed = true
      selectedSupplier.value.signed_at = response.data.signed_at
      selectedSupplier.value.distributor_signature_url = signatureBase64 
      selectedSupplier.value.status = 'pending_supplier'
      
      await fetchSuppliers()
    }
  } catch (error) {
    console.error('Error signing agreement:', error)
    toast.error('Failed to sign agreement', {
      description: error.response?.data?.message || 'An error occurred while saving your signature.'
    })
  } finally {
    signing.value = false
  }
}

onMounted(() => {
  fetchSuppliers()
})
</script>