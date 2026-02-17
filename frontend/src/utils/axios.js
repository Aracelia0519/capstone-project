import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api', 
  timeout: 10000, 
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Request interceptor - add token to requests
api.interceptors.request.use(
  (config) => {
    // Get token from localStorage
    const token = localStorage.getItem('auth_token')
    
    // If token exists, add it to headers
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor - handle errors globally
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    // Handle specific error statuses
    if (error.response) {
      const { status, data } = error.response
      
      switch (status) {
        case 401: // Unauthorized
          console.error('Unauthorized - Please login again')
          // You can redirect to login page here
          // router.push('/login')
          break
          
        case 403: // Forbidden
          console.error('Forbidden - You do not have permission')
          break
          
        case 404: // Not Found
          console.error('API endpoint not found')
          break
          
        case 422: // Validation Error
          console.error('Validation error:', data.errors)
          break
          
        case 500: // Server Error
          console.error('Server error - Please try again later')
          break
          
        default:
          console.error('API error:', error.message)
      }
    } else if (error.request) {
      // The request was made but no response was received
      console.error('No response received from server')
    } else {
      // Something happened in setting up the request
      console.error('Request setup error:', error.message)
    }
    
    return Promise.reject(error)
  }
)

export default api