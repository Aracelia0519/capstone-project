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

            <!-- Create Operational Distributor Form -->
            <div v-if="showOperationalForm && isVerifiedDistributor" class="p-5 md:p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create New Operational Distributor
                </h3>
                <button @click="showOperationalForm = false" class="text-gray-500 hover:text-gray-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div class="space-y-6">
                <!-- Personal Information -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Personal Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        First Name <span class="text-red-500">*</span>
                      </label>
                      <input v-model="operationalForm.first_name" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Enter first name">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name <span class="text-red-500">*</span>
                      </label>
                      <input v-model="operationalForm.last_name" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Enter last name">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                      </label>
                      <input v-model="operationalForm.email" type="email" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="email@example.com">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 ml-1">(11 digits starting with 0)</span>
                      </label>
                      <div class="relative">
                        <input v-model="operationalForm.phone" type="tel" 
                          @input="validatePhoneNumber"
                          maxlength="11"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                          placeholder="09XXXXXXXXX">
                      </div>
                      <p v-if="phoneError" class="text-xs text-red-500 mt-1">{{ phoneError }}</p>
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                      <textarea v-model="operationalForm.address" rows="2" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                        placeholder="Complete address including city and region"></textarea>
                    </div>
                  </div>
                </div>

                <!-- ID Verification -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">ID Verification</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Type of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <select v-model="operationalForm.valid_id_type" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
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
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                        placeholder="Enter ID number">
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Photo of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <div @click="triggerFileInput('operational', 'valid_id_photo')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'operational', 'valid_id_photo')"
                        class="cursor-pointer flex justify-center px-4 py-8 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 transition-colors bg-gray-50">
                        <div class="space-y-2 text-center">
                          <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500">
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
                        @change="handleFileChange($event, 'operational', 'valid_id_photo')">
                    </div>
                  </div>
                </div>

                <!-- Account Setup -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Account Setup</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                      </label>
                      <div class="relative">
                        <input v-model="operationalForm.password" :type="showOperationalPassword ? 'text' : 'password'" 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                          placeholder="Create password">
                        <button @click="showOperationalPassword = !showOperationalPassword" 
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                          placeholder="Confirm password">
                        <button @click="showOperationalConfirmPassword = !showOperationalConfirmPassword" 
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4">
                  <button @click="createOperationalDistributor" 
                    :disabled="!isOperationalFormValid || creatingOperational"
                    :class="[isOperationalFormValid && !creatingOperational ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed', creatingOperational ? 'bg-gradient-to-r from-green-400 to-emerald-400 cursor-wait' : '']"
                    class="flex-1 px-6 py-3 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.02] flex items-center justify-center">
                    <svg v-if="!creatingOperational" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <svg v-if="creatingOperational" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ creatingOperational ? 'Creating...' : 'Create Distributor' }}
                  </button>
                  <button @click="cancelOperationalForm" 
                    class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
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

            <!-- Create HR Manager Form -->
            <div v-if="showHRForm && isVerifiedDistributor" class="p-5 md:p-6 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create New HR Manager
                </h3>
                <button @click="showHRForm = false" class="text-gray-500 hover:text-gray-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div class="space-y-6">
                <!-- Personal Information -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Personal Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        First Name <span class="text-red-500">*</span>
                      </label>
                      <input v-model="hrForm.first_name" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Enter first name">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name <span class="text-red-500">*</span>
                      </label>
                      <input v-model="hrForm.last_name" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Enter last name">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                      </label>
                      <input v-model="hrForm.email" type="email" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="email@example.com">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 ml-1">(11 digits starting with 0)</span>
                      </label>
                      <div class="relative">
                        <input v-model="hrForm.phone" type="tel" 
                          @input="validateHRPhoneNumber"
                          maxlength="11"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                          placeholder="09XXXXXXXXX">
                      </div>
                      <p v-if="hrPhoneError" class="text-xs text-red-500 mt-1">{{ hrPhoneError }}</p>
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                      <textarea v-model="hrForm.address" rows="2" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors resize-none"
                        placeholder="Complete address including city and region"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Employment Information -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Employment Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Employment Type <span class="text-red-500">*</span>
                      </label>
                      <select v-model="hrForm.employment_type" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                        <option value="">Select Employment Type</option>
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="temporary">Temporary</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hire Date <span class="text-red-500">*</span>
                      </label>
                      <input v-model="hrForm.hire_date" type="date" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Salary (₱) <span class="text-red-500">*</span>
                      </label>
                      <input v-model="hrForm.salary" type="number" min="0" step="0.01" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="0.00">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Position
                      </label>
                      <input v-model="hrForm.position" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="HR Manager" disabled>
                    </div>
                  </div>
                </div>

                <!-- ID Verification -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">ID Verification</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Type of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <select v-model="hrForm.valid_id_type" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
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
                      <input v-model="hrForm.id_number" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                        placeholder="Enter ID number">
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Photo of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <div @click="triggerFileInput('hr', 'valid_id_photo')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'hr', 'valid_id_photo')"
                        class="cursor-pointer flex justify-center px-4 py-8 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-400 transition-colors bg-gray-50">
                        <div class="space-y-2 text-center">
                          <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500">
                              <span>Upload ID Photo</span>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                          <p v-if="hrForm.valid_id_photo" class="text-sm text-green-600 mt-2">
                            ✓ File selected: {{ hrForm.valid_id_photo.name }}
                          </p>
                        </div>
                      </div>
                      <input ref="hr_valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                        @change="handleFileChange($event, 'hr', 'valid_id_photo')">
                    </div>
                  </div>
                </div>

                <!-- Additional Documents -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Additional Documents</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Resume (Optional)
                      </label>
                      <div @click="triggerFileInput('hr', 'resume')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'hr', 'resume')"
                        class="cursor-pointer flex justify-center px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-400 transition-colors bg-gray-50">
                        <div class="space-y-2 text-center">
                          <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                          <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500">
                              <span>Upload Resume</span>
                            </label>
                          </div>
                          <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                          <p v-if="hrForm.resume" class="text-sm text-green-600 mt-2">
                            ✓ File selected: {{ hrForm.resume.name }}
                          </p>
                        </div>
                      </div>
                      <input ref="hr_resume" type="file" class="sr-only" accept=".pdf,.doc,.docx" 
                        @change="handleFileChange($event, 'hr', 'resume')">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Employment Contract (Optional)
                      </label>
                      <div @click="triggerFileInput('hr', 'employment_contract')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'hr', 'employment_contract')"
                        class="cursor-pointer flex justify-center px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-green-400 transition-colors bg-gray-50">
                        <div class="space-y-2 text-center">
                          <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                          <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500">
                              <span>Upload Contract</span>
                            </label>
                          </div>
                          <p class="text-xs text-gray-500">PDF up to 5MB</p>
                          <p v-if="hrForm.employment_contract" class="text-sm text-green-600 mt-2">
                            ✓ File selected: {{ hrForm.employment_contract.name }}
                          </p>
                        </div>
                      </div>
                      <input ref="hr_employment_contract" type="file" class="sr-only" accept=".pdf" 
                        @change="handleFileChange($event, 'hr', 'employment_contract')">
                    </div>
                  </div>
                </div>

                <!-- Account Setup -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Account Setup</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                      </label>
                      <div class="relative">
                        <input v-model="hrForm.password" :type="showHRPassword ? 'text' : 'password'" 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                          placeholder="Create password">
                        <button @click="showHRPassword = !showHRPassword" 
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="showHRPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            <path v-if="!showHRPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                      </div>
                      <div class="mt-2 space-y-1">
                        <div v-for="requirement in hrPasswordRequirements" :key="requirement.text" 
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
                        <input v-model="hrForm.password_confirmation" :type="showHRConfirmPassword ? 'text' : 'password'" 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                          placeholder="Confirm password">
                        <button @click="showHRConfirmPassword = !showHRConfirmPassword" 
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="showHRConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            <path v-if="!showHRConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                      </div>
                      <div class="mt-2">
                        <div v-if="hrForm.password && hrForm.password_confirmation" class="flex items-center text-xs">
                          <svg class="w-3 h-3 mr-1" :class="hrPasswordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="hrPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            <path v-if="!hrPasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                          <span :class="hrPasswordMatch ? 'text-green-600' : 'text-red-600'">
                            {{ hrPasswordMatch ? 'Passwords match' : 'Passwords do not match' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4">
                  <button @click="createHRManager" 
                    :disabled="!isHRFormValid || creatingHR"
                    :class="[isHRFormValid && !creatingHR ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed', creatingHR ? 'bg-gradient-to-r from-green-400 to-emerald-400 cursor-wait' : '']"
                    class="flex-1 px-6 py-3 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.02] flex items-center justify-center">
                    <svg v-if="!creatingHR" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <svg v-if="creatingHR" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ creatingHR ? 'Creating...' : 'Create HR Manager' }}
                  </button>
                  <button @click="cancelHRForm" 
                    class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
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

            <!-- Create Finance Manager Form -->
            <div v-if="showFinanceForm && isVerifiedDistributor" class="p-5 md:p-6 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-purple-50">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Create New Finance Manager
                </h3>
                <button @click="showFinanceForm = false" class="text-gray-500 hover:text-gray-700">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div class="space-y-6">
                <!-- Personal Information -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Personal Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        First Name <span class="text-red-500">*</span>
                      </label>
                      <input v-model="financeForm.first_name" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                        placeholder="Enter first name">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name <span class="text-red-500">*</span>
                      </label>
                      <input v-model="financeForm.last_name" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                        placeholder="Enter last name">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Email Address <span class="text-red-500">*</span>
                      </label>
                      <input v-model="financeForm.email" type="email" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                        placeholder="email@example.com">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Phone Number <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-500 ml-1">(11 digits starting with 0)</span>
                      </label>
                      <div class="relative">
                        <input v-model="financeForm.phone" type="tel" 
                          @input="validateFinancePhoneNumber"
                          maxlength="11"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                          placeholder="09XXXXXXXXX">
                      </div>
                      <p v-if="financePhoneError" class="text-xs text-red-500 mt-1">{{ financePhoneError }}</p>
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                      <textarea v-model="financeForm.address" rows="2" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors resize-none"
                        placeholder="Complete address including city and region"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Employment Information -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Employment Information</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Employment Type <span class="text-red-500">*</span>
                      </label>
                      <select v-model="financeForm.employment_type" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                        <option value="">Select Employment Type</option>
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="temporary">Temporary</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Hire Date <span class="text-red-500">*</span>
                      </label>
                      <input v-model="financeForm.hire_date" type="date" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Salary (₱) <span class="text-red-500">*</span>
                      </label>
                      <input v-model="financeForm.salary" type="number" min="0" step="0.01" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                        placeholder="0.00">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Position
                      </label>
                      <input v-model="financeForm.position" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                        placeholder="Finance Manager" disabled>
                    </div>
                  </div>
                </div>

                <!-- ID Verification -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">ID Verification</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Type of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <select v-model="financeForm.valid_id_type" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
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
                      <input v-model="financeForm.id_number" type="text" 
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                        placeholder="Enter ID number">
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Photo of Valid ID <span class="text-red-500">*</span>
                      </label>
                      <div @click="triggerFileInput('finance', 'valid_id_photo')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'finance', 'valid_id_photo')"
                        class="cursor-pointer flex justify-center px-4 py-8 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-400 transition-colors bg-gray-50">
                        <div class="space-y-2 text-center">
                          <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                          </svg>
                          <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500">
                              <span>Upload ID Photo</span>
                            </label>
                            <p class="pl-1">or drag and drop</p>
                          </div>
                          <p class="text-xs text-gray-500">PNG, JPG, PDF up to 5MB</p>
                          <p v-if="financeForm.valid_id_photo" class="text-sm text-green-600 mt-2">
                            ✓ File selected: {{ financeForm.valid_id_photo.name }}
                          </p>
                        </div>
                      </div>
                      <input ref="finance_valid_id_photo" type="file" class="sr-only" accept="image/*,.pdf" 
                        @change="handleFileChange($event, 'finance', 'valid_id_photo')">
                    </div>
                  </div>
                </div>

                <!-- Additional Documents -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Additional Documents</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Resume (Optional)
                      </label>
                      <div @click="triggerFileInput('finance', 'resume')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'finance', 'resume')"
                        class="cursor-pointer flex justify-center px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-400 transition-colors bg-gray-50">
                        <div class="space-y-2 text-center">
                          <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                          <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500">
                              <span>Upload Resume</span>
                            </label>
                          </div>
                          <p class="text-xs text-gray-500">PDF, DOC, DOCX up to 5MB</p>
                          <p v-if="financeForm.resume" class="text-sm text-green-600 mt-2">
                            ✓ File selected: {{ financeForm.resume.name }}
                          </p>
                        </div>
                      </div>
                      <input ref="finance_resume" type="file" class="sr-only" accept=".pdf,.doc,.docx" 
                        @change="handleFileChange($event, 'finance', 'resume')">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Employment Contract (Optional)
                      </label>
                      <div @click="triggerFileInput('finance', 'employment_contract')" 
                        @dragover.prevent @drop.prevent="handleFileDrop($event, 'finance', 'employment_contract')"
                        class="cursor-pointer flex justify-center px-4 py-6 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-400 transition-colors bg-gray-50">
                        <div class="space-y-2 text-center">
                          <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                          </svg>
                          <div class="flex text-sm text-gray-600 justify-center">
                            <label class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500">
                              <span>Upload Contract</span>
                            </label>
                          </div>
                          <p class="text-xs text-gray-500">PDF up to 5MB</p>
                          <p v-if="financeForm.employment_contract" class="text-sm text-green-600 mt-2">
                            ✓ File selected: {{ financeForm.employment_contract.name }}
                          </p>
                        </div>
                      </div>
                      <input ref="finance_employment_contract" type="file" class="sr-only" accept=".pdf" 
                        @change="handleFileChange($event, 'finance', 'employment_contract')">
                    </div>
                  </div>
                </div>

                <!-- Account Setup -->
                <div class="bg-white p-4 rounded-lg border border-gray-200">
                  <h4 class="text-md font-semibold text-gray-800 mb-4 pb-2 border-b border-gray-200">Account Setup</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                      </label>
                      <div class="relative">
                        <input v-model="financeForm.password" :type="showFinancePassword ? 'text' : 'password'" 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                          placeholder="Create password">
                        <button @click="showFinancePassword = !showFinancePassword" 
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="showFinancePassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            <path v-if="!showFinancePassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                      </div>
                      <div class="mt-2 space-y-1">
                        <div v-for="requirement in financePasswordRequirements" :key="requirement.text" 
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
                        <input v-model="financeForm.password_confirmation" :type="showFinanceConfirmPassword ? 'text' : 'password'" 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors"
                          placeholder="Confirm password">
                        <button @click="showFinanceConfirmPassword = !showFinanceConfirmPassword" 
                          class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="showFinanceConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            <path v-if="!showFinanceConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </button>
                      </div>
                      <div class="mt-2">
                        <div v-if="financeForm.password && financeForm.password_confirmation" class="flex items-center text-xs">
                          <svg class="w-3 h-3 mr-1" :class="financePasswordMatch ? 'text-green-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-if="financePasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            <path v-if="!financePasswordMatch" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                          </svg>
                          <span :class="financePasswordMatch ? 'text-green-600' : 'text-red-600'">
                            {{ financePasswordMatch ? 'Passwords match' : 'Passwords do not match' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-4">
                  <button @click="createFinanceManager" 
                    :disabled="!isFinanceFormValid || creatingFinance"
                    :class="[isFinanceFormValid && !creatingFinance ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600' : 'bg-gradient-to-r from-gray-400 to-gray-300 cursor-not-allowed', creatingFinance ? 'bg-gradient-to-r from-green-400 to-emerald-400 cursor-wait' : '']"
                    class="flex-1 px-6 py-3 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.02] flex items-center justify-center">
                    <svg v-if="!creatingFinance" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <svg v-if="creatingFinance" class="w-5 h-5 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ creatingFinance ? 'Creating...' : 'Create Finance Manager' }}
                  </button>
                  <button @click="cancelFinanceForm" 
                    class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Cancel
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

<script src="../distributor/script/coreDepartments.js">
</script>

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
</style>