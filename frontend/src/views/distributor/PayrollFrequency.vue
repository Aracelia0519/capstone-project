<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Calendar, Check, AlertCircle, Loader2, Clock, AlertTriangle } from 'lucide-vue-next'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
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
import { toast } from 'vue-sonner'
import api from '@/utils/axios'

// --- State ---
const frequency = ref('weekly')
const selectedDayOfWeek = ref('friday')
const selectedDayOfMonth = ref('1')
const selectedBiMonthlyOption = ref('1-15')

// Late Deduction State
const latePolicy = ref('none') // none, prorated, fixed_block
const lateAmount = ref('')
const lateMinutes = ref('15')

const isLoading = ref(false)
const isSaving = ref(false)
const showConfirmDialog = ref(false)

// --- Data Options ---
const daysOfWeek = [
  { value: 'monday', label: 'Monday' },
  { value: 'tuesday', label: 'Tuesday' },
  { value: 'wednesday', label: 'Wednesday' },
  { value: 'thursday', label: 'Thursday' },
  { value: 'friday', label: 'Friday' },
  { value: 'saturday', label: 'Saturday' },
  { value: 'sunday', label: 'Sunday' },
]

// Generate days 1-31 for monthly selection
const daysOfMonth = Array.from({ length: 31 }, (_, i) => ({
  value: String(i + 1),
  label: `${i + 1}${getOrdinalSuffix(i + 1)} of the month`
}))
daysOfMonth.push({ value: 'last', label: 'Last day of the month' })

const biMonthlyOptions = [
  { value: '1-15', label: '1st and 15th of the month' },
  { value: '5-20', label: '5th and 20th of the month' },
  { value: '10-25', label: '10th and 25th of the month' },
  { value: '15-last', label: '15th and Last day of the month' },
]

// --- Helpers ---
function getOrdinalSuffix(i: number) {
  const j = i % 10, k = i % 100
  if (j === 1 && k !== 11) return "st"
  if (j === 2 && k !== 12) return "nd"
  if (j === 3 && k !== 13) return "rd"
  return "th"
}

// --- Computed Summaries ---
const frequencySummary = computed(() => {
  if (frequency.value === 'daily') {
    return 'Employees will be paid every day.'
  }
  if (frequency.value === 'weekly') {
    const day = daysOfWeek.find(d => d.value === selectedDayOfWeek.value)?.label
    return `Employees will be paid every week on ${day}.`
  }
  if (frequency.value === 'bi-monthly') {
    const opt = biMonthlyOptions.find(o => o.value === selectedBiMonthlyOption.value)?.label
    return `Employees will be paid twice a month: ${opt}.`
  }
  if (frequency.value === 'monthly') {
    const day = daysOfMonth.find(d => d.value === selectedDayOfMonth.value)?.label
    return `Employees will be paid once a month on the ${day}.`
  }
  return ''
})

const lateSummary = computed(() => {
  if (latePolicy.value === 'none') {
    return 'No deductions will be applied for late attendance.'
  }
  if (latePolicy.value === 'prorated') {
    return `Employees will be deducted ₱${lateAmount.value || '0'} for every minute they are late.`
  }
  if (latePolicy.value === 'fixed_block') {
    return `Employees will be deducted ₱${lateAmount.value || '0'} for every ${lateMinutes.value || '0'} minutes late.`
  }
  return ''
})

// --- API Actions ---
const fetchSettings = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/distributor/payroll-settings')
    const data = response.data || response
    const settings = data.data || data

    if (settings) {
        // Frequency Settings
        if (settings.frequency) frequency.value = settings.frequency
        if (settings.day_of_week) selectedDayOfWeek.value = settings.day_of_week
        if (settings.bi_monthly_schedule) selectedBiMonthlyOption.value = settings.bi_monthly_schedule
        if (settings.day_of_month) selectedDayOfMonth.value = settings.day_of_month
        
        // Late Deduction Settings
        if (settings.late_deduction_policy) latePolicy.value = settings.late_deduction_policy
        if (settings.late_deduction_amount) lateAmount.value = settings.late_deduction_amount
        if (settings.late_deduction_minutes) lateMinutes.value = String(settings.late_deduction_minutes)
    }
  } catch (error) {
    console.error('Failed to load settings', error)
  } finally {
    isLoading.value = false
  }
}

// 1. Triggered by the "Save Changes" button
const handleSaveClick = () => {
  // Simple validation
  if (latePolicy.value !== 'none' && !lateAmount.value) {
    toast.error("Please enter a deduction amount.")
    return
  }
  showConfirmDialog.value = true
}

// 2. Triggered by "Continue" in the dialog
const executeSave = async () => {
  showConfirmDialog.value = false // Close dialog immediately
  isSaving.value = true
  
  const payload = {
    // Frequency
    frequency: frequency.value,
    day_of_week: frequency.value === 'weekly' ? selectedDayOfWeek.value : null,
    bi_monthly_schedule: frequency.value === 'bi-monthly' ? selectedBiMonthlyOption.value : null,
    day_of_month: frequency.value === 'monthly' ? selectedDayOfMonth.value : null,
    
    // Late Deduction
    late_deduction_policy: latePolicy.value,
    late_deduction_amount: latePolicy.value !== 'none' ? lateAmount.value : null,
    late_deduction_minutes: latePolicy.value === 'fixed_block' ? lateMinutes.value : null,
  }

  try {
    await api.put('/distributor/payroll-settings', payload)
    toast.success('Payroll settings updated successfully!')
  } catch (error: any) {
    console.error('Failed to save settings', error)
    let msg = 'Failed to update payroll settings.'
    if(error.response?.data?.message) msg = error.response.data.message
    toast.error(msg)
  } finally {
    isSaving.value = false
  }
}

onMounted(() => {
  fetchSettings()
})
</script>

<template>
  <div class="w-full max-w-8xl mx-auto p-4 space-y-6">
    
    <Card>
      <CardHeader>
        <div class="flex items-center gap-2">
          <div class="p-2 bg-primary/10 rounded-full">
            <Calendar class="w-5 h-5 text-primary" />
          </div>
          <div>
            <CardTitle>Payroll Frequency</CardTitle>
            <CardDescription>
              Configure the payment schedule for your Employees.
            </CardDescription>
          </div>
        </div>
      </CardHeader>

      <CardContent class="space-y-6">
        
        <div v-if="isLoading" class="flex justify-center py-10">
          <Loader2 class="w-8 h-8 animate-spin text-muted-foreground" />
        </div>

        <template v-else>
          <div class="space-y-3">
            <Label class="text-base">How often do you want to pay?</Label>
            <RadioGroup v-model="frequency" class="grid grid-cols-1 md:grid-cols-2 gap-4">
              
              <div>
                <RadioGroupItem id="daily" value="daily" class="peer sr-only" />
                <Label
                  for="daily"
                  class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-transparent p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary cursor-pointer transition-all"
                >
                  <span class="text-sm font-semibold">Daily</span>
                  <span class="text-xs text-muted-foreground mt-1">365 payments/year</span>
                </Label>
              </div>

              <div>
                <RadioGroupItem id="weekly" value="weekly" class="peer sr-only" />
                <Label
                  for="weekly"
                  class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-transparent p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary cursor-pointer transition-all"
                >
                  <span class="text-sm font-semibold">Weekly</span>
                  <span class="text-xs text-muted-foreground mt-1">52 payments/year</span>
                </Label>
              </div>

              <div>
                <RadioGroupItem id="bi-monthly" value="bi-monthly" class="peer sr-only" />
                <Label
                  for="bi-monthly"
                  class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-transparent p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary cursor-pointer transition-all"
                >
                  <span class="text-sm font-semibold">Bi-Monthly</span>
                  <span class="text-xs text-muted-foreground mt-1">24 payments/year</span>
                </Label>
              </div>

              <div>
                <RadioGroupItem id="monthly" value="monthly" class="peer sr-only" />
                <Label
                  for="monthly"
                  class="flex flex-col items-center justify-between rounded-md border-2 border-muted bg-transparent p-4 hover:bg-accent hover:text-accent-foreground peer-data-[state=checked]:border-primary [&:has([data-state=checked])]:border-primary cursor-pointer transition-all"
                >
                  <span class="text-sm font-semibold">Monthly</span>
                  <span class="text-xs text-muted-foreground mt-1">12 payments/year</span>
                </Label>
              </div>
            </RadioGroup>
          </div>

          <div class="pt-4 border-t">
            <div v-if="frequency === 'daily'" class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-300">
              <div class="p-4 bg-muted rounded-md text-sm text-muted-foreground">
                <span class="font-medium text-foreground">Note:</span> 
                Calculations and payouts will be processed automatically at the end of every day.
              </div>
            </div>

            <div v-else-if="frequency === 'weekly'" class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-300">
              <Label>Payout Day</Label>
              <Select v-model="selectedDayOfWeek">
                <SelectTrigger>
                  <SelectValue placeholder="Select a day" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="day in daysOfWeek" :key="day.value" :value="day.value">
                    {{ day.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div v-else-if="frequency === 'bi-monthly'" class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-300">
              <Label>Payout Schedule</Label>
              <Select v-model="selectedBiMonthlyOption">
                <SelectTrigger>
                  <SelectValue placeholder="Select dates" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem v-for="opt in biMonthlyOptions" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div v-else-if="frequency === 'monthly'" class="space-y-3 animate-in fade-in slide-in-from-top-2 duration-300">
              <Label>Payout Date</Label>
              <Select v-model="selectedDayOfMonth">
                <SelectTrigger>
                  <SelectValue placeholder="Select a date" />
                </SelectTrigger>
                <SelectContent class="max-h-[200px]">
                  <SelectItem v-for="day in daysOfMonth" :key="day.value" :value="day.value">
                    {{ day.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </template>
      </CardContent>
    </Card>

    <Card>
        <CardHeader>
            <div class="flex items-center gap-2">
                <div class="p-2 bg-destructive/10 rounded-full">
                    <Clock class="w-5 h-5 text-destructive" />
                </div>
                <div>
                    <CardTitle>Late Deduction Policy</CardTitle>
                    <CardDescription>
                        Set how deductions apply when an employee clocks in late.
                    </CardDescription>
                </div>
            </div>
        </CardHeader>
        <CardContent class="space-y-6">
            <template v-if="!isLoading">
                <RadioGroup v-model="latePolicy" class="space-y-4">
                    
                    <div class="flex items-start space-x-2">
                        <RadioGroupItem id="none" value="none" class="mt-1" />
                        <div class="grid gap-1.5">
                            <Label for="none" class="font-semibold cursor-pointer">No Deduction</Label>
                            <p class="text-sm text-muted-foreground">Employees are not penalized for being late.</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-2">
                        <RadioGroupItem id="prorated" value="prorated" class="mt-1" />
                        <div class="grid gap-1.5 w-full">
                            <Label for="prorated" class="font-semibold cursor-pointer">Prorated per Minute</Label>
                            <p class="text-sm text-muted-foreground">Deduct a specific amount for every minute late.</p>
                            
                            <div v-if="latePolicy === 'prorated'" class="mt-2 flex items-center gap-2 animate-in fade-in slide-in-from-top-2">
                                <span class="text-sm font-medium">₱</span>
                                <Input type="number" v-model="lateAmount" placeholder="Amount (e.g. 2.00)" class="w-32" min="0" step="0.01" />
                                <span class="text-sm text-muted-foreground">per minute</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start space-x-2">
                        <RadioGroupItem id="fixed" value="fixed_block" class="mt-1" />
                        <div class="grid gap-1.5 w-full">
                            <Label for="fixed" class="font-semibold cursor-pointer">Fixed Amount per Time Block</Label>
                            <p class="text-sm text-muted-foreground">Deduct a fixed amount for every X minutes (e.g., ₱50 per 15 mins).</p>
                            
                            <div v-if="latePolicy === 'fixed_block'" class="mt-2 flex flex-wrap items-center gap-2 animate-in fade-in slide-in-from-top-2">
                                <span class="text-sm font-medium">Deduct ₱</span>
                                <Input type="number" v-model="lateAmount" placeholder="50.00" class="w-24" min="0" step="0.01" />
                                <span class="text-sm font-medium">for every</span>
                                <Input type="number" v-model="lateMinutes" placeholder="15" class="w-20" min="1" />
                                <span class="text-sm text-muted-foreground">minutes late</span>
                            </div>
                        </div>
                    </div>

                </RadioGroup>
            </template>
        </CardContent>
    </Card>

    <div class="flex flex-col gap-4">
        <Alert class="bg-muted/50 border-primary/20">
            <AlertCircle class="h-4 w-4 text-primary" />
            <AlertTitle>Summary of Changes</AlertTitle>
            <AlertDescription class="mt-2 flex flex-col gap-2">
                <div class="flex gap-2">
                    <Check class="w-4 h-4 text-green-600 mt-0.5" />
                    <span>{{ frequencySummary }}</span>
                </div>
                <div class="flex gap-2">
                    <AlertTriangle v-if="latePolicy !== 'none'" class="w-4 h-4 text-amber-600 mt-0.5" />
                    <Check v-else class="w-4 h-4 text-green-600 mt-0.5" />
                    <span>{{ lateSummary }}</span>
                </div>
            </AlertDescription>
        </Alert>

        <div class="flex justify-end gap-3">
            <Button variant="outline" :disabled="isLoading || isSaving">Cancel</Button>
            <Button @click="handleSaveClick" :disabled="isLoading || isSaving" class="min-w-[140px]">
                <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" />
                <Check v-else class="w-4 h-4 mr-2" />
                {{ isSaving ? 'Saving...' : 'Save All Changes' }}
            </Button>
        </div>
    </div>

    <AlertDialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Confirm Payroll Settings</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to update these settings? This will affect how payroll is calculated for all employees.
            <div class="mt-4 space-y-2 p-3 bg-muted rounded-md text-sm text-foreground">
               <p><strong>Frequency:</strong> {{ frequencySummary }}</p>
               <p><strong>Lates:</strong> {{ lateSummary }}</p>
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeSave">Continue</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>