<template>
  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-600 mt-2">Manage your account information and settings</p>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Left Column - Navigation -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden sticky top-24">
            <!-- Profile Card -->
            <div class="p-6 border-b border-gray-200">
              <div class="flex items-center">
                <div class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                  <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="font-bold text-gray-900">{{ userProfile.name }}</h3>
                  <p class="text-sm text-gray-500">{{ userProfile.email }}</p>
                  <p class="text-xs text-gray-500 mt-1">Member since {{ userProfile.memberSince }}</p>
                </div>
              </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4">
              <ul class="space-y-1">
                <li v-for="item in navItems" :key="item.id">
                  <button
                    @click="activeTab = item.id"
                    :class="[
                      'w-full flex items-center space-x-3 px-3 py-3 rounded-lg transition-all',
                      activeTab === item.id
                        ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-blue-600 font-medium'
                        : 'text-gray-700 hover:bg-gray-50'
                    ]"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    <span>{{ item.label }}</span>
                  </button>
                </li>
              </ul>
            </nav>
          </div>
        </div>

        <!-- Right Column - Content -->
        <div class="lg:col-span-3">
          <!-- Personal Information Tab -->
          <div v-if="activeTab === 'personal'" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-bold text-gray-900">Personal Information</h2>
              <p class="text-gray-600 mt-1">Update your personal details</p>
            </div>
            
            <div class="p-6">
              <form @submit.prevent="updatePersonalInfo" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- First Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input
                      v-model="userProfile.firstName"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required
                    >
                  </div>
                  
                  <!-- Last Name -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input
                      v-model="userProfile.lastName"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required
                    >
                  </div>
                  
                  <!-- Email -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <div class="flex items-center">
                      <input
                        v-model="userProfile.email"
                        type="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                      >
                      <span v-if="userProfile.emailVerified" class="ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">
                        Verified
                      </span>
                      <button
                        v-else
                        type="button"
                        @click="verifyEmail"
                        class="ml-2 px-3 py-1 border border-blue-600 text-blue-600 text-xs font-semibold rounded hover:bg-blue-50"
                      >
                        Verify
                      </button>
                    </div>
                  </div>
                  
                  <!-- Phone -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input
                      v-model="userProfile.phone"
                      type="tel"
                      placeholder="+63 912 345 6789"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      required
                    >
                  </div>
                  
                  <!-- Birth Date -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input
                      v-model="userProfile.birthDate"
                      type="date"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                  </div>
                  
                  <!-- Gender -->
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                    <select
                      v-model="userProfile.gender"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">Prefer not to say</option>
                      <option value="male">Male</option>
                      <option value="female">Female</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>
                
                <!-- Save Button -->
                <div class="pt-6 border-t border-gray-200">
                  <div class="flex justify-end">
                    <button
                      type="button"
                      @click="resetForm"
                      class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium mr-3"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold"
                    >
                      Save Changes
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>

          <!-- Address Management Tab -->
          <div v-else-if="activeTab === 'addresses'" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <div class="flex justify-between items-center">
                <div>
                  <h2 class="text-xl font-bold text-gray-900">Address Book</h2>
                  <p class="text-gray-600 mt-1">Manage your shipping addresses</p>
                </div>
                <button
                  @click="openAddressModal"
                  class="px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold flex items-center"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add New Address
                </button>
              </div>
            </div>
            
            <div class="p-6">
              <!-- Addresses Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                  v-for="address in userProfile.addresses"
                  :key="address.id"
                  :class="[
                    'border rounded-xl p-5 transition-all',
                    address.isDefault
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="flex items-center mb-2">
                        <h3 class="font-semibold text-gray-900">{{ address.name }}</h3>
                        <span v-if="address.isDefault" class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                          Default
                        </span>
                      </div>
                      <p class="text-gray-600">{{ address.street }}</p>
                      <p class="text-gray-600">{{ address.city }}, {{ address.barangay }}</p>
                      <p class="text-gray-600">{{ address.province }} {{ address.zipCode }}</p>
                      <p class="text-gray-600 mt-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        {{ address.phone }}
                      </p>
                    </div>
                    <div class="flex space-x-2">
                      <button
                        @click="editAddress(address.id)"
                        class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </button>
                      <button
                        @click="deleteAddress(address.id)"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex space-x-3">
                      <button
                        v-if="!address.isDefault"
                        @click="setDefaultAddress(address.id)"
                        class="px-3 py-1 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm"
                      >
                        Set as Default
                      </button>
                      <button
                        @click="useAddress(address)"
                        class="px-3 py-1 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors text-sm"
                      >
                        Use this Address
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Empty State -->
              <div v-if="userProfile.addresses.length === 0" class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No addresses saved</h3>
                <p class="text-gray-500 mb-6">Add your first shipping address to get started</p>
                <button
                  @click="openAddressModal"
                  class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add Address
                </button>
              </div>
            </div>
          </div>

          <!-- Security Tab -->
          <div v-else-if="activeTab === 'security'" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-bold text-gray-900">Security Settings</h2>
              <p class="text-gray-600 mt-1">Manage your password and security preferences</p>
            </div>
            
            <div class="p-6">
              <!-- Change Password -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                <form @submit.prevent="changePassword" class="space-y-4 max-w-md">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <div class="relative">
                      <input
                        v-model="passwordData.currentPassword"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10"
                        required
                      >
                      <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                      <input
                        v-model="passwordData.newPassword"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10"
                        required
                      >
                      <button
                        type="button"
                        @click="showNewPassword = !showNewPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters with uppercase, lowercase, and number</p>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <div class="relative">
                      <input
                        v-model="passwordData.confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10"
                        required
                      >
                      <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div class="pt-4">
                    <button
                      type="submit"
                      class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold"
                    >
                      Update Password
                    </button>
                  </div>
                </form>
              </div>

              <!-- Two-Factor Authentication -->
              <div class="border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Two-Factor Authentication</h3>
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-gray-600">Add an extra layer of security to your account</p>
                    <p class="text-sm text-gray-500 mt-1">Currently: <span :class="userProfile.twoFactorEnabled ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                      {{ userProfile.twoFactorEnabled ? 'Enabled' : 'Disabled' }}
                    </span></p>
                  </div>
                  <button
                    @click="toggleTwoFactor"
                    :class="[
                      'px-4 py-2 rounded-lg font-semibold transition-all',
                      userProfile.twoFactorEnabled
                        ? 'bg-red-100 text-red-700 hover:bg-red-200'
                        : 'bg-green-100 text-green-700 hover:bg-green-200'
                    ]"
                  >
                    {{ userProfile.twoFactorEnabled ? 'Disable' : 'Enable' }} 2FA
                  </button>
                </div>
              </div>

              <!-- Session Management -->
              <div class="border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Sessions</h3>
                <div class="space-y-4">
                  <div
                    v-for="session in userProfile.sessions"
                    :key="session.id"
                    class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                  >
                    <div class="flex items-center">
                      <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="session.device === 'mobile'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-gray-900">{{ session.device }} • {{ session.browser }}</p>
                        <p class="text-sm text-gray-500">{{ session.location }} • {{ session.lastActive }}</p>
                      </div>
                    </div>
                    <div>
                      <span v-if="session.current" class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">
                        Current
                      </span>
                      <button
                        v-else
                        @click="endSession(session.id)"
                        class="px-3 py-1 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors text-sm"
                      >
                        End Session
                      </button>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button
                    @click="endAllSessions"
                    class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors font-medium"
                  >
                    End All Other Sessions
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Preferences Tab -->
          <div v-else-if="activeTab === 'preferences'" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-bold text-gray-900">Preferences</h2>
              <p class="text-gray-600 mt-1">Customize your experience</p>
            </div>
            
            <div class="p-6">
              <!-- Notification Preferences -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h3>
                <div class="space-y-4">
                  <div v-for="notification in notificationPreferences" :key="notification.id" class="flex items-center justify-between">
                    <div>
                      <p class="font-medium text-gray-900">{{ notification.label }}</p>
                      <p class="text-sm text-gray-500">{{ notification.description }}</p>
                    </div>
                    <div class="flex items-center">
                      <span class="mr-3 text-sm text-gray-500">{{ notification.enabled ? 'On' : 'Off' }}</span>
                      <button
                        @click="toggleNotification(notification.id)"
                        :class="[
                          'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                          notification.enabled ? 'bg-blue-600' : 'bg-gray-300'
                        ]"
                      >
                        <span :class="[
                          'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                          notification.enabled ? 'translate-x-6' : 'translate-x-1'
                        ]"></span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Email Preferences -->
              <div class="border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Preferences</h3>
                <div class="space-y-4">
                  <div v-for="email in emailPreferences" :key="email.id" class="flex items-center justify-between">
                    <div>
                      <p class="font-medium text-gray-900">{{ email.label }}</p>
                      <p class="text-sm text-gray-500">{{ email.description }}</p>
                    </div>
                    <div class="flex items-center">
                      <span class="mr-3 text-sm text-gray-500">{{ email.enabled ? 'On' : 'Off' }}</span>
                      <button
                        @click="toggleEmailPreference(email.id)"
                        :class="[
                          'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                          email.enabled ? 'bg-blue-600' : 'bg-gray-300'
                        ]"
                      >
                        <span :class="[
                          'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                          email.enabled ? 'translate-x-6' : 'translate-x-1'
                        ]"></span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Language & Region -->
              <div class="border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Language & Region</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                    <select
                      v-model="userProfile.language"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="en">English</option>
                      <option value="fil">Filipino</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time Zone</label>
                    <select
                      v-model="userProfile.timezone"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="Asia/Manila">Philippine Time (UTC+8)</option>
                      <option value="UTC">UTC</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Save Preferences -->
              <div class="border-t border-gray-200 pt-8">
                <div class="flex justify-end">
                  <button
                    @click="savePreferences"
                    class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold"
                  >
                    Save Preferences
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Account Status Tab -->
          <div v-else-if="activeTab === 'account'" class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200">
              <h2 class="text-xl font-bold text-gray-900">Account Status</h2>
              <p class="text-gray-600 mt-1">Manage your account membership and status</p>
            </div>
            
            <div class="p-6">
              <!-- Account Type -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Type</h3>
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-xl font-bold text-gray-900">{{ userProfile.accountType }}</h4>
                      <p class="text-gray-600 mt-1">Since {{ userProfile.memberSince }}</p>
                      <div class="flex items-center mt-4 space-x-4">
                        <div class="flex items-center">
                          <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          <span>Priority Support</span>
                        </div>
                        <div class="flex items-center">
                          <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          <span>Exclusive Discounts</span>
                        </div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-3xl font-bold text-gray-900">Free</div>
                      <p class="text-gray-600">Current Plan</p>
                      <button class="mt-4 px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                        Upgrade Plan
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Purchase History -->
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Purchase Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center">
                      <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="text-2xl font-bold text-gray-900">{{ userProfile.totalOrders }}</p>
                        <p class="text-sm text-gray-500">Total Orders</p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center">
                      <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="text-2xl font-bold text-gray-900">₱{{ userProfile.totalSpent.toLocaleString() }}</p>
                        <p class="text-sm text-gray-500">Total Spent</p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <div class="flex items-center">
                      <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="text-2xl font-bold text-gray-900">{{ userProfile.avgOrderValue.toLocaleString() }}</p>
                        <p class="text-sm text-gray-500">Avg. Order Value</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Account Actions -->
              <div class="border-t border-gray-200 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Actions</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                      <p class="font-medium text-gray-900">Download Account Data</p>
                      <p class="text-sm text-gray-500">Request a copy of all your data</p>
                    </div>
                    <button
                      @click="downloadAccountData"
                      class="px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium"
                    >
                      Request Data
                    </button>
                  </div>
                  
                  <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                      <p class="font-medium text-gray-900">Delete Account</p>
                      <p class="text-sm text-gray-500">Permanently delete your account and all data</p>
                    </div>
                    <button
                      @click="deleteAccount"
                      class="px-4 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-50 transition-colors font-medium"
                    >
                      Delete Account
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Address Modal -->
    <div v-if="showAddressModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-bold text-gray-900">{{ editingAddress ? 'Edit Address' : 'Add New Address' }}</h3>
            <button @click="closeAddressModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveAddress" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Address Label</label>
              <input
                v-model="addressForm.name"
                type="text"
                placeholder="Home, Office, etc."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
              <input
                v-model="addressForm.fullName"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
              <textarea
                v-model="addressForm.street"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              ></textarea>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <select
                  v-model="addressForm.city"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
                  <option value="">Select City</option>
                  <option value="bacoor">Bacoor</option>
                  <option value="imus">Imus</option>
                  <option value="dasmarinas">Dasmarinas</option>
                  <option value="tanza">Tanza</option>
                  <option value="trece-martires">Trece Martires</option>
                  <option value="general-trias">General Trias</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                <input
                  v-model="addressForm.barangay"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                <input
                  v-model="addressForm.province"
                  type="text"
                  value="Cavite"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                <input
                  v-model="addressForm.zipCode"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  required
                >
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
              <input
                v-model="addressForm.phone"
                type="tel"
                placeholder="+63 912 345 6789"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
            </div>
            
            <div class="flex items-center">
              <input
                v-model="addressForm.isDefault"
                type="checkbox"
                id="defaultAddress"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="defaultAddress" class="ml-2 text-sm text-gray-700">
                Set as default shipping address
              </label>
            </div>
            
            <div class="pt-4 border-t border-gray-200">
              <div class="flex justify-end space-x-3">
                <button
                  type="button"
                  @click="closeAddressModal"
                  class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-6 py-2 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 font-semibold"
                >
                  {{ editingAddress ? 'Update' : 'Save' }} Address
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'

// State
const activeTab = ref('personal')
const showAddressModal = ref(false)
const editingAddress = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// User Profile Data
const userProfile = reactive({
  name: 'Julian Namoc',
  email: 'IspyMILK@gmail.com',
  emailVerified: true,
  memberSince: 'June 2026',
  accountType: 'Premium Member',
  totalOrders: 12,
  totalSpent: 45600,
  avgOrderValue: 3800,
  
  // Personal Info
  firstName: 'Julian',
  lastName: 'Namoc',
  phone: '+63 912 345 6789',
  birthDate: '1990-05-15',
  gender: 'male',
  
  // Addresses
  addresses: [
    {
      id: 1,
      name: 'Home',
      fullName: 'Julian Namoc',
      street: '123 Main Street, Phase 1',
      city: 'Dasmarinas',
      barangay: 'San Miguel',
      province: 'Cavite',
      zipCode: '4114',
      phone: '+63 912 345 6789',
      isDefault: true
    },
    {
      id: 2,
      name: 'Office',
      fullName: 'Julian Namoc',
      street: '456 Oak Avenue, Suite 302',
      city: 'Imus',
      barangay: 'Bucandala',
      province: 'Cavite',
      zipCode: '4103',
      phone: '+63 917 890 1234',
      isDefault: false
    }
  ],
  
  // Security
  twoFactorEnabled: false,
  sessions: [
    {
      id: 1,
      device: 'Desktop',
      browser: 'Chrome on Windows',
      location: 'Cavite, Philippines',
      lastActive: 'Currently active',
      current: true
    },
    {
      id: 2,
      device: 'Mobile',
      browser: 'Safari on iOS',
      location: 'Manila, Philippines',
      lastActive: '2 hours ago',
      current: false
    }
  ],
  
  // Preferences
  language: 'en',
  timezone: 'Asia/Manila'
})

// Navigation Items
const navItems = ref([
  { id: 'personal', label: 'Personal Info', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
  { id: 'addresses', label: 'Addresses', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
  { id: 'security', label: 'Security', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
  { id: 'preferences', label: 'Preferences', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
  { id: 'account', label: 'Account Status', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }
])

// Notification Preferences
const notificationPreferences = ref([
  { id: 'order', label: 'Order Updates', description: 'Get notified about order status changes', enabled: true },
  { id: 'promo', label: 'Promotions & Offers', description: 'Receive special offers and discounts', enabled: true },
  { id: 'service', label: 'Service Reminders', description: 'Reminders for scheduled services', enabled: true },
  { id: 'news', label: 'Newsletter', description: 'Receive our monthly newsletter', enabled: false }
])

// Email Preferences
const emailPreferences = ref([
  { id: 'receipts', label: 'Order Receipts', description: 'Email receipts for your purchases', enabled: true },
  { id: 'feedback', label: 'Feedback Requests', description: 'Requests for product/service feedback', enabled: true },
  { id: 'account', label: 'Account Activity', description: 'Important account notifications', enabled: true }
])

// Password Data
const passwordData = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Address Form
const addressForm = reactive({
  id: null,
  name: '',
  fullName: '',
  street: '',
  city: '',
  barangay: '',
  province: 'Cavite',
  zipCode: '',
  phone: '',
  isDefault: false
})

// Computed
const fullName = computed(() => `${userProfile.firstName} ${userProfile.lastName}`)

// Methods
const updatePersonalInfo = () => {
  userProfile.name = fullName.value
  console.log('Personal info updated:', userProfile)
  alert('Personal information updated successfully!')
}

const resetForm = () => {
  // Reset to original values
  userProfile.firstName = 'Juan'
  userProfile.lastName = 'Dela Cruz'
  userProfile.email = 'juan.delacruz@email.com'
  userProfile.phone = '+63 912 345 6789'
  userProfile.birthDate = '1990-05-15'
  userProfile.gender = 'male'
}

const verifyEmail = () => {
  userProfile.emailVerified = true
  alert('Verification email sent! Please check your inbox.')
}

const openAddressModal = (address = null) => {
  if (address) {
    editingAddress.value = true
    Object.assign(addressForm, address)
  } else {
    editingAddress.value = false
    resetAddressForm()
  }
  showAddressModal.value = true
}

const closeAddressModal = () => {
  showAddressModal.value = false
  editingAddress.value = false
  resetAddressForm()
}

const resetAddressForm = () => {
  Object.assign(addressForm, {
    id: null,
    name: '',
    fullName: '',
    street: '',
    city: '',
    barangay: '',
    province: 'Cavite',
    zipCode: '',
    phone: '',
    isDefault: false
  })
}

const saveAddress = () => {
  if (editingAddress.value) {
    // Update existing address
    const index = userProfile.addresses.findIndex(a => a.id === addressForm.id)
    if (index !== -1) {
      userProfile.addresses[index] = { ...addressForm }
    }
  } else {
    // Add new address
    const newId = Math.max(...userProfile.addresses.map(a => a.id), 0) + 1
    userProfile.addresses.push({
      id: newId,
      ...addressForm
    })
  }
  
  // If set as default, update other addresses
  if (addressForm.isDefault) {
    userProfile.addresses.forEach(addr => {
      if (addr.id !== addressForm.id) {
        addr.isDefault = false
      }
    })
  }
  
  closeAddressModal()
  alert('Address saved successfully!')
}

const editAddress = (addressId) => {
  const address = userProfile.addresses.find(a => a.id === addressId)
  if (address) {
    openAddressModal(address)
  }
}

const deleteAddress = (addressId) => {
  if (confirm('Are you sure you want to delete this address?')) {
    userProfile.addresses = userProfile.addresses.filter(a => a.id !== addressId)
    alert('Address deleted successfully!')
  }
}

const setDefaultAddress = (addressId) => {
  userProfile.addresses.forEach(addr => {
    addr.isDefault = addr.id === addressId
  })
  alert('Default address updated!')
}

const useAddress = (address) => {
  console.log('Using address:', address)
  // In real app, this would set as shipping address for checkout
  alert(`Address "${address.name}" selected for shipping`)
}

const changePassword = () => {
  if (passwordData.newPassword !== passwordData.confirmPassword) {
    alert('New passwords do not match!')
    return
  }
  
  // Simple password validation
  if (passwordData.newPassword.length < 8) {
    alert('Password must be at least 8 characters long')
    return
  }
  
  console.log('Password changed successfully')
  alert('Password updated successfully!')
  
  // Reset form
  passwordData.currentPassword = ''
  passwordData.newPassword = ''
  passwordData.confirmPassword = ''
}

const toggleTwoFactor = () => {
  userProfile.twoFactorEnabled = !userProfile.twoFactorEnabled
  alert(`Two-factor authentication ${userProfile.twoFactorEnabled ? 'enabled' : 'disabled'}!`)
}

const endSession = (sessionId) => {
  if (sessionId === 1) {
    alert('Cannot end current session')
    return
  }
  
  userProfile.sessions = userProfile.sessions.filter(s => s.id !== sessionId)
  alert('Session ended successfully!')
}

const endAllSessions = () => {
  userProfile.sessions = userProfile.sessions.filter(s => s.current)
  alert('All other sessions ended!')
}

const toggleNotification = (id) => {
  const notification = notificationPreferences.value.find(n => n.id === id)
  if (notification) {
    notification.enabled = !notification.enabled
  }
}

const toggleEmailPreference = (id) => {
  const email = emailPreferences.value.find(e => e.id === id)
  if (email) {
    email.enabled = !email.enabled
  }
}

const savePreferences = () => {
  console.log('Preferences saved:', {
    language: userProfile.language,
    timezone: userProfile.timezone,
    notifications: notificationPreferences.value,
    emails: emailPreferences.value
  })
  alert('Preferences saved successfully!')
}

const downloadAccountData = () => {
  alert('Account data download request submitted. You will receive an email with download link within 24 hours.')
}

const deleteAccount = () => {
  if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
    alert('Account deletion request submitted. Our team will contact you for confirmation.')
  }
}
</script>

<style scoped>
/* Custom scrollbar for modals */
.max-h-\[90vh\]::-webkit-scrollbar {
  width: 6px;
}

.max-h-\[90vh\]::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.max-h-\[90vh\]::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

/* Smooth transitions */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom toggle switch */
.relative.inline-flex.h-6.w-11.items-center.rounded-full {
  cursor: pointer;
}

/* Custom select dropdown arrow */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}

/* Password visibility toggle positioning */
.relative > button {
  outline: none;
}
</style>