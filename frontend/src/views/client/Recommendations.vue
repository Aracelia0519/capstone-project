<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-900 to-gray-950 p-6 md:p-8 overflow-x-hidden">
    <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-8">
      <div class="flex-1 min-w-[300px]">
        <h1 class="flex items-center gap-3 text-3xl font-bold bg-gradient-to-r from-sky-400 to-cyan-400 bg-clip-text text-transparent mb-2">
          <svg class="w-8 h-8 flex-shrink-0 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
          </svg>
          Color Recommendations
        </h1>
        <p class="text-slate-400 text-sm max-w-[600px]">Personalized paint suggestions based on your preferences and Cavite trends</p>
      </div>
      <div class="flex items-center gap-4 w-full md:w-auto justify-start">
        <Button 
          variant="outline" 
          class="gap-2 bg-sky-400/10 border-sky-400/30 text-sky-400 hover:bg-sky-400/20 hover:text-sky-300 transition-all duration-200"
          @click="refreshRecommendations"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </Button>
        <Badge variant="outline" class="gap-1.5 py-1.5 px-3.5 bg-violet-500/10 border-violet-500/30 text-violet-400 text-xs font-medium rounded-full hover:bg-violet-500/10">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Powered by DSS
        </Badge>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <Card class="flex items-center gap-4 p-5 bg-slate-800/50 border-slate-700/30 backdrop-blur-md hover:-translate-y-0.5 hover:border-sky-400/30 hover:shadow-lg transition-all duration-200">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-r from-blue-500 to-cyan-500 shadow-lg shadow-blue-500/20">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
          </svg>
        </div>
        <div>
          <div class="text-2xl font-bold text-slate-100 leading-tight">{{ stats.totalSuggestions }}</div>
          <div class="text-xs text-slate-400 uppercase tracking-wide font-medium">Total Suggestions</div>
        </div>
      </Card>
      
      <Card class="flex items-center gap-4 p-5 bg-slate-800/50 border-slate-700/30 backdrop-blur-md hover:-translate-y-0.5 hover:border-purple-400/30 hover:shadow-lg transition-all duration-200">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-r from-purple-500 to-pink-500 shadow-lg shadow-purple-500/20">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
          </svg>
        </div>
        <div>
          <div class="text-2xl font-bold text-slate-100 leading-tight">{{ stats.topRated }}</div>
          <div class="text-xs text-slate-400 uppercase tracking-wide font-medium">Top Rated</div>
        </div>
      </Card>
      
      <Card class="flex items-center gap-4 p-5 bg-slate-800/50 border-slate-700/30 backdrop-blur-md hover:-translate-y-0.5 hover:border-amber-400/30 hover:shadow-lg transition-all duration-200">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center bg-gradient-to-r from-amber-500 to-yellow-500 shadow-lg shadow-amber-500/20">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div>
          <div class="text-2xl font-bold text-slate-100 leading-tight">{{ stats.recentTrends }}</div>
          <div class="text-xs text-slate-400 uppercase tracking-wide font-medium">Recent Trends</div>
        </div>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
      <Card class="bg-slate-800/70 border-slate-700/30 backdrop-blur-md p-6 hover:border-sky-400/20 hover:shadow-2xl transition-all duration-200 h-full">
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center gap-2 text-lg font-semibold text-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            Suggested Colors For You
          </div>
          <Badge class="bg-sky-400/20 text-sky-400 border-sky-400/30 uppercase text-xs font-semibold px-3 py-1 hover:bg-sky-400/20">Personalized</Badge>
        </div>
        
        <div class="grid grid-cols-2 gap-4">
          <div 
            v-for="color in suggestedColors" 
            :key="color.id" 
            class="group cursor-pointer rounded-xl overflow-hidden bg-slate-900/50 border border-slate-700/30 hover:-translate-y-0.5 hover:border-sky-400/40 hover:shadow-lg transition-all duration-200"
            @click="previewColor(color)"
          >
            <div class="h-[120px] relative" :style="{ backgroundColor: color.hex }">
              <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </div>
            </div>
            <div class="p-4">
              <div class="font-semibold text-slate-100 mb-1">{{ color.name }}</div>
              <div class="font-mono text-xs text-slate-400 mb-3">{{ color.hex }}</div>
              <div class="flex items-center gap-2">
                <Progress :model-value="color.match" class="h-1 bg-slate-700/50" indicator-class="bg-gradient-to-r from-sky-400 to-cyan-400" />
                <span class="text-xs text-slate-400 min-w-[60px] text-right">{{ color.match }}% match</span>
              </div>
            </div>
          </div>
        </div>
      </Card>

      <Card class="bg-slate-800/70 border-slate-700/30 backdrop-blur-md p-6 hover:border-pink-500/20 hover:shadow-2xl transition-all duration-200 h-full">
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center gap-2 text-lg font-semibold text-slate-100">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Popular Colors in Cavite
          </div>
          <Badge class="bg-pink-500/20 text-pink-500 border-pink-500/30 uppercase text-xs font-semibold px-3 py-1 hover:bg-pink-500/20">Trending</Badge>
        </div>
        
        <div class="flex flex-col gap-3">
          <div 
            v-for="(popular, index) in popularColors" 
            :key="popular.id" 
            class="flex items-center gap-3 p-3.5 bg-slate-900/50 border border-slate-700/30 rounded-xl hover:translate-x-1 hover:border-pink-500/30 hover:bg-pink-500/5 transition-all duration-200"
          >
            <div class="w-7 h-7 flex items-center justify-center bg-slate-800/80 rounded-full font-bold text-slate-100 text-xs flex-shrink-0">
              {{ index + 1 }}
            </div>
            <div class="w-10 h-10 rounded-lg border-2 border-white/10 flex-shrink-0" :style="{ backgroundColor: popular.hex }"></div>
            <div class="flex-1">
              <div class="font-semibold text-slate-100 leading-tight mb-0.5">{{ popular.name }}</div>
              <div class="flex items-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ popular.usage }} projects
              </div>
            </div>
            <div class="flex items-center gap-1 text-xs font-bold" :class="popular.trend === 'up' ? 'text-emerald-500' : 'text-red-500'">
              <svg v-if="popular.trend === 'up'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
              </svg>
              {{ popular.change }}%
            </div>
          </div>
        </div>
      </Card>

      <Card class="bg-slate-800/70 border-slate-700/30 backdrop-blur-md p-6 hover:border-violet-500/20 hover:shadow-2xl transition-all duration-200 h-full">
        <div class="flex justify-between items-center mb-6">
          <div class="flex items-center gap-2 text-lg font-semibold text-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            Trend-Based Recommendations
          </div>
          <Badge class="bg-violet-500/20 text-violet-500 border-violet-500/30 uppercase text-xs font-semibold px-3 py-1 hover:bg-violet-500/20">AI Generated</Badge>
        </div>
        
        <div class="flex flex-col gap-4">
          <div 
            v-for="trend in trendRecommendations" 
            :key="trend.id" 
            class="flex items-center gap-4 p-4 bg-slate-900/50 border border-slate-700/30 rounded-xl hover:translate-x-1 hover:border-violet-500/30 hover:bg-violet-500/5 transition-all duration-200"
          >
            <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-white" :class="trend.iconBg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="getTrendIcon(trend.type)"></svg>
            </div>
            <div class="flex-1">
              <h4 class="font-semibold text-slate-100 mb-1">{{ trend.title }}</h4>
              <p class="text-xs text-slate-400 mb-2">{{ trend.description }}</p>
              <div class="flex gap-1.5">
                <div v-for="color in trend.colors" :key="color" class="w-5 h-5 rounded border border-white/10" :style="{ backgroundColor: color }"></div>
              </div>
            </div>
            <Button 
              size="sm" 
              class="gap-1.5 bg-sky-400/10 border-sky-400/30 text-sky-400 hover:bg-sky-400/20 hover:text-sky-300 transition-all"
              @click="applyTrend(trend)"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              Apply
            </Button>
          </div>
        </div>
      </Card>

      <Card class="col-span-1 lg:col-span-2 xl:col-span-3 bg-slate-800/70 border-slate-700/30 backdrop-blur-md p-6 hover:border-sky-400/20 hover:shadow-2xl transition-all duration-200">
        <div class="flex items-center gap-2 text-lg font-semibold text-slate-100 mb-6">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
          </svg>
          Filter Recommendations
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="flex flex-col gap-3">
            <label class="text-sm font-semibold text-slate-200">Room Type</label>
            <div class="flex flex-wrap gap-2">
              <Badge 
                v-for="room in roomTypes" 
                :key="room" 
                :variant="selectedRoom === room ? 'default' : 'secondary'"
                class="cursor-pointer px-4 py-2 rounded-full text-xs font-medium transition-all duration-200 hover:text-sky-400 hover:border-sky-400/30"
                :class="selectedRoom === room 
                  ? 'bg-sky-400/20 border-sky-400/40 text-sky-400' 
                  : 'bg-slate-900/50 border-slate-700/30 text-slate-400'"
                @click="selectedRoom = room"
              >
                {{ room }}
              </Badge>
            </div>
          </div>
          
          <div class="flex flex-col gap-3">
            <label class="text-sm font-semibold text-slate-200">Color Temperature</label>
            <div class="py-2">
              <div class="flex justify-between text-xs text-slate-400 mb-2">
                <span>Cool</span>
                <span>Neutral</span>
                <span>Warm</span>
              </div>
              <Slider 
                v-model="temperatureArray" 
                :max="100" 
                :step="1" 
                class="w-full"
              />
              <div class="flex justify-between mt-2 px-2.5">
                <span v-for="n in 5" :key="n" class="w-0.5 h-1.5 bg-slate-700/50"></span>
              </div>
            </div>
          </div>
          
          <div class="flex flex-col gap-3">
            <label class="text-sm font-semibold text-slate-200">Brightness Level</label>
            <div class="flex items-center gap-4">
              <Button 
                variant="outline" 
                size="icon" 
                class="h-9 w-9 bg-slate-900/50 border-slate-700/30 text-slate-400 hover:text-sky-400 hover:border-sky-400/30"
                @click="decreaseBrightness"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              </Button>
              <div class="flex gap-1 flex-1">
                <div 
                  v-for="level in brightnessLevels" 
                  :key="level" 
                  class="flex-1 h-2 rounded-sm transition-all duration-200" 
                  :class="brightness >= level ? 'bg-gradient-to-r from-amber-400 to-amber-500' : 'bg-slate-700/50'"
                ></div>
              </div>
              <Button 
                variant="outline" 
                size="icon"
                class="h-9 w-9 bg-slate-900/50 border-slate-700/30 text-slate-400 hover:text-sky-400 hover:border-sky-400/30"
                @click="increaseBrightness"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </Button>
            </div>
          </div>
        </div>
        
        <Button 
          class="w-full bg-gradient-to-r from-sky-400 to-cyan-400 text-white font-semibold py-6 hover:shadow-lg hover:shadow-sky-400/20 transition-all duration-200"
          @click="applyFilters"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          Apply Filters
        </Button>
      </Card>
    </div>

    <div class="sticky bottom-6 flex justify-center gap-4 mt-8 flex-col md:flex-row px-4 md:px-0">
      <Button 
        variant="secondary" 
        class="bg-slate-800/80 border border-slate-700/30 text-slate-200 backdrop-blur-md hover:translate-y-[-2px] hover:border-sky-400/30 hover:shadow-lg transition-all"
        @click="saveToFavorites"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
        Save Favorites
      </Button>
      <Button 
        class="bg-gradient-to-r from-sky-400 to-cyan-400 text-white border-none hover:shadow-lg hover:shadow-sky-400/30 hover:translate-y-[-2px] transition-all"
        @click="shareRecommendations"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
        </svg>
        Share
      </Button>
      <Button 
        variant="secondary" 
        class="bg-slate-800/80 border border-slate-700/30 text-slate-200 backdrop-blur-md hover:translate-y-[-2px] hover:border-sky-400/30 hover:shadow-lg transition-all"
        @click="exportRecommendations"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
        </svg>
        Export
      </Button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Slider } from '@/components/ui/slider'

// Initialize router
const router = useRouter()

// Data State
const stats = reactive({
  totalSuggestions: 24,
  topRated: 8,
  recentTrends: 12
})

const suggestedColors = ref([
  { id: 1, name: 'Ocean Breeze', hex: '#38bdf8', match: 92 },
  { id: 2, name: 'Misty Morning', hex: '#cbd5e1', match: 87 },
  { id: 3, name: 'Golden Hour', hex: '#fbbf24', match: 85 },
  { id: 4, name: 'Forest Green', hex: '#10b981', match: 78 },
  { id: 5, name: 'Lavender Dream', hex: '#a78bfa', match: 76 },
  { id: 6, name: 'Coral Blush', hex: '#fb7185', match: 72 }
])

const popularColors = ref([
  { id: 1, name: 'Cavite Blue', hex: '#0ea5e9', usage: 245, trend: 'up', change: 12 },
  { id: 2, name: 'Sandy Beige', hex: '#d1d5db', usage: 198, trend: 'up', change: 8 },
  { id: 3, name: 'Emerald Bay', hex: '#059669', usage: 176, trend: 'up', change: 15 },
  { id: 4, name: 'Sunset Orange', hex: '#f97316', usage: 154, trend: 'down', change: 3 },
  { id: 5, name: 'Misty Gray', hex: '#6b7280', usage: 143, trend: 'up', change: 5 }
])

const trendRecommendations = ref([
  { 
    id: 1, 
    type: 'coastal', 
    title: 'Coastal Vibes', 
    description: 'Perfect for beach-inspired interiors',
    colors: ['#38bdf8', '#22d3ee', '#a5f3fc'],
    iconBg: 'bg-gradient-to-r from-blue-500 to-cyan-500'
  },
  { 
    id: 2, 
    type: 'modern', 
    title: 'Modern Minimalist', 
    description: 'Clean, sleek contemporary palette',
    colors: ['#1e293b', '#475569', '#cbd5e1'],
    iconBg: 'bg-gradient-to-r from-slate-700 to-slate-500'
  },
  { 
    id: 3, 
    type: 'earthy', 
    title: 'Earthy Tones', 
    description: 'Natural, organic color scheme',
    colors: ['#854d0e', '#a16207', '#fef3c7'],
    iconBg: 'bg-gradient-to-r from-amber-700 to-yellow-500'
  }
])

const roomTypes = ['Living Room', 'Bedroom', 'Kitchen', 'Bathroom', 'Office']
const selectedRoom = ref('Living Room')

// Slider needs to be an array for Shadcn
const temperatureArray = ref([50])
// Computed property to interface with original logic expecting a number
const temperature = computed({
  get: () => temperatureArray.value[0],
  set: (val) => temperatureArray.value = [val]
})

const brightness = ref(3)
const brightnessLevels = [1, 2, 3, 4, 5]

// Methods
const refreshRecommendations = () => {
  // Simulate API call
  console.log('Refreshing recommendations...')
  // In real app, this would fetch new data from API
}

const previewColor = (color) => {
  console.log('Previewing color:', color)
  // Navigate to color preview page with Unity integration
  router.push({ 
    path: '/Clients/colorPreview',
    query: { color: color.hex, name: color.name }
  })
}

const applyTrend = (trend) => {
  console.log('Applying trend:', trend)
  // Apply trend colors to Unity module
}

const getTrendIcon = (type) => {
  const icons = {
    coastal: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />',
    modern: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
    earthy: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />'
  }
  return icons[type] || icons.modern
}

const decreaseBrightness = () => {
  if (brightness.value > 1) brightness.value--
}

const increaseBrightness = () => {
  if (brightness.value < 5) brightness.value++
}

const applyFilters = () => {
  console.log('Applying filters:', {
    room: selectedRoom.value,
    temperature: temperature.value,
    brightness: brightness.value
  })
  // Apply filters and refresh recommendations
}

const saveToFavorites = () => {
  console.log('Saving to favorites...')
}

const shareRecommendations = () => {
  console.log('Sharing recommendations...')
}

const exportRecommendations = () => {
  console.log('Exporting recommendations...')
}
</script>

<style scoped>
/* Scoped styles are mostly replaced by Tailwind classes above, 
   but specific non-Tailwind overrides can go here if needed */
</style>