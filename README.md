# 🕉️ Portal Saberes Ancestrais

Wiki colaborativa sobre saberes ancestrais — gnose, espiritualidade, hermetismo, cristianismo esotérico e filosofia perene.

## Funcionalidades

- **Artigos** com categorias, tags, busca fulltext e contador de views
- **Categorias Gnósticas** — Gnose, Cristianismo Esotérico, Hermetismo, Teosofia, Meditação, Regeneração, Música-Sons
- **Citações Aleatórias do Dia** com conteúdo gnóstico importado
- **Multimídia** — player de áudio/vídeo integrado via Cloudinary
- **Autenticação** 3 níveis: admin / editor / user
- **Comentários** com moderação e respostas encadeadas
- **CAPTCHA** matemático + Rate Limiting + CSRF tokens
- **Busca Avançada** com filtros por categoria, data e ordenação
- **Editor WYSIWYG** (TinyMCE 7) com upload drag-and-drop para Cloudinary
- **Painel Admin** completo: dashboard, CRUD de artigos/categorias/usuários, galeria de mídia
- **SEO** — URLs amigáveis, sitemap XML automático, página 404 customizada
- **Cache** em arquivo + compressão Gzip
- **Responsivo** (mobile + desktop)

## Stack

| Camada | |
|--------|-|
| Backend | PHP 8.3+ nativo |
| Banco | MySQL/MariaDB |
| Frontend | HTML5 + CSS3 + JavaScript vanilla |
| Mídia | Cloudinary (áudio, vídeo, imagens) |
| Editor | TinyMCE 7 |
| Ícones | Bootstrap Icons |

## Conteúdo

O portal organiza saberes em categorias gnósticas especializadas, cada uma com artigos, referências e multimídia relacionada. O conteúdo pode ser importado via scripts CLI ou pela API de saberes.

## Arquitetura

```
├── admin/        Painel administrativo
├── api/          Endpoints de importação e utilidades
├── assets/       CSS, JS, fontes
├── auth/         Login, registro, logout
├── config/       Configuração global (ignorado pelo git)
├── database/     Schemas SQL
├── docs/         Documentação e guias
├── includes/     Core: Database, Security, Cache, Cloudinary
└── uploads/      Imagens (local/development)
```

## Deploy

Guia completo em [docs/GUIA_DEPLOY_CLOUDINARY_RENDER.md](docs/GUIA_DEPLOY_CLOUDINARY_RENDER.md) — Cloudinary + Render (Docker).

## Licença

Projeto livre para estudo e uso pessoal.
