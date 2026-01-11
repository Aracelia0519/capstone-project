<template>
  <div ref="pageContainer" class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4 overflow-hidden">
    <!-- Animated Paint Background -->
    <div class="absolute inset-0 overflow-hidden">
      <!-- Paint splatter effects -->
      <div class="absolute top-1/4 left-1/4 w-96 h-96 opacity-0 animate-fade-in" style="animation-delay: 300ms">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-blue-500/20 to-cyan-400/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute top-12 left-12 w-32 h-32 bg-gradient-to-r from-purple-500/15 to-pink-500/15 rounded-full blur-2xl animate-float-medium"></div>
      </div>
      
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 opacity-0 animate-fade-in" style="animation-delay: 500ms">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-emerald-500/15 to-teal-400/15 rounded-full blur-3xl animate-float-slow delay-1000"></div>
        <div class="absolute bottom-12 right-12 w-32 h-32 bg-gradient-to-r from-amber-500/10 to-yellow-400/10 rounded-full blur-2xl animate-float-medium delay-500"></div>
      </div>

      <!-- Paint brush strokes -->
      <div class="absolute top-1/3 right-1/3 w-64 h-96 opacity-10 opacity-0 animate-slide-in-right" style="animation-delay: 700ms">
        <div class="absolute w-8 h-40 bg-gradient-to-b from-blue-400 to-transparent rounded-full transform rotate-45 animate-brush-stroke-1"></div>
      </div>
      
      <div class="absolute bottom-1/3 left-1/3 w-64 h-96 opacity-10 opacity-0 animate-slide-in-left" style="animation-delay: 700ms">
        <div class="absolute w-8 h-32 bg-gradient-to-t from-purple-400 to-transparent rounded-full transform -rotate-12 animate-brush-stroke-2"></div>
      </div>

      <!-- Floating paint droplets -->
      <div class="absolute top-20 left-20 w-8 h-8 bg-gradient-to-r from-blue-400/30 to-cyan-300/30 rounded-full animate-bounce-droplet opacity-0 animate-fade-in" style="animation-delay: 400ms"></div>
      <div class="absolute top-40 right-40 w-6 h-6 bg-gradient-to-r from-purple-400/30 to-pink-300/30 rounded-full animate-bounce-droplet delay-300 opacity-0 animate-fade-in" style="animation-delay: 600ms"></div>
      <div class="absolute bottom-20 left-32 w-7 h-7 bg-gradient-to-r from-emerald-400/30 to-teal-300/30 rounded-full animate-bounce-droplet delay-700 opacity-0 animate-fade-in" style="animation-delay: 800ms"></div>
      <div class="absolute bottom-40 right-20 w-5 h-5 bg-gradient-to-r from-amber-400/30 to-yellow-300/30 rounded-full animate-bounce-droplet delay-1000 opacity-0 animate-fade-in" style="animation-delay: 1000ms"></div>

      <!-- Color palette circles -->
      <div class="absolute top-10 right-10 w-24 h-24 opacity-5 opacity-0 animate-scale-in" style="animation-delay: 900ms">
        <div class="absolute inset-0 border-2 border-blue-400/30 rounded-full"></div>
        <div class="absolute inset-4 border-2 border-purple-400/30 rounded-full"></div>
        <div class="absolute inset-8 border-2 border-emerald-400/30 rounded-full"></div>
        <div class="absolute inset-12 border-2 border-amber-400/30 rounded-full"></div>
      </div>

      <div class="absolute bottom-10 left-10 w-32 h-32 opacity-5 opacity-0 animate-scale-in" style="animation-delay: 1100ms">
        <div class="absolute inset-0 border-2 border-cyan-400/30 rounded-full"></div>
        <div class="absolute inset-6 border-2 border-pink-400/30 rounded-full"></div>
        <div class="absolute inset-12 border-2 border-teal-400/30 rounded-full"></div>
        <div class="absolute inset-18 border-2 border-yellow-400/30 rounded-full"></div>
      </div>
    </div>

    <!-- Wider Signup Card with increased margin -->
    <div ref="signupCard" class="relative w-full max-w-5xl z-10 opacity-0 translate-y-10 mx-auto my-8 md:my-12">
      <div class="flex flex-col lg:flex-row bg-gray-800/30 backdrop-blur-2xl rounded-3xl shadow-2xl border border-gray-700/30 overflow-hidden transform transition-all duration-700 hover:scale-[1.01] mx-4">
        <!-- Left Side - Visual Branding -->
        <div class="lg:w-2/5 p-8 lg:p-12 bg-gradient-to-br from-blue-900/20 via-purple-900/20 to-pink-900/20 flex flex-col justify-center items-center relative overflow-hidden">
          <!-- Animated gradient overlay -->
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5 opacity-0 animate-fade-in" style="animation-delay: 1200ms"></div>
          
          <!-- Paint Brand Logo -->
          <div class="relative mb-6 opacity-0 animate-fade-in" style="animation-delay: 1300ms">
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

          <!-- Role Selection Visualization -->
          <div class="mt-6 w-full max-w-xs opacity-0 animate-fade-in" style="animation-delay: 1400ms">
            <div class="relative mb-6">
              <!-- Role Circles -->
              <div class="flex justify-center space-x-6 mb-8">
                <div 
                  v-for="role in roles" 
                  :key="role.value"
                  @click="selectRole(role.value)"
                  class="relative cursor-pointer group"
                  :style="{ animationDelay: `${1400 + (roles.indexOf(role) * 100)}ms` }"
                >
                  <div class="w-16 h-16 rounded-full flex items-center justify-center transform transition-all duration-300 group-hover:scale-110"
                       :class="[
                         form.role === role.value 
                           ? 'ring-4 ring-offset-2 ring-offset-gray-900 ring-opacity-50 scale-110 animate-pulse-selected' 
                           : ''
                       ]">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center"
                         :class="role.gradient">
                      <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path v-for="path in role.icon" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 whitespace-nowrap text-xs font-medium"
                       :class="form.role === role.value ? 'text-white font-bold' : 'text-gray-400'">
                    {{ role.label }}
                  </div>
                </div>
              </div>

              <!-- Role Descriptions -->
              <div class="space-y-4">
                <div v-for="role in roles" :key="role.value" 
                     class="p-4 rounded-xl transition-all duration-300 opacity-0 animate-fade-in"
                     :class="form.role === role.value 
                       ? 'bg-gray-900/70 backdrop-blur-sm border-2 border-opacity-50 shadow-lg' 
                       : 'bg-gray-900/30 backdrop-blur-sm opacity-70 hover:opacity-100'"
                     :style="{ 
                       animationDelay: `${1500 + (roles.indexOf(role) * 100)}ms`,
                       borderColor: form.role === role.value 
                         ? getRoleBorderColor(role.value)
                         : 'transparent'
                     }">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                         :class="role.gradient">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path v-for="path in role.icon" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium"
                         :class="form.role === role.value ? 'text-white' : 'text-gray-200'">
                        {{ role.label }}
                      </p>
                      <p class="text-xs"
                         :class="form.role === role.value ? 'text-gray-300' : 'text-gray-400'">
                        {{ role.description }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Benefits Card -->
            <div class="mt-6 p-4 rounded-xl bg-gradient-to-br from-blue-900/20 to-purple-900/20 border border-blue-700/30 backdrop-blur-sm opacity-0 animate-fade-in" style="animation-delay: 1800ms">
              <div class="flex items-center space-x-3 mb-3">
                <div class="p-2 rounded-lg bg-gradient-to-r from-blue-500/20 to-cyan-400/20">
                  <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                  </svg>
                </div>
                <h4 class="text-sm font-semibold text-white">Secure Registration</h4>
              </div>
              <p class="text-xs text-gray-300 leading-relaxed">
                Your information is protected with our security. All passwords are encrypted and stored securely.
              </p>
            </div>
          </div>

          <!-- Brand Tagline -->
          <div class="mt-8 text-center opacity-0 animate-fade-in" style="animation-delay: 2000ms">
            <p class="text-gray-400/70 text-sm italic">"Color Your World With Us"</p>
          </div>
        </div>

        <!-- Right Side - Signup Form -->
        <div class="lg:w-3/5 p-8 lg:p-12">
          <!-- Form Header -->
          <div class="text-center mb-8 opacity-0 animate-fade-in" style="animation-delay: 1300ms">
            <h2 class="text-2xl font-bold text-white mb-2">Create Your Account</h2>
            <p class="text-gray-400">Join CaviteGo Paint in just a few steps</p>
          </div>

          <!-- Signup Form -->
          <form @submit.prevent="handleSignup" class="space-y-6">
            <!-- Name Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- First Name -->
              <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1400ms">
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
                    placeholder="..."
                    class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all duration-300 group-hover:border-blue-500/30"
                    :class="validationErrors.firstName ? 'border-red-500/50 focus:ring-red-500/30' : ''"
                    @input="updateFullName"
                  />
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
              </div>

              <!-- Last Name -->
              <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1450ms">
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
                    placeholder="..."
                    class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-purple-500/30 transition-all duration-300 group-hover:border-purple-500/30"
                    :class="validationErrors.lastName ? 'border-red-500/50 focus:ring-red-500/30' : ''"
                    @input="updateFullName"
                  />
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-purple-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
              </div>

              <!-- Auto-generated Full Name -->
              <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1500ms">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="group-hover:text-emerald-300 transition-colors">Full Name</span>
                  </div>
                </label>
                <div class="relative">
                  <input
                    v-model="form.fullName"
                    type="text"
                    readonly
                    class="w-full px-4 py-3 pl-11 bg-gray-900/30 border border-gray-700/30 rounded-xl text-gray-400 placeholder-gray-500/60 cursor-not-allowed"
                  />
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Contact Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Phone Number -->
              <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1550ms">
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
                    :class="validationErrors.phone ? 'border-red-500/50 focus:ring-red-500/30' : ''"
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

              <!-- Email Address -->
              <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1600ms">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span class="group-hover:text-blue-300 transition-colors">Email Address</span>
                  </div>
                </label>
                <div class="relative">
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    placeholder="your@email.com"
                    class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-blue-500/30 transition-all duration-300 group-hover:border-blue-500/30"
                    :class="validationErrors.email ? 'border-red-500/50 focus:ring-red-500/30' : ''"
                  />
                  <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-500 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Address -->
            <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1650ms">
              <label class="block text-sm font-medium text-gray-300 mb-2">
                <div class="flex items-center space-x-2">
                  <svg class="w-4 h-4 text-gray-400 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <span class="group-hover:text-amber-300 transition-colors">Address</span>
                </div>
              </label>
              <div class="relative">
                <textarea
                  v-model="form.address"
                  required
                  placeholder="Enter your complete address"
                  rows="3"
                  class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-amber-500/30 transition-all duration-300 group-hover:border-amber-500/30 resize-none"
                  :class="validationErrors.address ? 'border-red-500/50 focus:ring-red-500/30' : ''"
                ></textarea>
                <svg class="absolute left-3 top-4 transform w-5 h-5 text-gray-500 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
            </div>

            <!-- Password Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Password -->
              <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1700ms">
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
                    placeholder="••••••••"
                    class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-purple-500/30 transition-all duration-300 group-hover:border-purple-500/30"
                    :class="validationErrors.password ? 'border-red-500/50 focus:ring-red-500/30' : ''"
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
                <div v-if="form.password" class="mt-2">
                  <div class="flex items-center space-x-2">
                    <div class="flex-1 h-1 rounded-full overflow-hidden bg-gray-700">
                      <div class="h-full transition-all duration-500"
                           :class="passwordStrengthClass"></div>
                    </div>
                    <span class="text-xs" :class="passwordStrengthTextClass">
                      {{ passwordStrength }}
                    </span>
                  </div>
                  <div class="mt-1 text-xs text-gray-400">
                    Must be at least 8 characters with uppercase, number, and special character
                  </div>
                </div>
                <div v-if="validationErrors.password" class="mt-1 text-xs text-red-400">
                  {{ validationErrors.password }}
                </div>
              </div>

              <!-- Confirm Password -->
              <div class="group opacity-0 animate-slide-in-up" style="animation-delay: 1750ms">
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
                    placeholder="••••••••"
                    class="w-full px-4 py-3 pl-11 bg-gray-900/50 border border-gray-700/50 rounded-xl text-white placeholder-gray-500/60 focus:outline-none focus:ring-2 focus:ring-green-500/30 transition-all duration-300 group-hover:border-green-500/30"
                    :class="validationErrors.confirmPassword ? 'border-red-500/50 focus:ring-red-500/30' : ''"
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
                <div v-if="form.confirmPassword && !validationErrors.confirmPassword" class="mt-2 flex items-center space-x-2">
                  <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                  <span class="text-xs text-green-400">Passwords match</span>
                </div>
                <div v-if="validationErrors.confirmPassword" class="mt-1 text-xs text-red-400">
                  {{ validationErrors.confirmPassword }}
                </div>
              </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="flex items-start space-x-3 opacity-0 animate-fade-in" style="animation-delay: 1800ms">
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
                I understand that my account will be verified based on my selected role.
              </label>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              :disabled="isLoading"
              class="relative w-full py-3.5 text-white font-semibold rounded-xl shadow-2xl overflow-hidden group transition-all duration-500 hover:scale-[1.02] opacity-0 animate-scale-in"
              style="animation-delay: 1850ms"
              :class="isLoading ? 'opacity-50 cursor-not-allowed' : ''"
            >
              <!-- Animated gradient background -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 group-hover:from-blue-600 group-hover:via-purple-600 group-hover:to-pink-600 transition-all duration-500"></div>
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
                  {{ isLoading ? 'Creating Account...' : 'Create My Account' }}
                </span>
              </div>
            </button>

            <!-- Divider -->
            <div class="relative opacity-0 animate-fade-in" style="animation-delay: 1900ms">
              <div class="absolute inset-0 flex items-center">
                <div class="w-full h-px bg-gradient-to-r from-transparent via-gray-600/50 to-transparent"></div>
              </div>
              <div class="relative flex justify-center">
                <span class="px-4 bg-gray-800/80 text-gray-400/80 text-sm">Already have an account?</span>
              </div>
            </div>

            <!-- Login Button -->
            <button
              type="button"
              @click="handleLogin"
              class="relative w-full py-3 text-gray-300 font-medium rounded-xl border-2 border-transparent group overflow-hidden transition-all duration-300 hover:scale-[1.02] opacity-0 animate-fade-in"
              style="animation-delay: 1950ms"
            >
              <!-- Gradient border -->
              <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <div class="absolute inset-[2px] bg-gray-800/80 rounded-lg"></div>
              
              <!-- Content -->
              <div class="relative flex items-center justify-center space-x-2 group-hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                </svg>
                <span class="group-hover:scale-105 transition-transform">Sign In to Existing Account</span>
              </div>
            </button>
          </form>

          <!-- Footer -->
          <div class="mt-6 pt-4 border-t border-gray-700/30 text-center opacity-0 animate-fade-in" style="animation-delay: 2000ms">
            <p class="text-gray-400/70 text-xs">
              © 2026 CaviteGo Paint • Account verification required for all roles
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
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Refs for animation control
const pageContainer = ref(null)
const signupCard = ref(null)

// Form state
const form = reactive({
  firstName: '',
  lastName: '',
  fullName: '',
  phone: '',
  address: '',
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

// Role options
const roles = [
  {
    value: 'client',
    label: 'Client',
    description: 'Purchase paints and track orders',
    gradient: 'bg-gradient-to-br from-amber-500 to-yellow-400',
    icon: ['M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z']
  },
  {
    value: 'distributor',
    label: 'Distributor',
    description: 'Sell and distribute paint products',
    gradient: 'bg-gradient-to-br from-purple-500 to-pink-400',
    icon: ['M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4']
  },
  {
    value: 'service_provider',
    label: 'Service Provider',
    description: 'Offer painting and maintenance services',
    gradient: 'bg-gradient-to-br from-emerald-500 to-teal-400',
    icon: ['M13 10V3L4 14h7v7l9-11h-7z']
  }
]

// Function to select role with better visual feedback
const selectRole = (roleValue) => {
  form.role = roleValue
  // Add a subtle notification
  const selectedRole = roles.find(r => r.value === roleValue)
  showNotification('Role Selected', `You selected: ${selectedRole.label}`, 'info')
}

// Get border color for selected role
const getRoleBorderColor = (roleValue) => {
  switch (roleValue) {
    case 'client':
      return 'rgba(245, 158, 11, 0.5)' // amber
    case 'distributor':
      return 'rgba(168, 85, 247, 0.5)' // purple
    case 'service_provider':
      return 'rgba(16, 185, 129, 0.5)' // emerald
    default:
      return 'rgba(59, 130, 246, 0.5)' // blue
  }
}

// Format phone number - only allow numbers
const formatPhoneNumber = () => {
  // Remove all non-numeric characters
  form.phone = form.phone.replace(/\D/g, '')
  
  // Format as 09XX XXX XXXX
  if (form.phone.length > 3 && form.phone.length <= 6) {
    form.phone = form.phone.replace(/(\d{3})(\d+)/, '$1 $2')
  } else if (form.phone.length > 6 && form.phone.length <= 10) {
    form.phone = form.phone.replace(/(\d{3})(\d{3})(\d+)/, '$1 $2 $3')
  } else if (form.phone.length > 10) {
    form.phone = form.phone.substring(0, 11).replace(/(\d{3})(\d{3})(\d{4})/, '$1 $2 $3')
  }
}

// Password strength calculator with enhanced requirements
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

// Check if password meets all requirements
const validatePassword = () => {
  const password = form.password
  const hasLower = /[a-z]/.test(password)
  const hasUpper = /[A-Z]/.test(password)
  const hasNumber = /\d/.test(password)
  const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password)
  const hasMinLength = password.length >= 8
  
  return {
    hasLower,
    hasUpper,
    hasNumber,
    hasSpecial,
    hasMinLength,
    isValid: hasLower && hasUpper && hasNumber && hasSpecial && hasMinLength
  }
}

// Auto-update full name
const updateFullName = () => {
  if (form.firstName || form.lastName) {
    form.fullName = `${form.firstName} ${form.lastName}`.trim()
  } else {
    form.fullName = ''
  }
}

// Watch for password match
watch(() => [form.password, form.confirmPassword], () => {
  if (form.confirmPassword && form.password !== form.confirmPassword) {
    validationErrors.confirmPassword = 'Passwords do not match'
  } else {
    delete validationErrors.confirmPassword
  }
})

// Page load animation
onMounted(async () => {
  // Wait for next tick to ensure DOM is ready
  await nextTick()
  
  // Trigger the page container animation
  if (pageContainer.value) {
    pageContainer.value.style.opacity = '1'
    pageContainer.value.style.transition = 'opacity 0.8s ease-in-out'
  }
  
  // Trigger the main card animation
  if (signupCard.value) {
    setTimeout(() => {
      signupCard.value.style.opacity = '1'
      signupCard.value.style.transform = 'translateY(0)'
      signupCard.value.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)'
    }, 300)
  }
})

// Validation rules
const validateForm = () => {
  // Clear previous errors
  Object.keys(validationErrors).forEach(key => delete validationErrors[key])
  let isValid = true

  // First Name validation
  if (!form.firstName.trim()) {
    validationErrors.firstName = 'First name is required'
    isValid = false
  }

  // Last Name validation
  if (!form.lastName.trim()) {
    validationErrors.lastName = 'Last name is required'
    isValid = false
  }

  // Phone validation - only numbers
  if (!form.phone.trim()) {
    validationErrors.phone = 'Phone number is required'
    isValid = false
  } else {
    // Remove all non-numeric characters for validation
    const numericPhone = form.phone.replace(/\D/g, '')
    
    if (numericPhone.length < 10) {
      validationErrors.phone = 'Phone number must be at least 10 digits'
      isValid = false
    } else if (!/^09\d{9}$/.test(numericPhone)) {
      validationErrors.phone = 'Please enter a valid Philippine mobile number (09XXXXXXXXX)'
      isValid = false
    }
  }

  // Address validation
  if (!form.address.trim()) {
    validationErrors.address = 'Address is required'
    isValid = false
  }

  // Email validation
  if (!form.email) {
    validationErrors.email = 'Email is required'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    validationErrors.email = 'Please enter a valid email address'
    isValid = false
  }

  // Password validation with enhanced requirements
  if (!form.password) {
    validationErrors.password = 'Password is required'
    isValid = false
  } else {
    const passwordValidation = validatePassword()
    
    if (!passwordValidation.hasMinLength) {
      validationErrors.password = 'Password must be at least 8 characters'
      isValid = false
    } else if (!passwordValidation.hasUpper) {
      validationErrors.password = 'Password must contain at least one uppercase letter'
      isValid = false
    } else if (!passwordValidation.hasNumber) {
      validationErrors.password = 'Password must contain at least one number'
      isValid = false
    } else if (!passwordValidation.hasSpecial) {
      validationErrors.password = 'Password must contain at least one special character (!@#$%^&* etc.)'
      isValid = false
    } else if (!passwordValidation.hasLower) {
      validationErrors.password = 'Password must contain at least one lowercase letter'
      isValid = false
    }
  }

  // Confirm Password validation
  if (!form.confirmPassword) {
    validationErrors.confirmPassword = 'Please confirm your password'
    isValid = false
  } else if (form.password !== form.confirmPassword) {
    validationErrors.confirmPassword = 'Passwords do not match'
    isValid = false
  }

  // Terms validation
  if (!form.terms) {
    showNotification('Terms Required', 'Please agree to the terms and conditions', 'error')
    isValid = false
  }

  return isValid
}

// Show notification
const showNotification = (title, message, type = 'info') => {
  toastTitle.value = title
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 3000) // Reduced from 5000 to 3000 for role selection feedback
}

// Handle signup with API
const handleSignup = async () => {
  if (!validateForm()) {
    return
  }

  isLoading.value = true

  try {
    // Prepare data for API
    const userData = {
      first_name: form.firstName,
      last_name: form.lastName,
      email: form.email,
      phone: form.phone,
      address: form.address,
      password: form.password,
      password_confirmation: form.confirmPassword,
      role: form.role,
      terms: form.terms
    }

    // Send API request
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
);

    // Clear form
    Object.keys(form).forEach(key => {
      if (typeof form[key] === 'boolean') {
        form[key] = false
      } else {
        form[key] = ''
      }
    })
    form.role = 'client'

    // Store token in localStorage if needed
    if (data.data && data.data.token) {
      localStorage.setItem('auth_token', data.data.token)
    }

    // Redirect to login after delay
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
  @import "../landingPage/styleFolder/signUp.css";
</style>