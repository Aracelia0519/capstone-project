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

    <sideBarDistributor :class="{ 'mobile-visible': sidebarMobileVisible }" 
                        @toggle="handleSidebarToggle" />

    

      <!-- Main Content -->
      <div
  class="admin-content content-wrapper"
  :class="{ collapsed: sidebarCollapsed }"
>
  <router-view />
</div>



  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import sideBarDistributor from './sideBarDistributor.vue'
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

const props = defineProps({
  pageTitle: {
    type: String,
    default: 'Dashboard'
  }
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

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

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
  background: #f5f6fa;
}

.admin-content {
  flex: 1;
  min-height: 100vh;
  overflow-y: auto;
  background: #f8fafc;
  margin-left: 280px;
  transition: margin-left 0.3s ease;
  display: flex;
  flex-direction: column;
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

/* Admin Header */
.admin-header {
  background: white;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e2e8f0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-left {
  flex: 1;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.25rem;
}

.role-badge {
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.page-subtitle {
  color: #64748b;
  font-size: 0.875rem;
}

.header-right {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.header-btn {
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
}

.header-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.new-order-btn {
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  color: white;
  border: none;
}

.new-order-btn:hover {
  opacity: 0.9;
}

.notification-btn {
  position: relative;
  width: 40px;
  height: 40px;
  justify-content: center;
  padding: 0;
}

.notification-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: #ef4444;
  color: white;
  font-size: 0.625rem;
  width: 18px;
  height: 18px;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
}

/* Breadcrumb */
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  font-size: 0.875rem;
  color: #64748b;
}

.breadcrumb-item {
  color: #4A90E2;
  text-decoration: none;
  transition: color 0.2s ease;
}

.breadcrumb-item:hover {
  color: #9B59B6;
  text-decoration: underline;
}

.breadcrumb-separator {
  font-size: 0.75rem;
  opacity: 0.5;
}

.breadcrumb-current {
  color: #1e293b;
  font-weight: 500;
}

/* Content Wrapper */
.content-wrapper {
  flex: 1;
  padding: 2rem;
}

/* Admin Footer */
.admin-footer {
  margin-top: auto;
  padding: 1.5rem 2rem;
  background: white;
  border-top: 1px solid #e2e8f0;
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
}

.footer-update {
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: #94a3b8;
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
  transition: all 0.3s ease;
}

.mobile-toggle-btn:hover {
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
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .mobile-toggle-btn {
    display: block;
  }

  .admin-header {
    padding: 1rem;
    margin-top: 60px;
  }

  .header-content {
    flex-direction: column;
  }

  .header-right {
    width: 100%;
    justify-content: space-between;
  }

  .page-title {
    font-size: 1.5rem;
  }

  .content-wrapper {
    padding: 1rem;
  }

  .admin-footer {
    padding: 1rem;
    font-size: 0.75rem;
  }
}

@media (max-width: 480px) {
  .header-btn {
    padding: 0.5rem;
    font-size: 0.75rem;
  }

  .new-order-btn span {
    display: none;
  }

  .new-order-btn::after {
    content: '+';
    font-size: 1rem;
  }
}
</style>