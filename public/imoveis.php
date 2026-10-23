<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$codigo     = trim((string) ($_GET['codigo'] ?? ''));
$finalidade = in_array($_GET['finalidade'] ?? '', ['venda', 'aluguel'], true) ? $_GET['finalidade'] : '';
$cidade     = trim((string) ($_GET['cidade'] ?? ''));
$categoria  = (int) ($_GET['categoria'] ?? 0);
$q          = trim((string) ($_GET['q'] ?? ''));
$quartos    = (int) ($_GET['quartos'] ?? 0);

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
if ($quartos > 0) {
    $sql .= ' AND i.quartos >= ?';
    $params[] = $quartos;
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

$tituloPagina = 'Imóveis disponíveis';
if ($finalidade === 'venda') $tituloPagina = 'Imóveis à venda';
if ($finalidade === 'aluguel') $tituloPagina = 'Imóveis para alugar';

$pageTitle = $tituloPagina;
$pageDescription = $tituloPagina . ' na região do Cariri.';
$navActive = 'imoveis';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<div class="page-banner">
    <div class="page-banner-inner">
        <h1><?= e($tituloPagina) ?></h1>
        <p><?= count($imoveis) ?> imóvel(is) encontrado(s).</p>
    </div>
</div>

<section class="section">
    <div class="list-layout">
        <div>
            <?php if (empty($imoveis)): ?>
                <div class="empty-state">
                    <h3>Nenhum imóvel encontrado</h3>
                    <p>Tente alterar os filtros ou <a href="<?= e(url('imoveis.php')) ?>">limpar a busca</a>.</p>
                </div>
            <?php else: ?>
                <div class="lista-imoveis">
                    <?php foreach ($imoveis as $imovel): ?>
                        <?php $horizontal = true; require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="sidebar-filters">
            <h3>Filtrar</h3>
            <form method="get" action="<?= e(url('imoveis.php')) ?>">
                <label for="f-finalidade">Finalidade</label>
                <select id="f-finalidade" name="finalidade">
                    <option value="">Todas</option>
                    <option value="venda" <?= $finalidade === 'venda' ? 'selected' : '' ?>>Venda</option>
                    <option value="aluguel" <?= $finalidade === 'aluguel' ? 'selected' : '' ?>>Aluguel</option>
                </select>

                <label for="f-cat">Tipo de imóvel</label>
                <select id="f-cat" name="categoria">
                    <option value="">Todos</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= $categoria === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['nome']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="f-cidade">Cidade</label>
                <select id="f-cidade" name="cidade">
                    <option value="">Todas</option>
                    <?php foreach ($cidades as $cid): ?>
                        <option value="<?= e($cid) ?>" <?= $cidade === $cid ? 'selected' : '' ?>><?= e($cid) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="f-quartos">Dormitórios (mín.)</label>
                <select id="f-quartos" name="quartos">
                    <option value="">Todos</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= $quartos === $i ? 'selected' : '' ?>><?= $i ?>+</option>
                    <?php endfor; ?>
                </select>

                <label for="f-codigo">Código / Ref.</label>
                <input type="text" id="f-codigo" name="codigo" value="<?= e($codigo) ?>" placeholder="Ex: DEMO-001">

                <label for="f-q">Bairro ou palavra</label>
                <input type="search" id="f-q" name="q" value="<?= e($q) ?>" placeholder="Buscar…">

                <button type="submit" class="btn">Buscar</button>
            </form>
        </aside>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
