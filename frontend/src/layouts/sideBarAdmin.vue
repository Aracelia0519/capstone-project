<template>
    <!-- Sidebar -->
    <div class="sidebar" :class="{ collapsed: isCollapsed, 'mobile-visible': mobileVisible }">
      <!-- Logo Section -->
      <div class="logo-section" @click="logoClicked">
        <div class="logo">
          <div class="paint-can-icon">🎨</div>
          <h2 v-if="!isCollapsed">CaviteGo Paint</h2>
        </div>
        <button class="toggle-btn" @click.stop="toggleCollapse">
          <span v-if="!isCollapsed">«</span>
          <span v-else>»</span>
        </button>
      </div>

      

      <!-- Navigation -->
      <nav class="nav-menu">
        <div class="nav-section">
          <div class="section-label" v-if="!isCollapsed">MAIN NAVIGATION</div>
          <ul>
            <li 
              v-for="item in mainNavItems" 
              :key="item.id"
              :class="{ active: activeItem === item.id }"
              @click="setActiveItem(item.id)"
            >
              <router-link :to="item.route" class="nav-link" @click="handleNavClick">
                <span class="nav-icon">{{ item.icon }}</span>
                <span class="nav-text" v-if="!isCollapsed">{{ item.text }}</span>
                <span class="badge" v-if="item.badge && !isCollapsed">{{ item.badge }}</span>
              </router-link>
            </li>
          </ul>
        </div>

        <div class="nav-section" v-if="!isCollapsed">
          <div class="section-label">SYSTEM</div>
          <ul>
            <li 
              v-for="item in systemNavItems" 
              :key="item.id"
              :class="{ active: activeItem === item.id }"
              @click="setActiveItem(item.id)"
            >
              <router-link :to="item.route" class="nav-link" @click="handleNavClick">
                <span class="nav-icon">{{ item.icon }}</span>
                <span class="nav-text">{{ item.text }}</span>
              </router-link>
            </li>
          </ul>
        </div>
      </nav>

      <!-- User Profile -->
      <div class="user-profile" v-if="!isCollapsed">
        <div class="user-avatar">
          <div class="avatar">👨‍💼</div>
          <div class="user-info">
            <strong>System Admin</strong>
            <small>Administrator</small>
          </div>
        </div>
        <button class="logout-btn" @click="logout">
          <span class="logout-icon">🔐</span>
          <span class="logout-text">Logout</span>
        </button>
      </div>
    </div>
</template>

<script>
export default {
  name: 'AdminSidebar',
  props: {
    mobileVisible: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isCollapsed: false,
      activeItem: 'dashboard',
      currentColors: [
        { id: 1, name: 'Ocean Blue', hex: '#4A90E2' },
        { id: 2, name: 'Sunset Orange', hex: '#FF6B6B' },
        { id: 3, name: 'Meadow Green', hex: '#51C16B' },
        { id: 4, name: 'Lavender', hex: '#9B59B6' },
      ],
      mainNavItems: [
        { id: 'dashboard', icon: '📊', text: 'Dashboard', route: '/admin/dashboard', badge: 'Live' },
        { id: 'users', icon: '👥', text: 'User Management', route: '/admin/UserManagement', badge: '4' },
        { id: 'products', icon: '🎨', text: 'Paint Products', route: '/admin/PaintProducts' },
        { id: 'inventory', icon: '📦', text: 'Inventory Overview', route: '/admin/Inventory', badge: '12' },
        { id: 'colors', icon: '🌈', text: 'Color Customizations', route: '/admin/ColorCustomization', badge: 'New' },
        { id: 'services', icon: '🔧', text: 'Service Requests', route: '/admin/ServiceRequest' },
        { id: 'reports', icon: '📈', text: 'Reports', route: '/admin/Reports' },
      ],
      systemNavItems: [
        { id: 'settings', icon: '⚙️', text: 'System Settings', route: '/admin/SystemSettings' },
        { id: 'audit', icon: '📋', text: 'Audit Logs', route: '/admin/AuditLogs' },
      ]
    }
  },
  computed: {
    getActiveItemTitle() {
      const item = [...this.mainNavItems, ...this.systemNavItems]
        .find(item => item.id === this.activeItem);
      return item ? item.text : 'Dashboard';
    }
  },
  methods: {
    logoClicked() {
      // Just toggle collapse, don't emit toggle event for mobile visibility
      this.isCollapsed = !this.isCollapsed;
    },
    
    toggleCollapse() {
      this.isCollapsed = !this.isCollapsed;
      // Don't emit toggle event here - we only want to collapse/expand, not hide on mobile
    },
    
    setActiveItem(itemId) {
      this.activeItem = itemId;
    },
    
    logout() {
      alert('Token invalidated. Redirecting to login...');
      // In real app: this.$router.push('/login');
    },
    
    handleNavClick() {
      // Close sidebar on mobile when a nav item is clicked
      // Only emit if we're on mobile (mobileVisible is true)
      if (this.mobileVisible) {
        this.$emit('toggle');
      }
    }
  },
  mounted() {
    // Randomly change color palette every 30 seconds for visual appeal
    setInterval(() => {
      const hue = Math.floor(Math.random() * 360);
      this.currentColors = this.currentColors.map((color, index) => ({
        ...color,
        hex: `hsl(${hue + (index * 90)}, 70%, 60%)`
      }));
    }, 30000);
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Sidebar Styles */
.sidebar {
  width: 280px;
  height: 100vh;           /* full viewport height */
  position: fixed;          /* fix it to viewport */
  top: 0;                   /* top aligned */
  left: 0;                  /* start from left */
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #1a1a2e 0%, #16213e 100%);
  color: white;
  z-index: 1000;
  box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
  overflow: hidden;         /* no scrolling inside */
  transition: width 0.3s ease;
}


.sidebar.collapsed {
  width: 80px;
}

/* Logo Section */
.logo-section {
  padding: 25px 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  background: rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
  position: relative;
}

.logo-section:hover {
  background: rgba(0, 0, 0, 0.3);
}

.logo {
  display: flex;
  align-items: center;
  gap: 15px;
}

.paint-can-icon {
  font-size: 2.5rem;
  animation: paintDrip 2s ease-in-out infinite;
}

@keyframes paintDrip {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

.logo h2 {
  font-size: 1.5rem;
  font-weight: 700;
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  white-space: nowrap;
}

.toggle-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  position: absolute;
  right: 20px;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
}

.toggle-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-50%) rotate(180deg);
}

/* Color Palette */
.color-palette {
  padding: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.palette-title {
  font-size: 0.9rem;
  color: #aaa;
  margin-bottom: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.color-blends {
  display: flex;
  gap: 8px;
}

.color-blob {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  position: relative;
  overflow: hidden;
}

.color-blob::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.3) 1%, transparent 20%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.color-blob:hover {
  transform: scale(1.2) translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.color-blob:hover::after {
  opacity: 1;
}

/* Navigation */
.nav-menu {
  flex: 1;
  overflow-y: auto;
  padding: 20px 0;
}

.nav-section {
  margin-bottom: 25px;
}

.section-label {
  padding: 0 20px 10px;
  font-size: 0.75rem;
  color: #7a7a8c;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.nav-menu ul {
  list-style: none;
}

.nav-menu li {
  position: relative;
  margin: 5px 0;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 15px 20px;
  color: #b0b0c0;
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.nav-link:hover {
  background: linear-gradient(90deg, rgba(74, 144, 226, 0.1), transparent);
  color: white;
  padding-left: 25px;
}

.nav-link::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 3px;
  background: linear-gradient(180deg, #4A90E2, #9B59B6);
  transform: scaleY(0);
  transition: transform 0.3s ease;
}

.nav-link:hover::before {
  transform: scaleY(1);
}

.nav-icon {
  font-size: 1.2rem;
  margin-right: 15px;
  min-width: 24px;
  text-align: center;
  transition: margin-right 0.3s ease;
}

.nav-text {
  flex: 1;
  font-weight: 500;
  white-space: nowrap;
  transition: opacity 0.3s ease;
}

.badge {
  background: linear-gradient(45deg, #FF6B6B, #FF8E53);
  color: white;
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 10px;
  font-weight: bold;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

/* Active State */
li.active .nav-link {
  background: linear-gradient(90deg, rgba(74, 144, 226, 0.2), transparent);
  color: white;
  padding-left: 25px;
}

li.active .nav-link::before {
  transform: scaleY(1);
}

li.active .nav-link::after {
  content: '';
  position: absolute;
  right: 20px;
  width: 8px;
  height: 8px;
  background: #4A90E2;
  border-radius: 50%;
  box-shadow: 0 0 10px #4A90E2;
}

/* User Profile */
.user-profile {
  padding: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.user-avatar {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
}

.avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.user-info {
  display: flex;
  flex-direction: column;
  transition: opacity 0.3s ease;
}

.user-info small {
  color: #aaa;
  font-size: 0.85rem;
}

.logout-btn {
  width: 100%;
  padding: 12px;
  background: linear-gradient(45deg, #FF6B6B, #FF8E53);
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 6px rgba(255, 107, 107, 0.2);
}

.logout-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(255, 107, 107, 0.3);
}

/* Collapsed State Styles */
.sidebar.collapsed .nav-text,
.sidebar.collapsed .section-label,
.sidebar.collapsed .user-info,
.sidebar.collapsed .palette-title,
.sidebar.collapsed .logo h2,
.sidebar.collapsed .logout-text {
  opacity: 0;
  width: 0;
  height: 0;
  overflow: hidden;
  position: absolute;
}

.sidebar.collapsed .color-palette {
  padding: 15px 0;
  display: flex;
  justify-content: center;
}

.sidebar.collapsed .color-blends {
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.sidebar.collapsed .color-blob {
  width: 30px;
  height: 30px;
}

.sidebar.collapsed .logout-btn {
  padding: 10px;
  border-radius: 50%;
  width: 50px;
  height: 50px;
  margin: 0 auto;
}

.sidebar.collapsed .logout-icon {
  font-size: 1.2rem;
}

.sidebar.collapsed .nav-link {
  justify-content: center;
  padding: 15px;
}

.sidebar.collapsed .nav-icon {
  margin-right: 0;
  font-size: 1.3rem;
}

.sidebar.collapsed .badge {
  position: absolute;
  top: 5px;
  right: 5px;
  font-size: 0.6rem;
  padding: 1px 5px;
}

/* Mobile Responsive Design */
@media (max-width: 768px) {
  .sidebar {
    position: fixed;
    left: -280px;
    height: 100vh;
    z-index: 1000;
    transition: left 0.3s ease, width 0.3s ease;
  }

  .sidebar.mobile-visible {
    left: 0;
  }

  .sidebar.collapsed {
    width: 80px;
    left: -80px;
  }

  .sidebar.collapsed.mobile-visible {
    left: 0;
  }

  /* Overlay for mobile when sidebar is visible */
  .sidebar.mobile-visible::before {
    content: '';
    position: fixed;
    top: 0;
    right: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: -1;
    animation: fadeIn 0.3s ease;
  }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>