<template>
    <div class="min-h-screen bg-[#070a14] flex items-center justify-center p-4 overflow-hidden relative">

        <!-- ===== STATIC BACKGROUND (painted once, no blur / no animation) ===== -->
        <div class="absolute inset-0"
            style="background: radial-gradient(600px 400px at 15% 20%, rgba(59,130,246,0.16), transparent 70%), radial-gradient(600px 400px at 85% 80%, rgba(168,85,247,0.14), transparent 70%), radial-gradient(500px 350px at 50% 110%, rgba(236,72,153,0.10), transparent 70%);"></div>

        <!-- ===== MAIN CARD ===== -->
        <div class="relative w-full max-w-4xl z-10 my-6">
            <div class="auth-card bg-gray-900/95 border border-white/10 rounded-2xl shadow-2xl overflow-hidden">

                <!-- Accent bar -->
                <div class="h-1.5 bg-gradient-to-r from-blue-500 via-violet-500 to-pink-500"></div>

                <div class="flex flex-col lg:flex-row">

                    <!-- ===== LEFT PANEL (Branding + Stepper) ===== -->
                    <div
                        class="lg:w-2/6 p-6 sm:p-8 bg-white/[0.03] border-b lg:border-b-0 lg:border-r border-white/10 flex flex-col justify-center items-center text-center">

                        <img src="/favicon.svg" class="w-14 h-14" alt="CaviteGo Paint" />
                        <h1 class="text-xl font-bold text-white mt-3 tracking-tight">CaviteGo Paint</h1>
                        <p class="text-slate-400 text-sm mt-1">Join Our Colorful Community</p>

                        <div class="w-full max-w-xs mt-6 space-y-5">
                            <!-- ===== PROGRESS + STEPPER ===== -->
                            <div>
                                <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-violet-500 rounded-full"
                                        :style="{ width: (currentStep / steps.length) * 100 + '%' }"></div>
                                </div>

                                <div class="flex justify-between mt-3">
                                    <button v-for="(step, index) in steps" :key="step.id" @click="goToStep(index + 1)"
                                        :disabled="index + 1 > currentStep"
                                        class="flex flex-col items-center gap-1.5 disabled:cursor-default">
                                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold border-2 transition-colors"
                                            :class="[
                                                index + 1 < currentStep ? 'bg-emerald-500 border-emerald-500 text-white' :
                                                index + 1 === currentStep ? 'bg-blue-500 border-blue-400 text-white' :
                                                'bg-white/5 border-white/15 text-slate-500'
                                            ]">
                                            <svg v-if="index + 1 < currentStep" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span v-else>{{ index + 1 }}</span>
                                        </span>
                                        <span class="text-[9px] font-medium uppercase tracking-wider"
                                            :class="index + 1 === currentStep ? 'text-white' : 'text-slate-500'">
                                            {{ step.title }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- ===== STEP INFO CARD ===== -->
                            <div class="p-3 rounded-xl bg-white/[0.03] border border-white/10 text-left">
                                <h3 class="text-white font-semibold mb-1 text-sm">{{ steps[currentStep - 1].title }}</h3>
                                <p class="text-slate-400 text-xs leading-relaxed">
                                    {{ steps[currentStep - 1].description }}
                                </p>
                                <p v-if="steps[currentStep - 1].tips" class="mt-2 text-[10px] text-slate-500 italic">
                                    {{ steps[currentStep - 1].tips }}
                                </p>
                            </div>

                            <!-- ===== SELECTED ROLE DISPLAY ===== -->
                            <div v-if="form.role"
                                class="p-3 rounded-xl bg-white/[0.03] border border-white/10 flex items-center gap-3 text-left">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                    :class="getRoleGradient(form.role)">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-for="path in getRoleIcon(form.role)" :key="path" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="2" :d="path"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ getRoleLabel(form.role) }}</p>
                                    <p class="text-[10px] text-slate-400">Selected Role</p>
                                </div>
                                <span class="ml-auto w-2 h-2 rounded-full bg-emerald-400"></span>
                            </div>
                        </div>

                        <p class="hidden lg:block mt-6 text-xs text-slate-500 italic">"Color Your World With Us"</p>
                    </div>

                    <!-- ===== RIGHT PANEL (Form) ===== -->
                    <div class="lg:w-4/6 p-6 sm:p-8">
                        <div class="text-center mb-6">
                            <h2 class="text-xl font-bold text-white">Create Your Account</h2>
                            <p class="text-slate-400 text-sm mt-1">
                                Step {{ currentStep }} of {{ steps.length }}: {{ steps[currentStep - 1].title }}
                            </p>
                        </div>

                        <!-- ===== STEP CONTENT ===== -->
                        <div class="min-h-[340px]">
                            <transition name="step-fade" mode="out-in">
                                <!-- STEP 1: ROLE SELECTION -->
                                <div v-if="currentStep === 1" key="step1">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div v-for="role in roles" :key="role.value" @click="selectRole(role.value)"
                                            class="cursor-pointer p-3 rounded-xl border transition-colors text-left"
                                            :class="[
                                                form.role === role.value
                                                    ? 'bg-white/[0.06]'
                                                    : 'border-white/10 bg-white/[0.02] hover:border-white/20'
                                            ]"
                                            :style="form.role === role.value ? { borderColor: getRoleBorderColor(role.value) } : {}">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                                                    :class="role.gradient">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path v-for="path in role.icon" :key="path" stroke-linecap="round"
                                                            stroke-linejoin="round" stroke-width="2" :d="path"></path>
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <h4 class="text-sm font-semibold text-white">{{ role.label }}</h4>
                                                    <span v-if="form.role === role.value"
                                                        class="inline-block mt-0.5 px-2 py-0.5 rounded-full text-[9px] font-medium"
                                                        :class="getRoleBadgeClass(role.value)">
                                                        Selected
                                                    </span>
                                                </div>
                                                <div v-if="form.role === role.value"
                                                    class="ml-auto w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/40 shrink-0">
                                                    <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <p class="text-slate-400 text-xs mt-2">{{ role.description }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-5 p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-start gap-3">
                                        <svg class="w-4 h-4 text-blue-400 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div>
                                            <p class="text-xs text-white font-medium mb-0.5">Need help choosing?</p>
                                            <p class="text-[10px] text-slate-300">
                                                Roles define your account permissions. Suppliers provide materials,
                                                Distributors sell, and Service Providers offer labor.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2: PERSONAL INFO -->
                                <div v-else-if="currentStep === 2" key="step2" class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <Label for="su-firstname" class="text-slate-300 text-xs">
                                                First Name <span class="text-red-400">*</span>
                                            </Label>
                                            <div class="relative">
                                                <Input id="su-firstname" v-model="form.firstName" placeholder="Julian"
                                                    class="pl-9 h-9 text-sm bg-white/5 border-white/10 text-white placeholder:text-slate-500 focus:border-blue-500/60 focus:ring-blue-500/20"
                                                    :class="{ 'border-red-500/60': validationErrors.firstName }"
                                                    @input="validateStep2" />
                                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <span v-if="validationErrors.firstName" class="text-[10px] text-red-400">
                                                {{ validationErrors.firstName }}
                                            </span>
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label for="su-lastname" class="text-slate-300 text-xs">
                                                Last Name <span class="text-red-400">*</span>
                                            </Label>
                                            <div class="relative">
                                                <Input id="su-lastname" v-model="form.lastName" placeholder="Namoc"
                                                    class="pl-9 h-9 text-sm bg-white/5 border-white/10 text-white placeholder:text-slate-500 focus:border-blue-500/60 focus:ring-blue-500/20"
                                                    :class="{ 'border-red-500/60': validationErrors.lastName }"
                                                    @input="validateStep2" />
                                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <span v-if="validationErrors.lastName" class="text-[10px] text-red-400">
                                                {{ validationErrors.lastName }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <Label for="su-email" class="text-slate-300 text-xs">
                                                Email Address <span class="text-red-400">*</span>
                                            </Label>
                                            <div class="relative">
                                                <Input id="su-email" v-model="form.email" type="email" placeholder="juji@example.com"
                                                    class="pl-9 h-9 text-sm bg-white/5 border-white/10 text-white placeholder:text-slate-500 focus:border-blue-500/60 focus:ring-blue-500/20"
                                                    :class="{ 'border-red-500/60': validationErrors.email }"
                                                    @input="validateStep2" />
                                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                                </svg>
                                            </div>
                                            <span v-if="validationErrors.email" class="text-[10px] text-red-400">
                                                {{ validationErrors.email }}
                                            </span>
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label for="su-phone" class="text-slate-300 text-xs">
                                                Phone Number <span class="text-red-400">*</span>
                                            </Label>
                                            <div class="relative">
                                                <Input id="su-phone" v-model="form.phone" placeholder="0912 345 6789"
                                                    class="pl-9 h-9 text-sm bg-white/5 border-white/10 text-white placeholder:text-slate-500 focus:border-blue-500/60 focus:ring-blue-500/20"
                                                    :class="{ 'border-red-500/60': validationErrors.phone }"
                                                    @input="formatPhoneNumber" />
                                                <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                            </div>
                                            <span v-if="validationErrors.phone" class="text-[10px] text-red-400">
                                                {{ validationErrors.phone }}
                                            </span>
                                            <span v-else-if="form.phone" class="text-[10px] text-slate-500">
                                                Format: 09XX XXX XXXX
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 3: SECURITY -->
                                <div v-else-if="currentStep === 3" key="step3" class="space-y-4">
                                    <div class="space-y-1.5">
                                        <Label for="su-password" class="text-slate-300 text-xs">
                                            Password <span class="text-red-400">*</span>
                                        </Label>
                                        <div class="relative">
                                            <Input id="su-password" v-model="form.password"
                                                :type="showPassword ? 'text' : 'password'" placeholder="Create a strong password"
                                                class="pl-9 pr-9 h-9 text-sm bg-white/5 border-white/10 text-white placeholder:text-slate-500 focus:border-blue-500/60 focus:ring-blue-500/20"
                                                :class="{ 'border-red-500/60': validationErrors.password }"
                                                @input="validateStep3" />
                                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <button type="button" @click="showPassword = !showPassword"
                                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                                class="absolute right-3 top-2.5 text-slate-500 hover:text-slate-300 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path v-if="showPassword" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>

                                        <div v-if="form.password" class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 flex-1 bg-white/10 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full" :class="passwordStrengthClass"
                                                        :style="{ width: passwordStrengthScore + '%' }"></div>
                                                </div>
                                                <span class="text-[10px] font-medium" :class="passwordStrengthTextClass">
                                                    {{ passwordStrength }}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-1">
                                                <div v-for="(req, i) in passwordRequirements" :key="i"
                                                    class="flex items-center gap-2 text-[10px] transition-colors"
                                                    :class="req.met ? 'text-emerald-400' : 'text-slate-500'">
                                                    <span class="w-1.5 h-1.5 rounded-full"
                                                        :class="req.met ? 'bg-emerald-400' : 'bg-slate-600'"></span>
                                                    <span>{{ req.text }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span v-if="validationErrors.password" class="text-[10px] text-red-400">
                                            {{ validationErrors.password }}
                                        </span>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label for="su-confirm" class="text-slate-300 text-xs">
                                            Confirm Password <span class="text-red-400">*</span>
                                        </Label>
                                        <div class="relative">
                                            <Input id="su-confirm" v-model="form.confirmPassword"
                                                :type="showConfirmPassword ? 'text' : 'password'" placeholder="Re-enter password"
                                                class="pl-9 pr-9 h-9 text-sm bg-white/5 border-white/10 text-white placeholder:text-slate-500 focus:border-blue-500/60 focus:ring-blue-500/20"
                                                :class="{ 'border-red-500/60': validationErrors.confirmPassword }"
                                                @input="validateStep3" />
                                            <svg class="absolute left-3 top-2.5 w-4 h-4 text-slate-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                                                class="absolute right-3 top-2.5 text-slate-500 hover:text-slate-300 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path v-if="showConfirmPassword" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2"
                                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <span v-if="validationErrors.confirmPassword" class="text-[10px] text-red-400">
                                            {{ validationErrors.confirmPassword }}
                                        </span>
                                    </div>
                                </div>

                                <!-- STEP 4: REVIEW -->
                                <div v-else-if="currentStep === 4" key="step4" class="space-y-4">
                                    <div class="p-4 rounded-xl bg-white/[0.03] border border-white/10 space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-semibold text-white flex items-center gap-2">
                                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Account Summary
                                            </h4>
                                        </div>

                                        <!-- Role -->
                                        <div class="flex items-center justify-between p-3 rounded-lg bg-white/[0.04] border border-white/10">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                                    :class="getRoleGradient(form.role)">
                                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path v-for="path in getRoleIcon(form.role)" :key="path"
                                                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            :d="path"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-white">Role</p>
                                                    <p class="text-xs text-slate-400">{{ getRoleLabel(form.role) }}</p>
                                                </div>
                                            </div>
                                            <button type="button" @click="goToStep(1)"
                                                class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                                                Edit
                                            </button>
                                        </div>

                                        <!-- Personal info -->
                                        <div class="p-3 rounded-lg bg-white/[0.04] border border-white/10 space-y-2">
                                            <div class="flex justify-between items-center">
                                                <h5 class="text-xs font-medium text-white">Personal Information</h5>
                                                <button type="button" @click="goToStep(2)"
                                                    class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                                                    Edit
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-2 gap-2 text-xs">
                                                <div>
                                                    <p class="text-slate-500">Name</p>
                                                    <p class="text-white font-medium">{{ form.firstName }} {{ form.lastName }}
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-slate-500">Phone</p>
                                                    <p class="text-white font-medium">{{ form.phone }}</p>
                                                </div>
                                                <div class="col-span-2">
                                                    <p class="text-slate-500">Email</p>
                                                    <p class="text-white font-medium">{{ form.email }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start gap-3 p-3 rounded-xl bg-blue-500/10 border border-blue-500/20">
                                        <Checkbox id="terms" v-model="form.terms"
                                            class="mt-1 border-white/30 data-[state=checked]:bg-blue-500 data-[state=checked]:border-blue-500 w-4 h-4" />
                                        <div class="grid gap-1 leading-none">
                                            <Label for="terms" class="text-xs font-medium text-slate-300 cursor-pointer">
                                                I agree to the <span class="text-blue-400 hover:underline cursor-pointer"
                                                    role="button" tabindex="0"
                                                    @click.stop="showTerms"
                                                    @keydown.enter.prevent.stop="showTerms">Terms & Conditions</span> and <span
                                                    class="text-violet-400 hover:underline cursor-pointer"
                                                    role="button" tabindex="0"
                                                    @click.stop="showPrivacy"
                                                    @keydown.enter.prevent.stop="showPrivacy">Privacy Policy</span>.
                                            </Label>
                                            <p class="text-[10px] text-slate-400">
                                                I understand that my account will be verified based on my selected role.
                                            </p>
                                            <p v-if="validationErrors.terms" class="text-[10px] text-red-400 mt-1">
                                                {{ validationErrors.terms }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- ===== NAVIGATION BUTTONS ===== -->
                        <div class="mt-6 pt-4 border-t border-white/10 flex justify-between items-center gap-3">
                            <Button v-if="currentStep > 1" variant="outline" @click="prevStep"
                                class="border-white/15 text-slate-300 hover:bg-white/5 hover:text-white h-9 text-xs transition-colors">
                                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Back
                            </Button>
                            <div v-else></div>

                            <Button v-if="currentStep < steps.length" @click="nextStep" :disabled="!isStepValid"
                                class="bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 text-white border-0 h-9 text-xs transition-colors">
                                {{ steps[currentStep - 1].nextButton || 'Continue' }}
                                <svg class="w-3 h-3 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </Button>

                            <Button v-else @click="handleSignup" :disabled="!isStepValid || isLoading"
                                class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white border-0 px-6 h-10 text-sm transition-colors">
                                <span v-if="isLoading" class="flex items-center gap-2">
                                    <span class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                                    Creating...
                                </span>
                                <span v-else class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Create Account
                                </span>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== LEGAL MODAL (Terms & Privacy) ===== -->
        <transition
            enter-active-class="transition-opacity duration-200 ease-out"
            leave-active-class="transition-opacity duration-150 ease-in"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div v-if="legalModal"
                class="fixed inset-0 z-[110] flex items-center justify-center bg-black/70 p-4"
                @click.self="closeLegal">
                <div class="bg-gray-900 border border-white/10 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">

                    <!-- Header -->
                    <div class="p-5 sm:p-6 border-b border-white/10 flex items-start gap-3 shrink-0">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 border"
                            :class="legalModal === 'terms'
                                ? 'bg-blue-500/15 border-blue-500/25 text-blue-400'
                                : 'bg-violet-500/15 border-violet-500/25 text-violet-400'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-lg font-bold text-white tracking-tight">{{ legalTitle }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">CaviteGo Paint · Last updated: October 8, 2026</p>
                        </div>
                        <button type="button" @click="closeLegal" aria-label="Close"
                            class="ml-auto shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/5 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="legal-body overflow-y-auto p-5 sm:p-6 space-y-5">
                        <p class="text-[13px] leading-relaxed text-slate-400 italic">{{ legalIntro }}</p>

                        <section v-for="(s, i) in legalSections" :key="i" class="space-y-2">
                            <h4 class="text-sm font-semibold text-white">{{ s.h }}</h4>
                            <p v-for="(para, j) in (s.p || [])" :key="'p' + j"
                                class="text-[13px] leading-relaxed text-slate-400">{{ para }}</p>
                            <ul v-if="s.li" class="list-disc pl-5 space-y-1.5 text-[13px] leading-relaxed text-slate-400 marker:text-blue-400">
                                <li v-for="(item, k) in s.li" :key="'li' + k">{{ item }}</li>
                            </ul>
                            <p v-if="s.note" class="text-[13px] leading-relaxed font-medium text-slate-300">{{ s.note }}</p>
                        </section>
                    </div>

                    <!-- Footer -->
                    <div class="p-5 sm:p-6 border-t border-white/10 flex justify-end shrink-0">
                        <button type="button" @click="closeLegal"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 rounded-lg transition-colors">
                            I Understand
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <Toaster theme="dark" position="bottom-right" />
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Toaster } from '@/components/ui/sonner'
import { toast } from 'vue-sonner'
import api from '@/utils/axios' // ✅ Import the axios instance

const router = useRouter()

// ===== WIZARD STATE =====
const currentStep = ref(1)
const steps = [
    { id: 1, title: 'Select Role', description: 'Choose your role', tips: 'Verification required later', nextButton: 'Next' },
    { id: 2, title: 'Personal', description: 'Basic details', tips: 'Valid info required', nextButton: 'Next' },
    { id: 3, title: 'Security', description: 'Secure account', tips: 'Use strong password', nextButton: 'Finish' },
    { id: 4, title: 'Review', description: 'Confirm details', tips: 'Check before submit' }
]

// ===== FORM STATE =====
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

// ===== UI STATE =====
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const isLoading = ref(false)
const validationErrors = reactive({})

// ===== ROLES =====
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

// ===== PASSWORD STRENGTH =====
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

// ===== VALIDATION =====
const isStepValid = computed(() => {
    switch (currentStep.value) {
        case 1: return form.role !== ''
        case 2: return validateStep2(true)
        case 3: return validateStep3(true)
        case 4: return form.terms && validateStep2(true) && validateStep3(true)
        default: return false
    }
})

// ===== HELPERS =====
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

// ===== ACTIONS =====
const selectRole = (roleValue) => { form.role = roleValue }

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
    if (!form.firstName.trim()) { errors.firstName = 'Required';
        isValid = false }
    if (!form.lastName.trim()) { errors.lastName = 'Required';
        isValid = false }
    if (!form.email) { errors.email = 'Required';
        isValid = false } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) { errors.email = 'Invalid email';
        isValid = false }
    if (!form.phone.trim()) { errors.phone = 'Required';
        isValid = false } else {
        const num = form.phone.replace(/\D/g, '')
        if (num.length < 11 || !num.startsWith('09')) { errors.phone = 'Invalid PH number';
            isValid = false }
    }
    if (!silent) Object.assign(validationErrors, errors)
    return isValid
}

const validateStep3 = (silent = false) => {
    const errors = {}
    let isValid = true
    if (!form.password) { errors.password = 'Required';
        isValid = false } else if (passwordStrengthScore.value < 100 && form.password.length < 8) { errors.password = 'Weak password';
        isValid = false }
    if (!form.confirmPassword) { errors.confirmPassword = 'Required';
        isValid = false } else if (form.password !== form.confirmPassword) { errors.confirmPassword = 'Mismatch';
        isValid = false }
    if (!silent) Object.assign(validationErrors, errors)
    return isValid
}

const nextStep = () => { if (currentStep.value < steps.length && isStepValid.value) currentStep.value++ }
const prevStep = () => { if (currentStep.value > 1) currentStep.value-- }
const goToStep = (step) => { if (step >= 1 && step <= steps.length && step < currentStep.value + 1) currentStep.value = step }

// ===== SIGNUP (UPDATED TO USE AXIOS) =====
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

        // ✅ Use the axios instance instead of direct fetch
        const response = await api.post('/auth/register', userData)

        // ✅ Axios automatically parses JSON, so response.data is the parsed object
        const data = response.data

        // ✅ If the request succeeds (status 2xx), we consider it successful
        // But we also check if the backend returns a 'success' flag or similar
        if (data.status === 'success' || data.message) {
            toast.success('Success!', { description: data.message || 'Account created. Please verify your email.' })
            setTimeout(() => router.push('/Landing/logIn'), 3000)
        } else {
            // If the backend returns a non-success status but still 2xx, handle it
            throw new Error(data.message || 'Registration failed')
        }
    } catch (error) {
        // Axios errors have a response property with server response
        if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            const serverMessage = error.response.data?.message || 'Registration failed'
            toast.error('Registration Failed', { description: serverMessage })
        } else if (error.request) {
            // The request was made but no response was received
            toast.error('Network Error', { description: 'No response from server. Please check your connection.' })
        } else {
            // Something happened in setting up the request that triggered an Error
            toast.error('Error', { description: error.message })
        }
    } finally {
        isLoading.value = false
    }
}

// ===== LEGAL MODALS (Terms & Privacy) =====
const legalModal = ref(null) // 'terms' | 'privacy' | null

const legalContent = {
    terms: {
        title: 'Terms & Conditions',
        intro: 'These Terms govern your account and your use of the CaviteGo Paint system — including the paint catalog, e-commerce store, order management, and role-based dashboards.',
        sections: [
            {
                h: '1. Acceptance of These Terms',
                p: [
                    'By creating a CaviteGo Paint account and ticking the agreement box during registration, you accept these Terms & Conditions and our Privacy Policy in full. If you do not agree with any part of them, do not create an account.'
                ]
            },
            {
                h: '2. Eligibility and Account Registration',
                p: ['To register you must:'],
                li: [
                    'be at least 18 years old, or be supervised by a legal guardian;',
                    'provide accurate and complete registration details — legal first name, last name, an active email address, and an active Philippine mobile number in 09XX XXX XXXX format;',
                    'keep your details up to date from your profile settings.'
                ],
                note: 'You are responsible for keeping your email access and password secure. One account is allowed per person and per email address.'
            },
            {
                h: '3. Account Roles and Verification',
                p: [
                    'CaviteGo Paint is a role-based system. The roles available at registration are Client, Distributor, Service Provider, and Supplier, while staff and administrator roles are assigned internally by CaviteGo Paint.',
                    'The role you select determines which dashboard, features, and records you can access. Your selected role is subject to verification and approval by an administrator before full access is granted.'
                ],
                li: [
                    'Distributors and Suppliers may be asked to submit business documents (e.g., DTI/SEC registration or permits) to verify their role;',
                    'An administrator may approve, reject, or reassign a role at any time;',
                    'Registering under a role you do not legitimately belong to is grounds for suspension.'
                ]
            },
            {
                h: '4. Account Security and Two-Step Verification',
                p: [
                    'Your account is protected by your password plus two-step verification. When you sign in from an unrecognized device or browser, the system sends a 6-digit one-time password (OTP) to your primary email, or to your recovery email if you select it, and may also ask for the answer to your security question.',
                    'You are responsible for all activity performed under your account. Never share your password or OTP codes. Report any suspected unauthorized access to CaviteGo Paint support immediately.'
                ]
            },
            {
                h: '5. Orders, Pricing, and Services',
                p: [
                    'Product prices, stock availability, and delivery schedules shown in the store may change without notice. An order you submit is a request and is only confirmed once its status in the system changes to confirmed.',
                    'Quotations, consultations, and service bookings made through the platform are requests as well, and are only binding once confirmed by the other party.'
                ],
                li: [
                    'Providing fake order details, payment references, or delivery addresses may lead to account suspension;',
                    'Distributor pricing and bulk quantities apply only to verified distributor accounts.'
                ]
            },
            {
                h: '6. Acceptable Use',
                p: ['You agree not to misuse the system.'], li: [
                    'attempt to breach or bypass security features, including guessing OTP codes or using stolen credentials;',
                    'scrape, harvest, or copy other users\u2019 data, product listings, or images;',
                    'impersonate another person or misrepresent your role or business affiliation;',
                    'use the system for any fraudulent, unlawful, or harassing activity;',
                    'upload malicious content or interfere with the system\u2019s operation.'
                ]
            },
            {
                h: '7. Suspension and Termination',
                p: [
                    'CaviteGo Paint may suspend or close an account that violates these Terms, fails role verification after repeated attempts, is involved in fraud, or poses a security risk. You may request account deletion at any time through the support channel.'
                ]
            },
            {
                h: '8. Disclaimer and Limitation of Liability',
                p: [
                    'The system is provided on an "as is" and "as available" basis. To the fullest extent permitted by law, CaviteGo Paint is not liable for indirect, incidental, or consequential damages arising from your use of the platform. Catalog descriptions and product information are maintained in good faith, but errors may occur.'
                ]
            },
            {
                h: '9. Changes to These Terms',
                p: [
                    'Updated Terms will be posted inside the system with a new effective date. Continued use of your account after that date means you accept the changes. Material changes will also be announced through the system or by email.'
                ]
            },
            {
                h: '10. Contact Us',
                p: [
                    'Questions about these Terms may be sent through the Support/Contact page inside the system, or by emailing support@cavitegopaint.com.'
                ]
            }
        ]
    },
    privacy: {
        title: 'Privacy Policy',
        intro: 'This Policy explains what personal information the CaviteGo Paint system collects when you register, how it is used, who can see it, and how you can control it. We handle personal data in accordance with the Data Privacy Act of 2012 (RA 10173) of the Philippines.',
        sections: [
            {
                h: '1. Information We Collect',
                p: ['When you register and use the system, we collect:'],
                li: [
                    'your first name, last name, email address, mobile number, and the role you selected;',
                    'your password, which is stored only as a salted one-way hash, plus your security question and answer;',
                    'technical details such as IP address, browser, device type, and login timestamps — used for two-step verification and fraud protection;',
                    'records of your activity: orders, quotations, service requests, supply orders, and other transactions you make.'
                ]
            },
            {
                h: '2. How We Use Your Information',
                p: ['We use your data to:'],
                li: [
                    'create and manage your account and apply the correct role-based permissions;',
                    'verify your identity through OTP codes, security questions, and role verification;',
                    'process your orders and transactions and power your dashboards and reports;',
                    'send transactional messages such as verification codes, order updates, and account notices;',
                    'detect and prevent fraud, abuse, and unauthorized access, and to keep the system running reliably.'
                ]
            },
            {
                h: '3. How We Store and Protect It',
                p: [
                    'Passwords are never saved in plain text. After you sign in, the system keeps an authentication token (auth_token) and your basic account data (user_data) in your browser\u2019s local storage so that you stay signed in; logging out or clearing your browser data removes them.',
                    'Data is transmitted over encrypted HTTPS connections, and access to records is limited by role, so staff only see the information they need to perform their function.'
                ]
            },
            {
                h: '4. Cookies and Local Storage',
                p: [
                    'The system uses cookies and local storage only to keep you signed in and to remember essential preferences. We do not use third-party advertising or cross-site tracking cookies.'
                ]
            },
            {
                h: '5. Who Can See Your Information',
                p: ['Your information is shared only with:'],
                li: [
                    'authorized CaviteGo Paint personnel, based on their role — for example, operations and distributor staff who process your orders, finance staff for payments, and administrators who manage accounts;',
                    'service providers that host the system and deliver verification emails, under confidentiality obligations;',
                    'authorities, when required by law or a lawful order.'
                ],
                note: 'We never sell your personal information.'
            },
            {
                h: '6. Data Retention and Deletion',
                p: [
                    'Your personal data is kept while your account is active and for as long as required by law and accounting rules. Transaction records may be retained even after account closure where the law requires it. You may request deletion of your account and personal data through the support channel.'
                ]
            },
            {
                h: '7. Your Rights',
                p: ['You may at any time:'], li: [
                    'view and correct your personal details in your profile settings;',
                    'request a copy of the data we hold about you;',
                    'request deletion of your account and associated personal data;',
                    'withdraw your consent by closing your account, with the understanding that essential transaction records may be retained as required by law.'
                ]
            },
            {
                h: '8. Protection of Minors',
                p: [
                    'The system is intended for users who are 18 years of age and older. If we learn that a minor has registered an account, we will delete the account and its data.'
                ]
            },
            {
                h: '9. Changes to This Policy',
                p: [
                    'We may update this Privacy Policy from time to time. The revised version will be posted inside the system with an updated effective date, and continued use of your account means you accept it.'
                ]
            },
            {
                h: '10. Contact Us',
                p: [
                    'For questions or requests about your personal data, contact us through the Support/Contact page inside the system, or by emailing support@cavitegopaint.com.'
                ]
            }
        ]
    }
}

const legalTitle = computed(() => legalContent[legalModal.value]?.title || '')
const legalIntro = computed(() => legalContent[legalModal.value]?.intro || '')
const legalSections = computed(() => legalContent[legalModal.value]?.sections || [])

const showTerms = () => { legalModal.value = 'terms' }
const showPrivacy = () => { legalModal.value = 'privacy' }
const closeLegal = () => { legalModal.value = null }

const onLegalKeydown = (e) => { if (e.key === 'Escape') closeLegal() }

watch(legalModal, (value) => {
    if (value) {
        document.body.style.overflow = 'hidden'
        window.addEventListener('keydown', onLegalKeydown)
    } else {
        document.body.style.overflow = ''
        window.removeEventListener('keydown', onLegalKeydown)
    }
})

onBeforeUnmount(() => {
    document.body.style.overflow = ''
    window.removeEventListener('keydown', onLegalKeydown)
})

watch(currentStep, () => {
    for (const key in validationErrors) delete validationErrors[key]
})
</script>

<style scoped>
/* One-shot entrance animation (no infinite loops, no filters) */
@keyframes fade-up {
    from { opacity: 0; transform: translateY(12px); }
    to { opacity: 1; transform: translateY(0); }
}
.auth-card { animation: fade-up 0.4s ease-out both; }

/* ===== LEGAL MODAL SCROLLBAR ===== */
.legal-body::-webkit-scrollbar { width: 6px; }
.legal-body::-webkit-scrollbar-track { background: transparent; }
.legal-body::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
.legal-body::-webkit-scrollbar-thumb:hover { background: #475569; }

/* ===== STEP TRANSITION (lightweight fade) ===== */
.step-fade-enter-active,
.step-fade-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}
.step-fade-enter-from {
    opacity: 0;
    transform: translateY(6px);
}
.step-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

@media (prefers-reduced-motion: reduce) {
    .auth-card,
    .step-fade-enter-active,
    .step-fade-leave-active {
        animation: none;
        transition: none;
    }
}
</style>
