<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { 
  ArrowLeft,
  UploadCloud,
  Trash2,
  CheckCircle2,
  Briefcase,
  Quote,
  Images,
  Eye,
  Edit2,
  Award,
  Star,
  X,
  ChevronLeft,
  ChevronRight,
  Palette,
  Paintbrush,
  PaintRoller,
  Droplet
} from 'lucide-vue-next'

// Shadcn UI Components
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'

const router = useRouter()

const isLoading = ref(false)
const isSaving = ref(false)
const isPreviewMode = ref(false) // Toggle state for client view

const form = ref({
  motto: '',
  bio: '',
  experience_years: '',
  specialties: ''
})

// Image Upload States
const galleryInput = ref(null)
const selectedFiles = ref([]) // Actual files to be uploaded
const galleryPreviewUrls = ref([]) // URLs for displaying previews

// Lightbox State Variables
const isImageModalOpen = ref(false)
const currentImageIndex = ref(0)

// Vibrant gradients for the paint swatches (Used in Preview Mode)
const swatchGradients = [
  'bg-gradient-to-br from-rose-400 to-orange-500',
  'bg-gradient-to-br from-cyan-400 to-blue-600',
  'bg-gradient-to-br from-fuchsia-400 to-purple-600',
  'bg-gradient-to-br from-emerald-400 to-teal-600',
  'bg-gradient-to-br from-amber-300 to-yellow-500'
]

const getSwatchGradient = (index) => {
  return swatchGradients[index % swatchGradients.length]
}

// Computed property to neatly split specialties into an array for the preview chips
const specialtiesList = computed(() => {
  if (!form.value.specialties) return []
  return form.value.specialties.split(',').map(s => s.trim()).filter(s => s.length > 0)
})

// Generate local previews for uploaded images
const handleGalleryChange = (event) => {
  const files = event.target.files
  if (files && files.length > 0) {
    const newFiles = Array.from(files)
    
    // Add to selected files array
    selectedFiles.value = [...selectedFiles.value, ...newFiles]
    
    // Generate object URLs for immediate preview
    newFiles.forEach(file => {
      galleryPreviewUrls.value.push({
        file: file,
        url: URL.createObjectURL(file),
        isExisting: false
      })
    })
  }
}

const triggerGalleryUpload = () => {
  galleryInput.value?.click()
}

const removeImage = (index) => {
  const image = galleryPreviewUrls.value[index]
  
  if (!image.isExisting) {
    // Revoke object URL to prevent memory leaks for newly uploaded files
    URL.revokeObjectURL(image.url)
    
    // Find the corresponding file in selectedFiles and remove it
    const fileIndex = selectedFiles.value.findIndex(f => f === image.file)
    if (fileIndex !== -1) {
      selectedFiles.value.splice(fileIndex, 1)
    }
  }
  
  // Remove from preview array
  galleryPreviewUrls.value.splice(index, 1)
}

// Prevent negative years of experience
const preventInvalidChars = (e) => {
  if (['e', 'E', '+', '-'].includes(e.key)) {
    e.preventDefault()
  }
}

// Fetch existing portfolio data if any
const fetchPortfolioData = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/service-provider/portfolio')
    if (response.data.success && response.data.data) {
      const data = response.data.data
      form.value = {
        motto: data.motto || '',
        bio: data.bio || '',
        experience_years: data.experience_years || '',
        specialties: data.specialties || ''
      }
      
      // Load existing images from DB into previews
      if (data.gallery_urls && data.gallery_urls.length > 0) {
        galleryPreviewUrls.value = data.gallery_urls.map(url => ({
          url: url,
          isExisting: true
        }))
      }
    }
  } catch (error) {
    if (error.response?.status !== 404) {
       console.error("Error fetching portfolio:", error)
    }
  } finally {
    isLoading.value = false
  }
}

const savePortfolio = async () => {
  if (!form.value.motto || !form.value.bio || !form.value.experience_years) {
    toast.error('Please fill in your Motto, Bio, and Years of Experience.')
    return
  }

  isSaving.value = true
  const formData = new FormData()
  
  formData.append('motto', form.value.motto)
  formData.append('bio', form.value.bio)
  formData.append('experience_years', form.value.experience_years)
  formData.append('specialties', form.value.specialties)

  // Append new images
  selectedFiles.value.forEach((file, index) => {
    formData.append(`gallery_images[${index}]`, file)
  })

  try {
    const response = await api.post('/service-provider/portfolio', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    if (response.data.success) {
      toast.success('Portfolio successfully updated!')
      setTimeout(() => {
         router.push('/ServiceProvider/OfferJobs') 
      }, 1000)
    }
  } catch (error) {
    console.error("Error saving portfolio:", error)
    toast.error(error.response?.data?.message || 'Failed to save portfolio. Make sure backend endpoint exists.')
  } finally {
    isSaving.value = false
  }
}

const togglePreview = () => {
  if (!isPreviewMode.value && (!form.value.motto && !form.value.bio)) {
    toast.info("Add some details first to see the preview!")
    return
  }
  // Scroll to top when toggling
  window.scrollTo({ top: 0, behavior: 'smooth' })
  isPreviewMode.value = !isPreviewMode.value
}

// Lightbox Navigation Functions
const handleKeydown = (e) => {
  if (!isImageModalOpen.value) return
  if (e.key === 'Escape') closeImageModal()
  if (e.key === 'ArrowRight') nextImage()
  if (e.key === 'ArrowLeft') prevImage()
}

const openImageModal = (index) => {
  currentImageIndex.value = index
  isImageModalOpen.value = true
  document.body.style.overflow = 'hidden' // Prevent background scrolling
}

const closeImageModal = () => {
  isImageModalOpen.value = false
  document.body.style.overflow = 'auto' // Restore background scrolling
}

const nextImage = () => {
  if (galleryPreviewUrls.value.length) {
    currentImageIndex.value = (currentImageIndex.value + 1) % galleryPreviewUrls.value.length
  }
}

const prevImage = () => {
  if (galleryPreviewUrls.value.length) {
    currentImageIndex.value = currentImageIndex.value === 0 
      ? galleryPreviewUrls.value.length - 1 
      : currentImageIndex.value - 1
  }
}

onMounted(() => {
  fetchPortfolioData()
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = 'auto'
})
</script>

<template>
  <div :class="isPreviewMode ? 'min-h-screen bg-stone-50 pb-20 selection:bg-fuchsia-200 selection:text-fuchsia-900 transition-colors duration-500' : 'min-h-screen text-gray-100 p-4 md:p-8 pb-24 transition-colors duration-500'">
    
    <!-- Dynamic Header based on Mode -->
    <div :class="isPreviewMode ? 'bg-white/70 backdrop-blur-xl shadow-sm border-b border-gray-200 sticky top-0 z-30 mb-8 px-4 py-4 -mx-4 md:-mx-8' : 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8'">
      
      <div v-if="isPreviewMode" class="container mx-auto flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Button @click="togglePreview" variant="ghost" class="rounded-full h-10 w-10 p-0 text-gray-500 hover:text-gray-900 bg-white hover:bg-gray-100 shadow-sm border border-gray-200 transition-all hover:scale-105">
            <ArrowLeft class="w-5 h-5" />
          </Button>
          <div class="flex items-center gap-2">
            <Palette class="w-6 h-6 text-fuchsia-500" />
            <h1 class="text-xl font-black text-gray-900 tracking-tight uppercase">Previewing Client View</h1>
          </div>
        </div>
        <Button @click="togglePreview" variant="outline" class="bg-blue-600 border-blue-600 text-white hover:bg-blue-700 hover:text-white font-bold px-6 rounded-xl shadow-lg transition-all hover:-translate-y-0.5">
          <Edit2 class="w-4 h-4 mr-2" /> Back to Editing
        </Button>
      </div>

      <div v-else class="flex w-full justify-between items-start md:items-center">
        <div class="flex items-center gap-4">
          <Button @click="router.back()" variant="ghost" class="bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 h-10 w-10 p-0 rounded-full transition-colors shrink-0">
            <ArrowLeft class="w-5 h-5" />
          </Button>
          <div>
            <h1 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
              <Briefcase class="w-8 h-8 text-blue-500" />
              Setup Public Portfolio
            </h1>
            <p class="text-gray-400 mt-1 font-medium hidden sm:block">Advertise your skills and past projects to attract more clients.</p>
          </div>
        </div>
        <Button @click="togglePreview" variant="outline" class="border-blue-500/50 bg-gray-900 text-blue-400 hover:bg-blue-600 hover:border-blue-600 hover:text-white font-bold px-6 py-5 rounded-xl shadow-lg transition-all hover:-translate-y-0.5 shrink-0">
          <Eye class="w-5 h-5 mr-2 hidden sm:block" /> Preview as Client
        </Button>
      </div>

    </div>

    <div v-if="isLoading" class="flex flex-col justify-center items-center py-32">
        <div class="relative w-20 h-20 mb-6">
           <div class="absolute inset-0 rounded-full border-4 border-stone-200"></div>
           <div class="absolute inset-0 rounded-full border-4 border-fuchsia-500 border-t-transparent animate-spin"></div>
           <Paintbrush class="w-8 h-8 text-fuchsia-500 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse" />
        </div>
        <h3 class="text-xl font-bold" :class="isPreviewMode ? 'text-gray-900' : 'text-gray-100'">Mixing colors...</h3>
    </div>

    <!-- SETUP MODE -->
    <div v-else-if="!isPreviewMode" class="grid grid-cols-1 lg:grid-cols-12 gap-8 animate-in fade-in zoom-in-95 duration-300">
      
      <!-- Left Column: Form Fields -->
      <div class="lg:col-span-7 space-y-6">
        <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 shadow-xl overflow-hidden rounded-2xl">
          <div class="bg-gray-800/80 px-6 py-4 border-b border-gray-800">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              <Quote class="w-5 h-5 text-blue-400" />
              General Advertisement
            </h2>
          </div>
          <CardContent class="p-6 space-y-6">
            
            <div class="space-y-2">
              <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Your Professional Motto / Tagline</Label>
              <Input 
                v-model="form.motto" 
                placeholder="e.g. Bringing color to your life, one wall at a time." 
                class="bg-gray-800 border-gray-600 text-white focus:ring-2 focus:ring-blue-500 rounded-xl placeholder:text-gray-500 h-12 text-base" 
              />
              <p class="text-xs text-gray-500 mt-1">A short, catchy phrase that clients will see first.</p>
            </div>

            <div class="space-y-2">
              <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">About Me / Professional Bio</Label>
              <Textarea 
                v-model="form.bio" 
                placeholder="Tell clients about your background, your work ethic, and why they should hire you..." 
                class="min-h-[140px] bg-gray-800 border-gray-600 text-white focus:ring-2 focus:ring-blue-500 resize-none rounded-xl placeholder:text-gray-500" 
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Years of Experience</Label>
                <div class="relative">
                   <Input 
                     v-model="form.experience_years" 
                     type="number" 
                     min="0"
                     @keydown="preventInvalidChars"
                     placeholder="e.g. 5" 
                     class="bg-gray-800 border-gray-600 text-white focus:ring-2 focus:ring-blue-500 rounded-xl placeholder:text-gray-500 h-11" 
                   />
                   <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none text-gray-500 font-medium">
                     Years
                   </div>
                </div>
              </div>

              <div class="space-y-2">
                <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Key Specialties (Comma Separated)</Label>
                <Input 
                  v-model="form.specialties" 
                  placeholder="e.g. Exterior, Epoxy, Wood Varnish" 
                  class="bg-gray-800 border-gray-600 text-white focus:ring-2 focus:ring-blue-500 rounded-xl placeholder:text-gray-500 h-11" 
                />
              </div>
            </div>

          </CardContent>
        </Card>
      </div>

      <!-- Right Column: Gallery Upload -->
      <div class="lg:col-span-5 space-y-6">
        <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 shadow-xl overflow-hidden rounded-2xl h-full flex flex-col">
          <div class="bg-gray-800/80 px-6 py-4 border-b border-gray-800 flex justify-between items-center">
            <h2 class="text-lg font-bold text-white flex items-center gap-2">
              <Images class="w-5 h-5 text-blue-400" />
              Work Gallery
            </h2>
            <Badge class="bg-gray-700 text-gray-300 border-0">{{ galleryPreviewUrls.length }} Photos</Badge>
          </div>
          <CardContent class="p-6 flex-1 flex flex-col">
            
            <p class="text-sm text-gray-400 mb-4">
              Upload high-quality images of your past painting projects. Visual evidence is the strongest tool to get hired!
            </p>

            <div @click="triggerGalleryUpload" class="w-full h-32 border-2 border-dashed border-gray-600 rounded-xl bg-gray-800 flex flex-col items-center justify-center text-gray-400 cursor-pointer hover:border-blue-500 hover:bg-blue-500/10 hover:text-blue-400 transition-colors shadow-inner mb-6 shrink-0">
              <input type="file" multiple ref="galleryInput" @change="handleGalleryChange" accept="image/*" class="hidden" />
              <UploadCloud class="w-8 h-8 mb-2" />
              <span class="text-sm font-bold uppercase tracking-wider">Click to Upload Work Photos</span>
            </div>

            <!-- Previews -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 overflow-y-auto custom-scrollbar flex-1 pb-2">
               <div v-for="(preview, index) in galleryPreviewUrls" :key="index" class="relative group aspect-square rounded-xl overflow-hidden border border-gray-700 bg-gray-800">
                  <img :src="preview.url" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
                  <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                     <button @click.stop="removeImage(index)" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full shadow-lg transform scale-90 group-hover:scale-100 transition-all">
                        <Trash2 class="w-4 h-4" />
                     </button>
                  </div>
               </div>
               
               <div v-if="galleryPreviewUrls.length === 0" class="col-span-full flex flex-col items-center justify-center py-10 opacity-50">
                  <Images class="w-12 h-12 mb-3 text-gray-600" />
                  <p class="text-sm font-medium">Your gallery is currently empty.</p>
               </div>
            </div>

          </CardContent>
        </Card>
      </div>
    </div>

    <!-- PREVIEW MODE (ARTISAN CLIENT VIEW) -->
    <div v-else class="container mx-auto px-4 py-8 space-y-12 animate-in fade-in slide-in-from-bottom-8 duration-700 max-w-6xl">
      
      <!-- Vivid Hero Advertisement Section -->
      <div class="relative bg-slate-900 rounded-[3rem] p-8 md:p-12 lg:p-20 overflow-hidden shadow-2xl border-4 border-white">
        <!-- Abstract Paint Splatter/Glow Effects -->
        <div class="absolute -top-32 -right-32 w-[30rem] h-[30rem] bg-fuchsia-600/40 rounded-full blur-[100px] mix-blend-screen pointer-events-none animate-pulse duration-10000"></div>
        <div class="absolute -bottom-32 -left-32 w-[30rem] h-[30rem] bg-cyan-500/40 rounded-full blur-[100px] mix-blend-screen pointer-events-none animate-pulse duration-7000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40rem] h-[20rem] bg-yellow-400/20 rounded-full blur-[120px] mix-blend-screen pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col items-center text-center gap-8">
          <div class="flex flex-col items-center gap-4">
             <div class="w-20 h-20 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-md border border-white/30 shadow-[0_0_30px_rgba(217,70,239,0.3)]">
                <PaintRoller class="w-10 h-10 text-white" />
             </div>
             <div>
                <h2 class="text-2xl md:text-3xl font-black text-white tracking-wide uppercase">Your Business Name</h2>
                <div class="flex items-center justify-center gap-1.5 mt-1 text-cyan-300">
                  <Star class="w-4 h-4 fill-cyan-300" />
                  <span class="text-sm font-bold tracking-widest uppercase">Master Painter</span>
                </div>
             </div>
          </div>
          
          <h1 class="text-4xl md:text-6xl lg:text-7xl font-black leading-tight max-w-4xl mx-auto">
            <span class="bg-gradient-to-r from-rose-400 via-fuchsia-400 to-cyan-400 text-transparent bg-clip-text drop-shadow-sm">
              "{{ form.motto || 'Transforming spaces into living masterpieces.' }}"
            </span>
          </h1>
          
          <!-- Paint Swatch Specialties -->
          <div class="flex flex-wrap justify-center items-center gap-3 mt-6 max-w-3xl">
            <div v-if="form.experience_years" class="flex items-center gap-2 bg-white/10 border border-white/20 text-white px-5 py-2.5 rounded-full font-bold shadow-xl backdrop-blur-md hover:bg-white/20 transition-colors">
              <Award class="w-5 h-5 text-yellow-400" />
              {{ form.experience_years }} Years Crafting
            </div>
            
            <div 
              v-for="(specialty, index) in specialtiesList" 
              :key="specialty" 
              class="flex items-center gap-2.5 bg-slate-800/80 border border-slate-600 hover:border-slate-400 text-slate-200 px-5 py-2.5 rounded-full text-sm font-bold shadow-xl backdrop-blur-md transition-all hover:-translate-y-1 cursor-default"
            >
              <!-- Dynamic Paint Drop Color Indicator -->
              <div class="w-3.5 h-3.5 rounded-full shadow-inner border border-white/20" :class="getSwatchGradient(index)"></div>
              {{ specialty }}
            </div>
          </div>
        </div>
      </div>

      <!-- About Canvas Section -->
      <div class="relative bg-white rounded-[2.5rem] p-8 md:p-12 shadow-[0_20px_50px_-12px_rgba(0,0,0,0.05)] border border-stone-200 overflow-hidden">
        <!-- Decorative Paint Stroke on the edge -->
        <div class="absolute left-0 top-0 bottom-0 w-3 bg-gradient-to-b from-rose-400 via-fuchsia-500 to-cyan-500"></div>
        
        <h2 class="text-3xl font-black text-slate-900 mb-6 flex items-center gap-3 ml-4">
          <Quote class="w-8 h-8 text-fuchsia-500 opacity-50" />
          The Artist's Background
        </h2>
        <p class="text-lg md:text-xl text-slate-600 leading-relaxed whitespace-pre-wrap font-medium ml-4 max-w-4xl">
          {{ form.bio || 'Your detailed bio and background will be displayed here for clients to read.' }}
        </p>
      </div>

      <!-- Portfolio Masonry Gallery Display -->
      <div>
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 px-2 gap-4">
          <h2 class="text-3xl font-black text-slate-900 flex items-center gap-3">
            <Images class="w-8 h-8 text-cyan-500" />
            Masterpiece Gallery
          </h2>
          <p class="text-slate-500 font-medium" v-if="galleryPreviewUrls.length > 0">Click any canvas to view full size.</p>
        </div>
        
        <div v-if="galleryPreviewUrls.length > 0" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
          <div 
             v-for="(preview, index) in galleryPreviewUrls" 
             :key="index" 
             @click="openImageModal(index)"
             class="group relative rounded-2xl overflow-hidden shadow-lg border-4 border-white cursor-pointer break-inside-avoid bg-slate-100"
          >
            <img :src="preview.url" class="w-full h-auto object-cover transition-transform duration-1000 group-hover:scale-105" loading="lazy" />
            
            <!-- Vibrant Hover Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end p-6">
              <div class="w-full flex justify-between items-center transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                <span class="text-white font-black bg-fuchsia-600/90 backdrop-blur-md px-4 py-1.5 rounded-full text-xs tracking-widest uppercase border border-white/20 shadow-lg">
                  Canvas {{ index + 1 }}
                </span>
                <div class="bg-white/20 backdrop-blur-md p-2 rounded-full border border-white/30 text-white">
                  <Eye class="w-5 h-5" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-24 bg-white/50 backdrop-blur-sm rounded-[3rem] border-2 border-stone-200 border-dashed shadow-sm">
          <div class="w-24 h-24 bg-stone-100 rounded-full flex items-center justify-center mb-6 shadow-inner">
            <Droplet class="w-10 h-10 text-stone-400" />
          </div>
          <h3 class="text-2xl font-black text-slate-800 mb-2">A Blank Canvas</h3>
          <p class="text-slate-500 text-center max-w-md text-lg">You haven't uploaded any past project photos yet.</p>
        </div>
      </div>
    </div>

    <!-- FULL-SCREEN IMAGE MODAL LIGHTBOX -->
    <Teleport to="body">
      <transition 
        enter-active-class="transition duration-300 ease-out" 
        enter-from-class="opacity-0" 
        enter-to-class="opacity-100" 
        leave-active-class="transition duration-200 ease-in" 
        leave-from-class="opacity-100" 
        leave-to-class="opacity-0"
      >
        <div v-if="isImageModalOpen" class="fixed inset-0 z-[10000] flex items-center justify-center bg-slate-950/95 backdrop-blur-xl" @click="closeImageModal">
          
          <!-- Close Button -->
          <button @click.stop="closeImageModal" class="absolute top-6 right-6 text-white/50 hover:text-white bg-white/5 hover:bg-white/20 p-3 rounded-full transition-all z-50 border border-white/10 hover:border-white/30">
            <X class="w-6 h-6" />
          </button>
          
          <!-- Left Arrow Navigation -->
          <button 
             v-if="galleryPreviewUrls?.length > 1"
             @click.stop="prevImage" 
             class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white bg-white/5 hover:bg-white/20 p-4 rounded-full transition-all z-50 border border-white/10 hover:border-white/30 hover:-translate-x-1"
          >
            <ChevronLeft class="w-8 h-8" />
          </button>

          <!-- Current Image -->
          <img 
             :src="galleryPreviewUrls[currentImageIndex]?.url" 
             class="max-w-[90vw] max-h-[85vh] object-contain shadow-[0_0_50px_rgba(0,0,0,0.5)] animate-in zoom-in-95 duration-500 rounded-xl border-2 border-white/10" 
             @click.stop 
          />

          <!-- Right Arrow Navigation -->
          <button 
             v-if="galleryPreviewUrls?.length > 1"
             @click.stop="nextImage" 
             class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white bg-white/5 hover:bg-white/20 p-4 rounded-full transition-all z-50 border border-white/10 hover:border-white/30 hover:translate-x-1"
          >
            <ChevronRight class="w-8 h-8" />
          </button>

          <!-- Image Counter -->
          <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
            <div class="text-white font-black tracking-widest text-sm bg-black/40 px-5 py-2 rounded-full backdrop-blur-md border border-white/10">
              CANVAS {{ currentImageIndex + 1 }} OF {{ galleryPreviewUrls.length }}
            </div>
            
            <!-- Thumbnail Navigation Dots -->
            <div class="flex gap-2 mt-2">
              <button 
                v-for="(_, idx) in galleryPreviewUrls" 
                :key="'dot-'+idx"
                @click.stop="currentImageIndex = idx"
                class="w-2 h-2 rounded-full transition-all duration-300"
                :class="currentImageIndex === idx ? 'bg-fuchsia-500 w-6' : 'bg-white/30 hover:bg-white/60'"
              ></button>
            </div>
          </div>

        </div>
      </transition>
    </Teleport>

    <!-- Fixed Bottom Save Bar (Hidden in Preview Mode) -->
    <div v-if="!isPreviewMode" class="fixed bottom-0 left-0 right-0 bg-gray-900 border-t border-gray-800 p-4 z-40 shadow-[0_-10px_30px_-15px_rgba(0,0,0,0.5)]">
       <div class="container mx-auto flex justify-end gap-4 px-4 md:px-8">
          <Button @click="router.back()" variant="outline" class="border-gray-600 text-gray-200 bg-gray-800 hover:bg-gray-700 hover:text-white rounded-xl font-bold h-12 px-6">
            Cancel
          </Button>
          <Button @click="savePortfolio" :disabled="isSaving || !form.motto || !form.bio" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-lg shadow-blue-900/20 font-bold h-12 px-8">
            <CheckCircle2 v-if="!isSaving" class="w-5 h-5 mr-2" />
            <div v-else class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            {{ isSaving ? 'Saving Portfolio...' : 'Save & Publish Portfolio' }}
          </Button>
       </div>
    </div>

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

/* Remove default number input spinners */
input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button { 
  -webkit-appearance: none; 
  margin: 0; 
}
input[type=number] {
  -moz-appearance: textfield;
}

/* Masonry Gallery Setup */
.columns-1 { columns: 1; }
@media (min-width: 640px) { .sm\:columns-2 { columns: 2; } }
@media (min-width: 1024px) { .lg\:columns-3 { columns: 3; } }
.break-inside-avoid { break-inside: avoid; }
</style>