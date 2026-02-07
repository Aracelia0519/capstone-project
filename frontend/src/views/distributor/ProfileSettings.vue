<template>
  <div class="page-transition p-4 md:p-6 min-h-screen bg-gradient-to-br from-gray-50 to-blue-50">
    <div
      v-if="loading"
      class="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center"
    >
      <div class="text-center">
        <div class="relative inline-block">
          <div class="w-20 h-20 border-4 border-blue-100 rounded-full"></div>
          <div class="absolute top-0 left-0 animate-spin rounded-full h-20 w-20 border-t-4 border-b-0 border-r-0 border-l-0 border-blue-600 mb-4"></div>
        </div>
        <p class="text-gray-600 mt-4 font-medium">Loading profile data...</p>
      </div>
    </div>

    <Toaster position="top-right" />

    <div class="mb-8 md:mb-10">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg icon-hover">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              Profile & Settings
            </h1>
          </div>
          <p class="text-gray-500 smooth-color">Manage your account information and preferences</p>
        </div>
        
        <div class="flex items-center gap-3">
          <Button 
            @click="saveAllChanges" 
            :disabled="!hasUnsavedChanges || saving"
            class="h-auto px-6 py-3 rounded-xl transition-all duration-300 shadow-lg text-base"
            :class="[
              hasUnsavedChanges && !saving 
                ? 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-blue-500/25 btn-hover-effect' 
                : 'bg-gray-200 text-gray-500 hover:bg-gray-200 cursor-not-allowed',
              saving ? 'bg-gradient-to-r from-blue-400 to-blue-500 cursor-wait' : ''
            ]"
          >
            <Loader2 v-if="saving" class="w-5 h-5 mr-2 animate-spin" />
            <Save v-else class="w-5 h-5 mr-2 icon-hover" />
            {{ saving ? 'Saving...' : 'Save All Changes' }}
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
      
      <div class="lg:col-span-2 space-y-6 md:space-y-8">
        
        <Card class="card-shadow depth-hover border-gray-100 overflow-hidden rounded-2xl">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-lg shadow-md icon-hover">
                  <User class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Personal Information</h2>
              </div>
              <Badge variant="secondary" class="bg-blue-100 text-blue-600 hover:bg-blue-100 rounded-full px-3">Basic Info</Badge>
            </div>
          </div>

          <CardContent class="p-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200 p-5 mb-6">
              <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-6">
                <div class="flex-shrink-0">
                  <div class="relative">
                    <div class="avatar-hover w-24 h-24 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                      <span class="text-3xl font-bold text-white">{{ getInitials(userInfo.full_name) }}</span>
                    </div>
                    <button @click="changeProfilePhoto" class="absolute -bottom-2 -right-2 p-2 bg-white rounded-full border-2 border-white shadow-lg hover:bg-gray-50 transition-transform hover:scale-110">
                      <Camera class="w-4 h-4 text-gray-600 icon-hover" />
                    </button>
                  </div>
                </div>
                
                <div class="flex-1 space-y-2">
                  <div>
                    <h3 class="text-xl font-bold text-gray-900">{{ userInfo.full_name }}</h3>
                    <p class="text-gray-600">{{ capitalize(userInfo.role) }}</p>
                  </div>
                  
                  <div class="flex flex-wrap gap-2">
                    <Badge :class="[
                      'px-3 py-1.5 rounded-full text-sm font-medium shadow-sm border',
                      userInfo.status === 'active' 
                        ? 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 border-green-200 hover:bg-green-50'
                        : userInfo.status === 'pending' 
                          ? 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-700 border-yellow-200 hover:bg-yellow-50'
                          : 'bg-gradient-to-r from-red-50 to-rose-50 text-red-700 border-red-200 hover:bg-red-50'
                    ]">
                      <CheckCircle2 v-if="userInfo.status === 'active'" class="w-3 h-3 mr-1.5 icon-hover" />
                      <Clock v-if="userInfo.status === 'pending'" class="w-3 h-3 mr-1.5 icon-hover" />
                      <XCircle v-if="userInfo.status === 'inactive'" class="w-3 h-3 mr-1.5 icon-hover" />
                      {{ capitalize(userInfo.status) }}
                    </Badge>
                    
                    <Badge v-if="userInfo.role === 'distributor' && verificationData" 
                      :class="[
                        'px-3 py-1.5 rounded-full text-sm font-medium shadow-sm border',
                        verificationData.status === 'approved' 
                          ? 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 border-green-200 hover:bg-green-50'
                          : verificationData.status === 'pending' 
                            ? 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-700 border-yellow-200 hover:bg-yellow-50'
                            : verificationData.status === 'rejected' 
                              ? 'bg-gradient-to-r from-red-50 to-rose-50 text-red-700 border-red-200 hover:bg-red-50'
                              : 'bg-gradient-to-r from-gray-50 to-slate-50 text-gray-700 border-gray-200 hover:bg-gray-50'
                      ]">
                      <CheckCircle2 v-if="verificationData.status === 'approved'" class="w-3 h-3 mr-1.5 icon-hover" />
                      <Clock v-if="verificationData.status === 'pending'" class="w-3 h-3 mr-1.5 icon-hover" />
                      <AlertCircle v-if="verificationData.status === 'rejected'" class="w-3 h-3 mr-1.5 icon-hover" />
                      {{ verificationData.status ? capitalize(verificationData.status) : 'Not Submitted' }} Verification
                    </Badge>
                  </div>
                </div>
                
                <div class="text-right">
                  <p class="text-sm text-gray-500 mb-1 smooth-color">Member since</p>
                  <p class="font-semibold text-gray-900">{{ formatDate(userInfo.created_at) }}</p>
                </div>
              </div>
            </div>

            <div class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group space-y-2">
                  <Label class="smooth-color font-semibold text-gray-700">First Name</Label>
                  <div class="relative">
                    <User class="absolute left-3 top-3.5 w-5 h-5 text-gray-400 icon-hover pointer-events-none" />
                    <Input v-model="userInfo.first_name" type="text" class="pl-10 h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 form-input" />
                  </div>
                </div>
                
                <div class="form-group space-y-2">
                  <Label class="smooth-color font-semibold text-gray-700">Last Name</Label>
                  <div class="relative">
                    <User class="absolute left-3 top-3.5 w-5 h-5 text-gray-400 icon-hover pointer-events-none" />
                    <Input v-model="userInfo.last_name" type="text" class="pl-10 h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 form-input" />
                  </div>
                </div>
              </div>

              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">Email Address</Label>
                <div class="relative">
                  <Mail class="absolute left-3 top-3.5 w-5 h-5 text-gray-400 icon-hover pointer-events-none" />
                  <Input v-model="userInfo.email" type="email" class="pl-10 h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 form-input" />
                </div>
              </div>

              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">Phone Number</Label>
                <div class="relative">
                  <Phone class="absolute left-3 top-3.5 w-5 h-5 text-gray-400 icon-hover pointer-events-none" />
                  <Input v-model="userInfo.phone" type="tel" class="pl-10 h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 form-input" />
                </div>
              </div>

              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">Address</Label>
                <Textarea v-model="userInfo.address" rows="3" class="px-4 py-3 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 resize-none form-textarea" />
              </div>

              <div class="pt-6 border-t border-gray-200">
                <Button @click="saveUserInfo" 
                  :disabled="!userInfoChanged || saving"
                  class="h-auto px-6 py-3 rounded-xl transition-all duration-300 shadow-lg font-medium text-base w-full md:w-auto"
                  :class="[
                    userInfoChanged && !saving 
                      ? 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-blue-500/25 btn-hover-effect' 
                      : 'bg-gray-200 text-gray-500 hover:bg-gray-200 cursor-not-allowed',
                    saving ? 'bg-gradient-to-r from-blue-400 to-blue-500 cursor-wait' : ''
                  ]">
                  <Loader2 v-if="saving" class="w-5 h-5 mr-2 animate-spin" />
                  <Save v-else class="w-5 h-5 mr-2 icon-hover" />
                  {{ saving ? 'Saving...' : 'Update Personal Information' }}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card v-if="userInfo.role === 'distributor'" class="card-shadow depth-hover border-gray-100 overflow-hidden rounded-2xl">
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-green-500 to-emerald-500 rounded-lg shadow-md icon-hover">
                  <Briefcase class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Distributor Information</h2>
              </div>
              <Badge variant="secondary" class="bg-green-100 text-green-600 hover:bg-green-100 rounded-full px-3">Business Info</Badge>
            </div>
          </div>

          <CardContent class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">Company Name</Label>
                <Input v-model="distributorInfo.company_name" type="text" 
                  class="h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-green-500 focus-visible:border-green-500 transition-all duration-300 form-input"
                  :readonly="verificationData && verificationData.has_submitted" />
              </div>
              
              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">Business License Number</Label>
                <Input v-model="distributorInfo.business_registration_number" type="text" 
                  class="h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-green-500 focus-visible:border-green-500 transition-all duration-300 form-input"
                  :readonly="verificationData && verificationData.has_submitted" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">ID Type</Label>
                <Input v-model="distributorInfo.valid_id_type_display" type="text" 
                  class="h-12 bg-gray-100 border-gray-200 rounded-xl text-gray-600 cursor-not-allowed" readonly />
              </div>
              
              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">ID Number</Label>
                <Input v-model="distributorInfo.id_number" type="text" 
                  class="h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-green-500 focus-visible:border-green-500 transition-all duration-300 form-input"
                  :readonly="verificationData && verificationData.has_submitted" />
              </div>
            </div>

            <div v-if="verificationData && verificationData.has_submitted" 
              class="p-4 bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-blue-200">
              <div class="flex items-start">
                <div class="p-2 bg-blue-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                  <Lock class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <h4 class="font-semibold text-gray-800 mb-2">Information Locked</h4>
                  <p class="text-sm text-gray-600">
                    Your distributor information is currently locked because you have submitted business verification. 
                    To make changes, please contact admin support or wait for your verification to be processed.
                  </p>
                </div>
              </div>
            </div>

            <div class="pt-6 border-t border-gray-200">
              <Button @click="saveDistributorInfo" 
                :disabled="!distributorInfoChanged || savingDistributor || (verificationData && verificationData.has_submitted)"
                class="h-auto px-6 py-3 rounded-xl transition-all duration-300 shadow-lg font-medium text-base w-full md:w-auto"
                :class="[
                  distributorInfoChanged && !savingDistributor && (!verificationData || !verificationData.has_submitted)
                    ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-green-500/25 btn-hover-effect'
                    : 'bg-gray-200 text-gray-500 hover:bg-gray-200 cursor-not-allowed',
                  savingDistributor ? 'bg-gradient-to-r from-green-400 to-green-500 cursor-wait' : ''
                ]">
                <Loader2 v-if="savingDistributor" class="w-5 h-5 mr-2 animate-spin" />
                <Save v-else class="w-5 h-5 mr-2 icon-hover" />
                {{ savingDistributor ? 'Saving...' : 'Update Distributor Information' }}
              </Button>
            </div>
          </CardContent>
        </Card>

        <Card class="card-shadow depth-hover border-gray-100 overflow-hidden rounded-2xl">
          <div class="bg-gradient-to-r from-red-50 to-rose-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-red-500 to-rose-500 rounded-lg shadow-md icon-hover">
                  <ShieldCheck class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Security Settings</h2>
              </div>
              <Badge variant="secondary" class="bg-red-100 text-red-600 hover:bg-red-100 rounded-full px-3">Security</Badge>
            </div>
          </div>

          <CardContent class="p-6 space-y-6">
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border border-yellow-200 p-4">
              <div class="flex items-start">
                <div class="p-2 bg-yellow-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                  <AlertTriangle class="w-5 h-5 text-yellow-600" />
                </div>
                <div>
                  <h4 class="font-semibold text-yellow-800 mb-2">Security Recommendation</h4>
                  <p class="text-sm text-yellow-700">
                    Use a strong password with at least 8 characters including uppercase, lowercase, numbers, and special characters.
                  </p>
                </div>
              </div>
            </div>

            <div class="space-y-6">
              <div class="form-group space-y-2">
                <Label class="smooth-color font-semibold text-gray-700">Current Password</Label>
                <div class="relative">
                  <Lock class="absolute left-3 top-3.5 w-5 h-5 text-gray-400 icon-hover pointer-events-none" />
                  <Input v-model="password.current" :type="currentPasswordFieldType" class="pl-10 pr-12 h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 form-input" />
                  <button @click="toggleCurrentPasswordVisibility" type="button" class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                    <EyeOff v-if="showCurrentPassword" class="w-5 h-5" />
                    <Eye v-else class="w-5 h-5" />
                  </button>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div class="form-group space-y-2">
                    <Label class="smooth-color font-semibold text-gray-700">New Password</Label>
                    <div class="relative">
                      <Lock class="absolute left-3 top-3.5 w-5 h-5 text-gray-400 icon-hover pointer-events-none" />
                      <Input v-model="password.new" :type="newPasswordFieldType" class="pl-10 pr-12 h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 form-input" />
                      <button @click="toggleNewPasswordVisibility" type="button" class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <EyeOff v-if="showNewPassword" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                      </button>
                    </div>
                  </div>
                  
                  <div class="space-y-2">
                    <p class="smooth-color text-sm font-semibold text-gray-700">Password Requirements</p>
                    <div class="space-y-1.5">
                      <div v-for="requirement in passwordRequirements" :key="requirement.text" class="flex items-center">
                        <div class="flex-shrink-0 w-5 h-5 mr-2 flex items-center justify-center icon-hover">
                          <CheckCircle2 v-if="requirement.met" class="w-4 h-4 text-green-500" />
                          <div v-else class="w-4 h-4 rounded-full border border-gray-300"></div>
                        </div>
                        <span class="text-sm smooth-color" :class="requirement.met ? 'text-green-600' : 'text-gray-500'">
                          {{ requirement.text }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="form-group space-y-2">
                    <Label class="smooth-color font-semibold text-gray-700">Confirm New Password</Label>
                    <div class="relative">
                      <Lock class="absolute left-3 top-3.5 w-5 h-5 text-gray-400 icon-hover pointer-events-none" />
                      <Input v-model="password.confirm" :type="confirmPasswordFieldType" class="pl-10 pr-12 h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:ring-2 focus-visible:border-blue-500 transition-all duration-300 form-input" />
                      <button @click="toggleConfirmPasswordVisibility" type="button" class="password-toggle absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <EyeOff v-if="showConfirmPassword" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                      </button>
                    </div>
                  </div>
                  
                  <div class="space-y-2">
                    <p class="smooth-color text-sm font-semibold text-gray-700">Password Match</p>
                    <div class="flex items-center p-3 rounded-lg bg-gray-50 border border-gray-200 document-card">
                      <div class="flex-shrink-0 w-6 h-6 mr-3 flex items-center justify-center icon-hover">
                        <CheckCircle2 v-if="passwordMatch" class="w-5 h-5 text-green-500" />
                        <XCircle v-else-if="password.confirm" class="w-5 h-5 text-red-500" />
                        <div v-else class="w-4 h-4 rounded-full border-2 border-gray-300"></div>
                      </div>
                      <span class="text-sm font-medium smooth-color" :class="passwordMatch ? 'text-green-600' : password.confirm ? 'text-red-600' : 'text-gray-500'">
                        {{ password.new && password.confirm 
                          ? (passwordMatch ? 'Passwords match' : 'Passwords do not match') 
                          : 'Enter password to check' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="pt-6 border-t border-gray-200">
              <Button @click="changePassword" 
                :disabled="!canChangePassword || changingPassword"
                class="h-auto px-6 py-3 rounded-xl transition-all duration-300 shadow-lg font-medium text-base w-full touch-friendly"
                :class="[
                  canChangePassword && !changingPassword 
                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white shadow-blue-500/25 btn-hover-effect' 
                    : 'bg-gray-200 text-gray-500 hover:bg-gray-200 cursor-not-allowed',
                  changingPassword ? 'bg-gradient-to-r from-blue-400 to-blue-500 cursor-wait' : ''
                ]">
                <Loader2 v-if="changingPassword" class="w-5 h-5 mr-2 animate-spin" />
                <Key v-else class="w-5 h-5 mr-2 icon-hover" />
                {{ changingPassword ? 'Changing Password...' : 'Change Password' }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="space-y-6 md:space-y-8" v-if="userInfo.role === 'distributor'">
        <Card class="card-shadow depth-hover border-gray-100 overflow-hidden rounded-2xl">
          <div class="bg-gradient-to-r from-purple-50 to-indigo-50 px-6 py-4 border-b border-gray-200 card-header-hover">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-br from-purple-500 to-indigo-500 rounded-lg shadow-md icon-hover">
                  <ShieldCheck class="w-5 h-5 text-white" />
                </div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">Business Verification</h2>
              </div>
              <Badge variant="secondary" class="bg-purple-100 text-purple-600 hover:bg-purple-100 rounded-full px-3">Verification</Badge>
            </div>
          </div>

          <div v-if="verificationData && verificationData.has_submitted" class="p-6 border-b border-gray-200">
            <div :class="[
              'p-5 rounded-xl border shadow-sm',
              verificationData.status === 'approved' 
                ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-200 shadow-green-500/10'
                : verificationData.status === 'pending' 
                  ? 'bg-gradient-to-r from-yellow-50 to-amber-50 border-yellow-200 shadow-yellow-500/10'
                  : verificationData.status === 'rejected' 
                    ? 'bg-gradient-to-r from-red-50 to-rose-50 border-red-200 shadow-red-500/10'
                    : 'bg-gradient-to-r from-blue-50 to-cyan-50 border-blue-200 shadow-blue-500/10'
            ]">
              <div class="flex items-start">
                <div :class="[
                  'p-3 rounded-lg mr-4 flex-shrink-0 icon-hover',
                  verificationData.status === 'approved' ? 'bg-green-100' :
                  verificationData.status === 'pending' ? 'bg-yellow-100' :
                  verificationData.status === 'rejected' ? 'bg-red-100' :
                  'bg-blue-100'
                ]">
                  <CheckCircle2 v-if="verificationData.status === 'approved'" class="w-6 h-6 text-green-600" />
                  <Clock v-if="verificationData.status === 'pending'" class="w-6 h-6 text-yellow-600" />
                  <AlertCircle v-if="verificationData.status === 'rejected'" class="w-6 h-6 text-red-600" />
                  <Info v-if="!verificationData.status" class="w-6 h-6 text-blue-600" />
                </div>
                <div class="flex-1">
                  <h4 class="font-bold text-lg mb-2" 
                    :class="verificationData.status === 'approved' ? 'text-green-800' : 
                           verificationData.status === 'pending' ? 'text-yellow-800' : 
                           verificationData.status === 'rejected' ? 'text-red-800' : 
                           'text-blue-800'">
                    {{ getVerificationStatusTitle() }}
                  </h4>
                  <p class="text-sm mb-3" 
                    :class="verificationData.status === 'approved' ? 'text-green-700' : 
                           verificationData.status === 'pending' ? 'text-yellow-700' : 
                           verificationData.status === 'rejected' ? 'text-red-700' : 
                           'text-blue-700'">
                    {{ getVerificationStatusMessage() }}
                  </p>
                  <p v-if="verificationData.rejection_reason" 
                    class="text-sm p-3 bg-red-50 border border-red-200 rounded-lg text-red-700">
                    <strong class="font-semibold">Reason:</strong> {{ verificationData.rejection_reason }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <div v-if="!verificationData || !verificationData.has_submitted || verificationData.status === 'rejected'">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50">
              <div class="flex items-center justify-between relative">
                <div class="absolute top-4 left-0 right-0 h-0.5 bg-gray-200 z-0"></div>
                <div class="absolute top-4 left-0 h-0.5 bg-gradient-to-r from-blue-500 to-purple-600 z-0 transition-all duration-500 ease-out"
                  :style="{ width: getProgressWidth() }"></div>

                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 1 ? currentStep = 1 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 1 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span v-if="currentStep > 1" class="text-sm font-bold">✓</span>
                    <span v-else class="text-sm font-bold">1</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 1 ? 'text-gray-800' : 'text-gray-500'">Company</span>
                </div>

                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 2 ? currentStep = 2 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 2 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span v-if="currentStep > 2" class="text-sm font-bold">✓</span>
                    <span v-else class="text-sm font-bold">2</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 2 ? 'text-gray-800' : 'text-gray-500'">Valid ID</span>
                </div>

                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 3 ? currentStep = 3 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 3 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span v-if="currentStep > 3" class="text-sm font-bold">✓</span>
                    <span v-else class="text-sm font-bold">3</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 3 ? 'text-gray-800' : 'text-gray-500'">Documents</span>
                </div>

                <div class="nav-item flex flex-col items-center relative z-10" @click="currentStep >= 4 ? currentStep = 4 : null">
                  <div :class="[
                    'progress-step w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer',
                    currentStep >= 4 
                      ? 'bg-gradient-to-br from-blue-500 to-purple-600 border-blue-500 text-white shadow-lg shadow-blue-500/50' 
                      : 'bg-white border-gray-300 text-gray-400'
                  ]">
                    <span class="text-sm font-bold">4</span>
                  </div>
                  <span class="mt-2 text-xs font-semibold" 
                    :class="currentStep >= 4 ? 'text-gray-800' : 'text-gray-500'">Review</span>
                </div>
              </div>
            </div>

            <CardContent class="p-6">
              <div v-if="currentStep === 1" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <Building2 class="w-8 h-8 text-white" />
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Company Information</h3>
                  <p class="text-gray-600 mt-2">Let's start with your basic company details</p>
                </div>

                <div class="space-y-5">
                  <div class="form-group">
                    <Label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Company Name
                      <span class="text-red-500">*</span>
                    </Label>
                    <Input v-model="verificationForm.company_name" type="text" 
                      class="h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:border-blue-500 transition-all duration-300 form-input"
                      placeholder="Enter your company name"
                      @input="validateStep(1)" />
                  </div>

                  <div class="form-group">
                    <Label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Business Registration Number
                      <span class="text-red-500">*</span>
                    </Label>
                    <Input v-model="verificationForm.business_registration_number" type="text" 
                      class="h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:border-blue-500 transition-all duration-300 form-input"
                      placeholder="Enter registration number"
                      @input="validateStep(1)" />
                  </div>
                </div>
              </div>

              <div v-if="currentStep === 2" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <CreditCard class="w-8 h-8 text-white" />
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Valid ID Requirements</h3>
                  <p class="text-gray-600 mt-2">Upload your valid government-issued ID</p>
                </div>

                <div class="space-y-5">
                  <div class="form-group">
                    <Label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Type of Valid ID
                      <span class="text-red-500">*</span>
                    </Label>
                    <Select v-model="verificationForm.valid_id_type" @update:modelValue="validateStep(2)">
                      <SelectTrigger class="h-12 bg-gray-50 border-gray-200 rounded-xl form-select">
                        <SelectValue placeholder="Select ID Type" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="passport">Passport</SelectItem>
                        <SelectItem value="driver_license">Driver's License</SelectItem>
                        <SelectItem value="umid">UMID</SelectItem>
                        <SelectItem value="prc">PRC ID</SelectItem>
                        <SelectItem value="postal">Postal ID</SelectItem>
                        <SelectItem value="voter">Voter's ID</SelectItem>
                        <SelectItem value="tin">TIN ID</SelectItem>
                        <SelectItem value="sss">SSS ID</SelectItem>
                        <SelectItem value="philhealth">PhilHealth ID</SelectItem>
                        <SelectItem value="other">Other Government ID</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>

                  <div class="form-group">
                    <Label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      ID Number
                      <span class="text-red-500">*</span>
                    </Label>
                    <Input v-model="verificationForm.id_number" type="text" 
                      class="h-12 bg-gray-50 border-gray-200 rounded-xl focus-visible:ring-blue-500 focus-visible:border-blue-500 transition-all duration-300 form-input"
                      placeholder="Enter your ID number"
                      @input="validateStep(2)" />
                  </div>

                  <div class="form-group">
                    <Label class="smooth-color block text-sm font-semibold text-gray-700 mb-2">
                      Photo of Valid ID (Front)
                      <span class="text-red-500">*</span>
                    </Label>
                    <div @click="triggerFileInput('valid_id_photo')" 
                      @dragover.prevent @drop.prevent="handleFileDrop($event, 'valid_id_photo')"
                      class="file-upload-area shine-effect mt-1 cursor-pointer flex flex-col items-center justify-center px-6 pt-8 pb-8 border-2 border-dashed rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]"
                      :class="verificationForm.valid_id_photo ? 'border-green-300 bg-green-50' : 'border-gray-300'">
                      <Upload class="h-12 w-12 mb-4" :class="verificationForm.valid_id_photo ? 'text-green-500' : 'text-gray-400'" />
                      <div class="text-center">
                        <p class="text-sm font-medium text-gray-700">
                          <span class="text-blue-600 hover:text-blue-500">Upload ID Photo</span>
                          <span class="text-gray-500 ml-1">or drag and drop</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">PNG, JPG, PDF up to 5MB</p>
                        <p v-if="verificationForm.valid_id_photo" class="text-sm text-green-600 font-medium mt-2">
                          ✓ {{ verificationForm.valid_id_photo.name }}
                        </p>
                      </div>
                    </div>
                    <input ref="valid_id_photo_input" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'valid_id_photo')">
                  </div>
                </div>
              </div>

              <div v-if="currentStep === 3" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <FileText class="w-8 h-8 text-white" />
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Business Documents</h3>
                  <p class="text-gray-600 mt-2">Upload required business registration documents</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div @click="triggerFileInput('dti_certificate_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'dti_certificate_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.dti_certificate_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <FileText class="h-8 w-8" :class="verificationForm.dti_certificate_photo ? 'text-green-500' : 'text-gray-400'" />
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">DTI Certificate</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.dti_certificate_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="dti_certificate_photo_input" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'dti_certificate_photo')">
                  </div>

                  <div @click="triggerFileInput('mayor_permit_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'mayor_permit_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.mayor_permit_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <FileText class="h-8 w-8" :class="verificationForm.mayor_permit_photo ? 'text-green-500' : 'text-gray-400'" />
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">Mayor's Permit</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.mayor_permit_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="mayor_permit_photo_input" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'mayor_permit_photo')">
                  </div>

                  <div @click="triggerFileInput('barangay_clearance_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'barangay_clearance_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.barangay_clearance_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <FileText class="h-8 w-8" :class="verificationForm.barangay_clearance_photo ? 'text-green-500' : 'text-gray-400'" />
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">Barangay Clearance</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.barangay_clearance_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="barangay_clearance_photo_input" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'barangay_clearance_photo')">
                  </div>

                  <div @click="triggerFileInput('business_registration_photo')" 
                    @dragover.prevent @drop.prevent="handleFileDrop($event, 'business_registration_photo')"
                    :class="[
                      'document-card file-upload-area cursor-pointer p-4 border-2 rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]',
                      verificationForm.business_registration_photo 
                        ? 'border-green-300 bg-green-50' 
                        : 'border-gray-300 bg-white'
                    ]">
                    <div class="flex flex-col items-center justify-center space-y-2">
                      <div class="p-3 rounded-lg bg-gradient-to-br from-gray-100 to-gray-200 mb-2 icon-hover">
                        <FileText class="h-8 w-8" :class="verificationForm.business_registration_photo ? 'text-green-500' : 'text-gray-400'" />
                      </div>
                      <span class="text-sm font-semibold text-gray-700 text-center">Business Plate</span>
                      <span class="text-xs text-gray-500 text-center">Click to upload</span>
                      <p v-if="verificationForm.business_registration_photo" class="text-xs text-green-600 font-medium">
                        ✓ Uploaded
                      </p>
                    </div>
                    <input ref="business_registration_photo_input" type="file" class="sr-only" accept="image/*,.pdf" 
                      @change="handleFileChange($event, 'business_registration_photo')">
                  </div>
                </div>
              </div>

              <div v-if="currentStep === 4" class="space-y-6">
                <div class="text-center mb-6">
                  <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-100 to-purple-100 rounded-2xl flex items-center justify-center shadow-inner">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                      <CheckCircle class="w-8 h-8 text-white" />
                    </div>
                  </div>
                  <h3 class="text-xl font-bold text-gray-800">Review & Submit</h3>
                  <p class="text-gray-600 mt-2">Review all information before submission</p>
                </div>

                <div class="space-y-4">
                  <div class="document-card bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                      <Building2 class="w-5 h-5 mr-2 text-blue-500 icon-hover" />
                      Company Information
                    </h4>
                    <div class="space-y-3">
                      <div>
                        <p class="text-xs text-gray-600 mb-1">Company Name</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.company_name || 'Not provided' }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-600 mb-1">Registration Number</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.business_registration_number || 'Not provided' }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="document-card bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                      <CreditCard class="w-5 h-5 mr-2 text-blue-500 icon-hover" />
                      Valid ID Details
                    </h4>
                    <div class="space-y-3">
                      <div>
                        <p class="text-xs text-gray-600 mb-1">ID Type</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.valid_id_type ? formatIDType(verificationForm.valid_id_type) : 'Not selected' }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-600 mb-1">ID Number</p>
                        <p class="font-semibold text-gray-900">{{ verificationForm.id_number || 'Not provided' }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="document-card bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl border border-gray-200 p-5">
                    <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                      <FileText class="w-5 h-5 mr-2 text-blue-500 icon-hover" />
                      Uploaded Documents
                    </h4>
                    <div class="grid grid-cols-2 gap-3">
                      <template v-for="(value, key) in verificationForm" :key="key">
                        <div v-if="key.includes('photo') && value && typeof value === 'object' && value.name" 
                          class="flex items-center p-3 bg-white border border-gray-200 rounded-lg document-card">
                          <div class="p-1.5 bg-green-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                            <CheckCircle2 class="w-4 h-4 text-green-500" />
                          </div>
                          <span class="text-sm text-gray-700 truncate font-medium">{{ value.name }}</span>
                        </div>
                      </template>
                    </div>
                  </div>

                  <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start">
                      <input type="checkbox" v-model="acceptedTerms" id="terms" 
                        class="form-checkbox mt-1 mr-3 w-5 h-5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                      <label for="terms" class="text-sm text-gray-700 leading-relaxed smooth-color cursor-pointer">
                        I certify that all information provided is accurate and authentic. I understand that providing false information may result in account suspension.
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex justify-between pt-6 border-t border-gray-200 mt-6">
                <Button v-if="currentStep > 1" @click="previousStep" variant="outline"
                  class="px-5 py-2.5 rounded-xl border-gray-300 text-gray-700 hover:bg-gray-50 transition-all duration-300 flex items-center font-medium touch-friendly">
                  <ChevronLeft class="w-4 h-4 mr-2 icon-hover" />
                  Back
                </Button>
                <div v-else></div>

                <Button v-if="currentStep < 4" @click="nextStep" 
                  :disabled="!isStepValid(currentStep)"
                  class="h-auto px-5 py-2.5 rounded-xl transition-all duration-300 flex items-center font-medium shadow-md touch-friendly"
                  :class="[
                    isStepValid(currentStep) 
                      ? 'bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white shadow-blue-500/25 btn-hover-effect' 
                      : 'bg-gray-300 text-gray-500 hover:bg-gray-300 cursor-not-allowed'
                  ]">
                  Continue
                  <ChevronRight class="w-4 h-4 ml-2 icon-hover" />
                </Button>

                <Button v-if="currentStep === 4" @click="submitVerification" 
                  :disabled="!isStepValid(4) || !acceptedTerms || submittingVerification"
                  class="h-auto px-5 py-2.5 rounded-xl transition-all duration-300 flex items-center font-medium shadow-md touch-friendly"
                  :class="[
                    isStepValid(4) && acceptedTerms && !submittingVerification 
                      ? 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-green-500/25 btn-hover-effect' 
                      : 'bg-gray-300 text-gray-500 hover:bg-gray-300 cursor-not-allowed',
                    submittingVerification ? 'bg-gradient-to-r from-green-400 to-green-500 cursor-wait' : ''
                  ]">
                  <Loader2 v-if="submittingVerification" class="w-4 h-4 mr-2 animate-spin" />
                  <Save v-else class="w-4 h-4 mr-2 icon-hover" />
                  {{ submittingVerification ? 'Submitting...' : 'Submit Verification' }}
                </Button>
              </div>
            </CardContent>
          </div>

          <CardContent v-if="verificationData && verificationData.has_submitted && (verificationData.status === 'pending' || verificationData.status === 'approved')" 
            class="p-6">
            <div class="document-card bg-gradient-to-br from-gray-50 to-blue-50 border border-gray-200 rounded-xl p-5">
              <h3 class="font-bold text-gray-800 mb-4">Submitted Documents</h3>
              <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-xs text-gray-600 mb-1">Company Name:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.company_name }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 mb-1">ID Type:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.id_type_name }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 mb-1">ID Number:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.id_number }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-600 mb-1">Business Registration Number:</p>
                    <p class="font-semibold text-gray-900">{{ verificationData.business_registration_number }}</p>
                  </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                  <p class="text-xs text-gray-600 mb-3">Uploaded Documents:</p>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <a v-for="(url, field) in verificationData.photos" :key="field" 
                      :href="url" target="_blank" 
                      class="document-card p-3 bg-white border border-gray-200 rounded-lg hover:border-blue-300 hover:shadow-md transition-all duration-200 flex flex-col items-center hover:scale-[1.02]">
                      <div class="p-2 bg-blue-50 rounded-lg mb-2 icon-hover">
                        <FileText class="w-5 h-5 text-blue-600" />
                      </div>
                      <span class="text-xs text-gray-700 font-medium text-center">{{ formatFieldName(field) }}</span>
                    </a>
                  </div>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                  <p class="text-xs text-gray-600 mb-1">Submitted:</p>
                  <p class="font-semibold text-gray-900">{{ formatDateTime(verificationData.submitted_at) }}</p>
                </div>
                
                <div v-if="verificationData.status === 'pending'" class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-lg p-4">
                  <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg mr-3 flex-shrink-0 icon-hover">
                      <Clock class="w-5 h-5 text-yellow-600" />
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-yellow-800">Under Review</p>
                      <p class="text-xs text-yellow-700 mt-1">Your documents are being reviewed. This usually takes 3-5 business days.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/utils/axios' // Assuming axios is set up here
import { toast, Toaster } from 'vue-sonner'
import { 
  Loader2, Save, User, Mail, Phone, Camera, 
  CheckCircle2, Clock, XCircle, AlertCircle, 
  Briefcase, Lock, ShieldCheck, AlertTriangle, 
  Eye, EyeOff, Key, Info, Building2, CreditCard, 
  Upload, FileText, CheckCircle, ChevronLeft, ChevronRight 
} from 'lucide-vue-next'

// Shadcn Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent } from '@/components/ui/card'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const router = useRouter()

// State
const loading = ref(false)
const saving = ref(false)
const savingDistributor = ref(false)
const changingPassword = ref(false)
const submittingVerification = ref(false)
const currentStep = ref(1)
const acceptedTerms = ref(false)

// Data Structures
const userInfo = reactive({
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
})

const originalUserInfo = ref({})

const distributorInfo = reactive({
  company_name: '',
  business_registration_number: '',
  valid_id_type: '',
  valid_id_type_display: '',
  id_number: ''
})

const originalDistributorInfo = ref({})

const verificationData = ref(null)

const verificationForm = reactive({
  company_name: '',
  valid_id_type: '',
  id_number: '',
  valid_id_photo: null,
  dti_certificate_photo: null,
  mayor_permit_photo: null,
  barangay_clearance_photo: null,
  business_registration_number: '',
  business_registration_photo: null
})

const password = reactive({
  current: '',
  new: '',
  confirm: ''
})

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

// File Input Refs
const valid_id_photo_input = ref(null)
const dti_certificate_photo_input = ref(null)
const mayor_permit_photo_input = ref(null)
const barangay_clearance_photo_input = ref(null)
const business_registration_photo_input = ref(null)

// Computed
const currentPasswordFieldType = computed(() => showCurrentPassword.value ? 'text' : 'password')
const newPasswordFieldType = computed(() => showNewPassword.value ? 'text' : 'password')
const confirmPasswordFieldType = computed(() => showConfirmPassword.value ? 'text' : 'password')

const passwordRequirements = computed(() => {
  const newPassword = password.new
  return [
    { text: 'At least 8 characters', met: newPassword.length >= 8 },
    { text: 'Contains uppercase letter', met: /[A-Z]/.test(newPassword) },
    { text: 'Contains lowercase letter', met: /[a-z]/.test(newPassword) },
    { text: 'Contains number', met: /\d/.test(newPassword) },
    { text: 'Contains special character', met: /[!@#$%^&*(),.?":{}|<>]/.test(newPassword) }
  ]
})

const passwordMatch = computed(() => password.new === password.confirm)

const canChangePassword = computed(() => {
  return password.current && 
         password.new && 
         password.confirm &&
         passwordMatch.value &&
         passwordRequirements.value.every(req => req.met)
})

const userInfoChanged = computed(() => JSON.stringify(userInfo) !== JSON.stringify(originalUserInfo.value))
const distributorInfoChanged = computed(() => JSON.stringify(distributorInfo) !== JSON.stringify(originalDistributorInfo.value))
const hasUnsavedChanges = computed(() => userInfoChanged.value || distributorInfoChanged.value)

const isVerificationFormValid = computed(() => {
  return verificationForm.company_name &&
         verificationForm.valid_id_type &&
         verificationForm.id_number &&
         verificationForm.valid_id_photo &&
         verificationForm.dti_certificate_photo &&
         verificationForm.mayor_permit_photo &&
         verificationForm.barangay_clearance_photo &&
         verificationForm.business_registration_number &&
         verificationForm.business_registration_photo
})

// Methods
const capitalize = (value) => {
  if (!value) return ''
  value = value.toString()
  return value.charAt(0).toUpperCase() + value.slice(1)
}

const getInitials = (name) => {
  if (!name) return '??'
  return name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatDateTime = (dateTimeString) => {
  if (!dateTimeString) return 'N/A'
  const date = new Date(dateTimeString)
  return date.toLocaleString('en-PH', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// Wizard Navigation
const isStepValid = (step) => {
  switch(step) {
    case 1: return verificationForm.company_name && verificationForm.business_registration_number
    case 2: return verificationForm.valid_id_type && verificationForm.id_number && verificationForm.valid_id_photo
    case 3: return verificationForm.dti_certificate_photo && verificationForm.mayor_permit_photo && verificationForm.barangay_clearance_photo && verificationForm.business_registration_photo
    case 4: return isVerificationFormValid.value
    default: return false
  }
}

const validateStep = (step) => {
  // Logic to trigger validation reactivity if needed, 
  // but computed/reactive handles this mostly in Vue 3
  return isStepValid(step)
}

const nextStep = () => {
  if (isStepValid(currentStep.value) && currentStep.value < 4) {
    currentStep.value++
    scrollToTop()
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
    scrollToTop()
  }
}

const getProgressWidth = () => {
  const percentage = ((currentStep.value - 1) / 3) * 100
  return `${percentage}%`
}

const scrollToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const formatIDType = (type) => {
  const types = {
    'passport': 'Passport',
    'driver_license': "Driver's License",
    'umid': 'UMID',
    'prc': 'PRC ID',
    'postal': 'Postal ID',
    'voter': "Voter's ID",
    'tin': 'TIN ID',
    'sss': 'SSS ID',
    'philhealth': 'PhilHealth ID',
    'other': 'Other Government ID'
  }
  return types[type] || type
}

// Password Visibility
const toggleCurrentPasswordVisibility = () => showCurrentPassword.value = !showCurrentPassword.value
const toggleNewPasswordVisibility = () => showNewPassword.value = !showNewPassword.value
const toggleConfirmPasswordVisibility = () => showConfirmPassword.value = !showConfirmPassword.value

const changeProfilePhoto = () => {
  toast.info('Feature to change profile photo would open file picker')
}

// API Calls
const fetchUserData = async () => {
  loading.value = true
  try {
    const response = await axios.get('/profile')
    if (response.data.status === 'success') {
      Object.assign(userInfo, response.data.data.user)
    } else {
      throw new Error(response.data.message || 'Failed to fetch user data')
    }
  } catch (error) {
    console.error('Error fetching user data:', error)
    if (error.response && error.response.status === 401) {
      toast.error('Session expired. Please login again.')
      setTimeout(() => {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        router.push('/Landing/logIn')
      }, 2000)
    } else {
      toast.error(error.response?.data?.message || 'Failed to load profile data. Please try again.')
    }
  } finally {
    loading.value = false
  }
}

const fetchDistributorData = async () => {
  if (userInfo.role === 'distributor') {
    try {
      const response = await axios.get('/distributor/requirements')
      if (response.data.status === 'success') {
        const data = response.data.data
        Object.assign(distributorInfo, {
          company_name: data.company_name || '',
          business_registration_number: data.business_registration_number || '',
          valid_id_type: data.valid_id_type || '',
          valid_id_type_display: data.id_type_name || '',
          id_number: data.id_number || ''
        })
      } else {
        Object.assign(distributorInfo, {
          company_name: '',
          business_registration_number: '',
          valid_id_type: '',
          valid_id_type_display: '',
          id_number: ''
        })
      }
    } catch (error) {
      console.error('Error fetching distributor data:', error)
      // Similar error handling as original
      Object.assign(distributorInfo, {
          company_name: '',
          business_registration_number: '',
          valid_id_type: '',
          valid_id_type_display: '',
          id_number: ''
        })
    }
  }
}

const fetchVerificationData = async () => {
  try {
    const response = await axios.get('/distributor/requirements')
    if (response.data.status === 'success') {
      verificationData.value = response.data.data
      
      if (verificationData.value && verificationData.value.status === 'rejected') {
        verificationForm.company_name = verificationData.value.company_name || ''
        verificationForm.valid_id_type = verificationData.value.valid_id_type || ''
        verificationForm.id_number = verificationData.value.id_number || ''
        verificationForm.business_registration_number = verificationData.value.business_registration_number || ''
      }
    }
  } catch (error) {
    console.error('Error fetching verification data:', error)
    verificationData.value = null
  }
}

const setOriginalData = () => {
  originalUserInfo.value = JSON.parse(JSON.stringify(userInfo))
  originalDistributorInfo.value = JSON.parse(JSON.stringify(distributorInfo))
}

const saveUserInfo = async () => {
  if (!userInfoChanged.value || saving.value) return
  
  saving.value = true
  try {
    const response = await axios.put('/profile', {
      first_name: userInfo.first_name,
      last_name: userInfo.last_name,
      email: userInfo.email,
      phone: userInfo.phone,
      address: userInfo.address
    })
    
    if (response.data.status === 'success') {
      originalUserInfo.value = JSON.parse(JSON.stringify(userInfo))
      toast.success('Personal information updated successfully!')
      userInfo.full_name = `${userInfo.first_name} ${userInfo.last_name}`
    } else {
      throw new Error(response.data.message || 'Failed to update profile')
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      toast.error('Session expired. Please login again.')
      setTimeout(() => {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        router.push('/Landing/logIn')
      }, 2000)
    } else {
      toast.error(error.response?.data?.message || 'Failed to update profile. Please try again.')
      Object.assign(userInfo, JSON.parse(JSON.stringify(originalUserInfo.value)))
    }
  } finally {
    saving.value = false
  }
}

const saveDistributorInfo = async () => {
  if (!distributorInfoChanged.value || savingDistributor.value) return
  
  savingDistributor.value = true
  try {
    const response = await axios.put('/profile/distributor', {
      company_name: distributorInfo.company_name,
      business_registration_number: distributorInfo.business_registration_number,
      valid_id_type: distributorInfo.valid_id_type,
      id_number: distributorInfo.id_number
    })
    
    if (response.data.status === 'success') {
      originalDistributorInfo.value = JSON.parse(JSON.stringify(distributorInfo))
      toast.success('Distributor information updated successfully!')
    } else {
      throw new Error(response.data.message || 'Failed to update distributor information')
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      toast.error('Session expired. Please login again.')
      setTimeout(() => {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        router.push('/Landing/logIn')
      }, 2000)
    } else {
      toast.error(error.response?.data?.message || 'Failed to update distributor information. Please try again.')
      Object.assign(distributorInfo, JSON.parse(JSON.stringify(originalDistributorInfo.value)))
    }
  } finally {
    savingDistributor.value = false
  }
}

const changePassword = async () => {
  if (!canChangePassword.value || changingPassword.value) return
  
  changingPassword.value = true
  try {
    const response = await axios.put('/profile/password', {
      current_password: password.current,
      password: password.new,
      password_confirmation: password.confirm
    })
    
    if (response.data.status === 'success') {
      toast.success('Password changed successfully!')
      password.current = ''
      password.new = ''
      password.confirm = ''
      showCurrentPassword.value = false
      showNewPassword.value = false
      showConfirmPassword.value = false
    } else {
      throw new Error(response.data.message || 'Failed to change password')
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      toast.error('Session expired. Please login again.')
      setTimeout(() => {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        router.push('/Landing/logIn')
      }, 2000)
    } else {
      toast.error(error.response?.data?.message || 'Failed to change password. Please try again.')
    }
  } finally {
    changingPassword.value = false
  }
}

const submitVerification = async () => {
  if (!isVerificationFormValid.value || !acceptedTerms.value || submittingVerification.value) return
  
  submittingVerification.value = true
  try {
    const formData = new FormData()
    
    formData.append('company_name', verificationForm.company_name)
    formData.append('valid_id_type', verificationForm.valid_id_type)
    formData.append('id_number', verificationForm.id_number)
    formData.append('business_registration_number', verificationForm.business_registration_number)
    
    formData.append('valid_id_photo', verificationForm.valid_id_photo)
    formData.append('dti_certificate_photo', verificationForm.dti_certificate_photo)
    formData.append('mayor_permit_photo', verificationForm.mayor_permit_photo)
    formData.append('barangay_clearance_photo', verificationForm.barangay_clearance_photo)
    formData.append('business_registration_photo', verificationForm.business_registration_photo)
    
    const response = await axios.post('/distributor/requirements', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (response.data.status === 'success') {
      toast.success(response.data.message)
      verificationData.value = response.data.data
      
      Object.assign(distributorInfo, {
        company_name: verificationData.value.company_name,
        business_registration_number: verificationData.value.business_registration_number,
        valid_id_type: verificationData.value.valid_id_type,
        valid_id_type_display: verificationData.value.id_type_name,
        id_number: verificationData.value.id_number
      })
      originalDistributorInfo.value = JSON.parse(JSON.stringify(distributorInfo))
      
      // Reset form
      Object.assign(verificationForm, {
        company_name: '',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        dti_certificate_photo: null,
        mayor_permit_photo: null,
        barangay_clearance_photo: null,
        business_registration_number: '',
        business_registration_photo: null
      })
      currentStep.value = 1
      acceptedTerms.value = false
    } else {
      throw new Error(response.data.message || 'Failed to submit verification')
    }
  } catch (error) {
    if (error.response && error.response.status === 401) {
      toast.error('Session expired. Please login again.')
      setTimeout(() => {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
        router.push('/Landing/logIn')
      }, 2000)
    } else {
      let errorMessage = error.response?.data?.message || 'Failed to submit verification. Please try again.'
      if (error.response?.data?.errors) {
        const errors = Object.values(error.response.data.errors).flat()
        errorMessage = errors.join(', ')
      }
      toast.error(errorMessage)
    }
  } finally {
    submittingVerification.value = false
  }
}

const saveAllChanges = () => {
  if (userInfoChanged.value) saveUserInfo()
  if (distributorInfoChanged.value) saveDistributorInfo()
}

// File Handlers
const triggerFileInput = (field) => {
  const refMap = {
    'valid_id_photo': valid_id_photo_input,
    'dti_certificate_photo': dti_certificate_photo_input,
    'mayor_permit_photo': mayor_permit_photo_input,
    'barangay_clearance_photo': barangay_clearance_photo_input,
    'business_registration_photo': business_registration_photo_input
  }
  if(refMap[field] && refMap[field].value) {
    refMap[field].value.click()
  }
}

const handleFileChange = (event, field) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 5 * 1024 * 1024) {
      toast.error(`File "${file.name}" is too large. Maximum size is 5MB.`)
      return
    }
    const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
    if (!validTypes.includes(file.type)) {
      toast.error(`File "${file.name}" must be JPG, PNG, or PDF.`)
      return
    }
    verificationForm[field] = file
    validateStep(currentStep.value)
  }
}

const handleFileDrop = (event, field) => {
  const file = event.dataTransfer.files[0]
  if (file) {
    const input = { target: { files: [file] } }
    handleFileChange(input, field)
  }
}

const formatFieldName = (field) => {
  return field.replace(/_/g, ' ').replace(/(?:^|\s)\S/g, a => a.toUpperCase()).replace('Photo', '').trim()
}

const getVerificationStatusTitle = () => {
  if (!verificationData.value) return 'Verification Required'
  switch (verificationData.value.status) {
    case 'approved': return '✅ Verification Approved'
    case 'pending': return '⏳ Verification Pending'
    case 'rejected': return '❌ Verification Rejected'
    default: return 'Verification Required'
  }
}

const getVerificationStatusMessage = () => {
  if (!verificationData.value) return 'Please upload all required documents for business verification. All fields are mandatory for account verification.'
  switch (verificationData.value.status) {
    case 'approved': return 'Your business verification has been approved. You now have full access to all distributor features.'
    case 'pending': return 'Your documents are under review. Please wait for admin approval. This usually takes 3-5 business days.'
    case 'rejected': return 'Your verification has been rejected. Please review the reason below and resubmit with corrected documents.'
    default: return 'Please upload all required documents for business verification.'
  }
}

// Lifecycle
onMounted(async () => {
  await fetchUserData()
  await fetchDistributorData()
  if (userInfo.role === 'distributor') {
    await fetchVerificationData()
  }
  setOriginalData()
})
</script>

<style scoped>
/* Paste your exact provided CSS content here to maintain 100% design fidelity. 
   I have included the original CSS file logic below as part of this style block.
*/

/* Smooth scrolling */
html {
  scroll-behavior: smooth;
}

/* Custom scrollbar with gradient */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: linear-gradient(to bottom, #f1f5f9, #e2e8f0);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
  transform: scale(1.1);
}

/* Gradient text effects */
.text-gradient {
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Modern card shadows with enhanced hover */
.card-shadow {
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08), 0 2px 10px rgba(0, 0, 0, 0.04);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-shadow:hover {
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12), 0 5px 20px rgba(0, 0, 0, 0.06);
  transform: translateY(-4px);
}

/* Form input hover effects */
.form-input {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: left center;
}

.form-input:hover {
  border-color: #93c5fd;
  background-color: #f8fafc;
  transform: translateX(2px);
}

.form-input:focus {
  border-color: #3b82f6;
  background-color: white;
  transform: translateX(2px) scaleX(1.01);
  box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
}

/* Select dropdown hover */
.form-select {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.form-select:hover {
  border-color: #93c5fd;
  background-color: #f8fafc;
  transform: translateY(-1px);
}

.form-select:focus {
  border-color: #3b82f6;
  background-color: white;
  transform: translateY(-1px);
  box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
}

/* Textarea hover effects */
.form-textarea {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: top left;
}

.form-textarea:hover {
  border-color: #93c5fd;
  background-color: #f8fafc;
  transform: translateY(-2px);
}

.form-textarea:focus {
  border-color: #3b82f6;
  background-color: white;
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
}

/* File upload area hover effects */
.file-upload-area {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.file-upload-area:hover {
  border-color: #93c5fd;
  background-color: #f0f9ff;
  transform: translateY(-3px);
}

.file-upload-area:hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  animation: slideIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideIn {
  from {
    transform: translateX(-100%);
  }
  to {
    transform: translateX(0);
  }
}

/* Button hover effects with 3D illusion */
.btn-hover-effect {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.btn-hover-effect::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.7s ease-in-out;
}

.btn-hover-effect:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 15px 40px rgba(59, 130, 246, 0.25);
}

.btn-hover-effect:hover::before {
  left: 100%;
}

.btn-hover-effect:active {
  transform: translateY(-1px) scale(0.98);
  transition: transform 0.1s ease;
}

/* Checkbox hover effects */
.form-checkbox {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.form-checkbox:hover {
  transform: scale(1.1);
  border-color: #3b82f6;
}

.form-checkbox:checked {
  animation: checkmark 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes checkmark {
  0% {
    transform: scale(0.8);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

/* Progress step hover effects */
.progress-step {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.progress-step:hover {
  transform: scale(1.1);
  box-shadow: 0 5px 20px rgba(59, 130, 246, 0.3);
}

.progress-step.active {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.4);
  }
  50% {
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }
}

/* Card header hover effects */
.card-header-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-header-hover:hover {
  background-position: 100% 50%;
}

/* Status badge hover effects */
.status-badge {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.status-badge:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* Document card hover effects */
.document-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.document-card:hover {
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
  border-color: #3b82f6;
}

/* Password visibility toggle hover */
.password-toggle {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.password-toggle:hover {
  transform: scale(1.1);
  color: #3b82f6;
}

/* Tab/step navigation hover */
.nav-item {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
}

.nav-item:hover {
  transform: translateY(-2px);
}

.nav-item:hover::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  animation: expandWidth 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes expandWidth {
  from {
    width: 0;
    left: 50%;
  }
  to {
    width: 100%;
    left: 0;
  }
}

/* Shine effect for premium elements */
.shine-effect {
  position: relative;
  overflow: hidden;
}

.shine-effect::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -60%;
  width: 20%;
  height: 200%;
  opacity: 0;
  transform: rotate(30deg);
  background: linear-gradient(
    to right,
    rgba(255, 255, 255, 0.13) 0%,
    rgba(255, 255, 255, 0.13) 77%,
    rgba(255, 255, 255, 0.5) 92%,
    rgba(255, 255, 255, 0.0) 100%
  );
  transition: opacity 0.3s ease;
}

.shine-effect:hover::after {
  opacity: 1;
  left: 130%;
  transition: left 0.7s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
}

/* Icon hover effects */
.icon-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.icon-hover:hover {
  transform: scale(1.1) rotate(5deg);
  filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.1));
}

/* Touch-friendly buttons with feedback */
@media (max-width: 768px) {
  .touch-friendly {
    min-height: 48px;
    min-width: 48px;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  }
  
  .touch-friendly:active {
    transform: scale(0.95);
    opacity: 0.8;
  }
}

/* Form group hover effects */
.form-group {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-group:hover {
  transform: translateX(4px);
}

.form-group:hover label {
  color: #3b82f6;
  transform: translateX(2px);
}

.form-group label {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Avatar hover effects */
.avatar-hover {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.avatar-hover:hover {
  transform: scale(1.05) rotate(5deg);
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
}

/* Smooth color transitions */
.smooth-color {
  transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1),
              background-color 0.3s cubic-bezier(0.4, 0, 0.2, 1),
              border-color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Depth effect on hover */
.depth-hover {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.depth-hover:hover {
  transform: translateY(-4px);
  box-shadow: 
    0 10px 40px rgba(0, 0, 0, 0.1),
    0 2px 10px rgba(0, 0, 0, 0.05);
}

/* Page transition */
.page-transition {
  animation: pageFadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes pageFadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>