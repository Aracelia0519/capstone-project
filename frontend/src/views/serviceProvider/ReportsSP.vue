<template>
  <div class="reports-page min-h-screen text-gray-100">
    <header class="sticky top-0 z-40 backdrop-blur-lg border-b border-gray-800 shadow-xl">
      <div class="px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
              <div class="p-2 bg-gradient-to-br from-rose-600 to-pink-600 rounded-xl shadow-xl">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              Performance Reports
            </h1>
            <p class="text-gray-400 mt-2 flex items-center gap-2">
              <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
              Personal performance analytics & insights
            </p>
          </div>
          
          <div class="flex gap-3">
            <Button 
              @click="generateReport"
              class="h-12 px-6 bg-gradient-to-r from-blue-600 to-cyan-500 hover:from-blue-700 hover:to-cyan-600 border-0 shadow-lg shadow-blue-500/25"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              <span class="hidden sm:inline">Export Report</span>
              <span class="sm:hidden">Export</span>
            </Button>
            <Button 
              @click="refreshData"
              variant="outline"
              class="h-12 px-6 bg-gray-800 border-gray-700 text-white hover:bg-gray-700 hover:text-white"
            >
              <svg class="w-5 h-5 mr-2" :class="{'animate-spin': refreshing}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span class="hidden sm:inline">Refresh</span>
            </Button>
          </div>
        </div>
        
        <div class="mt-6 flex flex-wrap items-center gap-4">
          <div class="flex items-center gap-2 text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-sm">Reporting Period:</span>
          </div>
          <div class="flex flex-wrap gap-2">
            <Button 
              v-for="period in timePeriods" 
              :key="period.value"
              @click="selectedPeriod = period.value"
              size="sm"
              :class="selectedPeriod === period.value 
                ? 'bg-gradient-to-r from-blue-600 to-cyan-500 text-white shadow-lg shadow-blue-500/25 border-0 hover:from-blue-700 hover:to-cyan-600' 
                : 'bg-gray-800 text-gray-300 hover:bg-gray-700 border-gray-700'"
              variant="outline"
            >
              {{ period.label }}
            </Button>
          </div>
        </div>
      </div>
    </header>

    <main class="px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl text-white">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-gray-400 text-sm flex items-center gap-2">
                  <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Jobs Completed
                </div>
                <div class="text-3xl font-bold mt-2">{{ overviewStats.completedJobs }}</div>
                <div class="text-green-400 text-sm mt-1 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                  </svg>
                  +{{ overviewStats.jobGrowth }}% from last period
                </div>
              </div>
              <div class="p-3 bg-gray-800/50 rounded-xl">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-emerald-400 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl text-white">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-gray-400 text-sm flex items-center gap-2">
                  <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Avg. Completion Time
                </div>
                <div class="text-3xl font-bold mt-2">{{ overviewStats.avgTime }}</div>
                <div class="text-yellow-400 text-sm mt-1 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                  </svg>
                  {{ overviewStats.timeImprovement }}% faster
                </div>
              </div>
              <div class="p-3 bg-gray-800/50 rounded-xl">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-500 to-amber-400 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl text-white">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-gray-400 text-sm flex items-center gap-2">
                  <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Client Satisfaction
                </div>
                <div class="text-3xl font-bold mt-2">{{ overviewStats.satisfaction }}%</div>
                <div class="text-blue-400 text-sm mt-1 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-.464 5.535a1 1 0 10-1.415-1.414 3 3 0 01-4.242 0 1 1 0 00-1.415 1.414 5 5 0 007.072 0z" clip-rule="evenodd" />
                  </svg>
                  {{ overviewStats.satisfactionChange }} positive reviews
                </div>
              </div>
              <div class="p-3 bg-gray-800/50 rounded-xl">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl text-white">
          <CardContent class="p-5">
            <div class="flex items-center justify-between">
              <div>
                <div class="text-gray-400 text-sm flex items-center gap-2">
                  <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                  </svg>
                  Revenue Generated
                </div>
                <div class="text-3xl font-bold mt-2">${{ overviewStats.revenue.toLocaleString() }}</div>
                <div class="text-purple-400 text-sm mt-1 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  {{ overviewStats.revenueGrowth }}% growth
                </div>
              </div>
              <div class="p-3 bg-gray-800/50 rounded-xl">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-pink-400 flex items-center justify-center">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl overflow-hidden text-white">
          <CardHeader class="border-b border-gray-700">
             <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-r from-green-500 to-emerald-400 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-xl font-bold">Jobs Completed</h2>
                  <p class="text-gray-400 text-sm">Recent service jobs with completion details</p>
                </div>
              </div>
              <Badge variant="outline" class="bg-green-900/30 text-green-400 border-green-800">
                {{ completedJobs.length }} Total
              </Badge>
            </div>
          </CardHeader>
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow class="hover:bg-transparent border-gray-700">
                  <TableHead class="text-gray-400">Client</TableHead>
                  <TableHead class="text-gray-400">Service Type</TableHead>
                  <TableHead class="text-gray-400">Date</TableHead>
                  <TableHead class="text-gray-400">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow 
                  v-for="job in completedJobs" 
                  :key="job.id"
                  class="hover:bg-gray-800/30 border-gray-800 transition-colors"
                >
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center">
                        <span class="text-xs font-bold text-white">{{ job.client.charAt(0) }}</span>
                      </div>
                      <div>
                        <div class="font-medium text-white">{{ job.client }}</div>
                        <div class="text-gray-400 text-sm">{{ job.location }}</div>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="text-white">{{ job.serviceType }}</div>
                    <div class="text-gray-400 text-sm">{{ job.area }} sq ft</div>
                  </TableCell>
                  <TableCell>
                    <div class="text-white">{{ job.date }}</div>
                    <div class="text-gray-400 text-sm">{{ job.duration }}</div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline" class="font-medium border-0" :class="{
                      'bg-green-900/30 text-green-400 border border-green-800': job.status === 'Completed',
                      'bg-blue-900/30 text-blue-400 border border-blue-800': job.status === 'In Review',
                      'bg-yellow-900/30 text-yellow-400 border border-yellow-800': job.status === 'Scheduled'
                    }">
                      {{ job.status }}
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <div class="p-4 border-t border-gray-700 bg-gray-800/30 flex items-center justify-between">
            <div class="text-gray-400 text-sm">
              Showing {{ Math.min(completedJobs.length, 5) }} of {{ completedJobs.length }} jobs
            </div>
            <Button 
              variant="link" 
              @click="viewAllJobs"
              class="text-blue-400 hover:text-blue-300 p-0 h-auto"
            >
              View All
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Button>
          </div>
        </Card>

        <Card class="bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl overflow-hidden text-white">
          <CardHeader class="border-b border-gray-700">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-gradient-to-r from-purple-500 to-pink-400 rounded-lg">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                  </svg>
                </div>
                <div>
                  <h2 class="text-xl font-bold">Most Used Colors</h2>
                  <p class="text-gray-400 text-sm">Popular color preferences across projects</p>
                </div>
              </div>
              <Badge variant="outline" class="bg-purple-900/30 text-purple-400 border-purple-800">
                {{ colors.length }} Colors
              </Badge>
            </div>
          </CardHeader>
          
          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow class="hover:bg-transparent border-gray-700">
                  <TableHead class="text-gray-400">Color</TableHead>
                  <TableHead class="text-gray-400">Name</TableHead>
                  <TableHead class="text-gray-400">Usage Count</TableHead>
                  <TableHead class="text-gray-400">Trend</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow 
                  v-for="color in colors" 
                  :key="color.id"
                  class="hover:bg-gray-800/30 border-gray-800 transition-colors"
                >
                  <TableCell>
                    <div 
                      class="w-10 h-10 rounded-lg border-2 border-gray-700 shadow-lg"
                      :style="{ backgroundColor: color.hex }"
                    ></div>
                  </TableCell>
                  <TableCell>
                    <div class="font-medium text-white">{{ color.name }}</div>
                    <div class="text-gray-400 text-sm">{{ color.brand }}</div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <div class="text-white text-lg font-bold">{{ color.usage }}</div>
                      <div class="text-gray-400 text-sm">projects</div>
                    </div>
                    <div class="w-32 bg-gray-700 rounded-full h-2 mt-2 overflow-hidden">
                      <div 
                        class="h-full rounded-full transition-all duration-1000"
                        :style="{ 
                          width: `${(color.usage / maxColorUsage) * 100}%`,
                          backgroundColor: color.hex
                        }"
                      ></div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-2" :class="color.trend > 0 ? 'text-green-400' : 'text-red-400'">
                      <svg v-if="color.trend > 0" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                      </svg>
                      <span class="font-medium">{{ Math.abs(color.trend) }}%</span>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
          <div class="p-4 border-t border-gray-700 bg-gray-800/30">
            <div class="text-gray-400 text-sm">
              Based on {{ totalColorUsage }} color applications across all projects
            </div>
          </div>
        </Card>
      </div>

      <Card class="mt-8 bg-gradient-to-br from-gray-800 to-gray-900 border-gray-700 shadow-xl overflow-hidden text-white">
        <CardHeader class="border-b border-gray-700">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-gradient-to-r from-cyan-500 to-blue-400 rounded-lg">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
              <div>
                <h2 class="text-xl font-bold">Monthly Activity Summary</h2>
                <p class="text-gray-400 text-sm">Performance metrics over the last 6 months</p>
              </div>
            </div>
            <Badge variant="outline" class="bg-blue-900/30 text-blue-400 border-blue-800">
              Last 6 Months
            </Badge>
          </div>
        </CardHeader>
        
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow class="hover:bg-transparent border-gray-700">
                <TableHead class="text-gray-400">Month</TableHead>
                <TableHead class="text-gray-400">Jobs</TableHead>
                <TableHead class="text-gray-400">Revenue</TableHead>
                <TableHead class="text-gray-400">Avg. Time</TableHead>
                <TableHead class="text-gray-400">Satisfaction</TableHead>
                <TableHead class="text-gray-400">Performance</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="month in monthlyActivity" 
                :key="month.month"
                class="hover:bg-gray-800/30 border-gray-800 transition-colors"
              >
                <TableCell>
                  <div class="font-medium text-white">{{ month.month }}</div>
                  <div class="text-gray-400 text-sm">{{ month.year }}</div>
                </TableCell>
                <TableCell>
                  <div class="font-bold text-white">{{ month.jobs }}</div>
                  <div class="text-gray-400 text-sm">{{ month.jobsChange }} jobs</div>
                </TableCell>
                <TableCell>
                  <div class="font-bold text-white">${{ month.revenue.toLocaleString() }}</div>
                  <div :class="month.revenueChange >= 0 ? 'text-green-400' : 'text-red-400'" class="text-sm">
                    {{ month.revenueChange >= 0 ? '+' : '' }}{{ month.revenueChange }}%
                  </div>
                </TableCell>
                <TableCell>
                  <div class="font-bold text-white">{{ month.avgTime }}</div>
                  <div :class="month.timeChange <= 0 ? 'text-green-400' : 'text-red-400'" class="text-sm">
                    {{ month.timeChange <= 0 ? 'Faster' : 'Slower' }}
                  </div>
                </TableCell>
                <TableCell>
                  <div class="font-bold text-white">{{ month.satisfaction }}%</div>
                  <div class="w-24 bg-gray-700 rounded-full h-2 mt-2 overflow-hidden">
                    <div 
                      class="h-full rounded-full"
                      :style="{ 
                        width: `${month.satisfaction}%`,
                        background: `linear-gradient(90deg, ${month.satisfaction >= 80 ? '#10b981' : month.satisfaction >= 60 ? '#f59e0b' : '#ef4444'}, ${month.satisfaction >= 80 ? '#34d399' : month.satisfaction >= 60 ? '#fbbf24' : '#f87171'})`
                      }"
                    ></div>
                  </div>
                </TableCell>
                <TableCell>
                  <Badge variant="outline" class="font-medium border-0" :class="{
                    'bg-green-900/30 text-green-400 border border-green-800': month.performance === 'Excellent',
                    'bg-blue-900/30 text-blue-400 border border-blue-800': month.performance === 'Good',
                    'bg-yellow-900/30 text-yellow-400 border border-yellow-800': month.performance === 'Average',
                    'bg-red-900/30 text-red-400 border border-red-800': month.performance === 'Needs Improvement'
                  }">
                    {{ month.performance }}
                  </Badge>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </Card>
    </main>
  </div>
</template>

<script>
// Components
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

export default {
  name: 'ReportsPage',
  components: {
    Button,
    Card,
    CardContent,
    CardHeader,
    Badge,
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
  },
  data() {
    return {
      selectedPeriod: 'month',
      refreshing: false,
      timePeriods: [
        { label: 'This Week', value: 'week' },
        { label: 'This Month', value: 'month' },
        { label: 'Last Quarter', value: 'quarter' },
        { label: 'This Year', value: 'year' },
        { label: 'All Time', value: 'all' }
      ],
      overviewStats: {
        completedJobs: 42,
        jobGrowth: 15,
        avgTime: '2.5h',
        timeImprovement: 12,
        satisfaction: 94,
        satisfactionChange: 8,
        revenue: 28500,
        revenueGrowth: 22
      },
      completedJobs: [
        { id: 1, client: 'John Smith', location: 'Manila', serviceType: 'Wall Painting', area: 1200, date: '2024-01-15', duration: '2 days', status: 'Completed' },
        { id: 2, client: 'Maria Garcia', location: 'Makati', serviceType: 'Ceiling Repair', area: 850, date: '2024-01-14', duration: '1 day', status: 'Completed' },
        { id: 3, client: 'Robert Chen', location: 'Taguig', serviceType: 'Full House', area: 3200, date: '2024-01-12', duration: '5 days', status: 'In Review' },
        { id: 4, client: 'Lisa Wong', location: 'Pasig', serviceType: 'Kitchen Cabinet', area: 450, date: '2024-01-10', duration: '1 day', status: 'Completed' },
        { id: 5, client: 'David Park', location: 'Quezon City', serviceType: 'Exterior Walls', area: 1800, date: '2024-01-08', duration: '3 days', status: 'Scheduled' }
      ],
      colors: [
        { id: 1, name: 'Arctic White', brand: 'Sherwin-Williams', hex: '#f8fafc', usage: 18, trend: 25 },
        { id: 2, name: 'Midnight Blue', brand: 'Benjamin Moore', hex: '#1e3a8a', usage: 15, trend: 12 },
        { id: 3, name: 'Emerald Green', brand: 'Behr', hex: '#047857', usage: 12, trend: -5 },
        { id: 4, name: 'Charcoal Gray', brand: 'Sherwin-Williams', hex: '#374151', usage: 10, trend: 18 },
        { id: 5, name: 'Terracotta', brand: 'Valspar', hex: '#92400e', usage: 8, trend: 32 }
      ],
      monthlyActivity: [
        { month: 'January', year: '2024', jobs: 8, jobsChange: '+2', revenue: 5200, revenueChange: 15, avgTime: '2.5h', timeChange: -0.5, satisfaction: 94, performance: 'Excellent' },
        { month: 'December', year: '2023', jobs: 7, jobsChange: '+1', revenue: 4800, revenueChange: 8, avgTime: '2.8h', timeChange: 0.2, satisfaction: 92, performance: 'Good' },
        { month: 'November', year: '2023', jobs: 6, jobsChange: '-1', revenue: 4200, revenueChange: -5, avgTime: '3.1h', timeChange: 0.3, satisfaction: 88, performance: 'Average' },
        { month: 'October', year: '2023', jobs: 7, jobsChange: '+2', revenue: 5100, revenueChange: 12, avgTime: '2.9h', timeChange: -0.2, satisfaction: 90, performance: 'Good' },
        { month: 'September', year: '2023', jobs: 5, jobsChange: '-2', revenue: 3200, revenueChange: -8, avgTime: '3.3h', timeChange: 0.4, satisfaction: 85, performance: 'Average' },
        { month: 'August', year: '2023', jobs: 7, jobsChange: '+3', revenue: 4600, revenueChange: 18, avgTime: '2.7h', timeChange: -0.6, satisfaction: 93, performance: 'Excellent' }
      ]
    }
  },
  computed: {
    maxColorUsage() {
      return Math.max(...this.colors.map(c => c.usage))
    },
    totalColorUsage() {
      return this.colors.reduce((sum, color) => sum + color.usage, 0)
    }
  },
  methods: {
    generateReport() {
      // Simulate report generation
      // This part assumes standard DOM manipulation because $notify wasn't provided in the prompt context
      // but mimics the logic from the original file
      this.showToast('Performance report has been exported successfully', 'success')
    },
    
    refreshData() {
      this.refreshing = true
      
      // Simulate API call
      setTimeout(() => {
        this.refreshing = false
        
        // Update some data to show refresh
        this.overviewStats.completedJobs = Math.floor(Math.random() * 10) + 40
        this.overviewStats.satisfaction = Math.floor(Math.random() * 6) + 90
        
        this.showToast('Report data has been updated', 'info')
      }, 1500)
    },
    
    viewAllJobs() {
      this.showToast('Redirecting to jobs page...', 'info')
    },

    showToast(message, type) {
        // Simple console log or native alert if no Toast component library is active
        console.log(`[${type.toUpperCase()}] ${message}`)
        // If you have Sonner or Toast installed, call it here.
    }
  },
  mounted() {
    // Animate table rows on load
    setTimeout(() => {
      const rows = document.querySelectorAll('tbody tr')
      rows.forEach((row, index) => {
        row.style.opacity = '0'
        row.style.transform = 'translateX(20px)'
        setTimeout(() => {
          row.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)'
          row.style.opacity = '1'
          row.style.transform = 'translateX(0)'
        }, index * 100)
      })
    }, 300)
  }
}
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

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(31, 41, 55, 0.5);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .text-3xl {
    font-size: 1.75rem;
  }
}

@media print {
  .reports-page {
    background: white !important;
    color: black !important;
  }
  
  header, button {
    display: none !important;
  }
  
  table {
    border: 1px solid #ddd !important;
  }
  
  th, td {
    color: black !important;
    border-bottom: 1px solid #ddd !important;
  }
}
</style>