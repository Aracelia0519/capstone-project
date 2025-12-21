<template>
  <div class="distributors-container">
    <!-- Header Section -->
    <div class="header-section">
      <div class="header-content">
        <div class="header-left">
          <div class="header-icon">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div>
            <h1 class="page-title">Distributors</h1>
            <p class="page-subtitle">Supply awareness & inventory management</p>
          </div>
        </div>
        
        <div class="header-actions">
          <!-- Search Bar -->
          <div class="search-container">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Search distributors or paints..."
              class="search-input"
            >
          </div>
          
          <!-- Filter Button -->
          <button @click="toggleFilter" class="filter-btn">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            <span>Filter</span>
            <span v-if="activeFilterCount > 0" class="filter-badge">{{ activeFilterCount }}</span>
          </button>
          
          <!-- View Toggle -->
          <div class="view-toggle">
            <button 
              @click="viewMode = 'grid'"
              :class="['view-btn', { 'active': viewMode === 'grid' }]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
              </svg>
            </button>
            <button 
              @click="viewMode = 'list'"
              :class="['view-btn', { 'active': viewMode === 'list' }]"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Filter Panel -->
      <div v-if="showFilter" class="filter-panel">
        <div class="filter-grid">
          <div class="filter-group">
            <label class="filter-label">Sort by</label>
            <select v-model="sortBy" class="filter-select">
              <option value="name">Name A-Z</option>
              <option value="rating">Highest Rating</option>
              <option value="distance">Nearest First</option>
              <option value="inventory">Most Inventory</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">Paint Type</label>
            <select v-model="paintTypeFilter" class="filter-select">
              <option value="all">All Types</option>
              <option value="latex">Latex Paint</option>
              <option value="oil">Oil-based</option>
              <option value="spray">Spray Paint</option>
              <option value="specialty">Specialty</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">Availability</label>
            <select v-model="availabilityFilter" class="filter-select">
              <option value="all">All</option>
              <option value="in-stock">In Stock</option>
              <option value="low-stock">Low Stock</option>
            </select>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">Rating</label>
            <select v-model="ratingFilter" class="filter-select">
              <option value="all">All Ratings</option>
              <option value="4+">4+ Stars</option>
              <option value="3+">3+ Stars</option>
            </select>
          </div>
        </div>
        
        <div class="filter-actions">
          <button @click="clearFilters" class="clear-filters-btn">
            Clear Filters
          </button>
          <button @click="applyFilters" class="apply-filters-btn">
            Apply Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="stats-overview">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon bg-blue-500/10">
            <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalDistributors }}</div>
            <div class="stat-label">Total Distributors</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon bg-green-500/10">
            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ totalProducts }}</div>
            <div class="stat-label">Available Products</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon bg-purple-500/10">
            <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ inStockProducts }}</div>
            <div class="stat-label">In Stock Items</div>
          </div>
        </div>
        
        <div class="stat-card">
          <div class="stat-icon bg-amber-500/10">
            <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="stat-content">
            <div class="stat-value">{{ avgDeliveryTime }}</div>
            <div class="stat-label">Avg Delivery Time</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Empty State -->
      <div v-if="filteredDistributors.length === 0" class="empty-state">
        <div class="empty-icon">
          <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="empty-title">No distributors found</h3>
        <p class="empty-description">
          {{ searchQuery ? 'Try adjusting your search or filters' : 'No distributors available in your area' }}
        </p>
      </div>

      <!-- Grid View -->
      <div v-if="viewMode === 'grid' && filteredDistributors.length > 0" class="distributor-grid">
        <div 
          v-for="distributor in paginatedDistributors"
          :key="distributor.id"
          class="distributor-card"
        >
          <!-- Card Header -->
          <div class="card-header">
            <div class="distributor-avatar">
              {{ distributor.name.charAt(0).toUpperCase() }}
            </div>
            <div class="distributor-info">
              <h3 class="distributor-name">{{ distributor.name }}</h3>
              <div class="distributor-meta">
                <span class="distributor-type">{{ distributor.type }}</span>
                <div class="rating">
                  <div class="stars">
                    <span v-for="star in 5" :key="star">
                      <svg 
                        class="w-4 h-4"
                        :class="star <= Math.floor(distributor.rating) ? 'text-yellow-400 fill-current' : 'text-gray-600'"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                      </svg>
                    </span>
                  </div>
                  <span class="rating-text">{{ distributor.rating.toFixed(1) }}</span>
                  <span class="review-count">({{ distributor.reviews }})</span>
                </div>
              </div>
            </div>
            <div class="status-badge" :class="distributor.status">
              {{ distributor.status === 'online' ? 'Online' : 'Closed' }}
            </div>
          </div>

          <!-- Available Paints -->
          <div class="paints-section">
            <h4 class="section-title">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
              Available Paints
            </h4>
            <div class="paints-grid">
              <div 
                v-for="paint in distributor.paints.slice(0, 4)"
                :key="paint.name"
                class="paint-item"
                :style="{ '--paint-color': paint.color }"
              >
                <div class="paint-color" :style="{ backgroundColor: paint.color }"></div>
                <div class="paint-details">
                  <span class="paint-name">{{ paint.name }}</span>
                  <span class="paint-type">{{ paint.type }}</span>
                </div>
                <div class="paint-status" :class="paint.stock">
                  {{ paint.stock === 'in-stock' ? 'In Stock' : 'Low Stock' }}
                </div>
              </div>
            </div>
            <div v-if="distributor.paints.length > 4" class="more-paints">
              +{{ distributor.paints.length - 4 }} more paints available
            </div>
          </div>

          <!-- Contact Details -->
          <div class="contact-section">
            <h4 class="section-title">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Contact Details
            </h4>
            <div class="contact-grid">
              <div class="contact-item">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <span>{{ distributor.contact.phone }}</span>
              </div>
              <div class="contact-item">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span>{{ distributor.contact.email }}</span>
              </div>
              <div class="contact-item">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ distributor.contact.address }}</span>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="action-buttons">
            <button @click="contactDistributor(distributor)" class="action-btn-primary">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              Contact Now
            </button>
            <button @click="viewDetails(distributor)" class="action-btn-secondary">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              View Details
            </button>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list' && filteredDistributors.length > 0" class="distributor-list">
        <div class="list-header">
          <div class="list-column">Distributor</div>
          <div class="list-column">Available Paints</div>
          <div class="list-column">Contact</div>
          <div class="list-column">Status</div>
          <div class="list-column">Actions</div>
        </div>
        
        <div 
          v-for="distributor in paginatedDistributors"
          :key="distributor.id"
          class="list-item"
        >
          <div class="list-cell distributor-cell">
            <div class="distributor-avatar-sm">
              {{ distributor.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <div class="distributor-name">{{ distributor.name }}</div>
              <div class="distributor-type-sm">{{ distributor.type }}</div>
              <div class="rating-sm">
                <span class="stars-sm">
                  <span v-for="star in 5" :key="star">
                    <svg 
                      class="w-3 h-3"
                      :class="star <= Math.floor(distributor.rating) ? 'text-yellow-400 fill-current' : 'text-gray-600'"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                  </span>
                </span>
                <span class="rating-text-sm">{{ distributor.rating.toFixed(1) }}</span>
              </div>
            </div>
          </div>
          
          <div class="list-cell paints-cell">
            <div class="paints-list">
              <div class="paint-count">
                {{ distributor.paints.length }} paints available
              </div>
              <div class="paint-samples">
                <div 
                  v-for="paint in distributor.paints.slice(0, 3)"
                  :key="paint.name"
                  class="paint-sample"
                  :style="{ backgroundColor: paint.color }"
                  :title="paint.name"
                ></div>
              </div>
            </div>
          </div>
          
          <div class="list-cell contact-cell">
            <div class="contact-info-sm">
              <div class="contact-item-sm">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                {{ distributor.contact.phone }}
              </div>
              <div class="contact-item-sm">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ distributor.contact.email }}
              </div>
            </div>
          </div>
          
          <div class="list-cell status-cell">
            <div class="status-badge-sm" :class="distributor.status">
              {{ distributor.status === 'online' ? 'Online' : 'Closed' }}
            </div>
            <div class="delivery-time">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ distributor.deliveryTime }} hours
            </div>
          </div>
          
          <div class="list-cell actions-cell">
            <button @click="contactDistributor(distributor)" class="list-action-btn">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
            </button>
            <button @click="viewDetails(distributor)" class="list-action-btn">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="filteredDistributors.length > 0" class="pagination">
        <div class="pagination-info">
          Showing {{ startIndex + 1 }}-{{ endIndex }} of {{ filteredDistributors.length }} distributors
        </div>
        <div class="pagination-controls">
          <button 
            @click="prevPage" 
            :disabled="currentPage === 1"
            class="pagination-btn"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          
          <div class="page-numbers">
            <button 
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="['page-btn', { 'active': page === currentPage }]"
            >
              {{ page }}
            </button>
          </div>
          
          <button 
            @click="nextPage" 
            :disabled="currentPage === totalPages"
            class="pagination-btn"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Notification -->
    <div v-if="showNotification" class="notification">
      <div class="notification-content">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ notificationMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Distributors',
  data() {
    return {
      searchQuery: '',
      showFilter: false,
      viewMode: 'grid',
      sortBy: 'name',
      paintTypeFilter: 'all',
      availabilityFilter: 'all',
      ratingFilter: 'all',
      currentPage: 1,
      itemsPerPage: 8,
      showNotification: false,
      notificationMessage: '',
      
      // Sample data
      distributors: [
        {
          id: 1,
          name: 'Premium Paints Inc.',
          type: 'Wholesale Distributor',
          rating: 4.8,
          reviews: 234,
          status: 'online',
          deliveryTime: 24,
          paints: [
            { name: 'Ocean Blue', type: 'Latex', color: '#3B82F6', stock: 'in-stock' },
            { name: 'Forest Green', type: 'Oil-based', color: '#10B981', stock: 'in-stock' },
            { name: 'Sunset Orange', type: 'Latex', color: '#F59E0B', stock: 'low-stock' },
            { name: 'Royal Purple', type: 'Spray', color: '#8B5CF6', stock: 'in-stock' },
            { name: 'Crimson Red', type: 'Latex', color: '#EF4444', stock: 'in-stock' },
            { name: 'Sky Blue', type: 'Specialty', color: '#0EA5E9', stock: 'in-stock' },
          ],
          contact: {
            phone: '(555) 123-4567',
            email: 'contact@premiumpaints.com',
            address: '123 Paint St, Color City'
          }
        },
        {
          id: 2,
          name: 'Color Masters Supply',
          type: 'Retail Supplier',
          rating: 4.5,
          reviews: 189,
          status: 'online',
          deliveryTime: 36,
          paints: [
            { name: 'Emerald Green', type: 'Latex', color: '#10B981', stock: 'in-stock' },
            { name: 'Golden Yellow', type: 'Oil-based', color: '#FBBF24', stock: 'low-stock' },
            { name: 'Midnight Blue', type: 'Latex', color: '#1E40AF', stock: 'in-stock' },
            { name: 'Rose Pink', type: 'Spray', color: '#F472B6', stock: 'in-stock' },
          ],
          contact: {
            phone: '(555) 987-6543',
            email: 'sales@colormasters.com',
            address: '456 Supply Ave, Paint Town'
          }
        },
        {
          id: 3,
          name: 'Industrial Coatings Ltd.',
          type: 'Industrial Supplier',
          rating: 4.9,
          reviews: 312,
          status: 'online',
          deliveryTime: 48,
          paints: [
            { name: 'Slate Gray', type: 'Industrial', color: '#64748B', stock: 'in-stock' },
            { name: 'Charcoal Black', type: 'Industrial', color: '#1F2937', stock: 'in-stock' },
            { name: 'Pure White', type: 'Industrial', color: '#F8FAFC', stock: 'low-stock' },
            { name: 'Safety Yellow', type: 'Industrial', color: '#FDE047', stock: 'in-stock' },
            { name: 'Warning Red', type: 'Industrial', color: '#DC2626', stock: 'in-stock' },
          ],
          contact: {
            phone: '(555) 456-7890',
            email: 'industrial@coatings.com',
            address: '789 Industry Blvd, Factory District'
          }
        },
        {
          id: 4,
          name: 'Eco-Friendly Paints',
          type: 'Specialty Distributor',
          rating: 4.7,
          reviews: 156,
          status: 'closed',
          deliveryTime: 72,
          paints: [
            { name: 'Eco Green', type: 'Eco-friendly', color: '#34D399', stock: 'in-stock' },
            { name: 'Natural Brown', type: 'Eco-friendly', color: '#92400E', stock: 'in-stock' },
            { name: 'Organic White', type: 'Eco-friendly', color: '#F9FAFB', stock: 'in-stock' },
          ],
          contact: {
            phone: '(555) 234-5678',
            email: 'eco@ecopaints.com',
            address: '321 Green St, Eco Village'
          }
        },
        {
          id: 5,
          name: 'Metropolitan Paint Supply',
          type: 'Urban Distributor',
          rating: 4.3,
          reviews: 98,
          status: 'online',
          deliveryTime: 12,
          paints: [
            { name: 'City Gray', type: 'Latex', color: '#6B7280', stock: 'low-stock' },
            { name: 'Metro Blue', type: 'Latex', color: '#3B82F6', stock: 'in-stock' },
            { name: 'Urban Red', type: 'Spray', color: '#EF4444', stock: 'in-stock' },
            { name: 'Street Yellow', type: 'Latex', color: '#F59E0B', stock: 'in-stock' },
          ],
          contact: {
            phone: '(555) 876-5432',
            email: 'metro@paintsupply.com',
            address: '987 Urban Ave, Downtown'
          }
        },
        {
          id: 6,
          name: 'Artisan Paint Co.',
          type: 'Art Supplies',
          rating: 4.6,
          reviews: 210,
          status: 'online',
          deliveryTime: 60,
          paints: [
            { name: 'Artist Blue', type: 'Acrylic', color: '#2563EB', stock: 'in-stock' },
            { name: 'Canvas White', type: 'Acrylic', color: '#FFFFFF', stock: 'in-stock' },
            { name: 'Brush Red', type: 'Acrylic', color: '#DC2626', stock: 'low-stock' },
            { name: 'Palette Yellow', type: 'Acrylic', color: '#FBBF24', stock: 'in-stock' },
            { name: 'Easel Green', type: 'Acrylic', color: '#059669', stock: 'in-stock' },
          ],
          contact: {
            phone: '(555) 345-6789',
            email: 'art@artisanpaint.com',
            address: '654 Art St, Creative District'
          }
        },
        {
          id: 7,
          name: 'Quick Dry Paints',
          type: 'Fast Delivery',
          rating: 4.2,
          reviews: 143,
          status: 'online',
          deliveryTime: 6,
          paints: [
            { name: 'Quick White', type: 'Fast-dry', color: '#F8FAFC', stock: 'in-stock' },
            { name: 'Rapid Gray', type: 'Fast-dry', color: '#4B5563', stock: 'in-stock' },
            { name: 'Speedy Black', type: 'Fast-dry', color: '#111827', stock: 'low-stock' },
          ],
          contact: {
            phone: '(555) 567-8901',
            email: 'quick@quickdry.com',
            address: '321 Speedway, Express Zone'
          }
        },
        {
          id: 8,
          name: 'Heritage Paints',
          type: 'Traditional Supplier',
          rating: 4.8,
          reviews: 267,
          status: 'closed',
          deliveryTime: 96,
          paints: [
            { name: 'Heritage Red', type: 'Traditional', color: '#B91C1C', stock: 'in-stock' },
            { name: 'Classic Blue', type: 'Traditional', color: '#1D4ED8', stock: 'in-stock' },
            { name: 'Victorian Green', type: 'Traditional', color: '#047857', stock: 'in-stock' },
            { name: 'Edwardian Yellow', type: 'Traditional', color: '#F59E0B', stock: 'low-stock' },
          ],
          contact: {
            phone: '(555) 678-9012',
            email: 'heritage@traditional.com',
            address: '789 History Ln, Old Town'
          }
        },
      ]
    }
  },
  computed: {
    totalDistributors() {
      return this.distributors.length
    },
    
    totalProducts() {
      return this.distributors.reduce((total, dist) => total + dist.paints.length, 0)
    },
    
    inStockProducts() {
      return this.distributors.reduce((total, dist) => {
        return total + dist.paints.filter(p => p.stock === 'in-stock').length
      }, 0)
    },
    
    avgDeliveryTime() {
      const total = this.distributors.reduce((sum, dist) => sum + dist.deliveryTime, 0)
      return Math.round(total / this.distributors.length)
    },
    
    activeFilterCount() {
      let count = 0
      if (this.sortBy !== 'name') count++
      if (this.paintTypeFilter !== 'all') count++
      if (this.availabilityFilter !== 'all') count++
      if (this.ratingFilter !== 'all') count++
      return count
    },
    
    filteredDistributors() {
      let filtered = [...this.distributors]
      
      // Apply search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(dist => 
          dist.name.toLowerCase().includes(query) ||
          dist.type.toLowerCase().includes(query) ||
          dist.paints.some(p => p.name.toLowerCase().includes(query)) ||
          dist.contact.email.toLowerCase().includes(query)
        )
      }
      
      // Apply paint type filter
      if (this.paintTypeFilter !== 'all') {
        filtered = filtered.filter(dist => 
          dist.paints.some(p => p.type.toLowerCase().includes(this.paintTypeFilter))
        )
      }
      
      // Apply availability filter
      if (this.availabilityFilter === 'in-stock') {
        filtered = filtered.filter(dist => 
          dist.paints.some(p => p.stock === 'in-stock')
        )
      } else if (this.availabilityFilter === 'low-stock') {
        filtered = filtered.filter(dist => 
          dist.paints.some(p => p.stock === 'low-stock')
        )
      }
      
      // Apply rating filter
      if (this.ratingFilter !== 'all') {
        const minRating = parseFloat(this.ratingFilter)
        filtered = filtered.filter(dist => dist.rating >= minRating)
      }
      
      // Apply sorting
      switch (this.sortBy) {
        case 'rating':
          filtered.sort((a, b) => b.rating - a.rating)
          break
        case 'distance':
          filtered.sort((a, b) => a.deliveryTime - b.deliveryTime)
          break
        case 'inventory':
          filtered.sort((a, b) => b.paints.length - a.paints.length)
          break
        default: // name
          filtered.sort((a, b) => a.name.localeCompare(b.name))
      }
      
      return filtered
    },
    
    totalPages() {
      return Math.ceil(this.filteredDistributors.length / this.itemsPerPage)
    },
    
    paginatedDistributors() {
      const start = (this.currentPage - 1) * this.itemsPerPage
      const end = start + this.itemsPerPage
      return this.filteredDistributors.slice(start, end)
    },
    
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage
    },
    
    endIndex() {
      return Math.min(this.startIndex + this.itemsPerPage, this.filteredDistributors.length)
    },
    
    visiblePages() {
      const pages = []
      const maxVisible = 5
      let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2))
      let end = Math.min(this.totalPages, start + maxVisible - 1)
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    }
  },
  watch: {
    searchQuery() {
      this.currentPage = 1
    },
    paintTypeFilter() {
      this.currentPage = 1
    },
    availabilityFilter() {
      this.currentPage = 1
    }
  },
  methods: {
    toggleFilter() {
      this.showFilter = !this.showFilter
    },
    
    applyFilters() {
      this.showFilter = false
    },
    
    clearFilters() {
      this.sortBy = 'name'
      this.paintTypeFilter = 'all'
      this.availabilityFilter = 'all'
      this.ratingFilter = 'all'
      this.searchQuery = ''
      this.showFilter = false
    },
    
    contactDistributor(distributor) {
      this.showNotificationMessage(`Contacting ${distributor.name}...`)
    },
    
    viewDetails(distributor) {
      this.showNotificationMessage(`Viewing details for ${distributor.name}`)
    },
    
    showNotificationMessage(message) {
      this.notificationMessage = message
      this.showNotification = true
      setTimeout(() => {
        this.showNotification = false
      }, 3000)
    },
    
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--
        this.scrollToTop()
      }
    },
    
    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++
        this.scrollToTop()
      }
    },
    
    goToPage(page) {
      this.currentPage = page
      this.scrollToTop()
    },
    
    scrollToTop() {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }
}
</script>

<style scoped src="../serviceProvider/style/Distributors.css"></style>
