<template>
  <div class="flex flex-col gap-6 p-8 min-h-screen">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Distributor Partnerships</h1>
        <p class="text-slate-500 mt-2 text-sm">
          Manage incoming partnership proposals, active partners, and termination requests.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="fetchRequests">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4 mr-2" />
          Refresh Data
        </Button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Proposals</CardTitle>
          <Briefcase class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ pendingRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Awaiting your decision</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active Partners</CardTitle>
          <CheckCircle2 class="h-4 w-4 text-emerald-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ activeRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Officially partnered</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Reactivations</CardTitle>
          <RefreshCcw class="h-4 w-4 text-purple-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ reactivationRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Requested reactivation</p>
        </CardContent>
      </Card>
      
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Terminations</CardTitle>
          <Ban class="h-4 w-4 text-red-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ terminationRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Pending termination</p>
        </CardContent>
      </Card>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white p-4 rounded-lg border shadow-sm">
      <div class="flex gap-4 border-b border-transparent w-full sm:w-auto overflow-x-auto">
        <button 
          @click="activeTab = 'pending'" 
          :class="{'text-blue-600 border-b-2 border-blue-600 font-bold': activeTab === 'pending', 'text-slate-500 hover:text-slate-700': activeTab !== 'pending'}" 
          class="pb-2 text-sm font-medium transition-colors whitespace-nowrap px-2"
        >
          Pending Proposals ({{ pendingRequestsCount }})
        </button>
        <button 
          @click="activeTab = 'active'" 
          :class="{'text-emerald-600 border-b-2 border-emerald-600 font-bold': activeTab === 'active', 'text-slate-500 hover:text-slate-700': activeTab !== 'active'}" 
          class="pb-2 text-sm font-medium transition-colors whitespace-nowrap px-2"
        >
          Active Partners ({{ activeRequestsCount }})
        </button>
        <button 
          @click="activeTab = 'reactivations'" 
          :class="{'text-purple-600 border-b-2 border-purple-600 font-bold': activeTab === 'reactivations', 'text-slate-500 hover:text-slate-700': activeTab !== 'reactivations'}" 
          class="pb-2 text-sm font-medium transition-colors whitespace-nowrap px-2"
        >
          Reactivations ({{ reactivationRequestsCount }})
        </button>
        <button 
          @click="activeTab = 'terminations'" 
          :class="{'text-red-600 border-b-2 border-red-600 font-bold': activeTab === 'terminations', 'text-slate-500 hover:text-slate-700': activeTab !== 'terminations'}" 
          class="pb-2 text-sm font-medium transition-colors whitespace-nowrap px-2"
        >
          Terminations ({{ terminationRequestsCount }})
        </button>
      </div>

      <div class="relative w-full sm:w-80">
        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500" />
        <Input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search by distributor name..." 
          class="pl-9 w-full"
        />
      </div>
    </div>

    <Card class="border shadow-sm">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="bg-slate-50/50">
              <TableHead>Distributor (Buyer)</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Date Updated</TableHead>
              <TableHead>Message / Terms</TableHead>
              <TableHead class="text-right">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="5" class="h-32 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center gap-2">
                  <RefreshCw class="h-6 w-6 animate-spin text-slate-400" />
                  <p>Loading records...</p>
                </div>
              </TableCell>
            </TableRow>
            
            <TableRow v-else-if="filteredRequests.length === 0">
              <TableCell colspan="5" class="h-32 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center gap-2">
                  <Store class="h-8 w-8 text-slate-300" />
                  <p>No records found in this category.</p>
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-for="req in filteredRequests" :key="req.id" class="hover:bg-slate-50/50">
              <TableCell>
                <div class="font-medium text-slate-900">
                  {{ req.distributor?.company_name || req.distributor?.first_name }}
                </div>
                <div class="text-xs text-slate-500">{{ req.distributor?.email }}</div>
              </TableCell>
              
              <TableCell>
                <Badge v-if="req.status === 'pending_supplier'" variant="outline" class="bg-amber-50 text-amber-700 border-amber-200">
                  <Clock class="h-3 w-3 mr-1"/> Pending
                </Badge>
                <Badge v-else-if="req.status === 'active'" variant="outline" class="bg-emerald-50 text-emerald-700 border-emerald-200">
                  <CheckCircle2 class="h-3 w-3 mr-1"/> Active
                </Badge>
                <Badge v-else-if="req.status === 'pending_reactivation'" variant="outline" class="bg-purple-50 text-purple-700 border-purple-200">
                  <RefreshCcw class="h-3 w-3 mr-1"/> Reactivating
                </Badge>
                <Badge v-else-if="req.status === 'pending_termination'" variant="outline" class="bg-red-50 text-red-700 border-red-200">
                  <Ban class="h-3 w-3 mr-1"/> Terminating
                </Badge>
                <Badge v-else-if="req.status === 'terminated' || req.status === 'disconnected'" variant="outline" class="bg-slate-100 text-slate-700 border-slate-300">
                  <XCircle class="h-3 w-3 mr-1"/> Ended
                </Badge>
              </TableCell>

              <TableCell>
                <div class="text-sm">{{ formatDate(req.updated_at) }}</div>
                <div class="text-xs text-slate-500">{{ formatTime(req.updated_at) }}</div>
              </TableCell>

              <TableCell>
                <p class="text-sm text-slate-600 line-clamp-1 max-w-[250px]" :title="req.request_message">
                  {{ req.request_message || 'Standard agreement.' }}
                </p>
              </TableCell>

              <TableCell class="text-right">
                <Button v-if="req.status === 'pending_supplier'" variant="outline" size="sm" @click="viewRequest(req)">
                  Review Proposal
                </Button>
                <Button v-else-if="req.status === 'active'" variant="secondary" size="sm" class="bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100" @click="viewOfficialDocument(req)">
                  <FileBadge class="h-4 w-4 mr-2" /> View Official Document
                </Button>
                <Button v-else-if="req.status === 'pending_reactivation'" variant="outline" size="sm" class="bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100" @click="viewReactivateDocument(req)">
                  <RefreshCcw class="h-4 w-4 mr-2" /> Review Reactivation
                </Button>
                <Button v-else-if="req.status === 'pending_termination'" variant="destructive" size="sm" class="bg-red-50 text-red-700 border border-red-200 hover:bg-red-100" @click="viewTerminationDocument(req)">
                  <AlertTriangle class="h-4 w-4 mr-2" /> Review Termination
                </Button>
                <Button v-else-if="req.status === 'terminated' || req.status === 'disconnected'" variant="outline" size="sm" class="bg-slate-50 text-slate-700 border-slate-200" @click="viewTerminationDocument(req)">
                  <Download class="h-4 w-4 mr-2" /> Final Document
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Dialog :open="showViewDialog" @update:open="showViewDialog = $event">
      <DialogContent class="sm:max-w-[600px] p-0 overflow-hidden">
        <div class="bg-slate-900 p-6 text-white">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-lg bg-white/10 flex items-center justify-center backdrop-blur-sm">
              <Store class="h-6 w-6 text-blue-400" />
            </div>
            <div>
              <DialogTitle class="text-xl font-bold">Incoming Partnership Proposal</DialogTitle>
              <DialogDescription class="text-slate-400 mt-1">
                Distributor wants to source products from your catalog.
              </DialogDescription>
            </div>
          </div>
        </div>

        <div class="p-6 space-y-6" v-if="selectedRequest">
          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Distributor Profile</h4>
            <div class="space-y-3 bg-slate-50 p-4 rounded-lg border border-slate-100">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <Store class="h-4 w-4 text-slate-400" />
                  <span class="text-sm font-bold text-slate-900">
                    {{ selectedRequest.distributor?.company_name || selectedRequest.distributor?.first_name }}
                  </span>
                </div>
                <Badge variant="secondary" class="bg-blue-50 text-blue-700">Verified Buyer</Badge>
              </div>
              
              <div class="grid grid-cols-2 gap-4 mt-2 pt-2 border-t border-slate-200">
                <div class="flex items-center gap-2">
                  <Mail class="h-3.5 w-3.5 text-slate-400" />
                  <span class="text-xs text-slate-600 truncate" :title="selectedRequest.distributor?.email">{{ selectedRequest.distributor?.email }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <Phone class="h-3.5 w-3.5 text-slate-400" />
                  <span class="text-xs text-slate-600">{{ selectedRequest.distributor?.phone || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 border-t flex justify-end gap-3">
          <Button variant="outline" @click="showViewDialog = false">Close</Button>
          <Button variant="destructive" @click="initiateReject" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200">
            <XCircle class="h-4 w-4 mr-2" /> Decline
          </Button>
          <Button class="bg-blue-600 hover:bg-blue-700 text-white" @click="initiateApprove">
            <CheckCircle2 class="h-4 w-4 mr-2" /> Proceed to Sign & Accept
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showRejectDialog" @update:open="showRejectDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-600 flex items-center gap-2">
            <AlertCircle class="h-5 w-5" /> Decline Proposal
          </AlertDialogTitle>
          <AlertDialogDescription>
            You are rejecting this distributor's request to partner. They will be notified via email.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <div class="py-4">
          <label class="text-sm font-medium text-slate-700 mb-1 block">Reason for Declining (Optional)</label>
          <textarea 
            v-model="rejectReason"
            class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-red-500 focus:ring-red-500 min-h-[80px]"
            placeholder="E.g., Currently at maximum capacity..."
          ></textarea>
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isProcessing">Cancel</AlertDialogCancel>
          <Button variant="destructive" @click="submitReject" :disabled="isProcessing">
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            Confirm Decline
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog :open="showTermRejectDialog" @update:open="showTermRejectDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle class="text-blue-600 flex items-center gap-2">
            <AlertCircle class="h-5 w-5" /> Decline Termination
          </AlertDialogTitle>
          <AlertDialogDescription>
            You are declining the termination request. The partnership will revert to <strong>Active</strong> status and the distributor will be notified.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <div class="py-4">
          <label class="text-sm font-medium text-slate-700 mb-1 block">Reason for Declining (Optional)</label>
          <textarea 
            v-model="termRejectReason"
            class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-blue-500 focus:ring-blue-500 min-h-[80px]"
            placeholder="E.g., Let's resolve the pending transactions first..."
          ></textarea>
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isProcessing">Cancel</AlertDialogCancel>
          <Button class="bg-blue-600 hover:bg-blue-700 text-white" @click="submitTermReject" :disabled="isProcessing">
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            Decline Termination
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog :open="showReactivateRejectDialog" @update:open="showReactivateRejectDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-600 flex items-center gap-2">
            <AlertCircle class="h-5 w-5" /> Decline Reactivation
          </AlertDialogTitle>
          <AlertDialogDescription>
            You are declining the reactivation request. The partnership will remain <strong>Terminated</strong> and the distributor will be notified.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <div class="py-4">
          <label class="text-sm font-medium text-slate-700 mb-1 block">Reason for Declining (Optional)</label>
          <textarea 
            v-model="reactivateRejectReason"
            class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-red-500 focus:ring-red-500 min-h-[80px]"
            placeholder="E.g., We are no longer accepting returning partners at this time..."
          ></textarea>
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isProcessing">Cancel</AlertDialogCancel>
          <Button variant="destructive" @click="submitReactivateReject" :disabled="isProcessing">
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            Decline Reactivation
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showSignatureDialog" @update:open="showSignatureDialog = $event">
      <DialogContent class="max-w-5xl h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 border-b bg-white shrink-0">
          <DialogTitle class="flex items-center gap-2 text-slate-800">
            <FileText class="h-5 w-5 text-blue-600" />
            Accept Partnership Agreement
          </DialogTitle>
        </DialogHeader>
        <div class="flex-1 overflow-hidden p-4 relative bg-slate-100">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
        </div>
        <div class="p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10">
            <div class="flex flex-col gap-4 max-w-4xl mx-auto">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-4 w-4 text-blue-600" /> Counter-Sign to Activate
                        </label>
                        <Button variant="ghost" size="sm" @click="clearSignature">Clear Pad</Button>
                    </div>
                    <div class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 overflow-hidden relative" ref="canvasContainer">
                        <canvas 
                            ref="signatureCanvas" 
                            class="w-full h-[160px] cursor-crosshair touch-none"
                            @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                            @touchstart.prevent="startDrawing" @touchmove.prevent="draw" @touchend.prevent="stopDrawing"
                        ></canvas>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <Button variant="outline" @click="showSignatureDialog = false">Cancel</Button>
                    <Button :disabled="!hasDrawn || isProcessing" @click="submitApprove" class="bg-blue-600 text-white hover:bg-blue-700">
                        <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Activating...
                    </Button>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showReactivateSignatureDialog" @update:open="showReactivateSignatureDialog = $event">
      <DialogContent class="max-w-5xl h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 border-b bg-white shrink-0">
          <DialogTitle class="flex items-center gap-2 text-indigo-800">
            <RefreshCcw class="h-5 w-5 text-purple-600" />
            Review Partnership Reactivation
          </DialogTitle>
        </DialogHeader>
        <div class="flex-1 overflow-hidden p-4 relative bg-slate-100">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
        </div>
        <div class="p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10 max-h-[40vh] overflow-y-auto">
            <div class="flex flex-col gap-4 max-w-4xl mx-auto">
                <div class="p-3 bg-purple-50 border border-purple-100 rounded-lg flex items-center gap-4">
                     <div class="h-16 w-32 bg-white border flex items-center justify-center shrink-0">
                         <img :src="selectedRequest?.distributor_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                     </div>
                     <p class="text-sm text-purple-800 font-medium">Distributor has re-signed the agreement to reactivate the partnership. Please countersign to restore their active status.</p>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-4 w-4 text-purple-600" /> Counter-Sign to Reactivate
                        </label>
                        <Button variant="ghost" size="sm" @click="clearReactivateSignature">Clear Pad</Button>
                    </div>
                    <div class="border-2 border-dashed border-purple-300 rounded-lg bg-slate-50 overflow-hidden relative" ref="reactivateCanvasContainer">
                        <canvas 
                            ref="reactivateSignatureCanvas" 
                            class="w-full h-[140px] cursor-crosshair touch-none"
                            @mousedown="startReactivateDrawing" @mousemove="drawReactivate" @mouseup="stopReactivateDrawing" @mouseleave="stopReactivateDrawing"
                            @touchstart.prevent="startReactivateDrawing" @touchmove.prevent="drawReactivate" @touchend.prevent="stopReactivateDrawing"
                        ></canvas>
                        <div v-if="!hasDrawnReactivate" class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-40">
                            <span class="text-slate-400 font-medium select-none">Supplier Sign Here</span>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <Button variant="outline" @click="initiateReactivateReject" class="bg-red-50 text-red-700 border-red-200 hover:bg-red-100">Decline Reactivation</Button>
                    <Button :disabled="!hasDrawnReactivate || isProcessing" @click="submitReactivateApprove" class="bg-purple-600 text-white hover:bg-purple-700">
                        <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Sign & Reactivate
                    </Button>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showTermSignatureDialog" @update:open="showTermSignatureDialog = $event">
      <DialogContent class="max-w-5xl h-[100dvh] sm:h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 border-b bg-white shrink-0">
          <DialogTitle class="flex items-center justify-between w-full pr-6 gap-3">
             <span class="flex items-center gap-2 text-slate-800">
                <Ban class="h-5 w-5 text-red-600" />
                Partnership Termination Document
             </span>
             <Button v-if="selectedRequest?.status === 'terminated' || selectedRequest?.status === 'disconnected'" variant="outline" size="sm" @click="downloadTerminationDoc" class="text-indigo-600 hover:text-indigo-700 border-indigo-200 bg-indigo-50 shrink-0">
                <Download class="h-4 w-4 mr-2" /> Download Final Document
            </Button>
          </DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-4 relative bg-slate-100 overflow-y-auto">
            <div v-if="loadingTermDoc" class="h-full flex flex-col items-center justify-center text-slate-500">
                <Loader2 class="h-8 w-8 animate-spin mb-2 text-red-500" />
                <p>Retrieving official document...</p>
            </div>
            <div v-else-if="terminationHtml" class="bg-white rounded-lg shadow-sm border border-slate-200 h-full overflow-y-auto p-6" v-html="terminationHtml"></div>
            <div v-else class="flex flex-col h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>Termination document not found.</p>
            </div>
        </div>

        <div v-if="selectedRequest?.status === 'pending_termination'" class="p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10 max-h-[40vh] overflow-y-auto">
            <div class="flex flex-col gap-4 max-w-4xl mx-auto">
                <div class="p-3 bg-red-50 border border-red-100 rounded-lg flex items-center gap-4">
                     <div class="h-16 w-32 bg-white border flex items-center justify-center shrink-0">
                         <img :src="selectedRequest.distributor_termination_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                     </div>
                     <p class="text-sm text-red-800 font-medium">Distributor has initiated termination and applied their signature. Please countersign to officially end the partnership or decline to stay active.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-4 w-4 text-red-600" /> Countersign to Finalize Termination
                        </label>
                        <Button variant="ghost" size="sm" @click="clearTermSignature">Clear Pad</Button>
                    </div>
                    <div class="border-2 border-dashed border-red-300 rounded-lg bg-slate-50 overflow-hidden relative" ref="termCanvasContainer">
                        <canvas 
                            ref="termSignatureCanvas" 
                            class="w-full h-[140px] cursor-crosshair touch-none"
                            @mousedown="startTermDrawing" @mousemove="drawTerm" @mouseup="stopTermDrawing" @mouseleave="stopTermDrawing"
                            @touchstart.prevent="startTermDrawing" @touchmove.prevent="drawTerm" @touchend.prevent="stopTermDrawing"
                        ></canvas>
                        <div v-if="!hasDrawnTerm" class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-40">
                            <span class="text-slate-400 font-medium select-none">Supplier Sign Here</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-2">
                    <Button variant="outline" @click="initiateTermReject" class="bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100">Decline Termination</Button>
                    <Button :disabled="!hasDrawnTerm || isProcessing" @click="submitTermApprove" class="bg-red-600 text-white hover:bg-red-700">
                        <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Sign & End Partnership
                    </Button>
                </div>
            </div>
        </div>

        <div v-else-if="selectedRequest?.status === 'terminated' || selectedRequest?.status === 'disconnected'" class="p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10 max-h-[40vh] overflow-y-auto">
             <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-4">Official Termination Signatures</h4>
             <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-4xl mx-auto">
                <div class="flex flex-col items-center justify-center border border-slate-200 p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 border border-indigo-200">Distributor</Badge>
                    <div class="h-20 w-full flex items-center justify-center mt-2">
                        <img :src="selectedRequest.distributor_termination_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                    </div>
                    <Separator class="my-3 w-3/4 bg-slate-300" />
                    <p class="text-sm font-bold text-slate-800">{{ selectedRequest.distributor?.company_name }}</p>
                </div>
                <div class="flex flex-col items-center justify-center border border-slate-200 p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-blue-100 text-blue-700 border border-blue-200">Supplier</Badge>
                    <div class="h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedRequest.supplier_termination_signature_url" :src="selectedRequest.supplier_termination_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else class="text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-3 w-3/4 bg-slate-300" />
                    <p class="text-sm font-bold text-slate-800">Your Business</p>
                    <p class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle2 class="h-3 w-3 text-emerald-500" /> Ended
                    </p>
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
        <div class="p-6 border-t bg-white shrink-0 z-10 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)] overflow-y-auto max-h-[40vh] sm:max-h-none">
            <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-3 sm:mb-4">Official Signatures</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 max-w-4xl mx-auto">
               <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 border border-indigo-200">Distributor (Buyer)</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedRequest?.distributor_signature_url" :src="selectedRequest.distributor_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else class="text-xs sm:text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800">{{ selectedRequest?.distributor?.company_name || selectedRequest?.distributor?.first_name }}</p>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle class="h-3 w-3 text-emerald-500" /> Signed
                    </p>
                </div>

                <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-blue-100 text-blue-700 border border-blue-200">Supplier (Seller)</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedRequest?.supplier_signature_url" :src="selectedRequest.supplier_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else class="text-xs sm:text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800">Your Business</p>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle class="h-3 w-3 text-emerald-500" /> Signed
                    </p>
                </div>
            </div>
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
  Store, Briefcase, CheckCircle2, XCircle, AlertCircle, MapPin, Phone, Mail, 
  RefreshCw, RefreshCcw, Search, PenTool, FileText, FileBadge, FileX, Loader2, Clock, Ban,
  AlertTriangle, Download
} from 'lucide-vue-next'

import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

// State
const requests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const isProcessing = ref(false)
const activeTab = ref('pending') 

const selectedRequest = ref(null)
const showViewDialog = ref(false)
const showOfficialDocDialog = ref(false)

// Reject State
const showRejectDialog = ref(false)
const rejectReason = ref('')

// Term Reject State
const showTermRejectDialog = ref(false)
const termRejectReason = ref('')

// Reactivate Reject State
const showReactivateRejectDialog = ref(false)
const reactivateRejectReason = ref('')

// Document Fetching state
const terminationHtml = ref('')
const loadingTermDoc = ref(false)

// Initial Signature State
const showSignatureDialog = ref(false)
const hasDrawn = ref(false)
const signatureCanvas = ref(null)
const canvasContainer = ref(null)
let ctx = null
let isDrawing = false

// Termination Signature State
const showTermSignatureDialog = ref(false)
const hasDrawnTerm = ref(false)
const termSignatureCanvas = ref(null)
const termCanvasContainer = ref(null)
let termCtx = null
let isTermDrawing = false

// Reactivation Signature State
const showReactivateSignatureDialog = ref(false)
const hasDrawnReactivate = ref(false)
const reactivateSignatureCanvas = ref(null)
const reactivateCanvasContainer = ref(null)
let reactivateCtx = null
let isReactivateDrawing = false

// --- Canvas Initializers ---
const initCanvas = () => {
  if (!signatureCanvas.value || !canvasContainer.value) return
  signatureCanvas.value.width = canvasContainer.value.clientWidth
  signatureCanvas.value.height = canvasContainer.value.clientHeight
  ctx = signatureCanvas.value.getContext('2d')
  ctx.lineWidth = 3
  ctx.lineCap = 'round'
  ctx.strokeStyle = '#1e3a8a'
}

const initTermCanvas = () => {
  if (!termSignatureCanvas.value || !termCanvasContainer.value) return
  termSignatureCanvas.value.width = termCanvasContainer.value.clientWidth
  termSignatureCanvas.value.height = termCanvasContainer.value.clientHeight
  termCtx = termSignatureCanvas.value.getContext('2d')
  termCtx.lineWidth = 3
  termCtx.lineCap = 'round'
  termCtx.strokeStyle = '#dc2626'
}

const initReactivateCanvas = () => {
  if (!reactivateSignatureCanvas.value || !reactivateCanvasContainer.value) return
  reactivateSignatureCanvas.value.width = reactivateCanvasContainer.value.clientWidth
  reactivateSignatureCanvas.value.height = reactivateCanvasContainer.value.clientHeight
  reactivateCtx = reactivateSignatureCanvas.value.getContext('2d')
  reactivateCtx.lineWidth = 3
  reactivateCtx.lineCap = 'round'
  reactivateCtx.strokeStyle = '#9333ea' // Purple ink for reactivation
}

const getCoordinates = (e, canvasObj) => {
  if (!canvasObj) return { x: 0, y: 0 }
  const rect = canvasObj.getBoundingClientRect()
  const clientX = e.touches && e.touches.length > 0 ? e.touches[0].clientX : e.clientX
  const clientY = e.touches && e.touches.length > 0 ? e.touches[0].clientY : e.clientY
  return { x: clientX - rect.left, y: clientY - rect.top }
}

// Initial Agreement Drawing
const startDrawing = (e) => { isDrawing = true; hasDrawn.value = true; const c = getCoordinates(e, signatureCanvas.value); ctx.beginPath(); ctx.moveTo(c.x, c.y) }
const draw = (e) => { if (!isDrawing) return; const c = getCoordinates(e, signatureCanvas.value); ctx.lineTo(c.x, c.y); ctx.stroke() }
const stopDrawing = () => { isDrawing = false; if(ctx) ctx.closePath() }
const clearSignature = () => { if (!ctx) return; ctx.clearRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height); hasDrawn.value = false }

// Termination Drawing
const startTermDrawing = (e) => { isTermDrawing = true; hasDrawnTerm.value = true; const c = getCoordinates(e, termSignatureCanvas.value); termCtx.beginPath(); termCtx.moveTo(c.x, c.y) }
const drawTerm = (e) => { if (!isTermDrawing) return; const c = getCoordinates(e, termSignatureCanvas.value); termCtx.lineTo(c.x, c.y); termCtx.stroke() }
const stopTermDrawing = () => { isTermDrawing = false; if(termCtx) termCtx.closePath() }
const clearTermSignature = () => { if (!termCtx) return; termCtx.clearRect(0, 0, termSignatureCanvas.value.width, termSignatureCanvas.value.height); hasDrawnTerm.value = false }

// Reactivation Drawing
const startReactivateDrawing = (e) => { isReactivateDrawing = true; hasDrawnReactivate.value = true; const c = getCoordinates(e, reactivateSignatureCanvas.value); reactivateCtx.beginPath(); reactivateCtx.moveTo(c.x, c.y) }
const drawReactivate = (e) => { if (!isReactivateDrawing) return; const c = getCoordinates(e, reactivateSignatureCanvas.value); reactivateCtx.lineTo(c.x, c.y); reactivateCtx.stroke() }
const stopReactivateDrawing = () => { isReactivateDrawing = false; if(reactivateCtx) reactivateCtx.closePath() }
const clearReactivateSignature = () => { if (!reactivateCtx) return; reactivateCtx.clearRect(0, 0, reactivateSignatureCanvas.value.width, reactivateSignatureCanvas.value.height); hasDrawnReactivate.value = false }


watch(showSignatureDialog, async (newVal) => { if (newVal) { hasDrawn.value = false; await nextTick(); setTimeout(initCanvas, 50) }})
watch(showTermSignatureDialog, async (newVal) => { if (newVal && selectedRequest.value?.status === 'pending_termination') { hasDrawnTerm.value = false; await nextTick(); setTimeout(initTermCanvas, 50) }})
watch(showReactivateSignatureDialog, async (newVal) => { if (newVal && selectedRequest.value?.status === 'pending_reactivation') { hasDrawnReactivate.value = false; await nextTick(); setTimeout(initReactivateCanvas, 50) }})


// --- API Logic ---
const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/supplier/distributor-requests')
    if (response.data.success) {
      requests.value = response.data.data
    }
  } catch (error) {
    toast.error('Failed to load records')
  } finally {
    loading.value = false
  }
}

const pendingRequestsCount = computed(() => requests.value.filter(r => r.status === 'pending_supplier').length)
const activeRequestsCount = computed(() => requests.value.filter(r => r.status === 'active').length)
const terminationRequestsCount = computed(() => requests.value.filter(r => r.status === 'pending_termination').length)
const reactivationRequestsCount = computed(() => requests.value.filter(r => r.status === 'pending_reactivation').length)

const filteredRequests = computed(() => {
  let list = []
  if (activeTab.value === 'pending') list = requests.value.filter(r => r.status === 'pending_supplier')
  else if (activeTab.value === 'active') list = requests.value.filter(r => r.status === 'active')
  else if (activeTab.value === 'reactivations') list = requests.value.filter(r => r.status === 'pending_reactivation')
  else if (activeTab.value === 'terminations') list = requests.value.filter(r => r.status === 'pending_termination' || r.status === 'terminated' || r.status === 'disconnected')
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(req => req.distributor?.company_name?.toLowerCase().includes(q) || req.distributor?.first_name?.toLowerCase().includes(q))
  }
  return list
})

const viewRequest = (req) => { selectedRequest.value = req; showViewDialog.value = true }
const viewOfficialDocument = (req) => { selectedRequest.value = req; showOfficialDocDialog.value = true }
const viewReactivateDocument = (req) => { selectedRequest.value = req; showReactivateSignatureDialog.value = true }

const viewTerminationDocument = async (req) => { 
    selectedRequest.value = req; 
    showTermSignatureDialog.value = true;
    loadingTermDoc.value = true;
    terminationHtml.value = '';

    try {
        const response = await api.get(`/supplier/distributor-requests/${req.id}/termination-raw`);
        if (response.data.success) {
            terminationHtml.value = response.data.html;
        }
    } catch (error) {
        toast.error('Failed to load termination document');
    } finally {
        loadingTermDoc.value = false;
    }
}

// Initial Actions
const initiateApprove = () => { showViewDialog.value = false; showSignatureDialog.value = true }
const submitApprove = async () => {
  if (!selectedRequest.value || !hasDrawn.value) return
  isProcessing.value = true
  try {
    const b64 = signatureCanvas.value.toDataURL('image/png')
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/approve`, { signature_image: b64 })
    if (res.data.success) {
      await fetchRequests()
      showSignatureDialog.value = false
      activeTab.value = 'active'
      toast.success('Partnership Activated')
    }
  } catch (err) { toast.error('Approval Failed') }
  finally { isProcessing.value = false }
}

const initiateReject = () => { rejectReason.value = ''; showViewDialog.value = false; showRejectDialog.value = true }
const submitReject = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true
  try {
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/reject`, { reason: rejectReason.value })
    if (res.data.success) {
      await fetchRequests()
      showRejectDialog.value = false
      toast.info('Proposal Declined')
    }
  } catch (err) { toast.error('Action Failed') }
  finally { isProcessing.value = false }
}


// Termination Actions
const initiateTermReject = () => {
    termRejectReason.value = ''
    showTermSignatureDialog.value = false
    showTermRejectDialog.value = true
}

const submitTermReject = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true
  try {
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/terminate-reject`, { reason: termRejectReason.value })
    if (res.data.success) {
      await fetchRequests()
      showTermRejectDialog.value = false
      activeTab.value = 'active'
      toast.success('Termination Declined, Partnership Restored')
    }
  } catch (err) { toast.error('Action Failed') }
  finally { isProcessing.value = false }
}

const submitTermApprove = async () => {
  if (!selectedRequest.value || !hasDrawnTerm.value) return
  isProcessing.value = true
  try {
    const b64 = termSignatureCanvas.value.toDataURL('image/png')
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/terminate-approve`, { signature_image: b64 })
    if (res.data.success) {
      await fetchRequests()
      showTermSignatureDialog.value = false
      toast.success('Partnership Terminated')
    }
  } catch (err) { toast.error('Termination Failed') }
  finally { isProcessing.value = false }
}

// Reactivation Actions
const initiateReactivateReject = () => {
    reactivateRejectReason.value = ''
    showReactivateSignatureDialog.value = false
    showReactivateRejectDialog.value = true
}

const submitReactivateReject = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true
  try {
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/reactivate-reject`, { reason: reactivateRejectReason.value })
    if (res.data.success) {
      await fetchRequests()
      showReactivateRejectDialog.value = false
      activeTab.value = 'terminations'
      toast.success('Reactivation Declined, Kept as Terminated')
    }
  } catch (err) { toast.error('Action Failed') }
  finally { isProcessing.value = false }
}

const submitReactivateApprove = async () => {
  if (!selectedRequest.value || !hasDrawnReactivate.value) return
  isProcessing.value = true
  try {
    const b64 = reactivateSignatureCanvas.value.toDataURL('image/png')
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/reactivate-approve`, { signature_image: b64 })
    if (res.data.success) {
      await fetchRequests()
      showReactivateSignatureDialog.value = false
      activeTab.value = 'active'
      toast.success('Partnership Reactivated Successfully')
    }
  } catch (err) { toast.error('Reactivation Failed') }
  finally { isProcessing.value = false }
}

// Download Termination Document
const downloadTerminationDoc = async () => {
    if (!selectedRequest.value) return;
    const toastId = toast.loading('Preparing Final Document...');
    try {
        const response = await api.get(`/supplier/distributor-requests/${selectedRequest.value.id}/termination-raw`);
        if (!response.data.success) throw new Error("Document load failed");

        let htmlContent = response.data.html;
        let sigHtml = `
        <div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #cbd5e1; font-family: 'Helvetica Neue', Arial, sans-serif; page-break-inside: avoid;">
            <h3 style="text-align: center; color: #dc2626; text-transform: uppercase;">Official Cryptographic Signatures</h3>
            <div style="display: flex; justify-content: space-around; margin-top: 40px;">
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px;">Distributor</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <img src="${selectedRequest.value.distributor_termination_signature_url}" style="max-height: 100px; max-width: 100%; object-fit: contain;" />
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                </div>
                <div style="text-align: center; width: 40%;">
                    <p style="font-weight: bold; margin-bottom: 10px;">Supplier</p>
                    <div style="height: 100px; display: flex; align-items: center; justify-content: center;">
                        <img src="${selectedRequest.value.supplier_termination_signature_url}" style="max-height: 100px; max-width: 100%; object-fit: contain;" />
                    </div>
                    <hr style="border: 0; border-top: 1px solid #94a3b8; margin: 15px 0;" />
                </div>
            </div>
        </div>`;

        if (htmlContent.includes('</body>')) {
            htmlContent = htmlContent.replace('</body>', sigHtml + '</body>');
        } else {
            htmlContent += sigHtml;
        }

        const blob = new Blob([htmlContent], { type: 'text/html' });
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = `Final_Termination_${selectedRequest.value.distributor?.company_name.replace(/\s+/g, '_')}.html`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);

        toast.success('Document Downloaded', { id: toastId });
    } catch (e) {
        toast.error('Download Failed', { id: toastId });
    }
}

const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'
const formatTime = (dateStr) => dateStr ? new Date(dateStr).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' }) : ''

onMounted(() => fetchRequests())
</script>