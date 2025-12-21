<template>
  <div class="color-history-container">
    <!-- Header Section -->
    <div class="header-section">
      <div class="header-content">
        <div class="header-left">
          <div class="header-icon">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h1 class="page-title">Color History</h1>
            <p class="page-subtitle">Reuse previous color mixes</p>
          </div>
        </div>
        
        <div class="header-actions">
          <!-- Search Bar -->
          <div class="search-container">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search by client or color..."
              class="search-input"
            >
          </div>
          
          <!-- Filter Button -->
          <button @click="toggleFilter" class="filter-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>Filter</span>
            <span v-if="activeFilterCount > 0" class="filter-badge">{{ activeFilterCount }}</span>
          </button>
        </div>
      </div>
      
      <!-- Filter Panel -->
      <div v-if="showFilter" class="filter-panel">
        <div class="filter-grid">
          <div class="filter-group">
            <label class="filter-label">Sort by</label>
            <select v-model="sortBy" class="filter-select">
              <option value="newest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="client">Client Name</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">Date Range</label>
            <select v-model="dateRange" class="filter-select">
              <option value="all">All Time</option>
              <option value="week">Last Week</option>
              <option value="month">Last Month</option>
              <option value="quarter">Last 3 Months</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">Client</label>
            <select v-model="clientFilter" class="filter-select">
              <option value="all">All Clients</option>
              <option v-for="client in uniqueClients" :key="client" :value="client">{{ client }}</option>
            </select>
          </div>
        </div>
        
        <div class="filter-actions">
          <button @click="clearFilters" class="clear-filters-btn">
            Clear Filters
          </button>
          <button @click="applyFilters" class="apply-filters-btn">
            Apply Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Empty State -->
      <div v-if="filteredColors.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="empty-title">No color mixes found</h3>
        <p class="empty-description">
          {{ searchQuery ? 'Try adjusting your search or filters' : 'Start creating colors in the Color Mixer' }}
        </p>
        <button v-if="!searchQuery" class="empty-action-btn">
          <router-link to="/service-provider/color-mixer" class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Go to Color Mixer
          </router-link>
        </button>
      </div>

      <!-- Color Grid -->
      <div v-else class="color-grid">
        <div 
          v-for="color in paginatedColors"
          :key="color.id"
          class="color-card"
          :style="{ '--card-color': color.hex }"
        >
          <!-- Card Header -->
          <div class="card-header">
            <div class="client-info">
              <div class="client-avatar">
                {{ color.client.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="client-name">{{ color.client }}</h3>
                <p class="color-name">{{ color.name }}</p>
              </div>
            </div>
            <div class="card-actions">
              <button @click="showColorActions(color)" class="action-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
            </div>
          </div>

          <!-- Color Preview -->
          <div class="color-preview-section">
            <div class="color-preview" :style="{ backgroundColor: color.hex }"></div>
            <div class="color-values">
              <div class="color-value-group">
                <span class="value-label">HEX</span>
                <div class="value-display">
                  <span class="value-text">{{ color.hex }}</span>
                  <button @click="copyToClipboard(color.hex)" class="copy-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="color-value-group">
                <span class="value-label">RGB</span>
                <div class="value-display">
                  <span class="value-text">{{ color.rgb }}</span>
                  <button @click="copyToClipboard(color.rgb)" class="copy-btn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Color Metadata -->
          <div class="color-meta">
            <div class="meta-item">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span>{{ formatDate(color.created) }}</span>
            </div>
            <div class="meta-item">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>{{ color.timeAgo }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="action-buttons">
            <button @click="reapplyColor(color)" class="action-btn-primary">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Re-apply Color
            </button>
            <button @click="assignToJob(color)" class="action-btn-secondary">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Assign to Job
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredColors.length > 0" class="pagination">
        <div class="pagination-info">
          Showing {{ startIndex + 1 }}-{{ endIndex }} of {{ filteredColors.length }} colors
        </div>
        <div class="pagination-controls">
          <button 
            @click="prevPage" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          
          <div class="page-numbers">
            <button 
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="['page-btn', { 'active': page === currentPage }]"
            >
              {{ page }}
            </button>
          </div>
          
          <button 
            @click="nextPage" 
            :disabled="currentPage === totalPages"
            class="pagination-btn"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Color Actions Dropdown -->
    <div 
      v-if="showActionsFor"
      class="actions-dropdown"
      :style="{ top: actionsPosition.top + 'px', left: actionsPosition.left + 'px' }"
    >
      <button @click="editColor(showActionsFor)" class="dropdown-item">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Edit Details
      </button>
      <button @click="duplicateColor(showActionsFor)" class="dropdown-item">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        Duplicate
      </button>
      <button @click="shareColor(showActionsFor)" class="dropdown-item">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
        Share
      </button>
      <div class="dropdown-divider"></div>
      <button @click="deleteColor(showActionsFor)" class="dropdown-item text-red-400 hover:text-red-300">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Delete
      </button>
    </div>

    <!-- Notification -->
    <div v-if="showNotification" class="notification">
      <div class="notification-content">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ notificationMessage }}</span>
      </div>
    </div>

    <!-- Modal Backdrop -->
    <div v-if="showActionsFor" class="modal-backdrop" @click="showActionsFor = null"></div>
  </div>
</template>

<script>
export default {
  name: 'ColorHistory',
  data() {
    return {
      searchQuery: '',
      showFilter: false,
      sortBy: 'newest',
      dateRange: 'all',
      clientFilter: 'all',
      currentPage: 1,
      itemsPerPage: 8,
      showActionsFor: null,
      actionsPosition: { top: 0, left: 0 },
      showNotification: false,
      notificationMessage: '',
      
      // Sample data - in a real app, this would come from an API
      colors: [
        {
          id: 1,
          client: 'John Smith',
          name: 'Ocean Breeze',
          hex: '#3B82F6',
          rgb: '59, 130, 246',
          created: '2024-01-15',
          timeAgo: '2 days ago'
        },
        {
          id: 2,
          client: 'Sarah Johnson',
          name: 'Forest Green',
          hex: '#10B981',
          rgb: '16, 185, 129',
          created: '2024-01-14',
          timeAgo: '3 days ago'
        },
        {
          id: 3,
          client: 'Mike Wilson',
          name: 'Sunset Orange',
          hex: '#F59E0B',
          rgb: '245, 158, 11',
          created: '2024-01-13',
          timeAgo: '4 days ago'
        },
        {
          id: 4,
          client: 'Emma Davis',
          name: 'Royal Purple',
          hex: '#8B5CF6',
          rgb: '139, 92, 246',
          created: '2024-01-12',
          timeAgo: '5 days ago'
        },
        {
          id: 5,
          client: 'Robert Brown',
          name: 'Crimson Red',
          hex: '#EF4444',
          rgb: '239, 68, 68',
          created: '2024-01-11',
          timeAgo: '6 days ago'
        },
        {
          id: 6,
          client: 'Lisa Miller',
          name: 'Sky Blue',
          hex: '#0EA5E9',
          rgb: '14, 165, 233',
          created: '2024-01-10',
          timeAgo: '1 week ago'
        },
        {
          id: 7,
          client: 'David Wilson',
          name: 'Emerald',
          hex: '#10B981',
          rgb: '16, 185, 129',
          created: '2024-01-09',
          timeAgo: '1 week ago'
        },
        {
          id: 8,
          client: 'Anna Taylor',
          name: 'Golden Yellow',
          hex: '#FBBF24',
          rgb: '251, 191, 36',
          created: '2024-01-08',
          timeAgo: '1 week ago'
        },
        {
          id: 9,
          client: 'John Smith',
          name: 'Midnight Blue',
          hex: '#1E40AF',
          rgb: '30, 64, 175',
          created: '2024-01-07',
          timeAgo: '2 weeks ago'
        },
        {
          id: 10,
          client: 'Mike Wilson',
          name: 'Rose Pink',
          hex: '#F472B6',
          rgb: '244, 114, 182',
          created: '2024-01-06',
          timeAgo: '2 weeks ago'
        },
        {
          id: 11,
          client: 'Sarah Johnson',
          name: 'Teal',
          hex: '#14B8A6',
          rgb: '20, 184, 166',
          created: '2024-01-05',
          timeAgo: '2 weeks ago'
        },
        {
          id: 12,
          client: 'Emma Davis',
          name: 'Slate Gray',
          hex: '#64748B',
          rgb: '100, 116, 139',
          created: '2024-01-04',
          timeAgo: '2 weeks ago'
        }
      ]
    }
  },
  computed: {
    uniqueClients() {
      return [...new Set(this.colors.map(color => color.client))].sort()
    },
    
    activeFilterCount() {
      let count = 0
      if (this.sortBy !== 'newest') count++
      if (this.dateRange !== 'all') count++
      if (this.clientFilter !== 'all') count++
      return count
    },
    
    filteredColors() {
      let filtered = [...this.colors]
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(color => 
          color.client.toLowerCase().includes(query) ||
          color.name.toLowerCase().includes(query) ||
          color.hex.toLowerCase().includes(query)
        )
      }
      
      // Apply client filter
      if (this.clientFilter !== 'all') {
        filtered = filtered.filter(color => color.client === this.clientFilter)
      }
      
      // Apply date range filter (simplified for demo)
      if (this.dateRange !== 'all') {
        const now = new Date()
        const daysAgo = this.dateRange === 'week' ? 7 : 
                      this.dateRange === 'month' ? 30 : 90
        
        filtered = filtered.filter(color => {
          const colorDate = new Date(color.created)
          const diffDays = Math.floor((now - colorDate) / (1000 * 60 * 60 * 24))
          return diffDays <= daysAgo
        })
      }
      
      // Apply sorting
      switch (this.sortBy) {
        case 'oldest':
          filtered.sort((a, b) => new Date(a.created) - new Date(b.created))
          break
        case 'client':
          filtered.sort((a, b) => a.client.localeCompare(b.client))
          break
        default: // newest
          filtered.sort((a, b) => new Date(b.created) - new Date(a.created))
      }
      
      return filtered
    },
    
    totalPages() {
      return Math.ceil(this.filteredColors.length / this.itemsPerPage)
    },
    
    paginatedColors() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredColors.slice(start, end)
    },
    
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage
    },
    
    endIndex() {
      return Math.min(this.startIndex + this.itemsPerPage, this.filteredColors.length)
    },
    
    visiblePages() {
      const pages = []
      const maxVisible = 5
      let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2))
      let end = Math.min(this.totalPages, start + maxVisible - 1)
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    }
  },
  watch: {
    searchQuery() {
      this.currentPage = 1
    },
    dateRange() {
      this.currentPage = 1
    },
    clientFilter() {
      this.currentPage = 1
    }
  },
  methods: {
    toggleFilter() {
      this.showFilter = !this.showFilter
    },
    
    applyFilters() {
      this.showFilter = false
    },
    
    clearFilters() {
      this.sortBy = 'newest'
      this.dateRange = 'all'
      this.clientFilter = 'all'
      this.searchQuery = ''
      this.showFilter = false
    },
    
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },
    
    copyToClipboard(text) {
      navigator.clipboard.writeText(text)
      this.showNotificationMessage(`Copied ${text} to clipboard`)
    },
    
    reapplyColor(color) {
      // In a real app, this would open the color mixer with this color
      this.showNotificationMessage(`Applying ${color.name} to mixer...`)
    },
    
    assignToJob(color) {
      // In a real app, this would open a job assignment modal
      this.showNotificationMessage(`Assigning ${color.name} to new job...`)
    },
    
    showColorActions(color, event) {
      if (event) {
        this.actionsPosition = {
          top: event.clientY,
          left: event.clientX
        }
      }
      this.showActionsFor = color
    },
    
    editColor(color) {
      this.showNotificationMessage(`Editing ${color.name}...`)
      this.showActionsFor = null
    },
    
    duplicateColor(color) {
      this.showNotificationMessage(`Duplicating ${color.name}...`)
      this.showActionsFor = null
    },
    
    shareColor(color) {
      this.showNotificationMessage(`Sharing ${color.name}...`)
      this.showActionsFor = null
    },
    
    deleteColor(color) {
      if (confirm(`Are you sure you want to delete ${color.name}?`)) {
        const index = this.colors.findIndex(c => c.id === color.id)
        if (index > -1) {
          this.colors.splice(index, 1)
          this.showNotificationMessage(`Deleted ${color.name}`)
        }
      }
      this.showActionsFor = null
    },
    
    showNotificationMessage(message) {
      this.notificationMessage = message
      this.showNotification = true
      setTimeout(() => {
        this.showNotification = false
      }, 3000)
    },
    
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--
        this.scrollToTop()
      }
    },
    
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++
        this.scrollToTop()
      }
    },
    
    goToPage(page) {
      this.currentPage = page
      this.scrollToTop()
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  },
  mounted() {
    // Close actions dropdown on click outside
    document.addEventListener('click', (e) => {
      if (this.showActionsFor && !e.target.closest('.actions-dropdown') && !e.target.closest('.card-actions')) {
        this.showActionsFor = null
      }
    })
  }
}
</script>

<style scoped>
.color-history-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  padding: 2rem;
}

/* Header Styles */
.header-section {
  margin-bottom: 2rem;
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.header-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: white;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  color: #94a3b8;
  font-size: 0.875rem;
}

.header-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.search-container {
  flex: 1;
  min-width: 250px;
  position: relative;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1.25rem;
  height: 1.25rem;
  color: #64748b;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  border-radius: 10px;
  color: white;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  border-radius: 10px;
  color: #cbd5e0;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.filter-btn:hover {
  background: rgba(51, 65, 85, 0.8);
  border-color: #475569;
}

.filter-badge {
  background: #8b5cf6;
  color: white;
  font-size: 0.75rem;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  margin-left: 0.25rem;
}

/* Filter Panel */
.filter-panel {
  margin-top: 1.5rem;
  padding: 1.5rem;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  border-radius: 12px;
  backdrop-filter: blur(10px);
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.filter-select {
  padding: 0.625rem 0.75rem;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid #475569;
  border-radius: 8px;
  color: white;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.filter-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.clear-filters-btn {
  padding: 0.625rem 1.25rem;
  background: transparent;
  border: 1px solid #475569;
  border-radius: 8px;
  color: #94a3b8;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.clear-filters-btn:hover {
  border-color: #64748b;
  color: #cbd5e0;
}

.apply-filters-btn {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.apply-filters-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

/* Main Content */
.main-content {
  max-width: 1400px;
  margin: 0 auto;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto 1.5rem;
  background: rgba(30, 41, 59, 0.8);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
}

.empty-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: white;
  margin-bottom: 0.5rem;
}

.empty-description {
  color: #94a3b8;
  margin-bottom: 1.5rem;
}

.empty-action-btn {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  transition: all 0.2s ease;
}

.empty-action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

/* Color Grid */
.color-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.color-card {
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  border-radius: 16px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.color-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--card-color, #8b5cf6), transparent);
  border-radius: 16px 16px 0 0;
}

.color-card:hover {
  transform: translateY(-4px);
  border-color: #475569;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.5rem;
}

.client-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.client-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.client-name {
  font-weight: 600;
  color: white;
  margin-bottom: 0.125rem;
}

.color-name {
  font-size: 0.75rem;
  color: #94a3b8;
}

.card-actions {
  position: relative;
}

.action-btn {
  padding: 0.5rem;
  background: transparent;
  border: none;
  color: #64748b;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #cbd5e0;
}

/* Color Preview */
.color-preview-section {
  margin-bottom: 1.5rem;
}

.color-preview {
  height: 120px;
  border-radius: 12px;
  margin-bottom: 1rem;
  border: 1px solid #334155;
  transition: transform 0.3s ease;
}

.color-card:hover .color-preview {
  transform: scale(1.02);
}

.color-values {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.color-value-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.value-label {
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 500;
}

.value-display {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0.75rem;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid #334155;
  border-radius: 8px;
}

.value-text {
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
  font-size: 0.875rem;
  color: #e2e8f0;
}

.copy-btn {
  padding: 0.25rem;
  background: transparent;
  border: none;
  color: #64748b;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.copy-btn:hover {
  background: rgba(255, 255, 255, 0.05);
  color: #cbd5e0;
}

/* Color Metadata */
.color-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding-top: 1rem;
  border-top: 1px solid #334155;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Action Buttons */
.action-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.action-btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.action-btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
}

.action-btn-secondary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid #475569;
  border-radius: 8px;
  color: #cbd5e0;
  font-weight: 500;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.action-btn-secondary:hover {
  background: rgba(51, 65, 85, 0.8);
  border-color: #64748b;
}

/* Pagination */
.pagination {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
  padding-top: 2rem;
  border-top: 1px solid #334155;
}

.pagination-info {
  color: #94a3b8;
  font-size: 0.875rem;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  padding: 0.5rem;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  border-radius: 6px;
  color: #cbd5e0;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: rgba(51, 65, 85, 0.8);
  border-color: #475569;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

.page-btn {
  min-width: 40px;
  padding: 0.5rem;
  background: rgba(30, 41, 59, 0.8);
  border: 1px solid #334155;
  border-radius: 6px;
  color: #cbd5e0;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.page-btn:hover {
  background: rgba(51, 65, 85, 0.8);
  border-color: #475569;
}

.page-btn.active {
  background: linear-gradient(135deg, #8b5cf6 0%, #ec4899 100%);
  border-color: transparent;
  color: white;
}

/* Actions Dropdown */
.actions-dropdown {
  position: fixed;
  z-index: 1000;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid #475569;
  border-radius: 8px;
  min-width: 180px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: scale(0.95);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  background: transparent;
  border: none;
  color: #cbd5e0;
  font-size: 0.875rem;
  text-align: left;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background: rgba(255, 255, 255, 0.05);
  color: white;
}

.dropdown-divider {
  height: 1px;
  background: #334155;
  margin: 0.25rem 0;
}

/* Notification */
.notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
  animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.notification-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: rgba(30, 41, 59, 0.95);
  backdrop-filter: blur(10px);
  border: 1px solid #475569;
  border-radius: 12px;
  color: white;
  font-size: 0.875rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

/* Modal Backdrop */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 999;
  background: rgba(0, 0, 0, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
  .color-history-container {
    padding: 1rem;
  }
  
  .color-grid {
    grid-template-columns: 1fr;
  }
  
  .header-actions {
    flex-direction: column;
  }
  
  .search-container {
    min-width: 100%;
  }
  
  .filter-grid {
    grid-template-columns: 1fr;
  }
  
  .action-buttons {
    grid-template-columns: 1fr;
  }
  
  .notification {
    left: 1rem;
    right: 1rem;
    bottom: 1rem;
  }
}

@media (min-width: 769px) and (max-width: 1024px) {
  .color-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 1025px) and (max-width: 1440px) {
  .color-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (min-width: 1441px) {
  .color-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.8);
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #8b5cf6, #ec4899);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #7c3aed, #db2777);
}
</style>