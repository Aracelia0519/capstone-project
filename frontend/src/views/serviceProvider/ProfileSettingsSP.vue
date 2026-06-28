<template>
  <div class="min-h-screen text-slate-50 pb-20">
    

    <header class="sticky top-0 z-40  backdrop-blur-md border-b border-gray-800 shadow-xl">
      <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-linear-to-br from-gray-600 to-gray-500 rounded-xl shadow-xl">
                <UserCog class="w-6 h-6 text-white" />
              </div>
              Account Management
            </h1>
            <p class="text-gray-400 mt-2 flex items-center gap-2">
              <ShieldCheck class="w-4 h-4 text-blue-400" />
              Manage your profile, security, and preferences
            </p>
          </div>

          <div class="flex gap-3">
            <Button 
              @click="saveProfile" 
              :disabled="!hasChanges || isLoading"
              size="lg"
              class="rounded-xl font-medium transition-all duration-300"
              :class="hasChanges && !isLoading 
                ? 'bg-linear-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white shadow-lg hover:shadow-blue-500/25 border-0' 
                : 'bg-gray-800 text-gray-400 border border-gray-700 hover:bg-gray-800'"
            >
              <Loader2 v-if="isLoading" class="w-5 h-5 animate-spin mr-2" />
              <Save v-else class="w-5 h-5 mr-2" />
              <span class="hidden sm:inline">{{ isLoading ? 'Saving...' : 'Save Changes' }}</span>
              <span class="sm:hidden">{{ isLoading ? 'Saving...' : 'Save' }}</span>
            </Button>

            <Button 
              @click="resetChanges" 
              :disabled="!hasChanges || isLoading"
              variant="outline"
              size="lg"
              class="bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700 hover:text-white rounded-xl"
            >
              <RotateCcw class="w-5 h-5 mr-2" />
              <span class="hidden sm:inline">Reset</span>
            </Button>
          </div>
        </div>
      </div>
    </header>

    <div v-if="loading" class="flex justify-center items-center min-h-100">
      <div class="text-center">
        <Loader2 class="animate-spin w-12 h-12 text-blue-500 mx-auto mb-4" />
        <p class="text-gray-400">Loading profile data...</p>
      </div>
    </div>

    <div v-else-if="error" class="p-6 mx-4 sm:mx-6 lg:mx-8 my-6">
       <Card class="bg-red-900/20 border-red-800 text-red-100">
          <CardContent class="flex items-center gap-4 pt-6">
            <AlertCircle class="w-6 h-6 text-red-500" />
            <div>
              <h3 class="text-lg font-semibold text-red-400">Failed to load profile</h3>
              <p class="text-gray-300 mt-1">{{ error }}</p>
            </div>
            <Button @click="fetchUserProfile" variant="destructive" class="ml-auto">Retry</Button>
          </CardContent>
       </Card>
    </div>

    <main v-else class="px-4 sm:px-6 lg:px-8 py-6 space-y-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 flex flex-col gap-6">
          <Card class="bg-linear-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl overflow-hidden shrink-0">
            <CardContent class="pt-8 pb-6 px-6 text-center">
              <div class="relative inline-block mb-4">
                <Avatar class="w-32 h-32 border-4 border-gray-800 shadow-2xl">
                  <AvatarImage src="" alt="User Avatar" /> <AvatarFallback class="bg-linear-to-br from-blue-500 via-purple-500 to-pink-500 text-4xl font-bold text-white">
                    {{ userInitials }}
                  </AvatarFallback>
                </Avatar>
                <button 
                  @click="editAvatar"
                  class="absolute bottom-0 right-0 bg-gray-800 hover:bg-gray-700 border border-gray-600 rounded-full p-2.5 shadow-lg transition-all hover:scale-110"
                >
                  <Camera class="w-4 h-4 text-white" />
                </button>
              </div>

              <h2 class="text-2xl font-bold text-white mb-1">{{ user.full_name || `${user.first_name} ${user.last_name}` }}</h2>
              <p class="text-gray-400 mb-4">{{ formatRole(user.role) }}</p>

              <Badge variant="outline" class="mb-6 px-4 py-1.5 rounded-full capitalize" :class="statusClasses">
                <div class="w-2 h-2 rounded-full mr-2" :class="statusDotClasses"></div>
                {{ user.status || 'active' }}
              </Badge>

              <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="text-center p-3 bg-gray-800/50 rounded-xl border border-gray-700/50">
                  <div class="text-2xl font-bold text-white">{{ stats.completedJobs || 0 }}</div>
                  <div class="text-gray-400 text-sm">Jobs Done</div>
                </div>
                <div class="text-center p-3 bg-gray-800/50 rounded-xl border border-gray-700/50">
                  <div class="text-2xl font-bold text-white">{{ stats.satisfaction || 0 }}%</div>
                  <div class="text-gray-400 text-sm">Rating</div>
                </div>
              </div>

              <Separator class="bg-gray-700 my-4" />

              <div class="text-center">
                <div class="text-gray-400 text-sm mb-1">Member Since</div>
                <div class="text-white font-medium">{{ formatDate(user.created_at) }}</div>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-linear-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl flex-1">
            <CardHeader class="pb-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-linear-to-r from-yellow-500 to-amber-400 rounded-lg">
                  <Shield class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h2 class="text-lg font-bold text-white">Security</h2>
                  <p class="text-gray-400 text-sm">Account security status</p>
                </div>
              </div>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-gray-300">Password Last Changed</span>
                <span class="text-sm text-gray-400">{{ securityStatus.passwordLastChanged }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-300">Email Verified</span>
                <span v-if="user.email_verified_at" class="inline-flex items-center gap-1 text-green-400">
                  <CheckCircle2 class="w-4 h-4" /> Verified
                </span>
                <span v-else class="inline-flex items-center gap-1 text-yellow-400">
                  <AlertTriangle class="w-4 h-4" /> Pending
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-300">ID Verification</span>
                <span class="capitalize" :class="idVerificationStatusClass">{{ idVerificationStatus }}</span>
              </div>

              <Button 
                @click="showChangePassword = true" 
                variant="outline"
                class="w-full mt-4 bg-gray-800 border-gray-700 hover:bg-gray-700 text-gray-300"
              >
                <Key class="w-4 h-4 mr-2" /> Change Password
              </Button>
            </CardContent>
          </Card>
        </div>

        <div class="lg:col-span-2 flex flex-col gap-6">
          <Card class="bg-linear-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl flex-1">
            <CardHeader>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-linear-to-r from-blue-500 to-cyan-400 rounded-lg">
                    <User class="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <h2 class="text-xl font-bold text-white">Personal Details</h2>
                    <p class="text-gray-400 text-sm">Update your personal information</p>
                  </div>
                </div>
                <div class="text-sm text-gray-500">
                  Last updated: {{ formatDate(user.updated_at) }}
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label class="text-gray-300">First Name</Label>
                  <Input 
                    v-model="user.first_name" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500" 
                    placeholder="Enter first name" 
                  />
                  <p v-if="validationErrors.first_name" class="text-xs text-red-400">{{ validationErrors.first_name[0] }}</p>
                </div>

                <div class="space-y-2">
                  <Label class="text-gray-300">Last Name</Label>
                  <Input 
                    v-model="user.last_name" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500" 
                    placeholder="Enter last name" 
                  />
                  <p v-if="validationErrors.last_name" class="text-xs text-red-400">{{ validationErrors.last_name[0] }}</p>
                </div>

                <div class="space-y-2">
                  <Label class="text-gray-300">Email Address</Label>
                  <Input 
                    type="email"
                    v-model="user.email" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500" 
                    placeholder="Enter email" 
                  />
                  <div v-if="!user.email_verified_at" class="text-sm text-yellow-400 flex items-center gap-1">
                    <AlertTriangle class="w-3 h-3" /> Email not verified
                  </div>
                  <p v-if="validationErrors.email" class="text-xs text-red-400">{{ validationErrors.email[0] }}</p>
                </div>

                <div class="space-y-2">
                  <Label class="text-gray-300">Phone Number</Label>
                  <Input 
                    type="tel"
                    v-model="user.phone" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500" 
                    placeholder="Enter phone number" 
                  />
                   <p v-if="validationErrors.phone" class="text-xs text-red-400">{{ validationErrors.phone[0] }}</p>
                </div>

                <div class="md:col-span-2 space-y-2">
                  <Label class="text-gray-300">Address Overview</Label>
                  <Textarea 
                    v-model="user.address" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500 min-h-20" 
                    placeholder="Enter your address" 
                  />
                  <p v-if="validationErrors.address" class="text-xs text-red-400">{{ validationErrors.address[0] }}</p>
                </div>

                <div class="md:col-span-2 space-y-2">
                  <Label class="text-gray-300">Bio / Professional Summary</Label>
                  <Textarea 
                    v-model="user.bio" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500 min-h-25" 
                    placeholder="Tell us about yourself..." 
                  />
                  <p class="text-xs text-gray-500">Note: Bio field is stored locally until saved.</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card v-if="showChangePassword" class="bg-linear-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl transition-all duration-300 shrink-0">
            <CardHeader>
               <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-linear-to-r from-green-500 to-emerald-400 rounded-lg">
                    <Lock class="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <h2 class="text-xl font-bold text-white">Change Password</h2>
                    <p class="text-gray-400 text-sm">Update your account password</p>
                  </div>
                </div>
                <Button variant="ghost" size="icon" @click="showChangePassword = false" class="text-gray-400 hover:text-white">
                  <X class="w-5 h-5" />
                </Button>
              </div>
            </CardHeader>
            <CardContent>
               <div v-if="passwordError" class="mb-6 p-3 bg-red-900/30 border border-red-800 rounded-lg text-red-400 text-sm">
                {{ passwordError }}
              </div>

              <div class="space-y-4">
                <div class="space-y-2">
                   <Label class="text-gray-300">Current Password</Label>
                   <div class="relative">
                      <Input 
                        :type="showCurrentPassword ? 'text' : 'password'"
                        v-model="password.current"
                        class="bg-gray-800 border-gray-700 text-white pr-10"
                        placeholder="Current password"
                      />
                      <button @click="showCurrentPassword = !showCurrentPassword" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-300">
                        <Eye v-if="showCurrentPassword" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                      </button>
                   </div>
                   <p v-if="validationErrors.current_password" class="text-xs text-red-400">{{ validationErrors.current_password[0] }}</p>
                </div>

                <div class="space-y-2">
                   <Label class="text-gray-300">New Password</Label>
                   <div class="relative">
                      <Input 
                        :type="showNewPassword ? 'text' : 'password'"
                        v-model="password.new"
                        class="bg-gray-800 border-gray-700 text-white pr-10"
                        placeholder="New password"
                      />
                      <button @click="showNewPassword = !showNewPassword" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-300">
                        <Eye v-if="showNewPassword" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                      </button>
                   </div>
                   <p class="text-xs text-gray-500">Min 8 chars, uppercase, lowercase, number.</p>
                   <p v-if="validationErrors.password" class="text-xs text-red-400">{{ validationErrors.password[0] }}</p>
                </div>

                <div class="space-y-2">
                   <Label class="text-gray-300">Confirm Password</Label>
                   <div class="relative">
                      <Input 
                        :type="showConfirmPassword ? 'text' : 'password'"
                        v-model="password.confirm"
                        class="bg-gray-800 border-gray-700 text-white pr-10"
                        placeholder="Confirm password"
                      />
                       <button @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-300">
                        <Eye v-if="showConfirmPassword" class="w-4 h-4" />
                        <EyeOff v-else class="w-4 h-4" />
                      </button>
                   </div>
                   <p v-if="passwordMismatch" class="text-xs text-red-400">Passwords do not match</p>
                </div>

                <Button 
                  @click="changePassword" 
                  :disabled="!canChangePassword || passwordLoading"
                  class="w-full mt-2 bg-linear-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white border-0"
                >
                   <Loader2 v-if="passwordLoading" class="w-4 h-4 animate-spin mr-2" />
                   {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <div class="w-full">
        <Card id="sp-verification-wizard" class="bg-linear-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl overflow-hidden">
             <CardHeader>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-linear-to-r from-indigo-500 to-purple-400 rounded-lg">
                    <FileBadge class="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <h2 class="text-xl font-bold text-white">Identity & Location Verification</h2>
                    <p class="text-gray-400 text-sm">Complete your profile setup in 4 easy steps</p>
                     <p v-if="idVerification.rejectionReason" class="text-xs text-red-400 mt-1">
                      Reason: {{ idVerification.rejectionReason }}
                    </p>
                  </div>
                </div>
                <div class="text-sm" :class="idVerificationStatusClass">
                   {{ idVerificationStatus }}
                   <div v-if="idVerification.submittedAt" class="text-xs text-gray-500 text-right">
                    {{ formatDate(idVerification.submittedAt) }}
                   </div>
                </div>
              </div>
             </CardHeader>
             
             <CardContent>
              <div v-if="idVerification.status === 'verified' || idVerification.status === 'approved'" class="bg-green-900/20 border border-green-800 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                  <CheckCircle2 class="w-6 h-6 text-green-400" />
                  <div>
                    <h3 class="font-semibold text-green-400">Account Verified</h3>
                    <p class="text-gray-300 text-sm">Your identity and location have been verified and approved.</p>
                  </div>
                </div>
              </div>

              <!-- MAX RESUBMISSION UI BLOCK -->
              <div v-if="idVerification.resubmission_count >= 3" class="mb-6 p-4 bg-red-900/20 border border-red-800 rounded-xl text-red-400 text-left">
                  <div class="flex items-center gap-2 mb-2 font-bold">
                      <AlertTriangle class="w-5 h-5" />
                      Maximum Attempts Reached
                  </div>
                  <p class="text-sm">You have reached the maximum of 3 submission attempts. You cannot submit your requirements anymore. Please contact the administrator via chat to request a reset.</p>
              </div>

              <!-- SUBMISSION STATUS TRACKER UI BLOCK -->
              <div v-if="idVerification.status !== 'not_submitted'" class="bg-gray-800/50 rounded-xl p-4 text-left border border-gray-700/50 mb-6">
                <div class="flex justify-between py-2 border-b border-gray-700/30">
                  <span class="text-gray-500 text-sm">Submitted On</span>
                  <span class="text-gray-300 font-medium">{{ formatDate(idVerification.submittedAt) || 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-gray-700/30">
                  <span class="text-gray-500 text-sm">Status</span>
                  <span :class="idVerificationStatusClass">{{ idVerificationStatus }}</span>
                </div>
                <div class="flex justify-between py-2">
                  <span class="text-gray-500 text-sm">Attempts Used</span>
                  <span class="text-gray-300 font-medium">{{ idVerification.resubmission_count }} / 3</span>
                </div>
              </div>

               <div v-if="idVerification.status !== 'verified' && idVerification.status !== 'approved'" class="mb-8 px-2 overflow-x-auto">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 min-w-125">
                  <div class="flex items-center gap-4 w-full md:w-auto">
                    
                    <div class="flex flex-col items-center">
                      <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer"
                           @click="currentStep >= 1 ? currentStep = 1 : null"
                           :class="currentStep >= 1 ? 'bg-linear-to-r from-blue-500 to-indigo-500 border-blue-500 shadow-lg' : 'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white"><Check v-if="currentStep > 1" class="w-5 h-5"/> <span v-else>1</span></span>
                      </div>
                      <span class="mt-2 text-xs md:text-sm whitespace-nowrap" :class="currentStep >= 1 ? 'text-blue-400 font-medium' : 'text-gray-500'">Location</span>
                    </div>
                    
                    <div class="w-8 h-1 md:w-12 rounded-full" :class="currentStep >= 2 ? 'bg-linear-to-r from-indigo-500 to-purple-500' : 'bg-gray-700'"></div>
                    
                    <div class="flex flex-col items-center">
                      <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer"
                           @click="currentStep >= 2 ? currentStep = 2 : null"
                           :class="currentStep >= 2 ? 'bg-linear-to-r from-indigo-500 to-purple-500 border-indigo-500 shadow-lg' : 'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white"><Check v-if="currentStep > 2" class="w-5 h-5"/> <span v-else>2</span></span>
                      </div>
                      <span class="mt-2 text-xs md:text-sm whitespace-nowrap" :class="currentStep >= 2 ? 'text-indigo-400 font-medium' : 'text-gray-500'">ID Details</span>
                    </div>
                    
                    <div class="w-8 h-1 md:w-12 rounded-full" :class="currentStep >= 3 ? 'bg-linear-to-r from-purple-500 to-pink-500' : 'bg-gray-700'"></div>
                    
                    <div class="flex flex-col items-center">
                      <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer"
                           @click="currentStep >= 3 ? currentStep = 3 : null"
                           :class="currentStep >= 3 ? 'bg-linear-to-r from-purple-500 to-pink-500 border-purple-500 shadow-lg' : 'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white"><Check v-if="currentStep > 3" class="w-5 h-5"/> <span v-else>3</span></span>
                      </div>
                      <span class="mt-2 text-xs md:text-sm whitespace-nowrap" :class="currentStep >= 3 ? 'text-purple-400 font-medium' : 'text-gray-500'">ID Photo</span>
                    </div>
                    
                    <div class="w-8 h-1 md:w-12 rounded-full" :class="currentStep >= 4 ? 'bg-linear-to-r from-pink-500 to-rose-500' : 'bg-gray-700'"></div>
                    
                    <div class="flex flex-col items-center">
                      <div class="w-10 h-10 md:w-12 md:h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer"
                           @click="currentStep >= 4 ? currentStep = 4 : null"
                           :class="currentStep >= 4 ? 'bg-linear-to-r from-pink-500 to-rose-500 border-pink-500 shadow-lg' : 'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white">4</span>
                      </div>
                      <span class="mt-2 text-xs md:text-sm whitespace-nowrap" :class="currentStep >= 4 ? 'text-pink-400 font-medium' : 'text-gray-500'">Selfie</span>
                    </div>
                  </div>
                </div>
              </div>

               <div v-if="idVerification.status !== 'verified' && idVerification.status !== 'approved'" class="space-y-6">
                <div v-show="currentStep === 1" class="space-y-6 animate-fade-in">
                    <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                       <MapPin class="w-5 h-5 text-blue-400" /> Step 1: Address & Location
                    </h3>

                    <div class="space-y-5">
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                          <Label class="text-gray-300">Province <span class="text-red-500">*</span></Label>
                          <Input model-value="Cavite" readonly class="bg-gray-800 border-gray-700 text-gray-400 cursor-not-allowed" />
                        </div>

                        <div class="space-y-2">
                          <Label class="text-gray-300">City/Municipality <span class="text-red-500">*</span></Label>
                          <Select v-model="idVerification.city" :disabled="['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3">
                            <SelectTrigger class="w-full bg-gray-800 border-gray-700 text-white">
                               <SelectValue placeholder="Select City/Municipality" />
                            </SelectTrigger>
                            <SelectContent class="bg-gray-800 border-gray-700 text-white max-h-56">
                              <SelectItem v-for="city in caviteCities" :key="city" :value="city">{{ city }}</SelectItem>
                            </SelectContent>
                          </Select>
                        </div>
                      </div>

                      <div class="space-y-2">
                        <Label class="text-gray-300">Barangay <span class="text-red-500">*</span></Label>
                        <Input v-model="idVerification.barangay" type="text" :disabled="['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3" class="bg-gray-800 border-gray-700 text-white" placeholder="Enter Barangay" />
                      </div>

                      <div class="space-y-2">
                        <Label class="text-gray-300">Block/Street/Subdivision <span class="text-red-500">*</span></Label>
                        <Textarea v-model="idVerification.block_address" rows="2" :disabled="['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3" class="bg-gray-800 border-gray-700 text-white resize-none" placeholder="Block No., Lot No., Street Name, Subdivision/Village" />
                      </div>

                      <div class="p-4 bg-gray-800/50 rounded-xl border border-gray-700">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                          <Label class="text-gray-300">Pin Location <span class="text-red-500">*</span></Label>
                          <Button type="button" size="sm" @click="getCurrentLocation" :disabled="gettingLocation || ['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-3 py-1 text-xs w-full sm:w-auto flex justify-center">
                            <Navigation v-if="!gettingLocation" class="w-3 h-3 mr-1.5" />
                            <Loader2 v-else class="w-3 h-3 mr-1.5 animate-spin" />
                            {{ gettingLocation ? 'Locating...' : 'Get My Location' }}
                          </Button>
                        </div>
                        
                        <div id="sp-map" class="h-80 sm:h-96 md:h-[450px] w-full rounded-xl border border-gray-600 shadow-md z-0 overflow-hidden relative"></div>

                        <div class="grid grid-cols-2 gap-4 mt-3">
                          <div>
                            <span class="text-xs text-gray-500 block mb-1">Latitude</span>
                            <Input v-model="idVerification.latitude" readonly class="h-10 bg-gray-900 border-gray-700 text-gray-400" placeholder="0.000000" />
                          </div>
                          <div>
                            <span class="text-xs text-gray-500 block mb-1">Longitude</span>
                            <Input v-model="idVerification.longitude" readonly class="h-10 bg-gray-900 border-gray-700 text-gray-400" placeholder="0.000000" />
                          </div>
                        </div>
                        <p class="text-xs text-red-400 mt-2" v-if="locationError">{{ locationError }}</p>
                        <p class="text-xs text-gray-400 mt-2" v-else>Drag the blue pin to your exact location.</p>
                      </div>
                    </div>
                </div>

                <div v-show="currentStep === 2" class="space-y-6 animate-fade-in">
                  <div class="bg-linear-to-br from-indigo-900/20 to-purple-900/20 border border-indigo-800/30 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                       <CreditCard class="w-5 h-5 text-indigo-400" /> Step 2: ID Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                      <div class="space-y-2">
                        <Label class="text-gray-300">Select ID Type <span class="text-red-400">*</span></Label>
                         <Select v-model="idVerification.idType" :disabled="['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3">
                          <SelectTrigger class="w-full bg-gray-800 border-gray-700 text-white">
                             <SelectValue placeholder="Select ID Type" />
                          </SelectTrigger>
                          <SelectContent class="bg-gray-800 border-gray-700 text-white">
                            <SelectItem value="phil_id">Philippine National ID</SelectItem>
                            <SelectItem value="passport">Passport</SelectItem>
                            <SelectItem value="driver_license">Driver's License</SelectItem>
                            <SelectItem value="umid">UMID (SSS/GSIS)</SelectItem>
                            <SelectItem value="prc_id">PRC ID</SelectItem>
                            <SelectItem value="voter_id">Voter's ID</SelectItem>
                            <SelectItem value="other">Other Valid ID</SelectItem>
                          </SelectContent>
                        </Select>
                        <p v-if="idVerificationErrors.idType" class="text-xs text-red-400">{{ idVerificationErrors.idType }}</p>
                      </div>
                      
                      <div class="space-y-2">
                        <Label class="text-gray-300">ID Number <span class="text-red-400">*</span></Label>
                        <Input 
                          v-model="idVerification.idNumber"
                          :disabled="['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3"
                          class="bg-gray-800 border-gray-700 text-white"
                          placeholder="Enter ID number"
                        />
                         <p v-if="idVerificationErrors.idNumber" class="text-xs text-red-400">{{ idVerificationErrors.idNumber }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-show="currentStep === 3" class="space-y-6 animate-fade-in">
                   <div class="bg-linear-to-br from-purple-900/20 to-pink-900/20 border border-purple-800/30 rounded-xl p-6">
                     <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                       <Image class="w-5 h-5 text-purple-400" /> Step 3: ID Photo Upload
                     </h3>
                     
                     <div 
                        @dragover.prevent="handleDragOver"
                        @dragleave="handleDragLeave"
                        @drop.prevent="handleIdDrop"
                        @click="(['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3) ? null : triggerIdUpload()"
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="[
                          idUploadClasses,
                          (['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-purple-900/10'
                        ]"
                      >
                        <input type="file" ref="idFileInput" @change="handleIdUploadChange" accept="image/*" class="hidden" :disabled="['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3"/>
                        
                        <div v-if="!idVerification.idPhotoPreview && !idVerification.idPhotoUrl" class="space-y-4">
                          <UploadCloud class="w-16 h-16 text-gray-500 mx-auto" />
                          <div>
                            <p class="text-gray-300 font-medium">Drop your ID photo here or click to browse</p>
                            <p class="text-gray-500 text-sm">Supports JPG, PNG up to 5MB</p>
                          </div>
                        </div>

                        <div v-else class="space-y-4">
                          <div class="relative inline-block">
                             <img :src="idVerification.idPhotoPreview || idVerification.idPhotoUrl" alt="ID Preview" class="max-h-48 mx-auto rounded-lg shadow-lg border border-gray-700" />
                             <button v-if="!['pending', 'verified', 'approved'].includes(idVerification.status) && idVerification.resubmission_count < 3" @click.stop="removeIdPhoto" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow-md">
                               <X class="w-4 h-4" />
                             </button>
                          </div>
                          <p class="text-green-400 text-sm flex justify-center items-center gap-2"><Check class="w-4 h-4"/> ID photo uploaded</p>
                        </div>
                        
                        <div v-if="idUploadProgress > 0 && idUploadProgress < 100" class="mt-4 max-w-xs mx-auto">
                            <Progress :model-value="idUploadProgress" class="h-2 bg-gray-700" />
                            <p class="text-gray-400 text-xs mt-2">{{ idUploadProgress }}% uploaded</p>
                         </div>
                      </div>
                      <p v-if="idVerificationErrors.idPhoto" class="text-xs text-red-400 mt-2">{{ idVerificationErrors.idPhoto }}</p>
                   </div>
                </div>

                <div v-show="currentStep === 4" class="space-y-6 animate-fade-in">
                  <div class="bg-linear-to-br from-pink-900/20 to-rose-900/20 border border-pink-800/30 rounded-xl p-6">
                     <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                       <Camera class="w-5 h-5 text-pink-400" /> Step 4: Selfie with ID
                     </h3>

                     <div 
                        @dragover.prevent="handleSelfieDragOver"
                        @dragleave="handleSelfieDragLeave"
                        @drop.prevent="handleSelfieDrop"
                        @click="(['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3) ? null : triggerSelfieUpload()"
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="[
                          selfieUploadClasses,
                          (['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3) ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-pink-900/10'
                        ]"
                      >
                        <input type="file" ref="selfieFileInput" @change="handleSelfieUploadChange" accept="image/*" class="hidden" :disabled="['pending', 'verified', 'approved'].includes(idVerification.status) || idVerification.resubmission_count >= 3"/>
                        
                        <div v-if="!idVerification.selfiePhotoPreview && !idVerification.selfiePhotoUrl" class="space-y-4">
                          <User class="w-16 h-16 text-gray-500 mx-auto" />
                          <div>
                            <p class="text-gray-300 font-medium">Drop selfie here or click to browse</p>
                            <p class="text-gray-500 text-sm">Hold ID next to face</p>
                          </div>
                        </div>

                        <div v-else class="space-y-4">
                          <div class="relative inline-block">
                             <img :src="idVerification.selfiePhotoPreview || idVerification.selfiePhotoUrl" alt="Selfie Preview" class="max-h-48 mx-auto rounded-lg shadow-lg border border-gray-700" />
                             <button v-if="!['pending', 'verified', 'approved'].includes(idVerification.status) && idVerification.resubmission_count < 3" @click.stop="removeSelfiePhoto" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow-md">
                               <X class="w-4 h-4" />
                             </button>
                          </div>
                          <p class="text-green-400 text-sm flex justify-center items-center gap-2"><Check class="w-4 h-4"/> Selfie uploaded</p>
                        </div>
                      </div>
                      <p v-if="idVerificationErrors.selfiePhoto" class="text-xs text-red-400 mt-2">{{ idVerificationErrors.selfiePhoto }}</p>
                  </div>
                </div>

                <div v-if="idVerification.status === 'pending'" class="md:col-span-2">
                  <div class="bg-yellow-900/20 border border-yellow-800 rounded-xl p-6 flex items-start gap-4">
                    <Loader2 class="w-8 h-8 text-yellow-400 animate-spin shrink-0" />
                    <div>
                      <h3 class="font-semibold text-yellow-400 mb-1">Verification Under Review</h3>
                      <p class="text-gray-300 text-sm">Your ID and Location verification is being reviewed. This usually takes 1-2 business days.</p>
                    </div>
                  </div>
                </div>

                <div v-if="!['pending', 'verified', 'approved'].includes(idVerification.status)" class="flex justify-between pt-6 border-t border-gray-800">
                  <Button 
                    @click="prevStep" 
                    v-if="currentStep > 1" 
                    variant="secondary"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300"
                  >
                    <ChevronLeft class="w-4 h-4 mr-2" /> Previous
                  </Button>
                  <div v-else></div>
                  
                  <div class="flex gap-4">
                     <Button 
                        v-if="currentStep < 4" 
                        @click="nextStep" 
                        :disabled="!canProceedToNextStep"
                        class="bg-linear-to-r from-blue-600 to-indigo-500 hover:from-blue-700 hover:to-indigo-600 text-white border-0"
                      >
                        Next Step <ChevronRight class="w-4 h-4 ml-2" />
                      </Button>

                      <Button 
                        v-if="currentStep === 4" 
                        @click="submitIdVerification" 
                        :disabled="!canSubmitIdVerification || idVerificationLoading"
                        class="bg-linear-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white border-0"
                      >
                         <Loader2 v-if="idVerificationLoading" class="w-4 h-4 animate-spin mr-2" />
                         <Check v-else class="w-4 h-4 mr-2" />
                         {{ idVerificationLoading ? 'Submitting...' : 'Submit Verification' }}
                      </Button>
                  </div>
                </div>
              </div>
             </CardContent>
          </Card>
      </div>

      <!-- Floating Chat Widget (Connected to Backend) -->
      <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        <!-- Chat Window -->
        <transition name="fade-slide">
          <div v-if="showAdminChat" class="bg-gray-900 border border-gray-700 rounded-xl shadow-2xl w-80 sm:w-96 h-[32rem] mb-4 flex flex-col overflow-hidden">
            <!-- Chat Header -->
            <div class="p-4 border-b border-gray-800 bg-gray-800/50 flex justify-between items-center">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white">
                  <UserCog class="w-4 h-4"/>
                </div>
                <div>
                  <h3 class="text-white font-medium text-sm">Admin Support</h3>
                  <p class="text-xs text-green-400">Online</p>
                </div>
              </div>
              <button @click="toggleAdminChat" class="text-gray-400 hover:text-white">
                <X class="w-5 h-5"/>
              </button>
            </div>
            <!-- Chat Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="chatMessagesContainer">
              <div v-if="chatLoading" class="flex justify-center py-4">
                <Loader2 class="animate-spin h-5 w-5 text-blue-500" />
              </div>
              <div v-else-if="chatMessages.length === 0" class="text-center text-gray-500 text-sm mt-10">No messages yet. Start a conversation!</div>
              <div v-for="msg in chatMessages" :key="msg.id" class="flex flex-col" :class="msg.sender_id === user.id ? 'items-end' : 'items-start'">
                <div class="max-w-[80%] p-3 rounded-2xl text-sm" :class="msg.sender_id === user.id ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-gray-800 text-gray-200 rounded-tl-none'">
                  {{ msg.message }}
                </div>
                <span class="text-[10px] text-gray-500 mt-1">{{ formatDateTime(msg.created_at) }}</span>
              </div>
            </div>
            <!-- Chat Input -->
            <div class="p-3 border-t border-gray-800 bg-gray-900">
              <form @submit.prevent="sendAdminMessage" class="flex gap-2">
                <input v-model="newChatMessage" type="text" placeholder="Type a message..." class="flex-1 bg-gray-800 border-none rounded-full px-4 text-sm text-white focus:ring-1 focus:ring-blue-500" />
                <button type="submit" :disabled="!newChatMessage.trim() || sendingMessage" class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white disabled:opacity-50">
                  <Navigation class="w-4 h-4" style="transform: rotate(90deg);" />
                </button>
              </form>
            </div>
          </div>
        </transition>
        
        <!-- Chat Toggle Button -->
        <button @click="toggleAdminChat" class="w-14 h-14 bg-blue-600 rounded-full shadow-[0_0_15px_rgba(59,130,246,0.5)] flex items-center justify-center text-white hover:bg-blue-700 hover:scale-105 transition-all">
          <MessageSquare v-if="!showAdminChat" class="w-6 h-6" />
          <X v-else class="w-6 h-6" />
          <span v-if="unreadAdminMessages > 0 && !showAdminChat" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-[10px] font-bold flex items-center justify-center border-2 border-gray-900">{{ unreadAdminMessages }}</span>
        </button>
      </div>

    </main>
  </div>
</template>

<script>
import { getCurrentUser, clearAuthData } from '@/utils/auth'
import axios from '@/utils/axios'
import echo from '@/utils/websocket'
import { Toaster, toast } from 'vue-sonner'

// Leaflet
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'

// Import Marker Images
// @ts-ignore
import iconRetinaUrl from 'leaflet/dist/images/marker-icon-2x.png'
// @ts-ignore
import iconUrl from 'leaflet/dist/images/marker-icon.png'
// @ts-ignore
import shadowUrl from 'leaflet/dist/images/marker-shadow.png'

// Shadcn UI Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Separator } from '@/components/ui/separator'

// Icons
import { 
  UserCog, ShieldCheck, Save, RotateCcw, Loader2, AlertCircle, 
  Camera, Shield, CheckCircle2, AlertTriangle, Key, User, 
  FileBadge, Check, X, CreditCard, Image, UploadCloud, 
  ChevronLeft, ChevronRight, Lock, Eye, EyeOff, MapPin, Navigation, MessageSquare
} from 'lucide-vue-next'

export default {
  name: 'ProfileSettingsPage',
  props: ['verificationStatus'],
  components: {
    Toaster, Button, Input, Label, Textarea, Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    Card, CardContent, CardHeader, CardTitle, CardDescription, Avatar, AvatarFallback, AvatarImage,
    Badge, Progress, Separator, UserCog, ShieldCheck, Save, RotateCcw, Loader2, AlertCircle,
    Camera, Shield, CheckCircle2, AlertTriangle, Key, User, FileBadge, Check, X, CreditCard, Image, UploadCloud,
    ChevronLeft, ChevronRight, Lock, Eye, EyeOff, MapPin, Navigation, MessageSquare
  },
  data() {
    return {
      loading: true,
      error: null,
      isLoading: false,
      passwordLoading: false,
      idVerificationLoading: false,
      showChangePassword: false,
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      validationErrors: {},
      passwordError: null,
      hasChanges: false,
      user: {
        id: null, first_name: '', last_name: '', full_name: '', name: '', email: '', phone: '', address: '', role: '', status: '', email_verified_at: null, created_at: '', updated_at: '', bio: ''
      },
      originalUser: null,
      password: { current: '', new: '', confirm: '' },
      securityStatus: { passwordLastChanged: 'Unknown' },
      stats: { completedJobs: 0, satisfaction: 0, activeProjects: 0 },
      // ID Verification
      idVerification: {
        city: '', barangay: '', block_address: '', latitude: '', longitude: '', idType: '', idNumber: '', idPhoto: null, idPhotoPreview: null, idPhotoUrl: null, selfiePhoto: null, selfiePhotoPreview: null, selfiePhotoUrl: null, status: 'not_submitted', submittedAt: null, reviewedAt: null, rejectionReason: null, resubmission_count: 0
      },
      idVerificationErrors: {},
      isDraggingId: false,
      isDraggingSelfie: false,
      idUploadProgress: 0,
      currentStep: 1,

      // Geolocation and Map
      gettingLocation: false,
      locationError: '',
      map: null, marker: null, caviteLayer: null,
      caviteCities: [
        'Alfonso', 'Amadeo', 'Bacoor', 'Carmona', 'Cavite City', 'Dasmariñas', 'General Emilio Aguinaldo', 'General Mariano Alvarez', 'General Trias', 'Imus', 'Indang', 'Kawit', 'Magallanes', 'Maragondon', 'Mendez', 'Naic', 'Noveleta', 'Rosario', 'Silang', 'Tagaytay', 'Tanza', 'Ternate', 'Trece Martires'
      ],

      // Admin Chat States
      showAdminChat: false,
      chatMessages: [],
      newChatMessage: '',
      chatLoading: false,
      sendingMessage: false,
      unreadAdminMessages: 0,
    }
  },
  computed: {
    userInitials() {
      if (this.user.first_name && this.user.last_name) return (this.user.first_name.charAt(0) + this.user.last_name.charAt(0)).toUpperCase()
      if (this.user.name) {
        const names = this.user.name.split(' ')
        return names.length >= 2 ? (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase() : names[0].charAt(0).toUpperCase()
      }
      return '?'
    },
    passwordMismatch() { return this.password.new && this.password.confirm && this.password.new !== this.password.confirm },
    canChangePassword() {
      return this.password.current && this.password.new && this.password.confirm && !this.passwordMismatch &&
             this.password.new.length >= 8 && /[A-Z]/.test(this.password.new) && /[0-9]/.test(this.password.new) && /[^A-Za-z0-9]/.test(this.password.new)
    },
    statusClasses() {
      const status = this.user.status || 'active'
      if(status === 'active') return 'bg-green-900/30 text-green-400 border-green-800'
      if(status === 'pending') return 'bg-yellow-900/30 text-yellow-400 border-yellow-800'
      if(status === 'inactive') return 'bg-red-900/30 text-red-400 border-red-800'
      return 'bg-gray-800/30 text-gray-400 border-gray-700'
    },
    statusDotClasses() {
      const status = this.user.status || 'active'
      if(status === 'active') return 'bg-green-400'
      if(status === 'pending') return 'bg-yellow-400'
      if(status === 'inactive') return 'bg-red-400'
      return 'bg-gray-400'
    },
    idVerificationStatus() {
      switch (this.idVerification.status) {
        case 'pending': return 'Pending Verification'
        case 'verified': return 'Verified'
        case 'approved': return 'Verified'
        case 'rejected': return 'Rejected - Please resubmit'
        default: return 'Not Submitted'
      }
    },
    idVerificationStatusClass() {
      switch (this.idVerification.status) {
        case 'pending': return 'text-yellow-400 font-medium'
        case 'verified': return 'text-green-400 font-medium'
        case 'approved': return 'text-green-400 font-medium'
        case 'rejected': return 'text-red-400 font-medium'
        default: return 'text-gray-500'
      }
    },
    idUploadClasses() { return this.isDraggingId ? 'border-gray-700 border-purple-500 bg-purple-900/20' : 'border-gray-700' },
    selfieUploadClasses() { return this.isDraggingSelfie ? 'border-gray-700 border-pink-500 bg-pink-900/20' : 'border-gray-700' },
    canSubmitIdVerification() {
      return this.idVerification.idType && this.idVerification.idNumber && (this.idVerification.idPhoto || this.idVerification.idPhotoUrl) && 
             (this.idVerification.selfiePhoto || this.idVerification.selfiePhotoUrl) && this.idVerification.city && this.idVerification.barangay &&
             this.idVerification.block_address && this.idVerification.latitude && this.idVerification.longitude &&
             !['pending', 'verified', 'approved'].includes(this.idVerification.status) &&
             this.idVerification.resubmission_count < 3
    },
    canProceedToNextStep() {
      if (this.idVerification.resubmission_count >= 3) return false;
      switch (this.currentStep) {
        case 1: return this.idVerification.city && this.idVerification.barangay.trim() && this.idVerification.block_address.trim() && this.idVerification.latitude && this.idVerification.longitude && !this.locationError;
        case 2: return this.idVerification.idType && this.idVerification.idNumber.trim();
        case 3: return this.idVerification.idPhoto || this.idVerification.idPhotoUrl;
        case 4: return this.idVerification.selfiePhoto || this.idVerification.selfiePhotoUrl;
        default: return false
      }
    }
  },
  watch: {
    verificationStatus(newStatus) {
      if (newStatus && newStatus !== this.idVerification.status) {
        this.loadIdVerificationStatus();
      }
    },
    currentStep(newStep) {
      if (newStep === 1 && !['verified', 'approved'].includes(this.idVerification.status)) {
        this.$nextTick(() => { if (!this.loading && !this.error) this.initMap(); });
      } else {
        if (this.map) { this.map.remove(); this.map = null; this.marker = null; this.caviteLayer = null; }
      }
    },
    loading(isLoading) {
      if (!isLoading && !this.error && this.currentStep === 1 && !['verified', 'approved'].includes(this.idVerification.status)) {
        this.$nextTick(() => { this.initMap(); });
      }
    }
  },
  created() {
    this.fetchUserProfile()
    this.loadIdVerificationStatus()
  },
  mounted() {
    setTimeout(() => {
      const cards = document.querySelectorAll('.bg-linear-to-br')
      cards.forEach((card, index) => {
        card.style.opacity = '0'
        card.style.transform = 'translateY(20px)'
        setTimeout(() => {
          card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
          card.style.opacity = '1'
          card.style.transform = 'translateY(0)'
        }, index * 100)
      })
    }, 100)
  },
  beforeUnmount() {
      if (this.user.id) {
          echo.leave(`user.${this.user.id}.requirements`); // MUST LEAVE THE CHANNEL
          echo.leave(`support.user.${this.user.id}`);
      }
  },
  methods: {
    // ---- WebSockets & Real-time Update Methods ----
    setupRequirementsListener() {
        if (!this.user.id) return;
        
        echo.private(`user.${this.user.id}.requirements`)
            .listen('.RequirementStatusUpdated', (e) => {
                let msg = e.status === 'reset' 
                    ? 'Your submission attempts have been reset to 0 by the administrator.'
                    : `Your verification status was updated to: ${e.status}`;
                
                this.showNotification(msg, e.status === 'rejected' ? 'error' : 'success');
                
                if (e.reason) {
                    this.showNotification(`Reason: ${e.reason}`, 'error');
                }
                // INSTANTLY RELOAD THE DATA
                this.loadIdVerificationStatus();
            });
    },

    setupChatListener() {
      if (!this.user.id) return;
      echo.private(`support.user.${this.user.id}`)
        .listen('.SupportMessageSent', (e) => {
          if (e.message.sender_id !== this.user.id) {
            this.chatMessages.push(e.message);
            if (!this.showAdminChat) this.unreadAdminMessages++;
            else this.scrollToBottom();
          }
        });
    },
    // ------------------------------------------------

    // ---- Admin Chat Methods ----
    toggleAdminChat() {
      this.showAdminChat = !this.showAdminChat;
      if (this.showAdminChat) {
        this.unreadAdminMessages = 0;
        this.fetchAdminMessages();
      }
    },
    async fetchAdminMessages() {
      this.chatLoading = true;
      try {
        const res = await axios.get('/service-provider/support/messages');
        if (res.data.status === 'success') {
          this.chatMessages = res.data.messages;
          this.scrollToBottom();
        }
      } catch(e) {
        console.error('Failed to load support messages', e);
      } finally {
        this.chatLoading = false;
      }
    },
    async sendAdminMessage() {
      if (!this.newChatMessage.trim() || this.sendingMessage) return;
      const msg = this.newChatMessage;
      this.newChatMessage = '';
      this.sendingMessage = true;
      
      const tempMsg = {
        id: Date.now(),
        sender_id: this.user.id,
        message: msg,
        created_at: new Date().toISOString()
      };
      this.chatMessages.push(tempMsg);
      this.scrollToBottom();

      try {
        const res = await axios.post('/service-provider/support/messages', { message: msg });
        if (res.data.status === 'success') {
          const index = this.chatMessages.findIndex(m => m.id === tempMsg.id);
          if (index !== -1) this.chatMessages[index] = res.data.message_data;
        }
      } catch (e) {
        this.showNotification('Failed to send message', 'error');
        this.chatMessages = this.chatMessages.filter(m => m.id !== tempMsg.id);
      } finally {
        this.sendingMessage = false;
        this.scrollToBottom();
      }
    },
    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.chatMessagesContainer;
        if (container) container.scrollTop = container.scrollHeight;
      });
    },
    // ----------------------------

    showNotification(message, type = 'info') {
      if (type === 'success') toast.success(message)
      else if (type === 'error') toast.error(message)
      else if (type === 'warning') toast.warning(message)
      else toast.info(message)
    },

    async fetchUserProfile() {
      this.loading = true
      this.error = null
      
      try {
        const authUser = await getCurrentUser()
        if (!authUser) throw new Error('Please login to view your profile')

        const response = await axios.get('/auth/me')
        
        if (response.data.status === 'success') {
          this.user = response.data.user || response.data
          this.user.bio = localStorage.getItem(`user_bio_${this.user.id}`) || ''
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          await this.fetchServiceProviderStats()
          this.showNotification('Profile loaded successfully!', 'success')
          
          // INIT WEBSOCKETS HERE
          this.setupRequirementsListener();
          this.setupChatListener();
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
        this.loading = false
      }
    },
    
    async fetchServiceProviderStats() {
      try {
        const response = await axios.get('/dashboard/service-provider')
        if (response.data.status === 'success') {
          this.stats = {
            completedJobs: response.data.data?.completedJobs || response.data.completedJobs || 0,
            satisfaction: response.data.data?.satisfaction || response.data.satisfaction || 0,
            activeProjects: response.data.data?.activeProjects || 0
          }
        }
      } catch (error) {
        console.error('Error fetching service provider stats:', error)
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'Unknown'
      try {
        const date = new Date(dateString)
        if (isNaN(date.getTime())) return 'Unknown'
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
      } catch (e) { return 'Unknown' }
    },
    
    formatDateTime(dateTime) {
      if (!dateTime) return 'Unknown'
      const date = new Date(dateTime)
      return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
    },

    formatRole(role) {
      const roles = { 'client': 'Client Account', 'distributor': 'Distributor Account', 'service_provider': 'Service Provider Account', 'admin': 'Administrator' }
      return roles[role] || (role ? role.replace('_', ' ') : 'Service Provider')
    },
    
    markChanged() {
      if (!this.originalUser) return
      const current = JSON.stringify({ first_name: this.user.first_name, last_name: this.user.last_name, email: this.user.email, phone: this.user.phone, address: this.user.address, bio: this.user.bio })
      const original = JSON.stringify({ first_name: this.originalUser.first_name, last_name: this.originalUser.last_name, email: this.originalUser.email, phone: this.originalUser.phone, address: this.originalUser.address, bio: this.originalUser.bio })
      this.hasChanges = current !== original
    },
    
    async saveProfile() {
      if (!this.hasChanges || this.isLoading) return
      this.isLoading = true
      this.validationErrors = {}
      
      try {
        const updateData = { first_name: this.user.first_name, last_name: this.user.last_name, email: this.user.email, phone: this.user.phone, address: this.user.address }
        const response = await axios.put('/profile', updateData)
        if (response.data.status === 'success') {
          if (this.user.bio && this.user.id) localStorage.setItem(`user_bio_${this.user.id}`, this.user.bio)
          Object.assign(this.user, updateData)
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          this.showNotification('Profile updated successfully!', 'success')
        } else throw new Error('Update failed')
      } catch (error) {
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.showNotification('Please fix the validation errors', 'error')
        } else this.showNotification(error.response?.data?.message || 'Failed to update profile', 'error')
      } finally { this.isLoading = false }
    },
    
    resetChanges() {
      if (!this.originalUser) return
      this.user = JSON.parse(JSON.stringify(this.originalUser))
      this.hasChanges = false
      this.validationErrors = {}
      this.showNotification('Changes have been reset', 'info')
    },
    
    async changePassword() {
      if (!this.canChangePassword || this.passwordLoading) return
      this.passwordLoading = true
      this.passwordError = null
      this.validationErrors = {}
      
      try {
        const response = await axios.put('/profile/password', { current_password: this.password.current, password: this.password.new, password_confirmation: this.password.confirm })
        if (response.data.status === 'success') {
          this.password = { current: '', new: '', confirm: '' }
          this.showChangePassword = false
          this.securityStatus.passwordLastChanged = 'Just now'
          this.showNotification('Password changed successfully!', 'success')
        } else throw new Error('Password change failed')
      } catch (error) {
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.passwordError = error.response.data.message || 'Password change failed'
          this.showNotification(this.passwordError, 'error')
        } else {
          this.passwordError = error.response?.data?.message || error.message || 'Failed to change password'
          this.showNotification(this.passwordError, 'error')
        }
      } finally { this.passwordLoading = false }
    },
    
    editAvatar() { this.showNotification('Avatar upload feature will be available soon', 'info') },
    nextStep() { if (this.currentStep < 4 && this.canProceedToNextStep) this.currentStep++ },
    prevStep() { if (this.currentStep > 1) this.currentStep-- },
    
    fixLeafletIcons() {
      delete L.Icon.Default.prototype._getIconUrl;
      L.Icon.Default.mergeOptions({ iconRetinaUrl, iconUrl, shadowUrl });
    },

    getAddressComponent(address, keys) {
      if (!address) return '';
      for (const key of keys) { if (address[key]) return address[key]; }
      return '';
    },

    async drawCaviteBoundary() {
      try {
        const response = await fetch('https://nominatim.openstreetmap.org/search?state=Cavite&country=Philippines&polygon_geojson=1&format=json');
        const data = await response.json();
        if (data && data.length > 0) {
          const caviteData = data.find(d => d.class === 'boundary' && d.type === 'administrative') || data[0];
          this.caviteLayer = L.geoJSON(caviteData.geojson, { style: { color: '#4f46e5', weight: 4, fillColor: '#4f46e5', fillOpacity: 0.1, dashArray: '5, 5' } }).addTo(this.map);
          const exactBounds = this.caviteLayer.getBounds();
          this.map.setMaxBounds(exactBounds.pad(0.02)); 
          this.map.setMinZoom(10);
          if (!this.idVerification.latitude) this.map.fitBounds(exactBounds);
        }
      } catch (err) {
        console.error('Failed to load strictly exact boundary for Cavite:', err);
        const fallbackBounds = L.latLngBounds([14.0000, 120.5000], [14.6000, 121.1000]);
        if(this.map) this.map.setMaxBounds(fallbackBounds);
      }
    },

    initMap() {
      const mapContainer = document.getElementById('sp-map');
      if (!mapContainer || this.map) return; 
      
      const startLat = this.idVerification.latitude ? parseFloat(this.idVerification.latitude) : 14.24;
      const startLng = this.idVerification.longitude ? parseFloat(this.idVerification.longitude) : 120.88;

      this.fixLeafletIcons();
      this.map = L.map('sp-map', { minZoom: 10 }).setView([startLat, startLng], 12);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(this.map);

      this.marker = L.marker([startLat, startLng], { draggable: true }).addTo(this.map);
      this.marker.on('dragend', async (e) => {
        const latlng = e.target.getLatLng();
        await this.updateLocationFromCoords(latlng.lat, latlng.lng);
      });
      this.map.on('click', async (e) => {
        this.marker.setLatLng(e.latlng);
        await this.updateLocationFromCoords(e.latlng.lat, e.latlng.lng);
      });
      this.drawCaviteBoundary();
    },

    async updateLocationFromCoords(lat, lon) {
      this.locationError = '';
      this.idVerification.latitude = lat.toString();
      this.idVerification.longitude = lon.toString();
      
      try {
        const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`);
        if (!response.ok) throw new Error('Failed to fetch address');
        const data = await response.json();
        const addr = data.address;

        const isWater = data.class === 'waterway' || (data.class === 'natural' && data.type === 'water') || (addr && (addr.sea || addr.ocean || addr.bay || addr.water));
        if (isWater) {
            this.locationError = "You cannot pin a water area. Please select a valid land location within Cavite.";
            this.idVerification.city = ''; this.idVerification.barangay = ''; this.idVerification.block_address = ''; this.idVerification.latitude = ''; this.idVerification.longitude = '';
            return;
        }
        
        if (addr) {
          const rawCity = this.getAddressComponent(addr, ['city', 'town', 'municipality', 'village', 'county']);
          const matchedCity = this.caviteCities.find(c => rawCity && (rawCity.toLowerCase().includes(c.toLowerCase()) || c.toLowerCase().includes(rawCity.toLowerCase())));
          if (!matchedCity && addr.state !== 'Cavite') {
             this.locationError = "Location must be within Cavite province.";
             this.idVerification.city = '';
             return;
          }

          this.idVerification.city = matchedCity || rawCity;
          const rawBarangay = this.getAddressComponent(addr, ['quarter', 'neighbourhood', 'suburb', 'hamlet', 'district']);
          this.idVerification.barangay = rawBarangay.replace('Barangay', '').trim();

          const parts = [];
          if (addr.house_number) parts.push(`No. ${addr.house_number}`);
          if (addr.building) parts.push(addr.building);
          const road = this.getAddressComponent(addr, ['road', 'pedestrian', 'highway', 'street']);
          if (road) parts.push(road);
          const subdivision = this.getAddressComponent(addr, ['residential', 'subdivision', 'village', 'allotments']);
          if (subdivision && subdivision !== rawCity && subdivision !== rawBarangay) parts.push(subdivision);
          this.idVerification.block_address = parts.length > 0 ? parts.join(', ') : (road || 'Location pinned on map');
        }
      } catch (error) { console.error('Reverse geocoding error:', error); }
    },

    async getCurrentLocation() {
      if (!navigator.geolocation) { this.locationError = "Geolocation is not supported by your browser"; return; }
      this.gettingLocation = true; this.locationError = "";
      
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          const lat = position.coords.latitude; const lon = position.coords.longitude;
          const caviteBounds = L.latLngBounds([14.0000, 120.5000], [14.6000, 121.1000]);
          if (!caviteBounds.contains([lat, lon])) {
             this.locationError = "Your current device location is outside Cavite. Please pin manually.";
             this.gettingLocation = false; return;
          }

          this.idVerification.latitude = lat.toString(); this.idVerification.longitude = lon.toString();
          if (this.map && this.marker) {
            this.map.flyTo([lat, lon], 16); this.marker.setLatLng([lat, lon]); await this.updateLocationFromCoords(lat, lon);
          } else await this.updateLocationFromCoords(lat, lon);
          this.gettingLocation = false;
        },
        (error) => {
          this.gettingLocation = false;
          switch(error.code) {
            case error.PERMISSION_DENIED: this.locationError = "User denied the request for Geolocation."; break;
            case error.POSITION_UNAVAILABLE: this.locationError = "Location information is unavailable."; break;
            case error.TIMEOUT: this.locationError = "Request timed out."; break;
            default: this.locationError = "An unknown error occurred."; break;
          }
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
      );
    },

    async loadIdVerificationStatus() {
      try {
        const response = await axios.get('/service-provider/requirements')
        if (response.data.status === 'success') {
          const data = response.data.data
          this.idVerification = {
            idType: data.id_type || '', idNumber: data.id_number || '',
            idPhoto: null, idPhotoPreview: null, idPhotoUrl: data.id_photo_url || null,
            selfiePhoto: null, selfiePhotoPreview: null, selfiePhotoUrl: data.selfie_photo_url || null,
            status: data.status || 'not_submitted', submittedAt: data.submitted_at || null, reviewedAt: data.reviewed_at || null, rejectionReason: data.rejection_reason || null,
            resubmission_count: data.resubmission_count || 0,
            city: data.address?.city || '', barangay: data.address?.barangay || '', block_address: data.address?.block_address || '', latitude: data.address?.latitude || '', longitude: data.address?.longitude || '',
          }
          if (['pending', 'verified', 'approved'].includes(this.idVerification.status)) {
            this.currentStep = 1
          } else {
            if (!this.loading && !this.error) this.$nextTick(() => { this.initMap(); });
          }
        }
      } catch (error) { console.error('Error loading ID verification status:', error) }
    },

    handleDragOver(event) { this.isDraggingId = true; event.preventDefault() },
    handleDragLeave() { this.isDraggingId = false },
    handleIdDrop(event) {
      this.isDraggingId = false
      const files = event.dataTransfer.files
      if (files.length > 0 && !['pending', 'verified', 'approved'].includes(this.idVerification.status) && this.idVerification.resubmission_count < 3) this.processIdFile(files[0])
    },

    handleSelfieDragOver(event) { this.isDraggingSelfie = true; event.preventDefault() },
    handleSelfieDragLeave() { this.isDraggingSelfie = false },
    handleSelfieDrop(event) {
      this.isDraggingSelfie = false
      const files = event.dataTransfer.files
      if (files.length > 0 && !['pending', 'verified', 'approved'].includes(this.idVerification.status) && this.idVerification.resubmission_count < 3) this.processSelfieFile(files[0])
    },

    triggerIdUpload() { 
      if (['pending', 'verified', 'approved'].includes(this.idVerification.status) || this.idVerification.resubmission_count >= 3) return
      this.$refs.idFileInput.click() 
    },
    
    triggerSelfieUpload() { 
      if (['pending', 'verified', 'approved'].includes(this.idVerification.status) || this.idVerification.resubmission_count >= 3) return
      this.$refs.selfieFileInput.click() 
    },

    handleIdUploadChange(event) {
      const file = event.target.files[0]
      if (file && !['pending', 'verified', 'approved'].includes(this.idVerification.status) && this.idVerification.resubmission_count < 3) this.processIdFile(file)
    },
    handleSelfieUploadChange(event) {
      const file = event.target.files[0]
      if (file && !['pending', 'verified', 'approved'].includes(this.idVerification.status) && this.idVerification.resubmission_count < 3) this.processSelfieFile(file)
    },

    processIdFile(file) {
      if (!file.type.startsWith('image/')) { this.showNotification('Please upload an image file', 'error'); return }
      if (file.size > 5 * 1024 * 1024) { this.showNotification('File size must be less than 5MB', 'error'); return }
      this.idVerificationErrors.idPhoto = null
      this.idUploadProgress = 0
      const interval = setInterval(() => {
        this.idUploadProgress += 10
        if (this.idUploadProgress >= 100) {
          clearInterval(interval)
          const reader = new FileReader()
          reader.onload = (e) => { this.idVerification.idPhotoPreview = e.target.result; this.idVerification.idPhoto = file; this.idVerification.idPhotoUrl = null; this.showNotification('ID photo uploaded successfully!', 'success') }
          reader.readAsDataURL(file)
        }
      }, 50)
    },

    processSelfieFile(file) {
      if (!file.type.startsWith('image/')) { this.showNotification('Please upload an image file', 'error'); return }
      if (file.size > 5 * 1024 * 1024) { this.showNotification('File size must be less than 5MB', 'error'); return }
      this.idVerificationErrors.selfiePhoto = null
      const reader = new FileReader()
      reader.onload = (e) => { this.idVerification.selfiePhotoPreview = e.target.result; this.idVerification.selfiePhoto = file; this.idVerification.selfiePhotoUrl = null; this.showNotification('Selfie uploaded successfully!', 'success') }
      reader.readAsDataURL(file)
    },

    removeIdPhoto() { this.idVerification.idPhoto = null; this.idVerification.idPhotoPreview = null; this.idVerification.idPhotoUrl = null; this.idUploadProgress = 0 },
    removeSelfiePhoto() { this.idVerification.selfiePhoto = null; this.idVerification.selfiePhotoPreview = null; this.idVerification.selfiePhotoUrl = null },

    async submitIdVerification() {
      if (!this.canSubmitIdVerification || this.idVerificationLoading) return
      this.idVerificationLoading = true; this.idVerificationErrors = {}

      if (!this.idVerification.idType) { this.idVerificationErrors.idType = 'Please select an ID type'; this.idVerificationLoading = false; return }
      if (!this.idVerification.idNumber.trim()) { this.idVerificationErrors.idNumber = 'Please enter your ID number'; this.idVerificationLoading = false; return }

      try {
        const formData = new FormData()
        formData.append('id_type', this.idVerification.idType); formData.append('id_number', this.idVerification.idNumber)
        
        // Append photos if they are new files, otherwise the backend will keep the existing ones
        if (this.idVerification.idPhoto) formData.append('id_photo', this.idVerification.idPhoto)
        if (this.idVerification.selfiePhoto) formData.append('selfie_photo', this.idVerification.selfiePhoto)
        
        formData.append('province', 'Cavite'); formData.append('city', this.idVerification.city)
        formData.append('barangay', this.idVerification.barangay); formData.append('block_address', this.idVerification.block_address)
        formData.append('latitude', this.idVerification.latitude); formData.append('longitude', this.idVerification.longitude)

        const response = await axios.post('/service-provider/requirements', formData, { headers: { 'Content-Type': 'multipart/form-data' } })
        if (response.data.status === 'success') {
          this.idVerification.status = 'pending'
          this.idVerification.submittedAt = response.data.data.submitted_at
          this.idVerification.idPhotoUrl = response.data.data.id_photo_url
          this.idVerification.selfiePhotoUrl = response.data.data.selfie_photo_url
          this.idVerification.resubmission_count = response.data.data.resubmission_count
          this.idVerification.idPhoto = null; this.idVerification.selfiePhoto = null
          this.currentStep = 1
          this.showNotification('Verification submitted successfully!', 'success')
        }
      } catch (error) {
        if (error.response?.status === 422) {
          this.idVerificationErrors = error.response.data.errors || {}
          this.showNotification('Please fix the validation errors', 'error')
        } else this.showNotification(error.response?.data?.message || 'Failed to submit verification', 'error')
      } finally { this.idVerificationLoading = false }
    }
  }
}
</script>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.3s ease-out forwards;
}

/* Chat transition animations */
.fade-slide-enter-active, .fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from, .fade-slide-leave-to {
  opacity: 0;
  transform: translateY(20px) scale(0.95);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5);
}
::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
}
</style>