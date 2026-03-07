<template>
  <div class="p-4 md:p-6 w-full max-w-8xl mx-auto space-y-6 md:space-y-8 relative">
    <Toaster richColors position="top-right" expand />

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400 mb-1 md:mb-2">
          Promotions & Discounts
        </h1>
        <p class="text-sm md:text-base text-gray-400">Increase sales through targeted promotions and discounts</p>
      </div>
      <Button v-if="permissions.can_create" 
              @click="showCreateModal = true" 
              class="w-full md:w-auto bg-gradient-to-r from-orange-500 to-red-500 text-white border-0 hover:from-orange-400 hover:to-red-400 transition-all duration-300 shadow-lg shadow-orange-500/20 rounded-lg px-6">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg>
        Create Promotion
      </Button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
      <Card class="relative overflow-hidden bg-gradient-to-br from-orange-500/10 to-red-500/10 border-white/5 rounded-2xl hover:border-orange-500/30 transition-colors">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-2 md:p-2.5 bg-orange-500/20 rounded-xl shadow-inner">
              <svg class="w-5 h-5 md:w-6 md:h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11" />
              </svg>
            </div>
            <span class="text-[10px] md:text-xs font-semibold px-2.5 py-1 bg-black/40 rounded-full text-orange-400 border border-orange-500/20">Total</span>
          </div>
          <div>
            <p class="text-2xl md:text-3xl font-bold text-white mb-1">{{ promotions.length }}</p>
            <p class="text-xs md:text-sm text-gray-400 font-medium">Total Promotions</p>
          </div>
        </CardContent>
      </Card>
      
      <Card class="relative overflow-hidden bg-gradient-to-br from-green-500/10 to-emerald-500/10 border-white/5 rounded-2xl hover:border-green-500/30 transition-colors">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-2 md:p-2.5 bg-green-500/20 rounded-xl shadow-inner">
              <svg class="w-5 h-5 md:w-6 md:h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span class="text-[10px] md:text-xs font-semibold px-2.5 py-1 bg-black/40 rounded-full text-green-400 border border-green-500/20">Active</span>
          </div>
          <div>
            <p class="text-2xl md:text-3xl font-bold text-white mb-1">{{ activePromotions }}</p>
            <p class="text-xs md:text-sm text-gray-400 font-medium">Currently Active</p>
          </div>
        </CardContent>
      </Card>
      
      <Card class="relative overflow-hidden bg-gradient-to-br from-blue-500/10 to-indigo-500/10 border-white/5 rounded-2xl hover:border-blue-500/30 transition-colors">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-2 md:p-2.5 bg-blue-500/20 rounded-xl shadow-inner">
              <svg class="w-5 h-5 md:w-6 md:h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span class="text-[10px] md:text-xs font-semibold px-2.5 py-1 bg-black/40 rounded-full text-blue-400 border border-blue-500/20">Pending</span>
          </div>
          <div>
            <p class="text-2xl md:text-3xl font-bold text-white mb-1">{{ pendingPromotions }}</p>
            <p class="text-xs md:text-sm text-gray-400 font-medium">Pending Approval</p>
          </div>
        </CardContent>
      </Card>

      <Card class="relative overflow-hidden bg-gradient-to-br from-purple-500/10 to-pink-500/10 border-white/5 rounded-2xl hover:border-purple-500/30 transition-colors">
        <CardContent class="p-4 md:p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="p-2 md:p-2.5 bg-purple-500/20 rounded-xl shadow-inner">
              <svg class="w-5 h-5 md:w-6 md:h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
              </svg>
            </div>
            <span class="text-[10px] md:text-xs font-semibold px-2.5 py-1 bg-black/40 rounded-full text-purple-400 border border-purple-500/20">Uses</span>
          </div>
          <div>
            <p class="text-2xl md:text-3xl font-bold text-white mb-1">{{ totalUses }}</p>
            <p class="text-xs md:text-sm text-gray-400 font-medium">Total Code Uses</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="bg-black/20 backdrop-blur-sm border-white/10 rounded-2xl overflow-hidden shadow-2xl">
      <CardContent class="p-0">
        <div class="p-4 md:p-5 border-b border-white/5 flex flex-col md:flex-row gap-4 items-center justify-between bg-white/[0.02]">
          <div class="relative w-full md:w-96">
            <Search class="absolute left-3.5 top-1/2 transform -translate-y-1/2 text-white w-4 h-4" />
            <Input 
              v-model="searchQuery"
              placeholder="Search promotions by name or code..." 
              class="pl-10 bg-black/40 border-white/10 text-white placeholder:text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 w-full rounded-lg transition-all"
            />
          </div>
        </div>

        <div v-if="loading" class="p-16 flex flex-col items-center justify-center space-y-4">
          <div class="animate-spin rounded-full h-10 w-10 border-2 border-orange-500/20 border-t-orange-500"></div>
          <p class="text-gray-500 text-sm animate-pulse">Loading promotions...</p>
        </div>
        
        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm text-gray-300 min-w-[800px]">
            <thead class="bg-black/40 text-xs uppercase font-semibold text-gray-400 tracking-wider">
              <tr>
                <th class="px-6 py-5">Promotion Name</th>
                <th class="px-6 py-5">Type & Discount</th>
                <th class="px-6 py-5">Promo Code</th>
                <th class="px-6 py-5">Usage Status</th>
                <th class="px-6 py-5">Valid Period</th>
                <th class="px-6 py-5">Status</th>
                <th class="px-6 py-5 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr v-for="promo in filteredPromotions" :key="promo.id" class="hover:bg-white/[0.02] transition-colors group">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-semibold text-gray-100 group-hover:text-white transition-colors">{{ promo.name }}</div>
                  <div class="text-xs text-gray-500 mt-0.5" v-if="promo.product">For: {{ promo.product.name }}</div>
                  <div class="text-[11px] text-gray-500 mt-0.5 uppercase tracking-wider font-semibold" v-else>Storewide Promotion</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-xs text-gray-400 mb-1">{{ formatPromoType(promo.type) }}</div>
                  <div class="text-orange-400 font-bold bg-orange-400/10 inline-block px-2 py-0.5 rounded text-xs">{{ formatDiscount(promo) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2.5 py-1 bg-black/50 text-gray-300 font-mono text-xs tracking-wider rounded border border-white/10 shadow-inner">
                    {{ promo.code }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-3">
                    <div class="flex-1 w-24 bg-gray-800 rounded-full h-1.5 overflow-hidden">
                      <div class="bg-gradient-to-r from-orange-500 to-red-500 h-1.5 rounded-full" :style="`width: ${(promo.used_count / promo.usage_limit) * 100}%`"></div>
                    </div>
                    <div class="text-xs font-medium text-gray-400">{{ promo.used_count }}/{{ promo.usage_limit }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-white">{{ formatDate(promo.start_date) }}</div>
                  <div class="text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                    <span class="w-1 h-1 rounded-full bg-gray-600"></span>
                    {{ formatDate(promo.end_date) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    class="px-2.5 py-1 text-[11px] font-bold uppercase tracking-wider rounded-full shadow-sm"
                    :class="{
                      'bg-green-500/10 text-green-400 border border-green-500/20': promo.status === 'active',
                      'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20': promo.status === 'pending',
                      'bg-red-500/10 text-red-400 border border-red-500/20': promo.status === 'rejected',
                      'bg-gray-500/10 text-gray-400 border border-gray-500/20': promo.status === 'inactive'
                    }"
                  >
                    {{ promo.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <Button variant="ghost" size="icon" class="text-gray-500 hover:text-white hover:bg-white/10 rounded-full transition-colors h-8 w-8">
                    <MoreVertical class="w-4 h-4" />
                  </Button>
                </td>
              </tr>
              <tr v-if="filteredPromotions.length === 0">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center">
                      <Search class="w-6 h-6 text-gray-500" />
                    </div>
                    <p class="text-gray-400 font-medium">No promotions found matching your criteria.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showCreateModal" @update:open="val => showCreateModal = val">
      <DialogContent class="bg-[#121212] border-white/10 text-white w-[95vw] sm:max-w-[600px] max-h-[90dvh] overflow-y-auto rounded-2xl shadow-2xl p-4 md:p-6">
        <DialogHeader class="mb-2">
          <DialogTitle class="text-xl md:text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400">
            Create New Promotion
          </DialogTitle>
          <DialogDescription class="text-gray-400 text-sm mt-1.5">
            Configure a new discount or promotional offer. It will be marked as <span class="text-yellow-500/90 font-medium">pending</span> until head approval.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent class="space-y-4 md:space-y-5">
          <div class="space-y-4">
            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Promotion Name</label>
              <Input 
                v-model="promoForm.name" 
                placeholder="e.g., Summer Paint Sale 2026" 
                class="bg-black/50 border-white/10 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 text-white rounded-lg transition-all"
              />
            </div>

            <div class="space-y-1.5">
              <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Target Product</label>
              <Select v-model="promoForm.productId">
                <SelectTrigger class="bg-black/50 border-white/10 text-white w-full rounded-lg hover:bg-black/70 transition-colors">
                  <SelectValue placeholder="All Products (Storewide)" />
                </SelectTrigger>
                <SelectContent position="popper" class="z-[100] bg-[#1a1a1a] border-white/10 text-white rounded-xl shadow-xl w-full">
                  <SelectItem value="all" class="cursor-pointer focus:bg-white/10 focus:text-white hover:bg-white/10 hover:text-white transition-colors">
                    All Products (Storewide)
                  </SelectItem>
                  <SelectItem v-for="product in products" :key="product.id" :value="product.id.toString()" class="cursor-pointer focus:bg-white/10 focus:text-white hover:bg-white/10 hover:text-white transition-colors">
                    {{ product.name }} <span class="text-gray-500 ml-1 group-hover:text-gray-300">(Stock: {{ product.stock || 0 }})</span>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Discount Type</label>
                <Select v-model="promoForm.type">
                  <SelectTrigger class="bg-black/50 border-white/10 text-white w-full rounded-lg hover:bg-black/70 transition-colors">
                    <SelectValue placeholder="Select type" />
                  </SelectTrigger>
                  <SelectContent position="popper" class="z-[100] bg-[#1a1a1a] border-white/10 text-white rounded-xl shadow-xl">
                    <SelectItem value="percentage" class="cursor-pointer focus:bg-white/10 focus:text-white hover:bg-white/10 hover:text-white transition-colors">Percentage (%)</SelectItem>
                    <SelectItem value="fixed" class="cursor-pointer focus:bg-white/10 focus:text-white hover:bg-white/10 hover:text-white transition-colors">Fixed Amount (₱)</SelectItem>
                    <SelectItem value="free_shipping" class="cursor-pointer focus:bg-white/10 focus:text-white hover:bg-white/10 hover:text-white transition-colors">Free Shipping</SelectItem>
                    <SelectItem value="bogo" class="cursor-pointer focus:bg-white/10 focus:text-white hover:bg-white/10 hover:text-white transition-colors">Buy One Get One</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div class="space-y-1.5" v-if="['percentage', 'fixed'].includes(promoForm.type)">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Discount Value</label>
                <Input 
                  v-model="promoForm.discountValue" 
                  type="number" 
                  placeholder="e.g., 20" 
                  class="bg-black/50 border-white/10 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 text-white rounded-lg transition-all"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Promo Code</label>
                <Input 
                  v-model="promoForm.code" 
                  @input="handleCodeInput"
                  placeholder="e.g., SUMMER-8X2A" 
                  class="bg-black/50 border-white/10 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 text-white uppercase font-mono rounded-lg transition-all"
                />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Usage Limit</label>
                <Input 
                  v-model="promoForm.usageLimit" 
                  type="number" 
                  placeholder="e.g., 100" 
                  class="bg-black/50 border-white/10 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 text-white rounded-lg transition-all"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Start Date</label>
                <Input 
                  v-model="promoForm.startDate" 
                  type="date" 
                  class="bg-black/50 border-white/10 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 text-white rounded-lg transition-all [color-scheme:dark]"
                />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">End Date</label>
                <Input 
                  v-model="promoForm.endDate" 
                  type="date" 
                  class="bg-black/50 border-white/10 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 text-white rounded-lg transition-all [color-scheme:dark]"
                />
              </div>
            </div>
          </div>

          <div class="flex flex-col md:flex-row items-center justify-end gap-3 pt-4 mt-2 border-t border-white/5">
            <Button type="button" variant="outline" @click="showCreateModal = false" class="w-full md:w-auto border-white/10 bg-transparent text-gray-300 hover:text-white hover:bg-white/5 rounded-lg transition-colors">
              Cancel
            </Button>
            <Button type="button" @click="handleFormSubmit" class="w-full md:w-auto bg-gradient-to-r from-orange-500 to-red-500 text-white border-0 hover:from-orange-400 hover:to-red-400 shadow-lg shadow-orange-500/20 rounded-lg transition-all duration-300">
              Continue
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showConfirmDialog" @update:open="val => showConfirmDialog = val">
      <AlertDialogContent class="bg-[#121212] border border-white/10 rounded-2xl shadow-2xl z-[150] w-[95vw] sm:max-w-[500px] p-4 md:p-6">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-white text-lg md:text-xl">Confirm Promotion</AlertDialogTitle>
          <AlertDialogDescription class="text-gray-400 mt-2 text-sm md:text-base">
            Are you sure you want to create this promotion? It will be marked as <span class="text-yellow-500 font-medium">pending</span> until approved.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-4 flex flex-col sm:flex-row gap-3">
          <AlertDialogCancel @click="showConfirmDialog = false" class="mt-0 w-full sm:w-auto bg-transparent border-white/10 text-gray-300 hover:bg-white/5 hover:text-white transition-colors">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction @click="submitPromotion" :disabled="saving" class="w-full sm:w-auto bg-gradient-to-r from-orange-500 to-red-500 text-white border-0 hover:from-orange-400 hover:to-red-400 shadow-lg shadow-orange-500/20">
            <span v-if="saving" class="animate-spin mr-2 h-4 w-4 border-2 border-white/20 border-t-white rounded-full"></span>
            {{ saving ? 'Submitting...' : 'Yes, Submit' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from '@/utils/axios'
import { Toaster, toast } from 'vue-sonner' 
import { Search, Filter, MoreVertical } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
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

const showCreateModal = ref(false)
const showConfirmDialog = ref(false)
const searchQuery = ref('')
const loading = ref(false)
const saving = ref(false)

const isCodeManuallyEdited = ref(false)

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_create: false,
  can_update: false,
  can_delete: false
})

const generateRandomSuffix = () => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
  let result = ''
  for (let i = 0; i < 5; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length))
  }
  return result
}

const currentRandomSuffix = ref(generateRandomSuffix())

const promotions = ref([])
const products = ref([])

const promoForm = ref({
  name: '',
  productId: 'all',
  type: 'percentage',
  discountValue: '',
  code: '',
  usageLimit: 100,
  startDate: '',
  endDate: ''
})

watch(() => promoForm.value.name, (newName) => {
  if (!isCodeManuallyEdited.value) {
    const cleanName = newName.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 10)
    promoForm.value.code = cleanName ? `${cleanName}-${currentRandomSuffix.value}` : ''
  }
})

const handleCodeInput = (event) => {
  const value = event.target.value
  if (!value) {
    isCodeManuallyEdited.value = false
    currentRandomSuffix.value = generateRandomSuffix()
    const cleanName = promoForm.value.name.replace(/[^a-zA-Z0-9]/g, '').toUpperCase().substring(0, 10)
    promoForm.value.code = cleanName ? `${cleanName}-${currentRandomSuffix.value}` : ''
  } else {
    isCodeManuallyEdited.value = true
  }
}

const fetchProducts = async () => {
  try {
    const res = await axios.get('/crm/promotions/products')
    if(res.data.status === 'success') {
       products.value = res.data.data
    }
  } catch (err) {
    console.error('Error fetching products:', err)
  }
}

const fetchPromotions = async () => {
  loading.value = true
  try {
    const res = await axios.get('/crm/promotions')
    if(res.data.status === 'success') {
       promotions.value = res.data.data
       
       if (res.data.permissions) {
         permissions.value = res.data.permissions
       }
    }
  } catch (err) {
    console.error('Error fetching promotions:', err)
    if (err.response?.status === 403) {
      toast.error('Access Denied', {
         description: err.response.data.message || 'You lack permissions to view promotions.',
         style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
      })
    } else {
      toast.error('Failed to load promotions')
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProducts()
  fetchPromotions()
})

const filteredPromotions = computed(() => {
  if (!searchQuery.value) return promotions.value
  const query = searchQuery.value.toLowerCase()
  return promotions.value.filter(promo => 
    promo.name.toLowerCase().includes(query) || 
    promo.code.toLowerCase().includes(query)
  )
})

const activePromotions = computed(() => promotions.value.filter(p => p.status === 'active').length)
const pendingPromotions = computed(() => promotions.value.filter(p => p.status === 'pending').length)
const totalUses = computed(() => promotions.value.reduce((sum, p) => sum + p.used_count, 0))

const formatPromoType = (type) => {
  const types = {
    'percentage': 'Percentage Discount',
    'fixed': 'Fixed Amount Discount',
    'free_shipping': 'Free Shipping',
    'bogo': 'Buy One Get One'
  }
  return types[type] || type
}

const formatDiscount = (promo) => {
  if (promo.type === 'percentage') return `${promo.discount_value}% OFF`
  if (promo.type === 'fixed') return `₱${promo.discount_value} OFF`
  if (promo.type === 'free_shipping') return 'FREE SHIPPING'
  if (promo.type === 'bogo') return 'BOGO'
  return ''
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  }).format(date)
}

const handleFormSubmit = () => {
  const { name, type, discountValue, code, usageLimit, startDate, endDate, productId } = promoForm.value;

  if (!name) return toast.error('Promotion Name is required.');
  if (!productId) return toast.error('Target Product is required.');
  if (!type) return toast.error('Discount Type is required.');
  
  if (['percentage', 'fixed'].includes(type) && !discountValue) {
    return toast.error('Discount Value is required.');
  }

  if (!code) return toast.error('Promo Code is required.');
  if (!usageLimit) return toast.error('Usage Limit is required.');
  if (!startDate) return toast.error('Start Date is required.');
  if (!endDate) return toast.error('End Date is required.');

  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const start = new Date(startDate);
  start.setHours(0, 0, 0, 0);
  const end = new Date(endDate);
  end.setHours(0, 0, 0, 0);

  if (start < today) {
    return toast.error('Start Date cannot be in the past.');
  }

  if (end < start) {
    return toast.error('End Date cannot be earlier than Start Date.');
  }

  const maxEndDate = new Date(start);
  maxEndDate.setDate(maxEndDate.getDate() + 30);

  if (end > maxEndDate) {
    return toast.error('End Date cannot be more than 30 days after the Start Date.');
  }

  showConfirmDialog.value = true;
}

const submitPromotion = async () => {
  // FINAL HARD STOP on the frontend before interacting with the API
  if (!permissions.value.can_create) {
     toast.error('Access Denied', {
        description: 'You do not have permission to create promotions.',
        style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
     });
     showConfirmDialog.value = false;
     showCreateModal.value = false;
     return;
  }

  saving.value = true
  try {
    const response = await axios.post('/crm/promotions', {
      name: promoForm.value.name,
      product_id: promoForm.value.productId === 'all' ? null : promoForm.value.productId,
      type: promoForm.value.type,
      discount_value: promoForm.value.discountValue || null,
      code: promoForm.value.code.toUpperCase(),
      usage_limit: parseInt(promoForm.value.usageLimit),
      start_date: promoForm.value.startDate,
      end_date: promoForm.value.endDate
    })

    if (response.data.status === 'success') {
      toast.success(response.data.message)
      showConfirmDialog.value = false 
      showCreateModal.value = false 
      
      isCodeManuallyEdited.value = false
      currentRandomSuffix.value = generateRandomSuffix()
      promoForm.value = {
        name: '',
        productId: 'all',
        type: 'percentage',
        discountValue: '',
        code: '',
        usageLimit: 100,
        startDate: '',
        endDate: ''
      }
      
      fetchPromotions() 
    }
  } catch (err) {
    console.error('Failed to create promotion:', err)
    if (err.response?.status === 403) {
      toast.error('Action Restricted', { description: err.response.data.message || 'You do not have permission to create promotions.' })
    } else if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      toast.error(errors[0])
    } else {
      toast.error(err.response?.data?.message || 'Failed to submit promotion')
    }
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>