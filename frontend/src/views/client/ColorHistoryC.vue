<template>
  <div class="color-history-container bg-gradient-to-br from-slate-900 via-gray-900 to-gray-950 min-h-screen p-4 md:p-8">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-indigo-400 via-purple-400 to-violet-400 bg-clip-text text-transparent mb-2">
        Color History
      </h1>
      <p class="text-gray-400">View and manage your past paint color selections</p>
      
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
        <div class="stat-card bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Total Colors</p>
              <p class="text-2xl font-bold text-white">{{ stats.totalColors }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-indigo-500/20 to-purple-500/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="stat-card bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Recent Selections</p>
              <p class="text-2xl font-bold text-white">{{ stats.recentSelections }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-cyan-500/20 to-teal-500/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="stat-card bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Most Used</p>
              <p class="text-2xl font-bold text-white">{{ stats.mostUsed }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-emerald-500/20 to-green-500/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="stat-card bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Service Providers</p>
              <p class="text-2xl font-bold text-white">{{ stats.serviceProviders }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-amber-500/20 to-yellow-500/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="mb-6 flex flex-col md:flex-row gap-4 items-center justify-between">
      <div class="flex flex-wrap gap-3">
        <button 
          v-for="filter in filters" 
          :key="filter.id"
          @click="setActiveFilter(filter.id)"
          :class="[
            'filter-btn px-4 py-2 rounded-lg transition-all duration-300 border',
            activeFilter === filter.id 
              ? 'bg-gradient-to-r from-indigo-500/20 to-purple-500/20 text-indigo-300 border-indigo-500/50' 
              : 'bg-slate-800/30 text-gray-400 border-slate-700/50 hover:border-slate-600'
          ]"
        >
          <span class="flex items-center gap-2">
            <component :is="filter.icon" class="w-4 h-4" />
            {{ filter.label }}
          </span>
        </button>
      </div>
      
      <div class="relative w-full md:w-auto">
        <div class="relative">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search colors, providers, or dates..."
            class="w-full md:w-64 pl-10 pr-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all"
          >
          <svg class="absolute left-3 top-3 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Color History Grid -->
    <div v-if="filteredColors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="color in filteredColors" 
        :key="color.id"
        class="color-card group bg-gradient-to-br from-slate-800/40 to-slate-900/40 border border-slate-700/30 rounded-2xl p-5 transition-all duration-500 hover:border-indigo-500/30 hover:shadow-2xl hover:shadow-indigo-900/20 hover:scale-[1.02] backdrop-blur-sm"
      >
        <!-- Color Preview Section -->
        <div class="mb-4">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold text-white truncate">{{ color.name }}</h3>
            <div class="flex items-center gap-2">
              <button @click="copyToClipboard(color.hex)" class="p-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors group/copy">
                <svg class="w-4 h-4 text-gray-400 group-hover/copy:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </button>
              <button @click="toggleFavorite(color.id)" class="p-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors">
                <svg 
                  :class="['w-4 h-4', color.favorite ? 'text-amber-400 fill-amber-400' : 'text-gray-400']" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Color Swatch -->
          <div class="relative h-32 rounded-xl overflow-hidden border border-slate-700/50 shadow-lg">
            <div 
              :style="{ backgroundColor: color.hex }" 
              class="w-full h-full transition-all duration-700 group-hover:scale-110"
            ></div>
            <!-- Color Overlay Info -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
              <div class="absolute bottom-3 left-3 right-3">
                <p class="text-xs text-gray-300">Click to preview on walls</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Color Information -->
        <div class="space-y-3">
          <!-- Color Values -->
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-slate-800/30 rounded-lg p-3">
              <p class="text-xs text-gray-400 mb-1">HEX</p>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" :style="{ backgroundColor: color.hex }"></div>
                <p class="text-sm font-mono text-gray-300">{{ color.hex }}</p>
              </div>
            </div>
            <div class="bg-slate-800/30 rounded-lg p-3">
              <p class="text-xs text-gray-400 mb-1">RGB</p>
              <p class="text-sm font-mono text-gray-300">{{ color.rgb }}</p>
            </div>
          </div>

          <!-- Usage Details -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-400">Used</span>
              </div>
              <span class="text-sm text-gray-300">{{ formatDate(color.date) }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="text-sm text-gray-400">Service Provider</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-gradient-to-r from-cyan-500 to-teal-400 flex items-center justify-center">
                  <span class="text-xs font-bold text-white">{{ getInitials(color.provider) }}</span>
                </div>
                <span class="text-sm text-gray-300 truncate max-w-[100px]">{{ color.provider }}</span>
              </div>
            </div>
          </div>

          <!-- Project Info -->
          <div v-if="color.project" class="mt-2 pt-3 border-t border-slate-700/30">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
              <span class="text-sm text-gray-400">Project:</span>
              <span class="text-sm text-gray-300">{{ color.project }}</span>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2 pt-3">
            <button 
              @click="reuseColor(color)"
              class="flex-1 py-2 px-4 bg-gradient-to-r from-indigo-500/20 to-purple-500/20 text-indigo-300 rounded-lg border border-indigo-500/30 hover:border-indigo-400/50 hover:bg-indigo-500/30 transition-all duration-300 text-sm font-medium flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Reuse
            </button>
            <button 
              @click="shareColor(color)"
              class="flex-1 py-2 px-4 bg-slate-700/50 text-gray-300 rounded-lg border border-slate-600 hover:border-slate-500 hover:bg-slate-600/50 transition-all duration-300 text-sm font-medium flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
              </svg>
              Share
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-20">
      <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-r from-slate-800 to-slate-900 border border-slate-700 flex items-center justify-center">
        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <h3 class="text-2xl font-bold text-gray-400 mb-3">No Color History Yet</h3>
      <p class="text-gray-500 mb-6">Start by exploring colors in the preview module or wait for your service provider's recommendations.</p>
      <button 
        @click="goToPreview"
        class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 font-medium inline-flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
        </svg>
        Explore Colors
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="i in 6" :key="i" class="color-card bg-gradient-to-br from-slate-800/40 to-slate-900/40 border border-slate-700/30 rounded-2xl p-5">
        <div class="animate-pulse">
          <div class="h-6 bg-slate-700/50 rounded mb-4"></div>
          <div class="h-32 bg-slate-700/50 rounded-xl mb-4"></div>
          <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
              <div class="h-16 bg-slate-700/50 rounded-lg"></div>
              <div class="h-16 bg-slate-700/50 rounded-lg"></div>
            </div>
            <div class="space-y-2">
              <div class="h-4 bg-slate-700/50 rounded"></div>
              <div class="h-4 bg-slate-700/50 rounded"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="toast.show" class="fixed bottom-4 right-4 z-50 animate-slide-up">
      <div class="bg-gradient-to-r from-slate-800 to-slate-900 border border-slate-700 rounded-xl p-4 shadow-2xl max-w-sm">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gradient-to-r from-cyan-500/20 to-teal-500/20 flex items-center justify-center">
            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-white font-medium">{{ toast.message }}</p>
            <p class="text-gray-400 text-sm">{{ toast.detail }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ColorHistory',
  data() {
    return {
      loading: false,
      searchQuery: '',
      activeFilter: 'all',
      toast: {
        show: false,
        message: '',
        detail: ''
      },
      stats: {
        totalColors: 12,
        recentSelections: 4,
        mostUsed: 3,
        serviceProviders: 2
      },
      filters: [
        { id: 'all', label: 'All Colors', icon: 'AllColorsIcon' },
        { id: 'recent', label: 'Recent', icon: 'RecentIcon' },
        { id: 'favorites', label: 'Favorites', icon: 'FavoriteIcon' },
        { id: 'projects', label: 'By Project', icon: 'ProjectIcon' }
      ],
      colors: [
        {
          id: 1,
          name: 'Ocean Blue',
          hex: '#2563eb',
          rgb: '37, 99, 235',
          date: '2024-01-15',
          provider: 'John Paint Masters',
          favorite: true,
          project: 'Living Room Redesign'
        },
        {
          id: 2,
          name: 'Forest Green',
          hex: '#16a34a',
          rgb: '22, 163, 74',
          date: '2024-01-12',
          provider: 'Maria Color Experts',
          favorite: false,
          project: 'Bedroom Makeover'
        },
        {
          id: 3,
          name: 'Sunset Orange',
          hex: '#ea580c',
          rgb: '234, 88, 12',
          date: '2024-01-10',
          provider: 'John Paint Masters',
          favorite: true,
          project: 'Kitchen Renovation'
        },
        {
          id: 4,
          name: 'Midnight Purple',
          hex: '#7c3aed',
          rgb: '124, 58, 237',
          date: '2024-01-08',
          provider: 'ColorPro Services',
          favorite: false,
          project: 'Home Office'
        },
        {
          id: 5,
          name: 'Cream White',
          hex: '#f8fafc',
          rgb: '248, 250, 252',
          date: '2024-01-05',
          provider: 'Maria Color Experts',
          favorite: true,
          project: 'Hallway Refresh'
        },
        {
          id: 6,
          name: 'Charcoal Gray',
          hex: '#334155',
          rgb: '51, 65, 85',
          date: '2024-01-03',
          provider: 'John Paint Masters',
          favorite: false,
          project: 'Exterior Walls'
        }
      ]
    }
  },
  computed: {
    filteredColors() {
      let filtered = this.colors
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(color => 
          color.name.toLowerCase().includes(query) ||
          color.hex.toLowerCase().includes(query) ||
          color.provider.toLowerCase().includes(query) ||
          color.project.toLowerCase().includes(query)
        )
      }
      
      // Apply active filter
      switch (this.activeFilter) {
        case 'recent':
          filtered = filtered.slice(0, 4)
          break
        case 'favorites':
          filtered = filtered.filter(color => color.favorite)
          break
        case 'projects':
          filtered = filtered.filter(color => color.project)
          break
      }
      
      return filtered
    }
  },
  methods: {
    setActiveFilter(filterId) {
      this.activeFilter = filterId
    },
    
    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
      })
    },
    
    getInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase()
    },
    
    toggleFavorite(colorId) {
      const color = this.colors.find(c => c.id === colorId)
      if (color) {
        color.favorite = !color.favorite
        this.showToast(
          color.favorite ? 'Added to Favorites' : 'Removed from Favorites',
          `${color.name} ${color.favorite ? 'is now in your favorites' : 'was removed from favorites'}`
        )
      }
    },
    
    copyToClipboard(text) {
      navigator.clipboard.writeText(text)
      this.showToast('Copied to Clipboard', `Color code ${text} copied`)
    },
    
    reuseColor(color) {
      this.showToast('Color Reused', `${color.name} added to current project`)
      // In real app, this would navigate to color preview with the color pre-loaded
    },
    
    shareColor(color) {
      this.showToast('Share Link Generated', `Shareable link for ${color.name} created`)
      // In real app, this would generate a shareable link
    },
    
    goToPreview() {
      this.$router.push('/Clients/colorPreview')
    },
    
    showToast(message, detail) {
      this.toast = {
        show: true,
        message,
        detail
      }
      
      setTimeout(() => {
        this.toast.show = false
      }, 3000)
    }
  },
  mounted() {
    // Simulate loading data
    this.loading = true
    setTimeout(() => {
      this.loading = false
    }, 1000)
  },
  components: {
    AllColorsIcon: {
      template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
      </svg>`
    },
    RecentIcon: {
      template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>`
    },
    FavoriteIcon: {
      template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
      </svg>`
    },
    ProjectIcon: {
      template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
      </svg>`
    }
  }
}
</script>

<style scoped>
.color-history-container {
  min-height: 100vh;
}

.stat-card {
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

.color-card {
  backdrop-filter: blur(10px);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.color-card:hover {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.filter-btn {
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.filter-btn:hover {
  transform: translateY(-1px);
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #4f46e5, #7c3aed);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #6366f1, #8b5cf6);
}

/* Animations */
@keyframes slide-up {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}

/* Loading skeleton animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .color-history-container {
    padding: 1rem;
  }
  
  .stat-card {
    padding: 0.75rem;
  }
  
  .color-card {
    padding: 1rem;
  }
  
  .filter-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
}

@media (max-width: 640px) {
  .color-history-container {
    padding: 0.75rem;
  }
  
  h1 {
    font-size: 1.75rem;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>