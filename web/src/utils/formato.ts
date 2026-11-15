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
