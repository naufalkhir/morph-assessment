import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ListView from '../views/ListView.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/list', name: 'list', component: ListView },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})