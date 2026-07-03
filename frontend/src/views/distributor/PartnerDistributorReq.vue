<template>
  <div class="flex flex-col gap-6 p-8 min-h-screen bg-white">
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
      <Card class="border shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Approvals</CardTitle>
          <Clock class="h-4 w-4 text-orange-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-slate-900">{{ requests.length }}</div>
          <p class="text-xs text-muted-foreground">Requires immediate attention</p>
        </CardContent>
      </Card>

      <Card class="border shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Recently Reviewed</CardTitle>
          <CheckCircle2 class="h-4 w-4 text-emerald-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-slate-900">0</div>
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
                  <span class="text-sm font-medium text-slate-800">{{ req.creator?.first_name }} {{ req.creator?.last_name }}</span>
                </div>
              </TableCell>

              <TableCell>
                <div class="text-sm text-slate-800">{{ formatDate(req.created_at) }}</div>
                <div class="text-xs text-slate-500">{{ formatTime(req.created_at) }}</div>
              </TableCell>

              <TableCell>
                <p class="text-sm text-slate-600 line-clamp-1 max-w-[250px]" :title="req.request_message">
                  {{ req.request_message || 'No message provided' }}
                  <span v-if="parseTerms(req.negotiated_terms).length" class="text-indigo-500 font-semibold ml-1"> (+{{ parseTerms(req.negotiated_terms).length }} terms)</span>
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
      <DialogContent class="sm:max-w-[600px] p-0 overflow-hidden bg-white">
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
                <span class="text-sm font-medium text-slate-900">{{ selectedRequest.supplier?.supplier_requirements?.company_name || selectedRequest.supplier?.first_name }}</span>
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

        <div class="p-4 bg-slate-50 border-t flex flex-wrap justify-between items-center gap-3">
          <Button variant="outline" @click="viewSupplierProducts(selectedRequest.supplier_id)" class="text-indigo-600 border-indigo-200 hover:bg-indigo-50">
            <Package class="h-4 w-4 mr-2" /> View Catalog
          </Button>

          <div class="flex items-center gap-2">
            <Button variant="outline" @click="showViewDialog = false" class="bg-white hover:bg-slate-100 text-slate-700 border-slate-200">Close</Button>
            <Button variant="destructive" @click="initiateReject" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200">
              <XCircle class="h-4 w-4 mr-2" /> Reject
            </Button>
            <Button class="bg-indigo-600 hover:bg-indigo-700 text-white" @click="initiateApprove">
              <CheckCircle2 class="h-4 w-4 mr-2" /> Establish Contract
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showRejectDialog" @update:open="showRejectDialog = $event">
      <AlertDialogContent class="bg-white border border-slate-200">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-red-600 flex items-center gap-2">
            <AlertCircle class="h-5 w-5" /> Reject Partnership Request
          </AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500">
            Are you sure you want to decline this request? The employee will be notified.
          </AlertDialogDescription>
        </AlertDialogHeader>
        
        <div class="py-4">
          <label class="text-sm font-medium text-slate-700 mb-1 block">Reason for Rejection (Optional)</label>
          <textarea 
            v-model="rejectReason"
            class="w-full rounded-md border border-slate-300 p-3 text-sm focus:border-red-500 focus:ring-red-500 min-h-[80px] bg-white text-slate-800"
            placeholder="E.g., We already have a primary supplier for these goods..."
          ></textarea>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isRejecting" class="bg-white text-slate-700 border-slate-200">Cancel</AlertDialogCancel>
          <Button variant="destructive" @click="submitReject" :disabled="isRejecting">
            <span v-if="isRejecting" class="mr-2 h-4 w-4 animate-spin">◌</span>
            Confirm Rejection
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showSignatureDialog" @update:open="showSignatureDialog = $event">
      <!-- FIXED: Bypassing Shadcn max-width using raw styles -->
      <DialogContent style="max-width: 85vw; width: 85vw;" class="h-[95vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 sm:p-6 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex items-center gap-2 text-xl sm:text-2xl text-slate-800">
            <FileText class="h-6 w-6 text-indigo-600" />
            Set Duration & Terms - {{ selectedRequest?.supplier?.supplier_requirements?.company_name || selectedRequest?.supplier?.first_name }}
          </DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-4 sm:p-6 relative bg-slate-200 flex gap-6 min-h-[200px] z-0">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-md border border-slate-300"
            ></iframe>
            <div v-else class="flex flex-col w-full h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-300 shadow-md">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>No digital agreement document was found for this request.</p>
            </div>
        </div>

        <div class="p-6 sm:p-8 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] overflow-x-hidden overflow-y-auto max-h-[60vh] z-10">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-10 w-full max-w-[1600px] mx-auto">
                
                <!-- Left Column: Date & Terms -->
                <div class="flex flex-col gap-6">
                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 shadow-sm">
                        <label class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2 mb-3">
                            <Calendar class="h-5 w-5 text-indigo-600" />
                            Contract End Date
                        </label>
                        <Input type="date" v-model="contractEndDate" class="w-full text-base sm:text-lg p-3 h-auto bg-white border-slate-300 focus:ring-indigo-500" />
                        <p v-if="!isDateValid && contractEndDate" class="text-xs text-red-500 mt-2 font-semibold">
                            Contract must last at least 1 month from today.
                        </p>
                        <p class="text-sm text-slate-500 mt-2">
                            Set an expiration date for this partnership. The supplier can negotiate this date.
                        </p>
                    </div>

                    <div class="bg-slate-50 p-5 rounded-xl border border-slate-200 shadow-sm flex-1 flex flex-col">
                        <label class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2 mb-4">
                            <FileText class="h-5 w-5 text-indigo-600" />
                            Terms & Conditions
                        </label>
                        <div class="space-y-3 mb-4 max-h-48 overflow-y-auto pr-2 flex-1">
                            <div v-for="(term, index) in terms" :key="index" class="flex justify-between items-start bg-white p-3 border border-slate-200 rounded-lg text-sm shadow-sm">
                                <span class="text-slate-700 flex-1 pr-3 leading-relaxed">{{ term }}</span>
                                <Button type="button" variant="ghost" size="icon" class="h-8 w-8 text-red-500 hover:bg-red-50 shrink-0 rounded-full" @click="removeTerm(index)">
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                            <div v-if="terms.length === 0" class="text-sm text-slate-400 italic p-6 text-center bg-white border border-dashed border-slate-300 rounded-lg">No additional terms added.</div>
                        </div>
                        <div class="flex gap-2 mt-auto pt-2">
                            <Input v-model="newTerm" placeholder="E.g., Payment must be settled within 30 days..." class="flex-1 text-sm h-11 bg-white border-slate-300 focus:ring-indigo-500" @keyup.enter="addTerm" />
                            <Button type="button" @click="addTerm" class="bg-indigo-600 text-white hover:bg-indigo-700 h-11 px-5 shrink-0 shadow-sm font-semibold">Add Term</Button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Signature Pad -->
                <div class="flex flex-col bg-slate-50 p-5 rounded-xl border border-slate-200 shadow-sm h-full">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                        <label class="text-sm sm:text-base font-bold text-slate-800 flex items-center gap-2">
                            <PenTool class="h-5 w-5 text-indigo-600" />
                            Draw or Upload Signature
                        </label>
                        <div class="flex items-center gap-2 flex-wrap bg-white p-1 rounded-lg border border-slate-200 shadow-sm">
                            <input type="file" ref="signatureFileInput" accept="image/*" class="hidden" @change="handleSignatureUpload" />
                            <Button variant="ghost" size="sm" class="h-8 text-xs sm:text-sm text-indigo-600 font-medium hover:text-indigo-700 hover:bg-indigo-50 shrink-0" @click="signatureFileInput.click()">
                                <Upload class="h-4 w-4 mr-1.5" /> Upload Image
                            </Button>
                            <div class="w-px h-4 bg-slate-200"></div>
                            <Button variant="ghost" size="sm" class="h-8 text-xs sm:text-sm text-slate-600 font-medium hover:text-red-600 hover:bg-red-50 shrink-0" @click="clearSignature">
                                Clear Pad
                            </Button>
                        </div>
                    </div>
                    
                    <div class="border-2 border-dashed border-slate-300 rounded-xl bg-white overflow-hidden relative w-full flex-1 min-h-[250px] shadow-inner" ref="canvasContainer">
                        <canvas 
                            ref="signatureCanvas" 
                            class="w-full h-full cursor-crosshair touch-none block"
                            @mousedown="startDrawing"
                            @mousemove="draw"
                            @mouseup="stopDrawing"
                            @mouseleave="stopDrawing"
                            @touchstart.prevent="startDrawingTouch"
                            @touchmove.prevent="drawTouch"
                            @touchend.prevent="stopDrawing"
                        ></canvas>
                        
                        <div v-if="!hasDrawn" class="absolute inset-0 pointer-events-none flex items-center justify-center opacity-50 bg-slate-50/50">
                            <span class="text-slate-500 font-bold select-none text-lg border-2 border-slate-200 px-8 py-4 rounded-2xl bg-white shadow-sm">Sign Here or Upload</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-end items-center gap-4 mt-8 pt-6 border-t border-slate-200 w-full max-w-[1600px] mx-auto">
                <Button variant="outline" size="lg" class="bg-white border-slate-300 text-slate-700 font-semibold px-8 w-full sm:w-auto shadow-sm hover:bg-slate-50" @click="showSignatureDialog = false">Cancel</Button>
                <Button 
                  size="lg"
                  :disabled="!hasDrawn || !isDateValid || signing" 
                  @click="submitApprove" 
                  class="bg-indigo-600 text-white font-bold hover:bg-indigo-700 min-w-[240px] px-8 w-full sm:w-auto shadow-md"
                >
                    <Loader2 v-if="signing" class="mr-2 h-5 w-5 animate-spin" />
                    {{ signing ? 'Approving & Submitting...' : 'Sign & Submit to Supplier' }}
                </Button>
            </div>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/utils/axios'
import echo from '@/utils/websocket.js'
import { toast } from 'vue-sonner'
import { 
  Building2, Clock, CheckCircle2, XCircle, AlertCircle, Phone, Mail, Calendar,
  RefreshCw, Search, PenTool, FileText, FileX, Loader2, Package, Upload, X
} from 'lucide-vue-next'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

const router = useRouter()
const route = useRoute()

const requests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const currentDistributorId = ref(null)

const selectedRequest = ref(null)
const showViewDialog = ref(false)

const showRejectDialog = ref(false)
const rejectReason = ref('')
const isRejecting = ref(false)

const showSignatureDialog = ref(false)
const signing = ref(false)
const hasDrawn = ref(false)
const signatureFileInput = ref(null)

const signatureCanvas = ref(null)
const canvasContainer = ref(null)
let ctx = null
let isDrawing = false

const contractEndDate = ref('')
const terms = ref([])
const newTerm = ref('')

const parseTerms = (termsStr) => {
    if (!termsStr) return [];
    if (Array.isArray(termsStr)) return termsStr;
    try {
        let parsed = JSON.parse(termsStr);
        if (typeof parsed === 'string') parsed = JSON.parse(parsed); // catch potential double stringification
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        return [];
    }
}

const addTerm = () => {
    if (newTerm.value.trim()) {
        terms.value.push(newTerm.value.trim());
        newTerm.value = '';
    }
}
const removeTerm = (index) => {
    terms.value.splice(index, 1);
}

const isDateValid = computed(() => {
    if (!contractEndDate.value) return false;
    const selected = new Date(contractEndDate.value);
    const minDate = new Date();
    minDate.setMonth(minDate.getMonth() + 1);
    return selected >= minDate;
});

const viewSupplierProducts = (id) => {
    const basePath = route.path.startsWith('/special-rbac') ? '/special-rbac' : '/distributor';
    router.push(`${basePath}/SupplierProducts/${id}`);
}

const setupWebsocket = (distributorId) => {
    if (!distributorId) return;
    echo.leave(`distributor.${distributorId}.requests`);
    echo.private(`distributor.${distributorId}.requests`)
        .listen('.PartnershipRequest.Created', (e) => {
            const req = e.request;
            const index = requests.value.findIndex(r => r.id === req.id);
            if (index !== -1) {
                requests.value.splice(index, 1, req);
            } else if (req.status === 'pending_internal') {
                requests.value.unshift(req);
            }
        })
        .listen('.PartnershipRequest.Updated', (e) => {
            const req = e.request;
            requests.value = requests.value.filter(r => r.id !== req.id);
        });
}

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
  return { x: clientX - rect.left, y: clientY - rect.top }
}

const startDrawing = (e) => {
  isDrawing = true; hasDrawn.value = true;
  const coords = getCoordinates(e); ctx.beginPath(); ctx.moveTo(coords.x, coords.y)
}
const draw = (e) => {
  if (!isDrawing) return;
  const coords = getCoordinates(e); ctx.lineTo(coords.x, coords.y); ctx.stroke()
}
const stopDrawing = () => {
  if (!isDrawing) return;
  isDrawing = false; ctx.closePath()
}
const startDrawingTouch = (e) => startDrawing(e)
const drawTouch = (e) => draw(e)
const clearSignature = () => {
  if (!ctx || !signatureCanvas.value) return;
  ctx.clearRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height); hasDrawn.value = false
}

const handleSignatureUpload = (e) => {
  const file = e.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = (event) => {
    const img = new Image()
    img.onload = () => {
      if (!ctx || !signatureCanvas.value) return
      clearSignature()
      const canvas = signatureCanvas.value
      const hRatio = canvas.width / img.width
      const vRatio = canvas.height / img.height
      const ratio = Math.min(hRatio, vRatio) * 0.95 
      const centerShift_x = (canvas.width - img.width * ratio) / 2
      const centerShift_y = (canvas.height - img.height * ratio) / 2
      ctx.drawImage(img, 0, 0, img.width, img.height, centerShift_x, centerShift_y, img.width * ratio, img.height * ratio)
      hasDrawn.value = true
    }
    img.src = event.target.result
  }
  reader.readAsDataURL(file)
  e.target.value = ''
}

watch(showSignatureDialog, async (newVal) => {
  if (newVal) {
    hasDrawn.value = false;
    contractEndDate.value = '';
    terms.value = [];
    newTerm.value = '';
    await nextTick();
    setTimeout(initCanvas, 50); 
  }
})

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/distributor/partner-requests')
    if (response.data.success) {
      requests.value = response.data.data
      if (response.data.distributor_id) {
          currentDistributorId.value = response.data.distributor_id;
          setupWebsocket(response.data.distributor_id);
      }
    }
  } catch (error) { toast.error('Failed to load requests') } 
  finally { loading.value = false }
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

const viewRequest = (req) => { selectedRequest.value = req; showViewDialog.value = true }

const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
const formatTime = (dateStr) => new Date(dateStr).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' })

const initiateApprove = () => { showViewDialog.value = false; showSignatureDialog.value = true }

const submitApprove = async () => {
  if (!selectedRequest.value || !hasDrawn.value || !signatureCanvas.value || !isDateValid.value) return
  
  signing.value = true
  const signatureBase64 = signatureCanvas.value.toDataURL('image/png')

  try {
    const response = await api.post(`/distributor/partner-requests/${selectedRequest.value.id}/approve`, {
      signature_image: signatureBase64,
      contract_end_date: contractEndDate.value,
      negotiated_terms: terms.value
    })
    
    if (response.data.success) {
      requests.value = requests.value.filter(req => req.id !== selectedRequest.value.id)
      toast.success('Partnership Approved & Forwarded', { description: 'The agreement, terms, and duration have been sent to the supplier.' })
      showSignatureDialog.value = false
      selectedRequest.value = null
    }
  } catch (error) {
    toast.error('Approval Failed', { description: error.response?.data?.message || 'Server error occurred.' })
    showViewDialog.value = true
  } finally { signing.value = false }
}

const initiateReject = () => { rejectReason.value = ''; showViewDialog.value = false; showRejectDialog.value = true }

const submitReject = async () => {
  if (!selectedRequest.value) return
  isRejecting.value = true
  try {
    const response = await api.post(`/distributor/partner-requests/${selectedRequest.value.id}/reject`, { reason: rejectReason.value })
    if (response.data.success) {
      requests.value = requests.value.filter(req => req.id !== selectedRequest.value.id)
      showRejectDialog.value = false
      selectedRequest.value = null
      toast.info('Request Rejected', { description: 'Internal request has been declined.' })
    }
  } catch (error) { toast.error('Action Failed', { description: error.response?.data?.message || 'Server error occurred.' }) } 
  finally { isRejecting.value = false }
}

onMounted(() => fetchRequests())

onUnmounted(() => {
    if (currentDistributorId.value) {
        echo.leave(`distributor.${currentDistributorId.value}.requests`);
    }
})
</script>