<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { 
  Loader2, 
  Package, 
  FileText, 
  Upload, 
  CheckCircle, 
  Search,
  Printer,
  Download,
  FileCheck,
  Menu,
  Image as ImageIcon,
  X,
  AlertTriangle,
  CornerDownLeft,
  XCircle,
  RefreshCw,
  Eye
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'

// UI Components
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { Textarea } from '@/components/ui/textarea'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Sheet,
  SheetContent,
  SheetTrigger,
} from '@/components/ui/sheet'

// --- Interfaces ---
interface ReturnRequest {
  id: number
  request_code: string
  status: string
  created_at: string
  distributor_name: string
  distributor_address: string
  product_name: string
  category: string
  unit_price: number
  quantity_returned: number
  reason: string
  proof_image_path: string | null
  replacement_receipt_path: string | null
  replacement_proof_path: string | null
}

interface SupplierDetails {
  company_name: string
  business_registration_number: string
  address: string
}

// --- State ---
const returns = ref<ReturnRequest[]>([])
const supplierDetails = ref<SupplierDetails | null>(null)

const selectedReturnId = ref<number | null>(null)
const isLoading = ref(false)
const isSubmitting = ref(false)
const showMobileSheet = ref(false)

// Accept Form State
const receiptFile = ref<File | null>(null)
const proofFile = ref<File | null>(null)
const notes = ref('')
const proofPreview = ref<string | null>(null)

// Reject Form State
const showRejectDialog = ref(false)
const rejectionReason = ref('')
const isRejecting = ref(false)

// Print & Preview State
const printRef = ref<HTMLElement | null>(null)
const showReceiptPreview = ref(false)
const previewHtml = ref('')

// --- Computed ---
const selectedReturn = computed(() => 
  returns.value.find(r => r.id === selectedReturnId.value)
)

const pendingReturns = computed(() => {
  // Only fetching pending and processing. Prepared items are handled by the logistics module.
  return returns.value.filter(r => r.status === 'pending' || r.status === 'processing')
})

const getVatableSales = computed(() => {
  return 0 // Replacement receipts are zeroed out
})

const getVatAmount = computed(() => {
  return 0
})

// NO IMPORT.META - Strictly uses URL detection
const getImageUrl = (path: string | null) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
  const baseUrl = isLocalhost ? 'http://localhost:8000' : 'https://api.capstone001.com';
  const cleanPath = path.replace(/^\/+/, '');
  return `${baseUrl}/storage/${cleanPath}`;
}

// --- Methods ---
const fetchReturns = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/supplier/returns-management')
    
    returns.value = response.data.returns
    supplierDetails.value = response.data.supplier
    
    if (pendingReturns.value.length > 0 && !selectedReturnId.value) {
      selectedReturnId.value = pendingReturns.value[0].id
    }
  } catch (err: any) {
    console.error('Failed to fetch returns:', err)
    toast.error('Failed to load returns', {
      description: err.response?.data?.message || 'An error occurred'
    })
  } finally {
    isLoading.value = false
  }
}

const handleProofUpload = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    proofFile.value = file
    const reader = new FileReader()
    reader.onload = (ev) => {
      proofPreview.value = ev.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const removeProofFile = () => {
  proofFile.value = null
  proofPreview.value = null
}

const handleReceiptUpload = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    receiptFile.value = target.files[0]
  }
}

const selectReturnAndCloseSheet = (retId: number) => {
  selectedReturnId.value = retId
  showMobileSheet.value = false
  
  // Reset forms
  receiptFile.value = null
  proofFile.value = null
  notes.value = ''
  proofPreview.value = null
}

const generateReceiptNumber = () => {
  if (!selectedReturn.value) return ''
  const datePart = selectedReturn.value.created_at.replace(/-/g, '')
  return `REP-${datePart}-${selectedReturn.value.id.toString().padStart(4, '0')}`
}

const generateAndAttachReceipt = () => {
  if (!selectedReturn.value || !supplierDetails.value || !printRef.value) return
  
  const receiptHTML = `
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 40px; }
        .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #eee; padding-bottom: 20px; }
        .company-name { font-size: 28px; font-weight: bold; color: #111; margin: 0 0 5px 0; }
        .company-details { font-size: 14px; color: #666; margin: 0; }
        .receipt-title { font-size: 22px; font-weight: bold; margin: 20px 0 5px 0; letter-spacing: 2px; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 40px; font-size: 14px; }
        .info-block { width: 48%; }
        .info-title { font-weight: bold; color: #555; margin-bottom: 5px; text-transform: uppercase; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background-color: #f8f9fa; color: #333; font-weight: 600; text-align: left; padding: 12px; border-bottom: 2px solid #ddd; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals { width: 50%; float: right; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
        .total-row.grand-total { font-size: 18px; font-weight: bold; border-top: 2px solid #333; padding-top: 15px; margin-top: 10px; }
        .footer { clear: both; text-align: center; margin-top: 80px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #888; }
        .watermark { text-align: center; font-size: 10px; color: #aaa; margin-top: 10px; }
      </style>
    </head>
    <body>
      <div class="header">
        <h1 class="company-name">${supplierDetails.value.company_name}</h1>
        <p class="company-details">${supplierDetails.value.address}</p>
        <p class="company-details">TIN / Reg No: ${supplierDetails.value.business_registration_number}</p>
        <h2 class="receipt-title">OFFICIAL REPLACEMENT RECEIPT</h2>
        <p style="margin:0; font-size: 14px; color: #666;">No. ${generateReceiptNumber()}</p>
      </div>

      <div class="info-section">
        <div class="info-block">
          <div class="info-title">Bill To:</div>
          <div style="font-weight: bold; font-size: 16px; margin-bottom: 4px;">${selectedReturn.value.distributor_name}</div>
          <div>${selectedReturn.value.distributor_address}</div>
        </div>
        <div class="info-block" style="text-align: right;">
          <div class="info-title">Return Details:</div>
          <div><strong>Date:</strong> ${selectedReturn.value.created_at}</div>
          <div><strong>Ref:</strong> ${selectedReturn.value.request_code}</div>
        </div>
      </div>

      <table>
        <thead>
          <tr>
            <th>Item Description</th>
            <th class="text-center">Qty</th>
            <th class="text-right">Unit Price</th>
            <th class="text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              ${selectedReturn.value.product_name} 
              <br><span style="font-size:12px; color:#d32f2f; font-style:italic;">(Replacement Item Issued)</span>
            </td>
            <td class="text-center">${selectedReturn.value.quantity_returned}</td>
            <td class="text-right">P0.00</td>
            <td class="text-right">P0.00</td>
          </tr>
        </tbody>
      </table>

      <div class="totals">
        <div class="total-row">
          <span>VATable Sales</span>
          <span>P0.00</span>
        </div>
        <div class="total-row">
          <span>VAT Amount (12%)</span>
          <span>P0.00</span>
        </div>
        <div class="total-row grand-total">
          <span>Total Amount Due</span>
          <span>P0.00</span>
        </div>
      </div>

      <div class="footer">
        <p>This document is an electronically generated Official Receipt.</p>
        <p>Thank you for your business!</p>
        <div class="watermark">Generated securely by Capstone001 System</div>
      </div>
    </body>
    </html>
  `

  const blob = new Blob(['\ufeff', receiptHTML], { type: 'application/msword' })
  const fileName = `Replacement_${selectedReturn.value.request_code}.doc`
  const file = new File([blob], fileName, { type: 'application/msword' })
  
  receiptFile.value = file
  toast.success('Replacement Generated', { description: 'The digital replacement receipt has been attached automatically.' })
}

const previewReceipt = () => {
  if (!printRef.value) return;
  previewHtml.value = printRef.value.innerHTML;
  showReceiptPreview.value = true;
}

const submitAcceptance = async () => {
  if (!selectedReturn.value || !receiptFile.value || !proofFile.value) {
    toast.error('Missing Requirements', { description: 'Please attach both the replacement receipt and proof of preparation.' })
    return
  }

  isSubmitting.value = true
  const toastId = toast.loading('Accepting Return...', { description: 'Uploading documents and notifying distributor.' })

  try {
    const formData = new FormData()
    formData.append('receipt_file', receiptFile.value)
    formData.append('proof_file', proofFile.value)
    if (notes.value) {
      formData.append('notes', notes.value)
    }

    await api.post(`/supplier/returns-management/${selectedReturn.value.id}/accept`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    toast.success('Return Accepted!', { id: toastId, description: `Replacement for ${selectedReturn.value.request_code} is prepared.` })
    
    await fetchReturns()

    receiptFile.value = null
    proofFile.value = null
    notes.value = ''
    proofPreview.value = null
    
    // Automatically select the next available return if any
    if (pendingReturns.value.length > 0) {
      selectedReturnId.value = pendingReturns.value[0].id
    } else {
      selectedReturnId.value = null
    }

  } catch (err: any) {
    toast.error('Submission Failed', { id: toastId, description: err.response?.data?.message || 'An error occurred.' })
  } finally {
    isSubmitting.value = false
  }
}

const openRejectDialog = () => {
  rejectionReason.value = ''
  showRejectDialog.value = true
}

const submitRejection = async () => {
  if (!selectedReturn.value || !rejectionReason.value.trim()) {
    toast.error('Reason Required', { description: 'Please provide a reason for rejecting this return.' })
    return
  }

  isRejecting.value = true
  const toastId = toast.loading('Rejecting Return...')

  try {
    await api.post(`/supplier/returns-management/${selectedReturn.value.id}/reject`, {
      rejection_reason: rejectionReason.value
    })

    toast.success('Return Rejected', { id: toastId, description: 'The distributor has been notified of the rejection.' })
    showRejectDialog.value = false
    await fetchReturns()
  } catch (err: any) {
    toast.error('Rejection Failed', { id: toastId, description: err.response?.data?.message || 'An error occurred.' })
  } finally {
    isRejecting.value = false
  }
}

const formatCurrency = (val: number | string) => {
  const num = typeof val === 'string' ? parseFloat(val) : val
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(num)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric', month: 'short', day: 'numeric'
  })
}

onMounted(() => {
  fetchReturns()
})
</script>

<template>
  <div class="flex h-screen w-full flex-col bg-muted/20">
    <div class="hidden md:flex h-full w-full">
      
      <div class="w-80 lg:w-96 border-r bg-background flex flex-col h-full">
        <div class="p-4 border-b flex flex-col gap-3 sticky top-0 bg-background z-10">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg flex items-center gap-2 text-red-500">
              <CornerDownLeft class="h-5 w-5" /> Returns Management
            </h2>
            <Button variant="ghost" size="icon" @click="fetchReturns" :disabled="isLoading">
              <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
            </Button>
          </div>
        </div>

        <div class="flex-1 overflow-y-auto custom-scrollbar">
          <div class="flex flex-col gap-2 p-4">
            <div v-if="pendingReturns.length === 0 && !isLoading" class="text-center text-muted-foreground py-10">
              <CheckCircle class="h-12 w-12 mx-auto mb-3 text-emerald-500/30" />
              <p class="font-medium">All caught up!</p>
              <p class="text-sm">No return requests require your attention.</p>
            </div>

            <button
              v-for="ret in pendingReturns"
              :key="ret.id"
              @click="selectedReturnId = ret.id"
              class="flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all hover:bg-accent"
              :class="selectedReturnId === ret.id ? 'bg-red-50 dark:bg-red-950/20 border-red-500 ring-1 ring-red-500' : 'bg-card'"
            >
              <div class="flex w-full items-start justify-between gap-2">
                <div>
                  <div class="font-semibold text-sm">{{ ret.request_code }}</div>
                  <div class="text-xs text-muted-foreground truncate w-40 mt-0.5">{{ ret.distributor_name }}</div>
                </div>
                <div class="text-right flex flex-col items-end">
                  <Badge variant="destructive" class="text-[10px] px-1.5 py-0 h-5 mb-1 whitespace-nowrap bg-red-500">
                    Return Req
                  </Badge>
                  <span class="text-[10px] text-muted-foreground whitespace-nowrap">{{ formatDate(ret.created_at) }}</span>
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>

      <div class="flex-1 flex flex-col h-full overflow-hidden bg-background">
        <header class="flex items-center justify-between border-b px-6 py-4">
          <div v-if="selectedReturn">
            <h1 class="text-xl font-bold flex items-center gap-2 text-red-500">
               <CornerDownLeft class="h-5 w-5" />
               Return Processing
            </h1>
            <p class="text-sm text-muted-foreground">
              Evaluating return request from {{ selectedReturn.distributor_name }}
            </p>
          </div>
          <div v-else>
             <h1 class="text-xl font-bold">Select a Return</h1>
             <p class="text-sm text-muted-foreground">Choose an item from the sidebar to review</p>
          </div>
        </header>

        <div class="flex-1 overflow-y-auto custom-scrollbar">
          <div v-if="selectedReturn" class="p-6 mx-auto max-w-4xl flex flex-col gap-6 pb-20">
            
            <div class="bg-red-50 border border-red-200 dark:bg-red-950/20 dark:border-red-900/50 p-4 rounded-xl">
              <div class="flex items-center gap-3 mb-4">
                <AlertTriangle class="h-5 w-5 text-red-500" />
                <h4 class="font-bold text-red-700 dark:text-red-400 text-base">Distributor's Return Claim</h4>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                   <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Product to Replace</p>
                   <p class="font-bold text-sm text-foreground">{{ selectedReturn.product_name }}</p>
                   <p class="text-xs text-muted-foreground">{{ selectedReturn.category }}</p>
                </div>
                <div>
                   <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Quantity Returned</p>
                   <p class="font-bold text-lg text-foreground">{{ selectedReturn.quantity_returned }} Units</p>
                </div>
                <div class="md:col-span-2">
                   <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Stated Reason</p>
                   <p class="text-sm italic border-l-2 border-red-300 pl-3 py-1 bg-red-100/50 dark:bg-red-900/20 rounded-r">
                     "{{ selectedReturn.reason }}"
                   </p>
                </div>
              </div>
            </div>

            <Card>
              <CardHeader class="pb-3 border-b">
                 <CardTitle class="text-md flex items-center gap-2">
                   <ImageIcon class="h-4 w-4" /> Attached Proof of Return
                 </CardTitle>
              </CardHeader>
              <CardContent class="p-6 flex justify-center bg-muted/20">
                 <img 
                   v-if="selectedReturn.proof_image_path" 
                   :src="getImageUrl(selectedReturn.proof_image_path)" 
                   class="max-w-full max-h-96 rounded-lg border shadow-sm object-contain"
                   alt="Return Proof Image"
                 />
                 <div v-else class="flex flex-col items-center text-muted-foreground py-10">
                   <ImageIcon class="h-12 w-12 opacity-20 mb-2" />
                   <p>No image attached for this return.</p>
                 </div>
              </CardContent>
            </Card>

            <div class="flex items-center gap-4 mt-2">
              <Separator class="flex-1" />
              <span class="text-xs font-bold text-muted-foreground uppercase tracking-widest">Supplier Decision</span>
              <Separator class="flex-1" />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              
              <Card class="border-primary/20 shadow-sm flex flex-col">
                <CardHeader class="pb-3 border-b">
                  <CardTitle class="text-md flex items-center gap-2">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">1</span>
                    Generate Replacement Receipt
                  </CardTitle>
                </CardHeader>
                <CardContent class="pt-5 flex-1 flex flex-col">
                  <p class="text-sm text-muted-foreground mb-6">
                    Create a zero-value replacement receipt to attach to the new shipment.
                  </p>
                  
                  <div class="mt-auto">
                    <div class="flex gap-2 mb-3">
                      <Button 
                        @click="generateAndAttachReceipt" 
                        class="flex-1" 
                        variant="outline"
                        :class="receiptFile ? 'border-emerald-500 bg-emerald-50/50 text-emerald-700 hover:bg-emerald-50' : ''"
                      >
                        <CheckCircle v-if="receiptFile" class="mr-2 h-4 w-4" />
                        <Printer v-else class="mr-2 h-4 w-4" />
                        {{ receiptFile ? 'Attached' : 'Generate' }}
                      </Button>
                      
                      <Button 
                        v-if="receiptFile"
                        @click="previewReceipt"
                        variant="outline"
                        class="border-indigo-200 text-indigo-600 hover:bg-indigo-50"
                        title="Preview Document"
                      >
                        <Eye class="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card class="border-primary/20 shadow-sm flex flex-col">
                <CardHeader class="pb-3 border-b">
                  <CardTitle class="text-md flex items-center gap-2">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-[11px] font-bold text-primary-foreground">2</span>
                    Proof of Preparation
                  </CardTitle>
                </CardHeader>
                <CardContent class="pt-5 flex-1 flex flex-col">
                  <p class="text-sm text-muted-foreground mb-4">
                    Upload a photo of the packed replacement items.
                  </p>
                  
                  <div class="mt-auto">
                    <label 
                      class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg cursor-pointer transition-colors"
                      :class="proofFile ? 'border-emerald-500 bg-emerald-50/10' : 'border-muted-foreground/25 hover:bg-accent/50'"
                    >
                      <div v-if="proofPreview" class="relative w-full h-full flex items-center justify-center p-2 group">
                        <img :src="proofPreview" class="max-h-full max-w-full rounded object-contain" />
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-lg">
                          <Button variant="destructive" size="icon" class="h-8 w-8 rounded-full" @click.prevent="removeProofFile"><X class="h-4 w-4" /></Button>
                        </div>
                      </div>
                      <div v-else class="flex flex-col items-center justify-center pt-5 pb-6">
                        <ImageIcon class="w-8 h-8 mb-2 text-muted-foreground opacity-50" />
                        <p class="text-sm text-muted-foreground font-medium">Click to upload photo</p>
                      </div>
                      <input type="file" class="hidden" accept="image/*" @change="handleProofUpload" />
                    </label>
                  </div>
                </CardContent>
              </Card>

            </div>

            <div class="hidden">
              <div ref="printRef" class="bg-white p-8 text-black" style="width: 800px;">
                <div class="text-center border-b-2 border-gray-200 pb-6 mb-6">
                  <h1 class="text-3xl font-black text-gray-900 uppercase tracking-tight">{{ supplierDetails?.company_name }}</h1>
                  <p class="text-sm text-gray-600 mt-1">{{ supplierDetails?.address }}</p>
                  <p class="text-sm text-gray-600">TIN / Reg No: {{ supplierDetails?.business_registration_number }}</p>
                </div>
                <div class="text-center mb-6">
                  <h2 class="text-xl font-bold tracking-wider">OFFICIAL REPLACEMENT RECEIPT</h2>
                  <p class="text-xs text-gray-500 mt-1">Receipt No: {{ generateReceiptNumber() }}</p>
                </div>
                <div class="flex justify-between mb-8 text-sm">
                  <div class="w-1/2">
                    <p class="text-gray-500 font-bold mb-1 uppercase text-xs">Billed To:</p>
                    <p class="font-bold text-gray-900 text-lg">{{ selectedReturn?.distributor_name }}</p>
                    <p class="text-gray-700 pr-4">{{ selectedReturn?.distributor_address }}</p>
                  </div>
                  <div class="w-1/3 text-right">
                    <p class="text-gray-500 font-bold mb-1 uppercase text-xs">Return Details:</p>
                    <p class="text-gray-800"><span class="font-medium mr-2">Date:</span> {{ selectedReturn?.created_at }}</p>
                    <p class="text-gray-800"><span class="font-medium mr-2">Ref:</span> {{ selectedReturn?.request_code }}</p>
                  </div>
                </div>
                <table class="w-full mb-8 text-left border-collapse">
                  <thead>
                    <tr class="bg-gray-100">
                      <th class="py-3 px-2 font-bold text-gray-700 text-sm uppercase">Item Description</th>
                      <th class="py-3 px-2 font-bold text-gray-700 text-sm uppercase text-center">Qty</th>
                      <th class="py-3 px-2 font-bold text-gray-700 text-sm uppercase text-right">Unit Price</th>
                      <th class="py-3 px-2 font-bold text-gray-700 text-sm uppercase text-right">Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="border-b border-gray-100">
                      <td class="py-3 px-2 text-sm text-gray-800">
                        {{ selectedReturn?.product_name }}
                        <span class="text-xs text-red-500 italic block mt-0.5">(Replacement Issued)</span>
                      </td>
                      <td class="py-3 px-2 text-center text-sm text-gray-800">{{ selectedReturn?.quantity_returned }}</td>
                      <td class="py-3 px-2 text-right text-sm text-gray-800">₱0.00</td>
                      <td class="py-3 px-2 text-right text-sm text-gray-800 font-medium">₱0.00</td>
                    </tr>
                  </tbody>
                </table>
                <div class="w-1/2 ml-auto mb-12">
                  <div class="flex justify-between py-2 text-sm text-gray-600">
                    <span>VATable Sales</span>
                    <span>₱0.00</span>
                  </div>
                  <div class="flex justify-between py-2 text-sm text-gray-600">
                    <span>VAT Amount (12%)</span>
                    <span>₱0.00</span>
                  </div>
                  <div class="flex justify-between py-3 mt-2 border-t-2 border-gray-800 text-lg font-bold text-gray-900">
                    <span>Total Amount Due</span>
                    <span>₱0.00</span>
                  </div>
                </div>
                <div class="mt-16 pt-8 border-t border-gray-200 text-center">
                  <p class="text-sm font-medium text-gray-800 mb-1">This document is an electronically generated Official Receipt.</p>
                  <p class="text-sm text-gray-500 italic">Thank you for your business!</p>
                </div>
              </div>
            </div>

            <Card class="border-primary/20 shadow-sm mt-4">
              <CardContent class="p-4">
                <Textarea 
                  v-model="notes" 
                  placeholder="Optional delivery notes for replacement..."
                  class="resize-none"
                  rows="2"
                />
              </CardContent>
              <CardFooter class="bg-muted/30 p-4 border-t flex gap-3 flex-col sm:flex-row">
                <Button 
                  @click="openRejectDialog" 
                  variant="outline"
                  class="w-full sm:w-1/3 border-red-200 text-red-500 hover:bg-red-50 hover:text-red-600 dark:border-red-900/50 dark:hover:bg-red-950/30"
                >
                  <XCircle class="mr-2 h-4 w-4" /> Reject Return
                </Button>

                <Button 
                  @click="submitAcceptance" 
                  :disabled="isSubmitting || !receiptFile || !proofFile"
                  class="w-full sm:w-2/3 bg-emerald-600 hover:bg-emerald-700 text-white"
                >
                  <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                  <Package v-else class="mr-2 h-4 w-4" />
                  {{ isSubmitting ? 'Processing...' : 'Accept & Prep Replacement' }}
                </Button>
              </CardFooter>
            </Card>

          </div>
          
          <div v-else class="flex flex-col items-center justify-center text-muted-foreground py-20 px-4 h-full">
            <CornerDownLeft class="h-16 w-16 mb-4 opacity-20" />
            <p class="font-medium text-center">Select a return request</p>
            <p class="text-sm text-center">Click on a return from the sidebar to review.</p>
          </div>
        </div>
      </div>
    </div>

    <Dialog :open="showRejectDialog" @update:open="(val) => !val && (showRejectDialog = false)">
      <DialogContent class="sm:max-w-106.25">
        <DialogHeader>
          <DialogTitle class="text-red-500 flex items-center gap-2">
            <AlertTriangle class="h-5 w-5" /> Reject Return Request
          </DialogTitle>
          <DialogDescription>
            Please provide a detailed reason for rejecting this return. The distributor will be notified.
          </DialogDescription>
        </DialogHeader>
        <div class="grid gap-4 py-4">
          <Textarea 
            v-model="rejectionReason"
            placeholder="e.g. Beyond the 7-day return policy, physical damage caused by mishandling..."
            rows="4"
          />
        </div>
        <DialogFooter>
          <Button variant="ghost" @click="showRejectDialog = false" :disabled="isRejecting">Cancel</Button>
          <Button variant="destructive" @click="submitRejection" :disabled="isRejecting || !rejectionReason.trim()">
            <Loader2 v-if="isRejecting" class="mr-2 h-4 w-4 animate-spin" />
            Confirm Rejection
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog :open="showReceiptPreview" @update:open="(val) => !val && (showReceiptPreview = false)">
      <DialogContent class="sm:max-w-3xl max-h-[90vh] overflow-y-auto bg-white text-black p-0 border-none">
        <DialogHeader class="sticky top-0 bg-white z-10 border-b px-6 py-4 flex flex-row items-center justify-between">
          <DialogTitle class="text-black font-bold">Document Preview</DialogTitle>
        </DialogHeader>
        <div class="px-6 py-4">
          <div class="border border-gray-200 shadow-sm rounded-lg overflow-hidden" v-html="previewHtml"></div>
        </div>
        <DialogFooter class="sticky bottom-0 bg-white z-10 border-t px-6 py-4">
          <Button @click="showReceiptPreview = false" variant="outline" class="text-black border-gray-300">Close Preview</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <div class="flex md:hidden flex-col h-full w-full bg-background">
      <header class="flex items-center justify-between border-b px-4 py-3 sticky top-0 z-20 bg-background/95 backdrop-blur supports-backdrop-filter:bg-background/60">
        <div class="flex items-center gap-3">
          <Sheet v-model:open="showMobileSheet">
            <SheetTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9">
                <Menu class="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-[85%] sm:w-80 p-0">
              <div class="flex flex-col h-full">
                <div class="p-4 border-b flex flex-col gap-3 sticky top-0 bg-background z-10">
                  <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-lg flex items-center gap-2 text-red-500">
                      <CornerDownLeft class="h-5 w-5" /> Returns
                    </h2>
                    <Button variant="ghost" size="icon" @click="fetchReturns" :disabled="isLoading">
                      <RefreshCw :class="['h-4 w-4', isLoading ? 'animate-spin' : '']" />
                    </Button>
                  </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar">
                  <div class="flex flex-col gap-2 p-4">
                    <div v-if="pendingReturns.length === 0 && !isLoading" class="text-center text-muted-foreground py-10">
                      <CheckCircle class="h-12 w-12 mx-auto mb-3 text-emerald-500/30" />
                      <p class="font-medium">All caught up!</p>
                    </div>

                    <div v-for="ret in pendingReturns" :key="ret.id" class="space-y-1">
                      <button
                        @click="selectReturnAndCloseSheet(ret.id)"
                        class="w-full flex flex-col items-start gap-2 rounded-lg border p-3 text-left text-sm transition-all"
                        :class="selectedReturnId === ret.id ? 'bg-red-50 dark:bg-red-950/20 border-red-500' : 'bg-card'"
                      >
                        <div class="flex w-full items-start justify-between gap-2">
                          <div>
                            <div class="font-semibold text-sm">{{ ret.request_code }}</div>
                            <div class="text-xs text-muted-foreground truncate w-40 mt-0.5">{{ ret.distributor_name }}</div>
                          </div>
                          <div class="text-right flex flex-col items-end">
                            <Badge variant="destructive" class="text-[10px] px-1.5 py-0 h-5 mb-1 whitespace-nowrap bg-red-500">Return Req</Badge>
                          </div>
                        </div>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </SheetContent>
          </Sheet>
          
          <div>
            <h1 class="font-semibold text-base">Return Request</h1>
            <p v-if="selectedReturn" class="text-xs text-muted-foreground truncate w-40">
              {{ selectedReturn.request_code }}
            </p>
          </div>
        </div>
      </header>

      <div class="flex-1 overflow-y-auto custom-scrollbar">
        <div v-if="selectedReturn" class="p-4 space-y-4 pb-32">
          
          <div class="bg-red-50 border border-red-200 dark:bg-red-950/20 dark:border-red-900/50 p-4 rounded-xl">
            <div class="flex items-center gap-3 mb-4">
              <AlertTriangle class="h-5 w-5 text-red-500" />
              <h4 class="font-bold text-red-700 dark:text-red-400 text-base">Distributor's Return Claim</h4>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2 sm:col-span-1">
                 <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Product</p>
                 <p class="font-bold text-sm text-foreground">{{ selectedReturn.product_name }}</p>
                 <p class="text-xs text-muted-foreground">{{ selectedReturn.category }}</p>
              </div>
              <div class="col-span-2 sm:col-span-1">
                 <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Quantity</p>
                 <p class="font-bold text-lg text-foreground">{{ selectedReturn.quantity_returned }} Units</p>
              </div>
              <div class="col-span-2">
                 <p class="text-xs text-red-500 font-semibold uppercase tracking-wider mb-1">Stated Reason</p>
                 <p class="text-sm italic border-l-2 border-red-300 pl-3 py-1 bg-red-100/50 dark:bg-red-900/20 rounded-r">
                   "{{ selectedReturn.reason }}"
                 </p>
              </div>
            </div>
          </div>

          <Card>
            <CardHeader class="pb-3 border-b">
               <CardTitle class="text-md flex items-center gap-2">
                 <ImageIcon class="h-4 w-4" /> Attached Proof of Return
               </CardTitle>
            </CardHeader>
            <CardContent class="p-4 flex justify-center bg-muted/20">
               <img 
                 v-if="selectedReturn.proof_image_path" 
                 :src="getImageUrl(selectedReturn.proof_image_path)" 
                 class="max-w-full max-h-[300px] rounded-lg border shadow-sm object-contain"
                 alt="Return Proof Image"
               />
               <div v-else class="flex flex-col items-center text-muted-foreground py-8">
                 <ImageIcon class="h-10 w-10 opacity-20 mb-2" />
                 <p class="text-sm">No image attached.</p>
               </div>
            </CardContent>
          </Card>

          <div class="flex items-center gap-4 mt-2">
            <Separator class="flex-1" />
            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Supplier Decision</span>
            <Separator class="flex-1" />
          </div>

          <Card class="border-primary/20">
            <CardHeader class="pb-2">
              <CardTitle class="text-sm">1. Replacement Receipt</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex gap-2">
                <Button @click="generateAndAttachReceipt" class="flex-1 mb-3" variant="outline" size="sm" :class="receiptFile ? 'border-emerald-500 text-emerald-600' : ''">
                  <CheckCircle v-if="receiptFile" class="mr-2 h-4 w-4" />
                  <Printer v-else class="mr-2 h-4 w-4" />
                  {{ receiptFile ? 'Attached' : 'Generate' }}
                </Button>
                <Button v-if="receiptFile" @click="previewReceipt" variant="outline" size="sm" class="border-indigo-200 text-indigo-600">
                  <Eye class="h-4 w-4" />
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card class="border-primary/20">
            <CardHeader class="pb-2"><CardTitle class="text-sm">2. Preparation Proof</CardTitle></CardHeader>
            <CardContent>
              <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed rounded-lg cursor-pointer" :class="proofFile ? 'border-emerald-500 bg-emerald-50/10' : 'border-muted-foreground/25'">
                <div v-if="proofPreview" class="relative w-full h-full flex items-center justify-center p-1 group">
                  <img :src="proofPreview" class="max-h-full max-w-full rounded object-contain" />
                  <div class="absolute inset-0 bg-black/40 flex items-center justify-center rounded-lg">
                    <Button variant="destructive" size="icon" class="h-8 w-8 rounded-full" @click.prevent="removeProofFile"><X class="h-4 w-4" /></Button>
                  </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center">
                  <ImageIcon class="w-6 h-6 mb-1 text-muted-foreground opacity-50" />
                  <p class="text-xs text-muted-foreground">Upload photo</p>
                </div>
                <input type="file" class="hidden" accept="image/*" @change="handleProofUpload" />
              </label>
            </CardContent>
          </Card>

          <Card>
            <CardHeader class="pb-2"><CardTitle class="text-sm">Notes</CardTitle></CardHeader>
            <CardContent>
              <Textarea v-model="notes" placeholder="Optional delivery notes..." rows="2" class="text-sm" />
            </CardContent>
          </Card>

        </div>

        <div v-else class="flex flex-col items-center justify-center text-muted-foreground py-20 px-4">
          <CornerDownLeft class="h-16 w-16 mb-4 opacity-20" />
          <p class="font-medium text-center">Select a return</p>
          <p class="text-sm text-center">Tap the menu icon to select</p>
        </div>
      </div>

      <div v-if="selectedReturn" class="mobile-sticky-button p-4 border-t bg-background flex flex-col gap-2">
        <Button @click="submitAcceptance" :disabled="isSubmitting || !receiptFile || !proofFile" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white" size="lg">
          <Loader2 v-if="isSubmitting" class="mr-2 h-5 w-5 animate-spin" />
          <Package v-else class="mr-2 h-5 w-5" />
          {{ isSubmitting ? 'Processing...' : 'Accept & Prep' }}
        </Button>
        <Button @click="openRejectDialog" variant="outline" class="w-full border-red-200 text-red-500" size="default">
          Reject Return
        </Button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(107, 114, 128, 0.8);
}

@media (max-width: 768px) {
  .mobile-sticky-button {
    position: sticky;
    bottom: 0;
    z-index: 30;
  }
}
@media (max-width: 640px) {
  button, [role="button"], input, select, textarea { font-size: 16px !important; }
}
</style>