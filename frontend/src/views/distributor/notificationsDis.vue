<template>
  <div class="p-4 md:p-8 font-sans selection:bg-blue-100 selection:text-blue-900">
    <div class="max-w-8xl mx-auto space-y-6">
      
      <!-- Page Header -->
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200 dark:border-slate-800">
        <div class="flex items-center gap-5">
          <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shrink-0 shadow-lg shadow-indigo-500/30">
            <Bell class="w-7 h-7" />
          </div>
          <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">System Alerts</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">
              You have <strong class="text-indigo-600 dark:text-indigo-400">{{ unreadCount }}</strong> unread messages.
            </p>
          </div>
        </div>
        <Button 
          v-if="unreadCount > 0" 
          @click="markAllAsRead" 
          variant="outline" 
          class="bg-white hover:bg-slate-50 border-slate-200 text-slate-700 shadow-sm rounded-xl h-11 px-5 font-bold transition-all hover:scale-105 shrink-0"
        >
          <CheckCircle2 class="w-4 h-4 mr-2 text-emerald-500" /> Mark all as read
        </Button>
      </div>

      <!-- Main Notifications List -->
      <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-200 dark:border-slate-800 overflow-hidden relative">
        
        <!-- Loading State -->
        <div v-if="isLoading" class="p-24 flex flex-col items-center justify-center text-slate-400">
          <Loader2 class="w-12 h-12 animate-spin mb-4 text-indigo-500" />
          <p class="font-bold text-lg">Fetching updates...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="notifications.length === 0" class="p-24 flex flex-col items-center justify-center text-slate-400">
          <div class="w-24 h-24 bg-slate-50 dark:bg-slate-800/50 rounded-full flex items-center justify-center mb-6 border border-slate-100 dark:border-slate-800">
            <BellOff class="w-10 h-10 text-slate-300" />
          </div>
          <p class="font-black text-2xl text-slate-600 dark:text-slate-300">You're all caught up!</p>
          <p class="text-base mt-2 text-slate-400">No new alerts at this time.</p>
        </div>

        <!-- Populated List -->
        <div v-else class="divide-y divide-slate-100 dark:divide-slate-800/50">
          <div 
            v-for="notification in notifications" 
            :key="notification.id"
            class="p-6 md:p-8 transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50 cursor-pointer flex gap-5 md:gap-6 items-start relative group"
            :class="!notification.is_read ? 'bg-indigo-50/50 dark:bg-indigo-900/10' : ''"
            @click="markAsRead(notification)"
          >
            <!-- Left border indicator for unread -->
            <div 
              v-if="!notification.is_read" 
              class="absolute left-0 top-0 bottom-0 w-1.5 bg-indigo-500 shadow-[0_0_10px_rgba(99,102,241,0.5)]"
            ></div>
            
            <div class="shrink-0 mt-1">
              <div class="w-12 h-12 rounded-full flex items-center justify-center shadow-sm border border-black/5" :class="getIconColor(notification.type)">
                <component :is="getIcon(notification.type)" class="w-6 h-6" />
              </div>
            </div>

            <div class="flex-1 space-y-2 min-w-0">
              <div class="flex flex-col md:flex-row md:items-start justify-between gap-2">
                <h3 
                  class="text-base md:text-lg font-bold leading-tight pr-4 truncate"
                  :class="!notification.is_read ? 'text-slate-900 dark:text-white' : 'text-slate-700 dark:text-slate-300'"
                >
                  {{ notification.title }}
                </h3>
                <div class="flex items-center gap-2 shrink-0 md:pt-1">
                  <span class="text-xs font-bold uppercase tracking-wider" :class="!notification.is_read ? 'text-indigo-600' : 'text-slate-400'">
                    {{ formatTimeAgo(notification.created_at) }}
                  </span>
                  <span v-if="!notification.is_read" class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
                </div>
              </div>
              
              <p class="text-sm md:text-base leading-relaxed max-w-4xl" :class="!notification.is_read ? 'text-slate-700 dark:text-slate-300 font-medium' : 'text-slate-500 dark:text-slate-400'">
                {{ notification.message }}
              </p>

              <div v-if="notification.attachment" class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 text-xs font-bold text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900 shadow-sm transition-transform group-hover:scale-105">
                <Paperclip class="w-3.5 h-3.5" /> View Attached File
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import api from '@/utils/axios' 
import echo from '@/utils/websocket' // IMPORT YOUR CENTRALIZED WEBSOCKET FILE
import { toast } from 'vue-sonner'
import { 
  Bell, BellOff, Loader2, Package, CheckCircle2, AlertCircle, Info, MessageSquare, ShieldAlert, Users, Paperclip, Truck, CircleDollarSign
} from 'lucide-vue-next'

import { Button } from '@/components/ui/button'

const props = defineProps({
  user: {
    type: Object,
    required: true
  }
})

const notifications = ref([])
const unreadCount = ref(0)
const isLoading = ref(true)

// Configure Echo WebSocket using the centralized instance
const setupWebsockets = () => {
  if (!props.user || !props.user.id) return

  // Listen to User's private notification channel using the imported echo instance
  echo.private(`notifications.${props.user.id}`)
    .listen('.NotificationSent', (e) => {
      notifications.value.unshift(e.notification)
      unreadCount.value++
      
      toast('System Alert', {
        description: e.notification.title,
        icon: Bell
      })
    })
}

// Fetch Notifications from DB
const fetchNotifications = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/distributor/notifications')
    if (res.data.success) {
      notifications.value = res.data.data
      unreadCount.value = res.data.unread_count
    }
  } catch (error) {
    console.error("Failed to load notifications", error)
  } finally {
    isLoading.value = false
  }
}

// Read Actions
const markAsRead = async (notification) => {
  if (notification.is_read) return

  // Optimistic update
  notification.is_read = true
  unreadCount.value = Math.max(0, unreadCount.value - 1)

  try {
    await api.put(`/distributor/notifications/${notification.id}/read`)
  } catch (error) {
    // Revert if failed
    notification.is_read = false
    unreadCount.value++
  }
}

const markAllAsRead = async () => {
  // Optimistic update
  notifications.value.forEach(n => n.is_read = true)
  unreadCount.value = 0

  try {
    await api.post('/distributor/notifications/mark-all-read')
  } catch (error) {
    console.error("Failed to mark all as read", error)
  }
}

// Visual Utilities
const getIcon = (type) => {
  const t = type.toLowerCase()
  if (t.includes('order') || t.includes('procurement')) return Package
  if (t.includes('alert') || t.includes('warning') || t.includes('report') || t.includes('rejected')) return ShieldAlert
  if (t.includes('success') || t.includes('approved') || t.includes('verified')) return CheckCircle2
  if (t.includes('message') || t.includes('chat')) return MessageSquare
  if (t.includes('partner') || t.includes('supplier')) return Users
  if (t.includes('delivery') || t.includes('shipped')) return Truck
  if (t.includes('payment') || t.includes('finance') || t.includes('remit')) return CircleDollarSign
  return Info
}

const getIconColor = (type) => {
  const t = type.toLowerCase()
  if (t.includes('order') || t.includes('procurement')) return 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400'
  if (t.includes('alert') || t.includes('warning') || t.includes('report') || t.includes('rejected')) return 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400'
  if (t.includes('success') || t.includes('approved') || t.includes('verified')) return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400'
  if (t.includes('message') || t.includes('chat')) return 'bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400'
  if (t.includes('payment') || t.includes('finance') || t.includes('remit')) return 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'
  if (t.includes('delivery') || t.includes('shipped')) return 'bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400'
  
  return 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
}

// Native JS fallback for "time ago" 
const formatTimeAgo = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)

  let interval = seconds / 31536000
  if (interval > 1) return Math.floor(interval) + ' years ago'
  interval = seconds / 2592000
  if (interval > 1) return Math.floor(interval) + ' months ago'
  interval = seconds / 86400
  if (interval > 1) return Math.floor(interval) + ' days ago'
  interval = seconds / 3600
  if (interval > 1) return Math.floor(interval) + ' hours ago'
  interval = seconds / 60
  if (interval > 1) return Math.floor(interval) + ' min ago'
  
  if (seconds < 10) return 'Just now'
  return Math.floor(seconds) + ' sec ago'
}

onMounted(() => {
  fetchNotifications()
  setupWebsockets()
})

onUnmounted(() => {
  if (echo && props.user) {
    echo.leave(`notifications.${props.user.id}`)
  }
})
</script>