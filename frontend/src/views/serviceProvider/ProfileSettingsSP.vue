<template>
  <!-- Root container with both notifications and main content -->
  <div>
    <!-- Notification Container - Now at ROOT level -->
    <div class="notifications-container">
      <transition-group name="notification-slide">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          class="notification"
          :class="notification.type"
          @click="removeNotification(notification.id)"
        >
          <div class="notification-icon">
            <svg v-if="notification.type === 'success'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="notification.type === 'error'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="notification.type === 'warning'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <svg v-else class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="notification-content">
            <div class="notification-title">
              {{ notification.type === 'success' ? 'Success' : 
                 notification.type === 'error' ? 'Error' : 
                 notification.type === 'warning' ? 'Warning' : 'Info' }}
            </div>
            <div class="notification-message">{{ notification.message }}</div>
          </div>
          <button class="notification-close">
            <svg class="close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <div class="notification-progress" :style="{ animationDuration: notification.duration + 'ms' }"></div>
        </div>
      </transition-group>
    </div>

    <!-- Main Page Content -->
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
                @click="saveProfile"
                :disabled="!hasChanges || isLoading"
                class="px-4 sm:px-6 py-3 rounded-xl font-medium flex items-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="hasChanges && !isLoading
                  ? 'bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white hover:shadow-lg hover:shadow-blue-500/25 hover:-translate-y-0.5' 
                  : 'bg-gray-800 text-gray-400 border border-gray-700'"
              >
                <svg v-if="!isLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <svg v-else class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="hidden sm:inline">{{ isLoading ? 'Saving...' : 'Save Changes' }}</span>
                <span class="sm:hidden">{{ isLoading ? 'Saving...' : 'Save' }}</span>
              </button>
              <button 
                @click="resetChanges"
                :disabled="!hasChanges || isLoading"
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

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center min-h-[400px]">
        <div class="text-center">
          <svg class="animate-spin w-12 h-12 text-blue-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <p class="text-gray-400">Loading profile data...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-900/20 border border-red-800 rounded-xl p-6 mx-4 sm:mx-6 lg:mx-8 my-6">
        <div class="flex items-center gap-3">
          <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <h3 class="text-lg font-semibold text-red-400">Failed to load profile</h3>
            <p class="text-gray-300 mt-1">{{ error }}</p>
          </div>
        </div>
        <button @click="fetchUserProfile" class="mt-4 px-4 py-2 bg-red-800 hover:bg-red-700 text-white rounded-lg transition-colors">
          Retry
        </button>
      </div>

      <!-- Main Content -->
      <main v-else class="px-4 sm:px-6 lg:px-8 py-6">
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
                
                <h2 class="text-2xl font-bold text-white mb-1">{{ user.full_name || `${user.first_name} ${user.last_name}` }}</h2>
                <p class="text-gray-400 mb-4">{{ formatRole(user.role) }}</p>
                
                <!-- Account Status -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border mb-6" 
                     :class="statusClasses">
                  <div class="w-2 h-2 rounded-full" :class="statusDotClasses"></div>
                  <span class="text-sm font-medium capitalize">{{ user.status || 'active' }}</span>
                </div>
                
                <!-- Account Stats -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                  <div class="text-center p-3 bg-gray-800/50 rounded-xl">
                    <div class="text-2xl font-bold text-white">{{ stats.completedJobs || 0 }}</div>
                    <div class="text-gray-400 text-sm">Jobs Done</div>
                  </div>
                  <div class="text-center p-3 bg-gray-800/50 rounded-xl">
                    <div class="text-2xl font-bold text-white">{{ stats.satisfaction || 0 }}%</div>
                    <div class="text-gray-400 text-sm">Rating</div>
                  </div>
                </div>
                
                <!-- Member Since -->
                <div class="text-center pt-4 border-t border-gray-800">
                  <div class="text-gray-400 text-sm mb-1">Member Since</div>
                  <div class="text-white font-medium">{{ formatDate(user.created_at) || 'Unknown' }}</div>
                </div>
              </div>
            </div>
            
            <!-- Security Status -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 shadow-xl p-6">
              <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-gradient-to-r from-yellow-500 to-amber-400 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-bold text-white">Security</h2>
                  <p class="text-gray-400 text-sm">Account security status</p>
                </div>
              </div>
              
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="text-gray-300">Password Last Changed</span>
                  <span class="text-sm text-gray-400">{{ securityStatus.passwordLastChanged || 'Unknown' }}</span>
                </div>
                
                <div class="flex items-center justify-between">
                  <span class="text-gray-300">Email Verified</span>
                  <span v-if="user.email_verified_at" class="inline-flex items-center gap-1 text-green-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Verified
                  </span>
                  <span v-else class="inline-flex items-center gap-1 text-yellow-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pending
                  </span>
                </div>
                
                <div class="flex items-center justify-between">
                  <span class="text-gray-300">ID Verification</span>
                  <span class="capitalize" :class="idVerificationStatusClass">{{ idVerificationStatus }}</span>
                </div>
                
                <button 
                  @click="showChangePassword = true"
                  class="w-full mt-4 px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-gray-300 font-medium transition-colors flex items-center justify-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                  </svg>
                  Change Password
                </button>
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
                  Last updated: {{ formatDate(user.updated_at) || 'Unknown' }}
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
                      v-model="user.first_name"
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                      placeholder="Enter first name"
                      @input="markChanged"
                    />
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div v-if="validationErrors.first_name" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.first_name[0] }}
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
                      v-model="user.last_name"
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                      placeholder="Enter last name"
                      @input="markChanged"
                    />
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  <div v-if="validationErrors.last_name" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.last_name[0] }}
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
                      v-model="user.email"
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                      placeholder="Enter email address"
                      @input="markChanged"
                    />
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div v-if="!user.email_verified_at" class="mt-2 text-sm text-yellow-400 flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Email not verified
                  </div>
                  <div v-if="validationErrors.email" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.email[0] }}
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
                      v-model="user.phone"
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all"
                      placeholder="Enter phone number"
                      @input="markChanged"
                    />
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                  </div>
                  <div v-if="validationErrors.phone" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.phone[0] }}
                  </div>
                </div>
                
                <!-- Address -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Address
                  </label>
                  <div class="relative">
                    <textarea 
                      v-model="user.address"
                      rows="3"
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all resize-none"
                      placeholder="Enter your address"
                      @input="markChanged"
                    ></textarea>
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  <div v-if="validationErrors.address" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.address[0] }}
                  </div>
                </div>
                
                <!-- Bio -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    Bio / Professional Summary
                  </label>
                  <div class="relative">
                    <textarea 
                      v-model="user.bio"
                      rows="4"
                      class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all resize-none"
                      placeholder="Tell us about yourself and your painting expertise..."
                      @input="markChanged"
                    ></textarea>
                    <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                  <div class="mt-2 text-xs text-gray-500">
                    Note: Bio field is stored locally and will be saved to the database when you save changes
                  </div>
                </div>
              </div>
            </div>

            <!-- ID Verification Wizard -->
            <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
              <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-gradient-to-r from-indigo-500 to-purple-400 rounded-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                  </div>
                  <div>
                    <h2 class="text-xl font-bold text-white">ID Verification</h2>
                    <p class="text-gray-400 text-sm">Complete identity verification in 3 easy steps</p>
                    <p v-if="idVerification.rejectionReason" class="text-xs text-red-400 mt-1">
                      Rejection Reason: {{ idVerification.rejectionReason }}
                    </p>
                  </div>
                </div>
                <div class="text-sm" :class="idVerificationStatusClass">
                  {{ idVerificationStatus }}
                  <div v-if="idVerification.submittedAt" class="text-xs text-gray-500">
                    Submitted: {{ formatDate(idVerification.submittedAt) }}
                  </div>
                </div>
              </div>
              
              <!-- Verification Status Indicators -->
              <div v-if="idVerification.status === 'verified'" class="bg-green-900/20 border border-green-800 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                  <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  <div>
                    <h3 class="font-semibold text-green-400">ID Verified</h3>
                    <p class="text-gray-300 text-sm">Your ID has been verified and approved.</p>
                    <p v-if="idVerification.reviewedAt" class="text-xs text-gray-400">
                      Verified on: {{ formatDate(idVerification.reviewedAt) }}
                    </p>
                  </div>
                </div>
              </div>
              
              <!-- Wizard Navigation -->
              <div v-if="idVerification.status !== 'verified'" class="mb-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                  <!-- Steps Indicator -->
                  <div class="flex items-center gap-4 w-full md:w-auto">
                    <!-- Step 1 -->
                    <div class="flex flex-col items-center">
                      <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                           :class="currentStep >= 1 ? 
                                  'bg-gradient-to-r from-indigo-500 to-purple-500 border-indigo-500' : 
                                  'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white">1</span>
                      </div>
                      <span class="mt-2 text-sm" :class="currentStep >= 1 ? 'text-indigo-400 font-medium' : 'text-gray-500'">
                        ID Details
                      </span>
                    </div>
                    
                    <!-- Connector Line -->
                    <div class="hidden md:block w-16 h-1" :class="currentStep >= 2 ? 'bg-gradient-to-r from-purple-500 to-purple-400' : 'bg-gray-700'"></div>
                    
                    <!-- Step 2 -->
                    <div class="flex flex-col items-center">
                      <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                           :class="currentStep >= 2 ? 
                                  'bg-gradient-to-r from-purple-500 to-purple-400 border-purple-500' : 
                                  'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white">2</span>
                      </div>
                      <span class="mt-2 text-sm" :class="currentStep >= 2 ? 'text-purple-400 font-medium' : 'text-gray-500'">
                        ID Photo
                      </span>
                    </div>
                    
                    <!-- Connector Line -->
                    <div class="hidden md:block w-16 h-1" :class="currentStep >= 3 ? 'bg-gradient-to-r from-purple-400 to-purple-300' : 'bg-gray-700'"></div>
                    
                    <!-- Step 3 -->
                    <div class="flex flex-col items-center">
                      <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                           :class="currentStep >= 3 ? 
                                  'bg-gradient-to-r from-purple-400 to-purple-300 border-purple-400' : 
                                  'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white">3</span>
                      </div>
                      <span class="mt-2 text-sm" :class="currentStep >= 3 ? 'text-purple-300 font-medium' : 'text-gray-500'">
                        Selfie
                      </span>
                    </div>
                  </div>
                  
                  <!-- Progress -->
                  <div class="text-sm text-gray-400">
                    Step {{ currentStep }} of 3
                  </div>
                </div>
              </div>
              
              <!-- Wizard Content -->
              <div v-if="idVerification.status !== 'verified'" class="space-y-6">
                <!-- Step 1: ID Details -->
                <div v-show="currentStep === 1" class="space-y-6 animate-fade-in">
                  <div class="bg-gradient-to-br from-indigo-900/20 to-purple-900/20 border border-indigo-800/30 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                      <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                      Step 1: ID Information
                    </h3>
                    <p class="text-gray-400 text-sm mb-6">Enter your government-issued ID details</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- ID Type Selection -->
                      <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                          Select ID Type <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                          <select 
                            v-model="idVerification.idType"
                            :disabled="idVerification.status === 'pending'"
                            class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:outline-none transition-all appearance-none disabled:opacity-50 disabled:cursor-not-allowed"
                          >
                            <option value="" disabled>Select ID Type</option>
                            <option value="phil_id">Philippine National ID (PhilID/ePhilID)</option>
                            <option value="passport">Passport</option>
                            <option value="driver_license">Driver's License</option>
                            <option value="umid">UMID (SSS/GSIS)</option>
                            <option value="prc_id">PRC ID</option>
                            <option value="voter_id">Voter's ID</option>
                            <option value="postal_id">Postal ID</option>
                            <option value="philhealth">PhilHealth ID</option>
                            <option value="nbi">NBI Clearance</option>
                            <option value="senior_citizen">Senior Citizen ID</option>
                            <option value="other">Other Valid ID</option>
                          </select>
                          <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                          </svg>
                          <svg class="absolute right-4 top-3.5 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                        </div>
                        <div v-if="idVerificationErrors.idType" class="mt-2 text-xs text-red-400">
                          {{ idVerificationErrors.idType }}
                        </div>
                      </div>
                      
                      <!-- ID Number -->
                      <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                          ID Number <span class="text-red-400">*</span>
                        </label>
                        <div class="relative">
                          <input 
                            type="text" 
                            v-model="idVerification.idNumber"
                            :disabled="idVerification.status === 'pending'"
                            class="w-full bg-gray-800 border border-gray-700 rounded-xl pl-12 pr-4 py-3 text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                            placeholder="Enter ID number"
                          />
                          <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                          </svg>
                        </div>
                        <div v-if="idVerificationErrors.idNumber" class="mt-2 text-xs text-red-400">
                          {{ idVerificationErrors.idNumber }}
                        </div>
                      </div>
                    </div>
                    
                    <!-- Tips for Step 1 -->
                    <div class="mt-6 p-4 bg-indigo-900/20 rounded-lg border border-indigo-800/30">
                      <h4 class="text-sm font-medium text-indigo-300 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Important Tips
                      </h4>
                      <ul class="text-xs text-gray-400 space-y-1">
                        <li>• Use a valid, government-issued ID</li>
                        <li>• Ensure your ID is not expired</li>
                        <li>• Double-check your ID number for accuracy</li>
                        <li>• Choose the correct ID type from the list</li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Step 2: ID Photo -->
                <div v-show="currentStep === 2" class="space-y-6 animate-fade-in">
                  <div class="bg-gradient-to-br from-purple-900/20 to-pink-900/20 border border-purple-800/30 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                      <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      Step 2: ID Photo Upload
                    </h3>
                    <p class="text-gray-400 text-sm mb-6">Upload a clear photo of your ID document</p>
                    
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-300 mb-2">
                        Upload ID Photo <span class="text-red-400">*</span>
                      </label>
                      <div 
                        @dragover.prevent="handleDragOver"
                        @dragleave="handleDragLeave"
                        @drop.prevent="handleIdDrop"
                        @click="idVerification.status !== 'pending' ? triggerIdUpload() : null"
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="[
                          idUploadClasses,
                          idVerification.status === 'pending' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:scale-[1.02]'
                        ]"
                      >
                        <input 
                          type="file" 
                          ref="idFileInput"
                          @change="handleIdUploadChange"
                          accept="image/*"
                          class="hidden"
                          :disabled="idVerification.status === 'pending'"
                        />
                        
                        <div v-if="!idVerification.idPhotoPreview && !idVerification.idPhotoUrl" class="space-y-4">
                          <svg class="w-16 h-16 text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                          </svg>
                          <div>
                            <p class="text-gray-300 font-medium mb-1">Drop your ID photo here or click to browse</p>
                            <p class="text-gray-500 text-sm">Supports JPG, PNG up to 5MB</p>
                          </div>
                          <p class="text-xs text-gray-500 mt-2">
                            Upload a clear, front-facing photo of your ID. Make sure all details are readable.
                          </p>
                        </div>
                        
                        <div v-else class="space-y-4">
                          <div class="relative inline-block">
                            <img 
                              :src="idVerification.idPhotoPreview || idVerification.idPhotoUrl" 
                              alt="ID Preview"
                              class="max-h-48 mx-auto rounded-lg shadow-lg"
                            />
                            <button 
                              v-if="idVerification.status !== 'pending'"
                              @click.stop="removeIdPhoto"
                              class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 transition-colors shadow-lg"
                            >
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>
                          <p class="text-green-400 text-sm">
                            ✓ ID photo uploaded successfully
                          </p>
                        </div>
                        
                        <div v-if="idUploadProgress > 0 && idUploadProgress < 100" class="mt-4">
                          <div class="w-full bg-gray-700 rounded-full h-2">
                            <div 
                              class="bg-gradient-to-r from-purple-500 to-pink-500 h-2 rounded-full transition-all duration-300"
                              :style="{ width: idUploadProgress + '%' }"
                            ></div>
                          </div>
                          <p class="text-gray-400 text-sm mt-2">{{ idUploadProgress }}% uploaded</p>
                        </div>
                      </div>
                      
                      <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-gray-500">
                        <div class="flex items-center gap-2 p-2 bg-purple-900/20 rounded-lg">
                          <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          Clear and readable
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-purple-900/20 rounded-lg">
                          <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          All corners visible
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-purple-900/20 rounded-lg">
                          <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          No glare or blur
                        </div>
                      </div>
                      
                      <div v-if="idVerificationErrors.idPhoto" class="mt-2 text-xs text-red-400">
                        {{ idVerificationErrors.idPhoto }}
                      </div>
                    </div>
                    
                    <!-- Preview & Requirements -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                      <div class="p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="text-sm font-medium text-white mb-2">📸 ID Photo Example</h4>
                        <div class="text-xs text-gray-400 space-y-2">
                          <p>• Place ID on a dark surface</p>
                          <p>• Good lighting, no shadows</p>
                          <p>• Capture full document</p>
                          <p>• Ensure text is legible</p>
                        </div>
                      </div>
                      <div class="p-4 bg-red-900/20 rounded-lg border border-red-800/30">
                        <h4 class="text-sm font-medium text-red-300 mb-2">❌ What to Avoid</h4>
                        <div class="text-xs text-gray-400 space-y-2">
                          <p>• Blurry or out-of-focus photos</p>
                          <p>• Glare or reflections</p>
                          <p>• Cut-off edges or corners</p>
                          <p>• Edited or filtered images</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 3: Selfie with ID -->
                <div v-show="currentStep === 3" class="space-y-6 animate-fade-in">
                  <div class="bg-gradient-to-br from-pink-900/20 to-rose-900/20 border border-pink-800/30 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                      <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                      Step 3: Selfie with ID
                    </h3>
                    <p class="text-gray-400 text-sm mb-6">Take a selfie while holding your ID next to your face</p>
                    
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-300 mb-2">
                        Upload Selfie with ID <span class="text-red-400">*</span>
                      </label>
                      <div 
                        @dragover.prevent="handleSelfieDragOver"
                        @dragleave="handleSelfieDragLeave"
                        @drop.prevent="handleSelfieDrop"
                        @click="idVerification.status !== 'pending' ? triggerSelfieUpload() : null"
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="[
                          selfieUploadClasses,
                          idVerification.status === 'pending' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:scale-[1.02]'
                        ]"
                      >
                        <input 
                          type="file" 
                          ref="selfieFileInput"
                          @change="handleSelfieUploadChange"
                          accept="image/*"
                          class="hidden"
                          :disabled="idVerification.status === 'pending'"
                        />
                        
                        <div v-if="!idVerification.selfiePhotoPreview && !idVerification.selfiePhotoUrl" class="space-y-4">
                          <svg class="w-16 h-16 text-gray-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                          </svg>
                          <div>
                            <p class="text-gray-300 font-medium mb-1">Drop your selfie here or click to browse</p>
                            <p class="text-gray-500 text-sm">Supports JPG, PNG up to 5MB</p>
                          </div>
                          <p class="text-xs text-gray-500 mt-2">
                            Take a selfie while holding your ID next to your face. Make sure both your face and ID are clearly visible.
                          </p>
                        </div>
                        
                        <div v-else class="space-y-4">
                          <div class="relative inline-block">
                            <img 
                              :src="idVerification.selfiePhotoPreview || idVerification.selfiePhotoUrl" 
                              alt="Selfie Preview"
                              class="max-h-48 mx-auto rounded-lg shadow-lg"
                            />
                            <button 
                              v-if="idVerification.status !== 'pending'"
                              @click.stop="removeSelfiePhoto"
                              class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 transition-colors shadow-lg"
                            >
                              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                              </svg>
                            </button>
                          </div>
                          <p class="text-green-400 text-sm">
                            ✓ Selfie with ID uploaded successfully
                          </p>
                        </div>
                      </div>
                      
                      <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs text-gray-500">
                        <div class="flex items-center gap-2 p-2 bg-pink-900/20 rounded-lg">
                          <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          Face clearly visible
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-pink-900/20 rounded-lg">
                          <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          ID next to face
                        </div>
                        <div class="flex items-center gap-2 p-2 bg-pink-900/20 rounded-lg">
                          <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          Good lighting
                        </div>
                      </div>
                      
                      <div v-if="idVerificationErrors.selfiePhoto" class="mt-2 text-xs text-red-400">
                        {{ idVerificationErrors.selfiePhoto }}
                      </div>
                    </div>
                    
                    <!-- Example & Tips -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                      <div class="p-4 bg-gray-800/50 rounded-lg">
                        <h4 class="text-sm font-medium text-white mb-2">🤳 How to Take a Good Selfie</h4>
                        <div class="text-xs text-gray-400 space-y-2">
                          <p>• Hold ID next to your cheek</p>
                          <p>• Look directly at camera</p>
                          <p>• Use natural daylight</p>
                          <p>• Keep hands visible</p>
                          <p>• No hats or sunglasses</p>
                        </div>
                      </div>
                      <div class="p-4 bg-green-900/20 rounded-lg border border-green-800/30">
                        <h4 class="text-sm font-medium text-green-300 mb-2">✅ Verification Checklist</h4>
                        <div class="text-xs text-gray-400 space-y-2">
                          <p>• All 3 steps completed ✓</p>
                          <p>• Information matches ID</p>
                          <p>• Photos are clear and valid</p>
                          <p>• Ready for submission</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Pending Status -->
                <div v-if="idVerification.status === 'pending'" class="md:col-span-2">
                  <div class="bg-yellow-900/20 border border-yellow-800 rounded-xl p-6">
                    <div class="flex items-center gap-4">
                      <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <div class="flex-1">
                        <h3 class="font-semibold text-yellow-400 mb-1">Verification Under Review</h3>
                        <p class="text-gray-300 text-sm">Your ID verification is being reviewed by our team. This usually takes 1-2 business days.</p>
                        <div class="mt-4 flex flex-col md:flex-row gap-4 text-sm">
                          <div class="text-gray-400">
                            <span class="block">Submitted:</span>
                            <span class="text-white">{{ formatDate(idVerification.submittedAt) }}</span>
                          </div>
                          <div class="text-gray-400">
                            <span class="block">ID Type:</span>
                            <span class="text-white">{{ getReadableIdType(idVerification.idType) }}</span>
                          </div>
                          <div class="text-gray-400">
                            <span class="block">ID Number:</span>
                            <span class="text-white font-mono">{{ idVerification.idNumber }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Wizard Navigation Buttons -->
                <div v-if="idVerification.status !== 'pending' && idVerification.status !== 'verified'" class="flex justify-between pt-6 border-t border-gray-800">
                  <button 
                    @click="prevStep"
                    v-show="currentStep > 1"
                    class="px-6 py-3 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-xl text-gray-300 font-medium flex items-center gap-2 transition-all duration-300 hover:shadow-lg"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous Step
                  </button>
                  
                  <div class="flex gap-4 ml-auto">
                    <button 
                      v-if="currentStep < 3"
                      @click="nextStep"
                      :disabled="!canProceedToNextStep"
                      class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-500 hover:from-indigo-700 hover:to-purple-600 rounded-xl text-white font-medium flex items-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-indigo-500/25"
                    >
                      Next Step
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                    
                    <button 
                      v-if="currentStep === 3"
                      @click="submitIdVerification"
                      :disabled="!canSubmitIdVerification || idVerificationLoading"
                      class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 rounded-xl text-white font-medium flex items-center justify-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-green-500/25"
                    >
                      <svg v-if="!idVerificationLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                      <svg v-else class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {{ idVerificationLoading ? 'Submitting...' : 'Submit for Verification' }}
                    </button>
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
              
              <div v-if="passwordError" class="mb-6 p-3 bg-red-900/30 border border-red-800 rounded-lg text-red-400 text-sm">
                {{ passwordError }}
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
                  <div v-if="validationErrors.current_password" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.current_password[0] }}
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
                  <div v-if="validationErrors.password" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.password[0] }}
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
                  <div v-if="passwordMismatch" class="mt-2 text-xs text-red-400">
                    Passwords do not match
                  </div>
                </div>
                
                <!-- Update Password Button -->
                <div class="md:col-span-2">
                  <button 
                    @click="changePassword"
                    :disabled="!canChangePassword || passwordLoading"
                    class="w-full px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 rounded-xl text-white font-medium flex items-center justify-center gap-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-lg hover:shadow-green-500/25"
                  >
                    <svg v-if="!passwordLoading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <svg v-else class="animate-spin w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { getCurrentUser, clearAuthData } from '@/utils/auth'
import axios from '@/utils/axios'

export default {
  name: 'ProfileSettingsPage',
  data() {
    return {
      // Notification system
      notifications: [],
      notificationId: 0,
      
      loading: true,
      error: null,
      isLoading: false,
      passwordLoading: false,
      idVerificationLoading: false,
      showChangePassword: false,
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      validationErrors: {},
      passwordError: null,
      hasChanges: false,
      user: {
        id: null,
        first_name: '',
        last_name: '',
        full_name: '',
        name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: '',
        email_verified_at: null,
        created_at: '',
        updated_at: '',
        bio: ''
      },
      originalUser: null,
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      securityStatus: {
        passwordLastChanged: 'Unknown'
      },
      stats: {
        completedJobs: 0,
        satisfaction: 0,
        activeProjects: 0
      },
      // ID Verification
      idVerification: {
        idType: '',
        idNumber: '',
        idPhoto: null,
        idPhotoPreview: null,
        idPhotoUrl: null,
        selfiePhoto: null,
        selfiePhotoPreview: null,
        selfiePhotoUrl: null,
        status: 'not_submitted',
        submittedAt: null,
        reviewedAt: null,
        rejectionReason: null
      },
      idVerificationErrors: {},
      isDraggingId: false,
      isDraggingSelfie: false,
      idUploadProgress: 0,
      // Wizard state
      currentStep: 1
    }
  },
  computed: {
    userInitials() {
      if (this.user.first_name && this.user.last_name) {
        return (this.user.first_name.charAt(0) + this.user.last_name.charAt(0)).toUpperCase()
      }
      if (this.user.name) {
        const names = this.user.name.split(' ')
        return names.length >= 2 
          ? (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase()
          : names[0].charAt(0).toUpperCase()
      }
      return '?'
    },
    passwordStrength() {
      const password = this.password.new
      if (password.length < 6) return 'weak'
      if (password.length < 8) return 'medium'
      if (password.length < 10) return 'strong'
      if (this.hasSpecialChars(password) && this.hasNumbers(password) && this.hasUpperCase(password)) {
        return 'very-strong'
      }
      return 'strong'
    },
    passwordStrengthLabel() {
      const labels = {
        'weak': 'Weak',
        'medium': 'Medium',
        'strong': 'Strong',
        'very-strong': 'Very Strong'
      }
      return labels[this.passwordStrength] || 'Weak'
    },
    passwordMismatch() {
      return this.password.new && this.password.confirm && this.password.new !== this.password.confirm
    },
    canChangePassword() {
      return this.password.current && 
             this.password.new && 
             this.password.confirm && 
             !this.passwordMismatch &&
             this.password.new.length >= 8 &&
             /[A-Z]/.test(this.password.new) &&
             /[0-9]/.test(this.password.new) &&
             /[^A-Za-z0-9]/.test(this.password.new)
    },
    statusClasses() {
      const status = this.user.status || 'active'
      switch (status) {
        case 'active':
          return 'bg-green-900/30 text-green-400 border-green-800'
        case 'pending':
          return 'bg-yellow-900/30 text-yellow-400 border-yellow-800'
        case 'inactive':
          return 'bg-red-900/30 text-red-400 border-red-800'
        default:
          return 'bg-gray-800/30 text-gray-400 border-gray-700'
      }
    },
    statusDotClasses() {
      const status = this.user.status || 'active'
      switch (status) {
        case 'active':
          return 'bg-green-400'
        case 'pending':
          return 'bg-yellow-400'
        case 'inactive':
          return 'bg-red-400'
        default:
          return 'bg-gray-400'
      }
    },
    statusTextClasses() {
      const status = this.user.status || 'active'
      switch (status) {
        case 'active':
          return 'text-green-400'
        case 'pending':
          return 'text-yellow-400'
        case 'inactive':
          return 'text-red-400'
        default:
          return 'text-gray-400'
      }
    },
    // ID Verification Computed Properties
    idVerificationStatus() {
      switch (this.idVerification.status) {
        case 'pending':
          return 'Pending Verification'
        case 'verified':
          return 'Verified'
        case 'rejected':
          return 'Rejected - Please resubmit'
        default:
          return 'Not Submitted'
      }
    },
    idVerificationStatusClass() {
      switch (this.idVerification.status) {
        case 'pending':
          return 'text-yellow-400 font-medium'
        case 'verified':
          return 'text-green-400 font-medium'
        case 'rejected':
          return 'text-red-400 font-medium'
        default:
          return 'text-gray-500'
      }
    },
    idUploadClasses() {
      const base = 'border-gray-700 hover:border-purple-500'
      if (this.isDraggingId) {
        return `${base} border-purple-500 bg-purple-900/20`
      }
      return base
    },
    selfieUploadClasses() {
      const base = 'border-gray-700 hover:border-pink-500'
      if (this.isDraggingSelfie) {
        return `${base} border-pink-500 bg-pink-900/20`
      }
      return base
    },
    canSubmitIdVerification() {
      return this.idVerification.idType && 
             this.idVerification.idNumber && 
             (this.idVerification.idPhoto || this.idVerification.idPhotoUrl) && 
             (this.idVerification.selfiePhoto || this.idVerification.selfiePhotoUrl) &&
             this.idVerification.status !== 'pending' &&
             this.idVerification.status !== 'verified'
    },
    canProceedToNextStep() {
      switch (this.currentStep) {
        case 1:
          return this.idVerification.idType && this.idVerification.idNumber.trim()
        case 2:
          return this.idVerification.idPhoto || this.idVerification.idPhotoUrl
        case 3:
          return this.idVerification.selfiePhoto || this.idVerification.selfiePhotoUrl
        default:
          return false
      }
    }
  },
  created() {
    this.fetchUserProfile()
    this.loadIdVerificationStatus()
  },
  methods: {
    // Notification System Methods
    showNotification(message, type = 'info', duration = 5000) {
      const id = this.notificationId++
      const notification = {
        id,
        message,
        type,
        duration
      }
      
      this.notifications.push(notification)
      
      // Auto-remove after duration
      setTimeout(() => {
        this.removeNotification(id)
      }, duration)
    },
    
    removeNotification(id) {
      this.notifications = this.notifications.filter(n => n.id !== id)
    },
    
    async fetchUserProfile() {
      this.loading = true
      this.error = null
      
      try {
        // First check if user is authenticated
        const authUser = await getCurrentUser()
        if (!authUser) {
          throw new Error('Please login to view your profile')
        }

        // Fetch profile data from API
        const response = await axios.get('/auth/me')
        
        if (response.data.status === 'success') {
          this.user = response.data.user || response.data
          
          // Add bio from localStorage
          this.user.bio = localStorage.getItem(`user_bio_${this.user.id}`) || ''
          
          // Store original data for comparison
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          
          // Try to fetch service provider stats
          await this.fetchServiceProviderStats()
          
          this.showNotification('Profile loaded successfully!', 'success')
        } else {
          throw new Error('Failed to load profile data')
        }
      } catch (error) {
        console.error('Error fetching profile:', error)
        this.error = error.response?.data?.message || error.message || 'Failed to load profile data'
        
        if (error.response?.status === 401) {
          clearAuthData()
          this.$router.push('/Landing/logIn')
          this.showNotification('Session expired. Please login again.', 'error')
        } else {
          this.showNotification(this.error, 'error')
        }
      } finally {
        this.loading = false
      }
    },
    
    async fetchServiceProviderStats() {
      try {
        // FIXED: Removed /api prefix since axios already has it
        const response = await axios.get('/dashboard/service-provider')
        
        if (response.data.status === 'success') {
          this.stats = {
            completedJobs: response.data.data?.completedJobs || response.data.completedJobs || 0,
            satisfaction: response.data.data?.satisfaction || response.data.satisfaction || 0,
            activeProjects: response.data.data?.activeProjects || 0
          }
        }
      } catch (error) {
        console.error('Error fetching service provider stats:', error)
        this.stats = { completedJobs: 0, satisfaction: 0, activeProjects: 0 }
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Unknown'
      try {
        const date = new Date(dateString)
        if (isNaN(date.getTime())) return 'Unknown'
        return date.toLocaleDateString('en-US', { 
          year: 'numeric', 
          month: 'long', 
          day: 'numeric' 
        })
      } catch (e) {
        return 'Unknown'
      }
    },
    
    formatRole(role) {
      const roles = {
        'client': 'Client Account',
        'distributor': 'Distributor Account',
        'service_provider': 'Service Provider Account',
        'admin': 'Administrator'
      }
      return roles[role] || (role ? role.replace('_', ' ') : 'Service Provider')
    },
    
    markChanged() {
      if (!this.originalUser) return
      
      const current = JSON.stringify({
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        phone: this.user.phone,
        address: this.user.address,
        bio: this.user.bio
      })
      
      const original = JSON.stringify({
        first_name: this.originalUser.first_name,
        last_name: this.originalUser.last_name,
        email: this.originalUser.email,
        phone: this.originalUser.phone,
        address: this.originalUser.address,
        bio: this.originalUser.bio
      })
      
      this.hasChanges = current !== original
    },
    
    async saveProfile() {
      if (!this.hasChanges || this.isLoading) return
      
      this.isLoading = true
      this.validationErrors = {}
      
      try {
        const updateData = {
          first_name: this.user.first_name,
          last_name: this.user.last_name,
          email: this.user.email,
          phone: this.user.phone,
          address: this.user.address
        }
        
        const response = await axios.put('/profile', updateData)
        
        if (response.data.status === 'success') {
          if (this.user.bio && this.user.id) {
            localStorage.setItem(`user_bio_${this.user.id}`, this.user.bio)
          }
          
          Object.assign(this.user, updateData)
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          
          this.showNotification('Profile updated successfully!', 'success')
        } else {
          throw new Error('Update failed')
        }
      } catch (error) {
        console.error('Error updating profile:', error)
        
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.showNotification('Please fix the validation errors', 'error')
        } else {
          this.showNotification(
            error.response?.data?.message || 'Failed to update profile', 
            'error'
          )
        }
      } finally {
        this.isLoading = false
      }
    },
    
    resetChanges() {
      if (!this.originalUser) return
      
      this.user = JSON.parse(JSON.stringify(this.originalUser))
      this.hasChanges = false
      this.validationErrors = {}
      this.showNotification('Changes have been reset', 'info')
    },
    
    hasSpecialChars(password) {
      return /[^A-Za-z0-9]/.test(password)
    },
    
    hasNumbers(password) {
      return /[0-9]/.test(password)
    },
    
    hasUpperCase(password) {
      return /[A-Z]/.test(password)
    },
    
    async changePassword() {
      if (!this.canChangePassword || this.passwordLoading) return
      
      this.passwordLoading = true
      this.passwordError = null
      this.validationErrors = {}
      
      try {
        const passwordData = {
          current_password: this.password.current,
          password: this.password.new,
          password_confirmation: this.password.confirm
        }
        
        const response = await axios.put('/profile/password', passwordData)
        
        if (response.data.status === 'success') {
          this.password = { current: '', new: '', confirm: '' }
          this.showChangePassword = false
          
          this.securityStatus.passwordLastChanged = 'Just now'
          
          this.showNotification('Password changed successfully!', 'success')
        } else {
          throw new Error('Password change failed')
        }
      } catch (error) {
        console.error('Error changing password:', error)
        
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.passwordError = error.response.data.message || 'Password change failed'
          this.showNotification(this.passwordError, 'error')
        } else {
          this.passwordError = error.response?.data?.message || error.message || 'Failed to change password'
          this.showNotification(this.passwordError, 'error')
        }
      } finally {
        this.passwordLoading = false
      }
    },
    
    editAvatar() {
      this.showNotification('Avatar upload feature will be available soon', 'info')
    },

    // Wizard Methods
    nextStep() {
      if (this.currentStep < 3 && this.canProceedToNextStep) {
        this.currentStep++
      }
    },
    
    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--
      }
    },

    // ID Verification Methods
    async loadIdVerificationStatus() {
      try {
        // FIXED: Removed /api prefix since axios already has it
        const response = await axios.get('/service-provider/requirements')
        
        if (response.data.status === 'success') {
          const data = response.data.data
          this.idVerification = {
            idType: data.id_type || '',
            idNumber: data.id_number || '',
            idPhoto: null,
            idPhotoPreview: null,
            idPhotoUrl: data.id_photo_url || null,
            selfiePhoto: null,
            selfiePhotoPreview: null,
            selfiePhotoUrl: data.selfie_photo_url || null,
            status: data.status || 'not_submitted',
            submittedAt: data.submitted_at || null,
            reviewedAt: data.reviewed_at || null,
            rejectionReason: data.rejection_reason || null
          }
          
          // Set current step based on status
          if (this.idVerification.status === 'pending' || this.idVerification.status === 'verified') {
            this.currentStep = 1
          }
        }
      } catch (error) {
        console.error('Error loading ID verification status:', error)
        // Use default if API fails
      }
    },

    handleDragOver(event) {
      this.isDraggingId = true
      event.preventDefault()
    },

    handleDragLeave() {
      this.isDraggingId = false
    },

    handleIdDrop(event) {
      this.isDraggingId = false
      const files = event.dataTransfer.files
      if (files.length > 0 && this.idVerification.status !== 'pending') {
        this.processIdFile(files[0])
      }
    },

    handleSelfieDragOver(event) {
      this.isDraggingSelfie = true
      event.preventDefault()
    },

    handleSelfieDragLeave() {
      this.isDraggingSelfie = false
    },

    handleSelfieDrop(event) {
      this.isDraggingSelfie = false
      const files = event.dataTransfer.files
      if (files.length > 0 && this.idVerification.status !== 'pending') {
        this.processSelfieFile(files[0])
      }
    },

    triggerIdUpload() {
      if (this.idVerification.status !== 'pending') {
        this.$refs.idFileInput.click()
      }
    },

    triggerSelfieUpload() {
      if (this.idVerification.status !== 'pending') {
        this.$refs.selfieFileInput.click()
      }
    },

    handleIdUploadChange(event) {
      const file = event.target.files[0]
      if (file && this.idVerification.status !== 'pending') {
        this.processIdFile(file)
      }
    },

    handleSelfieUploadChange(event) {
      const file = event.target.files[0]
      if (file && this.idVerification.status !== 'pending') {
        this.processSelfieFile(file)
      }
    },

    processIdFile(file) {
      if (!file.type.startsWith('image/')) {
        this.idVerificationErrors.idPhoto = 'Please upload an image file'
        this.showNotification('Please upload an image file', 'error')
        return
      }

      if (file.size > 5 * 1024 * 1024) {
        this.idVerificationErrors.idPhoto = 'File size must be less than 5MB'
        this.showNotification('File size must be less than 5MB', 'error')
        return
      }

      this.idVerificationErrors.idPhoto = null

      this.idUploadProgress = 0
      const interval = setInterval(() => {
        this.idUploadProgress += 10
        if (this.idUploadProgress >= 100) {
          clearInterval(interval)
          const reader = new FileReader()
          reader.onload = (e) => {
            this.idVerification.idPhotoPreview = e.target.result
            this.idVerification.idPhoto = file
            this.idVerification.idPhotoUrl = null
            this.showNotification('ID photo uploaded successfully!', 'success')
          }
          reader.readAsDataURL(file)
        }
      }, 100)
    },

    processSelfieFile(file) {
      if (!file.type.startsWith('image/')) {
        this.idVerificationErrors.selfiePhoto = 'Please upload an image file'
        this.showNotification('Please upload an image file', 'error')
        return
      }

      if (file.size > 5 * 1024 * 1024) {
        this.idVerificationErrors.selfiePhoto = 'File size must be less than 5MB'
        this.showNotification('File size must be less than 5MB', 'error')
        return
      }

      this.idVerificationErrors.selfiePhoto = null

      const reader = new FileReader()
      reader.onload = (e) => {
        this.idVerification.selfiePhotoPreview = e.target.result
        this.idVerification.selfiePhoto = file
        this.idVerification.selfiePhotoUrl = null
        this.showNotification('Selfie with ID uploaded successfully!', 'success')
      }
      reader.readAsDataURL(file)
    },

    removeIdPhoto() {
      this.idVerification.idPhoto = null
      this.idVerification.idPhotoPreview = null
      this.idVerification.idPhotoUrl = null
      this.idVerification.idType = ''
      this.idVerification.idNumber = ''
      this.idUploadProgress = 0
      this.showNotification('ID photo removed', 'info')
    },

    removeSelfiePhoto() {
      this.idVerification.selfiePhoto = null
      this.idVerification.selfiePhotoPreview = null
      this.idVerification.selfiePhotoUrl = null
      this.showNotification('Selfie photo removed', 'info')
    },

    getReadableIdType(idType) {
      const types = {
        'phil_id': 'Philippine National ID (PhilID/ePhilID)',
        'passport': 'Passport',
        'driver_license': "Driver's License",
        'umid': 'UMID (SSS/GSIS)',
        'prc_id': 'PRC ID',
        'voter_id': "Voter's ID",
        'postal_id': 'Postal ID',
        'philhealth': 'PhilHealth ID',
        'nbi': 'NBI Clearance',
        'senior_citizen': 'Senior Citizen ID',
        'other': 'Other Valid ID'
      }
      return types[idType] || idType
    },

    async submitIdVerification() {
      if (!this.canSubmitIdVerification || this.idVerificationLoading) return

      this.idVerificationLoading = true
      this.idVerificationErrors = {}

      if (!this.idVerification.idType) {
        this.idVerificationErrors.idType = 'Please select an ID type'
        this.showNotification('Please select an ID type', 'error')
        this.idVerificationLoading = false
        return
      }

      if (!this.idVerification.idNumber.trim()) {
        this.idVerificationErrors.idNumber = 'Please enter your ID number'
        this.showNotification('Please enter your ID number', 'error')
        this.idVerificationLoading = false
        return
      }

      try {
        const formData = new FormData()
        formData.append('id_type', this.idVerification.idType)
        formData.append('id_number', this.idVerification.idNumber)
        formData.append('id_photo', this.idVerification.idPhoto)
        formData.append('selfie_photo', this.idVerification.selfiePhoto)

        // FIXED: Removed /api prefix since axios already has it
        const response = await axios.post('/service-provider/requirements', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        if (response.data.status === 'success') {
          this.idVerification.status = 'pending'
          this.idVerification.submittedAt = response.data.data.submitted_at
          this.idVerification.idPhotoUrl = response.data.data.id_photo_url
          this.idVerification.selfiePhotoUrl = response.data.data.selfie_photo_url
          
          // Clear file previews
          this.idVerification.idPhoto = null
          this.idVerification.idPhotoPreview = null
          this.idVerification.selfiePhoto = null
          this.idVerification.selfiePhotoPreview = null
          
          // Reset to step 1
          this.currentStep = 1
          
          this.showNotification('ID verification submitted successfully! Verification typically takes 1-2 business days.', 'success')
        }
      } catch (error) {
        console.error('Error submitting ID verification:', error)
        
        if (error.response?.status === 422) {
          this.idVerificationErrors = error.response.data.errors || {}
          this.showNotification('Please fix the validation errors', 'error')
        } else {
          this.showNotification(
            error.response?.data?.message || 'Failed to submit ID verification. Please try again.',
            'error'
          )
        }
      } finally {
        this.idVerificationLoading = false
      }
    }
  },
  mounted() {
    if (!this.loading) {
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
}
</script>

<style scoped>
  @import "../serviceProvider/style/profileSP.css";

  /* Notification Container - NOW AT ROOT LEVEL */
  .notifications-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 350px;
    max-width: calc(100vw - 40px);
    pointer-events: none; /* Allow clicks to pass through when empty */
  }

  .notification {
    position: relative;
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
    border-left: 4px solid #3d6ef7;
    pointer-events: auto; /* Enable clicks on notifications */
  }

  .notification:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
  }

  .notification.success {
    border-left-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  }

  .notification.error {
    border-left-color: #ef4444;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  }

  .notification.warning {
    border-left-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  }

  .notification.info {
    border-left-color: #3d6ef7;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  }

  .notification-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .notification-icon .icon {
    width: 20px;
    height: 20px;
    stroke-width: 2;
  }

  .notification.success .icon {
    stroke: #10b981;
  }

  .notification.error .icon {
    stroke: #ef4444;
  }

  .notification.warning .icon {
    stroke: #f59e0b;
  }

  .notification.info .icon {
    stroke: #3d6ef7;
  }

  .notification-content {
    flex: 1;
    min-width: 0;
  }

  .notification-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
    color: #1f2937;
  }

  .notification.success .notification-title {
    color: #065f46;
  }

  .notification.error .notification-title {
    color: #991b1b;
  }

  .notification.warning .notification-title {
    color: #92400e;
  }

  .notification.info .notification-title {
    color: #1e40af;
  }

  .notification-message {
    font-size: 13px;
    line-height: 1.5;
    color: #4b5563;
  }

  .notification.success .notification-message {
    color: #047857;
  }

  .notification.error .notification-message {
    color: #b91c1c;
  }

  .notification.warning .notification-message {
    color: #92400e;
  }

  .notification.info .notification-message {
    color: #1e40af;
  }

  .notification-close {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    opacity: 0.6;
    transition: opacity 0.2s;
  }

  .notification-close:hover {
    opacity: 1;
  }

  .notification-close .close-icon {
    width: 16px;
    height: 16px;
    stroke: #6b7280;
  }

  .notification-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: rgba(0, 0, 0, 0.1);
    animation: progress-bar linear forwards;
  }

  .notification.success .notification-progress {
    background: rgba(16, 185, 129, 0.3);
  }

  .notification.error .notification-progress {
    background: rgba(239, 68, 68, 0.3);
  }

  .notification.warning .notification-progress {
    background: rgba(245, 158, 11, 0.3);
  }

  .notification.info .notification-progress {
    background: rgba(59, 130, 246, 0.3);
  }

  /* Animation for notifications */
  .notification-slide-enter-active,
  .notification-slide-leave-active {
    transition: all 0.4s ease;
  }

  .notification-slide-enter-from {
    opacity: 0;
    transform: translateX(100%);
  }

  .notification-slide-leave-to {
    opacity: 0;
    transform: translateX(100%);
  }

  @keyframes progress-bar {
    from {
      width: 100%;
    }
    to {
      width: 0%;
    }
  }

  /* Wizard animations */
  @keyframes fade-in {
    from {
      opacity: 0;
      transform: translateY(10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .animate-fade-in {
    animation: fade-in 0.3s ease-out forwards;
  }

  /* Step indicator animation */
  @keyframes pulse-step {
    0%, 100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
  }

  /* Responsive notifications */
  @media (max-width: 640px) {
    .notifications-container {
      top: 10px;
      right: 10px;
      left: 10px;
      width: auto;
    }
    
    .notification {
      padding: 12px;
    }
    
    /* Wizard responsive adjustments */
    .flex-col.md\:flex-row {
      flex-direction: column;
      align-items: flex-start;
    }
    
    .hidden.md\:block {
      display: none;
    }
    
    .flex.items-center.gap-4 {
      gap: 2rem;
    }
    
    .w-12.h-12 {
      width: 2.5rem;
      height: 2.5rem;
    }
  }

  /* Responsive wizard steps */
  @media (max-width: 768px) {
    .flex-col.md\:flex-row {
      flex-direction: column;
      align-items: stretch;
    }
    
    .w-16.h-1 {
      display: none;
    }
    
    .flex.items-center.gap-4 {
      justify-content: space-between;
      width: 100%;
    }
    
    .grid.grid-cols-1.md\:grid-cols-2 {
      grid-template-columns: 1fr;
    }
  }

  /* Smooth transitions for wizard content */
  .transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
  }
</style>