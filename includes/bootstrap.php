<?php
declare(strict_types=1);

// Ponto de entrada comum: configuração, erros, sessão e conexão com o banco.

define('BASE_PATH', dirname(__DIR__));

$appFile = BASE_PATH . '/config/app.php';
$dbFile  = BASE_PATH . '/config/database.php';

if (!is_file($appFile) || !is_file($dbFile)) {
    http_response_code(500);
    exit('Site não configurado. Copie config/app.example.php para config/app.php '
       . 'e config/database.example.php para config/database.php.');
}

$GLOBALS['config'] = require $appFile;
$GLOBALS['dbConfig'] = require $dbFile;

// Erros: detalhados só em ambiente local; em produção vão para o log.
error_reporting(E_ALL);
if (($GLOBALS['config']['env'] ?? 'production') === 'local') {
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}

require_once __DIR__ . '/functions.php';
require_once BASE_PATH . '/app/Services/Auth.php';

if (PHP_SAPI !== 'cli') {
    // Cabeçalhos básicos de segurança
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('imob_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/** Conexão PDO única, com prepared statements reais. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = $GLOBALS['dbConfig'];
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'], $c['port'], $c['name'], $c['charset']);
        try {
            $pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Falha na conexão com o banco: ' . $e->getMessage());
            http_response_code(500);
            exit('Não foi possível conectar ao banco de dados. Tente novamente mais tarde.');
        }
    }
    return $pdo;
}
