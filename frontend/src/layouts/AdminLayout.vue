<template>
  <div class="admin-layout">
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle-btn" @click="toggleSidebarMobile" v-if="isMobile">
      <i :class="sidebarMobileVisible ? 'fas fa-times' : 'fas fa-bars'"></i>
    </button>

    <!-- Sidebar with Mobile Overlay -->
    <div class="sidebar-overlay" 
         v-if="isMobile && sidebarMobileVisible" 
         @click="sidebarMobileVisible = false">
    </div>

    <sideBarAdmin :class="{ 'mobile-visible': sidebarMobileVisible }" 
                  @toggle="handleSidebarToggle" />

    <main class="admin-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import sideBarAdmin from '@/layouts/sideBarAdmin.vue'
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
.admin-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
  position: relative;
}

.admin-content {
  flex: 1;
  height: 100vh;
  overflow-y: auto;
  background: #f5f6fa;
  margin-left: 280px; /* <-- added */
  transition: margin-left 0.3s ease;
}

/* When sidebar is collapsed */
.sidebar.collapsed ~ .admin-content {
  margin-left: 80px;
}

/* Mobile: no left margin since sidebar overlays */
@media (max-width: 768px) {
  .admin-content {
    margin-left: 0;
  }
}



/* Mobile Toggle Button */
.mobile-toggle-btn {
  display: none;
  position: fixed;
  top: 15px;
  left: 15px;
  z-index: 1100;
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.2rem;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
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
    display: block;
  }
}
</style>