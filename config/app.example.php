<?php
// Copie este arquivo para config/app.php.
return [
    // 'local' mostra erros detalhados; 'production' esconde e grava em log.
    'env'      => 'local',
    // Endereço do site SEM barra no final.
    // XAMPP: http://localhost/imobiliaria/public   | Produção: https://seusite.com.br
    'base_url' => 'http://localhost/imobiliaria/public',
    // Tempo máximo sem atividade no painel (segundos).
    'session_idle' => 7200,
    // Login: máximo de falhas dentro da janela, e tamanho da janela (segundos).
    'login_max_falhas' => 5,
    'login_janela'     => 900,
];
