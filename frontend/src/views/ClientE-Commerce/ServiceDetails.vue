<template>
  <div class="min-h-screen relative pb-20">
    
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-30">
      <div class="container mx-auto px-4 py-4 md:py-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Button @click="router.back()" variant="ghost" class="rounded-full h-10 w-10 p-0 text-gray-500 hover:text-gray-900 bg-gray-50 hover:bg-gray-100">
            <ArrowLeft class="w-5 h-5" />
          </Button>
          <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900 tracking-tight">Service Details</h1>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex flex-col items-center justify-center py-32">
        <div class="relative w-16 h-16 mb-6">
           <div class="absolute inset-0 rounded-full border-4 border-gray-100"></div>
           <div class="absolute inset-0 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
        </div>
        <h3 class="text-xl font-bold text-gray-900">Loading service details...</h3>
    </div>

    <div v-else-if="!selectedService" class="text-center py-32">
        <h3 class="text-2xl font-bold text-gray-900 mb-2">Service Not Found</h3>
        <p class="text-gray-500 mb-6">The service you are looking for may have been removed or is unavailable.</p>
        <Button @click="router.push('/client/services')" class="bg-blue-600 text-white hover:bg-blue-700">Back to Services</Button>
    </div>

    <div v-else class="container mx-auto px-4 py-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-7 space-y-8">
          
          <div class="bg-white rounded-3xl p-2 border border-gray-100 shadow-sm overflow-hidden">
            <div class="relative h-[300px] md:h-[450px] w-full rounded-2xl overflow-hidden bg-gray-100 flex items-center justify-center group/slider">
              <div v-if="selectedService.image_paths && selectedService.image_paths.length > 0" class="w-full h-full">
                <img 
                  :src="getImageUrl(selectedService.image_paths[currentImageIndex])" 
                  alt="Service Image" 
                  class="object-cover w-full h-full transition-transform duration-500 hover:scale-105"
                  @error="handleImageError"
                />
                
                <div v-if="selectedService.image_paths.length > 1" class="absolute inset-0 flex items-center justify-between px-4 opacity-0 group-hover/slider:opacity-100 transition-opacity duration-300">
                  <button @click.stop="prevImage" class="w-10 h-10 rounded-full bg-white/80 hover:bg-white text-gray-900 flex items-center justify-center shadow-lg transition-colors">
                    <ChevronLeft class="w-6 h-6" />
                  </button>
                  <button @click.stop="nextImage" class="w-10 h-10 rounded-full bg-white/80 hover:bg-white text-gray-900 flex items-center justify-center shadow-lg transition-colors">
                    <ChevronRight class="w-6 h-6" />
                  </button>
                </div>
              </div>
              <div v-else class="flex flex-col items-center text-gray-400">
                <ImageIcon class="w-16 h-16 mb-2 opacity-50" />
                <span class="text-sm font-medium uppercase tracking-wider">No Image Available</span>
              </div>
              <div class="absolute top-4 left-4 z-10">
                <Badge class="bg-white/90 text-gray-800 backdrop-blur-md border-0 shadow-sm font-semibold text-sm px-3 py-1">
                  {{ selectedService.category }}
                </Badge>
              </div>
            </div>

            <div v-if="selectedService.image_paths && selectedService.image_paths.length > 1" class="flex gap-2 mt-2 overflow-x-auto hide-scrollbar p-1">
               <button 
                  v-for="(img, index) in selectedService.image_paths" 
                  :key="index"
                  @click="currentImageIndex = index"
                  class="w-20 h-20 rounded-xl overflow-hidden shrink-0 border-2 transition-all duration-200"
                  :class="currentImageIndex === index ? 'border-blue-500 shadow-md scale-100' : 'border-transparent opacity-60 hover:opacity-100 scale-95'"
               >
                  <img :src="getImageUrl(img)" class="w-full h-full object-cover" />
               </button>
            </div>
          </div>

          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
             <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                <FileText class="w-5 h-5 text-blue-500" />
                About This Service
             </h2>
             <p class="text-gray-600 leading-relaxed whitespace-pre-line text-[15px]">
                {{ selectedService.description }}
             </p>
          </div>

          <div class="bg-white rounded-3xl p-6 md:p-8 border border-gray-100 shadow-sm">
            <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2 border-b border-gray-100 pb-4">
              <Star class="w-6 h-6 text-amber-500 fill-amber-500" /> 
              Client Reviews ({{ selectedService.total_reviews || 0 }})
            </h3>
            
            <div v-if="!selectedService.reviews || selectedService.reviews.length === 0" class="text-center py-10 bg-gray-50 rounded-2xl border border-gray-100">
              <MessageSquare class="w-10 h-10 text-gray-300 mx-auto mb-3" />
              <p class="text-base font-medium text-gray-500">No reviews yet for this service.</p>
            </div>
            
            <div v-else class="space-y-6">
              <div v-for="review in selectedService.reviews" :key="review.id" class="bg-gray-50/50 p-5 rounded-2xl border border-gray-100">
                <div class="flex justify-between items-start mb-3">
                  <div>
                    <p class="font-bold text-gray-900 text-base">{{ review.client_name }}</p>
                    <div class="flex items-center gap-1 mt-1">
                      <Star v-for="n in 5" :key="n" :class="['w-4 h-4', n <= review.rating ? 'text-amber-500 fill-amber-500' : 'text-gray-200']" />
                    </div>
                  </div>
                  <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">{{ formatDate(review.created_at) }}</p>
                </div>
                <p class="text-[15px] text-gray-700 leading-relaxed">"{{ review.comment || 'No specific comment provided.' }}"</p>
                
                <div v-if="review.reply" class="mt-4 bg-white border border-blue-100 rounded-xl p-4 relative ml-4 shadow-sm">
                  <div class="absolute -left-4 top-4 text-blue-200"><CornerDownRight class="w-6 h-6" /></div>
                  <p class="text-[11px] font-bold text-blue-600 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <ShieldCheck class="w-3.5 h-3.5" /> Response from Provider
                  </p>
                  <p class="text-sm text-gray-700 leading-relaxed">{{ review.reply }}</p>
                </div>

                <div v-if="props.user && props.user.id === review.client_id && review.reply && !review.client_reply" class="mt-4 ml-8">
                    <Button size="sm" variant="outline" class="text-xs border-blue-200 text-blue-600 hover:bg-blue-50" @click="openClientReplyModal(review)">
                      <Reply class="w-3.5 h-3.5 mr-1.5" /> Reply to Provider
                    </Button>
                </div>

                <div v-else-if="review.client_reply" class="mt-4 bg-gray-100 border border-gray-200 rounded-xl p-4 relative ml-8">
                  <div class="absolute -left-4 top-4 text-gray-300"><CornerDownRight class="w-6 h-6" /></div>
                  <p class="text-[11px] font-bold text-gray-600 uppercase tracking-wider mb-1.5">Client Follow-up</p>
                  <p class="text-sm text-gray-700 leading-relaxed">{{ review.client_reply }}</p>
                </div>

              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-5">
          <div class="bg-white rounded-3xl p-6 border border-gray-100 shadow-xl sticky top-24">
            
            <div class="mb-6">
               <h1 class="text-2xl font-black text-gray-900 tracking-tight leading-tight mb-2">{{ selectedService.title }}</h1>
               <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                 <div class="flex items-center text-sm text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                    <User class="w-4 h-4 text-blue-500 mr-2 shrink-0" />
                    <span class="font-bold">{{ selectedService.provider_name }}</span>
                 </div>
                 <div class="flex items-center bg-amber-50 px-2 py-1 rounded-lg border border-amber-100 text-amber-600">
                    <Star class="w-4 h-4 fill-amber-500 mr-1" />
                    <span class="font-bold">{{ selectedService.average_rating > 0 ? selectedService.average_rating.toFixed(1) : 'New' }}</span>
                 </div>
               </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 mb-8 bg-blue-50/50 p-4 rounded-2xl border border-blue-100">
              <div class="flex-1 flex items-center gap-3">
                 <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <DollarSign class="w-5 h-5" />
                 </div>
                 <div>
                   <p class="text-xs font-bold text-blue-600/70 uppercase tracking-wider">Estimated Rate</p>
                   <p class="font-black text-gray-900 text-lg">₱{{ formatCurrency(selectedService.price) }} <span class="text-sm text-gray-500 font-medium normal-case">/ {{ formatPriceType(selectedService.price_type) }}</span></p>
                 </div>
              </div>
              <div class="hidden sm:block w-px bg-blue-200"></div>
              <div class="flex-1 flex items-center gap-3">
                 <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <Clock class="w-5 h-5" />
                 </div>
                 <div>
                   <p class="text-xs font-bold text-blue-600/70 uppercase tracking-wider">Est. Duration</p>
                   <p class="font-bold text-gray-900 text-base">{{ selectedService.duration }}</p>
                 </div>
              </div>
            </div>

            <h3 class="font-bold text-gray-900 text-lg mb-4 flex items-center gap-2">
               <CalendarCheck class="w-5 h-5 text-blue-500" /> Book This Service
            </h3>

            <div class="space-y-5">
              <div>
                <Label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">Project Details</Label>
                <Textarea 
                  v-model="bookingForm.description" 
                  placeholder="Describe what needs to be done. Include estimated sizes, conditions..." 
                  class="resize-none min-h-[90px] border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm bg-gray-50"
                />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <Label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">Pref. Date</Label>
                  <Input 
                    v-model="bookingForm.preferred_date" 
                    type="date" 
                    :min="minDate"
                    class="border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm h-11 bg-gray-50"
                  />
                </div>
                <div>
                  <Label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">Timeframe</Label>
                  <Select v-model="bookingForm.time_preference">
                    <SelectTrigger class="border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm h-11 bg-gray-50">
                      <SelectValue placeholder="Select" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="Morning (8AM - 12PM)">Morning (8AM - 12PM)</SelectItem>
                      <SelectItem value="Afternoon (1PM - 5PM)">Afternoon (1PM - 5PM)</SelectItem>
                      <SelectItem value="Flexible">Flexible / Anytime</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div>
                <Label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">Contact Number</Label>
                <Input 
                  v-model="bookingForm.contact_number" 
                  @input="bookingForm.contact_number = $event.target.value.replace(/[^0-9]/g, '').slice(0, 11)"
                  placeholder="e.g. 09123456789" 
                  class="border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl shadow-sm h-11 bg-gray-50"
                />
              </div>

              <div>
                <Label class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">Service Location</Label>
                <div class="space-y-2">
                  <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer transition-all duration-200" :class="addressMode === 'default' ? 'border-blue-500 bg-blue-50/30' : 'border-gray-200 bg-gray-50'">
                    <input type="radio" v-model="addressMode" value="default" class="hidden" />
                    <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center" :class="addressMode === 'default' ? 'border-blue-500' : 'border-gray-300'">
                      <div v-if="addressMode === 'default'" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    </div>
                    <span class="text-sm font-bold text-gray-900">Use Profile Address</span>
                  </label>

                  <label class="flex items-start gap-3 p-3 border rounded-xl cursor-pointer transition-all duration-200" :class="addressMode === 'custom' ? 'border-blue-500 bg-blue-50/30' : 'border-gray-200 bg-gray-50'">
                    <input type="radio" v-model="addressMode" value="custom" class="hidden" />
                    <div class="mt-1 w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0" :class="addressMode === 'custom' ? 'border-blue-500' : 'border-gray-300'">
                      <div v-if="addressMode === 'custom'" class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    </div>
                    <div class="w-full">
                      <span class="text-sm font-bold text-gray-900 block mb-2">Use Custom Address</span>
                      <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 max-h-0" enter-to-class="opacity-100 max-h-32">
                        <Textarea 
                          v-if="addressMode === 'custom'" 
                          v-model="customAddress" 
                          @click.stop
                          placeholder="Complete address details..." 
                          class="w-full p-3 border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white resize-none min-h-[70px]"
                        />
                      </transition>
                    </div>
                  </label>
                </div>
              </div>
            </div>

            <Button @click="confirmSubmission" :disabled="isSubmitting" class="w-full mt-8 bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/20 rounded-xl font-bold h-14 text-base transition-all">
              <span v-if="isSubmitting" class="flex items-center">
                <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div> Processing...
              </span>
              <span v-else>Submit Booking Request</span>
            </Button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="otherServices.length > 0" class="container mx-auto px-4 mt-16 pt-10 border-t border-gray-200">
      <div class="flex items-center justify-between mb-8">
         <h2 class="text-2xl font-black text-gray-900 tracking-tight">Other Services Available</h2>
         <Button variant="ghost" @click="router.push('/ECommerceClient/EccommerceServices')" class="text-blue-600 font-bold hover:bg-blue-50 rounded-xl">View All <ArrowRight class="w-4 h-4 ml-2" /></Button>
      </div>
      
      <div class="flex gap-6 overflow-x-auto pb-6 hide-scrollbar">
         <Card
        v-for="service in otherServices"
        :key="service.id"
        @click="router.push(`/ECommerceClient/ServiceDetails/${service.id}`)"
        class="min-w-[280px] w-[280px] md:min-w-[320px] md:w-[320px] bg-white rounded-2xl shadow-sm border border-gray-200 hover:shadow-xl hover:border-blue-400 hover:-translate-y-1 transition-all duration-300 overflow-hidden group cursor-pointer shrink-0"
        >
            <div class="h-40 relative overflow-hidden bg-gray-100 flex items-center justify-center shrink-0">
              <img 
                 v-if="service.image_paths && service.image_paths.length > 0"
                 :src="getImageUrl(service.image_paths[0])" 
                 class="object-cover w-full h-full transition-transform duration-700 group-hover:scale-105"
                 @error="handleImageError"
              />
              <ImageIcon v-else class="w-10 h-10 opacity-30 text-gray-400" />
              <Badge class="absolute top-2 left-2 bg-white/90 text-gray-800 backdrop-blur-md border-0 shadow-sm font-semibold text-xs py-0.5">
                  {{ service.category }}
              </Badge>
            </div>
            <CardContent class="p-4">
               <h3 class="font-bold text-base leading-tight text-gray-900 mb-1 line-clamp-1 group-hover:text-blue-600 transition-colors">{{ service.title }}</h3>
               <div class="flex items-center mb-2">
                  <Star class="w-3.5 h-3.5 text-amber-500 fill-amber-500 mr-1" />
                  <span class="text-xs font-bold text-gray-800">{{ service.average_rating > 0 ? service.average_rating.toFixed(1) : 'New' }}</span>
               </div>
               <div class="flex items-baseline text-blue-600 mt-2">
                  <span class="text-lg font-black tracking-tight">₱{{ formatCurrency(service.price) }}</span>
                  <span class="text-[10px] font-bold text-gray-500 ml-1">/ {{ formatPriceType(service.price_type) }}</span>
               </div>
            </CardContent>
          </Card>
      </div>
    </div>

    <Teleport to="body">
      <transition enter-active-class="transition duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="isAuthAlertOpen || replyModalOpen || showConfirmDialog" class="fixed inset-0 z-[9990] bg-gray-900/60 backdrop-blur-sm pointer-events-none"></div>
      </transition>

      <AlertDialog :open="isAuthAlertOpen" @update:open="isAuthAlertOpen = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
              Authentication Required
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3">
              You must be logged in to book a service. Please log in or create an account to continue.
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

      <AlertDialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
        <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10000]">
          <AlertDialogHeader>
            <AlertDialogTitle class="text-xl font-bold flex items-center gap-2">
              <CalendarCheck class="w-6 h-6 text-blue-500" />
              Confirm Service Request
            </AlertDialogTitle>
            <AlertDialogDescription class="text-gray-500 font-medium text-base mt-3">
              Are you sure you want to book <strong class="text-gray-800">{{ selectedService?.title }}</strong> for <strong class="text-gray-800">{{ formatDate(bookingForm.preferred_date) }} ({{ bookingForm.time_preference }})</strong>?
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter class="mt-6 sm:space-x-3">
            <AlertDialogCancel @click="showConfirmDialog = false" class="rounded-xl font-bold border-gray-200 text-gray-600 hover:bg-gray-50 h-11">Cancel</AlertDialogCancel>
            <AlertDialogAction @click="submitServiceRequest" class="rounded-xl font-bold bg-blue-600 hover:bg-blue-700 text-white h-11 px-6 shadow-md shadow-blue-600/20">
              Confirm Request
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>

      <Dialog :open="replyModalOpen" @update:open="(val) => !val && (replyModalOpen = false)">
        <DialogContent class="bg-white rounded-2xl shadow-2xl w-full max-w-md border-0 z-[10001]">
           <DialogHeader>
              <DialogTitle class="text-gray-900 font-bold flex items-center gap-2">
                 <Reply class="w-5 h-5 text-blue-500" />
                 Reply to Provider
              </DialogTitle>
           </DialogHeader>
           <div class="py-4 space-y-4">
              <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                 <p class="text-[10px] font-bold text-gray-500 uppercase mb-1">Provider's Note:</p>
                 <p class="text-sm text-gray-700 italic">"{{ reviewToReply?.reply }}"</p>
              </div>
              <div>
                 <Label class="text-xs font-bold text-gray-600 uppercase mb-2 block">Your Final Response</Label>
                 <Textarea 
                    v-model="clientReplyText" 
                    placeholder="Type your response here..." 
                    class="w-full resize-none min-h-[100px] border-gray-200 focus:ring-blue-500 focus:border-blue-500 rounded-xl"
                 />
              </div>
           </div>
           <div class="flex justify-end gap-2">
              <Button variant="outline" @click="replyModalOpen = false" class="border-gray-200">Cancel</Button>
              <Button @click="submitClientReply" :disabled="isSubmittingReply || !clientReplyText.trim()" class="bg-blue-600 hover:bg-blue-700 text-white">
                 <span v-if="isSubmittingReply" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Sending...</span>
                 <span v-else>Post Reply</span>
              </Button>
           </div>
        </DialogContent>
      </Dialog>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, defineProps, watch } from 'vue' 
import { useRoute, useRouter } from 'vue-router'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { 
  ArrowLeft,
  ArrowRight,
  CalendarCheck,
  Clock, 
  MapPin, 
  Phone, 
  User, 
  Image as ImageIcon,
  DollarSign,
  Star,
  MessageSquare,
  CornerDownRight,
  Reply,
  ShieldCheck,
  FileText,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
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

const route = useRoute()
const router = useRouter() 

const isLoading = ref(true)
const selectedService = ref(null)
const otherServices = ref([])
const currentImageIndex = ref(0)
const isSubmitting = ref(false)

// Confirm Dialog State
const showConfirmDialog = ref(false)

// Reply Modal State
const replyModalOpen = ref(false)
const reviewToReply = ref(null)
const clientReplyText = ref('')
const isSubmittingReply = ref(false)

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
  today.setDate(today.getDate() + 1)
  return today.toISOString().split('T')[0]
})

// FIX: Robust Dynamic Image URL Generator
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

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('en-US', options);
}

// Fetch main service details and the list of other services
const fetchPageData = async (id) => {
  try {
    isLoading.value = true
    currentImageIndex.value = 0
    
    // We fetch all services to find the current one and populate "Others"
    // (If your backend has a show/:id route, use that instead for selectedService)
    const response = await api.get('/client/services')
    
    if (response.data.success) {
      const allServices = response.data.data
      
      // Find the specific service by route ID
      selectedService.value = allServices.find(s => s.id == id) || null
      
      if (selectedService.value) {
         // Filter out the current service to show "Other Services"
         otherServices.value = allServices.filter(s => s.id != id).slice(0, 8) // Show up to 8
      }
    }
  } catch (error) {
    toast.error('Failed to load service details')
    console.error('Error fetching services:', error)
  } finally {
    isLoading.value = false
  }
}

// Image Navigation
const nextImage = () => {
  if (selectedService.value && selectedService.value.image_paths.length > 1) {
    currentImageIndex.value = (currentImageIndex.value + 1) % selectedService.value.image_paths.length
  }
}

const prevImage = () => {
  if (selectedService.value && selectedService.value.image_paths.length > 1) {
    currentImageIndex.value = currentImageIndex.value === 0 
      ? selectedService.value.image_paths.length - 1 
      : currentImageIndex.value - 1
  }
}

const openClientReplyModal = (review) => {
  reviewToReply.value = review
  clientReplyText.value = ''
  replyModalOpen.value = true
}

const submitClientReply = async () => {
  if (!clientReplyText.value.trim() || !reviewToReply.value) return;
  
  isSubmittingReply.value = true;
  try {
     const response = await api.post(`/client/services/reviews/${reviewToReply.value.id}/reply`, {
        client_reply: clientReplyText.value
     });

     if (response.data.success) {
        toast.success('Reply submitted successfully!');
        reviewToReply.value.client_reply = clientReplyText.value;
        replyModalOpen.value = false;
     }
  } catch (error) {
     toast.error(error.response?.data?.message || 'Failed to submit reply');
  } finally {
     isSubmittingReply.value = false;
  }
}

const confirmSubmission = () => {
  if (!props.user) {
     isAuthAlertOpen.value = true;
     return;
  }

  if (!bookingForm.value.description || !bookingForm.value.preferred_date || !bookingForm.value.time_preference || !bookingForm.value.contact_number) {
    toast.error('Missing Information', { description: 'Please fill in all required fields.' })
    return
  }

  if (!/^0[0-9]{10}$/.test(bookingForm.value.contact_number)) {
    toast.error('Invalid Contact Number', { description: 'Contact number must be exactly 11 digits and start with 0.' })
    return
  }

  if (addressMode.value === 'custom' && !customAddress.value.trim()) {
    toast.error('Missing Address', { description: 'Please provide your custom address.' })
    return
  }

  // If validation passes, show the confirmation dialog
  showConfirmDialog.value = true;
}

const submitServiceRequest = async () => {
  try {
    isSubmitting.value = true
    showConfirmDialog.value = false // Hide dialog immediately after confirming
    
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
      toast.success('Service Requested Successfully!', {
        description: 'The provider has been notified and will contact you soon.'
      })
      
      // Reset form
      bookingForm.value = { description: '', preferred_date: '', time_preference: '', contact_number: '' }
      
      // Redirect to bookings
      setTimeout(() => {
         router.push('/Clients/myServiceRequest')
      }, 1000)
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

// Re-fetch if they click a service from the "Other Services" carousel
watch(() => route.params.id, (newId) => {
  if (newId) {
    // Scroll to top when changing services
    window.scrollTo({ top: 0, behavior: 'smooth' });
    fetchPageData(newId);
  }
})

onMounted(() => {
  if (route.params.id) {
     fetchPageData(route.params.id)
  }
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
</style>