<template>
  <div class="min-h-screen text-slate-200 p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Service Jobs & Requests</h1>
          <p class="text-gray-400 text-sm md:text-base">Manage client painting projects and service requests</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 md:space-x-4 md:gap-0">
          
          <Button @click="openGcashModal" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold shadow-lg shadow-blue-900/20">
             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
             View GCash Wallet
          </Button>

          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" class="w-full sm:w-auto justify-between sm:justify-center bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">
                <span class="flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                  Filter: {{ activeFilter.label }}
                </span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="bg-slate-900 border-slate-700 text-slate-300 w-[var(--radix-dropdown-menu-trigger-width)] sm:w-auto">
               <DropdownMenuItem v-for="filter in filters" :key="filter.value" @click="activeFilter = filter" class="focus:bg-slate-800 cursor-pointer justify-between">
                  {{ filter.label }}
                  <span v-if="activeFilter.value === filter.value" class="text-emerald-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></span>
               </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          
        </div>
      </div>
      
      <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <Card class="bg-blue-900/20 border-blue-800/50">
           <CardContent class="p-3 md:p-4 flex items-center">
              <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
              </div>
              <div>
                 <p class="text-gray-400 text-xs md:text-sm">Total Jobs</p>
                 <p class="text-xl md:text-2xl font-bold text-white">{{ jobs.length }}</p>
              </div>
           </CardContent>
        </Card>
        <Card class="bg-amber-900/20 border-amber-800/50">
           <CardContent class="p-3 md:p-4 flex items-center">
              <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
              <div>
                 <p class="text-gray-400 text-xs md:text-sm">Pending</p>
                 <p class="text-xl md:text-2xl font-bold text-white">
                   {{ jobs.filter(j => j.status === 'pending').length }}
                 </p>
              </div>
           </CardContent>
        </Card>
      </div>
    </div>

    <div class="md:hidden space-y-4 mb-8">
      <div v-for="job in filteredJobs" :key="job.id" class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
        <div class="flex justify-between items-start mb-3">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-blue-900/20 border border-blue-800/50 flex items-center justify-center mr-2 text-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <div>
              <p class="text-white font-medium text-sm">#{{ job.id }}</p>
              <p class="text-gray-400 text-xs">{{ job.date }}</p>
            </div>
          </div>
          <Badge variant="outline" :class="[
            'text-xs px-2 py-0.5',
            job.status === 'pending' ? 'border-amber-500/30 text-amber-500 bg-amber-500/10' : 
            job.status === 'verifying' ? 'border-purple-500/30 text-purple-500 bg-purple-500/10' : 
            job.status === 'ongoing' ? 'border-blue-500/30 text-blue-500 bg-blue-500/10' : 
            job.status === 'completion_review' ? 'border-pink-500/30 text-pink-500 bg-pink-500/10' :
            job.status === 'completed' ? 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10' : 'border-red-500/30 text-red-500 bg-red-500/10'
          ]">
            {{ getCustomStatusText(job) }}
          </Badge>
        </div>
        
        <div class="space-y-3 mb-4">
          <div class="flex items-center justify-between border-b border-slate-800/50 pb-2">
            <div class="flex items-center">
              <Avatar class="h-6 w-6 mr-2 bg-gradient-to-br from-blue-500 to-purple-500">
                  <AvatarFallback class="bg-transparent text-white text-[10px] font-bold">{{ getInitials(job.client) }}</AvatarFallback>
              </Avatar>
              <span class="text-slate-300 text-sm">{{ job.client }}</span>
            </div>
            <span class="text-slate-500 text-xs truncate max-w-[120px]">{{ job.location }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-slate-400 text-xs">Service:</span>
            <div class="flex items-center">
              <span class="text-slate-300 text-sm mr-2">{{ job.serviceDetails.title }}</span>
              <span class="text-slate-500 text-[10px] uppercase">({{ job.serviceDetails.category }})</span>
            </div>
          </div>
          
          <div class="flex items-center justify-between border-b border-slate-800/50 pb-2">
            <span class="text-slate-400 text-xs">Time:</span>
            <span class="text-slate-300 text-sm">{{ job.paintBrand }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-slate-400 text-xs">Payment:</span>
            <div class="text-right">
               <span class="text-emerald-400 font-bold text-xs">₱{{ parseFloat(job.serviceDetails.price).toLocaleString() }}</span>
               <span class="text-slate-400 text-[10px] block mt-0.5">{{ job.paymentTerm }}</span>
               <span class="text-slate-500 text-[9px] block uppercase">{{ job.paymentStatus.replace('_', ' ') }}</span>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-800">
           <Button class="flex-1 bg-slate-800 text-slate-300 hover:text-white border-slate-700 h-9 text-xs" variant="outline" @click="viewJobDetails(job)">
             Details
           </Button>
           <Button v-if="job.status === 'verifying' || job.status === 'ongoing' || job.status === 'completion_review'" class="flex-1 bg-emerald-900/20 text-emerald-400 hover:bg-emerald-900/40 border-emerald-800/30 h-9 text-xs" variant="outline" @click="goToChat(job)">
             Message
           </Button>
           <Button v-if="job.status === 'ongoing'" class="w-full bg-blue-600 hover:bg-blue-700 text-white border-blue-700 h-9 text-xs mt-2" @click="promptCompleteJob(job)">
             Mark as Complete
           </Button>
        </div>
      </div>
    </div>

    <div class="hidden md:block bg-slate-900 rounded-xl border border-slate-800 overflow-hidden mb-8">
      <Table>
        <TableHeader class="bg-slate-950">
          <TableRow class="border-slate-800 hover:bg-transparent">
            <TableHead class="text-slate-400">Job ID</TableHead>
            <TableHead class="text-slate-400">Client</TableHead>
            <TableHead class="text-slate-400">Service Category</TableHead>
            <TableHead class="text-slate-400">Time / Contact</TableHead>
            <TableHead class="text-slate-400">Payment Terms</TableHead>
            <TableHead class="text-slate-400">Status</TableHead>
            <TableHead class="text-slate-400">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="job in filteredJobs" :key="job.id" class="border-slate-800 hover:bg-slate-800/50">
            <TableCell>
               <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-blue-900/20 border border-blue-800/50 flex items-center justify-center mr-3 text-blue-400">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                  </div>
                  <div>
                     <p class="text-white font-medium">#{{ job.id }}</p>
                     <p class="text-gray-400 text-xs">{{ job.date }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <Avatar class="bg-gradient-to-br from-blue-500 to-purple-500 border-0 h-9 w-9 mr-3">
                     <AvatarFallback class="bg-transparent text-white text-xs font-bold">{{ getInitials(job.client) }}</AvatarFallback>
                  </Avatar>
                  <div>
                     <p class="text-white font-medium">{{ job.client }}</p>
                     <p class="text-gray-400 text-xs truncate max-w-[150px]">{{ job.location }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg bg-indigo-900/20 border border-indigo-800/50 flex items-center justify-center mr-3 text-indigo-400">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                  </div>
                  <div>
                     <p class="text-white font-medium">{{ job.serviceDetails.title }}</p>
                     <p class="text-gray-400 text-xs">{{ job.serviceDetails.category }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center mr-3 text-slate-400">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  </div>
                  <div>
                     <p class="text-white font-medium">{{ job.paintBrand }}</p>
                     <p class="text-gray-400 text-xs">{{ job.paintType }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div>
                  <p class="text-emerald-400 font-bold text-sm">₱{{ parseFloat(job.serviceDetails.price).toLocaleString() }}</p>
                  <p class="text-white font-medium text-xs">{{ job.paymentTerm }}</p>
                  <p class="text-gray-400 text-[10px] uppercase font-semibold mt-0.5">{{ job.paymentStatus.replace('_', ' ') }}</p>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div :class="['w-2 h-2 rounded-full mr-2', 
                     job.status === 'pending' ? 'bg-amber-500' : 
                     job.status === 'verifying' ? 'bg-purple-500' :
                     job.status === 'ongoing' ? 'bg-blue-500' : 
                     job.status === 'completion_review' ? 'bg-pink-500' :
                     job.status === 'completed' ? 'bg-emerald-500' : 'bg-red-500']"></div>
                  <Badge variant="outline" :class="[
                     job.status === 'pending' ? 'border-amber-500/30 text-amber-500 bg-amber-500/10' : 
                     job.status === 'verifying' ? 'border-purple-500/30 text-purple-500 bg-purple-500/10' : 
                     job.status === 'ongoing' ? 'border-blue-500/30 text-blue-500 bg-blue-500/10' : 
                     job.status === 'completion_review' ? 'border-pink-500/30 text-pink-500 bg-pink-500/10' :
                     job.status === 'completed' ? 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10' : 'border-red-500/30 text-red-500 bg-red-500/10'
                  ]">
                     {{ getCustomStatusText(job) }}
                  </Badge>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center gap-2">
                  <Button size="icon" variant="ghost" class="h-8 w-8 bg-blue-900/20 text-blue-400 hover:bg-blue-900/40 hover:text-blue-300 border border-blue-800/30" @click="viewJobDetails(job)" title="View Details">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </Button>
                  
                  <Button v-if="job.status === 'verifying' || job.status === 'ongoing' || job.status === 'completion_review'" size="icon" variant="ghost" class="h-8 w-8 bg-emerald-900/20 text-emerald-400 hover:bg-emerald-900/40 hover:text-emerald-300 border border-emerald-800/30" @click="goToChat(job)" title="Message Client">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                  </Button>

                  <Button v-if="job.status === 'ongoing'" size="icon" variant="ghost" class="h-8 w-8 bg-indigo-900/20 text-indigo-400 hover:bg-indigo-900/40 hover:text-indigo-300 border border-indigo-800/30" @click="promptCompleteJob(job)" title="Mark as Complete">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  </Button>
               </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <Dialog v-model:open="showDetailsModal">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[95vw] max-w-[95vw] md:max-w-[700px] max-h-[90vh] overflow-y-auto custom-scrollbar">
        <DialogHeader>
          <DialogTitle>Job Request Details</DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedJob" class="py-4 space-y-6">

          <div v-if="selectedJob.originalData.latest_completion && selectedJob.originalData.latest_completion.status === 'rejected'" class="bg-red-900/20 border border-red-800/50 p-4 rounded-xl">
             <h4 class="text-sm font-bold text-red-400 flex items-center gap-2 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Client rejected the previous completion proof
             </h4>
             <p class="text-gray-300 text-sm italic border-l-2 border-red-500/50 pl-3">"{{ selectedJob.originalData.latest_completion.rejection_reason }}"</p>
          </div>

          <div v-if="selectedJob.status === 'completion_review'" class="bg-blue-900/20 border border-blue-800/50 p-4 rounded-xl">
             <h4 class="text-sm font-bold text-blue-400 flex items-center gap-2 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Waiting for Client Approval
             </h4>
             <p class="text-gray-300 text-sm mb-3">You have submitted proof that this job is completed. The client is currently reviewing your submission.</p>
             <div class="flex gap-2 overflow-x-auto pb-2 custom-scrollbar">
                <div v-for="(img, idx) in selectedJob.originalData.latest_completion?.proof_images_url" :key="idx" class="w-24 h-24 rounded-lg overflow-hidden flex-shrink-0 border border-slate-700">
                   <img :src="img" class="w-full h-full object-cover hover:scale-105 transition-transform" />
                </div>
             </div>
          </div>

          <div v-if="selectedJob.status === 'pending' && selectedJob.originalData.survey_agreement" class="bg-indigo-900/20 border border-indigo-800/50 p-4 rounded-xl">
             <h4 class="text-sm font-bold text-indigo-400 flex items-center gap-2 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Survey Agreement Status
             </h4>
             <p class="text-gray-300 text-sm">
                <span v-if="selectedJob.originalData.survey_agreement.status === 'pending_client'">Agreement created. Waiting for client's formal signature.</span>
                <span v-else-if="selectedJob.originalData.survey_agreement.status === 'signed'">Client has signed the agreement! You may now proceed to survey the area.</span>
                <span v-else-if="selectedJob.originalData.survey_agreement.status === 'in_progress'">Survey currently in progress. Please log your measurements.</span>
                <span v-else-if="selectedJob.originalData.survey_agreement.status === 'completed'">Survey Phase completed successfully. Ready for formal Approval.</span>
             </p>
             
             <div v-if="selectedJob.originalData.survey_agreement.status !== 'pending_client'" class="mt-4 pt-3 border-t border-indigo-800/30 grid grid-cols-2 gap-4">
                <div>
                   <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Your Signature</p>
                   <img :src="selectedJob.originalData.survey_agreement.provider_signature_url" class="h-10 invert opacity-80" />
                </div>
                <div>
                   <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Client Signature</p>
                   <img :src="selectedJob.originalData.survey_agreement.client_signature_url" class="h-10 invert opacity-80" />
                </div>
             </div>

             <Button @click="showViewAgreementModal = true" size="sm" class="mt-4 w-full bg-slate-800 hover:bg-slate-700 text-white border border-slate-700 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                View Full Agreement Document
             </Button>

             <div v-if="selectedJob.originalData.survey_agreement.status === 'signed'" class="mt-4">
                 <Button @click="handleSurveyAction('start')" :disabled="isSurveyProcessing" class="w-full h-12 text-base font-bold bg-cyan-600 hover:bg-cyan-700 text-white shadow-lg shadow-cyan-900/20 transition-all hover:scale-[1.02]">
                     <span v-if="isSurveyProcessing" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...</span>
                     <span v-else class="flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Start Survey</span>
                 </Button>
             </div>
             <div v-else-if="selectedJob.originalData.survey_agreement.status === 'in_progress'" class="mt-4">
                 <Button @click="handleSurveyAction('complete')" :disabled="isSurveyProcessing" class="w-full h-12 text-base font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-900/20 transition-all hover:scale-[1.02]">
                     <span v-if="isSurveyProcessing" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...</span>
                     <span v-else class="flex items-center justify-center gap-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> End & Complete Survey</span>
                 </Button>
             </div>
          </div>

          <div class="grid grid-cols-2 gap-5">
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Client Name</p>
                <p class="text-white font-medium">{{ selectedJob.client }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Status</p>
                <Badge variant="outline" :class="[
                     selectedJob.status === 'pending' ? 'border-amber-500/30 text-amber-500 bg-amber-500/10' : 
                     selectedJob.status === 'verifying' ? 'border-purple-500/30 text-purple-500 bg-purple-500/10' : 
                     selectedJob.status === 'ongoing' ? 'border-blue-500/30 text-blue-500 bg-blue-500/10' : 
                     selectedJob.status === 'completion_review' ? 'border-pink-500/30 text-pink-500 bg-pink-500/10' :
                     selectedJob.status === 'completed' ? 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10' : 'border-red-500/30 text-red-500 bg-red-500/10'
                  ]">
                     {{ getCustomStatusText(selectedJob) }}
                  </Badge>
             </div>
             
             <div v-if="selectedJob.originalData.official_deal" class="col-span-2 border border-blue-800/50 bg-blue-900/10 p-4 rounded-xl mt-2">
                 <h4 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Official Deal & Payment Details
                 </h4>
                 <div class="grid grid-cols-2 gap-4">
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Agreed Final Price</p>
                        <p class="text-emerald-400 font-bold text-lg">₱{{ parseFloat(selectedJob.originalData.official_deal.price).toLocaleString() }}</p>
                     </div>
                     <div v-if="selectedJob.originalData.payment_term">
                        <p class="text-slate-400 text-xs uppercase mb-1">Payment Method & Term</p>
                        <p class="text-white text-sm uppercase font-semibold">{{ selectedJob.originalData.payment_term.payment_method.replace('_', ' ') }}</p>
                        <p class="text-gray-300 text-xs mt-0.5">{{ selectedJob.originalData.payment_term.payment_term }}</p>
                     </div>
                 </div>

                 <div v-if="selectedJob.originalData.payment_term" class="grid grid-cols-2 gap-4 bg-slate-950 p-3 rounded-xl border border-slate-800 mb-4 mt-3">
                   <div>
                     <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total Paid By Client</p>
                     <p class="text-sm text-emerald-400 font-bold tracking-tight">₱{{ Number(selectedJob.originalData.payment_term.total_paid || 0).toLocaleString() }}</p>
                   </div>
                   <div>
                     <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Remaining Balance</p>
                     <p class="text-sm text-red-400 font-bold tracking-tight">₱{{ Number(selectedJob.originalData.payment_term.balance || 0).toLocaleString() }}</p>
                   </div>
                 </div>

                 <div v-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.payment_method === 'on_hand' && selectedJob.originalData.payment_term.status === 'awaiting_proof_approval'" class="mt-4 border-t border-blue-800/30 pt-4">
                    <p class="text-yellow-400 text-sm font-bold mb-2">Client Uploaded Proof of Payment</p>
                    <div class="w-full max-w-[200px] rounded-lg overflow-hidden border border-slate-700 mb-3">
                       <img :src="selectedJob.originalData.payment_term.proof_of_payment_url" class="w-full h-auto object-cover" />
                    </div>
                    <Button @click="approveProof(selectedJob.originalData.payment_term.id)" :disabled="isApprovingProof" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-9">
                       <span v-if="isApprovingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Approving...</span>
                       <span v-else>Approve & Verify Payment</span>
                    </Button>
                 </div>

                 <div v-else-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.status === 'paid' && selectedJob.originalData.payment_term.balance <= 0" class="mt-4 border-t border-blue-800/30 pt-3">
                    <Badge class="bg-emerald-500/20 text-emerald-400 border-emerald-500/30 px-3 py-1">Fully Paid & Completed</Badge>
                 </div>
                 <div v-else-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.status === 'paid' && selectedJob.originalData.payment_term.balance > 0" class="mt-4 border-t border-blue-800/30 pt-3">
                    <Badge class="bg-blue-500/20 text-blue-400 border-blue-500/30 px-3 py-1">Initial Payment Verified - Pending Balance</Badge>
                 </div>
                 <div v-else-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.status !== 'pending' && selectedJob.originalData.payment_term.status !== 'agreed'" class="mt-4 border-t border-blue-800/30 pt-3">
                    <p class="text-slate-400 text-xs">Payment Status: {{ selectedJob.originalData.payment_term.status.replace('_', ' ') }}</p>
                 </div>
                 
                 <div v-if="selectedJob.status === 'completed' && selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.balance > 0" class="mt-4 border-t border-blue-800/30 pt-4">
                     <p class="text-red-400 text-sm font-bold mb-2">Unpaid Balance Action</p>
                     <p class="text-xs text-gray-300 mb-3">Client has not fully paid. Reminders sent: <span class="font-bold">{{ selectedJob.originalData.payment_term.reminder_count || 0 }}</span>/3</p>

                     <div class="flex gap-2 flex-wrap">
                        <Button @click="sendReminder(selectedJob.originalData.payment_term.id)" :disabled="isSendingReminder || (selectedJob.originalData.payment_term.reminder_count >= 3)" class="bg-amber-600 hover:bg-amber-700 text-white text-xs h-9">
                           <span v-if="isSendingReminder" class="flex items-center"><div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-2"></div> Sending...</span>
                           <span v-else>Send Email Reminder</span>
                        </Button>

                        <Button v-if="selectedJob.originalData.payment_term.reminder_count >= 3 && !selectedJob.originalData.payment_term.legal_report_path" @click="generateReport(selectedJob.originalData.payment_term.id)" :disabled="isGeneratingReport" class="bg-red-600 hover:bg-red-700 text-white text-xs h-9 shadow-lg shadow-red-900/20">
                           <span v-if="isGeneratingReport" class="flex items-center"><div class="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-2"></div> Generating...</span>
                           <span v-else>Generate Legal Report</span>
                        </Button>
                        
                        <a v-if="selectedJob.originalData.payment_term.legal_report_path" :href="selectedJob.originalData.payment_term.legal_report_path" target="_blank" class="inline-flex items-center justify-center rounded-md text-xs font-bold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 ring-offset-slate-900 bg-red-900/50 text-red-400 hover:bg-red-900/80 border border-red-800/50 h-9 px-4">
                           <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                           Download Legal Report (PDF)
                        </a>
                     </div>
                 </div>

             </div>

             <div class="col-span-2 border-t border-slate-800 pt-4 mt-2">
                 <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-wider mb-4">Service Offering Details</h4>
                 <div class="grid grid-cols-2 gap-4">
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Service Category</p>
                        <p class="text-white">{{ selectedJob.serviceDetails.category }}</p>
                     </div>
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Service Title</p>
                        <p class="text-white">{{ selectedJob.serviceDetails.title }}</p>
                     </div>
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Service Rate</p>
                        <p class="text-white">₱{{ parseFloat(selectedJob.serviceDetails.price).toLocaleString() }} / {{ selectedJob.serviceDetails.price_type || 'N/A' }}</p>
                     </div>
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Est. Duration</p>
                        <p class="text-white">{{ selectedJob.serviceDetails.duration }}</p>
                     </div>
                 </div>
             </div>

             <div class="col-span-2 border-t border-slate-800 pt-4 mt-2">
                 <h4 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-4">Client Initial Request Info</h4>
             </div>
             <div class="col-span-2">
                <p class="text-slate-400 text-xs uppercase mb-1">Complete Location</p>
                <p class="text-white bg-slate-950 p-2 rounded-lg border border-slate-800 text-sm">{{ selectedJob.location }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Preferred Date</p>
                <p class="text-white">{{ selectedJob.date }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Time Preference</p>
                <p class="text-white">{{ selectedJob.paintBrand }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Contact Details</p>
                <p class="text-white">{{ selectedJob.paintType }}</p>
             </div>
             <div class="col-span-2 mt-2">
                <p class="text-slate-400 text-xs uppercase mb-1">Client Description / Notes</p>
                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800 text-sm whitespace-pre-wrap leading-relaxed">
                   {{ selectedJob.serviceDetails.description || 'No additional description provided.' }}
                </div>
             </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800 mt-4 px-6 pb-6">
           <template v-if="selectedJob && selectedJob.status === 'pending'">
             <Button variant="outline" class="bg-red-600/20 text-red-500 hover:bg-red-600/40 hover:text-red-400 border border-red-800/30" @click="promptRejectJob">Reject Request</Button>
             
             <Button v-if="!selectedJob.originalData.survey_agreement" class="bg-indigo-600 hover:bg-indigo-700 text-white" @click="openGenerateModal">
                Generate Survey Agreement
             </Button>

             <Button v-else-if="selectedJob.originalData.survey_agreement.status === 'completed'" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 border-0 text-white shadow-lg shadow-emerald-600/20" @click="promptApproveJob">
                Officially Approve Request
             </Button>

           </template>
           <template v-if="selectedJob && selectedJob.status === 'ongoing'">
             <Button class="bg-blue-600 hover:bg-blue-700 text-white" @click="promptCompleteJob(selectedJob)">Submit Proof of Completion</Button>
           </template>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showGenerateAgreementModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[90vw] md:max-w-[500px]" @opened="initCanvas">
          <DialogTitle class="text-indigo-400 font-bold mb-2 flex items-center gap-2">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg> 
             Sign & Generate Agreement
          </DialogTitle>
          <div class="py-2 space-y-4">
             <p class="text-sm text-gray-300">Please review the generated agreement text and provide your official signature to bind the Survey Agreement.</p>
             
             <div class="bg-slate-950 border border-slate-700 rounded-xl p-4 max-h-[180px] overflow-y-auto whitespace-pre-wrap text-[11px] text-gray-400 font-mono shadow-inner custom-scrollbar">
                {{ agreementPreviewText }}
             </div>
             
             <div class="space-y-2">
                <p class="text-[10px] uppercase text-gray-500 font-bold tracking-widest">Draw Your Signature Below</p>
                <div class="border border-slate-700 bg-slate-950 rounded-xl overflow-hidden touch-none relative">
                   <canvas 
                      ref="signaturePad" 
                      width="400" 
                      height="150" 
                      class="w-full h-[150px] cursor-crosshair touch-none"
                      @mousedown="startDrawing" 
                      @mousemove="draw" 
                      @mouseup="stopDrawing" 
                      @mouseleave="stopDrawing"
                      @touchstart="startDrawing" 
                      @touchmove="draw" 
                      @touchend="stopDrawing"
                   ></canvas>
                   <Button variant="ghost" size="sm" @click="clearSignature" class="absolute bottom-2 right-2 h-7 text-xs bg-slate-800/80 text-gray-400 hover:text-white">Clear</Button>
                </div>
             </div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
             <Button variant="ghost" @click="showGenerateAgreementModal = false" class="text-gray-400 hover:text-white">Cancel</Button>
             <Button @click="generateSurveyAgreement" :disabled="isGeneratingAgreement" class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-900/20">
                <span v-if="isGeneratingAgreement" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Generating...</span>
                <span v-else>Confirm & Send</span>
             </Button>
          </div>
       </DialogContent>
    </Dialog>

    <Dialog v-model:open="showViewAgreementModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[90vw] md:max-w-[500px]">
          <DialogTitle class="text-indigo-400 font-bold mb-2 flex items-center gap-2">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
             View Survey Agreement
          </DialogTitle>
          <div class="py-2 space-y-4">
             <div class="bg-slate-950 border border-slate-700 rounded-xl p-4 max-h-[350px] overflow-y-auto whitespace-pre-wrap text-xs text-gray-300 font-mono shadow-inner custom-scrollbar">
                {{ selectedJob?.originalData?.survey_agreement?.agreement_text || 'Loading agreement...' }}
                
                <div class="mt-6 pt-4 border-t border-slate-800 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] text-gray-500 mb-1">Provider's Signature:</p>
                        <img v-if="selectedJob?.originalData?.survey_agreement?.provider_signature_url" :src="selectedJob?.originalData?.survey_agreement?.provider_signature_url" class="h-12 invert opacity-80" />
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-500 mb-1">Client's Signature:</p>
                        <img v-if="selectedJob?.originalData?.survey_agreement?.client_signature_url" :src="selectedJob?.originalData?.survey_agreement?.client_signature_url" class="h-12 invert opacity-80" />
                        <p v-else class="text-[10px] text-amber-500 italic mt-2">Pending Client Signature</p>
                    </div>
                </div>
             </div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
             <Button variant="ghost" @click="showViewAgreementModal = false" class="text-gray-400 hover:text-white">Close</Button>
             
             <Button @click="downloadAgreement" class="bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-900/20">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Download PDF
             </Button>
          </div>
       </DialogContent>
    </Dialog>


    <Dialog v-model:open="showCompleteModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[95vw] md:max-w-[500px]">
          <DialogHeader>
             <DialogTitle>Mark Job as Complete</DialogTitle>
          </DialogHeader>
          <div class="py-4">
             <p class="text-sm text-gray-400 mb-4">Please upload images showing the completed work. The client will review these to finalize the job.</p>
             <input type="file" ref="completionProofInput" multiple accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20 mb-3 cursor-pointer" />
          </div>
          <div class="flex justify-end gap-3 mt-4">
             <Button variant="ghost" @click="showCompleteModal = false" class="text-gray-400 hover:text-white">Cancel</Button>
             <Button @click="submitCompletion" :disabled="isCompleting" class="bg-blue-600 hover:bg-blue-700 text-white">
                <span v-if="isCompleting" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Uploading...</span>
                <span v-else>Submit & Request Approval</span>
             </Button>
          </div>
       </DialogContent>
    </Dialog>

    <Dialog v-model:open="showGcashModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 sm:max-w-[400px]">
          <DialogHeader>
             <DialogTitle class="flex items-center gap-2 text-blue-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                GCash Wallet Overview
             </DialogTitle>
          </DialogHeader>
          <div class="py-4 space-y-4" v-if="gcashDetails">
             <div class="bg-blue-900/20 border border-blue-800/50 rounded-xl p-5 text-center">
                <p class="text-slate-400 text-sm mb-1 uppercase tracking-wider">Total GCash Earnings</p>
                <p class="text-3xl font-bold text-white tracking-tight">₱{{ gcashDetails.total_earnings.toLocaleString() }}</p>
             </div>
             <div class="bg-slate-950 border border-slate-800 p-4 rounded-xl space-y-3">
                <div>
                   <p class="text-xs text-slate-500 uppercase">Registered GCash Number</p>
                   <p class="text-slate-200 font-medium text-lg">{{ gcashDetails.gcash_number }}</p>
                </div>
                <div>
                   <p class="text-xs text-slate-500 uppercase">Registered Account Name</p>
                   <p class="text-slate-200 font-medium">{{ gcashDetails.gcash_name }}</p>
                </div>
             </div>
             <p class="text-xs text-slate-500 text-center">These details are synced with your Payment Settings.</p>
          </div>
          <div v-else class="py-8 flex justify-center">
             <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          </div>
       </DialogContent>
    </Dialog>

    <AlertDialog v-model:open="showConfirmDialog">
      <AlertDialogContent class="bg-slate-900 border-slate-800 text-slate-200">
        <AlertDialogHeader>
          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-400">
            {{ actionType === 'approve' 
               ? 'This will formally approve the job request, indicating you accept the work post-survey.' 
               : 'This will reject the job request. This action cannot be undone.' }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showConfirmDialog = false" class="bg-transparent border-slate-700 text-white hover:bg-slate-800 hover:text-white">Cancel</AlertDialogCancel>
          <AlertDialogAction 
             @click="handleConfirmAction" 
             :disabled="isProcessing"
             :class="actionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white'">
            <span v-if="isProcessing" class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...
            </span>
            <span v-else>Confirm</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue' 
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner' 
import api from '@/utils/axios' 
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'

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
const showDetailsModal = ref(false)
const selectedJob = ref(null)

// Confirm Dialog States
const showConfirmDialog = ref(false)
const actionType = ref('') 
const isProcessing = ref(false)
const isApprovingProof = ref(false)

// Completion Proof states
const showCompleteModal = ref(false)
const completionProofInput = ref(null)
const isCompleting = ref(false)
const jobToComplete = ref(null)

// Reminder & Report states
const isSendingReminder = ref(false)
const isGeneratingReport = ref(false)

// GCash Modal States
const showGcashModal = ref(false)
const gcashDetails = ref(null)

// Survey State Flags
const isGeneratingAgreement = ref(false)
const isSurveyProcessing = ref(false)
const showGenerateAgreementModal = ref(false)
const showViewAgreementModal = ref(false)

// --- Signature Pad Logic
const signaturePad = ref(null)
const isDrawing = ref(false)
let ctx = null

const openGenerateModal = () => {
    showGenerateAgreementModal.value = true
}

const initCanvas = () => {
   if (!signaturePad.value) return
   ctx = signaturePad.value.getContext('2d')
   ctx.lineWidth = 3
   ctx.lineCap = 'round'
   ctx.strokeStyle = '#38bdf8' 
   clearSignature()
}

watch(showGenerateAgreementModal, async (isOpen) => {
  if (isOpen) {
    await nextTick()
    setTimeout(() => {
      initCanvas()
    }, 150)
  }
})

const getPos = (e) => {
   const rect = signaturePad.value.getBoundingClientRect()
   const clientX = e.clientX || (e.touches && e.touches[0].clientX)
   const clientY = e.clientY || (e.touches && e.touches[0].clientY)
   
   const scaleX = signaturePad.value.width / rect.width
   const scaleY = signaturePad.value.height / rect.height

   return {
      x: (clientX - rect.left) * scaleX,
      y: (clientY - rect.top) * scaleY
   }
}

const startDrawing = (e) => {
   e.preventDefault()
   if (!ctx) initCanvas()
   if (!ctx) return 

   isDrawing.value = true
   const pos = getPos(e)
   ctx.beginPath()
   ctx.moveTo(pos.x, pos.y)
}

const draw = (e) => {
   e.preventDefault()
   if (!isDrawing.value) return
   const pos = getPos(e)
   ctx.lineTo(pos.x, pos.y)
   ctx.stroke()
}

const stopDrawing = () => {
   isDrawing.value = false
}

const clearSignature = () => {
   if(!ctx || !signaturePad.value) return
   ctx.clearRect(0, 0, signaturePad.value.width, signaturePad.value.height)
}

const agreementPreviewText = computed(() => {
   if (!selectedJob.value) return '';
   
   const date = new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
   const clientName = selectedJob.value.client;
   const location = selectedJob.value.location;
   const serviceTitle = selectedJob.value.serviceDetails?.title || 'Custom Job';
   
   return `FORMAL SURVEY AGREEMENT\n\nDate Issued: ${date}\nService Provider: [Your Name]\nClient: ${clientName}\nService Location: ${location}\n\nBy signing this agreement, the Client formally authorizes the Service Provider to enter the specified premises to conduct a comprehensive site survey. This survey is required to evaluate the scope of work, verify measurements, and confirm the feasibility of the requested service: '${serviceTitle}'.\n\nThis agreement does not commit the Client to a final contract but ensures mutual understanding and safety during the inspection phase.`;
});


const activeFilter = ref({ value: 'all', label: 'All Jobs' })

const filters = [
  { value: 'all', label: 'All Jobs' },
  { value: 'pending', label: 'Pending' },
  { value: 'verifying', label: 'Verifying' }, 
  { value: 'ongoing', label: 'Ongoing' },
  { value: 'completion_review', label: 'Under Review' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' }
]

const jobs = ref([])

const filteredJobs = computed(() => activeFilter.value.value === 'all' ? jobs.value : jobs.value.filter(job => job.status === activeFilter.value.value))

const getInitials = (name) => {
  if (!name) return 'UN'
  return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2)
}

const getStatusText = (status) => {
  if (!status) return 'Unknown'
  if (status === 'completion_review') return 'Under Review'
  return status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const getCustomStatusText = (job) => {
    if (job.status === 'pending' && job.originalData.survey_agreement) {
        if (job.originalData.survey_agreement.status === 'pending_client') return 'Wait Client Sign';
        if (job.originalData.survey_agreement.status === 'signed') return 'Ready for Survey';
        if (job.originalData.survey_agreement.status === 'in_progress') return 'Survey Ongoing';
        if (job.originalData.survey_agreement.status === 'completed') return 'Survey Done';
    }
    return getStatusText(job.status);
}

const fetchJobRequests = async () => {
  try {
    const response = await api.get('/service-provider/job-requests')
    
    if (response.data.success) {
      jobs.value = response.data.data.map(req => {
        const deal = req.official_deal;
        const term = req.payment_term;

        return {
          id: `REQ-${req.id}`,
          client: req.client ? `${req.client.first_name} ${req.client.last_name}` : 'Unknown Client',
          location: deal?.address || req.address || 'No location provided',
          
          serviceDetails: {
            title: req.service_offering ? req.service_offering.title : 'General Service',
            category: req.service_offering ? req.service_offering.category : 'Uncategorized',
            price: deal?.price || (req.service_offering ? req.service_offering.price : 0),
            price_type: req.service_offering ? req.service_offering.price_type : '',
            duration: req.service_offering ? req.service_offering.duration : 'N/A',
            description: deal?.description || (req.service_offering ? req.service_offering.description : '')
          },

          paintBrand: deal?.time_preference || req.time_preference || 'Flexible Time',
          paintType: deal?.contact_number || req.contact_number || 'N/A',
          status: req.status || 'pending',
          date: deal?.preferred_date || req.preferred_date || 'TBD',
          paymentTerm: term ? `${term.payment_term} (${term.payment_method.replace('_', ' ')})` : 'Pending Term',
          paymentStatus: term ? term.status : 'N/A',
          originalData: req
        };
      })
      if (selectedJob.value) {
          const updated = jobs.value.find(j => j.id === selectedJob.value.id);
          if(updated) selectedJob.value = updated;
      }
    }
  } catch (error) {
    console.error('Error fetching service requests:', error)
    toast.error('Failed to load service requests.')
  }
}

onMounted(() => {
  fetchJobRequests()
})

const viewJobDetails = (job) => {
  selectedJob.value = job
  showDetailsModal.value = true
}

// --- Survey Actions ---
const generateSurveyAgreement = async () => {
    if(!selectedJob.value) return;
    
    const signatureBase64 = signaturePad.value.toDataURL('image/png');
    if(signatureBase64.length < 5000) {
        toast.error("Please provide your signature.");
        return;
    }

    isGeneratingAgreement.value = true;
    try {
        const response = await api.post(`/service-provider/job-requests/${selectedJob.value.originalData.id}/survey-agreement`, {
            provider_signature: signatureBase64
        });
        if(response.data.success) {
            toast.success(response.data.message);
            showGenerateAgreementModal.value = false;
            fetchJobRequests();
        }
    } catch(err) {
        toast.error('Failed to generate agreement.');
    } finally {
        isGeneratingAgreement.value = false;
    }
}

const downloadAgreement = () => {
    if (!selectedJob.value?.originalData?.survey_agreement) return;
    const agreement = selectedJob.value.originalData.survey_agreement;

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Survey Agreement Document</title>
            <style>
                body { font-family: sans-serif; padding: 40px; line-height: 1.6; color: #000; }
                .signature-block { margin-top: 60px; display: flex; justify-content: space-between; }
                .signature { text-align: center; width: 45%; border-top: 1px solid #000; padding-top: 10px; margin-top: 80px; position: relative;}
                .sig-img { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); max-height: 80px; }
                h2 { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <h2>Formal Survey Agreement</h2>
            <pre style="font-family: inherit; white-space: pre-wrap; font-size: 14px;">${agreement.agreement_text}</pre>
            <div class="signature-block">
                <div class="signature">
                    ${agreement.provider_signature_url ? `<img src="${agreement.provider_signature_url}" class="sig-img"/>` : ''}
                    <p><strong>Service Provider Signature</strong></p>
                </div>
                <div class="signature">
                    ${agreement.client_signature_url ? `<img src="${agreement.client_signature_url}" class="sig-img"/>` : ''}
                    <p><strong>Client Signature</strong></p>
                </div>
            </div>
            <p style="text-align: center; margin-top: 60px; font-size: 12px; color: #666;">Generated electronically on ${new Date().toLocaleDateString()}</p>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 750);
}

const handleSurveyAction = async (action) => {
    if(!selectedJob.value) return;
    isSurveyProcessing.value = true;
    try {
        const endpoint = action === 'start' ? 'start-survey' : 'complete-survey';
        const response = await api.post(`/service-provider/job-requests/${selectedJob.value.originalData.id}/${endpoint}`);
        if(response.data.success) {
            toast.success(response.data.message);
            fetchJobRequests();
        }
    } catch(err) {
        toast.error(`Failed to ${action} survey.`);
    } finally {
        isSurveyProcessing.value = false;
    }
}

const promptCompleteJob = (job) => {
   jobToComplete.value = job
   showCompleteModal.value = true
}

const submitCompletion = async () => {
   if (!completionProofInput.value || completionProofInput.value.files.length === 0) {
      toast.error('Please select at least one image.')
      return
   }

   const formData = new FormData()
   for (let i = 0; i < completionProofInput.value.files.length; i++) {
      formData.append('proof_images[]', completionProofInput.value.files[i])
   }

   isCompleting.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/${jobToComplete.value.originalData.id}/complete`, formData, {
         headers: { 'Content-Type': 'multipart/form-data' }
      })
      if (res.data.success) {
         toast.success(res.data.message)
         showCompleteModal.value = false
         showDetailsModal.value = false
         fetchJobRequests() 
      }
   } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to submit proof.')
   } finally {
      isCompleting.value = false
      if (completionProofInput.value) completionProofInput.value.value = null
   }
}

const openGcashModal = async () => {
  showGcashModal.value = true
  gcashDetails.value = null
  try {
     const res = await api.get('/service-provider/job-requests/gcash-details')
     if (res.data.success) {
        gcashDetails.value = res.data.data
     }
  } catch (error) {
     toast.error('Failed to fetch GCash details.')
     showGcashModal.value = false
  }
}

const promptApproveJob = () => {
  actionType.value = 'approve'
  showConfirmDialog.value = true
}

const promptRejectJob = () => {
  actionType.value = 'reject'
  showConfirmDialog.value = true
}

const handleConfirmAction = async () => {
  if (!selectedJob.value) return
  isProcessing.value = true

  try {
    if (actionType.value === 'approve') {
      const response = await api.post(`/service-provider/job-requests/${selectedJob.value.originalData.id}/approve`)
      
      if(response.data.success) {
        selectedJob.value.status = 'verifying'
        const index = jobs.value.findIndex(j => j.id === selectedJob.value.id)
        if (index !== -1) jobs.value[index].status = 'verifying'
        
        toast.success('Job request officially approved successfully.')
        showDetailsModal.value = false
      }
    } else if (actionType.value === 'reject') {
      const response = await api.post(`/service-provider/job-requests/${selectedJob.value.originalData.id}/reject`)
      
      if(response.data.success) {
        selectedJob.value.status = 'rejected'
        const index = jobs.value.findIndex(j => j.id === selectedJob.value.id)
        if (index !== -1) jobs.value[index].status = 'rejected'
        
        toast.success('Job request rejected successfully.')
        showDetailsModal.value = false
      }
    }
  } catch (error) {
    console.error(`Error processing ${actionType.value} job:`, error)
    toast.error(error.response?.data?.message || 'An error occurred while processing the request.')
  } finally {
    isProcessing.value = false
    showConfirmDialog.value = false
  }
}

const approveProof = async (termId) => {
   if (!termId) return
   isApprovingProof.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/payment-terms/${termId}/approve`)
      if (res.data.success) {
         toast.success('Payment verified successfully!')
         await fetchJobRequests()
      }
   } catch(error) {
      toast.error('Failed to verify payment proof.')
   } finally {
      isApprovingProof.value = false
   }
}

const sendReminder = async (termId) => {
   if (!termId) return
   isSendingReminder.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/payment-terms/${termId}/remind`)
      if (res.data.success) {
         toast.success('Payment reminder email sent successfully to the client.')
         await fetchJobRequests()
      }
   } catch (error) {
      toast.error('Failed to send payment reminder.')
   } finally {
      isSendingReminder.value = false
   }
}

const generateReport = async (termId) => {
   if (!termId) return
   isGeneratingReport.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/payment-terms/${termId}/legal-report`)
      if (res.data.success) {
         toast.success('Legal report generated successfully. Client has been notified.')
         await fetchJobRequests()
      }
   } catch (error) {
      toast.error('Failed to generate legal report.')
   } finally {
      isGeneratingReport.value = false
   }
}

const goToChat = (job) => {
  router.push('/serviceProvider/SPChat')
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(30, 41, 59, 0.3); border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(to right, #3b82f6, #0ea5e9); border-radius: 3px; }
</style>