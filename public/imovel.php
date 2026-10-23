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
?>

<section class="detail-hero">
    <div class="detail-inner">
        <div class="detail-grid">
            <div class="detail-gallery">
                <?php if (!empty($fotos)): ?>
                    <img src="<?= e(foto_url($fotos[0]['arquivo'])) ?>" alt="<?= e($fotos[0]['legenda'] ?: $imovel['titulo']) ?>">
                <?php else: ?>
                    <span>Sem foto cadastrada</span>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <p class="text-muted" style="margin:0 0 4px;font-size:0.85rem">
                    <?= e(finalidade_label($imovel['finalidade'])) ?>
                    · <?= e($imovel['categoria']) ?>
                    · Cód. <?= e($imovel['codigo']) ?>
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
                    <div class="tags">
                        <?php foreach ($caracteristicas as $car): ?>
                            <span><?= e($car) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="detail-cta">
                    <h3>Interessado?</h3>
                    <p>Chame no WhatsApp para mais informações ou visita.</p>
                    <?php if ($whatsapp): ?>
                        <a class="btn btn-accent" href="https://wa.me/55<?= e($whatsapp) ?>?text=<?= $msgWa ?>" target="_blank" rel="noopener">
                            Chamar no WhatsApp
                        </a>
                    <?php else: ?>
                        <a class="btn btn-accent" href="<?= e(url('contato.php?imovel=' . (int)$imovel['id'])) ?>">Enviar mensagem</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="detail-desc">
            <h2>Descrição</h2>
            <div><?= nl2br(e($imovel['descricao'])) ?></div>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
