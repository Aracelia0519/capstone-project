<template>
  <div class="departments p-4 md:p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Departments</h1>
        <p class="text-gray-600">Group employees logically by organizational units</p>
      </div>
      <Button @click="showAddDeptModal = true" class="mt-4 md:mt-0 bg-blue-600 hover:bg-blue-700 text-white">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Department
      </Button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <Card v-for="dept in departments" :key="dept.id" 
           class="border-l-4 shadow-md" :class="getDeptBorderColor(dept.name)">
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <div class="flex items-center mb-2">
                <div class="p-2 rounded-lg mr-3" :class="getDeptBgColor(dept.name)">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800">{{ dept.name }}</h3>
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.832.055-1.68.113-2.5.113-4.97 0-9-2.239-9-5s4.03-5 9-5c1.72 0 3.32.404 4.786 1.09" />
                </svg>
                <span>{{ dept.employeeCount }} employees</span>
              </div>
            </div>
            <div class="flex space-x-1">
              <Button variant="ghost" size="icon" @click="editDepartment(dept)" class="h-8 w-8 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </Button>
              <Button variant="ghost" size="icon" @click="deleteDepartment(dept.id)" class="h-8 w-8 text-gray-400 hover:text-red-600 hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </Button>
            </div>
          </div>
          
          <div class="mb-4">
            <p class="text-sm text-gray-600">{{ dept.description || 'Department for organizational grouping' }}</p>
          </div>
          
          <Separator class="my-4" />

          <div>
            <div class="flex items-center justify-between mb-2">
              <h4 class="text-sm font-medium text-gray-700">Department Head</h4>
              <Button variant="link" size="sm" @click="assignHead(dept)" class="h-auto p-0 text-xs text-blue-600 hover:text-blue-800">
                Assign
              </Button>
            </div>
            <div v-if="dept.head" class="flex items-center">
              <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                <span class="text-blue-600 font-medium text-xs">{{ getInitials(dept.head.name) }}</span>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-800">{{ dept.head.name }}</p>
                <p class="text-xs text-gray-500">{{ dept.head.position }}</p>
              </div>
            </div>
            <div v-else class="text-sm text-gray-500 italic">
              No head assigned
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="mt-8 shadow-md">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
        <CardTitle class="text-lg font-semibold text-gray-800">Employee Distribution by Department</CardTitle>
        <Button variant="link" @click="assignEmployees" class="text-blue-600 hover:text-blue-800 font-medium">
          Bulk Assign
        </Button>
      </CardHeader>
      <CardContent class="p-0">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="w-[200px]">Department</TableHead>
              <TableHead>Total Employees</TableHead>
              <TableHead>Active</TableHead>
              <TableHead>Inactive</TableHead>
              <TableHead class="w-[30%]">Percentage</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50">
              <TableCell>
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center mr-3" :class="getDeptBgColor(dept.name)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ dept.name }}</span>
                </div>
              </TableCell>
              <TableCell class="font-medium">{{ dept.employeeCount }}</TableCell>
              <TableCell class="text-green-600">{{ dept.activeCount || Math.floor(dept.employeeCount * 0.9) }}</TableCell>
              <TableCell class="text-gray-600">{{ dept.inactiveCount || Math.floor(dept.employeeCount * 0.1) }}</TableCell>
              <TableCell>
                <div class="flex items-center">
                  <Progress :model-value="(dept.employeeCount / totalEmployees * 100)" class="h-2 mr-3 bg-gray-200 [&>div]:bg-blue-600" />
                  <span class="text-sm text-gray-600 w-10">{{ Math.round((dept.employeeCount / totalEmployees * 100)) }}%</span>
                </div>
              </TableCell>
              <TableCell>
                <Button variant="link" @click="viewDepartmentEmployees(dept)" class="text-blue-600 hover:text-blue-800 p-0 h-auto">
                  View Employees
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>

    <Dialog v-model:open="showAddDeptModal">
      <DialogContent class="sm:max-w-lg">
        <DialogHeader>
          <DialogTitle>{{ editingDept ? 'Edit Department' : 'Add New Department' }}</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="saveDepartment" class="space-y-4 py-4">
          <div class="space-y-2">
            <Label>Department Name</Label>
            <Input v-model="deptForm.name" required placeholder="e.g., Administration, Finance" />
          </div>
          
          <div class="space-y-2">
            <Label>Description (Optional)</Label>
            <Textarea v-model="deptForm.description" rows="3" placeholder="Brief description of department functions..." />
          </div>
          
          <div class="space-y-2">
            <Label>Department Code</Label>
            <Input v-model="deptForm.code" placeholder="e.g., ADM, FIN, HR" />
          </div>
          
          <div class="space-y-2">
            <Label>Color Theme</Label>
            <div class="flex space-x-2">
              <button type="button" v-for="color in colorThemes" :key="color.name" 
                      @click="deptForm.color = color.name"
                      class="w-8 h-8 rounded-full border-2 transition-all focus:outline-none focus:ring-2 focus:ring-offset-2" 
                      :class="[color.bg, deptForm.color === color.name ? 'border-gray-800' : 'border-transparent']"
                      :title="color.name">
              </button>
            </div>
          </div>
          
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeModal">Cancel</Button>
            <Button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white">
              {{ editingDept ? 'Update Department' : 'Save Department' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Progress } from '@/components/ui/progress'
import { Separator } from '@/components/ui/separator'

const showAddDeptModal = ref(false)
const editingDept = ref(null)

const departments = ref([
  { id: 1, name: 'Administration', code: 'ADM', employeeCount: 24, color: 'blue', description: 'Administrative and support functions', head: { name: 'Emma Davis', position: 'Admin Manager' } },
  { id: 2, name: 'Human Resources', code: 'HR', employeeCount: 18, color: 'green', description: 'Employee management and development', head: { name: 'John Smith', position: 'HR Manager' } },
  { id: 3, name: 'Finance', code: 'FIN', employeeCount: 32, color: 'purple', description: 'Financial management and accounting', head: { name: 'Sarah Johnson', position: 'Finance Director' } },
  { id: 4, name: 'Logistics', code: 'LOG', employeeCount: 45, color: 'yellow', description: 'Supply chain and inventory management', head: { name: 'Mike Wilson', position: 'Logistics Supervisor' } },
  { id: 5, name: 'Operations', code: 'OPS', employeeCount: 37, color: 'red', description: 'Daily business operations', head: { name: 'David Brown', position: 'Operations Manager' } },
])

const deptForm = ref({
  name: '',
  description: '',
  code: '',
  color: 'blue'
})

const colorThemes = [
  { name: 'blue', bg: 'bg-blue-500' },
  { name: 'green', bg: 'bg-green-500' },
  { name: 'purple', bg: 'bg-purple-500' },
  { name: 'yellow', bg: 'bg-yellow-500' },
  { name: 'red', bg: 'bg-red-500' },
  { name: 'indigo', bg: 'bg-indigo-500' },
]

const totalEmployees = computed(() => {
  return departments.value.reduce((sum, dept) => sum + dept.employeeCount, 0)
})

const getDeptBorderColor = (deptName) => {
  const dept = departments.value.find(d => d.name === deptName)
  if (!dept) return 'border-l-blue-500'
  
  const colors = {
    'blue': 'border-l-blue-500',
    'green': 'border-l-green-500',
    'purple': 'border-l-purple-500',
    'yellow': 'border-l-yellow-500',
    'red': 'border-l-red-500',
    'indigo': 'border-l-indigo-500'
  }
  return colors[dept.color] || 'border-l-blue-500'
}

const getDeptBgColor = (deptName) => {
  const dept = departments.value.find(d => d.name === deptName)
  if (!dept) return 'bg-blue-100 text-blue-600'
  
  const colors = {
    'blue': 'bg-blue-100 text-blue-600',
    'green': 'bg-green-100 text-green-600',
    'purple': 'bg-purple-100 text-purple-600',
    'yellow': 'bg-yellow-100 text-yellow-600',
    'red': 'bg-red-100 text-red-600',
    'indigo': 'bg-indigo-100 text-indigo-600'
  }
  return colors[dept.color] || 'bg-blue-100 text-blue-600'
}

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const editDepartment = (dept) => {
  editingDept.value = dept
  deptForm.value = { ...dept }
  showAddDeptModal.value = true
}

const assignHead = (dept) => {
  console.log('Assign head for department:', dept.name)
}

const viewDepartmentEmployees = (dept) => {
  console.log('View employees in department:', dept.name)
}

const assignEmployees = () => {
  console.log('Bulk assign employees to departments')
}

const deleteDepartment = (deptId) => {
  if (confirm('Are you sure you want to delete this department?')) {
    departments.value = departments.value.filter(d => d.id !== deptId)
  }
}

const saveDepartment = () => {
  if (editingDept.value) {
    const index = departments.value.findIndex(d => d.id === editingDept.value.id)
    if (index !== -1) {
      departments.value[index] = { ...departments.value[index], ...deptForm.value }
    }
  } else {
    const newId = Math.max(...departments.value.map(d => d.id)) + 1
    departments.value.push({
      id: newId,
      ...deptForm.value,
      employeeCount: 0,
      head: null
    })
  }
  closeModal()
}

const closeModal = () => {
  showAddDeptModal.value = false
  editingDept.value = null
  deptForm.value = { name: '', description: '', code: '', color: 'blue' }
}
</script>

<style scoped>
.departments {
  min-height: calc(100vh - 80px);
}
</style>