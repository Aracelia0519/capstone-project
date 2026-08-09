<template>
  <div class="admin-tech-reports min-h-screen p-4 md:p-8  text-slate-900">
    <div class="max-w-7xl mx-auto space-y-6">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-black tracking-tight flex items-center gap-3">
            <Wrench class="w-8 h-8 text-indigo-600" />
            System Technical Reports
          </h1>
          <p class="text-slate-500 mt-1">Monitor, analyze, and resolve technical issues reported by users across the platform.</p>
        </div>
        <button @click="refreshData" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2 rounded-xl font-bold hover:bg-slate-50 shadow-sm transition-all">
            <RefreshCw :class="['w-4 h-4', isLoading ? 'animate-spin text-indigo-600' : '']" />
            Refresh Data
        </button>
      </div>

      <!-- Statistics KPI Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-full flex items-center justify-center">
                <FileText class="w-7 h-7" />
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Reports</p>
                <h3 class="text-3xl font-black text-slate-900">{{ stats.total }}</h3>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center">
                <AlertCircle class="w-7 h-7" />
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Review</p>
                <h3 class="text-3xl font-black text-slate-900">{{ stats.pending }}</h3>
            </div>
        </div>
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex items-center gap-4">
            <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center">
                <CheckCircle2 class="w-7 h-7" />
            </div>
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Resolved / Reviewed</p>
                <h3 class="text-3xl font-black text-slate-900">{{ stats.reviewed }}</h3>
            </div>
        </div>
      </div>

      <!-- Analytics Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Issues by Category</h3>
            <div class="h-64 flex justify-center">
                <Pie v-if="hasCategoryData" :data="categoryChartData" :options="pieOptions" />
                <div v-else class="flex flex-col items-center justify-center text-slate-400 h-full w-full">
                    <PieChartIcon class="w-10 h-10 mb-2 opacity-20" />
                    <span class="text-sm">Not enough data to display.</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-6">Reports by User Role</h3>
            <div class="h-64">
                <Bar v-if="hasRoleData" :data="roleChartData" :options="barOptions" />
                <div v-else class="flex flex-col items-center justify-center text-slate-400 h-full w-full">
                    <BarChartIcon class="w-10 h-10 mb-2 opacity-20" />
                    <span class="text-sm">Not enough data to display.</span>
                </div>
            </div>
        </div>
      </div>

      <!-- Main Reports Table -->
      <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-lg font-black text-slate-900">All Technical Reports</h2>
            
            <div class="flex items-center gap-3">
                <div class="relative w-full sm:w-64">
                    <Search class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                    <input v-model="searchQuery" type="text" placeholder="Search user or issue..." class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl pl-9 pr-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                </div>
                <select v-model="statusFilter" class="bg-slate-50 border border-slate-200 text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none appearance-none cursor-pointer">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 text-xs uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="px-6 py-4">Report Details</th>
                        <th class="px-6 py-4">User Info</th>
                        <th class="px-6 py-4">Environment</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    <tr v-if="isLoading" class="bg-white">
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <Loader2 class="w-8 h-8 animate-spin mx-auto text-indigo-500 mb-2" />
                            Loading records...
                        </td>
                    </tr>
                    <tr v-else-if="filteredReports.length === 0" class="bg-white">
                        <td colspan="5" class="py-12 text-center text-slate-400">
                            <FileX class="w-10 h-10 mx-auto mb-3 opacity-30" />
                            No technical reports found matching your criteria.
                        </td>
                    </tr>
                    <tr v-else v-for="report in filteredReports" :key="report.id" class="bg-white hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4 w-1/3">
                            <div class="font-bold text-slate-900 capitalize mb-1">{{ report.category.replace('_', ' ') }}</div>
                            <div class="text-xs text-slate-500 mb-2 line-clamp-2">{{ report.error_message }}</div>
                            <div class="flex items-center gap-3 text-[11px] font-mono text-slate-400">
                                <span class="flex items-center gap-1"><MapPin class="w-3 h-3 text-indigo-500" /> {{ report.page }}</span>
                                <span class="flex items-center gap-1"><Clock class="w-3 h-3 text-indigo-500" /> {{ formatDate(report.created_at) }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs uppercase shrink-0">
                                    {{ report.user_name ? report.user_name.charAt(0) : 'U' }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ report.user_name || 'Unknown User' }}</div>
                                    <div class="text-[11px] text-slate-500 capitalize">{{ report.role.replace('_', ' ') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-600">
                            <div class="flex items-center gap-1.5 mb-1"><Smartphone class="w-3.5 h-3.5 text-slate-400" /> {{ report.device }}</div>
                            <div class="flex items-center gap-1.5"><Globe class="w-3.5 h-3.5 text-slate-400" /> {{ report.browser }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <Badge variant="outline" :class="[
                                'text-[10px] font-black uppercase tracking-widest px-2.5 py-1 border-none',
                                report.status === 'reviewed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
                            ]">
                                {{ report.status }}
                            </Badge>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button v-if="report.attachment" @click="viewImage(report.attachment)" class="p-2 bg-slate-100 text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="View Attachment">
                                    <ImageIcon class="w-4 h-4" />
                                </button>
                                <button v-if="report.status === 'pending'" @click="updateStatus(report.id, 'reviewed')" class="p-2 bg-emerald-50 text-emerald-600 hover:bg-emerald-100 rounded-lg transition-colors" title="Mark as Reviewed">
                                    <CheckCircle2 class="w-4 h-4" />
                                </button>
                                <button v-if="report.status === 'reviewed'" @click="updateStatus(report.id, 'pending')" class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition-colors" title="Revert to Pending">
                                    <CornerUpLeft class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
      </div>
    </div>

    <!-- Image Viewer Modal -->
    <Dialog :open="!!selectedImage" @update:open="selectedImage = null">
      <DialogContent class="bg-white border-slate-200 shadow-2xl max-w-5xl p-2 sm:rounded-2xl flex justify-center items-center">
        <img :src="selectedImage" class="max-h-[85vh] object-contain rounded-xl" />
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { 
    Wrench, RefreshCw, FileText, AlertCircle, CheckCircle2, 
    Search, MapPin, Clock, Smartphone, Globe, 
    Image as ImageIcon, CornerUpLeft, PieChart as PieChartIcon, BarChart as BarChartIcon, Loader2, FileX
} from 'lucide-vue-next'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import echo from '@/utils/websocket'

// Chart.js Setup
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'
import { Bar, Pie } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

const reports = ref([])
const stats = ref({
    total: 0, pending: 0, reviewed: 0, by_category: [], by_role: []
})
const isLoading = ref(true)
const searchQuery = ref('')
const statusFilter = ref('all')
const selectedImage = ref(null)

onMounted(() => {
    fetchData()
    setupWebsockets()
})

onUnmounted(() => {
    if (echo) {
        echo.leave('admin.technical_reports')
    }
})

const setupWebsockets = () => {
    echo.private(`admin.technical_reports`)
        .listen('.TechnicalReportSubmitted', (e) => {
            // Unshift the new report to the top of the array
            if(e.report) {
                 // Trigger full refetch to guarantee user joins are accurate, 
                 // or just push the payload if it's formatted perfectly. 
                 // Let's re-fetch to keep charts and table in perfect sync effortlessly.
                fetchData()
                toast.info('New Technical Report', { 
                    description: `A new ${e.report.category} issue was reported on the ${e.report.page} page.`
                })
            }
        })
}

const fetchData = async () => {
    isLoading.value = true
    try {
        const [reportsRes, statsRes] = await Promise.all([
            api.get('/admin/technical-reports'),
            api.get('/admin/technical-reports/statistics')
        ])

        if (reportsRes.data.success) {
            reports.value = reportsRes.data.data
        }
        if (statsRes.data.success) {
            stats.value = statsRes.data.data
        }
    } catch (error) {
        toast.error('Data Fetch Error', { description: 'Could not load technical reports or statistics.' })
    } finally {
        isLoading.value = false
    }
}

const refreshData = () => {
    fetchData()
}

const updateStatus = async (id, newStatus) => {
    try {
        const response = await api.put(`/admin/technical-reports/${id}/status`, { status: newStatus })
        if (response.data.success) {
            toast.success('Status Updated', { description: `Report marked as ${newStatus}.` })
            
            // Optimistic local update
            const index = reports.value.findIndex(r => r.id === id)
            if (index !== -1) {
                reports.value[index].status = newStatus
                
                // Manually adjust stats for immediate feedback without refetching
                if (newStatus === 'reviewed') {
                    stats.value.pending--
                    stats.value.reviewed++
                } else {
                    stats.value.pending++
                    stats.value.reviewed--
                }
            }
        }
    } catch (error) {
        toast.error('Update Failed', { description: 'Could not change report status.' })
    }
}

const viewImage = (url) => {
    selectedImage.value = url
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// -------------------------------------------------------------
// FILTERING
// -------------------------------------------------------------
const filteredReports = computed(() => {
    let result = reports.value

    if (statusFilter.value !== 'all') {
        result = result.filter(r => r.status === statusFilter.value)
    }

    if (searchQuery.value.trim() !== '') {
        const lowerQ = searchQuery.value.toLowerCase()
        result = result.filter(r => 
            (r.user_name && r.user_name.toLowerCase().includes(lowerQ)) ||
            (r.error_message && r.error_message.toLowerCase().includes(lowerQ)) ||
            (r.category && r.category.toLowerCase().includes(lowerQ)) ||
            (r.page && r.page.toLowerCase().includes(lowerQ))
        )
    }

    return result
})

// -------------------------------------------------------------
// CHARTS LOGIC
// -------------------------------------------------------------
const hasCategoryData = computed(() => stats.value.by_category.length > 0)
const hasRoleData = computed(() => stats.value.by_role.length > 0)

const pieOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, usePointStyle: true, font: { size: 11, family: 'Inter' } } }
    }
}

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false }
    },
    scales: {
        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
        x: { grid: { display: false } }
    }
}

// Array of nice colors for the pie chart
const brandColors = [
    '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', 
    '#ec4899', '#0ea5e9', '#14b8a6', '#f43f5e', '#64748b'
]

const categoryChartData = computed(() => {
    return {
        labels: stats.value.by_category.map(item => item.category.replace('_', ' ').toUpperCase()),
        datasets: [{
            data: stats.value.by_category.map(item => item.count),
            backgroundColor: brandColors.slice(0, stats.value.by_category.length),
            borderWidth: 0,
            hoverOffset: 4
        }]
    }
})

const roleChartData = computed(() => {
    return {
        labels: stats.value.by_role.map(item => item.role.replace('_', ' ').toUpperCase()),
        datasets: [{
            label: 'Reports Filed',
            data: stats.value.by_role.map(item => item.count),
            backgroundColor: '#818cf8',
            borderRadius: 6,
            barThickness: 'flex',
            maxBarThickness: 40
        }]
    }
})
</script>