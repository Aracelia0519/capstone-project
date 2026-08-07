<template>
  <div class="min-h-screen  pb-20 selection:bg-fuchsia-200 selection:text-fuchsia-900">
    
    <!-- Top Navigation Bar -->
    <div class="bg-white/70 backdrop-blur-xl shadow-sm border-b border-gray-200 sticky top-0 z-30">
      <div class="container mx-auto px-4 py-4 flex items-center gap-4">
        <Button @click="router.back()" variant="ghost" class="rounded-full h-10 w-10 p-0 text-gray-500 hover:text-gray-900 bg-white hover:bg-gray-100 shadow-sm border border-gray-200 transition-all hover:scale-105">
          <ArrowLeft class="w-5 h-5" />
        </Button>
        <div class="flex items-center gap-2">
          <Palette class="w-6 h-6 text-fuchsia-500" />
          <h1 class="text-xl font-black text-gray-900 tracking-tight uppercase">Artisan Profile</h1>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
        <div class="relative w-20 h-20 mb-6">
           <div class="absolute inset-0 rounded-full border-4 border-stone-200"></div>
           <div class="absolute inset-0 rounded-full border-4 border-fuchsia-500 border-t-transparent animate-spin"></div>
           <Paintbrush class="w-8 h-8 text-fuchsia-500 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 animate-pulse" />
        </div>
        <h3 class="text-xl font-bold text-gray-900">Mixing colors...</h3>
    </div>

    <!-- Profile Content -->
    <div v-else-if="provider" class="container mx-auto px-4 py-8 space-y-12 animate-in fade-in slide-in-from-bottom-8 duration-700 max-w-6xl">
      
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
                <h2 class="text-2xl md:text-3xl font-black text-white tracking-wide uppercase">{{ provider.provider_name }}</h2>
                <div class="flex items-center justify-center gap-1.5 mt-1 text-cyan-300">
                  <Star class="w-4 h-4 fill-cyan-300" />
                  <span class="text-sm font-bold tracking-widest uppercase">Master Painter</span>
                </div>
             </div>
          </div>
          
          <h1 class="text-4xl md:text-6xl lg:text-7xl font-black leading-tight max-w-4xl mx-auto">
            <span class="bg-gradient-to-r from-rose-400 via-fuchsia-400 to-cyan-400 text-transparent bg-clip-text drop-shadow-sm">
              "{{ provider.motto || 'Transforming spaces into living masterpieces.' }}"
            </span>
          </h1>
          
          <!-- Paint Swatch Specialties -->
          <div class="flex flex-wrap justify-center items-center gap-3 mt-6 max-w-3xl">
            <div v-if="provider.experience_years" class="flex items-center gap-2 bg-white/10 border border-white/20 text-white px-5 py-2.5 rounded-full font-bold shadow-xl backdrop-blur-md hover:bg-white/20 transition-colors">
              <Award class="w-5 h-5 text-yellow-400" />
              {{ provider.experience_years }} Years Crafting
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
          {{ provider.bio || 'This professional is currently setting up their bio. Rest assured, they are verified and ready to bring vibrant colors to your next project.' }}
        </p>
      </div>

      <!-- Portfolio Masonry Gallery Display -->
      <div>
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 px-2 gap-4">
          <h2 class="text-3xl font-black text-slate-900 flex items-center gap-3">
            <Images class="w-8 h-8 text-cyan-500" />
            Masterpiece Gallery
          </h2>
          <p class="text-slate-500 font-medium">Click any canvas to view full size.</p>
        </div>
        
        <div v-if="provider.gallery_urls && provider.gallery_urls.length > 0" class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-6">
          <div 
             v-for="(url, index) in provider.gallery_urls" 
             :key="index" 
             @click="openImageModal(index)"
             class="group relative rounded-2xl overflow-hidden shadow-lg border-4 border-white cursor-pointer break-inside-avoid bg-slate-100"
          >
            <img :src="url" class="w-full h-auto object-cover transition-transform duration-1000 group-hover:scale-105" loading="lazy" />
            
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
          <p class="text-slate-500 text-center max-w-md text-lg">This professional hasn't uploaded their past project photos yet.</p>
        </div>
      </div>
    </div>

    <!-- Fallback if not found -->
    <div v-else class="flex flex-col items-center justify-center py-32 text-center px-4">
        <Paintbrush class="w-20 h-20 text-stone-300 mb-6" />
        <h3 class="text-3xl font-black text-slate-900 mb-3">Profile Not Found</h3>
        <p class="text-slate-500 mb-8 text-lg max-w-md">We could not locate this artist's profile, or it may have been removed from the gallery.</p>
        <Button @click="router.back()" class="bg-slate-900 hover:bg-slate-800 text-white shadow-xl px-8 py-6 rounded-full font-bold text-lg">Return to Gallery</Button>
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
             v-if="provider?.gallery_urls?.length > 1"
             @click.stop="prevImage" 
             class="absolute left-4 md:left-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white bg-white/5 hover:bg-white/20 p-4 rounded-full transition-all z-50 border border-white/10 hover:border-white/30 hover:-translate-x-1"
          >
            <ChevronLeft class="w-8 h-8" />
          </button>

          <!-- Current Image -->
          <img 
             :src="provider.gallery_urls[currentImageIndex]" 
             class="max-w-[90vw] max-h-[85vh] object-contain shadow-[0_0_50px_rgba(0,0,0,0.5)] animate-in zoom-in-95 duration-500 rounded-xl border-2 border-white/10" 
             @click.stop 
          />

          <!-- Right Arrow Navigation -->
          <button 
             v-if="provider?.gallery_urls?.length > 1"
             @click.stop="nextImage" 
             class="absolute right-4 md:right-8 top-1/2 -translate-y-1/2 text-white/50 hover:text-white bg-white/5 hover:bg-white/20 p-4 rounded-full transition-all z-50 border border-white/10 hover:border-white/30 hover:translate-x-1"
          >
            <ChevronRight class="w-8 h-8" />
          </button>

          <!-- Image Counter -->
          <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2">
            <div class="text-white font-black tracking-widest text-sm bg-black/40 px-5 py-2 rounded-full backdrop-blur-md border border-white/10">
              CANVAS {{ currentImageIndex + 1 }} OF {{ provider.gallery_urls.length }}
            </div>
            
            <!-- Thumbnail Navigation Dots -->
            <div class="flex gap-2 mt-2">
              <button 
                v-for="(_, idx) in provider.gallery_urls" 
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

  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/utils/axios'
import { 
  ArrowLeft, 
  Award, 
  Quote, 
  Images, 
  Star, 
  Briefcase, 
  Eye,
  X,
  ChevronLeft,
  ChevronRight,
  Palette,
  Paintbrush,
  PaintRoller,
  Droplet
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const provider = ref(null)

// Lightbox State Variables
const isImageModalOpen = ref(false)
const currentImageIndex = ref(0)

// Vibrant gradients for the paint swatches
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

const specialtiesList = computed(() => {
  if (!provider.value?.specialties) return []
  return provider.value.specialties.split(',').map(s => s.trim()).filter(s => s.length > 0)
})

const fetchProfile = async () => {
  isLoading.value = true
  try {
    const response = await api.get(`/client/provider-profile/${route.params.id}`)
    if (response.data.success) {
      provider.value = response.data.data
    }
  } catch (error) {
    console.error("Failed to load profile:", error)
  } finally {
    isLoading.value = false
  }
}

// Keydown event listener for modal navigation
const handleKeydown = (e) => {
  if (!isImageModalOpen.value) return
  if (e.key === 'Escape') closeImageModal()
  if (e.key === 'ArrowRight') nextImage()
  if (e.key === 'ArrowLeft') prevImage()
}

// Lightbox Navigation Functions
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
  if (provider.value?.gallery_urls?.length) {
    currentImageIndex.value = (currentImageIndex.value + 1) % provider.value.gallery_urls.length
  }
}

const prevImage = () => {
  if (provider.value?.gallery_urls?.length) {
    currentImageIndex.value = currentImageIndex.value === 0 
      ? provider.value.gallery_urls.length - 1 
      : currentImageIndex.value - 1
  }
}

onMounted(() => {
  if (route.params.id) {
     fetchProfile()
  }
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = 'auto' // Failsafe
})
</script>

<style scoped>
/* Masonry Gallery Setup */
.columns-1 { columns: 1; }
@media (min-width: 640px) { .sm\:columns-2 { columns: 2; } }
@media (min-width: 1024px) { .lg\:columns-3 { columns: 3; } }
.break-inside-avoid { break-inside: avoid; }
</style>