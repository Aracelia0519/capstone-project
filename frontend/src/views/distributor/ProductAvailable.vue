<template>
  <div class="ecommerce-container">
    <!-- Header with Navigation -->
    <header class="ecommerce-header">
      <div class="header-content">
        <div class="brand-section">
          <div class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
              <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
              <line x1="12" y1="22.08" x2="12" y2="12"></line>
            </svg>
            <h1>CaviteGo Distributor</h1>
          </div>
          <p class="tagline">Professional Paint & Supplies Procurement</p>
        </div>
        
        <div class="header-actions">
          <div class="search-container">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
              type="text" 
              v-model="searchQuery"
              placeholder="Search products..." 
              class="search-input"
            >
          </div>
          
          <button class="cart-btn" @click="showCart = !showCart">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <span class="cart-count" v-if="procurementRequests.length > 0">
              {{ procurementRequests.length }}
            </span>
            <span>Request List</span>
          </button>
        </div>
      </div>
    </header>

    <main class="main-content">
      <!-- Sidebar Filters -->
      <aside class="sidebar">
        <div class="sidebar-card">
          <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="4" y1="21" x2="4" y2="14"></line>
              <line x1="4" y1="10" x2="4" y2="3"></line>
              <line x1="12" y1="21" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12" y2="3"></line>
              <line x1="20" y1="21" x2="20" y2="16"></line>
              <line x1="20" y1="12" x2="20" y2="3"></line>
              <line x1="1" y1="14" x2="7" y2="14"></line>
              <line x1="9" y1="8" x2="15" y2="8"></line>
              <line x1="17" y1="16" x2="23" y2="16"></line>
            </svg>
            Filters
          </h3>
          
          <div class="filter-group">
            <h4>Category</h4>
            <div class="filter-options">
              <label v-for="category in categories" :key="category.value" class="filter-checkbox">
                <input 
                  type="checkbox" 
                  :value="category.value"
                  v-model="selectedCategories"
                >
                <span class="checkbox-custom"></span>
                <span class="filter-label">
                  <span class="category-dot" :class="category.class"></span>
                  {{ category.label }}
                </span>
                <span class="filter-count">{{ category.count }}</span>
              </label>
            </div>
          </div>
          
          <div class="filter-group">
            <h4>Product Type</h4>
            <select v-model="selectedType" class="filter-select">
              <option value="">All Types</option>
              <option v-for="type in availableTypes" :key="type" :value="type">
                {{ type }}
              </option>
            </select>
          </div>
          
          <div class="filter-group">
            <h4>Size</h4>
            <div class="filter-chips">
              <button 
                v-for="size in popularSizes" 
                :key="size"
                @click="toggleSize(size)"
                :class="['size-chip', { active: selectedSizes.includes(size) }]"
              >
                {{ size }}
              </button>
            </div>
          </div>
          
          <div class="filter-group" v-if="selectedCategories.includes('Paint Products')">
            <h4>Color</h4>
            <div class="color-filters">
              <div 
                v-for="color in colorOptions" 
                :key="color.value"
                class="color-option"
                :class="{ active: selectedColor === color.value }"
                @click="selectedColor = selectedColor === color.value ? '' : color.value"
                :style="{ backgroundColor: color.value }"
                :title="color.name"
              ></div>
            </div>
          </div>
          
          <button @click="clearFilters" class="clear-filters">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 6h18"></path>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            Clear All Filters
          </button>
        </div>
        
        <!-- Quick Add Product Card -->
        <div class="sidebar-card add-product-card">
          <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Quick Add Product
          </h3>
          <button @click="showAddModal = true" class="add-product-btn">
            Add New Product
          </button>
        </div>
      </aside>

      <!-- Main Product Grid -->
      <div class="product-grid-section">
        <div class="grid-header">
          <h2>Available Products <span class="product-count">({{ filteredProducts.length }})</span></h2>
          <div class="sort-options">
            <span>Sort by:</span>
            <select v-model="sortOption" class="sort-select">
              <option value="name">Name (A-Z)</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="newest">Newest First</option>
            </select>
          </div>
        </div>
        
        <div class="product-grid">
          <div 
            v-for="product in sortedProducts" 
            :key="product.id"
            class="product-card"
            :class="{ 'in-request': isInRequestList(product.id) }"
          >
            <div class="product-image">
              <img v-if="product.image" :src="product.image" :alt="product.name">
              <div v-else class="image-placeholder" :style="{ backgroundColor: product.rgb || '#f0f0f0' }">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <circle cx="8.5" cy="8.5" r="1.5"></circle>
                  <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
              </div>
              <span class="category-badge" :class="getCategoryClass(product.category)">
                {{ product.category.split(' ')[0] }}
              </span>
              <button 
                v-if="product.rgb" 
                class="color-preview"
                :style="{ backgroundColor: product.rgb }"
                :title="product.rgb"
              ></button>
            </div>
            
            <div class="product-info">
              <div class="product-header">
                <h3 class="product-name">{{ product.name }}</h3>
                <span class="product-price">₱{{ formatPrice(product.price) }}</span>
              </div>
              
              <div class="product-meta">
                <span class="product-type">{{ product.type }}</span>
                <span class="product-size">{{ product.size }}</span>
              </div>
              
              <p v-if="product.description" class="product-description">
                {{ truncateDescription(product.description) }}
              </p>
              
              <div class="product-actions">
                <button 
                  @click="toggleRequest(product)"
                  :class="['request-btn', { 'in-cart': isInRequestList(product.id) }]"
                >
                  <svg v-if="isInRequestList(product.id)" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                  </svg>
                  {{ isInRequestList(product.id) ? 'In Request List' : 'Add to Request' }}
                </button>
                
                <button @click="editProduct(product)" class="details-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                  Edit
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="filteredProducts.length === 0" class="empty-state">
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <h3>No products found</h3>
          <p>Try adjusting your filters or add a new product</p>
        </div>
      </div>

      <!-- Procurement Request Sidebar (Cart) -->
      <div class="cart-sidebar" :class="{ active: showCart }">
        <div class="cart-header">
          <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
              <line x1="3" y1="6" x2="21" y2="6"></line>
              <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            Procurement Request List
          </h3>
          <button @click="showCart = false" class="close-cart">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="cart-content">
          <div v-if="procurementRequests.length === 0" class="empty-cart">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="9" cy="21" r="1"></circle>
              <circle cx="20" cy="21" r="1"></circle>
              <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
            <p>Your request list is empty</p>
            <p class="hint">Add products from the catalog</p>
          </div>
          
          <div v-else class="cart-items">
            <div 
              v-for="item in procurementRequests" 
              :key="item.id"
              class="cart-item"
            >
              <div class="cart-item-image">
                <img v-if="item.image" :src="item.image" :alt="item.name">
                <div v-else class="item-placeholder" :style="{ backgroundColor: item.rgb || '#f0f0f0' }"></div>
              </div>
              
              <div class="cart-item-info">
                <h4>{{ item.name }}</h4>
                <div class="item-meta">
                  <span class="item-type">{{ item.type }}</span>
                  <span class="item-size">{{ item.size }}</span>
                </div>
                <div class="item-price">₱{{ formatPrice(item.price) }}</div>
              </div>
              
              <button @click="removeFromRequest(item.id)" class="remove-item">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
          </div>
          
          <div v-if="procurementRequests.length > 0" class="cart-summary">
            <div class="summary-row">
              <span>Total Items:</span>
              <span>{{ procurementRequests.length }}</span>
            </div>
            <div class="summary-row total">
              <span>Estimated Total:</span>
              <span>₱{{ formatPrice(totalRequestValue) }}</span>
            </div>
            
            <div class="cart-actions">
              <button @click="clearRequestList" class="btn-clear">
                Clear All
              </button>
              <button @click="submitProcurementRequest" class="btn-submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Submit Request
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Overlay for cart sidebar -->
      <div v-if="showCart" class="cart-overlay" @click="showCart = false"></div>
    </main>

    <!-- Add Product Modal -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            {{ isEditing ? 'Edit Product' : 'Add New Product' }}
          </h3>
          <button @click="closeModal" class="close-modal">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <form @submit.prevent="isEditing ? updateProduct() : addProduct()" class="add-product-form">
          <div class="form-group">
            <label for="category">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
              </svg>
              Category *
            </label>
            <select id="category" v-model="newProduct.category" required @change="onCategoryChange" class="form-select">
              <option value="">Select Category</option>
              <option value="Paint Products">Paint Products</option>
              <option value="Coatings & Chemicals">Coatings & Chemicals</option>
              <option value="Solvents & Thinners">Solvents & Thinners</option>
              <option value="Painting Tools & Accessories">Painting Tools & Accessories</option>
              <option value="Packaging & Containers">Packaging & Containers</option>
            </select>
          </div>

          <div class="form-group">
            <label for="type">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                <polyline points="2 17 12 22 22 17"></polyline>
                <polyline points="2 12 12 17 22 12"></polyline>
              </svg>
              Type *
            </label>
            <select id="type" v-model="newProduct.type" required :disabled="!newProduct.category" class="form-select">
              <option value="">Select Type</option>
              <option v-for="type in filteredTypes" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>

          <div class="form-group">
            <label for="name">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
              </svg>
              Product Name *
            </label>
            <input type="text" id="name" v-model="newProduct.name" placeholder="Enter product name" required class="form-input">
          </div>

          <div class="form-group">
            <label for="size">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="9" y1="9" x2="15" y2="15"></line>
                <line x1="15" y1="9" x2="9" y2="15"></line>
              </svg>
              Size *
            </label>
            <select id="size" v-model="newProduct.size" required class="form-select">
              <option value="">Select Size</option>
              <option v-for="size in sizes" :key="size" :value="size">{{ size }}</option>
            </select>
          </div>

          <div class="form-group" v-if="showColorField">
            <label for="rgb">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <circle cx="12" cy="12" r="10"></circle>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
              Color (RGB/Hex)
            </label>
            <div class="color-input-group">
              <input type="text" id="rgb" v-model="newProduct.rgb" placeholder="#FFFFFF or rgb(255,255,255)" class="form-input">
              <div class="color-preview-display" :style="{ backgroundColor: newProduct.rgb || 'transparent' }"></div>
            </div>
          </div>

          <div class="form-group">
            <label for="price">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg>
              Price (₱) *
            </label>
            <input type="number" id="price" v-model="newProduct.price" placeholder="0.00" min="0" step="0.01" required class="form-input">
          </div>

          <div class="form-group">
            <label for="description">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              Description
            </label>
            <textarea id="description" v-model="newProduct.description" rows="3" placeholder="Enter product description..." class="form-textarea"></textarea>
          </div>

          <div class="form-group">
            <label>
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                <polyline points="21 15 16 10 5 21"></polyline>
              </svg>
              Product Image
            </label>
            <div class="image-upload">
              <input type="file" id="image" ref="fileInput" @change="handleImageUpload" accept="image/*" class="file-input">
              <label for="image" class="upload-label">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="17 8 12 3 7 8"></polyline>
                  <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span>{{ imagePreview || newProduct.image ? 'Change Image' : 'Upload Image' }}</span>
              </label>
              <div v-if="imagePreview || newProduct.image" class="image-preview">
                <img :src="imagePreview || newProduct.image" alt="Preview">
                <button type="button" @click="removeImage" class="remove-image">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" @click="closeModal" class="btn-cancel">
              Cancel
            </button>
            <button v-if="isEditing" type="submit" class="btn-submit">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Update Product
            </button>
            <button v-else type="submit" class="btn-submit">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              Add Product
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProductAvailableEcommerce',
  data() {
    return {
      searchQuery: '',
      selectedCategories: [],
      selectedType: '',
      selectedSizes: [],
      selectedColor: '',
      sortOption: 'newest',
      showCart: false,
      showAddModal: false,
      procurementRequests: [],
      isEditing: false,
      editingId: null,
      imagePreview: '',
      
      newProduct: {
        category: '',
        type: '',
        name: '',
        size: '',
        rgb: '',
        price: '',
        description: '',
        image: ''
      },
      
      products: [
        {
          id: 1,
          name: 'Premium Latex Paint',
          category: 'Paint Products',
          type: 'Latex / Acrylic',
          size: '4L',
          rgb: '#FFFFFF',
          price: 1250.00,
          description: 'High-quality interior latex paint, perfect for walls and ceilings.',
          image: ''
        },
        {
          id: 2,
          name: 'Weather-Resistant Exterior Paint',
          category: 'Paint Products',
          type: 'Weather-resistant',
          size: '20L',
          rgb: '#B0C4DE',
          price: 4500.00,
          description: 'Durable exterior paint with UV protection.',
          image: ''
        },
        {
          id: 3,
          name: 'Metal Primer',
          category: 'Coatings & Chemicals',
          type: 'Metal primer',
          size: '4L',
          price: 850.00,
          description: 'Anti-rust primer for metal surfaces.',
          image: ''
        },
        {
          id: 4,
          name: 'Paint Brushes Set',
          category: 'Painting Tools & Accessories',
          type: 'Paint brushes',
          size: 'Medium',
          price: 350.00,
          description: 'Professional grade paint brushes set.',
          image: ''
        },
        {
          id: 5,
          name: 'Paint Thinner',
          category: 'Solvents & Thinners',
          type: 'Paint thinner',
          size: '1L',
          price: 120.00,
          description: 'High-quality paint thinner.',
          image: ''
        },
        {
          id: 6,
          name: 'Paint Cans',
          category: 'Packaging & Containers',
          type: 'Metal cans',
          size: '4L',
          price: 45.00,
          description: 'Empty metal paint cans.',
          image: ''
        }
      ],
      
      productTypes: {
        'Paint Products': [
          'Latex / Acrylic', 'Water-based', 'Low-VOC', 'Anti-mold', 'Washable',
          'Weather-resistant', 'Waterproof', 'UV-resistant', 'Epoxy paint',
          'Enamel paint', 'Anti-rust', 'Chalk paint', 'Textured paint',
          'Metallic paint', 'General spray', 'Industrial spray',
          'Decorative spray', 'Protective spray'
        ],
        'Coatings & Chemicals': [
          'Wall primer', 'Metal primer', 'Wood primer', 'Sealer',
          'Clear varnish', 'Satin varnish', 'Gloss varnish',
          'Fast-dry lacquer', 'Protective finish', 'Liquid waterproofing',
          'Hardeners', 'Thinners'
        ],
        'Solvents & Thinners': [
          'Paint thinner', 'Mineral spirits', 'Turpentine',
          'Cleaning solvents', 'Degreasers'
        ],
        'Painting Tools & Accessories': [
          'Paint brushes', 'Rollers', 'Roller covers', 'Spray guns',
          'Paint trays', 'Sandpaper', 'Scrapers', 'Putty knives',
          'Wire brushes', 'Gloves', 'Face masks', 'Respirators',
          'Safety goggles', 'Coveralls'
        ],
        'Packaging & Containers': [
          'Metal cans', 'Plastic buckets', 'Steel drums',
          'Plastic drums', 'Aerosol cans', 'Plastic containers'
        ]
      },
      
      sizes: [
        '1L', '4L', '20L', '400ml', '600ml', '500ml',
        'Small', 'Medium', 'Large', '4"', '6"', '9"',
        '80 grit', '120 grit', '220 grit', '6"', '2"', '4"',
        '10L', '50L', '200L', '5L', 'Disposable', 'Reusable',
        'Half-mask', 'Full-face', 'Standard', 'Anti-fog', 'XL'
      ],
      
      categories: [
        { value: 'Paint Products', label: 'Paint Products', class: 'cat-paint', count: 2 },
        { value: 'Coatings & Chemicals', label: 'Coatings', class: 'cat-coatings', count: 1 },
        { value: 'Solvents & Thinners', label: 'Solvents', class: 'cat-solvents', count: 1 },
        { value: 'Painting Tools & Accessories', label: 'Tools', class: 'cat-tools', count: 1 },
        { value: 'Packaging & Containers', label: 'Packaging', class: 'cat-packaging', count: 1 }
      ],
      
      popularSizes: ['1L', '4L', '20L', 'Small', 'Medium', 'Large'],
      
      colorOptions: [
        { name: 'White', value: '#FFFFFF' },
        { name: 'Beige', value: '#F5F5DC' },
        { name: 'Light Blue', value: '#B0C4DE' },
        { name: 'Gray', value: '#708090' },
        { name: 'Red', value: '#8B0000' }
      ],
      
      nextId: 7
    };
  },
  computed: {
    availableTypes() {
      const types = new Set();
      this.products.forEach(product => {
        types.add(product.type);
      });
      return Array.from(types);
    },
    
    filteredProducts() {
      let filtered = this.products;
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(product =>
          product.name.toLowerCase().includes(query) ||
          product.type.toLowerCase().includes(query) ||
          (product.description && product.description.toLowerCase().includes(query))
        );
      }
      
      if (this.selectedCategories.length > 0) {
        filtered = filtered.filter(product => 
          this.selectedCategories.includes(product.category)
        );
      }
      
      if (this.selectedType) {
        filtered = filtered.filter(product => 
          product.type === this.selectedType
        );
      }
      
      if (this.selectedSizes.length > 0) {
        filtered = filtered.filter(product => 
          this.selectedSizes.includes(product.size)
        );
      }
      
      if (this.selectedColor && this.selectedCategories.includes('Paint Products')) {
        filtered = filtered.filter(product => 
          product.rgb && product.rgb.toLowerCase() === this.selectedColor.toLowerCase()
        );
      }
      
      return filtered;
    },
    
    sortedProducts() {
      const products = [...this.filteredProducts];
      
      switch (this.sortOption) {
        case 'name':
          return products.sort((a, b) => a.name.localeCompare(b.name));
        case 'price_low':
          return products.sort((a, b) => a.price - b.price);
        case 'price_high':
          return products.sort((a, b) => b.price - a.price);
        case 'newest':
          return products.sort((a, b) => b.id - a.id);
        default:
          return products;
      }
    },
    
    totalRequestValue() {
      return this.procurementRequests.reduce((total, item) => total + item.price, 0);
    },
    
    filteredTypes() {
      if (!this.newProduct.category) return [];
      return this.productTypes[this.newProduct.category] || [];
    },
    
    showColorField() {
      return this.newProduct.category === 'Paint Products';
    }
  },
  methods: {
    getCategoryClass(category) {
      const classes = {
        'Paint Products': 'cat-paint',
        'Coatings & Chemicals': 'cat-coatings',
        'Solvents & Thinners': 'cat-solvents',
        'Painting Tools & Accessories': 'cat-tools',
        'Packaging & Containers': 'cat-packaging'
      };
      return classes[category] || '';
    },
    
    toggleSize(size) {
      const index = this.selectedSizes.indexOf(size);
      if (index > -1) {
        this.selectedSizes.splice(index, 1);
      } else {
        this.selectedSizes.push(size);
      }
    },
    
    toggleRequest(product) {
      const index = this.procurementRequests.findIndex(item => item.id === product.id);
      if (index > -1) {
        this.procurementRequests.splice(index, 1);
      } else {
        this.procurementRequests.push({ ...product });
      }
    },
    
    isInRequestList(productId) {
      return this.procurementRequests.some(item => item.id === productId);
    },
    
    removeFromRequest(productId) {
      this.procurementRequests = this.procurementRequests.filter(item => item.id !== productId);
    },
    
    clearRequestList() {
      if (confirm('Clear all items from request list?')) {
        this.procurementRequests = [];
      }
    },
    
    submitProcurementRequest() {
      if (this.procurementRequests.length === 0) {
        alert('Please add products to your request list.');
        return;
      }
      
      alert(`Procurement request submitted for ${this.procurementRequests.length} items! Total: ₱${this.formatPrice(this.totalRequestValue)}`);
      this.procurementRequests = [];
      this.showCart = false;
    },
    
    editProduct(product) {
      this.isEditing = true;
      this.editingId = product.id;
      this.newProduct = { 
        ...product,
        price: product.price.toString()
      };
      this.imagePreview = product.image;
      this.showAddModal = true;
    },
    
    updateProduct() {
      if (!this.validateProduct()) return;
      
      const index = this.products.findIndex(p => p.id === this.editingId);
      if (index !== -1) {
        this.products[index] = {
          id: this.editingId,
          ...this.newProduct,
          price: parseFloat(this.newProduct.price),
          image: this.imagePreview || this.newProduct.image
        };
        
        this.updateCategoryCounts();
        alert('Product updated successfully!');
        this.closeModal();
      }
    },
    
    clearFilters() {
      this.selectedCategories = [];
      this.selectedType = '';
      this.selectedSizes = [];
      this.selectedColor = '';
      this.searchQuery = '';
    },
    
    closeModal() {
      this.showAddModal = false;
      this.isEditing = false;
      this.editingId = null;
      this.resetForm();
    },
    
    onCategoryChange() {
      this.newProduct.type = '';
    },
    
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.imagePreview = e.target.result;
            this.newProduct.image = e.target.result;
          };
          reader.readAsDataURL(file);
        } else {
          alert('Please select an image file.');
          this.$refs.fileInput.value = '';
        }
      }
    },
    
    removeImage() {
      this.imagePreview = '';
      this.newProduct.image = '';
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    
    addProduct() {
      if (!this.validateProduct()) return;
      
      const product = {
        id: this.nextId++,
        ...this.newProduct,
        price: parseFloat(this.newProduct.price),
        image: this.imagePreview || this.newProduct.image
      };
      
      this.products.unshift(product);
      this.updateCategoryCounts();
      this.closeModal();
    },
    
    deleteProduct(id) {
      if (confirm('Are you sure you want to delete this product?')) {
        this.products = this.products.filter(product => product.id !== id);
        this.updateCategoryCounts();
      }
    },
    
    resetForm() {
      this.newProduct = {
        category: '',
        type: '',
        name: '',
        size: '',
        rgb: '',
        price: '',
        description: '',
        image: ''
      };
      this.imagePreview = '';
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    
    validateProduct() {
      if (!this.newProduct.category) {
        alert('Please select a category.');
        return false;
      }
      if (!this.newProduct.type) {
        alert('Please select a type.');
        return false;
      }
      if (!this.newProduct.name.trim()) {
        alert('Please enter a product name.');
        return false;
      }
      if (!this.newProduct.size) {
        alert('Please select a size.');
        return false;
      }
      if (!this.newProduct.price || parseFloat(this.newProduct.price) <= 0) {
        alert('Please enter a valid price.');
        return false;
      }
      return true;
    },
    
    updateCategoryCounts() {
      this.categories = this.categories.map(category => {
        const count = this.products.filter(p => p.category === category.value).length;
        return { ...category, count };
      });
    },
    
    formatPrice(price) {
      return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    
    truncateDescription(desc, length = 60) {
      return desc.length > length ? desc.substring(0, length) + '...' : desc;
    }
  }
};
</script>

<style scoped>
.ecommerce-container {
  min-height: 100vh;
  background: #f8f9fa;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

/* Header Styles */
.ecommerce-header {
  background: white;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.brand-section .logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-section h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

.tagline {
  color: #718096;
  font-size: 0.9rem;
  margin-top: 0.25rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.search-container {
  position: relative;
  background: #f7fafc;
  border-radius: 8px;
  padding: 0.5rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 300px;
}

.search-container svg {
  color: #a0aec0;
  flex-shrink: 0;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  font-size: 0.95rem;
}

.cart-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
  position: relative;
}

.cart-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.cart-count {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #f56565;
  color: white;
  border-radius: 50%;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Main Content Layout */
.main-content {
  max-width: 1400px;
  margin: 2rem auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: 2rem;
  position: relative;
}

@media (max-width: 1024px) {
  .main-content {
    grid-template-columns: 1fr;
  }
}

/* Sidebar Styles */
.sidebar-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.sidebar-card h3 {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 1.25rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-group {
  margin-bottom: 1.5rem;
}

.filter-group h4 {
  font-size: 0.9rem;
  font-weight: 600;
  color: #4a5568;
  margin: 0 0 0.75rem 0;
}

.filter-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.filter-checkbox {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 0.25rem 0;
  position: relative;
}

.filter-checkbox input[type="checkbox"] {
  display: none;
}

.checkbox-custom {
  width: 18px;
  height: 18px;
  border: 2px solid #e2e8f0;
  border-radius: 4px;
  position: relative;
  transition: all 0.2s;
}

.filter-checkbox input:checked + .checkbox-custom {
  background: #667eea;
  border-color: #667eea;
}

.filter-checkbox input:checked + .checkbox-custom::after {
  content: '';
  position: absolute;
  left: 5px;
  top: 2px;
  width: 5px;
  height: 9px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}

.filter-label {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  color: #4a5568;
}

.category-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.cat-paint { background: #4299e1; }
.cat-coatings { background: #48bb78; }
.cat-solvents { background: #ed8936; }
.cat-tools { background: #9f7aea; }
.cat-packaging { background: #38b2ac; }

.filter-count {
  font-size: 0.8rem;
  color: #a0aec0;
}

.filter-select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  font-size: 0.9rem;
  color: #4a5568;
  cursor: pointer;
  outline: none;
}

.filter-select:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filter-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.size-chip {
  padding: 0.5rem 0.75rem;
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  font-size: 0.85rem;
  color: #4a5568;
  cursor: pointer;
  transition: all 0.2s;
}

.size-chip:hover {
  border-color: #cbd5e0;
}

.size-chip.active {
  background: #667eea;
  border-color: #667eea;
  color: white;
}

.color-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.color-option {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s;
}

.color-option:hover {
  transform: scale(1.1);
}

.color-option.active {
  border-color: #2d3748;
  box-shadow: 0 0 0 2px white, 0 0 0 4px #667eea;
}

.clear-filters {
  width: 100%;
  padding: 0.75rem;
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  color: #718096;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.clear-filters:hover {
  background: #edf2f7;
  border-color: #cbd5e0;
}

.add-product-card {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.add-product-card h3 {
  color: white;
}

.add-product-btn {
  width: 100%;
  padding: 0.875rem;
  background: rgba(255, 255, 255, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 6px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  backdrop-filter: blur(10px);
}

.add-product-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Product Grid */
.product-grid-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
}

.grid-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.grid-header h2 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2d3748;
  margin: 0;
}

.product-count {
  color: #718096;
  font-weight: 400;
}

.sort-options {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sort-options span {
  font-size: 0.9rem;
  color: #718096;
}

.sort-select {
  padding: 0.5rem 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  background: white;
  font-size: 0.9rem;
  color: #4a5568;
  cursor: pointer;
  outline: none;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  }
}

.product-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  overflow: hidden;
  transition: all 0.3s ease;
  position: relative;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.product-card.in-request {
  border-color: #667eea;
  border-width: 2px;
}

.product-image {
  position: relative;
  height: 200px;
  background: #f7fafc;
  overflow: hidden;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #cbd5e0;
}

.category-badge {
  position: absolute;
  top: 1rem;
  left: 1rem;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.cat-paint { background: #4299e1; }
.cat-coatings { background: #48bb78; }
.cat-solvents { background: #ed8936; }
.cat-tools { background: #9f7aea; }
.cat-packaging { background: #38b2ac; }

.color-preview {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.product-info {
  padding: 1.25rem;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.75rem;
}

.product-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  flex: 1;
  line-height: 1.4;
}

.product-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #38a169;
  margin-left: 0.5rem;
}

.product-meta {
  display: flex;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
}

.product-type,
.product-size {
  font-size: 0.85rem;
  padding: 0.25rem 0.5rem;
  background: #f7fafc;
  border-radius: 4px;
  color: #718096;
}

.product-description {
  font-size: 0.9rem;
  color: #718096;
  line-height: 1.5;
  margin-bottom: 1rem;
  min-height: 40px;
}

.product-actions {
  display: flex;
  gap: 0.75rem;
}

.request-btn,
.details-btn {
  flex: 1;
  padding: 0.75rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.request-btn {
  background: #edf2f7;
  color: #4a5568;
}

.request-btn:hover {
  background: #e2e8f0;
}

.request-btn.in-cart {
  background: #48bb78;
  color: white;
}

.request-btn.in-cart:hover {
  background: #38a169;
}

.details-btn {
  background: #667eea;
  color: white;
}

.details-btn:hover {
  background: #5a67d8;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  color: #a0aec0;
}

.empty-state svg {
  margin-bottom: 1rem;
  color: #e2e8f0;
}

.empty-state h3 {
  font-size: 1.25rem;
  color: #718096;
  margin: 0 0 0.5rem 0;
}

.empty-state p {
  margin: 0;
}

/* Cart Sidebar */
.cart-sidebar {
  position: fixed;
  top: 0;
  right: 0;
  width: 380px;
  height: 100vh;
  background: white;
  box-shadow: -5px 0 25px rgba(0, 0, 0, 0.1);
  transform: translateX(100%);
  transition: transform 0.3s ease;
  z-index: 1000;
  display: flex;
  flex-direction: column;
}

.cart-sidebar.active {
  transform: translateX(0);
}

.cart-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-header h3 {
  font-size: 1.2rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-cart {
  background: none;
  border: none;
  color: #718096;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: background 0.2s;
}

.close-cart:hover {
  background: #f7fafc;
}

.cart-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.empty-cart {
  text-align: center;
  padding: 3rem 1rem;
  color: #a0aec0;
}

.empty-cart svg {
  margin-bottom: 1rem;
  color: #e2e8f0;
}

.empty-cart p {
  margin: 0.5rem 0;
}

.hint {
  font-size: 0.9rem;
  color: #cbd5e0;
}

.cart-items {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.cart-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f7fafc;
  border-radius: 8px;
  position: relative;
}

.cart-item-image {
  width: 60px;
  height: 60px;
  border-radius: 6px;
  overflow: hidden;
  flex-shrink: 0;
}

.cart-item-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-placeholder {
  width: 100%;
  height: 100%;
  background: #e2e8f0;
}

.cart-item-info {
  flex: 1;
  min-width: 0;
}

.cart-item-info h4 {
  font-size: 0.95rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0 0 0.25rem 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-meta {
  display: flex;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: #718096;
  margin-bottom: 0.25rem;
}

.item-price {
  font-weight: 700;
  color: #38a169;
  font-size: 0.95rem;
}

.remove-item {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 24px;
  height: 24px;
  border-radius: 4px;
  background: rgba(229, 62, 62, 0.1);
  border: none;
  color: #e53e3e;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}

.remove-item:hover {
  background: rgba(229, 62, 62, 0.2);
}

.cart-summary {
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e2e8f0;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  color: #4a5568;
}

.summary-row.total {
  font-size: 1.1rem;
  font-weight: 700;
  color: #2d3748;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.cart-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.btn-clear,
.btn-submit {
  flex: 1;
  padding: 0.875rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-clear {
  background: #fed7d7;
  color: #c53030;
}

.btn-clear:hover {
  background: #feb2b2;
}

.btn-submit {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

.cart-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 999;
  backdrop-filter: blur(3px);
}

/* Add Product Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1rem;
  backdrop-filter: blur(3px);
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  padding: 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  font-size: 1.5rem;
  font-weight: 600;
  color: #2d3748;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.close-modal {
  background: none;
  border: none;
  color: #718096;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  transition: background 0.2s;
}

.close-modal:hover {
  background: #f7fafc;
}

.add-product-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 0.5rem;
}

.form-input,
.form-select,
.form-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 6px;
  font-size: 0.95rem;
  color: #4a5568;
  background: white;
  transition: all 0.3s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
  font-family: inherit;
}

.form-input[type="number"] {
  -moz-appearance: textfield;
}

.form-input[type="number"]::-webkit-outer-spin-button,
.form-input[type="number"]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.color-input-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.color-input-group input {
  flex: 1;
}

.color-preview-display {
  width: 40px;
  height: 40px;
  border-radius: 6px;
  border: 2px solid #e2e8f0;
  flex-shrink: 0;
}

.image-upload {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.file-input {
  display: none;
}

.upload-label {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 20px;
  background: #f8f9fa;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
  color: #495057;
  font-weight: 500;
}

.upload-label:hover {
  background: #e9ecef;
  border-color: #667eea;
}

.image-preview {
  position: relative;
  width: 120px;
  height: 120px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e0e0e0;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: background 0.3s;
}

.remove-image:hover {
  background: rgba(0, 0, 0, 0.9);
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e2e8f0;
}

.btn-cancel,
.btn-submit {
  flex: 1;
  padding: 0.875rem;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-cancel {
  background: #f7fafc;
  color: #4a5568;
  border: 1px solid #e2e8f0;
}

.btn-cancel:hover {
  background: #edf2f7;
}

.btn-submit {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .header-content {
    flex-direction: column;
    text-align: center;
    padding: 1rem;
  }
  
  .search-container {
    min-width: 100%;
  }
  
  .main-content {
    padding: 0 1rem;
  }
  
  .cart-sidebar {
    width: 100%;
  }
  
  .product-grid {
    grid-template-columns: 1fr;
  }
  
  .product-actions {
    flex-direction: column;
  }
  
  .modal-content {
    margin: 1rem;
    max-height: 85vh;
  }
}
</style>