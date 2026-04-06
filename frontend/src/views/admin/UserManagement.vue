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
              class="appearance-none flex h-10 w-full sm:w-[150px] items-center justify-between rounded-md border border-slate-200 bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
              style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.7rem top 50%; background-size: 0.65rem auto;"
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
                       <template v-if="viewingUser.status === 'pending' || viewingUser.verification_status === 'pending'">
                          <Button @click="approveUser(viewingUser)" class="w-full justify-start gap-2 bg-green-600 hover:bg-green-700 text-white">
                             <i class="fas fa-check-circle"></i> Approve User
                          </Button>
                          <Button @click="openRejectModal(viewingUser)" variant="destructive" class="w-full justify-start gap-2">
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

  <div v-if="(viewingUser.role === 'client' && userRequirements.client) || (viewingUser.role === 'service_provider' && userRequirements.service_provider)">
    <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 mb-6 flex items-center justify-between">
      <div class="space-y-1">
        <Label class="text-xs text-slate-500">ID Type</Label>
        <p class="font-medium text-slate-900">
          {{ viewingUser.role === 'client' ? (userRequirements.client.valid_id_type_display || userRequirements.client.valid_id_type) : 
             (userRequirements.service_provider.valid_id_type_display || userRequirements.service_provider.valid_id_type) }}
        </p>
      </div>
      <div class="space-y-1 text-right">
        <Label class="text-xs text-slate-500">ID Number</Label>
        <p class="font-medium text-slate-900 font-mono">
          {{ viewingUser.role === 'client' ? userRequirements.client.id_number : 
             userRequirements.service_provider.id_number }}
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-if="viewingUser.role === 'client' ? userRequirements.client.valid_id_photo_url : userRequirements.service_provider.valid_id_photo_url">
        <h6 class="text-sm font-medium text-slate-700 mb-3">Valid ID</h6>
        <div class="group relative rounded-lg overflow-hidden border border-slate-200 cursor-pointer shadow-sm hover:shadow-md"
             @click="showImageModal(viewingUser.role === 'client' ? userRequirements.client.valid_id_photo_url : userRequirements.service_provider.valid_id_photo_url, 'Valid ID')">
          <img :src="viewingUser.role === 'client' ? userRequirements.client.valid_id_photo_url : userRequirements.service_provider.valid_id_photo_url" class="w-full h-48 object-cover">
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

    <Dialog :open="showAddUserModal" @update:open="val => val ? null : closeAddUserModal()">
      <div v-if="showAddUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <Card class="w-full max-w-3xl shadow-xl max-h-[90vh] flex flex-col">
          <CardHeader class="border-b shrink-0 pb-4">
            <div class="flex items-center justify-between">
              <div>
                <CardTitle>Add New User</CardTitle>
                <p class="text-sm text-slate-500 mt-1">Step {{ addUserStep }} of 4: {{ addUserSteps[addUserStep - 1].title }}</p>
              </div>
              <Button variant="ghost" size="icon" @click="closeAddUserModal()">
                <i class="fas fa-times text-slate-500"></i>
              </Button>
            </div>
             <div class="mt-4">
                <div class="flex justify-between mb-2">
                  <div v-for="step in 4" :key="step" class="flex flex-col items-center flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition-colors"
                         :class="step === addUserStep ? 'border-blue-600 bg-blue-600 text-white' : (step < addUserStep ? 'border-green-500 bg-green-500 text-white' : 'border-slate-300 bg-white text-slate-400')">
                      <i v-if="step < addUserStep" class="fas fa-check text-xs"></i>
                      <span v-else class="text-sm font-medium">{{ step }}</span>
                    </div>
                    <span class="text-[11px] mt-1 font-medium" :class="step === addUserStep ? 'text-blue-600' : 'text-slate-500'">{{ addUserSteps[step - 1].short }}</span>
                  </div>
                </div>
                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                   <div class="h-full bg-blue-600 transition-all duration-300" :style="`width: ${((addUserStep - 1) / 3) * 100}%`"></div>
                </div>
             </div>
          </CardHeader>
          <CardContent class="p-6 overflow-y-auto flex-1">
             <div v-if="addUserStep === 1" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="role in availableRoles" :key="role.value"
                     @click="newUser.role = role.value"
                     class="cursor-pointer p-4 rounded-xl border-2 transition-all hover:shadow-md"
                     :class="newUser.role === role.value ? 'border-blue-600 bg-blue-50/50 ring-4 ring-blue-100/50' : 'border-slate-200 hover:border-blue-300'">
                     <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white" :class="role.colorClass">
                           <i :class="role.icon"></i>
                        </div>
                        <h4 class="font-bold text-slate-800">{{ role.label }}</h4>
                     </div>
                     <p class="text-xs text-slate-500 leading-relaxed">{{ role.description }}</p>
                </div>
             </div>

             <div v-if="addUserStep === 2" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div class="space-y-2">
                    <Label>First Name <span class="text-red-500">*</span></Label>
                    <Input v-model="newUser.first_name" @input="validateStep2(true)" :class="{'border-red-500 focus:ring-red-500': validationErrors.first_name}" />
                    <span v-if="validationErrors.first_name" class="text-xs text-red-500">{{ validationErrors.first_name }}</span>
                 </div>
                 <div class="space-y-2">
                    <Label>Last Name <span class="text-red-500">*</span></Label>
                    <Input v-model="newUser.last_name" @input="validateStep2(true)" :class="{'border-red-500 focus:ring-red-500': validationErrors.last_name}" />
                    <span v-if="validationErrors.last_name" class="text-xs text-red-500">{{ validationErrors.last_name }}</span>
                 </div>
                 <div class="space-y-2">
                    <Label>Email Address <span class="text-red-500">*</span></Label>
                    <div class="relative">
                      <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                      <Input type="email" v-model="newUser.email" @input="validateStep2(true)" placeholder="user@example.com" class="pl-9" :class="{'border-red-500 focus:ring-red-500': validationErrors.email}" />
                    </div>
                    <span v-if="validationErrors.email" class="text-xs text-red-500">{{ validationErrors.email }}</span>
                 </div>
                 <div class="space-y-2">
                    <Label>Phone Number <span class="text-red-500">*</span></Label>
                    <div class="relative">
                      <i class="fas fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                      <Input type="tel" v-model="newUser.phone" @input="formatPhoneAndValidate" placeholder="09XX XXX XXXX" class="pl-9" :class="{'border-red-500 focus:ring-red-500': validationErrors.phone}" />
                    </div>
                    <span v-if="validationErrors.phone" class="text-xs text-red-500">{{ validationErrors.phone }}</span>
                 </div>
                 <div class="md:col-span-2 space-y-2">
                    <Label>Full Address</Label>
                    <div class="relative">
                      <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                      <Input v-model="newUser.address" class="pl-9" placeholder="Unit, Street, Barangay, City, Province" />
                    </div>
                 </div>
             </div>

             <div v-if="addUserStep === 3" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div class="space-y-2 md:col-span-2">
                    <Label>Initial Account Status</Label>
                    <select
                       v-model="newUser.status"
                       class="appearance-none flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                       style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23131313%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.7rem top 50%; background-size: 0.65rem auto;"
                    >
                       <option value="active">Active</option>
                       <option value="inactive">Inactive</option>
                       <option value="pending">Pending</option>
                    </select>
                 </div>
                 <div class="space-y-2">
                    <Label>Password <span class="text-red-500">*</span></Label>
                    <div class="relative">
                       <Input :type="showPassword ? 'text' : 'password'" v-model="newUser.password" @input="validateStep3(true)" :class="{'border-red-500 focus:ring-red-500': validationErrors.password}" />
                       <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-800 focus:outline-none">
                          <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                       </button>
                    </div>
                    <div v-if="newUser.password" class="space-y-2 mt-2 p-3 bg-slate-50 rounded-lg border border-slate-100">
                      <div class="flex items-center gap-2">
                        <div class="h-1.5 flex-1 bg-slate-200 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300" :class="passwordStrengthClass" :style="`width: ${passwordStrengthScore}%`"></div>
                        </div>
                        <span class="text-xs font-bold" :class="passwordStrengthTextClass">{{ passwordStrength }}</span>
                      </div>
                      <div class="grid grid-cols-2 gap-1.5 mt-2">
                        <div v-for="(req, i) in passwordRequirements" :key="i" class="flex items-center space-x-1.5 text-[10px]" :class="req.met ? 'text-green-600' : 'text-slate-500'">
                          <i :class="req.met ? 'fas fa-check-circle' : 'far fa-circle'"></i>
                          <span>{{ req.text }}</span>
                        </div>
                      </div>
                    </div>
                    <span v-if="validationErrors.password" class="text-xs text-red-500">{{ validationErrors.password }}</span>
                 </div>
                 <div class="space-y-2">
                    <Label>Confirm Password <span class="text-red-500">*</span></Label>
                    <div class="relative">
                       <Input :type="showConfirmPassword ? 'text' : 'password'" v-model="newUser.password_confirmation" @input="validateStep3(true)" :class="{'border-red-500 focus:ring-red-500': validationErrors.password_confirmation}" />
                       <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-800 focus:outline-none">
                          <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                       </button>
                    </div>
                    <span v-if="validationErrors.password_confirmation" class="text-xs text-red-500">{{ validationErrors.password_confirmation }}</span>
                 </div>
             </div>

             <div v-if="addUserStep === 4" class="space-y-6">
                <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100">
                   <h4 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                      <i class="fas fa-clipboard-check text-blue-600"></i> Account Summary
                   </h4>
                   <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                      <div class="flex flex-col">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Role</span> 
                        <span class="font-medium flex items-center gap-2 text-slate-800">
                          <i :class="getRoleIcon(newUser.role)" class="text-blue-500"></i>
                          {{ newUser.role.replace('_', ' ') }}
                        </span>
                      </div>
                      <div class="flex flex-col">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Status</span> 
                        <span class="font-medium capitalize text-slate-800 flex items-center gap-1.5">
                           <span class="w-2 h-2 rounded-full" :class="getStatusDotClass(newUser.status)"></span>
                           {{ newUser.status }}
                        </span>
                      </div>
                      <div class="flex flex-col sm:col-span-2">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Full Name</span> 
                        <span class="font-medium text-slate-800">{{ newUser.first_name }} {{ newUser.last_name }}</span>
                      </div>
                      <div class="flex flex-col">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Contact Phone</span> 
                        <span class="font-medium text-slate-800">{{ newUser.phone }}</span>
                      </div>
                      <div class="flex flex-col">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Email Address</span> 
                        <span class="font-medium text-slate-800">{{ newUser.email }}</span>
                      </div>
                      <div class="flex flex-col sm:col-span-2">
                        <span class="text-xs text-slate-500 uppercase tracking-wider font-semibold mb-1">Location / Address</span> 
                        <span class="font-medium text-slate-800">{{ newUser.address || 'No address provided' }}</span>
                      </div>
                   </div>
                </div>
                <div class="bg-amber-50 p-4 rounded-lg border border-amber-200 flex gap-3 text-amber-800">
                   <i class="fas fa-exclamation-triangle mt-0.5"></i>
                   <p class="text-xs leading-relaxed">
                      Please verify all information above. This user will receive a welcome email, but they will still need to verify their email address and submit their requirements for full approval if required by their role.
                   </p>
                </div>
             </div>
          </CardContent>
          <div class="border-t p-4 bg-slate-50/50 flex justify-between shrink-0 rounded-b-xl">
             <Button type="button" variant="outline" @click="addUserStep > 1 ? prevAddUserStep() : closeAddUserModal()" class="w-24">
                {{ addUserStep > 1 ? 'Back' : 'Cancel' }}
             </Button>
             <Button v-if="addUserStep < 4" type="button" @click="nextAddUserStep" class="bg-blue-600 hover:bg-blue-700 text-white w-24">
                Next
             </Button>
             <Button v-else type="button" @click="addNewUser" :disabled="addingUser" class="bg-emerald-600 hover:bg-emerald-700 text-white min-w-[140px] shadow-md hover:shadow-lg transition-all">
                <i v-if="addingUser" class="fas fa-spinner fa-spin mr-2"></i>
                {{ addingUser ? 'Creating...' : 'Create Account' }}
             </Button>
          </div>
        </Card>
      </div>
    </Dialog>

    <Dialog :open="showRejectModal" @update:open="closeRejectModal">
      <div v-if="showRejectModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <Card class="w-full max-w-md shadow-xl">
          <CardHeader class="border-b">
            <CardTitle>Reject User</CardTitle>
            <p class="text-sm text-slate-500 mt-1">Provide a reason for rejecting {{ userToReject?.full_name }}</p>
          </CardHeader>
          <CardContent class="p-6">
            <div class="space-y-2">
               <Label>Rejection Reason <span class="text-red-500">*</span></Label>
               <textarea 
                  v-model="rejectReason" 
                  rows="4" 
                  class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                  required 
                  placeholder="Enter the reason for rejection here..."></textarea>
            </div>
            <div class="flex justify-end gap-3 mt-6">
               <Button type="button" variant="outline" @click="closeRejectModal">Cancel</Button>
               <Button type="button" @click="confirmRejectUser" :disabled="processingRejection || !rejectReason.trim()" variant="destructive" class="min-w-[120px]">
                  <i v-if="processingRejection" class="fas fa-spinner fa-spin mr-2"></i>
                  {{ processingRejection ? 'Rejecting...' : 'Reject User' }}
               </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </Dialog>

    <Dialog :open="showImageModalFlag" @update:open="closeImageModal">
      <div v-if="showImageModalFlag" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
        <div class="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
          <div class="flex items-center justify-between p-4 border-b">
            <h3 class="font-bold text-lg">{{ currentImageTitle }}</h3>
            <Button variant="ghost" size="icon" @click="closeImageModal">
               <i class="fas fa-times text-slate-500"></i>
            </Button>
          </div>
          <div class="flex-1 p-4 bg-slate-900 flex items-center justify-center overflow-auto">
             <img :src="currentImageUrl" class="max-w-full max-h-[70vh] rounded shadow-lg object-contain" />
          </div>
          <div class="p-4 border-t flex justify-end gap-2">
            <Button variant="outline" @click="closeImageModal">Close</Button>
             <Button as="a" :href="currentImageUrl" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white">
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
          <AlertDialogCancel @click="alertDialog.open = false" :disabled="alertDialog.loading">Cancel</AlertDialogCancel>
          <AlertDialogAction @click.prevent="handleAlertDialogAction" :disabled="alertDialog.loading" class="bg-blue-600 text-white hover:bg-blue-700">
             <i v-if="alertDialog.loading" class="fas fa-spinner fa-spin mr-2"></i>
             {{ alertDialog.loading ? 'Processing...' : 'Continue' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script>
import api from '@/utils/axios'; 
import { toast } from 'vue-sonner';

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
      tabs: [
        { label: 'All Users', value: 'all', icon: 'fas fa-users' },
        { label: 'Admins', value: 'admin', icon: 'fas fa-user-shield' },
        { label: 'Distributors', value: 'distributor', icon: 'fas fa-truck' },
        { label: 'Suppliers', value: 'supplier', icon: 'fas fa-boxes' },
        { label: 'Service Providers', value: 'service_provider', icon: 'fas fa-tools' },
        { label: 'Clients', value: 'client', icon: 'fas fa-user' }
      ],
      
      activeTab: 'all',
      searchQuery: '',
      statusFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      showAddUserModal: false,
      showViewModal: false,
      showImageModalFlag: false,
      showPassword: false,
      showConfirmPassword: false,
      
      showRejectModal: false,
      rejectReason: '',
      userToReject: null,
      processingRejection: false,
      
      loading: false,
      loadingRequirements: false,
      addingUser: false,
      
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
      
      // Wizard Data
      addUserStep: 1,
      validationErrors: {},
      addUserSteps: [
        { title: 'Select Role', short: 'Role' },
        { title: 'Personal Details', short: 'Personal' },
        { title: 'Security', short: 'Security' },
        { title: 'Review', short: 'Review' }
      ],
      availableRoles: [
        { value: 'admin', label: 'Administrator', description: 'System moderation & control', icon: 'fas fa-user-shield', colorClass: 'bg-red-500' },
        { value: 'distributor', label: 'Distributor', description: 'Sell & distribute products', icon: 'fas fa-truck', colorClass: 'bg-purple-500' },
        { value: 'supplier', label: 'Supplier', description: 'Supply raw materials', icon: 'fas fa-boxes', colorClass: 'bg-orange-500' },
        { value: 'service_provider', label: 'Service Provider', description: 'Offer painting services', icon: 'fas fa-tools', colorClass: 'bg-emerald-500' },
        { value: 'client', label: 'Client', description: 'Purchase paints & track orders', icon: 'fas fa-user', colorClass: 'bg-amber-500' }
      ],
      
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
      viewingUser: {},
      userRequirements: null,
      currentImageUrl: '',
      currentImageTitle: '',
      debounceTimer: null,
      
      alertDialog: {
        open: false,
        title: '',
        description: '',
        action: null,
        loading: false
      }
    }
  },
  computed: {
    passwordStrength() {
      if (!this.newUser.password) return '';
      const score = this.passwordStrengthScore;
      if (score < 40) return 'Weak';
      if (score < 70) return 'Fair';
      if (score < 90) return 'Good';
      return 'Strong';
    },
    passwordStrengthScore() {
      if (!this.newUser.password) return 0;
      let score = 0;
      const p = this.newUser.password;
      if (p.length >= 8) score += 20;
      if (p.length >= 12) score += 10;
      if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score += 20;
      if (/\d/.test(p)) score += 20;
      if (/[!@#$%^&*(),.?":{}|<>]/.test(p)) score += 30;
      return Math.min(100, score);
    },
    passwordStrengthClass() {
      const s = this.passwordStrength;
      if (s === 'Weak') return 'bg-red-500';
      if (s === 'Fair') return 'bg-amber-500';
      if (s === 'Good') return 'bg-blue-500';
      return 'bg-emerald-500';
    },
    passwordStrengthTextClass() {
      const s = this.passwordStrength;
      if (s === 'Weak') return 'text-red-500';
      if (s === 'Fair') return 'text-amber-600';
      if (s === 'Good') return 'text-blue-600';
      return 'text-emerald-600';
    },
    passwordRequirements() {
      const p = this.newUser.password || '';
      return [
        { text: '8+ chars', met: p.length >= 8 },
        { text: 'Upper', met: /[A-Z]/.test(p) },
        { text: 'Lower', met: /[a-z]/.test(p) },
        { text: 'Number', met: /\d/.test(p) },
        { text: 'Special', met: /[!@#$%^&*(),.?":{}|<>]/.test(p) }
      ];
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
    confirmAction(title, description, action) {
      this.alertDialog = {
        open: true,
        title,
        description,
        action,
        loading: false
      };
    },
    
    async handleAlertDialogAction() {
      if (this.alertDialog.action) {
        this.alertDialog.loading = true;
        try {
          await this.alertDialog.action();
        } finally {
          this.alertDialog.loading = false;
        }
      }
      this.alertDialog.open = false;
    },

    async fetchUsers() {
      this.loading = true;
      try {
        const params = {
          page: this.currentPage,
          per_page: this.itemsPerPage,
          status: this.statusFilter !== 'all' ? this.statusFilter : undefined,
          search: this.searchQuery || undefined,
          role: this.activeTab !== 'all' ? this.activeTab : undefined
        };
        const response = await api.get('/admin/users', { params });
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
    
    async fetchStatistics() {
      try {
        try {
          const response = await api.get('/admin/users/statistics');
          if (response.data.success) {
            this.statistics = response.data.statistics;
            return;
          }
        } catch (apiError) {}
        this.calculateStatisticsFromUsers();
      } catch (error) {
        this.calculateStatisticsFromUsers();
      }
    },

    calculateStatisticsFromUsers() {
      const stats = {
        total: this.users.length,
        admin: this.users.filter(u => u.role === 'admin').length,
        distributor: this.users.filter(u => u.role === 'distributor').length,
        service_provider: this.users.filter(u => u.role === 'service_provider').length,
        client: this.users.filter(u => u.role === 'client').length,
        supplier: this.users.filter(u => u.role === 'supplier').length,
        active: this.users.filter(u => u.status === 'active').length,
        inactive: this.users.filter(u => u.status === 'inactive').length,
        pending: this.users.filter(u => u.status === 'pending').length,
      };
      this.statistics = stats;
    },
    
    async fetchUserRequirements(userId) {
      this.loadingRequirements = true;
      try {
        const storageUrl = 'http://localhost:8000';
        this.userRequirements = null;
        const response = await api.get(`/admin/users/${userId}`);
        
        if (response.data.success) {
          const user = response.data.user;
          const reqs = response.data.requirements;
          
          if (reqs && reqs.supplier) {
             this.userRequirements = {
                supplier: {
                    ...reqs.supplier,
                    valid_id_photo_url: reqs.supplier.valid_id_photo ? `${storageUrl}/storage/${reqs.supplier.valid_id_photo}` : null,
                    dti_certificate_photo_url: reqs.supplier.dti_certificate_photo ? `${storageUrl}/storage/${reqs.supplier.dti_certificate_photo}` : null,
                    mayor_permit_photo_url: reqs.supplier.mayor_permit_photo ? `${storageUrl}/storage/${reqs.supplier.mayor_permit_photo}` : null,
                    barangay_clearance_photo_url: reqs.supplier.barangay_clearance_photo ? `${storageUrl}/storage/${reqs.supplier.barangay_clearance_photo}` : null,
                    business_registration_photo_url: reqs.supplier.business_registration_photo ? `${storageUrl}/storage/${reqs.supplier.business_registration_photo}` : null
                }
             };
          } else {
              switch (user.role) {
                case 'distributor':
                  if (user.verification_details) {
                    this.userRequirements = {
                      distributor: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? `${storageUrl}/storage/${user.verification_details.valid_id_photo}` : null,
                        dti_certificate_photo_url: user.verification_details.dti_certificate_photo ? `${storageUrl}/storage/${user.verification_details.dti_certificate_photo}` : null,
                        mayor_permit_photo_url: user.verification_details.mayor_permit_photo ? `${storageUrl}/storage/${user.verification_details.mayor_permit_photo}` : null,
                        barangay_clearance_photo_url: user.verification_details.barangay_clearance_photo ? `${storageUrl}/storage/${user.verification_details.barangay_clearance_photo}` : null,
                        business_registration_photo_url: user.verification_details.business_registration_photo ? `${storageUrl}/storage/${user.verification_details.business_registration_photo}` : null
                      }
                    };
                  }
                  break;
                case 'client':
                  if (user.verification_details) {
                    this.userRequirements = {
                      client: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? `${storageUrl}/storage/${user.verification_details.valid_id_photo}` : null
                      }
                    };
                  }
                  break;
                case 'service_provider':
                  if (user.verification_details) {
                    this.userRequirements = {
                      service_provider: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? `${storageUrl}/storage/${user.verification_details.valid_id_photo}` : null,
                        selfie_with_id_photo_url: user.verification_details.selfie_with_id_photo ? `${storageUrl}/storage/${user.verification_details.selfie_with_id_photo}` : null
                      }
                    };
                  }
                  break;
                case 'supplier':
                  if (user.verification_details) {
                    this.userRequirements = {
                      supplier: {
                        ...user.verification_details,
                        valid_id_photo_url: user.verification_details.valid_id_photo ? `${storageUrl}/storage/${user.verification_details.valid_id_photo}` : null,
                        dti_certificate_photo_url: user.verification_details.dti_certificate_photo ? `${storageUrl}/storage/${user.verification_details.dti_certificate_photo}` : null,
                        mayor_permit_photo_url: user.verification_details.mayor_permit_photo ? `${storageUrl}/storage/${user.verification_details.mayor_permit_photo}` : null,
                        barangay_clearance_photo_url: user.verification_details.barangay_clearance_photo ? `${storageUrl}/storage/${user.verification_details.barangay_clearance_photo}` : null,
                        business_registration_photo_url: user.verification_details.business_registration_photo ? `${storageUrl}/storage/${user.verification_details.business_registration_photo}` : null
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
    
    showImageModal(imageUrl, title) {
      this.currentImageUrl = imageUrl;
      this.currentImageTitle = title;
      this.showImageModalFlag = true;
    },
    
    closeImageModal() {
      this.showImageModalFlag = false;
      this.currentImageUrl = '';
      this.currentImageTitle = '';
    },
    
    debouncedFetchUsers() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => {
        this.currentPage = 1;
        this.fetchUsers();
      }, 500);
    },
    
    getPaginationRange() {
      const range = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.pagination.last_page, start + 4);
      for (let i = start; i <= end; i++) range.push(i);
      return range;
    },
    
    goToPage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.currentPage = page;
        this.fetchUsers();
      }
    },
    
    async viewUser(user) {
      this.viewingUser = { ...user };
      this.showViewModal = true;
      await this.fetchUserRequirements(user.id);
    },

    // Wizard Functions
    validateStep2(silent = false) {
      const errors = {};
      let isValid = true;

      if (!this.newUser.first_name?.trim()) { errors.first_name = 'Required'; isValid = false; }
      if (!this.newUser.last_name?.trim()) { errors.last_name = 'Required'; isValid = false; }
      if (!this.newUser.email) { errors.email = 'Required'; isValid = false; }
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.newUser.email)) { errors.email = 'Invalid email format'; isValid = false; }

      if (!this.newUser.phone?.trim()) { errors.phone = 'Required'; isValid = false; }
      else {
        const num = this.newUser.phone.replace(/\D/g, '');
        if (num.length < 11 || !num.startsWith('09')) { errors.phone = 'Invalid PH format (09XX XXX XXXX)'; isValid = false; }
      }

      if (!silent) this.validationErrors = { ...this.validationErrors, first_name: errors.first_name, last_name: errors.last_name, email: errors.email, phone: errors.phone };
      return isValid;
    },
    
    validateStep3(silent = false) {
      const errors = {};
      let isValid = true;

      if (!this.newUser.password) { errors.password = 'Required'; isValid = false; }
      else if (this.passwordStrengthScore < 100 && this.newUser.password.length < 8) { errors.password = 'Password too weak'; isValid = false; }

      if (!this.newUser.password_confirmation) { errors.password_confirmation = 'Required'; isValid = false; }
      else if (this.newUser.password !== this.newUser.password_confirmation) { errors.password_confirmation = 'Passwords must match'; isValid = false; }

      if (!silent) this.validationErrors = { ...this.validationErrors, password: errors.password, password_confirmation: errors.password_confirmation };
      return isValid;
    },
    
    formatPhoneAndValidate() {
      let val = this.newUser.phone.replace(/\D/g, '');
      if (val.length > 11) val = val.substring(0, 11);
      if (val.length > 6) val = val.replace(/(\d{4})(\d{3})(\d+)/, '$1 $2 $3');
      else if (val.length > 4) val = val.replace(/(\d{4})(\d+)/, '$1 $2');
      this.newUser.phone = val;
      this.validateStep2(true);
    },
    
    nextAddUserStep() {
      if (this.addUserStep === 1) {
          if (!this.newUser.role) return toast.error('Please select a role to continue.');
          this.addUserStep++;
      } else if (this.addUserStep === 2) {
          if (this.validateStep2()) this.addUserStep++;
      } else if (this.addUserStep === 3) {
          if (this.validateStep3()) this.addUserStep++;
      }
    },
    
    prevAddUserStep() {
      if (this.addUserStep > 1) this.addUserStep--;
    },
    
    async addNewUser() {
      if (!this.validateStep2(false) || !this.validateStep3(false)) return;
      this.addingUser = true;
      try {
        const payload = { ...this.newUser, phone: this.newUser.phone.replace(/\D/g, '') };
        const response = await api.post('/admin/users', payload);
        
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
    
    approveUser(user) {
      this.confirmAction(
        'Approve User',
        `Are you sure you want to approve ${user.full_name}? This will activate their account and approve all requirements. An email notification will be sent.`,
        async () => {
          try {
            try {
              const approveResponse = await api.post(`/admin/users/${user.id}/approve`, {});
              if (approveResponse.data.success) {
                toast.success('User approved successfully');
                this.closeViewModal();
                this.fetchUsers();
                this.fetchStatistics();
                return;
              }
            } catch (approveError) {}
            
            const userResponse = await api.post(`/admin/users/${user.id}/activate`, {});
            if (userResponse.data.success) {
              toast.success('User activated successfully');
              if (user.role === 'distributor' && user.verification_status === 'pending') {
                try {
                  const distributorReq = await api.get('/distributor/requirements/admin/pending');
                  const pendingReq = distributorReq.data.data?.find(req => req.user_id === user.id);
                  if (pendingReq) {
                    await api.put(`/distributor/requirements/admin/${pendingReq.id}`, { status: 'approved', rejection_reason: null });
                    toast.success('Distributor requirements approved');
                  }
                } catch (error) {}
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
    
    openRejectModal(user) {
      this.userToReject = user;
      this.rejectReason = '';
      this.showRejectModal = true;
    },

    closeRejectModal() {
      this.showRejectModal = false;
      this.userToReject = null;
      this.rejectReason = '';
    },

    async confirmRejectUser() {
      if (!this.rejectReason.trim()) return toast.error('Please provide a rejection reason');
      this.processingRejection = true;
      try {
        try {
          const rejectResponse = await api.post(`/admin/users/${this.userToReject.id}/reject`, { reason: this.rejectReason });
          if (rejectResponse.data.success) {
            toast.success(`User rejected successfully.`);
            this.closeRejectModal();
            this.closeViewModal();
            this.fetchUsers();
            this.fetchStatistics();
            return;
          }
        } catch (rejectError) {}
        
        const userResponse = await api.post(`/admin/users/${this.userToReject.id}/deactivate`, {});
        if (userResponse.data.success) {
          if (this.userToReject.role === 'distributor' && this.userToReject.verification_status === 'pending') {
            try {
              const distributorReq = await api.get('/distributor/requirements/admin/pending');
              const pendingReq = distributorReq.data.data?.find(req => req.user_id === this.userToReject.id);
              if (pendingReq) {
                await api.put(`/distributor/requirements/admin/${pendingReq.id}`, { status: 'rejected', rejection_reason: this.rejectReason });
              }
            } catch (error) {}
          }
          toast.success(`User rejected successfully.`);
          this.closeRejectModal();
          this.closeViewModal();
          this.fetchUsers();
          this.fetchStatistics();
        }
      } catch (error) {
        this.handleError(error);
      } finally {
        this.processingRejection = false;
      }
    },
    
    activateUser(user) {
      this.confirmAction(
        'Activate User',
        `Are you sure you want to activate ${user.full_name}?`,
        async () => {
          try {
            const response = await api.post(`/admin/users/${user.id}/activate`, {});
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
    
    deactivateUser(user) {
      this.confirmAction(
        'Deactivate User',
        `Are you sure you want to deactivate ${user.full_name}?`,
        async () => {
          try {
            const response = await api.post(`/admin/users/${user.id}/deactivate`, {});
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
    
    async reviewRequirements(user) {
      toast.info('Redirecting to requirements review page...');
    },
    
    closeViewModal() {
      this.showViewModal = false;
      this.viewingUser = {};
      this.userRequirements = null;
      this.loadingRequirements = false;
    },
    
    closeAddUserModal() {
      this.showAddUserModal = false;
      this.addUserStep = 1;
      this.validationErrors = {};
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
      this.showConfirmPassword = false;
    },
    
    formatCurrency(amount) {
      if (!amount) return '₱0.00';
      return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    },
    
    getRoleBadgeClass(role) {
      const classes = {
        'admin': 'border-red-200 bg-red-50 text-red-700 hover:bg-red-50',
        'distributor': 'border-green-200 bg-green-50 text-green-700 hover:bg-green-50',
        'service_provider': 'border-purple-200 bg-purple-50 text-purple-700 hover:bg-purple-50',
        'client': 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-50',
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
        'supplier': 'fas fa-boxes'
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
    
    getVerificationDetails(user) {
      if (!user.verification_details) return '';
      const details = user.verification_details;
      if (user.role === 'distributor' || user.role === 'supplier') {
        return details.company_name || '';
      } else if (user.role === 'client' || user.role === 'service_provider') {
        return details.valid_id_type || '';
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