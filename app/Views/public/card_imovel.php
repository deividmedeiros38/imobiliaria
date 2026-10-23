<?php
/** @var array $imovel */
/** @var bool $horizontal */
$horizontal = !empty($horizontal);
$foto = imovel_foto_principal((int) $imovel['id']);
$fotoSrc = $foto ? foto_url($foto['arquivo']) : '';
$finalidade = finalidade_label($imovel['finalidade']);
$link = url('imovel.php?slug=' . urlencode($imovel['slug']));
?>
<?php if ($horizontal): ?>
<article class="card-h">
    <a href="<?= e($link) ?>" class="card-h-photo" tabindex="-1">
        <?php if ($fotoSrc): ?>
            <img src="<?= e($fotoSrc) ?>" alt="<?= e($foto['legenda'] ?: $imovel['titulo']) ?>" loading="lazy">
        <?php else: ?>
            <div class="placeholder">Sem foto</div>
        <?php endif; ?>
        <span class="card-h-badge"><?= e($finalidade) ?></span>
    </a>
    <div class="card-h-body">
        <div class="card-h-top">
            <div>
                <h3 class="card-h-loc">
                    <a href="<?= e($link) ?>"><?= e($imovel['bairro']) ?>, <?= e($imovel['cidade']) ?></a>
                </h3>
                <p class="card-h-ref">Ref: <?= e($imovel['codigo']) ?> — <?= e($imovel['categoria'] ?? '') ?></p>
            </div>
            <div class="card-h-price">
                <?= e(money($imovel['preco'])) ?>
                <?php if ($imovel['finalidade'] === 'aluguel'): ?>
                    <small style="font-size:0.75rem;font-weight:normal">/mês</small>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-h-meta">
            <?php if ((int)$imovel['quartos'] > 0): ?>
                <span class="pill"><?= (int)$imovel['quartos'] ?> dormitório<?= (int)$imovel['quartos'] > 1 ? 's' : '' ?></span>
            <?php endif; ?>
            <?php if ((int)$imovel['banheiros'] > 0): ?>
                <span class="pill"><?= (int)$imovel['banheiros'] ?> banheiro<?= (int)$imovel['banheiros'] > 1 ? 's' : '' ?></span>
            <?php endif; ?>
            <?php if ((int)$imovel['vagas'] > 0): ?>
                <span class="pill"><?= (int)$imovel['vagas'] ?> vaga<?= (int)$imovel['vagas'] > 1 ? 's' : '' ?></span>
            <?php endif; ?>
            <?php if ($imovel['area_construida']): ?>
                <span class="pill"><?= e(number_format((float)$imovel['area_construida'], 0, ',', '.')) ?> m²</span>
            <?php endif; ?>
        </div>
        <div class="card-h-actions">
            <a class="btn btn-msg" href="<?= e(url('contato.php?imovel=' . (int)$imovel['id'])) ?>">Enviar mensagem</a>
            <a class="link" href="<?= e($link) ?>">Ver detalhes</a>
        </div>
    </div>
</article>
<?php else: ?>
<article class="card-imovel">
    <a href="<?= e($link) ?>" class="card-photo" tabindex="-1">
        <?php if ($fotoSrc): ?>
            <img src="<?= e($fotoSrc) ?>" alt="<?= e($foto['legenda'] ?: $imovel['titulo']) ?>" loading="lazy">
        <?php else: ?>
            <div class="placeholder">Sem foto</div>
        <?php endif; ?>
        <span class="card-badge <?= $imovel['finalidade'] === 'aluguel' ? 'aluguel' : '' ?>"><?= e($finalidade) ?></span>
    </a>
    <div class="card-body">
        <div class="card-price">
            <?= e(money($imovel['preco'])) ?>
            <?php if ($imovel['finalidade'] === 'aluguel'): ?>
                <small style="font-size:0.75rem;font-weight:normal">/mês</small>
            <?php endif; ?>
        </div>
        <h3 class="card-title">
            <a href="<?= e($link) ?>"><?= e($imovel['titulo']) ?></a>
        </h3>
        <div class="card-loc"><?= e($imovel['bairro']) ?> · <?= e($imovel['cidade']) ?></div>
        <div class="card-meta">
            <?php if ((int)$imovel['quartos'] > 0): ?>
                <span><?= (int)$imovel['quartos'] ?> quarto<?= (int)$imovel['quartos'] > 1 ? 's' : '' ?></span>
            <?php endif; ?>
            <?php if ((int)$imovel['banheiros'] > 0): ?>
                <span><?= (int)$imovel['banheiros'] ?> banheiro<?= (int)$imovel['banheiros'] > 1 ? 's' : '' ?></span>
            <?php endif; ?>
            <?php if ((int)$imovel['vagas'] > 0): ?>
                <span><?= (int)$imovel['vagas'] ?> vaga<?= (int)$imovel['vagas'] > 1 ? 's' : '' ?></span>
            <?php endif; ?>
            <?php if ($imovel['area_construida']): ?>
                <span><?= e(number_format((float)$imovel['area_construida'], 0, ',', '.')) ?> m²</span>
            <?php endif; ?>
        </div>
        <div class="card-code">Ref. <?= e($imovel['codigo']) ?></div>
    </div>
</article>
<?php endif; ?>
