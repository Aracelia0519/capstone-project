<template>
  <div class="flex flex-col gap-6 p-8 min-h-screen relative">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">Distributor Partnerships</h1>
        <p class="text-slate-500 mt-2 text-sm">
          Manage incoming partnership proposals, active partners, and negotiations.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button variant="outline" size="sm" @click="fetchRequests">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4 mr-2" />
          Refresh Data
        </Button>
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
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
          <p class="text-xs text-muted-foreground">Officially partnered</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Archived</CardTitle>
          <XCircle class="h-4 w-4 text-slate-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ archivedRequestsCount }}</div>
          <p class="text-xs text-muted-foreground">Ended partnerships</p>
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
        <button 
          @click="activeTab = 'archived'" 
          :class="{'text-slate-600 border-b-2 border-slate-600 font-bold': activeTab === 'archived', 'text-slate-500 hover:text-slate-700': activeTab !== 'archived'}" 
          class="pb-2 text-sm font-medium transition-colors whitespace-nowrap px-2"
        >
          Archived ({{ archivedRequestsCount }})
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
              <TableHead>Contract Until</TableHead>
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
                  <Clock class="h-3 w-3 mr-1"/> Negotiating
                </Badge>
                <Badge v-else-if="req.status === 'active'" variant="outline" class="bg-emerald-50 text-emerald-700 border-emerald-200">
                  <CheckCircle2 class="h-3 w-3 mr-1"/> Active
                </Badge>
                <Badge v-else variant="outline" class="bg-slate-100 text-slate-700 border-slate-300">
                  <XCircle class="h-3 w-3 mr-1"/> Ended
                </Badge>
              </TableCell>

              <TableCell>
                <div class="text-sm font-medium text-slate-800">
                    <span v-if="(req.status === 'active' || req.status === 'pending_supplier') && (req.proposed_end_date || req.contract_end_date)">
                        {{ formatDate(req.proposed_end_date || req.contract_end_date) }}
                    </span>
                    <span v-else class="text-slate-400">N/A</span>
                </div>
                <div class="text-xs text-slate-500" v-if="req.status === 'pending_supplier'">
                   <span v-if="req.last_proposed_by === 'supplier'" class="text-amber-600">Pending Distributor</span>
                   <span v-else class="text-blue-600">Action Required</span>
                </div>
              </TableCell>

              <TableCell>
                <p class="text-sm text-slate-600 line-clamp-1 max-w-[250px]" :title="req.request_message">
                  {{ req.request_message || 'Standard agreement.' }}
                </p>
              </TableCell>

              <TableCell class="text-right">
                <div class="flex items-center justify-end gap-2">
                    <Button type="button" variant="outline" size="sm" @click.stop.prevent="openChat(req)" class="text-blue-600 border-blue-200 hover:bg-blue-50 shrink-0 relative z-10">
                        <MessageSquare class="h-4 w-4 mr-1 pointer-events-none" /> Chat
                    </Button>
                    <Button v-if="req.status === 'pending_supplier'" variant="outline" size="sm" @click="viewRequest(req)">
                        Review Proposal
                    </Button>
                    <Button v-else-if="req.status === 'active'" variant="secondary" size="sm" class="bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 shrink-0" @click="viewOfficialDocument(req)">
                        <FileBadge class="h-4 w-4 mr-2" /> View Document
                    </Button>
                    <Button v-else variant="outline" size="sm" class="bg-slate-50 text-slate-700 border-slate-200 shrink-0" @click="viewOfficialDocument(req)">
                        <Download class="h-4 w-4 mr-2" /> Document
                    </Button>
                </div>
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

        <div class="p-6 space-y-4" v-if="selectedRequest">
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

          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
             <h4 class="text-sm font-bold text-blue-900 flex items-center gap-2 mb-2">
                 <Calendar class="h-4 w-4" /> Contract Duration Negotiation
             </h4>
             <div class="text-sm text-blue-800 flex flex-col gap-2">
                 <div>
                    <strong>Target End Date:</strong> 
                    <span v-if="selectedRequest.proposed_end_date || selectedRequest.contract_end_date">
                        {{ formatDate(selectedRequest.proposed_end_date || selectedRequest.contract_end_date) }}
                    </span>
                    <span v-else class="text-amber-600">Not set</span>
                 </div>
                 
                 <div v-if="!selectedRequest.proposed_end_date && !selectedRequest.contract_end_date">
                    <span class="text-xs font-semibold bg-amber-100 text-amber-800 px-2 py-1 rounded">No end date set. Please propose one.</span>
                 </div>
                 <div v-else-if="selectedRequest.last_proposed_by === 'supplier'">
                    <span class="text-xs font-semibold bg-amber-100 text-amber-800 px-2 py-1 rounded">Awaiting Distributor to review your proposed date.</span>
                 </div>
                 <div v-else class="text-xs">
                    Please accept the current date and sign, or propose a new duration.
                 </div>
                 
                 <div class="mt-2" v-if="selectedRequest.last_proposed_by !== 'supplier'">
                    <Button variant="outline" size="sm" class="bg-white" @click="showProposeDateDialog = true">
                        Propose <span v-if="selectedRequest.contract_end_date || selectedRequest.proposed_end_date">New </span>Date
                    </Button>
                 </div>
             </div>
          </div>
        </div>

        <div class="p-4 bg-slate-50 border-t flex justify-end items-center gap-3">
          <Button type="button" variant="outline" @click.stop.prevent="openChat(selectedRequest)" class="text-blue-600 border-blue-200 hover:bg-blue-50 mr-auto">
            <MessageSquare class="h-4 w-4 mr-2 pointer-events-none" /> Message Buyer
          </Button>
          <Button variant="outline" @click="showViewDialog = false">Close</Button>
          <Button variant="destructive" @click="initiateReject" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200">
            <XCircle class="h-4 w-4 mr-2" /> Decline
          </Button>
          <Button class="bg-blue-600 hover:bg-blue-700 text-white" :disabled="selectedRequest?.last_proposed_by === 'supplier'" @click="initiateApprove">
            <CheckCircle2 class="h-4 w-4 mr-2" /> Proceed to Sign & Accept
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showProposeDateDialog" @update:open="showProposeDateDialog = $event">
      <DialogContent>
          <DialogHeader>
              <DialogTitle>Propose New Contract Date</DialogTitle>
              <DialogDescription>Select a new end date for this partnership. The distributor will need to accept it.</DialogDescription>
          </DialogHeader>
          <div class="py-4">
              <label class="text-sm font-semibold mb-2 block">New End Date</label>
              <Input type="date" v-model="proposedDate" />
              <p class="text-xs text-slate-500 mt-2">Date must be at least 1 month from today.</p>
          </div>
          <div class="flex justify-end gap-2">
              <Button variant="outline" @click="showProposeDateDialog = false">Cancel</Button>
              <Button class="bg-blue-600 text-white" :disabled="!isDateValid || isProcessing" @click="submitProposeDate">
                  <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Send Proposal
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
            placeholder="E.g., Currently at maximum capacity..."
          ></textarea>
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel :disabled="isProcessing">Cancel</AlertDialogCancel>
          <Button variant="destructive" @click="submitReject" :disabled="isProcessing">
            <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" />
            Confirm Decline
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showSignatureDialog" @update:open="showSignatureDialog = $event">
      <DialogContent class="w-[95vw] max-w-6xl h-[95vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 sm:p-6 border-b bg-white shrink-0">
          <DialogTitle class="flex items-center gap-2 text-xl sm:text-2xl text-slate-800">
            <FileText class="h-6 w-6 text-blue-600" />
            Accept Partnership Agreement
          </DialogTitle>
        </DialogHeader>
        <div class="flex-1 overflow-hidden p-4 sm:p-6 relative bg-slate-100 min-h-[200px]">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
        </div>
        <div class="p-4 sm:p-6 border-t bg-white shrink-0 shadow-[0_-4px_10px_rgba(0,0,0,0.02)] z-10 overflow-y-auto max-h-[50vh]">
            <div class="flex flex-col gap-4 w-full max-w-5xl mx-auto">
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                        <label class="text-base font-semibold text-slate-700 flex items-center gap-2">
                            <PenTool class="h-5 w-5 text-blue-600" /> Counter-Sign to Activate
                        </label>
                        <div class="flex items-center gap-2 sm:gap-4">
                          <button @click="signatureMode = 'draw'" :class="{'text-blue-600 font-bold border-b-2 border-blue-600': signatureMode === 'draw'}" class="text-sm px-2 pb-1">Draw Pad</button>
                          <button @click="signatureMode = 'upload'" :class="{'text-blue-600 font-bold border-b-2 border-blue-600': signatureMode === 'upload'}" class="text-sm px-2 pb-1">Upload File</button>
                          <Button v-if="signatureMode === 'draw'" variant="ghost" size="sm" class="h-9" @click="clearSignature">Clear Pad</Button>
                        </div>
                    </div>
                    
                    <div v-if="signatureMode === 'draw'" class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 overflow-hidden relative w-full h-[200px]" ref="canvasContainer">
                        <canvas 
                            ref="signatureCanvas" 
                            class="w-full h-full cursor-crosshair touch-none bg-white"
                            @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                            @touchstart.prevent="startDrawing" @touchmove.prevent="draw" @touchend.prevent="stopDrawing"
                        ></canvas>
                    </div>
                    
                    <div v-else class="border-2 border-dashed border-slate-300 rounded-lg bg-slate-50 p-6 flex flex-col items-center justify-center min-h-[200px]">
                      <input type="file" accept="image/*" class="hidden" ref="sigFileInput" @change="handleSignatureUpload($event)" />
                      <div v-if="uploadedSigUrl" class="flex flex-col items-center gap-2">
                        <img :src="uploadedSigUrl" class="h-[140px] object-contain border p-2 bg-white rounded shadow-sm" />
                        <Button type="button" variant="outline" size="sm" @click="uploadedSigUrl = ''; uploadedSigBase64 = ''">Remove file</Button>
                      </div>
                      <Button v-else type="button" variant="outline" @click="$refs.sigFileInput.click()">
                        <Download class="h-5 w-5 mr-2" /> Choose Signature Image
                      </Button>
                      <p class="text-xs text-slate-400 mt-2">Supports PNG, JPG, or JPEG image formats</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row justify-end gap-3 mt-4">
                    <Button variant="outline" class="px-6 w-full sm:w-auto" @click="showSignatureDialog = false">Cancel</Button>
                    <Button class="bg-blue-600 text-white hover:bg-blue-700 min-w-[200px] px-6 w-full sm:w-auto" :disabled="((signatureMode === 'draw' && !hasDrawn) || (signatureMode === 'upload' && !uploadedSigBase64)) || isProcessing" @click="submitApprove">
                        <Loader2 v-if="isProcessing" class="mr-2 h-5 w-5 animate-spin" /> Activating...
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
            Official Partnership Agreement
          </DialogTitle>
        </DialogHeader>
        <div class="flex-1 overflow-hidden p-4 relative">
            <iframe 
              v-if="selectedRequest?.agreement_url" 
              :src="selectedRequest.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-300"
            ></iframe>
        </div>
        <div class="p-6 border-t bg-white shrink-0 z-10 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)]">
            <div class="flex justify-end mt-4">
                <Button variant="outline" @click="showOfficialDocDialog = false">Close Document</Button>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Teleport to="body">
      <div v-if="showChatPanel" class="fixed inset-0 bg-slate-900/50 z-[9998] transition-opacity" @click="closeChat"></div>

      <div v-if="showChatPanel" class="fixed inset-y-0 right-0 w-full md:w-[400px] bg-white shadow-2xl z-[9999] flex flex-col transform transition-transform duration-300 pointer-events-auto">
          <div class="p-4 border-b bg-slate-50 flex items-center justify-between">
              <div class="flex items-center gap-3">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold">
                      {{ chatPartnerName.charAt(0) }}
                  </div>
                  <div>
                      <h3 class="font-bold text-slate-800">{{ chatPartnerName }}</h3>
                      <p class="text-xs text-slate-500">Partnership Chat</p>
                  </div>
              </div>
              <Button type="button" variant="ghost" size="icon" @click="closeChat">
                  <X class="h-5 w-5 text-slate-500" />
              </Button>
          </div>

          <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-100" ref="chatScrollContainer">
              <div v-if="isChatLoading" class="flex justify-center items-center h-full">
                  <Loader2 class="h-6 w-6 animate-spin text-slate-400" />
              </div>
              <template v-else>
                  <div v-if="chatMessages.length === 0" class="text-center text-slate-500 my-10 text-sm">
                      No messages yet. Start the conversation!
                  </div>
                  <div v-for="msg in chatMessages" :key="msg.id" class="flex" :class="msg.sender_id === currentSupplierId ? 'justify-end' : 'justify-start'">
                      <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm" :class="msg.sender_id === currentSupplierId ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white text-slate-800 rounded-bl-none shadow-sm'">
                          {{ msg.message }}
                          <div class="text-[10px] mt-1 opacity-70" :class="msg.sender_id === currentSupplierId ? 'text-blue-100' : 'text-slate-400'">
                              {{ formatTime(msg.created_at) }}
                          </div>
                      </div>
                  </div>
              </template>
          </div>

          <div class="p-4 bg-white border-t">
              <form @submit.prevent="sendMessage" class="flex gap-2">
                  <Input v-model="newMessage" placeholder="Type a message..." class="flex-1" :disabled="isSending" />
                  <Button type="submit" size="icon" class="bg-blue-600 hover:bg-blue-700 text-white shrink-0" :disabled="!newMessage.trim() || isSending">
                      <Send class="h-4 w-4" v-if="!isSending" />
                      <Loader2 class="h-4 w-4 animate-spin" v-else />
                  </Button>
              </form>
          </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue'
import api from '@/utils/axios'
import echo from '@/utils/websocket.js' 
import { toast } from 'vue-sonner'
import { 
  Store, Briefcase, CheckCircle2, XCircle, AlertCircle, MapPin, Phone, Mail, 
  RefreshCw, Search, PenTool, FileText, FileBadge, FileX, Loader2, Clock, Calendar,
  Download, MessageSquare, Send, X, Upload
} from 'lucide-vue-next'

import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

const requests = ref([])
const loading = ref(true)
const searchQuery = ref('')
const isProcessing = ref(false)
const activeTab = ref('pending') 
const currentSupplierId = ref(null) 

const selectedRequest = ref(null)
const showViewDialog = ref(false)
const showOfficialDocDialog = ref(false)

const showProposeDateDialog = ref(false)
const proposedDate = ref('')

const showChatPanel = ref(false)
const chatMessages = ref([])
const newMessage = ref('')
const chatPartnerId = ref(null)
const chatPartnerName = ref('')
const isChatLoading = ref(false)
const isSending = ref(false)
const chatScrollContainer = ref(null)

const showRejectDialog = ref(false)
const rejectReason = ref('')

const signatureMode = ref('draw')
const uploadedSigUrl = ref('')
const uploadedSigBase64 = ref('')
const showSignatureDialog = ref(false)
const hasDrawn = ref(false)
const sigFileInput = ref(null)

const signatureCanvas = ref(null)
const canvasContainer = ref(null)
let ctx = null
let isDrawing = false

const isDateValid = computed(() => {
    if (!proposedDate.value) return false;
    const selected = new Date(proposedDate.value);
    const minDate = new Date();
    minDate.setMonth(minDate.getMonth() + 1);
    return selected >= minDate;
});

const setupWebsocket = (supplierId) => {
    if (!supplierId) return;
    echo.leave(`supplier.${supplierId}.requests`);
    echo.private(`supplier.${supplierId}.requests`)
        .listen('.PartnershipRequest.Updated', (e) => {
            const req = e.request;
            const index = requests.value.findIndex(r => r.id === req.id);
            if (index !== -1) requests.value.splice(index, 1, req);
            else { requests.value.unshift(req); toast.info('Partnership Update', { description: 'A distributor updated a partnership request.' }); }
        })
        .listen('.SupplierRequest.Updated', (e) => {
            const req = e.request;
            const index = requests.value.findIndex(r => r.id === req.id);
            if (index !== -1) requests.value.splice(index, 1, req);
            else requests.value.unshift(req);
        })
        .listen('.PartnershipMessageSent', (e) => {
            if (showChatPanel.value && (e.message.sender_id === chatPartnerId.value || e.message.receiver_id === chatPartnerId.value)) {
                if (!chatMessages.value.find(m => m.id === e.message.id)) { chatMessages.value.push(e.message); scrollToBottom(); }
            } else if (!showChatPanel.value && e.message.receiver_id === currentSupplierId.value) {
                toast.info('New Message', { description: 'You received a new message from a partner.' });
            }
        });
}

const openChat = (req) => {
    if (!req) return;
    if (showViewDialog.value) showViewDialog.value = false;
    chatPartnerId.value = req.distributor_id || req.distributor?.id;
    chatPartnerName.value = req.distributor?.company_name || req.distributor?.first_name || 'Distributor';
    showChatPanel.value = true;
    fetchChatMessages();
}

const fetchChatMessages = async () => {
    isChatLoading.value = true;
    try {
        const res = await api.get(`/supplier/distributor-requests/${chatPartnerId.value}/chat`);
        if (res.data.success) { chatMessages.value = res.data.data; scrollToBottom(); }
    } catch (e) { toast.error('Failed to load chat'); } finally { isChatLoading.value = false; }
}

const sendMessage = async () => {
    if (!newMessage.value.trim() || isSending.value) return;
    isSending.value = true;
    try {
        const res = await api.post(`/supplier/distributor-requests/${chatPartnerId.value}/chat`, { message: newMessage.value });
        if (res.data.success) {
            if (!chatMessages.value.find(m => m.id === res.data.data.id)) { chatMessages.value.push(res.data.data); scrollToBottom(); }
            newMessage.value = '';
        }
    } catch (e) { toast.error('Failed to send message'); } finally { isSending.value = false; }
}

const closeChat = () => { showChatPanel.value = false; chatPartnerId.value = null; }
const scrollToBottom = () => { nextTick(() => { if (chatScrollContainer.value) { chatScrollContainer.value.scrollTop = chatScrollContainer.value.scrollHeight; } }); }
const formatTime = (dateStr) => { if (!dateStr) return ''; return new Date(dateStr).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' }) }
const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'

const handleSignatureUpload = (event) => {
  const file = event.target.files[0]
  if (!file) return
  if (!file.type.startsWith('image/')) { toast.error('Please select an image file.'); return }
  const reader = new FileReader()
  reader.onload = (e) => { uploadedSigUrl.value = e.target.result; uploadedSigBase64.value = e.target.result }
  reader.readAsDataURL(file)
}

const initCanvas = () => {
  if (!signatureCanvas.value || !canvasContainer.value) return
  signatureCanvas.value.width = canvasContainer.value.clientWidth
  signatureCanvas.value.height = canvasContainer.value.clientHeight
  ctx = signatureCanvas.value.getContext('2d')
  ctx.lineWidth = 3
  ctx.lineCap = 'round'
  ctx.strokeStyle = '#1e3a8a'
}

const getCoordinates = (e, canvasObj) => {
  if (!canvasObj) return { x: 0, y: 0 }
  const rect = canvasObj.getBoundingClientRect()
  const clientX = e.touches && e.touches.length > 0 ? e.touches[0].clientX : e.clientX
  const clientY = e.touches && e.touches.length > 0 ? e.touches[0].clientY : e.clientY
  return { x: clientX - rect.left, y: clientY - rect.top }
}

const startDrawing = (e) => { isDrawing = true; hasDrawn.value = true; const c = getCoordinates(e, signatureCanvas.value); ctx.beginPath(); ctx.moveTo(c.x, c.y) }
const draw = (e) => { if (!isDrawing) return; const c = getCoordinates(e, signatureCanvas.value); ctx.lineTo(c.x, c.y); ctx.stroke() }
const stopDrawing = () => { isDrawing = false; if(ctx) ctx.closePath() }
const clearSignature = () => { if (!ctx) return; ctx.clearRect(0, 0, signatureCanvas.value.width, signatureCanvas.value.height); hasDrawn.value = false }

watch(showSignatureDialog, async (newVal) => { 
  if (newVal) { 
    hasDrawn.value = false; uploadedSigUrl.value = ''; uploadedSigBase64.value = '';
    if(signatureMode.value === 'draw') { await nextTick(); setTimeout(initCanvas, 50) }
  }
})
watch(signatureMode, async (newVal) => { if (newVal === 'draw') { await nextTick(); setTimeout(initCanvas, 50) } })

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/supplier/distributor-requests')
    if (response.data.success) {
      requests.value = response.data.data
      if (response.data.supplier_id) { currentSupplierId.value = response.data.supplier_id; setupWebsocket(response.data.supplier_id); }
    }
  } catch (error) { toast.error('Failed to load records') } finally { loading.value = false }
}

const pendingRequestsCount = computed(() => requests.value.filter(r => r.status === 'pending_supplier').length)
const activeRequestsCount = computed(() => requests.value.filter(r => r.status === 'active').length)
const archivedRequestsCount = computed(() => requests.value.filter(r => r.status === 'terminated' || r.status === 'disconnected').length)

const filteredRequests = computed(() => {
  let list = []
  if (activeTab.value === 'pending') list = requests.value.filter(r => r.status === 'pending_supplier')
  else if (activeTab.value === 'active') list = requests.value.filter(r => r.status === 'active')
  else if (activeTab.value === 'archived') list = requests.value.filter(r => r.status === 'terminated' || r.status === 'disconnected')
  
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(req => req.distributor?.company_name?.toLowerCase().includes(q) || req.distributor?.first_name?.toLowerCase().includes(q))
  }
  return list
})

const viewRequest = (req) => { selectedRequest.value = req; showViewDialog.value = true }
const viewOfficialDocument = (req) => { selectedRequest.value = req; showOfficialDocDialog.value = true }

const submitProposeDate = async () => {
    if (!selectedRequest.value || !isDateValid.value) return;
    isProcessing.value = true;
    try {
        const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/propose-date`, { proposed_date: proposedDate.value });
        if (res.data.success) {
            showProposeDateDialog.value = false;
            showViewDialog.value = false;
            toast.success('Proposal Sent', { description: 'The distributor will review your proposed date.' });
            fetchRequests();
        }
    } catch (err) { toast.error('Action Failed') } finally { isProcessing.value = false; }
}

const initiateApprove = () => { showViewDialog.value = false; showSignatureDialog.value = true }
const submitApprove = async () => {
  if (!selectedRequest.value) return
  if (signatureMode.value === 'draw' && !hasDrawn.value) return
  if (signatureMode.value === 'upload' && !uploadedSigBase64.value) return

  isProcessing.value = true
  try {
    const b64 = signatureMode.value === 'draw' ? signatureCanvas.value.toDataURL('image/png') : uploadedSigBase64.value
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/approve`, { signature_image: b64 })
    if (res.data.success) {
      showSignatureDialog.value = false
      activeTab.value = 'active'
      toast.success('Partnership Activated')
      fetchRequests();
    }
  } catch (err) { toast.error('Approval Failed') } finally { isProcessing.value = false }
}

const initiateReject = () => { rejectReason.value = ''; showViewDialog.value = false; showRejectDialog.value = true }
const submitReject = async () => {
  if (!selectedRequest.value) return
  isProcessing.value = true
  try {
    const res = await api.post(`/supplier/distributor-requests/${selectedRequest.value.id}/reject`, { reason: rejectReason.value })
    if (res.data.success) {
      showRejectDialog.value = false
      toast.info('Proposal Declined')
      fetchRequests();
    }
  } catch (err) { toast.error('Action Failed') } finally { isProcessing.value = false }
}

onMounted(() => fetchRequests())
onUnmounted(() => { if (currentSupplierId.value) echo.leave(`supplier.${currentSupplierId.value}.requests`); })
</script>