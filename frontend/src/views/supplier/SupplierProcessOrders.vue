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
  ArrowLeft,
  ChevronRight,
  Image,
  X
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'

// UI Components
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Separator } from '@/components/ui/separator'
import { ScrollArea } from '@/components/ui/scroll-area'
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
  DialogTrigger,
} from '@/components/ui/dialog'
import {
  Sheet,
  SheetContent,
  SheetTrigger,
} from '@/components/ui/sheet'

// --- Interfaces ---
interface OrderItem {
  id: number
  name: string
  quantity: number
  unit_price: number
  total: number
}

interface Order {
  id: number
  request_code: string
  status: 'processing' | 'prepared'
  order_date: string
  distributor_name: string
  distributor_address: string 
  total_amount: number
  items: OrderItem[]
}

// --- State ---
const orders = ref<Order[]>([])
const selectedOrderId = ref<number | null>(null)
const isLoading = ref(false)
const isSubmitting = ref(false)
const searchQuery = ref('')
const isReceiptDialogOpen = ref(false)
const showMobileSheet = ref(false)
const showMobilePreview = ref(false)

// Form State
const receiptFile = ref<File | null>(null)
const proofFile = ref<File | null>(null)
const notes = ref('')
const receiptPreview = ref<string | null>(null)
const proofPreview = ref<string | null>(null)

// --- Computed ---
const filteredOrders = computed(() => {
  if (!searchQuery.value) return orders.value
  return orders.value.filter(o => 
    o.request_code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    o.distributor_name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const selectedOrder = computed(() => 
  orders.value.find(o => o.id === selectedOrderId.value)
)

// --- Actions ---
const fetchProcessingOrders = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/supplier/processing-orders')
    orders.value = response.data

    if (orders.value.length > 0 && !selectedOrderId.value) {
      selectedOrderId.value = orders.value[0].id
    }
  } catch (error) {
    console.error(error)
    toast.error("Failed to fetch orders", {
      description: "Please try again later."
    })
  } finally {
    isLoading.value = false
  }
}

const handleFileChange = (event: Event, type: 'receipt' | 'proof') => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    if (type === 'receipt') {
      receiptFile.value = file
      // Create preview for images
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          receiptPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
      } else {
        receiptPreview.value = null
      }
    } else {
      proofFile.value = file
      // Create preview for images
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          proofPreview.value = e.target?.result as string
        }
        reader.readAsDataURL(file)
      } else {
        proofPreview.value = null
      }
    }
  }
}

const clearFile = (type: 'receipt' | 'proof') => {
  if (type === 'receipt') {
    receiptFile.value = null
    receiptPreview.value = null
    // Reset file input
    const input = document.getElementById('receipt') as HTMLInputElement
    if (input) input.value = ''
  } else {
    proofFile.value = null
    proofPreview.value = null
    const input = document.getElementById('proof') as HTMLInputElement
    if (input) input.value = ''
  }
}

// Generate the blob content (Word Doc format)
const generateReceiptBlob = () => {
  if (!selectedOrder.value) return null

  // HTML content styled for Word
  const content = `
    <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/1999/xhtml'>
    <head>
      <meta charset="utf-8">
      <title>Official Receipt</title>
      <style>
        body { font-family: 'Calibri', 'Arial', sans-serif; font-size: 11pt; margin: 1.5cm; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-name { font-size: 16pt; font-weight: bold; }
        .sub-header { color: #555; font-size: 10pt; }
        .meta-table { width: 100%; margin-bottom: 20px; border: none; }
        .meta-table td { padding: 5px; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { border-bottom: 2px solid #000; padding: 8px 5px; text-align: left; background-color: #f0f0f0; font-weight: bold; }
        .items-table td { border-bottom: 1px solid #ddd; padding: 8px 5px; }
        .total-section { margin-top: 20px; text-align: right; font-size: 14pt; font-weight: bold; border-top: 2px solid #000; padding-top: 10px; }
        .footer { margin-top: 50px; text-align: center; font-size: 9pt; color: #888; border-top: 1px solid #eee; padding-top: 10px; }
      </style>
    </head>
    <body>
      <div class="header">
        <div class="company-name">MY SUPPLIER CO.</div>
        <div class="sub-header">123 Warehouse St., Philippines | VAT Reg: 000-123-456-000</div>
        <br/>
        <div style="font-size: 14pt; font-weight: bold;">OFFICIAL RECEIPT</div>
      </div>

      <table class="meta-table">
        <tr>
          <td width="60%">
            <strong>BILL TO:</strong><br/>
            ${selectedOrder.value.distributor_name}<br/>
            ${selectedOrder.value.distributor_address || 'N/A'}
          </td>
          <td width="40%" style="text-align: right;">
            <strong>INVOICE #:</strong> INV-${selectedOrder.value.id}<br/>
            <strong>DATE:</strong> ${new Date().toLocaleDateString()}<br/>
            <strong>REF CODE:</strong> ${selectedOrder.value.request_code}
          </td>
        </tr>
      </table>

      <table class="items-table">
        <thead>
          <tr>
            <th>Item Description</th>
            <th style="text-align: right;">Qty</th>
            <th style="text-align: right;">Unit Price</th>
            <th style="text-align: right;">Amount</th>
          </tr>
        </thead>
        <tbody>
          ${selectedOrder.value.items.map(item => `
            <tr>
              <td>${item.name}</td>
              <td style="text-align: right;">${item.quantity}</td>
              <td style="text-align: right;">${formatCurrency(item.unit_price)}</td>
              <td style="text-align: right;">${formatCurrency(item.total)}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>

      <div class="total-section">
        TOTAL AMOUNT: ${formatCurrency(selectedOrder.value.total_amount)}
      </div>

      <div class="footer">
        This is a system-generated receipt.<br/>
        Generated on ${new Date().toLocaleString()}
      </div>
    </body>
    </html>
  `
  
  // Create a Blob with Word MIME type
  return new Blob(['\ufeff', content], { type: 'application/msword' })
}

// Download Only Action
const downloadReceipt = () => {
  const blob = generateReceiptBlob()
  if (!blob || !selectedOrder.value) return

  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `Receipt_${selectedOrder.value.request_code}.doc`
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  URL.revokeObjectURL(url)
}

// Attach AND Download Action
const attachGeneratedReceipt = () => {
  const blob = generateReceiptBlob()
  if (!blob || !selectedOrder.value) return

  // 1. Download
  downloadReceipt()

  // 2. Attach to Form
  const fileName = `Receipt_${selectedOrder.value.request_code}.doc`
  const file = new File([blob], fileName, { type: 'application/msword' })
  
  receiptFile.value = file
  receiptPreview.value = null
  isReceiptDialogOpen.value = false
  
  toast.success("Receipt Generated & Downloaded", {
    description: `${fileName} has been attached and saved to your device.`
  })
}

const submitPreparation = async () => {
  if (!selectedOrder.value) return
  
  if (!receiptFile.value || !proofFile.value) {
    toast.error("Missing Documents", {
      description: "Please upload both the receipt and proof of preparation.",
    })
    return
  }

  isSubmitting.value = true

  try {
    const formData = new FormData()
    formData.append('receipt_file', receiptFile.value)
    formData.append('proof_file', proofFile.value)
    if (notes.value) {
      formData.append('notes', notes.value)
    }

    await api.post(`/supplier/processing-orders/${selectedOrder.value.id}/prepare`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    const index = orders.value.findIndex(o => o.id === selectedOrderId.value)
    if (index !== -1) {
      orders.value.splice(index, 1) 
    }

    toast.success("Order Prepared Successfully", {
      description: `Order ${selectedOrder.value?.request_code} has been marked as prepared.`,
    })

    // Clear form
    receiptFile.value = null
    receiptPreview.value = null
    proofFile.value = null
    proofPreview.value = null
    notes.value = ''
    
    // Reset file inputs
    const receiptInput = document.getElementById('receipt') as HTMLInputElement
    if (receiptInput) receiptInput.value = ''
    const proofInput = document.getElementById('proof') as HTMLInputElement
    if (proofInput) proofInput.value = ''

    // Close mobile sheet if open
    showMobileSheet.value = false
    showMobilePreview.value = false

    // Select next order if available
    if (orders.value.length > 0) {
      selectedOrderId.value = orders.value[0].id
    } else {
      selectedOrderId.value = null
    }

  } catch (error: any) {
    console.error(error)
    toast.error("Submission Failed", {
      description: error.response?.data?.message || "An error occurred while submitting the order."
    })
  } finally {
    isSubmitting.value = false
  }
}

const selectOrder = (orderId: number) => {
  selectedOrderId.value = orderId
  showMobileSheet.value = false
  showMobilePreview.value = true
}

const formatCurrency = (val: number) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(val)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  fetchProcessingOrders()
})
</script>

<template>
  <div class="flex h-screen w-full flex-col bg-muted/20">
    
    <!-- Desktop View (md and up) -->
    <div class="hidden md:flex h-full w-full">
      <!-- Left Sidebar - Orders List -->
      <div class="w-80 lg:w-96 border-r bg-background flex flex-col h-full">
        <div class="p-4 border-b space-y-4">
          <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg flex items-center gap-2">
              <Package class="h-5 w-5 text-primary" /> 
              Processing Orders
            </h2>
            <Badge variant="secondary">{{ filteredOrders.length }}</Badge>
          </div>
          <div class="relative">
            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input 
              v-model="searchQuery" 
              type="search" 
              placeholder="Search orders..." 
              class="pl-8 bg-muted/50" 
            />
          </div>
        </div>

        <ScrollArea class="flex-1">
          <div v-if="isLoading" class="flex justify-center p-8">
              <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
          
          <div v-else-if="filteredOrders.length === 0" class="text-center text-muted-foreground py-10 px-4">
            <Package class="h-12 w-12 mx-auto mb-3 opacity-20" />
            <p>No processing orders found.</p>
          </div>

          <div v-else class="flex flex-col gap-2 p-3">
            <button
              v-for="order in filteredOrders"
              :key="order.id"
              @click="selectedOrderId = order.id"
              class="flex flex-col items-start gap-2 rounded-lg border p-4 text-left text-sm transition-all hover:bg-accent hover:text-accent-foreground"
              :class="selectedOrderId === order.id ? 'bg-accent border-primary ring-1 ring-primary' : 'bg-card'"
            >
              <div class="flex w-full items-center justify-between">
                 <span class="font-bold font-mono">{{ order.request_code }}</span>
                 <span class="text-xs text-muted-foreground">{{ formatDate(order.order_date) }}</span>
              </div>
              <div class="font-medium">{{ order.distributor_name }}</div>
              <div class="flex w-full items-center justify-between mt-1">
                  <Badge variant="secondary" class="text-[10px] uppercase">Processing</Badge>
                  <span class="font-semibold text-primary">{{ formatCurrency(order.total_amount) }}</span>
              </div>
            </button>
          </div>
        </ScrollArea>
      </div>

      <!-- Right Side - Order Details -->
      <div class="flex-1 flex flex-col h-full overflow-hidden bg-muted/10">
        <header class="h-16 border-b bg-background flex items-center px-6 justify-between shrink-0">
          <h1 class="text-xl font-bold flex items-center gap-2">
            <FileText class="h-5 w-5 text-muted-foreground" />
            Process Order
          </h1>
        </header>

        <div v-if="selectedOrder" class="flex-1 overflow-hidden flex flex-col md:flex-row">
          <ScrollArea class="flex-1 p-6">
            <div class="max-w-3xl mx-auto space-y-6">
              
              <!-- Order Summary Card -->
              <Card>
                <CardHeader class="pb-3">
                  <div class="flex items-start justify-between">
                    <div>
                      <CardTitle>{{ selectedOrder.distributor_name }}</CardTitle>
                      <CardDescription>Request Code: {{ selectedOrder.request_code }}</CardDescription>
                    </div>
                    <Badge class="bg-yellow-500/15 text-yellow-600 hover:bg-yellow-500/25 border-yellow-200">
                      Processing
                    </Badge>
                  </div>
                </CardHeader>
                <Separator />
                <CardContent class="pt-6">
                   <h3 class="font-semibold mb-4 text-sm uppercase tracking-wide text-muted-foreground">Items to Prepare</h3>
                   <Table>
                      <TableHeader>
                        <TableRow>
                          <TableHead>Item Name</TableHead>
                          <TableHead class="text-right">Qty</TableHead>
                          <TableHead class="text-right">Total</TableHead>
                        </TableRow>
                      </TableHeader>
                      <TableBody>
                        <TableRow v-for="item in selectedOrder.items" :key="item.id">
                          <TableCell class="font-medium">{{ item.name }}</TableCell>
                          <TableCell class="text-right">{{ item.quantity }}</TableCell>
                          <TableCell class="text-right">{{ formatCurrency(item.total) }}</TableCell>
                        </TableRow>
                      </TableBody>
                   </Table>
                   <div class="flex justify-end mt-4 pt-4 border-t">
                      <div class="text-lg font-bold">
                        Total: <span class="text-primary">{{ formatCurrency(selectedOrder.total_amount) }}</span>
                      </div>
                   </div>
                </CardContent>
              </Card>

              <!-- Fulfillment Documentation Card -->
              <Card class="border-dashed border-2 bg-background/50">
                <CardHeader>
                  <CardTitle class="flex items-center gap-2">
                     <Upload class="h-5 w-5" /> Fulfillment Documentation
                  </CardTitle>
                  <CardDescription>
                    Generate invoice or upload documents to mark this order as prepared.
                  </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                   
                   <!-- Receipt Upload Section -->
                   <div class="grid w-full items-center gap-2">
                      <Label class="font-semibold">1. Official Receipt / Invoice</Label>
                      
                      <div class="flex flex-col md:flex-row gap-3">
                          <div class="flex-1">
                            <div class="flex gap-2 items-center border rounded-md p-2 bg-background">
                              <Input 
                                  id="receipt" 
                                  type="file" 
                                  class="border-0 p-0 h-auto"
                                  @change="(e) => handleFileChange(e, 'receipt')" 
                                  accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingmlocument" 
                              />
                              <Button 
                                v-if="receiptFile" 
                                variant="ghost" 
                                size="icon" 
                                class="h-8 w-8 shrink-0"
                                @click="clearFile('receipt')"
                              >
                                <X class="h-4 w-4" />
                              </Button>
                              <CheckCircle v-else-if="receiptFile" class="h-5 w-5 text-green-500 shrink-0" />
                            </div>
                            <div v-if="receiptFile" class="mt-2">
                              <p class="text-xs text-muted-foreground flex items-center gap-1">
                                <FileText class="h-3 w-3" />
                                {{ receiptFile.name }}
                              </p>
                            </div>
                          </div>

                          <div class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground">OR</span>
                          </div>

                          <Dialog v-model:open="isReceiptDialogOpen">
                            <DialogTrigger as-child>
                               <Button variant="outline" class="shrink-0 gap-2 w-full md:w-auto">
                                  <Printer class="h-4 w-4" />
                                  Generate Receipt
                               </Button>
                            </DialogTrigger>
                            
                            <DialogContent class="w-[95%] max-w-[500px] mx-auto rounded-lg">
                              <DialogHeader>
                                <DialogTitle>Generate Official Receipt</DialogTitle>
                                <DialogDescription>
                                  A Word Document (.doc) will be generated.
                                </DialogDescription>
                              </DialogHeader>
                              
                              <ScrollArea class="max-h-[60vh]">
                                <div class="border rounded-lg p-4 bg-white text-black text-sm font-serif mt-2">
                                   <div class="text-center border-b pb-4 mb-4">
                                      <h3 class="font-bold text-base">MY SUPPLIER CO.</h3>
                                      <p class="text-xs text-gray-500">123 Warehouse St., Philippines</p>
                                      <p class="text-xs text-gray-500">VAT Reg: 000-123-456-000</p>
                                      <h2 class="font-bold text-lg mt-4">OFFICIAL RECEIPT</h2>
                                   </div>
                                   
                                   <div class="flex flex-col gap-4 mb-4 text-xs">
                                      <div>
                                        <span class="text-gray-500 block">Bill To:</span>
                                        <span class="font-bold">{{ selectedOrder.distributor_name }}</span><br/>
                                        <span>{{ selectedOrder.distributor_address || 'N/A' }}</span>
                                      </div>
                                      <div class="text-left md:text-right">
                                        <span class="text-gray-500 block">Invoice #:</span>
                                        <span>INV-{{ selectedOrder.id }}</span><br/>
                                        <span class="text-gray-500 block mt-1">Date:</span>
                                        <span>{{ new Date().toLocaleDateString() }}</span>
                                      </div>
                                   </div>

                                   <table class="w-full text-left mb-4 text-xs">
                                      <thead class="border-b-2 border-black">
                                         <tr>
                                            <th class="py-1">Item</th>
                                            <th class="py-1 text-right">Qty</th>
                                            <th class="py-1 text-right">Amount</th>
                                         </tr>
                                      </thead>
                                      <tbody>
                                         <tr v-for="item in selectedOrder.items" :key="item.id">
                                            <td class="py-1">{{ item.name }}</td>
                                            <td class="py-1 text-right">{{ item.quantity }}</td>
                                            <td class="py-1 text-right">{{ formatCurrency(item.total) }}</td>
                                         </tr>
                                      </tbody>
                                   </table>

                                   <div class="border-t-2 border-black pt-2 flex justify-between font-bold text-base">
                                      <span>Total</span>
                                      <span>{{ formatCurrency(selectedOrder.total_amount) }}</span>
                                   </div>
                                </div>
                              </ScrollArea>

                              <DialogFooter class="flex-col sm:flex-row gap-2">
                                <Button variant="secondary" @click="downloadReceipt" class="w-full sm:w-auto">
                                  <Download class="h-4 w-4 mr-2" />
                                  Download
                                </Button>
                                <div class="flex gap-2 w-full sm:w-auto">
                                  <Button variant="ghost" @click="isReceiptDialogOpen = false" class="flex-1 sm:flex-none">Cancel</Button>
                                  <Button @click="attachGeneratedReceipt" class="bg-blue-600 hover:bg-blue-700 text-white flex-1 sm:flex-none">
                                      <FileCheck class="h-4 w-4 mr-2" />
                                      Attach
                                  </Button>
                                </div>
                              </DialogFooter>
                            </DialogContent>
                          </Dialog>
                      </div>
                   </div>

                   <Separator />

                   <!-- Proof of Preparation Section -->
                   <div class="grid w-full items-center gap-2">
                      <Label class="font-semibold">2. Proof of Preparation (Photo)</Label>
                      <div>
                        <div class="flex gap-2 items-center border rounded-md p-2 bg-background">
                            <Input 
                              id="proof" 
                              type="file" 
                              @change="(e) => handleFileChange(e, 'proof')" 
                              accept="image/*" 
                              class="border-0 p-0 h-auto" 
                            />
                            <Button 
                              v-if="proofFile" 
                              variant="ghost" 
                              size="icon" 
                              class="h-8 w-8 shrink-0"
                              @click="clearFile('proof')"
                            >
                              <X class="h-4 w-4" />
                            </Button>
                            <CheckCircle v-else-if="proofFile" class="h-5 w-5 text-green-500 shrink-0" />
                        </div>
                        <div v-if="proofFile" class="mt-2">
                          <p class="text-xs text-muted-foreground flex items-center gap-1">
                            <Image class="h-3 w-3" />
                            {{ proofFile.name }}
                          </p>
                          <div v-if="proofPreview" class="mt-2">
                            <img :src="proofPreview" class="max-h-32 rounded-md border" alt="Preview" />
                          </div>
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">
                          Take a photo of the packaged items ready for pickup.
                        </p>
                      </div>
                   </div>

                   <!-- Notes Section -->
                   <div class="grid w-full gap-2 pt-2">
                      <Label for="notes">Additional Notes</Label>
                      <Textarea v-model="notes" placeholder="Any remarks regarding packaging or items..." rows="3" />
                   </div>

                </CardContent>
                <CardFooter class="bg-muted/30 flex justify-end p-4">
                   <Button 
                      size="lg" 
                      @click="submitPreparation" 
                      :disabled="isSubmitting || !receiptFile || !proofFile"
                      class="w-full md:w-auto font-semibold"
                    >
                      <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                      <Package v-else class="mr-2 h-4 w-4" />
                      {{ isSubmitting ? 'Finalizing...' : 'Prepare Order' }}
                   </Button>
                </CardFooter>
              </Card>
            </div>
          </ScrollArea>
        </div>

        <!-- No Order Selected State -->
        <div v-else class="flex h-full flex-col items-center justify-center text-muted-foreground pb-20 px-4">
           <div class="bg-muted/50 p-6 rounded-full mb-4">
              <Package class="h-12 w-12 opacity-50" />
           </div>
           <h3 class="text-lg font-semibold text-center">No Order Selected</h3>
           <p class="text-center">Select a processing order from the left to view details.</p>
        </div>
      </div>
    </div>

    <!-- Mobile View (below md) -->
    <div class="flex md:hidden flex-col h-full w-full">
      
      <!-- Mobile Header -->
      <header class="border-b bg-background px-4 py-3 sticky top-0 z-20 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <Sheet v-model:open="showMobileSheet">
            <SheetTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9">
                <Menu class="h-5 w-5" />
              </Button>
            </SheetTrigger>
            <SheetContent side="left" class="w-[85%] sm:w-80 p-0">
              <!-- Mobile Orders List -->
              <div class="flex flex-col h-full">
                <div class="p-4 border-b space-y-4">
                  <div class="flex items-center justify-between">
                    <h2 class="font-semibold text-lg flex items-center gap-2">
                      <Package class="h-5 w-5 text-primary" /> 
                      Processing
                    </h2>
                    <Badge variant="secondary">{{ filteredOrders.length }}</Badge>
                  </div>
                  <div class="relative">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input 
                      v-model="searchQuery" 
                      type="search" 
                      placeholder="Search..." 
                      class="pl-8 bg-muted/50" 
                    />
                  </div>
                </div>

                <ScrollArea class="flex-1">
                  <div v-if="isLoading" class="flex justify-center p-8">
                      <Loader2 class="h-6 w-6 animate-spin text-muted-foreground" />
                  </div>
                  
                  <div v-else-if="filteredOrders.length === 0" class="text-center text-muted-foreground py-10 px-4">
                    <Package class="h-12 w-12 mx-auto mb-3 opacity-20" />
                    <p>No orders found</p>
                  </div>

                  <div v-else class="flex flex-col gap-2 p-3">
                    <button
                      v-for="order in filteredOrders"
                      :key="order.id"
                      @click="selectOrder(order.id)"
                      class="flex flex-col items-start gap-2 rounded-lg border p-4 text-left text-sm transition-all hover:bg-accent"
                      :class="selectedOrderId === order.id ? 'bg-accent border-primary ring-1 ring-primary' : 'bg-card'"
                    >
                      <div class="flex w-full items-center justify-between">
                         <span class="font-bold font-mono">{{ order.request_code }}</span>
                         <ChevronRight class="h-4 w-4 text-muted-foreground" />
                      </div>
                      <div class="font-medium">{{ order.distributor_name }}</div>
                      <div class="flex w-full items-center justify-between mt-1">
                          <Badge variant="secondary" class="text-[10px] uppercase">Processing</Badge>
                          <span class="font-semibold text-primary">{{ formatCurrency(order.total_amount) }}</span>
                      </div>
                    </button>
                  </div>
                </ScrollArea>
              </div>
            </SheetContent>
          </Sheet>
          
          <div>
            <h1 class="font-semibold text-base">
              {{ selectedOrder ? `Order #${selectedOrder.request_code}` : 'Process Orders' }}
            </h1>
            <p v-if="selectedOrder" class="text-xs text-muted-foreground">
              {{ selectedOrder.distributor_name }}
            </p>
            <p v-else class="text-xs text-muted-foreground">
              {{ filteredOrders.length }} order(s) to process
            </p>
          </div>
        </div>
        
        <Button 
          v-if="selectedOrder && !showMobilePreview" 
          variant="ghost" 
          size="sm"
          @click="showMobilePreview = true"
        >
          View Details
        </Button>
        <Button 
          v-else-if="selectedOrder && showMobilePreview" 
          variant="ghost" 
          size="sm"
          @click="showMobilePreview = false"
        >
          Hide
        </Button>
      </header>

      <!-- Mobile Content -->
      <ScrollArea class="flex-1">
        <div v-if="!selectedOrder" class="flex flex-col items-center justify-center text-muted-foreground py-20 px-4">
          <div class="bg-muted/50 p-6 rounded-full mb-4">
            <Package class="h-12 w-12 opacity-50" />
          </div>
          <p class="font-medium text-center">No Order Selected</p>
          <p class="text-sm text-center">Tap the menu icon to select an order</p>
        </div>

        <div v-else-if="!showMobilePreview" class="p-4">
          <!-- Mobile Order Summary (Collapsed View) -->
          <Card>
            <CardContent class="p-4">
              <div class="flex items-center justify-between mb-3">
                <Badge class="bg-yellow-500/15 text-yellow-600">Processing</Badge>
                <span class="font-bold text-primary">{{ formatCurrency(selectedOrder.total_amount) }}</span>
              </div>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Order Date:</span>
                  <span>{{ formatDate(selectedOrder.order_date) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-muted-foreground">Items:</span>
                  <span>{{ selectedOrder.items.length }} item(s)</span>
                </div>
              </div>
              <Button 
                @click="showMobilePreview = true" 
                variant="outline" 
                class="w-full mt-4"
              >
                View Full Details
              </Button>
            </CardContent>
          </Card>
        </div>

        <div v-else class="p-4 space-y-4 pb-8">
          <!-- Mobile Full Details View -->
          
          <!-- Order Summary Card -->
          <Card>
            <CardHeader class="pb-2">
              <div class="flex items-start justify-between">
                <div>
                  <CardTitle class="text-base">{{ selectedOrder.distributor_name }}</CardTitle>
                  <CardDescription class="text-xs">Code: {{ selectedOrder.request_code }}</CardDescription>
                </div>
                <Badge class="bg-yellow-500/15 text-yellow-600 text-xs">Processing</Badge>
              </div>
            </CardHeader>
            <CardContent>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Order Date:</span>
                  <span>{{ formatDate(selectedOrder.order_date) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-muted-foreground">Address:</span>
                  <span class="text-right">{{ selectedOrder.distributor_address || 'N/A' }}</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Items List -->
          <Card>
            <CardHeader class="pb-2">
              <CardTitle class="text-base">Items to Prepare</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
              <div class="divide-y">
                <div v-for="item in selectedOrder.items" :key="item.id" class="p-3">
                  <div class="flex justify-between items-start mb-1">
                    <div>
                      <p class="font-medium text-sm">{{ item.name }}</p>
                    </div>
                    <p class="font-bold text-sm">{{ formatCurrency(item.total) }}</p>
                  </div>
                  <div class="flex justify-between text-xs text-muted-foreground">
                    <span>Qty: {{ item.quantity }}</span>
                    <span>@ {{ formatCurrency(item.unit_price) }}</span>
                  </div>
                </div>
              </div>
            </CardContent>
            <CardFooter class="bg-muted/50 p-3">
              <div class="flex w-full justify-between items-center font-bold">
                <span>Total</span>
                <span class="text-primary">{{ formatCurrency(selectedOrder.total_amount) }}</span>
              </div>
            </CardFooter>
          </Card>

          <!-- Fulfillment Documentation (Mobile) -->
          <Card class="border-dashed border-2">
            <CardHeader class="pb-2">
              <CardTitle class="text-base flex items-center gap-2">
                <Upload class="h-4 w-4" /> Upload Documents
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              
              <!-- Receipt Upload -->
              <div class="space-y-2">
                <Label class="font-semibold text-sm">1. Official Receipt</Label>
                <div class="border rounded-md p-2 bg-background">
                  <Input 
                    id="receipt-mobile" 
                    type="file" 
                    class="border-0 p-0 h-auto text-sm"
                    @change="(e) => handleFileChange(e, 'receipt')" 
                    accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" 
                  />
                </div>
                <div v-if="receiptFile" class="flex items-center justify-between bg-muted/50 p-2 rounded-md">
                  <span class="text-xs truncate flex-1">{{ receiptFile.name }}</span>
                  <Button variant="ghost" size="icon" class="h-6 w-6" @click="clearFile('receipt')">
                    <X class="h-3 w-3" />
                  </Button>
                </div>
                
                <div class="flex items-center gap-2">
                  <span class="text-xs text-muted-foreground">OR</span>
                  <Dialog v-model:open="isReceiptDialogOpen">
                    <DialogTrigger as-child>
                      <Button variant="outline" size="sm" class="gap-1">
                        <Printer class="h-3 w-3" /> Generate
                      </Button>
                    </DialogTrigger>
                    <DialogContent class="w-[95%] max-w-[400px] mx-auto">
                      <!-- Dialog content same as desktop but optimized for mobile -->
                      <DialogHeader>
                        <DialogTitle class="text-base">Generate Receipt</DialogTitle>
                        <DialogDescription class="text-xs">
                          A Word Document (.doc) will be generated.
                        </DialogDescription>
                      </DialogHeader>
                      
                      <ScrollArea class="max-h-[50vh]">
                        <div class="border rounded-lg p-3 bg-white text-black text-xs">
                          <!-- Simplified preview for mobile -->
                          <div class="text-center border-b pb-2 mb-2">
                            <h3 class="font-bold">MY SUPPLIER CO.</h3>
                            <p class="text-[10px] text-gray-500">123 Warehouse St., Philippines</p>
                          </div>
                          <div class="mb-2">
                            <span class="font-bold">{{ selectedOrder.distributor_name }}</span>
                          </div>
                          <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between text-[10px] mb-1">
                            <span>{{ item.name }} x{{ item.quantity }}</span>
                            <span>{{ formatCurrency(item.total) }}</span>
                          </div>
                          <div class="border-t mt-2 pt-2 flex justify-between font-bold">
                            <span>Total</span>
                            <span>{{ formatCurrency(selectedOrder.total_amount) }}</span>
                          </div>
                        </div>
                      </ScrollArea>

                      <DialogFooter class="flex-col gap-2">
                        <Button variant="secondary" size="sm" @click="downloadReceipt" class="w-full">
                          <Download class="h-3 w-3 mr-2" /> Download
                        </Button>
                        <div class="flex gap-2">
                          <Button variant="ghost" size="sm" @click="isReceiptDialogOpen = false" class="flex-1">Cancel</Button>
                          <Button size="sm" @click="attachGeneratedReceipt" class="bg-blue-600 flex-1">
                            <FileCheck class="h-3 w-3 mr-2" /> Attach
                          </Button>
                        </div>
                      </DialogFooter>
                    </DialogContent>
                  </Dialog>
                </div>
              </div>

              <!-- Proof of Preparation -->
              <div class="space-y-2">
                <Label class="font-semibold text-sm">2. Proof of Preparation</Label>
                <div class="border rounded-md p-2 bg-background">
                  <Input 
                    id="proof-mobile" 
                    type="file" 
                    @change="(e) => handleFileChange(e, 'proof')" 
                    accept="image/*" 
                    class="border-0 p-0 h-auto text-sm" 
                  />
                </div>
                <div v-if="proofFile" class="flex items-center justify-between bg-muted/50 p-2 rounded-md">
                  <span class="text-xs truncate flex-1">{{ proofFile.name }}</span>
                  <Button variant="ghost" size="icon" class="h-6 w-6" @click="clearFile('proof')">
                    <X class="h-3 w-3" />
                  </Button>
                </div>
                <div v-if="proofPreview" class="mt-2">
                  <img :src="proofPreview" class="max-h-32 rounded-md border w-full object-cover" alt="Preview" />
                </div>
              </div>

              <!-- Notes -->
              <div class="space-y-2">
                <Label for="notes-mobile" class="font-semibold text-sm">Notes</Label>
                <Textarea 
                  id="notes-mobile" 
                  v-model="notes" 
                  placeholder="Any remarks..." 
                  rows="2"
                  class="text-sm"
                />
              </div>
            </CardContent>
            <CardFooter class="bg-muted/30 p-4">
              <Button 
                @click="submitPreparation" 
                :disabled="isSubmitting || !receiptFile || !proofFile"
                class="w-full"
                size="default"
              >
                <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                <Package v-else class="mr-2 h-4 w-4" />
                {{ isSubmitting ? 'Submitting...' : 'Prepare Order' }}
              </Button>
            </CardFooter>
          </Card>
        </div>
      </ScrollArea>
    </div>
  </div>
</template>

<style scoped>
/* Mobile optimizations */
@media (max-width: 768px) {
  .mobile-sticky-button {
    position: sticky;
    bottom: 0;
    background: linear-gradient(to top, hsl(var(--background)) 50%, transparent);
  }
}

/* Improve touch targets on mobile */
@media (max-width: 640px) {
  button, 
  [role="button"],
  input,
  select,
  textarea {
    font-size: 16px !important; /* Prevents zoom on focus in iOS */
  }
  
  /* Better spacing for touch targets */
  .p-4 {
    padding: 1rem;
  }
  
  /* Full width buttons on mobile */
  .w-full-mobile {
    width: 100%;
  }
}

/* Smooth transitions */
.sheet-transition {
  transition: transform 0.3s ease-in-out;
}

/* Dialog mobile optimization */
.dialog-content {
  max-height: 90vh;
  overflow-y: auto;
}
</style>