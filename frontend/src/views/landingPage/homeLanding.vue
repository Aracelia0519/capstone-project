<template>
  <main class="bg-slate-950 text-slate-200 font-sans antialiased overflow-x-hidden selection:bg-cyan-500/30">
    
    <!-- Ultra-Modern Spline Hero Section -->
    <section class="relative w-full h-screen overflow-hidden bg-[#020617]">
      
      <!-- Ambient Hero Glow -->
      <div class="absolute top-0 left-1/4 w-[600px] h-[600px] bg-cyan-600/20 rounded-full blur-[150px] pointer-events-none z-0 mix-blend-screen"></div>
      <div class="absolute bottom-0 right-1/4 w-[600px] h-[600px] bg-purple-600/20 rounded-full blur-[150px] pointer-events-none z-0 mix-blend-screen"></div>

      <spline-viewer 
        url="https://prod.spline.design/lFsfkwYcQzrH9Yus/scene.splinecode"
        class="hidden md:block absolute inset-0 w-full h-full z-0 opacity-90"
      ></spline-viewer>

      <!-- Sleeker Gradient Fade -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent pointer-events-none z-10"></div>

      

      <!-- Floating Scroll Indicator -->
    
    </section>

    <!-- Main Content Area with Ambient Lighting -->
    <div class="w-full min-h-screen bg-[#020617] relative overflow-hidden z-10 pt-12 pb-24 border-t border-white/5">
      
      <!-- Global Ambient Background Orbs -->
      <div class="absolute top-40 -left-64 w-[500px] h-[500px] bg-blue-600/10 rounded-full blur-[120px] pointer-events-none"></div>
      <div class="absolute bottom-40 -right-64 w-[600px] h-[600px] bg-purple-600/10 rounded-full blur-[120px] pointer-events-none"></div>

      <!-- Section 1: Hero Grid -->
      <section class="grid grid-cols-1 lg:grid-cols-12 gap-8 p-8 lg:p-12 max-w-[1920px] mx-auto relative z-10">
        
        <!-- Big Interactive Module Display -->
        <div class="lg:col-span-8 relative min-h-[500px] rounded-[3rem] overflow-hidden border border-white/5 bg-slate-900/50 backdrop-blur-3xl shadow-2xl group transition-all duration-500 hover:border-cyan-500/30">
          <img src="/hero-paint-store.jpg" alt="Cavite Paint Industry" class="absolute inset-0 w-full h-full object-cover opacity-30 group-hover:opacity-40 group-hover:scale-105 transition-all duration-[3s] ease-out"/>
          <div class="absolute inset-0 bg-gradient-to-br from-[#020617]/90 via-transparent to-purple-900/30 mix-blend-overlay"></div>
          
          <!-- Floating UI Elements -->
          <div class="absolute left-10 top-1/2 -translate-y-1/2 flex flex-col gap-5 z-10">
            <div v-for="(module, i) in coreModules" :key="i" 
                 class="w-14 h-14 rounded-2xl border border-white/10 backdrop-blur-2xl flex items-center justify-center text-slate-300 font-bold text-xs bg-slate-900/60 hover:bg-cyan-500/10 hover:border-cyan-400 hover:text-cyan-400 hover:shadow-[0_0_20px_rgba(34,211,238,0.3)] transition-all duration-300 cursor-pointer group/item relative hover:-translate-y-1">
              {{ module.short }}
              <span class="absolute left-16 bg-slate-800 text-[10px] px-3 py-1.5 rounded-lg whitespace-nowrap opacity-0 group-hover/item:opacity-100 transition-opacity border border-white/10 shadow-xl font-medium tracking-wide">
                {{ module.name }}
              </span>
            </div>
          </div>

          <!-- Pulsing Action Button -->
          <div class="absolute bottom-12 left-1/2 -translate-x-1/2 z-20">
            <button @click="$router.push('/virtual-mixing')" 
                    class="relative group/btn w-32 h-32 rounded-full flex flex-col items-center justify-center font-black text-[10px] uppercase tracking-[0.2em] transition-all duration-500 hover:scale-110">
              <div class="absolute inset-0 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 animate-spin-slow opacity-80 group-hover/btn:opacity-100 blur-[2px]"></div>
              <div class="absolute inset-[4px] rounded-full bg-slate-950 flex flex-col items-center justify-center border border-white/10 group-hover/btn:bg-slate-900 transition-colors">
                <span class="text-3xl mb-1 drop-shadow-[0_0_15px_rgba(255,255,255,0.5)] group-hover/btn:scale-110 transition-transform">
                  <img src="/favicon.svg" class="w-12 h-12" alt="icon" />
                </span>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 to-purple-200 mt-1">Virtual Mix</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Side Cards -->
        <div class="lg:col-span-4 flex flex-col gap-8">
          <!-- Mobile App Card -->
          <div class="bg-slate-900/40 backdrop-blur-2xl rounded-[3rem] p-8 h-1/2 flex flex-col justify-end relative overflow-hidden group border border-white/5 hover:border-purple-500/30 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_20px_40px_-15px_rgba(168,85,247,0.2)]">
            <img src="/mobile-app.jpg" class="absolute inset-0 w-full h-full object-cover opacity-10 group-hover:scale-110 transition-transform duration-[2s]" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
            
            <div class="relative z-10">
              <div class="w-14 h-14 bg-purple-500/10 rounded-2xl flex items-center justify-center mb-6 border border-purple-500/20 group-hover:bg-purple-500/20 group-hover:shadow-[0_0_20px_rgba(168,85,247,0.3)] transition-all">
                <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
              </div>
              <span class="text-[10px] font-black tracking-[0.3em] uppercase text-purple-400">Mobile App</span>
              <h3 class="text-3xl font-bold mt-2 text-white tracking-tight">On-The-Go Access</h3>
              <p class="text-sm text-slate-400 mt-3 font-light">Android companion app for field operations & real-time updates.</p>
              
              <a href="https://median.co/share/eenpqek#apk" target="_blank" rel="noopener noreferrer" 
                 class="inline-flex items-center gap-3 mt-6 px-6 py-3 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-purple-500 hover:text-white hover:border-purple-400 transition-all shadow-lg hover:shadow-[0_0_20px_rgba(168,85,247,0.4)]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                Download APK
              </a>
            </div>
          </div>

          <!-- DSS Card -->
          <div class="bg-slate-900/40 backdrop-blur-2xl rounded-[3rem] p-8 h-1/2 flex flex-col justify-end relative overflow-hidden group border border-white/5 hover:border-blue-500/30 transition-all duration-500 hover:-translate-y-2 hover:shadow-[0_20px_40px_-15px_rgba(59,130,246,0.2)]">
            <img src="/dss-analytics.jpg" class="absolute inset-0 w-full h-full object-cover opacity-10 group-hover:scale-110 transition-transform duration-[2s]" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
            
            <div class="relative z-10">
              <div class="w-14 h-14 bg-blue-500/10 rounded-2xl flex items-center justify-center mb-6 border border-blue-500/20 group-hover:bg-blue-500/20 group-hover:shadow-[0_0_20px_rgba(59,130,246,0.3)] transition-all">
                <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
              <span class="text-[10px] font-black tracking-[0.3em] uppercase text-blue-400">DSS Module</span>
              <h3 class="text-3xl font-bold mt-2 text-white tracking-tight">Decision Support</h3>
              <p class="text-sm text-slate-400 mt-3 font-light">AI-driven analytics & forecasting for Cavite market trends.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Section 2: Core Business Modules (4-Grid) -->
      <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 p-8 lg:p-12 max-w-[1920px] mx-auto relative z-10">
        
        <div v-for="(mod, i) in businessModules" :key="i" class="bg-slate-900/40 backdrop-blur-xl rounded-[3rem] p-10 relative overflow-hidden group border border-white/5 transition-all duration-500 hover:-translate-y-2 cursor-pointer flex flex-col justify-between min-h-[300px]" :class="mod.hoverBorder">
          
          <div class="relative z-10">
            <div class="flex justify-between items-start mb-6">
              <span class="text-[10px] font-black tracking-widest uppercase" :class="mod.textColor">{{ mod.tag }}</span>
              <!-- Animated pulse dot -->
              <span class="w-2 h-2 rounded-full animate-pulse" :class="mod.dotColor"></span>
            </div>
            <h4 class="text-2xl font-bold text-white tracking-tight">{{ mod.title }}</h4>
            <p class="text-sm text-slate-400 mt-4 leading-relaxed font-light">{{ mod.desc }}</p>
          </div>
          
          <div class="relative z-10 mt-8">
            <button class="w-fit px-6 py-3 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-slate-950 transition-colors shadow-lg">
              {{ mod.action }}
            </button>
          </div>
          
          <!-- Ambient Card Glow -->
          <div class="absolute -right-20 -bottom-20 w-64 h-64 rounded-full blur-[80px] transition-all duration-700 opacity-30 group-hover:opacity-60" :class="mod.glowBg"></div>
          
          <img :src="mod.img" class="absolute right-[-10%] bottom-[-10%] w-72 h-72 object-cover rounded-full opacity-10 group-hover:opacity-30 group-hover:scale-110 transition-all duration-[2s] mix-blend-screen" />
        </div>

      </section>

      <!-- Center Informational Break -->
      <section class="py-24 relative z-10 flex flex-col items-center justify-center text-center px-4 overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-2xl h-[200px] bg-gradient-to-r from-cyan-600/20 to-purple-600/20 blur-[100px] pointer-events-none"></div>
        
        <p class="text-[10px] font-black uppercase tracking-[0.4em] text-cyan-400 mb-6 border border-cyan-500/30 px-4 py-2 rounded-full bg-cyan-500/10">Cavite Paint IMS</p>
        <h1 class="font-black text-5xl sm:text-6xl md:text-7xl lg:text-8xl text-transparent bg-clip-text bg-gradient-to-br from-white via-slate-200 to-slate-500 tracking-tighter drop-shadow-2xl">
          Integrated<br/>Management System
        </h1>
        <p class="text-base sm:text-lg text-slate-400 leading-relaxed max-w-2xl mx-auto mt-8 font-light">
          A centralized, high-performance platform for paint distributors and service providers in Cavite, seamlessly integrating 12 modules into one unified ecosystem.
        </p>
        
        <div class="flex items-center gap-4 mt-12 bg-slate-900/50 backdrop-blur-xl border border-white/10 p-2 pl-4 pr-6 rounded-full shadow-2xl">
          <div class="flex -space-x-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-400 to-blue-600 border-2 border-[#020617] flex items-center justify-center text-xs font-black text-white shadow-lg">NC</div>
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-400 to-pink-600 border-2 border-[#020617] flex items-center justify-center text-xs font-black text-white shadow-lg">ST</div>
          </div>
          <div class="flex flex-col text-left border-l border-white/10 pl-4">
            <span class="text-xs font-bold text-white tracking-wide">Developed by Researchers</span>
            <span class="text-[10px] text-slate-400">Namoc · Isanan · Prado · Pellazar · Ermita</span>
          </div>
          <div class="ml-4 text-[10px] text-white bg-slate-800 px-3 py-1.5 rounded-full border border-white/10 shadow-inner">
            ⚡ v1.0.0
          </div>
        </div>
      </section>

      <!-- Section 3: Extra Modules (3-Grid) -->
      <section class="grid grid-cols-1 md:grid-cols-3 gap-8 p-8 lg:p-12 max-w-[1920px] mx-auto relative z-10">
        
        <div v-for="(mod, i) in extraModules" :key="i" class="bg-slate-900/40 backdrop-blur-xl rounded-[3rem] p-10 relative overflow-hidden group border border-white/5 transition-all duration-500 hover:-translate-y-2 cursor-pointer flex flex-col justify-between min-h-[300px]" :class="mod.hoverBorder">
          <div class="relative z-10">
            <div class="flex justify-between items-start mb-6">
              <span class="text-[10px] font-black tracking-widest uppercase" :class="mod.textColor">{{ mod.tag }}</span>
            </div>
            <h4 class="text-2xl font-bold text-white tracking-tight">{{ mod.title }}</h4>
            <p class="text-sm text-slate-400 mt-4 leading-relaxed font-light">{{ mod.desc }}</p>
          </div>
          <div class="relative z-10 mt-8">
            <button class="w-fit px-6 py-3 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-slate-950 transition-colors shadow-lg">
              {{ mod.action }}
            </button>
          </div>
          <div class="absolute -right-20 -bottom-20 w-64 h-64 rounded-full blur-[80px] transition-all duration-700 opacity-30 group-hover:opacity-60" :class="mod.glowBg"></div>
          <img :src="mod.img" class="absolute right-[-10%] bottom-[-10%] w-72 h-72 object-cover rounded-full opacity-10 group-hover:opacity-30 group-hover:scale-110 transition-all duration-[2s] mix-blend-screen" />
        </div>

      </section>

      <!-- Upgraded Cool FAQ Section -->
      <section class="max-w-5xl mx-auto py-24 px-8 relative z-20 w-full overflow-hidden">
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-cyan-600/10 rounded-full blur-[120px] pointer-events-none z-0"></div>

        <div class="mb-16 text-center flex flex-col items-center relative z-10">
          <span class="px-4 py-1.5 rounded-full border border-cyan-500/30 bg-cyan-500/10 text-[10px] font-black uppercase tracking-[0.4em] text-cyan-400 mb-6 shadow-[0_0_15px_rgba(34,211,238,0.2)]">Knowledge Base</span>
          <h2 class="text-4xl md:text-6xl font-black mt-2 text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-200 to-slate-500 tracking-tight">System FAQ</h2>
          <p class="text-sm md:text-base text-slate-400 mt-4 max-w-lg">Everything you need to know about the system features, products, and support procedures.</p>
        </div>
        
        <div class="space-y-4 relative z-10 max-w-4xl mx-auto">
          <div v-for="(faq, i) in faqs" :key="i"
               class="group relative rounded-3xl transition-all duration-500"
               :class="faq.open ? 'bg-slate-800/80 shadow-[0_0_30px_rgba(34,211,238,0.1)] scale-[1.02] border border-cyan-500/50' : 'bg-slate-900/40 border-white/5 hover:bg-slate-800/40 border'">

            <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-3xl transition-all duration-500 z-20"
                 :class="faq.open ? 'bg-cyan-400 shadow-[0_0_15px_rgba(34,211,238,0.8)]' : 'bg-transparent group-hover:bg-slate-600'"></div>

            <button @click="toggleFaq(i)" class="w-full text-left px-6 md:px-8 py-5 md:py-6 flex items-center justify-between focus:outline-none rounded-3xl relative z-10 overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-r from-cyan-500/10 to-purple-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none" :class="{'opacity-100': faq.open}"></div>
              
              <div class="flex items-center gap-4 md:gap-6 pr-4 relative z-10">
                <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl flex items-center justify-center transition-all duration-500 flex-shrink-0 border"
                     :class="faq.open ? 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30 shadow-[0_0_15px_rgba(34,211,238,0.3)]' : 'bg-slate-800 text-slate-400 border-slate-700 group-hover:text-cyan-300 group-hover:border-slate-600'">
                  <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" v-html="faq.icon"></svg>
                </div>
                <span class="font-bold text-sm md:text-lg transition-colors duration-300 tracking-wide" :class="faq.open ? 'text-cyan-300' : 'text-slate-200 group-hover:text-white'">{{ faq.question }}</span>
              </div>

              <div class="w-8 h-8 rounded-full border flex items-center justify-center transition-all duration-500 flex-shrink-0 relative z-10"
                   :class="faq.open ? 'border-cyan-500/50 bg-cyan-500/10 text-cyan-400 rotate-180' : 'border-slate-600 text-slate-500 group-hover:border-slate-400 group-hover:text-slate-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </div>
            </button>
            
            <transition name="cool-fade">
              <div v-show="faq.open" class="px-6 md:px-8 pb-6 md:pb-8 pt-2 relative z-10">
                <div class="pl-[3.5rem] md:pl-[4.5rem]">
                  <p class="text-sm md:text-base text-slate-300 leading-relaxed border-l border-white/10 pl-5 py-1 font-light">
                    {{ faq.answer }}
                  </p>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </section>

      <!-- Footer Features Grid -->
      <div class="max-w-[1920px] mx-auto px-8 lg:px-12 relative z-20 pt-12 pb-8">
        <div class="bg-slate-900/30 backdrop-blur-2xl border border-white/5 rounded-[3rem] p-10 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6 shadow-2xl">
          <div v-for="(feature, i) in systemFeatures" :key="i" class="flex flex-col items-center group cursor-pointer p-4 rounded-3xl hover:bg-white/[0.02] transition-colors duration-300">
            <div class="w-16 h-16 rounded-2xl bg-slate-800/50 border border-white/5 flex items-center justify-center mb-4 transition-all duration-500 group-hover:-translate-y-2 group-hover:bg-cyan-500/10 group-hover:border-cyan-500/30 group-hover:shadow-[0_10px_30px_-10px_rgba(34,211,238,0.4)]">
              <component :is="feature.icon" class="w-7 h-7 text-slate-500 group-hover:text-cyan-400 transition-colors duration-300 drop-shadow-md" />
            </div>
            <span class="text-[9px] font-black uppercase tracking-widest text-center text-slate-500 group-hover:text-cyan-400 transition-colors duration-300">{{ feature.name }}</span>
          </div>
        </div>
      </div>
      
    </div>
  </main>
</template>

<script setup>
import { h, ref, onMounted, onUnmounted } from 'vue';

// Hero Tag Definitions
const heroTags = [
  { label: 'IMS', textClass: 'text-cyan-300', bgGlow: 'bg-cyan-500', borderHover: 'group-hover:border-cyan-400' },
  { label: 'DSS Module', textClass: 'text-blue-300', bgGlow: 'bg-blue-500', borderHover: 'group-hover:border-blue-400' },
  { label: 'VR Mixing', textClass: 'text-purple-300', bgGlow: 'bg-purple-500', borderHover: 'group-hover:border-purple-400' },
  { label: 'Mobile App', textClass: 'text-pink-300', bgGlow: 'bg-pink-500', borderHover: 'group-hover:border-pink-400' },
  { label: 'Finance', textClass: 'text-green-300', bgGlow: 'bg-green-500', borderHover: 'group-hover:border-green-400' }
];

// 4-Grid Modules
const businessModules = [
  { tag: 'SCM Module', title: 'Supply Chain', desc: 'End-to-end tracking from supplier to service provider.', action: 'Configure', img: '/scm-meeting.jpg', hoverBorder: 'hover:border-blue-500/30 hover:shadow-[0_20px_40px_-15px_rgba(59,130,246,0.2)]', textColor: 'text-blue-400', dotColor: 'bg-blue-500', glowBg: 'bg-blue-600' },
  { tag: 'CRM + Chat', title: 'Client Engagement', desc: 'Customer profiles, service history & real-time messaging.', action: 'Connect', img: '/crm-engagement.jpg', hoverBorder: 'hover:border-purple-500/30 hover:shadow-[0_20px_40px_-15px_rgba(168,85,247,0.2)]', textColor: 'text-purple-400', dotColor: 'bg-purple-500', glowBg: 'bg-purple-600' },
  { tag: 'Logistics', title: 'Delivery Management', desc: 'Manage delivery schedules, transportation, and order fulfillment.', action: 'Track', img: '/logistics-truck.jpg', hoverBorder: 'hover:border-orange-500/30 hover:shadow-[0_20px_40px_-15px_rgba(249,115,22,0.2)]', textColor: 'text-orange-400', dotColor: 'bg-orange-500', glowBg: 'bg-orange-600' },
  { tag: 'Procurement', title: 'Material Requirements', desc: 'Identify needed products and send requests to suppliers.', action: 'Order', img: '/procurement-warehouse.jpg', hoverBorder: 'hover:border-indigo-500/30 hover:shadow-[0_20px_40px_-15px_rgba(99,102,241,0.2)]', textColor: 'text-indigo-400', dotColor: 'bg-indigo-500', glowBg: 'bg-indigo-600' }
];

// 3-Grid Modules
const extraModules = [
  { tag: 'EM Module', title: 'Employee Management', desc: 'Employee records, roles, and performance monitoring for distributors.', action: 'Manage', img: '/hr-team.jpg', hoverBorder: 'hover:border-amber-500/30 hover:shadow-[0_20px_40px_-15px_rgba(245,158,11,0.2)]', textColor: 'text-amber-400', glowBg: 'bg-amber-600' },
  { tag: 'Finance Module', title: 'Financial Management', desc: 'Track sales, expenses, revenue and profitability analytics.', action: 'View', img: '/finance-calculator.jpg', hoverBorder: 'hover:border-green-500/30 hover:shadow-[0_20px_40px_-15px_rgba(34,197,94,0.2)]', textColor: 'text-green-400', glowBg: 'bg-green-600' },
  { tag: 'E-Commerce', title: 'Online Store', desc: 'Browse products, check availability, and place orders online.', action: 'Shop', img: '/ecommerce-store.jpg', hoverBorder: 'hover:border-pink-500/30 hover:shadow-[0_20px_40px_-15px_rgba(236,72,153,0.2)]', textColor: 'text-pink-400', glowBg: 'bg-pink-600' }
];

// FAQs Array (Retained previous upgrades)
const faqs = ref([
  { 
    question: 'General Information: What is this platform?', 
    answer: 'This is a comprehensive Integrated Management System designed specifically for paint distributors and service providers to handle inventory, logistics, and partner management under one umbrella.', 
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
    open: false 
  },
  { 
    question: 'How do I book Appointments for a service provider?', 
    answer: 'To book a service appointment, please navigate to the E-Commerce portal and select the "Services" option from the top navigation bar. From there, you may browse and select the specific service professional you require.', 
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />',
    open: false 
  },
  { 
    question: 'How does Products & Shopping work?', 
    answer: 'To purchase products, navigate to the E-Commerce portal and select the "Shop" tab located on the top navigation bar. You may then browse our comprehensive catalog for the items you need or desire.', 
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />',
    open: false 
  },
  { 
    question: 'What is Virtual Paint Color Mixing?', 
    answer: 'The VR Color Mixing tool provides an interactive environment where users can simulate blending different paint shades in real-time, allowing you to preview custom colors before making a purchase.', 
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />',
    open: false 
  },
  { 
    question: 'What does the Decision Support System (DSS) do?', 
    answer: 'The DSS leverages historical sales and geographic data to provide analytics, demand forecasting, and priority rankings to help administrators and distributors make well-informed business decisions.', 
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
    open: false 
  },
  { 
    question: 'How do I register as a user (Client, Service Provider, Distributor, and Supplier)?', 
    answer: 'Prospective users can initiate the registration process by navigating to the designated sign-up portal. You will be prompted to select your intended account type—Client, Service Provider, Distributor, or Supplier—and submit the appropriate credentials. Upon successful review and verification, your specific portal access will be granted.', 
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />',
    open: false 
  },
  { 
    question: 'Account & Security: What if I hit the maximum account submissions or face errors?', 
    answer: 'If you encounter continuous account errors, trigger brute-force warnings, or reach the maximum account submission limit, your session may be flagged for security. Please contact the system administrator immediately to restore access or resolve the issue.', 
    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />',
    open: false 
  }
]);

const toggleFaq = (index) => {
  faqs.value.forEach((faq, i) => {
    if (i !== index) faq.open = false;
  });
  faqs.value[index].open = !faqs.value[index].open;
};

// Scroll handling for Spline
const handleScroll = (e) => {
  if (e.target.closest('spline-viewer')) {
    window.scrollBy({ top: e.deltaY, behavior: 'auto' });
  }
};

onMounted(() => {
  if (!document.querySelector('script[src="https://unpkg.com/@splinetool/viewer@1.12.81/build/spline-viewer.js"]')) {
    const script = document.createElement('script');
    script.type = 'module';
    script.src = 'https://unpkg.com/@splinetool/viewer@1.12.81/build/spline-viewer.js';
    document.head.appendChild(script);
  }
  window.addEventListener('wheel', handleScroll, { passive: true });
});

onUnmounted(() => {
  window.removeEventListener('wheel', handleScroll);
});

// Minimal Icons Setup
const InventoryIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' })]); }};
const DSSIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })]); }};
const VirtualMixIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01' })]); }};
const MobileIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z' })]); }};
const HRIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })]); }};
const FinanceIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })]); }};
const SCMIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' })]); }};
const CRMIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5' })]); }};
const ChatIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' })]); }};
const EcommerceIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z' })]); }};
const ProcurementIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' })]); }};
const LogisticsIcon = { render() { return h('svg', { class: 'w-full h-full', fill: 'none', stroke: 'currentColor', viewBox: '0 0 24 24' }, [h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM9 17h10M5 17h2m3-4h6m-9-4h6m-6 4h2M5 9h14M3 3l2 2m0 0l-2 2m2-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z' })]); }};

const coreModules = [
  { short: 'IMS', name: 'Management System' },
  { short: 'DSS', name: 'Decision Support' },
  { short: 'VR', name: 'Color Mixing' },
  { short: 'APP', name: 'Mobile App' },
  { short: 'HR', name: 'Human Resources' },
  { short: 'FIN', name: 'Finance Module' },
  { short: 'SCM', name: 'Supply Chain' },
  { short: 'CRM', name: 'Customer Relations' }
];

const systemFeatures = [
  { name: 'Inventory Mgmt', icon: InventoryIcon },
  { name: 'Decision System', icon: DSSIcon },
  { name: 'Virtual Mixing', icon: VirtualMixIcon },
  { name: 'Mobile Platform', icon: MobileIcon },
  { name: 'Employee Mgmt', icon: HRIcon },
  { name: 'Finance Hub', icon: FinanceIcon },
  { name: 'Supply Chain', icon: SCMIcon },
  { name: 'Client Relations', icon: CRMIcon },
  { name: 'Live Chat', icon: ChatIcon },
  { name: 'E-Commerce', icon: EcommerceIcon },
  { name: 'Procurement', icon: ProcurementIcon },
  { name: 'Logistics', icon: LogisticsIcon }
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;700;800&display=swap');

main {
  font-family: 'Plus Jakarta Sans', sans-serif;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-track {
  background: #020617;
}
::-webkit-scrollbar-thumb {
  background: #1e293b;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #38bdf8;
}

/* Keyframes for New Animations */
@keyframes gradient-x {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
.animate-gradient {
  animation: gradient-x 6s ease infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0) translateX(-50%); }
  50% { transform: translateY(-10px) translateX(-50%); }
}
.animate-float {
  animation: float 3s ease-in-out infinite;
}

@keyframes spin-slow {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin-slow {
  animation: spin-slow 8s linear infinite;
}

/* FAQ Slide Transition */
.cool-fade-enter-active {
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.cool-fade-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.cool-fade-enter-from,
.cool-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.98);
}
</style>