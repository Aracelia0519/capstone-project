<template>
  <div class="employees-list p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Employees</h1>
        <p class="text-gray-600">Manage employee records and profiles</p>
        <div v-if="companyInfo" class="mt-2">
          <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            {{ companyInfo.company_name }}
          </span>
        </div>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <button @click="showAddModal = true" class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Employee
        </button>
        <button @click="exportToCSV" class="flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm hover:shadow">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export
        </button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-gradient-to-br from-blue-100 to-blue-50">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-600">Total Employees</p>
            <p class="text-2xl font-bold text-gray-800">{{ statistics.total_employees }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-gradient-to-br from-green-100 to-green-50">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-600">Active</p>
            <p class="text-2xl font-bold text-gray-800">{{ statistics.active_employees }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-gradient-to-br from-yellow-100 to-yellow-50">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-600">Probationary</p>
            <p class="text-2xl font-bold text-gray-800">{{ statistics.probationary_employees }}</p>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition-shadow duration-300">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-gradient-to-br from-purple-100 to-purple-50">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm text-gray-600">Departments</p>
            <p class="text-2xl font-bold text-gray-800">{{ statistics.department_distribution?.length || 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
      <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
        <div class="flex-1">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input v-model="searchQuery" type="text" placeholder="Search employees..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline">
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <select v-model="departmentFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline min-w-[150px]">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
          <select v-model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline min-w-[150px]">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="on_leave">On Leave</option>
          </select>
          <select v-model="employmentStatusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline min-w-[150px]">
            <option value="">All Employment Status</option>
            <option value="probationary">Probationary</option>
            <option value="regular">Regular</option>
            <option value="contractual">Contractual</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-xl p-6 mb-6">
      <div class="flex items-center">
        <svg class="w-6 h-6 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
          <h3 class="text-lg font-medium text-red-800">Error Loading Employees</h3>
          <p class="text-red-700">{{ error }}</p>
          <button @click="fetchEmployees" class="mt-3 px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all">
            Try Again
          </button>
        </div>
      </div>
    </div>

    <!-- Employee Table -->
    <div v-else class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employment Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employee in paginatedEmployees" :key="employee.id" class="hover:bg-gray-50 transition-colors duration-200">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-medium text-gray-900">{{ employee.employee_code }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center shadow-sm">
                      <span class="text-blue-600 font-medium">{{ getInitials(employee.full_name) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ employee.full_name }}</div>
                    <div class="text-sm text-gray-500">{{ employee.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ employee.position }}</div>
                <div class="text-xs text-gray-500">{{ employee.job_title }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 text-xs rounded-full shadow-sm" :class="getDeptColor(employee.department)">
                  {{ employee.department }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 text-xs rounded-full font-medium shadow-sm" :class="getEmploymentStatusClass(employee.employment_status)">
                  {{ employee.employment_status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 text-xs rounded-full font-medium shadow-sm" :class="getStatusClass(employee.status)">
                  {{ employee.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-3">
                  <button @click="viewEmployee(employee.id)" class="text-blue-600 hover:text-blue-900 transform hover:scale-110 transition-transform" title="View Details">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editEmployee(employee.id)" class="text-yellow-600 hover:text-yellow-900 transform hover:scale-110 transition-transform" title="Edit">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button v-if="employee.employment_status === 'probationary'" @click="regularizeEmployee(employee.id)" class="text-green-600 hover:text-green-900 transform hover:scale-110 transition-transform" title="Regularize">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button @click="uploadDocument(employee.id)" class="text-purple-600 hover:text-purple-900 transform hover:scale-110 transition-transform" title="Upload Documents">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredEmployees.length === 0" class="py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No employees found</h3>
        <p class="mt-2 text-gray-500">Get started by creating your first employee.</p>
        <div class="mt-6">
          <button @click="showAddModal = true" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Employee
          </button>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredEmployees.length > 0" class="bg-white px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="mb-4 md:mb-0">
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to 
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredEmployees.length) }}</span> of 
              <span class="font-medium">{{ filteredEmployees.length }}</span> results
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 hover:shadow disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
              Previous
            </button>
            <span class="text-sm text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 hover:shadow disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Employee Wizard Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-2 sm:p-4 z-50 overflow-y-auto">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <!-- Wizard Header -->
          <div class="flex items-center justify-between mb-6 sticky top-0 bg-white pb-4 border-b z-10">
            <div>
              <h3 class="text-lg sm:text-xl font-bold text-gray-800">Add New Employee</h3>
              <p class="text-sm text-gray-600">Step {{ currentStep }} of {{ steps.length }}</p>
            </div>
            <button @click="closeAddModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Wizard Progress Bar -->
          <div class="mb-8">
            <div class="flex justify-between items-center mb-2">
              <div class="flex-1">
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div 
                    class="h-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500 ease-out"
                    :style="{ width: `${((currentStep - 1) / (steps.length - 1)) * 100}%` }"
                  ></div>
                </div>
              </div>
              <span class="ml-4 text-sm font-medium text-blue-600 whitespace-nowrap">
                {{ Math.round(((currentStep - 1) / (steps.length - 1)) * 100) }}% Complete
              </span>
            </div>
            
            <!-- Wizard Steps -->
            <div class="flex justify-between mt-6 overflow-x-auto pb-2">
              <div 
                v-for="(step, index) in steps" 
                :key="step.name"
                @click="goToStep(index + 1)"
                class="flex flex-col items-center cursor-pointer group min-w-[80px] sm:min-w-0"
                :class="{ 'opacity-50': currentStep < index + 1 }"
              >
                <div 
                  class="w-10 h-10 rounded-full flex items-center justify-center mb-2 transition-all duration-300"
                  :class="[
                    currentStep > index + 1 
                      ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-lg' 
                      : currentStep === index + 1 
                      ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg ring-4 ring-blue-100' 
                      : 'bg-gray-100 text-gray-400'
                  ]"
                >
                  <svg v-if="currentStep > index + 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span v-else class="font-medium">{{ index + 1 }}</span>
                </div>
                <span 
                  class="text-xs font-medium text-center transition-colors"
                  :class="[
                    currentStep > index + 1 
                      ? 'text-green-600' 
                      : currentStep === index + 1 
                      ? 'text-blue-600 font-semibold' 
                      : 'text-gray-500'
                  ]"
                >
                  {{ step.name }}
                </span>
              </div>
            </div>
          </div>

          <!-- Wizard Content -->
          <form @submit.prevent="saveEmployee" class="space-y-6" enctype="multipart/form-data">
            <!-- Step 1: Basic Information -->
            <div v-if="currentStep === 1" class="space-y-6 animate-fade-in">
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-100">
                <h4 class="font-bold text-lg text-blue-800 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Basic Information
                </h4>
                <p class="text-blue-700 text-sm mb-4">Start by providing the employee's basic personal details.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      First Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.first_name" 
                      type="text" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="Enter first name"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Middle Name
                    </label>
                    <input 
                      v-model="newEmployee.middle_name" 
                      type="text" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="Enter middle name"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Last Name <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.last_name" 
                      type="text" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="Enter last name"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.email" 
                      type="email" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="employee@company.com"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Phone <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.phone" 
                      type="tel" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="+63 912 345 6789"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Emergency Contact
                    </label>
                    <input 
                      v-model="newEmployee.emergency_contact" 
                      type="tel" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="Emergency contact number"
                    >
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Details -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                  <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Personal Details
                  </h5>
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Date of Birth <span class="text-red-500">*</span>
                      </label>
                      <input 
                        v-model="newEmployee.date_of_birth" 
                        type="date" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      >
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Gender <span class="text-red-500">*</span>
                      </label>
                      <select 
                        v-model="newEmployee.gender" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      >
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Marital Status <span class="text-red-500">*</span>
                      </label>
                      <select 
                        v-model="newEmployee.marital_status" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      >
                        <option value="">Select Status</option>
                        <option value="single">Single</option>
                        <option value="married">Married</option>
                        <option value="widowed">Widowed</option>
                        <option value="separated">Separated</option>
                        <option value="divorced">Divorced</option>
                      </select>
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Nationality <span class="text-red-500">*</span>
                      </label>
                      <input 
                        v-model="newEmployee.nationality" 
                        type="text" 
                        required 
                        value="Filipino" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      >
                    </div>
                  </div>
                </div>

                <!-- Address Information -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                  <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Address Information
                  </h5>
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Address <span class="text-red-500">*</span>
                      </label>
                      <textarea 
                        v-model="newEmployee.address" 
                        rows="3" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline resize-none"
                        placeholder="Enter complete address"
                      ></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Employment Details -->
            <div v-if="currentStep === 2" class="space-y-6 animate-fade-in">
              <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-5 rounded-xl border border-green-100">
                <h4 class="font-bold text-lg text-green-800 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  Employment Details
                </h4>
                <p class="text-green-700 text-sm mb-4">Provide the employee's job role, department, and employment terms.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Department <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.department" 
                      type="text" 
                      required 
                      placeholder="e.g., Human Resources" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Position <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.position" 
                      type="text" 
                      required 
                      placeholder="e.g., HR Staff" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Job Title <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.job_title" 
                      type="text" 
                      required 
                      placeholder="e.g., Human Resources Specialist" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Employment Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                      v-model="newEmployee.employment_type" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                      <option value="">Select Type</option>
                      <option value="full_time">Full Time</option>
                      <option value="part_time">Part Time</option>
                      <option value="contract">Contract</option>
                      <option value="probationary">Probationary</option>
                      <option value="intern">Intern</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Employment Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                      v-model="newEmployee.employment_status" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                      <option value="probationary">Probationary</option>
                      <option value="regular">Regular</option>
                      <option value="contractual">Contractual</option>
                      <option value="resigned">Resigned</option>
                      <option value="terminated">Terminated</option>
                      <option value="retired">Retired</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Hire Date <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.hire_date" 
                      type="date" 
                      required 
                      :max="today" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                  </div>
                  <div v-if="newEmployee.employment_status === 'probationary'" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Probation End Date
                    </label>
                    <input 
                      v-model="newEmployee.probation_end_date" 
                      type="date" 
                      :min="newEmployee.hire_date" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                  </div>
                </div>
              </div>

              <!-- Salary Information -->
              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Salary & Compensation
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Salary <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-2">
                      <select 
                        v-model="newEmployee.salary_currency" 
                        class="px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      >
                        <option value="PHP">PHP</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                      </select>
                      <input 
                        v-model="newEmployee.salary" 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        required 
                        placeholder="0.00" 
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      >
                    </div>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Payment Frequency <span class="text-red-500">*</span>
                    </label>
                    <select 
                      v-model="newEmployee.payment_frequency" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                      <option value="">Select Frequency</option>
                      <option value="monthly">Monthly</option>
                      <option value="bi_monthly">Bi-Monthly</option>
                      <option value="weekly">Weekly</option>
                      <option value="daily">Daily</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 3: Financial & Government -->
            <div v-if="currentStep === 3" class="space-y-6 animate-fade-in">
              <div class="bg-gradient-to-r from-purple-50 to-violet-50 p-5 rounded-xl border border-purple-100">
                <h4 class="font-bold text-lg text-purple-800 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                  </svg>
                  Financial & Government Information
                </h4>
                <p class="text-purple-700 text-sm mb-4">Provide bank details and government identification numbers.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Bank Details -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                  <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Bank Details
                  </h5>
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Bank Name
                      </label>
                      <input 
                        v-model="newEmployee.bank_name" 
                        type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                        placeholder="e.g., BDO, BPI, Metrobank"
                      >
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Account Number
                      </label>
                      <input 
                        v-model="newEmployee.bank_account_number" 
                        type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                        placeholder="Enter account number"
                      >
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Account Name
                      </label>
                      <input 
                        v-model="newEmployee.bank_account_name" 
                        type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                        placeholder="Account holder name"
                      >
                    </div>
                  </div>
                </div>

                <!-- Government Numbers -->
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                  <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Government Numbers
                  </h5>
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        SSS Number
                      </label>
                      <input 
                        v-model="newEmployee.sss_number" 
                        type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                        placeholder="XX-XXXXXXX-X"
                      >
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        PhilHealth Number
                      </label>
                      <input 
                        v-model="newEmployee.philhealth_number" 
                        type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                        placeholder="XX-XXXXXXXXX-X"
                      >
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        Pag-IBIG Number
                      </label>
                      <input 
                        v-model="newEmployee.pagibig_number" 
                        type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                        placeholder="XXXX-XXXX-XXXX"
                      >
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">
                        TIN Number
                      </label>
                      <input 
                        v-model="newEmployee.tin_number" 
                        type="text" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                        placeholder="XXX-XXX-XXX-XXXXX"
                      >
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 4: Identification & Education -->
            <div v-if="currentStep === 4" class="space-y-6 animate-fade-in">
              <div class="bg-gradient-to-r from-yellow-50 to-amber-50 p-5 rounded-xl border border-yellow-100">
                <h4 class="font-bold text-lg text-yellow-800 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Identification & Education
                </h4>
                <p class="text-yellow-700 text-sm mb-4">Provide identification documents and educational background.</p>
              </div>

              <!-- Identification -->
              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                  </svg>
                  Identification
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Valid ID Type <span class="text-red-500">*</span>
                    </label>
                    <select 
                      v-model="newEmployee.valid_id_type" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                      <option value="">Select ID Type</option>
                      <option value="passport">Passport</option>
                      <option value="driver_license">Driver's License</option>
                      <option value="sss">SSS ID</option>
                      <option value="philhealth">PhilHealth ID</option>
                      <option value="pagibig">Pag-IBIG ID</option>
                      <option value="umid">UMID</option>
                      <option value="voter_id">Voter's ID</option>
                      <option value="prc">PRC ID</option>
                      <option value="postal">Postal ID</option>
                      <option value="tin">TIN ID</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      ID Number <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.id_number" 
                      type="text" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="Enter ID number"
                    >
                  </div>
                  <div class="space-y-2 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Valid ID Photo (Image or PDF)
                    </label>
                    <div class="flex items-center space-x-3">
                      <input 
                        type="file" 
                        @change="handleFileUpload($event, 'valid_id_photo')" 
                        accept="image/*,.pdf" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      >
                      <div class="text-xs text-gray-500 whitespace-nowrap">
                        Max 5MB
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Educational Background -->
              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                  </svg>
                  Educational Background
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Educational Attainment <span class="text-red-500">*</span>
                    </label>
                    <select 
                      v-model="newEmployee.educational_attainment" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                      <option value="">Select Attainment</option>
                      <option value="elementary">Elementary</option>
                      <option value="high_school">High School</option>
                      <option value="vocational">Vocational</option>
                      <option value="college">College</option>
                      <option value="post_graduate">Post Graduate</option>
                      <option value="doctorate">Doctorate</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      School Graduated <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.school_graduated" 
                      type="text" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="University or School name"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Year Graduated <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.year_graduated" 
                      type="number" 
                      min="1900" 
                      :max="currentYear" 
                      required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                      placeholder="Year"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Course <span class="text-red-500">*</span>
                    </label>
                    <input 
                      v-model="newEmployee.course" 
                      type="text" 
                      required 
                      placeholder="e.g., Bachelor of Science in Business Administration" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 5: Documents & Review -->
            <div v-if="currentStep === 5" class="space-y-6 animate-fade-in">
              <div class="bg-gradient-to-r from-indigo-50 to-blue-50 p-5 rounded-xl border border-indigo-100">
                <h4 class="font-bold text-lg text-indigo-800 mb-3 flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Documents & Final Review
                </h4>
                <p class="text-indigo-700 text-sm mb-4">Upload additional documents and review employee information.</p>
              </div>

              <!-- Company Information -->
              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-200">
                <h5 class="font-semibold text-blue-800 mb-3 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Company Information
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-blue-700 mb-1">Company</label>
                    <input 
                      :value="companyInfo?.company_name || 'Loading...'" 
                      type="text" 
                      disabled 
                      class="w-full px-4 py-3 bg-white border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                    >
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-blue-700 mb-1">Created By</label>
                    <input 
                      :value="userName" 
                      type="text" 
                      disabled 
                      class="w-full px-4 py-3 bg-white border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm"
                    >
                  </div>
                </div>
              </div>

              <!-- Documents Upload -->
              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Additional Documents (Optional)
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Resume
                    </label>
                    <input 
                      type="file" 
                      @change="handleFileUpload($event, 'resume')" 
                      accept=".pdf,.doc,.docx" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                    <p class="text-xs text-gray-500">PDF, DOC, DOCX</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Employment Contract
                    </label>
                    <input 
                      type="file" 
                      @change="handleFileUpload($event, 'employment_contract')" 
                      accept=".pdf" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                    <p class="text-xs text-gray-500">PDF only</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Medical Certificate
                    </label>
                    <input 
                      type="file" 
                      @change="handleFileUpload($event, 'medical_certificate')" 
                      accept="image/*,.pdf" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                    <p class="text-xs text-gray-500">Image or PDF</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      NBI Clearance
                    </label>
                    <input 
                      type="file" 
                      @change="handleFileUpload($event, 'nbi_clearance')" 
                      accept="image/*,.pdf" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                    <p class="text-xs text-gray-500">Image or PDF</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Police Clearance
                    </label>
                    <input 
                      type="file" 
                      @change="handleFileUpload($event, 'police_clearance')" 
                      accept="image/*,.pdf" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline"
                    >
                    <p class="text-xs text-gray-500">Image or PDF</p>
                  </div>
                </div>
              </div>

              <!-- Notes -->
              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                  </svg>
                  Additional Notes
                </h5>
                <div class="space-y-2">
                  <textarea 
                    v-model="newEmployee.notes" 
                    rows="4" 
                    placeholder="Any additional notes or comments about the employee..." 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline resize-none"
                  ></textarea>
                  <p class="text-xs text-gray-500">Optional: Add any special instructions, remarks, or important information.</p>
                </div>
              </div>

              <!-- Review Summary -->
              <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-5 rounded-xl border border-green-200">
                <h5 class="font-semibold text-green-800 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Review Summary
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <p class="text-sm text-gray-600">Employee Name:</p>
                    <p class="font-medium text-gray-800">{{ newEmployee.first_name }} {{ newEmployee.middle_name }} {{ newEmployee.last_name }}</p>
                  </div>
                  <div class="space-y-2">
                    <p class="text-sm text-gray-600">Department & Position:</p>
                    <p class="font-medium text-gray-800">{{ newEmployee.department }} - {{ newEmployee.position }}</p>
                  </div>
                  <div class="space-y-2">
                    <p class="text-sm text-gray-600">Employment Type:</p>
                    <p class="font-medium text-gray-800">{{ newEmployee.employment_type }} ({{ newEmployee.employment_status }})</p>
                  </div>
                  <div class="space-y-2">
                    <p class="text-sm text-gray-600">Salary:</p>
                    <p class="font-medium text-gray-800">{{ newEmployee.salary_currency }} {{ formatCurrency(newEmployee.salary) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>

        <!-- Wizard Navigation -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div class="text-sm text-gray-600">
              Step {{ currentStep }} of {{ steps.length }}: {{ steps[currentStep - 1].description }}
            </div>
            <div class="flex space-x-3 w-full sm:w-auto">
              <button 
                v-if="currentStep > 1"
                type="button" 
                @click="prevStep" 
                class="flex-1 sm:flex-none px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm hover:shadow"
              >
                Previous
              </button>
              <button 
                v-if="currentStep < steps.length"
                type="button" 
                @click="nextStep" 
                class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg"
              >
                Continue to {{ steps[currentStep].name }}
              </button>
              <button 
                v-if="currentStep === steps.length"
                type="submit" 
                @click="saveEmployee"
                :disabled="saving" 
                class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
              >
                <svg v-if="saving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ saving ? 'Creating Employee...' : 'Create Employee' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios'
import { getCurrentUser } from '@/utils/auth'

const router = useRouter()

// State
const searchQuery = ref('')
const departmentFilter = ref('')
const statusFilter = ref('')
const employmentStatusFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = 10
const showAddModal = ref(false)
const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const employees = ref([])
const departments = ref([])
const statistics = ref(null)
const companyInfo = ref(null)
const userInfo = ref(null)
const currentStep = ref(1)

const today = computed(() => new Date().toISOString().split('T')[0])
const currentYear = new Date().getFullYear()

// Wizard steps configuration
const steps = [
  { name: 'Basic Info', description: 'Personal Information' },
  { name: 'Employment', description: 'Job Details' },
  { name: 'Financial', description: 'Bank & Government' },
  { name: 'Documents', description: 'ID & Education' },
  { name: 'Review', description: 'Final Review' }
]

// Get user info on component mount
onMounted(async () => {
  try {
    userInfo.value = await getCurrentUser()
    fetchEmployees()
  } catch (err) {
    console.error('Failed to get user info:', err)
  }
})

const userName = computed(() => {
  if (!userInfo.value) return ''
  return `${userInfo.value.first_name || ''} ${userInfo.value.last_name || ''}`.trim()
})

// New employee form
const newEmployee = reactive({
  // Personal Information
  first_name: '',
  middle_name: '',
  last_name: '',
  email: '',
  phone: '',
  emergency_contact: '',
  address: '',
  date_of_birth: '',
  gender: 'male',
  marital_status: 'single',
  nationality: 'Filipino',
  
  // Employment Details
  department: '',
  position: '',
  job_title: '',
  employment_type: 'full_time',
  employment_status: 'probationary',
  hire_date: today.value,
  probation_end_date: '',
  regularization_date: '',
  salary: 0,
  salary_currency: 'PHP',
  payment_frequency: 'monthly',
  
  // Bank Details
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  
  // Government Numbers
  sss_number: '',
  philhealth_number: '',
  pagibig_number: '',
  tin_number: '',
  
  // Identification
  valid_id_type: '',
  id_number: '',
  
  // Educational Background
  educational_attainment: '',
  school_graduated: '',
  year_graduated: currentYear,
  course: '',
  
  // Files (will be handled separately)
  valid_id_photo: null,
  resume: null,
  employment_contract: null,
  medical_certificate: null,
  nbi_clearance: null,
  police_clearance: null,
  
  notes: ''
})

// Wizard navigation
const nextStep = () => {
  if (currentStep.value < steps.length) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const goToStep = (step) => {
  if (step >= 1 && step <= steps.length) {
    currentStep.value = step
  }
}

// Helper function to format currency
const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

// File upload handling
const handleFileUpload = (event, field) => {
  const file = event.target.files[0]
  if (file) {
    // Check file size (5MB limit)
    if (file.size > 5 * 1024 * 1024) {
      alert('File size exceeds 5MB limit')
      event.target.value = ''
      return
    }
    newEmployee[field] = file
  }
}

// Computed properties
const filteredEmployees = computed(() => {
  let filtered = employees.value
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(emp => 
      emp.full_name?.toLowerCase().includes(query) ||
      emp.email?.toLowerCase().includes(query) ||
      emp.employee_code?.toLowerCase().includes(query) ||
      emp.phone?.toLowerCase().includes(query)
    )
  }
  
  if (departmentFilter.value) {
    filtered = filtered.filter(emp => emp.department === departmentFilter.value)
  }
  
  if (statusFilter.value) {
    filtered = filtered.filter(emp => emp.status === statusFilter.value)
  }
  
  if (employmentStatusFilter.value) {
    filtered = filtered.filter(emp => emp.employment_status === employmentStatusFilter.value)
  }
  
  return filtered
})

const totalPages = computed(() => Math.ceil(filteredEmployees.value.length / itemsPerPage))

const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredEmployees.value.slice(start, end)
})

// Helper functions
const getInitials = (name) => {
  if (!name) return '??'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

const getDeptColor = (dept) => {
  const colors = {
    'Human Resources': 'bg-gradient-to-r from-green-100 to-green-50 text-green-800',
    'Finance': 'bg-gradient-to-r from-purple-100 to-purple-50 text-purple-800',
    'Logistics': 'bg-gradient-to-r from-yellow-100 to-yellow-50 text-yellow-800',
    'Operations': 'bg-gradient-to-r from-red-100 to-red-50 text-red-800',
    'Sales': 'bg-gradient-to-r from-blue-100 to-blue-50 text-blue-800',
    'Marketing': 'bg-gradient-to-r from-pink-100 to-pink-50 text-pink-800',
    'IT': 'bg-gradient-to-r from-indigo-100 to-indigo-50 text-indigo-800',
    'Administration': 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-800'
  }
  return colors[dept] || 'bg-gradient-to-r from-blue-100 to-blue-50 text-blue-800'
}

const getEmploymentStatusClass = (status) => {
  const classes = {
    'probationary': 'bg-gradient-to-r from-yellow-100 to-yellow-50 text-yellow-800',
    'regular': 'bg-gradient-to-r from-green-100 to-green-50 text-green-800',
    'contractual': 'bg-gradient-to-r from-blue-100 to-blue-50 text-blue-800',
    'resigned': 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-800',
    'terminated': 'bg-gradient-to-r from-red-100 to-red-50 text-red-800',
    'retired': 'bg-gradient-to-r from-purple-100 to-purple-50 text-purple-800'
  }
  return classes[status] || 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    'active': 'bg-gradient-to-r from-green-100 to-green-50 text-green-800',
    'inactive': 'bg-gradient-to-r from-red-100 to-red-50 text-red-800',
    'on_leave': 'bg-gradient-to-r from-yellow-100 to-yellow-50 text-yellow-800'
  }
  return classes[status] || 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-800'
}

// API functions
const fetchEmployees = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Fetch employees
    const response = await axios.get('/hr/employees')
    if (response.data.status === 'success') {
      employees.value = response.data.data.employees
      departments.value = response.data.data.departments
    }
    
    // Fetch statistics
    const statsResponse = await axios.get('/hr/employees/statistics')
    if (statsResponse.data.status === 'success') {
      statistics.value = statsResponse.data.data.statistics
      companyInfo.value = {
        company_name: statsResponse.data.data.company_name
      }
    }
  } catch (err) {
    console.error('Failed to fetch employees:', err)
    error.value = err.response?.data?.message || 'Failed to load employees'
  } finally {
    loading.value = false
  }
}

const saveEmployee = async () => {
  saving.value = true
  
  try {
    // Create FormData for file uploads
    const formData = new FormData()
    
    // Add all employee data to FormData
    Object.keys(newEmployee).forEach(key => {
      if (key === 'valid_id_photo' || key === 'resume' || key === 'employment_contract' || 
          key === 'medical_certificate' || key === 'nbi_clearance' || key === 'police_clearance') {
        // Handle files separately
        if (newEmployee[key] instanceof File) {
          formData.append(key, newEmployee[key])
        }
      } else {
        // Handle regular fields
        if (newEmployee[key] !== null && newEmployee[key] !== undefined) {
          formData.append(key, newEmployee[key])
        }
      }
    })
    
    const response = await axios.post('/hr/employees', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    if (response.data.status === 'success') {
      // Show success message
      alert('Employee created successfully!')
      
      // Reset form and wizard
      Object.keys(newEmployee).forEach(key => {
        if (key === 'gender') newEmployee[key] = 'male'
        else if (key === 'marital_status') newEmployee[key] = 'single'
        else if (key === 'nationality') newEmployee[key] = 'Filipino'
        else if (key === 'employment_type') newEmployee[key] = 'full_time'
        else if (key === 'employment_status') newEmployee[key] = 'probationary'
        else if (key === 'hire_date') newEmployee[key] = today.value
        else if (key === 'salary_currency') newEmployee[key] = 'PHP'
        else if (key === 'payment_frequency') newEmployee[key] = 'monthly'
        else if (key === 'year_graduated') newEmployee[key] = currentYear
        else if (key === 'salary') newEmployee[key] = 0
        else newEmployee[key] = ''
      })
      
      // Reset wizard to first step
      currentStep.value = 1
      
      // Close modal and refresh data
      showAddModal.value = false
      fetchEmployees()
    }
  } catch (err) {
    console.error('Failed to create employee:', err)
    const errorMsg = err.response?.data?.message || err.response?.data?.errors || 'Failed to create employee'
    alert(typeof errorMsg === 'object' ? JSON.stringify(errorMsg) : errorMsg)
  } finally {
    saving.value = false
  }
}

const viewEmployee = (id) => {
  router.push(`/hr/employee/${id}`)
}

const editEmployee = (id) => {
  // For now, just show a message
  const employee = employees.value.find(emp => emp.id === id)
  if (employee) {
    alert(`Edit employee: ${employee.full_name} (ID: ${employee.employee_code})`)
  }
}

const regularizeEmployee = async (id) => {
  if (confirm('Are you sure you want to regularize this employee?')) {
    try {
      const response = await axios.post(`/hr/employees/${id}/regularize`)
      if (response.data.status === 'success') {
        alert('Employee regularized successfully!')
        fetchEmployees()
      }
    } catch (err) {
      console.error('Failed to regularize employee:', err)
      alert(err.response?.data?.message || 'Failed to regularize employee')
    }
  }
}

const uploadDocument = (id) => {
  const employee = employees.value.find(emp => emp.id === id)
  if (employee) {
    alert(`Upload document for: ${employee.full_name}`)
  }
}

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++
}

const closeAddModal = () => {
  showAddModal.value = false
  currentStep.value = 1
  
  // Reset form
  Object.keys(newEmployee).forEach(key => {
    if (key === 'gender') newEmployee[key] = 'male'
    else if (key === 'marital_status') newEmployee[key] = 'single'
    else if (key === 'nationality') newEmployee[key] = 'Filipino'
    else if (key === 'employment_type') newEmployee[key] = 'full_time'
    else if (key === 'employment_status') newEmployee[key] = 'probationary'
    else if (key === 'hire_date') newEmployee[key] = today.value
    else if (key === 'salary_currency') newEmployee[key] = 'PHP'
    else if (key === 'payment_frequency') newEmployee[key] = 'monthly'
    else if (key === 'year_graduated') newEmployee[key] = currentYear
    else if (key === 'salary') newEmployee[key] = 0
    else newEmployee[key] = ''
  })
}

const exportToCSV = () => {
  // Basic CSV export
  const headers = ['Employee ID', 'Name', 'Email', 'Phone', 'Department', 'Position', 'Employment Status', 'Status', 'Hire Date']
  const data = filteredEmployees.value.map(emp => [
    emp.employee_code,
    emp.full_name,
    emp.email,
    emp.phone,
    emp.department,
    emp.position,
    emp.employment_status,
    emp.status,
    emp.hire_date
  ])
  
  const csvContent = [
    headers.join(','),
    ...data.map(row => row.map(cell => `"${cell}"`).join(','))
  ].join('\n')
  
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  link.href = URL.createObjectURL(blob)
  link.download = `employees_${new Date().toISOString().split('T')[0]}.csv`
  link.click()
  
  alert('CSV exported successfully!')
}
</script>

<style scoped>
.employees-list {
  min-height: calc(100vh - 80px);
}

/* Animation for step transitions */
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
  animation: fade-in 0.3s ease-out;
}

/* Custom scrollbar for modal */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Responsive improvements */
@media (max-width: 640px) {
  .employees-list {
    padding: 1rem !important;
  }
  
  .bg-white.rounded-xl.shadow-md {
    border-radius: 0.75rem !important;
  }
  
  .p-4 {
    padding: 1rem !important;
  }
  
  .p-6 {
    padding: 1.25rem !important;
  }
}

/* Focus styles for better accessibility */
.focus\:shadow-outline:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Hover transitions */
.hover\:shadow-lg:hover {
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Gradient backgrounds */
.bg-gradient-to-r {
  background-size: 200% 200%;
  background-position: 0% 0%;
}

.bg-gradient-to-r:hover {
  background-position: 100% 100%;
}
</style>