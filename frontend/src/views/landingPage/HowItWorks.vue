<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- How It Works Section -->
    <section class="relative overflow-hidden py-12 md:py-20 px-4 sm:px-6 lg:px-8">
      <!-- Background Elements -->
      <div class="absolute inset-0">
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-pink-500/5 rounded-full blur-3xl"></div>
      </div>
      
      <div class="relative max-w-7xl mx-auto">
        <!-- Section Header -->
        <div class="text-center mb-12 md:mb-16">
          <div class="inline-flex items-center px-4 py-2 rounded-full bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20 mb-4">
            <svg class="w-4 h-4 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span class="text-sm font-medium text-blue-300">Simple Workflow</span>
          </div>
          
          <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight mb-4">
            <span class="bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
              How It Works
            </span>
          </h1>
          
          <p class="text-lg md:text-xl text-gray-300 max-w-3xl mx-auto">
            A simple 4-step process to transform your space with perfect colors and professional service.
          </p>
        </div>

        <!-- Process Steps -->
        <div class="relative">
          <!-- Connecting Line for Desktop -->
          <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500/20 via-purple-500/20 to-pink-500/20 -translate-y-1/2 z-0"></div>
          
          <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 lg:gap-4 relative z-10">
            <!-- Step 1: Choose Service Provider -->
            <div class="group">
              <div class="relative">
                <!-- Step Number -->
                <div class="absolute -top-4 -left-4 w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                  1
                </div>
                
                <!-- Step Card -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700 hover:border-blue-500/50 transition-all duration-300 hover:scale-[1.02] group-hover:shadow-xl">
                  <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-500/10 to-blue-600/10 border border-blue-500/20 flex items-center justify-center mb-6 mx-auto">
                    <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                  </div>
                  
                  <h3 class="text-xl font-bold text-white text-center mb-3">Choose Service Provider</h3>
                  
                  <p class="text-gray-300 text-center text-sm md:text-base leading-relaxed">
                    Select from our network of certified paint distributors and service providers in Cavite.
                  </p>
                  
                  <!-- Features List -->
                  <div class="mt-6 space-y-2">
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Verified professionals</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Customer reviews</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Location-based matching</span>
                    </div>
                  </div>
                </div>
                
                <!-- Arrow for Mobile -->
                <div class="lg:hidden flex justify-center mt-4">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Step 2: Virtual Color Visualization -->
            <div class="group">
              <div class="relative">
                <!-- Step Number -->
                <div class="absolute -top-4 -left-4 w-8 h-8 rounded-full bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                  2
                </div>
                
                <!-- Step Card -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700 hover:border-purple-500/50 transition-all duration-300 hover:scale-[1.02] group-hover:shadow-xl">
                  <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-purple-500/10 to-purple-600/10 border border-purple-500/20 flex items-center justify-center mb-6 mx-auto">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                  </div>
                  
                  <h3 class="text-xl font-bold text-white text-center mb-3">Virtual Paint Visualization</h3>
                  
                  <p class="text-gray-300 text-center text-sm md:text-base leading-relaxed">
                    Use our Unity-based virtual tool to visualize paint colors on your walls in real-time.
                  </p>
                  
                  <!-- Features List -->
                  <div class="mt-6 space-y-2">
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Real-time color mixing</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">3D wall visualization</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Lighting simulation</span>
                    </div>
                  </div>
                </div>
                
                <!-- Arrow for Mobile -->
                <div class="lg:hidden flex justify-center mt-4">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Step 3: AI Recommendations -->
            <div class="group">
              <div class="relative">
                <!-- Step Number -->
                <div class="absolute -top-4 -left-4 w-8 h-8 rounded-full bg-gradient-to-r from-pink-500 to-pink-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                  3
                </div>
                
                <!-- Step Card -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700 hover:border-pink-500/50 transition-all duration-300 hover:scale-[1.02] group-hover:shadow-xl">
                  <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-pink-500/10 to-pink-600/10 border border-pink-500/20 flex items-center justify-center mb-6 mx-auto">
                    <svg class="w-8 h-8 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                  </div>
                  
                  <h3 class="text-xl font-bold text-white text-center mb-3">Color Recommendations</h3>
                  
                  <p class="text-gray-300 text-center text-sm md:text-base leading-relaxed">
                    Get color suggestions based on your preferences, room type, and lighting conditions.
                  </p>
                  
                  <!-- Features List -->
                  <div class="mt-6 space-y-2">
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-pink-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Personalized suggestions</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-pink-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Trend analysis</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-pink-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Inventory matching</span>
                    </div>
                  </div>
                </div>
                
                <!-- Arrow for Mobile -->
                <div class="lg:hidden flex justify-center mt-4">
                  <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                  </svg>
                </div>
              </div>
            </div>

            <!-- Step 4: Request Service -->
            <div class="group">
              <div class="relative">
                <!-- Step Number -->
                <div class="absolute -top-4 -left-4 w-8 h-8 rounded-full bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center text-white font-bold text-sm shadow-lg">
                  4
                </div>
                
                <!-- Step Card -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-6 border border-gray-700 hover:border-green-500/50 transition-all duration-300 hover:scale-[1.02] group-hover:shadow-xl">
                  <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-green-500/10 to-green-600/10 border border-green-500/20 flex items-center justify-center mb-6 mx-auto">
                    <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                  </div>
                  
                  <h3 class="text-xl font-bold text-white text-center mb-3">Request Painting Service</h3>
                  
                  <p class="text-gray-300 text-center text-sm md:text-base leading-relaxed">
                    Schedule and manage your painting project with real-time updates and progress tracking.
                  </p>
                  
                  <!-- Features List -->
                  <div class="mt-6 space-y-2">
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-green-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Online scheduling</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-green-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Progress tracking</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 rounded-full bg-green-500"></div>
                      <span class="text-xs md:text-sm text-gray-300">Secure payments</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-16 md:mt-20 text-center">
          <div class="max-w-3xl mx-auto bg-gradient-to-r from-gray-800/30 to-gray-900/30 backdrop-blur-sm rounded-2xl p-8 border border-gray-700">
            <h3 class="text-2xl font-bold text-white mb-4">Ready to Transform Your Space?</h3>
            <p class="text-gray-300 mb-6">
              Experience the future of paint selection and service management with our integrated platform.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <button 
                @click="$router.push('/explore-colors')"
                class="px-8 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-xl font-semibold hover:shadow-2xl transition-all duration-300 hover:scale-105 flex items-center justify-center space-x-3"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                <span>Start Your Project</span>
              </button>
              
              <button 
                @click="$router.push('/services')"
                class="px-8 py-3 border-2 border-gray-600 text-gray-300 rounded-xl font-semibold hover:bg-gray-800/50 transition-all duration-300 flex items-center justify-center space-x-3"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>Learn More</span>
              </button>
            </div>
          </div>
          
          <!-- Defense Explanation Note -->
          <div class="mt-8 p-4 bg-gradient-to-r from-blue-500/5 to-purple-500/5 rounded-xl border border-blue-500/10 max-w-2xl mx-auto">
            <p class="text-sm text-gray-400 italic">
              <span class="font-semibold text-blue-300">Defense Note:</span> 
              This section demonstrates the system workflow, helping users understand the platform's functionality before engaging with interactive features.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

// Workflow steps data
const workflowSteps = [
  {
    step: 1,
    title: 'Choose Service Provider',
    description: 'Select from our network of certified paint distributors and service providers in Cavite.',
    icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
    color: 'blue',
    features: [
      'Verified professionals',
      'Customer reviews',
      'Location-based matching'
    ]
  },
  {
    step: 2,
    title: 'Virtual Paint Visualization',
    description: 'Use our Unity-based virtual tool to visualize paint colors on your walls in real-time.',
    icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
    color: 'purple',
    features: [
      'Real-time color mixing',
      '3D wall visualization',
      'Lighting simulation'
    ]
  },
  {
    step: 3,
    title: 'AI Color Recommendations',
    description: 'Get intelligent color suggestions based on your preferences, room type, and lighting conditions.',
    icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    color: 'pink',
    features: [
      'Personalized suggestions',
      'Trend analysis',
      'Inventory matching'
    ]
  },
  {
    step: 4,
    title: 'Request Painting Service',
    description: 'Schedule and manage your painting project with real-time updates and progress tracking.',
    icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    color: 'green',
    features: [
      'Online scheduling',
      'Progress tracking',
      'Secure payments'
    ]
  }
]
</script>

<style scoped>
/* Custom animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out forwards;
}

/* Step card hover effects */
.group:hover .group-hover\:shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Gradient text animation */
.gradient-text {
  background-size: 200% 200%;
  animation: gradient 3s ease infinite;
}

@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

/* Glass morphism effect */
.glass-effect {
  background: rgba(30, 41, 59, 0.4);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile responsive adjustments */
@media (max-width: 1024px) {
  .lg\:grid-cols-4 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
  
  .lg\:block {
    display: none;
  }
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.4);
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}
</style>