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
            <Badge class="bg-gradient-to-r from-blue-500 to-violet-500 border-0">CRM</Badge>
          </h1>
          <p class="text-slate-400 text-sm">Manage client relationships and service history</p>
        </div>
        <div class="flex gap-4 items-center flex-wrap w-full md:w-auto">
          <Dialog v-model:open="showAddClientModal">
            <DialogTrigger as-child>
              <Button class="bg-gradient-to-r from-blue-600 to-violet-600 hover:from-blue-700 hover:to-violet-700 text-white border-0 shadow-lg shadow-blue-500/20" @click="resetForm(); editingClient = null">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                Add New Client
              </Button>
            </DialogTrigger>
            <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 sm:max-w-[600px]">
              <DialogHeader>
                <DialogTitle>{{ editingClient ? 'Edit Client' : 'Add New Client' }}</DialogTitle>
              </DialogHeader>
              <form @submit.prevent="saveClient" class="grid gap-4 py-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label>Full Name *</Label>
                    <Input v-model="clientForm.name" required placeholder="Enter client name" class="bg-slate-950 border-slate-800" />
                  </div>
                  <div class="space-y-2">
                    <Label>Email Address *</Label>
                    <Input v-model="clientForm.email" type="email" required placeholder="client@example.com" class="bg-slate-950 border-slate-800" />
                  </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                   <div class="space-y-2">
                    <Label>Phone Number *</Label>
                    <Input v-model="clientForm.phone" type="tel" required placeholder="+63 XXX XXX XXXX" class="bg-slate-950 border-slate-800" />
                  </div>
                  <div class="space-y-2">
                    <Label>Status</Label>
                    <RadioGroup v-model="clientForm.status" class="flex gap-4 mt-2">
                      <div class="flex items-center space-x-2">
                        <RadioGroupItem value="active" id="active" class="border-slate-600 text-blue-500" />
                        <Label htmlFor="active">Active</Label>
                      </div>
                      <div class="flex items-center space-x-2">
                        <RadioGroupItem value="inactive" id="inactive" class="border-slate-600 text-blue-500" />
                        <Label htmlFor="inactive">Inactive</Label>
                      </div>
                    </RadioGroup>
                  </div>
                </div>
                <div class="space-y-2">
                  <Label>Address</Label>
                  <Textarea v-model="clientForm.address" placeholder="Enter client address" class="bg-slate-950 border-slate-800 min-h-[80px]" />
                </div>
                <div class="space-y-2">
                  <Label>Notes</Label>
                  <Textarea v-model="clientForm.notes" placeholder="Add notes..." class="bg-slate-950 border-slate-800 min-h-[100px]" />
                </div>
                <div class="flex justify-end gap-3 mt-4">
                   <Button type="button" variant="outline" class="border-slate-700 hover:bg-slate-800" @click="closeModal">Cancel</Button>
                   <Button type="submit" class="bg-gradient-to-r from-blue-600 to-violet-600 border-0">{{ editingClient ? 'Update Client' : 'Add Client' }}</Button>
                </div>
              </form>
            </DialogContent>
          </Dialog>

          <div class="relative w-full md:w-[250px]">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
               <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <Input v-model="searchQuery" placeholder="Search clients..." class="pl-10 bg-slate-900 border-slate-800 focus:border-blue-500" />
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

    <div class="m-6 md:m-8 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
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
          <Button variant="outline" class="border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-200" @click="exportClients">
             Export
          </Button>
        </div>
      </div>

      <div v-if="viewMode === 'list'" class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-slate-950/50">
            <TableRow class="border-slate-800 hover:bg-transparent">
              <TableHead class="w-[50px]"><Checkbox :checked="selectAll" @update:checked="toggleSelectAll" class="border-slate-600 data-[state=checked]:bg-blue-600 data-[state=checked]:border-blue-600" /></TableHead>
              <TableHead>Client</TableHead>
              <TableHead>Contact</TableHead>
              <TableHead>Location</TableHead>
              <TableHead>Jobs</TableHead>
              <TableHead>Status</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="client in filteredClients" :key="client.id" class="border-slate-800 hover:bg-slate-800/30">
              <TableCell><Checkbox :checked="selectedClients.includes(client.id)" @update:checked="(val) => { if(val) selectedClients.push(client.id); else selectedClients = selectedClients.filter(id => id !== client.id) }" class="border-slate-600" /></TableCell>
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
                <Badge variant="outline" class="border-blue-500/30 bg-blue-500/10 text-blue-400">{{ client.jobCount }} jobs</Badge>
              </TableCell>
              <TableCell>
                 <Badge :class="[client.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'bg-slate-500/10 text-slate-400 hover:bg-slate-500/20']" variant="secondary">
                    {{ client.status === 'active' ? 'Active' : 'Inactive' }}
                 </Badge>
              </TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="ghost" size="icon" @click="viewClientHistory(client)" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></Button>
                  <Button variant="ghost" size="icon" @click="editClient(client); showAddClientModal = true" class="h-8 w-8 text-slate-400 hover:text-white hover:bg-slate-800"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg></Button>
                  <Button variant="ghost" size="icon" @click="deleteClient(client.id)" class="h-8 w-8 text-red-400 hover:text-red-300 hover:bg-red-900/20"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></Button>
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
                   <Checkbox :checked="selectedClients.includes(client.id)" @update:checked="(val) => { if(val) selectedClients.push(client.id); else selectedClients = selectedClients.filter(id => id !== client.id) }" class="border-slate-600" />
                </div>
                <h3 class="font-medium text-slate-200">{{ client.name }}</h3>
                <p class="text-sm text-slate-500 mb-4">{{ client.email }}</p>
                <div class="space-y-2 text-sm text-slate-400">
                   <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg> {{ client.phone }}</div>
                   <div class="flex items-center gap-2"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /></svg> {{ client.address }}</div>
                </div>
             </div>
             <div class="p-4 flex justify-between items-center bg-slate-900/50">
                <div class="flex flex-col">
                   <span class="text-lg font-bold text-slate-200">{{ client.jobCount }}</span>
                   <span class="text-xs text-slate-500">Jobs</span>
                </div>
                <div class="flex gap-2">
                   <Button variant="secondary" size="sm" class="bg-slate-800 text-slate-300 hover:text-white" @click="viewClientHistory(client)">History</Button>
                   <Button variant="secondary" size="sm" class="bg-slate-800 text-slate-300 hover:text-white" @click="editClient(client); showAddClientModal = true">Edit</Button>
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
         <p class="text-slate-400 mb-6">Try adjusting your search or add a new client to get started.</p>
         <Button @click="showAddClientModal = true" class="bg-gradient-to-r from-blue-600 to-violet-600 border-0">Add New Client</Button>
      </div>
    </div>

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
                No service history available.
             </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Table, TableHeader, TableRow, TableHead, TableBody, TableCell } from '@/components/ui/table'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Checkbox } from '@/components/ui/checkbox'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'

// Data
const clients = ref([
  { id: 1, name: 'John Michael Santos', email: 'john.santos@example.com', phone: '+63 912 345 6789', address: '123 Main St, Bacoor, Cavite', jobCount: 5, status: 'active', notes: 'Prefers eco-friendly paint options' },
  { id: 2, name: 'Maria Cristina Garcia', email: 'maria.garcia@example.com', phone: '+63 917 890 1234', address: '456 Oak Ave, Imus, Cavite', jobCount: 3, status: 'active', notes: 'Commercial building owner' },
  // ... rest of your dummy data ...
  { id: 3, name: 'Robert Lim', email: 'robert.lim@example.com', phone: '+63 918 567 8901', address: '789 Pine Rd, Dasmariñas, Cavite', jobCount: 8, status: 'active', notes: 'Frequent customer, bulk orders' },
  { id: 4, name: 'Sarah Tan', email: 'sarah.tan@example.com', phone: '+63 919 234 5678', address: '321 Elm St, Tagaytay, Cavite', jobCount: 2, status: 'inactive', notes: 'Moved to different province' },
])

const searchQuery = ref('')
const viewMode = ref('list')
const selectedClients = ref([])
const selectAll = ref(false)
const showAddClientModal = ref(false)
const showHistoryModal = ref(false)
const editingClient = ref(null)
const selectedClient = ref(null)

const clientForm = ref({ name: '', email: '', phone: '', address: '', status: 'active', notes: '' })

const clientHistory = ref([
  { id: 1, service: 'Living Room Painting', date: '2024-01-15', color: '#4A90E2', colorName: 'Ocean Blue', status: 'completed', notes: 'Two coats applied, client satisfied with color matching' },
  { id: 2, service: 'Bedroom Wall Repair & Painting', date: '2024-02-20', color: '#F5A623', colorName: 'Sunset Orange', status: 'completed', notes: 'Minor wall repair before painting' },
  { id: 3, service: 'Kitchen Cabinets Refinishing', date: '2024-03-10', color: '#7ED321', colorName: 'Mint Green', status: 'in-progress', notes: 'Scheduled for second coat' }
])

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

const toggleSelectAll = (checked) => {
  selectAll.value = checked
  selectedClients.value = checked ? clients.value.map(client => client.id) : []
}

const getInitials = (name) => name.split(' ').map(word => word[0]).join('').toUpperCase().slice(0, 2)

const saveClient = () => {
  if (editingClient.value) {
    const index = clients.value.findIndex(c => c.id === editingClient.value.id)
    if (index !== -1) clients.value[index] = { ...editingClient.value, ...clientForm.value }
  } else {
    clients.value.unshift({ id: clients.value.length + 1, ...clientForm.value, jobCount: 0 })
  }
  showAddClientModal.value = false
  resetForm()
}

const editClient = (client) => {
  editingClient.value = client
  clientForm.value = { ...client }
}

const deleteClient = (id) => {
  if (confirm('Are you sure you want to delete this client?')) {
    clients.value = clients.value.filter(client => client.id !== id)
    selectedClients.value = selectedClients.value.filter(clientId => clientId !== id)
  }
}

const viewClientHistory = (client) => {
  selectedClient.value = client
  showHistoryModal.value = true
}

const exportClients = () => {
  const blob = new Blob([JSON.stringify(filteredClients.value, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'clients-export.json'
  a.click()
  URL.revokeObjectURL(url)
}

const formatDate = (dateString) => new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })

const resetForm = () => {
  clientForm.value = { name: '', email: '', phone: '', address: '', status: 'active', notes: '' }
  editingClient.value = null
}

const closeModal = () => {
  showAddClientModal.value = false
  resetForm()
}
</script>