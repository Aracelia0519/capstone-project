<template>
  <div class="flex flex-col gap-6 p-8 min-h-screen">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Partnership Approvals</h1>
        <p class="text-slate-500 mt-2 text-sm">
          Overview of internal partnership requests awaiting your authorization.
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
          <CardTitle class="text-sm font-medium">Pending Approvals</CardTitle>
          <Clock class="h-4 w-4 text-orange-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ requests.length }}</div>
          <p class="text-xs text-muted-foreground">Requires immediate attention</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Recently Reviewed</CardTitle>
          <CheckCircle2 class="h-4 w-4 text-emerald-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">0</div>
          <p class="text-xs text-muted-foreground">In the last 7 days</p>
        </CardContent>
      </Card>
    </div>

    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white p-4 rounded-lg border shadow-sm">
      <div class="relative w-full sm:w-96">
        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-slate-500" />
        <Input 
          v-model="searchQuery"
          type="text" 
          placeholder="Search by supplier or requested by..." 
          class="pl-9 w-full"
        />
      </div>
    </div>

    <Card class="border shadow-sm">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="bg-slate-50/50">
              <TableHead>Supplier</TableHead>
              <TableHead>Requested By</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Message</TableHead>
              <TableHead class="text-right">Action</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="5" class="h-32 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center gap-2">
                  <RefreshCw class="h-6 w-6 animate-spin text-slate-400" />
                  <p>Loading requests...</p>
                </div>
              </TableCell>
            </TableRow>
            
            <TableRow v-else-if="filteredRequests.length === 0">
              <TableCell colspan="5" class="h-32 text-center text-slate-500">
                <div class="flex flex-col items-center justify-center gap-2">
                  <Building2 class="h-8 w-8 text-slate-300" />
                  <p>No pending partnership requests found.</p>
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-for="req in filteredRequests" :key="req.id" class="hover:bg-slate-50/50">
              <TableCell>
                <div class="font-medium text-slate-900">
                  {{ req.supplier?.supplier_requirements?.company_name || req.supplier?.first_name }}
                </div>
                <div class="text-xs text-slate-500">{{ req.supplier?.email }}</div>
              </TableCell>
              
              <TableCell>
                <div class="flex items-center gap-2">
                  <Badge variant="outline" class="bg-indigo-50 text-indigo-700 border-indigo-200">
                    {{ req.creator?.role?.replace('_', ' ') }}
                  </Badge>
                  <span class="text-sm font-medium">{{ req.creator?.first_name }} {{ req.creator?.last_name }}</span>
                </div>
              </TableCell>

              <TableCell>
                <div class="text-sm">{{ formatDate(req.created_at) }}</div>
                <div class="text-xs text-slate-500">{{ formatTime(req.created_at) }}</div>
              </TableCell>

              <TableCell>
                <p class="text-sm text-slate-600 line-clamp-1 max-w-[250px]" :title="req.request_message">
                  {{ req.request_message || 'No message provided' }}
                </p>
              </TableCell>

              <TableCell class="text-right">
                <Button variant="outline" size="sm" @click="viewRequest(req)">
                  Review
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
              <Building2 class="h-6 w-6 text-indigo-400" />
            </div>
            <div>
              <DialogTitle class="text-xl font-bold">Partnership Request</DialogTitle>
              <DialogDescription class="text-slate-400 mt-1">
                Internal request awaiting owner authorization.
              </DialogDescription>
            </div>
          </div>
        </div>

        <div class="p-6 space-y-6" v-if="selectedRequest">
          <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Requested By</h4>
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                {{ selectedRequest.creator?.first_name?.[0] }}{{ selectedRequest.creator?.last_name?.[0] }}
              </div>
              <div>
                <p class="text-sm font-bold text-slate-900">{{ selectedRequest.creator?.first_name }} {{ selectedRequest.creator?.last_name }}</p>
                <p class="text-xs text-slate-500 capitalize">{{ selectedRequest.creator?.role?.replace('_', ' ') }}</p>
              </div>
            </div>
          </div>

          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Supplier Information</h4>
            <div class="space-y-3">
              <div class="flex items-center gap-3">
                <Building2 class="h-4 w-4 text-slate-400" />
                <span class="text-sm font-medium">{{ selectedRequest.supplier?.supplier_requirements?.company_name || selectedRequest.supplier?.first_name }}</span>
              </div>
              <div class="flex items-center gap-3">
                <Mail class="h-4 w-4 text-slate-400" />
                <span class="text-sm text-slate-600">{{ selectedRequest.supplier?.email }}</span>
              </div>
              <div class="flex items-center gap-3">
                <Phone class="h-4 w-4 text-slate-400" />
                <span class="text-sm text-slate-600">{{ selectedRequest.supplier?.phone || 'No phone provided' }}</span>
              </div>
            </div>
          </div>

          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Attached Message</h4>
            <div class="bg-white border rounded-lg p-4 text-sm text-slate-700 leading-relaxed shadow-sm">
              {{ selectedRequest.request_message || 'No additional message was provided with this request.' }}
            </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 border-t flex justify-end gap-3">
          <Button variant="outline" @click="showViewDialog = false">Close</Button>
          <Button variant="destructive" @click="initiateReject" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200">
            <XCircle class="h-4 w-4 mr-2" /> Reject
          </Button>
          <Button class="bg-indigo-600 hover:bg-indigo-700 text-white" @click="initiateApprove">
            <CheckCircle2 class="h-4 w-4 mr-2" /> Proceed to Sign & Approve
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showRejectDialog" @update:open="showRejectDialog = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-600 flex items-center gap-2">
            <AlertCircle class="h-5 w-5" /> Reject Partnership Request
          </AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to decline this request? The employee will be notified.
          </AlertDialogDescription>
        </AlertDialogHeader>
        
        <div class="py-4">
          <label class="text-sm font-medium text-slate-700 mb-1 block">Reason for Rejection (Optional)</label>
          <textarea 
            v-model="rejectReason"
            class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-red-500 focus:ring-red-500 min-h-[80px]"
            placeholder="E.g., We already have a primary supplier for these goods..."
          ></textarea>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isRejecting">Cancel</AlertDialogCancel>
          <Button variant="destructive" @click="submitReject" :disabled="isRejecting">
            <span v-if="isRejecting" class="mr-2 h-4 w-4 animate-spin">◌</span>
            Confirm Rejection
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showSignatureDialog" @update:open="showSignatureDialog = $event">
      <DialogContent class="max-w-5xl h-[90vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl">
        <DialogHeader class="p-4 border-b bg-white shrink-0">
          <DialogTitle class="flex items-center gap-2 text-slate-800">
            <FileText class="h-5 w-5 text-indigo-600" />
            Approve & Sign Agreement - {{ selectedRequest?.supplier?.supplier_requirements?.company_name || selectedRequest?.supplier?.first_name }}
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

        <div class="p-4 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)]">
            <div class="flex flex-col gap-4 max-w-4xl mx-auto">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-4 w-4 text-indigo-600" />
                            Draw Your Signature to Approve
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
                            <span class="text-slate-400 font-medium select-none">Sign Here to Approve</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-2 text-center">
                        By signing and submitting, you formally approve this request and legally bind your business to the agreement shown above.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <Button variant="outline" @click="showSignatureDialog = false">Cancel</Button>
                    <Button 
                      :disabled="!hasDrawn || signing" 
                      @click="submitApprove" 
                      class="bg-indigo-600 text-white hover:bg-indigo-700 min-w-[140px]"
                    >
                        <Loader2 v-if="signing" class="mr-2 h-4 w-4 animate-spin" />
                        {{ signing ? 'Approving...' : 'Sign & Approve' }}
                    </Button>
                </div>
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
  Building2,
  Clock,
  CheckCircle2,
  XCircle,
  AlertCircle,
  User as UserIcon,
  Phone,
  Mail,
  Calendar,
  MessageSquare,
  RefreshCw,
  Search,
  PenTool,
  FileText,
  FileX,
  Loader2
} from 'lucide-vue-next'

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog'
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
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
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

const selectedRequest = ref(null)
const showViewDialog = ref(false)

// Reject State
const showRejectDialog = ref(false)
const rejectReason = ref('')
const isRejecting = ref(false)

// Signature & Approve State
const showSignatureDialog = ref(false)
const signing = ref(false)
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
  ctx.strokeStyle = '#1e1b4b' 
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
    const response = await api.get('/distributor/partner-requests')
    if (response.data.success) {
      requests.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch', error)
    toast.error('Failed to load requests')
  } finally {
    loading.value = false
  }
}

const filteredRequests = computed(() => {
  if (!searchQuery.value) return requests.value
  
  const q = searchQuery.value.toLowerCase()
  return requests.value.filter(req => {
    const compName = req.supplier?.supplier_requirements?.company_name?.toLowerCase() || ''
    const creatorName = `${req.creator?.first_name} ${req.creator?.last_name}`.toLowerCase()
    
    return compName.includes(q) || creatorName.includes(q)
  })
})

const viewRequest = (req) => {
  selectedRequest.value = req
  showViewDialog.value = true
}

// --- Formatting Helpers ---
const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-PH', {
    month: 'short', day: 'numeric', year: 'numeric'
  })
}

const formatTime = (dateStr) => {
  return new Date(dateStr).toLocaleTimeString('en-PH', {
    hour: '2-digit', minute: '2-digit'
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
  
  signing.value = true
  const signatureBase64 = signatureCanvas.value.toDataURL('image/png')

  try {
    const response = await api.post(`/distributor/partner-requests/${selectedRequest.value.id}/approve`, {
      signature_image: signatureBase64
    })
    
    if (response.data.success) {
      requests.value = requests.value.filter(req => req.id !== selectedRequest.value.id)
      
      toast.success('Partnership Approved & Signed', {
        description: 'The request has been officially forwarded to the supplier.',
      })
      showSignatureDialog.value = false
      selectedRequest.value = null
    }
  } catch (error) {
    console.error(error)
    toast.error('Approval Failed', {
      description: error.response?.data?.message || 'Server error occurred.'
    })
    showViewDialog.value = true
  } finally {
    signing.value = false
  }
}

// 2. Reject Flow
const initiateReject = () => {
  rejectReason.value = ''
  showViewDialog.value = false
  showRejectDialog.value = true
}

const submitReject = async () => {
  if (!selectedRequest.value) return
  
  isRejecting.value = true
  try {
    const response = await api.post(`/distributor/partner-requests/${selectedRequest.value.id}/reject`, {
      reason: rejectReason.value
    })
    
    if (response.data.success) {
      requests.value = requests.value.filter(req => req.id !== selectedRequest.value.id)
      showRejectDialog.value = false
      selectedRequest.value = null
      
      toast.info('Request Rejected', {
        description: 'Internal request has been declined.',
      })
    }
  } catch (error) {
    console.error(error)
    toast.error('Action Failed', {
      description: error.response?.data?.message || 'Server error occurred.'
    })
  } finally {
    isRejecting.value = false
  }
}

onMounted(() => {
  fetchRequests()
})
</script>