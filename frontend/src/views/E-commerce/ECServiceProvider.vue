<template>
  <div class="flex flex-col gap-6 p-4 sm:p-8 min-h-screen text-slate-200 relative">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-white flex items-center gap-3">
          <div class="p-2 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl shadow-lg shrink-0">
            <Users class="w-6 h-6 text-white" />
          </div>
          Provider Contracts
        </h1>
        <p class="text-slate-400 mt-2 text-sm">Review partnership proposals and negotiate contract durations with Service Providers.</p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="fetchData" class="bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4 mr-2" /> Refresh
        </Button>
      </div>
    </div>

    <Card class="bg-slate-900 border-slate-800 shadow-lg">
      <CardHeader>
        <div class="flex flex-col sm:flex-row justify-between gap-4">
          <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800 w-full sm:w-auto">
            <button @click="currentTab = 'pending'" :class="currentTab === 'pending' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'" class="px-5 py-2 rounded-lg text-sm font-medium">Action Needed</button>
            <button @click="currentTab = 'negotiating'" :class="currentTab === 'negotiating' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'" class="px-5 py-2 rounded-lg text-sm font-medium">Awaiting SP</button>
            <button @click="currentTab = 'active'" :class="currentTab === 'active' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'" class="px-5 py-2 rounded-lg text-sm font-medium">Active Partners</button>
            <button @click="currentTab = 'expired'" :class="currentTab === 'expired' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200'" class="px-5 py-2 rounded-lg text-sm font-medium">Expired Contracts</button>
          </div>
          
          <div class="relative w-full sm:w-72">
            <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-500" />
            <Input v-model="searchQuery" placeholder="Search provider name or email..." class="pl-9 bg-slate-950 border-slate-700 text-white placeholder:text-slate-500 focus-visible:ring-indigo-500" />
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
          <h3 class="text-lg font-semibold text-white">No Records Found</h3>
          <p class="text-slate-400 text-sm max-w-sm mt-1">There are currently no records matching your criteria in this tab.</p>
        </div>

        <div v-else class="rounded-xl border border-slate-800 overflow-hidden bg-slate-950/50 min-w-[700px]">
          <Table>
            <TableHeader class="bg-slate-800/50 border-b border-slate-800">
              <TableRow class="hover:bg-transparent border-slate-800">
                <TableHead class="w-[300px] text-slate-300">Service Provider</TableHead>
                <TableHead class="text-slate-300">Location</TableHead>
                <TableHead class="text-slate-300">End Date</TableHead>
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
                
                <TableCell><span class="text-sm text-slate-400">{{ req.location }}</span></TableCell>

                <TableCell>
                  <div class="flex items-center gap-2 text-sm" :class="isNearExpiry(req.contract_end_date) ? 'text-red-400' : 'text-slate-400'">
                    <Calendar class="w-3.5 h-3.5 text-slate-500" />
                    {{ formatDate(req.status === 'active' || req.status === 'expired' ? req.contract_end_date : req.proposed_end_date) }}
                  </div>
                </TableCell>

                <TableCell>
                  <Badge v-if="req.status === 'active'" variant="secondary" class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</Badge>
                  <Badge v-else-if="req.status === 'expired'" variant="secondary" class="bg-red-500/10 text-red-400 border border-red-500/20">Expired</Badge>
                  <Badge v-else-if="req.status === 'pending' || req.status === 'negotiating'" variant="secondary" class="bg-amber-500/10 text-amber-400 border border-amber-500/20">Negotiating</Badge>
                </TableCell>

                <TableCell class="text-right">
                  <Button v-if="req.status === 'pending' || (req.status === 'negotiating' && req.last_proposed_by === 'service_provider')" variant="outline" size="sm" class="h-8 gap-2 bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white" @click="openViewDialog(req)">
                    <Eye class="h-4 w-4" /> Review
                  </Button>
                  
                  <div v-else-if="req.status === 'active'" class="flex items-center justify-end gap-2">
                    <Button variant="outline" size="sm" class="h-8 gap-2 bg-indigo-500/10 border-indigo-500/30 text-indigo-400 hover:bg-indigo-500/20 hover:text-indigo-300" @click="openAgreementDialog(req)">
                      <FileText class="h-4 w-4" /> Agreement
                    </Button>
                  </div>
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
              <DialogTitle class="text-white text-left">Partnership Proposal Review</DialogTitle>
              <DialogDescription class="text-slate-400 text-left mt-1">Review the provider's details and finalize the contract duration.</DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="overflow-y-auto custom-scrollbar p-5 flex-1" v-if="selectedRequest">
          <div class="grid gap-6">
            <div class="rounded-xl border border-slate-800 bg-slate-950/50 p-4 grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                <div class="space-y-1"><span class="text-xs font-semibold text-slate-500 uppercase">Provider Name</span><p class="font-medium text-slate-200 text-base">{{ selectedRequest.provider_name }}</p></div>
                <div class="space-y-1"><span class="text-xs font-semibold text-slate-500 uppercase">Contact Number</span><p class="text-slate-400">{{ selectedRequest.phone }}</p></div>
                <div class="space-y-1 sm:col-span-2"><span class="text-xs font-semibold text-slate-500 uppercase">Email Address</span><p class="text-slate-400">{{ selectedRequest.email }}</p></div>
            </div>

            <div class="bg-amber-500/10 border border-amber-500/20 p-4 rounded-xl">
               <span class="text-sm font-semibold text-amber-400">Proposed Contract End Date</span>
               <p class="text-slate-300 text-lg"><strong class="text-white">{{ formatDate(selectedRequest.proposed_end_date) }}</strong></p>
            </div>

            <div v-if="negotiationMode === 'counter'">
              <label class="block text-sm font-semibold text-slate-300 mb-2">Counter Proposal End Date <span class="text-red-400">*</span></label>
              <input type="date" v-model="counterDate" :min="minAllowedDate" class="w-full px-4 py-3 bg-slate-950 border border-slate-700 rounded-xl text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500" />
              <p class="text-[10px] text-slate-500 mt-1">Contracts must be at least 1 month in duration.</p>
            </div>

            <div class="space-y-3">
              <h4 class="text-sm font-medium leading-none text-slate-200">Message from Provider</h4>
              <div class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-sm text-slate-400 italic">"{{ selectedRequest.message || 'No formal message provided.' }}"</div>
            </div>

            <!-- Terms and Conditions block (visible in both view and counter modes) -->
            <div class="space-y-3 pt-4 border-t border-slate-800">
              <label class="block text-sm font-semibold text-slate-300">Terms and Conditions</label>
              <div class="bg-slate-950 border border-slate-700 p-4 rounded-xl text-sm text-slate-400 h-40 overflow-y-auto custom-scrollbar shadow-inner leading-relaxed">
                <h4 class="font-bold text-slate-200 mb-2">1. Authorization & Access</h4>
                <p class="mb-4">The Service Provider is authorized to access the Distributor's wholesale catalog, view pricing, and submit procurement requests upon final approval of this digital agreement.</p>
                <h4 class="font-bold text-slate-200 mb-2">2. Procurement Standards</h4>
                <p class="mb-4">All materials procured are subject to the Distributor's standard quality assurance protocols. Pricing tiers are exclusive and confidential.</p>
                <h4 class="font-bold text-slate-200 mb-2">3. Digital Signature Binding</h4>
                <p>By affixing your digital signature below, you certify that you are an authorized representative of your entity and agree to enter into a legally binding commercial partnership.</p>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between">
                 <label class="block text-sm font-medium text-slate-200">Your Signature to {{ negotiationMode === 'counter' ? 'Counter' : 'Accept' }} <span class="text-red-400">*</span></label>
                 <div class="flex items-center gap-2">
                   <button @click="signatureMethod = 'draw'" :class="signatureMethod === 'draw' ? 'text-indigo-400' : 'text-slate-500'" class="text-xs font-medium">Draw</button>
                   <span class="text-slate-600">|</span>
                   <button @click="signatureMethod = 'upload'" :class="signatureMethod === 'upload' ? 'text-indigo-400' : 'text-slate-500'" class="text-xs font-medium">Upload</button>
                 </div>
              </div>

              <div v-if="signatureMethod === 'draw'" class="bg-white rounded-xl border border-slate-700 overflow-hidden relative shadow-inner">
                <div class="flex justify-end p-1 bg-slate-100 border-b border-slate-300"><button @click="clearSignature" class="text-[10px] text-red-500 font-bold uppercase px-2">Clear</button></div>
                <canvas ref="signaturePad" width="600" height="150" class="w-full h-[150px] touch-none cursor-crosshair"
                  @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing" 
                  @touchstart.prevent="startDrawingTouch" @touchmove.prevent="drawTouch" @touchend.prevent="stopDrawing"></canvas>
                <div v-if="!hasSignature" class="absolute inset-0 pointer-events-none flex items-center justify-center text-slate-300 font-medium tracking-wide pt-6">Sign here</div>
              </div>

              <div v-else class="border border-dashed border-slate-600 rounded-xl bg-slate-900/50 p-6 flex flex-col items-center justify-center relative min-h-[150px]">
                <input type="file" accept="image/png, image/jpeg" @change="handleSignatureUpload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" />
                <div v-if="!uploadedSignature" class="text-center text-slate-400">
                  <svg class="w-8 h-8 mx-auto mb-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>
                  <span class="text-sm font-medium">Click to upload signature</span>
                </div>
                <img v-else :src="uploadedSignature" class="max-h-[100px] object-contain mb-2" />
              </div>
            </div>

            <!-- Checkbox text changes based on mode -->
            <label class="flex items-start gap-3 mt-2 cursor-pointer group">
              <input type="checkbox" v-model="agreedToTerms" class="mt-0.5 w-4 h-4 rounded bg-slate-900 border-slate-500 checked:bg-indigo-500" />
              <span class="text-sm text-slate-300 group-hover:text-slate-200">
                I verify my signature to {{ negotiationMode === 'counter' ? 'propose a new date' : 'finalize this contract' }}.
              </span>
            </label>
          </div>
        </div>

        <DialogFooter class="px-5 py-4 border-t border-slate-800 bg-slate-900 flex justify-between">
           <Button variant="ghost" class="text-slate-400 hover:text-white" @click="closeViewDialog">Cancel</Button>
           <div class="flex gap-2">
              <Button v-if="negotiationMode === 'view'" variant="outline" class="border-red-900/50 bg-red-950/20 text-red-400 hover:bg-red-900/40" @click="requirePermission('approve', initiateReject)">
                <X class="mr-2 h-4 w-4" /> Decline
              </Button>
              <Button v-if="negotiationMode === 'view'" variant="outline" class="border-amber-500 text-amber-500 hover:bg-amber-500/10" @click="negotiationMode = 'counter'">
                Counter Proposal
              </Button>
              <Button v-if="negotiationMode === 'counter'" class="bg-amber-600 hover:bg-amber-500 text-white" @click="submitCounter" :disabled="!isFormValid || isProcessing">
                <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Send Counter
              </Button>
              <Button v-if="negotiationMode === 'view'" class="bg-indigo-600 hover:bg-indigo-500 text-white" @click="requirePermission('approve', submitAccept)" :disabled="!isFormValid || isProcessing">
                <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Accept & Finalize
              </Button>
           </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog :open="showRejectDialog" @update:open="closeRejectDialog">
      <DialogContent class="sm:max-w-[425px] w-[95vw] rounded-xl bg-slate-900 border-slate-800 text-slate-200">
        <DialogHeader>
          <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2 text-left">
            <div class="p-2 bg-red-500/10 border border-red-500/20 rounded-full w-fit">
              <ShieldAlert class="h-5 w-5 text-red-400" />
            </div>
            <div>
              <DialogTitle class="text-white">Decline Proposal</DialogTitle>
              <DialogDescription class="text-slate-400 mt-1">Provide a reason for declining this request.</DialogDescription>
            </div>
          </div>
        </DialogHeader>
        <div class="py-2 space-y-4">
          <div class="space-y-2 text-left">
            <Label for="reason" class="text-xs font-semibold text-slate-400 uppercase">Rejection Reason</Label>
            <Textarea id="reason" v-model="rejectReason" placeholder="e.g., Not accepting new partners." class="bg-slate-950 border-slate-700 text-white focus-visible:ring-indigo-500" rows="4" />
          </div>
        </div>
        <DialogFooter class="flex gap-2 mt-4">
          <Button variant="outline" @click="closeRejectDialog" class="bg-transparent border-slate-700 text-slate-300">Back</Button>
          <Button variant="destructive" @click="submitReject" :disabled="isProcessing || !rejectReason.trim()" class="bg-red-600 text-white">Decline Request</Button>
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
              <DialogDescription class="text-slate-400 text-left mt-1">Signed document containing terms and dates.</DialogDescription>
            </div>
          </div>
        </DialogHeader>
        <div class="flex-1 bg-slate-950 p-4 sm:p-6 overflow-hidden">
          <div class="w-full h-full bg-white rounded-lg overflow-hidden border border-slate-700 relative">
             <div v-if="docLoading" class="absolute inset-0 flex items-center justify-center bg-white/80 z-10"><Loader2 class="w-8 h-8 animate-spin text-indigo-500" /></div>
            <iframe v-if="documentUrlToView" :src="documentUrlToView" class="w-full h-full border-0" @load="docLoading = false"></iframe>
          </div>
        </div>
        <DialogFooter class="px-5 py-4 border-t border-slate-800 bg-slate-900 shrink-0 flex justify-between">
          <Button variant="ghost" class="text-slate-400" @click="showAgreementDialog = false">Close Viewer</Button>
          <Button class="bg-indigo-600 text-white" @click="downloadAgreement(documentUrlToView)"><Download class="mr-2 h-4 w-4" /> Download Document</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import echo from '@/utils/websocket.js' 

import { Loader2, RefreshCw, Search, Eye, Check, X, Inbox, Users, CalendarRange, CheckCircle2, Calendar, Briefcase, UserCheck, FileText, Download, FileCheck, FileX, ShieldAlert } from 'lucide-vue-next'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'

const requests = ref([])
const loading = ref(false)
const searchQuery = ref('')
const isProcessing = ref(false)
const agreedToTerms = ref(false)
const currentTab = ref('pending') 
const currentUserId = ref(null)
let echoInitialized = false

const showViewDialog = ref(false)
const showRejectDialog = ref(false)
const showAgreementDialog = ref(false) 

const selectedRequest = ref(null)
const rejectReason = ref('')
const negotiationMode = ref('view')
const counterDate = ref('')
const documentUrlToView = ref('')
const docLoading = ref(true)

const signatureMethod = ref('draw')
const signaturePad = ref(null)
const isDrawing = ref(false)
const ctx = ref(null)
const hasSignature = ref(false)
const uploadedSignature = ref(null)

const permissions = ref({ can_view: false, can_manage: false, can_approve: false })

const requirePermission = (action, callback) => {
  if (!permissions.value[`can_${action}`]) {
    toast.error(`Access Denied`, { description: `You do not have permission to ${action} requests.`, style: { background: '#0f172a', color: '#ffffff' } });
    return;
  }
  if (callback) callback();
}

const minAllowedDate = computed(() => {
  const min = new Date();
  min.setMonth(min.getMonth() + 1);
  return min.toISOString().split('T')[0];
})

const isSignatureValid = computed(() => signatureMethod.value === 'draw' ? hasSignature.value : !!uploadedSignature.value);

const isFormValid = computed(() => {
  const isDateValid = negotiationMode.value === 'view' ? true : (counterDate.value && counterDate.value >= minAllowedDate.value);
  return agreedToTerms.value && isSignatureValid.value && isDateValid;
})

const filteredRequests = computed(() => {
  let list = []
  if (currentTab.value === 'pending') {
    list = requests.value.filter(req => (req.status === 'pending' || req.status === 'negotiating') && req.last_proposed_by === 'service_provider')
  } else if (currentTab.value === 'negotiating') {
    list = requests.value.filter(req => req.status === 'negotiating' && req.last_proposed_by === 'distributor')
  } else {
    list = requests.value.filter(req => req.status === currentTab.value)
  }
  
  if (!searchQuery.value) return list
  const query = searchQuery.value.toLowerCase()
  return list.filter(req => req.provider_name.toLowerCase().includes(query) || req.email.toLowerCase().includes(query))
})

const formatDate = (dateString) => dateString ? new Date(dateString).toLocaleDateString() : 'N/A'
const isNearExpiry = (dateString) => dateString && (new Date(dateString) - new Date()) / (1000 * 60 * 60 * 24) <= 30 && (new Date(dateString) - new Date()) > 0

const fetchData = async () => {
  loading.value = true
  try {
    const response = await api.get('/operation-distributor/service-provider-requests')
    if (response.data.success) {
      requests.value = response.data.data
      currentUserId.value = response.data.current_user_id
      if (response.data.permissions) permissions.value = response.data.permissions
      initializeEcho()
    }
  } catch (error) { toast.error('Failed to load data') } 
  finally { loading.value = false }
}

const fetchDataSilently = async () => {
    try {
        const response = await api.get('/operation-distributor/service-provider-requests')
        if (response.data.success) requests.value = response.data.data
    } catch (error) { console.error('Failed to silently fetch updates') }
}

const initializeEcho = () => {
    if (!echoInitialized && currentUserId.value && echo) {
        echo.private(`partnership.user.${currentUserId.value}`).listen('.PartnershipStatusUpdated', () => { fetchDataSilently() });
        echoInitialized = true;
    }
}

onMounted(() => { fetchData() })
onBeforeUnmount(() => { if (currentUserId.value && echo) echo.leave(`partnership.user.${currentUserId.value}`) })

const openViewDialog = (req) => {
  selectedRequest.value = req
  negotiationMode.value = 'view'
  agreedToTerms.value = false
  hasSignature.value = false
  uploadedSignature.value = null
  counterDate.value = minAllowedDate.value
  showViewDialog.value = true
  nextTick(() => { if(signatureMethod.value === 'draw') initSignaturePad() })
}

const closeViewDialog = () => { showViewDialog.value = false }

const openAgreementDialog = (req) => {
  documentUrlToView.value = req.agreement_url
  docLoading.value = true
  showAgreementDialog.value = true
}

const downloadAgreement = (url) => {
  if (!url) return
  window.open(url, '_blank')
}

const initiateReject = () => { rejectReason.value = ''; showViewDialog.value = false; showRejectDialog.value = true }
const closeRejectDialog = () => { showRejectDialog.value = false }

// Signature Pad
const initSignaturePad = () => {
  if (!signaturePad.value) return
  ctx.value = signaturePad.value.getContext('2d')
  ctx.value.lineWidth = 2.5; ctx.value.strokeStyle = '#000'
  clearSignature()
}
const startDrawing = (e) => { isDrawing.value = true; hasSignature.value = true; draw(e) }
const draw = (e) => {
  if (!isDrawing.value || !ctx.value) return
  const rect = signaturePad.value.getBoundingClientRect()
  ctx.value.lineTo(e.clientX - rect.left, e.clientY - rect.top)
  ctx.value.stroke(); ctx.value.beginPath(); ctx.value.moveTo(e.clientX - rect.left, e.clientY - rect.top)
}
const stopDrawing = () => { isDrawing.value = false; if (ctx.value) ctx.value.beginPath() }
const startDrawingTouch = (e) => {
  const touch = e.touches[0]; const rect = signaturePad.value.getBoundingClientRect()
  isDrawing.value = true; hasSignature.value = true
  ctx.value.beginPath(); ctx.value.moveTo(touch.clientX - rect.left, touch.clientY - rect.top)
}
const drawTouch = (e) => {
  if (!isDrawing.value || !ctx.value) return
  const touch = e.touches[0]; const rect = signaturePad.value.getBoundingClientRect()
  ctx.value.lineTo(touch.clientX - rect.left, touch.clientY - rect.top)
  ctx.value.stroke(); ctx.value.beginPath(); ctx.value.moveTo(touch.clientX - rect.left, touch.clientY - rect.top)
}
const clearSignature = () => {
  if (!ctx.value || !signaturePad.value) return
  ctx.value.clearRect(0, 0, signaturePad.value.width, signaturePad.value.height)
  hasSignature.value = false; uploadedSignature.value = null; ctx.value.beginPath()
}
const handleSignatureUpload = (e) => {
  const f = e.target.files[0];
  if(f && (f.type === 'image/png' || f.type === 'image/jpeg')) {
    const r = new FileReader(); r.onload = ev => uploadedSignature.value = ev.target.result; r.readAsDataURL(f);
  } else toast.error("Invalid file format.");
}
const getFinalSignature = () => signatureMethod.value === 'draw' ? signaturePad.value.toDataURL('image/png') : uploadedSignature.value;

const submitAccept = async () => {
  if (!isFormValid.value || !selectedRequest.value) return
  isProcessing.value = true

  try {
    const response = await api.post(`/operation-distributor/service-provider-requests/${selectedRequest.value.id}/approve`, { signature: getFinalSignature() })
    if (response.data.success) {
      toast.success('Contract Finalized', { description: 'The partnership is now officially active.' })
      closeViewDialog()
      fetchData()
    }
  } catch (error) { toast.error('Acceptance failed') } finally { isProcessing.value = false }
}

const submitCounter = async () => {
  if (!isFormValid.value || !selectedRequest.value) return
  isProcessing.value = true

  try {
    const response = await api.post(`/operation-distributor/service-provider-requests/${selectedRequest.value.id}/counter`, { 
      signature: getFinalSignature(), proposed_end_date: counterDate.value 
    })
    if (response.data.success) {
      toast.success('Counter Proposal Sent', { description: 'Sent back to the service provider for review.' })
      closeViewDialog()
      fetchData()
    }
  } catch (error) { toast.error('Counter failed') } finally { isProcessing.value = false }
}

const submitReject = async () => {
  if (!selectedRequest.value || !rejectReason.value.trim()) return
  isProcessing.value = true

  try {
    const response = await api.post(`/operation-distributor/service-provider-requests/${selectedRequest.value.id}/reject`, { reason: rejectReason.value })
    if (response.data.success) {
      toast.error('Proposal Declined')
      closeRejectDialog()
      fetchData()
    }
  } catch (error) { toast.error('Failed to reject proposal') } finally { isProcessing.value = false }
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
</style>