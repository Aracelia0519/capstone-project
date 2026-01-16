<template>
  <header class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-sm shadow-lg border-b border-gray-200">
    <!-- Main Navigation -->
    <nav class="container mx-auto px-4 py-3">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/ECommerceClient" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
              Paint Store
            </h1>
            <p class="text-xs text-gray-500">Premium Paints & Services</p>
          </div>
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center space-x-6">
          <div v-for="item in navItems" :key="item.id" class="relative">
            <router-link
              v-if="item.route"
              :to="item.route"
              @click="setActiveItem(item.id)"
              :class="[
                'flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all duration-300 group relative',
                activeItem === item.id
                  ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-600 shadow-sm'
                  : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath"></path>
              </svg>
              <span>{{ item.label }}</span>
              <span v-if="item.id === 'cart' && cartCount > 0" 
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                {{ cartCount }}
              </span>
            </router-link>
            
            <!-- Logout Button (no route) -->
            <button
              v-else
              @click="handleLogout"
              :class="[
                'flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all duration-300 group',
                activeItem === item.id
                  ? 'bg-gradient-to-r from-red-50 to-orange-50 text-red-600 shadow-sm'
                  : 'text-gray-700 hover:text-red-600 hover:bg-gray-50'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath"></path>
              </svg>
              <span>{{ item.label }}</span>
            </button>
          </div>

          <!-- User Profile Dropdown -->
          <div class="relative ml-6">
            <button
              @click="toggleProfileDropdown"
              class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-all duration-300 group"
            >
              <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <span class="text-sm font-medium text-gray-700">Julian Namoc</span>
              <svg :class="['w-4 h-4 text-gray-500 transition-transform duration-300', profileDropdownOpen ? 'rotate-180' : '']" 
                   fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>

            <!-- Profile Dropdown Menu -->
            <div
              v-show="profileDropdownOpen"
              class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden animate-slideDown z-50"
            >
              <div class="p-2">
                <div class="px-3 py-2 border-b border-gray-100">
                  <p class="text-sm font-medium text-gray-900">Julian Namoc</p>
                  <p class="text-xs text-gray-500">IspyMILK@gmail.com</p>
                </div>
                
                <router-link
                  v-for="item in profileMenuItems"
                  :key="item.id"
                  :to="item.route"
                  @click="handleProfileMenuClick(item.id)"
                  class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-50 transition-all duration-300 text-gray-700 hover:text-blue-600"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath"></path>
                  </svg>
                  <span class="text-sm">{{ item.label }}</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <div class="flex items-center space-x-3 lg:hidden">
          <!-- Cart Icon for Mobile -->
          <router-link
            to="/ECommerceClient/EccommerceCart"
            class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span v-if="cartCount > 0" 
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
              {{ cartCount }}
            </span>
          </router-link>
          
          <button
            @click="toggleMobileMenu"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
          >
            <div class="relative w-6 h-6">
              <span :class="[
                'absolute block w-6 h-0.5 bg-gray-700 transform transition-all duration-300',
                mobileMenuOpen ? 'rotate-45 top-3' : 'top-1'
              ]"></span>
              <span :class="[
                'absolute block w-6 h-0.5 bg-gray-700 transition-all duration-300',
                mobileMenuOpen ? 'opacity-0' : 'top-3 opacity-100'
              ]"></span>
              <span :class="[
                'absolute block w-6 h-0.5 bg-gray-700 transform transition-all duration-300',
                mobileMenuOpen ? '-rotate-45 top-3' : 'top-5'
              ]"></span>
            </div>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div
        v-show="mobileMenuOpen"
        class="lg:hidden mt-3 bg-white rounded-xl shadow-2xl border border-gray-100 overflow-hidden animate-slideDown"
      >
        <div class="p-3 space-y-1">
          <div
            v-for="item in navItems"
            :key="item.id"
            class="mb-1"
          >
            <router-link
              v-if="item.route"
              :to="item.route"
              @click="handleMobileNavClick(item.id)"
              :class="[
                'flex items-center space-x-2 p-3 rounded-lg font-medium transition-all duration-300 relative',
                activeItem === item.id
                  ? 'bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath"></path>
              </svg>
              <span>{{ item.label }}</span>
              <span v-if="item.id === 'cart' && cartCount > 0" 
                    class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                {{ cartCount }}
              </span>
            </router-link>
            
            <button
              v-else
              @click="handleLogout"
              :class="[
                'flex items-center space-x-2 p-3 rounded-lg font-medium transition-all duration-300 w-full text-left',
                activeItem === item.id
                  ? 'bg-gradient-to-r from-red-50 to-orange-50 text-red-600'
                  : 'text-gray-700 hover:bg-gray-50 hover:text-red-600'
              ]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath"></path>
              </svg>
              <span>{{ item.label }}</span>
            </button>
          </div>

          <!-- User Info in Mobile Menu -->
          <div class="pt-3 border-t border-gray-100">
            <div class="flex items-center space-x-3 p-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900">Julian Namoc</p>
                <p class="text-xs text-gray-500">IspyMILK@gmail.com</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Progress Bar -->
    <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600"></div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const activeItem = ref('shop')
const mobileMenuOpen = ref(false)
const profileDropdownOpen = ref(false)
const cartCount = ref(3) // Example cart count - would come from backend

// Navigation Items - Updated routes to match your routing structure
const navItems = ref([
  {
    id: 'shop',
    label: 'Shop',
    route: '/ECommerceClient/EccommerceShop',
    iconPath: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'
  },
  {
    id: 'services',
    label: 'Services',
    route: '/ECommerceClient/EccommerceServices',
    iconPath: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'
  },
  {
    id: 'cart',
    label: 'Cart',
    route: '/ECommerceClient/EccommerceCart',
    iconPath: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'
  },
  {
    id: 'orders',
    label: 'Orders',
    route: '/ECommerceClient/EccommerceOrders',
    iconPath: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
  },
  {
    id: 'profile',
    label: 'Profile',
    route: '/ECommerceClient/EccommerceProfile',
    iconPath: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
  },
  {
    id: 'logout',
    label: 'Logout',
    route: null, // No route for logout - handled by click event
    iconPath: 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'
  }
])

// Profile Menu Items - Updated routes
const profileMenuItems = ref([
  {
    id: 'profile',
    label: 'My Profile',
    route: '/ECommerceClient/EccommerceProfile',
    iconPath: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
  },
  {
    id: 'settings',
    label: 'Settings',
    route: '/ECommerceClient/EccommerceProfile?tab=preferences',
    iconPath: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
  },
  {
    id: 'help',
    label: 'Help & Support',
    route: '/ECommerceClient/EccommerceProfile?tab=account',
    iconPath: 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'
  }
])

// Methods
const setActiveItem = (itemId) => {
  activeItem.value = itemId
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
  if (mobileMenuOpen.value) {
    profileDropdownOpen.value = false
  }
}

const toggleProfileDropdown = () => {
  profileDropdownOpen.value = !profileDropdownOpen.value
  if (profileDropdownOpen.value) {
    mobileMenuOpen.value = false
  }
}

const handleMobileNavClick = (itemId) => {
  setActiveItem(itemId)
  mobileMenuOpen.value = false
  
  // Handle special actions
  if (itemId === 'logout') {
    handleLogout()
  }
}

const handleProfileMenuClick = (itemId) => {
  setActiveItem(itemId)
  profileDropdownOpen.value = false
  
  if (itemId === 'profile') {
    router.push('/ECommerceClient/EccommerceProfile')
  }
}

const handleLogout = () => {
  console.log('Logout functionality would be implemented here')
  // In a real app, this would:
  // 1. Clear authentication tokens
  // 2. Clear user data from store/localStorage
  // 3. Redirect to login page
  router.push('/login')
}

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  const target = event.target
  if (!target.closest('.relative') && !target.closest('.lg\\:hidden')) {
    profileDropdownOpen.value = false
  }
}

// Lifecycle hooks
onMounted(() => {
  // Set active item based on current route
  const currentRoute = router.currentRoute.value.path
  const routeItem = navItems.value.find(item => 
    item.route && currentRoute === item.route
  )
  if (routeItem) {
    activeItem.value = routeItem.id
  }
  
  // Add click outside listener
  document.addEventListener('click', handleClickOutside)
})

// Cleanup
import { onUnmounted } from 'vue'
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slideDown {
  animation: slideDown 0.3s ease-out forwards;
}

/* Hover effects for better UX */
.group:hover .group-hover\:text-blue-400 {
  color: #60a5fa;
}

.group:hover .group-hover\:bg-blue-200 {
  background-color: #dbeafe;
}

/* Smooth transitions for all interactive elements */
button, a {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus styles for accessibility */
button:focus, a:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Custom scrollbar for dropdown */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>