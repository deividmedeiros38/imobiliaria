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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
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
            <?php if ($waCabecalho !== ''): ?>
                <a class="nav-wa-mobile" href="<?= e($waCabecalho) ?>" target="_blank" rel="noopener">WhatsApp</a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">
            <?php if ($waCabecalho !== ''): ?>
                <a class="header-whatsapp" href="<?= e($waCabecalho) ?>" target="_blank" rel="noopener">WhatsApp</a>
                <a class="header-wa-icon" href="<?= e($waCabecalho) ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false">
                        <path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                </a>
            <?php endif; ?>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
                <span class="sr-only">Abrir menu</span>
            </button>
        </div>
    </div>
</header>

<main id="conteudo">
<?php foreach (flash_pull() as $f): ?>
    <div class="container flash-wrap">
        <p class="alert alert-<?= e($f['type']) ?>" role="status"><?= e($f['message']) ?></p>
    </div>
<?php endforeach; ?>
