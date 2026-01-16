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
      @collapsed="handleSidebarCollapsed" />

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
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import SideBarECommerce from './sideBarECommerce.vue'

const router = useRouter()

// Sidebar states
const sidebarMobileVisible = ref(false)
const isMobile = ref(false)
const sidebarCollapsed = ref(false)
const isLoading = ref(false)

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
  checkMobile()
  window.addEventListener('resize', checkMobile)
  
  // Simulate loading
  isLoading.value = true
  setTimeout(() => {
    isLoading.value = false
  }, 800)
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

/* Smooth transition for the main content when sidebar collapses/expands */
.ecommerce-content {
  transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>