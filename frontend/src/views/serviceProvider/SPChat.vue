<template>
  <div class="h-[calc(100dvh-4rem)] md:h-[calc(100vh-6rem)] min-h-[500px] md:min-h-[600px] p-0 md:p-4 text-slate-200">
    
    <div class="max-w-7xl mx-auto h-full flex md:border border-slate-800 md:rounded-2xl overflow-hidden bg-slate-900 shadow-2xl shadow-black/50">
      
      <div class="w-full md:w-80 lg:w-[380px] flex-shrink-0 md:border-r border-slate-800 bg-slate-950 flex flex-col transition-all duration-300" 
           :class="{ 'hidden md:flex': showMobileChat }">
        
        <div class="h-14 md:h-16 px-3 md:px-4 flex items-center justify-between border-b border-slate-800 bg-slate-950 shrink-0">
          <h2 class="text-lg font-bold text-white tracking-tight">Messages</h2>
          <Button variant="ghost" size="icon" class="text-slate-400 hover:text-white hover:bg-slate-800 rounded-full h-8 w-8 md:h-10 md:w-10">
            <MoreVertical class="w-4 h-4 md:w-5 md:h-5" />
          </Button>
        </div>

        <div class="p-3 border-b border-slate-800 shrink-0">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
            <Input 
              v-model="searchQuery"
              placeholder="Search conversations..." 
              class="w-full pl-9 bg-slate-900 border-slate-800 text-slate-200 placeholder:text-slate-500 focus-visible:ring-blue-500 rounded-xl h-10 text-sm"
            />
          </div>
        </div>

        <div class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar">
          <div v-if="isLoadingContacts" class="flex flex-col items-center justify-center py-10 space-y-3">
             <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
             <p class="text-xs text-slate-500">Loading chats...</p>
          </div>

          <div v-else-if="filteredContacts.length === 0" class="flex flex-col items-center justify-center py-10 text-center px-4">
             <p class="text-sm text-slate-500">No active clients found in verifying or higher stages.</p>
          </div>

          <div 
            v-else
            v-for="contact in filteredContacts" 
            :key="contact.id"
            @click="selectContact(contact)"
            class="flex items-center p-3 cursor-pointer transition-colors border-b border-slate-800/50 hover:bg-slate-800/50 w-full"
            :class="{ 'bg-slate-800/80 border-l-2 border-l-blue-500': activeContact?.id === contact.id }"
          >
            <div class="relative mr-3 shrink-0">
              <Avatar class="h-10 w-10 md:h-12 md:w-12 border border-slate-700">
                <AvatarFallback class="bg-gradient-to-br from-blue-600 to-purple-600 text-white font-bold text-sm md:text-base">
                  {{ getInitials(contact.name) }}
                </AvatarFallback>
              </Avatar>
              <div 
                class="absolute bottom-0 right-0 w-2.5 h-2.5 md:w-3 md:h-3 rounded-full border-2 border-slate-950"
                :class="contact.status === 'online' ? 'bg-emerald-500' : 'bg-slate-500'"
              ></div>
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline mb-0.5 md:mb-1">
                <h3 class="text-sm font-semibold text-slate-200 truncate pr-2">{{ contact.name }}</h3>
                <span class="text-[10px] md:text-xs text-slate-500 shrink-0">{{ contact.time }}</span>
              </div>
              <div class="flex justify-between items-center">
                <p class="text-xs text-slate-400 truncate pr-2" :class="{'text-slate-300 font-medium': contact.unread > 0}">
                  {{ contact.lastMessage }}
                </p>
                <Badge v-if="contact.unread > 0" class="bg-blue-600 hover:bg-blue-600 text-white rounded-full h-4 min-w-[16px] md:h-5 md:min-w-[20px] flex items-center justify-center px-1 shrink-0 text-[9px] md:text-[10px]">
                  {{ contact.unread }}
                </Badge>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-1 flex flex-col bg-slate-900 relative w-full h-full" :class="{ 'hidden md:flex': !showMobileChat }">
        
        <div v-if="activeContact" class="h-14 md:h-16 px-2 md:px-4 flex items-center justify-between border-b border-slate-800 bg-slate-950/50 backdrop-blur-sm shrink-0 z-10">
          <div class="flex items-center min-w-0 flex-1">
            <Button variant="ghost" size="icon" class="md:hidden mr-1 md:mr-2 text-slate-400 hover:text-white hover:bg-slate-800 shrink-0 h-8 w-8" @click="showMobileChat = false">
              <ArrowLeft class="w-4 h-4 md:w-5 md:h-5" />
            </Button>
            
            <div class="relative mr-2 md:mr-3 shrink-0">
              <Avatar class="h-8 w-8 md:h-10 md:w-10 border border-slate-700">
                <AvatarFallback class="bg-gradient-to-br from-blue-600 to-purple-600 text-white text-xs md:text-sm font-bold">
                  {{ getInitials(activeContact.name) }}
                </AvatarFallback>
              </Avatar>
              <div class="absolute bottom-0 right-0 w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border-2 border-slate-950 bg-emerald-500"></div>
            </div>
            
            <div class="min-w-0 pr-2">
              <h2 class="text-sm md:text-base font-bold text-white leading-tight truncate">{{ activeContact.name }}</h2>
              <p class="text-[10px] md:text-xs text-slate-400 truncate">{{ activeContact.jobTitle }}</p>
            </div>
          </div>
          
          <div class="flex items-center gap-0.5 sm:gap-1 md:gap-2 shrink-0">
            <Button variant="ghost" size="icon" class="text-slate-400 hover:text-blue-400 hover:bg-blue-900/20 rounded-full h-8 w-8 md:h-9 md:w-9">
              <Phone class="w-3.5 h-3.5 md:w-4 md:h-4" />
            </Button>
            <div class="w-px h-5 md:h-6 bg-slate-800 mx-0.5 md:mx-1 hidden sm:block"></div>
            <Button variant="ghost" size="icon" class="hidden sm:inline-flex text-slate-400 hover:text-white hover:bg-slate-800 rounded-full h-8 w-8 md:h-9 md:w-9">
              <Info class="w-4 h-4 md:w-5 md:h-5" />
            </Button>
          </div>
        </div>

        <div 
          v-if="activeContact"
          ref="chatContainer" 
          class="flex-1 overflow-y-auto p-3 md:p-4 custom-scrollbar space-y-4 md:space-y-6 flex flex-col"
        >
          <div v-if="isLoadingMessages" class="flex justify-center my-4">
             <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-slate-400"></div>
          </div>

          <div 
            v-for="(message, index) in activeMessages" 
            :key="message.id"
            class="flex w-full shrink-0"
            :class="message.sender === 'me' ? 'justify-end' : 'justify-start'"
          >
            <Avatar v-if="message.sender !== 'me'" class="h-6 w-6 md:h-8 md:w-8 mr-1.5 md:mr-2 shrink-0 self-end mb-1 border border-slate-700 hidden sm:flex">
               <AvatarFallback class="bg-gradient-to-br from-blue-600 to-purple-600 text-white text-[10px] md:text-xs">
                 {{ getInitials(activeContact.name) }}
               </AvatarFallback>
            </Avatar>

            <div class="flex flex-col max-w-[95%] sm:max-w-[85%] md:max-w-[75%]" :class="message.sender === 'me' ? 'items-end' : 'items-start'">
              
              <div v-if="message.type === 'text'"
                class="px-3 py-2 md:px-4 md:py-2.5 text-[13px] md:text-sm shadow-sm whitespace-pre-wrap break-words"
                :class="message.sender === 'me' ? 'bg-blue-600 text-white rounded-2xl rounded-tr-sm' : 'bg-slate-800 text-slate-200 rounded-2xl rounded-tl-sm border border-slate-700'"
              >
                {{ message.text }}
              </div>

              <div v-else-if="message.type === 'request_summary'" class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow-md w-full max-w-sm sm:max-w-md">
                 <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                    <ClipboardList class="w-4 h-4 text-blue-400" />
                    <span class="text-sm font-bold text-slate-200 uppercase tracking-wider">Client Request Summary</span>
                 </div>
                 <div class="space-y-2 text-xs text-slate-300">
                    <div class="flex justify-between border-b border-slate-700/50 pb-1"><span class="text-slate-500">Service</span> <span class="font-medium text-right">{{ message.payload?.title || 'N/A' }}</span></div>
                    <div class="flex justify-between border-b border-slate-700/50 pb-1"><span class="text-slate-500">Date</span> <span class="font-medium">{{ message.payload?.preferred_date || 'N/A' }}</span></div>
                    <div class="flex justify-between border-b border-slate-700/50 pb-1"><span class="text-slate-500">Time</span> <span class="font-medium text-right">{{ message.payload?.time_preference || 'N/A' }}</span></div>
                    <div class="flex justify-between border-b border-slate-700/50 pb-1"><span class="text-slate-500">Contact</span> <span class="font-medium text-right">{{ message.payload?.contact_number || 'N/A' }}</span></div>
                    <div class="flex justify-between border-b border-slate-700/50 pb-1 gap-4"><span class="text-slate-500 shrink-0">Location</span> <span class="font-medium text-right truncate">{{ message.payload?.address || 'N/A' }}</span></div>
                    <div class="mt-2 text-slate-400 p-2 bg-slate-900/50 rounded break-words italic">"{{ message.payload?.description || 'N/A' }}"</div>
                 </div>
              </div>

              <div v-else-if="message.type === 'official_deal'" class="bg-gradient-to-br from-indigo-900/50 to-blue-900/50 border border-blue-500/50 rounded-xl p-4 shadow-lg shadow-blue-900/20 w-full max-w-sm sm:max-w-md">
                 <div class="flex items-center justify-between mb-3 border-b border-blue-500/30 pb-2">
                    <div class="flex items-center gap-2">
                      <ShieldCheck class="w-5 h-5 text-emerald-400" />
                      <span class="text-sm font-bold text-white uppercase tracking-wider">Official Deal Offer</span>
                    </div>
                 </div>
                 <div class="space-y-2 text-xs text-slate-200 bg-slate-900/50 p-3 rounded-lg border border-slate-800 shadow-inner">
                    <div class="flex justify-between items-center mb-1">
                       <span class="text-slate-400 font-medium">Final Price</span> 
                       <span class="font-black text-emerald-400 text-xl tracking-tight">₱{{ Number(message.payload?.price || 0).toLocaleString() }}</span>
                    </div>
                    <div class="h-px bg-slate-700/50 my-1"></div>
                    <div class="flex justify-between"><span class="text-slate-400">Execution Date</span> <span class="font-medium">{{ message.payload?.preferred_date || 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Arrival Time</span> <span class="font-medium">{{ message.payload?.time_preference || 'N/A' }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">Contact</span> <span class="font-medium">{{ message.payload?.contact_number || 'N/A' }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-slate-400 shrink-0">Address</span> <span class="text-right font-medium line-clamp-2">{{ message.payload?.address || 'N/A' }}</span></div>
                    <div class="mt-2 text-slate-300 break-words border-t border-slate-700/50 pt-2">{{ message.payload?.description || 'N/A' }}</div>
                 </div>

                 <div v-if="message.sender !== 'me' && message.payload?.deal_status === 'pending'" class="mt-3 flex gap-2">
                    <Button size="sm" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-lg shadow-emerald-900/20">Accept Deal</Button>
                    <Button size="sm" variant="outline" class="flex-1 border-red-500/30 text-red-400 hover:bg-red-500/10">Decline</Button>
                 </div>
                 
                 <div v-else class="mt-3 text-center text-xs font-bold" :class="{ 'text-blue-300 animate-pulse': message.payload?.deal_status === 'pending' || !message.payload?.deal_status, 'text-emerald-400': message.payload?.deal_status === 'ongoing', 'text-red-400': message.payload?.deal_status === 'declined' }">
                    <span v-if="message.payload?.deal_status === 'pending' || !message.payload?.deal_status">Waiting for client's approval...</span>
                    <span v-else-if="message.payload?.deal_status === 'ongoing'">✅ Client Accepted the Deal!</span>
                    <span v-else-if="message.payload?.deal_status === 'declined'">❌ Client Declined the Deal.</span>
                 </div>
              </div>
              
              <div class="flex items-center mt-1 space-x-1" :class="message.sender === 'me' ? 'justify-end' : 'justify-start sm:ml-1'">
                <span class="text-[9px] md:text-[10px] text-slate-500">{{ message.time }}</span>
                <template v-if="message.sender === 'me'">
                  <Check v-if="message.status === 'sent'" class="w-2.5 h-2.5 md:w-3 md:h-3 text-slate-500" />
                  <CheckCheck v-else-if="message.status === 'read'" class="w-2.5 h-2.5 md:w-3 md:h-3 text-blue-500" />
                </template>
              </div>
            </div>
          </div>
          <div class="h-2 shrink-0"></div>
        </div>

        <div v-else class="flex-1 flex flex-col items-center justify-center text-slate-500 p-6 md:p-8 text-center w-full">
          <div class="w-16 h-16 md:w-20 md:h-20 bg-slate-800 rounded-full flex items-center justify-center mb-4">
            <ImageIcon class="w-8 h-8 md:w-10 md:h-10 text-slate-600" />
          </div>
          <h3 class="text-base md:text-lg font-medium text-slate-300 mb-2">Select a conversation</h3>
          <p class="text-xs md:text-sm max-w-[250px] md:max-w-sm">Choose a client from the sidebar to view their service request and start messaging.</p>
        </div>

        <div v-if="activeContact" class="p-2 md:p-3 bg-slate-950 border-t border-slate-800 shrink-0 sticky bottom-0 z-10 w-full">
          <div class="flex items-end gap-1 md:gap-2 bg-slate-900 border border-slate-800 p-1.5 md:p-2 rounded-xl md:rounded-2xl focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all">
            
            <DropdownMenu>
              <DropdownMenuTrigger as-child>
                <Button variant="ghost" size="icon" class="text-slate-400 hover:text-white hover:bg-slate-800 rounded-full shrink-0 h-8 w-8 md:h-10 md:w-10">
                  <Paperclip class="w-4 h-4 md:w-5 md:h-5" />
                </Button>
              </DropdownMenuTrigger>
              <DropdownMenuContent class="bg-slate-800 border-slate-700 text-slate-200 mb-2 ml-2 w-56 rounded-xl shadow-xl shadow-black/50">
                 <div class="px-3 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-800/50 rounded-t-xl border-b border-slate-700">Quick Actions</div>
                 <div class="p-1">
                   <DropdownMenuItem class="cursor-pointer focus:bg-slate-700 py-2.5 rounded-lg" @click="sendRequestSummary">
                      <ClipboardList class="w-4 h-4 mr-2 text-blue-400" /> 
                      <span class="font-medium text-sm">Send Request Details</span>
                   </DropdownMenuItem>
                   <DropdownMenuItem class="cursor-pointer focus:bg-slate-700 py-2.5 rounded-lg mt-1" @click="openOfficialDealModal">
                      <ShieldCheck class="w-4 h-4 mr-2 text-emerald-400" /> 
                      <span class="font-medium text-sm">Create Official Deal</span>
                   </DropdownMenuItem>
                 </div>
              </DropdownMenuContent>
            </DropdownMenu>

            <textarea 
              v-model="newMessage"
              @keydown.enter.prevent="sendTextMessage"
              placeholder="Type your message..." 
              class="flex-1 max-h-24 md:max-h-32 min-h-[32px] md:min-h-[40px] bg-transparent border-0 text-slate-200 placeholder:text-slate-500 focus:ring-0 resize-none py-1.5 md:py-2 text-[13px] md:text-sm custom-scrollbar w-full overflow-y-auto pl-2"
              rows="1"
            ></textarea>

            <Button 
              @click="sendTextMessage"
              :disabled="!newMessage.trim() || isSending"
              class="shrink-0 rounded-full h-8 w-8 md:h-10 md:w-10 p-0 transition-all mb-0.5 md:mb-0"
              :class="newMessage.trim() ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-md' : 'bg-slate-800 text-slate-500'"
            >
              <Send v-if="!isSending" class="w-3.5 h-3.5 md:w-4 md:h-4 ml-0.5 md:ml-1" />
              <div v-else class="animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>
            </Button>
          </div>
          <div class="text-center mt-1 md:mt-2 hidden sm:block">
             <span class="text-[9px] md:text-[10px] text-slate-500">Press <kbd class="px-1 bg-slate-800 rounded border border-slate-700 font-sans">Enter</kbd> to send</span>
          </div>
        </div>

      </div>
    </div>

    <Dialog v-model:open="showDealModal">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 w-[95vw] max-w-[600px] rounded-3xl overflow-hidden p-0 max-h-[90vh] flex flex-col shadow-2xl shadow-black/80">
        
        <div class="px-6 py-6 border-b border-slate-800 bg-slate-950/50 shrink-0">
          <DialogTitle class="text-xl md:text-2xl font-bold flex items-center gap-2 tracking-tight">
             <ShieldCheck class="w-6 h-6 text-emerald-400" />
             Create Official Deal Offer
          </DialogTitle>
          <p class="text-slate-400 text-sm mt-2">Review and finalize the service details below to send the official contract terms to <span class="font-semibold text-white">{{ activeContact?.name }}</span>.</p>
        </div>
        
        <div class="px-6 py-6 overflow-y-auto custom-scrollbar flex-1 space-y-6">
           
           <div class="bg-blue-900/20 border border-blue-800/50 p-4 rounded-2xl flex items-center justify-between">
              <div>
                 <p class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-1.5">Final Price / Cost (₱) <span class="text-red-500">*</span></p>
                 <Input 
                   type="number" 
                   min="0"
                   step="any"
                   v-model="dealForm.price" 
                   @keydown="preventInvalidChars"
                   class="bg-slate-950 border-blue-800/50 focus:border-blue-500 focus:ring-blue-500 text-lg font-bold w-48 h-12 rounded-xl" 
                 />
              </div>
           </div>

           <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
              <div class="space-y-2">
                 <Label class="text-slate-300 text-xs font-bold uppercase tracking-wider">Execution Date <span class="text-red-500">*</span></Label>
                 <Input type="date" v-model="dealForm.preferred_date" class="bg-slate-950 border-slate-700 focus:border-blue-500 focus:ring-blue-500 block w-full h-12 rounded-xl" />
              </div>
              <div class="space-y-2">
                 <Label class="text-slate-300 text-xs font-bold uppercase tracking-wider">Arrival Time <span class="text-red-500">*</span></Label>
                 <Select v-model="dealForm.time_preference">
                    <SelectTrigger class="bg-slate-950 border-slate-700 focus:border-blue-500 focus:ring-blue-500 h-12 rounded-xl">
                      <SelectValue placeholder="Select timeframe" />
                    </SelectTrigger>
                    <SelectContent class="bg-slate-800 border-slate-700 text-white rounded-xl">
                      <SelectItem value="Morning (8AM - 12PM)">Morning (8AM - 12PM)</SelectItem>
                      <SelectItem value="Afternoon (1PM - 5PM)">Afternoon (1PM - 5PM)</SelectItem>
                      <SelectItem value="Flexible">Flexible / Anytime</SelectItem>
                    </SelectContent>
                  </Select>
              </div>
           </div>

           <div class="space-y-2">
              <Label class="text-slate-300 text-xs font-bold uppercase tracking-wider">Contact Number <span class="text-red-500">*</span></Label>
              <Input type="text" v-model="dealForm.contact_number" class="bg-slate-950 border-slate-700 focus:border-blue-500 focus:ring-blue-500 h-12 rounded-xl" />
           </div>

           <div class="space-y-2">
              <Label class="text-slate-300 text-xs font-bold uppercase tracking-wider">Complete Address <span class="text-red-500">*</span></Label>
              <Textarea v-model="dealForm.address" rows="2" class="bg-slate-950 border-slate-700 focus:border-blue-500 focus:ring-blue-500 resize-none rounded-xl"></Textarea>
           </div>

           <div class="space-y-2">
              <Label class="text-slate-300 text-xs font-bold uppercase tracking-wider">Final Agreement Notes</Label>
              <Textarea v-model="dealForm.description" rows="3" class="bg-slate-950 border-slate-700 focus:border-blue-500 focus:ring-blue-500 resize-none rounded-xl" placeholder="Included materials, specific terms, preparation instructions..."></Textarea>
           </div>
        </div>

        <div class="px-6 py-5 bg-slate-950/80 border-t border-slate-800 flex justify-end gap-3 shrink-0">
           <Button variant="outline" class="border-slate-700 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl font-bold h-12 px-6" @click="showDealModal = false">Cancel</Button>
           <Button class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold h-12 px-8 shadow-lg shadow-emerald-900/20 transition-all" @click="sendOfficialDeal" :disabled="isSending">
              <span v-if="isSending" class="flex items-center">
                 <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div> Processing...
              </span>
              <span v-else>Send Deal Offer</span>
           </Button>
        </div>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

import { 
  Search, 
  Send, 
  Paperclip, 
  MoreVertical, 
  Phone, 
  Info, 
  Image as ImageIcon,
  ArrowLeft,
  Check,
  CheckCheck,
  ClipboardList,
  ShieldCheck
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Label } from '@/components/ui/label'

// State
const currentUser = ref(null)
const searchQuery = ref('')
const showMobileChat = ref(false)
const newMessage = ref('')
const chatContainer = ref(null)
const isLoadingContacts = ref(true)
const isLoadingMessages = ref(false)
const isSending = ref(false)

const contacts = ref([])
const activeContact = ref(null)
const messages = ref([])

// Deal Modal Form State
const showDealModal = ref(false)
const dealForm = ref({ 
  price: '', 
  preferred_date: '', 
  time_preference: '', 
  contact_number: '', 
  address: '', 
  description: '' 
})

// Initialize WebSockets securely using Pusher Cloud
const initWebSockets = (userId) => {
  window.Pusher = Pusher
  const token = localStorage.getItem('auth_token') 
  
  const apiUrl = import.meta.env?.VITE_APP_API_URL || 'http://127.0.0.1:8000/api'

  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: 'fade6ce6ed8705f2ace4', // Your exact Pusher Key
    cluster: 'ap1',              // Your exact Pusher Cluster
    forceTLS: true,              
    authEndpoint: `${apiUrl}/broadcasting/auth`, 
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json'
      }
    }
  })

  window.Echo.private(`chat.${userId}`)
    .listen('.MessageSent', (e) => {
      const incomingMsg = {
        id: e.message.id,
        sender: 'client',
        text: e.message.message,
        type: e.message.type,
        payload: e.message.payload,
        time: new Date(e.message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: 'read'
      }

      if (activeContact.value && activeContact.value.id === e.message.sender_id) {
        messages.value.push(incomingMsg)
        scrollToBottom()
      } else {
        const cIdx = contacts.value.findIndex(c => c.id === e.message.sender_id)
        if(cIdx !== -1) {
           contacts.value[cIdx].unread += 1
           contacts.value[cIdx].lastMessage = incomingMsg.type === 'text' ? incomingMsg.text : `New ${incomingMsg.type.replace('_', ' ')}`
        }
      }
    })
}

// Blocks mathematical operators and negative signs from the price input
const preventInvalidChars = (e) => {
  if (['e', 'E', '+', '-'].includes(e.key)) {
    e.preventDefault()
  }
}

// Helpers
const getInitials = (name) => {
  if (!name) return 'UN'
  return name.split(' ').map(w => w[0]).join('').toUpperCase().substring(0, 2)
}

const scrollToBottom = async () => {
  await nextTick()
  if (chatContainer.value) {
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight
  }
}

const filteredContacts = computed(() => {
  if (!searchQuery.value) return contacts.value
  const query = searchQuery.value.toLowerCase()
  return contacts.value.filter(c => 
    c.name.toLowerCase().includes(query) || 
    c.jobTitle.toLowerCase().includes(query)
  )
})

const activeMessages = computed(() => messages.value)

// API Actions
const fetchCurrentUser = async () => {
  try {
    const res = await api.get('/auth/me')
    currentUser.value = res.data.data || res.data.user || res.data 
    
    if (currentUser.value && currentUser.value.id) {
       initWebSockets(currentUser.value.id)
    }
  } catch (error) { 
    console.error('Auth error', error) 
  }
}

const fetchContacts = async () => {
  isLoadingContacts.value = true
  try {
    const response = await api.get('/service-provider/chat/contacts')
    if (response.data.success) {
      contacts.value = response.data.data
      if (window.innerWidth >= 768 && contacts.value.length > 0) {
        selectContact(contacts.value[0])
      }
    }
  } catch (error) { toast.error('Failed to load contacts') }
  finally { isLoadingContacts.value = false }
}

const fetchMessages = async (clientId) => {
  isLoadingMessages.value = true
  messages.value = []
  try {
    const response = await api.get(`/service-provider/chat/messages/${clientId}`)
    if (response.data.success) {
      messages.value = response.data.data.map(m => ({
        id: m.id,
        sender: m.sender_id === currentUser.value?.id ? 'me' : 'client',
        text: m.message,
        type: m.type,
        payload: m.payload,
        time: new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: m.is_read ? 'read' : 'sent'
      }))
      scrollToBottom()
    }
  } catch (error) { toast.error('Failed to load chat history') }
  finally { isLoadingMessages.value = false }
}

const selectContact = (contact) => {
  activeContact.value = contact
  contact.unread = 0
  showMobileChat.value = true
  fetchMessages(contact.id)
}

const submitMessageToDb = async (type, textContent, payloadData = null) => {
  if(!activeContact.value) return null
  
  isSending.value = true
  try {
    const response = await api.post('/service-provider/chat/send', {
      receiver_id: activeContact.value.id,
      service_request_id: activeContact.value.requestContext.id,
      message: textContent,
      type: type,
      payload: payloadData
    })

    if(response.data.success) {
      const m = response.data.data
      const newMsgObj = {
        id: m.id,
        sender: 'me',
        text: m.message,
        type: m.type,
        payload: m.payload,
        time: new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: 'sent'
      }
      
      messages.value.push(newMsgObj)
      
      const cRef = contacts.value.find(c => c.id === activeContact.value.id)
      if(cRef) cRef.lastMessage = type === 'text' ? textContent : `Sent ${type.replace('_', ' ')}`
      
      scrollToBottom()
      return true
    }
  } catch (error) {
    toast.error('Failed to send message')
    return false
  } finally {
    isSending.value = false
  }
}

const sendTextMessage = async () => {
  if (!newMessage.value.trim() || isSending.value) return
  const text = newMessage.value.trim()
  newMessage.value = '' 
  await submitMessageToDb('text', text)
}

const sendRequestSummary = async () => {
  const req = activeContact.value.requestContext
  const summaryPayload = {
    title: req.service_offering?.title || 'Custom Service',
    preferred_date: req.preferred_date || 'N/A',
    time_preference: req.time_preference || 'N/A',
    contact_number: req.contact_number || 'N/A',
    address: req.address || 'N/A',
    description: req.description || 'No additional details provided.'
  }
  await submitMessageToDb('request_summary', null, summaryPayload)
}

const openOfficialDealModal = () => {
  const req = activeContact.value.requestContext
  dealForm.value = {
    price: req.service_offering?.price || '',
    preferred_date: req.preferred_date || '',
    time_preference: req.time_preference || '',
    contact_number: req.contact_number || '',
    address: req.address || '',
    description: `Official agreement for ${req.service_offering?.title}.\n\nClient Notes: ${req.description || 'None'}`
  }
  showDealModal.value = true
}

const sendOfficialDeal = async () => {
  if(!dealForm.value.price || !dealForm.value.preferred_date || !dealForm.value.time_preference || !dealForm.value.contact_number || !dealForm.value.address) {
    toast.error("Please complete all required fields")
    return
  }
  // Ensures value is numeric and cleanly passed
  dealForm.value.price = parseFloat(dealForm.value.price);
  
  const success = await submitMessageToDb('official_deal', null, { ...dealForm.value })
  if(success) {
    toast.success("Official deal sent successfully!")
    showDealModal.value = false
  }
}

onMounted(async () => {
  await fetchCurrentUser()
  await fetchContacts()
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
@media (min-width: 768px) { .custom-scrollbar::-webkit-scrollbar { width: 5px; } }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #475569; }
textarea { -webkit-appearance: none; border-radius: 0; }
</style>