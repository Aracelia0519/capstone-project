<template>
  <div class="finance-layout">
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle-btn" @click="toggleSidebarMobile" v-if="isMobile">
      <svg v-if="sidebarMobileVisible" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <!-- Sidebar with Mobile Overlay -->
    <div class="sidebar-overlay" 
         v-if="isMobile && sidebarMobileVisible" 
         @click="sidebarMobileVisible = false">
    </div>

    <sideBarFinance 
      :class="{ 'mobile-visible': sidebarMobileVisible }" 
      @toggle="handleSidebarToggle"
      :user-name="userName"
      :user-role="userRole" />

    <main class="finance-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import sideBarFinance from './sideBarFinance.vue'
import axios from '@/utils/axios'

const userName = ref('')
const userRole = ref('')
const isLoading = ref(false)

// Fetch user data
const fetchUserData = async () => {
  isLoading.value = true
  try {
    // Get user profile
    const userResponse = await axios.get('/auth/me')
    if (userResponse.data && userResponse.data.user) {
      const user = userResponse.data.user
      userName.value = user.name || `${user.first_name} ${user.last_name}` || 'Finance Officer'
      userRole.value = user.role ? 
        user.role.charAt(0).toUpperCase() + user.role.slice(1).replace(/_/g, ' ') 
        : 'Finance Department'
    }
  } catch (error) {
    console.error('Failed to fetch user data:', error)
    // Fallback to localStorage
    const storedUser = localStorage.getItem('user_data')
    if (storedUser) {
      try {
        const user = JSON.parse(storedUser)
        userName.value = user.name || `${user.first_name} ${user.last_name}` || 'Finance Officer'
        userRole.value = user.role ? 
          user.role.charAt(0).toUpperCase() + user.role.slice(1).replace(/_/g, ' ') 
          : 'Finance Department'
      } catch (e) {
        console.error('Failed to parse stored user data:', e)
      }
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchUserData()
})

const sidebarMobileVisible = ref(false)
const isMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768
}

const toggleSidebarMobile = () => {
  sidebarMobileVisible.value = !sidebarMobileVisible.value
}

const handleSidebarToggle = () => {
  if (isMobile.value) {
    sidebarMobileVisible.value = !sidebarMobileVisible.value
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.finance-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
  position: relative;
  background: linear-gradient(135deg, #f0fff4 0%, #e6fffa 100%);
}

.finance-content {
  flex: 1;
  min-height: 100vh;
  overflow-y: auto;
  background: #f0fff4;
  margin-left: 280px;
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 20px;
}

/* When sidebar is collapsed */
.sidebar.collapsed ~ .finance-content {
  margin-left: 80px;
}

/* Mobile: no left margin since sidebar overlays */
@media (max-width: 768px) {
  .finance-content {
    margin-left: 0;
    padding: 15px;
  }
}

/* Mobile Toggle Button */
.mobile-toggle-btn {
  display: none;
  position: fixed;
  top: 15px;
  left: 15px;
  z-index: 1100;
  background: linear-gradient(45deg, #10b981, #059669);
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mobile-toggle-btn:hover {
  background: linear-gradient(45deg, #059669, #047857);
  transform: scale(1.05);
}

/* Sidebar Overlay for Mobile */
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  backdrop-filter: blur(2px);
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .mobile-toggle-btn {
    display: flex;
  }
}

/* Content animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(20px);
  opacity: 0;
}
</style>