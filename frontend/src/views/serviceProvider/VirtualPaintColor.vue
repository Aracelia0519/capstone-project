<template>
  <div class="unity-container bg-gray-900 min-h-screen p-4 md:p-6">
    <!-- Header Section -->
    <div class="unity-header mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Virtual Paint Color Mixer</h1>
          <p class="text-gray-400 text-sm md:text-base">Real-time 3D visualization using Unity WebGL</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
          <!-- Status Badge -->
          <div class="unity-status-badge">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
            <span class="text-green-400 text-sm font-medium">Unity Connected</span>
          </div>
          
          <!-- Quick Actions -->
          <div class="flex items-center space-x-2">
            <button @click="toggleFullscreen" class="unity-action-button">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
              </svg>
            </button>
            <button @click="resetScene" class="unity-action-button">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Project Info -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="project-info-card">
          <div class="flex items-center">
            <div class="project-icon bg-gradient-to-r from-blue-500 to-cyan-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-gray-400 text-sm">Client</p>
              <p class="text-white font-medium">Juan Dela Cruz</p>
            </div>
          </div>
        </div>
        <div class="project-info-card">
          <div class="flex items-center">
            <div class="project-icon bg-gradient-to-r from-amber-500 to-orange-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-gray-400 text-sm">Project</p>
              <p class="text-white font-medium">Living Room Painting</p>
            </div>
          </div>
        </div>
        <div class="project-info-card">
          <div class="flex items-center">
            <div class="project-icon bg-gradient-to-r from-teal-500 to-emerald-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-gray-400 text-sm">Distributor</p>
              <p class="text-white font-medium">Cavite Paint Supply</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="unity-content grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Unity WebGL Canvas (Left Column) -->
      <div class="lg:col-span-2">
        <!-- Unity Container -->
        <div class="unity-canvas-container">
          <!-- Unity Loading Screen -->
          <div v-if="isUnityLoading" class="unity-loading-screen">
            <div class="loading-content">
              <div class="loading-spinner">
                <div class="spinner-ring"></div>
              </div>
              <h3 class="text-xl font-bold text-white mb-2">Loading Unity WebGL</h3>
              <p class="text-gray-300 mb-4">Initializing 3D visualization engine...</p>
              <div class="loading-progress">
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: loadingProgress + '%' }"></div>
                </div>
                <span class="text-sm text-gray-400 mt-2">{{ loadingProgress }}%</span>
              </div>
            </div>
          </div>

          <!-- Unity WebGL Canvas -->
          <div class="unity-webgl-canvas">
            <!-- Placeholder for Unity WebGL build -->
            <div class="unity-placeholder">
              <div class="placeholder-house">
                <!-- House Structure -->
                <div class="house-container">
                  <!-- Main House -->
                  <div class="house-main" :style="{ backgroundColor: currentColor.hex }">
                    <div class="house-roof"></div>
                    <div class="house-door"></div>
                    <div class="house-windows">
                      <div class="window left-window"></div>
                      <div class="window right-window"></div>
                      <div class="window top-window"></div>
                    </div>
                  </div>
                  
                  <!-- Side Wall -->
                  <div class="side-wall" :style="{ backgroundColor: currentColor.hex }"></div>
                  
                  <!-- Ground -->
                  <div class="ground"></div>
                </div>
              </div>
              
              <!-- Unity Badge -->
              <div class="unity-canvas-badge">
                <span class="unity-badge-text">UNITY</span>
                <span class="unity-badge-version">WebGL</span>
              </div>
              
              <!-- Control Instructions -->
              <div class="unity-instructions">
                <div class="instruction-item">
                  <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                  </svg>
                  <span class="text-gray-300 text-sm">Drag to rotate view</span>
                </div>
                <div class="instruction-item">
                  <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                  <span class="text-gray-300 text-sm">Scroll to zoom</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Scene Controls -->
        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <button @click="changeScene('living-room')" :class="['scene-button', { 'active': activeScene === 'living-room' }]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Living Room
          </button>
          <button @click="changeScene('bedroom')" :class="['scene-button', { 'active': activeScene === 'bedroom' }]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
            Bedroom
          </button>
          <button @click="changeScene('kitchen')" :class="['scene-button', { 'active': activeScene === 'kitchen' }]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Kitchen
          </button>
          <button @click="changeScene('exterior')" :class="['scene-button', { 'active': activeScene === 'exterior' }]">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Exterior
          </button>
        </div>
      </div>

      <!-- Control Panel (Right Column) -->
      <div class="space-y-6">
        <!-- Color Controls -->
        <div class="control-panel bg-gray-800 rounded-xl border border-gray-700 p-6">
          <h2 class="text-xl font-bold text-white mb-6">Color Controls</h2>
          
          <!-- Color Preview -->
          <div class="color-preview mb-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-white">Current Color</h3>
              <div class="color-values">
                <span class="text-gray-300 text-sm mr-3">RGB: {{ currentColor.rgb.join(', ') }}</span>
                <span class="text-gray-300 text-sm">HEX: {{ currentColor.hex }}</span>
              </div>
            </div>
            <div class="color-display" :style="{ backgroundColor: currentColor.hex }">
              <div class="color-name">{{ currentColor.name }}</div>
            </div>
          </div>

          <!-- RGB Sliders -->
          <div class="space-y-5">
            <div class="slider-control">
              <div class="flex justify-between mb-2">
                <label class="text-gray-300 font-medium">Red</label>
                <span class="text-white font-bold">{{ currentColor.rgb[0] }}</span>
              </div>
              <input 
                type="range" 
                min="0" 
                max="255" 
                v-model="currentColor.rgb[0]" 
                @input="updateColor"
                class="color-slider red-slider"
              />
            </div>
            
            <div class="slider-control">
              <div class="flex justify-between mb-2">
                <label class="text-gray-300 font-medium">Green</label>
                <span class="text-white font-bold">{{ currentColor.rgb[1] }}</span>
              </div>
              <input 
                type="range" 
                min="0" 
                max="255" 
                v-model="currentColor.rgb[1]" 
                @input="updateColor"
                class="color-slider green-slider"
              />
            </div>
            
            <div class="slider-control">
              <div class="flex justify-between mb-2">
                <label class="text-gray-300 font-medium">Blue</label>
                <span class="text-white font-bold">{{ currentColor.rgb[2] }}</span>
              </div>
              <input 
                type="range" 
                min="0" 
                max="255" 
                v-model="currentColor.rgb[2]" 
                @input="updateColor"
                class="color-slider blue-slider"
              />
            </div>

            <!-- Preset Colors -->
            <div class="mt-6">
              <h4 class="text-gray-300 font-medium mb-3">Color Presets</h4>
              <div class="grid grid-cols-5 gap-3">
                <button 
                  v-for="preset in colorPresets" 
                  :key="preset.name"
                  @click="selectPreset(preset)"
                  class="preset-color-button"
                  :style="{ backgroundColor: preset.hex }"
                  :title="preset.name"
                ></button>
              </div>
            </div>
          </div>
        </div>

        <!-- Save & Export Panel -->
        <div class="control-panel bg-gray-800 rounded-xl border border-gray-700 p-6">
          <h2 class="text-xl font-bold text-white mb-6">Save & Export</h2>
          
          <!-- Save Form -->
          <div class="space-y-4">
            <div>
              <label class="form-label">Color Name</label>
              <input 
                v-model="saveData.name" 
                type="text" 
                class="form-input"
                placeholder="e.g., Ocean Breeze"
              />
            </div>
            
            <div>
              <label class="form-label">Project Reference</label>
              <select v-model="saveData.projectId" class="form-select">
                <option value="">Select project</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">
                  {{ project.name }} - {{ project.client }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="form-label">Paint Brand</label>
              <select v-model="saveData.paintBrand" class="form-select">
                <option value="">Select brand</option>
                <option value="Boysen">Boysen</option>
                <option value="Davies">Davies</option>
                <option value="Rainbow">Rainbow</option>
                <option value="Camel">Camel</option>
                <option value="Asian">Asian</option>
              </select>
            </div>
            
            <div>
              <label class="form-label">Notes</label>
              <textarea 
                v-model="saveData.notes" 
                class="form-textarea" 
                rows="2"
                placeholder="Add notes about this color..."
              ></textarea>
            </div>
            
            <!-- Save Actions -->
            <div class="grid grid-cols-2 gap-3 mt-6">
              <button @click="saveColor" class="save-button primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Save Color
              </button>
              <button @click="exportColor" class="save-button secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                </svg>
                Export
              </button>
            </div>
          </div>
          
          <!-- Recent Colors -->
          <div class="mt-8">
            <h4 class="text-gray-300 font-medium mb-3">Recent Colors</h4>
            <div class="space-y-3">
              <div 
                v-for="color in recentColors" 
                :key="color.id"
                @click="loadRecentColor(color)"
                class="recent-color-item"
              >
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg mr-3" :style="{ backgroundColor: color.hex }"></div>
                  <div class="flex-1">
                    <p class="text-white font-medium">{{ color.name }}</p>
                    <p class="text-gray-400 text-xs">{{ color.hex }}</p>
                  </div>
                  <button @click.stop="deleteRecentColor(color.id)" class="text-gray-500 hover:text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Integration Panel -->
        <div class="control-panel bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-700/50 rounded-xl p-6">
          <h2 class="text-xl font-bold text-white mb-4">System Integration</h2>
          <p class="text-gray-300 mb-6">This color will be linked to:</p>
          
          <div class="space-y-4">
            <div class="integration-item">
              <div class="integration-icon bg-gradient-to-r from-blue-500 to-cyan-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-gray-300 text-sm">Client</p>
                <p class="text-white font-medium">Juan Dela Cruz</p>
              </div>
            </div>
            
            <div class="integration-item">
              <div class="integration-icon bg-gradient-to-r from-amber-500 to-orange-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-gray-300 text-sm">Job/Project</p>
                <p class="text-white font-medium">Living Room Painting</p>
              </div>
            </div>
            
            <div class="integration-item">
              <div class="integration-icon bg-gradient-to-r from-teal-500 to-emerald-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-gray-300 text-sm">Distributor Inventory</p>
                <p class="text-white font-medium">Cavite Paint Supply</p>
              </div>
            </div>
          </div>
          
          <div class="mt-6 pt-6 border-t border-purple-700/50">
            <button @click="linkToSystem" class="integration-button">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
              </svg>
              Link to System
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Confirmation Modal -->
    <div v-if="showSaveModal" class="modal-overlay">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="text-xl font-bold text-white">Color Saved Successfully!</h3>
          <button @click="closeSaveModal" class="modal-close-button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="modal-content">
          <div class="flex items-center justify-center mb-6">
            <div class="w-16 h-16 rounded-full bg-gradient-to-r from-green-500 to-emerald-400 flex items-center justify-center">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>
          <h4 class="text-xl font-bold text-white text-center mb-2">{{ savedColorName }}</h4>
          <div class="flex justify-center mb-4">
            <div class="w-32 h-32 rounded-lg" :style="{ backgroundColor: currentColor.hex }"></div>
          </div>
          <div class="text-center">
            <p class="text-gray-300 mb-2">RGB: {{ currentColor.rgb.join(', ') }}</p>
            <p class="text-gray-300 mb-6">HEX: {{ currentColor.hex }}</p>
            <div class="space-y-3">
              <p class="text-gray-300">This color has been linked to:</p>
              <div class="flex justify-center space-x-4">
                <span class="px-3 py-1 bg-blue-900/30 text-blue-300 rounded-full text-sm">Client</span>
                <span class="px-3 py-1 bg-amber-900/30 text-amber-300 rounded-full text-sm">Job</span>
                <span class="px-3 py-1 bg-teal-900/30 text-teal-300 rounded-full text-sm">Inventory</span>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button @click="closeSaveModal" class="modal-action-button">
            Continue Mixing
          </button>
          <button @click="goToJobs" class="modal-action-button primary">
            View Jobs
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UnityColorMixer',
  data() {
    return {
      isUnityLoading: false,
      loadingProgress: 0,
      activeScene: 'living-room',
      currentColor: {
        name: 'Ocean Breeze',
        hex: '#4CC9F0',
        rgb: [76, 201, 240]
      },
      colorPresets: [
        { name: 'Ocean Blue', hex: '#4CC9F0', rgb: [76, 201, 240] },
        { name: 'Sage Green', hex: '#8A9A5B', rgb: [138, 154, 91] },
        { name: 'Coral Sunset', hex: '#FF7F50', rgb: [255, 127, 80] },
        { name: 'Midnight Blue', hex: '#191970', rgb: [25, 25, 112] },
        { name: 'Soft Lavender', hex: '#B19CD9', rgb: [177, 156, 217] },
        { name: 'Sunset Orange', hex: '#FF6B35', rgb: [255, 107, 53] },
        { name: 'Mint Green', hex: '#98FB98', rgb: [152, 251, 152] },
        { name: 'Blush Pink', hex: '#FFB6C1', rgb: [255, 182, 193] },
        { name: 'Warm Beige', hex: '#F5DEB3', rgb: [245, 222, 179] },
        { name: 'Charcoal Gray', hex: '#36454F', rgb: [54, 69, 79] }
      ],
      saveData: {
        name: '',
        projectId: '',
        paintBrand: '',
        notes: ''
      },
      projects: [
        { id: 1, name: 'Living Room Painting', client: 'Juan Dela Cruz' },
        { id: 2, name: 'Bedroom Renovation', client: 'Maria Gonzales' },
        { id: 3, name: 'Kitchen Remodel', client: 'Robert Lim' },
        { id: 4, name: 'Office Space', client: 'Anna Santos' },
        { id: 5, name: 'Nursery Design', client: 'Carlos Reyes' }
      ],
      recentColors: [
        { id: 1, name: 'Ocean Breeze', hex: '#4CC9F0', rgb: [76, 201, 240] },
        { id: 2, name: 'Sage Green', hex: '#8A9A5B', rgb: [138, 154, 91] },
        { id: 3, name: 'Coral Sunset', hex: '#FF7F50', rgb: [255, 127, 80] },
        { id: 4, name: 'Midnight Blue', hex: '#191970', rgb: [25, 25, 112] },
        { id: 5, name: 'Soft Lavender', hex: '#B19CD9', rgb: [177, 156, 217] }
      ],
      showSaveModal: false,
      savedColorName: ''
    }
  },
  mounted() {
    this.simulateUnityLoading()
    this.setupColorPicker()
  },
  methods: {
    simulateUnityLoading() {
      this.isUnityLoading = true
      const interval = setInterval(() => {
        this.loadingProgress += 10
        if (this.loadingProgress >= 100) {
          clearInterval(interval)
          setTimeout(() => {
            this.isUnityLoading = false
          }, 500)
        }
      }, 300)
    },
    
    setupColorPicker() {
      // Initialize color picker
      this.updateColor()
    },
    
    updateColor() {
      // Convert RGB to HEX
      const [r, g, b] = this.currentColor.rgb
      this.currentColor.hex = this.rgbToHex(r, g, b)
      
      // Update Unity scene with new color
      this.updateUnityColor()
    },
    
    rgbToHex(r, g, b) {
      return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)
    },
    
    updateUnityColor() {
      // This would call Unity WebGL methods to update the scene
      console.log('Updating Unity color to:', this.currentColor)
      
      // Simulate Unity API call
      if (window.unityInstance) {
        // window.unityInstance.SendMessage('WallController', 'SetColor', 
        //   `${this.currentColor.rgb[0]},${this.currentColor.rgb[1]},${this.currentColor.rgb[2]}`)
      }
    },
    
    selectPreset(preset) {
      this.currentColor = { ...preset }
      this.saveData.name = preset.name
      this.updateColor()
    },
    
    changeScene(scene) {
      this.activeScene = scene
      console.log('Changing scene to:', scene)
      // This would call Unity to change the scene
    },
    
    saveColor() {
      if (!this.saveData.name) {
        alert('Please enter a color name')
        return
      }
      
      // Save color to database via API
      const colorData = {
        name: this.saveData.name,
        hex: this.currentColor.hex,
        rgb: this.currentColor.rgb,
        projectId: this.saveData.projectId,
        paintBrand: this.saveData.paintBrand,
        notes: this.saveData.notes,
        date: new Date().toISOString()
      }
      
      console.log('Saving color:', colorData)
      
      // Simulate API call
      setTimeout(() => {
        // Add to recent colors
        this.recentColors.unshift({
          id: Date.now(),
          name: colorData.name,
          hex: colorData.hex,
          rgb: colorData.rgb
        })
        
        // Keep only last 10 recent colors
        if (this.recentColors.length > 10) {
          this.recentColors = this.recentColors.slice(0, 10)
        }
        
        // Show success modal
        this.savedColorName = colorData.name
        this.showSaveModal = true
        
        // Reset save form
        this.saveData = {
          name: '',
          projectId: '',
          paintBrand: '',
          notes: ''
        }
      }, 500)
    },
    
    exportColor() {
      const colorData = {
        name: this.saveData.name || this.currentColor.name,
        hex: this.currentColor.hex,
        rgb: this.currentColor.rgb,
        date: new Date().toISOString()
      }
      
      // Create downloadable JSON file
      const dataStr = JSON.stringify(colorData, null, 2)
      const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr)
      
      const exportFileDefaultName = `${colorData.name.replace(/\s+/g, '_')}_${Date.now()}.json`
      
      const linkElement = document.createElement('a')
      linkElement.setAttribute('href', dataUri)
      linkElement.setAttribute('download', exportFileDefaultName)
      linkElement.click()
      
      console.log('Color exported:', colorData)
    },
    
    loadRecentColor(color) {
      this.currentColor = { ...color }
      this.saveData.name = color.name
      this.updateColor()
    },
    
    deleteRecentColor(colorId) {
      this.recentColors = this.recentColors.filter(color => color.id !== colorId)
    },
    
    linkToSystem() {
      // Link color to client, job, and distributor inventory
      const linkData = {
        color: this.currentColor,
        client: 'Juan Dela Cruz',
        project: 'Living Room Painting',
        distributor: 'Cavite Paint Supply',
        date: new Date().toISOString()
      }
      
      console.log('Linking color to system:', linkData)
      alert('Color linked to system successfully!')
    },
    
    toggleFullscreen() {
      const element = document.querySelector('.unity-webgl-canvas')
      if (!document.fullscreenElement) {
        element.requestFullscreen().catch(err => {
          console.error(`Error attempting to enable fullscreen: ${err.message}`)
        })
      } else {
        document.exitFullscreen()
      }
    },
    
    resetScene() {
      this.currentColor = {
        name: 'Ocean Breeze',
        hex: '#4CC9F0',
        rgb: [76, 201, 240]
      }
      this.updateColor()
      console.log('Scene reset to default')
    },
    
    closeSaveModal() {
      this.showSaveModal = false
    },
    
    goToJobs() {
      this.closeSaveModal()
      this.$router.push('/service-provider/jobs')
    }
  }
}
</script>

<style scoped>
.unity-container {
  background: linear-gradient(180deg, #111827 0%, #1f2937 100%);
  min-height: 100vh;
}

/* Status Badge */
.unity-status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background: rgba(16, 185, 129, 0.1);
  border: 1px solid rgba(16, 185, 129, 0.3);
  border-radius: 9999px;
  backdrop-filter: blur(10px);
}

/* Action Buttons */
.unity-action-button {
  width: 40px;
  height: 40px;
  border-radius: 0.625rem;
  background: linear-gradient(45deg, #2d3748, #4a5568);
  border: 1px solid #4a5568;
  color: #cbd5e0;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  cursor: pointer;
}

.unity-action-button:hover {
  background: linear-gradient(45deg, #4a5568, #718096);
  border-color: #718096;
  transform: translateY(-2px);
}

/* Project Info Cards */
.project-info-card {
  padding: 1.25rem;
  border-radius: 1rem;
  background: linear-gradient(to bottom right, #1a202c, #2d3748);
  border: 1px solid #4a5568;
  transition: all 0.3s ease;
}

.project-info-card:hover {
  transform: translateY(-2px);
  border-color: #4299e1;
  box-shadow: 0 8px 20px rgba(66, 153, 225, 0.1);
}

.project-icon {
  width: 48px;
  height: 48px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
}

/* Unity Canvas Container */
.unity-canvas-container {
  position: relative;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  background: #000;
  min-height: 500px;
}

/* Loading Screen */
.unity-loading-screen {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  backdrop-filter: blur(10px);
}

.loading-content {
  text-align: center;
  max-width: 400px;
  padding: 2rem;
}

.loading-spinner {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto 2rem;
}

.spinner-ring {
  position: absolute;
  width: 100%;
  height: 100%;
  border: 8px solid transparent;
  border-top: 8px solid #4299e1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-progress {
  margin-top: 2rem;
}

.progress-bar {
  width: 100%;
  height: 8px;
  background: #2d3748;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #4299e1, #9f7aea);
  border-radius: 4px;
  transition: width 0.3s ease;
}

/* Unity WebGL Canvas */
.unity-webgl-canvas {
  width: 100%;
  height: 500px;
  background: #1a1a1a;
}

.unity-placeholder {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* House Visualization */
.placeholder-house {
  position: relative;
  width: 300px;
  height: 300px;
  perspective: 1000px;
}

.house-container {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  transform: rotateY(-30deg) rotateX(10deg);
}

.house-main {
  position: absolute;
  width: 200px;
  height: 150px;
  background: #4CC9F0;
  left: 50px;
  top: 100px;
  border-radius: 10px 10px 0 0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  transition: background-color 0.3s ease;
}

.house-roof {
  position: absolute;
  width: 0;
  height: 0;
  border-left: 120px solid transparent;
  border-right: 120px solid transparent;
  border-bottom: 80px solid #2d3748;
  top: -70px;
  left: -20px;
}

.house-door {
  position: absolute;
  width: 40px;
  height: 60px;
  background: #8B4513;
  bottom: 0;
  left: 80px;
  border-radius: 5px 5px 0 0;
}

.house-windows {
  position: absolute;
  top: 30px;
  width: 100%;
}

.window {
  position: absolute;
  width: 30px;
  height: 30px;
  background: #ADD8E6;
  border: 3px solid #1a202c;
  border-radius: 5px;
}

.left-window {
  left: 20px;
}

.right-window {
  right: 20px;
}

.top-window {
  left: 85px;
}

.side-wall {
  position: absolute;
  width: 100px;
  height: 150px;
  background: #4CC9F0;
  left: 200px;
  top: 100px;
  transform: skewY(30deg);
  border-radius: 0 10px 0 0;
  transition: background-color 0.3s ease;
}

.ground {
  position: absolute;
  width: 400px;
  height: 50px;
  background: #4a5568;
  bottom: 0;
  left: -50px;
  border-radius: 10px;
}

.unity-canvas-badge {
  position: absolute;
  top: 20px;
  right: 20px;
  background: linear-gradient(45deg, #8b5cf6, #ec4899);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.unity-badge-text {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.unity-badge-version {
  font-size: 0.6rem;
  opacity: 0.8;
}

.unity-instructions {
  position: absolute;
  bottom: 20px;
  left: 20px;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(10px);
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid #4a5568;
}

.instruction-item {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
}

.instruction-item:last-child {
  margin-bottom: 0;
}

/* Scene Buttons */
.scene-button {
  padding: 0.75rem 1rem;
  background: linear-gradient(45deg, #2d3748, #4a5568);
  border: 1px solid #4a5568;
  border-radius: 0.75rem;
  color: #cbd5e0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 500;
  transition: all 0.3s ease;
  cursor: pointer;
}

.scene-button:hover {
  transform: translateY(-2px);
  border-color: #4299e1;
}

.scene-button.active {
  background: linear-gradient(45deg, #4299e1, #9f7aea);
  color: white;
  border-color: #9f7aea;
  box-shadow: 0 4px 15px rgba(66, 153, 225, 0.3);
}

/* Control Panel */
.control-panel {
  backdrop-filter: blur(10px);
}

/* Color Preview */
.color-preview {
  background: rgba(0, 0, 0, 0.3);
  border-radius: 1rem;
  padding: 1.5rem;
}

.color-display {
  height: 100px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.color-name {
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-weight: 600;
  backdrop-filter: blur(10px);
}

/* Slider Controls */
.slider-control {
  background: rgba(255, 255, 255, 0.05);
  padding: 1rem;
  border-radius: 0.75rem;
}

.color-slider {
  width: 100%;
  height: 10px;
  border-radius: 5px;
  outline: none;
  -webkit-appearance: none;
  appearance: none;
  background: linear-gradient(to right, #2d3748, #4a5568);
}

.color-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  border: 3px solid white;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

.red-slider::-webkit-slider-thumb {
  background: #ef4444;
}

.green-slider::-webkit-slider-thumb {
  background: #10b981;
}

.blue-slider::-webkit-slider-thumb {
  background: #3b82f6;
}

/* Preset Colors */
.preset-color-button {
  width: 100%;
  aspect-ratio: 1;
  border-radius: 0.5rem;
  border: 2px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
}

.preset-color-button:hover {
  transform: scale(1.1);
  border-color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

/* Form Styles */
.form-label {
  display: block;
  margin-bottom: 0.5rem;
  color: #cbd5e0;
  font-size: 0.875rem;
  font-weight: 500;
}

.form-input,
.form-select,
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

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #4299e1;
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.form-textarea {
  resize: vertical;
}

/* Save Buttons */
.save-button {
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
}

.save-button.primary {
  background: linear-gradient(45deg, #4299e1, #9f7aea);
  color: white;
}

.save-button.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(66, 153, 225, 0.4);
}

.save-button.secondary {
  background: linear-gradient(45deg, #4a5568, #718096);
  color: white;
}

.save-button.secondary:hover {
  transform: translateY(-2px);
  background: linear-gradient(45deg, #718096, #a0aec0);
}

/* Recent Colors */
.recent-color-item {
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.recent-color-item:hover {
  background: rgba(66, 153, 225, 0.1);
  transform: translateX(4px);
}

/* Integration Items */
.integration-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 0.75rem;
}

.integration-icon {
  width: 36px;
  height: 36px;
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.integration-button {
  width: 100%;
  padding: 0.75rem 1rem;
  background: linear-gradient(45deg, #8b5cf6, #ec4899);
  color: white;
  border: none;
  border-radius: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.integration-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(139, 92, 246, 0.4);
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
  max-width: 500px;
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
  justify-content: center;
  gap: 1rem;
}

.modal-action-button {
  padding: 0.625rem 1.25rem;
  border-radius: 0.625rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid #4a5568;
  background: transparent;
  color: #cbd5e0;
}

.modal-action-button:hover {
  background: #4a5568;
  color: white;
}

.modal-action-button.primary {
  background: linear-gradient(45deg, #4299e1, #9f7aea);
  border: none;
  color: white;
}

.modal-action-button.primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(66, 153, 225, 0.4);
}

/* Responsive Design */
@media (max-width: 768px) {
  .unity-content {
    grid-template-columns: 1fr;
  }
  
  .unity-canvas-container {
    min-height: 400px;
  }
  
  .unity-webgl-canvas {
    height: 400px;
  }
  
  .placeholder-house {
    transform: scale(0.8);
  }
  
  .scene-button {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
  }
}

@media (max-width: 640px) {
  .project-info-card {
    padding: 1rem;
  }
  
  .modal-container {
    width: 95%;
    margin: 1rem;
  }
  
  .color-presets-grid {
    grid-template-columns: repeat(4, 1fr);
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
  background: linear-gradient(45deg, #8b5cf6, #ec4899);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(45deg, #7c3aed, #db2777);
}

/* Animations */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.control-panel,
.project-info-card,
.scene-button {
  animation: fadeIn 0.5s ease-out;
}

/* House Animation */
.house-main,
.side-wall {
  transition: background-color 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>