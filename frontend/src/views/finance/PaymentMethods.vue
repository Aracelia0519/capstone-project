<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-purple-100 text-purple-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </span>
          Payment Methods
        </h1>
        <p class="text-sm text-gray-600">Manage available payment channels</p>
      </div>
      <div class="flex gap-3">
        <button class="px-4 py-2.5 bg-purple-600 text-white rounded-lg font-medium flex items-center gap-2 hover:bg-purple-700 transition-colors duration-200" @click="showAddMethod = true">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Add Payment Method</span>
        </button>
      </div>
    </div>

    <!-- Payment Methods Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div 
        v-for="method in paymentMethods" 
        :key="method.id"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-300"
        :class="{ 'opacity-60': !method.enabled }"
      >
        <div class="flex justify-between items-start mb-4">
          <div class="w-12 h-12 rounded-xl flex items-center justify-center"
            :class="method.id === 1 ? 'bg-green-100 text-green-600' : 
                    method.id === 2 ? 'bg-blue-100 text-blue-600' :
                    method.id === 3 ? 'bg-indigo-100 text-indigo-600' :
                    'bg-red-100 text-red-600'">
            <component :is="method.icon" class="w-8 h-8" />
          </div>
          <div class="flex items-center gap-2">
            <span :class="['w-2 h-2 rounded-full', method.enabled ? 'bg-green-500' : 'bg-gray-400']"></span>
            <span class="text-xs text-gray-600">{{ method.enabled ? 'Enabled' : 'Disabled' }}</span>
          </div>
        </div>
        
        <div class="space-y-4">
          <h3 class="text-lg font-semibold text-gray-900">{{ method.name }}</h3>
          <p class="text-sm text-gray-600">{{ method.description }}</p>
          
          <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div class="space-y-1">
              <span class="block text-xs text-gray-500">Transactions</span>
              <span class="block text-sm font-semibold text-gray-900">{{ method.transactionCount }}</span>
            </div>
            <div class="space-y-1">
              <span class="block text-xs text-gray-500">Total Amount</span>
              <span class="block text-sm font-semibold text-gray-900">₱{{ method.totalAmount.toLocaleString() }}</span>
            </div>
          </div>
        </div>
        
        <div class="flex gap-2 mt-6 pt-4 border-t border-gray-100">
          <button 
            @click="toggleMethod(method)"
            class="flex-1 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-50 transition-colors duration-200 flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="method.enabled" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ method.enabled ? 'Disable' : 'Enable' }}
          </button>
          <button 
            @click="editMethod(method)"
            class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg font-medium text-sm hover:bg-gray-200 transition-colors duration-200 flex items-center justify-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </button>
        </div>
      </div>
    </div>

    <!-- Manual Card Entry -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
      <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Manual Card Entry</h2>
        <p class="text-sm text-gray-600">For credit/debit card payments (no API integration)</p>
      </div>
      
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Card Number</label>
            <input 
              type="text" 
              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
              placeholder="1234 5678 9012 3456"
              maxlength="19"
              @input="formatCardNumber"
            >
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Cardholder Name</label>
            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="John Doe">
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Expiry Date</label>
            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="MM/YY" maxlength="5">
          </div>
          
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">CVV</label>
            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="123" maxlength="3">
          </div>
        </div>
        
        <div class="flex justify-end gap-3">
          <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">Clear</button>
          <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Save Card Manually</button>
        </div>
      </div>
    </div>

    <!-- Usage Statistics -->
    <div class="mb-8">
      <h2 class="text-lg font-semibold text-gray-900 mb-6">Payment Method Usage</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Most Used Method</h3>
            <span class="text-sm font-medium bg-green-100 text-green-800 px-2 py-1 rounded-full">↑ 15%</span>
          </div>
          <div class="space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-2 bg-blue-100 text-blue-800 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
              </svg>
              <span>Bank Transfer</span>
            </div>
            <p class="text-sm text-gray-600">Used in 45% of all transactions</p>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Fastest Growing</h3>
            <span class="text-sm font-medium bg-green-100 text-green-800 px-2 py-1 rounded-full">↑ 32%</span>
          </div>
          <div class="space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-2 bg-indigo-100 text-indigo-800 rounded-lg">
              <div class="w-6 h-6 bg-indigo-600 text-white rounded text-xs flex items-center justify-center font-bold">GC</div>
              <span>GCash</span>
            </div>
            <p class="text-sm text-gray-600">Increased usage this quarter</p>
          </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
          <div class="flex justify-between items-start mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Lowest Fee Method</h3>
            <span class="text-sm font-medium bg-gray-100 text-gray-800 px-2 py-1 rounded-full">-</span>
          </div>
          <div class="space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-2 bg-green-100 text-green-800 rounded-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <span>Cash</span>
            </div>
            <p class="text-sm text-gray-600">No transaction fees applied</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Method Modal -->
    <div v-if="showAddMethod" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click="showAddMethod = false">
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Add Payment Method</h3>
          <button @click="showAddMethod = false" class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div 
              v-for="option in methodOptions" 
              :key="option.id"
              class="border-2 border-gray-200 rounded-lg p-4 cursor-pointer transition-all duration-200 hover:border-purple-300"
              :class="{ 'border-purple-500 bg-purple-50': selectedMethod === option.id }"
              @click="selectedMethod = option.id"
            >
              <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mb-3">
                <component :is="option.icon" class="w-6 h-6" />
              </div>
              <div>
                <h4 class="font-medium text-gray-900 mb-1">{{ option.name }}</h4>
                <p class="text-sm text-gray-600">{{ option.description }}</p>
              </div>
            </div>
          </div>
          
          <div class="space-y-4">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Method Name</label>
              <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="e.g., PayPal, PayMaya">
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 min-h-[100px]" placeholder="Brief description of the payment method"></textarea>
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Transaction Fee (%)</label>
              <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="2.5" step="0.1" min="0">
            </div>
            
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Processing Time</label>
              <select class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="instant">Instant</option>
                <option value="1-2">1-2 Business Days</option>
                <option value="3-5">3-5 Business Days</option>
                <option value="weekly">Weekly</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
          <button @click="showAddMethod = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700" @click="addNewMethod">
            Add Method
          </button>
        </div>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Define icon components inline
const CashIcon = {
  template: `
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  `
}

const BankIcon = {
  template: `
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
    </svg>
  `
}

const DigitalWalletIcon = {
  template: `
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
    </svg>
  `
}

const CreditCardIcon = {
  template: `
    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
    </svg>
  `
}

// State
const showAddMethod = ref(false)
const selectedMethod = ref('cash')

// Payment methods data
const paymentMethods = ref([
  {
    id: 1,
    name: 'Cash',
    description: 'Physical cash payments',
    icon: CashIcon,
    enabled: true,
    transactionCount: 156,
    totalAmount: 245000
  },
  {
    id: 2,
    name: 'Bank Transfer',
    description: 'Direct bank transfers',
    icon: BankIcon,
    enabled: true,
    transactionCount: 89,
    totalAmount: 589000
  },
  {
    id: 3,
    name: 'GCash',
    description: 'Mobile wallet payments',
    icon: DigitalWalletIcon,
    enabled: true,
    transactionCount: 134,
    totalAmount: 324500
  },
  {
    id: 4,
    name: 'Credit/Debit Card',
    description: 'Card payments (manual entry)',
    icon: CreditCardIcon,
    enabled: false,
    transactionCount: 42,
    totalAmount: 178000
  }
])

const methodOptions = ref([
  {
    id: 'cash',
    name: 'Cash',
    description: 'Physical cash payments',
    icon: CashIcon
  },
  {
    id: 'bank',
    name: 'Bank Transfer',
    description: 'Direct bank transfers',
    icon: BankIcon
  },
  {
    id: 'digital',
    name: 'Digital Wallet',
    description: 'GCash, PayMaya, etc.',
    icon: DigitalWalletIcon
  },
  {
    id: 'card',
    name: 'Credit/Debit Card',
    description: 'Card payments',
    icon: CreditCardIcon
  }
])

// Methods
const toggleMethod = (method) => {
  method.enabled = !method.enabled
  alert(`${method.name} has been ${method.enabled ? 'enabled' : 'disabled'}`)
}

const editMethod = (method) => {
  selectedMethod.value = method.name.toLowerCase().replace(/ /g, '-')
  showAddMethod.value = true
}

const addNewMethod = () => {
  const newMethod = {
    id: paymentMethods.value.length + 1,
    name: 'New Payment Method',
    description: 'Newly added payment method',
    icon: DigitalWalletIcon,
    enabled: true,
    transactionCount: 0,
    totalAmount: 0
  }
  
  paymentMethods.value.push(newMethod)
  showAddMethod.value = false
  alert('New payment method added successfully!')
}

const formatCardNumber = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.length > 16) value = value.substring(0, 16)
  
  const formatted = value.replace(/(\d{4})(?=\d)/g, '$1 ')
  event.target.value = formatted
}
</script>

<style scoped>
/* Modal animation */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Smooth hover transitions */
.hover-shadow-transition {
  transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.hover-shadow-transition:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .payment-method-card {
    padding: 16px;
  }
  
  .method-stats {
    grid-template-columns: 1fr;
  }
}
</style>