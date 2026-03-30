<template>
  <div class="flex flex-col gap-6 p-4 sm:p-8 min-h-screen text-slate-200 relative">
    <Teleport to="body">
      <Toaster richColors position="top-right"
      :expand="false"
      :close-button="true"
      :visible-toasts="1"/>
    </Teleport>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-white flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg shadow-indigo-500/20 shrink-0">
            <Users class="w-6 h-6 text-white" />
          </div>
          Provider Requests
        </h1>
        <p class="text-slate-400 mt-2 text-sm">
          Manage incoming partnership proposals from service providers wanting to source materials from your network.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="fetchData" class="bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4 mr-2" />
          Refresh
        </Button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card class="bg-slate-900 border-slate-800 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-slate-300">Pending Requests</CardTitle>
          <Inbox class="h-4 w-4 text-amber-400" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-white">{{ pendingCount }}</div>
          <p class="text-xs text-slate-500">Awaiting your approval</p>
        </CardContent>
      </Card>

      <Card class="bg-slate-900 border-slate-800 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-slate-300">Active Partners</CardTitle>
          <Users class="h-4 w-4 text-emerald-400" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-white">{{ activeCount }}</div>
          <p class="text-xs text-slate-500">Connected service providers</p>
        </CardContent>
      </Card>

      <Card class="bg-slate-900 border-slate-800 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-slate-300">New This Week</CardTitle>
          <CalendarRange class="h-4 w-4 text-indigo-400" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-white">{{ newThisWeekCount }}</div>
          <p class="text-xs text-slate-500">Recent inquiries</p>
        </CardContent>
      </Card>

      <Card class="bg-slate-900 border-slate-800 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium text-slate-300">Approval Rate</CardTitle>
          <CheckCircle2 class="h-4 w-4 text-purple-400" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-white">{{ approvalRate }}</div>
          <p class="text-xs text-slate-500">Historical acceptance</p>
        </CardContent>
      </Card>
    </div>

    <Card class="col-span-4 bg-slate-900 border-slate-800 shadow-lg">
      <CardHeader>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800 w-full sm:w-auto">
            <button 
              @click="currentTab = 'pending'" 
              :class="currentTab === 'pending' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800'" 
              class="flex-1 sm:flex-none px-5 py-2 rounded-lg text-sm font-medium transition-all"
            >
              Pending ({{ pendingCount }})
            </button>
            <button 
              @click="currentTab = 'active'" 
              :class="currentTab === 'active' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800'" 
              class="flex-1 sm:flex-none px-5 py-2 rounded-lg text-sm font-medium transition-all"
            >
              Active Partners ({{ activeCount }})
            </button>
          </div>
          
          <div class="relative w-full sm:w-72">
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-500" />
            <Input 
              v-model="searchQuery" 
              placeholder="Search provider name or email..." 
              class="pl-9 bg-slate-950 border-slate-700 text-white placeholder:text-slate-500 focus-visible:ring-indigo-500" 
            />
          </div>
        </div>
      </CardHeader>
      
      <CardContent class="overflow-x-auto">
        <div v-if="loading" class="flex flex-col items-center justify-center py-12 space-y-4">
          <Loader2 class="w-10 h-10 animate-spin text-indigo-500" />
          <p class="text-sm text-slate-400">Loading records...</p>
        </div>

        <div v-else-if="filteredRequests.length === 0" class="flex flex-col items-center justify-center py-16 text-center border-2 border-dashed rounded-xl border-slate-800 bg-slate-800/20">
          <div class="bg-slate-800 p-4 rounded-full mb-4">
            <Briefcase class="w-8 h-8 text-slate-400" />
          </div>
          <h3 class="text-lg font-semibold text-white">No {{ currentTab === 'pending' ? 'Pending Requests' : 'Active Partners' }}</h3>
          <p class="text-slate-400 text-sm max-w-sm mt-1">
            There are currently no records matching your criteria in this tab.
          </p>
        </div>

        <div v-else class="rounded-xl border border-slate-800 overflow-hidden bg-slate-950/50 min-w-[700px]">
          <Table>
            <TableHeader class="bg-slate-800/50 border-b border-slate-800">
              <TableRow class="hover:bg-transparent border-slate-800">
                <TableHead class="w-[300px] text-slate-300">Service Provider</TableHead>
                <TableHead class="text-slate-300">Location</TableHead>
                <TableHead class="text-slate-300">Date Applied</TableHead>
                <TableHead class="text-slate-300">Status</TableHead>
                <TableHead class="text-right text-slate-300">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="req in filteredRequests" :key="req.id" class="hover:bg-slate-800/50 border-slate-800 transition-colors">
                <TableCell>
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 font-bold text-sm shrink-0">
                      {{ req.provider_name.substring(0, 2).toUpperCase() }}
                    </div>
                    <div class="flex flex-col">
                      <span class="font-semibold text-slate-200">{{ req.provider_name }}</span>
                      <span class="text-xs text-slate-500">{{ req.email }}</span>
                    </div>
                  </div>
                </TableCell>
                
                <TableCell>
                  <div class="flex flex-col text-sm text-slate-400">
                    <span>{{ req.location }}</span>
                  </div>
                </TableCell>

                <TableCell>
                  <div class="flex items-center gap-2 text-sm text-slate-400">
                    <Calendar class="w-3.5 h-3.5 text-slate-500" />
                    {{ req.date_requested }}
                  </div>
                </TableCell>

                <TableCell>
                  <Badge v-if="req.status === 'pending'" variant="secondary" class="bg-amber-500/10 text-amber-400 border border-amber-500/20">
                    Pending
                  </Badge>
                  <Badge v-else-if="req.status === 'active'" variant="secondary" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Active
                  </Badge>
                </TableCell>

                <TableCell class="text-right">
                  <Button 
                    v-if="req.status === 'pending'"
                    variant="outline" 
                    size="sm"
                    class="h-8 gap-2 bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white"
                    @click="openViewDialog(req)"
                  >
                    <Eye class="h-4 w-4" />
                    Review
                  </Button>
                  <Button 
                    v-else-if="req.status === 'active'"
                    variant="outline" 
                    size="sm"
                    class="h-8 gap-2 bg-indigo-500/10 border-indigo-500/30 text-indigo-400 hover:bg-indigo-500/20 hover:text-indigo-300"
                    @click="openAgreementDialog(req)"
                  >
                    <FileText class="h-4 w-4" />
                    View Agreement
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="showViewDialog" @update:open="closeViewDialog">
      <DialogContent class="sm:max-w-[700px] w-[95vw] bg-slate-900 border-slate-800 text-slate-200 flex flex-col max-h-[90vh] p-0 overflow-hidden">
        <DialogHeader class="px-5 py-4 border-b border-slate-800 shrink-0 bg-slate-900 z-10">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-500/20 border border-indigo-500/30 rounded-lg shrink-0">
              <UserCheck class="h-5 w-5 text-indigo-400" />
            </div>
            <div>
              <DialogTitle class="text-white text-left">Service Provider Proposal</DialogTitle>
              <DialogDescription class="text-slate-400 text-left mt-1">
                Review the provider's details, agreement document, and finalize the partnership.
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="overflow-y-auto custom-scrollbar p-5 flex-1" v-if="selectedRequest">
          <div class="grid gap-6">
            <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                <div class="space-y-1">
                  <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Provider Name</span>
                  <p class="font-medium text-slate-200 text-base">{{ selectedRequest.provider_name }}</p>
                </div>
                <div class="space-y-1">
                  <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Contact Number</span>
                  <p class="text-slate-400">{{ selectedRequest.phone }}</p>
                </div>
                <div class="space-y-1 sm:col-span-2">
                  <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Email Address</span>
                  <p class="text-slate-400 break-all">{{ selectedRequest.email }}</p>
                </div>
                <div class="space-y-1 sm:col-span-2">
                  <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Registered Address</span>
                  <p class="text-slate-400">{{ selectedRequest.full_address }}</p>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <h4 class="text-sm font-medium leading-none text-slate-200">Partnership Message</h4>
              <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-sm text-slate-400 italic leading-relaxed">
                "{{ selectedRequest.message || 'No formal message provided.' }}"
              </div>
            </div>
            
            <div class="space-y-3 pt-4 border-t border-slate-800">
              <label class="block text-sm font-medium text-slate-200 flex items-center justify-between">
                <span>Partnership Agreement Document</span>
                <span class="text-xs font-normal text-emerald-400 flex items-center"><FileCheck class="w-3 h-3 mr-1" /> Signed by Provider</span>
              </label>
              <div class="h-[250px] border border-slate-700 rounded-xl overflow-hidden bg-white">
                <iframe 
                  v-if="selectedRequest.agreement_url" 
                  :src="selectedRequest.agreement_url" 
                  class="w-full h-full border-0"
                ></iframe>
                <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 bg-slate-900">
                  <FileX class="w-8 h-8 mb-2 opacity-50" />
                  No agreement document found.
                </div>
              </div>
            </div>

            <div class="space-y-3 pt-4 border-t border-slate-800">
              <label class="block text-sm font-medium text-slate-200 flex justify-between">
                <span>Your Digital Signature <span class="text-red-400">*</span></span>
                <button @click="clearSignature" class="text-xs text-indigo-400 hover:text-indigo-300">Clear Pad</button>
              </label>
              <div class="bg-white rounded-xl border border-slate-700 overflow-hidden relative shadow-inner">
                <canvas 
                  ref="signaturePad" 
                  width="600" 
                  height="150" 
                  class="w-full h-[150px] touch-none cursor-crosshair"
                  @mousedown="startDrawing" 
                  @mousemove="draw" 
                  @mouseup="stopDrawing" 
                  @mouseleave="stopDrawing" 
                  @touchstart.prevent="startDrawingTouch" 
                  @touchmove.prevent="drawTouch" 
                  @touchend.prevent="stopDrawing"
                ></canvas>
                <div v-if="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center text-slate-300 font-medium tracking-wide">
                  Sign here to officially approve
                </div>
              </div>
            </div>

            <div class="space-y-3 pt-4 border-t border-slate-800">
              <label class="flex items-start gap-3 mt-2 cursor-pointer group">
                <div class="relative flex items-center justify-center mt-0.5 shrink-0">
                  <input 
                    type="checkbox" 
                    v-model="agreedToTerms"
                    class="peer appearance-none w-5 h-5 border-2 border-slate-600 rounded-md bg-slate-900 checked:bg-indigo-500 checked:border-indigo-500 transition-colors cursor-pointer"
                  />
                  <svg class="absolute w-3 h-3 text-white opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span class="text-sm text-slate-300 group-hover:text-slate-200 transition-colors leading-relaxed">
                  I have read the agreement document and certify that the signature above is my true digital signature to accept this Service Provider into our network.
                </span>
              </label>
            </div>
          </div>
        </div>

        <DialogFooter class="px-5 py-4 border-t border-slate-800 flex flex-col-reverse sm:flex-row gap-2 sm:justify-between items-center bg-slate-900 shrink-0">
           <Button variant="ghost" class="w-full sm:w-auto text-slate-400 hover:text-white hover:bg-slate-800" @click="closeViewDialog">
             Cancel
           </Button>
           <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
              <Button 
                variant="outline" 
                class="w-full sm:w-auto border-red-900/50 bg-red-950/20 text-red-400 hover:bg-red-900/40 hover:text-red-300"
                @click="requirePermission('approve', initiateReject)"
              >
                <X class="mr-2 h-4 w-4" />
                Decline
              </Button>
              <Button 
                class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white border-0 disabled:opacity-50 disabled:cursor-not-allowed transition-all" 
                @click="requirePermission('approve', initiateApprove)"
                :disabled="!agreedToTerms || !hasSignature"
              >
                <Check class="mr-2 h-4 w-4" />
                Approve Partner
              </Button>
           </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showApproveDialog" @update:open="showApproveDialog = $event">
      <AlertDialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[95vw] sm:max-w-md rounded-xl">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-white text-left">Confirm Partnership</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-400 text-left">
            You are about to accept <b class="text-white">{{ selectedRequest?.provider_name }}</b> into your distributor network. 
            <br/><br/>
            An official agreement document bearing your signature will be securely generated and saved. Proceed?
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex flex-col-reverse sm:flex-row gap-2">
          <AlertDialogCancel @click="showApproveDialog = false" class="mt-0 sm:mt-0 bg-slate-800 border-slate-700 text-slate-300 hover:bg-slate-700 hover:text-white" :disabled="isProcessing">
            Review Again
          </AlertDialogCancel>
          
          <Button 
            @click="confirmApprove" 
            :disabled="isProcessing"
            class="bg-indigo-600 hover:bg-indigo-500 text-white border-0"
          >
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            <span v-if="isProcessing">Processing...</span>
            <span v-else>Finalize Approval</span>
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showRejectDialog" @update:open="closeRejectDialog">
      <DialogContent class="sm:max-w-[425px] w-[95vw] rounded-xl bg-slate-900 border-slate-800 text-slate-200">
        <DialogHeader>
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2 text-left">
            <div class="p-2 bg-red-500/10 border border-red-500/20 rounded-full w-fit">
              <ShieldAlert class="h-5 w-5 text-red-400" />
            </div>
            <div>
              <DialogTitle class="text-white">Decline Proposal</DialogTitle>
              <DialogDescription class="text-slate-400 mt-1">
                Provide a reason for declining this request.
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>
        
        <div class="py-2 space-y-4">
          <div class="space-y-2 text-left">
            <Label for="reason" class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Rejection Reason</Label>
            <Textarea 
              id="reason" 
              v-model="rejectReason" 
              placeholder="e.g., We are not accepting new partners in this region currently."
              class="resize-none bg-slate-950 border-slate-700 text-white placeholder:text-slate-600 focus-visible:ring-indigo-500"
              rows="4"
            />
          </div>
        </div>

        <DialogFooter class="flex flex-col-reverse sm:flex-row gap-2 mt-4">
          <Button variant="outline" @click="closeRejectDialog" class="w-full sm:w-auto bg-transparent border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">Back</Button>
          <Button 
            variant="destructive"
            @click="submitReject" 
            :disabled="isProcessing || !rejectReason.trim()"
            class="w-full sm:w-auto bg-red-600 hover:bg-red-500 text-white border-0 disabled:opacity-50"
          >
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            Decline Request
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog :open="showAgreementDialog" @update:open="showAgreementDialog = $event">
      <DialogContent class="sm:max-w-[800px] w-[95vw] bg-slate-900 border-slate-800 text-slate-200 flex flex-col h-[85vh] p-0 overflow-hidden shadow-2xl">
        <DialogHeader class="px-5 py-4 border-b border-slate-800 shrink-0 bg-slate-900 z-10">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-500/20 border border-indigo-500/30 rounded-lg shrink-0">
              <FileText class="h-5 w-5 text-indigo-400" />
            </div>
            <div>
              <DialogTitle class="text-white text-left">Official Partnership Agreement</DialogTitle>
              <DialogDescription class="text-slate-400 text-left mt-1">
                Signed document containing terms, conditions, and authorized signatures.
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="flex-1 bg-slate-950 p-4 sm:p-6 overflow-hidden">
          <div class="w-full h-full bg-white rounded-lg overflow-hidden border border-slate-700 shadow-inner">
            <iframe 
              v-if="selectedAgreement?.agreement_url" 
              :src="selectedAgreement.agreement_url" 
              class="w-full h-full border-0"
              title="Finalized Agreement"
            ></iframe>
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-500 bg-slate-900">
              <FileX class="w-8 h-8 mb-2 opacity-50" />
              Document file not found.
            </div>
          </div>
        </div>

        <DialogFooter class="px-5 py-4 border-t border-slate-800 flex flex-col sm:flex-row gap-2 sm:justify-between items-center bg-slate-900 shrink-0">
          <Button variant="ghost" class="w-full sm:w-auto text-slate-400 hover:text-white hover:bg-slate-800" @click="showAgreementDialog = false">
            Close Viewer
          </Button>
          <Button 
            class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white border-0"
            @click="downloadAgreement(selectedAgreement?.agreement_url)"
            :disabled="!selectedAgreement?.agreement_url"
          >
            <Download class="mr-2 h-4 w-4" />
            Download Document
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { Toaster, toast } from 'vue-sonner'
import api from '@/utils/axios'
import { 
  Loader2, RefreshCw, Search, Eye, Check, X,
  Inbox, Users, CalendarRange, CheckCircle2,
  Calendar, Briefcase, UserCheck, FileText, Download,
  FileCheck, FileX, ShieldAlert
} from 'lucide-vue-next'

import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'

// State Variables
const requests = ref([])
const loading = ref(false)
const searchQuery = ref('')
const isProcessing = ref(false)
const agreedToTerms = ref(false)
const currentTab = ref('pending') 

// Dialog Visibility
const showViewDialog = ref(false)
const showApproveDialog = ref(false)
const showRejectDialog = ref(false)
const showAgreementDialog = ref(false) 

// Selection & Forms
const selectedRequest = ref(null)
const selectedAgreement = ref(null) 
const rejectReason = ref('')

// Signature Pad State
const signaturePad = ref(null)
const isDrawing = ref(false)
const ctx = ref(null)
const hasSignature = ref(false)
const finalSignatureBase64 = ref('') 

// User Permissions setup via RBAC
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// RBAC Action Interceptor
const requirePermission = (action, callback) => {
  const permKey = `can_${action}`
  
  if (!permissions.value[permKey]) {
    toast.error(`Access Denied`, {
        description: `You do not have permission to ${action} service provider requests.`,
        duration: 5000,
        style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
    });
    return;
  }
  if (callback) callback();
}

const filteredRequests = computed(() => {
  let list = requests.value.filter(req => req.status === currentTab.value)
  if (!searchQuery.value) return list
  
  const query = searchQuery.value.toLowerCase()
  return list.filter(req => 
    req.provider_name.toLowerCase().includes(query) ||
    req.email.toLowerCase().includes(query) ||
    req.location.toLowerCase().includes(query)
  )
})

const pendingCount = computed(() => requests.value.filter(r => r.status === 'pending').length)
const activeCount = computed(() => requests.value.filter(r => r.status === 'active').length)
const newThisWeekCount = computed(() => {
  const oneWeekAgo = new Date()
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
  return requests.value.filter(r => new Date(r.date_requested) >= oneWeekAgo).length
})
const approvalRate = computed(() => {
  const totalResolved = requests.value.filter(r => r.status === 'active' || r.status === 'rejected').length
  if (totalResolved === 0) return '0%'
  const approved = requests.value.filter(r => r.status === 'active').length
  return Math.round((approved / totalResolved) * 100) + '%'
})

const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/operation-distributor/service-provider-requests')
    if (response.data.success) {
      requests.value = response.data.data
      if (response.data.permissions) {
        permissions.value = response.data.permissions
      }
    }
  } catch (error) {
    console.error(error)
    if (error.response?.status === 403) {
      toast.error('Access Denied', {
         description: error.response.data.message || 'You lack permissions to view these requests.',
         style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
      })
    } else {
      toast.error('Failed to load data', { description: 'Check your internet connection.' })
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

const openViewDialog = (req) => {
  selectedRequest.value = req
  agreedToTerms.value = false
  hasSignature.value = false
  finalSignatureBase64.value = ''
  showViewDialog.value = true
  
  nextTick(() => {
    initSignaturePad()
  })
}

const closeViewDialog = () => {
  showViewDialog.value = false
  agreedToTerms.value = false
  hasSignature.value = false
  finalSignatureBase64.value = ''
}

const openAgreementDialog = (req) => {
  selectedAgreement.value = req
  showAgreementDialog.value = true
}

const downloadAgreement = (url) => {
  if (!url) return
  const link = document.createElement('a')
  link.href = url
  link.download = 'Official_Partnership_Agreement.html'
  link.target = '_blank'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const closeRejectDialog = () => {
  showRejectDialog.value = false
  rejectReason.value = ''
}

const initSignaturePad = () => {
  if (!signaturePad.value) return
  ctx.value = signaturePad.value.getContext('2d')
  ctx.value.lineWidth = 2.5
  ctx.value.lineCap = 'round'
  ctx.value.strokeStyle = '#000000'
  clearSignature()
}

const startDrawing = (e) => {
  isDrawing.value = true
  hasSignature.value = true
  draw(e)
}

const draw = (e) => {
  if (!isDrawing.value || !ctx.value) return
  const rect = signaturePad.value.getBoundingClientRect()
  const x = e.clientX - rect.left
  const y = e.clientY - rect.top

  ctx.value.lineTo(x, y)
  ctx.value.stroke()
  ctx.value.beginPath()
  ctx.value.moveTo(x, y)
}

const stopDrawing = () => {
  isDrawing.value = false
  if (ctx.value) ctx.value.beginPath()
}

const startDrawingTouch = (e) => {
  const touch = e.touches[0]
  const rect = signaturePad.value.getBoundingClientRect()
  const x = touch.clientX - rect.left
  const y = touch.clientY - rect.top
  
  isDrawing.value = true
  hasSignature.value = true
  ctx.value.beginPath()
  ctx.value.moveTo(x, y)
}

const drawTouch = (e) => {
  if (!isDrawing.value || !ctx.value) return
  const touch = e.touches[0]
  const rect = signaturePad.value.getBoundingClientRect()
  const x = touch.clientX - rect.left
  const y = touch.clientY - rect.top

  ctx.value.lineTo(x, y)
  ctx.value.stroke()
  ctx.value.beginPath()
  ctx.value.moveTo(x, y)
}

const clearSignature = () => {
  if (!ctx.value || !signaturePad.value) return
  ctx.value.clearRect(0, 0, signaturePad.value.width, signaturePad.value.height)
  hasSignature.value = false
  finalSignatureBase64.value = ''
  ctx.value.beginPath()
}

const initiateApprove = () => {
  if (!agreedToTerms.value || !hasSignature.value) return
  
  finalSignatureBase64.value = signaturePad.value ? signaturePad.value.toDataURL('image/png') : ''

  showViewDialog.value = false
  showApproveDialog.value = true
}

const confirmApprove = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true

  try {
    const response = await api.post(`/operation-distributor/service-provider-requests/${selectedRequest.value.id}/approve`, {
      signature: finalSignatureBase64.value
    })
    
    if (response.data.success) {
      const index = requests.value.findIndex(r => r.id === selectedRequest.value.id)
      if (index !== -1) {
        requests.value[index].status = 'active'
      }
      
      showApproveDialog.value = false
      agreedToTerms.value = false
      hasSignature.value = false
      finalSignatureBase64.value = '' 
      selectedRequest.value = null
      
      toast.success('Partnership Approved', {
        description: 'Agreement generated and confirmation emails dispatched.',
      })
      
      currentTab.value = 'active'
      fetchData()
    }
  } catch (error) {
    console.error(error)
    if (error.response?.status === 403) {
      toast.error('Action Restricted', { description: error.response.data.message || 'You do not have permission to approve.' })
    } else {
      toast.error('Approval failed', { description: error.response?.data?.message || 'An error occurred.' })
    }
  } finally {
    isProcessing.value = false
  }
}

const initiateReject = () => {
  rejectReason.value = ''
  showViewDialog.value = false
  showRejectDialog.value = true
}

const submitReject = async () => {
  if (!selectedRequest.value || !rejectReason.value.trim()) return
  isProcessing.value = true

  try {
    const response = await api.post(`/operation-distributor/service-provider-requests/${selectedRequest.value.id}/reject`, {
      reason: rejectReason.value
    })
    
    if (response.data.success) {
      const index = requests.value.findIndex(r => r.id === selectedRequest.value.id)
      if (index !== -1) requests.value[index].status = 'rejected'
      
      showRejectDialog.value = false
      agreedToTerms.value = false
      selectedRequest.value = null
      
      toast.error('Proposal Declined', {
        description: 'The provider has been notified via email of your decision.',
      })
    }
  } catch (error) {
    console.error(error)
    if (error.response?.status === 403) {
      toast.error('Action Restricted', { description: error.response.data.message || 'You do not have permission to decline.' })
    } else {
      toast.error('Failed to reject proposal', { description: error.response?.data?.message || 'An error occurred.' })
    }
  } finally {
    isProcessing.value = false
  }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
</style>