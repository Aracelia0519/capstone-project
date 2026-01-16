<template>
  <div class="positions-roles p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Positions & Roles</h1>
        <p class="text-gray-600">Define job roles and responsibilities within the organization</p>
      </div>
      <button @click="showAddPositionModal = true" class="mt-4 md:mt-0 flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Position
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ positions.length }}</h3>
            <p class="text-gray-600 text-sm">Total Positions</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ activeEmployeesCount }}</h3>
            <p class="text-gray-600 text-sm">Employees with Assigned Roles</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ departments.length }}</h3>
            <p class="text-gray-600 text-sm">Departments with Roles</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Positions Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <div v-for="position in positions" :key="position.id" class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:border-blue-200 transition-colors">
        <div class="flex items-start justify-between mb-4">
          <div>
            <div class="flex items-center mb-2">
              <div class="p-2 rounded-lg mr-3" :class="getPositionColor(position.department)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-gray-800">{{ position.title }}</h3>
            </div>
            <span class="px-3 py-1 text-xs rounded-full" :class="getDeptBadgeColor(position.department)">
              {{ position.department }}
            </span>
          </div>
          <div class="flex space-x-2">
            <button @click="editPosition(position)" class="p-1 text-gray-400 hover:text-yellow-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button @click="deletePosition(position.id)" class="p-1 text-gray-400 hover:text-red-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-3">{{ position.description }}</p>
          <div class="flex items-center text-sm text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>{{ position.employeeCount }} employees assigned</span>
          </div>
        </div>
        
        <div class="pt-4 border-t border-gray-100">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Assigned Employees</h4>
          <div class="flex flex-wrap gap-2">
            <span v-for="employee in getPositionEmployees(position.id)" :key="employee.id" 
                  class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-700">
              {{ employee.name.split(' ')[0] }}
            </span>
            <button @click="assignEmployees(position)" class="px-3 py-1 text-xs rounded-full border border-dashed border-gray-300 text-gray-500 hover:border-blue-300 hover:text-blue-600">
              + Assign
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Position Modal -->
    <div v-if="showAddPositionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">{{ editingPosition ? 'Edit Position' : 'Add New Position' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="savePosition" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Position Title</label>
              <input v-model="positionForm.title" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g., HR Staff, Finance Officer">
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
              <select v-model="positionForm.department" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Select Department</option>
                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Role Description</label>
              <textarea v-model="positionForm.description" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Describe the responsibilities and requirements for this position..."></textarea>
            </div>
            
            <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
              <button type="button" @click="closeModal" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                {{ editingPosition ? 'Update Position' : 'Save Position' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const showAddPositionModal = ref(false)
const editingPosition = ref(null)

const departments = ['Administration', 'Human Resources', 'Finance', 'Logistics', 'Operations']

const positions = ref([
  { id: 1, title: 'HR Staff', department: 'Human Resources', description: 'Responsible for employee relations, recruitment, and HR administration tasks.', employeeCount: 3 },
  { id: 2, title: 'Finance Officer', department: 'Finance', description: 'Handles financial transactions, budgeting, and accounting records management.', employeeCount: 4 },
  { id: 3, title: 'Logistics Staff', department: 'Logistics', description: 'Manages inventory, shipping, and supply chain operations.', employeeCount: 6 },
  { id: 4, title: 'Admin Assistant', department: 'Administration', description: 'Provides administrative support, document management, and office coordination.', employeeCount: 2 },
  { id: 5, title: 'Operations Manager', department: 'Operations', description: 'Oversees daily operations, team management, and process optimization.', employeeCount: 3 },
  { id: 6, title: 'IT Support', department: 'Administration', description: 'Provides technical support and maintains computer systems and networks.', employeeCount: 2 },
])

const employees = ref([
  { id: 1, name: 'John Smith', positionId: 1 },
  { id: 2, name: 'Sarah Johnson', positionId: 2 },
  { id: 3, name: 'Mike Wilson', positionId: 3 },
  { id: 4, name: 'Emma Davis', positionId: 4 },
  { id: 5, name: 'David Brown', positionId: 5 },
  { id: 6, name: 'Lisa Taylor', positionId: 5 },
])

const positionForm = ref({
  title: '',
  department: '',
  description: ''
})

const activeEmployeesCount = computed(() => {
  return employees.value.length
})

const getPositionColor = (department) => {
  const colors = {
    'Administration': 'bg-blue-100 text-blue-600',
    'Human Resources': 'bg-green-100 text-green-600',
    'Finance': 'bg-purple-100 text-purple-600',
    'Logistics': 'bg-yellow-100 text-yellow-600',
    'Operations': 'bg-red-100 text-red-600'
  }
  return colors[department] || 'bg-gray-100 text-gray-600'
}

const getDeptBadgeColor = (department) => {
  const colors = {
    'Administration': 'bg-blue-100 text-blue-800',
    'Human Resources': 'bg-green-100 text-green-800',
    'Finance': 'bg-purple-100 text-purple-800',
    'Logistics': 'bg-yellow-100 text-yellow-800',
    'Operations': 'bg-red-100 text-red-800'
  }
  return colors[department] || 'bg-gray-100 text-gray-800'
}

const getPositionEmployees = (positionId) => {
  return employees.value.filter(emp => emp.positionId === positionId).slice(0, 3)
}

const editPosition = (position) => {
  editingPosition.value = position
  positionForm.value = { ...position }
  showAddPositionModal.value = true
}

const assignEmployees = (position) => {
  console.log('Assign employees to position:', position.title)
  // In a real app, you would open an employee assignment modal
}

const deletePosition = (positionId) => {
  if (confirm('Are you sure you want to delete this position?')) {
    positions.value = positions.value.filter(p => p.id !== positionId)
  }
}

const savePosition = () => {
  if (editingPosition.value) {
    // Update existing position
    const index = positions.value.findIndex(p => p.id === editingPosition.value.id)
    if (index !== -1) {
      positions.value[index] = { ...positions.value[index], ...positionForm.value }
    }
  } else {
    // Add new position
    const newId = Math.max(...positions.value.map(p => p.id)) + 1
    positions.value.push({
      id: newId,
      ...positionForm.value,
      employeeCount: 0
    })
  }
  
  closeModal()
}

const closeModal = () => {
  showAddPositionModal.value = false
  editingPosition.value = null
  positionForm.value = { title: '', department: '', description: '' }
}
</script>

<style scoped>
.positions-roles {
  min-height: calc(100vh - 80px);
}
</style>