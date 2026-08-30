<template>
  <!--ECommerceServices.vue-->
  <div class="min-h-screen relative ">
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
      <div class="container mx-auto px-4 py-4 md:py-6 flex justify-between items-center gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">Professional Services</h1>
          <p class="text-gray-500 mt-1 text-sm hidden sm:block">Book verified experts for your painting and maintenance needs</p>
        </div>
        
        <div class="hidden md:flex items-center gap-3">
          <Button @click="openTutorial" variant="outline" class="rounded-xl border-indigo-200 text-indigo-700 hover:bg-indigo-50 transition-colors font-medium">
            <HelpCircle class="w-4 h-4 mr-2" />
            How it works
          </Button>
           <Button @click="goToMyBookings" variant="outline" class="rounded-xl border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors font-medium">
            <ClipboardList class="w-4 h-4 mr-2" />
            Manage My Bookings
          </Button>
        </div>

        <div class="md:hidden flex items-center">
          <Sheet v-model:open="showMobileMenu">
            <SheetTrigger as-child>
              <Button variant="ghost" size="icon" class="h-10 w-10 text-gray-600 hover:bg-gray-100 rounded-xl">
                <Menu class="h-6 w-6" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" class="w-[85%] sm:w-80 p-0 z-10005">
              <div class="flex flex-col h-full bg-white">
                <div class="p-6 border-b border-gray-100">
                  <h2 class="text-lg font-bold text-gray-900 tracking-tight">Menu</h2>
                  <p class="text-sm text-gray-500 mt-1">Professional Services</p>
                </div>
                <div class="p-4 flex-1 flex flex-col gap-3">
                  <Button @click="() => { showMobileMenu = false; openTutorial(); }" variant="outline" class="w-full justify-start rounded-xl border-indigo-200 text-indigo-700 hover:bg-indigo-50 transition-colors font-medium h-12">
                    <HelpCircle class="w-5 h-5 mr-3" />
                    How it works
                  </Button>
                  <Button @click="goToMyBookingsMobile" variant="outline" class="w-full justify-start rounded-xl border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors font-medium h-12">
                    <ClipboardList class="w-5 h-5 mr-3" />
                    Manage My Bookings
                  </Button>
                </div>
              </div>
            </SheetContent>
          </Sheet>
        </div>
      </div>
    </div>

    <div class="bg-white border-b border-gray-100 shadow-sm relative z-20">
      <div class="container mx-auto px-4 py-4">
        <div class="flex flex-col lg:flex-row gap-4">
          <div class="flex-1">
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <svg class="h-5 w-5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <Input
                type="text"
                v-model="searchQuery"
                placeholder="Search for services, providers, or categories..."
                class="pl-10 pr-4 py-3 h-12 w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 bg-gray-50 focus:bg-white transition-all shadow-sm"
              />
            </div>
          </div>

          <div class="flex gap-2 overflow-x-auto pb-2 lg:pb-0 hide-scrollbar items-center">
            <Button
              v-for="filter in quickFilters"
              :key="filter.id"
              @click="toggleFilter(filter.id)"
              :variant="activeFilters.includes(filter.id) ? 'default' : 'secondary'"
              :class="[
                'whitespace-nowrap flex items-center space-x-2 rounded-xl transition-all h-10',
                activeFilters.includes(filter.id)
                  ? 'bg-blue-600 text-white shadow-md hover:bg-blue-700 border-transparent'
                  : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300'
              ]"
            >
              <component :is="filter.icon" class="w-4 h-4 mr-1.5" />
              <span class="font-medium">{{ filter.label }}</span>
            </Button>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-2 md:grid-cols-3 gap-4">
          <div>
            <Label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Category</Label>
            <Select v-model="selectedCategory">
              <SelectTrigger class="rounded-xl bg-gray-50 border-gray-200 hover:bg-white hover:border-blue-300 transition-colors focus:ring-blue-500 h-11">
                <SelectValue placeholder="All Categories" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_categories_reset">All Categories</SelectItem>
                <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Price Structure</Label>
            <Select v-model="selectedPriceType">
              <SelectTrigger class="rounded-xl bg-gray-50 border-gray-200 hover:bg-white hover:border-blue-300 transition-colors focus:ring-blue-500 h-11">
                <SelectValue placeholder="Any Price Type" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_types_reset">Any Price Type</SelectItem>
                <SelectItem value="Fixed">Fixed Price</SelectItem>
                <SelectItem value="Hourly">Per Hour</SelectItem>
                <SelectItem value="Per Sqm">Per Square Meter</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="col-span-2 md:col-span-1">
            <Label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Sort By</Label>
            <Select v-model="sortBy">
              <SelectTrigger class="rounded-xl bg-gray-50 border-gray-200 hover:bg-white hover:border-blue-300 transition-colors focus:ring-blue-500 h-11">
                <SelectValue placeholder="Recommended" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="recommended">Recommended</SelectItem>
                <SelectItem value="price-low">Price: Low to High</SelectItem>
                <SelectItem value="price-high">Price: High to Low</SelectItem>
                <SelectItem value="newest">Newest Listed</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 py-8 md:py-10">
      
      <div v-if="isLoading" class="text-center py-20 flex flex-col items-center">
        <div class="relative w-20 h-20 mb-6">
           <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
           <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
           <PaintRoller class="absolute inset-0 m-auto w-8 h-8 text-blue-600 animate-pulse" />
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Finding available experts...</h3>
        <p class="text-gray-500 font-medium">Please wait while we load the service catalog</p>
      </div>

      <div v-else>
        <div class="flex justify-between items-center mb-6">
          <p class="text-gray-600 font-medium bg-white px-4 py-2 rounded-full shadow-sm border border-gray-100 text-sm">
            Showing <strong class="text-gray-900">{{ filteredServices.length }}</strong> available services
          </p>
          <Button v-if="hasActiveFilters" @click="clearFilters" variant="ghost" class="text-blue-600 hover:text-blue-700 hover:bg-blue-50 h-9 px-3 rounded-lg text-sm font-semibold">
            Clear Filters
          </Button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          <Card
            v-for="service in filteredServices"
            :key="service.id"
            @click="goToServiceDetails(service.hash_id || service.id)"
            class="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-xl hover:border-blue-400 hover:-translate-y-1 transition-all duration-300 overflow-hidden group flex flex-col h-full cursor-pointer"
          >
            <div class="h-48 relative overflow-hidden bg-gray-100 flex items-center justify-center shrink-0">
              <div v-if="service.image_paths && service.image_paths.length > 0" class="w-full h-full relative group/slider">
                <img 
                  :src="getImageUrl(service.image_paths[service.currentImageIndex || 0])" 
                  alt="Service Image" 
                  class="object-cover w-full h-full transition-transform duration-700 group-hover:scale-105"
                  @error="handleImageError"
                />
                
                <div v-if="service.image_paths.length > 1" class="absolute inset-0 flex items-center justify-between px-2 opacity-0 group-hover/slider:opacity-100 transition-opacity duration-300">
                  <button @click.stop="prevImage(service)" class="w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                  </button>
                  <button @click.stop="nextImage(service)" class="w-8 h-8 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                  </button>
                </div>
                
                <div v-if="service.image_paths.length > 1" class="absolute bottom-2 left-0 right-0 flex justify-center gap-1.5 z-10">
                  <div v-for="(_, index) in service.image_paths" :key="index" 
                       :class="['w-1.5 h-1.5 rounded-full transition-all duration-300 shadow-sm', (service.currentImageIndex || 0) === index ? 'bg-white w-3' : 'bg-white/60 hover:bg-white/80']">
                  </div>
                </div>
              </div>
              <div v-else class="flex flex-col items-center text-gray-400">
                <ImageIcon class="w-10 h-10 mb-2 opacity-50" />
                <span class="text-xs font-medium uppercase tracking-wider">No Image</span>
              </div>
              
              <div class="absolute top-3 left-3 z-10">
                <Badge class="bg-white/90 text-gray-800 backdrop-blur-md border-0 shadow-sm font-semibold tracking-tight">
                  {{ service.category }}
                </Badge>
              </div>
            </div>

            <CardContent class="p-5 flex-1 flex flex-col justify-start">
              <div>
                <h3 class="font-bold text-lg leading-tight text-gray-900 mb-1.5 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ service.title }}</h3>
                
                <div class="flex items-center mb-3">
                  <Star class="w-4 h-4 text-amber-500 fill-amber-500 mr-1" />
                  <span class="text-sm font-bold text-gray-800">{{ service.average_rating > 0 ? service.average_rating.toFixed(1) : 'No rating' }}</span>
                  <span v-if="service.total_reviews > 0" class="text-xs text-gray-400 font-medium ml-1">({{ service.total_reviews }} reviews)</span>
                </div>

                <div class="flex items-center text-sm text-gray-600 mb-3 bg-gray-50 px-2.5 py-1.5 rounded-lg w-max border border-gray-100">
                  <User class="w-4 h-4 text-blue-500 mr-2 shrink-0" />
                  <span class="font-medium truncate max-w-37.5">{{ service.provider_name }}</span>
                </div>
                
                <p class="text-sm text-gray-500 line-clamp-3 leading-relaxed mb-4">
                  {{ service.description }}
                </p>
              </div>

              <div class="space-y-3 pt-4 border-t border-gray-100 mt-auto">
                <div class="flex items-center text-sm text-gray-600">
                  <Clock class="w-4 h-4 text-gray-400 mr-2 shrink-0" />
                  <span class="font-medium">Duration: <span class="text-gray-900">{{ service.duration }}</span></span>
                </div>
                
                <div class="flex justify-between items-end mt-2">
                  <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Starting at</p>
                    <div class="flex items-baseline text-blue-600">
                      <span class="text-2xl font-black tracking-tight">₱{{ formatCurrency(service.price) }}</span>
                      <span class="text-xs font-bold text-gray-500 ml-1">/ {{ formatPriceType(service.price_type) }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>

            <CardFooter class="p-5 pt-0 mt-auto border-t border-gray-50 bg-gray-50/50 rounded-b-2xl">
              <Button
                variant="outline"
                class="w-full mt-4 rounded-xl font-bold bg-white hover:bg-blue-50 text-blue-600 border-blue-200 h-12 shadow-sm transition-all group-hover:bg-blue-600 group-hover:text-white"
              >
                View Details
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
              </Button>
            </CardFooter>
          </Card>
        </div>

        <div v-if="filteredServices.length === 0" class="text-center py-24 bg-white rounded-3xl border border-gray-200 shadow-sm mt-4">
          <div class="w-20 h-20 mx-auto mb-5 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 border border-gray-100 shadow-inner">
            <Search class="w-8 h-8" />
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">No services found</h3>
          <p class="text-gray-500 mb-6 max-w-md mx-auto">We couldn't find any services matching your current filters. Try adjusting your search criteria.</p>
          <Button @click="clearFilters" class="rounded-xl bg-gray-900 hover:bg-gray-800 text-white px-8 h-11 shadow-md">
            Clear All Filters
          </Button>
        </div>
      </div>
    </div>

    <Teleport to="body">
      <!-- High-End Tutorial Dialog -->
      <transition enter-active-class="transition duration-500 ease-out" enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-300 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
        <div v-if="showTutorial" class="fixed inset-0 z-10000 flex items-center justify-center p-4 sm:p-6 bg-slate-900/90 backdrop-blur-md">
          <div class="bg-white rounded-4xl shadow-[0_0_50px_-12px_rgba(0,0,0,0.5)] max-w-6xl w-full h-[95vh] flex flex-col overflow-hidden relative">
            
            <!-- Floating Close Button -->
            <button @click="closeTutorial" class="absolute top-5 right-5 z-50 bg-white/80 hover:bg-gray-100 text-gray-500 hover:text-gray-900 rounded-full p-2.5 backdrop-blur-sm shadow-sm transition-all border border-gray-200">
              <X class="w-5 h-5" />
            </button>

            <div class="flex-1 flex flex-col h-full bg-gray-50/30">
              <!-- Animated Progress Bar -->
              <div class="w-full h-1.5 bg-gray-100">
                <div class="h-full bg-linear-to-r from-blue-500 via-indigo-500 to-purple-500 transition-all duration-500 ease-out" :style="{ width: `${((currentTutorialStep + 1) / tutorialSteps.length) * 100}%` }"></div>
              </div>

              <!-- Main Tutorial Content -->
              <div class="flex-1 overflow-y-auto flex flex-col items-center justify-start p-8 sm:p-12 text-center">
                <!-- Step Indicator -->
                <div class="inline-flex items-center justify-center px-5 py-2 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 font-black text-xs tracking-widest mb-6 uppercase shadow-sm">
                  Step {{ currentTutorialStep + 1 }} of {{ tutorialSteps.length }}
                </div>

                <!-- Descriptive Text -->
                <h3 class="text-2xl sm:text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mb-8 max-w-4xl tracking-tight">
                  {{ tutorialSteps[currentTutorialStep].text }}
                </h3>

                <!-- Image Showcase (Clickable for Fullscreen) -->
                <div class="relative w-full max-w-5xl flex-1 flex items-center justify-center min-h-[400px] group/img cursor-pointer" @click="isFullscreen = true">
                  <transition name="slide-fade" mode="out-in">
                    <img 
                      :key="currentTutorialStep"
                      :src="tutorialSteps[currentTutorialStep].image" 
                      :alt="'Step ' + (currentTutorialStep + 1)" 
                      class="max-w-full max-h-[65vh] object-contain rounded-2xl shadow-2xl border border-gray-200/80 bg-white ring-4 ring-gray-50 transition-transform duration-300 group-hover/img:scale-[1.02]" 
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
              <div class="p-6 bg-white border-t border-gray-100 flex justify-between items-center shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.05)] z-10 shrink-0">
                <Button @click="prevTutorialStep" :disabled="currentTutorialStep === 0" variant="outline" class="rounded-2xl font-bold h-14 px-6 border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-all text-base">
                  <ChevronLeft class="w-5 h-5 mr-2" />
                  Previous
                </Button>
                
                <!-- Interactive Dots -->
                <div class="hidden md:flex gap-3">
                  <button v-for="(_, index) in tutorialSteps" :key="index" @click="currentTutorialStep = index" :class="['w-2.5 h-2.5 rounded-full transition-all duration-500 ease-out', currentTutorialStep === index ? 'bg-indigo-600 w-10 shadow-md shadow-indigo-200' : 'bg-gray-200 hover:bg-gray-300']"></button>
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
        <div v-if="isFullscreen" class="fixed inset-0 z-11000 flex items-center justify-center bg-black/95 backdrop-blur-xl p-4 md:p-8" @click="isFullscreen = false" style="z-index: 11000;">
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

      <!-- Auth Modal -->
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isAuthAlertOpen" class="fixed inset-0 z-9990 bg-gray-900/60 backdrop-blur-sm pointer-events-none"></div>
      </transition>

      <AlertDialog :open="isAuthAlertOpen" @update:open="isAuthAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-10000">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Authentication Required
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3">
              You must be logged in to manage your bookings. Please log in or create an account to continue.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="isAuthAlertOpen = false" class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="router.push('/Landing/logIn')" class="rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white h-11 px-6 shadow-md shadow-blue-600/20">
              Log In
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, defineProps } from 'vue'
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { 
  Clock, 
  User, 
  Briefcase,
  Image as ImageIcon,
  PaintRoller,
  ClipboardList,
  Search,
  Star,
  Menu,
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
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
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
  Sheet,
  SheetContent,
  SheetTrigger,
} from '@/components/ui/sheet'

const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const router = useRouter() 
const services = ref([])
const isLoading = ref(true)

// Mobile Menu State
const showMobileMenu = ref(false)

// Authentication Modal State
const isAuthAlertOpen = ref(false)

// Tutorial Modal State
const showTutorial = ref(false)
const currentTutorialStep = ref(0)
const isFullscreen = ref(false)

const tutorialSteps = ref([
  { image: '/ClientTutorial/001.png', text: 'Select a service that aligns with your specific requirements and needs.' },
  { image: '/ClientTutorial/002.png', text: 'Complete the service request form by providing all necessary information.' },
  { image: '/ClientTutorial/003.png', text: 'Await official confirmation from the selected service provider.' },
  { image: '/ClientTutorial/004.png', text: 'Review and sign the survey agreement to authorize the service provider to inspect the requested area.' },
  { image: '/ClientTutorial/005.png', text: 'Upon completion of the survey, navigate to your messages to proceed with the service fulfillment process.' },
  { image: '/ClientTutorial/006.png', text: 'Carefully review the formal request details sent by the service provider to ensure they match your original requirements.' },
  { image: '/ClientTutorial/007.png', text: 'Accept or negotiate the official service terms with the provider until an agreement is reached. (Note: Fixed-price services are non-negotiable.)' },
  { image: '/ClientTutorial/008.png', text: 'Finalize the payment method and terms by accepting or negotiating the conditions set by the service provider.' },
  { image: '/ClientTutorial/009.png', text: 'Return to the Service Request dashboard to monitor the progress and completion of the agreed-upon work.' },
  { image: '/ClientTutorial/010.png', text: 'Once the service provider submits a completion request, review the work. You may approve it to finalize the job, or decline and request revisions if any requirements were not met.' },
  { image: '/ClientTutorial/011.png', text: 'Ensure timely compliance with the payment terms. (Note: The payment process is dictated by the conditions agreed upon during the negotiation phase.)' }
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
  }, 300) // Wait for transition to finish before resetting
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

// Filters State
const searchQuery = ref('')
const activeFilters = ref([])
const selectedCategory = ref('')
const selectedPriceType = ref('')
const sortBy = ref('recommended')

const quickFilters = ref([
  { id: 'painting', label: 'Painting', icon: PaintRoller },
  { id: 'waterproofing', label: 'Waterproofing', icon: Briefcase },
  { id: 'consultation', label: 'Color Consult', icon: User },
])

const categories = ref([
  'Interior Painting',
  'Exterior Painting',
  'Waterproofing',
  'Surface Preparation',
  'Wood Staining & Varnish',
  'Color Consultation',
  'Other'
])

const hasActiveFilters = computed(() => {
  return searchQuery.value || 
         activeFilters.value.length > 0 || 
         (selectedCategory.value && selectedCategory.value !== 'all_categories_reset') || 
         (selectedPriceType.value && selectedPriceType.value !== 'all_types_reset') || 
         sortBy.value !== 'recommended'
})

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

const handleImageError = (e) => {
  e.target.style.display = 'none'
  e.target.nextElementSibling && (e.target.nextElementSibling.style.display = 'flex')
}

const formatCurrency = (value) => {
  return Number(value || 0).toLocaleString('en-PH', { 
    minimumFractionDigits: 2, 
    maximumFractionDigits: 2 
  });
}

const formatPriceType = (type) => {
  if (type === 'Fixed') return 'Fixed Price'
  if (type === 'Hourly') return 'Hour'
  if (type === 'Per Sqm') return 'Sqm'
  return type
}

const fetchServices = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/client/services')
    if (response.data.success) {
      services.value = response.data.data.map(service => ({
        ...service,
        currentImageIndex: 0
      }))
    }
  } catch (error) {
    toast.error('Failed to load services')
    console.error('Error fetching services:', error)
  } finally {
    isLoading.value = false
  }
}

// Carousel Controls
const nextImage = (service) => {
  if (service.image_paths && service.image_paths.length > 1) {
    service.currentImageIndex = (service.currentImageIndex + 1) % service.image_paths.length
  }
}

const prevImage = (service) => {
  if (service.image_paths && service.image_paths.length > 1) {
    service.currentImageIndex = service.currentImageIndex === 0 
      ? service.image_paths.length - 1 
      : service.currentImageIndex - 1
  }
}

const toggleFilter = (filterId) => {
  const index = activeFilters.value.indexOf(filterId)
  if (index > -1) activeFilters.value.splice(index, 1)
  else activeFilters.value.push(filterId)
}

const clearFilters = () => {
  searchQuery.value = ''
  activeFilters.value = []
  selectedCategory.value = ''
  selectedPriceType.value = ''
  sortBy.value = 'recommended'
}

const filteredServices = computed(() => {
  let filtered = [...services.value]

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(s => 
      (s.title && s.title.toLowerCase().includes(q)) ||
      (s.provider_name && s.provider_name.toLowerCase().includes(q)) ||
      (s.category && s.category.toLowerCase().includes(q))
    )
  }

  if (activeFilters.value.includes('painting')) {
    filtered = filtered.filter(s => s.category && s.category.toLowerCase().includes('painting'))
  }
  if (activeFilters.value.includes('waterproofing')) {
    filtered = filtered.filter(s => s.category && s.category.toLowerCase().includes('waterproof'))
  }
  if (activeFilters.value.includes('consultation')) {
    filtered = filtered.filter(s => s.category && s.category.toLowerCase().includes('consultation'))
  }

  if (selectedCategory.value && selectedCategory.value !== 'all_categories_reset') {
    filtered = filtered.filter(s => s.category === selectedCategory.value)
  }
  
  if (selectedPriceType.value && selectedPriceType.value !== 'all_types_reset') {
    filtered = filtered.filter(s => s.price_type === selectedPriceType.value)
  }

  if (sortBy.value === 'price-low') {
    filtered.sort((a, b) => a.price - b.price)
  } else if (sortBy.value === 'price-high') {
    filtered.sort((a, b) => b.price - a.price)
  } else if (sortBy.value === 'newest') {
    filtered.sort((a, b) => new Date(b.created_at || 0) - new Date(a.created_at || 0))
  }

  return filtered
})

const goToServiceDetails = (hashId) => {
  router.push(`/ECommerceClient/ServiceDetails/${hashId}`)
}

const goToMyBookings = () => {
  if (!props.user) {
    isAuthAlertOpen.value = true;
    return;
  }
  router.push('/Clients/myServiceRequest') 
}

const goToMyBookingsMobile = () => {
  showMobileMenu.value = false;
  goToMyBookings();
}

onMounted(() => {
  fetchServices()
})
</script>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
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