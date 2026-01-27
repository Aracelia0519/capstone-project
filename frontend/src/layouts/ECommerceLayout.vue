<!-- ECommerceLayout.vue -->
<template>
  <div class="ecommerce-layout">
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle-btn" @click="toggleSidebarMobile" v-if="isMobile">
      <i :class="sidebarMobileVisible ? 'fas fa-times' : 'fas fa-shopping-cart'"></i>
    </button>

    <!-- Sidebar with Mobile Overlay -->
    <div class="sidebar-overlay" 
         v-if="isMobile && sidebarMobileVisible" 
         @click="sidebarMobileVisible = false">
    </div>

    <SideBarECommerce 
      :mobileVisible="sidebarMobileVisible"
      @toggle="handleSidebarToggle"
      @link-click="handleSidebarToggle"
      @collapsed="handleSidebarCollapsed"
      @logout-click="showLogoutModal = true"
      :userData="userData"
      :dashboardData="dashboardData" />

    <!-- Main Content - Dynamically adjusts based on sidebar collapse state -->
    <div 
      class="ecommerce-content"
      :class="{ 
        'sidebar-collapsed': sidebarCollapsed && !isMobile,
        'loading': isLoading
      }"
      :style="sidebarCollapsed && !isMobile ? 'margin-left: 80px' : 'margin-left: 280px'"
    >
      <div v-if="isLoading" class="content-loading">
        <div class="loading-spinner"></div>
        <p>Loading e-commerce dashboard...</p>
      </div>
      <template v-else>
        <router-view />
      </template>
    </div>

    <!-- Logout Modal (Now at viewport level) -->
    <transition name="fade">
      <div 
        v-if="showLogoutModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="cancelLogout"
      >
        <div class="relative w-full max-w-md">
          <!-- Animated background effect with E-commerce colors -->
          <div class="absolute -inset-4 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-2xl animate-pulse"></div>
          
          <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Modal header with animated paint drip -->
            <div class="relative p-6 border-b border-gray-700/50">
              <!-- Paint drip animation with E-commerce colors -->
              <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-16 h-8">
                <div class="w-4 h-4 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full mx-auto animate-drip"></div>
                <div class="w-2 h-4 bg-gradient-to-b from-indigo-400/50 to-transparent rounded-full mx-auto animate-drip-delay"></div>
              </div>
              
              <div class="flex items-center justify-center mb-4">
                <div class="relative">
                  <!-- Animated logout icon with E-commerce gradient -->
                  <div class="w-20 h-20 rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-2xl animate-pulse-slow">
                    <svg class="w-10 h-10 text-white animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                  </div>
                  <!-- Pulsing ring with indigo/purple theme -->
                  <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-full animate-ping-slow"></div>
                </div>
              </div>
              
              <h3 class="text-2xl font-bold text-center bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-2">
                Logout Confirmation
              </h3>
              <p class="text-gray-300 text-center">
                Are you sure you want to logout from E-commerce Hub?
              </p>
            </div>

            <!-- Modal body -->
            <div class="p-6">
              <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border border-indigo-500/20">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 p-2 rounded-lg bg-gradient-to-r from-indigo-500/20 to-purple-500/20">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-gray-200 mb-1">E-commerce Session Ending</h4>
                    <p class="text-xs text-gray-400">
                      You'll be redirected to the login page. Your current cart and preferences will be saved.
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
                  class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl hover:from-indigo-600 hover:to-purple-600 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group relative overflow-hidden"
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

    <!-- Success Toast Notification (Viewport level) -->
    <transition name="slide-fade">
      <div 
        v-if="showSuccessToast"
        class="fixed top-6 right-6 z-[99999] max-w-sm"
      >
        <div class="toast-notification success">
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
      </div>
    </transition>

    <!-- Error Toast Notification (Viewport level) -->
    <transition name="slide-fade">
      <div 
        v-if="showErrorToast"
        class="fixed top-6 right-6 z-[99999] max-w-sm"
      >
        <div class="toast-notification error">
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
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import SideBarECommerce from './sideBarECommerce.vue'
import axios from '@/utils/axios'

const router = useRouter()

// Sidebar states
const sidebarMobileVisible = ref(false)
const isMobile = ref(false)
const sidebarCollapsed = ref(false)
const isLoading = ref(false)
const userData = ref(null)
const dashboardData = ref(null)

// Logout modal states
const showLogoutModal = ref(false)
const showSuccessToast = ref(false)
const showErrorToast = ref(false)
const errorMessage = ref('')
const isLoggingOut = ref(false)

// Fetch user data and dashboard data
const fetchData = async () => {
  isLoading.value = true
  try {
    // Get user profile
    const userResponse = await axios.get('/auth/me')
    userData.value = userResponse.data.user
    
    // Get e-commerce dashboard data
    
    
  } catch (error) {
    console.error('Failed to fetch data:', error)
  } finally {
    isLoading.value = false
  }
}

// Logout functions
const cancelLogout = () => {
  showLogoutModal.value = false
}

const confirmLogout = async () => {
  try {
    isLoggingOut.value = true
    
    // Call the backend logout endpoint
    const response = await axios.post('/auth/logout')
    
    if (response.data.status === 'success') {
      console.log('Logout successful')
      
      // Clear all local storage items
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user_data')
      localStorage.removeItem('user_role')
      localStorage.removeItem('user_name')
      localStorage.removeItem('remember_me')
      localStorage.removeItem('cart_items') // Clear cart
      
      // Clear any session storage
      sessionStorage.clear()
      
      // Clear axios authorization header
      delete axios.defaults.headers.common['Authorization']
      
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
    localStorage.removeItem('cart_items')
    sessionStorage.clear()
    delete axios.defaults.headers.common['Authorization']
    
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

// Responsive methods
const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768
  if (!isMobile.value) {
    sidebarMobileVisible.value = false
  }
}

const toggleSidebarMobile = () => {
  sidebarMobileVisible.value = !sidebarMobileVisible.value
}

const handleSidebarToggle = () => {
  if (isMobile.value) {
    sidebarMobileVisible.value = !sidebarMobileVisible.value
  }
}

const handleSidebarCollapsed = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed
}

// Initialize on mount
onMounted(() => {
  fetchData()
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

// Cleanup
onBeforeUnmount(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.ecommerce-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
  position: relative;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

.ecommerce-content {
  flex: 1;
  min-height: 100vh;
  overflow-y: auto;
  margin-left: 280px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: transparent;
  position: relative;
  width: calc(100% - 280px);
}

/* When sidebar is collapsed on desktop */
.ecommerce-content.sidebar-collapsed {
  margin-left: 80px;
  width: calc(100% - 80px);
}

/* Loading state */
.ecommerce-content.loading {
  display: flex;
  align-items: center;
  justify-content: center;
}

.content-loading {
  text-align: center;
  color: #94a3b8;
  z-index: 10;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 3px solid rgba(79, 70, 229, 0.3);
  border-radius: 50%;
  border-top-color: #4f46e5;
  animation: spin 1s ease-in-out infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Animated background for e-commerce area */
.ecommerce-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(79, 70, 229, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.1) 0%, transparent 50%);
  pointer-events: none;
}

/* Mobile: no left margin since sidebar overlays */
@media (max-width: 768px) {
  .ecommerce-content {
    margin-left: 0 !important;
    width: 100% !important;
  }
}

/* Mobile Toggle Button */
.mobile-toggle-btn {
  display: none;
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1100;
  background: linear-gradient(45deg, #4f46e5, #6366f1);
  color: white;
  border: none;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  cursor: pointer;
  font-size: 1.3rem;
  box-shadow: 0 6px 12px rgba(79, 70, 229, 0.3);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mobile-toggle-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 8px 16px rgba(79, 70, 229, 0.4);
}

/* Sidebar Overlay for Mobile */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(15, 23, 42, 0.7);
  z-index: 999;
  backdrop-filter: blur(4px);
  animation: fadeIn 0.3s ease;
}

/* Toast Notifications */
.toast-notification {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(10px);
  animation: slideIn 0.3s ease;
  min-width: 300px;
}

.toast-notification.success {
  border-left: 4px solid #10b981;
}

.toast-notification.error {
  border-left: 4px solid #ef4444;
}

.toast-icon {
  flex-shrink: 0;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toast-notification.success .toast-icon {
  background: rgba(16, 185, 129, 0.2);
  color: #10b981;
}

.toast-notification.error .toast-icon {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.toast-content {
  flex: 1;
}

.toast-title {
  font-weight: 600;
  color: white;
  font-size: 0.95rem;
  margin-bottom: 0.25rem;
}

.toast-message {
  color: #94a3b8;
  font-size: 0.85rem;
}

.toast-close {
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 4px;
  transition: all 0.2s;
}

.toast-close:hover {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.toast-progress {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 0 0 12px 12px;
  overflow: hidden;
}

.toast-progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #10b981, #34d399);
  animation: progress 2s linear forwards;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes progress {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Modal animations */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active, .slide-fade-leave-active {
  transition: all 0.3s ease;
}

.slide-fade-enter-from {
  transform: translateY(-10px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateY(10px);
  opacity: 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .mobile-toggle-btn {
    display: flex;
  }
  
  .toast-notification {
    min-width: calc(100vw - 3rem);
    max-width: calc(100vw - 3rem);
  }
}

/* Smooth transition for the main content when sidebar collapses/expands */
.ecommerce-content {
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>