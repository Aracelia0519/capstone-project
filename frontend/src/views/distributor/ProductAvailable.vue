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
            <h1>{{ distributorName }}</h1>
          </div>
          <p class="tagline">Available Products Catalog</p>
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
          
          <div class="catalog-stats">
            <div class="stat-item">
              <span class="stat-label">Total Products</span>
              <span class="stat-value">{{ totalProducts }}</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">Categories</span>
              <span class="stat-value">{{ uniqueCategories }}</span>
            </div>
          </div>
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
                v-for="size in allSizes" 
                :key="size"
                @click="toggleSize(size)"
                :class="['size-chip', { active: selectedSizes.includes(size) }]"
              >
                {{ size }}
              </button>
            </div>
          </div>
          
          <div class="filter-group" v-if="selectedCategories.includes('Paint Products') || selectedCategories.includes('Spray Paints')">
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
            Product Management
          </h3>
          <button @click="showAddModal = true" class="add-product-btn">
            Add New Product
          </button>
          <button @click="exportCatalog" class="export-btn">
            Export Catalog
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
          >
            <div class="product-image">
              <img v-if="product.image_url" 
                   :src="getFullImageUrl(product.image_url)" 
                   :alt="product.name"
                   @error="handleImageError"
                   class="product-img">
              <div v-else class="image-placeholder" :style="{ backgroundColor: product.color_code || '#f0f0f0' }">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                  <circle cx="8.5" cy="8.5" r="1.5"></circle>
                  <polyline points="21 15 16 10 5 21"></polyline>
                </svg>
              </div>
              <span class="category-badge" :class="getCategoryClass(product.category)">
                {{ getCategoryShortName(product.category) }}
              </span>
              <button 
                v-if="product.color_code" 
                class="color-preview"
                :style="{ backgroundColor: product.color_code }"
                :title="product.color_code"
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
                <span v-if="product.sku_code" class="product-sku">SKU: {{ product.sku_code }}</span>
              </div>
              
              <p v-if="product.description" class="product-description">
                {{ truncateDescription(product.description) }}
              </p>
              
              <div class="product-specs">
                <div class="spec-item">
                  <span class="spec-label">Reference Stock:</span>
                  <span class="spec-value">Min: {{ product.min_stock_level || 'N/A' }} | Max: {{ product.max_stock_level || 'N/A' }}</span>
                </div>
                <div v-if="product.cost" class="spec-item">
                  <span class="spec-label">Cost Price:</span>
                  <span class="spec-value">₱{{ formatPrice(product.cost) }}</span>
                </div>
                <div v-if="product.category" class="spec-item">
                  <span class="spec-label">Packaging:</span>
                  <span class="spec-value">{{ getDefaultPackaging(product.category) }}</span>
                </div>
              </div>
              
              <div class="product-actions">
                <button @click="editProduct(product)" class="edit-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                  </svg>
                  Edit
                </button>
                
                <button @click="deleteProduct(product.id)" class="delete-btn">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18"></path>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                  </svg>
                  Delete
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
    </main>

    <!-- Add/Edit Product Modal with Wizard -->
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
        
        <!-- Wizard Progress -->
        <div class="wizard-progress">
          <div 
            v-for="(step, index) in wizardSteps" 
            :key="index"
            :class="['wizard-step', 
                     { 'active': currentStep === index + 1, 
                       'completed': currentStep > index + 1 }]"
          >
            <div class="wizard-step-circle">
              <span v-if="currentStep <= index + 1">{{ index + 1 }}</span>
              <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </div>
            <span class="wizard-step-label">{{ step.label }}</span>
          </div>
        </div>
        
        <!-- Step Indicator for Mobile -->
        <div class="wizard-step-indicator">
          Step {{ currentStep }} of {{ wizardSteps.length }}: {{ wizardSteps[currentStep - 1].label }}
        </div>
        
        <form @submit.prevent="isEditing ? updateProduct() : addProduct()" class="add-product-form" enctype="multipart/form-data">
          <div class="wizard-form-content">
            
            <!-- Step 1: Product Basics -->
            <div v-if="currentStep === 1" class="wizard-form-step">
              <div class="wizard-form-row">
                <div class="wizard-form-group">
                  <label for="category">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                      <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    Category <span class="required">*</span>
                  </label>
                  <select id="category" v-model="newProduct.category" required @change="onCategoryChange" class="form-select">
                    <option value="">Select Category</option>
                    <optgroup label="🎨 PAINT PRODUCTS">
                      <option value="Interior Paints">🏠 Interior Paints</option>
                      <option value="Exterior Paints">🌦️ Exterior Paints</option>
                      <option value="Industrial & Protective Paints">🏭 Industrial & Protective Paints</option>
                      <option value="Specialty Paints">🎭 Specialty Paints</option>
                      <option value="Spray Paints">🎯 Spray Paints</option>
                    </optgroup>
                    <optgroup label="🧪 COATINGS & CHEMICALS">
                      <option value="Primers & Sealers">🧴 Primers & Sealers</option>
                    </optgroup>
                    <optgroup label="🛢️ SOLVENTS & THINNERS">
                      <option value="Solvents & Thinners">🛢️ Solvents & Thinners</option>
                    </optgroup>
                    <optgroup label="🧰 PAINTING TOOLS & ACCESSORIES">
                      <option value="Application Tools">🎨 Application Tools</option>
                      <option value="Surface Preparation">🧱 Surface Preparation</option>
                      <option value="Safety Equipment">🦺 Safety Equipment</option>
                    </optgroup>
                    <optgroup label="📦 PACKAGING & CONTAINERS">
                      <option value="Packaging & Containers">📦 Packaging & Containers</option>
                    </optgroup>
                  </select>
                  <div class="wizard-step-validation" :class="{ 'hidden': newProduct.category }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Please select a category
                  </div>
                </div>

                <div class="wizard-form-group">
                  <label for="type">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                      <polyline points="2 17 12 22 22 17"></polyline>
                      <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                    Type <span class="required">*</span>
                  </label>
                  <select id="type" v-model="newProduct.type" required :disabled="!newProduct.category" class="form-select">
                    <option value="">Select Type</option>
                    <option v-for="type in filteredTypes" :key="type" :value="type">{{ type }}</option>
                  </select>
                  <div class="wizard-step-validation" :class="{ 'hidden': newProduct.type }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Please select a type
                  </div>
                </div>
              </div>

              <div class="wizard-form-group wizard-form-full">
                <label for="name">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                  </svg>
                  Product Name <span class="required">*</span>
                </label>
                <input type="text" id="name" v-model="newProduct.name" placeholder="Enter product name" required class="form-input">
                <div class="wizard-step-validation" :class="{ 'hidden': newProduct.name && newProduct.name.trim() }">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                  Please enter a product name
                </div>
              </div>

              <div class="wizard-form-row">
                <div class="wizard-form-group">
                  <label for="sku">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                      <path d="M9 9h6v6H9z"></path>
                    </svg>
                    SKU Code
                  </label>
                  <input type="text" id="sku" v-model="newProduct.sku_code" placeholder="Enter SKU (optional)" class="form-input">
                </div>

                <div class="wizard-form-group">
                  <label for="size">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="9" y1="9" x2="15" y2="15"></line>
                      <line x1="15" y1="9" x2="9" y2="15"></line>
                    </svg>
                    Size <span class="required">*</span>
                  </label>
                  <select id="size" v-model="newProduct.size" required class="form-select">
                    <option value="">Select Size</option>
                    <optgroup v-if="newProduct.category.includes('Paint')" label="Paint Sizes">
                      <option v-for="size in paintSizes" :key="size" :value="size">{{ size }}</option>
                    </optgroup>
                    <optgroup v-if="newProduct.category.includes('Spray')" label="Spray Sizes">
                      <option v-for="size in spraySizes" :key="size" :value="size">{{ size }}</option>
                    </optgroup>
                    <optgroup v-if="newProduct.category.includes('Solvents')" label="Solvent Sizes">
                      <option v-for="size in solventSizes" :key="size" :value="size">{{ size }}</option>
                    </optgroup>
                    <optgroup v-if="newProduct.category.includes('Tools') || newProduct.category.includes('Safety')" label="Tool Sizes">
                      <option v-for="size in toolSizes" :key="size" :value="size">{{ size }}</option>
                    </optgroup>
                    <optgroup v-if="newProduct.category.includes('Packaging')" label="Packaging Sizes">
                      <option v-for="size in packagingSizes" :key="size" :value="size">{{ size }}</option>
                    </optgroup>
                    <optgroup v-if="newProduct.category.includes('Sandpaper')" label="Sandpaper Grits">
                      <option v-for="size in sandpaperGrits" :key="size" :value="size">{{ size }}</option>
                    </optgroup>
                  </select>
                  <div class="wizard-step-validation" :class="{ 'hidden': newProduct.size }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Please select a size
                  </div>
                </div>
              </div>

              <div class="wizard-form-group" v-if="showColorField">
                <label for="color_code">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  Color (Hex)
                </label>
                <div class="color-input-group">
                  <input type="text" id="color_code" v-model="newProduct.color_code" placeholder="#FFFFFF" maxlength="20" class="form-input">
                  <div class="color-preview-display" :style="{ backgroundColor: newProduct.color_code || 'transparent' }"></div>
                </div>
              </div>
            </div>

            <!-- Step 2: Pricing & Inventory -->
            <div v-else-if="currentStep === 2" class="wizard-form-step">
              <div class="wizard-form-row">
                <div class="wizard-form-group">
                  <label for="price">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <line x1="12" y1="1" x2="12" y2="23"></line>
                      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    Selling Price (₱) <span class="required">*</span>
                  </label>
                  <input type="number" id="price" v-model="newProduct.price" placeholder="0.00" min="0" step="0.01" required class="form-input">
                  <div class="wizard-step-validation" :class="{ 'hidden': newProduct.price && parseFloat(newProduct.price) > 0 }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="12" y1="8" x2="12" y2="12"></line>
                      <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Please enter a valid price
                  </div>
                </div>

                <div class="wizard-form-group">
                  <label for="cost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <line x1="12" y1="1" x2="12" y2="23"></line>
                      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    Cost Price (₱)
                  </label>
                  <input type="number" id="cost" v-model="newProduct.cost" placeholder="0.00" min="0" step="0.01" class="form-input">
                </div>
              </div>

              <div class="wizard-form-row">
                <div class="wizard-form-group">
                  <label for="min_stock_level">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <path d="M6 9l6 6 6-6"></path>
                    </svg>
                    Min Reference Stock
                  </label>
                  <input type="number" id="min_stock_level" v-model="newProduct.min_stock_level" placeholder="10" min="0" class="form-input">
                </div>

                <div class="wizard-form-group">
                  <label for="max_stock_level">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                      <path d="M18 15l-6-6-6 6"></path>
                    </svg>
                    Max Reference Stock
                  </label>
                  <input type="number" id="max_stock_level" v-model="newProduct.max_stock_level" placeholder="100" min="0" class="form-input">
                </div>
              </div>

              <div class="wizard-form-group wizard-form-full">
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
            </div>

            <!-- Step 3: Image Upload -->
            <div v-else-if="currentStep === 3" class="wizard-form-step">
              <div class="wizard-form-group wizard-form-full">
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
                  <div @click="triggerFileInput"
                    @dragover.prevent @drop.prevent="handleFileDrop"
                    class="file-upload-area cursor-pointer flex flex-col items-center justify-center px-6 pt-8 pb-8 border-2 border-dashed rounded-xl transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 hover:scale-[1.02]"
                    :class="imagePreview || newProduct.image_url ? 'border-green-300 bg-green-50' : 'border-gray-300'">
                    <svg class="h-12 w-12 mb-4" :class="imagePreview || newProduct.image_url ? 'text-green-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <div class="text-center">
                      <p class="text-sm font-medium text-gray-700">
                        <span class="text-blue-600 hover:text-blue-500">Click to upload image</span>
                        <span class="text-gray-500 ml-1">or drag and drop</span>
                      </p>
                      <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG up to 2MB</p>
                      <p v-if="imagePreview || newProduct.image_url" class="text-sm text-green-600 font-medium mt-2">
                        ✓ Image selected
                      </p>
                    </div>
                  </div>
                  
                  <div v-if="imagePreview || newProduct.image_url" class="image-preview mt-4">
                    <img :src="imagePreview || getFullImageUrl(newProduct.image_url)" alt="Preview">
                    <button type="button" @click="removeImage" class="remove-image">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              
              <div class="wizard-form-group wizard-form-full">
                <p class="text-sm text-gray-600">
                  <strong>Note:</strong> Image is optional. If no image is provided, a colored placeholder will be shown based on the product color or a default gray background.
                </p>
              </div>
            </div>

            <!-- Step 4: Review & Submit -->
            <div v-else-if="currentStep === 4" class="wizard-form-step">
              <div class="wizard-summary">
                <h4 class="text-lg font-semibold mb-4 text-gray-800">Product Summary</h4>
                
                <div class="wizard-summary-item">
                  <div class="wizard-summary-label">Product Name:</div>
                  <div class="wizard-summary-value">{{ newProduct.name || 'Not provided' }}</div>
                </div>
                
                <div class="wizard-summary-item">
                  <div class="wizard-summary-label">Category:</div>
                  <div class="wizard-summary-value">{{ newProduct.category || 'Not provided' }}</div>
                </div>
                
                <div class="wizard-summary-item">
                  <div class="wizard-summary-label">Type:</div>
                  <div class="wizard-summary-value">{{ newProduct.type || 'Not provided' }}</div>
                </div>
                
                <div class="wizard-summary-item">
                  <div class="wizard-summary-label">Size:</div>
                  <div class="wizard-summary-value">{{ newProduct.size || 'Not provided' }}</div>
                </div>
                
                <div class="wizard-summary-item">
                  <div class="wizard-summary-label">SKU Code:</div>
                  <div class="wizard-summary-value">{{ newProduct.sku_code || 'Not provided' }}</div>
                </div>
                
                <div v-if="newProduct.color_code" class="wizard-summary-item">
                  <div class="wizard-summary-label">Color:</div>
                  <div class="wizard-summary-value">
                    <div class="flex items-center gap-2">
                      <div class="w--4 h-4 rounded-full border" :style="{ backgroundColor: newProduct.color_code }"></div>
                      {{ newProduct.color_code }}
                    </div>
                  </div>
                </div>
                
                <div class="wizard-summary-item">
                  <div class="wizard-summary-label">Selling Price:</div>
                  <div class="wizard-summary-value font-semibold">₱{{ formatPrice(newProduct.price) || '0.00' }}</div>
                </div>
                
                <div v-if="newProduct.cost" class="wizard-summary-item">
                  <div class="wizard-summary-label">Cost Price:</div>
                  <div class="wizard-summary-value">₱{{ formatPrice(newProduct.cost) }}</div>
                </div>
                
                <div class="wizard-summary-item">
                  <div class="wizard-summary-label">Reference Stock:</div>
                  <div class="wizard-summary-value">
                    Min: {{ newProduct.min_stock_level || '10' }} | Max: {{ newProduct.max_stock_level || '100' }}
                  </div>
                </div>
                
                <div v-if="newProduct.description" class="wizard-summary-item">
                  <div class="wizard-summary-label">Description:</div>
                  <div class="wizard-summary-value">{{ newProduct.description }}</div>
                </div>
                
                <div v-if="imagePreview || newProduct.image_url" class="wizard-summary-image">
                  <p class="text-sm font-medium text-gray-700 mb-2">Product Image:</p>
                  <img :src="imagePreview || getFullImageUrl(newProduct.image_url)" alt="Product Image Preview">
                </div>
              </div>
              
              <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <h5 class="font-semibold text-blue-800 mb-2">Ready to {{ isEditing ? 'update' : 'add' }} this product?</h5>
                <p class="text-sm text-blue-700">
                  Review all the information above. If everything looks correct, click the "{{ isEditing ? 'Update Product' : 'Add Product' }}" button below.
                  To make changes, use the Previous button or click on any step in the progress bar.
                </p>
              </div>
            </div>
          </div>

          <!-- Wizard Navigation -->
          <div class="wizard-form-actions">
            <button 
              type="button" 
              @click="prevStep" 
              class="wizard-btn-prev"
              :disabled="currentStep === 1"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
              </svg>
              Previous
            </button>
            
            <button 
              v-if="currentStep < wizardSteps.length"
              type="button" 
              @click="nextStep" 
              class="wizard-btn-next"
              :disabled="!validateCurrentStep"
            >
              Next
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
            
            <button 
              v-else
              type="submit" 
              class="wizard-btn-submit"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon">
                <path d="M19 21H5a2 2 0 0 1-2 2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
              </svg>
              {{ isEditing ? 'Update Product' : 'Add Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/utils/axios'
import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

export default {
  name: 'DistributorProductsCatalog',
  data() {
    return {
      searchQuery: '',
      selectedCategories: [],
      selectedType: '',
      selectedSizes: [],
      selectedColor: '',
      sortOption: 'newest',
      showAddModal: false,
      isEditing: false,
      editingId: null,
      imagePreview: '',
      uploadedImage: null,
      distributorName: 'CaviteGo Distributor',
      
      // Wizard state
      currentStep: 1,
      wizardSteps: [
        { label: 'Product Basics', completed: false },
        { label: 'Pricing & Inventory', completed: false },
        { label: 'Image Upload', completed: false },
        { label: 'Review & Submit', completed: false }
      ],
      
      newProduct: {
        category: '',
        type: '',
        name: '',
        sku_code: '',
        size: '',
        color_code: '',
        price: '',
        cost: '',
        min_stock_level: 10,
        max_stock_level: 100,
        description: '',
        image_url: ''
      },
      
      products: [],
      
      // Comprehensive Product Types based on your data
      productTypes: {
        'Interior Paints': [
          'Latex / Acrylic', 'Water-based', 'Low-VOC', 'Anti-mold', 'Washable interior paint'
        ],
        'Exterior Paints': [
          'Weather-resistant', 'Waterproof', 'UV-resistant', 'Elastomeric'
        ],
        'Industrial & Protective Paints': [
          'Epoxy (Part A & Part B)', 'Enamel', 'Anti-rust', 'Heat-resistant', 'Chemical-resistant coating'
        ],
        'Specialty Paints': [
          'Chalk paint', 'Textured paint', 'Metallic paint', 'Fire-retardant', 'Anti-graffiti'
        ],
        'Spray Paints': [
          'General spray paint', 'Decorative spray paint', 'Industrial spray paint', 'Protective spray coating'
        ],
        'Primers & Sealers': [
          'Wall primer', 'Metal primer', 'Wood primer', 'Concrete sealer', 'Varnish',
          'Lacquer', 'Clear coat', 'Waterproofing solution', 'Paint additives'
        ],
        'Solvents & Thinners': [
          'Paint thinner', 'Mineral spirits', 'Turpentine', 'Degreasers', 'Cleaning solvents'
        ],
        'Application Tools': [
          'Paint Brushes', 'Paint Rollers', 'Roller Covers', 'Spray Guns',
          'Paint trays', 'Mixing sticks'
        ],
        'Surface Preparation': [
          'Sandpaper', 'Scrapers', 'Putty knives', 'Wire brushes'
        ],
        'Safety Equipment': [
          'Gloves', 'Face masks', 'Respirators', 'Safety goggles', 'Coveralls'
        ],
        'Packaging & Containers': [
          'Paint cans', 'Plastic buckets', 'Steel drums', 'Spray cans', 'Mixing containers'
        ]
      },
      
      // Size options by category
      paintSizes: ['250 ml', '500 ml', '1 Liter', '4 Liters', '10 Liters', '16 Liters', '20 Liters'],
      spraySizes: ['200 ml', '300 ml', '400 ml'],
      solventSizes: ['250 ml', '500 ml', '1 Liter', '4 Liters', '20 Liters'],
      toolSizes: ['1 inch', '1.5 inch', '2 inch', '2.5 inch', '3 inch', '4 inch', 'Small', 'Medium', 'Large', '4"', '6"', '7"', '9"', '12"'],
      packagingSizes: ['250 ml', '500 ml', '1 Liter', '4 Liters', '10 Liters', '16 Liters', '20 Liters', '200 Liters'],
      sandpaperGrits: ['80 grit', '120 grit', '180 grit', '220 grit', '320 grit', '400 grit'],
      
      categories: [
        { value: 'Interior Paints', label: 'Interior Paints', class: 'cat-interior', count: 0 },
        { value: 'Exterior Paints', label: 'Exterior Paints', class: 'cat-exterior', count: 0 },
        { value: 'Industrial & Protective Paints', label: 'Industrial Paints', class: 'cat-industrial', count: 0 },
        { value: 'Specialty Paints', label: 'Specialty Paints', class: 'cat-specialty', count: 0 },
        { value: 'Spray Paints', label: 'Spray Paints', class: 'cat-spray', count: 0 },
        { value: 'Primers & Sealers', label: 'Primers & Sealers', class: 'cat-primers', count: 0 },
        { value: 'Solvents & Thinners', label: 'Solvents', class: 'cat-solvents', count: 0 },
        { value: 'Application Tools', label: 'Application Tools', class: 'cat-tools', count: 0 },
        { value: 'Surface Preparation', label: 'Surface Prep', class: 'cat-prep', count: 0 },
        { value: 'Safety Equipment', label: 'Safety Equipment', class: 'cat-safety', count: 0 },
        { value: 'Packaging & Containers', label: 'Packaging', class: 'cat-packaging', count: 0 }
      ],
      
      // Packaging options
      packagingOptions: {
        'Interior Paints': 'Plastic pail / Metal can',
        'Exterior Paints': 'Plastic pail / Metal can',
        'Industrial & Protective Paints': 'Metal can / Steel pail',
        'Spray Paints': 'Aerosol can',
        'Solvents & Thinners': 'Plastic bottle / Metal container / Jerry can',
        'Packaging & Containers': 'Various containers'
      },
      
      colorOptions: [
        { name: 'White', value: '#FFFFFF' },
        { name: 'Beige', value: '#F5F5DC' },
        { name: 'Light Blue', value: '#B0C4DE' },
        { name: 'Gray', value: '#708090' },
        { name: 'Red', value: '#8B0000' },
        { name: 'Blue', value: '#0000FF' },
        { name: 'Green', value: '#008000' },
        { name: 'Yellow', value: '#FFFF00' },
        { name: 'Black', value: '#000000' },
        { name: 'Brown', value: '#8B4513' }
      ]
    };
  },
  computed: {
    availableTypes() {
      if (!Array.isArray(this.products)) {
        return [];
      }
      const types = new Set();
      this.products.forEach(product => {
        if (product && product.type) {
          types.add(product.type);
        }
      });
      return Array.from(types);
    },
    
    allSizes() {
      const all = [...this.paintSizes, ...this.spraySizes, ...this.solventSizes, ...this.toolSizes, ...this.packagingSizes, ...this.sandpaperGrits];
      return [...new Set(all)].sort();
    },
    
    filteredProducts() {
      if (!Array.isArray(this.products)) {
        return [];
      }
      
      let filtered = this.products;
      
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(product => {
          if (!product) return false;
          return (
            (product.name && product.name.toLowerCase().includes(query)) ||
            (product.type && product.type.toLowerCase().includes(query)) ||
            (product.description && product.description.toLowerCase().includes(query)) ||
            (product.sku_code && product.sku_code.toLowerCase().includes(query))
          );
        });
      }
      
      if (this.selectedCategories.length > 0) {
        filtered = filtered.filter(product => 
          product && product.category && this.selectedCategories.includes(product.category)
        );
      }
      
      if (this.selectedType) {
        filtered = filtered.filter(product => 
          product && product.type === this.selectedType
        );
      }
      
      if (this.selectedSizes.length > 0) {
        filtered = filtered.filter(product => 
          product && product.size && this.selectedSizes.includes(product.size)
        );
      }
      
      if (this.selectedColor && (this.selectedCategories.includes('Interior Paints') || 
                                 this.selectedCategories.includes('Exterior Paints') ||
                                 this.selectedCategories.includes('Specialty Paints'))) {
        filtered = filtered.filter(product => 
          product && product.color_code && product.color_code.toLowerCase() === this.selectedColor.toLowerCase()
        );
      }
      
      return filtered;
    },
    
    sortedProducts() {
      if (!Array.isArray(this.filteredProducts)) {
        return [];
      }
      
      const products = [...this.filteredProducts];
      
      switch (this.sortOption) {
        case 'name':
          return products.sort((a, b) => {
            if (!a.name || !b.name) return 0;
            return a.name.localeCompare(b.name);
          });
        case 'price_low':
          return products.sort((a, b) => (a.price || 0) - (b.price || 0));
        case 'price_high':
          return products.sort((a, b) => (b.price || 0) - (a.price || 0));
        case 'newest':
          return products.sort((a, b) => (b.id || 0) - (a.id || 0));
        default:
          return products;
      }
    },
    
    totalProducts() {
      return Array.isArray(this.products) ? this.products.length : 0;
    },
    
    uniqueCategories() {
      if (!Array.isArray(this.products)) {
        return 0;
      }
      const categories = new Set(this.products.map(p => p && p.category).filter(Boolean));
      return categories.size;
    },
    
    filteredTypes() {
      if (!this.newProduct.category) return [];
      return this.productTypes[this.newProduct.category] || [];
    },
    
    showColorField() {
      const paintCategories = ['Interior Paints', 'Exterior Paints', 'Specialty Paints', 'Spray Paints'];
      return paintCategories.includes(this.newProduct.category);
    },
    
    // Wizard validation
    validateCurrentStep() {
      switch (this.currentStep) {
        case 1:
          return this.newProduct.category && 
                 this.newProduct.type && 
                 this.newProduct.name && 
                 this.newProduct.name.trim() && 
                 this.newProduct.size;
        case 2:
          return this.newProduct.price && 
                 parseFloat(this.newProduct.price) > 0;
        case 3:
          // Image is optional, so always valid
          return true;
        case 4:
          // All previous validations passed
          return true;
        default:
          return false;
      }
    }
  },
  methods: {
    // Wizard navigation
    nextStep() {
      if (this.currentStep < this.wizardSteps.length && this.validateCurrentStep) {
        this.wizardSteps[this.currentStep - 1].completed = true;
        this.currentStep++;
      }
    },
    
    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },
    
    // Clean: No console messages
    getFullImageUrl(imageUrl) {
      if (!imageUrl) return '';
      return imageUrl;
    },
    
    handleImageError(event) {
      const img = event.target;
      img.style.display = 'none';
      
      const placeholder = img.parentElement.querySelector('.image-placeholder');
      if (placeholder) {
        placeholder.style.display = 'flex';
      }
    },
    
    getCategoryClass(category) {
      if (!category) return '';
      const classes = {
        'Interior Paints': 'cat-paint',
        'Exterior Paints': 'cat-exterior',
        'Industrial & Protective Paints': 'cat-industrial',
        'Specialty Paints': 'cat-specialty',
        'Spray Paints': 'cat-spray',
        'Primers & Sealers': 'cat-primers',
        'Solvents & Thinners': 'cat-solvents',
        'Application Tools': 'cat-tools',
        'Surface Preparation': 'cat-prep',
        'Safety Equipment': 'cat-safety',
        'Packaging & Containers': 'cat-packaging'
      };
      return classes[category] || 'cat-paint';
    },
    
    getCategoryShortName(category) {
      if (!category) return '';
      const shortNames = {
        'Interior Paints': 'Interior',
        'Exterior Paints': 'Exterior',
        'Industrial & Protective Paints': 'Industrial',
        'Specialty Paints': 'Specialty',
        'Spray Paints': 'Spray',
        'Primers & Sealers': 'Primer',
        'Solvents & Thinners': 'Solvent',
        'Application Tools': 'Tool',
        'Surface Preparation': 'Prep',
        'Safety Equipment': 'Safety',
        'Packaging & Containers': 'Packaging'
      };
      return shortNames[category] || category.split(' ')[0];
    },
    
    getDefaultPackaging(category) {
      return this.packagingOptions[category] || 'Standard packaging';
    },
    
    showSuccessToast(message) {
      Toastify({
        text: message,
        duration: 3000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #10b981, #059669)",
          color: "#ffffff",
          borderRadius: "8px",
          padding: "12px 16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(16, 185, 129, 0.2)"
        }
      }).showToast();
    },
    
    showErrorToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #ef4444, #dc2626)",
          color: "#ffffff",
          borderRadius: "8px",
          padding: "12px 16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(239, 68, 68, 0.2)"
        }
      }).showToast();
    },
    
    showWarningToast(message) {
      Toastify({
        text: message,
        duration: 4000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #f59e0b, #d97706)",
          color: "#ffffff",
          borderRadius: "8px",
          padding: "12px 16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(245, 158, 11, 0.2)"
        }
      }).showToast();
    },
    
    async loadProducts() {
      try {
        const response = await api.get('/distributor/products');
        
        if (response.data && response.data.success) {
          this.products = Array.isArray(response.data.data) ? response.data.data : [];
        } else {
          this.products = [];
        }
        this.updateCategoryCounts();
      } catch (error) {
        this.loadSampleData();
      }
    },
    
    loadSampleData() {
      this.products = [
        {
          id: 1,
          name: 'Premium Latex Paint - White',
          category: 'Interior Paints',
          type: 'Latex / Acrylic',
          sku_code: 'PLP-WH-001',
          size: '4 Liters',
          color_code: '#FFFFFF',
          price: 1250.00,
          cost: 850.00,
          min_stock_level: 10,
          max_stock_level: 100,
          description: 'High-quality interior latex paint, perfect for walls and ceilings.',
          image_url: ''
        },
        {
          id: 2,
          name: 'Weather-Resistant Exterior Paint',
          category: 'Exterior Paints',
          type: 'Weather-resistant',
          sku_code: 'WREP-BL-001',
          size: '20 Liters',
          color_code: '#0000FF',
          price: 3200.00,
          cost: 2200.00,
          min_stock_level: 5,
          max_stock_level: 50,
          description: 'Professional-grade waterproof coating for roofs and walls.',
          image_url: ''
        },
        {
          id: 3,
          name: 'Professional Paint Brush Set',
          category: 'Application Tools',
          type: 'Paint Brushes',
          sku_code: 'PBS-001',
          size: 'Set of 5',
          color_code: null,
          price: 450.00,
          cost: 250.00,
          min_stock_level: 20,
          max_stock_level: 200,
          description: 'Set of 5 professional paint brushes for various surfaces.',
          image_url: ''
        },
        {
          id: 4,
          name: 'Paint Thinner',
          category: 'Solvents & Thinners',
          type: 'Paint thinner',
          sku_code: 'PT-001',
          size: '1 Liter',
          color_code: null,
          price: 150.00,
          cost: 80.00,
          min_stock_level: 30,
          max_stock_level: 300,
          description: 'High-quality paint thinner for cleaning brushes and thinning paint.',
          image_url: ''
        },
        {
          id: 5,
          name: 'Safety Goggles',
          category: 'Safety Equipment',
          type: 'Safety goggles',
          sku_code: 'SG-001',
          size: 'Standard',
          color_code: null,
          price: 120.00,
          cost: 60.00,
          min_stock_level: 25,
          max_stock_level: 250,
          description: 'Protective safety goggles for painting and construction work.',
          image_url: ''
        }
      ];
      this.updateCategoryCounts();
    },
    
    toggleSize(size) {
      const index = this.selectedSizes.indexOf(size);
      if (index > -1) {
        this.selectedSizes.splice(index, 1);
      } else {
        this.selectedSizes.push(size);
      }
    },
    
    async addProduct() {
      if (!this.validateProduct()) return;
      
      try {
        const formData = new FormData();
        
        formData.append('category', this.newProduct.category);
        formData.append('type', this.newProduct.type);
        formData.append('name', this.newProduct.name);
        if (this.newProduct.sku_code) {
          formData.append('sku_code', this.newProduct.sku_code);
        }
        formData.append('size', this.newProduct.size);
        if (this.newProduct.color_code) {
          formData.append('color_code', this.newProduct.color_code);
        }
        formData.append('price', parseFloat(this.newProduct.price));
        if (this.newProduct.cost) {
          formData.append('cost', parseFloat(this.newProduct.cost));
        }
        if (this.newProduct.min_stock_level) {
          formData.append('min_stock_level', parseInt(this.newProduct.min_stock_level));
        }
        if (this.newProduct.max_stock_level) {
          formData.append('max_stock_level', parseInt(this.newProduct.max_stock_level));
        }
        if (this.newProduct.description) {
          formData.append('description', this.newProduct.description);
        }
        
        if (this.uploadedImage) {
          formData.append('image', this.uploadedImage);
        }
        
        const response = await api.post('/distributor/products', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data && response.data.success) {
          this.products.unshift(response.data.data);
          this.updateCategoryCounts();
          this.showSuccessToast('Product added successfully!');
          this.closeModal();
        } else {
          this.showErrorToast('Failed to add product. Please try again.');
        }
      } catch (error) {
        let errorMessage = 'Error adding product. Please try again.';
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          errorMessage = errors.join(', ');
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        }
        
        this.showErrorToast(errorMessage);
      }
    },
    
    editProduct(product) {
      this.isEditing = true;
      this.editingId = product.id;
      this.newProduct = { 
        category: product.category || '',
        type: product.type || '',
        name: product.name || '',
        sku_code: product.sku_code || '',
        size: product.size || '',
        color_code: product.color_code || '',
        price: product.price ? product.price.toString() : '',
        cost: product.cost ? product.cost.toString() : '',
        min_stock_level: product.min_stock_level ? product.min_stock_level.toString() : '10',
        max_stock_level: product.max_stock_level ? product.max_stock_level.toString() : '100',
        description: product.description || '',
        image_url: product.image_url || ''
      };
      this.imagePreview = product.image_url;
      this.uploadedImage = null;
      this.currentStep = 1;
      this.wizardSteps.forEach(step => step.completed = false);
      this.showAddModal = true;
    },
    
    async updateProduct() {
      if (!this.validateProduct()) return;
      
      try {
        const formData = new FormData();
        
        formData.append('category', this.newProduct.category);
        formData.append('type', this.newProduct.type);
        formData.append('name', this.newProduct.name);
        if (this.newProduct.sku_code) {
          formData.append('sku_code', this.newProduct.sku_code);
        }
        formData.append('size', this.newProduct.size);
        if (this.newProduct.color_code) {
          formData.append('color_code', this.newProduct.color_code);
        }
        formData.append('price', parseFloat(this.newProduct.price));
        if (this.newProduct.cost) {
          formData.append('cost', parseFloat(this.newProduct.cost));
        }
        if (this.newProduct.min_stock_level) {
          formData.append('min_stock_level', parseInt(this.newProduct.min_stock_level));
        }
        if (this.newProduct.max_stock_level) {
          formData.append('max_stock_level', parseInt(this.newProduct.max_stock_level));
        }
        if (this.newProduct.description) {
          formData.append('description', this.newProduct.description);
        }
        
        if (this.uploadedImage) {
          formData.append('image', this.uploadedImage);
        }
        
        const response = await api.put(`/distributor/products/${this.editingId}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        if (response.data && response.data.success) {
          const index = this.products.findIndex(p => p.id === this.editingId);
          if (index !== -1) {
            this.products[index] = response.data.data;
            this.updateCategoryCounts();
            this.showSuccessToast('Product updated successfully!');
            this.closeModal();
          }
        } else {
          this.showErrorToast('Failed to update product. Please try again.');
        }
      } catch (error) {
        let errorMessage = 'Error updating product. Please try again.';
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat();
          errorMessage = errors.join(', ');
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        }
        
        this.showErrorToast(errorMessage);
      }
    },
    
    async deleteProduct(productId) {
      if (!confirm('Are you sure you want to delete this product?')) return;
      
      try {
        const response = await api.delete(`/distributor/products/${productId}`);
        
        if (response.data && response.data.success) {
          this.products = this.products.filter(product => product.id !== productId);
          this.updateCategoryCounts();
          this.showSuccessToast('Product deleted successfully!');
        } else {
          this.showErrorToast('Failed to delete product. Please try again.');
        }
      } catch (error) {
        this.showErrorToast('Error deleting product. Please try again.');
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
      this.currentStep = 1;
      this.wizardSteps.forEach(step => step.completed = false);
      this.resetForm();
    },
    
    onCategoryChange() {
      this.newProduct.type = '';
      this.newProduct.size = '';
    },
    
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
    
    handleImageUpload(event) {
      const file = event.target.files[0];
      if (file) {
        this.processImageFile(file);
      }
    },
    
    handleFileDrop(event) {
      event.preventDefault();
      const file = event.dataTransfer.files[0];
      if (file) {
        this.processImageFile(file);
      }
    },
    
    processImageFile(file) {
      if (!file.type.startsWith('image/')) {
        this.showErrorToast('Please select an image file (JPG, PNG, etc.).');
        return;
      }
      
      if (file.size > 2 * 1024 * 1024) {
        this.showErrorToast('Image size should be less than 2MB. Please compress or choose a smaller image.');
        return;
      }
      
      this.uploadedImage = file;
      
      const reader = new FileReader();
      reader.onload = (e) => {
        this.imagePreview = e.target.result;
      };
      reader.readAsDataURL(file);
    },
    
    removeImage() {
      this.imagePreview = '';
      this.uploadedImage = null;
      this.newProduct.image_url = '';
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    
    resetForm() {
      this.newProduct = {
        category: '',
        type: '',
        name: '',
        sku_code: '',
        size: '',
        color_code: '',
        price: '',
        cost: '',
        min_stock_level: 10,
        max_stock_level: 100,
        description: '',
        image_url: ''
      };
      this.imagePreview = '';
      this.uploadedImage = null;
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },
    
    validateProduct() {
      if (!this.newProduct.category) {
        this.showWarningToast('Please select a category.');
        return false;
      }
      if (!this.newProduct.type) {
        this.showWarningToast('Please select a type.');
        return false;
      }
      if (!this.newProduct.name || !this.newProduct.name.trim()) {
        this.showWarningToast('Please enter a product name.');
        return false;
      }
      if (!this.newProduct.size) {
        this.showWarningToast('Please select a size.');
        return false;
      }
      if (!this.newProduct.price || parseFloat(this.newProduct.price) <= 0) {
        this.showWarningToast('Please enter a valid price.');
        return false;
      }
      if (this.newProduct.color_code && !this.newProduct.color_code.match(/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/)) {
        this.showWarningToast('Please enter a valid hex color code (e.g., #FFFFFF or #FFF).');
        return false;
      }
      return true;
    },
    
    updateCategoryCounts() {
      if (!Array.isArray(this.products)) {
        this.products = [];
      }
      
      this.categories = this.categories.map(category => {
        const count = this.products.filter(p => p && p.category === category.value).length;
        return { ...category, count };
      });
    },
    
    formatPrice(price) {
      if (!price) return '0.00';
      return parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    },
    
    truncateDescription(desc, length = 60) {
      if (!desc) return '';
      return desc.length > length ? desc.substring(0, length) + '...' : desc;
    },
    
    exportCatalog() {
      if (!Array.isArray(this.products) || this.products.length === 0) {
        this.showWarningToast('No products to export.');
        return;
      }
      
      const headers = ['Name', 'Category', 'Type', 'SKU', 'Size', 'Color', 'Selling Price', 'Cost Price', 'Min Ref Stock', 'Max Ref Stock', 'Description'];
      const csvContent = [
        headers.join(','),
        ...this.products.map(product => [
          `"${product.name || ''}"`,
          `"${product.category || ''}"`,
          `"${product.type || ''}"`,
          `"${product.sku_code || ''}"`,
          `"${product.size || ''}"`,
          `"${product.color_code || ''}"`,
          product.price || '0',
          product.cost || '0',
          product.min_stock_level || '0',
          product.max_stock_level || '0',
          `"${product.description || ''}"`
        ].join(','))
      ].join('\n');
      
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      const link = document.createElement('a');
      const url = URL.createObjectURL(blob);
      link.setAttribute('href', url);
      link.setAttribute('download', `product-catalog-${new Date().toISOString().split('T')[0]}.csv`);
      link.style.visibility = 'hidden';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      
      this.showSuccessToast('Catalog exported successfully!');
    }
  },
  mounted() {
    this.loadProducts();
  }
};
</script>

<style scoped>
  @import "../distributor/styles/ProductAvailable.css";
  
  /* Additional styles for catalog */
  .catalog-stats {
    display: flex;
    gap: 2rem;
    background: white;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  }
  
  .stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .stat-label {
    font-size: 0.85rem;
    color: #718096;
    margin-bottom: 0.25rem;
  }
  
  .stat-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: #2d3748;
  }
  
  .product-sku {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
    background: #f7fafc;
    border-radius: 4px;
    color: #718096;
  }
  
  .product-specs {
    margin: 1rem 0;
    padding: 0.75rem;
    background: #f8fafc;
    border-radius: 6px;
  }
  
  .spec-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
  }
  
  .spec-item:last-child {
    margin-bottom: 0;
  }
  
  .spec-label {
    color: #4a5568;
    font-weight: 500;
  }
  
  .spec-value {
    color: #2d3748;
  }
  
  .product-actions {
    display: flex;
    gap: 0.75rem;
  }
  
  .edit-btn, .delete-btn {
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
  
  .edit-btn {
    background: #667eea;
    color: white;
  }
  
  .edit-btn:hover {
    background: #5a67d8;
  }
  
  .delete-btn {
    background: #fed7d7;
    color: #c53030;
  }
  
  .delete-btn:hover {
    background: #feb2b2;
  }
  
  .export-btn {
    width: 100%;
    padding: 0.875rem;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    backdrop-filter: blur(10px);
    margin-top: 0.75rem;
  }
  
  .export-btn:hover {
    background: rgba(255, 255, 255, 0.2);
  }
  
  .form-row {
    display: flex;
    gap: 1rem;
  }
  
  .form-row .form-group {
    flex: 1;
  }
  
  /* File upload area styling */
  .file-upload-area {
    border: 2px dashed #e2e8f0;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.3s ease;
  }
  
  .file-upload-area:hover {
    border-color: #667eea;
    background: #edf2f7;
  }
  
  /* Product image styling */
  .product-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  
  /* New category badge colors */
  .cat-interior { background: #4299e1; }
  .cat-exterior { background: #48bb78; }
  .cat-industrial { background: #ed8936; }
  .cat-specialty { background: #9f7aea; }
  .cat-spray { background: #f56565; }
  .cat-primers { background: #38b2ac; }
  .cat-solvents { background: #ecc94b; }
  .cat-tools { background: #667eea; }
  .cat-prep { background: #ed64a6; }
  .cat-safety { background: #4fd1c7; }
  .cat-packaging { background: #a0aec0; }
  
  @media (max-width: 768px) {
    .catalog-stats {
      flex-direction: column;
      gap: 1rem;
      padding: 1rem;
    }
    
    .form-row {
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .product-actions {
      flex-direction: column;
    }
  }
</style>