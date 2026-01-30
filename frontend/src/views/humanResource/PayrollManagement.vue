<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Payroll Management</h1>
            <p class="text-gray-600">Process employee payroll by department</p>
          </div>
          <div class="flex items-center space-x-4">
            <button @click="downloadPayroll" class="btn-secondary">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Export
              </div>
            </button>
            <button @click="processPayroll" class="btn-primary">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Process Payroll
              </div>
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="p-4 md:p-6">
      <!-- Progress Steps -->
      <div class="mb-6 md:mb-8">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <div v-for="(step, index) in steps" :key="step.id" 
               class="flex-1 flex items-center w-full md:w-auto">
            <div class="flex flex-col items-center">
              <div :class="[
                'w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300',
                step.id < currentStep 
                  ? 'bg-blue-600 border-blue-600 text-white' 
                  : step.id === currentStep
                  ? 'border-blue-600 text-blue-600 bg-white'
                  : 'border-gray-300 text-gray-400 bg-white'
              ]">
                <svg v-if="step.id === 1" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <svg v-else-if="step.id === 2" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <span :class="[
                'mt-2 text-sm font-medium',
                step.id <= currentStep ? 'text-blue-600' : 'text-gray-500'
              ]">{{ step.title }}</span>
            </div>
            <div v-if="index < steps.length - 1" 
                 :class="[
                   'hidden md:block flex-1 h-1 mx-4',
                   step.id < currentStep ? 'bg-blue-600' : 'bg-gray-300'
                 ]"></div>
          </div>
        </div>
      </div>

      <!-- Step Content -->
      <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 md:p-6">
        <!-- Step 1: Department & Employee Selection -->
        <div v-if="currentStep === 1" class="space-y-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-semibold text-gray-800">Select Departments & Employees</h2>
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-3 md:space-y-0 md:space-x-4">
              <div class="relative w-full md:w-auto">
                <svg class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input v-model="searchQuery" type="text" 
                       placeholder="Search employees..." 
                       class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg w-full md:w-64 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <button @click="toggleSelectAll" class="text-sm text-blue-600 hover:text-blue-800 whitespace-nowrap">
                {{ selectAll ? 'Deselect All' : 'Select All' }}
              </button>
            </div>
          </div>

          <!-- Department Summary -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="dept in departmentStats" :key="dept.name" 
                 class="bg-white border rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
                 :class="getDepartmentBorder(dept.name)">
              <div class="flex items-center justify-between">
                <div>
                  <div class="text-sm font-medium text-gray-600">{{ dept.name }}</div>
                  <div class="text-2xl font-bold mt-1">{{ dept.employeeCount }}</div>
                </div>
                <div class="p-2 rounded-full" :class="getDepartmentBg(dept.name)">
                  <svg class="w-5 h-5" :class="getDepartmentText(dept.name)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
              </div>
              <div class="mt-3 flex justify-between text-sm">
                <span class="text-gray-500">Selected: {{ dept.selectedCount }}</span>
                <button @click="toggleDepartment(dept.name)" 
                        class="text-blue-600 hover:text-blue-800 font-medium">
                  {{ dept.selectedCount === dept.employeeCount ? 'Deselect All' : 'Select All' }}
                </button>
              </div>
            </div>
          </div>

          <!-- Department Tabs -->
          <div class="border-b border-gray-200">
            <nav class="flex space-x-8 overflow-x-auto">
              <button v-for="dept in departments" :key="dept"
                      @click="activeDepartment = dept"
                      :class="[
                        'py-3 px-1 text-sm font-medium whitespace-nowrap border-b-2',
                        activeDepartment === dept
                          ? 'border-blue-500 text-blue-600'
                          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                      ]">
                {{ dept }}
                <span class="ml-2 px-2 py-0.5 text-xs rounded-full" 
                      :class="activeDepartment === dept ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'">
                  {{ getEmployeesByDepartment(dept).length }}
                </span>
              </button>
            </nav>
          </div>

          <!-- Employee Table by Department -->
          <div class="overflow-x-auto">
            <div class="flex items-center justify-between py-4 px-2">
              <h3 class="text-lg font-medium text-gray-900">{{ activeDepartment }} Department</h3>
              <div class="text-sm text-gray-600">
                {{ getSelectedCountByDepartment(activeDepartment) }} of {{ getEmployeesByDepartment(activeDepartment).length }} selected
              </div>
            </div>
            
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">
                    <input type="checkbox" 
                           :checked="isDepartmentAllSelected(activeDepartment)"
                           @change="toggleSelectDepartmentAll(activeDepartment)" 
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Employee
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Position
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Type
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pay Frequency
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Basic Salary
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="employee in getEmployeesByDepartment(activeDepartment)" :key="employee.id" 
                    :class="['hover:bg-gray-50 transition-colors duration-150', selectedEmployees.includes(employee.id) ? getSelectedRowBg(employee.department) : '']">
                  <td class="px-4 py-4 whitespace-nowrap">
                    <input type="checkbox" :value="employee.id" v-model="selectedEmployees" 
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full flex items-center justify-center" :class="getDepartmentBg(employee.department)">
                          <svg class="w-5 h-5" :class="getDepartmentText(employee.department)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                          </svg>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                        <div class="text-sm text-gray-500">{{ employee.employeeId }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ employee.position }}
                    <div v-if="employee.salaryGrade" class="text-xs text-gray-500 mt-1">
                      Grade: {{ employee.salaryGrade }}
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                          :class="employee.employmentType === 'Full-time' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'">
                      {{ employee.employmentType }}
                    </span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ employee.payFrequency }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ formatCurrency(employee.basicSalary) }}</div>
                    <div v-if="employee.allowances > 0" class="text-xs text-green-600 mt-1">
                      +{{ formatCurrency(employee.allowances) }} allowances
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex flex-col md:flex-row justify-between items-center pt-6 border-t border-gray-200 gap-4">
            <div class="text-sm text-gray-600">
              {{ selectedEmployees.length }} of {{ filteredEmployees.length }} employees selected across {{ departments.length }} departments
            </div>
            <button @click="nextStep" :disabled="selectedEmployees.length === 0" 
                    :class="[
                      'btn-primary w-full md:w-auto',
                      selectedEmployees.length === 0 ? 'opacity-50 cursor-not-allowed' : ''
                    ]">
              <div class="flex items-center justify-center">
                Continue to Payroll
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Step 2: Payroll Details by Department -->
        <div v-if="currentStep === 2" class="space-y-6">
          <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h2 class="text-xl font-semibold text-gray-800">Payroll Details by Department</h2>
            <div class="flex flex-col md:flex-row items-start md:items-center space-y-3 md:space-y-0 md:space-x-4">
              <div class="text-sm text-gray-600">
                Pay Period: {{ payPeriod }}
              </div>
              <button @click="showPayrollSettings = true" class="text-blue-600 hover:text-blue-800 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Settings
              </button>
            </div>
          </div>

          <!-- Payroll Settings Modal -->
          <div v-if="showPayrollSettings" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
              <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Payroll Settings</h3>
              </div>
              <div class="p-6 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Pay Period</label>
                  <input v-model="payPeriod" type="text" 
                         class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                  <select v-model="paymentMethod" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Check">Check</option>
                    <option value="Cash">Cash</option>
                  </select>
                </div>
              </div>
              <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                <button @click="showPayrollSettings = false" class="btn-secondary w-full sm:w-auto">
                  Cancel
                </button>
                <button @click="savePayrollSettings" class="btn-primary w-full sm:w-auto">
                  Save
                </button>
              </div>
            </div>
          </div>

          <!-- Department Payroll Summary -->
          <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900">Department Payroll Summary</h3>
            
            <!-- Department Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
              <div v-for="dept in getDepartmentPayrollSummary()" :key="dept.name" 
                   class="bg-white border rounded-lg p-4" :class="getDepartmentBorder(dept.name)">
                <div class="flex items-center justify-between mb-3">
                  <div class="flex items-center">
                    <div class="p-2 rounded-full" :class="getDepartmentBg(dept.name)">
                      <svg class="w-4 h-4" :class="getDepartmentText(dept.name)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                      </svg>
                    </div>
                    <span class="ml-3 font-medium text-gray-900">{{ dept.name }}</span>
                  </div>
                  <span class="text-sm font-semibold" :class="getDepartmentText(dept.name)">
                    {{ dept.employeeCount }} emp
                  </span>
                </div>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Gross Pay:</span>
                    <span class="font-medium">{{ formatCurrency(dept.totalGross) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Deductions:</span>
                    <span class="font-medium text-red-600">{{ formatCurrency(dept.totalDeductions) }}</span>
                  </div>
                  <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="font-medium">Net Pay:</span>
                    <span class="font-bold">{{ formatCurrency(dept.totalNet) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Department Payroll Breakdown -->
            <div class="space-y-4">
              <div v-for="dept in departments" :key="dept" class="border border-gray-200 rounded-lg overflow-hidden">
                <!-- Department Header -->
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="p-2 rounded-full mr-3" :class="getDepartmentBg(dept)">
                      <svg class="w-4 h-4" :class="getDepartmentText(dept)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                      </svg>
                    </div>
                    <h4 class="font-medium text-gray-900">{{ dept }} Department</h4>
                    <span class="ml-3 text-sm text-gray-500">
                      {{ getSelectedCountByDepartment(dept) }} employees selected
                    </span>
                  </div>
                  <div class="text-sm font-medium">
                    Total: {{ formatCurrency(getDepartmentTotalNet(dept)) }}
                  </div>
                </div>

                <!-- Department Employee Table -->
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Employee
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Gross Pay
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Deductions
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Net Pay
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                          Status
                        </th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="employee in getEmployeesByDepartment(dept).filter(e => selectedEmployees.includes(e.id))" 
                          :key="employee.id" class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-4 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="ml-4">
                              <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                              <div class="text-sm text-gray-500">{{ employee.position }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                          {{ formatCurrency(calculateEmployeeGross(employee)) }}
                          <div class="text-xs text-gray-500 mt-1">
                            Base: {{ formatCurrency(employee.basicSalary) }}
                            <span v-if="employee.allowances > 0" class="text-green-600">
                              +{{ formatCurrency(employee.allowances) }}
                            </span>
                          </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900">{{ formatCurrency(calculateEmployeeDeductions(employee)) }}</div>
                          <div class="text-xs text-gray-500 mt-1 flex flex-wrap gap-2">
                            <span>SSS: {{ formatCurrency(employee.deductions.sss) }}</span>
                            <span>Tax: {{ formatCurrency(employee.deductions.tax) }}</span>
                            <span>PhilHealth: {{ formatCurrency(employee.deductions.philhealth) }}</span>
                          </div>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                          <span class="text-sm font-semibold text-gray-900">
                            {{ formatCurrency(calculateEmployeeNet(employee)) }}
                          </span>
                        </td>
                        <td class="px-4 py-4 whitespace-nowrap">
                          <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Pending
                          </span>
                        </td>
                      </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                      <tr>
                        <td colspan="5" class="px-4 py-3 text-sm font-medium text-gray-900">
                          <div class="flex justify-between items-center">
                            <span>{{ dept }} Department Total</span>
                            <div class="flex space-x-6">
                              <span>Gross: {{ formatCurrency(getDepartmentTotalGross(dept)) }}</span>
                              <span>Deductions: {{ formatCurrency(getDepartmentTotalDeductions(dept)) }}</span>
                              <span class="font-bold">Net: {{ formatCurrency(getDepartmentTotalNet(dept)) }}</span>
                            </div>
                          </div>
                        </td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col md:flex-row justify-between items-center pt-6 border-t border-gray-200 gap-4">
            <button @click="prevStep" class="btn-secondary w-full md:w-auto">
              <div class="flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
              </div>
            </button>
            <button @click="nextStep" class="btn-primary w-full md:w-auto">
              <div class="flex items-center justify-center">
                Review & Approve
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </button>
          </div>
        </div>

        <!-- Step 3: Review & Approve -->
        <div v-if="currentStep === 3" class="space-y-6">
          <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Review & Approve Payroll</h2>
            <p class="text-gray-600 mt-2">Review payroll by department before final processing</p>
          </div>

          <!-- Department Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="dept in getDepartmentPayrollSummary()" :key="dept.name" 
                 class="bg-white border rounded-lg p-4 shadow-sm" :class="getDepartmentBorder(dept.name)">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                  <div class="p-2 rounded-full mr-3" :class="getDepartmentBg(dept.name)">
                    <svg class="w-4 h-4" :class="getDepartmentText(dept.name)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                  </div>
                  <span class="font-medium text-gray-900">{{ dept.name }}</span>
                </div>
                <span class="text-sm font-semibold" :class="getDepartmentText(dept.name)">
                  {{ dept.employeeCount }}
                </span>
              </div>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Employees:</span>
                  <span class="font-medium">{{ dept.selectedCount }} / {{ dept.employeeCount }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Net Pay:</span>
                  <span class="font-bold">{{ formatCurrency(dept.totalNet) }}</span>
                </div>
                <div class="pt-2 border-t border-gray-100">
                  <div class="text-xs text-gray-500">
                    Avg: {{ formatCurrency(dept.averagePay) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payroll Summary -->
          <div class="bg-gray-50 rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h4 class="text-sm font-medium text-gray-700 mb-4">Payroll Information</h4>
                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Pay Period</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ payPeriod }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Payment Method</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ paymentMethod }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Total Employees</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ selectedEmployees.length }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Departments</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ getSelectedDepartments().length }}</dd>
                  </div>
                </dl>
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-700 mb-4">Financial Summary</h4>
                <dl class="space-y-3">
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Total Gross Pay</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(calculateTotalGross()) }}</dd>
                  </div>
                  <div class="flex justify-between">
                    <dt class="text-sm text-gray-600">Total Deductions</dt>
                    <dd class="text-sm font-medium text-gray-900">{{ formatCurrency(calculateTotalDeductions()) }}</dd>
                  </div>
                  <div class="flex justify-between border-t border-gray-200 pt-3">
                    <dt class="text-sm font-medium text-gray-900">Total Net Pay</dt>
                    <dd class="text-sm font-bold text-gray-900">{{ formatCurrency(calculateTotalNet()) }}</dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>

          <!-- Approval Section -->
          <div class="border border-gray-200 rounded-lg p-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Approval</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Approved By</label>
                <select v-model="approvedBy" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Select approver</option>
                  <option value="Finance Manager">Finance Manager</option>
                  <option value="HR Director">HR Director</option>
                  <option value="Company Owner">Company Owner</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Department Head Review</label>
                <div class="space-y-2">
                  <div v-for="dept in getSelectedDepartments()" :key="dept" class="flex items-center">
                    <input type="checkbox" :id="'dept-' + dept" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label :for="'dept-' + dept" class="ml-2 block text-sm text-gray-900">
                      {{ dept }} Department reviewed
                    </label>
                  </div>
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea v-model="payrollNotes" rows="3" 
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Add any remarks or notes for this payroll..."></textarea>
              </div>
              <div class="flex items-start">
                <input v-model="confirmedApproval" type="checkbox" id="approvalCheck" 
                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1">
                <label for="approvalCheck" class="ml-2 block text-sm text-gray-900">
                  I confirm that all payroll information is accurate and ready for processing
                </label>
              </div>
            </div>
          </div>

          <div class="flex flex-col md:flex-row justify-between items-center pt-6 border-t border-gray-200 gap-4">
            <button @click="prevStep" class="btn-secondary w-full md:w-auto">
              <div class="flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
              </div>
            </button>
            <div class="flex flex-col sm:flex-row w-full md:w-auto space-y-3 sm:space-y-0 sm:space-x-3">
              <button @click="saveAsDraft" class="btn-secondary">
                Save as Draft
              </button>
              <button @click="approvePayroll" :disabled="!canApprove" 
                      :class="[
                        'btn-primary',
                        !canApprove ? 'opacity-50 cursor-not-allowed' : ''
                      ]">
                <div class="flex items-center justify-center">
                  Approve & Process
                  <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Success Modal -->
      <div v-if="showSuccessModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
          <div class="p-6 md:p-8 text-center">
            <div class="w-16 h-16 md:w-20 md:h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
              <svg class="w-8 h-8 md:w-10 md:h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">Payroll Processed Successfully!</h3>
            <p class="text-gray-600 mb-6">
              The payroll for {{ selectedEmployees.length }} employees across {{ getSelectedDepartments().length }} departments has been processed.
            </p>
            <div class="space-y-4">
              <button @click="downloadPayslips" class="w-full btn-primary">
                <div class="flex items-center justify-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                  </svg>
                  Download Department Reports
                </div>
              </button>
              <button @click="resetPayroll" class="w-full btn-secondary">
                Process Another Payroll
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'PayrollManagement',
  data() {
    return {
      currentStep: 1,
      steps: [
        { id: 1, title: 'Select Departments', icon: 'building' },
        { id: 2, title: 'Payroll Details', icon: 'document' },
        { id: 3, title: 'Review & Approve', icon: 'check' }
      ],
      searchQuery: '',
      selectAll: false,
      selectedEmployees: [],
      activeDepartment: 'All',
      showPayrollSettings: false,
      payPeriod: '1–15 Jan 2026',
      paymentMethod: 'Bank Transfer',
      approvedBy: '',
      payrollNotes: '',
      confirmedApproval: false,
      processingDate: 'January 15, 2026',
      showSuccessModal: false,
      
      // Sample employee data with departments
      employees: [
        {
          id: 1,
          employeeId: 'EMP-001',
          name: 'Juan Dela Cruz',
          department: 'Finance',
          position: 'Senior Accountant',
          employmentType: 'Full-time',
          payFrequency: 'Monthly',
          basicSalary: 45000,
          allowances: 5000,
          salaryGrade: 'SG-18',
          dateHired: '2020-03-15',
          deductions: {
            sss: 1350,
            philhealth: 1125,
            pagibig: 100,
            tax: 4500,
            others: 0
          },
          overtime: 8,
          overtimeRate: 375,
          attendance: {
            daysWorked: 10,
            absences: 0,
            lateMinutes: 30
          }
        },
        {
          id: 2,
          employeeId: 'EMP-002',
          name: 'Maria Santos',
          department: 'Finance',
          position: 'Accounting Assistant',
          employmentType: 'Full-time',
          payFrequency: 'Monthly',
          basicSalary: 35000,
          allowances: 3000,
          salaryGrade: 'SG-12',
          dateHired: '2021-06-20',
          deductions: {
            sss: 1050,
            philhealth: 875,
            pagibig: 100,
            tax: 3000,
            others: 0
          },
          overtime: 4,
          overtimeRate: 291.67,
          attendance: {
            daysWorked: 10,
            absences: 1,
            lateMinutes: 0
          }
        },
        {
          id: 3,
          employeeId: 'EMP-003',
          name: 'Pedro Reyes',
          department: 'IT',
          position: 'Software Developer',
          employmentType: 'Full-time',
          payFrequency: 'Monthly',
          basicSalary: 55000,
          allowances: 6000,
          salaryGrade: 'SG-22',
          dateHired: '2019-11-10',
          deductions: {
            sss: 1650,
            philhealth: 1375,
            pagibig: 100,
            tax: 6000,
            others: 1500
          },
          overtime: 16,
          overtimeRate: 458.33,
          attendance: {
            daysWorked: 9,
            absences: 0,
            lateMinutes: 60
          }
        },
        {
          id: 4,
          employeeId: 'EMP-004',
          name: 'Ana Lim',
          department: 'HR',
          position: 'HR Specialist',
          employmentType: 'Part-time',
          payFrequency: 'Bi-Monthly',
          basicSalary: 30000,
          allowances: 3000,
          salaryGrade: 'SG-11',
          dateHired: '2022-02-28',
          deductions: {
            sss: 900,
            philhealth: 750,
            pagibig: 100,
            tax: 2250,
            others: 0
          },
          overtime: 4,
          overtimeRate: 250,
          attendance: {
            daysWorked: 8,
            absences: 2,
            lateMinutes: 15
          }
        },
        {
          id: 5,
          employeeId: 'EMP-005',
          name: 'Robert Sy',
          department: 'Sales',
          position: 'Sales Executive',
          employmentType: 'Full-time',
          payFrequency: 'Monthly',
          basicSalary: 40000,
          allowances: 15000,
          salaryGrade: 'SG-15',
          dateHired: '2020-08-05',
          deductions: {
            sss: 1200,
            philhealth: 1000,
            pagibig: 100,
            tax: 4000,
            others: 0
          },
          overtime: 20,
          overtimeRate: 333.33,
          attendance: {
            daysWorked: 10,
            absences: 0,
            lateMinutes: 0
          }
        },
        {
          id: 6,
          employeeId: 'EMP-006',
          name: 'Susan Tan',
          department: 'Sales',
          position: 'Sales Manager',
          employmentType: 'Full-time',
          payFrequency: 'Monthly',
          basicSalary: 65000,
          allowances: 20000,
          salaryGrade: 'SG-24',
          dateHired: '2018-04-12',
          deductions: {
            sss: 1950,
            philhealth: 1625,
            pagibig: 100,
            tax: 8500,
            others: 0
          },
          overtime: 12,
          overtimeRate: 541.67,
          attendance: {
            daysWorked: 10,
            absences: 0,
            lateMinutes: 45
          }
        },
        {
          id: 7,
          employeeId: 'EMP-007',
          name: 'Michael Chen',
          department: 'Operations',
          position: 'Operations Manager',
          employmentType: 'Full-time',
          payFrequency: 'Monthly',
          basicSalary: 60000,
          allowances: 8000,
          salaryGrade: 'SG-23',
          dateHired: '2019-07-30',
          deductions: {
            sss: 1800,
            philhealth: 1500,
            pagibig: 100,
            tax: 7500,
            others: 0
          },
          overtime: 12,
          overtimeRate: 500,
          attendance: {
            daysWorked: 10,
            absences: 1,
            lateMinutes: 0
          }
        },
        {
          id: 8,
          employeeId: 'EMP-008',
          name: 'Lisa Wong',
          department: 'Operations',
          position: 'Operations Assistant',
          employmentType: 'Contractual',
          payFrequency: 'Weekly',
          basicSalary: 25000,
          allowances: 2000,
          salaryGrade: 'SG-8',
          dateHired: '2023-01-15',
          deductions: {
            sss: 750,
            philhealth: 625,
            pagibig: 100,
            tax: 1500,
            others: 500
          },
          overtime: 8,
          overtimeRate: 208.33,
          attendance: {
            daysWorked: 9,
            absences: 0,
            lateMinutes: 20
          }
        }
      ]
    }
  },
  computed: {
    filteredEmployees() {
      if (!this.searchQuery) return this.employees
      const query = this.searchQuery.toLowerCase()
      return this.employees.filter(emp =>
        emp.name.toLowerCase().includes(query) ||
        emp.employeeId.toLowerCase().includes(query) ||
        emp.department.toLowerCase().includes(query) ||
        emp.position.toLowerCase().includes(query)
      )
    },
    departments() {
      return [...new Set(this.employees.map(emp => emp.department))].sort()
    },
    selectedEmployeeDetails() {
      return this.employees.filter(emp => this.selectedEmployees.includes(emp.id))
    },
    departmentStats() {
      return this.departments.map(dept => {
        const deptEmployees = this.getEmployeesByDepartment(dept)
        const selectedDeptEmployees = deptEmployees.filter(emp => 
          this.selectedEmployees.includes(emp.id)
        )
        return {
          name: dept,
          employeeCount: deptEmployees.length,
          selectedCount: selectedDeptEmployees.length,
          totalSalary: deptEmployees.reduce((sum, emp) => sum + emp.basicSalary, 0)
        }
      })
    },
    canApprove() {
      return this.approvedBy && this.confirmedApproval
    }
  },
  methods: {
    getDepartmentClass(department) {
      if (department === 'Finance') return 'bg-blue-100 text-blue-800'
      if (department === 'IT') return 'bg-purple-100 text-purple-800'
      if (department === 'HR') return 'bg-pink-100 text-pink-800'
      if (department === 'Sales') return 'bg-orange-100 text-orange-800'
      if (department === 'Operations') return 'bg-green-100 text-green-800'
      return 'bg-gray-100 text-gray-800'
    },
    getDepartmentBg(department) {
      if (department === 'Finance') return 'bg-blue-100'
      if (department === 'IT') return 'bg-purple-100'
      if (department === 'HR') return 'bg-pink-100'
      if (department === 'Sales') return 'bg-orange-100'
      if (department === 'Operations') return 'bg-green-100'
      return 'bg-gray-100'
    },
    getDepartmentText(department) {
      if (department === 'Finance') return 'text-blue-600'
      if (department === 'IT') return 'text-purple-600'
      if (department === 'HR') return 'text-pink-600'
      if (department === 'Sales') return 'text-orange-600'
      if (department === 'Operations') return 'text-green-600'
      return 'text-gray-600'
    },
    getDepartmentBorder(department) {
      if (department === 'Finance') return 'border-blue-200'
      if (department === 'IT') return 'border-purple-200'
      if (department === 'HR') return 'border-pink-200'
      if (department === 'Sales') return 'border-orange-200'
      if (department === 'Operations') return 'border-green-200'
      return 'border-gray-200'
    },
    getSelectedRowBg(department) {
      if (department === 'Finance') return 'bg-blue-50'
      if (department === 'IT') return 'bg-purple-50'
      if (department === 'HR') return 'bg-pink-50'
      if (department === 'Sales') return 'bg-orange-50'
      if (department === 'Operations') return 'bg-green-50'
      return 'bg-gray-50'
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2
      }).format(amount)
    },
    getEmployeesByDepartment(department) {
      if (!this.searchQuery) {
        return this.employees.filter(emp => emp.department === department)
      }
      return this.filteredEmployees.filter(emp => emp.department === department)
    },
    getSelectedCountByDepartment(department) {
      const deptEmployees = this.getEmployeesByDepartment(department)
      return deptEmployees.filter(emp => this.selectedEmployees.includes(emp.id)).length
    },
    isDepartmentAllSelected(department) {
      const deptEmployees = this.getEmployeesByDepartment(department)
      if (deptEmployees.length === 0) return false
      return deptEmployees.every(emp => this.selectedEmployees.includes(emp.id))
    },
    toggleSelectDepartmentAll(department) {
      const deptEmployees = this.getEmployeesByDepartment(department)
      const allSelected = this.isDepartmentAllSelected(department)
      
      if (allSelected) {
        // Deselect all employees in this department
        this.selectedEmployees = this.selectedEmployees.filter(id => 
          !deptEmployees.map(emp => emp.id).includes(id)
        )
      } else {
        // Select all employees in this department
        const newSelected = deptEmployees.map(emp => emp.id)
        this.selectedEmployees = [...new Set([...this.selectedEmployees, ...newSelected])]
      }
    },
    toggleDepartment(department) {
      const deptEmployees = this.getEmployeesByDepartment(department)
      const allSelected = deptEmployees.every(emp => this.selectedEmployees.includes(emp.id))
      
      if (allSelected) {
        // Deselect all employees in this department
        this.selectedEmployees = this.selectedEmployees.filter(id => 
          !deptEmployees.map(emp => emp.id).includes(id)
        )
      } else {
        // Select all employees in this department
        const newSelected = deptEmployees.map(emp => emp.id)
        this.selectedEmployees = [...new Set([...this.selectedEmployees, ...newSelected])]
      }
    },
    toggleSelectAll() {
      if (this.selectAll) {
        this.selectedEmployees = this.filteredEmployees.map(emp => emp.id)
      } else {
        this.selectedEmployees = []
      }
    },
    nextStep() {
      if (this.currentStep < this.steps.length) {
        this.currentStep++
      }
    },
    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--
      }
    },
    calculateEmployeeGross(employee) {
      const overtimePay = employee.overtime * employee.overtimeRate
      return employee.basicSalary + employee.allowances + overtimePay
    },
    calculateEmployeeDeductions(employee) {
      return Object.values(employee.deductions).reduce((a, b) => a + b, 0)
    },
    calculateEmployeeNet(employee) {
      return this.calculateEmployeeGross(employee) - this.calculateEmployeeDeductions(employee)
    },
    calculateTotalGross() {
      return this.selectedEmployeeDetails.reduce((total, emp) => {
        return total + this.calculateEmployeeGross(emp)
      }, 0)
    },
    calculateTotalDeductions() {
      return this.selectedEmployeeDetails.reduce((total, emp) => {
        return total + this.calculateEmployeeDeductions(emp)
      }, 0)
    },
    calculateTotalNet() {
      return this.calculateTotalGross() - this.calculateTotalDeductions()
    },
    getDepartmentTotalGross(department) {
      const deptEmployees = this.getEmployeesByDepartment(department)
        .filter(emp => this.selectedEmployees.includes(emp.id))
      return deptEmployees.reduce((total, emp) => total + this.calculateEmployeeGross(emp), 0)
    },
    getDepartmentTotalDeductions(department) {
      const deptEmployees = this.getEmployeesByDepartment(department)
        .filter(emp => this.selectedEmployees.includes(emp.id))
      return deptEmployees.reduce((total, emp) => total + this.calculateEmployeeDeductions(emp), 0)
    },
    getDepartmentTotalNet(department) {
      return this.getDepartmentTotalGross(department) - this.getDepartmentTotalDeductions(department)
    },
    getDepartmentPayrollSummary() {
      return this.departments.map(dept => {
        const deptEmployees = this.getEmployeesByDepartment(dept)
        const selectedDeptEmployees = deptEmployees.filter(emp => 
          this.selectedEmployees.includes(emp.id)
        )
        const totalGross = selectedDeptEmployees.reduce((sum, emp) => 
          sum + this.calculateEmployeeGross(emp), 0
        )
        const totalDeductions = selectedDeptEmployees.reduce((sum, emp) => 
          sum + this.calculateEmployeeDeductions(emp), 0
        )
        const totalNet = totalGross - totalDeductions
        const averagePay = selectedDeptEmployees.length > 0 
          ? totalNet / selectedDeptEmployees.length 
          : 0
        
        return {
          name: dept,
          employeeCount: deptEmployees.length,
          selectedCount: selectedDeptEmployees.length,
          totalGross,
          totalDeductions,
          totalNet,
          averagePay
        }
      }).filter(dept => dept.selectedCount > 0)
    },
    getSelectedDepartments() {
      const selectedDepts = new Set()
      this.selectedEmployeeDetails.forEach(emp => {
        selectedDepts.add(emp.department)
      })
      return Array.from(selectedDepts)
    },
    savePayrollSettings() {
      this.showPayrollSettings = false
    },
    downloadPayroll() {
      alert('Department payroll export functionality would be implemented here')
    },
    processPayroll() {
      this.currentStep = 1
      this.selectedEmployees = []
    },
    saveAsDraft() {
      alert('Payroll saved as draft')
    },
    approvePayroll() {
      this.showSuccessModal = true
    },
    downloadPayslips() {
      alert('Department payroll reports download functionality would be implemented here')
    },
    resetPayroll() {
      this.showSuccessModal = false
      this.currentStep = 1
      this.selectedEmployees = []
      this.approvedBy = ''
      this.payrollNotes = ''
      this.confirmedApproval = false
    }
  },
  watch: {
    selectedEmployees(newVal) {
      if (newVal.length === this.filteredEmployees.length && this.filteredEmployees.length > 0) {
        this.selectAll = true
      } else {
        this.selectAll = false
      }
    }
  },
  mounted() {
    // Set first department as active
    if (this.departments.length > 0) {
      this.activeDepartment = this.departments[0]
    }
  }
}
</script>

<style>
/* Custom styles */
.btn-primary {
  background-color: #2563eb;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary:hover:not(:disabled) {
  background-color: #1d4ed8;
}

.btn-primary:focus {
  outline: none;
  ring-width: 2px;
  ring-color: #3b82f6;
}

.btn-secondary {
  background-color: white;
  color: #374151;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  font-weight: 500;
  border: 1px solid #d1d5db;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-secondary:hover {
  background-color: #f9fafb;
}

.btn-secondary:focus {
  outline: none;
  ring-width: 2px;
  ring-color: #9ca3af;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  table {
    font-size: 0.875rem;
  }
  
  th, td {
    padding: 0.5rem;
  }
  
  .btn-primary, .btn-secondary {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
}

/* Modal animation */
.modal-enter-active {
  animation: modal-fade 0.3s;
}

@keyframes modal-fade {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
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

/* Smooth transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Table row hover effect */
tbody tr {
  transition: background-color 0.15s ease-in-out;
}

/* Form input focus states */
input:focus,
select:focus,
textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Department color coding */
.department-finance {
  border-left-color: #3b82f6;
  border-left-width: 4px;
}

.department-it {
  border-left-color: #8b5cf6;
  border-left-width: 4px;
}

.department-hr {
  border-left-color: #ec4899;
  border-left-width: 4px;
}

.department-sales {
  border-left-color: #f97316;
  border-left-width: 4px;
}

.department-operations {
  border-left-color: #10b981;
  border-left-width: 4px;
}
</style>