<template>
  <div class="client-dashboard min-h-screen p-4 md:p-6 lg:p-10 bg-[#0f172a] text-slate-200">
    
    <section class="hero-section relative overflow-hidden rounded-3xl border border-sky-500/10 bg-gradient-to-br from-slate-800/80 to-slate-900/90 p-6 md:p-10 mb-8 shadow-2xl">
      <div class="hero-gradient absolute inset-0 bg-[radial-gradient(circle_at_70%_50%,rgba(56,189,248,0.1),transparent_70%)]"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="hero-left text-center md:text-left">
          <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight mb-4">
            <span class="block">Transform Your Space</span>
            <span class="block bg-gradient-to-r from-sky-400 via-sky-500 to-sky-700 bg-clip-text text-transparent">With Perfect Colors</span>
          </h1>
          <p class="text-slate-400 text-lg mb-8 max-w-xl">Visualize, select, and track your paint projects with real-time updates</p>
          
          <div class="flex flex-wrap justify-center md:justify-start gap-3">
            <Badge variant="outline" class="bg-white/5 border-sky-500/20 px-4 py-2 rounded-full text-sm font-medium backdrop-blur-md">
              <CheckCircle2 class="w-4 h-4 mr-2 text-sky-400" />
              {{ dashboardStats.activeProjects }} Active Projects
            </Badge>
            <Badge variant="outline" class="bg-white/5 border-sky-500/20 px-4 py-2 rounded-full text-sm font-medium backdrop-blur-md">
              <Palette class="w-4 h-4 mr-2 text-sky-400" />
              {{ dashboardStats.colorsSelected }} Colors Selected
            </Badge>
          </div>
        </div>

        <div class="hero-right flex justify-center items-center">
          <div class="color-wheel-animation relative w-32 h-32 md:w-48 md:h-48">
            <div class="color-circle absolute inset-0 rounded-full opacity-30 animate-[spin_20s_linear_infinite] bg-[conic-gradient(from_0deg,#38bdf8,#0ea5e9,#0284c7,#38bdf8)]"></div>
            <div class="color-circle-2 absolute inset-[10%] rounded-full animate-[spin_15s_linear_infinite_reverse] bg-[conic-gradient(from_180deg,#c084fc,#a855f7,#7c3aed,#c084fc)]"></div>
            <div class="color-circle-3 absolute inset-[20%] rounded-full animate-[spin_10s_linear_infinite] bg-[conic-gradient(from_90deg,#10b981,#059669,#047857,#10b981)]"></div>
          </div>
        </div>
      </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
      
      <Card class="lg:col-span-7 bg-slate-800/40 border-slate-700/50 backdrop-blur-xl">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-6">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-sky-500 rounded-lg shadow-lg shadow-sky-500/20">
              <ClipboardList class="w-5 h-5 text-white" />
            </div>
            <CardTitle class="text-xl font-bold text-slate-100">Current Service Requests</CardTitle>
          </div>
          <Badge class="bg-sky-500/10 text-sky-400 border-sky-500/20">{{ serviceRequests.length }} Active</Badge>
        </CardHeader>
        <CardContent class="space-y-4">
          <div v-for="request in serviceRequests" :key="request.id" class="group relative flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl bg-slate-900/50 border border-slate-700/30 hover:border-sky-500/30 transition-all">
            <div class="mb-4 md:mb-0">
              <h4 class="font-semibold text-slate-200 mb-1">{{ request.type }}</h4>
              <div class="flex items-center text-sm text-slate-400 gap-4">
                <span class="flex items-center"><User class="w-3 h-3 mr-1" /> {{ request.serviceProvider }}</span>
                <span>{{ formatDate(request.date) }}</span>
              </div>
            </div>
            <div class="flex flex-col items-start md:items-end gap-2 w-full md:w-40">
              <Badge :class="getStatusClass(request.status)">{{ formatStatus(request.status) }}</Badge>
              <div class="flex items-center gap-2 w-full">
                <Progress :model-value="request.progress" class="h-1.5 flex-1 bg-slate-700" />
                <span class="text-xs text-slate-400 w-8 text-right">{{ request.progress }}%</span>
              </div>
            </div>
          </div>
          <Button variant="ghost" class="w-full mt-2 text-sky-400 hover:bg-sky-500/10 hover:text-sky-300">
            View All Requests <ChevronRight class="w-4 h-4 ml-1" />
          </Button>
        </CardContent>
      </Card>

      <Card class="lg:col-span-5 bg-slate-800/40 border-slate-700/50 backdrop-blur-xl">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-6">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-sky-500 rounded-lg shadow-lg shadow-sky-500/20">
              <Paintbrush2 class="w-5 h-5 text-white" />
            </div>
            <CardTitle class="text-xl font-bold text-slate-100">Selected Colors</CardTitle>
          </div>
          <Badge variant="secondary" class="bg-slate-700 text-slate-300">Latest</Badge>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div v-for="color in selectedColors" :key="color.id" class="overflow-hidden rounded-xl bg-slate-900/50 border border-slate-700/30">
              <div class="h-20 flex items-center justify-center relative" :style="{ backgroundColor: color.hex }">
                <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                <span class="relative z-10 text-white font-bold text-sm drop-shadow-md">{{ color.name }}</span>
              </div>
              <div class="p-3 text-[10px] md:text-xs">
                <div class="flex justify-between text-slate-400 mb-1">
                  <span>HEX: {{ color.hex }}</span>
                  <span>{{ color.date }}</span>
                </div>
                <div class="text-slate-500 truncate">{{ color.project }}</div>
              </div>
            </div>
          </div>
          <Button variant="ghost" class="w-full text-sky-400 hover:bg-sky-500/10">
            View Color History <ChevronRight class="w-4 h-4 ml-1" />
          </Button>
        </CardContent>
      </Card>

      <Card class="lg:col-span-4 bg-slate-800/40 border-slate-700/50 backdrop-blur-xl">
        <CardHeader>
          <div class="flex items-center gap-3">
            <div class="p-2 bg-sky-500 rounded-lg shadow-lg shadow-sky-500/20">
              <Users class="w-5 h-5 text-white" />
            </div>
            <CardTitle class="text-xl font-bold text-slate-100">Providers</CardTitle>
          </div>
        </CardHeader>
        <CardContent class="space-y-4">
          <div v-for="provider in serviceProviders" :key="provider.id" class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-700/30 transition-colors">
            <div class="relative">
              <Avatar class="w-12 h-12 border-2 border-slate-700 bg-sky-500 text-white font-bold">
                <AvatarFallback>{{ provider.initials }}</AvatarFallback>
              </Avatar>
              <span :class="['absolute bottom-0 right-0 w-3 h-3 rounded-full border-2 border-slate-800', provider.status === 'online' ? 'bg-emerald-500' : 'bg-slate-500']"></span>
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="font-medium truncate text-slate-200">{{ provider.name }}</h4>
              <div class="flex items-center text-yellow-500 text-xs">
                <Star v-for="n in 5" :key="n" :class="['w-3 h-3', n <= Math.floor(provider.rating) ? 'fill-current' : 'text-slate-600']" />
                <span class="ml-1 text-slate-400">{{ provider.rating }}</span>
              </div>
              <p class="text-[11px] text-slate-500 truncate">{{ provider.specialty }}</p>
            </div>
            <Button size="icon" variant="outline" class="border-sky-500/20 bg-sky-500/5 text-sky-400 hover:bg-sky-500 hover:text-white">
              <Mail class="w-4 h-4" />
            </Button>
          </div>
        </CardContent>
      </Card>

      <Card class="lg:col-span-8 bg-slate-800/40 border-slate-700/50 backdrop-blur-xl overflow-hidden">
        <CardHeader>
          <div class="flex items-center gap-3">
            <div class="p-2 bg-sky-500 rounded-lg shadow-lg shadow-sky-500/20">
              <History class="w-5 h-5 text-white" />
            </div>
            <CardTitle class="text-xl font-bold text-slate-100">Recent Activity</CardTitle>
          </div>
        </CardHeader>
        <CardContent class="p-0">
          <Table>
            <TableHeader class="bg-slate-900/40">
              <TableRow class="border-slate-700/50">
                <TableHead class="text-slate-400 px-6">Activity</TableHead>
                <TableHead class="text-slate-400">Project</TableHead>
                <TableHead class="text-slate-400">Date</TableHead>
                <TableHead class="text-slate-400 text-right pr-6">Status</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="activity in recentActivity" :key="activity.id" class="border-slate-700/50 hover:bg-slate-700/20">
                <TableCell class="px-6 font-medium">
                  <div class="flex items-center gap-3">
                    <div :class="getActivityIconClass(activity.type)">
                      <Palette v-if="activity.type === 'color'" class="w-4 h-4" />
                      <FileText v-else-if="activity.type === 'request'" class="w-4 h-4" />
                      <Info v-else class="w-4 h-4" />
                    </div>
                    {{ activity.description }}
                  </div>
                </TableCell>
                <TableCell class="text-slate-400">{{ activity.project }}</TableCell>
                <TableCell class="text-slate-400 text-xs">{{ activity.date }}</TableCell>
                <TableCell class="text-right pr-6">
                  <span :class="['text-[10px] font-bold uppercase tracking-wider', activity.status === 'completed' ? 'text-emerald-400' : activity.status === 'in-progress' ? 'text-sky-400' : 'text-amber-400']">
                    {{ activity.status }}
                  </span>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>

    <div class="quick-actions">
      <h3 class="text-lg font-bold mb-4 text-slate-200">Quick Actions</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Button 
          v-for="action in quickActionList" 
          :key="action.label"
          variant="secondary"
          class="h-auto flex-col gap-4 p-6 bg-slate-800/40 border-slate-700/50 hover:border-sky-500/40 transition-all hover:bg-slate-800/60"
          @click="action.handler"
        >
          <div :class="['p-3 rounded-2xl shadow-lg text-white', action.color]">
            <component :is="action.icon" class="w-6 h-6" />
          </div>
          <span class="font-semibold">{{ action.label }}</span>
        </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { 
  Card, CardContent, CardHeader, CardTitle 
} from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Button } from '@/components/ui/button'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { 
  Table, TableBody, TableCell, TableHead, TableHeader, TableRow 
} from '@/components/ui/table'
import { 
  CheckCircle2, Palette, ClipboardList, ChevronRight, 
  User, Paintbrush2, Users, Star, Mail, History, 
  FileText, Info, Plus, Eye, Lightbulb
} from 'lucide-vue-next'

// --- Script Logic (Preserved and Reactive) ---

const dashboardStats = reactive({
  activeProjects: 3,
  colorsSelected: 8,
  totalRequests: 5,
  providersAvailable: 12
})

const serviceRequests = reactive([
  { id: 1, type: 'Living Room Painting', serviceProvider: 'John Paint Masters', date: '2024-03-15', status: 'in-progress', progress: 65 },
  { id: 2, type: 'Exterior House Paint', serviceProvider: 'Cavite Pro Painters', date: '2024-03-20', status: 'pending', progress: 20 },
  { id: 3, type: 'Kitchen Cabinet Paint', serviceProvider: 'Color Experts PH', date: '2024-03-10', status: 'completed', progress: 100 }
])

const selectedColors = reactive([
  { id: 1, name: 'Ocean Breeze', hex: '#38bdf8', rgb: '56, 189, 248', project: 'Bedroom Renovation', date: 'Today' },
  { id: 2, name: 'Misty Morning', hex: '#c084fc', rgb: '192, 132, 252', project: 'Living Room', date: 'Yesterday' },
  { id: 3, name: 'Sunset Glow', hex: '#f59e0b', rgb: '245, 158, 11', project: 'Kitchen Accent', date: 'Mar 18' },
  { id: 4, name: 'Forest Green', hex: '#10b981', rgb: '16, 185, 129', project: 'Study Room', date: 'Mar 15' }
])

const serviceProviders = reactive([
  { id: 1, initials: 'JM', name: 'John Paint Masters', rating: 4.8, specialty: 'Interior & Exterior', status: 'online' },
  { id: 2, initials: 'CP', name: 'Cavite Pro Painters', rating: 4.6, specialty: 'Commercial Projects', status: 'offline' },
  { id: 3, initials: 'CE', name: 'Color Experts PH', rating: 4.9, specialty: 'Color Consultation', status: 'online' }
])

const recentActivity = reactive([
  { id: 1, type: 'color', description: 'Selected new color for bedroom', project: 'Bedroom Renovation', date: 'Today, 10:30 AM', status: 'completed' },
  { id: 2, type: 'request', description: 'Service request submitted', project: 'Kitchen Cabinet Paint', date: 'Mar 18, 2:15 PM', status: 'in-progress' },
  { id: 3, type: 'update', description: 'Project status updated', project: 'Living Room Painting', date: 'Mar 17, 4:45 PM', status: 'pending' },
  { id: 4, type: 'color', description: 'Color preview generated', project: 'Study Room', date: 'Mar 16, 11:20 AM', status: 'completed' },
  { id: 5, type: 'request', description: 'New service inquiry', project: 'Exterior House Paint', date: 'Mar 15, 9:00 AM', status: 'pending' }
])

// --- Methods ---

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric'
  })
}

const formatStatus = (status) => {
  const statusMap = { 'pending': 'Pending', 'in-progress': 'In Progress', 'completed': 'Completed' }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  if (status === 'completed') return 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20'
  if (status === 'in-progress') return 'bg-sky-500/10 text-sky-500 border-sky-500/20'
  return 'bg-amber-500/10 text-amber-500 border-amber-500/20'
}

const getActivityIconClass = (type) => {
  const base = "p-1.5 rounded-md border "
  if (type === 'color') return base + "bg-purple-500/10 text-purple-400 border-purple-500/20"
  if (type === 'request') return base + "bg-sky-500/10 text-sky-400 border-sky-500/20"
  return base + "bg-amber-500/10 text-amber-400 border-amber-500/20"
}

// Quick Action Configurations
const quickActionList = [
  { label: 'New Service Request', icon: Plus, color: 'bg-gradient-to-br from-blue-500 to-cyan-400', handler: () => console.log('New service') },
  { label: 'Browse Colors', icon: Palette, color: 'bg-gradient-to-br from-purple-500 to-pink-400', handler: () => console.log('Browse colors') },
  { label: 'Color Preview', icon: Eye, color: 'bg-gradient-to-br from-indigo-500 to-violet-400', handler: () => console.log('Preview') },
  { label: 'Recommendations', icon: Lightbulb, color: 'bg-gradient-to-br from-amber-500 to-orange-400', handler: () => console.log('Recommendations') }
]
</script>

<style scoped>
/* Keyframe animations that Tailwind doesn't handle natively */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.hero-section {
  backdrop-filter: blur(10px);
}
</style>