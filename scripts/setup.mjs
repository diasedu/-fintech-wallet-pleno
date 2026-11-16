// Prepara api/ e web/ para rodar localmente: dependências, .env e banco SQLite.
// Uso: npm run setup (uma vez), depois npm run dev.
import { execSync } from 'node:child_process'
import { copyFileSync, existsSync, closeSync, openSync } from 'node:fs'
import { fileURLToPath } from 'node:url'
import path from 'node:path'

const root = path.dirname(path.dirname(fileURLToPath(import.meta.url)))
const api = path.join(root, 'api')
const web = path.join(root, 'web')

function run(command, cwd) {
  console.log(`\n> ${command}  (em ${path.relative(root, cwd) || '.'})`)
  execSync(command, { cwd, stdio: 'inherit' })
}

function copyIfMissing(from, to) {
  if (existsSync(to)) return
  copyFileSync(from, to)
  console.log(`Criado ${path.relative(root, to)}`)
}

copyIfMissing(path.join(api, '.env.example'), path.join(api, '.env'))
copyIfMissing(path.join(web, '.env.example'), path.join(web, '.env'))

const sqlitePath = path.join(api, 'database', 'database.sqlite')
if (!existsSync(sqlitePath)) {
  closeSync(openSync(sqlitePath, 'w'))
  console.log('Criado api/database/database.sqlite')
}

run('composer install', api)
run('npm install', web)
run('php artisan key:generate --ansi', api)
run('php artisan migrate', api)

console.log('\nSetup concluído. Rode "npm run dev" para subir backend e frontend juntos.')
