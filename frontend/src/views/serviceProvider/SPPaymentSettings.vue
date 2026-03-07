<template>
  <div class="min-h-screen p-4 md:p-8 text-white">
    <div class="max-w-4xl mx-auto">
      
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <Wallet class="w-8 h-8 text-blue-500" />
          Service Payment Settings
        </h1>
        <p class="text-gray-400 mt-2 text-lg">
          Configure the payment methods available for clients booking your services.
        </p>
      </div>

      <div v-if="isLoading" class="flex justify-center items-center py-20">
        <Loader2 class="w-10 h-10 animate-spin text-blue-500" />
      </div>

      <div v-else class="space-y-6">
        
        <Card class="bg-gray-800/50 border-gray-700 shadow-sm">
          <CardHeader class="pb-4">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-500/20 flex items-center justify-center shrink-0">
                  <Banknote class="w-6 h-6 text-emerald-400" />
                </div>
                <div>
                  <CardTitle class="text-xl text-white">On Hand Payment</CardTitle>
                  <CardDescription class="mt-1 text-gray-400">
                    Allow clients to pay you in cash directly after the service is completed.
                  </CardDescription>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-2">
                <input type="checkbox" v-model="settingsForm.is_on_hand_enabled" class="sr-only peer">
                <div class="w-14 h-7 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-200 after:border-gray-500 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-emerald-500"></div>
              </label>
            </div>
          </CardHeader>
        </Card>

        <Card class="bg-gray-800/50 shadow-sm transition-all duration-300" :class="settingsForm.is_gcash_enabled ? 'ring-1 ring-blue-500 border-blue-500' : 'border-gray-700'">
          <CardHeader class="pb-4 border-b border-gray-700/50">
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center shrink-0 shadow-inner">
                  <Smartphone class="w-6 h-6 text-white" />
                </div>
                <div>
                  <CardTitle class="text-xl text-white">GCash Direct Transfer</CardTitle>
                  <CardDescription class="mt-1 text-gray-400">
                    Accept direct GCash transfers. You must provide a valid GCash number.
                  </CardDescription>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer shrink-0 mt-2">
                <input type="checkbox" v-model="settingsForm.is_gcash_enabled" class="sr-only peer">
                <div class="w-14 h-7 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-gray-200 after:border-gray-500 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600"></div>
              </label>
            </div>
          </CardHeader>
          
          <div 
            class="transition-all duration-300 ease-in-out overflow-hidden"
            :class="settingsForm.is_gcash_enabled ? 'max-h-40 opacity-100' : 'max-h-0 opacity-0'"
          >
            <CardContent class="pt-6">
              <div class="max-w-md space-y-3">
                <Label for="gcash_number" class="text-sm font-semibold flex items-center gap-1 text-gray-300">
                  GCash Mobile Number <span class="text-red-500">*</span>
                </Label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <span class="text-gray-400 font-medium">+63</span>
                  </div>
                  <Input 
                    id="gcash_number" 
                    type="tel" 
                    v-model="settingsForm.gcash_number" 
                    placeholder="912 345 6789"
                    class="pl-12 h-12 text-lg font-medium tracking-wide bg-gray-900 border-gray-600 text-white placeholder:text-gray-500 focus:border-blue-500 focus:ring-blue-500"
                    maxlength="11"
                  />
                </div>
                <p class="text-xs text-gray-400 flex items-center gap-1">
                  <Info class="w-3.5 h-3.5 text-blue-400" />
                  Ensure this number is fully verified and can receive funds.
                </p>
              </div>
            </CardContent>
          </div>
        </Card>

        <div class="flex items-center justify-end pt-6 mt-4 border-t border-gray-800">
          <Button @click="fetchSettings" variant="ghost" class="mr-4 text-gray-300 hover:text-white hover:bg-gray-800" :disabled="isSaving">
            Discard Changes
          </Button>
          <Button @click="saveSettings" size="lg" class="px-8 font-bold bg-blue-600 hover:bg-blue-500 text-white border-0" :disabled="isSaving">
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
  is_on_hand_enabled: true,
  is_gcash_enabled: false,
  gcash_number: ''
})

// Fetch Settings
const fetchSettings = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/service-provider/payment-settings')
    if (response.data.success && response.data.data) {
      settingsForm.value = {
        is_on_hand_enabled: !!response.data.data.is_on_hand_enabled,
        is_gcash_enabled: !!response.data.data.is_gcash_enabled,
        gcash_number: response.data.data.gcash_number || ''
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
  // Validation
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

  if (!settingsForm.value.is_on_hand_enabled && !settingsForm.value.is_gcash_enabled) {
    toast.warning('Warning', {
      description: 'You have disabled all payment methods. Clients will not be able to pay.'
    })
  }

  isSaving.value = true
  try {
    const response = await api.put('/service-provider/payment-settings', settingsForm.value)
    
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