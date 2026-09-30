import axios from 'axios'

const baseURL = 'http://localhost:8000/api';

const api = axios.create({
  baseURL, 
  timeout: 10000, 
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Storage base URL (for public files like verification photos)
export const storageBaseURL = baseURL.replace(/\/api$/, '');

// Request interceptor - add token to requests
api.interceptors.request.use(
  (config) => {
    // Get token from localStorage
    const token = localStorage.getItem('auth_token')
    
    // If token exists, add it to headers
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }

    // A FormData body must go out with NO Content-Type of its own, and this
    // deals with both ways that goes wrong. Both failures are silent: the
    // request completes, the server answers, and the only symptom is a
    // validation error claiming a file was never attached.
    //
    // 1. Left as the instance default of 'application/json', axios does not
    //    send the FormData at all. transformRequest sees a JSON content type
    //    plus a FormData body and returns JSON.stringify(formDataToJSON(data))
    //    -- text fields survive, the file is silently dropped.
    // 2. Pinned by hand to 'multipart/form-data', the header carries no
    //    boundary parameter, so PHP cannot tell where one part ends and the
    //    next begins. PHP answers "Missing boundary in multipart/form-data
    //    POST data" and populates neither $_POST nor $_FILES.
    //
    // Only the browser can generate a correct header, because only the browser
    // knows the boundary it chose for this particular body. So the header is
    // removed here and the user agent supplies it.
    //
    // null, not undefined: axios reads undefined as "unset" but then falls back
    // to 'application/x-www-form-urlencoded' (defaults/index.js), which is no
    // better. Only a null or false value actually deletes the header.
    //
    // This runs as a request interceptor, which is before transformRequest
    // (Axios.js dispatches the interceptor chain ahead of dispatchRequest), so
    // the deletion lands in time to stop the JSON serialisation.
    if (typeof FormData !== 'undefined' && config.data instanceof FormData) {
      config.headers['Content-Type'] = null
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