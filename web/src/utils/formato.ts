const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' })

const dataHora = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' })

/** Formata a moeda no padrão brasileiro */
export function formatarMoeda(valor: string | number | null | undefined): string {
  return moeda.format(Number(valor ?? 0))
}

/** Data ISO vinda da API */
export function formatarDataHora(iso: string): string {
  return dataHora.format(new Date(iso))
}

/**
 * Máscara de moeda brasileira para inputs: a partir do texto digitado.
 */
export function mascararMoeda(valorDigitado: string): string {
  const digitos = valorDigitado.replace(/\D/g, '')
  if (!digitos) {
    return ''
  }

  const numero = Number(digitos) / 100
  return numero.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

/** Converte o texto já mascarado (ex.: "1.234,56") para number (1234.56). */
export function desmascararMoeda(valorMascarado: string): number {
  if (!valorMascarado) return 0

  const limpo = valorMascarado.replace(/\./g, '').replace(',', '.')
  return Number(limpo) || 0
}
