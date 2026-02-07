<template>
  <div class="positions-roles p-4 md:p-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Positions & Roles</h1>
        <p class="text-gray-600">Define job roles and responsibilities within the organization</p>
      </div>
      <Button 
        @click="startNewPosition" 
        class="mt-4 md:mt-0 bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 shadow-md hover:shadow-lg border-0"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Position
      </Button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <Card class="bg-gradient-to-br from-white to-blue-50 border-blue-100 shadow-md">
        <CardContent class="p-6 flex items-center">
          <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl mr-4 shadow-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ statistics.total_positions || 0 }}</h3>
            <p class="text-gray-600 text-sm">Total Positions</p>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-white to-green-50 border-green-100 shadow-md">
        <CardContent class="p-6 flex items-center">
          <div class="p-3 bg-gradient-to-br from-green-500 to-green-600 rounded-xl mr-4 shadow-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ statistics.active_positions || 0 }}</h3>
            <p class="text-gray-600 text-sm">Active Positions</p>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-gradient-to-br from-white to-purple-50 border-purple-100 shadow-md">
        <CardContent class="p-6 flex items-center">
          <div class="p-3 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl mr-4 shadow-sm">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <h3 class="text-2xl font-bold text-gray-800 mb-1">{{ statistics.departments_count || 0 }}</h3>
            <p class="text-gray-600 text-sm">Departments</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 bg-gradient-to-r from-white to-gray-50 border-gray-200">
      <CardContent class="p-4 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
          <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <Input 
            v-model="searchQuery" 
            @input="searchPositions" 
            type="text" 
            placeholder="Search positions by title, department, or description..." 
            class="pl-10 w-full"
          />
        </div>
        <div class="w-full md:w-48">
          <Select v-model="selectedDepartment" @update:modelValue="fetchPositions">
            <SelectTrigger>
              <SelectValue placeholder="All Departments" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Departments</SelectItem>
              <SelectItem v-for="dept in departments" :key="dept.name" :value="dept.name">
                {{ dept.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="w-full md:w-48">
          <Select v-model="selectedStatus" @update:modelValue="fetchPositions">
            <SelectTrigger>
              <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Status</SelectItem>
              <SelectItem value="active">Active</SelectItem>
              <SelectItem value="inactive">Inactive</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </CardContent>
    </Card>

    <div v-if="loading" class="flex justify-center items-center p-12">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading positions...</p>
      </div>
    </div>

    <div v-else-if="error" class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-xl p-8 text-center shadow-sm">
      <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.17 16.5c-.77.833.192 2.5 1.732 2.5z" />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-red-800 mb-2">Error Loading Positions</h3>
      <p class="text-red-600 mb-6 max-w-md mx-auto">{{ error }}</p>
      <Button @click="fetchPositions" class="bg-gradient-to-r from-red-600 to-red-700 text-white hover:from-red-700 hover:to-red-800 shadow-md">
        Retry Loading
      </Button>
    </div>

    <div v-else-if="positions.length === 0 && !loading" class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-sm p-12 text-center border border-gray-200">
      <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-800 mb-3">No Positions Found</h3>
      <p class="text-gray-600 mb-8 max-w-md mx-auto">Start building your team structure by creating your first position.</p>
      <Button @click="startNewPosition" class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 shadow-md h-auto py-3">
        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Create Your First Position
      </Button>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card v-for="position in positions" :key="position.id" class="group bg-gradient-to-br from-white to-gray-50 border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div>
              <div class="flex items-center mb-2">
                <div class="p-2 rounded-xl mr-3 shadow-sm" :class="position.department_color.bg">
                  <svg class="w-5 h-5" :class="position.department_color.text" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition-colors">{{ position.title }}</h3>
                  <Badge 
                    :class="['text-xs px-2 py-1 rounded-full font-medium shadow-none', position.status === 'active' ? 'bg-gradient-to-r from-green-100 to-green-50 text-green-700 border-green-200' : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 border-gray-200']"
                    variant="outline"
                  >
                    {{ position.status }}
                  </Badge>
                </div>
              </div>
              <span class="inline-block px-3 py-1 text-xs font-medium rounded-full" :class="position.department_color.badge">
                {{ position.department }}
              </span>
            </div>
            <div class="flex space-x-1">
              <Button variant="ghost" size="icon" @click="editPosition(position)" class="h-8 w-8 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </Button>
              <Button variant="ghost" size="icon" @click="deletePosition(position.id)" class="h-8 w-8 text-gray-400 hover:text-red-600 hover:bg-red-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </Button>
            </div>
          </div>
          
          <div class="mb-4">
            <p class="text-sm text-gray-600 mb-4 line-clamp-2 min-h-[40px]">{{ position.description }}</p>
            
            <div v-if="position.min_salary && position.max_salary" class="flex items-center text-sm text-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 px-3 py-2 rounded-lg border border-blue-100">
              <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="font-medium">${{ formatSalary(position.min_salary) }} - ${{ formatSalary(position.max_salary) }}</span>
            </div>

            <div class="flex items-center text-xs text-gray-500 mt-4 pt-4 border-t border-gray-100">
              <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Created {{ formatDate(position.created_at) }} by {{ position.creator?.name || 'System' }}</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <div v-if="positions.length > 0 && pagination.last_page > 1" class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 p-4 bg-gradient-to-r from-white to-gray-50 rounded-xl shadow-sm border border-gray-200">
      <div class="text-sm text-gray-600">
        Showing {{ positions.length }} of {{ pagination.total }} positions
      </div>
      <div class="flex items-center space-x-2">
        <Button 
          variant="outline" 
          @click="prevPage" 
          :disabled="pagination.current_page === 1" 
          class="flex items-center"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Previous
        </Button>
        <div class="flex items-center space-x-1">
          <span class="px-3 py-1 bg-blue-600 text-white rounded-lg font-medium text-sm">{{ pagination.current_page }}</span>
          <span class="text-gray-600 text-sm">of {{ pagination.last_page }}</span>
        </div>
        <Button 
          variant="outline" 
          @click="nextPage" 
          :disabled="pagination.current_page === pagination.last_page" 
          class="flex items-center"
        >
          Next
          <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Button>
      </div>
    </div>

    <Dialog v-model:open="showPositionWizard">
      <DialogContent class="max-w-4xl p-0 overflow-hidden max-h-[90vh] flex flex-col gap-0 [&>button]:hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 p-4 md:p-6 flex-shrink-0">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <DialogTitle class="text-xl md:text-2xl font-bold text-white mb-1 truncate">
                {{ editingPosition ? 'Edit Position' : 'Create New Position' }}
              </DialogTitle>
              <DialogDescription class="text-blue-100 text-sm md:text-base truncate">
                Define roles and responsibilities for your team
              </DialogDescription>
            </div>
            <button @click="closeWizard" class="ml-4 p-2 text-white hover:bg-white/20 rounded-full transition-colors flex-shrink-0">
              <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <div class="mt-6 md:mt-8">
            <div class="flex items-center justify-center">
              <div class="flex items-center flex-wrap justify-center gap-2">
                <div class="flex items-center">
                  <div :class="['w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300', 
                    currentStep >= 1 ? 'bg-white border-white text-blue-600' : 'border-blue-300 text-blue-300']">
                    <span class="font-semibold text-sm md:text-base">1</span>
                  </div>
                  <div :class="['ml-1 md:ml-2 text-xs md:text-sm font-medium transition-all duration-300 whitespace-nowrap', 
                    currentStep >= 1 ? 'text-white' : 'text-blue-300']">Basic Info</div>
                </div>
                
                <div :class="['w-8 h-1 md:w-16 md:h-1 mx-1 md:mx-2 transition-all duration-300', 
                  currentStep >= 2 ? 'bg-white' : 'bg-blue-300']"></div>
                
                <div class="flex items-center">
                  <div :class="['w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300', 
                    currentStep >= 2 ? 'bg-white border-white text-blue-600' : 'border-blue-300 text-blue-300']">
                    <span class="font-semibold text-sm md:text-base">2</span>
                  </div>
                  <div :class="['ml-1 md:ml-2 text-xs md:text-sm font-medium transition-all duration-300 whitespace-nowrap', 
                    currentStep >= 2 ? 'text-white' : 'text-blue-300']">Details</div>
                </div>
                
                <div :class="['w-8 h-1 md:w-16 md:h-1 mx-1 md:mx-2 transition-all duration-300', 
                  currentStep >= 3 ? 'bg-white' : 'bg-blue-300']"></div>
                
                <div class="flex items-center">
                  <div :class="['w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300', 
                    currentStep >= 3 ? 'bg-white border-white text-blue-600' : 'border-blue-300 text-blue-300']">
                    <span class="font-semibold text-sm md:text-base">3</span>
                  </div>
                  <div :class="['ml-1 md:ml-2 text-xs md:text-sm font-medium transition-all duration-300 whitespace-nowrap', 
                    currentStep >= 3 ? 'text-white' : 'text-blue-300']">Review</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex-1 overflow-y-auto p-4 md:p-8">
          <div v-if="currentStep === 1" class="space-y-4 md:space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Position Title & Department</h3>
              <p class="text-gray-600 mb-4 md:mb-6 text-sm md:text-base">Start by providing the basic information about the position.</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Position Title
                  </label>
                  <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <Input v-model="positionForm.title" type="text" required 
                      class="pl-10 w-full"
                      placeholder="e.g., Senior Software Engineer, HR Manager" />
                  </div>
                  <p class="text-xs text-gray-500 mt-2">Enter a clear and descriptive title for this position</p>
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Department
                  </label>
                  <div class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3">
                    <button v-for="dept in allDepartments.slice(0, 9)" :key="dept"
                      @click="positionForm.department = dept"
                      :class="['px-3 py-2 md:px-4 md:py-3 rounded-lg border transition-all text-xs md:text-sm font-medium', 
                      positionForm.department === dept 
                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' 
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                      {{ dept }}
                    </button>
                    <button @click="positionForm.department = 'other'; showCustomDept = true"
                      :class="['px-3 py-2 md:px-4 md:py-3 rounded-lg border transition-all text-xs md:text-sm font-medium col-span-2 md:col-span-1', 
                      positionForm.department === 'other' 
                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' 
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                      + Custom Department
                    </button>
                  </div>
                  
                  <div v-if="showCustomDept" class="mt-4">
                    <Input v-model="positionForm.custom_department" type="text" required
                      class="border-blue-300 focus:border-blue-500"
                      placeholder="Enter custom department name..." />
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="currentStep === 2" class="space-y-4 md:space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Position Details</h3>
              <p class="text-gray-600 mb-4 md:mb-6 text-sm md:text-base">Define the responsibilities and compensation for this role.</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Role Description
                  </label>
                  <Textarea v-model="positionForm.description" rows="4" required
                    class="resize-none"
                    placeholder="Describe the key responsibilities, requirements, and expectations for this position..." />
                  <p class="text-xs text-gray-500 mt-2">Be specific about duties, required skills, and daily tasks</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Salary Range</label>
                    <div class="space-y-3">
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 z-10">$</span>
                        <Input v-model="positionForm.min_salary" type="number" step="0.01" min="0"
                          class="pl-8"
                          placeholder="Minimum salary" />
                      </div>
                      <div class="text-center text-gray-400">to</div>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 z-10">$</span>
                        <Input v-model="positionForm.max_salary" type="number" step="0.01" min="0"
                          class="pl-8"
                          placeholder="Maximum salary" />
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="grid grid-cols-2 gap-3">
                      <button @click="positionForm.status = 'active'"
                        :class="['px-3 py-2 md:px-4 md:py-3 rounded-lg border transition-all text-sm font-medium', 
                        positionForm.status === 'active' 
                          ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-500 text-green-700 ring-2 ring-green-200' 
                          : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                        <div class="flex items-center justify-center">
                          <div class="w-2 h-2 rounded-full bg-green-500 mr-2"></div>
                          Active
                        </div>
                      </button>
                      <button @click="positionForm.status = 'inactive'"
                        :class="['px-3 py-2 md:px-4 md:py-3 rounded-lg border transition-all text-sm font-medium', 
                        positionForm.status === 'inactive' 
                          ? 'bg-gradient-to-r from-gray-100 to-gray-50 border-gray-400 text-gray-700 ring-2 ring-gray-200' 
                          : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                        <div class="flex items-center justify-center">
                          <div class="w-2 h-2 rounded-full bg-gray-500 mr-2"></div>
                          Inactive
                        </div>
                      </button>
                    </div>
                  </div>
                </div>

                <div v-if="positionForm.department === 'Human Resources'" class="mt-6 pt-6 border-t border-gray-200">
                  <h4 class="text-lg font-semibold text-gray-800 mb-4">Accessibility Settings</h4>
                  <p class="text-gray-600 mb-4 text-sm md:text-base">Select which sidebar items this position can access:</p>
                  
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3">
                    <div v-for="item in accessibilityOptions" :key="item.id"
                      class="flex items-center p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                      @click="toggleAccessibilityItem(item.id)">
                      <div class="flex items-center h-5">
                        <Checkbox 
                          :id="`accessibility-${item.id}`" 
                          :checked="positionForm.accessibility.includes(item.id)"
                          @update:checked="toggleAccessibilityItem(item.id)"
                        />
                      </div>
                      <label :for="`accessibility-${item.id}`" 
                        class="ml-3 text-sm font-medium text-gray-700 cursor-pointer select-none">
                        {{ item.label }}
                      </label>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 mt-2">
                    Note: These settings determine which HR sidebar items will be accessible to users assigned to this position.
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="currentStep === 3" class="space-y-4 md:space-y-6">
            <div>
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Review & Confirm</h3>
              <p class="text-gray-600 mb-4 md:mb-6 text-sm md:text-base">Please review all the information before saving.</p>
              
              <div class="bg-gradient-to-br from-gray-50 to-blue-50 rounded-xl p-4 md:p-6 space-y-3 md:space-y-4">
                <div class="flex flex-col md:flex-row md:items-start">
                  <div class="w-full md:w-32 text-sm font-medium text-gray-600 mb-1 md:mb-0">Title:</div>
                  <div class="flex-1 font-medium text-gray-800 break-words">{{ positionForm.title }}</div>
                </div>
                <div class="flex flex-col md:flex-row md:items-start">
                  <div class="w-full md:w-32 text-sm font-medium text-gray-600 mb-1 md:mb-0">Department:</div>
                  <div class="flex-1">
                    <span class="inline-block px-3 py-1 bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 rounded-full text-sm font-medium">
                      {{ positionForm.department === 'other' ? positionForm.custom_department : positionForm.department }}
                    </span>
                  </div>
                </div>
                <div class="flex flex-col md:flex-row md:items-start">
                  <div class="w-full md:w-32 text-sm font-medium text-gray-600 mb-1 md:mb-0">Status:</div>
                  <div class="flex-1">
                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', 
                      positionForm.status === 'active' 
                        ? 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-700' 
                        : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700']">
                      <span :class="['w-2 h-2 rounded-full mr-2', 
                        positionForm.status === 'active' ? 'bg-green-500' : 'bg-gray-500']"></span>
                      {{ positionForm.status }}
                    </span>
                  </div>
                </div>
                <div v-if="positionForm.min_salary || positionForm.max_salary" class="flex flex-col md:flex-row md:items-start">
                  <div class="w-full md:w-32 text-sm font-medium text-gray-600 mb-1 md:mb-0">Salary Range:</div>
                  <div class="flex-1 font-medium text-gray-800">
                    <span v-if="positionForm.min_salary && positionForm.max_salary">
                      ${{ formatSalary(positionForm.min_salary) }} - ${{ formatSalary(positionForm.max_salary) }}
                    </span>
                    <span v-else-if="positionForm.min_salary">
                      From ${{ formatSalary(positionForm.min_salary) }}
                    </span>
                    <span v-else-if="positionForm.max_salary">
                      Up to ${{ formatSalary(positionForm.max_salary) }}
                    </span>
                  </div>
                </div>
                
                <div v-if="positionForm.department === 'Human Resources'" class="flex flex-col md:flex-row md:items-start">
                  <div class="w-full md:w-32 text-sm font-medium text-gray-600 mb-1 md:mb-0">Accessibility:</div>
                  <div class="flex-1">
                    <div v-if="positionForm.accessibility.length > 0" class="space-y-2">
                      <div class="flex flex-wrap gap-2">
                        <div v-for="item in accessibilityOptions.filter(opt => positionForm.accessibility.includes(opt.id))" :key="item.id"
                          class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 rounded-full text-sm font-medium">
                          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                          </svg>
                          {{ item.label }}
                        </div>
                      </div>
                    </div>
                    <div v-else class="text-gray-500 italic">
                      No accessibility permissions selected
                    </div>
                  </div>
                </div>
                
                <div class="flex flex-col md:flex-row md:items-start">
                  <div class="w-full md:w-32 text-sm font-medium text-gray-600 mb-1 md:mb-0">Description:</div>
                  <div class="flex-1 text-gray-700 break-words">{{ positionForm.description }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="px-4 md:px-8 py-4 md:py-6 bg-gray-50 border-t border-gray-200 flex-shrink-0">
          <div class="flex justify-between items-center">
            <div>
              <Button v-if="currentStep > 1" @click="previousStep" 
                variant="outline"
                class="px-4 py-2 md:px-5 md:py-2.5 flex items-center text-sm md:text-base">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
              </Button>
            </div>
            
            <div class="flex items-center space-x-2 md:space-x-3">
              <Button v-if="currentStep < 3" @click="nextStep" 
                :disabled="!canProceed"
                class="px-4 py-2 md:px-6 md:py-2.5 text-sm md:text-base bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white shadow-md"
              >
                Continue
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </Button>
              
              <Button v-if="currentStep === 3" @click="savePosition" :disabled="saving"
                class="px-4 py-2 md:px-6 md:py-2.5 text-sm md:text-base bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-md"
              >
                <svg v-if="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ saving ? 'Saving...' : (editingPosition ? 'Update Position' : 'Create Position') }}</span>
              </Button>
              
              <Button variant="ghost" @click="closeWizard" class="px-4 py-2 md:px-5 md:py-2.5 text-gray-600 hover:text-gray-800 hover:bg-gray-100 text-sm md:text-base">
                Cancel
              </Button>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from '@/utils/axios'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Checkbox } from '@/components/ui/checkbox'

const showPositionWizard = ref(false)
const currentStep = ref(1)
const editingPosition = ref(null)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const searchQuery = ref('')
const selectedDepartment = ref('all')
const selectedStatus = ref('active')
const showCustomDept = ref(false)
let searchTimeout = null

// Default departments
const defaultDepartments = [
  'Human Resources',
  'Finance',
  'Distributor Assistant',
  'Operational Distributor',
]

// Accessibility options for HR department
const accessibilityOptions = [
  { id: 'dashboard', label: 'Dashboard' },
  { id: 'employee_list', label: 'Employee List' },
  { id: 'positions_roles', label: 'Position & Roles' },
  { id: 'departments', label: 'Departments' },
  { id: 'employee_status', label: 'Employee Status' },
  { id: 'recruitment_application', label: 'Recruitment Application' },
  { id: 'payroll_management', label: 'Payroll Management' }
]

const positions = ref([])
const departments = ref([])
const statistics = ref({})
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

const positionForm = ref({
  title: '',
  department: '',
  custom_department: '',
  description: '',
  min_salary: '',
  max_salary: '',
  status: 'active',
  accessibility: [] 
})

// Computed properties
const allDepartments = computed(() => {
  const existingDepts = departments.value.map(d => d.name)
  const uniqueDepts = [...new Set([...defaultDepartments, ...existingDepts])]
  return uniqueDepts.sort()
})

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1:
      return positionForm.value.title.trim() && 
             (positionForm.value.department || 
              (positionForm.value.department === 'other' && positionForm.value.custom_department.trim()))
    case 2:
      return positionForm.value.description.trim()
    default:
      return true
  }
})

// Methods
const fetchPositions = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      department: selectedDepartment.value !== 'all' ? selectedDepartment.value : undefined,
      status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
      search: searchQuery.value || undefined
    }

    const response = await axios.get('/hr/positions', { params })
    
    if (response.data.success) {
      positions.value = response.data.data.data
      departments.value = response.data.departments
      statistics.value = response.data.statistics
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total
      }
    } else {
      error.value = response.data.message || 'Failed to load positions'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Network error occurred'
    console.error('Error fetching positions:', err)
  } finally {
    loading.value = false
  }
}

const searchPositions = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    pagination.value.current_page = 1
    fetchPositions()
  }, 500)
}

const fetchStatistics = async () => {
  try {
    const response = await axios.get('/hr/positions/statistics')
    if (response.data.success) {
      statistics.value = response.data.data
    }
  } catch (err) {
    console.error('Failed to fetch statistics:', err)
  }
}

const formatSalary = (salary) => {
  if (!salary) return '0'
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(salary)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const startNewPosition = () => {
  editingPosition.value = null
  currentStep.value = 1
  showCustomDept.value = false
  positionForm.value = {
    title: '',
    department: '',
    custom_department: '',
    description: '',
    min_salary: '',
    max_salary: '',
    status: 'active',
    accessibility: []
  }
  showPositionWizard.value = true
}

const editPosition = (position) => {
  editingPosition.value = position
  currentStep.value = 1
  showCustomDept.value = false
  
  // Parse accessibility from requirements or set empty array
  let accessibility = []
  if (position.requirements && position.requirements.accessibility) {
    accessibility = position.requirements.accessibility
  }
  
  positionForm.value = {
    title: position.title,
    department: position.department,
    custom_department: '',
    description: position.description,
    min_salary: position.min_salary,
    max_salary: position.max_salary,
    status: position.status,
    accessibility: accessibility
  }
  showPositionWizard.value = true
}

const toggleAccessibilityItem = (itemId) => {
  const index = positionForm.value.accessibility.indexOf(itemId)
  if (index === -1) {
    positionForm.value.accessibility.push(itemId)
  } else {
    positionForm.value.accessibility.splice(index, 1)
  }
}

const deletePosition = async (positionId) => {
  if (!confirm('Are you sure you want to delete this position? This action cannot be undone.')) {
    return
  }

  try {
    const response = await axios.delete(`/hr/positions/${positionId}`)
    
    if (response.data.success) {
      toast.success('Position deleted successfully')
      fetchPositions()
      fetchStatistics()
    } else {
      toast.error(response.data.message || 'Failed to delete position')
    }
  } catch (err) {
    toast.error(err.response?.data?.message || 'Failed to delete position')
  }
}

const nextStep = () => {
  if (currentStep.value < 3 && canProceed.value) {
    currentStep.value++
  }
}

const previousStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const closeWizard = () => {
  showPositionWizard.value = false
  currentStep.value = 1
  editingPosition.value = null
  showCustomDept.value = false
  positionForm.value = {
    title: '',
    department: '',
    custom_department: '',
    description: '',
    min_salary: '',
    max_salary: '',
    status: 'active',
    accessibility: []
  }
}

const savePosition = async () => {
  saving.value = true
  
  try {
    // Validate salary range
    if (positionForm.value.min_salary && positionForm.value.max_salary) {
      const min = parseFloat(positionForm.value.min_salary)
      const max = parseFloat(positionForm.value.max_salary)
      if (max <= min) {
        toast.error('Maximum salary must be greater than minimum salary')
        saving.value = false
        return
      }
    }

    // Determine department
    const department = positionForm.value.department === 'other' 
      ? positionForm.value.custom_department 
      : positionForm.value.department

    // Prepare requirements with accessibility for HR department
    let requirements = null
    if (department === 'Human Resources') {
      requirements = {
        accessibility: positionForm.value.accessibility,
        permissions: positionForm.value.accessibility.map(item => {
          const option = accessibilityOptions.find(opt => opt.id === item)
          return option ? option.label : item
        })
      }
    }

    const payload = {
      ...positionForm.value,
      department: department,
      requirements: requirements
    }

    // Remove custom_department and accessibility from payload
    delete payload.custom_department
    delete payload.accessibility

    // Convert empty strings to null for salary fields
    if (payload.min_salary === '') payload.min_salary = null
    if (payload.max_salary === '') payload.max_salary = null

    let response
    if (editingPosition.value) {
      response = await axios.put(`/hr/positions/${editingPosition.value.id}`, payload)
    } else {
      response = await axios.post('/hr/positions', payload)
    }

    if (response.data.success) {
      toast.success(editingPosition.value ? 'Position updated successfully' : 'Position created successfully')
      closeWizard()
      fetchPositions()
      fetchStatistics()
    } else {
      toast.error(response.data.message || 'Failed to save position')
    }
  } catch (err) {
    const errorMsg = err.response?.data?.message || 'Failed to save position'
    if (err.response?.data?.errors) {
      // Handle validation errors
      const errors = Object.values(err.response.data.errors).flat()
      toast.error(errors.join(', '))
    } else {
      toast.error(errorMsg)
    }
  } finally {
    saving.value = false
  }
}

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++
    fetchPositions()
  }
}

const prevPage = () => {
  if (pagination.value.current_page > 1) {
    pagination.value.current_page--
    fetchPositions()
  }
}

// Lifecycle hooks
onMounted(() => {
  fetchPositions()
  fetchStatistics()
})
</script>

<style scoped>
.positions-roles {
  min-height: calc(100vh - 80px);
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@keyframes modal-appear {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-modal-appear {
  animation: modal-appear 0.3s ease-out;
}

/* Custom scrollbar for modal */
.overflow-y-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}
</style>