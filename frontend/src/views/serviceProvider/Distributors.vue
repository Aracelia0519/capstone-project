<template>
  <div class="min-h-screen font-sans bg-[#0f172a] text-slate-200">
    <Teleport to="body">
      <Toaster richColors position="top-right" :expand="false" :close-button="true" :visible-toasts="1" />
    </Teleport>

    <header class="sticky top-0 z-40 backdrop-blur-xl border-b border-slate-800/60 shadow-lg bg-slate-900/75">
      <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-6">
          <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 sm:p-2.5 bg-gradient-to-br from-violet-600 to-indigo-600 rounded-xl shadow-lg shadow-violet-500/20 shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              Distributor Network
            </h1>
            <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-slate-400 max-w-2xl leading-relaxed">
              Connect with verified material distributors, negotiate partnerships, and streamline your supply chain.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <button @click="fetchDistributors" class="flex items-center justify-center p-2.5 sm:p-3 rounded-xl bg-slate-800/50 text-slate-300 hover:text-white hover:bg-slate-700/50 border border-slate-700/50 transition-all active:scale-95 group">
              <svg :class="{ 'animate-spin': loading }" class="w-4 h-4 sm:w-5 sm:h-5 transition-transform group-hover:rotate-180 duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>

        <div class="mt-6 flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="flex p-1 bg-slate-800/50 backdrop-blur-md rounded-xl border border-slate-700/50 overflow-x-auto hide-scrollbar w-full sm:w-auto">
            <button v-for="tab in ['All', 'Connected', 'Pending', 'Disconnected']" :key="tab" @click="activeTab = tab"
              class="flex-1 sm:flex-none px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-all duration-300 relative whitespace-nowrap"
              :class="activeTab === tab ? 'text-white shadow-md bg-indigo-500/20 border border-indigo-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-700/30'">
              {{ tab }}
              <span v-if="tab === 'Pending' && pendingCount > 0" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-amber-500 rounded-full shadow-lg shadow-amber-500/30">
                {{ pendingCount }}
              </span>
              <span v-if="tab === 'Connected' && connectedCount > 0" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-emerald-500 rounded-full shadow-lg shadow-emerald-500/30">
                {{ connectedCount }}
              </span>
              <span v-if="tab === 'Disconnected' && disconnectedCount > 0" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-red-500 rounded-full shadow-lg shadow-red-500/30">
                {{ disconnectedCount }}
              </span>
            </button>
          </div>

          <div class="relative w-full sm:w-auto min-w-[280px]">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
              <svg class="h-4 w-4 sm:h-5 sm:w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input type="text" v-model="searchQuery" placeholder="Search by name, material, or location..."
              class="block w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-900/50 border border-slate-700/50 rounded-xl leading-5 text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-slate-800 transition-all text-sm sm:text-base shadow-inner" />
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
      <div v-if="loading" class="flex flex-col items-center justify-center py-20 sm:py-32">
        <div class="relative w-16 h-16 sm:w-20 sm:h-20">
          <div class="absolute inset-0 border-4 border-indigo-500/20 rounded-full"></div>
          <div class="absolute inset-0 border-4 border-indigo-500 rounded-full border-t-transparent animate-spin"></div>
        </div>
        <p class="mt-6 text-sm sm:text-base text-slate-400 font-medium tracking-wide animate-pulse">Syncing Distributor Network...</p>
      </div>

      <div v-else-if="filteredDistributors.length === 0" class="flex flex-col items-center justify-center py-20 sm:py-32 px-4 text-center">
        <div class="p-6 sm:p-8 bg-slate-800/30 rounded-full mb-6 border border-slate-700/50 shadow-inner">
          <svg class="w-12 h-12 sm:w-16 sm:h-16 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
          </svg>
        </div>
        <h3 class="text-xl sm:text-2xl font-bold text-white mb-2">No Distributors Found</h3>
        <p class="text-slate-400 text-sm sm:text-base max-w-md mx-auto leading-relaxed">
          {{ searchQuery ? `We couldn't find any match for "${searchQuery}". Try different keywords.` : "There are currently no distributors available in the network." }}
        </p>
        <button v-if="searchQuery" @click="searchQuery = ''" class="mt-6 px-6 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg transition-colors text-sm font-medium border border-slate-600">
          Clear Filters
        </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 sm:gap-8">
        <div v-for="distributor in filteredDistributors" :key="distributor.id"
          class="group bg-slate-800/40 border border-slate-700/60 rounded-2xl overflow-hidden hover:bg-slate-800/60 hover:border-slate-600 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 flex flex-col h-full relative backdrop-blur-sm">
          
          <div class="absolute top-4 right-4 z-10 flex flex-col gap-2 items-end">
            <span v-if="distributor.status === 'active'" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 backdrop-blur-md shadow-sm">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-2 animate-pulse"></span> Connected
            </span>
            <span v-else-if="distributor.status === 'pending'" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 backdrop-blur-md shadow-sm">
              <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              Awaiting Approval
            </span>
            <span v-else-if="distributor.status === 'pending_termination'" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-orange-500/10 text-orange-400 border border-orange-500/20 backdrop-blur-md shadow-sm">
              <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              Termination Pending
            </span>
            <span v-else-if="distributor.status === 'disconnected'" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/20 backdrop-blur-md shadow-sm">
              <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
              Terminated
            </span>
          </div>

          <div class="p-6 sm:p-8 flex-grow">
            <div class="flex items-start gap-4 sm:gap-5 mb-6">
              <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 border border-indigo-500/30 flex items-center justify-center shrink-0 shadow-inner">
                <span class="text-xl sm:text-2xl font-bold text-indigo-400">{{ distributor.name.charAt(0) }}</span>
              </div>
              <div class="flex-1 min-w-0 pr-16 sm:pr-24">
                <h3 class="text-lg sm:text-xl font-bold text-white truncate group-hover:text-indigo-300 transition-colors">{{ distributor.name }}</h3>
                <div class="flex items-center text-slate-400 mt-1.5 text-xs sm:text-sm">
                  <svg class="w-4 h-4 mr-1.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span class="truncate">{{ distributor.location }}</span>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div>
                <p class="text-[10px] sm:text-xs font-bold tracking-wider text-slate-500 uppercase mb-2.5">Key Material Offerings</p>
                <div class="flex flex-wrap gap-2">
                  <span v-for="(spec, index) in distributor.specialties.slice(0, 3)" :key="index"
                    class="px-2.5 sm:px-3 py-1 sm:py-1.5 bg-slate-900/80 border border-slate-700 text-slate-300 text-[10px] sm:text-xs font-medium rounded-lg shadow-sm">
                    {{ spec }}
                  </span>
                  <span v-if="distributor.specialties.length > 3" class="px-2.5 sm:px-3 py-1 sm:py-1.5 bg-slate-800 text-slate-400 text-[10px] sm:text-xs font-medium rounded-lg border border-slate-700/50">
                    +{{ distributor.specialties.length - 3 }} more
                  </span>
                  <span v-if="distributor.specialties.length === 0" class="text-xs text-slate-500 italic">No materials listed yet.</span>
                </div>
              </div>
            </div>
          </div>

          <div class="px-6 sm:px-8 py-4 sm:py-5 bg-slate-900/50 border-t border-slate-800/80 mt-auto">
            
            <button v-if="!distributor.status" @click="openModal(distributor)"
              class="w-full py-2.5 sm:py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 flex items-center justify-center gap-2">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
              Request Partnership
            </button>
            
            <button v-else-if="distributor.status === 'pending'" disabled
              class="w-full py-2.5 sm:py-3 px-4 bg-slate-800 text-slate-400 text-sm font-medium rounded-xl border border-slate-700 cursor-not-allowed flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              Review in Progress
            </button>

            <div v-else-if="distributor.status === 'pending_termination'" class="w-full flex flex-col gap-2">
              <template v-if="distributor.distributor_termination_signed_at && !distributor.sp_termination_signed_at">
                <span class="text-[10px] text-red-400 font-semibold text-center uppercase tracking-wider bg-red-500/10 py-1 rounded-md border border-red-500/20 mb-1">Distributor Requested Termination</span>
                <button @click="openTerminationReviewModal(distributor)"
                  class="w-full py-2.5 sm:py-3 px-4 bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 hover:border-red-500/50 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  Review Dist. Request
                </button>
              </template>
              
              <template v-else-if="distributor.sp_termination_signed_at && !distributor.distributor_termination_signed_at">
                <span class="text-[10px] text-orange-400 font-semibold text-center uppercase tracking-wider bg-orange-500/10 py-1 rounded-md border border-orange-500/20 mb-1">Awaiting Dist. Approval</span>
                <button v-if="distributor.termination_url" @click="downloadTermination(distributor)"
                  class="w-full py-2.5 sm:py-3 px-4 bg-slate-800 hover:bg-slate-700 text-orange-400 text-sm font-medium rounded-xl border border-slate-700 flex items-center justify-center gap-2 transition-colors">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  View Document
                </button>
              </template>
            </div>

            <div v-else-if="distributor.status === 'disconnected'" class="flex gap-2 sm:gap-3">
              <button @click="openReactivationModal(distributor)" 
                class="flex-1 py-2.5 sm:py-3 px-4 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 hover:border-emerald-500/50 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Request Reactivation
              </button>
              <button v-if="distributor.termination_url" @click="downloadTermination(distributor)" title="Download Termination Document" 
                class="p-2.5 sm:p-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all border border-slate-600 shrink-0">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
              </button>
            </div>
            
            <div v-else-if="distributor.status === 'active'" class="flex gap-2 sm:gap-3">
               <button class="flex-1 py-2.5 sm:py-3 px-3 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-xl transition-all border border-slate-600 hover:border-slate-500 flex items-center justify-center gap-1.5">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                  Procure
               </button>
               <button @click="downloadAgreement(distributor)" title="Download Agreement Document" class="p-2.5 sm:p-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all border border-slate-600 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
               </button>
               <button @click="openSpInitiatedTerminationModal(distributor)" title="Request Partnership Termination" class="p-2.5 sm:p-3 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-xl transition-all border border-red-500/30 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
               </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/80 backdrop-blur-sm p-4 sm:p-6" @mousedown.self="closeModal">
      <div class="relative bg-slate-900 border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-800 flex justify-between items-center shrink-0 bg-slate-900/95 rounded-t-2xl z-10 sticky top-0">
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Partnership Proposal</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Initiating commercial relationship with <span class="font-semibold text-indigo-400">{{ selectedDistributor?.name }}</span></p>
          </div>
          <button @click="closeModal" class="text-slate-500 hover:text-white bg-slate-800 hover:bg-slate-700 p-2 rounded-lg transition-colors focus:outline-none">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="p-5 sm:p-8 overflow-y-auto custom-scrollbar flex-1 relative">
          <div class="space-y-6 sm:space-y-8">
            <div class="bg-indigo-500/10 border border-indigo-500/20 p-4 sm:p-5 rounded-xl flex items-start gap-4">
               <svg class="w-6 h-6 text-indigo-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
               <p class="text-sm text-slate-300 leading-relaxed">By submitting this request, you agree to abide by the distributor's wholesale policies. Once approved, you will gain access to their catalog and partner-tier pricing.</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">Introductory Message (Optional)</label>
              <textarea v-model="requestMessage" rows="3" placeholder="Briefly introduce your business and expected procurement volume..."
                class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm shadow-inner resize-none"></textarea>
            </div>

            <div>
               <label class="block text-sm font-semibold text-slate-300 mb-2">Terms and Conditions</label>
               <div class="bg-slate-950 border border-slate-700 p-4 sm:p-5 rounded-xl text-sm text-slate-400 h-40 sm:h-48 overflow-y-auto custom-scrollbar shadow-inner leading-relaxed">
                  <h4 class="font-bold text-slate-200 mb-2">1. Authorization & Access</h4>
                  <p class="mb-4">The Service Provider is authorized to access the Distributor's wholesale catalog, view pricing, and submit procurement requests upon final approval of this digital agreement.</p>
                  
                  <h4 class="font-bold text-slate-200 mb-2">2. Procurement Standards</h4>
                  <p class="mb-4">All materials procured are subject to the Distributor's standard quality assurance protocols. Pricing tiers are exclusive and confidential.</p>

                  <h4 class="font-bold text-slate-200 mb-2">3. Digital Signature Binding</h4>
                  <p>By affixing your digital signature below, you certify that you are an authorized representative of your entity and agree to enter into a legally binding commercial partnership.</p>
               </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                 <label class="block text-sm font-semibold text-slate-300">Your Digital Signature <span class="text-red-400">*</span></label>
                 <button @click="clearSignature" class="text-xs sm:text-sm text-indigo-400 hover:text-indigo-300 font-medium transition-colors">Clear Signature</button>
              </div>
              <div class="border border-slate-700 rounded-xl bg-white overflow-hidden relative shadow-inner">
                <canvas ref="signaturePad" width="600" height="200" class="w-full h-[150px] sm:h-[200px] touch-none cursor-crosshair"
                  @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                  @touchstart.prevent="startDrawingTouch" @touchmove.prevent="drawTouch" @touchend.prevent="stopDrawing"></canvas>
                <div v-if="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                  <span class="text-slate-300 text-lg font-medium tracking-widest uppercase">Sign Here</span>
                </div>
              </div>
            </div>

            <label class="flex items-start gap-3 sm:gap-4 p-4 rounded-xl border border-slate-700/50 bg-slate-800/20 cursor-pointer group hover:bg-slate-800/40 transition-colors">
              <div class="relative flex items-center justify-center shrink-0 mt-0.5">
                 <input type="checkbox" v-model="agreedToTerms" class="peer appearance-none w-5 h-5 border-2 border-slate-500 rounded bg-slate-900 checked:bg-indigo-500 checked:border-indigo-500 transition-colors cursor-pointer" />
                 <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                 </svg>
              </div>
              <span class="text-sm text-slate-300 leading-relaxed group-hover:text-slate-200 transition-colors">
                I hereby affix my signature and agree to the partnership terms. I understand this document will be sent to the distributor for final execution.
              </span>
            </label>
          </div>
        </div>

        <div class="px-5 sm:px-8 py-4 sm:py-5 border-t border-slate-800 bg-slate-900/95 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4 shrink-0 rounded-b-2xl">
          <button @click="closeModal" :disabled="isSubmitting" class="w-full sm:w-auto px-6 py-2.5 sm:py-3 text-slate-300 bg-transparent hover:bg-slate-800 border border-slate-700 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
            Cancel
          </button>
          <button @click="submitRequest" :disabled="!isFormValid || isSubmitting"
            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg v-if="isSubmitting" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span v-if="isSubmitting">Submitting...</span>
            <span v-else>Submit Formal Request</span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="showTerminationModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/80 backdrop-blur-sm p-4 sm:p-6" @mousedown.self="closeTerminationModal">
      <div class="relative bg-slate-900 border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-800 flex justify-between items-center shrink-0 bg-slate-900/95 rounded-t-2xl z-10 sticky top-0">
          <div class="flex items-center gap-3">
             <div class="p-2 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
             </div>
            <div>
              <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Review Termination</h2>
              <p class="text-xs sm:text-sm text-slate-400 mt-1">Termination requested by <span class="font-semibold text-red-400">{{ selectedTermination?.name }}</span></p>
            </div>
          </div>
          <button @click="closeTerminationModal" class="text-slate-500 hover:text-white bg-slate-800 hover:bg-slate-700 p-2 rounded-lg transition-colors focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="p-5 sm:p-8 overflow-y-auto custom-scrollbar flex-1 relative">
          <div class="space-y-6 sm:space-y-8">
            <div class="bg-red-500/10 border border-red-500/20 p-4 sm:p-5 rounded-xl flex items-start gap-4">
               <p class="text-sm text-slate-300 leading-relaxed">The distributor has submitted a formal request to terminate this commercial partnership. Please review the official document below. You may choose to approve the termination (officially revoking access) or decline it.</p>
            </div>

            <div class="space-y-3">
              <label class="block text-sm font-semibold text-slate-300 flex items-center justify-between">
                <span>Official Termination Request Document</span>
              </label>
              <div class="h-[250px] sm:h-[350px] border border-slate-700 rounded-xl overflow-hidden bg-white shadow-inner">
                <iframe 
                  v-if="selectedTermination?.termination_url" 
                  :src="selectedTermination.termination_url" 
                  class="w-full h-full border-0"
                ></iframe>
                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 bg-slate-900">
                  <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  Document not available.
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                 <label class="block text-sm font-semibold text-slate-300">Your Signature <span class="text-red-400">*</span></label>
                 <button @click="clearTerminationSignature" class="text-xs sm:text-sm text-indigo-400 hover:text-indigo-300 font-medium transition-colors">Clear Pad</button>
              </div>
              <div class="border border-slate-700 rounded-xl bg-white overflow-hidden relative shadow-inner">
                <canvas ref="termSignaturePad" width="600" height="150" class="w-full h-[150px] touch-none cursor-crosshair"
                  @mousedown="startTermDrawing" @mousemove="drawTerm" @mouseup="stopTermDrawing" @mouseleave="stopTermDrawing"
                  @touchstart.prevent="startTermDrawingTouch" @touchmove.prevent="drawTermTouch" @touchend.prevent="stopTermDrawing"></canvas>
                <div v-if="!hasTermSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                  <span class="text-slate-300 text-lg font-medium tracking-widest uppercase">Sign Here to Approve Termination</span>
                </div>
              </div>
            </div>

            <label class="flex items-start gap-3 sm:gap-4 p-4 rounded-xl border border-slate-700/50 bg-slate-800/20 cursor-pointer group hover:bg-slate-800/40 transition-colors">
              <div class="relative flex items-center justify-center shrink-0 mt-0.5">
                 <input type="checkbox" v-model="agreedToTermination" class="peer appearance-none w-5 h-5 border-2 border-slate-500 rounded bg-slate-900 checked:bg-red-500 checked:border-red-500 transition-colors cursor-pointer" />
                 <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                 </svg>
              </div>
              <span class="text-sm text-slate-300 leading-relaxed group-hover:text-slate-200 transition-colors">
                I understand that by signing and approving, I am officially dissolving this partnership and my wholesale access will be revoked.
              </span>
            </label>
          </div>
        </div>

        <div class="px-5 sm:px-8 py-4 sm:py-5 border-t border-slate-800 bg-slate-900/95 flex flex-col-reverse sm:flex-row justify-between gap-3 sm:gap-4 shrink-0 rounded-b-2xl">
          <button @click="submitDeclineTermination" :disabled="isSubmittingTermination" 
            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 text-slate-300 bg-transparent hover:bg-slate-800 border border-slate-700 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
            Decline Request
          </button>
          <button @click="submitApproveTermination" :disabled="!agreedToTermination || !hasTermSignature || isSubmittingTermination"
            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-red-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg v-if="isSubmittingTermination" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span v-if="isSubmittingTermination">Processing...</span>
            <span v-else>Approve Termination</span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="showSpInitiatedModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/80 backdrop-blur-sm p-4 sm:p-6" @mousedown.self="closeSpInitiatedTerminationModal">
      <div class="relative bg-slate-900 border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-800 flex justify-between items-center shrink-0 bg-slate-900/95 rounded-t-2xl z-10 sticky top-0">
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
              <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              Request Partnership Termination
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Initiating termination with <span class="font-semibold text-red-400">{{ selectedSpTermination?.name }}</span></p>
          </div>
          <button @click="closeSpInitiatedTerminationModal" class="text-slate-500 hover:text-white bg-slate-800 hover:bg-slate-700 p-2 rounded-lg transition-colors focus:outline-none">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="p-5 sm:p-8 overflow-y-auto custom-scrollbar flex-1 relative">
          <div class="space-y-6 sm:space-y-8">
            <div class="bg-red-500/10 border border-red-500/20 p-4 sm:p-5 rounded-xl flex items-start gap-4">
               <p class="text-sm text-slate-300 leading-relaxed">This will generate a formal Termination Request Document. Once the distributor approves, your access to their catalog will be permanently revoked.</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">Termination Reason / Message (Optional)</label>
              <textarea v-model="spTerminationMessage" rows="3" placeholder="Briefly explain your reason for terminating the partnership..."
                class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all text-sm shadow-inner resize-none"></textarea>
            </div>

            <div>
               <label class="block text-sm font-semibold text-slate-300 mb-2">Terms of Revocation</label>
               <div class="bg-slate-950 border border-slate-700 p-4 sm:p-5 rounded-xl text-sm text-slate-400 h-40 sm:h-48 overflow-y-auto custom-scrollbar shadow-inner leading-relaxed">
                  <h4 class="font-bold text-slate-200 mb-2">1. Access Revocation</h4>
                  <p class="mb-4">Upon final approval by the Distributor, access to the Distributor's wholesale catalog and partner-tier pricing will be revoked.</p>
                  
                  <h4 class="font-bold text-slate-200 mb-2">2. Transaction Fulfillment</h4>
                  <p class="mb-4">Any pending transactions initiated prior to this date shall be processed normally, subject to standard review.</p>

                  <h4 class="font-bold text-slate-200 mb-2">3. Mutual Confidentiality</h4>
                  <p>Both parties are reminded of their obligation to protect proprietary business data obtained during the partnership period.</p>
               </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                 <label class="block text-sm font-semibold text-slate-300">Your Digital Signature <span class="text-red-400">*</span></label>
                 <button @click="clearSpTermSignature" class="text-xs sm:text-sm text-indigo-400 hover:text-indigo-300 font-medium transition-colors">Clear Signature</button>
              </div>
              <div class="border border-slate-700 rounded-xl bg-white overflow-hidden relative shadow-inner">
                <canvas ref="spTermSignaturePad" width="600" height="200" class="w-full h-[150px] sm:h-[200px] touch-none cursor-crosshair"
                  @mousedown="startSpTermDrawing" @mousemove="drawSpTerm" @mouseup="stopSpTermDrawing" @mouseleave="stopSpTermDrawing"
                  @touchstart.prevent="startSpTermDrawingTouch" @touchmove.prevent="drawSpTermTouch" @touchend.prevent="stopSpTermDrawing"></canvas>
                <div v-if="!hasSpTermSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                  <span class="text-slate-300 text-lg font-medium tracking-widest uppercase">Sign Here to Terminate</span>
                </div>
              </div>
            </div>

            <label class="flex items-start gap-3 sm:gap-4 p-4 rounded-xl border border-slate-700/50 bg-slate-800/20 cursor-pointer group hover:bg-slate-800/40 transition-colors">
              <div class="relative flex items-center justify-center shrink-0 mt-0.5">
                 <input type="checkbox" v-model="spTerminationAgreedToTerms" class="peer appearance-none w-5 h-5 border-2 border-slate-500 rounded bg-slate-900 checked:bg-red-500 checked:border-red-500 transition-colors cursor-pointer" />
                 <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                 </svg>
              </div>
              <span class="text-sm text-slate-300 leading-relaxed group-hover:text-slate-200 transition-colors">
                I hereby affix my signature and formally request the termination of this partnership. This document will be sent to the distributor for final execution.
              </span>
            </label>
          </div>
        </div>

        <div class="px-5 sm:px-8 py-4 sm:py-5 border-t border-slate-800 bg-slate-900/95 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4 shrink-0 rounded-b-2xl">
          <button @click="closeSpInitiatedTerminationModal" :disabled="isSubmittingSpTermination" class="w-full sm:w-auto px-6 py-2.5 sm:py-3 text-slate-300 bg-transparent hover:bg-slate-800 border border-slate-700 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
            Cancel
          </button>
          <button @click="submitSpInitiatedTermination" :disabled="!isFormValidSpTermination || isSubmittingSpTermination"
            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-red-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg v-if="isSubmittingSpTermination" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span v-if="isSubmittingSpTermination">Submitting...</span>
            <span v-else>Send Termination Request</span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="showReactivationModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/80 backdrop-blur-sm p-4 sm:p-6" @mousedown.self="closeReactivationModal">
      <div class="relative bg-slate-900 border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-800 flex justify-between items-center shrink-0 bg-slate-900/95 rounded-t-2xl z-10 sticky top-0">
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
              <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
              Request Partnership Reactivation
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Proposing to restore commercial relationship with <span class="font-semibold text-emerald-400">{{ selectedReactivation?.name }}</span></p>
          </div>
          <button @click="closeReactivationModal" class="text-slate-500 hover:text-white bg-slate-800 hover:bg-slate-700 p-2 rounded-lg transition-colors focus:outline-none">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="p-5 sm:p-8 overflow-y-auto custom-scrollbar flex-1 relative">
          <div class="space-y-6 sm:space-y-8">
            <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 sm:p-5 rounded-xl flex items-start gap-4">
               <svg class="w-6 h-6 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
               <p class="text-sm text-slate-300 leading-relaxed">This will generate a fresh Partnership Agreement. Once the distributor approves, your previous access to their wholesale catalog and partner-tier pricing will be fully restored.</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-300 mb-2">Reactivation Message (Optional)</label>
              <textarea v-model="reactivationMessage" rows="3" placeholder="Briefly explain your intent to reconnect..."
                class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-sm shadow-inner resize-none"></textarea>
            </div>

            <div>
               <label class="block text-sm font-semibold text-slate-300 mb-2">Terms and Conditions</label>
               <div class="bg-slate-950 border border-slate-700 p-4 sm:p-5 rounded-xl text-sm text-slate-400 h-40 sm:h-48 overflow-y-auto custom-scrollbar shadow-inner leading-relaxed">
                  <h4 class="font-bold text-slate-200 mb-2">1. Authorization & Access</h4>
                  <p class="mb-4">The Service Provider is authorized to access the Distributor's wholesale catalog, view pricing, and submit procurement requests upon final approval of this digital agreement.</p>
                  
                  <h4 class="font-bold text-slate-200 mb-2">2. Procurement Standards</h4>
                  <p class="mb-4">All materials procured are subject to the Distributor's standard quality assurance protocols. Pricing tiers are exclusive and confidential.</p>

                  <h4 class="font-bold text-slate-200 mb-2">3. Digital Signature Binding</h4>
                  <p>By affixing your digital signature below, you certify that you are an authorized representative of your entity and agree to enter into a legally binding commercial partnership.</p>
               </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                 <label class="block text-sm font-semibold text-slate-300">Your Digital Signature <span class="text-red-400">*</span></label>
                 <button @click="clearReactivationSignature" class="text-xs sm:text-sm text-emerald-400 hover:text-emerald-300 font-medium transition-colors">Clear Signature</button>
              </div>
              <div class="border border-slate-700 rounded-xl bg-white overflow-hidden relative shadow-inner">
                <canvas ref="reactivationSignaturePad" width="600" height="200" class="w-full h-[150px] sm:h-[200px] touch-none cursor-crosshair"
                  @mousedown="startReactivationDrawing" @mousemove="drawReactivation" @mouseup="stopReactivationDrawing" @mouseleave="stopReactivationDrawing"
                  @touchstart.prevent="startReactivationDrawingTouch" @touchmove.prevent="drawReactivationTouch" @touchend.prevent="stopReactivationDrawing"></canvas>
                <div v-if="!hasReactivationSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center">
                  <span class="text-slate-300 text-lg font-medium tracking-widest uppercase">Sign Here to Reactivate</span>
                </div>
              </div>
            </div>

            <label class="flex items-start gap-3 sm:gap-4 p-4 rounded-xl border border-slate-700/50 bg-slate-800/20 cursor-pointer group hover:bg-slate-800/40 transition-colors">
              <div class="relative flex items-center justify-center shrink-0 mt-0.5">
                 <input type="checkbox" v-model="reactivationAgreedToTerms" class="peer appearance-none w-5 h-5 border-2 border-slate-500 rounded bg-slate-900 checked:bg-emerald-500 checked:border-emerald-500 transition-colors cursor-pointer" />
                 <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                 </svg>
              </div>
              <span class="text-sm text-slate-300 leading-relaxed group-hover:text-slate-200 transition-colors">
                I hereby affix my signature and agree to the partnership terms to request reactivation. This document will be sent to the distributor for final execution.
              </span>
            </label>
          </div>
        </div>

        <div class="px-5 sm:px-8 py-4 sm:py-5 border-t border-slate-800 bg-slate-900/95 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4 shrink-0 rounded-b-2xl">
          <button @click="closeReactivationModal" :disabled="isSubmittingReactivation" class="w-full sm:w-auto px-6 py-2.5 sm:py-3 text-slate-300 bg-transparent hover:bg-slate-800 border border-slate-700 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
            Cancel
          </button>
          <button @click="submitReactivation" :disabled="!isFormValidReactivation || isSubmittingReactivation"
            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg v-if="isSubmittingReactivation" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span v-if="isSubmittingReactivation">Submitting...</span>
            <span v-else>Send Reactivation Request</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import api from '@/utils/axios';
import { Toaster, toast } from 'vue-sonner';

export default {
  name: 'DistributorList',
  components: { Toaster },
  data() {
    return {
      distributors: [],
      loading: true,
      activeTab: 'All',
      searchQuery: '',

      // Partnership Request Modal State
      showModal: false,
      selectedDistributor: null,
      requestMessage: '',
      agreedToTerms: false,
      isSubmitting: false,
      hasSignature: false,
      isDrawing: false,
      ctx: null,

      // Termination Review Modal State (Distributor Initiated)
      showTerminationModal: false,
      selectedTermination: null,
      agreedToTermination: false,
      isSubmittingTermination: false,
      hasTermSignature: false,
      isTermDrawing: false,
      termCtx: null,

      // SP Initiated Termination Modal State
      showSpInitiatedModal: false,
      selectedSpTermination: null,
      spTerminationMessage: '',
      spTerminationAgreedToTerms: false,
      isSubmittingSpTermination: false,
      hasSpTermSignature: false,
      isSpTermDrawing: false,
      spTermCtx: null,

      // Reactivation Modal State
      showReactivationModal: false,
      selectedReactivation: null,
      reactivationMessage: '',
      isSubmittingReactivation: false,
      reactivationAgreedToTerms: false,
      hasReactivationSignature: false,
      isReactivationDrawing: false,
      reactivationCtx: null,
    };
  },
  computed: {
    pendingCount() { return this.distributors.filter(d => d.status === 'pending').length; },
    connectedCount() { return this.distributors.filter(d => d.status === 'active' || d.status === 'pending_termination').length; },
    disconnectedCount() { return this.distributors.filter(d => d.status === 'disconnected').length; },
    filteredDistributors() {
      let filtered = this.distributors;
      if (this.activeTab === 'Connected') {
        filtered = filtered.filter(d => d.status === 'active' || d.status === 'pending_termination');
      } else if (this.activeTab === 'Pending') {
        filtered = filtered.filter(d => d.status === 'pending');
      } else if (this.activeTab === 'Disconnected') {
        filtered = filtered.filter(d => d.status === 'disconnected');
      }
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(d => 
          d.name.toLowerCase().includes(query) || 
          d.location.toLowerCase().includes(query) ||
          d.specialties.some(s => s.toLowerCase().includes(query))
        );
      }
      return filtered;
    },
    isFormValid() { return this.agreedToTerms && this.hasSignature; },
    isFormValidReactivation() { return this.reactivationAgreedToTerms && this.hasReactivationSignature; },
    isFormValidSpTermination() { return this.spTerminationAgreedToTerms && this.hasSpTermSignature; }
  },
  created() { this.fetchDistributors(); },
  methods: {
    async fetchDistributors() {
      this.loading = true;
      try {
        const response = await api.get('/service-provider/distributors');
        if (response.data.success) {
          this.distributors = response.data.data.map(dist => ({
             id: dist.id,
             partnership_id: dist.partnership_id,
             name: dist.name,
             location: dist.location,
             specialties: dist.specialties,
             status: dist.status,
             agreement_url: dist.agreement_url,
             termination_url: dist.termination_url,
             distributor_termination_signed_at: dist.distributor_termination_signed_at,
             sp_termination_signed_at: dist.sp_termination_signed_at
          }));
        }
      } catch (error) {
        toast.error('Failed to load network data', { description: 'Please check your connection and try again.' });
      } finally {
        this.loading = false;
      }
    },

    // --- Partnership Request Logic ---
    openModal(distributor) {
      this.selectedDistributor = distributor;
      this.showModal = true;
      document.body.style.overflow = 'hidden';
      this.$nextTick(() => { this.initSignaturePad(); });
    },
    closeModal() {
      this.showModal = false;
      this.selectedDistributor = null;
      this.requestMessage = '';
      this.agreedToTerms = false;
      this.hasSignature = false;
      document.body.style.overflow = '';
    },
    initSignaturePad() {
      const canvas = this.$refs.signaturePad;
      if (!canvas) return;
      this.ctx = canvas.getContext('2d');
      this.ctx.lineWidth = 2.5;
      this.ctx.lineCap = 'round';
      this.ctx.strokeStyle = '#000000';
      this.clearSignature();
    },
    startDrawing(e) { this.isDrawing = true; this.hasSignature = true; this.draw(e); },
    draw(e) {
      if (!this.isDrawing) return;
      const rect = this.$refs.signaturePad.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      this.ctx.lineTo(x, y);
      this.ctx.stroke();
      this.ctx.beginPath();
      this.ctx.moveTo(x, y);
    },
    stopDrawing() { this.isDrawing = false; this.ctx.beginPath(); },
    startDrawingTouch(e) {
      const touch = e.touches[0];
      const rect = this.$refs.signaturePad.getBoundingClientRect();
      this.isDrawing = true;
      this.hasSignature = true;
      this.ctx.beginPath();
      this.ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    drawTouch(e) {
      if (!this.isDrawing) return;
      const touch = e.touches[0];
      const rect = this.$refs.signaturePad.getBoundingClientRect();
      this.ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
      this.ctx.stroke();
      this.ctx.beginPath();
      this.ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    clearSignature() {
      const canvas = this.$refs.signaturePad;
      if(!canvas || !this.ctx) return;
      this.ctx.clearRect(0, 0, canvas.width, canvas.height);
      this.hasSignature = false;
      this.ctx.beginPath();
    },
    downloadAgreement(distributor) {
       if(distributor.agreement_url) {
          window.open(distributor.agreement_url, '_blank');
       }
    },
    downloadTermination(distributor) {
       if(distributor.termination_url) {
          window.open(distributor.termination_url, '_blank');
       }
    },
    async submitRequest() {
      if (!this.isFormValid) return;
      this.isSubmitting = true;
      
      const signatureBase64 = this.$refs.signaturePad.toDataURL('image/png');

      try {
        const response = await api.post('/service-provider/distributors/request', {
          distributor_id: this.selectedDistributor.id,
          request_message: this.requestMessage,
          signature: signatureBase64
        });

        if (response.data.success) {
          const index = this.distributors.findIndex(d => d.id === this.selectedDistributor.id);
          if (index !== -1) this.distributors[index].status = 'pending';
          
          toast.success('Partnership Proposal Sent', {
            description: `Official request and signed agreement successfully submitted to ${this.selectedDistributor.name}.`,
            duration: 4000,
          });
          this.closeModal();
        }
      } catch (error) {
        toast.error('Submission Failed', {
          description: error.response?.data?.message || 'There was an issue processing your agreement. Please try again.',
          duration: 4000,
        });
      } finally {
        this.isSubmitting = false;
      }
    },

    // --- Reactivation Logic ---
    openReactivationModal(distributor) {
      this.selectedReactivation = distributor;
      this.showReactivationModal = true;
      document.body.style.overflow = 'hidden';
      this.$nextTick(() => { this.initReactivationSignaturePad(); });
    },
    closeReactivationModal() {
      this.showReactivationModal = false;
      this.selectedReactivation = null;
      this.reactivationMessage = '';
      this.reactivationAgreedToTerms = false;
      this.hasReactivationSignature = false;
      document.body.style.overflow = '';
    },
    initReactivationSignaturePad() {
      const canvas = this.$refs.reactivationSignaturePad;
      if (!canvas) return;
      this.reactivationCtx = canvas.getContext('2d');
      this.reactivationCtx.lineWidth = 2.5;
      this.reactivationCtx.lineCap = 'round';
      this.reactivationCtx.strokeStyle = '#000000';
      this.clearReactivationSignature();
    },
    startReactivationDrawing(e) { this.isReactivationDrawing = true; this.hasReactivationSignature = true; this.drawReactivation(e); },
    drawReactivation(e) {
      if (!this.isReactivationDrawing) return;
      const rect = this.$refs.reactivationSignaturePad.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      this.reactivationCtx.lineTo(x, y);
      this.reactivationCtx.stroke();
      this.reactivationCtx.beginPath();
      this.reactivationCtx.moveTo(x, y);
    },
    stopReactivationDrawing() { this.isReactivationDrawing = false; this.reactivationCtx.beginPath(); },
    startReactivationDrawingTouch(e) {
      const touch = e.touches[0];
      const rect = this.$refs.reactivationSignaturePad.getBoundingClientRect();
      this.isReactivationDrawing = true;
      this.hasReactivationSignature = true;
      this.reactivationCtx.beginPath();
      this.reactivationCtx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    drawReactivationTouch(e) {
      if (!this.isReactivationDrawing) return;
      const touch = e.touches[0];
      const rect = this.$refs.reactivationSignaturePad.getBoundingClientRect();
      this.reactivationCtx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
      this.reactivationCtx.stroke();
      this.reactivationCtx.beginPath();
      this.reactivationCtx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    clearReactivationSignature() {
      const canvas = this.$refs.reactivationSignaturePad;
      if(!canvas || !this.reactivationCtx) return;
      this.reactivationCtx.clearRect(0, 0, canvas.width, canvas.height);
      this.hasReactivationSignature = false;
      this.reactivationCtx.beginPath();
    },
    async submitReactivation() {
      if (!this.isFormValidReactivation || !this.selectedReactivation) return;
      this.isSubmittingReactivation = true;

      const signatureBase64 = this.$refs.reactivationSignaturePad.toDataURL('image/png');

      try {
        const response = await api.post(`/service-provider/distributors/${this.selectedReactivation.partnership_id}/request-reactivation`, {
          message: this.reactivationMessage,
          signature: signatureBase64
        });

        if (response.data.success) {
          const index = this.distributors.findIndex(d => d.partnership_id === this.selectedReactivation.partnership_id);
          if (index !== -1) {
            this.distributors[index].status = 'pending';
            this.distributors[index].termination_url = null;
          }

          toast.success('Reactivation Requested', {
            description: `A signed request to restore the partnership has been sent to ${this.selectedReactivation.name}.`,
            duration: 4000,
          });
          
          this.activeTab = 'Pending';
          this.closeReactivationModal();
        }
      } catch (error) {
        toast.error('Reactivation Failed', {
          description: error.response?.data?.message || 'There was an issue sending your request. Please try again.',
          duration: 4000,
        });
      } finally {
        this.isSubmittingReactivation = false;
      }
    },

    // --- Termination Review Logic (Distributor Initiated) ---
    openTerminationReviewModal(distributor) {
      this.selectedTermination = distributor;
      this.showTerminationModal = true;
      document.body.style.overflow = 'hidden';
      this.$nextTick(() => { this.initTermSignaturePad(); });
    },
    closeTerminationModal() {
      this.showTerminationModal = false;
      this.selectedTermination = null;
      this.agreedToTermination = false;
      this.hasTermSignature = false;
      document.body.style.overflow = '';
    },
    initTermSignaturePad() {
      const canvas = this.$refs.termSignaturePad;
      if (!canvas) return;
      this.termCtx = canvas.getContext('2d');
      this.termCtx.lineWidth = 2.5;
      this.termCtx.lineCap = 'round';
      this.termCtx.strokeStyle = '#000000';
      this.clearTerminationSignature();
    },
    startTermDrawing(e) { this.isTermDrawing = true; this.hasTermSignature = true; this.drawTerm(e); },
    drawTerm(e) {
      if (!this.isTermDrawing) return;
      const rect = this.$refs.termSignaturePad.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      this.termCtx.lineTo(x, y);
      this.termCtx.stroke();
      this.termCtx.beginPath();
      this.termCtx.moveTo(x, y);
    },
    stopTermDrawing() { this.isTermDrawing = false; this.termCtx.beginPath(); },
    startTermDrawingTouch(e) {
      const touch = e.touches[0];
      const rect = this.$refs.termSignaturePad.getBoundingClientRect();
      this.isTermDrawing = true;
      this.hasTermSignature = true;
      this.termCtx.beginPath();
      this.termCtx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    drawTermTouch(e) {
      if (!this.isTermDrawing) return;
      const touch = e.touches[0];
      const rect = this.$refs.termSignaturePad.getBoundingClientRect();
      this.termCtx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
      this.termCtx.stroke();
      this.termCtx.beginPath();
      this.termCtx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    clearTerminationSignature() {
      const canvas = this.$refs.termSignaturePad;
      if(!canvas || !this.termCtx) return;
      this.termCtx.clearRect(0, 0, canvas.width, canvas.height);
      this.hasTermSignature = false;
      this.termCtx.beginPath();
    },
    async submitApproveTermination() {
      if (!this.agreedToTermination || !this.hasTermSignature) return;
      this.isSubmittingTermination = true;
      
      const signatureBase64 = this.$refs.termSignaturePad.toDataURL('image/png');

      try {
        const response = await api.post(`/service-provider/distributors/${this.selectedTermination.partnership_id}/approve-termination`, {
          signature: signatureBase64
        });

        if (response.data.success) {
          const index = this.distributors.findIndex(d => d.partnership_id === this.selectedTermination.partnership_id);
          if (index !== -1) {
             this.distributors[index].status = 'disconnected';
             this.distributors[index].termination_url = response.data.termination_url;
          }
          
          toast.success('Termination Approved', {
            description: `The partnership with ${this.selectedTermination.name} has been officially terminated.`,
            duration: 4000,
          });
          this.closeTerminationModal();
        }
      } catch (error) {
        toast.error('Approval Failed', {
          description: error.response?.data?.message || 'There was an issue processing the termination. Please try again.',
          duration: 4000,
        });
      } finally {
        this.isSubmittingTermination = false;
      }
    },
    async submitDeclineTermination() {
      this.isSubmittingTermination = true;
      try {
        const response = await api.post(`/service-provider/distributors/${this.selectedTermination.partnership_id}/decline-termination`);

        if (response.data.success) {
          const index = this.distributors.findIndex(d => d.partnership_id === this.selectedTermination.partnership_id);
          if (index !== -1) this.distributors[index].status = 'active';
          
          toast.success('Termination Declined', {
            description: `The partnership with ${this.selectedTermination.name} remains active.`,
            duration: 4000,
          });
          this.closeTerminationModal();
        }
      } catch (error) {
        toast.error('Decline Failed', {
          description: error.response?.data?.message || 'There was an issue declining the termination. Please try again.',
          duration: 4000,
        });
      } finally {
        this.isSubmittingTermination = false;
      }
    },

    // --- SP Initiated Termination Logic ---
    openSpInitiatedTerminationModal(distributor) {
      this.selectedSpTermination = distributor;
      this.showSpInitiatedModal = true;
      document.body.style.overflow = 'hidden';
      this.$nextTick(() => { this.initSpTermSignaturePad(); });
    },
    closeSpInitiatedTerminationModal() {
      this.showSpInitiatedModal = false;
      this.selectedSpTermination = null;
      this.spTerminationMessage = '';
      this.spTerminationAgreedToTerms = false;
      this.hasSpTermSignature = false;
      document.body.style.overflow = '';
    },
    initSpTermSignaturePad() {
      const canvas = this.$refs.spTermSignaturePad;
      if (!canvas) return;
      this.spTermCtx = canvas.getContext('2d');
      this.spTermCtx.lineWidth = 2.5;
      this.spTermCtx.lineCap = 'round';
      this.spTermCtx.strokeStyle = '#000000';
      this.clearSpTermSignature();
    },
    startSpTermDrawing(e) { this.isSpTermDrawing = true; this.hasSpTermSignature = true; this.drawSpTerm(e); },
    drawSpTerm(e) {
      if (!this.isSpTermDrawing) return;
      const rect = this.$refs.spTermSignaturePad.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      this.spTermCtx.lineTo(x, y);
      this.spTermCtx.stroke();
      this.spTermCtx.beginPath();
      this.spTermCtx.moveTo(x, y);
    },
    stopSpTermDrawing() { this.isSpTermDrawing = false; this.spTermCtx.beginPath(); },
    startSpTermDrawingTouch(e) {
      const touch = e.touches[0];
      const rect = this.$refs.spTermSignaturePad.getBoundingClientRect();
      this.isSpTermDrawing = true;
      this.hasSpTermSignature = true;
      this.spTermCtx.beginPath();
      this.spTermCtx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    drawSpTermTouch(e) {
      if (!this.isSpTermDrawing) return;
      const touch = e.touches[0];
      const rect = this.$refs.spTermSignaturePad.getBoundingClientRect();
      this.spTermCtx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
      this.spTermCtx.stroke();
      this.spTermCtx.beginPath();
      this.spTermCtx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    clearSpTermSignature() {
      const canvas = this.$refs.spTermSignaturePad;
      if(!canvas || !this.spTermCtx) return;
      this.spTermCtx.clearRect(0, 0, canvas.width, canvas.height);
      this.hasSpTermSignature = false;
      this.spTermCtx.beginPath();
    },
    async submitSpInitiatedTermination() {
      if (!this.isFormValidSpTermination || !this.selectedSpTermination) return;
      this.isSubmittingSpTermination = true;

      const signatureBase64 = this.$refs.spTermSignaturePad.toDataURL('image/png');

      try {
        const response = await api.post(`/service-provider/distributors/${this.selectedSpTermination.partnership_id}/request-termination`, {
          message: this.spTerminationMessage,
          signature: signatureBase64
        });

        if (response.data.success) {
          const index = this.distributors.findIndex(d => d.partnership_id === this.selectedSpTermination.partnership_id);
          if (index !== -1) {
            this.distributors[index].status = 'pending_termination';
            this.distributors[index].sp_termination_signed_at = response.data.signed_at; // to reflect UI logic
          }

          toast.success('Termination Requested', {
            description: `A formal request to terminate the partnership has been sent to ${this.selectedSpTermination.name}.`,
            duration: 4000,
          });
          
          this.closeSpInitiatedTerminationModal();
        }
      } catch (error) {
        toast.error('Termination Failed', {
          description: error.response?.data?.message || 'There was an issue sending your termination request. Please try again.',
          duration: 4000,
        });
      } finally {
        this.isSubmittingSpTermination = false;
      }
    },
  },
  beforeUnmount() { document.body.style.overflow = ''; }
}
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #0f172a; }
::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>