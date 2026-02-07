<template>
  <div class="ecommerce-promotions p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Promotions & Discounts</h1>
          <p class="text-gray-300">Increase sales through targeted promotions and discounts</p>
        </div>
        <Button @click="showCreateModal = true" 
                class="mt-4 md:mt-0 bg-gradient-to-r from-orange-500 to-red-500 text-white border-0 hover:opacity-90">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Create Promotion
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-orange-500/20 to-red-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ activePromotions }}</div>
          <div class="text-sm text-gray-300">Active Promotions</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">₱{{ totalDiscounts.toLocaleString() }}</div>
          <div class="text-sm text-gray-300">Total Discounts</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ promoUsage }}</div>
          <div class="text-sm text-gray-300">Promo Codes Used</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ conversionRate }}%</div>
          <div class="text-sm text-gray-300">Conversion Rate</div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <Card v-for="promo in promotions" :key="promo.id" 
           :class="[
             'border',
             promo.status === 'active' ? 'bg-gradient-to-br from-orange-500/10 to-red-500/10 border-orange-500/30' :
             promo.status === 'upcoming' ? 'bg-gradient-to-br from-blue-500/10 to-cyan-500/10 border-blue-500/30' :
             'bg-gradient-to-br from-gray-800/50 to-gray-900/50 border-gray-700'
           ]">
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="text-xl font-bold text-white mb-2">{{ promo.name }}</h3>
              <div class="flex items-center mb-3">
                <Badge :class="[
                  'rounded-full border-0',
                  promo.status === 'active' ? 'bg-green-500/20 text-green-300' :
                  promo.status === 'upcoming' ? 'bg-blue-500/20 text-blue-300' :
                  'bg-gray-500/20 text-gray-300'
                ]">
                  {{ promo.status === 'active' ? 'Active' : 
                     promo.status === 'upcoming' ? 'Upcoming' : 'Expired' }}
                </Badge>
                <span class="text-sm text-gray-400 ml-3">{{ promo.type }}</span>
              </div>
            </div>
            
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon" class="text-gray-400 hover:text-white hover:bg-gray-800/50">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                  </svg>
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="w-48 bg-gray-800 border-gray-700 text-gray-300">
                <DropdownMenuItem @click="editPromotion(promo)" class="text-blue-400 focus:text-blue-300 focus:bg-gray-700 cursor-pointer">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  Edit Promotion
                </DropdownMenuItem>
                <DropdownMenuItem @click="togglePromoStatus(promo)" class="text-amber-400 focus:text-amber-300 focus:bg-gray-700 cursor-pointer">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  {{ promo.status === 'active' ? 'Deactivate' : 'Activate' }}
                </DropdownMenuItem>
                <DropdownMenuItem @click="deletePromotion(promo)" class="text-red-400 focus:text-red-300 focus:bg-gray-700 cursor-pointer">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Delete
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>
          </div>
          
          <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-gray-300">Discount:</span>
              <span class="text-white font-bold text-lg">{{ promo.discount }}</span>
            </div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-gray-300">Promo Code:</span>
              <Badge variant="secondary" class="bg-gray-800 text-white font-mono px-2 py-1">
                {{ promo.code }}
              </Badge>
            </div>
            <div class="flex items-center justify-between mb-2">
              <span class="text-gray-300">Usage:</span>
              <span class="text-white">{{ promo.used }}/{{ promo.limit }}</span>
            </div>
          </div>
          
          <div class="mb-6">
            <div class="text-sm text-gray-400 mb-2">Valid Period:</div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-300">{{ promo.startDate }}</span>
              <span class="text-gray-400">to</span>
              <span class="text-gray-300">{{ promo.endDate }}</span>
            </div>
          </div>
          
          <div class="mb-6">
            <div class="text-sm text-gray-400 mb-2">Progress:</div>
            <Progress :model-value="(promo.used / promo.limit * 100)" 
                     class="h-2 bg-gray-700" 
                     indicator-class="bg-gradient-to-r from-orange-500 to-red-500" />
            <div class="text-xs text-gray-400 text-right mt-1">{{ Math.round(promo.used / promo.limit * 100) }}% used</div>
          </div>
        </CardContent>
        <CardFooter class="pt-0 border-t border-gray-700/50 p-6 flex justify-between">
          <Button variant="outline" size="sm" @click="viewDetails(promo)" 
                 class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
            Details
          </Button>
          <Button size="sm" @click="copyPromoCode(promo.code)" 
                 class="bg-gradient-to-r from-orange-500/20 to-red-500/20 text-orange-300 hover:from-orange-500/30 hover:to-red-500/30 border-0">
            Copy Code
          </Button>
        </CardFooter>
      </Card>
    </div>

    <div class="mb-8">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white">Distributor-Specific Promotions</h3>
        <Button @click="showDistributorPromo = true" 
                class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0 hover:opacity-90">
          Add Distributor Promo
        </Button>
      </div>
      
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
          <Table>
            <TableHeader class="bg-gray-900/80">
              <TableRow class="hover:bg-transparent border-gray-800">
                <TableHead class="text-gray-300">Promotion</TableHead>
                <TableHead class="text-gray-300">Distributor</TableHead>
                <TableHead class="text-gray-300">Discount</TableHead>
                <TableHead class="text-gray-300">Products</TableHead>
                <TableHead class="text-gray-300">Status</TableHead>
                <TableHead class="text-gray-300">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="promo in distributorPromos" :key="promo.id" 
                  class="border-gray-800 hover:bg-white/5">
                <TableCell>
                  <div class="text-white font-medium">{{ promo.name }}</div>
                  <div class="text-sm text-gray-400">{{ promo.code }}</div>
                </TableCell>
                <TableCell>
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs mr-3">
                      {{ promo.distributor.charAt(0) }}
                    </div>
                    <span class="text-gray-300">{{ promo.distributor }}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="text-white font-medium">{{ promo.discount }}</div>
                  <div class="text-sm text-gray-400">{{ promo.minPurchase }}</div>
                </TableCell>
                <TableCell>
                  <div class="text-gray-300">{{ promo.products.length }} products</div>
                  <div class="text-sm text-gray-400 truncate max-w-xs">{{ promo.products.join(', ') }}</div>
                </TableCell>
                <TableCell>
                  <Badge :class="[
                    'rounded-full border-0',
                    promo.status === 'active' ? 'bg-green-500/20 text-green-300' :
                    'bg-gray-500/20 text-gray-300'
                  ]">
                    {{ promo.status }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <div class="flex space-x-2">
                    <Button size="icon" variant="ghost" @click="editDistributorPromo(promo)" 
                           class="text-blue-400 hover:text-blue-300 hover:bg-blue-500/20">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </Button>
                    <Button size="icon" variant="ghost" @click="toggleDistributorPromo(promo)" 
                           class="text-amber-400 hover:text-amber-300 hover:bg-amber-500/20">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </Card>
    </div>

    <Dialog v-model:open="showCreateModal">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>{{ editingPromotion ? 'Edit Promotion' : 'Create New Promotion' }}</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="savePromotion" class="space-y-6 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label class="text-gray-300">Promotion Name *</Label>
              <Input type="text" v-model="promoForm.name" required 
                     class="bg-gray-800 border-gray-700 text-white" />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Promotion Type *</Label>
              <Select v-model="promoForm.type">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select type" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="percentage">Percentage Discount</SelectItem>
                  <SelectItem value="fixed">Fixed Amount Discount</SelectItem>
                  <SelectItem value="free_shipping">Free Shipping</SelectItem>
                  <SelectItem value="buy_one_get_one">Buy One Get One</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Discount Value *</Label>
              <div class="flex">
                <span v-if="promoForm.type === 'percentage'" 
                      class="inline-flex items-center px-3 bg-gray-700 border border-r-0 border-gray-600 rounded-l-lg text-gray-300 text-sm">
                  %
                </span>
                <span v-else 
                      class="inline-flex items-center px-3 bg-gray-700 border border-r-0 border-gray-600 rounded-l-lg text-gray-300 text-sm">
                  ₱
                </span>
                <Input type="number" v-model="promoForm.discountValue" required 
                       class="rounded-l-none bg-gray-800 border-gray-700 text-white" />
              </div>
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Promo Code *</Label>
              <Input type="text" v-model="promoForm.code" required 
                     class="bg-gray-800 border-gray-700 text-white font-mono"
                     placeholder="e.g., SUMMER2024" />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Start Date *</Label>
              <Input type="date" v-model="promoForm.startDate" required 
                     class="bg-gray-800 border-gray-700 text-white" />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">End Date *</Label>
              <Input type="date" v-model="promoForm.endDate" required 
                     class="bg-gray-800 border-gray-700 text-white" />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Usage Limit</Label>
              <Input type="number" v-model="promoForm.usageLimit" 
                     class="bg-gray-800 border-gray-700 text-white"
                     placeholder="0 for unlimited" />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Minimum Purchase</Label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 z-10">₱</span>
                <Input type="number" v-model="promoForm.minPurchase" 
                       class="pl-8 bg-gray-800 border-gray-700 text-white" />
              </div>
            </div>
          </div>
          
          <div class="space-y-2">
            <Label class="text-gray-300">Applicable Products</Label>
            <div class="border border-gray-700 rounded-lg p-4 max-h-40 overflow-y-auto space-y-2">
              <div v-for="product in availableProducts" :key="product.id" class="flex items-center">
                <Checkbox :id="'product-' + product.id" 
                       :checked="promoForm.applicableProducts.includes(product.id)"
                       @update:checked="(checked) => {
                         if (checked) promoForm.applicableProducts.push(product.id)
                         else promoForm.applicableProducts = promoForm.applicableProducts.filter(id => id !== product.id)
                       }"
                       class="border-gray-500 data-[state=checked]:bg-orange-500 data-[state=checked]:border-orange-500" />
                <label :for="'product-' + product.id" class="ml-2 text-gray-300 text-sm cursor-pointer">
                  {{ product.name }} (₱{{ product.price }})
                </label>
              </div>
            </div>
          </div>
          
          <div class="space-y-2">
            <Label class="text-gray-300">Promotion Description</Label>
            <Textarea v-model="promoForm.description" rows="3" 
                     class="bg-gray-800 border-gray-700 text-white" />
          </div>
          
          <div class="flex items-center">
            <Checkbox id="isActive" v-model:checked="promoForm.isActive" 
                   class="border-gray-500 data-[state=checked]:bg-orange-500 data-[state=checked]:border-orange-500" />
            <label for="isActive" class="ml-2 text-gray-300 text-sm cursor-pointer">Set as active immediately</label>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
            <Button type="button" variant="outline" @click="showCreateModal = false" 
                   class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
              Cancel
            </Button>
            <Button type="submit" 
                   class="bg-gradient-to-r from-orange-500 to-red-500 text-white border-0 hover:opacity-90">
              {{ editingPromotion ? 'Update Promotion' : 'Create Promotion' }}
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import { Progress } from '@/components/ui/progress'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
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
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const showCreateModal = ref(false)
const showDistributorPromo = ref(false)
const editingPromotion = ref(null)

const promotions = ref([
  { 
    id: 1, 
    name: 'Summer Sale 2024', 
    type: 'Percentage Discount',
    discount: '20% OFF',
    code: 'SUMMER2024',
    used: 45,
    limit: 100,
    startDate: '2024-03-01',
    endDate: '2024-03-31',
    status: 'active'
  },
  { 
    id: 2, 
    name: 'Free Shipping Weekend', 
    type: 'Free Shipping',
    discount: 'FREE SHIPPING',
    code: 'FREESHIP',
    used: 28,
    limit: 50,
    startDate: '2024-03-15',
    endDate: '2024-03-17',
    status: 'active'
  },
  { 
    id: 3, 
    name: 'New Customer Discount', 
    type: 'Fixed Amount',
    discount: '₱500 OFF',
    code: 'NEW500',
    used: 15,
    limit: 30,
    startDate: '2024-04-01',
    endDate: '2024-04-30',
    status: 'upcoming'
  },
  { 
    id: 4, 
    name: 'Bulk Purchase Discount', 
    type: 'Percentage Discount',
    discount: '15% OFF',
    code: 'BULK15',
    used: 20,
    limit: 20,
    startDate: '2024-02-01',
    endDate: '2024-02-28',
    status: 'expired'
  },
  { 
    id: 5, 
    name: 'Paint Tools Bundle', 
    type: 'Buy One Get One',
    discount: 'BOGO',
    code: 'TOOLSBOGO',
    used: 8,
    limit: 25,
    startDate: '2024-03-10',
    endDate: '2024-03-24',
    status: 'active'
  }
])

const distributorPromos = ref([
  { 
    id: 1, 
    name: 'CaviteGo Exclusive', 
    code: 'CAVITEGO20',
    distributor: 'CaviteGo Distributors',
    discount: '20% OFF',
    minPurchase: 'Min. ₱5,000',
    products: ['Premium White Paint', 'Weatherproof Exterior'],
    status: 'active'
  },
  { 
    id: 2, 
    name: 'Premium Paint Special', 
    code: 'PREMIUM15',
    distributor: 'Premium Paint Supply',
    discount: '15% OFF',
    minPurchase: 'Min. ₱3,000',
    products: ['All Interior Paints'],
    status: 'active'
  }
])

const promoForm = ref({
  name: '',
  type: 'percentage',
  discountValue: '',
  code: '',
  startDate: '',
  endDate: '',
  usageLimit: '',
  minPurchase: '',
  applicableProducts: [],
  description: '',
  isActive: true
})

const availableProducts = ref([
  { id: 1, name: 'Premium White Paint', price: 2450 },
  { id: 2, name: 'Weatherproof Exterior Paint', price: 3250 },
  { id: 3, name: 'Quick Dry Primer', price: 1280 },
  { id: 4, name: 'Gloss Finish Coating', price: 2980 },
  { id: 5, name: 'Eco-Friendly Paint', price: 2750 },
  { id: 6, name: 'Anti-Mold Exterior Paint', price: 3850 }
])

const activePromotions = computed(() => {
  return promotions.value.filter(p => p.status === 'active').length
})

const totalDiscounts = computed(() => {
  return 124500
})

const promoUsage = computed(() => {
  return promotions.value.reduce((sum, p) => sum + p.used, 0)
})

const conversionRate = computed(() => {
  return 12.5
})

const editPromotion = (promo) => {
  editingPromotion.value = promo
  promoForm.value = {
    name: promo.name,
    type: promo.type.toLowerCase().includes('percentage') ? 'percentage' :
          promo.type.toLowerCase().includes('fixed') ? 'fixed' :
          promo.type.toLowerCase().includes('shipping') ? 'free_shipping' : 'buy_one_get_one',
    discountValue: promo.discount.replace(/[^0-9]/g, ''),
    code: promo.code,
    startDate: promo.startDate,
    endDate: promo.endDate,
    usageLimit: promo.limit,
    minPurchase: '',
    applicableProducts: [],
    description: '',
    isActive: promo.status === 'active'
  }
  showCreateModal.value = true
}

const togglePromoStatus = (promo) => {
  promo.status = promo.status === 'active' ? 'inactive' : 'active'
}

const deletePromotion = (promo) => {
  if (confirm(`Are you sure you want to delete "${promo.name}"?`)) {
    promotions.value = promotions.value.filter(p => p.id !== promo.id)
  }
}

const viewDetails = (promo) => {
  alert(`Viewing details for: ${promo.name}`)
}

const copyPromoCode = (code) => {
  navigator.clipboard.writeText(code)
  alert(`Promo code "${code}" copied to clipboard!`)
}

const editDistributorPromo = (promo) => {
  alert(`Editing distributor promo: ${promo.name}`)
}

const toggleDistributorPromo = (promo) => {
  promo.status = promo.status === 'active' ? 'inactive' : 'active'
}

const savePromotion = () => {
  if (editingPromotion.value) {
    const index = promotions.value.findIndex(p => p.id === editingPromotion.value.id)
    if (index !== -1) {
      promotions.value[index] = {
        ...promotions.value[index],
        name: promoForm.value.name,
        discount: promoForm.value.type === 'percentage' ? `${promoForm.value.discountValue}% OFF` :
                  promoForm.value.type === 'fixed' ? `₱${promoForm.value.discountValue} OFF` :
                  promoForm.value.type === 'free_shipping' ? 'FREE SHIPPING' : 'BOGO',
        code: promoForm.value.code,
        status: promoForm.value.isActive ? 'active' : 'inactive'
      }
    }
  } else {
    const newId = Math.max(...promotions.value.map(p => p.id)) + 1
    promotions.value.push({
      id: newId,
      name: promoForm.value.name,
      type: promoForm.value.type === 'percentage' ? 'Percentage Discount' :
            promoForm.value.type === 'fixed' ? 'Fixed Amount Discount' :
            promoForm.value.type === 'free_shipping' ? 'Free Shipping' : 'Buy One Get One',
      discount: promoForm.value.type === 'percentage' ? `${promoForm.value.discountValue}% OFF` :
                promoForm.value.type === 'fixed' ? `₱${promoForm.value.discountValue} OFF` :
                promoForm.value.type === 'free_shipping' ? 'FREE SHIPPING' : 'BOGO',
      code: promoForm.value.code,
      used: 0,
      limit: parseInt(promoForm.value.usageLimit) || 100,
      startDate: promoForm.value.startDate,
      endDate: promoForm.value.endDate,
      status: promoForm.value.isActive ? 'active' : 'inactive'
    })
  }
  
  showCreateModal.value = false
  editingPromotion.value = null
  promoForm.value = {
    name: '',
    type: 'percentage',
    discountValue: '',
    code: '',
    startDate: '',
    endDate: '',
    usageLimit: '',
    minPurchase: '',
    applicableProducts: [],
    description: '',
    isActive: true
  }
}
</script>

<style scoped>
.ecommerce-promotions {
  min-height: 100vh;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-promotions {
    padding: 1rem;
  }
}
</style>