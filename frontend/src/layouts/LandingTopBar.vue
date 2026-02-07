<template>
  <header class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-sm shadow-lg border-b border-gray-100">
    <nav class="container mx-auto px-4 h-20 flex items-center justify-between">
      <router-link to="/" class="flex items-center space-x-3 group">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center shadow-md group-hover:shadow-lg transition-all duration-300">
          <Paintbrush class="w-5 h-5 text-white" />
        </div>
        <div>
          <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
            CaviteGo Paint
          </h1>
          <p class="text-xs text-gray-500">Color Your World Beautifully</p>
        </div>
      </router-link>

      <div class="hidden lg:flex items-center space-x-2">
        <Button
          v-for="item in navItems"
          :key="item.id"
          variant="ghost"
          as-child
          class="font-medium transition-all"
          :class="activeItem === item.id ? 'text-blue-600 bg-blue-50' : 'text-gray-700 hover:text-blue-600'"
        >
          <router-link :to="item.route" @click="activeItem = item.id">
            {{ item.label }}
          </router-link>
        </Button>

        <div class="flex items-center space-x-3 ml-6">
          <Button 
            variant="outline" 
            class="border-blue-500 text-blue-500 hover:bg-blue-50 rounded-lg font-semibold"
            @click="navigateTo('/Landing/logIn')"
          >
            <LogIn class="w-4 h-4 mr-2" />
            Login
          </Button>
          <Button 
            class="bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-lg font-semibold shadow-md hover:shadow-xl transition-all hover:scale-105"
            @click="navigateTo('/Landing/SignUp')"
          >
            <UserPlus class="w-4 h-4 mr-2" />
            Get Started
          </Button>
        </div>
      </div>

      <Button variant="ghost" class="lg:hidden p-2" @click="mobileMenuOpen = !mobileMenuOpen">
        <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
        <X v-else class="w-6 h-6" />
      </Button>
    </nav>

    <div v-if="mobileMenuOpen" class="lg:hidden border-t bg-white px-4 py-6 space-y-4 animate-in slide-in-from-top duration-300">
      <div class="space-y-1">
        <Button 
          v-for="item in navItems" 
          :key="item.id" 
          variant="ghost" 
          class="w-full justify-start h-12 rounded-xl"
          :class="activeItem === item.id ? 'bg-blue-50 text-blue-600' : ''"
          @click="handleMobileNavClick(item.id)"
        >
          <router-link :to="item.route" class="flex items-center w-full">
            {{ item.label }}
          </router-link>
        </Button>
      </div>
      <div class="pt-4 border-t border-gray-100 flex flex-col gap-3">
        <Button variant="outline" class="w-full border-blue-500 text-blue-500" @click="handleMobileNavClick('login')">Login</Button>
        <Button class="w-full bg-gradient-to-r from-blue-500 to-purple-500 text-white" @click="handleMobileNavClick('register')">Get Started Free</Button>
      </div>
    </div>
    <div class="h-1 w-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Button } from '@/components/ui/button'
import { Paintbrush, Menu, X, LogIn, UserPlus } from 'lucide-vue-next'

const router = useRouter()
const activeItem = ref('home')
const mobileMenuOpen = ref(false)

const navItems = [
  { id: 'home', label: 'Home', route: '/Landing/homeLanding' },
  { id: 'how-it-works', label: 'How It Works', route: '/Landing/HowItWorks' },
  { id: 'explore-colors', label: 'Explore Colors', route: '/Landing/exploreColors' },
  { id: 'services', label: 'Services', route: '/Landing/servicesLanding' }
]

const navigateTo = (route) => {
  router.push(route)
  mobileMenuOpen.value = false
}

const handleMobileNavClick = (itemId) => {
  if (itemId === 'login') navigateTo('/Landing/logIn')
  else if (itemId === 'register') navigateTo('/Landing/SignUp')
  else {
    activeItem.value = itemId
    mobileMenuOpen.value = false
  }
}

onMounted(() => {
  const currentRoute = router.currentRoute.value.path
  const routeItem = navItems.find(item => item.route === currentRoute)
  if (routeItem) activeItem.value = routeItem.id
})
</script>