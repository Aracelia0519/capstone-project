<script setup lang="ts">
import { ref, computed } from 'vue'
import { toast } from 'vue-sonner'
import axios from '@/utils/axios' // Assuming this is your axios instance location
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { 
  User, Briefcase, FileText, KeyRound, Check, Eye, EyeOff, UploadCloud, ShieldCheck, Mail, Phone 
} from 'lucide-vue-next'

// --- State ---
const currentStep = ref(1)
const isSubmitting = ref(false)

const formData = ref({
  // Step 1: Personal
  firstName: '',
  lastName: '',
  email: '',
  phone: '',
  // Step 2: Employment
  employmentType: '',
  hireDate: '',
  position: 'Personnel Officer', // Fixed position
  // Step 3: Documents
  validIdType: '',
  idNumber: '',
  validIdPhoto: null as File | null,
  resume: null as File | null,
  contract: null as File | null,
  // Step 4: Account
  password: '',
  confirmPassword: ''
})

// --- UI Toggles ---
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// --- Validation Logic ---
const phoneError = computed(() => {
  const p = formData.value.phone
  if (p.length > 0 && p.length !== 11) return 'Phone number must be exactly 11 digits'
  if (p.length > 0 && !/^\d+$/.test(p)) return 'Phone number must contain only digits'
  if (p.length > 0 && !/^0/.test(p)) return 'Phone number must start with 0'
  return ''
})

const passwordRequirements = computed(() => {
  const p = formData.value.password
  return [
    { text: 'At least 8 characters', met: p.length >= 8 },
    { text: 'Contains uppercase letter', met: /[A-Z]/.test(p) },
    { text: 'Contains lowercase letter', met: /[a-z]/.test(p) },
    { text: 'Contains number', met: /\d/.test(p) }
  ]
})

const passwordMatch = computed(() => {
  return formData.value.password !== '' && formData.value.password === formData.value.confirmPassword
})

const isStepValid = computed(() => {
  switch (currentStep.value) {
    case 1:
      return formData.value.firstName && formData.value.lastName && formData.value.email && 
             formData.value.phone.length === 11 && !phoneError.value
    case 2:
      // Salary removed from validation
      return formData.value.employmentType && formData.value.hireDate
    case 3:
      return formData.value.validIdType && formData.value.idNumber && formData.value.validIdPhoto
    case 4:
      return formData.value.password && passwordMatch.value && passwordRequirements.value.every(r => r.met)
    default:
      return false
  }
})

// --- Navigation & Actions ---
const nextStep = () => {
  if (currentStep.value < 4 && isStepValid.value) currentStep.value++
}

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--
}

const handleFileUpload = (event: Event, field: 'validIdPhoto' | 'resume' | 'contract') => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    formData.value[field] = target.files[0]
  }
}

const onSubmit = async () => {
  if (!isStepValid.value || isSubmitting.value) return
  
  isSubmitting.value = true

  try {
    const payload = new FormData()
    
    // Append Text Data
    payload.append('first_name', formData.value.firstName)
    payload.append('last_name', formData.value.lastName)
    payload.append('email', formData.value.email)
    payload.append('phone', formData.value.phone)
    payload.append('employment_type', formData.value.employmentType)
    payload.append('hire_date', formData.value.hireDate)
    payload.append('position', formData.value.position)
    payload.append('valid_id_type', formData.value.validIdType)
    payload.append('id_number', formData.value.idNumber)
    payload.append('password', formData.value.password)
    payload.append('password_confirmation', formData.value.confirmPassword)

    // Append File Data
    if (formData.value.validIdPhoto) {
      payload.append('valid_id_photo', formData.value.validIdPhoto)
    }
    if (formData.value.resume) {
      payload.append('resume', formData.value.resume)
    }
    if (formData.value.contract) {
      payload.append('employment_contract', formData.value.contract)
    }

    const response = await axios.post('/supplier/personnel-officers', payload, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    if (response.data.status === 'success') {
      toast.success('Personnel Officer created successfully!', {
        description: `Account for ${formData.value.firstName} ${formData.value.lastName} has been registered.`
      })

      // Reset Form
      formData.value = {
        firstName: '', lastName: '', email: '', phone: '',
        employmentType: '', hireDate: '', position: 'Personnel Officer',
        validIdType: '', idNumber: '', validIdPhoto: null, resume: null, contract: null,
        password: '', confirmPassword: ''
      }
      currentStep.value = 1
    }
  } catch (error: any) {
    console.error('Error creating Personnel Officer:', error)
    let errorMessage = error.response?.data?.message || 'Failed to create Personnel Officer.'
    if (error.response?.data?.errors) {
      const errors = Object.values(error.response.data.errors).flat()
      errorMessage = errors.join(', ')
    }
    toast.error('Error', { description: errorMessage })
  } finally {
    isSubmitting.value = false
  }
}

// --- Options ---
const employmentTypes = [
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'temporary', label: 'Temporary' }
]

const idTypes = [
  { value: 'passport', label: 'Passport' },
  { value: 'driver_license', label: "Driver's License" },
  { value: 'umid', label: 'UMID' },
  { value: 'prc', label: 'PRC ID' },
  { value: 'postal', label: 'Postal ID' },
  { value: 'voter', label: "Voter's ID" },
  { value: 'other', label: "Other ID" }
]
</script>

<template>
  <div class="max-w-4xl mx-auto p-4 md:p-6 space-y-6">
    
    <div class="flex items-center space-x-4 mb-6">
      <div class="p-3 bg-primary/10 rounded-full">
        <ShieldCheck class="w-6 h-6 text-primary" />
      </div>
      <div>
        <h2 class="text-2xl font-bold tracking-tight">Create Personnel Officer</h2>
        <p class="text-muted-foreground">Follow the steps to register and provision a new officer account.</p>
      </div>
    </div>

    <Card class="border-muted/60 shadow-sm overflow-hidden">
      <div class="bg-muted/30 px-6 py-4 border-b">
        <div class="flex items-center justify-between relative">
          <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-muted rounded-full overflow-hidden z-0">
            <div 
              class="h-full bg-primary transition-all duration-500 ease-in-out"
              :style="{ width: `${((currentStep - 1) / 3) * 100}%` }"
            ></div>
          </div>
          
          <div v-for="step in 4" :key="step" class="relative z-10 flex flex-col items-center gap-2 bg-background p-1 rounded-full">
            <div 
              class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-colors duration-300 shadow-sm"
              :class="[
                step < currentStep ? 'bg-primary border-primary text-primary-foreground' : 
                step === currentStep ? 'bg-background border-primary text-primary' : 
                'bg-background border-muted text-muted-foreground'
              ]"
            >
              <Check v-if="step < currentStep" class="w-5 h-5" />
              <User v-else-if="step === 1" class="w-5 h-5" />
              <Briefcase v-else-if="step === 2" class="w-5 h-5" />
              <FileText v-else-if="step === 3" class="w-5 h-5" />
              <KeyRound v-else class="w-5 h-5" />
            </div>
            <span class="text-xs font-medium hidden sm:block" 
              :class="step <= currentStep ? 'text-foreground' : 'text-muted-foreground'">
              {{ ['Personal', 'Employment', 'Documents', 'Account'][step - 1] }}
            </span>
          </div>
        </div>
      </div>

      <CardContent class="p-6 md:p-8">
        <form @submit.prevent="onSubmit" class="min-h-[350px]">
          
          <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold">Personal Information</h3>
              <p class="text-sm text-muted-foreground">Enter the officer's basic contact details.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="firstName">First Name <span class="text-destructive">*</span></Label>
                <Input id="firstName" v-model="formData.firstName" placeholder="e.g. Julian" />
              </div>
              
              <div class="space-y-2">
                <Label for="lastName">Last Name <span class="text-destructive">*</span></Label>
                <Input id="lastName" v-model="formData.lastName" placeholder="e.g. Namoc" />
              </div>
              
              <div class="space-y-2">
                <Label for="email">Email Address <span class="text-destructive">*</span></Label>
                <div class="relative">
                  <Mail class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input id="email" type="email" v-model="formData.email" class="pl-9" placeholder="juan@company.com" />
                </div>
              </div>
              
              <div class="space-y-2">
                <Label for="phone">Phone Number <span class="text-destructive">*</span></Label>
                <div class="relative">
                  <Phone class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input id="phone" v-model="formData.phone" class="pl-9" placeholder="09XXXXXXXXX" maxlength="11" />
                </div>
                <p v-if="phoneError" class="text-xs text-destructive">{{ phoneError }}</p>
                <p v-else class="text-xs text-muted-foreground">11 digits starting with 0</p>
              </div>
            </div>
          </div>

          <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold">Employment Information</h3>
              <p class="text-sm text-muted-foreground">Set employment details.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="employmentType">Employment Type <span class="text-destructive">*</span></Label>
                <select id="employmentType" v-model="formData.employmentType" 
                  class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                  <option value="" disabled>Select Type...</option>
                  <option v-for="type in employmentTypes" :key="type.value" :value="type.value">
                    {{ type.label }}
                  </option>
                </select>
              </div>
              
              <div class="space-y-2">
                <Label for="hireDate">Hire Date <span class="text-destructive">*</span></Label>
                <Input id="hireDate" type="date" v-model="formData.hireDate" />
              </div>
              
              <div class="space-y-2">
                <Label for="position">System Position</Label>
                <Input id="position" v-model="formData.position" disabled class="bg-muted/50" />
              </div>
            </div>
          </div>

          <div v-show="currentStep === 3" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold">Documents & Verification</h3>
              <p class="text-sm text-muted-foreground">Upload required identification and contracts.</p>
            </div>
            
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label>Type of Valid ID <span class="text-destructive">*</span></Label>
                  <select v-model="formData.validIdType" 
                    class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                    <option value="" disabled>Select ID Type...</option>
                    <option v-for="type in idTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                  </select>
                </div>
                
                <div class="space-y-2">
                  <Label>ID Number <span class="text-destructive">*</span></Label>
                  <Input v-model="formData.idNumber" placeholder="Enter ID number" />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4">
                <div class="space-y-2 col-span-1 md:col-span-3 lg:col-span-1">
                  <Label>Valid ID Photo <span class="text-destructive">*</span></Label>
                  <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.validIdPhoto ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <UploadCloud class="w-8 h-8 mb-2" :class="formData.validIdPhoto ? 'text-primary' : 'text-muted-foreground'" />
                      <p class="text-sm text-muted-foreground truncate max-w-[150px]">
                        {{ formData.validIdPhoto ? formData.validIdPhoto.name : 'Click to upload ID' }}
                      </p>
                    </div>
                    <input type="file" class="hidden" accept="image/*,.pdf" @change="e => handleFileUpload(e, 'validIdPhoto')" />
                  </label>
                </div>
                
                <div class="space-y-2">
                  <Label>Resume <span class="text-muted-foreground font-normal">(Optional)</span></Label>
                  <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.resume ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <FileText class="w-8 h-8 mb-2" :class="formData.resume ? 'text-primary' : 'text-muted-foreground'" />
                      <p class="text-sm text-muted-foreground truncate max-w-[150px]">
                        {{ formData.resume ? formData.resume.name : 'Upload Resume' }}
                      </p>
                    </div>
                    <input type="file" class="hidden" accept=".pdf,.doc,.docx" @change="e => handleFileUpload(e, 'resume')" />
                  </label>
                </div>
                
                <div class="space-y-2">
                  <Label>Contract <span class="text-muted-foreground font-normal">(Optional)</span></Label>
                  <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.contract ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <FileText class="w-8 h-8 mb-2" :class="formData.contract ? 'text-primary' : 'text-muted-foreground'" />
                      <p class="text-sm text-muted-foreground truncate max-w-[150px]">
                        {{ formData.contract ? formData.contract.name : 'Upload Contract' }}
                      </p>
                    </div>
                    <input type="file" class="hidden" accept=".pdf" @change="e => handleFileUpload(e, 'contract')" />
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div v-show="currentStep === 4" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold">Account Setup</h3>
              <p class="text-sm text-muted-foreground">Create secure login credentials for the officer.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-4">
                <div class="space-y-2">
                  <Label for="password">Password <span class="text-destructive">*</span></Label>
                  <div class="relative">
                    <KeyRound class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input id="password" :type="showPassword ? 'text' : 'password'" v-model="formData.password" class="pl-9 pr-10" placeholder="Create password" />
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground">
                      <Eye v-if="!showPassword" class="h-4 w-4" />
                      <EyeOff v-else class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="confirmPassword">Confirm Password <span class="text-destructive">*</span></Label>
                  <div class="relative">
                    <KeyRound class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input id="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'" v-model="formData.confirmPassword" class="pl-9 pr-10" placeholder="Confirm password" />
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground">
                      <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                      <EyeOff v-else class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div v-if="formData.password && formData.confirmPassword" class="text-sm flex items-center gap-2 p-3 rounded-md" :class="passwordMatch ? 'bg-emerald-500/10 text-emerald-600' : 'bg-destructive/10 text-destructive'">
                  <Check v-if="passwordMatch" class="w-4 h-4" />
                  <span>{{ passwordMatch ? 'Passwords match perfectly!' : 'Passwords do not match' }}</span>
                </div>
              </div>
              
              <div class="p-4 bg-muted/30 rounded-lg border border-border/50">
                <h5 class="text-sm font-medium mb-3">Password Requirements</h5>
                <ul class="space-y-2">
                  <li v-for="req in passwordRequirements" :key="req.text" class="flex items-center text-sm">
                    <Check class="w-4 h-4 mr-2" :class="req.met ? 'text-emerald-500' : 'text-muted-foreground/30'" />
                    <span :class="req.met ? 'text-foreground' : 'text-muted-foreground'">{{ req.text }}</span>
                  </li>
                </ul>
              </div>
            </div>
          </div>

        </form>
      </CardContent>
      
      <CardFooter class="flex items-center justify-between p-6 bg-muted/10 border-t">
        <Button variant="outline" type="button" @click="currentStep === 1 ? $router.back() : prevStep()">
          {{ currentStep === 1 ? 'Cancel' : 'Back' }}
        </Button>
        
        <Button v-if="currentStep < 4" type="button" @click="nextStep" :disabled="!isStepValid">
          Continue
        </Button>
        
        <Button v-else type="button" @click="onSubmit" :disabled="!isStepValid || isSubmitting">
          {{ isSubmitting ? 'Creating Account...' : 'Create Personnel Officer' }}
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>