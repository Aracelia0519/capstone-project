<template>
  <div class="payroll-employee">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Salary & Compensation</h1>
      <p class="text-gray-500 mt-1">View your payslips and earnings breakdown</p>
    </div>

    <!-- Current Period Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <div class="bg-gradient-to-r from-indigo-50 to-purple-100 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Current Period</h3>
            <p class="text-2xl font-bold text-gray-800 mt-2">Nov 16-30, 2023</p>
          </div>
          <div class="p-3 bg-white rounded-lg shadow-sm">
            <i class="fas fa-calendar-week text-indigo-600 text-xl"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-green-50 to-emerald-100 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Net Pay</h3>
            <p class="text-2xl font-bold text-gray-800 mt-2">₱45,750.00</p>
          </div>
          <div class="p-3 bg-white rounded-lg shadow-sm">
            <i class="fas fa-wallet text-green-600 text-xl"></i>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-r from-blue-50 to-cyan-100 rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">YTD Earnings</h3>
            <p class="text-2xl font-bold text-gray-800 mt-2">₱549,000.00</p>
          </div>
          <div class="p-3 bg-white rounded-lg shadow-sm">
            <i class="fas fa-chart-line text-blue-600 text-xl"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Payslip List -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Recent Payslips</h3>
            <button class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
              <i class="fas fa-download mr-1"></i>
              Export All
            </button>
          </div>
          
          <div class="space-y-4">
            <div v-for="payslip in payslips" :key="payslip.id" 
                 class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors">
              <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div class="flex items-center">
                  <div class="p-3 bg-indigo-50 rounded-lg mr-4">
                    <i class="fas fa-file-invoice-dollar text-indigo-600 text-xl"></i>
                  </div>
                  <div>
                    <p class="font-medium text-gray-800">{{ payslip.period }}</p>
                    <p class="text-sm text-gray-600">Issued: {{ payslip.issuedDate }}</p>
                  </div>
                </div>
                <div class="mt-3 md:mt-0 flex items-center space-x-4">
                  <span class="text-lg font-bold text-gray-800">₱{{ payslip.amount }}</span>
                  <div class="flex space-x-2">
                    <button class="p-2 text-gray-500 hover:text-indigo-600 transition-colors">
                      <i class="fas fa-eye"></i>
                    </button>
                    <button class="p-2 text-gray-500 hover:text-indigo-600 transition-colors">
                      <i class="fas fa-download"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Salary Breakdown -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-6">Salary Breakdown (Current Period)</h3>
          <div class="space-y-4">
            <div v-for="item in salaryBreakdown" :key="item.category" 
                 class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
              <div class="flex items-center">
                <div :class="[
                  'w-3 h-3 rounded-full mr-3',
                  item.type === 'earning' ? 'bg-green-500' : 'bg-red-500'
                ]"></div>
                <div>
                  <p class="font-medium text-gray-800">{{ item.category }}</p>
                  <p class="text-sm text-gray-500">{{ item.description }}</p>
                </div>
              </div>
              <div>
                <span :class="[
                  'font-semibold',
                  item.type === 'earning' ? 'text-green-600' : 'text-red-600'
                ]">
                  {{ item.type === 'earning' ? '+' : '-' }}₱{{ item.amount }}
                </span>
              </div>
            </div>
          </div>
          
          <!-- Total Row -->
          <div class="border-t border-gray-200 mt-6 pt-6 flex items-center justify-between">
            <p class="text-lg font-bold text-gray-800">Net Pay</p>
            <p class="text-2xl font-bold text-indigo-600">₱45,750.00</p>
          </div>
        </div>
      </div>

      <!-- Right Column: Bonuses & Stats -->
      <div>
        <!-- Bonuses & Incentives -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Bonuses & Incentives</h3>
          <div class="space-y-4">
            <div v-for="bonus in bonuses" :key="bonus.id" 
                 class="border border-gray-200 rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <span class="font-medium text-gray-800">{{ bonus.type }}</span>
                <span class="text-green-600 font-bold">+₱{{ bonus.amount }}</span>
              </div>
              <p class="text-sm text-gray-600">{{ bonus.description }}</p>
              <p class="text-xs text-gray-500 mt-2">{{ bonus.date }}</p>
            </div>
          </div>
        </div>

        <!-- Tax Information -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Tax & Deductions Summary</h3>
          <div class="space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Income Tax</span>
              <span class="font-medium text-gray-800">₱6,850.00</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">SSS Contribution</span>
              <span class="font-medium text-gray-800">₱1,125.00</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">PhilHealth</span>
              <span class="font-medium text-gray-800">₱800.00</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600">Pag-IBIG</span>
              <span class="font-medium text-gray-800">₱100.00</span>
            </div>
            <div class="border-t border-gray-200 pt-3 mt-3">
              <div class="flex items-center justify-between">
                <span class="font-bold text-gray-800">Total Deductions</span>
                <span class="font-bold text-red-600">₱8,875.00</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const payslips = ref([
  { id: 1, period: 'November 16-30, 2023', issuedDate: 'Dec 5, 2023', amount: '45,750.00' },
  { id: 2, period: 'November 1-15, 2023', issuedDate: 'Nov 20, 2023', amount: '45,000.00' },
  { id: 3, period: 'October 16-31, 2023', issuedDate: 'Nov 5, 2023', amount: '44,500.00' },
  { id: 4, period: 'October 1-15, 2023', issuedDate: 'Oct 20, 2023', amount: '44,000.00' },
])

const salaryBreakdown = ref([
  { category: 'Basic Salary', type: 'earning', amount: '40,000.00', description: 'Monthly base pay' },
  { category: 'Overtime Pay', type: 'earning', amount: '5,750.00', description: '15.5 hours @ ₱370.97' },
  { category: 'Performance Bonus', type: 'earning', amount: '3,500.00', description: 'Q4 2023 bonus' },
  { category: 'Income Tax', type: 'deduction', amount: '6,850.00', description: 'Withholding tax' },
  { category: 'SSS Contribution', type: 'deduction', amount: '1,125.00', description: 'Social Security' },
  { category: 'PhilHealth', type: 'deduction', amount: '800.00', description: 'Health insurance' },
  { category: 'Pag-IBIG', type: 'deduction', amount: '100.00', description: 'Housing fund' },
])

const bonuses = ref([
  { id: 1, type: 'Performance Bonus', amount: '3,500.00', description: 'Exceeded Q3 targets', date: 'Nov 30, 2023' },
  { id: 2, type: 'Attendance Bonus', amount: '1,000.00', description: 'Perfect attendance for Oct 2023', date: 'Nov 15, 2023' },
  { id: 3, type: 'Project Incentive', amount: '5,000.00', description: 'Project Alpha completion', date: 'Oct 30, 2023' },
])
</script>

<style scoped>
.payroll-employee {
  max-width: 1400px;
  margin: 0 auto;
}
</style>