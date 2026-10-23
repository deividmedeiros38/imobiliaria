<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';
Auth::require();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$imovel = null;
if ($id > 0) {
    $st = db()->prepare('SELECT * FROM imoveis WHERE id = ?');
    $st->execute([$id]);
    $imovel = $st->fetch();
    if (!$imovel) {
        flash('error', 'Imóvel não encontrado.');
        redirect('admin/imoveis.php');
    }
}

$categorias = db()->query('SELECT id, nome FROM categorias WHERE ativo = 1 ORDER BY nome')->fetchAll();
$todasCaract = db()->query('SELECT id, nome FROM caracteristicas ORDER BY nome')->fetchAll();

$caractSelecionadas = [];
if ($imovel) {
    $st = db()->prepare('SELECT caracteristica_id FROM imovel_caracteristicas WHERE imovel_id = ?');
    $st->execute([$id]);
    $caractSelecionadas = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
}

$erro = '';
$dados = [
    'codigo' => $imovel['codigo'] ?? '',
    'titulo' => $imovel['titulo'] ?? '',
    'descricao' => $imovel['descricao'] ?? '',
    'finalidade' => $imovel['finalidade'] ?? 'venda',
    'categoria_id' => (int)($imovel['categoria_id'] ?? 0),
    'preco' => $imovel ? number_format((float)$imovel['preco'], 2, ',', '.') : '',
    'cidade' => $imovel['cidade'] ?? 'Juazeiro do Norte',
    'bairro' => $imovel['bairro'] ?? '',
    'quartos' => (int)($imovel['quartos'] ?? 0),
    'suites' => (int)($imovel['suites'] ?? 0),
    'banheiros' => (int)($imovel['banheiros'] ?? 0),
    'vagas' => (int)($imovel['vagas'] ?? 0),
    'area_construida' => $imovel['area_construida'] !== null && $imovel['area_construida'] !== ''
        ? number_format((float)$imovel['area_construida'], 2, ',', '.') : '',
    'area_terreno' => $imovel['area_terreno'] !== null && $imovel['area_terreno'] !== ''
        ? number_format((float)$imovel['area_terreno'], 2, ',', '.') : '',
    'ativo' => $imovel ? (int)$imovel['ativo'] : 1,
    'destaque' => $imovel ? (int)$imovel['destaque'] : 0,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();

    $dados['codigo'] = post_str('codigo', 20);
    $dados['titulo'] = post_str('titulo', 160);
    $dados['descricao'] = post_str('descricao', 20000);
    $dados['finalidade'] = in_array($_POST['finalidade'] ?? '', ['venda', 'aluguel'], true) ? $_POST['finalidade'] : 'venda';
    $dados['categoria_id'] = (int) ($_POST['categoria_id'] ?? 0);
    $dados['preco'] = post_str('preco', 30);
    $dados['cidade'] = post_str('cidade', 80);
    $dados['bairro'] = post_str('bairro', 80);
    $dados['quartos'] = max(0, min(99, (int)($_POST['quartos'] ?? 0)));
    $dados['suites'] = max(0, min(99, (int)($_POST['suites'] ?? 0)));
    $dados['banheiros'] = max(0, min(99, (int)($_POST['banheiros'] ?? 0)));
    $dados['vagas'] = max(0, min(99, (int)($_POST['vagas'] ?? 0)));
    $dados['area_construida'] = post_str('area_construida', 20);
    $dados['area_terreno'] = post_str('area_terreno', 20);
    $dados['ativo'] = !empty($_POST['ativo']) ? 1 : 0;
    $dados['destaque'] = !empty($_POST['destaque']) ? 1 : 0;
    $caractSelecionadas = array_map('intval', $_POST['caracteristicas'] ?? []);

    $precoNum = parse_money($dados['preco']);
    $areaC = $dados['area_construida'] !== '' ? parse_money($dados['area_construida']) : null;
    $areaT = $dados['area_terreno'] !== '' ? parse_money($dados['area_terreno']) : null;

    if ($dados['titulo'] === '' || mb_strlen($dados['titulo']) < 5) {
        $erro = 'Informe um título (mín. 5 caracteres).';
    } elseif ($dados['descricao'] === '' || mb_strlen($dados['descricao']) < 10) {
        $erro = 'Informe a descrição (mín. 10 caracteres).';
    } elseif ($dados['categoria_id'] <= 0) {
        $erro = 'Selecione a categoria.';
    } elseif ($precoNum <= 0) {
        $erro = 'Informe um preço válido.';
    } elseif ($dados['cidade'] === '' || $dados['bairro'] === '') {
        $erro = 'Informe cidade e bairro.';
    } else {
        $codigo = unique_imovel_codigo($dados['codigo'] !== '' ? $dados['codigo'] : 'IMO', $id ?: null);
        $slug = unique_imovel_slug($dados['titulo'], $id ?: null);

        if ($id > 0) {
            $sql = 'UPDATE imoveis SET
                codigo=?, slug=?, titulo=?, descricao=?, finalidade=?, categoria_id=?,
                preco=?, cidade=?, bairro=?, quartos=?, suites=?, banheiros=?, vagas=?,
                area_construida=?, area_terreno=?, ativo=?, destaque=?, demo=0
                WHERE id=?';
            db()->prepare($sql)->execute([
                $codigo, $slug, $dados['titulo'], $dados['descricao'], $dados['finalidade'], $dados['categoria_id'],
                $precoNum, $dados['cidade'], $dados['bairro'], $dados['quartos'], $dados['suites'],
                $dados['banheiros'], $dados['vagas'], $areaC, $areaT, $dados['ativo'], $dados['destaque'], $id,
            ]);
            db()->prepare('DELETE FROM imovel_caracteristicas WHERE imovel_id = ?')->execute([$id]);
            $imovelId = $id;
            flash('success', 'Imóvel atualizado.');
        } else {
            $sql = 'INSERT INTO imoveis
                (codigo, slug, titulo, descricao, finalidade, categoria_id, preco, cidade, bairro,
                 quartos, suites, banheiros, vagas, area_construida, area_terreno, ativo, destaque, demo)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)';
            db()->prepare($sql)->execute([
                $codigo, $slug, $dados['titulo'], $dados['descricao'], $dados['finalidade'], $dados['categoria_id'],
                $precoNum, $dados['cidade'], $dados['bairro'], $dados['quartos'], $dados['suites'],
                $dados['banheiros'], $dados['vagas'], $areaC, $areaT, $dados['ativo'], $dados['destaque'],
            ]);
            $imovelId = (int) db()->lastInsertId();
            flash('success', 'Imóvel cadastrado. Agora você pode adicionar fotos.');
        }

        if (!empty($caractSelecionadas)) {
            $ins = db()->prepare('INSERT IGNORE INTO imovel_caracteristicas (imovel_id, caracteristica_id) VALUES (?, ?)');
            foreach ($caractSelecionadas as $cid) {
                if ($cid > 0) {
                    $ins->execute([$imovelId, $cid]);
                }
            }
        }

        if ($id > 0) {
            redirect('admin/imoveis.php');
        } else {
            redirect('admin/imovel-fotos.php?id=' . $imovelId);
        }
    }
}

$pageTitle = $id ? 'Editar imóvel' : 'Novo imóvel';
require BASE_PATH . '/app/Views/admin/layout_top.php';
?>
<div class="admin-toolbar">
    <h1 style="margin:0"><?= e($pageTitle) ?></h1>
    <a href="<?= e(url('admin/imoveis.php')) ?>">← Voltar</a>
</div>

<?php if ($erro): ?>
    <p class="alert alert-error" role="alert"><?= e($erro) ?></p>
<?php endif; ?>

<form method="post" class="admin-form">
    <?= csrf_field() ?>
    <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

    <div class="form-grid">
        <div>
            <label for="titulo">Título *</label>
            <input type="text" id="titulo" name="titulo" required maxlength="160" value="<?= e($dados['titulo']) ?>">
        </div>
        <div>
            <label for="codigo">Código / Ref.</label>
            <input type="text" id="codigo" name="codigo" maxlength="20" value="<?= e($dados['codigo']) ?>" placeholder="Automático se vazio">
        </div>
        <div>
            <label for="finalidade">Finalidade *</label>
            <select id="finalidade" name="finalidade">
                <option value="venda" <?= $dados['finalidade'] === 'venda' ? 'selected' : '' ?>>Venda</option>
                <option value="aluguel" <?= $dados['finalidade'] === 'aluguel' ? 'selected' : '' ?>>Aluguel</option>
            </select>
        </div>
        <div>
            <label for="categoria_id">Categoria *</label>
            <select id="categoria_id" name="categoria_id" required>
                <option value="">Selecione…</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= $dados['categoria_id'] === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="preco">Preço (R$) *</label>
            <input type="text" id="preco" name="preco" required value="<?= e($dados['preco']) ?>" placeholder="350.000,00">
        </div>
        <div>
            <label for="cidade">Cidade *</label>
            <input type="text" id="cidade" name="cidade" required maxlength="80" value="<?= e($dados['cidade']) ?>">
        </div>
        <div>
            <label for="bairro">Bairro *</label>
            <input type="text" id="bairro" name="bairro" required maxlength="80" value="<?= e($dados['bairro']) ?>">
        </div>
        <div class="form-row-4">
            <div>
                <label for="quartos">Quartos</label>
                <input type="number" id="quartos" name="quartos" min="0" max="99" value="<?= (int)$dados['quartos'] ?>">
            </div>
            <div>
                <label for="suites">Suítes</label>
                <input type="number" id="suites" name="suites" min="0" max="99" value="<?= (int)$dados['suites'] ?>">
            </div>
            <div>
                <label for="banheiros">Banheiros</label>
                <input type="number" id="banheiros" name="banheiros" min="0" max="99" value="<?= (int)$dados['banheiros'] ?>">
            </div>
            <div>
                <label for="vagas">Vagas</label>
                <input type="number" id="vagas" name="vagas" min="0" max="99" value="<?= (int)$dados['vagas'] ?>">
            </div>
        </div>
        <div>
            <label for="area_construida">Área construída (m²)</label>
            <input type="text" id="area_construida" name="area_construida" value="<?= e($dados['area_construida']) ?>">
        </div>
        <div>
            <label for="area_terreno">Área do terreno (m²)</label>
            <input type="text" id="area_terreno" name="area_terreno" value="<?= e($dados['area_terreno']) ?>">
        </div>
    </div>

    <label for="descricao">Descrição *</label>
    <textarea id="descricao" name="descricao" required rows="8"><?= e($dados['descricao']) ?></textarea>

    <fieldset class="checks">
        <legend>Características</legend>
        <div class="check-grid">
            <?php foreach ($todasCaract as $c): ?>
                <label class="check-item">
                    <input type="checkbox" name="caracteristicas[]" value="<?= (int)$c['id'] ?>"
                        <?= in_array((int)$c['id'], $caractSelecionadas, true) ? 'checked' : '' ?>>
                    <?= e($c['nome']) ?>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="check-row">
        <label class="check-item">
            <input type="checkbox" name="ativo" value="1" <?= $dados['ativo'] ? 'checked' : '' ?>> Ativo no site
        </label>
        <label class="check-item">
            <input type="checkbox" name="destaque" value="1" <?= $dados['destaque'] ? 'checked' : '' ?>> Destaque na home
        </label>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn"><?= $id ? 'Salvar alterações' : 'Cadastrar e ir para fotos' ?></button>
        <?php if ($id): ?>
            <a class="btn btn-secondary" href="<?= e(url('admin/imovel-fotos.php?id=' . $id)) ?>">Gerenciar fotos</a>
        <?php endif; ?>
        <a href="<?= e(url('admin/imoveis.php')) ?>">Cancelar</a>
    </div>
</form>
<?php require BASE_PATH . '/app/Views/admin/layout_bottom.php'; ?>
