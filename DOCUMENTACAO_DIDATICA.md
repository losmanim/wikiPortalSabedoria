# Documentação Didática — Portal Saberes Ancestrais

## Sumário

1. [Visão Geral](#1-visão-geral)
2. [Arquitetura do Projeto](#2-arquitetura-do-projeto)
3. [Fluxo de Dados](#3-fluxo-de-dados)
4. [Sistema de Rotas](#4-sistema-de-rotas)
5. [Autenticação e Autorização](#5-autenticação-e-autorização)
6. [Banco de Dados](#6-banco-de-dados)
7. [Painel Administrativo](#7-painel-administrativo)
8. [Funcionalidades do Frontend](#8-funcionalidades-do-frontend)
9. [Testes Realizados](#9-testes-realizados)
10. [Bugs Encontrados e Corrigidos](#10-bugs-encontrados-e-corrigidos)
11. [Como Executar o Projeto](#11-como-executar-o-projeto)

---

## 1. Visão Geral

O **Portal Saberes Ancestrais** é uma aplicação web do tipo **Wiki / CMS** (Sistema de Gerenciamento de Conteúdo) que funciona como uma plataforma colaborativa sobre saberes ancestrais. O objetivo é unir ciência, espiritualidade e filosofia em um só lugar.

### Tecnologias Utilizadas

| Camada        | Tecnologia                          |
|---------------|-------------------------------------|
| **Backend**   | PHP 8.x (puro, sem framework)       |
| **Banco**     | MySQL / MariaDB                     |
| **Frontend**  | HTML5 + CSS3 + JavaScript puro      |
| **Editor**    | TinyMCE 7 (CDN)                     |
| **Ícones**    | Bootstrap Icons (CDN)               |
| **Servidor**  | Apache (com mod_rewrite)            |

---

## 2. Arquitetura do Projeto

### Estrutura de Diretórios

```
portal-saberes/
├── admin/               # Painel administrativo
│   ├── artigos/         # CRUD de artigos
│   ├── categorias/      # CRUD de categorias
│   ├── comentarios/     # Moderação de comentários
│   ├── midia/           # Galeria de mídia (uploads)
│   ├── paginas/         # CRUD de páginas estáticas
│   └── usuarios/        # Gerenciamento de usuários
├── assets/
│   └── css/             # Folhas de estilo (portal.css, admin.css)
├── auth/                # Autenticação
│   ├── login.php        # Login
│   ├── registro.php     # Cadastro
│   └── logout.php       # Sair
├── config/
│   └── app.php          # Configurações globais
├── database/
│   └── schema.sql       # Estrutura do banco + dados iniciais
├── includes/
│   ├── Database.php     # Classe de conexão PDO (Singleton)
│   ├── functions.php    # Funções utilitárias
│   ├── header.php       # Template do cabeçalho
│   └── footer.php       # Template do rodapé
├── api/                 # (reservado para futura API)
├── uploads/             # Imagens enviadas pelos usuários
├── .htaccess            # URLs amigáveis + segurança + cache
├── index.php            # Página inicial
├── artigo.php           # Página de artigo
├── categoria.php        # Página de categoria
├── pagina.php           # Página estática
├── busca.php            # Página de busca
├── sitemap.php          # XML Sitemap
├── install.php          # Instalador do banco
├── 404.php              # Página 404 personalizada
├── importar.php         # Script de importação em lote
└── README.md            # Documentação do projeto
```

### Padrão de Arquitetura

O projeto **não usa um framework PHP**. Cada arquivo atua como **controlador e visão ao mesmo tempo** (padrão procedural). A lógica de negócio, a consulta ao banco e a renderização HTML estão no mesmo arquivo.

**Componentes reutilizáveis:**

- `includes/Database.php` — Abstração de banco de dados (Singleton)
- `includes/functions.php` — Biblioteca de funções helpers
- `includes/header.php` / `includes/footer.php` — Templates de layout

---

## 3. Fluxo de Dados

### Ciclo de uma Requisição Típica

```
Usuário                        Servidor Apache                 PHP                    MySQL
   │                               │                          │                       │
   ├─ 1. GET /artigo/gnose ───────►│                          │                       │
   │                               │                          │                       │
   │                    2. .htaccess reescreve                │                       │
   │                       → artigo.php?slug=gnose           │                       │
   │                               │                          │                       │
   │                               ├─ 3. Inclui config/app ──►│                       │
   │                               │     (constantes, sessão) │                       │
   │                               │                          │                       │
   │                               ├─ 4. Inclui Database.php ─►│                       │
   │                               │     (Singleton PDO)      │                       │
   │                               │                          │                       │
   │                               ├─ 5. Inclui functions ───►│                       │
   │                               │     (helpers)            │                       │
   │                               │                          │                       │
   │                               ├─ 6. Query artigo ───────►│── SQL ──────────────►│
   │                               │     (slug = ?)           │◄──── resultados ─────┤
   │                               │                          │                       │
   │                               ├─ 7. Inclui header.php ──►│                       │
   │                               │     (renderiza nav)      │                       │
   │                               │                          │                       │
   │                               ├─ 8. Renderiza artigo ───►│                       │
   │                               │     (HTML + dados)       │                       │
   │                               │                          │                       │
   │                               ├─ 9. Inclui footer.php ──►│                       │
   │                               │     (fecha HTML)         │                       │
   │                               │                          │                       │
   │◄──── 10. Página HTML ─────────┤                          │                       │
```

### Fluxo do Painel Admin (Autenticado)

```
Usuário → Login (POST) → Valida bcrypt → Sessão criada → Redireciona /admin/
  ├─ Ações CRUD (criar/editar/excluir artigos, categorias, etc.)
  ├─ Upload de mídia (imagens)
  └─ Moderação de comentários
```

---

## 4. Sistema de Rotas

### 4.1 URLs Amigáveis (.htaccess)

O Apache via `mod_rewrite` transforma URLs amigáveis em parâmetros GET:

```
/artigo/{slug}     →  artigo.php?slug={slug}
/categoria/{slug}  →  categoria.php?slug={slug}
/pagina/{slug}     →  pagina.php?slug={slug}
/busca/{termo}     →  busca.php?q={termo}
```

### 4.2 Rotas de Frontend (Públicas)

| URL                          | Arquivo       | Descrição                    |
|------------------------------|---------------|------------------------------|
| `/`                          | `index.php`   | Homepage com destaques       |
| `/artigo/{slug}`             | GET           | Exibe artigo + comentários   |
| `/artigo/{slug}`             | POST          | Envia comentário             |
| `/categoria/{slug}`          | GET           | Lista artigos da categoria   |
| `/pagina/{slug}`             | GET           | Página estática              |
| `/busca`                     | GET           | Formulário de busca          |
| `/busca?q=` ou `/busca/{q}` | GET           | Resultados da busca          |
| `/sitemap.php`               | GET           | XML Sitemap                  |
| `*` (qualquer outra)        | `404.php`     | Página 404 personalizada     |

### 4.3 Rotas de Autenticação

| URL                        | Métodos | Descrição                |
|----------------------------|---------|--------------------------|
| `/auth/login.php`          | GET/POST| Login                    |
| `/auth/registro.php`       | GET/POST| Cadastro                 |
| `/auth/logout.php`         | GET     | Logout + destroy session |

### 4.4 Rotas do Admin

Todas exigem login. Algumas exigem nível `admin` ou `editor`.

| URL                              | Ação                    | Nível     |
|----------------------------------|-------------------------|-----------|
| `/admin/index.php`               | Dashboard               | usuário   |
| `/admin/artigos/index.php`       | CRUD artigos            | editor+   |
| `/admin/categorias/index.php`    | CRUD categorias         | admin     |
| `/admin/comentarios/index.php`   | Moderar comentários     | editor+   |
| `/admin/usuarios/index.php`      | Gerenciar usuários      | admin     |
| `/admin/paginas/index.php`       | CRUD páginas estáticas  | editor+   |
| `/admin/midia/index.php`         | Upload/galeria de mídia | editor+   |

### 4.5 Instalação

| URL                | Descrição                           |
|--------------------|-------------------------------------|
| `/install.php`     | Instalador do banco de dados        |

---

## 5. Autenticação e Autorização

### 5.1 Níveis de Acesso

| Nível       | Acesso                                                       |
|-------------|--------------------------------------------------------------|
| **admin**   | Acesso total (CRUD completo, gerenciar usuários, deletar)    |
| **editor**  | Criar/editar artigos e páginas, moderar comentários, upload  |
| **user**    | Navegar, comentar (quando logado)                            |
| **visitante** | Navegar apenas (não logado)                                |

### 5.2 Fluxo de Login

```
1. Usuário envia email + senha (POST)
2. PHP busca usuário por email no banco
3. Verifica senha com password_verify() (bcrypt)
4. Se OK: cria sessão (session_start()), redireciona para /admin/
5. Se falha: mostra erro "Credenciais inválidas"
```

### 5.3 Funções de Autorização

Definidas em `includes/functions.php`:

| Função             | Comportamento                                    |
|--------------------|--------------------------------------------------|
| `esta_logado()`    | Retorna true/false se há sessão ativa            |
| `required_login()` | Redireciona para /auth/login.php se não logado   |
| `is_admin()`       | Verifica se o usuário logado é admin             |
| `required_admin()` | Bloqueia com 403 se não for admin                |
| `required_editor()`| Bloqueia se não for admin nem editor             |

### 5.4 Segurança

- Senhas armazenadas com `password_hash(PASSWORD_BCRYPT)` — hash de mão única
- Sessão nomeada (`PORTAL_SABERES_SID`) para evitar conflitos
- SQL Injection prevenido com **prepared statements** (PDO)
- XSS prevenido com `htmlspecialchars()` em todas as saídas
- Upload validado: extensões permitidas (`jpg, jpeg, png, gif, webp, svg`)
- Tamanho máximo de upload: 5MB

---

## 6. Banco de Dados

### 6.1 Estrutura (8 Tabelas)

```sql
-- portal_saberes

usuarios        -- Usuários do sistema (admin, editor, user)
  ├── id (PK), nome, email (UNIQUE), senha (bcrypt)
  ├── avatar, bio
  ├── nivel (admin|editor|user), status (ativo|banido)
  └── criado_em, atualizado_em

categorias      -- Categorias hierárquicas de artigos
  ├── id (PK), nome, slug (UNIQUE), descricao
  ├── icone, cor, parent_id (auto-referência), ordem
  └── criado_em

artigos         -- Artigos/publicações
  ├── id (PK), categoria_id (FK), autor_id (FK)
  ├── titulo, slug (UNIQUE), resumo, conteudo (LONGTEXT)
  ├── tags, imagem, fonte, status (rascunho|publicado|arquivado)
  ├── views, criado_em, publicado_em, atualizado_em
  └── FULLTEXT INDEX (titulo, resumo, conteudo, tags) -- busca textual

comentarios     -- Comentários aninhados nos artigos
  ├── id (PK), artigo_id (FK CASCADE), usuario_id (FK)
  ├── autor_nome, autor_email (para visitantes)
  ├── conteudo, status (pendente|aprovado|rejeitado)
  └── parent_id (auto-referência para respostas)

paginas         -- Páginas estáticas (Sobre, Contato, etc.)
  ├── id (PK), titulo, slug (UNIQUE), conteudo
  ├── ordem, no_menu, status (rascunho|publicado)
  └── criado_em, atualizado_em

configuracoes   -- Configurações do site (chave-valor)
  ├── chave (PK), valor
  └── atualizado_em

logs            -- Registro de atividades
  ├── id (PK), usuario_id (FK), acao, descricao
  ├── ip, criado_em
  └── (usado para auditoria)

artigos_views   -- Registro de visualizações de artigos
  ├── id (PK), artigo_id (FK CASCADE)
  ├── ip, user_agent, criado_em
  └── (evita contagem múltipla por sessão)
```

### 6.2 Relacionamentos

```
categorias ──< artigos >── usuarios
                  │
                  └──< comentarios >── usuarios (opcional)
                  
categorias ──< categorias (parent_id) [auto-relacionamento hierárquico]
comentarios ──< comentarios (parent_id) [respostas aninhadas]
```

### 6.3 Dados Iniciais (Seed)

- **1 admin**: admin@saberes.com / admin123 (bcrypt)
- **7 configurações**: nome do site, descrição, email, tema escuro, paginação, etc.
- **2 páginas**: Sobre e Contato

---

## 7. Painel Administrativo

### 7.1 Dashboard

O dashboard exibe cards estatísticos:
- Total de artigos, visualizações, categorias, comentários, usuários
- Artigos recentes (últimos 5)
- Comentários recentes (últimos 5)
- Log de atividades (últimas 10 ações)

### 7.2 Gerenciamento de Artigos

- Listagem com busca e paginação
- Editor rico **TinyMCE 7** (WYSIWYG) com upload de imagens via drag-and-drop
- Controles de status: rascunho → publicado → arquivado
- Slugs gerados automaticamente a partir do título
- Tags separadas por vírgula (buscáveis via FULLTEXT)

**Fluxo de criação:**
```
1. Admin clica "Novo Artigo"
2. Preenche formulário (título, categoria, conteúdo com TinyMCE, tags)
3. Escolhe status (rascunho ou publicado)
4. Salva → INSERT no banco
5. Log de atividade registrado
6. Se publicado → aparece no frontend
```

### 7.3 Gerenciamento de Categorias

- Listagem + formulário de criação na mesma página
- Hierarquia (categoria pai)
- Ícone (Bootstrap Icons) e cor personalizáveis
- Exibidas no menu de navegação do site

### 7.4 Moderação de Comentários

- Fluxo: pendente → aprovar / rejeitar
- Visitantes podem comentar (nome + email)
- Usuários logados comentam automaticamente vinculados à conta
- Comentários aprovados aparecem no artigo
- Comentários podem ter respostas (thread)

### 7.5 Galeria de Mídia

- Upload manual ou via TinyMCE (AJAX)
- Upload validado: extensões (jpg, png, gif, webp, svg), tamanho máx 5MB
- Listagem com thumbnails
- Botão "Copiar URL" para facilitar inserção em artigos
- Exclusão de arquivos

### 7.6 Gerenciamento de Usuários (admin apenas)

- Lista todos os usuários
- Ações: banir / ativar / deletar
- Níveis: admin, editor, user

### 7.7 Páginas Estáticas

- Criação e edição de páginas (Sobre, Contato, etc.)
- Opção de exibir ou não no menu de navegação
- Ordenação personalizável

---

## 8. Funcionalidades do Frontend

### 8.1 Página Inicial

- Seção **hero** com chamada para ação
- Grid de **categorias** com ícones e cores
- Lista de **artigos recentes** com cards
- Design responsivo adaptável a mobile

### 8.2 Página de Artigo

- Cabeçalho com categoria, título, autor, data, views
- **Contador de visualizações** (1 por sessão para evitar refresh)
- Conteúdo renderizado com HTML permitido
- Tags clicáveis que levam à busca
- Seção de **comentários** com formulário para visitantes e logados
- Comentários aninhados (respostas)

### 8.3 Busca

- Busca textual usando `LIKE` no MySQL
- Pesquisa em título, resumo, conteúdo e tags
- Resultados paginados
- URLs amigáveis: `/busca/{termo}`

### 8.4 Página de Categoria

- Listagem de artigos por categoria
- Paginação (12 artigos por página, configurável)
- Breadcrumb de navegação

### 8.5 Design Responsivo

- **Tema escuro** padrão com toggle para tema claro
- CSS custom properties para fácil estilização
- Glassmorphism (efeito vidro) nos cards
- Gradientes nos elementos de destaque
- Mobile-first (menu colapsável, sidebar adaptável)

### 8.6 XML Sitemap

- Geração dinâmica de sitemap em `/sitemap.php`
- Inclui: homepage, artigos, categorias, páginas, busca
- Prioridades e frequências configuradas por tipo de conteúdo
- Útil para SEO e indexação por mecanismos de busca

### 8.7 Página 404 Personalizada

- Exibe "Página não encontrada"
- Sugere categorias do site para navegação alternativa

---

## 9. Testes Realizados

### 9.1 Resumo dos Testes

| # | Funcionalidade                  | Resultado |
|---|---------------------------------|-----------|
| 1 | Página inicial (index.php)      | ✅ OK     |
| 2 | Página de artigo                | ✅ OK     |
| 3 | Página de categoria             | ✅ OK     |
| 4 | Página estática (Sobre/Contato) | ✅ OK     |
| 5 | Busca                           | ✅ OK     |
| 6 | XML Sitemap                     | ✅ OK     |
| 7 | Página 404                      | ✅ OK     |
| 8 | Login (admin)                   | ✅ OK     |
| 9 | Registro de usuário             | ✅ OK     |
| 10| Logout                          | ✅ OK     |
| 11| Dashboard admin                 | ✅ OK     |
| 12| CRUD Categorias (criar/listar)  | ✅ OK     |
| 13| CRUD Artigos (criar)            | ✅ OK     |
| 14| Comentário no artigo            | ✅ OK     |
| 15| Moderação de comentário         | ✅ OK     |
| 16| Admin Páginas Estáticas         | ✅ OK     |
| 17| Admin Usuários                  | ✅ OK     |
| 18| Admin Mídia                     | ✅ OK     |
| 19| URLs amigáveis (.htaccess)      | ✅ OK     |
| 20| Instalação (install.php)        | ✅ OK     |
| 21| Tema claro/escuro               | ✅ OK     |

### 9.2 Ambiente de Teste

- **Servidor**: Apache 2.4.58 (XAMPP)
- **PHP**: 8.2.12
- **Banco**: MariaDB 12.2.2
- **Sistema**: Linux (via terminal)

---

## 10. Bugs Encontrados e Corrigidos

### 10.1 Hash de Senha Incorreto no Schema SQL

**Arquivo**: `database/schema.sql:174`

**Problema**: O hash armazenado no banco correspondia à senha `password`, mas o comentário indicava `admin123`. Isso impedia o login com a senha documentada.

**Correção**: Substituído pelo hash correto de `admin123` usando `password_hash(PASSWORD_BCRYPT)`.

**Impacto**: Qualquer nova instalação agora permite login com admin123 conforme documentado.

---

### 10.2 Senha do Banco de Dados Incorreta

**Arquivo**: `includes/Database.php:10`

**Problema**: A constante `DB_PASS` estava definida como string vazia (`''`), mas o MySQL do ambiente exigia a senha `novasenha`.

**Correção**: Alterado `DB_PASS` de `''` para `'novasenha'`.

**Impacto**: O sistema agora conecta ao banco de dados corretamente.

---

### 10.3 Loop de Redirecionamento nas URLs Amigáveis

**Arquivo**: `.htaccess`

**Problema**: As regras de redirecionamento SEO (linhas 10-19) estavam sendo aplicadas mesmo após a reescrita interna das URLs amigáveis, causando:
1. Loop de redirecionamento infinito (apenas interrompido pelo navegador)
2. URLs geradas sem o prefixo `/portal-saberes/` (ex: `/artigo/gnose` em vez de `/portal-saberes/artigo/gnose`)

**Causa**: O Apache reaplica as regras RewriteRule após uma reescrita interna. As regras de redirecionamento capturavam a query string gerada pela reescrita e redirecionavam novamente.

**Correção**:
1. Adicionado `RewriteBase /portal-saberes/` para que os redirecionamentos relativos funcionem corretamente
2. Adicionado `RewriteCond %{ENV:REDIRECT_STATUS} ^$` nas regras de redirecionamento para que só afetem requisições diretas (não reescritas internamente)
3. Removida a barra inicial dos destinos de redirecionamento (ex: `artigo/%1` em vez de `/artigo/%1`)

**Impacto**: URLs amigáveis funcionam corretamente sem loops de redirecionamento.

---

### 10.4 Redirecionamento Pós-Comentário Usando URL Antiga

**Arquivo**: `artigo.php:89`

**Problema**: Após enviar um comentário, o JavaScript redirecionava para `artigo.php?slug={slug}` (formato antigo), que sofria um redirect 301 para a URL amigável.

**Correção**: Alterado para redirecionar diretamente para a URL amigável: `APP_URL . '/artigo/' . esc($slug)`.

**Impacto**: Redirecionamento direto sem redirecionamento extra, melhor performance e experiência.

---

## 11. Como Executar o Projeto

### Pré-requisitos

- Apache com mod_rewrite habilitado
- PHP 8.x
- MySQL / MariaDB

### Instalação

```bash
# 1. Copiar para o diretório do servidor web
cp -r portal-saberes /opt/lampp/htdocs/

# 2. Iniciar Apache e MySQL

# 3. Acessar o instalador
# http://localhost/portal-saberes/install.php
# Clique em "Instalar Banco de Dados"

# 4. (Recomendado) Remover install.php após instalação
rm /opt/lampp/htdocs/portal-saberes/install.php

# 5. Acessar:
# Site:  http://localhost/portal-saberes/
# Admin: http://localhost/portal-saberes/auth/login.php

# 6. Credenciais padrão:
# Email: admin@saberes.com
# Senha: admin123
```

### Configuração do Banco de Dados

Edite `includes/Database.php` se necessário:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'portal_saberes');
define('DB_USER', 'root');
define('DB_PASS', 'suasenhaaqui');   // <-- ajuste aqui
define('DB_CHARSET', 'utf8mb4');
```

---

*Documentação gerada em 30/05/2026 — Portal Saberes Ancestrais v1.0.0*
