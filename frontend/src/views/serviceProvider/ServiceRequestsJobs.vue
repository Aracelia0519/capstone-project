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

    <!-- Mobile View -->
    <div class="md:hidden space-y-4 mb-4">
      <div v-for="job in paginatedJobs" :key="job.id" class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
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

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-slate-900 rounded-xl border border-slate-800 overflow-hidden mb-4">
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
          <TableRow v-for="job in paginatedJobs" :key="job.id" class="border-slate-800 hover:bg-slate-800/50">
            <TableCell>
               <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-blue-900/20 border border-blue-800/50 flex items-center justify-center mr-3 text-blue-400">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
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

    <!-- Pagination Controls -->
    <div v-if="filteredJobs.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 mt-4 mb-8">
      <p class="text-sm text-slate-400">
        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredJobs.length) }} of {{ filteredJobs.length }} jobs
      </p>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="prevPage" :disabled="currentPage === 1" class="bg-slate-800 border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700">
          Previous
        </Button>
        <div class="text-sm text-slate-300 px-2 font-medium">Page {{ currentPage }} of {{ totalPages }}</div>
        <Button variant="outline" size="sm" @click="nextPage" :disabled="currentPage === totalPages" class="bg-slate-800 border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700">
          Next
        </Button>
      </div>
    </div>

    <Dialog v-model:open="showDetailsModal">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[95vw] max-w-[95vw] md:max-w-[1100px] max-h-[90vh] overflow-y-auto custom-scrollbar">
        <DialogHeader>
          <DialogTitle>Job Request Details</DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedJob" class="py-4 space-y-6">

          <!-- ═══════════ ALERTS ═══════════ -->
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

          <!-- ═══════════ SURVEY AGREEMENT ═══════════ -->
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

          <!-- ═══════════ REQUEST OVERVIEW ═══════════ -->
          <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4">
             <h4 class="text-sm font-bold text-slate-300 uppercase tracking-wider mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Request Overview
             </h4>
             <div class="grid grid-cols-2 gap-4">
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
             </div>
             <div class="mt-4">
                <p class="text-slate-400 text-xs uppercase mb-1">Complete Location</p>
                <p class="text-white bg-slate-900 p-2 rounded-lg border border-slate-800 text-sm">{{ selectedJob.location }}</p>
             </div>
             <div class="mt-4">
                <p class="text-slate-400 text-xs uppercase mb-1">Client Description / Notes</p>
                <div class="bg-slate-900 p-3 rounded-lg border border-slate-800 text-sm whitespace-pre-wrap leading-relaxed">
                   {{ selectedJob.serviceDetails.description || 'No additional description provided.' }}
                </div>
             </div>
          </div>

          <!-- ═══════════ SERVICE OFFERING ═══════════ -->
          <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4">
             <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Service Offering Details
             </h4>
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

          <!-- ═══════════ OFFICIAL DEAL & PAYMENT DETAILS ═══════════ -->
          <div v-if="selectedJob.originalData.official_deal" class="bg-slate-950/60 border border-blue-800/50 rounded-xl p-4">
             <h4 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Official Deal & Payment Details
             </h4>

             <!-- Legacy (fixed-price) deal summary -->
             <template v-if="!selectedJob.originalData.daily_billing">
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

             <div v-if="selectedJob.originalData.payment_term" class="grid grid-cols-2 gap-4 bg-slate-900 p-3 rounded-xl border border-slate-800 mb-4 mt-3">
               <div>
                 <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total Paid By Client</p>
                 <p class="text-sm text-emerald-400 font-bold tracking-tight">₱{{ Number(selectedJob.originalData.payment_term.total_paid || 0).toLocaleString() }}</p>
               </div>
               <div>
                 <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Remaining Balance</p>
                 <p class="text-sm text-red-400 font-bold tracking-tight">₱{{ Number(selectedJob.originalData.payment_term.balance || 0).toLocaleString() }}</p>
               </div>
             </div>

             <!-- Invoice PWD Discount Visibility Block -->
             <div v-if="selectedJob.originalData.invoice_details?.pwd_discount_applied" class="mb-4 bg-indigo-900/30 border border-indigo-500/50 p-3 rounded-xl mt-3">
                 <p class="text-sm font-bold text-indigo-400 flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> PWD DISCOUNT APPLIED</p>
                 <p class="text-xs text-gray-300 mt-1">{{ selectedJob.originalData.invoice_details.pwd_discount_text }}</p>
             </div>

             <div v-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.payment_method === 'on_hand' && selectedJob.originalData.payment_term.status === 'awaiting_proof_approval'" class="mb-4 mt-4 border-t border-blue-800/30 pt-4">
                <p class="text-yellow-400 text-sm font-bold mb-2">Client Uploaded Proof of Payment</p>
                <div class="w-full max-w-[200px] rounded-lg overflow-hidden border border-slate-700 mb-3">
                   <img :src="selectedJob.originalData.payment_term.proof_of_payment_url" class="w-full h-auto object-cover" />
                </div>
                <div class="flex gap-2 flex-wrap">
                   <Button @click="approveProof(selectedJob.originalData.payment_term.id)" :disabled="isApprovingProof" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-9">
                      <span v-if="isApprovingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Approving...</span>
                      <span v-else>Approve & Verify Payment</span>
                   </Button>
                   <Button @click="rejectProof(selectedJob.originalData.payment_term.id)" :disabled="isRejectingProof" class="bg-red-600/20 hover:bg-red-700/60 text-red-300 font-bold h-9 border border-red-500/30">
                      <span v-if="isRejectingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-red-400 mr-2"></div> Rejecting...</span>
                      <span v-else>Reject Proof</span>
                   </Button>
                </div>
             </div>

             <div v-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.status === 'paid' && selectedJob.originalData.payment_term.balance <= 0" class="mt-4">
                <Badge class="bg-emerald-500/20 text-emerald-400 border-emerald-500/30 px-3 py-1">Fully Paid & Completed</Badge>
             </div>
             <div v-else-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.status === 'paid' && selectedJob.originalData.payment_term.balance > 0" class="mt-4">
                <Badge class="bg-blue-500/20 text-blue-400 border-blue-500/30 px-3 py-1">Initial Payment Verified - Pending Balance</Badge>
             </div>
             <div v-else-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.status !== 'pending' && selectedJob.originalData.payment_term.status !== 'agreed'" class="mt-4">
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
             </template>

             <!-- Daily billing panel -->
             <div v-if="selectedJob.originalData.daily_billing" class="space-y-4">
                <div class="bg-blue-900/30 border border-blue-500/40 rounded-xl p-3">
                   <p class="text-sm font-bold text-blue-300 uppercase tracking-wider mb-1">Daily Billing Active</p>
                   <p class="text-xs text-gray-300">Client is billed <span class="font-bold text-white">₱{{ Number(selectedJob.originalData.daily_billing.daily_rate).toLocaleString() }}</span> per day since {{ selectedJob.originalData.daily_billing.billing_started_at }} until the service is completed &amp; approved.</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-900 p-3 rounded-xl border border-slate-800">
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Days Worked</p>
                      <p class="text-sm text-white font-bold">{{ selectedJob.originalData.daily_billing.days_elapsed }}</p>
                   </div>
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Exempted</p>
                      <p class="text-sm text-gray-300 font-bold">{{ selectedJob.originalData.daily_billing.days_exempt }}</p>
                   </div>
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Days Paid</p>
                      <p class="text-sm text-emerald-400 font-bold">{{ selectedJob.originalData.daily_billing.days_paid }}</p>
                   </div>
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Unpaid Day{{ selectedJob.originalData.daily_billing.days_outstanding === 1 ? '' : 's' }}</p>
                      <p class="text-sm text-red-400 font-bold">{{ selectedJob.originalData.daily_billing.days_outstanding }}</p>
                   </div>
                </div>

                <div class="grid grid-cols-3 gap-3 bg-slate-900 p-3 rounded-xl border border-slate-800">
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total Due</p>
                      <p class="text-sm text-gray-200 font-bold">₱{{ Number(selectedJob.originalData.daily_billing.total_due).toLocaleString() }}</p>
                   </div>
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total Paid</p>
                      <p class="text-sm text-emerald-400 font-bold">₱{{ Number(selectedJob.originalData.daily_billing.total_paid).toLocaleString() }}</p>
                   </div>
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Outstanding</p>
                      <p class="text-sm text-red-400 font-bold">₱{{ Number(selectedJob.originalData.daily_billing.outstanding).toLocaleString() }}</p>
                   </div>
                </div>

                <div v-if="['ongoing', 'completion_review'].includes(selectedJob.status)" class="bg-slate-900 border border-slate-800 rounded-xl p-3">
                   <div class="flex items-center justify-between mb-2">
                      <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Work Day Tracker</p>
                      <button type="button" @click="workDayBypass = !workDayBypass" :class="workDayBypass ? 'bg-blue-600/30 text-blue-400 border border-blue-500/40' : 'bg-slate-800 text-gray-400 border border-slate-700 hover:bg-slate-700'" class="text-[10px] font-bold h-6 px-2 rounded-md transition-colors">
                         {{ workDayBypass ? 'Bypass ON' : 'Bypass' }}
                      </button>
                   </div>
                   <p class="text-xs text-gray-500 mb-2">Mark a day as <span class="text-red-400 font-bold">NOT worked</span> to waive the client's fee for that day. Unmarked days are treated as worked.</p>

                   <!-- Bypass (presentation mode): pick ANY date and mark it worked / not worked -->
                   <div v-if="workDayBypass" class="bg-blue-900/20 border border-blue-500/40 rounded-lg p-3 mb-3 space-y-2">
                      <p class="text-[10px] font-bold text-blue-300 uppercase tracking-wider">Bypass — Mark Any Date</p>
                      <div class="flex items-center gap-2 flex-wrap">
                         <input type="date" v-model="workDayBypassDate" class="bg-slate-900 border border-slate-700 rounded-md text-xs text-gray-200 px-2 py-1.5 min-w-[150px]" />
                         <button type="button" @click="markBypassDay(selectedJob.originalData.id, true)" :disabled="isMarkingWorkDay" class="bg-emerald-600/30 text-emerald-400 border border-emerald-600/40 text-[10px] font-bold h-7 px-3 rounded-md transition-colors disabled:opacity-60">
                            <span v-if="isMarkingWorkDay">Marking...</span>
                            <span v-else>Mark Worked</span>
                         </button>
                         <button type="button" @click="markBypassDay(selectedJob.originalData.id, false)" :disabled="isMarkingWorkDay" class="bg-red-600/30 text-red-400 border border-red-600/40 text-[10px] font-bold h-7 px-3 rounded-md transition-colors disabled:opacity-60">
                            <span v-if="isMarkingWorkDay">Marking...</span>
                            <span v-else>Mark Not Worked</span>
                         </button>
                      </div>
                   </div>

                   <div class="max-h-44 overflow-y-auto space-y-1 pr-1">
                      <div v-for="day in getWorkDays(selectedJob.originalData.daily_billing)" :key="day.date" class="flex items-center justify-between rounded-lg px-2 py-1.5 border border-slate-800 bg-slate-900/60">
                         <div class="flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-300">{{ day.label }}</span>
                            <span v-if="day.isToday" class="text-[9px] px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30">Today</span>
                            <span v-if="day.worked === false" class="text-[9px] px-2 py-0.5 rounded-full bg-gray-600/30 text-gray-400 border border-gray-600/50">Exempted</span>
                         </div>
                         <div class="flex gap-1">
                            <button type="button" @click="markWorkDay(selectedJob.originalData.id, day.date, true)" :disabled="isMarkingWorkDay || day.worked === true" :class="day.worked === true ? 'bg-emerald-600/30 text-emerald-400 border border-emerald-600/40' : 'bg-slate-800 text-gray-400 border border-slate-700 hover:bg-slate-700'" class="text-[10px] font-bold h-6 px-2 rounded-md transition-colors disabled:opacity-60">
                               Worked
                            </button>
                            <button type="button" @click="markWorkDay(selectedJob.originalData.id, day.date, false)" :disabled="isMarkingWorkDay || day.worked === false" :class="day.worked === false ? 'bg-red-600/30 text-red-400 border border-red-600/40' : 'bg-slate-800 text-gray-400 border border-slate-700 hover:bg-slate-700'" class="text-[10px] font-bold h-6 px-2 rounded-md transition-colors disabled:opacity-60">
                               Not Worked
                            </button>
                         </div>
                      </div>
                   </div>
                </div>

                <div v-if="selectedJob.status === 'ongoing'" class="bg-slate-900 p-3 rounded-xl border border-slate-800">
                   <div v-if="selectedJob.originalData.daily_billing.today_exempt" class="text-center">
                      <p class="text-sm font-bold text-gray-400">No daily fee today</p>
                      <p class="text-xs text-gray-500 mt-1">You marked today as not worked, so the client does not owe today's fee.</p>
                   </div>
                   <div v-else-if="selectedJob.originalData.daily_billing.today_paid" class="flex items-center justify-center gap-2">
                      <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      <p class="text-sm font-bold text-emerald-400">Client settled today's daily fee</p>
                   </div>
                   <div v-else class="flex items-center justify-center gap-2">
                      <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      <p class="text-sm font-bold text-amber-400">Awaiting today's daily fee of ₱{{ Number(selectedJob.originalData.daily_billing.daily_rate).toLocaleString() }}</p>
                   </div>
                </div>

                <div v-if="selectedJob.originalData.payment_term && selectedJob.originalData.payment_term.status === 'awaiting_proof_approval'" class="border-t border-blue-800/30 pt-3">
                   <p class="text-yellow-400 text-sm font-bold mb-2">Client Uploaded Daily Payment Proof</p>
                   <div class="w-full max-w-[200px] rounded-lg overflow-hidden border border-slate-700 mb-3">
                      <img :src="selectedJob.originalData.payment_term.proof_of_payment_url" class="w-full h-auto object-cover" />
                   </div>
                   <div class="flex gap-2 flex-wrap">
                      <Button @click="approveProof(selectedJob.originalData.payment_term.id)" :disabled="isApprovingProof" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-9">
                         <span v-if="isApprovingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Approving...</span>
                         <span v-else>Approve & Verify Payment</span>
                      </Button>
                      <Button @click="rejectProof(selectedJob.originalData.payment_term.id)" :disabled="isRejectingProof" class="bg-red-600/20 hover:bg-red-700/60 text-red-300 font-bold h-9 border border-red-500/30">
                         <span v-if="isRejectingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-red-400 mr-2"></div> Rejecting...</span>
                         <span v-else>Reject Proof</span>
                      </Button>
                   </div>
                </div>

                <div v-if="selectedJob.originalData.daily_billing.payment_log.length" class="bg-slate-900 border border-slate-800 rounded-xl p-3 max-h-44 overflow-y-auto">
                   <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Daily Payment Log</p>
                   <div v-for="entry in selectedJob.originalData.daily_billing.payment_log" :key="entry.paid_date" class="flex items-center justify-between py-1.5 border-b border-slate-800/60 last:border-0">
                      <div class="flex items-center gap-2">
                         <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                         <span class="text-xs text-gray-300">Payment for {{ entry.covers_date }}</span>
                      </div>
                      <span class="text-xs font-bold text-emerald-400">₱{{ Number(entry.amount).toLocaleString() }}</span>
                   </div>
                </div>

                <div v-if="selectedJob.status === 'completed' && selectedJob.originalData.daily_billing.days_outstanding > 0" class="border-t border-blue-800/30 pt-3">
                   <p class="text-red-400 text-sm font-bold mb-2">Unpaid Daily Balance Action</p>
                   <p class="text-xs text-gray-300 mb-3">{{ selectedJob.originalData.daily_billing.days_outstanding }} unpaid day(s) remaining (₱{{ Number(selectedJob.originalData.daily_billing.outstanding).toLocaleString() }}). Reminders sent: <span class="font-bold">{{ selectedJob.originalData.payment_term.reminder_count || 0 }}</span>/3</p>
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

             <!-- System Native Invoices & Receipts -->
             <div class="flex gap-2 flex-wrap mt-4 border-t border-blue-800/30 pt-4">
                 <Button v-if="selectedJob.originalData.invoice_details" variant="secondary" size="sm" @click="openInvoiceModal(selectedJob)" class="bg-indigo-600/20 hover:bg-indigo-600/40 text-indigo-400 border border-indigo-500/30">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> View Invoice
                 </Button>
                 <Button v-if="selectedJob.originalData.receipt_details" variant="secondary" size="sm" @click="openReceiptModal(selectedJob)" class="bg-emerald-600/20 hover:bg-emerald-600/40 text-emerald-400 border border-emerald-500/30">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-4 8l6-6M5 8h.01M5 12h.01M5 16h.01M3 21l2-2 2 2 2-2 2 2 2-2 2 2 2-2 2 2v-16a2 2 0 00-2-2h-14a2 2 0 00-2 2v16z"/></svg> View Receipt
                 </Button>
             </div>
          </div>

          <!-- ═══════════ MATERIALS REIMBURSEMENT ═══════════ -->
          <div v-if="selectedJob.originalData.official_deal" class="bg-slate-950/60 border border-amber-700/40 rounded-xl p-4">
             <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                <h4 class="text-sm font-bold text-amber-400 uppercase tracking-wider flex items-center gap-2">
                   <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                   Materials Reimbursement
                </h4>
                <Button size="sm" @click="showMaterialsForm = !showMaterialsForm" :class="showMaterialsForm ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-amber-600 hover:bg-amber-700 text-white'" class="h-8 text-xs">
                   {{ showMaterialsForm ? 'Cancel' : '+ Add Materials' }}
                </Button>
             </div>

             <!-- Add-materials form -->
             <div v-if="showMaterialsForm" class="bg-slate-900 border border-amber-700/40 rounded-lg p-3 space-y-2">
                <p class="text-[10px] font-bold text-amber-400 uppercase tracking-wider mb-1">New Materials Request</p>
                <div v-for="(item, idx) in materialsItems" :key="idx" class="flex gap-2 items-center">
                   <input v-model="item.item_name" placeholder="Material name" class="flex-1 bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-xs text-gray-200" />
                   <input v-model.number="item.quantity" type="number" min="1" placeholder="Qty" class="w-16 bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-xs text-gray-200" />
                   <input v-model.number="item.unit_price" type="number" min="0" step="0.01" placeholder="Price each" class="w-28 bg-slate-900 border border-slate-700 rounded-md px-2 py-1.5 text-xs text-gray-200" />
                   <button type="button" @click="removeMaterialRow(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold" :disabled="materialsItems.length === 1">✕</button>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                   <button type="button" @click="addMaterialRow" class="text-[10px] font-bold h-6 px-2 rounded-md bg-slate-800 text-blue-400 border border-slate-700 hover:bg-slate-700">+ Add Item</button>
                   <input type="file" ref="materialsProofInput" accept="image/*" class="text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-amber-500/10 file:text-amber-400 cursor-pointer" />
                </div>
                <Button @click="submitMaterials" :disabled="isAddingMaterials" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold h-9 rounded-xl text-xs">
                   <span v-if="isAddingMaterials" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Submitting...</span>
                   <span v-else>Submit for Client Approval</span>
                </Button>
             </div>

             <!-- Materials batches -->
             <div v-if="selectedJob.originalData.materials?.length" class="space-y-2">
                <div v-for="batch in selectedJob.originalData.materials" :key="batch.id" class="bg-slate-900/60 border border-slate-800 rounded-lg p-3 space-y-2">
                   <div class="flex items-center justify-between gap-2">
                      <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Batch #{{ batch.id }} <span class="text-gray-600">·</span> {{ new Date(batch.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}</span>
                      <div class="flex items-center gap-2">
                         <Badge :class="batch.status === 'approved' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : batch.status === 'rejected' ? 'bg-red-500/20 text-red-400 border-red-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30'" class="uppercase text-[10px] px-2 py-0.5">{{ batch.status }}</Badge>
                         <button v-if="batch.status === 'pending'" type="button" @click="deleteMaterials(batch.id)" :disabled="isDeletingMaterials" class="text-red-400 hover:text-red-300 text-xs font-bold">Delete</button>
                      </div>
                   </div>
                   <div class="space-y-1">
                      <div v-for="item in batch.items" :key="item.id" class="flex items-center justify-between text-xs gap-2">
                         <span class="text-gray-300 flex-1">{{ item.item_name }} <span class="text-gray-500">× {{ item.quantity }}</span></span>
                         <span class="text-gray-500">₱{{ Number(item.unit_price).toLocaleString() }}/pc</span>
                         <span class="text-white font-bold w-20 text-right">₱{{ Number(item.total_price).toLocaleString() }}</span>
                      </div>
                   </div>
                   <div class="flex items-center justify-between border-t border-slate-800 pt-2 text-xs">
                      <span class="text-gray-400 font-bold uppercase tracking-wider">Batch Total</span>
                      <span class="text-amber-400 font-bold">₱{{ Number(batch.items_total).toLocaleString() }}</span>
                   </div>
                   <img v-if="batch.proof_photo_url" :src="batch.proof_photo_url" class="h-20 rounded-lg border border-slate-700 object-cover" />
                   <div v-if="batch.status === 'rejected' && batch.rejection_reason" class="bg-red-900/20 border border-red-800/50 rounded-lg p-2">
                      <p class="text-[10px] font-bold text-red-400 uppercase tracking-wider">Rejected — reason</p>
                      <p class="text-xs text-gray-300 mt-0.5 italic">{{ batch.rejection_reason }}</p>
                   </div>
                </div>
             </div>

             <!-- Materials payment term -->
             <div v-if="selectedJob.originalData.materials_term && ['agreed', 'awaiting_proof_approval', 'paid'].includes(selectedJob.originalData.materials_term.status)" class="bg-slate-900 border border-amber-700/40 rounded-lg p-3 space-y-3">
                <p class="text-[10px] font-bold text-amber-400 uppercase tracking-wider">Materials Payment</p>
                <div class="grid grid-cols-3 gap-2">
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Total</p>
                      <p class="text-sm text-white font-bold">₱{{ Number(selectedJob.originalData.materials_term.amount).toLocaleString() }}</p>
                   </div>
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Paid</p>
                      <p class="text-sm text-emerald-400 font-bold">₱{{ Number(selectedJob.originalData.materials_term.total_paid).toLocaleString() }}</p>
                   </div>
                   <div>
                      <p class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Balance</p>
                      <p class="text-sm text-red-400 font-bold">₱{{ Number(selectedJob.originalData.materials_term.balance).toLocaleString() }}</p>
                   </div>
                </div>

                <div v-if="selectedJob.originalData.materials_term.status === 'awaiting_proof_approval'" class="border-t border-amber-800/30 pt-3">
                   <p class="text-yellow-400 text-xs font-bold mb-2">Client Uploaded Materials Proof</p>
                   <div class="w-full max-w-[180px] rounded-lg overflow-hidden border border-slate-700 mb-3">
                      <img :src="selectedJob.originalData.materials_term.proof_of_payment_url" class="w-full h-auto object-cover" />
                   </div>
                   <div class="flex gap-2 flex-wrap">
                      <Button @click="approveProof(selectedJob.originalData.materials_term.id)" :disabled="isApprovingProof" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-8 text-xs">
                         <span v-if="isApprovingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Approving...</span>
                         <span v-else>Approve &amp; Verify</span>
                      </Button>
                      <Button @click="rejectProof(selectedJob.originalData.materials_term.id)" :disabled="isRejectingProof" class="bg-red-600/20 hover:bg-red-700/60 text-red-300 font-bold h-8 text-xs border border-red-500/30">
                         <span v-if="isRejectingProof" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-red-400 mr-2"></div> Rejecting...</span>
                         <span v-else>Reject Proof</span>
                      </Button>
                   </div>
                </div>

                <div v-if="selectedJob.originalData.materials_term.status === 'paid' && selectedJob.originalData.materials_term.balance <= 0" class="text-center">
                   <p class="text-xs font-bold text-emerald-400">✓ Materials fully paid by the client.</p>
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
             <div class="mb-4">
                <h4 class="text-sm font-bold text-slate-300 mb-2">Location Verification</h4>
                <div id="completion-map" class="h-48 w-full rounded-xl z-0 border border-slate-700 bg-slate-800"></div>
                
                <div class="flex items-center justify-between mt-2">
                   <div class="flex items-center gap-2">
                      <span v-if="isLocating" class="text-xs text-blue-400 flex items-center">
                         <div class="animate-spin h-3 w-3 border-2 border-blue-400 border-t-transparent rounded-full mr-1"></div> Locating...
                      </span>
                      <span v-else-if="isAtLocation" class="text-xs text-emerald-400 flex items-center">
                         <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Verified at location
                      </span>
                      <span v-else-if="bypassLocation" class="text-xs text-amber-400 flex items-center">
                         <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg> Location Bypassed
                      </span>
                      <span v-else class="text-xs text-red-400 flex items-center">
                         <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Not at client location
                      </span>
                   </div>
                   
                   <Button v-if="!isAtLocation && !bypassLocation && !isLocating" @click="bypassLocation = true" variant="outline" size="sm" class="h-7 text-[10px] border-amber-500/50 text-amber-500 hover:bg-amber-500/10">
                      Bypass Location (Demo)
                   </Button>
                </div>
             </div>

             <p class="text-sm text-gray-400 mb-4 border-t border-slate-800 pt-4">Please upload images showing the completed work. The client will review these to finalize the job.</p>
             <input type="file" ref="completionProofInput" multiple accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20 mb-3 cursor-pointer" />
          </div>
          <div class="flex justify-end gap-3 mt-4 border-t border-slate-800 pt-4">
             <Button variant="ghost" @click="showCompleteModal = false" class="text-gray-400 hover:text-white">Cancel</Button>
             <Button @click="submitCompletion" :disabled="isCompleting || (!isAtLocation && !bypassLocation) || isLocating" class="bg-blue-600 hover:bg-blue-700 text-white">
                <span v-if="isCompleting" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Uploading...</span>
                <span v-else>Submit & Request Approval</span>
             </Button>
          </div>
       </DialogContent>
    </Dialog>

    <Dialog v-model:open="showInvoiceModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[90vw] md:max-w-[500px]">
          <DialogTitle class="text-indigo-400 font-bold mb-2 flex items-center gap-2">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Official Invoice
          </DialogTitle>
          <div v-if="selectedInvoiceReq" class="py-2 space-y-4">
             <div class="flex justify-between items-start border-b border-slate-800 pb-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Invoice No.</p>
                    <p class="text-sm font-bold text-white">{{ selectedInvoiceReq.originalData.invoice_details.invoice_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Status</p>
                    <Badge :class="selectedInvoiceReq.originalData.invoice_details.status === 'paid' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-amber-500/20 text-amber-400 border-amber-500/30'">
                        {{ selectedInvoiceReq.originalData.invoice_details.status.toUpperCase() }}
                    </Badge>
                </div>
             </div>

             <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Client</span>
                    <span class="font-medium">{{ selectedInvoiceReq.client }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Service</span>
                    <span class="font-medium">{{ selectedInvoiceReq.serviceDetails.title }}</span>
                </div>
                <div class="flex justify-between text-sm border-b border-slate-800 pb-2">
                    <span class="text-gray-400">Date Issued</span>
                    <span class="font-medium">{{ new Date(selectedInvoiceReq.originalData.invoice_details.issued_date).toLocaleDateString() }}</span>
                </div>

                <div v-if="selectedInvoiceReq.originalData.invoice_details.pwd_discount_applied" class="flex justify-between text-sm text-indigo-400 bg-indigo-900/20 p-2 rounded-lg border border-indigo-500/30">
                    <span>Discount</span>
                    <span class="font-medium font-mono text-xs">{{ selectedInvoiceReq.originalData.invoice_details.pwd_discount_text }}</span>
                </div>

                <div class="flex justify-between text-base pt-2 font-bold text-white">
                    <span>Total Amount</span>
                    <span>₱{{ Number(selectedInvoiceReq.originalData.invoice_details.total_amount).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between text-sm text-emerald-400">
                    <span>Amount Paid</span>
                    <span>₱{{ Number(selectedInvoiceReq.originalData.invoice_details.amount_paid).toLocaleString() }}</span>
                </div>
                <div class="flex justify-between text-sm text-red-400">
                    <span>Balance Due</span>
                    <span>₱{{ Number(selectedInvoiceReq.originalData.invoice_details.balance).toLocaleString() }}</span>
                </div>
             </div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
             <Button variant="ghost" @click="showInvoiceModal = false" class="text-gray-400 hover:text-white">Close</Button>
             <Button @click="downloadInvoice" class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg shadow-indigo-900/20">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg> Download Invoice
             </Button>
          </div>
       </DialogContent>
    </Dialog>

    <Dialog v-model:open="showReceiptModal">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[90vw] md:max-w-[400px]">
          <DialogTitle class="text-emerald-400 font-bold mb-2 flex items-center gap-2">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-4 8l6-6M5 8h.01M5 12h.01M5 16h.01M3 21l2-2 2 2 2-2 2 2 2-2 2 2 2-2 2 2v-16a2 2 0 00-2-2h-14a2 2 0 00-2 2v16z"/></svg> Official Receipt
          </DialogTitle>
          <div v-if="selectedReceiptReq" class="py-2 space-y-4">
             <div class="text-center border-b border-slate-800 pb-4">
                <div class="w-12 h-12 bg-emerald-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="text-xs text-gray-500 uppercase">Receipt No.</p>
                <p class="text-sm font-mono text-white mb-2">{{ selectedReceiptReq.originalData.receipt_details.receipt_number }}</p>
                <h3 class="text-2xl font-bold text-emerald-400">₱{{ Number(selectedReceiptReq.originalData.receipt_details.total_paid).toLocaleString() }}</h3>
                <p class="text-xs text-gray-400 mt-1">Successfully Collected</p>
             </div>

             <div class="space-y-2 text-sm bg-slate-950 p-4 rounded-xl border border-slate-800">
                <div class="flex justify-between">
                    <span class="text-gray-500">Date Completed</span>
                    <span class="font-medium">{{ new Date(selectedReceiptReq.originalData.receipt_details.completion_date).toLocaleDateString() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Service</span>
                    <span class="font-medium text-right max-w-[150px] truncate">{{ selectedReceiptReq.originalData.receipt_details.service_name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Client</span>
                    <span class="font-medium">{{ selectedReceiptReq.originalData.receipt_details.client_name }}</span>
                </div>
             </div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
             <Button variant="ghost" @click="showReceiptModal = false" class="text-gray-400 hover:text-white">Close</Button>
             <Button @click="downloadReceipt" class="bg-emerald-600 hover:bg-emerald-700 text-white shadow-lg shadow-emerald-900/20">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg> Download Receipt
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
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue' 
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner' 
import api from '@/utils/axios' 
import echo from '@/utils/websocket' 
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'

import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

// Fix for missing marker icons in Leaflet via Vue/Vite
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: new URL('leaflet/dist/images/marker-icon-2x.png', import.meta.url).href,
  iconUrl: new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href,
  shadowUrl: new URL('leaflet/dist/images/marker-shadow.png', import.meta.url).href,
});

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

const activeProviderId = ref(null)

const showConfirmDialog = ref(false)
const actionType = ref('') 
const isProcessing = ref(false)
const isApprovingProof = ref(false)

const showCompleteModal = ref(false)
const completionProofInput = ref(null)
const isCompleting = ref(false)
const jobToComplete = ref(null)

// Map states
const isLocating = ref(false)
const isAtLocation = ref(false)
const bypassLocation = ref(false)
const mapInstance = ref(null)

const isSendingReminder = ref(false)
const isGeneratingReport = ref(false)

const showGcashModal = ref(false)
const gcashDetails = ref(null)

const isGeneratingAgreement = ref(false)
const isSurveyProcessing = ref(false)
const showGenerateAgreementModal = ref(false)
const showViewAgreementModal = ref(false)

const showInvoiceModal = ref(false)
const selectedInvoiceReq = ref(null)

const showReceiptModal = ref(false)
const selectedReceiptReq = ref(null)

const signaturePad = ref(null)
const isDrawing = ref(false)
let ctx = null

// --- PAGINATION STATE ---
const currentPage = ref(1)
const itemsPerPage = ref(10)

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

// --- PAGINATION COMPUTED PROPERTIES & METHODS ---
const totalPages = computed(() => Math.ceil(filteredJobs.value.length / itemsPerPage.value) || 1)

const paginatedJobs = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredJobs.value.slice(start, end)
})

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

// Reset pagination when filter changes
watch(() => activeFilter.value, () => {
  currentPage.value = 1
})

const initMap = async () => {
   isLocating.value = true
   isAtLocation.value = false
   bypassLocation.value = false
   
   await nextTick()
   
   if (mapInstance.value) {
       mapInstance.value.remove()
       mapInstance.value = null
   }

   const targetLat = jobToComplete.value?.originalData?.target_lat || 14.4200
   const targetLng = jobToComplete.value?.originalData?.target_lng || 120.9600
   
   mapInstance.value = L.map('completion-map').setView([targetLat, targetLng], 15)
   
   L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
       attribution: '© OpenStreetMap & CARTO'
   }).addTo(mapInstance.value)

   L.circle([targetLat, targetLng], {
       color: '#3b82f6',
       fillColor: '#3b82f6',
       fillOpacity: 0.2,
       radius: 500
   }).addTo(mapInstance.value)

   L.marker([targetLat, targetLng]).addTo(mapInstance.value).bindPopup('Client Location').openPopup()

   if (navigator.geolocation) {
       navigator.geolocation.getCurrentPosition(
           (position) => {
               const userLat = position.coords.latitude
               const userLng = position.coords.longitude
               
               const userLatLng = L.latLng(userLat, userLng)
               const targetLatLng = L.latLng(targetLat, targetLng)
               const distance = userLatLng.distanceTo(targetLatLng)

               L.circleMarker([userLat, userLng], {
                   radius: 8,
                   fillColor: "#10b981",
                   color: "#fff",
                   weight: 2,
                   opacity: 1,
                   fillOpacity: 1
               }).addTo(mapInstance.value).bindPopup('Your Current Location').openPopup()

               mapInstance.value.fitBounds(L.latLngBounds([userLatLng, targetLatLng]), { padding: [30, 30] })

               if (distance <= 500) {
                   isAtLocation.value = true
               } else {
                   isAtLocation.value = false
               }
               isLocating.value = false
           },
           (error) => {
               console.error("Geolocation error:", error)
               isLocating.value = false
           },
           { enableHighAccuracy: true }
       )
   } else {
       isLocating.value = false
   }
}

watch(showCompleteModal, (newVal) => {
  if (newVal) {
    bypassLocation.value = false;
    isAtLocation.value = false;
    setTimeout(() => {
      initMap();
    }, 300);
  } else {
    if (mapInstance.value) {
      mapInstance.value.remove();
      mapInstance.value = null;
    }
  }
})

const openGenerateModal = () => {
    showGenerateAgreementModal.value = true
}

const openInvoiceModal = (job) => {
    selectedInvoiceReq.value = job
    showInvoiceModal.value = true
}

const openReceiptModal = (job) => {
    selectedReceiptReq.value = job
    showReceiptModal.value = true
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

const fetchJobRequests = async (isBackground = false) => {
  try {
    const response = await api.get('/service-provider/job-requests')
    
    if (response.data.success) {
      activeProviderId.value = response.data.provider_id

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

      if (!isBackground) {
        setupWebSocket()
      }
    }
  } catch (error) {
    console.error('Error fetching service requests:', error)
    if (!isBackground) toast.error('Failed to load service requests.')
  }
}

const setupWebSocket = () => {
    if (activeProviderId.value) {
        echo.private(`provider.${activeProviderId.value}.requests`)
            .listen('.request.created', (e) => {
                fetchJobRequests(true)
                toast.success('New Service Request Received!', {
                    description: 'A client has just booked one of your services.'
                })
            })
            .listen('.request.updated', (e) => {
                fetchJobRequests(true)
                toast.info('Job Status Updated', {
                    description: 'A client has updated a request, uploaded a proof, or signed an agreement.'
                })
            })
    }
}

onMounted(() => {
  fetchJobRequests()
})

onUnmounted(() => {
   if (activeProviderId.value) {
       echo.leave(`provider.${activeProviderId.value}.requests`)
   }
})

const viewJobDetails = (job) => {
  selectedJob.value = job
  showDetailsModal.value = true
}

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
            fetchJobRequests(true);
        }
    } catch(err) {
        toast.error('Failed to generate agreement.');
    } finally {
        isGeneratingAgreement.value = false;
    }
}

const printDocument = (title, htmlContent) => {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>${title}</title>
            <style>
                body { font-family: sans-serif; padding: 40px; line-height: 1.6; color: #000; }
                h2, h3 { text-align: center; margin-bottom: 10px; }
                .details { margin-bottom: 30px; }
                .details p { margin: 5px 0; }
                .table-container { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
                th { background-color: #f4f4f4; }
                .total { font-weight: bold; font-size: 1.2em; text-align: right; }
                .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
                .discount { color: #d9534f; font-weight: bold; font-size: 0.9em;}
            </style>
        </head>
        <body>
            ${htmlContent}
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

const downloadInvoice = () => {
    if(!selectedInvoiceReq.value) return;
    const inv = selectedInvoiceReq.value.originalData.invoice_details;
    const content = `
        <h2>SERVICE INVOICE</h2>
        <h3>${inv.invoice_number}</h3>
        <div class="details">
            <p><strong>Provider:</strong> You</p>
            <p><strong>Client:</strong> ${selectedInvoiceReq.value.client}</p>
            <p><strong>Status:</strong> ${inv.status.toUpperCase()}</p>
            <p><strong>Issued Date:</strong> ${new Date(inv.issued_date).toLocaleDateString()}</p>
        </div>
        <table class="table-container">
            <tr><th>Description</th><th>Amount</th></tr>
            <tr>
                <td>${selectedInvoiceReq.value.serviceDetails.title}</td>
                <td>P${Number(inv.total_amount).toLocaleString()}</td>
            </tr>
        </table>
        ${inv.pwd_discount_applied ? `<p class="discount">(${inv.pwd_discount_text})</p>` : ''}
        <p class="total">Total Due: P${Number(inv.total_amount).toLocaleString()}</p>
        <p class="total">Amount Paid: P${Number(inv.amount_paid).toLocaleString()}</p>
        <p class="total" style="color: #d9534f;">Balance: P${Number(inv.balance).toLocaleString()}</p>
        <div class="footer">Thank you for your business.</div>
    `;
    printDocument('Invoice - ' + inv.invoice_number, content);
}

const downloadReceipt = () => {
    if(!selectedReceiptReq.value) return;
    const rec = selectedReceiptReq.value.originalData.receipt_details;
    const content = `
        <h2>OFFICIAL RECEIPT</h2>
        <h3>${rec.receipt_number}</h3>
        <div class="details">
            <p><strong>Provider:</strong> You</p>
            <p><strong>Client:</strong> ${rec.client_name}</p>
            <p><strong>Service:</strong> ${rec.service_name}</p>
            <p><strong>Completion Date:</strong> ${new Date(rec.completion_date).toLocaleDateString()}</p>
        </div>
        <table class="table-container">
            <tr><th>Description</th><th>Total Collected</th></tr>
            <tr>
                <td>Full Payment for ${rec.service_name}</td>
                <td>P${Number(rec.total_paid).toLocaleString()}</td>
            </tr>
        </table>
        <p class="total">Grand Total Collected: P${Number(rec.total_paid).toLocaleString()}</p>
        <div class="footer">This serves as your official collection receipt. Thank you!</div>
    `;
    printDocument('Receipt - ' + rec.receipt_number, content);
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
            fetchJobRequests(true);
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
   if (!isAtLocation.value && !bypassLocation.value) {
      toast.error('You must be at the client location or choose to bypass for this presentation.')
      return
   }

   const formData = new FormData()
   for (let i = 0; i < completionProofInput.value.files.length; i++) {
      formData.append('proof_images[]', completionProofInput.value.files[i])
   }
   formData.append('is_bypassed', bypassLocation.value ? 1 : 0)

   isCompleting.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/${jobToComplete.value.originalData.id}/complete`, formData, {
         headers: { 'Content-Type': 'multipart/form-data' }
      })
      if (res.data.success) {
         toast.success(res.data.message)
         showCompleteModal.value = false
         showDetailsModal.value = false
         fetchJobRequests(true) 
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
         await fetchJobRequests(true)
      }
   } catch(error) {
      toast.error('Failed to verify payment proof.')
   } finally {
      isApprovingProof.value = false
   }
}

const isRejectingProof = ref(false)
const rejectProof = async (termId) => {
   if (!termId) return
   const reason = window.prompt('Reason for rejecting this proof of payment:', '')
   if (reason === null) return
   if (!reason.trim()) {
      toast.error('A reason is required to reject the proof.')
      return
   }
   isRejectingProof.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/payment-terms/${termId}/reject-proof`, { reason: reason.trim() })
      if (res.data.success) {
         toast.success(res.data.message)
         await fetchJobRequests(true)
      }
   } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to reject proof.')
   } finally {
      isRejectingProof.value = false
   }
}

const isMarkingWorkDay = ref(false)
const workDayBypass = ref(false) // presentation mode: lets you mark ANY date
const workDayBypassDate = ref(new Date().toISOString().slice(0, 10))
const markWorkDay = async (reqId, workDate, worked, bypass = false) => {
   if (!reqId || !workDate || isMarkingWorkDay.value) return
   isMarkingWorkDay.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/${reqId}/daily-work-day`, { work_date: workDate, worked, bypass })
      if (res.data.success) {
         toast.success(res.data.message)
         await fetchJobRequests(true)
      }
   } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to update work day.')
   } finally {
      isMarkingWorkDay.value = false
   }
}

const markBypassDay = async (reqId, worked) => {
   if (!workDayBypassDate.value) {
      toast.error('Please pick a date first.')
      return
   }
   await markWorkDay(reqId, workDayBypassDate.value, worked, true)
}

const getWorkDays = (billing) => {
   if (!billing || !billing.billing_started_at) return []
   const workedMap = {}
   ;(billing.work_log || []).forEach((w) => { workedMap[w.date] = w.worked })
   const start = new Date(billing.billing_started_at + 'T00:00:00')
   const today = new Date()
   today.setHours(0, 0, 0, 0)
   const cursor = new Date(start)
   const days = []
   let guard = 0
   while (cursor <= today && guard < 400) {
      const iso = cursor.toISOString().slice(0, 10)
      days.push({
         date: iso,
         label: cursor.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
         worked: workedMap[iso] ?? null,
         isToday: cursor.getTime() === today.getTime(),
      })
      cursor.setDate(cursor.getDate() + 1)
      guard++
   }
   return days
}

// ---- Materials Reimbursement ----
const showMaterialsForm = ref(false)
const materialsItems = ref([{ item_name: '', quantity: 1, unit_price: null }])
const materialsProofInput = ref(null)
const isAddingMaterials = ref(false)
const isDeletingMaterials = ref(false)

const addMaterialRow = () => materialsItems.value.push({ item_name: '', quantity: 1, unit_price: null })
const removeMaterialRow = (idx) => {
   if (materialsItems.value.length > 1) materialsItems.value.splice(idx, 1)
}

const submitMaterials = async () => {
   if (!selectedJob.value) return
   const items = materialsItems.value.filter((it) => it.item_name && it.item_name.trim() && it.unit_price !== null && it.unit_price !== '' && Number(it.unit_price) >= 0)
   if (!items.length) {
      toast.error('Add at least one material with a name and price.')
      return
   }
   const formData = new FormData()
   items.forEach((it, idx) => {
      formData.append(`items[${idx}][item_name]`, it.item_name.trim())
      formData.append(`items[${idx}][quantity]`, Number(it.quantity) || 1)
      formData.append(`items[${idx}][unit_price]`, Number(it.unit_price))
   })
   if (materialsProofInput.value && materialsProofInput.value.files && materialsProofInput.value.files[0]) {
      formData.append('proof_image', materialsProofInput.value.files[0])
   }
   isAddingMaterials.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/${selectedJob.value.originalData.id}/materials`, formData, {
         headers: { 'Content-Type': 'multipart/form-data' }
      })
      if (res.data.success) {
         toast.success(res.data.message)
         showMaterialsForm.value = false
         materialsItems.value = [{ item_name: '', quantity: 1, unit_price: null }]
         if (materialsProofInput.value) materialsProofInput.value.value = null
         await fetchJobRequests(true)
      }
   } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to add materials.')
   } finally {
      isAddingMaterials.value = false
   }
}

const deleteMaterials = async (batchId) => {
   if (!selectedJob.value || !window.confirm('Delete this materials request?')) return
   isDeletingMaterials.value = true
   try {
      const res = await api.delete(`/service-provider/job-requests/materials/${batchId}`)
      if (res.data.success) {
         toast.success(res.data.message)
         await fetchJobRequests(true)
      }
   } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to delete materials.')
   } finally {
      isDeletingMaterials.value = false
   }
}

const sendReminder = async (termId) => {
   if (!termId) return
   isSendingReminder.value = true
   try {
      const res = await api.post(`/service-provider/job-requests/payment-terms/${termId}/remind`)
      if (res.data.success) {
         toast.success('Payment reminder email sent successfully to the client.')
         await fetchJobRequests(true)
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
         await fetchJobRequests(true)
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
