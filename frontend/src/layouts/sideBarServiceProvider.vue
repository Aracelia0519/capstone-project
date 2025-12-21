<template>
  <aside 
    :class="['sidebar', { 
      'mobile-visible': mobileVisible,
      'collapsed': isCollapsed && !isMobile
    }]"
    :style="isCollapsed && !isMobile ? 'width: 80px' : 'width: 280px'"
  >
    <!-- Close button for mobile -->
    <button 
      v-if="isMobile"
      @click="closeSidebar"
      class="absolute top-6 right-6 text-white hover:text-gray-200 z-50 lg:hidden p-2 rounded-full bg-gradient-to-r from-red-500 to-pink-600 shadow-lg"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <div class="flex items-center space-x-4">
        <!-- Animated Paint Palette Logo -->
        <div class="relative">
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-2xl">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
          </div>
          <!-- Active status indicator -->
          <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 rounded-full border-2 border-white"></div>
        </div>
        
        <div v-if="!isCollapsed || isMobile" class="sidebar-title">
          <h2 class="text-xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">
            Paint Pro
          </h2>
          <p class="text-xs text-gray-300 flex items-center mt-0.5">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5"></span>
            Service Provider
          </p>
        </div>
      </div>
      
      <!-- Quick Stats Widget -->
      
    </div>

    <!-- Navigation Items -->
    <nav class="sidebar-nav">
      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <span class="section-icon">🧭</span>
          Dashboard
        </h3>
        <ul class="nav-list">
          <!-- Dashboard -->
          <li>
            <router-link 
              to="/serviceProvider/dashboardSP"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-cyan-500 to-blue-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Dashboard</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">New</div>
            </router-link>
          </li>

          <!-- Clients -->
          <li>
            <router-link 
              to="/serviceProvider/Clients"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-emerald-500 to-green-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Clients</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">24</div>
            </router-link>
          </li>

          <!-- Service Requests / Jobs -->
          <li>
            <router-link 
              to="/serviceProvider/ServiceRequestsJobs"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-amber-500 to-orange-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Service Jobs</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">12</div>
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
          <!-- Virtual Paint Color Mixer (UNITY) -->
          <li>
            <router-link 
              to="/serviceProvider/VirtualPaintColor"
              @click="handleNavigation"
              class="nav-item group unity-nav-item"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-purple-600 to-pink-500 relative">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                  <div class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-xs font-bold text-white border border-white">
                    U
                  </div>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Color Mixer</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge unity-badge">UNITY</div>
            </router-link>
          </li>

          <!-- Color History -->
          <li>
            <router-link 
              to="/serviceProvider/ColorHistory"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-violet-500 to-indigo-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Color History</span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <span class="section-icon">📦</span>
          Resources
        </h3>
        <ul class="nav-list">
          <!-- Paint Products -->
          <li>
            <router-link 
              to="/serviceProvider/PaintProductsSP"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-blue-500 to-cyan-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Paint Products</span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge">Read Only</div>
            </router-link>
          </li>

          <!-- Distributors -->
          <li>
            <router-link 
              to="/serviceProvider/Distributors"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-teal-500 to-emerald-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Distributors</span>
              </div>
            </router-link>
          </li>

          <!-- Reports -->
          <li>
            <router-link 
              to="/serviceProvider/ReportsSP"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-rose-500 to-pink-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Reports</span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Divider -->
      <div v-if="!isCollapsed || isMobile" class="nav-divider"></div>

      <!-- Settings Section -->
      <div class="nav-section">
        <ul class="nav-list">
          <li>
            <router-link 
              to="/serviceProvider/ProfileSettingsSP"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-gray-600 to-gray-500">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Profile</span>
              </div>
            </router-link>
          </li>

          <li>
            <button 
              @click="logout"
              class="nav-item group logout-btn w-full"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-red-600 to-red-500">
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
          <div class="color-dot bg-blue-500"></div>
          <div class="color-dot bg-green-500"></div>
          <div class="color-dot bg-yellow-500"></div>
          <div class="color-dot bg-red-500"></div>
        </div>
        <p class="footer-version">v2.0</p>
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
  name: 'SideBarServiceProvider',
  props: {
    mobileVisible: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isCollapsed: false,
      isMobile: false
    }
  },
  watch: {
    mobileVisible(newVal) {
      if (newVal) {
        this.isMobile = window.innerWidth <= 768
      }
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
      console.log('Logging out... Destroying token...')
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
  emits: ['toggle', 'link-click']
}
</script>

<style scoped>
/* Main Sidebar Container */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 280px;
  background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  z-index: 1000;
  transition: all 0.3s ease;
  border-right: 1px solid rgba(74, 85, 104, 0.5);
  display: flex;
  flex-direction: column;
  overflow-x: hidden; /* Prevent horizontal scroll */
  overflow-y: auto; /* Allow vertical scroll only on mobile */
}

.sidebar.mobile-visible {
  transform: translateX(0) !important;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

/* Sidebar Header - Fixed Height */
.sidebar-header {
  padding: 1.25rem;
  border-bottom: 1px solid rgba(74, 85, 104, 0.5);
  background: linear-gradient(90deg, rgba(66, 153, 225, 0.1) 0%, rgba(159, 122, 234, 0.1) 100%);
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
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
}

.stat-item:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.1);
}

.stat-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: white;
}

.stat-label {
  font-size: 0.7rem;
  color: #a0aec0;
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
  color: #cbd5e0;
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
  border-radius: 0.625rem;
  text-decoration: none;
  color: #cbd5e0;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
  min-height: 44px;
  width: 100%;
  box-sizing: border-box;
}

.nav-item:hover {
  background: linear-gradient(90deg, rgba(66, 153, 225, 0.2), rgba(159, 122, 234, 0.2));
  transform: translateX(4px);
}

.nav-item-active {
  background: linear-gradient(90deg, rgba(66, 153, 225, 0.3), rgba(159, 122, 234, 0.3));
  box-shadow: 0 4px 12px rgba(66, 153, 225, 0.2);
  border-left: 3px solid #4299e1;
}

.unity-nav-item:hover {
  background: linear-gradient(90deg, rgba(159, 122, 234, 0.3), rgba(236, 72, 153, 0.3));
}

.unity-nav-item.nav-item-active {
  background: linear-gradient(90deg, rgba(159, 122, 234, 0.4), rgba(236, 72, 153, 0.4));
}

.nav-icon-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0; /* Allow text to truncate */
  flex: 1;
}

.nav-icon {
  width: 36px;
  height: 36px;
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.nav-item:hover .nav-icon {
  transform: scale(1.1);
}

.nav-text {
  font-weight: 500;
  font-size: 0.85rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color 0.2s ease;
  flex: 1;
  min-width: 0; /* Allow text truncation */
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
}

.unity-badge {
  background: linear-gradient(45deg, #9f7aea, #ec4899);
  color: white;
  font-size: 0.6rem;
  padding: 0.2rem 0.35rem;
}

.nav-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(66, 153, 225, 0.3), transparent);
  margin: 1rem 0.5rem;
  width: calc(100% - 1rem);
}

/* Logout Button */
.logout-btn {
  background: linear-gradient(90deg, rgba(245, 101, 101, 0.1), rgba(229, 62, 62, 0.2));
}

.logout-btn:hover {
  background: linear-gradient(90deg, rgba(245, 101, 101, 0.2), rgba(229, 62, 62, 0.3));
}

/* Sidebar Footer - Fixed Height */
.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(74, 85, 104, 0.5);
  background: linear-gradient(90deg, rgba(66, 153, 225, 0.1), rgba(159, 122, 234, 0.1));
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
  color: #90cdf4;
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
  width: 6px;
  height: 6px;
  border-radius: 50%;
}

.footer-version {
  font-size: 0.7rem;
  color: #718096;
}

/* Collapse Toggle */
.collapse-toggle {
  position: absolute;
  top: 1.25rem;
  right: 8px;
  background: linear-gradient(45deg, #4299e1, #9f7aea);
  color: white;
  border: 2px solid #2d3748;
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
  box-shadow: 0 6px 12px rgba(66, 153, 225, 0.4);
}

/* Custom Scrollbar for Mobile */
.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: rgba(45, 55, 72, 0.5);
  border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #4299e1, #9f7aea);
  border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #3182ce, #805ad5);
}

/* Hide vertical scrollbar on desktop when not needed */
/* Desktop - no vertical scroll at all */
@media (min-width: 769px) {
  .sidebar {
    overflow-y: hidden !important; /* Never show scrollbar */
    height: 100vh;
    display: flex;
    flex-direction: column;
  }

  .sidebar .sidebar-nav {
    flex: 1;
    overflow-y: hidden; /* prevent nav from scrolling */
  }

  .sidebar:hover {
    overflow-y: hidden; /* still hidden on hover */
  }
}


/* Mobile styles */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    width: 280px !important;
    overflow-y: auto; /* Always show scrollbar on mobile if needed */
  }
  
  .sidebar.mobile-visible {
    transform: translateX(0);
  }
  
  .collapse-toggle {
    display: none;
  }
}

/* Desktop collapsed state */
@media (min-width: 769px) {
  .sidebar.collapsed {
    width: 80px !important;
  }
  
  .sidebar.collapsed .nav-text,
  .sidebar.collapsed .sidebar-title,
  .sidebar.collapsed .sidebar-stats,
  .sidebar.collapsed .nav-badge,
  .sidebar.collapsed .section-title,
  .sidebar.collapsed .nav-divider,
  .sidebar.collapsed .footer-title,
  .sidebar.collapsed .color-dots,
  .sidebar.collapsed .footer-version {
    display: none;
  }
  
  .sidebar.collapsed .nav-icon-wrapper {
    justify-content: center;
  }
  
  .sidebar.collapsed .nav-item {
    justify-content: center;
    padding: 0.625rem;
  }
  
  .sidebar.collapsed .nav-icon {
    width: 36px;
    height: 36px;
    margin: 0;
  }
  
  .sidebar.collapsed .sidebar-footer {
    padding: 1rem 0.25rem;
  }
}

/* Prevent horizontal scroll */
* {
  max-width: 100%;
}

.sidebar * {
  box-sizing: border-box;
}
</style>