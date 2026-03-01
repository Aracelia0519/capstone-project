<script setup>
import { ref, computed, nextTick } from 'vue'
import { Search, Send, Paperclip, MoreVertical, Phone, Video, Smile } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
import { ScrollArea } from '@/components/ui/scroll-area'
import { toast } from 'vue-sonner'

// Mock Data for Paint Store Clients
const contacts = ref([
  {
    id: 1,
    name: 'Maria Santos',
    avatar: 'https://i.pravatar.cc/150?u=maria',
    initials: 'MS',
    lastMessage: 'Do you have the Boysen Matte Black in gallons?',
    time: '10:42 AM',
    unread: 2,
    online: true,
  },
  {
    id: 2,
    name: 'Juan Dela Cruz',
    avatar: 'https://i.pravatar.cc/150?u=juan',
    initials: 'JD',
    lastMessage: 'Thanks! The epoxy primer arrived today.',
    time: 'Yesterday',
    unread: 0,
    online: false,
  },
  {
    id: 3,
    name: 'Elena Garcia',
    avatar: 'https://i.pravatar.cc/150?u=elena',
    initials: 'EG',
    lastMessage: 'Can you mix a custom teal color for me?',
    time: 'Tuesday',
    unread: 0,
    online: true,
  }
])

const messages = ref([
  { id: 1, senderId: 1, text: 'Hi! I ordered some paints last week.', time: '10:30 AM', isMine: false },
  { id: 2, senderId: 0, text: 'Hello Maria! Yes, I see your order for the latex paints. How can I help?', time: '10:35 AM', isMine: true },
  { id: 3, senderId: 1, text: 'I realized I need more for the living room.', time: '10:41 AM', isMine: false },
  { id: 4, senderId: 1, text: 'Do you have the Boysen Matte Black in gallons?', time: '10:42 AM', isMine: false },
])

const activeContactId = ref(1)
const activeContact = computed(() => contacts.value.find(c => c.id === activeContactId.value))
const searchQuery = ref('')

const newMessage = ref('')
const messageContainer = ref(null)

const selectContact = (id) => {
  activeContactId.value = id
  const contact = contacts.value.find(c => c.id === id)
  if (contact) contact.unread = 0
}

const sendMessage = async () => {
  if (!newMessage.value.trim()) return

  messages.value.push({
    id: Date.now(),
    senderId: 0,
    text: newMessage.value,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    isMine: true
  })

  // Update last message in sidebar
  const contact = contacts.value.find(c => c.id === activeContactId.value)
  if (contact) {
    contact.lastMessage = newMessage.value
    contact.time = 'Just now'
  }

  newMessage.value = ''
  
  toast.success('Message sent')

  // Scroll to bottom
  await nextTick()
  if (messageContainer.value) {
    const scrollEl = messageContainer.value.$el.querySelector('[data-radix-scroll-area-viewport]')
    if (scrollEl) scrollEl.scrollTop = scrollEl.scrollHeight
  }
}
</script>

<template>
  <div class="flex h-[80vh] min-h-[600px] w-full overflow-hidden rounded-xl border bg-background text-foreground shadow-lg">
    
    <div class="flex w-full flex-col border-r bg-muted/10 sm:w-80 md:w-96">
      <div class="flex items-center justify-between border-b p-4">
        <h2 class="text-xl font-semibold tracking-tight">Messages</h2>
        <Button variant="ghost" size="icon" class="h-8 w-8 rounded-full">
          <MoreVertical class="h-5 w-5" />
        </Button>
      </div>

      <div class="p-4">
        <div class="relative">
          <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
          <Input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Search clients..." 
            class="pl-9 bg-muted/50" 
          />
        </div>
      </div>

      <ScrollArea class="flex-1">
        <div class="flex flex-col gap-1 p-2">
          <button
            v-for="contact in contacts"
            :key="contact.id"
            @click="selectContact(contact.id)"
            class="flex items-start gap-3 rounded-lg p-3 text-left transition-colors hover:bg-muted/50 focus:outline-none"
            :class="{ 'bg-muted/80': activeContactId === contact.id }"
          >
            <div class="relative">
              <Avatar class="h-12 w-12 border border-border">
                <AvatarImage :src="contact.avatar" :alt="contact.name" />
                <AvatarFallback>{{ contact.initials }}</AvatarFallback>
              </Avatar>
              <span 
                v-if="contact.online" 
                class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-background bg-green-500"
              ></span>
            </div>
            
            <div class="flex flex-1 flex-col overflow-hidden">
              <div class="flex items-center justify-between">
                <span class="font-medium truncate" :class="{ 'font-semibold': contact.unread > 0 }">
                  {{ contact.name }}
                </span>
                <span class="text-xs text-muted-foreground whitespace-nowrap ml-2">
                  {{ contact.time }}
                </span>
              </div>
              <div class="flex items-center justify-between mt-0.5">
                <span 
                  class="text-sm truncate text-muted-foreground"
                  :class="{ 'text-foreground font-medium': contact.unread > 0 }"
                >
                  {{ contact.lastMessage }}
                </span>
                <span 
                  v-if="contact.unread > 0" 
                  class="ml-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] font-medium text-primary-foreground"
                >
                  {{ contact.unread }}
                </span>
              </div>
            </div>
          </button>
        </div>
      </ScrollArea>
    </div>

    <div class="flex flex-1 flex-col bg-background">
      <div v-if="activeContact" class="flex h-[73px] items-center justify-between border-b px-6 py-4 shadow-sm z-10">
        <div class="flex items-center gap-3">
          <Avatar class="h-10 w-10">
            <AvatarImage :src="activeContact.avatar" :alt="activeContact.name" />
            <AvatarFallback>{{ activeContact.initials }}</AvatarFallback>
          </Avatar>
          <div class="flex flex-col">
            <span class="font-semibold">{{ activeContact.name }}</span>
            <span class="text-xs text-muted-foreground">
              {{ activeContact.online ? 'Active now' : 'Offline' }}
            </span>
          </div>
        </div>
        <div class="flex items-center gap-1">
          <Button variant="ghost" size="icon" class="text-muted-foreground hover:text-foreground">
            <Phone class="h-5 w-5" />
          </Button>
          <Button variant="ghost" size="icon" class="text-muted-foreground hover:text-foreground">
            <Video class="h-5 w-5" />
          </Button>
          <Button variant="ghost" size="icon" class="text-muted-foreground hover:text-foreground">
            <MoreVertical class="h-5 w-5" />
          </Button>
        </div>
      </div>

      <ScrollArea ref="messageContainer" class="flex-1 p-4" v-if="activeContact">
        <div class="flex flex-col gap-4 py-4 max-w-3xl mx-auto">
          <div 
            v-for="message in messages" 
            :key="message.id"
            class="flex w-full"
            :class="message.isMine ? 'justify-end' : 'justify-start'"
          >
            <div class="flex flex-col gap-1 max-w-[70%]">
              <div 
                class="rounded-2xl px-4 py-2.5 text-sm shadow-sm"
                :class="[
                  message.isMine 
                    ? 'bg-primary text-primary-foreground rounded-br-sm' 
                    : 'bg-muted/80 text-foreground rounded-bl-sm border border-border/50'
                ]"
              >
                {{ message.text }}
              </div>
              <span 
                class="text-[10px] text-muted-foreground px-1"
                :class="message.isMine ? 'text-right' : 'text-left'"
              >
                {{ message.time }}
              </span>
            </div>
          </div>
        </div>
      </ScrollArea>
      
      <div v-else class="flex flex-1 items-center justify-center">
        <p class="text-muted-foreground">Select a conversation to start messaging</p>
      </div>

      <div v-if="activeContact" class="p-4 border-t bg-background">
        <form @submit.prevent="sendMessage" class="flex items-end gap-2 max-w-3xl mx-auto">
          <div class="flex items-center gap-1 pb-1">
            <Button type="button" variant="ghost" size="icon" class="shrink-0 text-muted-foreground hover:text-foreground rounded-full">
              <Paperclip class="h-5 w-5" />
            </Button>
          </div>
          
          <div class="relative flex-1">
            <Input 
              v-model="newMessage"
              placeholder="Type a message..." 
              class="min-h-[44px] w-full rounded-full bg-muted/50 pr-12 focus-visible:ring-primary"
            />
            <Button 
              type="button" 
              variant="ghost" 
              size="icon" 
              class="absolute right-1 top-1 h-9 w-9 text-muted-foreground hover:text-foreground rounded-full"
            >
              <Smile class="h-5 w-5" />
            </Button>
          </div>

          <Button 
            type="submit" 
            size="icon" 
            class="h-11 w-11 shrink-0 rounded-full bg-primary hover:bg-primary/90 text-primary-foreground shadow-sm transition-transform active:scale-95"
            :disabled="!newMessage.trim()"
          >
            <Send class="h-5 w-5 ml-0.5" />
          </Button>
        </form>
      </div>
    </div>
  </div>
</template>