<template>
  <div class="p-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Color Customizations</h1>
        <p class="text-gray-600 mt-2">View and manage color mixes from Unity Virtual Paint System</p>
      </div>
      <div class="flex gap-3">
        <Button 
          variant="outline"
          @click="refreshData"
          class="border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center gap-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </Button>
        <Button 
          @click="openUnitySimulator"
          class="bg-gradient-to-r from-purple-500 to-pink-600 text-white hover:from-purple-600 hover:to-pink-700 shadow-lg hover:shadow-xl border-0 flex items-center gap-2 h-auto py-3"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
          </svg>
          Launch Unity Mixer
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
      <Card class="bg-white shadow-sm border border-gray-100">
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-purple-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Total Mixes</p>
              <p class="text-2xl font-bold text-gray-800">{{ colorMixes.length }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-white shadow-sm border border-gray-100">
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Active Service Providers</p>
              <p class="text-2xl font-bold text-gray-800">{{ uniqueServiceProviders }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-white shadow-sm border border-gray-100">
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-5.197a6 6 0 00-9 5.197" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">Total Clients</p>
              <p class="text-2xl font-bold text-gray-800">{{ uniqueClients }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-white shadow-sm border border-gray-100">
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-500">This Month</p>
              <p class="text-2xl font-bold text-gray-800">{{ mixesThisMonth }}</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="bg-white shadow-sm border border-gray-100 mb-6">
      <CardContent class="p-4">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 z-10">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search by client name, service provider, or color code..."
                class="pl-12 pr-4 border-gray-300 focus-visible:ring-blue-500"
              />
            </div>
          </div>
          <div class="flex gap-2">
            <Select v-model="filterServiceProvider">
              <SelectTrigger class="w-[200px] border-gray-300">
                <SelectValue placeholder="All Service Providers" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all_providers">All Service Providers</SelectItem>
                <SelectItem v-for="provider in serviceProviders" :key="provider" :value="provider">{{ provider }}</SelectItem>
              </SelectContent>
            </Select>

            <Select v-model="filterTimeRange">
              <SelectTrigger class="w-[180px] border-gray-300">
                <SelectValue placeholder="All Time" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Time</SelectItem>
                <SelectItem value="today">Today</SelectItem>
                <SelectItem value="week">This Week</SelectItem>
                <SelectItem value="month">This Month</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardContent>
    </Card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <Card 
        v-for="mix in filteredMixes" 
        :key="mix.id"
        class="bg-white shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
              <div 
                class="w-20 h-20 rounded-lg border border-gray-200 shadow-lg"
                :style="{ 
                  background: `linear-gradient(135deg, ${mix.colorHex} 0%, ${adjustColor(mix.colorHex, 20)} 100%)`,
                  boxShadow: `0 4px 20px ${mix.colorHex}40`
                }"
              ></div>
              <div>
                <h3 class="font-bold text-gray-800 text-lg">{{ mix.colorName }}</h3>
                <p class="text-sm text-gray-500 mt-1">Created by {{ mix.serviceProvider }}</p>
                <div class="flex items-center gap-3 mt-2">
                  <Badge variant="secondary" class="bg-gray-100 text-gray-800 hover:bg-gray-200">
                    {{ mix.colorHex }}
                  </Badge>
                  <Badge variant="secondary" class="bg-blue-100 text-blue-800 hover:bg-blue-200">
                    RGB: {{ mix.rgb.r }}, {{ mix.rgb.g }}, {{ mix.rgb.b }}
                  </Badge>
                </div>
              </div>
            </div>
            <div class="text-right">
              <Badge class="bg-green-100 text-green-800 hover:bg-green-100 shadow-none border-0">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Saved
              </Badge>
              <p class="text-xs text-gray-500 mt-2">{{ formatDate(mix.createdAt) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Client</p>
                  <p class="font-medium text-gray-800">{{ mix.clientName }}</p>
                  <p class="text-xs text-gray-400">{{ mix.clientEmail }}</p>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm text-gray-500">Service Provider</p>
                  <p class="font-medium text-gray-800">{{ mix.serviceProvider }}</p>
                  <p class="text-xs text-gray-400">{{ mix.serviceCompany }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-gradient-to-r from-gray-50 to-white p-4 rounded-lg mb-4">
            <h4 class="font-medium text-gray-700 mb-3 flex items-center gap-2">
              <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
              </svg>
              Color Formula Details
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
              <div class="text-center">
                <div class="w-12 h-12 rounded-lg mx-auto mb-2" style="background-color: #FF6B6B;"></div>
                <p class="text-xs text-gray-500">Red Base</p>
                <p class="font-medium">{{ mix.formula.red }}%</p>
              </div>
              <div class="text-center">
                <div class="w-12 h-12 rounded-lg mx-auto mb-2" style="background-color: #51C16B;"></div>
                <p class="text-xs text-gray-500">Green Base</p>
                <p class="font-medium">{{ mix.formula.green }}%</p>
              </div>
              <div class="text-center">
                <div class="w-12 h-12 rounded-lg mx-auto mb-2" style="background-color: #4A90E2;"></div>
                <p class="text-xs text-gray-500">Blue Base</p>
                <p class="font-medium">{{ mix.formula.blue }}%</p>
              </div>
              <div class="text-center">
                <div class="w-12 h-12 rounded-lg mx-auto mb-2" style="background-color: #FFFFFF; border: 1px solid #e5e7eb;"></div>
                <p class="text-xs text-gray-500">White Base</p>
                <p class="font-medium">{{ mix.formula.white }}%</p>
              </div>
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <Button 
              variant="ghost" 
              @click="viewColorDetails(mix)"
              class="text-blue-600 hover:bg-blue-50 hover:text-blue-700 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
              View Details
            </Button>
            <Button 
              variant="ghost" 
              @click="exportColor(mix)"
              class="text-green-600 hover:bg-green-50 hover:text-green-700 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
              </svg>
              Export to Paint
            </Button>
            <Button 
              variant="ghost" 
              @click="deleteColorMix(mix)"
              class="text-red-600 hover:bg-red-50 hover:text-red-700 flex items-center gap-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Delete
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card v-if="filteredMixes.length === 0" class="py-16 text-center bg-white shadow-sm border border-gray-100">
      <CardContent>
        <div class="flex justify-center mb-4">
          <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No color customizations found</h3>
        <p class="text-gray-500 mb-6">Start using the Unity Virtual Paint Mixer to create your first color customization.</p>
        <Button 
          @click="openUnitySimulator"
          class="bg-gradient-to-r from-purple-500 to-pink-600 text-white hover:from-purple-600 hover:to-pink-700 shadow-lg hover:shadow-xl inline-flex items-center gap-2 h-auto py-3"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create First Color Mix
        </Button>
      </CardContent>
    </Card>

    <Dialog :open="showUnityModal" @update:open="showUnityModal = $event">
      <DialogContent class="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle class="text-2xl font-bold text-gray-800 flex justify-between items-center">
            Unity Virtual Paint Mixer
          </DialogTitle>
        </DialogHeader>
        
        <div class="py-4">
          <div class="aspect-video bg-gradient-to-br from-gray-900 to-gray-800 rounded-lg flex items-center justify-center mb-6">
            <div class="text-center">
              <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 animate-pulse">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
              </div>
              <p class="text-white text-lg font-medium mb-2">Unity WebGL Loaded</p>
              <p class="text-gray-300 text-sm">Virtual paint color mixing simulation is running</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-700 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
                Color Mixing
              </h4>
              <p class="text-sm text-gray-600">Real-time color blending with pigment simulation</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-700 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Secure Save
              </h4>
              <p class="text-sm text-gray-600">Color data saved via Laravel API with token auth</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="font-medium text-gray-700 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                Real-time Preview
              </h4>
              <p class="text-sm text-gray-600">Instant visualization on 3D surfaces with lighting</p>
            </div>
          </div>
          
          <div class="border-t border-gray-200 pt-6">
            <div class="flex justify-end gap-4">
              <Button
                variant="outline"
                @click="showUnityModal = false"
                class="border-gray-300 text-gray-700 hover:bg-gray-50"
              >
                Close Preview
              </Button>
              <Button
                @click="simulateColorSave"
                class="bg-gradient-to-r from-purple-500 to-pink-600 text-white hover:from-purple-600 hover:to-pink-700 shadow-lg border-0"
              >
                <span class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Simulate Color Save
                </span>
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'

// State
const colorMixes = ref([])
const searchQuery = ref('')
const filterServiceProvider = ref('') // Initially empty for 'All'
const filterTimeRange = ref('all')
const showUnityModal = ref(false)

// Computed properties
const filteredMixes = computed(() => {
  return colorMixes.value.filter(mix => {
    const matchesSearch = !searchQuery.value || 
      mix.clientName.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      mix.serviceProvider.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      mix.colorHex.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      mix.colorName.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    // Check against specific value or empty/all_providers
    const matchesProvider = !filterServiceProvider.value || 
                          filterServiceProvider.value === 'all_providers' || 
                          mix.serviceProvider === filterServiceProvider.value
    
    const matchesTime = filterTimeRange.value === 'all' || 
      isWithinTimeRange(mix.createdAt, filterTimeRange.value)
    
    return matchesSearch && matchesProvider && matchesTime
  })
})

const uniqueServiceProviders = computed(() => {
  const providers = new Set(colorMixes.value.map(mix => mix.serviceProvider))
  return providers.size
})

const uniqueClients = computed(() => {
  const clients = new Set(colorMixes.value.map(mix => mix.clientName))
  return clients.size
})

const mixesThisMonth = computed(() => {
  const now = new Date()
  const thisMonth = now.getMonth()
  const thisYear = now.getFullYear()
  
  return colorMixes.value.filter(mix => {
    const mixDate = new Date(mix.createdAt)
    return mixDate.getMonth() === thisMonth && mixDate.getFullYear() === thisYear
  }).length
})

const serviceProviders = computed(() => {
  const unique = [...new Set(colorMixes.value.map(mix => mix.serviceProvider))]
  return unique.sort()
})

// Methods
const adjustColor = (hex, amount) => {
  // Simple function to adjust color brightness
  let usePound = false
  if (hex[0] === "#") {
    hex = hex.slice(1)
    usePound = true
  }
  
  const num = parseInt(hex, 16)
  let r = (num >> 16) + amount
  let g = ((num >> 8) & 0x00FF) + amount
  let b = (num & 0x0000FF) + amount
  
  r = Math.min(Math.max(0, r), 255)
  g = Math.min(Math.max(0, g), 255)
  b = Math.min(Math.max(0, b), 255)
  
  return (usePound ? "#" : "") + (b | (g << 8) | (r << 16)).toString(16).padStart(6, '0')
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const isWithinTimeRange = (dateString, range) => {
  const date = new Date(dateString)
  const now = new Date()
  
  switch (range) {
    case 'today':
      return date.toDateString() === now.toDateString()
    case 'week':
      const weekAgo = new Date(now.setDate(now.getDate() - 7))
      return date >= weekAgo
    case 'month':
      const monthAgo = new Date(now.setMonth(now.getMonth() - 1))
      return date >= monthAgo
    default:
      return true
  }
}

const openUnitySimulator = () => {
  showUnityModal.value = true
}

const refreshData = () => {
  // Simulate API refresh
  colorMixes.value = [...colorMixes.value] // Trigger reactivity
  console.log('Refreshing color mix data from API...')
}

const viewColorDetails = (mix) => {
  alert(`Viewing details for ${mix.colorName}\n\nHex: ${mix.colorHex}\nRGB: ${mix.rgb.r}, ${mix.rgb.g}, ${mix.rgb.b}\n\nFormula:\nRed: ${mix.formula.red}%\nGreen: ${mix.formula.green}%\nBlue: ${mix.formula.blue}%\nWhite: ${mix.formula.white}%`)
}

const exportColor = (mix) => {
  alert(`Exporting ${mix.colorName} to paint inventory...\n\nThis would trigger:\n1. Create new paint product\n2. Update inventory\n3. Link to Unity color database`)
}

const deleteColorMix = (mix) => {
  if (confirm(`Are you sure you want to delete "${mix.colorName}"?\nThis action cannot be undone.`)) {
    const index = colorMixes.value.findIndex(m => m.id === mix.id)
    if (index !== -1) {
      colorMixes.value.splice(index, 1)
    }
  }
}

const simulateColorSave = () => {
  const newMix = {
    id: Date.now(),
    colorName: `Custom Mix ${Math.floor(Math.random() * 1000)}`,
    colorHex: `#${Math.floor(Math.random()*16777215).toString(16).padStart(6, '0')}`,
    rgb: {
      r: Math.floor(Math.random() * 256),
      g: Math.floor(Math.random() * 256),
      b: Math.floor(Math.random() * 256)
    },
    serviceProvider: "ColorMaster Pro",
    serviceCompany: "Cavite Paint Services",
    clientName: "Sample Client",
    clientEmail: "client@example.com",
    formula: {
      red: Math.floor(Math.random() * 100),
      green: Math.floor(Math.random() * 100),
      blue: Math.floor(Math.random() * 100),
      white: Math.floor(Math.random() * 100)
    },
    createdAt: new Date().toISOString()
  }
  
  colorMixes.value.unshift(newMix)
  showUnityModal.value = false
  
  alert('Color mix saved successfully!\n\nThe color data has been sent to the Laravel backend and will be available for:\n1. Inventory management\n2. Decision Support System\n3. Future recommendations')
}

// Initialize with sample data
onMounted(() => {
  colorMixes.value = [
    {
      id: 1,
      colorName: "Sunset Glow",
      colorHex: "#FF8E53",
      rgb: { r: 255, g: 142, b: 83 },
      serviceProvider: "Maria Santos",
      serviceCompany: "Cavite Paint Masters",
      clientName: "Juan Dela Cruz",
      clientEmail: "juan.dc@email.com",
      formula: { red: 65, green: 25, blue: 5, white: 5 },
      createdAt: "2024-02-15T10:30:00"
    },
    {
      id: 2,
      colorName: "Ocean Breeze",
      colorHex: "#4A90E2",
      rgb: { r: 74, g: 144, b: 226 },
      serviceProvider: "Carlos Reyes",
      serviceCompany: "Coatings Philippines",
      clientName: "Ana Gonzales",
      clientEmail: "ana.g@email.com",
      formula: { red: 20, green: 40, blue: 90, white: 0 },
      createdAt: "2024-02-14T14:45:00"
    },
    {
      id: 3,
      colorName: "Meadow Green",
      colorHex: "#51C16B",
      rgb: { r: 81, g: 193, b: 107 },
      serviceProvider: "Maria Santos",
      serviceCompany: "Cavite Paint Masters",
      clientName: "Robert Lim",
      clientEmail: "r.lim@email.com",
      formula: { red: 15, green: 75, blue: 25, white: 5 },
      createdAt: "2024-02-13T09:15:00"
    },
    {
      id: 4,
      colorName: "Lavender Dream",
      colorHex: "#9B59B6",
      rgb: { r: 155, g: 89, b: 182 },
      serviceProvider: "James Wilson",
      serviceCompany: "Premium Paint Services",
      clientName: "Sarah Chen",
      clientEmail: "s.chen@email.com",
      formula: { red: 60, green: 35, blue: 70, white: 10 },
      createdAt: "2024-02-12T16:20:00"
    }
  ]
})
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Color card hover effects */
.color-card {
  transition: all 0.3s ease;
}

.color-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .color-mix-card {
    margin-bottom: 1rem;
  }
  
  .color-preview-large {
    width: 60px;
    height: 60px;
  }
  
  .color-formula-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>