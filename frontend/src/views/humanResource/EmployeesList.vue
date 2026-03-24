<template>
  <div class="employees-list p-4 md:p-6 text-slate-900">
    <Toaster richColors position="top-right" expand />

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 no-print">
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
        <button @click="requirePermission('manage', () => openAddModal())" class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Employee
        </button>
        <button @click="requirePermission('view', () => exportToCSV())" class="flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors shadow-sm hover:shadow">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export
        </button>
      </div>
    </div>

    <div v-if="statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 no-print">
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

    <div class="bg-white rounded-xl shadow-md p-4 mb-6 no-print">
      <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
        <div class="flex-1">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input v-model="searchQuery" type="text" placeholder="Search employees..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline">
          </div>
        </div>
        <div class="flex flex-wrap gap-2">
          <div class="relative min-w-[150px]">
            <select v-model="departmentFilter" class="appearance-none !bg-none w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline">
              <option value="">All Departments</option>
              <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
          </div>
          <div class="relative min-w-[150px]">
            <select v-model="statusFilter" class="appearance-none !bg-none w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline">
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="on_leave">On Leave</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
          </div>
          <div class="relative min-w-[150px]">
            <select v-model="employmentStatusFilter" class="appearance-none !bg-none w-full px-4 py-2 pr-10 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:shadow-outline">
              <option value="">All Employment Status</option>
              <option value="probationary">Probationary</option>
              <option value="regular">Regular</option>
              <option value="contractual">Contractual</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-12 no-print">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <div v-else-if="error" class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-xl p-6 mb-6 no-print">
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

    <div v-else class="bg-white rounded-xl shadow-md overflow-hidden no-print">
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
                <div class="flex space-x-3 items-center">
                  <!-- EDIT BUTTON with lazy loading spinner -->
                  <button
                    @click="requirePermission('manage', () => editEmployee(employee.id))"
                    :disabled="actionLoading.type === 'edit' && actionLoading.id === employee.id"
                    class="text-yellow-600 hover:text-yellow-900 transform hover:scale-110 transition-transform disabled:opacity-50 disabled:cursor-wait disabled:transform-none"
                    title="Edit Employee"
                  >
                    <svg v-if="actionLoading.type === 'edit' && actionLoading.id === employee.id" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>

                  <!-- DOCUMENT BUTTON with lazy loading spinner -->
                  <button
                    @click="requirePermission('view', () => openDocumentModal(employee.id))"
                    :disabled="actionLoading.type === 'doc' && actionLoading.id === employee.id"
                    class="text-indigo-600 hover:text-indigo-900 transform hover:scale-110 transition-transform disabled:opacity-50 disabled:cursor-wait disabled:transform-none"
                    title="View Document"
                  >
                    <svg v-if="actionLoading.type === 'doc' && actionLoading.id === employee.id" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>

                  <!-- REGULARIZE BUTTON with lazy loading spinner -->
                  <button
                    v-if="employee.employment_status === 'probationary'"
                    @click="requirePermission('manage', () => regularizeEmployee(employee.id))"
                    :disabled="actionLoading.type === 'regularize' && actionLoading.id === employee.id"
                    class="text-green-600 hover:text-green-900 transform hover:scale-110 transition-transform disabled:opacity-50 disabled:cursor-wait disabled:transform-none"
                    title="Regularize"
                  >
                    <svg v-if="actionLoading.type === 'regularize' && actionLoading.id === employee.id" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!loading && filteredEmployees.length === 0" class="py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No employees found</h3>
        <p class="mt-2 text-gray-500">Get started by creating your first employee.</p>
        <div class="mt-6">
          <button @click="requirePermission('manage', () => openAddModal())" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Employee
          </button>
        </div>
      </div>

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

    <!-- DOCUMENT MODAL -->
    <div v-if="showDocumentModal && documentData" class="fixed inset-0 bg-white/50 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 z-50 overflow-y-auto no-print">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[95vh] flex flex-col relative printable-document">
        
        <!-- Modal Header (no-print) -->
        <div class="flex items-center justify-between p-4 border-b bg-gray-50 rounded-t-xl sticky top-0 z-10 no-print">
          <h3 class="text-lg font-bold text-gray-800 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Official Employee Record
          </h3>
          <div class="flex space-x-2">
            <button @click="printDocument" class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors shadow-sm">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
              Print Record
            </button>
            <button @click="showDocumentModal = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-full transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Scrollable Content (this is what gets printed) -->
        <div class="p-8 overflow-y-auto bg-white flex-1 text-black print-container" id="printable-content">
          <div class="text-center mb-8 pb-6 border-b-2 border-gray-800">
            <h1 class="text-3xl font-extrabold uppercase tracking-wider">{{ documentData.company_name }}</h1>
            <h2 class="text-xl font-bold mt-2 uppercase text-gray-700">Employee Information Form</h2>
            <p class="text-sm mt-1">Generated: {{ new Date().toLocaleDateString() }}</p>
          </div>

          <div class="flex justify-between items-center bg-gray-100 p-4 rounded mb-6 font-semibold">
            <div><span class="text-gray-600">Employee Code:</span> {{ documentData.employee_code }}</div>
            <div><span class="text-gray-600">Status:</span> {{ documentData.status.toUpperCase() }}</div>
            <div><span class="text-gray-600">Hire Date:</span> {{ documentData.hire_date }}</div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-3 pb-1 uppercase tracking-wide">Personal Information</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
              <div><span class="font-semibold text-gray-600">Full Name:</span> {{ documentData.full_name }}</div>
              <div><span class="font-semibold text-gray-600">Date of Birth:</span> {{ documentData.date_of_birth }} (Age: {{ documentData.age }})</div>
              <div><span class="font-semibold text-gray-600">Gender:</span> {{ capitalize(documentData.gender) }}</div>
              <div><span class="font-semibold text-gray-600">Civil Status:</span> {{ capitalize(documentData.marital_status) }}</div>
              <div><span class="font-semibold text-gray-600">Nationality:</span> {{ documentData.nationality }}</div>
              <div><span class="font-semibold text-gray-600">Email:</span> {{ documentData.email }}</div>
              <div><span class="font-semibold text-gray-600">Contact Number:</span> {{ documentData.phone }}</div>
              <div><span class="font-semibold text-gray-600">Emergency Contact:</span> {{ documentData.emergency_contact || 'N/A' }}</div>
              <div class="col-span-2"><span class="font-semibold text-gray-600">Address:</span> {{ documentData.address }}</div>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-3 pb-1 uppercase tracking-wide">Employment Details</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
              <div><span class="font-semibold text-gray-600">Department:</span> {{ documentData.department }}</div>
              <div><span class="font-semibold text-gray-600">Position:</span> {{ documentData.position }}</div>
              <div><span class="font-semibold text-gray-600">Employment Type:</span> {{ documentData.employment_type.replace('_', ' ').toUpperCase() }}</div>
              <div><span class="font-semibold text-gray-600">Employment Status:</span> {{ capitalize(documentData.employment_status) }}</div>
              <div><span class="font-semibold text-gray-600">Probation End Date:</span> {{ documentData.probation_end_date || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">Regularization Date:</span> {{ documentData.regularization_date || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">Years of Service:</span> {{ documentData.years_of_service }}</div>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-3 pb-1 uppercase tracking-wide">Financial & Government Data</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
              <div><span class="font-semibold text-gray-600">Bank Name:</span> {{ documentData.bank_name || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">Bank Account No:</span> {{ documentData.bank_account_number || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">SSS Number:</span> {{ documentData.sss_number || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">PhilHealth Number:</span> {{ documentData.philhealth_number || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">Pag-IBIG Number:</span> {{ documentData.pagibig_number || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">TIN Number:</span> {{ documentData.tin_number || 'N/A' }}</div>
              <div><span class="font-semibold text-gray-600">Primary ID Type:</span> {{ capitalize(documentData.valid_id_type) }}</div>
              <div><span class="font-semibold text-gray-600">Primary ID Number:</span> {{ documentData.id_number }}</div>
            </div>
          </div>

          <div class="mb-6">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-3 pb-1 uppercase tracking-wide">Educational Background</h3>
            <div class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm">
              <div><span class="font-semibold text-gray-600">Highest Attainment:</span> {{ capitalize(documentData.educational_attainment) }}</div>
              <div><span class="font-semibold text-gray-600">Year Graduated:</span> {{ documentData.year_graduated }}</div>
              <div class="col-span-2"><span class="font-semibold text-gray-600">School/University:</span> {{ documentData.school_graduated }}</div>
              <div class="col-span-2"><span class="font-semibold text-gray-600">Course/Degree:</span> {{ documentData.course }}</div>
            </div>
          </div>

          <!-- FIX: Attached 201 Files — shows count, handles zero files gracefully -->
          <div class="mb-8">
            <h3 class="text-lg font-bold border-b border-gray-300 mb-3 pb-1 uppercase tracking-wide">
              Attached 201 Files
              <span class="ml-2 text-sm font-normal text-gray-500 normal-case">({{ attachedFilesCount }} document{{ attachedFilesCount !== 1 ? 's' : '' }} attached)</span>
            </h3>
            <ul v-if="attachedFilesCount > 0" class="list-disc pl-5 text-sm space-y-2">
              <li v-if="documentData.valid_id_photo_url">
                Valid ID Photo
                <a :href="getFileUrl(documentData.valid_id_photo_url)" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline no-print ml-2">(View Document)</a>
              </li>
              <li v-if="documentData.resume_url">
                Resume / CV
                <a :href="getFileUrl(documentData.resume_url)" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline no-print ml-2">(View Document)</a>
              </li>
              <li v-if="documentData.employment_contract_url">
                Employment Contract
                <a :href="getFileUrl(documentData.employment_contract_url)" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline no-print ml-2">(View Document)</a>
              </li>
              <li v-if="documentData.medical_certificate_url">
                Medical Certificate
                <a :href="getFileUrl(documentData.medical_certificate_url)" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline no-print ml-2">(View Document)</a>
              </li>
              <li v-if="documentData.nbi_clearance_url">
                NBI Clearance
                <a :href="getFileUrl(documentData.nbi_clearance_url)" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline no-print ml-2">(View Document)</a>
              </li>
              <li v-if="documentData.police_clearance_url">
                Police Clearance
                <a :href="getFileUrl(documentData.police_clearance_url)" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline no-print ml-2">(View Document)</a>
              </li>
            </ul>
            <p v-else class="text-sm text-gray-400 italic">No documents have been uploaded for this employee yet.</p>
          </div>

          <div class="mt-16 grid grid-cols-2 gap-8 text-center pt-8">
            <div>
              <div class="border-t border-black w-3/4 mx-auto pt-2">
                <p class="font-bold">{{ documentData.full_name }}</p>
                <p class="text-xs text-gray-600">Employee Signature over Printed Name</p>
              </div>
            </div>
            <div>
              <div class="border-t border-black w-3/4 mx-auto pt-2">
                <p class="font-bold">{{ documentData.hr_manager ? documentData.hr_manager.name : 'HR Department' }}</p>
                <p class="text-xs text-gray-600">Authorized HR Representative</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ADD / EDIT MODAL -->
    <div v-if="showAddModal" class="fixed inset-0 bg-white/30 backdrop-blur-md flex items-center justify-center p-2 sm:p-4 z-40 overflow-y-auto no-print">
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[95vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
          <div class="flex items-center justify-between mb-6 sticky top-0 bg-white pb-4 border-b z-10">
            <div>
              <h3 class="text-lg sm:text-xl font-bold text-gray-800">{{ isEditing ? 'Edit Employee' : 'Add New Employee' }}</h3>
              <p class="text-sm text-gray-600">Step {{ currentStep }} of {{ steps.length }}</p>
            </div>
            <button @click="closeAddModal" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

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
            
            <div class="flex justify-between mt-6 overflow-x-auto pb-2">
              <div 
                v-for="(step, index) in steps" 
                :key="step.name"
                class="flex flex-col items-center min-w-[80px] sm:min-w-0"
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

          <div v-if="formError" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start">
             <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             <div>
               <h4 class="text-sm font-semibold text-red-800">Validation Error</h4>
               <p class="text-sm text-red-700">{{ formError }}</p>
             </div>
          </div>

          <form @submit.prevent="checkAndOpenConfirmDialog" class="space-y-6" enctype="multipart/form-data">
            <!-- STEP 1: Basic Info -->
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
                    <label class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.first_name" type="text" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.first_name, 'border-gray-300': !errors.first_name}" placeholder="Enter first name">
                    <p v-if="errors.first_name" class="text-red-500 text-xs mt-1">{{ errors.first_name[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                    <input v-model="newEmployee.middle_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="Enter middle name">
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.last_name" type="text" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.last_name, 'border-gray-300': !errors.last_name}" placeholder="Enter last name">
                    <p v-if="errors.last_name" class="text-red-500 text-xs mt-1">{{ errors.last_name[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.email" type="email" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.email, 'border-gray-300': !errors.email}" placeholder="employee@company.com">
                    <p v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Phone <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.phone" type="tel" maxlength="11" @input="newEmployee.phone = newEmployee.phone.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.phone, 'border-gray-300': !errors.phone}" placeholder="09123456789">
                    <p class="text-xs text-gray-500">11 digits, numbers only</p>
                    <p v-if="errors.phone" class="text-red-500 text-xs mt-1">{{ errors.phone[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Emergency Contact</label>
                    <input v-model="newEmployee.emergency_contact" type="tel" maxlength="11" @input="newEmployee.emergency_contact = newEmployee.emergency_contact.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="09123456789">
                    <p class="text-xs text-gray-500">11 digits, numbers only</p>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                  <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Personal Details
                  </h5>
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Date of Birth <span class="text-red-500">*</span></label>
                      <input v-model="newEmployee.date_of_birth" type="date" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.date_of_birth, 'border-gray-300': !errors.date_of_birth}">
                      <p v-if="errors.date_of_birth" class="text-red-500 text-xs mt-1">{{ errors.date_of_birth[0] }}</p>
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Gender <span class="text-red-500">*</span></label>
                      <div class="relative">
                        <select v-model="newEmployee.gender" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.gender, 'border-gray-300': !errors.gender}">
                          <option value="">Select Gender</option>
                          <option value="male">Male</option>
                          <option value="female">Female</option>
                          <option value="other">Other</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                      </div>
                      <p v-if="errors.gender" class="text-red-500 text-xs mt-1">{{ errors.gender[0] }}</p>
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Marital Status <span class="text-red-500">*</span></label>
                      <div class="relative">
                        <select v-model="newEmployee.marital_status" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.marital_status, 'border-gray-300': !errors.marital_status}">
                          <option value="">Select Status</option>
                          <option value="single">Single</option>
                          <option value="married">Married</option>
                          <option value="widowed">Widowed</option>
                          <option value="separated">Separated</option>
                          <option value="divorced">Divorced</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                      </div>
                      <p v-if="errors.marital_status" class="text-red-500 text-xs mt-1">{{ errors.marital_status[0] }}</p>
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Nationality <span class="text-red-500">*</span></label>
                      <input v-model="newEmployee.nationality" type="text" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.nationality, 'border-gray-300': !errors.nationality}">
                      <p v-if="errors.nationality" class="text-red-500 text-xs mt-1">{{ errors.nationality[0] }}</p>
                    </div>
                  </div>
                </div>

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
                      <label class="block text-sm font-medium text-gray-700">Address <span class="text-red-500">*</span></label>
                      <textarea v-model="newEmployee.address" rows="3" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline resize-none" :class="{'border-red-500': errors.address, 'border-gray-300': !errors.address}" placeholder="Enter complete address"></textarea>
                      <p v-if="errors.address" class="text-red-500 text-xs mt-1">{{ errors.address[0] }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 2: Employment -->
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
                    <label class="block text-sm font-medium text-gray-700">Department <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="newEmployee.department" @change="loadPositionsForDepartment" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.department, 'border-gray-300': !errors.department}">
                        <option value="">Select Department</option>
                        <option v-for="dept in availableDepartments" :key="dept" :value="dept">{{ dept }}</option>
                      </select>
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                    <p v-if="errors.department" class="text-red-500 text-xs mt-1">{{ errors.department[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Position <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="newEmployee.position" :disabled="!newEmployee.department || availablePositions.length === 0" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline disabled:opacity-50 disabled:cursor-not-allowed" :class="{'border-red-500': errors.position, 'border-gray-300': !errors.position}">
                        <option value="">Select Position</option>
                        <option v-for="position in availablePositions" :key="position.id" :value="position.title">{{ position.title }}</option>
                      </select>
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                    <p v-if="errors.position" class="text-red-500 text-xs mt-1">{{ errors.position[0] }}</p>
                    <p v-if="!newEmployee.department" class="text-xs text-gray-500">Please select a department first</p>
                    <p v-if="newEmployee.department && availablePositions.length === 0" class="text-xs text-yellow-600">No positions available. Please add positions first.</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Employment Type <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="newEmployee.employment_type" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.employment_type, 'border-gray-300': !errors.employment_type}">
                        <option value="">Select Type</option>
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="probationary">Probationary</option>
                        <option value="intern">Intern</option>
                      </select>
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                    <p v-if="errors.employment_type" class="text-red-500 text-xs mt-1">{{ errors.employment_type[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Employment Status <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="newEmployee.employment_status" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.employment_status, 'border-gray-300': !errors.employment_status}">
                        <option value="probationary">Probationary</option>
                        <option value="regular">Regular</option>
                        <option value="contractual">Contractual</option>
                        <option value="resigned">Resigned</option>
                        <option value="terminated">Terminated</option>
                        <option value="retired">Retired</option>
                      </select>
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                    <p v-if="errors.employment_status" class="text-red-500 text-xs mt-1">{{ errors.employment_status[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Hire Date <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.hire_date" type="date" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.hire_date, 'border-gray-300': !errors.hire_date}">
                    <p v-if="errors.hire_date" class="text-red-500 text-xs mt-1">{{ errors.hire_date[0] }}</p>
                  </div>
                  <div v-if="newEmployee.employment_status === 'probationary'" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Probation End Date</label>
                    <input v-model="newEmployee.probation_end_date" type="date" :min="newEmployee.hire_date" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline">
                  </div>
                </div>
              </div>

              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Salary & Compensation
                </h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Salary <span class="text-red-500">*</span></label>
                    <div class="flex space-x-2">
                      <div class="relative w-28">
                        <select v-model="newEmployee.salary_currency" class="w-full appearance-none !bg-none px-3 py-3 pr-8 border border-gray-300 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline">
                          <option value="PHP">PHP</option>
                          <option value="USD">USD</option>
                          <option value="EUR">EUR</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                      </div>
                      <input v-model="newEmployee.salary" type="number" step="0.01" min="0" @keydown="preventInvalidMathChars" placeholder="0.00" class="flex-1 px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.salary, 'border-gray-300': !errors.salary}">
                    </div>
                    <p class="text-xs text-gray-500">Numeric values only</p>
                    <p v-if="errors.salary" class="text-red-500 text-xs mt-1">{{ errors.salary[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Payment Frequency <span class="text-red-500">*</span></label>
                    <input :value="formatFrequency(newEmployee.payment_frequency)" type="text" readonly class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 focus:ring-0 cursor-not-allowed">
                    <p class="text-xs text-gray-500">Determined by Distributor settings</p>
                    <p v-if="errors.payment_frequency" class="text-red-500 text-xs mt-1">{{ errors.payment_frequency[0] }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 3: Financial -->
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
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                  <h5 class="font-semibold text-gray-800 mb-4">Bank Details</h5>
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Bank Name</label>
                      <input v-model="newEmployee.bank_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="e.g., BDO, BPI, Metrobank">
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Account Number</label>
                      <input v-model="newEmployee.bank_account_number" type="text" @input="newEmployee.bank_account_number = newEmployee.bank_account_number.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="Enter account number">
                      <p class="text-xs text-gray-500">Numbers only</p>
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Account Name</label>
                      <input v-model="newEmployee.bank_account_name" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="Account holder name">
                    </div>
                  </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                  <h5 class="font-semibold text-gray-800 mb-4">Government Numbers</h5>
                  <div class="space-y-4">
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">SSS Number</label>
                      <input v-model="newEmployee.sss_number" type="text" @input="newEmployee.sss_number = newEmployee.sss_number.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="Numbers only">
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">PhilHealth Number</label>
                      <input v-model="newEmployee.philhealth_number" type="text" @input="newEmployee.philhealth_number = newEmployee.philhealth_number.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="Numbers only">
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">Pag-IBIG Number</label>
                      <input v-model="newEmployee.pagibig_number" type="text" @input="newEmployee.pagibig_number = newEmployee.pagibig_number.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="Numbers only">
                    </div>
                    <div class="space-y-2">
                      <label class="block text-sm font-medium text-gray-700">TIN Number</label>
                      <input v-model="newEmployee.tin_number" type="text" @input="newEmployee.tin_number = newEmployee.tin_number.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" placeholder="Numbers only">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 4: Documents/ID/Education -->
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

              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4">Identification</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Valid ID Type <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="newEmployee.valid_id_type" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.valid_id_type, 'border-gray-300': !errors.valid_id_type}">
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
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                    <p v-if="errors.valid_id_type" class="text-red-500 text-xs mt-1">{{ errors.valid_id_type[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">ID Number <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.id_number" type="text" @input="newEmployee.id_number = newEmployee.id_number.replace(/[^0-9]/g, '')" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.id_number, 'border-gray-300': !errors.id_number}" placeholder="Numbers only">
                    <p class="text-xs text-gray-500">Numbers only</p>
                    <p v-if="errors.id_number" class="text-red-500 text-xs mt-1">{{ errors.id_number[0] }}</p>
                  </div>
                  <div class="space-y-2 sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                      Valid ID Photo (Image or PDF) <span v-if="!isEditing" class="text-red-500">*</span>
                    </label>
                    <!-- New file selected -->
                    <div v-if="newEmployee.valid_id_photo" class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                      <span class="text-sm text-blue-800 truncate flex-1">{{ newEmployee.valid_id_photo.name }}</span>
                      <button type="button" @click="removeFile('valid_id_photo')" class="text-red-500 hover:text-red-700 ml-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                      </button>
                    </div>
                    <!-- Existing file (edit mode) - FIX: use getFileUrl() -->
                    <div v-else-if="existingFiles.valid_id_photo_url" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                      <a :href="getFileUrl(existingFiles.valid_id_photo_url)" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline truncate flex-1">View Current ID Photo</a>
                      <label class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 ml-2 cursor-pointer">
                        Replace <input type="file" @change="handleFileUpload($event, 'valid_id_photo')" accept="image/*,.pdf" class="hidden">
                      </label>
                    </div>
                    <!-- No file yet -->
                    <div v-else class="flex items-center space-x-3">
                      <input type="file" @change="handleFileUpload($event, 'valid_id_photo')" accept="image/*,.pdf" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.valid_id_photo, 'border-gray-300': !errors.valid_id_photo}">
                      <div class="text-xs text-gray-500 whitespace-nowrap">Max 5MB</div>
                    </div>
                    <p v-if="errors.valid_id_photo" class="text-red-500 text-xs mt-1">{{ errors.valid_id_photo[0] }}</p>
                  </div>
                </div>
              </div>

              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4">Educational Background</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Educational Attainment <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <select v-model="newEmployee.educational_attainment" class="w-full appearance-none !bg-none px-4 py-3 pr-10 border rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.educational_attainment, 'border-gray-300': !errors.educational_attainment}">
                        <option value="">Select Attainment</option>
                        <option value="elementary">Elementary</option>
                        <option value="high_school">High School</option>
                        <option value="vocational">Vocational</option>
                        <option value="college">College</option>
                        <option value="post_graduate">Post Graduate</option>
                        <option value="doctorate">Doctorate</option>
                      </select>
                      <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></div>
                    </div>
                    <p v-if="errors.educational_attainment" class="text-red-500 text-xs mt-1">{{ errors.educational_attainment[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">School Graduated <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.school_graduated" type="text" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.school_graduated, 'border-gray-300': !errors.school_graduated}" placeholder="University or School name">
                    <p v-if="errors.school_graduated" class="text-red-500 text-xs mt-1">{{ errors.school_graduated[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Year Graduated <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.year_graduated" type="number" min="1900" :max="currentYear" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.year_graduated, 'border-gray-300': !errors.year_graduated}" placeholder="Year">
                    <p v-if="errors.year_graduated" class="text-red-500 text-xs mt-1">{{ errors.year_graduated[0] }}</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Course <span class="text-red-500">*</span></label>
                    <input v-model="newEmployee.course" type="text" placeholder="e.g., Bachelor of Science in Business Administration" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline" :class="{'border-red-500': errors.course, 'border-gray-300': !errors.course}">
                    <p v-if="errors.course" class="text-red-500 text-xs mt-1">{{ errors.course[0] }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- STEP 5: Review & Documents -->
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

              <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-200">
                <h5 class="font-semibold text-blue-800 mb-3">Company Information</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-blue-700 mb-1">Company</label>
                    <input :value="companyInfo?.company_name || 'Loading...'" type="text" disabled class="w-full px-4 py-3 bg-gray-50 border border-blue-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-blue-700 mb-1">Created/Updated By</label>
                    <input :value="userName" type="text" disabled class="w-full px-4 py-3 bg-gray-50 border border-blue-300 rounded-lg text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm">
                  </div>
                </div>
              </div>

              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4">Additional Documents</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                  <!-- Resume -->
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Resume <span v-if="!isEditing" class="text-red-500">*</span></label>
                    <div v-if="newEmployee.resume" class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <span class="text-sm text-blue-800 truncate flex-1">{{ newEmployee.resume.name }}</span>
                      <button type="button" @click="removeFile('resume')" class="text-red-500 hover:text-red-700 ml-2 font-bold">X</button>
                    </div>
                    <div v-else-if="existingFiles.resume_url" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                      <a :href="getFileUrl(existingFiles.resume_url)" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline truncate flex-1">View Current</a>
                      <label class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 ml-2 cursor-pointer">
                        Replace <input type="file" @change="handleFileUpload($event, 'resume')" accept=".pdf,.doc,.docx" class="hidden">
                      </label>
                    </div>
                    <div v-else>
                      <input type="file" @change="handleFileUpload($event, 'resume')" accept=".pdf,.doc,.docx" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400" :class="{'border-red-500': errors.resume, 'border-gray-300': !errors.resume}">
                      <p class="text-xs text-gray-500">PDF, DOC, DOCX</p>
                      <p v-if="errors.resume" class="text-red-500 text-xs mt-1">{{ errors.resume[0] }}</p>
                    </div>
                  </div>
                  <!-- Employment Contract -->
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Employment Contract <span v-if="!isEditing" class="text-red-500">*</span></label>
                    <div v-if="newEmployee.employment_contract" class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <span class="text-sm text-blue-800 truncate flex-1">{{ newEmployee.employment_contract.name }}</span>
                      <button type="button" @click="removeFile('employment_contract')" class="text-red-500 hover:text-red-700 ml-2 font-bold">X</button>
                    </div>
                    <div v-else-if="existingFiles.employment_contract_url" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                      <a :href="getFileUrl(existingFiles.employment_contract_url)" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline truncate flex-1">View Current</a>
                      <label class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 ml-2 cursor-pointer">
                        Replace <input type="file" @change="handleFileUpload($event, 'employment_contract')" accept=".pdf" class="hidden">
                      </label>
                    </div>
                    <div v-else>
                      <input type="file" @change="handleFileUpload($event, 'employment_contract')" accept=".pdf" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400" :class="{'border-red-500': errors.employment_contract, 'border-gray-300': !errors.employment_contract}">
                      <p class="text-xs text-gray-500">PDF only</p>
                      <p v-if="errors.employment_contract" class="text-red-500 text-xs mt-1">{{ errors.employment_contract[0] }}</p>
                    </div>
                  </div>
                  <!-- Medical Certificate -->
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Medical Certificate <span v-if="!isEditing" class="text-red-500">*</span></label>
                    <div v-if="newEmployee.medical_certificate" class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <span class="text-sm text-blue-800 truncate flex-1">{{ newEmployee.medical_certificate.name }}</span>
                      <button type="button" @click="removeFile('medical_certificate')" class="text-red-500 hover:text-red-700 ml-2 font-bold">X</button>
                    </div>
                    <div v-else-if="existingFiles.medical_certificate_url" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                      <a :href="getFileUrl(existingFiles.medical_certificate_url)" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline truncate flex-1">View Current</a>
                      <label class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 ml-2 cursor-pointer">
                        Replace <input type="file" @change="handleFileUpload($event, 'medical_certificate')" accept="image/*,.pdf" class="hidden">
                      </label>
                    </div>
                    <div v-else>
                      <input type="file" @change="handleFileUpload($event, 'medical_certificate')" accept="image/*,.pdf" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400" :class="{'border-red-500': errors.medical_certificate, 'border-gray-300': !errors.medical_certificate}">
                      <p v-if="errors.medical_certificate" class="text-red-500 text-xs mt-1">{{ errors.medical_certificate[0] }}</p>
                    </div>
                  </div>
                  <!-- NBI Clearance -->
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">NBI Clearance <span v-if="!isEditing" class="text-red-500">*</span></label>
                    <div v-if="newEmployee.nbi_clearance" class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <span class="text-sm text-blue-800 truncate flex-1">{{ newEmployee.nbi_clearance.name }}</span>
                      <button type="button" @click="removeFile('nbi_clearance')" class="text-red-500 hover:text-red-700 ml-2 font-bold">X</button>
                    </div>
                    <div v-else-if="existingFiles.nbi_clearance_url" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                      <a :href="getFileUrl(existingFiles.nbi_clearance_url)" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline truncate flex-1">View Current</a>
                      <label class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 ml-2 cursor-pointer">
                        Replace <input type="file" @change="handleFileUpload($event, 'nbi_clearance')" accept="image/*,.pdf" class="hidden">
                      </label>
                    </div>
                    <div v-else>
                      <input type="file" @change="handleFileUpload($event, 'nbi_clearance')" accept="image/*,.pdf" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400" :class="{'border-red-500': errors.nbi_clearance, 'border-gray-300': !errors.nbi_clearance}">
                      <p v-if="errors.nbi_clearance" class="text-red-500 text-xs mt-1">{{ errors.nbi_clearance[0] }}</p>
                    </div>
                  </div>
                  <!-- Police Clearance -->
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Police Clearance <span v-if="!isEditing" class="text-red-500">*</span></label>
                    <div v-if="newEmployee.police_clearance" class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-lg">
                      <span class="text-sm text-blue-800 truncate flex-1">{{ newEmployee.police_clearance.name }}</span>
                      <button type="button" @click="removeFile('police_clearance')" class="text-red-500 hover:text-red-700 ml-2 font-bold">X</button>
                    </div>
                    <div v-else-if="existingFiles.police_clearance_url" class="flex items-center p-3 bg-gray-50 border border-gray-200 rounded-lg">
                      <a :href="getFileUrl(existingFiles.police_clearance_url)" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:underline truncate flex-1">View Current</a>
                      <label class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 ml-2 cursor-pointer">
                        Replace <input type="file" @change="handleFileUpload($event, 'police_clearance')" accept="image/*,.pdf" class="hidden">
                      </label>
                    </div>
                    <div v-else>
                      <input type="file" @change="handleFileUpload($event, 'police_clearance')" accept="image/*,.pdf" class="w-full px-4 py-3 border rounded-lg bg-white text-gray-900 placeholder-gray-400" :class="{'border-red-500': errors.police_clearance, 'border-gray-300': !errors.police_clearance}">
                      <p v-if="errors.police_clearance" class="text-red-500 text-xs mt-1">{{ errors.police_clearance[0] }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
                <h5 class="font-semibold text-gray-800 mb-4">Additional Notes</h5>
                <div class="space-y-2">
                  <textarea v-model="newEmployee.notes" rows="4" placeholder="Any additional notes or comments about the employee..." class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-white text-gray-900 placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm focus:shadow-outline resize-none"></textarea>
                  <p class="text-xs text-gray-500">Optional: Add any special instructions, remarks, or important information.</p>
                </div>
              </div>

              <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-5 rounded-xl border border-green-200">
                <h5 class="font-semibold text-green-800 mb-4">Review Summary</h5>
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

        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4 sm:p-6 no-print">
          <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
            <div class="text-sm text-gray-600">
              Step {{ currentStep }} of {{ steps.length }}: {{ steps[currentStep - 1].description }}
            </div>
            <div class="flex space-x-3 w-full sm:w-auto">
              <button v-if="currentStep > 1" type="button" @click="prevStep" class="flex-1 sm:flex-none px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all shadow-sm hover:shadow">
                Previous
              </button>
              <button v-if="currentStep < steps.length" type="button" @click="nextStep" class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all shadow-md hover:shadow-lg">
                Continue to {{ steps[currentStep].name }}
              </button>
              <button v-if="currentStep === steps.length" type="button" @click="checkAndOpenConfirmDialog" :disabled="saving" class="flex-1 sm:flex-none px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                <svg v-if="saving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ saving ? (isEditing ? 'Updating Employee...' : 'Creating Employee...') : (isEditing ? 'Update Employee' : 'Create Employee') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CONFIRM DIALOG -->
    <div v-if="showConfirmDialog" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm no-print">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 animate-fade-in">
        <div class="flex items-center mb-4">
          <div class="p-2 bg-yellow-100 rounded-full mr-3">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-gray-900">Confirm Employee {{ isEditing ? 'Update' : 'Creation' }}</h3>
        </div>
        <p class="text-gray-600 mb-6">
          Are you sure you want to {{ isEditing ? 'update' : 'create' }} this employee record? Please verify that all information is correct before proceeding.
        </p>
        <div class="flex justify-end space-x-3">
          <button @click="showConfirmDialog = false" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
            Cancel
          </button>
          <button @click="finalizeEmployeeCreation" class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors shadow-sm">
            Confirm & {{ isEditing ? 'Update' : 'Create' }}
          </button>
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
import { Toaster, toast } from 'vue-sonner' 

const router = useRouter()

// State
const searchQuery = ref('')
const departmentFilter = ref('')
const statusFilter = ref('')
const employmentStatusFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = 10
const showAddModal = ref(false)
const showConfirmDialog = ref(false) 
const showDocumentModal = ref(false)
const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const formError = ref(null)
const errors = ref({}) 
const employees = ref([])
const departments = ref([])
const statistics = ref(null)
const companyInfo = ref(null)
const userInfo = ref(null)
const currentStep = ref(1)
const availableDepartments = ref([])
const availablePositions = ref([])
const documentData = ref(null)

// --- FIX: Per-button lazy loading state ---
// type: 'edit' | 'doc' | 'regularize' | null
// id: employee id | null
const actionLoading = ref({ type: null, id: null })

// Editing State
const isEditing = ref(false)
const editId = ref(null)
const existingFiles = reactive({
  valid_id_photo_url: null,
  resume_url: null,
  employment_contract_url: null,
  medical_certificate_url: null,
  nbi_clearance_url: null,
  police_clearance_url: null
})

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

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
    fetchDepartments()
    fetchPayrollSettings() 
  } catch (err) {
    console.error('Failed to get user info:', err)
  }
})

const userName = computed(() => {
  if (!userInfo.value) return ''
  return `${userInfo.value.first_name || ''} ${userInfo.value.last_name || ''}`.trim()
})

// RBAC Action Interceptor
const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied: You do not have permission to ${action} employees.`);
    return;
  }
  if (callback) callback();
}

// --- FIX: File URL helper ---
// Ensures file links always point to the correct backend URL,
// even when Storage::url() returns a relative /storage/... path in dev.
const getFileUrl = (url) => {
  if (!url) return '#'
  if (url.startsWith('http://') || url.startsWith('https://')) return url
  // Relative path — prepend backend base URL derived from axios baseURL
  const base = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '')
  return base + url
}

// --- FIX: Count of actually attached documents ---
const attachedFilesCount = computed(() => {
  if (!documentData.value) return 0
  return [
    documentData.value.valid_id_photo_url,
    documentData.value.resume_url,
    documentData.value.employment_contract_url,
    documentData.value.medical_certificate_url,
    documentData.value.nbi_clearance_url,
    documentData.value.police_clearance_url,
  ].filter(Boolean).length
})

// New employee form
const newEmployee = reactive({
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

  department: '',
  position: '',
  employment_type: 'full_time',
  employment_status: 'probationary',
  hire_date: today.value,
  probation_end_date: '',
  regularization_date: '',
  salary: 0,
  salary_currency: 'PHP',
  payment_frequency: '', 
  
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  
  sss_number: '',
  philhealth_number: '',
  pagibig_number: '',
  tin_number: '',
  
  valid_id_type: '',
  id_number: '',
  
  educational_attainment: '',
  school_graduated: '',
  year_graduated: currentYear,
  course: '',
  
  valid_id_photo: null,
  resume: null,
  employment_contract: null,
  medical_certificate: null,
  nbi_clearance: null,
  police_clearance: null,
  
  notes: ''
})

const validateStep = (step) => {
    errors.value = {} 
    formError.value = null
    let isValid = true

    const addError = (field, message) => {
        errors.value[field] = [message]
        isValid = false
    }

    if (step === 1) {
        if (!newEmployee.first_name) addError('first_name', 'First name is required')
        if (!newEmployee.last_name) addError('last_name', 'Last name is required')
        if (!newEmployee.email) addError('email', 'Email is required')
        if (!newEmployee.phone) addError('phone', 'Phone number is required')
        else if (!/^\d{11}$/.test(newEmployee.phone)) addError('phone', 'Phone must be exactly 11 digits')
        
        if (newEmployee.emergency_contact && !/^\d{11}$/.test(newEmployee.emergency_contact)) {
            addError('emergency_contact', 'Emergency contact must be exactly 11 digits')
        }

        if (!newEmployee.date_of_birth) addError('date_of_birth', 'Date of birth is required')
        if (!newEmployee.address) addError('address', 'Address is required')
        if (!newEmployee.nationality) addError('nationality', 'Nationality is required')
    }

    if (step === 2) {
        if (!newEmployee.department) addError('department', 'Department is required')
        if (!newEmployee.position) addError('position', 'Position is required')
        if (!newEmployee.employment_type) addError('employment_type', 'Employment type is required')
        if (!newEmployee.employment_status) addError('employment_status', 'Employment status is required')
        if (!newEmployee.hire_date) addError('hire_date', 'Hire date is required')
        if (!newEmployee.salary && newEmployee.salary !== 0) addError('salary', 'Salary is required')
        if (!newEmployee.payment_frequency) addError('payment_frequency', 'Payment frequency is not set. Please check distributor settings.')
    }
    
    if (step === 4) {
        if (!newEmployee.valid_id_type) addError('valid_id_type', 'ID Type is required')
        if (!newEmployee.id_number) addError('id_number', 'ID Number is required')
        if (!isEditing.value && !newEmployee.valid_id_photo && !existingFiles.valid_id_photo_url) addError('valid_id_photo', 'Valid ID photo is required')
        if (!newEmployee.educational_attainment) addError('educational_attainment', 'Educational attainment is required')
        if (!newEmployee.school_graduated) addError('school_graduated', 'School graduated is required')
        if (!newEmployee.year_graduated) addError('year_graduated', 'Year graduated is required')
        if (!newEmployee.course) addError('course', 'Course is required')
    }
    
    if (step === 5) {
        if (!isEditing.value) {
            if (!newEmployee.resume && !existingFiles.resume_url) addError('resume', 'Resume is required')
            if (!newEmployee.employment_contract && !existingFiles.employment_contract_url) addError('employment_contract', 'Employment contract is required')
            if (!newEmployee.medical_certificate && !existingFiles.medical_certificate_url) addError('medical_certificate', 'Medical certificate is required')
            if (!newEmployee.nbi_clearance && !existingFiles.nbi_clearance_url) addError('nbi_clearance', 'NBI clearance is required')
            if (!newEmployee.police_clearance && !existingFiles.police_clearance_url) addError('police_clearance', 'Police clearance is required')
        }
    }

    if (!isValid) {
        formError.value = "Please fix the errors in the form before proceeding."
        toast.error("Please fix the errors in the form before proceeding.") 
    }

    return isValid
}

const preventInvalidMathChars = (e) => {
  const invalidChars = ['e', 'E', '+', '-'];
  if (invalidChars.includes(e.key)) {
    e.preventDefault();
  }
}

const nextStep = () => {
  if (validateStep(currentStep.value)) {
    if (currentStep.value < steps.length) {
        currentStep.value++
    }
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
    formError.value = null 
    errors.value = {}
  }
}

const capitalize = (str) => {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const formatFrequency = (freq) => {
    if (!freq) return 'Loading...';
    const map = {
        'daily': 'Daily',
        'weekly': 'Weekly',
        'bi-monthly': 'Bi-Monthly',
        'bi_monthly': 'Bi-Monthly', 
        'monthly': 'Monthly'
    };
    return map[freq] || freq.charAt(0).toUpperCase() + freq.slice(1);
}

const handleFileUpload = (event, field) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      toast.error('File size exceeds 5MB limit') 
      event.target.value = ''
      return
    }
    newEmployee[field] = file
    if (errors.value[field]) delete errors.value[field]
  }
}

const removeFile = (field) => {
    newEmployee[field] = null
}

const fetchDepartments = async () => {
  try {
    const response = await axios.get('/hr/positions/departments')
    if (response.data.success) {
      availableDepartments.value = response.data.data.map(dept => dept.name)
    }
  } catch (err) {
    console.error('Failed to fetch departments:', err)
    if (departments.value.length > 0) {
      availableDepartments.value = departments.value
    }
  }
}

const loadPositionsForDepartment = async () => {
  if (!newEmployee.department) {
    availablePositions.value = []
    return
  }
  
  try {
    const currentPositionCache = newEmployee.position
    newEmployee.position = ''
    
    const response = await axios.get('/hr/positions', {
      params: {
        department: newEmployee.department,
        status: 'active',
        per_page: 100 
      }
    })
    
    if (response.data.success) {
      availablePositions.value = response.data.data.data || []
      // restore position if we are editing and it exists in options
      if (isEditing.value && availablePositions.value.some(p => p.title === currentPositionCache)) {
        newEmployee.position = currentPositionCache
      }
    } else {
      availablePositions.value = []
    }
  } catch (err) {
    console.error('Failed to fetch positions:', err)
    availablePositions.value = []
  }
}

const fetchPayrollSettings = async () => {
    try {
        const response = await axios.get('/distributor/payroll-settings')
        if (response.data) {
            let freq = response.data.frequency;
            if (freq === 'bi-monthly') freq = 'bi_monthly'; 
            newEmployee.payment_frequency = freq;
        }
    } catch (err) {
        console.error('Failed to fetch payroll settings:', err)
        toast.error('Could not load payroll settings.')
    }
}

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

const fetchEmployees = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await axios.get('/hr/employees')
    if (response.data.status === 'success') {
      employees.value = response.data.data.employees
      departments.value = response.data.data.departments
      if (response.data.data.permissions) {
        permissions.value = response.data.data.permissions
      }
    }
    
    if (permissions.value.can_view) {
      const statsResponse = await axios.get('/hr/employees/statistics')
      if (statsResponse.data.status === 'success') {
        statistics.value = statsResponse.data.data.statistics
        companyInfo.value = {
          company_name: statsResponse.data.data.company_name
        }
      }
    }
  } catch (err) {
    console.error('Failed to fetch employees:', err)
    error.value = err.response?.data?.message || 'Failed to load employees'
    toast.error('Failed to load employees. You may not have the required permissions.') 
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  isEditing.value = false
  editId.value = null
  showAddModal.value = true
}

// --- FIX: openDocumentModal with lazy loading state ---
const openDocumentModal = async (id) => {
  actionLoading.value = { type: 'doc', id }
  try {
    const response = await axios.get(`/hr/employees/${id}`)
    if (response.data.status === 'success') {
      documentData.value = response.data.data.employee
      showDocumentModal.value = true
    }
  } catch (err) {
    console.error('Failed to load employee document:', err)
    toast.error(err.response?.data?.message || 'Failed to load employee document data.')
  } finally {
    actionLoading.value = { type: null, id: null }
  }
}

// --- FIX: printDocument — opens a new window to avoid modal overflow/clipping ---
const printDocument = () => {
  if (!documentData.value) return

  const contentEl = document.getElementById('printable-content')
  if (!contentEl) return

  const printWindow = window.open('', '_blank', 'width=900,height=700')
  if (!printWindow) {
    toast.error('Pop-up blocked. Please allow pop-ups to print.')
    return
  }

  printWindow.document.write(`<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Employee Record - ${documentData.value.full_name}</title>
  <style>
    * { -webkit-print-color-adjust: exact; print-color-adjust: exact; box-sizing: border-box; }
    @page { margin: 0.75in; }
    body { font-family: Arial, sans-serif; color: #000; font-size: 13px; line-height: 1.5; }
    .no-print { display: none !important; }
    .grid { display: grid; }
    .grid-cols-2 { grid-template-columns: 1fr 1fr; }
    .gap-x-8 { column-gap: 2rem; }
    .gap-y-3 { row-gap: 0.75rem; }
    .gap-8 { gap: 2rem; }
    .col-span-2 { grid-column: span 2; }
    .text-center { text-align: center; }
    .text-3xl { font-size: 1.875rem; }
    .text-xl { font-size: 1.25rem; }
    .text-lg { font-size: 1.125rem; }
    .text-sm { font-size: 0.875rem; }
    .text-xs { font-size: 0.75rem; }
    .font-extrabold { font-weight: 800; }
    .font-bold { font-weight: 700; }
    .font-semibold { font-weight: 600; }
    .uppercase { text-transform: uppercase; }
    .tracking-wider { letter-spacing: 0.05em; }
    .tracking-wide { letter-spacing: 0.025em; }
    .text-gray-600 { color: #4b5563; }
    .text-gray-700 { color: #374151; }
    .text-gray-800 { color: #1f2937; }
    .border-b-2 { border-bottom: 2px solid; }
    .border-b { border-bottom: 1px solid; }
    .border-gray-800 { border-color: #1f2937; }
    .border-gray-300 { border-color: #d1d5db; }
    .border-black { border-color: #000; }
    .border-t { border-top: 1px solid; }
    .mb-8 { margin-bottom: 2rem; }
    .mb-6 { margin-bottom: 1.5rem; }
    .mb-3 { margin-bottom: 0.75rem; }
    .mb-2 { margin-bottom: 0.5rem; }
    .mt-16 { margin-top: 4rem; }
    .mt-2 { margin-top: 0.5rem; }
    .mt-1 { margin-top: 0.25rem; }
    .pb-6 { padding-bottom: 1.5rem; }
    .pb-1 { padding-bottom: 0.25rem; }
    .pt-8 { padding-top: 2rem; }
    .pt-2 { padding-top: 0.5rem; }
    .p-4 { padding: 1rem; }
    .rounded { border-radius: 0.25rem; }
    .bg-gray-100 { background-color: #f3f4f6; }
    .flex { display: flex; }
    .justify-between { justify-content: space-between; }
    .items-center { align-items: center; }
    .mx-auto { margin-left: auto; margin-right: auto; }
    .w-3\/4 { width: 75%; }
    a { color: #2563eb; }
    ul { list-style: disc; padding-left: 1.5rem; }
    li { margin-bottom: 0.25rem; }
  </style>
</head>
<body>
${contentEl.innerHTML}
</body>
</html>`)

  printWindow.document.close()
  printWindow.focus()
  // Give Tailwind/CSS a moment to apply before printing
  setTimeout(() => {
    printWindow.print()
    printWindow.close()
  }, 300)
}

// --- FIX: editEmployee with lazy loading state ---
const editEmployee = async (id) => {
  actionLoading.value = { type: 'edit', id }
  try {
    const response = await axios.get(`/hr/employees/${id}`)
    if (response.data.status === 'success') {
      const emp = response.data.data.employee
      
      isEditing.value = true
      editId.value = emp.id
      
      // Populate fields
      Object.keys(newEmployee).forEach(key => {
        if (emp.hasOwnProperty(key)) {
          newEmployee[key] = emp[key] !== null ? emp[key] : ''
        }
      })
      
      // Handle file URLs mapping — these are now absolute URLs from the fixed controller
      existingFiles.valid_id_photo_url = emp.valid_id_photo_url
      existingFiles.resume_url = emp.resume_url
      existingFiles.employment_contract_url = emp.employment_contract_url
      existingFiles.medical_certificate_url = emp.medical_certificate_url
      existingFiles.nbi_clearance_url = emp.nbi_clearance_url
      existingFiles.police_clearance_url = emp.police_clearance_url
      
      // Clear file objects in newEmployee (we don't re-upload existing files)
      newEmployee.valid_id_photo = null
      newEmployee.resume = null
      newEmployee.employment_contract = null
      newEmployee.medical_certificate = null
      newEmployee.nbi_clearance = null
      newEmployee.police_clearance = null
      
      await loadPositionsForDepartment()
      
      currentStep.value = 1
      showAddModal.value = true
    }
  } catch (err) {
    console.error('Failed to fetch employee details for editing', err)
    toast.error(err.response?.data?.message || 'Could not load employee details.')
  } finally {
    actionLoading.value = { type: null, id: null }
  }
}

const checkAndOpenConfirmDialog = () => {
  if (!validateStep(currentStep.value)) {
    return;
  }
  showConfirmDialog.value = true;
}

const finalizeEmployeeCreation = async () => {
  showConfirmDialog.value = false;

  saving.value = true
  formError.value = null
  errors.value = {}
  
  try {
    const formData = new FormData()
    
    Object.keys(newEmployee).forEach(key => {
      if (key === 'valid_id_photo' || key === 'resume' || key === 'employment_contract' || 
          key === 'medical_certificate' || key === 'nbi_clearance' || key === 'police_clearance') {
        if (newEmployee[key] instanceof File) {
          formData.append(key, newEmployee[key])
        }
      } else {
        if (newEmployee[key] !== null && newEmployee[key] !== undefined) {
          formData.append(key, newEmployee[key])
        }
      }
    })
    
    let response;
    
    if (isEditing.value) {
      formData.append('_method', 'PUT')
      response = await axios.post(`/hr/employees/${editId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    } else {
      response = await axios.post('/hr/employees', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    
    if (response.data.status === 'success') {
      toast.success(isEditing.value ? 'Employee updated successfully!' : 'Employee created and welcome email sent successfully!') 
      closeAddModal()
      fetchEmployees()
    }
  } catch (err) {
    console.error('Failed to process employee:', err)
    
    if (err.response && err.response.status === 422) {
        errors.value = err.response.data.errors 
        formError.value = err.response.data.message || 'Please fix the errors in the form.'
        toast.error(formError.value) 
        
        const errorFields = Object.keys(errors.value)
        if (errorFields.some(f => ['first_name', 'last_name', 'email', 'phone', 'date_of_birth'].includes(f))) {
            currentStep.value = 1
        } else if (errorFields.some(f => ['department', 'position', 'salary'].includes(f))) {
            currentStep.value = 2
        } else if (errorFields.some(f => ['bank_name', 'sss_number', 'tin_number'].includes(f))) {
            currentStep.value = 3
        } else if (errorFields.some(f => ['valid_id_type', 'school_graduated'].includes(f))) {
            currentStep.value = 4
        }
    } else {
        const serverMessage = err.response?.data?.message || err.message;
        toast.error(`Failed: ${serverMessage}`); 
    }
  } finally {
    saving.value = false
  }
}

// --- FIX: regularizeEmployee with lazy loading state ---
const regularizeEmployee = async (id) => {
  if (!confirm('Are you sure you want to regularize this employee?')) return
  
  actionLoading.value = { type: 'regularize', id }
  try {
    const response = await axios.post(`/hr/employees/${id}/regularize`)
    if (response.data.status === 'success') {
      toast.success('Employee regularized successfully!') 
      fetchEmployees()
    }
  } catch (err) {
    console.error('Failed to regularize employee:', err)
    toast.error(err.response?.data?.message || 'Failed to regularize employee') 
  } finally {
    actionLoading.value = { type: null, id: null }
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
  showConfirmDialog.value = false 
  isEditing.value = false
  editId.value = null
  currentStep.value = 1
  formError.value = null
  errors.value = {}
  
  // Clear Existing Files
  Object.keys(existingFiles).forEach(k => existingFiles[k] = null)
  
  const currentFreq = newEmployee.payment_frequency;
  Object.keys(newEmployee).forEach(key => {
    if (key === 'gender') newEmployee[key] = 'male'
    else if (key === 'marital_status') newEmployee[key] = 'single'
    else if (key === 'nationality') newEmployee[key] = 'Filipino'
    else if (key === 'employment_type') newEmployee[key] = 'full_time'
    else if (key === 'employment_status') newEmployee[key] = 'probationary'
    else if (key === 'hire_date') newEmployee[key] = today.value
    else if (key === 'salary_currency') newEmployee[key] = 'PHP'
    else if (key === 'payment_frequency') newEmployee[key] = currentFreq 
    else if (key === 'year_graduated') newEmployee[key] = currentYear
    else if (key === 'salary') newEmployee[key] = 0
    else {
      newEmployee[key] = ''
    }
  })
  
  availablePositions.value = []
}

const exportToCSV = () => {
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
  
  toast.success('CSV exported successfully!') 
}
</script>

<style scoped>
.employees-list {
  min-height: calc(100vh - 80px);
}

/* Animation for step transitions */
@keyframes fade-in {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fade-in 0.3s ease-out;
}

/* Custom scrollbar for modal */
::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }

/* Responsive improvements */
@media (max-width: 640px) {
  .employees-list { padding: 1rem !important; }
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

/* ============================================================
   PRINT STYLES — Fixed so the employee record prints correctly
   ============================================================ */
@media print {
  /* Hide everything by default */
  body * { visibility: hidden; }

  /* Show only the printable document and all its children */
  .printable-document,
  .printable-document * { visibility: visible; }

  /* Override modal constraints so content is NOT clipped */
  .printable-document {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    height: auto !important;
    max-height: none !important;
    overflow: visible !important;
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    flex: none !important;
  }

  /* The scrollable content area — remove scroll clipping */
  .print-container {
    overflow: visible !important;
    max-height: none !important;
    height: auto !important;
    padding: 30px !important;
    flex: none !important;
    display: block !important;
  }

  /* Hide the modal header toolbar (Print/Close buttons) and all other UI */
  .no-print { display: none !important; }

  /* Colour adjust for backgrounds */
  * {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
}
</style>