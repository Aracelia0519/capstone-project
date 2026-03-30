<template>
  <div class="flex flex-col gap-6 p-8 min-h-screen">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Distributor Partnerships</h1>
        <p class="text-slate-500 mt-2 text-sm">
          Manage incoming partnership proposals from distributors and view your active official partners.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="fetchRequests">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4 mr-2" />
          Refresh Data
        </Button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Proposals</CardTitle>
          <Briefcase class="h-4 w-4 text-blue-600" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ pendingRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Awaiting your decision</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Active Partners</CardTitle>
          <CheckCircle2 class="h-4 w-4 text-emerald-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ activeRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Officially partnered distributors</p>
        </CardContent>
      </Card>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white p-4 rounded-lg border shadow-sm">
      <div class="flex gap-4 border-b border-transparent w-full sm:w-auto overflow-x-auto">
        <button 
          @click="activeTab = 'pending'" 
          :class="{'text-blue-600 border-b-2 border-blue-600 font-bold': activeTab === 'pending', 'text-slate-500 hover:text-slate-700': activeTab !== 'pending'}" 
          class="pb-2 text-sm font-medium transition-colors whitespace-nowrap px-2"
        >
          Pending Proposals ({{ pendingRequestsCount }})
        </button>
        <button 
          @click="activeTab = 'active'" 
          :class="{'text-emerald-600 border-b-2 border-emerald-600 font-bold': activeTab === 'active', 'text-slate-500 hover:text-slate-700': activeTab !== 'active'}" 
          class="pb-2 text-sm font-medium transition-colors whitespace-nowrap px-2"
        >
          Active Partners ({{ activeRequestsCount }})
        </button>
      </div>

      <div class="relative w-full sm:w-80">
        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500" />
        <Input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search by distributor name..." 
          class="pl-9 w-full"
        />
      </div>
    </div>

    <Card class="border shadow-sm">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="bg-slate-50/50">
              <TableHead>Distributor (Buyer)</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Date Updated</TableHead>
              <TableHead>Message / Terms</TableHead>
              <TableHead class="text-right">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="5" class="h-32 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center gap-2">
                  <RefreshCw class="h-6 w-6 animate-spin text-slate-400" />
                  <p>Loading records...</p>
                </div>
              </TableCell>
            </TableRow>
            
            <TableRow v-else-if="filteredRequests.length === 0">
              <TableCell colspan="5" class="h-32 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center gap-2">
                  <Store class="h-8 w-8 text-slate-300" />
                  <p>No records found in this category.</p>
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-for="req in filteredRequests" :key="req.id" class="hover:bg-slate-50/50">
              <TableCell>
                <div class="font-medium text-slate-900">
                  {{ req.distributor?.company_name || req.distributor?.first_name }}
                </div>
                <div class="text-xs text-slate-500">{{ req.distributor?.email }}</div>
              </TableCell>
              
              <TableCell>
                <Badge v-if="req.status === 'pending_supplier'" variant="outline" class="bg-amber-50 text-amber-700 border-amber-200">
                  <Clock class="h-3 w-3 mr-1"/> Pending
                </Badge>
                <Badge v-else-if="req.status === 'active'" variant="outline" class="bg-emerald-50 text-emerald-700 border-emerald-200">
                  <CheckCircle2 class="h-3 w-3 mr-1"/> Active
                </Badge>
              </TableCell>

              <TableCell>
                <div class="text-sm">{{ formatDate(req.updated_at) }}</div>
                <div class="text-xs text-slate-500">{{ formatTime(req.updated_at) }}</div>
              </TableCell>

              <TableCell>
                <p class="text-sm text-slate-600 line-clamp-1 max-w-[250px]" :title="req.request_message">
                  {{ req.request_message || 'Standard agreement.' }}
                </p>
              </TableCell>

              <TableCell class="text-right">
                <Button v-if="req.status === 'pending_supplier'" variant="outline" size="sm" @click="viewRequest(req)">
                  Review Proposal
                </Button>
                <Button v-else-if="req.status === 'active'" variant="secondary" size="sm" class="bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100" @click="viewOfficialDocument(req)">
                  <FileBadge class="h-4 w-4 mr-2" /> View Official Document
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Dialog :open="showViewDialog" @update:open="showViewDialog = $event">
      <DialogContent class="sm:max-w-[600px] p-0 overflow-hidden">
        <div class="bg-slate-900 p-6 text-white">
          <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-lg bg-white/10 flex items-center justify-center backdrop-blur-sm">
              <Store class="h-6 w-6 text-blue-400" />
            </div>
            <div>
              <DialogTitle class="text-xl font-bold">Incoming Partnership Proposal</DialogTitle>
              <DialogDescription class="text-slate-400 mt-1">
                Distributor wants to source products from your catalog.
              </DialogDescription>
            </div>
          </div>
        </div>

        <div class="p-6 space-y-6" v-if="selectedRequest">
          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Distributor Profile</h4>
            <div class="space-y-3 bg-slate-50 p-4 rounded-lg border border-slate-100">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <Store class="h-4 w-4 text-slate-400" />
                  <span class="text-sm font-bold text-slate-900">
                    {{ selectedRequest.distributor?.company_name || selectedRequest.distributor?.first_name }}
                  </span>
                </div>
                <Badge variant="secondary" class="bg-blue-50 text-blue-700">Verified Buyer</Badge>
              </div>
              
              <div class="grid grid-cols-2 gap-4 mt-2 pt-2 border-t border-slate-200">
                <div class="flex items-center gap-2">
                  <Mail class="h-3.5 w-3.5 text-slate-400" />
                  <span class="text-xs text-slate-600 truncate" :title="selectedRequest.distributor?.email">{{ selectedRequest.distributor?.email }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <Phone class="h-3.5 w-3.5 text-slate-400" />
                  <span class="text-xs text-slate-600">{{ selectedRequest.distributor?.phone || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Proposal Notes</h4>
            <div class="bg-white border rounded-lg p-4 text-sm text-slate-700 leading-relaxed shadow-sm">
              {{ selectedRequest.request_message || 'The distributor is proposing a standard B2B supply partnership.' }}
            </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 border-t flex justify-end gap-3">
          <Button variant="outline" @click="showViewDialog = false">Close</Button>
          <Button variant="destructive" @click="initiateReject" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200">
            <XCircle class="h-4 w-4 mr-2" /> Decline
          </Button>
          <Button class="bg-blue-600 hover:bg-blue-700 text-white" @click="initiateApprove">
            <CheckCircle2 class="h-4 w-4 mr-2" /> Proceed to Sign & Accept
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showRejectDialog" @update:open="showRejectDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-600 flex items-center gap-2">
            <AlertCircle class="h-5 w-5" /> Decline Proposal
          </AlertDialogTitle>
          <AlertDialogDescription>
            You are rejecting this distributor's request to partner. They will be notified via email.
          </AlertDialogDescription>
        </AlertDialogHeader>
        
        <div class="py-4">
          <label class="text-sm font-medium text-slate-700 mb-1 block">Reason for Declining (Optional)</label>
          <textarea 
            v-model="rejectReason"
            class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-red-500 focus:ring-red-500 min-h-[80px]"
            placeholder="E.g., Currently at maximum capacity for new distributors..."
          ></textarea>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isProcessing">Cancel</AlertDialogCancel>
          <Button variant="destructive" @click="submitReject" :disabled="isProcessing">
            <span v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin">◌</span>
            Confirm Decline
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showSignatureDialog" @update:open="showSignatureDialog = $event">
      <DialogContent class="max-w-5xl h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 border-b bg-white shrink-0">
          <DialogTitle class="flex items-center gap-2 text-slate-800">
            <FileText class="h-5 w-5 text-blue-600" />
            Accept Partnership Agreement - {{ selectedRequest?.distributor?.company_name || selectedRequest?.distributor?.first_name }}
          </DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-4 relative bg-slate-100">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
            <div v-else class="flex flex-col h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>No digital agreement document was found for this request.</p>
            </div>
        </div>

        <div class="p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10">
            <div class="flex flex-col gap-4 max-w-4xl mx-auto">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-4 w-4 text-blue-600" />
                            Counter-Sign to Activate Partnership
                        </label>
                        <Button variant="ghost" size="sm" class="h-7 text-xs text-slate-500 hover:text-red-600" @click="clearSignature">
                            Clear Pad
                        </Button>
                    </div>
                    
                    <div class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 overflow-hidden relative" ref="canvasContainer">
                        <canvas 
                            ref="signatureCanvas" 
                            class="w-full h-[160px] sm:h-[180px] cursor-crosshair touch-none"
                            @mousedown="startDrawing"
                            @mousemove="draw"
                            @mouseup="stopDrawing"
                            @mouseleave="stopDrawing"
                            @touchstart.prevent="startDrawingTouch"
                            @touchmove.prevent="drawTouch"
                            @touchend.prevent="stopDrawing"
                        ></canvas>
                        
                        <div v-if="!hasDrawn" class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-40">
                            <span class="text-slate-400 font-medium select-none">Supplier Sign Here</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2 text-center">
                        By signing and accepting, you finalize the agreement and the distributor will gain access to your product catalog immediately.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <Button variant="outline" @click="showSignatureDialog = false">Cancel</Button>
                    <Button 
                      :disabled="!hasDrawn || isProcessing" 
                      @click="submitApprove" 
                      class="bg-blue-600 text-white hover:bg-blue-700 min-w-[140px]"
                    >
                        <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isProcessing ? 'Activating...' : 'Sign & Activate' }}
                    </Button>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showOfficialDocDialog" @update:open="showOfficialDocDialog = $event">
      <DialogContent class="max-w-5xl h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-100 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex items-center gap-2 text-slate-800">
            <FileBadge class="h-5 w-5 text-emerald-600" />
            Official Partnership Agreement - {{ selectedRequest?.distributor?.company_name || selectedRequest?.distributor?.first_name }}
          </DialogTitle>
          <DialogDescription class="text-xs text-slate-500 mt-1">
            This is a legally binding, digitally signed document verifying your active partnership.
          </DialogDescription>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-4 relative">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-300"
            ></iframe>
            <div v-else class="flex flex-col h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>Digital agreement document not available.</p>
            </div>
        </div>

        <div class="p-6 border-t bg-white shrink-0 z-10 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-4">Official Cryptographic Signatures</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-4xl mx-auto">
                
                <div class="flex flex-col items-center justify-center border border-slate-200 p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 border border-indigo-200">Distributor (Buyer)</Badge>
                    <div class="h-20 w-full flex items-center justify-center mt-2">
                        <img 
                          v-if="selectedRequest?.distributor_signature_url" 
                          :src="selectedRequest.distributor_signature_url" 
                          alt="Distributor Signature" 
                          class="max-h-full object-contain mix-blend-multiply opacity-90"
                        />
                        <span v-else class="text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-3 w-3/4 bg-slate-300" />
                    <p class="text-sm font-bold text-slate-800">{{ selectedRequest?.distributor?.company_name || selectedRequest?.distributor?.first_name }}</p>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle2 class="h-3 w-3 text-emerald-500" /> 
                        Signed: {{ formatDateTime(selectedRequest?.distributor_signed_at) }}
                    </p>
                </div>

                <div class="flex flex-col items-center justify-center border border-slate-200 p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-blue-100 text-blue-700 border border-blue-200">Supplier (Seller)</Badge>
                    <div class="h-20 w-full flex items-center justify-center mt-2">
                        <img 
                          v-if="selectedRequest?.supplier_signature_url" 
                          :src="selectedRequest.supplier_signature_url" 
                          alt="Supplier Signature" 
                          class="max-h-full object-contain mix-blend-multiply opacity-90"
                        />
                        <span v-else class="text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-3 w-3/4 bg-slate-300" />
                    <p class="text-sm font-bold text-slate-800">Your Business</p>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle2 class="h-3 w-3 text-emerald-500" /> 
                        Signed: {{ formatDateTime(selectedRequest?.supplier_signed_at) }}
                    </p>
                </div>

            </div>
            <div class="flex justify-end mt-4">
                <Button variant="outline" @click="showOfficialDocDialog = false">Close Document</Button>
            </div>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import { 
  Store,
  Briefcase,
  CheckCircle2,
  XCircle,
  AlertCircle,
  MapPin,
  Phone,
  Mail,
  RefreshCw,
  Search,
  PenTool,
  FileText,
  FileBadge,
  FileX,
  Loader2,
  Clock
} from 'lucide-vue-next'

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  AlertDialog,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

// State
const requests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const isProcessing = ref(false)
const activeTab = ref('pending') // Tabs: 'pending' or 'active'

const selectedRequest = ref(null)
const showViewDialog = ref(false)
const showOfficialDocDialog = ref(false)

// Reject State
const showRejectDialog = ref(false)
const rejectReason = ref('')

// Signature State
const showSignatureDialog = ref(false)
const hasDrawn = ref(false)

// Canvas State
const signatureCanvas = ref(null)
const canvasContainer = ref(null)
let ctx = null
let isDrawing = false

// --- Canvas Logic ---
const initCanvas = () => {
  if (!signatureCanvas.value || !canvasContainer.value) return
  
  signatureCanvas.value.width = canvasContainer.value.clientWidth
  signatureCanvas.value.height = canvasContainer.value.clientHeight
  
  ctx = signatureCanvas.value.getContext('2d')
  ctx.lineWidth = 3
  ctx.lineCap = 'round'
  ctx.lineJoin = 'round'
  ctx.strokeStyle = '#1e3a8a' // Dark blue ink for supplier
}

const getCoordinates = (e) => {
  if (!signatureCanvas.value) return { x: 0, y: 0 }
  const rect = signatureCanvas.value.getBoundingClientRect()
  
  const clientX = e.touches && e.touches.length > 0 ? e.touches[0].clientX : e.clientX
  const clientY = e.touches && e.touches.length > 0 ? e.touches[0].clientY : e.clientY
  
  return {
    x: clientX - rect.left,
    y: clientY - rect.top
  }
}

const startDrawing = (e) => {
  isDrawing = true
  hasDrawn.value = true
  const coords = getCoordinates(e)
  ctx.beginPath()
  ctx.moveTo(coords.x, coords.y)
}

const draw = (e) => {
  if (!isDrawing) return
  const coords = getCoordinates(e)
  ctx.lineTo(coords.x, coords.y)
  ctx.stroke()
}

const stopDrawing = () => {
  if (!isDrawing) return
  isDrawing = false
  ctx.closePath()
}

const startDrawingTouch = (e) => startDrawing(e)
const drawTouch = (e) => draw(e)

const clearSignature = () => {
  if (!ctx || !signatureCanvas.value) return
  ctx.clearRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height)
  hasDrawn.value = false
}

watch(showSignatureDialog, async (newVal) => {
  if (newVal) {
    hasDrawn.value = false
    await nextTick()
    setTimeout(initCanvas, 50) 
  }
})

// --- Data Fetching ---
const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/supplier/distributor-requests')
    if (response.data.success) {
      requests.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch', error)
    toast.error('Failed to load records')
  } finally {
    loading.value = false
  }
}

const pendingRequestsCount = computed(() => requests.value.filter(r => r.status === 'pending_supplier').length)
const activeRequestsCount = computed(() => requests.value.filter(r => r.status === 'active').length)

const filteredRequests = computed(() => {
  let list = requests.value.filter(req => req.status === (activeTab.value === 'pending' ? 'pending_supplier' : 'active'))
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(req => {
      const compName = req.distributor?.company_name?.toLowerCase() || ''
      const distName = req.distributor?.first_name?.toLowerCase() || ''
      return compName.includes(q) || distName.includes(q)
    })
  }
  
  return list
})

const viewRequest = (req) => {
  selectedRequest.value = req
  showViewDialog.value = true
}

const viewOfficialDocument = (req) => {
  selectedRequest.value = req
  showOfficialDocDialog.value = true
}

// --- Formatting Helpers ---
const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleDateString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric'
  })
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('en-PH', {
    hour: '2-digit', minute: '2-digit'
  })
}

const formatDateTime = (dateStr) => {
  if (!dateStr) return 'N/A'
  return new Date(dateStr).toLocaleString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'
  })
}

// --- Action Flows ---

// 1. Approve & Sign Flow
const initiateApprove = () => {
  showViewDialog.value = false
  showSignatureDialog.value = true
}

const submitApprove = async () => {
  if (!selectedRequest.value || !hasDrawn.value || !signatureCanvas.value) return
  isProcessing.value = true

  const signatureBase64 = signatureCanvas.value.toDataURL('image/png')

  try {
    const response = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/approve`, {
        signature_image: signatureBase64
    })
    
    if (response.data.success) {
        // Find index and update it locally to instantly move it to Active Partners tab
        const index = requests.value.findIndex(r => r.id === selectedRequest.value.id)
        if (index !== -1) {
            requests.value[index].status = 'active'
            requests.value[index].supplier_signed_at = new Date().toISOString()
            requests.value[index].supplier_signature_url = signatureBase64
        }
        
        showSignatureDialog.value = false
        selectedRequest.value = null
        activeTab.value = 'active' // Auto switch tab to show the new active partner
        
        toast.success('Partnership Activated', {
            description: 'Distributor officially added to active partners. Agreement filed.',
            class: 'bg-emerald-50 border-emerald-200 text-emerald-800'
        })
    }
  } catch (error) {
    console.error(error)
    toast.error('Approval Failed', {
        description: error.response?.data?.message || 'A server error occurred.'
    })
  } finally {
    isProcessing.value = false
  }
}

// 2. Reject Workflow
const initiateReject = () => {
  rejectReason.value = ''
  showViewDialog.value = false
  showRejectDialog.value = true
}

const submitReject = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true

  try {
    const response = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/reject`, {
        reason: rejectReason.value
    })
    
    if (response.data.success) {
        requests.value = requests.value.filter(r => r.id !== selectedRequest.value.id)
        showRejectDialog.value = false
        selectedRequest.value = null
        
        toast.info('Proposal Declined', {
            description: 'The distributor has been notified via email.',
        })
    }
  } catch (error) {
    console.error(error)
    toast.error('Action Failed', {
        description: error.response?.data?.message || 'Server error.'
    })
  } finally {
    isProcessing.value = false
  }
}

onMounted(() => {
  fetchRequests()
})
</script>