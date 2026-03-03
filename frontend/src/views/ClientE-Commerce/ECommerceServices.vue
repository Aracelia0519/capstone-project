<script setup>
import { ref, onMounted, computed } from 'vue' // Added computed
import { useRouter } from 'vue-router' // <-- Added for routing
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { 
  Calendar, 
  Clock, 
  MapPin, 
  Phone, 
  User, 
  Briefcase,
  Image as ImageIcon,
  DollarSign,
  PaintRoller,
  ClipboardList // <-- Added for the manage button icon
} from 'lucide-vue-next'

// Shadcn Components
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

// State
const router = useRouter() // <-- Initialize router
const services = ref([])
const isLoading = ref(true)
const showServiceModal = ref(false)
const isSubmitting = ref(false)
const selectedService = ref(null)

const serviceRequest = ref({
  description: '',
  preferredDate: '',
  timePreference: '',
  contact: '',
  address: ''
})

// --- NEW: Calculate the minimum allowed date (Today + 3 Days) ---
const minDate = computed(() => {
  const date = new Date()
  date.setDate(date.getDate() + 3)
  return date.toISOString().split('T')[0] // Formats as YYYY-MM-DD
})

// Fetch Data
const fetchServices = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/client/services')
    if (response.data.success) {
      services.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching services:', error)
    toast.error('Failed to load services.')
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchServices()
})

// Modal Logic
const openServiceModal = (service) => {
  selectedService.value = service
  showServiceModal.value = true
}

const closeServiceModal = () => {
  showServiceModal.value = false
  setTimeout(() => {
    selectedService.value = null
    serviceRequest.value = {
      description: '',
      preferredDate: '',
      timePreference: '',
      contact: '',
      address: ''
    }
  }, 300)
}

const submitServiceRequest = async () => {
  if (!serviceRequest.value.preferredDate || !serviceRequest.value.timePreference || !serviceRequest.value.contact || !serviceRequest.value.address) {
    toast.error('Please fill in all required fields')
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      service_offering_id: selectedService.value.id,
      provider_id: selectedService.value.provider_id,
      description: serviceRequest.value.description || 'No specific instructions provided.',
      preferred_date: serviceRequest.value.preferredDate,
      time_preference: serviceRequest.value.timePreference,
      contact_number: serviceRequest.value.contact,
      address: serviceRequest.value.address
    }

    const response = await api.post('/client/services/request', payload)

    if (response.data.success) {
      toast.success(response.data.message)
      closeServiceModal()
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to submit request. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50/50">
    
    <div class="bg-white border-b border-gray-200">
      <div class="container mx-auto px-4 py-12 md:py-16 text-center max-w-3xl">
        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
          <PaintRoller class="w-8 h-8 text-blue-600" />
        </div>
        <h1 class="text-3xl md:text-5xl font-black text-gray-900 tracking-tight mb-4">Professional Painting Services</h1>
        <p class="text-lg text-gray-500 font-medium mb-8">Browse and request verified service providers for your interior, exterior, and custom painting needs.</p>
        
        <Button 
          @click="router.push('/Clients/myServiceRequest')" 
          variant="outline" 
          class="border-2 border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 rounded-xl font-bold px-6 h-12 shadow-sm transition-all inline-flex items-center gap-2"
        >
          <ClipboardList class="w-5 h-5 text-blue-600" />
          Manage Requested Services
        </Button>
      </div>
    </div>

    <div class="container mx-auto px-4 py-12">
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-500 font-medium">Loading available services...</p>
      </div>

      <div v-else-if="services.length === 0" class="text-center py-20 bg-white rounded-3xl border border-gray-100 shadow-sm max-w-2xl mx-auto">
        <Briefcase class="w-16 h-16 text-gray-300 mx-auto mb-4" />
        <h3 class="text-2xl font-bold text-gray-900 mb-2">No Services Available</h3>
        <p class="text-gray-500">Service providers have not posted any offerings yet. Please check back later.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <Card 
          v-for="service in services" 
          :key="service.id" 
          class="rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col bg-white"
        >
          <div class="h-56 bg-gray-100 relative overflow-hidden flex items-center justify-center">
            
            <div v-if="service.image_paths && service.image_paths.length > 0" class="w-full h-full flex overflow-x-auto snap-x snap-mandatory hide-scrollbar">
              <img 
                v-for="(image, index) in service.image_paths" 
                :key="index" 
                :src="image" 
                class="w-full h-full object-cover shrink-0 snap-center group-hover:scale-105 transition-transform duration-700" 
              />
            </div>
            
            <div v-else class="flex flex-col items-center justify-center text-gray-400 w-full h-full">
              <ImageIcon class="w-10 h-10 mb-2 opacity-50" />
              <span class="text-xs font-semibold uppercase tracking-widest">No Image</span>
            </div>
            
            <div class="absolute top-4 left-4 z-10 pointer-events-none flex flex-col gap-2 items-start">
              <Badge class="bg-white/95 text-blue-700 border-0 shadow-sm backdrop-blur-md font-bold px-3 py-1">
                {{ service.category }}
              </Badge>
            </div>

            <div v-if="service.image_paths && service.image_paths.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1 z-10 pointer-events-none bg-black/30 px-2 py-1 rounded-full backdrop-blur-sm">
              <div v-for="(_, index) in service.image_paths" :key="'dot-'+index" class="w-1.5 h-1.5 rounded-full bg-white/70"></div>
            </div>
          </div>

          <CardContent class="p-6 flex-1 flex flex-col">
            <div class="flex items-center gap-2 mb-3">
              <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center">
                <User class="w-3 h-3 text-indigo-700" />
              </div>
              <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">{{ service.provider_name }}</span>
            </div>

            <h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight group-hover:text-blue-600 transition-colors">{{ service.title }}</h3>
            
            <p class="text-sm text-gray-600 mb-6 line-clamp-3 leading-relaxed flex-1 whitespace-pre-wrap">
              {{ service.description }}
            </p>

            <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 font-medium">Rate / Price</span>
                <div class="text-right">
                  <span class="text-lg font-black text-gray-900">₱{{ service.price.toLocaleString() }}</span>
                  <span class="text-[10px] text-gray-500 uppercase tracking-wider ml-1">({{ service.price_type }})</span>
                </div>
              </div>
              <div class="h-px bg-gray-200"></div>
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 font-medium">Est. Duration</span>
                <span class="text-sm font-bold text-gray-900 flex items-center">
                  <Clock class="w-3.5 h-3.5 mr-1.5 text-blue-500" />
                  {{ service.duration }}
                </span>
              </div>
            </div>
          </CardContent>

          <CardFooter class="p-6 pt-0 mt-auto">
            <Button @click="openServiceModal(service)" class="w-full bg-gray-900 hover:bg-black text-white rounded-xl h-12 font-bold shadow-lg shadow-gray-900/20 transition-all">
              Request this Service
            </Button>
          </CardFooter>
        </Card>
      </div>
    </div>

    <Teleport to="body">
      <Dialog :open="showServiceModal" @update:open="(val) => !val && closeServiceModal()">
        <DialogContent class="sm:max-w-[600px] p-0 rounded-3xl overflow-hidden border-0 shadow-2xl bg-white max-h-[90vh] flex flex-col">
          
          <div class="px-6 py-6 border-b border-gray-100 bg-gray-50/50">
            <DialogTitle class="text-2xl font-black text-gray-900 tracking-tight">Request Service</DialogTitle>
            <DialogDescription class="text-gray-500 mt-1 font-medium">
              You are requesting <span class="font-bold text-gray-900">{{ selectedService?.title }}</span> from <span class="text-blue-600 font-bold">{{ selectedService?.provider_name }}</span>.
            </DialogDescription>
          </div>

          <div class="px-6 py-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
            
            <div class="bg-blue-50/50 border border-blue-100 p-4 rounded-2xl flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-sm border border-blue-100">
                  <DollarSign class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <p class="text-xs font-bold text-blue-800 uppercase tracking-wider">Estimated Cost</p>
                  <p class="text-sm font-medium text-blue-600">₱{{ selectedService?.price.toLocaleString() }} ({{ selectedService?.price_type }})</p>
                </div>
              </div>
            </div>

            <div class="space-y-2">
              <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Describe what you need</Label>
              <Textarea 
                v-model="serviceRequest.description" 
                placeholder="Give details about the area to be painted, current condition, specific colors requested..." 
                class="min-h-[100px] bg-gray-50 border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl resize-none p-4 shadow-inner"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="space-y-2">
                <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Preferred Date <span class="text-red-500">*</span></Label>
                <div class="relative">
                  <Calendar class="absolute left-3 top-3 h-5 w-5 text-gray-400 pointer-events-none" />
                  <Input 
                    type="date" 
                    v-model="serviceRequest.preferredDate" 
                    :min="minDate"
                    class="bg-gray-50 border-gray-200 pl-10 focus:border-blue-500 focus:ring-blue-500 rounded-xl h-11"
                  />
                </div>
                <p class="text-xs text-gray-500 mt-1">Must be at least 3 days from today.</p>
              </div>
              
              <div class="space-y-2">
                <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Time Preference <span class="text-red-500">*</span></Label>
                <Select v-model="serviceRequest.timePreference">
                  <SelectTrigger class="bg-gray-50 border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl h-11">
                    <div class="flex items-center">
                      <Clock class="h-4 w-4 text-gray-400 mr-2" />
                      <SelectValue placeholder="Select timeframe" />
                    </div>
                  </SelectTrigger>
                  <SelectContent class="rounded-xl">
                    <SelectItem value="Morning (8AM - 12PM)">Morning (8AM - 12PM)</SelectItem>
                    <SelectItem value="Afternoon (1PM - 5PM)">Afternoon (1PM - 5PM)</SelectItem>
                    <SelectItem value="Flexible">Flexible / Anytime</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div class="space-y-2">
              <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Contact Number <span class="text-red-500">*</span></Label>
              <div class="relative">
                <Phone class="absolute left-3 top-3 h-5 w-5 text-gray-400 pointer-events-none" />
                <Input 
                  type="tel" 
                  v-model="serviceRequest.contact" 
                  placeholder="e.g. +63 912 345 6789"
                  class="bg-gray-50 border-gray-200 pl-10 focus:border-blue-500 focus:ring-blue-500 rounded-xl h-11"
                />
              </div>
            </div>

            <div class="space-y-2">
              <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider">Complete Address <span class="text-red-500">*</span></Label>
              <div class="relative">
                <MapPin class="absolute left-3 top-4 h-5 w-5 text-gray-400 pointer-events-none" />
                <Textarea 
                  v-model="serviceRequest.address" 
                  placeholder="Street, Barangay, City, Province" 
                  class="bg-gray-50 border-gray-200 pl-10 pt-4 focus:border-blue-500 focus:ring-blue-500 rounded-xl resize-none min-h-[80px] shadow-inner"
                />
              </div>
            </div>

          </div>

          <div class="px-6 py-4 bg-white border-t border-gray-100 flex justify-end gap-3 shrink-0">
            <Button variant="outline" @click="closeServiceModal" class="border-gray-200 text-gray-600 hover:bg-gray-50 rounded-xl font-bold h-12 px-6">
              Cancel
            </Button>
            <Button @click="submitServiceRequest" :disabled="isSubmitting" class="bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/20 rounded-xl font-bold h-12 px-8 transition-all">
              <span v-if="isSubmitting" class="flex items-center">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...
              </span>
              <span v-else>Submit Request</span>
            </Button>
          </div>
        </DialogContent>
      </Dialog>
    </Teleport>

  </div>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e5e7eb;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #d1d5db;
}

input[type=date]::-webkit-calendar-picker-indicator {
  opacity: 0;
  width: 100%;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  cursor: pointer;
}
</style>