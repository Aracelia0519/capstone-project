<template>
  <div class="p-4 md:p-6 operational-distributors">
    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-80 z-50 flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-600">Loading operational distributors...</p>
      </div>
    </div>

    <!-- Toastify Container -->
    <div id="toast-container"></div>

    <!-- Page Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Operational Distributors</h1>
          <p class="text-gray-600 mt-2">Manage your network of operational distributors</p>
        </div>
        <div class="flex items-center space-x-3">
          <!-- Stats Quick View -->
          <div class="flex items-center space-x-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ stats?.total || 0 }}</div>
              <div class="text-xs text-gray-500">Total Distributors</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ stats?.active || 0 }}</div>
              <div class="text-xs text-gray-500">Active</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">{{ stats?.with_accounts || 0 }}</div>
              <div class="text-xs text-gray-500">With Accounts</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Left Column: Quick Actions & Stats -->
      <div class="lg:col-span-1 space-y-6">
        <!-- Quick Actions Card -->
        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl shadow-sm border border-blue-100 p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Quick Actions</h2>
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
          
          <div class="space-y-3">
            <button 
              @click="handleAddNewDistributor()" 
              :disabled="!isVerifiedDistributor"
              :class="[isVerifiedDistributor ? 'bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
              class="w-full px-4 py-3 text-white rounded-lg transition-all duration-300 flex items-center justify-center group transform hover:scale-[1.02]">
              <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add New Distributor
            </button>
            
            <button @click="exportToExcel" 
              class="w-full px-4 py-3 bg-white border border-blue-200 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors flex items-center justify-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export to Excel
            </button>
            
            <button @click="refreshData" 
              class="w-full px-4 py-3 bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors flex items-center justify-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh Data
            </button>
          </div>
          
          <!-- Verification Status -->
          <div v-if="!isVerifiedDistributor" class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
              <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              <div>
                <h4 class="font-medium text-yellow-800">Verification Required</h4>
                <p class="text-sm text-yellow-700 mt-1">
                  You must be verified as a distributor to manage operational distributors.
                  <router-link to="/distributor/ProfileSettings" class="underline ml-1">Complete verification</router-link>
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Performance Overview Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Performance</h2>
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
          </div>
          
          <div class="space-y-4">
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">Active Rate</span>
                <span class="text-sm font-bold text-green-600">{{ getActiveRate() }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-500 h-2 rounded-full" :style="{ width: getActiveRate() + '%' }"></div>
              </div>
            </div>
            
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-sm font-medium text-gray-700">Account Setup</span>
                <span class="text-sm font-bold text-blue-600">{{ getAccountRate() }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-blue-500 h-2 rounded-full" :style="{ width: getAccountRate() + '%' }"></div>
              </div>
            </div>
            
            <div class="pt-4 border-t border-gray-200">
              <div class="grid grid-cols-2 gap-3">
                <div class="text-center p-3 bg-blue-50 rounded-lg">
                  <div class="text-xl font-bold text-blue-700">{{ stats?.pending || 0 }}</div>
                  <div class="text-xs text-blue-600">Pending Review</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                  <div class="text-xl font-bold text-purple-700">{{ stats?.inactive || 0 }}</div>
                  <div class="text-xs text-purple-600">Inactive</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Main Content -->
      <div class="lg:col-span-3 space-y-6">
        <!-- Operational Distributors Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <!-- Card Header -->
          <div class="p-5 md:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div>
                <h2 class="text-xl font-bold text-gray-800">Operational Distributors</h2>
                <p class="text-gray-600 mt-1">Manage your network of operational distributors</p>
              </div>
              <div class="flex items-center space-x-3">
                <!-- Search -->
                <div class="relative">
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <input v-model="search" type="text" 
                    placeholder="Search distributors..." 
                    class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-64">
                </div>
                
                <!-- Filter Dropdown -->
                <div class="relative">
                  <button @click="showFilterDropdown = !showFilterDropdown"
                    class="px-4 py-2.5 border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  
                  <!-- Filter Dropdown Menu -->
                  <div v-if="showFilterDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                    <div class="p-2">
                      <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input v-model="filterStatus" type="radio" value="" class="mr-2">
                        <span class="text-sm">All Status</span>
                      </label>
                      <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input v-model="filterStatus" type="radio" value="active" class="mr-2">
                        <span class="text-sm text-green-600">● Active</span>
                      </label>
                      <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input v-model="filterStatus" type="radio" value="pending" class="mr-2">
                        <span class="text-sm text-yellow-600">● Pending</span>
                      </label>
                      <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input v-model="filterStatus" type="radio" value="inactive" class="mr-2">
                        <span class="text-sm text-red-600">● Inactive</span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Create Operational Distributor Wizard -->
          <div v-if="showForm && isVerifiedDistributor" class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
            <!-- Wizard Progress -->
            <div class="mb-8">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800 flex items-center">
                  <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create New Operational Distributor
                </h3>
                <button @click="cancelForm" class="text-gray-500 hover:text-gray-700 transition-colors">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Progress Steps -->
              <div class="flex items-center justify-between mb-6">
                <div class="flex-1 relative">
                  <!-- Progress Bar -->
                  <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 transform -translate-y-1/2"></div>
                  <div class="absolute top-1/2 left-0 h-1 bg-blue-500 transform -translate-y-1/2 transition-all duration-500" 
                       :style="{ width: `${((wizardStep - 1) / 3) * 100}%` }"></div>
                  
                  <!-- Step Indicators -->
                  <div class="relative flex justify-between">
                    <button @click="setWizardStep(1)" 
                      :class="wizardStep >= 1 ? 'text-blue-600' : 'text-gray-400'"
                      class="flex flex-col items-center focus:outline-none z-10">
                      <div :class="wizardStep >= 1 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'" 
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                        <span v-if="wizardStep > 1" class="text-white">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                        </span>
                        <span v-else class="font-semibold">1</span>
                      </div>
                      <span class="text-xs font-medium">Personal Info</span>
                    </button>
                    
                    <button @click="setWizardStep(2)" :disabled="wizardStep < 2"
                      :class="wizardStep >= 2 ? 'text-blue-600' : 'text-gray-400'"
                      class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                      <div :class="wizardStep >= 2 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'" 
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                        <span v-if="wizardStep > 2" class="text-white">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                        </span>
                        <span v-else class="font-semibold">2</span>
                      </div>
                      <span class="text-xs font-medium">ID Verification</span>
                    </button>
                    
                    <button @click="setWizardStep(3)" :disabled="wizardStep < 3"
                      :class="wizardStep >= 3 ? 'text-blue-600' : 'text-gray-400'"
                      class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                      <div :class="wizardStep >= 3 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'" 
                        class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                        <span class="font-semibold">3</span>
                      </div>
                      <span class="text-xs font-medium">Account Setup</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Wizard Content -->
            <div class="space-y-6">
              <!-- Step 1: Personal Information -->
              <div v-if="wizardStep === 1" class="animate-fade-in">
                <div class="bg-white rounded-xl shadow-lg p-6 border border-blue-100">
                  <div class="flex items-center mb-6">
                    <div class="p-3 bg-blue-100 rounded-lg mr-4 flex-shrink-0">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-gray-800">Personal Information</h4>
                      <p class="text-gray-600">Enter the distributor's basic information</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        First Name <span class="text-red-500">*</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                        </div>
                        <input v-model="form.first_name" type="text" 
                          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                          placeholder="Enter first name">
                      </div>
                    </div>
                    
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Last Name <span class="text-red-500">*</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                          </svg>
                        </div>
                        <input v-model="form.last_name" type="text" 
                          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                          placeholder="Enter last name">
                      </div>
                    </div>
                    
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Email Address <span class="text-red-500">*</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                          </svg>
                        </div>
                        <input v-model="form.email" type="email" 
                          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                          placeholder="email@example.com">
                      </div>
                    </div>
                    
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Phone Number <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 ml-1">(11 digits starting with 0)</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                          </svg>
                        </div>
                        <input v-model="form.phone" type="tel" 
                          @input="validatePhoneNumber"
                          maxlength="11"
                          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                          placeholder="09XXXXXXXXX">
                      </div>
                      <p v-if="phoneError" class="text-xs text-red-500 mt-1 animate-pulse">{{ phoneError }}</p>
                    </div>
                    
                    <div class="md:col-span-2 space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Address
                      </label>
                      <div class="relative group">
                        <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                          </svg>
                        </div>
                        <textarea v-model="form.address" rows="3" 
                          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400 resize-none"
                          placeholder="Complete address including city and region"></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 2: ID Verification -->
              <div v-if="wizardStep === 2" class="animate-fade-in">
                <div class="bg-white rounded-xl shadow-lg p-6 border border-blue-100">
                  <div class="flex items-center mb-6">
                    <div class="p-3 bg-blue-100 rounded-lg mr-4 flex-shrink-0">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-gray-800">ID Verification</h4>
                      <p class="text-gray-600">Upload valid ID for verification</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Type of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                          </svg>
                        </div>
                        <select v-model="form.valid_id_type" 
                          class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400 appearance-none bg-white">
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
                    </div>
                    
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        ID Number <span class="text-red-500">*</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                          </svg>
                        </div>
                        <input v-model="form.id_number" type="text" 
                          class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                          placeholder="Enter ID number">
                      </div>
                    </div>
                    
                    <div class="md:col-span-2 space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Photo of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <div @click="triggerFileInput('valid_id_photo')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'valid_id_photo')"
                        :class="form.valid_id_photo ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-blue-400'"
                        class="cursor-pointer p-6 border-2 border-dashed rounded-xl transition-all duration-300 group hover:shadow-md">
                        <div class="space-y-4 text-center">
                          <div class="relative inline-block">
                            <svg class="mx-auto h-16 w-16 text-gray-400 group-hover:text-blue-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                              <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <div v-if="form.valid_id_photo" class="absolute -top-2 -right-2">
                              <div class="bg-green-500 text-white p-1 rounded-full">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                              </div>
                            </div>
                          </div>
                          <div class="px-4">
                            <div class="flex flex-col sm:flex-row text-sm text-gray-600 justify-center items-center gap-1">
                              <label class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 transition-colors">
                                <span class="text-lg">Click to upload</span>
                              </label>
                              <p class="text-gray-500">or drag and drop</p>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">PNG, JPG, PDF up to 5MB</p>
                            <p v-if="form.valid_id_photo" class="text-sm text-green-600 mt-3 font-medium truncate max-w-xs mx-auto">
                              ✓ {{ form.valid_id_photo.name }}
                            </p>
                          </div>
                        </div>
                      </div>
                      <input ref="valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                        @change="handleFileChange($event, 'valid_id_photo')">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Step 3: Account Setup -->
              <div v-if="wizardStep === 3" class="animate-fade-in">
                <div class="bg-white rounded-xl shadow-lg p-6 border border-blue-100">
                  <div class="flex items-center mb-6">
                    <div class="p-3 bg-blue-100 rounded-lg mr-4 flex-shrink-0">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-gray-800">Account Setup</h4>
                      <p class="text-gray-600">Create secure login credentials</p>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Password <span class="text-red-500">*</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                          </svg>
                        </div>
                        <input v-model="form.password" :type="showPassword ? 'text' : 'password'" 
                          class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                          placeholder="Create secure password">
                        <button @click="showPassword = !showPassword" 
                          class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            <path v-if="!showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                      </div>
                      
                      <!-- Password Requirements -->
                      <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Password Requirements</h5>
                        <div class="space-y-2">
                          <div v-for="requirement in passwordRequirements" :key="requirement.text" 
                            class="flex items-center">
                            <svg class="w-4 h-4 mr-2" :class="requirement.met ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span :class="requirement.met ? 'text-green-600 font-medium' : 'text-gray-500'" class="text-sm">{{ requirement.text }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Confirm Password <span class="text-red-500">*</span>
                      </label>
                      <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                          </svg>
                        </div>
                        <input v-model="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" 
                          class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                          placeholder="Confirm your password">
                        <button @click="showConfirmPassword = !showConfirmPassword" 
                          class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            <path v-if="!showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                      </div>
                      
                      <!-- Password Match Indicator -->
                      <div v-if="form.password && form.password_confirmation" 
                        class="mt-4 p-4 rounded-lg border transition-all duration-300"
                        :class="passwordMatch ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                        <div class="flex items-center">
                          <svg class="w-5 h-5 mr-2" :class="passwordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="passwordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            <path v-if="!passwordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                          <span :class="passwordMatch ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                            {{ passwordMatch ? 'Passwords match perfectly!' : 'Passwords do not match' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Summary Preview -->
                  <div class="mt-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <h5 class="text-sm font-medium text-blue-800 mb-2">Summary Preview</h5>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                      <div class="text-gray-600">Name:</div>
                      <div class="font-medium">{{ form.first_name }} {{ form.last_name }}</div>
                      <div class="text-gray-600">Email:</div>
                      <div class="font-medium">{{ form.email || 'Not set' }}</div>
                      <div class="text-gray-600">Phone:</div>
                      <div class="font-medium">{{ form.phone || 'Not set' }}</div>
                      <div class="text-gray-600">ID Type:</div>
                      <div class="font-medium">{{ form.valid_id_type || 'Not set' }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Wizard Navigation -->
              <div class="flex justify-between pt-6">
                <button @click="previousStep" 
                  v-if="wizardStep > 1"
                  class="px-6 py-3 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                  <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                  Back
                </button>
                <div v-else></div>
                
                <button @click="nextStep" 
                  v-if="wizardStep < 3"
                  :disabled="!isStepValid"
                  :class="[isStepValid ? 'bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
                  class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                  Continue
                  <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
                
                <button @click="createOperationalDistributor" 
                  v-if="wizardStep === 3"
                  :disabled="!isFormValid || creating"
                  :class="[isFormValid && !creating ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed', creating ? 'bg-gradient-to-r from-green-400 to-emerald-400 cursor-wait' : '']"
                  class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center justify-center group hover:scale-[1.02] min-w-[160px]">
                  <svg v-if="!creating" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  <svg v-if="creating" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                  </svg>
                  {{ creating ? 'Creating...' : 'Create Distributor' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Operational Distributors Table -->
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Distributor</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contact</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ID Verification</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="distributor in filteredDistributors" :key="distributor.id" 
                  class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                          <span class="text-white font-semibold text-sm">{{ getInitials(distributor.full_name) }}</span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="font-medium text-gray-900">{{ distributor.full_name }}</div>
                        <div class="text-sm text-gray-500">Added {{ formatDate(distributor.created_at) }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <div class="flex items-center text-sm text-gray-900">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ distributor.email || 'No email' }}
                      </div>
                      <div class="flex items-center text-sm text-gray-500 mt-1">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ distributor.phone || 'No phone' }}
                      </div>
                      <div v-if="distributor.has_user_account" class="mt-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          </svg>
                          Account Active
                        </span>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ distributor.id_type_name || 'Not set' }}</div>
                      <div class="text-sm text-gray-500">{{ distributor.id_number || 'No ID' }}</div>
                      <a v-if="distributor.valid_id_photo_url" :href="distributor.valid_id_photo_url" target="_blank" 
                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 mt-1">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        View ID
                      </a>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="getStatusClasses(distributor.status)"
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                      <span :class="getStatusDotClass(distributor.status)" class="w-2 h-2 rounded-full mr-2"></span>
                      {{ distributor.status | capitalize }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center space-x-2">
                      <button @click="viewDistributor(distributor.id)" 
                        class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="View Details">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                      </button>
                      <button v-if="distributor.status !== 'active'" @click="activateDistributor(distributor.id)" 
                        class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                        title="Activate">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                      </button>
                      <button v-if="distributor.status === 'active'" @click="deactivateDistributor(distributor.id)" 
                        class="p-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                        title="Deactivate">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                      </button>
                      <button @click="deleteDistributor(distributor.id)" 
                        class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                
                <!-- Empty State -->
                <tr v-if="filteredDistributors.length === 0">
                  <td colspan="5" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                      </svg>
                      <h3 class="text-lg font-medium text-gray-900 mb-2">No operational distributors found</h3>
                      <p class="text-gray-500 mb-4">Get started by creating your first operational distributor.</p>
                      <button @click="handleAddNewDistributor()" 
                        :disabled="!isVerifiedDistributor"
                        :class="[isVerifiedDistributor ? 'bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
                        class="px-4 py-2 text-white rounded-lg transition-colors">
                        Add New Distributor
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="pagination && filteredDistributors.length > 0" 
            class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="text-sm text-gray-700">
                Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} distributors
              </div>
              <div class="flex items-center space-x-2">
                <button @click="changePage(pagination.current_page - 1)" 
                  :disabled="pagination.current_page === 1"
                  class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                  Previous
                </button>
                <div class="flex items-center space-x-1">
                  <button v-for="page in getVisiblePages()" :key="page"
                    @click="changePage(page)"
                    :class="[page === pagination.current_page ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100']"
                    class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium">
                    {{ page }}
                  </button>
                </div>
                <button @click="changePage(pagination.current_page + 1)" 
                  :disabled="pagination.current_page === pagination.last_page"
                  class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                  Next
                  <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
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
  name: 'OperationalDistributors',
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
      
      // Operational Distributors Wizard
      wizardStep: 1,
      showForm: false,
      creating: false,
      distributors: [],
      stats: null,
      pagination: null,
      search: '',
      filterStatus: '',
      showFilterDropdown: false,
      currentPage: 1,
      showPassword: false,
      showConfirmPassword: false,
      phoneError: '',
      
      // Verification data
      verificationData: null,
      
      // Form
      form: {
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
    }
  },
  computed: {
    passwordRequirements() {
      const password = this.form.password
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
    passwordMatch() {
      return this.form.password === this.form.password_confirmation
    },
    isFormValid() {
      return this.form.first_name &&
             this.form.last_name &&
             this.form.email &&
             this.form.phone &&
             this.form.phone.length === 11 &&
             /^\d+$/.test(this.form.phone) &&
             /^0/.test(this.form.phone) &&
             this.form.valid_id_type &&
             this.form.id_number &&
             this.form.valid_id_photo &&
             this.form.password &&
             this.form.password_confirmation &&
             this.passwordRequirements.every(req => req.met) &&
             this.passwordMatch
    },
    isStepValid() {
      switch (this.wizardStep) {
        case 1:
          return this.form.first_name &&
                 this.form.last_name &&
                 this.form.email &&
                 this.form.phone &&
                 this.form.phone.length === 11 &&
                 /^\d+$/.test(this.form.phone) &&
                 /^0/.test(this.form.phone)
        case 2:
          return this.form.valid_id_type &&
                 this.form.id_number &&
                 this.form.valid_id_photo
        case 3:
          return this.form.password &&
                 this.form.password_confirmation &&
                 this.passwordRequirements.every(req => req.met) &&
                 this.passwordMatch
        default:
          return false
      }
    },
    isVerifiedDistributor() {
      return this.verificationData && this.verificationData.status === 'approved'
    },
    filteredDistributors() {
      let filtered = this.distributors
      
      // Apply search filter
      if (this.search) {
        const search = this.search.toLowerCase()
        filtered = filtered.filter(distributor => {
          return distributor.full_name.toLowerCase().includes(search) ||
                 distributor.email?.toLowerCase().includes(search) ||
                 distributor.phone?.toLowerCase().includes(search) ||
                 distributor.id_number.toLowerCase().includes(search)
        })
      }
      
      // Apply status filter
      if (this.filterStatus) {
        filtered = filtered.filter(distributor => distributor.status === this.filterStatus)
      }
      
      return filtered
    }
  },
  async created() {
    await this.fetchInitialData()
  },
  methods: {
    // Toast Methods
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

    // Data Fetching
    async fetchInitialData() {
      this.loading = true
      try {
        await this.fetchVerificationData()
        await this.fetchDistributorsData()
      } catch (error) {
        console.error('Error fetching initial data:', error)
        this.showErrorToast('Failed to load data. Please try again.')
      } finally {
        this.loading = false
      }
    },

    async fetchVerificationData() {
      try {
        const response = await axios.get('/distributor/requirements')
        if (response.data.status === 'success') {
          this.verificationData = response.data.data
        }
      } catch (error) {
        console.error('Error fetching verification data:', error)
        this.verificationData = null
      }
    },

    async fetchDistributorsData() {
      if (!this.isVerifiedDistributor) return
      
      try {
        await Promise.all([
          this.fetchDistributors(),
          this.fetchStats()
        ])
      } catch (error) {
        console.error('Error fetching operational data:', error)
      }
    },

    async fetchDistributors(page = 1) {
      try {
        const response = await axios.get(`/distributor/operational-distributors?page=${page}&per_page=10`)
        if (response.data.status === 'success') {
          this.distributors = response.data.data.operational_distributors
          this.pagination = response.data.data.pagination
        }
      } catch (error) {
        console.error('Error fetching operational distributors:', error)
        this.showErrorToast('Failed to load operational distributors.')
      }
    },
    
    async fetchStats() {
      try {
        const response = await axios.get('/distributor/operational-distributors/statistics')
        if (response.data.status === 'success') {
          this.stats = response.data.data
        }
      } catch (error) {
        console.error('Error fetching operational stats:', error)
      }
    },

    // Wizard Navigation Methods
    setWizardStep(step) {
      if (step > 0 && step <= 3) {
        this.wizardStep = step
      }
    },

    nextStep() {
      if (this.wizardStep < 3 && this.isStepValid) {
        this.wizardStep++
      }
    },

    previousStep() {
      if (this.wizardStep > 1) {
        this.wizardStep--
      }
    },

    // Form Validation
    validatePhoneNumber() {
      const phone = this.form.phone
      
      if (phone.length > 0 && phone.length !== 11) {
        this.phoneError = 'Phone number must be exactly 11 digits'
      } else if (!/^\d+$/.test(phone)) {
        this.phoneError = 'Phone number must contain only digits'
      } else if (!/^0/.test(phone)) {
        this.phoneError = 'Phone number must start with 0'
      } else {
        this.phoneError = ''
      }
    },

    // Operational Distributor Methods
    async createOperationalDistributor() {
      if (!this.isFormValid || this.creating) return
      
      this.creating = true
      try {
        const formData = new FormData()
        
        // Append text fields
        formData.append('first_name', this.form.first_name)
        formData.append('last_name', this.form.last_name)
        formData.append('email', this.form.email)
        formData.append('phone', this.form.phone)
        formData.append('address', this.form.address)
        formData.append('valid_id_type', this.form.valid_id_type)
        formData.append('id_number', this.form.id_number)
        formData.append('password', this.form.password)
        formData.append('password_confirmation', this.form.password_confirmation)
        
        // Append file
        formData.append('valid_id_photo', this.form.valid_id_photo)
        
        const response = await axios.post('/distributor/operational-distributors', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor created successfully!')
          
          // Reset form
          this.cancelForm()
          
          // Refresh data
          await this.fetchDistributorsData()
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
        this.creating = false
      }
    },
    
    async viewDistributor(id) {
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
    
    async activateDistributor(id) {
      try {
        const response = await axios.post(`/distributor/operational-distributors/${id}/activate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor activated successfully.')
          await this.fetchDistributorsData()
        }
      } catch (error) {
        console.error('Error activating operational distributor:', error)
        this.showErrorToast('Failed to activate operational distributor.')
      }
    },
    
    async deactivateDistributor(id) {
      try {
        const response = await axios.post(`/distributor/operational-distributors/${id}/deactivate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor deactivated successfully.')
          await this.fetchDistributorsData()
        }
      } catch (error) {
        console.error('Error deactivating operational distributor:', error)
        this.showErrorToast('Failed to deactivate operational distributor.')
      }
    },
    
    async deleteDistributor(id) {
      if (!confirm('Are you sure you want to delete this operational distributor? This action cannot be undone.')) {
        return
      }
      
      try {
        const response = await axios.delete(`/distributor/operational-distributors/${id}`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor deleted successfully.')
          await this.fetchDistributorsData()
        }
      } catch (error) {
        console.error('Error deleting operational distributor:', error)
        this.showErrorToast('Failed to delete operational distributor.')
      }
    },

    // File Methods
    triggerFileInput(field) {
      this.$refs[field].click()
    },
    
    handleFileChange(event, field) {
      const file = event.target.files[0]
      if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
          this.showErrorToast(`File "${file.name}" is too large. Maximum size is 5MB.`)
          return
        }
        
        // Check file type
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
        if (!validTypes.includes(file.type)) {
          this.showErrorToast(`File "${file.name}" must be JPG, PNG, or PDF.`)
          return
        }
        
        this.form[field] = file
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

    // Form Methods
    cancelForm() {
      this.form = {
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
      this.showForm = false
      this.wizardStep = 1
    },

    // Pagination Methods
    changePage(page) {
      this.currentPage = page
      this.fetchDistributors(page)
    },

    getVisiblePages() {
      if (!this.pagination) return []
      
      const current = this.pagination.current_page
      const last = this.pagination.last_page
      const pages = []
      
      // Always show first page
      pages.push(1)
      
      // Show pages around current page
      for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
        pages.push(i)
      }
      
      // Always show last page if different from first
      if (last > 1) {
        pages.push(last)
      }
      
      // Remove duplicates and sort
      return [...new Set(pages)].sort((a, b) => a - b)
    },

    // Utility Methods
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
        month: 'short',
        day: 'numeric'
      })
    },

    getStatusClasses(status) {
      switch (status) {
        case 'active': return 'bg-green-100 text-green-800'
        case 'pending': return 'bg-yellow-100 text-yellow-800'
        case 'inactive': return 'bg-red-100 text-red-800'
        default: return 'bg-gray-100 text-gray-800'
      }
    },

    getStatusDotClass(status) {
      switch (status) {
        case 'active': return 'bg-green-500'
        case 'pending': return 'bg-yellow-500'
        case 'inactive': return 'bg-red-500'
        default: return 'bg-gray-500'
      }
    },

    getActiveRate() {
      if (!this.stats || this.stats.total === 0) return 0
      return Math.round((this.stats.active / this.stats.total) * 100)
    },

    getAccountRate() {
      if (!this.stats || this.stats.total === 0) return 0
      return Math.round((this.stats.with_accounts / this.stats.total) * 100)
    },

    // UI Methods
    handleAddNewDistributor() {
      if (this.isVerifiedDistributor) {
        this.showForm = true
      }
    },

    async refreshData() {
      await this.fetchInitialData()
      this.showSuccessToast('Data refreshed successfully!')
    },

    exportToExcel() {
      this.showInfoToast('Export to Excel feature would be implemented here')
    }
  }
}
</script>

<style scoped>
.operational-distributors {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  min-height: 100vh;
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
  background: #c1c1c1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}

/* Table row hover effect */
tbody tr {
  transition: all 0.2s ease;
}

tbody tr:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

/* Animations */
@keyframes fadeIn {
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
  animation: fadeIn 0.3s ease-out;
}

/* Custom focus styles */
input:focus, select:focus, textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Button hover effects */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

/* Wizard step improvements */
@media (max-width: 768px) {
  .wizard-step-indicators {
    gap: 0.5rem;
  }
  
  .wizard-step-indicators button {
    min-width: auto;
  }
  
  .wizard-step-indicators .text-xs {
    font-size: 0.7rem;
  }
}

/* Ensure wizard step indicators don't overlap */
.z-10 {
  z-index: 10;
}

.relative {
  position: relative;
}

/* Fix for flex items on mobile */
.flex-shrink-0 {
  flex-shrink: 0;
}

/* Improve responsive text in file upload */
@media (max-width: 640px) {
  .text-lg {
    font-size: 1rem;
  }
  
  .flex-col.sm\:flex-row {
    flex-direction: column;
    align-items: center;
  }
  
  .gap-1 {
    gap: 0.25rem;
  }
  
  .max-w-xs {
    max-width: 16rem;
  }
}
</style>