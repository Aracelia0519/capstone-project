<template>
  <div class="min-h-screen w-full  font-sans p-6 md:p-8">
    <div class="max-w-[1600px] mx-auto space-y-8">
      
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-4 border-b border-slate-200">
        <div class="space-y-1">
          <div class="inline-flex items-center rounded-full border border-indigo-500/30 bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-600 mb-3">
            <i class="fas fa-database mr-2"></i>
            Data Hub
          </div>
          <h2 class="text-3xl font-bold tracking-tight text-slate-900">
            System Records & Exports
          </h2>
          <p class="text-sm font-medium text-slate-500">Tabular breakdown of platform entities and filed reports.</p>
        </div>
        
        <div class="flex items-center gap-3">
          <!-- EXPORT ALL DATA BUTTON -->
          <button 
            @click="exportAllData" 
            class="inline-flex items-center justify-center rounded-md text-sm font-medium border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50 h-10 px-5"
          >
            <i class="fas fa-file-archive text-indigo-600 mr-2"></i>
            <span>Export All Data</span>
          </button>

          <button 
            @click="fetchReports(false)" 
            class="relative inline-flex items-center justify-center rounded-md text-sm font-medium focus-visible:outline-none disabled:pointer-events-none disabled:opacity-50 bg-slate-900 text-white shadow-sm hover:bg-slate-800 h-10 px-5"
          >
            <svg v-if="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <i v-else class="fas fa-sync-alt mr-2"></i>
            <span>Sync</span>
          </button>
        </div>
      </div>

      <!-- Quick Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-500">Total Registered Users</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ summary.total_users }}</h3>
          </div>
          <div class="h-10 w-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
            <i class="fas fa-users text-lg"></i>
          </div>
        </div>
        
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-500">Total Technical Reports</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ summary.total_tech_reports }}</h3>
          </div>
          <div class="h-10 w-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
            <i class="fas fa-bug text-lg"></i>
          </div>
        </div>
        
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-slate-500">Total User Reports</p>
            <h3 class="text-2xl font-bold text-slate-900 mt-1">{{ summary.total_user_reports }}</h3>
          </div>
          <div class="h-10 w-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
            <i class="fas fa-user-shield text-lg"></i>
          </div>
        </div>
      </div>

      <!-- High-Level Charts -->
      <div class="space-y-6">
        
        <!-- Row 1: Ecosystem Roles -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col h-[400px]">
          <h3 class="font-semibold text-slate-900 mb-4">Ecosystem Roles</h3>
          <div class="flex-grow relative flex items-center justify-center w-full">
            <div class="w-full max-w-lg h-full relative">
              <Doughnut v-if="chartDataRoles.datasets?.length" :data="chartDataRoles" :options="doughnutOptions" />
            </div>
          </div>
        </div>

        <!-- Row 2: Tech and User Reports -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col h-[350px]">
            <h3 class="font-semibold text-slate-900 mb-4">Tech Issue Distribution</h3>
            <div class="flex-grow relative">
              <Bar v-if="chartDataTech.datasets?.length" :data="chartDataTech" :options="barOptions" />
            </div>
          </div>
          
          <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm flex flex-col h-[350px]">
            <h3 class="font-semibold text-slate-900 mb-4">Infraction Volume</h3>
            <div class="flex-grow relative">
              <Bar v-if="chartDataUser.datasets?.length" :data="chartDataUser" :options="barOptionsHorizontal" />
            </div>
          </div>
        </div>
      </div>

      <!-- TABLE 1: Registered Users -->
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
          <div>
            <h3 class="text-lg font-bold text-slate-900"><i class="fas fa-users text-indigo-500 mr-2"></i>Registered Users</h3>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <input v-model="filters.users.search" type="text" placeholder="Search names/emails..." class="text-sm border-slate-200 rounded-lg px-3 py-2 w-64 focus:ring-indigo-500 focus:border-indigo-500">
            
            <div class="relative">
              <select v-model="filters.users.role" class="appearance-none text-sm border-slate-200 rounded-lg pl-3 pr-8 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                <option value="">All Roles</option>
                <option v-for="role in availableRoles" :key="role" :value="role">{{ role }}</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500 pointer-events-none"></i>
            </div>

            <div class="relative">
              <select v-model="filters.users.status" class="appearance-none text-sm border-slate-200 rounded-lg pl-3 pr-8 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                <option value="">All Statuses</option>
                <option value="Active">Active</option>
                <option value="Pending">Pending</option>
                <option value="Inactive">Inactive</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500 pointer-events-none"></i>
            </div>

            <button @click="exportTable(filteredUsers, 'Users_Report')" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors border border-emerald-200 shadow-sm">
              <i class="fas fa-download mr-1.5"></i> Export Filtered
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="text-xs text-slate-500 bg-slate-50 uppercase border-b border-slate-100">
              <tr>
                <th class="px-6 py-4 font-semibold">User details</th>
                <th class="px-6 py-4 font-semibold">Platform Role</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold">Join Date</th>
                <th class="px-6 py-4 font-semibold text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading"><td colspan="5" class="px-6 py-8 text-center text-slate-400">Loading...</td></tr>
              <tr v-else-if="!paginatedUsers.length"><td colspan="5" class="px-6 py-8 text-center text-slate-400">No users found matching filters.</td></tr>
              <tr v-else v-for="(user, index) in paginatedUsers" :key="'u'+index" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-medium text-slate-900">{{ user.name }}</div>
                  <div class="text-xs text-slate-500">{{ user.email }}</div>
                </td>
                <td class="px-6 py-4 text-slate-700 font-medium">{{ user.role }}</td>
                <td class="px-6 py-4">
                  <span :class="getStatusBadgeClass(user.status)" class="px-2.5 py-1 rounded-md text-xs font-semibold border">{{ user.status }}</span>
                </td>
                <td class="px-6 py-4 text-slate-500">{{ user.date }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="openViewModal('user', user)" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">View Data</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="!isLoading" class="p-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
          <span class="text-sm text-slate-500">Showing {{ filteredUsers.length ? (pagination.users.page - 1) * pagination.users.perPage + 1 : 0 }} to {{ Math.min(pagination.users.page * pagination.users.perPage, filteredUsers.length) }} of {{ filteredUsers.length }}</span>
          <div class="flex items-center gap-2">
            <button :disabled="getCurrentPage('users') === 1" @click="changePage('users', -1)" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-sm disabled:opacity-50 hover:bg-slate-100"><i class="fas fa-chevron-left"></i></button>
            <span class="text-sm font-medium text-slate-700 px-2">Page {{ getCurrentPage('users') }} of {{ getMaxPage('users') || 1 }}</span>
            <button :disabled="getCurrentPage('users') >= getMaxPage('users')" @click="changePage('users', 1)" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-sm disabled:opacity-50 hover:bg-slate-100"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
      </div>

      <!-- TABLE 2: Technical Reports -->
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
          <div>
            <h3 class="text-lg font-bold text-slate-900"><i class="fas fa-bug text-amber-500 mr-2"></i>Technical Reports</h3>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <input v-model="filters.tech.search" type="text" placeholder="Search reporters..." class="text-sm border-slate-200 rounded-lg px-3 py-2 w-64 focus:ring-indigo-500 focus:border-indigo-500">
            
            <div class="relative">
              <select v-model="filters.tech.category" class="appearance-none text-sm border-slate-200 rounded-lg pl-3 pr-8 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                <option value="">All Categories</option>
                <option v-for="category in availableTechCategories" :key="category" :value="category">{{ category }}</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500 pointer-events-none"></i>
            </div>

            <div class="relative">
              <select v-model="filters.tech.status" class="appearance-none text-sm border-slate-200 rounded-lg pl-3 pr-8 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Reviewed">Reviewed</option>
                <option value="Resolved">Resolved</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500 pointer-events-none"></i>
            </div>

            <button @click="exportTable(filteredTechReports, 'Technical_Reports')" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors border border-emerald-200 shadow-sm">
              <i class="fas fa-download mr-1.5"></i> Export Filtered
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="text-xs text-slate-500 bg-slate-50 uppercase border-b border-slate-100">
              <tr>
                <th class="px-6 py-4 font-semibold">Reporter Details</th>
                <th class="px-6 py-4 font-semibold">Issue Category</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold">Filed On</th>
                <th class="px-6 py-4 font-semibold text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading"><td colspan="5" class="px-6 py-8 text-center text-slate-400">Loading...</td></tr>
              <tr v-else-if="!paginatedTechReports.length"><td colspan="5" class="px-6 py-8 text-center text-slate-400">No technical reports matching filters.</td></tr>
              <tr v-else v-for="(report, index) in paginatedTechReports" :key="'t'+index" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-medium text-slate-900">{{ report.reporter }}</div>
                  <div class="text-xs text-slate-500">{{ report.role }}</div>
                </td>
                <td class="px-6 py-4 text-slate-700 font-medium">{{ report.category }}</td>
                <td class="px-6 py-4">
                  <span :class="getStatusBadgeClass(report.status)" class="px-2.5 py-1 rounded-md text-xs font-semibold border">{{ report.status }}</span>
                </td>
                <td class="px-6 py-4 text-slate-500">{{ report.date }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="openViewModal('tech', report)" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">View Specs</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div v-if="!isLoading" class="p-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
          <span class="text-sm text-slate-500">Showing {{ filteredTechReports.length ? (pagination.tech.page - 1) * pagination.tech.perPage + 1 : 0 }} to {{ Math.min(pagination.tech.page * pagination.tech.perPage, filteredTechReports.length) }} of {{ filteredTechReports.length }}</span>
          <div class="flex items-center gap-2">
            <button :disabled="getCurrentPage('tech') === 1" @click="changePage('tech', -1)" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-sm disabled:opacity-50 hover:bg-slate-100"><i class="fas fa-chevron-left"></i></button>
            <span class="text-sm font-medium text-slate-700 px-2">Page {{ getCurrentPage('tech') }} of {{ getMaxPage('tech') || 1 }}</span>
            <button :disabled="getCurrentPage('tech') >= getMaxPage('tech')" @click="changePage('tech', 1)" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-sm disabled:opacity-50 hover:bg-slate-100"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
      </div>

      <!-- TABLE 3: User Reports -->
      <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
          <div>
            <h3 class="text-lg font-bold text-slate-900"><i class="fas fa-user-shield text-rose-500 mr-2"></i>User Infraction Reports</h3>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <input v-model="filters.userReport.search" type="text" placeholder="Search reporters or reasons..." class="text-sm border-slate-200 rounded-lg px-3 py-2 w-64 focus:ring-indigo-500 focus:border-indigo-500">
            
            <div class="relative">
              <select v-model="filters.userReport.status" class="appearance-none text-sm border-slate-200 rounded-lg pl-3 pr-8 py-2 w-full focus:ring-indigo-500 focus:border-indigo-500 bg-white cursor-pointer shadow-sm">
                <option value="">All Statuses</option>
                <option value="Pending">Pending</option>
                <option value="Reviewed">Reviewed</option>
              </select>
              <i class="fas fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-500 pointer-events-none"></i>
            </div>

            <button @click="exportTable(filteredUserReports, 'User_Infractions')" class="bg-emerald-50 text-emerald-700 hover:bg-emerald-100 px-4 py-2 rounded-lg text-sm font-medium transition-colors border border-emerald-200 shadow-sm">
              <i class="fas fa-download mr-1.5"></i> Export Filtered
            </button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left whitespace-nowrap">
            <thead class="text-xs text-slate-500 bg-slate-50 uppercase border-b border-slate-100">
              <tr>
                <th class="px-6 py-4 font-semibold">Incident Details</th>
                <th class="px-6 py-4 font-semibold">Reported Entity</th>
                <th class="px-6 py-4 font-semibold">Status</th>
                <th class="px-6 py-4 font-semibold">Filed On</th>
                <th class="px-6 py-4 font-semibold text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="isLoading"><td colspan="5" class="px-6 py-8 text-center text-slate-400">Loading...</td></tr>
              <tr v-else-if="!paginatedUserReports.length"><td colspan="5" class="px-6 py-8 text-center text-slate-400">No user reports matching filters.</td></tr>
              <tr v-else v-for="(report, index) in paginatedUserReports" :key="'ur'+index" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4">
                  <div class="font-medium text-slate-900">{{ report.reason }}</div>
                  <div class="text-xs text-slate-500">By: {{ report.reporter }}</div>
                </td>
                <td class="px-6 py-4 font-medium text-rose-600">{{ report.reported_user }}</td>
                <td class="px-6 py-4">
                  <span :class="getStatusBadgeClass(report.status)" class="px-2.5 py-1 rounded-md text-xs font-semibold border">{{ report.status }}</span>
                </td>
                <td class="px-6 py-4 text-slate-500">{{ report.date }}</td>
                <td class="px-6 py-4 text-right">
                  <button @click="openViewModal('userReport', report)" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition-colors">View Details</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div v-if="!isLoading" class="p-4 border-t border-slate-100 flex items-center justify-between bg-slate-50/50">
          <span class="text-sm text-slate-500">Showing {{ filteredUserReports.length ? (pagination.userReport.page - 1) * pagination.userReport.perPage + 1 : 0 }} to {{ Math.min(pagination.userReport.page * pagination.userReport.perPage, filteredUserReports.length) }} of {{ filteredUserReports.length }}</span>
          <div class="flex items-center gap-2">
            <button :disabled="getCurrentPage('userReport') === 1" @click="changePage('userReport', -1)" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-sm disabled:opacity-50 hover:bg-slate-100"><i class="fas fa-chevron-left"></i></button>
            <span class="text-sm font-medium text-slate-700 px-2">Page {{ getCurrentPage('userReport') }} of {{ getMaxPage('userReport') || 1 }}</span>
            <button :disabled="getCurrentPage('userReport') >= getMaxPage('userReport')" @click="changePage('userReport', 1)" class="px-3 py-1 border border-slate-200 bg-white rounded-md text-sm disabled:opacity-50 hover:bg-slate-100"><i class="fas fa-chevron-right"></i></button>
          </div>
        </div>
      </div>

    </div>

    <!-- UNIVERSAL VIEW MODAL -->
    <div v-if="modal.isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
      <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-200 flex flex-col max-h-[90vh]">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50">
          <h3 class="text-lg font-bold text-slate-900">
            <i :class="modal.type === 'user' ? 'fas fa-user text-indigo-500' : modal.type === 'tech' ? 'fas fa-bug text-amber-500' : 'fas fa-shield-alt text-rose-500'" class="mr-2"></i>
            {{ modal.type === 'user' ? 'Account Profile' : modal.type === 'tech' ? 'System Anomaly Report' : 'Infraction Dossier' }}
          </h3>
          <button @click="closeModal" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-md hover:bg-slate-200 transition-colors">
            <i class="fas fa-times text-lg"></i>
          </button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-grow space-y-6 bg-white">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
            <div v-for="(value, key) in formatModalData(modal.data)" :key="key" class="flex flex-col col-span-1" :class="{ 'sm:col-span-2': String(value).length > 50 }">
              <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">{{ key.replace(/_/g, ' ') }}</span>
              <span class="text-sm font-medium text-slate-800 bg-slate-50 p-3 rounded-lg border border-slate-100 break-words whitespace-pre-wrap leading-relaxed">{{ value || 'N/A' }}</span>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
          <button @click="closeModal" class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-md hover:bg-slate-100 transition-colors">Close</button>
          <button @click="exportSingleRecord" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md shadow-sm transition-colors">
            <i class="fas fa-download mr-2"></i> Export Record
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, shallowRef, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import api from '@/utils/axios.js';
import echo from '@/utils/websocket.js';
import { Bar, Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

// Control States
const isComponentMounted = ref(false);
const isLoading = ref(true);

// Core Data
const summary = ref({ total_users: 0, total_tech_reports: 0, total_user_reports: 0 });
const usersData = ref([]);
const techReportsDataRaw = ref([]);
const userReportsDataRaw = ref([]);

// Dynamic Options for Filters
const availableRoles = computed(() => [...new Set(usersData.value.map(u => u.role))].sort());
const availableTechCategories = computed(() => [...new Set(techReportsDataRaw.value.map(t => t.category))].sort());

// Pagination & Filters State
const filters = ref({
  users: { search: '', status: '', role: '' },
  tech: { search: '', status: '', category: '' },
  userReport: { search: '', status: '' }
});

const pagination = ref({
  users: { page: 1, perPage: 5 },
  tech: { page: 1, perPage: 5 },
  userReport: { page: 1, perPage: 5 }
});

// Reset pagination when filters change
watch(() => filters.value.users, () => pagination.value.users.page = 1, { deep: true });
watch(() => filters.value.tech, () => pagination.value.tech.page = 1, { deep: true });
watch(() => filters.value.userReport, () => pagination.value.userReport.page = 1, { deep: true });

// Chart Data (shallowRef prevents chartjs deep clone crashes)
const chartDataRoles = shallowRef({ labels: [], datasets: [] });
const chartDataTech = shallowRef({ labels: [], datasets: [] });
const chartDataUser = shallowRef({ labels: [], datasets: [] });

// Modal State
const modal = ref({
  isOpen: false,
  type: null, 
  data: null
});

// Palettes
const palettes = { primary: '#4f46e5', secondary: '#0ea5e9', accent1: '#e11d48', accent2: '#f59e0b', accent3: '#10b981', accent4: '#8b5cf6', grid: '#f1f5f9', text: '#64748b' };
const defaultTooltip = { backgroundColor: '#ffffff', titleColor: '#0f172a', bodyColor: '#475569', borderColor: '#e2e8f0', borderWidth: 1, padding: 10, cornerRadius: 6, usePointStyle: true };

const doughnutOptions = { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } }, tooltip: defaultTooltip } };
const barOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: defaultTooltip }, scales: { y: { border: { display: false }, grid: { color: palettes.grid }, ticks: { color: palettes.text } }, x: { grid: { display: false }, ticks: { color: palettes.text } } }, borderRadius: 6, barThickness: 24 };

// Horizontal Bar Options
const barOptionsHorizontal = {
  ...barOptions,
  indexAxis: 'y',
  scales: {
    x: { border: { display: false }, grid: { color: palettes.grid, drawTicks: false }, ticks: { color: palettes.text, padding: 10 } },
    y: { grid: { display: false }, ticks: { color: palettes.text, padding: 10 } }
  },
  barThickness: 24
};

// Computed logic for Users
const filteredUsers = computed(() => {
  return usersData.value.filter(u => {
    const matchSearch = u.name.toLowerCase().includes(filters.value.users.search.toLowerCase()) || u.email.toLowerCase().includes(filters.value.users.search.toLowerCase());
    const matchStatus = filters.value.users.status ? u.status === filters.value.users.status : true;
    const matchRole = filters.value.users.role ? u.role === filters.value.users.role : true;
    return matchSearch && matchStatus && matchRole;
  });
});
const paginatedUsers = computed(() => {
  const start = (pagination.value.users.page - 1) * pagination.value.users.perPage;
  return filteredUsers.value.slice(start, start + pagination.value.users.perPage);
});

// Computed logic for Tech Reports
const filteredTechReports = computed(() => {
  return techReportsDataRaw.value.filter(t => {
    const matchSearch = t.reporter.toLowerCase().includes(filters.value.tech.search.toLowerCase());
    const matchStatus = filters.value.tech.status ? t.status === filters.value.tech.status : true;
    const matchCategory = filters.value.tech.category ? t.category === filters.value.tech.category : true;
    return matchSearch && matchStatus && matchCategory;
  });
});
const paginatedTechReports = computed(() => {
  const start = (pagination.value.tech.page - 1) * pagination.value.tech.perPage;
  return filteredTechReports.value.slice(start, start + pagination.value.tech.perPage);
});

// Computed logic for User Reports
const filteredUserReports = computed(() => {
  return userReportsDataRaw.value.filter(ur => {
    const matchSearch = ur.reporter.toLowerCase().includes(filters.value.userReport.search.toLowerCase()) || ur.reason.toLowerCase().includes(filters.value.userReport.search.toLowerCase());
    const matchStatus = filters.value.userReport.status ? ur.status === filters.value.userReport.status : true;
    return matchSearch && matchStatus;
  });
});
const paginatedUserReports = computed(() => {
  const start = (pagination.value.userReport.page - 1) * pagination.value.userReport.perPage;
  return filteredUserReports.value.slice(start, start + pagination.value.userReport.perPage);
});

// Pagination Helpers
const getCurrentPage = (type) => pagination.value[type].page;
const getMaxPage = (type) => {
  if (type === 'users') return Math.ceil(filteredUsers.value.length / pagination.value.users.perPage);
  if (type === 'tech') return Math.ceil(filteredTechReports.value.length / pagination.value.tech.perPage);
  return Math.ceil(filteredUserReports.value.length / pagination.value.userReport.perPage);
};
const changePage = (type, step) => {
  pagination.value[type].page += step;
};

// Fetch Data
const fetchReports = async (isBackgroundRefresh = false) => {
  if (!isComponentMounted.value) return;
  if (!isBackgroundRefresh) isLoading.value = true;

  try {
    const response = await api.get('/admin/reports-analytics');
    if (!isComponentMounted.value) return;

    if (response.data && response.data.data) {
      const { data } = response.data;
      summary.value = data.summary;
      usersData.value = data.users;
      techReportsDataRaw.value = data.tech_reports;
      userReportsDataRaw.value = data.user_reports;

      extractChartData(data.users, data.tech_reports, data.user_reports);
    }
  } catch (error) {
    if (isComponentMounted.value) console.error("Data Hub fetch failed:", error);
  } finally {
    if (isComponentMounted.value && !isBackgroundRefresh) isLoading.value = false;
  }
};

const extractChartData = (users, tech, userReps) => {
  // Roles Doughnut
  const roles = {};
  users.forEach(u => roles[u.role] = (roles[u.role] || 0) + 1);
  chartDataRoles.value = {
    labels: Object.keys(roles),
    datasets: [{ data: Object.values(roles), backgroundColor: [palettes.primary, palettes.secondary, palettes.accent3, palettes.accent4, palettes.accent2, palettes.accent1], borderWidth: 0 }]
  };

  // Tech Categories Bar
  const tCats = {};
  tech.forEach(t => tCats[t.category] = (tCats[t.category] || 0) + 1);
  chartDataTech.value = {
    labels: Object.keys(tCats),
    datasets: [{ data: Object.values(tCats), backgroundColor: palettes.accent2, borderRadius: 4 }]
  };

  // User Reports Horizontal Bar
  const uCats = {};
  userReps.forEach(u => uCats[u.reason] = (uCats[u.reason] || 0) + 1);
  chartDataUser.value = {
    labels: Object.keys(uCats),
    datasets: [{ data: Object.values(uCats), backgroundColor: palettes.accent1, borderRadius: 4 }]
  };
};

// Modals
const openViewModal = (type, data) => {
  modal.value = { isOpen: true, type, data };
};
const closeModal = () => {
  modal.value.isOpen = false;
};

// Filter out internal IDs for modal display
const formatModalData = (data) => {
  if (!data) return {};
  const formatted = { ...data };
  delete formatted.id;
  delete formatted.raw_date;
  return formatted;
};

// Styling Badges
const getStatusBadgeClass = (status) => {
  const s = status.toLowerCase();
  if (['active', 'resolved', 'approved'].includes(s)) return 'bg-emerald-50 text-emerald-700 border-emerald-200';
  if (['pending'].includes(s)) return 'bg-amber-50 text-amber-700 border-amber-200';
  if (['reviewed'].includes(s)) return 'bg-blue-50 text-blue-700 border-blue-200';
  return 'bg-rose-50 text-rose-700 border-rose-200'; 
};

// Exporters
const convertToCSV = (dataArr) => {
  if (!dataArr || !dataArr.length) return '';
  const keys = Object.keys(dataArr[0]).filter(k => k !== 'id' && k !== 'raw_date');
  let csv = keys.map(k => `"${k.toUpperCase()}"`).join(',') + '\n';
  dataArr.forEach(row => {
    csv += keys.map(k => {
      let cell = row[k] === null || row[k] === undefined ? '' : String(row[k]);
      cell = cell.replace(/"/g, '""'); 
      return `"${cell}"`;
    }).join(',') + '\n';
  });
  return csv;
};

const triggerDownload = (csvContent, filename) => {
  const encodedUri = encodeURI("data:text/csv;charset=utf-8," + csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", filename + ".csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const exportTable = (filteredArray, filename) => {
  if (!filteredArray.length) return alert("No data to export matching filters.");
  const csv = convertToCSV(filteredArray);
  triggerDownload(csv, filename);
};

const exportSingleRecord = () => {
  if (!modal.value.data) return;
  const csv = convertToCSV([modal.value.data]);
  triggerDownload(csv, `Record_Export_${modal.value.type}`);
};

const exportAllData = () => {
  if (usersData.value.length) {
    setTimeout(() => exportTable(usersData.value, 'All_Users_Database'), 0);
  }
  if (techReportsDataRaw.value.length) {
    setTimeout(() => exportTable(techReportsDataRaw.value, 'All_Technical_Reports'), 500);
  }
  if (userReportsDataRaw.value.length) {
    setTimeout(() => exportTable(userReportsDataRaw.value, 'All_User_Infractions'), 1000);
  }
};

// Lifecycle
onMounted(() => {
  isComponentMounted.value = true;
  fetchReports(false);

  if (echo) {
    try {
      echo.private('admin.technical_reports')
        .listen('.report.submitted', () => {
          if (!isComponentMounted.value) return;
          fetchReports(true); 
        })
        .listen('.report.updated', () => {
          if (isComponentMounted.value) fetchReports(true);
        });
    } catch (e) {
      console.warn("WebSocket listener initialization skipped:", e);
    }
  }
});

onBeforeUnmount(() => {
  isComponentMounted.value = false;
  chartDataRoles.value = { labels: [], datasets: [] };
  chartDataTech.value = { labels: [], datasets: [] };
  chartDataUser.value = { labels: [], datasets: [] };
  try {
    if (echo) echo.leave('admin.technical_reports');
  } catch (e) {}
});
</script>