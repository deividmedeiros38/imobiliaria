<?php
/** @var string $pageTitle */
/** @var string|null $pageDescription */
$nomeSite   = site_setting('nome_site', 'Imobiliária');
$telefone   = site_setting('telefone', '');
$whatsapp   = preg_replace('/\D+/', '', site_setting('whatsapp', $telefone));
$creci      = site_setting('creci', '');
$corretor   = site_setting('corretor_nome', '');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> · <?= e($nomeSite) ?></title>
    <?php if (!empty($pageDescription)): ?>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body>
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>

<?php if ($whatsapp !== '' || $telefone !== ''): ?>
<div class="topbar">
    <div class="topbar-inner">
        <span>
            <?php if ($corretor): ?><?= e($corretor) ?><?php endif; ?>
            <?php if ($creci): ?> · CRECI <?= e($creci) ?><?php endif; ?>
        </span>
        <?php if ($whatsapp !== ''): ?>
            <a class="topbar-wa" href="https://wa.me/55<?= e($whatsapp) ?>" target="_blank" rel="noopener">
                WhatsApp <?= e($telefone ?: $whatsapp) ?>
            </a>
        <?php elseif ($telefone): ?>
            <a href="tel:<?= e(preg_replace('/\D+/', '', $telefone)) ?>"><?= e($telefone) ?></a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<header class="site-header">
    <div class="header-inner">
        <a href="<?= e(url('')) ?>" class="brand">
            <?= e($nomeSite) ?>
            <?php if ($corretor && $corretor !== $nomeSite): ?>
                <small><?= e($corretor) ?></small>
            <?php endif; ?>
        </a>
        <nav class="site-nav" aria-label="Menu principal">
            <a href="<?= e(url('')) ?>" class="<?= ($navActive ?? '') === 'home' ? 'active' : '' ?>">Início</a>
            <a href="<?= e(url('imoveis.php')) ?>" class="<?= ($navActive ?? '') === 'imoveis' ? 'active' : '' ?>">Imóveis</a>
            <a href="<?= e(url('imoveis.php?finalidade=venda')) ?>">Venda</a>
            <a href="<?= e(url('imoveis.php?finalidade=aluguel')) ?>">Aluguel</a>
            <a href="<?= e(url('sobre.php')) ?>" class="<?= ($navActive ?? '') === 'sobre' ? 'active' : '' ?>">Sobre</a>
            <a href="<?= e(url('contato.php')) ?>" class="<?= ($navActive ?? '') === 'contato' ? 'active' : '' ?>">Contato</a>
        </nav>
    </div>
</header>

<main id="conteudo">
<?php foreach (flash_pull() as $f): ?>
    <div class="container" style="padding-top:12px">
        <p class="alert alert-<?= e($f['type']) ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['message']) ?></p>
    </div>
<?php endforeach; ?>
