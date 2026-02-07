<template>
  <div class="dashboard-employee p-4 md:p-6">
    <div class="mb-8">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
        Welcome back, <span class="text-indigo-600">Julian Namoc</span>
      </h1>
      <p class="text-gray-500 mt-1">Today is {{ formattedDate }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <Card class="border-l-4 border-green-500 shadow-lg">
        <CardContent class="p-6 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Today's Status</h3>
            <div class="mt-2">
              <span class="text-2xl font-bold text-gray-800">IN</span>
              <span class="ml-2 text-sm text-green-600">● Present</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Time In: 8:30 AM</p>
          </div>
          <div class="p-3 bg-green-50 rounded-lg">
            <i class="fas fa-clock text-green-600 text-xl"></i>
          </div>
        </CardContent>
      </Card>

      <Card class="border-l-4 border-blue-500 shadow-lg">
        <CardContent class="p-6 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Next Payday</h3>
            <div class="mt-2">
              <span class="text-2xl font-bold text-gray-800">{{ nextPayday }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">{{ daysUntilPayday }} days remaining</p>
          </div>
          <div class="p-3 bg-blue-50 rounded-lg">
            <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
          </div>
        </CardContent>
      </Card>

      <Card class="border-l-4 border-purple-500 shadow-lg">
        <CardContent class="p-6 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Last Payslip</h3>
            <div class="mt-2">
              <span class="text-2xl font-bold text-gray-800">₱{{ latestPayslip }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Period: Oct 16-31, 2023</p>
          </div>
          <div class="p-3 bg-purple-50 rounded-lg">
            <i class="fas fa-file-invoice-dollar text-purple-600 text-xl"></i>
          </div>
        </CardContent>
      </Card>

      <Card class="border-l-4 border-amber-500 shadow-lg">
        <CardContent class="p-6 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Pending Requests</h3>
            <div class="mt-2">
              <span class="text-2xl font-bold text-gray-800">{{ pendingRequests }}</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Requiring attention</p>
          </div>
          <div class="p-3 bg-amber-50 rounded-lg">
            <i class="fas fa-exclamation-circle text-amber-600 text-xl"></i>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card class="shadow-lg">
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-gray-800">Quick Actions</CardTitle>
        </CardHeader>
        <CardContent class="space-y-3">
          <Button variant="ghost" class="w-full justify-between h-auto p-3 hover:bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="p-2 bg-indigo-100 rounded-lg mr-3">
                <i class="fas fa-clock text-indigo-600"></i>
              </div>
              <span class="text-gray-700">Clock In/Out</span>
            </div>
            <i class="fas fa-chevron-right text-gray-400"></i>
          </Button>

          <Button variant="ghost" class="w-full justify-between h-auto p-3 hover:bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="p-2 bg-green-100 rounded-lg mr-3">
                <i class="fas fa-calendar-plus text-green-600"></i>
              </div>
              <span class="text-gray-700">File Leave</span>
            </div>
            <i class="fas fa-chevron-right text-gray-400"></i>
          </Button>

          <Button variant="ghost" class="w-full justify-between h-auto p-3 hover:bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="p-2 bg-blue-100 rounded-lg mr-3">
                <i class="fas fa-file-alt text-blue-600"></i>
              </div>
              <span class="text-gray-700">View Payslip</span>
            </div>
            <i class="fas fa-chevron-right text-gray-400"></i>
          </Button>
        </CardContent>
      </Card>

      <Card class="shadow-lg">
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-gray-800">Recent Activity</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="flex items-start">
            <div class="p-2 bg-green-100 rounded-lg mr-3">
              <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div>
              <p class="text-sm text-gray-800">Attendance correction approved</p>
              <p class="text-xs text-gray-500">2 hours ago</p>
            </div>
          </div>
          <div class="flex items-start">
            <div class="p-2 bg-blue-100 rounded-lg mr-3">
              <i class="fas fa-file-invoice text-blue-600"></i>
            </div>
            <div>
              <p class="text-sm text-gray-800">November payslip released</p>
              <p class="text-xs text-gray-500">1 day ago</p>
            </div>
          </div>
          <div class="flex items-start">
            <div class="p-2 bg-amber-100 rounded-lg mr-3">
              <i class="fas fa-clock text-amber-600"></i>
            </div>
            <div>
              <p class="text-sm text-gray-800">Overtime request pending</p>
              <p class="text-xs text-gray-500">3 days ago</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="shadow-lg">
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-gray-800">Upcoming</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-calendar-day text-red-600"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-800">Payday</p>
                <p class="text-xs text-gray-500">Dec 15, 2023</p>
              </div>
            </div>
            <span class="text-sm font-semibold text-red-600">3 days</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-plane text-purple-600"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-800">Leave Approved</p>
                <p class="text-xs text-gray-500">Dec 20-22</p>
              </div>
            </div>
            <span class="text-sm font-semibold text-green-600">Confirmed</span>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-briefcase text-blue-600"></i>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-800">Team Meeting</p>
                <p class="text-xs text-gray-500">Dec 18, 10:00 AM</p>
              </div>
            </div>
            <span class="text-sm font-semibold text-blue-600">Required</span>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'

const formattedDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const nextPayday = ref('Dec 15, 2023')
const daysUntilPayday = computed(() => {
  const payday = new Date('2023-12-15')
  const today = new Date()
  const diffTime = payday - today
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
})

const latestPayslip = ref('45,750.00')
const pendingRequests = ref('3')
</script>

<style scoped>
.dashboard-employee {
  max-width: 1600px;
  margin: 0 auto;
}
</style>