<template>
  <div class="clients-page">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="page-title">
            <div class="title-icon-wrapper">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <span>Client Management</span>
            <span class="role-badge">CRM</span>
          </h1>
          <p class="page-subtitle">Manage client relationships and service history</p>
        </div>
        <div class="header-right">
          <button @click="showAddClientModal = true" class="header-btn add-client-btn">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add New Client
          </button>
          <div class="search-container">
            <div class="search-icon">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search clients..." 
              class="search-input"
              @input="filterClients"
            />
          </div>
        </div>
      </div>
      
      <!-- Stats Overview -->
      <div class="stats-overview">
        <div class="stat-card">
          <div class="stat-icon bg-gradient-to-r from-blue-500 to-blue-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0a6 6 0 01-9 5.197" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">{{ filteredClients.length }}</div>
            <div class="stat-label">Total Clients</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon bg-gradient-to-r from-green-500 to-emerald-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">8</div>
            <div class="stat-label">Active Projects</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon bg-gradient-to-r from-purple-500 to-purple-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">42</div>
            <div class="stat-label">Total Jobs</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon bg-gradient-to-r from-yellow-500 to-amber-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-value">3</div>
            <div class="stat-label">New This Week</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Clients Table -->
    <div class="content-card">
      <div class="card-header">
        <div class="card-title">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
          Client Directory
        </div>
        <div class="card-actions">
          <div class="view-toggle">
            <button @click="viewMode = 'grid'" :class="['view-btn', { 'active': viewMode === 'grid' }]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </button>
            <button @click="viewMode = 'list'" :class="['view-btn', { 'active': viewMode === 'list' }]">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
          </div>
          <button @click="exportClients" class="action-btn">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export
          </button>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list'" class="table-container">
        <table class="clients-table">
          <thead>
            <tr>
              <th class="table-header">
                <div class="flex items-center">
                  <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="checkbox" />
                  <span class="ml-2">Client</span>
                </div>
              </th>
              <th class="table-header">Contact Info</th>
              <th class="table-header">Location</th>
              <th class="table-header">Job Count</th>
              <th class="table-header">Status</th>
              <th class="table-header">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="client in filteredClients" :key="client.id" class="table-row">
              <td class="table-cell">
                <div class="client-info">
                  <input type="checkbox" :value="client.id" v-model="selectedClients" class="checkbox" />
                  <div class="client-avatar">
                    <div class="avatar-initials">{{ getInitials(client.name) }}</div>
                    <div :class="['status-dot', client.status === 'active' ? 'bg-green-500' : 'bg-gray-500']"></div>
                  </div>
                  <div class="client-details">
                    <div class="client-name">{{ client.name }}</div>
                    <div class="client-email">{{ client.email }}</div>
                  </div>
                </div>
              </td>
              <td class="table-cell">
                <div class="contact-info">
                  <div class="flex items-center mb-1">
                    <svg class="w-3 h-3 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    <span class="text-sm">{{ client.phone }}</span>
                  </div>
                </div>
              </td>
              <td class="table-cell">
                <div class="location-info">
                  <div class="flex items-start">
                    <svg class="w-3 h-3 mr-2 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-sm">{{ client.address }}</span>
                  </div>
                </div>
              </td>
              <td class="table-cell">
                <div class="job-count">
                  <div class="count-badge">{{ client.jobCount }}</div>
                  <span class="text-xs text-gray-400 ml-2">jobs</span>
                </div>
              </td>
              <td class="table-cell">
                <div :class="['status-badge', client.status === 'active' ? 'status-active' : 'status-inactive']">
                  {{ client.status === 'active' ? 'Active' : 'Inactive' }}
                </div>
              </td>
              <td class="table-cell">
                <div class="action-buttons">
                  <button @click="viewClientHistory(client)" class="action-icon" title="View History">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button @click="editClient(client)" class="action-icon" title="Edit Client">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="deleteClient(client.id)" class="action-icon text-red-400 hover:text-red-300" title="Delete Client">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid'" class="grid-container">
        <div class="grid">
          <div v-for="client in filteredClients" :key="client.id" class="client-card">
            <div class="card-top">
              <div class="card-header-grid">
                <div class="client-avatar-grid">
                  <div class="avatar-initials-grid">{{ getInitials(client.name) }}</div>
                  <div :class="['status-dot-grid', client.status === 'active' ? 'bg-green-500' : 'bg-gray-500']"></div>
                </div>
                <div class="client-info-grid">
                  <div class="client-name-grid">{{ client.name }}</div>
                  <div class="client-email-grid">{{ client.email }}</div>
                </div>
                <div class="card-actions-grid">
                  <input type="checkbox" :value="client.id" v-model="selectedClients" class="checkbox" />
                </div>
              </div>
              
              <div class="client-details-grid">
                <div class="detail-item">
                  <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span>{{ client.phone }}</span>
                </div>
                <div class="detail-item">
                  <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  </svg>
                  <span class="truncate">{{ client.address }}</span>
                </div>
              </div>
            </div>
            
            <div class="card-bottom">
              <div class="card-stats">
                <div class="stat-item-grid">
                  <div class="stat-value-grid">{{ client.jobCount }}</div>
                  <div class="stat-label-grid">Jobs</div>
                </div>
                <div :class="['status-badge-grid', client.status === 'active' ? 'status-active' : 'status-inactive']">
                  {{ client.status === 'active' ? 'Active' : 'Inactive' }}
                </div>
              </div>
              
              <div class="card-actions-bottom">
                <button @click="viewClientHistory(client)" class="card-action-btn">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-xs">History</span>
                </button>
                <button @click="editClient(client)" class="card-action-btn">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  <span class="text-xs">Edit</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty State -->
      <div v-if="filteredClients.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <h3 class="empty-title">No Clients Found</h3>
        <p class="empty-description">Try adjusting your search or add a new client to get started.</p>
        <button @click="showAddClientModal = true" class="empty-action-btn">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add New Client
        </button>
      </div>
    </div>

    <!-- Add/Edit Client Modal -->
    <div v-if="showAddClientModal || showEditClientModal" class="modal-overlay" @click="closeModal">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">
            {{ editingClient ? 'Edit Client' : 'Add New Client' }}
          </h3>
          <button @click="closeModal" class="modal-close-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="saveClient" class="modal-form">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input 
                v-model="clientForm.name"
                type="text" 
                required
                class="form-input"
                placeholder="Enter client name"
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">Email Address *</label>
              <input 
                v-model="clientForm.email"
                type="email" 
                required
                class="form-input"
                placeholder="client@example.com"
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">Phone Number *</label>
              <input 
                v-model="clientForm.phone"
                type="tel" 
                required
                class="form-input"
                placeholder="+63 XXX XXX XXXX"
              />
            </div>
            
            <div class="form-group">
              <label class="form-label">Address</label>
              <textarea 
                v-model="clientForm.address"
                class="form-input h-20"
                placeholder="Enter client address"
                rows="3"
              ></textarea>
            </div>
            
            <div class="form-group">
              <label class="form-label">Status</label>
              <div class="status-options">
                <label class="status-option">
                  <input 
                    type="radio" 
                    v-model="clientForm.status" 
                    value="active"
                    class="radio-input"
                  />
                  <span class="status-label">Active</span>
                </label>
                <label class="status-option">
                  <input 
                    type="radio" 
                    v-model="clientForm.status" 
                    value="inactive"
                    class="radio-input"
                  />
                  <span class="status-label">Inactive</span>
                </label>
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">Notes</label>
              <textarea 
                v-model="clientForm.notes"
                class="form-input h-32"
                placeholder="Add any notes about this client..."
                rows="4"
              ></textarea>
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" @click="closeModal" class="cancel-btn">
              Cancel
            </button>
            <button type="submit" class="submit-btn">
              {{ editingClient ? 'Update Client' : 'Add Client' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View History Modal -->
    <div v-if="showHistoryModal" class="modal-overlay" @click="closeHistoryModal">
      <div class="modal-container history-modal" @click.stop>
        <div class="modal-header">
          <h3 class="modal-title">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Service History - {{ selectedClient?.name }}
          </h3>
          <button @click="closeHistoryModal" class="modal-close-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="history-content">
          <div class="timeline">
            <div v-for="job in clientHistory" :key="job.id" class="timeline-item">
              <div class="timeline-dot"></div>
              <div class="timeline-content">
                <div class="timeline-header">
                  <div class="timeline-title">{{ job.service }}</div>
                  <div class="timeline-date">{{ formatDate(job.date) }}</div>
                </div>
                <div class="timeline-body">
                  <div class="timeline-details">
                    <span class="detail-label">Paint Color:</span>
                    <div class="color-preview" :style="{ backgroundColor: job.color }"></div>
                    <span class="color-value">{{ job.colorName }}</span>
                  </div>
                  <div class="timeline-details">
                    <span class="detail-label">Status:</span>
                    <span :class="['status-badge-small', job.status === 'completed' ? 'status-active' : 'status-inactive']">
                      {{ job.status }}
                    </span>
                  </div>
                  <div class="timeline-notes" v-if="job.notes">
                    {{ job.notes }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-if="clientHistory.length === 0" class="no-history">
            <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p class="no-history-text">No service history available for this client.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// Data
const clients = ref([
  {
    id: 1,
    name: 'John Michael Santos',
    email: 'john.santos@example.com',
    phone: '+63 912 345 6789',
    address: '123 Main St, Bacoor, Cavite',
    jobCount: 5,
    status: 'active',
    notes: 'Prefers eco-friendly paint options'
  },
  {
    id: 2,
    name: 'Maria Cristina Garcia',
    email: 'maria.garcia@example.com',
    phone: '+63 917 890 1234',
    address: '456 Oak Ave, Imus, Cavite',
    jobCount: 3,
    status: 'active',
    notes: 'Commercial building owner'
  },
  {
    id: 3,
    name: 'Robert Lim',
    email: 'robert.lim@example.com',
    phone: '+63 918 567 8901',
    address: '789 Pine Rd, Dasmariñas, Cavite',
    jobCount: 8,
    status: 'active',
    notes: 'Frequent customer, bulk orders'
  },
  {
    id: 4,
    name: 'Sarah Tan',
    email: 'sarah.tan@example.com',
    phone: '+63 919 234 5678',
    address: '321 Elm St, Tagaytay, Cavite',
    jobCount: 2,
    status: 'inactive',
    notes: 'Moved to different province'
  },
  {
    id: 5,
    name: 'David Chen',
    email: 'david.chen@example.com',
    phone: '+63 920 876 5432',
    address: '654 Maple Blvd, Trece Martires',
    jobCount: 6,
    status: 'active',
    notes: 'Interior design contractor'
  },
  {
    id: 6,
    name: 'Ana Rodriguez',
    email: 'ana.rodriguez@example.com',
    phone: '+63 921 345 6789',
    address: '987 Cedar Ln, Gen. Trias, Cavite',
    jobCount: 4,
    status: 'active',
    notes: 'Residential painting projects'
  },
  {
    id: 7,
    name: 'James Wilson',
    email: 'james.wilson@example.com',
    phone: '+63 922 987 6543',
    address: '147 Birch St, Silang, Cavite',
    jobCount: 1,
    status: 'inactive',
    notes: 'Single project completed'
  },
  {
    id: 8,
    name: 'Lisa Martinez',
    email: 'lisa.martinez@example.com',
    phone: '+63 923 456 7890',
    address: '258 Walnut Ave, Kawit, Cavite',
    jobCount: 7,
    status: 'active',
    notes: 'Hotel renovation project'
  }
])

const searchQuery = ref('')
const viewMode = ref('list')
const selectedClients = ref([])
const selectAll = ref(false)
const showAddClientModal = ref(false)
const showEditClientModal = ref(false)
const showHistoryModal = ref(false)
const editingClient = ref(null)
const selectedClient = ref(null)

const clientForm = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  status: 'active',
  notes: ''
})

const clientHistory = ref([
  {
    id: 1,
    service: 'Living Room Painting',
    date: '2024-01-15',
    color: '#4A90E2',
    colorName: 'Ocean Blue',
    status: 'completed',
    notes: 'Two coats applied, client satisfied with color matching'
  },
  {
    id: 2,
    service: 'Bedroom Wall Repair & Painting',
    date: '2024-02-20',
    color: '#F5A623',
    colorName: 'Sunset Orange',
    status: 'completed',
    notes: 'Minor wall repair before painting'
  },
  {
    id: 3,
    service: 'Kitchen Cabinets Refinishing',
    date: '2024-03-10',
    color: '#7ED321',
    colorName: 'Mint Green',
    status: 'in-progress',
    notes: 'Scheduled for second coat'
  }
])

// Computed
const filteredClients = computed(() => {
  if (!searchQuery.value.trim()) return clients.value
  
  const query = searchQuery.value.toLowerCase()
  return clients.value.filter(client => 
    client.name.toLowerCase().includes(query) ||
    client.email.toLowerCase().includes(query) ||
    client.phone.includes(query) ||
    client.address.toLowerCase().includes(query)
  )
})

// Methods
const filterClients = () => {
  // Search logic handled by computed property
}

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedClients.value = clients.value.map(client => client.id)
  } else {
    selectedClients.value = []
  }
}

const getInitials = (name) => {
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

const addClient = () => {
  const newClient = {
    id: clients.value.length + 1,
    ...clientForm.value,
    jobCount: 0
  }
  clients.value.unshift(newClient)
  resetForm()
  showAddClientModal.value = false
}

const editClient = (client) => {
  editingClient.value = client
  clientForm.value = { ...client }
  showEditClientModal.value = true
}

const saveClient = () => {
  if (editingClient.value) {
    const index = clients.value.findIndex(c => c.id === editingClient.value.id)
    if (index !== -1) {
      clients.value[index] = { ...editingClient.value, ...clientForm.value }
    }
  } else {
    addClient()
  }
  closeModal()
}

const deleteClient = (id) => {
  if (confirm('Are you sure you want to delete this client?')) {
    clients.value = clients.value.filter(client => client.id !== id)
    selectedClients.value = selectedClients.value.filter(clientId => clientId !== id)
  }
}

const viewClientHistory = (client) => {
  selectedClient.value = client
  showHistoryModal.value = true
}

const exportClients = () => {
  const data = JSON.stringify(filteredClients.value, null, 2)
  const blob = new Blob([data], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'clients-export.json'
  a.click()
  URL.revokeObjectURL(url)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const resetForm = () => {
  clientForm.value = {
    name: '',
    email: '',
    phone: '',
    address: '',
    status: 'active',
    notes: ''
  }
  editingClient.value = null
}

const closeModal = () => {
  showAddClientModal.value = false
  showEditClientModal.value = false
  resetForm()
}

const closeHistoryModal = () => {
  showHistoryModal.value = false
  selectedClient.value = null
}

// Lifecycle
onMounted(() => {
  // Any initialization logic
})
</script>

<style scoped>
.clients-page {
  min-height: 100vh;
  background: #0f172a;
  color: #e2e8f0;
}

/* Page Header */
.page-header {
  padding: 1.5rem 2rem;
  background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
  border-bottom: 1px solid rgba(30, 41, 59, 0.5);
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.header-left {
  flex: 1;
  min-width: 300px;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #f1f5f9;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.title-icon-wrapper {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: linear-gradient(45deg, #10b981, #34d399);
  display: flex;
  align-items: center;
  justify-content: center;
}

.role-badge {
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.page-subtitle {
  color: #94a3b8;
  font-size: 0.875rem;
}

.header-right {
  display: flex;
  gap: 1rem;
  align-items: center;
  flex-wrap: wrap;
}

.add-client-btn {
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  border: none;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  white-space: nowrap;
}

.add-client-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.search-container {
  position: relative;
  min-width: 250px;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
}

.search-input {
  width: 100%;
  padding: 0.625rem 0.75rem 0.625rem 2.5rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  color: #e2e8f0;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.search-input::placeholder {
  color: #64748b;
}

/* Stats Overview */
.stats-overview {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.stat-card {
  background: rgba(30, 41, 59, 0.5);
  border: 1px solid rgba(51, 65, 85, 0.5);
  border-radius: 0.75rem;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: #475569;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 40px;
  height: 40px;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #f1f5f9;
}

.stat-label {
  font-size: 0.75rem;
  color: #94a3b8;
}

/* Content Card */
.content-card {
  margin: 1.5rem 2rem;
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.75rem;
  overflow: hidden;
}

.card-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #334155;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #f1f5f9;
  display: flex;
  align-items: center;
}

.card-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.view-toggle {
  display: flex;
  gap: 0.25rem;
  background: #0f172a;
  border-radius: 0.375rem;
  padding: 0.25rem;
}

.view-btn {
  padding: 0.375rem;
  border-radius: 0.25rem;
  background: transparent;
  border: none;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-btn.active {
  background: #334155;
  color: #e2e8f0;
}

.view-btn:hover:not(.active) {
  background: rgba(51, 65, 85, 0.5);
}

.action-btn {
  padding: 0.5rem 0.75rem;
  border-radius: 0.375rem;
  background: #334155;
  border: 1px solid #475569;
  color: #e2e8f0;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
}

.action-btn:hover {
  background: #475569;
  border-color: #64748b;
}

/* Table Styles */
.table-container {
  overflow-x: auto;
}

.clients-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header {
  padding: 1rem 1.5rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #334155;
  background: rgba(15, 23, 42, 0.5);
}

.table-row {
  border-bottom: 1px solid #334155;
  transition: all 0.2s ease;
}

.table-row:hover {
  background: rgba(51, 65, 85, 0.2);
}

.table-cell {
  padding: 1rem 1.5rem;
  vertical-align: middle;
}

.client-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.checkbox {
  width: 1rem;
  height: 1rem;
  border-radius: 0.25rem;
  border: 1px solid #475569;
  background: #1e293b;
  cursor: pointer;
  accent-color: #3b82f6;
}

.client-avatar {
  position: relative;
  width: 36px;
  height: 36px;
}

.avatar-initials {
  width: 36px;
  height: 36px;
  border-radius: 0.5rem;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.75rem;
}

.status-dot {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid #1e293b;
}

.client-details {
  flex: 1;
}

.client-name {
  font-weight: 500;
  color: #f1f5f9;
  margin-bottom: 0.125rem;
}

.client-email {
  font-size: 0.75rem;
  color: #94a3b8;
}

.job-count {
  display: flex;
  align-items: center;
}

.count-badge {
  padding: 0.25rem 0.5rem;
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #60a5fa;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  display: inline-block;
}

.status-active {
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.2);
  color: #4ade80;
}

.status-inactive {
  background: rgba(100, 116, 139, 0.1);
  border: 1px solid rgba(100, 116, 139, 0.2);
  color: #94a3b8;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-icon {
  padding: 0.375rem;
  border-radius: 0.375rem;
  background: transparent;
  border: 1px solid #475569;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-icon:hover {
  background: #334155;
  border-color: #64748b;
  color: #e2e8f0;
}

/* Grid View */
.grid-container {
  padding: 1.5rem;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.client-card {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid #334155;
  border-radius: 0.75rem;
  overflow: hidden;
  transition: all 0.2s ease;
}

.client-card:hover {
  transform: translateY(-2px);
  border-color: #475569;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.card-top {
  padding: 1.25rem;
  border-bottom: 1px solid #334155;
}

.card-header-grid {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.client-avatar-grid {
  position: relative;
}

.avatar-initials-grid {
  width: 40px;
  height: 40px;
  border-radius: 0.5rem;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.status-dot-grid {
  position: absolute;
  bottom: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  border-radius: 50%;
  border: 2px solid #1e293b;
}

.client-info-grid {
  flex: 1;
}

.client-name-grid {
  font-weight: 500;
  color: #f1f5f9;
  margin-bottom: 0.125rem;
}

.client-email-grid {
  font-size: 0.75rem;
  color: #94a3b8;
}

.card-actions-grid {
  margin-left: auto;
}

.client-details-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

.card-bottom {
  padding: 1.25rem;
}

.card-stats {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.stat-item-grid {
  text-align: center;
}

.stat-value-grid {
  font-size: 1.25rem;
  font-weight: 700;
  color: #f1f5f9;
}

.stat-label-grid {
  font-size: 0.75rem;
  color: #94a3b8;
}

.status-badge-grid {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.card-actions-bottom {
  display: flex;
  gap: 0.5rem;
}

.card-action-btn {
  flex: 1;
  padding: 0.5rem;
  border-radius: 0.375rem;
  background: #334155;
  border: 1px solid #475569;
  color: #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
}

.card-action-btn:hover {
  background: #475569;
  border-color: #64748b;
}

/* Empty State */
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.empty-icon {
  margin-bottom: 1rem;
  color: #475569;
}

.empty-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #f1f5f9;
  margin-bottom: 0.5rem;
}

.empty-description {
  color: #94a3b8;
  font-size: 0.875rem;
  margin-bottom: 1.5rem;
}

.empty-action-btn {
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  border: none;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
}

.empty-action-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal-container {
  background: #1e293b;
  border: 1px solid #334155;
  border-radius: 0.75rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #334155;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #f1f5f9;
  display: flex;
  align-items: center;
}

.modal-close-btn {
  padding: 0.5rem;
  border-radius: 0.375rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.modal-close-btn:hover {
  background: #334155;
  color: #e2e8f0;
}

.modal-form {
  padding: 1.5rem;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.form-group:nth-child(4),
.form-group:nth-child(5),
.form-group:nth-child(6) {
  grid-column: span 2;
}

.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #e2e8f0;
  margin-bottom: 0.5rem;
}

.form-input {
  width: 100%;
  padding: 0.625rem 0.75rem;
  background: #0f172a;
  border: 1px solid #334155;
  border-radius: 0.5rem;
  color: #e2e8f0;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
}

.form-input::placeholder {
  color: #64748b;
}

.status-options {
  display: flex;
  gap: 1rem;
}

.status-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.radio-input {
  width: 1rem;
  height: 1rem;
  border-radius: 50%;
  border: 1px solid #475569;
  background: #0f172a;
  cursor: pointer;
  accent-color: #3b82f6;
}

.status-label {
  font-size: 0.875rem;
  color: #e2e8f0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid #334155;
}

.cancel-btn {
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  background: transparent;
  border: 1px solid #475569;
  color: #e2e8f0;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: #334155;
  border-color: #64748b;
}

.submit-btn {
  padding: 0.625rem 1.25rem;
  border-radius: 0.5rem;
  border: none;
  background: linear-gradient(45deg, #3b82f6, #8b5cf6);
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.submit-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* History Modal */
.history-modal {
  max-width: 700px;
}

.history-content {
  padding: 1.5rem;
}

.timeline {
  position: relative;
  padding-left: 2rem;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 1.25rem;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #334155;
}

.timeline-item {
  position: relative;
  margin-bottom: 1.5rem;
}

.timeline-dot {
  position: absolute;
  left: -2rem;
  top: 0.5rem;
  width: 0.75rem;
  height: 0.75rem;
  border-radius: 50%;
  background: #3b82f6;
  border: 2px solid #1e293b;
}

.timeline-content {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid #334155;
  border-radius: 0.5rem;
  padding: 1rem;
}

.timeline-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.timeline-title {
  font-weight: 500;
  color: #f1f5f9;
}

.timeline-date {
  font-size: 0.75rem;
  color: #94a3b8;
}

.timeline-body {
  font-size: 0.875rem;
  color: #cbd5e1;
}

.timeline-details {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.detail-label {
  color: #94a3b8;
  font-weight: 500;
}

.color-preview {
  width: 1rem;
  height: 1rem;
  border-radius: 0.25rem;
  border: 1px solid #334155;
}

.color-value {
  color: #e2e8f0;
}

.status-badge-small {
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.timeline-notes {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #334155;
  color: #94a3b8;
  font-style: italic;
}

.no-history {
  text-align: center;
  padding: 3rem 1rem;
}

.no-history-text {
  margin-top: 1rem;
  color: #94a3b8;
  font-size: 0.875rem;
}

/* Responsive Design */
@media (max-width: 1024px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .form-group:nth-child(4),
  .form-group:nth-child(5),
  .form-group:nth-child(6) {
    grid-column: span 1;
  }
}

@media (max-width: 768px) {
  .page-header {
    padding: 1rem;
  }
  
  .content-card {
    margin: 1rem;
  }
  
  .header-content {
    flex-direction: column;
  }
  
  .header-right {
    width: 100%;
    flex-direction: column;
    gap: 1rem;
  }
  
  .search-container {
    width: 100%;
  }
  
  .stats-overview {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .grid {
    grid-template-columns: 1fr;
  }
  
  .modal-container {
    width: 95%;
    margin: 1rem;
  }
}

@media (max-width: 640px) {
  .stats-overview {
    grid-template-columns: 1fr;
  }
  
  .card-header {
    flex-direction: column;
    gap: 1rem;
    align-items: stretch;
  }
  
  .card-actions {
    justify-content: space-between;
  }
  
  .clients-table {
    min-width: 800px;
  }
  
  .table-container {
    overflow-x: auto;
  }
}

/* Scrollbar Styling */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #1e293b;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}
</style>