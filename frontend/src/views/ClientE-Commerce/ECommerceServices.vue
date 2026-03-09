<template>
  <div class="min-h-screen relative bg-gray-50/50">
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
      <div class="container mx-auto px-4 py-4 md:py-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">Professional Services</h1>
          <p class="text-gray-500 mt-1 text-sm">Book verified experts for your painting and maintenance needs</p>
        </div>
        
        <div class="flex items-center gap-3">
           <Button @click="goToMyBookings" variant="outline" class="rounded-xl border-blue-200 text-blue-700 hover:bg-blue-50 transition-colors font-medium">
            <ClipboardList class="w-4 h-4 mr-2" />
            Manage My Bookings
          </Button>
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <Card
            v-for="service in filteredServices"
            :key="service.id"
            class="bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-xl hover:border-blue-200 hover:-translate-y-1 transition-all duration-300 overflow-hidden group flex flex-col h-full"
          >
            <div class="h-48 relative overflow-hidden bg-gray-100 flex items-center justify-center">
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

            <CardContent class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <h3 class="font-bold text-lg leading-tight text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">{{ service.title }}</h3>
                
                <div class="flex items-center text-sm text-gray-600 mb-3 bg-gray-50 px-2.5 py-1.5 rounded-lg w-max border border-gray-100">
                  <User class="w-4 h-4 text-blue-500 mr-2 shrink-0" />
                  <span class="font-medium truncate max-w-[150px]">{{ service.provider_name }}</span>
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
                @click="openServiceModal(service)"
                class="w-full mt-4 rounded-xl font-bold bg-gray-900 hover:bg-black text-white h-12 shadow-md transition-all group-hover:shadow-lg"
              >
                Request Service
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
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showServiceModal || isAuthAlertOpen" class="fixed inset-0 z-[9990] bg-gray-900/60 backdrop-blur-sm pointer-events-none"></div>
      </transition>

      <AlertDialog :open="isAuthAlertOpen" @update:open="isAuthAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Authentication Required
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3">
              You must be logged in to request a service or manage your bookings. Please log in or create an account to continue.
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

      <Dialog :open="showServiceModal" @update:open="(val) => !val && closeServiceModal()">
        <DialogContent class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh] ring-1 ring-black/5 p-0 border-0 z-[9999]">
          
          <div class="px-6 py-5 border-b border-gray-100 bg-white/80 backdrop-blur-md sticky top-0 z-20 flex justify-between items-start shrink-0">
            <div>
              <DialogTitle class="text-2xl font-black text-gray-900 tracking-tight">{{ selectedService?.title }}</DialogTitle>
              <DialogDescription class="text-gray-500 font-medium mt-1 flex items-center">
                 By <span class="text-blue-600 ml-1 font-bold">{{ selectedService?.provider_name }}</span>
              </DialogDescription>
            </div>
            <button @click="closeServiceModal" class="p-2 bg-gray-50 hover:bg-gray-100 rounded-full text-gray-400 hover:text-gray-600 transition-colors shrink-0 border border-gray-200 shadow-sm">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
          </div>
          
          <div class="px-6 py-6 overflow-y-auto flex-1 custom-scrollbar bg-gray-50/30">
            
            <div class="flex flex-col sm:flex-row gap-4 mb-8 bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
              <div class="flex-1 flex items-center gap-3">
                 <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <DollarSign class="w-5 h-5" />
                 </div>
                 <div>
                   <p class="text-xs font-bold text-blue-600/70 uppercase tracking-wider">Estimated Rate</p>
                   <p class="font-black text-gray-900 text-lg">₱{{ formatCurrency(selectedService?.price) }} <span class="text-sm text-gray-500 font-medium normal-case">/ {{ formatPriceType(selectedService?.price_type) }}</span></p>
                 </div>
              </div>
              <div class="hidden sm:block w-px bg-blue-200"></div>
              <div class="flex-1 flex items-center gap-3">
                 <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <Clock class="w-5 h-5" />
                 </div>
                 <div>
                   <p class="text-xs font-bold text-blue-600/70 uppercase tracking-wider">Est. Duration</p>
                   <p class="font-bold text-gray-900 text-base">{{ selectedService?.duration }}</p>
                 </div>
              </div>
            </div>

            <div class="space-y-6">
              <div>
                <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-2 flex items-center gap-2">
                  <ClipboardList class="w-4 h-4 text-gray-400" />
                  Project Details
                </Label>
                <Textarea 
                  v-model="bookingForm.description" 
                  placeholder="Describe what needs to be done. Include estimated sizes, current conditions, or specific requirements..." 
                  class="resize-none min-h-[100px] border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm bg-white"
                />
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-2 flex items-center gap-2">
                    <Calendar class="w-4 h-4 text-gray-400" />
                    Preferred Date
                  </Label>
                  <Input 
                    v-model="bookingForm.preferred_date" 
                    type="date" 
                    :min="minDate"
                    class="border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm h-12 bg-white"
                  />
                </div>
                <div>
                  <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-2 flex items-center gap-2">
                    <Clock class="w-4 h-4 text-gray-400" />
                    Preferred Time
                  </Label>
                  <Select v-model="bookingForm.time_preference">
                    <SelectTrigger class="border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm h-12 bg-white">
                      <SelectValue placeholder="Select a timeframe" />
                    </SelectTrigger>
                    
                    <SelectContent class="z-[10000]">
                      <SelectItem value="Morning (8AM - 12PM)">Morning (8AM - 12PM)</SelectItem>
                      <SelectItem value="Afternoon (1PM - 5PM)">Afternoon (1PM - 5PM)</SelectItem>
                      <SelectItem value="Flexible">Flexible / Anytime</SelectItem>
                    </SelectContent>
                    
                  </Select>
                </div>
              </div>

              <div>
                <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-2 flex items-center gap-2">
                  <Phone class="w-4 h-4 text-gray-400" />
                  Contact Number
                </Label>
                <Input 
                  v-model="bookingForm.contact_number" 
                  @input="bookingForm.contact_number = $event.target.value.replace(/[^0-9]/g, '').slice(0, 11)"
                  placeholder="e.g. 09123456789" 
                  class="border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm h-12 bg-white"
                />
              </div>

              <div class="mb-4">
                <Label class="text-sm font-bold text-gray-700 uppercase tracking-wider block mb-2 flex items-center gap-2">
                  <MapPin class="w-4 h-4 text-gray-400" />
                  Service Location
                </Label>
                <div class="space-y-3">
                  <label class="flex items-start gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 bg-white" :class="addressMode === 'default' ? 'border-blue-500 bg-blue-50/30 shadow-sm' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" v-model="addressMode" value="default" class="hidden" />
                    <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'default' ? 'border-blue-500' : 'border-gray-300'">
                      <div v-if="addressMode === 'default'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                    </div>
                    <div>
                      <span class="block font-bold text-gray-900 mb-0.5">Use Profile Address</span>
                      <span class="text-sm text-gray-500 font-medium">Use the saved address from your account.</span>
                    </div>
                  </label>

                  <label class="flex items-start gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 bg-white" :class="addressMode === 'custom' ? 'border-blue-500 bg-blue-50/30 shadow-sm' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" v-model="addressMode" value="custom" class="hidden" />
                    <div class="mt-0.5 w-5 h-5 rounded-full border-2 flex flex-shrink-0 items-center justify-center" :class="addressMode === 'custom' ? 'border-blue-500' : 'border-gray-300'">
                      <div v-if="addressMode === 'custom'" class="w-2.5 h-2.5 bg-blue-500 rounded-full"></div>
                    </div>
                    <div class="w-full">
                      <span class="block font-bold text-gray-900 mb-1">Custom Address</span>
                      <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-32">
                        <Textarea 
                          v-if="addressMode === 'custom'" 
                          v-model="customAddress" 
                          @click.stop
                          placeholder="Enter complete block, street, barangay, city, province..." 
                          class="mt-2 w-full p-3 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-inner resize-none min-h-[80px]"
                        />
                      </transition>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <div class="p-4 bg-white border-t border-gray-100 flex justify-end gap-3 shrink-0">
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

<script setup>
import { ref, onMounted, computed, defineProps } from 'vue' 
import { useRouter } from 'vue-router'
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
  ClipboardList,
  Search
} from 'lucide-vue-next'

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

const props = defineProps({
  user: {
    type: Object,
    default: null
  }
})

const router = useRouter() 
const services = ref([])
const isLoading = ref(true)
const showServiceModal = ref(false)
const selectedService = ref(null)
const isSubmitting = ref(false)

// Authentication Modal State
const isAuthAlertOpen = ref(false)

const addressMode = ref('default')
const customAddress = ref('')
const bookingForm = ref({
  description: '',
  preferred_date: '',
  time_preference: '',
  contact_number: ''
})

// Current Date for input min
const minDate = computed(() => {
  const today = new Date()
  today.setDate(today.getDate() + 1) // Require at least 1 day advance
  return today.toISOString().split('T')[0]
})

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
  
  // 3. If it's already a full HTTP URL (like S3 or after our replacement), return it directly
  if (path.startsWith('http')) return path;
  
  // 4. Handle new DB records that correctly only saved the relative path ('service_offerings/xyz.jpg')
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

// Filter Logic
const toggleFilter = (filterId) => {
  const index = activeFilters.value.indexOf(filterId)
  if (index > -1) {
    activeFilters.value.splice(index, 1)
  } else {
    activeFilters.value.push(filterId)
  }
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

const openServiceModal = (service) => {
  if (!props.user) {
    isAuthAlertOpen.value = true;
    return;
  }
  
  selectedService.value = service
  bookingForm.value = {
    description: '',
    preferred_date: '',
    time_preference: '',
    contact_number: ''
  }
  addressMode.value = 'default'
  customAddress.value = ''
  showServiceModal.value = true
}

const closeServiceModal = () => {
  showServiceModal.value = false
  setTimeout(() => {
    selectedService.value = null
  }, 300)
}

const goToMyBookings = () => {
  if (!props.user) {
    isAuthAlertOpen.value = true;
    return;
  }
  router.push('/Clients/myServiceRequest') 
}

const submitServiceRequest = async () => {
  if (!bookingForm.value.description || !bookingForm.value.preferred_date || !bookingForm.value.time_preference || !bookingForm.value.contact_number) {
    toast.error('Missing Information', { description: 'Please fill in all required fields.' })
    return
  }

  // FIX: Frontend Validation Check before sending request
  if (!/^0[0-9]{10}$/.test(bookingForm.value.contact_number)) {
    toast.error('Invalid Contact Number', { description: 'Contact number must be exactly 11 digits and start with 0 (e.g. 09123456789).' })
    return
  }

  if (addressMode.value === 'custom' && !customAddress.value.trim()) {
    toast.error('Missing Address', { description: 'Please provide your custom address.' })
    return
  }

  try {
    isSubmitting.value = true
    
    const payload = {
      service_offering_id: selectedService.value.id,
      provider_id: selectedService.value.provider_id,
      description: bookingForm.value.description,
      preferred_date: bookingForm.value.preferred_date,
      time_preference: bookingForm.value.time_preference,
      contact_number: bookingForm.value.contact_number,
      address: addressMode.value === 'custom' ? customAddress.value : 'default'
    }

    const response = await api.post('/client/services/request', payload)

    if (response.data.success) {
      toast.success('Service Requested!', {
        description: 'The provider has been notified and will contact you soon.'
      })
      closeServiceModal()
    }
  } catch (error) {
    toast.error('Failed to submit request', {
      description: error.response?.data?.message || 'Please check your details and try again.'
    })
    console.error('Submission error:', error)
  } finally {
    isSubmitting.value = false
  }
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
</style>