<template>
  <div class="p-6 min-h-screen space-y-8 text-slate-100">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-white">Partner Suppliers</h1>
        <p class="text-slate-400 mt-1">Discover and connect with verified suppliers for your distribution network.</p>
      </div>
      <div class="flex items-center gap-2">
         <Button variant="outline" @click="fetchSuppliers" class="border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-slate-300">
            <i class="fas fa-sync-alt mr-2 text-slate-500"></i> Refresh
         </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
       <div class="md:col-span-3">
          <div class="relative">
             <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
             <Input 
                v-model="searchQuery" 
                placeholder="Search suppliers by name, email, or location..." 
                class="pl-10 h-12 text-lg bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500/20"
             />
          </div>
       </div>
       
       <Card class="bg-gradient-to-br from-blue-900 to-indigo-900 text-white border-blue-800 shadow-lg">
          <CardContent class="p-4 flex items-center justify-between">
             <div>
                <p class="text-blue-200 text-sm font-medium">Available Partners</p>
                <p class="text-2xl font-bold">{{ filteredSuppliers.length }}</p>
             </div>
             <div class="h-10 w-10 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/10">
                <i class="fas fa-handshake"></i>
             </div>
          </CardContent>
       </Card>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
       <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
       <p class="text-slate-400">Loading suppliers...</p>
    </div>

    <div v-else-if="filteredSuppliers.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <Card 
        v-for="supplier in filteredSuppliers" 
        :key="supplier.id" 
        class="group bg-slate-900/50 border-slate-800 hover:border-slate-600 hover:shadow-xl hover:shadow-blue-900/10 transition-all duration-300 overflow-hidden flex flex-col backdrop-blur-sm"
      >
        <div class="h-24 bg-slate-800/50 relative border-b border-slate-800">
           <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 to-transparent"></div>
           
           <div class="absolute -bottom-6 left-6">
              <div class="h-16 w-16 rounded-xl border-4 border-slate-900 bg-slate-800 shadow-lg flex items-center justify-center overflow-hidden">
                 <span class="text-xl font-bold text-blue-400 select-none">{{ getInitials(supplier.name) }}</span>
              </div>
           </div>
           <div class="absolute top-3 right-3">
              <Badge 
                 variant="secondary" 
                 class="bg-slate-950/80 backdrop-blur text-slate-300 border border-slate-800 shadow-sm"
              >
                 <i class="fas fa-star text-amber-500 mr-1"></i> {{ supplier.rating }}
              </Badge>
           </div>
        </div>

        <CardContent class="pt-8 px-6 flex-1">
           <div class="mb-4">
              <h3 class="text-lg font-bold text-slate-100 group-hover:text-blue-400 transition-colors">{{ supplier.name }}</h3>
              <div class="flex items-center gap-2 mt-1">
                 <Badge variant="outline" class="text-xs font-normal text-slate-400 border-slate-700 bg-slate-800/50">
                    {{ supplier.category }}
                 </Badge>
                 <span class="text-xs text-slate-600">•</span>
                 <span class="text-xs text-slate-500 flex items-center gap-1">
                    <i class="fas fa-map-marker-alt"></i> {{ supplier.location }}
                 </span>
              </div>
           </div>
           
           <p class="text-sm text-slate-400 line-clamp-3 mb-4 leading-relaxed">
              {{ supplier.description }}
           </p>

           <div class="grid grid-cols-2 gap-2 text-xs text-slate-500 mb-4 bg-slate-950/50 p-3 rounded-lg border border-slate-800">
              <div class="flex flex-col">
                 <span class="text-slate-500">Min. Order</span>
                 <span class="font-semibold text-slate-300">{{ formatCurrency(supplier.min_order) }}</span>
              </div>
              <div class="flex flex-col border-l border-slate-800 pl-2">
                 <span class="text-slate-500">Fulfillment</span>
                 <span class="font-semibold text-slate-300">{{ supplier.fulfillment_rate }}%</span>
              </div>
           </div>
           
           <div v-if="supplier.status !== 'available'" class="mb-2">
              <p class="text-xs text-center text-slate-400">
                Status: 
                <span :class="{
                  'text-amber-400': supplier.status === 'pending',
                  'text-green-400': supplier.status === 'connected',
                  'text-red-400': supplier.status === 'rejected'
                }" class="font-medium capitalize">{{ supplier.partnership_details?.status === 'pending_internal' ? 'Waiting Internal Approval' : supplier.status }}</span>
              </p>
           </div>
        </CardContent>

        <CardFooter class="px-6 pb-6 pt-0 mt-auto">
           <Button 
              v-if="supplier.status === 'available' || supplier.status === 'rejected'"
              @click="initiatePartnership(supplier)" 
              class="w-full bg-slate-100 hover:bg-white text-slate-900 transition-all shadow-md hover:shadow-lg font-medium"
           >
              <i class="fas fa-plus-circle mr-2"></i> {{ supplier.status === 'rejected' ? 'Re-apply Partnership' : 'Request Partnership' }}
           </Button>
           
           <Button 
              v-else-if="supplier.status === 'pending'"
              disabled
              variant="secondary"
              class="w-full bg-amber-900/20 text-amber-500 border border-amber-900/50"
           >
              <i class="fas fa-clock mr-2"></i> Request Pending
           </Button>

           <Button 
              v-else-if="supplier.status === 'connected'"
              variant="outline"
              class="w-full text-green-400 border-green-900/50 bg-green-900/20 hover:bg-green-900/30"
           >
              <i class="fas fa-check-circle mr-2"></i> Partnered
           </Button>
        </CardFooter>
      </Card>
    </div>

    <Dialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
      <div v-if="showConfirmDialog && selectedSupplier" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <Card class="w-full max-w-md shadow-2xl border border-slate-800 bg-slate-900 animate-in fade-in zoom-in duration-200">
           <CardHeader class="border-b border-slate-800 bg-slate-900/50 pb-6">
              <div class="flex items-center gap-4">
                 <div class="h-12 w-12 rounded-lg bg-slate-800 border border-slate-700 shadow-sm flex items-center justify-center overflow-hidden">
                    <span class="text-lg font-bold text-blue-400 select-none">{{ getInitials(selectedSupplier.name) }}</span>
                 </div>
                 <div>
                    <CardTitle class="text-lg text-white">Partner with {{ selectedSupplier.name }}?</CardTitle>
                    <CardDescription class="text-slate-400">Send a formal partnership request.</CardDescription>
                 </div>
              </div>
           </CardHeader>
           <CardContent class="pt-6 space-y-4">
              <div class="p-3 bg-blue-900/20 text-blue-300 text-sm rounded-md border border-blue-800/50 flex gap-3">
                 <i class="fas fa-info-circle mt-0.5"></i>
                 <p>This request will first be sent to your Business Owner for approval, then forwarded to the supplier.</p>
              </div>
              
              <div class="space-y-2">
                 <label class="text-sm font-medium text-slate-300">Message (Optional)</label>
                 <textarea 
                    v-model="requestMessage"
                    class="flex w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200 ring-offset-background placeholder:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 disabled:cursor-not-allowed disabled:opacity-50 min-h-[100px]" 
                    placeholder="Introduce your business..."
                 ></textarea>
              </div>
           </CardContent>
           <CardFooter class="flex justify-end gap-3 pt-4 pb-6 bg-slate-900 border-t border-slate-800">
              <Button variant="ghost" class="text-slate-400 hover:text-white hover:bg-slate-800" @click="showConfirmDialog = false">Cancel</Button>
              <Button @click="confirmPartnership" :disabled="isProcessing" class="bg-blue-600 hover:bg-blue-700 text-white min-w-[120px]">
                 <i v-if="isProcessing" class="fas fa-circle-notch fa-spin mr-2"></i>
                 {{ isProcessing ? 'Sending...' : 'Send Request' }}
              </Button>
           </CardFooter>
        </Card>
      </div>
    </Dialog>

    <AlertDialog :open="showAlertDialog" @update:open="showAlertDialog = $event">
      <AlertDialogContent class="bg-slate-900 border border-slate-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-white">Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-400">
            This will send a partnership request to <span class="text-blue-400 font-medium">{{ selectedSupplier?.name }}</span>.
            The request will require approval before the partnership is finalized.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-transparent text-slate-300 hover:bg-slate-800 border-slate-700">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executePartnershipRequest" class="bg-blue-600 text-white hover:bg-blue-700">Continue</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios' 
import { toast } from 'vue-sonner' 
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Dialog } from '@/components/ui/dialog'
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

// State
const searchQuery = ref('')
const showConfirmDialog = ref(false)
const showAlertDialog = ref(false)
const selectedSupplier = ref(null)
const isProcessing = ref(false)
const loading = ref(false)
const requestMessage = ref('')
const suppliers = ref([])

// Methods
const getInitials = (name) => {
  if (!name) return '??';
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
}

const formatCurrency = (value) => {
  if (!value) return '₱0.00';
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 0
  }).format(value);
}

const fetchSuppliers = async () => {
  loading.value = true;
  try {
    // UPDATED: Using api instance (centralized axios)
    // Note: Assuming api instance handles base URL and auth tokens automatically
    const response = await api.get('/partners/suppliers');

    if (response.data.success) {
      suppliers.value = response.data.data;
    } else {
      toast.error('Failed to load suppliers', {
         style: { background: '#ffffff', color: '#000000', border: '1px solid #e2e8f0' }
      });
    }
  } catch (error) {
    console.error(error);
    toast.error('Connection error: Unable to fetch suppliers', {
       style: { background: '#ffffff', color: '#000000', border: '1px solid #e2e8f0' }
    });
  } finally {
    loading.value = false;
  }
}

const initiatePartnership = (supplier) => {
  selectedSupplier.value = supplier;
  requestMessage.value = '';
  showConfirmDialog.value = true;
}

const confirmPartnership = () => {
  if (!selectedSupplier.value) return;
  showAlertDialog.value = true;
}

const executePartnershipRequest = async () => {
  isProcessing.value = true;
  try {
    // UPDATED: Using api instance
    const response = await api.post('/partners/request', {
       supplier_id: selectedSupplier.value.id,
       message: requestMessage.value
    });

    if (response.data.success) {
      toast.success(`Request sent to ${selectedSupplier.value.name}!`, {
        description: 'It has been submitted for approval.',
        style: { background: '#ffffff', color: '#000000', border: '1px solid #e2e8f0' }
      });
      
      await fetchSuppliers();
      showAlertDialog.value = false;
      showConfirmDialog.value = false;
      selectedSupplier.value = null;
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to send request.';
    toast.error(msg, {
       style: { background: '#ffffff', color: '#000000', border: '1px solid #e2e8f0' }
    });
    showAlertDialog.value = false; 
  } finally {
    isProcessing.value = false;
  }
}

// Computed
const filteredSuppliers = computed(() => {
  if (!searchQuery.value) return suppliers.value;
  
  const query = searchQuery.value.toLowerCase();
  return suppliers.value.filter(supplier => 
    (supplier.name && supplier.name.toLowerCase().includes(query)) || 
    (supplier.category && supplier.category.toLowerCase().includes(query)) ||
    (supplier.location && supplier.location.toLowerCase().includes(query)) ||
    (supplier.email && supplier.email.toLowerCase().includes(query))
  );
})

// Hooks
onMounted(() => {
  fetchSuppliers();
})
</script>