<template>
  <div class="min-h-screen bg-gradient-to-b from-white to-gray-50 flex flex-col">
    <!-- Top Navigation Bar -->
    <TopBar />
    
    <!-- Main Content - Adjusted for seamless integration -->
    <main class="flex-1 w-full">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-gradient-to-r from-gray-900 to-gray-800 text-white">
      <div class="container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Company Info -->
          <div class="space-y-4">
            <div class="flex items-center space-x-3">
              <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-2xl font-bold text-white">CaviteGo Paint</h2>
                <p class="text-sm text-gray-300">Color Your World Beautifully</p>
              </div>
            </div>
            <p class="text-gray-400 text-sm">
              Revolutionizing paint management with virtual color visualization and seamless service integration.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="text-lg font-semibold mb-4 text-white">Quick Links</h3>
            <ul class="space-y-2">
              <li><router-link to="/" class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span>Home</span>
              </router-link></li>
              <li><router-link to="/how-it-works" class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <span>How It Works</span>
              </router-link></li>
              <li><router-link to="/explore-colors" class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                <span>Explore Colors</span>
              </router-link></li>
              <li><router-link to="/services" class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span>Services</span>
              </router-link></li>
            </ul>
          </div>

          <!-- Services -->
          <div>
            <h3 class="text-lg font-semibold mb-4 text-white">Our Services</h3>
            <ul class="space-y-2">
              <li class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <span>Interior Painting</span>
              </li>
              <li class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"></path>
                </svg>
                <span>Exterior Painting</span>
              </li>
              <li class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <span>Color Consultation</span>
              </li>
              <li class="text-gray-400 hover:text-white transition-colors flex items-center space-x-2 group">
                <svg class="w-4 h-4 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span>Maintenance</span>
              </li>
            </ul>
          </div>

          <!-- Contact -->
          <div>
            <h3 class="text-lg font-semibold mb-4 text-white">Contact Us</h3>
            <div class="space-y-3">
              <div class="flex items-center space-x-3 group">
                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                  <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <span class="text-gray-400 group-hover:text-white transition-colors">Cavite, Philippines</span>
              </div>
              <div class="flex items-center space-x-3 group">
                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                  <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <span class="text-gray-400 group-hover:text-white transition-colors">info@cavitegopaint.com</span>
              </div>
              <div class="flex items-center space-x-3 group">
                <div class="w-8 h-8 rounded-full bg-blue-500/20 flex items-center justify-center group-hover:bg-blue-500/30 transition-colors">
                  <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                  </svg>
                </div>
                <span class="text-gray-400 group-hover:text-white transition-colors">+63 912 345 6789</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-700 mt-8 pt-8">
          <div class="flex flex-col md:flex-row justify-between items-center">
            <p class="text-gray-500 text-sm">
              © 2026 CaviteGo Paint. All rights reserved.
            </p>
            <div class="flex space-x-4 mt-4 md:mt-0">
              <a href="#" class="text-gray-500 hover:text-white transition-colors text-sm">Privacy Policy</a>
              <a href="#" class="text-gray-500 hover:text-white transition-colors text-sm">Terms of Service</a>
              <a href="#" class="text-gray-500 hover:text-white transition-colors text-sm">Cookies</a>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import TopBar from '@/layouts/LandingTopBar.vue'
</script>

<style scoped>
/* Custom animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Smooth transitions */
* {
  transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease;
}

/* Ensure main content takes full width and connects seamlessly */
main {
  margin: 0;
  padding: 0;
}
</style>