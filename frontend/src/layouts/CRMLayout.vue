<template>
  <div class="crm-layout">
    <!-- Mobile Toggle Button -->
    <button class="mobile-toggle-btn" @click="toggleSidebarMobile" v-if="isMobile">
      <i :class="sidebarMobileVisible ? 'fas fa-times' : 'fas fa-bars'"></i>
    </button>

    <!-- Sidebar with Mobile Overlay -->
    <div class="sidebar-overlay" 
         v-if="isMobile && sidebarMobileVisible" 
         @click="sidebarMobileVisible = false">
    </div>

    <sideBarCRM :class="{ 'mobile-visible': sidebarMobileVisible }" 
                @toggle="handleSidebarToggle" />

    <main class="crm-content">
      <div class="content-wrapper">
        <router-view />
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import sideBarCRM from '@/layouts/sideBarCRM.vue'

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
.crm-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
  position: relative;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.crm-content {
  flex: 1;
  min-height: 100vh;
  overflow-y: auto;
  background: #f8fafc;
  margin-left: 280px;
  transition: margin-left 0.3s ease;
  position: relative;
}

.content-wrapper {
  min-height: 100vh;
  padding: 24px;
  background: #f8fafc;
}

/* When sidebar is collapsed */
.sidebar.collapsed ~ .crm-content {
  margin-left: 80px;
}

/* Mobile: no left margin since sidebar overlays */
@media (max-width: 768px) {
  .crm-content {
    margin-left: 0;
    padding: 16px;
  }
  
  .content-wrapper {
    padding: 16px;
  }
}

/* Mobile Toggle Button */
.mobile-toggle-btn {
  display: none;
  position: fixed;
  top: 20px;
  left: 20px;
  z-index: 1100;
  background: linear-gradient(45deg, #667eea, #764ba2);
  color: white;
  border: none;
  width: 44px;
  height: 44px;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1.2rem;
  box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
  transition: all 0.3s ease;
}

.mobile-toggle-btn:hover {
  transform: scale(1.05);
  box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
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
  backdrop-filter: blur(3px);
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .mobile-toggle-btn {
    display: block;
  }
}
</style>