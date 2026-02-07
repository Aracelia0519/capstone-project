<template>
  <div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">System Settings</h1>
        <p class="text-gray-600 mt-2">Admin control panel and system configuration</p>
      </div>
      <div class="flex gap-3">
        <Button 
          @click="resetToDefaults"
          variant="outline"
          class="border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Reset Defaults
        </Button>
        <Button 
          @click="saveSettings"
          class="bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700 shadow-lg border-0"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Save All Changes
        </Button>
      </div>
    </div>

    <div class="mb-8">
      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="border-b border-gray-200 w-full justify-start bg-transparent p-0 rounded-none h-auto">
          <TabsTrigger 
            v-for="tab in tabs"
            :key="tab.id"
            :value="tab.id"
            class="py-4 px-1 border-b-2 rounded-none bg-transparent data-[state=active]:border-blue-500 data-[state=active]:text-blue-600 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 data-[state=active]:bg-transparent data-[state=active]:shadow-none mr-8"
          >
            <div class="flex items-center gap-2">
              <component :is="tab.icon" class="w-5 h-5" />
              {{ tab.name }}
            </div>
          </TabsTrigger>
        </TabsList>

        <TabsContent value="general" class="space-y-8 mt-8">
          <Card class="shadow-sm border-gray-100">
            <CardContent class="p-6">
              <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                General Settings
              </h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">System Name</label>
                  <Input
                    v-model="settings.systemName"
                    type="text"
                    placeholder="CaviteGo Paint Management System"
                    class="border-gray-300 focus-visible:ring-blue-500"
                  />
                  <p class="text-xs text-gray-500 mt-2">This name appears in the header and browser tab.</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Semester / Project</label>
                  <Select v-model="settings.semester">
                    <SelectTrigger class="border-gray-300">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="2nd Semester 2023-2024">2nd Semester 2023-2024</SelectItem>
                      <SelectItem value="1st Semester 2024-2025">1st Semester 2024-2025</SelectItem>
                      <SelectItem value="2nd Semester 2024-2025">2nd Semester 2024-2025</SelectItem>
                      <SelectItem value="Capstone Project">Capstone Project</SelectItem>
                    </SelectContent>
                  </Select>
                  <p class="text-xs text-gray-500 mt-2">For academic reference and reporting.</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="shadow-sm border-gray-100">
            <CardContent class="p-6">
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
                  <Button variant="outline" class="border-gray-300 text-gray-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    Upload New Logo
                  </Button>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Brand Color</label>
                  <div class="flex gap-2">
                    <div class="relative w-16 h-12">
                      <input
                        v-model="settings.brandColor"
                        type="color"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                      />
                      <div 
                        class="w-full h-full rounded-lg border-2 border-gray-200" 
                        :style="{ backgroundColor: settings.brandColor }"
                      ></div>
                    </div>
                    <Input
                      v-model="settings.brandColor"
                      type="text"
                      placeholder="#4A90E2"
                      class="flex-1 border-gray-300 focus-visible:ring-blue-500"
                    />
                  </div>
                  <p class="text-xs text-gray-500 mt-2">Primary color used throughout the system.</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="security" class="space-y-8 mt-8">
          <Card class="shadow-sm border-gray-100">
            <CardContent class="p-6">
              <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Security & Authentication
              </h2>
              
              <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Token Expiration Time</label>
                    <Select v-model="settings.tokenExpiration">
                      <SelectTrigger class="border-gray-300">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="1">1 hour</SelectItem>
                        <SelectItem value="8">8 hours</SelectItem>
                        <SelectItem value="24">24 hours</SelectItem>
                        <SelectItem value="168">7 days</SelectItem>
                        <SelectItem value="720">30 days</SelectItem>
                      </SelectContent>
                    </Select>
                    <p class="text-xs text-gray-500 mt-2">How long authentication tokens remain valid.</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
                    <Input
                      v-model="settings.maxLoginAttempts"
                      type="number"
                      min="1"
                      max="10"
                      class="border-gray-300 focus-visible:ring-blue-500"
                    />
                    <p class="text-xs text-gray-500 mt-2">Account lockout after failed attempts.</p>
                  </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                  <h3 class="text-lg font-semibold text-gray-800 mb-4">Session Management</h3>
                  <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                      <Switch 
                        id="auto-logout" 
                        :checked="settings.enableAutoLogout" 
                        @update:checked="val => settings.enableAutoLogout = val"
                      />
                      <label for="auto-logout" class="text-gray-700 cursor-pointer">Enable automatic logout after inactivity</label>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                      <Switch 
                        id="session-record" 
                        :checked="settings.enableSessionRecording" 
                        @update:checked="val => settings.enableSessionRecording = val"
                      />
                      <label for="session-record" class="text-gray-700 cursor-pointer">Record user sessions for audit purposes</label>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                      <Switch 
                        id="strong-pass" 
                        :checked="settings.requireStrongPasswords" 
                        @update:checked="val => settings.requireStrongPasswords = val"
                      />
                      <label for="strong-pass" class="text-gray-700 cursor-pointer">Require strong passwords (min. 8 characters)</label>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="ai" class="space-y-8 mt-8">
          <Card class="shadow-sm border-gray-100">
            <CardContent class="p-6">
              <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                AI & Machine Learning
              </h2>
              
              <div class="space-y-6">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
                  <div class="flex items-center justify-between mb-4">
                    <div>
                      <h3 class="font-semibold text-green-800 text-lg">Machine Learning Module</h3>
                      <p class="text-sm text-green-600 mt-1">Enable/disable AI features for Decision Support System</p>
                    </div>
                    <Switch 
                      :checked="settings.enableML" 
                      @update:checked="toggleML"
                      class="data-[state=checked]:bg-green-600"
                    />
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

                <div v-if="settings.enableML" class="space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">ML Model Accuracy Threshold</label>
                      <div class="flex items-center gap-4">
                        <Slider 
                          :model-value="[settings.mlAccuracyThreshold]"
                          @update:model-value="(v) => settings.mlAccuracyThreshold = v[0]" 
                          :min="70" 
                          :max="95" 
                          :step="5"
                          class="w-full"
                        />
                        <span class="text-sm font-medium text-gray-700 w-12">{{ settings.mlAccuracyThreshold }}%</span>
                      </div>
                      <p class="text-xs text-gray-500 mt-2">Minimum confidence level for ML predictions.</p>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Training Frequency</label>
                      <Select v-model="settings.mlTrainingFrequency">
                        <SelectTrigger class="border-gray-300">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="daily">Daily</SelectItem>
                          <SelectItem value="weekly">Weekly</SelectItem>
                          <SelectItem value="monthly">Monthly</SelectItem>
                          <SelectItem value="manual">Manual Only</SelectItem>
                        </SelectContent>
                      </Select>
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
                          <Badge :class="model.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" variant="secondary" class="border-0">
                            {{ model.status === 'active' ? 'Active' : 'Training' }}
                          </Badge>
                          <Button 
                            variant="ghost" 
                            size="icon"
                            @click="toggleMLModel(model)"
                            class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 h-8 w-8"
                          >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path v-if="model.status === 'active'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="unity" class="space-y-8 mt-8">
          <Card class="shadow-sm border-gray-100">
            <CardContent class="p-6">
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
                    <Switch 
                      :checked="settings.enableUnity" 
                      @update:checked="toggleUnity"
                      class="data-[state=checked]:bg-purple-600"
                    />
                  </div>
                  
                  <div v-if="settings.enableUnity" class="mt-4 space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-purple-700 mb-2">Unity WebGL Quality</label>
                      <Select v-model="settings.unityQuality">
                        <SelectTrigger class="border-purple-200 bg-white">
                          <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="low">Low (Faster Loading)</SelectItem>
                          <SelectItem value="medium">Medium (Balanced)</SelectItem>
                          <SelectItem value="high">High (Best Visuals)</SelectItem>
                        </SelectContent>
                      </Select>
                    </div>
                    
                    <div>
                      <label class="block text-sm font-medium text-purple-700 mb-2">API Endpoint for Unity</label>
                      <div class="flex">
                        <Input
                          :model-value="unityApiEndpoint"
                          readonly
                          class="rounded-r-none border-purple-200 bg-gray-50 text-gray-600"
                        />
                        <Button
                          @click="copyApiEndpoint"
                          class="rounded-l-none bg-purple-600 hover:bg-purple-700 text-white"
                        >
                          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                          </svg>
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>

    <div v-if="saveStatus" class="fixed bottom-4 right-4 z-50 transition-all duration-300">
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
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Switch } from '@/components/ui/switch'
import { Slider } from '@/components/ui/slider'
import { Badge } from '@/components/ui/badge'

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

const toggleML = (val) => {
  settings.value.enableML = val
}

const toggleUnity = (val) => {
  settings.value.enableUnity = val
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
/* Color input styling override for custom aesthetic */
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

/* Responsive adjustments */
@media (max-width: 768px) {
  .settings-grid {
    grid-template-columns: 1fr;
  }
}
</style>