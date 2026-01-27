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

      <!-- Identity Verification Card - Wizard Style -->
      <div class="info-card wizard-card">
        <div class="card-header">
          <div class="verification-header-content">
            <div class="verification-title-section">
              <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <h3 class="card-title">Identity Verification</h3>
                <p class="verification-subtitle">Complete verification to unlock all features</p>
              </div>
            </div>
            <div class="verification-status-badge" :class="idVerification.status">
              <svg v-if="idVerification.status === 'verified' || idVerification.status === 'approved'" 
                   class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else-if="idVerification.status === 'pending'" 
                   class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <svg v-else-if="idVerification.status === 'rejected'" 
                   class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <span class="status-text">
                {{ idVerification.status === 'verified' ? 'Verified' : 
                   idVerification.status === 'approved' ? 'Approved' :
                   idVerification.status === 'pending' ? 'Pending' : 
                   idVerification.status === 'rejected' ? 'Rejected' : 'Not Started' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Wizard Progress Steps -->
        <div class="wizard-progress">
          <div class="wizard-steps">
            <div v-for="(step, index) in wizardSteps" 
                 :key="index"
                 class="wizard-step"
                 :class="{ 
                   'active': currentStep === index,
                   'completed': step.completed,
                   'disabled': !step.enabled
                 }"
                 @click="goToStep(index)">
              <div class="step-indicator">
                <div v-if="step.completed" class="step-check">
                  <svg class="check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <span v-else class="step-number">{{ index + 1 }}</span>
              </div>
              <div class="step-info">
                <span class="step-title">{{ step.title }}</span>
                <span class="step-description">{{ step.description }}</span>
              </div>
            </div>
          </div>
          <div class="wizard-progress-bar">
            <div class="progress-fill" :style="{ width: wizardProgress + '%' }"></div>
          </div>
        </div>

        <!-- Wizard Content -->
        <div class="wizard-content">
          <!-- Step 1: Choose ID Type -->
          <div v-if="currentStep === 0" class="wizard-step-content step-choose-id">
            <div class="step-header">
              <h4 class="step-title">Choose Your ID Type</h4>
              <p class="step-subtitle">Select the government-issued ID you want to use for verification</p>
            </div>
            
            <div v-if="idVerification.error" class="error-message wizard-error">
              {{ idVerification.error }}
            </div>
            
            <div class="id-type-grid">
              <div v-for="idType in availableIdTypes" 
                   :key="idType.value"
                   class="id-type-card"
                   :class="{ 'selected': idVerification.idType === idType.value, 'disabled': idVerification.status === 'pending' || idVerification.status === 'approved' }"
                   @click="selectIdType(idType)">
                <div class="id-type-icon">
                  <svg class="type-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :d="idType.icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
                  </svg>
                </div>
                <div class="id-type-info">
                  <h5 class="id-type-name">{{ idType.name }}</h5>
                  <p class="id-type-desc">{{ idType.description }}</p>
                </div>
                <div v-if="idVerification.idType === idType.value" class="selected-indicator">
                  <svg class="selected-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
              </div>
            </div>
            
            <div v-if="idVerification.errors.idType" class="error-message">
              {{ idVerification.errors.idType }}
            </div>
          </div>

          <!-- Step 2: Enter ID Details -->
          <div v-else-if="currentStep === 1" class="wizard-step-content step-enter-details">
            <div class="step-header">
              <h4 class="step-title">Enter ID Details</h4>
              <p class="step-subtitle">Please provide your {{ selectedIdTypeName }} information</p>
            </div>
            
            <div class="id-details-form">
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
                  :placeholder="`Enter your ${selectedIdTypeName} number`"
                  @input="validateIdNumber"
                  :disabled="idVerification.status === 'pending' || idVerification.status === 'approved'"
                >
                <div v-if="idVerification.errors.idNumber" class="error-message">
                  {{ idVerification.errors.idNumber }}
                </div>
                <div class="form-hint">
                  <svg class="hint-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Enter the exact number as shown on your ID
                </div>
              </div>
              
              <div class="id-preview-card">
                <div class="preview-header">
                  <svg class="preview-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <span>ID Format Example</span>
                </div>
                <div class="preview-content">
                  <div class="preview-id-type">{{ selectedIdTypeName }}</div>
                  <div class="preview-id-number">XXX-XXXX-XXXX-XXXX</div>
                  <div class="preview-hint">Your ID number format may vary</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Upload ID Photo -->
          <div v-else-if="currentStep === 2" class="wizard-step-content step-upload-photo">
            <div class="step-header">
              <h4 class="step-title">Upload ID Photo</h4>
              <p class="step-subtitle">Take a clear photo of your {{ selectedIdTypeName }}</p>
            </div>
            
            <div class="upload-wizard-container">
              <!-- Upload Requirements -->
              <div class="upload-requirements-card">
                <h5 class="requirements-title">
                  <svg class="requirements-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Photo Requirements
                </h5>
                <ul class="requirements-list">
                  <li class="requirement-item">
                    <svg class="requirement-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Clear and legible text</span>
                  </li>
                  <li class="requirement-item">
                    <svg class="requirement-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>All four corners visible</span>
                  </li>
                  <li class="requirement-item">
                    <svg class="requirement-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Good lighting, no glare</span>
                  </li>
                  <li class="requirement-item">
                    <svg class="requirement-check" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Max file size: 5MB</span>
                  </li>
                </ul>
              </div>
              
              <!-- Upload Area -->
              <div class="upload-wizard-area" 
                   @dragover.prevent="handleDragOver"
                   @dragleave.prevent="handleDragLeave"
                   @drop.prevent="handleFileDrop"
                   :class="{ 'drag-over': idVerification.isDragging, 'has-photo': idVerification.idPhotoPreview }"
                   @click="triggerFileInput">
                
                <div v-if="!idVerification.idPhotoPreview" class="upload-wizard-placeholder">
                  <div class="upload-icon-container">
                    <svg class="upload-wizard-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 11l3-3m0 0l3 3m-3-3v12" />
                    </svg>
                  </div>
                  <div class="upload-wizard-text">
                    <h5>Drag & Drop Your ID Photo</h5>
                    <p>or click to browse files</p>
                    <span class="upload-wizard-hint">Supports JPG, PNG, PDF • Max 5MB</span>
                  </div>
                </div>
                
                <div v-else class="upload-wizard-preview">
                  <div class="preview-container">
                    <img :src="idVerification.idPhotoPreview" alt="ID Preview" class="id-preview-image" />
                    <div class="preview-overlay">
                      <button type="button" class="preview-change-btn" @click.stop="triggerFileInput">
                        <svg class="change-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Change Photo
                      </button>
                      <button type="button" class="preview-remove-btn" @click.stop="removeIdPhoto">
                        <svg class="remove-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 011.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Remove
                      </button>
                    </div>
                  </div>
                  <div class="preview-info">
                    <div class="file-info">
                      <svg class="file-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      <div class="file-details">
                        <p class="file-name">{{ idVerification.idPhoto?.name }}</p>
                        <p class="file-size">{{ idVerification.idPhoto ? formatFileSize(idVerification.idPhoto.size) : '' }}</p>
                      </div>
                    </div>
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
            </div>
          </div>

          <!-- Step 4: Review & Submit -->
          <div v-else-if="currentStep === 3" class="wizard-step-content step-review">
            <div class="step-header">
              <h4 class="step-title">Review & Submit</h4>
              <p class="step-subtitle">Please review your information before submission</p>
            </div>
            
            <div class="review-summary">
              <div class="review-card">
                <div class="review-header">
                  <svg class="review-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <h5>Verification Summary</h5>
                </div>
                
                <div class="review-details">
                  <div class="review-item">
                    <span class="review-label">ID Type:</span>
                    <span class="review-value">{{ selectedIdTypeName }}</span>
                  </div>
                  <div class="review-item">
                    <span class="review-label">ID Number:</span>
                    <span class="review-value">{{ idVerification.idNumber }}</span>
                  </div>
                  <div class="review-item">
                    <span class="review-label">File Uploaded:</span>
                    <span class="review-value">
                      <svg v-if="idVerification.idPhoto" class="file-check-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      {{ idVerification.idPhoto?.name || 'No file uploaded' }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="verification-note">
                <div class="note-header">
                  <svg class="note-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Important Information</span>
                </div>
                <div class="note-content">
                  <p>• Your ID information will be securely encrypted</p>
                  <p>• Verification typically takes 1-2 business days</p>
                  <p>• You'll receive email notifications about your status</p>
                  <p>• Once submitted, you cannot edit until review is complete</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 5: Success / Status -->
          <div v-else-if="currentStep === 4" class="wizard-step-content step-success">
            <div class="success-container" :class="idVerification.status">
              <div class="success-icon">
                <svg v-if="idVerification.status === 'pending'" class="status-icon pending" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="idVerification.status === 'approved' || idVerification.status === 'verified'" class="status-icon success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <svg v-else-if="idVerification.status === 'rejected'" class="status-icon error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <svg v-else class="status-icon info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              
              <h4 class="success-title">
                {{ idVerification.status === 'pending' ? 'Verification Submitted!' : 
                   idVerification.status === 'approved' ? 'Verification Approved!' :
                   idVerification.status === 'verified' ? 'Verification Complete!' :
                   idVerification.status === 'rejected' ? 'Verification Rejected' :
                   'Ready to Submit!' }}
              </h4>
              
              <p class="success-message">
                {{ idVerification.status === 'pending' ? 
                   'Your ID verification has been submitted and is under review. You\'ll be notified once the review is complete.' :
                   idVerification.status === 'approved' || idVerification.status === 'verified' ?
                   'Your identity has been successfully verified. You now have access to all features.' :
                   idVerification.status === 'rejected' ?
                   'Your verification was rejected. Please check the reason and submit again.' :
                   'All information has been entered. Ready to submit for verification.' }}
              </p>
              
              <div v-if="idVerification.status === 'pending' || idVerification.status === 'approved' || idVerification.status === 'verified'" 
                   class="verification-details">
                <div class="detail-item">
                  <span class="detail-label">Submitted:</span>
                  <span class="detail-value">{{ formatDateTime(idVerification.submittedAt || new Date()) }}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Reference ID:</span>
                  <span class="detail-value">VER-{{ Math.random().toString(36).substr(2, 9).toUpperCase() }}</span>
                </div>
              </div>
              
              <div v-if="idVerification.status === 'rejected'" class="rejection-reason">
                <h5>Reason for Rejection:</h5>
                <p>• Photo quality is poor, please upload a clearer image</p>
                <p>• ID information doesn't match our records</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Wizard Navigation -->
        <div class="wizard-navigation">
          <button type="button" 
                  class="wizard-nav-btn prev-btn"
                  @click="previousStep"
                  :disabled="currentStep === 0 || idVerification.status === 'pending' || idVerification.status === 'approved'">
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Previous
          </button>
          
          <div class="wizard-step-indicator">
            <span class="current-step">{{ currentStep + 1 }}</span>
            <span class="step-separator">/</span>
            <span class="total-steps">{{ wizardSteps.length }}</span>
          </div>
          
          <button v-if="currentStep < wizardSteps.length - 1"
                  type="button"
                  class="wizard-nav-btn next-btn"
                  @click="nextStep"
                  :disabled="!canProceedToNextStep || idVerification.status === 'pending' || idVerification.status === 'approved'">
            Next
            <svg class="nav-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
          
          <button v-else-if="currentStep === wizardSteps.length - 1 && idVerification.status === 'not_submitted'"
                  type="button"
                  class="wizard-nav-btn submit-btn"
                  @click="submitIdVerification"
                  :disabled="!canSubmitId || idVerification.loading">
            <svg v-if="!idVerification.loading" class="submit-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ idVerification.loading ? 'Submitting...' : 'Submit Verification' }}
          </button>
          
          <button v-else-if="currentStep === wizardSteps.length - 1 && idVerification.status !== 'pending' && idVerification.status !== 'approved'"
                  type="button"
                  class="wizard-nav-btn restart-btn"
                  @click="restartVerification">
            <svg class="restart-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Start New Verification
          </button>
        </div>
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
      
      // Wizard Configuration
      currentStep: 0,
      wizardSteps: [
        { 
          title: 'Choose ID', 
          description: 'Select ID type',
          completed: false,
          enabled: true
        },
        { 
          title: 'Enter Details', 
          description: 'Provide ID information',
          completed: false,
          enabled: false
        },
        { 
          title: 'Upload Photo', 
          description: 'Upload clear ID photo',
          completed: false,
          enabled: false
        },
        { 
          title: 'Review', 
          description: 'Check all information',
          completed: false,
          enabled: false
        },
        { 
          title: 'Submit', 
          description: 'Complete verification',
          completed: false,
          enabled: false
        }
      ],
      
      // Available ID Types with icons
      availableIdTypes: [
        { 
          value: 'philid', 
          name: 'Philippine National ID', 
          description: 'PhilID or ePhilID',
          icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
        },
        { 
          value: 'passport', 
          name: 'Passport', 
          description: 'International passport',
          icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
        },
        { 
          value: 'driver_license', 
          name: 'Driver\'s License', 
          description: 'LTO issued license',
          icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'umid', 
          name: 'UMID Card', 
          description: 'SSS/GSIS unified ID',
          icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        },
        { 
          value: 'prc', 
          name: 'PRC ID', 
          description: 'Professional Regulation',
          icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        },
        { 
          value: 'voter', 
          name: 'Voter\'s ID', 
          description: 'Comelec issued ID',
          icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'postal', 
          name: 'Postal ID', 
          description: 'Philippine Postal ID',
          icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'
        },
        { 
          value: 'philhealth', 
          name: 'PhilHealth ID', 
          description: 'Health insurance card',
          icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'nbi', 
          name: 'NBI Clearance', 
          description: 'National Bureau ID',
          icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
        },
        { 
          value: 'senior_citizen', 
          name: 'Senior Citizen ID', 
          description: 'OSCA issued ID',
          icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
        },
        { 
          value: 'other', 
          name: 'Other Government ID', 
          description: 'Other valid government ID',
          icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
        }
      ],
      
      // ID Verification Data
      idVerification: {
        idType: '',
        idNumber: '',
        idPhoto: null,
        idPhotoPreview: '',
        status: 'not_submitted',
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
    // Password related computed properties
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
    
    // Wizard related computed properties
    wizardProgress() {
      const completedSteps = this.wizardSteps.filter(step => step.completed).length
      return (completedSteps / this.wizardSteps.length) * 100
    },
    
    selectedIdTypeName() {
      const selected = this.availableIdTypes.find(type => type.value === this.idVerification.idType)
      return selected ? selected.name : 'ID'
    },
    
    canProceedToNextStep() {
      switch(this.currentStep) {
        case 0:
          return !!this.idVerification.idType
        case 1:
          return !!this.idVerification.idNumber && this.idVerification.idNumber.trim().length > 5
        case 2:
          return !!this.idVerification.idPhoto
        case 3:
          return true // Review step always accessible
        default:
          return true
      }
    },
    
    canSubmitId() {
      return this.idVerification.idType &&
            this.idVerification.idNumber &&
            this.idVerification.idPhoto &&
            this.idVerification.status === 'not_submitted'
    }
  },
  watch: {
    'idVerification.idType': function(newVal) {
      if (newVal) {
        this.wizardSteps[0].completed = true
        this.wizardSteps[1].enabled = true
      }
    },
    
    'idVerification.idNumber': function(newVal) {
      if (newVal && newVal.trim().length > 5) {
        this.wizardSteps[1].completed = true
        this.wizardSteps[2].enabled = true
      }
    },
    
    'idVerification.idPhoto': function(newVal) {
      if (newVal) {
        this.wizardSteps[2].completed = true
        this.wizardSteps[3].enabled = true
        this.wizardSteps[4].enabled = true
      }
    },
    
    currentStep: function(newStep) {
      // Update completed status for previous steps
      this.wizardSteps.forEach((step, index) => {
        if (index < newStep) {
          step.completed = true
        }
      })
    }
  },
  created() {
    this.fetchUserProfile()
  },
  methods: {
    // Wizard Navigation Methods
    goToStep(stepIndex) {
      if (this.wizardSteps[stepIndex].enabled && 
          this.idVerification.status !== 'pending' && 
          this.idVerification.status !== 'approved') {
        this.currentStep = stepIndex
      }
    },
    
    nextStep() {
      if (this.canProceedToNextStep && this.currentStep < this.wizardSteps.length - 1) {
        this.currentStep++
      }
    },
    
    previousStep() {
      if (this.currentStep > 0) {
        this.currentStep--
      }
    },
    
    restartVerification() {
      if (this.idVerification.status !== 'pending') {
        this.currentStep = 0
        this.idVerification.idType = ''
        this.idVerification.idNumber = ''
        this.idVerification.idPhoto = null
        this.idVerification.idPhotoPreview = ''
        this.idVerification.errors = {}
        this.idVerification.error = null
        
        // Reset wizard steps
        this.wizardSteps.forEach((step, index) => {
          step.completed = index === 0 ? false : false
          step.enabled = index === 0 ? true : false
        })
        
        if (this.$refs.fileInput) {
          this.$refs.fileInput.value = ''
        }
        
        this.showNotification('Verification restarted', 'info')
      }
    },
    
    selectIdType(idType) {
      if (this.idVerification.status === 'pending' || this.idVerification.status === 'approved') return
      
      this.idVerification.idType = idType.value
      this.idVerification.errors.idType = null
      
      // Auto-advance to next step after short delay
      setTimeout(() => {
        if (this.canProceedToNextStep) {
          this.nextStep()
        }
      }, 300)
    },
    
    validateIdNumber() {
      this.idVerification.errors.idNumber = null
      
      if (!this.idVerification.idNumber || this.idVerification.idNumber.trim().length < 6) {
        this.idVerification.errors.idNumber = 'ID number must be at least 6 characters'
      }
    },
    
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

        // Fetch profile data from API
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
            
            // Update wizard based on status
            this.updateWizardFromStatus()
          }
        }
      } catch (error) {
        console.error('Error loading ID verification data:', error)
        this.showNotification('Failed to load verification data', 'error')
      }
    },
    
    updateWizardFromStatus() {
      if (this.idVerification.status === 'pending' || 
          this.idVerification.status === 'approved' || 
          this.idVerification.status === 'verified') {
        
        // Mark all steps as completed
        this.wizardSteps.forEach(step => {
          step.completed = true
          step.enabled = true
        })
        
        // Go to success step
        this.currentStep = 4
        
      } else if (this.idVerification.status === 'rejected') {
        // Go to success step with rejection message
        this.currentStep = 4
      }
    },
    
    // File Upload Methods
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
      if (this.idVerification.status === 'pending' || this.idVerification.status === 'approved') return
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
      const maxSize = 5 * 1024 * 1024
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
          // Auto-advance to next step
          setTimeout(() => {
            if (this.canProceedToNextStep) {
              this.nextStep()
            }
          }, 300)
        }
        reader.readAsDataURL(file)
      } else {
        // For PDFs, show a generic preview
        this.idVerification.idPhotoPreview = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCIgaGVpZ2h0PSIxMDAiIGZpbGw9IiMxRTI5M0QiLz48cGF0aCBkPSJNNjUgMjVIMzVWNzVINjVWNVYyNVoiIGZpbGw9IiMzRDU0NzYiLz48cGF0aCBkPSJNNjUgMjVINDFWNUw2NSAyNVoiIGZpbGw9IiM4Q0JBREUiLz48cGF0aCBkPSJNNDUgMzVINTVWNDVINDVWMzVaIiBmaWxsPSIjM0U0QTZFIi8+PHBhdGggZD0iTTM1IDU1SDY1VjY1SDM1VjU1WiIgZmlsbD0iIzNFNEE2RSIvPjxwYXRoIGQ9Ik0zNSA2NUg1NVY3NUgzNVY2NVoiIGZpbGw9IiMzRTRBNkUiLz48L3N2Zz4='
        setTimeout(() => {
          if (this.canProceedToNextStep) {
            this.nextStep()
          }
        }, 300)
      }
      
      this.showNotification('File uploaded successfully!', 'success')
    },
    
    removeIdPhoto() {
      this.idVerification.idPhoto = null
      this.idVerification.idPhotoPreview = ''
      this.idVerification.errors.idPhoto = null
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = ''
      }
      
      // Update wizard state
      this.wizardSteps[2].completed = false
      this.wizardSteps[3].enabled = false
      this.wizardSteps[4].enabled = false
      
      this.showNotification('File removed', 'info')
    },
    
    formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes'
      const k = 1024
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(k))
      return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    },
    
    async submitIdVerification() {
      if (!this.canSubmitId || this.idVerification.loading) return
      
      this.idVerification.loading = true
      this.idVerification.error = null
      this.idVerification.errors = {}
      
      try {
        const formData = new FormData()
        formData.append('id_type', this.idVerification.idType)
        formData.append('id_number', this.idVerification.idNumber)
        formData.append('id_photo', this.idVerification.idPhoto)
        
        const response = await axios.post('/client/requirements', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          timeout: 30000
        })
        
        if (response.data.status === 'success') {
          this.idVerification.status = response.data.id_verification.status
          this.idVerification.submittedAt = response.data.id_verification.submitted_at
          
          if (response.data.id_verification.id_photo_url) {
            this.idVerification.idPhotoPreview = response.data.id_verification.id_photo_url
          }
          
          this.idVerification.originalData = {
            idType: this.idVerification.idType,
            idNumber: this.idVerification.idNumber,
            idPhoto: this.idVerification.idPhoto
          }
          
          // Update wizard to show success
          this.wizardSteps[3].completed = true
          this.wizardSteps[4].completed = true
          this.currentStep = 4
          
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
          
          // Go back to appropriate step based on error
          if (error.response.data.errors.idType) {
            this.currentStep = 0
          } else if (error.response.data.errors.idNumber) {
            this.currentStep = 1
          } else if (error.response.data.errors.idPhoto) {
            this.currentStep = 2
          }
          
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
    
    // Existing methods
    markChanged() {
      if (!this.originalUser) return
      
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
          Object.assign(this.user, updateData)
          this.originalUser = JSON.parse(JSON.stringify(this.user))
          this.hasChanges = false
          
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
          this.password = { current: '', new: '', confirm: '' }
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
  
  /* Add these wizard-specific styles to your existing CSS */
  
  /* Wizard Card Styles */
  .wizard-card {
    grid-column: 1 / -1;
    margin-top: 1.5rem;
  }
  
  .verification-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }
  
  .verification-title-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
  }
  
  .verification-subtitle {
    color: #94a3b8;
    font-size: 0.875rem;
    margin-top: 0.25rem;
  }
  
  .verification-status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.375rem;
  }
  
  .verification-status-badge.verified,
  .verification-status-badge.approved {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.4);
  }
  
  .verification-status-badge.pending {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.4);
  }
  
  .verification-status-badge.rejected {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.4);
  }
  
  .verification-status-badge.not_submitted {
    background: rgba(148, 163, 184, 0.2);
    color: #94a3b8;
    border: 1px solid rgba(148, 163, 184, 0.4);
  }
  
  .status-icon {
    width: 14px;
    height: 14px;
    stroke-width: 2.5;
  }
  
  /* Wizard Progress Steps */
  .wizard-progress {
    padding: 1.5rem;
    background: rgba(15, 23, 42, 0.5);
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
  }
  
  .wizard-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
  }
  
  .wizard-step {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 8px;
    transition: all 0.3s ease;
    min-width: 120px;
  }
  
  .wizard-step:hover:not(.disabled) {
    background: rgba(255, 255, 255, 0.05);
  }
  
  .wizard-step.active {
    background: rgba(56, 189, 248, 0.1);
  }
  
  .wizard-step.completed {
    opacity: 0.9;
  }
  
  .wizard-step.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .step-indicator {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(71, 85, 105, 0.5);
    border: 2px solid rgba(71, 85, 105, 0.3);
    flex-shrink: 0;
    transition: all 0.3s ease;
  }
  
  .wizard-step.active .step-indicator {
    background: rgba(56, 189, 248, 0.2);
    border-color: #38bdf8;
  }
  
  .wizard-step.active .step-number {
    color: #38bdf8;
  }
  
  .wizard-step.completed .step-indicator {
    background: rgba(16, 185, 129, 0.2);
    border-color: #10b981;
  }
  
  .step-number {
    font-weight: 600;
    font-size: 0.875rem;
    color: #cbd5e1;
  }
  
  .step-check {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .check-icon {
    width: 16px;
    height: 16px;
    stroke-width: 2.5;
    color: #10b981;
  }
  
  .step-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .step-title {
    font-weight: 600;
    font-size: 0.875rem;
    color: #e2e8f0;
  }
  
  .step-description {
    font-size: 0.75rem;
    color: #94a3b8;
  }
  
  .wizard-progress-bar {
    height: 4px;
    background: rgba(71, 85, 105, 0.3);
    border-radius: 2px;
    overflow: hidden;
  }
  
  .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
    border-radius: 2px;
    transition: width 0.5s ease;
  }
  
  /* Wizard Content */
  .wizard-content {
    padding: 1.5rem;
    background: rgba(30, 41, 59, 0.3);
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
  }
  
  .wizard-step-content {
    animation: slideIn 0.3s ease;
  }
  
  @keyframes slideIn {
    from {
      opacity: 0;
      transform: translateX(20px);
    }
    to {
      opacity: 1;
      transform: translateX(0);
    }
  }
  
  .step-header {
    margin-bottom: 2rem;
    text-align: center;
  }
  
  .step-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 0.5rem;
  }
  
  .step-subtitle {
    color: #94a3b8;
    font-size: 0.875rem;
  }
  
  /* ID Type Grid */
  .id-type-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
    margin-top: 1.5rem;
  }
  
  .id-type-card {
    padding: 1.5rem;
    border: 1px solid rgba(71, 85, 105, 0.5);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    background: rgba(15, 23, 42, 0.5);
  }
  
  .id-type-card:hover:not(.disabled) {
    border-color: #38bdf8;
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(56, 189, 248, 0.1);
  }
  
  .id-type-card.selected {
    border-color: #38bdf8;
    background: rgba(56, 189, 248, 0.1);
  }
  
  .id-type-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .id-type-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
  }
  
  .type-icon {
    width: 24px;
    height: 24px;
    stroke-width: 1.5;
    color: white;
  }
  
  .id-type-info {
    margin-bottom: 0.5rem;
  }
  
  .id-type-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: #e2e8f0;
  }
  
  .id-type-desc {
    font-size: 0.875rem;
    color: #94a3b8;
  }
  
  .selected-indicator {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 20px;
    height: 20px;
    background: #10b981;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .selected-icon {
    width: 12px;
    height: 12px;
    stroke-width: 3;
    color: white;
  }
  
  /* ID Details Form */
  .id-details-form {
    max-width: 500px;
    margin: 0 auto;
  }
  
  .id-preview-card {
    margin-top: 2rem;
    padding: 1.5rem;
    background: rgba(15, 23, 42, 0.5);
    border-radius: 12px;
    border: 1px solid rgba(71, 85, 105, 0.5);
  }
  
  .preview-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: #e2e8f0;
    font-weight: 600;
  }
  
  .preview-icon {
    width: 20px;
    height: 20px;
    stroke-width: 2;
    color: #38bdf8;
  }
  
  .preview-content {
    text-align: center;
  }
  
  .preview-id-type {
    font-size: 1.125rem;
    font-weight: 700;
    color: #38bdf8;
    margin-bottom: 0.5rem;
  }
  
  .preview-id-number {
    font-family: 'Courier New', monospace;
    font-size: 1.25rem;
    color: #e2e8f0;
    margin-bottom: 0.5rem;
    letter-spacing: 2px;
    background: rgba(71, 85, 105, 0.3);
    padding: 0.5rem;
    border-radius: 6px;
  }
  
  .preview-hint {
    font-size: 0.875rem;
    color: #94a3b8;
  }
  
  /* Upload Wizard */
  .upload-wizard-container {
    max-width: 600px;
    margin: 0 auto;
  }
  
  .upload-requirements-card {
    background: rgba(56, 189, 248, 0.1);
    border: 1px solid rgba(56, 189, 248, 0.2);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 2rem;
  }
  
  .requirements-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #38bdf8;
    margin-bottom: 1rem;
  }
  
  .requirements-icon {
    width: 20px;
    height: 20px;
    stroke-width: 2;
  }
  
  .requirements-list {
    list-style: none;
    padding: 0;
  }
  
  .requirement-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
    color: #e2e8f0;
  }
  
  .requirement-check {
    width: 20px;
    height: 20px;
    stroke-width: 2;
    flex-shrink: 0;
    color: #10b981;
  }
  
  .upload-wizard-area {
    border: 2px dashed rgba(71, 85, 105, 0.5);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: rgba(15, 23, 42, 0.5);
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .upload-wizard-area:hover:not(.has-photo) {
    border-color: #38bdf8;
    background: rgba(15, 23, 42, 0.7);
  }
  
  .upload-wizard-area.drag-over {
    border-color: #10b981;
    background: rgba(16, 185, 129, 0.1);
  }
  
  .upload-wizard-area.has-photo {
    padding: 1rem;
    border-style: solid;
    border-color: rgba(71, 85, 105, 0.5);
  }
  
  .upload-wizard-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
  }
  
  .upload-icon-container {
    width: 80px;
    height: 80px;
    background: rgba(56, 189, 248, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  
  .upload-wizard-icon {
    width: 40px;
    height: 40px;
    stroke-width: 1.5;
    color: #38bdf8;
  }
  
  .upload-wizard-text h5 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 0.5rem;
  }
  
  .upload-wizard-text p {
    color: #94a3b8;
    margin-bottom: 0.5rem;
  }
  
  .upload-wizard-hint {
    font-size: 0.875rem;
    color: #64748b;
  }
  
  .upload-wizard-preview {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
  }
  
  .preview-container {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
  }
  
  .id-preview-image {
    width: 100%;
    max-height: 300px;
    object-fit: contain;
    border-radius: 12px;
  }
  
  .preview-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    padding: 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
    opacity: 0;
    transition: opacity 0.3s ease;
  }
  
  .preview-container:hover .preview-overlay {
    opacity: 1;
  }
  
  .preview-change-btn,
  .preview-remove-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
  }
  
  .preview-change-btn {
    background: white;
    color: #1f2937;
  }
  
  .preview-change-btn:hover {
    background: #f3f4f6;
  }
  
  .preview-remove-btn {
    background: #ef4444;
    color: white;
  }
  
  .preview-remove-btn:hover {
    background: #dc2626;
  }
  
  .change-icon,
  .remove-icon {
    width: 16px;
    height: 16px;
    stroke-width: 2;
  }
  
  .preview-info {
    display: flex;
    justify-content: center;
  }
  
  .file-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: rgba(30, 41, 59, 0.5);
    border-radius: 8px;
    width: 100%;
  }
  
  .file-icon {
    width: 24px;
    height: 24px;
    stroke-width: 1.5;
    color: #94a3b8;
  }
  
  .file-details {
    flex: 1;
  }
  
  .file-name {
    color: #e2e8f0;
    font-weight: 500;
    margin-bottom: 0.25rem;
    word-break: break-all;
  }
  
  .file-size {
    color: #94a3b8;
    font-size: 0.85rem;
  }
  
  .upload-error {
    margin-top: 0.5rem;
  }
  
  /* Review Step */
  .review-summary {
    max-width: 600px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 2rem;
  }
  
  .review-card {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid rgba(71, 85, 105, 0.5);
  }
  
  .review-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    color: #38bdf8;
  }
  
  .review-icon {
    width: 24px;
    height: 24px;
    stroke-width: 2;
  }
  
  .review-header h5 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #e2e8f0;
  }
  
  .review-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  
  .review-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(71, 85, 105, 0.3);
  }
  
  .review-label {
    font-weight: 500;
    color: #94a3b8;
  }
  
  .review-value {
    font-weight: 600;
    color: #e2e8f0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .file-check-icon {
    width: 16px;
    height: 16px;
    stroke-width: 3;
    color: #10b981;
  }
  
  .verification-note {
    background: rgba(251, 191, 36, 0.1);
    border: 1px solid rgba(251, 191, 36, 0.2);
    border-radius: 12px;
    padding: 1.5rem;
  }
  
  .note-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    color: #fbbf24;
  }
  
  .note-icon {
    width: 20px;
    height: 20px;
    stroke-width: 2;
  }
  
  .note-content {
    color: #fbbf24;
    font-size: 0.875rem;
    line-height: 1.6;
  }
  
  .note-content p {
    margin-bottom: 0.5rem;
  }
  
  /* Success Step */
  .success-container {
    text-align: center;
    padding: 2rem;
    max-width: 500px;
    margin: 0 auto;
  }
  
  .success-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(56, 189, 248, 0.1);
  }
  
  .success-container.pending .success-icon {
    background: rgba(251, 191, 36, 0.1);
  }
  
  .success-container.approved .success-icon,
  .success-container.verified .success-icon {
    background: rgba(16, 185, 129, 0.1);
  }
  
  .success-container.rejected .success-icon {
    background: rgba(239, 68, 68, 0.1);
  }
  
  .status-icon {
    width: 40px;
    height: 40px;
    stroke-width: 2;
  }
  
  .status-icon.success {
    color: #10b981;
  }
  
  .status-icon.pending {
    color: #f59e0b;
  }
  
  .status-icon.error {
    color: #ef4444;
  }
  
  .status-icon.info {
    color: #38bdf8;
  }
  
  .success-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 1rem;
  }
  
  .success-message {
    color: #94a3b8;
    line-height: 1.6;
    margin-bottom: 2rem;
  }
  
  .verification-details {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 2rem;
  }
  
  .detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(71, 85, 105, 0.3);
  }
  
  .detail-item:last-child {
    border-bottom: none;
  }
  
  .detail-label {
    font-weight: 500;
    color: #94a3b8;
  }
  
  .detail-value {
    font-weight: 600;
    color: #e2e8f0;
  }
  
  .rejection-reason {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 2rem;
    text-align: left;
  }
  
  .rejection-reason h5 {
    color: #f87171;
    margin-bottom: 1rem;
    font-weight: 600;
  }
  
  .rejection-reason p {
    color: #fecaca;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
  }
  
  /* Wizard Navigation */
  .wizard-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(71, 85, 105, 0.3);
  }
  
  .wizard-nav-btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
  }
  
  .wizard-nav-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }
  
  .prev-btn {
    background: rgba(71, 85, 105, 0.3);
    color: #cbd5e1;
  }
  
  .prev-btn:not(:disabled):hover {
    background: rgba(71, 85, 105, 0.5);
  }
  
  .next-btn {
    background: linear-gradient(90deg, #38bdf8, #22d3ee);
    color: white;
  }
  
  .next-btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
  }
  
  .submit-btn {
    background: linear-gradient(90deg, #10b981, #34d399);
    color: white;
  }
  
  .submit-btn:not(:disabled):hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
  }
  
  .restart-btn {
    background: rgba(251, 191, 36, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(251, 191, 36, 0.3);
  }
  
  .restart-btn:not(:disabled):hover {
    background: rgba(251, 191, 36, 0.3);
  }
  
  .nav-icon,
  .submit-icon,
  .restart-icon {
    width: 16px;
    height: 16px;
    stroke-width: 2.5;
  }
  
  .wizard-step-indicator {
    font-weight: 600;
    color: #94a3b8;
    font-size: 0.875rem;
  }
  
  .current-step {
    color: #38bdf8;
    font-size: 1.125rem;
  }
  
  .step-separator {
    margin: 0 0.25rem;
  }
  
  .total-steps {
    opacity: 0.7;
  }
  
  /* Responsive Design */
  @media (max-width: 768px) {
    .verification-header-content {
      flex-direction: column;
      align-items: flex-start;
      gap: 1rem;
    }
    
    .verification-status-badge {
      align-self: flex-start;
    }
    
    .wizard-steps {
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .wizard-step {
      min-width: 100%;
    }
    
    .id-type-grid {
      grid-template-columns: 1fr;
    }
    
    .wizard-navigation {
      flex-direction: column;
      gap: 1rem;
    }
    
    .wizard-nav-btn {
      width: 100%;
      justify-content: center;
    }
    
    .wizard-step-indicator {
      order: -1;
      width: 100%;
      text-align: center;
    }
  }
</style>