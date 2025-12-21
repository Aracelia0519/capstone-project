<template>
  <div class="p-4 md:p-6">
    <!-- Page Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Service Providers</h1>
          <p class="text-gray-600 mt-2">Manage relationships with paint service providers in Cavite</p>
        </div>
        <div class="flex items-center space-x-3">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search providers..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full lg:w-64"
            >
          </div>
          <div class="hidden md:flex items-center space-x-2">
            <button @click="exportProviders" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
              Export
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center">
          <div class="p-3 bg-blue-50 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-600">Total Providers</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.totalProviders }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center">
          <div class="p-3 bg-green-50 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-600">Active Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.activeOrders }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center">
          <div class="p-3 bg-yellow-50 rounded-lg mr-4">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-600">Avg. Response Time</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.avgResponseTime }}h</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center">
          <div class="p-3 bg-purple-50 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-600">Satisfaction Score</p>
            <p class="text-2xl font-bold text-gray-900">{{ statistics.satisfactionScore }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap gap-3">
          <button 
            v-for="filter in statusFilters" 
            :key="filter.id"
            @click="setStatusFilter(filter.id)"
            :class="activeStatusFilter === filter.id ? 'bg-blue-100 text-blue-700 border-blue-300' : 'text-gray-700 border-gray-300'"
            class="px-4 py-2 border rounded-lg text-sm font-medium transition-colors hover:bg-gray-50 flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="filter.id === 'all'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
              <path v-else-if="filter.id === 'active'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            {{ filter.label }}
          </button>
        </div>
        <div class="flex items-center space-x-3">
          <select v-model="sortBy" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="name">Sort by Name</option>
            <option value="orders">Sort by Orders</option>
            <option value="rating">Sort by Rating</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Providers Grid/Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <!-- Desktop Table -->
      <div class="hidden lg:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Service Provider</th>
              <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Contact Info</th>
              <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Total Orders</th>
              <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Activity Status</th>
              <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Rating</th>
              <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="provider in filteredProviders" :key="provider.id" class="hover:bg-gray-50">
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center border border-blue-200">
                    <span class="text-lg font-semibold text-blue-700">{{ getInitials(provider.name) }}</span>
                  </div>
                  <div class="ml-4">
                    <h3 class="font-semibold text-gray-900">{{ provider.name }}</h3>
                    <p class="text-sm text-gray-600">{{ provider.specialization }}</p>
                    <div class="flex items-center mt-1">
                      <svg class="w-4 h-4 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                      <span class="text-xs text-gray-500">{{ provider.location }}</span>
                    </div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="space-y-2">
                  <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <a :href="`mailto:${provider.email}`" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                      {{ provider.email }}
                    </a>
                  </div>
                  <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span class="text-sm text-gray-700">{{ provider.phone }}</span>
                  </div>
                  <div v-if="provider.website" class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                    <a :href="provider.website" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                      {{ provider.website }}
                    </a>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-baseline">
                  <span class="text-2xl font-bold text-gray-900">{{ provider.totalOrders }}</span>
                  <span class="text-sm text-gray-500 ml-1">orders</span>
                </div>
                <div class="mt-2">
                  <div class="flex items-center text-sm">
                    <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span class="text-gray-600">₱{{ formatCurrency(provider.totalValue) }}</span>
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ provider.activeOrders }} active
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="relative">
                    <div class="w-3 h-3 rounded-full" :class="getStatusColor(provider.status)"></div>
                    <div v-if="provider.status === 'active'" class="absolute inset-0 animate-ping rounded-full" :class="getStatusColor(provider.status)"></div>
                  </div>
                  <span class="ml-2 text-sm font-medium" :class="getStatusTextClass(provider.status)">
                    {{ provider.status.charAt(0).toUpperCase() + provider.status.slice(1) }}
                  </span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                  Last active: {{ formatDate(provider.lastActive) }}
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center">
                  <div class="flex items-center">
                    <svg v-for="n in 5" :key="n" class="w-4 h-4" :class="n <= Math.floor(provider.rating) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  </div>
                  <span class="ml-2 text-sm font-medium text-gray-900">{{ provider.rating.toFixed(1) }}</span>
                  <span class="text-xs text-gray-500 ml-1">({{ provider.reviews }} reviews)</span>
                </div>
                <div class="mt-2">
                  <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full" :style="{ width: (provider.rating / 5 * 100) + '%' }"></div>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center space-x-2">
                  <button @click="viewDetails(provider)" class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Details">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </button>
                  <button @click="contactProvider(provider)" class="p-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Contact">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                  </button>
                  <button @click="viewOrders(provider)" class="p-2 text-gray-600 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="View Orders">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div class="lg:hidden">
        <div class="p-4 space-y-4">
          <div v-for="provider in filteredProviders" :key="provider.id" class="bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
              <div class="flex items-start">
                <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center border border-blue-200">
                  <span class="text-lg font-semibold text-blue-700">{{ getInitials(provider.name) }}</span>
                </div>
                <div class="ml-4">
                  <h3 class="font-semibold text-gray-900 text-lg">{{ provider.name }}</h3>
                  <p class="text-sm text-gray-600">{{ provider.specialization }}</p>
                  <div class="flex items-center mt-1">
                    <div class="flex items-center mr-3">
                      <div class="w-2 h-2 rounded-full mr-1" :class="getStatusColor(provider.status)"></div>
                      <span class="text-xs font-medium" :class="getStatusTextClass(provider.status)">
                        {{ provider.status }}
                      </span>
                    </div>
                    <div class="flex items-center">
                      <svg class="w-3 h-3 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                      </svg>
                      <span class="text-xs text-gray-500">{{ provider.location }}</span>
                    </div>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-1">
                <button @click="viewDetails(provider)" class="p-1.5 text-gray-400 hover:text-blue-600">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-gray-500">Contact</p>
                <div class="mt-1 space-y-1">
                  <a :href="`mailto:${provider.email}`" class="text-sm text-blue-600 hover:text-blue-800 hover:underline block truncate">
                    {{ provider.email }}
                  </a>
                  <span class="text-sm text-gray-700 block">{{ provider.phone }}</span>
                </div>
              </div>
              <div>
                <p class="text-xs text-gray-500">Orders</p>
                <div class="mt-1">
                  <div class="flex items-baseline">
                    <span class="text-xl font-bold text-gray-900">{{ provider.totalOrders }}</span>
                    <span class="text-sm text-gray-500 ml-1">total</span>
                  </div>
                  <div class="text-xs text-gray-600 mt-1">{{ provider.activeOrders }} active orders</div>
                </div>
              </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex items-center justify-between">
                <div class="flex items-center">
                  <div class="flex items-center mr-3">
                    <svg v-for="n in 5" :key="n" class="w-4 h-4" :class="n <= Math.floor(provider.rating) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  </div>
                  <span class="text-sm font-medium text-gray-900">{{ provider.rating.toFixed(1) }}</span>
                </div>
                <div class="flex space-x-2">
                  <button @click="contactProvider(provider)" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">
                    Contact
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredProviders.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No service providers found</h3>
        <p class="text-gray-500 mb-4">Try adjusting your search or filter</p>
        <button @click="resetFilters" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          Reset Filters
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="filteredProviders.length > 0" class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
      <div class="text-sm text-gray-700 mb-4 sm:mb-0">
        Showing <span class="font-medium">{{ filteredProviders.length }}</span> of <span class="font-medium">{{ providers.length }}</span> service providers
      </div>
      <div class="flex items-center space-x-2">
        <button @click="previousPage" :disabled="currentPage === 1" 
          class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Previous
        </button>
        <span class="text-sm text-gray-700 px-3">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button @click="nextPage" :disabled="currentPage === totalPages"
          class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
          Next
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ServiceProviders',
  data() {
    return {
      searchQuery: '',
      activeStatusFilter: 'all',
      sortBy: 'name',
      currentPage: 1,
      itemsPerPage: 10,
      statusFilters: [
        { id: 'all', label: 'All Providers' },
        { id: 'active', label: 'Active' },
        { id: 'inactive', label: 'Inactive' },
        { id: 'busy', label: 'Busy' }
      ],
      statistics: {
        totalProviders: 24,
        activeOrders: 156,
        avgResponseTime: '2.5',
        satisfactionScore: '94.2'
      },
      providers: [
        {
          id: 1,
          name: 'Cavite Paint Masters',
          specialization: 'Interior & Exterior Painting',
          location: 'Imus, Cavite',
          email: 'info@cavitepaintmasters.ph',
          phone: '+63 912 345 6789',
          website: 'www.cavitepaintmasters.ph',
          totalOrders: 245,
          activeOrders: 12,
          totalValue: 1250000,
          status: 'active',
          lastActive: '2024-01-15',
          rating: 4.8,
          reviews: 89
        },
        {
          id: 2,
          name: 'Imus Color Experts',
          specialization: 'Residential Painting',
          location: 'Imus, Cavite',
          email: 'contact@imuscolorexperts.ph',
          phone: '+63 917 654 3210',
          website: null,
          totalOrders: 189,
          activeOrders: 8,
          totalValue: 980000,
          status: 'busy',
          lastActive: '2024-01-14',
          rating: 4.6,
          reviews: 67
        },
        {
          id: 3,
          name: 'Bacoor Painting Services',
          specialization: 'Commercial Painting',
          location: 'Bacoor, Cavite',
          email: 'service@bacoorpainters.ph',
          phone: '+63 918 765 4321',
          website: 'www.bacoorpaintingservices.ph',
          totalOrders: 312,
          activeOrders: 15,
          totalValue: 1850000,
          status: 'active',
          lastActive: '2024-01-15',
          rating: 4.9,
          reviews: 124
        },
        {
          id: 4,
          name: 'Dasmarinas Paint Pros',
          specialization: 'Industrial Coating',
          location: 'Dasmarinas, Cavite',
          email: 'pros@dasmarinaspaint.ph',
          phone: '+63 919 876 5432',
          website: null,
          totalOrders: 156,
          activeOrders: 6,
          totalValue: 850000,
          status: 'inactive',
          lastActive: '2024-01-10',
          rating: 4.4,
          reviews: 45
        },
        {
          id: 5,
          name: 'Trece Martires Contractors',
          specialization: 'Government Projects',
          location: 'Trece Martires, Cavite',
          email: 'contracts@trecemartires.ph',
          phone: '+63 920 987 6543',
          website: 'www.trececontractors.ph',
          totalOrders: 278,
          activeOrders: 18,
          totalValue: 2100000,
          status: 'active',
          lastActive: '2024-01-15',
          rating: 4.7,
          reviews: 92
        },
        {
          id: 6,
          name: 'Silang Painting Specialists',
          specialization: 'Heritage Restoration',
          location: 'Silang, Cavite',
          email: 'restore@silangpaint.ph',
          phone: '+63 921 234 5678',
          website: null,
          totalOrders: 134,
          activeOrders: 4,
          totalValue: 720000,
          status: 'busy',
          lastActive: '2024-01-13',
          rating: 4.5,
          reviews: 56
        },
        {
          id: 7,
          name: 'General Trias Paint Works',
          specialization: 'New Construction',
          location: 'General Trias, Cavite',
          email: 'works@gentripaint.ph',
          phone: '+63 922 345 6789',
          website: 'www.gentripaintworks.ph',
          totalOrders: 201,
          activeOrders: 11,
          totalValue: 1150000,
          status: 'active',
          lastActive: '2024-01-14',
          rating: 4.8,
          reviews: 78
        },
        {
          id: 8,
          name: 'Kawit Color Lab',
          specialization: 'Color Consultation',
          location: 'Kawit, Cavite',
          email: 'lab@kawitcolor.ph',
          phone: '+63 923 456 7890',
          website: null,
          totalOrders: 98,
          activeOrders: 3,
          totalValue: 450000,
          status: 'inactive',
          lastActive: '2024-01-08',
          rating: 4.3,
          reviews: 34
        },
        {
          id: 9,
          name: 'Naic Painting Co.',
          specialization: 'Waterproofing & Sealants',
          location: 'Naic, Cavite',
          email: 'co@naicpainting.ph',
          phone: '+63 924 567 8901',
          website: 'www.naicpaintingco.ph',
          totalOrders: 167,
          activeOrders: 9,
          totalValue: 920000,
          status: 'active',
          lastActive: '2024-01-15',
          rating: 4.6,
          reviews: 61
        },
        {
          id: 10,
          name: 'Tagaytay Premium Painters',
          specialization: 'Luxury Properties',
          location: 'Tagaytay, Cavite',
          email: 'premium@tagaytaypaint.ph',
          phone: '+63 925 678 9012',
          website: 'www.tagaytaypainters.ph',
          totalOrders: 123,
          activeOrders: 5,
          totalValue: 680000,
          status: 'busy',
          lastActive: '2024-01-14',
          rating: 4.9,
          reviews: 42
        }
      ]
    }
  },
  computed: {
    filteredProviders() {
      let filtered = this.providers.filter(provider => {
        // Search filter
        const matchesSearch = !this.searchQuery || 
          provider.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          provider.specialization.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          provider.location.toLowerCase().includes(this.searchQuery.toLowerCase())
        
        // Status filter
        const matchesStatus = this.activeStatusFilter === 'all' || 
          provider.status === this.activeStatusFilter
        
        return matchesSearch && matchesStatus
      })
      
      // Sort
      filtered.sort((a, b) => {
        if (this.sortBy === 'name') {
          return a.name.localeCompare(b.name)
        } else if (this.sortBy === 'orders') {
          return b.totalOrders - a.totalOrders
        } else if (this.sortBy === 'rating') {
          return b.rating - a.rating
        }
        return 0
      })
      
      // Pagination
      const startIndex = (this.currentPage - 1) * this.itemsPerPage
      return filtered.slice(startIndex, startIndex + this.itemsPerPage)
    },
    totalPages() {
      const totalFiltered = this.providers.filter(provider => {
        const matchesSearch = !this.searchQuery || 
          provider.name.toLowerCase().includes(this.searchQuery.toLowerCase())
        const matchesStatus = this.activeStatusFilter === 'all' || 
          provider.status === this.activeStatusFilter
        return matchesSearch && matchesStatus
      }).length
      
      return Math.ceil(totalFiltered / this.itemsPerPage)
    }
  },
  methods: {
    getInitials(name) {
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    },
    getStatusColor(status) {
      const colors = {
        active: 'bg-green-500',
        inactive: 'bg-gray-400',
        busy: 'bg-yellow-500'
      }
      return colors[status] || 'bg-gray-400'
    },
    getStatusTextClass(status) {
      const classes = {
        active: 'text-green-700',
        inactive: 'text-gray-700',
        busy: 'text-yellow-700'
      }
      return classes[status] || 'text-gray-700'
    },
    formatDate(dateString) {
      const date = new Date(dateString)
      const now = new Date()
      const diffDays = Math.floor((now - date) / (1000 * 60 * 60 * 24))
      
      if (diffDays === 0) return 'Today'
      if (diffDays === 1) return 'Yesterday'
      if (diffDays < 7) return `${diffDays} days ago`
      
      return date.toLocaleDateString('en-PH', {
        month: 'short',
        day: 'numeric'
      })
    },
    formatCurrency(amount) {
      if (amount >= 1000000) {
        return `₱${(amount / 1000000).toFixed(1)}M`
      } else if (amount >= 1000) {
        return `₱${(amount / 1000).toFixed(0)}K`
      }
      return `₱${amount.toLocaleString()}`
    },
    setStatusFilter(status) {
      this.activeStatusFilter = status
      this.currentPage = 1
    },
    resetFilters() {
      this.searchQuery = ''
      this.activeStatusFilter = 'all'
      this.sortBy = 'name'
      this.currentPage = 1
    },
    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--
      }
    },
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++
      }
    },
    viewDetails(provider) {
      // In a real app, this would navigate to a details page
      console.log('Viewing details for:', provider.name)
      alert(`Viewing details for ${provider.name}\nSpecialization: ${provider.specialization}\nLocation: ${provider.location}`)
    },
    contactProvider(provider) {
      // In a real app, this would open a contact form or initiate communication
      console.log('Contacting provider:', provider.name)
      alert(`Initiating contact with ${provider.name}\nEmail: ${provider.email}\nPhone: ${provider.phone}`)
    },
    viewOrders(provider) {
      // In a real app, this would show orders for this provider
      console.log('Viewing orders for:', provider.name)
      alert(`Showing orders for ${provider.name}\nTotal Orders: ${provider.totalOrders}\nActive Orders: ${provider.activeOrders}`)
    },
    exportProviders() {
      const data = {
        exportDate: new Date().toISOString(),
        filters: {
          search: this.searchQuery,
          status: this.activeStatusFilter,
          sortBy: this.sortBy
        },
        providers: this.filteredProviders,
        statistics: this.statistics
      }
      console.log('Exporting providers data:', data)
      alert('Service providers data exported (check console for data structure)')
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar */
.overflow-x-auto {
  -webkit-overflow-scrolling: touch;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Avatar gradient animation */
@keyframes gradientShift {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.bg-gradient-to-br {
  background-size: 200% 200%;
}

/* Status indicator animation */
@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}

.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}

/* Card hover effects */
.hover\:shadow-md {
  transition: all 0.2s ease-in-out;
}

.hover\:shadow-md:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Mobile optimizations */
@media (max-width: 1024px) {
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .grid-cols-4 {
    grid-template-columns: 1fr;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .text-3xl {
    font-size: 1.75rem;
  }
}

/* Loading skeleton animation */
@keyframes shimmer {
  0% {
    background-position: -468px 0;
  }
  100% {
    background-position: 468px 0;
  }
}

.shimmer {
  background: linear-gradient(to right, #f6f7f8 8%, #edeef1 18%, #f6f7f8 33%);
  background-size: 800px 104px;
  animation: shimmer 1.5s infinite linear;
}

/* Print styles */
@media print {
  button, select, input {
    display: none !important;
  }
  
  .bg-white {
    background: white !important;
    border: 1px solid #e5e7eb !important;
  }
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Focus styles for accessibility */
:focus {
  outline: none;
  ring: 2px solid #3b82f6;
  ring-offset: 2px;
}

/* Rating stars spacing */
svg + svg {
  margin-left: 0.125rem;
}

/* Link hover effects */
a:hover {
  text-decoration: underline;
}

/* Responsive typography */
@media (max-width: 768px) {
  .text-lg {
    font-size: 1rem;
  }
  
  .text-xl {
    font-size: 1.125rem;
  }
}
</style>