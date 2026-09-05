<template>
  <div class="min-h-screen p-4 md:p-8 font-sans selection:bg-indigo-100 selection:text-indigo-900">
    
    <!-- Hero Page Header -->
    <div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-8 shadow-2xl mb-8 border border-slate-700">
      <div class="absolute -right-20 -top-20 opacity-10 pointer-events-none transform rotate-12">
        <ShieldAlert class="w-96 h-96 text-white" />
      </div>
      <div class="absolute left-1/4 bottom-0 opacity-20 pointer-events-none blur-3xl">
        <div class="w-64 h-64 bg-indigo-500 rounded-full"></div>
      </div>

      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/20 text-white/80 text-xs font-bold uppercase tracking-wider mb-4 backdrop-blur-md">
            <Sparkles class="w-3.5 h-3.5 text-indigo-400" /> Admin Module
          </div>
          <h1 class="text-4xl font-black text-white tracking-tight flex items-center gap-3">
            E-Commerce Shop Restrictions
          </h1>
          <p class="text-slate-400 mt-2 text-lg max-w-2xl">
            Monitor and resolve restriction petitions filed by users who have exceeded failed delivery limits.
          </p>
        </div>
        <div class="flex gap-3">
            <Button 
            @click="router.push('/admin/userReports')" 
            variant="outline" 
            class="bg-white/10 hover:bg-white/20 border-white/20 text-white backdrop-blur-md shadow-lg transition-all hover:scale-105 h-12 px-6 rounded-xl"
            >
            <ShieldAlert class="w-4 h-4 mr-2" /> 
            <span class="font-bold">User Reports</span>
            </Button>
            <Button 
            @click="fetchUsers" 
            variant="outline" 
            class="bg-indigo-600 hover:bg-indigo-500 border-0 text-white backdrop-blur-md shadow-lg shadow-indigo-600/20 transition-all hover:scale-105 h-12 px-6 rounded-xl"
            :disabled="isLoading"
            >
            <RefreshCw class="w-4 h-4 mr-2" :class="{'animate-spin': isLoading}" /> 
            <span class="font-bold">Sync Petitions</span>
            </Button>
        </div>
      </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 relative z-10">
      <Card class="border-0 bg-white dark:bg-slate-900 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 overflow-hidden group">
        <CardContent class="p-6 relative">
          <div class="absolute -right-6 -top-6 opacity-5 group-hover:opacity-10 transition-opacity"><FileText class="w-32 h-32 text-indigo-600" /></div>
          <div class="flex items-center gap-5 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-indigo-500/30">
              <FileText class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total Petitions Sent</p>
              <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ totalPetitionsCount }}</h3>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="border-0 bg-white dark:bg-slate-900 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-300 overflow-hidden group">
        <CardContent class="p-6 relative">
          <div class="absolute -right-6 -top-6 opacity-5 group-hover:opacity-10 transition-opacity"><Ban class="w-32 h-32 text-rose-600" /></div>
          <div class="flex items-center gap-5 relative z-10">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-500 to-pink-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-500/30">
              <Ban class="w-6 h-6" />
            </div>
            <div>
              <p class="text-sm font-bold text-slate-500 uppercase tracking-wider">Restricted Accounts Appealing</p>
              <h3 class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ groupedUsers.length }}</h3>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-3 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row items-center gap-3 mb-6 relative z-10">
      <div class="relative flex-1 w-full">
        <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5" />
        <Input v-model="searchQuery" placeholder="Search by name or email..." class="pl-11 h-12 w-full bg-slate-50 border-0 focus-visible:ring-2 focus-visible:ring-indigo-500 rounded-xl font-medium text-slate-700" />
      </div>
      <div class="flex gap-3 w-full sm:w-auto shrink-0">
        <Select v-model="roleFilter">
          <SelectTrigger class="w-[180px] h-12 rounded-xl bg-slate-50 border-0 font-bold text-slate-600 focus:ring-indigo-500">
            <SelectValue placeholder="Filter by Role" />
          </SelectTrigger>
          <SelectContent class="rounded-xl shadow-xl border-slate-100 font-medium">
            <SelectItem value="all">All Account Roles</SelectItem>
            <SelectItem value="client">Client</SelectItem>
            <SelectItem value="service_provider">Service Provider</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <!-- Data Table -->
    <Card class="border-0 shadow-[0_8px_30px_rgb(0,0,0,0.03)] bg-white rounded-3xl overflow-hidden relative z-10">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-slate-50 border-b border-slate-100">
            <TableRow class="hover:bg-transparent">
              <TableHead class="py-5 px-6 font-bold text-slate-500 uppercase tracking-wider text-xs">Petitioner User</TableHead>
              <TableHead class="font-bold text-slate-500 uppercase tracking-wider text-xs">Account Role</TableHead>
              <TableHead class="text-center font-bold text-slate-500 uppercase tracking-wider text-xs">Failed Deliveries</TableHead>
              <TableHead class="text-center font-bold text-slate-500 uppercase tracking-wider text-xs">Petitions Filed</TableHead>
              <TableHead class="font-bold text-slate-500 uppercase tracking-wider text-xs">Latest Filing Date</TableHead>
              <TableHead class="text-right py-5 px-6 font-bold text-slate-500 uppercase tracking-wider text-xs">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="isLoading">
              <TableCell colspan="6" class="h-64 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400">
                  <Loader2 class="w-10 h-10 animate-spin mb-4 text-indigo-500" />
                  <p class="font-medium text-lg">Fetching restriction petitions...</p>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-else-if="filteredUsers.length === 0">
              <TableCell colspan="6" class="h-64 text-center">
                <div class="flex flex-col items-center justify-center text-slate-400">
                  <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                    <ShieldCheck class="w-10 h-10 text-emerald-400" />
                  </div>
                  <p class="font-bold text-xl text-slate-600">All Clear</p>
                  <p class="text-sm mt-1">No petitions found matching your criteria.</p>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-else v-for="user in filteredUsers" :key="user.role + '_' + user.user_id" class="hover:bg-indigo-50/50 transition-colors border-b border-slate-50">
              <TableCell class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-300 flex items-center justify-center font-black text-slate-500 shadow-sm shrink-0">
                    {{ user.first_name.charAt(0) }}{{ user.last_name.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-bold text-slate-900 text-sm">{{ user.first_name }} {{ user.last_name }}</div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ user.email }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <Badge variant="secondary" class="capitalize bg-slate-100 text-slate-700 font-bold border-0 shadow-sm">
                  {{ user.role.replace('_', ' ') }}
                </Badge>
              </TableCell>
              <TableCell class="text-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-black shadow-sm" :class="user.failed_deliveries >= 3 ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-700'">
                  {{ user.failed_deliveries }}
                </span>
              </TableCell>
              <TableCell class="text-center">
                <div class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                  {{ user.total_petitions }} Petition(s)
                </div>
              </TableCell>
              <TableCell class="text-sm font-medium text-slate-600">
                {{ formatDate(user.latest_petition_date) }}
              </TableCell>
              <TableCell class="text-right py-4 px-6">
                <Button variant="outline" size="sm" class="text-indigo-600 hover:text-indigo-700 bg-white hover:bg-indigo-50 border-slate-200 hover:border-indigo-200 shadow-sm font-bold transition-all rounded-lg" @click="openDetailsModal(user)">
                  <Eye class="w-4 h-4 mr-2" /> Inspect Cases
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <!-- Petition Details Modal (Grouped by User) -->
    <Dialog :open="isDetailsModalOpen" @update:open="val => !val && (isDetailsModalOpen = false)">
      <DialogContent class="sm:max-w-2xl bg-white rounded-3xl p-0 overflow-hidden border-0 shadow-2xl max-h-[90vh] flex flex-col">
        <DialogHeader class="px-8 py-6 bg-gradient-to-br from-slate-900 to-slate-800 relative shrink-0">
          <div class="absolute top-0 right-0 opacity-10 pointer-events-none">
            <FileText class="w-40 h-40 -mt-6 -mr-6 text-white" />
          </div>
          <div v-if="selectedUser" class="relative z-10 pr-12">
            <div class="flex items-center gap-3 mb-3">
              <Badge variant="outline" class="border-white/20 bg-white/10 text-white backdrop-blur-sm font-bold uppercase tracking-wider shadow-sm">
                Restriction Appeals
              </Badge>
              <span class="text-slate-300 text-sm font-medium">{{ selectedUser.email }}</span>
            </div>
            <DialogTitle class="text-2xl font-black text-white tracking-tight flex items-center gap-3">
              {{ selectedUser.first_name }} {{ selectedUser.last_name }}
            </DialogTitle>
            <DialogDescription class="mt-2 text-slate-300 text-sm font-medium flex items-center gap-2">
              Role: <strong class="text-white capitalize">{{ selectedUser.role.replace('_', ' ') }}</strong>
              <span class="mx-2 text-slate-500">|</span>
              Failed Deliveries: <strong class="text-rose-400">{{ selectedUser.failed_deliveries }}</strong>
            </DialogDescription>
          </div>
        </DialogHeader>

        <div v-if="selectedUser" class="flex-1 overflow-y-auto p-8 custom-scrollbar bg-slate-50/50">
          
          <div v-for="(petition, index) in selectedUser.petitions" :key="petition.petition_id" class="mb-8 border-b border-slate-200 pb-8 last:border-0 last:pb-0">
            <h4 class="text-sm font-black text-indigo-500 uppercase tracking-widest mb-4 flex items-center gap-2">
              <FileText class="w-4 h-4" /> Petition #{{ selectedUser.petitions.length - index }} 
              <span class="text-slate-400 font-medium text-xs ml-2 normal-case tracking-normal">Filed on {{ formatDate(petition.created_at) }}</span>
            </h4>
            
            <div class="mb-4 pl-4 border-l-2 border-indigo-100">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Explanation / Reason</span>
              <div class="bg-white p-4 rounded-xl border border-slate-100 text-sm text-slate-700 font-medium shadow-sm">
                "{{ petition.reason }}"
              </div>
            </div>

            <div class="pl-4 border-l-2 border-indigo-100">
               <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 block flex items-center gap-1">
                <Paperclip class="w-3 h-3" /> Attached Evidence
              </span>
              <Button v-if="petition.attachment_path" @click="viewEvidence(petition.attachment_path)" variant="outline" size="sm" class="w-max h-9 text-xs text-indigo-700 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 hover:border-indigo-300 font-bold shadow-sm rounded-lg transition-all hover:-translate-y-0.5">
                  <Eye class="w-3.5 h-3.5 mr-2" /> Inspect Attached File
              </Button>
              <div v-else class="inline-flex items-center px-3 py-1.5 bg-slate-50 border border-slate-200 border-dashed rounded-lg">
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No evidence attached</p>
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 bg-white border-t border-slate-100 flex gap-3 shrink-0">
          <Button @click="confirmDeny" class="flex-1 rounded-xl h-12 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 shadow-sm font-bold text-sm transition-all hover:scale-105">
            <XCircle class="w-4 h-4 mr-2" /> Deny Petitions
          </Button>
          <Button @click="confirmRevoke" class="flex-[2] rounded-xl h-12 bg-emerald-500 hover:bg-emerald-600 text-white border-0 shadow-md shadow-emerald-500/20 font-bold text-sm transition-all hover:scale-105">
            <CheckCircle2 class="w-4 h-4 mr-2" /> Revoke Restriction
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Revoke Confirmation Alert -->
    <AlertDialog :open="isRevokeAlertOpen" @update:open="isRevokeAlertOpen = $event">
      <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10005]">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-xl font-bold flex items-center gap-2 text-emerald-600">
            <CheckCircle2 class="w-6 h-6"/> Restore Privileges
          </AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500 font-medium text-base mt-3 leading-relaxed">
            Are you sure you want to revoke the shop restriction for <strong class="text-slate-800">{{ selectedUser?.first_name }}</strong>? 
            <br/><br/>
            This action will completely delete all their failed delivery records and fully restore their purchasing access. A notification will be sent automatically.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-6 sm:space-x-3">
          <AlertDialogCancel @click="isRevokeAlertOpen = false" class="rounded-xl font-bold border-slate-200 text-slate-600 hover:bg-slate-50 h-11">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeRevoke" :disabled="isProcessingApi" class="border-0 rounded-xl font-bold bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/20 text-white h-11 px-6 shadow-md transition-transform hover:scale-105">
            <Loader2 v-if="isProcessingApi" class="w-4 h-4 animate-spin mr-2" />
            Yes, Revoke Restriction
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Deny Confirmation Alert -->
    <AlertDialog :open="isDenyAlertOpen" @update:open="isDenyAlertOpen = $event">
      <AlertDialogContent class="rounded-2xl border-0 shadow-2xl max-w-md z-[10005]">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-xl font-bold flex items-center gap-2 text-rose-600">
            <Ban class="w-6 h-6"/> Deny Petitions
          </AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500 font-medium text-base mt-3 leading-relaxed">
            Are you sure you want to deny all petitions for this user?
            <br/><br/>
            The restriction will remain active on <strong class="text-slate-800">{{ selectedUser?.first_name }}'s</strong> account. A notification will be sent informing them of this decision.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-6 sm:space-x-3">
          <AlertDialogCancel @click="isDenyAlertOpen = false" class="rounded-xl font-bold border-slate-200 text-slate-600 hover:bg-slate-50 h-11">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeDeny" :disabled="isProcessingApi" class="border-0 rounded-xl font-bold bg-rose-600 hover:bg-rose-700 shadow-rose-600/20 text-white h-11 px-6 shadow-md transition-transform hover:scale-105">
            <Loader2 v-if="isProcessingApi" class="w-4 h-4 animate-spin mr-2" />
            Yes, Deny Petitions
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { 
  ShieldAlert, RefreshCw, Search, Eye, Loader2, ShieldCheck, 
  Calendar, Paperclip, Sparkles, Ban, FileText, CheckCircle2, XCircle
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'

const router = useRouter()

// Main State
const groupedUsers = ref([])
const isLoading = ref(true)

// Filters State
const searchQuery = ref('')
const roleFilter = ref('all')

// Details Modal State
const isDetailsModalOpen = ref(false)
const selectedUser = ref(null)

// Action States
const isRevokeAlertOpen = ref(false)
const isDenyAlertOpen = ref(false)
const isProcessingApi = ref(false)

const getImageUrl = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;

    // Extract the base URL from your axios configuration and remove the '/api' suffix
    let baseUrl = api.defaults.baseURL || 'http://localhost:8000';
    baseUrl = baseUrl.replace(/\/api\/?$/, ''); 

    const cleanPath = path.startsWith('storage/') ? path.replace('storage/', '') : path;
    return `${baseUrl}/storage/${cleanPath}`;
}

const fetchUsers = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/admin/banned-users')
    if (res.data.success) {
      groupedUsers.value = res.data.data
    }
  } catch (err) {
    toast.error('Error fetching data', { description: 'Could not load restriction petitions.' })
  } finally {
    isLoading.value = false
  }
}

const openDetailsModal = (user) => {
  selectedUser.value = user
  isDetailsModalOpen.value = true
}

const viewEvidence = (path) => {
  const url = getImageUrl(path)
  if (url) window.open(url, '_blank')
}

// ---------------------------------
// ACTIONS MODALS & API INTEGRATION
// ---------------------------------

const confirmRevoke = () => {
    isRevokeAlertOpen.value = true
}

const confirmDeny = () => {
    isDenyAlertOpen.value = true
}

const executeRevoke = async () => {
    isProcessingApi.value = true
    try {
        const payload = {
            user_id: selectedUser.value.user_id,
            role: selectedUser.value.role
        }

        const res = await api.post('/admin/banned-users/revoke', payload)
        if (res.data.success) {
            toast.success('Restriction Revoked', { description: `User's purchasing privileges have been fully restored.` })
            isRevokeAlertOpen.value = false
            isDetailsModalOpen.value = false
            fetchUsers() // refresh list
        }
    } catch (error) {
        toast.error('Action Failed', { description: error.response?.data?.message || 'Could not revoke restriction.' })
    } finally {
        isProcessingApi.value = false
    }
}

const executeDeny = async () => {
    isProcessingApi.value = true
    try {
        const payload = {
            user_id: selectedUser.value.user_id,
            role: selectedUser.value.role
        }

        const res = await api.post('/admin/banned-users/deny', payload)
        if (res.data.success) {
            toast.success('Petitions Denied', { description: `The restriction remains active for this user.` })
            isDenyAlertOpen.value = false
            isDetailsModalOpen.value = false
            fetchUsers() // refresh list
        }
    } catch (error) {
        toast.error('Action Failed', { description: error.response?.data?.message || 'Could not deny petition.' })
    } finally {
        isProcessingApi.value = false
    }
}


// --- Computed Properties ---
const totalPetitionsCount = computed(() => {
  return groupedUsers.value.reduce((acc, user) => acc + user.total_petitions, 0)
})

const pendingCount = computed(() => {
  let count = 0;
  groupedUsers.value.forEach(user => {
      user.petitions.forEach(p => {
          if(p.status === 'pending') count++;
      })
  });
  return count;
})

const filteredUsers = computed(() => {
  return groupedUsers.value.filter(item => {
    const searchStr = `${item.first_name} ${item.last_name} ${item.email}`.toLowerCase()
    const matchesSearch = searchStr.includes(searchQuery.value.toLowerCase())
    const matchesRole = roleFilter.value === 'all' || item.role === roleFilter.value
    
    return matchesSearch && matchesRole
  })
})

// --- Utilities ---
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return isNaN(date.getTime()) ? 'N/A' : new Intl.DateTimeFormat('en-US', {
    month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit'
  }).format(date)
}

onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>