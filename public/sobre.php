<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$nomeSite = site_setting('nome_site', 'Imobiliária');
$corretor = site_setting('corretor_nome', 'Corretor de Imóveis');
$sobre    = site_setting('sobre', '');
$creci    = site_setting('creci', '');
$cidade   = site_setting('cidade_atuacao', 'Cariri - CE');

$pageTitle = 'Quem sou';
$pageDescription = "{$corretor} — corretor de imóveis em {$cidade}.";
$navActive = 'sobre';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<section class="section">
    <div class="about-block" style="max-width:720px">
        <h2>Quem sou</h2>
        <p style="font-size:1.1rem;font-weight:bold;color:var(--azul);margin-bottom:4px"><?= e($corretor) ?></p>
        <?php if ($creci): ?>
            <p class="text-muted" style="margin-top:0">CRECI <?= e($creci) ?></p>
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

        <p style="margin-top:22px">
            <a href="<?= e(url('contato.php')) ?>" class="btn btn-accent">Entrar em contato</a>
            &nbsp;
            <a href="<?= e(url('imoveis.php')) ?>" class="btn">Ver imóveis</a>
        </p>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
