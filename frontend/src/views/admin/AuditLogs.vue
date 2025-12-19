<template>
  <div class="audit-logs p-6">
    <!-- Page Header -->
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-800 mb-2">Audit Logs</h1>
          <p class="text-gray-600">System activity tracking and accountability records</p>
        </div>
        <div class="flex flex-wrap gap-3 mt-4 md:mt-0">
          <button 
            @click="clearOldLogs"
            class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 rounded-lg text-sm font-medium bg-white hover:bg-red-50 transition-colors"
          >
            <i class="fas fa-trash-alt mr-2"></i>
            Clear Old Logs
          </button>
          <button 
            @click="exportLogs"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 transition-colors"
          >
            <i class="fas fa-file-export mr-2"></i>
            Export Logs
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-blue-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Total Logs</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ stats.totalLogs }}</h3>
            </div>
            <div class="p-3 bg-blue-50 rounded-lg">
              <i class="fas fa-history text-blue-500 text-xl"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-green-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Today's Activity</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ stats.todaysLogs }}</h3>
            </div>
            <div class="p-3 bg-green-50 rounded-lg">
              <i class="fas fa-calendar-day text-green-500 text-xl"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-purple-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Active Users</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ stats.activeUsers }}</h3>
            </div>
            <div class="p-3 bg-purple-50 rounded-lg">
              <i class="fas fa-users text-purple-500 text-xl"></i>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-xl p-5 shadow-md border-l-4 border-yellow-500">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">Security Events</p>
              <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ stats.securityEvents }}</h3>
            </div>
            <div class="p-3 bg-yellow-50 rounded-lg">
              <i class="fas fa-shield-alt text-yellow-500 text-xl"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl p-4 shadow-sm mb-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-col md:flex-row gap-4 flex-1">
          <div class="relative flex-1 md:max-w-md">
            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search users, actions, or details..."
              class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full"
            >
          </div>
          
          <div class="flex flex-wrap gap-3">
            <select 
              v-model="selectedUser"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
              <option value="all">All Users</option>
              <option v-for="user in uniqueUsers" :value="user">{{ user }}</option>
            </select>
            
            <select 
              v-model="selectedAction"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
              <option value="all">All Actions</option>
              <option v-for="action in uniqueActions" :value="action">{{ formatAction(action) }}</option>
            </select>
            
            <select 
              v-model="selectedSeverity"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
            >
              <option value="all">All Severity</option>
              <option value="info">Info</option>
              <option value="warning">Warning</option>
              <option value="critical">Critical</option>
            </select>
          </div>
        </div>
        
        <div class="flex items-center gap-2 text-sm text-gray-600">
          <i class="fas fa-info-circle"></i>
          <span>Showing last {{ timeRange }} days</span>
        </div>
      </div>
      
      <!-- Time Range Filter -->
      <div class="mt-4 flex items-center gap-4">
        <div class="text-sm text-gray-600">Time Range:</div>
        <div class="flex flex-wrap gap-2">
          <button 
            v-for="range in timeRanges" 
            :key="range.value"
            @click="setTimeRange(range.value)"
            class="px-3 py-1 text-sm rounded-lg transition-colors"
            :class="timeRange === range.value 
              ? 'bg-blue-600 text-white' 
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
          >
            {{ range.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-xl shadow overflow-hidden">
      <div class="border-b border-gray-200 px-6 py-4 bg-gray-50">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <i class="fas fa-clipboard-list text-gray-500 text-lg mr-3"></i>
            <h3 class="text-lg font-semibold text-gray-800">System Audit Trail</h3>
          </div>
          <div class="flex items-center gap-3">
            <div class="text-xs text-gray-500">
              Auto-refresh: 
              <span class="font-medium" :class="autoRefresh ? 'text-green-600' : 'text-gray-400'">
                {{ autoRefresh ? 'ON' : 'OFF' }}
              </span>
            </div>
            <button 
              @click="toggleAutoRefresh"
              class="text-sm text-blue-600 hover:text-blue-800"
            >
              <i class="fas fa-sync-alt"></i>
            </button>
          </div>
        </div>
        <p class="text-sm text-gray-500 mt-1">Complete record of all system activities and user actions</p>
      </div>
      
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-user"></i>
                  <span>User</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-bolt"></i>
                  <span>Action</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-calendar-alt"></i>
                  <span>Timestamp</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-info-circle"></i>
                  <span>Details</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-exclamation-triangle"></i>
                  <span>Severity</span>
                </div>
              </th>
              <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                <div class="flex items-center gap-2">
                  <i class="fas fa-map-marker-alt"></i>
                  <span>IP Address</span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr 
              v-for="log in filteredLogs" 
              :key="log.id"
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div 
                    class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center"
                    :class="getUserRoleClass(log.userRole)"
                  >
                    <i :class="getUserIcon(log.userRole)" class="text-white"></i>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ log.userName }}</div>
                    <div class="text-xs text-gray-500">{{ log.userRole }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="mr-3">
                    <i :class="getActionIcon(log.actionType)" class="text-lg" :style="{ color: getActionColor(log.actionType) }"></i>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ formatAction(log.actionType) }}</div>
                    <div class="text-xs text-gray-500">{{ log.entityType }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(log.timestamp) }}</div>
                <div class="text-xs text-gray-500">{{ formatTime(log.timestamp) }}</div>
                <div class="text-xs text-gray-400">{{ getTimeAgo(log.timestamp) }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate">{{ log.details }}</div>
                <div v-if="log.entityId" class="text-xs text-gray-500">
                  ID: {{ log.entityId }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span 
                  class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                  :class="getSeverityClass(log.severity)"
                >
                  <i :class="getSeverityIcon(log.severity)" class="mr-1"></i>
                  {{ log.severity.charAt(0).toUpperCase() + log.severity.slice(1) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-mono text-gray-900">{{ log.ipAddress }}</div>
                <div class="text-xs text-gray-500">{{ log.browser }}</div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Empty State -->
      <div v-if="filteredLogs.length === 0" class="text-center py-16">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <i class="fas fa-search text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No audit logs found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">Try adjusting your search or filter criteria.</p>
      </div>
      
      <!-- Table Footer -->
      <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div class="text-sm text-gray-500 mb-2 md:mb-0">
            Showing <span class="font-medium">{{ filteredLogs.length }}</span> of 
            <span class="font-medium">{{ auditLogs.length }}</span> audit logs
            <span v-if="filteredLogs.length < auditLogs.length" class="ml-2 text-blue-600">
              (filtered)
            </span>
          </div>
          <div class="flex items-center gap-4">
            <button 
              @click="loadMoreLogs"
              class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
            >
              <i class="fas fa-plus mr-2"></i>
              Load More
            </button>
            <div class="text-xs text-gray-500">
              <i class="fas fa-database mr-1"></i>
              Log retention: 90 days
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Information Panel -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Activity Summary -->
      <div class="bg-white rounded-xl shadow p-6">
        <div class="flex items-center mb-4">
          <i class="fas fa-chart-pie text-blue-500 text-lg mr-3"></i>
          <h4 class="font-bold text-gray-800">Activity Summary</h4>
        </div>
        <div class="space-y-4">
          <div 
            v-for="activity in activitySummary" 
            :key="activity.type"
            class="flex items-center justify-between"
          >
            <div class="flex items-center">
              <div class="w-3 h-3 rounded-full mr-3" :style="{ backgroundColor: activity.color }"></div>
              <span class="text-sm text-gray-700">{{ activity.label }}</span>
            </div>
            <div class="text-sm font-medium text-gray-900">{{ activity.count }} events</div>
          </div>
        </div>
        <div class="mt-6 pt-6 border-t border-gray-100">
          <div class="text-xs text-gray-500">
            <i class="fas fa-clock mr-1"></i>
            Last updated: {{ lastUpdated }}
          </div>
        </div>
      </div>

      <!-- Security Notice -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-start mb-4">
          <div class="flex-shrink-0">
            <i class="fas fa-shield-alt text-blue-500 text-2xl"></i>
          </div>
          <div class="ml-4">
            <h4 class="font-bold text-blue-900">Security & Compliance</h4>
            <p class="text-blue-700 text-sm mt-2">
              Audit logs provide accountability and are essential for security monitoring, 
              compliance requirements, and troubleshooting system issues.
            </p>
          </div>
        </div>
        <ul class="text-sm text-blue-700 space-y-2 mt-4">
          <li class="flex items-start">
            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
            <span>All user actions are logged for accountability</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
            <span>Logs are retained for 90 days for compliance</span>
          </li>
          <li class="flex items-start">
            <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
            <span>Critical security events trigger alerts</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'

// Audit logs data
const auditLogs = ref([
  {
    id: 1,
    userName: 'System Admin',
    userRole: 'admin',
    actionType: 'login',
    entityType: 'User',
    entityId: 'ADM001',
    details: 'User logged in successfully',
    severity: 'info',
    timestamp: new Date(Date.now() - 300000).toISOString(), // 5 mins ago
    ipAddress: '192.168.1.100',
    browser: 'Chrome 120'
  },
  {
    id: 2,
    userName: 'John Painters Inc.',
    userRole: 'service_provider',
    actionType: 'create',
    entityType: 'Service Request',
    entityId: 'SR-045',
    details: 'Created new service request for client Maria Santos',
    severity: 'info',
    timestamp: new Date(Date.now() - 900000).toISOString(), // 15 mins ago
    ipAddress: '203.145.67.89',
    browser: 'Firefox 121'
  },
  {
    id: 3,
    userName: 'ColorCraft Supplies',
    userRole: 'distributor',
    actionType: 'update',
    entityType: 'Inventory',
    entityId: 'INV-789',
    details: 'Updated paint inventory quantity for Ocean Blue',
    severity: 'info',
    timestamp: new Date(Date.now() - 1800000).toISOString(), // 30 mins ago
    ipAddress: '175.158.32.45',
    browser: 'Safari 17'
  },
  {
    id: 4,
    userName: 'System Admin',
    userRole: 'admin',
    actionType: 'delete',
    entityType: 'User',
    entityId: 'USR-023',
    details: 'Deleted inactive user account',
    severity: 'warning',
    timestamp: new Date(Date.now() - 3600000).toISOString(), // 1 hour ago
    ipAddress: '192.168.1.100',
    browser: 'Chrome 120'
  },
  {
    id: 5,
    userName: 'Premium Paint Pros',
    userRole: 'service_provider',
    actionType: 'color_mix',
    entityType: 'Color Customization',
    entityId: 'COL-156',
    details: 'Generated new color formula: Sunset Orange variant',
    severity: 'info',
    timestamp: new Date(Date.now() - 7200000).toISOString(), // 2 hours ago
    ipAddress: '210.187.54.32',
    browser: 'Edge 120'
  },
  {
    id: 6,
    userName: 'Unknown',
    userRole: 'unknown',
    actionType: 'failed_login',
    entityType: 'Security',
    entityId: null,
    details: 'Failed login attempt with invalid credentials',
    severity: 'critical',
    timestamp: new Date(Date.now() - 10800000).toISOString(), // 3 hours ago
    ipAddress: '45.76.189.223',
    browser: 'Unknown'
  },
  {
    id: 7,
    userName: 'Brush Masters Co.',
    userRole: 'distributor',
    actionType: 'export',
    entityType: 'Report',
    entityId: 'REP-2024-01',
    details: 'Exported monthly sales report',
    severity: 'info',
    timestamp: new Date(Date.now() - 14400000).toISOString(), // 4 hours ago
    ipAddress: '189.204.76.33',
    browser: 'Chrome 119'
  },
  {
    id: 8,
    userName: 'System Admin',
    userRole: 'admin',
    actionType: 'system_update',
    entityType: 'System',
    entityId: null,
    details: 'Applied security patches to user management module',
    severity: 'info',
    timestamp: new Date(Date.now() - 21600000).toISOString(), // 6 hours ago
    ipAddress: '192.168.1.100',
    browser: 'Chrome 120'
  },
  {
    id: 9,
    userName: 'Maria Santos',
    userRole: 'client',
    actionType: 'view',
    entityType: 'Color Preview',
    entityId: 'COL-142',
    details: 'Viewed virtual paint color preview on living room wall',
    severity: 'info',
    timestamp: new Date(Date.now() - 28800000).toISOString(), // 8 hours ago
    ipAddress: '112.198.65.12',
    browser: 'Mobile Safari'
  },
  {
    id: 10,
    userName: 'System Auto',
    userRole: 'system',
    actionType: 'backup',
    entityType: 'Database',
    entityId: 'DB-20240115',
    details: 'Automated daily database backup completed successfully',
    severity: 'info',
    timestamp: new Date(Date.now() - 43200000).toISOString(), // 12 hours ago
    ipAddress: '127.0.0.1',
    browser: 'System Process'
  }
])

// Statistics
const stats = ref({
  totalLogs: 1247,
  todaysLogs: 42,
  activeUsers: 18,
  securityEvents: 7
})

// Filters
const searchQuery = ref('')
const selectedUser = ref('all')
const selectedAction = ref('all')
const selectedSeverity = ref('all')
const timeRange = ref(7)
const autoRefresh = ref(false)

// Time ranges for filtering
const timeRanges = [
  { label: 'Today', value: 1 },
  { label: '7 Days', value: 7 },
  { label: '30 Days', value: 30 },
  { label: 'All Time', value: 365 }
]

// Computed properties
const uniqueUsers = computed(() => {
  const users = new Set(auditLogs.value.map(log => log.userName))
  return Array.from(users).sort()
})

const uniqueActions = computed(() => {
  const actions = new Set(auditLogs.value.map(log => log.actionType))
  return Array.from(actions).sort()
})

const filteredLogs = computed(() => {
  let filtered = auditLogs.value
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(log => 
      log.userName.toLowerCase().includes(query) ||
      log.actionType.toLowerCase().includes(query) ||
      log.details.toLowerCase().includes(query) ||
      log.entityType.toLowerCase().includes(query)
    )
  }
  
  // Apply user filter
  if (selectedUser.value !== 'all') {
    filtered = filtered.filter(log => log.userName === selectedUser.value)
  }
  
  // Apply action filter
  if (selectedAction.value !== 'all') {
    filtered = filtered.filter(log => log.actionType === selectedAction.value)
  }
  
  // Apply severity filter
  if (selectedSeverity.value !== 'all') {
    filtered = filtered.filter(log => log.severity === selectedSeverity.value)
  }
  
  return filtered
})

const activitySummary = computed(() => {
  const summary = {
    'login': { label: 'User Logins', count: 0, color: '#4A90E2' },
    'create': { label: 'Create Actions', count: 0, color: '#2ECC71' },
    'update': { label: 'Update Actions', count: 0, color: '#F1C40F' },
    'delete': { label: 'Delete Actions', count: 0, color: '#E74C3C' },
    'security': { label: 'Security Events', count: 0, color: '#9B59B6' }
  }
  
  auditLogs.value.forEach(log => {
    if (log.actionType.includes('login')) summary.login.count++
    else if (log.actionType.includes('create')) summary.create.count++
    else if (log.actionType.includes('update')) summary.update.count++
    else if (log.actionType.includes('delete')) summary.delete.count++
    if (log.severity === 'critical' || log.actionType.includes('failed')) summary.security.count++
  })
  
  return Object.values(summary)
})

const lastUpdated = computed(() => {
  return new Date().toLocaleTimeString('en-US', { 
    hour: '2-digit', 
    minute: '2-digit',
    second: '2-digit'
  })
})

// Helper functions
const getUserRoleClass = (role) => {
  const classes = {
    'admin': 'bg-blue-600',
    'distributor': 'bg-green-600',
    'service_provider': 'bg-purple-600',
    'client': 'bg-yellow-600',
    'system': 'bg-gray-600',
    'unknown': 'bg-red-600'
  }
  return classes[role] || 'bg-gray-400'
}

const getUserIcon = (role) => {
  const icons = {
    'admin': 'fas fa-user-shield',
    'distributor': 'fas fa-warehouse',
    'service_provider': 'fas fa-user-hard-hat',
    'client': 'fas fa-user',
    'system': 'fas fa-server',
    'unknown': 'fas fa-question'
  }
  return icons[role] || 'fas fa-user'
}

const getActionIcon = (action) => {
  const icons = {
    'login': 'fas fa-sign-in-alt',
    'logout': 'fas fa-sign-out-alt',
    'create': 'fas fa-plus-circle',
    'update': 'fas fa-edit',
    'delete': 'fas fa-trash-alt',
    'view': 'fas fa-eye',
    'export': 'fas fa-download',
    'color_mix': 'fas fa-palette',
    'system_update': 'fas fa-cogs',
    'backup': 'fas fa-database',
    'failed_login': 'fas fa-exclamation-triangle'
  }
  return icons[action] || 'fas fa-bolt'
}

const getActionColor = (action) => {
  const colors = {
    'login': '#4A90E2',
    'create': '#2ECC71',
    'update': '#F1C40F',
    'delete': '#E74C3C',
    'failed_login': '#E74C3C'
  }
  return colors[action] || '#7F8C8D'
}

const formatAction = (action) => {
  return action.split('_').map(word => 
    word.charAt(0).toUpperCase() + word.slice(1)
  ).join(' ')
}

const formatDate = (timestamp) => {
  return new Date(timestamp).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTimeAgo = (timestamp) => {
  const now = new Date()
  const logTime = new Date(timestamp)
  const diffMs = now - logTime
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)
  
  if (diffMins < 60) return `${diffMins} minute${diffMins !== 1 ? 's' : ''} ago`
  if (diffHours < 24) return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`
  return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`
}

const getSeverityClass = (severity) => {
  const classes = {
    'info': 'bg-blue-100 text-blue-800',
    'warning': 'bg-yellow-100 text-yellow-800',
    'critical': 'bg-red-100 text-red-800'
  }
  return classes[severity] || 'bg-gray-100 text-gray-800'
}

const getSeverityIcon = (severity) => {
  const icons = {
    'info': 'fas fa-info-circle',
    'warning': 'fas fa-exclamation-triangle',
    'critical': 'fas fa-fire'
  }
  return icons[severity] || 'fas fa-circle'
}

// Methods
const setTimeRange = (days) => {
  timeRange.value = days
  console.log(`Loading logs from last ${days} days`)
  // In real app: fetch logs for the selected time range
}

const toggleAutoRefresh = () => {
  autoRefresh.value = !autoRefresh.value
  if (autoRefresh.value) {
    alert('Auto-refresh enabled - logs will update every 30 seconds')
  } else {
    alert('Auto-refresh disabled')
  }
}

const loadMoreLogs = () => {
  console.log('Loading more audit logs...')
  // In real app: fetch more logs from API with pagination
  alert('Loading additional audit logs...')
}

const clearOldLogs = () => {
  if (confirm('Are you sure you want to clear logs older than 90 days? This action cannot be undone.')) {
    console.log('Clearing old audit logs...')
    alert('Old audit logs cleared successfully')
  }
}

const exportLogs = () => {
  const headers = ['Timestamp', 'User', 'Role', 'Action', 'Entity', 'Details', 'Severity', 'IP Address']
  const csvContent = [
    headers.join(','),
    ...filteredLogs.value.map(log => [
      new Date(log.timestamp).toISOString(),
      log.userName,
      log.userRole,
      formatAction(log.actionType),
      log.entityType,
      `"${log.details}"`,
      log.severity,
      log.ipAddress
    ].join(','))
  ].join('\n')
  
  const blob = new Blob([csvContent], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `audit-logs-${new Date().toISOString().split('T')[0]}.csv`
  a.click()
  window.URL.revokeObjectURL(url)
}

// Auto-refresh interval
let refreshInterval = null

onMounted(() => {
  console.log('Audit Logs page loaded')
  
  // Set up auto-refresh if enabled
  if (autoRefresh.value) {
    refreshInterval = setInterval(() => {
      console.log('Auto-refreshing audit logs...')
      // In real app: fetch latest logs from API
    }, 30000) // Every 30 seconds
  }
})

onBeforeUnmount(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<style scoped>
/* Custom styles */
.w-10 {
  width: 2.5rem;
}
.h-10 {
  height: 2.5rem;
}

/* Mobile responsive adjustments */
@media (max-width: 768px) {
  .text-3xl {
    font-size: 1.75rem;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .lg\:grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  table {
    display: block;
    overflow-x: auto;
  }
  
  .max-w-xs {
    max-width: 12rem;
  }
}
</style>