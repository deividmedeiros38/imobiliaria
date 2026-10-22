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

// Fotos
$stFotos = db()->prepare('SELECT arquivo, legenda FROM imovel_fotos WHERE imovel_id = ? ORDER BY principal DESC, ordem ASC, id ASC');
$stFotos->execute([(int)$imovel['id']]);
$fotos = $stFotos->fetchAll();

// Características
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
?>

<section class="detail-hero">
    <div class="detail-inner">
        <div class="detail-grid">
            <div class="detail-gallery">
                <?php if (!empty($fotos)): ?>
                    <img src="<?= e(foto_url($fotos[0]['arquivo'])) ?>" alt="<?= e($fotos[0]['legenda'] ?: $imovel['titulo']) ?>">
                <?php else: ?>
                    <div style="text-align:center;padding:2rem">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true">
                            <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                            <path d="M9 21V12h6v9"/>
                        </svg>
                        <p style="margin:.75rem 0 0;font-size:.9rem">Fotos em breve</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <p class="text-muted" style="margin:0 0 .35rem;font-size:.85rem">
                    <?= e(finalidade_label($imovel['finalidade'])) ?> · <?= e($imovel['categoria']) ?> · Cód. <?= e($imovel['codigo']) ?>
                </p>
                <h1><?= e($imovel['titulo']) ?></h1>
                <div class="detail-price">
                    <?= e(money($imovel['preco'])) ?>
                    <?= $imovel['finalidade'] === 'aluguel' ? '<small style="font-size:.9rem;font-weight:500">/mês</small>' : '' ?>
                </div>
                <div class="detail-meta">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= e($imovel['bairro']) ?>, <?= e($imovel['cidade']) ?>
                    </span>
                </div>

                <div class="detail-specs">
                    <?php if ((int)$imovel['quartos'] > 0): ?>
                        <div class="spec"><strong><?= (int)$imovel['quartos'] ?></strong><span>Quartos</span></div>
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
                        <div class="spec"><strong><?= e(number_format((float)$imovel['area_construida'], 0, ',', '.')) ?></strong><span>m² construídos</span></div>
                    <?php endif; ?>
                    <?php if ($imovel['area_terreno']): ?>
                        <div class="spec"><strong><?= e(number_format((float)$imovel['area_terreno'], 0, ',', '.')) ?></strong><span>m² terreno</span></div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($caracteristicas)): ?>
                    <p style="margin:.5rem 0 0;font-size:.9rem">
                        <?php foreach ($caracteristicas as $i => $car): ?>
                            <span style="display:inline-block;background:var(--bg);padding:.25rem .55rem;border-radius:4px;margin:.2rem .25rem .2rem 0"><?= e($car) ?></span>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>

                <div class="detail-cta">
                    <h3>Interessado neste imóvel?</h3>
                    <p>Fale agora pelo WhatsApp e tire suas dúvidas com agilidade.</p>
                    <?php if ($whatsapp): ?>
                        <a class="btn btn-accent" href="https://wa.me/55<?= e($whatsapp) ?>?text=<?= $msgWa ?>" target="_blank" rel="noopener">
                            Conversar no WhatsApp
                        </a>
                    <?php else: ?>
                        <a class="btn btn-accent" href="<?= e(url('contato.php?imovel=' . (int)$imovel['id'])) ?>">Enviar mensagem</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="detail-desc">
            <h2 style="font-size:1.15rem;color:var(--navy);margin:1.5rem 0 .75rem">Descrição</h2>
            <div><?= nl2br(e($imovel['descricao'])) ?></div>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
