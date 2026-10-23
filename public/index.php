<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$nomeSite = site_setting('nome_site', 'Imobiliária');
$corretor = site_setting('corretor_nome', 'Corretor de Imóveis');
$sobre    = site_setting('sobre', '');
$cidade   = site_setting('cidade_atuacao', 'Cariri - CE');
$creci    = site_setting('creci', '');

$destaques = db()->query(
    "SELECT i.*, c.nome AS categoria
       FROM imoveis i
       JOIN categorias c ON c.id = i.categoria_id
      WHERE i.ativo = 1 AND i.destaque = 1
      ORDER BY i.atualizado_em DESC
      LIMIT 6"
)->fetchAll();

if (count($destaques) < 3) {
    $destaques = db()->query(
        "SELECT i.*, c.nome AS categoria
           FROM imoveis i
           JOIN categorias c ON c.id = i.categoria_id
          WHERE i.ativo = 1
          ORDER BY i.destaque DESC, i.atualizado_em DESC
          LIMIT 6"
    )->fetchAll();
}

$totalAtivos = (int) db()->query('SELECT COUNT(*) FROM imoveis WHERE ativo = 1')->fetchColumn();
$categorias = db()->query('SELECT id, nome FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll();

$pageTitle = 'O imóvel que você procura está aqui';
$pageDescription = "Imóveis à venda e para alugar em {$cidade}.";
$navActive = 'home';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<section class="hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="hero-inner">
        <h1>O imóvel que você procura está aqui!</h1>
        <p class="hero-lead">
            Confiança, transparência e credibilidade são os pilares do nosso atendimento.
        </p>
    </div>
</section>

<div class="search-wrap">
    <div class="search-box">
        <div class="search-tabs">
            <a href="<?= e(url('imoveis.php?finalidade=venda')) ?>" class="active">Venda</a>
            <a href="<?= e(url('imoveis.php?finalidade=aluguel')) ?>">Aluguel</a>
            <a href="<?= e(url('imoveis.php')) ?>">Código</a>
        </div>
        <form class="search-fields" action="<?= e(url('imoveis.php')) ?>" method="get">
            <div class="field">
                <label for="s-finalidade">Finalidade</label>
                <select id="s-finalidade" name="finalidade">
                    <option value="">Todas</option>
                    <option value="venda" selected>Venda</option>
                    <option value="aluguel">Aluguel</option>
                </select>
            </div>
            <div class="field">
                <label for="s-cat">Tipo de imóvel</label>
                <select id="s-cat" name="categoria">
                    <option value="">Todos</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"><?= e($cat['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="s-codigo">Código</label>
                <input type="text" id="s-codigo" name="codigo" placeholder="Ex: DEMO-001">
            </div>
            <div class="field">
                <label for="s-q">Bairro / palavra</label>
                <input type="search" id="s-q" name="q" placeholder="Buscar…">
            </div>
            <button type="submit" class="btn">Buscar</button>
        </form>
    </div>
</div>

<section class="section">
    <div class="section-head">
        <h2>Imóveis em destaque</h2>
        <a href="<?= e(url('imoveis.php')) ?>">Ver todos (<?= $totalAtivos ?>)</a>
    </div>

    <?php if (empty($destaques)): ?>
        <div class="empty-state">
            <h3>Nenhum imóvel no momento</h3>
            <p>Entre em contato para mais informações.</p>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($destaques as $imovel): ?>
                <?php $horizontal = false; require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="section" style="padding-top:0">
    <div class="banner-sobre">
        <div class="banner-sobre-text">
            <h2>Negócios Imobiliários</h2>
            <?php if ($sobre !== ''): ?>
                <p><?= e(mb_substr($sobre, 0, 220)) ?><?= mb_strlen($sobre) > 220 ? '…' : '' ?></p>
            <?php else: ?>
                <p>Atendimento direto na região do Cariri, com transparência nas negociações.</p>
            <?php endif; ?>
            <div class="banner-sobre-meta">
                <?php if ($creci): ?>
                    <div><strong>CRECI</strong> <?= e($creci) ?></div>
                <?php endif; ?>
                <div><strong>Região</strong> <?= e($cidade) ?></div>
            </div>
            <p style="margin-top:18px">
                <a href="<?= e(url('sobre.php')) ?>" class="btn btn-outline" style="border-color:#c9a227;color:#c9a227">Conhecer</a>
                &nbsp;
                <a href="<?= e(url('contato.php')) ?>" class="btn">Fale conosco</a>
            </p>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
