# 🕉️ Portal Saberes Ancestrais

Wiki/CMS colaborativa sobre saberes ancestrais que unem ciência, espiritualidade e filosofia.

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.3+ (nativo, sem frameworks) |
| Banco | MySQL/MariaDB 10+ |
| Frontend | HTML5 + CSS3 + JavaScript vanilla |
| Editor | TinyMCE 7 (WYSIWYG) |
| Ícones | Bootstrap Icons |
| SEO | URLs amigáveis (mod_rewrite) + Sitemap XML |

## Funcionalidades

- ✅ **Artigos** com categorias, tags, busca fulltext, contador de views
- ✅ **Categorias** hierárquicas com ícones e cores personalizadas
- ✅ **Categorias Gnósticas** especializadas (Gnose, Cristianismo Esotérico, Hermetismo, etc.)
- ✅ **Citações Aleatórias do Dia** com conteúdo gnóstico pré-importado
- ✅ **Autenticação** com 3 níveis: admin / editor / user
- ✅ **Comentários** com moderação e respostas encadeadas
- ✅ **CAPTCHA** matemático em formulários (registro, comentários)
- ✅ **Rate Limiting** proteção contra ataques de força bruta
- ✅ **Segurança** CSRF tokens, sanitização de input, headers de segurança
- ✅ **Sistema de Cache** em arquivo para performance
- ✅ **Busca Avançada** com filtros por categoria, data e ordenação
- ✅ **Importador Multimídia** para conteúdo gnóstico de áudio e vídeo
- ✅ **Páginas estáticas** com suporte a menu
- ✅ **Painel Admin** completo: dashboard, CRUD, upload de imagens
- ✅ **Editor WYSIWYG** (TinyMCE) com upload drag-and-drop
- ✅ **Galeria de Mídia** com upload, preview, copiar URL, deletar
- ✅ **URLs amigáveis** (SEO) com redirect 301 automático
- ✅ **Sitemap XML** automático
- ✅ **Página 404** customizada com sugestões
- ✅ **Responsivo** (mobile + desktop)
- ✅ **Cache** de assets + compressão Gzip
- ✅ **HTACCESS** seguro (proteção SQL/MD/LOG/ENV, sem Indexes)

## Estrutura

```
portal-saberes/
├── admin/
│   ├── artigos/index.php      # CRUD artigos + TinyMCE
│   ├── categorias/index.php   # Gerenciar categorias
│   ├── comentarios/index.php  # Moderar comentários
│   ├── index.php              # Dashboard com stats
│   ├── midia/index.php        # Galeria de upload
│   ├── paginas/index.php      # Gerenciar páginas
│   ├── usuarios/index.php     # Gerenciar usuários
│   └── sidebar.php            # Navegação admin
├── assets/css/                # Estilos (admin.css + portal.css)
├── auth/                      # Login, registro, logout
├── config/app.php             # Configurações globais
├── database/
│   ├── schema.sql             # Schema principal (8 tabelas)
│   ├── quotes_schema.sql      # Schema de citações
│   └── gnostic_categories.sql # Categorias gnósticas
├── includes/
│   ├── Database.php           # PDO Singleton
│   ├── Security.php           # CSRF, Rate Limiting, Sanitização
│   ├── Captcha.php            # CAPTCHA matemático
│   ├── Cache.php              # Sistema de cache em arquivo
│   ├── Quotes.php             # Sistema de citações
│   ├── functions.php          # Funções auxiliares
│   ├── header.php             # Header com citação do dia
│   └── footer.php             # Footer
├── multimidia/                # Arquivos de áudio e vídeo gnósticos
│   ├── audios-lz/             # Áudios organizados por categoria
│   └── videos-lz/             # Vídeos organizados por categoria
├── uploads/                   # Imagens enviadas
├── cache/                     # Cache do sistema (criado automaticamente)
├── .htaccess                  # URLs amigáveis + segurança + cache
├── 404.php                    # Página 404 customizada
├── artigo.php                 # Página de artigo
├── busca.php                  # Busca avançada com filtros
├── categoria.php              # Página de categoria
├── importar.php               # Seed automático do Modelos/
├── importar_multimedia.php    # Importador de conteúdo multimídia
├── importar_quotes.php        # Importador de citações gnósticas
├── index.php                  # Home
├── install.php                # Instalador one-click
├── pagina.php                 # Página estática
├── sitemap.php                # XML Sitemap
└── README.md
```

## Instalação Local (XAMPP)

```bash
# 1. Copie para o htdocs
cp -r portal-saberes /opt/lampp/htdocs/
# ou
cp -r portal-saberes /home/lzntn/xampp/htdocs/
# ou
cp -r portal-saberes /var/www/html/

# 2. Inicie Apache + MySQL

# 3. Acesse no navegador
http://localhost/portal-saberes/install.php

# 4. Clique "Instalar Banco de Dados"
#    (cria banco, 8 tabelas, admin + categorias iniciais)

# 5. Delete install.php após instalar

# 6. (Opcional) Importar conteúdo do Modelos/
php importar.php
# ou acesse http://localhost/portal-saberes/importar.php

# 7. Importar categorias gnósticas
mysql -u root portal_saberes < database/gnostic_categories.sql

# 8. Importar citações gnósticas
php importar_quotes.php

# 9. Importar conteúdo multimídia (áudios e vídeos)
php importar_multimedia.php

# 10. Acesse o portal
http://localhost/portal-saberes/
```

### Credenciais Padrão

| Papel | Email | Senha |
|-------|-------|-------|
| Admin | admin@saberes.com | admin123 |
| User | (criar no registro) | (definir no cadastro) |

## Deploy no InfinityFree

```bash
# 1. Crie conta em https://infinityfree.com
# 2. Crie um site (subdomínio grátis)
# 3. Acesse o cPanel do site

# 4. Banco de Dados:
#    - MySQL Database Wizard
#    - Crie banco + usuário + senha
#    - Anote: host, dbname, user, pass

# 5. Edite config/database.php:
#    Altere host, dbname, user, password

# 6. Edite config/app.php:
#    Altere APP_URL para https://seudominio.infinityfreeapp.com
#    Altere APP_ENV para 'production'

# 7. Upload via FTP:
#    - Conecte ao servidor FTP
#    - Envie tudo para htdocs/
#    - (NÃO envie importar.php)

# 8. Acesse no navegador:
#    https://seudominio.infinityfreeapp.com/install.php
#    Clique "Instalar Banco de Dados"

# 9. Delete install.php via FTP

# 10. Pronto! Acesse o portal.
```

## Configuração do Banco

Edite `includes/Database.php`:

```php
private $host = '127.0.0.1';     // Local: 127.0.0.1 | InfinityFree: sqlxxx.infinityfree.com
private $dbname = 'portal_saberes';
private $user = 'root';          // Local: root | InfinityFree: if0_XXXXXXX
private $pass = '';              // Local: vazio | InfinityFree: sua_senha
```

Edite `config/app.php`:

```php
define('APP_URL', 'http://localhost/portal-saberes');  // Altere para seu domínio
define('APP_ENV', 'development');                       // 'production' em produção
```

## Manutenção

### Backup do Banco
```bash
mysqldump -u root portal_saberes > backup_$(date +%Y%m%d).sql
```

### Adicionar Conteúdo
- Use `importar.php` para seeds dos diretórios em `/home/lzntn/Modelos/`
- Use `importar_quotes.php` para importar citações gnósticas
- Use `importar_multimedia.php` para importar conteúdo de áudio e vídeo

### Gerenciar Cache
```bash
# Limpar cache expirado
php -r "require_once 'includes/Cache.php'; Cache::clearExpired();"

# Limpar todo o cache
php -r "require_once 'includes/Cache.php'; Cache::clear();"

# Ver estatísticas do cache
php -r "require_once 'includes/Cache.php'; print_r(Cache::stats());"
```

### URLs Amigáveis
- Artigo: `https://site.com/artigo/slug-do-artigo`
- Categoria: `https://site.com/categoria/slug-da-categoria`
- Página: `https://site.com/pagina/slug-da-pagina`

Links antigos com `?slug=` são redirecionados automaticamente (301).

### Sitemap
- URL: `https://site.com/sitemap.php`
- Pode ser submetido ao Google Search Console

### Segurança
- CAPTCHA matemático em formulários públicos
- Rate limiting em login (5 tentativas/minuto), registro (3 tentativas/5 minutos), comentários (3 tentativas/minuto)
- CSRF tokens em todos os formulários
- Headers de segurança HTTP configurados
- Logs de segurança em `logs/security.log`

## Licença

Projeto livre para estudo e uso pessoal.
