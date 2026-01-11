<template>
  <div class="sidebar" :class="{ collapsed: isCollapsed, 'mobile-visible': mobileVisible }">
    <!-- Enhanced Logout Confirmation Modal -->
    <transition name="fade">
      <div 
        v-if="showLogoutModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="cancelLogout"
      >
        <div class="relative w-full max-w-md">
          <!-- Animated background effect with Admin colors -->
          <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-2xl animate-pulse"></div>
          
          <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Modal header with animated paint drip -->
            <div class="relative p-6 border-b border-gray-700/50">
              <!-- Paint drip animation with Admin colors -->
              <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-16 h-8">
                <div class="w-4 h-4 bg-gradient-to-b from-blue-500 to-purple-500 rounded-full mx-auto animate-drip"></div>
                <div class="w-2 h-4 bg-gradient-to-b from-blue-400/50 to-transparent rounded-full mx-auto animate-drip-delay"></div>
              </div>
              
              <div class="flex items-center justify-center mb-4">
                <div class="relative">
                  <!-- Animated logout icon with Admin gradient -->
                  <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-2xl animate-pulse-slow">
                    <svg class="w-10 h-10 text-white animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                  </div>
                  <!-- Pulsing ring with blue/purple theme -->
                  <div class="absolute inset-0 border-4 border-blue-500/30 rounded-full animate-ping-slow"></div>
                </div>
              </div>
              
              <h3 class="text-2xl font-bold text-center bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-2">
                Logout Confirmation
              </h3>
              <p class="text-gray-300 text-center">
                Are you sure you want to logout?
              </p>
            </div>

            <!-- Modal body -->
            <div class="p-6">
              <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 p-2 rounded-lg bg-gradient-to-r from-blue-500/20 to-purple-500/20">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-gray-200 mb-1">Admin Session Ending</h4>
                    <p class="text-xs text-gray-400">
                      You'll be redirected to the login page. Your current admin session will be securely terminated.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Action buttons -->
              <div class="flex flex-col sm:flex-row gap-3">
                <button
                  @click="cancelLogout"
                  :disabled="isLoggingOut"
                  class="flex-1 py-3 px-6 rounded-xl border border-gray-600 bg-gray-800/50 text-gray-300 font-semibold hover:bg-gray-700/50 hover:text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Cancel</span>
                  </div>
                </button>
                
                <button
                  @click="confirmLogout"
                  :disabled="isLoggingOut"
                  class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl hover:from-blue-600 hover:to-purple-600 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group relative overflow-hidden"
                >
                  <!-- Animated shine effect -->
                  <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute top-0 left-0 w-12 h-full bg-white/20 skew-x-12 animate-shine"></div>
                  </div>
                  
                  <div class="relative flex items-center justify-center space-x-2">
                    <svg v-if="isLoggingOut" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg v-else class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>{{ isLoggingOut ? 'Logging out...' : 'Yes, Logout' }}</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Modal footer -->
            <div class="px-6 py-4 border-t border-gray-700/50 bg-gray-900/50">
              <div class="flex items-center justify-center space-x-2 text-xs text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Secure logout powered by Laravel Sanctum</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Success Toast Notification -->
    <transition name="slide-fade">
      <div 
        v-if="showSuccessToast"
        class="toast-notification success"
      >
        <div class="toast-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="toast-content">
          <h4 class="toast-title">Successfully Logged Out!</h4>
          <p class="toast-message">Redirecting to login page...</p>
        </div>
        <div class="toast-progress">
          <div class="toast-progress-bar"></div>
        </div>
      </div>
    </transition>

    <!-- Error Toast Notification -->
    <transition name="slide-fade">
      <div 
        v-if="showErrorToast"
        class="toast-notification error"
      >
        <div class="toast-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <div class="toast-content">
          <h4 class="toast-title">Logout Failed</h4>
          <p class="toast-message">{{ errorMessage }}</p>
        </div>
        <button @click="showErrorToast = false" class="toast-close">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </transition>

    <!-- Logo Section -->
    <div class="logo-section" @click="logoClicked">
      <div class="logo">
        <div class="paint-can-icon">🎨</div>
        <h2 v-if="!isCollapsed">CaviteGo Paint</h2>
      </div>
      <button class="toggle-btn" @click.stop="toggleCollapse">
        <span v-if="!isCollapsed">«</span>
        <span v-else>»</span>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="nav-menu">
      <div class="nav-section">
        <div class="section-label" v-if="!isCollapsed">MAIN NAVIGATION</div>
        <ul>
          <li 
            v-for="item in mainNavItems" 
            :key="item.id"
            :class="{ active: activeItem === item.id }"
            @click="setActiveItem(item.id)"
          >
            <router-link :to="item.route" class="nav-link" @click="handleNavClick">
              <span class="nav-icon">
                <svg v-if="item.id === 'dashboard'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <svg v-if="item.id === 'users'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0c-.832.055-1.68.113-2.5.113-4.97 0-9-2.239-9-5s4.03-5 9-5c1.72 0 3.32.404 4.786 1.09" />
                </svg>
                <svg v-if="item.id === 'products'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <svg v-if="item.id === 'inventory'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <svg v-if="item.id === 'colors'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                <svg v-if="item.id === 'services'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-if="item.id === 'reports'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </span>
              <span class="nav-text" v-if="!isCollapsed">{{ item.text }}</span>
              <span class="badge" v-if="item.badge && !isCollapsed">{{ item.badge }}</span>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section" v-if="!isCollapsed">
        <div class="section-label">SYSTEM</div>
        <ul>
          <li 
            v-for="item in systemNavItems" 
            :key="item.id"
            :class="{ active: activeItem === item.id }"
            @click="setActiveItem(item.id)"
          >
            <router-link :to="item.route" class="nav-link" @click="handleNavClick">
              <span class="nav-icon">
                <svg v-if="item.id === 'settings'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <svg v-if="item.id === 'audit'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </span>
              <span class="nav-text">{{ item.text }}</span>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>

    <!-- User Profile -->
    <div class="user-profile" v-if="!isCollapsed">
      <div class="user-avatar">
        <div class="avatar">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <div class="user-info">
          <strong>{{ userName || 'System Admin' }}</strong>
          <small>Administrator</small>
        </div>
      </div>
      <button class="logout-btn" @click="showLogoutModal = true">
        <span class="logout-icon">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </span>
        <span class="logout-text">Logout</span>
      </button>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'

export default {
  name: 'AdminSidebar',
  props: {
    mobileVisible: {
      type: Boolean,
      default: false
    }
  },
  setup(props, { emit }) {
    const router = useRouter()
    const isCollapsed = ref(false)
    const activeItem = ref('dashboard')
    const userName = ref('')
    const showLogoutModal = ref(false)
    const showSuccessToast = ref(false)
    const showErrorToast = ref(false)
    const errorMessage = ref('')
    const isLoggingOut = ref(false)

    const currentColors = ref([
      { id: 1, name: 'Ocean Blue', hex: '#4A90E2' },
      { id: 2, name: 'Sunset Orange', hex: '#FF6B6B' },
      { id: 3, name: 'Meadow Green', hex: '#51C16B' },
      { id: 4, name: 'Lavender', hex: '#9B59B6' },
    ])

    const mainNavItems = ref([
      { id: 'dashboard', icon: 'dashboard', text: 'Dashboard', route: '/admin/dashboard', badge: 'Live' },
      { id: 'users', icon: 'users', text: 'User Management', route: '/admin/UserManagement', badge: '4' },
      { id: 'products', icon: 'products', text: 'Paint Products', route: '/admin/PaintProducts' },
      { id: 'inventory', icon: 'inventory', text: 'Inventory Overview', route: '/admin/Inventory', badge: '12' },
      { id: 'colors', icon: 'colors', text: 'Color Customizations', route: '/admin/ColorCustomization', badge: 'New' },
      { id: 'services', icon: 'services', text: 'Service Requests', route: '/admin/ServiceRequest' },
      { id: 'reports', icon: 'reports', text: 'Reports', route: '/admin/Reports' },
    ])

    const systemNavItems = ref([
      { id: 'settings', icon: 'settings', text: 'System Settings', route: '/admin/SystemSettings' },
      { id: 'audit', icon: 'audit', text: 'Audit Logs', route: '/admin/AuditLogs' },
    ])

    const loadUserData = () => {
      const userData = localStorage.getItem('user_data')
      if (userData) {
        try {
          const user = JSON.parse(userData)
          userName.value = user.name || `${user.first_name} ${user.last_name}` || 'System Admin'
        } catch (e) {
          console.error('Failed to parse user data:', e)
        }
      }
    }

    const logoClicked = () => {
      isCollapsed.value = !isCollapsed.value
    }

    const toggleCollapse = () => {
      isCollapsed.value = !isCollapsed.value
    }

    const setActiveItem = (itemId) => {
      activeItem.value = itemId
    }

    const cancelLogout = () => {
      showLogoutModal.value = false
    }

    const confirmLogout = async () => {
      try {
        isLoggingOut.value = true
        
        // Call the backend logout endpoint
        const response = await api.post('/auth/logout')
        
        if (response.data.status === 'success') {
          console.log('Logout successful')
          
          // Clear all local storage items
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user_data')
          localStorage.removeItem('user_role')
          localStorage.removeItem('user_name')
          localStorage.removeItem('remember_me')
          
          // Clear any session storage
          sessionStorage.clear()
          
          // Clear axios authorization header
          delete api.defaults.headers.common['Authorization']
          
          // Hide modal and show success toast
          showLogoutModal.value = false
          showSuccessToast.value = true
          
          // Redirect to login page after 2 seconds
          setTimeout(() => {
            router.push('/Landing/logIn')
          }, 2000)
          
        } else {
          console.error('Logout failed:', response.data.message)
          errorMessage.value = response.data.message || 'Logout failed. Please try again.'
          showLogoutModal.value = false
          showErrorToast.value = true
        }
        
      } catch (error) {
        console.error('Logout error:', error)
        
        // Even if API call fails, clear local data
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user_data')
        localStorage.removeItem('user_role')
        localStorage.removeItem('user_name')
        localStorage.removeItem('remember_me')
        sessionStorage.clear()
        delete api.defaults.headers.common['Authorization']
        
        // Show appropriate error message
        if (error.response) {
          console.error('Response error:', error.response.data)
          // Check if the error is actually success
          if (error.response.data.status === 'success') {
            showSuccessToast.value = true
            setTimeout(() => {
              router.push('/Landing/logIn')
            }, 2000)
            return
          }
          errorMessage.value = error.response.data.message || 'Server error'
        } else if (error.request) {
          errorMessage.value = 'No response from server'
        } else {
          errorMessage.value = error.message
        }
        
        showLogoutModal.value = false
        showErrorToast.value = true
        
        // Still redirect to login after showing error
        setTimeout(() => {
          router.push('/Landing/logIn')
        }, 3000)
        
      } finally {
        isLoggingOut.value = false
      }
    }

    const handleNavClick = () => {
      // Close sidebar on mobile when a nav item is clicked
      if (props.mobileVisible) {
        emit('toggle')
      }
    }

    onMounted(() => {
      loadUserData()
      
      // Randomly change color palette every 30 seconds for visual appeal
      setInterval(() => {
        const hue = Math.floor(Math.random() * 360)
        currentColors.value = currentColors.value.map((color, index) => ({
          ...color,
          hex: `hsl(${hue + (index * 90)}, 70%, 60%)`
        }))
      }, 30000)
    })

    return {
      isCollapsed,
      activeItem,
      userName,
      currentColors,
      mainNavItems,
      systemNavItems,
      showLogoutModal,
      showSuccessToast,
      showErrorToast,
      errorMessage,
      isLoggingOut,
      logoClicked,
      toggleCollapse,
      setActiveItem,
      cancelLogout,
      confirmLogout,
      handleNavClick
    }
  },
  
  emits: ['toggle']
}
</script>

<style scoped>
  @import "../layouts/styles/adminSideBar.css";
</style>