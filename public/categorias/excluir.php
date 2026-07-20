<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $usuario_id = $_SESSION['usuario_id'];
    
    $query = "DELETE FROM categorias WHERE id = $1 AND usuario_id = $2";
    $result = @pg_query_params($conexao, $query, array($id, $usuario_id));

    if ($result && pg_affected_rows($result) > 0) {
        header('Location: /categorias/listar.php?sucesso=1');
    } else {
        header('Location: /categorias/listar.php?erro=1');
    }
    exit;
}

header('Location: /categorias/listar.php');
exit;
