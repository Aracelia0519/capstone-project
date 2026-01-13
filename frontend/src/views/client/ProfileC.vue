<template>
  <div class="profile-container">
    <!-- Notification Container -->
    <div class="notifications-container">
      <transition-group name="notification-slide">
        <div 
          v-for="notification in notifications" 
          :key="notification.id"
          class="notification"
          :class="notification.type"
          @click="removeNotification(notification.id)"
        >
          <div class="notification-icon">
            <svg v-if="notification.type === 'success'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="notification.type === 'error'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <svg v-else-if="notification.type === 'warning'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <svg v-else class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="notification-content">
            <div class="notification-title">
              {{ notification.type === 'success' ? 'Success' : 
                 notification.type === 'error' ? 'Error' : 
                 notification.type === 'warning' ? 'Warning' : 'Info' }}
            </div>
            <div class="notification-message">{{ notification.message }}</div>
          </div>
          <button class="notification-close">
            <svg class="close-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <div class="notification-progress" :style="{ animationDuration: notification.duration + 'ms' }"></div>
        </div>
      </transition-group>
    </div>

    <!-- Header Section -->
    <div class="header-section">
      <div class="header-content">
        <h1 class="title">
          <svg class="title-icon text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Account Management
        </h1>
        <p class="subtitle">Manage your personal information, security, and contact details</p>
      </div>
      <div class="header-actions">
        <div class="account-status">
          <div class="status-indicator" :class="{ active: user.status === 'active', pending: user.status === 'pending' }"></div>
          <span class="status-text">{{ user.status === 'active' ? 'Account Active' : user.status === 'pending' ? 'Pending Approval' : 'Inactive' }}</span>
        </div>
        <button class="save-btn" :disabled="!hasChanges || loading" @click="saveProfile">
          <svg class="save-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
          </svg>
          {{ loading ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="initialLoading" class="loading-container">
      <div class="loading-spinner"></div>
      <p>Loading profile data...</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="error-message">
      <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      {{ error }}
      <button @click="fetchUserProfile" class="retry-btn">Retry</button>
    </div>

    <!-- Main Content Grid (only show when loaded) -->
    <div v-if="!initialLoading && !error" class="content-grid">
      <!-- Profile Overview Card -->
      <div class="overview-card">
        <div class="overview-header">
          <div class="profile-avatar-wrapper">
            <div class="profile-avatar">
              <svg class="avatar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div class="avatar-status" :class="{ active: user.status === 'active', pending: user.status === 'pending' }"></div>
          </div>
          <div class="overview-info">
            <h2 class="profile-name">{{ user.full_name || `${user.first_name} ${user.last_name}` }}</h2>
            <p class="profile-role">{{ formatRole(user.role) }}</p>
            <div class="profile-meta">
              <div class="meta-item">
                <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Member since {{ formatDate(user.created_at) }}
              </div>
              <div class="meta-item">
                <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ user.email }}
              </div>
            </div>
          </div>
        </div>
        <div class="overview-stats">
          <div class="stat">
            <div class="stat-value">{{ stats.colorSelections }}</div>
            <div class="stat-label">Colors Saved</div>
          </div>
          <div class="stat">
            <div class="stat-value">{{ stats.serviceRequests }}</div>
            <div class="stat-label">Service Requests</div>
          </div>
          <div class="stat">
            <div class="stat-value">{{ stats.activeProjects }}</div>
            <div class="stat-label">Active Projects</div>
          </div>
        </div>
      </div>

      <!-- Combined Personal Information & Contact Details Card -->
      <div class="info-card combined-form">
        <div class="card-header">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
          </svg>
          <h3 class="card-title">Personal Information & Contact Details</h3>
        </div>
        
        <form class="combined-form-content" @submit.prevent="updatePersonalAndContactInfo">
          <!-- Personal Information Section -->
          <div class="form-section">
            <h4 class="section-title">Personal Information</h4>
            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">
                  <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  First Name
                </label>
                <input 
                  type="text" 
                  v-model="user.first_name"
                  class="form-input"
                  :class="{ 'error': validationErrors.first_name }"
                  placeholder="Enter your first name"
                  @input="markChanged"
                >
                <div v-if="validationErrors.first_name" class="error-message">
                  {{ validationErrors.first_name[0] }}
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  Last Name
                </label>
                <input 
                  type="text" 
                  v-model="user.last_name"
                  class="form-input"
                  :class="{ 'error': validationErrors.last_name }"
                  placeholder="Enter your last name"
                  @input="markChanged"
                >
                <div v-if="validationErrors.last_name" class="error-message">
                  {{ validationErrors.last_name[0] }}
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  Email Address
                </label>
                <input 
                  type="email" 
                  v-model="user.email"
                  class="form-input"
                  :class="{ 'error': validationErrors.email }"
                  placeholder="Enter your email"
                  @input="markChanged"
                >
                <div v-if="validationErrors.email" class="error-message">
                  {{ validationErrors.email[0] }}
                </div>
                <div class="form-hint">
                  <svg class="hint-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Used for login and notifications
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  Phone Number
                </label>
                <input 
                  type="tel" 
                  v-model="user.phone"
                  class="form-input"
                  :class="{ 'error': validationErrors.phone }"
                  placeholder="Enter your phone number"
                  @input="markChanged"
                >
                <div v-if="validationErrors.phone" class="error-message">
                  {{ validationErrors.phone[0] }}
                </div>
              </div>
              
              <div class="form-group">
                <label class="form-label">
                  <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  Address
                </label>
                <textarea 
                  v-model="user.address"
                  class="form-input"
                  :class="{ 'error': validationErrors.address }"
                  placeholder="Enter your complete address"
                  @input="markChanged"
                  rows="3"
                ></textarea>
                <div v-if="validationErrors.address" class="error-message">
                  {{ validationErrors.address[0] }}
                </div>
              </div>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" class="cancel-btn" @click="resetPersonalAndContactInfo" :disabled="loading">
              Cancel
            </button>
            <button type="submit" class="update-btn" :disabled="!hasChanges || loading">
              {{ loading ? 'Updating...' : 'Update Information' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Change Password Card -->
      <div class="info-card">
        <div class="card-header">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
          <h3 class="card-title">Change Password</h3>
        </div>
        
        <form class="password-form" @submit.prevent="changePassword">
          <div v-if="passwordError" class="error-message password-error">
            {{ passwordError }}
          </div>
          
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                Current Password
              </label>
              <div class="password-input-wrapper">
                <input 
                  :type="showCurrentPassword ? 'text' : 'password'"
                  v-model="password.current"
                  class="form-input"
                  :class="{ 'error': validationErrors.current_password }"
                  placeholder="Enter current password"
                  required
                >
                <button type="button" class="password-toggle" @click="togglePasswordVisibility('current')">
                  <svg class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="showCurrentPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <div v-if="validationErrors.current_password" class="error-message">
                {{ validationErrors.current_password[0] }}
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                New Password
              </label>
              <div class="password-input-wrapper">
                <input 
                  :type="showNewPassword ? 'text' : 'password'"
                  v-model="password.new"
                  class="form-input"
                  :class="{ 'error': validationErrors.password }"
                  placeholder="Enter new password"
                  required
                >
                <button type="button" class="password-toggle" @click="togglePasswordVisibility('new')">
                  <svg class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="showNewPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <div v-if="validationErrors.password" class="error-message">
                {{ validationErrors.password[0] }}
              </div>
              <div class="password-strength">
                <div class="strength-label">Password Strength:</div>
                <div class="strength-bars">
                  <div v-for="n in 5" :key="n" 
                       class="strength-bar" 
                       :class="{ 
                         'weak': n <= 2 && passwordStrength === 'weak',
                         'medium': n <= 3 && passwordStrength === 'medium',
                         'strong': n <= 4 && passwordStrength === 'strong',
                         'very-strong': n === 5 && passwordStrength === 'very-strong'
                       }">
                  </div>
                </div>
                <div class="strength-text">{{ passwordStrengthLabel }}</div>
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Confirm Password
              </label>
              <div class="password-input-wrapper">
                <input 
                  :type="showConfirmPassword ? 'text' : 'password'"
                  v-model="password.confirm"
                  class="form-input"
                  :class="{ 'error': passwordMismatch || validationErrors.password }"
                  placeholder="Confirm new password"
                  required
                >
                <button type="button" class="password-toggle" @click="togglePasswordVisibility('confirm')">
                  <svg class="toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="showConfirmPassword" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
              <div v-if="passwordMismatch" class="error-message">
                <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Passwords do not match
              </div>
            </div>
          </div>
          
          <div class="password-requirements">
            <h4 class="requirements-title">Password Requirements:</h4>
            <ul class="requirements-list">
              <li :class="{ 'met': password.new.length >= 8 }">
                <svg class="requirement-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="password.new.length >= 8" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                At least 8 characters
              </li>
              <li :class="{ 'met': /[A-Z]/.test(password.new) }">
                <svg class="requirement-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="/[A-Z]/.test(password.new)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                One uppercase letter
              </li>
              <li :class="{ 'met': /[0-9]/.test(password.new) }">
                <svg class="requirement-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="/[0-9]/.test(password.new)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                One number
              </li>
              <li :class="{ 'met': /[^A-Za-z0-9]/.test(password.new) }">
                <svg class="requirement-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="/[^A-Za-z0-9]/.test(password.new)" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                One special character
              </li>
            </ul>
          </div>
          
          <button type="submit" class="change-password-btn" :disabled="!canChangePassword || passwordLoading">
            {{ passwordLoading ? 'Changing...' : 'Change Password' }}
          </button>
        </form>
      </div>

      <!-- Identity Verification Card (NEW) -->
      <div class="info-card">
        <div class="card-header">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="card-title">Identity Verification</h3>
          <div class="verification-status" :class="idVerification.status">
            {{ idVerification.status === 'verified' ? 'Verified' : 
               idVerification.status === 'approved' ? 'Approved' :
               idVerification.status === 'pending' ? 'Pending Review' : 
               idVerification.status === 'rejected' ? 'Rejected' : 'Not Submitted' }}
          </div>
        </div>
        
        <form class="id-verification-form" @submit.prevent="submitIdVerification">
          <div v-if="idVerification.error" class="error-message">
            {{ idVerification.error }}
          </div>
          
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                ID Type
              </label>
              <div class="custom-select-wrapper">
                <select 
                  v-model="idVerification.idType"
                  class="custom-select"
                  :class="{ 'error': idVerification.errors.idType }"
                  @change="markIdChanged"
                  :disabled="idVerification.status === 'pending' || idVerification.status === 'approved'"
                >
                  <option value="" disabled>Select ID Type</option>
                  <option value="philid">Philippine National ID (PhilID/ePhilID)</option>
                  <option value="passport">Passport</option>
                  <option value="driver_license">Driver's License</option>
                  <option value="umid">UMID (SSS/GSIS)</option>
                  <option value="prc">PRC ID</option>
                  <option value="voter">Voter's ID</option>
                  <option value="postal">Postal ID</option>
                  <option value="philhealth">PhilHealth ID</option>
                  <option value="nbi">NBI Clearance</option>
                  <option value="senior_citizen">Senior Citizen ID</option>
                  <option value="other">Other Government ID</option>
                </select>
                <div class="select-arrow">
                  <svg class="arrow-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
              <div v-if="idVerification.errors.idType" class="error-message">
                {{ idVerification.errors.idType }}
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ID Number
              </label>
              <input 
                type="text" 
                v-model="idVerification.idNumber"
                class="form-input"
                :class="{ 'error': idVerification.errors.idNumber }"
                placeholder="Enter your ID number"
                @input="markIdChanged"
                :disabled="idVerification.status === 'pending' || idVerification.status === 'approved'"
              >
              <div v-if="idVerification.errors.idNumber" class="error-message">
                {{ idVerification.errors.idNumber }}
              </div>
            </div>
          </div>
          
          <!-- ID Photo Upload -->
          <div class="upload-section" v-if="idVerification.status !== 'pending' && idVerification.status !== 'approved'">
            <h4 class="section-title">
              <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              ID Photo Upload
            </h4>
            
            <div class="upload-requirements">
              <p class="requirements-text">Please upload a clear photo of your ID document.</p>
              <ul class="requirements-list">
                <li>
                  <svg class="requirement-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Clear, legible photo
                </li>
                <li>
                  <svg class="requirement-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  All four corners visible
                </li>
                <li>
                  <svg class="requirement-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Good lighting, no glare
                </li>
              </ul>
            </div>
            
            <div class="upload-area" 
                 @dragover.prevent="handleDragOver"
                 @dragleave.prevent="handleDragLeave"
                 @drop.prevent="handleFileDrop"
                 :class="{ 'drag-over': idVerification.isDragging }"
                 @click="triggerFileInput">
              <div v-if="!idVerification.idPhotoPreview" class="upload-placeholder">
                <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="upload-text">Drag & drop your ID photo here</p>
                <p class="upload-subtext">or click to browse files</p>
                <p class="upload-hint">Max file size: 5MB (JPG, PNG, PDF)</p>
              </div>
              
              <div v-else class="upload-preview">
                <img :src="idVerification.idPhotoPreview" alt="ID Preview" class="id-preview-image" />
                <div class="preview-overlay">
                  <button type="button" class="preview-remove" @click.stop="removeIdPhoto">
                    <svg class="remove-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              
              <input 
                type="file" 
                ref="fileInput"
                @change="handleFileSelect"
                accept=".jpg,.jpeg,.png,.pdf"
                class="file-input"
                hidden
              >
            </div>
            
            <div v-if="idVerification.errors.idPhoto" class="error-message upload-error">
              {{ idVerification.errors.idPhoto }}
            </div>
            
            <div v-if="idVerification.idPhoto" class="file-info">
              <svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              <div class="file-details">
                <p class="file-name">{{ idVerification.idPhoto.name }}</p>
                <p class="file-size">{{ formatFileSize(idVerification.idPhoto.size) }}</p>
              </div>
            </div>
          </div>
          
          <!-- Show uploaded photo preview if status is pending or approved -->
          <div v-if="(idVerification.status === 'pending' || idVerification.status === 'approved') && idVerification.idPhotoPreview" class="uploaded-photo-preview">
            <h4 class="section-title">
              <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              Uploaded ID Photo
            </h4>
            <div class="uploaded-preview">
              <img :src="idVerification.idPhotoPreview" alt="Uploaded ID" class="id-preview-image" />
              <div class="uploaded-info text-gray-800 dark:text-gray-200">
  <p><strong>ID Type:</strong> {{ getIDTypeName(idVerification.idType) }}</p>
  <p><strong>ID Number:</strong> {{ idVerification.idNumber }}</p>
  <p><strong>Status:</strong> 
    <span class="status-badge text-white" :class="idVerification.status">
      {{ idVerification.status === 'approved' ? 'Approved' : 'Pending Review' }}
    </span>
  </p>
  <p v-if="idVerification.submittedAt">
    <strong>Submitted:</strong> {{ formatDateTime(idVerification.submittedAt) }}
  </p>
</div>
            </div>
          </div>
          
          <div class="verification-note">
            <svg class="note-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>Your ID information will be securely stored and only used for verification purposes. Verification typically takes 1-2 business days.</p>
          </div>
          
          <div class="form-actions" v-if="idVerification.status !== 'pending' && idVerification.status !== 'approved'">
            <button type="button" class="cancel-btn" @click="resetIdVerification" :disabled="idVerification.loading">
              Cancel
            </button>
            <button type="submit" class="id-submit-btn" :disabled="!canSubmitId || idVerification.loading">
              <svg v-if="!idVerification.loading" class="submit-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ idVerification.loading ? 'Submitting...' : 'Submit for Verification' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { getCurrentUser, clearAuthData } from '@/utils/auth'
import axios from '@/utils/axios'

export default {
  name: 'ClientProfile',
  data() {
    return {
      // Notification system
      notifications: [],
      notificationId: 0,
      
      hasChanges: false,
      loading: false,
      initialLoading: true,
      passwordLoading: false,
      error: null,
      passwordError: null,
      validationErrors: {},
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      user: {
        id: null,
        first_name: '',
        last_name: '',
        full_name: '',
        email: '',
        phone: '',
        address: '',
        role: '',
        status: '',
        created_at: ''
      },
      originalUser: null,
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      stats: {
        colorSelections: 0,
        serviceRequests: 0,
        activeProjects: 0
      },
      // ID Verification Data (NEW)
      idVerification: {
        idType: '',
        idNumber: '',
        idPhoto: null,
        idPhotoPreview: '',
        status: 'not_submitted', // 'not_submitted', 'pending', 'approved', 'rejected'
        loading: false,
        error: null,
        errors: {},
        isDragging: false,
        originalData: null,
        submittedAt: null
      }
    }
  },
  computed: {
    passwordStrength() {
      const password = this.password.new
      if (password.length < 6) return 'weak'
      if (password.length < 8) return 'medium'
      if (password.length < 10) return 'strong'
      if (this.hasSpecialChars(password) && this.hasNumbers(password) && this.hasUpperCase(password)) {
        return 'very-strong'
      }
      return 'strong'
    },
    passwordStrengthLabel() {
      const labels = {
        'weak': 'Weak',
        'medium': 'Medium',
        'strong': 'Strong',
        'very-strong': 'Very Strong'
      }
      return labels[this.passwordStrength]
    },
    passwordMismatch() {
      return this.password.new && this.password.confirm && this.password.new !== this.password.confirm
    },
    canChangePassword() {
      return this.password.current && 
             this.password.new && 
             this.password.confirm && 
             !this.passwordMismatch &&
             this.password.new.length >= 8 &&
             /[A-Z]/.test(this.password.new) &&
             /[0-9]/.test(this.password.new) &&
             /[^A-Za-z0-9]/.test(this.password.new)
    },
    // ID Verification Computed Properties (NEW)
    canSubmitId() {
      const isFirstSubmission = !this.idVerification.originalData ||
        (
          !this.idVerification.originalData.idType &&
          !this.idVerification.originalData.idNumber
        )

      return this.idVerification.idType &&
            this.idVerification.idNumber &&
            this.idVerification.idPhoto &&
            (isFirstSubmission || this.hasIdChanges()) &&
            this.idVerification.status !== 'pending' &&
            this.idVerification.status !== 'approved'
    },
    hasIdChanges() {
      if (!this.idVerification.originalData) return false
      
      const current = JSON.stringify({
        idType: this.idVerification.idType,
        idNumber: this.idVerification.idNumber,
        // Compare file names if exists
        idPhotoName: this.idVerification.idPhoto ? this.idVerification.idPhoto.name : ''
      })
      
      const original = JSON.stringify({
        idType: this.idVerification.originalData.idType,
        idNumber: this.idVerification.originalData.idNumber,
        idPhotoName: this.idVerification.originalData.idPhoto ? this.idVerification.originalData.idPhoto.name : ''
      })
      
      return current !== original
    }
  },
  created() {
    this.fetchUserProfile()
  },
  methods: {
    // Notification System Methods
    showNotification(message, type = 'info', duration = 5000) {
      const id = this.notificationId++
      const notification = {
        id,
        message,
        type,
        duration
      }
      
      this.notifications.push(notification)
      
      // Auto-remove after duration
      setTimeout(() => {
        this.removeNotification(id)
      }, duration)
    },
    
    removeNotification(id) {
      this.notifications = this.notifications.filter(n => n.id !== id)
    },
    
    async fetchUserProfile() {
      this.initialLoading = true
      this.error = null
      try {
        // First check if user is authenticated
        const authUser = await getCurrentUser()
        if (!authUser) {
          throw new Error('Please login to view your profile')
        }

        // Fetch profile data from API - Use the auth/me endpoint
        const response = await axios.get('/auth/me')
        
        if (response.data.status === 'success') {
          this.user = response.data.user
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          
          // Load ID verification data
          await this.loadIdVerificationData()
          
          this.showNotification('Profile loaded successfully!', 'success')
        } else {
          throw new Error('Failed to load profile data')
        }
      } catch (error) {
        console.error('Error fetching profile:', error)
        this.error = error.response?.data?.message || error.message || 'Failed to load profile data'
        
        if (error.response?.status === 401) {
          clearAuthData()
          this.$router.push('/Landing/logIn')
          this.showNotification('Session expired. Please login again.', 'error')
        } else {
          this.showNotification(this.error, 'error')
        }
      } finally {
        this.initialLoading = false
      }
    },
    
    // ID Verification Methods (NEW)
    async loadIdVerificationData() {
      try {
        const response = await axios.get('/client/requirements')
        
        if (response.data.status === 'success') {
          if (response.data.id_verification) {
            const data = response.data.id_verification
            this.idVerification.idType = data.id_type || ''
            this.idVerification.idNumber = data.id_number || ''
            this.idVerification.status = data.status || 'not_submitted'
            this.idVerification.submittedAt = data.submitted_at || null
            
            // If there's a stored photo URL, set it as preview
            if (data.id_photo_url) {
              this.idVerification.idPhotoPreview = data.id_photo_url
            }
            
            // Set original data
            this.idVerification.originalData = {
              idType: data.id_type || '',
              idNumber: data.id_number || '',
              idPhoto: null
            }
          } else {
            // No verification data exists yet
            this.idVerification.originalData = {
              idType: '',
              idNumber: '',
              idPhoto: null
            }
          }
        }
      } catch (error) {
        console.error('Error loading ID verification data:', error)
        this.showNotification('Failed to load verification data', 'error')
      }
    },
    
    markIdChanged() {
      // This will trigger reactive updates for hasIdChanges
    },
    
    handleDragOver(event) {
      event.preventDefault()
      this.idVerification.isDragging = true
    },
    
    handleDragLeave(event) {
      event.preventDefault()
      this.idVerification.isDragging = false
    },
    
    handleFileDrop(event) {
      this.idVerification.isDragging = false
      const files = event.dataTransfer.files
      if (files.length > 0) {
        this.validateAndSetFile(files[0])
      }
    },
    
    triggerFileInput() {
      this.$refs.fileInput.click()
    },
    
    handleFileSelect(event) {
      const file = event.target.files[0]
      if (file) {
        this.validateAndSetFile(file)
      }
    },
    
    validateAndSetFile(file) {
      this.idVerification.errors.idPhoto = null
      
      // Check file size (5MB max)
      const maxSize = 5 * 1024 * 1024 // 5MB in bytes
      if (file.size > maxSize) {
        this.idVerification.errors.idPhoto = 'File size must be less than 5MB'
        this.showNotification('File too large. Maximum size is 5MB.', 'error')
        return
      }
      
      // Check file type
      const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']
      if (!validTypes.includes(file.type)) {
        this.idVerification.errors.idPhoto = 'File must be JPG, PNG, or PDF'
        this.showNotification('Invalid file type. Please upload JPG, PNG, or PDF.', 'error')
        return
      }
      
      // Set file
      this.idVerification.idPhoto = file
      
      // Create preview for images
      if (file.type.startsWith('image/')) {
        const reader = new FileReader()
        reader.onload = (e) => {
          this.idVerification.idPhotoPreview = e.target.result
        }
        reader.readAsDataURL(file)
      } else {
        // For PDFs, show a generic preview
        this.idVerification.idPhotoPreview = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiMxRTI5M0QiLz48cGF0aCBkPSJNNjUgMjVIMzVWNzVINjVWNVYyNVoiIGZpbGw9IiMzRDU0NzYiLz48cGF0aCBkPSJNNjUgMjVINDFWNUw2NSAyNVoiIGZpbGw9IiM4Q0JBREUiLz48cGF0aCBkPSJNNDUgMzVINTVWNDVINDVWMzVaIiBmaWxsPSIjM0U0QTZFIi8+PHBhdGggZD0iTTM1IDU1SDY1VjY1SDM1VjU1WiIgZmlsbD0iIzNFNEE2RSIvPjxwYXRoIGQ9Ik0zNSA2NUg1NVY3NUgzNVY2NVoiIGZpbGw9IiMzRTRBNkUiLz48L3N2Zz4='
      }
      
      this.showNotification('File uploaded successfully!', 'success')
      this.markIdChanged()
    },
    
    removeIdPhoto() {
      this.idVerification.idPhoto = null
      this.idVerification.idPhotoPreview = ''
      this.idVerification.errors.idPhoto = null
      this.$refs.fileInput.value = ''
      this.showNotification('File removed', 'info')
      this.markIdChanged()
    },
    
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },
    
    resetIdVerification() {
      this.idVerification.idType = this.idVerification.originalData.idType
      this.idVerification.idNumber = this.idVerification.originalData.idNumber
      this.idVerification.errors = {}
      this.idVerification.error = null
      
      // Reset file if it was changed
      if (this.hasIdChanges) {
        this.removeIdPhoto()
      }
      
      this.showNotification('Verification form reset', 'info')
    },
    
    async submitIdVerification() {
      if (!this.canSubmitId || this.idVerification.loading) return
      
      this.idVerification.loading = true
      this.idVerification.error = null
      this.idVerification.errors = {}
      
      try {
        // Create FormData for file upload
        const formData = new FormData()
        formData.append('id_type', this.idVerification.idType)
        formData.append('id_number', this.idVerification.idNumber)
        formData.append('id_photo', this.idVerification.idPhoto)
        
        // Make API call to submit ID verification
        const response = await axios.post('/client/requirements', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          timeout: 30000 // 30 second timeout
        })
        
        if (response.data.status === 'success') {
          // Update verification status
          this.idVerification.status = response.data.id_verification.status
          this.idVerification.submittedAt = response.data.id_verification.submitted_at
          
          // Update preview with server URL
          if (response.data.id_verification.id_photo_url) {
            this.idVerification.idPhotoPreview = response.data.id_verification.id_photo_url
          }
          
          // Update original data
          this.idVerification.originalData = {
            idType: this.idVerification.idType,
            idNumber: this.idVerification.idNumber,
            idPhoto: this.idVerification.idPhoto
          }
          
          // Show success message
          this.showNotification(response.data.message || 'ID verification submitted successfully!', 'success')
          
        } else {
          throw new Error(response.data.message || 'Failed to submit ID verification')
        }
        
      } catch (error) {
        console.error('Error submitting ID verification:', error)
        
        if (error.response?.status === 422) {
          this.idVerification.errors = error.response.data.errors || {}
          this.idVerification.error = error.response.data.message || 'Validation failed'
          this.showNotification('Please fix the validation errors', 'error')
        } else {
          this.idVerification.error = error.response?.data?.message || 
                                   error.message || 
                                   'Failed to submit ID verification'
          this.showNotification(this.idVerification.error, 'error')
        }
      } finally {
        this.idVerification.loading = false
      }
    },
    
    getIDTypeName(idType) {
      const idTypes = {
        'philid': 'Philippine National ID (PhilID/ePhilID)',
        'passport': 'Passport',
        'driver_license': 'Driver\'s License',
        'umid': 'UMID (SSS/GSIS)',
        'prc': 'PRC ID',
        'voter': 'Voter\'s ID',
        'postal': 'Postal ID',
        'philhealth': 'PhilHealth ID',
        'nbi': 'NBI Clearance',
        'senior_citizen': 'Senior Citizen ID',
        'other': 'Other Government ID'
      }
      return idTypes[idType] || idType
    },
    
    formatDateTime(dateTime) {
      if (!dateTime) return 'N/A'
      const date = new Date(dateTime)
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },
    
    markChanged() {
      if (!this.originalUser) return
      
      // Compare current user data with original
      const current = JSON.stringify({
        first_name: this.user.first_name,
        last_name: this.user.last_name,
        email: this.user.email,
        phone: this.user.phone,
        address: this.user.address
      })
      
      const original = JSON.stringify({
        first_name: this.originalUser.first_name,
        last_name: this.originalUser.last_name,
        email: this.originalUser.email,
        phone: this.originalUser.phone,
        address: this.originalUser.address
      })
      
      this.hasChanges = current !== original
    },
    
    async saveProfile() {
      if (!this.hasChanges || this.loading) return
      
      this.loading = true
      this.validationErrors = {}
      
      try {
        const updateData = {
          first_name: this.user.first_name,
          last_name: this.user.last_name,
          email: this.user.email,
          phone: this.user.phone,
          address: this.user.address
        }
        
        const response = await axios.put('/profile', updateData)
        
        if (response.data.status === 'success') {
          // Update local user data
          Object.assign(this.user, updateData)
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          
          // Show success message
          this.showNotification('Profile updated successfully!', 'success')
        } else {
          throw new Error('Update failed')
        }
      } catch (error) {
        console.error('Error updating profile:', error)
        
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.showNotification('Please fix the validation errors', 'error')
        } else {
          this.showNotification(
            error.response?.data?.message || 'Failed to update profile', 
            'error'
          )
        }
      } finally {
        this.loading = false
      }
    },
    
    resetPersonalAndContactInfo() {
      if (this.originalUser) {
        this.user = JSON.parse(JSON.stringify(this.originalUser))
        this.hasChanges = false
        this.validationErrors = {}
        this.showNotification('Changes discarded', 'info')
      }
    },
    
    updatePersonalAndContactInfo() {
      this.saveProfile()
    },
    
    togglePasswordVisibility(type) {
      if (type === 'current') this.showCurrentPassword = !this.showCurrentPassword
      if (type === 'new') this.showNewPassword = !this.showNewPassword
      if (type === 'confirm') this.showConfirmPassword = !this.showConfirmPassword
    },
    
    hasSpecialChars(password) {
      return /[^A-Za-z0-9]/.test(password)
    },
    
    hasNumbers(password) {
      return /[0-9]/.test(password)
    },
    
    hasUpperCase(password) {
      return /[A-Z]/.test(password)
    },
    
    async changePassword() {
      if (!this.canChangePassword || this.passwordLoading) return
      
      this.passwordLoading = true
      this.passwordError = null
      this.validationErrors = {}
      
      try {
        const passwordData = {
          current_password: this.password.current,
          password: this.password.new,
          password_confirmation: this.password.confirm
        }
        
        const response = await axios.put('/profile/password', passwordData)
        
        if (response.data.status === 'success') {
          // Reset password fields
          this.password = { current: '', new: '', confirm: '' }
          
          // Show success message
          this.showNotification('Password changed successfully!', 'success')
        } else {
          throw new Error('Password change failed')
        }
      } catch (error) {
        console.error('Error changing password:', error)
        
        if (error.response?.status === 422) {
          this.validationErrors = error.response.data.errors || {}
          this.passwordError = error.response.data.message || 'Password change failed'
          this.showNotification(this.passwordError, 'error')
        } else {
          this.passwordError = error.response?.data?.message || error.message || 'Failed to change password'
          this.showNotification(this.passwordError, 'error')
        }
      } finally {
        this.passwordLoading = false
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A'
      
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long'
        })
      } catch (e) {
        return 'N/A'
      }
    },
    
    formatRole(role) {
      const roles = {
        'client': 'Client Account',
        'distributor': 'Distributor Account',
        'service_provider': 'Service Provider Account',
        'admin': 'Administrator'
      }
      return roles[role] || role
    }
  }
}
</script>

<style scoped>
  @import "../client/styles/profileC.css";  

  /* Notification System Styles */
  .notifications-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 10px;
    width: 350px;
    max-width: calc(100vw - 40px);
  }

  .notification {
    position: relative;
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    overflow: hidden;
    border-left: 4px solid #3d6ef7;
  }

  .notification:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
  }

  .notification.success {
    border-left-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  }

  .notification.error {
    border-left-color: #ef4444;
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  }

  .notification.warning {
    border-left-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  }

  .notification.info {
    border-left-color: #3d6ef7;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  }

  .notification-icon {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .notification-icon .icon {
    width: 20px;
    height: 20px;
    stroke-width: 2;
  }

  .notification.success .icon {
    stroke: #10b981;
  }

  .notification.error .icon {
    stroke: #ef4444;
  }

  .notification.warning .icon {
    stroke: #f59e0b;
  }

  .notification.info .icon {
    stroke: #3d6ef7;
  }

  .notification-content {
    flex: 1;
    min-width: 0;
  }

  .notification-title {
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 4px;
    color: #1f2937;
  }

  .notification.success .notification-title {
    color: #065f46;
  }

  .notification.error .notification-title {
    color: #991b1b;
  }

  .notification.warning .notification-title {
    color: #92400e;
  }

  .notification.info .notification-title {
    color: #1e40af;
  }

  .notification-message {
    font-size: 13px;
    line-height: 1.5;
    color: #4b5563;
  }

  .notification.success .notification-message {
    color: #047857;
  }

  .notification.error .notification-message {
    color: #b91c1c;
  }

  .notification.warning .notification-message {
    color: #92400e;
  }

  .notification.info .notification-message {
    color: #1e40af;
  }

  .notification-close {
    flex-shrink: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    opacity: 0.6;
    transition: opacity 0.2s;
  }

  .notification-close:hover {
    opacity: 1;
  }

  .notification-close .close-icon {
    width: 16px;
    height: 16px;
    stroke: #6b7280;
  }

  .notification-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: rgba(0, 0, 0, 0.1);
    animation: progress-bar linear forwards;
  }

  .notification.success .notification-progress {
    background: rgba(16, 185, 129, 0.3);
  }

  .notification.error .notification-progress {
    background: rgba(239, 68, 68, 0.3);
  }

  .notification.warning .notification-progress {
    background: rgba(245, 158, 11, 0.3);
  }

  .notification.info .notification-progress {
    background: rgba(59, 130, 246, 0.3);
  }

  /* Animation for notifications */
  .notification-slide-enter-active,
  .notification-slide-leave-active {
    transition: all 0.4s ease;
  }

  .notification-slide-enter-from {
    opacity: 0;
    transform: translateX(100%);
  }

  .notification-slide-leave-to {
    opacity: 0;
    transform: translateX(100%);
  }

  @keyframes progress-bar {
    from {
      width: 100%;
    }
    to {
      width: 0%;
    }
  }

  /* Responsive notifications */
  @media (max-width: 640px) {
    .notifications-container {
      top: 10px;
      right: 10px;
      left: 10px;
      width: auto;
    }
    
    .notification {
      padding: 12px;
    }
  }
</style>