<template>
  <div class="service-providers-container min-h-screen p-4 md:p-8">
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-teal-400 via-emerald-400 to-green-400 bg-clip-text text-transparent mb-2">
            Service Providers
          </h1>
          <p class="text-gray-400">Trusted painting professionals you've interacted with</p>
        </div>
        
        <div class="flex gap-4">
          <Card class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 border-slate-700/50 rounded-xl p-3 border-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-teal-500/20 to-emerald-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-400">Total Contacts</p>
                <p class="text-xl font-bold text-white">{{ stats.available }}</p>
              </div>
            </div>
          </Card>
          
          <Card class="bg-gradient-to-br from-slate-800/50 to-slate-900/50 border-slate-700/50 rounded-xl p-3 border-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500/20 to-cyan-500/20 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-400">Avg. Rating</p>
                <p class="text-xl font-bold text-white">{{ stats.avgRating }}</p>
              </div>
            </div>
          </Card>
        </div>
      </div>

      <div class="bg-gradient-to-r from-slate-800/40 to-slate-900/40 border border-slate-700/30 rounded-xl p-4 mb-6 backdrop-blur-sm">
        <div class="flex flex-col md:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <Input 
                v-model="searchQuery"
                type="text" 
                placeholder="Search service providers by name or specialty..."
                class="w-full pl-10 pr-4 py-6 bg-slate-800/50 border-slate-700/50 rounded-lg text-gray-300 placeholder:text-gray-500 focus-visible:ring-teal-500/50 transition-all"
              />
              <svg class="absolute left-3 top-4 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
          </div>
          
          <div class="flex flex-wrap gap-2">
            <Button 
              v-for="filter in filters" 
              :key="filter.id"
              @click="setActiveFilter(filter.id)"
              variant="outline"
              :class="[
                'filter-btn h-12 px-4 py-2 rounded-lg transition-all duration-300 border flex items-center gap-2',
                activeFilter === filter.id 
                  ? 'bg-gradient-to-r from-teal-500/20 to-emerald-500/20 text-teal-300 border-teal-500/50 hover:text-teal-200' 
                  : 'bg-slate-800/30 text-gray-400 border-slate-700/50 hover:bg-slate-700/50 hover:border-slate-600 hover:text-gray-300'
              ]"
            >
              <component :is="filter.icon" class="w-4 h-4" />
              {{ filter.label }}
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Skeletons Loader -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card v-for="i in 6" :key="i" class="provider-card bg-gradient-to-br from-slate-800/40 to-slate-900/40 border border-slate-700/30 rounded-2xl p-6">
        <div class="animate-pulse">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <Skeleton class="w-14 h-14 rounded-full bg-slate-700/50" />
              <div class="space-y-2">
                <Skeleton class="h-4 w-32 bg-slate-700/50 rounded" />
                <Skeleton class="h-3 w-24 bg-slate-700/50 rounded" />
              </div>
            </div>
            <Skeleton class="w-9 h-9 bg-slate-700/50 rounded-lg" />
          </div>
          <div class="space-y-3 mb-4">
            <Skeleton class="h-4 w-40 bg-slate-700/50 rounded" />
            <div class="flex gap-2">
              <Skeleton class="h-6 w-16 bg-slate-700/50 rounded-full" />
              <Skeleton class="h-6 w-16 bg-slate-700/50 rounded-full" />
            </div>
          </div>
          <div class="space-y-3 mb-6">
            <Skeleton class="h-10 bg-slate-700/50 rounded-lg" />
            <Skeleton class="h-10 bg-slate-700/50 rounded-lg" />
          </div>
          <div class="flex gap-2 pt-4">
            <Skeleton class="h-10 flex-1 bg-slate-700/50 rounded-lg" />
            <Skeleton class="h-10 flex-1 bg-slate-700/50 rounded-lg" />
          </div>
        </div>
      </Card>
    </div>

    <!-- Loaded Data -->
    <template v-else>
      <div v-if="filteredProviders.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card 
          v-for="provider in filteredProviders" 
          :key="provider.id"
          class="provider-card group bg-gradient-to-br from-slate-800/40 to-slate-900/40 border-slate-700/30 rounded-2xl p-6 transition-all duration-500 hover:border-teal-500/30 hover:shadow-2xl hover:shadow-teal-900/20 hover:scale-[1.02] backdrop-blur-sm overflow-hidden"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="relative">
                <Avatar class="w-14 h-14 border-0 shadow-lg">
                  <AvatarFallback class="bg-gradient-to-br from-teal-500 to-emerald-500 text-white font-bold text-xl">
                    {{ getInitials(provider.name) }}
                  </AvatarFallback>
                </Avatar>
                <div v-if="provider.online" class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-2 border-slate-900">
                  <div class="w-full h-full rounded-full bg-green-400 animate-pulse"></div>
                </div>
              </div>
              
              <div>
                <h3 class="text-lg font-bold text-white">{{ provider.name }}</h3>
                <p class="text-sm text-teal-400">{{ provider.title }}</p>
                <div class="flex items-center gap-1 mt-1">
                  <span class="text-xs text-gray-400">{{ provider.experience }} experience</span>
                </div>
              </div>
            </div>
            
            <div class="flex flex-col gap-2">
              <Button 
                variant="ghost" 
                size="icon"
                @click="toggleFavorite(provider.id)"
                class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-slate-600/50 hover:text-amber-400 transition-colors group/fav"
                title="Save Provider"
              >
                <svg 
                  :class="['w-5 h-5', provider.favorite ? 'text-amber-400 fill-amber-400' : 'text-gray-400 group-hover/fav:text-amber-400']" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
              </Button>
              
              <!-- REPORT BUTTON -->
              <Button 
                variant="ghost" 
                size="icon"
                @click="openReportModal(provider)"
                class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-red-900/50 hover:text-red-400 text-gray-400 transition-colors"
                title="Report Provider"
              >
                <Flag class="w-4 h-4" />
              </Button>
            </div>

          </div>

          <div class="space-y-3 mb-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="flex">
                  <svg 
                    v-for="n in 5" 
                    :key="n"
                    :class="['w-4 h-4', n <= provider.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-600']"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                </div>
                <span class="text-sm text-gray-300">{{ provider.rating > 0 ? provider.rating.toFixed(1) : 'New' }}</span>
                <span class="text-xs text-gray-500">({{ provider.reviews }})</span>
              </div>
              <Badge 
                variant="outline"
                class="border-0 px-2 py-1 rounded-full text-xs font-normal" 
                :class="provider.status === 'Available' ? 'bg-green-900/30 text-green-400' : 'bg-blue-900/30 text-blue-400'"
              >
                {{ provider.status }}
              </Badge>
            </div>

            <div class="flex flex-wrap gap-2">
              <Badge 
                v-for="specialty in provider.specialties.slice(0, 3)" 
                :key="specialty"
                variant="outline"
                class="px-2 py-1 text-xs rounded-full bg-slate-700/50 text-gray-300 border-slate-600/50 font-normal hover:bg-slate-600/50"
              >
                {{ specialty }}
              </Badge>
              <Badge 
                v-if="provider.specialties.length > 3"
                variant="outline"
                class="px-2 py-1 text-xs rounded-full bg-slate-700/50 text-gray-400 border-slate-600/50 font-normal"
              >
                +{{ provider.specialties.length - 3 }} more
              </Badge>
            </div>
          </div>

          <div class="space-y-3 mb-6">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-400">Phone</p>
                <p class="text-sm text-gray-300">{{ provider.phone }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-400">Email</p>
                <p class="text-sm text-gray-300 truncate">{{ provider.email }}</p>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-slate-700/50 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-400">Location</p>
                <p class="text-sm text-gray-300">{{ provider.location }}</p>
              </div>
            </div>
          </div>

          <div v-if="provider.recentProjects.length > 0" class="mb-4">
            <p class="text-xs text-gray-400 mb-2">Transacted Services</p>
            <div class="grid grid-cols-2 gap-2">
              <div 
                v-for="(project, index) in provider.recentProjects.slice(0, 4)" 
                :key="index"
                class="relative p-2 rounded-lg bg-gradient-to-br from-teal-400/10 to-emerald-400/10 cursor-pointer group/project border border-teal-500/20 hover:border-teal-400 transition-colors"
                @click="viewProject(project)"
              >
                <span class="text-xs font-medium text-teal-300 truncate block text-center">{{ project.name }}</span>
              </div>
            </div>
          </div>
          <div v-else class="mb-4 h-16 flex items-center justify-center border border-dashed border-slate-700/50 rounded-lg">
             <span class="text-xs text-gray-500 italic">No formal service transactions yet</span>
          </div>

          <div class="flex gap-2 pt-4 border-t border-slate-700/30">
            <Button 
              @click="contactProvider(provider)"
              class="flex-1 py-5 px-4 bg-gradient-to-r from-teal-500/20 to-emerald-500/20 text-teal-300 rounded-lg border border-teal-500/30 hover:border-teal-400/50 hover:bg-teal-500/30 hover:text-teal-200 transition-all duration-300 text-sm font-medium flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              Chat
            </Button>
            <Button 
              @click="viewProfile(provider)"
              variant="outline"
              class="flex-1 py-5 px-4 bg-slate-700/50 text-gray-300 rounded-lg border-slate-600 hover:border-slate-500 hover:bg-slate-600/50 hover:text-gray-100 transition-all duration-300 text-sm font-medium flex items-center justify-center gap-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
              </svg>
              Profile
            </Button>
          </div>
        </Card>
      </div>

      <div v-else class="text-center py-20">
        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-r from-slate-800 to-slate-900 border border-slate-700 flex items-center justify-center">
          <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-400 mb-3">No Service Providers Found</h3>
        <p class="text-gray-500 mb-6">You haven't interacted with any service providers yet, or none match your search/filter.</p>
        <Button 
          @click="resetFilters"
          class="px-6 py-6 bg-gradient-to-r from-teal-500 to-emerald-600 text-white rounded-lg hover:from-teal-600 hover:to-emerald-700 transition-all duration-300 font-medium inline-flex items-center gap-2 border-0"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Reset Filters
        </Button>
      </div>
    </template>

    <!-- REPORT MODAL DIALOG -->
    <Dialog :open="isReportModalOpen" @update:open="isReportModalOpen = $event">
      <DialogContent class="sm:max-w-md bg-slate-900 border-slate-800 text-white rounded-2xl shadow-2xl">
        <DialogHeader>
          <DialogTitle class="flex items-center gap-2 text-xl font-bold text-red-500">
            <Flag class="w-5 h-5" /> Report Provider
          </DialogTitle>
          <DialogDescription class="text-gray-400">
            Submit a formal report regarding <span class="text-white font-bold">{{ selectedProviderForReport?.name }}</span>. This will be reviewed by an administrator.
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="submitReport" class="space-y-4 mt-4">
          
          <div class="space-y-2">
            <Label class="text-gray-300">Reason for Report</Label>
            <Select v-model="reportForm.reason">
              <SelectTrigger class="w-full bg-slate-800 border-slate-700 text-white focus:ring-red-500">
                <SelectValue placeholder="Select a reason..." />
              </SelectTrigger>
              <SelectContent class="bg-slate-800 border-slate-700 text-white">
                <SelectItem value="Scam / Fraud">Scam / Fraud</SelectItem>
                <SelectItem value="Inappropriate Behavior">Inappropriate Behavior</SelectItem>
                <SelectItem value="Poor Service Quality">Poor Service Quality</SelectItem>
                <SelectItem value="Unresponsive">Unresponsive / Ghosting</SelectItem>
                <SelectItem value="Other">Other</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-gray-300">Detailed Description</Label>
            <Textarea 
              v-model="reportForm.description"
              placeholder="Please provide specific details about what happened..."
              class="bg-slate-800 border-slate-700 text-white focus-visible:ring-red-500 placeholder:text-gray-500 min-h-[100px] resize-none"
              required
            ></Textarea>
          </div>

          <div class="space-y-2">
            <Label class="text-gray-300">Date of Incident</Label>
            <Input 
              type="date" 
              v-model="reportForm.incident_date"
              class="bg-slate-800 border-slate-700 text-white focus-visible:ring-red-500"
              required
            />
          </div>

          <div class="space-y-2">
            <Label class="text-gray-300">Evidence <span class="text-gray-500 text-xs">(Optional, max 5MB)</span></Label>
            <Input 
              type="file" 
              accept=".jpg,.jpeg,.png,.pdf,.mp4"
              @change="handleReportEvidence"
              class="bg-slate-800 border-slate-700 text-gray-300 file:bg-slate-700 file:text-white file:border-0 file:mr-4 file:py-1 file:px-3 file:rounded cursor-pointer focus-visible:ring-red-500"
            />
          </div>

          <p class="text-xs text-gray-500 text-center mt-2">Note: You can submit up to 3 reports per day.</p>

          <div class="flex justify-end gap-3 pt-4 border-t border-slate-800 mt-6">
            <Button type="button" variant="outline" @click="isReportModalOpen = false" class="bg-slate-800 border-slate-700 text-white hover:bg-slate-700">Cancel</Button>
            <Button type="submit" :disabled="isSubmittingReport" class="bg-red-600 hover:bg-red-700 text-white">
              <span v-if="isSubmittingReport" class="flex items-center gap-2">
                <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div> Submitting...
              </span>
              <span v-else>Submit Report</span>
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

  </div>
</template>

<script>
// Shadcn Components
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Skeleton } from '@/components/ui/skeleton'
import { Textarea } from '@/components/ui/textarea'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { Flag } from 'lucide-vue-next'

export default {
  name: 'ServiceProviders',
  components: {
    Card, CardContent,
    Button,
    Input,
    Badge,
    Avatar, AvatarFallback, AvatarImage,
    Skeleton,
    Textarea, Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, Label,
    Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
    Flag,
    
    // Icon Components preserved
    AllProvidersIcon: {
      template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
      </svg>`
    },
    SavedIcon: {
      template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
      </svg>`
    }
  },
  data() {
    return {
      loading: true,
      searchQuery: '',
      activeFilter: 'all',
      showMap: false,
      stats: {
        available: 0,
        avgRating: 0.0
      },
      filters: [
        { id: 'all', label: 'All Providers', icon: 'AllProvidersIcon' },
        { id: 'saved', label: 'Saved Providers', icon: 'SavedIcon' }
      ],
      providers: [], 

      // Report State
      isReportModalOpen: false,
      selectedProviderForReport: null,
      isSubmittingReport: false,
      reportForm: {
        reason: '',
        description: '',
        incident_date: '',
      },
      reportEvidenceFile: null
    }
  },
  computed: {
    filteredProviders() {
      if (!this.providers) return []
      let filtered = this.providers
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(provider => 
          provider.name.toLowerCase().includes(query) ||
          provider.title.toLowerCase().includes(query) ||
          provider.specialties.some(s => s.toLowerCase().includes(query)) ||
          provider.location.toLowerCase().includes(query)
        )
      }
      
      // Apply active filter
      if (this.activeFilter === 'saved') {
        filtered = filtered.filter(p => p.favorite)
      }
      
      return filtered
    }
  },
  methods: {
    async fetchProviders() {
       this.loading = true
       try {
          const res = await api.get('/client/interacted-providers')
          if (res.data.success) {
             this.providers = res.data.data
             
             // Update top statistics based on accurate data
             this.stats.available = this.providers.length;
             const totalRating = this.providers.reduce((sum, p) => sum + p.rating, 0);
             this.stats.avgRating = this.providers.length > 0 ? (totalRating / this.providers.length).toFixed(1) : 0;
          }
       } catch (error) {
          console.error("Failed to load providers:", error)
          this.showToast('Error', 'Unable to load service providers')
       } finally {
          this.loading = false
       }
    },
    
    setActiveFilter(filterId) {
      this.activeFilter = filterId
    },
    
    resetFilters() {
      this.searchQuery = ''
      this.activeFilter = 'all'
      this.showToast('Filters Reset', 'Showing all service providers')
    },
    
    getInitials(name) {
      if (!name) return 'SP'
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
    },
    
    async toggleFavorite(providerId) {
      const provider = this.providers.find(p => p.id === providerId)
      if (!provider) return

      // Optimistic UI Update
      const originalState = provider.favorite
      provider.favorite = !provider.favorite

      try {
        const res = await api.post(`/client/providers/${providerId}/toggle-favorite`)
        
        if (res.data.success) {
          provider.favorite = res.data.is_favorite // ensure sync
          this.showToast(
            provider.favorite ? 'Saved to Favorites' : 'Removed from Favorites',
            `${provider.name} ${provider.favorite ? 'is now in your saved list' : 'was removed from your saved list'}`
          )
        } else {
           // Revert on error
           provider.favorite = originalState
           this.showToast('Error', 'Failed to save provider')
        }
      } catch (error) {
        // Revert on catch
        provider.favorite = originalState
        console.error(error)
        this.showToast('Error', 'Failed to communicate with server')
      }
    },

    // --- REPORTING LOGIC ---
    openReportModal(provider) {
      this.selectedProviderForReport = provider
      this.reportForm = {
        reason: '',
        description: '',
        incident_date: ''
      }
      this.reportEvidenceFile = null
      this.isReportModalOpen = true
    },

    handleReportEvidence(event) {
      if (event.target.files.length > 0) {
        this.reportEvidenceFile = event.target.files[0]
      }
    },

    async submitReport() {
      if (!this.selectedProviderForReport) return
      if (!this.reportForm.reason) {
        toast.error('Missing Reason', { description: 'Please select a reason for your report.' })
        return
      }

      this.isSubmittingReport = true
      
      const formData = new FormData()
      formData.append('reason', this.reportForm.reason)
      formData.append('description', this.reportForm.description)
      formData.append('incident_date', this.reportForm.incident_date)
      
      if (this.reportEvidenceFile) {
        formData.append('evidence', this.reportEvidenceFile)
      }

      try {
        const res = await api.post(`/client/providers/${this.selectedProviderForReport.id}/report`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (res.data.success) {
          toast.success('Report Submitted', { description: 'The admin team will review this incident.' })
          this.isReportModalOpen = false
        }
      } catch (error) {
        console.error(error)
        toast.error('Failed to submit report', { description: error.response?.data?.message || 'Check your inputs and try again.' })
      } finally {
        this.isSubmittingReport = false
      }
    },
    // ----------------------
    
    contactProvider(provider) {
      // Redirects to chat list. Client can select provider inside ClientChat page.
      this.$router.push('/Clients/ClientChat')
    },
    
    viewProfile(provider) {
      // Direct to dynamic Profile Setup
      this.$router.push(`/ECommerceClient/ProviderProfile/${provider.id}`)
    },
    
    viewProject(project) {
      if (project && project.id) {
         // Directs to specific transacted service details
         this.$router.push(`/ECommerceClient/ServiceDetails/${project.id}`)
      }
    },
    
    showToast(message, detail) {
      toast(message, {
        description: detail,
        className: 'bg-slate-800 border-slate-700 text-white',
      })
    }
  },
  mounted() {
    this.fetchProviders()
  }
}
</script>

<style scoped>
.service-providers-container {
  min-height: 100vh;
}

.provider-card {
  backdrop-filter: blur(10px);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.provider-card:hover {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
}

.filter-btn {
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.filter-btn:hover {
  transform: translateY(-1px);
}

/* Animations */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.3);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #0d9488, #059669);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #14b8a6, #10b981);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .service-providers-container {
    padding: 1rem;
  }
  
  .provider-card {
    padding: 1rem;
  }
  
  .filter-btn {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
  }
}

@media (max-width: 640px) {
  .service-providers-container {
    padding: 0.75rem;
  }
  
  h1 {
    font-size: 1.75rem;
  }
}
</style>