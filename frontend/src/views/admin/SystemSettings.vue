<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">System Settings</h1>
        <p class="text-gray-600 mt-2">Admin control panel and system configuration</p>
      </div>
      <div class="flex gap-3">
        <button 
          @click="resetToDefaults"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Reset Defaults
        </button>
        <button 
          @click="saveSettings"
          class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Save All Changes
        </button>
      </div>
    </div>

    <!-- Settings Tabs -->
    <div class="mb-8">
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <div class="flex items-center gap-2">
              <component :is="tab.icon" class="w-5 h-5" />
              {{ tab.name }}
            </div>
          </button>
        </nav>
      </div>
    </div>

    <!-- General Settings -->
    <div v-if="activeTab === 'general'" class="space-y-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          General Settings
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- System Name -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">System Name</label>
            <input
              v-model="settings.systemName"
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition-all"
              placeholder="CaviteGo Paint Management System"
            >
            <p class="text-xs text-gray-500 mt-2">This name appears in the header and browser tab.</p>
          </div>

          <!-- Semester/Project -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Semester / Project</label>
            <select
              v-model="settings.semester"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
            >
              <option value="2nd Semester 2023-2024">2nd Semester 2023-2024</option>
              <option value="1st Semester 2024-2025">1st Semester 2024-2025</option>
              <option value="2nd Semester 2024-2025">2nd Semester 2024-2025</option>
              <option value="Capstone Project">Capstone Project</option>
            </select>
            <p class="text-xs text-gray-500 mt-2">For academic reference and reporting.</p>
          </div>
        </div>
      </div>

      <!-- Logo Settings -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Logo & Branding
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Current Logo</label>
              <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-16 h-16 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                </svg>
              </div>
            </div>
            <button class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
              </svg>
              Upload New Logo
            </button>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Brand Color</label>
            <div class="flex gap-2">
              <input
                v-model="settings.brandColor"
                type="color"
                class="w-16 h-16 cursor-pointer"
              >
              <input
                v-model="settings.brandColor"
                type="text"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                placeholder="#4A90E2"
              >
            </div>
            <p class="text-xs text-gray-500 mt-2">Primary color used throughout the system.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Security Settings -->
    <div v-if="activeTab === 'security'" class="space-y-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          Security & Authentication
        </h2>
        
        <div class="space-y-6">
          <!-- Token Settings -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Token Expiration Time</label>
              <select
                v-model="settings.tokenExpiration"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
              >
                <option value="1">1 hour</option>
                <option value="8">8 hours</option>
                <option value="24">24 hours</option>
                <option value="168">7 days</option>
                <option value="720">30 days</option>
              </select>
              <p class="text-xs text-gray-500 mt-2">How long authentication tokens remain valid.</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
              <input
                v-model="settings.maxLoginAttempts"
                type="number"
                min="1"
                max="10"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
              >
              <p class="text-xs text-gray-500 mt-2">Account lockout after failed attempts.</p>
            </div>
          </div>

          <!-- Session Settings -->
          <div class="border-t border-gray-100 pt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Session Management</h3>
            <div class="space-y-4">
              <label class="flex items-center">
                <input
                  v-model="settings.enableAutoLogout"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-3 text-gray-700">Enable automatic logout after inactivity</span>
              </label>
              
              <label class="flex items-center">
                <input
                  v-model="settings.enableSessionRecording"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-3 text-gray-700">Record user sessions for audit purposes</span>
              </label>
              
              <label class="flex items-center">
                <input
                  v-model="settings.requireStrongPasswords"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="ml-3 text-gray-700">Require strong passwords (min. 8 characters)</span>
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- AI/ML Settings -->
    <div v-if="activeTab === 'ai'" class="space-y-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
          AI & Machine Learning
        </h2>
        
        <div class="space-y-6">
          <!-- ML Toggle -->
          <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="font-semibold text-green-800 text-lg">Machine Learning Module</h3>
                <p class="text-sm text-green-600 mt-1">Enable/disable AI features for Decision Support System</p>
              </div>
              <button
                @click="toggleML"
                :class="[
                  'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2',
                  settings.enableML ? 'bg-green-600' : 'bg-gray-200'
                ]"
              >
                <span
                  :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                    settings.enableML ? 'translate-x-6' : 'translate-x-1'
                  ]"
                />
              </button>
            </div>
            
            <div class="mt-4">
              <div class="flex items-center gap-2 text-sm text-green-700 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Status: {{ settings.enableML ? 'ACTIVE' : 'DISABLED' }}
              </div>
              <p class="text-xs text-green-600">
                When enabled, the system will use ML algorithms for:
                <br>• Color recommendations
                <br>• Demand forecasting
                <br>• Trend analysis
              </p>
            </div>
          </div>

          <!-- ML Settings -->
          <div v-if="settings.enableML" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ML Model Accuracy Threshold</label>
                <div class="flex items-center gap-4">
                  <input
                    v-model="settings.mlAccuracyThreshold"
                    type="range"
                    min="70"
                    max="95"
                    step="5"
                    class="w-full"
                  >
                  <span class="text-sm font-medium text-gray-700">{{ settings.mlAccuracyThreshold }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Minimum confidence level for ML predictions.</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Training Frequency</label>
                <select
                  v-model="settings.mlTrainingFrequency"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                >
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                  <option value="manual">Manual Only</option>
                </select>
                <p class="text-xs text-gray-500 mt-2">How often ML models are retrained with new data.</p>
              </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
              <h3 class="text-lg font-semibold text-gray-800 mb-4">Active ML Models</h3>
              <div class="space-y-4">
                <div v-for="model in mlModels" :key="model.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                  <div>
                    <h4 class="font-medium text-gray-800">{{ model.name }}</h4>
                    <p class="text-sm text-gray-600">{{ model.description }}</p>
                  </div>
                  <div class="flex items-center gap-4">
                    <span :class="[
                      'px-3 py-1 rounded-full text-sm font-medium',
                      model.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                    ]">
                      {{ model.status === 'active' ? 'Active' : 'Training' }}
                    </span>
                    <button 
                      @click="toggleMLModel(model)"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path v-if="model.status === 'active'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Unity Integration -->
    <div v-if="activeTab === 'unity'" class="space-y-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
          </svg>
          Unity Integration
        </h2>
        
        <div class="space-y-6">
          <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h3 class="font-semibold text-purple-800 text-lg">Virtual Paint Color Mixing System</h3>
                <p class="text-sm text-purple-600 mt-1">WebGL Unity module for real-time color visualization</p>
              </div>
              <button
                @click="toggleUnity"
                :class="[
                  'relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2',
                  settings.enableUnity ? 'bg-purple-600' : 'bg-gray-200'
                ]"
              >
                <span
                  :class="[
                    'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                    settings.enableUnity ? 'translate-x-6' : 'translate-x-1'
                  ]"
                />
              </button>
            </div>
            
            <div v-if="settings.enableUnity" class="mt-4 space-y-4">
              <div>
                <label class="block text-sm font-medium text-purple-700 mb-2">Unity WebGL Quality</label>
                <select
                  v-model="settings.unityQuality"
                  class="w-full px-4 py-3 border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent outline-none bg-white"
                >
                  <option value="low">Low (Faster Loading)</option>
                  <option value="medium">Medium (Balanced)</option>
                  <option value="high">High (Best Visuals)</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-purple-700 mb-2">API Endpoint for Unity</label>
                <div class="flex">
                  <input
                    :value="unityApiEndpoint"
                    type="text"
                    readonly
                    class="flex-1 px-4 py-3 border border-purple-200 rounded-l-lg bg-gray-50 text-gray-600"
                  >
                  <button
                    @click="copyApiEndpoint"
                    class="px-4 py-3 bg-purple-600 text-white rounded-r-lg hover:bg-purple-700 transition-colors"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Status -->
    <div v-if="saveStatus" class="fixed bottom-4 right-4 z-50">
      <div :class="[
        'px-6 py-4 rounded-lg shadow-lg border',
        saveStatus.type === 'success' ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800'
      ]">
        <div class="flex items-center gap-3">
          <svg v-if="saveStatus.type === 'success'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <span class="font-medium">{{ saveStatus.message }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// State
const activeTab = ref('general')
const settings = ref({})
const mlModels = ref([])
const saveStatus = ref(null)

// Tabs with icons
const tabs = [
  { id: 'general', name: 'General', icon: 'CogIcon' },
  { id: 'security', name: 'Security', icon: 'LockClosedIcon' },
  { id: 'ai', name: 'AI/ML', icon: 'LightBulbIcon' },
  { id: 'unity', name: 'Unity', icon: 'ComputerDesktopIcon' }
]

// Icon components
const CogIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`
}

const LockClosedIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>`
}

const LightBulbIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>`
}

const ComputerDesktopIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>`
}

// Computed properties
const unityApiEndpoint = computed(() => {
  return `${window.location.origin}/api/v1/unity/color-mixes`
})

// Methods
const loadSettings = () => {
  // Load from localStorage or API in real app
  const savedSettings = localStorage.getItem('systemSettings')
  if (savedSettings) {
    settings.value = JSON.parse(savedSettings)
  } else {
    // Default settings
    settings.value = {
      systemName: 'CaviteGo Paint Management System',
      semester: '2nd Semester 2023-2024',
      brandColor: '#4A90E2',
      tokenExpiration: '24',
      maxLoginAttempts: 5,
      enableAutoLogout: true,
      enableSessionRecording: false,
      requireStrongPasswords: true,
      enableML: true,
      mlAccuracyThreshold: 85,
      mlTrainingFrequency: 'weekly',
      enableUnity: true,
      unityQuality: 'medium'
    }
  }
}

const saveSettings = () => {
  // Save to localStorage or API in real app
  localStorage.setItem('systemSettings', JSON.stringify(settings.value))
  
  // Show success message
  saveStatus.value = {
    type: 'success',
    message: 'Settings saved successfully!'
  }
  
  // Hide message after 3 seconds
  setTimeout(() => {
    saveStatus.value = null
  }, 3000)
}

const resetToDefaults = () => {
  if (confirm('Are you sure you want to reset all settings to defaults?')) {
    localStorage.removeItem('systemSettings')
    loadSettings()
    
    saveStatus.value = {
      type: 'success',
      message: 'Settings reset to defaults!'
    }
    
    setTimeout(() => {
      saveStatus.value = null
    }, 3000)
  }
}

const toggleML = () => {
  settings.value.enableML = !settings.value.enableML
}

const toggleUnity = () => {
  settings.value.enableUnity = !settings.value.enableUnity
}

const toggleMLModel = (model) => {
  model.status = model.status === 'active' ? 'training' : 'active'
}

const copyApiEndpoint = () => {
  navigator.clipboard.writeText(unityApiEndpoint.value)
  
  saveStatus.value = {
    type: 'success',
    message: 'API endpoint copied to clipboard!'
  }
  
  setTimeout(() => {
    saveStatus.value = null
  }, 2000)
}

// Load ML models
const loadMLModels = () => {
  mlModels.value = [
    {
      id: 1,
      name: 'Color Recommendation',
      description: 'Suggests paint colors based on room type and preferences',
      status: 'active'
    },
    {
      id: 2,
      name: 'Demand Forecasting',
      description: 'Predicts paint inventory needs based on historical data',
      status: 'active'
    },
    {
      id: 3,
      name: 'Trend Analysis',
      description: 'Identifies popular color trends in Cavite region',
      status: 'training'
    }
  ]
}

// Initialize
onMounted(() => {
  loadSettings()
  loadMLModels()
})
</script>

<style scoped>
/* Custom range slider */
input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  height: 8px;
  border-radius: 4px;
  background: #e5e7eb;
  outline: none;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

input[type="range"]::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Color input styling */
input[type="color"] {
  -webkit-appearance: none;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  padding: 0;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
  border-radius: 6px;
}

/* Toggle switch styling */
.toggle-bg {
  transition: background-color 0.2s ease;
}

.toggle-dot {
  transition: transform 0.2s ease;
}

/* Save status animation */
.save-status-enter-active,
.save-status-leave-active {
  transition: all 0.3s ease;
}

.save-status-enter-from,
.save-status-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

/* Tab animation */
.tab-content-enter-active,
.tab-content-leave-active {
  transition: opacity 0.3s ease;
}

.tab-content-enter-from,
.tab-content-leave-to {
  opacity: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
  
  .tab-nav {
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>