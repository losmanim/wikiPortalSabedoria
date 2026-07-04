<div class="sidebar-header">
    <div class="sidebar-logo">
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="sidebarLogoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" style="stop-color:#f39c12"/>
                    <stop offset="100%" style="stop-color:#e74c3c"/>
                </linearGradient>
            </defs>
            <circle cx="50" cy="50" r="45" fill="none" stroke="url(#sidebarLogoGradient)" stroke-width="2"/>
            <text x="50" y="58" text-anchor="middle" font-size="40">🕉️</text>
        </svg>
    </div>
    <div class="sidebar-title">
        <span class="sidebar-title-main">Portal Saberes</span>
        <span class="sidebar-title-sub">Painel Admin</span>
    </div>
</div>
<nav class="sidebar-nav">
    <a href="../index.php" class="nav-item">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>
    <a href="../artigos/index.php" class="nav-item">
        <i class="bi bi-file-text"></i>
        <span>Artigos</span>
    </a>
    <a href="../categorias/index.php" class="nav-item">
        <i class="bi bi-folder"></i>
        <span>Categorias</span>
    </a>
    <a href="../comentarios/index.php" class="nav-item">
        <i class="bi bi-chat-dots"></i>
        <span>Comentários</span>
    </a>
    <a href="../usuarios/index.php" class="nav-item">
        <i class="bi bi-people"></i>
        <span>Usuários</span>
    </a>
    <a href="../paginas/index.php" class="nav-item">
        <i class="bi bi-file-earmark"></i>
        <span>Páginas</span>
    </a>
    <a href="../midia/index.php" class="nav-item">
        <i class="bi bi-images"></i>
        <span>Mídia</span>
    </a>
    <div class="nav-divider"></div>
    <a href="../../index.php" target="_blank" class="nav-item nav-item-external">
        <i class="bi bi-box-arrow-up-right"></i>
        <span>Ver Site</span>
    </a>
    <a href="../../auth/logout.php" class="nav-item nav-item-logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Sair</span>
    </a>
</nav>
<div class="sidebar-footer">
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <i class="bi bi-person-circle"></i>
        </div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name"><?= esc($_SESSION['usuario_nome']) ?></span>
            <span class="sidebar-user-role"><?= esc($_SESSION['usuario_papel'] ?? 'Usuário') ?></span>
        </div>
    </div>
</div>
