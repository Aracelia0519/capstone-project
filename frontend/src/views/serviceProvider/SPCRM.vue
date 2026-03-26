<template>
  <div class="min-h-screen p-4 md:p-6 text-slate-200">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Customer Feedback</h1>
          <p class="text-gray-400 text-sm md:text-base">Manage client reviews, respond to feedback, and control visibility.</p>
        </div>
      </div>
      
      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card class="bg-slate-900 border-slate-800 shadow-lg">
           <CardContent class="p-5 flex items-center justify-between">
              <div>
                 <p class="text-gray-400 text-xs md:text-sm font-semibold uppercase tracking-wider mb-1">Overall Rating</p>
                 <div class="flex items-end gap-2">
                    <p class="text-3xl md:text-4xl font-bold text-amber-400">{{ stats.average }}</p>
                    <p class="text-gray-500 mb-1 text-sm">/ 5.0</p>
                 </div>
              </div>
              <div class="w-12 h-12 rounded-xl bg-amber-500/10 flex items-center justify-center">
                 <Star class="w-6 h-6 text-amber-400 fill-amber-400" />
              </div>
           </CardContent>
        </Card>
        
        <Card class="bg-slate-900 border-slate-800 shadow-lg">
           <CardContent class="p-5 flex items-center justify-between">
              <div>
                 <p class="text-gray-400 text-xs md:text-sm font-semibold uppercase tracking-wider mb-1">Total Reviews</p>
                 <p class="text-3xl md:text-4xl font-bold text-white">{{ stats.total }}</p>
              </div>
              <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center">
                 <MessageSquare class="w-6 h-6 text-blue-400" />
              </div>
           </CardContent>
        </Card>

        <Card class="bg-slate-900 border-slate-800 shadow-lg flex items-center p-4">
           <div class="w-full space-y-1.5">
              <div v-for="star in [5,4,3,2,1]" :key="star" class="flex items-center gap-2 text-xs">
                 <span class="w-3 text-slate-400">{{ star }}</span>
                 <Star class="w-3 h-3 text-amber-500 fill-amber-500" />
                 <Progress 
                    :model-value="stats.total > 0 ? (stats.distribution[star] / stats.total) * 100 : 0" 
                    class="h-1.5 flex-1 bg-slate-800 [&>div]:bg-amber-500" 
                 />
                 <span class="w-5 text-right text-slate-500 text-[10px]">{{ stats.distribution[star] || 0 }}</span>
              </div>
           </div>
        </Card>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center py-20">
       <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="reviews.length === 0" class="text-center py-20 bg-slate-900 border border-slate-800 rounded-2xl">
       <MessageSquare class="w-12 h-12 text-slate-600 mx-auto mb-4" />
       <h3 class="text-lg font-bold text-white">No reviews yet</h3>
       <p class="text-slate-400 text-sm">Clients will be able to leave reviews once their service is completed.</p>
    </div>

    <div v-else class="space-y-4 md:space-y-6">
       <Card v-for="review in reviews" :key="review.id" :class="['bg-slate-900 border-slate-800 shadow-lg transition-all', review.is_hidden ? 'opacity-70' : '']">
          <CardContent class="p-5 md:p-6">
             <div class="flex justify-between items-start mb-4 border-b border-slate-800/50 pb-4">
                <div class="flex gap-3 items-center">
                   <Avatar class="w-10 h-10 border border-slate-700">
                      <AvatarFallback class="bg-blue-600 text-white font-bold">{{ getInitials(review.client?.first_name, review.client?.last_name) }}</AvatarFallback>
                   </Avatar>
                   <div>
                      <p class="font-bold text-white text-sm md:text-base">{{ review.client?.first_name }} {{ review.client?.last_name }}</p>
                      <p class="text-xs text-slate-400 mt-0.5">Service: <span class="text-blue-400">{{ review.service_request?.service_offering?.title || 'Custom Job' }}</span></p>
                   </div>
                </div>
                <div class="text-right">
                   <div class="flex items-center justify-end gap-1 mb-1">
                      <Star v-for="n in 5" :key="n" :class="['w-4 h-4', n <= review.rating ? 'text-amber-400 fill-amber-400' : 'text-slate-700']" />
                   </div>
                   <p class="text-[10px] text-slate-500 uppercase tracking-wider">{{ formatDate(review.created_at) }}</p>
                </div>
             </div>

             <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                   <Badge v-if="review.is_hidden" class="bg-red-500/10 text-red-400 border-red-500/30 text-[10px]">Hidden from public</Badge>
                   <Badge v-else class="bg-emerald-500/10 text-emerald-400 border-emerald-500/30 text-[10px]">Publicly Visible</Badge>
                </div>
                <p :class="['text-sm md:text-base leading-relaxed', review.is_hidden ? 'text-slate-500 italic' : '']">
                   "{{ review.comment || 'No specific comment provided.' }}"
                </p>
             </div>

             <div v-if="review.reply" class="bg-slate-950 border border-slate-800 rounded-xl p-4 ml-4 md:ml-8 mt-4 relative">
                <div class="absolute -left-3 top-4 text-slate-700">
                   <CornerDownRight class="w-5 h-5" />
                </div>
                <p class="text-xs font-bold text-blue-400 mb-1 flex items-center gap-2 uppercase tracking-wider">
                   Your Response
                </p>
                <p class="text-sm text-slate-300 whitespace-pre-wrap">{{ review.reply }}</p>
             </div>

             <div v-if="review.client_reply" class="bg-slate-900 border border-slate-700 rounded-xl p-4 ml-8 md:ml-12 mt-3 relative">
                <div class="absolute -left-3 top-4 text-slate-600">
                   <CornerDownRight class="w-5 h-5" />
                </div>
                <p class="text-xs font-bold text-amber-400 mb-1 flex items-center gap-2 uppercase tracking-wider">
                   Client's Follow-up
                </p>
                <p class="text-sm text-slate-300 whitespace-pre-wrap">{{ review.client_reply }}</p>
             </div>

             <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-slate-800">
                <Button 
                   variant="outline" 
                   size="sm" 
                   @click="toggleVisibility(review)"
                   :class="review.is_hidden ? 'border-emerald-800/50 text-emerald-400 hover:bg-emerald-900/20' : 'border-red-800/50 text-red-400 hover:bg-red-900/20'"
                >
                   <template v-if="review.is_hidden">
                      <Eye class="w-4 h-4 mr-2" /> Show Comment
                   </template>
                   <template v-else>
                      <EyeOff class="w-4 h-4 mr-2" /> Disable Comment
                   </template>
                </Button>

                <Button 
                   v-if="!review.reply"
                   @click="openReplyModal(review)"
                   size="sm" 
                   class="bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-900/20"
                >
                   <Reply class="w-4 h-4 mr-2" /> Write Reply
                </Button>
             </div>
          </CardContent>
       </Card>
    </div>

    <Dialog v-model:open="replyModalOpen">
       <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[90vw] md:max-w-[500px]">
          <DialogTitle class="text-white font-bold mb-2 flex items-center gap-2">
             <Reply class="w-5 h-5 text-blue-400" /> Respond to Client
          </DialogTitle>
          <div class="py-2 space-y-4">
             <div class="bg-slate-950 p-3 rounded-lg border border-slate-800 mb-2">
                <div class="flex items-center gap-1 mb-1">
                   <Star v-for="n in selectedReview?.rating || 0" :key="n" class="w-3 h-3 text-amber-500 fill-amber-500" />
                </div>
                <p class="text-xs text-slate-400 italic">"{{ selectedReview?.comment || 'No comment' }}"</p>
             </div>

             <div class="space-y-1">
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Your Official Reply</p>
                <textarea 
                   v-model="replyText" 
                   rows="4" 
                   class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-white focus:outline-none focus:border-blue-500/50 shadow-inner" 
                   placeholder="Thank the client for their feedback or address their concerns..."
                ></textarea>
             </div>
          </div>
          <div class="flex justify-end gap-2 mt-4">
             <Button variant="ghost" @click="replyModalOpen = false" class="text-gray-400 hover:text-white">Cancel</Button>
             <Button @click="submitReply" :disabled="isSubmitting || !replyText.trim()" class="bg-blue-600 hover:bg-blue-700 text-white">
                <span v-if="isSubmitting" class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Sending...</span>
                <span v-else>Post Reply</span>
             </Button>
          </div>
       </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'

import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Progress } from '@/components/ui/progress'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { Star, MessageSquare, Eye, EyeOff, Reply, CornerDownRight } from 'lucide-vue-next'

const reviews = ref([])
const stats = ref({ total: 0, average: 0, distribution: {} })
const isLoading = ref(true)

const replyModalOpen = ref(false)
const selectedReview = ref(null)
const replyText = ref('')
const isSubmitting = ref(false)

const getInitials = (first, last) => {
   if (!first) return 'U'
   return `${first.charAt(0)}${last ? last.charAt(0) : ''}`.toUpperCase()
}

const formatDate = (dateString) => {
   const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }
   return new Date(dateString).toLocaleDateString('en-US', options)
}

const fetchReviews = async () => {
   isLoading.value = true
   try {
      const res = await api.get('/service-provider/crm/reviews')
      if (res.data.success) {
         reviews.value = res.data.data.reviews
         stats.value = res.data.data.stats
      }
   } catch (error) {
      toast.error('Failed to load reviews')
   } finally {
      isLoading.value = false
   }
}

const openReplyModal = (review) => {
   selectedReview.value = review
   replyText.value = review.reply || ''
   replyModalOpen.value = true
}

const submitReply = async () => {
   if (!replyText.value.trim()) return
   isSubmitting.value = true
   try {
      const res = await api.post(`/service-provider/crm/reviews/${selectedReview.value.id}/reply`, {
         reply: replyText.value
      })
      if (res.data.success) {
         toast.success(res.data.message)
         replyModalOpen.value = false
         fetchReviews() // Refresh list to reflect changes
      }
   } catch (error) {
      toast.error(error.response?.data?.message || 'Failed to submit reply')
   } finally {
      isSubmitting.value = false
   }
}

const toggleVisibility = async (review) => {
   try {
      const res = await api.patch(`/service-provider/crm/reviews/${review.id}/toggle-visibility`)
      if (res.data.success) {
         review.is_hidden = res.data.is_hidden
         if (review.is_hidden) {
            toast.error(res.data.message)
         } else {
            toast.success(res.data.message)
         }
      }
   } catch (error) {
      toast.error('Failed to update visibility')
   }
}

onMounted(() => {
   fetchReviews()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(30, 41, 59, 0.3); border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(to bottom, #3b82f6, #60a5fa); border-radius: 3px; }
</style>