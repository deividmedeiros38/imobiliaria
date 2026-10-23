<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$nomeSite = site_setting('nome_site', 'Imobiliária');
$corretor = site_setting('corretor_nome', 'Corretor de Imóveis');
$sobre    = site_setting('sobre', '');
$cidade   = site_setting('cidade_atuacao', 'Cariri - CE');

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

$pageTitle = 'Imóveis à venda e para alugar';
$pageDescription = "Imóveis em {$cidade}. Atendimento direto com o corretor.";
$navActive = 'home';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<section class="hero">
    <div class="hero-inner">
        <h1>Imóveis em <?= e($cidade) ?></h1>
        <p class="hero-lead">
            Venda e aluguel. Atendimento direto, sem intermediário.
        </p>
        <form class="hero-search" action="<?= e(url('imoveis.php')) ?>" method="get" role="search">
            <label for="busca-codigo">Código do imóvel</label>
            <input type="search" id="busca-codigo" name="codigo" placeholder="Código do imóvel" autocomplete="off">
            <label for="busca-finalidade">Finalidade</label>
            <select id="busca-finalidade" name="finalidade">
                <option value="">Venda e aluguel</option>
                <option value="venda">Venda</option>
                <option value="aluguel">Aluguel</option>
            </select>
            <button type="submit" class="btn btn-accent">Buscar</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="section-head">
        <h2>Imóveis em destaque</h2>
        <a href="<?= e(url('imoveis.php')) ?>">Ver todos (<?= $totalAtivos ?>)</a>
    </div>

    <?php if (empty($destaques)): ?>
        <div class="empty-state">
            <h3>Nenhum imóvel no momento</h3>
            <p>Volte em breve ou entre em contato.</p>
        </div>
    <?php else: ?>
        <div class="cards-grid">
            <?php foreach ($destaques as $imovel): ?>
                <?php require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="section" style="padding-top:0">
    <div class="about-block">
        <h2>Quem sou</h2>
        <?php if ($sobre !== ''): ?>
            <p><?= nl2br(e($sobre)) ?></p>
        <?php else: ?>
            <p>Corretor de imóveis atuando na região do Cariri com atendimento personalizado.</p>
        <?php endif; ?>
        <ul class="about-list">
            <li>Atendimento direto com o corretor</li>
            <li>Orientação em documentação e negociação</li>
            <li>Conhecimento do mercado local</li>
        </ul>
        <p style="margin-top:18px">
            <a href="<?= e(url('sobre.php')) ?>" class="btn btn-outline">Saiba mais</a>
            &nbsp;
            <a href="<?= e(url('contato.php')) ?>" class="btn">Fale comigo</a>
        </p>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
