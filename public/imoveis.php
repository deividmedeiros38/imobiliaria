<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

const IMOVEIS_POR_PAGINA = 9;

// ---------- Filtros recebidos pela URL (GET) ----------
$codigo     = trim((string) ($_GET['codigo'] ?? ''));
$finalidade = in_array($_GET['finalidade'] ?? '', ['venda', 'aluguel'], true) ? (string) $_GET['finalidade'] : '';
$cidade     = trim((string) ($_GET['cidade'] ?? ''));
$categoria  = (int) ($_GET['categoria'] ?? 0);
$q          = trim((string) ($_GET['q'] ?? ''));
$quartos    = (int) ($_GET['quartos'] ?? 0);
$precoMinTxt = trim((string) ($_GET['preco_min'] ?? ''));
$precoMaxTxt = trim((string) ($_GET['preco_max'] ?? ''));
$precoMin   = parse_money($precoMinTxt);
$precoMax   = parse_money($precoMaxTxt);
$pagina     = max(1, (int) ($_GET['pagina'] ?? 1));

// ---------- Monta o WHERE (sempre com parâmetros, nunca concatenando texto do usuário) ----------
$where  = 'i.ativo = 1';
$params = [];

if ($codigo !== '') {
    $where .= ' AND i.codigo LIKE ?';
    $params[] = '%' . $codigo . '%';
}
if ($finalidade !== '') {
    $where .= ' AND i.finalidade = ?';
    $params[] = $finalidade;
}
if ($cidade !== '') {
    $where .= ' AND i.cidade = ?';
    $params[] = $cidade;
}
if ($categoria > 0) {
    $where .= ' AND i.categoria_id = ?';
    $params[] = $categoria;
}
if ($quartos > 0) {
    $where .= ' AND i.quartos >= ?';
    $params[] = $quartos;
}
if ($precoMin > 0) {
    $where .= ' AND i.preco >= ?';
    $params[] = $precoMin;
}
if ($precoMax > 0) {
    $where .= ' AND i.preco <= ?';
    $params[] = $precoMax;
}
if ($q !== '') {
    // A busca livre procura em título, bairro, cidade, código e descrição.
    $where .= ' AND (i.titulo LIKE ? OR i.bairro LIKE ? OR i.cidade LIKE ? OR i.codigo LIKE ? OR i.descricao LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}

// ---------- Total e página atual ----------
$stTotal = db()->prepare("SELECT COUNT(*) FROM imoveis i WHERE {$where}");
$stTotal->execute($params);
$total        = (int) $stTotal->fetchColumn();
$totalPaginas = max(1, (int) ceil($total / IMOVEIS_POR_PAGINA));
$pagina       = min($pagina, $totalPaginas);
$offset       = ($pagina - 1) * IMOVEIS_POR_PAGINA;

$st = db()->prepare(
    "SELECT i.*, c.nome AS categoria
       FROM imoveis i
       JOIN categorias c ON c.id = i.categoria_id
      WHERE {$where}
      ORDER BY i.destaque DESC, i.atualizado_em DESC
      LIMIT " . IMOVEIS_POR_PAGINA . " OFFSET {$offset}"
);
$st->execute($params);
$imoveis = $st->fetchAll();

// ---------- Opções dos campos do formulário ----------
$categorias = db()->query('SELECT id, nome FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll();
$cidades    = db()->query('SELECT DISTINCT cidade FROM imoveis WHERE ativo = 1 ORDER BY cidade')->fetchAll(PDO::FETCH_COLUMN);

// Filtros preenchidos, para montar os links da paginação e o botão "Limpar".
$filtrosAtivos = array_filter([
    'finalidade' => $finalidade,
    'categoria'  => $categoria > 0 ? (string) $categoria : '',
    'cidade'     => $cidade,
    'quartos'    => $quartos > 0 ? (string) $quartos : '',
    'preco_min'  => $precoMinTxt,
    'preco_max'  => $precoMaxTxt,
    'codigo'     => $codigo,
    'q'          => $q,
], static fn (string $v): bool => $v !== '');

$tituloPagina = 'Imóveis disponíveis';
if ($finalidade === 'venda') {
    $tituloPagina = 'Imóveis à venda';
} elseif ($finalidade === 'aluguel') {
    $tituloPagina = 'Imóveis para alugar';
}

$pageTitle       = $tituloPagina;
$pageDescription = $tituloPagina . ' na região do Cariri.';
$navActive       = $finalidade !== '' ? $finalidade : 'imoveis';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<div class="page-banner">
    <div class="container">
        <h1><?= e($tituloPagina) ?></h1>
        <p><?= $total === 0 ? 'Nenhum imóvel encontrado.' : e(plural($total, 'imóvel encontrado', 'imóveis encontrados')) . '.' ?></p>
    </div>
</div>

<section class="section section-listing">
    <div class="container">
        <form class="filter-bar" method="get" action="<?= e(url('imoveis.php')) ?>" aria-label="Filtrar imóveis">
            <div class="form-field">
                <label for="f-finalidade">Finalidade</label>
                <select id="f-finalidade" name="finalidade">
                    <option value="">Comprar ou alugar</option>
                    <option value="venda" <?= $finalidade === 'venda' ? 'selected' : '' ?>>Comprar</option>
                    <option value="aluguel" <?= $finalidade === 'aluguel' ? 'selected' : '' ?>>Alugar</option>
                </select>
            </div>

            <div class="form-field">
                <label for="f-cat">Tipo de imóvel</label>
                <select id="f-cat" name="categoria">
                    <option value="">Todos os tipos</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int) $cat['id'] ?>" <?= $categoria === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="f-cidade">Cidade</label>
                <select id="f-cidade" name="cidade">
                    <option value="">Todas as cidades</option>
                    <?php foreach ($cidades as $cid): ?>
                        <option value="<?= e($cid) ?>" <?= $cidade === $cid ? 'selected' : '' ?>><?= e($cid) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-field">
                <label for="f-quartos">Quartos</label>
                <select id="f-quartos" name="quartos">
                    <option value="">Qualquer</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= $quartos === $i ? 'selected' : '' ?>><?= $i ?>+ quarto<?= $i > 1 ? 's' : '' ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-field price-field">
                <label for="f-min">Faixa de preço (R$)</label>
                <div>
                    <input id="f-min" name="preco_min" inputmode="decimal" placeholder="Mín." value="<?= e($precoMinTxt) ?>" aria-label="Preço mínimo">
                    <input id="f-max" name="preco_max" inputmode="decimal" placeholder="Máx." value="<?= e($precoMaxTxt) ?>" aria-label="Preço máximo">
                </div>
            </div>

            <div class="form-field">
                <label for="f-q">Bairro ou palavra</label>
                <input type="search" id="f-q" name="q" value="<?= e($q) ?>" placeholder="Ex.: Centro">
            </div>

            <div class="form-field">
                <label for="f-codigo">Código / Ref.</label>
                <input type="text" id="f-codigo" name="codigo" value="<?= e($codigo) ?>" placeholder="Ex.: DEMO-001">
            </div>

            <div class="filter-actions">
                <button type="submit" class="button button-primary">Buscar imóveis</button>
                <?php if ($filtrosAtivos): ?>
                    <a class="text-link" href="<?= e(url('imoveis.php')) ?>">Limpar filtros</a>
                <?php endif; ?>
            </div>
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

            <?php if ($totalPaginas > 1): ?>
                <nav class="pagination" aria-label="Paginação">
                    <?php for ($p = 1; $p <= $totalPaginas; $p++): ?>
                        <?php $href = url('imoveis.php?' . http_build_query($filtrosAtivos + ['pagina' => $p])); ?>
                        <?php if ($p === $pagina): ?>
                            <span class="is-current" aria-current="page"><?= $p ?></span>
                        <?php else: ?>
                            <a href="<?= e($href) ?>" aria-label="Página <?= $p ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
