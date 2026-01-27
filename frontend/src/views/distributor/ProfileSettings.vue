<template>
  <div class="page-transition p-4 md:p-6 min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white/90 backdrop-blur-sm z-50 flex items-center justify-center">
      <div class="text-center">
        <div class="relative">
          <div class="w-20 h-20 border-4 border-blue-100 rounded-full"></div>
          <div class="absolute top-0 left-0 w-20 h-20 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <p class="text-gray-600 mt-4 font-medium">Loading profile data...</p>
      </div>
    </div>

    <!-- Toastify Container -->
    <div id="toast-container"></div>

    <!-- Page Header -->
    <div class="mb-8 md:mb-10">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg icon-hover">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Profile & Settings
            </h1>
          </div>
          <p class="text-gray-500 smooth-color">Manage your account information and preferences</p>
        </div>
        
        <div class="flex items-center gap-3">
          <button @click="saveAllChanges" 
            :disabled="!hasUnsavedChanges || saving"
            :class="[
              'btn-hover-effect btn-modern px-6 py-3 rounded-xl transition-all duration-300 flex items-center font-medium shadow-lg touch-friendly',
              hasUnsavedChanges && !saving 
                ? 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-blue-500/25' 
                : 'bg-gray-200 text-gray-500 cursor-not-allowed',
              saving ? 'bg-gradient-to-r from-blue-400 to-blue-500 cursor-wait' : ''
            ]">
            <svg v-if="!saving" class="w-5 h-5 mr-2 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <svg v-if="saving" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ saving ? 'Saving...' : 'Save All Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
      <!-- Left Column: Distributor Info -->
      <div class="lg:col-span-2 space-y-6 md:space-y-8">
        <!-- User Information Card -->
        <div class="card-shadow depth-hover bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform transition-transform duration-300">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg shadow-md icon-hover">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Personal Information</h2>
              </div>
              <span class="status-badge text-xs font-semibold px-3 py-1 bg-blue-100 text-blue-600 rounded-full">Basic Info</span>
            </div>
          </div>

          <!-- Profile Overview -->
          <div class="p-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-5 mb-6">
              <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
                <div class="flex-shrink-0">
                  <div class="relative">
                    <div class="avatar-hover w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                      <span class="text-3xl font-bold text-white">{{ getInitials(userInfo.full_name) }}</span>
                    </div>
                    <button @click="changeProfilePhoto" class="absolute -bottom-2 -right-2 p-2 bg-white rounded-full border-2 border-white shadow-lg hover:bg-gray-50 transition-transform hover:scale-110">
                      <svg class="w-4 h-4 text-gray-600 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                    </button>
                  </div>
                </div>
                
                <div class="flex-1 space-y-2">
                  <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ userInfo.full_name }}</h3>
                    <p class="text-gray-600">{{ userInfo.role | capitalize }}</p>
                  </div>
                  
                  <div class="flex flex-wrap gap-2">
                    <span :class="[
                      'status-badge inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium shadow-sm',
                      userInfo.status === 'active' 
                        ? 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 border border-green-200'
                        : userInfo.status === 'pending' 
                          ? 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-700 border border-yellow-200'
                          : 'bg-gradient-to-r from-red-50 to-rose-50 text-red-700 border border-red-200'
                    ]">
                      <svg class="w-3 h-3 mr-1.5 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="userInfo.status === 'active'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        <path v-if="userInfo.status === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path v-if="userInfo.status === 'inactive'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      {{ userInfo.status | capitalize }}
                    </span>
                    
                    <span v-if="userInfo.role === 'distributor' && verificationData" 
                      :class="[
                        'status-badge inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium shadow-sm',
                        verificationData.status === 'approved' 
                          ? 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 border border-green-200'
                          : verificationData.status === 'pending' 
                            ? 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-700 border border-yellow-200'
                            : verificationData.status === 'rejected' 
                              ? 'bg-gradient-to-r from-red-50 to-rose-50 text-red-700 border border-red-200'
                              : 'bg-gradient-to-r from-gray-50 to-slate-50 text-gray-700 border border-gray-200'
                      ]">
                      <svg class="w-3 h-3 mr-1.5 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="verificationData.status === 'approved'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        <path v-if="verificationData.status === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path v-if="verificationData.status === 'rejected'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path v-if="!verificationData.status" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      {{ verificationData.status ? verificationData.status.charAt(0).toUpperCase() + verificationData.status.slice(1) : 'Not Submitted' }} Verification
                    </span>
                  </div>
                </div>
                
                <div class="text-right">
                  <p class="text-sm text-gray-500 mb-1 smooth-color">Member since</p>
                  <p class="font-semibold text-gray-900">{{ formatDate(userInfo.created_at) }}</p>
                </div>
              </div>
            </div>

            <!-- User Form -->
            <div class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group space-y-2">
                  <label class="smooth-color block text-sm font-semibold text-gray-700">First Name</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="w-5 h-5 text-gray-400 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                    <input v-model="userInfo.first_name" type="text" 
                      class="form-input w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300">
                  </div>
                </div>
                
                <div class="form-group space-y-2">
                  <label class="smooth-color block text-sm font-semibold text-gray-700">Last Name</label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="w-5 h-5 text-gray-400 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                    <input v-model="userInfo.last_name" type="text" 
                      class="form-input w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300">
                  </div>
                </div>
              </div>

              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">Email Address</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <input v-model="userInfo.email" type="email" 
                    class="form-input w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300">
                </div>
              </div>

              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">Phone Number</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                  </div>
                  <input v-model="userInfo.phone" type="tel" 
                    class="form-input w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300">
                </div>
              </div>

              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">Address</label>
                <textarea v-model="userInfo.address" rows="3" 
                  class="form-textarea w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300 resize-none"></textarea>
              </div>

              <div class="pt-6 border-t border-gray-200">
                <button @click="saveUserInfo" 
                  :disabled="!userInfoChanged || saving"
                  :class="[
                    'btn-hover-effect btn-modern px-6 py-3 rounded-xl transition-all duration-300 flex items-center justify-center font-medium shadow-lg touch-friendly',
                    userInfoChanged && !saving 
                      ? 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-blue-500/25 hover:shadow-xl' 
                      : 'bg-gray-200 text-gray-500 cursor-not-allowed',
                    saving ? 'bg-gradient-to-r from-blue-400 to-blue-500 cursor-wait' : ''
                  ]">
                  <svg v-if="!saving" class="w-5 h-5 mr-2 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg v-if="saving" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  {{ saving ? 'Saving...' : 'Update Personal Information' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Distributor-specific Information Card -->
        <div v-if="userInfo.role === 'distributor'" class="card-shadow depth-hover bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform transition-transform duration-300">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg shadow-md icon-hover">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                  </svg>
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Distributor Information</h2>
              </div>
              <span class="status-badge text-xs font-semibold px-3 py-1 bg-green-100 text-green-600 rounded-full">Business Info</span>
            </div>
          </div>

          <!-- Distributor Form -->
          <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">Company Name</label>
                <input v-model="distributorInfo.company_name" type="text" 
                  class="form-input w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:bg-white transition-all duration-300"
                  :readonly="verificationData && verificationData.has_submitted">
              </div>
              
              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">Business License Number</label>
                <input v-model="distributorInfo.business_registration_number" type="text" 
                  class="form-input w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:bg-white transition-all duration-300"
                  :readonly="verificationData && verificationData.has_submitted">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">ID Type</label>
                <input v-model="distributorInfo.valid_id_type_display" type="text" 
                  class="form-input w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-xl text-gray-600" readonly>
              </div>
              
              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">ID Number</label>
                <input v-model="distributorInfo.id_number" type="text" 
                  class="form-input w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:bg-white transition-all duration-300"
                  :readonly="verificationData && verificationData.has_submitted">
              </div>
            </div>

            <div v-if="verificationData && verificationData.has_submitted" 
              class="p-4 bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-blue-200">
              <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-800 mb-2">Information Locked</h4>
                  <p class="text-sm text-gray-600">
                    Your distributor information is currently locked because you have submitted business verification. 
                    To make changes, please contact admin support or wait for your verification to be processed.
                  </p>
                </div>
              </div>
            </div>

            <div class="pt-6 border-t border-gray-200">
              <button @click="saveDistributorInfo" 
                :disabled="!distributorInfoChanged || savingDistributor || (verificationData && verificationData.has_submitted)"
                :class="[
                  'btn-hover-effect btn-modern px-6 py-3 rounded-xl transition-all duration-300 flex items-center justify-center font-medium shadow-lg touch-friendly',
                  distributorInfoChanged && !savingDistributor && (!verificationData || !verificationData.has_submitted)
                    ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-green-500/25 hover:shadow-xl'
                    : 'bg-gray-200 text-gray-500 cursor-not-allowed',
                  savingDistributor ? 'bg-gradient-to-r from-green-400 to-green-500 cursor-wait' : ''
                ]">
                <svg v-if="!savingDistributor" class="w-5 h-5 mr-2 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-if="savingDistributor" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ savingDistributor ? 'Saving...' : 'Update Distributor Information' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Password Change Card -->
        <div class="card-shadow depth-hover bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform transition-transform duration-300">
          <div class="bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-red-500 to-rose-500 rounded-lg shadow-md icon-hover">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Security Settings</h2>
              </div>
              <span class="status-badge text-xs font-semibold px-3 py-1 bg-red-100 text-red-600 rounded-full">Security</span>
            </div>
          </div>

          <div class="p-6 space-y-6">
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border border-yellow-200 p-4">
              <div class="flex items-start">
                <div class="p-2 bg-yellow-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <h4 class="font-semibold text-yellow-800 mb-2">Security Recommendation</h4>
                  <p class="text-sm text-yellow-700">
                    Use a strong password with at least 8 characters including uppercase, lowercase, numbers, and special characters.
                  </p>
                </div>
              </div>
            </div>

            <div class="space-y-6">
              <div class="form-group space-y-2">
                <label class="smooth-color block text-sm font-semibold text-gray-700">Current Password</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                  </div>
                  <input v-model="password.current" :type="currentPasswordFieldType" 
                    class="form-input w-full pl-10 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300">
                  <button @click="toggleCurrentPasswordVisibility" 
                    class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                      <path v-if="!showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="form-group space-y-2">
                    <label class="smooth-color block text-sm font-semibold text-gray-700">New Password</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                      </div>
                      <input v-model="password.new" :type="newPasswordFieldType" 
                        class="form-input w-full pl-10 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300">
                      <button @click="toggleNewPasswordVisibility" 
                        class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                          <path v-if="!showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div class="space-y-2">
                    <p class="smooth-color text-sm font-semibold text-gray-700">Password Requirements</p>
                    <div class="space-y-1.5">
                      <div v-for="requirement in passwordRequirements" :key="requirement.text" 
                        class="flex items-center">
                        <div class="flex-shrink-0 w-5 h-5 mr-2 flex items-center justify-center icon-hover">
                          <svg v-if="requirement.met" class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                          </svg>
                          <svg v-else class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                          </svg>
                        </div>
                        <span class="text-sm smooth-color" :class="requirement.met ? 'text-green-600' : 'text-gray-500'">
                          {{ requirement.text }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="form-group space-y-2">
                    <label class="smooth-color block text-sm font-semibold text-gray-700">Confirm New Password</label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                      </div>
                      <input v-model="password.confirm" :type="confirmPasswordFieldType" 
                        class="form-input w-full pl-10 pr-12 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300">
                      <button @click="toggleConfirmPasswordVisibility" 
                        class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                          <path v-if="!showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div class="space-y-2">
                    <p class="smooth-color text-sm font-semibold text-gray-700">Password Match</p>
                    <div class="flex items-center p-3 rounded-lg bg-gray-50 border border-gray-200 document-card">
                      <div class="flex-shrink-0 w-6 h-6 mr-3 flex items-center justify-center icon-hover">
                        <svg v-if="passwordMatch" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg v-else-if="password.confirm" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <svg v-else class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <circle cx="12" cy="12" r="10" stroke-width="2"/>
                        </svg>
                      </div>
                      <span class="text-sm font-medium smooth-color" :class="passwordMatch ? 'text-green-600' : password.confirm ? 'text-red-600' : 'text-gray-500'">
                        {{ password.new && password.confirm 
                          ? (passwordMatch ? 'Passwords match' : 'Passwords do not match') 
                          : 'Enter password to check' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-6 border-t border-gray-200">
              <button @click="changePassword" 
                :disabled="!canChangePassword || changingPassword"
                :class="[
                  'btn-hover-effect btn-modern px-6 py-3 rounded-xl transition-all duration-300 flex items-center justify-center font-medium shadow-lg w-full touch-friendly',
                  canChangePassword && !changingPassword 
                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-blue-500/25 hover:shadow-xl' 
                    : 'bg-gray-200 text-gray-500 cursor-not-allowed',
                  changingPassword ? 'bg-gradient-to-r from-blue-400 to-blue-500 cursor-wait' : ''
                ]">
                <svg v-if="!changingPassword" class="w-5 h-5 mr-2 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <svg v-if="changingPassword" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ changingPassword ? 'Changing Password...' : 'Change Password' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Business Verification -->
      <div class="space-y-6 md:space-y-8" v-if="userInfo.role === 'distributor'">
        <!-- Business Verification Wizard Card -->
        <div class="card-shadow depth-hover bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden transform transition-transform duration-300">
          <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-lg shadow-md icon-hover">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  </svg>
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Business Verification</h2>
              </div>
              <span class="status-badge text-xs font-semibold px-3 py-1 bg-purple-100 text-purple-600 rounded-full">Verification</span>
            </div>
          </div>

          <!-- Status Banner -->
          <div v-if="verificationData && verificationData.has_submitted" class="p-6 border-b border-gray-200">
            <div :class="[
              'p-5 rounded-xl border shadow-sm',
              verificationData.status === 'approved' 
                ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-200 shadow-green-500/10'
                : verificationData.status === 'pending' 
                  ? 'bg-gradient-to-r from-yellow-50 to-amber-50 border-yellow-200 shadow-yellow-500/10'
                  : verificationData.status === 'rejected' 
                    ? 'bg-gradient-to-r from-red-50 to-rose-50 border-red-200 shadow-red-500/10'
                    : 'bg-gradient-to-r from-blue-50 to-cyan-50 border-blue-200 shadow-blue-500/10'
            ]">
              <div class="flex items-start">
                <div :class="[
                  'p-3 rounded-lg mr-4 flex-shrink-0 icon-hover',
                  verificationData.status === 'approved' ? 'bg-green-100' :
                  verificationData.status === 'pending' ? 'bg-yellow-100' :
                  verificationData.status === 'rejected' ? 'bg-red-100' :
                  'bg-blue-100'
                ]">
                  <svg class="w-6 h-6" 
                    :class="verificationData.status === 'approved' ? 'text-green-600' : 
                           verificationData.status === 'pending' ? 'text-yellow-600' : 
                           verificationData.status === 'rejected' ? 'text-red-600' : 
                           'text-blue-600'" 
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="verificationData.status === 'approved'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    <path v-if="verificationData.status === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path v-if="verificationData.status === 'rejected'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="flex-1">
                  <h4 class="font-bold text-lg mb-2" 
                    :class="verificationData.status === 'approved' ? 'text-green-800' : 
                           verificationData.status === 'pending' ? 'text-yellow-800' : 
                           verificationData.status === 'rejected' ? 'text-red-800' : 
                           'text-blue-800'">
                    {{ getVerificationStatusTitle() }}
                  </h4>
                  <p class="text-sm mb-3" 
                    :class="verificationData.status === 'approved' ? 'text-green-700' : 
                           verificationData.status === 'pending' ? 'text-yellow-700' : 
                           verificationData.status === 'rejected' ? 'text-red-700' : 
                           'text-blue-700'">
                    {{ getVerificationStatusMessage() }}
                  </p>
                  <p v-if="verificationData.rejection_reason" 
                    class="text-sm p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    <strong class="font-semibold">Reason:</strong> {{ verificationData.rejection_reason }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Wizard Content -->
          <div v-if="!verificationData || !verificationData.has_submitted || verificationData.status === 'rejected'">
            <!-- Wizard Progress Steps -->
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50">
              <div class="flex items-center justify-between relative">
                <!-- Progress Line -->
                <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-200 z-0"></div>
                <div class="absolute top-4 left-0 h-0.5 bg-gradient-to-r from-blue-500 to-purple-600 z-0 transition-all duration-500 ease-out"
                  :style="{ width: getProgressWidth() }"></div>

                <!-- Step 1 -->
                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 1 ? currentStep = 1 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 1 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span v-if="currentStep > 1" class="text-sm font-bold">✓</span>
                    <span v-else class="text-sm font-bold">1</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 1 ? 'text-gray-800' : 'text-gray-500'">Company</span>
                </div>

                <!-- Step 2 -->
                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 2 ? currentStep = 2 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 2 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span v-if="currentStep > 2" class="text-sm font-bold">✓</span>
                    <span v-else class="text-sm font-bold">2</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 2 ? 'text-gray-800' : 'text-gray-500'">Valid ID</span>
                </div>

                <!-- Step 3 -->
                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 3 ? currentStep = 3 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 3 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span v-if="currentStep > 3" class="text-sm font-bold">✓</span>
                    <span v-else class="text-sm font-bold">3</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 3 ? 'text-gray-800' : 'text-gray-500'">Documents</span>
                </div>

                <!-- Step 4 -->
                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 4 ? currentStep = 4 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 4 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span class="text-sm font-bold">4</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 4 ? 'text-gray-800' : 'text-gray-500'">Review</span>
                </div>
              </div>
            </div>

            <!-- Step Content Area -->
            <div class="p-6">
              <!-- Step 1: Company Information -->
              <div v-if="currentStep === 1" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Company Information</h3>
                  <p class="text-gray-600 mt-2">Let's start with your basic company details</p>
                </div>

                <div class="space-y-5">
                  <div class="form-group">
                    <label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Company Name
                      <span class="text-red-500">*</span>
                    </label>
                    <input v-model="verificationForm.company_name" type="text" 
                      class="form-input w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300"
                      placeholder="Enter your company name"
                      @input="validateStep(1)">
                  </div>

                  <div class="form-group">
                    <label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Business Registration Number
                      <span class="text-red-500">*</span>
                    </label>
                    <input v-model="verificationForm.business_registration_number" type="text" 
                      class="form-input w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300"
                      placeholder="Enter registration number"
                      @input="validateStep(1)">
                  </div>
                </div>
              </div>

              <!-- Step 2: Valid ID -->
              <div v-if="currentStep === 2" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                      </svg>
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Valid ID Requirements</h3>
                  <p class="text-gray-600 mt-2">Upload your valid government-issued ID</p>
                </div>

                <div class="space-y-5">
                  <div class="form-group">
                    <label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Type of Valid ID
                      <span class="text-red-500">*</span>
                    </label>
                    <select v-model="verificationForm.valid_id_type" 
                      class="form-select w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300 appearance-none"
                      @change="validateStep(2)">
                      <option value="">Select ID Type</option>
                      <option value="passport">Passport</option>
                      <option value="driver_license">Driver's License</option>
                      <option value="umid">UMID</option>
                      <option value="prc">PRC ID</option>
                      <option value="postal">Postal ID</option>
                      <option value="voter">Voter's ID</option>
                      <option value="tin">TIN ID</option>
                      <option value="sss">SSS ID</option>
                      <option value="philhealth">PhilHealth ID</option>
                      <option value="other">Other Government ID</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      ID Number
                      <span class="text-red-500">*</span>
                    </label>
                    <input v-model="verificationForm.id_number" type="text" 
                      class="form-input w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all duration-300"
                      placeholder="Enter your ID number"
                      @input="validateStep(2)">
                  </div>

                  <div class="form-group">
                    <label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Photo of Valid ID (Front)
                      <span class="text-red-500">*</span>
                    </label>
                    <div @click="triggerFileInput('valid_id_photo')" 
                      @dragover.prevent @drop.prevent="handleFileDrop($event, 'valid_id_photo')"
                      class="file-upload-area shine-effect mt-1 cursor-pointer flex flex-col items-center justify-center px-6 pt-8 pb-8 border-2 border-dashed rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]"
                      :class="verificationForm.valid_id_photo ? 'border-green-300 bg-green-50' : 'border-gray-300'">
                      <svg class="h-12 w-12 mb-4" :class="verificationForm.valid_id_photo ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                      </svg>
                      <div class="text-center">
                        <p class="text-sm font-medium text-gray-700">
                          <span class="text-blue-600 hover:text-blue-500">Upload ID Photo</span>
                          <span class="text-gray-500 ml-1">or drag and drop</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, PDF up to 5MB</p>
                        <p v-if="verificationForm.valid_id_photo" class="text-sm text-green-600 font-medium mt-2">
                          ✓ {{ verificationForm.valid_id_photo.name }}
                        </p>
                      </div>
                    </div>
                    <input ref="valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'valid_id_photo')">
                  </div>
                </div>
              </div>

              <!-- Step 3: Business Documents -->
              <div v-if="currentStep === 3" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Business Documents</h3>
                  <p class="text-gray-600 mt-2">Upload required business registration documents</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- DTI Certificate -->
                  <div @click="triggerFileInput('dti_certificate_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'dti_certificate_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.dti_certificate_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <svg class="h-8 w-8" :class="verificationForm.dti_certificate_photo ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">DTI Certificate</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.dti_certificate_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="dti_certificate_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'dti_certificate_photo')">
                  </div>

                  <!-- Mayor's Permit -->
                  <div @click="triggerFileInput('mayor_permit_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'mayor_permit_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.mayor_permit_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <svg class="h-8 w-8" :class="verificationForm.mayor_permit_photo ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">Mayor's Permit</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.mayor_permit_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="mayor_permit_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'mayor_permit_photo')">
                  </div>

                  <!-- Barangay Clearance -->
                  <div @click="triggerFileInput('barangay_clearance_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'barangay_clearance_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.barangay_clearance_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <svg class="h-8 w-8" :class="verificationForm.barangay_clearance_photo ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">Barangay Clearance</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.barangay_clearance_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="barangay_clearance_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'barangay_clearance_photo')">
                  </div>

                  <!-- Business Registration Plate -->
                  <div @click="triggerFileInput('business_registration_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'business_registration_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.business_registration_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <svg class="h-8 w-8" :class="verificationForm.business_registration_photo ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">Business Plate</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.business_registration_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="business_registration_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'business_registration_photo')">
                  </div>
                </div>
              </div>

              <!-- Step 4: Review and Submit -->
              <div v-if="currentStep === 4" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                      </svg>
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Review & Submit</h3>
                  <p class="text-gray-600 mt-2">Review all information before submission</p>
                </div>

                <!-- Review Summary -->
                <div class="space-y-4">
                  <div class="document-card bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                      <svg class="w-5 h-5 mr-2 text-blue-500 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                      Company Information
                    </h4>
                    <div class="space-y-3">
                      <div>
                        <p class="text-xs text-gray-600 mb-1">Company Name</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.company_name || 'Not provided' }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-600 mb-1">Registration Number</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.business_registration_number || 'Not provided' }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="document-card bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                      <svg class="w-5 h-5 mr-2 text-blue-500 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                      </svg>
                      Valid ID Details
                    </h4>
                    <div class="space-y-3">
                      <div>
                        <p class="text-xs text-gray-600 mb-1">ID Type</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.valid_id_type ? formatIDType(verificationForm.valid_id_type) : 'Not selected' }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-600 mb-1">ID Number</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.id_number || 'Not provided' }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="document-card bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                      <svg class="w-5 h-5 mr-2 text-blue-500 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                      </svg>
                      Uploaded Documents
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                      <template v-for="(value, key) in verificationForm" :key="key">
                        <div v-if="key.includes('photo') && value && typeof value === 'object' && value.name" 
                          class="flex items-center p-3 bg-white border border-gray-200 rounded-lg document-card">
                          <div class="p-1.5 bg-green-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                          </div>
                          <span class="text-sm text-gray-700 truncate font-medium">{{ value.name }}</span>
                        </div>
                      </template>
                    </div>
                  </div>

                  <!-- Terms and Conditions -->
                  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start">
                      <input type="checkbox" v-model="acceptedTerms" id="terms" 
                        class="form-checkbox mt-1 mr-3 w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                      <label for="terms" class="text-sm text-gray-700 leading-relaxed smooth-color">
                        I certify that all information provided is accurate and authentic. I understand that providing false information may result in account suspension.
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Wizard Navigation -->
              <div class="flex justify-between pt-6 border-t border-gray-200 mt-6">
                <button v-if="currentStep > 1" @click="previousStep" 
                  class="btn-hover-effect px-5 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-all duration-300 flex items-center font-medium hover:border-gray-400 touch-friendly">
                  <svg class="w-4 h-4 mr-2 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                  </svg>
                  Back
                </button>
                <div v-else></div>

                <button v-if="currentStep < 4" @click="nextStep" 
                  :disabled="!isStepValid(currentStep)"
                  :class="[
                    'btn-hover-effect btn-modern px-5 py-2.5 rounded-xl transition-all duration-300 flex items-center font-medium shadow-md touch-friendly',
                    isStepValid(currentStep) 
                      ? 'bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white shadow-blue-500/25 hover:shadow-lg' 
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                  ]">
                  Continue
                  <svg class="w-4 h-4 ml-2 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>

                <button v-if="currentStep === 4" @click="submitVerification" 
                  :disabled="!isStepValid(4) || !acceptedTerms || submittingVerification"
                  :class="[
                    'btn-hover-effect btn-modern px-5 py-2.5 rounded-xl transition-all duration-300 flex items-center font-medium shadow-md touch-friendly',
                    isStepValid(4) && acceptedTerms && !submittingVerification 
                      ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-green-500/25 hover:shadow-lg' 
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed',
                    submittingVerification ? 'bg-gradient-to-r from-green-400 to-green-500 cursor-wait' : ''
                  ]">
                  <svg v-if="!submittingVerification" class="w-4 h-4 mr-2 icon-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <svg v-if="submittingVerification" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  {{ submittingVerification ? 'Submitting...' : 'Submit Verification' }}
                </button>
              </div>
            </div>
          </div>

          <!-- View Submitted Documents -->
          <div v-if="verificationData && verificationData.has_submitted && (verificationData.status === 'pending' || verificationData.status === 'approved')" 
            class="p-6">
            <div class="document-card bg-gradient-to-br from-gray-50 to-blue-50 border border-gray-200 rounded-xl p-5">
              <h3 class="font-bold text-gray-800 mb-4">Submitted Documents</h3>
              <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs text-gray-600 mb-1">Company Name:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.company_name }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 mb-1">ID Type:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.id_type_name }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 mb-1">ID Number:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.id_number }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 mb-1">Business Registration Number:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.business_registration_number }}</p>
                  </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                  <p class="text-xs text-gray-600 mb-3">Uploaded Documents:</p>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <a v-for="(url, field) in verificationData.photos" :key="field" 
                      :href="url" target="_blank" 
                      class="document-card p-3 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col items-center hover:scale-[1.02]">
                      <div class="p-2 bg-blue-50 rounded-lg mb-2 icon-hover">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </div>
                      <span class="text-xs text-gray-700 font-medium text-center">{{ formatFieldName(field) }}</span>
                    </a>
                  </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                  <p class="text-xs text-gray-600 mb-1">Submitted:</p>
                  <p class="font-semibold text-gray-900">{{ formatDateTime(verificationData.submitted_at) }}</p>
                </div>
                
                <div v-if="verificationData.status === 'pending'" class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                      <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-yellow-800">Under Review</p>
                      <p class="text-xs text-yellow-700 mt-1">Your documents are being reviewed. This usually takes 3-5 business days.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '@/utils/axios'
import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

export default {
  name: 'ProfileSettings',
  filters: {
    capitalize(value) {
      if (!value) return ''
      value = value.toString()
      return value.charAt(0).toUpperCase() + value.slice(1)
    }
  },
  data() {
    return {
      loading: false,
      saving: false,
      savingDistributor: false,
      changingPassword: false,
      submittingVerification: false,
      currentStep: 1,
      acceptedTerms: false,
      userInfo: {
        id: null,
        first_name: '',
        last_name: '',
        full_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: '',
        created_at: ''
      },
      originalUserInfo: {},
      distributorInfo: {
        company_name: '',
        business_registration_number: '',
        valid_id_type: '',
        valid_id_type_display: '',
        id_number: ''
      },
      originalDistributorInfo: {},
      verificationData: null,
      verificationForm: {
        company_name: '',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        dti_certificate_photo: null,
        mayor_permit_photo: null,
        barangay_clearance_photo: null,
        business_registration_number: '',
        business_registration_photo: null
      },
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
    }
  },
  computed: {
    currentPasswordFieldType() {
      return this.showCurrentPassword ? 'text' : 'password'
    },
    newPasswordFieldType() {
      return this.showNewPassword ? 'text' : 'password'
    },
    confirmPasswordFieldType() {
      return this.showConfirmPassword ? 'text' : 'password'
    },
    passwordRequirements() {
      const newPassword = this.password.new
      return [
        {
          text: 'At least 8 characters',
          met: newPassword.length >= 8
        },
        {
          text: 'Contains uppercase letter',
          met: /[A-Z]/.test(newPassword)
        },
        {
          text: 'Contains lowercase letter',
          met: /[a-z]/.test(newPassword)
        },
        {
          text: 'Contains number',
          met: /\d/.test(newPassword)
        },
        {
          text: 'Contains special character',
          met: /[!@#$%^&*(),.?":{}|<>]/.test(newPassword)
        }
      ]
    },
    passwordMatch() {
      return this.password.new === this.password.confirm
    },
    canChangePassword() {
      return this.password.current && 
             this.password.new && 
             this.password.confirm &&
             this.passwordMatch &&
             this.passwordRequirements.every(req => req.met)
    },
    userInfoChanged() {
      return JSON.stringify(this.userInfo) !== JSON.stringify(this.originalUserInfo)
    },
    distributorInfoChanged() {
      return JSON.stringify(this.distributorInfo) !== JSON.stringify(this.originalDistributorInfo)
    },
    hasUnsavedChanges() {
      return this.userInfoChanged || this.distributorInfoChanged
    },
    isVerificationFormValid() {
      return this.verificationForm.company_name &&
             this.verificationForm.valid_id_type &&
             this.verificationForm.id_number &&
             this.verificationForm.valid_id_photo &&
             this.verificationForm.dti_certificate_photo &&
             this.verificationForm.mayor_permit_photo &&
             this.verificationForm.barangay_clearance_photo &&
             this.verificationForm.business_registration_number &&
             this.verificationForm.business_registration_photo
    }
  },
  async created() {
    await this.fetchUserData()
    await this.fetchDistributorData()
    if (this.userInfo.role === 'distributor') {
      await this.fetchVerificationData()
    }
    this.setOriginalData()
  },
  methods: {
    // Wizard Navigation Methods
    nextStep() {
      if (this.isStepValid(this.currentStep) && this.currentStep < 4) {
        this.currentStep++
        this.scrollToTop()
      }
    },

    previousStep() {
      if (this.currentStep > 1) {
        this.currentStep--
        this.scrollToTop()
      }
    },

    isStepValid(step) {
      switch(step) {
        case 1:
          return this.verificationForm.company_name && 
                 this.verificationForm.business_registration_number
        case 2:
          return this.verificationForm.valid_id_type && 
                 this.verificationForm.id_number && 
                 this.verificationForm.valid_id_photo
        case 3:
          return this.verificationForm.dti_certificate_photo &&
                 this.verificationForm.mayor_permit_photo &&
                 this.verificationForm.barangay_clearance_photo &&
                 this.verificationForm.business_registration_photo
        case 4:
          return this.isVerificationFormValid
        default:
          return false
      }
    },

    validateStep(step) {
      // Trigger validation when fields change
      return this.isStepValid(step)
    },

    getProgressWidth() {
      const percentage = ((this.currentStep - 1) / 3) * 100
      return `${percentage}%`
    },

    scrollToTop() {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      })
    },

    formatIDType(type) {
      const types = {
        'passport': 'Passport',
        'driver_license': "Driver's License",
        'umid': 'UMID',
        'prc': 'PRC ID',
        'postal': 'Postal ID',
        'voter': "Voter's ID",
        'tin': 'TIN ID',
        'sss': 'SSS ID',
        'philhealth': 'PhilHealth ID',
        'other': 'Other Government ID'
      }
      return types[type] || type
    },

    // Toastify Notification Methods
    showSuccessToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #10b981, #059669)",
          color: "#ffffff",
          borderRadius: "12px",
          padding: "16px 20px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 10px 25px rgba(16, 185, 129, 0.25)",
          zIndex: 9999
        },
        stopOnFocus: true
      }).showToast();
    },

    showErrorToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #ef4444, #dc2626)",
          color: "#ffffff",
          borderRadius: "12px",
          padding: "16px 20px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 10px 25px rgba(239, 68, 68, 0.25)",
          zIndex: 9999
        },
        stopOnFocus: true
      }).showToast();
    },

    showWarningToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #f59e0b, #d97706)",
          color: "#ffffff",
          borderRadius: "12px",
          padding: "16px 20px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 10px 25px rgba(245, 158, 11, 0.25)",
          zIndex: 9999
        },
        stopOnFocus: true
      }).showToast();
    },

    showInfoToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #3b82f6, #2563eb)",
          color: "#ffffff",
          borderRadius: "12px",
          padding: "16px 20px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 10px 25px rgba(59, 130, 246, 0.25)",
          zIndex: 9999
        },
        stopOnFocus: true
      }).showToast();
    },

    // File Validation Toast
    showFileErrorToast(message) {
      this.showErrorToast(message);
    },

    async fetchUserData() {
      this.loading = true
      try {
        const response = await axios.get('/profile')
        if (response.data.status === 'success') {
          this.userInfo = response.data.data.user
        } else {
          throw new Error(response.data.message || 'Failed to fetch user data')
        }
      } catch (error) {
        console.error('Error fetching user data:', error)
        // Check if it's an authentication error
        if (error.response && error.response.status === 401) {
          // Handle authentication error
          this.showErrorToast('Session expired. Please login again.')
          // Redirect to login page
          setTimeout(() => {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            this.$router.push('/Landing/logIn')
          }, 2000)
        } else {
          this.showErrorToast(error.response?.data?.message || 'Failed to load profile data. Please try again.')
        }
      } finally {
        this.loading = false
      }
    },

    async fetchDistributorData() {
      if (this.userInfo.role === 'distributor') {
        try {
          const response = await axios.get('/distributor/requirements')
          if (response.data.status === 'success') {
            const data = response.data.data
            
            this.distributorInfo = {
              company_name: data.company_name || '',
              business_registration_number: data.business_registration_number || '',
              valid_id_type: data.valid_id_type || '',
              valid_id_type_display: data.id_type_name || '',
              id_number: data.id_number || ''
            }
          } else {
            this.distributorInfo = {
              company_name: '',
              business_registration_number: '',
              valid_id_type: '',
              valid_id_type_display: '',
              id_number: ''
            }
          }
        } catch (error) {
          console.error('Error fetching distributor data:', error)
          if (error.response && error.response.status === 401) {
            this.showErrorToast('Session expired. Please login again.')
            setTimeout(() => {
              localStorage.removeItem('auth_token')
              localStorage.removeItem('user')
              this.$router.push('/Landing/logIn')
            }, 2000)
          } else {
            this.distributorInfo = {
              company_name: '',
              business_registration_number: '',
              valid_id_type: '',
              valid_id_type_display: '',
              id_number: ''
            }
          }
        }
      }
    },

    async fetchVerificationData() {
      try {
        const response = await axios.get('/distributor/requirements')
        if (response.data.status === 'success') {
          this.verificationData = response.data.data
          
          if (this.verificationData && this.verificationData.status === 'rejected') {
            this.verificationForm.company_name = this.verificationData.company_name || ''
            this.verificationForm.valid_id_type = this.verificationData.valid_id_type || ''
            this.verificationForm.id_number = this.verificationData.id_number || ''
            this.verificationForm.business_registration_number = this.verificationData.business_registration_number || ''
          }
        }
      } catch (error) {
        console.error('Error fetching verification data:', error)
        if (error.response && error.response.status === 401) {
          // Don't show toast here to avoid duplicate messages
        } else {
          this.verificationData = null
        }
      }
    },

    setOriginalData() {
      this.originalUserInfo = JSON.parse(JSON.stringify(this.userInfo))
      this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
    },

    async saveUserInfo() {
      if (!this.userInfoChanged || this.saving) return
      
      this.saving = true
      try {
        const response = await axios.put('/profile', {
          first_name: this.userInfo.first_name,
          last_name: this.userInfo.last_name,
          email: this.userInfo.email,
          phone: this.userInfo.phone,
          address: this.userInfo.address
        })
        
        if (response.data.status === 'success') {
          this.originalUserInfo = JSON.parse(JSON.stringify(this.userInfo))
          this.showSuccessToast('Personal information updated successfully!')
          
          this.userInfo.full_name = `${this.userInfo.first_name} ${this.userInfo.last_name}`
        } else {
          throw new Error(response.data.message || 'Failed to update profile')
        }
      } catch (error) {
        console.error('Error updating profile:', error)
        if (error.response && error.response.status === 401) {
          this.showErrorToast('Session expired. Please login again.')
          setTimeout(() => {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            this.$router.push('/Landing/logIn')
          }, 2000)
        } else {
          this.showErrorToast(error.response?.data?.message || 'Failed to update profile. Please try again.')
          this.userInfo = JSON.parse(JSON.stringify(this.originalUserInfo))
        }
      } finally {
        this.saving = false
      }
    },

    async saveDistributorInfo() {
      if (!this.distributorInfoChanged || this.savingDistributor) return
      
      this.savingDistributor = true
      try {
        const response = await axios.put('/profile/distributor', {
          company_name: this.distributorInfo.company_name,
          business_registration_number: this.distributorInfo.business_registration_number,
          valid_id_type: this.distributorInfo.valid_id_type,
          id_number: this.distributorInfo.id_number
        })
        
        if (response.data.status === 'success') {
          this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
          this.showSuccessToast('Distributor information updated successfully!')
        } else {
          throw new Error(response.data.message || 'Failed to update distributor information')
        }
      } catch (error) {
        console.error('Error updating distributor info:', error)
        if (error.response && error.response.status === 401) {
          this.showErrorToast('Session expired. Please login again.')
          setTimeout(() => {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            this.$router.push('/Landing/logIn')
          }, 2000)
        } else {
          this.showErrorToast(error.response?.data?.message || 'Failed to update distributor information. Please try again.')
          this.distributorInfo = JSON.parse(JSON.stringify(this.originalDistributorInfo))
        }
      } finally {
        this.savingDistributor = false
      }
    },

    async changePassword() {
      if (!this.canChangePassword || this.changingPassword) return
      
      this.changingPassword = true
      try {
        const response = await axios.put('/profile/password', {
          current_password: this.password.current,
          password: this.password.new,
          password_confirmation: this.password.confirm
        })
        
        if (response.data.status === 'success') {
          this.showSuccessToast('Password changed successfully!')
          
          this.password = {
            current: '',
            new: '',
            confirm: ''
          }
          this.showCurrentPassword = false
          this.showNewPassword = false
          this.showConfirmPassword = false
        } else {
          throw new Error(response.data.message || 'Failed to change password')
        }
      } catch (error) {
        console.error('Error changing password:', error)
        if (error.response && error.response.status === 401) {
          this.showErrorToast('Session expired. Please login again.')
          setTimeout(() => {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            this.$router.push('/Landing/logIn')
          }, 2000)
        } else {
          this.showErrorToast(error.response?.data?.message || 'Failed to change password. Please try again.')
        }
      } finally {
        this.changingPassword = false
      }
    },

    async submitVerification() {
      if (!this.isVerificationFormValid || !this.acceptedTerms || this.submittingVerification) return
      
      this.submittingVerification = true
      try {
        const formData = new FormData()
        
        formData.append('company_name', this.verificationForm.company_name)
        formData.append('valid_id_type', this.verificationForm.valid_id_type)
        formData.append('id_number', this.verificationForm.id_number)
        formData.append('business_registration_number', this.verificationForm.business_registration_number)
        
        formData.append('valid_id_photo', this.verificationForm.valid_id_photo)
        formData.append('dti_certificate_photo', this.verificationForm.dti_certificate_photo)
        formData.append('mayor_permit_photo', this.verificationForm.mayor_permit_photo)
        formData.append('barangay_clearance_photo', this.verificationForm.barangay_clearance_photo)
        formData.append('business_registration_photo', this.verificationForm.business_registration_photo)
        
        const response = await axios.post('/distributor/requirements', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.status === 'success') {
          this.showSuccessToast(response.data.message)
          this.verificationData = response.data.data
          
          this.distributorInfo = {
            company_name: this.verificationData.company_name,
            business_registration_number: this.verificationData.business_registration_number,
            valid_id_type: this.verificationData.valid_id_type,
            valid_id_type_display: this.verificationData.id_type_name,
            id_number: this.verificationData.id_number
          }
          this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
          
          this.verificationForm = {
            company_name: '',
            valid_id_type: '',
            id_number: '',
            valid_id_photo: null,
            dti_certificate_photo: null,
            mayor_permit_photo: null,
            barangay_clearance_photo: null,
            business_registration_number: '',
            business_registration_photo: null
          }
          this.currentStep = 1
          this.acceptedTerms = false
        } else {
          throw new Error(response.data.message || 'Failed to submit verification')
        }
      } catch (error) {
        console.error('Error submitting verification:', error)
        if (error.response && error.response.status === 401) {
          this.showErrorToast('Session expired. Please login again.')
          setTimeout(() => {
            localStorage.removeItem('auth_token')
            localStorage.removeItem('user')
            this.$router.push('/Landing/logIn')
          }, 2000)
        } else {
          let errorMessage = error.response?.data?.message || 'Failed to submit verification. Please try again.'
          
          if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat()
            errorMessage = errors.join(', ')
          }
          
          this.showErrorToast(errorMessage)
        }
      } finally {
        this.submittingVerification = false
      }
    },

    triggerFileInput(field) {
      this.$refs[field].click()
    },

    handleFileChange(event, field) {
      const file = event.target.files[0]
      if (file) {
        if (file.size > 5 * 1024 * 1024) {
          this.showFileErrorToast(`File "${file.name}" is too large. Maximum size is 5MB.`)
          return
        }
        
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
        if (!validTypes.includes(file.type)) {
          this.showFileErrorToast(`File "${file.name}" must be JPG, PNG, or PDF.`)
          return
        }
        
        this.verificationForm[field] = file
        this.validateStep(this.currentStep)
      }
    },

    handleFileDrop(event, field) {
      event.preventDefault()
      const file = event.dataTransfer.files[0]
      if (file) {
        const input = {
          target: { files: [file] }
        }
        this.handleFileChange(input, field)
      }
    },

    getVerificationStatusTitle() {
      if (!this.verificationData) return 'Verification Required'
      
      switch (this.verificationData.status) {
        case 'approved':
          return '✅ Verification Approved'
        case 'pending':
          return '⏳ Verification Pending'
        case 'rejected':
          return '❌ Verification Rejected'
        default:
          return 'Verification Required'
      }
    },

    getVerificationStatusMessage() {
      if (!this.verificationData) {
        return 'Please upload all required documents for business verification. All fields are mandatory for account verification.'
      }
      
      switch (this.verificationData.status) {
        case 'approved':
          return 'Your business verification has been approved. You now have full access to all distributor features.'
        case 'pending':
          return 'Your documents are under review. Please wait for admin approval. This usually takes 3-5 business days.'
        case 'rejected':
          return 'Your verification has been rejected. Please review the reason below and resubmit with corrected documents.'
        default:
          return 'Please upload all required documents for business verification.'
      }
    },

    formatFieldName(field) {
      return field
        .replace(/_/g, ' ')
        .replace(/(?:^|\s)\S/g, a => a.toUpperCase())
        .replace('Photo', '')
        .trim()
    },

    getInitials(name) {
      if (!name) return '??'
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },

    formatDateTime(dateTimeString) {
      if (!dateTimeString) return 'N/A'
      const date = new Date(dateTimeString)
      return date.toLocaleString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    toggleCurrentPasswordVisibility() {
      this.showCurrentPassword = !this.showCurrentPassword
    },

    toggleNewPasswordVisibility() {
      this.showNewPassword = !this.showNewPassword
    },

    toggleConfirmPasswordVisibility() {
      this.showConfirmPassword = !this.showConfirmPassword
    },

    changeProfilePhoto() {
      this.showInfoToast('Feature to change profile photo would open file picker')
    },

    async logout() {
      if (confirm('Are you sure you want to logout?')) {
        try {
          await axios.post('/auth/logout')
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user')
          this.showInfoToast('Logged out successfully')
          this.$router.push('/Landing/logIn')
        } catch (error) {
          console.error('Logout error:', error)
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user')
          this.$router.push('/Landing/logIn')
        }
      }
    },

    saveAllChanges() {
      if (this.userInfoChanged) {
        this.saveUserInfo()
      }
      if (this.distributorInfoChanged) {
        this.saveDistributorInfo()
      }
    }
  }
}
</script>

<style scoped>
  @import "../distributor/styles/profileSettings.css";
</style>

<style>
/* Custom Toastify Styles */
.toastify {
  z-index: 99999 !important;
  position: fixed !important;
  top: 24px !important;
  right: 24px !important;
  max-width: 400px !important;
  backdrop-filter: blur(10px) !important;
}

.toastify.on {
  opacity: 1 !important;
  transform: translateX(0) !important;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes shimmer {
  0% {
    background-position: -468px 0;
  }
  100% {
    background-position: 468px 0;
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.shimmer {
  background: linear-gradient(to right, #f6f7f8 8%, #edeef1 18%, #f6f7f8 33%);
  background-size: 800px 104px;
  animation: shimmer 1.5s infinite linear;
}

/* Gradient animations */
.bg-gradient-to-br {
  background-size: 200% 200%;
}

@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.animate-gradient {
  animation: gradientShift 3s ease infinite;
}

/* Spinner animation */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .grid-cols-1 {
    grid-template-columns: 1fr;
  }
  
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .text-3xl {
    font-size: 1.75rem;
  }
  
  .toastify {
    max-width: 90% !important;
    left: 5% !important;
    right: 5% !important;
    text-align: center !important;
  }
}

@media (max-width: 640px) {
  .p-6 {
    padding: 1rem;
  }
  
  .space-y-6 > * + * {
    margin-top: 1rem;
  }
  
  .grid-cols-3 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Print styles */
@media print {
  button, input, select, textarea, .bg-gradient-to-r {
    display: none !important;
  }
  
  .bg-white {
    background: white !important;
    border: 1px solid #e5e7eb !important;
  }
}

/* Focus styles */
:focus {
  outline: none;
}

:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Selection color */
::selection {
  background-color: rgba(59, 130, 246, 0.2);
  color: #1f2937;
}

/* Smooth transitions */
* {
  transition: background-color 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
}

/* Hardware acceleration for smooth animations */
.hardware-accelerated {
  transform: translateZ(0);
  backface-visibility: hidden;
}

/* Page load animation */
@keyframes pageLoad {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.page-transition {
  animation: pageLoad 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Ripple effect for buttons */
.btn-ripple {
  position: relative;
  overflow: hidden;
}

.btn-ripple:after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 5px;
  height: 5px;
  background: rgba(255, 255, 255, 0.5);
  opacity: 0;
  border-radius: 100%;
  transform: scale(1, 1) translate(-50%);
  transform-origin: 50% 50%;
}

.btn-ripple:focus:not(:active)::after {
  animation: ripple 1s ease-out;
}

@keyframes ripple {
  0% {
    transform: scale(0, 0);
    opacity: 0.5;
  }
  20% {
    transform: scale(25, 25);
    opacity: 0.3;
  }
  100% {
    transform: scale(40, 40);
    opacity: 0;
  }
}
</style>