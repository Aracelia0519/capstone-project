<script setup lang="ts">
import { ref, computed } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios' // ADJUST THIS PATH TO POINT TO YOUR AXIOS.JS FILE
import {
  Card,
  CardContent,
  CardFooter,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { 
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { 
  User, Briefcase, FileText, KeyRound, Check, Eye, EyeOff, UploadCloud, Users, Mail, Phone, MapPin, Landmark, GraduationCap, FileCheck
} from 'lucide-vue-next'

// --- State ---
const currentStep = ref(1)
const isSubmitting = ref(false)
const showConfirmDialog = ref(false)

const today = computed(() => new Date().toISOString().split('T')[0])
const currentYear = new Date().getFullYear()

// A helper function to generate the initial blank state
const getInitialFormData = () => ({
  // Step 1: Personal Information
  firstName: '',
  middleName: '',
  lastName: '',
  email: '',
  phone: '',
  emergencyContact: '',
  dateOfBirth: '',
  gender: '',
  maritalStatus: '',
  nationality: 'Filipino',
  address: '',

  // Step 2: Role & Account
  personnelType: '', 
  customPersonnelType: '', 
  employmentType: '',
  hireDate: today.value,
  password: '',
  confirmPassword: '',

  // Step 3: Financial & Government
  bankName: '',
  bankAccountNumber: '',
  bankAccountName: '',
  sssNumber: '',
  philhealthNumber: '',
  pagibigNumber: '',
  tinNumber: '',

  // Step 4: Education & Identification
  educationalAttainment: '',
  schoolGraduated: '',
  yearGraduated: currentYear,
  course: '',
  validIdType: '',
  idNumber: '',
  validIdPhoto: null as File | null,

  // Step 5: Documents & Notes
  resume: null as File | null,
  employmentContract: null as File | null,
  medicalCertificate: null as File | null,
  nbiClearance: null as File | null,
  policeClearance: null as File | null,
  notes: ''
})

// Initialize formData with the blank state
const formData = ref(getInitialFormData())

// --- UI Toggles ---
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// --- Validation Logic ---
const phoneError = computed(() => {
  const p = formData.value.phone
  if (p.length > 0 && p.length !== 11) return 'Must be exactly 11 digits'
  if (p.length > 0 && !/^\d+$/.test(p)) return 'Digits only'
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
             formData.value.phone.length === 11 && !phoneError.value && 
             formData.value.dateOfBirth && formData.value.gender && 
             formData.value.maritalStatus && formData.value.address
    case 2:
      const isRoleValid = formData.value.personnelType === 'other' 
                          ? formData.value.customPersonnelType.trim() !== '' 
                          : formData.value.personnelType !== ''
      return isRoleValid && formData.value.employmentType && formData.value.hireDate && 
             formData.value.password && passwordMatch.value && passwordRequirements.value.every(r => r.met)
    case 3:
      // Assuming government numbers and bank details might be partially optional, but let's require at least one gov ID
      return true 
    case 4:
      return formData.value.educationalAttainment && formData.value.schoolGraduated && 
             formData.value.yearGraduated && formData.value.validIdType && 
             formData.value.idNumber && formData.value.validIdPhoto
    case 5:
      return formData.value.resume && formData.value.medicalCertificate && formData.value.nbiClearance
    default:
      return false
  }
})

// --- Navigation & Actions ---
const nextStep = () => {
  if (currentStep.value < 5 && isStepValid.value) currentStep.value++
}

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--
}

const handleFileUpload = (event: Event, field: keyof ReturnType<typeof getInitialFormData>) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    // @ts-ignore - Dynamically assigning file to state
    formData.value[field] = target.files[0]
  }
}

// Open the confirmation dialog instead of submitting right away
const onSubmit = () => {
  if (!isStepValid.value || isSubmitting.value) return
  showConfirmDialog.value = true
}

// Actually execute the API call after confirming
const executeSubmit = async () => {
  showConfirmDialog.value = false
  isSubmitting.value = true

  try {
    const formDataPayload = new FormData()

    // Map regular text fields
    Object.keys(formData.value).forEach(key => {
      // @ts-ignore
      const val = formData.value[key]
      if (val !== null && !(val instanceof File)) {
        formDataPayload.append(key, val)
      }
    })

    // Map File uploads
    const fileFields = ['validIdPhoto', 'resume', 'employmentContract', 'medicalCertificate', 'nbiClearance', 'policeClearance']
    fileFields.forEach(field => {
      // @ts-ignore
      if (formData.value[field] instanceof File) {
        // @ts-ignore
        formDataPayload.append(field, formData.value[field])
      }
    })

    // API Call
    const response = await api.post('/supplier/personnel-officer/personnel', formDataPayload, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    const finalRole = formData.value.personnelType === 'other' 
                      ? formData.value.customPersonnelType 
                      : formData.value.personnelType
    
    toast.success('Personnel added successfully!', {
      description: `${formData.value.firstName} ${formData.value.lastName} registered as ${finalRole} (Pending).`
    })

    // Reset Form to Page 1
    currentStep.value = 1
    isSubmitting.value = false
    
    // FULLY CLEAR ALL DATA (Text fields, Selects, and Files)
    formData.value = getInitialFormData()

  } catch (error: any) {
    toast.error('Failed to register personnel.', {
      description: error.response?.data?.message || 'Check your inputs and try again.'
    })
    isSubmitting.value = false
  }
}

// --- Options ---
const personnelTypes = [
  { value: 'Finance Personnel', label: 'Finance Personnel' },
  { value: 'Operational Personnel', label: 'Operational Personnel' },
  { value: 'Delivery Personnel', label: 'Delivery Personnel' },
  { value: 'other', label: 'Other (Custom Role)' }
]

const employmentTypes = [
  { value: 'full_time', label: 'Full Time' },
  { value: 'part_time', label: 'Part Time' },
  { value: 'contract', label: 'Contract' },
  { value: 'temporary', label: 'Temporary' }
]

const idTypes = [
  { value: 'passport', label: 'Passport' },
  { value: 'driver_license', label: "Driver's License" },
  { value: 'sss', label: 'SSS ID' },
  { value: 'philhealth', label: 'PhilHealth ID' },
  { value: 'pagibig', label: 'Pag-IBIG ID' },
  { value: 'umid', label: 'UMID' },
  { value: 'voter_id', label: "Voter's ID" },
  { value: 'prc', label: 'PRC ID' },
  { value: 'postal', label: 'Postal ID' },
  { value: 'tin', label: 'TIN ID' }
]
</script>

<template>
  <div class="max-w-8xl mx-auto p-4 md:p-6 space-y-6">
    
    <div class="flex items-center space-x-4 mb-6">
      <div class="p-3 bg-primary/10 rounded-full">
        <Users class="w-6 h-6 text-primary" />
      </div>
      <div>
        <h2 class="text-2xl font-bold tracking-tight">Add New Personnel</h2>
        <p class="text-muted-foreground">Register general staff, assign their role, and upload credentials.</p>
      </div>
    </div>

    <Card class="border-muted/60 shadow-sm overflow-hidden">
      <div class="bg-muted/30 px-6 py-4 border-b overflow-x-auto">
        <div class="flex items-center justify-between relative min-w-[500px]">
          <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-muted rounded-full overflow-hidden z-0">
            <div 
              class="h-full bg-primary transition-all duration-500 ease-in-out"
              :style="{ width: `${((currentStep - 1) / 4) * 100}%` }"
            ></div>
          </div>
          
          <div v-for="step in 5" :key="step" class="relative z-10 flex flex-col items-center gap-2 bg-background p-1 rounded-full">
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
              <Landmark v-else-if="step === 3" class="w-5 h-5" />
              <GraduationCap v-else-if="step === 4" class="w-5 h-5" />
              <FileCheck v-else class="w-5 h-5" />
            </div>
            <span class="text-xs font-medium hidden sm:block whitespace-nowrap" 
              :class="step <= currentStep ? 'text-foreground' : 'text-muted-foreground'">
              {{ ['Personal', 'Role & Account', 'Financial', 'Education & ID', 'Documents'][step - 1] }}
            </span>
          </div>
        </div>
      </div>

      <CardContent class="p-6 md:p-8">
        <form @submit.prevent="onSubmit" class="min-h-[400px]">
          
          <div v-show="currentStep === 1" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold flex items-center gap-2"><User class="w-5 h-5 text-primary"/> Personal Information</h3>
              <p class="text-sm text-muted-foreground">Basic identity and contact details.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="space-y-2">
                <Label for="firstName">First Name <span class="text-destructive">*</span></Label>
                <Input id="firstName" v-model="formData.firstName" placeholder="First Name" />
              </div>
              <div class="space-y-2">
                <Label for="middleName">Middle Name</Label>
                <Input id="middleName" v-model="formData.middleName" placeholder="Middle Name" />
              </div>
              <div class="space-y-2">
                <Label for="lastName">Last Name <span class="text-destructive">*</span></Label>
                <Input id="lastName" v-model="formData.lastName" placeholder="Last Name" />
              </div>

              <div class="space-y-2">
                <Label for="email">Email Address <span class="text-destructive">*</span></Label>
                <div class="relative">
                  <Mail class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input id="email" type="email" v-model="formData.email" class="pl-9" placeholder="email@example.com" />
                </div>
              </div>
              <div class="space-y-2">
                <Label for="phone">Phone Number <span class="text-destructive">*</span></Label>
                <div class="relative">
                  <Phone class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input id="phone" v-model="formData.phone" class="pl-9" placeholder="09XXXXXXXXX" maxlength="11" />
                </div>
                <p v-if="phoneError" class="text-xs text-destructive">{{ phoneError }}</p>
              </div>
              <div class="space-y-2">
                <Label for="emergencyContact">Emergency Contact</Label>
                <div class="relative">
                  <Phone class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input id="emergencyContact" v-model="formData.emergencyContact" class="pl-9" placeholder="09XXXXXXXXX" maxlength="11" />
                </div>
              </div>

              <div class="space-y-2">
                <Label for="dateOfBirth">Date of Birth <span class="text-destructive">*</span></Label>
                <Input id="dateOfBirth" type="date" v-model="formData.dateOfBirth" />
              </div>
              <div class="space-y-2">
                <Label for="gender">Gender <span class="text-destructive">*</span></Label>
                <select id="gender" v-model="formData.gender" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none">
                  <option value="" disabled>Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="space-y-2">
                <Label for="maritalStatus">Marital Status <span class="text-destructive">*</span></Label>
                <select id="maritalStatus" v-model="formData.maritalStatus" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none">
                  <option value="" disabled>Select Status</option>
                  <option value="single">Single</option>
                  <option value="married">Married</option>
                  <option value="widowed">Widowed</option>
                  <option value="separated">Separated</option>
                  <option value="divorced">Divorced</option>
                </select>
              </div>

              <div class="space-y-2">
                <Label for="nationality">Nationality <span class="text-destructive">*</span></Label>
                <Input id="nationality" v-model="formData.nationality" />
              </div>
              <div class="space-y-2 md:col-span-2">
                <Label for="address">Complete Address <span class="text-destructive">*</span></Label>
                <div class="relative">
                  <MapPin class="absolute left-3 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input id="address" v-model="formData.address" class="pl-9" placeholder="Street, Barangay, City, Province" />
                </div>
              </div>
            </div>
          </div>

          <div v-show="currentStep === 2" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold flex items-center gap-2"><Briefcase class="w-5 h-5 text-primary"/> Role & Account Setup</h3>
              <p class="text-sm text-muted-foreground">Define their specific personnel role and system access.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-6">
                <div class="space-y-2">
                  <Label for="personnelType">Personnel Role <span class="text-destructive">*</span></Label>
                  <select id="personnelType" v-model="formData.personnelType" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none">
                    <option value="" disabled>Select Role...</option>
                    <option v-for="type in personnelTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                  </select>
                </div>

                <div v-if="formData.personnelType === 'other'" class="space-y-2 animate-in fade-in slide-in-from-top-2">
                  <Label for="customPersonnelType">Specify Role <span class="text-destructive">*</span></Label>
                  <Input id="customPersonnelType" v-model="formData.customPersonnelType" placeholder="e.g. Marketing Specialist" />
                </div>
                
                <div class="space-y-2">
                  <Label for="employmentType">Employment Type <span class="text-destructive">*</span></Label>
                  <select id="employmentType" v-model="formData.employmentType" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none">
                    <option value="" disabled>Select Type...</option>
                    <option v-for="type in employmentTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                  </select>
                </div>
                
                <div class="space-y-2">
                  <Label for="hireDate">Hire Date <span class="text-destructive">*</span></Label>
                  <Input id="hireDate" type="date" v-model="formData.hireDate" />
                </div>
              </div>

              <div class="space-y-6 bg-muted/20 p-5 rounded-lg border border-muted">
                <h4 class="font-medium flex items-center gap-2"><KeyRound class="w-4 h-4"/> Security Details</h4>
                <div class="space-y-2">
                  <Label for="password">Password <span class="text-destructive">*</span></Label>
                  <div class="relative">
                    <Input id="password" :type="showPassword ? 'text' : 'password'" v-model="formData.password" class="pr-10" placeholder="Create password" />
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground">
                      <Eye v-if="!showPassword" class="h-4 w-4" />
                      <EyeOff v-else class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="confirmPassword">Confirm Password <span class="text-destructive">*</span></Label>
                  <div class="relative">
                    <Input id="confirmPassword" :type="showConfirmPassword ? 'text' : 'password'" v-model="formData.confirmPassword" class="pr-10" placeholder="Confirm password" />
                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2.5 text-muted-foreground hover:text-foreground">
                      <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                      <EyeOff v-else class="h-4 w-4" />
                    </button>
                  </div>
                </div>

                <div class="p-3 bg-background rounded-md border text-sm">
                  <ul class="space-y-1.5">
                    <li v-for="req in passwordRequirements" :key="req.text" class="flex items-center">
                      <Check class="w-3.5 h-3.5 mr-2" :class="req.met ? 'text-emerald-500' : 'text-muted-foreground/30'" />
                      <span :class="req.met ? 'text-foreground' : 'text-muted-foreground'">{{ req.text }}</span>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <div v-show="currentStep === 3" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold flex items-center gap-2"><Landmark class="w-5 h-5 text-primary"/> Financial & Government</h3>
              <p class="text-sm text-muted-foreground">Bank details and mandatory government numbers.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-4 bg-muted/10 p-5 rounded-lg border border-muted">
                <h4 class="font-medium">Bank Information</h4>
                <div class="space-y-2">
                  <Label>Bank Name</Label>
                  <Input v-model="formData.bankName" placeholder="e.g. BDO, BPI, UnionBank" />
                </div>
                <div class="space-y-2">
                  <Label>Account Number</Label>
                  <Input v-model="formData.bankAccountNumber" placeholder="Numbers only" />
                </div>
                <div class="space-y-2">
                  <Label>Account Holder Name</Label>
                  <Input v-model="formData.bankAccountName" placeholder="Name on bank account" />
                </div>
              </div>

              <div class="space-y-4 bg-muted/10 p-5 rounded-lg border border-muted">
                <h4 class="font-medium">Government Numbers</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label>SSS Number</Label>
                    <Input v-model="formData.sssNumber" placeholder="00-0000000-0" />
                  </div>
                  <div class="space-y-2">
                    <Label>PhilHealth Number</Label>
                    <Input v-model="formData.philhealthNumber" placeholder="00-000000000-0" />
                  </div>
                  <div class="space-y-2">
                    <Label>Pag-IBIG Number</Label>
                    <Input v-model="formData.pagibigNumber" placeholder="0000-0000-0000" />
                  </div>
                  <div class="space-y-2">
                    <Label>TIN Number</Label>
                    <Input v-model="formData.tinNumber" placeholder="000-000-000-000" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div v-show="currentStep === 4" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold flex items-center gap-2"><GraduationCap class="w-5 h-5 text-primary"/> Education & ID</h3>
              <p class="text-sm text-muted-foreground">Educational background and primary identification upload.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-4">
                <h4 class="font-medium border-b pb-2">Educational Background</h4>
                <div class="space-y-2">
                  <Label>Educational Attainment <span class="text-destructive">*</span></Label>
                  <select v-model="formData.educationalAttainment" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none">
                    <option value="" disabled>Select Attainment...</option>
                    <option value="high_school">High School</option>
                    <option value="vocational">Vocational</option>
                    <option value="college">College</option>
                    <option value="post_graduate">Post Graduate</option>
                  </select>
                </div>
                <div class="space-y-2">
                  <Label>School Graduated <span class="text-destructive">*</span></Label>
                  <Input v-model="formData.schoolGraduated" placeholder="University/School Name" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label>Year Graduated <span class="text-destructive">*</span></Label>
                    <Input type="number" v-model="formData.yearGraduated" :max="currentYear" />
                  </div>
                  <div class="space-y-2">
                    <Label>Course/Degree <span class="text-destructive">*</span></Label>
                    <Input v-model="formData.course" placeholder="e.g. BSBA" />
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <h4 class="font-medium border-b pb-2">Primary Identification</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label>ID Type <span class="text-destructive">*</span></Label>
                    <select v-model="formData.validIdType" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none">
                      <option value="" disabled>Select ID Type...</option>
                      <option v-for="type in idTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                    </select>
                  </div>
                  <div class="space-y-2">
                    <Label>ID Number <span class="text-destructive">*</span></Label>
                    <Input v-model="formData.idNumber" placeholder="Enter ID number" />
                  </div>
                </div>

                <div class="space-y-2 pt-2">
                  <Label>Upload Valid ID Photo <span class="text-destructive">*</span></Label>
                  <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.validIdPhoto ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                      <UploadCloud class="w-8 h-8 mb-2" :class="formData.validIdPhoto ? 'text-primary' : 'text-muted-foreground'" />
                      <p class="text-sm text-muted-foreground truncate max-w-[200px]">
                        {{ formData.validIdPhoto ? formData.validIdPhoto.name : 'Click to upload ID (Image/PDF)' }}
                      </p>
                    </div>
                    <input type="file" class="hidden" accept="image/*,.pdf" @change="e => handleFileUpload(e, 'validIdPhoto')" />
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div v-show="currentStep === 5" class="space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
            <div>
              <h3 class="text-lg font-semibold flex items-center gap-2"><FileCheck class="w-5 h-5 text-primary"/> Documents & Notes</h3>
              <p class="text-sm text-muted-foreground">Upload necessary employment clearances and add any final remarks.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="space-y-2">
                <Label>Resume / CV <span class="text-destructive">*</span></Label>
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.resume ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                  <div class="flex flex-col items-center justify-center">
                    <FileText class="w-5 h-5 mb-1" :class="formData.resume ? 'text-primary' : 'text-muted-foreground'" />
                    <p class="text-xs text-muted-foreground truncate max-w-[120px]">{{ formData.resume ? formData.resume.name : 'Upload Resume' }}</p>
                  </div>
                  <input type="file" class="hidden" accept=".pdf,.doc,.docx" @change="e => handleFileUpload(e, 'resume')" />
                </label>
              </div>

              <div class="space-y-2">
                <Label>Medical Certificate <span class="text-destructive">*</span></Label>
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.medicalCertificate ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                  <div class="flex flex-col items-center justify-center">
                    <FileText class="w-5 h-5 mb-1" :class="formData.medicalCertificate ? 'text-primary' : 'text-muted-foreground'" />
                    <p class="text-xs text-muted-foreground truncate max-w-[120px]">{{ formData.medicalCertificate ? formData.medicalCertificate.name : 'Upload Medical' }}</p>
                  </div>
                  <input type="file" class="hidden" accept="image/*,.pdf" @change="e => handleFileUpload(e, 'medicalCertificate')" />
                </label>
              </div>

              <div class="space-y-2">
                <Label>NBI Clearance <span class="text-destructive">*</span></Label>
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.nbiClearance ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                  <div class="flex flex-col items-center justify-center">
                    <FileText class="w-5 h-5 mb-1" :class="formData.nbiClearance ? 'text-primary' : 'text-muted-foreground'" />
                    <p class="text-xs text-muted-foreground truncate max-w-[120px]">{{ formData.nbiClearance ? formData.nbiClearance.name : 'Upload NBI' }}</p>
                  </div>
                  <input type="file" class="hidden" accept="image/*,.pdf" @change="e => handleFileUpload(e, 'nbiClearance')" />
                </label>
              </div>

              <div class="space-y-2">
                <Label>Police Clearance <span class="text-muted-foreground font-normal">(Optional)</span></Label>
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.policeClearance ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                  <div class="flex flex-col items-center justify-center">
                    <FileText class="w-5 h-5 mb-1" :class="formData.policeClearance ? 'text-primary' : 'text-muted-foreground'" />
                    <p class="text-xs text-muted-foreground truncate max-w-[120px]">{{ formData.policeClearance ? formData.policeClearance.name : 'Upload Police Cert' }}</p>
                  </div>
                  <input type="file" class="hidden" accept="image/*,.pdf" @change="e => handleFileUpload(e, 'policeClearance')" />
                </label>
              </div>

              <div class="space-y-2">
                <Label>Employment Contract <span class="text-muted-foreground font-normal">(Optional)</span></Label>
                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer hover:bg-muted/50 transition-colors" :class="formData.employmentContract ? 'border-primary bg-primary/5' : 'border-muted-foreground/25'">
                  <div class="flex flex-col items-center justify-center">
                    <FileText class="w-5 h-5 mb-1" :class="formData.employmentContract ? 'text-primary' : 'text-muted-foreground'" />
                    <p class="text-xs text-muted-foreground truncate max-w-[120px]">{{ formData.employmentContract ? formData.employmentContract.name : 'Upload Contract' }}</p>
                  </div>
                  <input type="file" class="hidden" accept=".pdf" @change="e => handleFileUpload(e, 'employmentContract')" />
                </label>
              </div>

              <div class="space-y-2 md:col-span-3">
                <Label>Additional Notes</Label>
                <textarea 
                  v-model="formData.notes" 
                  rows="3" 
                  placeholder="Any extra instructions or remarks regarding this personnel..."
                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-ring focus:outline-none resize-none"
                ></textarea>
              </div>
            </div>
          </div>

        </form>
      </CardContent>
      
      <CardFooter class="flex items-center justify-between p-6 bg-muted/10 border-t">
        <Button variant="outline" type="button" @click="currentStep === 1 ? $router.back() : prevStep()">
          {{ currentStep === 1 ? 'Cancel' : 'Back' }}
        </Button>
        
        <Button v-if="currentStep < 5" type="button" @click="nextStep" :disabled="!isStepValid">
          Continue
        </Button>
        
        <Button v-else type="button" @click="onSubmit" :disabled="!isStepValid || isSubmitting">
          {{ isSubmitting ? 'Registering...' : 'Register Personnel' }}
        </Button>
      </CardFooter>
    </Card>

    <AlertDialog v-model:open="showConfirmDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Confirm Registration</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to register <strong>{{ formData.firstName }} {{ formData.lastName }}</strong>? 
            Their account will be created and set to a <strong>pending</strong> status.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showConfirmDialog = false">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeSubmit" class="bg-primary text-primary-foreground">
            Yes, register personnel
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>