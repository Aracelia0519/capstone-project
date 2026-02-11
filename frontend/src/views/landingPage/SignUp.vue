[file name]: SignUp.vue
<template>
  <div ref="pageContainer" class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4 overflow-hidden">
    <!-- Animated Paint Background -->
    <div class="absolute inset-0 overflow-hidden">
      <!-- Paint splatter effects -->
      <div class="absolute top-1/4 left-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-blue-500/20 to-cyan-400/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute top-12 left-12 w-32 h-32 bg-gradient-to-r from-purple-500/15 to-pink-500/15 rounded-full blur-2xl animate-float-medium"></div>
      </div>
      
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-emerald-500/15 to-teal-400/15 rounded-full blur-3xl animate-float-slow delay-1000"></div>
        <div class="absolute bottom-12 right-12 w-32 h-32 bg-gradient-to-r from-amber-500/10 to-yellow-400/10 rounded-full blur-2xl animate-float-medium delay-500"></div>
      </div>

      <!-- Paint brush strokes -->
      <div class="absolute top-1/3 right-1/3 w-64 h-96 opacity-10">
        <div class="absolute w-8 h-40 bg-gradient-to-b from-blue-400 to-transparent rounded-full transform rotate-45 animate-brush-stroke-1"></div>
      </div>
      
      <div class="absolute bottom-1/3 left-1/3 w-64 h-96 opacity-10">
        <div class="absolute w-8 h-32 bg-gradient-to-t from-purple-400 to-transparent rounded-full transform -rotate-12 animate-brush-stroke-2"></div>
      </div>

      <!-- Floating paint droplets -->
      <div class="absolute top-20 left-20 w-8 h-8 bg-gradient-to-r from-blue-400/30 to-cyan-300/30 rounded-full animate-bounce-droplet"></div>
      <div class="absolute top-40 right-40 w-6 h-6 bg-gradient-to-r from-purple-400/30 to-pink-300/30 rounded-full animate-bounce-droplet delay-300"></div>
      <div class="absolute bottom-20 left-32 w-7 h-7 bg-gradient-to-r from-emerald-400/30 to-teal-300/30 rounded-full animate-bounce-droplet delay-700"></div>
      <div class="absolute bottom-40 right-20 w-5 h-5 bg-gradient-to-r from-amber-400/30 to-yellow-300/30 rounded-full animate-bounce-droplet delay-1000"></div>

      <!-- Color palette circles -->
      <div class="absolute top-10 right-10 w-24 h-24 opacity-5">
        <div class="absolute inset-0 border-2 border-blue-400/30 rounded-full"></div>
        <div class="absolute inset-4 border-2 border-purple-400/30 rounded-full"></div>
        <div class="absolute inset-8 border-2 border-emerald-400/30 rounded-full"></div>
        <div class="absolute inset-12 border-2 border-amber-400/30 rounded-full"></div>
      </div>

      <div class="absolute bottom-10 left-10 w-32 h-32 opacity-5">
        <div class="absolute inset-0 border-2 border-cyan-400/30 rounded-full"></div>
        <div class="absolute inset-6 border-2 border-pink-400/30 rounded-full"></div>
        <div class="absolute inset-12 border-2 border-teal-400/30 rounded-full"></div>
        <div class="absolute inset-18 border-2 border-yellow-400/30 rounded-full"></div>
      </div>
    </div>

    <!-- Wider Signup Card with wizard steps -->
    <div ref="signupCard" class="relative w-full max-w-5xl z-10 opacity-0 translate-y-10 mx-auto my-8 md:my-12">
      <div class="flex flex-col lg:flex-row bg-gray-800/30 backdrop-blur-2xl rounded-3xl shadow-2xl border border-gray-700/30 overflow-hidden transform transition-all duration-700 hover:scale-[1.01] mx-4">
        <!-- Left Side - Wizard Progress & Branding -->
        <div class="lg:w-2/5 p-8 lg:p-12 bg-gradient-to-br from-blue-900/20 via-purple-900/20 to-pink-900/20 flex flex-col justify-center items-center relative overflow-hidden">
          <!-- Animated gradient overlay -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5"></div>
          
          <!-- Paint Brand Logo -->
          <div class="relative mb-6">
            <div class="w-24 h-24 rounded-3xl bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 shadow-2xl flex items-center justify-center mb-4 transform transition-all duration-500 hover:rotate-12 hover:scale-110">
              <svg class="w-12 h-12 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
            </div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 bg-clip-text text-transparent text-center">
              CaviteGo Paint
            </h1>
            <p class="text-gray-300/80 text-center mt-2">Join Our Colorful Community</p>
          </div>

          <!-- Wizard Progress Steps -->
          <div class="w-full max-w-xs">
            <!-- Progress Bar -->
            <div class="relative mb-8">
              <div class="h-1 bg-gray-700/50 rounded-full overflow-hidden">
                <div class="progress-bar h-full rounded-full transition-all duration-500" 
                     :style="{ width: `${(currentStep / steps.length) * 100}%` }"></div>
              </div>
              
              <!-- Step Indicators -->
              <div class="flex justify-between -mt-3.5">
                <div v-for="(step, index) in steps" :key="step.id" class="flex flex-col items-center">
                  <button
                    @click="goToStep(index + 1)"
                    :disabled="index + 1 > currentStep"
                    class="relative step-indicator rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110 disabled:cursor-not-allowed"
                    :class="[
                      index + 1 < currentStep ? 'completed' : 
                      index + 1 === currentStep ? 'active' : 'pending'
                    ]"
                  >
                    <span v-if="index + 1 < currentStep" class="text-white text-sm font-semibold">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                      </svg>
                    </span>
                    <span v-else class="text-white text-sm font-semibold">
                      {{ index + 1 }}
                    </span>
                  </button>
                  <span class="step-label mt-2 text-xs font-medium"
                        :class="index + 1 === currentStep ? 'text-white' : 'text-gray-400'">
                    {{ step.title }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Current Step Description -->
            <div class="p-4 rounded-xl bg-gradient-to-br from-gray-900/40 to-gray-800/40 border border-gray-700/30 backdrop-blur-sm mb-6">
              <h3 class="text-white font-semibold mb-2">{{ steps[currentStep - 1].title }}</h3>
              <p class="text-gray-300 text-sm leading-relaxed">
                {{ steps[currentStep - 1].description }}
              </p>
              <div v-if="steps[currentStep - 1].tips" class="mt-3">
                <p class="text-xs text-gray-400 italic">{{ steps[currentStep - 1].tips }}</p>
              </div>
            </div>

            <!-- Role Preview (Visible on all steps) -->
            <div v-if="form.role" class="mt-4 p-4 rounded-xl bg-gradient-to-br from-blue-900/20 to-purple-900/20 border border-blue-700/30 backdrop-blur-sm">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                     :class="getRoleGradient(form.role)">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path v-for="path in getRoleIcon(form.role)" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-white">{{ getRoleLabel(form.role) }}</p>
                  <p class="text-xs text-gray-300">Selected Role</p>
                </div>
              </div>
            </div>

            <!-- Benefits Card -->
            <div class="mt-6 p-4 rounded-xl bg-gradient-to-br from-emerald-900/20 to-teal-900/20 border border-emerald-700/30 backdrop-blur-sm">
              <div class="flex items-center space-x-3 mb-3">
                <div class="p-2 rounded-lg bg-gradient-to-r from-emerald-500/20 to-teal-400/20">
                  <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                  </svg>
                </div>
                <h4 class="text-sm font-semibold text-white">Secure & Step-by-Step</h4>
              </div>
              <p class="text-xs text-gray-300 leading-relaxed">
                Complete registration in {{ steps.length }} easy steps. Your information is protected with encryption.
              </p>
            </div>
          </div>

          <!-- Brand Tagline -->
          <div class="mt-8 text-center">
            <p class="text-gray-400/70 text-sm italic">"Color Your World With Us"</p>
          </div>
        </div>

        <!-- Right Side - Wizard Steps Content -->
        <div class="lg:w-3/5 p-8 lg:p-12">
          <!-- Wizard Header -->
          <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white mb-2">Create Your Account</h2>
            <p class="text-gray-400">Step {{ currentStep }} of {{ steps.length }}: {{ steps[currentStep - 1].title }}</p>
          </div>

          <!-- Wizard Steps Content -->
          <div class="step-content relative min-h-[500px]">
            <!-- Step 1: Role Selection -->
            <div v-if="currentStep === 1" key="step1" class="step-enter-active">
              <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-white mb-3">Choose Your Role</h3>
                <p class="text-gray-300/80 max-w-md mx-auto">Select how you'll use CaviteGo Paint. Your choice affects available features.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div 
                  v-for="role in roles" 
                  :key="role.value"
                  @click="selectRole(role.value)"
                  class="cursor-pointer group"
                >
                  <div class="h-full p-6 rounded-2xl border-2 transition-all duration-300 transform hover:scale-[1.02] hover:-translate-y-1"
                       :class="[
                         form.role === role.value 
                           ? 'border-opacity-100 scale-[1.02] -translate-y-1 shadow-2xl' 
                           : 'border-gray-700/50 hover:border-opacity-70'
                       ]"
                       :style="{
                         borderColor: form.role === role.value ? getRoleBorderColor(role.value) : 'transparent',
                         background: form.role === role.value 
                           ? `linear-gradient(135deg, ${getRoleGradientColor(role.value, 0.1)}, rgba(17, 24, 39, 0.5))`
                           : 'linear-gradient(135deg, rgba(31, 41, 55, 0.5), rgba(17, 24, 39, 0.3))'
                       }">
                    <!-- Role Icon -->
                    <div class="w-16 h-16 rounded-xl flex items-center justify-center mx-auto mb-4 transform transition-all duration-300 group-hover:scale-110"
                         :class="role.gradient">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path v-for="path in role.icon" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                      </svg>
                    </div>
                    
                    <!-- Role Title -->
                    <h4 class="text-lg font-semibold text-white text-center mb-2">{{ role.label }}</h4>
                    
                    <!-- Role Description -->
                    <p class="text-gray-300 text-sm text-center mb-4">{{ role.description }}</p>
                    
                    <!-- Features -->
                    <ul class="space-y-2 mb-6">
                      <li v-for="feature in role.features" :key="feature" class="flex items-center text-xs text-gray-300">
                        <svg class="w-4 h-4 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ feature }}
                      </li>
                    </ul>
                    
                    <!-- Selected Indicator -->
                    <div v-if="form.role === role.value" class="text-center">
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                            :class="getRoleBadgeClass(role.value)">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Selected
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-8 p-4 rounded-xl bg-gradient-to-r from-blue-900/20 to-purple-900/20 border border-blue-700/30">
                <div class="flex items-start space-x-3">
                  <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <div>
                    <p class="text-sm text-white font-medium mb-1">Need help choosing?</p>
                    <p class="text-xs text-gray-300">
                      Clients purchase paints, Distributors sell products, and Service Providers offer painting services. 
                      You can update your role later with verification.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Personal Information -->
            <div v-else-if="currentStep === 2" key="step2" class="step-enter-active">
              <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-white mb-3">Personal Information</h3>
                <p class="text-gray-300/80 max-w-md mx-auto">Tell us about yourself. This helps us personalize your experience.</p>
              </div>

              <div class="space-y-6">
                <!-- Name Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- First Name -->
                  <div class="group">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="group-hover:text-blue-300 transition-colors">First Name</span>
                      </div>
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.firstName"
                        type="text"
                        required
                        placeholder="John"
                        class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all duration-300 group-hover:border-blue-500/30"
                        :class="validationErrors.firstName ? 'border-red-500/50 focus:ring-red-500/30 validation-error' : ''"
                        @input="validateStep2"
                      />
                      <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <div v-if="validationErrors.firstName" class="mt-1 text-xs text-red-400">
                      {{ validationErrors.firstName }}
                    </div>
                  </div>

                  <!-- Last Name -->
                  <div class="group">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="group-hover:text-purple-300 transition-colors">Last Name</span>
                      </div>
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.lastName"
                        type="text"
                        required
                        placeholder="Doe"
                        class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-purple-500/30 transition-all duration-300 group-hover:border-purple-500/30"
                        :class="validationErrors.lastName ? 'border-red-500/50 focus:ring-red-500/30 validation-error' : ''"
                        @input="validateStep2"
                      />
                      <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <div v-if="validationErrors.lastName" class="mt-1 text-xs text-red-400">
                      {{ validationErrors.lastName }}
                    </div>
                  </div>
                </div>

                <!-- Contact Information -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                  <!-- Email Address -->
                  <div class="group">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                        </svg>
                        <span class="group-hover:text-blue-300 transition-colors">Email Address</span>
                      </div>
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="john.doe@example.com"
                        class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all duration-300 group-hover:border-blue-500/30"
                        :class="validationErrors.email ? 'border-red-500/50 focus:ring-red-500/30 validation-error' : ''"
                        @input="validateStep2"
                      />
                      <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                      </svg>
                    </div>
                    <div v-if="validationErrors.email" class="mt-1 text-xs text-red-400">
                      {{ validationErrors.email }}
                    </div>
                  </div>

                  <!-- Phone Number -->
                  <div class="group">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                      <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="group-hover:text-cyan-300 transition-colors">Phone Number</span>
                      </div>
                    </label>
                    <div class="relative">
                      <input
                        v-model="form.phone"
                        type="text"
                        required
                        placeholder="0912 345 6789"
                        class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-cyan-500/30 transition-all duration-300 group-hover:border-cyan-500/30"
                        :class="validationErrors.phone ? 'border-red-500/50 focus:ring-red-500/30 validation-error' : ''"
                        @input="formatPhoneNumber"
                      />
                      <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                      </svg>
                    </div>
                    <div v-if="validationErrors.phone" class="mt-1 text-xs text-red-400">
                      {{ validationErrors.phone }}
                    </div>
                    <div v-else-if="form.phone && !validationErrors.phone" class="mt-1 text-xs text-cyan-400">
                      Philippine mobile number format: 09XX XXX XXXX
                    </div>
                  </div>
                </div>

                
              </div>
            </div>

            <!-- Step 3: Account Security -->
            <div v-else-if="currentStep === 3" key="step3" class="step-enter-active">
              <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-white mb-3">Account Security</h3>
                <p class="text-gray-300/80 max-w-md mx-auto">Create a strong password to protect your account.</p>
              </div>

              <div class="space-y-6">
                <!-- Password -->
                <div class="group">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4 text-gray-400 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                      </svg>
                      <span class="group-hover:text-purple-300 transition-colors">Password</span>
                    </div>
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      required
                      placeholder="Create a strong password"
                      class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-purple-500/30 transition-all duration-300 group-hover:border-purple-500/30"
                      :class="validationErrors.password ? 'border-red-500/50 focus:ring-red-500/30 validation-error' : ''"
                      @input="validateStep3"
                    />
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    <button
                      type="button"
                      @click="showPassword = !showPassword"
                      class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-pink-400 transition-colors"
                    >
                      <svg v-if="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                  </div>
                  
                  <!-- Password Strength & Requirements -->
                  <div v-if="form.password" class="mt-3">
                    <div class="flex items-center space-x-2 mb-2">
                      <div class="flex-1 h-2 rounded-full overflow-hidden bg-gray-700">
                        <div class="h-full transition-all duration-500"
                             :class="passwordStrengthClass"></div>
                      </div>
                      <span class="text-xs font-medium" :class="passwordStrengthTextClass">
                        {{ passwordStrength }}
                      </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                      <div v-for="(req, index) in passwordRequirements" :key="index" 
                           class="flex items-center space-x-2">
                        <svg class="w-4 h-4" :class="req.met ? 'text-green-400' : 'text-gray-500'"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path v-if="req.met" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                          <circle v-else cx="12" cy="12" r="9" stroke-width="2"></circle>
                        </svg>
                        <span class="text-xs" :class="req.met ? 'text-green-300' : 'text-gray-400'">
                          {{ req.text }}
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="validationErrors.password" class="mt-1 text-xs text-red-400 validation-error">
                    {{ validationErrors.password }}
                  </div>
                </div>

                <!-- Confirm Password -->
                <div class="group">
                  <label class="block text-sm font-medium text-gray-300 mb-2">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4 text-gray-400 group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                      </svg>
                      <span class="group-hover:text-green-300 transition-colors">Confirm Password</span>
                    </div>
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.confirmPassword"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      required
                      placeholder="Re-enter your password"
                      class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-green-500/30 transition-all duration-300 group-hover:border-green-500/30"
                      :class="validationErrors.confirmPassword ? 'border-red-500/50 focus:ring-red-500/30 validation-error' : ''"
                      @input="validateStep3"
                    />
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-green-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <button
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-pink-400 transition-colors"
                    >
                      <svg v-if="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                      </svg>
                      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                  </div>
                  
                  <!-- Password Match Indicator -->
                  <div v-if="form.confirmPassword" class="mt-2">
                    <div v-if="form.password === form.confirmPassword && form.password" 
                         class="flex items-center space-x-2 text-green-400">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                      </svg>
                      <span class="text-xs">Passwords match</span>
                    </div>
                    <div v-else-if="form.confirmPassword && form.password !== form.confirmPassword" 
                         class="flex items-center space-x-2 text-red-400">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                      <span class="text-xs">Passwords do not match</span>
                    </div>
                  </div>
                  
                  <div v-if="validationErrors.confirmPassword" class="mt-1 text-xs text-red-400 validation-error">
                    {{ validationErrors.confirmPassword }}
                  </div>
                </div>

                <!-- Security Tips -->
                <div class="p-4 rounded-xl bg-gradient-to-r from-purple-900/20 to-pink-900/20 border border-purple-700/30">
                  <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-purple-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <div>
                      <p class="text-sm text-white font-medium mb-1">Password Security Tips</p>
                      <ul class="text-xs text-gray-300 space-y-1">
                        <li class="flex items-center">
                          <svg class="w-3 h-3 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          Use a combination of letters, numbers, and symbols
                        </li>
                        <li class="flex items-center">
                          <svg class="w-3 h-3 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          Avoid using personal information like your name or birthdate
                        </li>
                        <li class="flex items-center">
                          <svg class="w-3 h-3 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                          </svg>
                          Use at least 8 characters with mixed case
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 4: Review & Terms -->
            <div v-else-if="currentStep === 4" key="step4" class="step-enter-active">
              <div class="text-center mb-8">
                <h3 class="text-xl font-bold text-white mb-3">Review & Terms</h3>
                <p class="text-gray-300/80 max-w-md mx-auto">Review your information and agree to the terms.</p>
              </div>

              <div class="space-y-6">
                <!-- Review Summary -->
                <div class="bg-gradient-to-br from-gray-900/50 to-gray-800/50 rounded-2xl border border-gray-700/30 p-6">
                  <h4 class="text-lg font-semibold text-white mb-4">Account Summary</h4>
                  
                  <div class="space-y-4">
                    <!-- Role -->
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-800/30">
                      <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center"
                             :class="getRoleGradient(form.role)">
                          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path v-for="path in getRoleIcon(form.role)" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                          </svg>
                        </div>
                        <div>
                          <p class="text-sm font-medium text-white">Role</p>
                          <p class="text-xs text-gray-300">{{ getRoleLabel(form.role) }}</p>
                        </div>
                      </div>
                      <button @click="goToStep(1)" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
                        Edit
                      </button>
                    </div>

                    <!-- Personal Info -->
                    <div class="p-3 rounded-lg bg-gray-800/30">
                      <div class="flex items-center justify-between mb-3">
                        <h5 class="text-sm font-medium text-white">Personal Information</h5>
                        <button @click="goToStep(2)" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
                          Edit
                        </button>
                      </div>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <p class="text-xs text-gray-400 mb-1">Full Name</p>
                          <p class="text-sm text-white">{{ form.firstName }} {{ form.lastName }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-400 mb-1">Email</p>
                          <p class="text-sm text-white">{{ form.email }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-400 mb-1">Phone</p>
                          <p class="text-sm text-white">{{ form.phone || 'Not provided' }}</p>
                        </div>
                        
                      </div>
                    </div>

                    <!-- Security -->
                    <div class="p-3 rounded-lg bg-gray-800/30">
                      <div class="flex items-center justify-between mb-3">
                        <h5 class="text-sm font-medium text-white">Security</h5>
                        <button @click="goToStep(3)" class="text-blue-400 hover:text-blue-300 text-sm font-medium transition-colors">
                          Edit
                        </button>
                      </div>
                      <div class="flex items-center justify-between">
                        <div>
                          <p class="text-xs text-gray-400 mb-1">Password Strength</p>
                          <div class="flex items-center space-x-2">
                            <div class="w-24 h-1 rounded-full bg-gray-700 overflow-hidden">
                              <div class="h-full" :class="passwordStrengthClass.split(' ')[0]"></div>
                            </div>
                            <span class="text-xs font-medium" :class="passwordStrengthTextClass">
                              {{ passwordStrength }}
                            </span>
                          </div>
                        </div>
                        <div class="text-right">
                          <p class="text-xs text-gray-400 mb-1">Account Protection</p>
                          <p class="text-sm text-green-400">✓ Encrypted</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="p-4 rounded-xl bg-gradient-to-r from-blue-900/20 to-purple-900/20 border border-blue-700/30">
                  <div class="flex items-start space-x-3">
                    <div class="relative mt-1">
                      <input
                        v-model="form.terms"
                        type="checkbox"
                        required
                        class="sr-only"
                        id="terms"
                      />
                      <div @click="form.terms = !form.terms"
                           class="w-5 h-5 border-2 border-gray-600 rounded-lg bg-gray-900/50 flex items-center justify-center transition-all duration-300 cursor-pointer hover:border-blue-400 hover:scale-110"
                           :class="form.terms ? 'border-blue-400 bg-blue-500/20 scale-110' : ''">
                        <svg v-if="form.terms" class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                      </div>
                    </div>
                    <label for="terms" class="flex-1 text-sm text-gray-300 cursor-pointer">
                      I agree to the 
                      <button type="button" @click="showTerms" class="text-blue-400 hover:text-blue-300 transition-colors">Terms & Conditions</button> 
                      and 
                      <button type="button" @click="showPrivacy" class="text-purple-400 hover:text-purple-300 transition-colors">Privacy Policy</button>. 
                      I understand that my account will be verified based on my selected role and that I must provide accurate information.
                    </label>
                  </div>
                  <div v-if="validationErrors.terms" class="mt-2 text-xs text-red-400">
                    {{ validationErrors.terms }}
                  </div>
                </div>

                <!-- Final Review Note -->
                <div class="p-4 rounded-xl bg-gradient-to-r from-amber-900/20 to-yellow-900/20 border border-amber-700/30">
                  <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-amber-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.342 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                      <p class="text-sm text-white font-medium mb-1">Important Note</p>
                      <p class="text-xs text-gray-300">
                        By creating an account, you acknowledge that:
                        <br>• Your information will be verified based on your selected role
                        <br>• You may be required to provide additional documentation
                        <br>• Account approval may take 24-48 hours
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Wizard Navigation -->
          <div class="mt-8 pt-6 border-t border-gray-700/30">
            <div class="flex justify-between items-center">
              <!-- Back Button -->
              <button
                v-if="currentStep > 1"
                @click="prevStep"
                class="px-6 py-3 text-gray-300 font-medium rounded-xl border-2 border-gray-600/50 hover:border-gray-500/70 transition-all duration-300 hover:scale-[1.02] flex items-center space-x-2 group step-nav-btn"
              >
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>Back</span>
              </button>
              <div v-else></div>

              <!-- Next/Submit Button -->
              <button
                v-if="currentStep < steps.length"
                @click="nextStep"
                :disabled="!isStepValid"
                class="px-8 py-3 text-white font-semibold rounded-xl shadow-xl transition-all duration-300 hover:scale-[1.02] flex items-center space-x-2 group step-nav-btn"
                :class="isStepValid 
                  ? 'bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600' 
                  : 'bg-gray-700 cursor-not-allowed opacity-50'"
              >
                <span>{{ steps[currentStep - 1].nextButton || 'Continue' }}</span>
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>

              <!-- Submit Button on Last Step -->
              <button
                v-else
                @click="handleSignup"
                :disabled="!isStepValid || isLoading"
                class="relative px-8 py-3 text-white font-semibold rounded-xl shadow-2xl overflow-hidden group transition-all duration-500 hover:scale-[1.02] step-nav-btn"
                :class="isStepValid && !isLoading 
                  ? '' 
                  : 'opacity-50 cursor-not-allowed'"
              >
                <!-- Animated gradient background -->
                <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 via-teal-500 to-green-500 group-hover:from-emerald-600 group-hover:via-teal-600 group-hover:to-green-600 transition-all duration-500"></div>
                <!-- Shine effect -->
                <div class="absolute inset-0 overflow-hidden">
                  <div class="absolute top-0 left-0 w-8 h-full bg-white/20 skew-x-12 -translate-x-full group-hover:translate-x-[400%] transition-all duration-1000"></div>
                </div>
                <!-- Button content -->
                <div class="relative flex items-center justify-center space-x-3">
                  <svg v-if="isLoading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                  </svg>
                  <span class="group-hover:scale-105 transition-transform">
                    {{ isLoading ? 'Creating Account...' : 'Create Account' }}
                  </span>
                </div>
              </button>
            </div>

            <!-- Step Indicator Dots -->
            <div class="flex justify-center space-x-2 mt-6">
              <div v-for="step in steps" :key="step.id"
                   class="w-2 h-2 rounded-full transition-all duration-300"
                   :class="step.id === currentStep ? 'bg-blue-400 w-4' : 'bg-gray-600'"></div>
            </div>
          </div>

          <!-- Login Link -->
          <div class="mt-6 pt-4 border-t border-gray-700/30 text-center">
            <p class="text-gray-400 text-sm">
              Already have an account? 
              <button @click="handleLogin" class="text-blue-400 hover:text-blue-300 font-medium transition-colors">
                Sign In
              </button>
            </p>
          </div>

          <!-- Footer -->
          <div class="mt-6 pt-4 border-t border-gray-700/30 text-center">
            <p class="text-gray-400/70 text-xs">
              © 2026 CaviteGo Paint • Step {{ currentStep }} of {{ steps.length }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition
      enter-active-class="transition-all duration-500 ease-out"
      leave-active-class="transition-all duration-300 ease-in"
      enter-from-class="opacity-0 translate-y-4 scale-95"
      leave-to-class="opacity-0 translate-y-4 scale-95"
    >
      <div
        v-if="showToast"
        class="fixed top-6 right-6 bg-gray-800/90 backdrop-blur-xl border border-gray-700/50 rounded-2xl shadow-2xl p-4 max-w-sm z-50"
      >
        <div class="flex items-start space-x-4">
          <div :class="[
            'p-3 rounded-xl',
            toastType === 'success' ? 'bg-gradient-to-br from-emerald-500/20 to-teal-400/20 border border-emerald-500/30' : 
            toastType === 'error' ? 'bg-gradient-to-br from-red-500/20 to-pink-400/20 border border-red-500/30' :
            toastType === 'info' ? 'bg-gradient-to-br from-blue-500/20 to-cyan-400/20 border border-blue-500/30' :
            'bg-gradient-to-br from-amber-500/20 to-yellow-400/20 border border-amber-500/30'
          ]">
            <svg v-if="toastType === 'success'" class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else-if="toastType === 'error'" class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            <svg v-else-if="toastType === 'info'" class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <svg v-else class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div class="flex-1">
            <h5 class="text-sm font-semibold text-white mb-2">{{ toastTitle }}</h5>
            <p class="text-xs text-gray-300">{{ toastMessage }}</p>
          </div>
          <button @click="showToast = false" class="text-gray-500 hover:text-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
    </transition>

    <!-- Loading Overlay -->
    <transition
      enter-active-class="transition-opacity duration-300"
      leave-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div v-if="isLoading" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="text-center">
          <div class="w-16 h-16 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-white font-medium">Creating your account...</p>
          <p class="text-gray-400 text-sm mt-2">Please wait while we set up your account</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Refs for animation control
const pageContainer = ref(null)
const signupCard = ref(null)

// Wizard state
const currentStep = ref(1)
const steps = [
  { 
    id: 1, 
    title: 'Select Role', 
    description: 'Choose how you will use CaviteGo Paint',
    tips: 'You can update your role later with verification',
    nextButton: 'Continue to Personal Info'
  },
  { 
    id: 2, 
    title: 'Personal Info', 
    description: 'Tell us about yourself',
    tips: 'We use this information to personalize your experience',
    nextButton: 'Continue to Security'
  },
  { 
    id: 3, 
    title: 'Security', 
    description: 'Create a secure password',
    tips: 'Use a strong password to protect your account',
    nextButton: 'Review & Finish'
  },
  { 
    id: 4, 
    title: 'Review', 
    description: 'Verify your information and agree to terms',
    tips: 'Make sure all information is correct before submitting'
  }
]

// Form state
const form = reactive({
  firstName: '',
  lastName: '',
  phone: '',
  email: '',
  password: '',
  confirmPassword: '',
  role: 'client',
  terms: false
})

// UI state
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isLoading = ref(false)
const showToast = ref(false)
const toastTitle = ref('')
const toastMessage = ref('')
const toastType = ref('info')
const validationErrors = reactive({})

// Role options with enhanced details
const roles = [
  {
    value: 'client',
    label: 'Client',
    description: 'Purchase paints and track orders',
    gradient: 'bg-gradient-to-br from-amber-500 to-yellow-400',
    icon: ['M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
    features: [
      'Browse paint catalog',
      'Track orders online',
      'Request quotes',
      'Schedule consultations'
    ]
  },
  {
    value: 'distributor',
    label: 'Distributor',
    description: 'Sell and distribute paint products',
    gradient: 'bg-gradient-to-br from-purple-500 to-pink-400',
    icon: ['M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
    features: [
      'Manage inventory',
      'Track sales & revenue',
      'Access distributor pricing',
      'Order in bulk'
    ]
  },
  {
    value: 'service_provider',
    label: 'Service Provider',
    description: 'Offer painting and maintenance services',
    gradient: 'bg-gradient-to-br from-emerald-500 to-teal-400',
    icon: ['M13 10V3L4 14h7v7l9-11h-7z'],
    features: [
      'List your services',
      'Get client requests',
      'Manage appointments',
      'Showcase your portfolio'
    ]
  }
]

// Password requirements
const passwordRequirements = computed(() => {
  const password = form.password
  return [
    { 
      text: 'At least 8 characters', 
      met: password.length >= 8 
    },
    { 
      text: 'Uppercase letter', 
      met: /[A-Z]/.test(password) 
    },
    { 
      text: 'Lowercase letter', 
      met: /[a-z]/.test(password) 
    },
    { 
      text: 'Number', 
      met: /\d/.test(password) 
    },
    { 
      text: 'Special character', 
      met: /[!@#$%^&*(),.?":{}|<>]/.test(password) 
    }
  ]
})

// Step validation
const isStepValid = computed(() => {
  switch (currentStep.value) {
    case 1:
      return form.role !== ''
    case 2:
      return validateStep2(true)
    case 3:
      return validateStep3(true)
    case 4:
      return form.terms && validateStep2(true) && validateStep3(true)
    default:
      return false
  }
})

// Wizard navigation with animation reset
const nextStep = () => {
  if (currentStep.value < steps.length && isStepValid.value) {
    currentStep.value++
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const goToStep = (step) => {
  if (step >= 1 && step <= steps.length) {
    currentStep.value = step
  }
}

// Helper functions for roles
const getRoleGradient = (roleValue) => {
  const role = roles.find(r => r.value === roleValue)
  return role ? role.gradient : 'bg-gradient-to-br from-gray-500 to-gray-400'
}

const getRoleIcon = (roleValue) => {
  const role = roles.find(r => r.value === roleValue)
  return role ? role.icon : []
}

const getRoleLabel = (roleValue) => {
  const role = roles.find(r => r.value === roleValue)
  return role ? role.label : 'Unknown'
}

const getRoleBorderColor = (roleValue) => {
  switch (roleValue) {
    case 'client':
      return '#f59e0b' // amber
    case 'distributor':
      return '#a855f7' // purple
    case 'service_provider':
      return '#10b981' // emerald
    default:
      return '#3b82f6' // blue
  }
}

const getRoleGradientColor = (roleValue, opacity = 0.1) => {
  switch (roleValue) {
    case 'client':
      return `rgba(245, 158, 11, ${opacity})`
    case 'distributor':
      return `rgba(168, 85, 247, ${opacity})`
    case 'service_provider':
      return `rgba(16, 185, 129, ${opacity})`
    default:
      return `rgba(59, 130, 246, ${opacity})`
  }
}

const getRoleBadgeClass = (roleValue) => {
  switch (roleValue) {
    case 'client':
      return 'bg-amber-500/20 text-amber-300 border border-amber-500/30'
    case 'distributor':
      return 'bg-purple-500/20 text-purple-300 border border-purple-500/30'
    case 'service_provider':
      return 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30'
    default:
      return 'bg-blue-500/20 text-blue-300 border border-blue-500/30'
  }
}

// Function to select role
const selectRole = (roleValue) => {
  form.role = roleValue
  showNotification('Role Selected', `You selected: ${getRoleLabel(roleValue)}`, 'info')
}

// Format phone number
const formatPhoneNumber = () => {
  form.phone = form.phone.replace(/\D/g, '')
  
  if (form.phone.length > 3 && form.phone.length <= 6) {
    form.phone = form.phone.replace(/(\d{3})(\d+)/, '$1 $2')
  } else if (form.phone.length > 6 && form.phone.length <= 10) {
    form.phone = form.phone.replace(/(\d{3})(\d{3})(\d+)/, '$1 $2 $3')
  } else if (form.phone.length > 10) {
    form.phone = form.phone.substring(0, 11).replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3')
  }
}

// Password strength calculator
const passwordStrength = computed(() => {
  if (!form.password) return ''
  
  const hasLower = /[a-z]/.test(form.password)
  const hasUpper = /[A-Z]/.test(form.password)
  const hasNumber = /\d/.test(form.password)
  const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(form.password)
  const length = form.password.length
  
  let score = 0
  if (length >= 8) score++
  if (length >= 12) score++
  if (hasLower && hasUpper) score++
  if (hasNumber) score++
  if (hasSpecial) score++
  
  if (score <= 2) return 'Weak'
  if (score <= 3) return 'Fair'
  if (score <= 4) return 'Good'
  return 'Strong'
})

const passwordStrengthClass = computed(() => {
  const strength = passwordStrength.value.toLowerCase()
  const classes = {
    weak: 'bg-gradient-to-r from-red-500 to-pink-500 w-1/4',
    fair: 'bg-gradient-to-r from-amber-500 to-yellow-500 w-1/2',
    good: 'bg-gradient-to-r from-blue-500 to-cyan-500 w-3/4',
    strong: 'bg-gradient-to-r from-emerald-500 to-teal-500 w-full'
  }
  return classes[strength] || 'bg-gray-600 w-0'
})

const passwordStrengthTextClass = computed(() => {
  const strength = passwordStrength.value.toLowerCase()
  const classes = {
    weak: 'text-red-400',
    fair: 'text-amber-400',
    good: 'text-blue-400',
    strong: 'text-emerald-400'
  }
  return classes[strength] || 'text-gray-400'
})

// Step 2 validation
const validateStep2 = (silent = false) => {
  const errors = {}
  let isValid = true

  // First Name
  if (!form.firstName.trim()) {
    errors.firstName = 'First name is required'
    isValid = false
  } else if (form.firstName.length < 2) {
    errors.firstName = 'First name must be at least 2 characters'
    isValid = false
  }

  // Last Name
  if (!form.lastName.trim()) {
    errors.lastName = 'Last name is required'
    isValid = false
  } else if (form.lastName.length < 2) {
    errors.lastName = 'Last name must be at least 2 characters'
    isValid = false
  }

  // Email
  if (!form.email) {
    errors.email = 'Email is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email address'
    isValid = false
  }

  // Phone
  if (!form.phone.trim()) {
    errors.phone = 'Phone number is required'
    isValid = false
  } else {
    const numericPhone = form.phone.replace(/\D/g, '')
    if (numericPhone.length < 10) {
      errors.phone = 'Phone number must be at least 10 digits'
      isValid = false
    } else if (!/^09\d{9}$/.test(numericPhone)) {
      errors.phone = 'Please enter a valid Philippine mobile number (09XXXXXXXXX)'
      isValid = false
    }
  }

  

  if (!silent) {
    Object.assign(validationErrors, errors)
  }
  
  return isValid
}

// Step 3 validation
const validateStep3 = (silent = false) => {
  const errors = {}
  let isValid = true

  // Password validation
  if (!form.password) {
    errors.password = 'Password is required'
    isValid = false
  } else {
    const hasLower = /[a-z]/.test(form.password)
    const hasUpper = /[A-Z]/.test(form.password)
    const hasNumber = /\d/.test(form.password)
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(form.password)
    const hasMinLength = form.password.length >= 8
    
    if (!hasMinLength) {
      errors.password = 'Password must be at least 8 characters'
      isValid = false
    } else if (!hasUpper) {
      errors.password = 'Password must contain at least one uppercase letter'
      isValid = false
    } else if (!hasNumber) {
      errors.password = 'Password must contain at least one number'
      isValid = false
    } else if (!hasSpecial) {
      errors.password = 'Password must contain at least one special character'
      isValid = false
    } else if (!hasLower) {
      errors.password = 'Password must contain at least one lowercase letter'
      isValid = false
    }
  }

  // Confirm Password
  if (!form.confirmPassword) {
    errors.confirmPassword = 'Please confirm your password'
    isValid = false
  } else if (form.password !== form.confirmPassword) {
    errors.confirmPassword = 'Passwords do not match'
    isValid = false
  }

  if (!silent) {
    Object.assign(validationErrors, errors)
  }
  
  return isValid
}

// Watch for step changes
watch(currentStep, (newStep) => {
  // Clear validation errors when changing steps
  Object.keys(validationErrors).forEach(key => {
    delete validationErrors[key]
  })
})

// Page load animation
onMounted(async () => {
  await nextTick()
  
  if (pageContainer.value) {
    pageContainer.value.style.opacity = '1'
    pageContainer.value.style.transition = 'opacity 0.8s ease-in-out'
  }
  
  if (signupCard.value) {
    setTimeout(() => {
      signupCard.value.style.opacity = '1'
      signupCard.value.style.transform = 'translateY(0)'
      signupCard.value.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)'
    }, 300)
  }
})

// Show notification
const showNotification = (title, message, type = 'info') => {
  toastTitle.value = title
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

// Handle signup
const handleSignup = async () => {
  if (!form.terms) {
    validationErrors.terms = 'You must agree to the terms and conditions'
    showNotification('Terms Required', 'Please agree to the terms and conditions', 'error')
    return
  }

  if (!validateStep2(true) || !validateStep3(true)) {
    showNotification('Validation Error', 'Please check all steps for errors', 'error')
    return
  }

  isLoading.value = true

  try {
    const userData = {
      first_name: form.firstName,
      last_name: form.lastName,
      email: form.email,
      phone: form.phone.replace(/\D/g, ''),
      password: form.password,
      password_confirmation: form.confirmPassword,
      role: form.role,
      terms: form.terms
    }

    const response = await fetch('http://localhost:8000/api/auth/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify(userData)
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'Registration failed')
    }

    showNotification(
      'Account Created Successfully!',
      'Your account has been created. Please check your email for verification instructions.',
      'success'
    )

    // Clear form
    Object.keys(form).forEach(key => {
      if (typeof form[key] === 'boolean') {
        form[key] = false
      } else {
        form[key] = ''
      }
    })
    form.role = 'client'
    currentStep.value = 1

    if (data.data && data.data.token) {
      localStorage.setItem('auth_token', data.data.token)
    }

    setTimeout(() => {
      router.push('/Landing/logIn')
    }, 3000)

  } catch (error) {
    if (error.message.includes('already registered') || error.message.includes('taken')) {
      showNotification('Registration Failed', 'This email is already registered', 'error')
    } else if (error.message.includes('Validation')) {
      showNotification('Validation Error', 'Please check your information and try again', 'error')
    } else {
      showNotification('Registration Error', error.message || 'Something went wrong. Please try again.', 'error')
    }
  } finally {
    isLoading.value = false
  }
}

// Handle login redirect
const handleLogin = () => {
  router.push('/Landing/logIn')
}

// Handle terms and privacy
const showTerms = () => {
  showNotification('Terms & Conditions', 'This feature will be implemented soon', 'info')
}

const showPrivacy = () => {
  showNotification('Privacy Policy', 'This feature will be implemented soon', 'info')
}
</script>

<style scoped>
/* We'll put the CSS directly here to avoid path issues */
.min-h-screen {
  opacity: 0;
}

/* Entry animations */
@keyframes fade-in {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slide-in-up {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-in-down {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-in-left {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slide-in-right {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes scale-in {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes float-slow {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

@keyframes float-medium {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-15px) rotate(-3deg); }
}

@keyframes brush-stroke-1 {
  0%, 100% { transform: translateX(0) rotate(45deg); }
  50% { transform: translateX(20px) rotate(55deg); }
}

@keyframes brush-stroke-2 {
  0%, 100% { transform: translateX(0) rotate(-12deg); }
  50% { transform: translateX(-15px) rotate(-8deg); }
}

@keyframes bounce-droplet {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

@keyframes pulse-selected {
  0%, 100% { 
    transform: scale(1.1);
    box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
  }
  50% { 
    transform: scale(1.15);
    box-shadow: 0 0 0 10px rgba(59, 130, 246, 0);
  }
}

/* Animation classes */
.animate-fade-in {
  animation: fade-in 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.animate-slide-in-up {
  animation: slide-in-up 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.animate-slide-in-down {
  animation: slide-in-down 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.animate-slide-in-left {
  animation: slide-in-left 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.animate-slide-in-right {
  animation: slide-in-right 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.animate-scale-in {
  animation: scale-in 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.animate-float-slow {
  animation: float-slow 8s ease-in-out infinite;
}

.animate-float-medium {
  animation: float-medium 6s ease-in-out infinite;
}

.animate-brush-stroke-1 {
  animation: brush-stroke-1 10s ease-in-out infinite;
}

.animate-brush-stroke-2 {
  animation: brush-stroke-2 12s ease-in-out infinite;
}

.animate-bounce-droplet {
  animation: bounce-droplet 4s ease-in-out infinite;
}

.animate-pulse {
  animation: pulse 2s ease-in-out infinite;
}

.animate-pulse-selected {
  animation: pulse-selected 2s ease-in-out infinite;
}

/* Smooth transitions */
* {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Input focus effects */
input:focus, textarea:focus, select:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6, #ec4899);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #60a5fa, #a78bfa, #f472b6);
}

/* Accessibility focus styles */
button:focus-visible,
a:focus-visible,
input:focus-visible,
textarea:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 3px;
  border-radius: 0.75rem;
}

/* Disabled state styles */
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Wizard progress bar */
.progress-bar {
  height: 4px;
  background: linear-gradient(to right, #3b82f6, #8b5cf6, #ec4899);
  transition: all 0.5s ease-in-out;
  transform-origin: left;
}

/* Step indicator styles */
.step-indicator {
  width: 40px;
  height: 40px;
  transition: all 0.3s ease-in-out;
}

.step-indicator.completed {
  background: linear-gradient(135deg, #10b981, #34d399);
}

.step-indicator.active {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

.step-indicator.pending {
  background: linear-gradient(135deg, #6b7280, #9ca3af);
}

/* Mobile responsiveness adjustments */
@media (max-width: 1024px) {
  .max-w-5xl {
    max-width: 100%;
  }
  
  .grid-cols-3, .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .wizard-steps {
    flex-direction: column;
    gap: 1rem;
  }
}

@media (max-width: 640px) {
  .p-8 {
    padding: 1.5rem;
  }
  
  /* Reduce animation intensity on mobile */
  .animate-float-slow,
  .animate-float-medium,
  .animate-brush-stroke-1,
  .animate-brush-stroke-2,
  .animate-pulse-selected {
    animation-duration: 12s;
  }
  
  /* Ensure proper spacing on mobile */
  .my-8 {
    margin-top: 2rem;
    margin-bottom: 2rem;
  }
  
  .mx-4 {
    margin-left: 1rem;
    margin-right: 1rem;
  }
  
  /* Wizard adjustments for mobile */
  .step-indicator {
    width: 32px;
    height: 32px;
  }
  
  .step-label {
    font-size: 0.75rem;
  }
}

/* Enhanced text gradients */
.bg-clip-text {
  -webkit-background-clip: text;
  background-clip: text;
}

/* Password strength indicator transitions */
.bg-gradient-to-r {
  transition: all 0.5s ease-in-out;
}

/* Wizard step content */
.step-content {
  min-height: 500px;
  position: relative;
}

/* Step navigation buttons */
.step-nav-btn {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.step-nav-btn:hover {
  transform: translateY(-2px);
}

/* Loading overlay */
.loading-overlay {
  background: rgba(17, 24, 39, 0.8);
  backdrop-filter: blur(10px);
}

/* Form validation styles */
.validation-error {
  animation: shake 0.5s cubic-bezier(.36,.07,.19,.97) both;
}

@keyframes shake {
  10%, 90% { transform: translateX(-1px); }
  20%, 80% { transform: translateX(2px); }
  30%, 50%, 70% { transform: translateX(-3px); }
  40%, 60% { transform: translateX(3px); }
}

/* Step transition effects */
.step-enter-active {
  animation: slide-next 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slide-next {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Additional wizard styles */
.delay-1000 {
  animation-delay: 1000ms;
}

.delay-300 {
  animation-delay: 300ms;
}

.delay-500 {
  animation-delay: 500ms;
}

.delay-700 {
  animation-delay: 700ms;
}
</style>