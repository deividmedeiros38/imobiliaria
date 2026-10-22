<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';

if (Auth::check()) {
    redirect('admin/dashboard.php');
}

$emailValue = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $emailValue = mb_strtolower(post_str('email', 190));
    $password   = (string) ($_POST['senha'] ?? '');

    if (!valid_email($emailValue) || $password === '') {
        $errors[] = 'Informe um e-mail válido e a senha.';
    } elseif (Auth::tooManyAttempts($emailValue)) {
        $errors[] = 'Muitas tentativas. Aguarde alguns minutos e tente de novo.';
    } elseif (Auth::attempt($emailValue, $password)) {
        redirect('admin/dashboard.php');
    } else {
        // Mensagem igual para e-mail inexistente e senha errada.
        $errors[] = 'E-mail ou senha incorretos.';
    }
}

$pageTitle = 'Entrar';
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<section class="login-box">
    <h1>Entrar no painel</h1>
    <?php foreach ($errors as $err): ?>
        <p class="alert alert-error" role="alert"><?= e($err) ?></p>
    <?php endforeach; ?>
    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" autocomplete="username" required value="<?= e($emailValue) ?>">
        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" autocomplete="current-password" required>
        <button type="submit" class="btn">Entrar</button>
    </form>
</section>
<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php';
