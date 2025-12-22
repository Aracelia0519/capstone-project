<template>
  <div class="recommendations-container">
    <!-- Header Section -->
    <div class="header-section">
      <div class="header-content">
        <h1 class="title">
          <svg class="title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
          Color Recommendations
        </h1>
        <p class="subtitle">Personalized paint suggestions based on your preferences and Cavite trends</p>
      </div>
      <div class="header-actions">
        <button class="refresh-btn" @click="refreshRecommendations">
          <svg class="refresh-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </button>
        <div class="info-chip">
          <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Powered by DSS
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-wrapper bg-gradient-to-r from-blue-500 to-cyan-500">
          <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.totalSuggestions }}</div>
          <div class="stat-label">Total Suggestions</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon-wrapper bg-gradient-to-r from-purple-500 to-pink-500">
          <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.topRated }}</div>
          <div class="stat-label">Top Rated</div>
        </div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon-wrapper bg-gradient-to-r from-amber-500 to-yellow-500">
          <svg class="stat-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-value">{{ stats.recentTrends }}</div>
          <div class="stat-label">Recent Trends</div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
      <!-- Suggested Colors Section -->
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <svg class="section-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            Suggested Colors For You
          </div>
          <div class="section-badge">Personalized</div>
        </div>
        
        <div class="colors-grid">
          <div v-for="color in suggestedColors" :key="color.id" class="color-card" @click="previewColor(color)">
            <div class="color-preview" :style="{ backgroundColor: color.hex }">
              <div class="color-overlay">
                <svg class="preview-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </div>
            </div>
            <div class="color-details">
              <div class="color-name">{{ color.name }}</div>
              <div class="color-code">{{ color.hex }}</div>
              <div class="color-match">
                <div class="match-bar">
                  <div class="match-fill" :style="{ width: color.match + '%' }"></div>
                </div>
                <span class="match-text">{{ color.match }}% match</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Popular Colors in Cavite Section -->
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <svg class="section-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Popular Colors in Cavite
          </div>
          <div class="section-badge trending">Trending</div>
        </div>
        
        <div class="popular-list">
          <div v-for="(popular, index) in popularColors" :key="popular.id" class="popular-item">
            <div class="popular-rank">{{ index + 1 }}</div>
            <div class="color-chip" :style="{ backgroundColor: popular.hex }"></div>
            <div class="popular-details">
              <div class="popular-name">{{ popular.name }}</div>
              <div class="popular-usage">
                <svg class="usage-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ popular.usage }} projects
              </div>
            </div>
            <div class="popular-trend">
              <svg v-if="popular.trend === 'up'" class="trend-icon up" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
              </svg>
              <svg v-else class="trend-icon down" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
              {{ popular.change }}%
            </div>
          </div>
        </div>
      </div>

      <!-- Trend-Based Recommendations -->
      <div class="section-card">
        <div class="section-header">
          <div class="section-title">
            <svg class="section-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Trend-Based Recommendations
          </div>
          <div class="section-badge ai">AI Generated</div>
        </div>
        
        <div class="trends-grid">
          <div v-for="trend in trendRecommendations" :key="trend.id" class="trend-card">
            <div class="trend-icon-wrapper" :class="trend.iconBg">
              <svg class="trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="getTrendIcon(trend.type)"></svg>
            </div>
            <div class="trend-content">
              <h4 class="trend-title">{{ trend.title }}</h4>
              <p class="trend-description">{{ trend.description }}</p>
              <div class="trend-colors">
                <div v-for="color in trend.colors" :key="color" class="trend-color-chip" :style="{ backgroundColor: color }"></div>
              </div>
            </div>
            <button class="trend-action-btn" @click="applyTrend(trend)">
              <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Apply
            </button>
          </div>
        </div>
      </div>

      <!-- Filter Panel -->
      <div class="section-card filter-card">
        <div class="section-header">
          <div class="section-title">
            <svg class="section-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filter Recommendations
          </div>
        </div>
        
        <div class="filter-options">
          <div class="filter-group">
            <label class="filter-label">Room Type</label>
            <div class="filter-chips">
              <span v-for="room in roomTypes" :key="room" 
                    class="filter-chip" 
                    :class="{ active: selectedRoom === room }"
                    @click="selectedRoom = room">
                {{ room }}
              </span>
            </div>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">Color Temperature</label>
            <div class="filter-slider">
              <div class="slider-labels">
                <span>Cool</span>
                <span>Neutral</span>
                <span>Warm</span>
              </div>
              <input type="range" v-model="temperature" min="0" max="100" class="temperature-slider">
              <div class="slider-ticks">
                <span v-for="n in 5" :key="n" class="slider-tick"></span>
              </div>
            </div>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">Brightness Level</label>
            <div class="brightness-control">
              <button class="brightness-btn" @click="decreaseBrightness">
                <svg class="brightness-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </button>
              <div class="brightness-levels">
                <span v-for="level in brightnessLevels" :key="level" 
                      class="brightness-level" 
                      :class="{ active: brightness >= level }">
                </span>
              </div>
              <button class="brightness-btn" @click="increaseBrightness">
                <svg class="brightness-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
          </div>
        </div>
        
        <button class="apply-filters-btn" @click="applyFilters">
          <svg class="apply-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Apply Filters
        </button>
      </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="quick-actions">
      <button class="quick-action-btn" @click="saveToFavorites">
        <svg class="action-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        Save Favorites
      </button>
      <button class="quick-action-btn primary" @click="shareRecommendations">
        <svg class="action-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
        Share
      </button>
      <button class="quick-action-btn" @click="exportRecommendations">
        <svg class="action-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
        </svg>
        Export
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClientRecommendations',
  data() {
    return {
      stats: {
        totalSuggestions: 24,
        topRated: 8,
        recentTrends: 12
      },
      suggestedColors: [
        { id: 1, name: 'Ocean Breeze', hex: '#38bdf8', match: 92 },
        { id: 2, name: 'Misty Morning', hex: '#cbd5e1', match: 87 },
        { id: 3, name: 'Golden Hour', hex: '#fbbf24', match: 85 },
        { id: 4, name: 'Forest Green', hex: '#10b981', match: 78 },
        { id: 5, name: 'Lavender Dream', hex: '#a78bfa', match: 76 },
        { id: 6, name: 'Coral Blush', hex: '#fb7185', match: 72 }
      ],
      popularColors: [
        { id: 1, name: 'Cavite Blue', hex: '#0ea5e9', usage: 245, trend: 'up', change: 12 },
        { id: 2, name: 'Sandy Beige', hex: '#d1d5db', usage: 198, trend: 'up', change: 8 },
        { id: 3, name: 'Emerald Bay', hex: '#059669', usage: 176, trend: 'up', change: 15 },
        { id: 4, name: 'Sunset Orange', hex: '#f97316', usage: 154, trend: 'down', change: 3 },
        { id: 5, name: 'Misty Gray', hex: '#6b7280', usage: 143, trend: 'up', change: 5 }
      ],
      trendRecommendations: [
        { 
          id: 1, 
          type: 'coastal', 
          title: 'Coastal Vibes', 
          description: 'Perfect for beach-inspired interiors',
          colors: ['#38bdf8', '#22d3ee', '#a5f3fc'],
          iconBg: 'bg-gradient-to-r from-blue-500 to-cyan-500'
        },
        { 
          id: 2, 
          type: 'modern', 
          title: 'Modern Minimalist', 
          description: 'Clean, sleek contemporary palette',
          colors: ['#1e293b', '#475569', '#cbd5e1'],
          iconBg: 'bg-gradient-to-r from-slate-700 to-slate-500'
        },
        { 
          id: 3, 
          type: 'earthy', 
          title: 'Earthy Tones', 
          description: 'Natural, organic color scheme',
          colors: ['#854d0e', '#a16207', '#fef3c7'],
          iconBg: 'bg-gradient-to-r from-amber-700 to-yellow-500'
        }
      ],
      roomTypes: ['Living Room', 'Bedroom', 'Kitchen', 'Bathroom', 'Office'],
      selectedRoom: 'Living Room',
      temperature: 50,
      brightness: 3,
      brightnessLevels: [1, 2, 3, 4, 5]
    }
  },
  methods: {
    refreshRecommendations() {
      // Simulate API call
      console.log('Refreshing recommendations...')
      // In real app, this would fetch new data from API
    },
    
    previewColor(color) {
      console.log('Previewing color:', color)
      // Navigate to color preview page with Unity integration
      this.$router.push({ 
        path: '/Clients/colorPreview',
        query: { color: color.hex, name: color.name }
      })
    },
    
    applyTrend(trend) {
      console.log('Applying trend:', trend)
      // Apply trend colors to Unity module
    },
    
    getTrendIcon(type) {
      const icons = {
        coastal: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />',
        modern: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
        earthy: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />'
      }
      return icons[type] || icons.modern
    },
    
    decreaseBrightness() {
      if (this.brightness > 1) this.brightness--
    },
    
    increaseBrightness() {
      if (this.brightness < 5) this.brightness++
    },
    
    applyFilters() {
      console.log('Applying filters:', {
        room: this.selectedRoom,
        temperature: this.temperature,
        brightness: this.brightness
      })
      // Apply filters and refresh recommendations
    },
    
    saveToFavorites() {
      console.log('Saving to favorites...')
    },
    
    shareRecommendations() {
      console.log('Sharing recommendations...')
    },
    
    exportRecommendations() {
      console.log('Exporting recommendations...')
    }
  }
}
</script>

<style scoped>
.recommendations-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  padding: 1.5rem;
  overflow-x: hidden;
}

/* Header Section */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-content {
  flex: 1;
  min-width: 300px;
}

.title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.875rem;
  font-weight: 700;
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

.title-icon {
  width: 2rem;
  height: 2rem;
  flex-shrink: 0;
}

.subtitle {
  color: #94a3b8;
  font-size: 0.875rem;
  max-width: 600px;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: rgba(56, 189, 248, 0.1);
  border: 1px solid rgba(56, 189, 248, 0.3);
  border-radius: 0.75rem;
  color: #38bdf8;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.refresh-btn:hover {
  background: rgba(56, 189, 248, 0.2);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(56, 189, 248, 0.2);
}

.refresh-icon {
  width: 1rem;
  height: 1rem;
}

.info-chip {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  background: rgba(139, 92, 246, 0.1);
  border: 1px solid rgba(139, 92, 246, 0.3);
  border-radius: 9999px;
  color: #8b5cf6;
  font-size: 0.75rem;
  font-weight: 500;
}

.info-icon {
  width: 0.875rem;
  height: 0.875rem;
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 1rem;
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: rgba(56, 189, 248, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
}

.stat-icon-wrapper {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 0.875rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon {
  width: 1.5rem;
  height: 1.5rem;
  color: white;
}

.stat-content {
  flex: 1;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #e2e8f0;
  margin-bottom: 0.125rem;
}

.stat-label {
  font-size: 0.75rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Main Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.section-card {
  background: rgba(30, 41, 59, 0.7);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 1rem;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
}

.section-card:hover {
  border-color: rgba(56, 189, 248, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #e2e8f0;
}

.section-title-icon {
  width: 1.25rem;
  height: 1.25rem;
  flex-shrink: 0;
}

.section-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.section-badge:not(.trending):not(.ai) {
  background: rgba(56, 189, 248, 0.2);
  color: #38bdf8;
  border: 1px solid rgba(56, 189, 248, 0.3);
}

.section-badge.trending {
  background: rgba(236, 72, 153, 0.2);
  color: #ec4899;
  border: 1px solid rgba(236, 72, 153, 0.3);
}

.section-badge.ai {
  background: rgba(139, 92, 246, 0.2);
  color: #8b5cf6;
  border: 1px solid rgba(139, 92, 246, 0.3);
}

/* Colors Grid */
.colors-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
}

.color-card {
  cursor: pointer;
  border-radius: 0.75rem;
  overflow: hidden;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  transition: all 0.2s ease;
}

.color-card:hover {
  transform: translateY(-2px);
  border-color: rgba(56, 189, 248, 0.4);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.color-preview {
  height: 120px;
  position: relative;
}

.color-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.color-card:hover .color-overlay {
  opacity: 1;
}

.preview-icon {
  width: 2rem;
  height: 2rem;
  color: white;
}

.color-details {
  padding: 1rem;
}

.color-name {
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.25rem;
}

.color-code {
  font-family: 'Monaco', 'Menlo', monospace;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.75rem;
}

.color-match {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.match-bar {
  flex: 1;
  height: 4px;
  background: rgba(71, 85, 105, 0.5);
  border-radius: 2px;
  overflow: hidden;
}

.match-fill {
  height: 100%;
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
  border-radius: 2px;
}

.match-text {
  font-size: 0.75rem;
  color: #94a3b8;
  min-width: 60px;
}

/* Popular List */
.popular-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.popular-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}

.popular-item:hover {
  transform: translateX(4px);
  border-color: rgba(236, 72, 153, 0.3);
  background: rgba(236, 72, 153, 0.05);
}

.popular-rank {
  width: 1.75rem;
  height: 1.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(30, 41, 59, 0.8);
  border-radius: 9999px;
  font-weight: 700;
  color: #e2e8f0;
  font-size: 0.75rem;
  flex-shrink: 0;
}

.color-chip {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.5rem;
  border: 2px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
}

.popular-details {
  flex: 1;
}

.popular-name {
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.125rem;
}

.popular-usage {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

.usage-icon {
  width: 0.75rem;
  height: 0.75rem;
}

.popular-trend {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #10b981;
}

.popular-trend .down {
  color: #ef4444;
}

.trend-icon {
  width: 1rem;
  height: 1rem;
}

.trend-icon.up {
  color: #10b981;
}

.trend-icon.down {
  color: #ef4444;
}

/* Trends Grid */
.trends-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.trend-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}

.trend-card:hover {
  transform: translateX(4px);
  border-color: rgba(139, 92, 246, 0.3);
  background: rgba(139, 92, 246, 0.05);
}

.trend-icon-wrapper {
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.trend-icon {
  width: 1.25rem;
  height: 1.25rem;
  color: white;
}

.trend-content {
  flex: 1;
}

.trend-title {
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.25rem;
}

.trend-description {
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.5rem;
}

.trend-colors {
  display: flex;
  gap: 0.375rem;
}

.trend-color-chip {
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 0.25rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.trend-action-btn {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  background: rgba(56, 189, 248, 0.1);
  border: 1px solid rgba(56, 189, 248, 0.3);
  border-radius: 0.5rem;
  color: #38bdf8;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.trend-action-btn:hover {
  background: rgba(56, 189, 248, 0.2);
  transform: translateY(-1px);
}

.action-icon {
  width: 0.875rem;
  height: 0.875rem;
}

/* Filter Card */
.filter-card {
  grid-column: 1 / -1;
}

.filter-options {
  display: grid;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.filter-label {
  font-weight: 600;
  color: #e2e8f0;
  font-size: 0.875rem;
}

.filter-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.filter-chip {
  padding: 0.5rem 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 9999px;
  font-size: 0.75rem;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-chip:hover {
  border-color: rgba(56, 189, 248, 0.3);
  color: #38bdf8;
}

.filter-chip.active {
  background: rgba(56, 189, 248, 0.2);
  border-color: rgba(56, 189, 248, 0.4);
  color: #38bdf8;
  font-weight: 500;
}

.filter-slider {
  padding: 0.5rem 0;
}

.slider-labels {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-bottom: 0.5rem;
}

.temperature-slider {
  width: 100%;
  height: 6px;
  -webkit-appearance: none;
  background: rgba(71, 85, 105, 0.5);
  border-radius: 3px;
  outline: none;
}

.temperature-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 18px;
  height: 18px;
  background: #38bdf8;
  border-radius: 50%;
  cursor: pointer;
  border: 2px solid #0f172a;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.slider-ticks {
  display: flex;
  justify-content: space-between;
  margin-top: 0.5rem;
  padding: 0 9px;
}

.slider-tick {
  width: 2px;
  height: 6px;
  background: rgba(71, 85, 105, 0.5);
}

.brightness-control {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.brightness-btn {
  padding: 0.5rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 0.5rem;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.brightness-btn:hover {
  border-color: rgba(56, 189, 248, 0.3);
  color: #38bdf8;
}

.brightness-icon {
  width: 1rem;
  height: 1rem;
}

.brightness-levels {
  display: flex;
  gap: 0.25rem;
  flex: 1;
}

.brightness-level {
  flex: 1;
  height: 8px;
  background: rgba(71, 85, 105, 0.5);
  border-radius: 2px;
  transition: all 0.2s ease;
}

.brightness-level.active {
  background: linear-gradient(90deg, #fbbf24, #f59e0b);
}

.apply-filters-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.875rem;
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
  border: none;
  border-radius: 0.75rem;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.apply-filters-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(56, 189, 248, 0.3);
}

.apply-icon {
  width: 1rem;
  height: 1rem;
}

/* Quick Actions Bar */
.quick-actions {
  position: sticky;
  bottom: 1.5rem;
  display: flex;
  justify-content: center;
  gap: 1rem;
  margin-top: 2rem;
}

.quick-action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.5rem;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 0.75rem;
  color: #e2e8f0;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
}

.quick-action-btn:hover {
  transform: translateY(-2px);
  border-color: rgba(56, 189, 248, 0.3);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.quick-action-btn.primary {
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
  color: white;
  border: none;
}

.quick-action-btn.primary:hover {
  box-shadow: 0 8px 24px rgba(56, 189, 248, 0.3);
}

.action-btn-icon {
  width: 1rem;
  height: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .recommendations-container {
    padding: 1rem;
  }
  
  .header-section {
    flex-direction: column;
  }
  
  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }
  
  .content-grid {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .colors-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .quick-actions {
    flex-direction: column;
  }
  
  .quick-action-btn {
    width: 100%;
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .colors-grid {
    grid-template-columns: 1fr;
  }
  
  .popular-item {
    flex-wrap: wrap;
  }
  
  .popular-trend {
    margin-left: auto;
  }
}
</style>