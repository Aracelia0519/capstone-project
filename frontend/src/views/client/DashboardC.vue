<template>
  <div class="client-dashboard">
    <!-- Hero Section / Landing Content -->
    <div class="hero-section">
      <div class="hero-gradient"></div>
      <div class="hero-content">
        <div class="hero-left">
          <h1 class="hero-title">
            <span class="hero-title-line">Transform Your Space</span>
            <span class="hero-title-line text-gradient">With Perfect Colors</span>
          </h1>
          <p class="hero-subtitle">Visualize, select, and track your paint projects with real-time updates</p>
          <div class="hero-stats">
            <div class="stat-bubble">
              <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="stat-text">{{ dashboardStats.activeProjects }} Active Projects</span>
            </div>
            <div class="stat-bubble">
              <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
              <span class="stat-text">{{ dashboardStats.colorsSelected }} Colors Selected</span>
            </div>
          </div>
        </div>
        <div class="hero-right">
          <div class="color-wheel-animation">
            <div class="color-circle"></div>
            <div class="color-circle color-circle-2"></div>
            <div class="color-circle color-circle-3"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid">
      <!-- Current Service Requests -->
      <div class="dashboard-card card-service-requests">
        <div class="card-header">
          <div class="card-title-wrapper">
            <div class="card-icon">
              <svg class="card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <h3 class="card-title">Current Service Requests</h3>
          </div>
          <span class="card-badge">{{ serviceRequests.length }} Active</span>
        </div>
        
        <div class="requests-list">
          <div v-for="request in serviceRequests" :key="request.id" class="request-item">
            <div class="request-info">
              <div class="request-type">{{ request.type }}</div>
              <div class="request-details">
                <div class="request-service-provider">
                  <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span class="provider-name">{{ request.serviceProvider }}</span>
                </div>
                <div class="request-date">{{ formatDate(request.date) }}</div>
              </div>
            </div>
            <div class="request-status">
              <span :class="['status-badge', `status-${request.status}`]">
                {{ formatStatus(request.status) }}
              </span>
              <div class="request-progress">
                <div class="progress-bar">
                  <div :class="['progress-fill', `progress-${request.status}`]" 
                       :style="{ width: request.progress + '%' }"></div>
                </div>
                <span class="progress-text">{{ request.progress }}%</span>
              </div>
            </div>
          </div>
        </div>
        
        <button class="view-all-btn">
          <span class="view-all-text">View All Requests</span>
          <svg class="view-all-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Selected Paint Colors -->
      <div class="dashboard-card card-colors">
        <div class="card-header">
          <div class="card-title-wrapper">
            <div class="card-icon">
              <svg class="card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            <h3 class="card-title">Selected Paint Colors</h3>
          </div>
          <span class="card-badge">Latest</span>
        </div>
        
        <div class="colors-grid">
          <div v-for="color in selectedColors" :key="color.id" class="color-item">
            <div class="color-preview" :style="{ backgroundColor: color.hex }">
              <div class="color-overlay"></div>
              <span class="color-name">{{ color.name }}</span>
            </div>
            <div class="color-details">
              <div class="color-codes">
                <div class="color-code">
                  <span class="code-label">HEX</span>
                  <span class="code-value">{{ color.hex }}</span>
                </div>
                <div class="color-code">
                  <span class="code-label">RGB</span>
                  <span class="code-value">{{ color.rgb }}</span>
                </div>
              </div>
              <div class="color-meta">
                <span class="color-project">{{ color.project }}</span>
                <span class="color-date">{{ color.date }}</span>
              </div>
            </div>
          </div>
        </div>
        
        <button class="view-all-btn">
          <span class="view-all-text">View Color History</span>
          <svg class="view-all-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>

      <!-- Service Providers -->
      <div class="dashboard-card card-providers">
        <div class="card-header">
          <div class="card-title-wrapper">
            <div class="card-icon">
              <svg class="card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <h3 class="card-title">Service Providers</h3>
          </div>
        </div>
        
        <div class="providers-list">
          <div v-for="provider in serviceProviders" :key="provider.id" class="provider-item">
            <div class="provider-avatar">
              <div class="avatar-circle">{{ provider.initials }}</div>
              <div :class="['status-indicator', provider.status]"></div>
            </div>
            <div class="provider-info">
              <h4 class="provider-name">{{ provider.name }}</h4>
              <div class="provider-rating">
                <div class="stars">
                  <span v-for="n in 5" :key="n" class="star">
                    <svg :class="['star-icon', n <= provider.rating ? 'text-yellow-400 fill-yellow-400' : 'text-gray-600']" 
                         fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                  </span>
                </div>
                <span class="rating-text">{{ provider.rating.toFixed(1) }}</span>
              </div>
              <p class="provider-specialty">{{ provider.specialty }}</p>
            </div>
            <button class="contact-btn">
              <svg class="contact-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Recent Activity Table -->
      <div class="dashboard-card card-activity">
        <div class="card-header">
          <div class="card-title-wrapper">
            <div class="card-icon">
              <svg class="card-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="card-title">Recent Activity</h3>
          </div>
        </div>
        
        <div class="activity-table-container">
          <div class="activity-table-scroll">
            <table class="activity-table">
              <thead>
                <tr>
                  <th class="activity-column">Activity</th>
                  <th class="project-column">Project</th>
                  <th class="date-column">Date</th>
                  <th class="status-column">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="activity in recentActivity" :key="activity.id">
                  <td class="activity-column">
                    <div class="activity-item">
                      <div :class="['activity-icon', `icon-${activity.type}`]">
                        <svg class="activity-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="activity.type === 'color'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                          <path v-if="activity.type === 'request'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                          <path v-if="activity.type === 'update'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                      <span class="activity-description">{{ activity.description }}</span>
                    </div>
                  </td>
                  <td class="project-column">{{ activity.project }}</td>
                  <td class="date-column">{{ activity.date }}</td>
                  <td class="status-column">
                    <span :class="['activity-status', `status-${activity.status}`]">
                      {{ activity.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
      <h3 class="quick-actions-title">Quick Actions</h3>
      <div class="actions-grid">
        <button class="action-btn" @click="requestNewService">
          <div class="action-icon bg-gradient-to-br from-blue-500 to-cyan-400">
            <svg class="action-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
          </div>
          <span class="action-text">New Service Request</span>
        </button>
        <button class="action-btn" @click="browseColors">
          <div class="action-icon bg-gradient-to-br from-purple-500 to-pink-400">
            <svg class="action-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <span class="action-text">Browse Colors</span>
        </button>
        <button class="action-btn" @click="openColorPreview">
          <div class="action-icon bg-gradient-to-br from-indigo-500 to-violet-400">
            <svg class="action-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
            </svg>
          </div>
          <span class="action-text">Color Preview</span>
        </button>
        <button class="action-btn" @click="viewRecommendations">
          <div class="action-icon bg-gradient-to-br from-amber-500 to-orange-400">
            <svg class="action-icon-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
          </div>
          <span class="action-text">Get Recommendations</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClientDashboard',
  data() {
    return {
      dashboardStats: {
        activeProjects: 3,
        colorsSelected: 8,
        totalRequests: 5,
        providersAvailable: 12
      },
      serviceRequests: [
        {
          id: 1,
          type: 'Living Room Painting',
          serviceProvider: 'John Paint Masters',
          date: '2024-03-15',
          status: 'in-progress',
          progress: 65
        },
        {
          id: 2,
          type: 'Exterior House Paint',
          serviceProvider: 'Cavite Pro Painters',
          date: '2024-03-20',
          status: 'pending',
          progress: 20
        },
        {
          id: 3,
          type: 'Kitchen Cabinet Paint',
          serviceProvider: 'Color Experts PH',
          date: '2024-03-10',
          status: 'completed',
          progress: 100
        }
      ],
      selectedColors: [
        {
          id: 1,
          name: 'Ocean Breeze',
          hex: '#38bdf8',
          rgb: '56, 189, 248',
          project: 'Bedroom Renovation',
          date: 'Today'
        },
        {
          id: 2,
          name: 'Misty Morning',
          hex: '#c084fc',
          rgb: '192, 132, 252',
          project: 'Living Room',
          date: 'Yesterday'
        },
        {
          id: 3,
          name: 'Sunset Glow',
          hex: '#f59e0b',
          rgb: '245, 158, 11',
          project: 'Kitchen Accent',
          date: 'Mar 18'
        },
        {
          id: 4,
          name: 'Forest Green',
          hex: '#10b981',
          rgb: '16, 185, 129',
          project: 'Study Room',
          date: 'Mar 15'
        }
      ],
      serviceProviders: [
        {
          id: 1,
          initials: 'JM',
          name: 'John Paint Masters',
          rating: 4.8,
          specialty: 'Interior & Exterior',
          status: 'online'
        },
        {
          id: 2,
          initials: 'CP',
          name: 'Cavite Pro Painters',
          rating: 4.6,
          specialty: 'Commercial Projects',
          status: 'offline'
        },
        {
          id: 3,
          initials: 'CE',
          name: 'Color Experts PH',
          rating: 4.9,
          specialty: 'Color Consultation',
          status: 'online'
        }
      ],
      recentActivity: [
        {
          id: 1,
          type: 'color',
          description: 'Selected new color for bedroom',
          project: 'Bedroom Renovation',
          date: 'Today, 10:30 AM',
          status: 'completed'
        },
        {
          id: 2,
          type: 'request',
          description: 'Service request submitted',
          project: 'Kitchen Cabinet Paint',
          date: 'Mar 18, 2:15 PM',
          status: 'in-progress'
        },
        {
          id: 3,
          type: 'update',
          description: 'Project status updated',
          project: 'Living Room Painting',
          date: 'Mar 17, 4:45 PM',
          status: 'pending'
        },
        {
          id: 4,
          type: 'color',
          description: 'Color preview generated',
          project: 'Study Room',
          date: 'Mar 16, 11:20 AM',
          status: 'completed'
        },
        {
          id: 5,
          type: 'request',
          description: 'New service inquiry',
          project: 'Exterior House Paint',
          date: 'Mar 15, 9:00 AM',
          status: 'pending'
        }
      ]
    }
  },
  methods: {
    requestNewService() {
      console.log('Request new service clicked')
      // Navigate to service request page
    },
    browseColors() {
      console.log('Browse colors clicked')
      // Navigate to color browser
    },
    openColorPreview() {
      console.log('Open color preview clicked')
      // Navigate to Unity color preview
    },
    viewRecommendations() {
      console.log('View recommendations clicked')
      // Navigate to recommendations page
    },
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      })
    },
    formatStatus(status) {
      const statusMap = {
        'pending': 'Pending',
        'in-progress': 'In Progress',
        'completed': 'Completed'
      }
      return statusMap[status] || status
    }
  }
}
</script>

<style scoped>
.client-dashboard {
  min-height: 100vh;
  padding: 1rem;
  background: #0f172a;
  color: #e2e8f0;
  overflow-x: hidden;
}

/* Mobile-first padding */
@media (min-width: 640px) {
  .client-dashboard {
    padding: 1.5rem;
  }
}

/* Hero Section - Mobile First */
.hero-section {
  position: relative;
  background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
  border-radius: 1rem;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  overflow: hidden;
  border: 1px solid rgba(56, 189, 248, 0.1);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

@media (min-width: 768px) {
  .hero-section {
    border-radius: 1.5rem;
    padding: 2rem;
    margin-bottom: 2rem;
  }
}

@media (min-width: 1024px) {
  .hero-section {
    padding: 2.5rem;
  }
}

.hero-gradient {
  position: absolute;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle at 70% 50%, rgba(56, 189, 248, 0.1) 0%, transparent 70%);
}

@media (min-width: 768px) {
  .hero-gradient {
    width: 60%;
  }
}

.hero-content {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 2rem;
  z-index: 1;
}

@media (min-width: 768px) {
  .hero-content {
    flex-direction: row;
    text-align: left;
    align-items: center;
    justify-content: space-between;
  }
}

.hero-left {
  width: 100%;
}

@media (min-width: 768px) {
  .hero-left {
    flex: 1;
  }
}

.hero-title {
  font-size: 1.75rem;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 0.75rem;
}

@media (min-width: 640px) {
  .hero-title {
    font-size: 2.25rem;
  }
}

@media (min-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
    margin-bottom: 1rem;
  }
}

@media (min-width: 1024px) {
  .hero-title {
    font-size: 3rem;
  }
}

.hero-title-line {
  display: block;
}

.text-gradient {
  background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 50%, #0284c7 100%);
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
}

.hero-subtitle {
  font-size: 1rem;
  color: #94a3b8;
  margin-bottom: 1.5rem;
  max-width: 100%;
}

@media (min-width: 640px) {
  .hero-subtitle {
    font-size: 1.125rem;
    max-width: 600px;
  }
}

@media (min-width: 768px) {
  .hero-subtitle {
    margin-bottom: 2rem;
  }
}

.hero-stats {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

@media (min-width: 640px) {
  .hero-stats {
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
  }
}

@media (min-width: 768px) {
  .hero-stats {
    justify-content: flex-start;
  }
}

.stat-bubble {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 9999px;
  border: 1px solid rgba(56, 189, 248, 0.2);
  backdrop-filter: blur(10px);
  font-weight: 500;
  width: 100%;
}

@media (min-width: 640px) {
  .stat-bubble {
    width: auto;
    justify-content: flex-start;
  }
}

.stat-icon {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
}

.stat-text {
  font-size: 0.875rem;
  white-space: nowrap;
}

@media (min-width: 640px) {
  .stat-text {
    font-size: 1rem;
  }
}

.hero-right {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.color-wheel-animation {
  position: relative;
  width: 150px;
  height: 150px;
}

@media (min-width: 640px) {
  .color-wheel-animation {
    width: 180px;
    height: 180px;
  }
}

@media (min-width: 768px) {
  .color-wheel-animation {
    width: 200px;
    height: 200px;
  }
}

.color-circle {
  position: absolute;
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: conic-gradient(from 0deg, #38bdf8, #0ea5e9, #0284c7, #38bdf8);
  opacity: 0.3;
  animation: spin 20s linear infinite;
}

.color-circle-2 {
  width: 80%;
  height: 80%;
  top: 10%;
  left: 10%;
  animation: spin 15s linear infinite reverse;
  background: conic-gradient(from 180deg, #c084fc, #a855f7, #7c3aed, #c084fc);
}

.color-circle-3 {
  width: 60%;
  height: 60%;
  top: 20%;
  left: 20%;
  animation: spin 10s linear infinite;
  background: conic-gradient(from 90deg, #10b981, #059669, #047857, #10b981);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Dashboard Grid - Mobile First (Single Column) */
.dashboard-grid {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 1024px) {
  .dashboard-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
  
  .card-service-requests {
    grid-column: span 7;
  }
  
  .card-colors {
    grid-column: span 5;
  }
  
  .card-providers {
    grid-column: span 4;
  }
  
  .card-activity {
    grid-column: span 8;
  }
}

/* Dashboard Cards */
.dashboard-card {
  background: rgba(30, 41, 59, 0.6);
  border-radius: 0.875rem;
  padding: 1.25rem;
  border: 1px solid rgba(100, 116, 139, 0.2);
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  width: 100%;
  overflow: hidden;
}

@media (min-width: 768px) {
  .dashboard-card {
    border-radius: 1rem;
    padding: 1.5rem;
  }
}

.dashboard-card:hover {
  border-color: rgba(56, 189, 248, 0.3);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.card-header {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
}

@media (min-width: 640px) {
  .card-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }
}

.card-title-wrapper {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-icon {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.75rem;
  background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

@media (min-width: 768px) {
  .card-icon {
    width: 2.5rem;
    height: 2.5rem;
  }
}

.card-icon-svg {
  width: 1.25rem;
  height: 1.25rem;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #e2e8f0;
  line-height: 1.3;
}

@media (min-width: 768px) {
  .card-title {
    font-size: 1.25rem;
  }
}

.card-badge {
  padding: 0.25rem 0.75rem;
  background: rgba(56, 189, 248, 0.1);
  color: #38bdf8;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid rgba(56, 189, 248, 0.2);
  align-self: flex-start;
}

@media (min-width: 640px) {
  .card-badge {
    align-self: center;
  }
}

/* Service Requests - Mobile Optimized */
.requests-list {
  margin-bottom: 1.25rem;
}

.request-item {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.75rem;
  margin-bottom: 0.75rem;
  border: 1px solid rgba(100, 116, 139, 0.1);
  transition: all 0.2s ease;
}

@media (min-width: 640px) {
  .request-item {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
  }
}

.request-item:hover {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(56, 189, 248, 0.2);
}

.request-info {
  flex: 1;
  min-width: 0;
}

.request-type {
  font-weight: 600;
  margin-bottom: 0.25rem;
  font-size: 0.95rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.request-details {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

@media (min-width: 640px) {
  .request-details {
    flex-direction: row;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
  }
}

.request-service-provider {
  display: flex;
  align-items: center;
  overflow: hidden;
}

.provider-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.request-status {
  width: 100%;
}

@media (min-width: 640px) {
  .request-status {
    width: auto;
    min-width: 160px;
  }
}

.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
}

.status-pending {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.status-in-progress {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
  border: 1px solid rgba(59, 130, 246, 0.2);
}

.status-completed {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.request-progress {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
}

.progress-bar {
  flex: 1;
  min-width: 0;
  height: 0.375rem;
  background: rgba(100, 116, 139, 0.2);
  border-radius: 9999px;
  overflow: hidden;
}

@media (min-width: 640px) {
  .progress-bar {
    width: 100px;
    flex: none;
  }
}

.progress-fill {
  height: 100%;
  border-radius: 9999px;
  transition: width 0.3s ease;
}

.progress-pending {
  background: linear-gradient(90deg, #f59e0b, #fbbf24);
}

.progress-in-progress {
  background: linear-gradient(90deg, #3b82f6, #60a5fa);
}

.progress-completed {
  background: linear-gradient(90deg, #10b981, #34d399);
}

.progress-text {
  font-size: 0.75rem;
  color: #94a3b8;
  min-width: 2.5rem;
  text-align: right;
}

/* Colors Grid - Mobile First */
.colors-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.875rem;
  margin-bottom: 1.25rem;
}

@media (min-width: 640px) {
  .colors-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}

.color-item {
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid rgba(100, 116, 139, 0.1);
  transition: all 0.2s ease;
  min-width: 0;
}

.color-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.color-preview {
  position: relative;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (min-width: 768px) {
  .color-preview {
    height: 100px;
  }
}

.color-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.3) 100%);
}

.color-name {
  position: relative;
  z-index: 1;
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
  font-size: 0.875rem;
}

@media (min-width: 768px) {
  .color-name {
    font-size: 1rem;
  }
}

.color-details {
  padding: 0.75rem;
}

.color-codes {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

@media (min-width: 480px) {
  .color-codes {
    flex-direction: row;
    justify-content: space-between;
  }
}

.color-code {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  min-width: 0;
}

.code-label {
  font-size: 0.7rem;
  color: #94a3b8;
}

.code-value {
  font-size: 0.75rem;
  font-family: monospace;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (min-width: 768px) {
  .code-value {
    font-size: 0.8rem;
  }
}

.color-meta {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.7rem;
  color: #94a3b8;
}

@media (min-width: 480px) {
  .color-meta {
    flex-direction: row;
    justify-content: space-between;
    font-size: 0.75rem;
  }
}

/* Service Providers - Mobile Optimized */
.providers-list {
  margin-bottom: 1rem;
}

.provider-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.5);
  border-radius: 0.75rem;
  margin-bottom: 0.75rem;
  border: 1px solid rgba(100, 116, 139, 0.1);
  transition: all 0.2s ease;
}

@media (min-width: 640px) {
  .provider-item {
    flex-direction: row;
    text-align: left;
    align-items: center;
    gap: 1rem;
  }
}

.provider-item:hover {
  background: rgba(15, 23, 42, 0.8);
  border-color: rgba(56, 189, 248, 0.2);
}

.provider-avatar {
  position: relative;
  flex-shrink: 0;
}

.avatar-circle {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.75rem;
}

@media (min-width: 768px) {
  .avatar-circle {
    width: 3rem;
    height: 3rem;
    font-size: 0.875rem;
  }
}

.status-indicator {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 0.625rem;
  height: 0.625rem;
  border-radius: 50%;
  border: 2px solid #1e293b;
}

@media (min-width: 768px) {
  .status-indicator {
    width: 0.75rem;
    height: 0.75rem;
  }
}

.status-indicator.online {
  background: #10b981;
}

.status-indicator.offline {
  background: #94a3b8;
}

.provider-info {
  flex: 1;
  min-width: 0;
}

.provider-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
  font-size: 0.95rem;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (min-width: 768px) {
  .provider-name {
    font-size: 1rem;
  }
}

.provider-rating {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

@media (min-width: 640px) {
  .provider-rating {
    justify-content: flex-start;
  }
}

.stars {
  display: flex;
  gap: 0.125rem;
}

.star-icon {
  width: 0.875rem;
  height: 0.875rem;
}

@media (min-width: 768px) {
  .star-icon {
    width: 1rem;
    height: 1rem;
  }
}

.rating-text {
  font-size: 0.75rem;
  color: #94a3b8;
}

@media (min-width: 768px) {
  .rating-text {
    font-size: 0.875rem;
  }
}

.provider-specialty {
  font-size: 0.7rem;
  color: #94a3b8;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (min-width: 768px) {
  .provider-specialty {
    font-size: 0.75rem;
  }
}

.contact-btn {
  padding: 0.5rem;
  background: rgba(56, 189, 248, 0.1);
  border-radius: 0.5rem;
  border: 1px solid rgba(56, 189, 248, 0.2);
  color: #38bdf8;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.contact-btn:hover {
  background: rgba(56, 189, 248, 0.2);
  transform: scale(1.05);
}

.contact-icon {
  width: 1rem;
  height: 1rem;
}

/* Activity Table - Mobile Optimized */
.activity-table-container {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.activity-table-scroll {
  min-width: 600px;
}

.activity-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.875rem;
}

@media (min-width: 768px) {
  .activity-table {
    font-size: 0.9375rem;
  }
}

.activity-table th {
  text-align: left;
  padding: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: #94a3b8;
  border-bottom: 1px solid rgba(100, 116, 139, 0.2);
  background: rgba(15, 23, 42, 0.3);
  white-space: nowrap;
}

@media (min-width: 768px) {
  .activity-table th {
    padding: 0.75rem 1rem;
  }
}

.activity-column {
  min-width: 200px;
}

.project-column {
  min-width: 120px;
}

.date-column {
  min-width: 120px;
}

.status-column {
  min-width: 80px;
}

.activity-table td {
  padding: 0.75rem;
  border-bottom: 1px solid rgba(100, 116, 139, 0.1);
  vertical-align: middle;
}

@media (min-width: 768px) {
  .activity-table td {
    padding: 1rem;
  }
}

.activity-table tr:hover td {
  background: rgba(15, 23, 42, 0.3);
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 0;
}

.activity-icon {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

@media (min-width: 768px) {
  .activity-icon {
    width: 2rem;
    height: 2rem;
  }
}

.activity-icon-svg {
  width: 1rem;
  height: 1rem;
}

@media (min-width: 768px) {
  .activity-icon-svg {
    width: 1.25rem;
    height: 1.25rem;
  }
}

.icon-color {
  background: rgba(168, 85, 247, 0.1);
  color: #a855f7;
  border: 1px solid rgba(168, 85, 247, 0.2);
}

.icon-request {
  background: rgba(56, 189, 248, 0.1);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.2);
}

.icon-update {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.activity-description {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.activity-status {
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.7rem;
  font-weight: 600;
  white-space: nowrap;
}

@media (min-width: 768px) {
  .activity-status {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
  }
}

.status-completed {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.status-in-progress {
  background: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

.status-pending {
  background: rgba(245, 158, 11, 0.1);
  color: #f59e0b;
}

/* View All Button */
.view-all-btn {
  width: 100%;
  padding: 0.75rem;
  background: rgba(56, 189, 248, 0.1);
  border: 1px solid rgba(56, 189, 248, 0.2);
  border-radius: 0.75rem;
  color: #38bdf8;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  gap: 0.5rem;
}

.view-all-btn:hover {
  background: rgba(56, 189, 248, 0.2);
  transform: translateY(-1px);
}

.view-all-text {
  font-size: 0.875rem;
}

@media (min-width: 768px) {
  .view-all-text {
    font-size: 1rem;
  }
}

.view-all-icon {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

/* Quick Actions - Mobile First */
.quick-actions {
  margin-top: 1.5rem;
}

@media (min-width: 768px) {
  .quick-actions {
    margin-top: 2rem;
  }
}

.quick-actions-title {
  font-size: 1.125rem;
  font-weight: 600;
  margin-bottom: 1rem;
  color: #e2e8f0;
}

@media (min-width: 768px) {
  .quick-actions-title {
    font-size: 1.25rem;
    margin-bottom: 1.5rem;
  }
}

.actions-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.875rem;
}

@media (min-width: 640px) {
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
}

@media (min-width: 1024px) {
  .actions-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.action-btn {
  padding: 1.25rem;
  background: rgba(30, 41, 59, 0.6);
  border-radius: 0.875rem;
  border: 1px solid rgba(100, 116, 139, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  min-height: 110px;
}

@media (min-width: 768px) {
  .action-btn {
    padding: 1.5rem;
    border-radius: 1rem;
    min-height: 120px;
  }
}

.action-btn:hover {
  border-color: rgba(56, 189, 248, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.action-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

@media (min-width: 768px) {
  .action-icon {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
  }
}

.action-icon-svg {
  width: 1.25rem;
  height: 1.25rem;
}

@media (min-width: 768px) {
  .action-icon-svg {
    width: 1.5rem;
    height: 1.5rem;
  }
}

.action-text {
  font-weight: 500;
  color: #e2e8f0;
  font-size: 0.875rem;
  text-align: center;
  line-height: 1.3;
}

@media (min-width: 768px) {
  .action-text {
    font-size: 1rem;
  }
}

/* Additional Mobile Optimizations */
@media (max-width: 639px) {
  .client-dashboard {
    padding: 0.75rem;
  }
  
  .hero-title {
    font-size: 1.5rem;
  }
  
  .hero-subtitle {
    font-size: 0.9375rem;
  }
  
  .stat-bubble {
    padding: 0.5rem 1rem;
  }
  
  .color-wheel-animation {
    width: 120px;
    height: 120px;
  }
  
  .dashboard-card {
    padding: 1rem;
  }
  
  .card-title {
    font-size: 1rem;
  }
  
  .request-type {
    font-size: 0.9rem;
  }
  
  .color-name {
    font-size: 0.8125rem;
  }
  
  .action-btn {
    padding: 1rem;
    min-height: 100px;
  }
  
  .action-icon {
    width: 2.5rem;
    height: 2.5rem;
  }
  
  .action-icon-svg {
    width: 1rem;
    height: 1rem;
  }
  
  .action-text {
    font-size: 0.8125rem;
  }
}

/* Prevent text overflow on very small screens */
@media (max-width: 374px) {
  .hero-title {
    font-size: 1.375rem;
  }
  
  .stat-text {
    font-size: 0.75rem;
  }
  
  .card-title {
    font-size: 0.9375rem;
  }
  
  .request-type {
    font-size: 0.85rem;
  }
  
  .action-text {
    font-size: 0.75rem;
  }
}
</style>