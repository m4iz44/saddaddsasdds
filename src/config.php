<?php
// Carregar variáveis de ambiente (simplificado)
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $env = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($env as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

$nome_site = getenv('SITE_NOME') ?: 'Financinhas';

// Configurações do banco
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'financinhas');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');

// Iniciar sessão
session_start();
?>
