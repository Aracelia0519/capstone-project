<template>
  <div class="recruitment-container">
    <!-- Header -->
    <div class="header">
      <div class="header-content">
        <div class="header-left">
          <h1 class="page-title">Recruitment Applications</h1>
          <p class="page-subtitle">Manage applications by department - Paint Business</p>
        </div>
        <div class="header-actions">
          <button class="btn btn-primary" @click="openNewApplicationModal">
            <span class="btn-icon">+</span>
            New Application
          </button>
          <button class="btn btn-outline">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
            </svg>
            Filter
          </button>
        </div>
      </div>
    </div>

    <!-- Department Tabs -->
    <div class="department-tabs">
      <div class="tabs-container">
        <button 
          v-for="dept in departments" 
          :key="dept.id"
          :class="['tab-btn', activeDepartment === dept.id ? 'active' : '']"
          @click="setActiveDepartment(dept.id)"
        >
          <span class="tab-icon">{{ dept.icon }}</span>
          <span class="tab-label">{{ dept.name }}</span>
          <span class="tab-count">{{ getDepartmentCount(dept.id) }}</span>
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-container">
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon-wrapper bg-blue-100">
            <svg class="stat-icon text-blue-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ activeDepartmentApplicants.length }}</div>
            <div class="stat-label">Total in {{ activeDepartmentName }}</div>
          </div>
          <div class="stat-trend positive">+{{ getDepartmentTrend(activeDepartment) }}%</div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrapper bg-green-100">
            <svg class="stat-icon text-green-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ shortlistedCount }}</div>
            <div class="stat-label">Shortlisted</div>
          </div>
          <div class="stat-trend positive">+{{ shortlistedTrend }}%</div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrapper bg-yellow-100">
            <svg class="stat-icon text-yellow-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ pendingCount }}</div>
            <div class="stat-label">Pending Review</div>
          </div>
          <div class="stat-trend negative">-{{ pendingTrend }}%</div>
        </div>

        <div class="stat-card">
          <div class="stat-icon-wrapper bg-purple-100">
            <svg class="stat-icon text-purple-600" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="stat-info">
            <div class="stat-number">{{ interviewCount }}</div>
            <div class="stat-label">Interviews</div>
          </div>
          <div class="stat-trend positive">+{{ interviewTrend }}%</div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
      <!-- Left Column - Search & Table -->
      <div class="left-column">
        <!-- Search and Filters -->
        <div class="search-filters">
          <div class="search-box">
            <svg class="search-icon" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
            <input
              type="text"
              v-model="searchQuery"
              :placeholder="`Search ${activeDepartmentName} applicants...`"
              class="search-input"
            />
          </div>

          <div class="filters">
            <select v-model="selectedStatus" class="filter-select">
              <option value="all">All Status</option>
              <option value="pending">Pending</option>
              <option value="shortlisted">Shortlisted</option>
              <option value="interview">Interview</option>
              <option value="rejected">Rejected</option>
              <option value="hired">Hired</option>
            </select>

            <button class="btn btn-secondary" @click="refreshData">
              <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
              </svg>
              Refresh
            </button>
          </div>
        </div>

        <!-- Data Table -->
        <div class="table-container">
          <div class="table-header">
            <h2 class="table-title">{{ activeDepartmentName }} Applicants</h2>
            <div class="table-actions">
              <button class="btn btn-outline" @click="exportToCSV">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                Export
              </button>
              <button class="btn btn-danger" @click="deleteSelected" :disabled="selectedApplicants.length === 0">
                <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Delete Selected
              </button>
            </div>
          </div>

          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th class="table-th">
                    <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="checkbox" />
                  </th>
                  <th class="table-th">Applicant</th>
                  <th class="table-th">Position</th>
                  <th class="table-th">Applied Date</th>
                  <th class="table-th">Status</th>
                  <th class="table-th">Rating</th>
                  <th class="table-th">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="applicant in paginatedApplicants" :key="applicant.id" 
                    :class="['table-tr', selectedApplicants.includes(applicant.id) ? 'selected-row' : '']">
                  <td class="table-td">
                    <input type="checkbox" :value="applicant.id" v-model="selectedApplicants" class="checkbox" />
                  </td>
                  <td class="table-td">
                    <div class="applicant-cell">
                      <div class="avatar" :style="{ background: getDepartmentColor(applicant.department) }">
                        {{ getInitials(applicant.name) }}
                      </div>
                      <div class="applicant-info">
                        <div class="applicant-name">{{ applicant.name }}</div>
                        <div class="applicant-email">{{ applicant.email }}</div>
                        <div class="applicant-department">
                          <span class="dept-badge">{{ applicant.department }}</span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="table-td">
                    <span class="position-tag">{{ applicant.position }}</span>
                  </td>
                  <td class="table-td">{{ formatDate(applicant.appliedDate) }}</td>
                  <td class="table-td">
                    <span :class="['status-badge', applicant.status]">
                      {{ getStatusText(applicant.status) }}
                    </span>
                  </td>
                  <td class="table-td">
                    <div class="rating-display">
                      <div class="stars">
                        <span v-for="n in 5" :key="n" :class="['star', n <= applicant.rating ? 'filled' : '']">★</span>
                      </div>
                      <span class="rating-text">{{ applicant.rating }}/5</span>
                    </div>
                  </td>
                  <td class="table-td">
                    <div class="action-buttons">
                      <button class="action-btn view-btn" @click="viewApplicant(applicant)" title="View">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                          <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                        </svg>
                      </button>
                      <button class="action-btn edit-btn" @click="editApplicant(applicant)" title="Edit">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </button>
                      <button class="action-btn delete-btn" @click="deleteApplicant(applicant)" title="Delete">
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="pagination">
            <div class="pagination-info">
              Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ filteredApplicants.length }} entries
            </div>
            <div class="pagination-controls">
              <button class="pagination-btn" @click="prevPage" :disabled="currentPage === 1">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
              
              <div class="page-numbers">
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  :class="['page-btn', currentPage === page ? 'active' : '']"
                  @click="goToPage(page)"
                >
                  {{ page }}
                </button>
              </div>
              
              <button class="pagination-btn" @click="nextPage" :disabled="currentPage === totalPages">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Department Stats & Wizard -->
      <div class="right-column">
        <!-- Department Statistics -->
        <div class="stats-card">
          <div class="stats-card-header">
            <h3 class="stats-card-title">Department Overview</h3>
            <span class="stats-card-subtitle">{{ activeDepartmentName }}</span>
          </div>
          <div class="dept-stats-grid">
            <div class="dept-stat" v-for="stat in departmentStats" :key="stat.label">
              <div class="dept-stat-icon" :style="{ background: stat.color + '20' }">
                <span :style="{ color: stat.color }">{{ stat.icon }}</span>
              </div>
              <div class="dept-stat-info">
                <div class="dept-stat-value">{{ stat.value }}</div>
                <div class="dept-stat-label">{{ stat.label }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="activity-card">
          <div class="activity-card-header">
            <h3 class="activity-card-title">Recent Activity</h3>
            <button class="activity-view-all" @click="viewAllActivity">View All</button>
          </div>
          <div class="activity-list">
            <div v-for="activity in recentActivities" :key="activity.id" class="activity-item">
              <div class="activity-avatar">
                {{ getInitials(activity.user) }}
              </div>
              <div class="activity-content">
                <div class="activity-text">
                  <span class="activity-user">{{ activity.user }}</span> {{ activity.action }}
                  <span class="activity-applicant">{{ activity.applicant }}</span>
                </div>
                <div class="activity-time">{{ activity.time }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-card">
          <h3 class="actions-card-title">Quick Actions</h3>
          <div class="actions-grid">
            <button class="quick-action" @click="scheduleInterview">
              <div class="action-icon">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                </svg>
              </div>
              <span class="action-label">Schedule Interview</span>
            </button>
            <button class="quick-action" @click="sendEmail">
              <div class="action-icon">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                  <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                </svg>
              </div>
              <span class="action-label">Send Email</span>
            </button>
            <button class="quick-action" @click="generateReport">
              <div class="action-icon">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </div>
              <span class="action-label">Generate Report</span>
            </button>
            <button class="quick-action" @click="addNote">
              <div class="action-icon">
                <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
              </div>
              <span class="action-label">Add Note</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Wizard Modal -->
    <div v-if="showWizard" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <div class="modal-header-left">
            <div class="modal-dept-badge" :style="{ background: getDepartmentColor(selectedApplicant?.department) }">
              {{ selectedApplicant?.department?.charAt(0) }}
            </div>
            <div>
              <h2 class="modal-title">{{ selectedApplicant?.name }}</h2>
              <p class="modal-subtitle">{{ selectedApplicant?.position }} • {{ selectedApplicant?.department }}</p>
            </div>
          </div>
          <button class="modal-close" @click="closeWizard">
            <svg class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <!-- Wizard Steps -->
        <div class="wizard-steps">
          <div
            v-for="step in steps"
            :key="step.id"
            :class="['step', currentStep >= step.id ? 'active' : '']"
          >
            <div class="step-number">{{ step.id }}</div>
            <div class="step-label">{{ step.label }}</div>
          </div>
        </div>

        <!-- Step Content -->
        <div class="wizard-content">
          <!-- Step 1: Personal Info -->
          <div v-if="currentStep === 1" class="step-content">
            <div class="applicant-profile">
              <div class="profile-avatar-large" :style="{ background: getDepartmentColor(selectedApplicant?.department) }">
                {{ getInitials(selectedApplicant?.name) }}
              </div>
              <div class="profile-info">
                <h3 class="profile-name">{{ selectedApplicant?.name }}</h3>
                <p class="profile-email">{{ selectedApplicant?.email }}</p>
                <p class="profile-phone">{{ selectedApplicant?.phone }}</p>
              </div>
            </div>

            <div class="info-grid">
              <div class="info-card">
                <h4 class="info-title">Contact Information</h4>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Address:</span>
                    <span class="info-value">{{ selectedApplicant?.address || 'Not specified' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Location:</span>
                    <span class="info-value">{{ selectedApplicant?.location || 'Remote' }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Department:</span>
                    <span class="info-value">{{ selectedApplicant?.department }}</span>
                  </div>
                </div>
              </div>

              <div class="info-card">
                <h4 class="info-title">Application Details</h4>
                <div class="info-list">
                  <div class="info-item">
                    <span class="info-label">Applied For:</span>
                    <span class="info-value">{{ selectedApplicant?.position }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Applied Date:</span>
                    <span class="info-value">{{ formatDate(selectedApplicant?.appliedDate) }}</span>
                  </div>
                  <div class="info-item">
                    <span class="info-label">Experience:</span>
                    <span class="info-value">{{ selectedApplicant?.experience }} years</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 2: Experience -->
          <div v-if="currentStep === 2" class="step-content">
            <h3 class="step-title">Experience & Skills</h3>
            
            <div class="section">
              <h4 class="section-title">Technical Skills</h4>
              <div class="skills-list">
                <span v-for="skill in selectedApplicant?.skills" :key="skill" class="skill-tag">
                  {{ skill }}
                </span>
              </div>
            </div>

            <div class="section">
              <h4 class="section-title">Work Experience</h4>
              <div class="experience-list">
                <div v-for="exp in selectedApplicant?.experienceDetails" :key="exp.company" class="experience-item">
                  <div class="exp-icon">
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                      <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                    </svg>
                  </div>
                  <div class="exp-details">
                    <h5 class="exp-company">{{ exp.company }}</h5>
                    <p class="exp-role">{{ exp.role }}</p>
                    <p class="exp-duration">{{ exp.duration }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Education -->
          <div v-if="currentStep === 3" class="step-content">
            <h3 class="step-title">Education & Certifications</h3>
            
            <div class="section">
              <h4 class="section-title">Education</h4>
              <div class="education-list">
                <div v-for="edu in selectedApplicant?.education" :key="edu.degree" class="education-item">
                  <div class="edu-icon">
                    <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z" />
                    </svg>
                  </div>
                  <div class="edu-details">
                    <h5 class="edu-degree">{{ edu.degree }}</h5>
                    <p class="edu-institution">{{ edu.institution }}</p>
                    <p class="edu-year">{{ edu.year }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="section">
              <h4 class="section-title">Certifications</h4>
              <div class="certifications-list">
                <span v-for="cert in selectedApplicant?.certifications" :key="cert" class="cert-tag">
                  {{ cert }}
                </span>
              </div>
            </div>
          </div>

          <!-- Step 4: Decision -->
          <div v-if="currentStep === 4" class="step-content">
            <h3 class="step-title">Review & Decision</h3>
            
            <div class="review-summary">
              <div class="summary-card">
                <h4 class="summary-title">Application Score</h4>
                <div class="score-display">
                  <div class="score-value">{{ selectedApplicant?.score || '85' }}</div>
                  <div class="score-label">/ 100</div>
                </div>
                <p class="summary-text">Overall rating based on qualifications</p>
              </div>

              <div class="summary-card">
                <h4 class="summary-title">Department Fit</h4>
                <div class="fit-meter">
                  <div class="fit-bar" :style="{ width: selectedApplicant?.fitScore + '%' }"></div>
                </div>
                <p class="summary-text">{{ selectedApplicant?.fitScore }}% match with {{ selectedApplicant?.department }}</p>
              </div>
            </div>

            <div class="decision-section">
              <h4 class="section-title">Make Your Decision</h4>
              <div class="decision-buttons">
                <button class="decision-btn reject" @click="makeDecision('rejected')">
                  <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                  </svg>
                  Reject
                </button>
                <button class="decision-btn shortlist" @click="makeDecision('shortlisted')">
                  <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  Shortlist
                </button>
                <button class="decision-btn hire" @click="makeDecision('hired')">
                  <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745a3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  Hire
                </button>
              </div>

              <div class="notes-section">
                <label class="notes-label">Review Notes</label>
                <textarea
                  v-model="reviewNotes"
                  placeholder="Add your review notes here..."
                  class="notes-textarea"
                  rows="4"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Wizard Navigation -->
        <div class="wizard-navigation">
          <button class="wizard-btn secondary" @click="prevStep" v-if="currentStep > 1">
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Previous
          </button>
          <div class="wizard-spacer"></div>
          <button class="wizard-btn primary" @click="nextStep" v-if="currentStep < 4">
            Next
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
          </button>
          <button class="wizard-btn success" @click="completeReview" v-if="currentStep === 4">
            Complete Review
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="showToast" class="toast" :class="toastType">
      <div class="toast-content">
        <div class="toast-icon">
          <svg v-if="toastType === 'success'" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <svg v-if="toastType === 'error'" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="toast-message">
          <div class="toast-title">{{ toastTitle }}</div>
          <div class="toast-text">{{ toastMessage }}</div>
        </div>
        <button class="toast-close" @click="showToast = false">
          <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'RecruitmentApplication',
  setup() {
    // Departments for Paint Business
    const departments = ref([
      { id: 'administration', name: 'Administration', icon: '📋', color: '#3B82F6' },
      { id: 'distributor', name: 'Distributor Assistant', icon: '🚚', color: '#10B981' },
      { id: 'finance', name: 'Finance', icon: '💰', color: '#8B5CF6' },
      { id: 'hr', name: 'Human Resources', icon: '👥', color: '#EC4899' },
      { id: 'it', name: 'IT', icon: '💻', color: '#6366F1' },
      { id: 'logistics', name: 'Logistics', icon: '📦', color: '#F59E0B' },
      { id: 'marketing', name: 'Marketing', icon: '📢', color: '#EF4444' },
      { id: 'operations', name: 'Operations', icon: '🏭', color: '#14B8A6' },
      { id: 'sales', name: 'Sales', icon: '📈', color: '#84CC16' }
    ])

    // Sample data with department assignments
    const applicants = ref([
      {
        id: 1,
        name: "John Doe",
        email: "john.doe@example.com",
        phone: "+1 (555) 123-4567",
        position: "Finance Manager",
        department: "Finance",
        appliedDate: "2024-01-15",
        status: "pending",
        rating: 4,
        experience: 5,
        score: 85,
        fitScore: 90,
        address: "123 Main St, San Francisco, CA",
        location: "San Francisco, CA",
        skills: ["Financial Analysis", "Budgeting", "Accounting", "Excel", "QuickBooks"],
        experienceDetails: [
          { company: "Financial Corp", role: "Finance Lead", duration: "2020-2024" },
          { company: "Bank Solutions", role: "Senior Analyst", duration: "2018-2020" }
        ],
        education: [
          { degree: "Bachelor of Finance", institution: "Stanford University", year: "2018" }
        ],
        certifications: ["CFA Level I", "CPA Certified"]
      },
      {
        id: 2,
        name: "Jane Smith",
        email: "jane.smith@example.com",
        phone: "+1 (555) 987-6543",
        position: "HR Specialist",
        department: "Human Resources",
        appliedDate: "2024-01-14",
        status: "shortlisted",
        rating: 5,
        experience: 3,
        score: 92,
        fitScore: 95,
        address: "456 Oak Ave, New York, NY",
        location: "New York, NY",
        skills: ["Recruitment", "Employee Relations", "HR Policies", "Training", "Compliance"],
        experienceDetails: [
          { company: "HR Solutions", role: "HR Specialist", duration: "2021-2024" },
          { company: "Talent Agency", role: "Recruiter", duration: "2019-2021" }
        ],
        education: [
          { degree: "Master of HR Management", institution: "NYU", year: "2019" }
        ],
        certifications: ["SHRM-CP", "PHR"]
      },
      {
        id: 3,
        name: "Robert Johnson",
        email: "robert.j@example.com",
        phone: "+1 (555) 456-7890",
        position: "IT Support Specialist",
        department: "IT",
        appliedDate: "2024-01-13",
        status: "interview",
        rating: 4,
        experience: 4,
        score: 78,
        fitScore: 85,
        address: "789 Pine St, Austin, TX",
        location: "Austin, TX",
        skills: ["Network Administration", "Help Desk", "Windows Server", "Linux", "Cybersecurity"],
        experienceDetails: [
          { company: "Tech Support Inc", role: "IT Specialist", duration: "2019-2024" }
        ],
        education: [
          { degree: "BS in Information Technology", institution: "UT Austin", year: "2019" }
        ],
        certifications: ["CompTIA A+", "Network+", "Security+"]
      },
      {
        id: 4,
        name: "Sarah Williams",
        email: "sarah.w@example.com",
        phone: "+1 (555) 321-0987",
        position: "Sales Executive",
        department: "Sales",
        appliedDate: "2024-01-12",
        status: "hired",
        rating: 5,
        experience: 6,
        score: 95,
        fitScore: 98,
        address: "101 Maple Dr, Boston, MA",
        location: "Boston, MA",
        skills: ["Sales Strategy", "Client Relations", "Negotiation", "CRM", "Market Analysis"],
        experienceDetails: [
          { company: "Sales Pro", role: "Senior Sales Executive", duration: "2020-2024" },
          { company: "Business Corp", role: "Sales Representative", duration: "2018-2020" }
        ],
        education: [
          { degree: "BA in Business Administration", institution: "Boston University", year: "2018" }
        ],
        certifications: ["Salesforce Certified", "Professional Selling"]
      },
      {
        id: 5,
        name: "Michael Brown",
        email: "michael.b@example.com",
        phone: "+1 (555) 654-3210",
        position: "Logistics Coordinator",
        department: "Logistics",
        appliedDate: "2024-01-11",
        status: "rejected",
        rating: 3,
        experience: 2,
        score: 65,
        fitScore: 70,
        address: "222 Cedar Ln, Seattle, WA",
        location: "Seattle, WA",
        skills: ["Supply Chain", "Inventory Management", "Shipping", "Warehouse", "Logistics"],
        experienceDetails: [
          { company: "Logistics Co", role: "Coordinator", duration: "2022-2024" }
        ],
        education: [
          { degree: "Associate in Logistics", institution: "Community College", year: "2022" }
        ],
        certifications: ["CLTD", "CPIM"]
      },
      {
        id: 6,
        name: "Emily Davis",
        email: "emily.d@example.com",
        phone: "+1 (555) 234-5678",
        position: "Marketing Coordinator",
        department: "Marketing",
        appliedDate: "2024-01-10",
        status: "pending",
        rating: 4,
        experience: 3,
        score: 82,
        fitScore: 88,
        address: "333 Birch St, Chicago, IL",
        location: "Chicago, IL",
        skills: ["Social Media", "Content Creation", "SEO", "Analytics", "Campaign Management"],
        experienceDetails: [
          { company: "Marketing Agency", role: "Coordinator", duration: "2021-2024" }
        ],
        education: [
          { degree: "BA in Marketing", institution: "UIC", year: "2021" }
        ],
        certifications: ["Google Analytics", "HubSpot Certified"]
      },
      {
        id: 7,
        name: "David Wilson",
        email: "david.w@example.com",
        phone: "+1 (555) 876-5432",
        position: "Operations Manager",
        department: "Operations",
        appliedDate: "2024-01-09",
        status: "shortlisted",
        rating: 5,
        experience: 8,
        score: 91,
        fitScore: 92,
        address: "444 Elm St, Houston, TX",
        location: "Houston, TX",
        skills: ["Process Improvement", "Team Management", "Quality Control", "Project Management", "Lean Six Sigma"],
        experienceDetails: [
          { company: "Manufacturing Co", role: "Operations Manager", duration: "2018-2024" },
          { company: "Production Inc", role: "Supervisor", duration: "2016-2018" }
        ],
        education: [
          { degree: "BS in Operations Management", institution: "Texas A&M", year: "2016" }
        ],
        certifications: ["Six Sigma Black Belt", "PMP"]
      },
      {
        id: 8,
        name: "Lisa Anderson",
        email: "lisa.a@example.com",
        phone: "+1 (555) 345-6789",
        position: "Administrative Assistant",
        department: "Administration",
        appliedDate: "2024-01-08",
        status: "interview",
        rating: 4,
        experience: 4,
        score: 79,
        fitScore: 82,
        address: "555 Oak St, Miami, FL",
        location: "Miami, FL",
        skills: ["Office Management", "Scheduling", "Documentation", "Communication", "Organization"],
        experienceDetails: [
          { company: "Executive Office", role: "Administrative Assistant", duration: "2020-2024" }
        ],
        education: [
          { degree: "Associate in Business Admin", institution: "Miami Dade College", year: "2020" }
        ],
        certifications: ["Microsoft Office Specialist", "Professional Secretary"]
      }
    ])

    // Reactive data
    const activeDepartment = ref('administration')
    const searchQuery = ref('')
    const selectedStatus = ref('all')
    const selectedApplicants = ref([])
    const selectAll = ref(false)
    const currentPage = ref(1)
    const itemsPerPage = 10
    const showWizard = ref(false)
    const selectedApplicant = ref(null)
    const currentStep = ref(1)
    const reviewNotes = ref('')
    const showToast = ref(false)
    const toastType = ref('success')
    const toastTitle = ref('')
    const toastMessage = ref('')

    const steps = ref([
      { id: 1, label: 'Personal Info' },
      { id: 2, label: 'Experience' },
      { id: 3, label: 'Education' },
      { id: 4, label: 'Decision' }
    ])

    const recentActivities = ref([
      { id: 1, user: 'John HR', action: 'reviewed', applicant: 'Michael Brown', time: '10 min ago' },
      { id: 2, user: 'Sarah M', action: 'shortlisted', applicant: 'Emily Davis', time: '25 min ago' },
      { id: 3, user: 'Robert T', action: 'scheduled interview with', applicant: 'David Wilson', time: '1 hour ago' },
      { id: 4, user: 'Lisa K', action: 'added notes to', applicant: 'John Doe', time: '2 hours ago' }
    ])

    // Computed properties
    const activeDepartmentName = computed(() => {
      const dept = departments.value.find(d => d.id === activeDepartment.value)
      return dept ? dept.name : 'All Departments'
    })

    const activeDepartmentApplicants = computed(() => {
      return applicants.value.filter(applicant => 
        applicant.department.toLowerCase() === activeDepartment.value
      )
    })

    const filteredApplicants = computed(() => {
      let filtered = activeDepartmentApplicants.value
      
      // Apply search filter
      if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(applicant => 
          applicant.name.toLowerCase().includes(query) ||
          applicant.email.toLowerCase().includes(query) ||
          applicant.position.toLowerCase().includes(query)
        )
      }
      
      // Apply status filter
      if (selectedStatus.value !== 'all') {
        filtered = filtered.filter(applicant => applicant.status === selectedStatus.value)
      }
      
      return filtered
    })

    const paginatedApplicants = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      const end = start + itemsPerPage
      return filteredApplicants.value.slice(start, end)
    })

    const totalPages = computed(() => Math.ceil(filteredApplicants.value.length / itemsPerPage))
    const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
    const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, filteredApplicants.value.length))

    const visiblePages = computed(() => {
      const pages = []
      const maxVisible = 5
      
      if (totalPages.value <= maxVisible) {
        for (let i = 1; i <= totalPages.value; i++) {
          pages.push(i)
        }
      } else {
        let start = Math.max(1, currentPage.value - 2)
        let end = Math.min(totalPages.value, start + maxVisible - 1)
        
        if (end - start < maxVisible - 1) {
          start = Math.max(1, end - maxVisible + 1)
        }
        
        for (let i = start; i <= end; i++) {
          pages.push(i)
        }
      }
      
      return pages
    })

    const departmentStats = computed(() => {
      const deptApplicants = activeDepartmentApplicants.value
      const total = deptApplicants.length
      const shortlisted = deptApplicants.filter(a => a.status === 'shortlisted').length
      const interviews = deptApplicants.filter(a => a.status === 'interview').length
      const pending = deptApplicants.filter(a => a.status === 'pending').length
      const avgRating = total > 0 
        ? (deptApplicants.reduce((sum, a) => sum + a.rating, 0) / total).toFixed(1)
        : '0.0'
      
      return [
        { icon: '👥', label: 'Total Applicants', value: total, color: '#3B82F6' },
        { icon: '⭐', label: 'Avg. Rating', value: avgRating, color: '#F59E0B' },
        { icon: '✅', label: 'Shortlisted', value: shortlisted, color: '#10B981' },
        { icon: '📅', label: 'Interviews', value: interviews, color: '#8B5CF6' },
        { icon: '⏳', label: 'Pending', value: pending, color: '#6366F1' },
        { icon: '🎯', label: 'Avg. Score', value: total > 0 
          ? Math.round(deptApplicants.reduce((sum, a) => sum + a.score, 0) / total)
          : 0, color: '#EC4899' }
      ]
    })

    const shortlistedCount = computed(() => {
      return activeDepartmentApplicants.value.filter(a => a.status === 'shortlisted').length
    })

    const pendingCount = computed(() => {
      return activeDepartmentApplicants.value.filter(a => a.status === 'pending').length
    })

    const interviewCount = computed(() => {
      return activeDepartmentApplicants.value.filter(a => a.status === 'interview').length
    })

    // Methods
    const setActiveDepartment = (deptId) => {
      activeDepartment.value = deptId
      currentPage.value = 1
      selectedApplicants.value = []
      selectAll.value = false
    }

    const getDepartmentCount = (deptId) => {
      return applicants.value.filter(a => a.department.toLowerCase() === deptId).length
    }

    const getDepartmentColor = (deptName) => {
      const dept = departments.value.find(d => d.name === deptName)
      return dept ? dept.color : '#6B7280'
    }

    const getDepartmentTrend = (deptId) => {
      // Mock trend data
      const trends = {
        administration: 12,
        distributor: 8,
        finance: 15,
        hr: 10,
        it: 18,
        logistics: 5,
        marketing: 22,
        operations: 7,
        sales: 25
      }
      return trends[deptId] || 10
    }

    const shortlistedTrend = computed(() => {
      return Math.floor(Math.random() * 15) + 5
    })

    const pendingTrend = computed(() => {
      return Math.floor(Math.random() * 10) + 1
    })

    const interviewTrend = computed(() => {
      return Math.floor(Math.random() * 20) + 8
    })

    const getInitials = (name) => {
      if (!name) return 'JD'
      return name.split(' ').map(n => n[0]).join('').toUpperCase()
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
      })
    }

    const getStatusText = (status) => {
      const statusMap = {
        'pending': 'Pending Review',
        'shortlisted': 'Shortlisted',
        'interview': 'Interview',
        'hired': 'Hired',
        'rejected': 'Rejected'
      }
      return statusMap[status] || status
    }

    const toggleSelectAll = () => {
      if (selectAll.value) {
        selectedApplicants.value = filteredApplicants.value.map(a => a.id)
      } else {
        selectedApplicants.value = []
      }
    }

    const viewApplicant = (applicant) => {
      selectedApplicant.value = applicant
      currentStep.value = 1
      showWizard.value = true
    }

    const editApplicant = (applicant) => {
      showToastMessage('info', 'Edit Feature', `Edit functionality for ${applicant.name} would be implemented here.`)
    }

    const deleteApplicant = (applicant) => {
      if (confirm(`Are you sure you want to delete ${applicant.name}'s application?`)) {
        applicants.value = applicants.value.filter(a => a.id !== applicant.id)
        showToastMessage('success', 'Deleted', `Application for ${applicant.name} has been deleted.`)
      }
    }

    const deleteSelected = () => {
      if (selectedApplicants.value.length === 0) return
      
      if (confirm(`Are you sure you want to delete ${selectedApplicants.value.length} selected applicants?`)) {
        applicants.value = applicants.value.filter(a => !selectedApplicants.value.includes(a.id))
        selectedApplicants.value = []
        selectAll.value = false
        showToastMessage('success', 'Deleted', `${selectedApplicants.value.length} applicants have been deleted.`)
      }
    }

    const prevStep = () => {
      if (currentStep.value > 1) {
        currentStep.value--
      }
    }

    const nextStep = () => {
      if (currentStep.value < steps.value.length) {
        currentStep.value++
      }
    }

    const makeDecision = (decision) => {
      if (selectedApplicant.value) {
        selectedApplicant.value.status = decision
        const applicant = applicants.value.find(a => a.id === selectedApplicant.value.id)
        if (applicant) {
          applicant.status = decision
        }
        showToastMessage('success', 'Decision Updated', `Application ${decision} successfully.`)
      }
    }

    const completeReview = () => {
      showWizard.value = false
      showToastMessage('success', 'Review Complete', `Review for ${selectedApplicant.value.name} has been completed.`)
      selectedApplicant.value = null
      currentStep.value = 1
      reviewNotes.value = ''
    }

    const closeWizard = () => {
      showWizard.value = false
      selectedApplicant.value = null
      currentStep.value = 1
      reviewNotes.value = ''
    }

    const openNewApplicationModal = () => {
      showToastMessage('info', 'New Application', 'New application modal would open here.')
    }

    const refreshData = () => {
      showToastMessage('success', 'Refreshed', 'Data has been refreshed.')
    }

    const exportToCSV = () => {
      showToastMessage('success', 'Export Started', 'Exporting data to CSV...')
    }

    const prevPage = () => {
      if (currentPage.value > 1) {
        currentPage.value--
      }
    }

    const nextPage = () => {
      if (currentPage.value < totalPages.value) {
        currentPage.value++
      }
    }

    const goToPage = (page) => {
      currentPage.value = page
    }

    const viewAllActivity = () => {
      showToastMessage('info', 'Activity', 'Viewing all activity...')
    }

    const scheduleInterview = () => {
      showToastMessage('success', 'Interview', 'Schedule interview dialog would open.')
    }

    const sendEmail = () => {
      showToastMessage('success', 'Email', 'Email composer would open.')
    }

    const generateReport = () => {
      showToastMessage('success', 'Report', 'Generating department report...')
    }

    const addNote = () => {
      showToastMessage('info', 'Note', 'Add note dialog would open.')
    }

    const showToastMessage = (type, title, message) => {
      toastType.value = type
      toastTitle.value = title
      toastMessage.value = message
      showToast.value = true
      
      setTimeout(() => {
        showToast.value = false
      }, 3000)
    }

    onMounted(() => {
      setTimeout(() => {
        showToastMessage('success', 'Welcome', 'Recruitment dashboard loaded successfully!')
      }, 1000)
    })

    return {
      // Data
      departments,
      applicants,
      activeDepartment,
      searchQuery,
      selectedStatus,
      selectedApplicants,
      selectAll,
      currentPage,
      showWizard,
      selectedApplicant,
      currentStep,
      reviewNotes,
      showToast,
      toastType,
      toastTitle,
      toastMessage,
      steps,
      recentActivities,
      
      // Computed
      activeDepartmentName,
      activeDepartmentApplicants,
      filteredApplicants,
      paginatedApplicants,
      totalPages,
      startIndex,
      endIndex,
      visiblePages,
      departmentStats,
      shortlistedCount,
      pendingCount,
      interviewCount,
      shortlistedTrend,
      pendingTrend,
      interviewTrend,
      
      // Methods
      setActiveDepartment,
      getDepartmentCount,
      getDepartmentColor,
      getDepartmentTrend,
      getInitials,
      formatDate,
      getStatusText,
      toggleSelectAll,
      viewApplicant,
      editApplicant,
      deleteApplicant,
      deleteSelected,
      prevStep,
      nextStep,
      makeDecision,
      completeReview,
      closeWizard,
      openNewApplicationModal,
      refreshData,
      exportToCSV,
      prevPage,
      nextPage,
      goToPage,
      viewAllActivity,
      scheduleInterview,
      sendEmail,
      generateReport,
      addNote
    }
  }
}
</script>

<style scoped>
/* Base Styles */
.recruitment-container {
  min-height: 100vh;
  background-color: #f9fafb;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}

/* Header */
.header {
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 1.5rem;
}

.header-content {
  max-width: 100%;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .header-content {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.header-left {
  flex: 1;
}

.page-title {
  font-size: 1.875rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.25rem;
}

.page-subtitle {
  color: #6b7280;
  font-size: 0.875rem;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

/* Department Tabs */
.department-tabs {
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
  padding: 0.5rem;
  overflow-x: auto;
}

.tabs-container {
  display: flex;
  gap: 0.25rem;
  min-width: max-content;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  background-color: white;
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.tab-btn:hover {
  background-color: #f9fafb;
  border-color: #d1d5db;
}

.tab-btn.active {
  background-color: #1e40af;
  color: white;
  border-color: #1e40af;
}

.tab-icon {
  font-size: 1rem;
}

.tab-count {
  background-color: #e5e7eb;
  color: #374151;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
}

.tab-btn.active .tab-count {
  background-color: rgba(255, 255, 255, 0.2);
  color: white;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  border: 1px solid transparent;
  gap: 0.5rem;
  white-space: nowrap;
}

.btn-primary {
  background-color: #3b82f6;
  color: white;
}

.btn-primary:hover {
  background-color: #2563eb;
}

.btn-secondary {
  background-color: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}

.btn-outline {
  background-color: white;
  color: #374151;
  border-color: #d1d5db;
}

.btn-outline:hover {
  background-color: #f9fafb;
}

.btn-danger {
  background-color: #ef4444;
  color: white;
}

.btn-danger:hover {
  background-color: #dc2626;
}

.btn-danger:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-icon {
  font-size: 1.25rem;
  font-weight: 600;
}

/* Stats Container */
.stats-container {
  padding: 1.5rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

@media (min-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

.stat-card {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: box-shadow 0.2s;
}

.stat-card:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.stat-icon-wrapper {
  width: 3rem;
  height: 3rem;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon {
  width: 1.5rem;
  height: 1.5rem;
}

.stat-info {
  flex: 1;
}

.stat-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.stat-trend {
  font-size: 0.875rem;
  font-weight: 500;
}

.stat-trend.positive {
  color: #10b981;
}

.stat-trend.negative {
  color: #ef4444;
}

/* Content Grid */
.content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  padding: 0 1.5rem 2rem;
}

@media (min-width: 1024px) {
  .content-grid {
    grid-template-columns: 2fr 1fr;
  }
}

.left-column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.right-column {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Search and Filters */
.search-filters {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 768px) {
  .search-filters {
    flex-direction: row;
    align-items: center;
  }
}

.search-box {
  position: relative;
  flex: 1;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1.25rem;
  height: 1.25rem;
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 0.5rem 0.75rem 0.5rem 2.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filters {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  background-color: white;
  color: #374151;
}

.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
}

/* Table Container */
.table-container {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  overflow: hidden;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.table-header {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

@media (min-width: 768px) {
  .table-header {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.table-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.table-actions {
  display: flex;
  gap: 0.5rem;
}

/* Table Styles */
.table-wrapper {
  overflow-x: auto;
  flex: 1;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 800px;
}

.table-th {
  padding: 0.75rem 1rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background-color: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  white-space: nowrap;
}

.table-tr {
  border-bottom: 1px solid #e5e7eb;
}

.table-tr:hover {
  background-color: #f9fafb;
}

.table-tr.selected-row {
  background-color: #f0f9ff;
}

.table-td {
  padding: 1rem;
  font-size: 0.875rem;
  color: #374151;
}

/* Applicant Cell */
.applicant-cell {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.avatar {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.375rem;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.875rem;
  flex-shrink: 0;
}

.applicant-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.applicant-name {
  font-weight: 600;
  color: #111827;
}

.applicant-email {
  color: #6b7280;
  font-size: 0.75rem;
}

.applicant-department {
  margin-top: 0.25rem;
}

.dept-badge {
  background-color: #f3f4f6;
  color: #374151;
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

/* Position Tag */
.position-tag {
  background-color: #f3f4f6;
  color: #374151;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
}

/* Status Badge */
.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  font-weight: 500;
  display: inline-block;
}

.status-badge.pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-badge.shortlisted {
  background-color: #d1fae5;
  color: #065f46;
}

.status-badge.interview {
  background-color: #dbeafe;
  color: #1e40af;
}

.status-badge.hired {
  background-color: #dcfce7;
  color: #166534;
}

.status-badge.rejected {
  background-color: #fee2e2;
  color: #991b1b;
}

/* Rating Display */
.rating-display {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stars {
  display: flex;
  gap: 0.125rem;
}

.star {
  color: #d1d5db;
  font-size: 0.875rem;
}

.star.filled {
  color: #fbbf24;
}

.rating-text {
  font-size: 0.75rem;
  color: #6b7280;
}

/* Action Buttons */
.action-buttons {
  display: flex;
  gap: 0.25rem;
}

.action-btn {
  width: 2rem;
  height: 2rem;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.action-btn:hover {
  transform: translateY(-1px);
}

.view-btn {
  background-color: #dbeafe;
  color: #1d4ed8;
}

.edit-btn {
  background-color: #fef3c7;
  color: #92400e;
}

.delete-btn {
  background-color: #fee2e2;
  color: #dc2626;
}

/* Checkbox */
.checkbox {
  width: 1rem;
  height: 1rem;
  border-radius: 0.25rem;
  border: 1px solid #d1d5db;
  cursor: pointer;
}

.checkbox:checked {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

/* Pagination */
.pagination {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 640px) {
  .pagination {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.pagination-info {
  font-size: 0.875rem;
  color: #6b7280;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.pagination-btn {
  width: 2rem;
  height: 2rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  background-color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #f3f4f6;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 0.25rem;
}

.page-btn {
  min-width: 2rem;
  height: 2rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  background-color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover {
  background-color: #f3f4f6;
}

.page-btn.active {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

/* Right Column Cards */
.stats-card {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
}

.stats-card-header {
  margin-bottom: 1rem;
}

.stats-card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
}

.stats-card-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
}

.dept-stats-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.dept-stat {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background-color: #f9fafb;
  border-radius: 0.375rem;
}

.dept-stat-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.dept-stat-info {
  flex: 1;
}

.dept-stat-value {
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827;
  margin-bottom: 0.125rem;
}

.dept-stat-label {
  font-size: 0.75rem;
  color: #6b7280;
}

.activity-card {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
}

.activity-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.activity-card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
}

.activity-view-all {
  font-size: 0.875rem;
  color: #3b82f6;
  background: none;
  border: none;
  cursor: pointer;
}

.activity-view-all:hover {
  text-decoration: underline;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  background-color: #f9fafb;
  border-radius: 0.375rem;
}

.activity-avatar {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-text {
  font-size: 0.875rem;
  color: #374151;
  margin-bottom: 0.25rem;
}

.activity-user {
  font-weight: 600;
}

.activity-applicant {
  font-weight: 500;
  color: #3b82f6;
}

.activity-time {
  font-size: 0.75rem;
  color: #6b7280;
}

.actions-card {
  background-color: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  padding: 1rem;
}

.actions-card-title {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.quick-action {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  background-color: white;
  cursor: pointer;
  transition: all 0.2s;
}

.quick-action:hover {
  background-color: #f9fafb;
  border-color: #d1d5db;
  transform: translateY(-2px);
}

.action-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: #dbeafe;
  color: #1d4ed8;
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #374151;
  text-align: center;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  padding: 1rem;
}

.modal {
  background-color: white;
  border-radius: 0.5rem;
  width: 100%;
  max-width: 900px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.modal-dept-badge {
  width: 3rem;
  height: 3rem;
  border-radius: 0.5rem;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 600;
}

.modal-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.modal-subtitle {
  font-size: 0.875rem;
  color: #6b7280;
}

.modal-close {
  width: 2rem;
  height: 2rem;
  border-radius: 0.375rem;
  border: none;
  background-color: #f3f4f6;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.modal-close:hover {
  background-color: #e5e7eb;
}

/* Wizard Steps */
.wizard-steps {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  position: relative;
}

.wizard-steps::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 1rem;
  right: 1rem;
  height: 2px;
  background-color: #e5e7eb;
  transform: translateY(-50%);
  z-index: 1;
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  position: relative;
  z-index: 2;
}

.step-number {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background-color: #f3f4f6;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  border: 2px solid #e5e7eb;
  transition: all 0.2s;
}

.step.active .step-number {
  background-color: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.step-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
}

.step.active .step-label {
  color: #111827;
}

/* Wizard Content */
.wizard-content {
  padding: 1rem;
}

.step-content {
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Step 1 Styles */
.applicant-profile {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.profile-avatar-large {
  width: 4rem;
  height: 4rem;
  border-radius: 0.5rem;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 600;
  flex-shrink: 0;
}

.profile-info {
  flex: 1;
}

.profile-name {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
}

.profile-email {
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.profile-phone {
  color: #4b5563;
  font-weight: 500;
}

.info-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

@media (min-width: 640px) {
  .info-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

.info-card {
  background-color: #f9fafb;
  border-radius: 0.375rem;
  padding: 1rem;
}

.info-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
}

.info-label {
  font-size: 0.875rem;
  color: #6b7280;
}

.info-value {
  font-size: 0.875rem;
  font-weight: 500;
  color: #111827;
  text-align: right;
}

/* Step 2 Styles */
.step-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 1rem;
}

.section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.75rem;
}

.skills-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.skill-tag {
  background-color: #dbeafe;
  color: #1e40af;
  padding: 0.375rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.experience-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.experience-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.375rem;
}

.exp-icon {
  width: 2.5rem;
  height: 2.5rem;
  background-color: #dbeafe;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1d4ed8;
  flex-shrink: 0;
}

.exp-details {
  flex: 1;
}

.exp-company {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
}

.exp-role {
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.exp-duration {
  font-size: 0.875rem;
  color: #9ca3af;
}

/* Step 3 Styles */
.education-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.education-item {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  padding: 1rem;
  background-color: #f9fafb;
  border-radius: 0.375rem;
}

.edu-icon {
  width: 2.5rem;
  height: 2.5rem;
  background-color: #d1fae5;
  border-radius: 0.375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #065f46;
  flex-shrink: 0;
}

.edu-details {
  flex: 1;
}

.edu-degree {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.25rem;
}

.edu-institution {
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.edu-year {
  font-size: 0.875rem;
  color: #9ca3af;
}

.certifications-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.cert-tag {
  background-color: #dcfce7;
  color: #166534;
  padding: 0.375rem 0.75rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  font-weight: 500;
}

/* Step 4 Styles */
.review-summary {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

@media (min-width: 640px) {
  .review-summary {
    grid-template-columns: repeat(2, 1fr);
  }
}

.summary-card {
  background-color: #f9fafb;
  border-radius: 0.375rem;
  padding: 1rem;
  text-align: center;
}

.summary-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.score-display {
  display: flex;
  align-items: baseline;
  justify-content: center;
  gap: 0.25rem;
  margin-bottom: 0.5rem;
}

.score-value {
  font-size: 2rem;
  font-weight: 700;
  color: #111827;
}

.score-label {
  font-size: 1rem;
  color: #6b7280;
}

.fit-meter {
  width: 100%;
  height: 0.5rem;
  background-color: #e5e7eb;
  border-radius: 0.25rem;
  margin: 1rem 0;
  overflow: hidden;
}

.fit-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  border-radius: 0.25rem;
  transition: width 0.3s ease;
}

.summary-text {
  font-size: 0.875rem;
  color: #6b7280;
}

.decision-section {
  background-color: #f9fafb;
  border-radius: 0.375rem;
  padding: 1rem;
}

.decision-buttons {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

@media (min-width: 640px) {
  .decision-buttons {
    flex-direction: row;
  }
}

.decision-btn {
  flex: 1;
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.decision-btn:hover {
  transform: translateY(-1px);
}

.decision-btn.reject {
  background-color: #fee2e2;
  color: #dc2626;
}

.decision-btn.shortlist {
  background-color: #d1fae5;
  color: #065f46;
}

.decision-btn.hire {
  background-color: #3b82f6;
  color: white;
}

.notes-section {
  margin-top: 1rem;
}

.notes-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.notes-textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  resize: vertical;
}

.notes-textarea:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Wizard Navigation */
.wizard-navigation {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.wizard-spacer {
  flex: 1;
}

.wizard-btn {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.wizard-btn.secondary {
  background-color: #f3f4f6;
  color: #374151;
}

.wizard-btn.secondary:hover {
  background-color: #e5e7eb;
}

.wizard-btn.primary {
  background-color: #3b82f6;
  color: white;
}

.wizard-btn.primary:hover {
  background-color: #2563eb;
}

.wizard-btn.success {
  background-color: #10b981;
  color: white;
}

.wizard-btn.success:hover {
  background-color: #059669;
}

/* Toast Notification */
.toast {
  position: fixed;
  bottom: 1rem;
  right: 1rem;
  background-color: white;
  border-radius: 0.375rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border-left: 4px solid;
  animation: slideInRight 0.3s ease;
  z-index: 100;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.toast.success {
  border-left-color: #10b981;
}

.toast.error {
  border-left-color: #ef4444;
}

.toast.info {
  border-left-color: #3b82f6;
}

.toast-content {
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 300px;
}

.toast-icon {
  flex-shrink: 0;
}

.toast.success .toast-icon {
  color: #10b981;
}

.toast.error .toast-icon {
  color: #ef4444;
}

.toast.info .toast-icon {
  color: #3b82f6;
}

.toast-message {
  flex: 1;
}

.toast-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #111827;
  margin-bottom: 0.125rem;
}

.toast-text {
  font-size: 0.875rem;
  color: #6b7280;
}

.toast-close {
  width: 2rem;
  height: 2rem;
  border-radius: 0.375rem;
  border: none;
  background-color: #f3f4f6;
  color: #6b7280;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
  flex-shrink: 0;
}

.toast-close:hover {
  background-color: #e5e7eb;
}
</style>