<template>
  <div class="profile-employee">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">My Profile</h1>
      <p class="text-gray-500 mt-1">Manage your personal information</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column: Profile Info -->
      <div>
        <!-- Profile Card -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
          <div class="flex flex-col items-center">
            <div class="w-24 h-24 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4">
              JD
            </div>
            <h2 class="text-xl font-bold text-gray-800">John Doe</h2>
            <p class="text-gray-600 mt-1">Senior Software Engineer</p>
            <p class="text-sm text-gray-500 mt-2">Employee ID: EMP-2023-001</p>
            
            <div class="mt-6 w-full">
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Department</span>
                <span class="font-medium text-gray-800">Engineering</span>
              </div>
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-gray-600">Join Date</span>
                <span class="font-medium text-gray-800">Jan 15, 2021</span>
              </div>
              <div class="flex items-center justify-between py-2">
                <span class="text-gray-600">Employment Type</span>
                <span class="font-medium text-green-600">Regular</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Change Password</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
              <input type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
              <input type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
              <input type="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <button class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors">
              Update Password
            </button>
          </div>
        </div>
      </div>

      <!-- Middle Column: Personal Details -->
      <div class="lg:col-span-2">
        <!-- Tabs -->
        <div class="mb-6">
          <div class="border-b border-gray-200">
            <nav class="flex space-x-8 overflow-x-auto">
              <button v-for="tab in tabs" 
                      :key="tab.id"
                      @click="activeTab = tab.id"
                      :class="[
                        'py-3 px-1 font-medium text-sm border-b-2 transition-colors whitespace-nowrap',
                        activeTab === tab.id 
                          ? 'border-indigo-600 text-indigo-600' 
                          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                      ]">
                <i :class="tab.icon + ' mr-2'"></i>
                {{ tab.label }}
              </button>
            </nav>
          </div>
        </div>

        <!-- Tab Content -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <!-- Personal Information -->
          <div v-if="activeTab === 'personal'">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
              <button class="px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                <i class="fas fa-edit mr-1"></i>
                Edit Information
              </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <div class="p-3 bg-gray-50 rounded-lg text-gray-800">John Michael Doe</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Birthdate</label>
                <div class="p-3 bg-gray-50 rounded-lg text-gray-800">May 15, 1990</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                <div class="p-3 bg-gray-50 rounded-lg text-gray-800">Male</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</label>
                <div class="p-3 bg-gray-50 rounded-lg text-gray-800">Single</div>
              </div>
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <div class="p-3 bg-gray-50 rounded-lg text-gray-800">123 Main Street, Makati City, Metro Manila, Philippines</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                <div class="p-3 bg-gray-50 rounded-lg text-gray-800">+63 912 345 6789</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                <div class="p-3 bg-gray-50 rounded-lg text-gray-800">john.doe@company.com</div>
              </div>
            </div>
          </div>

          <!-- Emergency Contacts -->
          <div v-if="activeTab === 'emergency'">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-800">Emergency Contacts</h3>
              <button class="px-4 py-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                <i class="fas fa-plus mr-1"></i>
                Add Contact
              </button>
            </div>
            
            <div class="space-y-6">
              <div v-for="contact in emergencyContacts" 
                   :key="contact.id"
                   class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <h4 class="font-medium text-gray-800">{{ contact.name }}</h4>
                  <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                    {{ contact.relationship }}
                  </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs text-gray-500 mb-1">Contact Number</label>
                    <div class="text-gray-800">{{ contact.phone }}</div>
                  </div>
                  <div>
                    <label class="block text-xs text-gray-500 mb-1">Email</label>
                    <div class="text-gray-800">{{ contact.email }}</div>
                  </div>
                  <div class="md:col-span-2">
                    <label class="block text-xs text-gray-500 mb-1">Address</label>
                    <div class="text-gray-800">{{ contact.address }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Government Numbers -->
          <div v-if="activeTab === 'government'">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Government Numbers</h3>
            
            <div class="space-y-4">
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-red-50 rounded-lg mr-4">
                    <i class="fas fa-id-card text-red-600"></i>
                  </div>
                  <div>
                    <h4 class="font-medium text-gray-800">SSS Number</h4>
                    <p class="text-sm text-gray-600">Social Security System</p>
                  </div>
                </div>
                <div class="font-mono text-gray-800">34-1234567-8</div>
              </div>
              
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-blue-50 rounded-lg mr-4">
                    <i class="fas fa-heartbeat text-blue-600"></i>
                  </div>
                  <div>
                    <h4 class="font-medium text-gray-800">PhilHealth Number</h4>
                    <p class="text-sm text-gray-600">Philippine Health Insurance</p>
                  </div>
                </div>
                <div class="font-mono text-gray-800">02-123456789-0</div>
              </div>
              
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-green-50 rounded-lg mr-4">
                    <i class="fas fa-home text-green-600"></i>
                  </div>
                  <div>
                    <h4 class="font-medium text-gray-800">Pag-IBIG MID Number</h4>
                    <p class="text-sm text-gray-600">Home Development Mutual Fund</p>
                  </div>
                </div>
                <div class="font-mono text-gray-800">1234-5678-9012</div>
              </div>
              
              <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                <div class="flex items-center">
                  <div class="p-2 bg-purple-50 rounded-lg mr-4">
                    <i class="fas fa-file-invoice-dollar text-purple-600"></i>
                  </div>
                  <div>
                    <h4 class="font-medium text-gray-800">TIN</h4>
                    <p class="text-sm text-gray-600">Tax Identification Number</p>
                  </div>
                </div>
                <div class="font-mono text-gray-800">123-456-789-000</div>
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

const activeTab = ref('personal')

const tabs = [
  { id: 'personal', label: 'Personal Details', icon: 'fas fa-user' },
  { id: 'emergency', label: 'Emergency Contacts', icon: 'fas fa-phone-alt' },
  { id: 'government', label: 'Government Numbers', icon: 'fas fa-id-card' },
]

const emergencyContacts = [
  { id: 1, name: 'Jane Doe', relationship: 'Sister', phone: '+63 917 123 4567', email: 'jane.doe@email.com', address: '456 Oak Street, Quezon City' },
  { id: 2, name: 'Michael Smith', relationship: 'Father', phone: '+63 922 987 6543', email: 'm.smith@email.com', address: '789 Pine Avenue, Taguig City' },
]
</script>

<style scoped>
.profile-employee {
  max-width: 1400px;
  margin: 0 auto;
}
</style>