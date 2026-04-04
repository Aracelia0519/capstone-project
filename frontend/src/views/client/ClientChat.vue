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
              placeholder="Search providers..." 
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
             <p class="text-sm text-slate-500">No active service providers found yet.</p>
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
                <AvatarFallback class="bg-gradient-to-br from-indigo-600 to-cyan-600 text-white font-bold text-sm md:text-base">
                  {{ getInitials(contact.name) }}
                </AvatarFallback>
              </Avatar>
              <div class="absolute bottom-0 right-0 w-2.5 h-2.5 md:w-3 md:h-3 rounded-full border-2 border-slate-950" :class="contact.status === 'online' ? 'bg-emerald-500' : 'bg-slate-500'"></div>
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-baseline mb-0.5 md:mb-1">
                <h3 class="text-sm font-semibold text-slate-200 truncate pr-2">{{ contact.name }}</h3>
                <span class="text-[10px] md:text-xs text-slate-500 shrink-0">{{ contact.time || contact.date }}</span>
              </div>
              <div class="flex justify-between items-center">
                <p class="text-xs text-slate-400 truncate pr-2" :class="{'text-slate-300 font-medium': contact.unread > 0}">
                  {{ contact.lastMessage || contact.last_message }}
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
                <AvatarFallback class="bg-gradient-to-br from-indigo-600 to-cyan-600 text-white text-xs md:text-sm font-bold">
                  {{ getInitials(activeContact.name) }}
                </AvatarFallback>
              </Avatar>
              <div class="absolute bottom-0 right-0 w-2 h-2 md:w-2.5 md:h-2.5 rounded-full border-2 border-slate-950 bg-emerald-500"></div>
            </div>
            
            <div class="min-w-0 pr-2">
              <h2 class="text-sm md:text-base font-bold text-white leading-tight truncate">{{ activeContact.name }}</h2>
              <p class="text-[10px] md:text-xs text-slate-400 truncate">{{ activeContact.jobTitle || activeContact.service_title }}</p>
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
            class="flex w-full shrink-0 group"
            :class="message.sender === 'me' ? 'justify-end' : 'justify-start'"
          >
            <Avatar v-if="message.sender !== 'me'" class="h-6 w-6 md:h-8 md:w-8 mr-1.5 md:mr-2 shrink-0 self-end mb-1 border border-slate-700 hidden sm:flex">
               <AvatarFallback class="bg-gradient-to-br from-indigo-600 to-cyan-600 text-white text-[10px] md:text-xs">
                 {{ getInitials(activeContact.name) }}
               </AvatarFallback>
            </Avatar>

            <div class="flex flex-col max-w-[95%] sm:max-w-[85%] md:max-w-[75%] relative group/msg" :class="message.sender === 'me' ? 'items-end' : 'items-start'">
              
              <div v-if="message.sender === 'me' && !message.is_deleted && (message.type === 'text' || message.type === 'image')" class="absolute top-0 right-full mr-2 opacity-0 group-hover/msg:opacity-100 transition-opacity flex items-center gap-1 bg-slate-800 border border-slate-700 rounded-md px-1 py-0.5 z-10">
                 <button v-if="message.type === 'text'" @click="startEditMessage(message)" class="p-1 text-slate-400 hover:text-blue-400" title="Edit"><Edit2 class="w-3 h-3"/></button>
                 <button @click="deleteMessage(message.id)" class="p-1 text-slate-400 hover:text-red-400" title="Delete"><Trash2 class="w-3 h-3"/></button>
              </div>

              <div v-if="editingMessageId === message.id" class="w-full bg-slate-800 border border-blue-500 rounded-xl p-2 mb-1 shadow-lg min-w-[200px]">
                 <input v-model="editMessageText" class="w-full bg-transparent text-sm text-white focus:outline-none mb-2" @keyup.enter="confirmEditMessage" />
                 <div class="flex justify-end gap-2">
                    <Button variant="ghost" size="sm" class="h-6 text-xs text-slate-400" @click="editingMessageId = null">Cancel</Button>
                    <Button size="sm" class="h-6 text-xs bg-blue-600 hover:bg-blue-700 text-white" @click="confirmEditMessage">Save</Button>
                 </div>
              </div>

              <div v-else-if="message.type === 'text'"
                class="px-3 py-2 md:px-4 md:py-2.5 text-[13px] md:text-sm shadow-sm whitespace-pre-wrap break-words"
                :class="[
                   message.is_deleted ? 'bg-slate-800/50 border border-slate-700 text-slate-500 italic rounded-2xl' :
                   message.sender === 'me' ? 'bg-blue-600 text-white rounded-2xl rounded-tr-sm' : 'bg-slate-800 text-slate-200 rounded-2xl rounded-tl-sm border border-slate-700'
                ]"
              >
                {{ message.text }}
              </div>

              <div v-else-if="message.type === 'image'" class="relative">
                 <img :src="getPayloadImage(message.text)" class="rounded-xl max-w-xs md:max-w-sm max-h-64 object-cover shadow-md border border-slate-700 cursor-pointer hover:opacity-90" @click="viewFullImage(message.text)" @error="(e) => e.target.src='https://via.placeholder.com/150'" />
              </div>

              <div v-else-if="message.type === 'color_share'" class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow-lg w-full max-w-[300px] sm:max-w-[360px]" :class="message.sender === 'me' ? 'rounded-tr-sm' : 'rounded-tl-sm'">
                 <div class="w-full h-32 sm:h-40 rounded-xl shadow-inner mb-4 border border-slate-700" :style="{ backgroundColor: message.payload?.hex_code }"></div>
                 <p class="text-lg sm:text-xl font-bold text-white break-words text-center mb-4">{{ message.payload?.color_name }}</p>
                 <div class="bg-slate-900/90 rounded-xl p-4 space-y-2.5 border border-slate-700">
                     <div class="flex justify-between items-center"><span class="text-xs sm:text-sm text-slate-500 font-bold uppercase">HEX</span> <span class="text-sm sm:text-base text-slate-200 font-mono">{{ message.payload?.hex_code }}</span></div>
                     <div class="flex justify-between items-center" v-if="message.payload?.rgb_values"><span class="text-xs sm:text-sm text-slate-500 font-bold uppercase">RGB</span> <span class="text-sm sm:text-base text-slate-200 font-mono">{{ message.payload?.rgb_values }}</span></div>
                     <div class="flex justify-between items-center" v-if="message.payload?.hsl_values"><span class="text-xs sm:text-sm text-slate-500 font-bold uppercase">HSL</span> <span class="text-sm sm:text-base text-slate-200 font-mono">{{ message.payload?.hsl_values }}</span></div>
                     <div class="flex justify-between items-center" v-if="message.payload?.temperature"><span class="text-xs sm:text-sm text-slate-500 font-bold uppercase">Temp</span> <span class="text-sm sm:text-base text-slate-200">{{ message.payload?.temperature }}</span></div>
                     <div class="flex justify-between items-center" v-if="message.payload?.color_family"><span class="text-xs sm:text-sm text-slate-500 font-bold uppercase">Family</span> <span class="text-sm sm:text-base text-slate-200">{{ message.payload?.color_family }}</span></div>
                     <div class="flex justify-between items-center" v-if="message.payload?.accessibility"><span class="text-xs sm:text-sm text-slate-500 font-bold uppercase">Contrast</span> <span class="text-sm sm:text-base text-slate-200">{{ message.payload?.accessibility }}</span></div>
                 </div>
              </div>

              <div v-else-if="message.type === 'product_share'" class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow-lg w-full max-w-[320px] sm:max-w-[400px] flex flex-col gap-3" :class="message.sender === 'me' ? 'rounded-tr-sm' : 'rounded-tl-sm'">
                 <div class="flex items-start gap-4">
                   <img :src="getPayloadImage(message.payload?.image)" class="w-24 h-24 sm:w-28 sm:h-28 rounded-xl object-cover border border-slate-700 bg-slate-900 shrink-0" @error="(e) => e.target.src='https://via.placeholder.com/150'" />
                   <div class="text-left flex-1 min-w-0 py-1">
                      <p class="text-base sm:text-lg font-bold text-white leading-tight mb-2 break-words">{{ message.payload?.product_name }}</p>
                      <p class="text-sm sm:text-base text-emerald-400 font-black mb-2">₱{{ message.payload?.price }}</p>
                      
                      <div class="flex flex-col gap-1.5">
                         <p class="text-xs sm:text-sm text-slate-300 break-words" v-if="message.payload?.category"><span class="text-slate-500 font-medium">Category:</span> {{ message.payload?.category }}</p>
                         <p class="text-xs sm:text-sm text-slate-300 break-words" v-if="message.payload?.type"><span class="text-slate-500 font-medium">Type:</span> {{ message.payload?.type }}</p>
                         <div class="text-xs sm:text-sm text-slate-300 flex items-center gap-2 mt-1" v-if="message.payload?.color_code && message.payload?.color_code !== 'N/A'">
                             <span class="text-slate-500 font-medium">Color:</span>
                             <span class="w-4 h-4 sm:w-5 sm:h-5 rounded-full border border-slate-500 shadow-sm inline-block" :style="{ backgroundColor: message.payload?.color_code }"></span>
                             <span class="font-mono">{{ message.payload?.color_code }}</span>
                         </div>
                      </div>
                   </div>
                 </div>
              </div>

              <div v-else-if="message.type === 'request_summary'" class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow-md w-full max-w-sm sm:max-w-md" :class="message.sender === 'me' ? 'rounded-tr-sm' : 'rounded-tl-sm'">
                 <div class="flex items-center gap-2 mb-3 border-b border-slate-700 pb-2">
                    <ClipboardList class="w-4 h-4 text-cyan-400" />
                    <span class="text-sm font-bold text-slate-200 uppercase tracking-wider">Service Request Summary</span>
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

              <div v-else-if="message.type === 'official_deal'" class="bg-gradient-to-br from-indigo-900/50 to-cyan-900/50 border border-cyan-500/50 rounded-xl p-4 shadow-lg shadow-cyan-900/20 w-full max-w-sm sm:max-w-md" :class="message.sender === 'me' ? 'rounded-tr-sm' : 'rounded-tl-sm'">
                 <div class="flex items-center justify-between mb-3 border-b border-cyan-500/30 pb-2">
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
                    <div class="mt-2 text-slate-300 break-words border-t border-slate-700/50 pt-2">{{ message.payload?.description || 'N/A' }}</div>
                    
                    <div v-if="message.payload?.colors" class="flex flex-col gap-1 mt-2">
                       <span class="text-slate-400">Target Color(s):</span>
                       <Badge class="bg-slate-800 border border-slate-600 text-slate-300 self-start text-[10px]">{{ message.payload.colors.replace(/["\[\]]/g, '') }}</Badge>
                    </div>
                 </div>
                 
                 <div v-if="message.sender !== 'me' && message.payload?.deal_status === 'pending'" class="mt-3 flex gap-2">
                    <Button size="sm" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-lg shadow-emerald-900/20" @click="handleDealAction('accept', message)">Accept Deal</Button>
                    <Button size="sm" variant="outline" class="flex-1 border-red-500/30 text-red-400 hover:bg-red-500/10" @click="handleDealAction('decline', message)">Decline</Button>
                 </div>
                 <div v-else class="mt-3 text-center text-xs font-bold" :class="{ 'text-cyan-300 animate-pulse': message.payload?.deal_status === 'pending' || !message.payload?.deal_status, 'text-emerald-400': message.payload?.deal_status === 'ongoing', 'text-red-400': message.payload?.deal_status === 'declined' }">
                    <span v-if="message.payload?.deal_status === 'pending' || !message.payload?.deal_status">Waiting for client's approval...</span>
                    <span v-else-if="message.payload?.deal_status === 'ongoing'">✅ Client Accepted the Deal!</span>
                    <span v-else-if="message.payload?.deal_status === 'declined'">❌ Client Declined the Deal.</span>
                 </div>
              </div>

              <div v-else-if="message.type === 'payment_term'" class="bg-gradient-to-br from-slate-800 to-slate-900 border border-yellow-500/50 rounded-xl p-4 shadow-lg w-full max-w-sm sm:max-w-md" :class="message.sender === 'me' ? 'rounded-tr-sm' : 'rounded-tl-sm'">
                 <div class="flex items-center gap-2 mb-3 border-b border-yellow-500/30 pb-2">
                    <CreditCard class="w-5 h-5 text-yellow-400" />
                    <span class="text-sm font-bold text-white uppercase tracking-wider">Payment Terms Offer</span>
                 </div>
                 <div class="space-y-2 text-xs text-slate-200 bg-slate-950/50 p-3 rounded-lg border border-slate-800 shadow-inner">
                    <div class="flex justify-between items-center mb-1">
                       <span class="text-slate-400 font-medium">Payment Method</span> 
                       <span class="font-bold text-yellow-400 uppercase">{{ message.payload?.payment_method?.replace('_', ' ') }}</span>
                    </div>
                    <div class="h-px bg-slate-700/50 my-1"></div>
                    <div class="flex flex-col gap-1">
                       <span class="text-slate-400">Payment Term Condition:</span> 
                       <span class="font-medium text-sm text-white">{{ message.payload?.payment_term }}</span>
                    </div>
                 </div>

                 <div v-if="message.sender !== 'me' && message.payload?.term_status === 'pending'" class="mt-3 flex gap-2">
                    <Button size="sm" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold" @click="handlePaymentTermAction('agree', message)">Agree to Terms</Button>
                    <Button size="sm" variant="outline" class="flex-1 border-red-500/30 text-red-400 hover:bg-red-500/10" @click="handlePaymentTermAction('decline', message)">Decline</Button>
                 </div>
                 <div v-else class="mt-3 text-center text-xs font-bold" :class="{ 'text-yellow-300 animate-pulse': message.payload?.term_status === 'pending', 'text-emerald-400': message.payload?.term_status === 'agreed', 'text-red-400': message.payload?.term_status === 'declined' }">
                    <span v-if="message.payload?.term_status === 'pending'">Waiting for your response...</span>
                    <span v-else-if="message.payload?.term_status === 'agreed'">✅ You Agreed to these Terms!</span>
                    <span v-else-if="message.payload?.term_status === 'declined'">❌ You Declined these Terms.</span>
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
          <h3 class="text-base md:text-lg font-medium text-slate-300 mb-2">Select a provider</h3>
          <p class="text-xs md:text-sm max-w-[250px] md:max-w-sm">Choose a service provider from the sidebar to discuss your project details.</p>
        </div>

        <div v-if="activeContact" class="p-2 md:p-3 bg-slate-950 border-t border-slate-800 shrink-0 sticky bottom-0 z-10 w-full relative">
          
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
                   <DropdownMenuItem class="cursor-pointer focus:bg-slate-700 py-2.5 rounded-lg" @click="$refs.imageInput.click()">
                      <ImageIcon class="w-4 h-4 mr-2 text-blue-400" /> 
                      <span class="font-medium text-sm">Upload Image</span>
                   </DropdownMenuItem>
                   <DropdownMenuItem class="cursor-pointer focus:bg-slate-700 py-2.5 rounded-lg mt-1" @click="openColorShareModal">
                      <Palette class="w-4 h-4 mr-2 text-pink-400" /> 
                      <span class="font-medium text-sm">Share Saved Color</span>
                   </DropdownMenuItem>
                 </div>
              </DropdownMenuContent>
            </DropdownMenu>

            <input type="file" ref="imageInput" accept="image/*" class="hidden" @change="uploadImage" />

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

    <Dialog v-model:open="showColorShareModal">
      <DialogContent class="bg-slate-900 border-slate-800 text-slate-200 max-w-md rounded-2xl">
        <DialogTitle class="flex items-center gap-2 text-pink-400 font-bold"><Palette class="w-5 h-5"/> My Saved Colors</DialogTitle>
        <div class="grid grid-cols-2 gap-3 max-h-[350px] overflow-y-auto custom-scrollbar p-1 mt-2">
           <div v-for="color in savedColors" :key="color.id" @click="sendColor(color)" class="bg-slate-950 border border-slate-800 rounded-xl p-3 cursor-pointer hover:border-pink-500 transition-all text-center group">
              <div class="w-full h-20 rounded-lg mb-3 shadow-inner group-hover:scale-105 transition-transform border border-slate-700" :style="{ backgroundColor: color.display_hex }"></div>
              <p class="text-sm font-bold truncate text-white">{{ color.display_name }}</p>
           </div>
           <div v-if="savedColors.length === 0" class="col-span-2 text-center text-slate-500 text-sm py-4">No saved colors found.</div>
        </div>
        <Button variant="ghost" class="mt-4 w-full text-slate-400 hover:text-white" @click="showColorShareModal = false">Cancel</Button>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showImageViewer">
       <DialogContent class="bg-transparent border-0 shadow-none max-w-3xl flex justify-center items-center">
          <img :src="getPayloadImage(viewingImage)" class="max-w-full max-h-[85vh] rounded-xl shadow-2xl object-contain" @error="(e) => e.target.src='https://via.placeholder.com/150'" />
          <Button variant="ghost" class="absolute top-2 right-2 text-white bg-black/50 hover:bg-black rounded-full w-8 h-8 p-0 flex items-center justify-center" @click="showImageViewer = false"><X class="w-5 h-5"/></Button>
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
  MoreVertical, 
  Phone, 
  Info, 
  Image as ImageIcon,
  ArrowLeft,
  Check,
  CheckCheck,
  ClipboardList,
  ShieldCheck,
  CreditCard,
  Palette,
  Edit2,
  Trash2,
  X,
  Paperclip,
  Package
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'

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

// New Feature States
const showAttachmentMenu = ref(false)
const imageInput = ref(null)
const showColorShareModal = ref(false)
const savedColors = ref([])
const showImageViewer = ref(false)
const viewingImage = ref('')

const editingMessageId = ref(null)
const editMessageText = ref('')


// =========================================================================
// API & STORAGE URL CONFIGURATION
// =========================================================================

const apiUrl = import.meta.env?.VITE_APP_API_URL || 'http://127.0.0.1:8000/api'

// --- HOSTINGER PRODUCTION SETUP (Uncomment this when deploying!) ---
// const apiUrl = 'https://api.capstone001.com/api'

const baseStorageUrl = apiUrl.replace(/\/api\/?$/, '') + '/storage/'

// Guaranteed Payload Image Resolver
const getPayloadImage = (img) => {
   if (!img) return 'https://via.placeholder.com/150'
   if (img.startsWith('http') || img.startsWith('data:')) return img
   return baseStorageUrl + img.replace(/^\/+/, '').replace(/^storage\//, '')
}

const initWebSockets = (userId) => {
  window.Pusher = Pusher
  const token = localStorage.getItem('auth_token') 
  
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

  window.Echo.private(`chat.${userId}`)
    .listen('.MessageSent', (e) => {
      const incomingMsg = {
        id: e.message.id,
        sender: 'client', 
        text: e.message.message,
        type: e.message.type,
        payload: e.message.payload,
        time: new Date(e.message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: 'read',
        is_deleted: e.message.payload?.is_deleted || false
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
    (c.jobTitle && c.jobTitle.toLowerCase().includes(query)) ||
    (c.service_title && c.service_title.toLowerCase().includes(query))
  )
})

const activeMessages = computed(() => messages.value)

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
    const response = await api.get('/client/chat/contacts')
    if (response.data.success) {
      contacts.value = response.data.contacts || response.data.data
      if (window.innerWidth >= 768 && contacts.value.length > 0) {
        selectContact(contacts.value[0])
      }
    }
  } catch (error) { toast.error('Failed to load contacts') }
  finally { isLoadingContacts.value = false }
}

const fetchMessages = async (providerId) => {
  isLoadingMessages.value = true
  messages.value = []
  try {
    const response = await api.get(`/client/chat/messages/${providerId}`)
    if (response.data.success) {
      messages.value = (response.data.messages || response.data.data).map(m => {
        return {
          id: m.id,
          sender: m.sender_id === currentUser.value?.id || m.sender === 'me' ? 'me' : 'client',
          text: m.message || m.text,
          type: m.type,
          payload: m.payload,
          time: m.time || new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
          status: m.status || (m.is_read ? 'read' : 'sent'),
          is_deleted: m.is_deleted || m.payload?.is_deleted || false
        }
      })
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

// Edit & Delete Handlers
const startEditMessage = (msg) => {
   editingMessageId.value = msg.id
   editMessageText.value = msg.text
}

const confirmEditMessage = async () => {
   if (!editMessageText.value.trim() || !editingMessageId.value) return
   try {
      const res = await api.put(`/client/chat/messages/${editingMessageId.value}`, { message: editMessageText.value })
      if (res.data.success) {
         const msgRef = messages.value.find(m => m.id === editingMessageId.value)
         if(msgRef) msgRef.text = editMessageText.value
         editingMessageId.value = null
         toast.success('Message updated')
      }
   } catch(err) {
      toast.error('Failed to edit message')
   }
}

const deleteMessage = async (id) => {
   try {
      const res = await api.delete(`/client/chat/messages/${id}`)
      if (res.data.success) {
         const msgRef = messages.value.find(m => m.id === id)
         if(msgRef) {
            msgRef.is_deleted = true
            msgRef.text = "This message was deleted"
         }
         toast.success('Message deleted')
      }
   } catch(err) {
      toast.error('Failed to delete message')
   }
}

const submitMessageToDb = async (type, textContent, payloadData = null) => {
  if(!activeContact.value) return null
  
  isSending.value = true
  try {
    const response = await api.post('/client/chat/send', {
      receiver_id: activeContact.value.id,
      service_request_id: activeContact.value.service_request_id || activeContact.value.requestContext?.id,
      message: textContent || 'Attachment',
      type: type,
      payload: payloadData
    })

    if(response.data.success) {
      const m = response.data.message || response.data.data
      const newMsgObj = {
        id: m.id,
        sender: 'me',
        text: m.message,
        type: m.type,
        payload: m.payload,
        time: new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: 'sent',
        is_deleted: false
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

// Image Upload
const uploadImage = async (e) => {
   showAttachmentMenu.value = false
   const file = e.target.files[0]
   if(!file) return
   
   const formData = new FormData()
   formData.append('image', file)
   formData.append('receiver_id', activeContact.value.id)
   formData.append('service_request_id', activeContact.value.service_request_id || activeContact.value.requestContext?.id)

   try {
      const res = await api.post('/client/chat/send-image', formData, {
         headers: { 'Content-Type': 'multipart/form-data' }
      })
      if(res.data.success) {
         messages.value.push({
            id: res.data.message.id,
            sender: 'me',
            text: res.data.message.message,
            type: 'image',
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
            status: 'sent',
            is_deleted: false
         })
         scrollToBottom()
      }
   } catch(err) {
      toast.error("Failed to upload image")
   } finally {
      if(imageInput.value) imageInput.value.value = null
   }
}

const viewFullImage = (url) => {
   viewingImage.value = url
   showImageViewer.value = true
}

// Colors - Expanded Details
const openColorShareModal = async () => {
   showAttachmentMenu.value = false
   try {
      const res = await api.get('/client/colors')
      if (res.data.success) {
         const dataPayload = res.data.data || res.data
         const dataArray = dataPayload.colors ? dataPayload.colors : (Array.isArray(dataPayload) ? dataPayload : [])
         
         savedColors.value = dataArray.map(color => ({
             id: color.id,
             display_name: color.color_name || color.name || 'Selected Color',
             display_hex: color.hex_code || color.hex || color.color_code || '#cccccc',
             rgb_values: color.rgb_values,
             hsl_values: color.hsl_values,
             temperature: color.temperature,
             color_family: color.color_family,
             accessibility: color.accessibility
         }))
         showColorShareModal.value = true
      }
   } catch(err) {
      toast.error("Failed to fetch colors")
   }
}

const sendColor = async (color) => {
   const payload = { 
       color_name: color.display_name, 
       hex_code: color.display_hex,
       rgb_values: color.rgb_values,
       hsl_values: color.hsl_values,
       temperature: color.temperature,
       color_family: color.color_family,
       accessibility: color.accessibility
   }
   const success = await submitMessageToDb('color_share', 'Shared a color', payload)
   if(success) showColorShareModal.value = false
}

// Deal Actions
const handleDealAction = async (action, message) => {
  if(!message.payload || !message.payload.deal_id) {
     toast.error("Deal reference missing");
     return;
  }
  
  try {
    const res = await api.post(`/client/chat/deals/${message.payload.deal_id}/respond`, {
      action: action,
      message_id: message.id
    })

    if (res.data.success) {
      toast.success(action === 'accept' ? 'Deal Agreed Successfully!' : 'Deal Declined')
      message.payload.deal_status = action === 'accept' ? 'ongoing' : 'declined';
      messages.value.push({
        id: Date.now(),
        sender: 'me',
        text: action === 'accept' ? "I have accepted the official deal. Let's begin the ongoing project!" : "I have declined the official deal. Can we adjust the terms?",
        type: 'text',
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: 'sent',
        is_deleted: false
      });
      scrollToBottom();
    }
  } catch(error) {
    toast.error('Failed to process deal action')
  }
}

// Payment Terms Action
const handlePaymentTermAction = async (action, message) => {
  if(!message.payload || !message.payload.term_id) {
     toast.error("Payment term reference missing");
     return;
  }
  
  try {
    const res = await api.post(`/client/chat/payment-terms/${message.payload.term_id}/respond`, {
      action: action,
      message_id: message.id
    })

    if (res.data.success) {
      toast.success(action === 'agree' ? 'Payment Term Agreed!' : 'Payment Term Declined')
      message.payload.term_status = action === 'agree' ? 'agreed' : 'declined';
      messages.value.push({
        id: Date.now(),
        sender: 'me',
        text: action === 'agree' ? "I have agreed to the payment terms." : "I have declined the payment terms. Let's negotiate.",
        type: 'text',
        time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        status: 'sent',
        is_deleted: false
      });
      scrollToBottom();
    }
  } catch(error) {
    toast.error('Failed to process payment term action')
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