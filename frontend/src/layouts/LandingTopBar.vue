<template>
  <header class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-sm shadow-lg border-b border-gray-100">
    <!-- Main Navigation -->
    <nav class="container mx-auto px-4 py-3">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
          </div>
          <div>
            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
              CaviteGo Paint
            </h1>
            <p class="text-xs text-gray-500">Color Your World Beautifully</p>
          </div>
        </router-link>

        <!-- Desktop Navigation -->
        <div class="hidden lg:flex items-center space-x-6">
          <div v-for="item in navItems" :key="item.id" class="relative">
            <router-link
              :to="item.route"
              @click="setActiveItem(item.id)"
              :class="[
                'flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all duration-300 group',
                activeItem === item.id
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-blue-600 shadow-sm'
                  : 'text-gray-700 hover:text-blue-600 hover:bg-gray-50'
              ]"
            >
              <span>{{ item.label }}</span>
            </router-link>
          </div>

          <!-- CTA Buttons -->
          <div class="flex items-center space-x-3 ml-6">
            <button
              @click="navigateTo('/Landing/logIn')"
              class="px-5 py-2.5 border border-blue-500 text-blue-500 rounded-lg font-semibold hover:bg-blue-50 transition-all duration-300 hover:shadow-md flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
              </svg>
              <span>Login</span>
            </button>
            <button
              @click="navigateTo('/Landing/SignUp')"
              class="px-5 py-2.5 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg font-semibold shadow-md hover:shadow-xl transition-all duration-300 hover:scale-105 hover:from-blue-600 hover:to-purple-600 flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
              <span>Get Started</span>
            </button>
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <button
          @click="toggleMobileMenu"
          class="lg:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors"
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
              :to="item.route"
              @click="handleMobileNavClick(item.id)"
              :class="[
                'flex items-center space-x-2 p-3 rounded-lg font-medium transition-all duration-300',
                activeItem === item.id
                  ? 'bg-gradient-to-r from-blue-50 to-purple-50 text-blue-600'
                  : 'text-gray-700 hover:bg-gray-50'
              ]"
            >
              <span>{{ item.label }}</span>
            </router-link>
          </div>

          <!-- Mobile CTA Buttons -->
          <div class="pt-3 border-t border-gray-100 space-y-2">
            <button
              @click="handleMobileNavClick('login')"
              class="w-full p-3 border border-blue-500 text-blue-500 rounded-lg font-semibold hover:bg-blue-50 transition-all duration-300 flex items-center justify-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
              </svg>
              <span>Login</span>
            </button>
            <button
              @click="handleMobileNavClick('register')"
              class="w-full p-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg font-semibold shadow-md hover:shadow-xl transition-all duration-300 hover:from-blue-600 hover:to-purple-600 flex items-center justify-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
              </svg>
              <span>Get Started Free</span>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Progress Bar -->
    <div class="h-1 w-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const activeItem = ref('home')
const mobileMenuOpen = ref(false)

// Navigation Items
const navItems = ref([
  {
    id: 'home',
    label: 'Home',
    route: '/Landing/homeLanding',
    iconPath: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
  },
  {
    id: 'how-it-works',
    label: 'How It Works',
    route: '/Landing/HowItWorks',
    iconPath: 'M13 10V3L4 14h7v7l9-11h-7z'
  },
  {
    id: 'explore-colors',
    label: 'Explore Colors',
    route: '/Landing/exploreColors',
    iconPath: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
  },
  {
    id: 'services',
    label: 'Services',
    route: '/Landing/servicesLanding',
    iconPath: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
  }
])

// Methods
const setActiveItem = (itemId) => {
  activeItem.value = itemId
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const handleMobileNavClick = (itemId) => {
  setActiveItem(itemId)
  mobileMenuOpen.value = false
  
  if (itemId === 'login') {
    router.push('/login')
  } else if (itemId === 'register') {
    router.push('/register')
  }
}

const navigateTo = (route) => {
  router.push(route)
  mobileMenuOpen.value = false
}

// Lifecycle hooks
onMounted(() => {
  // Set active item based on current route
  const currentRoute = router.currentRoute.value.path
  const routeItem = navItems.value.find(item => 
    item.route === currentRoute
  )
  if (routeItem) {
    activeItem.value = routeItem.id
  }
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
</style>