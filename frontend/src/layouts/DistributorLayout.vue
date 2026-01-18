<template>
  <div class="admin-layout">
    <!-- Verification Required Modal -->
    <VerificationModal 
      v-if="showVerificationModal" 
      @close="showVerificationModal = false"
      :verification-status="verificationStatus"
    />

    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle-btn" @click="toggleSidebarMobile" v-if="isMobile">
      <i :class="sidebarMobileVisible ? 'fas fa-times' : 'fas fa-bars'"></i>
    </button>

    <!-- Sidebar with Mobile Overlay -->
    <div class="sidebar-overlay" 
         v-if="isMobile && sidebarMobileVisible" 
         @click="sidebarMobileVisible = false">
    </div>

    <sideBarDistributor 
      :class="{ 'mobile-visible': sidebarMobileVisible }" 
      @toggle="handleSidebarToggle"
      :verification-status="verificationStatus"
      @open-verification-modal="showVerificationModal = true"
    />

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
import { ref, onMounted, onBeforeUnmount, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import sideBarDistributor from './sideBarDistributor.vue'
import VerificationModal from './VerificationModal.vue'
import axios from '@/utils/axios'

const route = useRoute()
const userData = ref(null)
const dashboardData = ref(null)
const isLoading = ref(false)
const verificationStatus = ref(null)
const showVerificationModal = ref(false)

// Check if verification is required from route query
watch(() => route.query, (newQuery) => {
  if (newQuery.verification_required === 'true') {
    verificationStatus.value = newQuery.status || 'none'
    showVerificationModal.value = true
  }
}, { immediate: true })

// Fetch user data and verification status
const fetchData = async () => {
  isLoading.value = true
  try {
    // Get user profile
    const userResponse = await axios.get('/auth/me')
    userData.value = userResponse.data.user
    
    // Get verification status
    if (userData.value.role === 'distributor') {
      try {
        const verificationResponse = await axios.get('/distributor/requirements')
        if (verificationResponse.data.status === 'success') {
          verificationStatus.value = verificationResponse.data.data?.status || 'none'
        }
      } catch (error) {
        console.error('Failed to fetch verification status:', error)
        verificationStatus.value = 'none'
      }
    }
    
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
  @import "../layouts/styles/distributorLayout.css";
</style>