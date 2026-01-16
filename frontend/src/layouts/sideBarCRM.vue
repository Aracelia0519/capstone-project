<template>
  <div class="sidebar" :class="{ collapsed: isCollapsed, 'mobile-visible': mobileVisible }">
    <!-- Logout Confirmation Modal (similar to admin) -->
    <transition name="fade">
      <div 
        v-if="showLogoutModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
        @click.self="cancelLogout"
      >
        <div class="relative w-full max-w-md">
          <div class="absolute -inset-4 bg-gradient-to-r from-indigo-500/20 via-purple-500/20 to-pink-500/20 rounded-3xl blur-2xl animate-pulse"></div>
          
          <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
            <div class="relative p-6 border-b border-gray-700/50">
              <div class="absolute -top-6 left-1/2 transform -translate-x-1/2 w-16 h-8">
                <div class="w-4 h-4 bg-gradient-to-b from-indigo-500 to-purple-500 rounded-full mx-auto animate-drip"></div>
                <div class="w-2 h-4 bg-gradient-to-b from-indigo-400/50 to-transparent rounded-full mx-auto animate-drip-delay"></div>
              </div>
              
              <div class="flex items-center justify-center mb-4">
                <div class="relative">
                  <div class="w-20 h-20 rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 flex items-center justify-center shadow-2xl animate-pulse-slow">
                    <svg class="w-10 h-10 text-white animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                  </div>
                  <div class="absolute inset-0 border-4 border-indigo-500/30 rounded-full animate-ping-slow"></div>
                </div>
              </div>
              
              <h3 class="text-2xl font-bold text-center bg-gradient-to-r from-indigo-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-2">
                Logout Confirmation
              </h3>
              <p class="text-gray-300 text-center">
                Are you sure you want to logout?
              </p>
            </div>

            <div class="p-6">
              <div class="mb-6 p-4 rounded-xl bg-gradient-to-r from-indigo-500/10 to-purple-500/10 border border-indigo-500/20">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0 p-2 rounded-lg bg-gradient-to-r from-indigo-500/20 to-purple-500/20">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                  </div>
                  <div>
                    <h4 class="text-sm font-semibold text-gray-200 mb-1">CRM Session Ending</h4>
                    <p class="text-xs text-gray-400">
                      You'll be redirected to the login page. Your current CRM session will be securely terminated.
                    </p>
                  </div>
                </div>
              </div>

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
                  class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl hover:from-indigo-600 hover:to-purple-600 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group relative overflow-hidden"
                >
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

    <!-- Toast Notifications -->
    <transition name="slide-fade">
      <div 
        v-if="showSuccessToast"
        class="fixed top-6 right-6 z-[10000] w-96"
      >
        <div class="bg-gradient-to-r from-green-500 to-emerald-500 rounded-xl shadow-2xl p-4 text-white">
          <div class="flex items-center">
            <div class="flex-shrink-0 p-2 rounded-lg bg-white/20">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3">
              <h4 class="font-semibold">Successfully Logged Out!</h4>
              <p class="text-sm opacity-90">Redirecting to login page...</p>
            </div>
          </div>
          <div class="mt-2 h-1 bg-white/30 rounded-full overflow-hidden">
            <div class="h-full bg-white animate-progress"></div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Logo Section -->
    <div class="logo-section" @click="logoClicked">
      <div class="logo">
        <div class="relationship-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <h2 v-if="!isCollapsed">CRM Dashboard</h2>
      </div>
      <button class="toggle-btn" @click.stop="toggleCollapse">
        <span v-if="!isCollapsed">«</span>
        <span v-else>»</span>
      </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="nav-menu">
      <div class="nav-section">
        <div class="section-label" v-if="!isCollapsed">CUSTOMER RELATIONSHIP</div>
        <ul>
          <li 
            v-for="item in navItems" 
            :key="item.id"
            :class="{ active: activeItem === item.id }"
            @click="setActiveItem(item.id)"
          >
            <router-link :to="item.route" class="nav-link" @click="handleNavClick">
              <span class="nav-icon">
                <svg v-if="item.id === 'dashboard'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <svg v-if="item.id === 'clients'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <svg v-if="item.id === 'distributors'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <svg v-if="item.id === 'service-providers'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <svg v-if="item.id === 'interactions'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <svg v-if="item.id === 'tasks'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <svg v-if="item.id === 'reports'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </span>
              <span class="nav-text" v-if="!isCollapsed">{{ item.text }}</span>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>

    <!-- User Profile -->
    <div class="user-profile" v-if="!isCollapsed">
      <div class="user-avatar">
        <div class="avatar">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <div class="user-info">
          <strong>{{ userName || 'CRM Manager' }}</strong>
          <small>Relationship Manager</small>
        </div>
      </div>
      <button class="logout-btn" @click="showLogoutModal = true">
        <span class="logout-icon">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
        </span>
        <span class="logout-text">Logout</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const emit = defineEmits(['toggle'])

const props = defineProps({
  mobileVisible: {
    type: Boolean,
    default: false
  }
})

// State
const isCollapsed = ref(false)
const activeItem = ref('dashboard')
const userName = ref('John Doe')
const showLogoutModal = ref(false)
const showSuccessToast = ref(false)
const showErrorToast = ref(false)
const errorMessage = ref('')
const isLoggingOut = ref(false)

// Navigation Items with descriptions matching your requirements
const navItems = ref([
  { 
    id: 'dashboard', 
    text: 'Dashboard', 
    route: '/crm/CRMDashboard',
    description: 'Quick summary of CRM activity with total clients, distributors, service providers, recent interactions, and pending follow-ups'
  },
  { 
    id: 'clients', 
    text: 'Clients', 
    route: '/crm/CRMClients',
    badge: '24',
    description: 'Manage client accounts, track their activities, purchase history, and interactions'
  },
  { 
    id: 'distributors', 
    text: 'Distributors', 
    route: '/crm/CRMDistributors',
    badge: '8',
    description: 'Manage distributor accounts, monitor performance, and track transaction history'
  },
  { 
    id: 'service-providers', 
    text: 'Service Providers', 
    route: '/crm/CRMServiceProviders',
    badge: '15',
    description: 'Manage service providers, their assignments, credentials, and performance tracking'
  },
  { 
    id: 'interactions', 
    text: 'Interactions', 
    route: '/crm/CRMInteractions',
    badge: '48',
    description: 'Log and track all communications with clients, distributors, and service providers'
  },
  { 
    id: 'tasks', 
    text: 'Follow-ups / Tasks', 
    route: '/crm/CRMFollowUps',
    badge: '12',
    description: 'Track pending actions, assign tasks, set deadlines, and monitor completion status'
  },
  { 
    id: 'reports', 
    text: 'Reports', 
    route: '/crm/CRMReports',
    description: 'Generate CRM reports for client acquisition, interaction history, and task analysis'
  }
])

// Methods
const logoClicked = () => {
  isCollapsed.value = !isCollapsed.value
}

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value
}

const setActiveItem = (itemId) => {
  activeItem.value = itemId
}

const cancelLogout = () => {
  showLogoutModal.value = false
}

const confirmLogout = async () => {
  try {
    isLoggingOut.value = true
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // Clear local storage
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
    localStorage.removeItem('user_role')
    localStorage.removeItem('user_name')
    sessionStorage.clear()
    
    // Show success toast
    showLogoutModal.value = false
    showSuccessToast.value = true
    
    // Redirect to login
    setTimeout(() => {
      router.push('/Landing/logIn')
    }, 2000)
    
  } catch (error) {
    console.error('Logout error:', error)
    errorMessage.value = 'Logout failed. Please try again.'
    showLogoutModal.value = false
    showErrorToast.value = true
  } finally {
    isLoggingOut.value = false
  }
}

const handleNavClick = () => {
  // Close sidebar on mobile when a nav item is clicked
  if (props.mobileVisible) {
    emit('toggle')
  }
}

// Load user data on mount
onMounted(() => {
  const userData = localStorage.getItem('user_data')
  if (userData) {
    try {
      const user = JSON.parse(userData)
      userName.value = user.name || `${user.first_name} ${user.last_name}` || 'CRM Manager'
    } catch (e) {
      console.error('Failed to parse user data:', e)
    }
  }
})
</script>

<style scoped>
/* Sidebar Base Styles */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 280px;
  height: 100vh;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  color: #cbd5e1;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 0 40px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.sidebar.collapsed {
  width: 80px;
}

/* Mobile Visibility */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    width: 280px;
  }
  
  .sidebar.mobile-visible {
    transform: translateX(0);
    box-shadow: 0 0 60px rgba(0, 0, 0, 0.5);
  }
}

/* Logo Section */
.logo-section {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  position: relative;
  background: rgba(15, 23, 42, 0.8);
  cursor: pointer;
  transition: all 0.3s ease;
}

.logo-section:hover {
  background: rgba(30, 41, 59, 0.8);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.relationship-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.logo h2 {
  font-size: 1.25rem;
  font-weight: 700;
  background: linear-gradient(90deg, #667eea, #a78bfa);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.toggle-btn {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #cbd5e1;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  transition: all 0.2s ease;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  color: white;
}

/* Navigation Menu */
.nav-menu {
  flex: 1;
  overflow-y: auto;
  padding: 20px 0;
}

.nav-section {
  margin-bottom: 24px;
}

.section-label {
  padding: 0 20px 8px;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #64748b;
  font-weight: 600;
}

.nav-menu ul {
  list-style: none;
  padding: 0;
}

.nav-menu li {
  margin: 2px 0;
  position: relative;
}

.nav-menu li.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(180deg, #667eea, #764ba2);
  border-radius: 0 4px 4px 0;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: #cbd5e1;
  text-decoration: none;
  transition: all 0.2s ease;
  position: relative;
  overflow: hidden;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.nav-link.active {
  background: rgba(102, 126, 234, 0.1);
  color: white;
}

.nav-icon {
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-right: 12px;
  color: #94a3b8;
}

.nav-link.active .nav-icon {
  color: #a78bfa;
}

.nav-text {
  flex: 1;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.badge {
  background: linear-gradient(135deg, #f97316, #ef4444);
  color: white;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 10px;
  margin-left: 8px;
  min-width: 20px;
  text-align: center;
}

/* User Profile */
.user-profile {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(15, 23, 42, 0.8);
}

.user-avatar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.avatar {
  width: 44px;
  height: 44px;
  background: linear-gradient(135deg, #a78bfa, #6366f1);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-info strong {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-info small {
  font-size: 0.75rem;
  color: #94a3b8;
}

.logout-btn {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.logout-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

/* Collapsed State */
.sidebar.collapsed .nav-text,
.sidebar.collapsed .section-label,
.sidebar.collapsed .user-info,
.sidebar.collapsed .logout-text,
.sidebar.collapsed .badge {
  display: none;
}

.sidebar.collapsed .logo h2 {
  display: none;
}

.sidebar.collapsed .nav-link {
  justify-content: center;
  padding: 12px;
}

.sidebar.collapsed .nav-icon {
  margin-right: 0;
}

.sidebar.collapsed .user-avatar {
  justify-content: center;
}

.sidebar.collapsed .logout-btn {
  padding: 10px;
}

.sidebar.collapsed .logout-text {
  display: none;
}

/* Animations */
@keyframes drip {
  0% { transform: translateY(0) scale(1); opacity: 1; }
  100% { transform: translateY(20px) scale(0); opacity: 0; }
}

@keyframes drip-delay {
  0% { transform: translateY(0) scale(1); opacity: 1; }
  100% { transform: translateY(20px) scale(0); opacity: 0; }
}

@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

@keyframes ping-slow {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.5); opacity: 0; }
}

@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes shine {
  0% { transform: translateX(-100px) skewX(-15deg); }
  100% { transform: translateX(300px) skewX(-15deg); }
}

@keyframes progress {
  0% { width: 100%; }
  100% { width: 0%; }
}

.animate-drip {
  animation: drip 1s ease-in-out infinite;
}

.animate-drip-delay {
  animation: drip-delay 1s ease-in-out 0.5s infinite;
}

.animate-pulse-slow {
  animation: pulse-slow 2s ease-in-out infinite;
}

.animate-ping-slow {
  animation: ping-slow 1.5s ease-in-out infinite;
}

.animate-spin-slow {
  animation: spin-slow 8s linear infinite;
}

.animate-shine {
  animation: shine 2s ease-in-out infinite;
}

.animate-progress {
  animation: progress 2s linear forwards;
}

/* Toast Animations */
.slide-fade-enter-active {
  transition: all 0.3s ease;
}

.slide-fade-leave-active {
  transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}

.slide-fade-enter-from {
  transform: translateX(100px);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(100px);
  opacity: 0;
}

/* Scrollbar Styling */
.nav-menu::-webkit-scrollbar {
  width: 4px;
}

.nav-menu::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.nav-menu::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
}

.nav-menu::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.3);
}
</style>