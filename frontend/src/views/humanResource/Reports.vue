<template>
  <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-6 overflow-x-hidden min-h-[calc(100vh-80px)] relative">
    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-blue-100 text-blue-600 rounded-lg shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </span>
          Human Resources Reports
        </h1>
        <p class="text-sm text-gray-600">Select a category below to view and export specific HR data.</p>
      </div>
      <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
        <button @click="showConfirmDialog = true" :disabled="tableData.length === 0"
                class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-blue-600 text-white rounded-lg shadow-sm hover:bg-blue-700 transition-colors disabled:opacity-50 text-sm font-medium">
          <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Export Current Tab
        </button>
        <button @click="showCustomReportDialog = true" 
                class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg shadow-sm hover:opacity-90 transition-opacity text-sm font-medium">
          <svg class="w-5 h-5 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Custom Builder
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 border-l-4 border-l-blue-500 hover:shadow-md transition-shadow">
        <div class="text-sm font-medium text-gray-500 mb-1">Total Active Employees</div>
        <div class="text-2xl font-bold text-gray-900">{{ formatInt(metrics.totalEmployees) }}</div>
      </div>
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 border-l-4 border-l-indigo-500 hover:shadow-md transition-shadow">
        <div class="text-sm font-medium text-gray-500 mb-1">Defined RBAC Positions</div>
        <div class="text-2xl font-bold text-gray-900">{{ formatInt(metrics.totalPositions) }}</div>
      </div>
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 border-l-4 border-l-emerald-500 hover:shadow-md transition-shadow">
        <div class="text-sm font-medium text-gray-500 mb-1">Period Attendances Logged</div>
        <div class="text-2xl font-bold text-gray-900">{{ formatInt(metrics.totalAttendances) }}</div>
      </div>
      <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-200 border-l-4 border-l-amber-500 hover:shadow-md transition-shadow">
        <div class="text-sm font-medium text-gray-500 mb-1">Period Leave Requests</div>
        <div class="text-2xl font-bold text-gray-900">{{ formatInt(metrics.totalLeaves) }}</div>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-4 flex flex-col sm:flex-row gap-4 items-end">
      <div class="w-full sm:w-1/3">
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
        <input type="date" v-model="filters.startDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-[42px] px-3 border bg-gray-50 text-gray-700" />
      </div>
      <div class="w-full sm:w-1/3">
        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">End Date</label>
        <input type="date" v-model="filters.endDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm h-[42px] px-3 border bg-gray-50 text-gray-700" />
      </div>
      <div class="w-full sm:w-1/3">
        <button @click="fetchData" :disabled="isLoading" class="w-full h-[42px] inline-flex justify-center items-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-800 hover:bg-gray-900 focus:outline-none disabled:opacity-50 transition-colors">
          <span v-if="isLoading" class="flex items-center">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Refreshing Data...
          </span>
          <span v-else>Apply Date Filter</span>
        </button>
      </div>
    </div>

    <div class="mb-6 flex overflow-x-auto hide-scrollbar space-x-2 pb-2">
      <button v-for="cat in categories" :key="cat.id" 
              @click="setCategory(cat.id)"
              :class="[
                'whitespace-nowrap px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200',
                activeCategory === cat.id 
                  ? 'bg-blue-600 text-white shadow-md transform -translate-y-0.5' 
                  : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:text-blue-600'
              ]">
        {{ cat.name }}
      </button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
      <div class="p-4 sm:p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h3 class="text-base font-bold text-gray-900">{{ currentCategoryName }} Data Table</h3>
        <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">{{ tableData.length }} Records</span>
      </div>

      <div v-if="activeCategory === 'rbac'" class="p-6 sm:p-10 bg-gradient-to-b from-slate-50 to-white border-b border-gray-200 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 rounded-full bg-blue-50 opacity-50 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 rounded-full bg-indigo-50 opacity-50 blur-3xl"></div>

        <div class="text-center mb-10 relative z-10">
          <h4 class="text-lg font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-800 to-blue-800 uppercase tracking-widest mb-2">System Hierarchy & Access Flow</h4>
          <p class="text-sm text-gray-500 max-w-2xl mx-auto">Visual representation of role-based permissions, module access, and organizational structure.</p>
        </div>
        
        <div class="flex flex-col items-center w-full max-w-4xl mx-auto relative z-10">
          
          <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-slate-600 to-slate-900 rounded-xl blur opacity-25 group-hover:opacity-50 transition duration-300"></div>
            <div class="relative bg-slate-800 text-white px-8 py-4 rounded-xl font-bold shadow-xl flex flex-col items-center justify-center border border-slate-700 min-w-[220px] transform transition hover:-translate-y-1">
              <svg class="w-6 h-6 text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
              <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest mb-1">Level 1</div>
              <div class="text-lg">System Admin</div>
            </div>
          </div>
          
          <div class="w-1 h-8 bg-gradient-to-b from-slate-800 to-indigo-600"></div>
          
          <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-blue-600 rounded-xl blur opacity-25 group-hover:opacity-50 transition duration-300"></div>
            <div class="relative bg-indigo-600 text-white px-8 py-4 rounded-xl font-bold shadow-xl flex flex-col items-center justify-center border border-indigo-500 min-w-[220px] transform transition hover:-translate-y-1">
              <svg class="w-6 h-6 text-indigo-200 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
              <div class="text-[10px] text-indigo-200 font-semibold uppercase tracking-widest mb-1">Level 2</div>
              <div class="text-lg">Distributor</div>
            </div>
          </div>
          
          <div class="w-full flex flex-col md:flex-row justify-between relative mt-8 md:mt-0">
            
            <div class="hidden md:block absolute top-0 left-[16.66%] right-[16.66%] h-1 bg-indigo-600"></div>
            
            <div class="flex flex-col items-center w-full md:w-1/3 relative mt-6 md:mt-0">
              <div class="hidden md:block absolute top-0 w-1 h-8 bg-indigo-600"></div>
              <div class="md:hidden absolute -top-14 w-1 h-14 bg-gradient-to-b from-indigo-600 to-emerald-500"></div>
              
              <div class="relative group mt-0 md:mt-8 z-10">
                <div class="relative bg-gradient-to-br from-emerald-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg flex flex-col items-center justify-center border border-emerald-400 min-w-[180px] transform transition hover:-translate-y-1">
                  <svg class="w-5 h-5 text-emerald-100 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                  <div class="text-[10px] text-emerald-200 font-semibold uppercase tracking-widest mb-1">Level 3</div>
                  <div class="text-sm">HR Manager</div>
                </div>
              </div>
              <div class="w-0.5 h-6 bg-gray-300"></div>
              <div class="bg-white border border-gray-200 shadow-sm text-gray-700 px-6 py-2.5 rounded-lg font-medium text-xs text-center min-w-[160px]">
                HR Positions
              </div>
            </div>

            <div class="flex flex-col items-center w-full md:w-1/3 relative mt-10 md:mt-0">
              <div class="hidden md:block absolute top-0 w-1 h-8 bg-indigo-600"></div>
              <div class="md:hidden absolute -top-10 w-1 h-10 bg-gray-300 border-l-2 border-dashed border-gray-400"></div>
              
              <div class="relative group mt-0 md:mt-8 z-10">
                <div class="relative bg-gradient-to-br from-blue-500 to-blue-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg flex flex-col items-center justify-center border border-blue-400 min-w-[180px] transform transition hover:-translate-y-1">
                  <svg class="w-5 h-5 text-blue-100 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                  <div class="text-[10px] text-blue-200 font-semibold uppercase tracking-widest mb-1">Level 3</div>
                  <div class="text-sm">Op. Distributor</div>
                </div>
              </div>
              <div class="w-0.5 h-6 bg-gray-300"></div>
              <div class="bg-white border border-gray-200 shadow-sm text-gray-700 px-6 py-2.5 rounded-lg font-medium text-xs text-center min-w-[160px]">
                Op. Positions
              </div>
            </div>

            <div class="flex flex-col items-center w-full md:w-1/3 relative mt-10 md:mt-0">
              <div class="hidden md:block absolute top-0 w-1 h-8 bg-indigo-600"></div>
              <div class="md:hidden absolute -top-10 w-1 h-10 bg-gray-300 border-l-2 border-dashed border-gray-400"></div>
              
              <div class="relative group mt-0 md:mt-8 z-10">
                <div class="relative bg-gradient-to-br from-amber-500 to-amber-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg flex flex-col items-center justify-center border border-amber-400 min-w-[180px] transform transition hover:-translate-y-1">
                  <svg class="w-5 h-5 text-amber-100 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                  <div class="text-[10px] text-amber-200 font-semibold uppercase tracking-widest mb-1">Level 3</div>
                  <div class="text-sm">Finance Manager</div>
                </div>
              </div>
              <div class="w-0.5 h-6 bg-gray-300"></div>
              <div class="bg-white border border-gray-200 shadow-sm text-gray-700 px-6 py-2.5 rounded-lg font-medium text-xs text-center min-w-[160px]">
                Finance Positions
              </div>
            </div>

          </div>
          
          
        </div>
      </div>

      <div class="overflow-x-auto w-full">
        <div class="min-w-[800px] max-h-[600px] overflow-y-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-white sticky top-0 z-10 shadow-sm">
              
              <tr v-if="activeCategory === 'employees'">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Employee Name</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Email</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Department</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Position Title</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Account Status</th>
              </tr>
              
              <tr v-else-if="activeCategory === 'rbac'">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Department</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Position Title (Level 4)</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Permission Module Key</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">View Access</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Manage Access</th>
                <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Approve Access</th>
              </tr>

              <tr v-else-if="activeCategory === 'attendance'">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Date Logged</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Employee Name</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Clock In Time</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Clock Out Time</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Total Hours</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Status</th>
              </tr>

              <tr v-else-if="activeCategory === 'attendance_requests'">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Requested Time</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Employee Name</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Request Type</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Provided Reason</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Status</th>
              </tr>

              <tr v-else-if="activeCategory === 'leave_requests'">
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Employee Name</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Leave Type</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Start Date</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">End Date</th>
                <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 border-b border-gray-200">Approval Status</th>
              </tr>
            </thead>
            
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="tableData.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                  <div class="flex flex-col items-center">
                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <span class="text-sm">No {{ currentCategoryName }} records found for the selected date range.</span>
                  </div>
                </td>
              </tr>
              
              <tr v-for="(row, index) in tableData" :key="index" class="hover:bg-blue-50/50 transition-colors">
                
                <template v-if="activeCategory === 'employees'">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ row.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ row.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ row.department }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ row.position }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="row.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2.5 py-1 rounded text-xs font-semibold capitalize">
                      {{ row.status }}
                    </span>
                  </td>
                </template>

                <template v-else-if="activeCategory === 'rbac'">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ row.department }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ row.position }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold bg-blue-50">{{ row.permission_key }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span v-if="row.can_view" class="text-green-500 font-bold">YES</span><span v-else class="text-red-400 font-bold">NO</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span v-if="row.can_manage" class="text-green-500 font-bold">YES</span><span v-else class="text-red-400 font-bold">NO</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span v-if="row.can_approve" class="text-green-500 font-bold">YES</span><span v-else class="text-red-400 font-bold">NO</span>
                  </td>
                </template>

                <template v-else-if="activeCategory === 'attendance'">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ row.date }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ row.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-emerald-600 font-medium">{{ row.time_in || '--:--' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-amber-600 font-medium">{{ row.time_out || '--:--' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-700">{{ row.total_hours || '0' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ row.status }}</td>
                </template>

                <template v-else-if="activeCategory === 'attendance_requests'">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatDate(row.requested_time) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ row.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium">{{ row.type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 max-w-xs truncate" :title="row.reason">{{ row.reason }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="row.status === 'approved' ? 'bg-green-100 text-green-800' : (row.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')" class="px-2.5 py-1 rounded text-xs font-semibold capitalize">
                      {{ row.status }}
                    </span>
                  </td>
                </template>

                <template v-else-if="activeCategory === 'leave_requests'">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ row.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-purple-600 font-medium">{{ row.type }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ row.start_date }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ row.end_date }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span :class="row.status === 'approved' ? 'bg-green-100 text-green-800' : (row.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800')" class="px-2.5 py-1 rounded text-xs font-semibold capitalize">
                      {{ row.status }}
                    </span>
                  </td>
                </template>

              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showConfirmDialog" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4 transition-all duration-300">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-200">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-4 bg-gradient-to-r from-blue-50 to-white">
          <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900">Confirm Action</h3>
        </div>
        <div class="px-6 py-6">
          <p class="text-gray-600 text-base leading-relaxed">
            Are you sure you want to download the <strong class="text-gray-900">{{ currentCategoryName }}</strong> report? This will compile all currently displayed records into a CSV file for your device.
          </p>
        </div>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3">
          <button @click="showConfirmDialog = false" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 font-medium transition-colors text-sm">Cancel</button>
          <button @click="exportCurrentTabCSV" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium shadow-md hover:shadow-lg transition-all text-sm flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Yes, Download CSV
          </button>
        </div>
      </div>
    </div>

    <div v-if="showCustomReportDialog" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4 py-6 transition-all duration-300">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200 max-h-full flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900">Custom Report Builder</h3>
          <button @click="showCustomReportDialog = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-1.5 rounded-lg transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
          </button>
        </div>
        
        <div class="px-6 py-6 overflow-y-auto flex-1">
          <p class="text-sm text-gray-600 mb-6">Select the parameters below to generate a tailored CSV report containing specific datasets and employee filters.</p>
          
          <div class="space-y-6">
            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">1. Select Report Category</label>
              <select v-model="customReportConfig.reportType" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-gray-50">
                <option value="all">Export All Categories (Master Data Dump)</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-bold text-gray-700 mb-2">2. Filter by Employee</label>
              <select v-model="customReportConfig.employeeId" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-gray-50">
                <option value="all">All Employees</option>
                <option v-for="emp in employeeList" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
              </select>
              <p class="text-xs text-gray-500 mt-1">* Note: RBAC permissions cannot be filtered by employee.</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">3. Start Date</label>
                <input type="date" v-model="customReportConfig.startDate" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-gray-50" />
              </div>
              <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">4. End Date</label>
                <input type="date" v-model="customReportConfig.endDate" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm p-3 border bg-gray-50" />
              </div>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-3 shrink-0">
          <button @click="showCustomReportDialog = false" class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 font-medium transition-colors text-sm">Cancel</button>
          <button @click="generateCustomReport" :disabled="isGeneratingCustom" class="px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:opacity-90 text-white rounded-xl font-medium shadow-md hover:shadow-lg transition-all text-sm disabled:opacity-50 inline-flex items-center">
            <span v-if="isGeneratingCustom" class="flex items-center">
              <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Building CSV...
            </span>
            <span v-else class="flex items-center">
               <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
               Download Custom CSV
            </span>
          </button>
        </div>
      </div>
    </div>

    <div v-if="systemAlert.show" class="fixed bottom-6 right-6 z-[80] animate-in slide-in-from-bottom-5 fade-in duration-300">
      <div class="bg-white rounded-xl shadow-2xl border border-gray-100 border-l-4 p-4 max-w-sm flex items-start gap-3"
           :class="systemAlert.type === 'success' ? 'border-l-emerald-500' : 'border-l-red-500'">
        <div class="shrink-0 mt-0.5">
          <svg v-if="systemAlert.type === 'success'" class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          <svg v-else class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="flex-1">
          <h4 class="text-sm font-bold text-gray-900">{{ systemAlert.title }}</h4>
          <p class="text-sm text-gray-600 mt-1">{{ systemAlert.message }}</p>
        </div>
        <button @click="systemAlert.show = false" class="text-gray-400 hover:text-gray-600">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'

// State Variables
const isLoading = ref(false)
const showConfirmDialog = ref(false)
const showCustomReportDialog = ref(false)
const isGeneratingCustom = ref(false)

// NEW: Global Action Alert State
const systemAlert = ref({
  show: false,
  title: '',
  message: '',
  type: 'success'
})

// NEW: Helper to show alerts
const triggerAlert = (title, message, type = 'success') => {
  systemAlert.value = { show: true, title, message, type }
  setTimeout(() => {
    systemAlert.value.show = false
  }, 4000)
}

const employeeList = ref([])

// Tabs Configuration
const categories = [
  { id: 'employees', name: 'Employee Roster' },
  { id: 'rbac', name: 'Role-Based Access (RBAC)' },
  { id: 'attendance', name: 'Attendance Logs' },
  { id: 'attendance_requests', name: 'Attendance Requests' },
  { id: 'leave_requests', name: 'Leave Requests' }
]

const activeCategory = ref('employees')

const filters = ref({
  startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0], 
  endDate: new Date().toISOString().split('T')[0], 
})

const customReportConfig = ref({
  reportType: 'all',
  employeeId: 'all',
  startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0], 
  endDate: new Date().toISOString().split('T')[0], 
})

// Metrics & Data
const metrics = ref({
  totalEmployees: 0,
  totalPositions: 0,
  totalAttendances: 0,
  totalLeaves: 0
})

const tableData = ref([])

// Computed properties
const currentCategoryName = computed(() => {
  const cat = categories.find(c => c.id === activeCategory.value)
  return cat ? cat.name : ''
})

// Handlers
const setCategory = (id) => {
  activeCategory.value = id
  fetchData()
}

// Utility formatters
const formatInt = (num) => {
  if (!num) return '0'
  return parseInt(num, 10).toLocaleString('en-US')
}

const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute:'2-digit' })
}

// Fetch Main API
const fetchData = async () => {
  try {
    isLoading.value = true
    tableData.value = [] 
    
    const params = {
      reportType: activeCategory.value,
      startDate: filters.value.startDate,
      endDate: filters.value.endDate
    }

    const response = await api.get('/hr/reports', { params })
    
    if (response.data.success) {
      metrics.value = response.data.metrics
      tableData.value = response.data.tableData
      employeeList.value = response.data.employeeList
    }
  } catch (error) {
    console.error("Error fetching HR report data:", error)
    triggerAlert("Sync Failed", "Failed to load HR report data. Check permissions or network.", "error")
  } finally {
    isLoading.value = false
  }
}

// Helper: Build CSV String for a specific block of data
const buildCsvBlock = (type, data) => {
  if (!data || data.length === 0) return ""
  let csv = ""

  if (type === 'employees') {
    csv += "EMPLOYEE ROSTER\r\n"
    csv += "Employee Name,Email,Department,Position,Status\r\n"
    data.forEach(row => csv += `"${row.name}","${row.email}","${row.department}","${row.position}","${row.status}"\r\n`)
  } 
  else if (type === 'rbac') {
    csv += "ROLE-BASED ACCESS CONTROL (RBAC) CONFIGURATION\r\n"
    csv += "Department,Position,Permission Module,Can View,Can Manage,Can Approve\r\n"
    data.forEach(row => csv += `"${row.department}","${row.position}","${row.permission_key}",${row.can_view ? 'Yes' : 'No'},${row.can_manage ? 'Yes' : 'No'},${row.can_approve ? 'Yes' : 'No'}\r\n`)
  }
  else if (type === 'attendance') {
    csv += "ATTENDANCE LOGS\r\n"
    csv += "Date,Employee Name,Time In,Time Out,Total Hours,Status\r\n"
    data.forEach(row => csv += `"${row.date}","${row.name}","${row.time_in || ''}","${row.time_out || ''}",${row.total_hours || 0},"${row.status}"\r\n`)
  }
  else if (type === 'attendance_requests') {
    csv += "ATTENDANCE REQUESTS\r\n"
    csv += "Requested Time,Employee Name,Type,Reason,Status\r\n"
    data.forEach(row => csv += `"${formatDate(row.requested_time)}","${row.name}","${row.type}","${row.reason || ''}","${row.status}"\r\n`)
  }
  else if (type === 'leave_requests') {
    csv += "LEAVE REQUESTS\r\n"
    csv += "Employee Name,Type,Start Date,End Date,Reason,Status\r\n"
    data.forEach(row => csv += `"${row.name}","${row.type}","${row.start_date}","${row.end_date}","${row.reason || ''}","${row.status}"\r\n`)
  }
  return csv + "\r\n"
}

// Trigger Download Blob
const downloadCsvFile = (content, filename) => {
  const blob = new Blob([content], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement("a")
  link.setAttribute("href", url)
  link.setAttribute("download", filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

// Current Tab Export
const exportCurrentTabCSV = () => {
  showConfirmDialog.value = false
  if(tableData.value.length === 0) {
    triggerAlert("Action Blocked", "No data available to export.", "error")
    return
  }
  let csvContent = "\uFEFF" 
  csvContent += buildCsvBlock(activeCategory.value, tableData.value)
  downloadCsvFile(csvContent, `HR_Report_${activeCategory.value.toUpperCase()}_${new Date().getTime()}.csv`)
  
  // Trigger success alert
  triggerAlert("Export Successful", `The ${currentCategoryName.value} report has been downloaded.`)
}

// Custom Report Generation
const generateCustomReport = async () => {
  try {
    isGeneratingCustom.value = true
    
    const params = {
      reportType: customReportConfig.value.reportType,
      employeeId: customReportConfig.value.employeeId,
      startDate: customReportConfig.value.startDate,
      endDate: customReportConfig.value.endDate
    }

    const response = await api.get('/hr/reports', { params })
    
    if (response.data.success) {
      let csvContent = "\uFEFF"
      csvContent += "CUSTOM HR REPORT DUMP\r\n"
      csvContent += `Generated Range: ${params.startDate} to ${params.endDate}\r\n\r\n`

      if (params.reportType === 'all') {
        const d = response.data.tableData
        csvContent += buildCsvBlock('employees', d.employees)
        csvContent += buildCsvBlock('rbac', d.rbac)
        csvContent += buildCsvBlock('attendance', d.attendance)
        csvContent += buildCsvBlock('attendance_requests', d.attendance_requests)
        csvContent += buildCsvBlock('leave_requests', d.leave_requests)
      } else {
        csvContent += buildCsvBlock(params.reportType, response.data.tableData)
      }

      downloadCsvFile(csvContent, `HR_Custom_Export_${new Date().getTime()}.csv`)
      showCustomReportDialog.value = false
      
      // Trigger success alert
      triggerAlert("Custom Export Complete", "Your tailored HR report has been successfully generated.")
    }
  } catch (error) {
    console.error("Error generating custom report:", error)
    triggerAlert("Generation Failed", "Failed to build custom HR report. Please try again.", "error")
  } finally {
    isGeneratingCustom.value = false
  }
}

// Initial Fetch
onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>