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
      class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 z-50 lg:hidden"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Sidebar Header -->
    <div class="sidebar-header">
      <div class="flex items-center space-x-3">
        <div class="relative">
          <!-- Animated paint palette icon -->
          <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-lg animate-pulse">
            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
            </svg>
          </div>
          <!-- Online status indicator -->
          <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white animate-ping"></div>
        </div>
        <div v-if="!isCollapsed || isMobile" class="sidebar-title">
          <h2 class="text-xl font-bold text-gray-800">Distributor Hub</h2>
          <p class="text-sm text-gray-600 flex items-center">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
            Active • Paint System
          </p>
        </div>
      </div>
      
      <!-- Quick stats -->
      <div v-if="!isCollapsed || isMobile" class="sidebar-stats">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value">12</div>
            <div class="stat-label">Orders</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">85%</div>
            <div class="stat-label">Stock</div>
          </div>
          <div class="stat-item">
            <div class="stat-value">3</div>
            <div class="stat-label">Low</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Items -->
    <nav class="sidebar-nav">
      <ul class="nav-list">
        <!-- Dashboard -->
        <li>
          <router-link 
            to="/distributor/distributordashboard"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-blue-500 to-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Dashboard</span>
            </div>
            <div v-if="!isCollapsed || isMobile" class="nav-badge bg-blue-100 text-blue-600">New</div>
          </router-link>
        </li>

        <!-- Paint Inventory -->
        <li>
          <router-link 
            to="/distributor/PaintInventory"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-green-500 to-emerald-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Paint Inventory</span>
            </div>
            <div v-if="!isCollapsed || isMobile" class="nav-badge bg-red-100 text-red-600 animate-pulse">3 Low</div>
          </router-link>
        </li>

        <!-- Orders / Requests -->
        <li>
          <router-link 
            to="/distributor/OrdersRequest"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-orange-500 to-amber-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Orders / Requests</span>
            </div>
            <div v-if="!isCollapsed || isMobile" class="nav-badge bg-orange-100 text-orange-600">5</div>
          </router-link>
        </li>

        <!-- Color Demand Insights -->
        <li>
          <router-link 
            to="/distributor/ColorDemandInsights"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-purple-500 to-pink-400 relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <!-- AI Indicator -->
                <div class="absolute -top-1 -right-1 w-2 h-2 bg-purple-300 rounded-full animate-ping"></div>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Color Demand Insights</span>
            </div>
            <div v-if="!isCollapsed || isMobile" class="nav-badge bg-purple-100 text-purple-600 font-bold">AI</div>
          </router-link>
        </li>

        <!-- Sales History -->
        <li>
          <router-link 
            to="/distributor/SalesHistory"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-indigo-500 to-violet-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Sales History</span>
            </div>
          </router-link>
        </li>

        <!-- Service Providers -->
        <li>
          <router-link 
            to="/distributor/ServiceProviders"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-teal-500 to-cyan-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Service Providers</span>
            </div>
            <div v-if="!isCollapsed || isMobile" class="nav-badge bg-teal-100 text-teal-600">24</div>
          </router-link>
        </li>

        <!-- Reports -->
        <li>
          <router-link 
            to="/distributor/ReportsD"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-pink-500 to-rose-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Reports</span>
            </div>
          </router-link>
        </li>

        <!-- Divider -->
        <div v-if="!isCollapsed || isMobile" class="nav-divider"></div>

        <!-- Profile / Settings -->
        <li>
          <router-link 
            to="/distributor/ProfileSettings"
            @click="handleNavigation"
            class="nav-item group"
            active-class="nav-item-active"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-gray-500 to-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Profile / Settings</span>
            </div>
          </router-link>
        </li>

        <!-- Logout -->
        <li>
          <button 
            @click="logout"
            class="nav-item group logout-btn"
          >
            <div class="nav-icon-wrapper">
              <div class="nav-icon bg-gradient-to-r from-red-500 to-red-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
              </div>
              <span v-if="!isCollapsed || isMobile" class="nav-text">Logout</span>
            </div>
          </button>
        </li>
      </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div v-if="!isCollapsed || isMobile" class="sidebar-footer">
      <div class="footer-content">
        <p class="footer-title">CaviteGo Paint System</p>
        <div class="color-dots">
          <div class="color-dot bg-blue-500 animate-bounce" style="animation-delay: 0s"></div>
          <div class="color-dot bg-green-500 animate-bounce" style="animation-delay: 0.2s"></div>
          <div class="color-dot bg-yellow-500 animate-bounce" style="animation-delay: 0.4s"></div>
          <div class="color-dot bg-red-500 animate-bounce" style="animation-delay: 0.6s"></div>
        </div>
        <p class="footer-version">Dashboard v1.0.0</p>
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
  name: 'SidebarDistributor',
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
      // Implement logout logic
      console.log('Logging out...')
      // Destroy token, redirect to login, etc.
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
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  width: 280px;
  background: linear-gradient(to bottom, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.98));
  backdrop-filter: blur(10px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  overflow-y: auto;
  border-right: 1px solid rgba(226, 232, 240, 0.6);
}

.sidebar.mobile-visible {
  transform: translateX(0) !important;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

/* Sidebar Header */
.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(226, 232, 240, 0.6);
}

.sidebar-title h2 {
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
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
  background: white;
  border-radius: 0.75rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease;
}

.stat-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stat-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
}

.stat-label {
  font-size: 0.75rem;
  color: #64748b;
}

/* Navigation */
.sidebar-nav {
  padding: 1rem 0.75rem;
  flex: 1;
}

.nav-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  margin-bottom: 0.375rem;
  border-radius: 0.75rem;
  text-decoration: none;
  color: #475569;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.nav-item::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.6s;
}

.nav-item:hover::before {
  left: 100%;
}

.nav-item:hover {
  background: linear-gradient(90deg, rgba(74, 144, 226, 0.1), rgba(155, 89, 182, 0.1));
  transform: translateX(5px);
}

.nav-item-active {
  background: linear-gradient(90deg, rgba(74, 144, 226, 0.15), rgba(155, 89, 182, 0.15));
  box-shadow: 0 4px 12px rgba(74, 144, 226, 0.2);
  border-left: 4px solid #4A90E2;
}

.nav-icon-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.nav-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

.nav-item:hover .nav-icon {
  transform: scale(1.1) rotate(5deg);
}

.nav-text {
  font-weight: 600;
  font-size: 0.875rem;
  white-space: nowrap;
  transition: color 0.3s ease;
}

.nav-item:hover .nav-text {
  color: #4A90E2;
}

.nav-badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-weight: 600;
}

.nav-divider {
  height: 1px;
  background: linear-gradient(90deg, transparent, rgba(74, 144, 226, 0.3), transparent);
  margin: 1rem 0;
}

/* Logout Button */
.logout-btn {
  background: linear-gradient(90deg, rgba(239, 68, 68, 0.05), rgba(239, 68, 68, 0.1));
}

.logout-btn:hover {
  background: linear-gradient(90deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.2));
}

/* Sidebar Footer */
.sidebar-footer {
  padding: 1.5rem;
  border-top: 1px solid rgba(226, 232, 240, 0.6);
  background: linear-gradient(to right, rgba(74, 144, 226, 0.05), rgba(155, 89, 182, 0.05));
}

.footer-content {
  text-align: center;
}

.footer-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #4A90E2;
  margin-bottom: 0.5rem;
}

.color-dots {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.color-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.footer-version {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Collapse Toggle */
.collapse-toggle {
  position: absolute;
  top: 1.5rem;
  right: 8px;
  background: white;
  border: 1px solid #e2e8f0;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  z-index: 1001;
}

.collapse-toggle:hover {
  background: #4A90E2;
  color: white;
  transform: scale(1.1);
}

/* Custom Scrollbar */
.sidebar::-webkit-scrollbar {
  width: 6px;
}

.sidebar::-webkit-scrollbar-track {
  background: rgba(226, 232, 240, 0.5);
  border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #4A90E2, #9B59B6);
  border-radius: 10px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
}

/* Mobile styles */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    width: 280px !important;
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
    padding: 0.75rem;
  }
  
  .sidebar.collapsed .nav-icon {
    width: 40px;
    height: 40px;
    margin: 0;
  }
  
  .sidebar.collapsed .sidebar-footer {
    padding: 1rem 0.5rem;
  }
}
</style>