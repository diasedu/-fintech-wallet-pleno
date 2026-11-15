import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import PrincipalView from '../views/PrincipalView.vue'

import { api, auth } from '../services/api'

const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/area-logada/principal', 'name': 'principal', component: PrincipalView, meta: { requiresAuth: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes : routes,
})

router.beforeEach( async(to) => {
  if (!to.meta.requiresAuth) {
    return
  }

  if (!auth.getToken()) {
    return '/login'                             // sem token: nem precisa perguntar à API
  }

  try {
    await api.get('/area-logada/principal', { silent: true }) // pergunta ao Laravel: "tem alguém logado?"
    return                                      // respondeu 200: logado, deixa passar
  } catch {
    return '/login'                             // deu erro (401): manda para o login
  }
})

export default router