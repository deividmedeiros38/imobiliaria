<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';
Auth::require();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'lido') {
    csrf_require();
    $cid = (int) ($_POST['id'] ?? 0);
    if ($cid > 0) {
        db()->prepare('UPDATE contatos SET lido = 1 WHERE id = ?')->execute([$cid]);
        flash('success', 'Marcado como lido.');
    }
    redirect('admin/contatos.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    csrf_require();
    $cid = (int) ($_POST['id'] ?? 0);
    if ($cid > 0) {
        db()->prepare('DELETE FROM contatos WHERE id = ?')->execute([$cid]);
        flash('success', 'Contato excluído.');
    }
    redirect('admin/contatos.php');
}

$lista = db()->query(
    'SELECT c.*, i.codigo AS imovel_codigo, i.titulo AS imovel_titulo
       FROM contatos c
       LEFT JOIN imoveis i ON i.id = c.imovel_id
      ORDER BY c.lido ASC, c.criado_em DESC
      LIMIT 200'
)->fetchAll();

$pageTitle = 'Contatos';
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<h1>Mensagens de contato</h1>
<p class="note"><?= count($lista) ?> mensagem(ns)</p>

<div class="table-wrap">
<table class="admin-table">
    <thead>
        <tr>
            <th>Data</th>
            <th>Nome</th>
            <th>Contato</th>
            <th>Assunto</th>
            <th>Mensagem</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($lista)): ?>
        <tr><td colspan="6">Nenhuma mensagem ainda.</td></tr>
    <?php endif; ?>
    <?php foreach ($lista as $c): ?>
        <tr class="<?= (int)$c['lido'] ? '' : 'row-unread' ?>">
            <td><?= e($c['criado_em']) ?></td>
            <td><?= e($c['nome']) ?></td>
            <td>
                <?php if ($c['telefone']): ?><?= e($c['telefone']) ?><br><?php endif; ?>
                <?php if ($c['email']): ?><small><?= e($c['email']) ?></small><?php endif; ?>
            </td>
            <td>
                <?= e($c['finalidade']) ?>
                <?php if ($c['imovel_codigo']): ?>
                    <br><small><?= e($c['imovel_codigo']) ?></small>
                <?php endif; ?>
            </td>
            <td style="max-width:16rem"><?= e(mb_substr($c['mensagem'], 0, 180)) ?><?= mb_strlen($c['mensagem']) > 180 ? '…' : '' ?></td>
            <td class="actions">
                <?php if (!(int)$c['lido']): ?>
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="lido">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button type="submit" class="link-btn">Marcar lido</button>
                </form>
                <?php endif; ?>
                <form method="post" class="inline-form" onsubmit="return confirm('Excluir?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                    <button type="submit" class="link-btn danger">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php'; ?>
