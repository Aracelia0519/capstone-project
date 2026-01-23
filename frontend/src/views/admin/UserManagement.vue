<template>
  <div class="user-management min-h-screen bg-gray-50 p-6">
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
          <p class="text-gray-600 mt-1">Control system access and manage user roles</p>
        </div>
        <button 
          @click="showAddUserModal = true" 
          class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition-all duration-200 hover:translate-y-[-2px]"
        >
          <i class="fas fa-plus"></i>
          <span>Add New User</span>
        </button>
      </div>
      
      <!-- Quick Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div 
          @click="activeTab = 'all'" 
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
              <i class="fas fa-users text-white text-lg"></i>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.total || 0 }}</p>
              <p class="text-gray-600 text-sm">Total Users</p>
            </div>
          </div>
        </div>
        
        <div 
          @click="activeTab = 'admin'" 
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-red-500 to-pink-600 flex items-center justify-center">
              <i class="fas fa-user-shield text-white text-lg"></i>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.admin || 0 }}</p>
              <p class="text-gray-600 text-sm">Admins</p>
            </div>
          </div>
        </div>
        
        <div 
          @click="activeTab = 'distributor'" 
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center">
              <i class="fas fa-truck text-white text-lg"></i>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.distributor || 0 }}</p>
              <p class="text-gray-600 text-sm">Distributors</p>
            </div>
          </div>
        </div>
        
        <div 
          @click="activeTab = 'service_provider'" 
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-purple-500 to-violet-600 flex items-center justify-center">
              <i class="fas fa-tools text-white text-lg"></i>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.service_provider || 0 }}</p>
              <p class="text-gray-600 text-sm">Service Providers</p>
            </div>
          </div>
        </div>
        
        <div 
          @click="activeTab = 'client'" 
          class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 cursor-pointer"
        >
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 flex items-center justify-center">
              <i class="fas fa-user text-white text-lg"></i>
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ statistics.client || 0 }}</p>
              <p class="text-gray-600 text-sm">Clients</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
      <!-- Tabs -->
      <div class="flex flex-wrap gap-2 mb-6">
        <button 
          v-for="tab in tabs" 
          :key="tab.value"
          @click="activeTab = tab.value" 
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-all duration-200',
            activeTab === tab.value 
              ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-sm' 
              : 'text-gray-700 hover:bg-gray-100'
          ]"
        >
          <i :class="tab.icon"></i>
          <span>{{ tab.label }}</span>
        </button>
      </div>
      
      <!-- Search and Filters -->
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1 relative">
          <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search by name, email, or phone..." 
            @input="debouncedFetchUsers"
            class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
          >
        </div>
        
        <select 
          v-model="statusFilter" 
          @change="fetchUsers"
          class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
        >
          <option value="all">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="pending">Pending</option>
        </select>
        
        <button 
          @click="fetchUsers" 
          class="flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-medium transition-all duration-200"
        >
          <i class="fas fa-sync-alt"></i>
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
      <!-- Table Header -->
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Users</h3>
            <p class="text-gray-500 text-sm mt-1">{{ pagination.total }} users found</p>
          </div>
        </div>
      </div>
      
      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="py-4 px-6 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">User</th>
              <th class="py-4 px-6 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Role</th>
              <th class="py-4 px-6 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Contact</th>
              <th class="py-4 px-6 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Verification</th>
              <th class="py-4 px-6 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Date Added</th>
              <th class="py-4 px-6 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Status</th>
              <th class="py-4 px-6 text-left text-xs font-medium text-gray-600 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <!-- Loading State -->
            <tr v-if="loading">
              <td colspan="7" class="py-8">
                <div class="flex items-center justify-center">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                  <span class="ml-3 text-gray-600">Loading users...</span>
                </div>
              </td>
            </tr>
            
            <!-- Empty State -->
            <tr v-if="!loading && users.length === 0">
              <td colspan="7" class="py-12 text-center">
                <div class="text-gray-500">
                  <i class="fas fa-users text-4xl mb-4"></i>
                  <p class="text-lg font-medium">No users found</p>
                  <p class="text-sm mt-1">Try changing your filters or add a new user</p>
                </div>
              </td>
            </tr>
            
            <!-- Users List -->
            <tr 
              v-for="user in users" 
              :key="user.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="py-4 px-6">
                <div class="flex items-center gap-4">
                  <div 
                    class="w-10 h-10 rounded-full flex items-center justify-center text-white font-semibold shadow-sm"
                    :style="{ backgroundColor: user.avatar_color }"
                  >
                    {{ user.initials }}
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">{{ user.full_name }}</p>
                    <p class="text-gray-500 text-sm">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              
              <td class="py-4 px-6">
                <div class="flex flex-col gap-1">
                  <span 
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-medium w-fit"
                    :class="getRoleBadgeClass(user.role)"
                  >
                    <i :class="getRoleIcon(user.role)"></i>
                    {{ user.role_display }}
                  </span>
                  <button 
                    v-if="user.role === 'distributor' && user.distributor_requirements_status === 'pending'"
                    @click="viewUser(user)"
                    class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 transition-colors duration-200"
                  >
                    <i class="fas fa-clipboard-check text-xs"></i>
                    Review Requirements
                  </button>
                </div>
              </td>
              
              <td class="py-4 px-6">
                <div class="space-y-1">
                  <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-envelope text-gray-400 text-xs"></i>
                    <span class="text-gray-700">{{ user.email }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-phone text-gray-400 text-xs"></i>
                    <span class="text-gray-700">{{ user.phone || 'Not provided' }}</span>
                  </div>
                </div>
              </td>
              
              <td class="py-4 px-6">
                <div class="flex flex-col gap-1">
                  <span 
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium w-fit"
                    :class="getVerificationBadgeClass(user.verification_status)"
                  >
                    <i :class="getVerificationIcon(user.verification_status)"></i>
                    {{ user.verification_status || 'N/A' }}
                  </span>
                  <p 
                    v-if="user.verification_details" 
                    class="text-xs text-gray-500 truncate max-w-[200px]"
                    :title="getVerificationDetails(user)"
                  >
                    {{ getVerificationDetails(user) }}
                  </p>
                </div>
              </td>
              
              <td class="py-4 px-6">
                <div class="text-sm text-gray-900">{{ user.created_at }}</div>
                <div class="text-xs text-gray-500">Member since</div>
              </td>
              
              <td class="py-4 px-6">
                <span 
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium"
                  :class="getStatusBadgeClass(user.status)"
                >
                  <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(user.status)"></span>
                  {{ user.status_display }}
                </span>
              </td>
              
              <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <!-- View Button -->
                  <button 
                    @click="viewUser(user)"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700 rounded-lg transition-colors duration-200 group"
                    title="View Details"
                  >
                    <i class="fas fa-eye"></i>
                    <span class="text-sm">View</span>
                  </button>
                  
                  <!-- Edit Button -->
                  <button 
                    @click="editUser(user)"
                    class="flex items-center gap-2 px-4 py-2 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 hover:text-yellow-700 rounded-lg transition-colors duration-200 group"
                    title="Edit User"
                  >
                    <i class="fas fa-edit"></i>
                    <span class="text-sm">Edit</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="!loading && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing <span class="font-medium">{{ pagination.from }}</span> to 
            <span class="font-medium">{{ pagination.to }}</span> of 
            <span class="font-medium">{{ pagination.total }}</span> results
          </div>
          
          <div class="flex items-center gap-1">
            <button 
              @click="goToPage(currentPage - 1)" 
              :disabled="currentPage === 1"
              class="w-10 h-10 rounded-lg flex items-center justify-center border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="fas fa-chevron-left"></i>
            </button>
            
            <button 
              v-for="page in getPaginationRange()" 
              :key="page" 
              @click="goToPage(page)"
              class="w-10 h-10 rounded-lg flex items-center justify-center border font-medium transition-colors duration-200"
              :class="currentPage === page 
                ? 'bg-gradient-to-r from-blue-600 to-purple-600 text-white border-transparent' 
                : 'border-gray-300 hover:bg-gray-50'"
            >
              {{ page }}
            </button>
            
            <button 
              @click="goToPage(currentPage + 1)" 
              :disabled="currentPage === pagination.last_page"
              class="w-10 h-10 rounded-lg flex items-center justify-center border border-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- View User Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold shadow-lg"
                 :style="{ backgroundColor: viewingUser.avatar_color }">
              {{ viewingUser.initials }}
            </div>
            <div>
              <h2 class="text-2xl font-bold text-gray-900">{{ viewingUser.full_name }}</h2>
              <div class="flex items-center gap-3 mt-1">
                <span class="text-gray-600">{{ viewingUser.email }}</span>
                <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                <span 
                  class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium"
                  :class="getStatusBadgeClass(viewingUser.status)"
                >
                  <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(viewingUser.status)"></span>
                  {{ viewingUser.status_display }}
                </span>
              </div>
            </div>
          </div>
          <button @click="closeViewModal" class="w-10 h-10 rounded-lg hover:bg-gray-200 flex items-center justify-center transition-colors duration-200">
            <i class="fas fa-times text-gray-600"></i>
          </button>
        </div>
        
        <!-- Modal Body with Scroll -->
        <div class="flex-1 overflow-y-auto p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- User Profile Section -->
            <div class="lg:col-span-1 space-y-6">
              <!-- Basic Info Card -->
              <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <i class="fas fa-id-card text-blue-600"></i>
                  User Profile
                </h4>
                <div class="space-y-4">
                  <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                      <i class="fas fa-user-tag text-blue-600"></i>
                    </div>
                    <div>
                      <p class="text-sm text-gray-600">Role</p>
                      <p class="font-medium">{{ viewingUser.role_display }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                      <i class="fas fa-calendar-alt text-green-600"></i>
                    </div>
                    <div>
                      <p class="text-sm text-gray-600">Member Since</p>
                      <p class="font-medium">{{ viewingUser.created_at }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-lg" :class="getStatusColorClass(viewingUser.status)">
                      <i :class="getStatusIcon(viewingUser.status)" class="text-lg"></i>
                    </div>
                    <div>
                      <p class="text-sm text-gray-600">Account Status</p>
                      <p class="font-medium">{{ viewingUser.status_display }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Verification Status Card -->
              <div v-if="viewingUser.verification_status" class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                  <i class="fas fa-shield-alt text-purple-600"></i>
                  Verification Status
                </h4>
                <div class="space-y-3">
                  <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full flex items-center justify-center" 
                           :class="getVerificationBadgeClass(viewingUser.verification_status)">
                        <i :class="getVerificationIcon(viewingUser.verification_status)" class="text-sm"></i>
                      </div>
                      <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="font-medium">{{ viewingUser.verification_status }}</p>
                      </div>
                    </div>
                  </div>
                  
                </div>
              </div>

              <!-- Action Card -->
              <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                  <i class="fas fa-cogs text-yellow-600"></i>
                  Account Management
                </h4>
                <div class="space-y-4">
                  <div class="flex flex-col gap-3">
                    <button 
                      @click="editUser(viewingUser)"
                      class="flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 font-medium"
                    >
                      <i class="fas fa-edit"></i>
                      Edit User
                    </button>
                    
                    <!-- Approve/Reject Buttons for Pending Users -->
                    <template v-if="viewingUser.status === 'pending' || viewingUser.verification_status === 'pending'">
                      <button 
                        @click="approveUser(viewingUser)"
                        class="flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 font-medium"
                      >
                        <i class="fas fa-check-circle"></i>
                        Approve User
                      </button>
                      
                      <button 
                        @click="rejectUser(viewingUser)"
                        class="flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 font-medium"
                      >
                        <i class="fas fa-times-circle"></i>
                        Reject User
                      </button>
                    </template>
                    
                    <!-- Activate/Deactivate for Active/Inactive Users -->
                    <template v-else>
                      <button 
                        v-if="viewingUser.status === 'active'"
                        @click="deactivateUser(viewingUser)"
                        class="flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 font-medium"
                      >
                        <i class="fas fa-user-slash"></i>
                        Deactivate User
                      </button>
                      
                      <button 
                        v-if="viewingUser.status === 'inactive'"
                        @click="activateUser(viewingUser)"
                        class="flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:shadow-lg transition-all duration-200 font-medium"
                      >
                        <i class="fas fa-user-check"></i>
                        Activate User
                      </button>
                    </template>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- User Details Section -->
            <div class="lg:col-span-2 space-y-6">
              <!-- Personal Information -->
              <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                  <i class="fas fa-user-circle text-blue-600"></i>
                  Personal Information
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-600">First Name</label>
                    <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ viewingUser.first_name }}</p>
                  </div>
                  <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-600">Last Name</label>
                    <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ viewingUser.last_name }}</p>
                  </div>
                  <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-600">Email Address</label>
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                      <i class="fas fa-envelope text-gray-400"></i>
                      <p class="text-gray-900 font-medium">{{ viewingUser.email }}</p>
                    </div>
                  </div>
                  <div class="space-y-1">
                    <label class="block text-sm font-medium text-gray-600">Phone Number</label>
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                      <i class="fas fa-phone text-gray-400"></i>
                      <p class="text-gray-900 font-medium">{{ viewingUser.phone || 'Not provided' }}</p>
                    </div>
                  </div>
                  <div class="md:col-span-2 space-y-1">
                    <label class="block text-sm font-medium text-gray-600">Address</label>
                    <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ viewingUser.address || 'Not provided' }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Requirements Section -->
              <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                  <i class="fas fa-file-contract text-purple-600"></i>
                  User Requirements
                </h4>
                
                <!-- Loading State -->
                <div v-if="loadingRequirements" class="text-center py-8">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
                  <p class="text-gray-600 mt-3">Loading requirements...</p>
                </div>
                
                <!-- No Requirements -->
                <div v-if="!loadingRequirements && (!userRequirements || Object.keys(userRequirements).length === 0)" class="text-center py-8">
                  <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
                  <p class="text-gray-500">No requirements submitted yet</p>
                </div>
                
                <!-- Requirements Content -->
                <div v-if="!loadingRequirements && userRequirements && Object.keys(userRequirements).length > 0">
                  <!-- Distributor Requirements -->
                  <div v-if="viewingUser.role === 'distributor' && userRequirements.distributor" class="space-y-6">
                    <div class="border-b border-gray-200 pb-6">
                      <h5 class="font-semibold text-gray-800 mb-4">Business Information</h5>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Company Name</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.distributor.company_name }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Business Registration Number</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.distributor.business_registration_number }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Distributor Documents -->
                    <div>
                      <h5 class="font-semibold text-gray-800 mb-4">Business Documents</h5>
                      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Valid ID -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Valid ID</h6>
                          <div class="space-y-2">
                            <p class="text-sm text-gray-600">Type: {{ userRequirements.distributor.valid_id_type_display || userRequirements.distributor.valid_id_type }}</p>
                            <p class="text-sm text-gray-600">Number: {{ userRequirements.distributor.id_number }}</p>
                            <div v-if="userRequirements.distributor.valid_id_photo_url" class="mt-2">
                              <img :src="userRequirements.distributor.valid_id_photo_url" 
                                   alt="Valid ID" 
                                   class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                   @click="showImageModal(userRequirements.distributor.valid_id_photo_url, 'Valid ID')">
                            </div>
                          </div>
                        </div>
                        
                        <!-- DTI Certificate -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">DTI Certificate</h6>
                          <div v-if="userRequirements.distributor.dti_certificate_photo_url">
                            <img :src="userRequirements.distributor.dti_certificate_photo_url" 
                                 alt="DTI Certificate" 
                                 class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="showImageModal(userRequirements.distributor.dti_certificate_photo_url, 'DTI Certificate')">
                          </div>
                        </div>
                        
                        <!-- Mayor's Permit -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Mayor's Permit</h6>
                          <div v-if="userRequirements.distributor.mayor_permit_photo_url">
                            <img :src="userRequirements.distributor.mayor_permit_photo_url" 
                                 alt="Mayor's Permit" 
                                 class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="showImageModal(userRequirements.distributor.mayor_permit_photo_url, 'Mayor\'s Permit')">
                          </div>
                        </div>
                        
                        <!-- Barangay Clearance -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Barangay Clearance</h6>
                          <div v-if="userRequirements.distributor.barangay_clearance_photo_url">
                            <img :src="userRequirements.distributor.barangay_clearance_photo_url" 
                                 alt="Barangay Clearance" 
                                 class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="showImageModal(userRequirements.distributor.barangay_clearance_photo_url, 'Barangay Clearance')">
                          </div>
                        </div>
                        
                        <!-- Business Registration -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Business Registration</h6>
                          <div v-if="userRequirements.distributor.business_registration_photo_url">
                            <img :src="userRequirements.distributor.business_registration_photo_url" 
                                 alt="Business Registration" 
                                 class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="showImageModal(userRequirements.distributor.business_registration_photo_url, 'Business Registration')">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Client Requirements -->
                  <div v-if="viewingUser.role === 'client' && userRequirements.client" class="space-y-6">
                    <div class="border-b border-gray-200 pb-6">
                      <h5 class="font-semibold text-gray-800 mb-4">ID Verification</h5>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">ID Type</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.client.valid_id_type_display || userRequirements.client.valid_id_type }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">ID Number</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.client.id_number }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Client Documents -->
                    <div>
                      <h5 class="font-semibold text-gray-800 mb-4">ID Document</h5>
                      <div class="flex justify-center">
                        <div v-if="userRequirements.client.valid_id_photo_url" class="max-w-md w-full">
                          <img :src="userRequirements.client.valid_id_photo_url" 
                               alt="Valid ID" 
                               class="w-full h-auto object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                               @click="showImageModal(userRequirements.client.valid_id_photo_url, 'Valid ID')">
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Service Provider Requirements -->
                  <div v-if="viewingUser.role === 'service_provider' && userRequirements.service_provider" class="space-y-6">
                    <div class="border-b border-gray-200 pb-6">
                      <h5 class="font-semibold text-gray-800 mb-4">ID Verification</h5>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">ID Type</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.service_provider.valid_id_type_display || userRequirements.service_provider.valid_id_type }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">ID Number</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.service_provider.id_number }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Service Provider Documents -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <h6 class="font-medium text-gray-700 mb-3">Valid ID</h6>
                        <div v-if="userRequirements.service_provider.valid_id_photo_url" class="mt-2">
                          <img :src="userRequirements.service_provider.valid_id_photo_url" 
                               alt="Valid ID" 
                               class="w-full h-64 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                               @click="showImageModal(userRequirements.service_provider.valid_id_photo_url, 'Valid ID')">
                        </div>
                      </div>
                      <div>
                        <h6 class="font-medium text-gray-700 mb-3">Selfie with ID</h6>
                        <div v-if="userRequirements.service_provider.selfie_with_id_photo_url" class="mt-2">
                          <img :src="userRequirements.service_provider.selfie_with_id_photo_url" 
                               alt="Selfie with ID" 
                               class="w-full h-64 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                               @click="showImageModal(userRequirements.service_provider.selfie_with_id_photo_url, 'Selfie with ID')">
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- HR Manager Requirements -->
                  <div v-if="viewingUser.role === 'hr_manager' && userRequirements.hr_manager" class="space-y-6">
                    <div class="border-b border-gray-200 pb-6">
                      <h5 class="font-semibold text-gray-800 mb-4">Employee Information</h5>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Position</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.hr_manager.position }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Employment Type</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.hr_manager.employment_type_display || userRequirements.hr_manager.employment_type }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Salary</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ formatCurrency(userRequirements.hr_manager.salary) }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Hire Date</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ formatDate(userRequirements.hr_manager.hire_date) }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- HR Manager Documents -->
                    <div>
                      <h5 class="font-semibold text-gray-800 mb-4">Documents</h5>
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Valid ID -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Valid ID</h6>
                          <div v-if="userRequirements.hr_manager.valid_id_photo_url" class="mt-2">
                            <img :src="userRequirements.hr_manager.valid_id_photo_url" 
                                 alt="Valid ID" 
                                 class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="showImageModal(userRequirements.hr_manager.valid_id_photo_url, 'Valid ID')">
                          </div>
                        </div>
                        
                        <!-- Resume -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Resume</h6>
                          <div v-if="userRequirements.hr_manager.resume_url" class="mt-2">
                            <a :href="userRequirements.hr_manager.resume_url" 
                               target="_blank" 
                               class="flex items-center gap-2 text-blue-600 hover:text-blue-800">
                              <i class="fas fa-file-pdf text-xl"></i>
                              <span>View Resume</span>
                            </a>
                          </div>
                        </div>
                        
                        <!-- Employment Contract -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Employment Contract</h6>
                          <div v-if="userRequirements.hr_manager.employment_contract_url" class="mt-2">
                            <a :href="userRequirements.hr_manager.employment_contract_url" 
                               target="_blank" 
                               class="flex items-center gap-2 text-blue-600 hover:text-blue-800">
                              <i class="fas fa-file-contract text-xl"></i>
                              <span>View Contract</span>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Finance Manager Requirements -->
                  <div v-if="viewingUser.role === 'finance_manager' && userRequirements.finance_manager" class="space-y-6">
                    <div class="border-b border-gray-200 pb-6">
                      <h5 class="font-semibold text-gray-800 mb-4">Employee Information</h5>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Position</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.finance_manager.position }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Employment Type</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.finance_manager.employment_type_display || userRequirements.finance_manager.employment_type }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Salary</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ formatCurrency(userRequirements.finance_manager.salary) }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">Hire Date</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ formatDate(userRequirements.finance_manager.hire_date) }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Finance Manager Documents -->
                    <div>
                      <h5 class="font-semibold text-gray-800 mb-4">Documents</h5>
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Valid ID -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Valid ID</h6>
                          <div v-if="userRequirements.finance_manager.valid_id_photo_url" class="mt-2">
                            <img :src="userRequirements.finance_manager.valid_id_photo_url" 
                                 alt="Valid ID" 
                                 class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                 @click="showImageModal(userRequirements.finance_manager.valid_id_photo_url, 'Valid ID')">
                          </div>
                        </div>
                        
                        <!-- Resume -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Resume</h6>
                          <div v-if="userRequirements.finance_manager.resume_url" class="mt-2">
                            <a :href="userRequirements.finance_manager.resume_url" 
                               target="_blank" 
                               class="flex items-center gap-2 text-blue-600 hover:text-blue-800">
                              <i class="fas fa-file-pdf text-xl"></i>
                              <span>View Resume</span>
                            </a>
                          </div>
                        </div>
                        
                        <!-- Employment Contract -->
                        <div class="border border-gray-200 rounded-lg p-4">
                          <h6 class="font-medium text-gray-700 mb-2">Employment Contract</h6>
                          <div v-if="userRequirements.finance_manager.employment_contract_url" class="mt-2">
                            <a :href="userRequirements.finance_manager.employment_contract_url" 
                               target="_blank" 
                               class="flex items-center gap-2 text-blue-600 hover:text-blue-800">
                              <i class="fas fa-file-contract text-xl"></i>
                              <span>View Contract</span>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Operational Distributor Requirements -->
                  <div v-if="viewingUser.role === 'operational_distributor' && userRequirements.operational_distributor" class="space-y-6">
                    <div class="border-b border-gray-200 pb-6">
                      <h5 class="font-semibold text-gray-800 mb-4">ID Verification</h5>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">ID Type</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.operational_distributor.valid_id_type_display || userRequirements.operational_distributor.valid_id_type }}</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium text-gray-600 mb-1">ID Number</label>
                          <p class="text-gray-900 font-medium p-3 bg-gray-50 rounded-lg">{{ userRequirements.operational_distributor.id_number }}</p>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Operational Distributor Documents -->
                    <div>
                      <h5 class="font-semibold text-gray-800 mb-4">ID Document</h5>
                      <div class="flex justify-center">
                        <div v-if="userRequirements.operational_distributor.valid_id_photo_url" class="max-w-md w-full">
                          <img :src="userRequirements.operational_distributor.valid_id_photo_url" 
                               alt="Valid ID" 
                               class="w-full h-auto object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                               @click="showImageModal(userRequirements.operational_distributor.valid_id_photo_url, 'Valid ID')">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Recent Activity -->
              <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                <h4 class="text-lg font-semibold text-gray-900 mb-6 flex items-center gap-2">
                  <i class="fas fa-history text-purple-600"></i>
                  Account Activity
                </h4>
                <div class="space-y-4">
                  <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <i class="fas fa-user-plus text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium text-gray-900">Account Created</p>
                      <p class="text-sm text-gray-500">{{ viewingUser.created_at }}</p>
                    </div>
                  </div>
                  
                  <div v-if="viewingUser.verification_details?.submitted_at" class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                      <i class="fas fa-clipboard-check text-green-600"></i>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium text-gray-900">Requirements Submitted</p>
                      <p class="text-sm text-gray-500">{{ formatDate(viewingUser.verification_details.submitted_at) }}</p>
                    </div>
                  </div>
                  
                  <div v-if="viewingUser.verification_details?.reviewed_at" class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
                      <i class="fas fa-eye text-yellow-600"></i>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium text-gray-900">Last Reviewed</p>
                      <p class="text-sm text-gray-500">{{ formatDate(viewingUser.verification_details.reviewed_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-between p-6 border-t border-gray-200 bg-gray-50">
          <button @click="closeViewModal" class="flex items-center gap-2 px-6 py-3 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors duration-200">
            <i class="fas fa-times"></i>
            Close
          </button>
          <div class="flex items-center gap-3">
            <button 
              v-if="viewingUser.role === 'distributor' && viewingUser.verification_status === 'pending'"
              @click="reviewRequirements(viewingUser)"
              class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:shadow-lg font-medium transition-all duration-200"
            >
              <i class="fas fa-file-contract"></i>
              Review Requirements
            </button>
            <button 
              @click="editUser(viewingUser)"
              class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:shadow-lg font-medium transition-all duration-200"
            >
              <i class="fas fa-edit"></i>
              Edit User
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="showImageModalFlag" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center p-4 z-[60]">
      <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b border-gray-200">
          <h3 class="text-xl font-bold text-gray-900">{{ currentImageTitle }}</h3>
          <button @click="closeImageModal" class="w-10 h-10 rounded-lg hover:bg-gray-100 flex items-center justify-center">
            <i class="fas fa-times text-gray-600"></i>
          </button>
        </div>
        <div class="p-4 flex items-center justify-center h-[70vh]">
          <img :src="currentImageUrl" 
               :alt="currentImageTitle" 
               class="max-w-full max-h-full object-contain rounded-lg">
        </div>
        <div class="p-4 border-t border-gray-200 flex justify-end">
          <a :href="currentImageUrl" 
             target="_blank" 
             class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-external-link-alt"></i>
            Open in New Tab
          </a>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Edit User</h2>
              <p class="text-gray-600 mt-1">Update user information and settings</p>
            </div>
            <button @click="closeEditUserModal" class="w-10 h-10 rounded-lg hover:bg-gray-100 flex items-center justify-center">
              <i class="fas fa-times text-gray-500"></i>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <form @submit.prevent="saveEditedUser">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                <input 
                  v-model="editingUser.first_name" 
                  type="text" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                <input 
                  v-model="editingUser.last_name" 
                  type="text" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                <input 
                  v-model="editingUser.email" 
                  type="email" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <input 
                  v-model="editingUser.phone" 
                  type="tel"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <textarea 
                  v-model="editingUser.address" 
                  rows="2"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                ></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                <select 
                  v-model="editingUser.role" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="admin">Administrator</option>
                  <option value="distributor">Distributor</option>
                  <option value="service_provider">Service Provider</option>
                  <option value="client">Client</option>
                  <option value="operational_distributor">Operational Distributor</option>
                  <option value="hr_manager">HR Manager</option>
                  <option value="finance_manager">Finance Manager</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select 
                  v-model="editingUser.status"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="pending">Pending</option>
                </select>
              </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
              <button 
                type="button" 
                @click="closeEditUserModal" 
                class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="updatingUser"
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:shadow-lg font-medium disabled:opacity-50"
              >
                <i v-if="updatingUser" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-save"></i>
                {{ updatingUser ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-gray-900">Add New User</h2>
              <p class="text-gray-600 mt-1">Create a new user account</p>
            </div>
            <button @click="closeAddUserModal" class="w-10 h-10 rounded-lg hover:bg-gray-100 flex items-center justify-center">
              <i class="fas fa-times text-gray-500"></i>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <form @submit.prevent="addNewUser">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                <input 
                  v-model="newUser.first_name" 
                  type="text" 
                  placeholder="Enter first name"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                <input 
                  v-model="newUser.last_name" 
                  type="text" 
                  placeholder="Enter last name"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                <input 
                  v-model="newUser.email" 
                  type="email" 
                  placeholder="user@example.com"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <input 
                  v-model="newUser.phone" 
                  type="tel" 
                  placeholder="Enter phone number"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <input 
                  v-model="newUser.address" 
                  type="text" 
                  placeholder="Enter address"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign Role *</label>
                <select 
                  v-model="newUser.role" 
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Select a role</option>
                  <option value="admin">Administrator</option>
                  <option value="distributor">Distributor</option>
                  <option value="service_provider">Service Provider</option>
                  <option value="client">Client</option>
                  <option value="operational_distributor">Operational Distributor</option>
                  <option value="hr_manager">HR Manager</option>
                  <option value="finance_manager">Finance Manager</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Status</label>
                <select 
                  v-model="newUser.status"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="pending">Pending</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                <div class="relative">
                  <input 
                    v-model="newUser.password" 
                    :type="showPassword ? 'text' : 'password'" 
                    placeholder="Enter password"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 pr-10"
                  >
                  <button 
                    type="button" 
                    @click="showPassword = !showPassword" 
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                  >
                    <i v-if="showPassword" class="fas fa-eye-slash"></i>
                    <i v-else class="fas fa-eye"></i>
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                <input 
                  v-model="newUser.password_confirmation" 
                  :type="showPassword ? 'text' : 'password'" 
                  placeholder="Confirm password"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
            </div>
            
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
              <button 
                type="button" 
                @click="closeAddUserModal" 
                class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium"
              >
                Cancel
              </button>
              <button 
                type="submit" 
                :disabled="addingUser"
                class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:shadow-lg font-medium disabled:opacity-50"
              >
                <i v-if="addingUser" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-plus"></i>
                {{ addingUser ? 'Creating...' : 'Create User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'UserManagement',
  data() {
    return {
      // Tabs data
      tabs: [
        { label: 'All Users', value: 'all', icon: 'fas fa-users' },
        { label: 'Admins', value: 'admin', icon: 'fas fa-user-shield' },
        { label: 'Distributors', value: 'distributor', icon: 'fas fa-truck' },
        { label: 'Service Providers', value: 'service_provider', icon: 'fas fa-tools' },
        { label: 'Clients', value: 'client', icon: 'fas fa-user' },
        { label: 'Operational', value: 'operational_distributor', icon: 'fas fa-cogs' },
        { label: 'HR Managers', value: 'hr_manager', icon: 'fas fa-user-tie' },
        { label: 'Finance', value: 'finance_manager', icon: 'fas fa-chart-line' }
      ],
      
      // State
      activeTab: 'all',
      searchQuery: '',
      statusFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      showAddUserModal: false,
      showEditUserModal: false,
      showViewModal: false,
      showImageModalFlag: false,
      showPassword: false,
      loading: false,
      loadingRequirements: false,
      addingUser: false,
      updatingUser: false,
      
      // Data
      users: [],
      statistics: {},
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      },
      
      // User objects
      newUser: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active',
        password: '',
        password_confirmation: ''
      },
      editingUser: {
        id: null,
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active'
      },
      viewingUser: {},
      userRequirements: null,
      currentImageUrl: '',
      currentImageTitle: '',
      
      debounceTimer: null
    }
  },
  mounted() {
    this.fetchUsers();
    this.fetchStatistics();
  },
  watch: {
    activeTab() {
      this.currentPage = 1;
      this.fetchUsers();
    },
    currentPage() {
      this.fetchUsers();
    }
  },
  methods: {
    // Fetch users with role-based filtering
    async fetchUsers() {
      this.loading = true;
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) {
          this.showToast('Please login first', 'error');
          this.$router.push('/login');
          return;
        }

        const params = {
          page: this.currentPage,
          per_page: this.itemsPerPage,
          status: this.statusFilter !== 'all' ? this.statusFilter : undefined,
          search: this.searchQuery || undefined,
          role: this.activeTab !== 'all' ? this.activeTab : undefined
        };

        const baseURL = 'http://localhost:8000';
        const response = await axios.get(`${baseURL}/api/admin/users`, {
          headers: { 
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          params: params
        });

        if (response.data.success) {
          this.users = response.data.users;
          this.pagination = response.data.pagination;
          this.statistics = response.data.statistics || this.statistics;
        } else {
          this.showToast(response.data.message || 'Failed to load users', 'error');
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.loading = false;
      }
    },
    
    // Fetch statistics
    async fetchStatistics() {
  try {
    // First try the API
    const token = localStorage.getItem('auth_token');
    const baseURL = 'http://localhost:8000';
    
    try {
      const response = await axios.get(`${baseURL}/api/admin/users/statistics`, {
        headers: { Authorization: `Bearer ${token}` }
      });
      
      if (response.data.success) {
        this.statistics = response.data.statistics;
        return;
      }
    } catch (apiError) {
      console.log('API failed, calculating from existing data');
    }
    
    // Fallback: Calculate from existing users data
    this.calculateStatisticsFromUsers();
    
  } catch (error) {
    console.error('Error in fetchStatistics:', error);
    this.calculateStatisticsFromUsers();
  }
},

calculateStatisticsFromUsers() {
  // Calculate statistics from the users you already have
  const stats = {
    total: this.users.length,
    admin: this.users.filter(u => u.role === 'admin').length,
    distributor: this.users.filter(u => u.role === 'distributor').length,
    service_provider: this.users.filter(u => u.role === 'service_provider').length,
    client: this.users.filter(u => u.role === 'client').length,
    operational_distributor: this.users.filter(u => u.role === 'operational_distributor').length,
    hr_manager: this.users.filter(u => u.role === 'hr_manager').length,
    finance_manager: this.users.filter(u => u.role === 'finance_manager').length,
    active: this.users.filter(u => u.status === 'active').length,
    inactive: this.users.filter(u => u.status === 'inactive').length,
    pending: this.users.filter(u => u.status === 'pending').length,
  };
  
  this.statistics = stats;
},
    
    // Fetch user requirements
    async fetchUserRequirements(userId) {
      this.loadingRequirements = true;
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        
        // Reset requirements
        this.userRequirements = null;
        
        // Fetch user details which should include requirements data
        const response = await axios.get(`${baseURL}/api/admin/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          const user = response.data.user;
          
          // Process requirements based on user role
          switch (user.role) {
            case 'distributor':
              if (user.verification_details) {
                this.userRequirements = {
                  distributor: {
                    ...user.verification_details,
                    valid_id_photo_url: user.verification_details.valid_id_photo ? 
                      `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                    dti_certificate_photo_url: user.verification_details.dti_certificate_photo ? 
                      `${baseURL}/storage/${user.verification_details.dti_certificate_photo}` : null,
                    mayor_permit_photo_url: user.verification_details.mayor_permit_photo ? 
                      `${baseURL}/storage/${user.verification_details.mayor_permit_photo}` : null,
                    barangay_clearance_photo_url: user.verification_details.barangay_clearance_photo ? 
                      `${baseURL}/storage/${user.verification_details.barangay_clearance_photo}` : null,
                    business_registration_photo_url: user.verification_details.business_registration_photo ? 
                      `${baseURL}/storage/${user.verification_details.business_registration_photo}` : null
                  }
                };
              }
              break;
              
            case 'client':
              if (user.verification_details) {
                this.userRequirements = {
                  client: {
                    ...user.verification_details,
                    valid_id_photo_url: user.verification_details.valid_id_photo ? 
                      `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null
                  }
                };
              }
              break;
              
            case 'service_provider':
              if (user.verification_details) {
                this.userRequirements = {
                  service_provider: {
                    ...user.verification_details,
                    valid_id_photo_url: user.verification_details.valid_id_photo ? 
                      `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                    selfie_with_id_photo_url: user.verification_details.selfie_with_id_photo ? 
                      `${baseURL}/storage/${user.verification_details.selfie_with_id_photo}` : null
                  }
                };
              }
              break;
              
            case 'hr_manager':
              if (user.verification_details) {
                this.userRequirements = {
                  hr_manager: {
                    ...user.verification_details,
                    valid_id_photo_url: user.verification_details.valid_id_photo ? 
                      `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                    resume_url: user.verification_details.resume ? 
                      `${baseURL}/storage/${user.verification_details.resume}` : null,
                    employment_contract_url: user.verification_details.employment_contract ? 
                      `${baseURL}/storage/${user.verification_details.employment_contract}` : null
                  }
                };
              }
              break;
              
            case 'finance_manager':
              if (user.verification_details) {
                this.userRequirements = {
                  finance_manager: {
                    ...user.verification_details,
                    valid_id_photo_url: user.verification_details.valid_id_photo ? 
                      `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                    resume_url: user.verification_details.resume ? 
                      `${baseURL}/storage/${user.verification_details.resume}` : null,
                    employment_contract_url: user.verification_details.employment_contract ? 
                      `${baseURL}/storage/${user.verification_details.employment_contract}` : null
                  }
                };
              }
              break;
              
            case 'operational_distributor':
              if (user.verification_details) {
                this.userRequirements = {
                  operational_distributor: {
                    ...user.verification_details,
                    valid_id_photo_url: user.verification_details.valid_id_photo ? 
                      `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null
                  }
                };
              }
              break;
          }
        }
      } catch (error) {
        console.error('Error fetching user requirements:', error);
        this.userRequirements = null;
      } finally {
        this.loadingRequirements = false;
      }
    },
    
    // Show image in modal
    showImageModal(imageUrl, title) {
      this.currentImageUrl = imageUrl;
      this.currentImageTitle = title;
      this.showImageModalFlag = true;
    },
    
    // Close image modal
    closeImageModal() {
      this.showImageModalFlag = false;
      this.currentImageUrl = '';
      this.currentImageTitle = '';
    },
    
    // Debounced search
    debouncedFetchUsers() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1;
        this.fetchUsers();
      }, 500);
    },
    
    // Pagination methods
    getPaginationRange() {
      const range = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.pagination.last_page, start + 4);
      
      for (let i = start; i <= end; i++) {
        range.push(i);
      }
      return range;
    },
    
    goToPage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.currentPage = page;
        this.fetchUsers();
      }
    },
    
    // User actions
    async viewUser(user) {
      this.viewingUser = { ...user };
      this.showViewModal = true;
      // Fetch user requirements
      await this.fetchUserRequirements(user.id);
    },
    
    editUser(user) {
      this.editingUser = {
        id: user.id,
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone || '',
        address: user.address || '',
        role: user.role,
        status: user.status
      };
      this.showEditUserModal = true;
      this.showViewModal = false;
    },
    
    async saveEditedUser() {
      this.updatingUser = true;
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        const response = await axios.put(`${baseURL}/api/admin/users/${this.editingUser.id}`, this.editingUser, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          this.showToast('User updated successfully', 'success');
          this.closeEditUserModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.updatingUser = false;
      }
    },
    
    async addNewUser() {
      this.addingUser = true;
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        const response = await axios.post(`${baseURL}/api/admin/users`, this.newUser, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          this.showToast('User created successfully', 'success');
          this.closeAddUserModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.addingUser = false;
      }
    },
    
    // Approve user (activate account and approve requirements)
    async approveUser(user) {
      const message = `Are you sure you want to approve ${user.full_name}? This will activate their account and approve all requirements.`;
      
      if (!confirm(message)) return;
      
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        
        // First, try the approve endpoint (if it exists)
        try {
          const approveResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/approve`, {}, {
            headers: { Authorization: `Bearer ${token}` }
          });
          
          if (approveResponse.data.success) {
            this.showToast('User approved successfully', 'success');
            this.closeViewModal();
            this.fetchUsers();
            this.fetchStatistics();
            return;
          }
        } catch (approveError) {
          console.log('Approve endpoint not found, using activate instead');
        }
        
        // Fallback to activate endpoint
        const userResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/activate`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (userResponse.data.success) {
          this.showToast('User activated successfully', 'success');
          
          // If user is distributor, also approve their requirements
          if (user.role === 'distributor' && user.verification_status === 'pending') {
            try {
              const distributorReq = await axios.get(`${baseURL}/api/distributor/requirements/admin/pending`, {
                headers: { Authorization: `Bearer ${token}` }
              });
              
              const pendingReq = distributorReq.data.data?.find(req => req.user_id === user.id);
              if (pendingReq) {
                await axios.put(`${baseURL}/api/distributor/requirements/admin/${pendingReq.id}`, {
                  status: 'approved',
                  rejection_reason: null
                }, {
                  headers: { Authorization: `Bearer ${token}` }
                });
                this.showToast('Distributor requirements approved', 'success');
              }
            } catch (error) {
              console.error('Error approving distributor requirements:', error);
            }
          }
          
          this.closeViewModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      }
    },
    
    // Reject user (with reason)
    async rejectUser(user) {
      const reason = prompt(`Please enter reason for rejecting ${user.full_name}:`, '');
      if (reason === null) return; // User cancelled
      
      if (!reason.trim()) {
        this.showToast('Please provide a rejection reason', 'error');
        return;
      }
      
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        
        // First, try the reject endpoint (if it exists)
        try {
          const rejectResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/reject`, {
            reason: reason
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
          
          if (rejectResponse.data.success) {
            this.showToast(`User rejected: ${reason}`, 'success');
            this.closeViewModal();
            this.fetchUsers();
            this.fetchStatistics();
            return;
          }
        } catch (rejectError) {
          console.log('Reject endpoint not found, using deactivate instead');
        }
        
        // Fallback to deactivate endpoint
        const userResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/deactivate`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (userResponse.data.success) {
          // If user is distributor, also reject their requirements
          if (user.role === 'distributor' && user.verification_status === 'pending') {
            try {
              const distributorReq = await axios.get(`${baseURL}/api/distributor/requirements/admin/pending`, {
                headers: { Authorization: `Bearer ${token}` }
              });
              
              const pendingReq = distributorReq.data.data?.find(req => req.user_id === user.id);
              if (pendingReq) {
                await axios.put(`${baseURL}/api/distributor/requirements/admin/${pendingReq.id}`, {
                  status: 'rejected',
                  rejection_reason: reason
                }, {
                  headers: { Authorization: `Bearer ${token}` }
                });
              }
            } catch (error) {
              console.error('Error rejecting distributor requirements:', error);
            }
          }
          
          this.showToast(`User rejected: ${reason}`, 'success');
          this.closeViewModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      }
    },
    
    // Activate user
    async activateUser(user) {
      if (!confirm(`Are you sure you want to activate ${user.full_name}?`)) return;
      
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        const response = await axios.post(`${baseURL}/api/admin/users/${user.id}/activate`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          this.showToast('User activated successfully', 'success');
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      }
    },
    
    // Deactivate user
    async deactivateUser(user) {
      if (!confirm(`Are you sure you want to deactivate ${user.full_name}?`)) return;
      
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        const response = await axios.post(`${baseURL}/api/admin/users/${user.id}/deactivate`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          this.showToast('User deactivated successfully', 'success');
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      }
    },
    
    // Review requirements (for distributors)
    async reviewRequirements(user) {
      this.showToast('Redirecting to requirements review page...', 'info');
      // In a real app, you would navigate to the requirements review page
      // this.$router.push(`/admin/distributor-requirements/${user.id}`);
    },
    
    // Modal methods
    closeViewModal() {
      this.showViewModal = false;
      this.viewingUser = {};
      this.userRequirements = null;
      this.loadingRequirements = false;
    },
    
    closeEditUserModal() {
      this.showEditUserModal = false;
      this.editingUser = {
        id: null,
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active'
      };
    },
    
    closeAddUserModal() {
      this.showAddUserModal = false;
      this.newUser = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active',
        password: '',
        password_confirmation: ''
      };
      this.showPassword = false;
    },
    
    // Format currency
    formatCurrency(amount) {
      if (!amount) return '₱0.00';
      return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    // Style helper methods
    getRoleBadgeClass(role) {
      const classes = {
        'admin': 'bg-red-100 text-red-800',
        'distributor': 'bg-green-100 text-green-800',
        'service_provider': 'bg-purple-100 text-purple-800',
        'client': 'bg-gray-100 text-gray-800',
        'operational_distributor': 'bg-yellow-100 text-yellow-800',
        'hr_manager': 'bg-blue-100 text-blue-800',
        'finance_manager': 'bg-indigo-100 text-indigo-800'
      };
      return classes[role] || 'bg-gray-100 text-gray-800';
    },
    
    getRoleIcon(role) {
      const icons = {
        'admin': 'fas fa-user-shield',
        'distributor': 'fas fa-truck',
        'service_provider': 'fas fa-tools',
        'client': 'fas fa-user',
        'operational_distributor': 'fas fa-cogs',
        'hr_manager': 'fas fa-user-tie',
        'finance_manager': 'fas fa-chart-line'
      };
      return icons[role] || 'fas fa-user';
    },
    
    getVerificationBadgeClass(status) {
      if (!status) return 'bg-gray-100 text-gray-800';
      
      const classes = {
        'approved': 'bg-green-100 text-green-800',
        'verified': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'rejected': 'bg-red-100 text-red-800'
      };
      return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
    },
    
    getVerificationIcon(status) {
      if (!status) return 'fas fa-question-circle';
      
      const icons = {
        'approved': 'fas fa-check-circle',
        'verified': 'fas fa-check-circle',
        'pending': 'fas fa-clock',
        'rejected': 'fas fa-times-circle'
      };
      return icons[status.toLowerCase()] || 'fas fa-question-circle';
    },
    
    getStatusBadgeClass(status) {
      const classes = {
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-red-100 text-red-800',
        'pending': 'bg-yellow-100 text-yellow-800'
      };
      return classes[status] || 'bg-gray-100 text-gray-800';
    },
    
    getStatusDotClass(status) {
      const classes = {
        'active': 'bg-green-500',
        'inactive': 'bg-red-500',
        'pending': 'bg-yellow-500'
      };
      return classes[status] || 'bg-gray-500';
    },
    
    getStatusIcon(status) {
      const icons = {
        'active': 'fas fa-check-circle text-green-600',
        'inactive': 'fas fa-times-circle text-red-600',
        'pending': 'fas fa-clock text-yellow-600'
      };
      return icons[status] || 'fas fa-question-circle text-gray-600';
    },
    
    getStatusColorClass(status) {
      const classes = {
        'active': 'bg-green-100',
        'inactive': 'bg-red-100',
        'pending': 'bg-yellow-100'
      };
      return classes[status] || 'bg-gray-100';
    },
    
    getVerificationDetails(user) {
      if (!user.verification_details) return '';
      
      const details = user.verification_details;
      if (user.role === 'distributor') {
        return details.company_name || '';
      } else if (user.role === 'client' || user.role === 'service_provider') {
        return details.valid_id_type || '';
      }
      return '';
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    // Utility methods
    showToast(message, type = 'info') {
      if (this.$toast) {
        this.$toast[type](message);
      } else {
        alert(message);
      }
    },
    
    handleError(error) {
      let errorMessage = 'An error occurred';
      
      if (error.code === 'ERR_NETWORK') {
        errorMessage = 'Cannot connect to server. Make sure Laravel backend is running.';
      } else if (error.response) {
        if (error.response.status === 401) {
          errorMessage = 'Session expired. Please login again.';
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user_data');
          localStorage.removeItem('user_role');
          this.$router.push('/login');
        } else if (error.response.status === 403) {
          errorMessage = 'Access denied. Admin privileges required.';
        } else if (error.response.status === 404) {
          errorMessage = 'API endpoint not found. Check if the route exists in Laravel.';
        } else if (error.response.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          errorMessage = errors.join(', ');
        } else if (error.response.data?.message) {
          errorMessage = error.response.data.message;
        } else {
          errorMessage = 'Server error occurred.';
        }
      } else if (error.request) {
        errorMessage = 'No response from server. Check if backend is running.';
      } else {
        errorMessage = 'An error occurred: ' + error.message;
      }
      
      this.showToast(errorMessage, 'error');
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar for modal */
.modal-body::-webkit-scrollbar {
  width: 8px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Smooth transitions for dropdown */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: opacity 0.2s, transform 0.2s;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Smooth modal transitions */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Custom tooltip styles */
[title] {
  position: relative;
}

[title]:hover::after {
  content: attr(title);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: #333;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  z-index: 1000;
}

/* Ensure dropdown appears above other elements */
.relative {
  position: relative;
}

/* Custom hover effects for table rows */
tr:hover {
  background-color: #f9fafb !important;
}

/* Style for the more actions dropdown */
.dropdown-menu {
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

/* Custom focus styles */
input:focus, select:focus, textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Image hover effects */
img {
  transition: transform 0.2s ease-in-out;
}

img:hover {
  transform: scale(1.02);
}

/* Document card styles */
.document-card {
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  background-color: #f9fafb;
}

.document-card:hover {
  border-color: #3b82f6;
  background-color: #eff6ff;
}
</style>