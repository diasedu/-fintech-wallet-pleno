import { onMounted, ref } from 'vue'
import { api } from '../services/api'

interface PoliticaSenhaApi {
  min: number
  pattern: string
  flags: string
  mensagem: string
}

export interface ForcaSenha {
  percentual: number
  cor: 'bg-danger' | 'bg-warning' | 'bg-success'
  label: string
  valida: boolean
}

export interface CriterioSenha {
  chave: string
  label: string
  exemplo: string
  atendido: boolean
}

// Fallback usado só se a API não responder (ex.: backend fora do ar): mantém a
// mesma regra de App\Support\PoliticaSenha para não deixar o formulário sem validação.
const PADRAO_FALLBACK: PoliticaSenhaApi = {
  min: 12,
  pattern: '(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z\\d]).{12,}',
  flags: 'u',
  mensagem:
    'A senha deve ter no mínimo 12 caracteres e incluir letra maiúscula, letra minúscula, número e caractere especial.',
}

/**
 * Busca a regra de senha forte no backend (GET /senha/politica) e expõe o necessário
 * para o formulário de cadastro validar com o mesmo critério, sem duplicar a regra no JS.
 */
export function usePoliticaSenha() {
  const min = ref(PADRAO_FALLBACK.min)
  const mensagem = ref(PADRAO_FALLBACK.mensagem)
  const patternSource = ref(PADRAO_FALLBACK.pattern)
  const regex = ref(new RegExp(`^${PADRAO_FALLBACK.pattern}$`, PADRAO_FALLBACK.flags))

  onMounted(async () => {
    try {
      const { data } = await api.get<{ data: PoliticaSenhaApi }>('/senha/politica', { silent: true })
      min.value = data.min
      mensagem.value = data.mensagem
      patternSource.value = data.pattern
      regex.value = new RegExp(`^${data.pattern}$`, data.flags)
    } catch {
      // mantém o fallback já definido acima
    }
  })

  function avaliarCriterios(senha: string): CriterioSenha[] {
    return [
      {
        chave: 'tamanho',
        label: `Mínimo de ${min.value} caracteres`,
        exemplo: 'ex.: "MinhaSenha123!" tem 14',
        atendido: senha.length >= min.value,
      },
      {
        chave: 'minuscula',
        label: 'Uma letra minúscula',
        exemplo: 'ex.: a, b, c...',
        atendido: /[a-z]/.test(senha),
      },
      {
        chave: 'maiuscula',
        label: 'Uma letra MAIÚSCULA',
        exemplo: 'ex.: A, B, C...',
        atendido: /[A-Z]/.test(senha),
      },
      {
        chave: 'numero',
        label: 'Um número',
        exemplo: 'ex.: 0, 1, 2...',
        atendido: /\d/.test(senha),
      },
      {
        chave: 'especial',
        label: 'Um caractere especial',
        exemplo: 'ex.: !, @, #, $...',
        atendido: /[^A-Za-z\d]/.test(senha),
      },
    ]
  }

  function avaliarForca(senha: string): ForcaSenha {
    const criterios = avaliarCriterios(senha)
    const atendidos = criterios.filter((c) => c.atendido).length
    const percentual = senha.length === 0 ? 0 : Math.round((atendidos / criterios.length) * 100)
    const valida = regex.value.test(senha)

    let cor: ForcaSenha['cor'] = 'bg-danger'
    let label = 'Fraca'
    if (valida) {
      cor = 'bg-success'
      label = 'Forte'
    } else if (percentual >= 60) {
      cor = 'bg-warning'
      label = 'Média'
    }

    return { percentual, cor, label, valida }
  }

  return { min, mensagem, patternSource, regex, avaliarForca, avaliarCriterios }
}
