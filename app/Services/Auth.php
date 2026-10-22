<?php
declare(strict_types=1);

// Autenticação do painel: login, sessão, limite de tentativas e logout.

final class Auth
{
    /** Hash usado para gastar o mesmo tempo quando o e-mail não existe. */
    private const DUMMY_HASH = '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';

    public static function check(): bool
    {
        if (empty($_SESSION['admin_id'])) {
            return false;
        }
        $idle = (int) cfg('session_idle', 7200);
        if (time() - (int) ($_SESSION['last_activity'] ?? 0) > $idle) {
            self::logout();
            return false;
        }
        $_SESSION['last_activity'] = time();
        return true;
    }

    /** Use no topo de toda página do painel. */
    public static function require(): void
    {
        if (!self::check()) {
            flash('error', 'Faça login para acessar o painel.');
            redirect('admin/login.php');
        }
    }

    public static function user(): array
    {
        return ['id' => $_SESSION['admin_id'] ?? null, 'nome' => $_SESSION['admin_nome'] ?? ''];
    }

    public static function tooManyAttempts(string $email): bool
    {
        $max    = (int) cfg('login_max_falhas', 5);
        $janela = (int) cfg('login_janela', 900);
        $st = db()->prepare(
            'SELECT COUNT(*) FROM login_tentativas
              WHERE sucesso = 0 AND criado_em > (NOW() - INTERVAL :j SECOND)
                AND (ip = :ip OR email = :email)'
        );
        $st->execute([':j' => $janela, ':ip' => client_ip(), ':email' => $email]);
        return (int) $st->fetchColumn() >= $max;
    }

    private static function record(string $email, bool $ok): void
    {
        $st = db()->prepare('INSERT INTO login_tentativas (ip, email, sucesso) VALUES (:ip, :email, :ok)');
        $st->execute([':ip' => client_ip(), ':email' => mb_substr($email, 0, 190), ':ok' => $ok ? 1 : 0]);
    }

    public static function attempt(string $email, string $password): bool
    {
        $st = db()->prepare('SELECT id, nome, senha_hash FROM administradores WHERE email = :e AND ativo = 1 LIMIT 1');
        $st->execute([':e' => $email]);
        $admin = $st->fetch();

        // password_verify roda sempre, para não revelar se o e-mail existe pelo tempo de resposta.
        $ok = password_verify($password, $admin['senha_hash'] ?? self::DUMMY_HASH) && $admin !== false;
        self::record($email, $ok);
        if (!$ok) {
            return false;
        }

        session_regenerate_id(true); // evita fixação de sessão
        $_SESSION['admin_id']      = (int) $admin['id'];
        $_SESSION['admin_nome']    = $admin['nome'];
        $_SESSION['last_activity'] = time();
        unset($_SESSION['csrf']);

        if (password_needs_rehash($admin['senha_hash'], PASSWORD_DEFAULT)) {
            $up = db()->prepare('UPDATE administradores SET senha_hash = :h WHERE id = :id');
            $up->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $admin['id']]);
        }
        $up = db()->prepare('UPDATE administradores SET ultimo_login = NOW() WHERE id = :id');
        $up->execute([':id' => $admin['id']]);
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 3600, 'path' => $p['path'],
                'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        session_start(); // sessão nova e limpa, para poder exibir mensagens
        session_regenerate_id(true);
    }
}
