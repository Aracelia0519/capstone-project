<template>
  <div class="min-h-screen bg-gray-50 font-sans">
    <header class="bg-white border-b border-gray-200 p-6">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex-1">
          <h1 class="text-3xl font-bold text-gray-900 mb-1">Recruitment Applications</h1>
          <p class="text-gray-600 text-sm">Manage applications by department - Paint Business</p>
        </div>
        <div class="flex gap-3">
          <Button class="bg-blue-600 hover:bg-blue-700" @click="openNewApplicationModal">
            <span class="mr-2 text-lg">+</span>
            New Application
          </Button>
          <Button variant="outline">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
            </svg>
            Filter
          </Button>
        </div>
      </div>
    </header>

    <div class="bg-white border-b border-gray-200 px-2 py-2 overflow-x-auto">
      <div class="flex gap-1 min-w-max">
        <button 
          v-for="dept in departments" 
          :key="dept.id"
          @click="setActiveDepartment(dept.id)"
          :class="[
            'flex items-center gap-2 px-4 py-3 rounded-lg text-sm font-medium transition-colors border',
            activeDepartment === dept.id 
              ? 'bg-blue-600 text-white border-blue-600' 
              : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
          ]"
        >
          <span class="text-base">{{ dept.icon }}</span>
          <span>{{ dept.name }}</span>
          <span :class="[
            'px-2 py-0.5 rounded-full text-xs font-bold',
            activeDepartment === dept.id ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700'
          ]">
            {{ getDepartmentCount(dept.id) }}
          </span>
        </button>
      </div>
    </div>

    <div class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card class="p-4 flex items-center gap-4 transition-shadow hover:shadow-md">
          <div class="w-12 h-12 rounded-md bg-blue-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ activeDepartmentApplicants.length }}</div>
            <div class="text-sm text-gray-600">Total in {{ activeDepartmentName }}</div>
          </div>
          <div class="text-sm font-medium text-green-600">+{{ getDepartmentTrend(activeDepartment) }}%</div>
        </Card>

        <Card class="p-4 flex items-center gap-4 transition-shadow hover:shadow-md">
          <div class="w-12 h-12 rounded-md bg-green-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ shortlistedCount }}</div>
            <div class="text-sm text-gray-600">Shortlisted</div>
          </div>
          <div class="text-sm font-medium text-green-600">+{{ shortlistedTrend }}%</div>
        </Card>

        <Card class="p-4 flex items-center gap-4 transition-shadow hover:shadow-md">
          <div class="w-12 h-12 rounded-md bg-yellow-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ pendingCount }}</div>
            <div class="text-sm text-gray-600">Pending Review</div>
          </div>
          <div class="text-sm font-medium text-red-600">-{{ pendingTrend }}%</div>
        </Card>

        <Card class="p-4 flex items-center gap-4 transition-shadow hover:shadow-md">
          <div class="w-12 h-12 rounded-md bg-purple-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="flex-1">
            <div class="text-2xl font-bold text-gray-900">{{ interviewCount }}</div>
            <div class="text-sm text-gray-600">Interviews</div>
          </div>
          <div class="text-sm font-medium text-green-600">+{{ interviewTrend }}%</div>
        </Card>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 px-6 pb-8">
      <div class="lg:col-span-2 flex flex-col gap-6">
        <Card class="p-4 flex flex-col md:flex-row gap-4 items-center">
          <div class="relative flex-1 w-full">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
            <Input
              type="text"
              v-model="searchQuery"
              :placeholder="`Search ${activeDepartmentName} applicants...`"
              class="pl-10"
            />
          </div>

          <div class="flex gap-2 w-full md:w-auto">
            <Select v-model="selectedStatus">
              <SelectTrigger class="w-[180px]">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="pending">Pending</SelectItem>
                <SelectItem value="shortlisted">Shortlisted</SelectItem>
                <SelectItem value="interview">Interview</SelectItem>
                <SelectItem value="rejected">Rejected</SelectItem>
                <SelectItem value="hired">Hired</SelectItem>
              </SelectContent>
            </Select>

            <Button variant="outline" @click="refreshData">
              <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
              </svg>
              Refresh
            </Button>
          </div>
        </Card>

        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden flex flex-col flex-1">
          <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">{{ activeDepartmentName }} Applicants</h2>
            <div class="flex gap-2">
              <Button variant="outline" size="sm" @click="exportToCSV">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Export
              </Button>
              <Button variant="destructive" size="sm" @click="deleteSelected" :disabled="selectedApplicants.length === 0">
                <svg class="w-4 h-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete Selected
              </Button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow class="bg-gray-50 hover:bg-gray-50">
                  <TableHead class="w-12">
                    <Checkbox :checked="selectAll" @update:checked="toggleSelectAll" />
                  </TableHead>
                  <TableHead>Applicant</TableHead>
                  <TableHead>Position</TableHead>
                  <TableHead>Applied Date</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead>Rating</TableHead>
                  <TableHead>Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="applicant in paginatedApplicants" :key="applicant.id" 
                    :class="[selectedApplicants.includes(applicant.id) ? 'bg-blue-50' : '']">
                  <TableCell>
                    <Checkbox :checked="selectedApplicants.includes(applicant.id)" @update:checked="(val) => {
                      if(val) selectedApplicants.push(applicant.id)
                      else selectedApplicants = selectedApplicants.filter(id => id !== applicant.id)
                    }" />
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-3">
                      <Avatar class="h-10 w-10 text-white font-semibold" :style="{ background: getDepartmentColor(applicant.department) }">
                        <AvatarFallback class="text-white bg-inherit">{{ getInitials(applicant.name) }}</AvatarFallback>
                      </Avatar>
                      <div class="flex flex-col">
                        <span class="font-semibold text-gray-900">{{ applicant.name }}</span>
                        <span class="text-xs text-gray-500">{{ applicant.email }}</span>
                        <Badge variant="secondary" class="mt-1 w-fit text-[10px]">{{ applicant.department }}</Badge>
                      </div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge variant="outline" class="bg-gray-50">{{ applicant.position }}</Badge>
                  </TableCell>
                  <TableCell class="text-sm">{{ formatDate(applicant.appliedDate) }}</TableCell>
                  <TableCell>
                    <Badge :class="[
                       applicant.status === 'pending' ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100' :
                       applicant.status === 'shortlisted' ? 'bg-green-100 text-green-800 hover:bg-green-100' :
                       applicant.status === 'interview' ? 'bg-blue-100 text-blue-800 hover:bg-blue-100' :
                       applicant.status === 'hired' ? 'bg-green-200 text-green-900 hover:bg-green-200' :
                       'bg-red-100 text-red-800 hover:bg-red-100'
                    ]">
                      {{ getStatusText(applicant.status) }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-1">
                      <div class="flex">
                        <span v-for="n in 5" :key="n" :class="['text-sm', n <= applicant.rating ? 'text-yellow-400' : 'text-gray-300']">★</span>
                      </div>
                      <span class="text-xs text-gray-500 ml-1">{{ applicant.rating }}/5</span>
                    </div>
                  </TableCell>
                  <TableCell>
                    <div class="flex items-center gap-1">
                      <Button variant="ghost" size="icon" class="h-8 w-8 text-blue-600 bg-blue-50 hover:bg-blue-100 hover:text-blue-700" @click="viewApplicant(applicant)" title="View">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                          <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                      </Button>
                      <Button variant="ghost" size="icon" class="h-8 w-8 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 hover:text-yellow-800" @click="editApplicant(applicant)" title="Edit">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </Button>
                      <Button variant="ghost" size="icon" class="h-8 w-8 text-red-600 bg-red-50 hover:bg-red-100 hover:text-red-700" @click="deleteApplicant(applicant)" title="Delete">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>

          <div class="p-4 border-t border-gray-200 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-gray-600">
              Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredApplicants.length }} entries
            </div>
            <div class="flex items-center gap-2">
              <Button variant="outline" size="icon" class="h-8 w-8" @click="prevPage" :disabled="currentPage === 1">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </Button>
              
              <div class="flex gap-1">
                <Button
                  v-for="page in visiblePages"
                  :key="page"
                  variant="outline"
                  size="icon"
                  :class="['h-8 w-8', currentPage === page ? 'bg-blue-600 text-white hover:bg-blue-700 hover:text-white border-blue-600' : '']"
                  @click="goToPage(page)"
                >
                  {{ page }}
                </Button>
              </div>
              
              <Button variant="outline" size="icon" class="h-8 w-8" @click="nextPage" :disabled="currentPage === totalPages">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </Button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col gap-6">
        <Card class="p-4">
          <div class="mb-4">
            <h3 class="font-semibold text-gray-900">Department Overview</h3>
            <span class="text-sm text-gray-600">{{ activeDepartmentName }}</span>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg" v-for="stat in departmentStats" :key="stat.label">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" :style="{ background: stat.color + '20' }">
                <span :style="{ color: stat.color }">{{ stat.icon }}</span>
              </div>
              <div>
                <div class="font-bold text-gray-900 text-lg leading-tight">{{ stat.value }}</div>
                <div class="text-xs text-gray-500">{{ stat.label }}</div>
              </div>
            </div>
          </div>
        </Card>

        <Card class="p-4">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-gray-900">Recent Activity</h3>
            <button class="text-sm text-blue-600 hover:underline" @click="viewAllActivity">View All</button>
          </div>
          <div class="flex flex-col gap-4">
            <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
              <Avatar class="h-8 w-8">
                <AvatarFallback class="bg-gradient-to-br from-blue-500 to-purple-600 text-white text-xs">
                    {{ getInitials(activity.user) }}
                </AvatarFallback>
              </Avatar>
              <div class="flex-1">
                <div class="text-sm text-gray-800">
                  <span class="font-semibold">{{ activity.user }}</span> {{ activity.action }}
                  <span class="font-medium text-blue-600">{{ activity.applicant }}</span>
                </div>
                <div class="text-xs text-gray-500">{{ activity.time }}</div>
              </div>
            </div>
          </div>
        </Card>

        <Card class="p-4">
          <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div class="grid grid-cols-2 gap-3">
            <button class="flex flex-col items-center gap-2 p-4 border rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all group" @click="scheduleInterview">
              <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-700 text-center">Schedule Interview</span>
            </button>
            <button class="flex flex-col items-center gap-2 p-4 border rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all group" @click="sendEmail">
              <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-700 text-center">Send Email</span>
            </button>
            <button class="flex flex-col items-center gap-2 p-4 border rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all group" @click="generateReport">
              <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-700 text-center">Generate Report</span>
            </button>
            <button class="flex flex-col items-center gap-2 p-4 border rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all group" @click="addNote">
              <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
              </div>
              <span class="text-xs font-medium text-gray-700 text-center">Add Note</span>
            </button>
          </div>
        </Card>
      </div>
    </div>

    <Dialog v-model:open="showWizard">
        <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-y-auto p-0 gap-0">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-white sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg text-white flex items-center justify-center text-2xl font-bold" :style="{ background: getDepartmentColor(selectedApplicant?.department) }">
                    {{ selectedApplicant?.department?.charAt(0) }}
                    </div>
                    <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ selectedApplicant?.name }}</h2>
                    <p class="text-sm text-gray-600">{{ selectedApplicant?.position }} • {{ selectedApplicant?.department }}</p>
                    </div>
                </div>
                </div>

            <div class="p-4 border-b border-gray-200 flex justify-between relative bg-white">
                <div class="absolute top-1/2 left-4 right-4 h-0.5 bg-gray-200 -z-10 -translate-y-1/2"></div>
                <div v-for="step in steps" :key="step.id" class="flex flex-col items-center gap-2 bg-white px-2">
                    <div :class="[
                        'w-10 h-10 rounded-full flex items-center justify-center font-bold border-2 transition-colors',
                        currentStep >= step.id ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-100 text-gray-500 border-gray-200'
                    ]">
                        {{ step.id }}
                    </div>
                    <div :class="['text-xs font-medium', currentStep >= step.id ? 'text-gray-900' : 'text-gray-500']">{{ step.label }}</div>
                </div>
            </div>

            <div class="p-6">
                 <div v-if="currentStep === 1" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-16 h-16 rounded-lg text-white flex items-center justify-center text-2xl font-bold" :style="{ background: getDepartmentColor(selectedApplicant?.department) }">
                            {{ getInitials(selectedApplicant?.name) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ selectedApplicant?.name }}</h3>
                            <p class="text-gray-600">{{ selectedApplicant?.email }}</p>
                            <p class="text-gray-500 font-medium">{{ selectedApplicant?.phone }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <Card class="p-4 bg-gray-50 border-0">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Contact Information</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Address:</span>
                                    <span class="font-medium text-gray-900">{{ selectedApplicant?.address || 'Not specified' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Location:</span>
                                    <span class="font-medium text-gray-900">{{ selectedApplicant?.location || 'Remote' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Department:</span>
                                    <span class="font-medium text-gray-900">{{ selectedApplicant?.department }}</span>
                                </div>
                            </div>
                        </Card>

                        <Card class="p-4 bg-gray-50 border-0">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Application Details</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Applied For:</span>
                                    <span class="font-medium text-gray-900">{{ selectedApplicant?.position }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Applied Date:</span>
                                    <span class="font-medium text-gray-900">{{ formatDate(selectedApplicant?.appliedDate) }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Experience:</span>
                                    <span class="font-medium text-gray-900">{{ selectedApplicant?.experience }} years</span>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>

                <div v-if="currentStep === 2" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Experience & Skills</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Technical Skills</h4>
                        <div class="flex flex-wrap gap-2">
                            <Badge variant="secondary" class="bg-blue-100 text-blue-700 hover:bg-blue-200" v-for="skill in selectedApplicant?.skills" :key="skill">
                                {{ skill }}
                            </Badge>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Work Experience</h4>
                        <div class="space-y-4">
                            <div v-for="exp in selectedApplicant?.experienceDetails" :key="exp.company" class="flex gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 bg-blue-100 rounded-md flex items-center justify-center text-blue-600 shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-gray-900">{{ exp.company }}</h5>
                                    <p class="text-gray-600">{{ exp.role }}</p>
                                    <p class="text-sm text-gray-400">{{ exp.duration }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                 <div v-if="currentStep === 3" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Education & Certifications</h3>
                    
                    <div class="mb-6">
                        <h4 class="font-semibold text-gray-700 mb-3">Education</h4>
                        <div class="space-y-4">
                            <div v-for="edu in selectedApplicant?.education" :key="edu.degree" class="flex gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="w-10 h-10 bg-green-100 rounded-md flex items-center justify-center text-green-700 shrink-0">
                                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="font-bold text-gray-900">{{ edu.degree }}</h5>
                                    <p class="text-gray-600">{{ edu.institution }}</p>
                                    <p class="text-sm text-gray-400">{{ edu.year }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-gray-700 mb-3">Certifications</h4>
                        <div class="flex flex-wrap gap-2">
                             <Badge variant="secondary" class="bg-green-100 text-green-700 hover:bg-green-200" v-for="cert in selectedApplicant?.certifications" :key="cert">
                                {{ cert }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <div v-if="currentStep === 4" class="animate-in fade-in slide-in-from-bottom-2 duration-300">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Review & Decision</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <Card class="p-4 text-center bg-gray-50 border-0">
                            <h4 class="text-sm font-semibold text-gray-500 mb-2">Application Score</h4>
                            <div class="flex items-baseline justify-center gap-1 mb-2">
                                <span class="text-3xl font-bold text-gray-900">{{ selectedApplicant?.score || '85' }}</span>
                                <span class="text-gray-500">/ 100</span>
                            </div>
                            <p class="text-xs text-gray-500">Overall rating based on qualifications</p>
                        </Card>

                        <Card class="p-4 text-center bg-gray-50 border-0">
                            <h4 class="text-sm font-semibold text-gray-500 mb-2">Department Fit</h4>
                             <Progress :model-value="selectedApplicant?.fitScore" class="h-2 mb-2 bg-gray-200" />
                            <p class="text-xs text-gray-500">{{ selectedApplicant?.fitScore }}% match with {{ selectedApplicant?.department }}</p>
                        </Card>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                         <h4 class="font-semibold text-gray-700 mb-3">Make Your Decision</h4>
                         <div class="flex flex-col md:flex-row gap-3 mb-4">
                             <Button variant="outline" class="flex-1 bg-red-50 text-red-600 border-red-200 hover:bg-red-100 hover:text-red-700" @click="makeDecision('rejected')">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                                Reject
                             </Button>
                             <Button variant="outline" class="flex-1 bg-green-50 text-green-700 border-green-200 hover:bg-green-100 hover:text-green-800" @click="makeDecision('shortlisted')">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Shortlist
                             </Button>
                             <Button class="flex-1 bg-blue-600 hover:bg-blue-700" @click="makeDecision('hired')">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745a3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Hire
                             </Button>
                         </div>

                         <div>
                             <label class="block text-sm font-semibold text-gray-700 mb-2">Review Notes</label>
                             <Textarea v-model="reviewNotes" placeholder="Add your review notes here..." rows="4" />
                         </div>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-200 flex justify-between bg-white sticky bottom-0 z-10">
                <Button variant="outline" @click="prevStep" :disabled="currentStep === 1" v-if="currentStep > 1">
                    <svg class="w-4 h-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Previous
                </Button>
                <div class="flex-1"></div>
                <Button class="bg-blue-600 hover:bg-blue-700" @click="nextStep" v-if="currentStep < 4">
                    Next
                    <svg class="w-4 h-4 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                </Button>
                <Button class="bg-green-600 hover:bg-green-700" @click="completeReview" v-if="currentStep === 4">
                    Complete Review
                    <svg class="w-4 h-4 ml-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </Button>
            </div>
        </DialogContent>
    </Dialog>

    <div v-if="showToast" :class="[
        'fixed bottom-4 right-4 bg-white rounded-lg shadow-lg border-l-4 p-4 z-50 flex items-center gap-3 min-w-[300px] animate-in slide-in-from-right duration-300',
        toastType === 'success' ? 'border-green-500' : 
        toastType === 'error' ? 'border-red-500' : 'border-blue-500'
    ]">
      <div :class="[
          'shrink-0',
          toastType === 'success' ? 'text-green-500' : 
          toastType === 'error' ? 'text-red-500' : 'text-blue-500'
      ]">
         <svg v-if="toastType === 'success'" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg v-if="toastType === 'error'" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <svg v-if="toastType === 'info'" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
               <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
          </svg>
      </div>
      <div class="flex-1">
          <div class="text-sm font-semibold text-gray-900">{{ toastTitle }}</div>
          <div class="text-sm text-gray-600">{{ toastMessage }}</div>
      </div>
      <button class="text-gray-400 hover:text-gray-600" @click="showToast = false">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Textarea } from '@/components/ui/textarea'
import { Progress } from '@/components/ui/progress'

// Departments for Paint Business
const departments = ref([
  { id: 'administration', name: 'Administration', icon: '📋', color: '#3B82F6' },
  { id: 'distributor', name: 'Distributor Assistant', icon: '🚚', color: '#10B981' },
  { id: 'finance', name: 'Finance', icon: '💰', color: '#8B5CF6' },
  { id: 'hr', name: 'Human Resources', icon: '👥', color: '#EC4899' },
  { id: 'it', name: 'IT', icon: '💻', color: '#6366F1' },
  { id: 'logistics', name: 'Logistics', icon: '📦', color: '#F59E0B' },
  { id: 'marketing', name: 'Marketing', icon: '📢', color: '#EF4444' },
  { id: 'operations', name: 'Operations', icon: '🏭', color: '#14B8A6' },
  { id: 'sales', name: 'Sales', icon: '📈', color: '#84CC16' }
])

// Sample data
const applicants = ref([
  {
    id: 1,
    name: "John Doe",
    email: "john.doe@example.com",
    phone: "+1 (555) 123-4567",
    position: "Finance Manager",
    department: "Finance",
    appliedDate: "2024-01-15",
    status: "pending",
    rating: 4,
    experience: 5,
    score: 85,
    fitScore: 90,
    address: "123 Main St, San Francisco, CA",
    location: "San Francisco, CA",
    skills: ["Financial Analysis", "Budgeting", "Accounting", "Excel", "QuickBooks"],
    experienceDetails: [
      { company: "Financial Corp", role: "Finance Lead", duration: "2020-2024" },
      { company: "Bank Solutions", role: "Senior Analyst", duration: "2018-2020" }
    ],
    education: [
      { degree: "Bachelor of Finance", institution: "Stanford University", year: "2018" }
    ],
    certifications: ["CFA Level I", "CPA Certified"]
  },
  {
    id: 2,
    name: "Jane Smith",
    email: "jane.smith@example.com",
    phone: "+1 (555) 987-6543",
    position: "HR Specialist",
    department: "Human Resources",
    appliedDate: "2024-01-14",
    status: "shortlisted",
    rating: 5,
    experience: 3,
    score: 92,
    fitScore: 95,
    address: "456 Oak Ave, New York, NY",
    location: "New York, NY",
    skills: ["Recruitment", "Employee Relations", "HR Policies", "Training", "Compliance"],
    experienceDetails: [
      { company: "HR Solutions", role: "HR Specialist", duration: "2021-2024" },
      { company: "Talent Agency", role: "Recruiter", duration: "2019-2021" }
    ],
    education: [
      { degree: "Master of HR Management", institution: "NYU", year: "2019" }
    ],
    certifications: ["SHRM-CP", "PHR"]
  },
  {
    id: 3,
    name: "Robert Johnson",
    email: "robert.j@example.com",
    phone: "+1 (555) 456-7890",
    position: "IT Support Specialist",
    department: "IT",
    appliedDate: "2024-01-13",
    status: "interview",
    rating: 4,
    experience: 4,
    score: 78,
    fitScore: 85,
    address: "789 Pine St, Austin, TX",
    location: "Austin, TX",
    skills: ["Network Administration", "Help Desk", "Windows Server", "Linux", "Cybersecurity"],
    experienceDetails: [
      { company: "Tech Support Inc", role: "IT Specialist", duration: "2019-2024" }
    ],
    education: [
      { degree: "BS in Information Technology", institution: "UT Austin", year: "2019" }
    ],
    certifications: ["CompTIA A+", "Network+", "Security+"]
  },
  {
    id: 4,
    name: "Sarah Williams",
    email: "sarah.w@example.com",
    phone: "+1 (555) 321-0987",
    position: "Sales Executive",
    department: "Sales",
    appliedDate: "2024-01-12",
    status: "hired",
    rating: 5,
    experience: 6,
    score: 95,
    fitScore: 98,
    address: "101 Maple Dr, Boston, MA",
    location: "Boston, MA",
    skills: ["Sales Strategy", "Client Relations", "Negotiation", "CRM", "Market Analysis"],
    experienceDetails: [
      { company: "Sales Pro", role: "Senior Sales Executive", duration: "2020-2024" },
      { company: "Business Corp", role: "Sales Representative", duration: "2018-2020" }
    ],
    education: [
      { degree: "BA in Business Administration", institution: "Boston University", year: "2018" }
    ],
    certifications: ["Salesforce Certified", "Professional Selling"]
  },
  {
    id: 5,
    name: "Michael Brown",
    email: "michael.b@example.com",
    phone: "+1 (555) 654-3210",
    position: "Logistics Coordinator",
    department: "Logistics",
    appliedDate: "2024-01-11",
    status: "rejected",
    rating: 3,
    experience: 2,
    score: 65,
    fitScore: 70,
    address: "222 Cedar Ln, Seattle, WA",
    location: "Seattle, WA",
    skills: ["Supply Chain", "Inventory Management", "Shipping", "Warehouse", "Logistics"],
    experienceDetails: [
      { company: "Logistics Co", role: "Coordinator", duration: "2022-2024" }
    ],
    education: [
      { degree: "Associate in Logistics", institution: "Community College", year: "2022" }
    ],
    certifications: ["CLTD", "CPIM"]
  },
  {
    id: 6,
    name: "Emily Davis",
    email: "emily.d@example.com",
    phone: "+1 (555) 234-5678",
    position: "Marketing Coordinator",
    department: "Marketing",
    appliedDate: "2024-01-10",
    status: "pending",
    rating: 4,
    experience: 3,
    score: 82,
    fitScore: 88,
    address: "333 Birch St, Chicago, IL",
    location: "Chicago, IL",
    skills: ["Social Media", "Content Creation", "SEO", "Analytics", "Campaign Management"],
    experienceDetails: [
      { company: "Marketing Agency", role: "Coordinator", duration: "2021-2024" }
    ],
    education: [
      { degree: "BA in Marketing", institution: "UIC", year: "2021" }
    ],
    certifications: ["Google Analytics", "HubSpot Certified"]
  },
  {
    id: 7,
    name: "David Wilson",
    email: "david.w@example.com",
    phone: "+1 (555) 876-5432",
    position: "Operations Manager",
    department: "Operations",
    appliedDate: "2024-01-09",
    status: "shortlisted",
    rating: 5,
    experience: 8,
    score: 91,
    fitScore: 92,
    address: "444 Elm St, Houston, TX",
    location: "Houston, TX",
    skills: ["Process Improvement", "Team Management", "Quality Control", "Project Management", "Lean Six Sigma"],
    experienceDetails: [
      { company: "Manufacturing Co", role: "Operations Manager", duration: "2018-2024" },
      { company: "Production Inc", role: "Supervisor", duration: "2016-2018" }
    ],
    education: [
      { degree: "BS in Operations Management", institution: "Texas A&M", year: "2016" }
    ],
    certifications: ["Six Sigma Black Belt", "PMP"]
  },
  {
    id: 8,
    name: "Lisa Anderson",
    email: "lisa.a@example.com",
    phone: "+1 (555) 345-6789",
    position: "Administrative Assistant",
    department: "Administration",
    appliedDate: "2024-01-08",
    status: "interview",
    rating: 4,
    experience: 4,
    score: 79,
    fitScore: 82,
    address: "555 Oak St, Miami, FL",
    location: "Miami, FL",
    skills: ["Office Management", "Scheduling", "Documentation", "Communication", "Organization"],
    experienceDetails: [
      { company: "Executive Office", role: "Administrative Assistant", duration: "2020-2024" }
    ],
    education: [
      { degree: "Associate in Business Admin", institution: "Miami Dade College", year: "2020" }
    ],
    certifications: ["Microsoft Office Specialist", "Professional Secretary"]
  }
])

// Reactive data
const activeDepartment = ref('administration')
const searchQuery = ref('')
const selectedStatus = ref('all')
const selectedApplicants = ref([])
const selectAll = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10
const showWizard = ref(false)
const selectedApplicant = ref(null)
const currentStep = ref(1)
const reviewNotes = ref('')
const showToast = ref(false)
const toastType = ref('success')
const toastTitle = ref('')
const toastMessage = ref('')

const steps = ref([
  { id: 1, label: 'Personal Info' },
  { id: 2, label: 'Experience' },
  { id: 3, label: 'Education' },
  { id: 4, label: 'Decision' }
])

const recentActivities = ref([
  { id: 1, user: 'John HR', action: 'reviewed', applicant: 'Michael Brown', time: '10 min ago' },
  { id: 2, user: 'Sarah M', action: 'shortlisted', applicant: 'Emily Davis', time: '25 min ago' },
  { id: 3, user: 'Robert T', action: 'scheduled interview with', applicant: 'David Wilson', time: '1 hour ago' },
  { id: 4, user: 'Lisa K', action: 'added notes to', applicant: 'John Doe', time: '2 hours ago' }
])

// Computed properties
const activeDepartmentName = computed(() => {
  const dept = departments.value.find(d => d.id === activeDepartment.value)
  return dept ? dept.name : 'All Departments'
})

const activeDepartmentApplicants = computed(() => {
  return applicants.value.filter(applicant => 
    applicant.department.toLowerCase() === activeDepartment.value
  )
})

const filteredApplicants = computed(() => {
  let filtered = activeDepartmentApplicants.value
  
  // Apply search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(applicant => 
      applicant.name.toLowerCase().includes(query) ||
      applicant.email.toLowerCase().includes(query) ||
      applicant.position.toLowerCase().includes(query)
    )
  }
  
  // Apply status filter
  if (selectedStatus.value !== 'all') {
    filtered = filtered.filter(applicant => applicant.status === selectedStatus.value)
  }
  
  return filtered
})

const paginatedApplicants = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredApplicants.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(filteredApplicants.value.length / itemsPerPage))
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, filteredApplicants.value.length))

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  
  if (totalPages.value <= maxVisible) {
    for (let i = 1; i <= totalPages.value; i++) {
      pages.push(i)
    }
  } else {
    let start = Math.max(1, currentPage.value - 2)
    let end = Math.min(totalPages.value, start + maxVisible - 1)
    
    if (end - start < maxVisible - 1) {
      start = Math.max(1, end - maxVisible + 1)
    }
    
    for (let i = start; i <= end; i++) {
      pages.push(i)
    }
  }
  
  return pages
})

const departmentStats = computed(() => {
  const deptApplicants = activeDepartmentApplicants.value
  const total = deptApplicants.length
  const shortlisted = deptApplicants.filter(a => a.status === 'shortlisted').length
  const interviews = deptApplicants.filter(a => a.status === 'interview').length
  const pending = deptApplicants.filter(a => a.status === 'pending').length
  const avgRating = total > 0 
    ? (deptApplicants.reduce((sum, a) => sum + a.rating, 0) / total).toFixed(1)
    : '0.0'
  
  return [
    { icon: '👥', label: 'Total Applicants', value: total, color: '#3B82F6' },
    { icon: '⭐', label: 'Avg. Rating', value: avgRating, color: '#F59E0B' },
    { icon: '✅', label: 'Shortlisted', value: shortlisted, color: '#10B981' },
    { icon: '📅', label: 'Interviews', value: interviews, color: '#8B5CF6' },
    { icon: '⏳', label: 'Pending', value: pending, color: '#6366F1' },
    { icon: '🎯', label: 'Avg. Score', value: total > 0 
      ? Math.round(deptApplicants.reduce((sum, a) => sum + a.score, 0) / total)
      : 0, color: '#EC4899' }
  ]
})

const shortlistedCount = computed(() => {
  return activeDepartmentApplicants.value.filter(a => a.status === 'shortlisted').length
})

const pendingCount = computed(() => {
  return activeDepartmentApplicants.value.filter(a => a.status === 'pending').length
})

const interviewCount = computed(() => {
  return activeDepartmentApplicants.value.filter(a => a.status === 'interview').length
})

const shortlistedTrend = computed(() => Math.floor(Math.random() * 15) + 5)
const pendingTrend = computed(() => Math.floor(Math.random() * 10) + 1)
const interviewTrend = computed(() => Math.floor(Math.random() * 20) + 8)

// Methods
const setActiveDepartment = (deptId) => {
  activeDepartment.value = deptId
  currentPage.value = 1
  selectedApplicants.value = []
  selectAll.value = false
}

const getDepartmentCount = (deptId) => {
  return applicants.value.filter(a => a.department.toLowerCase() === deptId).length
}

const getDepartmentColor = (deptName) => {
  const dept = departments.value.find(d => d.name === deptName)
  return dept ? dept.color : '#6B7280'
}

const getDepartmentTrend = (deptId) => {
  const trends = {
    administration: 12, distributor: 8, finance: 15, hr: 10,
    it: 18, logistics: 5, marketing: 22, operations: 7, sales: 25
  }
  return trends[deptId] || 10
}

const getInitials = (name) => {
  if (!name) return 'JD'
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const getStatusText = (status) => {
  const statusMap = {
    'pending': 'Pending Review',
    'shortlisted': 'Shortlisted',
    'interview': 'Interview',
    'hired': 'Hired',
    'rejected': 'Rejected'
  }
  return statusMap[status] || status
}

const toggleSelectAll = (checked) => {
  selectAll.value = checked
  if (checked) {
    selectedApplicants.value = filteredApplicants.value.map(a => a.id)
  } else {
    selectedApplicants.value = []
  }
}

const viewApplicant = (applicant) => {
  selectedApplicant.value = applicant
  currentStep.value = 1
  showWizard.value = true
}

const editApplicant = (applicant) => {
  showToastMessage('info', 'Edit Feature', `Edit functionality for ${applicant.name} would be implemented here.`)
}

const deleteApplicant = (applicant) => {
  if (confirm(`Are you sure you want to delete ${applicant.name}'s application?`)) {
    applicants.value = applicants.value.filter(a => a.id !== applicant.id)
    showToastMessage('success', 'Deleted', `Application for ${applicant.name} has been deleted.`)
  }
}

const deleteSelected = () => {
  if (selectedApplicants.value.length === 0) return
  if (confirm(`Are you sure you want to delete ${selectedApplicants.value.length} selected applicants?`)) {
    applicants.value = applicants.value.filter(a => !selectedApplicants.value.includes(a.id))
    selectedApplicants.value = []
    selectAll.value = false
    showToastMessage('success', 'Deleted', `${selectedApplicants.value.length} applicants have been deleted.`)
  }
}

const prevStep = () => { if (currentStep.value > 1) currentStep.value-- }
const nextStep = () => { if (currentStep.value < steps.value.length) currentStep.value++ }

const makeDecision = (decision) => {
  if (selectedApplicant.value) {
    selectedApplicant.value.status = decision
    const applicant = applicants.value.find(a => a.id === selectedApplicant.value.id)
    if (applicant) applicant.status = decision
    showToastMessage('success', 'Decision Updated', `Application ${decision} successfully.`)
  }
}

const completeReview = () => {
  showWizard.value = false
  showToastMessage('success', 'Review Complete', `Review for ${selectedApplicant.value.name} has been completed.`)
  selectedApplicant.value = null
  currentStep.value = 1
  reviewNotes.value = ''
}

const openNewApplicationModal = () => showToastMessage('info', 'New Application', 'New application modal would open here.')
const refreshData = () => showToastMessage('success', 'Refreshed', 'Data has been refreshed.')
const exportToCSV = () => showToastMessage('success', 'Export Started', 'Exporting data to CSV...')
const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }
const goToPage = (page) => currentPage.value = page
const viewAllActivity = () => showToastMessage('info', 'Activity', 'Viewing all activity...')
const scheduleInterview = () => showToastMessage('success', 'Interview', 'Schedule interview dialog would open.')
const sendEmail = () => showToastMessage('success', 'Email', 'Email composer would open.')
const generateReport = () => showToastMessage('success', 'Report', 'Generating department report...')
const addNote = () => showToastMessage('info', 'Note', 'Add note dialog would open.')

const showToastMessage = (type, title, message) => {
  toastType.value = type
  toastTitle.value = title
  toastMessage.value = message
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 3000)
}

onMounted(() => {
  setTimeout(() => { showToastMessage('success', 'Welcome', 'Recruitment dashboard loaded successfully!') }, 1000)
})
</script>