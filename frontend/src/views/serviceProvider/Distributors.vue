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
            <p class="text-sm sm:text-base text-slate-400 mt-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
              <span class="truncate">Connect with verified paint & materials distributors</span>
            </p>
          </div>
          
          <div class="flex gap-2 bg-slate-800/40 p-1.5 rounded-xl border border-slate-700/50 w-full sm:w-auto overflow-x-auto hide-scrollbar">
            <Button 
              @click="activeTab = 'discover'"
              :variant="activeTab === 'discover' ? 'default' : 'ghost'"
              :class="[
                'px-4 py-2 sm:py-2.5 rounded-lg font-medium transition-all duration-300 flex-1 sm:flex-none whitespace-nowrap text-sm sm:text-base',
                activeTab === 'discover' ? 'bg-indigo-500 hover:bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-700/50'
              ]"
            >
              Discover
            </Button>
            <Button 
              @click="activeTab = 'my-partners'"
              :variant="activeTab === 'my-partners' ? 'default' : 'ghost'"
              :class="[
                'px-4 py-2 sm:py-2.5 rounded-lg font-medium transition-all duration-300 flex-1 sm:flex-none whitespace-nowrap text-sm sm:text-base',
                activeTab === 'my-partners' ? 'bg-indigo-500 hover:bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-700/50'
              ]"
            >
              My Partners
              <Badge v-if="pendingCount > 0" class="ml-2 bg-amber-500 hover:bg-amber-600 text-white border-0">{{ pendingCount }}</Badge>
            </Button>
          </div>
        </div>
      </div>
    </header>

    <main class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8 max-w-7xl mx-auto">
      <div class="mb-6 sm:mb-8 flex flex-col lg:flex-row gap-4 lg:items-center justify-between">
        <div class="relative w-full lg:w-[400px] shrink-0">
          <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <Input 
            v-model="searchQuery" 
            placeholder="Search by name or location..." 
            class="pl-11 bg-slate-900 border-slate-700 text-white focus-visible:ring-indigo-500 w-full h-12 rounded-xl sm:text-base"
          />
        </div>
        
        <div class="w-full -mx-4 px-4 sm:mx-0 sm:px-0 overflow-x-auto hide-scrollbar">
          <div class="flex gap-2 w-max pb-2">
            <Badge 
              v-for="cat in categories" 
              :key="cat"
              @click="toggleCategory(cat)"
              class="cursor-pointer px-4 py-2 text-xs sm:text-sm rounded-full transition-colors whitespace-nowrap"
              :class="selectedCategory === cat ? 'bg-indigo-500/20 text-indigo-300 border-indigo-500/50' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700'"
              variant="outline"
            >
              {{ cat }}
            </Badge>
          </div>
        </div>
      </div>

      <div v-if="isLoading" class="text-center py-16 sm:py-24">
        <div class="w-12 h-12 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-slate-400">Loading network...</p>
      </div>

      <div v-else-if="filteredDistributors.length === 0" class="text-center py-16 sm:py-24 bg-slate-800/20 rounded-3xl border border-slate-800 border-dashed px-4">
        <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto bg-slate-800/80 rounded-full flex items-center justify-center mb-4">
          <svg class="w-8 h-8 sm:w-10 sm:h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002 2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-white mb-2">No distributors found</h3>
        <p class="text-sm sm:text-base text-slate-400 max-w-md mx-auto">Try adjusting your search or category filters.</p>
        <Button @click="resetFilters" variant="link" class="mt-4 text-indigo-400 font-semibold hover:text-indigo-300">Clear all filters</Button>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        <Card 
          v-for="dist in filteredDistributors" 
          :key="dist.id"
          class="bg-slate-900 border-slate-800 flex flex-col overflow-hidden group hover:border-slate-700 transition-all duration-300 shadow-lg hover:shadow-xl rounded-2xl"
        >
          <div class="h-24 sm:h-28 bg-gradient-to-r from-slate-800 to-slate-800/60 relative shrink-0">
            <div class="absolute -bottom-8 left-5 sm:left-6">
              <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-slate-800 border-4 border-slate-900 flex items-center justify-center text-xl sm:text-3xl font-bold text-white shadow-md overflow-hidden">
                <img v-if="dist.logoUrl" :src="dist.logoUrl" :alt="dist.name" class="w-full h-full object-cover" />
                <span v-else class="bg-gradient-to-br from-indigo-500 to-purple-600 w-full h-full flex items-center justify-center">{{ dist.name.charAt(0) }}</span>
              </div>
            </div>
            <div class="absolute top-4 right-4">
              <Badge v-if="dist.status === 'active'" class="bg-emerald-500/10 text-emerald-400 border-emerald-500/20">Active Partner</Badge>
              <Badge v-else-if="dist.status === 'pending'" class="bg-amber-500/10 text-amber-400 border-amber-500/20">Pending</Badge>
              <Badge v-else-if="dist.status === 'rejected'" class="bg-red-500/10 text-red-400 border-red-500/20">Rejected</Badge>
            </div>
          </div>
          
          <CardContent class="pt-12 sm:pt-14 pb-5 px-5 sm:px-6 flex-1 flex flex-col">
            <div class="flex justify-between items-start mb-4 gap-2">
              <div class="min-w-0 flex-1">
                <h3 class="text-base sm:text-lg font-bold text-white truncate">{{ dist.name }}</h3>
                <p class="text-xs sm:text-sm text-slate-400 flex items-center mt-1 truncate">
                  <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 mr-1.5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                  <span class="truncate">{{ dist.location }}</span>
                </p>
              </div>
              <div class="flex items-center bg-slate-800/80 px-2.5 py-1 rounded-md shrink-0 border border-slate-700/50">
                <svg class="w-3.5 h-3.5 text-amber-400 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                <span class="text-xs sm:text-sm font-bold text-white">{{ dist.rating }}</span>
              </div>
            </div>

            <div class="mb-6 flex-1">
              <p class="text-[10px] sm:text-xs text-slate-500 uppercase font-bold tracking-wider mb-2.5">Specialties</p>
              <div class="flex flex-wrap gap-2">
                <span v-for="spec in (dist.specialties || []).slice(0, 3)" :key="spec" class="text-xs px-2.5 py-1 bg-slate-800 text-slate-300 rounded-md border border-slate-700/80">{{ spec }}</span>
                <span v-if="(dist.specialties || []).length > 3" class="text-xs px-2.5 py-1 bg-slate-800/50 text-slate-400 rounded-md border border-slate-700/50">+{{ dist.specialties.length - 3 }}</span>
              </div>
            </div>

            <div class="pt-5 border-t border-slate-800/80 mt-auto">
              <Button v-if="dist.status === 'none' || dist.status === 'disconnected'" @click="openRequestModal(dist)" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white border-0 shadow-lg shadow-indigo-500/20 h-11 text-sm sm:text-base font-medium rounded-xl">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Request Partnership
              </Button>
              <Button v-else-if="dist.status === 'pending'" disabled variant="outline" class="w-full bg-slate-800/50 border-slate-700 text-slate-400 cursor-not-allowed h-11 text-sm sm:text-base font-medium rounded-xl">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Request Sent
              </Button>
              <div v-else-if="dist.status === 'active'" class="flex gap-2">
                <Button class="flex-1 bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white h-11 text-sm font-medium rounded-xl">Order Materials</Button>
                <Button v-if="dist.agreement_url" @click="viewAgreement(dist.agreement_url)" variant="outline" class="px-3 bg-indigo-600/10 border-indigo-500/30 text-indigo-400 hover:bg-indigo-600/20 hover:text-indigo-300 h-11 rounded-xl shrink-0" title="View Agreement">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </Button>
              </div>
              <Button v-else-if="dist.status === 'rejected'" disabled variant="outline" class="w-full bg-red-950/20 border-red-900/50 text-red-400 cursor-not-allowed h-11 text-sm sm:text-base font-medium rounded-xl">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Rejected
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </main>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm sm:p-6">
      <div class="bg-slate-900 border border-slate-700/60 rounded-2xl shadow-2xl w-full max-w-xl max-h-[95vh] flex flex-col overflow-hidden">
        <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-slate-800/60 flex justify-between items-center bg-slate-800/20 shrink-0">
          <h2 class="text-lg sm:text-xl font-bold text-white tracking-tight">Formal Partnership Request</h2>
          <button @click="closeModal" class="text-slate-400 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-slate-800">
            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <div class="p-5 sm:p-6 overflow-y-auto custom-scrollbar">
          <div class="flex items-center gap-4 mb-6 p-4 bg-slate-800/40 rounded-xl border border-slate-700/50">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xl font-bold text-white shadow-inner">
              {{ selectedDistributor?.name.charAt(0) }}
            </div>
            <div class="min-w-0">
              <p class="text-xs text-slate-400 mb-0.5">Requesting to form a binding partnership with</p>
              <p class="font-bold text-white text-base truncate">{{ selectedDistributor?.name }}</p>
            </div>
          </div>

          <div class="space-y-3 mb-6">
            <label class="block text-sm font-medium text-slate-200">Introduction Proposal (Optional)</label>
            <textarea 
              v-model="requestMessage"
              rows="3"
              placeholder="Briefly state your business intent and sourcing volume expectations..."
              class="w-full bg-slate-950/50 border border-slate-700 rounded-xl p-4 text-sm text-slate-200 placeholder:text-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 resize-none"
            ></textarea>
          </div>

          <div class="space-y-3">
            <label class="block text-sm font-medium text-slate-200">Partnership Agreement Terms</label>
            <div class="h-40 overflow-y-auto bg-slate-950 border border-slate-700 rounded-xl p-4 text-xs text-slate-400 space-y-4 custom-scrollbar">
              <div>
                <p class="font-bold text-slate-300 mb-1">1. Formation of Agreement</p>
                <p>By submitting this request, you (the "Service Provider") are extending a formal proposal to the Distributor to enter into a commercial supply relationship. Approval of this request grants you authorized partner status.</p>
              </div>
              
              <div>
                <p class="font-bold text-slate-300 mb-1">2. Procurement and Pricing</p>
                <p>Upon acceptance, the Service Provider shall be eligible to procure materials at designated wholesale or partner-tier pricing. The Distributor reserves the exclusive right to modify pricing tiers, stock availability, and catalog offerings with prior reasonable notice.</p>
              </div>
              
              <div>
                <p class="font-bold text-slate-300 mb-1">3. Compliance and Representation</p>
                <p>The Service Provider agrees to maintain all necessary local business licenses and industry certifications. Materials purchased must be used or resold in accordance with the manufacturer's safety guidelines and local commercial regulations.</p>
              </div>

              <div>
                <p class="font-bold text-slate-300 mb-1">4. Non-Disclosure and Confidentiality</p>
                <p>Both parties mutually agree to hold in strict confidence any sensitive business intelligence shared during this partnership, including but not limited to custom pricing structures, volume discounts, and client details.</p>
              </div>

              <div>
                <p class="font-bold text-slate-300 mb-1">5. Termination Clause</p>
                <p>Either party holds the right to terminate this partnership agreement at any time, with or without cause, by executing a disconnection through the platform. The Distributor may immediately revoke partnership status in the event of fraudulent activity or breach of conduct.</p>
              </div>
            </div>
            
            <label class="flex items-start gap-3 mt-4 cursor-pointer group">
              <div class="relative flex items-center justify-center mt-0.5 shrink-0">
                <input 
                  type="checkbox" 
                  v-model="agreedToTerms"
                  class="peer appearance-none w-5 h-5 border-2 border-slate-600 rounded-md bg-slate-900 checked:bg-indigo-500 checked:border-indigo-500 transition-colors cursor-pointer"
                />
                <svg class="absolute w-3 h-3 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
              </div>
              <span class="text-sm text-slate-300 group-hover:text-slate-200 transition-colors leading-relaxed">
                I acknowledge that I have read and agree to the <span class="text-indigo-400 font-medium">Partnership Agreement Terms</span>. I consent to sharing my verified business profile with this distributor for evaluation.
              </span>
            </label>
          </div>
        </div>

        <div class="p-5 sm:p-6 border-t border-slate-800/60 flex flex-col-reverse sm:flex-row justify-end gap-3 bg-slate-900/50 shrink-0">
          <Button @click="closeModal" variant="outline" class="w-full sm:w-auto bg-transparent border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800 h-12 rounded-xl">Cancel</Button>
          <Button 
            @click="confirmSubmit" 
            :disabled="isSubmitting || !agreedToTerms"
            class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white border-0 h-12 px-8 rounded-xl font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-all" 
          >
            <span v-if="isSubmitting" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              Processing...
            </span>
            <span v-else>Agree & Send Request</span>
          </Button>
        </div>
      </div>
    </div>

    <Dialog :open="showAgreementDialog" @update:open="showAgreementDialog = $event">
      <DialogContent class="sm:max-w-[800px] h-[85vh] bg-slate-900 border-slate-700 text-slate-200 flex flex-col p-0 overflow-hidden shadow-2xl">
        <DialogHeader class="px-6 py-4 border-b border-slate-800 shrink-0 bg-slate-900/50 z-10">
          <div class="flex items-center justify-between">
            <div>
              <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                Official Partnership Agreement
              </DialogTitle>
              <DialogDescription class="text-slate-400 mt-1">Secure document viewer</DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="flex-1 overflow-hidden bg-slate-950 p-4 sm:p-6">
          <div class="w-full h-full bg-white shadow-sm border border-slate-800 rounded-lg overflow-hidden">
            <iframe 
              v-if="currentAgreementUrl" 
              :src="currentAgreementUrl" 
              class="w-full h-full border-0" 
              title="Partnership Agreement"
            ></iframe>
          </div>
        </div>

        <DialogFooter class="px-6 py-4 border-t border-slate-800 bg-slate-900/50 shrink-0 flex-col sm:flex-row gap-3 sm:justify-between">
          <Button variant="outline" @click="showAgreementDialog = false" class="border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white w-full sm:w-auto">
            Close Viewer
          </Button>
          <Button @click="downloadAgreement" class="bg-indigo-600 hover:bg-indigo-500 text-white border-0 w-full sm:w-auto">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Download Document
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog v-model:open="showConfirm">
      <AlertDialogContent class="bg-slate-900 border-slate-800 text-slate-200">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-white">Sign and Submit Request</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-400">
            You are about to submit a formal partnership proposal to <b>{{ selectedDistributor?.name }}</b>. This will notify their management team to review your application. Proceed?
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showConfirm = false" class="bg-slate-800 border-slate-700 text-slate-300 hover:bg-slate-700 hover:text-white">Review Again</AlertDialogCancel>
          <AlertDialogAction @click="handleFinalSubmit" class="bg-indigo-600 hover:bg-indigo-500 text-white border-0">Confirm Submission</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

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
            boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
            padding: '12px 16px',
            fontSize: '14px',
            pointerEvents: 'auto',
          },
        }"
      />
    </Teleport>
  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import api from '@/utils/axios'

// Sonner and Alert Dialog Imports
import { Toaster, toast } from 'vue-sonner'
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
import { 
  Dialog, 
  DialogContent, 
  DialogDescription, 
  DialogFooter, 
  DialogHeader, 
  DialogTitle 
} from '@/components/ui/dialog' // ADDED DIALOG IMPORTS

export default {
  name: 'DistributorPartnership',
  components: {
    Button, Card, CardContent, Badge, Input, Toaster,
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent,
    AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
    Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
  },
  setup() {
    const isMobile = ref(window.innerWidth < 768)

    const handleResize = () => {
      isMobile.value = window.innerWidth < 768
    }

    onMounted(() => {
      window.addEventListener('resize', handleResize)
    })

    onBeforeUnmount(() => {
      window.removeEventListener('resize', handleResize)
    })

    return {
      isMobile,
    }
  },
  data() {
    return {
      activeTab: 'discover',
      searchQuery: '',
      selectedCategory: 'All',
      categories: ['All', 'Interior Paints', 'Exterior Paints', 'Specialty Paints', 'Tools & Hardware', 'Waterproofing'],
      showModal: false,
      showConfirm: false,
      
      // Agreement Viewer State
      showAgreementDialog: false,
      currentAgreementUrl: '',

      isLoading: true,
      isSubmitting: false,
      agreedToTerms: false, 
      selectedDistributor: null,
      requestMessage: '',
      distributors: []
    }
  },
  computed: {
    pendingCount() { return this.distributors.filter(d => d.status === 'pending').length; },
    filteredDistributors() {
      let result = this.distributors;
      if (this.activeTab === 'my-partners') result = result.filter(d => d.status === 'active' || d.status === 'pending');
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        result = result.filter(d => d.name.toLowerCase().includes(query) || d.location.toLowerCase().includes(query));
      }
      if (this.selectedCategory !== 'All') result = result.filter(d => (d.specialties || []).includes(this.selectedCategory));
      return result;
    }
  },
  mounted() { 
    this.fetchDistributors(); 
  },
  methods: {
    async fetchDistributors() {
      this.isLoading = true;
      try {
        const response = await api.get('/service-provider/distributors');
        if (response.data.success) this.distributors = response.data.data;
      } catch (error) {
        toast.error('Could not load distributors', {
          duration: 3000,
        });
      } finally { this.isLoading = false; }
    },
    toggleCategory(cat) { this.selectedCategory = cat; },
    resetFilters() { this.searchQuery = ''; this.selectedCategory = 'All'; },
    
    // Agreement Viewer Methods
    viewAgreement(url) {
      if (url) {
        this.currentAgreementUrl = url;
        this.showAgreementDialog = true;
      }
    },
    downloadAgreement() {
      if (this.currentAgreementUrl) {
        const link = document.createElement('a');
        link.href = this.currentAgreementUrl;
        link.download = 'Partnership_Agreement.html';
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
      }
    },

    openRequestModal(dist) {
      this.selectedDistributor = dist;
      this.showModal = true;
      document.body.style.overflow = 'hidden';
    },
    closeModal() {
      this.showModal = false;
      this.showConfirm = false;
      document.body.style.overflow = '';
      this.isSubmitting = false;
      this.requestMessage = '';
      this.agreedToTerms = false;
    },
    confirmSubmit() {
      if(this.agreedToTerms) {
        this.showConfirm = true;
      }
    },
    async handleFinalSubmit() {
      this.showConfirm = false;
      this.isSubmitting = true;
      try {
        const response = await api.post('/service-provider/distributors/request', {
          distributor_id: this.selectedDistributor.id,
          request_message: this.requestMessage
        });
        if (response.data.success) {
          const index = this.distributors.findIndex(d => d.id === this.selectedDistributor.id);
          if (index !== -1) this.distributors[index].status = 'pending';
          
          toast.success('Partnership Proposal Sent', {
            description: `Official request successfully submitted to ${this.selectedDistributor.name}.`,
            duration: 4000,
          });
          this.closeModal();
        }
      } catch (error) {
        toast.error('Submission Failed', {
          description: error.response?.data?.message || 'There was an issue processing your agreement. Please try again.',
          duration: 4000,
        });
        this.isSubmitting = false;
      }
    }
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

/* Custom scrollbar for the agreement text box */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #64748b; }
</style>