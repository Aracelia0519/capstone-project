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
      
      <!-- NEW: Added flex container and Set-up Portfolio Button -->
      <div class="flex flex-wrap items-center gap-3">
        <!-- Tutorial Button -->
        <Button @click="openTutorial" variant="outline" class="border-indigo-500/50 bg-gray-900 text-indigo-400 hover:bg-indigo-600 hover:border-indigo-600 hover:text-white font-bold px-6 py-6 rounded-xl shadow-lg transition-all hover:-translate-y-0.5">
          <HelpCircle class="w-5 h-5 mr-2" />
          How it works
        </Button>

        <Button @click="goToPortfolio" variant="outline" class="border-blue-500/50 bg-gray-900 text-blue-400 hover:bg-blue-600 hover:border-blue-600 hover:text-white font-bold px-6 py-6 rounded-xl shadow-lg transition-all hover:-translate-y-0.5">
          <Images class="w-5 h-5 mr-2" />
          Set-up Portfolio
        </Button>

        <Button @click="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-6 rounded-xl shadow-lg shadow-blue-900/20 transition-all hover:-translate-y-0.5">
          <Plus class="w-5 h-5 mr-2" />
          Post New Service
        </Button>
      </div>
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
              <span class="font-bold text-base">{{ Number(service.price).toLocaleString() }}</span>
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

    <!-- Forms Modal -->
    <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
      <DialogContent class="sm:max-w-[600px] bg-gray-900 border-gray-800 text-gray-100 rounded-2xl shadow-2xl p-0 overflow-hidden z-[10000]">
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

    <!-- Delete Alert -->
    <AlertDialog :open="deleteAlert.isOpen" @update:open="deleteAlert.isOpen = $event">
      <AlertDialogContent class="bg-gray-900 border-gray-800 text-white rounded-2xl shadow-2xl z-[10000]">
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

    <!-- Visibility Alert -->
    <AlertDialog :open="toggleAlert.isOpen" @update:open="toggleAlert.isOpen = $event">
      <AlertDialogContent class="bg-gray-900 border-gray-800 text-white rounded-2xl shadow-2xl z-[10000]">
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

    <!-- Tutorial System -->
    <Teleport to="body">
      <!-- High-End Tutorial Dialog (Dark Mode) -->
      <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
        <div v-if="showTutorial" class="fixed inset-0 z-[10000] flex items-center justify-center p-4 sm:p-6 bg-slate-900/90 backdrop-blur-md">
          <div class="bg-gray-900 rounded-4xl border border-gray-800 shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] max-w-6xl w-full h-[95vh] flex flex-col overflow-hidden relative">
            
            <!-- Floating Close Button -->
            <button @click="closeTutorial" class="absolute top-5 right-5 z-50 bg-gray-800/80 hover:bg-gray-700 text-gray-400 hover:text-gray-100 rounded-full p-2.5 backdrop-blur-sm shadow-sm transition-all border border-gray-700">
              <X class="w-5 h-5" />
            </button>

            <div class="flex-1 flex flex-col h-full bg-gray-900/50">
              <!-- Animated Progress Bar -->
              <div class="w-full h-1.5 bg-gray-800">
                <div class="h-full bg-linear-to-r from-blue-500 via-indigo-500 to-purple-500 transition-all duration-500 ease-out" :style="{ width: `${((currentTutorialStep + 1) / tutorialSteps.length) * 100}%` }"></div>
              </div>

              <!-- Main Tutorial Content -->
              <div class="flex-1 overflow-y-auto flex flex-col items-center justify-start p-8 sm:p-12 text-center">
                <!-- Step Indicator -->
                <div class="inline-flex items-center justify-center px-5 py-2 rounded-full bg-gray-800 border border-gray-700 text-indigo-400 font-black text-xs tracking-widest mb-6 uppercase shadow-sm">
                  Step {{ currentTutorialStep + 1 }} of {{ tutorialSteps.length }}
                </div>

                <!-- Descriptive Text -->
                <h3 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-white leading-tight mb-8 max-w-4xl tracking-tight">
                  {{ tutorialSteps[currentTutorialStep].text }}
                </h3>

                <!-- Image Showcase (Clickable for Fullscreen) -->
                <div class="relative w-full max-w-5xl flex-1 flex items-center justify-center min-h-[400px] group/img cursor-pointer" @click="isFullscreen = true">
                  <transition name="slide-fade" mode="out-in">
                    <img 
                      :key="currentTutorialStep"
                      :src="tutorialSteps[currentTutorialStep].image" 
                      :alt="'Step ' + (currentTutorialStep + 1)" 
                      class="max-w-full max-h-[65vh] object-contain rounded-2xl shadow-2xl border border-gray-700/80 bg-gray-800 ring-4 ring-gray-800/50 transition-transform duration-300 group-hover/img:scale-[1.02]" 
                    />
                  </transition>
                  <!-- Hover Overlay for Image -->
                  <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition-opacity duration-300 pointer-events-none">
                    <div class="bg-black/60 p-4 rounded-full text-white backdrop-blur-sm shadow-xl transform scale-90 group-hover/img:scale-100 transition-transform">
                      <ZoomIn class="w-8 h-8" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sleek Footer Controls -->
              <div class="p-6 bg-gray-900 border-t border-gray-800 flex justify-between items-center shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.2)] z-10 shrink-0">
                <Button @click="prevTutorialStep" :disabled="currentTutorialStep === 0" variant="outline" class="rounded-2xl font-bold h-14 px-6 border-gray-700 text-gray-300 bg-gray-800 hover:bg-gray-700 hover:text-white transition-all text-base">
                  <ChevronLeft class="w-5 h-5 mr-2" />
                  Previous
                </Button>
                
                <!-- Interactive Dots -->
                <div class="hidden md:flex gap-3">
                  <button v-for="(_, index) in tutorialSteps" :key="index" @click="currentTutorialStep = index" :class="['w-2.5 h-2.5 rounded-full transition-all duration-500 ease-out', currentTutorialStep === index ? 'bg-indigo-500 w-10 shadow-md shadow-indigo-500/50' : 'bg-gray-700 hover:bg-gray-600']"></button>
                </div>

                <Button v-if="currentTutorialStep < tutorialSteps.length - 1" @click="nextTutorialStep" class="rounded-2xl font-bold bg-indigo-600 hover:bg-indigo-700 text-white h-14 px-8 shadow-lg shadow-indigo-600/30 transition-all text-base group">
                  Next Step
                  <ChevronRight class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" />
                </Button>
                <Button v-else @click="closeTutorial" class="rounded-2xl font-bold bg-linear-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white h-14 px-8 shadow-lg shadow-blue-600/30 transition-all text-base group">
                  Got It, Let's Go!
                  <Check class="w-5 h-5 ml-2 group-hover:scale-110 transition-transform" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </transition>

      <!-- Fullscreen Image Viewer Modal -->
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isFullscreen" class="fixed inset-0 z-[11000] flex items-center justify-center bg-black/95 backdrop-blur-xl p-4 md:p-8" @click="isFullscreen = false">
          <button @click.stop="isFullscreen = false" class="absolute top-6 right-6 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-3 transition-colors z-50">
            <X class="w-8 h-8" />
          </button>
          <img 
            :src="tutorialSteps[currentTutorialStep].image" 
            :alt="'Fullscreen Step ' + (currentTutorialStep + 1)" 
            class="w-full h-full object-contain select-none cursor-zoom-out drop-shadow-2xl"
            style="image-rendering: high-quality;"
            @click.stop="isFullscreen = false"
          />
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router' 
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
  UploadCloud,
  Images, 
  HelpCircle,
  ChevronLeft,
  ChevronRight,
  Check,
  X,
  ZoomIn
} from 'lucide-vue-next'

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

const router = useRouter() 
const services = ref([])
const isLoading = ref(true)
const isSubmitting = ref(false)

const isModalOpen = ref(false)
const isEditing = ref(false)
const currentEditId = ref(null)

const fileInput = ref(null)
const selectedFiles = ref([])

const deleteAlert = ref({ isOpen: false, id: null })
const toggleAlert = ref({ isOpen: false, service: null })

// Tutorial State
const showTutorial = ref(false)
const currentTutorialStep = ref(0)
const isFullscreen = ref(false)

const tutorialSteps = ref([
  { image: '/SPTutorial/001.png', text: 'Formulate a comprehensive service offering that accurately represents your professional capabilities.' },
  { image: '/SPTutorial/002.png', text: 'Complete the service listing form by providing all required and relevant information.' },
  { image: '/SPTutorial/003.png', text: 'Await service requests or bookings from prospective clients.' },
  { image: '/SPTutorial/004.png', text: 'Generate a formal survey agreement to establish legal authorization for inspecting the client\'s premises.' },
  { image: '/SPTutorial/005.png', text: 'Affix your digital signature to validate the survey agreement.' },
  { image: '/SPTutorial/006.png', text: 'Await the client\'s countersignature and approval of the survey agreement.' },
  { image: '/SPTutorial/007.png', text: 'Upon client approval, initiate the official survey by clicking "Start Survey" in the system before conducting the physical inspection, and ensure you click "End Survey" upon completion.' },
  { image: '/SPTutorial/008.png', text: 'Evaluate the survey results and determine whether to accept or decline the requested service.' },
  { image: '/SPTutorial/009.png', text: 'Utilize the integrated messaging system to coordinate further details regarding the service fulfillment.' },
  { image: '/SPTutorial/010.png', text: 'Transmit the finalized request details to the client for mutual understanding.' },
  { image: '/SPTutorial/011.png', text: 'Draft an Official Deal and engage in negotiations until a mutually agreeable price is established. (Note: Fixed-Price services are non-negotiable).' },
  { image: '/SPTutorial/012.png', text: 'Select the appropriate payment method and clearly define the payment conditions.' },
  { image: '/SPTutorial/013.png', text: 'Navigate to the Service Jobs dashboard to monitor the progress and oversee the fulfillment of the agreed-upon work.' },
  { image: '/SPTutorial/014.png', text: 'Submit official proof of completion once the service is rendered. Note: Any incomplete tasks may result in the client declining the completion request, requiring you to fulfill the remaining obligations.' },
  { image: '/SPTutorial/015.png', text: 'If the service is completed but payment is pending, you may issue an email reminder. Should the maximum reminder attempts be reached, a formal document will be generated to assist you in pursuing legal recourse.' }
])

const openTutorial = () => {
  currentTutorialStep.value = 0
  showTutorial.value = true
}

const closeTutorial = () => {
  showTutorial.value = false
  isFullscreen.value = false
  setTimeout(() => {
    currentTutorialStep.value = 0
  }, 300) 
}

const nextTutorialStep = () => {
  if (currentTutorialStep.value < tutorialSteps.value.length - 1) {
    currentTutorialStep.value++
  }
}

const prevTutorialStep = () => {
  if (currentTutorialStep.value > 0) {
    currentTutorialStep.value--
  }
}

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

const getImageUrl = (path) => {
  if (!path) return '';
  const baseUrl = import.meta.env.VITE_API_URL 
      ? import.meta.env.VITE_API_URL.replace('/api', '') 
      : 'http://localhost:8000';
  if (path.includes('localhost:8000')) {
      path = path.replace('http://localhost:8000', baseUrl);
  }
  if (path.startsWith('http')) return path;
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
    fetchServices() 
  } catch (error) {
    console.error("Save error:", error)
    toast.error(error.response?.data?.message || 'Failed to save service')
  } finally {
    isSubmitting.value = false
  }
}

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

const goToPortfolio = () => {
  router.push('/ServiceProvider/PortfolioSetup') 
}
</script>

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

/* Tutorial Slide Transitions */
.slide-fade-enter-active {
  transition: all 0.4s ease-out;
}
.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from {
  transform: translateX(20px) scale(0.98);
  opacity: 0;
}
.slide-fade-leave-to {
  transform: translateX(-20px) scale(0.98);
  opacity: 0;
}
</style>