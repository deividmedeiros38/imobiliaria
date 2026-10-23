<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$imovelId = (int) ($_GET['imovel'] ?? $_POST['imovel_id'] ?? 0);
$imovelRef = null;
if ($imovelId > 0) {
    $st = db()->prepare('SELECT id, codigo, titulo FROM imoveis WHERE id = ? AND ativo = 1');
    $st->execute([$imovelId]);
    $imovelRef = $st->fetch() ?: null;
    if (!$imovelRef) {
        $imovelId = 0;
    }
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $nome     = post_str('nome', 120);
    $email    = post_str('email', 190);
    $telefone = post_str('telefone', 30);
    $mensagem = post_str('mensagem', 3000);
    $consent  = !empty($_POST['consentimento']);

    if ($nome === '' || mb_strlen($nome) < 2) {
        $erro = 'Informe seu nome.';
    } elseif ($email !== '' && !valid_email($email)) {
        $erro = 'E-mail inválido.';
    } elseif ($mensagem === '' || mb_strlen($mensagem) < 10) {
        $erro = 'Escreva uma mensagem com pelo menos 10 caracteres.';
    } elseif (!$consent) {
        $erro = 'É necessário autorizar o uso dos dados para retorno do contato.';
    } else {
        $finalidade = $imovelRef
            ? 'Interesse no imóvel ' . $imovelRef['codigo']
            : 'Contato pelo site';

        $st = db()->prepare(
            'INSERT INTO contatos (imovel_id, nome, email, telefone, mensagem, consentimento, consentimento_em, finalidade, ip)
             VALUES (?, ?, ?, ?, ?, 1, NOW(), ?, ?)'
        );
        $st->execute([
            $imovelId > 0 ? $imovelId : null,
            $nome,
            $email !== '' ? $email : null,
            $telefone !== '' ? $telefone : null,
            $mensagem,
            $finalidade,
            client_ip(),
        ]);
        flash('success', 'Mensagem enviada. Em breve retornamos o contato.');
        redirect('contato.php');
    }
}

$pageTitle = 'Contato';
$pageDescription = 'Fale com o corretor.';
$navActive = 'contato';
require BASE_PATH . '/app/Views/public/layout_top.php';

$tel = site_setting('telefone', '');
$wa  = preg_replace('/\D+/', '', site_setting('whatsapp', $tel));
$em  = site_setting('email', '');
?>

<div class="page-banner">
    <div class="page-banner-inner">
        <h1>Fale conosco</h1>
    </div>
</div>

<section class="section">
    <div style="display:grid;gap:28px;max-width:860px">
        <?php if ($imovelRef): ?>
            <p class="text-muted">
                Sobre o imóvel <strong><?= e($imovelRef['codigo']) ?></strong> — <?= e($imovelRef['titulo']) ?>
            </p>
        <?php endif; ?>

        <?php if ($erro): ?>
            <p class="alert alert-error" role="alert"><?= e($erro) ?></p>
        <?php endif; ?>

        <form class="contact-form" method="post" action="<?= e(url('contato.php')) ?>">
            <?= csrf_field() ?>
            <?php if ($imovelId > 0): ?>
                <input type="hidden" name="imovel_id" value="<?= $imovelId ?>">
            <?php endif; ?>

            <label for="nome">Nome *</label>
            <input type="text" id="nome" name="nome" required maxlength="120" value="<?= e(post_str('nome', 120)) ?>" autocomplete="name">

            <label for="telefone">Telefone / WhatsApp</label>
            <input type="text" id="telefone" name="telefone" maxlength="30" value="<?= e(post_str('telefone', 30)) ?>" autocomplete="tel" placeholder="(88) 9xxxx-xxxx">

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" maxlength="190" value="<?= e(post_str('email', 190)) ?>" autocomplete="email">

            <label for="mensagem">Mensagem *</label>
            <textarea id="mensagem" name="mensagem" required maxlength="3000"><?= e(post_str('mensagem', 3000)) ?></textarea>

            <label class="form-check">
                <input type="checkbox" name="consentimento" value="1" <?= !empty($_POST['consentimento']) ? 'checked' : '' ?> required>
                <span>Autorizo o uso dos meus dados para retorno sobre este contato.</span>
            </label>

            <button type="submit" class="btn">Enviar mensagem</button>
        </form>

        <?php if ($tel || $wa || $em): ?>
        <div>
            <p style="font-weight:bold;color:var(--verde);margin:0 0 8px">Ou fale direto:</p>
            <?php if ($tel): ?>
                <p>Tel: <a href="tel:<?= e(preg_replace('/\D+/', '', $tel)) ?>"><?= e($tel) ?></a></p>
            <?php endif; ?>
            <?php if ($wa): ?>
                <p>WhatsApp: <a href="https://wa.me/55<?= e($wa) ?>" target="_blank" rel="noopener"><?= e($tel ?: $wa) ?></a></p>
            <?php endif; ?>
            <?php if ($em): ?>
                <p>E-mail: <a href="mailto:<?= e($em) ?>"><?= e($em) ?></a></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/public/layout_bottom.php'; ?>
