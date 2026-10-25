<?php
declare(strict_types=1);

// Funções de apoio: saída segura, CSRF, mensagens, URLs e validação.

function cfg(string $key, $default = null)
{
    return $GLOBALS['config'][$key] ?? $default;
}

/** Escapa texto para HTML (proteção contra XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function url(string $path = ''): string
{
    return rtrim((string) cfg('base_url', ''), '/') . '/' . ltrim($path, '/');
}

/** Redireciona sempre para dentro do site (nunca para endereço vindo do usuário). */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function client_ip(): string
{
    // REMOTE_ADDR não pode ser forjado pelo visitante.
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = (string) ($_POST['csrf'] ?? '');
    return $sent !== '' && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/** Interrompe a requisição se o token CSRF for inválido. */
function csrf_require(): void
{
    if (!csrf_valid()) {
        http_response_code(419);
        exit('Sessão expirada ou requisição inválida. Volte e tente novamente.');
    }
}

// ---------- Mensagens (flash) ----------
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_pull(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

// ---------- Validação ----------
function post_str(string $key, int $max = 255): string
{
    $v = trim((string) ($_POST[$key] ?? ''));
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $v) ?? '';
    return mb_substr($v, 0, $max);
}

function valid_email(string $email): bool
{
    return mb_strlen($email) <= 190 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function slugify(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;
    $text = strtolower(preg_replace('/[^A-Za-z0-9]+/', '-', $text) ?? '');
    return trim($text, '-') ?: 'item';
}

function money(float|string $v): string
{
    return 'R$ ' . number_format((float) $v, 2, ',', '.');
}

/** Configuração do site salva no banco (cache por requisição). */
function site_setting(string $key, string $default = ''): string
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (db()->query('SELECT chave, valor FROM configuracoes') as $row) {
            $all[$row['chave']] = (string) $row['valor'];
        }
    }
    return ($all[$key] ?? '') !== '' ? $all[$key] : $default;
}

/** Retorna a foto principal do imóvel ou null. */
function imovel_foto_principal(int $imovelId): ?array
{
    $st = db()->prepare(
        'SELECT arquivo, legenda FROM imovel_fotos
         WHERE imovel_id = ? ORDER BY principal DESC, ordem ASC, id ASC LIMIT 1'
    );
    $st->execute([$imovelId]);
    $row = $st->fetch();
    return $row ?: null;
}

/** URL pública de uma foto (ou placeholder). */
function foto_url(?string $arquivo): string
{
    if ($arquivo) {
        return url('uploads/' . $arquivo);
    }
    return ''; // o CSS/HTML trata placeholder
}

/** Formata finalidade para exibição. */
function finalidade_label(string $f): string
{
    return $f === 'aluguel' ? 'Aluguel' : 'Venda';
}

/** Converte string de preço BR (1.234,56) para float. */
function parse_money(string $v): float
{
    $v = preg_replace('/[^\d,.\-]/', '', $v) ?? '';
    if (str_contains($v, ',')) {
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
    }
    return (float) $v;
}

/** Garante slug único na tabela imoveis. */
function unique_imovel_slug(string $base, ?int $ignoreId = null): string
{
    $slug = slugify($base);
    $n = 0;
    while (true) {
        $try = $n === 0 ? $slug : $slug . '-' . $n;
        $sql = 'SELECT id FROM imoveis WHERE slug = ?';
        $params = [$try];
        if ($ignoreId) {
            $sql .= ' AND id != ?';
            $params[] = $ignoreId;
        }
        $st = db()->prepare($sql . ' LIMIT 1');
        $st->execute($params);
        if (!$st->fetch()) {
            return $try;
        }
        $n++;
        if ($n > 50) {
            return $slug . '-' . bin2hex(random_bytes(3));
        }
    }
}

/** Garante código único. */
function unique_imovel_codigo(string $codigo, ?int $ignoreId = null): string
{
    $codigo = strtoupper(preg_replace('/[^A-Za-z0-9\-]/', '', $codigo) ?? '');
    if ($codigo === '') {
        $codigo = 'IMO-' . strtoupper(bin2hex(random_bytes(3)));
    }
    $sql = 'SELECT id FROM imoveis WHERE codigo = ?';
    $params = [$codigo];
    if ($ignoreId) {
        $sql .= ' AND id != ?';
        $params[] = $ignoreId;
    }
    $st = db()->prepare($sql . ' LIMIT 1');
    $st->execute($params);
    if ($st->fetch()) {
        return unique_imovel_codigo($codigo . '-' . strtoupper(bin2hex(random_bytes(2))), $ignoreId);
    }
    return $codigo;
}

/**
 * Salva upload de imagem em public/uploads/.
 * Retorna nome do arquivo ou null em caso de erro (mensagem em $error).
 */
function save_upload_image(array $file, ?string &$error = null): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $error = 'Nenhum arquivo enviado.';
        return null;
    }
    if (($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
        $error = 'Falha no upload (código ' . (int)$file['error'] . ').';
        return null;
    }
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'Arquivo maior que 5 MB.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($map[$mime])) {
        $error = 'Formato não permitido. Use JPG, PNG, WEBP ou GIF.';
        return null;
    }

    $dir = BASE_PATH . '/public/uploads';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        $error = 'Não foi possível criar a pasta de uploads.';
        return null;
    }

    $nome = bin2hex(random_bytes(16)) . '.' . $map[$mime];
    $dest = $dir . '/' . $nome;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $error = 'Falha ao salvar o arquivo.';
        return null;
    }
    return $nome;
}

function delete_upload_file(?string $arquivo): void
{
    if (!$arquivo) {
        return;
    }
    $arquivo = basename($arquivo);
    $path = BASE_PATH . '/public/uploads/' . $arquivo;
    if (is_file($path)) {
        @unlink($path);
    }
}

// ---------- Apoio ao site público ----------

/** "1 quarto" / "3 quartos": evita textos como "1 vagas". */
function plural(int $n, string $singular, string $plural): string
{
    return $n . ' ' . ($n === 1 ? $singular : $plural);
}

/** Número do WhatsApp só com dígitos (vazio se não houver nenhum configurado). */
function whatsapp_number(): string
{
    return preg_replace('/\D+/', '', site_setting('whatsapp', site_setting('telefone', ''))) ?? '';
}

/** Link wa.me com mensagem opcional. Retorna '' se o WhatsApp não estiver configurado. */
function whatsapp_url(string $mensagem = ''): string
{
    $numero = whatsapp_number();
    if ($numero === '') {
        return '';
    }
    $url = 'https://wa.me/55' . $numero;
    return $mensagem === '' ? $url : $url . '?text=' . rawurlencode($mensagem);
}

/** Área de exibição do imóvel: construída, ou do terreno quando não houver. */
function imovel_area(array $imovel): ?float
{
    $area = (float) ($imovel['area_construida'] ?: $imovel['area_terreno']);
    return $area > 0 ? $area : null;
}

/**
 * Imóveis ativos para as vitrines da home.
 * $finalidade: 'venda', 'aluguel' ou null (todas). $soDestaque: só os marcados como destaque.
 */
function imoveis_para_home(?string $finalidade = null, bool $soDestaque = false, int $limite = 3): array
{
    $sql = 'SELECT i.*, c.nome AS categoria
              FROM imoveis i
              JOIN categorias c ON c.id = i.categoria_id
             WHERE i.ativo = 1';
    $params = [];
    if ($finalidade !== null) {
        $sql .= ' AND i.finalidade = ?';
        $params[] = $finalidade;
    }
    if ($soDestaque) {
        $sql .= ' AND i.destaque = 1';
    }
    $sql .= ' ORDER BY i.destaque DESC, i.atualizado_em DESC LIMIT ' . max(1, $limite);

    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

