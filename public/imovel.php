<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$codigo = trim((string) ($_GET['codigo'] ?? ''));

if ($slug === '' && $codigo === '') {
    redirect('imoveis.php');
}

if ($slug !== '') {
    $st = db()->prepare(
        "SELECT i.*, c.nome AS categoria
           FROM imoveis i
           JOIN categorias c ON c.id = i.categoria_id
          WHERE i.slug = ? AND i.ativo = 1
          LIMIT 1"
    );
    $st->execute([$slug]);
} else {
    $st = db()->prepare(
        "SELECT i.*, c.nome AS categoria
           FROM imoveis i
           JOIN categorias c ON c.id = i.categoria_id
          WHERE i.codigo = ? AND i.ativo = 1
          LIMIT 1"
    );
    $st->execute([$codigo]);
}

$imovel = $st->fetch();
if (!$imovel) {
    http_response_code(404);
    $pageTitle = 'Imóvel não encontrado';
    $navActive = 'imoveis';
    require BASE_PATH . '/app/Views/public/layout_top.php';
    echo '<section class="section"><div class="empty-state"><h3>Imóvel não encontrado</h3><p><a href="' . e(url('imoveis.php')) . '">Ver todos os imóveis</a></p></div></section>';
    require BASE_PATH . '/app/Views/public/layout_bottom.php';
    exit;
}

$stFotos = db()->prepare('SELECT arquivo, legenda FROM imovel_fotos WHERE imovel_id = ? ORDER BY principal DESC, ordem ASC, id ASC');
$stFotos->execute([(int)$imovel['id']]);
$fotos = $stFotos->fetchAll();

$stCar = db()->prepare(
    'SELECT c.nome FROM caracteristicas c
     JOIN imovel_caracteristicas ic ON ic.caracteristica_id = c.id
     WHERE ic.imovel_id = ? ORDER BY c.nome'
);
$stCar->execute([(int)$imovel['id']]);
$caracteristicas = $stCar->fetchAll(PDO::FETCH_COLUMN);

$whatsapp = preg_replace('/\D+/', '', site_setting('whatsapp', site_setting('telefone', '')));
$msgWa = rawurlencode("Olá! Tenho interesse no imóvel {$imovel['codigo']} — {$imovel['titulo']}");

$pageTitle = $imovel['titulo'];
$pageDescription = mb_substr(strip_tags($imovel['descricao']), 0, 155);
$navActive = 'imoveis';
require BASE_PATH . '/app/Views/public/layout_top.php';

$fotosJson = [];
foreach ($fotos as $f) {
    $fotosJson[] = [
        'src' => foto_url($f['arquivo']),
        'alt' => $f['legenda'] ?: $imovel['titulo'],
    ];
}
?>

<section class="detail-hero">
    <div class="detail-inner">
        <div class="detail-grid">
            <div class="gallery" id="galeria" data-count="<?= count($fotos) ?>">
                <?php if (!empty($fotos)): ?>
                    <div class="gallery-main">
                        <button type="button" class="gallery-nav gallery-prev" aria-label="Foto anterior" <?= count($fotos) < 2 ? 'hidden' : '' ?>>&lsaquo;</button>
                        <button type="button" class="gallery-open" aria-label="Ampliar foto">
                            <img id="gallery-main-img"
                                 src="<?= e(foto_url($fotos[0]['arquivo'])) ?>"
                                 alt="<?= e($fotos[0]['legenda'] ?: $imovel['titulo']) ?>">
                        </button>
                        <button type="button" class="gallery-nav gallery-next" aria-label="Próxima foto" <?= count($fotos) < 2 ? 'hidden' : '' ?>>&rsaquo;</button>
                        <span class="gallery-counter" id="gallery-counter">1 / <?= count($fotos) ?></span>
                    </div>
                    <?php if (count($fotos) > 1): ?>
                    <div class="gallery-thumbs" role="list">
                        <?php foreach ($fotos as $i => $f): ?>
                            <button type="button"
                                    class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                                    data-index="<?= $i ?>"
                                    role="listitem"
                                    aria-label="Foto <?= $i + 1 ?>">
                                <img src="<?= e(foto_url($f['arquivo'])) ?>" alt="<?= e($f['legenda'] ?: '') ?>" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="gallery-empty">Sem foto cadastrada</div>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <p class="text-muted" style="margin:0 0 4px;font-size:0.85rem">
                    <?= e(finalidade_label($imovel['finalidade'])) ?>
                    · <?= e($imovel['categoria']) ?>
                    · Ref. <?= e($imovel['codigo']) ?>
                </p>
                <h1><?= e($imovel['titulo']) ?></h1>
                <div class="detail-price">
                    <?= e(money($imovel['preco'])) ?>
                    <?php if ($imovel['finalidade'] === 'aluguel'): ?>
                        <small style="font-size:0.85rem;font-weight:normal">/mês</small>
                    <?php endif; ?>
                </div>
                <div class="detail-meta">
                    <?= e($imovel['bairro']) ?>, <?= e($imovel['cidade']) ?>
                </div>

                <div class="detail-specs">
                    <?php if ((int)$imovel['quartos'] > 0): ?>
                        <div class="spec"><strong><?= (int)$imovel['quartos'] ?></strong><span>Dormitórios</span></div>
                    <?php endif; ?>
                    <?php if ((int)$imovel['suites'] > 0): ?>
                        <div class="spec"><strong><?= (int)$imovel['suites'] ?></strong><span>Suítes</span></div>
                    <?php endif; ?>
                    <?php if ((int)$imovel['banheiros'] > 0): ?>
                        <div class="spec"><strong><?= (int)$imovel['banheiros'] ?></strong><span>Banheiros</span></div>
                    <?php endif; ?>
                    <?php if ((int)$imovel['vagas'] > 0): ?>
                        <div class="spec"><strong><?= (int)$imovel['vagas'] ?></strong><span>Vagas</span></div>
                    <?php endif; ?>
                    <?php if ($imovel['area_construida']): ?>
                        <div class="spec"><strong><?= e(number_format((float)$imovel['area_construida'], 0, ',', '.')) ?></strong><span>m²</span></div>
                    <?php endif; ?>
                    <?php if ($imovel['area_terreno']): ?>
                        <div class="spec"><strong><?= e(number_format((float)$imovel['area_terreno'], 0, ',', '.')) ?></strong><span>m² terreno</span></div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($caracteristicas)): ?>
                    <div class="tags">
                        <?php foreach ($caracteristicas as $car): ?>
                            <span><?= e($car) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="detail-cta">
                    <h3>Interessado neste imóvel?</h3>
                    <p>Chame no WhatsApp ou envie uma mensagem.</p>
                    <?php if ($whatsapp): ?>
                        <a class="btn" href="https://wa.me/55<?= e($whatsapp) ?>?text=<?= $msgWa ?>" target="_blank" rel="noopener">
                            Chamar no WhatsApp
                        </a>
                    <?php endif; ?>
                    <p style="margin-top:10px">
                        <a href="<?= e(url('contato.php?imovel=' . (int)$imovel['id'])) ?>">Enviar mensagem pelo site</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="detail-desc">
            <h2>Descrição</h2>
            <div><?= nl2br(e($imovel['descricao'])) ?></div>
        </div>
    </div>
</section>

<?php if (!empty($fotos)): ?>
<div class="lightbox" id="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="Fechar">&times;</button>
    <button type="button" class="lightbox-nav lightbox-prev" aria-label="Anterior">&lsaquo;</button>
    <img id="lightbox-img" src="" alt="">
    <button type="button" class="lightbox-nav lightbox-next" aria-label="Próxima">&rsaquo;</button>
    <span class="lightbox-counter" id="lightbox-counter"></span>
</div>
<script>
(function () {
    var fotos = <?= json_encode($fotosJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    if (!fotos.length) return;

    var idx = 0;
    var mainImg = document.getElementById('gallery-main-img');
    var counter = document.getElementById('gallery-counter');
    var thumbs = document.querySelectorAll('.gallery-thumb');
    var lb = document.getElementById('lightbox');
    var lbImg = document.getElementById('lightbox-img');
    var lbCounter = document.getElementById('lightbox-counter');

    function show(i) {
        idx = (i + fotos.length) % fotos.length;
        mainImg.src = fotos[idx].src;
        mainImg.alt = fotos[idx].alt || '';
        counter.textContent = (idx + 1) + ' / ' + fotos.length;
        thumbs.forEach(function (t, n) {
            t.classList.toggle('active', n === idx);
        });
        if (!lb.hidden) {
            lbImg.src = fotos[idx].src;
            lbImg.alt = fotos[idx].alt || '';
            lbCounter.textContent = (idx + 1) + ' / ' + fotos.length;
        }
    }

    function openLb() {
        lb.hidden = false;
        document.body.style.overflow = 'hidden';
        lbImg.src = fotos[idx].src;
        lbImg.alt = fotos[idx].alt || '';
        lbCounter.textContent = (idx + 1) + ' / ' + fotos.length;
    }
    function closeLb() {
        lb.hidden = true;
        document.body.style.overflow = '';
    }

    document.querySelector('.gallery-prev')?.addEventListener('click', function () { show(idx - 1); });
    document.querySelector('.gallery-next')?.addEventListener('click', function () { show(idx + 1); });
    document.querySelector('.gallery-open')?.addEventListener('click', openLb);
    document.querySelector('.lightbox-prev')?.addEventListener('click', function () { show(idx - 1); });
    document.querySelector('.lightbox-next')?.addEventListener('click', function () { show(idx + 1); });
    document.querySelector('.lightbox-close')?.addEventListener('click', closeLb);
    lb.addEventListener('click', function (e) { if (e.target === lb) closeLb(); });

    thumbs.forEach(function (t) {
        t.addEventListener('click', function () {
            show(parseInt(t.getAttribute('data-index'), 10));
        });
    });

    document.addEventListener('keydown', function (e) {
        if (lb.hidden && !document.getElementById('galeria')) return;
        if (e.key === 'ArrowLeft') show(idx - 1);
        if (e.key === 'ArrowRight') show(idx + 1);
        if (e.key === 'Escape') closeLb();
    });
})();
</script>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
