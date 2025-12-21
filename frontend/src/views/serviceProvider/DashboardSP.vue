<template>
  <div class="dashboard-container bg-gray-900 min-h-screen p-4 md:p-6 overflow-x-hidden">
    <!-- Header Section -->
    <div class="dashboard-header mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Service Provider Dashboard</h1>
          <p class="text-gray-400 text-sm md:text-base">Welcome back, <span class="text-blue-400 font-semibold">Maria Santos</span></p>
        </div>
        <div class="mt-4 md:mt-0">
          <div class="flex items-center space-x-3">
            <div class="relative">
              <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                <span class="text-white font-bold">MS</span>
              </div>
              <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900"></div>
            </div>
            <div class="text-right">
              <p class="text-white text-sm font-medium">Service Provider</p>
              <p class="text-gray-400 text-xs">CaviteGo Paint</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Date & Time Widget -->
      <div class="mt-6">
        <div class="inline-flex items-center space-x-3 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700">
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span class="text-gray-300 text-sm">{{ currentDate }}</span>
          <span class="text-gray-500">•</span>
          <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-gray-300 text-sm">{{ currentTime }}</span>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
      <!-- Total Clients Card -->
      <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-blue-500 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Total Clients</p>
            <h3 class="text-3xl font-bold text-white mb-2">142</h3>
            <div class="flex items-center">
              <span class="text-green-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                </svg>
                +12% from last month
              </span>
            </div>
          </div>
          <div class="stat-icon bg-gradient-to-br from-blue-500 to-cyan-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Active Jobs Card -->
      <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-amber-500 transition-all duration-300 hover:shadow-lg hover:shadow-amber-500/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Active Jobs</p>
            <h3 class="text-3xl font-bold text-white mb-2">8</h3>
            <div class="flex items-center">
              <span class="text-amber-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                2 high priority
              </span>
            </div>
          </div>
          <div class="stat-icon bg-gradient-to-br from-amber-500 to-orange-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Completed Jobs Card -->
      <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-green-500 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Completed Jobs</p>
            <h3 class="text-3xl font-bold text-white mb-2">47</h3>
            <div class="flex items-center">
              <span class="text-green-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                94% satisfaction
              </span>
            </div>
          </div>
          <div class="stat-icon bg-gradient-to-br from-emerald-500 to-green-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Revenue Card -->
      <div class="stat-card bg-gradient-to-br from-gray-800 to-gray-900 border border-gray-700 hover:border-purple-500 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Monthly Revenue</p>
            <h3 class="text-3xl font-bold text-white mb-2">₱85,420</h3>
            <div class="flex items-center">
              <span class="text-purple-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                +18% from last month
              </span>
            </div>
          </div>
          <div class="stat-icon bg-gradient-to-br from-purple-500 to-pink-400">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Recent Color Customizations -->
      <div class="lg:col-span-2">
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 md:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6">
            <div class="mb-4 sm:mb-0">
              <h2 class="text-xl font-bold text-white mb-1">Recent Color Customizations</h2>
              <p class="text-gray-400 text-sm">Latest paint colors created with clients</p>
            </div>
            <button class="text-blue-400 hover:text-blue-300 text-sm font-medium flex items-center justify-end">
              View All
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <div class="overflow-hidden">
            <div class="hidden md:block">
              <table class="w-full min-w-full">
                <thead>
                  <tr class="border-b border-gray-700">
                    <th class="text-left py-3 px-2 text-gray-400 font-medium text-sm">Color</th>
                    <th class="text-left py-3 px-2 text-gray-400 font-medium text-sm">Client</th>
                    <th class="text-left py-3 px-2 text-gray-400 font-medium text-sm">Project</th>
                    <th class="text-left py-3 px-2 text-gray-400 font-medium text-sm">Status</th>
                    <th class="text-left py-3 px-2 text-gray-400 font-medium text-sm">Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="color in recentColors" :key="color.id" class="border-b border-gray-700/50 hover:bg-gray-700/30 transition-colors">
                    <td class="py-3 px-2">
                      <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg mr-3" :style="{ backgroundColor: color.hex }"></div>
                        <div class="min-w-0">
                          <p class="text-white font-medium truncate">{{ color.name }}</p>
                          <p class="text-gray-400 text-xs truncate">{{ color.hex }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-2">
                      <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center mr-3 flex-shrink-0">
                          <span class="text-white text-xs font-bold">{{ getInitials(color.client) }}</span>
                        </div>
                        <div class="min-w-0">
                          <p class="text-white font-medium truncate">{{ color.client }}</p>
                          <p class="text-gray-400 text-xs truncate">{{ color.clientLocation }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="py-3 px-2">
                      <div class="min-w-0">
                        <p class="text-white font-medium truncate">{{ color.project }}</p>
                        <p class="text-gray-400 text-xs truncate">{{ color.roomType }}</p>
                      </div>
                    </td>
                    <td class="py-3 px-2">
                      <span :class="['status-badge', color.status === 'Approved' ? 'status-approved' : color.status === 'Pending' ? 'status-pending' : 'status-inprogress']">
                        {{ color.status }}
                      </span>
                    </td>
                    <td class="py-3 px-2">
                      <div class="min-w-0">
                        <p class="text-white">{{ color.date }}</p>
                        <p class="text-gray-400 text-xs">{{ color.time }}</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Mobile View -->
            <div class="md:hidden space-y-4">
              <div v-for="color in recentColors" :key="color.id" class="bg-gray-700/30 rounded-lg p-4">
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg mr-3" :style="{ backgroundColor: color.hex }"></div>
                    <div>
                      <p class="text-white font-medium">{{ color.name }}</p>
                      <p class="text-gray-400 text-xs">{{ color.hex }}</p>
                    </div>
                  </div>
                  <span :class="['status-badge', color.status === 'Approved' ? 'status-approved' : color.status === 'Pending' ? 'status-pending' : 'status-inprogress']">
                    {{ color.status }}
                  </span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <p class="text-gray-400 text-xs mb-1">Client</p>
                    <div class="flex items-center">
                      <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center mr-2">
                        <span class="text-white text-xs font-bold">{{ getInitials(color.client) }}</span>
                      </div>
                      <p class="text-white text-sm truncate">{{ color.client }}</p>
                    </div>
                  </div>
                  <div>
                    <p class="text-gray-400 text-xs mb-1">Project</p>
                    <p class="text-white text-sm truncate">{{ color.project }}</p>
                  </div>
                  <div>
                    <p class="text-gray-400 text-xs mb-1">Date</p>
                    <p class="text-white text-sm">{{ color.date }}</p>
                  </div>
                  <div>
                    <p class="text-gray-400 text-xs mb-1">Time</p>
                    <p class="text-white text-sm">{{ color.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Link to Virtual Paint Color Mixer -->
        <div class="mt-6 bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-700/50 rounded-xl p-4 md:p-6">
          <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="flex items-center mb-4 md:mb-0">
              <div class="mr-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-xl font-bold text-white mb-1">Virtual Paint Color Mixer</h3>
                <p class="text-gray-300 text-sm">Create custom paint colors with real-time visualization</p>
              </div>
            </div>
            <button @click="openColorMixer" class="unity-button w-full md:w-auto mt-4 md:mt-0">
              <span>Launch Unity Mixer</span>
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </button>
          </div>
          <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
            <div class="text-center p-3 bg-gray-800/50 rounded-lg">
              <p class="text-gray-400 text-sm">Colors Created</p>
              <p class="text-xl md:text-2xl font-bold text-white">156</p>
            </div>
            <div class="text-center p-3 bg-gray-800/50 rounded-lg">
              <p class="text-gray-400 text-sm">This Month</p>
              <p class="text-xl md:text-2xl font-bold text-white">24</p>
            </div>
            <div class="text-center p-3 bg-gray-800/50 rounded-lg">
              <p class="text-gray-400 text-sm">Most Popular</p>
              <p class="text-base md:text-lg font-bold text-white truncate">Sage Green</p>
            </div>
            <div class="text-center p-3 bg-gray-800/50 rounded-lg">
              <p class="text-gray-400 text-sm">Avg. Time</p>
              <p class="text-xl md:text-2xl font-bold text-white">8m</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Activity & Quick Actions -->
      <div class="space-y-6">
        <!-- Activity Timeline -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 md:p-6">
          <h2 class="text-xl font-bold text-white mb-6">Recent Activity</h2>
          <div class="space-y-6">
            <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
              <div class="flex items-start">
                <div :class="['activity-icon', `icon-${activity.type}`]">
                  <component :is="activity.icon" class="w-4 h-4" />
                </div>
                <div class="ml-4 flex-1 min-w-0">
                  <p class="text-white font-medium truncate">{{ activity.title }}</p>
                  <p class="text-gray-400 text-sm mt-1 line-clamp-2">{{ activity.description }}</p>
                  <p class="text-gray-500 text-xs mt-2">{{ activity.time }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 md:p-6">
          <h2 class="text-xl font-bold text-white mb-6">Quick Actions</h2>
          <div class="grid grid-cols-2 gap-3 md:gap-4">
            <button @click="createNewClient" class="quick-action-button bg-gradient-to-br from-blue-900/30 to-blue-800/30 border border-blue-700/50">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
              </div>
              <span class="text-white font-medium text-sm md:text-base">New Client</span>
            </button>
            <button @click="createServiceRequest" class="quick-action-button bg-gradient-to-br from-amber-900/30 to-orange-800/30 border border-amber-700/50">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <span class="text-white font-medium text-sm md:text-base">New Job</span>
            </button>
            <button @click="viewReports" class="quick-action-button bg-gradient-to-br from-emerald-900/30 to-green-800/30 border border-emerald-700/50">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-emerald-500 to-green-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <span class="text-white font-medium text-sm md:text-base">Reports</span>
            </button>
            <button @click="checkInventory" class="quick-action-button bg-gradient-to-br from-cyan-900/30 to-teal-800/30 border border-cyan-700/50">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-cyan-500 to-teal-400 flex items-center justify-center mb-3">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <span class="text-white font-medium text-sm md:text-base">Inventory</span>
            </button>
          </div>
        </div>

        <!-- Performance Metrics -->
        <div class="bg-gray-800 rounded-xl border border-gray-700 p-4 md:p-6">
          <h2 class="text-xl font-bold text-white mb-6">Performance Metrics</h2>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-gray-300 text-sm">Job Completion Rate</span>
                <span class="text-white font-medium">92%</span>
              </div>
              <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-green-400 rounded-full" style="width: 92%"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-gray-300 text-sm">Client Satisfaction</span>
                <span class="text-white font-medium">94%</span>
              </div>
              <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full" style="width: 94%"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between mb-1">
                <span class="text-gray-300 text-sm">Color Mix Accuracy</span>
                <span class="text-white font-medium">87%</span>
              </div>
              <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-400 rounded-full" style="width: 87%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ServiceProviderDashboard',
  data() {
    return {
      currentDate: '',
      currentTime: '',
      recentColors: [
        {
          id: 1,
          name: 'Ocean Breeze',
          hex: '#4CC9F0',
          client: 'Juan Dela Cruz',
          clientLocation: 'Bacoor, Cavite',
          project: 'Bedroom Renovation',
          roomType: 'Master Bedroom',
          status: 'Approved',
          date: 'Feb 15, 2024',
          time: '10:30 AM'
        },
        {
          id: 2,
          name: 'Sage Green',
          hex: '#8A9A5B',
          client: 'Maria Gonzales',
          clientLocation: 'Imus, Cavite',
          project: 'Living Room Update',
          roomType: 'Living Room',
          status: 'In Progress',
          date: 'Feb 14, 2024',
          time: '2:15 PM'
        },
        {
          id: 3,
          name: 'Coral Sunset',
          hex: '#FF7F50',
          client: 'Robert Lim',
          clientLocation: 'Dasmarinas, Cavite',
          project: 'Kitchen Remodel',
          roomType: 'Kitchen',
          status: 'Pending',
          date: 'Feb 13, 2024',
          time: '4:45 PM'
        },
        {
          id: 4,
          name: 'Midnight Blue',
          hex: '#191970',
          client: 'Anna Santos',
          clientLocation: 'General Trias',
          project: 'Office Space',
          roomType: 'Home Office',
          status: 'Approved',
          date: 'Feb 12, 2024',
          time: '9:20 AM'
        },
        {
          id: 5,
          name: 'Soft Lavender',
          hex: '#B19CD9',
          client: 'Carlos Reyes',
          clientLocation: 'Trece Martires',
          project: 'Nursery Design',
          roomType: 'Baby Room',
          status: 'In Progress',
          date: 'Feb 11, 2024',
          time: '3:00 PM'
        }
      ],
      recentActivities: [
        {
          id: 1,
          type: 'color',
          icon: 'PaletteIcon',
          title: 'Created new color mix',
          description: 'Designed "Ocean Breeze" for Juan Dela Cruz',
          time: '2 hours ago'
        },
        {
          id: 2,
          type: 'job',
          icon: 'JobIcon',
          title: 'Completed service job',
          description: 'Finished painting project at Gonzales residence',
          time: '5 hours ago'
        },
        {
          id: 3,
          type: 'client',
          icon: 'ClientIcon',
          title: 'New client onboarded',
          description: 'Anna Santos signed up for office painting',
          time: 'Yesterday'
        },
        {
          id: 4,
          type: 'payment',
          icon: 'PaymentIcon',
          title: 'Payment received',
          description: '₱25,000 from Lim Kitchen project',
          time: '2 days ago'
        }
      ]
    }
  },
  components: {
    PaletteIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
      </svg>`
    },
    JobIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
      </svg>`
    },
    ClientIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>`
    },
    PaymentIcon: {
      template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>`
    }
  },
  mounted() {
    this.updateDateTime();
    this.dateTimeInterval = setInterval(this.updateDateTime, 60000); // Update every minute
  },
  beforeUnmount() {
    if (this.dateTimeInterval) {
      clearInterval(this.dateTimeInterval);
    }
  },
  methods: {
    updateDateTime() {
      const now = new Date();
      this.currentDate = now.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      this.currentTime = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    getInitials(name) {
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
    },
    openColorMixer() {
      // This would typically navigate to the Unity color mixer route
      this.$router.push('/service-provider/color-mixer');
      console.log('Opening Unity Virtual Paint Color Mixer...');
    },
    createNewClient() {
      console.log('Creating new client...');
      // Implement new client creation
    },
    createServiceRequest() {
      console.log('Creating new service request...');
      // Implement new service request
    },
    viewReports() {
      console.log('Viewing reports...');
      // Navigate to reports
    },
    checkInventory() {
      console.log('Checking inventory...');
      // Navigate to inventory
    }
  }
}
</script>

<style scoped>
.dashboard-container {
  background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
  min-height: 100vh;
  overflow-x: hidden;
}

/* Stat Cards */
.stat-card {
  padding: 1.25rem;
  border-radius: 1rem;
  transition: all 0.3s ease;
  cursor: pointer;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
  transition: all 0.3s ease;
}

.stat-card:hover .stat-icon {
  transform: scale(1.1);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4);
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.025em;
  white-space: nowrap;
}

.status-approved {
  background: linear-gradient(45deg, #10b981, #34d399);
  color: white;
}

.status-pending {
  background: linear-gradient(45deg, #f59e0b, #fbbf24);
  color: white;
}

.status-inprogress {
  background: linear-gradient(45deg, #3b82f6, #60a5fa);
  color: white;
}

/* Unity Button */
.unity-button {
  background: linear-gradient(45deg, #8b5cf6, #ec4899);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.unity-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
}

.unity-button:active {
  transform: translateY(0);
}

/* Activity Items */
.activity-item {
  padding-bottom: 1.5rem;
  border-bottom: 1px solid rgba(75, 85, 99, 0.3);
}

.activity-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-color {
  background: linear-gradient(45deg, rgba(139, 92, 246, 0.2), rgba(236, 72, 153, 0.2));
  color: #a78bfa;
  border: 1px solid rgba(139, 92, 246, 0.3);
}

.icon-job {
  background: linear-gradient(45deg, rgba(245, 158, 11, 0.2), rgba(251, 191, 36, 0.2));
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.icon-client {
  background: linear-gradient(45deg, rgba(16, 185, 129, 0.2), rgba(52, 211, 153, 0.2));
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.icon-payment {
  background: linear-gradient(45deg, rgba(59, 130, 246, 0.2), rgba(96, 165, 250, 0.2));
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

/* Quick Action Buttons */
.quick-action-button {
  padding: 1rem;
  border-radius: 0.75rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  min-height: 100px;
}

.quick-action-button:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(17, 24, 39, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(45deg, #2563eb, #7c3aed);
}

/* Responsive Design */
@media (max-width: 768px) {
  .dashboard-container {
    padding: 1rem;
  }
  
  .stat-card {
    padding: 1rem;
  }
  
  .unity-button {
    width: 100%;
    margin-top: 1rem;
  }
  
  table {
    font-size: 0.875rem;
  }
  
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .quick-action-button {
    min-height: 90px;
    padding: 0.75rem;
  }
}

@media (max-width: 640px) {
  .grid-cols-4 {
    grid-template-columns: repeat(1, 1fr);
  }
  
  .quick-action-button {
    min-height: 80px;
  }
}

/* Animation for new items */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.stat-card, .activity-item, .quick-action-button {
  animation: fadeInUp 0.5s ease-out;
}

/* Utility classes */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.min-w-0 {
  min-width: 0;
}

.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* For mobile responsiveness */
@media (max-width: 768px) {
  .text-3xl {
    font-size: 1.75rem;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .text-xl {
    font-size: 1.25rem;
  }
}
</style>