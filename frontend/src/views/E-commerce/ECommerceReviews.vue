<template>
  <div class="ecommerce-reviews p-4 md:p-6 min-h-screen relative">
    <Toaster richColors position="top-right" expand />

    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Reviews & Ratings</h1>
          <h2 class="text-gray-300">Collect and manage customer feedback</h2>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
          <div class="flex items-center bg-gray-800/50 px-4 py-2 rounded-lg border border-gray-700 shadow-sm">
            <svg class="w-6 h-6 text-amber-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <div class="flex flex-col">
              <span class="text-white font-black text-xl leading-none">{{ averageRating }}</span>
              <span class="text-gray-400 text-xs font-medium uppercase tracking-wider mt-0.5">Average Rating</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
    </div>

    <div v-else>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <Card class="bg-gray-900/50 border-gray-800 text-white hover:bg-gray-800/50 transition-colors shadow-sm">
          <CardContent class="p-5 flex flex-col justify-center items-center text-center h-full">
            <div class="text-4xl font-black text-blue-400 mb-1">{{ reviews.length }}</div>
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Reviews</h3>
          </CardContent>
        </Card>
        
        <Card class="bg-gray-900/50 border-gray-800 text-white hover:bg-gray-800/50 transition-colors shadow-sm">
          <CardContent class="p-5 flex flex-col justify-center items-center text-center h-full">
            <div class="text-4xl font-black text-amber-400 mb-1">{{ fiveStarCount }}</div>
            <div class="text-xs font-semibold uppercase tracking-wider text-gray-400 flex items-center">
            <h2>
              <svg class="w-3.5 h-3.5 text-amber-400 mr-1.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
            </h2>
            <h2>
              5 Star Reviews

            </h2>
              
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-gray-900/50 border-gray-800 text-white hover:bg-gray-800/50 transition-colors shadow-sm">
          <CardContent class="p-5 flex flex-col justify-center items-center text-center h-full">
            <div class="text-4xl font-black text-emerald-400 mb-1">{{ publishedCount }}</div>
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Published</h2>
          </CardContent>
        </Card>

        <Card class="bg-gray-900/50 border-gray-800 text-white hover:bg-gray-800/50 transition-colors relative overflow-hidden shadow-sm">
          <div v-if="pendingCount > 0" class="absolute top-0 right-0 w-16 h-16 bg-amber-500/10 rounded-bl-full border-b border-l border-amber-500/20"></div>
          <CardContent class="p-5 flex flex-col justify-center items-center text-center h-full relative z-10">
            <div class="text-4xl font-black text-gray-200 mb-1" :class="{'text-amber-400': pendingCount > 0}">{{ pendingCount }}</div>
            <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pending Approval</h2>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        
        <Card class="lg:col-span-1 bg-gray-900/50 border-gray-800 text-white shadow-sm h-full flex flex-col">
          <CardContent class="p-5 flex-1 flex flex-col">
            <h3 class="text-xs font-bold text-gray-400 mb-4 uppercase tracking-widest">Rating Distribution</h3>
            <div class="space-y-3.5 flex-1 flex flex-col justify-center">
              <div v-for="dist in ratingDistribution" :key="dist.stars" class="flex items-center text-sm group">
                <span class="w-10 flex items-center text-gray-300 font-bold">
                  {{ dist.stars }} <Star class="w-3.5 h-3.5 text-amber-400 ml-1.5 fill-current" />
                </span>
                <div class="flex-1 h-2.5 mx-3 bg-gray-800 rounded-full overflow-hidden relative">
                  <div 
                    class="absolute top-0 left-0 h-full bg-amber-400 rounded-full transition-all duration-1000 ease-out" 
                    :style="{ width: dist.percentage + '%' }"
                  ></div>
                </div>
                <span class="w-14 text-right text-white text-xs font-bold font-mono">
                  {{ dist.count }} <span class="text-white font-normal">({{ dist.percentage }}%)</span>
                </span>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="lg:col-span-3 bg-gray-900/50 border-gray-800 shadow-sm h-full">
          <CardContent class="p-5 h-full flex flex-col justify-center">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
              <div class="space-y-2">
                <Label class="text-xs font-bold text-white uppercase tracking-widest">Search</Label>
                <div class="relative">
                  <Search class="absolute left-3 top-3 h-4 w-4 text-gray-500" />
                  <Input type="text" v-model="searchQuery" placeholder="Product or client..." class="bg-gray-800 border-gray-700 text-white pl-9 placeholder:text-gray-500 rounded-xl" />
                </div>
              </div>
              
              <div class="space-y-2">
                <Label class="text-xs font-bold text-white uppercase tracking-widest">Rating Filter</Label>
                <Select v-model="selectedRating">
                  <SelectTrigger class="bg-gray-800 border-gray-700 text-white rounded-xl">
                    <SelectValue placeholder="All Ratings" />
                  </SelectTrigger>
                  <SelectContent class="bg-gray-800 border-gray-700 text-white rounded-xl">
                    <SelectItem value="0">All Ratings</SelectItem>
                    <SelectItem value="5">5 Stars only</SelectItem>
                    <SelectItem value="4">4 Stars & up</SelectItem>
                    <SelectItem value="3">3 Stars & up</SelectItem>
                    <SelectItem value="2">2 Stars & up</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div class="space-y-2">
                <Label class="text-xs font-bold text-white uppercase tracking-widest">Status</Label>
                <Select v-model="selectedStatus">
                  <SelectTrigger class="bg-gray-800 border-gray-700 text-white rounded-xl">
                    <SelectValue placeholder="All Statuses" />
                  </SelectTrigger>
                  <SelectContent class="bg-gray-800 border-gray-700 text-white rounded-xl">
                    <SelectItem value="all">All Statuses</SelectItem>
                    <SelectItem value="published">Published</SelectItem>
                    <SelectItem value="pending">Pending</SelectItem>
                    <SelectItem value="hidden">Hidden</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div class="flex items-end">
                <Button @click="resetFilters" variant="outline" class="w-full bg-gray-800 border-gray-700 text-white hover:bg-gray-700 hover:text-white rounded-xl font-bold">
                  Reset Filters
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="space-y-4">
        <div v-if="filteredReviews.length === 0" class="text-center py-12 bg-gray-900/30 rounded-2xl border border-gray-800">
          <MessageSquare class="w-12 h-12 text-gray-600 mx-auto mb-3" />
          <h3 class="text-lg font-bold text-gray-300">No reviews found</h3>
          <p class="text-gray-500">Try adjusting your filters</p>
        </div>

        <Card v-for="review in filteredReviews" :key="review.id" class="bg-gray-900/50 border-gray-800 hover:border-gray-700 transition-colors overflow-hidden rounded-2xl shadow-sm">
          <div class="flex flex-col md:flex-row border-b border-gray-800/50">
            
            <div class="md:w-1/3 lg:w-1/4 p-4 md:p-5 bg-gray-800/20 border-r-0 md:border-r border-gray-800">
              <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-blue-500 flex items-center justify-center text-white font-bold shadow-inner shrink-0">
                  {{ review.clientInitials }}
                </div>
                <div class="min-w-0">
                  <div class="text-white font-bold truncate">{{ review.client }}</div>
                  <div class="text-xs text-gray-500 font-medium truncate mt-0.5">Order: {{ review.orderId }}</div>
                </div>
              </div>
              
              <div class="flex items-center p-2.5 bg-gray-800/50 rounded-xl border border-gray-700/50">
                <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center mr-3 overflow-hidden shrink-0 shadow-sm">
                  <img v-if="review.productImage" :src="review.productImage" class="w-full h-full object-cover" />
                  <Package v-else class="w-5 h-5 text-gray-400" />
                </div>
                <div class="min-w-0">
                  <div class="text-sm text-gray-200 font-bold truncate">{{ review.product }}</div>
                  <div class="flex items-center mt-1 text-amber-400">
                    <Star v-for="i in 5" :key="i" class="w-3.5 h-3.5 drop-shadow-sm" :class="i <= review.rating ? 'fill-current' : 'text-gray-600'" />
                  </div>
                </div>
              </div>
            </div>
            
            <div class="p-4 md:p-5 flex-1 flex flex-col justify-between min-w-0">
              <div>
                <div class="flex justify-between items-start mb-2">
                  <div class="flex items-center space-x-3">
                    <Badge :class="statusClasses[review.status]" class="border-0 capitalize text-xs font-bold px-2.5 py-0.5">
                      {{ review.status }}
                    </Badge>
                    <span class="text-xs font-medium text-white">{{ review.date }}</span>
                  </div>
                  
                  <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                      <Button variant="ghost" size="icon" class="text-gray-400 hover:text-white h-8 w-8 -mr-2 shrink-0 rounded-full">
                        <MoreVertical class="w-4 h-4" />
                      </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-48 bg-gray-900 border-gray-700 text-gray-300 rounded-xl shadow-xl">
                      <DropdownMenuItem @click="requirePermission('manage', () => updateStatus(review.id, 'published'))" v-if="review.status !== 'published'" class="hover:bg-gray-800 focus:bg-gray-800 cursor-pointer rounded-lg m-1 font-medium">
                        <CheckCircle class="w-4 h-4 mr-2 text-emerald-400" /> Publish Review
                      </DropdownMenuItem>
                      <DropdownMenuItem @click="requirePermission('manage', () => updateStatus(review.id, 'hidden'))" v-if="review.status !== 'hidden'" class="hover:bg-gray-800 focus:bg-gray-800 cursor-pointer rounded-lg m-1 font-medium">
                        <EyeOff class="w-4 h-4 mr-2 text-gray-400" /> Hide Review
                      </DropdownMenuItem>
                      <DropdownMenuSeparator class="bg-gray-800" />
                      <DropdownMenuItem @click="requirePermission('manage', () => showResponseForm(review))" class="hover:bg-gray-800 focus:bg-gray-800 cursor-pointer rounded-lg m-1 font-medium">
                        <Reply class="w-4 h-4 mr-2 text-blue-400" /> Reply to Customer
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </div>
                
                <div class="mt-3.5 text-gray-300 text-sm leading-relaxed whitespace-pre-wrap break-words overflow-hidden">
                  <p v-if="review.comment" class="italic font-medium">"{{ review.comment }}"</p>
                  <p v-else class="text-white-500 italic">No written comment provided.</p>
                </div>
              </div>
              
              <div v-if="review.response" class="mt-5 bg-gray-800/40 rounded-xl p-4 border border-gray-700/50 relative">
                <div class="absolute -top-3 left-4 bg-gray-900 px-2 flex items-center text-xs text-blue-400 font-bold uppercase tracking-widest border border-gray-700/50 rounded-full">
                  <Reply class="w-3 h-3 mr-1.5" />
                  Your Response
                </div>
                <div class="flex justify-between items-start mt-1">
                  <div class="text-sm text-gray-300 whitespace-pre-wrap break-words font-medium leading-relaxed">
                    {{ review.response }}
                  </div>
                  <span class="text-[10px] font-bold text-white shrink-0 ml-4 mt-0.5">{{ review.responseDate }}</span>
                </div>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>

    <Dialog :open="!!respondingToReview" @update:open="(val) => !val && (respondingToReview = null)">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-[500px] rounded-2xl shadow-2xl">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold">Respond to Review</DialogTitle>
          <DialogDescription class="text-gray-400 font-medium">
            Your response will be visible to the customer and attached to the product.
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-5 py-4">
          <div class="bg-gray-800/50 p-4 rounded-xl border border-gray-700 shadow-inner">
            <div class="flex items-center text-amber-400 mb-2.5">
              <Star v-for="i in 5" :key="i" class="w-3.5 h-3.5 drop-shadow-sm" :class="i <= (respondingToReview?.rating || 0) ? 'fill-current' : 'text-gray-600'" />
              <span class="ml-2.5 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ respondingToReview?.client }}</span>
            </div>
            <p class="text-sm text-gray-300 italic break-words font-medium">"{{ respondingToReview?.comment }}"</p>
          </div>
          
          <div class="space-y-2">
            <Label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Your Reply</Label>
            <Textarea 
              v-model="responseForm.text" 
              placeholder="Thank you for your feedback..." 
              class="min-h-[140px] bg-gray-800 border-gray-700 text-white focus:ring-2 focus:ring-blue-500 resize-none rounded-xl p-4 shadow-inner"
            />
          </div>
        </div>
        
        <DialogFooter class="gap-3 sm:gap-0 pt-2">
          <Button variant="outline" @click="respondingToReview = null" class="bg-transparent border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white rounded-xl font-bold">
            Cancel
          </Button>
          <Button @click="requirePermission('manage', submitResponse)" :disabled="!responseForm.text || isProcessing" class="bg-blue-600 hover:bg-blue-700 text-white border-0 rounded-xl font-bold px-6 shadow-lg shadow-blue-600/20">
            {{ isProcessing ? 'Submitting...' : 'Submit Reply' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { Toaster, toast } from 'vue-sonner' // Added Toaster import
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { 
  Star, 
  MoreVertical, 
  CheckCircle, 
  EyeOff, 
  Reply, 
  Search,
  Package,
  MessageSquare
} from 'lucide-vue-next'

// State
const reviews = ref([])
const isLoading = ref(true)
const isProcessing = ref(false)

const searchQuery = ref('')
const selectedRating = ref('0')
const selectedStatus = ref('all')

const respondingToReview = ref(null)
const responseForm = ref({ text: '' })

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// RBAC Action Interceptor
const requirePermission = (action, callback) => {
  const permKey = `can_${action}`
  
  if (!permissions.value[permKey]) {
    toast.error(`Access Denied`, {
        description: `You do not have permission to perform this action on reviews.`,
        duration: 5000,
        style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
    });
    return;
  }
  if (callback) callback();
}

const statusClasses = {
  'published': 'bg-emerald-500/20 text-emerald-400',
  'pending': 'bg-amber-500/20 text-amber-400',
  'hidden': 'bg-gray-700/50 text-gray-400'
}

// Fetch Reviews
const fetchReviews = async () => {
  try {
    isLoading.value = true
    const response = await api.get('/operation-distributor/reviews')
    if (response.data.success) {
      reviews.value = response.data.data
      
      // Inject permissions for RBAC
      if (response.data.permissions) {
        permissions.value = response.data.permissions
      }
    }
  } catch (error) {
    console.error('Error fetching reviews:', error)
    if (error.response?.status === 403) {
      toast.error('Access Denied', {
         description: error.response.data.message || 'You lack permissions to view these reviews.',
         style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
      })
    } else {
      toast.error('Failed to load reviews')
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchReviews()
})

// Computations
const averageRating = computed(() => {
  if (reviews.value.length === 0) return '0.0'
  const sum = reviews.value.reduce((acc, r) => acc + r.rating, 0)
  return (sum / reviews.value.length).toFixed(1)
})

const fiveStarCount = computed(() => reviews.value.filter(r => r.rating === 5).length)
const publishedCount = computed(() => reviews.value.filter(r => r.status === 'published').length)
const pendingCount = computed(() => reviews.value.filter(r => r.status === 'pending').length)

// Rating Distribution logic
const ratingDistribution = computed(() => {
  const dist = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
  
  reviews.value.forEach(r => {
    if (dist[r.rating] !== undefined) {
      dist[r.rating]++
    }
  })
  
  const total = reviews.value.length
  
  return [5, 4, 3, 2, 1].map(star => ({
    stars: star,
    count: dist[star],
    percentage: total > 0 ? Math.round((dist[star] / total) * 100) : 0
  }))
})

const filteredReviews = computed(() => {
  return reviews.value.filter(review => {
    const matchesSearch = searchQuery.value === '' || 
      review.product.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      review.client.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      (review.comment && review.comment.toLowerCase().includes(searchQuery.value.toLowerCase()))
    
    const matchesRating = selectedRating.value === '0' || review.rating >= parseInt(selectedRating.value)
    
    const matchesStatus = selectedStatus.value === 'all' || review.status === selectedStatus.value
    
    return matchesSearch && matchesRating && matchesStatus
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedRating.value = '0'
  selectedStatus.value = 'all'
}

// Actions
const updateStatus = async (id, newStatus) => {
  try {
    const response = await api.put(`/operation-distributor/reviews/${id}/status`, { status: newStatus })
    if (response.data.success) {
      const review = reviews.value.find(r => r.id === id)
      if (review) review.status = newStatus
      toast.success(`Review successfully ${newStatus}`)
    }
  } catch (error) {
    if (error.response?.status === 403) {
      toast.error('Action Restricted', { description: error.response.data.message || 'You do not have permission to update reviews.' })
    } else {
      toast.error('Failed to update status')
    }
  }
}

const showResponseForm = (review) => {
  respondingToReview.value = review
  responseForm.value.text = review.response || ''
}

const submitResponse = async () => {
  if (!respondingToReview.value || !responseForm.value.text) return
  
  isProcessing.value = true
  try {
    const response = await api.post(`/operation-distributor/reviews/${respondingToReview.value.id}/respond`, {
      response: responseForm.value.text
    })
    
    if (response.data.success) {
      respondingToReview.value.response = responseForm.value.text
      respondingToReview.value.responseDate = new Date().toISOString().split('T')[0]
      respondingToReview.value.status = 'published' // Auto publish on reply
      toast.success('Response added successfully')
      respondingToReview.value = null
      responseForm.value.text = ''
    }
  } catch (error) {
    if (error.response?.status === 403) {
      toast.error('Action Restricted', { description: error.response.data.message || 'You do not have permission to respond to reviews.' })
    } else {
      toast.error('Failed to submit response')
    }
  } finally {
    isProcessing.value = false
  }
}
</script>