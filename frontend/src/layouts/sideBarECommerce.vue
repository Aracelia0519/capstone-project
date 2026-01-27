<!-- sideBarECommerce.vue -->
<template>
  <aside 
    :class="['sidebar-ecommerce', { 
      'mobile-visible': mobileVisible,
      'collapsed': isCollapsed && !isMobile
    }]"
    :style="isCollapsed && !isMobile ? 'width: 80px' : 'width: 280px'"
  >
    <!-- Close button for mobile -->
    <button 
      v-if="isMobile"
      @click="closeSidebar"
      class="absolute top-6 right-6 text-white hover:text-gray-200 z-50 lg:hidden p-2 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 shadow-lg"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <!-- Sidebar Header with User Info -->
    <div class="sidebar-header">
      <div class="flex items-center space-x-4">
        <!-- User Avatar -->
        <div class="relative">
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-2xl animate-pulse-slow">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
          <!-- Online indicator -->
          <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-2 border-white"></div>
        </div>
        
        <div v-if="!isCollapsed || isMobile" class="sidebar-title">
          <h2 class="text-xl font-bold bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
            E-Commerce Hub
          </h2>
          <p class="text-xs text-gray-200 flex items-center mt-0.5">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse"></span>
            {{ userRole || 'Store Dashboard' }}
          </p>
          <p class="text-xs text-gray-400 mt-1">
            Welcome, {{ userName || 'User' }}
          </p>
        </div>
      </div>
      
      <!-- Quick Stats Widget -->
      <div v-if="!isCollapsed || isMobile" class="sidebar-stats">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value text-indigo-300">{{ stats.totalOrders }}</div>
            <div class="stat-label">Orders</div>
          </div>
          <div class="stat-item">
            <div class="stat-value text-purple-300">{{ stats.products }}</div>
            <div class="stat-label">Products</div>
          </div>
          <div class="stat-item">
            <div class="stat-value text-pink-300">{{ stats.revenue }}</div>
            <div class="stat-label">Revenue</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Items -->
    <nav class="sidebar-nav">
      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <div class="section-icon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
          </div>
          Overview
        </h3>
        <ul class="nav-list">
          <!-- Dashboard -->
          <li>
            <router-link 
              :to="{ name: 'ECDashboard' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-indigo-400 to-purple-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Dashboard</span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <div class="section-icon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          Catalog
        </h3>
        <ul class="nav-list">
          <!-- Products -->
          <li>
            <router-link 
              :to="{ name: 'ECProducts' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-blue-400 to-cyan-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Products</span>
              </div>
            </router-link>
          </li>

          <!-- Categories -->
          <li>
            <router-link 
              :to="{ name: 'ECCategories' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-emerald-400 to-green-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Categories</span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <div class="section-icon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          Sales
        </h3>
        <ul class="nav-list">
          <!-- Orders -->
          <li>
            <router-link 
              :to="{ name: 'ECOrders' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-amber-400 to-yellow-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Orders</span>
              </div>
            </router-link>
          </li>

          <!-- Payments -->
          <li>
            <router-link 
              :to="{ name: 'ECPayment' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-green-400 to-teal-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Payments</span>
              </div>
            </router-link>
          </li>

          <!-- Delivery / Logistics -->
          <li>
            <router-link 
              :to="{ name: 'ECDelivery' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-cyan-400 to-blue-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Delivery / Logistics</span>
              </div>
            </router-link>
          </li>

          <!-- Returns & Refunds -->
          <li>
            <router-link 
              :to="{ name: 'ECReturns' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-red-400 to-pink-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Returns & Refunds</span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <div class="section-icon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
          </div>
          Customer Experience
        </h3>
        <ul class="nav-list">
          <!-- Reviews & Ratings -->
          <li>
            <router-link 
              :to="{ name: 'ECReviews' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-violet-400 to-purple-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Reviews & Ratings</span>
              </div>
            </router-link>
          </li>

          <!-- Promotions (Optional) -->
          <li>
            <router-link 
              :to="{ name: 'ECPromotions' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-orange-400 to-red-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Promotions</span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section">
        <h3 v-if="!isCollapsed || isMobile" class="section-title">
          <div class="section-icon">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          Analytics
        </h3>
        <ul class="nav-list">
          <!-- Reports -->
          <li>
            <router-link 
              :to="{ name: 'ECreports' }"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-sky-400 to-cyan-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Reports</span>
              </div>
            </router-link>
          </li>
        </ul>
      </div>

      <!-- Account Section -->
      <div class="nav-section account-section">
        <ul class="nav-list">
          <!-- Settings -->
          <li>
            <router-link 
              to="/settings"
              @click="handleNavigation"
              class="nav-item group"
              active-class="nav-item-active"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-gray-500 to-gray-400">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text">Settings</span>
              </div>
            </router-link>
          </li>

          <!-- Logout -->
          <li>
            <button 
              @click="$emit('logout-click')"
              class="nav-item group logout-btn w-full hover:bg-gradient-to-r hover:from-red-500/10 hover:to-pink-500/10"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-red-500 to-pink-400 group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text group-hover:text-red-300 transition-colors duration-300">
                  Logout
                </span>
              </div>
              <div v-if="!isCollapsed || isMobile" class="nav-badge animate-pulse">🔐</div>
            </button>
          </li>
        </ul>
      </div>
    </nav>

    <!-- Sidebar Footer -->
    <div v-if="!isCollapsed || isMobile" class="sidebar-footer">
      <div class="footer-content">
        <p class="footer-title">CaviteGo Paint Store</p>
        <div class="color-dots">
          <div class="color-dot bg-indigo-400"></div>
          <div class="color-dot bg-purple-400"></div>
          <div class="color-dot bg-pink-400"></div>
          <div class="color-dot bg-blue-400"></div>
        </div>
        <p class="footer-version">E-Commerce v1.0</p>
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
import { ref, onMounted, computed } from 'vue'

export default {
  name: 'SideBarECommerce',
  props: {
    mobileVisible: {
      type: Boolean,
      default: false
    },
    userData: {
      type: Object,
      default: null
    },
    dashboardData: {
      type: Object,
      default: null
    }
  },
  setup(props, { emit }) {
    const isCollapsed = ref(false)
    const isMobile = ref(false)
    
    // User info
    const userName = computed(() => {
      if (props.userData) {
        return props.userData.name || 
               `${props.userData.first_name || ''} ${props.userData.last_name || ''}`.trim() || 
               'Store User'
      }
      return 'Store User'
    })
    
    const userRole = computed(() => {
      if (props.userData) {
        const roleMap = {
          admin: 'Administrator',
          distributor: 'Distributor',
          service_provider: 'Service Provider',
          client: 'Client',
          customer: 'Customer'
        }
        return roleMap[props.userData.role] || 'Store User'
      }
      return 'Store Dashboard'
    })
    
    // Stats with fallback to dashboard data
    const stats = computed(() => {
      if (props.dashboardData) {
        return {
          totalOrders: props.dashboardData.total_orders || 156,
          products: props.dashboardData.total_products || 89,
          revenue: props.dashboardData.revenue || '$12.4K'
        }
      }
      return {
        totalOrders: 156,
        products: 89,
        revenue: '$12.4K'
      }
    })

    const closeSidebar = () => {
      emit('toggle')
    }
    
    const toggleCollapse = () => {
      isCollapsed.value = !isCollapsed.value
      emit('collapsed', isCollapsed.value)
    }
    
    const handleNavigation = () => {
      emit('link-click')
      if (isMobile.value) {
        closeSidebar()
      }
    }
    
    const checkMobile = () => {
      isMobile.value = window.innerWidth <= 768
    }

    onMounted(() => {
      checkMobile()
      window.addEventListener('resize', checkMobile)
    })

    return {
      isCollapsed,
      isMobile,
      stats,
      userName,
      userRole,
      closeSidebar,
      toggleCollapse,
      handleNavigation,
      checkMobile
    }
  },
  
  beforeUnmount() {
    window.removeEventListener('resize', this.checkMobile)
  },
  
  emits: ['toggle', 'link-click', 'collapsed', 'logout-click']
}
</script>

<style scoped>
.sidebar-ecommerce {
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
  backdrop-filter: blur(20px);
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 1000;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

/* Mobile visibility */
.sidebar-ecommerce.mobile-visible {
  transform: translateX(0);
}

@media (max-width: 768px) {
  .sidebar-ecommerce:not(.mobile-visible) {
    transform: translateX(-100%);
  }
}

/* Collapsed state */
.sidebar-ecommerce.collapsed {
  width: 80px;
}

/* Sidebar Header */
.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: linear-gradient(180deg, rgba(30, 27, 75, 0.5) 0%, transparent 100%);
}

.sidebar-title h2 {
  background-clip: text;
  -webkit-background-clip: text;
  font-weight: 700;
  font-size: 1.25rem;
  line-height: 1.2;
}

.sidebar-title p {
  font-size: 0.75rem;
  color: #cbd5e1;
  margin-top: 0.125rem;
}

/* Stats Widget */
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
  background: rgba(255, 255, 255, 0.05);
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.stat-value {
  font-size: 1.125rem;
  font-weight: 700;
  line-height: 1;
}

.stat-label {
  font-size: 0.625rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

/* Navigation */
.sidebar-nav {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.nav-section {
  margin-bottom: 0.5rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.5rem 0.75rem;
  margin-bottom: 0.5rem;
}

.section-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1rem;
  height: 1rem;
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
  padding: 0.75rem;
  border-radius: 0.75rem;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s ease;
  cursor: pointer;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateX(2px);
}

.nav-item-active {
  background: linear-gradient(90deg, rgba(79, 70, 229, 0.2) 0%, transparent 100%);
  color: white;
  border-left: 3px solid #4f46e5;
}

.nav-icon-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.nav-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.nav-item:hover .nav-icon {
  transform: scale(1.1);
}

.nav-text {
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  transition: color 0.2s ease;
}

.nav-badge {
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  background: rgba(255, 255, 255, 0.1);
  color: #94a3b8;
  font-weight: 600;
  min-width: 2rem;
  text-align: center;
}

/* Account Section */
.account-section {
  margin-top: auto;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 1rem;
}

.logout-btn {
  background: rgba(239, 68, 68, 0.1);
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
}

/* Sidebar Footer */
.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: linear-gradient(180deg, transparent 0%, rgba(30, 27, 75, 0.5) 100%);
}

.footer-content {
  text-align: center;
}

.footer-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
  margin-bottom: 0.5rem;
}

.color-dots {
  display: flex;
  justify-content: center;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
}

.color-dot {
  width: 0.5rem;
  height: 0.5rem;
  border-radius: 50%;
}

.footer-version {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Collapse Toggle */
.collapse-toggle {
  position: absolute;
  right: 0.75rem;
  top: 1.5rem;
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 10;
}

.collapse-toggle:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: scale(1.1);
}

/* Animations */
@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.animate-pulse-slow {
  animation: pulse-slow 3s ease-in-out infinite;
}

/* Scrollbar Styling */
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 10px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 10px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .sidebar-ecommerce {
    width: 280px;
  }
  
  .sidebar-ecommerce.mobile-visible {
    box-shadow: 20px 0 40px rgba(0, 0, 0, 0.3);
  }
}
</style>