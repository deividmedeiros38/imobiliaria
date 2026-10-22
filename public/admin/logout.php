<?php
declare(strict_types=1);
require __DIR__ . '/../../includes/bootstrap.php';

// Só sai por POST com token CSRF, para que um link externo não deslogue o administrador.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/dashboard.php');
}
csrf_require();
Auth::logout();
flash('success', 'Você saiu do painel.');
redirect('admin/login.php');
