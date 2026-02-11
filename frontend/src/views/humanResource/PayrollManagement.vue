<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="px-6 py-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Payroll Management</h1>
            <p class="text-gray-600">Process employee payroll by department</p>
          </div>
          <div class="flex items-center space-x-4">
            <Button variant="outline" @click="downloadPayroll">
              Export
            </Button>
            <Button @click="processPayroll" class="bg-blue-600 hover:bg-blue-700">
              Process Payroll
            </Button>
          </div>
        </div>
      </div>
    </header>

    <main class="p-4 md:p-6">
      <div class="mb-6 md:mb-8">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <div v-for="(step, index) in steps" :key="step.id" class="flex-1 flex items-center w-full md:w-auto">
             <div class="flex flex-col items-center">
              <div :class="['w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300', step.id < currentStep ? 'bg-blue-600 border-blue-600 text-white' : step.id === currentStep ? 'border-blue-600 text-blue-600 bg-white' : 'border-gray-300 text-gray-400 bg-white']">
                 <span class="font-bold">{{ step.id }}</span>
              </div>
              <span :class="['mt-2 text-sm font-medium', step.id <= currentStep ? 'text-blue-600' : 'text-gray-500']">{{ step.title }}</span>
            </div>
            <div v-if="index < steps.length - 1" :class="['hidden md:block flex-1 h-1 mx-4', step.id < currentStep ? 'bg-blue-600' : 'bg-gray-300']"></div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4 md:p-6">
        
        <div v-if="currentStep === 1" class="space-y-6">
          <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold">Select Employees</h2>
              <div class="flex gap-2">
                 <Input type="date" v-model="periodStart" class="w-auto" />
                 <span class="self-center">to</span>
                 <Input type="date" v-model="periodEnd" class="w-auto" />
              </div>
          </div>

          <div v-if="loading" class="text-center py-10">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
              <p class="mt-4 text-gray-500">Calculating deductions (Absent, Late, Tax) and loading employees...</p>
          </div>

          <div v-else>
              <div class="border-b border-gray-200 mb-4">
                <nav class="flex space-x-8 overflow-x-auto">
                  <button v-for="dept in departments" :key="dept"
                          @click="activeDepartment = dept"
                          :class="['py-3 px-1 text-sm font-medium whitespace-nowrap border-b-2', activeDepartment === dept ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700']">
                    {{ dept }}
                  </button>
                </nav>
              </div>

              <div class="overflow-x-auto">
                <Table>
                  <TableHeader class="bg-gray-50">
                    <TableRow>
                      <TableHead class="w-12">
                         <Checkbox 
                           :model-value="isDepartmentSelected" 
                           @update:model-value="toggleSelectDepartment" 
                         />
                      </TableHead>
                      <TableHead>Employee</TableHead>
                      <TableHead>Gross</TableHead>
                      <TableHead>Days Worked</TableHead> 
                      <TableHead>Absent</TableHead>
                      <TableHead>Late</TableHead>
                      <TableHead>Net Pay (Est.)</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="employee in getEmployeesByDepartment(activeDepartment)" :key="employee.user_id">
                      <TableCell>
                        <Checkbox 
                          :model-value="isEmployeeSelected(employee.user_id)" 
                          @update:model-value="(val) => handleRowSelect(val, employee)" 
                        />
                      </TableCell>
                      <TableCell>
                        <div class="font-medium">{{ employee.name }}</div>
                        <div class="text-xs text-gray-500">{{ employee.position }}</div>
                      </TableCell>
                      <TableCell>{{ formatCurrency(employee.gross_pay) }}</TableCell>
                      
                      <TableCell>
                         <div v-if="employee.attendance_info" class="text-sm">
                            <span class="font-medium">{{ employee.attendance_info.days_present }}</span> 
                            <span class="text-gray-400">/ {{ employee.attendance_info.expected_days }}</span>
                         </div>
                         <span v-else class="text-gray-400">-</span>
                      </TableCell>

                      <TableCell class="text-red-500">
                        <div v-if="employee.deductions?.absent > 0">
                            -{{ formatCurrency(employee.deductions?.absent) }}
                            <div v-if="employee.attendance_info?.absent_days > 0" class="text-xs text-gray-400">
                                ({{ employee.attendance_info.absent_days }} days)
                            </div>
                        </div>
                        <span v-else class="text-gray-300">-</span>
                      </TableCell>

                      <TableCell class="text-red-500">
                        <div v-if="employee.deductions?.late > 0">
                            -{{ formatCurrency(employee.deductions?.late) }}
                             <div v-if="employee.attendance_info?.late_count > 0" class="text-xs text-gray-400">
                                ({{ employee.attendance_info.late_count }}x)
                            </div>
                        </div>
                        <span v-else class="text-gray-300">-</span>
                      </TableCell>

                      <TableCell class="font-bold text-green-600">{{ formatCurrency(employee.net_pay) }}</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
          </div>

          <div class="flex justify-end pt-6 border-t border-gray-200">
             <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">Selected: {{ selectedEmployees.length }}</span>
                <Button @click="nextStep" :disabled="selectedEmployees.length === 0" class="bg-blue-600">
                  Proceed to Review
                </Button>
             </div>
          </div>
        </div>

        <div v-if="currentStep === 2" class="space-y-6">
            <h2 class="text-xl font-semibold mb-4">Review Payroll</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <Card class="p-4 bg-blue-50 border-blue-200">
                    <div class="text-sm text-blue-600">Total Gross</div>
                    <div class="text-2xl font-bold text-blue-800">{{ formatCurrency(totals.gross) }}</div>
                </Card>
                <Card class="p-4 bg-red-50 border-red-200">
                    <div class="text-sm text-red-600">Total Deductions</div>
                    <div class="text-2xl font-bold text-red-800">{{ formatCurrency(totals.deductions) }}</div>
                    <div class="text-xs text-red-500 mt-1">
                        Includes Absent: {{ formatCurrency(totals.absent) }} | Late: {{ formatCurrency(totals.late) }}
                    </div>
                </Card>
                <Card class="p-4 bg-green-50 border-green-200">
                    <div class="text-sm text-green-600">Total Net Pay</div>
                    <div class="text-2xl font-bold text-green-800">{{ formatCurrency(totals.net) }}</div>
                </Card>
            </div>

            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Absent</TableHead>
                        <TableHead>Late</TableHead>
                        <TableHead>Govt/Tax</TableHead>
                        <TableHead>Net Pay</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="emp in selectedEmployees" :key="emp.user_id">
                        <TableCell>
                            <div>{{ emp.name }}</div>
                            <div class="text-xs text-gray-500">{{ emp.position }}</div>
                        </TableCell>
                        <TableCell class="text-red-500">
                            <span v-if="emp.deductions?.absent > 0">-{{ formatCurrency(emp.deductions?.absent) }}</span>
                            <span v-else>-</span>
                        </TableCell>
                        <TableCell class="text-red-500">
                            <span v-if="emp.deductions?.late > 0">-{{ formatCurrency(emp.deductions?.late) }}</span>
                            <span v-else>-</span>
                        </TableCell>
                        <TableCell>
                            -{{ formatCurrency((emp.deductions?.sss || 0) + (emp.deductions?.philhealth || 0) + (emp.deductions?.pagibig || 0) + (emp.deductions?.tax || 0)) }}
                        </TableCell>
                        <TableCell class="font-bold">{{ formatCurrency(emp.net_pay) }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <div class="flex justify-between pt-6 border-t border-gray-200">
                <Button variant="outline" @click="prevStep">Back</Button>
                <Button @click="nextStep" class="bg-blue-600">Confirm & Approve</Button>
            </div>
        </div>

        <div v-if="currentStep === 3" class="space-y-6 text-center">
             <h2 class="text-2xl font-bold">Final Approval</h2>
             <p>You are about to process payroll for {{ selectedEmployees.length }} employees.</p>
             
             <div class="flex justify-center space-x-4 mt-8">
                 <Button variant="outline" @click="prevStep">Back</Button>
                 <Button @click="submitPayroll" :disabled="processing" class="bg-green-600 hover:bg-green-700">
                    <span v-if="processing">Processing...</span>
                    <span v-else>Approve & Pay</span>
                 </Button>
             </div>
        </div>

      </div>
    </main>

    <Dialog v-model:open="showSuccessModal">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Success</DialogTitle>
            </DialogHeader>
            <div class="text-center py-4">
                <p class="text-green-600 text-lg font-medium">Payroll has been processed successfully!</p>
                <p class="text-gray-500 text-sm mt-2">Batch Code: {{ batchCode }}</p>
            </div>
            <DialogFooter>
                <Button @click="resetPayroll">Close</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios' 
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Card } from '@/components/ui/card'

const currentStep = ref(1)
const steps = [
  { id: 1, title: 'Select Employees' },
  { id: 2, title: 'Review Details' },
  { id: 3, title: 'Approve' }
]

const loading = ref(false)
const processing = ref(false)
const showSuccessModal = ref(false)
const batchCode = ref('')

// Dates: Default to start and end of current month
const now = new Date()
const periodStart = ref(new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10))
const periodEnd = ref(new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10))

// Data Containers
const allEmployees = ref([])
const selectedEmployees = ref([]) 
const activeDepartment = ref('')

// Helper function to check if employee is selected
const isEmployeeSelected = (userId) => {
  return selectedEmployees.value.some(e => e.user_id === userId)
}

// Computed
const departments = computed(() => {
    if (!allEmployees.value) return []
    const depts = new Set(allEmployees.value.map(e => e.department))
    return Array.from(depts)
})

const getEmployeesByDepartment = (dept) => {
    return allEmployees.value.filter(e => e.department === dept)
}

// Logic: Are all employees in the ACTIVE department selected?
const isDepartmentSelected = computed(() => {
    const currentEmployees = getEmployeesByDepartment(activeDepartment.value)
    if (currentEmployees.length === 0) return false
    
    // Check if every employee in current view is inside selectedEmployees
    return currentEmployees.every(emp => 
        isEmployeeSelected(emp.user_id)
    )
})

const totals = computed(() => {
    return selectedEmployees.value.reduce((acc, curr) => {
        acc.gross += parseFloat(curr.gross_pay || 0)
        acc.deductions += parseFloat(curr.deductions?.total || 0)
        acc.absent += parseFloat(curr.deductions?.absent || 0)
        acc.late += parseFloat(curr.deductions?.late || 0)
        acc.net += parseFloat(curr.net_pay || 0)
        return acc
    }, { gross: 0, deductions: 0, absent: 0, late: 0, net: 0 })
})

// Methods
const fetchCalculatedPayroll = async () => {
    loading.value = true
    try {
        const response = await api.post('/hr/payroll/calculate', {
            period_start: periodStart.value,
            period_end: periodEnd.value
        })
        allEmployees.value = response.data.payroll_items || []
        
        if (departments.value.length > 0 && !activeDepartment.value) {
            activeDepartment.value = departments.value[0]
        }
    } catch (error) {
        console.error("Error calculating payroll", error)
    } finally {
        loading.value = false
    }
}

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val || 0)
}

const toggleSelectDepartment = (checked) => {
    const currentEmployees = getEmployeesByDepartment(activeDepartment.value)
    const isChecked = !!checked
    
    if (isChecked) {
        // Add anyone not already selected
        currentEmployees.forEach(emp => {
            const alreadySelected = isEmployeeSelected(emp.user_id)
            if (!alreadySelected) {
                selectedEmployees.value = [...selectedEmployees.value, emp]
            }
        })
    } else {
        // Remove anyone in the current view
        const idsToRemove = currentEmployees.map(e => e.user_id)
        selectedEmployees.value = selectedEmployees.value.filter(
            sel => !idsToRemove.includes(sel.user_id)
        )
    }
}

const handleRowSelect = (checked, employee) => {
    const isChecked = !!checked
    
    if (isChecked) {
        const exists = isEmployeeSelected(employee.user_id)
        if (!exists) {
            selectedEmployees.value = [...selectedEmployees.value, employee]
        }
    } else {
        selectedEmployees.value = selectedEmployees.value.filter(e => e.user_id !== employee.user_id)
    }
}

const nextStep = () => currentStep.value++
const prevStep = () => currentStep.value--

const processPayroll = () => {
    selectedEmployees.value = [] // Reset selection
    fetchCalculatedPayroll()
}

const downloadPayroll = () => {
    console.log("Download triggered")
}

const submitPayroll = async () => {
    processing.value = true
    try {
        const response = await api.post('/hr/payroll/process', {
            payroll_items: selectedEmployees.value,
            period_start: periodStart.value,
            period_end: periodEnd.value,
            approved_by: 1 
        })
        batchCode.value = response.data.batch_code
        showSuccessModal.value = true
    } catch (error) {
        console.error("Submission failed", error)
        alert("Failed to process payroll")
    } finally {
        processing.value = false
    }
}

const resetPayroll = () => {
    showSuccessModal.value = false
    currentStep.value = 1
    selectedEmployees.value = []
    allEmployees.value = []
    fetchCalculatedPayroll()
}

// Initial fetch on mount
onMounted(() => {
    fetchCalculatedPayroll()
})
</script>