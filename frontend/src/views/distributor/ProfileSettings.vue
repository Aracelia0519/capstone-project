<template>
  <div class="p-4 md:p-6">
    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-80 z-50 flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-600">Loading profile data...</p>
      </div>
    </div>

    <!-- Toastify Container (will be injected by Toastify) -->
    <div id="toast-container"></div>

    <!-- Page Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Profile & Settings</h1>
          <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
        </div>
        <div class="flex items-center space-x-3">
          <button @click="saveAllChanges" 
            :disabled="!hasUnsavedChanges || saving"
            :class="[hasUnsavedChanges && !saving ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed', saving ? 'bg-blue-400 cursor-wait' : '']"
            class="px-4 py-2 text-white rounded-lg transition-colors flex items-center">
            <svg v-if="!saving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            <svg v-if="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ saving ? 'Saving...' : 'Save All Changes' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Distributor Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- User Information Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Personal Information</h2>
            <div class="p-2 bg-blue-50 rounded-lg">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>

          <!-- Profile Overview -->
          <div class="mb-8 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
            <div class="flex flex-col md:flex-row md:items-center gap-4">
              <div class="flex-shrink-0">
                <div class="relative">
                  <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">{{ getInitials(userInfo.full_name) }}</span>
                  </div>
                  <button @click="changeProfilePhoto" class="absolute -bottom-2 -right-2 p-1.5 bg-white rounded-full border border-gray-300 shadow-sm hover:bg-gray-50">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ userInfo.full_name }}</h3>
                <p class="text-gray-600 mb-2">{{ userInfo.role | capitalize }}</p>
                <div class="flex flex-wrap gap-2">
                  <span :class="userInfo.status === 'active' ? 'bg-green-100 text-green-800' : userInfo.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="userInfo.status === 'active'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                      <path v-if="userInfo.status === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      <path v-if="userInfo.status === 'inactive'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ userInfo.status | capitalize }}
                  </span>
                  <!-- Business Verification Status Badge -->
                  <span v-if="userInfo.role === 'distributor' && verificationData" 
                    :class="verificationData.status === 'approved' ? 'bg-green-100 text-green-800' : verificationData.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : verificationData.status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800'"
                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <p class="text-sm text-gray-500">Member since</p>
                <p class="font-medium text-gray-900">{{ formatDate(userInfo.created_at) }}</p>
              </div>
            </div>
          </div>

          <!-- User Form -->
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                <input v-model="userInfo.first_name" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <input v-model="userInfo.last_name" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input v-model="userInfo.email" type="email" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <input v-model="userInfo.phone" type="tel" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
              <textarea v-model="userInfo.address" rows="3" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <button @click="saveUserInfo" 
                :disabled="!userInfoChanged || saving"
                :class="[userInfoChanged && !saving ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed', saving ? 'bg-blue-400 cursor-wait' : '']"
                class="px-4 py-2 text-white rounded-lg transition-colors flex items-center">
                <svg v-if="!saving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-if="saving" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ saving ? 'Saving...' : 'Update Personal Information' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Distributor-specific Information Card (Only for distributor role) -->
        <div v-if="userInfo.role === 'distributor'" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Distributor Information</h2>
            <div class="p-2 bg-green-50 rounded-lg">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
          </div>

          <!-- Distributor Form -->
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                <input v-model="distributorInfo.company_name" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  :readonly="verificationData && verificationData.has_submitted">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Business License Number</label>
                <input v-model="distributorInfo.business_registration_number" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  :readonly="verificationData && verificationData.has_submitted">
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ID Type</label>
                <input v-model="distributorInfo.valid_id_type_display" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50"
                  readonly>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ID Number</label>
                <input v-model="distributorInfo.id_number" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  :readonly="verificationData && verificationData.has_submitted">
              </div>
            </div>

            <!-- Read-only status info when verification is submitted -->
            <div v-if="verificationData && verificationData.has_submitted" class="p-4 bg-gray-50 rounded-lg border border-gray-200">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                  <h4 class="font-medium text-gray-800">Information Locked</h4>
                  <p class="text-sm text-gray-600 mt-1">
                    Your distributor information is currently locked because you have submitted business verification. 
                    To make changes, please contact admin support or wait for your verification to be processed.
                  </p>
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <button @click="saveDistributorInfo" 
                :disabled="!distributorInfoChanged || savingDistributor || (verificationData && verificationData.has_submitted)"
                :class="[distributorInfoChanged && !savingDistributor && (!verificationData || !verificationData.has_submitted) ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed', savingDistributor ? 'bg-green-400 cursor-wait' : '']"
                class="px-4 py-2 text-white rounded-lg transition-colors flex items-center">
                <svg v-if="!savingDistributor" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-if="savingDistributor" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ savingDistributor ? 'Saving...' : 'Update Distributor Information' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Password Change Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Password Change</h2>
            <div class="p-2 bg-red-50 rounded-lg">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
          </div>

          <div class="space-y-6">
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
              <div class="flex">
                <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                  <h4 class="font-medium text-yellow-800">Security Recommendation</h4>
                  <p class="text-sm text-yellow-700 mt-1">Use a strong password with at least 8 characters including uppercase, lowercase, numbers, and special characters.</p>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <input v-model="password.current" :type="currentPasswordFieldType" 
                  class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                <button @click="toggleCurrentPasswordVisibility" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                    <path v-if="!showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                <div class="relative">
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  <input v-model="password.new" :type="newPasswordFieldType" 
                    class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <button @click="toggleNewPasswordVisibility" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                      <path v-if="!showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
                <div class="mt-2 space-y-1">
                  <div v-for="requirement in passwordRequirements" :key="requirement.text" 
                    class="flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" :class="requirement.met ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span :class="requirement.met ? 'text-green-600' : 'text-gray-500'">{{ requirement.text }}</span>
                  </div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                <div class="relative">
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                  </svg>
                  <input v-model="password.confirm" :type="confirmPasswordFieldType" 
                    class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                  <button @click="toggleConfirmPasswordVisibility" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                      <path v-if="!showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                </div>
                <div class="mt-2">
                  <div v-if="password.new && password.confirm" class="flex items-center text-sm">
                    <svg class="w-4 h-4 mr-2" :class="passwordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="passwordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      <path v-if="!passwordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span :class="passwordMatch ? 'text-green-600' : 'text-red-600'">
                      {{ passwordMatch ? 'Passwords match' : 'Passwords do not match' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <button @click="changePassword" 
                :disabled="!canChangePassword || changingPassword"
                :class="[canChangePassword && !changingPassword ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed', changingPassword ? 'bg-blue-400 cursor-wait' : '']"
                class="px-4 py-2 text-white rounded-lg transition-colors flex items-center">
                <svg v-if="!changingPassword" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <svg v-if="changingPassword" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ changingPassword ? 'Changing Password...' : 'Change Password' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Business Verification and Operational Distributors -->
      <div class="space-y-6" v-if="userInfo.role === 'distributor'">
        <!-- Business Verification Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Business Verification</h2>
            <div class="p-2 bg-purple-50 rounded-lg">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
          </div>

          <!-- Status Banner -->
          <div v-if="verificationData" class="mb-6 p-4 rounded-lg border" 
            :class="verificationData.status === 'approved' ? 'bg-green-50 border-green-200' : 
                   verificationData.status === 'pending' ? 'bg-yellow-50 border-yellow-200' : 
                   verificationData.status === 'rejected' ? 'bg-red-50 border-red-200' : 
                   'bg-blue-50 border-blue-200'">
            <div class="flex">
              <svg class="w-5 h-5 mr-3 flex-shrink-0" 
                :class="verificationData.status === 'approved' ? 'text-green-600' : 
                       verificationData.status === 'pending' ? 'text-yellow-600' : 
                       verificationData.status === 'rejected' ? 'text-red-600' : 
                       'text-blue-600'" 
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="verificationData.status === 'approved'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                <path v-if="verificationData.status === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path v-if="verificationData.status === 'rejected'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path v-if="!verificationData.status" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <h4 class="font-medium" 
                  :class="verificationData.status === 'approved' ? 'text-green-800' : 
                         verificationData.status === 'pending' ? 'text-yellow-800' : 
                         verificationData.status === 'rejected' ? 'text-red-800' : 
                         'text-blue-800'">
                  {{ getVerificationStatusTitle() }}
                </h4>
                <p class="text-sm mt-1" 
                  :class="verificationData.status === 'approved' ? 'text-green-700' : 
                         verificationData.status === 'pending' ? 'text-yellow-700' : 
                         verificationData.status === 'rejected' ? 'text-red-700' : 
                         'text-blue-700'">
                  {{ getVerificationStatusMessage() }}
                </p>
                <p v-if="verificationData.rejection_reason" class="text-sm mt-2 text-red-700">
                  <strong>Reason:</strong> {{ verificationData.rejection_reason }}
                </p>
              </div>
            </div>
          </div>

          <!-- Verification Form -->
          <div v-if="!verificationData || verificationData.status === 'rejected' || (verificationData && verificationData.has_submitted === false)" class="space-y-8">
            <!-- Company Information -->
            <div>
              <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Company Information</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Company Name
                    <span class="text-red-500">*</span>
                  </label>
                  <input v-model="verificationForm.company_name" type="text" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Enter your company name">
                </div>
              </div>
            </div>

            <!-- Valid ID Section -->
            <div>
              <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Valid ID Requirements</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Type of Valid ID
                    <span class="text-red-500">*</span>
                  </label>
                  <select v-model="verificationForm.valid_id_type" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
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
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    ID Number
                    <span class="text-red-500">*</span>
                  </label>
                  <input v-model="verificationForm.id_number" type="text" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="Enter your ID number">
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Photo of Valid ID
                    <span class="text-red-500">*</span>
                  </label>
                  <div @click="triggerFileInput('valid_id_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'valid_id_photo')"
                    class="mt-1 cursor-pointer flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600 justify-center">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                          <span>Upload a file</span>
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Front side of ID required</span>
                      </div>
                      <p v-if="verificationForm.valid_id_photo" class="text-sm text-green-600 mt-2">
                        ✓ File selected: {{ verificationForm.valid_id_photo.name }}
                      </p>
                    </div>
                  </div>
                  <input ref="valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                    @change="handleFileChange($event, 'valid_id_photo')">
                </div>
              </div>
            </div>

            <!-- Business Documents -->
            <div>
              <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Business Documents</h3>
              <div class="space-y-6">
                
                <!-- DTI Certificate -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Photo of DTI Certificate
                    <span class="text-red-500">*</span>
                  </label>
                  <div @click="triggerFileInput('dti_certificate_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'dti_certificate_photo')"
                    class="mt-1 cursor-pointer flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600 justify-center">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                          <span>Upload DTI Certificate</span>
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Ensure certificate details are clear and readable</span>
                      </div>
                      <p v-if="verificationForm.dti_certificate_photo" class="text-sm text-green-600 mt-2">
                        ✓ File selected: {{ verificationForm.dti_certificate_photo.name }}
                      </p>
                    </div>
                  </div>
                  <input ref="dti_certificate_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                    @change="handleFileChange($event, 'dti_certificate_photo')">
                </div>

                <!-- Mayor's Permit -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Photo of Mayor's Permit to Operate
                    <span class="text-red-500">*</span>
                  </label>
                  <div @click="triggerFileInput('mayor_permit_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'mayor_permit_photo')"
                    class="mt-1 cursor-pointer flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600 justify-center">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                          <span>Upload Mayor's Permit</span>
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Current year's permit required</span>
                      </div>
                      <p v-if="verificationForm.mayor_permit_photo" class="text-sm text-green-600 mt-2">
                        ✓ File selected: {{ verificationForm.mayor_permit_photo.name }}
                      </p>
                    </div>
                  </div>
                  <input ref="mayor_permit_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                    @change="handleFileChange($event, 'mayor_permit_photo')">
                </div>

                <!-- Barangay Clearance -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Photo of Barangay Business Clearance
                    <span class="text-red-500">*</span>
                  </label>
                  <div @click="triggerFileInput('barangay_clearance_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'barangay_clearance_photo')"
                    class="mt-1 cursor-pointer flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600 justify-center">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                          <span>Upload Barangay Clearance</span>
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Issued within the last 6 months</span>
                      </div>
                      <p v-if="verificationForm.barangay_clearance_photo" class="text-sm text-green-600 mt-2">
                        ✓ File selected: {{ verificationForm.barangay_clearance_photo.name }}
                      </p>
                    </div>
                  </div>
                  <input ref="barangay_clearance_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                    @change="handleFileChange($event, 'barangay_clearance_photo')">
                </div>

                <!-- Business Registration Plate -->
                <div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Business Registration Plate Number
                        <span class="text-red-500">*</span>
                      </label>
                      <input v-model="verificationForm.business_registration_number" type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Enter plate number">
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Photo of Business Registration Plate
                        <span class="text-red-500">*</span>
                      </label>
                      <div @click="triggerFileInput('business_registration_photo')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'business_registration_photo')"
                        class="mt-1 cursor-pointer flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                        <div class="space-y-1 text-center">
                          <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <div class="text-sm text-gray-600">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                              <span>Upload Photo</span>
                            </label>
                          </div>
                          <p class="text-xs text-gray-500">Clear photo showing plate</p>
                          <p v-if="verificationForm.business_registration_photo" class="text-sm text-green-600 mt-2">
                            ✓ File selected: {{ verificationForm.business_registration_photo.name }}
                          </p>
                        </div>
                      </div>
                      <input ref="business_registration_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                        @change="handleFileChange($event, 'business_registration_photo')">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Additional Notes -->
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
              <div class="flex">
                <svg class="w-5 h-5 text-gray-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                  <h4 class="font-medium text-gray-800">Important Notes</h4>
                  <ul class="mt-2 text-sm text-gray-600 list-disc list-inside space-y-1">
                    <li>All documents must be current and valid</li>
                    <li>Photos should be clear and all text readable</li>
                    <li>Maximum file size per document: 5MB</li>
                    <li>Accepted formats: JPG, PNG, PDF</li>
                    <li>Verification process takes 3-5 business days</li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-gray-200">
              <button @click="submitVerification" 
                :disabled="!isVerificationFormValid || submittingVerification"
                :class="[isVerificationFormValid && !submittingVerification ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed', submittingVerification ? 'bg-blue-400 cursor-wait' : '']"
                class="w-full px-4 py-3 text-white rounded-lg transition-colors flex items-center justify-center">
                <svg v-if="!submittingVerification" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <svg v-if="submittingVerification" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ submittingVerification ? 'Submitting...' : 'Submit for Verification' }}
              </button>
              <p class="text-xs text-gray-500 text-center mt-2">
                By submitting, you confirm that all information provided is accurate and authentic
              </p>
            </div>
          </div>

          <!-- View Submitted Documents -->
          <div v-if="verificationData && verificationData.has_submitted === true && (verificationData.status === 'pending' || verificationData.status === 'approved')" 
            class="space-y-6">
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
              <h3 class="text-md font-semibold text-gray-800 mb-3">Submitted Documents</h3>
              <div class="space-y-4">
                <div>
                  <p class="text-sm text-gray-600 mb-1">Company Name:</p>
                  <p class="font-medium">{{ verificationData.company_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 mb-1">ID Type:</p>
                  <p class="font-medium">{{ verificationData.id_type_name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 mb-1">ID Number:</p>
                  <p class="font-medium">{{ verificationData.id_number }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 mb-1">Business Registration Number:</p>
                  <p class="font-medium">{{ verificationData.business_registration_number }}</p>
                </div>
                <div class="pt-3 border-t border-gray-200">
                  <p class="text-sm text-gray-600 mb-2">Uploaded Documents:</p>
                  <div class="grid grid-cols-2 gap-2">
                    <a v-for="(url, field) in verificationData.photos" :key="field" 
                      :href="url" target="_blank" 
                      class="p-2 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center">
                      <svg class="w-5 h-5 mx-auto text-blue-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      <span class="text-xs text-gray-700">{{ formatFieldName(field) }}</span>
                    </a>
                  </div>
                </div>
                <div class="pt-3 border-t border-gray-200">
                  <p class="text-sm text-gray-600 mb-1">Submitted:</p>
                  <p class="font-medium">{{ formatDateTime(verificationData.submitted_at) }}</p>
                </div>
                <div v-if="verificationData.status === 'pending'">
                  <p class="text-sm text-yellow-600 bg-yellow-50 p-2 rounded">
                    ⏳ Your documents are under review. Please wait for admin approval.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Operational Distributors Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Operational Distributors</h2>
            <div class="p-2 bg-indigo-50 rounded-lg">
              <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
            </div>
          </div>

          <!-- Stats Overview -->
          <div v-if="operationalStats" class="mb-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
              <p class="text-sm text-gray-600">Total</p>
              <p class="text-2xl font-bold text-gray-900">{{ operationalStats.total }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
              <p class="text-sm text-green-600">Active</p>
              <p class="text-2xl font-bold text-green-900">{{ operationalStats.active }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
              <p class="text-sm text-yellow-600">Pending</p>
              <p class="text-2xl font-bold text-yellow-900">{{ operationalStats.pending }}</p>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
              <p class="text-sm text-blue-600">With Accounts</p>
              <p class="text-2xl font-bold text-blue-900">{{ operationalStats.with_accounts }}</p>
            </div>
          </div>

          <!-- Add New Operational Distributor Button -->
          <div class="mb-6">
            <button @click="showOperationalForm = !showOperationalForm" 
              :disabled="!isVerifiedDistributor"
              :class="[isVerifiedDistributor ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed']"
              class="w-full px-4 py-3 text-white rounded-lg transition-colors flex items-center justify-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              {{ isVerifiedDistributor ? 'Add New Operational Distributor' : 'Verify Your Business First' }}
            </button>
            <p v-if="!isVerifiedDistributor" class="text-sm text-red-600 text-center mt-2">
              You must be verified as a distributor to create operational distributors
            </p>
          </div>

          <!-- Create Operational Distributor Form -->
          <div v-if="showOperationalForm && isVerifiedDistributor" class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="text-md font-semibold text-gray-800 mb-4">Create New Operational Distributor</h3>
            
            <div class="space-y-4">
              <!-- Personal Information -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    First Name <span class="text-red-500">*</span>
                  </label>
                  <input v-model="operationalForm.first_name" type="text" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    placeholder="Enter first name">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Last Name <span class="text-red-500">*</span>
                  </label>
                  <input v-model="operationalForm.last_name" type="text" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    placeholder="Enter last name">
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500">*</span></label>
                  <input v-model="operationalForm.email" type="email" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    placeholder="Enter email address">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number <span class="text-red-500">*</span>
                    <span class="text-xs text-gray-500">(11 digits only)</span>
                  </label>
                  <input v-model="operationalForm.phone" type="tel" 
                    @input="validatePhoneNumber"
                    maxlength="11"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                    placeholder="09123456789">
                  <p v-if="phoneError" class="text-xs text-red-500 mt-1">{{ phoneError }}</p>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea v-model="operationalForm.address" rows="2" 
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors resize-none"
                  placeholder="Optional"></textarea>
              </div>

              <!-- Password Fields -->
              <div class="pt-4 border-t border-gray-200">
                <h4 class="text-md font-medium text-gray-800 mb-3">Account Password</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <input v-model="operationalForm.password" :type="showOperationalPassword ? 'text' : 'password'" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="Enter password">
                      <button @click="showOperationalPassword = !showOperationalPassword" 
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="showOperationalPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                          <path v-if="!showOperationalPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </button>
                    </div>
                    <div class="mt-2 space-y-1">
                      <div v-for="requirement in operationalPasswordRequirements" :key="requirement.text" 
                        class="flex items-center text-xs">
                        <svg class="w-3 h-3 mr-1" :class="requirement.met ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span :class="requirement.met ? 'text-green-600' : 'text-gray-500'">{{ requirement.text }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Confirm Password <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <input v-model="operationalForm.password_confirmation" :type="showOperationalConfirmPassword ? 'text' : 'password'" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                        placeholder="Confirm password">
                      <button @click="showOperationalConfirmPassword = !showOperationalConfirmPassword" 
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="showOperationalConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                          <path v-if="!showOperationalConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </button>
                    </div>
                    <div class="mt-2">
                      <div v-if="operationalForm.password && operationalForm.password_confirmation" class="flex items-center text-xs">
                        <svg class="w-3 h-3 mr-1" :class="operationalPasswordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="operationalPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          <path v-if="!operationalPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span :class="operationalPasswordMatch ? 'text-green-600' : 'text-red-600'">
                          {{ operationalPasswordMatch ? 'Passwords match' : 'Passwords do not match' }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- ID Verification -->
              <div class="pt-4 border-t border-gray-200">
                <h4 class="text-md font-medium text-gray-800 mb-3">ID Verification</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Type of Valid ID <span class="text-red-500">*</span>
                    </label>
                    <select v-model="operationalForm.valid_id_type" 
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
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
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      ID Number <span class="text-red-500">*</span>
                    </label>
                    <input v-model="operationalForm.id_number" type="text" 
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                      placeholder="Enter ID number">
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Photo of Valid ID <span class="text-red-500">*</span>
                  </label>
                  <div @click="triggerOperationalFileInput('valid_id_photo')" 
                    @dragover.prevent @drop.prevent="handleOperationalFileDrop($event, 'valid_id_photo')"
                    class="cursor-pointer flex justify-center px-4 py-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-indigo-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600 justify-center">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                          <span>Upload ID Photo</span>
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <p v-if="operationalForm.valid_id_photo" class="text-sm text-green-600 mt-2">
                        ✓ File selected: {{ operationalForm.valid_id_photo.name }}
                      </p>
                    </div>
                  </div>
                  <input ref="operational_valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                    @change="handleOperationalFileChange($event, 'valid_id_photo')">
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button @click="createOperationalDistributor" 
                  :disabled="!isOperationalFormValid || creatingOperational"
                  :class="[isOperationalFormValid && !creatingOperational ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed', creatingOperational ? 'bg-indigo-400 cursor-wait' : '']"
                  class="flex-1 px-4 py-2 text-white rounded-lg transition-colors flex items-center justify-center">
                  <svg v-if="!creatingOperational" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  <svg v-if="creatingOperational" class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  {{ creatingOperational ? 'Creating...' : 'Create Operational Distributor' }}
                </button>
                <button @click="cancelOperationalForm" 
                  class="px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                  Cancel
                </button>
              </div>
            </div>
          </div>

          <!-- Operational Distributors Table -->
          <div v-if="operationalDistributors.length > 0">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-md font-semibold text-gray-800">Operational Distributors List</h3>
              <div class="relative">
                <input v-model="operationalSearch" type="text" 
                  placeholder="Search..." 
                  class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-48">
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Verification</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="distributor in filteredOperationalDistributors" :key="distributor.id">
                    <td class="px-4 py-3">
                      <div>
                        <p class="font-medium text-gray-900">{{ distributor.full_name }}</p>
                        <p class="text-sm text-gray-500">Created: {{ formatDate(distributor.created_at) }}</p>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <div>
                        <p class="text-sm text-gray-900">{{ distributor.email || 'No email' }}</p>
                        <p class="text-sm text-gray-500">{{ distributor.phone || 'No phone' }}</p>
                        <p v-if="distributor.has_user_account" class="text-xs text-green-600 mt-1">
                          ✓ User account created
                        </p>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <div>
                        <p class="text-sm text-gray-900">{{ distributor.id_type_name }}</p>
                        <p class="text-sm text-gray-500">{{ distributor.id_number }}</p>
                        <a v-if="distributor.valid_id_photo_url" :href="distributor.valid_id_photo_url" target="_blank" 
                          class="text-xs text-indigo-600 hover:text-indigo-800">
                          View ID
                        </a>
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <span :class="distributor.status === 'active' ? 'bg-green-100 text-green-800' : distributor.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                        {{ distributor.status | capitalize }}
                      </span>
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex space-x-2">
                        <button @click="viewOperationalDistributor(distributor.id)" 
                          class="p-1 text-indigo-600 hover:text-indigo-800">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                        <button v-if="distributor.status !== 'active'" @click="activateOperationalDistributor(distributor.id)" 
                          class="p-1 text-green-600 hover:text-green-800">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          </svg>
                        </button>
                        <button v-if="distributor.status === 'active'" @click="deactivateOperationalDistributor(distributor.id)" 
                          class="p-1 text-yellow-600 hover:text-yellow-800">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                          </svg>
                        </button>
                        <button @click="deleteOperationalDistributor(distributor.id)" 
                          class="p-1 text-red-600 hover:text-red-800">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="operationalPagination" class="mt-4 flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing {{ operationalPagination.from }} to {{ operationalPagination.to }} of {{ operationalPagination.total }} results
              </div>
              <div class="flex space-x-2">
                <button @click="changeOperationalPage(operationalPagination.current_page - 1)" 
                  :disabled="operationalPagination.current_page === 1"
                  class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                  Previous
                </button>
                <button @click="changeOperationalPage(operationalPagination.current_page + 1)" 
                  :disabled="operationalPagination.current_page === operationalPagination.last_page"
                  class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                  Next
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No operational distributors</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by creating a new operational distributor.</p>
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
      savingNotifications: false,
      changingPassword: false,
      submittingVerification: false,
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
      
      // Operational Distributors
      showOperationalForm: false,
      creatingOperational: false,
      operationalDistributors: [],
      operationalStats: null,
      operationalPagination: null,
      operationalSearch: '',
      phoneError: '',
      showOperationalPassword: false,
      showOperationalConfirmPassword: false,
      operationalForm: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        password: '',
        password_confirmation: ''
      },
      currentOperationalPage: 1
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
    },
    isVerifiedDistributor() {
      return this.verificationData && this.verificationData.status === 'approved'
    },
    operationalPasswordRequirements() {
      const password = this.operationalForm.password
      return [
        {
          text: 'At least 8 characters',
          met: password.length >= 8
        },
        {
          text: 'Contains uppercase letter',
          met: /[A-Z]/.test(password)
        },
        {
          text: 'Contains lowercase letter',
          met: /[a-z]/.test(password)
        },
        {
          text: 'Contains number',
          met: /\d/.test(password)
        }
      ]
    },
    operationalPasswordMatch() {
      return this.operationalForm.password === this.operationalForm.password_confirmation
    },
    isOperationalFormValid() {
      return this.operationalForm.first_name &&
             this.operationalForm.last_name &&
             this.operationalForm.email &&
             this.operationalForm.phone &&
             this.operationalForm.phone.length === 11 &&
             /^\d+$/.test(this.operationalForm.phone) &&
             this.operationalForm.valid_id_type &&
             this.operationalForm.id_number &&
             this.operationalForm.valid_id_photo &&
             this.operationalForm.password &&
             this.operationalForm.password_confirmation &&
             this.operationalPasswordRequirements.every(req => req.met) &&
             this.operationalPasswordMatch
    },
    filteredOperationalDistributors() {
      if (!this.operationalSearch) {
        return this.operationalDistributors
      }
      
      const search = this.operationalSearch.toLowerCase()
      return this.operationalDistributors.filter(distributor => {
        return distributor.full_name.toLowerCase().includes(search) ||
               distributor.email?.toLowerCase().includes(search) ||
               distributor.phone?.toLowerCase().includes(search) ||
               distributor.id_number.toLowerCase().includes(search)
      })
    }
  },
  async created() {
    await this.fetchUserData()
    await this.fetchDistributorData()
    if (this.userInfo.role === 'distributor') {
      await this.fetchVerificationData()
      await this.fetchOperationalDistributors()
      await this.fetchOperationalStats()
    }
    this.setOriginalData()
  },
  methods: {
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
          borderRadius: "8px",
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
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
          borderRadius: "8px",
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
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
          borderRadius: "8px",
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
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
          borderRadius: "8px",
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
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
        this.showErrorToast(error.response?.data?.message || 'Failed to load profile data. Please try again.')
      } finally {
        this.loading = false
      }
    },

    async fetchDistributorData() {
      if (this.userInfo.role === 'distributor') {
        try {
          // Fetch distributor data from the distributor_requirements table
          const response = await axios.get('/distributor/requirements')
          if (response.data.status === 'success') {
            const data = response.data.data
            
            // Fill distributorInfo with data from verification
            this.distributorInfo = {
              company_name: data.company_name || '',
              business_registration_number: data.business_registration_number || '',
              valid_id_type: data.valid_id_type || '',
              valid_id_type_display: data.id_type_name || '',
              id_number: data.id_number || ''
            }
          } else {
            // If no data found, set empty values
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
          // Set empty values on error
          this.distributorInfo = {
            company_name: '',
            business_registration_number: '',
            valid_id_type: '',
            valid_id_type_display: '',
            id_number: ''
          }
        }
      }
    },

    async fetchVerificationData() {
      try {
        const response = await axios.get('/distributor/requirements')
        if (response.data.status === 'success') {
          this.verificationData = response.data.data
          // Log for debugging
          console.log('Verification data fetched:', this.verificationData)
          
          // Pre-fill verification form with existing data if available and status is rejected
          if (this.verificationData && this.verificationData.status === 'rejected') {
            this.verificationForm.company_name = this.verificationData.company_name || ''
            this.verificationForm.valid_id_type = this.verificationData.valid_id_type || ''
            this.verificationForm.id_number = this.verificationData.id_number || ''
            this.verificationForm.business_registration_number = this.verificationData.business_registration_number || ''
          }
        }
      } catch (error) {
        console.error('Error fetching verification data:', error)
        // Set verificationData to null on error
        this.verificationData = null
      }
    },

    // Operational Distributor Methods
    async fetchOperationalDistributors(page = 1) {
      if (!this.isVerifiedDistributor) return
      
      try {
        const response = await axios.get(`/distributor/operational-distributors?page=${page}&per_page=5`)
        if (response.data.status === 'success') {
          this.operationalDistributors = response.data.data.operational_distributors
          this.operationalPagination = response.data.data.pagination
        }
      } catch (error) {
        console.error('Error fetching operational distributors:', error)
      }
    },
    
    async fetchOperationalStats() {
      if (!this.isVerifiedDistributor) return
      
      try {
        const response = await axios.get('/distributor/operational-distributors/statistics')
        if (response.data.status === 'success') {
          this.operationalStats = response.data.data
        }
      } catch (error) {
        console.error('Error fetching operational stats:', error)
      }
    },
    
    validatePhoneNumber() {
      const phone = this.operationalForm.phone
      
      if (phone.length > 0 && phone.length !== 11) {
        this.phoneError = 'Phone number must be exactly 11 digits'
      } else if (!/^\d+$/.test(phone)) {
        this.phoneError = 'Phone number must contain only digits'
      } else {
        this.phoneError = ''
      }
    },
    
    async createOperationalDistributor() {
      if (!this.isOperationalFormValid || this.creatingOperational) return
      
      this.creatingOperational = true
      try {
        const formData = new FormData()
        
        // Append text fields
        formData.append('first_name', this.operationalForm.first_name)
        formData.append('last_name', this.operationalForm.last_name)
        formData.append('email', this.operationalForm.email)
        formData.append('phone', this.operationalForm.phone)
        formData.append('address', this.operationalForm.address)
        formData.append('valid_id_type', this.operationalForm.valid_id_type)
        formData.append('id_number', this.operationalForm.id_number)
        formData.append('password', this.operationalForm.password)
        formData.append('password_confirmation', this.operationalForm.password_confirmation)
        
        // Append file
        formData.append('valid_id_photo', this.operationalForm.valid_id_photo)
        
        const response = await axios.post('/distributor/operational-distributors', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.status === 'success') {
          this.showSuccessToast(response.data.message)
          
          // Reset form
          this.operationalForm = {
            first_name: '',
            last_name: '',
            email: '',
            phone: '',
            address: '',
            valid_id_type: '',
            id_number: '',
            valid_id_photo: null,
            password: '',
            password_confirmation: ''
          }
          this.phoneError = ''
          this.showOperationalForm = false
          
          // Refresh data
          await this.fetchOperationalDistributors()
          await this.fetchOperationalStats()
        }
      } catch (error) {
        console.error('Error creating operational distributor:', error)
        let errorMessage = error.response?.data?.message || 'Failed to create operational distributor.'
        
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          errorMessage = errors.join(', ')
        }
        
        this.showErrorToast(errorMessage)
      } finally {
        this.creatingOperational = false
      }
    },
    
    async viewOperationalDistributor(id) {
      try {
        const response = await axios.get(`/distributor/operational-distributors/${id}`)
        if (response.data.status === 'success') {
          const distributor = response.data.data.operational_distributor
          this.showInfoToast(`Viewing ${distributor.full_name} - Status: ${distributor.status}`)
        }
      } catch (error) {
        console.error('Error viewing operational distributor:', error)
        this.showErrorToast('Failed to view operational distributor details.')
      }
    },
    
    async activateOperationalDistributor(id) {
      try {
        const response = await axios.post(`/distributor/operational-distributors/${id}/activate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor activated successfully.')
          await this.fetchOperationalDistributors()
          await this.fetchOperationalStats()
        }
      } catch (error) {
        console.error('Error activating operational distributor:', error)
        this.showErrorToast('Failed to activate operational distributor.')
      }
    },
    
    async deactivateOperationalDistributor(id) {
      try {
        const response = await axios.post(`/distributor/operational-distributors/${id}/deactivate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor deactivated successfully.')
          await this.fetchOperationalDistributors()
          await this.fetchOperationalStats()
        }
      } catch (error) {
        console.error('Error deactivating operational distributor:', error)
        this.showErrorToast('Failed to deactivate operational distributor.')
      }
    },
    
    async deleteOperationalDistributor(id) {
      if (!confirm('Are you sure you want to delete this operational distributor? This action cannot be undone.')) {
        return
      }
      
      try {
        const response = await axios.delete(`/distributor/operational-distributors/${id}`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor deleted successfully.')
          await this.fetchOperationalDistributors()
          await this.fetchOperationalStats()
        }
      } catch (error) {
        console.error('Error deleting operational distributor:', error)
        this.showErrorToast('Failed to delete operational distributor.')
      }
    },
    
    triggerOperationalFileInput(field) {
      this.$refs[`operational_${field}`].click()
    },
    
    handleOperationalFileChange(event, field) {
      const file = event.target.files[0]
      if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
          this.showFileErrorToast(`File "${file.name}" is too large. Maximum size is 5MB.`)
          return
        }
        
        // Check file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
        if (!validTypes.includes(file.type)) {
          this.showFileErrorToast(`File "${file.name}" must be JPG, PNG, or PDF.`)
          return
        }
        
        this.operationalForm[field] = file
      }
    },
    
    handleOperationalFileDrop(event, field) {
      event.preventDefault()
      const file = event.dataTransfer.files[0]
      if (file) {
        const input = {
          target: { files: [file] }
        }
        this.handleOperationalFileChange(input, field)
      }
    },
    
    cancelOperationalForm() {
      this.operationalForm = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        password: '',
        password_confirmation: ''
      }
      this.phoneError = ''
      this.showOperationalForm = false
    },
    
    changeOperationalPage(page) {
      this.currentOperationalPage = page
      this.fetchOperationalDistributors(page)
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
        this.showErrorToast(error.response?.data?.message || 'Failed to update profile. Please try again.')
        
        this.userInfo = JSON.parse(JSON.stringify(this.originalUserInfo))
      } finally {
        this.saving = false
      }
    },

    async saveDistributorInfo() {
      if (!this.distributorInfoChanged || this.savingDistributor) return
      
      this.savingDistributor = true
      try {
        // Update distributor information
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
        this.showErrorToast(error.response?.data?.message || 'Failed to update distributor information. Please try again.')
        
        this.distributorInfo = JSON.parse(JSON.stringify(this.originalDistributorInfo))
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
        this.showErrorToast(error.response?.data?.message || 'Failed to change password. Please try again.')
      } finally {
        this.changingPassword = false
      }
    },

    async submitVerification() {
      if (!this.isVerificationFormValid || this.submittingVerification) return
      
      this.submittingVerification = true
      try {
        const formData = new FormData()
        
        // Append text fields
        formData.append('company_name', this.verificationForm.company_name)
        formData.append('valid_id_type', this.verificationForm.valid_id_type)
        formData.append('id_number', this.verificationForm.id_number)
        formData.append('business_registration_number', this.verificationForm.business_registration_number)
        
        // Append files
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
          
          // Update distributor info with submitted data
          this.distributorInfo = {
            company_name: this.verificationData.company_name,
            business_registration_number: this.verificationData.business_registration_number,
            valid_id_type: this.verificationData.valid_id_type,
            valid_id_type_display: this.verificationData.id_type_name,
            id_number: this.verificationData.id_number
          }
          this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
          
          // Reset form
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
        } else {
          throw new Error(response.data.message || 'Failed to submit verification')
        }
      } catch (error) {
        console.error('Error submitting verification:', error)
        let errorMessage = error.response?.data?.message || 'Failed to submit verification. Please try again.'
        
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          errorMessage = errors.join(', ')
        }
        
        this.showErrorToast(errorMessage)
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
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
          this.showFileErrorToast(`File "${file.name}" is too large. Maximum size is 5MB.`)
          return
        }
        
        // Check file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
        if (!validTypes.includes(file.type)) {
          this.showFileErrorToast(`File "${file.name}" must be JPG, PNG, or PDF.`)
          return
        }
        
        this.verificationForm[field] = file
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
/* Custom Toastify Styles to ensure it's always on top */
.toastify {
  z-index: 99999 !important;
  position: fixed !important;
  top: 20px !important;
  right: 20px !important;
  max-width: 400px !important;
}

/* Ensure toast is always visible */
.toastify.on {
  opacity: 1 !important;
  transform: translateX(0) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .toastify {
    max-width: 90% !important;
    left: 5% !important;
    right: 5% !important;
    text-align: center !important;
  }
}
</style>