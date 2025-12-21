<template>
  <div class="profile-page min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Header Section -->
    <header class="sticky top-0 z-40 bg-gray-900/95 backdrop-blur-lg border-b border-gray-800 shadow-xl">
      <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-gradient-to-br from-gray-600 to-gray-500 rounded-xl shadow-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              Account Management
            </h1>
            <p class="text-gray-400 mt-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Manage your profile, security, and preferences
            </p>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex gap-3">
            <button 
              @click="saveChanges"
              :disabled="!hasChanges"
              class="px-4 sm:px-6 py-3 rounded-xl font-medium flex items-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
              :class="hasChanges 
                ? 'bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white hover:shadow-lg hover:shadow-blue-500/25 hover:-translate-y-0.5' 
                : 'bg-gray-800 text-gray-400 border border-gray-700'"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span class="hidden sm:inline">Save Changes</span>
              <span class="sm:hidden">Save</span>
            </button>
            <button 
              @click="resetChanges"
              :disabled="!hasChanges"
              class="px-4 sm:px-6 py-3 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-xl text-gray-300 font-medium flex items-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span class="hidden sm:inline">Reset</span>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Profile Overview -->
        <div class="lg:col-span-1">
          <!-- Profile Card -->
          <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <div class="text-center">
              <!-- Profile Avatar -->
              <div class="relative inline-block">
                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center mx-auto mb-4 shadow-2xl">
                  <span class="text-4xl font-bold text-white">{{ userInitials }}</span>
                </div>
                <button 
                  @click="editAvatar"
                  class="absolute bottom-4 right-0 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-full p-2.5 shadow-lg transition-all hover:scale-110"
                >
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
              </div>
              
              <h2 class="text-2xl font-bold text-white mb-1">{{ userProfile.fullName }}</h2>
              <p class="text-gray-400 mb-4">{{ userProfile.role }}</p>
              
              <!-- Account Status -->
              <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-900/30 text-green-400 rounded-full border border-green-800 mb-6">
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                <span class="text-sm font-medium">Active</span>
              </div>
              
              <!-- Account Stats -->
              <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="text-center p-3 bg-gray-800/50 rounded-xl">
                  <div class="text-2xl font-bold text-white">{{ userStats.completedJobs }}</div>
                  <div class="text-gray-400 text-sm">Jobs Done</div>
                </div>
                <div class="text-center p-3 bg-gray-800/50 rounded-xl">
                  <div class="text-2xl font-bold text-white">{{ userStats.satisfaction }}%</div>
                  <div class="text-gray-400 text-sm">Rating</div>
                </div>
              </div>
              
              <!-- Member Since -->
              <div class="text-center pt-4 border-t border-gray-800">
                <div class="text-gray-400 text-sm mb-1">Member Since</div>
                <div class="text-white font-medium">{{ userProfile.memberSince }}</div>
              </div>
            </div>
          </div>
          
          <!-- Security Status -->
          <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 shadow-xl p-6">
            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Security Status
            </h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-green-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-white font-medium">Password</div>
                    <div class="text-gray-400 text-sm">Last changed: {{ securityStatus.passwordLastChanged }}</div>
                  </div>
                </div>
                <button 
                  @click="showChangePassword = !showChangePassword"
                  class="text-blue-400 hover:text-blue-300 text-sm font-medium"
                >
                  Change
                </button>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-white font-medium">Two-Factor Auth</div>
                    <div class="text-gray-400 text-sm">{{ securityStatus.twoFactorEnabled ? 'Enabled' : 'Not enabled' }}</div>
                  </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                  <input 
                    type="checkbox" 
                    v-model="securityStatus.twoFactorEnabled"
                    class="sr-only peer" 
                  />
                  <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-yellow-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-white font-medium">Active Sessions</div>
                    <div class="text-gray-400 text-sm">{{ securityStatus.activeSessions }} devices</div>
                  </div>
                </div>
                <button 
                  @click="manageSessions"
                  class="text-blue-400 hover:text-blue-300 text-sm font-medium"
                >
                  Manage
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Settings Forms -->
        <div class="lg:col-span-2">
          <!-- Personal Details Form -->
          <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-xl font-bold text-white">Personal Details</h2>
                  <p class="text-gray-400 text-sm">Update your personal information</p>
                </div>
              </div>
              <div class="text-sm text-gray-500">
                Last updated: {{ formatDate(userProfile.lastUpdated) }}
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- First Name -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  First Name
                </label>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="userProfile.firstName"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    placeholder="Enter first name"
                  />
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </div>
              
              <!-- Last Name -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Last Name
                </label>
                <div class="relative">
                  <input 
                    type="text" 
                    v-model="userProfile.lastName"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    placeholder="Enter last name"
                  />
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
              </div>
              
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Email Address
                </label>
                <div class="relative">
                  <input 
                    type="email" 
                    v-model="userProfile.email"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    placeholder="Enter email address"
                  />
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
              
              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Phone Number
                </label>
                <div class="relative">
                  <input 
                    type="tel" 
                    v-model="userProfile.phone"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    placeholder="Enter phone number"
                  />
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                </div>
              </div>
              
              <!-- Address -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Address
                </label>
                <div class="relative">
                  <textarea 
                    v-model="userProfile.address"
                    rows="3"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all resize-none"
                    placeholder="Enter your address"
                  ></textarea>
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
              </div>
              
              <!-- Bio -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Bio / Professional Summary
                </label>
                <div class="relative">
                  <textarea 
                    v-model="userProfile.bio"
                    rows="4"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all resize-none"
                    placeholder="Tell us about yourself and your painting expertise..."
                  ></textarea>
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Change Password Form -->
          <div 
            v-if="showChangePassword"
            class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 shadow-xl p-6 mb-6 transition-all duration-300"
          >
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-r from-green-500 to-emerald-400 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-xl font-bold text-white">Change Password</h2>
                  <p class="text-gray-400 text-sm">Update your account password</p>
                </div>
              </div>
              <button 
                @click="showChangePassword = false"
                class="text-gray-400 hover:text-gray-300"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Current Password -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Current Password
                </label>
                <div class="relative">
                  <input 
                    :type="showCurrentPassword ? 'text' : 'password'"
                    v-model="password.current"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-12 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    placeholder="Enter current password"
                  />
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  <button 
                    @click="showCurrentPassword = !showCurrentPassword"
                    class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-300"
                  >
                    <svg v-if="showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <!-- New Password -->
              <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  New Password
                </label>
                <div class="relative">
                  <input 
                    :type="showNewPassword ? 'text' : 'password'"
                    v-model="password.new"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-12 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    placeholder="Enter new password"
                  />
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  <button 
                    @click="showNewPassword = !showNewPassword"
                    class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-300"
                  >
                    <svg v-if="showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                  Must be at least 8 characters with uppercase, lowercase, and number
                </div>
              </div>
              
              <!-- Confirm Password -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Confirm New Password
                </label>
                <div class="relative">
                  <input 
                    :type="showConfirmPassword ? 'text' : 'password'"
                    v-model="password.confirm"
                    class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-12 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                    placeholder="Confirm new password"
                  />
                  <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  <button 
                    @click="showConfirmPassword = !showConfirmPassword"
                    class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-300"
                  >
                    <svg v-if="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    </svg>
                  </button>
                </div>
                <div v-if="password.confirm && password.new !== password.confirm" class="mt-2 text-xs text-red-400">
                  Passwords do not match
                </div>
              </div>
              
              <!-- Update Password Button -->
              <div class="md:col-span-2">
                <button 
                  @click="updatePassword"
                  :disabled="!password.current || !password.new || password.new !== password.confirm"
                  class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 rounded-xl text-white font-medium flex items-center justify-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-green-500/25"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                  </svg>
                  Update Password
                </button>
              </div>
            </div>
          </div>

          <!-- Notification Settings -->
          <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 shadow-xl p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-r from-purple-500 to-pink-400 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-xl font-bold text-white">Notification Settings</h2>
                  <p class="text-gray-400 text-sm">Manage how you receive notifications</p>
                </div>
              </div>
              <div class="flex gap-2">
                <button 
                  @click="enableAllNotifications"
                  class="px-3 py-1.5 text-sm bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-gray-300 transition-colors"
                >
                  Enable All
                </button>
                <button 
                  @click="disableAllNotifications"
                  class="px-3 py-1.5 text-sm bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-gray-300 transition-colors"
                >
                  Disable All
                </button>
              </div>
            </div>
            
            <div class="space-y-6">
              <!-- Email Notifications -->
              <div>
                <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  Email Notifications
                </h3>
                <div class="space-y-4">
                  <div v-for="setting in emailNotifications" :key="setting.id" class="flex items-center justify-between p-4 bg-gray-800/50 rounded-xl">
                    <div>
                      <div class="text-white font-medium">{{ setting.label }}</div>
                      <div class="text-gray-400 text-sm mt-1">{{ setting.description }}</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input 
                        type="checkbox" 
                        v-model="setting.enabled"
                        class="sr-only peer" 
                      />
                      <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- Push Notifications -->
              <div>
                <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  Push Notifications
                </h3>
                <div class="space-y-4">
                  <div v-for="setting in pushNotifications" :key="setting.id" class="flex items-center justify-between p-4 bg-gray-800/50 rounded-xl">
                    <div>
                      <div class="text-white font-medium">{{ setting.label }}</div>
                      <div class="text-gray-400 text-sm mt-1">{{ setting.description }}</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input 
                        type="checkbox" 
                        v-model="setting.enabled"
                        class="sr-only peer" 
                      />
                      <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"></div>
                    </label>
                  </div>
                </div>
              </div>
              
              <!-- SMS Notifications -->
              <div>
                <h3 class="text-lg font-medium text-white mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  SMS Notifications
                </h3>
                <div class="space-y-4">
                  <div v-for="setting in smsNotifications" :key="setting.id" class="flex items-center justify-between p-4 bg-gray-800/50 rounded-xl">
                    <div>
                      <div class="text-white font-medium">{{ setting.label }}</div>
                      <div class="text-gray-400 text-sm mt-1">{{ setting.description }}</div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input 
                        type="checkbox" 
                        v-model="setting.enabled"
                        class="sr-only peer" 
                      />
                      <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'ProfileSettingsPage',
  data() {
    return {
      showChangePassword: false,
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      originalProfile: null,
      userProfile: {
        firstName: 'John',
        lastName: 'Doe',
        email: 'john.doe@paintpro.com',
        phone: '+1 (555) 123-4567',
        address: '123 Paint Street, Color District, Manila, Philippines',
        bio: 'Professional painter with 8+ years of experience specializing in residential and commercial painting. Expert in color matching, surface preparation, and custom finishes. Committed to delivering exceptional quality and customer satisfaction.',
        role: 'Senior Painter',
        memberSince: 'March 2022',
        lastUpdated: '2024-01-15'
      },
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      securityStatus: {
        passwordLastChanged: '2 weeks ago',
        twoFactorEnabled: true,
        activeSessions: 3
      },
      userStats: {
        completedJobs: 42,
        satisfaction: 94
      },
      emailNotifications: [
        { id: 1, label: 'New Job Assignments', description: 'Get notified when new jobs are assigned to you', enabled: true },
        { id: 2, label: 'Schedule Updates', description: 'Receive updates about schedule changes', enabled: true },
        { id: 3, label: 'Client Messages', description: 'Email notifications for new client messages', enabled: false },
        { id: 4, label: 'Weekly Reports', description: 'Receive weekly performance summaries', enabled: true },
        { id: 5, label: 'System Announcements', description: 'Important updates from Paint Pro', enabled: true }
      ],
      pushNotifications: [
        { id: 1, label: 'Job Reminders', description: 'Reminders for upcoming jobs', enabled: true },
        { id: 2, label: 'Urgent Requests', description: 'Notifications for urgent service requests', enabled: true },
        { id: 3, label: 'Client Feedback', description: 'Push notifications for new client reviews', enabled: false },
        { id: 4, label: 'Team Messages', description: 'Notifications from team members', enabled: true }
      ],
      smsNotifications: [
        { id: 1, label: 'Emergency Alerts', description: 'Critical system alerts via SMS', enabled: true },
        { id: 2, label: 'Daily Schedule', description: 'Morning schedule reminder', enabled: false },
        { id: 3, label: 'Payment Updates', description: 'SMS notifications for payments', enabled: true }
      ]
    }
  },
  computed: {
    userInitials() {
      return (this.userProfile.firstName.charAt(0) + this.userProfile.lastName.charAt(0)).toUpperCase()
    },
    hasChanges() {
      if (!this.originalProfile) return false
      
      return JSON.stringify(this.userProfile) !== JSON.stringify(this.originalProfile) ||
             this.emailNotifications.some(n => n.enabled !== (this.originalEmailNotifications?.find(o => o.id === n.id)?.enabled)) ||
             this.pushNotifications.some(n => n.enabled !== (this.originalPushNotifications?.find(o => o.id === n.id)?.enabled)) ||
             this.smsNotifications.some(n => n.enabled !== (this.originalSmsNotifications?.find(o => o.id === n.id)?.enabled))
    }
  },
  created() {
    // Store original data for comparison
    this.originalProfile = JSON.parse(JSON.stringify(this.userProfile))
    this.originalEmailNotifications = JSON.parse(JSON.stringify(this.emailNotifications))
    this.originalPushNotifications = JSON.parse(JSON.stringify(this.pushNotifications))
    this.originalSmsNotifications = JSON.parse(JSON.stringify(this.smsNotifications))
  },
  methods: {
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      })
    },
    
    saveChanges() {
      // Simulate API call
      const button = event?.target
      if (button) {
        const originalText = button.innerHTML
        button.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Saving...'
        
        setTimeout(() => {
          button.innerHTML = originalText
          
          // Update original references
          this.originalProfile = JSON.parse(JSON.stringify(this.userProfile))
          this.originalEmailNotifications = JSON.parse(JSON.stringify(this.emailNotifications))
          this.originalPushNotifications = JSON.parse(JSON.stringify(this.pushNotifications))
          this.originalSmsNotifications = JSON.parse(JSON.stringify(this.smsNotifications))
          
          // Update last updated timestamp
          this.userProfile.lastUpdated = new Date().toISOString().split('T')[0]
          
          // Show success message
          this.$notify({
            title: 'Changes Saved',
            message: 'Your profile has been updated successfully',
            type: 'success'
          })
        }, 1500)
      }
    },
    
    resetChanges() {
      this.userProfile = JSON.parse(JSON.stringify(this.originalProfile))
      this.emailNotifications = JSON.parse(JSON.stringify(this.originalEmailNotifications))
      this.pushNotifications = JSON.parse(JSON.stringify(this.originalPushNotifications))
      this.smsNotifications = JSON.parse(JSON.stringify(this.originalSmsNotifications))
      
      this.$notify({
        title: 'Changes Reset',
        message: 'All changes have been reverted',
        type: 'info'
      })
    },
    
    updatePassword() {
      if (!this.password.current || !this.password.new || this.password.new !== this.password.confirm) {
        this.$notify({
          title: 'Validation Error',
          message: 'Please fill all fields correctly',
          type: 'error'
        })
        return
      }
      
      // Simulate password update
      setTimeout(() => {
        this.securityStatus.passwordLastChanged = 'Just now'
        this.password = { current: '', new: '', confirm: '' }
        this.showChangePassword = false
        
        this.$notify({
          title: 'Password Updated',
          message: 'Your password has been changed successfully',
          type: 'success'
        })
      }, 1000)
    },
    
    editAvatar() {
      this.$notify({
        title: 'Edit Avatar',
        message: 'Avatar upload feature will be available soon',
        type: 'info'
      })
    },
    
    manageSessions() {
      this.$notify({
        title: 'Manage Sessions',
        message: 'Session management feature will be available soon',
        type: 'info'
      })
    },
    
    enableAllNotifications() {
      this.emailNotifications.forEach(n => n.enabled = true)
      this.pushNotifications.forEach(n => n.enabled = true)
      this.smsNotifications.forEach(n => n.enabled = true)
      
      this.$notify({
        title: 'Notifications Enabled',
        message: 'All notification types have been enabled',
        type: 'success'
      })
    },
    
    disableAllNotifications() {
      this.emailNotifications.forEach(n => n.enabled = false)
      this.pushNotifications.forEach(n => n.enabled = false)
      this.smsNotifications.forEach(n => n.enabled = false)
      
      this.$notify({
        title: 'Notifications Disabled',
        message: 'All notification types have been disabled',
        type: 'warning'
      })
    }
  },
  mounted() {
    // Animate form sections on load
    setTimeout(() => {
      const sections = document.querySelectorAll('.bg-gradient-to-br')
      sections.forEach((section, index) => {
        section.style.opacity = '0'
        section.style.transform = 'translateY(20px)'
        setTimeout(() => {
          section.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
          section.style.opacity = '1'
          section.style.transform = 'translateY(0)'
        }, index * 200)
      })
    }, 300)
  }
}
</script>

<style scoped>
/* Custom animations */
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

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Glass morphism effect */
.bg-gradient-to-br {
  backdrop-filter: blur(10px);
}

/* Form input focus effects */
input:focus, textarea:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}

/* Toggle switch customization */
input:checked + div {
  background-color: #2563eb;
}

input:checked + div:after {
  transform: translateX(100%);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .w-32 {
    width: 6rem;
    height: 6rem;
  }
  
  .text-4xl {
    font-size: 2rem;
  }
}

@media (max-width: 768px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .md\:col-span-2 {
    grid-column: span 1;
  }
}

/* Avatar animation */
.relative.inline-block:hover .absolute {
  transform: scale(1.1);
  transition: transform 0.2s ease;
}

/* Notification cards hover effect */
.bg-gray-800\/50:hover {
  background-color: rgba(55, 65, 81, 0.7) !important;
}

/* Password strength indicator (future enhancement) */
.password-strength {
  height: 4px;
  border-radius: 2px;
  transition: all 0.3s ease;
}

/* Print styles */
@media print {
  .profile-page {
    background: white !important;
    color: black !important;
  }
  
  header, button, .bg-gradient-to-br {
    background: white !important;
    color: black !important;
    border: 1px solid #ddd !important;
  }
  
  input, textarea {
    background: white !important;
    color: black !important;
    border: 1px solid #ddd !important;
  }
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>