<template>
  <div class="employment-status p-4 md:p-6">
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Employment Status</h1>
      <p class="text-gray-600">Track employee lifecycle and monitor workforce status</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
      <Card v-for="status in statusTypes" :key="status.id" 
           class="shadow-md" :class="status.borderClass">
        <CardContent class="p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-3 rounded-lg" :class="status.bgClass">
              <div v-html="status.icon" class="w-6 h-6"></div>
            </div>
            <Badge variant="secondary" :class="status.badgeClass">{{ status.count }}</Badge>
          </div>
          <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ status.name }}</h3>
          <p class="text-sm text-gray-600">{{ status.description }}</p>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <div class="lg:col-span-2">
        <Card class="shadow-md">
          <CardHeader>
            <CardTitle>Change Employee Status</CardTitle>
          </CardHeader>
          <CardContent>
            <form @submit.prevent="changeEmployeeStatus" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label>Select Employee</Label>
                  <Select v-model="statusForm.employeeId">
                    <SelectTrigger>
                      <SelectValue placeholder="Select Employee" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="employee in employees" :key="employee.id" :value="String(employee.id)">
                        {{ employee.name }} ({{ employee.id }}) - Current: {{ employee.status }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                
                <div class="space-y-2">
                  <Label>New Status</Label>
                  <Select v-model="statusForm.newStatus">
                    <SelectTrigger>
                      <SelectValue placeholder="Select Status" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="status in availableStatuses" :key="status" :value="status">
                        {{ status }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
              
              <div v-if="selectedEmployee">
                <div class="bg-gray-50 rounded-lg p-4 mb-4 border">
                  <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                      <span class="text-blue-600 font-medium">{{ getInitials(selectedEmployee.name) }}</span>
                    </div>
                    <div>
                      <h4 class="font-medium text-gray-800">{{ selectedEmployee.name }}</h4>
                      <p class="text-sm text-gray-600">{{ selectedEmployee.department }} • {{ selectedEmployee.role }}</p>
                    </div>
                  </div>
                  <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-700 mr-2">Current Status:</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(selectedEmployee.status)">
                      {{ selectedEmployee.status }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="space-y-2">
                <Label>Effective Date</Label>
                <Input v-model="statusForm.effectiveDate" type="date" required />
              </div>
              
              <div class="space-y-2">
                <Label>Notes / Remarks</Label>
                <Textarea v-model="statusForm.notes" rows="3" placeholder="Add notes about the status change..." />
              </div>
              
              <div class="pt-4 border-t border-gray-200">
                <Button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white h-12">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Update Employment Status
                </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
      
      <div>
        <Card class="shadow-md h-full">
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle>Recent Changes</CardTitle>
            <Button variant="link" @click="viewAllHistory" class="text-blue-600 p-0 h-auto">
              View All
            </Button>
          </CardHeader>
          <CardContent class="pt-4">
            <div class="space-y-4">
              <div v-for="record in recentHistory" :key="record.id" class="pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                <div class="flex items-start mb-2">
                  <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-blue-600 text-xs font-medium">{{ getInitials(record.employeeName) }}</span>
                    </div>
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-medium text-gray-800">{{ record.employeeName }}</p>
                    <p class="text-xs text-gray-500">{{ formatDate(record.date) }}</p>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <span class="text-xs px-2 py-1 rounded-full mr-2" :class="getStatusClass(record.oldStatus)">
                      {{ record.oldStatus }}
                    </span>
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                    <span class="text-xs px-2 py-1 rounded-full" :class="getStatusClass(record.newStatus)">
                      {{ record.newStatus }}
                    </span>
                  </div>
                  <Button variant="link" size="sm" @click="viewRecordDetails(record)" class="text-xs text-blue-600 hover:text-blue-800 h-auto p-0">
                    Details
                  </Button>
                </div>
              </div>
              
              <div v-if="recentHistory.length === 0" class="text-center py-4">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-sm text-gray-500">No status changes recorded yet</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <Card class="shadow-md mb-8">
      <CardHeader>
        <CardTitle>Status Distribution & Trends</CardTitle>
      </CardHeader>
      <CardContent>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-4">Monthly Status Changes</h3>
            <div class="space-y-3">
              <div v-for="month in monthlyChanges" :key="month.month" class="flex items-center">
                <span class="text-sm text-gray-600 w-24">{{ month.month }}</span>
                <div class="flex-1">
                  <div class="flex items-center text-xs">
                    <span class="w-20 text-right mr-2 text-green-600">↑ {{ month.hired }}</span>
                    <div class="h-2 bg-green-200 rounded-full flex-1">
                      <div class="h-full bg-green-500 rounded-full" :style="{ width: month.hiredPercentage + '%' }"></div>
                    </div>
                  </div>
                  <div class="flex items-center text-xs mt-1">
                    <span class="w-20 text-right mr-2 text-red-600">↓ {{ month.terminated }}</span>
                    <div class="h-2 bg-red-200 rounded-full flex-1">
                      <div class="h-full bg-red-500 rounded-full" :style="{ width: month.terminatedPercentage + '%' }"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div>
            <h3 class="text-sm font-medium text-gray-700 mb-4">Status Summary</h3>
            <div class="space-y-4">
              <div v-for="status in statusTypes" :key="status.id" class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full mr-3" :class="status.dotClass"></div>
                  <span class="text-sm text-gray-700">{{ status.name }}</span>
                </div>
                <div class="flex items-center">
                  <span class="text-sm font-medium text-gray-800 mr-3">{{ status.count }}</span>
                  <div class="w-24 bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full" :class="status.barClass" :style="{ width: (status.count / totalEmployees * 100) + '%' }"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog v-model:open="showRecordModal">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>Status Change Details</DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedRecord" class="space-y-4 py-4">
          <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center mb-3">
              <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                <span class="text-blue-600 font-medium">{{ getInitials(selectedRecord.employeeName) }}</span>
              </div>
              <div>
                <h4 class="font-medium text-gray-800">{{ selectedRecord.employeeName }}</h4>
                <p class="text-sm text-gray-600">Employee ID: {{ selectedRecord.employeeId }}</p>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-gray-500 mb-1">Changed By</p>
                <p class="text-sm font-medium text-gray-800">{{ selectedRecord.changedBy }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Effective Date</p>
                <p class="text-sm font-medium text-gray-800">{{ formatDate(selectedRecord.date) }}</p>
              </div>
            </div>
          </div>
          
          <div class="flex items-center justify-center my-6">
            <div class="text-center">
              <span class="px-3 py-1 rounded-full text-sm font-medium mr-4" :class="getStatusClass(selectedRecord.oldStatus)">
                {{ selectedRecord.oldStatus }}
              </span>
              <svg class="w-6 h-6 text-gray-400 inline mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
              </svg>
              <span class="px-3 py-1 rounded-full text-sm font-medium ml-4" :class="getStatusClass(selectedRecord.newStatus)">
                {{ selectedRecord.newStatus }}
              </span>
            </div>
          </div>
          
          <div>
            <h4 class="text-sm font-medium text-gray-700 mb-2">Notes</h4>
            <p class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">{{ selectedRecord.notes || 'No additional notes provided.' }}</p>
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" class="w-full" @click="showRecordModal = false">
            Close
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'

const statusForm = ref({
  employeeId: '',
  newStatus: '',
  effectiveDate: new Date().toISOString().split('T')[0],
  notes: ''
})

const showRecordModal = ref(false)
const selectedRecord = ref(null)

// SVG icons as strings (unchanged logic)
const checkIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
const xIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
const clockIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`
const exclamationIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>`
const banIcon = `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>`

const statusTypes = ref([
  { id: 1, name: 'Active', count: 142, description: 'Currently employed and working', 
    icon: checkIcon, bgClass: 'bg-green-100', iconClass: 'text-green-600', 
    borderClass: 'border-l-4 border-l-green-500', badgeClass: 'bg-green-100 text-green-800 hover:bg-green-200',
    dotClass: 'bg-green-500', barClass: 'bg-green-500' },
  { id: 2, name: 'Probationary', count: 8, description: 'Under probation period', 
    icon: clockIcon, bgClass: 'bg-blue-100', iconClass: 'text-blue-600', 
    borderClass: 'border-l-4 border-l-blue-500', badgeClass: 'bg-blue-100 text-blue-800 hover:bg-blue-200',
    dotClass: 'bg-blue-500', barClass: 'bg-blue-500' },
  { id: 3, name: 'Inactive', count: 6, description: 'Not currently active', 
    icon: xIcon, bgClass: 'bg-gray-100', iconClass: 'text-gray-600', 
    borderClass: 'border-l-4 border-l-gray-500', badgeClass: 'bg-gray-100 text-gray-800 hover:bg-gray-200',
    dotClass: 'bg-gray-500', barClass: 'bg-gray-500' },
  { id: 4, name: 'Resigned', count: 4, description: 'Voluntarily left the company', 
    icon: exclamationIcon, bgClass: 'bg-yellow-100', iconClass: 'text-yellow-600', 
    borderClass: 'border-l-4 border-l-yellow-500', badgeClass: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
    dotClass: 'bg-yellow-500', barClass: 'bg-yellow-500' },
  { id: 5, name: 'Terminated', count: 2, description: 'Employment terminated', 
    icon: banIcon, bgClass: 'bg-red-100', iconClass: 'text-red-600', 
    borderClass: 'border-l-4 border-l-red-500', badgeClass: 'bg-red-100 text-red-800 hover:bg-red-200',
    dotClass: 'bg-red-500', barClass: 'bg-red-500' },
])

const employees = ref([
  { id: 1, name: 'John Smith', status: 'Active', department: 'Human Resources', role: 'HR Staff' },
  { id: 2, name: 'Sarah Johnson', status: 'Active', department: 'Finance', role: 'Finance Officer' },
  { id: 3, name: 'Mike Wilson', status: 'Probationary', department: 'Logistics', role: 'Logistics Staff' },
  { id: 4, name: 'Emma Davis', status: 'Active', department: 'Administration', role: 'Admin' },
  { id: 5, name: 'David Brown', status: 'Active', department: 'Operations', role: 'Manager' },
  { id: 6, name: 'Lisa Taylor', status: 'Resigned', department: 'Operations', role: 'Supervisor' },
  { id: 7, name: 'Robert Miller', status: 'Inactive', department: 'Human Resources', role: 'HR Staff' },
  { id: 8, name: 'Jennifer Lee', status: 'Active', department: 'Finance', role: 'Finance Officer' },
])

const statusHistory = ref([
  { id: 1, employeeId: 6, employeeName: 'Lisa Taylor', oldStatus: 'Active', newStatus: 'Resigned', date: '2024-01-15', changedBy: 'John Smith', notes: 'Voluntary resignation with 2 weeks notice.' },
  { id: 2, employeeId: 7, employeeName: 'Robert Miller', oldStatus: 'Active', newStatus: 'Inactive', date: '2024-01-10', changedBy: 'Sarah Johnson', notes: 'Extended leave of absence for personal reasons.' },
  { id: 3, employeeId: 3, employeeName: 'Mike Wilson', oldStatus: 'Active', newStatus: 'Probationary', date: '2024-01-05', changedBy: 'Emma Davis', notes: 'New hire under 3-month probation period.' },
  { id: 4, employeeId: 5, employeeName: 'David Brown', oldStatus: 'Probationary', newStatus: 'Active', date: '2023-12-20', changedBy: 'John Smith', notes: 'Successfully completed probation period.' },
  { id: 5, employeeId: 8, employeeName: 'Jennifer Lee', oldStatus: 'Resigned', newStatus: 'Active', date: '2023-12-01', changedBy: 'Sarah Johnson', notes: 'Returned from resignation, original position reinstated.' },
])

const monthlyChanges = ref([
  { month: 'Jan 2024', hired: 3, terminated: 1, hiredPercentage: 75, terminatedPercentage: 25 },
  { month: 'Dec 2023', hired: 5, terminated: 2, hiredPercentage: 71, terminatedPercentage: 29 },
  { month: 'Nov 2023', hired: 2, terminated: 0, hiredPercentage: 100, terminatedPercentage: 0 },
  { month: 'Oct 2023', hired: 4, terminated: 3, hiredPercentage: 57, terminatedPercentage: 43 },
])

const availableStatuses = ['Active', 'Probationary', 'Inactive', 'Resigned', 'Terminated']

const selectedEmployee = computed(() => {
  return employees.value.find(emp => emp.id === parseInt(statusForm.value.employeeId))
})

const recentHistory = computed(() => {
  return [...statusHistory.value].sort((a, b) => new Date(b.date) - new Date(a.date)).slice(0, 5)
})

const totalEmployees = computed(() => {
  return statusTypes.value.reduce((sum, status) => sum + status.count, 0)
})

const getStatusClass = (status) => {
  const classes = {
    'Active': 'bg-green-100 text-green-800',
    'Probationary': 'bg-blue-100 text-blue-800',
    'Inactive': 'bg-gray-100 text-gray-800',
    'Resigned': 'bg-yellow-100 text-yellow-800',
    'Terminated': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const changeEmployeeStatus = () => {
  if (!statusForm.value.employeeId || !statusForm.value.newStatus) {
    alert('Please select both employee and new status')
    return
  }
  
  const employee = employees.value.find(emp => emp.id === parseInt(statusForm.value.employeeId))
  if (!employee) return
  
  const newRecord = {
    id: statusHistory.value.length + 1,
    employeeId: employee.id,
    employeeName: employee.name,
    oldStatus: employee.status,
    newStatus: statusForm.value.newStatus,
    date: statusForm.value.effectiveDate,
    changedBy: 'Current User', 
    notes: statusForm.value.notes
  }
  
  statusHistory.value.unshift(newRecord)
  employee.status = statusForm.value.newStatus
  
  const oldStatus = statusTypes.value.find(s => s.name === newRecord.oldStatus)
  const newStatus = statusTypes.value.find(s => s.name === newRecord.newStatus)
  
  if (oldStatus) oldStatus.count--
  if (newStatus) newStatus.count++
  
  statusForm.value = {
    employeeId: '',
    newStatus: '',
    effectiveDate: new Date().toISOString().split('T')[0],
    notes: ''
  }
  
  alert('Status updated successfully!')
}

const viewRecordDetails = (record) => {
  selectedRecord.value = record
  showRecordModal.value = true
}

const viewAllHistory = () => {
  console.log('View all status history')
}
</script>

<style scoped>
.employment-status {
  min-height: calc(100vh - 80px);
}
</style>