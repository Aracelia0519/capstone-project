<template>
  <div class="min-h-screen text-slate-50 pb-20">
    <Toaster position="top-right" theme="dark" :richColors="true" />

    <header class="sticky top-0 z-40  backdrop-blur-md border-b border-gray-800 shadow-xl">
      <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-gradient-to-br from-gray-600 to-gray-500 rounded-xl shadow-xl">
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
                ? 'bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 text-white shadow-lg hover:shadow-blue-500/25 border-0' 
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

    <div v-if="loading" class="flex justify-center items-center min-h-[400px]">
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

    <main v-else class="px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-1 space-y-6">
          <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl overflow-hidden">
            <CardContent class="pt-8 pb-6 px-6 text-center">
              <div class="relative inline-block mb-4">
                <Avatar class="w-32 h-32 border-4 border-gray-800 shadow-2xl">
                  <AvatarImage src="" alt="User Avatar" /> <AvatarFallback class="bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 text-4xl font-bold text-white">
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

          <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl">
            <CardHeader class="pb-4">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-r from-yellow-500 to-amber-400 rounded-lg">
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

        <div class="lg:col-span-2 space-y-6">
          
          <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl">
            <CardHeader>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-lg">
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
                  <Label class="text-gray-300">Address</Label>
                  <Textarea 
                    v-model="user.address" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500 min-h-[80px]" 
                    placeholder="Enter your address" 
                  />
                  <p v-if="validationErrors.address" class="text-xs text-red-400">{{ validationErrors.address[0] }}</p>
                </div>

                <div class="md:col-span-2 space-y-2">
                  <Label class="text-gray-300">Bio / Professional Summary</Label>
                  <Textarea 
                    v-model="user.bio" 
                    @input="markChanged"
                    class="bg-gray-800 border-gray-700 text-white focus:border-blue-500 min-h-[100px]" 
                    placeholder="Tell us about yourself..." 
                  />
                  <p class="text-xs text-gray-500">Note: Bio field is stored locally until saved.</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl overflow-hidden">
             <CardHeader>
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-gradient-to-r from-indigo-500 to-purple-400 rounded-lg">
                    <FileBadge class="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <h2 class="text-xl font-bold text-white">ID Verification</h2>
                    <p class="text-gray-400 text-sm">Complete identity verification in 3 easy steps</p>
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
              <div v-if="idVerification.status === 'verified'" class="bg-green-900/20 border border-green-800 rounded-xl p-4 mb-6">
                <div class="flex items-center gap-3">
                  <CheckCircle2 class="w-6 h-6 text-green-400" />
                  <div>
                    <h3 class="font-semibold text-green-400">ID Verified</h3>
                    <p class="text-gray-300 text-sm">Your ID has been verified and approved.</p>
                  </div>
                </div>
              </div>

               <div v-if="idVerification.status !== 'verified'" class="mb-8 px-2">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                  <div class="flex items-center gap-4 w-full md:w-auto">
                    <div class="flex flex-col items-center">
                      <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                           :class="currentStep >= 1 ? 'bg-gradient-to-r from-indigo-500 to-purple-500 border-indigo-500' : 'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white">1</span>
                      </div>
                      <span class="mt-2 text-sm" :class="currentStep >= 1 ? 'text-indigo-400 font-medium' : 'text-gray-500'">ID Details</span>
                    </div>
                    
                    <div class="hidden md:block w-16 h-1 rounded-full" :class="currentStep >= 2 ? 'bg-gradient-to-r from-purple-500 to-purple-400' : 'bg-gray-700'"></div>
                    
                    <div class="flex flex-col items-center">
                      <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                           :class="currentStep >= 2 ? 'bg-gradient-to-r from-purple-500 to-purple-400 border-purple-500' : 'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white">2</span>
                      </div>
                      <span class="mt-2 text-sm" :class="currentStep >= 2 ? 'text-purple-400 font-medium' : 'text-gray-500'">ID Photo</span>
                    </div>
                    
                    <div class="hidden md:block w-16 h-1 rounded-full" :class="currentStep >= 3 ? 'bg-gradient-to-r from-purple-400 to-purple-300' : 'bg-gray-700'"></div>
                    
                    <div class="flex flex-col items-center">
                      <div class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                           :class="currentStep >= 3 ? 'bg-gradient-to-r from-purple-400 to-purple-300 border-purple-400' : 'bg-gray-800 border-gray-700'">
                        <span class="font-bold text-white">3</span>
                      </div>
                      <span class="mt-2 text-sm" :class="currentStep >= 3 ? 'text-purple-300 font-medium' : 'text-gray-500'">Selfie</span>
                    </div>
                  </div>
                  <div class="text-sm text-gray-400">Step {{ currentStep }} of 3</div>
                </div>
              </div>

               <div v-if="idVerification.status !== 'verified'" class="space-y-6">
                <div v-show="currentStep === 1" class="space-y-6 animate-fade-in">
                  <div class="bg-gradient-to-br from-indigo-900/20 to-purple-900/20 border border-indigo-800/30 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                       <CreditCard class="w-5 h-5 text-indigo-400" /> Step 1: ID Information
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                      <div class="space-y-2">
                        <Label class="text-gray-300">Select ID Type <span class="text-red-400">*</span></Label>
                         <Select v-model="idVerification.idType" :disabled="idVerification.status === 'pending'">
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
                          :disabled="idVerification.status === 'pending'"
                          class="bg-gray-800 border-gray-700 text-white"
                          placeholder="Enter ID number"
                        />
                         <p v-if="idVerificationErrors.idNumber" class="text-xs text-red-400">{{ idVerificationErrors.idNumber }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-show="currentStep === 2" class="space-y-6 animate-fade-in">
                   <div class="bg-gradient-to-br from-purple-900/20 to-pink-900/20 border border-purple-800/30 rounded-xl p-6">
                     <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                       <Image class="w-5 h-5 text-purple-400" /> Step 2: ID Photo Upload
                     </h3>
                     
                     <div 
                        @dragover.prevent="handleDragOver"
                        @dragleave="handleDragLeave"
                        @drop.prevent="handleIdDrop"
                        @click="idVerification.status !== 'pending' ? triggerIdUpload() : null"
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="[
                          idUploadClasses,
                          idVerification.status === 'pending' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-purple-900/10'
                        ]"
                      >
                        <input type="file" ref="idFileInput" @change="handleIdUploadChange" accept="image/*" class="hidden" :disabled="idVerification.status === 'pending'"/>
                        
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
                             <button v-if="idVerification.status !== 'pending'" @click.stop="removeIdPhoto" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow-md">
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

                <div v-show="currentStep === 3" class="space-y-6 animate-fade-in">
                  <div class="bg-gradient-to-br from-pink-900/20 to-rose-900/20 border border-pink-800/30 rounded-xl p-6">
                     <h3 class="text-lg font-semibold text-white mb-6 flex items-center gap-2">
                       <Camera class="w-5 h-5 text-pink-400" /> Step 3: Selfie with ID
                     </h3>

                     <div 
                        @dragover.prevent="handleSelfieDragOver"
                        @dragleave="handleSelfieDragLeave"
                        @drop.prevent="handleSelfieDrop"
                        @click="idVerification.status !== 'pending' ? triggerSelfieUpload() : null"
                        class="relative border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300"
                        :class="[
                          selfieUploadClasses,
                          idVerification.status === 'pending' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-pink-900/10'
                        ]"
                      >
                        <input type="file" ref="selfieFileInput" @change="handleSelfieUploadChange" accept="image/*" class="hidden" :disabled="idVerification.status === 'pending'"/>
                        
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
                             <button v-if="idVerification.status !== 'pending'" @click.stop="removeSelfiePhoto" class="absolute -top-2 -right-2 bg-red-600 hover:bg-red-700 text-white rounded-full p-1.5 shadow-md">
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
                    <Loader2 class="w-8 h-8 text-yellow-400 animate-spin flex-shrink-0" />
                    <div>
                      <h3 class="font-semibold text-yellow-400 mb-1">Verification Under Review</h3>
                      <p class="text-gray-300 text-sm">Your ID verification is being reviewed. This usually takes 1-2 business days.</p>
                    </div>
                  </div>
                </div>

                <div v-if="idVerification.status !== 'pending' && idVerification.status !== 'verified'" class="flex justify-between pt-6 border-t border-gray-800">
                  <Button 
                    @click="prevStep" 
                    v-show="currentStep > 1" 
                    variant="secondary"
                    class="bg-gray-800 hover:bg-gray-700 text-gray-300"
                  >
                    <ChevronLeft class="w-4 h-4 mr-2" /> Previous
                  </Button>
                  
                  <div class="ml-auto flex gap-4">
                     <Button 
                        v-if="currentStep < 3" 
                        @click="nextStep" 
                        :disabled="!canProceedToNextStep"
                        class="bg-gradient-to-r from-indigo-600 to-purple-500 hover:from-indigo-700 hover:to-purple-600 text-white border-0"
                      >
                        Next Step <ChevronRight class="w-4 h-4 ml-2" />
                      </Button>

                      <Button 
                        v-if="currentStep === 3" 
                        @click="submitIdVerification" 
                        :disabled="!canSubmitIdVerification || idVerificationLoading"
                        class="bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white border-0"
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

          <Card v-if="showChangePassword" class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl transition-all duration-300">
            <CardHeader>
               <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-gradient-to-r from-green-500 to-emerald-400 rounded-lg">
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
                  class="w-full mt-2 bg-gradient-to-r from-green-600 to-emerald-500 hover:from-green-700 hover:to-emerald-600 text-white border-0"
                >
                   <Loader2 v-if="passwordLoading" class="w-4 h-4 animate-spin mr-2" />
                   {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                </Button>
              </div>
            </CardContent>
          </Card>

        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { getCurrentUser, clearAuthData } from '@/utils/auth'
import axios from '@/utils/axios'
import { Toaster, toast } from 'vue-sonner'

// Shadcn UI Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { 
  Select, 
  SelectContent, 
  SelectItem, 
  SelectTrigger, 
  SelectValue 
} from '@/components/ui/select'
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
  ChevronLeft, ChevronRight, Lock, Eye, EyeOff
} from 'lucide-vue-next'

export default {
  name: 'ProfileSettingsPage',
  components: {
    Toaster,
    Button, Input, Label, Textarea,
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    Card, CardContent, CardHeader, CardTitle, CardDescription,
    Avatar, AvatarFallback, AvatarImage,
    Badge, Progress, Separator,
    // Icons
    UserCog, ShieldCheck, Save, RotateCcw, Loader2, AlertCircle,
    Camera, Shield, CheckCircle2, AlertTriangle, Key, User,
    FileBadge, Check, X, CreditCard, Image, UploadCloud,
    ChevronLeft, ChevronRight, Lock, Eye, EyeOff
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
        id: null,
        first_name: '',
        last_name: '',
        full_name: '',
        name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: '',
        email_verified_at: null,
        created_at: '',
        updated_at: '',
        bio: ''
      },
      originalUser: null,
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      securityStatus: {
        passwordLastChanged: 'Unknown'
      },
      stats: {
        completedJobs: 0,
        satisfaction: 0,
        activeProjects: 0
      },
      // ID Verification
      idVerification: {
        idType: '',
        idNumber: '',
        idPhoto: null,
        idPhotoPreview: null,
        idPhotoUrl: null,
        selfiePhoto: null,
        selfiePhotoPreview: null,
        selfiePhotoUrl: null,
        status: 'not_submitted',
        submittedAt: null,
        reviewedAt: null,
        rejectionReason: null
      },
      idVerificationErrors: {},
      isDraggingId: false,
      isDraggingSelfie: false,
      idUploadProgress: 0,
      currentStep: 1
    }
  },
  computed: {
    userInitials() {
      if (this.user.first_name && this.user.last_name) {
        return (this.user.first_name.charAt(0) + this.user.last_name.charAt(0)).toUpperCase()
      }
      if (this.user.name) {
        const names = this.user.name.split(' ')
        return names.length >= 2 
          ? (names[0].charAt(0) + names[names.length - 1].charAt(0)).toUpperCase()
          : names[0].charAt(0).toUpperCase()
      }
      return '?'
    },
    passwordMismatch() {
      return this.password.new && this.password.confirm && this.password.new !== this.password.confirm
    },
    canChangePassword() {
      return this.password.current && 
             this.password.new && 
             this.password.confirm && 
             !this.passwordMismatch &&
             this.password.new.length >= 8 &&
             /[A-Z]/.test(this.password.new) &&
             /[0-9]/.test(this.password.new) &&
             /[^A-Za-z0-9]/.test(this.password.new)
    },
    statusClasses() {
      const status = this.user.status || 'active'
      switch (status) {
        case 'active':
          return 'bg-green-900/30 text-green-400 border-green-800'
        case 'pending':
          return 'bg-yellow-900/30 text-yellow-400 border-yellow-800'
        case 'inactive':
          return 'bg-red-900/30 text-red-400 border-red-800'
        default:
          return 'bg-gray-800/30 text-gray-400 border-gray-700'
      }
    },
    statusDotClasses() {
      const status = this.user.status || 'active'
      switch (status) {
        case 'active': return 'bg-green-400'
        case 'pending': return 'bg-yellow-400'
        case 'inactive': return 'bg-red-400'
        default: return 'bg-gray-400'
      }
    },
    idVerificationStatus() {
      switch (this.idVerification.status) {
        case 'pending': return 'Pending Verification'
        case 'verified': return 'Verified'
        case 'rejected': return 'Rejected - Please resubmit'
        default: return 'Not Submitted'
      }
    },
    idVerificationStatusClass() {
      switch (this.idVerification.status) {
        case 'pending': return 'text-yellow-400 font-medium'
        case 'verified': return 'text-green-400 font-medium'
        case 'rejected': return 'text-red-400 font-medium'
        default: return 'text-gray-500'
      }
    },
    idUploadClasses() {
      const base = 'border-gray-700'
      if (this.isDraggingId) {
        return `${base} border-purple-500 bg-purple-900/20`
      }
      return base
    },
    selfieUploadClasses() {
      const base = 'border-gray-700'
      if (this.isDraggingSelfie) {
        return `${base} border-pink-500 bg-pink-900/20`
      }
      return base
    },
    canSubmitIdVerification() {
      return this.idVerification.idType && 
             this.idVerification.idNumber && 
             (this.idVerification.idPhoto || this.idVerification.idPhotoUrl) && 
             (this.idVerification.selfiePhoto || this.idVerification.selfiePhotoUrl) &&
             this.idVerification.status !== 'pending' &&
             this.idVerification.status !== 'verified'
    },
    canProceedToNextStep() {
      switch (this.currentStep) {
        case 1: return this.idVerification.idType && this.idVerification.idNumber.trim()
        case 2: return this.idVerification.idPhoto || this.idVerification.idPhotoUrl
        case 3: return this.idVerification.selfiePhoto || this.idVerification.selfiePhotoUrl
        default: return false
      }
    }
  },
  created() {
    this.fetchUserProfile()
    this.loadIdVerificationStatus()
  },
  methods: {
    // Replaced custom notification with Sonner
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
      } catch (e) {
        return 'Unknown'
      }
    },
    
    formatRole(role) {
      const roles = {
        'client': 'Client Account',
        'distributor': 'Distributor Account',
        'service_provider': 'Service Provider Account',
        'admin': 'Administrator'
      }
      return roles[role] || (role ? role.replace('_', ' ') : 'Service Provider')
    },
    
    markChanged() {
      if (!this.originalUser) return
      
      const current = JSON.stringify({
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        phone: this.user.phone,
        address: this.user.address,
        bio: this.user.bio
      })
      
      const original = JSON.stringify({
        first_name: this.originalUser.first_name,
        last_name: this.originalUser.last_name,
        email: this.originalUser.email,
        phone: this.originalUser.phone,
        address: this.originalUser.address,
        bio: this.originalUser.bio
      })
      
      this.hasChanges = current !== original
    },
    
    async saveProfile() {
      if (!this.hasChanges || this.isLoading) return
      
      this.isLoading = true
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
          if (this.user.bio && this.user.id) {
            localStorage.setItem(`user_bio_${this.user.id}`, this.user.bio)
          }
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
          this.showNotification(error.response?.data?.message || 'Failed to update profile', 'error')
        }
      } finally {
        this.isLoading = false
      }
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
        const passwordData = {
          current_password: this.password.current,
          password: this.password.new,
          password_confirmation: this.password.confirm
        }
        
        const response = await axios.put('/profile/password', passwordData)
        
        if (response.data.status === 'success') {
          this.password = { current: '', new: '', confirm: '' }
          this.showChangePassword = false
          this.securityStatus.passwordLastChanged = 'Just now'
          this.showNotification('Password changed successfully!', 'success')
        } else {
          throw new Error('Password change failed')
        }
      } catch (error) {
        console.error('Error changing password:', error)
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.passwordError = error.response.data.message || 'Password change failed'
          this.showNotification(this.passwordError, 'error')
        } else {
          this.passwordError = error.response?.data?.message || error.message || 'Failed to change password'
          this.showNotification(this.passwordError, 'error')
        }
      } finally {
        this.passwordLoading = false
      }
    },
    
    editAvatar() {
      this.showNotification('Avatar upload feature will be available soon', 'info')
    },

    // Wizard Methods
    nextStep() {
      if (this.currentStep < 3 && this.canProceedToNextStep) this.currentStep++
    },
    prevStep() {
      if (this.currentStep > 1) this.currentStep--
    },

    async loadIdVerificationStatus() {
      try {
        const response = await axios.get('/service-provider/requirements')
        if (response.data.status === 'success') {
          const data = response.data.data
          this.idVerification = {
            idType: data.id_type || '',
            idNumber: data.id_number || '',
            idPhoto: null,
            idPhotoPreview: null,
            idPhotoUrl: data.id_photo_url || null,
            selfiePhoto: null,
            selfiePhotoPreview: null,
            selfiePhotoUrl: data.selfie_photo_url || null,
            status: data.status || 'not_submitted',
            submittedAt: data.submitted_at || null,
            reviewedAt: data.reviewed_at || null,
            rejectionReason: data.rejection_reason || null
          }
          if (this.idVerification.status === 'pending' || this.idVerification.status === 'verified') {
            this.currentStep = 1
          }
        }
      } catch (error) {
        console.error('Error loading ID verification status:', error)
      }
    },

    handleDragOver(event) {
      this.isDraggingId = true
      event.preventDefault()
    },
    handleDragLeave() { this.isDraggingId = false },
    handleIdDrop(event) {
      this.isDraggingId = false
      const files = event.dataTransfer.files
      if (files.length > 0 && this.idVerification.status !== 'pending') this.processIdFile(files[0])
    },

    handleSelfieDragOver(event) {
      this.isDraggingSelfie = true
      event.preventDefault()
    },
    handleSelfieDragLeave() { this.isDraggingSelfie = false },
    handleSelfieDrop(event) {
      this.isDraggingSelfie = false
      const files = event.dataTransfer.files
      if (files.length > 0 && this.idVerification.status !== 'pending') this.processSelfieFile(files[0])
    },

    triggerIdUpload() {
      if (this.idVerification.status !== 'pending') this.$refs.idFileInput.click()
    },
    triggerSelfieUpload() {
      if (this.idVerification.status !== 'pending') this.$refs.selfieFileInput.click()
    },

    handleIdUploadChange(event) {
      const file = event.target.files[0]
      if (file && this.idVerification.status !== 'pending') this.processIdFile(file)
    },
    handleSelfieUploadChange(event) {
      const file = event.target.files[0]
      if (file && this.idVerification.status !== 'pending') this.processSelfieFile(file)
    },

    processIdFile(file) {
      if (!file.type.startsWith('image/')) {
        this.showNotification('Please upload an image file', 'error')
        return
      }
      if (file.size > 5 * 1024 * 1024) {
        this.showNotification('File size must be less than 5MB', 'error')
        return
      }
      this.idVerificationErrors.idPhoto = null
      this.idUploadProgress = 0
      const interval = setInterval(() => {
        this.idUploadProgress += 10
        if (this.idUploadProgress >= 100) {
          clearInterval(interval)
          const reader = new FileReader()
          reader.onload = (e) => {
            this.idVerification.idPhotoPreview = e.target.result
            this.idVerification.idPhoto = file
            this.idVerification.idPhotoUrl = null
            this.showNotification('ID photo uploaded successfully!', 'success')
          }
          reader.readAsDataURL(file)
        }
      }, 50)
    },

    processSelfieFile(file) {
      if (!file.type.startsWith('image/')) {
        this.showNotification('Please upload an image file', 'error')
        return
      }
      if (file.size > 5 * 1024 * 1024) {
        this.showNotification('File size must be less than 5MB', 'error')
        return
      }
      this.idVerificationErrors.selfiePhoto = null
      const reader = new FileReader()
      reader.onload = (e) => {
        this.idVerification.selfiePhotoPreview = e.target.result
        this.idVerification.selfiePhoto = file
        this.idVerification.selfiePhotoUrl = null
        this.showNotification('Selfie uploaded successfully!', 'success')
      }
      reader.readAsDataURL(file)
    },

    removeIdPhoto() {
      this.idVerification.idPhoto = null
      this.idVerification.idPhotoPreview = null
      this.idVerification.idPhotoUrl = null
      this.idUploadProgress = 0
    },
    removeSelfiePhoto() {
      this.idVerification.selfiePhoto = null
      this.idVerification.selfiePhotoPreview = null
      this.idVerification.selfiePhotoUrl = null
    },

    async submitIdVerification() {
      if (!this.canSubmitIdVerification || this.idVerificationLoading) return
      this.idVerificationLoading = true
      this.idVerificationErrors = {}

      if (!this.idVerification.idType) {
        this.idVerificationErrors.idType = 'Please select an ID type'
        this.idVerificationLoading = false
        return
      }
      if (!this.idVerification.idNumber.trim()) {
        this.idVerificationErrors.idNumber = 'Please enter your ID number'
        this.idVerificationLoading = false
        return
      }

      try {
        const formData = new FormData()
        formData.append('id_type', this.idVerification.idType)
        formData.append('id_number', this.idVerification.idNumber)
        formData.append('id_photo', this.idVerification.idPhoto)
        formData.append('selfie_photo', this.idVerification.selfiePhoto)

        const response = await axios.post('/service-provider/requirements', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (response.data.status === 'success') {
          this.idVerification.status = 'pending'
          this.idVerification.submittedAt = response.data.data.submitted_at
          this.idVerification.idPhotoUrl = response.data.data.id_photo_url
          this.idVerification.selfiePhotoUrl = response.data.data.selfie_photo_url
          this.idVerification.idPhoto = null
          this.idVerification.selfiePhoto = null
          this.currentStep = 1
          this.showNotification('ID verification submitted successfully!', 'success')
        }
      } catch (error) {
        console.error('Error submitting ID verification:', error)
        if (error.response?.status === 422) {
          this.idVerificationErrors = error.response.data.errors || {}
          this.showNotification('Please fix the validation errors', 'error')
        } else {
          this.showNotification(error.response?.data?.message || 'Failed to submit verification', 'error')
        }
      } finally {
        this.idVerificationLoading = false
      }
    }
  },
  mounted() {
    // Retaining entrance animations
    setTimeout(() => {
      const cards = document.querySelectorAll('.bg-gradient-to-br')
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
  }
}
</script>

<style scoped>
/* Key animations from original file */
@keyframes fade-in {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in {
  animation: fade-in 0.3s ease-out forwards;
}

/* Custom scrollbar to match original */
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