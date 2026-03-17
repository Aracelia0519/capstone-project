<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Shadcn UI
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

// Icons
import { Package, Search, CheckCircle2, XCircle, RotateCcw, Truck, MessageSquare, Loader2, X, Send, Paperclip, Camera, Info, DollarSign, Image as ImageIcon, AlertTriangle, Clock } from 'lucide-vue-next'

const props = defineProps({ user: { type: Object, required: true } })

const returnRequests = ref([])
const permissions = ref({ can_view: false, can_create: false, can_update: false, can_delete: false })
const distributorId = ref(null)
const isLoading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('All')

// --- Request Details Modal ---
const isDetailsModalOpen = ref(false)
const selectedReturn = ref(null)
const isUpdatingStatus = ref(false)

// --- Refund Modal State ---
const isRefundModalOpen = ref(false)
const activeRefundReq = ref(null)
const refundAmount = ref('')
const clientGcashNumber = ref('')
const refundProofFile = ref(null)
const refundProofPreview = ref(null)
const isSubmittingRefund = ref(false)

// --- Chat Drawer State ---
const isChatOpen = ref(false)
const activeChatReq = ref(null)
const chatMessages = ref([])
const chatMessage = ref('')
const chatImageFile = ref(null)
const chatImagePreview = ref(null)
const isSendingMessage = ref(false)

// --- Initialize WebSockets ---
const initWebSockets = (distId) => {
  window.Pusher = Pusher
  const token = localStorage.getItem('auth_token')
  
  const apiUrl = import.meta.env?.VITE_APP_API_URL || 'http://127.0.0.1:8000/api'

  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'fade6ce6ed8705f2ace4',
    cluster: 'ap1',
    forceTLS: true,
    authEndpoint: `${apiUrl}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    }
  })

  // Listen to private channel for return messages
  window.Echo.private(`return.chat.${distId}`)
    .listen('.ReturnMessageSent', (e) => {
      if (isChatOpen.value && activeChatReq.value && e.message.return_request_id === activeChatReq.value.id) {
        chatMessages.value.push(e.message)
        scrollToBottom()
      } else {
        toast.info('New message in return chat')
        fetchReturns()
      }
    })
}

const getFullImageUrl = (path) => {
    if (!path) return ''
    if (path.startsWith('http') || path.startsWith('data:')) return path
    const baseUrl = api.defaults.baseURL ? api.defaults.baseURL.replace(/\/api\/?$/, '') : 'http://127.0.0.1:8000'
    
    let cleanPath = path.startsWith('/') ? path.substring(1) : path
    if (!cleanPath.startsWith('storage/')) {
        cleanPath = 'storage/' + cleanPath
    }
    return `${baseUrl}/${cleanPath}`
}

const openImageInNewTab = (url) => {
    if (url) window.open(url, '_blank')
}

const fetchReturns = async () => {
    isLoading.value = true
    try {
        const res = await api.get('/operation-distributor/returns')
        if (res.data.success) {
            const rawData = res.data.data || []
            returnRequests.value = Array.isArray(rawData) ? rawData : Object.values(rawData)
            
            distributorId.value = res.data.distributor_id
            permissions.value = res.data.permissions
            
            if (distributorId.value) {
                initWebSockets(distributorId.value)
            }
        }
    } catch (error) {
        toast.error('Failed to load return requests')
        console.error(error)
    } finally {
        isLoading.value = false
    }
}

onMounted(() => fetchReturns())

onUnmounted(() => {
    if (window.Echo && distributorId.value) {
        window.Echo.leave(`return.chat.${distributorId.value}`)
    }
})

const filteredRequests = computed(() => {
    return returnRequests.value.filter(req => {
        const matchesSearch = req.client?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                              req.order_item?.product?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                              req.id.toString().includes(searchQuery.value)
        const matchesStatus = statusFilter.value === 'All' || req.status.toLowerCase() === statusFilter.value.toLowerCase()
        return matchesSearch && matchesStatus
    })
})

const getStatusBadge = (status) => {
    const s = status.toLowerCase()
    if (s === 'pending') return 'bg-yellow-500/20 text-yellow-300'
    if (s === 'approved') return 'bg-blue-500/20 text-blue-300'
    if (s === 'shipped') return 'bg-purple-500/20 text-purple-300'
    if (s === 'completed') return 'bg-green-500/20 text-green-300'
    if (s === 'rejected') return 'bg-red-500/20 text-red-300'
    return 'bg-gray-500/20 text-gray-300'
}

const openDetailsModal = (req) => {
    selectedReturn.value = req
    isDetailsModalOpen.value = true
}

const updateStatus = async (id, action) => {
    isUpdatingStatus.value = true
    try {
        const res = await api.post(`/operation-distributor/returns/${id}/${action}`)
        if (res.data.success) {
            toast.success(`Request ${action}d successfully`)
            isDetailsModalOpen.value = false
            fetchReturns()
        }
    } catch (error) {
        toast.error(`Failed to ${action} request`)
    } finally {
        isUpdatingStatus.value = false
    }
}

const openRefundModal = (req) => {
    activeRefundReq.value = req
    refundAmount.value = req.order_item?.price || ''
    
    // Check both roots safely
    const gcash = req.client_gcash_number || req.client?.gcash_number || ''
    clientGcashNumber.value = gcash
    
    refundProofFile.value = null
    refundProofPreview.value = null
    isRefundModalOpen.value = true
}

const handleRefundProofUpload = (e) => { 
    const target = e.target
    if (target.files && target.files[0]) { 
        refundProofFile.value = target.files[0]
        refundProofPreview.value = URL.createObjectURL(target.files[0])
    } 
}

const submitRefundRequest = async () => {
    if (!activeRefundReq.value || !refundAmount.value || !refundProofFile.value) return
    
    // Aggressive check for GCash Number
    const hasGcash = activeRefundReq.value.client_has_gcash || activeRefundReq.value.client?.has_gcash;
    const finalGcashNumber = hasGcash 
        ? (activeRefundReq.value.client_gcash_number || activeRefundReq.value.client?.gcash_number) 
        : clientGcashNumber.value;
        
    if (!finalGcashNumber) {
        toast.error('Client GCash Number is required. Please ask the client via chat.')
        return
    }

    isSubmittingRefund.value = true
    const fd = new FormData()
    fd.append('amount', refundAmount.value)
    fd.append('receipt_proof', refundProofFile.value)
    
    // Safely send the final resolved string
    fd.append('client_gcash_number', finalGcashNumber)

    try {
        const res = await api.post(`/operation-distributor/returns/${activeRefundReq.value.id}/receive-refund`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        if (res.data.success) {
            toast.success('Item received and Refund Request sent to Finance!')
            isRefundModalOpen.value = false
            fetchReturns()
        }
    } catch (error) {
        toast.error('Failed to submit refund request')
    } finally {
        isSubmittingRefund.value = false
    }
}

const scrollToBottom = () => { 
    nextTick(() => { 
        const c = document.getElementById('distReturnChatContainer')
        if (c) c.scrollTop = c.scrollHeight 
    }) 
}

const handleChatImageUpload = (e) => { 
    const target = e.target
    if (target.files && target.files[0]) { 
        chatImageFile.value = target.files[0]
        chatImagePreview.value = URL.createObjectURL(target.files[0])
    } 
}

const removeChatImage = () => {
    chatImageFile.value = null
    if (chatImagePreview.value) { 
        URL.revokeObjectURL(chatImagePreview.value)
        chatImagePreview.value = null 
    }
    const fileInput = document.getElementById('chatImg')
    if (fileInput) fileInput.value = ''
}

const openChat = async (req) => {
    activeChatReq.value = req
    isChatOpen.value = true
    chatMessages.value = []
    removeChatImage()

    try {
        const res = await api.get(`/operation-distributor/returns/${req.id}/chat`)
        if (res.data.success) {
            chatMessages.value = res.data.messages || []
            scrollToBottom()
        }
    } catch (error) { 
        toast.error("Failed to load chat") 
    }
}

const closeChat = () => {
    isChatOpen.value = false
    if (window.Echo && distributorId.value) {
        window.Echo.leave(`return.chat.${distributorId.value}`)
    }
}

const sendChatMessage = async () => {
    if (!activeChatReq.value || (!chatMessage.value.trim() && !chatImageFile.value)) return
    isSendingMessage.value = true
    const fd = new FormData()
    if (chatMessage.value.trim()) fd.append('message', chatMessage.value)
    if (chatImageFile.value) fd.append('image', chatImageFile.value)
    
    try {
        const res = await api.post(`/operation-distributor/returns/${activeChatReq.value.id}/chat`, fd)
        if (res.data.success) { 
            chatMessages.value.push(res.data.data)
            chatMessage.value = '' 
            removeChatImage()
            scrollToBottom() 
        }
    } catch (error) { 
        toast.error('Failed to send message') 
    } finally { 
        isSendingMessage.value = false 
    }
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const d = new Date(dateString)
    return isNaN(d.getTime()) ? '' : new Intl.DateTimeFormat('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric', 
        hour: 'numeric', 
        minute: '2-digit' 
    }).format(d)
}

const pendingCount = computed(() => returnRequests.value.filter(o => o.status === 'pending').length)
const processingCount = computed(() => returnRequests.value.filter(o => ['approved', 'shipped'].includes(o.status)).length)
const completedCount = computed(() => returnRequests.value.filter(o => o.status === 'completed').length)
</script>

<template>
  <div class="ecommerce-returns p-4 md:p-6 text-white">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Returns & Refunds</h1>
          <p class="text-gray-300">Manage client product returns, chat, and request finance refunds.</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 flex items-center p-4 md:p-6">
        <div class="p-3 md:p-4 bg-emerald-500/20 text-emerald-400 rounded-xl mr-4 md:mr-6">
          <RotateCcw class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div>
          <p class="text-xs md:text-sm text-gray-400 font-medium">Total Returns</p>
          <p class="text-2xl md:text-3xl font-bold text-white">{{ returnRequests.length }}</p>
        </div>
      </Card>
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 flex items-center p-4 md:p-6">
        <div class="p-3 md:p-4 bg-yellow-500/20 text-yellow-400 rounded-xl mr-4 md:mr-6">
          <Clock class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div>
          <p class="text-xs md:text-sm text-gray-400 font-medium">Pending Approvals</p>
          <p class="text-2xl md:text-3xl font-bold text-white">{{ pendingCount }}</p>
        </div>
      </Card>
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 flex items-center p-4 md:p-6">
        <div class="p-3 md:p-4 bg-blue-500/20 text-blue-400 rounded-xl mr-4 md:mr-6">
          <Truck class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div>
          <p class="text-xs md:text-sm text-gray-400 font-medium">In Transit</p>
          <p class="text-2xl md:text-3xl font-bold text-white">{{ processingCount }}</p>
        </div>
      </Card>
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 flex items-center p-4 md:p-6">
        <div class="p-3 md:p-4 bg-green-500/20 text-green-400 rounded-xl mr-4 md:mr-6">
          <DollarSign class="w-6 h-6 md:w-8 md:h-8" />
        </div>
        <div>
          <p class="text-xs md:text-sm text-gray-400 font-medium">Refund Processing</p>
          <p class="text-2xl md:text-3xl font-bold text-white">{{ completedCount }}</p>
        </div>
      </Card>
    </div>

    <div class="bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-2xl p-4 md:p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <Input v-model="searchQuery" placeholder="Search product or client..." class="pl-10 bg-gray-800 border-gray-700 text-white placeholder-gray-500 w-full" />
                </div>
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <Select v-model="statusFilter">
                    <SelectTrigger class="w-full md:w-[160px] bg-gray-800 border-gray-700 text-white">
                        <SelectValue placeholder="Filter by Status" />
                    </SelectTrigger>
                    <SelectContent class="bg-gray-800 border-gray-700 text-white">
                        <SelectItem value="All">All Statuses</SelectItem>
                        <SelectItem value="Pending">Pending</SelectItem>
                        <SelectItem value="Approved">Approved</SelectItem>
                        <SelectItem value="Shipped">Shipped</SelectItem>
                        <SelectItem value="Completed">Completed</SelectItem>
                        <SelectItem value="Rejected">Rejected</SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>

        <div v-if="isLoading" class="flex justify-center items-center py-20">
            <Loader2 class="animate-spin text-emerald-500 w-8 h-8" />
        </div>

        <div v-else-if="filteredRequests.length === 0" class="text-center py-16">
            <div class="bg-gray-800 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <RotateCcw class="w-8 h-8 text-gray-500" />
            </div>
            <h3 class="text-lg font-medium text-white mb-2">No returns found</h3>
            <p class="text-gray-400 max-w-sm mx-auto text-sm">We couldn't find any return requests matching your current filters.</p>
        </div>

        <div v-else class="overflow-x-auto rounded-xl border border-gray-800">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800/50 border-b border-gray-800 text-gray-400 text-sm">
                        <th class="p-4 font-medium whitespace-nowrap">Return ID</th>
                        <th class="p-4 font-medium whitespace-nowrap">Client</th>
                        <th class="p-4 font-medium whitespace-nowrap">Product</th>
                        <th class="p-4 font-medium whitespace-nowrap">Reason</th>
                        <th class="p-4 font-medium whitespace-nowrap">Date</th>
                        <th class="p-4 font-medium whitespace-nowrap">Status</th>
                        <th class="p-4 font-medium whitespace-nowrap text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <tr v-for="req in filteredRequests" :key="req.id" class="hover:bg-gray-800/30 transition-colors">
                        <td class="p-4 font-mono text-sm">RET-{{ req.id }}</td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 flex items-center justify-center shrink-0">
                                    <span class="text-white font-bold text-xs">{{ req.client?.name?.charAt(0).toUpperCase() || 'C' }}</span>
                                </div>
                                <span class="text-gray-300 font-medium">{{ req.client?.name }}</span>
                            </div>
                        </td>
                        <td class="p-4 text-gray-300 text-sm max-w-[200px] truncate" :title="req.order_item?.product?.name">
                            {{ req.order_item?.product?.name || 'Unknown Product' }}
                        </td>
                        <td class="p-4 text-gray-400 text-sm truncate max-w-[150px]">{{ req.reason }}</td>
                        <td class="p-4 text-gray-400 text-sm">{{ formatDate(req.created_at) }}</td>
                        <td class="p-4">
                            <Badge :class="getStatusBadge(req.status)" class="border-0 uppercase tracking-wider text-[10px] font-bold px-2 py-1">{{ req.status }}</Badge>
                        </td>
                        <td class="p-4 text-right space-x-2 whitespace-nowrap">
                            <Button variant="outline" size="sm" @click="openDetailsModal(req)" class="bg-gray-800 border-gray-700 text-gray-300 hover:bg-gray-700 h-8">Details</Button>
                            <Button variant="secondary" size="sm" @click="openChat(req)" class="bg-emerald-500/20 text-emerald-400 hover:bg-emerald-500/30 h-8">
                                <MessageSquare class="w-3.5 h-3.5 mr-1.5" /> Chat
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <Dialog v-model:open="isDetailsModalOpen">
        <DialogContent class="sm:max-w-xl p-0 overflow-hidden rounded-2xl bg-gray-900 border-gray-800 text-white z-[9999]">
            <div class="p-6 border-b border-gray-800 bg-gray-800/30">
                <DialogTitle class="text-xl font-bold text-white mb-2">Return Details</DialogTitle>
                <Badge :class="getStatusBadge(selectedReturn?.status)" class="border-0 uppercase tracking-wider text-[10px] font-bold">{{ selectedReturn?.status }}</Badge>
            </div>
            
            <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto">
                <div class="flex gap-4 p-4 rounded-xl border border-gray-800 bg-gray-800/50">
                    <img v-if="selectedReturn?.order_item?.product?.image_url" :src="getFullImageUrl(selectedReturn?.order_item?.product?.image_url)" class="w-16 h-16 rounded-lg object-cover border border-gray-700 shrink-0" />
                    <div class="w-16 h-16 rounded-lg bg-gray-800 border border-gray-700 flex items-center justify-center shrink-0" v-else><ImageIcon class="w-6 h-6 text-gray-500" /></div>
                    <div>
                        <h4 class="font-bold text-white">{{ selectedReturn?.order_item?.product?.name || 'Unknown' }}</h4>
                        <p class="text-sm text-gray-400 mt-1">Client: <span class="font-semibold text-gray-300">{{ selectedReturn?.client?.name }}</span></p>
                        <p class="text-sm text-emerald-400 font-bold mt-1">Price: ₱{{ selectedReturn?.order_item?.price }}</p>
                        
                        <div class="mt-3 inline-flex items-center gap-2 px-3 py-1.5 rounded-md bg-blue-500/10 border border-blue-500/20">
                            <span class="text-xs text-blue-400 font-bold tracking-wide">GCASH:</span>
                            <span v-if="selectedReturn?.client_has_gcash || selectedReturn?.client?.has_gcash" class="text-sm font-mono text-white font-bold">
                                {{ selectedReturn?.client_gcash_number || selectedReturn?.client?.gcash_number }}
                            </span>
                            <span v-else class="text-xs text-amber-400 italic flex items-center gap-1"><AlertTriangle class="w-3 h-3"/> Not set (Ask via Chat)</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <Label class="text-xs text-gray-400 uppercase tracking-wider font-bold">Reason Provided</Label>
                        <p class="mt-1 text-sm text-gray-300 bg-gray-800/50 p-3 rounded-lg border border-gray-700">{{ selectedReturn?.reason }}</p>
                    </div>
                    <div>
                        <Label class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-2 block">Client Photo Proof</Label>
                        <img v-if="selectedReturn?.proof_image_path" :src="getFullImageUrl(selectedReturn?.proof_image_path)" class="w-full max-h-64 object-cover rounded-xl border border-gray-700 shadow-sm cursor-pointer hover:opacity-90 transition-opacity" @click="openImageInNewTab(getFullImageUrl(selectedReturn?.proof_image_path))" />
                    </div>
                </div>

                <div v-if="selectedReturn?.status === 'shipped'" class="space-y-4 pt-4 border-t border-gray-800">
                    <Alert class="bg-purple-500/20 text-purple-300 border-purple-500/30">
                        <Truck class="w-4 h-4"/>
                        <AlertDescription class="font-medium">Client has shipped the item. Waiting for physical receipt.</AlertDescription>
                    </Alert>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label class="text-xs text-gray-400 uppercase tracking-wider font-bold block mb-1">Tracking Number</Label>
                            <div class="font-mono text-lg font-bold text-white bg-gray-800 p-2 rounded-lg border border-gray-700 text-center">{{ selectedReturn?.tracking_number }}</div>
                        </div>
                        <div>
                            <Label class="text-xs text-gray-400 uppercase tracking-wider font-bold block mb-1">Tracking Proof</Label>
                            <img v-if="selectedReturn?.tracking_proof_path" :src="getFullImageUrl(selectedReturn?.tracking_proof_path)" class="w-full h-12 object-cover rounded-lg border border-gray-700 cursor-pointer hover:opacity-80 transition-opacity" @click="openImageInNewTab(getFullImageUrl(selectedReturn?.tracking_proof_path))" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 border-t border-gray-800 bg-gray-800/30">
                <div v-if="selectedReturn?.status === 'pending'" class="flex gap-3">
                    <Button @click="updateStatus(selectedReturn.id, 'reject')" :disabled="isUpdatingStatus || !permissions.can_update" variant="outline" class="flex-1 border-red-500/30 text-red-400 hover:bg-red-500/20 bg-gray-900"><XCircle class="w-4 h-4 mr-2"/> Reject</Button>
                    <Button @click="updateStatus(selectedReturn.id, 'approve')" :disabled="isUpdatingStatus || !permissions.can_update" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white border-0 shadow-lg shadow-emerald-900/20"><CheckCircle2 class="w-4 h-4 mr-2"/> Approve Return</Button>
                </div>
                <div v-else-if="selectedReturn?.status === 'shipped'">
                    <Button @click="() => { isDetailsModalOpen = false; openRefundModal(selectedReturn); }" :disabled="!permissions.can_update" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white border-0 h-12 text-md font-bold shadow-lg shadow-emerald-900/20">
                        <Package class="w-5 h-5 mr-2"/> Mark Received & Request Refund
                    </Button>
                </div>
                <div v-else-if="selectedReturn?.status === 'completed'" class="text-center text-sm font-semibold text-emerald-400 bg-emerald-500/10 py-3 rounded-xl border border-emerald-500/20">
                    <CheckCircle2 class="w-5 h-5 mx-auto mb-1" />
                    Refund processing with Finance
                </div>
                <div v-else class="text-center text-sm text-gray-500 py-2">
                    No further actions available for this status.
                </div>
            </div>
        </DialogContent>
    </Dialog>

    <Dialog v-model:open="isRefundModalOpen">
        <DialogContent class="sm:max-w-md p-6 rounded-2xl bg-gray-900 border-gray-800 text-white z-[9999]">
            <DialogHeader class="mb-4">
                <DialogTitle class="text-xl font-bold text-white flex items-center gap-2"><DollarSign class="w-6 h-6 text-emerald-500" /> Process Refund</DialogTitle>
                <DialogDescription class="text-gray-400">Confirm physical receipt and submit refund request to Finance.</DialogDescription>
            </DialogHeader>

            <div class="space-y-5">
                <div>
                    <Label class="font-bold text-gray-300">Refund Amount (₱)</Label>
                    <Input type="number" v-model="refundAmount" class="mt-1.5 h-12 text-lg font-bold bg-gray-800 border-gray-700 text-white" />
                </div>

                <Alert v-if="!(activeRefundReq?.client_has_gcash || activeRefundReq?.client?.has_gcash)" class="bg-amber-500/20 text-amber-300 border-amber-500/30">
                    <AlertTriangle class="w-4 h-4"/>
                    <AlertDescription class="font-medium">Client missing GCash info. Ask via chat and input below.</AlertDescription>
                </Alert>
                <div v-if="!(activeRefundReq?.client_has_gcash || activeRefundReq?.client?.has_gcash)">
                    <Label class="font-bold text-gray-300">Client GCash Number <span class="text-red-500">*</span></Label>
                    <Input type="text" v-model="clientGcashNumber" placeholder="e.g. 09123456789" class="mt-1.5 h-12 border-amber-500/50 bg-gray-800 text-white placeholder-gray-500" />
                </div>
                <div v-else>
                    <Label class="font-bold text-gray-300">Client GCash on File</Label>
                    <Input type="text" v-model="clientGcashNumber" disabled class="mt-1.5 h-12 bg-gray-800 border-gray-700 text-white font-mono" />
                </div>

                <div>
                    <Label class="font-bold text-gray-300 block mb-2">Upload Photo of Received Item <span class="text-red-500">*</span></Label>
                    <div class="border-2 border-dashed border-gray-700 rounded-xl p-6 text-center hover:bg-gray-800 cursor-pointer relative transition-colors" :class="{'bg-transparent': refundProofPreview}">
                        <input type="file" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" @change="handleRefundProofUpload" />
                        <div v-if="!refundProofPreview"><Camera class="mx-auto h-8 w-8 text-gray-500 mb-2" /><p class="text-sm font-medium text-gray-400">Tap to upload receipt photo</p></div>
                        <div v-else class="relative inline-block w-full">
                            <img :src="refundProofPreview" class="max-h-48 mx-auto rounded-lg shadow-sm border border-gray-700 object-cover" />
                            <Button size="icon" variant="destructive" class="absolute -top-3 -right-3 w-8 h-8 rounded-full shadow-lg z-20" @click.stop.prevent="refundProofFile = null; refundProofPreview = null"><X class="w-4 h-4"/></Button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-800">
                <Button variant="outline" @click="isRefundModalOpen = false" class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">Cancel</Button>
                <Button @click="submitRefundRequest" :disabled="isSubmittingRefund || !refundAmount || !refundProofFile || (!(activeRefundReq?.client_has_gcash || activeRefundReq?.client?.has_gcash) && !clientGcashNumber)" class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-0 hover:opacity-90 shadow-lg shadow-emerald-900/20">
                    <Loader2 v-if="isSubmittingRefund" class="w-4 h-4 mr-2 animate-spin" /> Submit to Finance
                </Button>
            </div>
        </DialogContent>
    </Dialog>

    <Teleport to="body">
        <div v-if="isChatOpen" class="fixed inset-0 flex justify-end pointer-events-auto" style="z-index: 99999;">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click.stop="closeChat"></div>
            
            <transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="translate-x-full" enter-to-class="translate-x-0" leave-active-class="transition-transform duration-200 ease-in" leave-from-class="translate-x-0" leave-to-class="translate-x-full">
                <div v-if="isChatOpen" class="relative w-full md:w-[450px] bg-gray-900 h-full shadow-2xl flex flex-col z-10 border-l border-gray-800 text-white">
                    <div class="p-4 border-b border-gray-800 flex justify-between items-center bg-gray-800/50 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 flex items-center justify-center">
                                <span class="text-white font-bold">{{ activeChatReq?.client?.name?.charAt(0).toUpperCase() || 'C' }}</span>
                            </div>
                            <div>
                                <h3 class="font-bold text-white leading-none">{{ activeChatReq?.client?.name }}</h3>
                                <p class="text-xs text-gray-400 mt-1 truncate max-w-[200px]">{{ activeChatReq?.order_item?.product?.name }}</p>
                            </div>
                        </div>
                        <Button variant="ghost" size="icon" @click.stop.prevent="closeChat" class="rounded-full text-gray-400 hover:bg-gray-800"><X class="w-5 h-5"/></Button>
                    </div>

                    <div class="flex flex-col flex-1 overflow-hidden bg-gray-900/50">
                        <div class="p-2 text-center text-[11px] font-bold text-white shadow-sm shrink-0 uppercase tracking-widest" :class="{ 'bg-yellow-500': activeChatReq.status === 'pending', 'bg-blue-500': activeChatReq.status === 'approved', 'bg-red-500': activeChatReq.status === 'rejected', 'bg-purple-500': activeChatReq.status === 'shipped', 'bg-green-500': activeChatReq.status === 'completed' }">
                            Status: {{ activeChatReq.status }}
                        </div>
                        
                        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="distReturnChatContainer">
                            <div v-for="msg in chatMessages" :key="msg.id" class="flex flex-col" :class="msg.sender_id === distributorId ? 'items-end' : 'items-start'">
                                <div v-if="msg.type === 'system'" class="w-full text-center my-4">
                                    <span class="bg-gray-800 text-gray-300 text-xs px-3 py-1 rounded-full border border-gray-700">{{ msg.message }}</span>
                                    <img v-if="msg.file_path" :src="getFullImageUrl(msg.file_path)" class="max-w-[200px] mt-2 rounded-lg mx-auto border border-gray-700 shadow-sm" />
                                </div>
                                <div v-else class="max-w-[80%] rounded-2xl p-3 shadow-sm" :class="msg.sender_id === distributorId ? 'bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-br-none' : 'bg-gray-800 border border-gray-700 text-white rounded-bl-none'">
                                    <p v-if="msg.type === 'text'" class="text-sm whitespace-pre-wrap">{{ msg.message }}</p>
                                    <div v-else-if="msg.type === 'image'">
                                        <img :src="getFullImageUrl(msg.file_path)" class="rounded-lg max-w-full cursor-pointer hover:opacity-90" @click="openImageInNewTab(getFullImageUrl(msg.file_path))" />
                                        <p v-if="msg.message" class="text-sm mt-2">{{ msg.message }}</p>
                                    </div>
                                </div>
                                <span class="text-[10px] text-gray-500 mt-1">{{ formatDate(msg.created_at) }}</span>
                            </div>
                        </div>

                        <div v-if="chatImagePreview" class="px-4 py-3 bg-gray-800 border-t border-gray-700 shrink-0">
                            <div class="relative inline-block">
                                <img :src="chatImagePreview" class="h-20 w-20 object-cover rounded-xl shadow-md border border-gray-700" />
                                <button @click="removeChatImage" class="absolute -top-2 -right-2 bg-gray-900 text-white rounded-full p-1 shadow-lg hover:scale-110 transition-transform"><X class="w-3 h-3" /></button>
                            </div>
                        </div>

                        <div v-if="['pending', 'approved', 'shipped'].includes(activeChatReq.status) && permissions.can_update" class="p-3 bg-gray-800/80 border-t border-gray-700 shrink-0 flex items-end gap-2">
                            <input type="file" id="chatImg" accept="image/*" class="hidden" @change="handleChatImageUpload" />
                            <label for="chatImg" class="cursor-pointer p-3 rounded-full text-gray-400 hover:bg-gray-700 shrink-0 transition-colors" :class="{'text-emerald-400 bg-emerald-500/20': chatImageFile}"><Paperclip class="w-5 h-5" /></label>
                            <div class="flex-1 bg-gray-900 rounded-2xl border border-gray-700 focus-within:border-emerald-500 flex items-center overflow-hidden transition-colors">
                                <input v-model="chatMessage" @keyup.enter="sendChatMessage" type="text" placeholder="Type a message..." class="w-full bg-transparent border-none focus:ring-0 text-sm px-4 py-3 outline-none text-white placeholder-gray-500" />
                            </div>
                            <Button @click="sendChatMessage" :disabled="isSendingMessage || (!chatMessage.trim() && !chatImageFile)" size="icon" class="rounded-full h-11 w-11 shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white border-0 shadow-md">
                                <Send class="w-4 h-4 ml-0.5" />
                            </Button>
                        </div>
                        <div v-else class="p-3 text-center text-xs font-semibold text-gray-500 bg-gray-800 border-t border-gray-700 shrink-0">
                            Chat is locked for this status or you lack permissions.
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </Teleport>
  </div>
</template>

<style scoped>
</style>