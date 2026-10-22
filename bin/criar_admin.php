<?php
declare(strict_types=1);

// Cria (ou redefine a senha de) um administrador. Só roda pelo terminal.
// Uso: php bin/criar_admin.php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acesso negado.');
}

require __DIR__ . '/../includes/bootstrap.php';

function ask(string $label, bool $hidden = false): string
{
    echo $label;
    if ($hidden && stripos(PHP_OS, 'WIN') !== 0) {
        system('stty -echo');
    }
    $v = trim((string) fgets(STDIN));
    if ($hidden && stripos(PHP_OS, 'WIN') !== 0) {
        system('stty echo');
        echo PHP_EOL;
    }
    return $v;
}

$nome  = ask('Nome: ');
$email = mb_strtolower(ask('E-mail: '));
$senha = ask('Senha (mínimo 10 caracteres): ', true);
$conf  = ask('Repita a senha: ', true);

if ($nome === '' || !valid_email($email)) {
    exit("Nome ou e-mail inválido.\n");
}
if (mb_strlen($senha) < 10 || $senha !== $conf) {
    exit("Senha curta demais (mínimo 10) ou as senhas não conferem.\n");
}

$st = db()->prepare(
    'INSERT INTO administradores (nome, email, senha_hash) VALUES (:n, :e, :h)
     ON DUPLICATE KEY UPDATE nome = VALUES(nome), senha_hash = VALUES(senha_hash), ativo = 1'
);
$st->execute([':n' => $nome, ':e' => $email, ':h' => password_hash($senha, PASSWORD_DEFAULT)]);
echo "Administrador salvo: $email\n";
