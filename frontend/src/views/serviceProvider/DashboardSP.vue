<template>
  <div class="min-h-screen text-slate-200">
    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
    </div>

    <div v-else>
      <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Service Provider Dashboard</h1>
            <p class="text-gray-400 text-sm md:text-base">Welcome back, <span class="text-blue-400 font-semibold">{{ dashboardData.user.name }}</span></p>
          </div>
          <div class="mt-4 md:mt-0">
            <div class="flex items-center space-x-3">
              <div class="relative">
                <Avatar class="h-10 w-10 border-2 border-transparent bg-gradient-to-br from-blue-500 to-purple-500">
                  <AvatarFallback class="bg-transparent text-white font-bold">{{ dashboardData.user.initials }}</AvatarFallback>
                </Avatar>
                <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900"></div>
              </div>
              <div class="text-right">
                <p class="text-white text-sm font-medium">Service Provider</p>
                <p class="text-gray-400 text-xs">Active Session</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-6">
          <div class="inline-flex items-center space-x-3 px-4 py-2 rounded-lg bg-gray-900 border border-gray-800">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span class="text-gray-300 text-sm">{{ currentDate }}</span>
            <span class="text-gray-500">•</span>
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="text-gray-300 text-sm">{{ currentTime }}</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-blue-500 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm mb-1">Total Clients</p>
              <h3 class="text-3xl font-bold text-white mb-2">{{ dashboardData.stats.totalClients }}</h3>
              <div class="flex items-center">
                <span class="text-sm font-medium flex items-center" :class="dashboardData.stats.clientGrowth >= 0 ? 'text-green-400' : 'text-red-400'">
                  <svg v-if="dashboardData.stats.clientGrowth >= 0" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                  <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                  {{ dashboardData.stats.clientGrowth >= 0 ? '+' : '' }}{{ dashboardData.stats.clientGrowth }}% this month
                </span>
              </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white shadow-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-amber-500 transition-all duration-300 hover:shadow-lg hover:shadow-amber-500/10">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm mb-1">Active Jobs</p>
              <h3 class="text-3xl font-bold text-white mb-2">{{ dashboardData.stats.activeJobs }}</h3>
              <div class="flex items-center">
                <span class="text-amber-400 text-sm font-medium flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                  In Progress
                </span>
              </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-400 flex items-center justify-center text-white shadow-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-green-500 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/10">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm mb-1">Completed Jobs</p>
              <h3 class="text-3xl font-bold text-white mb-2">{{ dashboardData.stats.completedJobs }}</h3>
              <div class="flex items-center">
                <span class="text-green-400 text-sm font-medium flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  {{ dashboardData.stats.satisfaction }}% satisfaction
                </span>
              </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-400 flex items-center justify-center text-white shadow-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-purple-500 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
          <CardContent class="p-5 flex items-center justify-between">
            <div>
              <p class="text-gray-400 text-sm mb-1">Monthly Revenue</p>
              <h3 class="text-3xl font-bold text-white mb-2">₱{{ formatCurrency(dashboardData.stats.monthlyRevenue) }}</h3>
              <div class="flex items-center">
                <span class="text-sm font-medium flex items-center" :class="dashboardData.stats.revenueGrowth >= 0 ? 'text-purple-400' : 'text-red-400'">
                  <svg v-if="dashboardData.stats.revenueGrowth >= 0" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                  <svg v-else class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
                  {{ dashboardData.stats.revenueGrowth >= 0 ? '+' : '' }}{{ dashboardData.stats.revenueGrowth }}% from last month
                </span>
              </div>
            </div>
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-400 flex items-center justify-center text-white shadow-lg">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          
          <Card class="bg-gray-900 border-gray-800">
            <CardHeader class="flex flex-row items-center justify-between pb-2">
              <div>
                <CardTitle class="text-xl font-bold text-white">Recent Color Customizations</CardTitle>
                <p class="text-gray-400 text-sm mt-1">Latest paint colors created from your library</p>
              </div>
              <Button @click="router.push('/service-provider/colors')" variant="ghost" class="text-blue-400 hover:text-blue-300 hover:bg-gray-800">
                View All <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
              </Button>
            </CardHeader>
            <CardContent>
              <div class="hidden md:block">
                <Table>
                  <TableHeader>
                    <TableRow class="border-gray-800 hover:bg-transparent">
                      <TableHead class="text-gray-400">Color</TableHead>
                      <TableHead class="text-gray-400">Client / Ref</TableHead>
                      <TableHead class="text-gray-400">Category</TableHead>
                      <TableHead class="text-gray-400">Status</TableHead>
                      <TableHead class="text-gray-400">Date</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="color in dashboardData.recentColors" :key="color.id" class="border-gray-800 hover:bg-gray-800/50">
                      <TableCell>
                        <div class="flex items-center">
                          <div class="w-8 h-8 rounded-lg mr-3 shadow-sm border border-gray-700" :style="{ backgroundColor: color.hex }"></div>
                          <div class="min-w-0">
                            <p class="text-white font-medium truncate">{{ color.name }}</p>
                            <p class="text-gray-400 text-xs truncate">{{ color.hex }}</p>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell>
                        <div class="flex items-center">
                          <Avatar class="h-8 w-8 mr-3 bg-gradient-to-br from-blue-500 to-purple-500 border-0">
                            <AvatarFallback class="bg-transparent text-white text-xs font-bold">{{ getInitials(color.client) }}</AvatarFallback>
                          </Avatar>
                          <div class="min-w-0">
                            <p class="text-white font-medium truncate">{{ color.client }}</p>
                          </div>
                        </div>
                      </TableCell>
                      <TableCell>
                        <div class="min-w-0">
                          <p class="text-white font-medium truncate">{{ color.project }}</p>
                          <p class="text-gray-400 text-xs truncate">{{ color.roomType }}</p>
                        </div>
                      </TableCell>
                      <TableCell>
                        <Badge class="bg-gradient-to-r from-emerald-500 to-green-400 hover:from-emerald-600 hover:to-green-500 border-0">
                          {{ color.status }}
                        </Badge>
                      </TableCell>
                      <TableCell>
                        <div class="min-w-0">
                          <p class="text-white">{{ color.date }}</p>
                          <p class="text-gray-400 text-xs">{{ color.time }}</p>
                        </div>
                      </TableCell>
                    </TableRow>
                    <TableRow v-if="dashboardData.recentColors.length === 0">
                       <TableCell colspan="5" class="text-center py-6 text-gray-500">No recent colors saved.</TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>

              <div class="md:hidden space-y-4">
                 <div v-for="color in dashboardData.recentColors" :key="'mob-'+color.id" class="bg-gray-800/50 rounded-lg p-4 border border-gray-800">
                    <div class="flex items-start justify-between mb-3">
                      <div class="flex items-center">
                        <div class="w-10 h-10 rounded-lg mr-3 shadow-sm border border-gray-700" :style="{ backgroundColor: color.hex }"></div>
                        <div>
                          <p class="text-white font-medium">{{ color.name }}</p>
                          <p class="text-gray-400 text-xs">{{ color.hex }}</p>
                        </div>
                      </div>
                      <Badge class="bg-gradient-to-r from-emerald-500 to-green-400 border-0">
                        {{ color.status }}
                      </Badge>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                       <div>
                         <p class="text-gray-400 text-xs mb-1">Client/Ref</p>
                         <p class="text-white text-sm truncate">{{ color.client }}</p>
                       </div>
                    </div>
                 </div>
                 <div v-if="dashboardData.recentColors.length === 0" class="text-center py-6 text-gray-500">
                   No recent colors saved.
                 </div>
              </div>
            </CardContent>
          </Card>

          <div class="mt-6 bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-700/50 rounded-xl p-4 md:p-6">
            <div class="flex flex-col md:flex-row items-center justify-between">
              <div class="flex items-center mb-4 md:mb-0">
                <div class="mr-4">
                  <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                  </div>
                </div>
                <div>
                  <h3 class="text-xl font-bold text-white mb-1">Virtual Paint Color Mixer</h3>
                  <p class="text-gray-300 text-sm">Create custom paint colors with real-time visualization</p>
                </div>
              </div>
              <Button @click="openColorMixer" class="w-full md:w-auto bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white border-0 shadow-lg shadow-purple-500/20">
                <span>Launch Mixer</span>
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
              </Button>
            </div>
            <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
               <div class="text-center p-3 bg-gray-900/50 rounded-lg">
                 <p class="text-gray-400 text-sm">Colors Created</p>
                 <p class="text-xl md:text-2xl font-bold text-white">{{ dashboardData.colorStats.totalCreated }}</p>
               </div>
               
            </div>
          </div>
        </div>

        <div class="space-y-6">
          <Card class="bg-gray-900 border-gray-800">
            <CardHeader>
              <CardTitle class="text-xl font-bold text-white">Recent Activity</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-6">
                <div v-for="activity in dashboardData.recentActivities" :key="activity.id" class="pb-6 border-b border-gray-800 last:border-0 last:pb-0">
                  <div class="flex items-start">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border', 
                      activity.type === 'color' ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' : 
                      activity.type === 'job' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 
                      activity.type === 'client' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 
                      'bg-blue-500/10 text-blue-400 border-blue-500/20']">
                      
                      <svg v-if="activity.type === 'color'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                      <svg v-else-if="activity.type === 'job'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                      <svg v-else-if="activity.type === 'client'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                      <p class="text-white font-medium truncate">{{ activity.title }}</p>
                      <p class="text-gray-400 text-sm mt-1 line-clamp-2">{{ activity.description }}</p>
                      <p class="text-gray-500 text-xs mt-2">{{ activity.time }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="dashboardData.recentActivities.length === 0" class="text-center py-4 text-gray-500">
                  No recent activity logged.
                </div>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-gray-900 border-gray-800">
            <CardHeader>
              <CardTitle class="text-xl font-bold text-white">Quick Actions</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-2 gap-3 md:gap-4">
                <button @click="viewReports" class="p-4 rounded-xl flex flex-col items-center justify-center text-center bg-blue-900/20 border border-blue-700/30 hover:bg-blue-900/30 hover:-translate-y-1 transition-all">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center mb-3 shadow-lg shadow-blue-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                  </div>
                  <span class="text-white font-medium text-sm">Reports</span>
                </button>
                <button @click="checkInventory" class="p-4 rounded-xl flex flex-col items-center justify-center text-center bg-cyan-900/20 border border-cyan-700/30 hover:bg-cyan-900/30 hover:-translate-y-1 transition-all">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-cyan-500 to-teal-400 flex items-center justify-center mb-3 shadow-lg shadow-cyan-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                  </div>
                  <span class="text-white font-medium text-sm">Inventory</span>
                </button>
              </div>
            </CardContent>
          </Card>

          <Card class="bg-gray-900 border-gray-800">
             <CardHeader><CardTitle class="text-xl font-bold text-white">Performance Metrics</CardTitle></CardHeader>
             <CardContent class="space-y-4">
                <div>
                   <div class="flex justify-between mb-1">
                      <span class="text-gray-300 text-sm">Job Completion Rate</span>
                      <span class="text-white font-medium">{{ dashboardData.stats.jobCompletionRate }}%</span>
                   </div>
                   <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                      <div class="h-full bg-gradient-to-r from-emerald-500 to-green-400 rounded-full transition-all duration-1000" :style="`width: ${dashboardData.stats.jobCompletionRate}%`"></div>
                   </div>
                </div>
             </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'

const router = useRouter()
const isLoading = ref(true)
const currentDate = ref('')
const currentTime = ref('')
const dateTimeInterval = ref(null)

const dashboardData = ref({
  user: { name: '...', initials: '..' },
  stats: {
    totalClients: 0,
    clientGrowth: 0,
    activeJobs: 0,
    completedJobs: 0,
    satisfaction: 0,
    monthlyRevenue: 0,
    revenueGrowth: 0,
    jobCompletionRate: 0
  },
  colorStats: {
    totalCreated: 0,
    avgTime: '0m'
  },
  recentColors: [],
  recentActivities: []
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(amount || 0)
}

const updateDateTime = () => {
  const now = new Date()
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

const getInitials = (name) => {
  if (!name) return 'SP'
  return name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2)
}

const fetchDashboardData = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/service-provider/dashboard')
    if (response.data && response.data.success) {
      dashboardData.value = response.data
    }
  } catch (error) {
    console.error("Error fetching Service Provider dashboard data:", error)
  } finally {
    isLoading.value = false
  }
}

// Quick Actions
const openColorMixer = () => {
  router.push('/serviceProvider/VirtualPaintColor')
}
const viewReports = () => {
  router.push('/serviceProvider/ReportsSP')
}
const checkInventory = () => {
  router.push('/serviceProvider/inventory')
}

onMounted(() => {
  updateDateTime()
  dateTimeInterval.value = setInterval(updateDateTime, 60000)
  fetchDashboardData()
})

onBeforeUnmount(() => {
  if (dateTimeInterval.value) clearInterval(dateTimeInterval.value)
})
</script>