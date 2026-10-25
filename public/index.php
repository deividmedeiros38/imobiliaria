<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

// ---------- Dados do site ----------
$corretor = site_setting('corretor_nome', 'Corretor de imóveis');
$sobre    = site_setting('sobre', '');
$cidade   = site_setting('cidade_atuacao', 'Cariri - CE');
$creci    = site_setting('creci', '');

// ---------- Dados para a busca ----------
$categorias = db()->query('SELECT id, nome FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll();

// ---------- Vitrines (usam só imóveis reais do banco) ----------
$destaques = imoveis_para_home(null, true, 6);
if (count($destaques) < 3) {
    $destaques = imoveis_para_home(null, false, 6); // poucos destaques: completa com os mais recentes
}
$comprar = imoveis_para_home('venda');
$alugar  = imoveis_para_home('aluguel');
$totalAtivos = (int) db()->query('SELECT COUNT(*) FROM imoveis WHERE ativo = 1')->fetchColumn();

$pageTitle       = 'Encontre seu novo imóvel';
$pageDescription = "Imóveis para comprar e alugar em {$cidade}.";
$navActive       = 'home';
$whatsappUrl     = whatsapp_url('Olá! Vim pelo site e gostaria de falar com um corretor.');
require BASE_PATH . '/app/Views/public/layout_top.php';
?>

<!-- 1. Banner principal -->
<section class="home-hero">
    <div class="home-hero-image" role="img" aria-label="Fachada de uma casa moderna ao entardecer"></div>
    <div class="container home-hero-content">
        <p class="hero-overline">Imóveis selecionados &middot; <?= e($cidade) ?></p>
        <h1>O imóvel que você procura está aqui!</h1>
        <p class="hero-text">Encontre casas, apartamentos e terrenos com segurança, praticidade e as melhores condições.</p>
        <div class="hero-actions">
            <a class="button button-primary" href="#buscar">Encontrar imóvel</a>
            <a class="button button-ghost" href="<?= e($whatsappUrl !== '' ? $whatsappUrl : url('contato.php')) ?>"<?= $whatsappUrl !== '' ? ' target="_blank" rel="noopener"' : '' ?>>Fale com um corretor</a>
        </div>
    </div>
</section>

<!-- 2. Busca de imóveis -->
<section class="property-search" id="buscar" aria-labelledby="busca-title">
    <div class="container">
        <form class="search-panel" action="<?= e(url('imoveis.php')) ?>" method="get">
            <div class="search-panel-title">
                <p class="section-kicker">Busca de imóveis</p>
                <h2 id="busca-title">Encontre seu novo imóvel</h2>
            </div>

            <div class="search-tabs" role="group" aria-label="Finalidade">
                <label><input type="radio" name="finalidade" value="" checked><span>Todos</span></label>
                <label><input type="radio" name="finalidade" value="venda"><span>Comprar</span></label>
                <label><input type="radio" name="finalidade" value="aluguel"><span>Alugar</span></label>
            </div>

            <div class="search-fields">
                <div class="form-field">
                    <label for="s-cat">Tipo de imóvel</label>
                    <select id="s-cat" name="categoria">
                        <option value="">Todos os tipos</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= (int) $cat['id'] ?>"><?= e($cat['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-field">
                    <label for="s-local">Cidade, bairro ou código</label>
                    <input id="s-local" name="q" type="search" placeholder="Onde você procura?">
                </div>

                <div class="form-field">
                    <label for="s-quartos">Quartos</label>
                    <select id="s-quartos" name="quartos">
                        <option value="">Qualquer</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?>+ quarto<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-field price-field">
                    <label for="s-min">Faixa de preço (R$)</label>
                    <div>
                        <input id="s-min" name="preco_min" inputmode="decimal" placeholder="Mín." aria-label="Preço mínimo">
                        <input id="s-max" name="preco_max" inputmode="decimal" placeholder="Máx." aria-label="Preço máximo">
                    </div>
                </div>

                <button class="button button-primary search-submit" type="submit">Buscar imóveis</button>
            </div>
        </form>
    </div>
</section>

<!-- 3. Imóveis em destaque -->
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="section-kicker">Seleção especial</p>
                <h2>Imóveis em destaque</h2>
            </div>
            <a class="text-link" href="<?= e(url('imoveis.php')) ?>">Ver todos os imóveis <span aria-hidden="true">&rarr;</span></a>
        </div>

        <?php if ($destaques): ?>
            <div class="cards-grid">
                <?php foreach ($destaques as $imovel): ?>
                    <?php require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h3>Nenhum imóvel disponível no momento.</h3>
                <p>Fale conosco para receber novas oportunidades.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 4. Imóveis para comprar -->
<?php if ($comprar): ?>
<section class="section section-soft">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="section-kicker">Para você comprar</p>
                <h2>Encontre o seu próximo endereço</h2>
            </div>
            <a class="text-link" href="<?= e(url('imoveis.php?finalidade=venda')) ?>">Ver opções de venda <span aria-hidden="true">&rarr;</span></a>
        </div>
        <div class="cards-grid">
            <?php foreach ($comprar as $imovel): ?>
                <?php require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5. Imóveis para alugar -->
<?php if ($alugar): ?>
<section class="section">
    <div class="container">
        <div class="section-heading">
            <div>
                <p class="section-kicker">Para você alugar</p>
                <h2>Praticidade para a sua rotina</h2>
            </div>
            <a class="text-link" href="<?= e(url('imoveis.php?finalidade=aluguel')) ?>">Ver opções de aluguel <span aria-hidden="true">&rarr;</span></a>
        </div>
        <div class="cards-grid">
            <?php foreach ($alugar as $imovel): ?>
                <?php require BASE_PATH . '/app/Views/public/card_imovel.php'; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 6. Apresentação do corretor -->
<section class="section section-dark">
    <div class="container about-highlight">
        <div>
            <p class="section-kicker">Atendimento próximo e transparente</p>
            <h2>Negócios imobiliários com a segurança que você procura.</h2>
            <p><?= e($sobre !== '' ? mb_substr($sobre, 0, 240) : 'Conte com atendimento personalizado para encontrar, vender ou alugar seu imóvel.') ?></p>
            <a class="button button-light" href="<?= e(url('sobre.php')) ?>">Conheça <?= e($corretor) ?></a>
        </div>
        <dl class="about-facts">
            <div><dt><?= $totalAtivos ?></dt><dd><?= $totalAtivos === 1 ? 'imóvel disponível' : 'imóveis disponíveis' ?></dd></div>
            <?php if ($creci !== ''): ?>
                <div><dt>CRECI <?= e($creci) ?></dt><dd>registro profissional</dd></div>
            <?php endif; ?>
            <div><dt><?= e($cidade) ?></dt><dd>região de atuação</dd></div>
        </dl>
    </div>
</section>

<!-- 7. Diferenciais -->
<section class="section">
    <div class="container benefits">
        <div>
            <p class="section-kicker">Por que contar conosco</p>
            <h2>Uma jornada imobiliária mais simples.</h2>
        </div>
        <div class="benefit-list">
            <article><span>01</span><h3>Imóveis selecionados</h3><p>Opções apresentadas com informações claras para a sua decisão.</p></article>
            <article><span>02</span><h3>Orientação em cada etapa</h3><p>Atendimento direto para tirar dúvidas e conduzir a negociação.</p></article>
            <article><span>03</span><h3>Transparência</h3><p>Relações baseadas em clareza, confiança e atenção aos detalhes.</p></article>
        </div>
    </div>
</section>

<!-- 9. Chamada para contato -->
<section class="contact-banner">
    <div class="container">
        <div>
            <p class="section-kicker">Precisa de ajuda?</p>
            <h2>Vamos encontrar o imóvel ideal para você.</h2>
        </div>
        <?php if ($whatsappUrl !== ''): ?>
            <a class="button button-whatsapp" href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener">Chamar no WhatsApp</a>
        <?php else: ?>
            <a class="button button-primary" href="<?= e(url('contato.php')) ?>">Falar com um corretor</a>
        <?php endif; ?>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
