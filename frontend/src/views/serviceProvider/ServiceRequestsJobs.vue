<template>
  <div class="jobs-container bg-gray-900 min-h-screen p-4 md:p-6">
    <!-- Header Section -->
    <div class="jobs-header mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Service Jobs & Requests</h1>
          <p class="text-gray-400 text-sm md:text-base">Manage client painting projects and service requests</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
          <!-- Filter Dropdown -->
          <div class="relative">
            <button @click="toggleFilterDropdown" class="filter-button">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
              </svg>
              Filter: {{ activeFilter.label }}
              <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
            <div v-if="showFilterDropdown" class="filter-dropdown">
              <button 
                v-for="filter in filters" 
                :key="filter.value"
                @click="setFilter(filter)"
                :class="['filter-option', { 'active': filter.value === activeFilter.value }]"
              >
                {{ filter.label }}
                <span v-if="filter.value === activeFilter.value" class="checkmark">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
              </button>
            </div>
          </div>
          
          <!-- Create New Job Button -->
          <button @click="openCreateJobModal" class="create-job-button">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create New Job
          </button>
        </div>
      </div>
      
      <!-- Stats Summary -->
      <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="job-stat-card bg-gradient-to-br from-blue-900/30 to-blue-800/30 border border-blue-700/50">
          <div class="flex items-center">
            <div class="job-stat-icon bg-gradient-to-r from-blue-500 to-cyan-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-gray-400 text-sm">Total Jobs</p>
              <p class="text-2xl font-bold text-white">47</p>
            </div>
          </div>
        </div>
        <div class="job-stat-card bg-gradient-to-br from-amber-900/30 to-orange-800/30 border border-amber-700/50">
          <div class="flex items-center">
            <div class="job-stat-icon bg-gradient-to-r from-amber-500 to-orange-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-gray-400 text-sm">Pending</p>
              <p class="text-2xl font-bold text-white">8</p>
            </div>
          </div>
        </div>
        <div class="job-stat-card bg-gradient-to-br from-emerald-900/30 to-green-800/30 border border-emerald-700/50">
          <div class="flex items-center">
            <div class="job-stat-icon bg-gradient-to-r from-emerald-500 to-green-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-gray-400 text-sm">In Progress</p>
              <p class="text-2xl font-bold text-white">12</p>
            </div>
          </div>
        </div>
        <div class="job-stat-card bg-gradient-to-br from-purple-900/30 to-pink-800/30 border border-purple-700/50">
          <div class="flex items-center">
            <div class="job-stat-icon bg-gradient-to-r from-purple-500 to-pink-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-gray-400 text-sm">Completed</p>
              <p class="text-2xl font-bold text-white">27</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="jobs-content">
      <!-- Jobs Table -->
      <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-900 border-b border-gray-700">
                <th class="text-left py-4 px-6 text-gray-400 font-medium text-sm">Job ID</th>
                <th class="text-left py-4 px-6 text-gray-400 font-medium text-sm">Client</th>
                <th class="text-left py-4 px-6 text-gray-400 font-medium text-sm">Paint Color</th>
                <th class="text-left py-4 px-6 text-gray-400 font-medium text-sm">Paint Brand</th>
                <th class="text-left py-4 px-6 text-gray-400 font-medium text-sm">Status</th>
                <th class="text-left py-4 px-6 text-gray-400 font-medium text-sm">Distributor</th>
                <th class="text-left py-4 px-6 text-gray-400 font-medium text-sm">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="job in filteredJobs" :key="job.id" class="border-b border-gray-700/50 hover:bg-gray-700/30 transition-colors">
                <!-- Job ID -->
                <td class="py-4 px-6">
                  <div class="flex items-center">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-900/30 to-blue-800/30 border border-blue-700/50 flex items-center justify-center mr-3">
                      <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-white font-medium">#{{ job.id }}</p>
                      <p class="text-gray-400 text-xs">{{ job.date }}</p>
                    </div>
                  </div>
                </td>
                
                <!-- Client -->
                <td class="py-4 px-6">
                  <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center mr-3">
                      <span class="text-white text-sm font-bold">{{ getInitials(job.client) }}</span>
                    </div>
                    <div>
                      <p class="text-white font-medium">{{ job.client }}</p>
                      <p class="text-gray-400 text-xs">{{ job.location }}</p>
                    </div>
                  </div>
                </td>
                
                <!-- Paint Color -->
                <td class="py-4 px-6">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg mr-3" :style="{ backgroundColor: job.color.hex }"></div>
                    <div>
                      <p class="text-white font-medium">{{ job.color.name }}</p>
                      <p class="text-gray-400 text-xs">{{ job.color.hex }}</p>
                      <div v-if="job.unityLinked" class="flex items-center mt-1">
                        <span class="unity-badge-small">Unity</span>
                      </div>
                    </div>
                  </div>
                </td>
                
                <!-- Paint Brand -->
                <td class="py-4 px-6">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-gray-800 to-gray-700 border border-gray-600 flex items-center justify-center mr-3">
                      <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-white font-medium">{{ job.paintBrand }}</p>
                      <p class="text-gray-400 text-xs">{{ job.paintType }}</p>
                    </div>
                  </div>
                </td>
                
                <!-- Status -->
                <td class="py-4 px-6">
                  <div class="flex items-center">
                    <div :class="['status-indicator', `status-${job.status}`]"></div>
                    <span :class="['status-badge', `status-${job.status}`]">
                      {{ getStatusText(job.status) }}
                    </span>
                    <button @click="updateJobStatus(job)" class="ml-2 text-gray-400 hover:text-white transition-colors">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                    </button>
                  </div>
                </td>
                
                <!-- Distributor -->
                <td class="py-4 px-6">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-emerald-400 flex items-center justify-center mr-3">
                      <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-white font-medium">{{ job.distributor.name }}</p>
                      <p class="text-gray-400 text-xs">{{ job.distributor.location }}</p>
                    </div>
                  </div>
                </td>
                
                <!-- Actions -->
                <td class="py-4 px-6">
                  <div class="flex items-center space-x-2">
                    <!-- View Job Details -->
                    <button @click="viewJobDetails(job)" class="action-button bg-gradient-to-br from-blue-900/30 to-blue-800/30 border border-blue-700/50">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </button>
                    
                    <!-- Link to Unity -->
                    <button @click="linkToUnity(job)" class="action-button bg-gradient-to-br from-purple-900/30 to-pink-800/30 border border-purple-700/50">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                      </svg>
                    </button>
                    
                    <!-- More Actions Dropdown -->
                    <div class="relative">
                      <button @click="toggleActionDropdown(job.id)" class="action-button bg-gradient-to-br from-gray-800 to-gray-700 border border-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                      </button>
                      <div v-if="activeActionDropdown === job.id" class="action-dropdown">
                        <button @click="editJob(job)" class="dropdown-option">
                          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                          Edit Job
                        </button>
                        <button @click="assignDistributor(job)" class="dropdown-option">
                          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-5.197a6 6 0 00-9 5.197" />
                          </svg>
                          Change Distributor
                        </button>
                        <button @click="generateInvoice(job)" class="dropdown-option">
                          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                          </svg>
                          Generate Invoice
                        </button>
                        <div class="border-t border-gray-700 my-1"></div>
                        <button @click="deleteJob(job)" class="dropdown-option text-red-400">
                          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                          Delete Job
                        </button>
                      </div>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Unity Integration Section -->
      <div class="mt-8 bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-700/50 rounded-xl p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
          <div class="flex items-start mb-6 md:mb-0">
            <div class="mr-4">
              <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
              </div>
            </div>
            <div>
              <h3 class="text-xl font-bold text-white mb-2">Unity Integration</h3>
              <p class="text-gray-300 mb-3">Link color mixing results from Unity to service jobs for accurate paint matching</p>
              <div class="flex items-center space-x-4">
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                  <span class="text-gray-300 text-sm">8 jobs linked to Unity</span>
                </div>
                <div class="flex items-center">
                  <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                  <span class="text-gray-300 text-sm">12 colors available</span>
                </div>
              </div>
            </div>
          </div>
          <div class="flex space-x-3">
            <button @click="openUnityMixer" class="unity-launch-button">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
              Open Unity Mixer
            </button>
            <button @click="importFromUnity" class="secondary-button">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
              </svg>
              Import Colors
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Job Modal -->
    <div v-if="showCreateModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="text-xl font-bold text-white">Create New Service Job</h3>
          <button @click="closeCreateJobModal" class="modal-close-button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="modal-content">
          <!-- Create Job Form Content -->
          <p class="text-gray-400 mb-6">Fill in the details to create a new service job for your client</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Client Selection -->
            <div>
              <label class="form-label">Select Client</label>
              <select v-model="newJob.clientId" class="form-select">
                <option value="">Choose a client</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }} - {{ client.location }}
                </option>
              </select>
            </div>
            
            <!-- Paint Brand -->
            <div>
              <label class="form-label">Paint Brand</label>
              <select v-model="newJob.paintBrand" class="form-select">
                <option value="">Select brand</option>
                <option value="Boysen">Boysen</option>
                <option value="Davies">Davies</option>
                <option value="Rainbow">Rainbow</option>
                <option value="Camel">Camel</option>
                <option value="Asian">Asian</option>
              </select>
            </div>
            
            <!-- Color Selection -->
            <div>
              <label class="form-label">Paint Color</label>
              <div class="flex space-x-3">
                <select v-model="newJob.colorId" class="form-select flex-1">
                  <option value="">Select color</option>
                  <option v-for="color in colors" :key="color.id" :value="color.id">
                    {{ color.name }} ({{ color.hex }})
                  </option>
                </select>
                <button @click="openColorPicker" class="color-picker-button">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- Distributor -->
            <div>
              <label class="form-label">Assigned Distributor</label>
              <select v-model="newJob.distributorId" class="form-select">
                <option value="">Assign distributor</option>
                <option v-for="distributor in distributors" :key="distributor.id" :value="distributor.id">
                  {{ distributor.name }} - {{ distributor.location }}
                </option>
              </select>
            </div>
            
            <!-- Job Type -->
            <div>
              <label class="form-label">Job Type</label>
              <select v-model="newJob.jobType" class="form-select">
                <option value="interior">Interior Painting</option>
                <option value="exterior">Exterior Painting</option>
                <option value="furniture">Furniture Painting</option>
                <option value="special">Special Project</option>
              </select>
            </div>
            
            <!-- Estimated Budget -->
            <div>
              <label class="form-label">Estimated Budget (₱)</label>
              <input v-model="newJob.estimatedBudget" type="number" class="form-input" placeholder="e.g., 25000">
            </div>
            
            <!-- Project Description -->
            <div class="md:col-span-2">
              <label class="form-label">Project Description</label>
              <textarea v-model="newJob.description" class="form-textarea" rows="3" placeholder="Describe the project details..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeCreateJobModal" class="modal-cancel-button">
            Cancel
          </button>
          <button @click="createJob" class="modal-submit-button">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Job
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ServiceJobs',
  data() {
    return {
      showFilterDropdown: false,
      showCreateModal: false,
      activeFilter: { value: 'all', label: 'All Jobs' },
      activeActionDropdown: null,
      filters: [
        { value: 'all', label: 'All Jobs' },
        { value: 'pending', label: 'Pending' },
        { value: 'in-progress', label: 'In Progress' },
        { value: 'completed', label: 'Completed' },
        { value: 'cancelled', label: 'Cancelled' }
      ],
      jobs: [
        {
          id: 'J-2024-001',
          client: 'Juan Dela Cruz',
          location: 'Bacoor, Cavite',
          color: { name: 'Ocean Breeze', hex: '#4CC9F0' },
          paintBrand: 'Boysen',
          paintType: 'Water-based',
          status: 'in-progress',
          distributor: { name: 'Cavite Paint Supply', location: 'Imus' },
          date: 'Feb 15, 2024',
          unityLinked: true
        },
        {
          id: 'J-2024-002',
          client: 'Maria Gonzales',
          location: 'Imus, Cavite',
          color: { name: 'Sage Green', hex: '#8A9A5B' },
          paintBrand: 'Davies',
          paintType: 'Oil-based',
          status: 'pending',
          distributor: { name: 'Dasma Hardware', location: 'Dasmarinas' },
          date: 'Feb 14, 2024',
          unityLinked: false
        },
        {
          id: 'J-2024-003',
          client: 'Robert Lim',
          location: 'Dasmarinas, Cavite',
          color: { name: 'Coral Sunset', hex: '#FF7F50' },
          paintBrand: 'Rainbow',
          paintType: 'Latex',
          status: 'completed',
          distributor: { name: 'Tri-City Supplies', location: 'General Trias' },
          date: 'Feb 13, 2024',
          unityLinked: true
        },
        {
          id: 'J-2024-004',
          client: 'Anna Santos',
          location: 'General Trias',
          color: { name: 'Midnight Blue', hex: '#191970' },
          paintBrand: 'Camel',
          paintType: 'Water-based',
          status: 'in-progress',
          distributor: { name: 'Trece Martires Depot', location: 'Trece' },
          date: 'Feb 12, 2024',
          unityLinked: true
        },
        {
          id: 'J-2024-005',
          client: 'Carlos Reyes',
          location: 'Trece Martires',
          color: { name: 'Soft Lavender', hex: '#B19CD9' },
          paintBrand: 'Asian',
          paintType: 'Acrylic',
          status: 'pending',
          distributor: { name: 'Bacoor Paint Center', location: 'Bacoor' },
          date: 'Feb 11, 2024',
          unityLinked: false
        },
        {
          id: 'J-2024-006',
          client: 'Liza Mendoza',
          location: 'Silang, Cavite',
          color: { name: 'Sunset Orange', hex: '#FF6B35' },
          paintBrand: 'Boysen',
          paintType: 'Water-based',
          status: 'completed',
          distributor: { name: 'Silang Supplier', location: 'Silang' },
          date: 'Feb 10, 2024',
          unityLinked: true
        }
      ],
      clients: [
        { id: 1, name: 'Juan Dela Cruz', location: 'Bacoor, Cavite' },
        { id: 2, name: 'Maria Gonzales', location: 'Imus, Cavite' },
        { id: 3, name: 'Robert Lim', location: 'Dasmarinas, Cavite' },
        { id: 4, name: 'Anna Santos', location: 'General Trias' },
        { id: 5, name: 'Carlos Reyes', location: 'Trece Martires' }
      ],
      colors: [
        { id: 1, name: 'Ocean Breeze', hex: '#4CC9F0' },
        { id: 2, name: 'Sage Green', hex: '#8A9A5B' },
        { id: 3, name: 'Coral Sunset', hex: '#FF7F50' },
        { id: 4, name: 'Midnight Blue', hex: '#191970' },
        { id: 5, name: 'Soft Lavender', hex: '#B19CD9' }
      ],
      distributors: [
        { id: 1, name: 'Cavite Paint Supply', location: 'Imus' },
        { id: 2, name: 'Dasma Hardware', location: 'Dasmarinas' },
        { id: 3, name: 'Tri-City Supplies', location: 'General Trias' },
        { id: 4, name: 'Trece Martires Depot', location: 'Trece' },
        { id: 5, name: 'Bacoor Paint Center', location: 'Bacoor' }
      ],
      newJob: {
        clientId: '',
        paintBrand: '',
        colorId: '',
        distributorId: '',
        jobType: 'interior',
        estimatedBudget: '',
        description: ''
      }
    }
  },
  computed: {
    filteredJobs() {
      if (this.activeFilter.value === 'all') {
        return this.jobs
      }
      return this.jobs.filter(job => job.status === this.activeFilter.value)
    }
  },
  methods: {
    getInitials(name) {
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
    },
    
    getStatusText(status) {
      const statusMap = {
        'pending': 'Pending',
        'in-progress': 'In Progress',
        'completed': 'Completed',
        'cancelled': 'Cancelled'
      }
      return statusMap[status] || status
    },
    
    toggleFilterDropdown() {
      this.showFilterDropdown = !this.showFilterDropdown
    },
    
    setFilter(filter) {
      this.activeFilter = filter
      this.showFilterDropdown = false
    },
    
    toggleActionDropdown(jobId) {
      this.activeActionDropdown = this.activeActionDropdown === jobId ? null : jobId
    },
    
    openCreateJobModal() {
      this.showCreateModal = true
    },
    
    closeCreateJobModal() {
      this.showCreateModal = false
      this.resetNewJob()
    },
    
    resetNewJob() {
      this.newJob = {
        clientId: '',
        paintBrand: '',
        colorId: '',
        distributorId: '',
        jobType: 'interior',
        estimatedBudget: '',
        description: ''
      }
    },
    
    createJob() {
      // Validate form
      if (!this.newJob.clientId || !this.newJob.paintBrand || !this.newJob.colorId) {
        alert('Please fill in all required fields')
        return
      }
      
      // Create new job logic here
      console.log('Creating new job:', this.newJob)
      
      // Simulate API call
      const newJob = {
        id: `J-2024-${String(this.jobs.length + 1).padStart(3, '0')}`,
        client: this.clients.find(c => c.id === this.newJob.clientId)?.name || 'Unknown',
        location: this.clients.find(c => c.id === this.newJob.clientId)?.location || 'Unknown',
        color: this.colors.find(c => c.id === this.newJob.colorId) || { name: 'Unknown', hex: '#CCCCCC' },
        paintBrand: this.newJob.paintBrand,
        paintType: 'Water-based', // Default
        status: 'pending',
        distributor: this.distributors.find(d => d.id === this.newJob.distributorId) || { name: 'Unassigned', location: 'N/A' },
        date: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
        unityLinked: false
      }
      
      this.jobs.unshift(newJob)
      this.closeCreateJobModal()
      alert('New job created successfully!')
    },
    
    viewJobDetails(job) {
      console.log('Viewing job details:', job)
      // Navigate to job details page or open modal
    },
    
    linkToUnity(job) {
      console.log('Linking job to Unity:', job)
      // Logic to link job with Unity color mixer
      job.unityLinked = true
      alert('Job linked to Unity successfully!')
    },
    
    openUnityMixer() {
      console.log('Opening Unity mixer...')
      this.$router.push('/service-provider/color-mixer')
    },
    
    importFromUnity() {
      console.log('Importing colors from Unity...')
      // Logic to import colors from Unity
    },
    
    editJob(job) {
      console.log('Editing job:', job)
      // Open edit modal with job data
    },
    
    assignDistributor(job) {
      console.log('Assigning distributor to job:', job)
      // Open distributor assignment modal
    },
    
    generateInvoice(job) {
      console.log('Generating invoice for job:', job)
      // Open invoice generation
    },
    
    deleteJob(job) {
      if (confirm(`Are you sure you want to delete job ${job.id}?`)) {
        this.jobs = this.jobs.filter(j => j.id !== job.id)
        console.log('Deleted job:', job)
      }
    },
    
    updateJobStatus(job) {
      const statusOptions = ['pending', 'in-progress', 'completed', 'cancelled']
      const currentIndex = statusOptions.indexOf(job.status)
      const nextIndex = (currentIndex + 1) % statusOptions.length
      job.status = statusOptions[nextIndex]
      console.log('Updated job status:', job)
    },
    
    openColorPicker() {
      console.log('Opening color picker...')
      // Open color picker modal or navigate to Unity
    }
  },
  mounted() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', (event) => {
      if (!event.target.closest('.relative')) {
        this.showFilterDropdown = false
        this.activeActionDropdown = null
      }
    })
  }
}
</script>

<style scoped>
.jobs-container {
  background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
  min-height: 100vh;
}

/* Filter Button */
.filter-button {
  background: linear-gradient(45deg, #2d3748, #4a5568);
  color: #cbd5e0;
  padding: 0.625rem 1rem;
  border-radius: 0.625rem;
  border: 1px solid #4a5568;
  display: flex;
  align-items: center;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
}

.filter-button:hover {
  background: linear-gradient(45deg, #4a5568, #718096);
  border-color: #718096;
}

.filter-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  margin-top: 0.5rem;
  background: #2d3748;
  border: 1px solid #4a5568;
  border-radius: 0.625rem;
  min-width: 200px;
  z-index: 1000;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

.filter-option {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem;
  color: #cbd5e0;
  transition: all 0.2s ease;
  width: 100%;
  text-align: left;
  cursor: pointer;
  background: transparent;
  border: none;
}

.filter-option:hover {
  background: rgba(66, 153, 225, 0.1);
  color: white;
}

.filter-option.active {
  background: rgba(66, 153, 225, 0.2);
  color: #63b3ed;
}

.checkmark {
  color: #48bb78;
}

/* Create Job Button */
.create-job-button {
  background: linear-gradient(45deg, #3182ce, #805ad5);
  color: white;
  padding: 0.625rem 1.25rem;
  border-radius: 0.625rem;
  border: none;
  display: flex;
  align-items: center;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(49, 130, 206, 0.3);
}

.create-job-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(49, 130, 206, 0.4);
}

/* Job Stat Cards */
.job-stat-card {
  padding: 1.25rem;
  border-radius: 1rem;
  transition: all 0.3s ease;
}

.job-stat-card:hover {
  transform: translateY(-2px);
}

.job-stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.025em;
}

.status-pending {
  background: linear-gradient(45deg, #f59e0b, #fbbf24);
  color: white;
}

.status-in-progress {
  background: linear-gradient(45deg, #3b82f6, #60a5fa);
  color: white;
}

.status-completed {
  background: linear-gradient(45deg, #10b981, #34d399);
  color: white;
}

.status-cancelled {
  background: linear-gradient(45deg, #ef4444, #f87171);
  color: white;
}

/* Status Indicator */
.status-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-right: 0.5rem;
}

.status-pending {
  background-color: #f59e0b;
}

.status-in-progress {
  background-color: #3b82f6;
}

.status-completed {
  background-color: #10b981;
}

.status-cancelled {
  background-color: #ef4444;
}

/* Unity Badge */
.unity-badge-small {
  display: inline-block;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-size: 0.625rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  background: linear-gradient(45deg, #8b5cf6, #ec4899);
  color: white;
  text-transform: uppercase;
}

/* Action Buttons */
.action-button {
  width: 36px;
  height: 36px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cbd5e0;
  transition: all 0.2s ease;
  cursor: pointer;
  border: 1px solid;
}

.action-button:hover {
  transform: translateY(-2px);
  color: white;
}

.action-dropdown {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 0.5rem;
  background: #2d3748;
  border: 1px solid #4a5568;
  border-radius: 0.625rem;
  min-width: 200px;
  z-index: 1000;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
}

.dropdown-option {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #cbd5e0;
  transition: all 0.2s ease;
  width: 100%;
  text-align: left;
  cursor: pointer;
  background: transparent;
  border: none;
  font-size: 0.875rem;
}

.dropdown-option:hover {
  background: rgba(66, 153, 225, 0.1);
  color: white;
}

/* Unity Integration */
.unity-launch-button {
  background: linear-gradient(45deg, #8b5cf6, #ec4899);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  border: none;
  display: flex;
  align-items: center;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.unity-launch-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
}

.secondary-button {
  background: linear-gradient(45deg, #4a5568, #718096);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 0.75rem;
  border: none;
  display: flex;
  align-items: center;
  font-weight: 600;
  transition: all 0.3s ease;
  cursor: pointer;
}

.secondary-button:hover {
  transform: translateY(-2px);
  background: linear-gradient(45deg, #718096, #a0aec0);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  backdrop-filter: blur(4px);
}

.modal-container {
  background: linear-gradient(180deg, #1a202c 0%, #2d3748 100%);
  border-radius: 1rem;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
  border: 1px solid #4a5568;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #4a5568;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-close-button {
  width: 32px;
  height: 32px;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cbd5e0;
  transition: all 0.2s ease;
  cursor: pointer;
  background: transparent;
  border: none;
}

.modal-close-button:hover {
  background: rgba(239, 68, 68, 0.1);
  color: #fc8181;
}

.modal-content {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1.5rem;
  border-top: 1px solid #4a5568;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 1rem;
}

/* Form Styles */
.form-label {
  display: block;
  margin-bottom: 0.5rem;
  color: #cbd5e0;
  font-size: 0.875rem;
  font-weight: 500;
}

.form-select,
.form-input,
.form-textarea {
  width: 100%;
  padding: 0.625rem 0.875rem;
  background: #2d3748;
  border: 1px solid #4a5568;
  border-radius: 0.5rem;
  color: white;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.form-select:focus,
.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.form-textarea {
  resize: vertical;
}

.color-picker-button {
  width: 48px;
  background: linear-gradient(45deg, #8b5cf6, #ec4899);
  color: white;
  border: none;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.color-picker-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.modal-cancel-button {
  padding: 0.625rem 1.25rem;
  background: transparent;
  border: 1px solid #4a5568;
  border-radius: 0.625rem;
  color: #cbd5e0;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.modal-cancel-button:hover {
  background: #4a5568;
  color: white;
}

.modal-submit-button {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(45deg, #3182ce, #805ad5);
  border: none;
  border-radius: 0.625rem;
  color: white;
  font-weight: 600;
  display: flex;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-submit-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(49, 130, 206, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
  .jobs-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .create-job-button {
    width: 100%;
    justify-content: center;
    margin-top: 1rem;
  }
  
  .modal-container {
    width: 95%;
    margin: 1rem;
  }
  
  .unity-launch-button,
  .secondary-button {
    width: 100%;
    justify-content: center;
    margin-top: 0.5rem;
  }
}

@media (max-width: 640px) {
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
  
  .job-stat-card {
    padding: 1rem;
  }
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

/* Animation for dropdowns */
.filter-dropdown,
.action-dropdown {
  animation: slideDown 0.2s ease-out;
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

/* Hover effects for table rows */
tr {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(5px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>