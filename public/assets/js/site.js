/**
 * JavaScript comum a todas as páginas públicas.
 * Por enquanto: abrir e fechar o menu no celular.
 */
(function () {
    var botao = document.querySelector('.nav-toggle');
    var menu = document.getElementById('site-nav');
    if (!botao || !menu) return;

    function alternar(abrir) {
        menu.classList.toggle('is-open', abrir);
        botao.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    }

    botao.addEventListener('click', function () {
        alternar(!menu.classList.contains('is-open'));
    });

    // Esc fecha o menu e devolve o foco ao botão (acessibilidade por teclado).
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && menu.classList.contains('is-open')) {
            alternar(false);
            botao.focus();
        }
    });
})();
