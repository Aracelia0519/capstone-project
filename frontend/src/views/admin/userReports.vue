<template>
  <div class="min-h-screen bg-[#f8fafc] dark:bg-gray-950 p-4 md:p-8 font-sans selection:bg-blue-100 selection:text-blue-900">
    
    <!-- Hero Page Header -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-8 shadow-2xl mb-8 border border-slate-700">
      <!-- Decorative background elements -->
      <div class="absolute -right-20 -top-20 opacity-10 pointer-events-none transform rotate-12">
        <ShieldAlert class="w-96 h-96 text-white" />
      </div>
      <div class="absolute left-1/4 bottom-0 opacity-20 pointer-events-none blur-3xl">
        <div class="w-64 h-64 bg-blue-500 rounded-full"></div>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white/80 text-xs font-bold uppercase tracking-wider mb-4 backdrop-blur-md">
            <Sparkles class="w-3.5 h-3.5 text-blue-400" /> Admin Module
          </div>
          <h1 class="text-4xl font-black text-white tracking-tight flex items-center gap-3">
            User Reports Center
          </h1>
          <p class="text-slate-400 mt-2 text-lg max-w-2xl">
            Monitor, investigate, and resolve community incident reports securely.
          </p>
        </div>
        <Button 
          @click="fetchSummaries" 
          variant="outline" 
          class="bg-white/10 hover:bg-white/20 border-white/20 text-white backdrop-blur-md shadow-lg transition-all hover:scale-105 h-12 px-6 rounded-xl"
          :disabled="isLoading"
        >
          <RefreshCw class="w-4 h-4 mr-2" :class="{'animate-spin text-blue-400': isLoading}" /> 
          <span class="font-bold">Sync Data</span>
        </Button>
      </div>
    </div>

    <!-- Cool Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 relative z-10">
      <Card class="border-0 bg-white dark:bg-slate-900 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 overflow-hidden group">
        <CardContent class="p-6 relative">
          <div class="absolute -right-6 -top-6 opacity-5 group-hover:opacity-10 transition-opacity"><Users class="w-32 h-32 text-blue-600" /></div>
          <div class="flex items-center gap-5 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/30">
              <Users class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Reported Users</p>
              <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ summaries.length }}</h3>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="border-0 bg-white dark:bg-slate-900 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 overflow-hidden group">
        <CardContent class="p-6 relative">
          <div class="absolute -right-6 -top-6 opacity-5 group-hover:opacity-10 transition-opacity"><Flag class="w-32 h-32 text-rose-600" /></div>
          <div class="flex items-center gap-5 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/30">
              <Flag class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Reports</p>
              <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ totalReportsCount }}</h3>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border-0 bg-white dark:bg-slate-900 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 overflow-hidden group">
        <CardContent class="p-6 relative">
          <div class="absolute -right-6 -top-6 opacity-5 group-hover:opacity-10 transition-opacity"><Clock class="w-32 h-32 text-amber-500" /></div>
          <div class="flex items-center gap-5 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center shrink-0 shadow-lg shadow-amber-500/30">
              <Clock class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Pending Review</p>
              <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ pendingReportsCount }}</h3>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Interactive Filters -->
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center gap-3 mb-6 relative z-10">
      <div class="relative flex-1 w-full">
        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5" />
        <Input v-model="searchQuery" placeholder="Search user name or email..." class="pl-11 h-12 w-full bg-slate-50 border-0 focus-visible:ring-2 focus-visible:ring-blue-500 rounded-xl font-medium text-slate-700" />
      </div>
      <div class="flex gap-3 w-full sm:w-auto shrink-0">
        <Select v-model="roleFilter">
          <SelectTrigger class="w-[180px] h-12 rounded-xl bg-slate-50 border-0 font-bold text-slate-600 focus:ring-blue-500">
            <SelectValue placeholder="Filter by Role" />
          </SelectTrigger>
          <SelectContent class="rounded-xl shadow-xl border-slate-100 font-medium">
            <SelectItem value="all">All Account Roles</SelectItem>
            <SelectItem value="distributor">Distributor</SelectItem>
            <SelectItem value="service_provider">Service Provider</SelectItem>
            <SelectItem value="client">Client</SelectItem>
            <SelectItem value="supplier">Supplier</SelectItem>
          </SelectContent>
        </Select>
        <Select v-model="statusFilter">
          <SelectTrigger class="w-[180px] h-12 rounded-xl bg-slate-50 border-0 font-bold text-slate-600 focus:ring-blue-500">
            <SelectValue placeholder="Filter by Status" />
          </SelectTrigger>
          <SelectContent class="rounded-xl shadow-xl border-slate-100 font-medium">
            <SelectItem value="all">All Statuses</SelectItem>
            <SelectItem value="has_pending">Has Pending Action</SelectItem>
            <SelectItem value="resolved">Fully Reviewed</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <!-- Main Data Table -->
    <Card class="border-0 shadow-[0_8px_30px_rgb(0,0,0,0.03)] bg-white rounded-3xl overflow-hidden relative z-10">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-slate-50 border-b border-slate-100">
            <TableRow class="hover:bg-transparent">
              <TableHead class="py-5 px-6 font-bold text-slate-500 uppercase tracking-wider text-xs">Reported User</TableHead>
              <TableHead class="font-bold text-slate-500 uppercase tracking-wider text-xs">Account Role</TableHead>
              <TableHead class="text-center font-bold text-slate-500 uppercase tracking-wider text-xs">Total Reports</TableHead>
              <TableHead class="text-center font-bold text-slate-500 uppercase tracking-wider text-xs">Status</TableHead>
              <TableHead class="font-bold text-slate-500 uppercase tracking-wider text-xs">Last Incident</TableHead>
              <TableHead class="text-right py-5 px-6 font-bold text-slate-500 uppercase tracking-wider text-xs">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="isLoading">
              <TableCell colspan="6" class="h-64 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400">
                  <Loader2 class="w-10 h-10 animate-spin mb-4 text-blue-500" />
                  <p class="font-medium text-lg">Fetching reports database...</p>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-else-if="filteredSummaries.length === 0">
              <TableCell colspan="6" class="h-64 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400">
                  <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <ShieldCheck class="w-10 h-10 text-emerald-400" />
                  </div>
                  <p class="font-bold text-xl text-slate-600">All Clear</p>
                  <p class="text-sm mt-1">No reports found matching your criteria.</p>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-else v-for="item in filteredSummaries" :key="item.reported_user_id" class="hover:bg-blue-50/50 transition-colors border-b border-slate-50">
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-300 flex items-center justify-center font-black text-slate-500 shadow-sm shrink-0">
                    {{ item.first_name.charAt(0) }}{{ item.last_name.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 text-sm">{{ item.first_name }} {{ item.last_name }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ item.email }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <Badge variant="secondary" class="capitalize bg-slate-100 text-slate-700 font-bold border-0 shadow-sm">
                  {{ item.reported_user_role.replace('_', ' ') }}
                </Badge>
              </TableCell>
              <TableCell class="text-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 font-black text-slate-700 shadow-sm">
                  {{ item.total_reports }}
                </span>
              </TableCell>
              <TableCell class="text-center">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold" 
                     :class="item.pending_reports > 0 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700'">
                  <div class="w-2 h-2 rounded-full" :class="item.pending_reports > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></div>
                  {{ item.pending_reports > 0 ? `${item.pending_reports} Pending` : 'All Reviewed' }}
                </div>
              </TableCell>
              <TableCell class="text-sm font-medium text-slate-600">
                {{ formatDate(item.last_report_date) }}
              </TableCell>
              <TableCell class="text-right py-4 px-6">
                <Button variant="outline" size="sm" class="text-blue-600 hover:text-blue-700 bg-white hover:bg-blue-50 border-slate-200 hover:border-blue-200 shadow-sm font-bold transition-all rounded-lg" @click="viewUserDetails(item.reported_user_id)">
                  <Eye class="w-4 h-4 mr-2" /> Inspect Cases
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <!-- Super Cool Details Modal -->
    <Dialog :open="isDetailsModalOpen" @update:open="closeDetailsModal">
      <!-- Added native HTML overflow and wide sizing -->
      <DialogContent class="w-[95vw] max-w-[95vw] xl:max-w-7xl h-[95vh] p-0 flex flex-col bg-slate-50 overflow-hidden rounded-2xl border-0 shadow-2xl [&>button]:text-slate-400 [&>button]:right-6 [&>button]:top-6 [&>button]:z-50 [&>button]:bg-white [&>button]:rounded-full [&>button]:shadow-md [&>button]:p-2">
        
        <!-- Modal Header -->
        <DialogHeader class="px-8 py-8 bg-gradient-to-r from-slate-900 to-slate-800 shrink-0 relative overflow-hidden">
          <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
            <ShieldAlert class="w-64 h-64 -mt-10 -mr-10 text-white" />
          </div>
          <div v-if="selectedUser" class="relative z-10 pr-12">
            <div class="flex items-center gap-3 mb-3">
              <Badge variant="outline" class="border-white/20 bg-white/10 text-white backdrop-blur-sm font-bold uppercase tracking-wider shadow-sm">
                Defendant Profile
              </Badge>
              <span class="text-slate-300 text-sm font-medium">{{ selectedUser.email }}</span>
            </div>
            <DialogTitle class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
              {{ selectedUser.first_name }} {{ selectedUser.last_name }}
            </DialogTitle>
            <DialogDescription class="mt-2 text-slate-300 text-base font-medium flex items-center gap-2">
              Registered Role: <strong class="text-white capitalize">{{ selectedUser.role.replace('_', ' ') }}</strong>
            </DialogDescription>
          </div>
        </DialogHeader>

        <!-- NATIVE Scrollable Content with Overflow-Y-Auto -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 custom-scrollbar relative">
          
          <div v-if="isFetchingDetails" class="flex flex-col justify-center items-center py-40">
            <Loader2 class="w-12 h-12 animate-spin text-blue-500 mb-4" />
            <p class="text-slate-500 text-lg font-bold animate-pulse">Compiling Evidence & Analytics...</p>
          </div>
          
          <div v-else-if="selectedUserAnalytics && selectedUserReports.length > 0" class="space-y-8 max-w-6xl mx-auto pb-10">
            
            <!-- Sleek Analytics Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              
              <!-- Review Progress Bar Card -->
              <Card class="border-0 shadow-lg bg-white rounded-3xl relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity pointer-events-none"><CheckCircle2 class="w-48 h-48 text-emerald-600" /></div>
                <CardHeader class="pb-0 pt-6 px-8 relative z-10">
                  <CardTitle class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <Activity class="w-4 h-4 text-emerald-500" /> Review Progress
                  </CardTitle>
                </CardHeader>
                <CardContent class="p-8 pt-6 relative z-10 flex flex-col justify-center h-full">
                  <div class="flex justify-between items-end mb-4">
                    <div>
                      <span class="text-5xl font-black text-slate-900 tracking-tighter">{{ Math.round(reviewedPercentage) }}%</span>
                      <span class="text-slate-500 font-bold ml-2 uppercase tracking-wide text-sm">Reviewed</span>
                    </div>
                    <div class="text-right">
                      <span class="block text-2xl font-black text-slate-800">{{ selectedUserAnalytics.total }}</span>
                      <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mt-1">Total Cases</span>
                    </div>
                  </div>
                  
                  <!-- Glowing Progress Bar -->
                  <div class="h-4 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner p-0.5">
                    <div class="h-full rounded-full transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(34,197,94,0.5)]" 
                         :class="reviewedPercentage === 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-amber-400 to-emerald-400'"
                         :style="{ width: reviewedPercentage + '%' }">
                    </div>
                  </div>

                  <div class="flex justify-between mt-6 pt-6 border-t border-slate-100">
                    <div class="flex items-center gap-3">
                      <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-lg border border-emerald-100 shadow-sm">{{ selectedUserAnalytics.statuses.reviewed }}</div>
                      <span class="font-bold text-slate-600 text-sm">Reviewed<br>Cases</span>
                    </div>
                    <div class="flex items-center gap-3 text-right">
                      <span class="font-bold text-slate-600 text-sm">Pending<br>Action</span>
                      <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-lg border border-amber-100 shadow-sm">{{ selectedUserAnalytics.statuses.pending }}</div>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <!-- Reasons Donut Chart Card -->
              <Card class="border-0 shadow-lg bg-white rounded-3xl overflow-hidden relative">
                <CardHeader class="pb-2 pt-6 px-8">
                  <CardTitle class="text-sm font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                    <PieChart class="w-4 h-4 text-blue-500" /> Offense Breakdown
                  </CardTitle>
                </CardHeader>
                <CardContent class="p-8 flex flex-col sm:flex-row items-center gap-10">
                  
                  <!-- CSS Conic Gradient Donut -->
                  <div class="relative w-40 h-40 rounded-full shadow-[0_0_20px_rgba(0,0,0,0.08)] shrink-0 transition-transform hover:scale-105 duration-500" :style="reasonsPieChartStyle">
                    <div class="absolute inset-3 bg-white rounded-full flex flex-col items-center justify-center shadow-inner">
                      <TrendingUp class="w-6 h-6 text-slate-300 mb-1" />
                      <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Metrics</span>
                    </div>
                  </div>

                  <!-- Color Legend -->
                  <div class="flex-1 w-full space-y-3 custom-scrollbar max-h-[160px] overflow-y-auto pr-2">
                    <div v-for="item in reasonsWithColors" :key="item.reason" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                      <div class="flex items-center gap-3 min-w-0">
                        <span class="w-4 h-4 rounded-full shrink-0 shadow-sm border border-black/5" :style="{ backgroundColor: item.color }"></span>
                        <span class="text-sm font-bold text-slate-700 truncate" :title="item.reason">{{ item.reason }}</span>
                      </div>
                      <Badge variant="secondary" class="bg-white border border-slate-200 shadow-sm text-slate-700 shrink-0 font-black ml-4 px-2">
                        {{ item.count }}
                      </Badge>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- List of Reports (Timeline style) -->
            <div class="mt-12">
              <h3 class="text-2xl font-black text-slate-900 mb-6 flex items-center gap-3">
                <FileText class="w-6 h-6 text-blue-600" /> Case History Database
              </h3>
              
              <div class="space-y-6">
                <div v-for="report in selectedUserReports" :key="report.id" 
                     class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all overflow-hidden flex flex-col md:flex-row group relative">
                  
                  <!-- Left Colored Border Strip -->
                  <div class="absolute left-0 top-0 bottom-0 w-2 md:w-3 transition-colors" 
                       :class="report.status === 'reviewed' ? 'bg-emerald-500' : 'bg-amber-400'"></div>
                  
                  <div class="p-6 md:p-8 pl-8 md:pl-10 flex-1 flex flex-col md:flex-row gap-8">
                    
                    <!-- Core Info -->
                    <div class="flex-1 space-y-4">
                      <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-black border"
                             :class="report.status === 'reviewed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200'">
                          {{ report.reason }}
                        </div>
                        <span class="text-sm text-slate-400 font-bold flex items-center gap-2 bg-slate-50 px-3 py-1 rounded-lg border border-slate-100 shadow-sm">
                          <Calendar class="w-4 h-4 text-slate-400" /> Incident Date: {{ formatDate(report.incident_date) }}
                        </span>
                      </div>

                      <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 text-base text-slate-700 font-medium leading-relaxed shadow-inner">
                        "{{ report.description }}"
                      </div>

                      <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500 mt-4 bg-slate-50/50 p-2 rounded-xl w-max border border-slate-100">
                        <UserCircle2 class="w-5 h-5 text-slate-400 shrink-0" />
                        <span>Filed by: <strong class="text-slate-800">{{ report.reporter_first_name }} {{ report.reporter_last_name }}</strong></span>
                        <Badge variant="outline" class="text-[10px] uppercase font-black tracking-wider bg-white">{{ report.reporter_role.replace('_', ' ') }}</Badge>
                        <span class="mx-1 text-slate-300">|</span>
                        <span class="text-xs font-bold"><Clock class="inline w-3 h-3 mr-1" />{{ formatDate(report.created_at) }}</span>
                      </div>
                    </div>

                    <!-- Action Sidebar -->
                    <div class="md:w-72 shrink-0 flex flex-col justify-center gap-6 border-t md:border-t-0 md:border-l border-slate-100 pt-6 md:pt-0 md:pl-8 relative">
                      
                      <!-- Absolute status icon for visual flair -->
                      <div class="absolute right-0 top-0 opacity-5 pointer-events-none hidden md:block">
                        <CheckCircle2 v-if="report.status === 'reviewed'" class="w-32 h-32 text-emerald-500" />
                        <Clock v-else class="w-32 h-32 text-amber-500" />
                      </div>

                      <div class="relative z-10">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                          <Paperclip class="w-3 h-3" /> Attached Evidence
                        </h4>
                        <Button v-if="report.evidence_url" @click="viewEvidence(report.evidence_url)" variant="outline" class="w-full h-12 text-sm text-blue-700 border-blue-200 bg-blue-50 hover:bg-blue-100 hover:border-blue-300 font-bold shadow-sm rounded-xl transition-all hover:-translate-y-0.5">
                          <Eye class="w-4 h-4 mr-2" /> Inspect File
                        </Button>
                        <div v-else class="h-12 flex items-center justify-center bg-slate-50 border border-slate-200 border-dashed rounded-xl">
                          <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">No evidence attached</p>
                        </div>
                      </div>

                      <div class="relative z-10">
                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                          <ShieldAlert class="w-3 h-3" /> Case Resolution
                        </h4>
                        <div class="flex items-center justify-between bg-white p-2 rounded-xl border shadow-sm transition-colors"
                             :class="report.status === 'reviewed' ? 'border-emerald-200' : 'border-amber-200'">
                          
                          <div class="flex items-center pl-2 gap-2">
                            <div class="w-2 h-2 rounded-full" :class="report.status === 'reviewed' ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse'"></div>
                            <span class="text-xs font-black uppercase tracking-wider" :class="report.status === 'reviewed' ? 'text-emerald-700' : 'text-amber-700'">
                              {{ report.status === 'reviewed' ? 'Reviewed' : 'Pending' }}
                            </span>
                          </div>

                          <Button 
                            size="sm" 
                            class="h-9 px-4 text-xs font-bold shadow-sm rounded-lg transition-all hover:scale-105"
                            :class="report.status === 'pending' ? 'bg-emerald-500 hover:bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-600'"
                            @click="toggleStatus(report)"
                            :disabled="isUpdatingStatus === report.id"
                          >
                            <Loader2 v-if="isUpdatingStatus === report.id" class="w-4 h-4 animate-spin" />
                            <span v-else>{{ report.status === 'pending' ? 'Mark Reviewed' : 'Mark Pending' }}</span>
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { 
  ShieldAlert, RefreshCw, Users, Flag, AlertCircle, Search, Eye, X, Loader2, ShieldCheck, 
  Calendar, UserCircle2, Paperclip, Sparkles, Activity, CheckCircle2, Clock, PieChart, TrendingUp, FileText
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

// Main State
const summaries = ref([])
const isLoading = ref(true)

// Filters State
const searchQuery = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')

// Details Modal State
const isDetailsModalOpen = ref(false)
const isFetchingDetails = ref(false)
const selectedUser = ref(null)
const selectedUserReports = ref([])
const selectedUserAnalytics = ref(null)
const isUpdatingStatus = ref(null)

// --- Methods ---

const fetchSummaries = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/admin/reports')
    if (res.data.success) {
      summaries.value = res.data.data
    }
  } catch (err) {
    toast.error('Error fetching data', { description: 'Could not load reports summary.' })
  } finally {
    isLoading.value = false
  }
}

const viewUserDetails = async (userId) => {
  isDetailsModalOpen.value = true
  isFetchingDetails.value = true
  try {
    const res = await api.get(`/admin/reports/user/${userId}`)
    if (res.data.success) {
      selectedUser.value = res.data.user
      selectedUserReports.value = res.data.reports
      selectedUserAnalytics.value = res.data.analytics
    }
  } catch (err) {
    toast.error('Error fetching user cases', { description: 'Could not load detailed reports.' })
    isDetailsModalOpen.value = false
  } finally {
    isFetchingDetails.value = false
  }
}

const toggleStatus = async (report) => {
  const newStatus = report.status === 'pending' ? 'reviewed' : 'pending'
  isUpdatingStatus.value = report.id
  try {
    const res = await api.put(`/admin/reports/${report.id}/status`, { status: newStatus })
    if (res.data.success) {
      report.status = newStatus
      toast.success('Case Status Updated', { description: `Report has been marked as ${newStatus}.` })
      
      if (newStatus === 'reviewed') {
        selectedUserAnalytics.value.statuses.pending--
        selectedUserAnalytics.value.statuses.reviewed++
      } else {
        selectedUserAnalytics.value.statuses.pending++
        selectedUserAnalytics.value.statuses.reviewed--
      }

      fetchSummariesBackground()
    }
  } catch (err) {
    toast.error('Update Failed', { description: 'Could not change the case status.' })
  } finally {
    isUpdatingStatus.value = null
  }
}

const fetchSummariesBackground = async () => {
    try {
        const res = await api.get('/admin/reports')
        if (res.data.success) summaries.value = res.data.data
    } catch(e) {}
}

const closeDetailsModal = (val) => {
  if (val === false || typeof val === 'object') {
    isDetailsModalOpen.value = false
    setTimeout(() => {
      selectedUser.value = null
      selectedUserReports.value = []
      selectedUserAnalytics.value = null
    }, 300)
  }
}

const viewEvidence = (url) => {
  if (url) window.open(url, '_blank')
}

// --- Computed Properties ---

const totalReportsCount = computed(() => {
  return summaries.value.reduce((acc, curr) => acc + parseInt(curr.total_reports), 0)
})

const pendingReportsCount = computed(() => {
  return summaries.value.reduce((acc, curr) => acc + parseInt(curr.pending_reports), 0)
})

const filteredSummaries = computed(() => {
  return summaries.value.filter(item => {
    const searchStr = `${item.first_name} ${item.last_name} ${item.email}`.toLowerCase()
    const matchesSearch = searchStr.includes(searchQuery.value.toLowerCase())
    const matchesRole = roleFilter.value === 'all' || item.reported_user_role === roleFilter.value
    
    let matchesStatus = true
    if (statusFilter.value === 'has_pending') {
      matchesStatus = parseInt(item.pending_reports) > 0
    } else if (statusFilter.value === 'resolved') {
      matchesStatus = parseInt(item.pending_reports) === 0
    }

    return matchesSearch && matchesRole && matchesStatus
  })
})

const reviewedPercentage = computed(() => {
  if (!selectedUserAnalytics.value || selectedUserAnalytics.value.total === 0) return 0
  const reviewed = selectedUserAnalytics.value.statuses.reviewed || 0
  return (reviewed / selectedUserAnalytics.value.total) * 100
})

// Pie Chart Logic (Donut Chart for Reasons)
const chartColors = ['#3b82f6', '#f43f5e', '#8b5cf6', '#f97316', '#10b981', '#ec4899', '#eab308', '#06b6d4']

const reasonsWithColors = computed(() => {
  if (!selectedUserAnalytics.value || !selectedUserAnalytics.value.reasons) return []
  let i = 0
  return Object.entries(selectedUserAnalytics.value.reasons).map(([reason, count]) => {
    const pct = (count / selectedUserAnalytics.value.total) * 100
    return { reason, count, pct, color: chartColors[i++ % chartColors.length] }
  })
})

const reasonsPieChartStyle = computed(() => {
  if (reasonsWithColors.value.length === 0) return 'background: #e2e8f0;'
  let currentPct = 0
  let gradientParts = []
  
  reasonsWithColors.value.forEach(item => {
    gradientParts.push(`${item.color} ${currentPct}% ${currentPct + item.pct}%`)
    currentPct += item.pct
  })
  
  return `background: conic-gradient(${gradientParts.join(', ')});`
})

// --- Utilities ---
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return isNaN(date.getTime()) ? 'N/A' : new Intl.DateTimeFormat('en-US', {
    month: 'short', day: 'numeric', year: 'numeric'
  }).format(date)
}

onMounted(() => {
  fetchSummaries()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>