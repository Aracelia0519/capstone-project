<template>
  <div class="user-management min-h-screen p-6 space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">User Management</h1>
        <p class="text-slate-500 mt-1">Control system access and manage user roles.</p>
      </div>
      <Button 
        @click="showAddUserModal = true" 
        class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg transition-all hover:-translate-y-0.5"
      >
        <i class="fas fa-plus mr-2"></i>
        Add New User
      </Button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
      <Card 
        v-for="(stat, key) in {
          total: { label: 'Total Users', icon: 'fa-users', color: 'from-blue-500 to-indigo-600' },
          admin: { label: 'Admins', icon: 'fa-user-shield', color: 'from-red-500 to-pink-600' },
          distributor: { label: 'Distributors', icon: 'fa-truck', color: 'from-green-500 to-emerald-600' },
          supplier: { label: 'Suppliers', icon: 'fa-boxes', color: 'from-orange-500 to-red-500' },
          service_provider: { label: 'Service Providers', icon: 'fa-tools', color: 'from-purple-500 to-violet-600' },
          client: { label: 'Clients', icon: 'fa-user', color: 'from-gray-600 to-gray-700' }
        }" 
        :key="key"
        @click="activeTab = key"
        :class="[
          'cursor-pointer hover:shadow-md transition-all border-l-4',
          activeTab === key ? 'border-l-blue-600 ring-2 ring-blue-100' : 'border-l-transparent'
        ]"
      >
        <CardContent class="p-6 flex items-center gap-4">
          <div :class="`w-10 h-10 rounded-lg bg-gradient-to-br ${stat.color} flex items-center justify-center shadow-sm`">
            <i :class="`fas ${stat.icon} text-white text-sm`"></i>
          </div>
          <div>
            <p class="text-2xl font-bold text-slate-900 leading-none">{{ statistics[key] || 0 }}</p>
            <p class="text-xs text-slate-500 font-medium mt-1">{{ stat.label }}</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="border-slate-200 shadow-sm">
      <CardHeader class="pb-4">
        <div class="flex flex-col md:flex-row justify-between gap-4">
          <div class="flex flex-wrap gap-2">
            <Button
              v-for="tab in tabs"
              :key="tab.value"
              variant="outline"
              size="sm"
              @click="activeTab = tab.value"
              :class="[
                'h-8 transition-all',
                activeTab === tab.value 
                  ? 'bg-slate-900 text-white hover:bg-slate-800 border-slate-900' 
                  : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'
              ]"
            >
              <i :class="`${tab.icon} mr-2 text-xs opacity-70`"></i>
              {{ tab.label }}
            </Button>
          </div>
        </div>
        
        <Separator class="my-4" />

        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="relative w-full sm:max-w-md">
            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400 text-sm"></i>
            <Input 
              v-model="searchQuery" 
              placeholder="Search by name, email, or phone..." 
              @input="debouncedFetchUsers"
              class="pl-9 bg-slate-50 border-slate-200 focus:bg-white"
            />
          </div>
          
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <select 
              v-model="statusFilter" 
              @change="fetchUsers"
              class="h-10 w-full sm:w-[150px] rounded-md border border-slate-200 bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="pending">Pending</option>
            </select>
            
            <Button variant="outline" @click="fetchUsers" class="gap-2">
              <i class="fas fa-sync-alt"></i>
            </Button>
          </div>
        </div>
      </CardHeader>

      <CardContent class="p-0">
        <div class="rounded-md border-t border-slate-100">
          <Table>
            <TableHeader class="bg-slate-50/50">
              <TableRow>
                <TableHead class="font-semibold text-slate-600">User</TableHead>
                <TableHead class="font-semibold text-slate-600">Role</TableHead>
                <TableHead class="font-semibold text-slate-600">Contact</TableHead>
                <TableHead class="font-semibold text-slate-600">Verification</TableHead>
                <TableHead class="font-semibold text-slate-600">Status</TableHead>
                <TableHead class="font-semibold text-slate-600 text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="loading">
                <TableCell colspan="6" class="h-32 text-center">
                   <div class="flex flex-col items-center justify-center gap-2 text-slate-500">
                      <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                      <span>Loading data...</span>
                   </div>
                </TableCell>
              </TableRow>

              <TableRow v-else-if="users.length === 0">
                <TableCell colspan="6" class="h-32 text-center text-slate-500">
                  <div class="flex flex-col items-center justify-center gap-1">
                    <i class="fas fa-search text-2xl mb-2 opacity-20"></i>
                    <p>No users found matching your criteria.</p>
                  </div>
                </TableCell>
              </TableRow>

              <TableRow v-for="user in users" :key="user.id" class="hover:bg-slate-50/50 transition-colors">
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div 
                      class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold shadow-sm ring-2 ring-white"
                      :style="{ backgroundColor: user.avatar_color }"
                    >
                      {{ user.initials }}
                    </div>
                    <div class="flex flex-col">
                      <span class="font-medium text-slate-900">{{ user.full_name }}</span>
                      <span class="text-xs text-slate-500">Added {{ user.created_at }}</span>
                    </div>
                  </div>
                </TableCell>
                
                <TableCell>
                  <Badge variant="outline" :class="getRoleBadgeClass(user.role) + ' gap-1 border-0 font-normal'">
                    <i :class="getRoleIcon(user.role)" class="text-[10px]"></i>
                    {{ user.role_display }}
                  </Badge>
                  <div 
                    v-if="(user.role === 'distributor' || user.role === 'supplier') && user.verification_status === 'pending'"
                    @click="viewUser(user)"
                    class="mt-1 text-[10px] text-blue-600 hover:text-blue-800 cursor-pointer flex items-center gap-1 font-medium"
                  >
                    <i class="fas fa-exclamation-circle"></i> Needs Review
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="flex flex-col gap-1 text-sm text-slate-600">
                    <div class="flex items-center gap-2">
                      <i class="fas fa-envelope text-slate-400 text-xs w-4"></i>
                      <span>{{ user.email }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <i class="fas fa-phone text-slate-400 text-xs w-4"></i>
                      <span>{{ user.phone || 'N/A' }}</span>
                    </div>
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="flex flex-col items-start gap-1">
                    <Badge variant="secondary" :class="getVerificationBadgeClass(user.verification_status) + ' gap-1'">
                      <i :class="getVerificationIcon(user.verification_status)"></i>
                      {{ user.verification_status || 'N/A' }}
                    </Badge>
                    <span 
                      v-if="user.verification_details" 
                      class="text-[10px] text-slate-500 truncate max-w-[150px]"
                      :title="getVerificationDetails(user)"
                    >
                      {{ getVerificationDetails(user) }}
                    </span>
                  </div>
                </TableCell>
                
                <TableCell>
                   <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-xs font-medium border" :class="getStatusBadgeClass(user.status)">
                      <span class="w-1.5 h-1.5 rounded-full" :class="getStatusDotClass(user.status)"></span>
                      {{ user.status_display }}
                   </span>
                </TableCell>
                
                <TableCell class="text-right">
                  <div class="flex items-center justify-end gap-2">
                    <Button variant="ghost" size="icon" @click="viewUser(user)" class="h-8 w-8 text-slate-500 hover:text-blue-600 hover:bg-blue-50">
                      <i class="fas fa-eye text-xs">View</i>
                    </Button>
                    <Button variant="ghost" size="icon" @click="editUser(user)" class="h-8 w-8 text-slate-500 hover:text-amber-600 hover:bg-amber-50">
                      <i class="fas fa-pencil-alt text-xs">Edit</i>
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        
        <div v-if="!loading && pagination.last_page > 1" class="flex items-center justify-between p-4 border-t border-slate-100 bg-slate-50/30">
          <div class="text-sm text-slate-500">
             Showing {{ pagination.from }}-{{ pagination.to }} of {{ pagination.total }}
          </div>
          <div class="flex gap-1">
             <Button 
                variant="outline" size="sm" 
                @click="goToPage(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="h-8 w-8 p-0"
             >
                <i class="fas fa-chevron-left text-xs"></i>
             </Button>
             
             <Button 
                v-for="page in getPaginationRange()" 
                :key="page" 
                :variant="currentPage === page ? 'default' : 'outline'"
                size="sm"
                @click="goToPage(page)"
                class="h-8 w-8 p-0"
                :class="currentPage === page ? 'bg-slate-900 hover:bg-slate-800' : ''"
             >
                {{ page }}
             </Button>
             
             <Button 
                variant="outline" size="sm" 
                @click="goToPage(currentPage + 1)" 
                :disabled="currentPage === pagination.last_page"
                class="h-8 w-8 p-0"
             >
                <i class="fas fa-chevron-right text-xs"></i>
             </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewModal" @update:open="closeViewModal">
      <div v-if="showViewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
          <div class="flex items-center justify-between p-6 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-4">
              <div 
                class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold shadow-md ring-4 ring-white"
                :style="{ backgroundColor: viewingUser.avatar_color }"
              >
                {{ viewingUser.initials }}
              </div>
              <div>
                <h2 class="text-2xl font-bold text-slate-900">{{ viewingUser.full_name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-slate-500 text-sm">{{ viewingUser.email }}</span>
                  <Badge variant="outline" class="text-xs uppercase">{{ viewingUser.role_display }}</Badge>
                </div>
              </div>
            </div>
            <Button variant="ghost" size="icon" @click="closeViewModal" class="rounded-full hover:bg-slate-200">
              <i class="fas fa-times text-slate-500"></i>
            </Button>
          </div>

          <ScrollArea class="flex-1 p-6 bg-white overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <div class="lg:col-span-1 space-y-6">
                <Card class="bg-slate-50/50 border-slate-200">
                  <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-slate-500 uppercase tracking-wider">Account Status</CardTitle>
                  </CardHeader>
                  <CardContent class="space-y-4">
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium text-slate-700">Access</span>
                      <Badge :class="getStatusBadgeClass(viewingUser.status)">{{ viewingUser.status_display }}</Badge>
                    </div>
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium text-slate-700">Verification</span>
                      <Badge :class="getVerificationBadgeClass(viewingUser.verification_status)">{{ viewingUser.verification_status || 'N/A' }}</Badge>
                    </div>
                    <div class="pt-4 flex flex-col gap-2">
                       <Button @click="editUser(viewingUser)" variant="outline" class="w-full justify-start gap-2">
                          <i class="fas fa-edit text-slate-400"></i> Edit Profile
                       </Button>

                       <template v-if="viewingUser.status === 'pending' || viewingUser.verification_status === 'pending'">
                          <Button @click="approveUser(viewingUser)" class="w-full justify-start gap-2 bg-green-600 hover:bg-green-700 text-white">
                             <i class="fas fa-check-circle"></i> Approve User
                          </Button>
                          <Button @click="rejectUser(viewingUser)" variant="destructive" class="w-full justify-start gap-2">
                             <i class="fas fa-times-circle"></i> Reject User
                          </Button>
                       </template>
                       
                       <template v-else>
                          <Button 
                             v-if="viewingUser.status === 'active'" 
                             @click="deactivateUser(viewingUser)" 
                             variant="destructive" 
                             class="w-full justify-start gap-2"
                          >
                             <i class="fas fa-user-slash"></i> Deactivate
                          </Button>
                          <Button 
                             v-if="viewingUser.status === 'inactive'" 
                             @click="activateUser(viewingUser)" 
                             class="w-full justify-start gap-2 bg-green-600 hover:bg-green-700 text-white"
                          >
                             <i class="fas fa-user-check"></i> Activate
                          </Button>
                       </template>
                    </div>
                  </CardContent>
                </Card>

                <Card class="border-slate-200">
                   <CardContent class="p-4 space-y-3">
                      <div class="flex items-center gap-3 text-sm">
                         <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fas fa-calendar-alt"></i>
                         </div>
                         <div>
                            <p class="text-slate-500 text-xs">Joined</p>
                            <p class="font-medium">{{ viewingUser.created_at }}</p>
                         </div>
                      </div>
                      <div v-if="viewingUser.verification_details?.submitted_at" class="flex items-center gap-3 text-sm">
                         <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fas fa-file-contract"></i>
                         </div>
                         <div>
                            <p class="text-slate-500 text-xs">Req. Submitted</p>
                            <p class="font-medium">{{ formatDate(viewingUser.verification_details.submitted_at) }}</p>
                         </div>
                      </div>
                   </CardContent>
                </Card>
              </div>

              <div class="lg:col-span-2 space-y-6">
                <Card>
                  <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                      <i class="fas fa-user-circle text-blue-600"></i>
                      Personal Information
                    </CardTitle>
                  </CardHeader>
                  <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-6">
                     <div class="space-y-1">
                        <Label class="text-slate-500">Full Name</Label>
                        <p class="font-medium text-slate-900">{{ viewingUser.first_name }} {{ viewingUser.last_name }}</p>
                     </div>
                     <div class="space-y-1">
                        <Label class="text-slate-500">Email</Label>
                        <p class="font-medium text-slate-900">{{ viewingUser.email }}</p>
                     </div>
                     <div class="space-y-1">
                        <Label class="text-slate-500">Phone</Label>
                        <p class="font-medium text-slate-900">{{ viewingUser.phone || 'Not Provided' }}</p>
                     </div>
                     <div class="space-y-1">
                        <Label class="text-slate-500">Address</Label>
                        <p class="font-medium text-slate-900">{{ viewingUser.address || 'Not Provided' }}</p>
                     </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardHeader>
                     <CardTitle class="flex items-center gap-2">
                        <i class="fas fa-folder-open text-purple-600"></i>
                        Requirements & Documents
                     </CardTitle>
                  </CardHeader>
                  <CardContent>
                     <div v-if="loadingRequirements" class="flex justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                     </div>
                     
                     <div v-else-if="!userRequirements || Object.keys(userRequirements).length === 0" class="text-center py-8 border-2 border-dashed rounded-lg">
                        <p class="text-slate-500">No requirements submitted.</p>
                     </div>

                     <div v-else class="space-y-6">

  <div v-if="viewingUser.role === 'distributor' && userRequirements.distributor">
    <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 mb-6">
      <h4 class="text-sm font-semibold text-slate-900 mb-4 uppercase tracking-wider">Business Information</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Company Name</Label>
          <p class="font-medium text-slate-900">{{ userRequirements.distributor.company_name }}</p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Registration Number</Label>
          <p class="font-medium text-slate-900">{{ userRequirements.distributor.business_registration_number }}</p>
        </div>
      </div>
    </div>

    <h4 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i class="fas fa-file-image text-slate-400"></i> Business Documents
    </h4>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="(doc, label) in {
        'Valid ID': userRequirements.distributor.valid_id_photo_url,
        'DTI Certificate': userRequirements.distributor.dti_certificate_photo_url,
        'Mayor\'s Permit': userRequirements.distributor.mayor_permit_photo_url,
        'Barangay Clearance': userRequirements.distributor.barangay_clearance_photo_url,
        'Business Registration': userRequirements.distributor.business_registration_photo_url
      }" :key="label">
        <div v-if="doc" 
             class="group relative aspect-[4/3] bg-slate-100 rounded-lg overflow-hidden border border-slate-200 cursor-pointer shadow-sm hover:shadow-md transition-all"
             @click="showImageModal(doc, label)">
          <img :src="doc" :alt="label" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-3 opacity-100 transition-opacity">
            <span class="text-white text-xs font-semibold">{{ label }}</span>
          </div>
          <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <i class="fas fa-search-plus text-white text-xl"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="viewingUser.role === 'supplier' && userRequirements.supplier">
    <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 mb-6">
      <h4 class="text-sm font-semibold text-slate-900 mb-4 uppercase tracking-wider">Business Information</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Company Name</Label>
          <p class="font-medium text-slate-900">{{ userRequirements.supplier.company_name }}</p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Registration Number</Label>
          <p class="font-medium text-slate-900">{{ userRequirements.supplier.business_registration_number }}</p>
        </div>
        <div v-if="userRequirements.supplier.address" class="md:col-span-2 space-y-1">
          <Label class="text-xs text-slate-500">Business Address</Label>
          <p class="font-medium text-slate-900">
            {{ userRequirements.supplier.address.block_address }}, 
            {{ userRequirements.supplier.address.barangay }}, 
            {{ userRequirements.supplier.address.city }}, 
            {{ userRequirements.supplier.address.province }}
          </p>
        </div>
      </div>
    </div>

    <h4 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i class="fas fa-file-image text-slate-400"></i> Business Documents
    </h4>
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="(doc, label) in {
        'Valid ID': userRequirements.supplier.valid_id_photo_url,
        'DTI Certificate': userRequirements.supplier.dti_certificate_photo_url,
        'Mayor\'s Permit': userRequirements.supplier.mayor_permit_photo_url,
        'Barangay Clearance': userRequirements.supplier.barangay_clearance_photo_url,
        'Business Registration': userRequirements.supplier.business_registration_photo_url
      }" :key="label">
        <div v-if="doc" 
             class="group relative aspect-[4/3] bg-slate-100 rounded-lg overflow-hidden border border-slate-200 cursor-pointer shadow-sm hover:shadow-md transition-all"
             @click="showImageModal(doc, label)">
          <img :src="doc" :alt="label" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
          <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-3 opacity-100 transition-opacity">
            <span class="text-white text-xs font-semibold">{{ label }}</span>
          </div>
          <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <i class="fas fa-search-plus text-white text-xl"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div v-if="(viewingUser.role === 'hr_manager' && userRequirements.hr_manager) || (viewingUser.role === 'finance_manager' && userRequirements.finance_manager)">
    <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 mb-6">
      <h4 class="text-sm font-semibold text-slate-900 mb-4 uppercase tracking-wider">Employment Details</h4>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Position</Label>
          <p class="font-medium text-slate-900">
            {{ viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.position : userRequirements.finance_manager.position }}
          </p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Salary</Label>
          <p class="font-medium text-slate-900">
            {{ formatCurrency(viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.salary : userRequirements.finance_manager.salary) }}
          </p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Employment Type</Label>
          <p class="font-medium text-slate-900">
            {{ viewingUser.role === 'hr_manager' ? (userRequirements.hr_manager.employment_type_display || userRequirements.hr_manager.employment_type) : (userRequirements.finance_manager.employment_type_display || userRequirements.finance_manager.employment_type) }}
          </p>
        </div>
        <div class="space-y-1">
          <Label class="text-xs text-slate-500">Hire Date</Label>
          <p class="font-medium text-slate-900">
            {{ formatDate(viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.hire_date : userRequirements.finance_manager.hire_date) }}
          </p>
        </div>
      </div>
    </div>

    <h4 class="text-sm font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <i class="fas fa-folder text-slate-400"></i> Employment Documents
    </h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div v-if="viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.valid_id_photo_url : userRequirements.finance_manager.valid_id_photo_url"
           class="group relative aspect-video bg-slate-100 rounded-lg overflow-hidden border border-slate-200 cursor-pointer"
           @click="showImageModal(viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.valid_id_photo_url : userRequirements.finance_manager.valid_id_photo_url, 'Valid ID')">
        <img :src="viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.valid_id_photo_url : userRequirements.finance_manager.valid_id_photo_url" class="w-full h-full object-cover">
        <div class="absolute bottom-0 left-0 right-0 p-2 bg-black/50 text-white text-xs font-medium text-center">Valid ID</div>
      </div>

      <Button v-if="viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.resume_url : userRequirements.finance_manager.resume_url" 
              variant="outline" class="h-full min-h-[100px] flex flex-col gap-2 hover:bg-slate-50 hover:border-blue-300" 
              as="a" :href="viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.resume_url : userRequirements.finance_manager.resume_url" target="_blank">
        <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
        <span>View Resume</span>
      </Button>

      <Button v-if="viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.employment_contract_url : userRequirements.finance_manager.employment_contract_url" 
              variant="outline" class="h-full min-h-[100px] flex flex-col gap-2 hover:bg-slate-50 hover:border-blue-300" 
              as="a" :href="viewingUser.role === 'hr_manager' ? userRequirements.hr_manager.employment_contract_url : userRequirements.finance_manager.employment_contract_url" target="_blank">
        <i class="fas fa-file-contract text-blue-500 text-2xl"></i>
        <span>View Contract</span>
      </Button>
    </div>
  </div>

  <div v-if="(viewingUser.role === 'client' && userRequirements.client) || (viewingUser.role === 'service_provider' && userRequirements.service_provider) || (viewingUser.role === 'operational_distributor' && userRequirements.operational_distributor)">
    <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 mb-6 flex items-center justify-between">
      <div class="space-y-1">
        <Label class="text-xs text-slate-500">ID Type</Label>
        <p class="font-medium text-slate-900">
          {{ viewingUser.role === 'client' ? (userRequirements.client.valid_id_type_display || userRequirements.client.valid_id_type) : 
             viewingUser.role === 'service_provider' ? (userRequirements.service_provider.valid_id_type_display || userRequirements.service_provider.valid_id_type) :
             (userRequirements.operational_distributor.valid_id_type_display || userRequirements.operational_distributor.valid_id_type) }}
        </p>
      </div>
      <div class="space-y-1 text-right">
        <Label class="text-xs text-slate-500">ID Number</Label>
        <p class="font-medium text-slate-900 font-mono">
          {{ viewingUser.role === 'client' ? userRequirements.client.id_number : 
             viewingUser.role === 'service_provider' ? userRequirements.service_provider.id_number :
             userRequirements.operational_distributor.id_number }}
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-if="viewingUser.role === 'client' ? userRequirements.client.valid_id_photo_url : 
                 viewingUser.role === 'service_provider' ? userRequirements.service_provider.valid_id_photo_url :
                 userRequirements.operational_distributor.valid_id_photo_url">
        <h6 class="text-sm font-medium text-slate-700 mb-3">Valid ID</h6>
        <div class="group relative rounded-lg overflow-hidden border border-slate-200 cursor-pointer shadow-sm hover:shadow-md"
             @click="showImageModal(viewingUser.role === 'client' ? userRequirements.client.valid_id_photo_url : viewingUser.role === 'service_provider' ? userRequirements.service_provider.valid_id_photo_url : userRequirements.operational_distributor.valid_id_photo_url, 'Valid ID')">
          <img :src="viewingUser.role === 'client' ? userRequirements.client.valid_id_photo_url : viewingUser.role === 'service_provider' ? userRequirements.service_provider.valid_id_photo_url : userRequirements.operational_distributor.valid_id_photo_url" class="w-full h-48 object-cover">
          <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <i class="fas fa-expand text-white text-xl"></i>
          </div>
        </div>
      </div>

      <div v-if="viewingUser.role === 'service_provider' && userRequirements.service_provider.selfie_with_id_photo_url">
        <h6 class="text-sm font-medium text-slate-700 mb-3">Selfie with ID</h6>
        <div class="group relative rounded-lg overflow-hidden border border-slate-200 cursor-pointer shadow-sm hover:shadow-md"
             @click="showImageModal(userRequirements.service_provider.selfie_with_id_photo_url, 'Selfie with ID')">
          <img :src="userRequirements.service_provider.selfie_with_id_photo_url" class="w-full h-48 object-cover">
          <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <i class="fas fa-expand text-white text-xl"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
                  </CardContent>
                </Card>
              </div>
            </div>
          </ScrollArea>

          <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
             <Button variant="outline" @click="closeViewModal">Close</Button>
             <Button 
                v-if="(viewingUser.role === 'distributor' || viewingUser.role === 'supplier') && viewingUser.verification_status === 'pending'"
                @click="reviewRequirements(viewingUser)"
                class="bg-purple-600 hover:bg-purple-700 text-white"
             >
                <i class="fas fa-file-contract mr-2"></i> Review Requirements
             </Button>
          </div>
        </div>
      </div>
    </Dialog>

    <Dialog :open="showAddUserModal || showEditUserModal" @update:open="val => val ? null : (showAddUserModal ? closeAddUserModal() : closeEditUserModal())">
      <div v-if="showAddUserModal || showEditUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <Card class="w-full max-w-2xl shadow-xl">
          <CardHeader class="border-b">
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>{{ showAddUserModal ? 'Add New User' : 'Edit User' }}</CardTitle>
                <p class="text-sm text-slate-500 mt-1">{{ showAddUserModal ? 'Create a new user account' : 'Update user information' }}</p>
              </div>
              <Button variant="ghost" size="icon" @click="showAddUserModal ? closeAddUserModal() : closeEditUserModal()">
                <i class="fas fa-times"></i>
              </Button>
            </div>
          </CardHeader>
          <CardContent class="p-6">
            <form @submit.prevent="showAddUserModal ? addNewUser() : saveEditedUser()">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div class="space-y-2">
                    <Label>First Name <span class="text-red-500">*</span></Label>
                    <Input v-model="(showAddUserModal ? newUser : editingUser).first_name" required />
                 </div>
                 <div class="space-y-2">
                    <Label>Last Name <span class="text-red-500">*</span></Label>
                    <Input v-model="(showAddUserModal ? newUser : editingUser).last_name" required />
                 </div>
                 <div class="space-y-2">
                    <Label>Email <span class="text-red-500">*</span></Label>
                    <Input type="email" v-model="(showAddUserModal ? newUser : editingUser).email" required />
                 </div>
                 <div class="space-y-2">
                    <Label>Phone</Label>
                    <Input type="tel" v-model="(showAddUserModal ? newUser : editingUser).phone" />
                 </div>
                 <div class="md:col-span-2 space-y-2">
                    <Label>Address</Label>
                    <Input v-model="(showAddUserModal ? newUser : editingUser).address" />
                 </div>
                 <div class="space-y-2">
                    <Label>Role <span class="text-red-500">*</span></Label>
                    <select 
                       v-model="(showAddUserModal ? newUser : editingUser).role" 
                       required
                       class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                       <option value="">Select Role</option>
                       <option value="admin">Administrator</option>
                       <option value="distributor">Distributor</option>
                       <option value="service_provider">Service Provider</option>
                       <option value="client">Client</option>
                       <option value="operational_distributor">Operational Distributor</option>
                       <option value="hr_manager">HR Manager</option>
                       <option value="finance_manager">Finance Manager</option>
                       <option value="supplier">Supplier</option>
                    </select>
                 </div>
                 <div class="space-y-2">
                    <Label>Status</Label>
                    <select 
                       v-model="(showAddUserModal ? newUser : editingUser).status"
                       class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                       <option value="active">Active</option>
                       <option value="inactive">Inactive</option>
                       <option value="pending">Pending</option>
                    </select>
                 </div>
                 
                 <template v-if="showAddUserModal">
                    <div class="space-y-2">
                       <Label>Password <span class="text-red-500">*</span></Label>
                       <div class="relative">
                          <Input :type="showPassword ? 'text' : 'password'" v-model="newUser.password" required />
                          <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-700">
                             <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                          </button>
                       </div>
                    </div>
                    <div class="space-y-2">
                       <Label>Confirm Password <span class="text-red-500">*</span></Label>
                       <Input :type="showPassword ? 'text' : 'password'" v-model="newUser.password_confirmation" required />
                    </div>
                 </template>
              </div>

              <div class="flex justify-end gap-3 mt-6 pt-6 border-t">
                 <Button type="button" variant="outline" @click="showAddUserModal ? closeAddUserModal() : closeEditUserModal()">Cancel</Button>
                 <Button type="submit" :disabled="addingUser || updatingUser" class="bg-blue-600 hover:bg-blue-700 text-white min-w-[120px]">
                    <i v-if="addingUser || updatingUser" class="fas fa-spinner fa-spin mr-2"></i>
                    {{ showAddUserModal ? (addingUser ? 'Creating...' : 'Create User') : (updatingUser ? 'Saving...' : 'Save Changes') }}
                 </Button>
              </div>
            </form>
          </CardContent>
        </Card>
      </div>
    </Dialog>

    <Dialog :open="showImageModalFlag" @update:open="closeImageModal">
      <div v-if="showImageModalFlag" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
          <div class="flex items-center justify-between p-4 border-b">
            <h3 class="font-bold text-lg">{{ currentImageTitle }}</h3>
            <Button variant="ghost" size="icon" @click="closeImageModal">
               <i class="fas fa-times">X</i>
            </Button>
          </div>
          <div class="flex-1 p-4 bg-slate-900 flex items-center justify-center overflow-auto">
             <img :src="currentImageUrl" class="max-w-full max-h-[70vh] rounded shadow-lg object-contain" />
          </div>
          <div class="p-4 border-t flex justify-end">
            <Button variant="ghost" size="icon" @click="closeImageModal">
               <i class="fas fa-times">Close</i>
            </Button>
             <Button as="a" :href="currentImageUrl" target="_blank" variant="secondary">
                <i class="fas fa-external-link-alt mr-2"></i> Open Original
             </Button>
          </div>
        </div>
      </div>
    </Dialog>

    <AlertDialog :open="alertDialog.open" @update:open="alertDialog.open = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>{{ alertDialog.title }}</AlertDialogTitle>
          <AlertDialogDescription>{{ alertDialog.description }}</AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="alertDialog.open = false">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="handleAlertDialogAction" class="bg-blue-600 text-white hover:bg-blue-700">Continue</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script>
import axios from 'axios';
import { toast } from 'vue-sonner';
// Shadcn Components Imports
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table';
import { Dialog } from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Separator } from '@/components/ui/separator';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'

export default {
  name: 'UserManagement',
  components: {
    Button, Input, Label, Badge, 
    Card, CardHeader, CardTitle, CardContent,
    Table, TableHeader, TableBody, TableHead, TableRow, TableCell,
    Dialog, ScrollArea, Separator,
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
  },
  data() {
    return {
      // Tabs data
      tabs: [
        { label: 'All Users', value: 'all', icon: 'fas fa-users' },
        { label: 'Admins', value: 'admin', icon: 'fas fa-user-shield' },
        { label: 'Distributors', value: 'distributor', icon: 'fas fa-truck' },
        { label: 'Suppliers', value: 'supplier', icon: 'fas fa-boxes' }, // Added Supplier
        { label: 'Service Providers', value: 'service_provider', icon: 'fas fa-tools' },
        { label: 'Clients', value: 'client', icon: 'fas fa-user' },
        { label: 'Operational', value: 'operational_distributor', icon: 'fas fa-cogs' },
        { label: 'HR Managers', value: 'hr_manager', icon: 'fas fa-user-tie' },
        { label: 'Finance', value: 'finance_manager', icon: 'fas fa-chart-line' }
      ],
      
      // State
      activeTab: 'all',
      searchQuery: '',
      statusFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      showAddUserModal: false,
      showEditUserModal: false,
      showViewModal: false,
      showImageModalFlag: false,
      showPassword: false,
      loading: false,
      loadingRequirements: false,
      addingUser: false,
      updatingUser: false,
      
      // Data
      users: [],
      statistics: {},
      pagination: {
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
      },
      
      // User objects
      newUser: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active',
        password: '',
        password_confirmation: ''
      },
      editingUser: {
        id: null,
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active'
      },
      viewingUser: {},
      userRequirements: null,
      currentImageUrl: '',
      currentImageTitle: '',
      
      debounceTimer: null,
      
      // Alert Dialog State
      alertDialog: {
        open: false,
        title: '',
        description: '',
        action: null
      }
    }
  },
  mounted() {
    this.fetchUsers();
    this.fetchStatistics();
  },
  watch: {
    activeTab() {
      this.currentPage = 1;
      this.fetchUsers();
    },
    currentPage() {
      this.fetchUsers();
    }
  },
  methods: {
    // Alert Dialog Helper
    confirmAction(title, description, action) {
      this.alertDialog = {
        open: true,
        title,
        description,
        action
      };
    },
    
    async handleAlertDialogAction() {
      if (this.alertDialog.action) {
        await this.alertDialog.action();
      }
      this.alertDialog.open = false;
    },

    // Fetch users with role-based filtering
    async fetchUsers() {
      this.loading = true;
      try {
        const token = localStorage.getItem('auth_token');
        if (!token) {
          toast.error('Please login first');
          this.$router.push('/login');
          return;
        }

        const params = {
          page: this.currentPage,
          per_page: this.itemsPerPage,
          status: this.statusFilter !== 'all' ? this.statusFilter : undefined,
          search: this.searchQuery || undefined,
          role: this.activeTab !== 'all' ? this.activeTab : undefined
        };

        const baseURL = 'http://localhost:8000';
        const response = await axios.get(`${baseURL}/api/admin/users`, {
          headers: { 
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          params: params
        });

        if (response.data.success) {
          this.users = response.data.users;
          this.pagination = response.data.pagination;
          this.statistics = response.data.statistics || this.statistics;
        } else {
          toast.error(response.data.message || 'Failed to load users');
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.loading = false;
      }
    },
    
    // Fetch statistics
    async fetchStatistics() {
      try {
        // First try the API
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        
        try {
          const response = await axios.get(`${baseURL}/api/admin/users/statistics`, {
            headers: { Authorization: `Bearer ${token}` }
          });
          
          if (response.data.success) {
            this.statistics = response.data.statistics;
            return;
          }
        } catch (apiError) {
          // Silent catch for stats API failure, fallback to calculation
        }
        
        // Fallback: Calculate from existing users data
        this.calculateStatisticsFromUsers();
        
      } catch (error) {
        this.calculateStatisticsFromUsers();
      }
    },

    calculateStatisticsFromUsers() {
      // Calculate statistics from the users you already have
      const stats = {
        total: this.users.length,
        admin: this.users.filter(u => u.role === 'admin').length,
        distributor: this.users.filter(u => u.role === 'distributor').length,
        service_provider: this.users.filter(u => u.role === 'service_provider').length,
        client: this.users.filter(u => u.role === 'client').length,
        operational_distributor: this.users.filter(u => u.role === 'operational_distributor').length,
        hr_manager: this.users.filter(u => u.role === 'hr_manager').length,
        finance_manager: this.users.filter(u => u.role === 'finance_manager').length,
        supplier: this.users.filter(u => u.role === 'supplier').length, // Added Supplier Count
        active: this.users.filter(u => u.status === 'active').length,
        inactive: this.users.filter(u => u.status === 'inactive').length,
        pending: this.users.filter(u => u.status === 'pending').length,
      };
      
      this.statistics = stats;
    },
    
    // Fetch user requirements
    async fetchUserRequirements(userId) {
      this.loadingRequirements = true;
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        
        // Reset requirements
        this.userRequirements = null;
        
        // Fetch user details which should include requirements data
        const response = await axios.get(`${baseURL}/api/admin/users/${userId}`, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          const user = response.data.user;
          const reqs = response.data.requirements;
          
          if (reqs && reqs.supplier) {
             this.userRequirements = {
                supplier: {
                    ...reqs.supplier,
                    valid_id_photo_url: reqs.supplier.valid_id_photo ? `${baseURL}/storage/${reqs.supplier.valid_id_photo}` : null,
                    dti_certificate_photo_url: reqs.supplier.dti_certificate_photo ? `${baseURL}/storage/${reqs.supplier.dti_certificate_photo}` : null,
                    mayor_permit_photo_url: reqs.supplier.mayor_permit_photo ? `${baseURL}/storage/${reqs.supplier.mayor_permit_photo}` : null,
                    barangay_clearance_photo_url: reqs.supplier.barangay_clearance_photo ? `${baseURL}/storage/${reqs.supplier.barangay_clearance_photo}` : null,
                    business_registration_photo_url: reqs.supplier.business_registration_photo ? `${baseURL}/storage/${reqs.supplier.business_registration_photo}` : null
                }
             };
          } else {
              switch (user.role) {
                case 'distributor':
                  if (user.verification_details) {
                    this.userRequirements = {
                      distributor: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                        dti_certificate_photo_url: user.verification_details.dti_certificate_photo ? 
                          `${baseURL}/storage/${user.verification_details.dti_certificate_photo}` : null,
                        mayor_permit_photo_url: user.verification_details.mayor_permit_photo ? 
                          `${baseURL}/storage/${user.verification_details.mayor_permit_photo}` : null,
                        barangay_clearance_photo_url: user.verification_details.barangay_clearance_photo ? 
                          `${baseURL}/storage/${user.verification_details.barangay_clearance_photo}` : null,
                        business_registration_photo_url: user.verification_details.business_registration_photo ? 
                          `${baseURL}/storage/${user.verification_details.business_registration_photo}` : null
                      }
                    };
                  }
                  break;
                  
                case 'client':
                  if (user.verification_details) {
                    this.userRequirements = {
                      client: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null
                      }
                    };
                  }
                  break;
                  
                case 'service_provider':
                  if (user.verification_details) {
                    this.userRequirements = {
                      service_provider: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                        selfie_with_id_photo_url: user.verification_details.selfie_with_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.selfie_with_id_photo}` : null
                      }
                    };
                  }
                  break;
                  
                case 'hr_manager':
                  if (user.verification_details) {
                    this.userRequirements = {
                      hr_manager: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                        resume_url: user.verification_details.resume ? 
                          `${baseURL}/storage/${user.verification_details.resume}` : null,
                        employment_contract_url: user.verification_details.employment_contract ? 
                          `${baseURL}/storage/${user.verification_details.employment_contract}` : null
                      }
                    };
                  }
                  break;
                  
                case 'finance_manager':
                  if (user.verification_details) {
                    this.userRequirements = {
                      finance_manager: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                        resume_url: user.verification_details.resume ? 
                          `${baseURL}/storage/${user.verification_details.resume}` : null,
                        employment_contract_url: user.verification_details.employment_contract ? 
                          `${baseURL}/storage/${user.verification_details.employment_contract}` : null
                      }
                    };
                  }
                  break;
                  
                case 'operational_distributor':
                  if (user.verification_details) {
                    this.userRequirements = {
                      operational_distributor: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null
                      }
                    };
                  }
                  break;

                case 'supplier': // Added Supplier fallback
                  if (user.verification_details) {
                    this.userRequirements = {
                      supplier: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? 
                          `${baseURL}/storage/${user.verification_details.valid_id_photo}` : null,
                        dti_certificate_photo_url: user.verification_details.dti_certificate_photo ? 
                          `${baseURL}/storage/${user.verification_details.dti_certificate_photo}` : null,
                        mayor_permit_photo_url: user.verification_details.mayor_permit_photo ? 
                          `${baseURL}/storage/${user.verification_details.mayor_permit_photo}` : null,
                        barangay_clearance_photo_url: user.verification_details.barangay_clearance_photo ? 
                          `${baseURL}/storage/${user.verification_details.barangay_clearance_photo}` : null,
                        business_registration_photo_url: user.verification_details.business_registration_photo ? 
                          `${baseURL}/storage/${user.verification_details.business_registration_photo}` : null
                      }
                    };
                  }
                  break;
              }
          }
        }
      } catch (error) {
        this.userRequirements = null;
      } finally {
        this.loadingRequirements = false;
      }
    },
    
    // Show image in modal
    showImageModal(imageUrl, title) {
      this.currentImageUrl = imageUrl;
      this.currentImageTitle = title;
      this.showImageModalFlag = true;
    },
    
    // Close image modal
    closeImageModal() {
      this.showImageModalFlag = false;
      this.currentImageUrl = '';
      this.currentImageTitle = '';
    },
    
    // Debounced search
    debouncedFetchUsers() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1;
        this.fetchUsers();
      }, 500);
    },
    
    // Pagination methods
    getPaginationRange() {
      const range = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.pagination.last_page, start + 4);
      
      for (let i = start; i <= end; i++) {
        range.push(i);
      }
      return range;
    },
    
    goToPage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.currentPage = page;
        this.fetchUsers();
      }
    },
    
    // User actions
    async viewUser(user) {
      this.viewingUser = { ...user };
      this.showViewModal = true;
      // Fetch user requirements
      await this.fetchUserRequirements(user.id);
    },
    
    editUser(user) {
      this.editingUser = {
        id: user.id,
        first_name: user.first_name,
        last_name: user.last_name,
        email: user.email,
        phone: user.phone || '',
        address: user.address || '',
        role: user.role,
        status: user.status
      };
      this.showEditUserModal = true;
      this.showViewModal = false;
    },
    
    async saveEditedUser() {
      this.updatingUser = true;
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        const response = await axios.put(`${baseURL}/api/admin/users/${this.editingUser.id}`, this.editingUser, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          toast.success('User updated successfully');
          this.closeEditUserModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.updatingUser = false;
      }
    },
    
    async addNewUser() {
      this.addingUser = true;
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        const response = await axios.post(`${baseURL}/api/admin/users`, this.newUser, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (response.data.success) {
          toast.success('User created successfully');
          this.closeAddUserModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.addingUser = false;
      }
    },
    
    // Approve user (activate account and approve requirements)
    approveUser(user) {
      this.confirmAction(
        'Approve User',
        `Are you sure you want to approve ${user.full_name}? This will activate their account and approve all requirements.`,
        async () => {
          try {
            const token = localStorage.getItem('auth_token');
            const baseURL = 'http://localhost:8000';
            
            // First, try the approve endpoint (if it exists)
            try {
              const approveResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/approve`, {}, {
                headers: { Authorization: `Bearer ${token}` }
              });
              
              if (approveResponse.data.success) {
                toast.success('User approved successfully');
                this.closeViewModal();
                this.fetchUsers();
                this.fetchStatistics();
                return;
              }
            } catch (approveError) {
              // Proceed to fallback
            }
            
            // Fallback to activate endpoint
            const userResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/activate`, {}, {
              headers: { Authorization: `Bearer ${token}` }
            });
            
            if (userResponse.data.success) {
              toast.success('User activated successfully');
              
              // If user is distributor, also approve their requirements
              if (user.role === 'distributor' && user.verification_status === 'pending') {
                try {
                  const distributorReq = await axios.get(`${baseURL}/api/distributor/requirements/admin/pending`, {
                    headers: { Authorization: `Bearer ${token}` }
                  });
                  
                  const pendingReq = distributorReq.data.data?.find(req => req.user_id === user.id);
                  if (pendingReq) {
                    await axios.put(`${baseURL}/api/distributor/requirements/admin/${pendingReq.id}`, {
                      status: 'approved',
                      rejection_reason: null
                    }, {
                      headers: { Authorization: `Bearer ${token}` }
                    });
                    toast.success('Distributor requirements approved');
                  }
                } catch (error) {
                  // Silent fail for nested requirement approval
                }
              }

              // Added Supplier fallback if specific Approve endpoint failed
              if (user.role === 'supplier' && user.verification_status === 'pending') {
                  // Assuming logic is handled by the unified approve endpoint now.
              }
              
              this.closeViewModal();
              this.fetchUsers();
              this.fetchStatistics();
            }
          } catch (error) {
            this.handleError(error);
          }
        }
      );
    },
    
    // Reject user (with reason)
    async rejectUser(user) {
      const reason = prompt(`Please enter reason for rejecting ${user.full_name}:`, '');
      if (reason === null) return; // User cancelled
      
      if (!reason.trim()) {
        toast.error('Please provide a rejection reason');
        return;
      }
      
      try {
        const token = localStorage.getItem('auth_token');
        const baseURL = 'http://localhost:8000';
        
        // First, try the reject endpoint (if it exists)
        try {
          const rejectResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/reject`, {
            reason: reason
          }, {
            headers: { Authorization: `Bearer ${token}` }
          });
          
          if (rejectResponse.data.success) {
            toast.success(`User rejected: ${reason}`);
            this.closeViewModal();
            this.fetchUsers();
            this.fetchStatistics();
            return;
          }
        } catch (rejectError) {
          // Proceed to fallback
        }
        
        // Fallback to deactivate endpoint
        const userResponse = await axios.post(`${baseURL}/api/admin/users/${user.id}/deactivate`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
        
        if (userResponse.data.success) {
          // If user is distributor, also reject their requirements
          if (user.role === 'distributor' && user.verification_status === 'pending') {
            try {
              const distributorReq = await axios.get(`${baseURL}/api/distributor/requirements/admin/pending`, {
                headers: { Authorization: `Bearer ${token}` }
              });
              
              const pendingReq = distributorReq.data.data?.find(req => req.user_id === user.id);
              if (pendingReq) {
                await axios.put(`${baseURL}/api/distributor/requirements/admin/${pendingReq.id}`, {
                  status: 'rejected',
                  rejection_reason: reason
                }, {
                  headers: { Authorization: `Bearer ${token}` }
                });
              }
            } catch (error) {
              // Silent fail for nested requirement rejection
            }
          }
          
          toast.success(`User rejected: ${reason}`);
          this.closeViewModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      }
    },
    
    // Activate user
    activateUser(user) {
      this.confirmAction(
        'Activate User',
        `Are you sure you want to activate ${user.full_name}?`,
        async () => {
          try {
            const token = localStorage.getItem('auth_token');
            const baseURL = 'http://localhost:8000';
            const response = await axios.post(`${baseURL}/api/admin/users/${user.id}/activate`, {}, {
              headers: { Authorization: `Bearer ${token}` }
            });
            
            if (response.data.success) {
              toast.success('User activated successfully');
              this.fetchUsers();
              this.fetchStatistics();
            }
          } catch (error) {
            this.handleError(error);
          }
        }
      );
    },
    
    // Deactivate user
    deactivateUser(user) {
      this.confirmAction(
        'Deactivate User',
        `Are you sure you want to deactivate ${user.full_name}?`,
        async () => {
          try {
            const token = localStorage.getItem('auth_token');
            const baseURL = 'http://localhost:8000';
            const response = await axios.post(`${baseURL}/api/admin/users/${user.id}/deactivate`, {}, {
              headers: { Authorization: `Bearer ${token}` }
            });
            
            if (response.data.success) {
              toast.success('User deactivated successfully');
              this.fetchUsers();
              this.fetchStatistics();
            }
          } catch (error) {
            this.handleError(error);
          }
        }
      );
    },
    
    // Review requirements (for distributors and suppliers)
    async reviewRequirements(user) {
      toast.info('Redirecting to requirements review page...');
      // In a real app, you would navigate to the requirements review page
      // this.$router.push(`/admin/requirements/${user.role}/${user.id}`);
    },
    
    // Modal methods
    closeViewModal() {
      this.showViewModal = false;
      this.viewingUser = {};
      this.userRequirements = null;
      this.loadingRequirements = false;
    },
    
    closeEditUserModal() {
      this.showEditUserModal = false;
      this.editingUser = {
        id: null,
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active'
      };
    },
    
    closeAddUserModal() {
      this.showAddUserModal = false;
      this.newUser = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: 'active',
        password: '',
        password_confirmation: ''
      };
      this.showPassword = false;
    },
    
    // Format currency
    formatCurrency(amount) {
      if (!amount) return '₱0.00';
      return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    // Style helper methods
    getRoleBadgeClass(role) {
      const classes = {
        'admin': 'border-red-200 bg-red-50 text-red-700 hover:bg-red-50',
        'distributor': 'border-green-200 bg-green-50 text-green-700 hover:bg-green-50',
        'service_provider': 'border-purple-200 bg-purple-50 text-purple-700 hover:bg-purple-50',
        'client': 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-50',
        'operational_distributor': 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-50',
        'hr_manager': 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-50',
        'finance_manager': 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-50',
        'supplier': 'border-orange-200 bg-orange-50 text-orange-700 hover:bg-orange-50'
      };
      return classes[role] || 'border-slate-200 bg-slate-50 text-slate-700';
    },
    
    getRoleIcon(role) {
      const icons = {
        'admin': 'fas fa-user-shield',
        'distributor': 'fas fa-truck',
        'service_provider': 'fas fa-tools',
        'client': 'fas fa-user',
        'operational_distributor': 'fas fa-cogs',
        'hr_manager': 'fas fa-user-tie',
        'finance_manager': 'fas fa-chart-line',
        'supplier': 'fas fa-boxes' // Added Supplier
      };
      return icons[role] || 'fas fa-user';
    },
    
    getVerificationBadgeClass(status) {
      if (!status) return 'bg-slate-100 text-slate-600 hover:bg-slate-200';
      
      const classes = {
        'approved': 'bg-green-100 text-green-700 hover:bg-green-200',
        'verified': 'bg-green-100 text-green-700 hover:bg-green-200',
        'pending': 'bg-amber-100 text-amber-700 hover:bg-amber-200',
        'rejected': 'bg-red-100 text-red-700 hover:bg-red-200'
      };
      return classes[status.toLowerCase()] || 'bg-slate-100 text-slate-600';
    },
    
    getVerificationIcon(status) {
      if (!status) return 'fas fa-question-circle';
      
      const icons = {
        'approved': 'fas fa-check-circle',
        'verified': 'fas fa-check-circle',
        'pending': 'fas fa-clock',
        'rejected': 'fas fa-times-circle'
      };
      return icons[status.toLowerCase()] || 'fas fa-question-circle';
    },
    
    getStatusBadgeClass(status) {
      const classes = {
        'active': 'border-green-200 bg-green-50 text-green-700',
        'inactive': 'border-red-200 bg-red-50 text-red-700',
        'pending': 'border-amber-200 bg-amber-50 text-amber-700'
      };
      return classes[status] || 'border-slate-200 bg-slate-50 text-slate-700';
    },
    
    getStatusDotClass(status) {
      const classes = {
        'active': 'bg-green-500',
        'inactive': 'bg-red-500',
        'pending': 'bg-amber-500'
      };
      return classes[status] || 'bg-slate-500';
    },
    
    getStatusIcon(status) {
      const icons = {
        'active': 'fas fa-check-circle text-green-600',
        'inactive': 'fas fa-times-circle text-red-600',
        'pending': 'fas fa-clock text-amber-600'
      };
      return icons[status] || 'fas fa-question-circle text-slate-600';
    },
    
    getStatusColorClass(status) {
      const classes = {
        'active': 'bg-green-100',
        'inactive': 'bg-red-100',
        'pending': 'bg-amber-100'
      };
      return classes[status] || 'bg-slate-100';
    },
    
    getVerificationDetails(user) {
      if (!user.verification_details) return '';
      
      const details = user.verification_details;
      if (user.role === 'distributor') {
        return details.company_name || '';
      } else if (user.role === 'client' || user.role === 'service_provider') {
        return details.valid_id_type || '';
      } else if (user.role === 'supplier') { // Added Supplier logic
        return details.company_name || '';
      }
      return '';
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    handleError(error) {
      let errorMessage = 'An error occurred';
      
      if (error.code === 'ERR_NETWORK') {
        errorMessage = 'Cannot connect to server. Make sure Laravel backend is running.';
      } else if (error.response) {
        if (error.response.status === 401) {
          errorMessage = 'Session expired. Please login again.';
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user_data');
          localStorage.removeItem('user_role');
          this.$router.push('/login');
        } else if (error.response.status === 403) {
          errorMessage = 'Access denied. Admin privileges required.';
        } else if (error.response.status === 404) {
          errorMessage = 'API endpoint not found. Check if the route exists in Laravel.';
        } else if (error.response.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          errorMessage = errors.join(', ');
        } else if (error.response.data?.message) {
          errorMessage = error.response.data.message;
        } else {
          errorMessage = 'Server error occurred.';
        }
      } else if (error.request) {
        errorMessage = 'No response from server. Check if backend is running.';
      } else {
        errorMessage = 'An error occurred: ' + error.message;
      }
      
      toast.error(errorMessage);
    }
  }
}
</script>