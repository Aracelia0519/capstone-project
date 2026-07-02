<template>
  <div class="min-h-screen font-sans bg-[#0f172a] text-slate-200">
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
            <button v-for="tab in ['All', 'Connected', 'Negotiating', 'Expired']" :key="tab" @click="activeTab = tab"
              class="flex-1 sm:flex-none px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium transition-all duration-300 relative whitespace-nowrap"
              :class="activeTab === tab ? 'text-white shadow-md bg-indigo-500/20 border border-indigo-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-700/30'">
              {{ tab }}
              <span v-if="tab === 'Negotiating' && negotiatingCount > 0" class="ml-2 inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-amber-500 rounded-full shadow-lg shadow-amber-500/30">
                {{ negotiatingCount }}
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
            <span v-else-if="distributor.status === 'pending' || distributor.status === 'negotiating'" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20 backdrop-blur-md shadow-sm">
              <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              {{ distributor.last_proposed_by === 'distributor' ? 'Action Required' : 'Awaiting Review' }}
            </span>
            <span v-else-if="distributor.status === 'expired'" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-500/10 text-slate-400 border border-slate-500/20 backdrop-blur-md shadow-sm">
              <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
              Contract Expired
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
              
              <div v-if="distributor.status === 'active' || distributor.status === 'expired'">
                <p class="text-[10px] sm:text-xs font-bold tracking-wider text-slate-500 uppercase mb-1">Contract End Date</p>
                <p class="text-sm font-semibold" :class="distributor.status === 'expired' || isNearExpiry(distributor.contract_end_date) ? 'text-red-400' : 'text-slate-300'">
                  {{ formatDate(distributor.contract_end_date) }}
                  <span v-if="isNearExpiry(distributor.contract_end_date) && distributor.status === 'active'" class="ml-2 text-[10px] bg-red-500/20 text-red-400 px-2 py-0.5 rounded-full border border-red-500/30">Expiring Soon</span>
                </p>
              </div>
            </div>
          </div>

          <div class="px-6 sm:px-8 py-4 sm:py-5 bg-slate-900/50 border-t border-slate-800/80 mt-auto">
            <!-- View Products button (always visible) -->
            <button @click="goToProducts(distributor.id)"
              class="w-full mb-2 py-2 sm:py-2.5 px-4 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 hover:border-indigo-500/50 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              View Products
            </button>
            
            <button v-if="!distributor.status" @click="openModal(distributor)"
              class="w-full py-2.5 sm:py-3 px-4 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-all shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 flex items-center justify-center gap-2">
              <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
              Request Partnership
            </button>
            
            <div v-else-if="distributor.status === 'pending' || distributor.status === 'negotiating'">
              <button v-if="distributor.last_proposed_by === 'distributor'" @click="openNegotiationModal(distributor)"
                class="w-full py-2.5 sm:py-3 px-4 bg-amber-600/20 hover:bg-amber-600/30 text-amber-400 border border-amber-500/30 hover:border-amber-500/50 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                Review Proposal
              </button>
              <button v-else disabled
                class="w-full py-2.5 sm:py-3 px-4 bg-slate-800 text-slate-400 text-sm font-medium rounded-xl border border-slate-700 cursor-not-allowed flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Review in Progress
              </button>
            </div>

            <div v-else-if="distributor.status === 'expired'" class="flex gap-2 sm:gap-3">
              <button @click="openRenewalModal(distributor)" 
                class="w-full py-2.5 sm:py-3 px-4 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 hover:border-emerald-500/50 text-sm font-semibold rounded-xl transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Renew Contract
              </button>
            </div>
            
            <div v-else-if="distributor.status === 'active'" class="flex gap-2 sm:gap-3">
               <button @click="goToShop(distributor.id)" class="flex-1 py-2.5 sm:py-3 px-3 bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold rounded-xl transition-all border border-slate-600 hover:border-slate-500 flex items-center justify-center gap-1.5">
                  <svg class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                  Order
               </button>
               <button @click="downloadAgreement(distributor)" title="Download Agreement Document" class="p-2.5 sm:p-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl transition-all border border-slate-600 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4-4m0 0l-4-4m4 4V4" /></svg>
               </button>
               <button v-if="isNearExpiry(distributor.contract_end_date)" @click="openRenewalModal(distributor)" title="Renew Contract" class="p-2.5 sm:p-3 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 rounded-xl transition-all border border-indigo-500/30 shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
               </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Request Partnership Modal -->
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-300 mb-2">Proposed Contract End Date <span class="text-red-400">*</span></label>
                <input type="date" v-model="proposedDate" :min="minAllowedDate" class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm shadow-inner" />
                <p class="text-[10px] sm:text-xs text-slate-500 mt-1">Contracts must be at least 1 month in duration.</p>
              </div>

              <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-300 mb-2">Introductory Message (Optional)</label>
                <textarea v-model="requestMessage" rows="3" placeholder="Briefly introduce your business and expected procurement volume..."
                  class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm shadow-inner resize-none"></textarea>
              </div>
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
                 <div class="flex items-center gap-2">
                   <button @click="signatureMethod = 'draw'" :class="signatureMethod === 'draw' ? 'text-indigo-400' : 'text-slate-500'" class="text-xs sm:text-sm font-medium transition-colors">Draw</button>
                   <span class="text-slate-600">|</span>
                   <button @click="signatureMethod = 'upload'" :class="signatureMethod === 'upload' ? 'text-indigo-400' : 'text-slate-500'" class="text-xs sm:text-sm font-medium transition-colors">Upload</button>
                 </div>
              </div>

              <div v-if="signatureMethod === 'draw'" class="border border-slate-700 rounded-xl bg-white overflow-hidden relative shadow-inner">
                <div class="flex justify-end p-1 bg-slate-100 border-b border-slate-300">
                  <button @click="clearSignature" class="text-[10px] sm:text-xs text-red-500 font-bold uppercase tracking-wider px-2">Clear</button>
                </div>
                <canvas ref="signaturePad" width="600" height="150" class="w-full h-[150px] touch-none cursor-crosshair"
                  @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                  @touchstart.prevent="startDrawingTouch" @touchmove.prevent="drawTouch" @touchend.prevent="stopDrawing"></canvas>
                <div v-if="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center pt-6">
                  <span class="text-slate-300 text-lg font-medium tracking-widest uppercase">Sign Here</span>
                </div>
              </div>

              <div v-else class="border border-dashed border-slate-600 rounded-xl bg-slate-900/50 p-6 flex flex-col items-center justify-center relative min-h-[150px]">
                <input type="file" accept="image/png, image/jpeg" @change="handleSignatureUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                <div v-if="!uploadedSignature" class="text-center text-slate-400">
                  <svg class="w-8 h-8 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                  <span class="text-sm font-medium">Click to upload signature (PNG/JPG)</span>
                </div>
                <div v-else class="flex flex-col items-center">
                  <img :src="uploadedSignature" class="max-h-[100px] object-contain mb-2" />
                  <span class="text-xs text-indigo-400">Click to change</span>
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

    <!-- Negotiation Modal -->
    <div v-if="showNegotiationModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/80 backdrop-blur-sm p-4 sm:p-6" @mousedown.self="closeNegotiationModal">
      <div class="relative bg-slate-900 border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-800 flex justify-between items-center shrink-0 bg-slate-900/95 rounded-t-2xl z-10 sticky top-0">
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Contract Negotiation</h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Reviewing proposal from <span class="font-semibold text-indigo-400">{{ selectedDistributor?.name }}</span></p>
          </div>
          <button @click="closeNegotiationModal" class="text-slate-500 hover:text-white bg-slate-800 hover:bg-slate-700 p-2 rounded-lg transition-colors focus:outline-none">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="p-5 sm:p-8 overflow-y-auto custom-scrollbar flex-1 relative">
          <div class="space-y-6 sm:space-y-8">
            <div class="bg-amber-500/10 border border-amber-500/20 p-4 sm:p-5 rounded-xl flex flex-col gap-2">
               <span class="text-sm font-semibold text-amber-400">Distributor Proposed Date</span>
               <p class="text-slate-300 text-lg">The distributor has proposed the contract end date: <strong class="text-white">{{ formatDate(selectedDistributor?.proposed_end_date) }}</strong>.</p>
            </div>

            <div v-if="negotiationMode === 'counter'">
              <label class="block text-sm font-semibold text-slate-300 mb-2">Counter Proposal End Date <span class="text-red-400">*</span></label>
              <input type="date" v-model="proposedDate" :min="minAllowedDate" class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm shadow-inner" />
              <p class="text-[10px] sm:text-xs text-slate-500 mt-1">Contracts must be at least 1 month in duration.</p>
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
                 <label class="block text-sm font-semibold text-slate-300">Your Signature to {{ negotiationMode === 'counter' ? 'Counter' : 'Accept' }} <span class="text-red-400">*</span></label>
                 <div class="flex items-center gap-2">
                   <button @click="signatureMethod = 'draw'" :class="signatureMethod === 'draw' ? 'text-indigo-400' : 'text-slate-500'" class="text-xs font-medium transition-colors">Draw</button>
                   <span class="text-slate-600">|</span>
                   <button @click="signatureMethod = 'upload'" :class="signatureMethod === 'upload' ? 'text-indigo-400' : 'text-slate-500'" class="text-xs font-medium transition-colors">Upload</button>
                 </div>
              </div>

              <div v-if="signatureMethod === 'draw'" class="border border-slate-700 rounded-xl bg-white overflow-hidden relative shadow-inner">
                <div class="flex justify-end p-1 bg-slate-100 border-b border-slate-300">
                  <button @click="clearSignature" class="text-[10px] sm:text-xs text-red-500 font-bold uppercase tracking-wider px-2">Clear</button>
                </div>
                <canvas ref="signaturePad" width="600" height="150" class="w-full h-[150px] touch-none cursor-crosshair"
                  @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                  @touchstart.prevent="startDrawingTouch" @touchmove.prevent="drawTouch" @touchend.prevent="stopDrawing"></canvas>
                <div v-if="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center pt-6">
                  <span class="text-slate-300 text-lg font-medium tracking-widest uppercase">Sign Here</span>
                </div>
              </div>

              <div v-else class="border border-dashed border-slate-600 rounded-xl bg-slate-900/50 p-6 flex flex-col items-center justify-center relative min-h-[150px]">
                <input type="file" accept="image/png, image/jpeg" @change="handleSignatureUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                <div v-if="!uploadedSignature" class="text-center text-slate-400">
                  <svg class="w-8 h-8 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                  <span class="text-sm font-medium">Click to upload signature</span>
                </div>
                <div v-else class="flex flex-col items-center">
                  <img :src="uploadedSignature" class="max-h-[100px] object-contain mb-2" />
                  <span class="text-xs text-indigo-400">Click to change</span>
                </div>
              </div>
            </div>

            <label class="flex items-start gap-3 p-4 rounded-xl border border-slate-700/50 bg-slate-800/20 cursor-pointer group">
              <input type="checkbox" v-model="agreedToTerms" class="mt-0.5 w-4 h-4 rounded bg-slate-900 border-slate-500 checked:bg-indigo-500" />
              <span class="text-sm text-slate-300 leading-relaxed group-hover:text-slate-200">
                I verify my signature to {{ negotiationMode === 'counter' ? 'propose a new date' : 'finalize this contract' }}.
              </span>
            </label>
          </div>
        </div>

        <div class="px-5 sm:px-8 py-4 sm:py-5 border-t border-slate-800 bg-slate-900/95 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4 shrink-0 rounded-b-2xl">
          <button @click="closeNegotiationModal" :disabled="isSubmitting" class="w-full sm:w-auto px-6 py-2.5 text-slate-300 bg-transparent hover:bg-slate-800 border border-slate-700 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
            Cancel
          </button>
          
          <button v-if="negotiationMode === 'view'" @click="negotiationMode = 'counter'" class="w-full sm:w-auto px-6 py-2.5 bg-amber-600/20 hover:bg-amber-600/30 text-amber-400 border border-amber-500/30 rounded-xl text-sm font-semibold transition-colors">
            Counter Proposal
          </button>
          
          <button v-if="negotiationMode === 'counter'" @click="submitCounter" :disabled="!isFormValid || isSubmitting" class="w-full sm:w-auto px-6 py-2.5 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-sm font-semibold transition-all disabled:opacity-50 flex justify-center gap-2">
            Send Counter
          </button>

          <button v-if="negotiationMode === 'view'" @click="submitAccept" :disabled="!isFormValid || isSubmitting" class="w-full sm:w-auto px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-semibold transition-all disabled:opacity-50 flex justify-center gap-2">
            Accept & Finalize
          </button>
        </div>
      </div>
    </div>

    <!-- Renewal Modal -->
    <div v-if="showRenewalModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-950/80 backdrop-blur-sm p-4 sm:p-6" @mousedown.self="closeRenewalModal">
      <div class="relative bg-slate-900 border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl flex flex-col max-h-[90vh] my-auto animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-5 sm:px-8 py-5 sm:py-6 border-b border-slate-800 flex justify-between items-center shrink-0 bg-slate-900/95 rounded-t-2xl z-10 sticky top-0">
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight flex items-center gap-2">
              <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
              Contract Renewal
            </h2>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Proposing renewal with <span class="font-semibold text-emerald-400">{{ selectedDistributor?.name }}</span></p>
          </div>
          <button @click="closeRenewalModal" class="text-slate-500 hover:text-white bg-slate-800 hover:bg-slate-700 p-2 rounded-lg transition-colors focus:outline-none">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>

        <div class="p-5 sm:p-8 overflow-y-auto custom-scrollbar flex-1 relative">
          <div class="space-y-6 sm:space-y-8">
            <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 sm:p-5 rounded-xl flex items-start gap-4">
               <svg class="w-6 h-6 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
               <p class="text-sm text-slate-300 leading-relaxed">This will request a contract renewal. The distributor will review your proposed end date and may accept or counter-propose.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-300 mb-2">New Proposed End Date <span class="text-red-400">*</span></label>
                <input type="date" v-model="proposedDate" :min="minAllowedDate" class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-sm shadow-inner" />
                <p class="text-[10px] sm:text-xs text-slate-500 mt-1">Contracts must be at least 1 month in duration.</p>
              </div>

              <div class="sm:col-span-2">
                <label class="block text-sm font-semibold text-slate-300 mb-2">Message (Optional)</label>
                <textarea v-model="requestMessage" rows="3" placeholder="Briefly explain your intent to renew..."
                  class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-sm shadow-inner resize-none"></textarea>
              </div>
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
                 <label class="block text-sm font-semibold text-slate-300">Your Signature <span class="text-red-400">*</span></label>
                 <div class="flex items-center gap-2">
                   <button @click="signatureMethod = 'draw'" :class="signatureMethod === 'draw' ? 'text-emerald-400' : 'text-slate-500'" class="text-xs font-medium transition-colors">Draw</button>
                   <span class="text-slate-600">|</span>
                   <button @click="signatureMethod = 'upload'" :class="signatureMethod === 'upload' ? 'text-emerald-400' : 'text-slate-500'" class="text-xs font-medium transition-colors">Upload</button>
                 </div>
              </div>

              <div v-if="signatureMethod === 'draw'" class="border border-slate-700 rounded-xl bg-white overflow-hidden relative shadow-inner">
                <div class="flex justify-end p-1 bg-slate-100 border-b border-slate-300">
                  <button @click="clearSignature" class="text-[10px] sm:text-xs text-red-500 font-bold uppercase tracking-wider px-2">Clear</button>
                </div>
                <canvas ref="signaturePad" width="600" height="150" class="w-full h-[150px] touch-none cursor-crosshair"
                  @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                  @touchstart.prevent="startDrawingTouch" @touchmove.prevent="drawTouch" @touchend.prevent="stopDrawing"></canvas>
                <div v-if="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center pt-6">
                  <span class="text-slate-300 text-lg font-medium tracking-widest uppercase">Sign Here to Renew</span>
                </div>
              </div>

              <div v-else class="border border-dashed border-slate-600 rounded-xl bg-slate-900/50 p-6 flex flex-col items-center justify-center relative min-h-[150px]">
                <input type="file" accept="image/png, image/jpeg" @change="handleSignatureUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                <div v-if="!uploadedSignature" class="text-center text-slate-400">
                  <svg class="w-8 h-8 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                  <span class="text-sm font-medium">Click to upload signature (PNG/JPG)</span>
                </div>
                <div v-else class="flex flex-col items-center">
                  <img :src="uploadedSignature" class="max-h-[100px] object-contain mb-2" />
                  <span class="text-xs text-emerald-400">Click to change</span>
                </div>
              </div>
            </div>

            <label class="flex items-start gap-3 sm:gap-4 p-4 rounded-xl border border-slate-700/50 bg-slate-800/20 cursor-pointer group hover:bg-slate-800/40 transition-colors">
              <div class="relative flex items-center justify-center shrink-0 mt-0.5">
                 <input type="checkbox" v-model="agreedToTerms" class="peer appearance-none w-5 h-5 border-2 border-slate-500 rounded bg-slate-900 checked:bg-emerald-500 checked:border-emerald-500 transition-colors cursor-pointer" />
                 <svg class="absolute w-3.5 h-3.5 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                 </svg>
              </div>
              <span class="text-sm text-slate-300 leading-relaxed group-hover:text-slate-200 transition-colors">
                I hereby affix my signature and agree to the partnership terms to request renewal.
              </span>
            </label>
          </div>
        </div>

        <div class="px-5 sm:px-8 py-4 sm:py-5 border-t border-slate-800 bg-slate-900/95 flex flex-col-reverse sm:flex-row justify-end gap-3 sm:gap-4 shrink-0 rounded-b-2xl">
          <button @click="closeRenewalModal" :disabled="isSubmitting" class="w-full sm:w-auto px-6 py-2.5 sm:py-3 text-slate-300 bg-transparent hover:bg-slate-800 border border-slate-700 rounded-xl text-sm font-semibold transition-colors disabled:opacity-50">
            Cancel
          </button>
          <button @click="submitRenewal" :disabled="!isFormValid || isSubmitting"
            class="w-full sm:w-auto px-6 py-2.5 sm:py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
            <svg v-if="isSubmitting" class="w-4 h-4 sm:w-5 sm:h-5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span v-if="isSubmitting">Submitting...</span>
            <span v-else>Send Renewal Request</span>
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import api from '@/utils/axios';
import { Toaster, toast } from 'vue-sonner';
import echo from '@/utils/websocket.js' 

export default {
  name: 'DistributorList',
  components: { Toaster },
  data() {
    return {
      distributors: [],
      loading: true,
      activeTab: 'All',
      searchQuery: '',
      currentUserId: null,
      echoInitialized: false,

      // Modals State
      showModal: false,
      showNegotiationModal: false,
      showRenewalModal: false,
      selectedDistributor: null,
      
      // Form Data
      proposedDate: '',
      requestMessage: '',
      agreedToTerms: false,
      isSubmitting: false,
      negotiationMode: 'view',

      // Signature State
      signatureMethod: 'draw', // 'draw' or 'upload'
      hasSignature: false,
      uploadedSignature: null,
      isDrawing: false,
      ctx: null,
    };
  },
  computed: {
    negotiatingCount() { return this.distributors.filter(d => d.status === 'pending' || d.status === 'negotiating').length; },
    filteredDistributors() {
      let filtered = this.distributors;
      if (this.activeTab === 'Connected') {
        filtered = filtered.filter(d => d.status === 'active');
      } else if (this.activeTab === 'Negotiating') {
        filtered = filtered.filter(d => d.status === 'pending' || d.status === 'negotiating');
      } else if (this.activeTab === 'Expired') {
        filtered = filtered.filter(d => d.status === 'expired');
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
    minAllowedDate() {
      const min = new Date();
      min.setMonth(min.getMonth() + 1);
      return min.toISOString().split('T')[0];
    },
    isSignatureValid() {
      return this.signatureMethod === 'draw' ? this.hasSignature : !!this.uploadedSignature;
    },
    isFormValid() {
      const isDateValid = this.negotiationMode === 'view' && this.showNegotiationModal ? true : (this.proposedDate && this.proposedDate >= this.minAllowedDate);
      return this.agreedToTerms && this.isSignatureValid && isDateValid;
    }
  },
  created() { this.fetchDistributors(); },
  methods: {
    formatDate(dateString) {
      if(!dateString) return 'Not Set';
      return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    },
    isNearExpiry(dateString) {
      if(!dateString) return false;
      const diffTime = new Date(dateString) - new Date();
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) <= 30 && diffTime > 0;
    },
    goToShop(distributorId) {
      this.$router.push('/serviceProvider/shop/' + distributorId);
    },
    goToProducts(distributorId) {
      this.$router.push({ name: 'sp_distributor_products', params: { id: distributorId } });
    },
    
    async fetchDistributors() {
      this.loading = true;
      try {
        const response = await api.get('/service-provider/distributors');
        if (response.data.success) {
          this.distributors = response.data.data;
          this.currentUserId = response.data.current_user_id;
          this.initializeEchoListener();
        }
      } catch (error) {
        toast.error('Failed to load network data', { description: 'Please check your connection and try again.' });
      } finally {
        this.loading = false;
      }
    },

    initializeEchoListener() {
        if (!this.echoInitialized && this.currentUserId && echo) {
            echo.private(`partnership.user.${this.currentUserId}`)
                .listen('.PartnershipStatusUpdated', (e) => {
                    this.fetchDistributorsSilently();
                    toast.info('Network Update', {
                        description: 'A distributor has updated your partnership status.'
                    });
                });
            this.echoInitialized = true;
        }
    },

    async fetchDistributorsSilently() {
      try {
        const response = await api.get('/service-provider/distributors');
        if (response.data.success) {
          this.distributors = response.data.data;
        }
      } catch (error) {
         console.error('Silent refresh failed');
      }
    },

    // --- Modal Resets ---
    resetForm() {
      this.requestMessage = '';
      this.agreedToTerms = false;
      this.hasSignature = false;
      this.uploadedSignature = null;
      this.proposedDate = this.minAllowedDate;
      if(this.ctx && this.$refs.signaturePad) {
          this.ctx.clearRect(0,0, this.$refs.signaturePad.width, this.$refs.signaturePad.height);
          this.ctx.beginPath();
      }
    },

    // --- Request Partnership Logic ---
    openModal(distributor) {
      this.selectedDistributor = distributor;
      this.showModal = true;
      document.body.style.overflow = 'hidden';
      this.resetForm();
      this.$nextTick(() => { if(this.signatureMethod === 'draw') this.initSignaturePad(); });
    },
    closeModal() {
      this.showModal = false;
      this.selectedDistributor = null;
      document.body.style.overflow = '';
    },

    // --- Negotiation Logic ---
    openNegotiationModal(distributor) {
      this.selectedDistributor = distributor;
      this.negotiationMode = 'view';
      this.showNegotiationModal = true;
      document.body.style.overflow = 'hidden';
      this.resetForm();
      this.$nextTick(() => { if(this.signatureMethod === 'draw') this.initSignaturePad(); });
    },
    closeNegotiationModal() {
      this.showNegotiationModal = false;
      this.selectedDistributor = null;
      document.body.style.overflow = '';
    },

    // --- Renewal Logic ---
    openRenewalModal(distributor) {
      this.selectedDistributor = distributor;
      this.showRenewalModal = true;
      document.body.style.overflow = 'hidden';
      this.resetForm();
      this.$nextTick(() => { if(this.signatureMethod === 'draw') this.initSignaturePad(); });
    },
    closeRenewalModal() {
      this.showRenewalModal = false;
      this.selectedDistributor = null;
      document.body.style.overflow = '';
    },

    // --- Signature Logic ---
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
      if (!this.isDrawing || !this.ctx) return;
      const rect = this.$refs.signaturePad.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      this.ctx.lineTo(x, y);
      this.ctx.stroke();
      this.ctx.beginPath();
      this.ctx.moveTo(x, y);
    },
    stopDrawing() { this.isDrawing = false; if(this.ctx) this.ctx.beginPath(); },
    startDrawingTouch(e) {
      const touch = e.touches[0];
      const rect = this.$refs.signaturePad.getBoundingClientRect();
      this.isDrawing = true;
      this.hasSignature = true;
      this.ctx.beginPath();
      this.ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
    },
    drawTouch(e) {
      if (!this.isDrawing || !this.ctx) return;
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
    handleSignatureUpload(event) {
      const file = event.target.files[0];
      if (file && (file.type === 'image/png' || file.type === 'image/jpeg')) {
        const reader = new FileReader();
        reader.onload = (e) => { this.uploadedSignature = e.target.result; };
        reader.readAsDataURL(file);
      } else {
        toast.error("Invalid file format. Please upload PNG or JPG.");
      }
    },
    getFinalSignature() {
      return this.signatureMethod === 'draw' ? this.$refs.signaturePad.toDataURL('image/png') : this.uploadedSignature;
    },

    downloadAgreement(distributor) {
       if(distributor.agreement_url) {
          window.open(distributor.agreement_url, '_blank');
       }
    },

    // --- Submissions ---
    async submitRequest() {
      if (!this.isFormValid) return;
      this.isSubmitting = true;

      try {
        const response = await api.post('/service-provider/distributors/request', {
          distributor_id: this.selectedDistributor.id,
          request_message: this.requestMessage,
          proposed_end_date: this.proposedDate,
          signature: this.getFinalSignature()
        });

        if (response.data.success) {
          toast.success('Partnership Proposal Sent', { description: `Official request successfully submitted to ${this.selectedDistributor.name}.` });
          this.closeModal();
          this.fetchDistributorsSilently();
        }
      } catch (error) {
        toast.error('Submission Failed', { description: error.response?.data?.message || 'There was an issue processing your request.' });
      } finally {
        this.isSubmitting = false;
      }
    },

    async submitCounter() {
      if (!this.isFormValid) return;
      this.isSubmitting = true;

      try {
        const response = await api.post(`/service-provider/distributors/${this.selectedDistributor.partnership_id}/counter-proposal`, {
          proposed_end_date: this.proposedDate,
          signature: this.getFinalSignature()
        });

        if (response.data.success) {
          toast.success('Counter-proposal Sent', { description: 'Your proposed date has been sent to the distributor.' });
          this.closeNegotiationModal();
          this.fetchDistributorsSilently();
        }
      } catch (error) {
        toast.error('Counter Failed', { description: error.response?.data?.message || 'There was an issue sending your counter-proposal.' });
      } finally {
        this.isSubmitting = false;
      }
    },

    async submitAccept() {
      if (!this.isFormValid) return;
      this.isSubmitting = true;

      try {
        const response = await api.post(`/service-provider/distributors/${this.selectedDistributor.partnership_id}/accept-proposal`, {
          signature: this.getFinalSignature()
        });

        if (response.data.success) {
          toast.success('Contract Finalized', { description: 'The partnership contract is now active.' });
          this.closeNegotiationModal();
          this.fetchDistributorsSilently();
        }
      } catch (error) {
        toast.error('Acceptance Failed', { description: error.response?.data?.message || 'There was an issue finalizing the contract.' });
      } finally {
        this.isSubmitting = false;
      }
    },

    async submitRenewal() {
      if (!this.isFormValid) return;
      this.isSubmitting = true;

      try {
        const response = await api.post(`/service-provider/distributors/${this.selectedDistributor.partnership_id}/renew-contract`, {
          message: this.requestMessage,
          proposed_end_date: this.proposedDate,
          signature: this.getFinalSignature()
        });

        if (response.data.success) {
          toast.success('Renewal Requested', { description: `A signed request to renew the partnership has been sent.` });
          this.closeRenewalModal();
          this.fetchDistributorsSilently();
        }
      } catch (error) {
        toast.error('Renewal Failed', { description: error.response?.data?.message || 'There was an issue sending your request.' });
      } finally {
        this.isSubmitting = false;
      }
    }
  },
  watch: {
    signatureMethod(val) {
      if (val === 'draw') {
        this.$nextTick(() => this.initSignaturePad());
      }
    }
  },
  beforeUnmount() { 
      document.body.style.overflow = ''; 
      if (this.currentUserId && echo) {
          echo.leave(`partnership.user.${this.currentUserId}`);
      }
  }
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