<template>
  <div class="sidebar" :class="{ collapsed: isCollapsed, 'mobile-visible': mobileVisible }">
    <!-- Logo Section -->
    <div class="logo-section" @click="logoClicked">
      <div class="logo">
        <div class="hr-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
          </svg>
        </div>
        <h2 v-if="!isCollapsed" class="text-lg font-bold">HR Module</h2>
      </div>
      <button class="toggle-btn" @click.stop="toggleCollapse">
        <span v-if="!isCollapsed">«</span>
        <span v-else>»</span>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="nav-menu">
      <div class="nav-section">
        <div class="section-label" v-if="!isCollapsed">HR MANAGEMENT</div>
        <ul>
          <li 
            v-for="item in hrNavItems" 
            :key="item.id"
            :class="{ active: activeItem === item.id }"
            @click="setActiveItem(item.id)"
          >
            <router-link :to="item.route" class="nav-link" @click="handleNavClick">
              <span class="nav-icon">
                <!-- Dashboard Icon -->
                <svg v-if="item.id === 'dashboard'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                
                <!-- Employee List Icon -->
                <svg v-if="item.id === 'employees'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                
                <!-- Positions & Roles Icon -->
                <svg v-if="item.id === 'positions'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                
                <!-- Departments Icon -->
                <svg v-if="item.id === 'departments'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                
                <!-- Employment Status Icon -->
                <svg v-if="item.id === 'status'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </span>
              <span class="nav-text" v-if="!isCollapsed">{{ item.text }}</span>
              <span class="badge" v-if="item.badge && !isCollapsed">{{ item.badge }}</span>
            </router-link>
          </li>
        </ul>
      </div>

      <div class="nav-section" v-if="!isCollapsed">
        <div class="section-label">TOOLS</div>
        <ul>
          <li 
            v-for="item in toolsNavItems" 
            :key="item.id"
            :class="{ active: activeItem === item.id }"
            @click="setActiveItem(item.id)"
          >
            <router-link :to="item.route" class="nav-link" @click="handleNavClick">
              <span class="nav-icon">
                <!-- Reports Icon -->
                <svg v-if="item.id === 'reports'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                
                <!-- Settings Icon -->
                <svg v-if="item.id === 'settings'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </span>
              <span class="nav-text">{{ item.text }}</span>
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
          <strong>HR Manager</strong>
          <small>Human Resources</small>
        </div>
      </div>
      <button class="logout-btn" @click="handleLogout">
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
import { ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const props = defineProps({
  mobileVisible: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['toggle'])

const isCollapsed = ref(false)
const activeItem = ref('dashboard')

// Updated navigation items to match your routes
const hrNavItems = ref([
  { id: 'dashboard', icon: 'dashboard', text: 'Dashboard', route: '/HR/HRdashboard', badge: 'Live' },
  { id: 'employees', icon: 'employees', text: 'Employee List', route: '/HR/employeesListHR', badge: '24' },
  { id: 'positions', icon: 'positions', text: 'Positions & Roles', route: '/HR/positionRolesHR' },
  { id: 'departments', icon: 'departments', text: 'Departments', route: '/HR/departmentsHR' },
  { id: 'status', icon: 'status', text: 'Employment Status', route: '/HR/employmentStatusHR' },
])

const toolsNavItems = ref([
  { id: 'reports', icon: 'reports', text: 'HR Reports', route: '/HR/reportsHR' },
  { id: 'settings', icon: 'settings', text: 'HR Settings', route: '/HR/settings' },
])

// Watch route changes to update active item
watch(() => route.path, (newPath) => {
  // Find the active item based on current route
  const allItems = [...hrNavItems.value, ...toolsNavItems.value]
  const active = allItems.find(item => item.route === newPath)
  if (active) {
    activeItem.value = active.id
  }
}, { immediate: true })

const logoClicked = () => {
  isCollapsed.value = !isCollapsed.value
}

const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value
}

const setActiveItem = (itemId) => {
  activeItem.value = itemId
}

const handleLogout = () => {
  // Simple logout - just redirect to login
  router.push('/Landing/logIn')
}

const handleNavClick = () => {
  // Close sidebar on mobile when a nav item is clicked
  if (props.mobileVisible) {
    emit('toggle')
  }
}
</script>

<style scoped>
.sidebar {
  width: 280px;
  min-height: 100vh;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  color: white;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  transition: all 0.3s ease;
  border-right: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
}

.sidebar.collapsed {
  width: 80px;
}

.logo-section {
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  position: relative;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.hr-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #3b82f6, #10b981);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.nav-menu {
  flex: 1;
  padding: 20px 0;
  overflow-y: auto;
}

.nav-section {
  margin-bottom: 24px;
}

.section-label {
  padding: 0 20px 8px;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: rgba(255, 255, 255, 0.5);
  font-weight: 600;
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

li {
  position: relative;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.3s ease;
  gap: 12px;
  position: relative;
}

.nav-link:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

li.active .nav-link {
  background: linear-gradient(90deg, rgba(59, 130, 246, 0.2) 0%, rgba(59, 130, 246, 0.1) 100%);
  color: white;
  border-left: 3px solid #3b82f6;
}

.nav-icon {
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.nav-text {
  flex: 1;
  font-size: 14px;
  font-weight: 500;
}

.badge {
  background: linear-gradient(135deg, #3b82f6, #10b981);
  color: white;
  font-size: 11px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 10px;
}

.user-profile {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.2);
}

.user-avatar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
}

.avatar {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #3b82f6, #10b981);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-info {
  flex: 1;
}

.user-info strong {
  display: block;
  font-size: 14px;
  font-weight: 600;
}

.user-info small {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.6);
}

.logout-btn {
  width: 100%;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.2);
  color: #ef4444;
  padding: 10px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.3);
}

/* Mobile styles */
@media (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
    z-index: 1000;
  }
  
  .sidebar.mobile-visible {
    transform: translateX(0);
  }
}
</style>