<?php
require_once __DIR__ . '/config.php';

$connection_string = "host=" . DB_HOST . " port=" . DB_PORT . " dbname=" . DB_NAME . " user=" . DB_USER . " password=" . DB_PASS;
$conexao = pg_connect($connection_string);

if (!$conexao) {
    die("Erro ao conectar ao banco de dados PostgreSQL.");
}
?>
