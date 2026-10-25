<?php
/**
 * Topo comum das páginas públicas: <head>, cabeçalho e menu.
 * Variáveis esperadas: $pageTitle (obrigatória), $pageDescription e $navActive (opcionais).
 */
$nomeSite    = site_setting('nome_site', 'Imobiliária');
$waCabecalho = whatsapp_url('Olá! Vim pelo site e gostaria de falar com um corretor.');
$navActive   = $navActive ?? '';

// Itens do menu: [chave para marcar como ativo, texto, endereço]
$menu = [
    ['home',    'Início',  ''],
    ['imoveis', 'Imóveis', 'imoveis.php'],
    ['venda',   'Venda',   'imoveis.php?finalidade=venda'],
    ['aluguel', 'Aluguel', 'imoveis.php?finalidade=aluguel'],
    ['sobre',   'Sobre',   'sobre.php'],
    ['contato', 'Contato', 'contato.php'],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | <?= e($nomeSite) ?></title>
    <?php if (!empty($pageDescription)): ?>
        <meta name="description" content="<?= e($pageDescription) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <script src="<?= e(url('assets/js/site.js')) ?>" defer></script>
</head>
<body>
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?= e(url('')) ?>" class="brand">
            <span class="brand-mark" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($nomeSite, 0, 2))) ?></span>
            <span class="brand-name"><?= e($nomeSite) ?><small>Negócios Imobiliários</small></span>
        </a>

        <nav id="site-nav" class="site-nav" aria-label="Menu principal">
            <?php foreach ($menu as [$chave, $texto, $destino]): ?>
                <a href="<?= e(url($destino)) ?>"<?= $navActive === $chave ? ' class="active" aria-current="page"' : '' ?>><?= e($texto) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($waCabecalho !== ''): ?>
            <a class="header-whatsapp" href="<?= e($waCabecalho) ?>" target="_blank" rel="noopener">WhatsApp</a>
        <?php endif; ?>

        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
            <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            <span class="sr-only">Abrir menu</span>
        </button>
    </div>
</header>

<main id="conteudo">
<?php foreach (flash_pull() as $f): ?>
    <div class="container flash-wrap">
        <p class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
    </div>
<?php endforeach; ?>
