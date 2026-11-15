import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import PrincipalView from '../views/PrincipalView.vue'
import DepositoView from '../views/DepositoView.vue'
import SaqueView from '../views/SaqueView.vue'
import HistoricoView from '../views/HistoricoView.vue'
import AreaLogadaLayout from '../layouts/AreaLogadaLayout.vue'

import { api, auth } from '../services/api'

const routes = [
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'login', component: LoginView },
    {
      // menu + conteúdo; o "requiresAuth" vale para todas as rotas filhas
      path: '/area-logada',
      component: AreaLogadaLayout,
      redirect: '/area-logada/principal',
      meta: { requiresAuth: true },
      children: [
        { path: 'principal', name: 'principal', component: PrincipalView },
        { path: 'depositar', name: 'depositar', component: DepositoView },
        { path: 'sacar',     name: 'sacar',     component: SaqueView },
        { path: 'historico', name: 'historico', component: HistoricoView },
      ],
    },
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