<template>
  <div class="notifications-employee">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Notifications</h1>
          <p class="text-gray-500 mt-1">System alerts and updates</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <button @click="markAllAsRead"
                  class="px-4 py-2 text-sm bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
            Mark All as Read
          </button>
          <button @click="clearAll"
                  class="px-4 py-2 text-sm bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
            Clear All
          </button>
        </div>
      </div>
    </div>

    <!-- Notification Filters -->
    <div class="mb-6">
      <div class="flex flex-wrap gap-2">
        <button v-for="filter in filters" 
                :key="filter.id"
                @click="activeFilter = filter.id"
                :class="[
                  'px-4 py-2 rounded-lg font-medium text-sm transition-colors',
                  activeFilter === filter.id 
                    ? 'bg-indigo-600 text-white' 
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]">
          {{ filter.label }}
          <span v-if="filter.count" 
                :class="[
                  'ml-2 px-2 py-0.5 rounded-full text-xs',
                  activeFilter === filter.id 
                    ? 'bg-white text-indigo-600' 
                    : 'bg-gray-300 text-gray-700'
                ]">
            {{ filter.count }}
          </span>
        </button>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <!-- Unread Notifications -->
      <div v-if="filteredNotifications.length > 0">
        <div v-for="notification in filteredNotifications" 
             :key="notification.id"
             :class="[
               'p-6 border-b border-gray-100 hover:bg-gray-50 transition-colors',
               notification.read ? 'bg-white' : 'bg-blue-50'
             ]">
          <div class="flex items-start">
            <!-- Icon -->
            <div :class="[
              'p-3 rounded-lg mr-4',
              getNotificationType(notification.type).colorClass
            ]">
              <i :class="[getNotificationType(notification.type).icon, getNotificationType(notification.type).iconColor, 'text-lg']"></i>
            </div>
            
            <!-- Content -->
            <div class="flex-1">
              <div class="flex items-start justify-between">
                <div>
                  <h3 class="font-semibold text-gray-800">{{ notification.title }}</h3>
                  <p class="text-gray-600 mt-1">{{ notification.message }}</p>
                  <p class="text-sm text-gray-500 mt-2">
                    <i class="far fa-clock mr-1"></i>
                    {{ notification.time }}
                  </p>
                </div>
                <div class="flex items-center space-x-3 ml-4">
                  <button v-if="!notification.read" 
                          @click="markAsRead(notification.id)"
                          class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                          title="Mark as read">
                    <i class="far fa-circle"></i>
                  </button>
                  <button @click="deleteNotification(notification.id)"
                          class="p-1 text-gray-400 hover:text-red-600 transition-colors"
                          title="Delete">
                    <i class="far fa-trash-alt"></i>
                  </button>
                </div>
              </div>
              
              <!-- Actions for specific notifications -->
              <div v-if="notification.action" class="mt-4 flex space-x-3">
                <button v-for="action in notification.action.buttons" 
                        :key="action.label"
                        @click="handleAction(notification.id, action.type)"
                        :class="[
                          'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                          action.type === 'primary' 
                            ? 'bg-indigo-600 text-white hover:bg-indigo-700' 
                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                        ]">
                  {{ action.label }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-else class="p-12 text-center">
        <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
          <i class="far fa-bell text-gray-400 text-3xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">No notifications</h3>
        <p class="text-gray-500">You're all caught up! New notifications will appear here.</p>
      </div>
    </div>

    <!-- Notification Stats -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
          <div class="p-3 bg-blue-50 rounded-lg mr-4">
            <i class="fas fa-bell text-blue-600 text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-gray-600">Total Notifications</p>
            <p class="text-2xl font-bold text-gray-800">{{ totalNotifications }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
          <div class="p-3 bg-green-50 rounded-lg mr-4">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-gray-600">Unread Notifications</p>
            <p class="text-2xl font-bold text-gray-800">{{ unreadCount }}</p>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
          <div class="p-3 bg-purple-50 rounded-lg mr-4">
            <i class="fas fa-file-invoice-dollar text-purple-600 text-xl"></i>
          </div>
          <div>
            <p class="text-sm text-gray-600">Payslip Alerts</p>
            <p class="text-2xl font-bold text-gray-800">{{ payslipNotifications }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const activeFilter = ref('all')

const filters = [
  { id: 'all', label: 'All', count: null },
  { id: 'unread', label: 'Unread', count: null },
  { id: 'payroll', label: 'Payroll', count: null },
  { id: 'attendance', label: 'Attendance', count: null },
  { id: 'leave', label: 'Leave', count: null },
]

const notifications = ref([
  { id: 1, type: 'payslip', title: 'Payslip Released', message: 'Your November 2023 payslip has been released. You can now view and download it from the payroll section.', time: '2 hours ago', read: false },
  { id: 2, type: 'leave', title: 'Leave Request Approved', message: 'Your vacation leave request for December 20-22, 2023 has been approved by your manager.', time: '1 day ago', read: false },
  { id: 3, type: 'attendance', title: 'Attendance Correction Required', message: 'Your time out for December 5, 2023 was flagged. Please submit a correction request.', time: '2 days ago', read: true, action: { buttons: [{ label: 'Submit Correction', type: 'primary' }, { label: 'View Details', type: 'secondary' }] } },
  { id: 4, type: 'system', title: 'System Maintenance', message: 'The HR system will undergo maintenance on December 15, 2023 from 10:00 PM to 2:00 AM.', time: '3 days ago', read: true },
  { id: 5, type: 'payroll', title: 'Bonus Notification', message: 'Your Q3 performance bonus has been processed and will be included in your next payroll.', time: '4 days ago', read: true },
  { id: 6, type: 'leave', title: 'Leave Request Rejected', message: 'Your sick leave request for December 8, 2023 was rejected due to insufficient documentation.', time: '5 days ago', read: true, action: { buttons: [{ label: 'Re-submit', type: 'primary' }, { label: 'View Reason', type: 'secondary' }] } },
  { id: 7, type: 'overtime', title: 'Overtime Request Approved', message: 'Your overtime request for December 7, 2023 (2 hours) has been approved.', time: '6 days ago', read: true },
  { id: 8, type: 'attendance', title: 'Absence Notification', message: 'You were marked absent on December 1, 2023. Please submit a leave request or attendance correction if this is incorrect.', time: '1 week ago', read: true },
])

const notificationTypes = {
  payslip: { icon: 'fas fa-file-invoice-dollar', colorClass: 'bg-purple-50', iconColor: 'text-purple-600' },
  leave: { icon: 'fas fa-calendar-alt', colorClass: 'bg-blue-50', iconColor: 'text-blue-600' },
  attendance: { icon: 'fas fa-clock', colorClass: 'bg-amber-50', iconColor: 'text-amber-600' },
  system: { icon: 'fas fa-cogs', colorClass: 'bg-gray-50', iconColor: 'text-gray-600' },
  payroll: { icon: 'fas fa-money-check-alt', colorClass: 'bg-green-50', iconColor: 'text-green-600' },
  overtime: { icon: 'fas fa-business-time', colorClass: 'bg-indigo-50', iconColor: 'text-indigo-600' },
}

const filteredNotifications = computed(() => {
  if (activeFilter.value === 'all') {
    return notifications.value
  } else if (activeFilter.value === 'unread') {
    return notifications.value.filter(n => !n.read)
  } else {
    return notifications.value.filter(n => n.type === activeFilter.value)
  }
})

const totalNotifications = computed(() => notifications.value.length)
const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)
const payslipNotifications = computed(() => notifications.value.filter(n => n.type === 'payslip').length)

const getNotificationType = (type) => {
  return notificationTypes[type] || notificationTypes.system
}

const markAsRead = (id) => {
  const notification = notifications.value.find(n => n.id === id)
  if (notification) {
    notification.read = true
  }
}

const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
}

const deleteNotification = (id) => {
  notifications.value = notifications.value.filter(n => n.id !== id)
}

const clearAll = () => {
  notifications.value = []
}

const handleAction = (id, actionType) => {
  console.log('Action performed:', actionType, 'for notification:', id)
  // Implement action handling
}
</script>

<style scoped>
.notifications-employee {
  max-width: 1400px;
  margin: 0 auto;
}
</style>