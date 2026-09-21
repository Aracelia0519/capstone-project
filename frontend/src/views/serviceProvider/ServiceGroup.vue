<template>
  <div class="min-h-screen text-slate-200 p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2 flex items-center gap-3">
            Service Providers Group
            <Badge v-if="pendingApprovals > 0" variant="outline" class="border-fuchsia-500/40 text-fuchsia-400 bg-fuchsia-500/10 text-xs">
              {{ pendingApprovals }} approval{{ pendingApprovals > 1 ? 's' : '' }} pending
            </Badge>
          </h1>
          <p class="text-gray-400 text-sm md:text-base">Work as a team, approve each other's services, and split job revenue fairly.</p>
        </div>
      </div>

      <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
        <Card class="bg-fuchsia-900/20 border-fuchsia-800/50">
          <CardContent class="p-3 md:p-4 flex items-center">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-fuchsia-500 to-pink-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
              <UsersRound class="w-5 h-5" />
            </div>
            <div>
              <p class="text-gray-400 text-xs md:text-sm">My Groups</p>
              <p class="text-xl md:text-2xl font-bold text-white">{{ groups.length }}</p>
            </div>
          </CardContent>
        </Card>
        <Card class="bg-amber-900/20 border-amber-800/50">
          <CardContent class="p-3 md:p-4 flex items-center">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
              <UserPlus class="w-5 h-5" />
            </div>
            <div>
              <p class="text-gray-400 text-xs md:text-sm">Invitations</p>
              <p class="text-xl md:text-2xl font-bold text-white">{{ receivedInvites.length }}</p>
            </div>
          </CardContent>
        </Card>
        <Card class="bg-blue-900/20 border-blue-800/50">
          <CardContent class="p-3 md:p-4 flex items-center">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
              <ClipboardCheck class="w-5 h-5" />
            </div>
            <div>
              <p class="text-gray-400 text-xs md:text-sm">Team Services</p>
              <p class="text-xl md:text-2xl font-bold text-white">{{ teamServiceCount }}</p>
            </div>
          </CardContent>
        </Card>
        <Card class="bg-emerald-900/20 border-emerald-800/50">
          <CardContent class="p-3 md:p-4 flex items-center">
            <div class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-400 flex items-center justify-center text-white shadow-lg mr-3 md:mr-4 shrink-0">
              <CheckCircle2 class="w-5 h-5" />
            </div>
            <div>
              <p class="text-gray-400 text-xs md:text-sm">Group Members</p>
              <p class="text-xl md:text-2xl font-bold text-white">{{ memberTotal }}</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- ═══════════════ RECEIVED INVITATIONS ═══════════════ -->
    <Card v-if="receivedInvites.length > 0" class="mb-6 bg-slate-900 border-amber-800/40">
      <CardHeader class="pb-3">
        <CardTitle class="text-white text-lg flex items-center gap-2">
          <UserPlus class="w-5 h-5 text-amber-400" /> Invitations to join
        </CardTitle>
        <CardDescription class="text-gray-400">Another provider has invited you to join their team.</CardDescription>
      </CardHeader>
      <CardContent class="space-y-3">
        <div v-for="inv in receivedInvites" :key="inv.id" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 bg-slate-800/40 border border-slate-700/60 rounded-xl">
          <div class="flex items-center gap-3 min-w-0">
            <Avatar class="h-10 w-10 bg-gradient-to-br from-amber-500 to-orange-400 shrink-0">
              <AvatarFallback class="bg-transparent text-white font-bold">{{ getInitials(inv.leader_name) }}</AvatarFallback>
            </Avatar>
            <div class="min-w-0">
              <p class="text-white font-semibold truncate">{{ inv.group_name }}</p>
              <p class="text-gray-400 text-xs truncate">Invited by <span class="text-amber-400/90">{{ inv.leader_name }}</span> · {{ formatDate(inv.invited_at) }}</p>
              <p v-if="inv.description" class="text-gray-500 text-xs truncate mt-0.5">{{ inv.description }}</p>
            </div>
          </div>
          <div class="flex gap-2 shrink-0">
            <Button variant="outline" class="bg-slate-800 border-slate-700 text-slate-300 hover:bg-slate-700" @click="respondInvite(inv, 'decline')">Decline</Button>
            <Button class="bg-gradient-to-r from-emerald-600 to-green-500 hover:opacity-90 text-white" @click="respondInvite(inv, 'accept')">
              <Check class="w-4 h-4 mr-1" /> Accept
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- ═══════════════ CREATE GROUP (when leading none) ═══════════════ -->
    <Card v-if="!leaderGroup" class="mb-6 bg-slate-900 border-fuchsia-700/40">
      <CardHeader class="pb-3">
        <CardTitle class="text-white text-lg flex items-center gap-2">
          <UsersRound class="w-5 h-5 text-fuchsia-400" /> Create a Service Provider Group
        </CardTitle>
        <CardDescription class="text-gray-400">
          You become the group leader. Invite OTHER verified service providers — group services only go live after every member approves them, and non-daily jobs share revenue by member-approved percentages.
        </CardDescription>
      </CardHeader>
      <CardContent>
        <div class="grid md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Group Name</Label>
            <Input v-model="createForm.group_name" placeholder="e.g. Manila Paint Squad" class="bg-slate-800 border-slate-700 text-white rounded-xl placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Description</Label>
            <Input v-model="createForm.description" placeholder="What does your team specialize in?" class="bg-slate-800 border-slate-700 text-white rounded-xl placeholder:text-gray-500" />
          </div>
        </div>
        <Button class="mt-4 bg-gradient-to-r from-fuchsia-600 to-pink-600 hover:opacity-90 text-white" :disabled="creating" @click="createGroup">
          <Loader2 v-if="creating" class="w-4 h-4 mr-2 animate-spin" />
          <UsersRound v-else class="w-4 h-4 mr-2" /> Create Group
        </Button>
      </CardContent>
    </Card>

    <!-- ═══════════════ MY GROUPS ═══════════════ -->
    <div v-if="groups.length === 0 && !loading" class="text-center py-10 text-gray-500">
      You are not part of any service provider group yet.
    </div>

    <div v-for="group in groups" :key="group.id" class="mb-8">
      <Card class="bg-slate-900 border-slate-700/60 overflow-hidden">
        <CardHeader class="pb-4 border-b border-slate-800">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <Avatar class="h-12 w-12 bg-gradient-to-br from-fuchsia-500 to-pink-500 shrink-0">
                <AvatarFallback class="bg-transparent text-white font-bold">{{ getInitials(group.group_name) }}</AvatarFallback>
              </Avatar>
              <div>
                <div class="flex items-center gap-2">
                  <CardTitle class="text-white text-xl">{{ group.group_name }}</CardTitle>
                  <Badge v-if="group.my_role === 'leader'" variant="outline" class="border-fuchsia-500/40 text-fuchsia-400 bg-fuchsia-500/10 text-[10px] uppercase">Leader</Badge>
                  <Badge v-else variant="outline" class="border-slate-600 text-slate-300 bg-slate-800 text-[10px] uppercase">Member</Badge>
                </div>
                <CardDescription class="text-gray-400 text-sm">Led by {{ group.leader_name }} · {{ group.member_count }} member{{ group.member_count > 1 ? 's' : '' }}</CardDescription>
              </div>
            </div>
            <div class="flex gap-2">
              <Button v-if="group.my_role !== 'leader'" variant="outline" class="bg-slate-800 border-slate-700 text-rose-400 hover:bg-rose-500/10" @click="leaveGroup(group)">
                Leave Group
              </Button>
            </div>
          </div>
          <p v-if="group.description" class="text-gray-400 text-sm mt-3">{{ group.description }}</p>
        </CardHeader>

        <CardContent class="pt-4 space-y-6">
          <!-- Members -->
          <div>
            <h3 class="text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2">Members</h3>
            <div class="flex flex-wrap gap-2">
              <div v-for="m in group.members" :key="m.id" class="flex items-center gap-2 px-3 py-1.5 bg-slate-800/60 border border-slate-700/60 rounded-full">
                <Avatar class="h-6 w-6 shrink-0 bg-gradient-to-br from-blue-500 to-purple-500">
                  <AvatarFallback class="bg-transparent text-white text-[10px] font-bold">{{ getInitials(m.name) }}</AvatarFallback>
                </Avatar>
                <span class="text-slate-300 text-xs font-medium">{{ m.name }}</span>
                <span v-if="m.role === 'leader'" class="text-[9px] uppercase font-black text-fuchsia-400">Leader</span>
                <Button
                  v-if="group.my_role === 'leader' && m.id !== group.leader_id"
                  variant="ghost"
                  class="h-5 w-5 p-0 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-full"
                  @click="removeMember(group, m.id)"
                >
                  <X class="w-3.5 h-3.5" />
                </Button>
              </div>
            </div>
          </div>

          <!-- Invite form (leader only) -->
          <div v-if="group.my_role === 'leader'" class="p-4 bg-slate-800/30 border border-slate-700/60 rounded-xl">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Invite a Verified Provider</Label>
            <div class="flex flex-col sm:flex-row gap-2 mt-2">
              <Input
                v-model="inviteEmails[group.id]"
                placeholder="provider@email.com"
                class="bg-slate-800 border-slate-700 text-white rounded-xl placeholder:text-gray-500"
                @keyup.enter="invite(group)"
              />
              <Button class="bg-gradient-to-r from-blue-600 to-cyan-500 hover:opacity-90 text-white shrink-0" :disabled="invitingGroupId === group.id" @click="invite(group)">
                <Loader2 v-if="invitingGroupId === group.id" class="w-4 h-4 mr-2 animate-spin" />
                <Send v-else class="w-4 h-4 mr-2" /> Send Invite
              </Button>
            </div>
            <div v-if="sentInvitesFor(group).length > 0" class="mt-4 space-y-2">
              <p class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Pending Invitations Sent</p>
              <div v-for="inv in sentInvitesFor(group)" :key="inv.invite_id" class="flex items-center justify-between px-3 py-2 bg-slate-900/60 border border-slate-800 rounded-lg">
                <span class="text-slate-300 text-sm">{{ inv.invitee_name }}</span>
                <Badge variant="outline" class="border-amber-500/30 text-amber-400 bg-amber-500/10 text-[10px]">Awaiting response</Badge>
              </div>
            </div>
          </div>

          <!-- ─────────── GROUP SERVICES ─────────── -->
          <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
              <h3 class="text-[11px] font-black text-slate-500 uppercase tracking-widest">Group Services</h3>
              <Button class="bg-gradient-to-r from-purple-600 to-indigo-500 hover:opacity-90 text-white text-xs h-9" @click="openServiceModal(group)">
                <Plus class="w-4 h-4 mr-1" /> Create Team Service
              </Button>
            </div>

            <div v-if="!group.servicesLoaded" class="text-center py-6 text-gray-500 text-sm">
              <Loader2 class="w-5 h-5 animate-spin mx-auto mb-2" /> Loading team services...
            </div>

            <div v-else-if="group.services.length === 0" class="text-center py-6 text-gray-500 text-sm border border-dashed border-slate-700/60 rounded-xl">
              No team services yet. Anyone in the group can create one — it is published only after every member approves it.
            </div>

            <div v-else class="space-y-3">
              <div v-for="svc in group.services" :key="svc.id" class="flex flex-col lg:flex-row lg:items-center gap-3 p-4 bg-slate-800/40 border border-slate-700/60 rounded-xl">
                <div class="flex items-center gap-3 min-w-0 lg:w-80">
                  <div class="w-14 h-14 rounded-lg overflow-hidden bg-slate-700/40 border border-slate-700 shrink-0 flex items-center justify-center">
                    <img v-if="svc.image_paths_formatted?.length" :src="svc.image_paths_formatted[0]" class="w-full h-full object-cover" alt="" />
                    <Paintbrush v-else class="w-6 h-6 text-slate-500" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-white font-semibold text-sm truncate">{{ svc.title }}</p>
                    <p class="text-gray-400 text-xs truncate">by {{ svc.created_by_name }} · {{ svc.category }}</p>
                    <p class="text-emerald-400 font-bold text-sm">₱{{ parseFloat(svc.price).toLocaleString() }} <span class="text-gray-500 text-[10px] font-normal">{{ svc.price_type }}</span></p>
                  </div>
                </div>

                <!-- Approval status -->
                <div class="flex-1 flex flex-wrap items-center gap-1.5">
                  <template v-if="svc.is_published">
                    <Badge variant="outline" class="border-emerald-500/40 text-emerald-400 bg-emerald-500/10">Published</Badge>
                  </template>
                  <template v-else>
                    <Badge v-if="svc.approval_state.status === 'pending'" variant="outline" class="border-amber-500/40 text-amber-400 bg-amber-500/10">Pending approval</Badge>
                    <Badge v-else-if="svc.approval_state.status === 'rejected'" variant="outline" class="border-rose-500/40 text-rose-400 bg-rose-500/10">Rejected</Badge>
                    <Badge v-else variant="outline" class="border-emerald-500/40 text-emerald-400 bg-emerald-500/10">Approved</Badge>
                  </template>
                  <span v-for="a in svc.approval_state.approvals" :key="a.member_id" class="text-[10px] px-1.5 py-0.5 rounded-md border"
                    :class="a.status === 'approved' ? 'border-emerald-500/30 text-emerald-400 bg-emerald-500/10' : a.status === 'rejected' ? 'border-rose-500/30 text-rose-400 bg-rose-500/10' : 'border-slate-600 text-slate-400 bg-slate-800'">
                    {{ shortName(a.member_name) }}: {{ a.status }}
                  </span>
                </div>

                <!-- Approve / Reject (other members, not the creator) -->
                <div v-if="!svc.is_published && svc.myApprovalStatus(svc) === 'pending' && !svc.isMine(svc)" class="flex gap-2 shrink-0">
                  <Button class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs h-9" @click="approveService(group, svc)">
                    <Check class="w-4 h-4 mr-1" /> Approve
                  </Button>
                  <Button variant="outline" class="bg-slate-800 border-rose-700/50 text-rose-400 hover:bg-rose-500/10 text-xs h-9" @click="openReject(group, svc)">
                    <X class="w-4 h-4 mr-1" /> Reject
                  </Button>
                </div>
                <div v-if="svc.isMine(svc) && !svc.is_published" class="shrink-0">
                  <Button variant="outline" class="bg-slate-800 border-slate-700 text-slate-300 text-xs h-9" @click="openServiceModal(group, svc)">
                    Edit Draft
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- ═══════════════ CREATE / EDIT SERVICE MODAL ═══════════════ -->
    <Dialog :open="!!serviceModalGroup" @update:open="v => !v && closeServiceModal()">
      <DialogContent class="bg-slate-900 border-slate-700 text-slate-200 max-w-2xl sm:rounded-3xl max-h-[90vh] overflow-y-auto z-[10010]">
        <DialogHeader>
          <DialogTitle class="text-white text-xl">{{ serviceForm.editing ? 'Edit Team Service Draft' : 'Create a Team Service' }}</DialogTitle>
          <DialogDescription class="text-gray-400">
            The draft will be posted for approval. Once EVERY other group member approves it, it is published to the e-commerce services.
          </DialogDescription>
        </DialogHeader>

        <div class="grid md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Service Title</Label>
            <Input v-model="serviceForm.title" placeholder="e.g. Full Home Exterior Painting" class="bg-slate-800 border-slate-700 text-white rounded-xl placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Category</Label>
            <Input v-model="serviceForm.category" placeholder="e.g. House Painting" class="bg-slate-800 border-slate-700 text-white rounded-xl placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Price (₱)</Label>
            <Input v-model="serviceForm.price" type="number" min="0" placeholder="0.00" class="bg-slate-800 border-slate-700 text-white rounded-xl placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Pricing Type</Label>
            <Select v-model="serviceForm.price_type">
              <SelectTrigger class="bg-slate-800 border-slate-700 text-white rounded-xl">
                <SelectValue placeholder="Type" />
              </SelectTrigger>
              <SelectContent class="bg-slate-800 border-slate-700 text-white rounded-xl z-[10020]">
                <SelectItem value="Base Rate">Base Rate</SelectItem>
                <SelectItem value="Starting Price">Starting Price</SelectItem>
                <SelectItem value="Per Sqm">Per Sqm</SelectItem>
                <SelectItem value="Daily">Daily</SelectItem>
                <SelectItem value="Fixed Price">Fixed Price</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Est. Duration</Label>
            <Input v-model="serviceForm.duration" placeholder="e.g. 3-5 Days" class="bg-slate-800 border-slate-700 text-white rounded-xl placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Photos</Label>
            <input ref="serviceFileInput" type="file" multiple accept="image/*" class="hidden" @change="onServiceFiles" />
            <Button variant="outline" class="w-full bg-slate-800 border-slate-700 text-slate-300" @click="serviceFileInput?.click()">
              <ImagePlus class="w-4 h-4 mr-2" /> Choose Images
            </Button>
            <p v-if="serviceForm.fileNames.length" class="text-xs text-gray-400">{{ serviceForm.fileNames.join(', ') }}</p>
          </div>
          <div class="space-y-2 md:col-span-2">
            <Label class="text-gray-300 font-bold uppercase tracking-wider text-xs">Description</Label>
            <Textarea v-model="serviceForm.description" rows="4" placeholder="Describe the service, inclusions, and what clients should expect..." class="bg-slate-800 border-slate-700 text-white rounded-xl resize-none placeholder:text-gray-500" />
          </div>
        </div>

        <div v-if="serviceForm.editing && serviceForm.rejectionNotice" class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-sm text-rose-300">
          This draft was rejected — editing resets the approvals so the group can review it again.
        </div>

        <DialogFooter>
          <Button variant="outline" class="bg-slate-800 border-slate-700 text-slate-300" @click="closeServiceModal">Cancel</Button>
          <Button class="bg-gradient-to-r from-purple-600 to-indigo-500 hover:opacity-90 text-white" :disabled="submittingService" @click="submitService">
            <Loader2 v-if="submittingService" class="w-4 h-4 mr-2 animate-spin" />
            {{ serviceForm.editing ? 'Update Draft' : 'Submit for Approval' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- ═══════════════ REJECT SERVICE MODAL ═══════════════ -->
    <Dialog :open="!!rejectTarget" @update:open="v => !v && (rejectTarget = null)">
      <DialogContent class="bg-slate-900 border-slate-700 text-slate-200 max-w-md sm:rounded-3xl z-[10010]">
        <DialogHeader>
          <DialogTitle class="text-white text-lg flex items-center gap-2">
            <X class="w-5 h-5 text-rose-400" /> Reject Team Service
          </DialogTitle>
          <DialogDescription class="text-gray-400">Tell {{ rejectTarget?.svc?.created_by_name }} why this draft should not be published.</DialogDescription>
        </DialogHeader>
        <Textarea v-model="rejectReason" rows="4" placeholder="Required — explain the reason for rejection..." class="bg-slate-800 border-slate-700 text-white rounded-xl resize-none placeholder:text-gray-500" />
        <DialogFooter>
          <Button variant="outline" class="bg-slate-800 border-slate-700 text-slate-300" @click="rejectTarget = null">Cancel</Button>
          <Button class="bg-rose-600 hover:bg-rose-500 text-white" :disabled="!rejectReason.trim()" @click="submitReject">
            Confirm Rejection
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Select, SelectTrigger, SelectValue, SelectContent, SelectItem } from '@/components/ui/select'
import {
  UsersRound, UserPlus, CheckCircle2, ClipboardCheck, Check, X, Loader2, Send, Plus,
  Paintbrush, ImagePlus
} from 'lucide-vue-next'

const loading = ref(true)
const creating = ref(false)
const groups = ref([])
const receivedInvites = ref([])
const sentInvites = ref([])
const inviteEmails = reactive({})
const invitingGroupId = ref(null)

const createForm = reactive({ group_name: '', description: '' })
const serviceModalGroup = ref(null)
const serviceFileInput = ref(null)
const submittingService = ref(false)
const serviceForm = reactive({ editing: false, groupId: null, serviceId: null, title: '', category: '', price: '', price_type: 'Base Rate', duration: '', description: '', fileNames: [], files: [], rejectionNotice: false })
const rejectTarget = ref(null)
const rejectReason = ref('')

const leaderGroup = computed(() => groups.value.find(g => g.my_role === 'leader') || null)
const teamServiceCount = computed(() => groups.value.reduce((n, g) => n + (g.services?.length || 0), 0))
const memberTotal = computed(() => groups.value.reduce((n, g) => n + (g.member_count || 0), 0))
const pendingApprovals = computed(() => {
  let n = 0
  for (const g of groups.value) {
    for (const s of g.services || []) {
      if (!s.is_published && s.approval_state.status === 'pending' && !s.isMine(s)) n++
    }
  }
  return n
})

const getInitials = (name = '') => {
  const parts = String(name).trim().split(/\s+/).filter(Boolean)
  if (!parts.length) return '?'
  return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase()
}

const shortName = (name = '') => String(name).split(/\s+/).slice(0, 2).map(p => p[0]).join('').toUpperCase()

const formatDate = (iso) => {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const sentInvitesFor = (group) => sentInvites.value.filter(i => i.id === group.id)

async function loadOverview() {
  loading.value = true
  try {
    const res = await api.get('/service-provider/groups')
    if (res.data.success) {
      groups.value = (res.data.data.my_groups || []).map(g => ({ ...g, services: [], servicesLoaded: false }))
      receivedInvites.value = res.data.data.received_invites || []
      sentInvites.value = res.data.data.sent_invites || []
      // eager-load services for every group
      for (const g of groups.value) {
        await loadGroupServices(g)
      }
    }
  } catch (e) {
    toast.error('Failed to load groups', { description: e.response?.data?.message || 'Please try again.' })
  } finally {
    loading.value = false
  }
}

async function loadGroupServices(group) {
  group.servicesLoaded = false
  try {
    const res = await api.get(`/service-provider/groups/${group.id}/services`)
    group.services = (res.data.data || []).map(s => ({
      ...s,
      isMine: (svc) => svc.provider_id === group.my_id,
      myApprovalStatus: (svc) => svc.approval_state?.my_approval?.status || 'none'
    }))
    groups.value = groups.value.map(g => ({ ...g, ...(g.id === group.id ? { services: group.services, servicesLoaded: true } : {}) }))
  } catch (e) {
    toast.error('Failed to load team services', { description: e.response?.data?.message })
    group.services = []
    group.servicesLoaded = true
  }
}

async function createGroup() {
  if (!createForm.group_name.trim()) {
    toast.error('Please enter a group name.')
    return
  }
  creating.value = true
  try {
    const res = await api.post('/service-provider/groups', {
      group_name: createForm.group_name,
      description: createForm.description
    })
    if (res.data.success) {
      toast.success('Group created', { description: 'Invite other verified providers to join your team.' })
      createForm.group_name = ''
      createForm.description = ''
      await loadOverview()
    }
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to create group.')
  } finally {
    creating.value = false
  }
}

async function invite(group) {
  const email = (inviteEmails[group.id] || '').trim()
  if (!email) { toast.error('Enter the provider email.'); return }
  invitingGroupId.value = group.id
  try {
    const res = await api.post(`/service-provider/groups/${group.id}/invite`, { email })
    toast.success('Invitation sent', { description: res.data.message })
    inviteEmails[group.id] = ''
    await loadOverview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to send invitation.')
  } finally {
    invitingGroupId.value = null
  }
}

async function respondInvite(inv, action) {
  try {
    const res = await api.post(`/service-provider/groups/${inv.group_id}/invites/${inv.id}/respond`, { action })
    toast.success(action === 'accept' ? 'You joined the group!' : 'Invitation declined')
    await loadOverview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to respond to invitation.')
  }
}

async function removeMember(group, memberId) {
  try {
    const res = await api.post(`/service-provider/groups/${group.id}/members/${memberId}/remove`)
    toast.success('Member removed', { description: res.data.message })
    await loadOverview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to remove member.')
  }
}

async function leaveGroup(group) {
  try {
    const res = await api.post(`/service-provider/groups/${group.id}/leave`)
    toast.success('Left group', { description: res.data.message })
    await loadOverview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to leave the group.')
  }
}

function openServiceModal(group, svc = null) {
  serviceModalGroup.value = group
  serviceForm.editing = !!svc
  serviceForm.groupId = group.id
  serviceForm.serviceId = svc?.id || null
  serviceForm.title = svc?.title || ''
  serviceForm.category = svc?.category || ''
  serviceForm.price = svc?.price || ''
  serviceForm.price_type = svc?.price_type || 'Base Rate'
  serviceForm.duration = svc?.duration || ''
  serviceForm.description = svc?.description || ''
  serviceForm.fileNames = []
  serviceForm.files = []
  serviceForm.rejectionNotice = !!svc && svc.approval_state?.status === 'rejected'
  if (serviceFileInput.value) serviceFileInput.value.value = ''
}

function closeServiceModal() {
  serviceModalGroup.value = null
}

function onServiceFiles(event) {
  const files = Array.from(event.target.files || [])
  serviceForm.files = files
  serviceForm.fileNames = files.map(f => f.name)
}

async function submitService() {
  if (!serviceForm.title || !serviceForm.category || !serviceForm.price || !serviceForm.duration || !serviceForm.description) {
    toast.error('Please complete all required fields.')
    return
  }
  submittingService.value = true
  const fd = new FormData()
  fd.append('title', serviceForm.title)
  fd.append('category', serviceForm.category)
  fd.append('price', serviceForm.price)
  fd.append('price_type', serviceForm.price_type)
  fd.append('duration', serviceForm.duration)
  fd.append('description', serviceForm.description)
  serviceForm.files.forEach((f, i) => fd.append(`images[${i}]`, f))

  try {
    const url = serviceForm.editing
      ? `/service-provider/groups/${serviceForm.groupId}/services/${serviceForm.serviceId}`
      : `/service-provider/groups/${serviceForm.groupId}/services`
    const res = await api.post(url, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
    toast.success(res.data.message)
    closeServiceModal()
    await loadOverview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to save team service.')
  } finally {
    submittingService.value = false
  }
}

async function approveService(group, svc) {
  try {
    const res = await api.post(`/service-provider/groups/${group.id}/services/${svc.id}/approve`)
    toast.success(res.data.message)
    await loadOverview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to approve service.')
  }
}

function openReject(group, svc) {
  rejectTarget.value = { group, svc }
  rejectReason.value = ''
}

async function submitReject() {
  if (!rejectReason.value.trim()) return
  const { group, svc } = rejectTarget.value
  try {
    const res = await api.post(`/service-provider/groups/${group.id}/services/${svc.id}/reject`, {
      rejection_reason: rejectReason.value
    })
    toast.success('Service rejected', { description: res.data.message })
    rejectTarget.value = null
    await loadOverview()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Failed to reject service.')
  }
}

onMounted(() => loadOverview())
</script>