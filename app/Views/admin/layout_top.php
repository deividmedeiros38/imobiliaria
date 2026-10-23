<?php /** @var string $pageTitle */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle) ?> · Painel</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
</head>
<body>
<a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
<?php if (Auth::check()): ?>
<header class="admin-header">
    <strong class="admin-brand"><?= e(site_setting('nome_site', 'Painel')) ?></strong>
    <nav aria-label="Menu do painel">
        <a href="<?= e(url('admin/dashboard.php')) ?>">Resumo</a>
        <a href="<?= e(url('admin/conta.php')) ?>">Conta</a>
        <a href="<?= e(url('')) ?>" target="_blank" rel="noopener">Ver site</a>
    </nav>
    <form method="post" action="<?= e(url('admin/logout.php')) ?>" class="inline-form">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-ghost">Sair (<?= e(Auth::user()['nome']) ?>)</button>
    </form>
</header>
<?php endif; ?>
<main id="conteudo" class="admin-main">
<?php foreach (flash_pull() as $f): ?>
    <p class="alert alert-<?= e($f['type']) ?>" role="<?= $f['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($f['message']) ?></p>
<?php endforeach; ?>
