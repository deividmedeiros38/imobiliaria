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
