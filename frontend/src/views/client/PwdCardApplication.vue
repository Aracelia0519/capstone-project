<template>
  <div class="min-h-screen  p-4 md:p-8">
    
    <!-- Header -->
    <div class="max-w-[90rem] mx-auto flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
      <div>
        <Button variant="ghost" @click="$router.push('/Clients/ProfileC')" class="text-slate-400 hover:text-white mb-2 -ml-4">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          Back to Profile
        </Button>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
          </svg>
          PWD Discount Application
        </h1>
        <p class="text-slate-400 mt-1">Submit your PWD ID details to avail of special discounts</p>
      </div>
      <Badge :class="getStatusBadgeClass(form.status)" class="px-4 py-2 text-sm uppercase tracking-wider">
        {{ formatStatus(form.status) }}
      </Badge>
    </div>

    <!-- Wizard Card -->
    <Card class="max-w-[90rem] mx-auto bg-slate-800/40 border-slate-700/30 backdrop-blur-sm shadow-xl">
      <CardHeader class="border-b border-slate-700/30 pb-8 pt-8">
        <div class="relative max-w-4xl mx-auto">
          <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-700/50 -translate-y-1/2 rounded-full"></div>
          <div class="absolute top-1/2 left-0 h-1 bg-indigo-500 -translate-y-1/2 transition-all duration-300 rounded-full" :style="{ width: wizardProgress + '%' }"></div>
          
          <div class="flex justify-between relative z-10">
            <div v-for="(step, index) in steps" :key="index" class="flex flex-col items-center gap-2" @click="goToStep(index)">
              <div 
                class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 cursor-pointer bg-slate-900"
                :class="[
                  currentStep === index ? 'border-indigo-500 text-indigo-400 shadow-[0_0_15px_rgba(99,102,241,0.4)] scale-110' : 
                  step.completed ? 'border-emerald-500 text-emerald-500 bg-emerald-500/10' : 
                  'border-slate-600 text-slate-500 hover:border-slate-400'
                ]"
              >
                <svg v-if="step.completed" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                <span v-else class="font-bold">{{ index + 1 }}</span>
              </div>
              <span class="text-xs font-medium hidden sm:block transition-colors duration-300" 
                    :class="currentStep === index ? 'text-indigo-400' : step.completed ? 'text-emerald-500' : 'text-slate-500'">
                {{ step.title }}
              </span>
            </div>
          </div>
        </div>
      </CardHeader>

      <CardContent class="p-6 md:p-10 min-h-[400px]">
        
        <!-- Step 1: Personal Information -->
        <div v-show="currentStep === 0" class="animate-in fade-in slide-in-from-right-4 duration-300 max-w-3xl mx-auto">
          <h3 class="text-xl font-semibold text-white mb-6 text-center">Personal Information</h3>
          <div class="space-y-6">
            <div class="space-y-2">
              <Label class="text-slate-300">Full Name (As it appears on ID) <span class="text-red-500">*</span></Label>
              <Input v-model="form.fullName" placeholder="e.g., Julian Namoc" class="bg-slate-900/50 border-slate-700 text-white focus-visible:ring-indigo-500" :disabled="isReadonly" />
            </div>
            <div class="space-y-2">
              <Label class="text-slate-300">Date of Birth <span class="text-red-500">*</span></Label>
              <Input v-model="form.dob" type="date" class="bg-slate-900/50 border-slate-700 text-white focus-visible:ring-indigo-500" :disabled="isReadonly" />
            </div>
          </div>
        </div>

        <!-- Step 2: ID Details -->
        <div v-show="currentStep === 1" class="animate-in fade-in slide-in-from-right-4 duration-300 max-w-3xl mx-auto">
          <h3 class="text-xl font-semibold text-white mb-6 text-center">PWD ID Details</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label class="text-slate-300">PWD ID Number <span class="text-red-500">*</span></Label>
              <Input v-model="form.idNumber" placeholder="XXXX-XXXX-XXXX" class="bg-slate-900/50 border-slate-700 text-white font-mono focus-visible:ring-indigo-500" :disabled="isReadonly" />
            </div>
            
            <div class="space-y-2">
              <Label class="text-slate-300">Disability Type <span class="text-red-500">*</span></Label>
              <Select v-model="form.disabilityType" :disabled="isReadonly">
                <SelectTrigger class="w-full bg-slate-900/50 border-slate-700 text-slate-100 focus:ring-indigo-500">
                  <SelectValue placeholder="Select disability type..." />
                </SelectTrigger>
                <SelectContent class="bg-slate-800 border-slate-700 text-white max-h-56">
                  <SelectItem v-for="type in disabilityTypes" :key="type" :value="type">{{ type }}</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="space-y-2">
              <Label class="text-slate-300">Issuing LGU / PDAO <span class="text-red-500">*</span></Label>
              <Input v-model="form.issuingLGU" placeholder="e.g., City of Bacoor" class="bg-slate-900/50 border-slate-700 text-white focus-visible:ring-indigo-500" :disabled="isReadonly" />
            </div>
            <div class="space-y-2">
              <Label class="text-slate-300">Expiration Date <span class="text-red-500">*</span></Label>
              <Input v-model="form.expirationDate" type="date" class="bg-slate-900/50 border-slate-700 text-white focus-visible:ring-indigo-500" :disabled="isReadonly" />
            </div>
          </div>
        </div>

        <!-- Step 3: Photo Upload -->
        <div v-show="currentStep === 2" class="animate-in fade-in slide-in-from-right-4 duration-300 max-w-4xl mx-auto">
          <h3 class="text-xl font-semibold text-white mb-6 text-center">Upload ID Photos</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <!-- Front Photo -->
            <div class="space-y-3">
              <Label class="text-slate-300 flex items-center justify-between">
                <span>Front of ID <span class="text-red-500">*</span></span>
                <Button v-if="form.photoFrontPreview && !isReadonly" variant="ghost" size="sm" @click="clearPhoto('front')" class="h-6 text-red-400 hover:text-red-300 hover:bg-red-400/10 px-2 text-xs">Remove</Button>
              </Label>
              <div 
                class="border-2 border-dashed rounded-xl h-64 flex items-center justify-center transition-all relative overflow-hidden group"
                :class="form.photoFrontPreview ? 'border-solid border-slate-600 bg-slate-900/50' : 'border-slate-700 bg-slate-800/30 hover:border-indigo-500/50 cursor-pointer'"
                @click="!isReadonly && !form.photoFrontPreview && $refs.frontInput.click()"
              >
                <input type="file" ref="frontInput" @change="(e) => handlePhotoUpload(e, 'front')" accept="image/*" class="hidden" :disabled="isReadonly">
                <img v-if="form.photoFrontPreview" :src="form.photoFrontPreview" class="object-contain w-full h-full p-2" />
                <div v-else class="text-center p-4">
                  <svg class="w-10 h-10 text-slate-500 mx-auto mb-2 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  <p class="text-sm text-slate-400 font-medium">Click to upload Front ID</p>
                  <p class="text-xs text-slate-500 mt-1">JPG, PNG (Max 5MB)</p>
                </div>
              </div>
            </div>

            <!-- Back Photo -->
            <div class="space-y-3">
              <Label class="text-slate-300 flex items-center justify-between">
                <span>Back of ID <span class="text-red-500">*</span></span>
                <Button v-if="form.photoBackPreview && !isReadonly" variant="ghost" size="sm" @click="clearPhoto('back')" class="h-6 text-red-400 hover:text-red-300 hover:bg-red-400/10 px-2 text-xs">Remove</Button>
              </Label>
              <div 
                class="border-2 border-dashed rounded-xl h-64 flex items-center justify-center transition-all relative overflow-hidden group"
                :class="form.photoBackPreview ? 'border-solid border-slate-600 bg-slate-900/50' : 'border-slate-700 bg-slate-800/30 hover:border-indigo-500/50 cursor-pointer'"
                @click="!isReadonly && !form.photoBackPreview && $refs.backInput.click()"
              >
                <input type="file" ref="backInput" @change="(e) => handlePhotoUpload(e, 'back')" accept="image/*" class="hidden" :disabled="isReadonly">
                <img v-if="form.photoBackPreview" :src="form.photoBackPreview" class="object-contain w-full h-full p-2" />
                <div v-else class="text-center p-4">
                  <svg class="w-10 h-10 text-slate-500 mx-auto mb-2 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                  <p class="text-sm text-slate-400 font-medium">Click to upload Back ID</p>
                  <p class="text-xs text-slate-500 mt-1">JPG, PNG (Max 5MB)</p>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- Step 4: Review & Status -->
        <div v-show="currentStep === 3" class="animate-in fade-in slide-in-from-right-4 duration-300 max-w-3xl mx-auto">
          
          <div v-if="form.status === 'unsubmitted' || form.status === 'rejected'" class="space-y-6">
            
            <div v-if="form.status === 'rejected'" class="bg-red-500/10 border border-red-500/30 p-5 rounded-xl shadow-sm">
              <h4 class="text-red-400 font-bold mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Application Rejected
              </h4>
              <p class="text-red-200 ml-7 text-sm leading-relaxed">{{ form.rejectionReason }}</p>
              <p class="text-slate-400 text-xs ml-7 mt-3">Please go back, correct the specified details above, and resubmit your application.</p>
            </div>

            <h3 class="text-xl font-semibold text-white mb-2 text-center">Review Your Details</h3>
            <p class="text-center text-slate-400 text-sm mb-6">Please verify your information before submitting.</p>
            
            <div class="bg-slate-900/50 border border-slate-700 rounded-xl overflow-hidden">
              <dl class="divide-y divide-slate-700/50">
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                  <dt class="text-sm font-medium text-slate-400">Full Name</dt>
                  <dd class="text-sm text-white col-span-2">{{ form.fullName }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                  <dt class="text-sm font-medium text-slate-400">ID Number</dt>
                  <dd class="text-sm font-mono text-indigo-400 col-span-2">{{ form.idNumber }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                  <dt class="text-sm font-medium text-slate-400">Disability Type</dt>
                  <dd class="text-sm text-white col-span-2">{{ form.disabilityType }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                  <dt class="text-sm font-medium text-slate-400">Expiration</dt>
                  <dd class="text-sm text-white col-span-2">{{ form.expirationDate }}</dd>
                </div>
                <div class="px-6 py-4 grid grid-cols-3 gap-4">
                  <dt class="text-sm font-medium text-slate-400">Photos</dt>
                  <dd class="text-sm text-emerald-400 col-span-2 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg> Front & Back Uploaded
                  </dd>
                </div>
              </dl>
            </div>

            <div class="bg-amber-500/10 border border-amber-500/20 p-4 rounded-xl text-amber-200 text-sm flex gap-3">
              <svg class="w-5 h-5 flex-shrink-0 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <p>By submitting, you certify that all uploaded documents are true and correct. Falsification of PWD IDs is punishable by law.</p>
            </div>
          </div>

          <div v-else class="text-center py-10 space-y-4">
            <div class="w-24 h-24 mx-auto rounded-full flex items-center justify-center mb-6"
                 :class="{
                   'bg-amber-500/10 text-amber-500 border border-amber-500/30': form.status === 'pending',
                   'bg-emerald-500/10 text-emerald-500 border border-emerald-500/30': form.status === 'verified'
                 }">
              <svg v-if="form.status === 'pending'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              <svg v-if="form.status === 'verified'" class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
            
            <h3 class="text-2xl font-bold text-white">
              {{ form.status === 'pending' ? 'Application Under Review' : 'PWD Discount Verified!' }}
            </h3>
            <p class="text-slate-400 max-w-md mx-auto">
              {{ form.status === 'pending' 
                ? 'Your PWD application has been submitted and is currently being reviewed by our administrators. This usually takes 1-2 business days.' 
                : 'Your PWD identification has been verified successfully. Your discount will automatically be applied to eligible transactions.' }}
            </p>
          </div>
        </div>

      </CardContent>

      <!-- Footer / Controls -->
      <div class="p-6 bg-slate-900/40 border-t border-slate-700/30 flex justify-between items-center rounded-b-xl">
        <Button 
          variant="outline" 
          @click="prevStep" 
          :disabled="currentStep === 0 || isReadonly"
          class="border-slate-600 text-slate-300 hover:bg-slate-800 hover:text-white bg-transparent"
        >
          Previous
        </Button>
        
        <div v-if="currentStep < 3" class="flex gap-1 text-sm">
          <span class="text-indigo-400 font-bold">{{ currentStep + 1 }}</span>
          <span class="text-slate-600">/</span>
          <span class="text-slate-500">4</span>
        </div>

        <Button 
          v-if="currentStep < 3" 
          @click="nextStep"
          :disabled="!canProceed || isReadonly"
          class="bg-indigo-600 hover:bg-indigo-700 text-white"
        >
          Next Step
        </Button>
        
        <Button 
          v-else-if="currentStep === 3 && (form.status === 'unsubmitted' || form.status === 'rejected')" 
          @click="submitApplication"
          :disabled="isSubmitting || !canProceed"
          class="bg-emerald-600 hover:bg-emerald-700 text-white min-w-[120px]"
        >
          <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ isSubmitting ? 'Submitting...' : (form.status === 'rejected' ? 'Resubmit Application' : 'Submit Application') }}
        </Button>
        
        <div v-else class="w-24"></div> 
      </div>
    </Card>

  </div>
</template>

<script>
import axios from '@/utils/axios'
import echo from '@/utils/websocket'
import { toast } from 'vue-sonner'

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

export default {
  name: 'PwdCardApplication',
  components: {
    Card, CardHeader, CardContent, CardTitle,
    Button, Input, Label, Badge,
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue
  },
  data() {
    return {
      currentStep: 0,
      isSubmitting: false,
      userId: null,
      disabilityTypes: [
        'Psychosocial Disability',
        'Chronic Illness',
        'Learning Disability',
        'Mental Disability',
        'Visual Disability',
        'Orthopedic Disability',
        'Communication Disability',
        'Hearing Disability',
        'Intellectual Disability',
        'Multiple Disabilities'
      ],
      steps: [
        { title: 'Personal Info', completed: false },
        { title: 'ID Details', completed: false },
        { title: 'Upload Photos', completed: false },
        { title: 'Review', completed: false }
      ],
      form: {
        fullName: '',
        dob: '',
        idNumber: '',
        disabilityType: '',
        issuingLGU: '',
        expirationDate: '',
        photoFront: null,
        photoFrontPreview: '',
        photoBack: null,
        photoBackPreview: '',
        status: 'unsubmitted',
        rejectionReason: ''
      }
    }
  },
  computed: {
    wizardProgress() {
      return (this.currentStep / (this.steps.length - 1)) * 100
    },
    isReadonly() {
      return this.form.status === 'pending' || this.form.status === 'verified'
    },
    canProceed() {
      if (this.isReadonly) return true; 
      
      if (this.currentStep === 0) {
        return this.form.fullName.trim() !== '' && this.form.dob !== ''
      }
      if (this.currentStep === 1) {
        return this.form.idNumber.trim() !== '' && 
               this.form.disabilityType !== '' && 
               this.form.issuingLGU.trim() !== '' && 
               this.form.expirationDate !== ''
      }
      if (this.currentStep === 2) {
        if (this.form.status === 'unsubmitted') {
          return this.form.photoFront !== null && this.form.photoBack !== null
        } else {
          return this.form.photoFrontPreview !== '' && this.form.photoBackPreview !== ''
        }
      }
      return true
    }
  },
  async mounted() {
    await this.fetchApplicationStatus()
  },
  beforeUnmount() {
    if (this.userId) {
      echo.leave(`pwd.application.${this.userId}`)
    }
  },
  methods: {
    async fetchApplicationStatus() {
      try {
        const response = await axios.get('/client/pwd-application')
        
        const userRes = await axios.get('/auth/me');
        this.userId = userRes.data.user.id;
        this.setupWebSocketListener();

        if (response.data.status && response.data.status !== 'unsubmitted') {
          this.form.status = response.data.status
          
          if (response.data.data) {
            this.form.fullName = response.data.data.full_name
            this.form.idNumber = response.data.data.id_number
            this.form.disabilityType = response.data.data.disability_type
            this.form.issuingLGU = response.data.data.issuing_lgu || ''
            this.form.dob = response.data.data.dob || ''
            this.form.expirationDate = response.data.data.expiration_date
            
            this.form.rejectionReason = response.data.data.rejection_reason || ''

            this.form.photoFrontPreview = response.data.data.photo_front_path ? 'http://localhost:8000/storage/' + response.data.data.photo_front_path : ''
            this.form.photoBackPreview = response.data.data.photo_back_path ? 'http://localhost:8000/storage/' + response.data.data.photo_back_path : ''
          }
          
          this.steps.forEach(step => step.completed = true)
          this.currentStep = 3
        }
      } catch (error) {
        console.error('Error fetching PWD application status', error)
      }
    },
    
    setupWebSocketListener() {
      if (!this.userId) return;
      
      echo.private(`pwd.application.${this.userId}`)
        .listen('.PwdApplicationStatusUpdated', (e) => {
          this.form.status = e.status;
          if (e.rejectionReason) {
            this.form.rejectionReason = e.rejectionReason;
          } else {
            this.form.rejectionReason = '';
          }
          toast.info(`Your PWD application status has been updated to: ${e.status}`);
        });
    },

    goToStep(index) {
      if (index <= this.currentStep || this.isReadonly) {
        this.currentStep = index
      } else if (this.canProceed && index === this.currentStep + 1) {
        this.nextStep()
      }
    },
    nextStep() {
      if (this.canProceed && this.currentStep < this.steps.length - 1) {
        this.steps[this.currentStep].completed = true
        this.currentStep++
      }
    },
    prevStep() {
      if (this.currentStep > 0) {
        this.currentStep--
      }
    },
    handlePhotoUpload(event, side) {
      const file = event.target.files[0]
      if (!file) return

      if (file.size > 5 * 1024 * 1024) {
        alert('File size exceeds 5MB limit.')
        return
      }

      const reader = new FileReader()
      reader.onload = (e) => {
        if (side === 'front') {
          this.form.photoFront = file
          this.form.photoFrontPreview = e.target.result
        } else {
          this.form.photoBack = file
          this.form.photoBackPreview = e.target.result
        }
      }
      reader.readAsDataURL(file)
    },
    clearPhoto(side) {
      if (side === 'front') {
        this.form.photoFront = null
        this.form.photoFrontPreview = ''
        if (this.$refs.frontInput) this.$refs.frontInput.value = ''
      } else {
        this.form.photoBack = null
        this.form.photoBackPreview = ''
        if (this.$refs.backInput) this.$refs.backInput.value = ''
      }
      this.steps[2].completed = false
    },
    async submitApplication() {
      if (!this.canProceed || this.isSubmitting) return
      this.isSubmitting = true
      
      try {
        const formData = new FormData()
        formData.append('full_name', this.form.fullName)
        formData.append('dob', this.form.dob)
        formData.append('id_number', this.form.idNumber)
        formData.append('disability_type', this.form.disabilityType)
        formData.append('issuing_lgu', this.form.issuingLGU)
        formData.append('expiration_date', this.form.expirationDate)
        
        if (this.form.photoFront) formData.append('photo_front', this.form.photoFront)
        if (this.form.photoBack) formData.append('photo_back', this.form.photoBack)

        const response = await axios.post('/client/pwd-application', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (response.data.status === 'success') {
          this.form.status = 'pending'
          this.form.rejectionReason = ''
          this.steps[3].completed = true
          toast.success('Application resubmitted successfully!');
        }
      } catch (error) {
        alert(error.response?.data?.message || 'An error occurred while submitting.')
      } finally {
        this.isSubmitting = false
      }
    },
    formatStatus(status) {
      if (status === 'unsubmitted') return 'Not Submitted'
      if (status === 'pending') return 'Pending Verification'
      if (status === 'verified') return 'Verified'
      if (status === 'rejected') return 'Rejected'
      return status
    },
    getStatusBadgeClass(status) {
      if (status === 'verified') return 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
      if (status === 'pending') return 'bg-amber-500/20 text-amber-400 border-amber-500/30'
      if (status === 'rejected') return 'bg-red-500/20 text-red-400 border-red-500/30'
      return 'bg-slate-700/50 text-slate-400 border-slate-600'
    }
  }
}
</script>

<style scoped>
.slide-in-from-right-4 {
  animation: slideInRight 0.3s ease-out;
}
@keyframes slideInRight {
  from { opacity: 0; transform: translateX(20px); }
  to { opacity: 1; transform: translateX(0); }
}
</style>