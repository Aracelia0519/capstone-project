<template>
  <div class="p-4 md:p-6">
    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-80 z-50 flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-600">Loading profile data...</p>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-red-800">{{ error }}</span>
        <button @click="error = ''" class="ml-auto text-red-500 hover:text-red-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Success Alert -->
    <div v-if="successMessage" class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
      <div class="flex items-center">
        <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-green-800">{{ successMessage }}</span>
        <button @click="successMessage = ''" class="ml-auto text-green-500 hover:text-green-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

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
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
              <div>
                <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Business License Number</label>
                <input v-model="distributorInfo.license_number" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <button @click="saveDistributorInfo" 
                :disabled="!distributorInfoChanged || savingDistributor"
                :class="[distributorInfoChanged && !savingDistributor ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed', savingDistributor ? 'bg-green-400 cursor-wait' : '']"
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

      <!--Authentication for Verification -->
      <div class="space-y-6">
        <!-- Verification Requirements Card -->
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
          <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex">
              <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <h4 class="font-medium text-blue-800">Verification Required</h4>
                <p class="text-sm text-blue-700 mt-1">Please upload all required documents for business verification. All fields are mandatory for account verification.</p>
              </div>
            </div>
          </div>

          <!-- Verification Form -->
          <div class="space-y-8">
            <!-- Company Information -->
            <div>
              <h3 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Company Information</h3>
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Company Name
                    <span class="text-red-500">*</span>
                  </label>
                  <input type="text" 
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
                  <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
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
                    Photo of Valid ID
                    <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                          <span>Upload a file</span>
                          <input type="file" class="sr-only" accept="image/*">
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Front side of ID required</span>
                      </div>
                    </div>
                  </div>
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
                  <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                          <span>Upload DTI Certificate</span>
                          <input type="file" class="sr-only" accept="image/*,.pdf">
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Ensure certificate details are clear and readable</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Mayor's Permit -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Photo of Mayor's Permit to Operate
                    <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                          <span>Upload Mayor's Permit</span>
                          <input type="file" class="sr-only" accept="image/*,.pdf">
                        </label>
                        <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Current year's permit required</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Barangay Clearance -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Photo of Barangay Business Clearance
                    <span class="text-red-500">*</span>
                  </label>
                  <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                    <div class="space-y-1 text-center">
                      <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                      </svg>
                      <div class="flex text-sm text-gray-600">
                        <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                          <span>Upload Barangay Clearance</span>
                          <input type="file" class="sr-only" accept="image/*,.pdf">
                        </label>
                          <p class="pl-1">or drag and drop</p>
                      </div>
                      <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                      <div class="mt-2">
                        <span class="text-xs text-gray-500">Issued within the last 6 months</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Business Registration Plate -->
                <div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Business Registration Plate Number
                        <span class="text-red-500">*</span>
                      </label>
                      <input type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Enter plate number">
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Photo of Business Registration Plate
                        <span class="text-red-500">*</span>
                      </label>
                      <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors">
                        <div class="space-y-1 text-center">
                          <svg class="mx-auto h-8 w-8 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <div class="text-sm text-gray-600">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
                              <span>Upload Photo</span>
                              <input type="file" class="sr-only" accept="image/*">
                            </label>
                          </div>
                          <p class="text-xs text-gray-500">Clear photo showing plate</p>
                        </div>
                      </div>
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
              <button 
                class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Submit for Verification
              </button>
              <p class="text-xs text-gray-500 text-center mt-2">
                By submitting, you confirm that all information provided is accurate and authentic
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from '@/utils/axios'

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
      error: '',
      successMessage: '',
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
        business_type: 'retail',
        license_number: '',
        tin_number: ''
      },
      originalDistributorInfo: {},
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      emailPreferences: [
        {
          id: 1,
          label: 'Order Updates',
          description: 'Receive updates on order status changes',
          enabled: true
        },
        {
          id: 2,
          label: 'Stock Alerts',
          description: 'Get notified when stock is low',
          enabled: true
        },
        {
          id: 3,
          label: 'Promotions & Offers',
          description: 'Receive special offers and promotions',
          enabled: false
        },
        {
          id: 4,
          label: 'Monthly Reports',
          description: 'Receive monthly sales reports',
          enabled: true
        },
        {
          id: 5,
          label: 'System Announcements',
          description: 'Important system updates and maintenance',
          enabled: true
        }
      ],
      appPreferences: [
        {
          id: 1,
          label: 'Order Notifications',
          description: 'In-app notifications for new orders',
          enabled: true
        },
        {
          id: 2,
          label: 'Price Changes',
          description: 'Get notified about price updates',
          enabled: true
        },
        {
          id: 3,
          label: 'New Features',
          description: 'Notifications about new system features',
          enabled: false
        },
        {
          id: 4,
          label: 'Reminders',
          description: 'Payment and order follow-up reminders',
          enabled: true
        }
      ],
      originalNotifications: {},
      twoFactorEnabled: true,
      lastLogin: new Date().toISOString()
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
    notificationsChanged() {
      const current = {
        email: this.emailPreferences,
        app: this.appPreferences
      }
      return JSON.stringify(current) !== JSON.stringify(this.originalNotifications)
    },
    hasUnsavedChanges() {
      return this.userInfoChanged || this.distributorInfoChanged || this.notificationsChanged
    }
  },
  async created() {
    await this.fetchUserData()
    await this.fetchDistributorData()
    this.setOriginalData()
  },
  methods: {
    async fetchUserData() {
      this.loading = true
      this.error = ''
      try {
        const response = await axios.get('/profile') // Changed from '/api/profile' to '/profile'
        if (response.data.status === 'success') {
          this.userInfo = response.data.data.user
        } else {
          throw new Error(response.data.message || 'Failed to fetch user data')
        }
      } catch (error) {
        console.error('Error fetching user data:', error)
        this.error = error.response?.data?.message || 'Failed to load profile data. Please try again.'
      } finally {
        this.loading = false
      }
    },

    async fetchDistributorData() {
      // This would be a separate API endpoint for distributor-specific data
      // For now, we'll use mock data or leave it empty
      if (this.userInfo.role === 'distributor') {
        try {
          // Example API call (you need to create this endpoint):
          // const response = await axios.get('/distributor/profile')
          // this.distributorInfo = response.data.data.distributorInfo
          
          // For now, use empty object or mock data
          this.distributorInfo = {
            company_name: '',
            business_type: 'retail',
            license_number: '',
            tin_number: ''
          }
        } catch (error) {
          console.error('Error fetching distributor data:', error)
        }
      }
    },

    setOriginalData() {
      this.originalUserInfo = JSON.parse(JSON.stringify(this.userInfo))
      this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
      this.originalNotifications = {
        email: JSON.parse(JSON.stringify(this.emailPreferences)),
        app: JSON.parse(JSON.stringify(this.appPreferences))
      }
    },

    async saveUserInfo() {
      if (!this.userInfoChanged || this.saving) return
      
      this.saving = true
      this.error = ''
      this.successMessage = ''
      
      try {
        const response = await axios.put('/profile', { // Changed from '/api/profile' to '/profile'
          first_name: this.userInfo.first_name,
          last_name: this.userInfo.last_name,
          email: this.userInfo.email,
          phone: this.userInfo.phone,
          address: this.userInfo.address
        })
        
        if (response.data.status === 'success') {
          this.originalUserInfo = JSON.parse(JSON.stringify(this.userInfo))
          this.successMessage = 'Personal information updated successfully!'
          
          // Update full name after saving
          this.userInfo.full_name = `${this.userInfo.first_name} ${this.userInfo.last_name}`
        } else {
          throw new Error(response.data.message || 'Failed to update profile')
        }
      } catch (error) {
        console.error('Error updating profile:', error)
        this.error = error.response?.data?.message || 'Failed to update profile. Please try again.'
        
        // Rollback changes on error
        this.userInfo = JSON.parse(JSON.stringify(this.originalUserInfo))
      } finally {
        this.saving = false
      }
    },

    async saveDistributorInfo() {
      if (!this.distributorInfoChanged || this.savingDistributor) return
      
      this.savingDistributor = true
      this.error = ''
      this.successMessage = ''
      
      try {
        // This would be a separate API endpoint for distributor-specific data
        // Example: const response = await axios.put('/distributor/profile', this.distributorInfo)
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
        this.successMessage = 'Distributor information updated successfully!'
      } catch (error) {
        console.error('Error updating distributor info:', error)
        this.error = error.response?.data?.message || 'Failed to update distributor information. Please try again.'
        
        // Rollback changes on error
        this.distributorInfo = JSON.parse(JSON.stringify(this.originalDistributorInfo))
      } finally {
        this.savingDistributor = false
      }
    },

    async changePassword() {
      if (!this.canChangePassword || this.changingPassword) return
      
      this.changingPassword = true
      this.error = ''
      this.successMessage = ''
      
      try {
        const response = await axios.put('/profile/password', { // Changed from '/api/profile/password' to '/profile/password'
          current_password: this.password.current,
          password: this.password.new,
          password_confirmation: this.password.confirm
        })
        
        if (response.data.status === 'success') {
          this.successMessage = 'Password changed successfully!'
          
          // Reset password fields
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
        this.error = error.response?.data?.message || 'Failed to change password. Please try again.'
      } finally {
        this.changingPassword = false
      }
    },

    async saveNotificationPreferences() {
      if (!this.notificationsChanged || this.savingNotifications) return
      
      this.savingNotifications = true
      this.error = ''
      this.successMessage = ''
      
      try {
        // This would be a separate API endpoint for notification preferences
        // Example: const response = await axios.put('/notifications', {
        //   email: this.emailPreferences,
        //   app: this.appPreferences
        // })
        
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        this.originalNotifications = {
          email: JSON.parse(JSON.stringify(this.emailPreferences)),
          app: JSON.parse(JSON.stringify(this.appPreferences))
        }
        this.successMessage = 'Notification preferences updated successfully!'
      } catch (error) {
        console.error('Error updating notification preferences:', error)
        this.error = error.response?.data?.message || 'Failed to update notification preferences. Please try again.'
      } finally {
        this.savingNotifications = false
      }
    },

    async saveAllChanges() {
      if (this.userInfoChanged) {
        await this.saveUserInfo()
      }
      if (this.userInfo.role === 'distributor' && this.distributorInfoChanged) {
        await this.saveDistributorInfo()
      }
      if (this.notificationsChanged) {
        await this.saveNotificationPreferences()
      }
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
      alert('Feature to change profile photo would open file picker')
    },

    toggleTwoFactor() {
      // This would be an API call to update 2FA status
      alert(`Two-factor authentication ${this.twoFactorEnabled ? 'enabled' : 'disabled'}`)
    },

    async logout() {
      if (confirm('Are you sure you want to logout?')) {
        try {
          await axios.post('/auth/logout') // Changed from '/api/auth/logout' to '/auth/logout'
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user')
          this.$router.push('/Landing/logIn')
        } catch (error) {
          console.error('Logout error:', error)
          // Still clear local storage and redirect even if API call fails
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user')
          this.$router.push('/Landing/logIn')
        }
      }
    }
  }
}
</script>

<style scoped>
  @import "../distributor/styles/profileSettings.css";
</style>