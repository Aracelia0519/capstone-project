<template>
  <div class="p-4 md:p-8 text-slate-900 w-full">
    <div class="max-w-[1400px] mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-black tracking-tight flex items-center gap-3">
            <ShieldCheck class="w-8 h-8 text-indigo-600" />
            System Audit Logs
          </h1>
          <p class="text-slate-500 mt-1">Monitor, analyze, and track authentication activities across the platform.</p>
        </div>
        <button @click="refreshData" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold hover:bg-slate-50 shadow-sm transition-all">
          <RefreshCw :class="['w-4 h-4', isLoading ? 'animate-spin text-indigo-600' : '']" />
          Refresh Data
        </button>
      </div>

      <!-- Statistics KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
          <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center shrink-0">
            <FileText class="w-7 h-7" />
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Logs</p>
            <h3 class="text-3xl font-black text-slate-900">{{ stats.total }}</h3>
          </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
          <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center shrink-0">
            <CheckCircle2 class="w-7 h-7" />
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Successful Logins</p>
            <h3 class="text-3xl font-black text-slate-900">{{ stats.success }}</h3>
          </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
          <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center shrink-0">
            <AlertCircle class="w-7 h-7" />
          </div>
          <div>
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Failed Attempts</p>
            <h3 class="text-3xl font-black text-slate-900">{{ stats.failed }}</h3>
          </div>
        </div>
      </div>

      <!-- DSS Live Ranking & Threat Analysis Panel -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
        <div class="flex justify-between items-start mb-6 shrink-0">
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                  <Activity class="w-4 h-4 text-indigo-600" />
                  DSS Security Analysis & Threat Detection
                </h3>
                <p class="text-xs text-slate-500 mt-1">Real-time dynamic threat scaling based on network failure averages and sequence anomalies.</p>
            </div>
            <div class="group relative cursor-help z-20">
                <Info class="w-4 h-4 text-slate-400 hover:text-indigo-500 transition-colors" />
                <div class="absolute right-0 w-80 p-4 bg-slate-800 text-white text-xs rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10 pointer-events-none mt-2">
                    <strong class="text-sm block mb-2 text-indigo-300">Dynamic Decision Support System (DSS)</strong>
                    <p class="mb-2">Calculates the current average failure rate across all users to establish a dynamic baseline.</p>
                    <ul class="space-y-1.5">
                      <li><span class="text-rose-400 font-bold">Critical (Breach):</span> Successful login immediately following a critical sequence of failures.</li>
                      <li><span class="text-rose-400 font-bold">Critical:</span> Consecutive failures exceeding {{ dssInsights.criticalThreshold }} (Dynamic Threshold).</li>
                      <li><span class="text-orange-400 font-bold">High:</span> Consecutive failures exceeding {{ dssInsights.highThreshold }} (Dynamic Threshold).</li>
                      <li><span class="text-amber-400 font-bold">Medium:</span> Successful logins at unusual hours (12 AM - 5 AM).</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- DSS Analytics -->
            <div class="lg:col-span-1 border-b lg:border-b-0 lg:border-r border-slate-100 pb-6 lg:pb-0 lg:pr-6 flex flex-col justify-center">
                <div class="space-y-4">
                  <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase">Avg Network Failures</span>
                    <span class="text-lg font-black text-slate-900">{{ dssInsights.avgFailures.toFixed(1) }} <span class="text-xs font-normal text-slate-500">per user</span></span>
                  </div>
                  <div class="bg-orange-50 p-4 rounded-xl border border-orange-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-orange-600 uppercase">High Threshold</span>
                    <span class="text-lg font-black text-orange-700">>= {{ dssInsights.highThreshold }}</span>
                  </div>
                  <div class="bg-rose-50 p-4 rounded-xl border border-rose-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-600 uppercase">Critical Threshold</span>
                    <span class="text-lg font-black text-rose-700">>= {{ dssInsights.criticalThreshold }}</span>
                  </div>
                </div>
            </div>

            <!-- DSS Live Threat List -->
            <div class="lg:col-span-2 h-56 flex flex-col">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Identified High-Risk Accounts</h4>
                    <button v-if="dssInsights.allAtRiskAccounts.length > 0" @click="isAtRiskModalOpen = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 transition-colors">
                        View Accounts <ChevronRight class="w-3 h-3" />
                    </button>
                </div>
                
                <div class="overflow-y-auto pr-2 custom-scrollbar flex-1">
                  <div v-if="dssInsights.atRiskAccounts.length === 0" class="flex flex-col items-center justify-center text-slate-400 h-32 w-full">
                      <ShieldCheck class="w-8 h-8 mb-2 opacity-50" />
                      <span class="text-sm">No critical threats detected.</span>
                  </div>

                  <div v-else class="space-y-2">
                    <div v-for="account in dssInsights.atRiskAccounts" :key="account.email" 
                      class="flex justify-between items-center p-3 rounded-xl border"
                      :class="account.threatLevel.includes('Critical') ? 'bg-rose-50 border-rose-200' : 'bg-orange-50 border-orange-200'">
                    
                      <div>
                        <div class="font-bold text-sm" :class="account.threatLevel.includes('Critical') ? 'text-rose-900' : 'text-orange-900'">
                          {{ account.email }}
                        </div>
                        <div class="text-xs font-medium mt-0.5" :class="account.threatLevel.includes('Critical') ? 'text-rose-600' : 'text-orange-600'">
                          <span v-if="account.threatLevel.includes('Breach')">⚠️ Critical: Potential Account Compromise</span>
                          <span v-else-if="account.threatLevel.includes('Critical')">⚠️ Critical: Sustained Brute Force Attack</span>
                          <span v-else>⚠️ High Risk: Continuous Brute Force Targeting</span>
                        </div>
                      </div>

                      <div class="text-right shrink-0">
                        <div class="text-[10px] uppercase font-bold text-slate-500 mb-0.5">Consecutive Failures</div>
                        <div class="text-lg font-black" :class="account.threatLevel.includes('Critical') ? 'text-rose-700' : 'text-orange-700'">
                          {{ account.maxConsecutiveFailures }}
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
            </div>
        </div>
      </div>

      <!-- Custom SVG Analytics Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Chart 1: Login Trends (SVG Line Chart) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col h-72">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1">Login Trends</h3>
          <p class="text-xs text-slate-500 mb-6">Daily authentication attempts (Last 7 Days)</p>
          
          <div class="relative w-full h-full flex items-end mt-auto pt-4 group">
            <svg class="w-full h-full overflow-visible" viewBox="0 0 100 40" preserveAspectRatio="none">
              <line x1="0" y1="10" x2="100" y2="10" stroke="#f8fafc" stroke-width="0.5" />
              <line x1="0" y1="20" x2="100" y2="20" stroke="#f8fafc" stroke-width="0.5" />
              <line x1="0" y1="30" x2="100" y2="30" stroke="#f8fafc" stroke-width="0.5" />
              
              <path :d="trendData.fillPath" fill="rgba(79, 70, 229, 0.1)" />
              <path :d="trendData.linePath" fill="none" stroke="#4f46e5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
              
              <circle 
                v-for="(pt, i) in trendData.points" 
                :key="i"
                :cx="pt.x" :cy="pt.y" r="2" fill="#fff" stroke="#4f46e5" stroke-width="1.5" 
                class="transition-all duration-300 opacity-0 group-hover:opacity-100 hover:r-3 cursor-pointer"
              >
                <title>{{ pt.label }}: {{ pt.val }} Logins</title>
              </circle>
            </svg>
            <div class="absolute -bottom-6 left-0 right-0 flex justify-between text-[10px] font-medium text-slate-400">
              <span v-for="pt in trendData.points" :key="pt.label">{{ pt.label }}</span>
            </div>
          </div>
        </div>

        <!-- Chart 2: Success Rate (SVG Doughnut Chart) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col h-72">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1">Status Overview</h3>
          <p class="text-xs text-slate-500 mb-6">Success vs Failed distributions</p>
          
          <div class="relative w-full h-full flex flex-col items-center justify-center mt-auto">
            <div class="relative w-36 h-36 flex items-center justify-center">
              <svg viewBox="0 0 36 36" class="w-full h-full transform -rotate-90">
                <circle cx="18" cy="18" r="15.91549430918954" fill="transparent" stroke="#f1f5f9" stroke-width="4"></circle>
                <circle cx="18" cy="18" r="15.91549430918954" fill="transparent" stroke="#10b981" stroke-width="4"
                        stroke-dasharray="100"
                        :stroke-dashoffset="100 - doughnutData.successPct"
                        stroke-linecap="round"
                        class="transition-all duration-1000 ease-out"></circle>
              </svg>
              <div class="absolute flex flex-col items-center">
                <span class="text-2xl font-black text-slate-900">{{ Math.round(doughnutData.successPct) }}%</span>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Success</span>
              </div>
            </div>
            
            <div class="flex items-center justify-center gap-6 mt-6 w-full">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-slate-600">Success ({{ doughnutData.success }})</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-slate-200"></span>
                <span class="text-xs font-bold text-slate-600">Failed ({{ doughnutData.failed }})</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart 3: Roles Distribution (CSS Horizontal Bar Chart) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col h-72">
          <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-1">Activity by Role</h3>
          <p class="text-xs text-slate-500 mb-6">Logins distributed by user role</p>
          
          <div class="flex flex-col justify-end h-full gap-4">
            <div v-if="barData.length === 0" class="text-xs text-center text-slate-400 py-4 flex flex-col items-center gap-2">
              <Activity class="w-8 h-8 opacity-20" />
              No data available
            </div>
            <div v-for="item in barData" :key="item.label" class="w-full group">
              <div class="flex justify-between text-xs mb-1.5">
                <span class="text-slate-700 font-bold capitalize truncate pr-2">{{ item.label }}</span>
                <span class="text-slate-500 font-medium group-hover:text-indigo-600 transition-colors">{{ item.value }}</span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                <div class="bg-indigo-500 h-2 rounded-full transition-all duration-1000 ease-out" 
                     :style="{ width: `${item.percentage}%` }"></div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Main Logs Table with DSS Indicators -->
      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <!-- Table Header & Filters -->
        <div class="p-6 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
          <h2 class="text-lg font-black text-slate-900 shrink-0">Authentication Records</h2>
          
          <!-- Filter Bar -->
          <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
            <div class="relative w-full sm:w-64">
              <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="searchQuery" type="text" placeholder="Search user or IP..." class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl pl-9 pr-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
            </div>

            <select v-model="roleFilter" class="bg-slate-50 border border-slate-200 text-sm font-medium text-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
              <option value="all">All Roles</option>
              <option v-for="role in uniqueRoles" :key="role" :value="role">
                {{ role.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
              </option>
            </select>

            <select v-model="riskFilter" class="bg-slate-50 border border-slate-200 text-sm font-medium text-slate-600 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
              <option value="all">All DSS Risks</option>
              <option value="Critical">Critical Risk</option>
              <option value="High">High Risk</option>
              <option value="Medium">Medium Risk</option>
              <option value="Safe">Safe</option>
            </select>
          </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50/80 text-slate-500 text-[11px] uppercase tracking-wider font-bold border-b border-slate-200">
                <th class="px-6 py-4">DSS Risk Level</th>
                <th class="px-6 py-4">Date & Time</th>
                <th class="px-6 py-4">User Details</th>
                <th class="px-6 py-4 text-center">Status</th>
                <th class="px-6 py-4">Browser & Device</th>
                <th class="px-6 py-4">DSS Analysis</th>
              </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-100">
              <tr v-if="isLoading" class="bg-white">
                <td colspan="6" class="py-12 text-center text-slate-400">
                  <Loader2 class="w-8 h-8 animate-spin mx-auto text-indigo-500 mb-2" />
                  Analyzing records...
                </td>
              </tr>
              <tr v-else-if="paginatedLogs.length === 0" class="bg-white">
                <td colspan="6" class="py-12 text-center text-slate-400">
                  <FileX class="w-10 h-10 mx-auto mb-3 opacity-30" />
                  No audit logs found matching your criteria.
                </td>
              </tr>
              <tr v-else v-for="log in paginatedLogs" :key="log.id" class="bg-white hover:bg-slate-50 transition-colors group">
                
                <!-- DSS Risk Level -->
                <td class="px-6 py-4">
                  <span :class="[
                      'text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-md inline-flex items-center gap-1.5 border',
                      log.dss_risk === 'Critical' ? 'bg-rose-50 text-rose-700 border-rose-200' : 
                      log.dss_risk === 'High' ? 'bg-orange-50 text-orange-700 border-orange-200' :
                      log.dss_risk === 'Medium' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                      'bg-slate-100 text-slate-500 border-slate-200'
                    ]">
                    <span class="w-1.5 h-1.5 rounded-full" :class="
                      log.dss_risk === 'Critical' ? 'bg-rose-500' : 
                      log.dss_risk === 'High' ? 'bg-orange-500' :
                      log.dss_risk === 'Medium' ? 'bg-amber-500' : 'bg-slate-400'"></span>
                    {{ log.dss_risk }}
                  </span>
                </td>

                <!-- Date & Time -->
                <td class="px-6 py-4 w-44">
                  <div class="flex items-center gap-2 text-[11px] font-mono text-slate-500">
                    <Clock class="w-3.5 h-3.5 text-indigo-400 shrink-0" />
                    {{ formatDate(log.created_at) }}
                  </div>
                </td>

                <!-- User Info & Role -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs uppercase shrink-0"
                         :class="log.role === 'client' ? 'bg-blue-100 text-blue-600' : 'bg-indigo-100 text-indigo-600'">
                      {{ log.Fullname ? log.Fullname.charAt(0) : 'U' }}
                    </div>
                    <div>
                      <div class="font-bold text-slate-900 text-xs">{{ log.Fullname || 'Unknown User' }}</div>
                      <div class="text-[10px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                        {{ log.email }}
                        <span v-if="log.role" class="px-1.5 py-0.5 bg-slate-100 rounded border capitalize">{{ log.role.replace(/_/g, ' ') }}</span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Auth Status -->
                <td class="px-6 py-4 text-center">
                  <span :class="[
                      'text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md inline-flex items-center',
                      log.status === 'Success' ? 'text-emerald-600 bg-emerald-50' : 'text-rose-600 bg-rose-50'
                    ]">
                    {{ log.status }}
                  </span>
                </td>

                <!-- Browser/Device -->
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2 text-[10px] text-slate-500 max-w-[150px] truncate" :title="log.browser">
                    <Globe class="w-3 h-3 text-slate-400 shrink-0" />
                    <span class="truncate">{{ log.browser || 'Unknown' }}</span>
                  </div>
                </td>

                <!-- Notes / DSS Reason -->
                <td class="px-6 py-4">
                  <div class="text-[11px] leading-relaxed max-w-xs" :class="[
                    log.dss_risk === 'Critical' ? 'text-rose-600 font-bold' : 
                    log.dss_risk === 'High' ? 'text-orange-600 font-semibold' :
                    log.dss_risk === 'Medium' ? 'text-amber-600 font-medium' : 'text-slate-400'
                  ]">
                    {{ log.dss_reason }}
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Footer -->
        <div v-if="!isLoading && filteredLogs.length > 0" class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
          <span class="text-sm text-slate-500 font-medium">
            Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredLogs.length) }} of {{ filteredLogs.length }} entries
          </span>
          <div class="flex items-center gap-4">
            <button @click="prevPage" :disabled="currentPage === 1" class="px-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-xl hover:bg-slate-50 text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
              Previous
            </button>
            <span class="text-sm font-bold text-slate-700">Page {{ currentPage }} of {{ totalPages || 1 }}</span>
            <button @click="nextPage" :disabled="currentPage === totalPages || totalPages === 0" class="px-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-xl hover:bg-slate-50 text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
              Next
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- At-Risk Accounts Modal -->
    <Dialog :open="isAtRiskModalOpen" @update:open="isAtRiskModalOpen = $event">
      <DialogContent class="sm:max-w-[700px] p-0 overflow-hidden bg-white border-none shadow-2xl rounded-2xl">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
          <div>
            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
              <AlertCircle class="w-5 h-5 text-rose-500" />
              All Identified High-Risk Accounts
            </h2>
            <p class="text-xs text-slate-500 mt-1">Review potentially compromised or targeted accounts.</p>
          </div>
          
        </div>
        
        <div class="max-h-[60vh] overflow-y-auto p-6 space-y-3 custom-scrollbar">
          <div v-if="dssInsights.allAtRiskAccounts.length === 0" class="flex flex-col items-center justify-center text-slate-400 py-10 w-full">
            <ShieldCheck class="w-10 h-10 mb-3 opacity-50 text-emerald-500" />
            <span class="text-sm font-medium text-slate-600">No high-risk accounts identified in the current logs.</span>
          </div>

          <div v-else v-for="account in dssInsights.allAtRiskAccounts" :key="account.email" 
            class="flex flex-col sm:flex-row justify-between sm:items-center p-4 rounded-xl border transition-all"
            :class="account.threatLevel.includes('Critical') ? 'bg-rose-50 border-rose-200' : 'bg-orange-50 border-orange-200'">
            
            <div class="mb-3 sm:mb-0">
              <div class="font-bold text-sm" :class="account.threatLevel.includes('Critical') ? 'text-rose-900' : 'text-orange-900'">
                {{ account.email }}
              </div>
              <div class="text-xs font-medium mt-1" :class="account.threatLevel.includes('Critical') ? 'text-rose-600' : 'text-orange-600'">
                <span v-if="account.threatLevel.includes('Breach')">⚠️ Critical: Potential Account Compromise</span>
                <span v-else-if="account.threatLevel.includes('Critical')">⚠️ Critical: Sustained Brute Force Attack</span>
                <span v-else>⚠️ High Risk: Continuous Brute Force Targeting</span>
              </div>
              <div class="text-[10px] uppercase font-bold mt-2" :class="account.threatLevel.includes('Critical') ? 'text-rose-400' : 'text-orange-400'">
                Consecutive Failures: <span class="text-sm text-black">{{ account.maxConsecutiveFailures }}</span>
              </div>
            </div>

            <div class="shrink-0 flex items-center justify-end">
              <button 
                @click="notifyUser(account.email)" 
                :disabled="isNotifying[account.email]"
                class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed"
                :class="account.threatLevel.includes('Critical') ? 'bg-rose-600 hover:bg-rose-700 text-white' : 'bg-orange-500 hover:bg-orange-600 text-white'"
              >
                <Loader2 v-if="isNotifying[account.email]" class="w-4 h-4 animate-spin" />
                <Bell v-else class="w-4 h-4" />
                {{ isNotifying[account.email] ? 'Sending...' : 'Send Security Alert' }}
              </button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { 
  ShieldCheck, RefreshCw, FileText, AlertCircle, CheckCircle2, 
  Search, Clock, Globe, Loader2, FileX, Activity, Info, ChevronRight, X, Bell
} from 'lucide-vue-next'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { toast } from 'vue-sonner'
import axios from '@/utils/axios'
import echo from '@/utils/websocket'

// --- State ---
const rawLogs = ref([])
const isLoading = ref(true)
const searchQuery = ref('')
const roleFilter = ref('all')
const riskFilter = ref('all')

const isAtRiskModalOpen = ref(false)
const isNotifying = ref({})

// --- Pagination State ---
const currentPage = ref(1)
const itemsPerPage = ref(10)



// Reset to page 1 when any filter changes
watch([searchQuery, roleFilter, riskFilter], () => {
  currentPage.value = 1
})

// --- Fetch & WebSockets ---
const fetchLogs = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/admin/login-logs') // Using the API endpoint from api.php
    if (response.data.status === 'success') {
      rawLogs.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch audit logs:', error)
  } finally {
    isLoading.value = false
  }
}

const refreshData = () => {
  fetchLogs()
}

onMounted(() => {
  fetchLogs()

  if (echo) {
    echo.private('admin.login-logs')
      .listen('.new-login-log', (e) => {
        if (e.log) rawLogs.value.unshift(e.log)
      })
  }
})

onUnmounted(() => {
  if (echo) {
    echo.leaveChannel('admin.login-logs')
  }
})

// --- Notification Logic ---
const notifyUser = async (email) => {
  isNotifying.value[email] = true
  try {
    const res = await axios.post('/admin/login-logs/notify', { email })
    if (res.data.status === 'success') {
      toast.success('Notification Sent', { description: `Security alert sent regarding ${email}.` })
      // Track that a notification was successfully sent
      sentTodayMap.value[email] = (sentTodayMap.value[email] || 0) + 1
    }
  } catch (error) {
    toast.error('Notification Failed', { description: error.response?.data?.message || 'Unable to send security alert.' })
  } finally {
    isNotifying.value[email] = false
  }
}

// ==========================================
// DYNAMIC DECISION SUPPORT SYSTEM (DSS)
// ==========================================
const dssInsights = computed(() => {
  if (!rawLogs.value.length) {
    return { evaluatedLogs: [], avgFailures: 0, highThreshold: 3, criticalThreshold: 5, atRiskAccounts: [], allAtRiskAccounts: [] }
  }

  const emailStats = {}
  let totalFails = 0
  let usersWithFails = 0

  rawLogs.value.forEach(log => {
    if (!emailStats[log.email]) emailStats[log.email] = { fails: 0 }
    if (log.status === 'Failed') {
      emailStats[log.email].fails++
      totalFails++
    }
  })

  Object.values(emailStats).forEach(s => { if (s.fails > 0) usersWithFails++ })

  const avgFailures = usersWithFails > 0 ? (totalFails / usersWithFails) : 0
  const highThreshold = Math.min(10, Math.max(3, Math.ceil(avgFailures * 1.5)))
  const criticalThreshold = Math.min(15, Math.max(5, Math.ceil(avgFailures * 2.5)))

  const sortedLogs = [...rawLogs.value].sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
  
  const sequenceTracker = {} 
  const atRiskMap = {} 

  const evaluatedLogs = sortedLogs.map(log => {
    if (!sequenceTracker[log.email]) sequenceTracker[log.email] = 0

    let risk = 'Safe'
    let reason = ''
    const hour = new Date(log.created_at).getHours()

    if (log.status === 'Failed') {
      sequenceTracker[log.email]++
      const consecFails = sequenceTracker[log.email]

      if (consecFails >= criticalThreshold) {
        risk = 'Critical'
        reason = `Sustained Brute Force Attack detected (${consecFails} consecutive failures)`
      } else if (consecFails >= highThreshold) {
        risk = 'High'
        reason = `Suspiciously high consecutive failures (${consecFails})`
      } else {
        risk = 'Low'
        reason = log.failure_reason || 'Standard authentication failure'
      }

      if (consecFails >= highThreshold) {
        const existing = atRiskMap[log.email]
        if (!existing || (consecFails > existing.maxConsecutiveFailures && !existing.threatLevel.includes('Breach'))) {
          atRiskMap[log.email] = { 
            email: log.email, 
            maxConsecutiveFailures: consecFails,
            threatLevel: consecFails >= criticalThreshold ? 'Critical' : 'High'
          }
        }
      }

    } else if (log.status === 'Success') {
      const pastFails = sequenceTracker[log.email]

      if (pastFails >= highThreshold) {
        risk = 'Critical'
        reason = `CRITICAL: Successful login immediately following ${pastFails} failed attempts. Possible Account Compromise.`
        
        atRiskMap[log.email] = { 
          email: log.email, 
          maxConsecutiveFailures: pastFails,
          threatLevel: 'Critical (Breach Risk)'
        }
      } 
      else if (hour >= 0 && hour <= 5) {
        risk = 'Medium'
        reason = 'Successful login during unusual hours (12:00 AM - 5:00 AM)'
      } 
      else {
        risk = 'Safe'
        reason = 'Standard authorized login'
      }

      sequenceTracker[log.email] = 0
    }

    return { ...log, dss_risk: risk, dss_reason: reason }
  })

  const allAtRiskAccounts = Object.values(atRiskMap).sort((a, b) => b.maxConsecutiveFailures - a.maxConsecutiveFailures)
  const atRiskAccounts = allAtRiskAccounts.slice(0, 5)

  return { 
    evaluatedLogs: evaluatedLogs.reverse(), 
    avgFailures, 
    highThreshold, 
    criticalThreshold,
    atRiskAccounts,
    allAtRiskAccounts
  }
})

// --- Computed Filters based on Evaluated DSS Logs ---
const uniqueRoles = computed(() => {
  const roles = new Set(dssInsights.value.evaluatedLogs.map(l => l.role).filter(Boolean))
  return Array.from(roles).sort()
})

const filteredLogs = computed(() => {
  return dssInsights.value.evaluatedLogs.filter(log => {
    const matchRole = roleFilter.value === 'all' || log.role === roleFilter.value
    const matchRisk = riskFilter.value === 'all' || log.dss_risk === riskFilter.value
    
    const query = searchQuery.value.toLowerCase().trim()
    const matchSearch = query === '' ||
      (log.Fullname && log.Fullname.toLowerCase().includes(query)) ||
      (log.email && log.email.toLowerCase().includes(query))

    return matchRole && matchRisk && matchSearch
  })
})

const totalPages = computed(() => Math.ceil(filteredLogs.value.length / itemsPerPage.value))

const paginatedLogs = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredLogs.value.slice(start, end)
})

const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }

// --- Formatting ---
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const d = new Date(dateString)
  return `${d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })} ${d.toLocaleTimeString('en-US', { hour: '2-digit', minute:'2-digit' })}`
}

// ==========================================
// CUSTOM CHART LOGIC (NO EXTERNAL LIBRARIES)
// ==========================================

const stats = computed(() => {
  const total = rawLogs.value.length
  const success = rawLogs.value.filter(l => l.status === 'Success').length
  const failed = rawLogs.value.filter(l => l.status === 'Failed').length
  return { total, success, failed }
})

// 1. Line Chart Data
const trendData = computed(() => {
  const dates = [...Array(7)].map((_, i) => {
    const d = new Date()
    d.setDate(d.getDate() - i)
    return d.toISOString().split('T')[0]
  }).reverse()

  const counts = dates.map(date => {
    return rawLogs.value.filter(log => log.created_at && log.created_at.startsWith(date)).length
  })

  const maxVal = Math.max(...counts, 1)
  const width = 100
  const height = 40

  const points = counts.map((val, idx) => {
    const x = (idx / (counts.length - 1)) * width
    const y = height - (val / maxVal) * (height - 5)
    return { x, y, val, label: new Date(dates[idx]).toLocaleDateString('en-US', { weekday: 'short' }) }
  })

  const linePath = 'M ' + points.map(p => `${p.x} ${p.y}`).join(' L ')
  const fillPath = `${linePath} L 100 40 L 0 40 Z`

  return { points, linePath, fillPath }
})

// 2. Doughnut Chart Data
const doughnutData = computed(() => {
  const total = rawLogs.value.length || 1
  return {
    success: stats.value.success,
    failed: stats.value.failed,
    successPct: (stats.value.success / total) * 100
  }
})

// 3. Bar Chart Data
const barData = computed(() => {
  const roleCounts = {}
  rawLogs.value.forEach(log => {
    const r = log.role ? log.role.replace(/_/g, ' ') : 'Unknown'
    roleCounts[r] = (roleCounts[r] || 0) + 1
  })

  const sortedRoles = Object.entries(roleCounts).sort((a, b) => b[1] - a[1]).slice(0, 5)
  const maxCount = sortedRoles.length ? sortedRoles[0][1] : 1

  return sortedRoles.map(([label, value]) => ({
    label, value, percentage: (value / maxCount) * 100
  }))
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  height: 6px;
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 20px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}
</style>