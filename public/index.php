<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$nomeSite = site_setting('nome_site', 'Imobiliária');
$corretor = site_setting('corretor_nome', 'Corretor de Imóveis');
$sobre    = site_setting('sobre', 'Atendimento personalizado com ética, profissionalismo e transparência.');
$cidade   = site_setting('cidade_atuacao', 'Cariri - CE');

// Destaques
$destaques = db()->query(
    "SELECT i.*, c.nome AS categoria
       FROM imoveis i
       JOIN categorias c ON c.id = i.categoria_id
      WHERE i.ativo = 1 AND i.destaque = 1
      ORDER BY i.atualizado_em DESC
      LIMIT 6"
)->fetchAll();

// Últimos se não houver destaques suficientes
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

$pageTitle = 'O imóvel que você procura está aqui';
$pageDescription = "Imóveis à venda e para alugar em {$cidade}. Confiança, transparência e atendimento personalizado.";
$navActive = 'home';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<section class="hero">
    <div class="hero-inner">
        <h1>O imóvel que você procura está aqui</h1>
        <p class="hero-lead">
            Confiança, transparência e credibilidade são os pilares do nosso atendimento em <?= e($cidade) ?>.
        </p>
        <form class="hero-search" action="<?= e(url('imoveis.php')) ?>" method="get" role="search">
            <label for="busca-codigo">Código do imóvel</label>
            <input type="search" id="busca-codigo" name="codigo" placeholder="Buscar por código (ex: DEMO-001)" autocomplete="off">
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
        <h2>Oportunidades em destaque</h2>
        <a href="<?= e(url('imoveis.php')) ?>">Ver todos (<?= $totalAtivos ?>)</a>
    </div>

    <?php if (empty($destaques)): ?>
        <div class="empty-state">
            <h3>Nenhum imóvel cadastrado ainda</h3>
            <p>Em breve novas oportunidades.</p>
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
        <div class="about-text">
            <h2>Quem sou</h2>
            <p><?= nl2br(e($sobre)) ?></p>
            <p>
                Ofereço atendimento personalizado, resultando em segurança em todos os negócios realizados.
            </p>
            <p style="margin-top:1.25rem">
                <a href="<?= e(url('sobre.php')) ?>" class="btn btn-outline">Conhecer melhor</a>
            </p>
        </div>
        <div class="about-highlights">
            <div class="highlight">
                <div class="highlight-icon">✓</div>
                <div>
                    <strong>Ética e transparência</strong>
                    <span>Negociações claras do início ao fim</span>
                </div>
            </div>
            <div class="highlight">
                <div class="highlight-icon">★</div>
                <div>
                    <strong>Atendimento personalizado</strong>
                    <span>Cada cliente é único — ouvimos suas necessidades</span>
                </div>
            </div>
            <div class="highlight">
                <div class="highlight-icon">📍</div>
                <div>
                    <strong>Região do Cariri</strong>
                    <span>Conhecimento profundo do mercado local</span>
                </div>
            </div>
            <div class="highlight">
                <div class="highlight-icon">🛡</div>
                <div>
                    <strong>Segurança jurídica</strong>
                    <span>Documentação e orientação em cada etapa</span>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
