<template>
  <div class="admin-tech-reports min-h-screen p-4 md:p-8 text-slate-900">
    <div class="max-w-[1400px] mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-black tracking-tight flex items-center gap-3">
            <Wrench class="w-8 h-8 text-indigo-600" />
            System Technical Reports
          </h1>
          <p class="text-slate-500 mt-1">Monitor, analyze, and resolve technical issues reported by users across the platform.</p>
        </div>
        <button @click="refreshData" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold hover:bg-slate-50 shadow-sm transition-all">
            <RefreshCw :class="['w-4 h-4', isLoading ? 'animate-spin text-indigo-600' : '']" />
            Refresh Data
        </button>
      </div>

      <!-- Statistics KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                <FileText class="w-7 h-7" />
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Reports</p>
                <h3 class="text-3xl font-black text-slate-900">{{ stats.total }}</h3>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center">
                <AlertCircle class="w-7 h-7" />
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unresolved Issues</p>
                <h3 class="text-3xl font-black text-slate-900">{{ stats.pending + stats.reviewed }}</h3>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                <CheckCheck class="w-7 h-7" />
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Resolved Issues</p>
                <h3 class="text-3xl font-black text-slate-900">{{ stats.resolved }}</h3>
            </div>
        </div>
      </div>

      <!-- DSS Live Ranking & Priority Distribution (Own Row) -->
      <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
          <div class="flex justify-between items-start mb-6 shrink-0">
              <div>
                  <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">DSS Priority Distribution & Live Ranking</h3>
                  <p class="text-xs text-slate-500 mt-1">Real-time priority scaling based on the volume of unresolved issues (Pending & Reviewed).</p>
              </div>
              <div class="group relative cursor-help z-20">
                  <Info class="w-4 h-4 text-slate-400 hover:text-indigo-500 transition-colors" />
                  <div class="absolute right-0 w-72 p-4 bg-slate-800 text-white text-xs rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10 pointer-events-none mt-2">
                      <strong class="text-sm block mb-1 text-indigo-300">Dynamic Volume Scaling (DSS)</strong>
                      Automatically scales priority based on live traffic, ensuring flexibility as data grows.<br><br>
                      <span class="text-red-400 font-bold">Critical:</span> Top 25% of highest volume<br>
                      <span class="text-orange-400 font-bold">High:</span> Top 50% of highest volume<br>
                      <span class="text-yellow-400 font-bold">Medium:</span> Top 75% of highest volume<br>
                      <span class="text-blue-400 font-bold">Low:</span> Below 25% or isolated reports
                  </div>
              </div>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- DSS Priority Chart -->
              <div class="h-64 flex justify-center lg:border-r border-slate-100 lg:pr-8">
                  <Pie v-if="reportsWithDSS.length > 0" :data="priorityChartData" :options="pieOptions" class="h-full w-full" />
                  <div v-else class="flex flex-col items-center justify-center text-slate-400 h-full w-full">
                      <Activity class="w-10 h-10 mb-2 opacity-20" />
                      <span class="text-sm">Not enough data to display.</span>
                  </div>
              </div>

              <!-- DSS Live Ranking List -->
              <div class="h-64 overflow-y-auto pr-2 custom-scrollbar space-y-4">
                  <!-- Critical -->
                  <div v-if="getCategoriesByPriority('Critical').length">
                      <h4 class="text-[10px] font-black uppercase tracking-widest text-red-500 mb-2 flex items-center gap-1"><AlertCircle class="w-3 h-3"/> Critical</h4>
                      <div class="space-y-1.5">
                          <div v-for="cat in getCategoriesByPriority('Critical')" :key="cat.name" class="flex justify-between items-center bg-red-50/50 p-2 rounded-lg border border-red-100">
                              <span class="text-xs font-bold text-slate-700 capitalize">{{ cat.name.replace('_', ' ') }}</span>
                              <span class="text-[10px] font-bold text-red-600 bg-red-100 px-2 py-0.5 rounded-full">{{ cat.count }} Unresolved</span>
                          </div>
                      </div>
                  </div>
                  <!-- High -->
                  <div v-if="getCategoriesByPriority('High').length">
                      <h4 class="text-[10px] font-black uppercase tracking-widest text-orange-500 mb-2 mt-4 flex items-center gap-1"><Activity class="w-3 h-3"/> High</h4>
                      <div class="space-y-1.5">
                          <div v-for="cat in getCategoriesByPriority('High')" :key="cat.name" class="flex justify-between items-center bg-orange-50/50 p-2 rounded-lg border border-orange-100">
                              <span class="text-xs font-bold text-slate-700 capitalize">{{ cat.name.replace('_', ' ') }}</span>
                              <span class="text-[10px] font-bold text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full">{{ cat.count }} Unresolved</span>
                          </div>
                      </div>
                  </div>
                  <!-- Medium -->
                  <div v-if="getCategoriesByPriority('Medium').length">
                      <h4 class="text-[10px] font-black uppercase tracking-widest text-yellow-600 mb-2 mt-4 flex items-center gap-1"><CheckCircle2 class="w-3 h-3"/> Medium</h4>
                      <div class="space-y-1.5">
                          <div v-for="cat in getCategoriesByPriority('Medium')" :key="cat.name" class="flex justify-between items-center bg-yellow-50/50 p-2 rounded-lg border border-yellow-100">
                              <span class="text-xs font-bold text-slate-700 capitalize">{{ cat.name.replace('_', ' ') }}</span>
                              <span class="text-[10px] font-bold text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded-full">{{ cat.count }} Unresolved</span>
                          </div>
                      </div>
                  </div>
                  <!-- Low -->
                  <div v-if="getCategoriesByPriority('Low').length">
                      <h4 class="text-[10px] font-black uppercase tracking-widest text-blue-500 mb-2 mt-4 flex items-center gap-1"><Info class="w-3 h-3"/> Low</h4>
                      <div class="space-y-1.5">
                          <div v-for="cat in getCategoriesByPriority('Low')" :key="cat.name" class="flex justify-between items-center bg-slate-50 p-2 rounded-lg border border-slate-200">
                              <span class="text-xs font-medium text-slate-600 capitalize">{{ cat.name.replace('_', ' ') }}</span>
                              <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">{{ cat.count }} Unresolved</span>
                          </div>
                      </div>
                  </div>
                  
                  <div v-if="reportsWithDSS.filter(r => r.status !== 'resolved').length === 0" class="flex flex-col items-center justify-center text-slate-400 h-full w-full opacity-50">
                      <CheckCheck class="w-8 h-8 mb-2" />
                      <span class="text-xs">No unresolved reports</span>
                  </div>
              </div>
          </div>
      </div>

      <!-- Analytics Charts (Category and Role) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Category Chart -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Total Issues by Category</h3>
            <div class="h-64 flex justify-center">
                <Pie v-if="hasCategoryData" :data="categoryChartData" :options="pieOptions" class="h-full w-full" />
                <div v-else class="flex flex-col items-center justify-center text-slate-400 h-full w-full">
                    <PieChartIcon class="w-10 h-10 mb-2 opacity-20" />
                    <span class="text-sm">Not enough data to display.</span>
                </div>
            </div>
        </div>

        <!-- Role Chart -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Total Reports by User Role</h3>
            <div class="h-64">
                <Bar v-if="hasRoleData" :data="roleChartData" :options="barOptions" class="h-full w-full" />
                <div v-else class="flex flex-col items-center justify-center text-slate-400 h-full w-full">
                    <BarChartIcon class="w-10 h-10 mb-2 opacity-20" />
                    <span class="text-sm">Not enough data to display.</span>
                </div>
            </div>
        </div>
      </div>

      <!-- Main Reports Table -->
      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
            <h2 class="text-lg font-black text-slate-900 shrink-0">All Technical Reports</h2>
            
            <!-- Filter Bar -->
            <div class="flex flex-wrap items-center gap-3 w-full xl:w-auto">
                <div class="relative w-full sm:w-64">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input v-model="searchQuery" type="text" placeholder="Search user or issue..." class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl pl-9 pr-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>

                <select v-model="categoryFilter" class="bg-slate-50 border border-slate-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
                    <option value="all">All Categories</option>
                    <option v-for="cat in uniqueCategories" :key="cat" :value="cat">
                        {{ cat.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                    </option>
                </select>

                <select v-model="roleFilter" class="bg-slate-50 border border-slate-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
                    <option value="all">All Roles</option>
                    <option v-for="role in uniqueRoles" :key="role" :value="role">
                        {{ getRoleNickname(role) }}
                    </option>
                </select>

                <select v-model="priorityFilter" class="bg-slate-50 border border-slate-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
                    <option value="all">All Priorities</option>
                    <option value="Critical">Critical</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>

                <select v-model="statusFilter" class="bg-slate-50 border border-slate-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="px-6 py-4">Report Details</th>
                        <th class="px-6 py-4">Category & Priority</th>
                        <th class="px-6 py-4">User Info</th>
                        <th class="px-6 py-4">Environment</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <tr v-if="isLoading" class="bg-white">
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <Loader2 class="w-8 h-8 animate-spin mx-auto text-indigo-500 mb-2" />
                            Loading records...
                        </td>
                    </tr>
                    <tr v-else-if="filteredReports.length === 0" class="bg-white">
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <FileX class="w-10 h-10 mx-auto mb-3 opacity-30" />
                            No technical reports found matching your criteria.
                        </td>
                    </tr>
                    <tr v-else v-for="report in paginatedReports" :key="report.id" class="bg-white hover:bg-slate-50 transition-colors group">
                        <!-- Report Details -->
                        <td class="px-6 py-4 w-1/4">
                            <div class="text-xs text-slate-600 mb-2 line-clamp-2 leading-relaxed">{{ report.error_message }}</div>
                            <div class="flex items-center gap-3 text-[11px] font-mono text-slate-400">
                                <span class="flex items-center gap-1"><MapPin class="w-3 h-3 text-indigo-500" /> {{ report.page }}</span>
                                <span class="flex items-center gap-1"><Clock class="w-3 h-3 text-indigo-500" /> {{ formatDate(report.created_at) }}</span>
                            </div>
                        </td>

                        <!-- DSS Priority & Category Combined Column -->
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-start gap-1.5">
                                <Badge variant="outline" :class="[
                                    'text-[10px] font-black uppercase tracking-widest px-2 py-0.5 border-none shadow-sm',
                                    report.status === 'resolved' ? 'bg-slate-100 text-slate-400' :
                                    report.dss_priority === 'Critical' ? 'bg-red-500 text-white' : 
                                    report.dss_priority === 'High' ? 'bg-orange-500 text-white' :
                                    report.dss_priority === 'Medium' ? 'bg-yellow-400 text-yellow-900' :
                                    'bg-blue-500 text-white'
                                ]">
                                    {{ report.status === 'resolved' ? 'Resolved' : report.dss_priority + ' Priority' }}
                                </Badge>
                                <span class="text-xs font-bold text-slate-800 capitalize">{{ report.category.replace('_', ' ') }}</span>
                                <span v-if="report.status !== 'resolved'" class="text-[10px] text-slate-500 font-medium">
                                    Based on {{ report.category_pending_count }} unresolved {{ report.category_pending_count === 1 ? 'issue' : 'issues' }}
                                </span>
                            </div>
                        </td>

                        <!-- User Info -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                    {{ report.user_name ? report.user_name.charAt(0) : 'U' }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ report.user_name || 'Unknown User' }}</div>
                                    <div class="text-[11px] text-slate-500 capitalize">{{ report.role.replace('_', ' ') }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Environment -->
                        <td class="px-6 py-4 text-xs text-slate-600">
                            <div class="flex items-center gap-1.5 mb-1"><Smartphone class="w-3.5 h-3.5 text-slate-400" /> {{ report.device }}</div>
                            <div class="flex items-center gap-1.5"><Globe class="w-3.5 h-3.5 text-slate-400" /> {{ report.browser }}</div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 text-center">
                            <Badge variant="outline" :class="[
                                'text-[10px] font-black uppercase tracking-widest px-2.5 py-1 border-none',
                                report.status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : 
                                report.status === 'reviewed' ? 'bg-blue-100 text-blue-700' : 
                                'bg-amber-100 text-amber-700'
                            ]">
                                {{ report.status }}
                            </Badge>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right">
                            <button @click="openReportModal(report)" class="p-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-lg transition-colors flex items-center gap-2 ml-auto" title="View Details">
                                <Eye class="w-4 h-4" /> <span class="text-xs font-bold hidden sm:inline">View</span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div v-if="!isLoading && filteredReports.length > 0" class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
            <span class="text-sm text-slate-500 font-medium">
                Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredReports.length) }} of {{ filteredReports.length }} entries
            </span>
            <div class="flex items-center gap-4">
                <button 
                    @click="prevPage" 
                    :disabled="currentPage === 1" 
                    class="px-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-xl hover:bg-slate-50 text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm"
                >
                    Previous
                </button>
                <span class="text-sm font-bold text-slate-700">Page {{ currentPage }} of {{ totalPages }}</span>
                <button 
                    @click="nextPage" 
                    :disabled="currentPage === totalPages" 
                    class="px-4 py-2 text-sm font-bold border border-slate-200 bg-white rounded-xl hover:bg-slate-50 text-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm"
                >
                    Next
                </button>
            </div>
        </div>

      </div>
    </div>

    <!-- Details Split-Modal (Report Details & DSS Related Cases) -->
    <Dialog :open="isReportModalOpen" @update:open="isReportModalOpen = $event">
      <DialogContent class="w-[95vw] max-w-[95vw] sm:max-w-[1200px] xl:max-w-[1400px] p-0 bg-transparent border-none shadow-none flex flex-col lg:flex-row gap-6 items-stretch outline-none">
        
        <!-- Main Panel: Report Details -->
        <div v-if="selectedReport" class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 flex-1 relative flex flex-col overflow-hidden border border-slate-200">
            <button @click="isReportModalOpen = false" class="absolute top-4 right-4 p-2 text-slate-400 hover:bg-slate-100 rounded-full transition-colors">
                <X class="w-5 h-5"/>
            </button>

            <div class="flex items-start gap-4 mb-6 pr-8">
                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg uppercase shrink-0">
                    {{ selectedReport.user_name ? selectedReport.user_name.charAt(0) : 'U' }}
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900">{{ selectedReport.user_name || 'Unknown User' }}</h2>
                    <div class="flex items-center gap-2 text-sm text-slate-500 mt-1">
                        <span class="capitalize">{{ selectedReport.role.replace('_', ' ') }}</span>
                        <span>•</span>
                        <span>{{ selectedReport.email }}</span>
                    </div>
                </div>
            </div>

            <!-- Expanded Grid for More Spacing -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 mb-6 bg-slate-50 p-6 rounded-xl border border-slate-100">
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Category</p>
                    <p class="text-slate-900 font-medium capitalize">{{ selectedReport.category.replace('_', ' ') }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Page Encountered</p>
                    <p class="text-slate-900 font-medium flex items-center gap-1.5"><MapPin class="w-3.5 h-3.5 text-indigo-500"/> {{ selectedReport.page }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Device Specs</p>
                    <p class="text-slate-900 font-medium flex items-center gap-1.5"><Smartphone class="w-3.5 h-3.5 text-indigo-500"/> {{ selectedReport.device }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Browser Info</p>
                    <p class="text-slate-900 font-medium flex items-center gap-1.5"><Globe class="w-3.5 h-3.5 text-indigo-500"/> {{ selectedReport.browser }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Current Status</p>
                    <Badge variant="outline" :class="[
                        'text-[10px] font-black uppercase tracking-widest px-2.5 py-1 border-none mt-1',
                        selectedReport.status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : 
                        selectedReport.status === 'reviewed' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'
                    ]">
                        {{ selectedReport.status }}
                    </Badge>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Reported At</p>
                    <p class="text-slate-900 font-medium flex items-center gap-1.5"><Clock class="w-3.5 h-3.5 text-indigo-500"/> {{ formatDate(selectedReport.created_at) }}</p>
                </div>
            </div>

            <div class="flex-1">
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-2">Issue Description</p>
                <div class="bg-slate-100 p-4 rounded-xl text-slate-700 text-sm whitespace-pre-line border border-slate-200">
                    {{ selectedReport.error_message }}
                </div>
            </div>

            <!-- Modal Action Buttons -->
            <div class="flex flex-wrap items-center gap-3 mt-8 pt-6 border-t border-slate-100">
                <button v-if="selectedReport.attachment" @click="viewImage(selectedReport.attachment)" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition-colors flex items-center gap-2">
                    <ImageIcon class="w-4 h-4" /> View Attachment
                </button>
                
                <div class="flex items-center gap-2 ml-auto">
                    <button v-if="selectedReport.status === 'pending'" @click="updateStatus(selectedReport.id, 'reviewed')" class="px-4 py-2 bg-blue-500 text-white font-bold rounded-xl hover:bg-blue-600 transition-colors flex items-center gap-2">
                        <CheckCircle2 class="w-4 h-4" /> Mark as Reviewed
                    </button>
                    
                    <button v-if="selectedReport.status === 'reviewed' || selectedReport.status === 'pending'" @click="updateStatus(selectedReport.id, 'resolved')" class="px-4 py-2 bg-emerald-500 text-white font-bold rounded-xl hover:bg-emerald-600 transition-colors flex items-center gap-2">
                        <CheckCheck class="w-4 h-4" /> Mark as Solved
                    </button>

                    <button v-if="selectedReport.status === 'reviewed'" @click="updateStatus(selectedReport.id, 'pending')" class="px-4 py-2 bg-amber-50 text-amber-600 font-bold rounded-xl hover:bg-amber-100 transition-colors flex items-center gap-2">
                        <CornerUpLeft class="w-4 h-4" /> Revert to Pending
                    </button>

                    <button v-if="selectedReport.status === 'resolved'" @click="updateStatus(selectedReport.id, 'reviewed')" class="px-4 py-2 bg-amber-50 text-amber-600 font-bold rounded-xl hover:bg-amber-100 transition-colors flex items-center gap-2">
                        <CornerUpLeft class="w-4 h-4" /> Revert to Reviewed
                    </button>
                </div>
            </div>
        </div>

        <!-- Side Panel: DSS Related Cases -->
        <div class="bg-slate-900 rounded-2xl shadow-2xl p-6 w-full lg:w-96 shrink-0 flex flex-col border border-slate-800 h-full max-h-[80vh] lg:max-h-none overflow-hidden">
            <div class="flex justify-between items-start mb-4 shrink-0">
                <h3 class="text-white font-black text-lg flex items-center gap-2">
                    <Layers class="w-5 h-5 text-indigo-400"/> Related Cases
                </h3>
                <div class="group relative cursor-help z-20">
                    <Info class="w-4 h-4 text-slate-400 hover:text-indigo-400 transition-colors" />
                    <div class="absolute right-0 w-64 p-3 bg-slate-800 text-white text-xs rounded-xl shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-10 pointer-events-none mt-2">
                        <strong>DSS Similarity Matching:</strong><br>
                        Shows reports that share at least 2 of these 3 attributes with the viewed report:<br>
                        • User Role<br>
                        • Issue Category<br>
                        • Page Encountered
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-3">
                <div v-if="relatedCases.length === 0" class="flex flex-col items-center justify-center text-slate-400 h-full w-full opacity-50 py-10">
                    <FileX class="w-10 h-10 mb-3 opacity-50" />
                    <span class="text-sm font-medium">No highly related cases found.</span>
                </div>
                
                <div 
                    v-else 
                    v-for="related in relatedCases" 
                    :key="related.id" 
                    @click="openReportModal(related)"
                    class="bg-slate-800 p-4 rounded-xl border border-slate-700 hover:border-indigo-500 cursor-pointer transition-all group"
                >
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-bold text-white capitalize group-hover:text-indigo-300 transition-colors">{{ related.category.replace('_', ' ') }}</span>
                        <Badge variant="outline" :class="[
                            'text-[9px] font-black uppercase tracking-widest px-1.5 py-0 border-none',
                            related.status === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' :
                            related.status === 'reviewed' ? 'bg-blue-500/20 text-blue-400' : 'bg-amber-500/20 text-amber-400'
                        ]">
                            {{ related.status }}
                        </Badge>
                    </div>
                    <p class="text-xs text-slate-400 line-clamp-2 mb-2">{{ related.error_message }}</p>
                    
                    <!-- Match Indicators -->
                    <div class="flex flex-wrap gap-1 mt-2">
                        <span v-if="related.role === selectedReport.role" class="text-[9px] px-1.5 py-0.5 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">Role Match</span>
                        <span v-if="related.category === selectedReport.category" class="text-[9px] px-1.5 py-0.5 rounded bg-pink-500/20 text-pink-300 border border-pink-500/30">Category Match</span>
                        <span v-if="related.page === selectedReport.page" class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Page Match</span>
                    </div>
                </div>
            </div>
        </div>

      </DialogContent>
    </Dialog>

    <!-- Image Viewer Modal (Stacked Above Details) -->
    <Dialog :open="!!selectedImage" @update:open="selectedImage = null">
      <DialogContent class="bg-slate-900 border-slate-800 shadow-2xl max-w-5xl p-2 sm:rounded-2xl flex justify-center items-center outline-none">
        <img :src="selectedImage" class="max-h-[85vh] object-contain rounded-xl" />
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { 
    Wrench, RefreshCw, FileText, AlertCircle, CheckCircle2, CheckCheck,
    Search, MapPin, Clock, Smartphone, Globe, 
    Image as ImageIcon, CornerUpLeft, PieChart as PieChartIcon, BarChart as BarChartIcon, Loader2, FileX, Info, Activity, Eye, X, Layers
} from 'lucide-vue-next'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import echo from '@/utils/websocket'

// Chart.js Setup
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'
import { Bar, Pie } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

const reports = ref([])
const stats = ref({
    total: 0, pending: 0, reviewed: 0, resolved: 0, by_category: [], by_role: []
})
const isLoading = ref(true)

// Filter states
const searchQuery = ref('')
const statusFilter = ref('all')
const categoryFilter = ref('all')
const roleFilter = ref('all')
const priorityFilter = ref('all')

// Modal states
const isReportModalOpen = ref(false)
const selectedReport = ref(null)
const selectedImage = ref(null)

// Pagination states
const currentPage = ref(1)
const itemsPerPage = ref(10)

onMounted(() => {
    fetchData()
    setupWebsockets()
})

onUnmounted(() => {
    if (echo) {
        echo.leave('admin.technical_reports')
    }
})

// Reset pagination to page 1 whenever ANY filter changes
watch([searchQuery, statusFilter, categoryFilter, roleFilter, priorityFilter], () => {
    currentPage.value = 1
})

const setupWebsockets = () => {
    echo.private(`admin.technical_reports`)
        .listen('.report.submitted', (e) => { 
            if(e.report) {
                fetchData() // Triggers re-computation of DSS values and charts automatically
                toast.info('New Technical Report', { 
                    description: `A new ${e.report.category.replace('_', ' ')} issue was reported on the ${e.report.page} page.`
                })
            }
        })
}

const fetchData = async () => {
    isLoading.value = true
    try {
        const [reportsRes, statsRes] = await Promise.all([
            api.get('/admin/technical-reports'),
            api.get('/admin/technical-reports/statistics')
        ])

        if (reportsRes.data.success) {
            reports.value = reportsRes.data.data
        }
        if (statsRes.data.success) {
            stats.value = statsRes.data.data
        }
    } catch (error) {
        toast.error('Data Fetch Error', { description: 'Could not load technical reports or statistics.' })
    } finally {
        isLoading.value = false
    }
}

const refreshData = () => {
    fetchData()
}

// -------------------------------------------------------------
// MODALS & ACTIONS
// -------------------------------------------------------------
const openReportModal = (report) => {
    selectedReport.value = report
    isReportModalOpen.value = true
}

const updateStatus = async (id, newStatus) => {
    try {
        const response = await api.put(`/admin/technical-reports/${id}/status`, { status: newStatus })
        if (response.data.success) {
            toast.success('Status Updated', { description: `Report marked as ${newStatus}.` })
            
            // Optimistic local update
            const index = reports.value.findIndex(r => r.id === id)
            if (index !== -1) {
                const oldStatus = reports.value[index].status
                reports.value[index].status = newStatus
                
                // If this is the currently viewed report, update its reactive state
                if (selectedReport.value && selectedReport.value.id === id) {
                    selectedReport.value.status = newStatus
                }

                // Dynamically adjust stats without refetching.
                // Because status strings match the keys exactly ('pending', 'reviewed', 'resolved')
                if (stats.value[oldStatus] !== undefined) stats.value[oldStatus]--
                if (stats.value[newStatus] !== undefined) stats.value[newStatus]++
                
                // Note: DSS logic & Charts automatically react because they are tied to computed properties!
            }
        }
    } catch (error) {
        toast.error('Update Failed', { description: 'Could not change report status.' })
    }
}

const viewImage = (url) => {
    selectedImage.value = url
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// -------------------------------------------------------------
// DECISION SUPPORT SYSTEM (DSS) LOGIC
// -------------------------------------------------------------

// Calculate dynamic thresholds based on the maximum volume of UNRESOLVED reports
const dssStats = computed(() => {
    const counts = {}
    reports.value.forEach(r => {
        // DSS only scales based on active issues (pending and reviewed)
        if (r.status !== 'resolved') {
            counts[r.category] = (counts[r.category] || 0) + 1
        }
    })
    const values = Object.values(counts)
    const max = values.length ? Math.max(...values) : 0
    return { counts, max }
})

// Map the raw reports and assign the DSS priority dynamically
const reportsWithDSS = computed(() => {
    const { counts, max } = dssStats.value;

    return reports.value.map(report => {
        const pendingCount = counts[report.category] || 0;
        let priority = 'Low';
        
        if (pendingCount > 0) {
            if (max <= 3) {
                // Static fallback for very low volume so it doesn't over-escalate
                if (pendingCount >= 3) priority = 'Critical';
                else if (pendingCount === 2) priority = 'High';
                else priority = 'Low';
            } else {
                // Dynamic proportional volume scaling
                const ratio = pendingCount / max;
                if (ratio >= 0.75) priority = 'Critical';
                else if (ratio >= 0.50) priority = 'High';
                else if (ratio >= 0.25) priority = 'Medium';
                else priority = 'Low';
            }
        }

        return {
            ...report,
            dss_priority: priority,
            category_pending_count: pendingCount
        };
    });
})

// Helper to get grouped categories for the DSS Display Panel (Unresolved only)
const getCategoriesByPriority = (priority) => {
    const { counts } = dssStats.value;
    const uniqueCats = Array.from(new Set(reportsWithDSS.value.filter(r => r.dss_priority === priority && r.status !== 'resolved').map(r => r.category)));
    
    return uniqueCats.map(name => ({
        name,
        count: counts[name]
    })).sort((a, b) => b.count - a.count); // Sort highest count first
}

// RELATED CASES DSS (Modal Right Panel)
const relatedCases = computed(() => {
    if (!selectedReport.value) return [];
    
    const target = selectedReport.value;
    
    return reportsWithDSS.value.filter(r => {
        if (r.id === target.id) return false; // Skip the viewed report itself

        let matchCount = 0;
        if (r.role === target.role) matchCount++;
        if (r.category === target.category) matchCount++;
        if (r.page === target.page) matchCount++;

        return matchCount >= 2; // Must match at least 2 out of 3 attributes
    }).sort((a, b) => new Date(b.created_at) - new Date(a.created_at)); // Latest first
})


// -------------------------------------------------------------
// FILTERING & PAGINATION
// -------------------------------------------------------------

// Extract dynamic unique categories and roles directly from the data for the dropdowns
const uniqueCategories = computed(() => {
    const cats = new Set(reports.value.map(r => r.category).filter(Boolean))
    return Array.from(cats).sort()
})

const uniqueRoles = computed(() => {
    const roles = new Set(reports.value.map(r => r.role).filter(Boolean))
    return Array.from(roles).sort()
})

// The main filter pipeline passing through all requirements
const filteredReports = computed(() => {
    return reportsWithDSS.value.filter(r => {
        // Multi-condition checks
        const matchStatus = statusFilter.value === 'all' || r.status === statusFilter.value
        const matchCategory = categoryFilter.value === 'all' || r.category === categoryFilter.value
        const matchRole = roleFilter.value === 'all' || r.role === roleFilter.value
        const matchPriority = priorityFilter.value === 'all' || r.dss_priority === priorityFilter.value
        
        // Search text
        const lowerQ = searchQuery.value.toLowerCase().trim()
        const matchSearch = lowerQ === '' || 
            (r.user_name && r.user_name.toLowerCase().includes(lowerQ)) ||
            (r.error_message && r.error_message.toLowerCase().includes(lowerQ)) ||
            (r.page && r.page.toLowerCase().includes(lowerQ))

        // All conditions must pass to show the item
        return matchStatus && matchCategory && matchRole && matchPriority && matchSearch
    })
})

const totalPages = computed(() => {
    return Math.ceil(filteredReports.value.length / itemsPerPage.value) || 1
})

const paginatedReports = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredReports.value.slice(start, end)
})

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

// -------------------------------------------------------------
// SHADCN STYLED VUE-CHARTJS LOGIC
// -------------------------------------------------------------
const hasCategoryData = computed(() => stats.value.by_category.length > 0)
const hasRoleData = computed(() => stats.value.by_role.length > 0)

const brandColors = [
    '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', 
    '#ec4899', '#0ea5e9', '#14b8a6', '#f43f5e', '#64748b'
]

// Helper function to create clean nicknames for the x-axis and template dropdowns
const getRoleNickname = (role) => {
    if (!role) return ''
    const nicknames = {
        'admin': 'Admin',
        'client': 'Client',
        'distributor': 'Dist',
        'operational_distributor': 'Op Dist',
        'service_provider': 'SP',
        'supplier': 'Supplier',
        'hr_manager': 'HR',
        'finance_manager': 'Finance',
        'employee': 'Emp',
        'supplier_employee': 'Sup Emp',
        'personnel_officer': 'PO'
    }
    return nicknames[role.toLowerCase()] || role.replace('_', ' ').toUpperCase()
}

// DSS PRIORITY CHART
const priorityChartData = computed(() => {
    const counts = { 'Critical': 0, 'High': 0, 'Medium': 0, 'Low': 0 }
    
    // Only count UNRESOLVED reports for DSS Priority visualization
    reportsWithDSS.value.filter(r => r.status !== 'resolved').forEach(r => {
        counts[r.dss_priority]++
    })

    return {
        labels: ['Critical', 'High', 'Medium', 'Low'],
        datasets: [{
            data: [counts.Critical, counts.High, counts.Medium, counts.Low],
            backgroundColor: ['#ef4444', '#f97316', '#eab308', '#3b82f6'], // Red, Orange, Yellow, Blue
            borderWidth: 0,
            hoverOffset: 4,
            cutout: '70%',
        }]
    }
})

// CATEGORY DONUT CHART
const categoryChartData = computed(() => {
    return {
        labels: stats.value.by_category.map(item => item.category.replace('_', ' ').toUpperCase()),
        datasets: [{
            data: stats.value.by_category.map(item => item.count),
            backgroundColor: brandColors.slice(0, stats.value.by_category.length),
            borderWidth: 0,
            hoverOffset: 4,
            cutout: '70%', 
        }]
    }
})

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { 
            position: 'right', 
            labels: { boxWidth: 10, usePointStyle: true, font: { size: 11, family: 'Inter, sans-serif' }, color: '#475569' } 
        },
        tooltip: {
            backgroundColor: '#ffffff',
            titleColor: '#0f172a',
            bodyColor: '#475569',
            borderColor: '#e2e8f0',
            borderWidth: 1,
            padding: 12,
            boxPadding: 4,
            usePointStyle: true,
        }
    }
}

// ROLE BAR CHART
const roleChartData = computed(() => {
    return {
        // Labels for the x-axis (Shortened)
        labels: stats.value.by_role.map(item => getRoleNickname(item.role)),
        datasets: [{
            label: 'Reports Filed',
            data: stats.value.by_role.map(item => item.count),
            fullNames: stats.value.by_role.map(item => item.role.replace('_', ' ').toUpperCase()), 
            backgroundColor: '#6366f1',
            borderRadius: 6,
            borderSkipped: false,
            barThickness: 40,
            maxBarThickness: 40
        }]
    }
})

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#ffffff',
            titleColor: '#0f172a',
            bodyColor: '#475569',
            borderColor: '#e2e8f0',
            borderWidth: 1,
            padding: 12,
            displayColors: false,
            callbacks: {
                title: (tooltipItems) => {
                    const item = tooltipItems[0]
                    return item.dataset.fullNames[item.dataIndex]
                }
            }
        }
    },
    scales: {
        y: { 
            beginAtZero: true, 
            grid: { color: '#f8fafc', drawBorder: false },
            ticks: { precision: 0, color: '#94a3b8', font: { family: 'Inter, sans-serif' } },
            border: { display: false }
        },
        x: { 
            grid: { display: false, drawBorder: false },
            ticks: { color: '#64748b', font: { family: 'Inter, sans-serif', size: 10, weight: 'bold' } },
            border: { display: false }
        }
    }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #cbd5e1;
    border-radius: 20px;
}
</style>