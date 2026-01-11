<template>
  <div class="client-layout">
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle-btn" @click="toggleSidebarMobile" v-if="isMobile">
      <i :class="sidebarMobileVisible ? 'fas fa-times' : 'fas fa-user'"></i>
    </button>

    <!-- Sidebar with Mobile Overlay -->
    <div class="sidebar-overlay" 
         v-if="isMobile && sidebarMobileVisible" 
         @click="sidebarMobileVisible = false">
    </div>

    <SideBarClient 
      :mobileVisible="sidebarMobileVisible"
      @toggle="handleSidebarToggle"
      @link-click="handleSidebarToggle"
      @collapsed="handleSidebarCollapsed" />

    <!-- Main Content - Dynamically adjusts based on sidebar collapse state -->
    <div 
      class="client-content"
      :class="{ 'sidebar-collapsed': sidebarCollapsed && !isMobile }"
      :style="sidebarCollapsed && !isMobile ? 'margin-left: 80px' : 'margin-left: 280px'"
    >
      <router-view />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import SideBarClient from './sideBarClient.vue'
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
    
    // Get dashboard data based on role
    const roleEndpoints = {
      admin: '/dashboard/admin',
      distributor: '/dashboard/distributor',
      service_provider: '/dashboard/service-provider',
      client: '/dashboard/client'
    }
    
    if (roleEndpoints[userData.value.role]) {
      const dashboardResponse = await axios.get(roleEndpoints[userData.value.role])
      dashboardData.value = dashboardResponse.data.data
    }
    
  } catch (error) {
    console.error('Failed to fetch data:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchData()
})

const sidebarMobileVisible = ref(false)
const isMobile = ref(false)
const sidebarCollapsed = ref(false)

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

const handleSidebarCollapsed = (isCollapsed) => {
  sidebarCollapsed.value = isCollapsed
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
.client-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
  position: relative;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

.client-content {
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
.client-content.sidebar-collapsed {
  margin-left: 80px;
  width: calc(100% - 80px);
}

/* Animated background for client area */
.client-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: 
    radial-gradient(circle at 20% 80%, rgba(56, 189, 248, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(20, 184, 166, 0.1) 0%, transparent 50%);
  pointer-events: none;
}

/* Mobile: no left margin since sidebar overlays */
@media (max-width: 768px) {
  .client-content {
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
  background: linear-gradient(45deg, #38bdf8, #0ea5e9);
  color: white;
  border: none;
  width: 48px;
  height: 48px;
  border-radius: 12px;
  cursor: pointer;
  font-size: 1.3rem;
  box-shadow: 0 6px 12px rgba(56, 189, 248, 0.3);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mobile-toggle-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 8px 16px rgba(56, 189, 248, 0.4);
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

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .mobile-toggle-btn {
    display: flex;
  }
}

/* Smooth transition for all interactive elements */
* {
  transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
}

/* Smooth transition for the main content when sidebar collapses/expands */
.client-content {
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>