<template>
  <div class="flex flex-col gap-6 p-4 sm:p-8 min-h-screen relative">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">My Suppliers</h1>
        <p class="text-slate-500 mt-1 text-sm">
          Manage your active supply chain network and monitor performance.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <Button class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white gap-2 shadow-sm" @click="refreshData">
          <RefreshCw :class="{'animate-spin': loading}" class="h-4 w-4" />
          Refresh
        </Button>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
      <div class="relative w-full lg:w-96">
        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
        <Input 
          v-model="searchQuery" 
          placeholder="Search by company, category..." 
          class="pl-9 border-slate-200 focus-visible:ring-indigo-500 w-full"
        />
      </div>
      <div class="flex flex-col sm:flex-row items-center gap-2 w-full lg:w-auto">
        <Select v-model="statusFilter">
          <SelectTrigger class="w-full sm:w-[190px]">
            <SelectValue placeholder="Status" />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">All Statuses</SelectItem>
            <SelectItem value="active">Active</SelectItem>
            <SelectItem value="pending_supplier">Negotiating / Pending Supplier</SelectItem>
          </SelectContent>
        </Select>
      </div>
    </div>

    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card v-for="i in 3" :key="i" class="h-64 animate-pulse bg-slate-100 border-slate-200">
        <CardContent></CardContent>
      </Card>
    </div>

    <div v-else-if="filteredSuppliers.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card 
        v-for="supplier in filteredSuppliers" 
        :key="supplier.id" 
        class="group flex flex-col hover:shadow-md transition-all duration-300 border-slate-200"
      >
        <CardContent class="p-6 flex-grow">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div 
                class="h-14 w-14 rounded-xl flex items-center justify-center text-lg font-bold shadow-sm"
                :class="getAvatarColor(supplier.company_name)"
              >
                {{ getInitials(supplier.company_name) }}
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900 leading-tight group-hover:text-indigo-600 transition-colors">
                  {{ supplier.company_name }}
                </h3>
                <span class="text-xs text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-full mt-1 inline-block">
                  {{ supplier.category }}
                </span>
              </div>
            </div>
            
            <Badge 
              :variant="['active'].includes(supplier.status) ? 'outline' : 'secondary'"
              class="capitalize"
              :class="getStatusColor(supplier.status)"
            >
              {{ supplier.status === 'pending_supplier' ? 'Negotiating' : supplier.status }}
            </Badge>
          </div>

          <div class="grid grid-cols-2 gap-4 py-4 border-t border-b border-slate-50 mb-4">
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Performance</span>
              <div class="flex items-center gap-2">
                <div class="flex items-center text-amber-500">
                  <Star class="h-3.5 w-3.5 fill-current" />
                  <span class="text-sm font-bold ml-1 text-slate-700">{{ supplier.rating }}</span>
                </div>
                <span class="text-xs text-slate-400">/ 5.0</span>
              </div>
            </div>
            <div class="space-y-1">
              <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Fulfillment</span>
              <div class="flex items-center gap-2">
                <Progress :model-value="supplier.fulfillment_rate" class="h-1.5 w-16" />
                <span class="text-xs font-bold text-slate-700">{{ supplier.fulfillment_rate }}%</span>
              </div>
            </div>
          </div>

          <div class="space-y-2.5">
            <div class="flex items-start gap-3">
              <MapPin class="h-4 w-4 text-slate-400 mt-0.5 shrink-0" />
              <span class="text-sm text-slate-600 leading-tight line-clamp-1" :title="supplier.location">
                {{ supplier.location }}
              </span>
            </div>
            <div class="flex items-center gap-3">
              <User class="h-4 w-4 text-slate-400 shrink-0" />
              <span class="text-sm text-slate-600">{{ supplier.contact_person }}</span>
            </div>
            <div class="flex items-center gap-3">
              <Phone class="h-4 w-4 text-slate-400 shrink-0" />
              <span class="text-sm text-slate-600">{{ supplier.phone }}</span>
            </div>
          </div>
        </CardContent>

        <CardFooter class="p-4 bg-slate-50/50 border-t border-slate-100 flex items-center justify-between gap-3">
          <Button 
            variant="outline" 
            class="flex-1 border-slate-200 text-slate-700 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50" 
            @click="openDetails(supplier)"
          >
            <span v-if="supplier.status === 'pending_supplier' && supplier.last_proposed_by === 'supplier'" class="text-amber-600 font-bold">Review Proposal</span>
            <span v-else>View Details</span>
          </Button>
          
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="ghost" size="icon" class="h-9 w-9 text-slate-400 hover:text-slate-700">
                <MoreHorizontal class="h-5 w-5" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
              <DropdownMenuItem @click="openChat(supplier)">
                <MessageSquare class="mr-2 h-4 w-4 text-indigo-600" /> Message
              </DropdownMenuItem>
              <DropdownMenuItem @click="viewAgreement(supplier)">
                <FileText class="mr-2 h-4 w-4" /> View Contract
              </DropdownMenuItem>
              
              <DropdownMenuSeparator v-if="supplier.status === 'active' && (isNearOrEnded(supplier.contract_end_date) || !supplier.contract_end_date)" />

              <DropdownMenuItem 
                v-if="supplier.status === 'active' && (isNearOrEnded(supplier.contract_end_date) || !supplier.contract_end_date)"
                @click="openRenewDialog(supplier)"
                class="text-blue-600 focus:text-blue-600 focus:bg-blue-50"
              >
                <RefreshCw class="mr-2 h-4 w-4" /> Renew Contract
              </DropdownMenuItem>

            </DropdownMenuContent>
          </DropdownMenu>
        </CardFooter>
      </Card>
    </div>

    <div v-else class="flex flex-col items-center justify-center py-16 sm:py-24 text-center bg-white rounded-xl border border-dashed border-slate-300 mx-4 sm:mx-0">
      <div class="bg-slate-50 p-4 rounded-full mb-4">
        <Store class="w-10 h-10 text-slate-400" />
      </div>
      <h3 class="text-lg font-medium text-slate-900">No suppliers found</h3>
      <p class="text-slate-500 max-w-sm mt-2 px-4">
        You don't have any active partnerships matching the criteria.
      </p>
      <Button variant="outline" class="mt-6" @click="resetFilters">
        Clear Filters
      </Button>
    </div>

    <!-- Chat Drawer Overlay -->
    <div v-if="showChatPanel" class="fixed inset-0 bg-slate-900/50 z-[100] transition-opacity" @click="closeChat"></div>

    <!-- Chat Drawer -->
    <div v-if="showChatPanel" class="fixed inset-y-0 right-0 w-full md:w-[400px] bg-white shadow-2xl z-[105] flex flex-col transform transition-transform duration-300">
        <div class="p-4 border-b bg-slate-50 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                    {{ chatPartnerName.charAt(0) }}
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">{{ chatPartnerName }}</h3>
                    <p class="text-xs text-slate-500">Partnership Chat</p>
                </div>
            </div>
            <Button variant="ghost" size="icon" @click="closeChat">
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
                <div v-for="msg in chatMessages" :key="msg.id" class="flex" :class="msg.sender_id === currentDistributorId ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm" :class="msg.sender_id === currentDistributorId ? 'bg-indigo-600 text-white rounded-br-none' : 'bg-white text-slate-800 rounded-bl-none shadow-sm'">
                        {{ msg.message }}
                        <div class="text-[10px] mt-1 opacity-70" :class="msg.sender_id === currentDistributorId ? 'text-indigo-100' : 'text-slate-400'">
                            {{ formatTime(msg.created_at) }}
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="p-4 bg-white border-t">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <Input v-model="newMessage" placeholder="Type a message..." class="flex-1" :disabled="isSending" />
                <Button type="submit" size="icon" class="bg-indigo-600 hover:bg-indigo-700 text-white shrink-0" :disabled="!newMessage.trim() || isSending">
                    <Send class="h-4 w-4" v-if="!isSending" />
                    <Loader2 class="h-4 w-4 animate-spin" v-else />
                </Button>
            </form>
        </div>
    </div>

    <Sheet :open="isSheetOpen" @update:open="isSheetOpen = $event">
      <SheetContent class="w-full sm:max-w-[540px] overflow-y-auto p-0 gap-0 border-l border-slate-200 [&>button]:hidden">
        
        <div class="bg-slate-900 text-white p-6 relative">
           <Button variant="ghost" size="icon" class="absolute right-4 top-4 z-50 text-slate-400 hover:text-white hover:bg-white/10 rounded-full" @click="isSheetOpen = false">
             <X class="h-5 w-5" />
           </Button>
           
           <div class="flex items-center gap-4 mt-2">
              <div class="h-16 w-16 rounded-xl flex items-center justify-center text-xl font-bold bg-white/10 text-white backdrop-blur-sm shadow-inner">
                {{ selectedSupplier ? getInitials(selectedSupplier.company_name) : '' }}
              </div>
              <div class="pr-8">
                <h2 class="text-xl font-bold tracking-tight leading-snug">{{ selectedSupplier?.company_name }}</h2>
                <div class="flex flex-wrap items-center gap-2 mt-2 text-slate-300 text-sm">
                  <Badge variant="outline" class="text-xs border-slate-600 text-slate-300 font-normal">
                    {{ selectedSupplier?.category }}
                  </Badge>
                </div>
              </div>
           </div>
        </div>

        <div class="p-6 space-y-8 bg-white min-h-full" v-if="selectedSupplier">
          
          <div class="grid grid-cols-2 gap-3">
            <Button class="bg-indigo-600 hover:bg-indigo-700 text-white border-0 shadow-sm w-full" @click="openChat(selectedSupplier)">
              <MessageSquare class="mr-2 h-4 w-4" /> Message
            </Button>
            <Button variant="outline" class="bg-white hover:bg-slate-50 text-slate-700 border-slate-200 shadow-sm w-full">
              <FileClock class="mr-2 h-4 w-4" /> History
            </Button>
          </div>

          <div v-if="selectedSupplier.status === 'pending_supplier'" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
             <h4 class="text-sm font-bold text-blue-900 flex items-center gap-2 mb-2">
                 <Calendar class="h-4 w-4" /> Contract Duration Negotiation
             </h4>
             <div class="text-sm text-blue-800 flex flex-col gap-2">
                 <div>
                    <strong>Target End Date:</strong> 
                    <span v-if="selectedSupplier.proposed_end_date || selectedSupplier.contract_end_date">
                        {{ formatDate(selectedSupplier.proposed_end_date || selectedSupplier.contract_end_date) }}
                    </span>
                    <span v-else class="text-amber-600">Not set</span>
                 </div>
                 
                 <div v-if="selectedSupplier.last_proposed_by === 'distributor'">
                    <span class="text-xs font-semibold bg-amber-100 text-amber-800 px-2 py-1 rounded">Awaiting Supplier to accept & sign.</span>
                 </div>
                 <div v-else-if="selectedSupplier.last_proposed_by === 'supplier'">
                    <span class="text-xs font-semibold">The supplier proposed this new end date.</span>
                    <div class="flex gap-2 mt-2">
                        <Button size="sm" class="bg-blue-600 text-white" @click="acceptProposedDate" :disabled="isProcessing">Accept Date</Button>
                        <Button size="sm" variant="outline" class="bg-white" @click="showProposeDateDialog = true">Propose Another</Button>
                    </div>
                 </div>
                 
                 <div v-if="!selectedSupplier.proposed_end_date && !selectedSupplier.contract_end_date" class="mt-2">
                     <Button size="sm" variant="outline" class="bg-white" @click="showProposeDateDialog = true">Propose Date</Button>
                 </div>
             </div>
          </div>

          <div v-if="selectedSupplier.status === 'active'" class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
             <h4 class="text-sm font-bold text-emerald-900 flex items-center gap-2 mb-2">
                 <Calendar class="h-4 w-4" /> Active Contract
             </h4>
             <div class="text-sm text-emerald-800 flex justify-between items-center">
                 <div>
                    <strong>Ends on:</strong> 
                    <span v-if="selectedSupplier.contract_end_date">{{ formatDate(selectedSupplier.contract_end_date) }}</span>
                    <span v-else class="text-slate-600">Not Set (Legacy Contract)</span>
                 </div>
                 <Button v-if="isNearOrEnded(selectedSupplier.contract_end_date) || !selectedSupplier.contract_end_date" size="sm" variant="outline" class="bg-white border-emerald-300 text-emerald-700" @click="openRenewDialog(selectedSupplier)">
                    Renew Contract
                 </Button>
             </div>
          </div>

          <div>
            <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Performance Metrics</h4>
            <div class="grid grid-cols-3 gap-3">
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-slate-900">{{ selectedSupplier.total_orders }}</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">Orders</div>
              </div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-slate-900">₱{{ selectedSupplier.total_spend }}</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">Spend</div>
              </div>
              <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 text-center">
                <div class="text-lg sm:text-2xl font-bold text-emerald-600">{{ selectedSupplier.on_time_rate }}%</div>
                <div class="text-[10px] text-slate-500 font-medium uppercase mt-1">On-Time</div>
              </div>
            </div>
          </div>

          <div>
             <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">Agreements</h4>
             <div 
               @click="viewAgreement(selectedSupplier)"
               class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors cursor-pointer group bg-white shadow-sm"
              >
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-indigo-50 rounded text-indigo-600 group-hover:bg-indigo-100 transition-colors">
                    <FileBadge class="h-5 w-5" />
                  </div>
                  <div>
                    <p class="text-sm font-medium text-slate-900">Partnership Agreement</p>
                    <p class="text-xs mt-0.5">
                      <span v-if="selectedSupplier.is_supplier_signed" class="text-emerald-600 font-semibold flex items-center gap-1">
                        <CheckCircle class="h-3 w-3" /> Fully Signed
                      </span>
                      <span v-else class="text-amber-500 font-semibold flex items-center gap-1">
                        <Clock class="h-3 w-3" /> Pending Signatures
                      </span>
                    </p>
                  </div>
                </div>
                <Button variant="ghost" size="icon" class="h-8 w-8 rounded-full">
                  <Eye class="h-4 w-4 text-slate-400 group-hover:text-indigo-600" />
                </Button>
             </div>
           </div>

        </div>
      </SheetContent>
    </Sheet>

    <Dialog :open="showAgreementDialog" @update:open="showAgreementDialog = $event">
      <DialogContent class="w-[95vw] max-w-6xl h-[95vh] flex flex-col p-0 overflow-hidden bg-slate-50 sm:rounded-xl shadow-2xl">
        <DialogHeader class="p-4 sm:p-6 border-b bg-white shrink-0 shadow-sm z-10">
          <DialogTitle class="flex items-center justify-between w-full pr-6 gap-3 text-xl sm:text-2xl">
            <span class="flex items-center gap-2 text-slate-800">
                <FileText class="h-6 w-6 text-indigo-600 shrink-0" />
                <span class="truncate">Partnership Agreement - {{ selectedSupplier?.company_name }}</span>
            </span>
            <Button v-if="selectedSupplier?.is_signed && selectedSupplier?.is_supplier_signed" variant="outline" size="sm" @click="downloadDocument" class="w-full sm:w-auto text-indigo-600 hover:text-indigo-700 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 shrink-0">
                <Download class="h-4 w-4 mr-2" /> Download Document
            </Button>
          </DialogTitle>
        </DialogHeader>
        
        <div class="flex-1 overflow-hidden p-4 sm:p-6 relative bg-slate-100 min-h-[200px]">
            <iframe 
              v-if="selectedSupplier?.agreement_url" 
              :src="selectedSupplier.agreement_url" 
              class="w-full h-full bg-white rounded-lg shadow-sm border border-slate-200"
            ></iframe>
            <div v-else class="flex flex-col h-full items-center justify-center text-slate-500 bg-white rounded-lg border border-slate-200 shadow-sm">
                <FileX class="h-12 w-12 text-slate-300 mb-2" />
                <p>No digital agreement document was found.</p>
            </div>
        </div>
        
        <div class="p-4 sm:p-6 border-t bg-white shrink-0 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)] z-10 overflow-y-auto max-h-[50vh]">
            <h4 class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-widest text-center mb-3 sm:mb-4">Official Signatures</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 max-w-4xl mx-auto">
               <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-indigo-100 text-indigo-700 border border-indigo-200">Distributor</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedSupplier?.distributor_signature_url" :src="selectedSupplier.distributor_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else class="text-xs sm:text-sm text-slate-400 italic">Signature not found</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800">Your Business</p>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle class="h-3 w-3 text-emerald-500" /> {{ selectedSupplier?.signed_at || 'N/A' }}
                    </p>
                </div>

                <div class="flex flex-col items-center justify-center border border-slate-200 p-3 sm:p-4 rounded-xl bg-slate-50 relative">
                    <Badge class="absolute -top-3 left-4 bg-blue-100 text-blue-700 border border-blue-200">Supplier</Badge>
                    <div class="h-16 sm:h-20 w-full flex items-center justify-center mt-2">
                        <img v-if="selectedSupplier?.supplier_signature_url" :src="selectedSupplier.supplier_signature_url" class="max-h-full object-contain mix-blend-multiply opacity-90" />
                        <span v-else class="text-xs sm:text-sm text-amber-500 italic flex items-center gap-1"><Clock class="h-3 w-3"/> Pending...</span>
                    </div>
                    <Separator class="my-2 sm:my-3 w-3/4 bg-slate-300" />
                    <p class="text-xs sm:text-sm font-bold text-slate-800 truncate max-w-[150px]">{{ selectedSupplier?.company_name }}</p>
                    <p class="text-[10px] sm:text-xs text-slate-500 mt-1 flex items-center gap-1">
                        <CheckCircle v-if="selectedSupplier?.supplier_signed_at" class="h-3 w-3 text-emerald-500" /> 
                        <Clock v-else class="h-3 w-3 text-amber-500" />
                        {{ selectedSupplier?.supplier_signed_at ? selectedSupplier.supplier_signed_at : 'Pending...' }}
                    </p>
                </div>
            </div>
        </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showProposeDateDialog" @update:open="showProposeDateDialog = $event">
      <DialogContent>
          <DialogHeader>
              <DialogTitle>Propose New Contract Date</DialogTitle>
              <DialogDescription>Select a new end date for this partnership. The supplier will need to review it.</DialogDescription>
          </DialogHeader>
          <div class="py-4">
              <label class="text-sm font-semibold mb-2 block">New End Date</label>
              <Input type="date" v-model="proposedDate" />
              <p class="text-xs text-slate-500 mt-2">Date must be at least 1 month from today.</p>
          </div>
          <div class="flex justify-end gap-2">
              <Button variant="outline" @click="showProposeDateDialog = false">Cancel</Button>
              <Button class="bg-blue-600 text-white" :disabled="!isDateValid(proposedDate) || isProcessing" @click="submitProposeDate">
                  <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Send Proposal
              </Button>
          </div>
      </DialogContent>
    </Dialog>

    <Dialog :open="showRenewDialog" @update:open="showRenewDialog = $event">
      <DialogContent>
          <DialogHeader>
              <DialogTitle>Renew Contract</DialogTitle>
              <DialogDescription>Set a new contract duration. The supplier will be required to sign the renewed agreement.</DialogDescription>
          </DialogHeader>
          <div class="py-4">
              <label class="text-sm font-semibold mb-2 block">New Expiration Date</label>
              <Input type="date" v-model="renewDate" />
              <p class="text-xs text-slate-500 mt-2">Must be at least 1 month from today.</p>
          </div>
          <div class="flex justify-end gap-2">
              <Button variant="outline" @click="showRenewDialog = false">Cancel</Button>
              <Button class="bg-blue-600 text-white" :disabled="!isDateValid(renewDate) || isProcessing" @click="submitRenewal">
                  <Loader2 v-if="isProcessing" class="mr-2 h-4 w-4 animate-spin" /> Initiate Renewal
              </Button>
          </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch, onUnmounted } from 'vue' 
import api from '@/utils/axios'
import echo from '@/utils/websocket.js' 
import { toast } from 'vue-sonner'
import { 
  Search, MapPin, Star, MoreHorizontal, User, Mail, Phone, Store, FileBadge, Eye, X, MessageSquare, FileClock,
  Ban, FileText, FileX, RefreshCw, RefreshCcw, CheckCircle, AlertCircle, Loader2, PenTool, Download, Clock, Send, Calendar
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { Separator } from '@/components/ui/separator'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Sheet, SheetContent } from '@/components/ui/sheet'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'

const suppliers = ref([])
const loading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('all')
const isSheetOpen = ref(false)
const selectedSupplier = ref(null)
const currentDistributorId = ref(null) 

const isProcessing = ref(false)
const showProposeDateDialog = ref(false)
const proposedDate = ref('')

const showRenewDialog = ref(false)
const renewDate = ref('')

const showChatPanel = ref(false)
const chatMessages = ref([])
const newMessage = ref('')
const chatPartnerId = ref(null)
const chatPartnerName = ref('')
const isChatLoading = ref(false)
const isSending = ref(false)
const chatScrollContainer = ref(null)

const showAgreementDialog = ref(false)

const isDateValid = (dateStr) => {
    if (!dateStr) return false;
    const selected = new Date(dateStr);
    const minDate = new Date();
    minDate.setMonth(minDate.getMonth() + 1);
    return selected >= minDate;
};

const isNearOrEnded = (dateStr) => {
    if (!dateStr) return false;
    const end = new Date(dateStr);
    const now = new Date();
    const diff = (end - now) / (1000 * 60 * 60 * 24);
    return diff <= 30;
};

const setupWebsocket = (distributorId) => {
    if (!distributorId) return;
    echo.leave(`distributor.${distributorId}.requests`);
    echo.private(`distributor.${distributorId}.requests`)
        .listen('.SupplierRequest.Updated', (e) => {
            const req = e.request || e;
            const index = suppliers.value.findIndex(s => s.supplier_user_id === req.supplier_id);
            if (index !== -1) {
                const supplier = suppliers.value[index];
                Object.assign(supplier, {
                  status: req.status,
                  is_signed: !!req.distributor_signature_url,
                  is_supplier_signed: !!req.supplier_signature_url,
                  distributor_signature_url: req.distributor_signature_url,
                  supplier_signature_url: req.supplier_signature_url,
                  agreement_url: req.agreement_url,
                  contract_end_date: req.contract_end_date,
                  proposed_end_date: req.proposed_end_date,
                  last_proposed_by: req.last_proposed_by
                })
                if (selectedSupplier.value && selectedSupplier.value.supplier_user_id === req.supplier_id) { selectedSupplier.value = { ...supplier }; }
            } else { fetchSuppliers(); }
        })
        .listen('.PartnershipMessageSent', (e) => {
            if (showChatPanel.value && (e.message.sender_id === chatPartnerId.value || e.message.receiver_id === chatPartnerId.value)) {
                if (!chatMessages.value.find(m => m.id === e.message.id)) { chatMessages.value.push(e.message); scrollToBottom(); }
            }
        });
}

const openChat = (supplier) => {
    chatPartnerId.value = supplier.supplier_user_id; 
    chatPartnerName.value = supplier.company_name || 'Supplier';
    showChatPanel.value = true;
    fetchChatMessages();
}

const fetchChatMessages = async () => {
    isChatLoading.value = true;
    try {
        const res = await api.get(`/distributor/partnered-suppliers/${chatPartnerId.value}/chat`);
        if (res.data.success) { chatMessages.value = res.data.data; scrollToBottom(); }
    } catch (e) { toast.error('Failed to load chat'); } finally { isChatLoading.value = false; }
}

const sendMessage = async () => {
    if (!newMessage.value.trim() || isSending.value) return;
    isSending.value = true;
    try {
        const res = await api.post(`/distributor/partnered-suppliers/${chatPartnerId.value}/chat`, { message: newMessage.value });
        if (res.data.success) {
            if (!chatMessages.value.find(m => m.id === res.data.data.id)) { chatMessages.value.push(res.data.data); scrollToBottom(); }
            newMessage.value = '';
        }
    } catch (e) { toast.error('Failed to send message'); } finally { isSending.value = false; }
}

const closeChat = () => { showChatPanel.value = false; chatPartnerId.value = null; }
const scrollToBottom = () => { nextTick(() => { if (chatScrollContainer.value) { chatScrollContainer.value.scrollTop = chatScrollContainer.value.scrollHeight; } }); }

const getInitials = (name) => name ? name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() : '??'
const getAvatarColor = (name) => 'bg-blue-100 text-blue-700'
const getStatusColor = (status) => status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'

const formatTime = (dateStr) => { if (!dateStr) return ''; return new Date(dateStr).toLocaleTimeString('en-PH', { hour: '2-digit', minute: '2-digit' }) }
const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' }) : 'N/A'

const fetchSuppliers = async () => {
  loading.value = true
  try {
    const response = await api.get('/distributor/partnered-suppliers')
    if (response.data.success) {
      suppliers.value = response.data.data
      if (response.data.distributor_id) { currentDistributorId.value = response.data.distributor_id; setupWebsocket(response.data.distributor_id); }
      if (selectedSupplier.value) {
         const updated = suppliers.value.find(s => s.id === selectedSupplier.value.id)
         if (updated) selectedSupplier.value = updated
      }
    }
  } catch (error) { toast.error('Failed to load suppliers') } finally { loading.value = false }
}

const filteredSuppliers = computed(() => {
  return suppliers.value.filter(s => {
    const searchLower = searchQuery.value.toLowerCase()
    const matchesSearch = s.company_name.toLowerCase().includes(searchLower) || s.contact_person.toLowerCase().includes(searchLower)
    const matchesStatus = statusFilter.value === 'all' || s.status === statusFilter.value
    return matchesSearch && matchesStatus
  })
})

const openDetails = (supplier) => { selectedSupplier.value = supplier; isSheetOpen.value = true }
const refreshData = () => { fetchSuppliers() }
const resetFilters = () => { searchQuery.value = ''; statusFilter.value = 'all' }

const submitProposeDate = async () => {
    if (!selectedSupplier.value || !isDateValid(proposedDate.value)) return;
    isProcessing.value = true;
    try {
        const res = await api.post(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/propose-date`, { proposed_date: proposedDate.value });
        if (res.data.success) {
            showProposeDateDialog.value = false;
            toast.success('Proposal Sent', { description: 'The supplier will review your proposed date.' });
            fetchSuppliers();
        }
    } catch (err) { toast.error('Action Failed') } finally { isProcessing.value = false; }
}

const acceptProposedDate = async () => {
    if (!selectedSupplier.value) return;
    isProcessing.value = true;
    try {
        const res = await api.post(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/accept-proposed-date`);
        if (res.data.success) {
            toast.success('Date Accepted', { description: 'The supplier will now be prompted to sign the finalized agreement.' });
            fetchSuppliers();
        }
    } catch (err) { toast.error('Action Failed') } finally { isProcessing.value = false; }
}

const openRenewDialog = (supplier) => {
    selectedSupplier.value = supplier;
    renewDate.value = '';
    showRenewDialog.value = true;
}

const submitRenewal = async () => {
    if (!selectedSupplier.value || !isDateValid(renewDate.value)) return;
    isProcessing.value = true;
    try {
        const res = await api.post(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/renew-contract`, { contract_end_date: renewDate.value });
        if (res.data.success) {
            showRenewDialog.value = false;
            isSheetOpen.value = false;
            toast.success('Contract Renewed', { description: 'Partnership is pending supplier signature for the new duration.' });
            fetchSuppliers();
        }
    } catch (err) { toast.error('Action Failed') } finally { isProcessing.value = false; }
}

const viewAgreement = (supplier) => {
  selectedSupplier.value = supplier
  showAgreementDialog.value = true
}

const downloadDocument = async () => {
    if (!selectedSupplier.value?.agreement_url) return;
    const toastId = toast.loading('Preparing Official Document...');
    try {
        const response = await api.get(`/distributor/partnered-suppliers/${selectedSupplier.value.id}/agreement-raw`);
        if (!response.data.success) throw new Error("Failed to load document content.");

        let htmlContent = response.data.html;
        let sigHtml = `<div style="margin-top: 60px; padding-top: 30px; border-top: 2px solid #cbd5e1; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;"><h3 style="text-align: center;">Official Cryptographic Signatures</h3><div style="display: flex; justify-content: space-around; margin-top: 40px;">`;
        if (selectedSupplier.value.distributor_signature_url) {
            sigHtml += `<div style="text-align: center; width: 40%;"><p>Distributor</p><img src="${selectedSupplier.value.distributor_signature_url}" style="max-height: 100px; max-width: 100%;" /><hr /></div>`;
        }
        if (selectedSupplier.value.supplier_signature_url) {
            sigHtml += `<div style="text-align: center; width: 40%;"><p>Supplier</p><img src="${selectedSupplier.value.supplier_signature_url}" style="max-height: 100px; max-width: 100%;" /><hr /></div>`;
        }
        sigHtml += `</div></div>`;
        if (htmlContent.includes('</body>')) htmlContent = htmlContent.replace('</body>', sigHtml + '</body>'); else htmlContent += sigHtml;

        const blob = new Blob([htmlContent], { type: 'text/html' });
        const blobUrl = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = blobUrl; link.download = `Agreement_${selectedSupplier.value.company_name.replace(/\s+/g, '_')}.html`;
        document.body.appendChild(link); link.click(); document.body.removeChild(link);
        setTimeout(() => URL.revokeObjectURL(blobUrl), 10000);
        toast.success('Document Downloaded', { id: toastId });
    } catch (e) { toast.error('Download Failed', { id: toastId }); }
}

onMounted(() => { fetchSuppliers() })
onUnmounted(() => { if (currentDistributorId.value) echo.leave(`distributor.${currentDistributorId.value}.requests`); })
</script>