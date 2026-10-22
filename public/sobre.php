<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$nomeSite = site_setting('nome_site', 'Imobiliária');
$corretor = site_setting('corretor_nome', 'Corretor de Imóveis');
$sobre    = site_setting('sobre', 'Atendimento personalizado com ética, profissionalismo e transparência.');
$creci    = site_setting('creci', '');
$cidade   = site_setting('cidade_atuacao', 'Cariri - CE');

$pageTitle = 'Quem sou';
$pageDescription = "Conheça {$corretor} — corretor de imóveis na região do Cariri.";
$navActive = 'sobre';
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<section class="section">
    <div class="about-block" style="max-width:820px;margin:0 auto">
        <div class="about-text">
            <h2>Quem sou</h2>
            <p style="font-size:1.05rem;font-weight:600;color:var(--navy)"><?= e($corretor) ?></p>
            <?php if ($creci): ?>
                <p class="text-muted" style="margin-top:-.4rem">CRECI <?= e($creci) ?></p>
            <?php endif; ?>
            <p><?= nl2br(e($sobre)) ?></p>
            <p>
                Atuo com ética, profissionalismo e transparência nas negociações.
                Ofereço aos clientes um atendimento personalizado, o que resulta em segurança
                a todos os negócios realizados.
            </p>
            <p>
                Conhecimento profundo do mercado imobiliário da região de <?= e($cidade) ?>,
                com foco em confiança e resultados.
            </p>
        </div>
        <div class="about-highlights">
            <div class="highlight">
                <div class="highlight-icon">1</div>
                <div>
                    <strong>Confiança</strong>
                    <span>Relacionamento transparente em cada etapa da negociação</span>
                </div>
            </div>
            <div class="highlight">
                <div class="highlight-icon">2</div>
                <div>
                    <strong>Profissionalismo</strong>
                    <span>Orientação técnica e acompanhamento completo</span>
                </div>
            </div>
            <div class="highlight">
                <div class="highlight-icon">3</div>
                <div>
                    <strong>Mercado local</strong>
                    <span>Especialista na região do Cariri (CE)</span>
                </div>
            </div>
            <div class="highlight">
                <div class="highlight-icon">4</div>
                <div>
                    <strong>Resultados</strong>
                    <span>Compromisso com o sucesso da sua compra ou venda</span>
                </div>
            </div>
        </div>
    </div>

    <p style="text-align:center;margin-top:2rem">
        <a href="<?= e(url('contato.php')) ?>" class="btn btn-accent">Fale comigo</a>
        &nbsp;
        <a href="<?= e(url('imoveis.php')) ?>" class="btn">Ver imóveis</a>
    </p>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
