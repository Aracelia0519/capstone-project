import axios from './axios'

let userPromise = null
let lastFetchTime = 0
const CACHE_DURATION = 5 * 60 * 1000 // 5 minutes

/**
 * Get current user with caching
 * @param {boolean} forceRefresh - Force refresh from API
 * @returns {Promise<Object|null>} User object or null
 */
export const getCurrentUser = async (forceRefresh = false) => {
  const token = localStorage.getItem('auth_token')
  const userData = localStorage.getItem('user_data')
  
  // If no token, return null
  if (!token) {
    return null
  }
  
  // Return cached user if exists and not forcing refresh
  if (userData && !forceRefresh) {
    const user = JSON.parse(userData)
    const now = Date.now()
    
    // If cached less than CACHE_DURATION ago, return it
    if (lastFetchTime && (now - lastFetchTime) < CACHE_DURATION) {
      return user
    }
  }
  
  // If there's already a pending request, return that promise
  if (userPromise && !forceRefresh) {
    return userPromise
  }
  
  // Make new request
  userPromise = axios.get('/auth/me')
    .then(response => {
      const user = response.data.user || response.data
      localStorage.setItem('user_data', JSON.stringify(user))
      lastFetchTime = Date.now()
      return user
    })
    .catch(error => {
      // Clear auth data on 401
      if (error.response?.status === 401) {
        clearAuthData()
      }
      throw error
    })
    .finally(() => {
      userPromise = null
    })
  
  return userPromise
}

/**
 * Clear authentication data and cache
 */
export const clearAuthData = () => {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_data')
  lastFetchTime = 0
  userPromise = null
}

/**
 * Refresh user data from API
 * @returns {Promise<Object>} User object
 */
export const refreshUserData = async () => {
  return getCurrentUser(true)
}

/**
 * Check if user has specific role
 * @param {string} role - Role to check
 * @returns {Promise<boolean>} True if user has role
 */
export const hasRole = async (role) => {
  try {
    const user = await getCurrentUser()
    return user?.role === role
  } catch (error) {
    return false
  }
}

/**
 * Get user role
 * @returns {Promise<string|null>} User role or null
 */
export const getUserRole = async () => {
  try {
    const user = await getCurrentUser()
    return user?.role || null
  } catch (error) {
    return null
  }
}