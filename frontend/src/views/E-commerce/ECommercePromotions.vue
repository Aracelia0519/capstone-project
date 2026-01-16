<!-- ECommercePromotions.vue -->
<template>
  <div class="ecommerce-promotions p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Promotions & Discounts</h1>
          <p class="text-gray-300">Increase sales through targeted promotions and discounts</p>
        </div>
        <button @click="showCreateModal = true" 
                class="mt-4 md:mt-0 px-4 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:opacity-90 transition-opacity flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Create Promotion
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ activePromotions }}</div>
        <div class="text-sm text-gray-300">Active Promotions</div>
      </div>
      <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">₱{{ totalDiscounts.toLocaleString() }}</div>
        <div class="text-sm text-gray-300">Total Discounts</div>
      </div>
      <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ promoUsage }}</div>
        <div class="text-sm text-gray-300">Promo Codes Used</div>
      </div>
      <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ conversionRate }}%</div>
        <div class="text-sm text-gray-300">Conversion Rate</div>
      </div>
    </div>

    <!-- Promotions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      <div v-for="promo in promotions" :key="promo.id" 
           :class="[
             'rounded-2xl p-6 border',
             promo.status === 'active' ? 'bg-gradient-to-br from-orange-500/10 to-red-500/10 border-orange-500/30' :
             promo.status === 'upcoming' ? 'bg-gradient-to-br from-blue-500/10 to-cyan-500/10 border-blue-500/30' :
             'bg-gradient-to-br from-gray-800/50 to-gray-900/50 border-gray-700'
           ]">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-xl font-bold text-white mb-2">{{ promo.name }}</h3>
            <div class="flex items-center mb-3">
              <span :class="[
                'px-3 py-1 rounded-full text-xs font-medium',
                promo.status === 'active' ? 'bg-green-500/20 text-green-300' :
                promo.status === 'upcoming' ? 'bg-blue-500/20 text-blue-300' :
                'bg-gray-500/20 text-gray-300'
              ]">
                {{ promo.status === 'active' ? 'Active' : 
                   promo.status === 'upcoming' ? 'Upcoming' : 'Expired' }}
              </span>
              <span class="text-sm text-gray-400 ml-3">{{ promo.type }}</span>
            </div>
          </div>
          <div class="relative">
            <button @click="togglePromoActions(promo.id)" 
                    class="p-2 text-gray-400 hover:text-white hover:bg-gray-800/50 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
              </svg>
            </button>
            <div v-if="activePromoActions === promo.id" class="absolute right-0 mt-2 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-10">
              <button @click="editPromotion(promo)" 
                     class="w-full text-left px-4 py-3 text-blue-400 hover:bg-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Promotion
              </button>
              <button @click="togglePromoStatus(promo)" 
                     class="w-full text-left px-4 py-3 text-amber-400 hover:bg-gray-700 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ promo.status === 'active' ? 'Deactivate' : 'Activate' }}
              </button>
              <button @click="deletePromotion(promo)" 
                     class="w-full text-left px-4 py-3 text-red-400 hover:bg-red-500/10 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
              </button>
            </div>
          </div>
        </div>
        
        <div class="mb-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300">Discount:</span>
            <span class="text-white font-bold text-lg">{{ promo.discount }}</span>
          </div>
          <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300">Promo Code:</span>
            <span class="font-mono text-white bg-gray-800 px-2 py-1 rounded">{{ promo.code }}</span>
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
          <div class="w-full bg-gray-700 h-2 rounded-full overflow-hidden mb-1">
            <div class="h-full bg-gradient-to-r from-orange-500 to-red-500 rounded-full" 
                 :style="{ width: (promo.used / promo.limit * 100) + '%' }"></div>
          </div>
          <div class="text-xs text-gray-400 text-right">{{ Math.round(promo.used / promo.limit * 100) }}% used</div>
        </div>
        
        <div class="pt-4 border-t border-gray-700/50">
          <div class="flex justify-between">
            <button @click="viewDetails(promo)" 
                   class="px-4 py-2 text-sm border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
              Details
            </button>
            <button @click="copyPromoCode(promo.code)" 
                   class="px-4 py-2 text-sm bg-gradient-to-r from-orange-500/20 to-red-500/20 text-orange-300 rounded-lg hover:from-orange-500/30 hover:to-red-500/30 transition-all">
              Copy Code
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Distributor-Specific Promotions -->
    <div class="mb-8">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-white">Distributor-Specific Promotions</h3>
        <button @click="showDistributorPromo = true" 
                class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg hover:opacity-90 transition-opacity text-sm">
          Add Distributor Promo
        </button>
      </div>
      
      <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-900/80">
                <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Promotion</th>
                <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Distributor</th>
                <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Discount</th>
                <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Products</th>
                <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Status</th>
                <th class="text-left py-4 px-6 text-sm text-gray-300 font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="promo in distributorPromos" :key="promo.id" 
                  class="border-t border-gray-800 hover:bg-white/5">
                <td class="py-4 px-6">
                  <div class="text-white font-medium">{{ promo.name }}</div>
                  <div class="text-sm text-gray-400">{{ promo.code }}</div>
                </td>
                <td class="py-4 px-6">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center text-white text-xs mr-3">
                      {{ promo.distributor.charAt(0) }}
                    </div>
                    <span class="text-gray-300">{{ promo.distributor }}</span>
                  </div>
                </td>
                <td class="py-4 px-6">
                  <div class="text-white font-medium">{{ promo.discount }}</div>
                  <div class="text-sm text-gray-400">{{ promo.minPurchase }}</div>
                </td>
                <td class="py-4 px-6">
                  <div class="text-gray-300">{{ promo.products.length }} products</div>
                  <div class="text-sm text-gray-400 truncate max-w-xs">{{ promo.products.join(', ') }}</div>
                </td>
                <td class="py-4 px-6">
                  <span :class="[
                    'px-3 py-1 rounded-full text-xs font-medium',
                    promo.status === 'active' ? 'bg-green-500/20 text-green-300' :
                    'bg-gray-500/20 text-gray-300'
                  ]">
                    {{ promo.status }}
                  </span>
                </td>
                <td class="py-4 px-6">
                  <div class="flex space-x-2">
                    <button @click="editDistributorPromo(promo)" 
                           class="p-2 text-blue-400 hover:text-blue-300 hover:bg-blue-500/20 rounded-lg transition-colors">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button @click="toggleDistributorPromo(promo)" 
                           class="p-2 text-amber-400 hover:text-amber-300 hover:bg-amber-500/20 rounded-lg transition-colors">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create/Edit Promotion Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-2xl">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">{{ editingPromotion ? 'Edit Promotion' : 'Create New Promotion' }}</h3>
            <button @click="closeModal" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <form @submit.prevent="savePromotion" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm text-gray-300 mb-2">Promotion Name *</label>
                <input type="text" v-model="promoForm.name" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Promotion Type *</label>
                <select v-model="promoForm.type" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                  <option value="percentage">Percentage Discount</option>
                  <option value="fixed">Fixed Amount Discount</option>
                  <option value="free_shipping">Free Shipping</option>
                  <option value="buy_one_get_one">Buy One Get One</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Discount Value *</label>
                <div class="flex">
                  <span v-if="promoForm.type === 'percentage'" 
                        class="inline-flex items-center px-3 bg-gray-700 border border-r-0 border-gray-600 rounded-l-lg text-gray-300">
                    %
                  </span>
                  <span v-else 
                        class="inline-flex items-center px-3 bg-gray-700 border border-r-0 border-gray-600 rounded-l-lg text-gray-300">
                    ₱
                  </span>
                  <input type="number" v-model="promoForm.discountValue" required 
                         class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-r-lg text-white">
                </div>
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Promo Code *</label>
                <input type="text" v-model="promoForm.code" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white font-mono"
                       placeholder="e.g., SUMMER2024">
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Start Date *</label>
                <input type="date" v-model="promoForm.startDate" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">End Date *</label>
                <input type="date" v-model="promoForm.endDate" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Usage Limit</label>
                <input type="number" v-model="promoForm.usageLimit" 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"
                       placeholder="0 for unlimited">
              </div>
              
              <div>
                <label class="block text-sm text-gray-300 mb-2">Minimum Purchase</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">₱</span>
                  <input type="number" v-model="promoForm.minPurchase" 
                         class="w-full pl-8 pr-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
                </div>
              </div>
            </div>
            
            <div>
              <label class="block text-sm text-gray-300 mb-2">Applicable Products</label>
              <div class="border border-gray-700 rounded-lg p-4 max-h-40 overflow-y-auto">
                <div v-for="product in availableProducts" :key="product.id" class="flex items-center mb-2">
                  <input type="checkbox" :id="'product-' + product.id" 
                         :value="product.id" v-model="promoForm.applicableProducts"
                         class="w-4 h-4 text-orange-500 bg-gray-800 border-gray-700 rounded">
                  <label :for="'product-' + product.id" class="ml-2 text-gray-300">
                    {{ product.name }} (₱{{ product.price }})
                  </label>
                </div>
              </div>
            </div>
            
            <div>
              <label class="block text-sm text-gray-300 mb-2">Promotion Description</label>
              <textarea v-model="promoForm.description" rows="3" 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"></textarea>
            </div>
            
            <div class="flex items-center">
              <input type="checkbox" id="isActive" v-model="promoForm.isActive" 
                     class="w-4 h-4 text-orange-500 bg-gray-800 border-gray-700 rounded">
              <label for="isActive" class="ml-2 text-gray-300">Set as active immediately</label>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
              <button type="button" @click="closeModal" 
                     class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
                Cancel
              </button>
              <button type="submit" 
                     class="px-6 py-2 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                {{ editingPromotion ? 'Update Promotion' : 'Create Promotion' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const showCreateModal = ref(false)
const showDistributorPromo = ref(false)
const activePromoActions = ref(null)
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
  // This would normally be calculated from actual usage
  return 124500
})

const promoUsage = computed(() => {
  return promotions.value.reduce((sum, p) => sum + p.used, 0)
})

const conversionRate = computed(() => {
  // This would normally be calculated from analytics
  return 12.5
})

const togglePromoActions = (id) => {
  activePromoActions.value = activePromoActions.value === id ? null : id
}

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
  activePromoActions.value = null
}

const togglePromoStatus = (promo) => {
  promo.status = promo.status === 'active' ? 'inactive' : 'active'
  activePromoActions.value = null
}

const deletePromotion = (promo) => {
  if (confirm(`Are you sure you want to delete "${promo.name}"?`)) {
    promotions.value = promotions.value.filter(p => p.id !== promo.id)
  }
  activePromoActions.value = null
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
    // Update existing promotion
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
    // Create new promotion
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
  
  closeModal()
}

const closeModal = () => {
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

// Close actions menu when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.relative')) {
    activePromoActions.value = null
  }
})
</script>

<style scoped>
.ecommerce-promotions {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-promotions {
    padding: 1rem;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>