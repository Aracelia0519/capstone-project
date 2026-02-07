<template>
  <header class="sticky top-0 z-50 w-full border-b bg-white/80 backdrop-blur-md">
    <div class="container mx-auto px-4 h-16 flex items-center justify-between">
      
      <router-link to="/ECommerceClient" class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
          <ShoppingBag class="w-5 h-5 text-white" />
        </div>
        <div class="hidden sm:block">
          <h1 class="text-lg font-bold tracking-tight text-slate-900">Paint Store</h1>
          <p class="text-[10px] uppercase font-bold text-blue-600 tracking-widest">Ecommerce</p>
        </div>
      </router-link>

      <nav class="hidden lg:flex items-center gap-1">
        <Button 
          v-for="item in navItems" 
          :key="item.id"
          variant="ghost"
          as-child
          class="relative h-10 px-4 font-medium transition-all"
          :class="activeItem === item.id ? 'text-blue-600 bg-blue-50' : 'text-slate-600'"
        >
          <router-link :to="item.route" @click="activeItem = item.id">
            <component :is="item.icon" class="w-4 h-4 mr-2" />
            {{ item.label }}
            <Badge v-if="item.id === 'cart' && cartCount > 0" class="ml-2 bg-blue-600 text-[10px] h-4 px-1">
              {{ cartCount }}
            </Badge>
          </router-link>
        </Button>
      </nav>

      <div class="flex items-center gap-2">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="relative h-10 w-10 rounded-full ring-2 ring-slate-100 ring-offset-2">
              <Avatar class="h-8 w-8">
                <div class="bg-gradient-to-tr from-blue-600 to-indigo-600 w-full h-full flex items-center justify-center">
                  <User class="w-4 h-4 text-white" />
                </div>
              </Avatar>
            </Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="w-56 mt-2 rounded-xl p-2">
            <div class="px-2 py-1.5 mb-2">
              <p class="text-sm font-bold text-slate-900">{{ userName }}</p>
              <p class="text-xs text-slate-500 truncate">{{ userEmail }}</p>
            </div>
            <DropdownMenuSeparator />
            <DropdownMenuItem v-for="p in profileMenuItems" :key="p.id" as-child class="rounded-lg">
              <router-link :to="p.route" class="w-full flex items-center">
                <component :is="p.icon" class="mr-2 h-4 w-4 text-slate-400" />
                {{ p.label }}
              </router-link>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem @click="showLogoutModal = true" class="text-red-600 focus:text-red-600 rounded-lg">
              <LogOut class="mr-2 h-4 w-4" />
              Logout
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>

        <Button variant="ghost" class="lg:hidden p-2" @click="mobileMenuOpen = !mobileMenuOpen">
          <Menu v-if="!mobileMenuOpen" class="w-6 h-6" />
          <X v-else class="w-6 h-6" />
        </Button>
      </div>
    </div>

    <div v-if="mobileMenuOpen" class="lg:hidden border-t bg-white px-4 py-4 space-y-1">
      <Button 
        v-for="item in navItems" 
        :key="item.id" 
        variant="ghost" 
        class="w-full justify-start h-12 rounded-xl"
        :class="activeItem === item.id ? 'bg-blue-50 text-blue-600' : ''"
        @click="mobileMenuOpen = false; activeItem = item.id"
      >
        <router-link :to="item.route" class="flex items-center w-full">
          <component :is="item.icon" class="w-5 h-5 mr-3" />
          {{ item.label }}
        </router-link>
      </Button>
    </div>

    <Dialog :open="showLogoutModal" @update:open="showLogoutModal = $event">
      <DialogContent class="sm:max-w-[400px] rounded-3xl">
        <div class="flex flex-col items-center py-4">
          <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mb-4">
            <LogOut class="w-8 h-8 text-red-500" />
          </div>
          <DialogTitle class="text-xl font-bold">End Session?</DialogTitle>
          <p class="text-slate-500 text-center mt-2">Are you sure you want to sign out of your account?</p>
          <div class="grid grid-cols-2 gap-3 w-full mt-8">
            <Button variant="outline" @click="showLogoutModal = false" class="rounded-xl">Cancel</Button>
            <Button @click="handleLogout" variant="destructive" class="rounded-xl bg-red-600 hover:bg-red-700">
              Logout
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ShoppingBag, Store, Wrench, ShoppingCart, 
  Package, User, UserCircle, Settings, 
  HelpCircle, LogOut, Menu, X 
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar } from '@/components/ui/avatar'
import { 
  DropdownMenu, DropdownMenuContent, DropdownMenuItem, 
  DropdownMenuSeparator, DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'

const router = useRouter()
const activeItem = ref('shop')
const mobileMenuOpen = ref(false)
const showLogoutModal = ref(false)
const cartCount = ref(3)
const userName = ref('Julian Namoc')
const userEmail = ref('IspyMILK@gmail.com')

const navItems = [
  { id: 'shop', label: 'Shop', route: '/ECommerceClient/EccommerceShop', icon: Store },
  { id: 'services', label: 'Services', route: '/ECommerceClient/EccommerceServices', icon: Wrench },
  { id: 'cart', label: 'Cart', route: '/ECommerceClient/EccommerceCart', icon: ShoppingCart },
  { id: 'orders', label: 'Orders', route: '/ECommerceClient/EccommerceOrders', icon: Package }
]

const profileMenuItems = [
  { id: 'profile', label: 'My Profile', route: '/ECommerceClient/EccommerceProfile', icon: UserCircle },
  { id: 'settings', label: 'Settings', route: '/ECommerceClient/EccommerceProfile?tab=preferences', icon: Settings },
  { id: 'help', label: 'Support', route: '/ECommerceClient/EccommerceProfile?tab=account', icon: HelpCircle }
]

const handleLogout = () => {
  localStorage.clear()
  router.push('/Landing/logIn')
}


</script>