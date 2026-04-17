<template>
  <div class="p-6 max-w-8xl mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">E-Commerce Deployment</h1>
        <p class="text-muted-foreground mt-1">
          Review and manage product deployment requests from operational distributors.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <div class="relative w-full md:w-64">
          <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
          <Input 
            v-model="searchQuery" 
            type="search" 
            placeholder="Search requests..." 
            class="w-full pl-8" 
          />
        </div>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
          <Package class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ totalRequests }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Pending Approval</CardTitle>
          <Clock class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-yellow-600">{{ pendingRequests }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Deployed</CardTitle>
          <CheckCircle class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-green-600">{{ deployedRequests }}</div>
        </CardContent>
      </Card>
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Rejected / Not Deployed</CardTitle>
          <XCircle class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-red-600">{{ rejectedRequests }}</div>
        </CardContent>
      </Card>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Deployment Requests</CardTitle>
        <CardDescription>
          Products pending approval to go live on the e-commerce storefront.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[120px]">Request ID</TableHead>
                <TableHead>Distributor</TableHead>
                <TableHead>Product Details</TableHead>
                <TableHead class="text-right">Qty in Stock</TableHead>
                <TableHead>Date Requested</TableHead>
                <TableHead>Status</TableHead>
                <TableHead class="text-right">Action</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="isLoading">
                <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                  Loading deployment requests...
                </TableCell>
              </TableRow>
              <TableRow v-else-if="filteredRequests.length === 0">
                <TableCell colspan="7" class="h-24 text-center text-muted-foreground">
                  No deployment requests found.
                </TableCell>
              </TableRow>
              <TableRow v-else v-for="request in filteredRequests" :key="request.id">
                <TableCell class="font-medium">REQ-{{ request.id }}</TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span class="font-medium">{{ request.distributor }}</span>
                    <span class="text-xs text-muted-foreground">{{ request.region }}</span>
                  </div>
                </TableCell>
                <TableCell>
                  <div class="flex flex-col">
                    <span class="font-medium">{{ request.productName }}</span>
                    <span class="text-xs text-muted-foreground">SKU: {{ request.sku }}</span>
                  </div>
                </TableCell>
                <TableCell class="text-right">{{ request.quantity }}</TableCell>
                <TableCell>{{ formatDate(request.dateRequested) }}</TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(request.status)">
                    {{ request.status }}
                  </Badge>
                </TableCell>
                <TableCell class="text-right">
                  <Button 
                    variant="outline" 
                    size="sm"
                    @click="openViewDialog(request)"
                  >
                    View Details
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </CardContent>
    </Card>

    <AlertDialog :open="isAlertOpen" @update:open="isAlertOpen = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription>
            You are about to <strong>{{ actionType.toLowerCase() }}</strong> the deployment request 
            <span class="font-semibold text-foreground">REQ-{{ selectedRequest?.id }}</span> 
            for {{ selectedRequest?.productName }}.
            <span v-if="actionType === 'Deploy'">This will apply a <strong>{{ markupPercentage }}% markup</strong> and make the product live on the e-commerce store.</span>
            <span v-if="actionType === 'Reject'">This will remove it from deployment consideration.</span>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="closeAlert">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click="executeAction" 
            :disabled="isProcessing"
            :class="actionType === 'Reject' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
          >
            <span v-if="isProcessing">Processing...</span>
            <span v-else>Yes, {{ actionType }}</span>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="isViewDialogOpen" @update:open="isViewDialogOpen = $event">
      <DialogContent class="sm:max-w-[600px]">
        <DialogHeader>
          <DialogTitle>Deployment Details</DialogTitle>
          <DialogDescription>
            Full details for deployment request REQ-{{ viewedRequest?.id }}
          </DialogDescription>
        </DialogHeader>
        
        <div v-if="viewedRequest" class="grid grid-cols-1 md:grid-cols-3 gap-6 py-4">
          <div class="col-span-1 border rounded-md p-2 flex items-center justify-center bg-muted/20">
            <img 
              v-if="viewedRequest.raw?.product?.image_url" 
              :src="viewedRequest.raw.product.image_url" 
              alt="Product Image" 
              class="w-full h-auto object-cover rounded"
            />
            <Package v-else class="h-24 w-24 text-muted-foreground/30" />
          </div>
          
          <div class="col-span-2 space-y-4">
            <div>
              <h3 class="font-semibold text-lg">{{ viewedRequest.productName }}</h3>
              <p class="text-sm text-muted-foreground">{{ viewedRequest.raw?.product?.category }} - {{ viewedRequest.raw?.product?.type }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-muted-foreground block text-xs">SKU Code</span>
                <span class="font-medium">{{ viewedRequest.sku || 'N/A' }}</span>
              </div>
              <div>
                <span class="text-muted-foreground block text-xs">Size</span>
                <span class="font-medium">{{ viewedRequest.raw?.product?.size || 'N/A' }}</span>
              </div>
              <div class="col-span-2">
                <span class="text-muted-foreground block text-xs">Inventory Quantity</span>
                <span class="font-medium">{{ viewedRequest.quantity }} units</span>
              </div>
              
              <div>
                <span class="text-muted-foreground block text-xs">Supplier Cost</span>
                <span class="font-medium text-gray-600">₱{{ Number(viewedRequest.raw?.product?.original_cost || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}</span>
              </div>
              
              <div v-if="viewedRequest.status === 'Pending'">
                <span class="text-muted-foreground block text-xs">Interest / Markup (%)</span>
                <div class="flex items-center gap-2 mt-1">
                  <Input type="number" v-model="markupPercentage" min="0" class="h-8 w-24" />
                  <span class="text-xs text-muted-foreground">%</span>
                </div>
              </div>

              <div :class="viewedRequest.status === 'Pending' ? 'col-span-2' : ''">
                <span class="text-muted-foreground block text-xs">
                   {{ viewedRequest.status === 'Pending' ? `Projected Price (Incl. ${markupPercentage}%)` : 'Final Selling Price' }}
                </span>
                <span class="font-medium text-green-600 text-lg">
                  ₱{{ viewedRequest.status === 'Pending' ? calculateProjectedPrice(viewedRequest.raw?.product?.original_cost, markupPercentage) : Number(viewedRequest.raw?.product?.projected_price || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                </span>
              </div>
              
              <div class="col-span-2 border-t pt-2 mt-2">
                <span class="text-muted-foreground block text-xs">Requested By (Distributor)</span>
                <span class="font-medium">{{ viewedRequest.distributor }}</span>
              </div>
              <div class="col-span-2">
                <span class="text-muted-foreground block text-xs">Current Status</span>
                <Badge :variant="getStatusVariant(viewedRequest.status)" class="mt-1">
                  {{ viewedRequest.status }}
                </Badge>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="flex sm:justify-between items-center w-full">
          <Button variant="ghost" @click="isViewDialogOpen = false">Close</Button>
          
          <div v-if="viewedRequest?.status === 'Pending'" class="flex gap-2">
            <Button 
              variant="outline"
              class="text-red-600 hover:text-red-700 hover:bg-red-50"
              @click="promptActionFromView('Reject', viewedRequest)"
            >
              <X class="w-4 h-4 mr-2" /> Reject
            </Button>
            <Button 
              class="bg-green-600 hover:bg-green-700 text-white"
              @click="promptActionFromView('Deploy', viewedRequest)"
            >
              <Check class="w-4 h-4 mr-2" /> Deploy to E-Commerce
            </Button>
          </div>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios' 
import { 
  Search, Check, X, Package, Clock, CheckCircle, XCircle 
} from 'lucide-vue-next'

// Shadcn UI Imports
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const searchQuery = ref('')
const isLoading = ref(true)
const isProcessing = ref(false)
const requests = ref([])

// Dynamic Markup State
const markupPercentage = ref(30)

// Helper to calculate the projected price dynamically on the frontend
const calculateProjectedPrice = (cost, markup) => {
  const numCost = Number(cost) || 0
  const numMarkup = Number(markup) || 0
  const projected = numCost * (1 + (numMarkup / 100))
  return projected.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
}

// Fetch data from backend
const fetchRequests = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/distributor/deployments')
    if (response.data.success) {
      // Map database fields to the frontend expected format
      requests.value = response.data.data.map(inv => ({
        id: inv.id,
        distributor: inv.distributor ? `${inv.distributor.first_name} ${inv.distributor.last_name}` : 'Unknown Distributor',
        region: inv.distributor?.address || 'No Address',
        productName: inv.product?.name || 'Unknown Product',
        sku: inv.product?.sku_code || 'N/A',
        quantity: inv.quantity,
        dateRequested: inv.updated_at,
        status: mapStatus(inv.ecommerce_status),
        raw: inv // Keep the raw data for the view modal
      }))
    }
  } catch (error) {
    console.error("Failed to fetch deployments:", error)
    toast.error("Failed to load requests", { description: "Please try refreshing the page." })
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchRequests()
})

// Convert db enum to readable status
const mapStatus = (status) => {
  switch (status) {
    case 'pending': return 'Pending'
    case 'deployed': return 'Deployed'
    case 'not_deployed': return 'Rejected' // Maps back to UI logic
    default: return 'Pending'
  }
}

// --- Dashboard Computed Properties ---
const totalRequests = computed(() => requests.value.length)
const pendingRequests = computed(() => requests.value.filter(r => r.status === 'Pending').length)
const deployedRequests = computed(() => requests.value.filter(r => r.status === 'Deployed').length)
const rejectedRequests = computed(() => requests.value.filter(r => r.status === 'Rejected').length)

// --- Search Filter ---
const filteredRequests = computed(() => {
  if (!searchQuery.value) return requests.value
  
  const query = searchQuery.value.toLowerCase()
  return requests.value.filter(req => 
    req.productName.toLowerCase().includes(query) || 
    req.distributor.toLowerCase().includes(query) ||
    String(req.id).includes(query)
  )
})

// --- View Details Dialog Logic ---
const isViewDialogOpen = ref(false)
const viewedRequest = ref(null)

const openViewDialog = (request) => {
  viewedRequest.value = request
  // Reset markup to 30 when opening a new dialog
  markupPercentage.value = 30 
  isViewDialogOpen.value = true
}

const promptActionFromView = (action, request) => {
  isViewDialogOpen.value = false // Close the view modal
  promptAction(action, request)  // Open the confirmation alert
}

// --- Alert Dialog State & Logic ---
const isAlertOpen = ref(false)
const actionType = ref('') // 'Deploy' or 'Reject'
const selectedRequest = ref(null)

const promptAction = (action, request) => {
  actionType.value = action
  selectedRequest.value = request
  isAlertOpen.value = true
}

const closeAlert = () => {
  isAlertOpen.value = false
  setTimeout(() => {
    selectedRequest.value = null
    actionType.value = ''
  }, 200) // Clear after animation
}

// Backend Execution Action
const executeAction = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true

  const targetId = selectedRequest.value.id
  const actionEndpoint = actionType.value === 'Deploy' ? 'deploy' : 'reject'
  
  // Attach the selected markup percentage to the payload
  const payload = actionType.value === 'Deploy' ? { markup_percentage: markupPercentage.value } : {}

  try {
    const response = await api.post(`/distributor/deployments/${targetId}/${actionEndpoint}`, payload)
    
    if (response.data.success) {
      if (actionType.value === 'Deploy') {
        toast.success('Product Deployed Successfully', {
          description: `${selectedRequest.value.productName} is now live on the e-commerce store.`
        })
      } else {
        toast.error('Deployment Rejected', {
          description: `The request for ${selectedRequest.value.productName} has been declined.`
        })
      }
      
      // Refresh list to sync state with database
      await fetchRequests()
    }
  } catch (error) {
    console.error(`Failed to ${actionEndpoint}:`, error)
    toast.error('Action Failed', {
      description: error.response?.data?.message || `An error occurred while trying to ${actionType.value.toLowerCase()} the product.`
    })
  } finally {
    isProcessing.value = false
    closeAlert()
  }
}

// --- Utilities ---
const getStatusVariant = (status) => {
  switch (status) {
    case 'Pending': return 'secondary'
    case 'Deployed': return 'default'
    case 'Rejected': return 'destructive'
    default: return 'outline'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const options = { year: 'numeric', month: 'short', day: 'numeric' }
  return new Date(dateString).toLocaleDateString(undefined, options)
}
</script>