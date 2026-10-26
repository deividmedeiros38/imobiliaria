<?php
/**
 * Cartão de imóvel, usado na home e na listagem.
 * Variável esperada: $imovel (linha da tabela imoveis + coluna "categoria").
 */
$foto    = imovel_foto_principal((int) $imovel['id']);
$fotoSrc = $foto ? foto_url($foto['arquivo']) : '';
$link    = url('imovel.php?slug=' . urlencode($imovel['slug']));
$area    = imovel_area($imovel);
$aluguel = $imovel['finalidade'] === 'aluguel';
?>
<article class="property-card">
    <a class="property-image" href="<?= e($link) ?>" tabindex="-1" aria-hidden="true">
        <?php if ($fotoSrc !== ''): ?>
            <img src="<?= e($fotoSrc) ?>" alt="<?= e($foto['legenda'] ?: $imovel['titulo']) ?>" loading="lazy">
        <?php else: ?>
            <span class="property-image-empty">Sem foto</span>
        <?php endif; ?>
        <b class="property-tag<?= $aluguel ? ' tag-rent' : '' ?>"><?= e(finalidade_label($imovel['finalidade'])) ?></b>
    </a>

    <div class="property-content">
        <p class="property-code">Ref. <?= e($imovel['codigo']) ?> · <?= e($imovel['categoria']) ?></p>
        <h3><a href="<?= e($link) ?>"><?= e($imovel['titulo']) ?></a></h3>
        <p class="property-location"><?= e($imovel['bairro']) ?>, <?= e($imovel['cidade']) ?></p>
        <p class="property-price">
            <?= e(money($imovel['preco'])) ?><?php if ($aluguel): ?><small>/mês</small><?php endif; ?>
        </p>

        <ul class="property-features">
            <?php if ((int) $imovel['quartos'] > 0): ?><li><?= e(plural((int) $imovel['quartos'], 'quarto', 'quartos')) ?></li><?php endif; ?>
            <?php if ((int) $imovel['banheiros'] > 0): ?><li><?= e(plural((int) $imovel['banheiros'], 'banheiro', 'banheiros')) ?></li><?php endif; ?>
            <?php if ((int) $imovel['vagas'] > 0): ?><li><?= e(plural((int) $imovel['vagas'], 'vaga', 'vagas')) ?></li><?php endif; ?>
            <?php if ($area !== null): ?><li><?= e(number_format($area, 0, ',', '.')) ?> m²</li><?php endif; ?>
        </ul>

        <a class="property-more" href="<?= e($link) ?>">Ver detalhes<span aria-hidden="true"> →</span><span class="sr-only"> de <?= e($imovel['titulo']) ?></span></a>
    </div>
</article>
