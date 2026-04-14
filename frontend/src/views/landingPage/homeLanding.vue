<template>
  <main class="bg-slate-950 text-slate-200 font-sans antialiased overflow-x-hidden">
    
    <section class="relative w-full h-screen overflow-hidden bg-slate-950">
      
      <spline-viewer 
        url="https://prod.spline.design/lFsfkwYcQzrH9Yus/scene.splinecode"
        class="hidden md:block absolute inset-0 w-full h-full z-0"
      ></spline-viewer>

      <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent pointer-events-none z-10"></div>

      <div class="absolute bottom-20 left-0 w-full px-8 lg:px-12 flex flex-col gap-6 z-20 pointer-events-none">
        <div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-8">
          
          <div class="flex items-center gap-4 md:hidden">
            <h1 class="text-5xl md:text-7xl lg:text-8xl font-black tracking-tighter bg-gradient-to-r from-cyan-400 to-purple-400 bg-clip-text text-transparent drop-shadow-2xl">
              CAVITEGO PAINT
            </h1>
          </div>

        </div>
        <div class="max-w-4xl bg-slate-950/40 backdrop-blur-md p-5 rounded-2xl border-l-4 border-cyan-500 pointer-events-auto shadow-2xl">
          <p class="text-base md:text-lg lg:text-xl text-slate-200 leading-relaxed drop-shadow-md">
            An Integrated Management System for paint distributors and service providers in Cavite, 
            featuring Decision Support System, Virtual Color Mixing, and companion mobile application.
          </p>
        </div>
        <div class="flex gap-3 mt-2 flex-wrap pointer-events-auto">
          <span class="px-4 py-2 bg-cyan-900/40 backdrop-blur-md border border-cyan-500/40 rounded-full text-xs font-bold text-cyan-300 shadow-lg">IMS</span>
          <span class="px-4 py-2 bg-purple-900/40 backdrop-blur-md border border-purple-500/40 rounded-full text-xs font-bold text-purple-300 shadow-lg">DSS</span>
          <span class="px-4 py-2 bg-blue-900/40 backdrop-blur-md border border-blue-500/40 rounded-full text-xs font-bold text-blue-300 shadow-lg">VR Mixing</span>
          <span class="px-4 py-2 bg-pink-900/40 backdrop-blur-md border border-pink-500/40 rounded-full text-xs font-bold text-pink-300 shadow-lg">Mobile App</span>
          <span class="px-4 py-2 bg-amber-900/40 backdrop-blur-md border border-amber-500/40 rounded-full text-xs font-bold text-amber-300 shadow-lg">EM</span>
          <span class="px-4 py-2 bg-green-900/40 backdrop-blur-md border border-green-500/40 rounded-full text-xs font-bold text-green-300 shadow-lg">Finance</span>
        </div>
      </div>

      <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex flex-col items-center gap-2 pointer-events-none opacity-80 animate-bounce">
        <span class="text-[10px] uppercase tracking-widest text-cyan-400 font-bold drop-shadow-lg">Scroll to Explore</span>
        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
      </div>

    </section>

    <div class="w-full min-h-screen bg-slate-900/20 backdrop-blur-3xl flex flex-col relative overflow-hidden z-10 border-t border-slate-700/50 pt-8">
      
      <section class="grid grid-cols-1 lg:grid-cols-12 gap-6 p-8 lg:p-10 flex-grow">
        
        <div class="lg:col-span-8 relative min-h-[500px] rounded-[45px] overflow-hidden border border-slate-700/50 shadow-inner group">
          <img 
            src="/hero-paint-store.jpg" 
            alt="Cavite Paint Industry" 
            class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-[2s]"
          />
          <div class="absolute inset-0 bg-gradient-to-br from-slate-950/80 via-transparent to-purple-900/20"></div>
          
          <div class="absolute left-10 top-1/2 -translate-y-1/2 flex flex-col gap-6 z-10">
            <div v-for="(module, i) in coreModules" :key="i" class="w-14 h-14 rounded-2xl border border-white/10 backdrop-blur-xl flex items-center justify-center text-white font-bold text-xs bg-slate-800/40 hover:border-cyan-400 hover:text-cyan-400 transition-all duration-300 cursor-pointer group relative">
              {{ module.short }}
              <span class="absolute left-16 bg-slate-800 text-[8px] px-2 py-1 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity border border-slate-700">
                {{ module.name }}
              </span>
            </div>
          </div>

          <div class="absolute bottom-12 left-1/2 -translate-x-1/2 z-20">
            <button @click="$router.push('/virtual-mixing')" class="bg-gradient-to-br from-blue-600 to-purple-600 text-white w-28 h-28 rounded-full flex flex-col items-center justify-center font-black text-[10px] uppercase tracking-widest border-[6px] border-slate-950 shadow-[0_0_30px_rgba(59,130,246,0.5)] hover:scale-110 transition-transform active:scale-95">
              <span class="text-3xl mb-1">
                <img src="/favicon.svg" class="w-15 h-15" alt="icon" />
              </span>
              Try Virtual Mix
            </button>
          </div>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
          <div class="bg-slate-900/40 backdrop-blur-xl rounded-[45px] p-8 h-1/2 flex flex-col justify-end relative overflow-hidden group border border-slate-700/50 shadow-xl">
             <img src="/mobile-app.jpg" class="absolute inset-0 w-full h-full object-cover opacity-10 group-hover:scale-110 transition-transform duration-1000" />
            <div class="relative z-10">
              <div class="w-12 h-12 bg-purple-600/20 rounded-xl flex items-center justify-center mb-4 border border-purple-500/30">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <span class="text-[10px] font-black tracking-[0.3em] uppercase text-purple-400">Mobile App</span>
              <h3 class="text-3xl font-bold mt-2 text-white">On-The-Go Access</h3>
              <p class="text-xs text-slate-400 mt-2">Android companion app for field operations</p>
              
              <a 
                href="https://median.co/share/eenpqek#apk" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="inline-flex items-center gap-2 mt-5 px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download APK
              </a>
            </div>
            <span class="absolute top-8 right-8 text-2xl text-slate-700 group-hover:text-purple-400 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </span>
          </div>

          <div class="bg-slate-900/40 backdrop-blur-xl rounded-[45px] p-8 h-1/2 flex flex-col justify-end relative overflow-hidden group border border-slate-700/50 shadow-xl">
            <img src="/dss-analytics.jpg" class="absolute inset-0 w-full h-full object-cover opacity-10 group-hover:opacity-30 transition-opacity duration-700" />
            <div class="relative z-10">
              <div class="w-12 h-12 bg-blue-600/20 rounded-xl flex items-center justify-center mb-4 border border-blue-500/30">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
              <span class="text-[10px] font-black tracking-[0.3em] uppercase text-blue-400">DSS Module</span>
              <h3 class="text-3xl font-bold mt-2 text-white">Decision Support System</h3>
              <p class="text-xs text-slate-400 mt-2">Analytics & forecasting for Cavite market trends</p>
            </div>
            <span class="absolute top-8 right-8 text-2xl text-slate-700 group-hover:text-blue-400 transition-colors">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </span>
          </div>
        </div>
      </section>

      <section class="grid grid-cols-1 lg:grid-cols-12 gap-12 p-8 lg:p-10">
        
        <div class="lg:col-span-4 space-y-6">
          <div class="bg-slate-900/40 rounded-[45px] p-10 relative overflow-hidden group border border-slate-700/50 hover:border-blue-500/30 transition-colors">
            <div class="relative z-10 h-full flex flex-col justify-between min-h-[160px]">
              <div>
                <span class="text-[10px] font-black text-slate-600 tracking-widest uppercase">SCM Module</span>
                <h4 class="text-3xl font-bold mt-2 text-white">Supply Chain</h4>
                <p class="text-sm text-slate-500 mt-4 leading-relaxed max-w-[180px]">End-to-end tracking from supplier to service provider.</p>
              </div>
              <button class="w-fit px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all">Configure</button>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-blue-600/10 rounded-full blur-[80px] group-hover:bg-blue-600/20 transition-all"></div>
            <img src="/scm-meeting.jpg" class="absolute right-[-10%] bottom-[-10%] w-72 h-72 object-cover rounded-full opacity-25 group-hover:opacity-50 group-hover:scale-110 transition-all duration-700 mix-blend-lighten" />
          </div>

          <div class="bg-slate-900/40 rounded-[45px] p-10 relative overflow-hidden group border border-slate-700/50 hover:border-purple-500/30 transition-colors">
            <div class="relative z-10 h-full flex flex-col justify-between min-h-[160px]">
              <div>
                <span class="text-[10px] font-black text-slate-600 tracking-widest uppercase">CRM + Chat</span>
                <h4 class="text-3xl font-bold mt-2 text-white">Client Engagement</h4>
                <p class="text-sm text-slate-500 mt-4 leading-relaxed max-w-[180px]">Customer profiles, service history & real-time messaging.</p>
              </div>
              <button class="w-fit px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all">Connect</button>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-purple-600/10 rounded-full blur-[80px] group-hover:bg-purple-600/20 transition-all"></div>
            <img src="/crm-engagement.jpg" class="absolute right-[-5%] bottom-[-5%] w-72 h-72 object-cover rounded-full opacity-25 group-hover:opacity-50 group-hover:scale-110 transition-all duration-700 mix-blend-lighten" />
          </div>
          <div class="bg-slate-900/40 rounded-[45px] p-10 relative overflow-hidden group border border-slate-700/50 hover:border-orange-500/30 transition-colors">
            <div class="relative z-10 h-full flex flex-col justify-between min-h-[160px]">
              <div>
                <span class="text-[10px] font-black text-slate-600 tracking-widest uppercase">Logistics</span>
                <h4 class="text-3xl font-bold mt-2 text-white">Delivery Management</h4>
                <p class="text-sm text-slate-500 mt-4 leading-relaxed max-w-[180px]">Manage delivery schedules, transportation, and order fulfillment.</p>
              </div>
              <button class="w-fit px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all">Track</button>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-orange-600/10 rounded-full blur-[80px] group-hover:bg-orange-600/20 transition-all"></div>
            <img src="/logistics-truck.jpg" class="absolute right-[-10%] bottom-[-10%] w-72 h-72 object-cover rounded-full opacity-25 group-hover:opacity-50 group-hover:scale-110 transition-all duration-700 mix-blend-lighten" />
          </div>

          <div class="bg-slate-900/40 rounded-[45px] p-10 relative overflow-hidden group border border-slate-700/50 hover:border-indigo-500/30 transition-colors">
            <div class="relative z-10 h-full flex flex-col justify-between min-h-[160px]">
              <div>
                <span class="text-[10px] font-black text-slate-600 tracking-widest uppercase">Procurement</span>
                <h4 class="text-3xl font-bold mt-2 text-white">Material Requirements</h4>
                <p class="text-sm text-slate-500 mt-4 leading-relaxed max-w-[180px]">Identify needed products and send requests to suppliers.</p>
              </div>
              <button class="w-fit px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all">Order</button>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-indigo-600/10 rounded-full blur-[80px] group-hover:bg-indigo-600/20 transition-all"></div>
            <img src="/procurement-warehouse.jpg" class="absolute right-[-5%] bottom-[-5%] w-72 h-72 object-cover rounded-full opacity-25 group-hover:opacity-50 group-hover:scale-110 transition-all duration-700 mix-blend-lighten" />
          </div>
        </div>

        <div class="lg:col-span-4 space-y-8">
          <div class="space-y-4">
            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-blue-400 text-center">Cavite Paint IMS</p>
            <h1
              class="font-bold text-center leading-tight
                    text-3xl sm:text-4xl md:text-5xl lg:text-6xl
                    px-4 break-words">
              Integrated
              <br class="sm:hidden" />
              Management
              <br class="sm:hidden" />
              System
            </h1>
          </div>
          <p class="text-sm sm:text-base text-slate-500 leading-relaxed max-w-sm mx-auto text-center">
            A centralized platform for paint distributors and service providers in Cavite, integrating 12 core modules for seamless operations.
          </p>
          
          <div class="flex flex-col gap-4">
            <div class="bg-slate-800/40 backdrop-blur-sm rounded-2xl p-5 border border-slate-700/50">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 bg-cyan-600/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
                <span class="text-xs font-bold text-slate-300">Developed by NCST Researchers</span>
              </div>
              <p class="text-[11px] text-slate-500 leading-relaxed">
                J. Namoc · M. Isanan · D. Prado · G. Pellazar · A. Ermita
              </p>
            </div>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="flex -space-x-2">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-cyan-500 to-blue-500 border-2 border-slate-900 flex items-center justify-center text-[10px] font-bold text-white">NCST</div>
                </div>
                <span class="text-[10px] text-white">BSIT Capstone 2026</span>
              </div>
              <div class="text-[10px] text-white bg-slate-800/30 px-3 py-1.5 rounded-full border border-slate-700/30">
                ⚡ v1.0.0
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-4 space-y-6">
          <div class="mb-12">
            <span class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-500">Additional Modules</span>
            <div class="w-12 h-0.5 bg-gradient-to-r from-cyan-500 to-purple-500 mt-2"></div>
          </div>
          
          <div class="bg-slate-900/40 rounded-[45px] p-10 relative overflow-hidden group border border-slate-700/50 hover:border-amber-500/30 transition-colors">
            <div class="relative z-10 h-full flex flex-col justify-between min-h-[160px]">
              <div>
                <span class="text-[10px] font-black text-slate-600 tracking-widest uppercase">EM Module</span>
                <h4 class="text-3xl font-bold mt-2 text-white">Employee Management</h4>
                <p class="text-sm text-slate-500 mt-4 leading-relaxed max-w-[180px]">Employee records, roles, and performance monitoring for distributors.</p>
              </div>
              <button class="w-fit px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all">Manage</button>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-amber-600/10 rounded-full blur-[80px] group-hover:bg-amber-600/20 transition-all"></div>
            <img src="/hr-team.jpg" class="absolute right-[-10%] bottom-[-10%] w-72 h-72 object-cover rounded-full opacity-25 group-hover:opacity-50 group-hover:scale-110 transition-all duration-700 mix-blend-lighten" />
          </div>

          <div class="bg-slate-900/40 rounded-[45px] p-10 relative overflow-hidden group border border-slate-700/50 hover:border-green-500/30 transition-colors">
            <div class="relative z-10 h-full flex flex-col justify-between min-h-[160px]">
              <div>
                <span class="text-[10px] font-black text-slate-600 tracking-widest uppercase">Finance Module</span>
                <h4 class="text-3xl font-bold mt-2 text-white">Financial Management</h4>
                <p class="text-sm text-slate-500 mt-4 leading-relaxed max-w-[180px]">Track sales, expenses, revenue and profitability analytics.</p>
              </div>
              <button class="w-fit px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all">View</button>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-green-600/10 rounded-full blur-[80px] group-hover:bg-green-600/20 transition-all"></div>
            <img src="/finance-calculator.jpg" class="absolute right-[-5%] bottom-[-5%] w-72 h-72 object-cover rounded-full opacity-25 group-hover:opacity-50 group-hover:scale-110 transition-all duration-700 mix-blend-lighten" />
          </div>

          <div class="bg-slate-900/40 rounded-[45px] p-10 relative overflow-hidden group border border-slate-700/50 hover:border-pink-500/30 transition-colors">
            <div class="relative z-10 h-full flex flex-col justify-between min-h-[160px]">
              <div>
                <span class="text-[10px] font-black text-slate-600 tracking-widest uppercase">E-Commerce</span>
                <h4 class="text-3xl font-bold mt-2 text-white">Online Store</h4>
                <p class="text-sm text-slate-500 mt-4 leading-relaxed max-w-[180px]">Browse products, check availability, and place orders online.</p>
              </div>
              <button class="w-fit px-6 py-2.5 bg-white/5 rounded-full text-[10px] font-black uppercase tracking-widest border border-white/10 hover:bg-white hover:text-black transition-all">Shop</button>
            </div>
            <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-pink-600/10 rounded-full blur-[80px] group-hover:bg-pink-600/20 transition-all"></div>
            <img src="/ecommerce-store.jpg" class="absolute right-[-10%] bottom-[-10%] w-72 h-72 object-cover rounded-full opacity-25 group-hover:opacity-50 group-hover:scale-110 transition-all duration-700 mix-blend-lighten" />
          </div>

        </div>
      </section>

      <div class="bg-slate-950/50 border-t border-slate-700/30 p-10 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div v-for="(feature, i) in systemFeatures" :key="i" class="flex flex-col items-center group cursor-pointer">
          <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border-2 border-slate-700 group-hover:border-cyan-400 flex items-center justify-center mb-3 transition-all group-hover:scale-110 group-hover:shadow-lg group-hover:shadow-cyan-500/20">
            <component :is="feature.icon" class="w-8 h-8 text-slate-400 group-hover:text-cyan-400 transition-colors" />
          </div>
          <span class="text-[9px] font-black uppercase tracking-widest text-center text-slate-500 group-hover:text-cyan-400 transition-colors">{{ feature.name }}</span>
        </div>
      </div>
      
    </div>
  </main>
</template>

<script setup>
import { h, onMounted, onUnmounted } from 'vue';

// Dynamically load the Spline viewer module and patch scroll hijack
onMounted(() => {
  if (!document.querySelector('script[src="https://unpkg.com/@splinetool/viewer@1.12.81/build/spline-viewer.js"]')) {
    const script = document.createElement('script');
    script.type = 'module';
    script.src = 'https://unpkg.com/@splinetool/viewer@1.12.81/build/spline-viewer.js';
    document.head.appendChild(script);
  }

  // Force scroll when hovering over the Spline Viewer
  const handleScroll = (e) => {
    // If the event target is the spline viewer or canvas inside it
    if (e.target.closest('spline-viewer')) {
      window.scrollBy({
        top: e.deltaY,
        behavior: 'auto'
      });
    }
  };

  // Add passive listener to window to bypass Spline's preventDefault
  window.addEventListener('wheel', handleScroll, { passive: true });

  onUnmounted(() => {
    window.removeEventListener('wheel', handleScroll);
  });
});

// SVG Icon Components
const InventoryIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
      })
    ]);
  }
};

const DSSIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
      })
    ]);
  }
};

const VirtualMixIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'
      })
    ]);
  }
};

const MobileIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'
      })
    ]);
  }
};

const HRIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
      })
    ]);
  }
};

const FinanceIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
      })
    ]);
  }
};

const SCMIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4'
      })
    ]);
  }
};

const CRMIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5'
      })
    ]);
  }
};

const ChatIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'
      })
    ]);
  }
};

const EcommerceIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'
      })
    ]);
  }
};

const ProcurementIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'
      })
    ]);
  }
};

const LogisticsIcon = {
  render() {
    return h('svg', {
      class: 'w-8 h-8',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24'
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0zM9 17h10M5 17h2m3-4h6m-9-4h6m-6 4h2M5 9h14M3 3l2 2m0 0l-2 2m2-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z'
      })
    ]);
  }
};

const coreModules = [
  { short: 'IMS', name: 'Integrated Management System' },
  { short: 'DSS', name: 'Decision Support System' },
  { short: 'VR', name: 'Virtual Color Mixing' },
  { short: 'APP', name: 'Mobile Application' },
  { short: 'HR', name: 'Human Resources' },
  { short: 'FIN', name: 'Finance Module' },
  { short: 'SCM', name: 'Supply Chain' },
  { short: 'CRM', name: 'Customer Relations' }
];

const systemFeatures = [
  { name: 'Inventory Management', icon: InventoryIcon },
  { name: 'Decision Support System', icon: DSSIcon },
  { name: 'Virtual Color Mixing', icon: VirtualMixIcon },
  { name: 'Mobile Application', icon: MobileIcon },
  { name: 'Employee Management', icon: HRIcon },
  { name: 'Finance Module', icon: FinanceIcon },
  { name: 'Supply Chain Mgmt', icon: SCMIcon },
  { name: 'Customer Relations', icon: CRMIcon },
  { name: 'Chat System', icon: ChatIcon },
  { name: 'E-Commerce', icon: EcommerceIcon },
  { name: 'Material Procurement', icon: ProcurementIcon },
  { name: 'Logistics', icon: LogisticsIcon }
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');

main {
  font-family: 'Plus Jakarta Sans', sans-serif;
}

/* Custom Scrollbar to match Color Lab */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-track {
  background: #020617;
}
::-webkit-scrollbar-thumb {
  background: #1e293b;
  border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover {
  background: #334155;
}

@media (max-width: 360px) {
  h1 {
    font-size: 1.5rem;
    line-height: 1.2;
  }
}
</style>