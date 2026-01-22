import axios from '@/utils/axios'
import Toastify from 'toastify-js'
import "toastify-js/src/toastify.css"

export default {
  name: 'CoreDepartments',
  filters: {
    capitalize(value) {
      if (!value) return ''
      value = value.toString()
      return value.charAt(0).toUpperCase() + value.slice(1)
    }
  },
  data() {
    return {
      activeTab: 'operational',
      loading: false,
      
      // Operational Distributors
      showOperationalForm: false,
      creatingOperational: false,
      operationalDistributors: [],
      operationalStats: null,
      operationalPagination: null,
      operationalSearch: '',
      operationalFilterStatus: '',
      showOperationalFilterDropdown: false,
      currentOperationalPage: 1,
      showOperationalPassword: false,
      showOperationalConfirmPassword: false,
      operationalPhoneError: '',
      
      // HR Managers
      showHRForm: false,
      creatingHR: false,
      hrManagers: [],
      hrStats: null,
      hrPagination: null,
      hrSearch: '',
      hrFilterStatus: '',
      hrFilterEmploymentType: '',
      showHRFilterDropdown: false,
      currentHRPage: 1,
      showHRPassword: false,
      showHRConfirmPassword: false,
      hrPhoneError: '',
      
      // Finance Managers
      showFinanceForm: false,
      creatingFinance: false,
      financeManagers: [],
      financeStats: null,
      financePagination: null,
      financeSearch: '',
      financeFilterStatus: '',
      financeFilterEmploymentType: '',
      showFinanceFilterDropdown: false,
      currentFinancePage: 1,
      showFinancePassword: false,
      showFinanceConfirmPassword: false,
      financePhoneError: '',
      
      // Common
      verificationData: null,
      recentActivities: [
        {
          id: 1,
          message: 'New operational distributor added: John Smith',
          time: '2 hours ago',
          icon: 'M12 4v16m8-8H4',
          iconBg: 'bg-blue-100',
          iconColor: 'text-blue-600'
        },
        {
          id: 2,
          message: 'HR manager activated: Maria Garcia',
          time: '1 day ago',
          icon: 'M5 13l4 4L19 7',
          iconBg: 'bg-green-100',
          iconColor: 'text-green-600'
        },
        {
          id: 3,
          message: 'Finance manager added: Robert Johnson',
          time: '2 days ago',
          icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
          iconBg: 'bg-purple-100',
          iconColor: 'text-purple-600'
        }
      ],
      
      // Forms
      operationalForm: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        password: '',
        password_confirmation: ''
      },
      
      hrForm: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        employment_type: '',
        hire_date: '',
        salary: '',
        position: 'HR Manager',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        resume: null,
        employment_contract: null,
        password: '',
        password_confirmation: ''
      },
      
      financeForm: {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        employment_type: '',
        hire_date: '',
        salary: '',
        position: 'Finance Manager',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        resume: null,
        employment_contract: null,
        password: '',
        password_confirmation: ''
      },
      
      hrEmploymentTypes: [
        { value: 'full_time', label: 'Full Time' },
        { value: 'part_time', label: 'Part Time' },
        { value: 'contract', label: 'Contract' },
        { value: 'temporary', label: 'Temporary' }
      ],
      
      financeEmploymentTypes: [
        { value: 'full_time', label: 'Full Time' },
        { value: 'part_time', label: 'Part Time' },
        { value: 'contract', label: 'Contract' },
        { value: 'temporary', label: 'Temporary' }
      ]
    }
  },
  computed: {
    // Operational Distributor Computed Properties
    operationalPasswordRequirements() {
      const password = this.operationalForm.password
      return [
        {
          text: 'At least 8 characters',
          met: password.length >= 8
        },
        {
          text: 'Contains uppercase letter',
          met: /[A-Z]/.test(password)
        },
        {
          text: 'Contains lowercase letter',
          met: /[a-z]/.test(password)
        },
        {
          text: 'Contains number',
          met: /\d/.test(password)
        }
      ]
    },
    operationalPasswordMatch() {
      return this.operationalForm.password === this.operationalForm.password_confirmation
    },
    isOperationalFormValid() {
      return this.operationalForm.first_name &&
             this.operationalForm.last_name &&
             this.operationalForm.email &&
             this.operationalForm.phone &&
             this.operationalForm.phone.length === 11 &&
             /^\d+$/.test(this.operationalForm.phone) &&
             /^0/.test(this.operationalForm.phone) &&
             this.operationalForm.valid_id_type &&
             this.operationalForm.id_number &&
             this.operationalForm.valid_id_photo &&
             this.operationalForm.password &&
             this.operationalForm.password_confirmation &&
             this.operationalPasswordRequirements.every(req => req.met) &&
             this.operationalPasswordMatch
    },
    
    // HR Manager Computed Properties
    hrPasswordRequirements() {
      const password = this.hrForm.password
      return [
        {
          text: 'At least 8 characters',
          met: password.length >= 8
        },
        {
          text: 'Contains uppercase letter',
          met: /[A-Z]/.test(password)
        },
        {
          text: 'Contains lowercase letter',
          met: /[a-z]/.test(password)
        },
        {
          text: 'Contains number',
          met: /\d/.test(password)
        }
      ]
    },
    hrPasswordMatch() {
      return this.hrForm.password === this.hrForm.password_confirmation
    },
    isHRFormValid() {
      return this.hrForm.first_name &&
             this.hrForm.last_name &&
             this.hrForm.email &&
             this.hrForm.phone &&
             this.hrForm.phone.length === 11 &&
             /^\d+$/.test(this.hrForm.phone) &&
             /^0/.test(this.hrForm.phone) &&
             this.hrForm.employment_type &&
             this.hrForm.hire_date &&
             this.hrForm.salary &&
             this.hrForm.valid_id_type &&
             this.hrForm.id_number &&
             this.hrForm.valid_id_photo &&
             this.hrForm.password &&
             this.hrForm.password_confirmation &&
             this.hrPasswordRequirements.every(req => req.met) &&
             this.hrPasswordMatch
    },
    
    // Finance Manager Computed Properties
    financePasswordRequirements() {
      const password = this.financeForm.password
      return [
        {
          text: 'At least 8 characters',
          met: password.length >= 8
        },
        {
          text: 'Contains uppercase letter',
          met: /[A-Z]/.test(password)
        },
        {
          text: 'Contains lowercase letter',
          met: /[a-z]/.test(password)
        },
        {
          text: 'Contains number',
          met: /\d/.test(password)
        }
      ]
    },
    financePasswordMatch() {
      return this.financeForm.password === this.financeForm.password_confirmation
    },
    isFinanceFormValid() {
      return this.financeForm.first_name &&
             this.financeForm.last_name &&
             this.financeForm.email &&
             this.financeForm.phone &&
             this.financeForm.phone.length === 11 &&
             /^\d+$/.test(this.financeForm.phone) &&
             /^0/.test(this.financeForm.phone) &&
             this.financeForm.employment_type &&
             this.financeForm.hire_date &&
             this.financeForm.salary &&
             this.financeForm.valid_id_type &&
             this.financeForm.id_number &&
             this.financeForm.valid_id_photo &&
             this.financeForm.password &&
             this.financeForm.password_confirmation &&
             this.financePasswordRequirements.every(req => req.met) &&
             this.financePasswordMatch
    },
    
    // Common Computed Properties
    isVerifiedDistributor() {
      return this.verificationData && this.verificationData.status === 'approved'
    },
    filteredOperationalDistributors() {
      let filtered = this.operationalDistributors
      
      // Apply search filter
      if (this.operationalSearch) {
        const search = this.operationalSearch.toLowerCase()
        filtered = filtered.filter(distributor => {
          return distributor.full_name.toLowerCase().includes(search) ||
                 distributor.email?.toLowerCase().includes(search) ||
                 distributor.phone?.toLowerCase().includes(search) ||
                 distributor.id_number.toLowerCase().includes(search)
        })
      }
      
      // Apply status filter
      if (this.operationalFilterStatus) {
        filtered = filtered.filter(distributor => distributor.status === this.operationalFilterStatus)
      }
      
      return filtered
    },
    filteredHRManagers() {
      let filtered = this.hrManagers
      
      // Apply search filter
      if (this.hrSearch) {
        const search = this.hrSearch.toLowerCase()
        filtered = filtered.filter(manager => {
          return manager.full_name.toLowerCase().includes(search) ||
                 manager.email?.toLowerCase().includes(search) ||
                 manager.phone?.toLowerCase().includes(search) ||
                 manager.position?.toLowerCase().includes(search) ||
                 manager.employment_type_name?.toLowerCase().includes(search)
        })
      }
      
      // Apply status filter
      if (this.hrFilterStatus) {
        filtered = filtered.filter(manager => manager.status === this.hrFilterStatus)
      }
      
      // Apply employment type filter
      if (this.hrFilterEmploymentType) {
        filtered = filtered.filter(manager => manager.employment_type === this.hrFilterEmploymentType)
      }
      
      return filtered
    },
    filteredFinanceManagers() {
      let filtered = this.financeManagers
      
      // Apply search filter
      if (this.financeSearch) {
        const search = this.financeSearch.toLowerCase()
        filtered = filtered.filter(manager => {
          return manager.full_name.toLowerCase().includes(search) ||
                 manager.email?.toLowerCase().includes(search) ||
                 manager.phone?.toLowerCase().includes(search) ||
                 manager.position?.toLowerCase().includes(search) ||
                 manager.employment_type_name?.toLowerCase().includes(search)
        })
      }
      
      // Apply status filter
      if (this.financeFilterStatus) {
        filtered = filtered.filter(manager => manager.status === this.financeFilterStatus)
      }
      
      // Apply employment type filter
      if (this.financeFilterEmploymentType) {
        filtered = filtered.filter(manager => manager.employment_type === this.financeFilterEmploymentType)
      }
      
      return filtered
    }
  },
  async created() {
    await this.fetchInitialData()
  },
  methods: {
    // Toast Methods
    showSuccessToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #10b981, #059669)",
          color: "#ffffff",
          borderRadius: "8px",
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
          zIndex: 9999
        },
        stopOnFocus: true
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
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
          zIndex: 9999
        },
        stopOnFocus: true
      }).showToast();
    },

    showInfoToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #3b82f6, #2563eb)",
          color: "#ffffff",
          borderRadius: "8px",
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
          zIndex: 9999
        },
        stopOnFocus: true
      }).showToast();
    },

    showWarningToast(message) {
      Toastify({
        text: message,
        duration: 5000,
        gravity: "top",
        position: "right",
        style: {
          background: "linear-gradient(to right, #f59e0b, #d97706)",
          color: "#ffffff",
          borderRadius: "8px",
          padding: "16px",
          fontSize: "14px",
          fontWeight: "500",
          boxShadow: "0 4px 12px rgba(0, 0, 0, 0.15)",
          zIndex: 9999
        },
        stopOnFocus: true
      }).showToast();
    },

    // Data Fetching
    async fetchInitialData() {
      this.loading = true
      try {
        await this.fetchVerificationData()
        await this.fetchOperationalData()
        await this.fetchHRData()
        await this.fetchFinanceData()
      } catch (error) {
        console.error('Error fetching initial data:', error)
        this.showErrorToast('Failed to load data. Please try again.')
      } finally {
        this.loading = false
      }
    },

    async fetchVerificationData() {
      try {
        const response = await axios.get('/distributor/requirements')
        if (response.data.status === 'success') {
          this.verificationData = response.data.data
        }
      } catch (error) {
        console.error('Error fetching verification data:', error)
        this.verificationData = null
      }
    },

    async fetchOperationalData() {
      if (!this.isVerifiedDistributor) return
      
      try {
        await Promise.all([
          this.fetchOperationalDistributors(),
          this.fetchOperationalStats()
        ])
      } catch (error) {
        console.error('Error fetching operational data:', error)
      }
    },

    async fetchOperationalDistributors(page = 1) {
      try {
        const response = await axios.get(`/distributor/operational-distributors?page=${page}&per_page=10`)
        if (response.data.status === 'success') {
          this.operationalDistributors = response.data.data.operational_distributors
          this.operationalPagination = response.data.data.pagination
        }
      } catch (error) {
        console.error('Error fetching operational distributors:', error)
        this.showErrorToast('Failed to load operational distributors.')
      }
    },
    
    async fetchOperationalStats() {
      try {
        const response = await axios.get('/distributor/operational-distributors/statistics')
        if (response.data.status === 'success') {
          this.operationalStats = response.data.data
        }
      } catch (error) {
        console.error('Error fetching operational stats:', error)
      }
    },

    async fetchHRData() {
      if (!this.isVerifiedDistributor) return
      
      try {
        await Promise.all([
          this.fetchHRManagers(),
          this.fetchHRStats()
        ])
      } catch (error) {
        console.error('Error fetching HR data:', error)
      }
    },

    async fetchHRManagers(page = 1) {
      try {
        const response = await axios.get(`/distributor/hr-managers?page=${page}&per_page=10`)
        if (response.data.status === 'success') {
          this.hrManagers = response.data.data.hr_managers
          this.hrPagination = response.data.data.pagination
        }
      } catch (error) {
        console.error('Error fetching HR managers:', error)
        this.showErrorToast('Failed to load HR managers.')
      }
    },
    
    async fetchHRStats() {
      try {
        const response = await axios.get('/distributor/hr-managers/statistics')
        if (response.data.status === 'success') {
          this.hrStats = response.data.data
        }
      } catch (error) {
        console.error('Error fetching HR stats:', error)
      }
    },

    async fetchFinanceData() {
      if (!this.isVerifiedDistributor) return
      
      try {
        await Promise.all([
          this.fetchFinanceManagers(),
          this.fetchFinanceStats()
        ])
      } catch (error) {
        console.error('Error fetching finance data:', error)
      }
    },

    async fetchFinanceManagers(page = 1) {
      try {
        const response = await axios.get(`/distributor/finance-managers?page=${page}&per_page=10`)
        if (response.data.status === 'success') {
          this.financeManagers = response.data.data.finance_managers
          this.financePagination = response.data.data.pagination
        }
      } catch (error) {
        console.error('Error fetching finance managers:', error)
        this.showErrorToast('Failed to load finance managers.')
      }
    },
    
    async fetchFinanceStats() {
      try {
        const response = await axios.get('/distributor/finance-managers/statistics')
        if (response.data.status === 'success') {
          this.financeStats = response.data.data
        }
      } catch (error) {
        console.error('Error fetching finance stats:', error)
      }
    },

    // Form Validation
    validatePhoneNumber() {
      this.validatePhoneNumberHelper(this.operationalForm, 'operationalPhoneError')
    },

    validateHRPhoneNumber() {
      this.validatePhoneNumberHelper(this.hrForm, 'hrPhoneError')
    },

    validateFinancePhoneNumber() {
      this.validatePhoneNumberHelper(this.financeForm, 'financePhoneError')
    },

    validatePhoneNumberHelper(form, errorField) {
      const phone = form.phone
      
      if (phone.length > 0 && phone.length !== 11) {
        this[errorField] = 'Phone number must be exactly 11 digits'
      } else if (!/^\d+$/.test(phone)) {
        this[errorField] = 'Phone number must contain only digits'
      } else if (!/^0/.test(phone)) {
        this[errorField] = 'Phone number must start with 0'
      } else {
        this[errorField] = ''
      }
    },

    // Operational Distributor Methods
    async createOperationalDistributor() {
      if (!this.isOperationalFormValid || this.creatingOperational) return
      
      this.creatingOperational = true
      try {
        const formData = new FormData()
        
        // Append text fields
        formData.append('first_name', this.operationalForm.first_name)
        formData.append('last_name', this.operationalForm.last_name)
        formData.append('email', this.operationalForm.email)
        formData.append('phone', this.operationalForm.phone)
        formData.append('address', this.operationalForm.address)
        formData.append('valid_id_type', this.operationalForm.valid_id_type)
        formData.append('id_number', this.operationalForm.id_number)
        formData.append('password', this.operationalForm.password)
        formData.append('password_confirmation', this.operationalForm.password_confirmation)
        
        // Append file
        formData.append('valid_id_photo', this.operationalForm.valid_id_photo)
        
        const response = await axios.post('/distributor/operational-distributors', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor created successfully!')
          
          // Reset form
          this.cancelOperationalForm()
          
          // Refresh data
          await this.fetchOperationalData()
          
          // Add to recent activities
          this.addRecentActivity(`New operational distributor added: ${this.operationalForm.first_name} ${this.operationalForm.last_name}`)
        }
      } catch (error) {
        console.error('Error creating operational distributor:', error)
        let errorMessage = error.response?.data?.message || 'Failed to create operational distributor.'
        
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          errorMessage = errors.join(', ')
        }
        
        this.showErrorToast(errorMessage)
      } finally {
        this.creatingOperational = false
      }
    },
    
    async viewOperationalDistributor(id) {
      try {
        const response = await axios.get(`/distributor/operational-distributors/${id}`)
        if (response.data.status === 'success') {
          const distributor = response.data.data.operational_distributor
          this.showInfoToast(`Viewing ${distributor.full_name} - Status: ${distributor.status}`)
        }
      } catch (error) {
        console.error('Error viewing operational distributor:', error)
        this.showErrorToast('Failed to view operational distributor details.')
      }
    },
    
    async activateOperationalDistributor(id) {
      try {
        const response = await axios.post(`/distributor/operational-distributors/${id}/activate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor activated successfully.')
          await this.fetchOperationalData()
          this.addRecentActivity('Operational distributor activated')
        }
      } catch (error) {
        console.error('Error activating operational distributor:', error)
        this.showErrorToast('Failed to activate operational distributor.')
      }
    },
    
    async deactivateOperationalDistributor(id) {
      try {
        const response = await axios.post(`/distributor/operational-distributors/${id}/deactivate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor deactivated successfully.')
          await this.fetchOperationalData()
          this.addRecentActivity('Operational distributor deactivated')
        }
      } catch (error) {
        console.error('Error deactivating operational distributor:', error)
        this.showErrorToast('Failed to deactivate operational distributor.')
      }
    },
    
    async deleteOperationalDistributor(id) {
      if (!confirm('Are you sure you want to delete this operational distributor? This action cannot be undone.')) {
        return
      }
      
      try {
        const response = await axios.delete(`/distributor/operational-distributors/${id}`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Operational distributor deleted successfully.')
          await this.fetchOperationalData()
          this.addRecentActivity('Operational distributor deleted')
        }
      } catch (error) {
        console.error('Error deleting operational distributor:', error)
        this.showErrorToast('Failed to delete operational distributor.')
      }
    },

    // HR Manager Methods
    async createHRManager() {
      if (!this.isHRFormValid || this.creatingHR) return
      
      this.creatingHR = true
      try {
        const formData = new FormData()
        
        // Append text fields
        formData.append('first_name', this.hrForm.first_name)
        formData.append('last_name', this.hrForm.last_name)
        formData.append('email', this.hrForm.email)
        formData.append('phone', this.hrForm.phone)
        formData.append('address', this.hrForm.address)
        formData.append('employment_type', this.hrForm.employment_type)
        formData.append('hire_date', this.hrForm.hire_date)
        formData.append('salary', this.hrForm.salary)
        formData.append('position', this.hrForm.position)
        formData.append('valid_id_type', this.hrForm.valid_id_type)
        formData.append('id_number', this.hrForm.id_number)
        formData.append('password', this.hrForm.password)
        formData.append('password_confirmation', this.hrForm.password_confirmation)
        
        // Append files
        formData.append('valid_id_photo', this.hrForm.valid_id_photo)
        if (this.hrForm.resume) {
          formData.append('resume', this.hrForm.resume)
        }
        if (this.hrForm.employment_contract) {
          formData.append('employment_contract', this.hrForm.employment_contract)
        }
        
        const response = await axios.post('/distributor/hr-managers', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.status === 'success') {
          this.showSuccessToast('HR Manager created successfully! User account has been created.')
          
          // Reset form
          this.cancelHRForm()
          
          // Refresh data
          await this.fetchHRData()
          
          // Add to recent activities
          this.addRecentActivity(`New HR manager added: ${this.hrForm.first_name} ${this.hrForm.last_name}`)
        }
      } catch (error) {
        console.error('Error creating HR manager:', error)
        let errorMessage = error.response?.data?.message || 'Failed to create HR manager.'
        
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          errorMessage = errors.join(', ')
        }
        
        this.showErrorToast(errorMessage)
      } finally {
        this.creatingHR = false
      }
    },
    
    async viewHRManager(id) {
      try {
        const response = await axios.get(`/distributor/hr-managers/${id}`)
        if (response.data.status === 'success') {
          const manager = response.data.data.hr_manager
          this.showInfoToast(`Viewing ${manager.full_name} - Position: ${manager.position}`)
        }
      } catch (error) {
        console.error('Error viewing HR manager:', error)
        this.showErrorToast('Failed to view HR manager details.')
      }
    },
    
    async activateHRManager(id) {
      try {
        const response = await axios.post(`/distributor/hr-managers/${id}/activate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('HR Manager activated successfully.')
          await this.fetchHRData()
          this.addRecentActivity('HR Manager activated')
        }
      } catch (error) {
        console.error('Error activating HR manager:', error)
        this.showErrorToast('Failed to activate HR manager.')
      }
    },
    
    async deactivateHRManager(id) {
      try {
        const response = await axios.post(`/distributor/hr-managers/${id}/deactivate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('HR Manager deactivated successfully.')
          await this.fetchHRData()
          this.addRecentActivity('HR Manager deactivated')
        }
      } catch (error) {
        console.error('Error deactivating HR manager:', error)
        this.showErrorToast('Failed to deactivate HR manager.')
      }
    },

    async putHRManagerOnLeave(id) {
      try {
        const response = await axios.post(`/distributor/hr-managers/${id}/put-on-leave`)
        if (response.data.status === 'success') {
          this.showSuccessToast('HR Manager put on leave successfully.')
          await this.fetchHRData()
          this.addRecentActivity('HR Manager put on leave')
        }
      } catch (error) {
        console.error('Error putting HR manager on leave:', error)
        this.showErrorToast('Failed to put HR manager on leave.')
      }
    },
    
    async deleteHRManager(id) {
      if (!confirm('Are you sure you want to delete this HR manager? This action cannot be undone.')) {
        return
      }
      
      try {
        const response = await axios.delete(`/distributor/hr-managers/${id}`)
        if (response.data.status === 'success') {
          this.showSuccessToast('HR Manager deleted successfully.')
          await this.fetchHRData()
          this.addRecentActivity('HR Manager deleted')
        }
      } catch (error) {
        console.error('Error deleting HR manager:', error)
        this.showErrorToast('Failed to delete HR manager.')
      }
    },

    // Finance Manager Methods
    async createFinanceManager() {
      if (!this.isFinanceFormValid || this.creatingFinance) return
      
      this.creatingFinance = true
      try {
        const formData = new FormData()
        
        // Append text fields
        formData.append('first_name', this.financeForm.first_name)
        formData.append('last_name', this.financeForm.last_name)
        formData.append('email', this.financeForm.email)
        formData.append('phone', this.financeForm.phone)
        formData.append('address', this.financeForm.address)
        formData.append('employment_type', this.financeForm.employment_type)
        formData.append('hire_date', this.financeForm.hire_date)
        formData.append('salary', this.financeForm.salary)
        formData.append('position', this.financeForm.position)
        formData.append('valid_id_type', this.financeForm.valid_id_type)
        formData.append('id_number', this.financeForm.id_number)
        formData.append('password', this.financeForm.password)
        formData.append('password_confirmation', this.financeForm.password_confirmation)
        
        // Append files
        formData.append('valid_id_photo', this.financeForm.valid_id_photo)
        if (this.financeForm.resume) {
          formData.append('resume', this.financeForm.resume)
        }
        if (this.financeForm.employment_contract) {
          formData.append('employment_contract', this.financeForm.employment_contract)
        }
        
        const response = await axios.post('/distributor/finance-managers', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.status === 'success') {
          this.showSuccessToast('Finance Manager created successfully! User account has been created.')
          
          // Reset form
          this.cancelFinanceForm()
          
          // Refresh data
          await this.fetchFinanceData()
          
          // Add to recent activities
          this.addRecentActivity(`New finance manager added: ${this.financeForm.first_name} ${this.financeForm.last_name}`)
        }
      } catch (error) {
        console.error('Error creating finance manager:', error)
        let errorMessage = error.response?.data?.message || 'Failed to create finance manager.'
        
        if (error.response?.data?.errors) {
          const errors = Object.values(error.response.data.errors).flat()
          errorMessage = errors.join(', ')
        }
        
        this.showErrorToast(errorMessage)
      } finally {
        this.creatingFinance = false
      }
    },
    
    async viewFinanceManager(id) {
      try {
        const response = await axios.get(`/distributor/finance-managers/${id}`)
        if (response.data.status === 'success') {
          const manager = response.data.data.finance_manager
          this.showInfoToast(`Viewing ${manager.full_name} - Position: ${manager.position}`)
        }
      } catch (error) {
        console.error('Error viewing finance manager:', error)
        this.showErrorToast('Failed to view finance manager details.')
      }
    },
    
    async activateFinanceManager(id) {
      try {
        const response = await axios.post(`/distributor/finance-managers/${id}/activate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Finance Manager activated successfully.')
          await this.fetchFinanceData()
          this.addRecentActivity('Finance Manager activated')
        }
      } catch (error) {
        console.error('Error activating finance manager:', error)
        this.showErrorToast('Failed to activate finance manager.')
      }
    },
    
    async deactivateFinanceManager(id) {
      try {
        const response = await axios.post(`/distributor/finance-managers/${id}/deactivate`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Finance Manager deactivated successfully.')
          await this.fetchFinanceData()
          this.addRecentActivity('Finance Manager deactivated')
        }
      } catch (error) {
        console.error('Error deactivating finance manager:', error)
        this.showErrorToast('Failed to deactivate finance manager.')
      }
    },

    async putFinanceManagerOnLeave(id) {
      try {
        const response = await axios.post(`/distributor/finance-managers/${id}/put-on-leave`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Finance Manager put on leave successfully.')
          await this.fetchFinanceData()
          this.addRecentActivity('Finance Manager put on leave')
        }
      } catch (error) {
        console.error('Error putting finance manager on leave:', error)
        this.showErrorToast('Failed to put finance manager on leave.')
      }
    },
    
    async deleteFinanceManager(id) {
      if (!confirm('Are you sure you want to delete this finance manager? This action cannot be undone.')) {
        return
      }
      
      try {
        const response = await axios.delete(`/distributor/finance-managers/${id}`)
        if (response.data.status === 'success') {
          this.showSuccessToast('Finance Manager deleted successfully.')
          await this.fetchFinanceData()
          this.addRecentActivity('Finance Manager deleted')
        }
      } catch (error) {
        console.error('Error deleting finance manager:', error)
        this.showErrorToast('Failed to delete finance manager.')
      }
    },

    // File Methods
    triggerFileInput(type, field) {
      this.$refs[`${type}_${field}`].click()
    },
    
    handleFileChange(event, type, field) {
      const file = event.target.files[0]
      if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
          this.showErrorToast(`File "${file.name}" is too large. Maximum size is 5MB.`)
          return
        }
        
        // Check file type based on field
        let validTypes = []
        if (field === 'valid_id_photo') {
          validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
        } else if (field === 'resume') {
          validTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        } else if (field === 'employment_contract') {
          validTypes = ['application/pdf']
        }
        
        if (!validTypes.includes(file.type)) {
          let allowedTypes = ''
          if (field === 'valid_id_photo') {
            allowedTypes = 'JPG, PNG, or PDF'
          } else if (field === 'resume') {
            allowedTypes = 'PDF, DOC, or DOCX'
          } else if (field === 'employment_contract') {
            allowedTypes = 'PDF'
          }
          this.showErrorToast(`File "${file.name}" must be ${allowedTypes}.`)
          return
        }
        
        if (type === 'operational') {
          this.operationalForm[field] = file
        } else if (type === 'hr') {
          this.hrForm[field] = file
        } else if (type === 'finance') {
          this.financeForm[field] = file
        }
      }
    },
    
    handleFileDrop(event, type, field) {
      event.preventDefault()
      const file = event.dataTransfer.files[0]
      if (file) {
        const input = {
          target: { files: [file] }
        }
        this.handleFileChange(input, type, field)
      }
    },

    // Form Methods
    cancelOperationalForm() {
      this.operationalForm = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        password: '',
        password_confirmation: ''
      }
      this.operationalPhoneError = ''
      this.showOperationalForm = false
    },
    
    cancelHRForm() {
      this.hrForm = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        employment_type: '',
        hire_date: '',
        salary: '',
        position: 'HR Manager',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        resume: null,
        employment_contract: null,
        password: '',
        password_confirmation: ''
      }
      this.hrPhoneError = ''
      this.showHRForm = false
    },
    
    cancelFinanceForm() {
      this.financeForm = {
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        address: '',
        employment_type: '',
        hire_date: '',
        salary: '',
        position: 'Finance Manager',
        valid_id_type: '',
        id_number: '',
        valid_id_photo: null,
        resume: null,
        employment_contract: null,
        password: '',
        password_confirmation: ''
      }
      this.financePhoneError = ''
      this.showFinanceForm = false
    },

    // Pagination Methods
    changeOperationalPage(page) {
      this.currentOperationalPage = page
      this.fetchOperationalDistributors(page)
    },

    changeHRPage(page) {
      this.currentHRPage = page
      this.fetchHRManagers(page)
    },

    changeFinancePage(page) {
      this.currentFinancePage = page
      this.fetchFinanceManagers(page)
    },

    getVisiblePages(type) {
      const pagination = type === 'operational' ? this.operationalPagination : 
                        type === 'hr' ? this.hrPagination : 
                        this.financePagination
      if (!pagination) return []
      
      const current = pagination.current_page
      const last = pagination.last_page
      const pages = []
      
      // Always show first page
      pages.push(1)
      
      // Show pages around current page
      for (let i = Math.max(2, current - 1); i <= Math.min(last - 1, current + 1); i++) {
        pages.push(i)
      }
      
      // Always show last page if different from first
      if (last > 1) {
        pages.push(last)
      }
      
      // Remove duplicates and sort
      return [...new Set(pages)].sort((a, b) => a - b)
    },

    // Utility Methods
    getInitials(name) {
      if (!name) return '??'
      return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },

    getStatusClasses(status) {
      switch (status) {
        case 'active': return 'bg-green-100 text-green-800'
        case 'pending': return 'bg-yellow-100 text-yellow-800'
        case 'inactive': return 'bg-red-100 text-red-800'
        case 'on_leave': return 'bg-blue-100 text-blue-800'
        default: return 'bg-gray-100 text-gray-800'
      }
    },

    getStatusDotClass(status) {
      switch (status) {
        case 'active': return 'bg-green-500'
        case 'pending': return 'bg-yellow-500'
        case 'inactive': return 'bg-red-500'
        case 'on_leave': return 'bg-blue-500'
        default: return 'bg-gray-500'
      }
    },

    getActiveRate() {
      let stats = null
      switch (this.activeTab) {
        case 'operational': stats = this.operationalStats; break
        case 'hr': stats = this.hrStats; break
        case 'finance': stats = this.financeStats; break
      }
      
      if (!stats || stats.total === 0) return 0
      return Math.round((stats.active / stats.total) * 100)
    },

    getAccountRate() {
      let stats = null
      switch (this.activeTab) {
        case 'operational': stats = this.operationalStats; break
        case 'hr': stats = this.hrStats; break
        case 'finance': stats = this.financeStats; break
      }
      
      if (!stats || stats.total === 0) return 0
      return Math.round((stats.with_accounts / stats.total) * 100)
    },

    getTotalStats() {
      const operationalTotal = this.operationalStats?.total || 0
      const hrTotal = this.hrStats?.total || 0
      const financeTotal = this.financeStats?.total || 0
      const operationalActive = this.operationalStats?.active || 0
      const hrActive = this.hrStats?.active || 0
      const financeActive = this.financeStats?.active || 0
      const operationalAccounts = this.operationalStats?.with_accounts || 0
      const hrAccounts = this.hrStats?.with_accounts || 0
      const financeAccounts = this.financeStats?.with_accounts || 0
      const operationalPending = this.operationalStats?.pending || 0
      const hrPending = this.hrStats?.pending || 0
      const financePending = this.financeStats?.pending || 0
      const operationalInactive = this.operationalStats?.inactive || 0
      const hrInactive = this.hrStats?.inactive || 0
      const financeInactive = this.financeStats?.inactive || 0
      
      return {
        total: operationalTotal + hrTotal + financeTotal,
        active: operationalActive + hrActive + financeActive,
        withAccounts: operationalAccounts + hrAccounts + financeAccounts,
        pending: operationalPending + hrPending + financePending,
        inactive: operationalInactive + hrInactive + financeInactive
      }
    },

    getStatusDistribution() {
      let items = []
      switch (this.activeTab) {
        case 'operational': items = this.operationalDistributors; break
        case 'hr': items = this.hrManagers; break
        case 'finance': items = this.financeManagers; break
      }
      
      const total = items.length
      if (total === 0) return []
      
      let statuses = ['active', 'pending', 'inactive']
      if (this.activeTab === 'hr' || this.activeTab === 'finance') {
        statuses.push('on_leave')
      }
      
      const distribution = statuses.map(status => {
        const count = items.filter(item => item.status === status).length
        return {
          name: status === 'on_leave' ? 'On Leave' : status.charAt(0).toUpperCase() + status.slice(1),
          count: count,
          percentage: total > 0 ? Math.round((count / total) * 100) : 0,
          color: status === 'active' ? 'bg-green-500' : 
                 status === 'pending' ? 'bg-yellow-500' : 
                 status === 'inactive' ? 'bg-red-500' : 
                 'bg-blue-500'
        }
      })
      
      return distribution.filter(item => item.count > 0)
    },

    addRecentActivity(message) {
      const activity = {
        id: this.recentActivities.length + 1,
        message: message,
        time: 'Just now',
        icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        iconBg: this.activeTab === 'operational' ? 'bg-blue-100' : 
                this.activeTab === 'hr' ? 'bg-green-100' : 
                'bg-purple-100',
        iconColor: this.activeTab === 'operational' ? 'text-blue-600' : 
                   this.activeTab === 'hr' ? 'text-green-600' : 
                   'text-purple-600'
      }
      this.recentActivities.unshift(activity)
      
      // Keep only last 3 activities
      if (this.recentActivities.length > 3) {
        this.recentActivities.pop()
      }
    },

    // Helper Methods for UI
    handleAddNewStaff() {
      switch (this.activeTab) {
        case 'operational': 
          this.showOperationalForm = true
          break
        case 'hr': 
          this.showHRForm = true
          break
        case 'finance': 
          this.showFinanceForm = true
          break
      }
    },

    getButtonGradient() {
      switch (this.activeTab) {
        case 'operational': return 'bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600'
        case 'hr': return 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600'
        case 'finance': return 'bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700'
        default: return 'bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600'
      }
    },

    getAddButtonText() {
      switch (this.activeTab) {
        case 'operational': return 'Operational Distributor'
        case 'hr': return 'HR Manager'
        case 'finance': return 'Finance Manager'
        default: return 'Staff Member'
      }
    },

    getOverviewGradient() {
      switch (this.activeTab) {
        case 'operational': return 'bg-gradient-to-br from-blue-500 to-indigo-600'
        case 'hr': return 'bg-gradient-to-br from-green-500 to-emerald-600'
        case 'finance': return 'bg-gradient-to-br from-purple-500 to-purple-600'
        default: return 'bg-gradient-to-br from-blue-500 to-indigo-600'
      }
    },

    getOverviewTitle() {
      switch (this.activeTab) {
        case 'operational': return 'Operational'
        case 'hr': return 'HR'
        case 'finance': return 'Finance'
        default: return 'Operational'
      }
    },

    getOverviewTotal() {
      switch (this.activeTab) {
        case 'operational': return this.operationalStats?.total || 0
        case 'hr': return this.hrStats?.total || 0
        case 'finance': return this.financeStats?.total || 0
        default: return 0
      }
    },

    getOverviewActive() {
      switch (this.activeTab) {
        case 'operational': return this.operationalStats?.active || 0
        case 'hr': return this.hrStats?.active || 0
        case 'finance': return this.financeStats?.active || 0
        default: return 0
      }
    },

    getStatusIconBg() {
      switch (this.activeTab) {
        case 'operational': return 'bg-blue-100'
        case 'hr': return 'bg-green-100'
        case 'finance': return 'bg-purple-100'
        default: return 'bg-blue-100'
      }
    },

    getStatusIconColor() {
      switch (this.activeTab) {
        case 'operational': return 'text-blue-600'
        case 'hr': return 'text-green-600'
        case 'finance': return 'text-purple-600'
        default: return 'text-blue-600'
      }
    },

    async refreshData() {
      await this.fetchInitialData()
      this.showSuccessToast('Data refreshed successfully!')
    },

    exportToExcel() {
      this.showInfoToast('Export to Excel feature would be implemented here')
    }
  }
}
