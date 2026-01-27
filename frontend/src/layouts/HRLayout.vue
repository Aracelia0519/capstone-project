<template>
  <div class="hr-layout">
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle-btn" @click="toggleSidebarMobile" v-if="isMobile">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path v-if="sidebarMobileVisible" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>

    <!-- Sidebar with Mobile Overlay -->
    <div class="sidebar-overlay" 
         v-if="isMobile && sidebarMobileVisible" 
         @click="sidebarMobileVisible = false">
    </div>

    <sideBarHR :class="{ 'mobile-visible': sidebarMobileVisible }" 
               :user-data="userData"
               :dashboard-data="dashboardData"
               @toggle="handleSidebarToggle" />

    <main class="hr-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import sideBarHR from '@/layouts/sideBarHR.vue'
import axios from '@/utils/axios'

const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)

// Fetch user data and dashboard data
const fetchData = async () => {
  isLoading.value = true
  try {
    // Get user profile
    const userResponse = await axios.get('/auth/me')
    userData.value = userResponse.data.user
    
  } catch (error) {
    console.error('Failed to fetch HR data:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchData()
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
.hr-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
  position: relative;
  background: #f8fafc;
}

.hr-content {
  flex: 1;
  min-height: 100vh;
  overflow-y: auto;
  background: #f8fafc;
  margin-left: 280px;
  transition: margin-left 0.3s ease;
  padding: 20px;
}

/* When sidebar is collapsed */
.sidebar.collapsed ~ .hr-content {
  margin-left: 80px;
}

/* Mobile: no left margin since sidebar overlays */
@media (max-width: 768px) {
  .hr-content {
    margin-left: 0;
    padding: 20px;
  }
}

/* Mobile Toggle Button */
.mobile-toggle-btn {
  display: none;
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1100;
  background: linear-gradient(135deg, #3b82f6, #10b981);
  color: white;
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.mobile-toggle-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
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

/* Animation for page content */
.hr-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Content styling */
.hr-content h1 {
  color: #1e293b;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1.5rem;
}

.hr-content p {
  color: #64748b;
  line-height: 1.6;
}
</style>