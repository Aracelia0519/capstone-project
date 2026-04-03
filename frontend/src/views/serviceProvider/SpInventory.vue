<script setup>
import { ref, onMounted } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Package, Image as ImageIcon, Loader2 } from 'lucide-vue-next'

const inventoryItems = ref([])
const isLoading = ref(true)

const isUseModalOpen = ref(false)
const selectedItem = ref(null)
const useQuantity = ref(1)
const isSubmittingUse = ref(false)

const fetchInventory = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/service-provider/inventory')
    if (res.data.success) {
      inventoryItems.value = res.data.data
    }
  } catch (error) {
    toast.error('Failed to load inventory')
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchInventory()
})

const openUseModal = (item) => {
  selectedItem.value = item
  useQuantity.value = 1
  isUseModalOpen.value = true
}

const submitUse = async () => {
  if (useQuantity.value < 1 || useQuantity.value > selectedItem.value.quantity) {
    toast.error('Invalid quantity')
    return
  }
  isSubmittingUse.value = true
  try {
    const res = await api.post(`/service-provider/inventory/${selectedItem.value.id}/use`, {
      quantity: useQuantity.value
    })
    if (res.data.success) {
      toast.success('Quantity updated successfully')
      isUseModalOpen.value = false
      fetchInventory()
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to use product')
  } finally {
    isSubmittingUse.value = false
  }
}
</script>

<template>
  <div class="min-h-screen font-sans text-slate-200">
    <div class="bg-slate-900/90 backdrop-blur-xl shadow-lg border-b border-slate-800 sticky top-0 z-10">
      <div class="container mx-auto px-4 md:px-8 py-6">
        <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl shadow-xl shadow-indigo-500/20">
            <Package class="w-6 h-6 text-white" />
          </div>
          My Inventory
        </h1>
        <p class="text-slate-400 mt-1 ml-[52px]">Manage your acquired products and deduct usages for your jobs.</p>
      </div>
    </div>

    <div class="container mx-auto px-4 md:px-8 py-8 max-w-7xl">
      <div v-if="isLoading" class="flex flex-col items-center justify-center py-20">
        <Loader2 class="w-10 h-10 animate-spin text-indigo-500 mb-4" />
        <p class="text-slate-500">Loading inventory...</p>
      </div>

      <div v-else-if="inventoryItems.length === 0" class="bg-slate-900/50 border border-slate-800 rounded-3xl p-12 text-center">
        <Package class="w-16 h-16 mx-auto text-slate-600 mb-4" />
        <h3 class="text-xl font-bold text-white mb-2">Inventory is empty</h3>
        <p class="text-slate-400">Products you add from your delivered orders will appear here.</p>
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <Card v-for="item in inventoryItems" :key="item.id" class="bg-slate-900/50 border-slate-800 hover:border-indigo-500/50 transition-colors rounded-2xl overflow-hidden cursor-pointer" @click="openUseModal(item)">
          <div class="aspect-square bg-slate-950 flex items-center justify-center border-b border-slate-800 overflow-hidden">
             <img v-if="item.product?.image_url" :src="item.product.image_url" class="w-full h-full object-cover" />
             <ImageIcon v-else class="w-12 h-12 text-slate-700" />
          </div>
          <CardContent class="p-5">
            <h3 class="text-lg font-bold text-white truncate">{{ item.product?.name || 'Unknown Product' }}</h3>
            <p class="text-sm text-slate-400 mt-1">{{ item.product?.category }} | {{ item.product?.size }}</p>
            <div class="mt-4 flex items-center justify-between">
              <span class="text-sm font-medium text-slate-500">Available:</span>
              <span class="text-xl font-black text-indigo-400">{{ item.quantity }}</span>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <Dialog :open="isUseModalOpen" @update:open="isUseModalOpen = $event">
      <DialogContent class="bg-slate-950 border-slate-800 text-slate-200 sm:max-w-md rounded-2xl">
        <DialogHeader>
          <DialogTitle class="text-xl font-bold text-white">Use Product</DialogTitle>
          <DialogDescription class="text-slate-400">
            Deduct quantity from <span class="font-semibold text-slate-300">{{ selectedItem?.product?.name }}</span>
          </DialogDescription>
        </DialogHeader>
        <div class="py-4 space-y-4">
          <div class="flex justify-between text-sm">
            <span class="text-slate-400">Current Stock:</span>
            <span class="font-bold text-white">{{ selectedItem?.quantity }}</span>
          </div>
          <div class="space-y-2">
            <label class="text-sm font-medium text-slate-300">Quantity to Use</label>
            <Input type="number" v-model="useQuantity" min="1" :max="selectedItem?.quantity" class="bg-slate-900 border-slate-700 text-white" />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="isUseModalOpen = false" class="border-slate-700 bg-slate-900 text-slate-300 hover:bg-slate-800">Cancel</Button>
          <Button @click="submitUse" :disabled="isSubmittingUse" class="bg-indigo-600 hover:bg-indigo-700 text-white border-0">
            <Loader2 v-if="isSubmittingUse" class="w-4 h-4 mr-2 animate-spin" />
            Confirm Use
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>