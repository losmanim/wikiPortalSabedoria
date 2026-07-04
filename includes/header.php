<?php
/**
 * Header do Portal
 * @var array $categorias Deve ser definido antes de incluir
 */
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo ?? APP_NAME ?> - <?= APP_NAME ?></title>
    <meta name="description" content="<?= $descricao ?? APP_DESC ?>">
    <meta name="keywords" content="saberes ancestrais, gnose, epigenética, hermetismo, teosofia, meditação, conhecimento">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🕉️</text></svg>">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#0a0a1a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Saberes Ancestrais">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/portal.css">
    <script src="<?= APP_URL ?>/assets/js/portal.js" defer></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/wikiPortalSabedoria/sw.js').catch(() => {});
            });
        }
    </script>
</head>
<body>
    <!-- Scroll Progress -->
    <div id="progress-bar"><div id="progress-fill"></div></div>

    <!-- Header -->
    <header class="portal-header">
        <div class="header-wrapper">
            <div class="container header-content">
                <a href="<?= APP_URL ?>/index.php" class="logo">
                    <div class="logo-icon">
                        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#f39c12"/>
                                    <stop offset="100%" style="stop-color:#e74c3c"/>
                                </linearGradient>
                            </defs>
                            <circle cx="50" cy="50" r="45" fill="none" stroke="url(#logoGradient)" stroke-width="2"/>
                            <text x="50" y="58" text-anchor="middle" font-size="40">🕉️</text>
                        </svg>
                    </div>
                    <div class="logo-text">
                        <span class="logo-title">Portal Saberes</span>
                        <span class="logo-subtitle">Ancestrais</span>
                    </div>
                </a>

                <nav class="main-nav">
                    <button class="nav-toggle" id="navToggle" aria-label="Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                    <ul class="nav-list" id="navList">
                        <li><a href="<?= APP_URL ?>/index.php" class="nav-link">
                            <i class="bi bi-house"></i> Início
                        </a></li>
                        <li><a href="<?= APP_URL ?>/biblioteca.php" class="nav-link">
                            <i class="bi bi-book"></i> Biblioteca
                        </a></li>
                        <li class="nav-dropdown">
                            <a href="#" class="nav-link dropdown-toggle">
                                <i class="bi bi-grid-3x3"></i> Categorias
                                <i class="bi bi-chevron-down dropdown-arrow"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isset($categorias)): ?>
                                    <?php foreach ($categorias as $cat): ?>
                                    <li><a href="<?= APP_URL ?>/categoria.php?slug=<?= esc($cat['slug']) ?>" class="dropdown-item">
                                        <i class="<?= esc($cat['icone']) ?>"></i> <?= esc($cat['nome']) ?>
                                    </a></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li><a href="<?= APP_URL ?>/busca.php" class="nav-link nav-search">
                            <i class="bi bi-search"></i> Buscar
                        </a></li>
                    </ul>
                </nav>

                <div class="header-actions">
                    <button class="action-btn" id="btnAleatorio" onclick="saberAleatorio()" title="Saber aleatório" aria-label="Saber aleatório">
                        <i class="bi bi-shuffle"></i>
                    </button>
                    <button class="action-btn" id="btnTema" title="Alternar tema" aria-label="Alternar tema">
                        <i class="bi bi-sun"></i>
                    </button>
                    <?php if (esta_logado()): ?>
                        <div class="user-menu">
                            <div class="user-avatar">
                                <i class="bi bi-person-circle"></i>
                            </div>
                            <div class="user-info">
                                <span class="user-name"><?= esc($_SESSION['usuario_nome']) ?></span>
                                <span class="user-role"><?= esc($_SESSION['usuario_papel'] ?? 'Usuário') ?></span>
                            </div>
                            <div class="user-actions">
                                <a href="<?= APP_URL ?>/admin/index.php" class="action-btn" title="Painel Admin">
                                    <i class="bi bi-gear"></i>
                                </a>
                                <a href="<?= APP_URL ?>/auth/logout.php" class="action-btn action-btn-danger" title="Sair">
                                    <i class="bi bi-box-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-outline">
                            <i class="bi bi-person"></i> Entrar
                        </a>
                        <a href="<?= APP_URL ?>/auth/registro.php" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Cadastrar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Secondary Header -->
    <nav class="secondary-header">
        <div class="container secondary-nav">
            <a href="<?= APP_URL ?>/pagina.php?slug=sobre" class="secondary-link">
                <i class="bi bi-info-circle"></i> Sobre
            </a>
            <a href="<?= APP_URL ?>/pagina.php?slug=comunidade" class="secondary-link">
                <i class="bi bi-people"></i> Comunidade
            </a>
            <a href="<?= APP_URL ?>/pagina.php?slug=politica-edicao" class="secondary-link">
                <i class="bi bi-pencil-square"></i> Política de Edição
            </a>
            <a href="<?= APP_URL ?>/pagina.php?slug=faq" class="secondary-link">
                <i class="bi bi-question-circle"></i> FAQ
            </a>
            <a href="<?= APP_URL ?>/pagina.php?slug=licenca" class="secondary-link">
                <i class="bi bi-shield-check"></i> Licença
            </a>
            <a href="<?= APP_URL ?>/pagina.php?slug=contato" class="secondary-link">
                <i class="bi bi-envelope"></i> Contato
            </a>
        </div>
    </nav>

    <!-- Quote of the Day -->
    <?php
    require_once __DIR__ . '/Quotes.php';
    $quotes = new Quotes(Database::getInstance());
    $dailyQuote = $quotes->getDailyQuote();
    if ($dailyQuote):
    ?>
    <div class="daily-quote">
        <div class="container">
            <div class="quote-content">
                <i class="bi bi-quote quote-icon"></i>
                <p class="quote-text"><?= esc($dailyQuote['texto']) ?></p>
                <span class="quote-author">
                    <?php if ($dailyQuote['autor']): ?>
                        — <?= esc($dailyQuote['autor']) ?>
                    <?php endif; ?>
                    <?php if ($dailyQuote['fonte']): ?>
                        <small class="quote-source">(<?= esc($dailyQuote['fonte']) ?>)</small>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="container main-content">
