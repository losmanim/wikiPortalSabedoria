/**
 * Portal Saberes Ancestrais — Enhanced JS
 * Integração com elementos do Saberes de Coração
 */

document.addEventListener('DOMContentLoaded', function () {

    // ─── Scroll Reveal (IntersectionObserver) ───
    const revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        revealEls.forEach(el => observer.observe(el));
    } else {
        revealEls.forEach(el => el.classList.add('visible'));
    }

    // ─── Tema (modo claro/escuro) ───
    const btnTema = document.getElementById('btnTema');
    if (btnTema) {
        const temaAtual = localStorage.getItem('portal_tema') || 'escuro';
        if (temaAtual === 'claro') document.body.classList.add('modo-claro');

        btnTema.addEventListener('click', function () {
            document.body.classList.toggle('modo-claro');
            const isClaro = document.body.classList.contains('modo-claro');
            localStorage.setItem('portal_tema', isClaro ? 'claro' : 'escuro');
            this.innerHTML = isClaro ? '<i class="bi bi-moon"></i>' : '<i class="bi bi-sun"></i>';
        });

        btnTema.innerHTML = temaAtual === 'claro'
            ? '<i class="bi bi-moon"></i>'
            : '<i class="bi bi-sun"></i>';
    }

    // ─── Modal ───
    window.abrirModal = function (titulo, conteudoHtml) {
        const overlay = document.getElementById('modalOverlay');
        const tituloEl = document.getElementById('modalTitulo');
        const contentEl = document.getElementById('modalContent');
        if (!overlay || !tituloEl || !contentEl) return;
        tituloEl.textContent = titulo;
        contentEl.innerHTML = conteudoHtml;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    };

    window.fecharModal = function (e) {
        if (e && e.target !== e.currentTarget) return;
        const overlay = document.getElementById('modalOverlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const overlay = document.getElementById('modalOverlay');
            if (overlay?.classList.contains('active')) {
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        }
    });

    // ─── Toast ───
    window.mostrarToast = function (mensagem) {
        const container = document.getElementById('toastContainer') || (function () {
            const c = document.createElement('div');
            c.id = 'toastContainer';
            c.className = 'toast-container';
            document.body.appendChild(c);
            return c;
        })();
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = mensagem;
        container.appendChild(toast);
        setTimeout(() => { if (toast.parentNode) toast.remove(); }, 2500);
    };

    // ─── Saber aleatório ───
    window.saberAleatorio = function () {
        fetch('api/saber_aleatorio.php')
            .then(r => r.json())
            .then(d => {
                if (d.slug) window.location.href = 'artigo.php?slug=' + d.slug;
                else mostrarToast('Nenhum saber encontrado');
            })
            .catch(() => mostrarToast('Erro ao buscar saber aleatório'));
    };

    // ─── Header scroll aprimorado ───
    let lastScrollY = window.scrollY;
    window.addEventListener('scroll', function () {
        const header = document.querySelector('.portal-header');
        const currentScrollY = window.scrollY;
        if (!header) return;
        if (currentScrollY > 80) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        lastScrollY = currentScrollY;
    }, { passive: true });
});
