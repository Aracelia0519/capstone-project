<template>
  <div ref="pageContainer" class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center p-4 overflow-hidden relative">
    
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-1/4 left-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-blue-500/20 to-cyan-400/20 rounded-full blur-3xl animate-float-slow"></div>
        <div class="absolute top-12 left-12 w-32 h-32 bg-gradient-to-r from-purple-500/15 to-pink-500/15 rounded-full blur-2xl animate-float-medium"></div>
      </div>
      
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96">
        <div class="absolute w-48 h-48 bg-gradient-to-r from-emerald-500/15 to-teal-400/15 rounded-full blur-3xl animate-float-slow delay-1000"></div>
        <div class="absolute bottom-12 right-12 w-32 h-32 bg-gradient-to-r from-amber-500/10 to-yellow-400/10 rounded-full blur-2xl animate-float-medium delay-500"></div>
      </div>

      <div class="absolute top-1/3 right-1/3 w-64 h-96 opacity-10">
        <div class="absolute w-8 h-40 bg-gradient-to-b from-blue-400 to-transparent rounded-full transform rotate-45 animate-brush-stroke-1"></div>
      </div>
      
      <div class="absolute bottom-1/3 left-1/3 w-64 h-96 opacity-10">
        <div class="absolute w-8 h-32 bg-gradient-to-t from-purple-400 to-transparent rounded-full transform -rotate-12 animate-brush-stroke-2"></div>
      </div>

      <div class="absolute top-20 left-20 w-8 h-8 bg-gradient-to-r from-blue-400/30 to-cyan-300/30 rounded-full animate-bounce-droplet"></div>
      <div class="absolute top-40 right-40 w-6 h-6 bg-gradient-to-r from-purple-400/30 to-pink-300/30 rounded-full animate-bounce-droplet delay-300"></div>
      <div class="absolute bottom-20 left-32 w-7 h-7 bg-gradient-to-r from-emerald-400/30 to-teal-300/30 rounded-full animate-bounce-droplet delay-700"></div>
      <div class="absolute bottom-40 right-20 w-5 h-5 bg-gradient-to-r from-amber-400/30 to-yellow-300/30 rounded-full animate-bounce-droplet delay-1000"></div>

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

    <div ref="signupCard" class="relative w-full max-w-4xl z-10 opacity-0 translate-y-10 mx-auto my-6">
      <div class="flex flex-col lg:flex-row bg-gray-800/30 backdrop-blur-2xl rounded-3xl shadow-2xl border border-gray-700/30 overflow-hidden transform transition-all duration-700 hover:scale-[1.01] mx-2">
        
        <div class="lg:w-2/6 p-6 lg:p-8 bg-gradient-to-br from-blue-900/20 via-purple-900/20 to-pink-900/20 flex flex-col justify-center items-center relative overflow-hidden">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 via-purple-500/5 to-pink-500/5"></div>
          
          <div class="relative mb-5 text-center">
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-400 via-purple-500 to-pink-500 shadow-2xl flex items-center justify-center mb-3 mx-auto transform transition-all duration-500 hover:rotate-12 hover:scale-110">
              <svg class="w-10 h-10 text-white drop-shadow-lg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
            </div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-300 via-purple-300 to-pink-300 bg-clip-text text-transparent">
              CaviteGo Paint
            </h1>
            <p class="text-gray-300/80 mt-1 text-sm">Join Our Colorful Community</p>
          </div>

          <div class="w-full max-w-xs space-y-5">
            <div class="relative">
              <Progress :model-value="(currentStep / steps.length) * 100" class="h-1 bg-gray-700/50" indicator-class="bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500" />
              
              <div class="flex justify-between -mt-2.5 relative z-10">
                <div v-for="(step, index) in steps" :key="step.id" class="flex flex-col items-center">
                  <button
                    @click="goToStep(index + 1)"
                    :disabled="index + 1 > currentStep"
                    class="relative w-5 h-5 rounded-full flex items-center justify-center transition-all duration-300 border-2"
                    :class="[
                      index + 1 < currentStep ? 'bg-green-500 border-green-500' : 
                      index + 1 === currentStep ? 'bg-blue-500 border-blue-400 ring-2 ring-blue-500/30' : 
                      'bg-gray-800 border-gray-600'
                    ]"
                  >
                    <svg v-if="index + 1 < currentStep" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                  </button>
                  <span class="mt-2 text-[9px] font-medium uppercase tracking-wider"
                        :class="index + 1 === currentStep ? 'text-white' : 'text-gray-500'">
                    {{ step.title }}
                  </span>
                </div>
              </div>
            </div>

            <Card class="bg-gray-900/40 border-gray-700/30 backdrop-blur-sm">
              <CardContent class="p-3">
                <h3 class="text-white font-semibold mb-1 text-sm">{{ steps[currentStep - 1].title }}</h3>
                <p class="text-gray-300 text-xs leading-relaxed">
                  {{ steps[currentStep - 1].description }}
                </p>
                <div v-if="steps[currentStep - 1].tips" class="mt-2 text-[10px] text-gray-400 italic">
                  {{ steps[currentStep - 1].tips }}
                </div>
              </CardContent>
            </Card>

            <div v-if="form.role" class="p-3 rounded-xl bg-gradient-to-br from-blue-900/20 to-purple-900/20 border border-blue-700/30 backdrop-blur-sm flex items-center space-x-3">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getRoleGradient(form.role)">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-for="path in getRoleIcon(form.role)" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-white">{{ getRoleLabel(form.role) }}</p>
                <p class="text-[10px] text-gray-300">Selected Role</p>
              </div>
            </div>
          </div>

          <div class="mt-6 text-center">
            <p class="text-gray-400/70 text-xs italic">"Color Your World With Us"</p>
          </div>
        </div>

        <div class="lg:w-4/6 p-6 lg:p-8">
          <div class="text-center mb-6">
            <h2 class="text-xl font-bold text-white mb-1">Create Your Account</h2>
            <p class="text-gray-400 text-sm">Step {{ currentStep }} of {{ steps.length }}: {{ steps[currentStep - 1].title }}</p>
          </div>

          <div class="step-content relative min-h-[350px]">
            <transition name="step" mode="out-in">
              <div v-if="currentStep === 1" key="step1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div 
                    v-for="role in roles" 
                    :key="role.value"
                    @click="selectRole(role.value)"
                    class="cursor-pointer group relative"
                  >
                    <div class="h-full p-3 rounded-xl border-2 transition-all duration-300 transform hover:scale-[1.02] hover:-translate-y-1"
                         :class="[
                           form.role === role.value 
                             ? 'border-opacity-100 scale-[1.02] -translate-y-1 shadow-2xl bg-gray-800/80' 
                             : 'border-gray-700/50 bg-gray-900/40 hover:border-gray-600'
                         ]"
                         :style="{
                           borderColor: form.role === role.value ? getRoleBorderColor(role.value) : ''
                         }">
                      
                      <div class="flex items-center space-x-3 mb-2">
                          <div class="w-10 h-10 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
                              :class="role.gradient">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path v-for="path in role.icon" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                            </svg>
                          </div>
                          <div>
                             <h4 class="text-sm font-semibold text-white">{{ role.label }}</h4>
                             <div v-if="form.role === role.value">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-medium"
                                      :class="getRoleBadgeClass(role.value)">
                                  Selected
                                </span>
                              </div>
                          </div>
                      </div>
                      
                      <p class="text-gray-400 text-xs pl-1">{{ role.description }}</p>
                    </div>
                  </div>
                </div>

                <div class="mt-6 p-3 rounded-xl bg-blue-900/10 border border-blue-700/20 flex items-start space-x-3">
                  <svg class="w-4 h-4 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <div>
                    <p class="text-xs text-white font-medium mb-0.5">Need help choosing?</p>
                    <p class="text-[10px] text-gray-300">
                      Roles define your account permissions. Suppliers provide materials, Distributors sell, and Service Providers offer labor.
                    </p>
                  </div>
                </div>
              </div>

              <div v-else-if="currentStep === 2" key="step2" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <Label class="text-gray-300 text-xs">First Name</Label>
                    <div class="relative">
                      <Input
                        v-model="form.firstName"
                        placeholder="Julian"
                        class="pl-9 h-9 text-sm bg-gray-900/50 border-gray-700/50 text-white placeholder:text-gray-600 focus:border-blue-500/50 focus:ring-blue-500/20"
                        :class="{'border-red-500/50': validationErrors.firstName}"
                        @input="validateStep2"
                      />
                      <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <span v-if="validationErrors.firstName" class="text-[10px] text-red-400">{{ validationErrors.firstName }}</span>
                  </div>

                  <div class="space-y-1.5">
                    <Label class="text-gray-300 text-xs">Last Name</Label>
                    <div class="relative">
                      <Input
                        v-model="form.lastName"
                        placeholder="Namoc"
                        class="pl-9 h-9 text-sm bg-gray-900/50 border-gray-700/50 text-white placeholder:text-gray-600 focus:border-purple-500/50 focus:ring-purple-500/20"
                        :class="{'border-red-500/50': validationErrors.lastName}"
                        @input="validateStep2"
                      />
                      <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                    <span v-if="validationErrors.lastName" class="text-[10px] text-red-400">{{ validationErrors.lastName }}</span>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-1.5">
                    <Label class="text-gray-300 text-xs">Email Address</Label>
                    <div class="relative">
                      <Input
                        v-model="form.email"
                        type="email"
                        placeholder="juji@example.com"
                        class="pl-9 h-9 text-sm bg-gray-900/50 border-gray-700/50 text-white placeholder:text-gray-600 focus:border-blue-500/50 focus:ring-blue-500/20"
                        :class="{'border-red-500/50': validationErrors.email}"
                        @input="validateStep2"
                      />
                      <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                      </svg>
                    </div>
                    <span v-if="validationErrors.email" class="text-[10px] text-red-400">{{ validationErrors.email }}</span>
                  </div>

                  <div class="space-y-1.5">
                    <Label class="text-gray-300 text-xs">Phone Number</Label>
                    <div class="relative">
                      <Input
                        v-model="form.phone"
                        placeholder="0912 345 6789"
                        class="pl-9 h-9 text-sm bg-gray-900/50 border-gray-700/50 text-white placeholder:text-gray-600 focus:border-cyan-500/50 focus:ring-cyan-500/20"
                        :class="{'border-red-500/50': validationErrors.phone}"
                        @input="formatPhoneNumber"
                      />
                      <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                      </svg>
                    </div>
                    <span v-if="validationErrors.phone" class="text-[10px] text-red-400">{{ validationErrors.phone }}</span>
                    <span v-else-if="form.phone" class="text-[10px] text-cyan-400">Format: 09XX XXX XXXX</span>
                  </div>
                </div>
              </div>

              <div v-else-if="currentStep === 3" key="step3" class="space-y-4">
                <div class="space-y-1.5">
                  <Label class="text-gray-300 text-xs">Password</Label>
                  <div class="relative">
                    <Input
                      v-model="form.password"
                      :type="showPassword ? 'text' : 'password'"
                      placeholder="Create a strong password"
                      class="pl-9 pr-9 h-9 text-sm bg-gray-900/50 border-gray-700/50 text-white placeholder:text-gray-600 focus:border-purple-500/50 focus:ring-purple-500/20"
                      :class="{'border-red-500/50': validationErrors.password}"
                      @input="validateStep3"
                    />
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-gray-500 hover:text-white">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="showPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                  </div>
                  
                  <div v-if="form.password" class="space-y-1.5">
                    <div class="flex items-center gap-2">
                      <Progress :model-value="passwordStrengthScore" class="h-1 flex-1 bg-gray-700" :indicator-class="passwordStrengthClass" />
                      <span class="text-[10px] font-medium" :class="passwordStrengthTextClass">{{ passwordStrength }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1">
                      <div v-for="(req, i) in passwordRequirements" :key="i" class="flex items-center space-x-2 text-[10px]" :class="req.met ? 'text-green-400' : 'text-gray-500'">
                        <span class="w-1 h-1 rounded-full" :class="req.met ? 'bg-green-400' : 'bg-gray-600'"></span>
                        <span>{{ req.text }}</span>
                      </div>
                    </div>
                  </div>
                  <span v-if="validationErrors.password" class="text-[10px] text-red-400">{{ validationErrors.password }}</span>
                </div>

                <div class="space-y-1.5">
                  <Label class="text-gray-300 text-xs">Confirm Password</Label>
                  <div class="relative">
                    <Input
                      v-model="form.confirmPassword"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      placeholder="Re-enter password"
                      class="pl-9 pr-9 h-9 text-sm bg-gray-900/50 border-gray-700/50 text-white placeholder:text-gray-600 focus:border-green-500/50 focus:ring-green-500/20"
                      :class="{'border-red-500/50': validationErrors.confirmPassword}"
                      @input="validateStep3"
                    />
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2.5 text-gray-500 hover:text-white">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                  </div>
                  <span v-if="validationErrors.confirmPassword" class="text-[10px] text-red-400">{{ validationErrors.confirmPassword }}</span>
                </div>
              </div>

              <div v-else-if="currentStep === 4" key="step4" class="space-y-4">
                <Card class="bg-gray-900/50 border-gray-700/30">
                  <CardHeader class="p-4 pb-2">
                    <CardTitle class="text-white text-base">Account Summary</CardTitle>
                  </CardHeader>
                  <CardContent class="p-4 pt-2 space-y-3">
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-800/50">
                      <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" :class="getRoleGradient(form.role)">
                           <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path v-for="path in getRoleIcon(form.role)" :key="path" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                          </svg>
                        </div>
                        <div>
                          <p class="text-sm font-medium text-white">Role</p>
                          <p class="text-xs text-gray-300">{{ getRoleLabel(form.role) }}</p>
                        </div>
                      </div>
                      <Button variant="ghost" size="sm" @click="goToStep(1)" class="text-blue-400 hover:text-blue-300 text-xs h-7">Edit</Button>
                    </div>

                    <div class="p-3 rounded-lg bg-gray-800/50 space-y-2">
                       <div class="flex justify-between items-center">
                          <h5 class="text-xs font-medium text-white">Personal Information</h5>
                          <Button variant="ghost" size="sm" @click="goToStep(2)" class="text-blue-400 hover:text-blue-300 h-auto p-0 text-xs">Edit</Button>
                       </div>
                       <div class="grid grid-cols-2 gap-2 text-xs">
                          <div>
                             <p class="text-gray-500">Name</p>
                             <p class="text-white">{{ form.firstName }} {{ form.lastName }}</p>
                          </div>
                          <div>
                             <p class="text-gray-500">Phone</p>
                             <p class="text-white">{{ form.phone }}</p>
                          </div>
                          <div class="col-span-2">
                             <p class="text-gray-500">Email</p>
                             <p class="text-white">{{ form.email }}</p>
                          </div>
                       </div>
                    </div>
                  </CardContent>
                </Card>

                <div class="flex items-start space-x-3 p-3 rounded-xl bg-blue-900/10 border border-blue-700/20">
                  <Checkbox 
                    id="terms" 
                    v-model="form.terms"
                    class="mt-1 border-gray-500 data-[state=checked]:bg-blue-500 data-[state=checked]:border-blue-500 w-4 h-4" 
                  />
                  <div class="grid gap-1 leading-none">
                    <Label for="terms" class="text-xs font-medium text-gray-300 cursor-pointer">
                      I agree to the <span class="text-blue-400 hover:underline cursor-pointer" @click.stop="showTerms">Terms & Conditions</span> and <span class="text-purple-400 hover:underline cursor-pointer" @click.stop="showPrivacy">Privacy Policy</span>.
                    </Label>
                    <p class="text-[10px] text-gray-400">
                      I understand that my account will be verified based on my selected role.
                    </p>
                    <p v-if="validationErrors.terms" class="text-[10px] text-red-400 mt-1">{{ validationErrors.terms }}</p>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <div class="mt-6 pt-4 border-t border-gray-700/30 flex justify-between items-center">
            <Button
              v-if="currentStep > 1"
              variant="outline"
              @click="prevStep"
              class="border-gray-600 text-gray-300 hover:bg-gray-800 hover:text-white h-9 text-xs"
            >
              Back
            </Button>
            <div v-else></div>

            <Button
              v-if="currentStep < steps.length"
              @click="nextStep"
              :disabled="!isStepValid"
              class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white border-0 h-9 text-xs"
            >
              {{ steps[currentStep - 1].nextButton || 'Continue' }}
              <svg class="w-3 h-3 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </Button>

            <Button
              v-else
              @click="handleSignup"
              :disabled="!isStepValid || isLoading"
              class="relative overflow-hidden bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white border-0 px-6 py-4 group h-10 text-sm"
            >
              <div class="absolute inset-0 bg-white/20 skew-x-12 -translate-x-full group-hover:translate-x-[400%] transition-all duration-1000"></div>
              <span v-if="isLoading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Creating...
              </span>
              <span v-else>Create Account</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <Toaster theme="dark" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Progress } from '@/components/ui/progress'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'

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
    description: 'Choose your role',
    tips: 'Verification required later',
    nextButton: 'Next'
  },
  { 
    id: 2, 
    title: 'Personal', 
    description: 'Basic details',
    tips: 'Valid info required',
    nextButton: 'Next'
  },
  { 
    id: 3, 
    title: 'Security', 
    description: 'Secure account',
    tips: 'Use strong password',
    nextButton: 'Finish'
  },
  { 
    id: 4, 
    title: 'Review', 
    description: 'Confirm details',
    tips: 'Check before submit'
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
const validationErrors = reactive({})

// Role options with enhanced details - ADDED SUPPLIER
const roles = [
  {
    value: 'client',
    label: 'Client',
    description: 'Purchase paints & track orders',
    gradient: 'bg-gradient-to-br from-amber-500 to-yellow-400',
    icon: ['M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'],
    features: ['Browse paint catalog', 'Track orders online', 'Request quotes', 'Schedule consultations']
  },
  {
    value: 'distributor',
    label: 'Distributor',
    description: 'Sell & distribute products',
    gradient: 'bg-gradient-to-br from-purple-500 to-pink-400',
    icon: ['M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
    features: ['Manage inventory', 'Track sales & revenue', 'Access distributor pricing', 'Order in bulk']
  },
  {
    value: 'service_provider',
    label: 'Service Provider',
    description: 'Offer painting services',
    gradient: 'bg-gradient-to-br from-emerald-500 to-teal-400',
    icon: ['M13 10V3L4 14h7v7l9-11h-7z'],
    features: ['List your services', 'Get client requests', 'Manage appointments', 'Showcase your portfolio']
  },
  {
    value: 'supplier',
    label: 'Supplier',
    description: 'Supply raw materials',
    gradient: 'bg-gradient-to-br from-red-500 to-orange-400',
    icon: ['M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
    features: ['Manage supply orders', 'Coordinate with distributors', 'Inventory tracking', 'B2B features']
  }
]

// Password strength calculation
const passwordStrength = computed(() => {
  if (!form.password) return ''
  const score = passwordStrengthScore.value
  if (score < 40) return 'Weak'
  if (score < 70) return 'Fair'
  if (score < 90) return 'Good'
  return 'Strong'
})

const passwordStrengthScore = computed(() => {
  if (!form.password) return 0
  let score = 0
  const p = form.password
  if (p.length >= 8) score += 20
  if (p.length >= 12) score += 10
  if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score += 20
  if (/\d/.test(p)) score += 20
  if (/[!@#$%^&*(),.?":{}|<>]/.test(p)) score += 30
  return Math.min(100, score)
})

const passwordStrengthClass = computed(() => {
  const s = passwordStrength.value
  if (s === 'Weak') return 'bg-red-500'
  if (s === 'Fair') return 'bg-amber-500'
  if (s === 'Good') return 'bg-blue-500'
  return 'bg-emerald-500'
})

const passwordStrengthTextClass = computed(() => {
  const s = passwordStrength.value
  if (s === 'Weak') return 'text-red-400'
  if (s === 'Fair') return 'text-amber-400'
  if (s === 'Good') return 'text-blue-400'
  return 'text-emerald-400'
})

const passwordRequirements = computed(() => {
  const p = form.password
  return [
    { text: '8+ chars', met: p.length >= 8 },
    { text: 'Upper', met: /[A-Z]/.test(p) },
    { text: 'Lower', met: /[a-z]/.test(p) },
    { text: 'Number', met: /\d/.test(p) },
    { text: 'Special', met: /[!@#$%^&*(),.?":{}|<>]/.test(p) }
  ]
})

// Step validation logic
const isStepValid = computed(() => {
  switch (currentStep.value) {
    case 1: return form.role !== ''
    case 2: return validateStep2(true)
    case 3: return validateStep3(true)
    case 4: return form.terms && validateStep2(true) && validateStep3(true)
    default: return false
  }
})

// Helper functions
const getRoleGradient = (roleValue) => roles.find(r => r.value === roleValue)?.gradient || 'bg-gray-500'
const getRoleIcon = (roleValue) => roles.find(r => r.value === roleValue)?.icon || []
const getRoleLabel = (roleValue) => roles.find(r => r.value === roleValue)?.label || 'Unknown'
const getRoleBorderColor = (roleValue) => {
  const colors = { client: '#f59e0b', distributor: '#a855f7', service_provider: '#10b981', supplier: '#ef4444' }
  return colors[roleValue] || '#3b82f6'
}
const getRoleBadgeClass = (roleValue) => {
  const classes = {
    client: 'bg-amber-500/20 text-amber-300 border border-amber-500/30',
    distributor: 'bg-purple-500/20 text-purple-300 border border-purple-500/30',
    service_provider: 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30',
    supplier: 'bg-red-500/20 text-red-300 border border-red-500/30'
  }
  return classes[roleValue]
}

// Actions
const selectRole = (roleValue) => {
  form.role = roleValue
  // Removed toast to make it faster/less annoying
}

const formatPhoneNumber = () => {
  let val = form.phone.replace(/\D/g, '')
  if (val.length > 11) val = val.substring(0, 11)
  
  if (val.length > 6) val = val.replace(/(\d{4})(\d{3})(\d+)/, '$1 $2 $3')
  else if (val.length > 4) val = val.replace(/(\d{4})(\d+)/, '$1 $2')
  
  form.phone = val
}

const validateStep2 = (silent = false) => {
  const errors = {}
  let isValid = true

  if (!form.firstName.trim()) { errors.firstName = 'Required'; isValid = false }
  if (!form.lastName.trim()) { errors.lastName = 'Required'; isValid = false }
  
  if (!form.email) { errors.email = 'Required'; isValid = false }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) { errors.email = 'Invalid email'; isValid = false }
  
  if (!form.phone.trim()) { errors.phone = 'Required'; isValid = false }
  else {
    const num = form.phone.replace(/\D/g, '')
    if (num.length < 11 || !num.startsWith('09')) { errors.phone = 'Invalid PH number'; isValid = false }
  }

  if (!silent) Object.assign(validationErrors, errors)
  return isValid
}

const validateStep3 = (silent = false) => {
  const errors = {}
  let isValid = true

  if (!form.password) { errors.password = 'Required'; isValid = false }
  else if (passwordStrengthScore.value < 100 && form.password.length < 8) { errors.password = 'Weak password'; isValid = false }
  
  if (!form.confirmPassword) { errors.confirmPassword = 'Required'; isValid = false }
  else if (form.password !== form.confirmPassword) { errors.confirmPassword = 'Mismatch'; isValid = false }

  if (!silent) Object.assign(validationErrors, errors)
  return isValid
}

const nextStep = () => { if (currentStep.value < steps.length && isStepValid.value) currentStep.value++ }
const prevStep = () => { if (currentStep.value > 1) currentStep.value-- }
const goToStep = (step) => { if (step >= 1 && step <= steps.length && step < currentStep.value + 1) currentStep.value = step }

const handleSignup = async () => {
  if (!form.terms) {
    validationErrors.terms = 'You must agree to the terms'
    toast.error('Terms Required', { description: 'Please agree to the terms' })
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
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(userData)
    })
    
    const data = await response.json()
    if (!response.ok) throw new Error(data.message || 'Registration failed')
    
    toast.success('Success!', { description: 'Account created. Please verify your email.' })
    setTimeout(() => router.push('/Landing/logIn'), 3000)
    
  } catch (error) {
    toast.error('Registration Failed', { description: error.message })
  } finally {
    isLoading.value = false
  }
}

const showTerms = () => toast.info('Terms & Conditions', { description: 'Coming soon...' })
const showPrivacy = () => toast.info('Privacy Policy', { description: 'Coming soon...' })

onMounted(async () => {
  await nextTick()
  if (pageContainer.value) pageContainer.value.style.opacity = '1'
  if (signupCard.value) {
    setTimeout(() => {
      signupCard.value.style.opacity = '1'
      signupCard.value.style.transform = 'translateY(0)'
    }, 300)
  }
})

watch(currentStep, () => {
  for (const key in validationErrors) delete validationErrors[key]
})
</script>

<style scoped>
/* Preserved custom animations */
.step-enter-active, .step-leave-active { transition: all 0.3s ease; }
.step-enter-from { opacity: 0; transform: translateX(20px); }
.step-leave-to { opacity: 0; transform: translateX(-20px); }

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

.animate-float-slow { animation: float-slow 8s ease-in-out infinite; }
.animate-float-medium { animation: float-medium 6s ease-in-out infinite; }
.animate-brush-stroke-1 { animation: brush-stroke-1 10s ease-in-out infinite; }
.animate-brush-stroke-2 { animation: brush-stroke-2 12s ease-in-out infinite; }
.animate-bounce-droplet { animation: bounce-droplet 4s ease-in-out infinite; }

::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); }
::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #3b82f6, #8b5cf6, #ec4899); border-radius: 4px; }
</style>