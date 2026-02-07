<template>
  <div class="profile-employee p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">My Profile</h1>
      <p class="text-gray-500 mt-1 text-sm md:text-base">Manage your personal information</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
      <div>
        <Card class="bg-white rounded-xl shadow-lg border-0 mb-6">
          <CardContent class="p-6 flex flex-col items-center">
            <Avatar class="w-24 h-24 mb-4 ring-4 ring-indigo-50">
              <AvatarImage src="" /> 
              <AvatarFallback class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-3xl font-bold">
                JD
              </AvatarFallback>
            </Avatar>
            <h2 class="text-xl font-bold text-gray-800 text-center">John Doe</h2>
            <p class="text-gray-600 mt-1 text-center">Senior Software Engineer</p>
            <p class="text-xs text-gray-400 mt-2 uppercase tracking-wide">ID: EMP-2023-001</p>
            
            <div class="mt-6 w-full space-y-1">
              <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Department</span>
                <span class="text-sm font-medium text-gray-800">Engineering</span>
              </div>
              <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Join Date</span>
                <span class="text-sm font-medium text-gray-800">Jan 15, 2021</span>
              </div>
              <div class="flex items-center justify-between py-3">
                <span class="text-sm text-gray-600">Employment Type</span>
                <span class="text-sm font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Regular</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-white rounded-xl shadow-lg border-0">
          <CardHeader class="p-6 pb-2">
             <CardTitle class="text-lg font-semibold text-gray-800">Change Password</CardTitle>
          </CardHeader>
          <CardContent class="p-6 pt-2 space-y-4">
            <div>
              <Label class="text-sm font-medium text-gray-700 mb-2 block">Current Password</Label>
              <Input type="password" class="w-full focus-visible:ring-indigo-500 h-10" />
            </div>
            <div>
              <Label class="text-sm font-medium text-gray-700 mb-2 block">New Password</Label>
              <Input type="password" class="w-full focus-visible:ring-indigo-500 h-10" />
            </div>
            <div>
              <Label class="text-sm font-medium text-gray-700 mb-2 block">Confirm Password</Label>
              <Input type="password" class="w-full focus-visible:ring-indigo-500 h-10" />
            </div>
            <Button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold h-11 mt-2">
              Update Password
            </Button>
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
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Full Name</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">John Michael Doe</div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Birthdate</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">May 15, 1990</div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Gender</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">Male</div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Marital Status</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">Single</div>
                  </div>
                  <div class="md:col-span-2">
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Address</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">123 Main Street, Makati City, Metro Manila, Philippines</div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100">+63 912 345 6789</div>
                  </div>
                  <div>
                    <Label class="block text-sm font-medium text-gray-700 mb-2">Email Address</Label>
                    <div class="p-3 bg-gray-50 rounded-lg text-gray-800 text-sm border border-gray-100 break-all">john.doe@company.com</div>
                  </div>
                </div>
              </TabsContent>

              <TabsContent value="emergency" class="mt-0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-2">
                  <h3 class="text-lg font-semibold text-gray-800">Emergency Contacts</h3>
                  <Button variant="ghost" class="text-sm text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 font-medium px-3 h-9 self-start sm:self-auto">
                    <i class="fas fa-plus mr-1"></i>
                    Add Contact
                  </Button>
                </div>
                
                <div class="space-y-6">
                  <div v-for="contact in emergencyContacts" 
                       :key="contact.id"
                       class="border border-gray-200 rounded-lg p-4 md:p-5 hover:border-indigo-200 transition-colors">
                    <div class="flex items-center justify-between mb-4">
                      <h4 class="font-medium text-gray-800 text-base">{{ contact.name }}</h4>
                      <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded">
                        {{ contact.relationship }}
                      </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <Label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Contact Number</Label>
                        <div class="text-gray-800 text-sm font-medium">{{ contact.phone }}</div>
                      </div>
                      <div>
                        <Label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Email</Label>
                        <div class="text-gray-800 text-sm font-medium break-all">{{ contact.email }}</div>
                      </div>
                      <div class="md:col-span-2">
                        <Label class="block text-xs uppercase tracking-wide text-gray-500 mb-1">Address</Label>
                        <div class="text-gray-800 text-sm">{{ contact.address }}</div>
                      </div>
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
                      34-1234567-8
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
                      02-123456789-0
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
                      1234-5678-9012
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
                      123-456-789-000
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
import { ref } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

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