<template>
  <div class="employees-list p-4 md:p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Employees</h1>
        <p class="text-gray-600">Manage employee records and profiles</p>
      </div>
      <div class="mt-4 md:mt-0 flex space-x-3">
        <button @click="showAddModal = true" class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add Employee
        </button>
        <button @click="exportToCSV" class="flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export
        </button>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-xl shadow-md p-4 mb-6">
      <div class="flex flex-col md:flex-row md:items-center space-y-4 md:space-y-0 md:space-x-4">
        <div class="flex-1">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input v-model="searchQuery" type="text" placeholder="Search employees..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
          </div>
        </div>
        <div class="flex space-x-3">
          <select v-model="departmentFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Departments</option>
            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
          </select>
          <select v-model="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Status</option>
            <option v-for="status in statusOptions" :key="status" :value="status">{{ status }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Employee Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px 6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employee in filteredEmployees" :key="employee.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-medium text-gray-900">{{ employee.id }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-blue-600 font-medium">{{ getInitials(employee.name) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ employee.name }}</div>
                    <div class="text-sm text-gray-500">{{ employee.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ employee.role }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 text-xs rounded-full" :class="getDeptColor(employee.department)">
                  {{ employee.department }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-3 py-1 text-xs rounded-full font-medium" :class="getStatusClass(employee.status)">
                  {{ employee.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-3">
                  <button @click="viewEmployee(employee.id)" class="text-blue-600 hover:text-blue-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button @click="editEmployee(employee.id)" class="text-yellow-600 hover:text-yellow-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="uploadDocument(employee.id)" class="text-green-600 hover:text-green-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="bg-white px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="mb-4 md:mb-0">
            <p class="text-sm text-gray-700">
              Showing <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to 
              <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredEmployees.length) }}</span> of 
              <span class="font-medium">{{ filteredEmployees.length }}</span> results
            </p>
          </div>
          <div class="flex items-center space-x-2">
            <button @click="prevPage" :disabled="currentPage === 1" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Previous
            </button>
            <span class="text-sm text-gray-700">Page {{ currentPage }} of {{ totalPages }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages" class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Employee Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-800">Add New Employee</h3>
            <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveEmployee" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                <input v-model="newEmployee.firstName" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                <input v-model="newEmployee.lastName" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="newEmployee.email" type="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input v-model="newEmployee.phone" type="tel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select v-model="newEmployee.role" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Select Role</option>
                  <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select v-model="newEmployee.department" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Select Department</option>
                  <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employment Status</label>
                <select v-model="newEmployee.status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="Active">Active</option>
                  <option value="Probationary">Probationary</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hire Date</label>
                <input v-model="newEmployee.hireDate" type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              </div>
            </div>
            
            <div class="pt-4 border-t border-gray-200 flex justify-end space-x-3">
              <button type="button" @click="showAddModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
              </button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Save Employee
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const searchQuery = ref('')
const departmentFilter = ref('')
const statusFilter = ref('')
const currentPage = ref(1)
const itemsPerPage = 10
const showAddModal = ref(false)

const departments = ['Administration', 'Human Resources', 'Finance', 'Logistics', 'Operations']
const roles = ['Admin', 'HR Staff', 'Finance Officer', 'Logistics Staff', 'Manager', 'Supervisor']
const statusOptions = ['Active', 'Inactive', 'Probationary', 'Resigned', 'Terminated']

const employees = ref([
  { id: 'EMP-001', name: 'John Smith', email: 'john.smith@company.com', phone: '+1 234 567 8901', role: 'HR Staff', department: 'Human Resources', status: 'Active', hireDate: '2022-01-15' },
  { id: 'EMP-002', name: 'Sarah Johnson', email: 'sarah.j@company.com', phone: '+1 234 567 8902', role: 'Finance Officer', department: 'Finance', status: 'Active', hireDate: '2021-03-22' },
  { id: 'EMP-003', name: 'Mike Wilson', email: 'mike.w@company.com', phone: '+1 234 567 8903', role: 'Logistics Staff', department: 'Logistics', status: 'Probationary', hireDate: '2024-01-05' },
  { id: 'EMP-004', name: 'Emma Davis', email: 'emma.d@company.com', phone: '+1 234 567 8904', role: 'Admin', department: 'Administration', status: 'Active', hireDate: '2020-11-30' },
  { id: 'EMP-005', name: 'David Brown', email: 'david.b@company.com', phone: '+1 234 567 8905', role: 'Manager', department: 'Operations', status: 'Active', hireDate: '2019-08-14' },
  { id: 'EMP-006', name: 'Lisa Taylor', email: 'lisa.t@company.com', phone: '+1 234 567 8906', role: 'Supervisor', department: 'Operations', status: 'Active', hireDate: '2021-05-18' },
  { id: 'EMP-007', name: 'Robert Miller', email: 'robert.m@company.com', phone: '+1 234 567 8907', role: 'HR Staff', department: 'Human Resources', status: 'Inactive', hireDate: '2022-07-09' },
  { id: 'EMP-008', name: 'Jennifer Lee', email: 'jennifer.l@company.com', phone: '+1 234 567 8908', role: 'Finance Officer', department: 'Finance', status: 'Active', hireDate: '2023-02-28' },
  { id: 'EMP-009', name: 'Thomas Clark', email: 'thomas.c@company.com', phone: '+1 234 567 8909', role: 'Logistics Staff', department: 'Logistics', status: 'Active', hireDate: '2022-09-11' },
  { id: 'EMP-010', name: 'Amanda White', email: 'amanda.w@company.com', phone: '+1 234 567 8910', role: 'Admin', department: 'Administration', status: 'Resigned', hireDate: '2021-12-01' },
])

const newEmployee = ref({
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  role: '',
  department: '',
  status: 'Active',
  hireDate: new Date().toISOString().split('T')[0]
})

const filteredEmployees = computed(() => {
  let filtered = employees.value
  
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(emp => 
      emp.name.toLowerCase().includes(query) ||
      emp.email.toLowerCase().includes(query) ||
      emp.id.toLowerCase().includes(query)
    )
  }
  
  if (departmentFilter.value) {
    filtered = filtered.filter(emp => emp.department === departmentFilter.value)
  }
  
  if (statusFilter.value) {
    filtered = filtered.filter(emp => emp.status === statusFilter.value)
  }
  
  return filtered
})

const totalPages = computed(() => Math.ceil(filteredEmployees.value.length / itemsPerPage))

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const getDeptColor = (dept) => {
  const colors = {
    'Administration': 'bg-blue-100 text-blue-800',
    'Human Resources': 'bg-green-100 text-green-800',
    'Finance': 'bg-purple-100 text-purple-800',
    'Logistics': 'bg-yellow-100 text-yellow-800',
    'Operations': 'bg-red-100 text-red-800'
  }
  return colors[dept] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    'Active': 'bg-green-100 text-green-800',
    'Inactive': 'bg-gray-100 text-gray-800',
    'Probationary': 'bg-blue-100 text-blue-800',
    'Resigned': 'bg-yellow-100 text-yellow-800',
    'Terminated': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const viewEmployee = (id) => {
  router.push(`/hr/employee/${id}`)
}

const editEmployee = (id) => {
  // In a real app, you would open an edit modal or navigate to edit page
  console.log('Edit employee:', id)
}

const uploadDocument = (id) => {
  console.log('Upload document for employee:', id)
}

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++
}

const saveEmployee = () => {
  const newId = `EMP-${String(employees.value.length + 1).padStart(3, '0')}`
  const newEmp = {
    id: newId,
    name: `${newEmployee.value.firstName} ${newEmployee.value.lastName}`,
    email: newEmployee.value.email,
    phone: newEmployee.value.phone,
    role: newEmployee.value.role,
    department: newEmployee.value.department,
    status: newEmployee.value.status,
    hireDate: newEmployee.value.hireDate
  }
  
  employees.value.unshift(newEmp)
  showAddModal.value = false
  
  // Reset form
  Object.keys(newEmployee.value).forEach(key => {
    if (key === 'status') {
      newEmployee.value[key] = 'Active'
    } else if (key === 'hireDate') {
      newEmployee.value[key] = new Date().toISOString().split('T')[0]
    } else {
      newEmployee.value[key] = ''
    }
  })
}

const exportToCSV = () => {
  console.log('Exporting to CSV...')
  // CSV export logic would go here
}

onMounted(() => {
  console.log('Employees list loaded')
})
</script>

<style scoped>
.employees-list {
  min-height: calc(100vh - 80px);
}
</style>