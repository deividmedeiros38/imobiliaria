<?php
/** @var array $imovel */
$foto = imovel_foto_principal((int) $imovel['id']);
$fotoSrc = $foto ? foto_url($foto['arquivo']) : '';
$finalidade = finalidade_label($imovel['finalidade']);
?>
<article class="card-imovel">
    <a href="<?= e(url('imovel.php?slug=' . urlencode($imovel['slug']))) ?>" class="card-photo" aria-hidden="true" tabindex="-1">
        <?php if ($fotoSrc): ?>
            <img src="<?= e($fotoSrc) ?>" alt="<?= e($foto['legenda'] ?: $imovel['titulo']) ?>" loading="lazy">
        <?php else: ?>
            <div class="placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1H4a1 1 0 01-1-1V9.5z"/>
                    <path d="M9 21V12h6v9"/>
                </svg>
            </div>
        <?php endif; ?>
        <span class="card-badge <?= $imovel['finalidade'] === 'aluguel' ? 'aluguel' : '' ?>"><?= e($finalidade) ?></span>
    </a>
    <div class="card-body">
        <div class="card-price"><?= e(money($imovel['preco'])) ?><?= $imovel['finalidade'] === 'aluguel' ? '<small style="font-size:.75rem;font-weight:500"> /mês</small>' : '' ?></div>
        <h3 class="card-title">
            <a href="<?= e(url('imovel.php?slug=' . urlencode($imovel['slug']))) ?>"><?= e($imovel['titulo']) ?></a>
        </h3>
        <div class="card-loc">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <?= e($imovel['bairro']) ?>, <?= e($imovel['cidade']) ?>
        </div>
        <div class="card-meta">
            <?php if ((int)$imovel['quartos'] > 0): ?>
                <span title="Quartos">🛏 <?= (int)$imovel['quartos'] ?></span>
            <?php endif; ?>
            <?php if ((int)$imovel['banheiros'] > 0): ?>
                <span title="Banheiros">🚿 <?= (int)$imovel['banheiros'] ?></span>
            <?php endif; ?>
            <?php if ((int)$imovel['vagas'] > 0): ?>
                <span title="Vagas">🚗 <?= (int)$imovel['vagas'] ?></span>
            <?php endif; ?>
            <?php if ($imovel['area_construida']): ?>
                <span title="Área construída">📐 <?= e(number_format((float)$imovel['area_construida'], 0, ',', '.')) ?> m²</span>
            <?php endif; ?>
        </div>
        <div class="card-code">Cód. <?= e($imovel['codigo']) ?></div>
    </div>
</article>
