<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';
Auth::require();

// Excluir
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    csrf_require();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $fotos = db()->prepare('SELECT arquivo FROM imovel_fotos WHERE imovel_id = ?');
        $fotos->execute([$id]);
        foreach ($fotos->fetchAll(PDO::FETCH_COLUMN) as $arq) {
            delete_upload_file($arq);
        }
        $del = db()->prepare('DELETE FROM imoveis WHERE id = ?');
        $del->execute([$id]);
        flash('success', 'Imóvel excluído.');
    }
    redirect('admin/imoveis.php');
}

// Toggle ativo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'toggle') {
    csrf_require();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        db()->prepare('UPDATE imoveis SET ativo = IF(ativo=1,0,1) WHERE id = ?')->execute([$id]);
        flash('success', 'Status atualizado.');
    }
    redirect('admin/imoveis.php');
}

$q = trim((string) ($_GET['q'] ?? ''));
$sql = "SELECT i.*, c.nome AS categoria,
        (SELECT COUNT(*) FROM imovel_fotos f WHERE f.imovel_id = i.id) AS total_fotos
        FROM imoveis i
        JOIN categorias c ON c.id = i.categoria_id
        WHERE 1=1";
$params = [];
if ($q !== '') {
    $sql .= ' AND (i.titulo LIKE ? OR i.codigo LIKE ? OR i.bairro LIKE ? OR i.cidade LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like];
}
$sql .= ' ORDER BY i.atualizado_em DESC';
$st = db()->prepare($sql);
$st->execute($params);
$imoveis = $st->fetchAll();

$pageTitle = 'Imóveis';
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<div class="admin-toolbar">
    <h1 style="margin:0">Imóveis</h1>
    <a class="btn" href="<?= e(url('admin/imovel-form.php')) ?>">+ Novo imóvel</a>
</div>

<form method="get" class="admin-search">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar código, título, bairro…">
    <button type="submit" class="btn btn-small">Buscar</button>
    <?php if ($q !== ''): ?>
        <a href="<?= e(url('admin/imoveis.php')) ?>">Limpar</a>
    <?php endif; ?>
</form>

<p class="note"><?= count($imoveis) ?> registro(s)</p>

<div class="table-wrap">
<table class="admin-table">
    <thead>
        <tr>
            <th>Código</th>
            <th>Título</th>
            <th>Tipo</th>
            <th>Preço</th>
            <th>Fotos</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($imoveis)): ?>
        <tr><td colspan="7">Nenhum imóvel cadastrado.</td></tr>
    <?php endif; ?>
    <?php foreach ($imoveis as $i): ?>
        <tr>
            <td><strong><?= e($i['codigo']) ?></strong></td>
            <td>
                <?= e($i['titulo']) ?>
                <?php if ((int)$i['destaque']): ?> <span class="badge">destaque</span><?php endif; ?>
                <?php if ((int)$i['demo']): ?> <span class="badge">demo</span><?php endif; ?>
                <br><small class="note"><?= e($i['bairro']) ?>, <?= e($i['cidade']) ?></small>
            </td>
            <td><?= e(finalidade_label($i['finalidade'])) ?><br><small><?= e($i['categoria']) ?></small></td>
            <td><?= e(money($i['preco'])) ?></td>
            <td><?= (int)$i['total_fotos'] ?></td>
            <td><?= (int)$i['ativo'] ? 'Ativo' : 'Inativo' ?></td>
            <td class="actions">
                <a href="<?= e(url('admin/imovel-form.php?id=' . (int)$i['id'])) ?>">Editar</a>
                <a href="<?= e(url('admin/imovel-fotos.php?id=' . (int)$i['id'])) ?>">Fotos</a>
                <a href="<?= e(url('imovel.php?slug=' . urlencode($i['slug']))) ?>" target="_blank" rel="noopener">Ver</a>
                <form method="post" class="inline-form" onsubmit="return confirm('Alterar status?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                    <button type="submit" class="link-btn"><?= (int)$i['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                </form>
                <form method="post" class="inline-form" onsubmit="return confirm('Excluir este imóvel e todas as fotos?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" value="<?= (int)$i['id'] ?>">
                    <button type="submit" class="link-btn danger">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php'; ?>
