<script setup>
import { ref, onMounted, computed } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { 
  Plus, 
  Edit, 
  Trash2, 
  Image as ImageIcon, 
  Clock, 
  Briefcase,
  PaintRoller,
  UploadCloud
} from 'lucide-vue-next'

// Shadcn UI Components
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Switch } from '@/components/ui/switch'
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

// --- State ---
const services = ref([])
const isLoading = ref(true)
const isSubmitting = ref(false)

const isModalOpen = ref(false)
const isEditing = ref(false)
const currentEditId = ref(null)

// File Upload State
const fileInput = ref(null)
const selectedFiles = ref([])

// Confirmation Dialog States
const deleteAlert = ref({ isOpen: false, id: null })
const toggleAlert = ref({ isOpen: false, service: null })

const form = ref({
  title: '',
  category: '',
  price: '',
  priceType: 'Base Rate',
  duration: '',
  description: '',
  active: true
})

const categories = ['Interior', 'Exterior', 'Commercial', 'Specialty', 'Maintenance']

// --- Methods ---

// FIX: Robust Dynamic Image URL Generator
const getImageUrl = (path) => {
  if (!path) return '';
  
  // 1. Get the true base URL from .env, fallback to localhost
  const baseUrl = import.meta.env.VITE_API_URL 
      ? import.meta.env.VITE_API_URL.replace('/api', '') 
      : 'http://localhost:8000';
  
  // 2. Fix old DB records that accidentally hardcoded localhost:8000
  if (path.includes('localhost:8000')) {
      path = path.replace('http://localhost:8000', baseUrl);
  }
  
  // 3. If it's already a full HTTP URL, return it directly
  if (path.startsWith('http')) return path;
  
  // 4. Handle new DB records that correctly only saved the relative path ('service_offerings/xyz.jpg')
  const cleanPath = path.startsWith('storage/') ? path.replace('storage/', '') : path;
  return `${baseUrl}/storage/${cleanPath}`;
}

const fetchServices = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/service-provider/services')
    if (response.data.success) {
      services.value = response.data.data
    }
  } catch (error) {
    console.error("Error fetching services:", error)
    toast.error('Failed to load services')
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchServices()
})

const openAddModal = () => {
  isEditing.value = false
  currentEditId.value = null
  selectedFiles.value = []
  form.value = {
    title: '',
    category: '',
    price: '',
    priceType: 'Base Rate',
    duration: '',
    description: '',
    active: true
  }
  isModalOpen.value = true
}

const openEditModal = (service) => {
  isEditing.value = true
  currentEditId.value = service.id
  selectedFiles.value = []
  
  form.value = { 
    title: service.title,
    category: service.category,
    price: service.price.toString().replace(/,/g, ''),
    priceType: service.price_type,
    duration: service.duration,
    description: service.description,
    active: service.is_active
  }
  isModalOpen.value = true
}

const handleFileChange = (event) => {
  const files = event.target.files
  if (files && files.length > 0) {
    selectedFiles.value = Array.from(files)
  }
}

const triggerFileInput = () => {
  fileInput.value?.click()
}

// Block mathematical symbols & negative signs on keydown
const preventInvalidChars = (e) => {
  if (['e', 'E', '+', '-'].includes(e.key)) {
    e.preventDefault()
  }
}

const saveService = async () => {
  if (!form.value.title || !form.value.category || !form.value.price) {
    toast.error('Please fill in all required fields.')
    return
  }

  if (Number(form.value.price) < 0) {
    toast.error('Price cannot be a negative value.')
    return
  }

  isSubmitting.value = true

  const formData = new FormData()
  formData.append('title', form.value.title)
  formData.append('category', form.value.category)
  formData.append('price', form.value.price)
  formData.append('price_type', form.value.priceType)
  formData.append('duration', form.value.duration)
  formData.append('description', form.value.description)
  formData.append('is_active', form.value.active ? 1 : 0)
  
  // Append images if selected
  selectedFiles.value.forEach((file, index) => {
    formData.append(`images[${index}]`, file)
  })

  try {
    if (isEditing.value) {
      const response = await api.post(`/service-provider/services/${currentEditId.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      if (response.data.success) {
        toast.success('Service updated successfully!')
      }
    } else {
      const response = await api.post('/service-provider/services', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      if (response.data.success) {
        toast.success('New service posted successfully!')
      }
    }
    isModalOpen.value = false
    fetchServices() // Refresh the list
  } catch (error) {
    console.error("Save error:", error)
    toast.error(error.response?.data?.message || 'Failed to save service')
  } finally {
    isSubmitting.value = false
  }
}

// Delete Logic
const openDeleteConfirm = (id) => {
  deleteAlert.value = { isOpen: true, id }
}

const proceedDelete = async () => {
  try {
    const response = await api.delete(`/service-provider/services/${deleteAlert.value.id}`)
    if (response.data.success) {
      services.value = services.value.filter(s => s.id !== deleteAlert.value.id)
      toast.success('Service removed from your offerings.')
    }
  } catch (error) {
    toast.error('Failed to delete service')
  } finally {
    deleteAlert.value.isOpen = false
  }
}

// Toggle Visibility Logic
const openToggleConfirm = (service) => {
  toggleAlert.value = { isOpen: true, service }
}

const proceedToggle = async () => {
  const service = toggleAlert.value.service
  if (service) {
    try {
      const response = await api.patch(`/service-provider/services/${service.id}/toggle`)
      if (response.data.success) {
        service.is_active = !service.is_active
        const statusText = service.is_active ? 'published and visible' : 'hidden from clients'
        toast.success(`Service is now ${statusText}.`)
      }
    } catch (error) {
      toast.error('Failed to update status')
    }
  }
  toggleAlert.value.isOpen = false
}
</script>

<template>
  <div class="min-h-screen text-gray-100 p-4 md:p-8">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
          <Briefcase class="w-8 h-8 text-blue-500" />
          My Service Offerings
        </h1>
        <p class="text-gray-400 mt-2 font-medium">Manage and publish the painting services you offer to clients.</p>
      </div>
      
      <Button @click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-6 rounded-xl shadow-lg shadow-blue-900/20 transition-all hover:-translate-y-0.5">
        <Plus class="w-5 h-5 mr-2" />
        Post New Service
      </Button>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="services.length === 0" class="flex flex-col items-center justify-center py-20 bg-gray-800/30 rounded-3xl border border-gray-800 border-dashed">
      <div class="w-20 h-20 bg-gray-800 rounded-full flex items-center justify-center mb-4 text-gray-500 shadow-inner">
        <PaintRoller class="w-10 h-10" />
      </div>
      <h3 class="text-xl font-bold text-white mb-2">No Services Posted Yet</h3>
      <p class="text-gray-400 mb-6 text-center max-w-md">You haven't listed any painting services. Post your first service to start getting booked by clients.</p>
      <Button @click="openAddModal" variant="outline" class="border-gray-600 text-gray-100 bg-gray-800 hover:text-white hover:bg-gray-700 rounded-xl font-bold">
        Create First Service
      </Button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card 
        v-for="service in services" 
        :key="service.id" 
        class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden hover:border-gray-700 transition-all duration-300 group flex flex-col shadow-lg"
        :class="!service.is_active ? 'opacity-75 grayscale-[30%]' : ''"
      >
        <div class="h-44 bg-gray-800 relative overflow-hidden flex items-center justify-center">
          <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent z-10"></div>
          
          <img v-if="service.image_paths && service.image_paths.length > 0" :src="getImageUrl(service.image_paths[0])" class="w-full h-full object-cover z-0" />
          <ImageIcon v-else class="w-12 h-12 text-gray-600" />
          
          <div class="absolute top-3 left-3 z-20 flex gap-2">
            <Badge class="bg-blue-500/90 text-white border-0 backdrop-blur-md font-bold shadow-sm">
              {{ service.category }}
            </Badge>
            <Badge :class="service.is_active ? 'bg-emerald-500/90 text-white' : 'bg-gray-600/90 text-gray-200'" class="border-0 backdrop-blur-md font-bold shadow-sm">
              {{ service.is_active ? 'Active' : 'Hidden' }}
            </Badge>
          </div>
        </div>

        <CardContent class="p-6 flex-1 flex flex-col">
          <h3 class="text-xl font-bold text-white mb-3 line-clamp-1 group-hover:text-blue-400 transition-colors">{{ service.title }}</h3>
          
          <div class="flex items-center gap-4 mb-4 text-sm text-gray-300 font-medium bg-gray-800 p-3 rounded-xl border border-gray-700 shadow-inner">
            <div class="flex items-center gap-1.5">
              <span class="text-emerald-400 font-bold text-base leading-none">₱</span>
              <span class="text-white font-bold text-base">{{ Number(service.price).toLocaleString() }}</span>
              <span class="text-[10px] text-gray-400 uppercase tracking-wider">({{ service.price_type }})</span>
            </div>
            <div class="h-4 w-px bg-gray-600"></div>
            <div class="flex items-center gap-1.5 text-gray-300">
              <Clock class="w-4 h-4 text-amber-400" />
              <span>{{ service.duration }}</span>
            </div>
          </div>

          <p class="text-sm text-gray-400 leading-relaxed line-clamp-3 mb-4 flex-1 whitespace-pre-wrap">
            {{ service.description }}
          </p>
        </CardContent>

        <CardFooter class="p-5 bg-gray-900/90 border-t border-gray-800 flex justify-between items-center">
          <div class="flex items-center gap-3 cursor-pointer bg-gray-800 px-3 py-2 rounded-lg border border-gray-700 hover:border-gray-500 transition-colors" @click.stop="openToggleConfirm(service)">
            <Switch :checked="service.is_active" class="pointer-events-none" />
            <span class="text-xs font-bold uppercase tracking-wider" :class="service.is_active ? 'text-gray-100' : 'text-gray-400'">
              {{ service.is_active ? 'Visible' : 'Hidden' }}
            </span>
          </div>

          <div class="flex gap-2">
            <Button variant="outline" size="sm" @click.stop="openEditModal(service)" class="border-gray-600 bg-gray-800 text-gray-100 hover:text-blue-400 hover:border-blue-500 hover:bg-gray-700 rounded-lg h-10 w-10 p-0 shadow-sm transition-all">
              <Edit class="w-4 h-4" />
            </Button>
            <Button variant="outline" size="sm" @click.stop="openDeleteConfirm(service.id)" class="border-gray-600 bg-gray-800 text-gray-100 hover:text-red-400 hover:border-red-500 hover:bg-gray-700 rounded-lg h-10 w-10 p-0 shadow-sm transition-all">
              <Trash2 class="w-4 h-4" />
            </Button>
          </div>
        </CardFooter>
      </Card>
    </div>

    <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
      <DialogContent class="sm:max-w-[600px] bg-gray-900 border-gray-800 text-gray-100 rounded-2xl shadow-2xl p-0 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-800 bg-gray-900/50">
          <DialogTitle class="text-xl font-bold text-white flex items-center gap-2">
            <PaintRoller class="w-5 h-5 text-blue-500" />
            {{ isEditing ? 'Edit Service Details' : 'Post a New Service' }}
          </DialogTitle>
          <DialogDescription class="text-gray-400 mt-1 font-medium">
            Fill in the details below to showcase this service to potential clients.
          </DialogDescription>
        </div>

        <div class="px-6 py-6 space-y-5 max-h-[70vh] overflow-y-auto custom-scrollbar">
          
          <div @click="triggerFileInput" class="w-full h-32 border-2 border-dashed border-gray-600 rounded-xl bg-gray-800 flex flex-col items-center justify-center text-gray-300 cursor-pointer hover:border-blue-500 hover:bg-blue-500/10 hover:text-blue-400 transition-colors shadow-inner relative overflow-hidden">
            <input type="file" multiple ref="fileInput" @change="handleFileChange" accept="image/*" class="hidden" />
            <UploadCloud class="w-8 h-8 mb-2" v-if="selectedFiles.length === 0" />
            <span class="text-sm font-bold uppercase tracking-wider">
              {{ selectedFiles.length > 0 ? `${selectedFiles.length} Images Selected` : 'Click to upload cover images' }}
            </span>
            <p v-if="selectedFiles.length > 0" class="text-xs text-blue-400 mt-1">Click again to re-select</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="space-y-2">
              <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Service Title</Label>
              <Input v-model="form.title" placeholder="e.g. Interior Wall Painting" class="bg-gray-800 border-gray-600 text-white focus:ring-2 focus:ring-blue-500 rounded-xl placeholder:text-gray-500" />
            </div>
            <div class="space-y-2">
              <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Category</Label>
              <Select v-model="form.category">
                <SelectTrigger class="bg-gray-800 border-gray-600 text-white rounded-xl">
                  <SelectValue placeholder="Select Category" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white rounded-xl">
                  <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="space-y-2">
              <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Rate / Price</Label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400 font-bold">₱</span>
                <Input 
                  v-model="form.price" 
                  type="number" 
                  min="0"
                  step="0.01"
                  @keydown="preventInvalidChars"
                  placeholder="0.00" 
                  class="bg-gray-800 border-gray-600 text-white pl-8 focus:ring-2 focus:ring-blue-500 rounded-xl placeholder:text-gray-500" 
                />
              </div>
            </div>
            <div class="space-y-2">
              <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Pricing Type</Label>
              <Select v-model="form.priceType">
                <SelectTrigger class="bg-gray-800 border-gray-600 text-white rounded-xl">
                  <SelectValue placeholder="Type" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white rounded-xl">
                  <SelectItem value="Base Rate">Base Rate</SelectItem>
                  <SelectItem value="Starting Price">Starting Price</SelectItem>
                  <SelectItem value="Per Sqm">Per Sqm</SelectItem>
                  <SelectItem value="Fixed Price">Fixed Price</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="space-y-2">
              <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Est. Duration</Label>
              <Input v-model="form.duration" placeholder="e.g. 2-3 Days" class="bg-gray-800 border-gray-600 text-white focus:ring-2 focus:ring-blue-500 rounded-xl placeholder:text-gray-500" />
            </div>
          </div>

          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Service Description</Label>
            <Textarea 
              v-model="form.description" 
              placeholder="Describe what is included in this service, your process, and why clients should choose you..." 
              class="h-28 bg-gray-800 border-gray-600 text-white focus:ring-2 focus:ring-blue-500 resize-none rounded-xl placeholder:text-gray-500" 
            />
          </div>

          <div class="flex items-center justify-between p-4 bg-gray-800 rounded-xl border border-gray-600 shadow-sm">
            <div>
              <Label class="text-white font-bold block mb-0.5">Publish Immediately</Label>
              <span class="text-xs text-gray-400 font-medium">Make this service visible to clients right away.</span>
            </div>
            <Switch v-model="form.active" />
          </div>

        </div>

        <div class="px-6 py-4 bg-gray-900 border-t border-gray-800 flex justify-end gap-3">
          <Button variant="outline" @click="isModalOpen = false" class="border-gray-600 text-gray-200 bg-gray-800 hover:bg-gray-700 hover:text-white rounded-xl font-bold shadow-sm">
            Cancel
          </Button>
          <Button @click="saveService" :disabled="!form.title || !form.category || !form.price || isSubmitting" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-900/20 font-bold px-6">
            {{ isSubmitting ? 'Saving...' : (isEditing ? 'Save Changes' : 'Post Service') }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="deleteAlert.isOpen" @update:open="deleteAlert.isOpen = $event">
      <AlertDialogContent class="bg-gray-900 border-gray-800 text-white rounded-2xl shadow-2xl">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
            <Trash2 class="w-5 h-5 text-red-500" />
            Delete Service
          </AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400 text-base mt-2">
            Are you sure you want to permanently remove this service? This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-6">
          <AlertDialogCancel class="rounded-xl font-bold border-gray-600 text-gray-200 bg-gray-800 hover:bg-gray-700 hover:text-white h-11">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction @click="proceedDelete" class="rounded-xl font-bold bg-red-600 hover:bg-red-700 text-white h-11 border-0">
            Yes, Delete
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <AlertDialog :open="toggleAlert.isOpen" @update:open="toggleAlert.isOpen = $event">
      <AlertDialogContent class="bg-gray-900 border-gray-800 text-white rounded-2xl shadow-2xl">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-xl font-bold">Change Visibility</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400 text-base mt-2">
            You are about to <strong class="text-white">{{ toggleAlert.service?.is_active ? 'hide' : 'publish' }}</strong> the service 
            "<span class="text-gray-200 italic">{{ toggleAlert.service?.title }}</span>". 
            <br/><br/>
            {{ toggleAlert.service?.is_active ? 'Clients will no longer be able to see or book this service.' : 'Clients will now be able to see and book this service on your profile.' }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-6">
          <AlertDialogCancel class="rounded-xl font-bold border-gray-600 text-gray-200 bg-gray-800 hover:bg-gray-700 hover:text-white h-11">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction @click="proceedToggle" class="rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white h-11 border-0">
            Confirm Change
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #4b5563; 
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #6b7280; 
}

/* Remove default number input spinners for cleaner look */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>