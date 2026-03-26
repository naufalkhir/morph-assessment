import axios from 'axios'

// Create a single Axios instance shared across the entire app
// baseURL comes from frontend/.env — VITE_API_BASE_URL=http://localhost:8000/api
// This means all API calls just use '/entries' instead of full URL
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json', // we send JSON
    'Accept': 'application/json',       // we expect JSON back
  },
})

// Interceptor — runs on every response BEFORE it reaches the component
api.interceptors.response.use(
  // Success (2xx) — pass through unchanged
  response => response,

  // Error — intercept and handle globally
  error => {
    // 422 = Laravel validation failed
    // Instead of returning the full Axios error object
    if (error.response?.status === 422) {
      return Promise.reject(error.response.data.errors)
    }
    // All other errors (500, 404, etc) — pass through normally
    return Promise.reject(error)
  }
)

export default api