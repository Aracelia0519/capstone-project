<template>
  <div class="attendance-employee p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Time Tracking</h1>
          <p class="text-gray-500 mt-1 text-sm md:text-base">Monitor your attendance and working hours</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
          <Button class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg h-12 px-6 text-base w-full sm:w-auto justify-center">
            <i class="fas fa-clock mr-2"></i>
            Clock In/Out
          </Button>
          <Button variant="outline" class="border-indigo-600 text-indigo-600 hover:bg-indigo-50 h-12 px-6 text-base w-full sm:w-auto justify-center">
            <i class="fas fa-edit mr-2"></i>
            Request Correction
          </Button>
        </div>
      </div>
    </div>

    <Card class="bg-gradient-to-r from-indigo-500 to-purple-600 shadow-lg border-0 mb-8 text-white">
      <CardContent class="p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div>
            <h3 class="text-xl font-semibold mb-2">Today's Attendance</h3>
            <div class="flex items-center">
              <div class="w-3 h-3 bg-green-400 rounded-full mr-3 animate-pulse"></div>
              <span class="text-lg">Currently Clocked In</span>
            </div>
            <p class="mt-2 opacity-90 text-sm md:text-base">Duration: 6 hours 30 minutes</p>
          </div>
          <div class="text-left md:text-center bg-white/10 md:bg-transparent p-4 md:p-0 rounded-lg">
            <div class="text-3xl md:text-4xl font-bold mb-1">8:30 AM</div>
            <p class="opacity-90 text-sm">Time In</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <Tabs v-model="activeTab" class="w-full">
      <div class="mb-6 md:mb-8 border-b border-gray-200">
        <div class="overflow-x-auto pb-1 -mb-1 w-full">
          <TabsList class="bg-transparent h-auto p-0 flex w-max min-w-full md:w-full space-x-2 md:space-x-8 justify-start">
            <TabsTrigger 
              v-for="tab in tabs" 
              :key="tab.id" 
              :value="tab.id"
              class="py-3 px-3 md:px-1 rounded-none border-b-2 border-transparent data-[state=active]:border-indigo-600 data-[state=active]:text-indigo-600 text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition-colors shadow-none whitespace-nowrap"
            >
              <i :class="tab.icon + ' mr-2'"></i>
              {{ tab.label }}
            </TabsTrigger>
          </TabsList>
        </div>
      </div>

      <Card class="shadow-lg border-0">
        <CardContent class="p-4 md:p-6">
          
          <TabsContent value="daily" class="mt-0">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Daily Time Record</h3>
            <div class="rounded-md border border-gray-100 overflow-hidden">
              <div class="overflow-x-auto">
                <Table>
                  <TableHeader class="bg-gray-50">
                    <TableRow>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Date</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Time In</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Time Out</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Hours</TableHead>
                      <TableHead class="text-gray-500 font-semibold uppercase tracking-wider whitespace-nowrap">Status</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="record in dailyRecords" :key="record.date">
                      <TableCell class="font-medium whitespace-nowrap">{{ record.date }}</TableCell>
                      <TableCell class="whitespace-nowrap">{{ record.timeIn }}</TableCell>
                      <TableCell class="whitespace-nowrap">{{ record.timeOut }}</TableCell>
                      <TableCell>{{ record.hours }}</TableCell>
                      <TableCell>
                        <Badge :class="[
                          'rounded-full px-2 py-1 font-semibold shadow-none',
                          record.status === 'Present' ? 'bg-green-100 text-green-800 hover:bg-green-100' :
                          record.status === 'Late' ? 'bg-amber-100 text-amber-800 hover:bg-amber-100' :
                          'bg-red-100 text-red-800 hover:bg-red-100'
                        ]">
                          {{ record.status }}
                        </Badge>
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </TabsContent>

          <TabsContent value="monthly" class="mt-0">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Attendance Summary</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-8">
              <div class="bg-gradient-to-r from-green-50 to-emerald-100 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="p-3 bg-green-100 rounded-lg mr-4 flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Days Present</p>
                    <p class="text-2xl font-bold text-gray-800">21</p>
                  </div>
                </div>
              </div>
              <div class="bg-gradient-to-r from-amber-50 to-yellow-100 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="p-3 bg-amber-100 rounded-lg mr-4 flex-shrink-0">
                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Late Arrivals</p>
                    <p class="text-2xl font-bold text-gray-800">2</p>
                  </div>
                </div>
              </div>
              <div class="bg-gradient-to-r from-blue-50 to-cyan-100 rounded-lg p-6">
                <div class="flex items-center">
                  <div class="p-3 bg-blue-100 rounded-lg mr-4 flex-shrink-0">
                    <i class="fas fa-business-time text-blue-600 text-xl"></i>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600">Overtime Hours</p>
                    <p class="text-2xl font-bold text-gray-800">15.5</p>
                  </div>
                </div>
              </div>
            </div>
          </TabsContent>

          <TabsContent value="overtime" class="mt-0">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Overtime Records</h3>
            <div class="space-y-4">
              <div v-for="ot in overtimeRecords" :key="ot.date" class="border border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                  <div>
                    <p class="font-medium text-gray-800">{{ ot.date }}</p>
                    <p class="text-sm text-gray-600">{{ ot.time }} • {{ ot.duration }} hours</p>
                    <p class="text-sm text-gray-600 mt-1 italic">"{{ ot.reason }}"</p>
                  </div>
                  <div class="self-start md:self-center">
                    <Badge :class="[
                      'rounded-full px-3 py-1 text-sm font-semibold shadow-none',
                      ot.status === 'Approved' ? 'bg-green-100 text-green-800 hover:bg-green-100' :
                      ot.status === 'Pending' ? 'bg-amber-100 text-amber-800 hover:bg-amber-100' :
                      'bg-red-100 text-red-800 hover:bg-red-100'
                    ]">
                      {{ ot.status }}
                    </Badge>
                  </div>
                </div>
              </div>
            </div>
          </TabsContent>

        </CardContent>
      </Card>
    </Tabs>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs'
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'

const activeTab = ref('daily')

const tabs = [
  { id: 'daily', label: 'Daily Time Record', icon: 'fas fa-calendar-day' },
  { id: 'monthly', label: 'Monthly Summary', icon: 'fas fa-calendar-alt' },
  { id: 'overtime', label: 'Overtime Records', icon: 'fas fa-business-time' },
]

const dailyRecords = [
  { date: '2023-12-10', timeIn: '8:30 AM', timeOut: '5:30 PM', hours: '9.0', status: 'Present' },
  { date: '2023-12-09', timeIn: '9:15 AM', timeOut: '6:00 PM', hours: '8.75', status: 'Late' },
  { date: '2023-12-08', timeIn: '8:45 AM', timeOut: '5:45 PM', hours: '9.0', status: 'Present' },
  { date: '2023-12-07', timeIn: '8:30 AM', timeOut: '7:30 PM', hours: '11.0', status: 'Present' },
  { date: '2023-12-06', timeIn: '8:30 AM', timeOut: '5:30 PM', hours: '9.0', status: 'Present' },
]

const overtimeRecords = [
  { date: '2023-12-07', time: '5:30 PM - 7:30 PM', duration: '2.0', reason: 'Project deadline', status: 'Approved' },
  { date: '2023-12-05', time: '5:30 PM - 8:00 PM', duration: '2.5', reason: 'Team meeting', status: 'Pending' },
  { date: '2023-11-30', time: '6:00 PM - 9:00 PM', duration: '3.0', reason: 'Client presentation', status: 'Approved' },
]
</script>

<style scoped>
.attendance-employee {
  max-width: 1600px;
  margin: 0 auto;
}
/* Hide scrollbar for cleaner look in tabs if desired, though functional scrolling is priority */
.overflow-x-auto::-webkit-scrollbar {
  height: 4px;
}
.overflow-x-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
  background-color: #e5e7eb;
  border-radius: 20px;
}
</style>