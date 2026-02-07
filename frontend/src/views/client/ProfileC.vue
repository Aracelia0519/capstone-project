<template>
  <div class="profile-container bg-gradient-to-br from-slate-900 via-gray-900 to-gray-950 min-h-screen p-4 md:p-8">
    
    <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-8">
      <div class="flex-1">
        <h1 class="flex items-center gap-3 text-3xl font-bold text-white mb-2">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Account Management
        </h1>
        <p class="text-slate-400">Manage your personal information, security, and contact details</p>
      </div>
      <div class="flex items-center gap-4 w-full md:w-auto">
        <div class="flex items-center gap-2 px-4 py-2 bg-slate-800/50 rounded-full border border-slate-700/50">
          <div class="w-2.5 h-2.5 rounded-full" :class="user.status === 'active' ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]'"></div>
          <span class="text-sm font-medium text-slate-300">{{ user.status === 'active' ? 'Account Active' : user.status === 'pending' ? 'Pending Approval' : 'Inactive' }}</span>
        </div>
        <Button 
          :disabled="!hasChanges || loading" 
          @click="saveProfile"
          class="bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white border-0 shadow-lg shadow-indigo-500/20"
        >
          <svg v-if="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
          </svg>
          <svg v-else class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ loading ? 'Saving...' : 'Save Changes' }}
        </Button>
      </div>
    </div>

    <div v-if="initialLoading" class="flex flex-col items-center justify-center py-20 text-slate-400">
      <div class="w-10 h-10 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin mb-4"></div>
      <p>Loading profile data...</p>
    </div>

    <div v-if="error" class="p-4 mb-6 bg-red-500/10 border border-red-500/20 rounded-xl flex items-center gap-3 text-red-400">
      <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ error }}
      <Button variant="link" @click="fetchUserProfile" class="text-red-400 underline ml-auto">Retry</Button>
    </div>

    <div v-if="!initialLoading && !error" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <Card class="col-span-1 lg:col-span-3 bg-gradient-to-br from-slate-800/60 to-slate-900/60 border-slate-700/50 backdrop-blur-sm">
        <CardContent class="p-6 md:p-8 flex flex-col md:flex-row items-center md:items-start gap-8">
          <div class="relative">
            <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-800 flex items-center justify-center shadow-xl border border-slate-600/50">
              <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-slate-800" 
                 :class="user.status === 'active' ? 'bg-emerald-500' : 'bg-amber-500'"></div>
          </div>
          
          <div class="flex-1 text-center md:text-left">
            <h2 class="text-2xl font-bold text-white mb-1">{{ user.full_name || `${user.first_name} ${user.last_name}` }}</h2>
            <Badge variant="secondary" class="mb-4 bg-slate-700/50 text-indigo-300 border-slate-600">{{ formatRole(user.role) }}</Badge>
            
            <div class="flex flex-col md:flex-row gap-4 text-sm text-slate-400">
              <div class="flex items-center gap-2 justify-center md:justify-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Member since {{ formatDate(user.created_at) }}
              </div>
              <div class="flex items-center gap-2 justify-center md:justify-start">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ user.email }}
              </div>
            </div>
          </div>

          <div class="flex gap-4 md:gap-8 w-full md:w-auto justify-center md:justify-end">
            <div class="text-center">
              <div class="text-xl font-bold text-white">{{ stats.colorSelections }}</div>
              <div class="text-xs text-slate-500 uppercase tracking-wider">Colors</div>
            </div>
            <div class="w-px bg-slate-700/50"></div>
            <div class="text-center">
              <div class="text-xl font-bold text-white">{{ stats.serviceRequests }}</div>
              <div class="text-xs text-slate-500 uppercase tracking-wider">Requests</div>
            </div>
            <div class="w-px bg-slate-700/50"></div>
            <div class="text-center">
              <div class="text-xl font-bold text-white">{{ stats.activeProjects }}</div>
              <div class="text-xs text-slate-500 uppercase tracking-wider">Active</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="col-span-1 lg:col-span-2 bg-slate-800/40 border-slate-700/30 backdrop-blur-sm">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-white">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
            </svg>
            Personal & Contact Information
          </CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="updatePersonalAndContactInfo" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label class="text-slate-300">First Name</Label>
                <Input v-model="user.first_name" @input="markChanged" placeholder="Enter your first name" class="bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus-visible:ring-indigo-500/50" />
                <p v-if="validationErrors.first_name" class="text-xs text-red-400">{{ validationErrors.first_name[0] }}</p>
              </div>
              <div class="space-y-2">
                <Label class="text-slate-300">Last Name</Label>
                <Input v-model="user.last_name" @input="markChanged" placeholder="Enter your last name" class="bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus-visible:ring-indigo-500/50" />
                <p v-if="validationErrors.last_name" class="text-xs text-red-400">{{ validationErrors.last_name[0] }}</p>
              </div>
              <div class="space-y-2">
                <Label class="text-slate-300">Email Address</Label>
                <Input type="email" v-model="user.email" @input="markChanged" placeholder="Enter your email" class="bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus-visible:ring-indigo-500/50" />
                <p v-if="validationErrors.email" class="text-xs text-red-400">{{ validationErrors.email[0] }}</p>
              </div>
              <div class="space-y-2">
                <Label class="text-slate-300">Phone Number</Label>
                <Input type="tel" v-model="user.phone" @input="markChanged" placeholder="Enter your phone number" class="bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus-visible:ring-indigo-500/50" />
                <p v-if="validationErrors.phone" class="text-xs text-red-400">{{ validationErrors.phone[0] }}</p>
              </div>
              <div class="space-y-2 md:col-span-2">
                <Label class="text-slate-300">Address</Label>
                <Textarea v-model="user.address" @input="markChanged" placeholder="Enter your complete address" rows="3" class="bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus-visible:ring-indigo-500/50" />
                <p v-if="validationErrors.address" class="text-xs text-red-400">{{ validationErrors.address[0] }}</p>
              </div>
            </div>
            <div class="flex justify-end gap-3 pt-4">
              <Button type="button" variant="outline" @click="resetPersonalAndContactInfo" :disabled="loading" class="border-slate-600 text-slate-300 hover:bg-slate-800 hover:text-white">Cancel</Button>
              <Button type="submit" :disabled="!hasChanges || loading" class="bg-indigo-600 hover:bg-indigo-700 text-white">{{ loading ? 'Updating...' : 'Update Information' }}</Button>
            </div>
          </form>
        </CardContent>
      </Card>

      <Card class="col-span-1 bg-slate-800/40 border-slate-700/30 backdrop-blur-sm">
        <CardHeader>
          <CardTitle class="flex items-center gap-2 text-white">
            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            Change Password
          </CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="changePassword" class="space-y-4">
            <div v-if="passwordError" class="p-3 bg-red-500/10 border border-red-500/20 rounded-lg text-xs text-red-400">
              {{ passwordError }}
            </div>

            <div class="space-y-2">
              <Label class="text-slate-300">Current Password</Label>
              <div class="relative">
                <Input 
                  :type="showCurrentPassword ? 'text' : 'password'"
                  v-model="password.current"
                  placeholder="Enter current password"
                  class="bg-slate-900/50 border-slate-700 text-slate-100 pr-10 focus-visible:ring-indigo-500/50"
                />
                <button type="button" @click="togglePasswordVisibility('current')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                  <svg v-if="showCurrentPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </button>
              </div>
            </div>

            <div class="space-y-2">
              <Label class="text-slate-300">New Password</Label>
              <div class="relative">
                <Input 
                  :type="showNewPassword ? 'text' : 'password'"
                  v-model="password.new"
                  placeholder="Enter new password"
                  class="bg-slate-900/50 border-slate-700 text-slate-100 pr-10 focus-visible:ring-indigo-500/50"
                />
                <button type="button" @click="togglePasswordVisibility('new')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                  <svg v-if="showNewPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </button>
              </div>
              <div class="flex gap-1 h-1 mt-2">
                <div v-for="n in 5" :key="n" class="flex-1 rounded-full transition-all duration-300" 
                     :class="getStrengthColor(n)"></div>
              </div>
              <p class="text-xs text-right text-slate-400 mt-1">{{ passwordStrengthLabel }}</p>
            </div>

            <div class="space-y-2">
              <Label class="text-slate-300">Confirm Password</Label>
              <div class="relative">
                <Input 
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="password.confirm"
                  placeholder="Confirm new password"
                  class="bg-slate-900/50 border-slate-700 text-slate-100 pr-10 focus-visible:ring-indigo-500/50"
                  :class="{ 'border-red-500/50': passwordMismatch }"
                />
                <button type="button" @click="togglePasswordVisibility('confirm')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300">
                  <svg v-if="showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </button>
              </div>
              <p v-if="passwordMismatch" class="text-xs text-red-400">Passwords do not match</p>
            </div>

            <Button type="submit" :disabled="!canChangePassword || passwordLoading" class="w-full bg-slate-700 hover:bg-slate-600 text-white">
              {{ passwordLoading ? 'Changing...' : 'Change Password' }}
            </Button>
          </form>
        </CardContent>
      </Card>

      <Card class="col-span-1 lg:col-span-3 bg-slate-800/40 border-slate-700/30 backdrop-blur-sm mt-2">
        <CardHeader class="flex flex-col md:flex-row items-start md:items-center justify-between pb-6 border-b border-slate-700/30">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-500/10 rounded-lg">
              <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <CardTitle class="text-white text-xl">Identity Verification</CardTitle>
              <CardDescription class="text-slate-400">Complete verification to unlock all features</CardDescription>
            </div>
          </div>
          <Badge :class="getStatusBadgeClass(idVerification.status)" class="mt-4 md:mt-0 px-3 py-1 text-xs uppercase tracking-wider">
            {{ formatVerificationStatus(idVerification.status) }}
          </Badge>
        </CardHeader>
        
        <CardContent class="p-0">
          <div class="p-6 bg-slate-900/30">
            <div class="flex justify-between mb-4 relative">
              <div class="absolute top-1/2 left-0 w-full h-0.5 bg-slate-700/50 -z-10 -translate-y-1/2"></div>
              
              <div v-for="(step, index) in wizardSteps" :key="index" 
                   class="flex flex-col items-center gap-2 cursor-pointer group"
                   @click="goToStep(index)">
                <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300 z-10 bg-slate-900"
                     :class="[
                       currentStep === index ? 'border-indigo-500 text-indigo-400 shadow-[0_0_10px_rgba(99,102,241,0.3)]' : 
                       step.completed ? 'border-emerald-500 text-emerald-500 bg-emerald-500/10' : 
                       'border-slate-600 text-slate-500'
                     ]">
                  <svg v-if="step.completed" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                  <span v-else class="text-xs font-bold">{{ index + 1 }}</span>
                </div>
                <span class="text-xs font-medium hidden md:block" 
                      :class="currentStep === index ? 'text-indigo-400' : step.completed ? 'text-emerald-500' : 'text-slate-500'">
                  {{ step.title }}
                </span>
              </div>
            </div>
            <Progress :model-value="wizardProgress" class="h-1 bg-slate-700/50" />
          </div>

          <div class="p-6 md:p-8 min-h-[400px]">
            
            <div v-if="currentStep === 0" class="animate-in fade-in slide-in-from-right-4 duration-300">
              <h3 class="text-lg font-semibold text-white mb-1 text-center">Choose Your ID Type</h3>
              <p class="text-slate-400 text-sm text-center mb-6">Select the government-issued ID you want to use</p>
              
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <div v-for="idType in availableIdTypes" :key="idType.value"
                     @click="selectIdType(idType)"
                     class="relative p-4 rounded-xl border transition-all cursor-pointer hover:shadow-lg group"
                     :class="[
                       idVerification.idType === idType.value 
                         ? 'bg-indigo-500/10 border-indigo-500/50 ring-1 ring-indigo-500/50' 
                         : 'bg-slate-800/50 border-slate-700/50 hover:border-indigo-500/30 hover:bg-slate-800'
                     ]">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center mb-3 text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path :d="idType.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
                    </svg>
                  </div>
                  <h4 class="font-medium text-white mb-1">{{ idType.name }}</h4>
                  <p class="text-xs text-slate-400">{{ idType.description }}</p>
                  
                  <div v-if="idVerification.idType === idType.value" class="absolute top-3 right-3 w-5 h-5 bg-emerald-500 rounded-full flex items-center justify-center text-white">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="currentStep === 1" class="max-w-md mx-auto animate-in fade-in slide-in-from-right-4 duration-300">
              <h3 class="text-lg font-semibold text-white mb-1 text-center">Enter ID Details</h3>
              <p class="text-slate-400 text-sm text-center mb-6">Provide your {{ selectedIdTypeName }} information</p>
              
              <div class="space-y-4">
                <div class="space-y-2">
                  <Label class="text-slate-300">ID Number</Label>
                  <Input 
                    v-model="idVerification.idNumber" 
                    @input="validateIdNumber"
                    :placeholder="`Enter your ${selectedIdTypeName} number`"
                    class="bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus-visible:ring-indigo-500/50"
                    :class="{ 'border-red-500/50': idVerification.errors.idNumber }"
                  />
                  <p v-if="idVerification.errors.idNumber" class="text-xs text-red-400">{{ idVerification.errors.idNumber }}</p>
                  <p class="text-xs text-slate-500">Enter exactly as shown on your ID</p>
                </div>

                <div class="p-4 bg-slate-800/50 rounded-xl border border-slate-700/50 mt-6">
                  <div class="flex items-center gap-2 mb-3 text-indigo-400 font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Example Format
                  </div>
                  <div class="font-mono text-lg text-white bg-slate-900/50 p-2 rounded border border-slate-700/30 text-center tracking-wider">
                    XXX-XXXX-XXXX
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="currentStep === 2" class="max-w-xl mx-auto animate-in fade-in slide-in-from-right-4 duration-300">
              <h3 class="text-lg font-semibold text-white mb-1 text-center">Upload ID Photo</h3>
              <p class="text-slate-400 text-sm text-center mb-6">Take a clear photo of your {{ selectedIdTypeName }}</p>

              <div 
                class="border-2 border-dashed rounded-xl p-8 text-center transition-all cursor-pointer relative"
                :class="[
                  idVerification.isDragging ? 'border-emerald-500 bg-emerald-500/10' : 'border-slate-700 bg-slate-800/30 hover:border-indigo-500/50 hover:bg-slate-800/50',
                  idVerification.idPhotoPreview ? 'border-solid border-slate-600' : ''
                ]"
                @dragover.prevent="handleDragOver"
                @dragleave.prevent="handleDragLeave"
                @drop.prevent="handleFileDrop"
                @click="triggerFileInput"
              >
                <input type="file" ref="fileInput" @change="handleFileSelect" accept=".jpg,.jpeg,.png,.pdf" hidden>
                
                <div v-if="!idVerification.idPhotoPreview" class="flex flex-col items-center">
                  <div class="w-16 h-16 rounded-full bg-indigo-500/10 flex items-center justify-center mb-4 text-indigo-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 11l3-3m0 0l3 3m-3-3v12" />
                    </svg>
                  </div>
                  <h5 class="text-lg font-medium text-white mb-1">Drag & Drop photo here</h5>
                  <p class="text-slate-400 text-sm mb-2">or click to browse</p>
                  <p class="text-xs text-slate-500">JPG, PNG, PDF • Max 5MB</p>
                </div>

                <div v-else class="relative group">
                  <img v-if="!idVerification.idPhotoPreview.startsWith('data:image/svg')" :src="idVerification.idPhotoPreview" class="max-h-64 mx-auto rounded-lg shadow-lg" />
                  <div v-else class="h-48 flex items-center justify-center bg-slate-700/50 rounded-lg text-slate-300">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                  </div>
                  
                  <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 rounded-lg">
                    <Button size="sm" variant="secondary" @click.stop="triggerFileInput">Change</Button>
                    <Button size="sm" variant="destructive" @click.stop="removeIdPhoto">Remove</Button>
                  </div>
                </div>
              </div>
              
              <div class="mt-6 p-4 bg-indigo-500/10 border border-indigo-500/20 rounded-xl">
                <h5 class="text-sm font-semibold text-indigo-400 mb-2 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  Requirements
                </h5>
                <ul class="grid grid-cols-2 gap-2 text-xs text-slate-300">
                  <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Clear text</li>
                  <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Four corners visible</li>
                  <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> No glare</li>
                  <li class="flex items-center gap-1.5"><svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Max 5MB</li>
                </ul>
              </div>
            </div>

            <div v-else-if="currentStep === 3" class="max-w-md mx-auto animate-in fade-in slide-in-from-right-4 duration-300">
              <h3 class="text-lg font-semibold text-white mb-1 text-center">Review & Submit</h3>
              <p class="text-slate-400 text-sm text-center mb-6">Review your information before submission</p>
              
              <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden">
                <div class="p-4 border-b border-slate-700/50 flex items-center gap-2 text-indigo-400 font-medium">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                  Summary
                </div>
                <div class="p-4 space-y-4">
                  <div class="flex justify-between items-center py-2 border-b border-slate-700/30">
                    <span class="text-slate-400 text-sm">ID Type</span>
                    <span class="text-white font-medium">{{ selectedIdTypeName }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2 border-b border-slate-700/30">
                    <span class="text-slate-400 text-sm">ID Number</span>
                    <span class="text-white font-medium font-mono">{{ idVerification.idNumber }}</span>
                  </div>
                  <div class="flex justify-between items-center py-2">
                    <span class="text-slate-400 text-sm">File</span>
                    <span class="text-emerald-400 text-sm flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                      {{ idVerification.idPhoto?.name || 'Uploaded' }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="mt-4 p-4 bg-amber-500/10 border border-amber-500/20 rounded-xl text-amber-400 text-xs leading-relaxed">
                <p class="font-bold mb-1 flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Important</p>
                Once submitted, you cannot edit this information until the review is complete (1-2 business days). Data is encrypted securely.
              </div>
            </div>

            <div v-else-if="currentStep === 4" class="max-w-md mx-auto text-center py-8 animate-in fade-in zoom-in-95 duration-300">
              <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6"
                   :class="{
                     'bg-amber-500/10 text-amber-500': idVerification.status === 'pending',
                     'bg-emerald-500/10 text-emerald-500': idVerification.status === 'approved' || idVerification.status === 'verified',
                     'bg-red-500/10 text-red-500': idVerification.status === 'rejected',
                     'bg-slate-700/30 text-slate-400': idVerification.status === 'not_submitted'
                   }">
                <svg v-if="idVerification.status === 'pending'" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <svg v-else-if="idVerification.status === 'approved' || idVerification.status === 'verified'" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <svg v-else-if="idVerification.status === 'rejected'" class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                <svg v-else class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
              
              <h3 class="text-2xl font-bold text-white mb-2">
                {{ getStatusTitle(idVerification.status) }}
              </h3>
              
              <p class="text-slate-400 mb-8">
                {{ getStatusMessage(idVerification.status) }}
              </p>
              
              <div v-if="idVerification.status !== 'not_submitted'" class="bg-slate-800/50 rounded-xl p-4 text-left border border-slate-700/50">
                <div class="flex justify-between py-2 border-b border-slate-700/30">
                  <span class="text-slate-500 text-sm">Submitted On</span>
                  <span class="text-slate-300 font-medium">{{ formatDateTime(idVerification.submittedAt || new Date()) }}</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="text-slate-500 text-sm">Reference ID</span>
                  <span class="text-indigo-400 font-mono font-medium">VER-{{ Math.random().toString(36).substr(2, 9).toUpperCase() }}</span>
                </div>
              </div>
            </div>

          </div>

          <div class="p-6 bg-slate-900/30 border-t border-slate-700/30 flex justify-between items-center">
            <Button 
              variant="ghost" 
              @click="previousStep" 
              :disabled="currentStep === 0 || ['pending', 'approved', 'verified'].includes(idVerification.status)"
              class="text-slate-400 hover:text-white"
            >
              Previous
            </Button>
            
            <div v-if="currentStep < 4" class="flex gap-1">
              <span class="text-indigo-400 font-bold">{{ currentStep + 1 }}</span>
              <span class="text-slate-600">/</span>
              <span class="text-slate-500">5</span>
            </div>

            <Button 
              v-if="currentStep < wizardSteps.length - 2" 
              @click="nextStep"
              :disabled="!canProceedToNextStep || ['pending', 'approved', 'verified'].includes(idVerification.status)"
              class="bg-indigo-600 hover:bg-indigo-700 text-white"
            >
              Next Step
            </Button>
            
            <Button 
              v-else-if="currentStep === wizardSteps.length - 2 && idVerification.status === 'not_submitted'" 
              @click="submitIdVerification"
              :disabled="!canSubmitId || idVerification.loading"
              class="bg-emerald-600 hover:bg-emerald-700 text-white"
            >
              {{ idVerification.loading ? 'Submitting...' : 'Submit Verification' }}
            </Button>
            
            <Button 
              v-else-if="currentStep === 4 && !['pending', 'approved', 'verified'].includes(idVerification.status)"
              @click="restartVerification"
              variant="outline"
              class="border-indigo-500/50 text-indigo-400 hover:bg-indigo-500/10"
            >
              Restart
            </Button>
            
            <div v-else class="w-20"></div> </div>
        </CardContent>
      </Card>

    </div>

    <Toaster />
  </div>
</template>

<script>
import { getCurrentUser, clearAuthData } from '@/utils/auth'
import axios from '@/utils/axios'
// Shadcn Components
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Separator } from '@/components/ui/separator'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'

export default {
  name: 'ClientProfile',
  components: {
    Card, CardContent, CardHeader, CardTitle, CardDescription,
    Button,
    Input,
    Label,
    Textarea,
    Badge,
    Progress,
    Separator,
    Toaster
  },
  data() {
    return {
      hasChanges: false,
      loading: false,
      initialLoading: true,
      passwordLoading: false,
      error: null,
      passwordError: null,
      validationErrors: {},
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      user: {
        id: null,
        first_name: '',
        last_name: '',
        full_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: '',
        created_at: ''
      },
      originalUser: null,
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      stats: {
        colorSelections: 0,
        serviceRequests: 0,
        activeProjects: 0
      },
      
      // Wizard Configuration
      currentStep: 0,
      wizardSteps: [
        { title: 'Choose ID', completed: false, enabled: true },
        { title: 'Details', completed: false, enabled: false },
        { title: 'Photo', completed: false, enabled: false },
        { title: 'Review', completed: false, enabled: false },
        { title: 'Status', completed: false, enabled: false }
      ],
      
      // Available ID Types with icons
      availableIdTypes: [
        { 
          value: 'philid', 
          name: 'Philippine National ID', 
          description: 'PhilID or ePhilID',
          icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        },
        { 
          value: 'passport', 
          name: 'Passport', 
          description: 'International passport',
          icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
        },
        { 
          value: 'driver_license', 
          name: 'Driver\'s License', 
          description: 'LTO issued license',
          icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'umid', 
          name: 'UMID Card', 
          description: 'SSS/GSIS unified ID',
          icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        },
        { 
          value: 'prc', 
          name: 'PRC ID', 
          description: 'Professional Regulation',
          icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        },
        { 
          value: 'voter', 
          name: 'Voter\'s ID', 
          description: 'Comelec issued ID',
          icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'postal', 
          name: 'Postal ID', 
          description: 'Philippine Postal ID',
          icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
        },
        { 
          value: 'philhealth', 
          name: 'PhilHealth ID', 
          description: 'Health insurance card',
          icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'nbi', 
          name: 'NBI Clearance', 
          description: 'National Bureau ID',
          icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
        },
        { 
          value: 'senior_citizen', 
          name: 'Senior Citizen ID', 
          description: 'OSCA issued ID',
          icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'other', 
          name: 'Other Government ID', 
          description: 'Other valid government ID',
          icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        }
      ],
      
      // ID Verification Data
      idVerification: {
        idType: '',
        idNumber: '',
        idPhoto: null,
        idPhotoPreview: '',
        status: 'not_submitted',
        loading: false,
        error: null,
        errors: {},
        isDragging: false,
        originalData: null,
        submittedAt: null
      }
    }
  },
  computed: {
    // Password related computed properties
    passwordStrength() {
      const password = this.password.new
      if (!password) return 'none'
      if (password.length < 6) return 'weak'
      if (password.length < 8) return 'medium'
      if (password.length < 10) return 'strong'
      if (this.hasSpecialChars(password) && this.hasNumbers(password) && this.hasUpperCase(password)) {
        return 'very-strong'
      }
      return 'strong'
    },
    
    passwordStrengthLabel() {
      const labels = {
        'none': '',
        'weak': 'Weak',
        'medium': 'Medium',
        'strong': 'Strong',
        'very-strong': 'Very Strong'
      }
      return labels[this.passwordStrength]
    },
    
    passwordMismatch() {
      return this.password.new && this.password.confirm && this.password.new !== this.password.confirm
    },
    
    canChangePassword() {
      return this.password.current && 
             this.password.new && 
             this.password.confirm && 
             !this.passwordMismatch &&
             this.password.new.length >= 8
    },
    
    // Wizard related computed properties
    wizardProgress() {
      // Calculate progress based on step index (0-4)
      return (this.currentStep / (this.wizardSteps.length - 1)) * 100
    },
    
    selectedIdTypeName() {
      const selected = this.availableIdTypes.find(type => type.value === this.idVerification.idType)
      return selected ? selected.name : 'ID'
    },
    
    canProceedToNextStep() {
      switch(this.currentStep) {
        case 0:
          return !!this.idVerification.idType
        case 1:
          return !!this.idVerification.idNumber && this.idVerification.idNumber.trim().length > 5
        case 2:
          return !!this.idVerification.idPhoto
        case 3:
          return true // Review step always accessible
        default:
          return true
      }
    },
    
    canSubmitId() {
      return this.idVerification.idType &&
            this.idVerification.idNumber &&
            this.idVerification.idPhoto &&
            this.idVerification.status === 'not_submitted'
    }
  },
  watch: {
    'idVerification.idType': function(newVal) {
      if (newVal) {
        this.wizardSteps[0].completed = true
        this.wizardSteps[1].enabled = true
      }
    },
    'idVerification.idNumber': function(newVal) {
      if (newVal && newVal.trim().length > 5) {
        this.wizardSteps[1].completed = true
        this.wizardSteps[2].enabled = true
      }
    },
    'idVerification.idPhoto': function(newVal) {
      if (newVal) {
        this.wizardSteps[2].completed = true
        this.wizardSteps[3].enabled = true
        this.wizardSteps[4].enabled = true
      }
    },
    currentStep: function(newStep) {
      this.wizardSteps.forEach((step, index) => {
        if (index < newStep) {
          step.completed = true
        }
      })
    }
  },
  created() {
    this.fetchUserProfile()
  },
  methods: {
    // Styling Helpers
    getStrengthColor(barIndex) {
      const strength = this.passwordStrength
      const map = {
        'weak': 2,
        'medium': 3,
        'strong': 4,
        'very-strong': 5
      }
      const score = map[strength] || 0
      
      if (barIndex > score) return 'bg-slate-700'
      
      if (score <= 2) return 'bg-red-500'
      if (score === 3) return 'bg-yellow-500'
      return 'bg-emerald-500'
    },

    formatVerificationStatus(status) {
      const map = {
        'verified': 'Verified',
        'approved': 'Approved',
        'pending': 'Pending Review',
        'rejected': 'Rejected',
        'not_submitted': 'Not Submitted'
      }
      return map[status] || status
    },

    getStatusBadgeClass(status) {
      if (status === 'verified' || status === 'approved') return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
      if (status === 'pending') return 'bg-amber-500/20 text-amber-400 border-amber-500/30'
      if (status === 'rejected') return 'bg-red-500/20 text-red-400 border-red-500/30'
      return 'bg-slate-700/50 text-slate-400 border-slate-600'
    },

    getStatusTitle(status) {
      if (status === 'pending') return 'Verification Submitted!'
      if (status === 'approved' || status === 'verified') return 'Verification Complete!'
      if (status === 'rejected') return 'Verification Rejected'
      return 'Ready to Submit!'
    },

    getStatusMessage(status) {
      if (status === 'pending') return 'Your ID verification has been submitted and is under review.'
      if (status === 'approved' || status === 'verified') return 'Your identity has been verified. You have full access.'
      if (status === 'rejected') return 'Your verification was rejected. Please try again with a clearer photo.'
      return 'All information entered. Ready to submit.'
    },

    // Wizard Navigation Methods
    goToStep(stepIndex) {
      if (this.wizardSteps[stepIndex].enabled && 
          this.idVerification.status !== 'pending' && 
          this.idVerification.status !== 'approved') {
        this.currentStep = stepIndex
      }
    },
    
    nextStep() {
      if (this.canProceedToNextStep && this.currentStep < this.wizardSteps.length - 1) {
        this.currentStep++
      }
    },
    
    previousStep() {
      if (this.currentStep > 0) {
        this.currentStep--
      }
    },
    
    restartVerification() {
      if (this.idVerification.status !== 'pending') {
        this.currentStep = 0
        this.idVerification.idType = ''
        this.idVerification.idNumber = ''
        this.idVerification.idPhoto = null
        this.idVerification.idPhotoPreview = ''
        this.idVerification.errors = {}
        this.idVerification.error = null
        this.idVerification.status = 'not_submitted'
        
        this.wizardSteps.forEach((step, index) => {
          step.completed = false
          step.enabled = index === 0
        })
        
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = ''
        }
        
        this.showNotification('Verification restarted', 'info')
      }
    },
    
    selectIdType(idType) {
      if (this.idVerification.status === 'pending' || this.idVerification.status === 'approved') return
      
      this.idVerification.idType = idType.value
      this.idVerification.errors.idType = null
      
      setTimeout(() => {
        if (this.canProceedToNextStep) {
          this.nextStep()
        }
      }, 300)
    },
    
    validateIdNumber() {
      this.idVerification.errors.idNumber = null
      if (!this.idVerification.idNumber || this.idVerification.idNumber.trim().length < 6) {
        this.idVerification.errors.idNumber = 'ID number must be at least 6 characters'
      }
    },
    
    // Notification System (Replaced with Sonner)
    showNotification(message, type = 'info') {
      const options = {
        className: 'bg-slate-800 border-slate-700 text-white',
        descriptionClass: 'text-slate-400'
      }
      
      if (type === 'success') {
        toast.success(message, { ...options, className: 'bg-emerald-900/90 border-emerald-700 text-emerald-50' })
      } else if (type === 'error') {
        toast.error(message, { ...options, className: 'bg-red-900/90 border-red-700 text-red-50' })
      } else if (type === 'warning') {
        toast.warning(message, { ...options, className: 'bg-amber-900/90 border-amber-700 text-amber-50' })
      } else {
        toast.info(message, { ...options })
      }
    },
    
    async fetchUserProfile() {
      this.initialLoading = true
      this.error = null
      try {
        const authUser = await getCurrentUser()
        if (!authUser) throw new Error('Please login to view your profile')

        const response = await axios.get('/auth/me')
        
        if (response.data.status === 'success') {
          this.user = response.data.user
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          await this.loadIdVerificationData()
          this.showNotification('Profile loaded successfully!', 'success')
        } else {
          throw new Error('Failed to load profile data')
        }
      } catch (error) {
        console.error('Error fetching profile:', error)
        this.error = error.response?.data?.message || error.message || 'Failed to load profile data'
        
        if (error.response?.status === 401) {
          clearAuthData()
          this.$router.push('/Landing/logIn')
          this.showNotification('Session expired. Please login again.', 'error')
        } else {
          this.showNotification(this.error, 'error')
        }
      } finally {
        this.initialLoading = false
      }
    },
    
    async loadIdVerificationData() {
      try {
        const response = await axios.get('/client/requirements')
        
        if (response.data.status === 'success') {
          if (response.data.id_verification) {
            const data = response.data.id_verification
            this.idVerification.idType = data.id_type || ''
            this.idVerification.idNumber = data.id_number || ''
            this.idVerification.status = data.status || 'not_submitted'
            this.idVerification.submittedAt = data.submitted_at || null
            
            if (data.id_photo_url) {
              this.idVerification.idPhotoPreview = data.id_photo_url
            }
            
            this.updateWizardFromStatus()
          }
        }
      } catch (error) {
        console.error('Error loading ID verification data:', error)
        this.showNotification('Failed to load verification data', 'error')
      }
    },
    
    updateWizardFromStatus() {
      if (['pending', 'approved', 'verified'].includes(this.idVerification.status)) {
        this.wizardSteps.forEach(step => {
          step.completed = true
          step.enabled = true
        })
        this.currentStep = 4
      } else if (this.idVerification.status === 'rejected') {
        this.currentStep = 4
      }
    },
    
    // File Upload Methods
    handleDragOver(event) {
      event.preventDefault()
      this.idVerification.isDragging = true
    },
    
    handleDragLeave(event) {
      event.preventDefault()
      this.idVerification.isDragging = false
    },
    
    handleFileDrop(event) {
      this.idVerification.isDragging = false
      const files = event.dataTransfer.files
      if (files.length > 0) {
        this.validateAndSetFile(files[0])
      }
    },
    
    triggerFileInput() {
      if (this.idVerification.status === 'pending' || this.idVerification.status === 'approved') return
      this.$refs.fileInput.click()
    },
    
    handleFileSelect(event) {
      const file = event.target.files[0]
      if (file) {
        this.validateAndSetFile(file)
      }
    },
    
    validateAndSetFile(file) {
      this.idVerification.errors.idPhoto = null
      
      const maxSize = 5 * 1024 * 1024
      if (file.size > maxSize) {
        this.idVerification.errors.idPhoto = 'File size must be less than 5MB'
        this.showNotification('File too large. Maximum size is 5MB.', 'error')
        return
      }
      
      const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']
      if (!validTypes.includes(file.type)) {
        this.idVerification.errors.idPhoto = 'File must be JPG, PNG, or PDF'
        this.showNotification('Invalid file type.', 'error')
        return
      }
      
      this.idVerification.idPhoto = file
      
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          this.idVerification.idPhotoPreview = e.target.result
          setTimeout(() => {
            if (this.canProceedToNextStep) this.nextStep()
          }, 300)
        }
        reader.readAsDataURL(file)
      } else {
        // Generic preview for PDF
        this.idVerification.idPhotoPreview = 'data:image/svg+xml;base64,...' // (truncated for brevity, keep your original string)
        setTimeout(() => { if (this.canProceedToNextStep) this.nextStep() }, 300)
      }
      
      this.showNotification('File uploaded successfully!', 'success')
    },
    
    removeIdPhoto() {
      this.idVerification.idPhoto = null
      this.idVerification.idPhotoPreview = ''
      this.idVerification.errors.idPhoto = null
      if (this.$refs.fileInput) this.$refs.fileInput.value = ''
      
      this.wizardSteps[2].completed = false
      this.showNotification('File removed', 'info')
    },
    
    async submitIdVerification() {
      if (!this.canSubmitId || this.idVerification.loading) return
      
      this.idVerification.loading = true
      this.idVerification.error = null
      this.idVerification.errors = {}
      
      try {
        const formData = new FormData()
        formData.append('id_type', this.idVerification.idType)
        formData.append('id_number', this.idVerification.idNumber)
        formData.append('id_photo', this.idVerification.idPhoto)
        
        const response = await axios.post('/client/requirements', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
          timeout: 30000
        })
        
        if (response.data.status === 'success') {
          this.idVerification.status = response.data.id_verification.status
          this.idVerification.submittedAt = response.data.id_verification.submitted_at
          
          this.wizardSteps[3].completed = true
          this.wizardSteps[4].completed = true
          this.currentStep = 4
          
          this.showNotification(response.data.message || 'ID verification submitted!', 'success')
        } else {
          throw new Error(response.data.message || 'Failed to submit')
        }
      } catch (error) {
        console.error('Error submitting ID verification:', error)
        if (error.response?.status === 422) {
          this.idVerification.errors = error.response.data.errors || {}
          this.showNotification('Please fix validation errors', 'error')
        } else {
          this.showNotification(error.message || 'Failed to submit', 'error')
        }
      } finally {
        this.idVerification.loading = false
      }
    },
    
    markChanged() {
      if (!this.originalUser) return
      const current = JSON.stringify({
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        phone: this.user.phone,
        address: this.user.address
      })
      const original = JSON.stringify({
        first_name: this.originalUser.first_name,
        last_name: this.originalUser.last_name,
        email: this.originalUser.email,
        phone: this.originalUser.phone,
        address: this.originalUser.address
      })
      this.hasChanges = current !== original
    },
    
    async saveProfile() {
      if (!this.hasChanges || this.loading) return
      
      this.loading = true
      this.validationErrors = {}
      
      try {
        const updateData = {
          first_name: this.user.first_name,
          last_name: this.user.last_name,
          email: this.user.email,
          phone: this.user.phone,
          address: this.user.address
        }
        
        const response = await axios.put('/profile', updateData)
        
        if (response.data.status === 'success') {
          Object.assign(this.user, updateData)
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          this.showNotification('Profile updated successfully!', 'success')
        } else {
          throw new Error('Update failed')
        }
      } catch (error) {
        console.error('Error updating profile:', error)
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.showNotification('Please fix the validation errors', 'error')
        } else {
          this.showNotification('Failed to update profile', 'error')
        }
      } finally {
        this.loading = false
      }
    },
    
    resetPersonalAndContactInfo() {
      if (this.originalUser) {
        this.user = JSON.parse(JSON.stringify(this.originalUser))
        this.hasChanges = false
        this.validationErrors = {}
        this.showNotification('Changes discarded', 'info')
      }
    },
    
    updatePersonalAndContactInfo() {
      this.saveProfile()
    },
    
    togglePasswordVisibility(type) {
      if (type === 'current') this.showCurrentPassword = !this.showCurrentPassword
      if (type === 'new') this.showNewPassword = !this.showNewPassword
      if (type === 'confirm') this.showConfirmPassword = !this.showConfirmPassword
    },
    
    async changePassword() {
      if (!this.canChangePassword || this.passwordLoading) return
      
      this.passwordLoading = true
      this.passwordError = null
      
      try {
        const passwordData = {
          current_password: this.password.current,
          password: this.password.new,
          password_confirmation: this.password.confirm
        }
        
        const response = await axios.put('/profile/password', passwordData)
        
        if (response.data.status === 'success') {
          this.password = { current: '', new: '', confirm: '' }
          this.showNotification('Password changed successfully!', 'success')
        } else {
          throw new Error('Password change failed')
        }
      } catch (error) {
        this.passwordError = error.response?.data?.message || 'Failed to change password'
        this.showNotification(this.passwordError, 'error')
      } finally {
        this.passwordLoading = false
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      try {
        return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'long' })
      } catch (e) {
        return 'N/A'
      }
    },
    
    formatDateTime(dateTime) {
      if (!dateTime) return 'N/A'
      return new Date(dateTime).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
    },
    
    formatRole(role) {
      const roles = { 'client': 'Client Account', 'admin': 'Administrator' }
      return roles[role] || role
    },
    
    hasSpecialChars(password) { return /[^A-Za-z0-9]/.test(password) },
    hasNumbers(password) { return /[0-9]/.test(password) },
    hasUpperCase(password) { return /[A-Z]/.test(password) }
  }
}
</script>

<style scoped>
/* Only keeping essential transitions that are hard to replicate purely with utility classes */
.slide-in-from-right-4 {
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}

/* Custom scrollbar to match the theme */
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
}
::-webkit-scrollbar-thumb {
  background: #4f46e5;
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: #4338ca;
}
</style>