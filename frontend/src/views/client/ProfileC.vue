<template>
  <div class="profile-container">
    <!-- Header Section -->
    <div class="header-section">
      <div class="header-content">
        <h1 class="title">
          <svg class="title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
          Account Management
        </h1>
        <p class="subtitle">Manage your personal information, security, and contact details</p>
      </div>
      <div class="header-actions">
        <div class="account-status">
          <div class="status-indicator active"></div>
          <span class="status-text">Account Active</span>
        </div>
        <button class="save-btn" :disabled="!hasChanges" @click="saveProfile">
          <svg class="save-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
          </svg>
          Save Changes
        </button>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
      <!-- Profile Overview Card -->
      <div class="overview-card">
        <div class="overview-header">
          <div class="profile-avatar-wrapper">
            <div class="profile-avatar">
              <svg class="avatar-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div class="avatar-status"></div>
          </div>
          <div class="overview-info">
            <h2 class="profile-name">{{ profile.name }}</h2>
            <p class="profile-role">Client Account</p>
            <div class="profile-meta">
              <div class="meta-item">
                <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Member since {{ profile.joinDate }}
              </div>
              <div class="meta-item">
                <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ profile.email }}
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

      <!-- Personal Information Card -->
      <div class="info-card">
        <div class="card-header">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
          </svg>
          <h3 class="card-title">Personal Information</h3>
        </div>
        
        <form class="info-form" @submit.prevent="updatePersonalInfo">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Full Name
              </label>
              <input 
                type="text" 
                v-model="profile.name"
                class="form-input"
                placeholder="Enter your full name"
                @input="markChanged"
              >
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
                v-model="profile.email"
                class="form-input"
                placeholder="Enter your email"
                @input="markChanged"
              >
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
                v-model="profile.phone"
                class="form-input"
                placeholder="Enter your phone number"
                @input="markChanged"
              >
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Date of Birth
              </label>
              <input 
                type="date" 
                v-model="profile.dob"
                class="form-input"
                @input="markChanged"
              >
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" class="cancel-btn" @click="resetPersonalInfo">
              Cancel
            </button>
            <button type="submit" class="update-btn" :disabled="!hasChanges">
              Update Information
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
                  :class="{ 'error': passwordMismatch }"
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
          
          <button type="submit" class="change-password-btn" :disabled="!canChangePassword">
            Change Password
          </button>
        </form>
      </div>

      <!-- Contact Details Card -->
      <div class="info-card">
        <div class="card-header">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
          <h3 class="card-title">Contact Details</h3>
        </div>
        
        <form class="contact-form" @submit.prevent="updateContactDetails">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Address Line 1
              </label>
              <input 
                type="text" 
                v-model="contact.address1"
                class="form-input"
                placeholder="Street address"
                @input="markChanged"
              >
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Address Line 2
              </label>
              <input 
                type="text" 
                v-model="contact.address2"
                class="form-input"
                placeholder="Apartment, suite, unit"
                @input="markChanged"
              >
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                City
              </label>
              <input 
                type="text" 
                v-model="contact.city"
                class="form-input"
                placeholder="City"
                @input="markChanged"
              >
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Province
              </label>
              <input 
                type="text" 
                v-model="contact.province"
                class="form-input"
                value="Cavite"
                readonly
              >
              <div class="form-hint">
                <svg class="hint-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Service area: Cavite only
              </div>
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                Emergency Contact
              </label>
              <input 
                type="tel" 
                v-model="contact.emergencyContact"
                class="form-input"
                placeholder="Emergency contact number"
                @input="markChanged"
              >
            </div>
            
            <div class="form-group">
              <label class="form-label">
                <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Preferred Contact Method
              </label>
              <div class="contact-methods">
                <label class="method-option">
                  <input type="radio" v-model="contact.preferredMethod" value="email" @change="markChanged">
                  <span class="method-label">
                    <svg class="method-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email
                  </span>
                </label>
                <label class="method-option">
                  <input type="radio" v-model="contact.preferredMethod" value="phone" @change="markChanged">
                  <span class="method-label">
                    <svg class="method-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Phone
                  </span>
                </label>
                <label class="method-option">
                  <input type="radio" v-model="contact.preferredMethod" value="sms" @change="markChanged">
                  <span class="method-label">
                    <svg class="method-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    SMS
                  </span>
                </label>
              </div>
            </div>
          </div>
          
          <button type="submit" class="update-contact-btn" :disabled="!hasContactChanges">
            Update Contact Details
          </button>
        </form>
      </div>

      <!-- Account Security Card -->
      <div class="info-card security-card">
        <div class="card-header">
          <svg class="card-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
          <h3 class="card-title">Account Security</h3>
        </div>
        
        <div class="security-features">
          <div class="security-item">
            <div class="security-info">
              <h4 class="security-title">Two-Factor Authentication</h4>
              <p class="security-description">Add an extra layer of security to your account</p>
            </div>
            <div class="security-action">
              <label class="toggle-switch">
                <input type="checkbox" v-model="security.twoFactor" @change="toggleTwoFactor">
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>
          
          <div class="security-item">
            <div class="security-info">
              <h4 class="security-title">Login Activity</h4>
              <p class="security-description">Review recent login attempts to your account</p>
            </div>
            <div class="security-action">
              <button class="view-activity-btn" @click="viewLoginActivity">
                View Activity
              </button>
            </div>
          </div>
          
          <div class="security-item">
            <div class="security-info">
              <h4 class="security-title">Connected Devices</h4>
              <p class="security-description">Manage devices connected to your account</p>
            </div>
            <div class="security-action">
              <button class="manage-devices-btn" @click="manageDevices">
                Manage Devices
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ClientProfile',
  data() {
    return {
      hasChanges: false,
      showCurrentPassword: false,
      showNewPassword: false,
      showConfirmPassword: false,
      profile: {
        name: 'Juan Dela Cruz',
        email: 'juan.delacruz@email.com',
        phone: '+63 912 345 6789',
        dob: '1990-01-15',
        joinDate: 'January 2023'
      },
      originalProfile: null,
      password: {
        current: '',
        new: '',
        confirm: ''
      },
      contact: {
        address1: '123 Main Street',
        address2: 'Unit 4B',
        city: 'Imus',
        province: 'Cavite',
        emergencyContact: '+63 923 456 7890',
        preferredMethod: 'email'
      },
      originalContact: null,
      stats: {
        colorSelections: 12,
        serviceRequests: 5,
        activeProjects: 2
      },
      security: {
        twoFactor: false
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
    hasContactChanges() {
      if (!this.originalContact) return false
      return JSON.stringify(this.contact) !== JSON.stringify(this.originalContact)
    }
  },
  created() {
    // Store original values for comparison
    this.originalProfile = JSON.parse(JSON.stringify(this.profile))
    this.originalContact = JSON.parse(JSON.stringify(this.contact))
  },
  methods: {
    markChanged() {
      this.hasChanges = true
    },
    saveProfile() {
      console.log('Saving profile changes...')
      // API call would go here
      this.hasChanges = false
      this.originalProfile = JSON.parse(JSON.stringify(this.profile))
      this.originalContact = JSON.parse(JSON.stringify(this.contact))
      
      // Show success message
      alert('Profile updated successfully!')
    },
    resetPersonalInfo() {
      this.profile = JSON.parse(JSON.stringify(this.originalProfile))
      this.hasChanges = false
    },
    updatePersonalInfo() {
      console.log('Updating personal info:', this.profile)
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
    changePassword() {
      if (!this.canChangePassword) return
      
      console.log('Changing password...')
      // API call would go here
      
      // Reset password fields
      this.password = { current: '', new: '', confirm: '' }
      
      // Show success message
      alert('Password changed successfully!')
    },
    updateContactDetails() {
      console.log('Updating contact details:', this.contact)
      this.saveProfile()
    },
    toggleTwoFactor() {
      console.log('Two-factor authentication:', this.security.twoFactor ? 'Enabled' : 'Disabled')
      // API call would go here
    },
    viewLoginActivity() {
      console.log('Viewing login activity...')
      // Navigate to login activity page
    },
    manageDevices() {
      console.log('Managing devices...')
      // Navigate to device management page
    }
  }
}
</script>

<style scoped>
.profile-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  padding: 1.5rem;
  overflow-x: hidden;
}

/* Header Section */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-content {
  flex: 1;
  min-width: 300px;
}

.title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.875rem;
  font-weight: 700;
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  margin-bottom: 0.5rem;
}

.title-icon {
  width: 2rem;
  height: 2rem;
  flex-shrink: 0;
}

.subtitle {
  color: #94a3b8;
  font-size: 0.875rem;
  max-width: 600px;
}

.header-actions {
  display: flex;
  gap: 1rem;
  align-items: center;
}

.account-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  border-radius: 9999px;
}

.status-indicator {
  width: 8px;
  height: 8px;
  background: #22c55e;
  border-radius: 50%;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.status-text {
  font-size: 0.75rem;
  font-weight: 500;
  color: #22c55e;
}

.save-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
  border: none;
  border-radius: 0.75rem;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(56, 189, 248, 0.3);
}

.save-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.save-icon {
  width: 1rem;
  height: 1rem;
}

/* Main Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 1.5rem;
}

/* Overview Card */
.overview-card {
  background: rgba(30, 41, 59, 0.7);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 1rem;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
  grid-column: 1 / -1;
}

.overview-card:hover {
  border-color: rgba(56, 189, 248, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
}

.overview-header {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.profile-avatar-wrapper {
  position: relative;
}

.profile-avatar {
  width: 5rem;
  height: 5rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #38bdf8, #22d3ee);
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-icon {
  width: 2.5rem;
  height: 2.5rem;
  color: white;
}

.avatar-status {
  position: absolute;
  bottom: 4px;
  right: 4px;
  width: 1rem;
  height: 1rem;
  background: #22c55e;
  border: 2px solid #0f172a;
  border-radius: 50%;
}

.overview-info {
  flex: 1;
}

.profile-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #e2e8f0;
  margin-bottom: 0.25rem;
}

.profile-role {
  color: #94a3b8;
  font-size: 0.875rem;
  margin-bottom: 0.75rem;
}

.profile-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #cbd5e1;
}

.meta-icon {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.overview-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 1px solid rgba(71, 85, 105, 0.3);
}

.stat {
  text-align: center;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #38bdf8;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Info Cards */
.info-card {
  background: rgba(30, 41, 59, 0.7);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 1rem;
  padding: 1.5rem;
  backdrop-filter: blur(10px);
  transition: all 0.2s ease;
}

.info-card:hover {
  border-color: rgba(56, 189, 248, 0.2);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(71, 85, 105, 0.3);
}

.card-icon {
  width: 1.5rem;
  height: 1.5rem;
  color: #38bdf8;
  flex-shrink: 0;
}

.card-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #e2e8f0;
}

/* Forms */
.form-grid {
  display: grid;
  gap: 1.25rem;
  margin-bottom: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  color: #cbd5e1;
  font-size: 0.875rem;
}

.label-icon {
  width: 1rem;
  height: 1rem;
  flex-shrink: 0;
}

.form-input {
  padding: 0.75rem 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.5);
  border-radius: 0.75rem;
  color: #e2e8f0;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.form-input:focus {
  outline: none;
  border-color: #38bdf8;
  box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
}

.form-input.error {
  border-color: #ef4444;
}

.form-input:read-only {
  background: rgba(71, 85, 105, 0.2);
  cursor: not-allowed;
}

.form-hint {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  color: #94a3b8;
  margin-top: 0.25rem;
}

.hint-icon {
  width: 0.875rem;
  height: 0.875rem;
  flex-shrink: 0;
}

/* Password Input */
.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 0.25rem;
}

.toggle-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.75rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

.error-icon {
  width: 0.875rem;
  height: 0.875rem;
  flex-shrink: 0;
}

/* Password Strength */
.password-strength {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.strength-label {
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
}

.strength-bars {
  display: flex;
  gap: 0.25rem;
  flex: 1;
}

.strength-bar {
  flex: 1;
  height: 4px;
  background: rgba(71, 85, 105, 0.5);
  border-radius: 2px;
  transition: all 0.2s ease;
}

.strength-bar.weak {
  background: #ef4444;
}

.strength-bar.medium {
  background: #f59e0b;
}

.strength-bar.strong {
  background: #10b981;
}

.strength-bar.very-strong {
  background: #22c55e;
}

.strength-text {
  font-size: 0.75rem;
  font-weight: 600;
  min-width: 80px;
  text-align: right;
}

.strength-bar.weak ~ .strength-text { color: #ef4444; }
.strength-bar.medium ~ .strength-text { color: #f59e0b; }
.strength-bar.strong ~ .strength-text { color: #10b981; }
.strength-bar.very-strong ~ .strength-text { color: #22c55e; }

/* Password Requirements */
.password-requirements {
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 0.75rem;
  padding: 1rem;
  margin-bottom: 1.5rem;
}

.requirements-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.75rem;
}

.requirements-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.requirements-list li {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #94a3b8;
}

.requirements-list li.met {
  color: #22c55e;
}

.requirement-icon {
  width: 0.875rem;
  height: 0.875rem;
  flex-shrink: 0;
}

/* Contact Methods */
.contact-methods {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.method-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.method-option input[type="radio"] {
  display: none;
}

.method-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.875rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.5);
  border-radius: 0.5rem;
  color: #94a3b8;
  font-size: 0.875rem;
  transition: all 0.2s ease;
}

.method-option input[type="radio"]:checked + .method-label {
  background: rgba(56, 189, 248, 0.1);
  border-color: #38bdf8;
  color: #38bdf8;
}

.method-icon {
  width: 1rem;
  height: 1rem;
}

/* Form Actions */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.cancel-btn {
  padding: 0.625rem 1.25rem;
  background: rgba(71, 85, 105, 0.3);
  border: 1px solid rgba(71, 85, 105, 0.5);
  border-radius: 0.75rem;
  color: #cbd5e1;
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.cancel-btn:hover {
  background: rgba(71, 85, 105, 0.5);
}

.update-btn,
.change-password-btn,
.update-contact-btn {
  padding: 0.625rem 1.25rem;
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
  border: none;
  border-radius: 0.75rem;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.update-btn:hover:not(:disabled),
.change-password-btn:hover:not(:disabled),
.update-contact-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(56, 189, 248, 0.3);
}

.update-btn:disabled,
.change-password-btn:disabled,
.update-contact-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Security Card */
.security-card {
  grid-column: 1 / -1;
}

.security-features {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.security-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: rgba(15, 23, 42, 0.5);
  border: 1px solid rgba(71, 85, 105, 0.3);
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}

.security-item:hover {
  border-color: rgba(56, 189, 248, 0.2);
}

.security-info {
  flex: 1;
}

.security-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 0.25rem;
}

.security-description {
  font-size: 0.75rem;
  color: #94a3b8;
}

.security-action {
  flex-shrink: 0;
}

/* Toggle Switch */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(71, 85, 105, 0.5);
  transition: .4s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  transition: .4s;
  border-radius: 50%;
}

input:checked + .toggle-slider {
  background: linear-gradient(90deg, #38bdf8, #22d3ee);
}

input:checked + .toggle-slider:before {
  transform: translateX(26px);
}

/* Security Action Buttons */
.view-activity-btn,
.manage-devices-btn {
  padding: 0.5rem 1rem;
  background: rgba(56, 189, 248, 0.1);
  border: 1px solid rgba(56, 189, 248, 0.3);
  border-radius: 0.5rem;
  color: #38bdf8;
  font-size: 0.75rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-activity-btn:hover,
.manage-devices-btn:hover {
  background: rgba(56, 189, 248, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
  .profile-container {
    padding: 1rem;
  }
  
  .header-section {
    flex-direction: column;
  }
  
  .header-actions {
    width: 100%;
    justify-content: space-between;
  }
  
  .content-grid {
    grid-template-columns: 1fr;
  }
  
  .overview-header {
    flex-direction: column;
    text-align: center;
  }
  
  .profile-meta {
    align-items: center;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .form-actions button {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .contact-methods {
    flex-direction: column;
  }
  
  .method-label {
    justify-content: center;
  }
  
  .security-item {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .security-action {
    width: 100%;
  }
  
  .view-activity-btn,
  .manage-devices-btn {
    width: 100%;
  }
}
</style>