# Guia: Subir o Portal Saberes Ancestrais para o GitHub

## 1. Criar o repositório no GitHub

1. Acesse https://github.com e faça login
2. Clique no **ícone de "+"** (canto superior direito) → **"New repository"**
3. Preencha:
   - **Repository name:** `wikiPortalSabedoria` (ou outro nome)
   - **Description (opcional):** "Portal colaborativo sobre saberes ancestrais — gnose, espiritualidade, hermetismo e filosofia perene"
   - **Visibility:** Público ou Privado (escolha)
   - **NÃO** marque "Add a README", ".gitignore" nem "license" — vamos subir o projeto existente
4. Clique em **"Create repository"**

Após criar, o GitHub vai mostrar uma página com instruções. Copie a URL do repositório (algo como `https://github.com/SEU_USUARIO/wikiPortalSabedoria.git`).

---

## 2. Inicializar o Git no projeto local

Abra o terminal na pasta do projeto:

```bash
cd /opt/lampp/htdocs/wikiPortalSabedoria
```

Inicialize o repositório Git:

```bash
git init
```

---

## 3. Criar o arquivo .gitignore

O `.gitignore` evita que arquivos desnecessários ou sensíveis sejam enviados.

Execute no terminal:

```bash
cat > .gitignore << 'EOF'
# Config
config/app.php

# Sensitive / logs
logs/
*.log

# Sessões PHP (se houver)
/tmp/

# IDE / OS
.vscode/
.idea/
*.swp
*.swo
*~
.DS_Store
Thumbs.db

# Node (se houver)
node_modules/

# Uploads (mantém só a estrutura)
uploads/*
!uploads/.gitkeep
EOF
```

> **Atenção:** O `config/app.php` foi incluído no `.gitignore` pois contém a senha do banco (vazia por enquanto, mas é boa prática). Se quiser versioná-lo, remova essa linha.

---

## 4. Adicionar e commitar os arquivos

```bash
# Ver o que será enviado
git status

# Adicionar tudo (exceto o que está no .gitignore)
git add .

# Criar o primeiro commit
git commit -m "feat: Portal Saberes Ancestrais - integracao Saberes de Coracao"
```

---

## 5. Conectar ao GitHub e enviar

Substitua `SEU_USUARIO` pelo seu nome de usuário do GitHub:

```bash
git remote add origin https://github.com/SEU_USUARIO/wikiPortalSabedoria.git
git branch -M main
git push -u origin main
```

Se pedir login/senha, use um **Personal Access Token** (não a senha da conta):

1. Vá em https://github.com/settings/tokens
2. Clique em **"Generate new token (classic)"**
3. Marque o escopo **`repo`**
4. Copie o token e use como senha quando o terminal pedir

---

## Resumo dos comandos

| Passo | Comando |
|-------|---------|
| Iniciar git | `git init` |
| Ver arquivos | `git status` |
| Adicionar tudo | `git add .` |
| Commitar | `git commit -m "mensagem"` |
| Conectar ao GitHub | `git remote add origin https://github.com/SEU_USUARIO/REPO.git` |
| Enviar | `git push -u origin main` |

---

## Próximos commits (quando alterar algo)

```bash
git add .
git commit -m "descricao do que mudou"
git push
```

---

> **Dica:** Se for acessar o projeto de outro computador, lembre-se de copiar o `config/app.php` com as credenciais do banco separadamente — ele não será versionado por segurança.
