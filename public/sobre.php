<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$nomeSite = site_setting('nome_site', 'Imobiliária');
$corretor = site_setting('corretor_nome', 'Corretor de Imóveis');
$sobre    = site_setting('sobre', '');
$creci    = site_setting('creci', '');
$cidade   = site_setting('cidade_atuacao', 'Cariri - CE');

$pageTitle = 'Sobre';
$pageDescription = "{$corretor} — corretor de imóveis em {$cidade}.";
$navActive = 'sobre';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<div class="page-banner">
    <div class="container">
        <h1>Sobre nós</h1>
    </div>
</div>

<section class="section">
    <div class="container">
    <div class="about-block">
        <h2><?= e($corretor) ?></h2>
        <?php if ($creci): ?>
            <p class="text-muted">CRECI <?= e($creci) ?></p>
        <?php endif; ?>

        <?php if ($sobre !== ''): ?>
            <p><?= nl2br(e($sobre)) ?></p>
        <?php else: ?>
            <p>Corretor de imóveis na região do Cariri. Atendimento direto, com foco em transparência nas negociações.</p>
        <?php endif; ?>

        <ul class="about-list">
            <li>Atendimento personalizado</li>
            <li>Orientação em documentação</li>
            <li>Mercado de <?= e($cidade) ?></li>
        </ul>

        <div class="about-actions">
            <a href="<?= e(url('contato.php')) ?>" class="button button-primary">Entrar em contato</a>
            <a href="<?= e(url('imoveis.php')) ?>" class="button button-outline">Ver imóveis</a>
        </div>
    </div>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
