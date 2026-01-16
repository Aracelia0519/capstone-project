<!-- ECommerceReviews.vue -->
<template>
  <div class="ecommerce-reviews p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Reviews & Ratings</h1>
          <p class="text-gray-300">Collect and manage customer feedback</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
          <div class="flex items-center bg-gray-800/50 px-4 py-2 rounded-lg">
            <svg class="w-5 h-5 text-amber-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span class="text-white font-medium">4.8</span>
            <span class="text-gray-400 text-sm ml-1">/ 5.0</span>
          </div>
          <button @click="showReviewAnalysis = true" 
                  class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:opacity-90 transition-opacity text-sm">
            Analytics
          </button>
        </div>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ totalReviews }}</div>
        <div class="text-sm text-gray-300">Total Reviews</div>
      </div>
      <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ averageRating.toFixed(1) }}</div>
        <div class="text-sm text-gray-300">Average Rating</div>
      </div>
      <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ pendingReviews }}</div>
        <div class="text-sm text-gray-300">Pending Review</div>
      </div>
      <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-2xl p-4 border border-gray-800">
        <div class="text-2xl font-bold text-white mb-1">{{ productCount }}</div>
        <div class="text-sm text-gray-300">Products Reviewed</div>
      </div>
    </div>

    <!-- Rating Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <div class="lg:col-span-2 bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Rating Distribution</h3>
        <div class="space-y-4">
          <div v-for="rating in ratingDistribution" :key="rating.stars" class="flex items-center">
            <div class="flex items-center w-16">
              <span class="text-gray-300 text-sm mr-2">{{ rating.stars }}★</span>
              <div class="flex">
                <svg v-for="star in 5" :key="star" 
                     class="w-4 h-4" 
                     :class="star <= rating.stars ? 'text-amber-400' : 'text-gray-600'"
                     fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
            </div>
            <div class="flex-1 mx-4">
              <div class="w-full bg-gray-700 h-2 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-amber-400 to-yellow-300 rounded-full" 
                     :style="{ width: rating.percentage + '%' }"></div>
              </div>
            </div>
            <span class="text-gray-300 text-sm w-12">{{ rating.count }} reviews</span>
          </div>
        </div>
      </div>
      
      <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6">
        <h3 class="text-lg font-semibold text-white mb-6">Quick Actions</h3>
        <div class="space-y-4">
          <button @click="filterByRating(5)" 
                 class="w-full p-3 bg-amber-500/10 border border-amber-500/20 rounded-lg hover:bg-amber-500/20 transition-colors flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex mr-3">
                <svg v-for="star in 5" :key="star" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <span class="text-white">5-star Reviews</span>
            </div>
            <span class="text-amber-400">{{ ratingDistribution[4].count }}</span>
          </button>
          <button @click="showPendingOnly = !showPendingOnly" 
                 class="w-full p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg hover:bg-blue-500/20 transition-colors flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-white">Pending Moderation</span>
            </div>
            <span class="text-blue-400">{{ pendingReviews }}</span>
          </button>
          <button @click="exportReviews" 
                 class="w-full p-3 bg-green-500/10 border border-green-500/20 rounded-lg hover:bg-green-500/20 transition-colors flex items-center justify-between">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <span class="text-white">Export Reviews</span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-800">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div class="md:col-span-2">
          <label class="block text-sm text-gray-300 mb-2">Search</label>
          <input type="text" v-model="searchQuery" placeholder="Search by product, client, or review..." 
                 class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500">
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Rating</label>
          <select v-model="selectedRating" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="0">All Ratings</option>
            <option value="5">5 Stars</option>
            <option value="4">4 Stars</option>
            <option value="3">3 Stars</option>
            <option value="2">2 Stars</option>
            <option value="1">1 Star</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-300 mb-2">Status</label>
          <select v-model="selectedStatus" class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white">
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="pending">Pending</option>
            <option value="hidden">Hidden</option>
          </select>
        </div>
        <div class="flex items-end">
          <button @click="resetFilters" class="w-full px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg text-white transition-colors">
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Reviews List -->
    <div class="space-y-6">
      <div v-for="review in filteredReviews" :key="review.id" 
           class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-6 hover:border-gray-700 transition-colors">
        <div class="flex flex-col md:flex-row md:items-start justify-between mb-4">
          <div class="flex items-start mb-4 md:mb-0">
            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-lg mr-4">
              {{ review.client.charAt(0) }}
            </div>
            <div>
              <div class="flex items-center mb-2">
                <h4 class="text-white font-medium mr-3">{{ review.client }}</h4>
                <div class="flex">
                  <svg v-for="star in 5" :key="star" 
                       class="w-4 h-4" 
                       :class="star <= review.rating ? 'text-amber-400' : 'text-gray-600'"
                       fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
              </div>
              <div class="text-sm text-gray-400">
                Reviewed {{ review.product }} • {{ review.date }}
              </div>
            </div>
          </div>
          
          <div class="flex items-center space-x-2">
            <span :class="[
              'px-3 py-1 rounded-full text-xs font-medium',
              review.status === 'published' ? 'bg-green-500/20 text-green-300' :
              review.status === 'pending' ? 'bg-amber-500/20 text-amber-300' :
              'bg-red-500/20 text-red-300'
            ]">
              {{ review.status === 'published' ? 'Published' : 
                 review.status === 'pending' ? 'Pending' : 'Hidden' }}
            </span>
            <div class="relative">
              <button @click="toggleReviewActions(review.id)" 
                      class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                </svg>
              </button>
              <div v-if="activeReviewActions === review.id" class="absolute right-0 mt-2 w-48 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-10">
                <button v-if="review.status === 'pending'" @click="approveReview(review)" 
                       class="w-full text-left px-4 py-3 text-green-400 hover:bg-gray-700 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Approve
                </button>
                <button v-if="review.status === 'published'" @click="hideReview(review)" 
                       class="w-full text-left px-4 py-3 text-amber-400 hover:bg-gray-700 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                  </svg>
                  Hide
                </button>
                <button v-if="review.status === 'hidden'" @click="approveReview(review)" 
                       class="w-full text-left px-4 py-3 text-blue-400 hover:bg-gray-700 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Show
                </button>
                <button @click="deleteReview(review)" 
                       class="w-full text-left px-4 py-3 text-red-400 hover:bg-red-500/10 flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mb-4">
          <h5 class="text-white font-medium mb-2">{{ review.title }}</h5>
          <p class="text-gray-300">{{ review.comment }}</p>
        </div>
        
        <div v-if="review.response" class="mt-4 pl-4 border-l-2 border-blue-500/50">
          <div class="flex items-center mb-2">
            <svg class="w-4 h-4 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            <span class="text-sm text-blue-400">Store Response</span>
          </div>
          <p class="text-gray-300 text-sm">{{ review.response }}</p>
          <div class="text-xs text-gray-400 mt-1">{{ review.responseDate }}</div>
        </div>
        
        <div v-else class="mt-4">
          <button @click="showResponseForm(review)" 
                 class="text-sm text-blue-400 hover:text-blue-300 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            Respond to Review
          </button>
        </div>
        
        <div class="mt-6 pt-6 border-t border-gray-800 flex justify-between items-center">
          <div class="text-sm text-gray-400">
            Order: {{ review.orderId }} • Verified Purchase
          </div>
          <div class="flex items-center space-x-4">
            <button @click="likeReview(review)" 
                   class="flex items-center text-sm text-gray-400 hover:text-white">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905a3.61 3.61 0 01-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
              </svg>
              {{ review.likes }} Helpful
            </button>
            <button @click="reportReview(review)" 
                   class="flex items-center text-sm text-gray-400 hover:text-red-400">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
              </svg>
              Report
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Response Modal -->
    <div v-if="respondingToReview" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-md">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Respond to Review</h3>
            <button @click="respondingToReview = null" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div class="mb-6">
            <div class="flex items-center mb-4">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-lg mr-3">
                {{ respondingToReview.client.charAt(0) }}
              </div>
              <div>
                <div class="text-white">{{ respondingToReview.client }}</div>
                <div class="flex">
                  <svg v-for="star in 5" :key="star" 
                       class="w-4 h-4" 
                       :class="star <= respondingToReview.rating ? 'text-amber-400' : 'text-gray-600'"
                       fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
              </div>
            </div>
            <p class="text-gray-300">{{ respondingToReview.comment }}</p>
          </div>
          
          <form @submit.prevent="submitResponse">
            <div class="mb-6">
              <label class="block text-sm text-gray-300 mb-2">Your Response</label>
              <textarea v-model="responseForm.text" rows="4" required 
                       class="w-full px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white"
                       placeholder="Type your response here..."></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="respondingToReview = null" 
                     class="px-6 py-2 border border-gray-700 text-gray-300 rounded-lg hover:bg-gray-800 transition-colors">
                Cancel
              </button>
              <button type="submit" 
                     class="px-6 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white rounded-lg hover:opacity-90 transition-opacity">
                Send Response
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Analytics Modal -->
    <div v-if="showReviewAnalysis" class="fixed inset-0 bg-black/70 flex items-center justify-center p-4 z-50">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-4xl">
        <div class="p-6 border-b border-gray-800">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-bold text-white">Review Analytics</h3>
            <button @click="showReviewAnalysis = false" class="text-gray-400 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
        
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Rating Trends</h4>
              <div class="h-40 flex items-end justify-between space-x-2">
                <div v-for="month in ratingTrends" :key="month.name" class="flex flex-col items-center">
                  <div class="text-xs text-gray-400 mb-2">{{ month.name }}</div>
                  <div class="w-6 bg-gradient-to-t from-amber-500 to-yellow-300 rounded-t-lg"
                       :style="{ height: month.average * 16 + 'px' }"
                       :title="month.average + ' stars'"></div>
                </div>
              </div>
            </div>
            
            <div class="bg-gray-800/50 rounded-xl p-4">
              <h4 class="text-sm text-gray-300 mb-3">Top Reviewed Products</h4>
              <div class="space-y-3">
                <div v-for="product in topProducts" :key="product.name" 
                     class="flex items-center justify-between">
                  <span class="text-gray-300">{{ product.name }}</span>
                  <div class="flex items-center">
                    <div class="flex mr-2">
                      <svg v-for="star in 5" :key="star" 
                           class="w-3 h-3" 
                           :class="star <= product.rating ? 'text-amber-400' : 'text-gray-600'"
                           fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.54-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                    </div>
                    <span class="text-white text-sm">{{ product.reviews }} reviews</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="bg-gray-800/50 rounded-xl p-4">
            <h4 class="text-sm text-gray-300 mb-3">Review Sentiment Analysis</h4>
            <div class="space-y-4">
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-300">Positive</span>
                  <span class="text-green-400">65%</span>
                </div>
                <div class="w-full bg-gray-700 h-2 rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-green-500 to-emerald-500 rounded-full" style="width: 65%"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-300">Neutral</span>
                  <span class="text-blue-400">25%</span>
                </div>
                <div class="w-full bg-gray-700 h-2 rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full" style="width: 25%"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-300">Negative</span>
                  <span class="text-red-400">10%</span>
                </div>
                <div class="w-full bg-gray-700 h-2 rounded-full overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-red-500 to-pink-500 rounded-full" style="width: 10%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const searchQuery = ref('')
const selectedRating = ref('0')
const selectedStatus = ref('')
const showPendingOnly = ref(false)
const activeReviewActions = ref(null)
const respondingToReview = ref(null)
const showReviewAnalysis = ref(false)

const reviews = ref([
  { 
    id: 1, 
    client: 'Juan Dela Cruz',
    rating: 5,
    title: 'Excellent Quality Paint',
    comment: 'The paint quality exceeded my expectations. Coverage was excellent and dried evenly. Highly recommended!',
    product: 'Premium White Interior Paint',
    orderId: 'ORD-2024-001',
    date: '2024-03-15',
    status: 'published',
    likes: 12,
    response: 'Thank you for your wonderful feedback! We\'re glad you loved our premium paint.',
    responseDate: '2024-03-16'
  },
  { 
    id: 2, 
    client: 'Maria Santos',
    rating: 4,
    title: 'Good but could be better',
    comment: 'Good paint overall, but the drying time was longer than expected. Color match was perfect though.',
    product: 'Weatherproof Exterior Paint',
    orderId: 'ORD-2024-002',
    date: '2024-03-14',
    status: 'published',
    likes: 5,
    response: null
  },
  { 
    id: 3, 
    client: 'Pedro Reyes',
    rating: 1,
    title: 'Arrived damaged',
    comment: 'Paint can arrived dented and leaking. Very disappointed with the packaging.',
    product: 'Gloss Finish Coating',
    orderId: 'ORD-2024-003',
    date: '2024-03-13',
    status: 'pending',
    likes: 0,
    response: null
  },
  { 
    id: 4, 
    client: 'Ana Lopez',
    rating: 5,
    title: 'Perfect for my project',
    comment: 'Used this for my living room renovation and the results were stunning. Easy to apply and great coverage.',
    product: 'Eco-Friendly Interior Paint',
    orderId: 'ORD-2024-004',
    date: '2024-03-12',
    status: 'published',
    likes: 8,
    response: 'We appreciate your positive review! Eco-friendly products are our specialty.',
    responseDate: '2024-03-13'
  },
  { 
    id: 5, 
    client: 'Luis Garcia',
    rating: 2,
    title: 'Color not as shown',
    comment: 'The color on the website is much brighter than what I received. Returning this item.',
    product: 'Anti-Mold Exterior Paint',
    orderId: 'ORD-2024-005',
    date: '2024-03-11',
    status: 'hidden',
    likes: 3,
    response: null
  }
])

const responseForm = ref({
  text: ''
})

const ratingDistribution = computed(() => {
  const distribution = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 }
  
  reviews.value.forEach(review => {
    if (review.status === 'published') {
      distribution[review.rating]++
    }
  })
  
  const total = Object.values(distribution).reduce((a, b) => a + b, 0)
  
  return [5, 4, 3, 2, 1].map(stars => ({
    stars,
    count: distribution[stars],
    percentage: total > 0 ? Math.round((distribution[stars] / total) * 100) : 0
  }))
})

const ratingTrends = ref([
  { name: 'Jan', average: 4.2 },
  { name: 'Feb', average: 4.5 },
  { name: 'Mar', average: 4.8 },
  { name: 'Apr', average: 4.3 },
  { name: 'May', average: 4.7 },
  { name: 'Jun', average: 4.9 }
])

const topProducts = ref([
  { name: 'Premium White Paint', rating: 4.8, reviews: 42 },
  { name: 'Weatherproof Exterior', rating: 4.5, reviews: 28 },
  { name: 'Eco-Friendly Paint', rating: 4.9, reviews: 35 },
  { name: 'Gloss Finish Coating', rating: 4.2, reviews: 18 },
  { name: 'Anti-Mold Exterior', rating: 4.6, reviews: 24 }
])

const totalReviews = computed(() => reviews.value.length)

const averageRating = computed(() => {
  const published = reviews.value.filter(r => r.status === 'published')
  if (published.length === 0) return 0
  return published.reduce((sum, r) => sum + r.rating, 0) / published.length
})

const pendingReviews = computed(() => {
  return reviews.value.filter(r => r.status === 'pending').length
})

const productCount = computed(() => {
  const products = new Set(reviews.value.map(r => r.product))
  return products.size
})

const filteredReviews = computed(() => {
  return reviews.value.filter(review => {
    const matchesSearch = searchQuery.value === '' || 
      review.client.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      review.product.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      review.comment.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesRating = selectedRating.value === '0' || 
      review.rating === parseInt(selectedRating.value)
    
    const matchesStatus = selectedStatus.value === '' || 
      review.status === selectedStatus.value
    
    const matchesPending = !showPendingOnly.value || review.status === 'pending'
    
    return matchesSearch && matchesRating && matchesStatus && matchesPending
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedRating.value = '0'
  selectedStatus.value = ''
  showPendingOnly.value = false
}

const toggleReviewActions = (id) => {
  activeReviewActions.value = activeReviewActions.value === id ? null : id
}

const approveReview = (review) => {
  review.status = 'published'
  activeReviewActions.value = null
}

const hideReview = (review) => {
  review.status = 'hidden'
  activeReviewActions.value = null
}

const deleteReview = (review) => {
  if (confirm('Are you sure you want to delete this review?')) {
    reviews.value = reviews.value.filter(r => r.id !== review.id)
  }
  activeReviewActions.value = null
}

const showResponseForm = (review) => {
  respondingToReview.value = review
}

const submitResponse = () => {
  if (respondingToReview.value) {
    respondingToReview.value.response = responseForm.value.text
    respondingToReview.value.responseDate = new Date().toISOString().split('T')[0]
    respondingToReview.value = null
    responseForm.value.text = ''
  }
}

const likeReview = (review) => {
  review.likes++
}

const reportReview = (review) => {
  alert(`Reported review from ${review.client}`)
}

const filterByRating = (rating) => {
  selectedRating.value = rating.toString()
}

const exportReviews = () => {
  alert('Exporting reviews to CSV...')
}

// Close actions menu when clicking outside
document.addEventListener('click', (e) => {
  if (!e.target.closest('.relative')) {
    activeReviewActions.value = null
  }
})
</script>

<style scoped>
.ecommerce-reviews {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-reviews {
    padding: 1rem;
  }
}
</style>