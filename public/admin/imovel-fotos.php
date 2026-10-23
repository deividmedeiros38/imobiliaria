<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';
Auth::require();

$id = (int) ($_GET['id'] ?? $_POST['imovel_id'] ?? 0);
$st = db()->prepare('SELECT id, titulo, codigo FROM imoveis WHERE id = ?');
$st->execute([$id]);
$imovel = $st->fetch();
if (!$imovel) {
    flash('error', 'Imóvel não encontrado.');
    redirect('admin/imoveis.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'upload') {
    csrf_require();
    $error = null;
    $nome = save_upload_image($_FILES['foto'] ?? [], $error);
    if (!$nome) {
        flash('error', $error ?? 'Falha no upload.');
    } else {
        $legenda = post_str('legenda', 160);

        $stc = db()->prepare('SELECT COUNT(*) FROM imovel_fotos WHERE imovel_id = ? AND principal = 1');
        $stc->execute([$id]);
        $temPrincipal = (int) $stc->fetchColumn() > 0;

        $sto = db()->prepare('SELECT COALESCE(MAX(ordem),0)+1 FROM imovel_fotos WHERE imovel_id = ?');
        $sto->execute([$id]);
        $ordem = (int) $sto->fetchColumn();

        $ins = db()->prepare(
            'INSERT INTO imovel_fotos (imovel_id, arquivo, legenda, ordem, principal) VALUES (?,?,?,?,?)'
        );
        $ins->execute([$id, $nome, $legenda !== '' ? $legenda : null, $ordem, $temPrincipal ? 0 : 1]);
        flash('success', 'Foto adicionada.');
    }
    redirect('admin/imovel-fotos.php?id=' . $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'principal') {
    csrf_require();
    $fotoId = (int) ($_POST['foto_id'] ?? 0);
    db()->prepare('UPDATE imovel_fotos SET principal = 0 WHERE imovel_id = ?')->execute([$id]);
    db()->prepare('UPDATE imovel_fotos SET principal = 1 WHERE id = ? AND imovel_id = ?')->execute([$fotoId, $id]);
    flash('success', 'Foto principal definida.');
    redirect('admin/imovel-fotos.php?id=' . $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    csrf_require();
    $fotoId = (int) ($_POST['foto_id'] ?? 0);
    $stf = db()->prepare('SELECT arquivo, principal FROM imovel_fotos WHERE id = ? AND imovel_id = ?');
    $stf->execute([$fotoId, $id]);
    $foto = $stf->fetch();
    if ($foto) {
        delete_upload_file($foto['arquivo']);
        db()->prepare('DELETE FROM imovel_fotos WHERE id = ?')->execute([$fotoId]);
        if ((int)$foto['principal'] === 1) {
            $prox = db()->prepare('SELECT id FROM imovel_fotos WHERE imovel_id = ? ORDER BY ordem, id LIMIT 1');
            $prox->execute([$id]);
            $pid = $prox->fetchColumn();
            if ($pid) {
                db()->prepare('UPDATE imovel_fotos SET principal = 1 WHERE id = ?')->execute([$pid]);
            }
        }
        flash('success', 'Foto removida.');
    }
    redirect('admin/imovel-fotos.php?id=' . $id);
}

$fotos = db()->prepare('SELECT * FROM imovel_fotos WHERE imovel_id = ? ORDER BY principal DESC, ordem ASC, id ASC');
$fotos->execute([$id]);
$lista = $fotos->fetchAll();

$pageTitle = 'Fotos — ' . $imovel['codigo'];
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<div class="admin-toolbar">
    <h1 style="margin:0">Fotos · <?= e($imovel['codigo']) ?></h1>
    <div>
        <a href="<?= e(url('admin/imovel-form.php?id=' . $id)) ?>">Editar imóvel</a>
        ·
        <a href="<?= e(url('admin/imoveis.php')) ?>">Lista</a>
    </div>
</div>
<p class="note"><?= e($imovel['titulo']) ?></p>

<section class="admin-box" style="max-width:32rem">
    <h2>Enviar foto</h2>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="acao" value="upload">
        <input type="hidden" name="imovel_id" value="<?= $id ?>">
        <label for="foto">Arquivo (JPG, PNG, WEBP ou GIF · máx. 5 MB)</label>
        <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp,image/gif" required>
        <label for="legenda">Legenda (opcional)</label>
        <input type="text" id="legenda" name="legenda" maxlength="160">
        <button type="submit" class="btn">Enviar foto</button>
    </form>
</section>

<section style="margin-top:1.5rem">
    <h2>Fotos (<?= count($lista) ?>)</h2>
    <?php if (empty($lista)): ?>
        <p class="note">Nenhuma foto ainda.</p>
    <?php else: ?>
        <div class="foto-grid">
            <?php foreach ($lista as $f): ?>
                <div class="foto-card">
                    <img src="<?= e(url('uploads/' . $f['arquivo'])) ?>" alt="<?= e($f['legenda'] ?: '') ?>">
                    <?php if ((int)$f['principal']): ?>
                        <span class="badge">Principal</span>
                    <?php endif; ?>
                    <?php if ($f['legenda']): ?>
                        <small><?= e($f['legenda']) ?></small>
                    <?php endif; ?>
                    <div class="foto-actions">
                        <?php if (!(int)$f['principal']): ?>
                        <form method="post" class="inline-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="principal">
                            <input type="hidden" name="foto_id" value="<?= (int)$f['id'] ?>">
                            <button type="submit" class="btn btn-small">Principal</button>
                        </form>
                        <?php endif; ?>
                        <form method="post" class="inline-form" onsubmit="return confirm('Excluir esta foto?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="foto_id" value="<?= (int)$f['id'] ?>">
                            <button type="submit" class="btn btn-small btn-danger">Excluir</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php'; ?>
