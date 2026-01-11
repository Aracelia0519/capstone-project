<template>
  <aside 
    :class="['sidebar', { 
      'mobile-visible': mobileVisible,
      'collapsed': isCollapsed && !isMobile
    }]"
    :style="isCollapsed && !isMobile ? 'width: 80px' : 'width: 280px'"
  >
    <!-- Enhanced Logout Confirmation Modal -->
    <transition name="fade">
      <div 
        v-if="showLogoutModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="cancelLogout"
      >
        <div class="relative w-full max-w-md">
          <!-- Animated background effect with Service Provider colors (blue/purple/pink) -->
          <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-2xl animate-pulse"></div>
          
          <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Modal header with animated paint drip -->
            <div class="relative p-6 border-b border-gray-700/50">
              <!-- Paint drip animation with Service Provider colors -->
              <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-16 h-8">
                <div class="w-4 h-4 bg-gradient-to-b from-blue-500 to-purple-500 rounded-full mx-auto animate-drip"></div>
                <div class="w-2 h-4 bg-gradient-to-b from-blue-400/50 to-transparent rounded-full mx-auto animate-drip-delay"></div>
              </div>
              
              <div class="flex items-center justify-center mb-4">
                <div class="relative">
                  <!-- Animated logout icon with Service Provider gradient -->
                  <div class="w-20 h-20 rounded-full bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-2xl animate-pulse-slow">
                    <svg class="w-10 h-10 text-white animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                  </div>
                  <!-- Pulsing ring with blue/purple theme -->
                  <div class="absolute inset-0 border-4 border-blue-500/30 rounded-full animate-ping-slow"></div>
                </div>
              </div>
              
              <h3 class="text-2xl font-bold text-center bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-2">
                Logout Confirmation
              </h3>
              <p class="text-gray-300 text-center">
                Are you sure you want to logout?
              </p>
            </div>

            <!-- Modal body -->
            <div class="p-6">
              <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 p-2 rounded-lg bg-gradient-to-r from-blue-500/20 to-purple-500/20">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-gray-200 mb-1">Service Provider Session Ending</h4>
                    <p class="text-xs text-gray-400">
                      You'll be redirected to the login page. Your current service provider session will be securely terminated.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Action buttons -->
              <div class="flex flex-col sm:flex-row gap-3">
                <button
                  @click="cancelLogout"
                  :disabled="isLoggingOut"
                  class="flex-1 py-3 px-6 rounded-xl border border-gray-600 bg-gray-800/50 text-gray-300 font-semibold hover:bg-gray-700/50 hover:text-white transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Cancel</span>
                  </div>
                </button>
                
                <button
                  @click="confirmLogout"
                  :disabled="isLoggingOut"
                  class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl hover:from-blue-600 hover:to-purple-600 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group relative overflow-hidden"
                >
                  <!-- Animated shine effect -->
                  <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute top-0 left-0 w-12 h-full bg-white/20 skew-x-12 animate-shine"></div>
                  </div>
                  
                  <div class="relative flex items-center justify-center space-x-2">
                    <svg v-if="isLoggingOut" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <svg v-else class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>{{ isLoggingOut ? 'Logging out...' : 'Yes, Logout' }}</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Modal footer -->
            <div class="px-6 py-4 border-t border-gray-700/50 bg-gray-900/50">
              <div class="flex items-center justify-center space-x-2 text-xs text-gray-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <span>Secure logout powered by Laravel Sanctum</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Success Toast Notification -->
    <transition name="slide-fade">
      <div 
        v-if="showSuccessToast"
        class="toast-notification success"
      >
        <div class="toast-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="toast-content">
          <h4 class="toast-title">Successfully Logged Out!</h4>
          <p class="toast-message">Redirecting to login page...</p>
        </div>
        <div class="toast-progress">
          <div class="toast-progress-bar"></div>
        </div>
      </div>
    </transition>

    <!-- Error Toast Notification -->
    <transition name="slide-fade">
      <div 
        v-if="showErrorToast"
        class="toast-notification error"
      >
        <div class="toast-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <div class="toast-content">
          <h4 class="toast-title">Logout Failed</h4>
          <p class="toast-message">{{ errorMessage }}</p>
        </div>
        <button @click="showErrorToast = false" class="toast-close">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </transition>

    <!-- Close button for mobile -->
    <button 
      v-if="isMobile"
      @click="closeSidebar"
      class="absolute top-6 right-6 text-white hover:text-gray-200 z-50 lg:hidden p-2 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 shadow-lg"
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
            {{ userName || 'Paint Pro' }}
          </h2>
          <p class="text-xs text-gray-300 flex items-center mt-0.5">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5"></span>
            Service Provider
          </p>
        </div>
      </div>
      
      <!-- Quick Stats Widget -->
      <div v-if="!isCollapsed || isMobile" class="sidebar-stats">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-value text-blue-300">{{ stats.activeJobs || 12 }}</div>
            <div class="stat-label">Active Jobs</div>
          </div>
          <div class="stat-item">
            <div class="stat-value text-purple-300">{{ stats.clients || 24 }}</div>
            <div class="stat-label">Clients</div>
          </div>
          <div class="stat-item">
            <div class="stat-value text-pink-300">{{ stats.completedJobs || 45 }}</div>
            <div class="stat-label">Completed</div>
          </div>
        </div>
      </div>
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
              <div v-if="!isCollapsed || isMobile" class="nav-badge bg-blue-500/20 text-blue-300">New</div>
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
              <div v-if="!isCollapsed || isMobile" class="nav-badge bg-green-500/20 text-green-300">{{ stats.clients || 24 }}</div>
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
              <div v-if="!isCollapsed || isMobile" class="nav-badge bg-orange-500/20 text-orange-300">{{ stats.activeJobs || 12 }}</div>
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
              <div v-if="!isCollapsed || isMobile" class="nav-badge bg-gradient-to-r from-purple-500 to-pink-500 text-white">UNITY</div>
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
              <div v-if="!isCollapsed || isMobile" class="nav-badge bg-blue-500/20 text-blue-300">Read Only</div>
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
          <!-- Profile -->
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

          <!-- Logout -->
          <li>
            <button 
              @click="showLogoutModal = true"
              class="nav-item group logout-btn w-full hover:bg-gradient-to-r hover:from-blue-500/10 hover:to-purple-500/10"
            >
              <div class="nav-icon-wrapper">
                <div class="nav-icon bg-gradient-to-r from-blue-500 to-purple-400 group-hover:scale-110 transition-transform duration-300">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                  </svg>
                </div>
                <span v-if="!isCollapsed || isMobile" class="nav-text group-hover:text-blue-300 transition-colors duration-300">
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
        <p class="footer-title">CaviteGo Paint</p>
        <div class="color-dots">
          <div class="color-dot bg-blue-500"></div>
          <div class="color-dot bg-purple-500"></div>
          <div class="color-dot bg-pink-500"></div>
          <div class="color-dot bg-indigo-500"></div>
        </div>
        <p class="footer-version">Service Provider v2.0</p>
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
import api from '../utils/axios'
import { useRouter } from 'vue-router'
import { ref, onMounted } from 'vue'

export default {
  name: 'SideBarServiceProvider',
  props: {
    mobileVisible: {
      type: Boolean,
      default: false
    }
  },
  setup(props, { emit }) {
    const router = useRouter()
    const isCollapsed = ref(false)
    const isMobile = ref(false)
    const isLoggingOut = ref(false)
    const userName = ref('')
    const showLogoutModal = ref(false)
    const showSuccessToast = ref(false)
    const showErrorToast = ref(false)
    const errorMessage = ref('')
    
    const stats = {
      activeJobs: 12,
      clients: 24,
      completedJobs: 45
    }

    const loadUserData = () => {
      const userData = localStorage.getItem('user_data')
      if (userData) {
        try {
          const user = JSON.parse(userData)
          userName.value = user.name || `${user.first_name} ${user.last_name}` || 'Paint Pro'
        } catch (e) {
          console.error('Failed to parse user data:', e)
        }
      }
    }

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
    
    const cancelLogout = () => {
      showLogoutModal.value = false
    }
    
    const confirmLogout = async () => {
      try {
        isLoggingOut.value = true
        
        // Call the backend logout endpoint
        const response = await api.post('/auth/logout')
        
        if (response.data.status === 'success') {
          console.log('Logout successful')
          
          // Clear all local storage items
          localStorage.removeItem('auth_token')
          localStorage.removeItem('user_data')
          localStorage.removeItem('user_role')
          localStorage.removeItem('user_name')
          localStorage.removeItem('remember_me')
          
          // Clear any session storage
          sessionStorage.clear()
          
          // Clear axios authorization header
          delete api.defaults.headers.common['Authorization']
          
          // Hide modal and show success toast
          showLogoutModal.value = false
          showSuccessToast.value = true
          
          // Redirect to login page after 2 seconds
          setTimeout(() => {
            router.push('/Landing/logIn')
          }, 2000)
          
        } else {
          console.error('Logout failed:', response.data.message)
          errorMessage.value = response.data.message || 'Logout failed. Please try again.'
          showLogoutModal.value = false
          showErrorToast.value = true
        }
        
      } catch (error) {
        console.error('Logout error:', error)
        
        // Even if API call fails, clear local data
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user_data')
        localStorage.removeItem('user_role')
        localStorage.removeItem('user_name')
        localStorage.removeItem('remember_me')
        sessionStorage.clear()
        delete api.defaults.headers.common['Authorization']
        
        // Show appropriate error message
        if (error.response) {
          console.error('Response error:', error.response.data)
          // Check if the error is actually success
          if (error.response.data.status === 'success') {
            showSuccessToast.value = true
            setTimeout(() => {
              router.push('/Landing/logIn')
            }, 2000)
            return
          }
          errorMessage.value = error.response.data.message || 'Server error'
        } else if (error.request) {
          errorMessage.value = 'No response from server'
        } else {
          errorMessage.value = error.message
        }
        
        showLogoutModal.value = false
        showErrorToast.value = true
        
        // Still redirect to login after showing error
        setTimeout(() => {
          router.push('/Landing/logIn')
        }, 3000)
        
      } finally {
        isLoggingOut.value = false
      }
    }
    
    const checkMobile = () => {
      isMobile.value = window.innerWidth <= 768
    }

    onMounted(() => {
      checkMobile()
      window.addEventListener('resize', checkMobile)
      loadUserData()
    })

    return {
      isCollapsed,
      isMobile,
      isLoggingOut,
      userName,
      stats,
      showLogoutModal,
      showSuccessToast,
      showErrorToast,
      errorMessage,
      closeSidebar,
      toggleCollapse,
      handleNavigation,
      cancelLogout,
      confirmLogout,
      checkMobile
    }
  },
  
  beforeUnmount() {
    window.removeEventListener('resize', this.checkMobile)
  },
  
  emits: ['toggle', 'link-click', 'collapsed']
}
</script>

<style scoped>
  @import "../layouts/styles/serviceProvider.css";
</style>