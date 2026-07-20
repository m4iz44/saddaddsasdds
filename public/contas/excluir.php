<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$conta_id = $_GET['id'] ?? 0;

if ($conta_id) {
    // Verifica se a conta pertence ao usuário antes de excluir
    $query = "DELETE FROM contas WHERE id = $1 AND usuario_id = $2";
    pg_query_params($conexao, $query, array($conta_id, $usuario_id));
}

header('Location: /contas/listar.php?sucesso=1');
exit;
?>
