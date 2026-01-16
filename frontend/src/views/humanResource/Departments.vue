<template>
  <div class="departments p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Departments</h1>
        <p class="text-gray-600">Group employees logically by organizational units</p>
      </div>
      <button @click="showAddDeptModal = true" class="mt-4 md:mt-0 flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Department
      </button>
    </div>

    <!-- Departments Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div v-for="dept in departments" :key="dept.id" 
           class="bg-white rounded-xl shadow-md p-6 border-l-4" :class="getDeptBorderColor(dept.name)">
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
          <div class="flex space-x-2">
            <button @click="editDepartment(dept)" class="p-1 text-gray-400 hover:text-yellow-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click="deleteDepartment(dept.id)" class="p-1 text-gray-400 hover:text-red-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600">{{ dept.description || 'Department for organizational grouping' }}</p>
        </div>
        
        <div class="pt-4 border-t border-gray-100">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-gray-700">Department Head</h4>
            <button @click="assignHead(dept)" class="text-xs text-blue-600 hover:text-blue-800">
              Assign
            </button>
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
      </div>
    </div>

    <!-- Employee Distribution -->
    <div class="mt-8 bg-white rounded-xl shadow-md p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Employee Distribution by Department</h2>
        <button @click="assignEmployees" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
          Bulk Assign
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Employees</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inactive</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center mr-3" :class="getDeptBgColor(dept.name)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ dept.name }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900">{{ dept.employeeCount }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-green-600 font-medium">{{ dept.activeCount || Math.floor(dept.employeeCount * 0.9) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-600">{{ dept.inactiveCount || Math.floor(dept.employeeCount * 0.1) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                    <div class="h-2 rounded-full bg-blue-600" :style="{ width: (dept.employeeCount / totalEmployees * 100) + '%' }"></div>
                  </div>
                  <span class="text-sm text-gray-600">{{ Math.round((dept.employeeCount / totalEmployees * 100)) }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button @click="viewDepartmentEmployees(dept)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                  View Employees
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add/Edit Department Modal -->
    <div v-if="showAddDeptModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">{{ editingDept ? 'Edit Department' : 'Add New Department' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveDepartment" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department Name</label>
              <input v-model="deptForm.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., Administration, Finance">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
              <textarea v-model="deptForm.description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Brief description of department functions..."></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department Code</label>
              <input v-model="deptForm.code" type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., ADM, FIN, HR">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Color Theme</label>
              <div class="flex space-x-2">
                <button type="button" v-for="color in colorThemes" :key="color.name" 
                        @click="deptForm.color = color.name"
                        class="w-8 h-8 rounded-full border-2" 
                        :class="[color.bg, deptForm.color === color.name ? 'border-gray-800' : 'border-transparent']"
                        :title="color.name">
                </button>
              </div>
            </div>
            
            <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
              <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                {{ editingDept ? 'Update Department' : 'Save Department' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Note Section -->
  
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

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
  // In a real app, you would open a modal to select department head
}

const viewDepartmentEmployees = (dept) => {
  console.log('View employees in department:', dept.name)
  // In a real app, you would navigate to filtered employee list
}

const assignEmployees = () => {
  console.log('Bulk assign employees to departments')
  // In a real app, you would open a bulk assignment modal
}

const deleteDepartment = (deptId) => {
  if (confirm('Are you sure you want to delete this department?')) {
    departments.value = departments.value.filter(d => d.id !== deptId)
  }
}

const saveDepartment = () => {
  if (editingDept.value) {
    // Update existing department
    const index = departments.value.findIndex(d => d.id === editingDept.value.id)
    if (index !== -1) {
      departments.value[index] = { ...departments.value[index], ...deptForm.value }
    }
  } else {
    // Add new department
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