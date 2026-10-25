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
    echo '<section class="section"><div class="container"><div class="empty-state"><h3>Imóvel não encontrado</h3><p><a href="' . e(url('imoveis.php')) . '">Ver todos os imóveis</a></p></div></div></section>';
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

$corretor = site_setting('corretor_nome', '');
$creci    = site_setting('creci', '');
$urlImovel = url('imovel.php?slug=' . urlencode($imovel['slug']));
$whatsappUrl = whatsapp_url("Olá! Tenho interesse no imóvel {$imovel['codigo']} — {$imovel['titulo']}.\n{$urlImovel}");
$aluguel = $imovel['finalidade'] === 'aluguel';

// Imóveis semelhantes: mesma finalidade, priorizando a mesma cidade.
$stSimilares = db()->prepare(
    'SELECT i.*, c.nome AS categoria
       FROM imoveis i
       JOIN categorias c ON c.id = i.categoria_id
      WHERE i.ativo = 1 AND i.id <> ? AND i.finalidade = ?
      ORDER BY (i.cidade = ?) DESC, i.atualizado_em DESC
      LIMIT 3'
);
$stSimilares->execute([(int) $imovel['id'], $imovel['finalidade'], $imovel['cidade']]);
$similares = $stSimilares->fetchAll();

$pageTitle       = $imovel['titulo'];
$pageDescription = mb_substr(strip_tags((string) $imovel['descricao']), 0, 155);
$navActive       = $aluguel ? 'aluguel' : 'venda';
require BASE_PATH . '/app/Views/public/layout_top.php';

// Dados da galeria para o JavaScript (assets/js/galeria.js).
$fotosJson = [];
foreach ($fotos as $f) {
    $fotosJson[] = [
        'src' => foto_url($f['arquivo']),
        'alt' => $f['legenda'] ?: $imovel['titulo'],
    ];
}

// Medidas do imóvel: [valor, rótulo] — só aparecem as que existem.
$medidas = [];
if ((int) $imovel['quartos'] > 0)   { $medidas[] = [(int) $imovel['quartos'], $imovel['quartos'] == 1 ? 'Quarto' : 'Quartos']; }
if ((int) $imovel['suites'] > 0)    { $medidas[] = [(int) $imovel['suites'], $imovel['suites'] == 1 ? 'Suíte' : 'Suítes']; }
if ((int) $imovel['banheiros'] > 0) { $medidas[] = [(int) $imovel['banheiros'], $imovel['banheiros'] == 1 ? 'Banheiro' : 'Banheiros']; }
if ((int) $imovel['vagas'] > 0)     { $medidas[] = [(int) $imovel['vagas'], $imovel['vagas'] == 1 ? 'Vaga' : 'Vagas']; }
if ($imovel['area_construida'])     { $medidas[] = [number_format((float) $imovel['area_construida'], 0, ',', '.'), 'm² construídos']; }
if ($imovel['area_terreno'])        { $medidas[] = [number_format((float) $imovel['area_terreno'], 0, ',', '.'), 'm² de terreno']; }
?>

<section class="detail">
    <div class="container">
        <nav class="breadcrumb" aria-label="Você está em">
            <a href="<?= e(url('')) ?>">Início</a>
            <a href="<?= e(url('imoveis.php')) ?>">Imóveis</a>
            <span aria-current="page"><?= e($imovel['titulo']) ?></span>
        </nav>

        <div class="detail-grid">
            <!-- Galeria de fotos -->
            <div class="gallery" id="galeria">
                <?php if (!empty($fotos)): ?>
                    <div class="gallery-main">
                        <button type="button" class="gallery-open" aria-label="Ampliar foto">
                            <img id="gallery-main-img"
                                 src="<?= e(foto_url($fotos[0]['arquivo'])) ?>"
                                 alt="<?= e($fotos[0]['legenda'] ?: $imovel['titulo']) ?>">
                        </button>
                        <?php if (count($fotos) > 1): ?>
                            <button type="button" class="gallery-nav gallery-prev" aria-label="Foto anterior">&lsaquo;</button>
                            <button type="button" class="gallery-nav gallery-next" aria-label="Próxima foto">&rsaquo;</button>
                        <?php endif; ?>
                        <span class="gallery-counter" id="gallery-counter">1 / <?= count($fotos) ?></span>
                        <b class="property-tag<?= $aluguel ? ' tag-rent' : '' ?>"><?= e(finalidade_label($imovel['finalidade'])) ?></b>
                    </div>

                    <?php if (count($fotos) > 1): ?>
                        <div class="gallery-thumbs">
                            <?php foreach ($fotos as $i => $f): ?>
                                <button type="button" class="gallery-thumb<?= $i === 0 ? ' active' : '' ?>" data-index="<?= $i ?>" aria-label="Ver foto <?= $i + 1 ?>">
                                    <img src="<?= e(foto_url($f['arquivo'])) ?>" alt="" loading="lazy">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="gallery-empty">Fotos em breve</div>
                <?php endif; ?>
            </div>

            <!-- Resumo, medidas e contato -->
            <aside class="detail-side">
                <div class="detail-info">
                    <p class="detail-kicker"><?= e($imovel['categoria']) ?> &middot; Ref. <?= e($imovel['codigo']) ?></p>
                    <h1><?= e($imovel['titulo']) ?></h1>
                    <p class="detail-meta"><?= e($imovel['bairro']) ?>, <?= e($imovel['cidade']) ?></p>
                    <p class="detail-price"><?= e(money($imovel['preco'])) ?><?php if ($aluguel): ?><small>/mês</small><?php endif; ?></p>

                    <?php if ($medidas): ?>
                        <dl class="detail-specs">
                            <?php foreach ($medidas as [$valor, $rotulo]): ?>
                                <div class="spec"><dt><?= e((string) $valor) ?></dt><dd><?= e($rotulo) ?></dd></div>
                            <?php endforeach; ?>
                        </dl>
                    <?php endif; ?>

                    <div class="detail-cta">
                        <h2>Interessado neste imóvel?</h2>
                        <p>Fale agora com <?= e($corretor !== '' ? $corretor : 'um corretor') ?> e tire suas dúvidas.</p>
                        <?php if ($whatsappUrl !== ''): ?>
                            <a class="button button-whatsapp" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener">Chamar no WhatsApp</a>
                        <?php endif; ?>
                        <a class="detail-cta-link" href="<?= e(url('contato.php?imovel=' . (int) $imovel['id'])) ?>">Enviar mensagem pelo site</a>
                    </div>
                </div>

                <?php if ($corretor !== ''): ?>
                    <div class="agent-card">
                        <span class="agent-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($corretor, 0, 1))) ?></span>
                        <div>
                            <p class="agent-name"><?= e($corretor) ?></p>
                            <p class="agent-role">Corretor de imóveis<?= $creci !== '' ? ' &middot; CRECI ' . e($creci) : '' ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>

            <!-- Descrição e características -->
            <div class="detail-content">
                <div class="detail-block">
                    <h2>Descrição</h2>
                    <?php if (trim((string) $imovel['descricao']) !== ''): ?>
                        <p><?= nl2br(e($imovel['descricao'])) ?></p>
                    <?php else: ?>
                        <p class="text-muted">Entre em contato para receber mais informações sobre este imóvel.</p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($caracteristicas)): ?>
                    <div class="detail-block">
                        <h2>Características e comodidades</h2>
                        <ul class="feature-list">
                            <?php foreach ($caracteristicas as $car): ?>
                                <li><?= e($car) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($similares): ?>
            <div class="related">
                <div class="section-heading">
                    <div>
                        <p class="section-kicker">Continue explorando</p>
                        <h2>Imóveis semelhantes</h2>
                    </div>
                </div>
                <div class="cards-grid">
                    <?php foreach ($similares as $imovel): ?>
                        <?php require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($fotos)): ?>
    <div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Galeria de fotos" hidden>
        <button type="button" class="lightbox-close" aria-label="Fechar">&times;</button>
        <?php if (count($fotos) > 1): ?>
            <button type="button" class="lightbox-nav lightbox-prev" aria-label="Anterior">&lsaquo;</button>
            <button type="button" class="lightbox-nav lightbox-next" aria-label="Próxima">&rsaquo;</button>
        <?php endif; ?>
        <img id="lightbox-img" src="" alt="">
        <span class="lightbox-counter" id="lightbox-counter"></span>
    </div>
    <script type="application/json" id="galeria-dados"><?= json_encode($fotosJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
    <script src="<?= e(url('assets/js/galeria.js')) ?>" defer></script>
<?php endif; ?>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
