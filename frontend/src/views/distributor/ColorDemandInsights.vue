<template>
  <div class="p-4 md:p-6">
    <!-- Page Header with Mode Toggle -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Color Demand Insights</h1>
          <p class="text-gray-600 mt-2">Decision Support System for paint distribution optimization</p>
        </div>
        <div class="flex items-center space-x-3">
          <div class="flex items-center bg-gray-100 rounded-lg p-1">
            <button 
              @click="setMode('rule-based')" 
              :class="activeMode === 'rule-based' ? 'bg-white shadow-sm' : 'text-gray-600'"
              class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
              </svg>
              Rule-Based
            </button>
            <button 
              @click="setMode('ml-enhanced')" 
              :class="activeMode === 'ml-enhanced' ? 'bg-white shadow-sm' : 'text-gray-600'"
              class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 flex items-center"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
              </svg>
              ML-Enhanced
            </button>
          </div>
          <button @click="refreshInsights" class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Mode Indicator -->
      <div class="mt-4 flex items-center">
        <span :class="modeBadgeClass" class="px-3 py-1 rounded-full text-sm font-medium inline-flex items-center">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="activeMode === 'rule-based'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
          {{ activeMode === 'rule-based' ? 'Using Rule-Based Analysis' : 'Using Machine Learning Predictions' }}
        </span>
        <span class="text-sm text-gray-500 ml-3">
          {{ activeMode === 'rule-based' ? 'Based on historical data and rules' : 'Enhanced with predictive analytics' }}
        </span>
      </div>
    </div>

    <!-- ML Explanation Banner -->
    <div v-if="activeMode === 'ml-enhanced'" class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 border border-purple-200 rounded-xl">
      <div class="flex items-start">
        <div class="p-2 bg-purple-100 rounded-lg mr-3">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="font-medium text-purple-800 mb-1">Machine Learning Insights</h3>
          <p class="text-sm text-purple-700">
            {{ mlExplanation }}
          </p>
        </div>
      </div>
    </div>

    <!-- Main Insights Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <!-- Most Requested Colors -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-semibold text-gray-800">Most Requested Colors</h2>
          <div class="p-2 bg-blue-50 rounded-lg">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
          </div>
        </div>
        
        <div class="space-y-4">
          <div v-for="color in mostRequestedColors" :key="color.id" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 rounded-lg border border-gray-300" :style="{ backgroundColor: color.hex }"></div>
            </div>
            <div class="ml-4 flex-1">
              <div class="flex justify-between items-center">
                <h3 class="font-medium text-gray-900">{{ color.name }}</h3>
                <span class="text-sm font-medium px-2 py-1 bg-blue-100 text-blue-800 rounded-full">
                  {{ color.percentage }}%
                </span>
              </div>
              <div class="mt-1 flex items-center">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                  <div class="bg-blue-600 h-2 rounded-full" :style="{ width: color.percentage + '%' }"></div>
                </div>
                <span class="text-sm text-gray-500 ml-3">{{ color.requests }} requests</span>
              </div>
              <p class="text-sm text-gray-600 mt-2">
                <span class="font-medium">Trend:</span> {{ color.trend }}
              </p>
            </div>
          </div>
        </div>
        
        <div class="mt-5 pt-5 border-t border-gray-200">
          <h4 class="text-sm font-medium text-gray-700 mb-2">Key Insights</h4>
          <ul class="text-sm text-gray-600 space-y-1">
            <li v-for="insight in colorInsights" :key="insight" class="flex items-start">
              <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              {{ insight }}
            </li>
          </ul>
        </div>
      </div>

      <!-- Trending Colors -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-semibold text-gray-800">Trending Colors</h2>
          <div class="flex items-center space-x-2">
            <button 
              @click="setTrendPeriod('weekly')" 
              :class="trendPeriod === 'weekly' ? 'bg-blue-100 text-blue-700' : 'text-gray-600'"
              class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
            >
              Weekly
            </button>
            <button 
              @click="setTrendPeriod('monthly')" 
              :class="trendPeriod === 'monthly' ? 'bg-blue-100 text-blue-700' : 'text-gray-600'"
              class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
            >
              Monthly
            </button>
          </div>
        </div>
        
        <div class="space-y-4">
          <div v-for="trend in trendingColors" :key="trend.id" class="p-3 bg-gradient-to-r from-gray-50 to-white rounded-lg border border-gray-200">
            <div class="flex items-center mb-3">
              <div class="w-12 h-12 rounded-lg border-2 border-white shadow-sm" :style="{ backgroundColor: trend.hex }"></div>
              <div class="ml-4">
                <h3 class="font-semibold text-gray-900">{{ trend.name }}</h3>
                <div class="flex items-center mt-1">
                  <span class="text-sm px-2 py-0.5 rounded-full" :class="trend.change >= 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    <svg v-if="trend.change >= 0" class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    <svg v-else class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                    {{ Math.abs(trend.change) }}% {{ trend.change >= 0 ? 'increase' : 'decrease' }}
                  </span>
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 text-sm">
              <div class="bg-white p-2 rounded border border-gray-200">
                <p class="text-gray-600">Current Rank</p>
                <p class="font-semibold text-lg text-gray-900">#{{ trend.rank }}</p>
              </div>
              <div class="bg-white p-2 rounded border border-gray-200">
                <p class="text-gray-600">Previous Rank</p>
                <p class="font-semibold text-lg text-gray-900">#{{ trend.previousRank }}</p>
              </div>
            </div>
            
            <div class="mt-3 pt-3 border-t border-gray-200">
              <p class="text-sm text-gray-600">
                <span class="font-medium">Demand Factor:</span> {{ trend.demandFactor }}
              </p>
              <p class="text-xs text-gray-500 mt-1">{{ trend.description }}</p>
            </div>
          </div>
        </div>
        
        <div class="mt-5 pt-5 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-medium text-gray-700">Seasonal Trends</h4>
            <span class="text-xs text-gray-500">{{ trendPeriod === 'weekly' ? 'Last 7 days' : 'Last 30 days' }}</span>
          </div>
          <div class="mt-2 flex items-center space-x-2">
            <span v-for="season in seasonalTrends" :key="season" class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">
              {{ season }}
            </span>
          </div>
        </div>
      </div>

      <!-- Recommended Restocks -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-5">
          <h2 class="text-lg font-semibold text-gray-800">Recommended Restocks</h2>
          <div class="p-2 bg-green-50 rounded-lg">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        
        <div class="space-y-4">
          <div v-for="recommendation in restockRecommendations" :key="recommendation.id" 
               class="p-3 rounded-lg border" :class="getRecommendationClass(recommendation.priority)">
            <div class="flex items-start">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-md" :style="{ backgroundColor: recommendation.hex }"></div>
              </div>
              <div class="ml-3 flex-1">
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="font-medium text-gray-900">{{ recommendation.productName }}</h3>
                    <p class="text-sm text-gray-600">{{ recommendation.brand }}</p>
                  </div>
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getPriorityClass(recommendation.priority)">
                    {{ recommendation.priority }}
                  </span>
                </div>
                
                <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                  <div>
                    <p class="text-gray-600">Current Stock</p>
                    <p class="font-semibold text-gray-900">{{ recommendation.currentStock }} units</p>
                  </div>
                  <div>
                    <p class="text-gray-600">Recommended</p>
                    <p class="font-semibold text-gray-900">{{ recommendation.recommendedOrder }} units</p>
                  </div>
                </div>
                
                <div class="mt-3">
                  <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Stock Level</span>
                    <span class="font-medium" :class="getStockLevelClass(recommendation.stockLevel)">
                      {{ recommendation.stockLevel }}
                    </span>
                  </div>
                  <div class="mt-1 bg-gray-200 rounded-full h-2">
                    <div class="h-2 rounded-full" :style="{ width: recommendation.stockPercentage + '%' }" 
                         :class="getStockBarClass(recommendation.stockPercentage)"></div>
                  </div>
                </div>
                
                <div class="mt-3 flex items-center justify-between">
                  <div class="text-sm">
                    <p class="text-gray-600">Lead Time</p>
                    <p class="font-medium text-gray-900">{{ recommendation.leadTime }} days</p>
                  </div>
                  <button @click="orderNow(recommendation)" class="px-3 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                    Order Now
                  </button>
                </div>
                
                <div v-if="activeMode === 'ml-enhanced'" class="mt-3 p-2 bg-blue-50 rounded border border-blue-200">
                  <p class="text-xs text-blue-700">
                    <span class="font-medium">ML Prediction:</span> {{ recommendation.mlPrediction }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-5 pt-5 border-t border-gray-200">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-sm font-medium text-gray-700">Restock Summary</h4>
            <span class="text-sm font-medium text-gray-900">₱{{ formatCurrency(totalRestockValue) }}</span>
          </div>
          <p class="text-sm text-gray-600">Total estimated value for all recommended restocks</p>
        </div>
      </div>
    </div>

    <!-- Additional Insights Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Advanced Analytics</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-gradient-to-br from-blue-50 to-white rounded-lg border border-blue-100">
          <div class="flex items-center mb-3">
            <div class="p-2 bg-blue-100 rounded-lg mr-3">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-600">Peak Demand Day</p>
              <p class="font-semibold text-gray-900">{{ analytics.peakDemandDay }}</p>
            </div>
          </div>
        </div>
        
        <div class="p-4 bg-gradient-to-br from-green-50 to-white rounded-lg border border-green-100">
          <div class="flex items-center mb-3">
            <div class="p-2 bg-green-100 rounded-lg mr-3">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-600">Growth Rate</p>
              <p class="font-semibold text-gray-900">{{ analytics.growthRate }}%</p>
            </div>
          </div>
        </div>
        
        <div class="p-4 bg-gradient-to-br from-yellow-50 to-white rounded-lg border border-yellow-100">
          <div class="flex items-center mb-3">
            <div class="p-2 bg-yellow-100 rounded-lg mr-3">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-600">Avg. Reorder Time</p>
              <p class="font-semibold text-gray-900">{{ analytics.avgReorderTime }} days</p>
            </div>
          </div>
        </div>
        
        <div class="p-4 bg-gradient-to-br from-purple-50 to-white rounded-lg border border-purple-100">
          <div class="flex items-center mb-3">
            <div class="p-2 bg-purple-100 rounded-lg mr-3">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <p class="text-sm text-gray-600">Accuracy Score</p>
              <p class="font-semibold text-gray-900">{{ analytics.accuracyScore }}%</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- ML Model Info (when in ML mode) -->
      <div v-if="activeMode === 'ml-enhanced'" class="mt-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex items-start">
          <div class="p-2 bg-gray-200 rounded-lg mr-3">
            <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
            </svg>
          </div>
          <div class="flex-1">
            <h4 class="font-medium text-gray-800 mb-2">Machine Learning Model Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div>
                <p class="text-gray-600">Model Type</p>
                <p class="font-medium text-gray-900">Random Forest Regression</p>
              </div>
              <div>
                <p class="text-gray-600">Training Data</p>
                <p class="font-medium text-gray-900">6 months of sales data</p>
              </div>
              <div>
                <p class="text-gray-600">Features Used</p>
                <p class="font-medium text-gray-900">Seasonality, trends, inventory</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="text-sm text-gray-600">
        Last updated: {{ lastUpdated }}
      </div>
      <div class="flex space-x-3">
        <button @click="exportInsights" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Export Report
        </button>
        <button @click="generateReport" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Generate Detailed Report
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ColorDemandInsights',
  data() {
    return {
      activeMode: 'rule-based',
      trendPeriod: 'weekly',
      lastUpdated: new Date().toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }),
      mostRequestedColors: [
        {
          id: 1,
          name: 'Ocean Blue',
          hex: '#1E40AF',
          requests: 142,
          percentage: 32,
          trend: 'Consistently high demand'
        },
        {
          id: 2,
          name: 'Premium White',
          hex: '#FFFFFF',
          requests: 118,
          percentage: 26,
          trend: 'Stable, year-round favorite'
        },
        {
          id: 3,
          name: 'Forest Green',
          hex: '#059669',
          requests: 89,
          percentage: 20,
          trend: 'Growing popularity'
        },
        {
          id: 4,
          name: 'Sunset Yellow',
          hex: '#F59E0B',
          requests: 65,
          percentage: 15,
          trend: 'Seasonal peak in summer'
        },
        {
          id: 5,
          name: 'Charcoal Gray',
          hex: '#4B5563',
          requests: 35,
          percentage: 8,
          trend: 'Modern interiors preference'
        }
      ],
      trendingColors: [
        {
          id: 1,
          name: 'Sage Green',
          hex: '#84CC16',
          change: 28,
          rank: 3,
          previousRank: 8,
          demandFactor: 'High in residential projects',
          description: 'Growing trend in modern home designs'
        },
        {
          id: 2,
          name: 'Terracotta',
          hex: '#B45309',
          change: 22,
          rank: 5,
          previousRank: 12,
          demandFactor: 'Warm color preference',
          description: 'Popular in accent walls and furniture'
        },
        {
          id: 3,
          name: 'Lavender Mist',
          hex: '#8B5CF6',
          change: 15,
          rank: 7,
          previousRank: 15,
          demandFactor: 'Bedroom and wellness spaces',
          description: 'Calming color for relaxation areas'
        }
      ],
      restockRecommendations: [
        {
          id: 1,
          productName: 'Ocean Blue Interior',
          brand: 'Davies',
          hex: '#1E40AF',
          currentStock: 15,
          recommendedOrder: 85,
          priority: 'High',
          stockLevel: 'Critical',
          stockPercentage: 15,
          leadTime: 5,
          mlPrediction: 'Expected 45% demand increase in next 2 weeks'
        },
        {
          id: 2,
          productName: 'Sage Green Matte',
          brand: 'Boysen',
          hex: '#84CC16',
          currentStock: 28,
          recommendedOrder: 60,
          priority: 'Medium',
          stockLevel: 'Low',
          stockPercentage: 32,
          leadTime: 3,
          mlPrediction: 'Trending color with 28% weekly growth'
        },
        {
          id: 3,
          productName: 'Premium White Paint',
          brand: 'Boysen',
          hex: '#FFFFFF',
          currentStock: 45,
          recommendedOrder: 105,
          priority: 'Medium',
          stockLevel: 'Adequate',
          stockPercentage: 30,
          leadTime: 2,
          mlPrediction: 'Stable demand, reorder for safety stock'
        },
        {
          id: 4,
          productName: 'Terracotta Gloss',
          brand: 'Columbia',
          hex: '#B45309',
          currentStock: 8,
          recommendedOrder: 50,
          priority: 'High',
          stockLevel: 'Critical',
          stockPercentage: 14,
          leadTime: 7,
          mlPrediction: 'Rapidly growing trend, order immediately'
        }
      ],
      colorInsights: [
        'Blue tones dominate 32% of all requests',
        'Green colors show strongest growth trend',
        'Neutrals remain consistent year-round',
        'Bright colors peak during summer months'
      ],
      seasonalTrends: ['Summer Brights', 'Earth Tones', 'Cool Neutrals', 'Pastel Shades'],
      analytics: {
        peakDemandDay: 'Friday',
        growthRate: '18.5',
        avgReorderTime: '4.2',
        accuracyScore: '92.3'
      }
    }
  },
  computed: {
    modeBadgeClass() {
      return this.activeMode === 'rule-based' 
        ? 'bg-blue-100 text-blue-800' 
        : 'bg-purple-100 text-purple-800'
    },
    mlExplanation() {
      return this.activeMode === 'ml-enhanced'
        ? 'Using machine learning algorithms to predict color trends and optimize inventory. Model trained on historical sales data, seasonality patterns, and market trends.'
        : 'Using rule-based analysis from historical data and predefined business rules.'
    },
    totalRestockValue() {
      return this.restockRecommendations.reduce((total, rec) => {
        const averagePrice = 500 // Average price per unit
        return total + (rec.recommendedOrder * averagePrice)
      }, 0)
    }
  },
  methods: {
    setMode(mode) {
      this.activeMode = mode
      if (mode === 'ml-enhanced') {
        // Simulate ML-enhanced data
        this.enhanceWithML()
      } else {
        // Reset to rule-based data
        this.resetToRuleBased()
      }
    },
    setTrendPeriod(period) {
      this.trendPeriod = period
      // In a real app, this would fetch data for the selected period
      console.log(`Switched to ${period} trend analysis`)
    },
    getPriorityClass(priority) {
      const classes = {
        'High': 'bg-red-100 text-red-800',
        'Medium': 'bg-yellow-100 text-yellow-800',
        'Low': 'bg-green-100 text-green-800'
      }
      return classes[priority] || 'bg-gray-100 text-gray-800'
    },
    getStockLevelClass(level) {
      const classes = {
        'Critical': 'text-red-600',
        'Low': 'text-yellow-600',
        'Adequate': 'text-green-600',
        'Optimal': 'text-blue-600'
      }
      return classes[level] || 'text-gray-600'
    },
    getStockBarClass(percentage) {
      if (percentage <= 15) return 'bg-red-500'
      if (percentage <= 40) return 'bg-yellow-500'
      return 'bg-green-500'
    },
    getRecommendationClass(priority) {
      const classes = {
        'High': 'bg-red-50 border-red-200',
        'Medium': 'bg-yellow-50 border-yellow-200',
        'Low': 'bg-green-50 border-green-200'
      }
      return classes[priority] || 'bg-gray-50 border-gray-200'
    },
    formatCurrency(amount) {
      return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(amount).replace('PHP', '₱')
    },
    refreshInsights() {
      // Simulate data refresh
      this.lastUpdated = new Date().toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
      
      // Show loading state
      const loadingEvent = new Event('loading-start')
      window.dispatchEvent(loadingEvent)
      
      // Simulate API call
      setTimeout(() => {
        const loadedEvent = new Event('loading-complete')
        window.dispatchEvent(loadedEvent)
        alert('Insights refreshed successfully!')
      }, 1000)
    },
    orderNow(recommendation) {
      alert(`Order initiated for ${recommendation.recommendedOrder} units of ${recommendation.productName}`)
      // In a real app, this would trigger an order workflow
    },
    exportInsights() {
      const data = {
        mode: this.activeMode,
        period: this.trendPeriod,
        mostRequestedColors: this.mostRequestedColors,
        trendingColors: this.trendingColors,
        restockRecommendations: this.restockRecommendations,
        generatedAt: new Date().toISOString()
      }
      console.log('Exporting insights:', data)
      alert('Insights report exported (check console for data structure)')
    },
    generateReport() {
      alert('Generating detailed analytics report...')
      // In a real app, this would generate a PDF or detailed report
    },
    enhanceWithML() {
      // Simulate ML-enhanced data
      this.mostRequestedColors = this.mostRequestedColors.map(color => ({
        ...color,
        mlConfidence: Math.floor(Math.random() * 20) + 80 + '% confidence'
      }))
      
      this.trendingColors = this.trendingColors.map(trend => ({
        ...trend,
        mlPrediction: `Predicted ${Math.floor(Math.random() * 20) + 10}% growth next month`
      }))
      
      this.analytics.accuracyScore = '94.7'
    },
    resetToRuleBased() {
      // Reset to original rule-based data
      this.mostRequestedColors = this.mostRequestedColors.map(({ mlConfidence, ...rest }) => rest)
      this.trendingColors = this.trendingColors.map(({ mlPrediction, ...rest }) => rest)
      this.analytics.accuracyScore = '88.2'
    }
  }
}
</script>

<style scoped>
/* Enhanced scrollbar */
.overflow-x-auto {
  -webkit-overflow-scrolling: touch;
}

/* Custom animations */
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

.fade-in {
  animation: fadeIn 0.5s ease-out;
}

/* Gradient border effect */
.gradient-border {
  position: relative;
  border: double 2px transparent;
  background-image: linear-gradient(white, white), 
                    linear-gradient(90deg, #3b82f6, #8b5cf6);
  background-origin: border-box;
  background-clip: content-box, border-box;
}

/* Glowing effect for important items */
.glow {
  box-shadow: 0 0 15px rgba(59, 130, 246, 0.1);
}

.glow:hover {
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .grid-cols-1 {
    grid-template-columns: 1fr;
  }
  
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .grid-cols-4 {
    grid-template-columns: 1fr;
  }
  
  .flex-col {
    flex-direction: column;
    align-items: stretch;
  }
  
  .space-x-3 > * + * {
    margin-left: 0;
    margin-top: 0.75rem;
  }
}

/* Custom scrollbar for better mobile experience */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

/* Print optimizations */
@media print {
  .no-print {
    display: none !important;
  }
  
  .bg-gradient-to-r,
  .bg-gradient-to-br {
    background: white !important;
    border: 1px solid #e5e7eb !important;
  }
  
  button {
    display: none !important;
  }
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Card hover effects */
.bg-white {
  transition: all 0.2s ease-in-out;
}

.bg-white:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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
</style>