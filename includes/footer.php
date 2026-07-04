    </main>

    <footer class="portal-footer">
        <div class="container footer-grid">
            <div class="footer-section">
                <h4>🕉️ Portal Saberes Ancestrais</h4>
                <p>Wiki colaborativa dedicada ao estudo e difusão dos saberes que unem ciência, espiritualidade e filosofia.</p>
            </div>
            <div class="footer-section">
                <h4>Navegação</h4>
                <ul>
                    <li><a href="<?= APP_URL ?>/index.php">Início</a></li>
                    <li><a href="<?= APP_URL ?>/biblioteca.php">📚 Biblioteca</a></li>
                    <li><a href="<?= APP_URL ?>/busca.php">Buscar</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Informações</h4>
                <ul>
                    <li><a href="<?= APP_URL ?>/pagina.php?slug=sobre">Sobre</a></li>
                    <li><a href="<?= APP_URL ?>/pagina.php?slug=comunidade">Comunidade</a></li>
                    <li><a href="<?= APP_URL ?>/pagina.php?slug=politica-edicao">Política de Edição</a></li>
                    <li><a href="<?= APP_URL ?>/pagina.php?slug=faq">FAQ</a></li>
                    <li><a href="<?= APP_URL ?>/pagina.php?slug=licenca">Licença</a></li>
                    <li><a href="<?= APP_URL ?>/pagina.php?slug=contato">Contato</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Filosofia</h4>
                <p>"O conhecimento sem prática é vazio.<br>A prática sem conhecimento é cega."</p>
                <p style="margin-top:10px;font-size:0.8rem;opacity:0.5">
                    © <?= date('Y') ?> — v<?= APP_VERSION ?>
                </p>
            </div>
        </div>
    </footer>

    <!-- Modal -->
    <div class="modal-overlay" id="modalOverlay" onclick="fecharModal(event)" role="dialog" aria-modal="true" aria-labelledby="modalTitulo">
        <div class="modal" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h2 class="modal-titulo" id="modalTitulo"></h2>
                <button class="modal-close" onclick="document.getElementById('modalOverlay').classList.remove('active');document.body.style.overflow=''" aria-label="Fechar">✕</button>
            </div>
            <div class="modal-content" id="modalContent"></div>
        </div>
    </div>

    <button id="backToTop" class="back-to-top" aria-label="Voltar ao topo">
        <i class="bi bi-arrow-up"></i>
    </button>

    <script>
    // Nav toggle mobile
    const navToggle = document.getElementById('navToggle');
    const navList = document.getElementById('navList');

    navToggle?.addEventListener('click', function() {
        navList?.classList.toggle('active');
        // Animate hamburger
        this.classList.toggle('active');
    });

    // Dropdown mobile toggle
    document.querySelectorAll('.nav-dropdown').forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        toggle?.addEventListener('click', function(e) {
            if (window.innerWidth <= 992) {
                e.preventDefault();
                dropdown.classList.toggle('active');
            }
        });
    });

    // Close menu when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.main-nav') && !e.target.closest('.nav-toggle')) {
            navList?.classList.remove('active');
            navToggle?.classList.remove('active');
        }
    });

    // Close menu on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            navList?.classList.remove('active');
            navToggle?.classList.remove('active');
            document.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('active'));
        }
    });

    // Scroll progress
    window.addEventListener('scroll', function() {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const pct = docHeight > 0 ? Math.min((scrollTop / docHeight) * 100, 100) : 0;
        const fill = document.getElementById('progress-fill');
        if (fill) fill.style.width = pct + '%';
    });

    // Back to top
    window.addEventListener('scroll', function() {
        const btn = document.getElementById('backToTop');
        if (btn) btn.classList.toggle('visible', window.scrollY > 500);
    });

    document.getElementById('backToTop')?.addEventListener('click', function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Header scroll effect
    let lastScroll = 0;
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.portal-header');
        const secondaryHeader = document.querySelector('.secondary-header');
        const currentScroll = window.scrollY;

        if (currentScroll > 100) {
            header?.classList.add('scrolled');
            if (secondaryHeader) {
                secondaryHeader.style.top = '60px';
            }
        } else {
            header?.classList.remove('scrolled');
            if (secondaryHeader) {
                secondaryHeader.style.top = '73px';
            }
        }

        lastScroll = currentScroll;
    });
    </script>
</body>
</html>
