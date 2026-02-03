<template>
  <div class="color-history-container bg-gradient-to-br from-slate-900 via-gray-900 to-gray-950 min-h-screen p-4 md:p-8">
    <!-- Header Section -->
    <div class="mb-8">
      <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-indigo-400 via-purple-400 to-violet-400 bg-clip-text text-transparent mb-2">
        My Saved Colors
      </h1>
      <p class="text-gray-400">View and manage your saved color blends</p>
      
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
              <p class="text-gray-400 text-sm">Favorites</p>
              <p class="text-2xl font-bold text-white">{{ stats.totalFavorites }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-amber-500/20 to-yellow-500/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="stat-card bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Color Families</p>
              <p class="text-2xl font-bold text-white">{{ stats.colorFamilies }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-emerald-500/20 to-green-500/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11.5v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 21v-4a2 2 0 012-2h4a2 2 0 012 2v4" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="stat-card bg-gradient-to-br from-slate-800/50 to-slate-900/50 border border-slate-700/50 rounded-xl p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm">Last Saved</p>
              <p class="text-sm font-bold text-white">{{ stats.lastSaved || 'No colors yet' }}</p>
            </div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-cyan-500/20 to-teal-500/20 flex items-center justify-center">
              <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
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
        
        <!-- Color Family Filter -->
        <div class="relative">
          <select 
            v-model="selectedFamily"
            @change="applyFamilyFilter"
            class="px-4 py-2 rounded-lg bg-slate-800/30 text-gray-400 border border-slate-700/50 hover:border-slate-600 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent appearance-none cursor-pointer pr-10"
          >
            <option value="">All Families</option>
            <option v-for="family in colorFamilies" :key="family" :value="family">
              {{ family }}
            </option>
          </select>
          <svg class="absolute right-3 top-3 w-4 h-4 text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </div>
      
      <div class="flex items-center gap-3">
        <!-- Refresh Button -->
        <button 
          @click="fetchSavedColors"
          :disabled="loading"
          class="p-2.5 rounded-lg bg-slate-800/30 border border-slate-700/50 hover:border-slate-600 hover:bg-slate-700/50 transition-all duration-300"
          title="Refresh colors"
        >
          <svg :class="['w-5 h-5', loading ? 'text-indigo-400 animate-spin' : 'text-gray-400']" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
        </button>
        
        <!-- Search Input -->
        <div class="relative">
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Search colors by name, hex, or family..."
            class="w-full md:w-64 pl-10 pr-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-transparent transition-all"
            @input="handleSearchInput"
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
            <div class="flex items-center gap-3">
              <h3 class="text-lg font-semibold text-white truncate max-w-[150px]">{{ color.name }}</h3>
              <span class="px-2 py-1 text-xs rounded-full bg-slate-700/50 text-gray-300 border border-slate-600">
                {{ color.color_family }}
              </span>
            </div>
            <div class="flex items-center gap-2">
              <button @click="copyToClipboard(color.hex_code)" class="p-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors group/copy" title="Copy hex code">
                <svg class="w-4 h-4 text-gray-400 group-hover/copy:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
              </button>
              <button @click="toggleFavorite(color.id)" class="p-1.5 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors" :title="color.is_favorite ? 'Remove from favorites' : 'Add to favorites'">
                <svg 
                  :class="['w-4 h-4', color.is_favorite ? 'text-amber-400 fill-amber-400' : 'text-gray-400']" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </button>
              <button @click="deleteColor(color.id)" class="p-1.5 rounded-lg bg-slate-700/50 hover:bg-red-500/20 hover:border-red-500/30 transition-colors" title="Delete color">
                <svg class="w-4 h-4 text-gray-400 hover:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Color Swatch -->
          <div class="relative h-32 rounded-xl overflow-hidden border border-slate-700/50 shadow-lg">
            <div 
              :style="{ backgroundColor: color.hex_code }" 
              class="w-full h-full transition-all duration-700 group-hover:scale-110"
            ></div>
            <!-- Color Overlay Info -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
              <div class="absolute bottom-3 left-3 right-3">
                <p class="text-xs text-gray-300">Temperature: {{ color.temperature }}</p>
                <p class="text-xs text-gray-300 mt-1">Accessibility: {{ color.accessibility }}</p>
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
                <div class="w-4 h-4 rounded" :style="{ backgroundColor: color.hex_code }"></div>
                <p class="text-sm font-mono text-gray-300">{{ color.hex_code }}</p>
              </div>
            </div>
            <div class="bg-slate-800/30 rounded-lg p-3">
              <p class="text-xs text-gray-400 mb-1">RGB</p>
              <p class="text-sm font-mono text-gray-300">{{ color.rgb_values }}</p>
            </div>
          </div>

          <!-- Color Properties -->
          <div class="grid grid-cols-3 gap-2">
            <div class="text-center">
              <div class="w-8 h-8 rounded-full mx-auto mb-1 flex items-center justify-center bg-slate-800/50">
                <span class="text-xs font-bold text-white">{{ color.hue }}°</span>
              </div>
              <p class="text-xs text-gray-400">Hue</p>
            </div>
            <div class="text-center">
              <div class="w-8 h-8 rounded-full mx-auto mb-1 flex items-center justify-center bg-slate-800/50">
                <span class="text-xs font-bold text-white">{{ color.saturation }}%</span>
              </div>
              <p class="text-xs text-gray-400">Sat</p>
            </div>
            <div class="text-center">
              <div class="w-8 h-8 rounded-full mx-auto mb-1 flex items-center justify-center bg-slate-800/50">
                <span class="text-xs font-bold text-white">{{ color.lightness }}%</span>
              </div>
              <p class="text-xs text-gray-400">Light</p>
            </div>
          </div>

          <!-- Usage Details -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-400">Saved</span>
              </div>
              <span class="text-sm text-gray-300">{{ formatDate(color.created_at) }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                <span class="text-sm text-gray-400">Stability</span>
              </div>
              <span class="text-sm text-gray-300">{{ color.stability }}</span>
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
              @click="viewDetails(color)"
              class="flex-1 py-2 px-4 bg-slate-700/50 text-gray-300 rounded-lg border border-slate-600 hover:border-slate-500 hover:bg-slate-600/50 transition-all duration-300 text-sm font-medium flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              Details
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!loading" class="text-center py-20">
      <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-r from-slate-800 to-slate-900 border border-slate-700 flex items-center justify-center">
        <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
      </div>
      <h3 class="text-2xl font-bold text-gray-400 mb-3">No Saved Colors Yet</h3>
      <p class="text-gray-500 mb-6">Start by creating and saving color blends in the Color Mixing Lab.</p>
      <button 
        @click="goToColorMixer"
        class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 font-medium inline-flex items-center gap-2"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
        </svg>
        Go to Color Mixer
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

    <!-- Pagination -->
    <div v-if="pagination.total > pagination.per_page" class="mt-8 flex items-center justify-center gap-2">
      <button 
        @click="changePage(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
        class="px-4 py-2 rounded-lg bg-slate-800/30 border border-slate-700/50 text-gray-400 disabled:opacity-50 disabled:cursor-not-allowed hover:border-slate-600 hover:text-gray-300 transition-all"
      >
        Previous
      </button>
      
      <div class="flex items-center gap-1">
        <button 
          v-for="page in visiblePages" 
          :key="page"
          @click="changePage(page)"
          :class="[
            'w-10 h-10 rounded-lg flex items-center justify-center transition-all',
            page === pagination.current_page
              ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white'
              : 'bg-slate-800/30 border border-slate-700/50 text-gray-400 hover:border-slate-600 hover:text-gray-300'
          ]"
        >
          {{ page }}
        </button>
      </div>
      
      <button 
        @click="changePage(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
        class="px-4 py-2 rounded-lg bg-slate-800/30 border border-slate-700/50 text-gray-400 disabled:opacity-50 disabled:cursor-not-allowed hover:border-slate-600 hover:text-gray-300 transition-all"
      >
        Next
      </button>
    </div>

    <!-- Color Details Modal -->
    <transition name="modal-fade">
      <div v-if="showDetailsModal" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="relative w-full max-w-4xl max-h-[90vh] overflow-y-auto">
          <!-- Animated background effect -->
          <div class="absolute -inset-4 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-violet-500/10 rounded-3xl blur-2xl animate-pulse"></div>
          
          <div class="relative bg-gradient-to-br from-slate-900 to-gray-900 border border-slate-700/50 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Modal header -->
            <div class="relative p-6 border-b border-slate-700/50">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold text-white">Color Details</h3>
                <button 
                  @click="closeDetailsModal"
                  class="p-2 rounded-lg bg-slate-800/50 hover:bg-slate-700/50 transition-colors"
                >
                  <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <!-- Main color info -->
              <div class="flex items-center gap-4">
                <div 
                  class="w-20 h-20 rounded-xl border-4 border-slate-700/50 shadow-lg"
                  :style="{ backgroundColor: selectedColor?.hex_code }"
                ></div>
                <div>
                  <h4 class="text-xl font-bold text-white">{{ selectedColor?.name }}</h4>
                  <div class="flex items-center gap-3 mt-2">
                    <span class="px-3 py-1 rounded-full bg-slate-800/50 text-gray-300 border border-slate-700">
                      {{ selectedColor?.color_family }}
                    </span>
                    <span class="px-3 py-1 rounded-full bg-slate-800/50 text-gray-300 border border-slate-700 flex items-center gap-1">
                      <svg v-if="selectedColor?.is_favorite" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                      {{ selectedColor?.is_favorite ? 'Favorite' : 'Not Favorite' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal body -->
            <div class="p-6">
              <!-- Color Codes Section -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- HEX Code -->
                <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/50">
                  <p class="text-sm text-gray-400 mb-2">HEX Color Code</p>
                  <div class="flex items-center justify-between">
                    <p class="text-lg font-mono text-white font-bold">{{ selectedColor?.hex_code }}</p>
                    <button 
                      @click="copyToClipboard(selectedColor?.hex_code)"
                      class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors"
                      title="Copy HEX"
                    >
                      <svg class="w-4 h-4 text-gray-400 hover:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- RGB Values -->
                <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/50">
                  <p class="text-sm text-gray-400 mb-2">RGB Values</p>
                  <div class="flex items-center justify-between">
                    <p class="text-lg font-mono text-white font-bold">{{ selectedColor?.rgb_values }}</p>
                    <button 
                      @click="copyToClipboard(selectedColor?.rgb_values)"
                      class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors"
                      title="Copy RGB"
                    >
                      <svg class="w-4 h-4 text-gray-400 hover:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </div>
                
                <!-- HSL Values -->
                <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/50">
                  <p class="text-sm text-gray-400 mb-2">HSL Values</p>
                  <div class="flex items-center justify-between">
                    <p class="text-lg font-mono text-white font-bold">{{ selectedColor?.hsl_values }}</p>
                    <button 
                      @click="copyToClipboard(selectedColor?.hsl_values)"
                      class="p-2 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 transition-colors"
                      title="Copy HSL"
                    >
                      <svg class="w-4 h-4 text-gray-400 hover:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Color Properties Grid -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- Hue -->
                <div class="bg-slate-800/30 rounded-xl p-4 text-center border border-slate-700/50">
                  <div class="w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center" 
                       :style="{ background: 'conic-gradient(from 0deg, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000)' }">
                    <span class="text-lg font-bold text-white bg-black/50 rounded-full w-10 h-10 flex items-center justify-center">
                      {{ selectedColor?.hue }}°
                    </span>
                  </div>
                  <p class="text-sm text-gray-400">Hue Angle</p>
                </div>
                
                <!-- Saturation -->
                <div class="bg-slate-800/30 rounded-xl p-4 text-center border border-slate-700/50">
                  <div class="w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center" 
                       :style="{ background: `conic-gradient(from 0deg, ${selectedColor?.hex_code || '#000000'}00, ${selectedColor?.hex_code || '#000000'}ff)` }">
                    <span class="text-lg font-bold text-white bg-black/50 rounded-full w-10 h-10 flex items-center justify-center">
                      {{ selectedColor?.saturation }}%
                    </span>
                  </div>
                  <p class="text-sm text-gray-400">Saturation</p>
                </div>
                
                <!-- Lightness -->
                <div class="bg-slate-800/30 rounded-xl p-4 text-center border border-slate-700/50">
                  <div class="w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center" 
                       :style="{ background: 'linear-gradient(to bottom, #ffffff, #000000)' }">
                    <span class="text-lg font-bold text-white bg-black/50 rounded-full w-10 h-10 flex items-center justify-center">
                      {{ selectedColor?.lightness }}%
                    </span>
                  </div>
                  <p class="text-sm text-gray-400">Lightness</p>
                </div>
                
                <!-- Contrast Ratio -->
                <div class="bg-slate-800/30 rounded-xl p-4 text-center border border-slate-700/50">
                  <div class="w-16 h-16 rounded-full mx-auto mb-2 flex items-center justify-center" 
                       :style="{ background: `linear-gradient(135deg, ${selectedColor?.hex_code || '#000000'}, #ffffff)` }">
                    <span class="text-lg font-bold text-white">
                      {{ selectedColor?.contrast_ratio }}:1
                    </span>
                  </div>
                  <p class="text-sm text-gray-400">Contrast Ratio</p>
                </div>
              </div>

              <!-- Advanced Properties -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Left Column -->
                <div class="space-y-4">
                  <!-- Color Characteristics -->
                  <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/50">
                    <h5 class="text-lg font-semibold text-white mb-3">Color Characteristics</h5>
                    <div class="space-y-3">
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Temperature</span>
                        <span class="font-semibold" :class="getTemperatureClass(selectedColor?.temperature)">
                          {{ selectedColor?.temperature }}
                        </span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Accessibility</span>
                        <span class="font-semibold" :class="getAccessibilityClass(selectedColor?.accessibility)">
                          {{ selectedColor?.accessibility }}
                        </span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Stability</span>
                        <span class="font-semibold text-cyan-300">{{ selectedColor?.stability }}</span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Quantum State</span>
                        <span class="font-semibold text-purple-300">{{ selectedColor?.quantum_state }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Luminance -->
                  <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/50">
                    <h5 class="text-lg font-semibold text-white mb-3">Luminance</h5>
                    <div class="space-y-2">
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Luminance Value</span>
                        <span class="font-semibold text-white">{{ selectedColor?.luminance }}%</span>
                      </div>
                      <div class="w-full h-3 bg-slate-700 rounded-full overflow-hidden">
                        <div 
                          class="h-full bg-gradient-to-r from-cyan-500 to-blue-500 rounded-full transition-all duration-1000"
                          :style="{ width: `${selectedColor?.luminance || 0}%` }"
                        ></div>
                      </div>
                      <p class="text-xs text-gray-500 mt-2">
                        Higher luminance means brighter color (0% = black, 100% = white)
                      </p>
                    </div>
                  </div>
                  
                  
                </div>
                
                <!-- Right Column -->
                <div class="space-y-4">
                  <!-- Source Colors -->
                   <div v-if="selectedColor?.source_colors && selectedColor.source_colors.length > 0" 
                       class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/50">
                    <h5 class="text-lg font-semibold text-white mb-3">Source Colors</h5>
                    <div class="space-y-3">
                      <div v-for="(source, index) in selectedColor.source_colors" :key="index" class="space-y-2">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg border border-slate-600" 
                                 :style="{ backgroundColor: source.hex || source.color || '#ffffff' }"></div>
                            <div>
                              <p class="text-gray-300 font-medium">{{ source.name || `Color ${index + 1}` }}</p>
                              <p class="text-xs text-gray-500">{{ source.hex || source.color || 'N/A' }}</p>
                            </div>
                          </div>
                          <span class="font-semibold text-white">{{ source.weight || 0 }}%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-700 rounded-full overflow-hidden">
                          <div 
                            class="h-full rounded-full transition-all duration-1000"
                            :style="{ 
                              width: `${source.weight || 0}%`, 
                              background: `linear-gradient(90deg, ${source.hex || source.color || '#000000'}, ${(source.hex || source.color || '#000000')}80)` 
                            }"
                          ></div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  
                  <!-- Metadata -->
                  <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/50">
                    <h5 class="text-lg font-semibold text-white mb-3">Metadata</h5>
                    <div class="space-y-2">
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Created</span>
                        <span class="text-white">{{ formatFullDate(selectedColor?.created_at) }}</span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Last Updated</span>
                        <span class="text-white">{{ formatFullDate(selectedColor?.updated_at) }}</span>
                      </div>
                      <div class="flex items-center justify-between">
                        <span class="text-gray-400">Frequency</span>
                        <span class="font-mono text-white">{{ selectedColor?.frequency || 'N/A' }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Modal footer -->
            <div class="p-6 border-t border-slate-700/50 bg-slate-900/50">
              <div class="flex flex-col sm:flex-row gap-3">
                <button 
                  @click="reuseColor(selectedColor)"
                  class="flex-1 py-3 px-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl font-semibold hover:from-indigo-600 hover:to-purple-700 transition-all duration-300 flex items-center justify-center gap-2"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  Reuse This Color
                </button>
                <button 
                  @click="closeDetailsModal"
                  class="flex-1 py-3 px-6 bg-slate-800/50 text-gray-300 rounded-xl font-semibold border border-slate-700 hover:bg-slate-700/50 hover:text-white transition-all duration-300"
                >
                  Close
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>

    <!-- Toast Notification -->
    <div v-if="toast.show" class="fixed bottom-4 right-4 z-50 animate-slide-up">
      <div :class="[
        'border rounded-xl p-4 shadow-2xl max-w-sm',
        toast.type === 'success' 
          ? 'bg-gradient-to-r from-emerald-900/80 to-green-900/80 border-emerald-700/50'
          : toast.type === 'error'
          ? 'bg-gradient-to-r from-red-900/80 to-rose-900/80 border-red-700/50'
          : 'bg-gradient-to-r from-slate-800 to-slate-900 border-slate-700'
      ]">
        <div class="flex items-center gap-3">
          <div :class="[
            'w-10 h-10 rounded-full flex items-center justify-center',
            toast.type === 'success'
              ? 'bg-gradient-to-r from-emerald-500/20 to-green-500/20'
              : toast.type === 'error'
              ? 'bg-gradient-to-r from-red-500/20 to-rose-500/20'
              : 'bg-gradient-to-r from-cyan-500/20 to-teal-500/20'
          ]">
            <svg class="w-5 h-5" :class="[
              toast.type === 'success' ? 'text-emerald-400' :
              toast.type === 'error' ? 'text-red-400' :
              'text-cyan-400'
            ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              <path v-else-if="toast.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-white font-medium">{{ toast.message }}</p>
            <p class="text-gray-300 text-sm">{{ toast.detail }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/utils/axios'

export default {
  name: 'ColorHistory',
  data() {
    return {
      loading: false,
      searchQuery: '',
      activeFilter: 'all',
      selectedFamily: '',
      showDetailsModal: false,
      selectedColor: null,
      toast: {
        show: false,
        message: '',
        detail: '',
        type: 'info'
      },
      stats: {
        totalColors: 0,
        totalFavorites: 0,
        colorFamilies: 0,
        lastSaved: null
      },
      filters: [
        { id: 'all', label: 'All Colors', icon: 'AllColorsIcon' },
        { id: 'favorites', label: 'Favorites', icon: 'FavoriteIcon' },
        { id: 'recent', label: 'Recent', icon: 'RecentIcon' },
      ],
      colors: [],
      colorFamilies: [],
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 12,
        total: 0,
        from: 0,
        to: 0
      },
      searchTimeout: null
    }
  },
  computed: {
    filteredColors() {
      let filtered = [...this.colors]
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(color => 
          color.name.toLowerCase().includes(query) ||
          color.hex_code.toLowerCase().includes(query) ||
          color.color_family.toLowerCase().includes(query) ||
          (color.temperature && color.temperature.toLowerCase().includes(query))
        )
      }
      
      // Apply active filter
      switch (this.activeFilter) {
        case 'favorites':
          filtered = filtered.filter(color => color.is_favorite)
          break
        case 'recent':
          // Sort by most recent
          filtered = [...filtered].sort((a, b) => 
            new Date(b.created_at) - new Date(a.created_at)
          ).slice(0, 6)
          break
      }
      
      // Apply family filter
      if (this.selectedFamily) {
        filtered = filtered.filter(color => color.color_family === this.selectedFamily)
      }
      
      return filtered
    },
    
    visiblePages() {
      const pages = []
      const total = this.pagination.last_page
      const current = this.pagination.current_page
      
      if (total <= 5) {
        for (let i = 1; i <= total; i++) {
          pages.push(i)
        }
      } else {
        if (current <= 3) {
          pages.push(1, 2, 3, 4, '...', total)
        } else if (current >= total - 2) {
          pages.push(1, '...', total - 3, total - 2, total - 1, total)
        } else {
          pages.push(1, '...', current - 1, current, current + 1, '...', total)
        }
      }
      
      return pages
    }
  },
  methods: {
    async fetchSavedColors() {
      try {
        this.loading = true
        
        const params = {
          page: this.pagination.current_page,
          per_page: this.pagination.per_page,
          search: this.searchQuery || undefined,
          family: this.selectedFamily || undefined,
          favorites_only: this.activeFilter === 'favorites' ? true : undefined
        }
        
        // Remove undefined params
        Object.keys(params).forEach(key => params[key] === undefined && delete params[key])
        
        const response = await api.get('/client/colors', { params })
        
        if (response.data.success) {
          this.colors = response.data.data.colors
          this.pagination = response.data.data.pagination
          
          // Update stats
          this.updateStats()
          
          // Extract unique color families
          this.colorFamilies = [...new Set(this.colors.map(color => color.color_family))].sort()
        } else {
          this.showToast('Error', 'Failed to load saved colors', 'error')
        }
      } catch (error) {
        console.error('Error fetching saved colors:', error)
        this.showToast('Error', 'Failed to load saved colors. Please try again.', 'error')
      } finally {
        this.loading = false
      }
    },
    
    async fetchColorStats() {
      try {
        const response = await api.get('/client/color-stats')
        
        if (response.data.success) {
          const stats = response.data.data.stats
          const distribution = response.data.data.family_distribution
          
          this.stats = {
            totalColors: stats.total_colors || 0,
            totalFavorites: stats.total_favorites || 0,
            colorFamilies: stats.color_families || 0,
            lastSaved: stats.last_saved ? this.formatDate(stats.last_saved) : 'No colors yet'
          }
        }
      } catch (error) {
        console.error('Error fetching color stats:', error)
        // Don't show error toast for stats - it's non-critical
      }
    },
    
    updateStats() {
      const totalColors = this.colors.length
      const totalFavorites = this.colors.filter(color => color.is_favorite).length
      const colorFamilies = [...new Set(this.colors.map(color => color.color_family))].length
      const lastSaved = this.colors.length > 0 
        ? this.formatDate(this.colors[0].created_at)
        : 'No colors yet'
      
      this.stats = {
        totalColors,
        totalFavorites,
        colorFamilies,
        lastSaved
      }
    },
    
    async toggleFavorite(colorId) {
      try {
        const response = await api.post(`/client/colors/${colorId}/toggle-favorite`)
        
        if (response.data.success) {
          // Update local state
          const colorIndex = this.colors.findIndex(c => c.id === colorId)
          if (colorIndex !== -1) {
            this.colors[colorIndex].is_favorite = response.data.data.is_favorite
            this.updateStats()
            
            const colorName = this.colors[colorIndex].name
            this.showToast(
              response.data.data.is_favorite ? 'Added to Favorites' : 'Removed from Favorites',
              `${colorName} ${response.data.data.is_favorite ? 'is now in your favorites' : 'was removed from favorites'}`,
              'success'
            )
          }
        }
      } catch (error) {
        console.error('Error toggling favorite:', error)
        this.showToast('Error', 'Failed to update favorite status', 'error')
      }
    },
    
    async deleteColor(colorId) {
      if (!confirm('Are you sure you want to delete this color? This action cannot be undone.')) {
        return
      }
      
      try {
        const response = await api.delete(`/client/colors/${colorId}`)
        
        if (response.data.success) {
          // Remove from local state
          this.colors = this.colors.filter(color => color.id !== colorId)
          this.updateStats()
          
          // Refresh color families
          this.colorFamilies = [...new Set(this.colors.map(color => color.color_family))].sort()
          
          this.showToast('Success', 'Color deleted successfully', 'success')
          
          // If we're on a page that's now empty, go back to previous page
          if (this.colors.length === 0 && this.pagination.current_page > 1) {
            this.pagination.current_page--
            this.fetchSavedColors()
          }
        }
      } catch (error) {
        console.error('Error deleting color:', error)
        this.showToast('Error', 'Failed to delete color', 'error')
      }
    },
    
    setActiveFilter(filterId) {
      this.activeFilter = filterId
      this.fetchSavedColors()
    },
    
    applyFamilyFilter() {
      this.fetchSavedColors()
    },
    
    handleSearchInput() {
      // Clear previous timeout
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout)
      }
      
      // Set new timeout for debouncing
      this.searchTimeout = setTimeout(() => {
        this.pagination.current_page = 1
        this.fetchSavedColors()
      }, 500)
    },
    
    changePage(page) {
      if (page === '...' || page < 1 || page > this.pagination.last_page) return
      
      this.pagination.current_page = page
      this.fetchSavedColors()
      
      // Scroll to top
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      const now = new Date()
      const diffTime = Math.abs(now - date)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      if (diffDays === 1) {
        return 'Yesterday'
      } else if (diffDays < 7) {
        return `${diffDays} days ago`
      } else if (diffDays < 30) {
        const weeks = Math.floor(diffDays / 7)
        return `${weeks} ${weeks === 1 ? 'week' : 'weeks'} ago`
      } else {
        return date.toLocaleDateString('en-US', { 
          month: 'short', 
          day: 'numeric', 
          year: 'numeric' 
        })
      }
    },
    
    formatFullDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', { 
        year: 'numeric',
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    copyToClipboard(text) {
      if (!text) return
      navigator.clipboard.writeText(text)
      this.showToast('Copied', `Copied to clipboard`, 'success')
    },
    
    viewDetails(color) {
      // Parse palettes safely
      let palettes = { monochromatic: [], analogous: [], complementary: [] }
      try {
        if (color.palettes) {
          if (typeof color.palettes === 'string') {
            palettes = JSON.parse(color.palettes)
          } else if (typeof color.palettes === 'object') {
            palettes = color.palettes
          }
        }
      } catch (e) {
        console.error('Error parsing palettes:', e)
      }
      
      // Parse source colors safely
      let sourceColors = []
      try {
        if (color.source_colors) {
          if (typeof color.source_colors === 'string') {
            sourceColors = JSON.parse(color.source_colors)
          } else if (Array.isArray(color.source_colors)) {
            sourceColors = color.source_colors
          }
        }
      } catch (e) {
        console.error('Error parsing source colors:', e)
      }
      
      this.selectedColor = {
        ...color,
        source_colors: sourceColors,
        palettes: palettes
      }
      this.showDetailsModal = true
      document.body.style.overflow = 'hidden'
    },
    
    closeDetailsModal() {
      this.showDetailsModal = false
      this.selectedColor = null
      document.body.style.overflow = 'auto'
    },
    
    reuseColor(color) {
      if (!color) return
      
      // Parse data safely
      let palettes = { monochromatic: [], analogous: [], complementary: [] }
      let sourceColors = []
      
      try {
        if (color.palettes) {
          if (typeof color.palettes === 'string') {
            palettes = JSON.parse(color.palettes)
          } else if (typeof color.palettes === 'object') {
            palettes = color.palettes
          }
        }
        
        if (color.source_colors) {
          if (typeof color.source_colors === 'string') {
            sourceColors = JSON.parse(color.source_colors)
          } else if (Array.isArray(color.source_colors)) {
            sourceColors = color.source_colors
          }
        }
      } catch (e) {
        console.error('Error parsing color data for reuse:', e)
      }
      
      // Store color data to reuse in color mixer
      localStorage.setItem('color_to_reuse', JSON.stringify({
        name: color.name,
        hex: color.hex_code,
        rgb: color.rgb_values,
        hsl: color.hsl_values,
        hue: color.hue,
        saturation: color.saturation,
        lightness: color.lightness,
        contrast: color.contrast_ratio,
        luminance: color.luminance,
        temperature: color.temperature,
        accessibility: color.accessibility,
        family: color.color_family,
        stability: color.stability,
        frequency: color.frequency,
        quantumState: color.quantum_state,
        sourceColors: sourceColors,
        palettes: palettes
      }))
      
      this.showToast('Ready to Reuse', `${color.name} loaded for editing`, 'success')
      this.closeDetailsModal()
      this.$router.push('/Clients/colorPreview')
    },
    
    goToColorMixer() {
      this.$router.push('/Clients/colorPreview')
    },
    
    getTemperatureClass(temperature) {
      if (!temperature) return 'text-gray-400'
      if (temperature.toLowerCase().includes('warm')) return 'text-orange-400'
      if (temperature.toLowerCase().includes('cool')) return 'text-blue-400'
      return 'text-gray-400'
    },
    
    getAccessibilityClass(accessibility) {
      if (!accessibility) return 'text-gray-400'
      if (accessibility.toLowerCase() === 'excellent') return 'text-emerald-400'
      if (accessibility.toLowerCase() === 'good') return 'text-green-400'
      if (accessibility.toLowerCase() === 'poor') return 'text-red-400'
      return 'text-gray-400'
    },
    
    getPaletteColors(palette) {
      if (!palette) return []
      if (Array.isArray(palette)) return palette
      if (typeof palette === 'string') {
        try {
          const parsed = JSON.parse(palette)
          return Array.isArray(parsed) ? parsed : []
        } catch {
          return palette.split(',').map(c => c.trim())
        }
      }
      return []
    },
    
    hasPalettes(palettes) {
      if (!palettes) return false
      const mono = this.getPaletteColors(palettes.monochromatic)
      const analog = this.getPaletteColors(palettes.analogous)
      const comp = this.getPaletteColors(palettes.complementary)
      return mono.length > 0 || analog.length > 0 || comp.length > 0
    },
    
    showToast(message, detail, type = 'info') {
      this.toast = {
        show: true,
        message,
        detail,
        type
      }
      
      setTimeout(() => {
        this.toast.show = false
      }, 3000)
    }
  },
  mounted() {
    this.fetchSavedColors()
    // Try to fetch stats, but don't worry if it fails
    this.fetchColorStats().catch(() => {
      // Silently handle error - we already have stats from fetchSavedColors
    })
  },
  beforeUnmount() {
    // Clear any pending timeout
    if (this.searchTimeout) {
      clearTimeout(this.searchTimeout)
    }
    // Ensure body scroll is restored
    document.body.style.overflow = 'auto'
  },
  watch: {
    showDetailsModal(newVal) {
      if (newVal) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = 'auto'
      }
    }
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

/* Modal Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-active .modal-content,
.modal-fade-leave-active .modal-content {
  transition: transform 0.3s ease;
}

.modal-fade-enter-from .modal-content {
  transform: scale(0.95);
}

.modal-fade-leave-to .modal-content {
  transform: scale(1.05);
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

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
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