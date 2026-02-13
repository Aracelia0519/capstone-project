<template>
  <div class="payroll-employee p-4 md:p-6">
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Salary & Compensation</h1>
      <p class="text-gray-500 mt-1">View your payslips and earnings breakdown</p>
    </div>

    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else-if="!currentPayroll && !loading" class="flex flex-col items-center justify-center h-64 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
      <i class="fas fa-file-invoice-dollar text-gray-400 text-4xl mb-4"></i>
      <p class="text-gray-500 font-medium">No paid payroll records found.</p>
    </div>

    <div v-else>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <Card class="bg-gradient-to-r from-indigo-50 to-purple-100 border-0 shadow-lg">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Current Period</h3>
              <p class="text-xl font-bold text-gray-800 mt-2">{{ formatDateRange(currentPayroll.period_start, currentPayroll.period_end) }}</p>
            </div>
            <div class="p-3 bg-white rounded-lg shadow-sm">
              <i class="fas fa-calendar-week text-indigo-600 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-gradient-to-r from-green-50 to-emerald-100 border-0 shadow-lg">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">Net Pay</h3>
              <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatCurrency(currentPayroll.net_pay) }}</p>
            </div>
            <div class="p-3 bg-white rounded-lg shadow-sm">
              <i class="fas fa-wallet text-green-600 text-xl"></i>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-gradient-to-r from-blue-50 to-cyan-100 border-0 shadow-lg">
          <CardContent class="p-6 flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">YTD Earnings</h3>
              <p class="text-2xl font-bold text-gray-800 mt-2">{{ formatCurrency(ytdEarnings) }}</p>
            </div>
            <div class="p-3 bg-white rounded-lg shadow-sm">
              <i class="fas fa-chart-line text-blue-600 text-xl"></i>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
          
          <Card class="shadow-lg border-0">
            <CardContent class="p-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-800">Recent Payslips</h3>
                </div>
              
              <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2">
                <div v-for="payslip in payslips" :key="payslip.id" 
                    @click="selectPayroll(payslip)"
                    :class="['border rounded-lg p-4 cursor-pointer transition-colors', 
                             currentPayroll.id === payslip.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300']">
                  <div class="flex flex-col md:flex-row md:items-center justify-between">
                    <div class="flex items-center">
                      <div class="p-3 bg-white rounded-lg mr-4 border border-gray-100 shadow-sm">
                        <i class="fas fa-file-invoice-dollar text-indigo-600 text-xl"></i>
                      </div>
                      <div>
                        <p class="font-medium text-gray-800">{{ formatDateRange(payslip.period_start, payslip.period_end) }}</p>
                        <p class="text-sm text-gray-600">Paid: {{ formatDate(payslip.paid_at) }}</p>
                        <p class="text-xs text-gray-400">Ref: {{ payslip.payment_reference || 'N/A' }}</p>
                      </div>
                    </div>
                    <div class="mt-3 md:mt-0 flex items-center space-x-4">
                      <span class="text-lg font-bold text-gray-800">{{ formatCurrency(payslip.net_pay) }}</span>
                      <div class="flex space-x-2">
                        <Button variant="ghost" size="icon" class="text-indigo-600 hover:text-indigo-800">
                          <i class="fas fa-eye"></i>
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="shadow-lg border-0">
            <CardContent class="p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-6">Salary Breakdown (Selected Period)</h3>
              <div class="space-y-4">
                
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                  <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-3 bg-green-500"></div>
                    <div>
                      <p class="font-medium text-gray-800">Basic Salary</p>
                      <p class="text-sm text-gray-500">Base Pay</p>
                    </div>
                  </div>
                  <span class="font-semibold text-green-600">+{{ formatCurrency(currentPayroll.basic_salary) }}</span>
                </div>

                <div v-if="parseFloat(currentPayroll.gross_pay) > parseFloat(currentPayroll.basic_salary)" 
                     class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                  <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-3 bg-green-400"></div>
                    <div>
                      <p class="font-medium text-gray-800">Other Earnings</p>
                      <p class="text-sm text-gray-500">Overtime, Allowances, Adjustments</p>
                    </div>
                  </div>
                  <span class="font-semibold text-green-600">
                    +{{ formatCurrency(currentPayroll.gross_pay - currentPayroll.basic_salary) }}
                  </span>
                </div>

                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                  <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-3 bg-red-500"></div>
                    <div>
                      <p class="font-medium text-gray-800">Total Deductions</p>
                      <p class="text-sm text-gray-500">Tax, SSS, PhilHealth, Pag-IBIG</p>
                    </div>
                  </div>
                  <span class="font-semibold text-red-600">-{{ formatCurrency(currentPayroll.total_deductions) }}</span>
                </div>

              </div>
              
              <Separator class="my-6" />
              <div class="flex items-center justify-between">
                <p class="text-lg font-bold text-gray-800">Net Pay</p>
                <p class="text-2xl font-bold text-indigo-600">{{ formatCurrency(currentPayroll.net_pay) }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <div class="space-y-6">
          <Card class="shadow-lg border-0">
            <CardContent class="p-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Deductions Detail</h3>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">SSS Contribution</span>
                  <span class="font-medium text-gray-800">{{ formatCurrency(currentPayroll.sss_contribution) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">PhilHealth</span>
                  <span class="font-medium text-gray-800">{{ formatCurrency(currentPayroll.philhealth_contribution) }}</span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-600">Pag-IBIG</span>
                  <span class="font-medium text-gray-800">{{ formatCurrency(currentPayroll.pagibig_contribution) }}</span>
                </div>
                
                <div v-if="calculateOtherDeductions(currentPayroll) > 0" class="flex items-center justify-between">
                  <span class="text-gray-600">Late/Absences</span>
                  <span class="font-medium text-gray-800">{{ formatCurrency(calculateOtherDeductions(currentPayroll)) }}</span>
                </div>

                <Separator class="my-3" />
                
                <div class="flex items-center justify-between">
                  <span class="font-bold text-gray-800">Total Deductions</span>
                  <span class="font-bold text-red-600">{{ formatCurrency(currentPayroll.total_deductions) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>
          
           <Card class="shadow-lg border-0 bg-indigo-600 text-white">
            <CardContent class="p-6">
              <h3 class="text-lg font-semibold mb-2">Payment Information</h3>
              <div class="space-y-2 text-indigo-100">
                  <div class="flex justify-between">
                      <span>Method:</span>
                      <span class="font-medium text-white">{{ currentPayroll.payment_method || 'N/A' }}</span>
                  </div>
                  <div class="flex justify-between">
                      <span>Reference:</span>
                      <span class="font-medium text-white">{{ currentPayroll.payment_reference || 'N/A' }}</span>
                  </div>
                  <div class="flex justify-between">
                      <span>Date Paid:</span>
                      <span class="font-medium text-white">{{ formatDate(currentPayroll.paid_at) }}</span>
                  </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from '@/utils/axios' // Assuming axios is exported from axios.js as uploaded
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'

const payslips = ref([])
const currentPayroll = ref(null)
const ytdEarnings = ref(0)
const loading = ref(true)

const fetchPayrolls = async () => {
  try {
    loading.value = true
    const response = await axios.get('/employee/payroll')
    
    if (response.data && response.data.payrolls) {
      payslips.value = response.data.payrolls
      ytdEarnings.value = response.data.ytd_earnings || 0
      
      // Select the most recent payroll by default
      if (payslips.value.length > 0) {
        currentPayroll.value = payslips.value[0]
      }
    }
  } catch (error) {
    console.error('Error fetching payrolls:', error)
  } finally {
    loading.value = false
  }
}

const selectPayroll = (payroll) => {
  currentPayroll.value = payroll
}

// Helpers
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2
  }).format(value || 0)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatDateRange = (start, end) => {
  if (!start || !end) return 'N/A'
  const s = new Date(start)
  const e = new Date(end)
  return `${s.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${e.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`
}

// Calculate diff between Total Deductions and Statutory Deductions to find "Other" (Lates/Absences)
const calculateOtherDeductions = (payroll) => {
    if (!payroll) return 0;
    const statTotal = parseFloat(payroll.sss_contribution || 0) + 
                      parseFloat(payroll.philhealth_contribution || 0) + 
                      parseFloat(payroll.pagibig_contribution || 0) + 
                      parseFloat(payroll.withholding_tax || 0);
    const total = parseFloat(payroll.total_deductions || 0);
    return Math.max(0, total - statTotal);
}

onMounted(() => {
  fetchPayrolls()
})
</script>

<style scoped>
.payroll-employee {
  max-width: 1600px;
  margin: 0 auto;
}
</style>