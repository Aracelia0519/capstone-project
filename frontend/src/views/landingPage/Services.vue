<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-100">
    <!-- Hero Section -->
    <div class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-blue-900/40 via-purple-900/40 to-pink-900/40"></div>
      <div class="container mx-auto px-4 py-16 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-4xl md:text-6xl font-bold mb-6 bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
            Premium Painting Services
          </h1>
          <p class="text-xl text-gray-300 mb-10 max-w-3xl mx-auto">
            Transform your spaces with our professional painting solutions. From interiors to exteriors, we bring colors to life.
          </p>
          <div class="flex flex-wrap justify-center gap-4">
            <button @click="scrollToServices" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl font-semibold hover:shadow-2xl hover:shadow-blue-500/30 transition-all duration-300 hover:scale-105 flex items-center space-x-3 group">
              <svg class="w-6 h-6 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
              <span>Explore Our Services</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Services Grid -->
    <div class="container mx-auto px-4 py-16" id="services">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Interior Painting -->
        <div class="group relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl border border-gray-700 hover:border-blue-500/50 p-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/20 overflow-hidden">
          <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-blue-500/10 to-purple-500/10 blur-xl"></div>
          <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
              <div class="relative overflow-hidden rounded-2xl w-full md:w-48 h-48">
                <img 
                  src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                  alt="Interior Painting - Living room with fresh blue paint"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-2xl font-bold text-white mb-2">Interior Painting</h3>
                  <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-full text-sm">Residential</span>
                    <span class="px-3 py-1 bg-purple-500/20 text-purple-300 rounded-full text-sm">Commercial</span>
                  </div>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500/20 to-purple-500/20 flex items-center justify-center">
                  <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-300 mb-6">Transform your indoor spaces with our expert interior painting services using premium paints and techniques.</p>
              <div class="space-y-3 mb-8">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Wall painting & color consultation</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Ceiling, trim, and detailed work</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-blue-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Surface preparation & priming</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-2xl font-bold text-white">From ₱3,500</span>
                  <span class="text-gray-400 text-sm ml-2">per room</span>
                </div>
                <button @click="showServiceDetail('interior')" class="px-5 py-2.5 bg-gradient-to-r from-blue-500/20 to-purple-500/20 hover:from-blue-500/30 hover:to-purple-500/30 rounded-xl font-medium transition-all duration-300 border border-blue-500/30 hover:border-blue-500/50">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Exterior Painting -->
        <div class="group relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl border border-gray-700 hover:border-orange-500/50 p-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-orange-500/20 overflow-hidden">
          <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-orange-500/10 to-red-500/10 blur-xl"></div>
          <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
              <div class="relative overflow-hidden rounded-2xl w-full md:w-48 h-48">
                <img 
                  src="https://images.unsplash.com/photo-1598228723793-52759bba239c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                  alt="Exterior Painting - House with fresh white paint"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-2xl font-bold text-white mb-2">Exterior Painting</h3>
                  <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-orange-500/20 text-orange-300 rounded-full text-sm">Weatherproof</span>
                    <span class="px-3 py-1 bg-red-500/20 text-red-300 rounded-full text-sm">Durable</span>
                  </div>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500/20 to-red-500/20 flex items-center justify-center">
                  <svg class="w-7 h-7 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-300 mb-6">Protect and beautify your property's exterior with our durable, weather-resistant painting solutions.</p>
              <div class="space-y-3 mb-8">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-orange-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Weather-resistant coatings</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-orange-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Surface preparation & repair</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-orange-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Pressure washing & cleaning</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-2xl font-bold text-white">From ₱15,000</span>
                  <span class="text-gray-400 text-sm ml-2">per project</span>
                </div>
                <button @click="showServiceDetail('exterior')" class="px-5 py-2.5 bg-gradient-to-r from-orange-500/20 to-red-500/20 hover:from-orange-500/30 hover:to-red-500/30 rounded-xl font-medium transition-all duration-300 border border-orange-500/30 hover:border-orange-500/50">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Consultation -->
        <div class="group relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl border border-gray-700 hover:border-purple-500/50 p-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-purple-500/20 overflow-hidden">
          <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-purple-500/10 to-pink-500/10 blur-xl"></div>
          <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
              <div class="relative overflow-hidden rounded-2xl w-full md:w-48 h-48">
                <img 
                  src="https://images.unsplash.com/photo-1600585154526-990dced4db0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                  alt="Color Consultation - Paint color samples and swatches"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-2xl font-bold text-white mb-2">Color Consultation</h3>
                  <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-purple-500/20 text-purple-300 rounded-full text-sm">Virtual</span>
                    <span class="px-3 py-1 bg-pink-500/20 text-pink-300 rounded-full text-sm">In-Person</span>
                  </div>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500/20 to-pink-500/20 flex items-center justify-center">
                  <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-300 mb-6">Get expert advice on color selection, paint types, and design concepts tailored to your space and style.</p>
              <div class="space-y-3 mb-8">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-purple-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Virtual color visualization</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-purple-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Material & finish selection</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-purple-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Project planning & budgeting</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-2xl font-bold text-white">From ₱1,500</span>
                  <span class="text-gray-400 text-sm ml-2">per session</span>
                </div>
                <button @click="showServiceDetail('consultation')" class="px-5 py-2.5 bg-gradient-to-r from-purple-500/20 to-pink-500/20 hover:from-purple-500/30 hover:to-pink-500/30 rounded-xl font-medium transition-all duration-300 border border-purple-500/30 hover:border-purple-500/50">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Maintenance Services -->
        <div class="group relative bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl border border-gray-700 hover:border-green-500/50 p-8 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-green-500/20 overflow-hidden">
          <div class="absolute -top-6 -right-6 w-24 h-24 rounded-full bg-gradient-to-br from-green-500/10 to-emerald-500/10 blur-xl"></div>
          <div class="flex flex-col md:flex-row gap-6">
            <div class="flex-shrink-0">
              <div class="relative overflow-hidden rounded-2xl w-full md:w-48 h-48">
                <img 
                  src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                  alt="Maintenance Services - Painter touching up wall paint"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </div>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h3 class="text-2xl font-bold text-white mb-2">Maintenance Services</h3>
                  <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-green-500/20 text-green-300 rounded-full text-sm">Regular</span>
                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-300 rounded-full text-sm">Emergency</span>
                  </div>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500/20 to-emerald-500/20 flex items-center justify-center">
                  <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  </svg>
                </div>
              </div>
              <p class="text-gray-300 mb-6">Keep your paint looking fresh with our regular upkeep services including touch-ups and protective coatings.</p>
              <div class="space-y-3 mb-8">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Regular touch-ups & repairs</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Protective coating application</span>
                </div>
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-400 rounded-full mr-3"></div>
                  <span class="text-gray-300">Scheduled maintenance plans</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <span class="text-2xl font-bold text-white">From ₱2,500</span>
                  <span class="text-gray-400 text-sm ml-2">per visit</span>
                </div>
                <button @click="showServiceDetail('maintenance')" class="px-5 py-2.5 bg-gradient-to-r from-green-500/20 to-emerald-500/20 hover:from-green-500/30 hover:to-emerald-500/30 rounded-xl font-medium transition-all duration-300 border border-green-500/30 hover:border-green-500/50">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="container mx-auto px-4 py-16">
      <div class="max-w-6xl mx-auto">
        <div class="relative rounded-3xl overflow-hidden">
          <img 
            src="https://images.unsplash.com/photo-1589939705384-5185137a7f0f?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80" 
            alt="Painting Professionals - Team of painters working together"
            class="w-full h-64 md:h-80 object-cover"
          />
          <div class="absolute inset-0 bg-gradient-to-r from-blue-900/80 via-purple-900/80 to-pink-900/80"></div>
          <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-8">
            <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">Find Certified Painting Professionals</h2>
            <p class="text-xl text-gray-200 mb-8 max-w-2xl">
              Connect with trusted painting experts in your area. Get personalized quotes and schedule services that match your vision.
            </p>
            <p class="text-sm text-gray-300 mb-8 max-w-3xl">
              "The Services section informs users of the painting services offered through the platform while providing a seamless way to connect with professionals."
            </p>
            <button 
              @click="handleFindProvider" 
              :disabled="!isLoggedIn"
              :class="[
                'px-10 py-4 rounded-2xl font-bold text-lg transition-all duration-300 flex items-center space-x-4',
                isLoggedIn 
                  ? 'bg-gradient-to-r from-blue-500 to-purple-500 hover:shadow-2xl hover:shadow-purple-500/40 hover:scale-105 cursor-pointer'
                  : 'bg-gradient-to-r from-gray-700 to-gray-800 cursor-not-allowed opacity-80'
              ]"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <span>{{ isLoggedIn ? 'Find a Service Provider' : 'Login Required' }}</span>
              <svg v-if="isLoggedIn" class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
              </svg>
            </button>
            <p v-if="!isLoggedIn" class="text-sm text-gray-300 mt-4">
              Please login to access our certified provider network
            </p>
          </div>
        </div>

        <!-- Stats Section -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-12">
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-700">
            <div class="text-3xl font-bold text-white mb-2">500+</div>
            <div class="text-gray-300">Projects Completed</div>
          </div>
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-700">
            <div class="text-3xl font-bold text-white mb-2">98%</div>
            <div class="text-gray-300">Client Satisfaction</div>
          </div>
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-700">
            <div class="text-3xl font-bold text-white mb-2">50+</div>
            <div class="text-gray-300">Certified Providers</div>
          </div>
          <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-700">
            <div class="text-3xl font-bold text-white mb-2">24/7</div>
            <div class="text-gray-300">Customer Support</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Service Detail Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 animate-fadeIn">
      <div class="bg-gradient-to-br from-gray-800 to-gray-900 rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-gray-700 shadow-2xl">
        <div class="sticky top-0 bg-gray-800/95 backdrop-blur-sm border-b border-gray-700 p-6 flex justify-between items-center">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 rounded-xl" :class="selectedService?.iconBg">
              <component :is="selectedService?.icon" class="w-6 h-6 m-3" />
            </div>
            <div>
              <h3 class="text-2xl font-bold text-white">{{ selectedService?.title }}</h3>
              <p class="text-gray-400">{{ selectedService?.subtitle }}</p>
            </div>
          </div>
          <button @click="closeModal" class="p-2 hover:bg-gray-700/50 rounded-xl transition-colors">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <div class="p-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
              <div class="relative overflow-hidden rounded-2xl mb-6">
                <img 
                  :src="selectedService?.image" 
                  :alt="selectedService?.alt"
                  class="w-full h-64 object-cover"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
              </div>
              <p class="text-gray-300 text-lg">{{ selectedService?.description }}</p>
            </div>
            
            <div>
              <h4 class="text-xl font-bold text-white mb-4">Service Details</h4>
              <div class="space-y-4">
                <div v-for="(feature, index) in selectedService?.features" :key="index" class="flex items-start">
                  <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center mr-3 mt-1">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                  <div>
                    <h5 class="font-semibold text-white">{{ feature.title }}</h5>
                    <p class="text-gray-400 text-sm">{{ feature.description }}</p>
                  </div>
                </div>
              </div>

              <div class="mt-8 p-6 bg-gray-900/50 rounded-2xl">
                <h4 class="text-lg font-bold text-white mb-3">Project Timeline</h4>
                <p class="text-gray-300">{{ selectedService?.timeline }}</p>
                <div class="flex items-center justify-between mt-6">
                  <div>
                    <div class="text-2xl font-bold text-white">{{ selectedService?.price }}</div>
                    <div class="text-gray-400 text-sm">{{ selectedService?.priceSubtitle }}</div>
                  </div>
                  <button 
                    @click="handleBooking" 
                    :disabled="!isLoggedIn"
                    :class="[
                      'px-6 py-3 rounded-xl font-medium transition-all duration-300',
                      isLoggedIn 
                        ? 'bg-gradient-to-r from-blue-500 to-purple-500 hover:shadow-lg cursor-pointer'
                        : 'bg-gray-700 cursor-not-allowed opacity-70'
                    ]"
                  >
                    {{ isLoggedIn ? 'Book Service' : 'Login to Book' }}
                  </button>
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
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const showModal = ref(false)
const selectedService = ref(null)
const isLoggedIn = ref(false) // Connect to your auth store

// Service Details Data with images
const serviceDetails = {
  interior: {
    title: 'Interior Painting',
    subtitle: 'Residential & Commercial',
    image: 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    alt: 'Interior Painting - Beautifully painted living room',
    description: 'Transform your indoor spaces with our comprehensive interior painting services. We specialize in residential and commercial interiors, using premium paints and techniques to ensure flawless results that last for years. Our team handles everything from preparation to cleanup.',
    icon: 'interior-icon',
    iconBg: 'bg-gradient-to-br from-blue-500/20 to-purple-500/20',
    features: [
      {
        title: 'Complete Color Consultation',
        description: 'Personalized color selection and visualization'
      },
      {
        title: 'Surface Preparation',
        description: 'Sanding, priming, and repair work included'
      },
      {
        title: 'Premium Materials',
        description: 'Eco-friendly, low-VOC paints available'
      },
      {
        title: 'Cleanup Service',
        description: 'Thorough cleanup and waste disposal'
      }
    ],
    timeline: 'Most interior projects are completed within 3-7 working days, depending on room size and complexity.',
    price: '₱3,500 - ₱15,000',
    priceSubtitle: 'Starting price per room'
  },
  exterior: {
    title: 'Exterior Painting',
    subtitle: 'Weatherproof & Durable',
    image: 'https://images.unsplash.com/photo-1598228723793-52759bba239c?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    alt: 'Exterior Painting - Freshly painted house exterior',
    description: 'Protect and beautify your property\'s exterior with our durable, weather-resistant painting solutions. Our team uses industry-leading techniques and premium materials to ensure long-lasting protection against sun, rain, and other elements.',
    icon: 'exterior-icon',
    iconBg: 'bg-gradient-to-br from-orange-500/20 to-red-500/20',
    features: [
      {
        title: 'Weather-Resistant Coatings',
        description: 'Specialized paints for Philippine climate'
      },
      {
        title: 'Professional Equipment',
        description: 'Scaffolding and spray equipment available'
      },
      {
        title: 'Surface Repair',
        description: 'Crack filling and water damage repair'
      },
      {
        title: 'Protective Sealing',
        description: 'Waterproof sealing and caulking'
      }
    ],
    timeline: 'Exterior projects typically take 5-10 working days, weather permitting.',
    price: '₱15,000 - ₱50,000',
    priceSubtitle: 'Based on property size'
  },
  consultation: {
    title: 'Color Consultation',
    subtitle: 'Virtual & In-Person',
    image: 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    alt: 'Color Consultation - Paint color samples and swatches',
    description: 'Get expert advice on color selection, paint types, and design concepts. Our consultants help you create the perfect ambiance for your space while staying within your budget. Virtual consultations available for remote clients.',
    icon: 'consultation-icon',
    iconBg: 'bg-gradient-to-br from-purple-500/20 to-pink-500/20',
    features: [
      {
        title: 'Virtual Visualization',
        description: 'See colors on your walls before painting'
      },
      {
        title: 'Material Selection',
        description: 'Guidance on paints, finishes, and brands'
      },
      {
        title: 'Budget Planning',
        description: 'Cost estimation and project planning'
      },
      {
        title: 'Follow-up Support',
        description: 'Post-consultation guidance included'
      }
    ],
    timeline: 'Consultation sessions are 1-2 hours with follow-up available within 7 days.',
    price: '₱1,500 - ₱3,000',
    priceSubtitle: 'One-time consultation fee'
  },
  maintenance: {
    title: 'Maintenance Services',
    subtitle: 'Regular & Emergency',
    image: 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80',
    alt: 'Maintenance Services - Painter performing touch-up work',
    description: 'Keep your paint looking fresh with our comprehensive maintenance services. We offer scheduled upkeep plans, touch-ups, and protective coatings to extend the life of your paint job and protect your investment.',
    icon: 'maintenance-icon',
    iconBg: 'bg-gradient-to-br from-green-500/20 to-emerald-500/20',
    features: [
      {
        title: 'Touch-up Services',
        description: 'Minor repairs and color matching'
      },
      {
        title: 'Protective Coatings',
        description: 'Anti-fungal and UV protective layers'
      },
      {
        title: 'Scheduled Plans',
        description: 'Quarterly or annual maintenance packages'
      },
      {
        title: 'Emergency Repairs',
        description: 'Quick response for urgent issues'
      }
    ],
    timeline: 'Maintenance visits are scheduled based on your chosen plan (quarterly, bi-annually, or annually).',
    price: '₱2,500 - ₱8,000',
    priceSubtitle: 'Per maintenance visit'
  }
}

// Methods
const scrollToServices = () => {
  const element = document.getElementById('services')
  element?.scrollIntoView({ behavior: 'smooth' })
}

const showServiceDetail = (serviceType) => {
  selectedService.value = serviceDetails[serviceType]
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedService.value = null
}

const handleFindProvider = () => {
  if (!isLoggedIn.value) {
    router.push('/login')
    return
  }
  router.push('/providers')
}

const handleBooking = () => {
  if (!isLoggedIn.value) {
    router.push('/login')
    return
  }
  router.push('/booking')
}

// Icons Components
const InteriorIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
  </svg>`
}

const ExteriorIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"></path>
  </svg>`
}

const ConsultationIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
  </svg>`
}

const MaintenanceIcon = {
  template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
  </svg>`
}
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 10px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 5px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Smooth transitions */
* {
  scroll-behavior: smooth;
}

/* Image hover effects */
.group:hover img {
  transform: scale(1.1);
}

/* Gradient borders */
.group:hover .border-blue-500\/50 {
  box-shadow: 0 0 30px rgba(59, 130, 246, 0.3);
}

.group:hover .border-orange-500\/50 {
  box-shadow: 0 0 30px rgba(249, 115, 22, 0.3);
}

.group:hover .border-purple-500\/50 {
  box-shadow: 0 0 30px rgba(139, 92, 246, 0.3);
}

.group:hover .border-green-500\/50 {
  box-shadow: 0 0 30px rgba(34, 197, 94, 0.3);
}

/* Glass effect */
.bg-gray-800\/50 {
  backdrop-filter: blur(10px);
}

/* Modal overlay */
.bg-black\/80 {
  backdrop-filter: blur(4px);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .text-4xl {
    font-size: 2rem;
  }
  
  .text-6xl {
    font-size: 2.5rem;
  }
  
  .p-8 {
    padding: 1.5rem;
  }
}

/* Loading animation for images */
img {
  transition: opacity 0.3s ease;
}

img:not([src]) {
  opacity: 0;
}

/* Focus styles for accessibility */
button:focus, 
a:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.5rem;
}
</style>