<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';
Auth::require();

$stats = db()->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(ativo = 1), 0) AS ativos,
            COALESCE(SUM(destaque = 1), 0) AS destaques
       FROM imoveis"
)->fetch();
$novos = (int) db()->query('SELECT COUNT(*) FROM contatos WHERE lido = 0')->fetchColumn();

$pageTitle = 'Resumo';
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<h1>Resumo</h1>
<div class="cards">
    <div class="card"><span class="card-num"><?= (int) $stats['total'] ?></span> imóveis cadastrados</div>
    <div class="card"><span class="card-num"><?= (int) $stats['ativos'] ?></span> anúncios ativos</div>
    <div class="card"><span class="card-num"><?= (int) $stats['destaques'] ?></span> em destaque</div>
    <div class="card"><span class="card-num"><?= $novos ?></span> contatos não lidos</div>
</div>
<p class="note">Etapa 1 concluída: base, segurança e login. O cadastro de imóveis chega na próxima etapa.</p>
<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php';
