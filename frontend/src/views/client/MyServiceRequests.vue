<template>
  <div class="min-h-screen bg-gradient-to-br p-3 sm:p-4 md:p-6 text-slate-200">
    <div class="mb-6 sm:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="max-w-full overflow-hidden">
          <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-300 to-teal-300 leading-tight sm:leading-normal">
            My Service Requests
          </h1>
          <p class="text-gray-400 mt-1 sm:mt-2 text-sm sm:text-base">Track and manage all your painting service jobs</p>
        </div>
        
        <div class="flex items-center space-x-2 mt-2 sm:mt-0">
          <Select v-model="activeFilter">
            <SelectTrigger class="w-[180px] bg-gray-800/50 border-gray-700 text-gray-300 rounded-xl focus:ring-cyan-500/30 backdrop-blur-sm">
              <div class="flex items-center gap-2">
                <Filter class="w-4 h-4 text-cyan-400" />
                <SelectValue placeholder="Filter by status" />
              </div>
            </SelectTrigger>
            <SelectContent class="bg-gray-800 border-gray-700 text-gray-300">
              <SelectItem value="all">All Requests</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="verifying">Verifying</SelectItem>
              <SelectItem value="ongoing">Ongoing</SelectItem>
              <SelectItem value="completion_review">Awaiting Approval</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
      
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
        <Card v-for="stat in statsCards" :key="stat.label" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 border-gray-700/50 backdrop-blur-sm shadow-none">
          <CardContent class="p-3 sm:p-4 flex items-center justify-between">
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm truncate">{{ stat.label }}</p>
              <p :class="['text-xl sm:text-2xl font-bold mt-1 truncate', stat.colorClass]">
                {{ stat.value }}
              </p>
            </div>
            <div :class="['p-2 sm:p-3 rounded-xl flex-shrink-0 ml-2', stat.bgClass]">
              <component :is="stat.icon" :class="['w-5 h-5 sm:w-6 sm:h-6', stat.iconClass]" />
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-cyan-500 mb-4"></div>
      <p class="text-gray-400 text-sm">Fetching your service requests...</p>
    </div>

    <div v-else class="grid grid-cols-1 gap-4 sm:gap-6">
      <Card 
        v-for="request in filteredRequests"
        :key="request.id"
        class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 border-gray-700/50 backdrop-blur-sm hover:border-cyan-500/30 transition-all duration-300 group shadow-none overflow-hidden"
      >
        <CardContent class="p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
            <div class="flex flex-col xs:flex-row items-start gap-3 sm:gap-4 w-full sm:w-auto">
              
              <div class="relative flex-shrink-0">
                <div 
                  class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl shadow-lg sm:shadow-2xl transition-transform duration-300 group-hover:scale-105 overflow-hidden flex items-center justify-center"
                  :style="!request.imageUrl ? { backgroundColor: request.color } : {}"
                >
                  <img v-if="request.imageUrl" :src="request.imageUrl" alt="Service Image" class="w-full h-full object-cover" />
                </div>
                <Badge variant="secondary" class="absolute -bottom-1 -right-1 px-2 py-0.5 bg-gray-900/90 text-[10px] font-mono border-gray-700">
                  {{ request.colorCode }}
                </Badge>
              </div>
              
              <div class="flex-1 min-w-0">
                <div class="flex flex-col xs:flex-row xs:items-center gap-2 xs:gap-3 mb-2 sm:mb-3">
                  <Badge :class="['font-semibold border uppercase tracking-wider text-[10px]', getStatusClasses(request.status)]">
                    {{ request.statusLabel }}
                  </Badge>
                  <span class="text-xs sm:text-sm text-gray-400">{{ request.date }}</span>
                </div>
                
                <h3 class="text-base sm:text-lg font-semibold text-white mb-1 sm:mb-2 truncate">{{ request.projectName }}</h3>
                <p class="text-gray-400 text-xs sm:text-sm mb-3 sm:mb-4 line-clamp-2 leading-relaxed">{{ request.description }}</p>
                
                <div class="flex items-center gap-2 sm:gap-3">
                  <Avatar class="w-7 h-7 sm:w-8 sm:h-8 border border-cyan-500/30">
                    <AvatarImage src="" />
                    <AvatarFallback class="bg-gradient-to-r from-blue-500 to-cyan-400 text-white text-[10px]">
                      <User class="w-3 h-3 sm:w-4 sm:h-4" />
                    </AvatarFallback>
                  </Avatar>
                  <div class="min-w-0">
                    <p class="text-xs sm:text-sm font-medium text-white truncate">{{ request.serviceProvider }}</p>
                    <p class="text-[10px] text-gray-500 uppercase tracking-tighter">Assigned Professional</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end sm:justify-start mt-3 sm:mt-0 sm:self-center gap-2">
              <Button 
                v-if="request.status === 'completion_review'"
                variant="default" 
                size="sm"
                @click="viewDetails(request)"
                class="bg-pink-600 hover:bg-pink-700 text-white rounded-xl gap-2 shadow-lg shadow-pink-900/20 animate-pulse-slow"
              >
                <Eye class="w-4 h-4" />
                Review Work
              </Button>

              <Button 
                v-if="request.status === 'pending' || request.status === 'completed' || request.status === 'rejected' || request.status === 'ongoing'"
                variant="outline" 
                size="sm"
                @click="viewDetails(request)"
                class="bg-gray-700/50 hover:bg-gray-700 text-gray-300 border-gray-600 hover:border-cyan-500/50 rounded-xl gap-2"
              >
                <Eye class="w-4 h-4" />
                Details
              </Button>

              <Button 
                v-if="request.status === 'completed' && !request.raw.service_review"
                variant="default" 
                size="sm"
                @click="openReviewModal(request)"
                class="bg-amber-500 hover:bg-amber-600 text-white rounded-xl gap-2 shadow-lg shadow-amber-900/20"
              >
                <Star class="w-4 h-4 fill-current" />
                Leave Review
              </Button>
              
              <Button 
                v-if="request.status === 'verifying' || request.status === 'ongoing' || request.status === 'completion_review'"
                variant="default" 
                size="sm"
                @click="goToChat"
                class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl gap-2 shadow-lg shadow-blue-900/20"
              >
                <MessageSquare class="w-4 h-4" />
                Messages
              </Button>

              <Button 
                v-if="request.raw.payment_term && (request.raw.payment_term.status === 'agreed' || request.raw.payment_term.status === 'awaiting_proof_approval' || request.raw.payment_term.status === 'paid')"
                variant="secondary" 
                size="sm"
                @click="viewDetails(request)"
                class="bg-yellow-600/20 hover:bg-yellow-600/40 text-yellow-400 border border-yellow-500/30 rounded-xl gap-2 shadow-lg shadow-yellow-900/20"
              >
                <CreditCard class="w-4 h-4" />
                Payment Actions
              </Button>
            </div>
          </div>
          
          <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-700/50">
            <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 mb-3">
              <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></div>
                  <span class="text-[11px] text-gray-400">Requested: <span class="text-gray-200">{{ request.requestedDate }}</span></span>
                </div>
                <div class="flex items-center gap-2">
                  <div :class="['w-2 h-2 rounded-full', getIndicatorColor(request.status)]"></div>
                  <span class="text-[11px] text-gray-400">Last Update: <span class="text-gray-200">{{ request.currentStageDate }}</span></span>
                </div>
              </div>
              
              <div :class="['text-xs font-bold uppercase tracking-widest', getStatusTextColor(request.status)]">
                {{ getStatusMessage(request.status) }}
              </div>
            </div>
            
            <Progress 
              :model-value="request.progress" 
              :class="['h-2 bg-gray-800', getProgressBarTheme(request.status)]" 
            />
          </div>
        </CardContent>
      </Card>
    </div>

    <div v-if="!isLoading && filteredRequests.length === 0" class="text-center py-12 sm:py-24">
      <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-6 bg-gray-800/50 rounded-3xl flex items-center justify-center border border-gray-700/50">
        <ClipboardList class="w-10 h-10 text-gray-600" />
      </div>
      <h3 class="text-lg sm:text-xl font-semibold text-gray-300 mb-2">No service requests found</h3>
      <p class="text-gray-500 max-w-md mx-auto text-sm px-4">
        {{ activeFilter === 'all' 
          ? "You haven't created any service requests yet." 
          : `You don't have any ${activeFilter} service requests.` 
        }}
      </p>
    </div>

    <Dialog v-model:open="isModalOpen">
      <DialogContent class="bg-gray-900 border-gray-800 text-slate-200 sm:max-w-xl rounded-2xl max-h-[90vh] overflow-y-auto custom-scrollbar p-0">
        <div class="sticky top-0 z-10 bg-gray-900/95 backdrop-blur-sm border-b border-gray-800 px-6 py-5">
          <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
             <ClipboardList class="w-5 h-5 text-cyan-400" />
             Service Request Details
          </DialogTitle>
        </div>
        
        <div v-if="selectedRequest" class="px-6 py-5 space-y-6">

           <div v-if="selectedRequest.status === 'completion_review'" class="bg-gradient-to-br from-pink-900/20 to-pink-800/20 rounded-2xl p-5 border border-pink-700/50 shadow-inner">
             <h4 class="text-sm font-bold text-pink-400 mb-2 flex items-center gap-2 uppercase tracking-wider">
               <CheckCircle2 class="w-4 h-4" />
               Provider Submitted Proof of Completion
             </h4>
             <p class="text-xs text-gray-300 mb-4">The service provider has marked this job as finished. Please review the attached proofs below.</p>
             
             <div class="grid grid-cols-2 gap-2 mb-4">
                <div v-for="(img, idx) in selectedRequest.raw.latest_completion?.proof_images_url" :key="idx" class="h-32 rounded-lg overflow-hidden border border-slate-700">
                   <img :src="img" class="w-full h-full object-cover hover:scale-105 transition-transform" />
                </div>
             </div>

             <div class="flex gap-3 pt-2">
                <Button @click="openRejectModal" class="flex-1 bg-red-600/20 text-red-400 hover:bg-red-600/30 border border-red-500/30">
                   Reject & Request Fix
                </Button>
                <Button @click="approveCompletion" :disabled="isApprovingWork" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-900/20">
                   <span v-if="isApprovingWork" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...</span>
                   <span v-else>Approve & Finish Job</span>
                </Button>
             </div>
           </div>

           <div v-if="selectedRequest.status === 'ongoing' && selectedRequest.raw.latest_completion && selectedRequest.raw.latest_completion.status === 'rejected'" class="bg-red-900/20 border border-red-800/50 p-4 rounded-xl">
             <p class="text-sm font-bold text-red-400 mb-1">You rejected the previous completion proof</p>
             <p class="text-xs text-gray-300 italic border-l-2 border-red-500/50 pl-2">"{{ selectedRequest.raw.latest_completion.rejection_reason }}"</p>
             <p class="text-xs text-gray-400 mt-2">The provider is currently working to address your feedback.</p>
           </div>
           
           <div v-if="selectedRequest.raw.payment_term" class="bg-gradient-to-br from-yellow-900/10 to-yellow-800/20 rounded-2xl p-5 border border-yellow-700/50 shadow-inner">
             <h4 class="text-sm font-bold text-yellow-400 mb-4 flex items-center gap-2 uppercase tracking-wider">
               <CreditCard class="w-4 h-4" />
               Official Payment Terms
             </h4>

             <div class="grid grid-cols-2 gap-4 bg-gray-900/50 p-3 rounded-xl border border-gray-800 mb-4">
               <div>
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Payment Method</p>
                 <p class="text-xs text-yellow-300 font-bold uppercase">{{ selectedRequest.raw.payment_term.payment_method }}</p>
               </div>
               <div>
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Term Condition</p>
                 <p class="text-xs text-gray-200 font-medium">{{ selectedRequest.raw.payment_term.payment_term }}</p>
               </div>
             </div>

             <div class="grid grid-cols-2 gap-4 bg-slate-900/80 p-3 rounded-xl border border-slate-700 mb-4 shadow-sm">
               <div>
                 <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total Paid So Far</p>
                 <p class="text-sm text-emerald-400 font-bold tracking-tight">₱{{ Number(selectedRequest.raw.payment_term.total_paid || 0).toLocaleString() }}</p>
               </div>
               <div>
                 <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Remaining Balance</p>
                 <p class="text-sm text-red-400 font-bold tracking-tight">₱{{ Number(selectedRequest.raw.payment_term.balance || 0).toLocaleString() }}</p>
               </div>
             </div>

             <div v-if="selectedRequest.raw.payment_term.status === 'agreed' || (selectedRequest.raw.payment_term.status === 'paid' && selectedRequest.raw.payment_term.balance > 0)">
                
                <div v-if="selectedRequest.raw.payment_term.status === 'paid' && selectedRequest.raw.payment_term.balance > 0" class="mb-4 text-center border-b border-gray-800 pb-3">
                   <p class="text-sm text-emerald-400 font-bold mb-1 flex items-center justify-center gap-1.5"><CheckCircle2 class="w-4 h-4"/> Initial payment settled!</p>
                   <p class="text-xs text-gray-400">You can now pay the remaining balance to fully settle the account.</p>
                </div>

                <div v-if="selectedRequest.raw.payment_term.payment_method === 'gcash'" class="text-center pt-2">
                   <p v-if="selectedRequest.raw.payment_term.status === 'agreed'" class="text-xs text-gray-400 mb-3">You have agreed to the terms. Please proceed with the payment.</p>
                   <Button @click="payWithGcash(selectedRequest.raw.payment_term.id)" :disabled="isPaying" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold h-10 rounded-xl">
                      <span v-if="isPaying" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...</span>
                      <span v-else>
                        {{ selectedRequest.raw.payment_term.total_paid > 0 ? 'Pay Remaining Balance via GCash' : 'Pay via GCash' }}
                      </span>
                   </Button>
                </div>
                
                <div v-if="selectedRequest.raw.payment_term.payment_method === 'on_hand'" class="pt-2">
                   <p v-if="selectedRequest.raw.payment_term.status === 'agreed'" class="text-xs text-gray-400 mb-3">You have agreed to the terms. Please hand the payment physically and upload the receipt/proof here.</p>
                   <input type="file" ref="proofInput" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-yellow-500/10 file:text-yellow-400 hover:file:bg-yellow-500/20 mb-3 cursor-pointer" />
                   
                   <Button @click="uploadProofOfPayment(selectedRequest.raw.payment_term.id)" :disabled="isUploadingProof" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold h-10 rounded-xl">
                      <span v-if="isUploadingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Uploading...</span>
                      <span v-else>
                         {{ selectedRequest.raw.payment_term.total_paid > 0 ? 'Upload Proof for Remaining Balance' : 'Upload Proof of Payment' }}
                      </span>
                   </Button>
                </div>
             </div>

             <div v-else-if="selectedRequest.raw.payment_term.status === 'awaiting_proof_approval'" class="text-center pt-2">
                <div class="bg-blue-900/30 border border-blue-500/30 p-3 rounded-xl">
                   <p class="text-sm font-bold text-blue-400">Proof Uploaded Successfully</p>
                   <p class="text-xs text-gray-300 mt-1">Waiting for the Service Provider to verify your payment.</p>
                   
                   <div v-if="selectedRequest.raw.payment_term.proof_of_payment_url" class="mt-3 w-full h-32 rounded-lg overflow-hidden border border-gray-700">
                      <img :src="selectedRequest.raw.payment_term.proof_of_payment_url" class="w-full h-full object-cover hover:scale-105 transition-transform" />
                   </div>
                </div>
             </div>

             <div v-else-if="selectedRequest.raw.payment_term.status === 'paid' && selectedRequest.raw.payment_term.balance <= 0" class="text-center pt-2">
                <div class="bg-emerald-900/30 border border-emerald-500/30 p-3 rounded-xl flex items-center justify-center gap-2">
                   <CheckCircle2 class="w-5 h-5 text-emerald-400" />
                   <p class="text-sm font-bold text-emerald-400">Fully Paid & Completed!</p>
                </div>
             </div>

             <div v-else-if="selectedRequest.raw.payment_term.status === 'pending'" class="text-center pt-2 text-xs text-gray-500">
                Awaiting your agreement in the chat section.
             </div>
           </div>

           <div v-if="selectedRequest.raw.service_review" class="bg-gradient-to-br from-amber-900/10 to-amber-800/20 border border-amber-800/30 p-5 rounded-2xl shadow-inner">
              <h4 class="text-sm font-bold text-amber-400 mb-2 flex items-center gap-2 uppercase tracking-wider">
                <Star class="w-4 h-4 fill-amber-400" />
                Your Submitted Review
              </h4>
              <div class="flex items-center gap-1 mb-3">
                 <Star v-for="n in 5" :key="n" :class="['w-4 h-4', n <= selectedRequest.raw.service_review.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-600']" />
                 <span class="text-xs font-bold text-amber-500 ml-2 bg-amber-500/10 px-2 py-0.5 rounded-full">{{ selectedRequest.raw.service_review.rating }}.0</span>
              </div>
              <p class="text-sm text-gray-300 italic bg-slate-900/50 p-3 rounded-lg border border-slate-800/50">"{{ selectedRequest.raw.service_review.comment || 'No specific comment provided.' }}"</p>

              <div v-if="selectedRequest.raw.service_review.reply" class="mt-4 bg-blue-900/20 border border-blue-800/50 rounded-lg p-3 relative ml-4">
                 <div class="absolute -left-3 top-3 text-blue-500"><CornerDownRight class="w-4 h-4" /></div>
                 <p class="text-[10px] font-bold text-blue-400 uppercase tracking-wider mb-1">Provider's Response</p>
                 <p class="text-xs text-gray-300">{{ selectedRequest.raw.service_review.reply }}</p>
              </div>

              <div v-if="selectedRequest.raw.service_review.reply && !selectedRequest.raw.service_review.client_reply" class="mt-3 ml-8">
                 <div class="flex flex-col gap-2">
                    <textarea v-model="clientReplyText" rows="2" class="w-full bg-slate-950 border border-slate-700 rounded-lg p-2 text-xs text-white focus:border-amber-500 focus:outline-none" placeholder="Type your final response to the provider..."></textarea>
                    <Button size="sm" class="self-end bg-amber-600 hover:bg-amber-700 text-white h-8 text-xs" :disabled="isSubmittingReply || !clientReplyText.trim()" @click="submitClientReply(selectedRequest.raw.service_review.id)">
                       <span v-if="isSubmittingReply">Sending...</span>
                       <span v-else>Post Reply</span>
                    </Button>
                 </div>
              </div>

              <div v-else-if="selectedRequest.raw.service_review.client_reply" class="mt-3 bg-slate-800/50 border border-slate-700 rounded-lg p-3 relative ml-8">
                 <div class="absolute -left-3 top-3 text-slate-500"><CornerDownRight class="w-4 h-4" /></div>
                 <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Your Reply</p>
                 <p class="text-xs text-gray-300">{{ selectedRequest.raw.service_review.client_reply }}</p>
              </div>
           </div>

           <div v-if="selectedRequest.raw.service_offering" class="bg-gray-800/40 rounded-2xl p-5 border border-gray-700/50 shadow-inner">
             <h4 class="text-sm font-bold text-cyan-400 mb-4 flex items-center gap-2 uppercase tracking-wider">
               <Briefcase class="w-4 h-4" />
               Service Package Info
             </h4>
             
             <div class="flex flex-col sm:flex-row gap-4 mb-4">
               <div v-if="selectedRequest.imageUrl" class="w-full sm:w-32 h-32 rounded-xl overflow-hidden flex-shrink-0 shadow-md border border-gray-700/50">
                 <img :src="selectedRequest.imageUrl" class="w-full h-full object-cover" />
               </div>
               
               <div class="flex-1 space-y-4">
                 <div>
                   <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Service Title</p>
                   <p class="font-semibold text-base text-white">{{ selectedRequest.raw.service_offering.title }}</p>
                 </div>
                 
                 <div class="grid grid-cols-2 gap-4 bg-gray-900/50 p-3 rounded-xl border border-gray-800">
                   <div>
                     <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Category</p>
                     <p class="text-xs text-gray-200 font-medium">{{ selectedRequest.raw.service_offering.category }}</p>
                   </div>
                   <div>
                     <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Base Price</p>
                     <p class="text-xs text-emerald-400 font-bold">
                       ₱{{ Number(selectedRequest.raw.service_offering.price).toLocaleString() }} 
                       <span class="text-gray-400 font-normal uppercase text-[9px]">/ {{ selectedRequest.raw.service_offering.price_type.replace('-', ' ') }}</span>
                     </p>
                   </div>
                 </div>
               </div>
             </div>

             <div class="grid grid-cols-1 mb-4">
               <div class="bg-gray-900/50 p-3 rounded-xl border border-gray-800">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Est. Duration</p>
                 <p class="text-xs text-gray-200 font-medium">{{ selectedRequest.raw.service_offering.duration }}</p>
               </div>
             </div>

             <div v-if="selectedRequest.raw.service_offering.description">
               <p class="text-[10px] text-gray-500 uppercase tracking-wider mb-1.5">Package Description</p>
               <p class="text-xs text-gray-400 leading-relaxed">{{ selectedRequest.raw.service_offering.description }}</p>
             </div>
           </div>

           <div v-else class="bg-gray-800/40 rounded-2xl p-5 border border-gray-700/50">
              <p class="text-sm text-gray-400 italic">Custom Service Request (No predefined package selected)</p>
           </div>

           <div class="space-y-5 px-1">
             <h4 class="text-sm font-bold text-white flex items-center gap-2 border-b border-gray-800 pb-2 uppercase tracking-wider">
               <User class="w-4 h-4 text-blue-400" />
               My Request Specifics
             </h4>

             <div class="space-y-1.5">
               <p class="text-[10px] text-gray-500 uppercase tracking-wider">My Notes / Instructions</p>
               <p class="text-sm bg-gray-950 border border-gray-800 p-4 rounded-xl text-gray-300 leading-relaxed italic">
                 "{{ selectedRequest.description || 'No additional notes provided.' }}"
               </p>
             </div>

             <div class="grid grid-cols-2 gap-x-4 gap-y-5">
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><Calendar class="w-3 h-3 text-gray-400"/> Preferred Date</p>
                 <p class="text-sm text-gray-200 font-semibold">{{ selectedRequest.requestedDate }}</p>
               </div>
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><Clock class="w-3 h-3 text-gray-400"/> Arrival Time</p>
                 <p class="text-sm text-gray-200 font-semibold">{{ selectedRequest.raw.time_preference || 'Flexible' }}</p>
               </div>
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><Phone class="w-3 h-3 text-gray-400"/> Contact</p>
                 <p class="text-sm text-gray-200 font-semibold">{{ selectedRequest.raw.contact_number || 'N/A' }}</p>
               </div>
               <div class="space-y-1">
                 <p class="text-[10px] text-gray-500 uppercase tracking-wider">Status</p>
                 <Badge :class="getStatusClasses(selectedRequest.status)" class="mt-0.5">{{ selectedRequest.statusLabel }}</Badge>
               </div>
             </div>

             <div class="space-y-1.5 pt-3 border-t border-gray-800">
               <p class="text-[10px] text-gray-500 uppercase tracking-wider flex items-center gap-1.5"><MapPin class="w-3 h-3 text-gray-400"/> Complete Address</p>
               <p class="text-sm text-gray-300">{{ selectedRequest.raw.address || 'N/A' }}</p>
             </div>
           </div>
           
           <div class="h-2"></div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showReviewModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[90vw] md:max-w-[450px]">
          <DialogTitle class="text-amber-400 font-bold mb-2 flex items-center gap-2">
             <Star class="w-5 h-5 fill-amber-400" /> Rate Service Provider
          </DialogTitle>
          <div class="py-2 space-y-4">
             <p class="text-sm text-gray-300">How was your experience with the service provided by <strong>{{ jobToReview?.serviceProvider }}</strong>?</p>
             
             <div class="flex flex-col items-center my-6">
                <div class="flex items-center gap-2 mb-2">
                   <button 
                      v-for="n in 5" 
                      :key="n" 
                      @click="reviewForm.rating = n" 
                      class="focus:outline-none hover:scale-110 transition-transform p-1"
                   >
                      <Star :class="['w-10 h-10 transition-colors', n <= reviewForm.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-700']" />
                   </button>
                </div>
                <span class="text-xs text-amber-500 font-bold tracking-wider uppercase">
                   {{ reviewForm.rating === 5 ? 'Excellent!' : reviewForm.rating === 4 ? 'Very Good' : reviewForm.rating === 3 ? 'Average' : reviewForm.rating === 2 ? 'Poor' : 'Terrible' }}
                </span>
             </div>

             <div class="space-y-1">
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Additional Feedback (Optional)</p>
                <textarea v-model="reviewForm.comment" rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-white focus:outline-none focus:border-amber-500/50 shadow-inner" placeholder="Tell us more about what you liked or how they can improve..."></textarea>
             </div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
             <Button variant="ghost" @click="showReviewModal = false" class="text-gray-400 hover:text-white">Cancel</Button>
             <Button @click="submitReview" :disabled="isSubmittingReview" class="bg-amber-500 hover:bg-amber-600 text-white shadow-lg shadow-amber-900/20">
                <span v-if="isSubmittingReview" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Submitting...</span>
                <span v-else>Submit Review</span>
             </Button>
          </div>
       </DialogContent>
    </Dialog>

    <Dialog v-model:open="showRejectModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[90vw] md:max-w-[450px]">
          <DialogTitle class="text-red-400 font-bold mb-2">Reject Work Completion</DialogTitle>
          <div class="py-2 space-y-3">
             <p class="text-sm text-gray-300">Please provide a clear reason why the work is not accepted so the provider can address the issue.</p>
             <textarea v-model="rejectionReason" rows="4" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-white focus:outline-none focus:border-red-500/50" placeholder="e.g. The edge near the window frame is not fully painted..."></textarea>
          </div>
          <div class="flex justify-end gap-2 mt-4">
             <Button variant="ghost" @click="showRejectModal = false" class="text-gray-400 hover:text-white">Cancel</Button>
             <Button @click="rejectCompletion" :disabled="isRejectingWork || !rejectionReason.trim()" class="bg-red-600 hover:bg-red-700 text-white">
                <span v-if="isRejectingWork" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...</span>
                <span v-else>Submit Rejection</span>
             </Button>
          </div>
       </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'

import { Card, CardContent } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Progress } from '@/components/ui/progress'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { 
  Filter, ClipboardList, Zap, Clock, CheckCircle2, User, Eye, MessageSquare, Briefcase, MapPin, Calendar, Phone, CreditCard, Star, CornerDownRight
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

// --- State Management ---
const activeFilter = ref('all')
const serviceRequests = ref([])
const isLoading = ref(true)

const isModalOpen = ref(false)
const selectedRequest = ref(null)

// Payment State
const proofInput = ref(null)
const isUploadingProof = ref(false)
const isPaying = ref(false)

// Work Completion State
const isApprovingWork = ref(false)
const showRejectModal = ref(false)
const rejectionReason = ref('')
const isRejectingWork = ref(false)

// Review State
const showReviewModal = ref(false)
const jobToReview = ref(null)
const reviewForm = ref({ rating: 5, comment: '' })
const isSubmittingReview = ref(false)

// Reply State
const clientReplyText = ref('')
const isSubmittingReply = ref(false)

// --- Data Fetching ---
const fetchRequests = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/client/services/my-requests')
    if (response.data.success) {
      serviceRequests.value = response.data.data.map(req => {
        let progress = 10;
        if (req.status === 'verifying') progress = 30;
        else if (req.status === 'approved') progress = 50;
        else if (req.status === 'ongoing') progress = 70;
        else if (req.status === 'completion_review') progress = 85;
        else if (req.status === 'completed' || req.status === 'rejected') progress = 100;

        const cId = req.id
        const generatedColor = ['#3B82F6', '#10B981', '#6B7280', '#F59E0B', '#8B5CF6', '#059669', '#ec4899'][cId % 7]

        let statusLabel = req.status.charAt(0).toUpperCase() + req.status.slice(1).replace('-', ' ')
        if (req.status === 'completion_review') statusLabel = 'Under Review'

        return {
          id: req.id,
          projectName: req.service_offering ? req.service_offering.title : 'Custom Service Request',
          description: req.description,
          serviceProvider: req.provider ? `${req.provider.first_name} ${req.provider.last_name}` : 'Pending Assignment',
          color: generatedColor,
          colorCode: generatedColor,
          imageUrl: req.service_offering?.image_paths?.length > 0 ? req.service_offering.image_paths[0] : null,
          status: req.status,
          statusLabel: statusLabel,
          date: new Date(req.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
          requestedDate: req.preferred_date || 'N/A',
          currentStageDate: new Date(req.updated_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
          progress: progress,
          raw: req
        }
      })

      // Auto update modal state if it's open
      if (selectedRequest.value) {
         const updatedReq = serviceRequests.value.find(r => r.id === selectedRequest.value.id)
         if(updatedReq) selectedRequest.value = updatedReq
      }
    }
  } catch (error) {
    console.error('Failed to load requests:', error)
  } finally {
    isLoading.value = false
  }
}

// --- Payment Actions ---
const uploadProofOfPayment = async (termId) => {
  if (!proofInput.value || !proofInput.value.files[0]) {
    toast.error("Please select an image file first.")
    return
  }
  
  const formData = new FormData()
  formData.append('proof_image', proofInput.value.files[0])

  isUploadingProof.value = true
  try {
    const res = await api.post(`/client/services/payment-terms/${termId}/upload-proof`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    if(res.data.success) {
      toast.success(res.data.message)
      await fetchRequests() // re-fetch to update statuses
    }
  } catch(error) {
    toast.error(error.response?.data?.message || "Failed to upload proof.")
  } finally {
    isUploadingProof.value = false
    if(proofInput.value) proofInput.value.value = null // clear input
  }
}

const payWithGcash = async (termId) => {
  isPaying.value = true
  try {
    const res = await api.post(`/client/services/payment-terms/${termId}/pay-gcash`)
    if(res.data.success) {
      window.location.href = res.data.checkout_url
    }
  } catch (error) {
    toast.error("Failed to generate GCash link.")
    isPaying.value = false
  }
}

const verifyServicePayment = async (termId) => {
  try {
    const res = await api.post('/client/services/payment-terms/verify-gcash', { term_id: termId })
    if (res.data.success) {
      toast.success(res.data.message || "Payment completed successfully!")
      fetchRequests() 
      router.replace({ query: {} }) // remove query string
    }
  } catch(error) {
    toast.error("Error verifying payment or payment is not complete.")
  }
}

// --- Completion Actions ---
const approveCompletion = async () => {
   if(!selectedRequest.value) return
   isApprovingWork.value = true
   try {
      const res = await api.post(`/client/services/requests/${selectedRequest.value.id}/approve-completion`)
      if (res.data.success) {
         toast.success(res.data.message)
         isModalOpen.value = false
         fetchRequests()
      }
   } catch (error) {
      toast.error(error.response?.data?.message || "Failed to approve completion.")
   } finally {
      isApprovingWork.value = false
   }
}

const openRejectModal = () => {
   rejectionReason.value = ''
   showRejectModal.value = true
}

const rejectCompletion = async () => {
   if(!selectedRequest.value || !rejectionReason.value.trim()) return
   isRejectingWork.value = true
   try {
      const res = await api.post(`/client/services/requests/${selectedRequest.value.id}/reject-completion`, {
         rejection_reason: rejectionReason.value
      })
      if (res.data.success) {
         toast.success(res.data.message)
         showRejectModal.value = false
         isModalOpen.value = false
         fetchRequests()
      }
   } catch (error) {
      toast.error(error.response?.data?.message || "Failed to reject completion.")
   } finally {
      isRejectingWork.value = false
   }
}

// --- Review Actions ---
const openReviewModal = (request) => {
  jobToReview.value = request
  reviewForm.value = { rating: 5, comment: '' }
  showReviewModal.value = true
}

const submitReview = async () => {
  if (!jobToReview.value) return
  isSubmittingReview.value = true
  try {
    const res = await api.post(`/client/services/requests/${jobToReview.value.id}/review`, reviewForm.value)
    if (res.data.success) {
      toast.success(res.data.message)
      showReviewModal.value = false
      fetchRequests()
    }
  } catch (error) {
    toast.error(error.response?.data?.message || "Failed to submit review.")
  } finally {
    isSubmittingReview.value = false
  }
}

const submitClientReply = async (reviewId) => {
  if (!clientReplyText.value.trim() || !reviewId) return;
  
  isSubmittingReply.value = true;
  try {
     const response = await api.post(`/client/services/reviews/${reviewId}/reply`, {
        client_reply: clientReplyText.value
     });

     if (response.data.success) {
        toast.success('Reply submitted successfully!');
        selectedRequest.value.raw.service_review.client_reply = clientReplyText.value;
        clientReplyText.value = '';
        fetchRequests(); // Reload data
     }
  } catch (error) {
     toast.error(error.response?.data?.message || 'Failed to submit reply');
  } finally {
     isSubmittingReply.value = false;
  }
}


onMounted(async () => {
  await fetchRequests()
  
  if (route.query.service_payment_term_id) {
     verifyServicePayment(route.query.service_payment_term_id)
  }
})

// --- Computeds ---
const statusCounts = computed(() => {
  return serviceRequests.value.reduce((acc, request) => {
    acc[request.status] = (acc[request.status] || 0) + 1
    return acc
  }, {})
})

const filteredRequests = computed(() => {
  if (activeFilter.value === 'all') return serviceRequests.value
  return serviceRequests.value.filter(request => request.status === activeFilter.value)
})

const statsCards = computed(() => [
  { 
    label: 'Total Requests', 
    value: serviceRequests.value.length, 
    colorClass: 'text-white', 
    bgClass: 'bg-blue-500/10', 
    iconClass: 'text-blue-400', 
    icon: ClipboardList 
  },
  { 
    label: 'Active', 
    value: statusCounts.value['ongoing'] || 0, 
    colorClass: 'text-cyan-300', 
    bgClass: 'bg-cyan-500/10', 
    iconClass: 'text-cyan-400', 
    icon: Zap 
  },
  { 
    label: 'Review/Pending', 
    value: (statusCounts.value['pending'] || 0) + (statusCounts.value['verifying'] || 0) + (statusCounts.value['completion_review'] || 0), 
    colorClass: 'text-amber-300', 
    bgClass: 'bg-amber-500/10', 
    iconClass: 'text-amber-400', 
    icon: Clock 
  },
  { 
    label: 'Completed', 
    value: statusCounts.value['completed'] || 0, 
    colorClass: 'text-emerald-300', 
    bgClass: 'bg-emerald-500/10', 
    iconClass: 'text-emerald-400', 
    icon: CheckCircle2 
  }
])

// --- Helper Methods ---
const getStatusClasses = (status) => {
  const classes = {
    'pending': 'bg-amber-500/10 text-amber-400 border-amber-500/30',
    'verifying': 'bg-blue-500/10 text-blue-400 border-blue-500/30',
    'ongoing': 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
    'completion_review': 'bg-pink-500/10 text-pink-400 border-pink-500/30',
    'completed': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
    'rejected': 'bg-red-500/10 text-red-400 border-red-500/30'
  }
  return classes[status] || 'bg-gray-500/10 text-gray-400 border-gray-500/30'
}

const getIndicatorColor = (status) => {
  if (status === 'pending') return 'bg-amber-500'
  if (status === 'verifying') return 'bg-blue-500'
  if (status === 'ongoing') return 'bg-cyan-500'
  if (status === 'completion_review') return 'bg-pink-500'
  if (status === 'rejected') return 'bg-red-500'
  return 'bg-emerald-500'
}

const getStatusTextColor = (status) => {
  if (status === 'pending') return 'text-amber-400'
  if (status === 'verifying') return 'text-blue-400'
  if (status === 'ongoing') return 'text-cyan-400'
  if (status === 'completion_review') return 'text-pink-400'
  if (status === 'rejected') return 'text-red-400'
  return 'text-emerald-400'
}

const getProgressBarTheme = (status) => {
  if (status === 'pending') return '[&>div]:bg-amber-500'
  if (status === 'verifying') return '[&>div]:bg-blue-500'
  if (status === 'ongoing') return '[&>div]:bg-cyan-500'
  if (status === 'completion_review') return '[&>div]:bg-pink-500'
  if (status === 'rejected') return '[&>div]:bg-red-500'
  return '[&>div]:bg-emerald-500'
}

const getStatusMessage = (status) => {
  const messages = {
    'pending': 'Awaiting confirmation',
    'verifying': 'Awaiting official deal',
    'ongoing': 'Ongoing project',
    'completion_review': 'Review submitted work',
    'completed': 'Successfully completed',
    'rejected': 'Request declined'
  }
  return messages[status] || 'Status unknown'
}

const viewDetails = (request) => {
  selectedRequest.value = request
  isModalOpen.value = true
}

const goToChat = () => {
  router.push('/Clients/ClientChat')
}
</script>

<style scoped>
* { transition: all 0.2s ease-in-out; }
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(30, 41, 59, 0.3); border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #38bdf8, #0ea5e9); border-radius: 3px; }

.animate-pulse { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
.animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .5; } }
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>