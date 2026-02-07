<template>
  <div class="min-h-screen bg-gradient-to-b">
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
        <p class="text-gray-600 mt-2">Manage your account information and settings</p>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <div class="lg:col-span-1">
          <Card class="border-gray-200 shadow-lg rounded-2xl overflow-hidden sticky top-24">
            <div class="p-6 border-b border-gray-200 bg-white">
              <div class="flex items-center">
                <div class="w-16 h-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                  <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div class="ml-4">
                  <h3 class="font-bold text-gray-900">{{ userProfile.name }}</h3>
                  <p class="text-sm text-gray-500">{{ userProfile.email }}</p>
                  <p class="text-xs text-gray-500 mt-1">Member since {{ userProfile.memberSince }}</p>
                </div>
              </div>
            </div>

            <nav class="p-4 bg-white">
              <ul class="space-y-1">
                <li v-for="item in navItems" :key="item.id">
                  <button
                    @click="activeTab = item.id"
                    :class="[
                      'w-full flex items-center space-x-3 px-3 py-3 rounded-lg transition-all',
                      activeTab === item.id
                        ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-blue-600 font-medium'
                        : 'text-gray-700 hover:bg-gray-50'
                    ]"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                    </svg>
                    <span>{{ item.label }}</span>
                  </button>
                </li>
              </ul>
            </nav>
          </Card>
        </div>

        <div class="lg:col-span-3">
          <Card v-if="activeTab === 'personal'" class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
            <CardHeader class="p-6 border-b border-gray-200 bg-white">
              <CardTitle class="text-xl font-bold text-gray-900">Personal Information</CardTitle>
              <p class="text-gray-600 mt-1 text-sm">Update your personal details</p>
            </CardHeader>
            
            <CardContent class="p-6 bg-white">
              <form @submit.prevent="updatePersonalInfo" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <Label class="mb-2 block">First Name</Label>
                    <Input v-model="userProfile.firstName" required />
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">Last Name</Label>
                    <Input v-model="userProfile.lastName" required />
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">Email Address</Label>
                    <div class="flex items-center space-x-2">
                      <Input v-model="userProfile.email" type="email" required />
                      <Badge v-if="userProfile.emailVerified" variant="secondary" class="bg-green-100 text-green-800 hover:bg-green-100">
                        Verified
                      </Badge>
                      <Button
                        v-else
                        type="button"
                        variant="outline"
                        size="sm"
                        @click="verifyEmail"
                        class="text-blue-600 border-blue-600 hover:bg-blue-50"
                      >
                        Verify
                      </Button>
                    </div>
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">Phone Number</Label>
                    <Input v-model="userProfile.phone" type="tel" placeholder="+63 912 345 6789" required />
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">Date of Birth</Label>
                    <Input v-model="userProfile.birthDate" type="date" />
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">Gender</Label>
                    <Select v-model="userProfile.gender">
                      <SelectTrigger>
                        <SelectValue placeholder="Select gender" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="male">Male</SelectItem>
                        <SelectItem value="female">Female</SelectItem>
                        <SelectItem value="other">Other</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
                
                <div class="pt-6 border-t border-gray-200">
                  <div class="flex justify-end space-x-3">
                    <Button
                      type="button"
                      variant="outline"
                      @click="resetForm"
                    >
                      Cancel
                    </Button>
                    <Button
                      type="submit"
                      class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
                    >
                      Save Changes
                    </Button>
                  </div>
                </div>
              </form>
            </CardContent>
          </Card>

          <Card v-else-if="activeTab === 'addresses'" class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
            <CardHeader class="p-6 border-b border-gray-200 bg-white">
              <div class="flex justify-between items-center">
                <div>
                  <CardTitle class="text-xl font-bold text-gray-900">Address Book</CardTitle>
                  <p class="text-gray-600 mt-1 text-sm">Manage your shipping addresses</p>
                </div>
                <Button
                  @click="openAddressModal"
                  class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add New Address
                </Button>
              </div>
            </CardHeader>
            
            <CardContent class="p-6 bg-white">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                  v-for="address in userProfile.addresses"
                  :key="address.id"
                  :class="[
                    'border rounded-xl p-5 transition-all',
                    address.isDefault
                      ? 'border-blue-500 bg-blue-50'
                      : 'border-gray-200 hover:border-gray-300'
                  ]"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <div class="flex items-center mb-2">
                        <h3 class="font-semibold text-gray-900">{{ address.name }}</h3>
                        <Badge v-if="address.isDefault" class="ml-2 bg-blue-100 text-blue-800 hover:bg-blue-100 border-0">
                          Default
                        </Badge>
                      </div>
                      <p class="text-gray-600 text-sm">{{ address.street }}</p>
                      <p class="text-gray-600 text-sm">{{ address.city }}, {{ address.barangay }}</p>
                      <p class="text-gray-600 text-sm">{{ address.province }} {{ address.zipCode }}</p>
                      <p class="text-gray-600 mt-2 text-sm flex items-center">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        {{ address.phone }}
                      </p>
                    </div>
                    <div class="flex space-x-2">
                      <Button
                        variant="ghost"
                        size="icon"
                        @click="editAddress(address.id)"
                        class="text-gray-400 hover:text-blue-600 hover:bg-blue-50 h-8 w-8"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </Button>
                      <Button
                        variant="ghost"
                        size="icon"
                        @click="deleteAddress(address.id)"
                        class="text-gray-400 hover:text-red-600 hover:bg-red-50 h-8 w-8"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </Button>
                    </div>
                  </div>
                  
                  <div class="mt-4 pt-4 border-t border-gray-200">
                    <div class="flex space-x-3">
                      <Button
                        v-if="!address.isDefault"
                        variant="outline"
                        size="sm"
                        @click="setDefaultAddress(address.id)"
                      >
                        Set as Default
                      </Button>
                      <Button
                        variant="outline"
                        size="sm"
                        @click="useAddress(address)"
                        class="text-blue-600 border-blue-600 hover:bg-blue-50"
                      >
                        Use this Address
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div v-if="userProfile.addresses.length === 0" class="text-center py-12">
                <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No addresses saved</h3>
                <p class="text-gray-500 mb-6">Add your first shipping address to get started</p>
                <Button
                  @click="openAddressModal"
                  class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Add Address
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card v-else-if="activeTab === 'security'" class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
            <CardHeader class="p-6 border-b border-gray-200 bg-white">
              <CardTitle class="text-xl font-bold text-gray-900">Security Settings</CardTitle>
              <p class="text-gray-600 mt-1 text-sm">Manage your password and security preferences</p>
            </CardHeader>
            
            <CardContent class="p-6 bg-white">
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Change Password</h3>
                <form @submit.prevent="changePassword" class="space-y-4 max-w-md">
                  <div>
                    <Label class="mb-2 block">Current Password</Label>
                    <div class="relative">
                      <Input
                        v-model="passwordData.currentPassword"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="pr-10"
                        required
                      />
                      <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">New Password</Label>
                    <div class="relative">
                      <Input
                        v-model="passwordData.newPassword"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="pr-10"
                        required
                      />
                      <button
                        type="button"
                        @click="showNewPassword = !showNewPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Must be at least 8 characters with uppercase, lowercase, and number</p>
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">Confirm New Password</Label>
                    <div class="relative">
                      <Input
                        v-model="passwordData.confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="pr-10"
                        required
                      />
                      <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600"
                      >
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                  
                  <div class="pt-4">
                    <Button
                      type="submit"
                      class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
                    >
                      Update Password
                    </Button>
                  </div>
                </form>
              </div>

              <Separator class="my-8" />

              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Two-Factor Authentication</h3>
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-gray-600">Add an extra layer of security to your account</p>
                    <p class="text-sm text-gray-500 mt-1">Currently: <span :class="userProfile.twoFactorEnabled ? 'text-green-600 font-medium' : 'text-red-600 font-medium'">
                      {{ userProfile.twoFactorEnabled ? 'Enabled' : 'Disabled' }}
                    </span></p>
                  </div>
                  <Button
                    @click="toggleTwoFactor"
                    variant="outline"
                    :class="[
                      userProfile.twoFactorEnabled
                        ? 'bg-red-50 text-red-700 hover:bg-red-100 border-red-200'
                        : 'bg-green-50 text-green-700 hover:bg-green-100 border-green-200'
                    ]"
                  >
                    {{ userProfile.twoFactorEnabled ? 'Disable' : 'Enable' }} 2FA
                  </Button>
                </div>
              </div>

              <Separator class="my-8" />

              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Sessions</h3>
                <div class="space-y-4">
                  <div
                    v-for="session in userProfile.sessions"
                    :key="session.id"
                    class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
                  >
                    <div class="flex items-center">
                      <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="session.device === 'mobile'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="font-medium text-gray-900">{{ session.device }} • {{ session.browser }}</p>
                        <p class="text-sm text-gray-500">{{ session.location }} • {{ session.lastActive }}</p>
                      </div>
                    </div>
                    <div>
                      <Badge v-if="session.current" variant="secondary" class="bg-green-100 text-green-800 hover:bg-green-100">
                        Current
                      </Badge>
                      <Button
                        v-else
                        variant="outline"
                        size="sm"
                        @click="endSession(session.id)"
                        class="text-red-600 border-red-600 hover:bg-red-50"
                      >
                        End Session
                      </Button>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <Button
                    variant="outline"
                    @click="endAllSessions"
                    class="text-red-600 border-red-600 hover:bg-red-50"
                  >
                    End All Other Sessions
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card v-else-if="activeTab === 'preferences'" class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
            <CardHeader class="p-6 border-b border-gray-200 bg-white">
              <CardTitle class="text-xl font-bold text-gray-900">Preferences</CardTitle>
              <p class="text-gray-600 mt-1 text-sm">Customize your experience</p>
            </CardHeader>
            
            <CardContent class="p-6 bg-white">
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notification Preferences</h3>
                <div class="space-y-4">
                  <div v-for="notification in notificationPreferences" :key="notification.id" class="flex items-center justify-between">
                    <div>
                      <p class="font-medium text-gray-900">{{ notification.label }}</p>
                      <p class="text-sm text-gray-500">{{ notification.description }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                      <span class="text-sm text-gray-500">{{ notification.enabled ? 'On' : 'Off' }}</span>
                      <Checkbox :id="notification.id" :checked="notification.enabled" @update:checked="toggleNotification(notification.id)" />
                    </div>
                  </div>
                </div>
              </div>

              <Separator class="my-8" />

              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Preferences</h3>
                <div class="space-y-4">
                  <div v-for="email in emailPreferences" :key="email.id" class="flex items-center justify-between">
                    <div>
                      <p class="font-medium text-gray-900">{{ email.label }}</p>
                      <p class="text-sm text-gray-500">{{ email.description }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                      <span class="text-sm text-gray-500">{{ email.enabled ? 'On' : 'Off' }}</span>
                      <Checkbox :id="email.id" :checked="email.enabled" @update:checked="toggleEmailPreference(email.id)" />
                    </div>
                  </div>
                </div>
              </div>

              <Separator class="my-8" />

              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Language & Region</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <Label class="mb-2 block">Language</Label>
                    <Select v-model="userProfile.language">
                      <SelectTrigger>
                        <SelectValue placeholder="Select language" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="en">English</SelectItem>
                        <SelectItem value="fil">Filipino</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  
                  <div>
                    <Label class="mb-2 block">Time Zone</Label>
                    <Select v-model="userProfile.timezone">
                      <SelectTrigger>
                        <SelectValue placeholder="Select timezone" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="Asia/Manila">Philippine Time (UTC+8)</SelectItem>
                        <SelectItem value="UTC">UTC</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>

              <Separator class="my-8" />

              <div>
                <div class="flex justify-end">
                  <Button
                    @click="savePreferences"
                    class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white"
                  >
                    Save Preferences
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card v-else-if="activeTab === 'account'" class="border-gray-200 shadow-lg rounded-2xl overflow-hidden">
            <CardHeader class="p-6 border-b border-gray-200 bg-white">
              <CardTitle class="text-xl font-bold text-gray-900">Account Status</CardTitle>
              <p class="text-gray-600 mt-1 text-sm">Manage your account membership and status</p>
            </CardHeader>
            
            <CardContent class="p-6 bg-white">
              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Type</h3>
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-6">
                  <div class="flex items-center justify-between">
                    <div>
                      <h4 class="text-xl font-bold text-gray-900">{{ userProfile.accountType }}</h4>
                      <p class="text-gray-600 mt-1 text-sm">Since {{ userProfile.memberSince }}</p>
                      <div class="flex items-center mt-4 space-x-4">
                        <div class="flex items-center text-sm">
                          <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          <span>Priority Support</span>
                        </div>
                        <div class="flex items-center text-sm">
                          <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          <span>Exclusive Discounts</span>
                        </div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="text-3xl font-bold text-gray-900">Free</div>
                      <p class="text-gray-600 text-sm">Current Plan</p>
                      <Button
                        variant="outline"
                        class="mt-4 text-blue-600 border-blue-600 hover:bg-blue-50"
                      >
                        Upgrade Plan
                      </Button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Purchase Summary</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                  <Card class="border-gray-200 shadow-sm">
                    <CardContent class="p-6 flex items-center">
                      <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="text-2xl font-bold text-gray-900">{{ userProfile.totalOrders }}</p>
                        <p class="text-sm text-gray-500">Total Orders</p>
                      </div>
                    </CardContent>
                  </Card>
                  
                  <Card class="border-gray-200 shadow-sm">
                    <CardContent class="p-6 flex items-center">
                      <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="text-2xl font-bold text-gray-900">₱{{ userProfile.totalSpent.toLocaleString() }}</p>
                        <p class="text-sm text-gray-500">Total Spent</p>
                      </div>
                    </CardContent>
                  </Card>
                  
                  <Card class="border-gray-200 shadow-sm">
                    <CardContent class="p-6 flex items-center">
                      <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                      </div>
                      <div>
                        <p class="text-2xl font-bold text-gray-900">{{ userProfile.avgOrderValue.toLocaleString() }}</p>
                        <p class="text-sm text-gray-500">Avg. Order Value</p>
                      </div>
                    </CardContent>
                  </Card>
                </div>
              </div>

              <Separator class="my-8" />

              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Actions</h3>
                <div class="space-y-4">
                  <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                      <p class="font-medium text-gray-900">Download Account Data</p>
                      <p class="text-sm text-gray-500">Request a copy of all your data</p>
                    </div>
                    <Button
                      variant="outline"
                      @click="downloadAccountData"
                      class="text-blue-600 border-blue-600 hover:bg-blue-50"
                    >
                      Request Data
                    </Button>
                  </div>
                  
                  <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                      <p class="font-medium text-gray-900">Delete Account</p>
                      <p class="text-sm text-gray-500">Permanently delete your account and all data</p>
                    </div>
                    <Button
                      variant="outline"
                      @click="deleteAccount"
                      class="text-red-600 border-red-600 hover:bg-red-50"
                    >
                      Delete Account
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <Dialog :open="showAddressModal" @update:open="val => { if(!val) closeAddressModal() }">
      <DialogContent class="max-w-md bg-white">
        <DialogHeader>
          <DialogTitle>{{ editingAddress ? 'Edit Address' : 'Add New Address' }}</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="saveAddress" class="space-y-4">
          <div>
            <Label class="mb-2 block">Address Label</Label>
            <Input v-model="addressForm.name" placeholder="Home, Office, etc." required />
          </div>
          
          <div>
            <Label class="mb-2 block">Full Name</Label>
            <Input v-model="addressForm.fullName" required />
          </div>
          
          <div>
            <Label class="mb-2 block">Street Address</Label>
            <Textarea v-model="addressForm.street" rows="2" required class="resize-none" />
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label class="mb-2 block">City</Label>
              <Select v-model="addressForm.city">
                <SelectTrigger>
                  <SelectValue placeholder="Select City" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="bacoor">Bacoor</SelectItem>
                  <SelectItem value="imus">Imus</SelectItem>
                  <SelectItem value="dasmarinas">Dasmarinas</SelectItem>
                  <SelectItem value="tanza">Tanza</SelectItem>
                  <SelectItem value="trece-martires">Trece Martires</SelectItem>
                  <SelectItem value="general-trias">General Trias</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div>
              <Label class="mb-2 block">Barangay</Label>
              <Input v-model="addressForm.barangay" required />
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <Label class="mb-2 block">Province</Label>
              <Input v-model="addressForm.province" required />
            </div>
            
            <div>
              <Label class="mb-2 block">ZIP Code</Label>
              <Input v-model="addressForm.zipCode" required />
            </div>
          </div>
          
          <div>
            <Label class="mb-2 block">Phone Number</Label>
            <Input v-model="addressForm.phone" type="tel" placeholder="+63 912 345 6789" required />
          </div>
          
          <div class="flex items-center space-x-2">
            <Checkbox id="defaultAddress" :checked="addressForm.isDefault" @update:checked="val => addressForm.isDefault = val" />
            <Label for="defaultAddress" class="text-sm text-gray-700 font-normal cursor-pointer">
              Set as default shipping address
            </Label>
          </div>
          
          <DialogFooter>
            <Button type="button" variant="outline" @click="closeAddressModal">Cancel</Button>
            <Button type="submit" class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white">
              {{ editingAddress ? 'Update' : 'Save' }} Address
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Separator } from '@/components/ui/separator'

// State
const activeTab = ref('personal')
const showAddressModal = ref(false)
const editingAddress = ref(false)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// User Profile Data
const userProfile = reactive({
  name: 'Julian Namoc',
  email: 'IspyMILK@gmail.com',
  emailVerified: true,
  memberSince: 'June 2026',
  accountType: 'Premium Member',
  totalOrders: 12,
  totalSpent: 45600,
  avgOrderValue: 3800,
  
  // Personal Info
  firstName: 'Julian',
  lastName: 'Namoc',
  phone: '+63 912 345 6789',
  birthDate: '1990-05-15',
  gender: 'male',
  
  // Addresses
  addresses: [
    {
      id: 1,
      name: 'Home',
      fullName: 'Julian Namoc',
      street: '123 Main Street, Phase 1',
      city: 'Dasmarinas',
      barangay: 'San Miguel',
      province: 'Cavite',
      zipCode: '4114',
      phone: '+63 912 345 6789',
      isDefault: true
    },
    {
      id: 2,
      name: 'Office',
      fullName: 'Julian Namoc',
      street: '456 Oak Avenue, Suite 302',
      city: 'Imus',
      barangay: 'Bucandala',
      province: 'Cavite',
      zipCode: '4103',
      phone: '+63 917 890 1234',
      isDefault: false
    }
  ],
  
  // Security
  twoFactorEnabled: false,
  sessions: [
    {
      id: 1,
      device: 'Desktop',
      browser: 'Chrome on Windows',
      location: 'Cavite, Philippines',
      lastActive: 'Currently active',
      current: true
    },
    {
      id: 2,
      device: 'Mobile',
      browser: 'Safari on iOS',
      location: 'Manila, Philippines',
      lastActive: '2 hours ago',
      current: false
    }
  ],
  
  // Preferences
  language: 'en',
  timezone: 'Asia/Manila'
})

// Navigation Items
const navItems = ref([
  { id: 'personal', label: 'Personal Info', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
  { id: 'addresses', label: 'Addresses', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
  { id: 'security', label: 'Security', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
  { id: 'preferences', label: 'Preferences', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z' },
  { id: 'account', label: 'Account Status', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' }
])

// Notification Preferences
const notificationPreferences = ref([
  { id: 'order', label: 'Order Updates', description: 'Get notified about order status changes', enabled: true },
  { id: 'promo', label: 'Promotions & Offers', description: 'Receive special offers and discounts', enabled: true },
  { id: 'service', label: 'Service Reminders', description: 'Reminders for scheduled services', enabled: true },
  { id: 'news', label: 'Newsletter', description: 'Receive our monthly newsletter', enabled: false }
])

// Email Preferences
const emailPreferences = ref([
  { id: 'receipts', label: 'Order Receipts', description: 'Email receipts for your purchases', enabled: true },
  { id: 'feedback', label: 'Feedback Requests', description: 'Requests for product/service feedback', enabled: true },
  { id: 'account', label: 'Account Activity', description: 'Important account notifications', enabled: true }
])

// Password Data
const passwordData = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

// Address Form
const addressForm = reactive({
  id: null,
  name: '',
  fullName: '',
  street: '',
  city: '',
  barangay: '',
  province: 'Cavite',
  zipCode: '',
  phone: '',
  isDefault: false
})

// Computed
const fullName = computed(() => `${userProfile.firstName} ${userProfile.lastName}`)

// Methods
const updatePersonalInfo = () => {
  userProfile.name = fullName.value
  console.log('Personal info updated:', userProfile)
  alert('Personal information updated successfully!')
}

const resetForm = () => {
  // Reset to original values
  userProfile.firstName = 'Julian'
  userProfile.lastName = 'Namoc'
  userProfile.email = 'IspyMILK@gmail.com'
  userProfile.phone = '+63 912 345 6789'
  userProfile.birthDate = '1990-05-15'
  userProfile.gender = 'male'
}

const verifyEmail = () => {
  userProfile.emailVerified = true
  alert('Verification email sent! Please check your inbox.')
}

const openAddressModal = (address = null) => {
  if (address) {
    editingAddress.value = true
    Object.assign(addressForm, address)
  } else {
    editingAddress.value = false
    resetAddressForm()
  }
  showAddressModal.value = true
}

const closeAddressModal = () => {
  showAddressModal.value = false
  editingAddress.value = false
  resetAddressForm()
}

const resetAddressForm = () => {
  Object.assign(addressForm, {
    id: null,
    name: '',
    fullName: '',
    street: '',
    city: '',
    barangay: '',
    province: 'Cavite',
    zipCode: '',
    phone: '',
    isDefault: false
  })
}

const saveAddress = () => {
  if (editingAddress.value) {
    // Update existing address
    const index = userProfile.addresses.findIndex(a => a.id === addressForm.id)
    if (index !== -1) {
      userProfile.addresses[index] = { ...addressForm }
    }
  } else {
    // Add new address
    const newId = Math.max(...userProfile.addresses.map(a => a.id), 0) + 1
    userProfile.addresses.push({
      id: newId,
      ...addressForm
    })
  }
  
  // If set as default, update other addresses
  if (addressForm.isDefault) {
    userProfile.addresses.forEach(addr => {
      if (addr.id !== addressForm.id) {
        addr.isDefault = false
      }
    })
  }
  
  closeAddressModal()
  alert('Address saved successfully!')
}

const editAddress = (addressId) => {
  const address = userProfile.addresses.find(a => a.id === addressId)
  if (address) {
    openAddressModal(address)
  }
}

const deleteAddress = (addressId) => {
  if (confirm('Are you sure you want to delete this address?')) {
    userProfile.addresses = userProfile.addresses.filter(a => a.id !== addressId)
    alert('Address deleted successfully!')
  }
}

const setDefaultAddress = (addressId) => {
  userProfile.addresses.forEach(addr => {
    addr.isDefault = addr.id === addressId
  })
  alert('Default address updated!')
}

const useAddress = (address) => {
  console.log('Using address:', address)
  // In real app, this would set as shipping address for checkout
  alert(`Address "${address.name}" selected for shipping`)
}

const changePassword = () => {
  if (passwordData.newPassword !== passwordData.confirmPassword) {
    alert('New passwords do not match!')
    return
  }
  
  // Simple password validation
  if (passwordData.newPassword.length < 8) {
    alert('Password must be at least 8 characters long')
    return
  }
  
  console.log('Password changed successfully')
  alert('Password updated successfully!')
  
  // Reset form
  passwordData.currentPassword = ''
  passwordData.newPassword = ''
  passwordData.confirmPassword = ''
}

const toggleTwoFactor = () => {
  userProfile.twoFactorEnabled = !userProfile.twoFactorEnabled
  alert(`Two-factor authentication ${userProfile.twoFactorEnabled ? 'enabled' : 'disabled'}!`)
}

const endSession = (sessionId) => {
  if (sessionId === 1) {
    alert('Cannot end current session')
    return
  }
  
  userProfile.sessions = userProfile.sessions.filter(s => s.id !== sessionId)
  alert('Session ended successfully!')
}

const endAllSessions = () => {
  userProfile.sessions = userProfile.sessions.filter(s => s.current)
  alert('All other sessions ended!')
}

const toggleNotification = (id) => {
  const notification = notificationPreferences.value.find(n => n.id === id)
  if (notification) {
    notification.enabled = !notification.enabled
  }
}

const toggleEmailPreference = (id) => {
  const email = emailPreferences.value.find(e => e.id === id)
  if (email) {
    email.enabled = !email.enabled
  }
}

const savePreferences = () => {
  console.log('Preferences saved:', {
    language: userProfile.language,
    timezone: userProfile.timezone,
    notifications: notificationPreferences.value,
    emails: emailPreferences.value
  })
  alert('Preferences saved successfully!')
}

const downloadAccountData = () => {
  alert('Account data download request submitted. You will receive an email with download link within 24 hours.')
}

const deleteAccount = () => {
  if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
    alert('Account deletion request submitted. Our team will contact you for confirmation.')
  }
}
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>