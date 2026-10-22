<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$codigo     = trim((string) ($_GET['codigo'] ?? ''));
$finalidade = in_array($_GET['finalidade'] ?? '', ['venda', 'aluguel'], true) ? $_GET['finalidade'] : '';
$cidade     = trim((string) ($_GET['cidade'] ?? ''));
$categoria  = (int) ($_GET['categoria'] ?? 0);
$q          = trim((string) ($_GET['q'] ?? ''));

$sql = "SELECT i.*, c.nome AS categoria
          FROM imoveis i
          JOIN categorias c ON c.id = i.categoria_id
         WHERE i.ativo = 1";
$params = [];

if ($codigo !== '') {
    $sql .= ' AND i.codigo LIKE ?';
    $params[] = '%' . $codigo . '%';
}
if ($finalidade !== '') {
    $sql .= ' AND i.finalidade = ?';
    $params[] = $finalidade;
}
if ($cidade !== '') {
    $sql .= ' AND i.cidade LIKE ?';
    $params[] = '%' . $cidade . '%';
}
if ($categoria > 0) {
    $sql .= ' AND i.categoria_id = ?';
    $params[] = $categoria;
}
if ($q !== '') {
    $sql .= ' AND (i.titulo LIKE ? OR i.bairro LIKE ? OR i.descricao LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= ' ORDER BY i.destaque DESC, i.atualizado_em DESC';

$st = db()->prepare($sql);
$st->execute($params);
$imoveis = $st->fetchAll();

$categorias = db()->query('SELECT id, nome FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll();
$cidades = db()->query('SELECT DISTINCT cidade FROM imoveis WHERE ativo = 1 ORDER BY cidade')->fetchAll(PDO::FETCH_COLUMN);

$pageTitle = 'Imóveis';
$pageDescription = 'Confira os imóveis disponíveis para venda e aluguel.';
$navActive = 'imoveis';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<section class="section">
    <div class="section-head">
        <h2>Imóveis disponíveis</h2>
        <span class="text-muted"><?= count($imoveis) ?> anúncio(s)</span>
    </div>

    <form class="filters" method="get" action="<?= e(url('imoveis.php')) ?>">
        <div class="field">
            <label for="f-codigo">Código</label>
            <input type="text" id="f-codigo" name="codigo" value="<?= e($codigo) ?>" placeholder="Ex: DEMO-001">
        </div>
        <div class="field">
            <label for="f-finalidade">Finalidade</label>
            <select id="f-finalidade" name="finalidade">
                <option value="">Todas</option>
                <option value="venda" <?= $finalidade === 'venda' ? 'selected' : '' ?>>Venda</option>
                <option value="aluguel" <?= $finalidade === 'aluguel' ? 'selected' : '' ?>>Aluguel</option>
            </select>
        </div>
        <div class="field">
            <label for="f-cidade">Cidade</label>
            <select id="f-cidade" name="cidade">
                <option value="">Todas</option>
                <?php foreach ($cidades as $cid): ?>
                    <option value="<?= e($cid) ?>" <?= $cidade === $cid ? 'selected' : '' ?>><?= e($cid) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="f-cat">Tipo</label>
            <select id="f-cat" name="categoria">
                <option value="">Todos</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int)$cat['id'] ?>" <?= $categoria === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field" style="flex:1.3 1 160px">
            <label for="f-q">Busca livre</label>
            <input type="search" id="f-q" name="q" value="<?= e($q) ?>" placeholder="Bairro, palavra-chave…">
        </div>
        <button type="submit" class="btn btn-accent">Filtrar</button>
    </form>

    <?php if (empty($imoveis)): ?>
        <div class="empty-state">
            <h3>Nenhum imóvel encontrado</h3>
            <p>Tente alterar os filtros ou <a href="<?= e(url('imoveis.php')) ?>">limpar a busca</a>.</p>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($imoveis as $imovel): ?>
                <?php require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
