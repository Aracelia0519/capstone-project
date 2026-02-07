<template>
  <div class="min-h-screen text-slate-200">
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Service Provider Dashboard</h1>
          <p class="text-gray-400 text-sm md:text-base">Welcome back, <span class="text-blue-400 font-semibold">Maria Santos</span></p>
        </div>
        <div class="mt-4 md:mt-0">
          <div class="flex items-center space-x-3">
            <div class="relative">
              <Avatar class="h-10 w-10 border-2 border-transparent bg-gradient-to-br from-blue-500 to-purple-500">
                <AvatarFallback class="bg-transparent text-white font-bold">MS</AvatarFallback>
              </Avatar>
              <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-gray-900"></div>
            </div>
            <div class="text-right">
              <p class="text-white text-sm font-medium">Service Provider</p>
              <p class="text-gray-400 text-xs">CaviteGo Paint</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="mt-6">
        <div class="inline-flex items-center space-x-3 px-4 py-2 rounded-lg bg-gray-900 border border-gray-800">
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
          <span class="text-gray-300 text-sm">{{ currentDate }}</span>
          <span class="text-gray-500">•</span>
          <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          <span class="text-gray-300 text-sm">{{ currentTime }}</span>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
      <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-blue-500 transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/10">
        <CardContent class="p-5 flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Total Clients</p>
            <h3 class="text-3xl font-bold text-white mb-2">142</h3>
            <div class="flex items-center">
              <span class="text-green-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                +12% from last month
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-amber-500 transition-all duration-300 hover:shadow-lg hover:shadow-amber-500/10">
        <CardContent class="p-5 flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Active Jobs</p>
            <h3 class="text-3xl font-bold text-white mb-2">8</h3>
            <div class="flex items-center">
              <span class="text-amber-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                2 high priority
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-400 flex items-center justify-center text-white shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-green-500 transition-all duration-300 hover:shadow-lg hover:shadow-green-500/10">
        <CardContent class="p-5 flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Completed Jobs</p>
            <h3 class="text-3xl font-bold text-white mb-2">47</h3>
            <div class="flex items-center">
              <span class="text-green-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                94% satisfaction
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-400 flex items-center justify-center text-white shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </CardContent>
      </Card>

      <Card class="bg-gradient-to-br from-gray-900 to-gray-950 border-gray-800 hover:border-purple-500 transition-all duration-300 hover:shadow-lg hover:shadow-purple-500/10">
        <CardContent class="p-5 flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-sm mb-1">Monthly Revenue</p>
            <h3 class="text-3xl font-bold text-white mb-2">₱85,420</h3>
            <div class="flex items-center">
              <span class="text-purple-400 text-sm font-medium flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                +18% from last month
              </span>
            </div>
          </div>
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-400 flex items-center justify-center text-white shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <Card class="bg-gray-900 border-gray-800">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <div>
              <CardTitle class="text-xl font-bold text-white">Recent Color Customizations</CardTitle>
              <p class="text-gray-400 text-sm mt-1">Latest paint colors created with clients</p>
            </div>
            <Button variant="ghost" class="text-blue-400 hover:text-blue-300 hover:bg-gray-800">
              View All <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            </Button>
          </CardHeader>
          <CardContent>
            <div class="hidden md:block">
              <Table>
                <TableHeader>
                  <TableRow class="border-gray-800 hover:bg-transparent">
                    <TableHead class="text-gray-400">Color</TableHead>
                    <TableHead class="text-gray-400">Client</TableHead>
                    <TableHead class="text-gray-400">Project</TableHead>
                    <TableHead class="text-gray-400">Status</TableHead>
                    <TableHead class="text-gray-400">Date</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="color in recentColors" :key="color.id" class="border-gray-800 hover:bg-gray-800/50">
                    <TableCell>
                      <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg mr-3 shadow-sm" :style="{ backgroundColor: color.hex }"></div>
                        <div class="min-w-0">
                          <p class="text-white font-medium truncate">{{ color.name }}</p>
                          <p class="text-gray-400 text-xs truncate">{{ color.hex }}</p>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="flex items-center">
                        <Avatar class="h-8 w-8 mr-3 bg-gradient-to-br from-blue-500 to-purple-500 border-0">
                          <AvatarFallback class="bg-transparent text-white text-xs font-bold">{{ getInitials(color.client) }}</AvatarFallback>
                        </Avatar>
                        <div class="min-w-0">
                          <p class="text-white font-medium truncate">{{ color.client }}</p>
                          <p class="text-gray-400 text-xs truncate">{{ color.clientLocation }}</p>
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="min-w-0">
                        <p class="text-white font-medium truncate">{{ color.project }}</p>
                        <p class="text-gray-400 text-xs truncate">{{ color.roomType }}</p>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge :class="[
                        color.status === 'Approved' ? 'bg-gradient-to-r from-emerald-500 to-green-400 hover:from-emerald-600 hover:to-green-500 border-0' : 
                        color.status === 'Pending' ? 'bg-gradient-to-r from-amber-500 to-yellow-400 hover:from-amber-600 hover:to-yellow-500 border-0' : 
                        'bg-gradient-to-r from-blue-500 to-cyan-400 hover:from-blue-600 hover:to-cyan-500 border-0'
                      ]">
                        {{ color.status }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div class="min-w-0">
                        <p class="text-white">{{ color.date }}</p>
                        <p class="text-gray-400 text-xs">{{ color.time }}</p>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <div class="md:hidden space-y-4">
               <div v-for="color in recentColors" :key="color.id" class="bg-gray-800/50 rounded-lg p-4">
                  <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center">
                      <div class="w-10 h-10 rounded-lg mr-3 shadow-sm" :style="{ backgroundColor: color.hex }"></div>
                      <div>
                        <p class="text-white font-medium">{{ color.name }}</p>
                        <p class="text-gray-400 text-xs">{{ color.hex }}</p>
                      </div>
                    </div>
                    <Badge :class="[
                        color.status === 'Approved' ? 'bg-gradient-to-r from-emerald-500 to-green-400 border-0' : 
                        color.status === 'Pending' ? 'bg-gradient-to-r from-amber-500 to-yellow-400 border-0' : 
                        'bg-gradient-to-r from-blue-500 to-cyan-400 border-0'
                      ]">
                      {{ color.status }}
                    </Badge>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                     <div>
                       <p class="text-gray-400 text-xs mb-1">Client</p>
                       <p class="text-white text-sm truncate">{{ color.client }}</p>
                     </div>
                     </div>
               </div>
            </div>
          </CardContent>
        </Card>

        <div class="mt-6 bg-gradient-to-r from-purple-900/30 to-pink-900/30 border border-purple-700/50 rounded-xl p-4 md:p-6">
          <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="flex items-center mb-4 md:mb-0">
              <div class="mr-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-purple-600 to-pink-500 flex items-center justify-center shadow-lg">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>
                </div>
              </div>
              <div>
                <h3 class="text-xl font-bold text-white mb-1">Virtual Paint Color Mixer</h3>
                <p class="text-gray-300 text-sm">Create custom paint colors with real-time visualization</p>
              </div>
            </div>
            <Button @click="openColorMixer" class="w-full md:w-auto bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white border-0 shadow-lg shadow-purple-500/20">
              <span>Launch Unity Mixer</span>
              <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
            </Button>
          </div>
          <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
             <div class="text-center p-3 bg-gray-900/50 rounded-lg">
               <p class="text-gray-400 text-sm">Colors Created</p>
               <p class="text-xl md:text-2xl font-bold text-white">156</p>
             </div>
             <div class="text-center p-3 bg-gray-900/50 rounded-lg">
               <p class="text-gray-400 text-sm">Avg. Time</p>
               <p class="text-xl md:text-2xl font-bold text-white">8m</p>
             </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <Card class="bg-gray-900 border-gray-800">
          <CardHeader>
            <CardTitle class="text-xl font-bold text-white">Recent Activity</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-6">
              <div v-for="activity in recentActivities" :key="activity.id" class="pb-6 border-b border-gray-800 last:border-0 last:pb-0">
                <div class="flex items-start">
                  <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 border', 
                    activity.type === 'color' ? 'bg-purple-500/10 text-purple-400 border-purple-500/20' : 
                    activity.type === 'job' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 
                    activity.type === 'client' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 
                    'bg-blue-500/10 text-blue-400 border-blue-500/20']">
                    <component :is="activity.icon" class="w-4 h-4" />
                  </div>
                  <div class="ml-4 flex-1 min-w-0">
                    <p class="text-white font-medium truncate">{{ activity.title }}</p>
                    <p class="text-gray-400 text-sm mt-1 line-clamp-2">{{ activity.description }}</p>
                    <p class="text-gray-500 text-xs mt-2">{{ activity.time }}</p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gray-900 border-gray-800">
          <CardHeader>
            <CardTitle class="text-xl font-bold text-white">Quick Actions</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 gap-3 md:gap-4">
              <button @click="createNewClient" class="p-4 rounded-xl flex flex-col items-center justify-center text-center bg-blue-900/20 border border-blue-700/30 hover:bg-blue-900/30 hover:-translate-y-1 transition-all">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center mb-3 shadow-lg shadow-blue-500/20">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                </div>
                <span class="text-white font-medium text-sm">New Client</span>
              </button>
              <button @click="checkInventory" class="p-4 rounded-xl flex flex-col items-center justify-center text-center bg-cyan-900/20 border border-cyan-700/30 hover:bg-cyan-900/30 hover:-translate-y-1 transition-all">
                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-cyan-500 to-teal-400 flex items-center justify-center mb-3 shadow-lg shadow-cyan-500/20">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
                <span class="text-white font-medium text-sm">Inventory</span>
              </button>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gray-900 border-gray-800">
           <CardHeader><CardTitle class="text-xl font-bold text-white">Performance Metrics</CardTitle></CardHeader>
           <CardContent class="space-y-4">
              <div>
                 <div class="flex justify-between mb-1">
                    <span class="text-gray-300 text-sm">Job Completion Rate</span>
                    <span class="text-white font-medium">92%</span>
                 </div>
                 <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-green-400 rounded-full" style="width: 92%"></div>
                 </div>
              </div>
              </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'

// Define components for icons to be used in dynamic component
const PaletteIcon = { template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" /></svg>` }
const JobIcon = { template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>` }
const ClientIcon = { template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>` }
const PaymentIcon = { template: `<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>` }

const router = useRouter()
const currentDate = ref('')
const currentTime = ref('')
const dateTimeInterval = ref(null)

const recentColors = ref([
  { id: 1, name: 'Ocean Breeze', hex: '#4CC9F0', client: 'Juan Dela Cruz', clientLocation: 'Bacoor, Cavite', project: 'Bedroom Renovation', roomType: 'Master Bedroom', status: 'Approved', date: 'Feb 15, 2024', time: '10:30 AM' },
  { id: 2, name: 'Sage Green', hex: '#8A9A5B', client: 'Maria Gonzales', clientLocation: 'Imus, Cavite', project: 'Living Room Update', roomType: 'Living Room', status: 'In Progress', date: 'Feb 14, 2024', time: '2:15 PM' },
  { id: 3, name: 'Coral Sunset', hex: '#FF7F50', client: 'Robert Lim', clientLocation: 'Dasmarinas, Cavite', project: 'Kitchen Remodel', roomType: 'Kitchen', status: 'Pending', date: 'Feb 13, 2024', time: '4:45 PM' },
  { id: 4, name: 'Midnight Blue', hex: '#191970', client: 'Anna Santos', clientLocation: 'General Trias', project: 'Office Space', roomType: 'Home Office', status: 'Approved', date: 'Feb 12, 2024', time: '9:20 AM' },
  { id: 5, name: 'Soft Lavender', hex: '#B19CD9', client: 'Carlos Reyes', clientLocation: 'Trece Martires', project: 'Nursery Design', roomType: 'Baby Room', status: 'In Progress', date: 'Feb 11, 2024', time: '3:00 PM' }
])

const recentActivities = ref([
  { id: 1, type: 'color', icon: PaletteIcon, title: 'Created new color mix', description: 'Designed "Ocean Breeze" for Juan Dela Cruz', time: '2 hours ago' },
  { id: 2, type: 'job', icon: JobIcon, title: 'Completed service job', description: 'Finished painting project at Gonzales residence', time: '5 hours ago' },
  { id: 3, type: 'client', icon: ClientIcon, title: 'New client onboarded', description: 'Anna Santos signed up for office painting', time: 'Yesterday' },
  { id: 4, type: 'payment', icon: PaymentIcon, title: 'Payment received', description: '₱25,000 from Lim Kitchen project', time: '2 days ago' }
])

const updateDateTime = () => {
  const now = new Date()
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
  currentTime.value = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

const getInitials = (name) => name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2)

const openColorMixer = () => {
  router.push('/service-provider/color-mixer')
  console.log('Opening Unity Virtual Paint Color Mixer...')
}

const createNewClient = () => console.log('Creating new client...')
const createServiceRequest = () => console.log('Creating new service request...')
const viewReports = () => console.log('Viewing reports...')
const checkInventory = () => console.log('Checking inventory...')

onMounted(() => {
  updateDateTime()
  dateTimeInterval.value = setInterval(updateDateTime, 60000)
})

onBeforeUnmount(() => {
  if (dateTimeInterval.value) clearInterval(dateTimeInterval.value)
})
</script>