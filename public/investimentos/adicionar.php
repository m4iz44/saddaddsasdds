<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'] ?? 'caixinha';
    $nome = $_POST['nome'] ?? '';
    $valor_aplicado = (float)($_POST['valor_aplicado'] ?? 0);
    $valor_atual = !empty($_POST['valor_atual']) ? (float)$_POST['valor_atual'] : $valor_aplicado;

    if (empty($nome)) {
        header('Location: /investimentos/listar.php?erro=Nome obrigatorio');
        exit;
    }

    $q = "INSERT INTO investimentos (usuario_id, tipo, nome, valor_aplicado, valor_atual) VALUES ($1, $2, $3, $4, $5)";
    pg_query_params($conexao, $q, array($usuario_id, $tipo, $nome, $valor_aplicado, $valor_atual));
    
    header('Location: /investimentos/listar.php?sucesso=1');
    exit;
} else {
    header('Location: /investimentos/listar.php');
    exit;
}
