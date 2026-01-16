<template>
  <div class="sidebar" :class="{ collapsed: isCollapsed, 'mobile-visible': mobileVisible }">
    <!-- Logo Section -->
    <div class="logo-section" @click="logoClicked">
      <div class="logo">
        <div class="finance-icon">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h2 v-if="!isCollapsed">Finance Portal</h2>
      </div>
      <button class="toggle-btn" @click.stop="toggleCollapse">
        <span v-if="!isCollapsed">«</span>
        <span v-else>»</span>
      </button>
    </div>

    <!-- Navigation -->
    <nav class="nav-menu">
      <div class="nav-section">
        <div class="section-label" v-if="!isCollapsed">FINANCE NAVIGATION</div>
        <ul>
          <li 
            v-for="item in financeNavItems" 
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
                
                <!-- Transactions Icon -->
                <svg v-if="item.id === 'transactions'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                
                <!-- Payment Methods Icon -->
                <svg v-if="item.id === 'payment-methods'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                
                <!-- Invoices/Billing Icon -->
                <svg v-if="item.id === 'invoices'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                
                <!-- Reports Icon -->
                <svg v-if="item.id === 'reports'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </span>
              <span class="nav-text" v-if="!isCollapsed">{{ item.text }}</span>
              <span class="badge" v-if="item.badge && !isCollapsed">{{ item.badge }}</span>
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
          <strong>Finance Officer</strong>
          <small>Finance Department</small>
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isCollapsed = ref(false)
const activeItem = ref('dashboard')
const mobileVisible = ref(false)

const financeNavItems = ref([
  { id: 'dashboard', text: 'Dashboard', route: '/finance/financeDashboard', badge: 'Live' },
  { id: 'transactions', text: 'Transactions', route: '/finance/transactions', badge: '24' },
  { id: 'payment-methods', text: 'Payment Methods', route: '/finance/paymentMethods' },
  { id: 'invoices', text: 'Invoices / Billing', route: '/finance/invoices', badge: '8' },
  { id: 'reports', text: 'Reports', route: '/finance/reportFinance' },
])

const financeToolsItems = ref([
  { id: 'reconciliation', text: 'Bank Reconciliation', route: '/finance/reconciliation' },
  { id: 'tax', text: 'Tax Management', route: '/finance/tax' },
  { id: 'audit', text: 'Financial Audit', route: '/finance/audit' },
])

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
  // For UI only - just redirect to login
  router.push('/login')
}

const handleNavClick = () => {
  // Close sidebar on mobile when a nav item is clicked
  if (mobileVisible.value) {
    emit('toggle')
  }
}

// Emit for mobile toggle
const emit = defineEmits(['toggle'])
</script>

<style scoped>
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #1e3a1e 0%, #0f1f0f 100%);
  color: white;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 15px rgba(0, 0, 0, 0.3);
  border-right: 1px solid rgba(34, 197, 94, 0.2);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
}

.sidebar.collapsed {
  width: 80px;
}

.sidebar.mobile-visible {
  transform: translateX(0);
}

/* Logo Section */
.logo-section {
  padding: 24px 20px;
  border-bottom: 1px solid rgba(34, 197, 94, 0.2);
  position: relative;
  background: rgba(21, 71, 21, 0.3);
  backdrop-filter: blur(10px);
  cursor: pointer;
  transition: all 0.3s ease;
}

.logo-section:hover {
  background: rgba(21, 71, 21, 0.5);
}

.logo {
  display: flex;
  align-items: center;
  gap: 12px;
}

.finance-icon {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  flex-shrink: 0;
}

.logo h2 {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(90deg, #10b981, #a7f3d0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.toggle-btn {
  position: absolute;
  right: 15px;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #10b981;
  width: 30px;
  height: 30px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.toggle-btn:hover {
  background: rgba(16, 185, 129, 0.3);
  transform: translateY(-50%) scale(1.1);
}

/* Navigation */
.nav-menu {
  flex: 1;
  padding: 20px 0;
  overflow-y: auto;
  overflow-x: hidden;
}

.nav-section {
  margin-bottom: 24px;
}

.section-label {
  padding: 0 20px 8px;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: rgba(167, 243, 208, 0.6);
  font-weight: 600;
}

.nav-menu ul {
  list-style: none;
  padding: 0;
}

.nav-menu li {
  margin: 2px 12px;
  border-radius: 8px;
  transition: all 0.3s ease;
  position: relative;
}

.nav-menu li:hover {
  background: rgba(16, 185, 129, 0.1);
}

.nav-menu li.active {
  background: linear-gradient(90deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.1));
  border-left: 3px solid #10b981;
}

.nav-menu li.active::before {
  content: '';
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: linear-gradient(180deg, #10b981, #a7f3d0);
  border-radius: 0 8px 8px 0;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 12px 20px;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  gap: 12px;
  position: relative;
}

.nav-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
  color: #a7f3d0;
}

.nav-text {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}

.badge {
  background: linear-gradient(90deg, #10b981, #059669);
  color: white;
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 12px;
  font-weight: 600;
  min-width: 20px;
  text-align: center;
}

/* User Profile */
.user-profile {
  padding: 20px;
  border-top: 1px solid rgba(34, 197, 94, 0.2);
  background: rgba(21, 71, 21, 0.3);
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
  background: linear-gradient(135deg, #10b981, #059669);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(16, 185, 129, 0.3);
}

.user-info {
  flex: 1;
  min-width: 0;
}

.user-info strong {
  display: block;
  font-size: 0.95rem;
  font-weight: 600;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-info small {
  display: block;
  font-size: 0.8rem;
  color: rgba(167, 243, 208, 0.8);
}

.logout-btn {
  width: 100%;
  padding: 10px;
  background: linear-gradient(90deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.1));
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.logout-btn:hover {
  background: linear-gradient(90deg, rgba(239, 68, 68, 0.3), rgba(220, 38, 38, 0.2));
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

.logout-icon {
  width: 20px;
  height: 20px;
}

.logout-text {
  font-size: 0.9rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .sidebar:not(.mobile-visible) {
    transform: translateX(-100%);
  }
  
  .sidebar {
    width: 280px;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .sidebar.mobile-visible {
    transform: translateX(0);
  }
  
  .logo h2 {
    font-size: 1.3rem;
  }
}

/* Collapsed State */
.sidebar.collapsed .logo h2,
.sidebar.collapsed .section-label,
.sidebar.collapsed .nav-text,
.sidebar.collapsed .user-info,
.sidebar.collapsed .logout-text,
.sidebar.collapsed .badge {
  display: none;
}

.sidebar.collapsed .logo {
  justify-content: center;
  gap: 0;
}

.sidebar.collapsed .nav-link {
  justify-content: center;
  padding: 12px;
}

.sidebar.collapsed .user-avatar {
  justify-content: center;
  gap: 0;
}

.sidebar.collapsed .user-profile {
  padding: 15px 10px;
}

.sidebar.collapsed .logout-btn {
  padding: 10px;
  justify-content: center;
}

/* Custom scrollbar */
.nav-menu::-webkit-scrollbar {
  width: 4px;
}

.nav-menu::-webkit-scrollbar-track {
  background: rgba(21, 71, 21, 0.2);
}

.nav-menu::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #10b981, #059669);
  border-radius: 4px;
}

.nav-menu::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #34d399, #10b981);
}
</style>