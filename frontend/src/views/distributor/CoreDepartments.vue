<template>
  <div class="p-4 md:p-6 core-departments">
    <!-- Loading Overlay -->
    <div v-if="loading" class="fixed inset-0 bg-white bg-opacity-80 z-50 flex items-center justify-center">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-600">Loading core departments...</p>
      </div>
    </div>

    <!-- Toastify Container -->
    <div id="toast-container"></div>

    <!-- Page Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Core Departments</h1>
          <p class="text-gray-600 mt-2">Manage operational distributors, HR, and Finance department personnel</p>
        </div>
        <div class="flex items-center space-x-3">
          <!-- Stats Quick View -->
          <div class="flex items-center space-x-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ getTotalStats().total || 0 }}</div>
              <div class="text-xs text-gray-500">Total Staff</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ getTotalStats().active || 0 }}</div>
              <div class="text-xs text-gray-500">Active</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">{{ getTotalStats().withAccounts || 0 }}</div>
              <div class="text-xs text-gray-500">With Accounts</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'operational'"
            :class="activeTab === 'operational' 
              ? 'border-blue-500 text-blue-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              Operational Distributors
              <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ operationalStats?.total || 0 }}
              </span>
            </div>
          </button>
          <button
            @click="activeTab = 'hr'"
            :class="activeTab === 'hr' 
              ? 'border-green-500 text-green-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
              </svg>
              HR Managers
              <span class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ hrStats?.total || 0 }}
              </span>
            </div>
          </button>
          <button
            @click="activeTab = 'finance'"
            :class="activeTab === 'finance' 
              ? 'border-purple-500 text-purple-600' 
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
          >
            <div class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              Finance Managers
              <span class="ml-2 bg-purple-100 text-purple-800 text-xs font-semibold px-2 py-0.5 rounded-full">
                {{ financeStats?.total || 0 }}
              </span>
            </div>
          </button>
        </nav>
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
              @click="handleAddNewStaff()" 
              :disabled="!isVerifiedDistributor"
              :class="[isVerifiedDistributor ? getButtonGradient() : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
              class="w-full px-4 py-3 text-white rounded-lg transition-all duration-300 flex items-center justify-center group transform hover:scale-[1.02]">
              <svg class="w-5 h-5 mr-2 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Add New {{ getAddButtonText() }}
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
                  You must be verified as a distributor to manage staff.
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
                  <div class="text-xl font-bold text-blue-700">{{ getTotalStats().pending || 0 }}</div>
                  <div class="text-xs text-blue-600">Pending Review</div>
                </div>
                <div class="text-center p-3 bg-purple-50 rounded-lg">
                  <div class="text-xl font-bold text-purple-700">{{ getTotalStats().inactive || 0 }}</div>
                  <div class="text-xs text-purple-600">Inactive</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Main Content -->
      <div class="lg:col-span-3 space-y-6">
        <!-- Operational Distributors Tab -->
        <div v-if="activeTab === 'operational'" class="space-y-6">
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
                    <input v-model="operationalSearch" type="text" 
                      placeholder="Search distributors..." 
                      class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-64">
                  </div>
                  
                  <!-- Filter Dropdown -->
                  <div class="relative">
                    <button @click="showOperationalFilterDropdown = !showOperationalFilterDropdown"
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
                    <div v-if="showOperationalFilterDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                      <div class="p-2">
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="operationalFilterStatus" type="radio" value="" class="mr-2">
                          <span class="text-sm">All Status</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="operationalFilterStatus" type="radio" value="active" class="mr-2">
                          <span class="text-sm text-green-600">● Active</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="operationalFilterStatus" type="radio" value="pending" class="mr-2">
                          <span class="text-sm text-yellow-600">● Pending</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="operationalFilterStatus" type="radio" value="inactive" class="mr-2">
                          <span class="text-sm text-red-600">● Inactive</span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Create Operational Distributor Wizard -->
            <div v-if="showOperationalForm && isVerifiedDistributor" class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
              <!-- Wizard Progress -->
              <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Operational Distributor
                  </h3>
                  <button @click="cancelOperationalForm" class="text-gray-500 hover:text-gray-700 transition-colors">
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
                         :style="{ width: `${((operationalWizardStep - 1) / 3) * 100}%` }"></div>
                    
                    <!-- Step Indicators -->
                    <div class="relative flex justify-between">
                      <button @click="setOperationalWizardStep(1)" 
                        :class="operationalWizardStep >= 1 ? 'text-blue-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none z-10">
                        <div :class="operationalWizardStep >= 1 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="operationalWizardStep > 1" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">1</span>
                        </div>
                        <span class="text-xs font-medium">Personal Info</span>
                      </button>
                      
                      <button @click="setOperationalWizardStep(2)" :disabled="operationalWizardStep < 2"
                        :class="operationalWizardStep >= 2 ? 'text-blue-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="operationalWizardStep >= 2 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="operationalWizardStep > 2" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">2</span>
                        </div>
                        <span class="text-xs font-medium">ID Verification</span>
                      </button>
                      
                      <button @click="setOperationalWizardStep(3)" :disabled="operationalWizardStep < 3"
                        :class="operationalWizardStep >= 3 ? 'text-blue-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="operationalWizardStep >= 3 ? 'bg-blue-500 border-blue-500' : 'bg-white border-gray-300'" 
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
                <div v-if="operationalWizardStep === 1" class="animate-fade-in">
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
                          <input v-model="operationalForm.first_name" type="text" 
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
                          <input v-model="operationalForm.last_name" type="text" 
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
                          <input v-model="operationalForm.email" type="email" 
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
                          <input v-model="operationalForm.phone" type="tel" 
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
                          <textarea v-model="operationalForm.address" rows="3" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400 resize-none"
                            placeholder="Complete address including city and region"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 2: ID Verification -->
                <div v-if="operationalWizardStep === 2" class="animate-fade-in">
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
                          <select v-model="operationalForm.valid_id_type" 
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
                          <input v-model="operationalForm.id_number" type="text" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                            placeholder="Enter ID number">
                        </div>
                      </div>
                      
                      <div class="md:col-span-2 space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Photo of Valid ID <span class="text-red-500">*</span>
                        </label>
                        <div @click="triggerFileInput('operational', 'valid_id_photo')" 
                          @dragover.prevent @drop.prevent="handleFileDrop($event, 'operational', 'valid_id_photo')"
                          :class="operationalForm.valid_id_photo ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-blue-400'"
                          class="cursor-pointer p-6 border-2 border-dashed rounded-xl transition-all duration-300 group hover:shadow-md">
                          <div class="space-y-4 text-center">
                            <div class="relative inline-block">
                              <svg class="mx-auto h-16 w-16 text-gray-400 group-hover:text-blue-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                              <div v-if="operationalForm.valid_id_photo" class="absolute -top-2 -right-2">
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
                              <p v-if="operationalForm.valid_id_photo" class="text-sm text-green-600 mt-3 font-medium truncate max-w-xs mx-auto">
                                ✓ {{ operationalForm.valid_id_photo.name }}
                              </p>
                            </div>
                          </div>
                        </div>
                        <input ref="operational_valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                          @change="handleFileChange($event, 'operational', 'valid_id_photo')">
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 3: Account Setup -->
                <div v-if="operationalWizardStep === 3" class="animate-fade-in">
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
                          <input v-model="operationalForm.password" :type="showOperationalPassword ? 'text' : 'password'" 
                            class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                            placeholder="Create secure password">
                          <button @click="showOperationalPassword = !showOperationalPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path v-if="showOperationalPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                              <path v-if="!showOperationalPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                          </button>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                          <h5 class="text-sm font-medium text-gray-700 mb-2">Password Requirements</h5>
                          <div class="space-y-2">
                            <div v-for="requirement in operationalPasswordRequirements" :key="requirement.text" 
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
                          <input v-model="operationalForm.password_confirmation" :type="showOperationalConfirmPassword ? 'text' : 'password'" 
                            class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 group-hover:border-blue-400"
                            placeholder="Confirm your password">
                          <button @click="showOperationalConfirmPassword = !showOperationalConfirmPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path v-if="showOperationalConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                              <path v-if="!showOperationalConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                          </button>
                        </div>
                        
                        <!-- Password Match Indicator -->
                        <div v-if="operationalForm.password && operationalForm.password_confirmation" 
                          class="mt-4 p-4 rounded-lg border transition-all duration-300"
                          :class="operationalPasswordMatch ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                          <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" :class="operationalPasswordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path v-if="operationalPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                              <path v-if="!operationalPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span :class="operationalPasswordMatch ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                              {{ operationalPasswordMatch ? 'Passwords match perfectly!' : 'Passwords do not match' }}
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
                        <div class="font-medium">{{ operationalForm.first_name }} {{ operationalForm.last_name }}</div>
                        <div class="text-gray-600">Email:</div>
                        <div class="font-medium">{{ operationalForm.email || 'Not set' }}</div>
                        <div class="text-gray-600">Phone:</div>
                        <div class="font-medium">{{ operationalForm.phone || 'Not set' }}</div>
                        <div class="text-gray-600">ID Type:</div>
                        <div class="font-medium">{{ operationalForm.valid_id_type || 'Not set' }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Wizard Navigation -->
                <div class="flex justify-between pt-6">
                  <button @click="previousOperationalStep" 
                    v-if="operationalWizardStep > 1"
                    class="px-6 py-3 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                  </button>
                  <div v-else></div>
                  
                  <button @click="nextOperationalStep" 
                    v-if="operationalWizardStep < 3"
                    :disabled="!isOperationalStepValid"
                    :class="[isOperationalStepValid ? 'bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
                    class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                    Continue
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                  
                  <button @click="createOperationalDistributor" 
                    v-if="operationalWizardStep === 3"
                    :disabled="!isOperationalFormValid || creatingOperational"
                    :class="[isOperationalFormValid && !creatingOperational ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed', creatingOperational ? 'bg-gradient-to-r from-green-400 to-emerald-400 cursor-wait' : '']"
                    class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center justify-center group hover:scale-[1.02] min-w-[160px]">
                    <svg v-if="!creatingOperational" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <svg v-if="creatingOperational" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ creatingOperational ? 'Creating...' : 'Create Distributor' }}
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
                  <tr v-for="distributor in filteredOperationalDistributors" :key="distributor.id" 
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
                        <button @click="viewOperationalDistributor(distributor.id)" 
                          class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                          title="View Details">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                        <button v-if="distributor.status !== 'active'" @click="activateOperationalDistributor(distributor.id)" 
                          class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="Activate">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          </svg>
                        </button>
                        <button v-if="distributor.status === 'active'" @click="deactivateOperationalDistributor(distributor.id)" 
                          class="p-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                          title="Deactivate">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                          </svg>
                        </button>
                        <button @click="deleteOperationalDistributor(distributor.id)" 
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
                  <tr v-if="filteredOperationalDistributors.length === 0">
                    <td colspan="5" class="px-6 py-12 text-center">
                      <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No operational distributors found</h3>
                        <p class="text-gray-500 mb-4">Get started by creating your first operational distributor.</p>
                        <button @click="showOperationalForm = true" 
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
            <div v-if="operationalPagination && filteredOperationalDistributors.length > 0" 
              class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-sm text-gray-700">
                  Showing {{ operationalPagination.from }} to {{ operationalPagination.to }} of {{ operationalPagination.total }} distributors
                </div>
                <div class="flex items-center space-x-2">
                  <button @click="changeOperationalPage(operationalPagination.current_page - 1)" 
                    :disabled="operationalPagination.current_page === 1"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                  </button>
                  <div class="flex items-center space-x-1">
                    <button v-for="page in getVisiblePages('operational')" :key="page"
                      @click="changeOperationalPage(page)"
                      :class="[page === operationalPagination.current_page ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100']"
                      class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium">
                      {{ page }}
                    </button>
                  </div>
                  <button @click="changeOperationalPage(operationalPagination.current_page + 1)" 
                    :disabled="operationalPagination.current_page === operationalPagination.last_page"
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

        <!-- HR Managers Tab -->
        <div v-if="activeTab === 'hr'" class="space-y-6">
          <!-- HR Managers Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Card Header -->
            <div class="p-5 md:p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                  <h2 class="text-xl font-bold text-gray-800">HR Managers</h2>
                  <p class="text-gray-600 mt-1">Manage your HR department managers and personnel</p>
                </div>
                <div class="flex items-center space-x-3">
                  <!-- Search -->
                  <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input v-model="hrSearch" type="text" 
                      placeholder="Search HR managers..." 
                      class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 w-full md:w-64">
                  </div>
                  
                  <!-- Filter Dropdown -->
                  <div class="relative">
                    <button @click="showHRFilterDropdown = !showHRFilterDropdown"
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
                    <div v-if="showHRFilterDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                      <div class="p-2">
                        <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</div>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="hrFilterStatus" type="radio" value="" class="mr-2">
                          <span class="text-sm">All Status</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="hrFilterStatus" type="radio" value="active" class="mr-2">
                          <span class="text-sm text-green-600">● Active</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="hrFilterStatus" type="radio" value="pending" class="mr-2">
                          <span class="text-sm text-yellow-600">● Pending</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="hrFilterStatus" type="radio" value="inactive" class="mr-2">
                          <span class="text-sm text-red-600">● Inactive</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="hrFilterStatus" type="radio" value="on_leave" class="mr-2">
                          <span class="text-sm text-blue-600">● On Leave</span>
                        </label>
                        
                        <div class="border-t border-gray-200 mt-2 pt-2">
                          <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Employment Type</div>
                          <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input v-model="hrFilterEmploymentType" type="radio" value="" class="mr-2">
                            <span class="text-sm">All Types</span>
                          </label>
                          <label v-for="type in hrEmploymentTypes" :key="type.value" 
                            class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input v-model="hrFilterEmploymentType" type="radio" :value="type.value" class="mr-2">
                            <span class="text-sm">{{ type.label }}</span>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Create HR Manager Wizard -->
            <div v-if="showHRForm && isVerifiedDistributor" class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
              <!-- Wizard Progress -->
              <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New HR Manager
                  </h3>
                  <button @click="cancelHRForm" class="text-gray-500 hover:text-gray-700 transition-colors">
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
                    <div class="absolute top-1/2 left-0 h-1 bg-green-500 transform -translate-y-1/2 transition-all duration-500" 
                         :style="{ width: `${((hrWizardStep - 1) / 4) * 100}%` }"></div>
                    
                    <!-- Step Indicators -->
                    <div class="relative flex justify-between">
                      <button @click="setHRWizardStep(1)" 
                        :class="hrWizardStep >= 1 ? 'text-green-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none z-10">
                        <div :class="hrWizardStep >= 1 ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="hrWizardStep > 1" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">1</span>
                        </div>
                        <span class="text-xs font-medium">Personal</span>
                      </button>
                      
                      <button @click="setHRWizardStep(2)" :disabled="hrWizardStep < 2"
                        :class="hrWizardStep >= 2 ? 'text-green-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="hrWizardStep >= 2 ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="hrWizardStep > 2" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">2</span>
                        </div>
                        <span class="text-xs font-medium">Employment</span>
                      </button>
                      
                      <button @click="setHRWizardStep(3)" :disabled="hrWizardStep < 3"
                        :class="hrWizardStep >= 3 ? 'text-green-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="hrWizardStep >= 3 ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="hrWizardStep > 3" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">3</span>
                        </div>
                        <span class="text-xs font-medium">Documents</span>
                      </button>
                      
                      <button @click="setHRWizardStep(4)" :disabled="hrWizardStep < 4"
                        :class="hrWizardStep >= 4 ? 'text-green-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="hrWizardStep >= 4 ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span class="font-semibold">4</span>
                        </div>
                        <span class="text-xs font-medium">Account</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Wizard Content -->
              <div class="space-y-6">
                <!-- Step 1: Personal Information -->
                <div v-if="hrWizardStep === 1" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-green-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-green-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Personal Information</h4>
                        <p class="text-gray-600">Enter the HR manager's basic details</p>
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
                          <input v-model="hrForm.first_name" type="text" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400"
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
                          <input v-model="hrForm.last_name" type="text" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400"
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
                          <input v-model="hrForm.email" type="email" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400"
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
                          <input v-model="hrForm.phone" type="tel" 
                            @input="validateHRPhoneNumber"
                            maxlength="11"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400"
                            placeholder="09XXXXXXXXX">
                        </div>
                        <p v-if="hrPhoneError" class="text-xs text-red-500 mt-1 animate-pulse">{{ hrPhoneError }}</p>
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
                          <textarea v-model="hrForm.address" rows="3" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400 resize-none"
                            placeholder="Complete address including city and region"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 2: Employment Information -->
                <div v-if="hrWizardStep === 2" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-green-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-green-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Employment Information</h4>
                        <p class="text-gray-600">Set employment details and compensation</p>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Employment Type <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                          </div>
                          <select v-model="hrForm.employment_type" 
                            class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400 appearance-none bg-white">
                            <option value="">Select Employment Type</option>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="temporary">Temporary</option>
                          </select>
                        </div>
                      </div>
                      
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Hire Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                          </div>
                          <input v-model="hrForm.hire_date" type="date" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400">
                        </div>
                      </div>
                      
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Salary (₱) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                          </div>
                          <input v-model="hrForm.salary" type="number" min="0" step="0.01" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400"
                            placeholder="0.00">
                        </div>
                      </div>
                      
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Position
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                          </div>
                          <input v-model="hrForm.position" type="text" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400 bg-gray-50"
                            placeholder="HR Manager" disabled>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 3: Documents -->
                <div v-if="hrWizardStep === 3" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-green-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-green-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Documents & Verification</h4>
                        <p class="text-gray-600">Upload required documents for verification</p>
                      </div>
                    </div>
                    
                    <div class="space-y-6">
                      <!-- ID Verification -->
                      <div class="space-y-4">
                        <h5 class="font-medium text-gray-800">ID Verification</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              Type of Valid ID <span class="text-red-500">*</span>
                            </label>
                            <select v-model="hrForm.valid_id_type" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
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
                          
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              ID Number <span class="text-red-500">*</span>
                            </label>
                            <input v-model="hrForm.id_number" type="text" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                              placeholder="Enter ID number">
                          </div>
                        </div>
                        
                        <!-- ID Photo Upload -->
                        <div class="space-y-2">
                          <label class="block text-sm font-medium text-gray-700">
                            Photo of Valid ID <span class="text-red-500">*</span>
                          </label>
                          <div @click="triggerFileInput('hr', 'valid_id_photo')" 
                            @dragover.prevent @drop.prevent="handleFileDrop($event, 'hr', 'valid_id_photo')"
                            :class="hrForm.valid_id_photo ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-green-400'"
                            class="cursor-pointer p-6 border-2 border-dashed rounded-xl transition-all duration-300 group hover:shadow-md">
                            <div class="space-y-3 text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-green-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                              <div class="px-4">
                                <div class="flex flex-col sm:flex-row text-sm text-gray-600 justify-center items-center gap-1">
                                  <label class="relative cursor-pointer rounded-md font-medium text-green-600 hover:text-green-500 transition-colors">
                                    <span>Upload ID Photo</span>
                                  </label>
                                  <p class="text-gray-500">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, PDF up to 5MB</p>
                                <p v-if="hrForm.valid_id_photo" class="text-sm text-green-600 mt-2 font-medium truncate max-w-xs mx-auto">
                                  ✓ {{ hrForm.valid_id_photo.name }}
                                </p>
                              </div>
                            </div>
                          </div>
                          <input ref="hr_valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                            @change="handleFileChange($event, 'hr', 'valid_id_photo')">
                        </div>
                      </div>
                      
                      <!-- Additional Documents -->
                      <div class="space-y-4">
                        <h5 class="font-medium text-gray-800">Additional Documents (Optional)</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <!-- Resume -->
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              Resume
                            </label>
                            <div @click="triggerFileInput('hr', 'resume')" 
                              @dragover.prevent @drop.prevent="handleFileDrop($event, 'hr', 'resume')"
                              :class="hrForm.resume ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-green-400'"
                              class="cursor-pointer p-4 border-2 border-dashed rounded-lg transition-all duration-300 group hover:shadow-sm">
                              <div class="space-y-2 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                  <label class="relative cursor-pointer text-sm font-medium text-green-600 hover:text-green-500 transition-colors">
                                    <span>Upload Resume</span>
                                  </label>
                                  <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX up to 5MB</p>
                                  <p v-if="hrForm.resume" class="text-xs text-green-600 mt-1 font-medium truncate">
                                    ✓ {{ hrForm.resume.name }}
                                  </p>
                                </div>
                              </div>
                            </div>
                            <input ref="hr_resume" type="file" class="sr-only" accept=".pdf,.doc,.docx" 
                              @change="handleFileChange($event, 'hr', 'resume')">
                          </div>
                          
                          <!-- Employment Contract -->
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              Employment Contract
                            </label>
                            <div @click="triggerFileInput('hr', 'employment_contract')" 
                              @dragover.prevent @drop.prevent="handleFileDrop($event, 'hr', 'employment_contract')"
                              :class="hrForm.employment_contract ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-green-400'"
                              class="cursor-pointer p-4 border-2 border-dashed rounded-lg transition-all duration-300 group hover:shadow-sm">
                              <div class="space-y-2 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                  <label class="relative cursor-pointer text-sm font-medium text-green-600 hover:text-green-500 transition-colors">
                                    <span>Upload Contract</span>
                                  </label>
                                  <p class="text-xs text-gray-500 mt-1">PDF up to 5MB</p>
                                  <p v-if="hrForm.employment_contract" class="text-xs text-green-600 mt-1 font-medium truncate">
                                    ✓ {{ hrForm.employment_contract.name }}
                                  </p>
                                </div>
                              </div>
                            </div>
                            <input ref="hr_employment_contract" type="file" class="sr-only" accept=".pdf" 
                              @change="handleFileChange($event, 'hr', 'employment_contract')">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 4: Account Setup -->
                <div v-if="hrWizardStep === 4" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-green-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-green-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Account Setup</h4>
                        <p class="text-gray-600">Create secure login credentials for the HR manager</p>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-4">
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
                            <input v-model="hrForm.password" :type="showHRPassword ? 'text' : 'password'" 
                              class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400"
                              placeholder="Create secure password">
                            <button @click="showHRPassword = !showHRPassword" 
                              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="showHRPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                <path v-if="!showHRPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                              </svg>
                            </button>
                          </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                          <h5 class="text-sm font-medium text-gray-700 mb-2">Password Requirements</h5>
                          <div class="space-y-2">
                            <div v-for="requirement in hrPasswordRequirements" :key="requirement.text" 
                              class="flex items-center">
                              <svg class="w-4 h-4 mr-2" :class="requirement.met ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                              </svg>
                              <span :class="requirement.met ? 'text-green-600 font-medium' : 'text-gray-500'" class="text-sm">{{ requirement.text }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="space-y-4">
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
                            <input v-model="hrForm.password_confirmation" :type="showHRConfirmPassword ? 'text' : 'password'" 
                              class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-300 group-hover:border-green-400"
                              placeholder="Confirm your password">
                            <button @click="showHRConfirmPassword = !showHRConfirmPassword" 
                              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="showHRConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                <path v-if="!showHRConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                              </svg>
                            </button>
                          </div>
                        </div>
                        
                        <!-- Password Match Indicator -->
                        <div v-if="hrForm.password && hrForm.password_confirmation" 
                          class="p-4 rounded-lg border transition-all duration-300"
                          :class="hrPasswordMatch ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                          <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" :class="hrPasswordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path v-if="hrPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                              <path v-if="!hrPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span :class="hrPasswordMatch ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                              {{ hrPasswordMatch ? 'Passwords match perfectly!' : 'Passwords do not match' }}
                            </span>
                          </div>
                        </div>
                        
                        <!-- Summary Preview -->
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                          <h5 class="text-sm font-medium text-green-800 mb-2">Summary Preview</h5>
                          <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="text-gray-600">Name:</div>
                            <div class="font-medium">{{ hrForm.first_name }} {{ hrForm.last_name }}</div>
                            <div class="text-gray-600">Email:</div>
                            <div class="font-medium">{{ hrForm.email || 'Not set' }}</div>
                            <div class="text-gray-600">Employment:</div>
                            <div class="font-medium">{{ hrForm.employment_type || 'Not set' }}</div>
                            <div class="text-gray-600">Salary:</div>
                            <div class="font-medium">{{ hrForm.salary ? '₱' + parseFloat(hrForm.salary).toLocaleString() : 'Not set' }}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Wizard Navigation -->
                <div class="flex justify-between pt-6">
                  <button @click="previousHRStep" 
                    v-if="hrWizardStep > 1"
                    class="px-6 py-3 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                  </button>
                  <div v-else></div>
                  
                  <button @click="nextHRStep" 
                    v-if="hrWizardStep < 4"
                    :disabled="!isHRStepValid"
                    :class="[isHRStepValid ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
                    class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                    Continue
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                  
                  <button @click="createHRManager" 
                    v-if="hrWizardStep === 4"
                    :disabled="!isHRFormValid || creatingHR"
                    :class="[isHRFormValid && !creatingHR ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed', creatingHR ? 'bg-gradient-to-r from-green-400 to-emerald-400 cursor-wait' : '']"
                    class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center justify-center group hover:scale-[1.02] min-w-[160px]">
                    <svg v-if="!creatingHR" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <svg v-if="creatingHR" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ creatingHR ? 'Creating...' : 'Create HR Manager' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- HR Managers Table -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">HR Manager</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Employment Details</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="manager in filteredHRManagers" :key="manager.id" 
                    class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="flex-shrink-0">
                          <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">{{ getInitials(manager.full_name) }}</span>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="font-medium text-gray-900">{{ manager.full_name }}</div>
                          <div class="text-sm text-gray-500">
                            {{ manager.employment_type_name }}
                          </div>
                          <div class="text-xs text-gray-400">{{ formatDate(manager.created_at) }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div>
                        <div class="flex items-center text-sm text-gray-900">
                          <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                          </svg>
                          {{ manager.email || 'No email' }}
                        </div>
                        <div class="flex items-center text-sm text-gray-500 mt-1">
                          <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                          </svg>
                          {{ manager.phone || 'No phone' }}
                        </div>
                        <div class="mt-2 space-x-1">
                          <span v-if="manager.has_user_account" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Account
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ manager.position || 'HR Manager' }}</div>
                        <div class="text-sm text-gray-500">Hired: {{ manager.formatted_hire_date || 'N/A' }}</div>
                        <div class="text-sm text-gray-500">Salary: {{ manager.formatted_salary || 'N/A' }}</div>
                        <a v-if="manager.valid_id_photo_url" :href="manager.valid_id_photo_url" target="_blank" 
                          class="inline-flex items-center text-sm text-green-600 hover:text-green-800 mt-1">
                          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                          View ID
                        </a>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="getStatusClasses(manager.status)"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                        <span :class="getStatusDotClass(manager.status)" class="w-2 h-2 rounded-full mr-2"></span>
                        {{ manager.status === 'on_leave' ? 'On Leave' : manager.status | capitalize }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <button @click="viewHRManager(manager.id)" 
                          class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="View Details">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                        <button v-if="manager.status !== 'active'" @click="activateHRManager(manager.id)" 
                          class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="Activate">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          </svg>
                        </button>
                        <button v-if="manager.status === 'active'" @click="deactivateHRManager(manager.id)" 
                          class="p-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                          title="Deactivate">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                          </svg>
                        </button>
                        <button v-if="manager.status !== 'on_leave'" @click="putHRManagerOnLeave(manager.id)" 
                          class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                          title="Put on Leave">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                          </svg>
                        </button>
                        <button @click="deleteHRManager(manager.id)" 
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
                  <tr v-if="filteredHRManagers.length === 0">
                    <td colspan="5" class="px-6 py-12 text-center">
                      <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 1.5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No HR managers found</h3>
                        <p class="text-gray-500 mb-4">Get started by creating your first HR manager.</p>
                        <button @click="showHRForm = true" 
                          :disabled="!isVerifiedDistributor"
                          :class="[isVerifiedDistributor ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
                          class="px-4 py-2 text-white rounded-lg transition-colors">
                          Add New HR Manager
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="hrPagination && filteredHRManagers.length > 0" 
              class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-sm text-gray-700">
                  Showing {{ hrPagination.from }} to {{ hrPagination.to }} of {{ hrPagination.total }} managers
                </div>
                <div class="flex items-center space-x-2">
                  <button @click="changeHRPage(hrPagination.current_page - 1)" 
                    :disabled="hrPagination.current_page === 1"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                  </button>
                  <div class="flex items-center space-x-1">
                    <button v-for="page in getVisiblePages('hr')" :key="page"
                      @click="changeHRPage(page)"
                      :class="[page === hrPagination.current_page ? 'bg-green-500 text-white' : 'text-gray-700 hover:bg-gray-100']"
                      class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium">
                      {{ page }}
                    </button>
                  </div>
                  <button @click="changeHRPage(hrPagination.current_page + 1)" 
                    :disabled="hrPagination.current_page === hrPagination.last_page"
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

        <!-- Finance Managers Tab -->
        <div v-if="activeTab === 'finance'" class="space-y-6">
          <!-- Finance Managers Card -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <!-- Card Header -->
            <div class="p-5 md:p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-50">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                  <h2 class="text-xl font-bold text-gray-800">Finance Managers</h2>
                  <p class="text-gray-600 mt-1">Manage your finance department managers and personnel</p>
                </div>
                <div class="flex items-center space-x-3">
                  <!-- Search -->
                  <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input v-model="financeSearch" type="text" 
                      placeholder="Search finance managers..." 
                      class="pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 w-full md:w-64">
                  </div>
                  
                  <!-- Filter Dropdown -->
                  <div class="relative">
                    <button @click="showFinanceFilterDropdown = !showFinanceFilterDropdown"
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
                    <div v-if="showFinanceFilterDropdown" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                      <div class="p-2">
                        <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</div>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="financeFilterStatus" type="radio" value="" class="mr-2">
                          <span class="text-sm">All Status</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="financeFilterStatus" type="radio" value="active" class="mr-2">
                          <span class="text-sm text-green-600">● Active</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="financeFilterStatus" type="radio" value="pending" class="mr-2">
                          <span class="text-sm text-yellow-600">● Pending</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="financeFilterStatus" type="radio" value="inactive" class="mr-2">
                          <span class="text-sm text-red-600">● Inactive</span>
                        </label>
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                          <input v-model="financeFilterStatus" type="radio" value="on_leave" class="mr-2">
                          <span class="text-sm text-blue-600">● On Leave</span>
                        </label>
                        
                        <div class="border-t border-gray-200 mt-2 pt-2">
                          <div class="px-2 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Employment Type</div>
                          <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input v-model="financeFilterEmploymentType" type="radio" value="" class="mr-2">
                            <span class="text-sm">All Types</span>
                          </label>
                          <label v-for="type in financeEmploymentTypes" :key="type.value" 
                            class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input v-model="financeFilterEmploymentType" type="radio" :value="type.value" class="mr-2">
                            <span class="text-sm">{{ type.label }}</span>
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Create Finance Manager Wizard -->
            <div v-if="showFinanceForm && isVerifiedDistributor" class="p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-50">
              <!-- Wizard Progress -->
              <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-xl font-bold text-gray-800 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create New Finance Manager
                  </h3>
                  <button @click="cancelFinanceForm" class="text-gray-500 hover:text-gray-700 transition-colors">
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
                    <div class="absolute top-1/2 left-0 h-1 bg-purple-500 transform -translate-y-1/2 transition-all duration-500" 
                         :style="{ width: `${((financeWizardStep - 1) / 4) * 100}%` }"></div>
                    
                    <!-- Step Indicators -->
                    <div class="relative flex justify-between">
                      <button @click="setFinanceWizardStep(1)" 
                        :class="financeWizardStep >= 1 ? 'text-purple-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none z-10">
                        <div :class="financeWizardStep >= 1 ? 'bg-purple-500 border-purple-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="financeWizardStep > 1" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">1</span>
                        </div>
                        <span class="text-xs font-medium">Personal</span>
                      </button>
                      
                      <button @click="setFinanceWizardStep(2)" :disabled="financeWizardStep < 2"
                        :class="financeWizardStep >= 2 ? 'text-purple-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="financeWizardStep >= 2 ? 'bg-purple-500 border-purple-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="financeWizardStep > 2" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">2</span>
                        </div>
                        <span class="text-xs font-medium">Employment</span>
                      </button>
                      
                      <button @click="setFinanceWizardStep(3)" :disabled="financeWizardStep < 3"
                        :class="financeWizardStep >= 3 ? 'text-purple-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="financeWizardStep >= 3 ? 'bg-purple-500 border-purple-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span v-if="financeWizardStep > 3" class="text-white">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </span>
                          <span v-else class="font-semibold">3</span>
                        </div>
                        <span class="text-xs font-medium">Documents</span>
                      </button>
                      
                      <button @click="setFinanceWizardStep(4)" :disabled="financeWizardStep < 4"
                        :class="financeWizardStep >= 4 ? 'text-purple-600' : 'text-gray-400'"
                        class="flex flex-col items-center focus:outline-none disabled:opacity-50 z-10">
                        <div :class="financeWizardStep >= 4 ? 'bg-purple-500 border-purple-500' : 'bg-white border-gray-300'" 
                          class="w-10 h-10 rounded-full border-2 flex items-center justify-center mb-2 transition-all duration-300 relative">
                          <span class="font-semibold">4</span>
                        </div>
                        <span class="text-xs font-medium">Account</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Wizard Content -->
              <div class="space-y-6">
                <!-- Step 1: Personal Information -->
                <div v-if="financeWizardStep === 1" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-purple-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Personal Information</h4>
                        <p class="text-gray-600">Enter the finance manager's basic details</p>
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
                          <input v-model="financeForm.first_name" type="text" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400"
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
                          <input v-model="financeForm.last_name" type="text" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400"
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
                          <input v-model="financeForm.email" type="email" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400"
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
                          <input v-model="financeForm.phone" type="tel" 
                            @input="validateFinancePhoneNumber"
                            maxlength="11"
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400"
                            placeholder="09XXXXXXXXX">
                        </div>
                        <p v-if="financePhoneError" class="text-xs text-red-500 mt-1 animate-pulse">{{ financePhoneError }}</p>
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
                          <textarea v-model="financeForm.address" rows="3" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400 resize-none"
                            placeholder="Complete address including city and region"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 2: Employment Information -->
                <div v-if="financeWizardStep === 2" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-purple-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Employment Information</h4>
                        <p class="text-gray-600">Set employment details and compensation</p>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Employment Type <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                          </div>
                          <select v-model="financeForm.employment_type" 
                            class="w-full px-4 py-3 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400 appearance-none bg-white">
                            <option value="">Select Employment Type</option>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="temporary">Temporary</option>
                          </select>
                        </div>
                      </div>
                      
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Hire Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                          </div>
                          <input v-model="financeForm.hire_date" type="date" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400">
                        </div>
                      </div>
                      
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Salary (₱) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                          </div>
                          <input v-model="financeForm.salary" type="number" min="0" step="0.01" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400"
                            placeholder="0.00">
                        </div>
                      </div>
                      
                      <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                          Position
                        </label>
                        <div class="relative group">
                          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                          </div>
                          <input v-model="financeForm.position" type="text" 
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400 bg-gray-50"
                            placeholder="Finance Manager" disabled>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 3: Documents -->
                <div v-if="financeWizardStep === 3" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-purple-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Documents & Verification</h4>
                        <p class="text-gray-600">Upload required documents for verification</p>
                      </div>
                    </div>
                    
                    <div class="space-y-6">
                      <!-- ID Verification -->
                      <div class="space-y-4">
                        <h5 class="font-medium text-gray-800">ID Verification</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              Type of Valid ID <span class="text-red-500">*</span>
                            </label>
                            <select v-model="financeForm.valid_id_type" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
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
                          
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              ID Number <span class="text-red-500">*</span>
                            </label>
                            <input v-model="financeForm.id_number" type="text" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                              placeholder="Enter ID number">
                          </div>
                        </div>
                        
                        <!-- ID Photo Upload -->
                        <div class="space-y-2">
                          <label class="block text-sm font-medium text-gray-700">
                            Photo of Valid ID <span class="text-red-500">*</span>
                          </label>
                          <div @click="triggerFileInput('finance', 'valid_id_photo')" 
                            @dragover.prevent @drop.prevent="handleFileDrop($event, 'finance', 'valid_id_photo')"
                            :class="financeForm.valid_id_photo ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-purple-400'"
                            class="cursor-pointer p-6 border-2 border-dashed rounded-xl transition-all duration-300 group hover:shadow-md">
                            <div class="space-y-3 text-center">
                              <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-purple-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                              </svg>
                              <div class="px-4">
                                <div class="flex flex-col sm:flex-row text-sm text-gray-600 justify-center items-center gap-1">
                                  <label class="relative cursor-pointer rounded-md font-medium text-purple-600 hover:text-purple-500 transition-colors">
                                    <span>Upload ID Photo</span>
                                  </label>
                                  <p class="text-gray-500">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">PNG, JPG, PDF up to 5MB</p>
                                <p v-if="financeForm.valid_id_photo" class="text-sm text-green-600 mt-2 font-medium truncate max-w-xs mx-auto">
                                  ✓ {{ financeForm.valid_id_photo.name }}
                                </p>
                              </div>
                            </div>
                          </div>
                          <input ref="finance_valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                            @change="handleFileChange($event, 'finance', 'valid_id_photo')">
                        </div>
                      </div>
                      
                      <!-- Additional Documents -->
                      <div class="space-y-4">
                        <h5 class="font-medium text-gray-800">Additional Documents (Optional)</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <!-- Resume -->
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              Resume
                            </label>
                            <div @click="triggerFileInput('finance', 'resume')" 
                              @dragover.prevent @drop.prevent="handleFileDrop($event, 'finance', 'resume')"
                              :class="financeForm.resume ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-purple-400'"
                              class="cursor-pointer p-4 border-2 border-dashed rounded-lg transition-all duration-300 group hover:shadow-sm">
                              <div class="space-y-2 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                  <label class="relative cursor-pointer text-sm font-medium text-purple-600 hover:text-purple-500 transition-colors">
                                    <span>Upload Resume</span>
                                  </label>
                                  <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX up to 5MB</p>
                                  <p v-if="financeForm.resume" class="text-xs text-green-600 mt-1 font-medium truncate">
                                    ✓ {{ financeForm.resume.name }}
                                  </p>
                                </div>
                              </div>
                            </div>
                            <input ref="finance_resume" type="file" class="sr-only" accept=".pdf,.doc,.docx" 
                              @change="handleFileChange($event, 'finance', 'resume')">
                          </div>
                          
                          <!-- Employment Contract -->
                          <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">
                              Employment Contract
                            </label>
                            <div @click="triggerFileInput('finance', 'employment_contract')" 
                              @dragover.prevent @drop.prevent="handleFileDrop($event, 'finance', 'employment_contract')"
                              :class="financeForm.employment_contract ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-purple-400'"
                              class="cursor-pointer p-4 border-2 border-dashed rounded-lg transition-all duration-300 group hover:shadow-sm">
                              <div class="space-y-2 text-center">
                                <svg class="mx-auto h-8 w-8 text-gray-400 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <div>
                                  <label class="relative cursor-pointer text-sm font-medium text-purple-600 hover:text-purple-500 transition-colors">
                                    <span>Upload Contract</span>
                                  </label>
                                  <p class="text-xs text-gray-500 mt-1">PDF up to 5MB</p>
                                  <p v-if="financeForm.employment_contract" class="text-xs text-green-600 mt-1 font-medium truncate">
                                    ✓ {{ financeForm.employment_contract.name }}
                                  </p>
                                </div>
                              </div>
                            </div>
                            <input ref="finance_employment_contract" type="file" class="sr-only" accept=".pdf" 
                              @change="handleFileChange($event, 'finance', 'employment_contract')">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 4: Account Setup -->
                <div v-if="financeWizardStep === 4" class="animate-fade-in">
                  <div class="bg-white rounded-xl shadow-lg p-6 border border-purple-100">
                    <div class="flex items-center mb-6">
                      <div class="p-3 bg-purple-100 rounded-lg mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                      </div>
                      <div>
                        <h4 class="text-lg font-semibold text-gray-800">Account Setup</h4>
                        <p class="text-gray-600">Create secure login credentials for the finance manager</p>
                      </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div class="space-y-4">
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
                            <input v-model="financeForm.password" :type="showFinancePassword ? 'text' : 'password'" 
                              class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400"
                              placeholder="Create secure password">
                            <button @click="showFinancePassword = !showFinancePassword" 
                              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="showFinancePassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                <path v-if="!showFinancePassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                              </svg>
                            </button>
                          </div>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                          <h5 class="text-sm font-medium text-gray-700 mb-2">Password Requirements</h5>
                          <div class="space-y-2">
                            <div v-for="requirement in financePasswordRequirements" :key="requirement.text" 
                              class="flex items-center">
                              <svg class="w-4 h-4 mr-2" :class="requirement.met ? 'text-green-500' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                              </svg>
                              <span :class="requirement.met ? 'text-green-600 font-medium' : 'text-gray-500'" class="text-sm">{{ requirement.text }}</span>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <div class="space-y-4">
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
                            <input v-model="financeForm.password_confirmation" :type="showFinanceConfirmPassword ? 'text' : 'password'" 
                              class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-300 group-hover:border-purple-400"
                              placeholder="Confirm your password">
                            <button @click="showFinanceConfirmPassword = !showFinanceConfirmPassword" 
                              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="showFinanceConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                <path v-if="!showFinanceConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                              </svg>
                            </button>
                          </div>
                        </div>
                        
                        <!-- Password Match Indicator -->
                        <div v-if="financeForm.password && financeForm.password_confirmation" 
                          class="p-4 rounded-lg border transition-all duration-300"
                          :class="financePasswordMatch ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                          <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" :class="financePasswordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path v-if="financePasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                              <path v-if="!financePasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span :class="financePasswordMatch ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                              {{ financePasswordMatch ? 'Passwords match perfectly!' : 'Passwords do not match' }}
                            </span>
                          </div>
                        </div>
                        
                        <!-- Summary Preview -->
                        <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                          <h5 class="text-sm font-medium text-purple-800 mb-2">Summary Preview</h5>
                          <div class="grid grid-cols-2 gap-2 text-sm">
                            <div class="text-gray-600">Name:</div>
                            <div class="font-medium">{{ financeForm.first_name }} {{ financeForm.last_name }}</div>
                            <div class="text-gray-600">Email:</div>
                            <div class="font-medium">{{ financeForm.email || 'Not set' }}</div>
                            <div class="text-gray-600">Employment:</div>
                            <div class="font-medium">{{ financeForm.employment_type || 'Not set' }}</div>
                            <div class="text-gray-600">Salary:</div>
                            <div class="font-medium">{{ financeForm.salary ? '₱' + parseFloat(financeForm.salary).toLocaleString() : 'Not set' }}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Wizard Navigation -->
                <div class="flex justify-between pt-6">
                  <button @click="previousFinanceStep" 
                    v-if="financeWizardStep > 1"
                    class="px-6 py-3 text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                    <svg class="w-5 h-5 mr-2 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                  </button>
                  <div v-else></div>
                  
                  <button @click="nextFinanceStep" 
                    v-if="financeWizardStep < 4"
                    :disabled="!isFinanceStepValid"
                    :class="[isFinanceStepValid ? 'bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
                    class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center group hover:scale-[1.02]">
                    Continue
                    <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </button>
                  
                  <button @click="createFinanceManager" 
                    v-if="financeWizardStep === 4"
                    :disabled="!isFinanceFormValid || creatingFinance"
                    :class="[isFinanceFormValid && !creatingFinance ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed', creatingFinance ? 'bg-gradient-to-r from-green-400 to-emerald-400 cursor-wait' : '']"
                    class="px-6 py-3 text-white rounded-lg transition-all duration-300 flex items-center justify-center group hover:scale-[1.02] min-w-[160px]">
                    <svg v-if="!creatingFinance" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <svg v-if="creatingFinance" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ creatingFinance ? 'Creating...' : 'Create Finance Manager' }}
                  </button>
                </div>
              </div>
            </div>

            <!-- Finance Managers Table -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Finance Manager</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Employment Details</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="manager in filteredFinanceManagers" :key="manager.id" 
                    class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="flex-shrink-0">
                          <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">{{ getInitials(manager.full_name) }}</span>
                          </div>
                        </div>
                        <div class="ml-4">
                          <div class="font-medium text-gray-900">{{ manager.full_name }}</div>
                          <div class="text-sm text-gray-500">
                            {{ manager.employment_type_name }}
                          </div>
                          <div class="text-xs text-gray-400">{{ formatDate(manager.created_at) }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div>
                        <div class="flex items-center text-sm text-gray-900">
                          <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                          </svg>
                          {{ manager.email || 'No email' }}
                        </div>
                        <div class="flex items-center text-sm text-gray-500 mt-1">
                          <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                          </svg>
                          {{ manager.phone || 'No phone' }}
                        </div>
                        <div class="mt-2 space-x-1">
                          <span v-if="manager.has_user_account" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Account
                          </span>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ manager.position || 'Finance Manager' }}</div>
                        <div class="text-sm text-gray-500">Hired: {{ manager.formatted_hire_date || 'N/A' }}</div>
                        <div class="text-sm text-gray-500">Salary: {{ manager.formatted_salary || 'N/A' }}</div>
                        <a v-if="manager.valid_id_photo_url" :href="manager.valid_id_photo_url" target="_blank" 
                          class="inline-flex items-center text-sm text-purple-600 hover:text-purple-800 mt-1">
                          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                          View ID
                        </a>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="getStatusClasses(manager.status)"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold">
                        <span :class="getStatusDotClass(manager.status)" class="w-2 h-2 rounded-full mr-2"></span>
                        {{ manager.status === 'on_leave' ? 'On Leave' : manager.status | capitalize }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <button @click="viewFinanceManager(manager.id)" 
                          class="p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors"
                          title="View Details">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                        <button v-if="manager.status !== 'active'" @click="activateFinanceManager(manager.id)" 
                          class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="Activate">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                          </svg>
                        </button>
                        <button v-if="manager.status === 'active'" @click="deactivateFinanceManager(manager.id)" 
                          class="p-2 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                          title="Deactivate">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                          </svg>
                        </button>
                        <button v-if="manager.status !== 'on_leave'" @click="putFinanceManagerOnLeave(manager.id)" 
                          class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                          title="Put on Leave">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                          </svg>
                        </button>
                        <button @click="deleteFinanceManager(manager.id)" 
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
                  <tr v-if="filteredFinanceManagers.length === 0">
                    <td colspan="5" class="px-6 py-12 text-center">
                      <div class="flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No finance managers found</h3>
                        <p class="text-gray-500 mb-4">Get started by creating your first finance manager.</p>
                        <button @click="showFinanceForm = true" 
                          :disabled="!isVerifiedDistributor"
                          :class="[isVerifiedDistributor ? 'bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed']"
                          class="px-4 py-2 text-white rounded-lg transition-colors">
                          Add New Finance Manager
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="financePagination && filteredFinanceManagers.length > 0" 
              class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="text-sm text-gray-700">
                  Showing {{ financePagination.from }} to {{ financePagination.to }} of {{ financePagination.total }} managers
                </div>
                <div class="flex items-center space-x-2">
                  <button @click="changeFinancePage(financePagination.current_page - 1)" 
                    :disabled="financePagination.current_page === 1"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous
                  </button>
                  <div class="flex items-center space-x-1">
                    <button v-for="page in getVisiblePages('finance')" :key="page"
                      @click="changeFinancePage(page)"
                      :class="[page === financePagination.current_page ? 'bg-purple-500 text-white' : 'text-gray-700 hover:bg-gray-100']"
                      class="w-10 h-10 flex items-center justify-center rounded-lg text-sm font-medium">
                      {{ page }}
                    </button>
                  </div>
                  <button @click="changeFinancePage(financePagination.current_page + 1)" 
                    :disabled="financePagination.current_page === financePagination.last_page"
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

        <!-- Department Insights -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Recent Activity -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-800">Recent Activity</h3>
              <div class="p-2 bg-purple-100 rounded-lg">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
            </div>
            <div class="space-y-3">
              <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start">
                <div :class="activity.iconBg" class="p-2 rounded-lg mr-3">
                  <svg class="w-4 h-4" :class="activity.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="activity.icon" />
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-800">{{ activity.message }}</p>
                  <p class="text-xs text-gray-500 mt-1">{{ activity.time }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Stats -->
          <div :class="getOverviewGradient()" class="rounded-xl shadow-lg p-5 text-white">
            <h3 class="font-semibold mb-4">{{ getOverviewTitle() }} Overview</h3>
            <div class="space-y-4">
              <div>
                <div class="text-3xl font-bold">{{ getOverviewTotal() }}</div>
                <div class="text-sm opacity-90">Total {{ getOverviewTitle() }}</div>
              </div>
              <div>
                <div class="text-2xl font-bold">{{ getOverviewActive() }}</div>
                <div class="text-sm opacity-90">Active Now</div>
              </div>
              <div class="pt-4 border-t border-white/20">
                <div class="text-sm opacity-90">Last Updated</div>
                <div class="font-medium">{{ formatDate(new Date()) }}</div>
              </div>
            </div>
          </div>

          <!-- Status Distribution -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-gray-800">Status Distribution</h3>
              <div :class="getStatusIconBg()" class="p-2 rounded-lg">
                <svg :class="getStatusIconColor()" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                </svg>
              </div>
            </div>
            <div class="space-y-3">
              <div v-for="status in getStatusDistribution()" :key="status.name" class="flex items-center justify-between">
                <div class="flex items-center">
                  <div :class="status.color" class="w-3 h-3 rounded-full mr-2"></div>
                  <span class="text-sm text-gray-700">{{ status.name }}</span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-semibold text-gray-900 mr-2">{{ status.count }}</span>
                  <span class="text-xs text-gray-500">({{ status.percentage }}%)</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script src="../distributor/script/coreDepartments.js"></script>

<style scoped>
.core-departments {
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

/* Gradient borders */
.gradient-border {
  position: relative;
}

.gradient-border::before {
  content: '';
  position: absolute;
  top: -2px;
  left: -2px;
  right: -2px;
  bottom: -2px;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6, #ec4899);
  border-radius: 12px;
  z-index: -1;
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

/* File upload hover effects */
.file-upload:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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

/* Input with icon improvements */
.input-with-icon {
  padding-left: 2.5rem;
  padding-right: 1rem;
}

.input-with-icon-right {
  padding-right: 2.5rem;
  padding-left: 1rem;
}

/* File upload text improvements */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.max-w-xs {
  max-width: 20rem;
}

@media (max-width: 640px) {
  .max-w-xs {
    max-width: 16rem;
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
}
</style>

<style>
/* Toastify Styles */
.toastify {
  z-index: 99999 !important;
  position: fixed !important;
  top: 20px !important;
  right: 20px !important;
  max-width: 400px !important;
}

.toastify.on {
  opacity: 1 !important;
  transform: translateX(0) !important;
}

@media (max-width: 768px) {
  .toastify {
    max-width: 90% !important;
    left: 5% !important;
    right: 5% !important;
    text-align: center !important;
  }
}

/* Wizard step animations */
.wizard-step-enter-active, .wizard-step-leave-active {
  transition: all 0.3s ease;
}
.wizard-step-enter-from, .wizard-step-leave-to {
  opacity: 0;
  transform: translateX(20px);
}

/* Ensure select dropdowns are visible */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}

/* Improve input padding for mobile */
@media (max-width: 640px) {
  .pl-10 {
    padding-left: 2.5rem;
  }
  
  .pr-10 {
    padding-right: 2.5rem;
  }
  
  .pl-10.pr-4 {
    padding-left: 2.5rem;
    padding-right: 1rem;
  }
}


</style>