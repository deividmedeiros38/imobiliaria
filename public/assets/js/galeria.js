/**
 * Galeria de fotos da página do imóvel: foto grande, miniaturas e ampliação (lightbox).
 * As fotos chegam do PHP num <script type="application/json" id="galeria-dados">.
 */
(function () {
    var dados = document.getElementById('galeria-dados');
    var lb = document.getElementById('lightbox');
    var principal = document.getElementById('gallery-main-img');
    if (!dados || !lb || !principal) return;

    var fotos = JSON.parse(dados.textContent);
    if (!fotos.length) return;

    var atual = 0;
    var contador = document.getElementById('gallery-counter');
    var miniaturas = document.querySelectorAll('.gallery-thumb');
    var lbImg = document.getElementById('lightbox-img');
    var lbContador = document.getElementById('lightbox-counter');
    var botaoAbrir = document.querySelector('.gallery-open');

    function mostrar(i) {
        atual = (i + fotos.length) % fotos.length;
        principal.src = fotos[atual].src;
        principal.alt = fotos[atual].alt || '';
        contador.textContent = (atual + 1) + ' / ' + fotos.length;
        miniaturas.forEach(function (m, n) {
            m.classList.toggle('active', n === atual);
        });
        if (!lb.hidden) {
            lbImg.src = fotos[atual].src;
            lbImg.alt = fotos[atual].alt || '';
            lbContador.textContent = (atual + 1) + ' / ' + fotos.length;
        }
    }

    function abrir() {
        lb.hidden = false;
        document.body.style.overflow = 'hidden';
        mostrar(atual);
        lb.querySelector('.lightbox-close').focus();
    }

    function fechar() {
        lb.hidden = true;
        document.body.style.overflow = '';
        botaoAbrir.focus();
    }

    function ao(seletor, evento, funcao) {
        document.querySelectorAll(seletor).forEach(function (el) {
            el.addEventListener(evento, funcao);
        });
    }

    ao('.gallery-prev, .lightbox-prev', 'click', function () { mostrar(atual - 1); });
    ao('.gallery-next, .lightbox-next', 'click', function () { mostrar(atual + 1); });
    ao('.gallery-open', 'click', abrir);
    ao('.lightbox-close', 'click', fechar);
    lb.addEventListener('click', function (e) { if (e.target === lb) fechar(); });

    miniaturas.forEach(function (m) {
        m.addEventListener('click', function () {
            mostrar(parseInt(m.getAttribute('data-index'), 10));
        });
    });

    // Teclado: setas trocam a foto; Esc fecha a ampliação.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !lb.hidden) fechar();
        if (e.key === 'ArrowLeft') mostrar(atual - 1);
        if (e.key === 'ArrowRight') mostrar(atual + 1);
    });
})();
