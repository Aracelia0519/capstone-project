<template>
  <div class="positions-roles p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Positions & Roles</h1>
        <p class="text-gray-600">Define job roles and responsibilities within the organization</p>
      </div>
      <button @click="startNewPosition" class="mt-4 md:mt-0 flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Position
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-md p-6 border border-blue-100">
        <div class="flex items-center">
          <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl mr-4 shadow-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ statistics.total_positions || 0 }}</h3>
            <p class="text-gray-600 text-sm">Total Positions</p>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-md p-6 border border-green-100">
        <div class="flex items-center">
          <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl mr-4 shadow-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ statistics.active_positions || 0 }}</h3>
            <p class="text-gray-600 text-sm">Active Positions</p>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-br from-white to-purple-50 rounded-xl shadow-md p-6 border border-purple-100">
        <div class="flex items-center">
          <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl mr-4 shadow-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ statistics.departments_count || 0 }}</h3>
            <p class="text-gray-600 text-sm">Departments</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row gap-4 mb-6 p-4 bg-gradient-to-r from-white to-gray-50 rounded-xl shadow-sm border border-gray-200">
      <div class="flex-1 relative">
        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input v-model="searchQuery" @input="searchPositions" type="text" placeholder="Search positions by title, department, or description..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue">
      </div>
      <div class="w-full md:w-48">
        <select v-model="selectedDepartment" @change="fetchPositions" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue bg-white">
          <option value="all">All Departments</option>
          <option v-for="dept in departments" :key="dept.name" :value="dept.name">
            {{ dept.name }}
          </option>
        </select>
      </div>
      <div class="w-full md:w-48">
        <select v-model="selectedStatus" @change="fetchPositions" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue bg-white">
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="all">All Status</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center p-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading positions...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-xl p-8 text-center shadow-sm">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.17 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-red-800 mb-2">Error Loading Positions</h3>
      <p class="text-red-600 mb-6 max-w-md mx-auto">{{ error }}</p>
      <button @click="fetchPositions" class="px-6 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all shadow-md hover:shadow-lg">
        Retry Loading
      </button>
    </div>

    <!-- Empty State -->
    <div v-else-if="positions.length === 0 && !loading" class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-sm p-12 text-center border border-gray-200">
      <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-800 mb-3">No Positions Found</h3>
      <p class="text-gray-600 mb-8 max-w-md mx-auto">Start building your team structure by creating your first position.</p>
      <button @click="startNewPosition" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg hover:from-blue-600 hover:to-indigo-700 transition-all shadow-md hover:shadow-lg">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Your First Position
      </button>
    </div>

    <!-- Positions Grid -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="position in positions" :key="position.id" class="group bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-md p-6 border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <div class="flex items-start justify-between mb-4">
          <div>
            <div class="flex items-center mb-2">
              <div class="p-2 rounded-xl mr-3 shadow-sm" :class="position.department_color.bg">
                <svg class="w-5 h-5" :class="position.department_color.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">{{ position.title }}</h3>
                <span :class="['text-xs px-2 py-1 rounded-full font-medium', position.status === 'active' ? 'bg-gradient-to-r from-green-100 to-green-50 text-green-700 border border-green-200' : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 border border-gray-200']">
                  {{ position.status }}
                </span>
              </div>
            </div>
            <span class="inline-block px-3 py-1 text-xs font-medium rounded-full" :class="position.department_color.badge">
              {{ position.department }}
            </span>
          </div>
          <div class="flex space-x-1">
            <button @click="editPosition(position)" class="p-2 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors" title="Edit">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click="deletePosition(position.id)" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ position.description }}</p>
          
          <!-- Salary Range -->
          <div v-if="position.min_salary && position.max_salary" class="flex items-center text-sm text-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 px-3 py-2 rounded-lg">
            <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="font-medium">${{ formatSalary(position.min_salary) }} - ${{ formatSalary(position.max_salary) }}</span>
          </div>

          <!-- Created Info -->
          <div class="flex items-center text-xs text-gray-500 mt-4 pt-4 border-t border-gray-100">
            <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Created {{ formatDate(position.created_at) }} by {{ position.creator?.name || 'System' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="positions.length > 0 && pagination.last_page > 1" class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 p-4 bg-gradient-to-r from-white to-gray-50 rounded-xl shadow-sm border border-gray-200">
      <div class="text-sm text-gray-600">
        Showing {{ positions.length }} of {{ pagination.total }} positions
      </div>
      <div class="flex items-center space-x-2">
        <button @click="prevPage" :disabled="pagination.current_page === 1" class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Previous
        </button>
        <div class="flex items-center space-x-1">
          <span class="px-3 py-1 bg-blue-600 text-white rounded-lg font-medium">{{ pagination.current_page }}</span>
          <span class="text-gray-600">of {{ pagination.last_page }}</span>
        </div>
        <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page" class="px-4 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50 transition-colors flex items-center">
          Next
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Position Wizard Modal -->
    <div v-if="showPositionWizard" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-gradient-to-br from-blue-50/80 to-indigo-50/80 backdrop-blur-sm" @click="closeWizard"></div>
      
      <div class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden animate-modal-appear">
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-6">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-2xl font-bold text-white mb-1">
                {{ editingPosition ? 'Edit Position' : 'Create New Position' }}
              </h2>
              <p class="text-blue-100">Define roles and responsibilities for your team</p>
            </div>
            <button @click="closeWizard" class="p-2 text-white hover:bg-white/20 rounded-full transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Progress Steps -->
          <div class="mt-8 flex items-center justify-center">
            <div class="flex items-center">
              <!-- Step 1 -->
              <div class="flex items-center">
                <div :class="['w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300', 
                  currentStep >= 1 ? 'bg-white border-white text-blue-600' : 'border-blue-300 text-blue-300']">
                  <span class="font-semibold">1</span>
                </div>
                <div :class="['ml-2 text-sm font-medium transition-all duration-300', 
                  currentStep >= 1 ? 'text-white' : 'text-blue-300']">Basic Info</div>
              </div>
              
              <!-- Connector -->
              <div :class="['w-16 h-1 mx-2 transition-all duration-300', 
                currentStep >= 2 ? 'bg-white' : 'bg-blue-300']"></div>
              
              <!-- Step 2 -->
              <div class="flex items-center">
                <div :class="['w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300', 
                  currentStep >= 2 ? 'bg-white border-white text-blue-600' : 'border-blue-300 text-blue-300']">
                  <span class="font-semibold">2</span>
                </div>
                <div :class="['ml-2 text-sm font-medium transition-all duration-300', 
                  currentStep >= 2 ? 'text-white' : 'text-blue-300']">Details</div>
              </div>
              
              <!-- Connector -->
              <div :class="['w-16 h-1 mx-2 transition-all duration-300', 
                currentStep >= 3 ? 'bg-white' : 'bg-blue-300']"></div>
              
              <!-- Step 3 -->
              <div class="flex items-center">
                <div :class="['w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300', 
                  currentStep >= 3 ? 'bg-white border-white text-blue-600' : 'border-blue-300 text-blue-300']">
                  <span class="font-semibold">3</span>
                </div>
                <div :class="['ml-2 text-sm font-medium transition-all duration-300', 
                  currentStep >= 3 ? 'text-white' : 'text-blue-300']">Review</div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Wizard Content -->
        <div class="p-8">
          <!-- Step 1: Basic Information -->
          <div v-if="currentStep === 1" class="space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Position Title & Department</h3>
              <p class="text-gray-600 mb-6">Start by providing the basic information about the position.</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Position Title
                  </label>
                  <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <input v-model="positionForm.title" type="text" required 
                      class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all"
                      placeholder="e.g., Senior Software Engineer, HR Manager">
                  </div>
                  <p class="text-xs text-gray-500 mt-2">Enter a clear and descriptive title for this position</p>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Department
                  </label>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <button v-for="dept in allDepartments.slice(0, 9)" :key="dept"
                      @click="positionForm.department = dept"
                      :class="['px-4 py-3 rounded-lg border transition-all text-sm font-medium', 
                      positionForm.department === dept 
                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' 
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                      {{ dept }}
                    </button>
                    <button @click="positionForm.department = 'other'; showCustomDept = true"
                      :class="['px-4 py-3 rounded-lg border transition-all text-sm font-medium', 
                      positionForm.department === 'other' 
                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' 
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                      + Custom Department
                    </button>
                  </div>
                  
                  <div v-if="showCustomDept" class="mt-4">
                    <input v-model="positionForm.custom_department" type="text" required
                      class="w-full px-4 py-3 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all"
                      placeholder="Enter custom department name...">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Step 2: Details -->
          <div v-if="currentStep === 2" class="space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Position Details</h3>
              <p class="text-gray-600 mb-6">Define the responsibilities and compensation for this role.</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Role Description
                  </label>
                  <textarea v-model="positionForm.description" rows="5" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all resize-none"
                    placeholder="Describe the key responsibilities, requirements, and expectations for this position..."></textarea>
                  <p class="text-xs text-gray-500 mt-2">Be specific about duties, required skills, and daily tasks</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Salary Range</label>
                    <div class="space-y-3">
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                        <input v-model="positionForm.min_salary" type="number" step="0.01" min="0"
                          class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all"
                          placeholder="Minimum salary">
                      </div>
                      <div class="text-center text-gray-400">to</div>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">$</span>
                        <input v-model="positionForm.max_salary" type="number" step="0.01" min="0"
                          class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline-blue transition-all"
                          placeholder="Maximum salary">
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="grid grid-cols-2 gap-3">
                      <button @click="positionForm.status = 'active'"
                        :class="['px-4 py-3 rounded-lg border transition-all text-sm font-medium', 
                        positionForm.status === 'active' 
                          ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-500 text-green-700 ring-2 ring-green-200' 
                          : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                        <div class="flex items-center justify-center">
                          <div class="w-2 h-2 rounded-full bg-green-500 mr-2"></div>
                          Active
                        </div>
                      </button>
                      <button @click="positionForm.status = 'inactive'"
                        :class="['px-4 py-3 rounded-lg border transition-all text-sm font-medium', 
                        positionForm.status === 'inactive' 
                          ? 'bg-gradient-to-r from-gray-100 to-gray-50 border-gray-400 text-gray-700 ring-2 ring-gray-200' 
                          : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                        <div class="flex items-center justify-center">
                          <div class="w-2 h-2 rounded-full bg-gray-500 mr-2"></div>
                          Inactive
                        </div>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Step 3: Review -->
          <div v-if="currentStep === 3" class="space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Review & Confirm</h3>
              <p class="text-gray-600 mb-6">Please review all the information before saving.</p>
              
              <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-6 space-y-4">
                <div class="flex items-start">
                  <div class="w-32 text-sm font-medium text-gray-600">Title:</div>
                  <div class="flex-1 font-medium text-gray-800">{{ positionForm.title }}</div>
                </div>
                <div class="flex items-start">
                  <div class="w-32 text-sm font-medium text-gray-600">Department:</div>
                  <div class="flex-1">
                    <span class="inline-block px-3 py-1 bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 rounded-full text-sm font-medium">
                      {{ positionForm.department === 'other' ? positionForm.custom_department : positionForm.department }}
                    </span>
                  </div>
                </div>
                <div class="flex items-start">
                  <div class="w-32 text-sm font-medium text-gray-600">Status:</div>
                  <div class="flex-1">
                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', 
                      positionForm.status === 'active' 
                        ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-700' 
                        : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700']">
                      <span :class="['w-2 h-2 rounded-full mr-2', 
                        positionForm.status === 'active' ? 'bg-green-500' : 'bg-gray-500']"></span>
                      {{ positionForm.status }}
                    </span>
                  </div>
                </div>
                <div v-if="positionForm.min_salary || positionForm.max_salary" class="flex items-start">
                  <div class="w-32 text-sm font-medium text-gray-600">Salary Range:</div>
                  <div class="flex-1 font-medium text-gray-800">
                    <span v-if="positionForm.min_salary && positionForm.max_salary">
                      ${{ formatSalary(positionForm.min_salary) }} - ${{ formatSalary(positionForm.max_salary) }}
                    </span>
                    <span v-else-if="positionForm.min_salary">
                      From ${{ formatSalary(positionForm.min_salary) }}
                    </span>
                    <span v-else-if="positionForm.max_salary">
                      Up to ${{ formatSalary(positionForm.max_salary) }}
                    </span>
                  </div>
                </div>
                <div class="flex items-start">
                  <div class="w-32 text-sm font-medium text-gray-600">Description:</div>
                  <div class="flex-1 text-gray-700">{{ positionForm.description }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Footer Navigation -->
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200">
          <div class="flex justify-between items-center">
            <div>
              <button v-if="currentStep > 1" @click="previousStep" 
                class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
              </button>
            </div>
            
            <div class="flex items-center space-x-3">
              <button v-if="currentStep < 3" @click="nextStep" 
                :disabled="!canProceed"
                :class="['px-6 py-2.5 rounded-lg transition-all flex items-center font-medium', 
                canProceed 
                  ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 shadow-md hover:shadow-lg' 
                  : 'bg-gray-200 text-gray-500 cursor-not-allowed']">
                Continue
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </button>
              
              <button v-if="currentStep === 3" @click="savePosition" :disabled="saving"
                :class="['px-6 py-2.5 rounded-lg transition-all flex items-center font-medium', 
                saving 
                  ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white' 
                  : 'bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 shadow-md hover:shadow-lg']">
                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ saving ? 'Saving...' : (editingPosition ? 'Update Position' : 'Create Position') }}</span>
              </button>
              
              <button @click="closeWizard" class="px-5 py-2.5 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div v-if="showSuccessToast" class="fixed bottom-6 right-6 z-50">
      <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-4 rounded-xl shadow-xl flex items-center space-x-3 animate-toast-slide">
        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <div class="font-medium">{{ successMessage }}</div>
      </div>
    </div>

    <!-- Error Toast -->
    <div v-if="showErrorToast" class="fixed bottom-6 right-6 z-50">
      <div class="bg-gradient-to-r from-red-500 to-pink-600 text-white px-6 py-4 rounded-xl shadow-xl flex items-center space-x-3 animate-toast-slide">
        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.17 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <div class="font-medium">{{ errorMessage }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '@/utils/axios'

const showPositionWizard = ref(false)
const currentStep = ref(1)
const editingPosition = ref(null)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const showSuccessToast = ref(false)
const showErrorToast = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const searchQuery = ref('')
const selectedDepartment = ref('all')
const selectedStatus = ref('active')
const showCustomDept = ref(false)
let searchTimeout = null

// Default departments
const defaultDepartments = [
  'Administration',
  'Human Resources',
  'Finance',
  'Distributor Assistant',
  'Operations',
  'Logistics',
  'IT',
  'Sales',
  'Marketing'
]

const positions = ref([])
const departments = ref([])
const statistics = ref({})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const positionForm = ref({
  title: '',
  department: '',
  custom_department: '',
  description: '',
  min_salary: '',
  max_salary: '',
  status: 'active'
})

// Computed properties
const allDepartments = computed(() => {
  const existingDepts = departments.value.map(d => d.name)
  const uniqueDepts = [...new Set([...defaultDepartments, ...existingDepts])]
  return uniqueDepts.sort()
})

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return positionForm.value.title.trim() && 
             (positionForm.value.department || 
              (positionForm.value.department === 'other' && positionForm.value.custom_department.trim()))
    case 2:
      return positionForm.value.description.trim()
    default:
      return true
  }
})

// Methods
const fetchPositions = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      department: selectedDepartment.value !== 'all' ? selectedDepartment.value : undefined,
      status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
      search: searchQuery.value || undefined
    }

    const response = await axios.get('/hr/positions', { params })
    
    if (response.data.success) {
      positions.value = response.data.data.data
      departments.value = response.data.departments
      statistics.value = response.data.statistics
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total
      }
    } else {
      error.value = response.data.message || 'Failed to load positions'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Network error occurred'
    console.error('Error fetching positions:', err)
  } finally {
    loading.value = false
  }
}

const searchPositions = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchPositions()
  }, 500)
}

const fetchStatistics = async () => {
  try {
    const response = await axios.get('/hr/positions/statistics')
    if (response.data.success) {
      statistics.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to fetch statistics:', err)
  }
}

const formatSalary = (salary) => {
  if (!salary) return '0'
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(salary)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const startNewPosition = () => {
  editingPosition.value = null
  currentStep.value = 1
  showCustomDept.value = false
  positionForm.value = {
    title: '',
    department: '',
    custom_department: '',
    description: '',
    min_salary: '',
    max_salary: '',
    status: 'active'
  }
  showPositionWizard.value = true
}

const editPosition = (position) => {
  editingPosition.value = position
  currentStep.value = 1
  showCustomDept.value = false
  positionForm.value = {
    title: position.title,
    department: position.department,
    custom_department: '',
    description: position.description,
    min_salary: position.min_salary,
    max_salary: position.max_salary,
    status: position.status
  }
  showPositionWizard.value = true
}

const deletePosition = async (positionId) => {
  if (!confirm('Are you sure you want to delete this position? This action cannot be undone.')) {
    return
  }

  try {
    const response = await axios.delete(`/hr/positions/${positionId}`)
    
    if (response.data.success) {
      showToast('Position deleted successfully', 'success')
      fetchPositions()
      fetchStatistics()
    } else {
      showToast(response.data.message || 'Failed to delete position', 'error')
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Failed to delete position', 'error')
  }
}

const nextStep = () => {
  if (currentStep.value < 3 && canProceed.value) {
    currentStep.value++
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const closeWizard = () => {
  showPositionWizard.value = false
  currentStep.value = 1
  editingPosition.value = null
  showCustomDept.value = false
  positionForm.value = {
    title: '',
    department: '',
    custom_department: '',
    description: '',
    min_salary: '',
    max_salary: '',
    status: 'active'
  }
}

const savePosition = async () => {
  saving.value = true
  
  try {
    // Validate salary range
    if (positionForm.value.min_salary && positionForm.value.max_salary) {
      const min = parseFloat(positionForm.value.min_salary)
      const max = parseFloat(positionForm.value.max_salary)
      if (max <= min) {
        showToast('Maximum salary must be greater than minimum salary', 'error')
        saving.value = false
        return
      }
    }

    // Determine department
    const department = positionForm.value.department === 'other' 
      ? positionForm.value.custom_department 
      : positionForm.value.department

    const payload = {
      ...positionForm.value,
      department: department
    }

    // Remove custom_department from payload
    delete payload.custom_department

    // Convert empty strings to null for salary fields
    if (payload.min_salary === '') payload.min_salary = null
    if (payload.max_salary === '') payload.max_salary = null

    let response
    if (editingPosition.value) {
      response = await axios.put(`/hr/positions/${editingPosition.value.id}`, payload)
    } else {
      response = await axios.post('/hr/positions', payload)
    }

    if (response.data.success) {
      showToast(
        editingPosition.value ? 'Position updated successfully' : 'Position created successfully',
        'success'
      )
      closeWizard()
      fetchPositions()
      fetchStatistics()
    } else {
      showToast(response.data.message || 'Failed to save position', 'error')
    }
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'Failed to save position'
    if (err.response?.data?.errors) {
      // Handle validation errors
      const errors = Object.values(err.response.data.errors).flat()
      showToast(errors.join(', '), 'error')
    } else {
      showToast(errorMsg, 'error')
    }
  } finally {
    saving.value = false
  }
}

const showToast = (message, type = 'success') => {
  if (type === 'success') {
    successMessage.value = message
    showSuccessToast.value = true
    setTimeout(() => {
      showSuccessToast.value = false
    }, 3000)
  } else {
    errorMessage.value = message
    showErrorToast.value = true
    setTimeout(() => {
      showErrorToast.value = false
    }, 3000)
  }
}

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++
    fetchPositions()
  }
}

const prevPage = () => {
  if (pagination.value.current_page > 1) {
    pagination.value.current_page--
    fetchPositions()
  }
}

// Lifecycle hooks
onMounted(() => {
  fetchPositions()
  fetchStatistics()
})
</script>

<style scoped>
.positions-roles {
  min-height: calc(100vh - 80px);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes modal-appear {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

@keyframes toast-slide {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.animate-modal-appear {
  animation: modal-appear 0.3s ease-out;
}

.animate-toast-slide {
  animation: toast-slide 0.3s ease-out;
}

/* Custom scrollbar for modal */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}
</style>