<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? 0;

if ($id) {
    // Delete
    $q = "DELETE FROM movimentacoes WHERE id=$1 AND usuario_id=$2";
    pg_query_params($conexao, $q, array($id, $usuario_id));
}

header('Location: /movimentacoes/listar.php?sucesso=1');
exit;
