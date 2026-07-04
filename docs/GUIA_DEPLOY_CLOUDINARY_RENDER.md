# Guia de Deploy: Cloudinary + Render

## Sumário

- [Parte 1 — Cloudinary (mídias)](#parte-1--cloudinary-mídias)
- [Parte 2 — Render (hospedagem)](#parte-2--render-hospedagem)
- [Parte 3 — Manutenção](#parte-3--manutenção)

---

## Parte 1 — Cloudinary (mídias)

### 1.1 Conta e credenciais

- **Conta:** já criada e mídias já enviadas
- **Cloud Name:** `deblzssiw`
- **API Key:** `149467494657849`
- **API Secret:** configurado em `config/app.php`

### 1.2 O que já foi implementado no código

#### a) Helper Cloudinary (`includes/Cloudinary.php`)

Classe com métodos para:
- `listResources($folder)` — lista arquivos de uma pasta
- `listFolders()` — lista todas as pastas
- `upload($filePath, $publicId)` — faz upload
- `delete($publicId)` — deleta recurso
- `getUrl($resource)` — retorna URL segura

#### b) Config (`config/app.php`)

Constantes adicionadas:
```php
define('CLOUDINARY_CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME') ?: 'deblzssiw');
define('CLOUDINARY_API_KEY', getenv('CLOUDINARY_API_KEY') ?: '149467494657849');
define('CLOUDINARY_API_SECRET', getenv('CLOUDINARY_API_SECRET') ?: '5c2NdFfZN05t3iIzJwydYGm1Le4');
```

Em produção (Render), as credenciais serão lidas de variáveis de ambiente.

#### c) Importador (`importar_multimedia.php`)

Agora consulta a API do Cloudinary em vez do sistema de arquivos local:
- Mapeia pastas do Cloudinary para categorias do portal
- Gera artigos com player HTML (áudio/vídeo) e link para download
- Armazena URL do Cloudinary no campo `fonte`

Para rodar (quando o banco MySQL estiver disponível):
```bash
php importar_multimedia.php
```

#### d) Admin de uploads (`admin/midia/index.php`)

- Em produção (`APP_ENV=production`): upload vai para Cloudinary
- Em desenvolvimento: upload vai para o sistema de arquivos local
- Listagem busca do Cloudinary em produção, ou do diretório `uploads/` local

### 1.3 Mapeamento pastas → categorias

| Pasta Cloudinary | Slug da Categoria |
|-----------------|-------------------|
| `01_gnose-esoterismo` | gnose-esoterismo |
| `02_cristianismo-esoterico` | cristianismo-esoterico |
| `02_filosofia-consciencia` | filosofia-consciencia |
| `03_hermetismo-teosofia` | hermetismo-teosofia |
| `04_consciencia-meditacao` | consciencia-meditacao |
| `05_animes` | animes-animacoes |
| `05_corpo-regeneracao` | corpo-regeneracao |
| `06_musica-sons` | musica-sons |

### 1.4 Formato das URLs

```
https://res.cloudinary.com/deblzssiw/{resource_type}/upload/v{version}/{public_id}.{ext}
```

Exemplo:
```
https://res.cloudinary.com/deblzssiw/video/upload/v1782164596/05_animes/GENKAI-Voce-Nunca-Chegou-No-Seu-Limite-D_Media_xqoqco.mp4
```

---

## Parte 2 — Render (hospedagem)

> **Nota:** Render não suporta PHP nativamente. A solução é usar Docker.

### 2.1 Preparar o projeto

#### a) Criar Dockerfile

Na raiz do projeto, crie `Dockerfile`:

```dockerfile
FROM php:8.3-apache

RUN a2enmod rewrite

RUN docker-php-ext-install pdo pdo_mysql

ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/uploads

# Config dinâmica via env vars
RUN echo '<?php\
    define("APP_URL", getenv("APP_URL"));\
    define("APP_ENV", getenv("APP_ENV") ?: "production");\
    define("DB_HOST", getenv("DB_HOST"));\
    define("DB_PORT", getenv("DB_PORT") ?: "3306");\
    define("DB_NAME", getenv("DB_NAME"));\
    define("DB_USER", getenv("DB_USER"));\
    define("DB_PASS", getenv("DB_PASS"));\
    define("CLOUDINARY_CLOUD_NAME", getenv("CLOUDINARY_CLOUD_NAME"));\
    define("CLOUDINARY_API_KEY", getenv("CLOUDINARY_API_KEY"));\
    define("CLOUDINARY_API_SECRET", getenv("CLOUDINARY_API_SECRET"));\
?>' > /var/www/html/config/env.php
```

#### b) Atualizar Database.php para usar variáveis de ambiente

Edite `includes/Database.php`:

```php
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'portal_saberes');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');
```

#### c) Atualizar config/app.php para usar env vars

```php
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/wikiPortalSabedoria');
define('APP_ENV', getenv('APP_ENV') ?: 'development');
```

### 2.2 Escolher um banco de dados na nuvem

Render não oferece MySQL gratuito. Opções:

| Serviço | Limite Gratuito | Observação |
|---------|----------------|------------|
| **Aiven** | MySQL 1GB RAM, 5GB disco | Cartão de crédito |
| **PlanetScale** | 1GB armazenamento, 1GB banda/mês | Sem cartão |
| **Railway** | PostgreSQL 250MB | Precisa adaptar SQL |
| **InfinityFree** | MySQL remoto | Grátis, mas limitado |

**Recomendação:** PlanetScale

1. Acesse https://planetscale.com e cadastre-se
2. Crie database `portal_saberes`
3. Copie a connection string
4. Importe os schemas SQL:
```bash
mysql -h HOST -u USER -p --ssl-mode=REQUIRED < database/schema.sql
mysql -h HOST -u USER -p --ssl-mode=REQUIRED < database/gnostic_categories.sql
mysql -h HOST -u USER -p --ssl-mode=REQUIRED < database/quotes_schema.sql
mysql -h HOST -u USER -p --ssl-mode=REQUIRED < database/references_schema.sql
mysql -h HOST -u USER -p --ssl-mode=REQUIRED < database/gamification_schema.sql
mysql -h HOST -u USER -p --ssl-mode=REQUIRED < database/reviews_schema.sql
mysql -h HOST -u USER -p --ssl-mode=REQUIRED < database/versions_schema.sql
```

### 2.3 Fazer deploy no Render

1. Acesse https://dashboard.render.com
2. New + → **Web Service**
3. Conecte o repositório GitHub (`losmanim/wikiPortalSabedoria`)
4. Configure:

| Campo | Valor |
|-------|-------|
| Name | `wiki-portal-sabedoria` |
| Runtime | **Docker** |
| Branch | `main` |
| Plan | Free |
| Region | mais próxima |

5. Adicione as variáveis de ambiente:

```
APP_URL = https://wiki-portal-sabedoria.onrender.com
APP_ENV = production
DB_HOST = (host do PlanetScale/Aiven)
DB_PORT = 3306
DB_NAME = portal_saberes
DB_USER = (usuário)
DB_PASS = (senha)
CLOUDINARY_CLOUD_NAME = deblzssiw
CLOUDINARY_API_KEY = 149467494657849
CLOUDINARY_API_SECRET = 5c2NdFfZN05t3iIzJwydYGm1Le4
```

6. Clique **Deploy Web Service**

### 2.4 Cron Jobs (opcional)

Para rodar o importador de mídia periodicamente:

1. Dashboard → **Cron Jobs** → New Cron Job
2. Command: `php importar_multimedia.php`
3. Schedule: `0 3 * * 0` (domingos às 3h)
4. Env vars: mesmas do Web Service

---

## Parte 3 — Manutenção

### Backup do banco

```bash
mysqldump -h HOST -u USER -p --ssl-mode=REQUIRED portal_saberes > backup.sql
```

### Atualizar o site

```bash
git add .
git commit -m "descricao"
git push
```

Render faz deploy automático a cada push na `main`.

---

## Resumo da Arquitetura

```
Usuário ──► Render (Docker + PHP + Apache)
                │
                ├── PlanetScale (MySQL)
                │
                └── Cloudinary (áudios, vídeos, imagens)
```

## Custo Mensal (Free Tier)

| Serviço | Custo | Limites |
|---------|-------|---------|
| Render Web Service | Grátis | 750h/mês, 512MB RAM |
| PlanetScale | Grátis | 1GB armazenamento |
| Cloudinary | Grátis | 25GB armazenamento, 25GB banda |
| **Total** | **R$0** | |
