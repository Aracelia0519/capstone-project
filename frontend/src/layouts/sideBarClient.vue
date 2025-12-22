<template>
  <aside 
    :class="['sidebar-client', { 
      'mobile-visible': mobileVisible,
      'collapsed': isCollapsed && !isMobile
    }]"
    :style="isCollapsed && !isMobile ? 'width: 80px' : 'width: 280px'"
  >
    <!-- Close button for mobile -->
    <button 
      v-if="isMobile"
      @click="closeSidebar"
      class="absolute top-6 right-6 text-white hover:text-gray-200 z-50 lg:hidden p-2 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 shadow-lg"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <div class="flex items-center space-x-4">
        <!-- Animated Client Logo -->
        <div class="relative">
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 via-cyan-400 to-teal-300 flex items-center justify-center shadow-2xl animate-pulse-slow">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
          </div>
          <!-- Welcome indicator -->
          <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-2 border-white"></div>
        </div>
        
        <div v-if="!isCollapsed || isMobile" class="sidebar-title">
          <h2 class="text-xl font-bold bg-gradient-to-r from-blue-400 via-cyan-400 to-teal-300 bg-clip-text text-transparent">
            Welcome Back!
          </h2>
          <p class="text-xs text-gray-200 flex items-center mt-0.5">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse"></span>
            Client Dashboard
          </p>
        </div>
      </div>
      
      <!-- Quick Stats Widget -->
      <div v-if="!isCollapsed || isMobile" class="sidebar-stats">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value text-blue-300">{{ stats.activeJobs }}</div>
            <div class="stat-label">Active Jobs</div>
          </div>
          <div class="stat-item">
            <div class="stat-value text-cyan-300">{{ stats.colorsSaved }}</div>
            <div class="stat-label">Colors</div>
          </div>
          <div class="stat-item">
            <div class="stat-value text-teal-300">{{ stats.recommendations }}</div>
            <div class="stat-label">Suggestions</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Items -->
    <nav class="sidebar-nav">
      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <span class="section-icon">🧭</span>
          Client Module
        </h3>
        <ul class="nav-list">
          <!-- Dashboard -->
          <li>
            <router-link 
              to="/Clients/dashboardC"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-blue-400 to-cyan-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Dashboard</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">Overview</div>
            </router-link>
          </li>

          <!-- My Service Requests -->
          <li>
            <router-link 
              to="/Clients/myServiceRequest"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-emerald-400 to-green-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Service Requests</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">{{ stats.activeJobs }}</div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <span class="section-icon">🎨</span>
          Visualization
        </h3>
        <ul class="nav-list">
          <!-- Color Preview (Unity - View Only) -->
          <li>
            <router-link 
              to="/Clients/colorPreview"
              @click="handleNavigation"
              class="nav-item group unity-nav-item"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-purple-400 to-pink-300 relative">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <div class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-r from-purple-400 to-pink-300 rounded-full flex items-center justify-center text-xs font-bold text-white border border-white">
                    U
                  </div>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Color Preview</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge unity-badge">View Only</div>
            </router-link>
          </li>

          <!-- Color History -->
          <li>
            <router-link 
              to="/Clients/ColorHistoryC"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-indigo-400 to-violet-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Color History</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">{{ stats.colorsSaved }}</div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <span class="section-icon">💡</span>
          Recommendations
        </h3>
        <ul class="nav-list">
          <!-- Recommendations -->
          <li>
            <router-link 
              to="/Clients/recommendation"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-amber-400 to-yellow-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Recommendations</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">{{ stats.recommendations }}</div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <span class="section-icon">👥</span>
          Service Providers
        </h3>
        <ul class="nav-list">
          <!-- Service Providers -->
          <li>
            <router-link 
              to="/Clients/serviceProviderC"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-teal-400 to-emerald-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Service Providers</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">Contact</div>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Divider -->
      <div v-if="!isCollapsed || isMobile" class="nav-divider"></div>

      <!-- Settings Section -->
      <div class="nav-section">
        <ul class="nav-list">
          <!-- Profile -->
          <li>
            <router-link 
              to="/Clients/profileC"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-gray-500 to-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Profile</span>
              </div>
            </router-link>
          </li>

          <!-- Logout -->
          <li>
            <button 
              @click="logout"
              class="nav-item group logout-btn w-full"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-red-500 to-pink-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Logout</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">🔐</div>
            </button>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Sidebar Footer -->
    <div v-if="!isCollapsed || isMobile" class="sidebar-footer">
      <div class="footer-content">
        <p class="footer-title">CaviteGo Paint</p>
        <div class="color-dots">
          <div class="color-dot bg-blue-400"></div>
          <div class="color-dot bg-cyan-400"></div>
          <div class="color-dot bg-teal-400"></div>
          <div class="color-dot bg-emerald-400"></div>
        </div>
        <p class="footer-version">Client Portal</p>
      </div>
    </div>

    <!-- Collapse Toggle Button (Desktop) -->
    <button 
      v-if="!isMobile"
      @click="toggleCollapse"
      class="collapse-toggle"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path v-if="isCollapsed" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>
  </aside>
</template>

<script>
export default {
  name: 'SideBarClient',
  props: {
    mobileVisible: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isCollapsed: false,
      isMobile: false,
      stats: {
        activeJobs: 2,
        colorsSaved: 5,
        recommendations: 8
      }
    }
  },
  watch: {
    mobileVisible(newVal) {
      if (newVal) {
        this.isMobile = window.innerWidth <= 768
      }
    },
    isCollapsed(newVal) {
      // Emit the collapsed state to parent component
      this.$emit('collapsed', newVal)
    }
  },
  methods: {
    closeSidebar() {
      this.$emit('toggle')
    },
    
    toggleCollapse() {
      this.isCollapsed = !this.isCollapsed
    },
    
    handleNavigation() {
      this.$emit('link-click')
      if (this.isMobile) {
        this.closeSidebar()
      }
    },
    
    logout() {
      console.log('Client logging out... Destroying token...')
      // Implement actual logout logic
    },
    
    checkMobile() {
      this.isMobile = window.innerWidth <= 768
    }
  },
  mounted() {
    this.checkMobile()
    window.addEventListener('resize', this.checkMobile)
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.checkMobile)
  },
  emits: ['toggle', 'link-click', 'collapsed']
}
</script>

<style scoped>
/* Main Sidebar Container */
.sidebar-client {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 280px;
  background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  z-index: 1000;
  transition: all 0.3s ease;
  border-right: 1px solid rgba(100, 116, 139, 0.3);
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
  overflow-y: auto;
}

.sidebar-client.mobile-visible {
  transform: translateX(0) !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Animations */
@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

.animate-pulse-slow {
  animation: pulse-slow 2s infinite;
}

/* Sidebar Header - Fixed Height */
.sidebar-header {
  padding: 1.25rem;
  border-bottom: 1px solid rgba(100, 116, 139, 0.3);
  background: linear-gradient(90deg, rgba(56, 189, 248, 0.1) 0%, rgba(20, 184, 166, 0.1) 100%);
  flex-shrink: 0;
}

.sidebar-stats {
  margin-top: 1rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.5rem;
}

.stat-item {
  text-align: center;
  padding: 0.5rem;
  border-radius: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-item:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 12px rgba(56, 189, 248, 0.2);
}

.stat-value {
  font-size: 1.125rem;
  font-weight: 700;
}

.stat-label {
  font-size: 0.7rem;
  color: #cbd5e1;
  margin-top: 0.125rem;
}

/* Navigation Items - Fit within sidebar */
.sidebar-nav {
  padding: 1rem 0.75rem;
  flex: 1;
  width: 100%;
  box-sizing: border-box;
}

.nav-section {
  margin-bottom: 1.25rem;
  width: 100%;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0 0.5rem 0.5rem;
  color: #94a3b8;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  width: 100%;
  box-sizing: border-box;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.section-icon {
  font-size: 0.875rem;
  flex-shrink: 0;
}

/* Navigation Items - Prevent horizontal overflow */
.nav-list {
  list-style: none;
  padding: 0;
  margin: 0;
  width: 100%;
}

.nav-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.625rem 0.75rem;
  margin-bottom: 0.375rem;
  border-radius: 0.75rem;
  text-decoration: none;
  color: #e2e8f0;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
  min-height: 44px;
  width: 100%;
  box-sizing: border-box;
  border: 1px solid transparent;
}

.nav-item:hover {
  background: linear-gradient(90deg, rgba(56, 189, 248, 0.15), rgba(20, 184, 166, 0.15));
  transform: translateX(4px);
  border-color: rgba(56, 189, 248, 0.2);
  box-shadow: 0 4px 12px rgba(56, 189, 248, 0.15);
}

.nav-item-active {
  background: linear-gradient(90deg, rgba(56, 189, 248, 0.25), rgba(20, 184, 166, 0.25));
  box-shadow: 0 4px 16px rgba(56, 189, 248, 0.25);
  border-left: 3px solid #38bdf8;
  border-color: rgba(56, 189, 248, 0.3);
}

.unity-nav-item:hover {
  background: linear-gradient(90deg, rgba(168, 85, 247, 0.15), rgba(236, 72, 153, 0.15));
  border-color: rgba(168, 85, 247, 0.2);
}

.unity-nav-item.nav-item-active {
  background: linear-gradient(90deg, rgba(168, 85, 247, 0.25), rgba(236, 72, 153, 0.25));
  border-left: 3px solid #a855f7;
}

.nav-icon-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
  flex: 1;
}

.nav-icon {
  width: 36px;
  height: 36px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.nav-item:hover .nav-icon {
  transform: scale(1.1) rotate(5deg);
}

.nav-text {
  font-weight: 500;
  font-size: 0.85rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.2s ease;
  flex: 1;
  min-width: 0;
}

.nav-item:hover .nav-text {
  color: white;
}

.nav-badge {
  font-size: 0.65rem;
  padding: 0.2rem 0.4rem;
  border-radius: 9999px;
  font-weight: 600;
  letter-spacing: 0.02em;
  background: rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
  flex-shrink: 0;
  margin-left: 0.5rem;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.unity-badge {
  background: linear-gradient(45deg, #a855f7, #ec4899);
  color: white;
  font-size: 0.6rem;
  padding: 0.2rem 0.35rem;
}

.nav-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(56, 189, 248, 0.3), transparent);
  margin: 1rem 0.5rem;
  width: calc(100% - 1rem);
}

/* Logout Button */
.logout-btn {
  background: linear-gradient(90deg, rgba(239, 68, 68, 0.1), rgba(244, 114, 182, 0.1));
  border-color: rgba(239, 68, 68, 0.2);
}

.logout-btn:hover {
  background: linear-gradient(90deg, rgba(239, 68, 68, 0.2), rgba(244, 114, 182, 0.2));
  border-color: rgba(239, 68, 68, 0.3);
}

/* Sidebar Footer - Fixed Height */
.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(100, 116, 139, 0.3);
  background: linear-gradient(90deg, rgba(56, 189, 248, 0.05), rgba(20, 184, 166, 0.05));
  flex-shrink: 0;
  width: 100%;
  box-sizing: border-box;
}

.footer-content {
  text-align: center;
  width: 100%;
}

.footer-title {
  font-size: 0.8rem;
  font-weight: 600;
  color: #7dd3fc;
  margin-bottom: 0.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.color-dots {
  display: flex;
  justify-content: center;
  gap: 0.375rem;
  margin-bottom: 0.5rem;
  width: 100%;
}

.color-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  box-shadow: 0 0 4px currentColor;
}

.footer-version {
  font-size: 0.7rem;
  color: #94a3b8;
}

/* Collapse Toggle */
.collapse-toggle {
  position: absolute;
  top: 1.25rem;
  right: 8px;
  background: linear-gradient(45deg, #38bdf8, #0ea5e9);
  color: white;
  border: 2px solid #1e293b;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  transition: all 0.2s ease;
  z-index: 1001;
}

.collapse-toggle:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 12px rgba(56, 189, 248, 0.4);
}

/* Custom Scrollbar */
.sidebar-client::-webkit-scrollbar {
  width: 6px;
}

.sidebar-client::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.5);
  border-radius: 3px;
}

.sidebar-client::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #38bdf8, #0ea5e9);
  border-radius: 3px;
}

.sidebar-client::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #0ea5e9, #0284c7);
}

/* Desktop - no vertical scroll */
@media (min-width: 769px) {
  .sidebar-client {
    overflow-y: hidden !important;
    height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .sidebar-client .sidebar-nav {
    flex: 1;
    overflow-y: hidden;
  }
}

/* Mobile styles */
@media (max-width: 768px) {
  .sidebar-client {
    transform: translateX(-100%);
    width: 280px !important;
    overflow-y: auto;
  }
  
  .sidebar-client.mobile-visible {
    transform: translateX(0);
  }
  
  .collapse-toggle {
    display: none;
  }
}

/* Desktop collapsed state */
@media (min-width: 769px) {
  .sidebar-client.collapsed {
    width: 80px !important;
  }
  
  .sidebar-client.collapsed .nav-text,
  .sidebar-client.collapsed .sidebar-title,
  .sidebar-client.collapsed .sidebar-stats,
  .sidebar-client.collapsed .nav-badge,
  .sidebar-client.collapsed .section-title,
  .sidebar-client.collapsed .nav-divider,
  .sidebar-client.collapsed .footer-title,
  .sidebar-client.collapsed .color-dots,
  .sidebar-client.collapsed .footer-version {
    display: none;
  }
  
  .sidebar-client.collapsed .nav-icon-wrapper {
    justify-content: center;
  }
  
  .sidebar-client.collapsed .nav-item {
    justify-content: center;
    padding: 0.625rem;
  }
  
  .sidebar-client.collapsed .nav-icon {
    width: 36px;
    height: 36px;
    margin: 0;
  }
  
  .sidebar-client.collapsed .sidebar-footer {
    padding: 1rem 0.25rem;
  }
}

/* Prevent horizontal scroll */
* {
  max-width: 100%;
}

.sidebar-client * {
  box-sizing: border-box;
}
</style>