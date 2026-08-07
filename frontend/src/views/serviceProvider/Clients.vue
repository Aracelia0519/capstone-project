<template>
  <div class="min-h-screen text-slate-200">
    <div class="p-6 md:p-8 border-b border-slate-800/50">
      <div class="flex flex-col md:flex-row justify-between items-start gap-6 mb-6">
        <div class="flex-1 min-w-[300px]">
          <h1 class="text-3xl font-bold text-slate-100 flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <span>Client Management</span>
            
          </h1>
          <p class="text-slate-400 text-sm">Manage client relationships, reports, and transacted service history</p>
        </div>
        <div class="flex gap-4 items-center flex-wrap w-full md:w-auto">
          <div class="relative w-full md:w-[250px]">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <Input v-model="searchQuery" placeholder="Search clients..." class="pl-10 bg-slate-900 border-slate-800 text-white focus:border-blue-500" />
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        <Card class="bg-slate-900/50 border-slate-800 hover:-translate-y-1 transition-transform">
          <CardContent class="p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
               <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0a6 6 0 01-9 5.197" /></svg>
            </div>
            <div>
               <div class="text-2xl font-bold text-slate-100">{{ filteredClients.length }}</div>
               <div class="text-xs text-slate-400">Total Clients</div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Skeletons Loader -->
    <div v-if="loading" class="m-6 md:m-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="i in 6" :key="i" class="bg-slate-950 border-slate-800 p-6">
        <div class="animate-pulse">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <Skeleton class="w-10 h-10 rounded-full bg-slate-800" />
              <div class="space-y-2">
                <Skeleton class="h-4 w-32 bg-slate-800 rounded" />
                <Skeleton class="h-3 w-24 bg-slate-800 rounded" />
              </div>
            </div>
          </div>
          <div class="space-y-3 mb-6">
            <Skeleton class="h-10 bg-slate-800 rounded-lg" />
            <Skeleton class="h-10 bg-slate-800 rounded-lg" />
          </div>
        </div>
      </Card>
    </div>

    <div v-else class="m-6 md:m-8 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
      <div class="p-5 border-b border-slate-800 flex justify-between items-center">
        <div class="font-semibold text-lg flex items-center text-slate-100">
           <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
           Client Directory
        </div>
        <div class="flex gap-3">
          <div class="flex bg-slate-950 rounded-md p-1 border border-slate-800">
            <button @click="viewMode = 'grid'" :class="['p-1.5 rounded transition-all', viewMode === 'grid' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-300']">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
            </button>
            <button @click="viewMode = 'list'" :class="['p-1.5 rounded transition-all', viewMode === 'list' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-300']">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="viewMode === 'list'" class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-slate-950/80">
            <TableRow class="border-slate-800 hover:bg-transparent">
              <TableHead class="text-slate-200 font-semibold">Client</TableHead>
              <TableHead class="text-slate-200 font-semibold">Contact</TableHead>
              <TableHead class="text-slate-200 font-semibold">Location</TableHead>
              <TableHead class="text-slate-200 font-semibold">Transacted Services</TableHead>
              <TableHead class="text-slate-200 font-semibold">Status</TableHead>
              <TableHead class="text-right text-slate-200 font-semibold">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="client in filteredClients" :key="client.id" class="border-slate-800 hover:bg-slate-800/30">
              <TableCell>
                <div class="flex items-center gap-3">
                   <div class="relative">
                      <Avatar class="bg-gradient-to-br from-blue-500 to-violet-500 border-0 h-9 w-9">
                        <AvatarFallback class="bg-transparent text-white text-xs font-bold">{{ getInitials(client.name) }}</AvatarFallback>
                      </Avatar>
                      <span :class="['absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-slate-900', client.status === 'active' ? 'bg-emerald-500' : 'bg-slate-500']"></span>
                   </div>
                   <div>
                      <div class="font-medium text-slate-200">{{ client.name }}</div>
                      <div class="text-xs text-slate-500">{{ client.email }}</div>
                   </div>
                </div>
              </TableCell>
              <TableCell class="text-slate-400">{{ client.phone }}</TableCell>
              <TableCell class="text-slate-400">{{ client.address }}</TableCell>
              <TableCell>
                <Badge variant="outline" class="border-blue-500/30 bg-blue-500/10 text-blue-400 cursor-pointer" @click="viewClientHistory(client)" title="Click to view history">
                   {{ client.jobCount }} Services
                </Badge>
              </TableCell>
              <TableCell>
                 <Badge :class="[client.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-slate-500/10 text-slate-400 hover:bg-slate-500/20']" variant="secondary">
                    {{ client.status === 'active' ? 'Active' : 'Inactive' }}
                 </Badge>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="icon" @click="viewClientHistory(client)" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800" title="View Services"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></Button>
                  
                  <!-- REPORT BUTTON -->
                  <Button variant="ghost" size="icon" @click="openReportModal(client)" class="h-8 w-8 text-red-400 hover:text-red-300 hover:bg-red-900/20" title="Report Client">
                     <Flag class="w-4 h-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="viewMode === 'grid'" class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <Card v-for="client in filteredClients" :key="client.id" class="bg-slate-950 border-slate-800 hover:border-slate-700 hover:shadow-lg transition-all">
          <CardContent class="p-0">
             <div class="p-5 border-b border-slate-800">
                <div class="flex justify-between items-start mb-4">
                   <div class="relative">
                      <Avatar class="bg-gradient-to-br from-blue-500 to-violet-500 border-0 h-10 w-10">
                        <AvatarFallback class="bg-transparent text-white text-sm font-bold">{{ getInitials(client.name) }}</AvatarFallback>
                      </Avatar>
                      <span :class="['absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-slate-950', client.status === 'active' ? 'bg-emerald-500' : 'bg-slate-500']"></span>
                   </div>
                </div>
                <h3 class="font-medium text-slate-200">{{ client.name }}</h3>
                <p class="text-sm text-slate-500 mb-4">{{ client.email }}</p>
                <div class="space-y-2 text-sm text-slate-400">
                   <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> {{ client.phone }}</div>
                   <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg> {{ client.address }}</div>
                </div>
             </div>
             
             <!-- Transacted Services Preview -->
             <div class="p-4 border-b border-slate-800" v-if="client.recentProjects && client.recentProjects.length > 0">
               <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-bold">Services Transacted</p>
               <div class="flex flex-wrap gap-1.5">
                  <span v-for="(project, i) in client.recentProjects.slice(0, 3)" :key="i" class="text-xs bg-blue-500/10 text-blue-400 px-2 py-1 rounded border border-blue-500/20">
                     {{ project.name }}
                  </span>
                  <span v-if="client.recentProjects.length > 3" class="text-xs bg-slate-800 text-slate-400 px-2 py-1 rounded">
                    +{{ client.recentProjects.length - 3 }} more
                  </span>
               </div>
             </div>

             <div class="p-4 flex justify-between items-center bg-slate-900/50">
                <div class="flex flex-col">
                   <span class="text-lg font-bold text-slate-200">{{ client.jobCount }}</span>
                   <span class="text-xs text-slate-500">Jobs</span>
                </div>
                <div class="flex gap-2">
                   <!-- REPORT BUTTON FOR GRID -->
                   <Button variant="secondary" size="sm" class="bg-red-900/20 text-red-500 hover:bg-red-900/40 hover:text-red-400" @click="openReportModal(client)">
                      <Flag class="w-3 h-3 mr-1" /> Report
                   </Button>

                   <Button variant="secondary" size="sm" class="bg-slate-800 text-slate-300 hover:text-white" @click="viewClientHistory(client)">History</Button>
                </div>
             </div>
          </CardContent>
        </Card>
      </div>

      <div v-if="filteredClients.length === 0" class="py-16 text-center">
         <div class="w-16 h-16 bg-slate-800/50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
         </div>
         <h3 class="text-lg font-medium text-slate-200 mb-2">No Clients Found</h3>
         <p class="text-slate-400 max-w-sm mx-auto">You haven't interacted with any clients yet, or no client matches your search criteria.</p>
      </div>
    </div>

    <!-- CLIENT HISTORY MODAL -->
    <Dialog v-model:open="showHistoryModal">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 sm:max-w-[700px]">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2">
             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
             Service History - {{ selectedClient?.name }}
          </DialogTitle>
        </DialogHeader>
        <div class="py-6 px-4">
          <div class="relative pl-8 space-y-8 before:absolute before:left-3 before:top-0 before:h-full before:w-0.5 before:bg-slate-800">
             <div v-for="job in clientHistory" :key="job.id" class="relative">
                <span class="absolute -left-[29px] top-1 h-4 w-4 rounded-full border-2 border-slate-900 bg-blue-500"></span>
                <Card class="bg-slate-950 border-slate-800">
                   <CardContent class="p-4">
                      <div class="flex justify-between items-start mb-2">
                         <h4 class="font-medium text-slate-200">{{ job.service }}</h4>
                         <span class="text-xs text-slate-500">{{ formatDate(job.date) }}</span>
                      </div>
                      <div class="flex gap-4 mb-3 text-sm">
                         <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded border border-slate-700" :style="{ backgroundColor: job.color }"></div>
                            <span class="text-slate-300">{{ job.colorName }}</span>
                         </div>
                         <Badge variant="outline" :class="job.status === 'completed' ? 'border-emerald-500/30 text-emerald-400' : 'border-blue-500/30 text-blue-400'">
                            {{ job.status }}
                         </Badge>
                      </div>
                      <p v-if="job.notes" class="text-sm text-slate-400 italic border-t border-slate-800 pt-2 mt-2">{{ job.notes }}</p>
                   </CardContent>
                </Card>
             </div>
             <div v-if="clientHistory.length === 0" class="text-center py-8 text-slate-500">
                No formal service history available.
             </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <!-- REPORT MODAL DIALOG -->
    <Dialog :open="isReportModalOpen" @update:open="isReportModalOpen = $event">
      <DialogContent class="sm:max-w-md bg-slate-900 border-slate-800 text-white rounded-2xl shadow-2xl">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2 text-xl font-bold text-red-500">
            <Flag class="w-5 h-5" /> Report Client
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Submit a formal report regarding <span class="text-white font-bold">{{ selectedClientForReport?.name }}</span>. This will be reviewed by an administrator.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submitReport" class="space-y-4 mt-4">
          
          <div class="space-y-2">
            <Label class="text-gray-300">Reason for Report</Label>
            <Select v-model="reportForm.reason">
              <SelectTrigger class="w-full bg-slate-800 border-slate-700 text-white focus:ring-red-500">
                <SelectValue placeholder="Select a reason..." />
              </SelectTrigger>
              <SelectContent class="bg-slate-800 border-slate-700 text-white">
                <SelectItem value="Scam / Fraud">Scam / Fraud</SelectItem>
                <SelectItem value="Inappropriate Behavior">Inappropriate Behavior</SelectItem>
                <SelectItem value="Unpaid Services">Unpaid Services</SelectItem>
                <SelectItem value="Unresponsive">Unresponsive / Ghosting</SelectItem>
                <SelectItem value="Other">Other</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-gray-300">Detailed Description</Label>
            <Textarea 
              v-model="reportForm.description"
              placeholder="Please provide specific details about what happened..."
              class="bg-slate-800 border-slate-700 text-white focus-visible:ring-red-500 placeholder:text-gray-500 min-h-[100px] resize-none"
              required
            ></Textarea>
          </div>

          <div class="space-y-2">
            <Label class="text-gray-300">Date of Incident</Label>
            <Input 
              type="date" 
              v-model="reportForm.incident_date"
              class="bg-slate-800 border-slate-700 text-white focus-visible:ring-red-500"
              required
            />
          </div>

          <div class="space-y-2">
            <Label class="text-gray-300">Evidence <span class="text-gray-500 text-xs">(Optional, max 5MB)</span></Label>
            <Input 
              type="file" 
              accept=".jpg,.jpeg,.png,.pdf,.mp4"
              @change="handleReportEvidence"
              class="bg-slate-800 border-slate-700 text-gray-300 file:bg-slate-700 file:text-white file:border-0 file:mr-4 file:py-1 file:px-3 file:rounded cursor-pointer focus-visible:ring-red-500"
            />
          </div>

          <p class="text-xs text-gray-500 text-center">Note: You can submit up to 3 reports per day.</p>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-800 mt-6">
            <Button type="button" variant="outline" @click="isReportModalOpen = false" class="bg-slate-800 border-slate-700 text-white hover:bg-slate-700">Cancel</Button>
            <Button type="submit" :disabled="isSubmittingReport" class="bg-red-600 hover:bg-red-700 text-white">
              <span v-if="isSubmittingReport" class="flex items-center gap-2">
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Submitting...
              </span>
              <span v-else>Submit Report</span>
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Flag } from 'lucide-vue-next'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'

// Data populated via API
const clients = ref([])
const loading = ref(true)

const searchQuery = ref('')
const viewMode = ref('list')
const showHistoryModal = ref(false)
const selectedClient = ref(null)

// Report State Variables
const isReportModalOpen = ref(false)
const selectedClientForReport = ref(null)
const isSubmittingReport = ref(false)
const reportForm = ref({ reason: '', description: '', incident_date: '' })
const reportEvidenceFile = ref(null)

// History dynamically populated
const clientHistory = ref([])

// Fetch Actual API Data
const fetchClients = async () => {
  loading.value = true
  try {
    const res = await api.get('/service-provider/interacted-clients')
    if (res.data.success) {
      clients.value = res.data.data
    }
  } catch (error) {
    console.error("Failed to load clients:", error)
    toast.error('Error', { description: 'Unable to load interacted clients.' })
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchClients()
})

const filteredClients = computed(() => {
  if (!searchQuery.value.trim()) return clients.value
  const query = searchQuery.value.toLowerCase()
  return clients.value.filter(client => 
    client.name.toLowerCase().includes(query) ||
    client.email.toLowerCase().includes(query) ||
    client.phone.includes(query) ||
    client.address.toLowerCase().includes(query)
  )
})

const getInitials = (name) => name ? name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2) : 'CL'

const viewClientHistory = (client) => {
  selectedClient.value = client
  
  // Map API recentProjects to history structure
  if (client.recentProjects && client.recentProjects.length > 0) {
     clientHistory.value = client.recentProjects.map((proj, idx) => ({
        id: proj.id || idx,
        service: proj.name,
        date: proj.date ? proj.date.split('T')[0] : new Date().toISOString().split('T')[0],
        color: '#3B82F6', // Default blue identifier
        colorName: 'Standard Package',
        status: proj.status || 'completed',
        notes: ''
     }))
  } else {
     clientHistory.value = []
  }
  
  showHistoryModal.value = true
}

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

// --- REPORTING LOGIC ---
const openReportModal = (client) => {
  selectedClientForReport.value = client
  reportForm.value = { reason: '', description: '', incident_date: '' }
  reportEvidenceFile.value = null
  isReportModalOpen.value = true
}

const handleReportEvidence = (event) => {
  if (event.target.files.length > 0) {
    reportEvidenceFile.value = event.target.files[0]
  }
}

const submitReport = async () => {
  if (!selectedClientForReport.value) return
  if (!reportForm.value.reason) {
    toast.error('Missing Reason', { description: 'Please select a reason for your report.' })
    return
  }

  isSubmittingReport.value = true
  
  const formData = new FormData()
  formData.append('reason', reportForm.value.reason)
  formData.append('description', reportForm.value.description)
  formData.append('incident_date', reportForm.value.incident_date)
  
  if (reportEvidenceFile.value) {
    formData.append('evidence', reportEvidenceFile.value)
  }

  try {
    const res = await api.post(`/service-provider/clients/${selectedClientForReport.value.id}/report`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    if (res.data.success) {
      toast.success('Report Submitted', { description: 'The admin team will review this incident.' })
      isReportModalOpen.value = false
    }
  } catch (error) {
    console.error(error)
    toast.error('Failed to submit report', { description: error.response?.data?.message || 'Check your inputs and try again.' })
  } finally {
    isSubmittingReport.value = false
  }
}
// ----------------------

</script>

<style scoped>
.provider-card {
  backdrop-filter: blur(10px);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.provider-card:hover {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.filter-btn {
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.filter-btn:hover {
  transform: translateY(-1px);
}

/* Animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #0d9488, #059669);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #14b8a6, #10b981);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .provider-card {
    padding: 1rem;
  }
  
  .filter-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
}

@media (max-width: 640px) {
  h1 {
    font-size: 1.75rem;
  }
}
</style>