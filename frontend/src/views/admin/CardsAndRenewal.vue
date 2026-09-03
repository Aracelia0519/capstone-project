<template>
  <div class="min-h-screen p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900">PWD Applications & Renewals</h1>
        <p class="text-slate-500 mt-1">Review and manage client PWD discount applications.</p>
      </div>
      <Button variant="outline" @click="fetchApplications" class="gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        Refresh Data
      </Button>
    </div>

    <!-- Data Table -->
    <Card class="border-slate-200 shadow-sm overflow-hidden flex flex-col">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-slate-50 border-b border-slate-100">
            <TableRow>
              <TableHead class="font-semibold text-slate-600">Applicant Name</TableHead>
              <TableHead class="font-semibold text-slate-600">PWD ID Number</TableHead>
              <TableHead class="font-semibold text-slate-600">Disability Type</TableHead>
              <TableHead class="font-semibold text-slate-600">Date Submitted</TableHead>
              <TableHead class="font-semibold text-slate-600">Status</TableHead>
              <TableHead class="font-semibold text-slate-600 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="6" class="h-32 text-center text-slate-500">
                <div class="flex justify-center items-center gap-2">
                  <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                  Loading applications...
                </div>
              </TableCell>
            </TableRow>
            
            <TableRow v-else-if="applications.length === 0">
              <TableCell colspan="6" class="h-32 text-center text-slate-500">
                No PWD applications found.
              </TableCell>
            </TableRow>

            <TableRow v-else v-for="app in applications" :key="app.id" class="hover:bg-slate-50 transition-colors">
              <TableCell class="font-medium text-slate-900">{{ app.full_name }}</TableCell>
              <TableCell class="font-mono text-slate-600">{{ app.id_number }}</TableCell>
              <TableCell class="text-slate-600">{{ app.disability_type }}</TableCell>
              <TableCell class="text-slate-600">{{ formatDate(app.created_at) }}</TableCell>
              <TableCell>
                <Badge :class="getStatusBadgeClass(app.status)" class="capitalize">
                  {{ app.status }}
                </Badge>
              </TableCell>
              <TableCell class="text-right">
                <Button variant="ghost" size="sm" @click="openModal(app)" class="text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50">
                  Review Details
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination Controls -->
      <div v-if="!loading && pagination.last_page > 1" class="flex items-center justify-between p-4 border-t border-slate-100 bg-slate-50/50">
        <div class="text-sm text-slate-500">
          Showing <span class="font-medium text-slate-900">{{ pagination.from || 0 }}</span> to <span class="font-medium text-slate-900">{{ pagination.to || 0 }}</span> of <span class="font-medium text-slate-900">{{ pagination.total }}</span> entries
        </div>
        <div class="flex gap-1">
          <Button 
            variant="outline" size="sm" 
            @click="goToPage(currentPage - 1)" 
            :disabled="currentPage === 1"
            class="h-8 w-8 p-0 flex items-center justify-center"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
          </Button>
          
          <Button 
            v-for="page in getPaginationRange()" 
            :key="page" 
            :variant="currentPage === page ? 'default' : 'outline'"
            size="sm"
            @click="goToPage(page)"
            class="h-8 w-8 p-0"
            :class="currentPage === page ? 'bg-indigo-600 hover:bg-indigo-700 text-white border-transparent' : ''"
          >
            {{ page }}
          </Button>
          
          <Button 
            variant="outline" size="sm" 
            @click="goToPage(currentPage + 1)" 
            :disabled="currentPage === pagination.last_page"
            class="h-8 w-8 p-0 flex items-center justify-center"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
          </Button>
        </div>
      </div>
    </Card>

    <!-- Review Modal -->
    <Dialog :open="showModal" @update:open="showModal = $event">
      <div v-if="showModal" class="fixed inset-0 z-40 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
          
          <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div>
              <h2 class="text-xl font-bold text-slate-900">Application Review</h2>
              <p class="text-sm text-slate-500 mt-1">Reviewing PWD Details for {{ selectedApp?.full_name }}</p>
            </div>
            <Badge :class="getStatusBadgeClass(selectedApp?.status)" class="uppercase px-3 py-1">
              {{ selectedApp?.status }}
            </Badge>
          </div>

          <div class="p-6 overflow-y-auto flex-1 bg-white">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              
              <!-- Personal Details Column -->
              <div class="space-y-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b pb-2">Information</h3>
                
                <div class="grid grid-cols-2 gap-4">
                  <div class="space-y-1">
                    <Label class="text-slate-500 text-xs">Full Name</Label>
                    <p class="font-semibold text-slate-900">{{ selectedApp?.full_name }}</p>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-slate-500 text-xs">Date of Birth</Label>
                    <p class="font-semibold text-slate-900">{{ selectedApp?.dob }}</p>
                  </div>
                  <div class="space-y-1 md:col-span-2">
                    <Label class="text-slate-500 text-xs">PWD ID Number</Label>
                    <p class="font-mono font-semibold text-indigo-600">{{ selectedApp?.id_number }}</p>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-slate-500 text-xs">Disability Type</Label>
                    <p class="font-semibold text-slate-900">{{ selectedApp?.disability_type }}</p>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-slate-500 text-xs">Issuing LGU / PDAO</Label>
                    <p class="font-semibold text-slate-900">{{ selectedApp?.issuing_lgu }}</p>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-slate-500 text-xs">Expiration Date</Label>
                    <p class="font-semibold text-slate-900" :class="isExpired(selectedApp?.expiration_date) ? 'text-red-600' : ''">
                      {{ selectedApp?.expiration_date }}
                      <span v-if="isExpired(selectedApp?.expiration_date)" class="text-xs ml-1">(Expired)</span>
                    </p>
                  </div>
                </div>

                <div v-if="selectedApp?.rejection_reason" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                  <Label class="text-red-700 text-xs font-bold uppercase tracking-wider mb-1 block">Rejection Reason</Label>
                  <p class="text-sm text-red-600">{{ selectedApp.rejection_reason }}</p>
                </div>
              </div>

              <!-- ID Photos Column -->
              <div class="space-y-6">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider border-b pb-2">Attached Documents</h3>
                
                <div class="space-y-4">
                  <div>
                    <Label class="text-slate-500 text-xs mb-1 block">Front of ID</Label>
                    <a :href="selectedApp?.photo_front_url" target="_blank" class="block border border-slate-200 rounded-lg overflow-hidden hover:border-indigo-400 transition-colors">
                      <img :src="selectedApp?.photo_front_url" class="w-full h-48 object-cover bg-slate-100" alt="Front ID" />
                    </a>
                  </div>
                  <div>
                    <Label class="text-slate-500 text-xs mb-1 block">Back of ID</Label>
                    <a :href="selectedApp?.photo_back_url" target="_blank" class="block border border-slate-200 rounded-lg overflow-hidden hover:border-indigo-400 transition-colors">
                      <img :src="selectedApp?.photo_back_url" class="w-full h-48 object-cover bg-slate-100" alt="Back ID" />
                    </a>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Actions Footer -->
          <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-between items-center shrink-0">
            <Button variant="outline" @click="showModal = false">Close Window</Button>
            
            <div v-if="selectedApp?.status === 'pending'" class="flex gap-3">
              <Button 
                variant="destructive" 
                @click="openActionDialog(selectedApp.id, 'reject')"
                class="min-w-[100px]"
              >
                Reject
              </Button>
              <Button 
                @click="openActionDialog(selectedApp.id, 'verify')"
                class="bg-emerald-600 hover:bg-emerald-700 text-white min-w-[100px]"
              >
                Verify & Approve
              </Button>
            </div>
          </div>

        </div>
      </div>
    </Dialog>

    <!-- Shadcn Alert Dialog for Actions -->
    <AlertDialog :open="alertDialog.open" @update:open="alertDialog.open = $event">
      <AlertDialogContent class="z-[60]">
        <AlertDialogHeader>
          <AlertDialogTitle>{{ alertDialog.title }}</AlertDialogTitle>
          <AlertDialogDescription>
            <p>{{ alertDialog.description }}</p>

            <!-- Rejection Input Area -->
            <div v-if="alertDialog.actionType === 'reject'" class="mt-4 text-left">
              <Label class="text-slate-700 font-medium block mb-2">Reason for Rejection <span class="text-red-500">*</span></Label>
              <textarea 
                v-model="rejectReason" 
                rows="3" 
                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50" 
                placeholder="Enter rejection reason to notify the client..."
              ></textarea>
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        
        <AlertDialogFooter>
          <AlertDialogCancel @click="alertDialog.open = false" :disabled="isProcessing">Cancel</AlertDialogCancel>
          <AlertDialogAction 
            @click.prevent="confirmAction" 
            :disabled="isProcessing || (alertDialog.actionType === 'reject' && !rejectReason.trim())" 
            :class="alertDialog.actionType === 'reject' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
          >
            <svg v-if="isProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            {{ isProcessing ? 'Processing...' : 'Confirm' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script>
import api from '@/utils/axios';
import echo from '@/utils/websocket';
import { toast } from 'vue-sonner';

// Shadcn UI Imports
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Table, TableHeader, TableBody, TableHead, TableRow, TableCell } from '@/components/ui/table';
import { Dialog } from '@/components/ui/dialog';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';

export default {
  name: 'CardsAndRenewal',
  components: {
    Card, Button, Label, Badge, Table, TableHeader, TableBody, TableHead, TableRow, TableCell, Dialog,
    AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle
  },
  data() {
    return {
      applications: [],
      loading: false,
      showModal: false,
      selectedApp: null,
      isProcessing: false,
      rejectReason: '',
      
      // Pagination State
      currentPage: 1,
      itemsPerPage: 10,
      pagination: {
        last_page: 1,
        total: 0,
        from: 0,
        to: 0
      },
      
      alertDialog: {
        open: false,
        title: '',
        description: '',
        actionType: '', // 'verify' or 'reject'
        appId: null
      }
    }
  },
  mounted() {
    this.fetchApplications();
    this.setupWebSocketListener();
  },
  beforeUnmount() {
    echo.leave('admin.pwd-applications');
  },
  methods: {
    async fetchApplications() {
      this.loading = true;
      try {
        const response = await api.get('/admin/pwd-applications', {
          params: { page: this.currentPage, per_page: this.itemsPerPage }
        });
        if (response.data.status === 'success') {
          this.applications = response.data.data;
          if (response.data.pagination) {
            this.pagination = response.data.pagination;
          }
        }
      } catch (error) {
        toast.error('Failed to load applications.');
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    
    setupWebSocketListener() {
      echo.private('admin.pwd-applications')
        .listen('.PwdApplicationSubmitted', (e) => {
          toast.info(`New PWD application submission/resubmission from ${e.fullName}`);
          this.fetchApplications();
        });
    },

    goToPage(page) {
      if (page >= 1 && page <= this.pagination.last_page) {
        this.currentPage = page;
        this.fetchApplications();
      }
    },

    getPaginationRange() {
      const range = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.pagination.last_page, start + 4);
      for (let i = start; i <= end; i++) range.push(i);
      return range;
    },
    
    openModal(app) {
      this.selectedApp = app;
      this.showModal = true;
    },

    openActionDialog(id, action) {
      this.alertDialog.appId = id;
      this.alertDialog.actionType = action;
      this.rejectReason = ''; 
      
      if (action === 'verify') {
        this.alertDialog.title = 'Verify Application';
        this.alertDialog.description = 'Are you sure you want to approve and verify this PWD application?';
      } else {
        this.alertDialog.title = 'Reject Application';
        this.alertDialog.description = 'Please provide a clear reason for rejecting this PWD application.';
      }
      
      this.alertDialog.open = true;
    },

    async confirmAction() {
      this.isProcessing = true;
      const { appId, actionType } = this.alertDialog;
      
      try {
        const payload = actionType === 'reject' ? { reason: this.rejectReason } : {};
        const response = await api.post(`/admin/pwd-applications/${appId}/${actionType}`, payload);
        
        if (response.data.status === 'success') {
          toast.success(response.data.message);
          this.alertDialog.open = false;
          this.showModal = false;
          this.fetchApplications();
        }
      } catch (error) {
        toast.error(error.response?.data?.message || `Failed to process application.`);
        console.error(error);
      } finally {
        this.isProcessing = false;
      }
    },

    getStatusBadgeClass(status) {
      if (status === 'verified') return 'bg-emerald-100 text-emerald-700 border-emerald-200 hover:bg-emerald-100';
      if (status === 'pending') return 'bg-amber-100 text-amber-700 border-amber-200 hover:bg-amber-100';
      if (status === 'rejected') return 'bg-red-100 text-red-700 border-red-200 hover:bg-red-100';
      return 'bg-slate-100 text-slate-700 border-slate-200';
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric',
        hour: '2-digit', minute: '2-digit'
      });
    },

    isExpired(dateString) {
      if (!dateString) return false;
      return new Date(dateString) < new Date();
    }
  }
}
</script>