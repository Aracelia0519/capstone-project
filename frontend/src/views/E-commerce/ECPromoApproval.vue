<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '@/utils/axios'
import { toast } from 'vue-sonner'
import { 
  Search, CheckCircle2, XCircle, Eye, Calendar, Tag, Package, AlertCircle, Loader2, Clock, CheckCircle
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter
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

const pendingPromotions = ref([])
const stats = ref({ total_pending: 0, total_active: 0, total_rejected: 0 })
const loading = ref(false)
const actionLoading = ref(false)
const searchQuery = ref('')
const selectedPromo = ref(null)
const showReviewModal = ref(false)
const showConfirmDialog = ref(false)
const pendingAction = ref({ type: '', id: null })

const fetchPendingPromotions = async () => {
  loading.value = true
  try {
    const res = await axios.get('/crm/promotions-approval')
    if (res.data.status === 'success') {
      pendingPromotions.value = res.data.data
      stats.value = res.data.stats
    }
  } catch (err) {
    toast.error('Failed to load pending promotions')
  } finally {
    loading.value = false
  }
}

const confirmAction = (type, id) => {
  pendingAction.value = { type, id }
  showConfirmDialog.value = true
}

const executeAction = async () => {
  actionLoading.value = true
  const { type, id } = pendingAction.value
  try {
    const res = await axios.post(`/crm/promotions-approval/${id}/${type}`)
    if (res.data.status === 'success') {
      toast.success(res.data.message)
      showConfirmDialog.value = false
      showReviewModal.value = false
      fetchPendingPromotions()
    }
  } catch (err) {
    toast.error(`Error: ${err.response?.data?.message || 'Action failed'}`)
  } finally {
    actionLoading.value = false
  }
}

onMounted(fetchPendingPromotions)

const filteredPromotions = computed(() => {
  return pendingPromotions.value.filter(p => 
    p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    p.code.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const openReview = (promo) => {
  selectedPromo.value = promo
  showReviewModal.value = true
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}
</script>

<template>
  <div class="p-4 md:p-6 w-full max-w-8xl mx-auto space-y-6 md:space-y-8 text-white">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-orange-400 to-red-400">
          Promotion Approval Dashboard
        </h1>
        <p class="text-sm text-gray-400">Manage and authorize upcoming discount campaigns</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
      <Card class="bg-black/20 border-white/10 overflow-hidden">
        <CardContent class="p-6 flex items-center gap-4">
          <div class="p-3 bg-orange-500/20 rounded-xl text-orange-400"><Clock class="w-6 h-6" /></div>
          <div>
            <p class="text-sm text-gray-400">Pending Approval</p>
            <h3 class="text-2xl font-bold text-white">{{ stats.total_pending }}</h3>
          </div>
        </CardContent>
      </Card>
      <Card class="bg-black/20 border-white/10 overflow-hidden">
        <CardContent class="p-6 flex items-center gap-4">
          <div class="p-3 bg-green-500/20 rounded-xl text-green-400"><CheckCircle class="w-6 h-6" /></div>
          <div>
            <p class="text-sm text-gray-400">Active Promotions</p>
            <h3 class="text-2xl font-bold text-white">{{ stats.total_active }}</h3>
          </div>
        </CardContent>
      </Card>
      <Card class="bg-black/20 border-white/10 overflow-hidden">
        <CardContent class="p-6 flex items-center gap-4">
          <div class="p-3 bg-red-500/20 rounded-xl text-red-400"><XCircle class="w-6 h-6" /></div>
          <div>
            <p class="text-sm text-gray-400">Rejected Requests</p>
            <h3 class="text-2xl font-bold text-white">{{ stats.total_rejected }}</h3>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="bg-black/20 backdrop-blur-md border-white/10 rounded-2xl overflow-hidden shadow-2xl">
      <CardContent class="p-0">
        <div class="p-4 md:p-5 border-b border-white/5 bg-white/[0.02]">
          <div class="relative w-full md:w-96">
            <Search class="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-gray-500 w-4 h-4" />
            <Input v-model="searchQuery" placeholder="Search by name or code..." class="pl-10 bg-black/40 border-white/10 text-white" />
          </div>
        </div>

        <div v-if="loading" class="p-20 flex flex-col items-center justify-center space-y-4">
          <Loader2 class="w-10 h-10 text-orange-500 animate-spin" />
          <p class="text-gray-400 animate-pulse">Loading requests...</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-300 min-w-[900px]">
            <thead class="bg-black/40 text-xs uppercase font-semibold text-gray-400 tracking-wider">
              <tr>
                <th class="px-6 py-5">Promotion</th>
                <th class="px-6 py-5">Benefit</th>
                <th class="px-6 py-5">Requested By</th>
                <th class="px-6 py-5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="promo in filteredPromotions" :key="promo.id" class="hover:bg-white/[0.03] transition-colors">
                <td class="px-6 py-4">
                  <div class="font-bold text-gray-100">{{ promo.name }}</div>
                  <div class="text-[11px] font-mono text-orange-400/80 uppercase tracking-widest">{{ promo.code }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold text-white">
                    {{ promo.type === 'percentage' ? `${promo.discount_value}% Off` : `₱${promo.discount_value} Off` }}
                  </div>
                  <div class="text-[10px] text-gray-500">Limit: {{ promo.usage_limit }}</div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-orange-500/20 flex items-center justify-center text-[10px] font-bold text-orange-400 uppercase">
                      {{ promo.creator.first_name[0] }}{{ promo.creator.last_name[0] }}
                    </div>
                    <div>
                      <div class="text-xs font-medium">{{ promo.creator.first_name }} {{ promo.creator.last_name }}</div>
                      <div class="text-[10px] text-gray-500">{{ formatDate(promo.created_at) }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <Button @click="openReview(promo)" size="sm" variant="ghost" class="text-orange-400 hover:bg-orange-500/10">
                    <Eye class="w-4 h-4 mr-2" /> Review
                  </Button>
                </td>
              </tr>
              <tr v-if="filteredPromotions.length === 0">
                <td colspan="4" class="px-6 py-20 text-center text-gray-500 italic">No pending requests found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showReviewModal" @update:open="val => showReviewModal = val">
      <DialogContent class="bg-[#121212] border-white/10 text-white sm:max-w-[550px] rounded-2xl shadow-2xl">
        <DialogHeader v-if="selectedPromo">
          <DialogTitle class="text-xl font-bold flex items-center gap-2">
            <AlertCircle class="w-5 h-5 text-orange-500" /> Review Request
          </DialogTitle>
          <DialogDescription class="text-gray-400">Created by {{ selectedPromo.creator.full_name }}</DialogDescription>
        </DialogHeader>

        <div v-if="selectedPromo" class="grid grid-cols-2 gap-6 py-4 border-y border-white/5 my-4">
          <div class="space-y-4">
            <div><label class="text-[10px] uppercase text-gray-500 font-bold tracking-widest">Code</label><div class="text-lg font-mono text-orange-400">{{ selectedPromo.code }}</div></div>
            <div><label class="text-[10px] uppercase text-gray-500 font-bold tracking-widest">Type</label><div class="text-base font-semibold capitalize">{{ selectedPromo.type }}</div></div>
          </div>
          <div class="space-y-4">
            <div><label class="text-[10px] uppercase text-gray-500 font-bold tracking-widest">Start - End</label><div class="text-sm font-medium">{{ formatDate(selectedPromo.start_date) }} - {{ formatDate(selectedPromo.end_date) }}</div></div>
            <div><label class="text-[10px] uppercase text-gray-500 font-bold tracking-widest">Product</label><div class="text-sm font-medium">{{ selectedPromo.product?.name || 'Storewide' }}</div></div>
          </div>
        </div>

        <DialogFooter class="flex flex-col sm:flex-row gap-3">
          <Button @click="confirmAction('reject', selectedPromo.id)" variant="outline" class="w-full sm:w-auto border-red-500/20 text-red-400 hover:bg-red-500/10">
            <XCircle class="w-4 h-4 mr-2" /> Reject
          </Button>
          <Button @click="confirmAction('approve', selectedPromo.id)" class="w-full sm:w-auto bg-gradient-to-r from-green-600 to-emerald-600">
            <CheckCircle2 class="w-4 h-4 mr-2" /> Approve Promotion
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showConfirmDialog" @update:open="val => showConfirmDialog = val">
      <AlertDialogContent class="bg-[#121212] border-white/10 text-white rounded-2xl">
        <AlertDialogHeader>
          <AlertDialogTitle>Confirm {{ pendingAction.type === 'approve' ? 'Approval' : 'Rejection' }}</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400">
            Are you sure you want to {{ pendingAction.type }} this promotion? This action will update its status immediately.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showConfirmDialog = false" class="bg-transparent border-white/10 text-white">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeAction" :disabled="actionLoading" 
            :class="pendingAction.type === 'approve' ? 'bg-green-600' : 'bg-red-600'">
            <Loader2 v-if="actionLoading" class="w-4 h-4 animate-spin mr-2" />
            {{ pendingAction.type === 'approve' ? 'Yes, Approve' : 'Yes, Reject' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>