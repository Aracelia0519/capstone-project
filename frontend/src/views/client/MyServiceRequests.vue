<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 to-slate-950 p-3 sm:p-4 md:p-6">
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="max-w-full overflow-hidden">
          <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-300 to-teal-300 leading-tight sm:leading-normal">
            My Service Requests
          </h1>
          <p class="text-gray-400 mt-1 sm:mt-2 text-sm sm:text-base">Track and manage all your painting service jobs</p>
        </div>
        
        <!-- Status Filter -->
        <div class="flex items-center space-x-2 mt-2 sm:mt-0">
          <div class="relative min-w-0 flex-1">
            <select 
              v-model="activeFilter"
              class="w-full bg-gray-800/50 border border-gray-700 rounded-xl pl-10 pr-4 py-2.5 sm:py-3 text-gray-300 text-sm sm:text-base focus:outline-none focus:ring-2 focus:ring-cyan-500/30 focus:border-cyan-500 appearance-none backdrop-blur-sm"
            >
              <option value="all">All Requests</option>
              <option value="pending">Pending</option>
              <option value="in-progress">In Progress</option>
              <option value="completed">Completed</option>
            </select>
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
          </div>
        </div>
      </div>
      
      <!-- Stats Overview -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-4 sm:mt-6">
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm truncate">Total Requests</p>
              <p class="text-xl sm:text-2xl font-bold text-white mt-1 truncate">{{ serviceRequests.length }}</p>
            </div>
            <div class="p-2 sm:p-3 rounded-xl bg-blue-500/10 flex-shrink-0 ml-2">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm truncate">Active</p>
              <p class="text-xl sm:text-2xl font-bold text-cyan-300 mt-1 truncate">{{ statusCounts['in-progress'] }}</p>
            </div>
            <div class="p-2 sm:p-3 rounded-xl bg-cyan-500/10 flex-shrink-0 ml-2">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm truncate">Pending</p>
              <p class="text-xl sm:text-2xl font-bold text-amber-300 mt-1 truncate">{{ statusCounts.pending }}</p>
            </div>
            <div class="p-2 sm:p-3 rounded-xl bg-amber-500/10 flex-shrink-0 ml-2">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 rounded-2xl p-3 sm:p-4 border border-gray-700/50 backdrop-blur-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <p class="text-gray-400 text-xs sm:text-sm truncate">Completed</p>
              <p class="text-xl sm:text-2xl font-bold text-emerald-300 mt-1 truncate">{{ statusCounts.completed }}</p>
            </div>
            <div class="p-2 sm:p-3 rounded-xl bg-emerald-500/10 flex-shrink-0 ml-2">
              <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Service Requests Grid -->
    <div class="grid grid-cols-1 gap-4 sm:gap-6">
      <div 
        v-for="request in filteredRequests"
        :key="request.id"
        class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-700/50 backdrop-blur-sm hover:border-cyan-500/30 transition-all duration-300 hover:shadow-xl sm:hover:shadow-2xl hover:shadow-cyan-500/10 group w-full overflow-hidden"
      >
        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">
          <!-- Color Preview Section -->
          <div class="flex flex-col xs:flex-row items-start gap-3 sm:gap-4 w-full sm:w-auto">
            <div class="relative flex-shrink-0">
              <!-- Color Preview Circle -->
              <div 
                class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl shadow-lg sm:shadow-2xl transition-transform duration-300 group-hover:scale-105"
                :style="{ backgroundColor: request.color }"
              ></div>
              <!-- Color Value Badge -->
              <div class="absolute -bottom-1 -right-1 px-2 py-1 bg-gray-900/90 backdrop-blur-sm rounded-full border border-gray-700">
                <span class="text-xs font-mono text-gray-300">{{ request.colorCode }}</span>
              </div>
            </div>
            
            <!-- Request Details -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-col xs:flex-row xs:items-center gap-2 xs:gap-3 mb-2 sm:mb-3">
                <span class="px-2 py-1 rounded-full text-xs font-semibold backdrop-blur-sm border w-fit"
                  :class="getStatusClasses(request.status)">
                  {{ request.statusLabel }}
                </span>
                <span class="text-xs sm:text-sm text-gray-400">{{ request.date }}</span>
              </div>
              
              <h3 class="text-base sm:text-lg font-semibold text-white mb-1 sm:mb-2 truncate">{{ request.projectName }}</h3>
              <p class="text-gray-400 text-xs sm:text-sm mb-3 sm:mb-4 line-clamp-2">{{ request.description }}</p>
              
              <!-- Service Provider Info -->
              <div class="flex items-center gap-2 sm:gap-3">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center flex-shrink-0">
                  <svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="min-w-0">
                  <p class="text-xs sm:text-sm font-medium text-white truncate">{{ request.serviceProvider }}</p>
                  <p class="text-xs text-gray-400 truncate">Assigned Professional</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div class="flex justify-end sm:justify-start mt-3 sm:mt-0 sm:self-center">
            <button 
              @click="viewDetails(request)"
              class="px-3 py-2 sm:px-4 sm:py-2 bg-gray-700/50 hover:bg-gray-700 text-gray-300 rounded-lg sm:rounded-xl text-xs sm:text-sm font-medium transition-all duration-200 border border-gray-600 hover:border-cyan-500/50 flex items-center gap-1.5 sm:gap-2"
            >
              <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              <span class="whitespace-nowrap">Details</span>
            </button>
          </div>
        </div>
        
        <!-- Progress Timeline -->
        <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-700/50">
          <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 sm:gap-0">
            <div class="flex flex-wrap gap-2 sm:gap-4">
              <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-cyan-500 flex-shrink-0"></div>
                <span class="text-xs text-gray-400 whitespace-nowrap">Requested</span>
                <span class="text-xs text-gray-300 whitespace-nowrap">{{ request.requestedDate }}</span>
              </div>
              
              <div class="flex items-center gap-1.5 sm:gap-2">
                <div class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full flex-shrink-0" :class="{
                  'bg-amber-500': request.status === 'pending',
                  'bg-cyan-500': request.status === 'in-progress',
                  'bg-emerald-500': request.status === 'completed'
                }"></div>
                <span class="text-xs text-gray-400 whitespace-nowrap">Current</span>
                <span class="text-xs text-gray-300 whitespace-nowrap">{{ request.currentStageDate }}</span>
              </div>
            </div>
            
            <div class="text-xs sm:text-sm font-medium whitespace-nowrap" :class="{
              'text-amber-400': request.status === 'pending',
              'text-cyan-400': request.status === 'in-progress',
              'text-emerald-400': request.status === 'completed'
            }">
              {{ getStatusMessage(request.status) }}
            </div>
          </div>
          
          <!-- Progress Bar -->
          <div class="mt-2 sm:mt-4">
            <div class="h-1.5 sm:h-2 bg-gray-800 rounded-full overflow-hidden">
              <div 
                class="h-full rounded-full transition-all duration-1000"
                :class="getProgressBarClass(request.status)"
                :style="{ width: getProgressWidth(request.status) }"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredRequests.length === 0" class="text-center py-12 sm:py-16">
      <div class="w-20 h-20 sm:w-24 sm:h-24 mx-auto mb-4 sm:mb-6 bg-gray-800/50 rounded-2xl flex items-center justify-center border border-gray-700/50">
        <svg class="w-10 h-10 sm:w-12 sm:h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
      </div>
      <h3 class="text-lg sm:text-xl font-semibold text-gray-300 mb-1 sm:mb-2">No service requests found</h3>
      <p class="text-gray-500 max-w-md mx-auto text-sm sm:text-base px-4">
        {{ activeFilter === 'all' 
          ? 'You haven\'t created any service requests yet.' 
          : `You don't have any ${activeFilter} service requests.` 
        }}
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MyServiceRequests',
  data() {
    return {
      activeFilter: 'all',
      serviceRequests: [
        {
          id: 1,
          projectName: 'Living Room Wall Painting',
          description: 'Complete wall painting with accent wall in blue',
          serviceProvider: 'John PaintMaster',
          color: '#3B82F6',
          colorCode: '#3B82F6',
          status: 'in-progress',
          statusLabel: 'In Progress',
          date: 'Oct 15, 2023',
          requestedDate: 'Oct 10, 2023',
          currentStageDate: 'Oct 20, 2023',
          progress: 60
        },
        {
          id: 2,
          projectName: 'Bedroom Ceiling & Walls',
          description: 'Full bedroom renovation with premium matte finish',
          serviceProvider: 'Sarah ColorExpert',
          color: '#10B981',
          colorCode: '#10B981',
          status: 'pending',
          statusLabel: 'Pending',
          date: 'Nov 5, 2023',
          requestedDate: 'Nov 5, 2023',
          currentStageDate: 'Nov 12, 2023',
          progress: 20
        },
        {
          id: 3,
          projectName: 'Kitchen Cabinet Refinishing',
          description: 'Cabinet refinishing with modern gray finish',
          serviceProvider: 'Mike BrushPro',
          color: '#6B7280',
          colorCode: '#6B7280',
          status: 'completed',
          statusLabel: 'Completed',
          date: 'Sep 28, 2023',
          requestedDate: 'Sep 15, 2023',
          currentStageDate: 'Sep 28, 2023',
          progress: 100
        },
        {
          id: 4,
          projectName: 'Exterior House Painting',
          description: 'Complete exterior painting with weather-resistant paint',
          serviceProvider: 'Alex SurfacePro',
          color: '#F59E0B',
          colorCode: '#F59E0B',
          status: 'in-progress',
          statusLabel: 'In Progress',
          date: 'Oct 25, 2023',
          requestedDate: 'Oct 20, 2023',
          currentStageDate: 'Oct 30, 2023',
          progress: 40
        },
        {
          id: 5,
          projectName: 'Bathroom Wall Tiling',
          description: 'Wall tiling with waterproof blue ceramic tiles',
          serviceProvider: 'Emma TileMaster',
          color: '#8B5CF6',
          colorCode: '#8B5CF6',
          status: 'pending',
          statusLabel: 'Pending',
          date: 'Nov 8, 2023',
          requestedDate: 'Nov 8, 2023',
          currentStageDate: 'Nov 15, 2023',
          progress: 10
        },
        {
          id: 6,
          projectName: 'Office Space Painting',
          description: 'Professional office space with calming green tones',
          serviceProvider: 'David OfficePro',
          color: '#059669',
          colorCode: '#059669',
          status: 'completed',
          statusLabel: 'Completed',
          date: 'Aug 15, 2023',
          requestedDate: 'Aug 1, 2023',
          currentStageDate: 'Aug 15, 2023',
          progress: 100
        }
      ]
    }
  },
  computed: {
    filteredRequests() {
      if (this.activeFilter === 'all') {
        return this.serviceRequests
      }
      return this.serviceRequests.filter(request => request.status === this.activeFilter)
    },
    statusCounts() {
      return this.serviceRequests.reduce((acc, request) => {
        acc[request.status] = (acc[request.status] || 0) + 1
        return acc
      }, { pending: 0, 'in-progress': 0, completed: 0 })
    }
  },
  methods: {
    getStatusClasses(status) {
      const classes = {
        'pending': 'bg-amber-500/10 text-amber-400 border-amber-500/30',
        'in-progress': 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
        'completed': 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
      }
      return classes[status] || 'bg-gray-500/10 text-gray-400 border-gray-500/30'
    },
    getProgressBarClass(status) {
      const classes = {
        'pending': 'bg-gradient-to-r from-amber-500 to-amber-400',
        'in-progress': 'bg-gradient-to-r from-cyan-500 to-blue-400',
        'completed': 'bg-gradient-to-r from-emerald-500 to-green-400'
      }
      return classes[status] || 'bg-gradient-to-r from-gray-500 to-gray-400'
    },
    getProgressWidth(status) {
      const widths = {
        'pending': '20%',
        'in-progress': '60%',
        'completed': '100%'
      }
      return widths[status] || '0%'
    },
    getStatusMessage(status) {
      const messages = {
        'pending': 'Awaiting confirmation',
        'in-progress': 'Work in progress',
        'completed': 'Successfully completed'
      }
      return messages[status] || 'Status unknown'
    },
    viewDetails(request) {
      console.log('Viewing details for:', request)
      // Implement navigation or modal display here
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #38bdf8, #0ea5e9);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #0ea5e9, #0284c7);
}

/* Smooth transitions */
* {
  transition: all 0.2s ease-in-out;
}

/* Card hover effects */
.bg-gradient-to-br {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.bg-gradient-to-br:hover {
  transform: translateY(-1px);
}

/* Status badge pulse animation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

.bg-cyan-500\/10 {
  animation: pulse 2s infinite;
}

/* Progress bar animation */
@keyframes progress {
  from {
    width: 0%;
  }
}

.bg-gradient-to-r {
  animation: progress 1s ease-out;
}

/* Backdrop blur for glass effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Gradient text effects */
.text-transparent {
  background-clip: text;
  -webkit-background-clip: text;
}

/* Smooth border transitions */
.border {
  transition: border-color 0.3s ease;
}

/* Card shadow depth */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Hover shadow enhancement */
.hover\:shadow-2xl:hover {
  box-shadow: 0 25px 50px -12px rgba(56, 189, 248, 0.15);
}

/* Status indicator animations */
.bg-cyan-500 {
  animation: pulse 2s infinite;
}

.bg-amber-500 {
  animation: pulse 3s infinite;
}

.bg-emerald-500 {
  animation: none; /* Completed status doesn't pulse */
}

/* Mobile-first responsive utilities */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Extra small devices (phones, less than 400px) */
@media (max-width: 399px) {
  .min-h-screen {
    padding: 0.75rem;
  }
  
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .flex-col.xs\:flex-row {
    flex-direction: column;
  }
}

/* Small devices (phones, 400px to 639px) */
@media (min-width: 400px) and (max-width: 639px) {
  .min-h-screen {
    padding: 1rem;
  }
  
  .text-2xl {
    font-size: 1.75rem;
  }
}

/* Medium devices (tablets, 640px to 767px) */
@media (min-width: 640px) and (max-width: 767px) {
  .min-h-screen {
    padding: 1.5rem;
  }
  
  .grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Large devices (desktops, 768px and up) */
@media (min-width: 768px) {
  .min-h-screen {
    padding: 1.5rem;
  }
}

/* Extra large devices (large desktops, 1024px and up) */
@media (min-width: 1024px) {
  .min-h-screen {
    padding: 2rem;
  }
  
  .grid-cols-1.lg\:grid-cols-2 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Prevent text overflow */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Print styles */
@media print {
  .bg-gradient-to-br {
    background: white !important;
    border: 1px solid #e5e7eb !important;
  }
  
  .text-white {
    color: #1f2937 !important;
  }
  
  .text-gray-400 {
    color: #6b7280 !important;
  }
}

/* Safe area insets for modern mobile devices */
@supports (padding: max(0px)) {
  .min-h-screen {
    padding-left: max(0.75rem, env(safe-area-inset-left));
    padding-right: max(0.75rem, env(safe-area-inset-right));
    padding-top: max(0.75rem, env(safe-area-inset-top));
    padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
  }
}

/* Touch-friendly tap targets */
button, 
select {
  min-height: 44px;
  min-width: 44px;
}

/* Prevent text selection on interactive elements */
button {
  user-select: none;
  -webkit-user-select: none;
}

/* Smooth scrolling for the entire page */
html {
  scroll-behavior: smooth;
}

/* Optimize for reduced motion */
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .bg-gradient-to-br {
    border-width: 2px;
  }
  
  .text-gray-400 {
    color: #4b5563 !important;
  }
}
</style>