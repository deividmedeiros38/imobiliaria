<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';
Auth::require();

$meuId = (int) Auth::user()['id'];
$erro = '';
$aba = in_array($_GET['aba'] ?? '', ['senha', 'novo'], true) ? $_GET['aba'] : 'senha';

// ---------- Trocar minha senha ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'senha') {
    csrf_require();
    $atual = (string) ($_POST['senha_atual'] ?? '');
    $nova  = (string) ($_POST['senha_nova'] ?? '');
    $conf  = (string) ($_POST['senha_conf'] ?? '');

    $st = db()->prepare('SELECT senha_hash FROM administradores WHERE id = ? AND ativo = 1');
    $st->execute([$meuId]);
    $hash = $st->fetchColumn();

    if (!$hash || !password_verify($atual, (string) $hash)) {
        $erro = 'Senha atual incorreta.';
        $aba = 'senha';
    } elseif (mb_strlen($nova) < 10) {
        $erro = 'A nova senha precisa ter no mínimo 10 caracteres.';
        $aba = 'senha';
    } elseif ($nova !== $conf) {
        $erro = 'A confirmação da senha não confere.';
        $aba = 'senha';
    } else {
        $up = db()->prepare('UPDATE administradores SET senha_hash = ? WHERE id = ?');
        $up->execute([password_hash($nova, PASSWORD_DEFAULT), $meuId]);
        flash('success', 'Senha alterada com sucesso.');
        redirect('admin/conta.php');
    }
}

// ---------- Criar novo admin ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'novo') {
    csrf_require();
    $nome  = post_str('nome', 120);
    $email = mb_strtolower(post_str('email', 190));
    $senha = (string) ($_POST['senha'] ?? '');
    $conf  = (string) ($_POST['senha_conf'] ?? '');

    if ($nome === '' || mb_strlen($nome) < 2) {
        $erro = 'Informe o nome.';
        $aba = 'novo';
    } elseif (!valid_email($email)) {
        $erro = 'E-mail inválido.';
        $aba = 'novo';
    } elseif (mb_strlen($senha) < 10) {
        $erro = 'A senha precisa ter no mínimo 10 caracteres.';
        $aba = 'novo';
    } elseif ($senha !== $conf) {
        $erro = 'A confirmação da senha não confere.';
        $aba = 'novo';
    } else {
        $existe = db()->prepare('SELECT id FROM administradores WHERE email = ?');
        $existe->execute([$email]);
        if ($existe->fetch()) {
            $erro = 'Já existe um administrador com este e-mail.';
            $aba = 'novo';
        } else {
            $ins = db()->prepare(
                'INSERT INTO administradores (nome, email, senha_hash) VALUES (?, ?, ?)'
            );
            $ins->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
            flash('success', 'Administrador criado: ' . $email);
            redirect('admin/conta.php?aba=novo');
        }
    }
}

// ---------- Desativar / reativar ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'toggle') {
    csrf_require();
    $id = (int) ($_POST['id'] ?? 0);
    if ($id === $meuId) {
        flash('error', 'Você não pode desativar a própria conta.');
    } elseif ($id > 0) {
        $st = db()->prepare('SELECT ativo FROM administradores WHERE id = ?');
        $st->execute([$id]);
        $atual = $st->fetchColumn();
        if ($atual !== false) {
            $novo = ((int) $atual === 1) ? 0 : 1;
            $up = db()->prepare('UPDATE administradores SET ativo = ? WHERE id = ?');
            $up->execute([$novo, $id]);
            flash('success', $novo ? 'Administrador reativado.' : 'Administrador desativado.');
        }
    }
    redirect('admin/conta.php');
}

$admins = db()->query(
    'SELECT id, nome, email, ativo, ultimo_login, criado_em
       FROM administradores
      ORDER BY nome'
)->fetchAll();

$pageTitle = 'Conta e administradores';
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<h1>Conta e administradores</h1>

<?php if ($erro): ?>
    <p class="alert alert-error" role="alert"><?= e($erro) ?></p>
<?php endif; ?>

<div class="admin-tabs">
    <a href="?aba=senha" class="<?= $aba === 'senha' ? 'active' : '' ?>">Minha senha</a>
    <a href="?aba=novo" class="<?= $aba === 'novo' ? 'active' : '' ?>">Novo administrador</a>
</div>

<?php if ($aba === 'senha'): ?>
<section class="admin-box">
    <h2>Alterar minha senha</h2>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="senha">
        <label for="senha_atual">Senha atual</label>
        <input type="password" id="senha_atual" name="senha_atual" required autocomplete="current-password">
        <label for="senha_nova">Nova senha (mín. 10 caracteres)</label>
        <input type="password" id="senha_nova" name="senha_nova" required minlength="10" autocomplete="new-password">
        <label for="senha_conf">Confirmar nova senha</label>
        <input type="password" id="senha_conf" name="senha_conf" required minlength="10" autocomplete="new-password">
        <button type="submit" class="btn">Salvar nova senha</button>
    </form>
</section>
<?php else: ?>
<section class="admin-box">
    <h2>Cadastrar administrador</h2>
    <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="novo">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" required maxlength="120" value="<?= e(post_str('nome', 120)) ?>">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required maxlength="190" value="<?= e(post_str('email', 190)) ?>">
        <label for="senha">Senha (mín. 10 caracteres)</label>
        <input type="password" id="senha" name="senha" required minlength="10" autocomplete="new-password">
        <label for="senha_conf2">Confirmar senha</label>
        <input type="password" id="senha_conf2" name="senha_conf" required minlength="10" autocomplete="new-password">
        <button type="submit" class="btn">Criar administrador</button>
    </form>
</section>
<?php endif; ?>

<section class="admin-box" style="margin-top:1.5rem">
    <h2>Administradores cadastrados</h2>
    <div class="table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Status</th>
                    <th>Último login</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($admins as $a): ?>
                <tr>
                    <td><?= e($a['nome']) ?><?= (int)$a['id'] === $meuId ? ' <span class="badge">você</span>' : '' ?></td>
                    <td><?= e($a['email']) ?></td>
                    <td><?= (int)$a['ativo'] === 1 ? 'Ativo' : 'Inativo' ?></td>
                    <td><?= $a['ultimo_login'] ? e($a['ultimo_login']) : '—' ?></td>
                    <td>
                        <?php if ((int)$a['id'] !== $meuId): ?>
                        <form method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="toggle">
                            <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                            <button type="submit" class="btn btn-small">
                                <?= (int)$a['ativo'] === 1 ? 'Desativar' : 'Reativar' ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php'; ?>
