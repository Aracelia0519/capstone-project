<template>
  <div class="min-h-screen text-slate-200 p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Service Jobs & Requests</h1>
          <p class="text-gray-400 text-sm md:text-base">Manage client painting projects and service requests</p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 md:space-x-4 md:gap-0">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" class="w-full sm:w-auto justify-between sm:justify-center bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">
                <span class="flex items-center">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                  Filter: {{ activeFilter.label }}
                </span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent class="bg-slate-900 border-slate-700 text-slate-300 w-[var(--radix-dropdown-menu-trigger-width)] sm:w-auto">
               <DropdownMenuItem v-for="filter in filters" :key="filter.value" @click="activeFilter = filter" class="focus:bg-slate-800 cursor-pointer justify-between">
                  {{ filter.label }}
                  <span v-if="activeFilter.value === filter.value" class="text-emerald-500"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></span>
               </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
          
          <Button class="w-full sm:w-auto bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 border-0 text-white shadow-lg shadow-blue-500/20" @click="showCreateModal = true">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Create New Job
          </Button>
        </div>
      </div>
      
      <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <Card class="bg-blue-900/20 border-blue-800/50">
           <CardContent class="p-3 md:p-4 flex items-center">
              <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
              </div>
              <div>
                 <p class="text-gray-400 text-xs md:text-sm">Total Jobs</p>
                 <p class="text-xl md:text-2xl font-bold text-white">47</p>
              </div>
           </CardContent>
        </Card>
        <Card class="bg-amber-900/20 border-amber-800/50">
           <CardContent class="p-3 md:p-4 flex items-center">
              <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
                 <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
              </div>
              <div>
                 <p class="text-gray-400 text-xs md:text-sm">Pending</p>
                 <p class="text-xl md:text-2xl font-bold text-white">8</p>
              </div>
           </CardContent>
        </Card>
      </div>
    </div>

    <div class="md:hidden space-y-4 mb-8">
      <div v-for="job in filteredJobs" :key="job.id" class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
        <div class="flex justify-between items-start mb-3">
          <div class="flex items-center">
            <div class="w-8 h-8 rounded-lg bg-blue-900/20 border border-blue-800/50 flex items-center justify-center mr-2 text-blue-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <div>
              <p class="text-white font-medium text-sm">#{{ job.id }}</p>
              <p class="text-gray-400 text-xs">{{ job.date }}</p>
            </div>
          </div>
          <Badge variant="outline" :class="[
            'text-xs px-2 py-0.5',
            job.status === 'pending' ? 'border-amber-500/30 text-amber-500 bg-amber-500/10' : 
            job.status === 'in-progress' ? 'border-blue-500/30 text-blue-500 bg-blue-500/10' : 
            job.status === 'completed' ? 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10' : 'border-red-500/30 text-red-500 bg-red-500/10'
          ]">
            {{ getStatusText(job.status) }}
          </Badge>
        </div>
        
        <div class="space-y-3 mb-4">
          <div class="flex items-center justify-between border-b border-slate-800/50 pb-2">
            <div class="flex items-center">
              <Avatar class="h-6 w-6 mr-2 bg-gradient-to-br from-blue-500 to-purple-500">
                  <AvatarFallback class="bg-transparent text-white text-[10px] font-bold">{{ getInitials(job.client) }}</AvatarFallback>
              </Avatar>
              <span class="text-slate-300 text-sm">{{ job.client }}</span>
            </div>
            <span class="text-slate-500 text-xs">{{ job.location }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-slate-400 text-xs">Color:</span>
            <div class="flex items-center">
              <span class="text-slate-300 text-sm mr-2">{{ job.color.name }}</span>
              <div class="w-4 h-4 rounded border border-slate-700" :style="{ backgroundColor: job.color.hex }"></div>
            </div>
          </div>
          
          <div class="flex items-center justify-between">
            <span class="text-slate-400 text-xs">Brand:</span>
            <span class="text-slate-300 text-sm">{{ job.paintBrand }} ({{ job.paintType }})</span>
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
           <Button class="flex-1 bg-slate-800 text-slate-300 hover:text-white border-slate-700 h-9 text-xs" variant="outline" @click="viewJobDetails(job)">
             Details
           </Button>
           <Button size="icon" variant="ghost" class="h-9 w-9 bg-purple-900/20 text-purple-400 border border-purple-800/30" @click="linkToUnity(job)">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
           </Button>
           <DropdownMenu>
              <DropdownMenuTrigger as-child>
                 <Button size="icon" variant="ghost" class="h-9 w-9 bg-slate-800 text-slate-400 border border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                 </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="bg-slate-900 border-slate-700 text-slate-300">
                 <DropdownMenuItem @click="editJob(job)">Edit Job</DropdownMenuItem>
                 <DropdownMenuItem @click="assignDistributor(job)">Change Distributor</DropdownMenuItem>
                 <DropdownMenuItem @click="generateInvoice(job)">Generate Invoice</DropdownMenuItem>
                 <DropdownMenuItem @click="updateJobStatus(job)">Next Status</DropdownMenuItem>
                 <DropdownMenuItem @click="deleteJob(job)" class="text-red-400">Delete Job</DropdownMenuItem>
              </DropdownMenuContent>
           </DropdownMenu>
        </div>
      </div>
    </div>

    <div class="hidden md:block bg-slate-900 rounded-xl border border-slate-800 overflow-hidden mb-8">
      <Table>
        <TableHeader class="bg-slate-950">
          <TableRow class="border-slate-800 hover:bg-transparent">
            <TableHead class="text-slate-400">Job ID</TableHead>
            <TableHead class="text-slate-400">Client</TableHead>
            <TableHead class="text-slate-400">Paint Color</TableHead>
            <TableHead class="text-slate-400">Brand</TableHead>
            <TableHead class="text-slate-400">Status</TableHead>
            <TableHead class="text-slate-400">Distributor</TableHead>
            <TableHead class="text-slate-400">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="job in filteredJobs" :key="job.id" class="border-slate-800 hover:bg-slate-800/50">
            <TableCell>
               <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-blue-900/20 border border-blue-800/50 flex items-center justify-center mr-3 text-blue-400">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                  </div>
                  <div>
                     <p class="text-white font-medium">#{{ job.id }}</p>
                     <p class="text-gray-400 text-xs">{{ job.date }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <Avatar class="bg-gradient-to-br from-blue-500 to-purple-500 border-0 h-9 w-9 mr-3">
                     <AvatarFallback class="bg-transparent text-white text-xs font-bold">{{ getInitials(job.client) }}</AvatarFallback>
                  </Avatar>
                  <div>
                     <p class="text-white font-medium">{{ job.client }}</p>
                     <p class="text-gray-400 text-xs">{{ job.location }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg mr-3 border border-slate-700" :style="{ backgroundColor: job.color.hex }"></div>
                  <div>
                     <p class="text-white font-medium">{{ job.color.name }}</p>
                     <p class="text-gray-400 text-xs">{{ job.color.hex }}</p>
                     <Badge v-if="job.unityLinked" variant="secondary" class="mt-1 bg-gradient-to-r from-violet-600 to-pink-600 text-white border-0 text-[10px] h-4 px-1">Unity</Badge>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center mr-3 text-slate-400">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                  </div>
                  <div>
                     <p class="text-white font-medium">{{ job.paintBrand }}</p>
                     <p class="text-gray-400 text-xs">{{ job.paintType }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div :class="['w-2 h-2 rounded-full mr-2', 
                     job.status === 'pending' ? 'bg-amber-500' : 
                     job.status === 'in-progress' ? 'bg-blue-500' : 
                     job.status === 'completed' ? 'bg-emerald-500' : 'bg-red-500']"></div>
                  <Badge variant="outline" :class="[
                     job.status === 'pending' ? 'border-amber-500/30 text-amber-500 bg-amber-500/10' : 
                     job.status === 'in-progress' ? 'border-blue-500/30 text-blue-500 bg-blue-500/10' : 
                     job.status === 'completed' ? 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10' : 'border-red-500/30 text-red-500 bg-red-500/10'
                  ]">
                     {{ getStatusText(job.status) }}
                  </Badge>
                  <button @click="updateJobStatus(job)" class="ml-2 text-slate-500 hover:text-white transition-colors">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                  </button>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-500 to-emerald-400 flex items-center justify-center mr-3 text-white">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                  </div>
                  <div>
                     <p class="text-white font-medium">{{ job.distributor.name }}</p>
                     <p class="text-gray-400 text-xs">{{ job.distributor.location }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center gap-2">
                  <Button size="icon" variant="ghost" class="h-8 w-8 bg-blue-900/20 text-blue-400 hover:bg-blue-900/40 hover:text-blue-300 border border-blue-800/30" @click="viewJobDetails(job)">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </Button>
                  <Button size="icon" variant="ghost" class="h-8 w-8 bg-purple-900/20 text-purple-400 hover:bg-purple-900/40 hover:text-purple-300 border border-purple-800/30" @click="linkToUnity(job)">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                  </Button>
                  <DropdownMenu>
                     <DropdownMenuTrigger as-child>
                        <Button size="icon" variant="ghost" class="h-8 w-8 bg-slate-800 text-slate-400 hover:text-white border border-slate-700">
                           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                        </Button>
                     </DropdownMenuTrigger>
                     <DropdownMenuContent class="bg-slate-900 border-slate-700 text-slate-300">
                        <DropdownMenuItem @click="editJob(job)" class="cursor-pointer">Edit Job</DropdownMenuItem>
                        <DropdownMenuItem @click="assignDistributor(job)" class="cursor-pointer">Change Distributor</DropdownMenuItem>
                        <DropdownMenuItem @click="generateInvoice(job)" class="cursor-pointer">Generate Invoice</DropdownMenuItem>
                        <DropdownMenuItem @click="deleteJob(job)" class="text-red-400 focus:text-red-300 cursor-pointer">Delete Job</DropdownMenuItem>
                     </DropdownMenuContent>
                  </DropdownMenu>
               </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-700/50 rounded-xl p-4 md:p-6">
       <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div class="flex flex-col sm:flex-row items-start">
             <div class="mr-4 mb-3 sm:mb-0">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center text-white shrink-0">
                   <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                </div>
             </div>
             <div>
                <h3 class="text-xl font-bold text-white mb-2">Unity Integration</h3>
                <p class="text-gray-300 mb-3 text-sm md:text-base">Link color mixing results from Unity to service jobs for accurate paint matching</p>
                <div class="flex flex-wrap items-center gap-3 md:space-x-4 md:gap-0">
                   <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div><span class="text-gray-300 text-sm">8 jobs linked to Unity</span></div>
                   <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div><span class="text-gray-300 text-sm">12 colors available</span></div>
                </div>
             </div>
          </div>
          <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 w-full sm:w-auto">
             <Button class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 border-0 text-white shadow-lg shadow-purple-500/20" @click="openUnityMixer">
                Open Unity Mixer
             </Button>
             <Button variant="secondary" class="w-full sm:w-auto bg-slate-700 text-white hover:bg-slate-600" @click="importFromUnity">
                Import Colors
             </Button>
          </div>
       </div>
    </div>

    <Dialog v-model:open="showCreateModal">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[95vw] max-w-[95vw] md:max-w-[700px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Create New Service Job</DialogTitle>
        </DialogHeader>
        <div class="py-4">
          <p class="text-slate-400 mb-6">Fill in the details to create a new service job for your client</p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
             <div class="space-y-2">
                <Label>Select Client</Label>
                <Select v-model="newJob.clientId">
                   <SelectTrigger class="bg-slate-950 border-slate-800"><SelectValue placeholder="Choose a client" /></SelectTrigger>
                   <SelectContent class="bg-slate-900 border-slate-800 text-slate-200">
                      <SelectItem v-for="client in clients" :key="client.id" :value="String(client.id)">{{ client.name }}</SelectItem>
                   </SelectContent>
                </Select>
             </div>
             <div class="space-y-2">
                <Label>Paint Brand</Label>
                <Select v-model="newJob.paintBrand">
                   <SelectTrigger class="bg-slate-950 border-slate-800"><SelectValue placeholder="Select brand" /></SelectTrigger>
                   <SelectContent class="bg-slate-900 border-slate-800 text-slate-200">
                      <SelectItem value="Boysen">Boysen</SelectItem>
                      <SelectItem value="Davies">Davies</SelectItem>
                      <SelectItem value="Rainbow">Rainbow</SelectItem>
                   </SelectContent>
                </Select>
             </div>
             <div class="space-y-2">
                <Label>Paint Color</Label>
                <div class="flex gap-2">
                   <Select v-model="newJob.colorId">
                      <SelectTrigger class="bg-slate-950 border-slate-800"><SelectValue placeholder="Select color" /></SelectTrigger>
                      <SelectContent class="bg-slate-900 border-slate-800 text-slate-200">
                         <SelectItem v-for="color in colors" :key="color.id" :value="String(color.id)">{{ color.name }}</SelectItem>
                      </SelectContent>
                   </Select>
                   <Button size="icon" class="bg-gradient-to-r from-purple-600 to-pink-600 border-0 shrink-0" @click="openColorPicker"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg></Button>
                </div>
             </div>
             <div class="space-y-2">
                <Label>Estimated Budget (₱)</Label>
                <Input type="number" v-model="newJob.estimatedBudget" class="bg-slate-950 border-slate-800" />
             </div>
             <div class="md:col-span-2 space-y-2">
                <Label>Project Description</Label>
                <Textarea v-model="newJob.description" class="bg-slate-950 border-slate-800" />
             </div>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
           <Button variant="outline" class="border-slate-700 hover:bg-slate-800" @click="closeCreateJobModal">Cancel</Button>
           <Button class="bg-gradient-to-r from-blue-600 to-violet-600 border-0" @click="createJob">Create Job</Button>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'

const router = useRouter()
const showCreateModal = ref(false)
const activeFilter = ref({ value: 'all', label: 'All Jobs' })

const filters = [
  { value: 'all', label: 'All Jobs' },
  { value: 'pending', label: 'Pending' },
  { value: 'in-progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' }
]

const jobs = ref([
  { id: 'J-2024-001', client: 'JUJI', location: 'Bacoor, Cavite', color: { name: 'Ocean Breeze', hex: '#4CC9F0' }, paintBrand: 'Boysen', paintType: 'Water-based', status: 'in-progress', distributor: { name: 'Cavite Paint Supply', location: 'Imus' }, date: 'Feb 15, 2024', unityLinked: true },
  // ... rest of job data
])

const clients = ref([
  { id: 1, name: 'Juan Dela Cruz', location: 'Bacoor, Cavite' },
  { id: 2, name: 'Maria Gonzales', location: 'Imus, Cavite' },
])

const colors = ref([
  { id: 1, name: 'Ocean Breeze', hex: '#4CC9F0' },
  { id: 2, name: 'Sage Green', hex: '#8A9A5B' },
])

const newJob = ref({ clientId: '', paintBrand: '', colorId: '', distributorId: '', jobType: 'interior', estimatedBudget: '', description: '' })

const filteredJobs = computed(() => activeFilter.value.value === 'all' ? jobs.value : jobs.value.filter(job => job.status === activeFilter.value.value))

const getInitials = (name) => name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2)
const getStatusText = (status) => status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())

const createJob = () => {
  if (!newJob.value.clientId) return alert('Please fill required fields')
  // ... Logic similar to original ...
  alert('Job created!')
  showCreateModal.value = false
}

const updateJobStatus = (job) => {
   // Toggle logic
   const states = ['pending', 'in-progress', 'completed', 'cancelled']
   const idx = states.indexOf(job.status)
   job.status = states[(idx + 1) % states.length]
}

const closeCreateJobModal = () => { showCreateModal.value = false; newJob.value = { clientId: '', paintBrand: '', colorId: '', distributorId: '', jobType: 'interior', estimatedBudget: '', description: '' } }
const openUnityMixer = () => router.push('/service-provider/color-mixer')
// ... other placeholder functions (linkToUnity, viewJobDetails, etc) ...
const linkToUnity = () => console.log('link')
const viewJobDetails = () => console.log('view')
const editJob = () => console.log('edit')
const assignDistributor = () => console.log('assign')
const generateInvoice = () => console.log('invoice')
const deleteJob = () => console.log('delete')
const importFromUnity = () => console.log('import')
const openColorPicker = () => console.log('picker')
</script>