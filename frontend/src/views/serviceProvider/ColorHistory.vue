<template>
  <div class="min-h-screen p-8 text-slate-200">
    <div class="mb-8">
      <div class="flex flex-col gap-6">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-violet-500 to-pink-500 flex items-center justify-center text-white shadow-lg shadow-violet-500/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
          <div>
            <h1 class="text-3xl font-bold text-white">Color History</h1>
            <p class="text-slate-400">Reuse previous color mixes</p>
          </div>
        </div>
        
        <div class="flex flex-wrap gap-4 items-center justify-between">
          <div class="relative flex-1 min-w-[250px] max-w-md">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <Input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search by client or color..."
              class="pl-10 bg-slate-900/80 border-slate-800 text-white placeholder:text-slate-500 focus-visible:ring-violet-500"
            />
          </div>
          
          <Button 
            @click="toggleFilter" 
            variant="outline" 
            class="bg-slate-900/80 border-slate-800 text-slate-300 hover:bg-slate-800 hover:text-white"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
            Filter
            <Badge v-if="activeFilterCount > 0" class="ml-2 bg-violet-600 hover:bg-violet-700">{{ activeFilterCount }}</Badge>
          </Button>
        </div>
      </div>
      
      <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
        <Card v-if="showFilter" class="mt-6 bg-slate-900/80 border-slate-800 backdrop-blur-sm">
          <CardContent class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="space-y-2">
                <Label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sort by</Label>
                <Select v-model="sortBy">
                  <SelectTrigger class="bg-slate-950/60 border-slate-700 text-white"><SelectValue placeholder="Select" /></SelectTrigger>
                  <SelectContent class="bg-slate-900 border-slate-700 text-white">
                    <SelectItem value="newest">Newest First</SelectItem>
                    <SelectItem value="oldest">Oldest First</SelectItem>
                    <SelectItem value="client">Client Name</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div class="space-y-2">
                <Label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Date Range</Label>
                <Select v-model="dateRange">
                  <SelectTrigger class="bg-slate-950/60 border-slate-700 text-white"><SelectValue placeholder="Select" /></SelectTrigger>
                  <SelectContent class="bg-slate-900 border-slate-700 text-white">
                    <SelectItem value="all">All Time</SelectItem>
                    <SelectItem value="week">Last Week</SelectItem>
                    <SelectItem value="month">Last Month</SelectItem>
                    <SelectItem value="quarter">Last 3 Months</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              
              <div class="space-y-2">
                <Label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Client</Label>
                <Select v-model="clientFilter">
                  <SelectTrigger class="bg-slate-950/60 border-slate-700 text-white"><SelectValue placeholder="Select" /></SelectTrigger>
                  <SelectContent class="bg-slate-900 border-slate-700 text-white">
                    <SelectItem value="all">All Clients</SelectItem>
                    <SelectItem v-for="client in uniqueClients" :key="client" :value="client">{{ client }}</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
            
            <div class="flex justify-end gap-3">
              <Button variant="ghost" @click="clearFilters" class="text-slate-400 hover:text-slate-200">Clear Filters</Button>
              <Button @click="applyFilters" class="bg-gradient-to-r from-violet-600 to-pink-600 text-white border-0 hover:opacity-90">Apply Filters</Button>
            </div>
          </CardContent>
        </Card>
      </transition>
    </div>

    <div class="max-w-[1400px] mx-auto">
      <div v-if="isLoading" class="text-center py-16">
        <div class="w-12 h-12 border-4 border-violet-500 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-slate-400">Loading your color history...</p>
      </div>

      <div v-else-if="filteredColors.length === 0" class="text-center py-16">
        <div class="w-20 h-20 mx-auto mb-6 bg-slate-900/80 rounded-full flex items-center justify-center text-slate-600">
          <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <h3 class="text-xl font-semibold text-white mb-2">No color mixes found</h3>
        <p class="text-slate-400 mb-6">{{ searchQuery ? 'Try adjusting your search or filters' : 'Start creating colors in the Color Mixer' }}</p>
        <Button v-if="!searchQuery" class="bg-gradient-to-r from-violet-600 to-pink-600 text-white">
          <router-link to="/service-provider/color-mixer" class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            Go to Color Mixer
          </router-link>
        </Button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        <Card 
          v-for="color in paginatedColors"
          :key="color.id"
          class="bg-slate-900/80 border-slate-800 overflow-hidden group hover:-translate-y-1 transition-all duration-300 hover:shadow-xl hover:shadow-black/20 hover:border-slate-700 relative"
        >
          <div class="h-1 w-full absolute top-0 left-0" :style="{ background: `linear-gradient(90deg, ${color.hex}, transparent)` }"></div>

          <CardContent class="p-6">
            <div class="flex justify-between items-start mb-6">
              <div class="flex items-center gap-3">
                <Avatar class="bg-gradient-to-br from-blue-500 to-violet-500 text-white font-semibold">
                  <AvatarFallback class="bg-transparent">{{ color.client ? color.client.charAt(0).toUpperCase() : 'P' }}</AvatarFallback>
                </Avatar>
                <div>
                  <h3 class="font-semibold text-white leading-tight">{{ color.name }}</h3>
                </div>
              </div>
              
              <DropdownMenu>
                <DropdownMenuTrigger as-child>
                   <Button variant="ghost" size="icon" class="h-8 w-8 text-slate-500 hover:text-white hover:bg-slate-800">
                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                   </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="bg-slate-900 border-slate-700 text-slate-200" align="end">
                  <DropdownMenuItem @click="editColor(color)" class="cursor-pointer hover:bg-slate-800">Edit Details</DropdownMenuItem>
                  <DropdownMenuItem @click="duplicateColor(color)" class="cursor-pointer hover:bg-slate-800">Duplicate</DropdownMenuItem>
                  <DropdownMenuItem @click="shareColor(color)" class="cursor-pointer hover:bg-slate-800">Share</DropdownMenuItem>
                  <DropdownMenuSeparator class="bg-slate-800" />
                  <DropdownMenuItem @click="deleteColor(color)" class="cursor-pointer text-red-400 hover:text-red-300 hover:bg-slate-800">Delete</DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>

            <div class="mb-6">
              <div class="h-[120px] rounded-xl mb-4 border border-slate-700 transition-transform group-hover:scale-[1.02]" :style="{ backgroundColor: color.hex }"></div>
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                  <span class="text-xs font-medium text-slate-400">HEX</span>
                  <div class="flex items-center justify-between p-2 bg-slate-950/60 border border-slate-800 rounded-lg">
                    <span class="text-xs font-mono text-slate-200">{{ color.hex }}</span>
                    <button @click="copyToClipboard(color.hex)" class="text-slate-500 hover:text-slate-300 transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </button>
                  </div>
                </div>
                <div class="space-y-1">
                  <span class="text-xs font-medium text-slate-400">RGB</span>
                  <div class="flex items-center justify-between p-2 bg-slate-950/60 border border-slate-800 rounded-lg">
                    <span class="text-xs font-mono text-slate-200 truncate">{{ color.rgb }}</span>
                    <button @click="copyToClipboard(color.rgb)" class="text-slate-500 hover:text-slate-300 transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex gap-4 mb-6 pt-4 border-t border-slate-800">
              <div class="flex items-center gap-2 text-xs text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <span>{{ formatDate(color.created) }}</span>
              </div>
              <div class="flex items-center gap-2 text-xs text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <span>{{ color.timeAgo }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
              <Button @click="reapplyColor(color)" class="bg-gradient-to-r from-violet-600 to-pink-600 hover:opacity-90 text-white border-0 shadow-lg shadow-violet-500/20">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                Re-apply
              </Button>
              <Button @click="assignToJob(color)" variant="outline" class="bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white hover:border-slate-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Assign
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <div v-if="filteredColors.length > 0" class="flex flex-col items-center gap-4 pt-8 border-t border-slate-800">
        <div class="text-sm text-slate-400">
          Showing <span class="text-white font-medium">{{ startIndex + 1 }}-{{ endIndex }}</span> of <span class="text-white font-medium">{{ filteredColors.length }}</span> colors
        </div>
        <div class="flex items-center gap-2">
          <Button @click="prevPage" :disabled="currentPage === 1" variant="outline" size="icon" class="bg-slate-900 border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 disabled:opacity-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          </Button>
          
          <div class="flex gap-1">
            <Button 
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :variant="page === currentPage ? 'default' : 'outline'"
              :class="['w-9 h-9', page === currentPage ? 'bg-gradient-to-r from-violet-600 to-pink-600 border-0 text-white' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800']"
            >
              {{ page }}
            </Button>
          </div>
          
          <Button @click="nextPage" :disabled="currentPage === totalPages" variant="outline" size="icon" class="bg-slate-900 border-slate-800 text-slate-400 hover:text-white hover:bg-slate-800 disabled:opacity-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
          </Button>
        </div>
      </div>
    </div>

    <transition enter-active-class="transform ease-out duration-300 transition" enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2" enter-to-class="translate-y-0 opacity-100 sm:translate-x-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showNotification" class="fixed bottom-4 right-4 z-50 flex items-center gap-3 px-6 py-4 bg-slate-900/95 backdrop-blur-sm border border-slate-700 rounded-xl shadow-2xl text-white">
        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>{{ notificationMessage }}</span>
      </div>
    </transition>
  </div>
</template>

<script>
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuSeparator } from '@/components/ui/dropdown-menu'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import api from '@/utils/axios'

export default {
  name: 'ColorHistory',
  components: {
    Card, CardContent, Button, Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger, DropdownMenuSeparator,
    Badge, Label, Avatar, AvatarFallback
  },
  data() {
    return {
      searchQuery: '',
      showFilter: false,
      sortBy: 'newest',
      dateRange: 'all',
      clientFilter: 'all',
      currentPage: 1,
      itemsPerPage: 8,
      showNotification: false,
      notificationMessage: '',
      isLoading: false,
      colors: [] // Now starts empty, fetched dynamically from DB
    }
  },
  computed: {
    uniqueClients() {
      return [...new Set(this.colors.map(color => color.client))].sort()
    },
    activeFilterCount() {
      let count = 0
      if (this.sortBy !== 'newest') count++
      if (this.dateRange !== 'all') count++
      if (this.clientFilter !== 'all') count++
      return count
    },
    filteredColors() {
      let filtered = [...this.colors]
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(color => 
          color.client.toLowerCase().includes(query) ||
          color.name.toLowerCase().includes(query) ||
          color.hex.toLowerCase().includes(query)
        )
      }
      
      if (this.clientFilter !== 'all') {
        filtered = filtered.filter(color => color.client === this.clientFilter)
      }
      
      if (this.dateRange !== 'all') {
        const now = new Date()
        const daysAgo = this.dateRange === 'week' ? 7 : this.dateRange === 'month' ? 30 : 90
        filtered = filtered.filter(color => {
          const colorDate = new Date(color.created)
          const diffDays = Math.floor((now - colorDate) / (1000 * 60 * 60 * 24))
          return diffDays <= daysAgo
        })
      }
      
      switch (this.sortBy) {
        case 'oldest': filtered.sort((a, b) => new Date(a.created) - new Date(b.created)); break
        case 'client': filtered.sort((a, b) => a.client.localeCompare(b.client)); break
        default: filtered.sort((a, b) => new Date(b.created) - new Date(a.created))
      }
      
      return filtered
    },
    totalPages() {
      return Math.ceil(this.filteredColors.length / this.itemsPerPage) || 1
    },
    paginatedColors() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredColors.slice(start, end)
    },
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage
    },
    endIndex() {
      return Math.min(this.startIndex + this.itemsPerPage, this.filteredColors.length)
    },
    visiblePages() {
      const pages = []
      const maxVisible = 5
      let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2))
      let end = Math.min(this.totalPages, start + maxVisible - 1)
      if (end - start + 1 < maxVisible) start = Math.max(1, end - maxVisible + 1)
      for (let i = start; i <= end; i++) pages.push(i)
      return pages
    }
  },
  watch: {
    searchQuery() { this.currentPage = 1 },
    dateRange() { this.currentPage = 1 },
    clientFilter() { this.currentPage = 1 }
  },
  async mounted() {
    await this.fetchColors()
  },
  methods: {
    async fetchColors() {
      try {
        this.isLoading = true
        const response = await api.get('/service-provider/color-history')
        
        // Map backend format to component's expected format
        this.colors = response.data.data.map(c => ({
          id: c.id,
          client: c.client_name, 
          name: c.name,
          hex: c.hex_code,
          rgb: c.rgb_values,
          created: c.created_at, 
          timeAgo: c.time_ago
        }))
      } catch (error) {
        console.error("Failed to fetch colors:", error)
        this.showNotificationMessage("Failed to load color history")
      } finally {
        this.isLoading = false
      }
    },
    toggleFilter() { this.showFilter = !this.showFilter },
    applyFilters() { this.showFilter = false },
    clearFilters() {
      this.sortBy = 'newest'; this.dateRange = 'all'; this.clientFilter = 'all';
      this.searchQuery = ''; this.showFilter = false
    },
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
    },
    copyToClipboard(text) {
      navigator.clipboard.writeText(text)
      this.showNotificationMessage(`Copied ${text} to clipboard`)
    },
    reapplyColor(color) { this.showNotificationMessage(`Applying ${color.name} to mixer...`) },
    assignToJob(color) { this.showNotificationMessage(`Assigning ${color.name} to new job...`) },
    editColor(color) { this.showNotificationMessage(`Editing ${color.name}...`) },
    duplicateColor(color) { this.showNotificationMessage(`Duplicating ${color.name}...`) },
    shareColor(color) { this.showNotificationMessage(`Sharing ${color.name}...`) },
    async deleteColor(color) {
      if (confirm(`Are you sure you want to delete ${color.name}?`)) {
        try {
          // Utilizing the existing endpoint you created previously for delete
          await api.delete(`/service-provider/colors/${color.id}`)
          
          const index = this.colors.findIndex(c => c.id === color.id)
          if (index > -1) {
            this.colors.splice(index, 1)
            this.showNotificationMessage(`Deleted ${color.name}`)
          }
        } catch (error) {
          console.error("Failed to delete color:", error)
          this.showNotificationMessage(`Failed to delete ${color.name}`)
        }
      }
    },
    showNotificationMessage(message) {
      this.notificationMessage = message
      this.showNotification = true
      setTimeout(() => { this.showNotification = false }, 3000)
    },
    prevPage() { if (this.currentPage > 1) { this.currentPage--; this.scrollToTop() } },
    nextPage() { if (this.currentPage < this.totalPages) { this.currentPage++; this.scrollToTop() } },
    goToPage(page) { this.currentPage = page; this.scrollToTop() },
    scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }) }
  }
}
</script>