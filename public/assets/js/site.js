/**
 * JavaScript comum a todas as páginas públicas.
 * Menu mobile: abrir/fechar, fecha ao clicar em link ou apertar Esc.
 */
(function () {
    var botao = document.querySelector('.nav-toggle');
    var menu = document.getElementById('site-nav');
    if (!botao || !menu) return;

    function alternar(abrir) {
        menu.classList.toggle('is-open', abrir);
        botao.setAttribute('aria-expanded', abrir ? 'true' : 'false');
        document.body.style.overflow = abrir ? 'hidden' : '';
    }

    botao.addEventListener('click', function () {
        alternar(!menu.classList.contains('is-open'));
    });

    // Fecha o menu ao clicar em um link (navegação no celular).
    menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            alternar(false);
        });
    });

    // Esc fecha o menu e devolve o foco ao botão.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) {
            alternar(false);
            botao.focus();
        }
    });

    // Fecha se a tela for redimensionada para desktop.
    window.addEventListener('resize', function () {
        if (window.innerWidth > 900 && menu.classList.contains('is-open')) {
            alternar(false);
        }
    });
})();
