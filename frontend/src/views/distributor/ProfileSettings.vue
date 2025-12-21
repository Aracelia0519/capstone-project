<template>
  <div class="p-4 md:p-6">
    <!-- Page Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Profile & Settings</h1>
          <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
        </div>
        <div class="flex items-center space-x-3">
          <button @click="saveAllChanges" 
            :disabled="!hasUnsavedChanges"
            :class="hasUnsavedChanges ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
            class="px-4 py-2 text-white rounded-lg transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Save All Changes
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Distributor Info -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Distributor Information Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Distributor Information</h2>
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
                    <span class="text-2xl font-bold text-white">{{ getInitials(distributorInfo.name) }}</span>
                  </div>
                  <button @click="changeProfilePhoto" class="absolute -bottom-2 -right-2 p-1.5 bg-white rounded-full border border-gray-300 shadow-sm hover:bg-gray-50">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9zM15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                  </button>
                </div>
              </div>
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ distributorInfo.name }}</h3>
                <p class="text-gray-600 mb-2">{{ distributorInfo.role }}</p>
                <div class="flex flex-wrap gap-2">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Verified Distributor
                  </span>
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Account ID: {{ distributorInfo.accountId }}
                  </span>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-500">Member since</p>
                <p class="font-medium text-gray-900">{{ formatDate(distributorInfo.memberSince) }}</p>
              </div>
            </div>
          </div>

          <!-- Distributor Form -->
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company Name</label>
                <input v-model="distributorInfo.companyName" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Business Type</label>
                <select v-model="distributorInfo.businessType" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                  <option value="retail">Retail Distributor</option>
                  <option value="wholesale">Wholesale Distributor</option>
                  <option value="exclusive">Exclusive Distributor</option>
                  <option value="authorized">Authorized Reseller</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Person</label>
                <input v-model="distributorInfo.contactPerson" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <div class="relative">
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  <input v-model="distributorInfo.email" type="email" 
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
              <div class="relative">
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                <input v-model="distributorInfo.phone" type="tel" 
                  class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Business Address</label>
              <textarea v-model="distributorInfo.address" rows="3" 
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tax Identification Number</label>
                <input v-model="distributorInfo.tin" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Business License Number</label>
                <input v-model="distributorInfo.licenseNumber" type="text" 
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
              </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <button @click="saveDistributorInfo" 
                :disabled="!distributorInfoChanged"
                :class="distributorInfoChanged ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                class="px-4 py-2 text-white rounded-lg transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Update Distributor Information
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
                :disabled="!canChangePassword"
                :class="canChangePassword ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                class="px-4 py-2 text-white rounded-lg transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Change Password
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Notification Preferences -->
      <div class="space-y-6">
        <!-- Notification Preferences Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg md:text-xl font-semibold text-gray-800">Notification Preferences</h2>
            <div class="p-2 bg-purple-50 rounded-lg">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
              </svg>
            </div>
          </div>

          <div class="space-y-6">
            <!-- Email Notifications -->
            <div>
              <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email Notifications
              </h3>
              <div class="space-y-3">
                <div v-for="pref in emailPreferences" :key="pref.id" class="flex items-center justify-between">
                  <div>
                    <p class="font-medium text-gray-900">{{ pref.label }}</p>
                    <p class="text-xs text-gray-500">{{ pref.description }}</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="pref.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>
            </div>

            <!-- In-App Notifications -->
            <div class="pt-4 border-t border-gray-200">
              <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                In-App Notifications
              </h3>
              <div class="space-y-3">
                <div v-for="pref in appPreferences" :key="pref.id" class="flex items-center justify-between">
                  <div>
                    <p class="font-medium text-gray-900">{{ pref.label }}</p>
                    <p class="text-xs text-gray-500">{{ pref.description }}</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="pref.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>
            </div>

            <!-- SMS Notifications -->
            <div class="pt-4 border-t border-gray-200">
              <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                SMS Notifications
              </h3>
              <div class="space-y-3">
                <div v-for="pref in smsPreferences" :key="pref.id" class="flex items-center justify-between">
                  <div>
                    <p class="font-medium text-gray-900">{{ pref.label }}</p>
                    <p class="text-xs text-gray-500">{{ pref.description }}</p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="pref.enabled" type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
              <button @click="saveNotificationPreferences" 
                :disabled="!notificationsChanged"
                :class="notificationsChanged ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-400 cursor-not-allowed'"
                class="w-full px-4 py-2 text-white rounded-lg transition-colors flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Notification Settings
              </button>
            </div>
          </div>
        </div>

        <!-- Account Status Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <h3 class="text-sm font-semibold text-gray-700 mb-4">Account Status</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Account Status</span>
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Active
              </span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Two-Factor Auth</span>
              <button @click="toggleTwoFactor" 
                :class="twoFactorEnabled ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 hover:bg-gray-400'"
                class="px-3 py-1 text-white text-sm rounded-lg transition-colors">
                {{ twoFactorEnabled ? 'Enabled' : 'Enable' }}
              </button>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Last Login</span>
              <span class="text-sm font-medium text-gray-900">{{ formatDate(lastLogin) }}</span>
            </div>
            <div class="pt-4 border-t border-gray-200">
              <button @click="logout" class="w-full px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProfileSettings',
  data() {
    return {
      distributorInfo: {
        name: 'Juan Dela Cruz',
        role: 'Senior Distributor Manager',
        accountId: 'DIST-2024-001',
        memberSince: '2023-06-15',
        companyName: 'Cavite Paint Distributors Inc.',
        businessType: 'retail',
        contactPerson: 'Maria Santos',
        email: 'juan.delacruz@cavitepaint.ph',
        phone: '+63 912 345 6789',
        address: '123 Paint Street, Imus City, Cavite, Philippines 4103',
        tin: '123-456-789-000',
        licenseNumber: 'BL-2023-7890'
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
      smsPreferences: [
        {
          id: 1,
          label: 'Urgent Alerts',
          description: 'Critical alerts via SMS',
          enabled: true
        },
        {
          id: 2,
          label: 'Order Confirmations',
          description: 'SMS confirmation for large orders',
          enabled: false
        },
        {
          id: 3,
          label: 'Security Alerts',
          description: 'Account security notifications',
          enabled: true
        }
      ],
      originalNotifications: {},
      twoFactorEnabled: true,
      lastLogin: '2024-01-15T14:30:00'
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
    getInitials() {
      return (name) => {
        return name
          .split(' ')
          .map(word => word[0])
          .join('')
          .toUpperCase()
          .slice(0, 2)
      }
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
    distributorInfoChanged() {
      return JSON.stringify(this.distributorInfo) !== JSON.stringify(this.originalDistributorInfo)
    },
    notificationsChanged() {
      const current = {
        email: this.emailPreferences,
        app: this.appPreferences,
        sms: this.smsPreferences
      }
      return JSON.stringify(current) !== JSON.stringify(this.originalNotifications)
    },
    hasUnsavedChanges() {
      return this.distributorInfoChanged || this.notificationsChanged
    }
  },
  created() {
    // Store original data for comparison
    this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
    this.originalNotifications = {
      email: JSON.parse(JSON.stringify(this.emailPreferences)),
      app: JSON.parse(JSON.stringify(this.appPreferences)),
      sms: JSON.parse(JSON.stringify(this.smsPreferences))
    }
  },
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
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
    saveDistributorInfo() {
      if (!this.distributorInfoChanged) return
      
      // In a real app, this would be an API call
      console.log('Saving distributor info:', this.distributorInfo)
      this.originalDistributorInfo = JSON.parse(JSON.stringify(this.distributorInfo))
      
      alert('Distributor information updated successfully!')
    },
    changePassword() {
      if (!this.canChangePassword) return
      
      // In a real app, this would be an API call
      console.log('Changing password...')
      
      // Reset password fields
      this.password = {
        current: '',
        new: '',
        confirm: ''
      }
      
      alert('Password changed successfully!')
    },
    saveNotificationPreferences() {
      if (!this.notificationsChanged) return
      
      // In a real app, this would be an API call
      console.log('Saving notification preferences:', {
        email: this.emailPreferences,
        app: this.appPreferences,
        sms: this.smsPreferences
      })
      
      this.originalNotifications = {
        email: JSON.parse(JSON.stringify(this.emailPreferences)),
        app: JSON.parse(JSON.stringify(this.appPreferences)),
        sms: JSON.parse(JSON.stringify(this.smsPreferences))
      }
      
      alert('Notification preferences updated successfully!')
    },
    saveAllChanges() {
      if (this.distributorInfoChanged) {
        this.saveDistributorInfo()
      }
      if (this.notificationsChanged) {
        this.saveNotificationPreferences()
      }
    },
    toggleTwoFactor() {
      this.twoFactorEnabled = !this.twoFactorEnabled
      alert(`Two-factor authentication ${this.twoFactorEnabled ? 'enabled' : 'disabled'}`)
    },
    logout() {
      if (confirm('Are you sure you want to logout?')) {
        alert('Logging out...')
        // In a real app, this would clear auth tokens and redirect to login
      }
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
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

/* Smooth transitions */
.transition-colors {
  transition-property: background-color, border-color, color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Focus styles */
:focus {
  outline: none;
}

:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Toggle switch styling */
.peer:checked ~ .peer-checked\:bg-blue-600 {
  background-color: #2563eb;
}

.peer:checked ~ .peer-checked\:after\:translate-x-full::after {
  transform: translateX(100%);
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
}

@media (max-width: 640px) {
  .p-6 {
    padding: 1rem;
  }
  
  .space-y-6 > * + * {
    margin-top: 1rem;
  }
}

/* Loading skeleton animation */
@keyframes shimmer {
  0% {
    background-position: -468px 0;
  }
  100% {
    background-position: 468px 0;
  }
}

.shimmer {
  background: linear-gradient(to right, #f6f7f8 8%, #edeef1 18%, #f6f7f8 33%);
  background-size: 800px 104px;
  animation: shimmer 1.5s infinite linear;
}

/* Card hover effects */
.bg-white {
  transition: all 0.2s ease-in-out;
}

.bg-white:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Input focus effects */
input:focus, select:focus, textarea:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Button hover effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:disabled {
  cursor: not-allowed;
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

/* Avatar initials styling */
.text-2xl {
  font-weight: 700;
}

/* Password strength indicator */
.text-green-500 {
  color: #10b981;
}

.text-green-600 {
  color: #059669;
}

.text-red-500 {
  color: #ef4444;
}

.text-red-600 {
  color: #dc2626;
}

/* Notification toggle spacing */
.space-y-3 > * + * {
  margin-top: 0.75rem;
}

/* Section spacing */
.space-y-6 > * + * {
  margin-top: 1.5rem;
}
</style>