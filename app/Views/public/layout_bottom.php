<?php
/** Rodapé comum das páginas públicas. */
$nomeSite    = site_setting('nome_site', 'Imobiliária');
$telefone    = site_setting('telefone', '');
$email       = site_setting('email', '');
$creci       = site_setting('creci', '');
$cidade      = site_setting('cidade_atuacao', 'Cariri - CE');
$waRodape = whatsapp_url('Olá! Vim pelo site e gostaria de atendimento.');
?>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <a class="footer-brand" href="<?= e(url('')) ?>"><?= e($nomeSite) ?></a>
            <p><?= e($cidade) ?></p>
            <?php if ($creci !== ''): ?><p>CRECI <?= e($creci) ?></p><?php endif; ?>
        </div>

        <div>
            <h2>Navegação</h2>
            <a href="<?= e(url('imoveis.php')) ?>">Imóveis</a>
            <a href="<?= e(url('imoveis.php?finalidade=venda')) ?>">Comprar</a>
            <a href="<?= e(url('imoveis.php?finalidade=aluguel')) ?>">Alugar</a>
            <a href="<?= e(url('sobre.php')) ?>">Sobre</a>
            <a href="<?= e(url('contato.php')) ?>">Contato</a>
        </div>

        <div>
            <h2>Contato</h2>
            <?php if ($telefone !== ''): ?>
                <a href="tel:<?= e(preg_replace('/\D+/', '', $telefone)) ?>"><?= e($telefone) ?></a>
            <?php endif; ?>
            <?php if ($email !== ''): ?>
                <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
            <?php endif; ?>
            <?php if ($waRodape !== ''): ?>
                <a href="<?= e($waRodape) ?>" target="_blank" rel="noopener">Atendimento via WhatsApp</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> <?= e($nomeSite) ?></span>
        <a href="<?= e(url('contato.php')) ?>">Fale conosco</a>
    </div>
</footer>

<?php if ($waRodape !== ''): ?>
    <a class="float-wa" href="<?= e($waRodape) ?>" target="_blank" rel="noopener" aria-label="Falar pelo WhatsApp">
        <svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false"><path fill="currentColor" d="M20 2H4a2 2 0 0 0-2 2v18l4-4h14a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Zm-4 11H8v-2h8v2Zm0-3H8V8h8v2Z"/></svg>
    </a>
<?php endif; ?>
</body>
</html>
