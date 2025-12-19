<template>
  <div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
      <div class="header-main">
        <h1><i class="fas fa-tachometer-alt"></i> System Dashboard</h1>
        <div class="mobile-date" v-if="isMobile">
          <i class="far fa-calendar-alt"></i> {{ shortDate }}
        </div>
      </div>
      <div class="header-actions">
        <button class="refresh-btn" @click="refreshData">
          <i class="fas fa-sync-alt"></i> 
          <span class="btn-text">Refresh</span>
        </button>
        <div class="date-display" v-if="!isMobile">
          <i class="far fa-calendar-alt"></i> {{ currentDate }}
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <!-- Total Users Card -->
      <div class="stat-card" style="border-left: 4px solid #4A90E2;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4A90E2, #357ABD);">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
          <h3>Total Users</h3>
          <div class="stat-value">{{ stats.totalUsers }}</div>
          <div class="stat-breakdown">
            <span class="role-badge admin">Admins: {{ stats.usersByRole.admin }}</span>
            <span class="role-badge distributor">Distributors: {{ stats.usersByRole.distributor }}</span>
            <span class="role-badge service-provider">Service: {{ stats.usersByRole.serviceProvider }}</span>
            <span class="role-badge client">Clients: {{ stats.usersByRole.client }}</span>
          </div>
        </div>
      </div>

      <!-- Distributors Card -->
      <div class="stat-card" style="border-left: 4px solid #51C16B;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #51C16B, #3DA857);">
          <i class="fas fa-truck"></i>
        </div>
        <div class="stat-content">
          <h3>Active Distributors</h3>
          <div class="stat-value">{{ stats.totalDistributors }}</div>
          <div class="stat-subtext">
            <span class="trend positive">
              <i class="fas fa-arrow-up"></i> 12% this month
            </span>
          </div>
          <div class="mini-chart">
            <div class="chart-bar" style="width: 70%; background-color: #51C16B;"></div>
            <div class="chart-bar" style="width: 85%; background-color: #51C16B;"></div>
            <div class="chart-bar" style="width: 60%; background-color: #51C16B;"></div>
          </div>
        </div>
      </div>

      <!-- Service Providers Card -->
      <div class="stat-card" style="border-left: 4px solid #FF6B6B;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #FF6B6B, #E05555);">
          <i class="fas fa-tools"></i>
        </div>
        <div class="stat-content">
          <h3>Service Providers</h3>
          <div class="stat-value">{{ stats.totalServiceProviders }}</div>
          <div class="stat-subtext">
            <span class="status active">{{ stats.activeServiceProviders }} Active</span>
          </div>
          <div class="progress-container">
            <div class="progress-bar" :style="{ width: stats.serviceProviderActivity + '%' }"></div>
            <span>Activity: {{ stats.serviceProviderActivity }}%</span>
          </div>
        </div>
      </div>

      <!-- Color Customizations Card -->
      <div class="stat-card" style="border-left: 4px solid #9B59B6;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #9B59B6, #8E44AD);">
          <i class="fas fa-palette"></i>
        </div>
        <div class="stat-content">
          <h3>Color Mixes</h3>
          <div class="stat-value">{{ stats.totalColorCustomizations }}</div>
          <div class="stat-subtext">From Unity System</div>
          <div class="color-preview">
            <div 
              v-for="color in recentColors" 
              :key="color.id"
              class="color-dot"
              :style="{ backgroundColor: color.hex }"
              :title="color.name"
            ></div>
            <div class="color-count">+{{ recentColors.length }} more</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="recent-activity-section">
      <div class="section-header">
        <h2><i class="fas fa-history"></i> Recent Activity</h2>
        <button class="view-all-btn" @click="viewAllActivity">
          View All <i class="fas fa-arrow-right"></i>
        </button>
      </div>

      <div class="activity-table">
        <table>
          <thead>
            <tr>
              <th>User</th>
              <th>Action</th>
              <th>Details</th>
              <th>Time</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="activity in recentActivities" :key="activity.id">
              <td class="user-cell">
                <div class="user-avatar">
                  <i :class="activity.userIcon"></i>
                </div>
                <div class="user-info">
                  <strong>{{ activity.userName }}</strong>
                  <small>{{ activity.userRole }}</small>
                </div>
              </td>
              <td>
                <span class="action-badge" :class="activity.actionType">
                  <i :class="activity.actionIcon"></i> {{ activity.action }}
                </span>
              </td>
              <td class="details-cell">
                <span class="details-text">{{ activity.details }}</span>
                <span v-if="activity.color" class="color-indicator" :style="{ backgroundColor: activity.color }"></span>
              </td>
              <td>
                <div class="time-display">
                  <i class="far fa-clock"></i> {{ activity.time }}
                </div>
              </td>
              <td>
                <span class="status-badge" :class="activity.status">
                  {{ activity.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="quick-stats-row">
      <div class="quick-stat">
        <div class="quick-stat-icon" style="background: linear-gradient(135deg, #FF8E53, #FF6B6B);">
          <i class="fas fa-chart-line"></i>
        </div>
        <div>
          <h4>Daily Color Mixes</h4>
          <p>{{ stats.dailyColorMixes }} today</p>
        </div>
      </div>
      <div class="quick-stat">
        <div class="quick-stat-icon" style="background: linear-gradient(135deg, #4A90E2, #357ABD);">
          <i class="fas fa-box-open"></i>
        </div>
        <div>
          <h4>Low Stock Items</h4>
          <p>{{ stats.lowStockItems }} products</p>
        </div>
      </div>
      <div class="quick-stat">
        <div class="quick-stat-icon" style="background: linear-gradient(135deg, #51C16B, #3DA857);">
          <i class="fas fa-bell"></i>
        </div>
        <div>
          <h4>Pending Requests</h4>
          <p>{{ stats.pendingRequests }} awaiting action</p>
        </div>
      </div>
      <div class="quick-stat">
        <div class="quick-stat-icon" style="background: linear-gradient(135deg, #9B59B6, #8E44AD);">
          <i class="fas fa-cog"></i>
        </div>
        <div>
          <h4>System Health</h4>
          <div class="health-status">
            <div class="health-indicator good"></div>
            <span>All Systems Operational</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Paint Splash Background Effects -->
    <div class="paint-splash-1"></div>
    <div class="paint-splash-2"></div>
  </div>
</template>

<script>
export default {
  name: 'Dashboard',
  data() {
    return {
      currentDate: new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      }),
      shortDate: new Date().toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric',
        year: 'numeric'
      }),
      isMobile: false,
      stats: {
        totalUsers: 142,
        usersByRole: {
          admin: 3,
          distributor: 12,
          serviceProvider: 28,
          client: 99
        },
        totalDistributors: 12,
        totalServiceProviders: 28,
        activeServiceProviders: 22,
        serviceProviderActivity: 78,
        totalColorCustomizations: 347,
        dailyColorMixes: 24,
        lowStockItems: 8,
        pendingRequests: 15
      },
      recentColors: [
        { id: 1, name: 'Ocean Blue', hex: '#4A90E2' },
        { id: 2, name: 'Sunset Orange', hex: '#FF6B6B' },
        { id: 3, name: 'Meadow Green', hex: '#51C16B' },
        { id: 4, name: 'Lavender', hex: '#9B59B6' },
        { id: 5, name: 'Sunshine Yellow', hex: '#F1C40F' }
      ],
      recentActivities: [
        {
          id: 1,
          userName: 'Maria Santos',
          userRole: 'Service Provider',
          userIcon: 'fas fa-user-tie',
          action: 'Color Mix',
          actionIcon: 'fas fa-palette',
          actionType: 'color-mix',
          details: 'Created "Ocean Breeze" color palette',
          color: '#4A90E2',
          time: '2 minutes ago',
          status: 'completed'
        },
        {
          id: 2,
          userName: 'Juan Dela Cruz',
          userRole: 'Distributor',
          userIcon: 'fas fa-truck',
          action: 'Inventory Update',
          actionIcon: 'fas fa-boxes',
          actionType: 'inventory',
          details: 'Added 50 units of Premium White',
          time: '15 minutes ago',
          status: 'completed'
        },
        {
          id: 3,
          userName: 'System Admin',
          userRole: 'Administrator',
          userIcon: 'fas fa-user-shield',
          action: 'User Added',
          actionIcon: 'fas fa-user-plus',
          actionType: 'user',
          details: 'Added new service provider',
          time: '1 hour ago',
          status: 'completed'
        },
        {
          id: 4,
          userName: 'Anna Reyes',
          userRole: 'Client',
          userIcon: 'fas fa-user',
          action: 'Color Request',
          actionIcon: 'fas fa-paint-brush',
          actionType: 'request',
          details: 'Requested custom color consultation',
          time: '2 hours ago',
          status: 'pending'
        },
        {
          id: 5,
          userName: 'Carlos Lim',
          userRole: 'Service Provider',
          userIcon: 'fas fa-user-tie',
          action: 'Job Completed',
          actionIcon: 'fas fa-check-circle',
          actionType: 'service',
          details: 'Completed paint job for Client #234',
          time: '3 hours ago',
          status: 'completed'
        }
      ]
    }
  },
  methods: {
    refreshData() {
      // Simulate data refresh
      this.stats.totalColorCustomizations += 1;
      this.stats.dailyColorMixes += 1;
      
      // Add a new activity
      const newActivity = {
        id: this.recentActivities.length + 1,
        userName: 'System',
        userRole: 'Auto',
        userIcon: 'fas fa-robot',
        action: 'System Update',
        actionIcon: 'fas fa-sync-alt',
        actionType: 'system',
        details: 'Dashboard data refreshed',
        time: 'Just now',
        status: 'completed'
      };
      
      this.recentActivities.unshift(newActivity);
      
      // Show success message
      this.$toast.success('Dashboard data refreshed successfully!');
    },
    viewAllActivity() {
      alert('Redirecting to full activity log...');
      // In real app: this.$router.push('/admin/audit');
    },
    initializeAnimations() {
      // Add subtle animations to stats cards on hover
      const cards = document.querySelectorAll('.stat-card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
          card.style.transform = 'translateY(-5px)';
          card.style.boxShadow = '0 12px 24px rgba(0,0,0,0.15)';
        });
        card.addEventListener('mouseleave', () => {
          card.style.transform = 'translateY(0)';
          card.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
        });
      });
    },
    checkMobile() {
      this.isMobile = window.innerWidth <= 768;
    }
  },
  mounted() {
    // Initialize animations
    this.initializeAnimations();
    
    // Check mobile on mount
    this.checkMobile();
    
    // Add resize listener
    window.addEventListener('resize', this.checkMobile);
  },
  beforeDestroy() {
    // Remove resize listener
    window.removeEventListener('resize', this.checkMobile);
  }
}
</script>

<style scoped>
.dashboard-container {
  padding: 30px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  width: 100%;
  box-sizing: border-box;
}

/* Remove scroll bar from the container */
.dashboard-container::-webkit-scrollbar {
  display: none;
}

.dashboard-container {
  -ms-overflow-style: none;  /* IE and Edge */
  scrollbar-width: none;  /* Firefox */
}

/* Dashboard Header - IMPROVED */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  background: white;
  padding: 20px 25px;
  border-radius: 15px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  flex-wrap: wrap;
  gap: 15px;
  transition: all 0.3s ease;
}

.header-main {
  display: flex;
  align-items: center;
  gap: 15px;
  flex: 1;
  min-width: 0;
}

.dashboard-header h1 {
  font-size: 1.8rem;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 15px;
  margin: 0;
  flex-wrap: wrap;
}

.dashboard-header h1 i {
  color: #4A90E2;
  font-size: 1.8rem;
  flex-shrink: 0;
}

.mobile-date {
  display: none;
  align-items: center;
  gap: 8px;
  color: #777;
  font-size: 0.9rem;
  padding: 6px 12px;
  background: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.mobile-date i {
  color: #4A90E2;
}

.header-actions {
  display: flex;
  gap: 15px;
  align-items: center;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.refresh-btn {
  background: linear-gradient(45deg, #4A90E2, #357ABD);
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  transition: all 0.3s ease;
  white-space: nowrap;
  min-height: 48px;
}

.refresh-btn .btn-text {
  transition: opacity 0.3s ease;
}

.refresh-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(74, 144, 226, 0.3);
}

.refresh-btn:active {
  transform: translateY(0);
}

.date-display {
  background: white;
  padding: 12px 20px;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #555;
  white-space: nowrap;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.date-display i {
  color: #4A90E2;
  font-size: 1rem;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 25px;
  margin-bottom: 40px;
}

.stat-card {
  background: white;
  border-radius: 15px;
  padding: 25px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  display: flex;
  gap: 20px;
  transition: all 0.3s ease;
  border-left: 4px solid;
  width: 100%;
  box-sizing: border-box;
}

.stat-card:hover {
  box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.stat-icon {
  width: 70px;
  height: 70px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.8rem;
  flex-shrink: 0;
}

.stat-content {
  flex: 1;
  min-width: 0; /* Prevents flex item from overflowing */
}

.stat-content h3 {
  color: #555;
  font-size: 1rem;
  margin-bottom: 10px;
  font-weight: 600;
  word-wrap: break-word;
}

.stat-value {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 10px;
  line-height: 1;
}

.stat-breakdown {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.role-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  white-space: nowrap;
}

.role-badge.admin {
  background: #E3F2FD;
  color: #1976D2;
}

.role-badge.distributor {
  background: #E8F5E9;
  color: #388E3C;
}

.role-badge.service-provider {
  background: #FFF3E0;
  color: #F57C00;
}

.role-badge.client {
  background: #F3E5F5;
  color: #7B1FA2;
}

.stat-subtext {
  color: #777;
  font-size: 0.9rem;
  margin: 8px 0;
  word-wrap: break-word;
}

.trend.positive {
  color: #51C16B;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.trend i {
  font-size: 0.8rem;
}

.mini-chart {
  display: flex;
  gap: 4px;
  height: 20px;
  align-items: flex-end;
  margin-top: 15px;
}

.chart-bar {
  height: 100%;
  border-radius: 2px;
  flex: 1;
  opacity: 0.7;
}

.progress-container {
  background: #f0f0f0;
  border-radius: 10px;
  height: 8px;
  margin: 15px 0;
  position: relative;
}

.progress-bar {
  background: #FF6B6B;
  height: 100%;
  border-radius: 10px;
  transition: width 0.5s ease;
}

.progress-container span {
  position: absolute;
  right: 0;
  top: -20px;
  font-size: 0.8rem;
  color: #777;
}

.color-preview {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 15px;
  flex-wrap: wrap;
}

.color-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  cursor: pointer;
  transition: transform 0.3s ease;
  flex-shrink: 0;
}

.color-dot:hover {
  transform: scale(1.2);
}

.color-count {
  color: #777;
  font-size: 0.9rem;
  margin-left: 5px;
}

/* Recent Activity */
.recent-activity-section {
  background: white;
  border-radius: 15px;
  padding: 30px;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  width: 100%;
  box-sizing: border-box;
  overflow: hidden;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  flex-wrap: wrap;
  gap: 15px;
}

.section-header h2 {
  font-size: 1.5rem;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 0;
}

.section-header h2 i {
  color: #4A90E2;
}

.view-all-btn {
  background: transparent;
  color: #4A90E2;
  border: 2px solid #4A90E2;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  white-space: nowrap;
  min-height: 48px;
}

.view-all-btn:hover {
  background: #4A90E2;
  color: white;
}

.activity-table {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
  width: 100%;
}

.activity-table table {
  width: 100%;
  border-collapse: collapse;
  min-width: 600px; /* Minimum width before scrolling */
}

.activity-table th {
  text-align: left;
  padding: 15px;
  background: #f8f9fa;
  color: #555;
  font-weight: 600;
  border-bottom: 2px solid #e0e0e0;
  white-space: nowrap;
}

.activity-table td {
  padding: 15px;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: middle;
}

.user-cell {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 150px;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4A90E2, #357ABD);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  flex-direction: column;
  min-width: 0; /* Allows text truncation */
}

.user-info strong {
  font-size: 0.95rem;
  color: #2c3e50;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-info small {
  color: #777;
  font-size: 0.8rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.action-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
}

.action-badge.color-mix {
  background: #F3E5F5;
  color: #9B59B6;
}

.action-badge.inventory {
  background: #E8F5E9;
  color: #51C16B;
}

.action-badge.user {
  background: #E3F2FD;
  color: #4A90E2;
}

.action-badge.request {
  background: #FFF3E0;
  color: #FF8E53;
}

.action-badge.service {
  background: #E8F5E9;
  color: #51C16B;
}

.action-badge.system {
  background: #f0f0f0;
  color: #777;
}

.details-cell {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 200px;
}

.details-text {
  flex: 1;
  word-wrap: break-word;
  min-width: 0;
}

.color-indicator {
  width: 20px;
  height: 20px;
  border-radius: 4px;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  flex-shrink: 0;
}

.time-display {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #777;
  white-space: nowrap;
}

.time-display i {
  font-size: 0.9rem;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 500;
  white-space: nowrap;
}

.status-badge.completed {
  background: #E8F5E9;
  color: #51C16B;
}

.status-badge.pending {
  background: #FFF3E0;
  color: #FF8E53;
}

.status-badge.failed {
  background: #FFEBEE;
  color: #F44336;
}

/* Quick Stats Row */
.quick-stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px;
  margin-top: 30px;
}

.quick-stat {
  background: white;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  width: 100%;
  box-sizing: border-box;
}

.quick-stat:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.12);
}

.quick-stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.quick-stat h4 {
  color: #2c3e50;
  margin-bottom: 5px;
  font-size: 1rem;
  word-wrap: break-word;
}

.quick-stat p {
  color: #4A90E2;
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0;
}

.health-status {
  display: flex;
  align-items: center;
  gap: 10px;
}

.health-indicator {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.health-indicator.good {
  background: #51C16B;
  box-shadow: 0 0 10px #51C16B;
}

/* Paint Splash Effects */
.paint-splash-1 {
  position: absolute;
  top: -100px;
  right: -100px;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(74, 144, 226, 0.05) 0%, transparent 70%);
  z-index: 0;
  pointer-events: none;
}

.paint-splash-2 {
  position: absolute;
  bottom: -150px;
  left: -150px;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(155, 89, 182, 0.05) 0%, transparent 70%);
  z-index: 0;
  pointer-events: none;
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-card, .recent-activity-section, .quick-stat {
  animation: fadeIn 0.6s ease-out forwards;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

/* Responsive Design - IMPROVED */
@media (max-width: 1200px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 992px) {
  .quick-stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .dashboard-container {
    padding: 20px 15px;
  }
  
  .dashboard-header {
    padding: 15px;
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
    margin-bottom: 20px;
  }
  
  .header-main {
    width: 100%;
    justify-content: space-between;
  }
  
  .mobile-date {
    display: flex;
  }
  
  .dashboard-header h1 {
    font-size: 1.6rem;
    gap: 10px;
  }
  
  .dashboard-header h1 i {
    font-size: 1.5rem;
  }
  
  .header-actions {
    width: 100%;
    flex-direction: row;
    justify-content: space-between;
  }
  
  .refresh-btn {
    flex: 1;
    min-height: 52px;
    padding: 12px 15px;
    justify-content: center;
  }
  
  .date-display {
    display: none;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .stat-card {
    padding: 20px;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  
  .stat-icon {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
  }
  
  .stat-value {
    font-size: 2.2rem;
  }
  
  .stat-breakdown {
    justify-content: center;
  }
  
  .recent-activity-section {
    padding: 20px 15px;
    margin-bottom: 20px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 15px;
  }
  
  .section-header h2 {
    font-size: 1.3rem;
  }
  
  .view-all-btn {
    width: 100%;
    justify-content: center;
    min-height: 52px;
  }
  
  .activity-table {
    margin: 0 -15px;
    width: calc(100% + 30px);
  }
  
  .activity-table table {
    min-width: 700px;
  }
  
  .quick-stats-row {
    grid-template-columns: 1fr;
    margin-top: 20px;
  }
  
  .quick-stat {
    flex-direction: column;
    text-align: center;
    gap: 15px;
  }
  
  .paint-splash-1,
  .paint-splash-2 {
    display: none;
  }
}

@media (max-width: 576px) {
  .dashboard-header h1 {
    font-size: 1.4rem;
  }
  
  .refresh-btn .btn-text {
    display: none;
  }
  
  .refresh-btn {
    max-width: 52px;
    min-width: 52px;
    padding: 12px;
  }
  
  .mobile-date {
    font-size: 0.85rem;
    padding: 6px 10px;
  }
  
  .stat-value {
    font-size: 2rem;
  }
  
  .role-badge {
    padding: 5px 10px;
    font-size: 0.75rem;
  }
  
  .action-badge, .status-badge {
    padding: 5px 10px;
    font-size: 0.8rem;
  }
  
  .activity-table th,
  .activity-table td {
    padding: 10px;
  }
  
  .user-cell {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
  
  .user-avatar {
    width: 35px;
    height: 35px;
  }
  
  .details-cell {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
}

@media (max-width: 375px) {
  .dashboard-container {
    padding: 15px 10px;
  }
  
  .dashboard-header {
    padding: 12px;
  }
  
  .dashboard-header h1 {
    font-size: 1.3rem;
  }
  
  .mobile-date {
    font-size: 0.8rem;
    padding: 5px 8px;
  }
  
  .stat-card {
    padding: 15px;
  }
  
  .stat-value {
    font-size: 1.8rem;
  }
}

/* Touch device optimizations */
@media (hover: none) and (pointer: coarse) {
  .stat-card:hover,
  .quick-stat:hover,
  .refresh-btn:hover,
  .view-all-btn:hover {
    transform: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  
  .color-dot:hover {
    transform: none;
  }
  
  /* Increase tap targets for mobile */
  .refresh-btn,
  .view-all-btn,
  .action-badge,
  .status-badge {
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  
  .role-badge {
    padding: 8px 12px;
  }
  
  /* Add active state feedback */
  .refresh-btn:active,
  .view-all-btn:active {
    transform: scale(0.98);
  }
}
</style>