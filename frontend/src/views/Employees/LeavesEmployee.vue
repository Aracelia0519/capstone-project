<template>
  <div class="leaves-employee p-4 md:p-6">
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Leave Management</h1>
          <p class="text-gray-500 mt-1">Request and track your leaves</p>
        </div>
        
        <Dialog v-model:open="showRequestModal">
          <DialogTrigger as-child>
            <Button class="mt-4 md:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white shadow-lg h-12 px-6 text-base">
              <i class="fas fa-plus mr-2"></i>
              File Leave Request
            </Button>
          </DialogTrigger>
          <DialogContent class="sm:max-w-[600px]">
            <DialogHeader>
              <DialogTitle class="text-xl font-semibold">File Leave Request</DialogTitle>
            </DialogHeader>
            <div class="space-y-6 py-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Leave Type</Label>
                  <Select>
                    <SelectTrigger>
                      <SelectValue placeholder="Select type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="vacation">Vacation Leave</SelectItem>
                      <SelectItem value="sick">Sick Leave</SelectItem>
                      <SelectItem value="emergency">Emergency Leave</SelectItem>
                      <SelectItem value="maternity">Maternity Leave</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Duration (Days)</Label>
                  <Input type="number" placeholder="Enter number of days" />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Start Date</Label>
                  <Input type="date" />
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">End Date</Label>
                  <Input type="date" />
                </div>
              </div>
              
              <div>
                <Label class="text-sm font-medium text-gray-700 mb-2">Reason</Label>
                <Textarea rows="4" placeholder="Briefly describe the reason for leave" />
              </div>
              
              <div class="flex justify-end space-x-4 pt-2">
                <Button variant="outline" @click="showRequestModal = false">
                  Cancel
                </Button>
                <Button class="bg-indigo-600 hover:bg-indigo-700 text-white">
                  Submit Request
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <Card v-for="balance in leaveBalances" :key="balance.type" class="shadow-lg border-0">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ balance.type }}</h3>
              <div class="mt-2">
                <span class="text-2xl font-bold text-gray-800">{{ balance.available }}</span>
                <span class="text-sm text-gray-500 ml-2">of {{ balance.total }} days</span>
              </div>
            </div>
            <div :class="['p-3 rounded-lg', balance.colorClass]">
              <i :class="[balance.icon, 'text-xl']"></i>
            </div>
          </div>
          <div class="mt-4">
            <Progress :model-value="balance.percentage" class="h-2 bg-gray-200" :class="[balance.progressClass]" />
            <p class="text-xs text-gray-500 mt-2">{{ balance.percentage }}% available</p>
          </div>
        </CardContent>
      </Card>
    </div>

    <Tabs v-model="activeTab" class="w-full">
      <div class="mb-8 border-b border-gray-200">
        <TabsList class="bg-transparent h-auto p-0 space-x-8 justify-start">
          <TabsTrigger 
            v-for="tab in tabs" 
            :key="tab.id" 
            :value="tab.id"
            class="py-3 px-1 rounded-none border-b-2 border-transparent data-[state=active]:border-indigo-600 data-[state=active]:text-indigo-600 text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium text-sm transition-colors shadow-none"
          >
            <i :class="tab.icon + ' mr-2'"></i>
            {{ tab.label }}
          </TabsTrigger>
        </TabsList>
      </div>

      <Card class="shadow-lg border-0">
        <CardContent class="p-6">
          <TabsContent value="history" class="mt-0">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">Leave History</h3>
            <div class="overflow-x-auto">
              <Table>
                <TableHeader class="bg-gray-50">
                  <TableRow>
                    <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Date Filed</TableHead>
                    <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Leave Type</TableHead>
                    <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Duration</TableHead>
                    <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Reason</TableHead>
                    <TableHead class="text-gray-500 font-semibold uppercase tracking-wider">Status</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="leave in leaveHistory" :key="leave.id">
                    <TableCell>{{ leave.dateFiled }}</TableCell>
                    <TableCell>
                      <Badge :class="['rounded-full px-2 py-1 font-semibold shadow-none', leave.typeClass]">
                        {{ leave.type }}
                      </Badge>
                    </TableCell>
                    <TableCell>{{ leave.duration }}</TableCell>
                    <TableCell>{{ leave.reason }}</TableCell>
                    <TableCell>
                      <Badge :class="[
                        'rounded-full px-3 py-1 font-semibold shadow-none',
                        leave.status === 'Approved' ? 'bg-green-100 text-green-800 hover:bg-green-100' :
                        leave.status === 'Pending' ? 'bg-amber-100 text-amber-800 hover:bg-amber-100' :
                        'bg-red-100 text-red-800 hover:bg-red-100'
                      ]">
                        {{ leave.status }}
                      </Badge>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </TabsContent>

          <TabsContent value="request" class="mt-0">
            <h3 class="text-lg font-semibold text-gray-800 mb-6">File Leave Request</h3>
            <div class="max-w-2xl space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Leave Type</Label>
                  <Select>
                    <SelectTrigger>
                      <SelectValue placeholder="Select type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="vacation">Vacation Leave</SelectItem>
                      <SelectItem value="sick">Sick Leave</SelectItem>
                      <SelectItem value="emergency">Emergency Leave</SelectItem>
                      <SelectItem value="maternity">Maternity Leave</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Duration (Days)</Label>
                  <Input type="number" placeholder="Enter number of days" />
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">Start Date</Label>
                  <Input type="date" />
                </div>
                <div>
                  <Label class="text-sm font-medium text-gray-700 mb-2">End Date</Label>
                  <Input type="date" />
                </div>
              </div>
              
              <div>
                <Label class="text-sm font-medium text-gray-700 mb-2">Reason</Label>
                <Textarea rows="4" placeholder="Briefly describe the reason for leave" />
              </div>
              
              <div class="flex justify-end space-x-4">
                <Button variant="outline">Cancel</Button>
                <Button class="bg-indigo-600 hover:bg-indigo-700 text-white">Submit Request</Button>
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
import { Progress } from '@/components/ui/progress'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const showRequestModal = ref(false)
const activeTab = ref('history')

const tabs = [
  { id: 'history', label: 'Leave History', icon: 'fas fa-history' },
  { id: 'request', label: 'File Request', icon: 'fas fa-plus-circle' },
]

// Note: Added progressClass to handle specific Shadcn Progress bar coloring overrides if your global styles allow, 
// otherwise standard Shadcn progress is usually one color.
const leaveBalances = [
  { type: 'Vacation Leave', available: 8, total: 15, percentage: 53, icon: 'fas fa-umbrella-beach text-blue-600', colorClass: 'bg-blue-50', progressClass: '[&>div]:bg-blue-500' },
  { type: 'Sick Leave', available: 12, total: 15, percentage: 80, icon: 'fas fa-heartbeat text-red-600', colorClass: 'bg-red-50', progressClass: '[&>div]:bg-red-500' },
  { type: 'Emergency Leave', available: 2, total: 5, percentage: 40, icon: 'fas fa-exclamation-triangle text-amber-600', colorClass: 'bg-amber-50', progressClass: '[&>div]:bg-amber-500' },
  { type: 'Maternity Leave', available: 105, total: 105, percentage: 100, icon: 'fas fa-baby text-purple-600', colorClass: 'bg-purple-50', progressClass: '[&>div]:bg-purple-500' },
]

const leaveHistory = [
  { id: 1, dateFiled: '2023-11-20', type: 'Vacation Leave', typeClass: 'bg-blue-100 text-blue-800 hover:bg-blue-100', duration: '3 days', reason: 'Family vacation', status: 'Approved' },
  { id: 2, dateFiled: '2023-10-15', type: 'Sick Leave', typeClass: 'bg-red-100 text-red-800 hover:bg-red-100', duration: '2 days', reason: 'Flu', status: 'Approved' },
  { id: 3, dateFiled: '2023-09-05', type: 'Emergency Leave', typeClass: 'bg-amber-100 text-amber-800 hover:bg-amber-100', duration: '1 day', reason: 'Family emergency', status: 'Approved' },
  { id: 4, dateFiled: '2023-12-01', type: 'Vacation Leave', typeClass: 'bg-blue-100 text-blue-800 hover:bg-blue-100', duration: '5 days', reason: 'Christmas holiday', status: 'Pending' },
  { id: 5, dateFiled: '2023-08-20', type: 'Sick Leave', typeClass: 'bg-red-100 text-red-800 hover:bg-red-100', duration: '1 day', reason: 'Doctor appointment', status: 'Approved' },
]
</script>

<style scoped>
.leaves-employee {
  max-width: 1600px;
  margin: 0 auto;
}
</style>