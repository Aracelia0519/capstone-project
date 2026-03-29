<template>
  <div class="profile-employee p-4 md:p-6 text-slate-900">
    <Toaster richColors position="top-right" expand />

    <div class="mb-6 md:mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">My Profile</h1>
      <p class="text-gray-500 mt-1 text-sm md:text-base">Manage your personal information</p>
    </div>

    <div v-if="loadingProfile" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
      <div>
        <Card class="bg-white rounded-xl shadow-lg border-0 mb-6">
          <CardContent class="p-6 flex flex-col items-center">
            <Avatar class="w-24 h-24 mb-4 ring-4 ring-indigo-50">
              <AvatarImage :src="profileData.avatar_url || ''" /> 
              <AvatarFallback class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-3xl font-bold">
                {{ getInitials(profileData.first_name, profileData.last_name) }}
              </AvatarFallback>
            </Avatar>
            <h2 class="text-xl font-bold text-gray-800 text-center">
                {{ profileData.first_name }} {{ profileData.last_name }}
            </h2>
            <p class="text-gray-600 mt-1 text-center capitalize">
                {{ profileData.position || formatRole(profileData.role) }}
            </p>
            <p v-if="profileData.employee_code" class="text-xs text-gray-400 mt-2 uppercase tracking-wide">
                ID: {{ profileData.employee_code }}
            </p>
            
            <div class="mt-6 w-full space-y-1">
              <div v-if="profileData.department" class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Department</span>
                <span class="text-sm font-medium text-gray-800">{{ profileData.department }}</span>
              </div>
              <div v-if="profileData.hire_date || profileData.created_at" class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Join Date</span>
                <span class="text-sm font-medium text-gray-800">{{ formatDate(profileData.hire_date || profileData.created_at) }}</span>
              </div>
              <div v-if="profileData.employment_status || profileData.role" class="flex items-center justify-between py-3">
                <span class="text-sm text-gray-600">Account Type</span>
                <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full capitalize">
                  {{ profileData.employment_status || formatRole(profileData.role) }}
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-white rounded-xl shadow-lg border-0">
          <CardHeader class="p-6 pb-2">
             <CardTitle class="text-lg font-semibold text-gray-800">Change Password</CardTitle>
          </CardHeader>
          <CardContent class="p-6 pt-2 space-y-4">
            <form @submit.prevent="updatePassword">
                <div class="mb-4">
                    <Label class="text-sm font-medium text-gray-700 mb-2 block">Current Password</Label>
                    <div class="relative">
                        <Input 
                            v-model="passwordForm.current_password" 
                            :type="showCurrentPassword ? 'text' : 'password'" 
                            required 
                            class="w-full bg-white text-gray-900 border-gray-300 focus-visible:ring-indigo-500 h-10 pr-10" 
                        />
                        <button 
                            type="button" 
                            @click="showCurrentPassword = !showCurrentPassword" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-indigo-600 focus:outline-none transition-colors"
                        >
                            <EyeOff v-if="showCurrentPassword" class="w-5 h-5" />
                            <Eye v-else class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <Label class="text-sm font-medium text-gray-700 mb-2 block">New Password</Label>
                    <div class="relative">
                        <Input 
                            v-model="passwordForm.password" 
                            :type="showNewPassword ? 'text' : 'password'" 
                            required 
                            minlength="8" 
                            class="w-full bg-white text-gray-900 border-gray-300 focus-visible:ring-indigo-500 h-10 pr-10" 
                        />
                        <button 
                            type="button" 
                            @click="showNewPassword = !showNewPassword" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-indigo-600 focus:outline-none transition-colors"
                        >
                            <EyeOff v-if="showNewPassword" class="w-5 h-5" />
                            <Eye v-else class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <div class="mb-4">
                    <Label class="text-sm font-medium text-gray-700 mb-2 block">Confirm Password</Label>
                    <div class="relative">
                        <Input 
                            v-model="passwordForm.password_confirmation" 
                            :type="showConfirmPassword ? 'text' : 'password'" 
                            required 
                            minlength="8" 
                            class="w-full bg-white text-gray-900 border-gray-300 focus-visible:ring-indigo-500 h-10 pr-10" 
                        />
                        <button 
                            type="button" 
                            @click="showConfirmPassword = !showConfirmPassword" 
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-600 hover:text-indigo-600 focus:outline-none transition-colors"
                        >
                            <EyeOff v-if="showConfirmPassword" class="w-5 h-5" />
                            <Eye v-else class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <Button 
                    type="submit" 
                    :disabled="savingPassword"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold h-11 mt-2 flex justify-center items-center"
                >
                    <svg v-if="savingPassword" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ savingPassword ? 'Updating...' : 'Update Password' }}
                </Button>
            </form>
          </CardContent>
        </Card>
      </div>

      <div class="lg:col-span-2">
        <Tabs v-model="activeTab" class="w-full">
          <div class="mb-6 border-b border-gray-200">
            <div class="overflow-x-auto pb-1 -mb-1 w-full">
              <TabsList class="bg-transparent p-0 h-auto flex w-max min-w-full md:w-full space-x-2 md:space-x-8 justify-start">
                <TabsTrigger 
                  v-for="tab in tabs" 
                  :key="tab.id"
                  :value="tab.id"
                  class="py-3 px-3 md:px-1 rounded-none border-b-2 border-transparent data-[state=active]:border-indigo-600 data-[state=active]:text-indigo-600 text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition-colors shadow-none bg-transparent whitespace-nowrap"
                >
                  <i :class="tab.icon + ' mr-2'"></i>
                  {{ tab.label }}
                </TabsTrigger>
              </TabsList>
            </div>
          </div>

          <Card class="bg-white rounded-xl shadow-lg border-0">
            <CardContent class="p-4 md:p-6">
              
              <TabsContent value="personal" class="mt-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
                  <h3 class="text-lg font-semibold text-gray-800">Personal Information</h3>
                  <Button variant="ghost" class="text-sm text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 font-medium px-3 h-9 self-start sm:self-auto">
                    <i class="fas fa-edit mr-1"></i>
                    Edit Information
                  </Button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">First Name</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">
                        {{ profileData.first_name || 'N/A' }}
                    </div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Last Name</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">
                        {{ profileData.last_name || 'N/A' }}
                    </div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Birthdate</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">
                        {{ formatDate(profileData.date_of_birth) || 'N/A' }}
                    </div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Gender</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100 capitalize">
                        {{ profileData.gender || 'N/A' }}
                    </div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100 capitalize">
                        {{ profileData.marital_status || 'N/A' }}
                    </div>
                  </div>
                  <div class="md:col-span-2">
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Address</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">
                        {{ profileData.address || 'N/A' }}
                    </div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">
                        {{ profileData.phone || 'N/A' }}
                    </div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">
                        {{ profileData.emergency_contact || 'N/A' }}
                    </div>
                  </div>
                  <div class="md:col-span-2">
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Email Address</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100 break-all">
                        {{ profileData.email || 'N/A' }}
                    </div>
                  </div>
                </div>
              </TabsContent>

              <TabsContent value="government" class="mt-0">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Government Numbers</h3>
                
                <div class="space-y-4">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-200 rounded-lg gap-3">
                    <div class="flex items-center">
                      <div class="p-2 bg-red-50 rounded-lg mr-4 flex-shrink-0">
                        <i class="fas fa-id-card text-red-600 text-lg"></i>
                      </div>
                      <div>
                        <h4 class="font-medium text-gray-800">SSS Number</h4>
                        <p class="text-xs md:text-sm text-gray-500">Social Security System</p>
                      </div>
                    </div>
                    <div class="font-mono text-gray-800 text-sm md:text-base bg-gray-50 sm:bg-transparent p-2 sm:p-0 rounded self-start sm:self-auto w-full sm:w-auto text-center sm:text-right">
                      {{ profileData.sss_number || 'Not provided' }}
                    </div>
                  </div>
                  
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-200 rounded-lg gap-3">
                    <div class="flex items-center">
                      <div class="p-2 bg-blue-50 rounded-lg mr-4 flex-shrink-0">
                        <i class="fas fa-heartbeat text-blue-600 text-lg"></i>
                      </div>
                      <div>
                        <h4 class="font-medium text-gray-800">PhilHealth Number</h4>
                        <p class="text-xs md:text-sm text-gray-500">Philippine Health Insurance</p>
                      </div>
                    </div>
                    <div class="font-mono text-gray-800 text-sm md:text-base bg-gray-50 sm:bg-transparent p-2 sm:p-0 rounded self-start sm:self-auto w-full sm:w-auto text-center sm:text-right">
                      {{ profileData.philhealth_number || 'Not provided' }}
                    </div>
                  </div>
                  
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-200 rounded-lg gap-3">
                    <div class="flex items-center">
                      <div class="p-2 bg-green-50 rounded-lg mr-4 flex-shrink-0">
                        <i class="fas fa-home text-green-600 text-lg"></i>
                      </div>
                      <div>
                        <h4 class="font-medium text-gray-800">Pag-IBIG MID</h4>
                        <p class="text-xs md:text-sm text-gray-500">Home Development Mutual Fund</p>
                      </div>
                    </div>
                    <div class="font-mono text-gray-800 text-sm md:text-base bg-gray-50 sm:bg-transparent p-2 sm:p-0 rounded self-start sm:self-auto w-full sm:w-auto text-center sm:text-right">
                      {{ profileData.pagibig_number || 'Not provided' }}
                    </div>
                  </div>
                  
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border border-gray-200 rounded-lg gap-3">
                    <div class="flex items-center">
                      <div class="p-2 bg-purple-50 rounded-lg mr-4 flex-shrink-0">
                        <i class="fas fa-file-invoice-dollar text-purple-600 text-lg"></i>
                      </div>
                      <div>
                        <h4 class="font-medium text-gray-800">TIN</h4>
                        <p class="text-xs md:text-sm text-gray-500">Tax Identification Number</p>
                      </div>
                    </div>
                    <div class="font-mono text-gray-800 text-sm md:text-base bg-gray-50 sm:bg-transparent p-2 sm:p-0 rounded self-start sm:self-auto w-full sm:w-auto text-center sm:text-right">
                      {{ profileData.tin_number || 'Not provided' }}
                    </div>
                  </div>
                </div>
              </TabsContent>
            </CardContent>
          </Card>
        </Tabs>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from '@/utils/axios'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Toaster, toast } from 'vue-sonner'
import { Eye, EyeOff } from 'lucide-vue-next'

const activeTab = ref('personal')
const loadingProfile = ref(true)
const savingPassword = ref(false)

// Password visibility toggles
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const profileData = ref({})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const tabs = [
  { id: 'personal', label: 'Personal Details', icon: 'fas fa-user' },
  { id: 'government', label: 'Government Numbers', icon: 'fas fa-id-card' },
]

onMounted(() => {
  fetchUserProfile()
})

const fetchUserProfile = async () => {
  loadingProfile.value = true
  try {
    const response = await axios.get('/employee/profile') // CHANGED ROUTE
    
    if (response.data.status === 'success') {
      profileData.value = response.data.data.profile
    }
  } catch (error) {
    console.error('Failed to load profile information.', error)
    toast.error('Failed to load profile information.')
  } finally {
    loadingProfile.value = false
  }
}

const updatePassword = async () => {
  if (passwordForm.password !== passwordForm.password_confirmation) {
    toast.error('New password and confirmation do not match.')
    return
  }

  savingPassword.value = true
  try {
    const response = await axios.post('/employee/profile/update-password', { // CHANGED ROUTE
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation
    })

    if (response.data.status === 'success') {
      toast.success(response.data.message || 'Password updated successfully!')
      
      // Reset form fields
      passwordForm.current_password = ''
      passwordForm.password = ''
      passwordForm.password_confirmation = ''

      // Reset visibility toggles
      showCurrentPassword.value = false
      showNewPassword.value = false
      showConfirmPassword.value = false
    }
  } catch (error) {
    console.error('Password update failed:', error)
    
    if (error.response?.status === 422) {
      const errorMsg = error.response.data.message || 'Validation failed';
      if (error.response.data.errors) {
        const firstKey = Object.keys(error.response.data.errors)[0];
        toast.error(error.response.data.errors[firstKey][0]);
      } else {
        toast.error(errorMsg);
      }
    } else {
      toast.error('An unexpected error occurred while updating the password.')
    }
  } finally {
    savingPassword.value = false
  }
}

// Format Name Initials
const getInitials = (first, last) => {
  if (!first && !last) return 'U'
  return `${(first || '').charAt(0)}${(last || '').charAt(0)}`.toUpperCase()
}

// Make roles readable (e.g., hr_manager => Hr Manager)
const formatRole = (role) => {
  if (!role) return 'Employee'
  return role.replace(/_/g, ' ')
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const options = { year: 'numeric', month: 'long', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('en-US', options)
}
</script>

<style scoped>
.profile-employee {
  max-width: 1600px;
  margin: 0 auto;
}
/* Scrollbar styling for horizontal tabs */
.overflow-x-auto::-webkit-scrollbar {
  height: 4px;
}
.overflow-x-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
  background-color: #e5e7eb;
  border-radius: 20px;
}
</style>