<template>
  <div class="user-management">
    <!-- Page Header -->
    <div class="page-header">
      <div class="header-content">
        <div>
          <h1 class="page-title">User Management</h1>
          <p class="page-subtitle">Control system access and manage user roles</p>
        </div>
        <div class="header-actions">
          <button @click="showAddUserModal = true" class="add-user-btn">
            <i class="fas fa-plus mr-2"></i> Add New User
          </button>
        </div>
      </div>
      
      <!-- Quick Stats -->
      <div class="quick-stats">
        <div class="stat-card" @click="activeTab = 'all'">
          <div class="stat-icon">
            <i class="fas fa-users"></i>
          </div>
          <div class="stat-content">
            <h3>142</h3>
            <p>Total Users</p>
          </div>
        </div>
        <div class="stat-card" @click="activeTab = 'admin'">
          <div class="stat-icon admin">
            <i class="fas fa-user-shield"></i>
          </div>
          <div class="stat-content">
            <h3>5</h3>
            <p>Admins</p>
          </div>
        </div>
        <div class="stat-card" @click="activeTab = 'distributor'">
          <div class="stat-icon distributor">
            <i class="fas fa-truck"></i>
          </div>
          <div class="stat-content">
            <h3>18</h3>
            <p>Distributors</p>
          </div>
        </div>
        <div class="stat-card" @click="activeTab = 'service-provider'">
          <div class="stat-icon service">
            <i class="fas fa-tools"></i>
          </div>
          <div class="stat-content">
            <h3>45</h3>
            <p>Service Providers</p>
          </div>
        </div>
        <div class="stat-card" @click="activeTab = 'client'">
          <div class="stat-icon client">
            <i class="fas fa-user"></i>
          </div>
          <div class="stat-content">
            <h3>74</h3>
            <p>Clients</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters and Tabs -->
    <div class="filters-section">
      <div class="tabs">
        <button @click="activeTab = 'all'" :class="['tab-btn', { 'active': activeTab === 'all' }]">
          <i class="fas fa-users mr-2"></i> All Users
        </button>
        <button @click="activeTab = 'admin'" :class="['tab-btn', { 'active': activeTab === 'admin' }]">
          <i class="fas fa-user-shield mr-2"></i> Admins
        </button>
        <button @click="activeTab = 'distributor'" :class="['tab-btn', { 'active': activeTab === 'distributor' }]">
          <i class="fas fa-truck mr-2"></i> Distributors
        </button>
        <button @click="activeTab = 'service-provider'" :class="['tab-btn', { 'active': activeTab === 'service-provider' }]">
          <i class="fas fa-tools mr-2"></i> Service Providers
        </button>
        <button @click="activeTab = 'client'" :class="['tab-btn', { 'active': activeTab === 'client' }]">
          <i class="fas fa-user mr-2"></i> Clients
        </button>
      </div>
      
      <div class="search-filter">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input v-model="searchQuery" type="text" placeholder="Search users..." @input="filterUsers">
        </div>
        <select v-model="statusFilter" @change="filterUsers" class="status-filter">
          <option value="all">All Status</option>
          <option value="active">Active Only</option>
          <option value="inactive">Inactive Only</option>
        </select>
        <button class="filter-btn">
          <i class="fas fa-filter"></i> More Filters
        </button>
      </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-container">
      <div class="table-header">
        <h3>User List</h3>
        <p class="text-gray-500 text-sm">{{ filteredUsers.length }} users found</p>
      </div>
      
      <div class="table-responsive">
        <table class="users-table">
          <thead>
            <tr>
              <th>User</th>
              <th>Role</th>
              <th>Email</th>
              <th>Date Added</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>
                <div class="user-info">
                  <div class="avatar" :style="{ backgroundColor: user.avatarColor }">
                    {{ user.initials }}
                  </div>
                  <div>
                    <p class="font-medium">{{ user.name }}</p>
                    <p class="text-gray-500 text-sm">{{ user.userId }}</p>
                  </div>
                </div>
              </td>
              <td>
                <span class="role-badge" :class="user.roleClass">
                  {{ user.role }}
                </span>
              </td>
              <td>{{ user.email }}</td>
              <td>{{ user.dateAdded }}</td>
              <td>
                <span class="status-badge" :class="user.status === 'Active' ? 'active' : 'inactive'">
                  <span class="status-dot" :class="user.status === 'Active' ? 'bg-green-500' : 'bg-red-500'"></span>
                  {{ user.status }}
                </span>
              </td>
              <td>
                <div class="action-buttons">
                  <button @click="editUser(user)" class="action-btn edit" title="Edit User">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="toggleUserStatus(user)" class="action-btn status" 
                          :title="user.status === 'Active' ? 'Deactivate User' : 'Activate User'">
                    <i v-if="user.status === 'Active'" class="fas fa-user-slash"></i>
                    <i v-else class="fas fa-user-check"></i>
                  </button>
                  <button class="action-btn more" title="More Options">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div class="pagination">
        <button class="page-btn" :disabled="currentPage === 1" @click="currentPage--">
          <i class="fas fa-chevron-left"></i>
        </button>
        <button v-for="page in totalPages" :key="page" 
                @click="currentPage = page"
                :class="['page-number', { 'active': currentPage === page }]">
          {{ page }}
        </button>
        <button class="page-btn" :disabled="currentPage === totalPages" @click="currentPage++">
          <i class="fas fa-chevron-right"></i>
        </button>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h2>Add New User</h2>
          <button @click="closeAddUserModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="addNewUser">
            <div class="form-grid">
              <div class="form-group">
                <label>Full Name *</label>
                <input v-model="newUser.name" type="text" placeholder="Enter full name" required>
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input v-model="newUser.email" type="email" placeholder="user@example.com" required>
              </div>
              <div class="form-group">
                <label>Assign Role *</label>
                <select v-model="newUser.role" required>
                  <option value="">Select a role</option>
                  <option value="admin">Administrator</option>
                  <option value="distributor">Distributor</option>
                  <option value="service-provider">Service Provider</option>
                  <option value="client">Client</option>
                </select>
              </div>
              <div class="form-group">
                <label>Initial Password</label>
                <div class="password-input">
                  <input v-model="newUser.password" :type="showPassword ? 'text' : 'password'" 
                         value="default123" readonly>
                  <button type="button" @click="showPassword = !showPassword" class="password-toggle">
                    <i v-if="showPassword" class="fas fa-eye-slash"></i>
                    <i v-else class="fas fa-eye"></i>
                  </button>
                </div>
                <p class="text-xs text-gray-500 mt-1">User will be prompted to change password on first login</p>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" @click="closeAddUserModal" class="btn-cancel">Cancel</button>
              <button type="submit" class="btn-primary">
                <i class="fas fa-plus mr-2"></i> Create User
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditUserModal" class="modal-overlay">
      <div class="modal">
        <div class="modal-header">
          <h2>Edit User</h2>
          <button @click="closeEditUserModal" class="close-btn">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="saveEditedUser">
            <div class="form-grid">
              <div class="form-group">
                <label>Full Name *</label>
                <input v-model="editingUser.name" type="text" required>
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input v-model="editingUser.email" type="email" required>
              </div>
              <div class="form-group">
                <label>Assign Role *</label>
                <select v-model="editingUser.role" required>
                  <option value="admin">Administrator</option>
                  <option value="distributor">Distributor</option>
                  <option value="service-provider">Service Provider</option>
                  <option value="client">Client</option>
                </select>
              </div>
              <div class="form-group">
                <label>Account Status</label>
                <div class="checkbox-group">
                  <label class="checkbox-label">
                    <input v-model="editingUser.isActive" type="checkbox" class="checkbox">
                    <span>Active Account</span>
                  </label>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" @click="closeEditUserModal" class="btn-cancel">Cancel</button>
              <button type="submit" class="btn-primary">Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserManagement',
  data() {
    return {
      activeTab: 'all',
      searchQuery: '',
      statusFilter: 'all',
      currentPage: 1,
      itemsPerPage: 10,
      showAddUserModal: false,
      showEditUserModal: false,
      showPassword: false,
      newUser: {
        name: '',
        email: '',
        role: '',
        password: 'default123'
      },
      editingUser: {
        id: null,
        name: '',
        email: '',
        role: '',
        isActive: true
      },
      users: [
        {
          id: 1,
          name: 'Maria Johnson',
          initials: 'MJ',
          userId: 'ADM-001',
          role: 'Administrator',
          roleClass: 'admin-badge',
          email: 'maria.johnson@paintims.com',
          dateAdded: 'Jan 15, 2024',
          status: 'Active',
          avatarColor: '#4A90E2'
        },
        {
          id: 2,
          name: 'Carlos Reyes',
          initials: 'CR',
          userId: 'DIST-012',
          role: 'Distributor',
          roleClass: 'distributor-badge',
          email: 'carlos.reyes@cavitepaint.com',
          dateAdded: 'Feb 28, 2024',
          status: 'Active',
          avatarColor: '#51C16B'
        },
        {
          id: 3,
          name: 'Ana Santos',
          initials: 'AS',
          userId: 'SP-045',
          role: 'Service Provider',
          roleClass: 'service-badge',
          email: 'ana.santos@paintservices.ph',
          dateAdded: 'Mar 10, 2024',
          status: 'Active',
          avatarColor: '#9B59B6'
        },
        {
          id: 4,
          name: 'Thomas Gomez',
          initials: 'TG',
          userId: 'CL-128',
          role: 'Client',
          roleClass: 'client-badge',
          email: 'thomas.gomez@gmail.com',
          dateAdded: 'Mar 15, 2024',
          status: 'Active',
          avatarColor: '#7F8C8D'
        },
        {
          id: 5,
          name: 'Luis dela Cruz',
          initials: 'LD',
          userId: 'DIST-008',
          role: 'Distributor',
          roleClass: 'distributor-badge',
          email: 'luis.delacruz@oldpaint.ph',
          dateAdded: 'Nov 22, 2023',
          status: 'Inactive',
          avatarColor: '#E74C3C'
        },
        {
          id: 6,
          name: 'Sarah Lim',
          initials: 'SL',
          userId: 'ADM-002',
          role: 'Administrator',
          roleClass: 'admin-badge',
          email: 'sarah.lim@paintims.com',
          dateAdded: 'Mar 01, 2024',
          status: 'Active',
          avatarColor: '#3498DB'
        },
        {
          id: 7,
          name: 'Miguel Torres',
          initials: 'MT',
          userId: 'SP-067',
          role: 'Service Provider',
          roleClass: 'service-badge',
          email: 'miguel.torres@paintservices.ph',
          dateAdded: 'Feb 14, 2024',
          status: 'Active',
          avatarColor: '#8E44AD'
        },
        {
          id: 8,
          name: 'Jennifer Lee',
          initials: 'JL',
          userId: 'CL-201',
          role: 'Client',
          roleClass: 'client-badge',
          email: 'jennifer.lee@gmail.com',
          dateAdded: 'Mar 20, 2024',
          status: 'Active',
          avatarColor: '#34495E'
        }
      ]
    }
  },
  computed: {
    filteredUsers() {
      return this.users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            user.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            user.userId.toLowerCase().includes(this.searchQuery.toLowerCase());
        
        const matchesStatus = this.statusFilter === 'all' || 
                            (this.statusFilter === 'active' && user.status === 'Active') ||
                            (this.statusFilter === 'inactive' && user.status === 'Inactive');
        
        const matchesRole = this.activeTab === 'all' || 
                          (this.activeTab === 'admin' && user.role === 'Administrator') ||
                          (this.activeTab === 'distributor' && user.role === 'Distributor') ||
                          (this.activeTab === 'service-provider' && user.role === 'Service Provider') ||
                          (this.activeTab === 'client' && user.role === 'Client');
        
        return matchesSearch && matchesStatus && matchesRole;
      });
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
    },
    paginatedUsers() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredUsers.slice(start, end);
    }
  },
  methods: {
    filterUsers() {
      this.currentPage = 1;
    },
    editUser(user) {
      const roleMap = {
        'Administrator': 'admin',
        'Distributor': 'distributor',
        'Service Provider': 'service-provider',
        'Client': 'client'
      };
      
      this.editingUser = {
        id: user.id,
        name: user.name,
        email: user.email,
        role: roleMap[user.role],
        isActive: user.status === 'Active'
      };
      this.showEditUserModal = true;
    },
    saveEditedUser() {
      const userIndex = this.users.findIndex(u => u.id === this.editingUser.id);
      if (userIndex !== -1) {
        const roleMap = {
          'admin': 'Administrator',
          'distributor': 'Distributor',
          'service-provider': 'Service Provider',
          'client': 'Client'
        };
        
        const roleClassMap = {
          'admin': 'admin-badge',
          'distributor': 'distributor-badge',
          'service-provider': 'service-badge',
          'client': 'client-badge'
        };
        
        const avatarColorMap = {
          'admin': '#4A90E2',
          'distributor': '#51C16B',
          'service-provider': '#9B59B6',
          'client': '#7F8C8D'
        };
        
        this.users[userIndex].name = this.editingUser.name;
        this.users[userIndex].email = this.editingUser.email;
        this.users[userIndex].role = roleMap[this.editingUser.role];
        this.users[userIndex].roleClass = roleClassMap[this.editingUser.role];
        this.users[userIndex].avatarColor = avatarColorMap[this.editingUser.role];
        this.users[userIndex].initials = this.getInitials(this.editingUser.name);
        this.users[userIndex].status = this.editingUser.isActive ? 'Active' : 'Inactive';
      }
      this.closeEditUserModal();
    },
    addNewUser() {
      if (!this.newUser.name || !this.newUser.email || !this.newUser.role) {
        alert('Please fill in all required fields');
        return;
      }
      
      const roleMap = {
        'admin': 'Administrator',
        'distributor': 'Distributor',
        'service-provider': 'Service Provider',
        'client': 'Client'
      };
      
      const roleClassMap = {
        'admin': 'admin-badge',
        'distributor': 'distributor-badge',
        'service-provider': 'service-badge',
        'client': 'client-badge'
      };
      
      const avatarColorMap = {
        'admin': '#4A90E2',
        'distributor': '#51C16B',
        'service-provider': '#9B59B6',
        'client': '#7F8C8D'
      };
      
      const newUser = {
        id: this.users.length + 1,
        name: this.newUser.name,
        initials: this.getInitials(this.newUser.name),
        userId: this.generateUserId(this.newUser.role),
        role: roleMap[this.newUser.role],
        roleClass: roleClassMap[this.newUser.role],
        email: this.newUser.email,
        dateAdded: new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }),
        status: 'Active',
        avatarColor: avatarColorMap[this.newUser.role]
      };
      
      this.users.unshift(newUser);
      this.closeAddUserModal();
      alert('User created successfully!');
    },
    toggleUserStatus(user) {
      const userIndex = this.users.findIndex(u => u.id === user.id);
      if (userIndex !== -1) {
        if (user.status === 'Active') {
          if (confirm(`Are you sure you want to deactivate ${user.name}? They will not be able to log in.`)) {
            this.users[userIndex].status = 'Inactive';
            this.users[userIndex].avatarColor = '#E74C3C';
          }
        } else {
          if (confirm(`Are you sure you want to activate ${user.name}?`)) {
            this.users[userIndex].status = 'Active';
            const colorMap = {
              'Administrator': '#4A90E2',
              'Distributor': '#51C16B',
              'Service Provider': '#9B59B6',
              'Client': '#7F8C8D'
            };
            this.users[userIndex].avatarColor = colorMap[user.role] || '#4A90E2';
          }
        }
      }
    },
    getInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
    },
    generateUserId(role) {
      const prefixes = {
        'admin': 'ADM',
        'distributor': 'DIST',
        'service-provider': 'SP',
        'client': 'CL'
      };
      
      const prefix = prefixes[role] || 'USR';
      const count = this.users.filter(u => u.role === role).length + 1;
      return `${prefix}-${count.toString().padStart(3, '0')}`;
    },
    closeAddUserModal() {
      this.showAddUserModal = false;
      this.newUser = {
        name: '',
        email: '',
        role: '',
        password: 'default123'
      };
      this.showPassword = false;
    },
    closeEditUserModal() {
      this.showEditUserModal = false;
    }
  }
}
</script>

<style scoped>
.user-management {
  padding: 20px;
}

/* Page Header */
.page-header {
  margin-bottom: 24px;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #1a1a2e;
  margin: 0;
}

.page-subtitle {
  color: #666;
  margin: 4px 0 0 0;
}

.add-user-btn {
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.add-user-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(74, 144, 226, 0.2);
}

/* Quick Stats */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 16px;
  margin-top: 16px;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
}

.stat-icon.admin { background: linear-gradient(45deg, #3498DB, #2980B9); }
.stat-icon.distributor { background: linear-gradient(45deg, #27AE60, #229954); }
.stat-icon.service { background: linear-gradient(45deg, #8E44AD, #732D91); }
.stat-icon.client { background: linear-gradient(45deg, #34495E, #2C3E50); }

.stat-content h3 {
  font-size: 24px;
  font-weight: 700;
  margin: 0;
  color: #1a1a2e;
}

.stat-content p {
  color: #666;
  margin: 4px 0 0 0;
  font-size: 14px;
}

/* Filters Section */
.filters-section {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 24px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.tab-btn {
  padding: 12px 20px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #4a5568;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.tab-btn:hover {
  border-color: #4A90E2;
  color: #4A90E2;
}

.tab-btn.active {
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  color: white;
  border-color: transparent;
}

.search-filter {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  align-items: center;
}

.search-box {
  flex: 1;
  min-width: 200px;
  position: relative;
}

.search-box i {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #a0aec0;
}

.search-box input {
  width: 100%;
  padding: 12px 12px 12px 44px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.search-box input:focus {
  outline: none;
  border-color: #4A90E2;
}

.status-filter {
  padding: 12px 20px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #4a5568;
  font-size: 14px;
  cursor: pointer;
}

.filter-btn {
  padding: 12px 20px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #4a5568;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.filter-btn:hover {
  border-color: #4A90E2;
  color: #4A90E2;
}

/* Users Table */
.users-table-container {
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.table-header {
  padding: 24px 24px 0;
}

.table-header h3 {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
  color: #1a1a2e;
}

.table-responsive {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
}

.users-table thead {
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
}

.users-table th {
  padding: 16px 24px;
  text-align: left;
  font-weight: 600;
  color: #4a5568;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.users-table tbody tr {
  border-bottom: 1px solid #e2e8f0;
  transition: background-color 0.3s ease;
}

.users-table tbody tr:hover {
  background-color: #f8fafc;
}

.users-table td {
  padding: 20px 24px;
  vertical-align: middle;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 14px;
}

.role-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

.admin-badge {
  background: rgba(52, 152, 219, 0.1);
  color: #2980B9;
}

.distributor-badge {
  background: rgba(39, 174, 96, 0.1);
  color: #229954;
}

.service-badge {
  background: rgba(142, 68, 173, 0.1);
  color: #732D91;
}

.client-badge {
  background: rgba(52, 73, 94, 0.1);
  color: #2C3E50;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.status-badge.active {
  background: rgba(39, 174, 96, 0.1);
  color: #229954;
}

.status-badge.inactive {
  background: rgba(231, 76, 60, 0.1);
  color: #C0392B;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.action-buttons {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 2px solid #e2e8f0;
  background: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.action-btn.edit {
  color: #3498DB;
}

.action-btn.edit:hover {
  background: rgba(52, 152, 219, 0.1);
  border-color: #3498DB;
}

.action-btn.status {
  color: #E74C3C;
}

.action-btn.status:hover {
  background: rgba(231, 76, 60, 0.1);
  border-color: #E74C3C;
}

.action-btn.more {
  color: #7F8C8D;
}

.action-btn.more:hover {
  background: rgba(127, 140, 141, 0.1);
  border-color: #7F8C8D;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
  padding: 24px;
  border-top: 1px solid #e2e8f0;
}

.page-btn, .page-number {
  min-width: 40px;
  height: 40px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #4a5568;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-btn:hover:not(:disabled) {
  border-color: #4A90E2;
  color: #4A90E2;
}

.page-number:hover {
  border-color: #4A90E2;
  color: #4A90E2;
}

.page-number.active {
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  color: white;
  border-color: transparent;
}

/* Modals */
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
  z-index: 1000;
  padding: 20px;
}

.modal {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 24px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
  color: #1a1a2e;
}

.close-btn {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #e2e8f0;
  background: white;
  color: #4a5568;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.close-btn:hover {
  border-color: #E74C3C;
  color: #E74C3C;
}

.modal-body {
  padding: 24px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #4a5568;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #4A90E2;
}

.password-input {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #a0aec0;
  cursor: pointer;
}

.checkbox-group {
  margin-top: 8px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.checkbox {
  width: 18px;
  height: 18px;
  border: 2px solid #e2e8f0;
  border-radius: 4px;
  cursor: pointer;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding-top: 24px;
  border-top: 1px solid #e2e8f0;
}

.btn-cancel {
  padding: 12px 24px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #4a5568;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  border-color: #E74C3C;
  color: #E74C3C;
}

.btn-primary {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  background: linear-gradient(45deg, #4A90E2, #9B59B6);
  color: white;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(74, 144, 226, 0.2);
}

/* Responsive Design */
@media (max-width: 768px) {
  .user-management {
    padding: 16px;
  }
  
  .header-content {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .quick-stats {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .tabs {
    flex-direction: column;
  }
  
  .tab-btn {
    width: 100%;
  }
  
  .search-filter {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-box,
  .status-filter,
  .filter-btn {
    width: 100%;
  }
  
  .users-table {
    min-width: 800px;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .modal {
    margin: 0;
    max-height: 95vh;
  }
}

@media (max-width: 480px) {
  .page-title {
    font-size: 24px;
  }
  
  .quick-stats {
    grid-template-columns: 1fr;
  }
  
  .stat-card {
    padding: 16px;
  }
  
  .modal-header {
    padding: 20px;
  }
  
  .modal-body {
    padding: 20px;
  }
}
</style>