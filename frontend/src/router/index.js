import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ListView from '../views/ListView.vue'

// Define two routes for the application
const routes = [
  // / — Home page, contains the entry form
  { path: '/', name: 'home', component: HomeView },

  // /list — List page, contains the entries table
  { path: '/list', name: 'list', component: ListView },
]

export default createRouter({
  // createWebHistory — uses real URLs (/) instead of hash (#/)
  // requires server to serve index.html for all routes
  history: createWebHistory(),
  routes,
})