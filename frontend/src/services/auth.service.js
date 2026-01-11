import api from '@/utils/axios'

const AuthService = {
  // Register a new user
  async register(userData) {
    try {
      const response = await api.post('/auth/register', userData)
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  // Login user
  async login(credentials) {
    try {
      const response = await api.post('/auth/login', credentials)
      
      // Save token to localStorage if successful
      if (response.data.data && response.data.data.token) {
        localStorage.setItem('auth_token', response.data.data.token)
      }
      
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  // Logout user
  async logout() {
    try {
      const response = await api.post('/auth/logout')
      
      // Remove token from localStorage
      localStorage.removeItem('auth_token')
      
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  // Get current user
  async getCurrentUser() {
    try {
      const response = await api.get('/auth/me')
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  // Check if email exists
  async checkEmail(email) {
    try {
      const response = await api.post('/auth/check-email', { email })
      return response.data
    } catch (error) {
      throw this.handleError(error)
    }
  },

  // Handle API errors
  handleError(error) {
    if (error.response && error.response.data) {
      // Return server error message
      return new Error(error.response.data.message || 'An error occurred')
    } else if (error.request) {
      // Network error
      return new Error('Network error. Please check your connection.')
    } else {
      // Other errors
      return new Error(error.message || 'An error occurred')
    }
  }
}

export default AuthService