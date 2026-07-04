# Guia de Deploy no InfinityFree

## O que é InfinityFree?

InfinityFree é um serviço de hospedagem **100% gratuito** que suporta PHP e MySQL. Ele exibe anúncios **apenas na página de erro 404**, não no seu site. Não precisa de cartão de crédito.

---

## Passo 1 — Criar conta e site

1. Acesse https://www.infinityfree.com
2. Clique em **"Get Free Hosting"**
3. Preencha:
   - **Email** (seu email real)
   - **Password** (senha da sua conta)
4. Confirme o email recebido
5. Faça login no painel: https://cp.infinityfree.com
6. Clique em **"Create Account"** (seu primeiro site)
7. Em **"Domain"**, escolha um subdomínio grátis:
   - Ex: `portalsaberes.infinityfreeapp.com`
   - Ou use um domínio próprio se tiver
8. Anote o **"Username"** e **"Server"** gerados

---

## Passo 2 — Criar o banco de dados MySQL

1. No painel InfinityFree, vá em **"MySQL Databases"**
2. Clique em **"Create Database"**
3. Preencha:
   - **Database Name:** `portal_saberes` (ou outro nome)
   - **Username:** vai ser gerado automaticamente (ex: `if0_12345678_user`)
   - **Password:** crie uma senha forte
4. Anote **TUDO** que for gerado:
   - Host do banco (ex: `sql123.infinityfree.com`)
   - Nome do banco (ex: `if0_12345678_portal_saberes`)
   - Usuário (ex: `if0_12345678_user`)
   - Senha (a que você criou)

---

## Passo 3 — Importar as tabelas via phpMyAdmin

1. No painel InfinityFree, clique em **"phpMyAdmin"** (ao lado do banco criado)
2. Selecione o banco de dados no lado esquerdo
3. Clique na aba **"SQL"**
4. Agora importe os arquivos na **ordem correta**:

### Antes de começar: selecione o banco

No phpMyAdmin, clique no nome do banco (ex: `if0_42337914_portal_saberes`) no painel esquerdo antes de colar os SQLs. Assim os comandos rodam **dentro** do banco correto.

### Ordem de importação

Abra cada arquivo `.sql` da pasta `database/` do seu projeto e cole no phpMyAdmin, um por um:

| Ordem | Arquivo | O que faz |
|-------|---------|-----------|
| 1º | `database/schema.sql` | Cria as 8 tabelas principais + admin + config iniciais |
| 2º | `database/gnostic_categories.sql` | Insere as categorias gnósticas |
| 3º | `database/quotes_schema.sql` | Cria tabela de citações |
| 4º | `database/references_schema.sql` | Cria tabela de referências |
| 5º | `database/gamification_schema.sql` | Cria sistema de badges e reputação |
| 6º | `database/reviews_schema.sql` | Cria sistema de revisão |
| 7º | `database/versions_schema.sql` | Cria controle de versão |

**Como fazer:** Para cada arquivo:
1. Abra o arquivo `.sql` no bloco de notas
2. Copie TODO o conteúdo (`Ctrl+A` → `Ctrl+C`)
3. No phpMyAdmin, **certifique-se de que o banco está selecionado** no painel esquerdo
4. Cole na caixa de texto da aba SQL (`Ctrl+V`)
5. Clique em **"Go"** (Executar)
6. Repita para o próximo arquivo

---

## Passo 4 — Configurar arquivos do projeto

Antes de enviar os arquivos, você precisa editar dois arquivos de configuração.

### 4.1 — Editar `config/app.php`

No computador, abra o arquivo `config/app.php` e altere:

```php
define('APP_URL', 'http://localhost/wikiPortalSabedoria');
// ↓ substitua por
define('APP_URL', 'https://portalsaberes.infinityfreeapp.com');
// (use a URL do seu site)

define('APP_ENV', 'development');
// ↓ substitua por
define('APP_ENV', 'production');
```

### 4.2 — Editar `includes/Database.php`

Abra `includes/Database.php` e altere as credenciais do banco:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'portal_saberes');
define('DB_USER', 'root');
define('DB_PASS', '');
// ↓ substitua pelos dados do InfinityFree
define('DB_HOST', 'sql123.infinityfree.com');   // Host que o InfinityFree gerou
define('DB_PORT', '3306');
define('DB_NAME', 'if0_12345678_portal_saberes'); // Nome completo gerado
define('DB_USER', 'if0_12345678_user');            // Usuário gerado
define('DB_PASS', 'sua_senha_aqui');               // Senha que você criou
```

---

## Passo 5 — Enviar arquivos via FTP

### 5.1 — Pegar dados FTP no InfinityFree

1. No painel InfinityFree, vá em **"FTP Accounts"**
2. Anote:
   - **FTP Server:** (ex: `ftpupload.net`)
   - **FTP Username:** (o mesmo username do site)
   - **FTP Password:** (a senha da sua conta InfinityFree)

### 5.2 — Conectar e enviar

Você pode usar **FileZilla** (recomendado) ou qualquer cliente FTP.

```
Servidor: ftpupload.net
Usuário: if0_12345678
Senha: (sua senha)
Porta: 21
```

**O que enviar:**

Envie **TODA a pasta do projeto** para o diretório `htdocs/` no servidor.

**Importante:** NÃO precisa enviar:
- `multimidia/` — as mídias estão no Cloudinary, não no servidor
- `.git/` — pasta oculta do git (não faz diferença no servidor)

Após o upload, a estrutura no servidor deve ficar assim:

```
htdocs/
├── admin/
├── api/
├── assets/
├── config/
├── database/
├── docs/
├── includes/
├── uploads/
├── .htaccess
├── index.php
├── ... (demais arquivos)
```

---

## Passo 6 — Acessar o site

1. No navegador, acesse: `https://portalsaberes.infinityfreeapp.com`
2. Se tudo estiver certo, o portal já vai aparecer
3. Faça login com:
   - **Email:** admin@saberes.com
   - **Senha:** admin123

---

## Passo 7 — Importar conteúdo (opcional)

Depois do site no ar, você pode importar conteúdo:

### Citações Gnósticas

Rode no terminal do seu computador local:

```bash
php importar_quotes.php
```

### Saberes (conteúdo principal)

```bash
php api/importar_saberes.php
```

### Multimídia do Cloudinary

```bash
php importar_multimedia.php
```

---

## Manutenção

### Alterar algo no site

1. Edite os arquivos no seu computador
2. Envie apenas os arquivos alterados via FTP
3. Pronto — as mudanças já aparecem no site

### Backup do banco

No phpMyAdmin:
1. Selecione o banco
2. Aba **"Export"**
3. Marque **"SQL"**
4. Clique **"Go"** — baixa um arquivo `.sql` com todo o banco

---

## Resumo dos comandos

| Ação | Onde fazer |
|------|-----------|
| Criar conta | https://infinityfree.com |
| Criar banco | Painel InfinityFree → MySQL Databases |
| Importar SQL | phpMyAdmin (atalho no painel) |
| Editar config | No computador, antes do upload |
| Enviar arquivos | FileZilla (FTP) para `htdocs/` |
| Acessar site | `https://seudominio.infinityfreeapp.com` |
| Login admin | admin@saberes.com / admin123 |
| Backup | phpMyAdmin → Export |

---

**Dica:** Se algo der errado, o InfinityFree mostra os erros PHP no site. Verifique se:
- As credenciais do banco estão corretas
- O `APP_URL` está certo
- Os arquivos SQL foram importados na ordem correta
