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
                 <p class="text-xl md:text-2xl font-bold text-white">{{ jobs.length }}</p>
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
                 <p class="text-xl md:text-2xl font-bold text-white">
                   {{ jobs.filter(j => j.status === 'pending').length }}
                 </p>
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
            job.status === 'verifying' ? 'border-purple-500/30 text-purple-500 bg-purple-500/10' : 
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
            <span class="text-slate-500 text-xs truncate max-w-[120px]">{{ job.location }}</span>
          </div>

          <div class="flex items-center justify-between">
            <span class="text-slate-400 text-xs">Service:</span>
            <div class="flex items-center">
              <span class="text-slate-300 text-sm mr-2">{{ job.serviceDetails.title }}</span>
              <span class="text-slate-500 text-[10px] uppercase">({{ job.serviceDetails.category }})</span>
            </div>
          </div>
          
          <div class="flex items-center justify-between">
            <span class="text-slate-400 text-xs">Time:</span>
            <span class="text-slate-300 text-sm">{{ job.paintBrand }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
           <Button class="flex-1 bg-slate-800 text-slate-300 hover:text-white border-slate-700 h-9 text-xs" variant="outline" @click="viewJobDetails(job)">
             Details
           </Button>
           <Button v-if="job.status === 'verifying'" class="flex-1 bg-emerald-900/20 text-emerald-400 hover:bg-emerald-900/40 border-emerald-800/30 h-9 text-xs" variant="outline" @click="goToChat(job)">
             Message
           </Button>
        </div>
      </div>
    </div>

    <div class="hidden md:block bg-slate-900 rounded-xl border border-slate-800 overflow-hidden mb-8">
      <Table>
        <TableHeader class="bg-slate-950">
          <TableRow class="border-slate-800 hover:bg-transparent">
            <TableHead class="text-slate-400">Job ID</TableHead>
            <TableHead class="text-slate-400">Client</TableHead>
            <TableHead class="text-slate-400">Service Category</TableHead>
            <TableHead class="text-slate-400">Time / Contact</TableHead>
            <TableHead class="text-slate-400">Status</TableHead>
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
                     <p class="text-gray-400 text-xs truncate max-w-[150px]">{{ job.location }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg bg-indigo-900/20 border border-indigo-800/50 flex items-center justify-center mr-3 text-indigo-400">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                  </div>
                  <div>
                     <p class="text-white font-medium">{{ job.serviceDetails.title }}</p>
                     <p class="text-gray-400 text-xs">{{ job.serviceDetails.category }} | ₱{{ parseFloat(job.serviceDetails.price).toLocaleString() }}</p>
                  </div>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center">
                  <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center mr-3 text-slate-400">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
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
                     job.status === 'verifying' ? 'bg-purple-500' :
                     job.status === 'in-progress' ? 'bg-blue-500' : 
                     job.status === 'completed' ? 'bg-emerald-500' : 'bg-red-500']"></div>
                  <Badge variant="outline" :class="[
                     job.status === 'pending' ? 'border-amber-500/30 text-amber-500 bg-amber-500/10' : 
                     job.status === 'verifying' ? 'border-purple-500/30 text-purple-500 bg-purple-500/10' : 
                     job.status === 'in-progress' ? 'border-blue-500/30 text-blue-500 bg-blue-500/10' : 
                     job.status === 'completed' ? 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10' : 'border-red-500/30 text-red-500 bg-red-500/10'
                  ]">
                     {{ getStatusText(job.status) }}
                  </Badge>
               </div>
            </TableCell>
            <TableCell>
               <div class="flex items-center gap-2">
                  <Button size="icon" variant="ghost" class="h-8 w-8 bg-blue-900/20 text-blue-400 hover:bg-blue-900/40 hover:text-blue-300 border border-blue-800/30" @click="viewJobDetails(job)" title="View Details">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                  </Button>
                  
                  <Button v-if="job.status === 'verifying'" size="icon" variant="ghost" class="h-8 w-8 bg-emerald-900/20 text-emerald-400 hover:bg-emerald-900/40 hover:text-emerald-300 border border-emerald-800/30" @click="goToChat(job)" title="Message Client">
                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                  </Button>
               </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>


    <Dialog v-model:open="showDetailsModal">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[95vw] max-w-[95vw] md:max-w-[650px] max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Job Request Details</DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedJob" class="py-4 space-y-6">
          <div class="grid grid-cols-2 gap-5">
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Client Name</p>
                <p class="text-white font-medium">{{ selectedJob.client }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Status</p>
                <Badge variant="outline" :class="[
                     selectedJob.status === 'pending' ? 'border-amber-500/30 text-amber-500 bg-amber-500/10' : 
                     selectedJob.status === 'verifying' ? 'border-purple-500/30 text-purple-500 bg-purple-500/10' : 
                     selectedJob.status === 'in-progress' ? 'border-blue-500/30 text-blue-500 bg-blue-500/10' : 
                     selectedJob.status === 'completed' ? 'border-emerald-500/30 text-emerald-500 bg-emerald-500/10' : 'border-red-500/30 text-red-500 bg-red-500/10'
                  ]">
                     {{ getStatusText(selectedJob.status) }}
                  </Badge>
             </div>
             
             <div class="col-span-2 border-t border-slate-800 pt-4 mt-2">
                 <h4 class="text-sm font-bold text-indigo-400 uppercase tracking-wider mb-4">Service Offering Details</h4>
                 <div class="grid grid-cols-2 gap-4">
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Service Category</p>
                        <p class="text-white">{{ selectedJob.serviceDetails.category }}</p>
                     </div>
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Service Title</p>
                        <p class="text-white">{{ selectedJob.serviceDetails.title }}</p>
                     </div>
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Service Rate</p>
                        <p class="text-white">₱{{ parseFloat(selectedJob.serviceDetails.price).toLocaleString() }} / {{ selectedJob.serviceDetails.price_type }}</p>
                     </div>
                     <div>
                        <p class="text-slate-400 text-xs uppercase mb-1">Est. Duration</p>
                        <p class="text-white">{{ selectedJob.serviceDetails.duration }}</p>
                     </div>
                 </div>
             </div>

             <div class="col-span-2 border-t border-slate-800 pt-4 mt-2">
                 <h4 class="text-sm font-bold text-blue-400 uppercase tracking-wider mb-4">Client Request Information</h4>
             </div>
             <div class="col-span-2">
                <p class="text-slate-400 text-xs uppercase mb-1">Complete Location</p>
                <p class="text-white bg-slate-950 p-2 rounded-lg border border-slate-800 text-sm">{{ selectedJob.location }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Preferred Date</p>
                <p class="text-white">{{ selectedJob.date }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Time Preference</p>
                <p class="text-white">{{ selectedJob.paintBrand }}</p>
             </div>
             <div>
                <p class="text-slate-400 text-xs uppercase mb-1">Contact Details</p>
                <p class="text-white">{{ selectedJob.paintType }}</p>
             </div>
             <div class="col-span-2 mt-2">
                <p class="text-slate-400 text-xs uppercase mb-1">Client Description / Notes</p>
                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800 text-sm whitespace-pre-wrap leading-relaxed">
                   {{ selectedJob.originalData?.description || 'No additional description provided by the client.' }}
                </div>
             </div>
          </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-800">
           
           <template v-if="selectedJob && selectedJob.status === 'pending'">
             <Button class="bg-red-600/20 text-red-500 hover:bg-red-600/40 hover:text-red-400 border border-red-800/30" @click="promptRejectJob">Reject Request</Button>
             <Button class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 border-0 text-white shadow-lg shadow-emerald-600/20" @click="promptApproveJob">Approve Request</Button>
           </template>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog v-model:open="showConfirmDialog">
      <AlertDialogContent class="bg-slate-900 border-slate-800 text-slate-200">
        <AlertDialogHeader>
          <AlertDialogTitle>Are you sure?</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-400">
            {{ actionType === 'approve' 
               ? 'This will approve the job request and notify the client.' 
               : 'This will reject the job request. This action cannot be undone.' }}
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="showConfirmDialog = false" class="bg-transparent border-slate-700 text-white hover:bg-slate-800 hover:text-white">Cancel</AlertDialogCancel>
          <AlertDialogAction 
             @click="handleConfirmAction" 
             :disabled="isProcessing"
             :class="actionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-red-600 hover:bg-red-700 text-white'">
            <span v-if="isProcessing" class="flex items-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...
            </span>
            <span v-else>Confirm</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue' 
import { useRouter } from 'vue-router'
import { toast } from 'vue-sonner' // Imported Sonner
import api from '@/utils/axios' 
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'

// Imported AlertDialog components
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

const router = useRouter()
const showCreateModal = ref(false)
const showDetailsModal = ref(false)
const selectedJob = ref(null)

// Confirm Dialog States
const showConfirmDialog = ref(false)
const actionType = ref('') // 'approve' or 'reject'
const isProcessing = ref(false)

const activeFilter = ref({ value: 'all', label: 'All Jobs' })

const filters = [
  { value: 'all', label: 'All Jobs' },
  { value: 'pending', label: 'Pending' },
  { value: 'verifying', label: 'Verifying' }, // Updated from verificating
  { value: 'in-progress', label: 'In Progress' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' }
]

const jobs = ref([])

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

const getInitials = (name) => {
  if (!name) return 'UN'
  return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2)
}

const getStatusText = (status) => {
  if (!status) return 'Unknown'
  return status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const fetchJobRequests = async () => {
  try {
    const response = await api.get('/service-provider/job-requests')
    
    if (response.data.success) {
      jobs.value = response.data.data.map(req => ({
        id: `REQ-${req.id}`,
        client: req.client ? `${req.client.first_name} ${req.client.last_name}` : 'Unknown Client',
        location: req.address || 'No location provided',
        
        serviceDetails: {
          title: req.service_offering ? req.service_offering.title : 'General Service',
          category: req.service_offering ? req.service_offering.category : 'Uncategorized',
          price: req.service_offering ? req.service_offering.price : 0,
          price_type: req.service_offering ? req.service_offering.price_type : '',
          duration: req.service_offering ? req.service_offering.duration : 'N/A',
          description: req.service_offering ? req.service_offering.description : ''
        },

        color: { 
          name: req.service_offering ? req.service_offering.title : 'General Service', 
          hex: '#64748b' 
        },
        paintBrand: req.time_preference || 'Flexible Time',
        paintType: req.contact_number || 'N/A',
        status: req.status || 'pending',
        distributor: { 
          name: 'Not Assigned', 
          location: 'Pending' 
        },
        date: req.preferred_date || 'TBD',
        unityLinked: false,
        originalData: req
      }))
    }
  } catch (error) {
    console.error('Error fetching service requests:', error)
    toast.error('Failed to load service requests.')
  }
}

onMounted(() => {
  fetchJobRequests()
})

const createJob = () => {
  if (!newJob.value.clientId) {
    toast.error('Please fill required fields')
    return
  }
  toast.success('Job created successfully!')
  showCreateModal.value = false
}

const closeCreateJobModal = () => { showCreateModal.value = false; newJob.value = { clientId: '', paintBrand: '', colorId: '', distributorId: '', jobType: 'interior', estimatedBudget: '', description: '' } }
const openUnityMixer = () => router.push('/service-provider/color-mixer')

const viewJobDetails = (job) => {
  selectedJob.value = job
  showDetailsModal.value = true
}

// Action Handlers
const promptApproveJob = () => {
  actionType.value = 'approve'
  showConfirmDialog.value = true
}

const promptRejectJob = () => {
  actionType.value = 'reject'
  showConfirmDialog.value = true
}

const handleConfirmAction = async () => {
  if (!selectedJob.value) return
  isProcessing.value = true

  try {
    if (actionType.value === 'approve') {
      const response = await api.post(`/service-provider/job-requests/${selectedJob.value.originalData.id}/approve`)
      
      if(response.data.success) {
        selectedJob.value.status = 'verifying'
        const index = jobs.value.findIndex(j => j.id === selectedJob.value.id)
        if (index !== -1) jobs.value[index].status = 'verifying'
        
        toast.success('Job request approved successfully.')
        showDetailsModal.value = false
      }
    } else if (actionType.value === 'reject') {
      const response = await api.post(`/service-provider/job-requests/${selectedJob.value.originalData.id}/reject`)
      
      if(response.data.success) {
        selectedJob.value.status = 'rejected'
        const index = jobs.value.findIndex(j => j.id === selectedJob.value.id)
        if (index !== -1) jobs.value[index].status = 'rejected'
        
        toast.success('Job request rejected successfully.')
        showDetailsModal.value = false
      }
    }
  } catch (error) {
    console.error(`Error processing ${actionType.value} job:`, error)
    toast.error(error.response?.data?.message || 'An error occurred while processing the request.')
  } finally {
    isProcessing.value = false
    showConfirmDialog.value = false
  }
}

const goToChat = (job) => {
  router.push('/serviceProvider/SPChat')
}

const importFromUnity = () => console.log('import')
const openColorPicker = () => console.log('picker')
</script>