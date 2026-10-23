<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';
Auth::require();

$chaves = [
    'nome_site' => 'Nome do site',
    'corretor_nome' => 'Nome do corretor',
    'creci' => 'CRECI',
    'telefone' => 'Telefone',
    'whatsapp' => 'WhatsApp (só números, com DDD)',
    'email' => 'E-mail de contato',
    'cidade_atuacao' => 'Cidade / região de atuação',
    'sobre' => 'Texto “Quem sou”',
];

$valores = [];
foreach (db()->query('SELECT chave, valor FROM configuracoes') as $row) {
    $valores[$row['chave']] = (string) $row['valor'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $up = db()->prepare(
        'INSERT INTO configuracoes (chave, valor) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
    );
    foreach ($chaves as $chave => $label) {
        if ($chave === 'sobre') {
            $val = post_str('sobre', 5000);
        } elseif ($chave === 'whatsapp') {
            $val = preg_replace('/\D+/', '', post_str('whatsapp', 20)) ?? '';
        } else {
            $val = post_str($chave, $chave === 'email' ? 190 : 200);
        }
        $up->execute([$chave, $val]);
        $valores[$chave] = $val;
    }
    flash('success', 'Configurações salvas.');
    redirect('admin/configuracoes.php');
}

$pageTitle = 'Configurações';
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<h1>Configurações do site</h1>
<form method="post" class="admin-form" style="max-width:36rem">
    <?= csrf_field() ?>
    <?php foreach ($chaves as $chave => $label): ?>
        <label for="<?= e($chave) ?>"><?= e($label) ?></label>
        <?php if ($chave === 'sobre'): ?>
            <textarea id="<?= e($chave) ?>" name="<?= e($chave) ?>" rows="6"><?= e($valores[$chave] ?? '') ?></textarea>
        <?php else: ?>
            <input type="<?= $chave === 'email' ? 'email' : 'text' ?>"
                   id="<?= e($chave) ?>" name="<?= e($chave) ?>"
                   value="<?= e($valores[$chave] ?? '') ?>">
        <?php endif; ?>
    <?php endforeach; ?>
    <button type="submit" class="btn">Salvar</button>
</form>
<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php'; ?>
