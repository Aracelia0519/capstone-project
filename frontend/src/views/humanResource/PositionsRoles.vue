<template>
  <div class="positions-roles p-4 md:p-6">
    <Toaster richColors position="top-right" expand />

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Positions & Roles</h1>
        <p class="text-gray-600">Define job roles and access responsibilities within the organization</p>
      </div>
      <Button 
        @click="requirePermission('manage', startNewPosition)" 
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
      <Button @click="requirePermission('manage', startNewPosition)" class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white hover:from-blue-600 hover:to-indigo-700 shadow-md h-auto py-3">
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
                <div class="p-2 rounded-xl mr-3 shadow-sm" :class="position.department_color?.bg || 'bg-gray-100'">
                  <svg class="w-5 h-5" :class="position.department_color?.text || 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
              <span class="inline-block px-3 py-1 text-xs font-medium rounded-full" :class="position.department_color?.badge || 'bg-gray-100 text-gray-800'">
                {{ position.department }}
              </span>
            </div>
            <div class="flex space-x-1">
              <Button variant="ghost" size="icon" @click="requirePermission('manage', () => editPosition(position))" class="h-8 w-8 text-gray-400 hover:text-yellow-600 hover:bg-yellow-50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </Button>
              <Button variant="ghost" size="icon" @click="requirePermission('manage', () => deletePosition(position.id))" class="h-8 w-8 text-gray-400 hover:text-red-600 hover:bg-red-50">
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
      <DialogContent class="max-w-[95vw] xl:max-w-7xl p-0 overflow-hidden max-h-[90vh] flex flex-col gap-0 [&>button]:hidden">
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
                    currentStep >= 2 ? 'text-white' : 'text-blue-300']">Details & Access</div>
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
        
        <div class="flex-1 overflow-y-auto custom-scrollbar p-4 md:p-8">
          
          <div v-if="currentStep === 1" class="space-y-4 md:space-y-6">
            <div class="max-w-4xl mx-auto">
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
                </div>
                
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Department
                  </label>
                  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2 md:gap-3">
                    <button v-for="dept in defaultDepartments" :key="dept"
                      @click="if(positionForm.department !== dept) { positionForm.accessibility = {}; positionForm.department = dept }"
                      :class="['px-3 py-3 md:px-4 md:py-3 rounded-lg border transition-all text-xs md:text-sm font-medium', 
                      positionForm.department === dept 
                        ? 'bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-500 text-blue-700 ring-2 ring-blue-200' 
                        : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400']">
                      {{ dept }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="currentStep === 2" class="space-y-4 md:space-y-6">
            <div class="max-w-6xl mx-auto">
              <h3 class="text-lg font-semibold text-gray-800 mb-2">Position Details & Access</h3>
              <p class="text-gray-600 mb-4 md:mb-6 text-sm md:text-base">Define the responsibilities and grant system access control based on Levels.</p>
              
              <div class="space-y-6">
                <div class="max-w-4xl">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="text-red-500">*</span> Role Description
                  </label>
                  <Textarea v-model="positionForm.description" rows="3" required
                    class="resize-none"
                    placeholder="Describe the key responsibilities..." />
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 max-w-4xl">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Salary Range</label>
                    <div class="space-y-3">
                      <div class="relative flex items-center gap-2">
                        <Input v-model="positionForm.min_salary" type="number" step="0.01" min="0" placeholder="Min" />
                        <span class="text-gray-400">-</span>
                        <Input v-model="positionForm.max_salary" type="number" step="0.01" min="0" placeholder="Max" />
                      </div>
                    </div>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <div class="grid grid-cols-2 gap-3">
                      <button @click="positionForm.status = 'active'"
                        :class="['px-3 py-2 rounded-lg border transition-all text-sm font-medium', 
                        positionForm.status === 'active' 
                          ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-500 text-green-700 ring-2 ring-green-200' 
                          : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50']">
                        <div class="flex items-center justify-center">
                          <div class="w-2 h-2 rounded-full bg-green-500 mr-2"></div>
                          Active
                        </div>
                      </button>
                      <button @click="positionForm.status = 'inactive'"
                        :class="['px-3 py-2 rounded-lg border transition-all text-sm font-medium', 
                        positionForm.status === 'inactive' 
                          ? 'bg-gradient-to-r from-gray-100 to-gray-50 border-gray-400 text-gray-700 ring-2 ring-gray-200' 
                          : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50']">
                        <div class="flex items-center justify-center">
                          <div class="w-2 h-2 rounded-full bg-gray-500 mr-2"></div>
                          Inactive
                        </div>
                      </button>
                    </div>
                  </div>
                </div>

                <div v-if="currentModules.length > 0" class="pt-6 border-t border-gray-200">
                  <div class="flex justify-between items-end mb-4">
                    <div>
                      <h4 class="text-lg font-semibold text-gray-800 mb-1">Role-Based Access Control (RBAC)</h4>
                      <p class="text-sm text-gray-500">Grant granular permissions grouped by access levels.</p>
                    </div>
                  </div>

                  <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm w-full">
                    <div class="overflow-auto max-h-[55vh] custom-scrollbar relative">
                      <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead class="bg-gray-50 sticky top-0 z-20 shadow-sm outline outline-1 outline-gray-200">
                          <tr>
                            <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-50 w-auto">Module Name</th>
                            <th scope="col" class="py-3 px-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider w-24 md:w-32 bg-gray-50">
                              <div class="flex flex-col items-center justify-center gap-1">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <span>Level 1 (View)</span>
                              </div>
                            </th>
                            <th scope="col" class="py-3 px-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider w-24 md:w-32 bg-gray-50">
                              <div class="flex flex-col items-center justify-center gap-1">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                <span title="Create, Update, Delete">Level 2 (Manage)</span>
                              </div>
                            </th>
                            <th scope="col" class="py-3 px-2 text-center text-xs font-bold text-gray-700 uppercase tracking-wider w-24 md:w-32 bg-gray-50">
                              <div class="flex flex-col items-center justify-center gap-1">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                                <span title="Approve, Reject Workflows">Level 3 (Approve)</span>
                              </div>
                            </th>
                            <th scope="col" class="py-3 px-2 text-center text-xs font-bold text-indigo-900 uppercase tracking-wider w-20 md:w-32 bg-indigo-50">
                              <div class="flex flex-col items-center justify-center gap-1">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                <span>All Access</span>
                              </div>
                            </th>
                          </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                          
                          <template v-for="group in currentModules" :key="group.category">
                            
                            <tr class="bg-gray-50/80 border-y border-gray-200">
                              <td colspan="5" class="py-2.5 px-4 text-xs font-bold text-gray-500 uppercase tracking-widest bg-gray-100">
                                {{ group.category }}
                              </td>
                            </tr>

                            <tr v-for="mod in group.items" :key="mod.permissionKey || mod.key" class="hover:bg-blue-50/40 transition-colors group">
                              <td class="py-2.5 px-4 text-sm font-medium text-gray-900 border-r border-gray-50 pl-6">
                                <label class="flex items-center cursor-pointer select-none">
                                  <input 
                                    type="checkbox" 
                                    :checked="!!positionForm.accessibility[mod.permissionKey || mod.key]"
                                    @change="toggleModule(mod.permissionKey || mod.key, $event.target.checked)"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600 mr-3"
                                  >
                                  <span class="truncate group-hover:text-indigo-700 transition-colors">{{ mod.text || mod.label || mod.name }}</span>
                                </label>
                              </td>

                              <td class="py-2.5 px-2 text-center hover:bg-gray-50 transition-colors">
                                <input 
                                  v-if="positionForm.accessibility[mod.permissionKey || mod.key]" 
                                  type="checkbox" 
                                  v-model="positionForm.accessibility[mod.permissionKey || mod.key].view"
                                  @change="enforceDependencies(mod.permissionKey || mod.key, 'view')"
                                  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600 cursor-pointer"
                                >
                              </td>

                              <td class="py-2.5 px-2 text-center hover:bg-gray-50 transition-colors">
                                <input 
                                  v-if="positionForm.accessibility[mod.permissionKey || mod.key]" 
                                  type="checkbox" 
                                  v-model="positionForm.accessibility[mod.permissionKey || mod.key].manage"
                                  @change="enforceDependencies(mod.permissionKey || mod.key, 'manage')"
                                  class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500 cursor-pointer"
                                >
                              </td>

                              <td class="py-2.5 px-2 text-center hover:bg-gray-50 transition-colors">
                                <input 
                                  v-if="positionForm.accessibility[mod.permissionKey || mod.key]" 
                                  type="checkbox" 
                                  v-model="positionForm.accessibility[mod.permissionKey || mod.key].approve"
                                  @change="enforceDependencies(mod.permissionKey || mod.key, 'approve')"
                                  class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-600 cursor-pointer"
                                >
                              </td>

                              <td class="py-2.5 px-2 text-center bg-indigo-50/30 hover:bg-indigo-100/50 transition-colors">
                                <input 
                                  v-if="positionForm.accessibility[mod.permissionKey || mod.key]" 
                                  type="checkbox" 
                                  :checked="isFullAccess(mod.permissionKey || mod.key)"
                                  @change="toggleFullAccess(mod.permissionKey || mod.key, $event.target.checked)"
                                  class="h-4 w-4 rounded border-indigo-300 text-indigo-700 focus:ring-indigo-700 cursor-pointer"
                                >
                              </td>
                            </tr>
                          </template>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          
          <div v-if="currentStep === 3" class="space-y-4 md:space-y-6">
            <div class="max-w-4xl mx-auto">
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
                      {{ positionForm.department }}
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
                
                <div class="flex flex-col md:flex-row md:items-start">
                  <div class="w-full md:w-32 text-sm font-medium text-gray-600 mb-1 md:mb-0">Accessibility:</div>
                  <div class="flex-1">
                    <div v-if="Object.keys(positionForm.accessibility).length > 0" class="space-y-2">
                      <div class="flex flex-wrap gap-2">
                        <div v-for="(perms, key) in positionForm.accessibility" :key="key"
                          class="inline-flex items-center px-3 py-1 bg-gradient-to-r from-green-50 to-emerald-50 text-green-700 rounded border border-green-200 text-xs font-medium">
                          {{ getModuleName(key) }} 
                          <span class="ml-1 opacity-70">
                            ({{ [perms.view ? 'L1' : '', perms.manage ? 'L2' : '', perms.approve ? 'L3' : ''].filter(Boolean).join(',') }})
                          </span>
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
import { Toaster, toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogTitle, DialogDescription } from '@/components/ui/dialog'

const showPositionWizard = ref(false)
const currentStep = ref(1)
const editingPosition = ref(null)
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const searchQuery = ref('')
const selectedDepartment = ref('all')
const selectedStatus = ref('active')
let searchTimeout = null

const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

const requirePermission = (action, callback) => {
  if (!permissions.value['can_' + action]) {
    toast.error(`Access Denied: You do not have permission to ${action} positions.`);
    return;
  }
  if (callback) callback();
}

const defaultDepartments = [
  'Human Resources',
  'Finance',
  'Operational Distributor',
  'Special RBAC'
]

const hrNavItems = [
  {
    category: 'Overview & Reports',
    items: [
      { label: 'Dashboard', permissionKey: 'dashboard' },
      { label: 'HR Reports', permissionKey: 'reports' }
    ]
  },
  {
    category: 'Organization Management',
    items: [
      { label: 'Departments', permissionKey: 'departments' },
      { label: 'Positions & Roles', permissionKey: 'positions_roles' }
    ]
  },
  {
    category: 'Employee Management',
    items: [
      { label: 'Employee List', permissionKey: 'employee_list' },
      { label: 'Employment Status', permissionKey: 'employment_status' },
      { label: 'Recruitment Application', permissionKey: 'recruitment' }
    ]
  },
  {
    category: 'Payroll & Requests',
    items: [
      { label: 'Attendance Request', permissionKey: 'attendance_request' },
      { label: 'Leave Request', permissionKey: 'leave_request' },
      { label: 'Payroll Management', permissionKey: 'payroll_management' }
    ]
  }
]

const financeNavItems = [
  {
    category: 'Financial Records',
    items: [
      { name: 'Dashboard', permissionKey: 'finance_dashboard' },
      { name: 'Transactions', permissionKey: 'finance_transactions' },
      { name: 'Payment Methods', permissionKey: 'finance_payment_methods' }
    ]
  },
  {
    category: 'Operations',
    items: [
      { name: 'Invoices / Billing', permissionKey: 'finance_invoices' },
      { name: 'Reports', permissionKey: 'finance_reports' }
    ]
  },
  {
    category: 'Procurement',
    items: [
      { name: 'Procurement Requests', permissionKey: 'finance_procurement' },
      { name: 'Budget Release', permissionKey: 'finance_budget_release' }
    ]
  },
  {
    category: 'Payroll Management',
    items: [
      { name: 'Payroll Request', permissionKey: 'finance_payroll_request' },
      { name: 'Payroll Paid', permissionKey: 'finance_payroll_paid' }
    ]
  }
]

const odNavItems = [
  {
    category: 'Overview & Analytics',
    items: [
      { label: 'OD Dashboard', permissionKey: 'ec_dashboard' },
      { label: 'Reports', permissionKey: 'ec_reports' }
    ]
  },
  {
    category: 'Procurement & Inventory',
    items: [
      { label: 'Procurement', permissionKey: 'ec_procurement' },
      { label: 'Process Request', permissionKey: 'ec_process_procurement' },
      { label: 'Track Procurement', permissionKey: 'ec_track_procurement' },
      { label: 'Arrived Item', permissionKey: 'ec_arrived_item' },
      { label: 'Inventory', permissionKey: 'ec_inventory' },
      { label: 'Categories', permissionKey: 'ec_categories' }
    ]
  },
  {
    category: 'E-Commerce Fulfillment',
    items: [
      { label: 'Orders', permissionKey: 'ec_orders' },
      { label: 'Prepare Order', permissionKey: 'ec_prepare_order' },
      { label: 'Delivery', permissionKey: 'ec_delivery' },
      { label: 'Returns', permissionKey: 'ec_returns' }
    ]
  },
  {
    category: 'Partnerships & Services',
    items: [
      { label: 'Partner Supplier', permissionKey: 'ec_partner_supplier' },
      { label: 'Service Provider', permissionKey: 'ec_service_provider' }
    ]
  },
  {
    category: 'Customer Engagement',
    items: [
      { label: 'Messages', permissionKey: 'ec_messages' },
      { label: 'Reviews', permissionKey: 'ec_reviews' },
      { label: 'Promotions', permissionKey: 'ec_promotions' },
      { label: 'Promo Approval', permissionKey: 'ec_promo_approval' }
    ]
  },
  {
    category: 'Financial Operations',
    items: [
      { label: 'Payments', permissionKey: 'ec_payment' }
    ]
  }
]

const positions = ref([])
const departments = ref([])
const statistics = ref({})
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })

const positionForm = ref({
  title: '',
  department: '',
  description: '',
  min_salary: '',
  max_salary: '',
  status: 'active',
  accessibility: {} 
})

const currentModules = computed(() => {
  if (positionForm.value.department === 'Human Resources') return hrNavItems;
  if (positionForm.value.department === 'Finance') return financeNavItems;
  if (positionForm.value.department === 'Operational Distributor') return odNavItems;
  
  if (positionForm.value.department === 'Special RBAC') {
    return [
      ...hrNavItems.map(g => ({ ...g, category: `HR - ${g.category}` })),
      ...financeNavItems.map(g => ({ ...g, category: `Finance - ${g.category}` })),
      ...odNavItems.map(g => ({ ...g, category: `OD - ${g.category}` }))
    ];
  }
  return [];
})

const canProceed = computed(() => {
  switch (currentStep.value) {
    case 1: return positionForm.value.title.trim() && positionForm.value.department;
    case 2: return positionForm.value.description.trim();
    default: return true;
  }
})

const getModuleName = (key) => {
  const allGroups = [...hrNavItems, ...financeNavItems, ...odNavItems];
  for (const group of allGroups) {
    const mod = group.items.find(m => (m.permissionKey || m.key) === key);
    if (mod) return mod.text || mod.label || mod.name;
  }
  return key;
}

const toggleModule = (moduleKey, isChecked) => {
  if (isChecked) {
    if (!positionForm.value.accessibility[moduleKey]) {
      positionForm.value.accessibility[moduleKey] = { view: true, manage: false, approve: false };
    }
  } else {
    delete positionForm.value.accessibility[moduleKey];
  }
}

const isFullAccess = (moduleKey) => {
  const perms = positionForm.value.accessibility[moduleKey];
  if (!perms) return false;
  return perms.view && perms.manage && perms.approve;
}

const toggleFullAccess = (moduleKey, isChecked) => {
  if (isChecked) {
    positionForm.value.accessibility[moduleKey] = { view: true, manage: true, approve: true };
  } else {
    positionForm.value.accessibility[moduleKey] = { view: true, manage: false, approve: false };
  }
}

// Logic to ensure level dependencies cascade correctly
const enforceDependencies = (moduleKey, levelChanged) => {
  const perms = positionForm.value.accessibility[moduleKey];
  if (!perms) return;
  
  if (levelChanged === 'approve') {
    if (perms.approve) {
      perms.manage = true;
      perms.view = true;
    }
  } else if (levelChanged === 'manage') {
    if (perms.manage) {
      perms.view = true;
    } else {
      perms.approve = false;
    }
  } else if (levelChanged === 'view') {
    if (!perms.view) {
      perms.manage = false;
      perms.approve = false;
    }
  }
}

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
      
      if (response.data.permissions) {
        permissions.value = response.data.permissions
      } else {
        permissions.value = { can_view: true, can_manage: true, can_approve: true }
      }
    } else {
      error.value = response.data.message || 'Failed to load positions'
      toast.error(error.value)
    }
  } catch (err) {
    if (err.response?.status === 403) {
      toast.error("Unauthorized. You do not have permission to access positions.");
    }
    error.value = err.response?.data?.message || 'Network error occurred'
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
  } catch (err) {}
}

const formatSalary = (salary) => {
  if (!salary) return '0'
  return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(salary)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const startNewPosition = () => {
  editingPosition.value = null
  currentStep.value = 1
  positionForm.value = {
    title: '', department: '', description: '', min_salary: '', max_salary: '', status: 'active', accessibility: {}
  }
  showPositionWizard.value = true
}

const editPosition = (position) => {
  editingPosition.value = position
  currentStep.value = 1
  
  let parsedAccessibility = {}
  if (position.accessibility && typeof position.accessibility === 'object' && !Array.isArray(position.accessibility)) {
    parsedAccessibility = JSON.parse(JSON.stringify(position.accessibility));
  } else if (Array.isArray(position.accessibility)) {
    position.accessibility.forEach(k => {
      parsedAccessibility[k] = { view: true, manage: true, approve: true };
    });
  }

  positionForm.value = {
    title: position.title,
    department: position.department,
    description: position.description,
    min_salary: position.min_salary,
    max_salary: position.max_salary,
    status: position.status,
    accessibility: parsedAccessibility
  }
  showPositionWizard.value = true
}

const deletePosition = async (positionId) => {
  if (!confirm('Are you sure you want to delete this position? This action cannot be undone.')) return
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

const nextStep = () => { if (currentStep.value < 3 && canProceed.value) currentStep.value++ }
const previousStep = () => { if (currentStep.value > 1) currentStep.value-- }
const closeWizard = () => { showPositionWizard.value = false; currentStep.value = 1; editingPosition.value = null; }

const savePosition = async () => {
  saving.value = true
  try {
    if (positionForm.value.min_salary && positionForm.value.max_salary) {
      if (parseFloat(positionForm.value.max_salary) <= parseFloat(positionForm.value.min_salary)) {
        toast.error('Maximum salary must be greater than minimum salary')
        saving.value = false
        return
      }
    }

    const payload = { ...positionForm.value }
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
      toast.error(Object.values(err.response.data.errors).flat().join(', '))
    } else {
      toast.error(errorMsg)
    }
  } finally {
    saving.value = false
  }
}

const nextPage = () => { if (pagination.value.current_page < pagination.value.last_page) { pagination.value.current_page++; fetchPositions() } }
const prevPage = () => { if (pagination.value.current_page > 1) { pagination.value.current_page--; fetchPositions() } }

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
  from { opacity: 0; transform: scale(0.95) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.animate-modal-appear {
  animation: modal-appear 0.3s ease-out;
}

.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 transparent;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: #cbd5e1;
  border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background-color: #94a3b8;
}
</style>