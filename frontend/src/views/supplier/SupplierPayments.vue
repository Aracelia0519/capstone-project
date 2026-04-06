<template>
  <div class="min-h-screen bg-muted/20 p-4 md:p-8">
    <div class="max-w-4xl mx-auto">
      
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-foreground flex items-center gap-3">
          <Wallet class="w-8 h-8 text-primary" />
          Payment Settings
        </h1>
        <p class="text-muted-foreground mt-2 text-lg">
          Configure the payment methods available for your clients and distributors.
        </p>
      </div>

      <div v-if="isLoading" class="flex justify-center items-center py-20">
        <Loader2 class="w-10 h-10 animate-spin text-primary" />
      </div>

      <div v-else class="space-y-6">
        
        <Card class="border-border shadow-sm">
          <CardHeader class="pb-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                  <Banknote class="w-6 h-6 text-blue-600" />
                </div>
                <div>
                  <CardTitle class="text-xl">Cash on Delivery (COD)</CardTitle>
                  <CardDescription class="mt-1">
                    Allow clients to pay in cash upon successful delivery of their items.
                  </CardDescription>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-2">
                <input type="checkbox" v-model="settingsForm.is_cod_enabled" class="sr-only peer">
                <div class="w-14 h-7 bg-muted-foreground/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary"></div>
              </label>
            </div>
          </CardHeader>
        </Card>

        <Card class="border-border shadow-sm transition-all duration-300" :class="{'ring-1 ring-blue-500 border-blue-500': settingsForm.is_gcash_enabled}">
          <CardHeader class="pb-4 border-b border-border/50">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center shrink-0 shadow-inner">
                  <Smartphone class="w-6 h-6 text-white" />
                </div>
                <div>
                  <CardTitle class="text-xl">GCash Direct Transfer</CardTitle>
                  <CardDescription class="mt-1">
                    Accept direct GCash transfers. You must provide a valid GCash number.
                  </CardDescription>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-2">
                <input type="checkbox" v-model="settingsForm.is_gcash_enabled" class="sr-only peer">
                <div class="w-14 h-7 bg-muted-foreground/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </CardHeader>
          
          <div 
            class="transition-all duration-300 ease-in-out overflow-hidden"
            :class="settingsForm.is_gcash_enabled ? 'max-h-40 opacity-100' : 'max-h-0 opacity-0'"
          >
            <CardContent class="pt-6">
              <div class="max-w-md space-y-3">
                <Label for="gcash_number" class="text-sm font-semibold flex items-center gap-1">
                  GCash Mobile Number <span class="text-red-500">*</span>
                </Label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <span class="text-muted-foreground font-medium">+63</span>
                  </div>
                  <Input 
                    id="gcash_number" 
                    type="tel" 
                    v-model="settingsForm.gcash_number" 
                    placeholder="912 345 6789"
                    class="pl-12 h-12 text-lg font-medium tracking-wide"
                    maxlength="11"
                  />
                </div>
                <p class="text-xs text-muted-foreground flex items-center gap-1">
                  <Info class="w-3.5 h-3.5" />
                  Ensure this number is fully verified and can receive funds.
                </p>
              </div>
            </CardContent>
          </div>
        </Card>

        <Card class="border-border shadow-sm transition-all duration-300" :class="{'ring-1 ring-purple-500 border-purple-500': settingsForm.is_bank_enabled}">
          <CardHeader class="pb-4 border-b border-border/50">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-purple-600 flex items-center justify-center shrink-0 shadow-inner">
                  <Building class="w-6 h-6 text-white" />
                </div>
                <div>
                  <CardTitle class="text-xl">Bank Transfer</CardTitle>
                  <CardDescription class="mt-1">
                    Accept direct bank deposits or transfers. Provide your bank name, account name, and account number.
                  </CardDescription>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-2">
                <input type="checkbox" v-model="settingsForm.is_bank_enabled" class="sr-only peer">
                <div class="w-14 h-7 bg-muted-foreground/30 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-purple-600"></div>
              </label>
            </div>
          </CardHeader>
          
          <div 
            class="transition-all duration-300 ease-in-out overflow-hidden"
            :class="settingsForm.is_bank_enabled ? 'max-h-[400px] opacity-100' : 'max-h-0 opacity-0'"
          >
            <CardContent class="pt-6">
              <div class="max-w-md space-y-4">
                <div class="space-y-2">
                  <Label for="bank_name" class="text-sm font-semibold flex items-center gap-1">
                    Bank Name <span class="text-red-500">*</span>
                  </Label>
                  <Input 
                    id="bank_name" 
                    type="text" 
                    v-model="settingsForm.bank_name" 
                    placeholder="e.g. BDO, BPI, UnionBank"
                    class="h-12 text-lg font-medium"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="bank_account_name" class="text-sm font-semibold flex items-center gap-1">
                    Account Name <span class="text-red-500">*</span>
                  </Label>
                  <Input 
                    id="bank_account_name" 
                    type="text" 
                    v-model="settingsForm.bank_account_name" 
                    placeholder="e.g. Julian Namoc"
                    class="h-12 text-lg font-medium"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="bank_account_number" class="text-sm font-semibold flex items-center gap-1">
                    Bank Account Number <span class="text-red-500">*</span>
                  </Label>
                  <Input 
                    id="bank_account_number" 
                    type="text" 
                    v-model="settingsForm.bank_account_number" 
                    placeholder="Enter account number"
                    class="h-12 text-lg font-medium tracking-wide"
                  />
                </div>
                <p class="text-xs text-muted-foreground flex items-center gap-1">
                  <Info class="w-3.5 h-3.5" />
                  Make sure the details match your official banking records.
                </p>
              </div>
            </CardContent>
          </div>
        </Card>

        <div class="flex items-center justify-end pt-6 mt-4 border-t border-border">
          <Button @click="fetchSettings" variant="ghost" class="mr-4 hover:bg-transparent" :disabled="isSaving">
            Discard Changes
          </Button>
          <Button @click="saveSettings" size="lg" class="px-8 font-bold" :disabled="isSaving">
            <Loader2 v-if="isSaving" class="w-5 h-5 mr-2 animate-spin" />
            <Save v-else class="w-5 h-5 mr-2" />
            {{ isSaving ? 'Saving...' : 'Save Settings' }}
          </Button>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'

// Icons
import { 
  Wallet, 
  Banknote, 
  Smartphone, 
  Building,
  Save, 
  Loader2,
  Info
} from 'lucide-vue-next'

// Shadcn UI Components
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

// State
const isLoading = ref(true)
const isSaving = ref(false)

const settingsForm = ref({
  is_cod_enabled: true,
  is_gcash_enabled: false,
  is_bank_enabled: false,
  gcash_number: '',
  bank_name: '',
  bank_account_name: '',
  bank_account_number: ''
})

// Fetch Settings
const fetchSettings = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/supplier/payment-settings')
    if (response.data.success && response.data.data) {
      settingsForm.value = {
        is_cod_enabled: !!response.data.data.is_cod_enabled,
        is_gcash_enabled: !!response.data.data.is_gcash_enabled,
        is_bank_enabled: !!response.data.data.is_bank_enabled,
        gcash_number: response.data.data.gcash_number || '',
        bank_name: response.data.data.bank_name || '',
        bank_account_name: response.data.data.bank_account_name || '',
        bank_account_number: response.data.data.bank_account_number || ''
      }
    }
  } catch (error) {
    console.error('Failed to fetch payment settings:', error)
    toast.error('Unable to load payment settings.')
  } finally {
    isLoading.value = false
  }
}

// Save Settings
const saveSettings = async () => {
  // Validation for GCash
  if (settingsForm.value.is_gcash_enabled) {
    const number = settingsForm.value.gcash_number?.trim() || ''
    if (!number) {
      toast.error('GCash Number Required', {
        description: 'You must provide a GCash number if GCash is enabled.'
      })
      return
    }
    // Basic PH mobile number check
    if (number.length < 10) {
      toast.error('Invalid GCash Number', {
        description: 'Please enter a valid 10 or 11 digit mobile number.'
      })
      return
    }
  }

  // Validation for Bank Transfer
  if (settingsForm.value.is_bank_enabled) {
    if (!settingsForm.value.bank_name?.trim() || !settingsForm.value.bank_account_name?.trim() || !settingsForm.value.bank_account_number?.trim()) {
      toast.error('Bank Details Required', {
        description: 'You must provide the Bank Name, Account Name, and Account Number.'
      })
      return
    }
  }

  if (!settingsForm.value.is_cod_enabled && !settingsForm.value.is_gcash_enabled && !settingsForm.value.is_bank_enabled) {
    toast.warning('Warning', {
      description: 'You have disabled all payment methods. Clients will not be able to checkout.'
    })
  }

  isSaving.value = true
  try {
    const response = await api.put('/supplier/payment-settings', settingsForm.value)
    
    if (response.data.success) {
      toast.success('Settings Saved', {
        description: 'Your payment preferences have been updated successfully.'
      })
    }
  } catch (error) {
    console.error('Failed to save settings:', error)
    toast.error('Failed to save settings', {
      description: error.response?.data?.message || 'An unexpected error occurred.'
    })
  } finally {
    isSaving.value = false
  }
}

// Init
onMounted(() => {
  fetchSettings()
})
</script>