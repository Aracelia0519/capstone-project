<template>
  <div class="min-h-screen bg-gray-50 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
            <svg class="w-6 h-6 md:w-8 md:h-8 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Service Providers
          </h1>
          <p class="text-gray-600 mt-2">Manage service providers and their assignments</p>
        </div>
        <button class="mt-4 md:mt-0 bg-purple-600 hover:bg-purple-700 text-white px-4 md:px-6 py-2 md:py-3 rounded-lg flex items-center transition-colors">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add New Provider
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-purple-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Total Providers</p>
            <h3 class="text-2xl font-bold text-gray-800">89</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Available</p>
            <h3 class="text-2xl font-bold text-gray-800">65</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Services This Month</p>
            <h3 class="text-2xl font-bold text-gray-800">142</h3>
          </div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex items-center">
          <div class="bg-yellow-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Avg. Rating</p>
            <h3 class="text-2xl font-bold text-gray-800">4.7</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Providers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
      <div v-for="provider in serviceProviders" :key="provider.id" 
           class="bg-white rounded-xl shadow hover-lift overflow-hidden">
        <div class="p-4 md:p-6">
          <!-- Provider Header -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center">
              <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center mr-4">
                <span class="text-purple-600 font-bold text-lg">{{ provider.name.charAt(0) }}</span>
              </div>
              <div>
                <h3 class="font-bold text-gray-800">{{ provider.name }}</h3>
                <p class="text-sm text-gray-500">{{ provider.specialty }}</p>
              </div>
            </div>
            <span :class="`px-3 py-1 rounded-full text-xs font-medium ${getStatusClass(provider.status)}`">
              {{ provider.status }}
            </span>
          </div>

          <!-- Provider Details -->
          <div class="space-y-3 mb-4">
            <div class="flex items-center text-sm text-gray-600">
              <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              {{ provider.email }}
            </div>
            <div class="flex items-center text-sm text-gray-600">
              <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              {{ provider.phone }}
            </div>
            <div class="flex items-center text-sm text-gray-600">
              <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ provider.location }}
            </div>
          </div>

          <!-- Performance Metrics -->
          <div class="grid grid-cols-3 gap-2 mb-4">
            <div class="text-center p-2 bg-gray-50 rounded-lg">
              <div class="font-bold text-gray-800">{{ provider.servicesCompleted }}</div>
              <div class="text-xs text-gray-500">Services</div>
            </div>
            <div class="text-center p-2 bg-gray-50 rounded-lg">
              <div class="font-bold text-gray-800">{{ provider.rating }}</div>
              <div class="text-xs text-gray-500">Rating</div>
            </div>
            <div class="text-center p-2 bg-gray-50 rounded-lg">
              <div class="font-bold text-gray-800">{{ provider.experience }}</div>
              <div class="text-xs text-gray-500">Years</div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex space-x-2">
            <button class="flex-1 bg-purple-50 text-purple-600 hover:bg-purple-100 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
              View Profile
            </button>
            <button class="flex-1 bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
              Assign Job
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script>
export default {
  name: 'ServiceProviders',
  data() {
    return {
      serviceProviders: [
        { id: 'SP-001', name: 'David Wilson', email: 'david@paintingpros.com', phone: '+1 (555) 123-4567', specialty: 'Interior Painting', location: 'Austin, TX', status: 'Available', servicesCompleted: 156, rating: 4.9, experience: 8, credentials: ['License', 'Insurance', 'Certification'] },
        { id: 'SP-002', name: 'Lisa Chen', email: 'lisa@expertpaint.com', phone: '+1 (555) 234-5678', specialty: 'Exterior Painting', location: 'Denver, CO', status: 'On Job', servicesCompleted: 89, rating: 4.7, experience: 5, credentials: ['License', 'Insurance'] },
        { id: 'SP-003', name: 'Michael Brown', email: 'michael@qualitypaint.com', phone: '+1 (555) 345-6789', specialty: 'Commercial Painting', location: 'Seattle, WA', status: 'Available', servicesCompleted: 203, rating: 4.8, experience: 12, credentials: ['License', 'Insurance', 'Certification', 'Bonded'] },
        { id: 'SP-004', name: 'Sarah Johnson', email: 'sarah@precisionpaint.com', phone: '+1 (555) 456-7890', specialty: 'Detail Painting', location: 'Boston, MA', status: 'Available', servicesCompleted: 134, rating: 4.6, experience: 6, credentials: ['License', 'Insurance'] },
        { id: 'SP-005', name: 'James Miller', email: 'james@industrialpaint.com', phone: '+1 (555) 567-8901', specialty: 'Industrial Painting', location: 'Houston, TX', status: 'On Leave', servicesCompleted: 178, rating: 4.5, experience: 10, credentials: ['License', 'Insurance', 'Certification'] },
        { id: 'SP-006', name: 'Emma Davis', email: 'emma@eco-paint.com', phone: '+1 (555) 678-9012', specialty: 'Eco-Friendly Painting', location: 'Portland, OR', status: 'Available', servicesCompleted: 92, rating: 4.9, experience: 4, credentials: ['License', 'Insurance', 'Eco-Certification'] }
      ]
    }
  },
  methods: {
    getStatusClass(status) {
      const classes = {
        'Available': 'bg-green-100 text-green-800',
        'On Job': 'bg-blue-100 text-blue-800',
        'On Leave': 'bg-yellow-100 text-yellow-800',
        'Inactive': 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }
  }
}
</script>

<style scoped>
/* Custom card hover effect */
.hover-lift {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

/* Smooth transitions for all interactive elements */
button, .status-badge, .metric-card {
  transition: all 0.2s ease;
}

/* Custom gradient for cards */
.bg-gradient-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

/* Custom border radius for profile images */
.profile-image {
  border: 3px solid #fff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Responsive typography */
@media (max-width: 640px) {
  .provider-name {
    font-size: 1rem;
  }
  
  .provider-detail {
    font-size: 0.875rem;
  }
}
</style>