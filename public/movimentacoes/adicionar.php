<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = $_POST['descricao'] ?? '';
    $valor = $_POST['valor'] ?? 0;
    $tipo = $_POST['tipo'] ?? '';
    $conta_id = $_POST['conta_id'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? 0;
    $data_movimento = $_POST['data_movimento'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'pago';

    if (empty($descricao) || empty($tipo) || !$conta_id || !$categoria_id) {
        header('Location: /movimentacoes/listar.php?erro=' . urlencode('Preencha todos os campos obrigatórios.'));
        exit;
    }

    pg_query($conexao, "BEGIN");

    // Inserir movimentação
    $query_mov = "INSERT INTO movimentacoes (usuario_id, conta_id, categoria_id, descricao, valor, tipo, data_movimento, status) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
    $result_mov = pg_query_params($conexao, $query_mov, array($usuario_id, $conta_id, $categoria_id, $descricao, $valor, $tipo, $data_movimento, $status));

    if ($result_mov) {
        pg_query($conexao, "COMMIT");
        $timestamp = strtotime($data_movimento);
        $mes = (int)date('m', $timestamp);
        $ano = (int)date('Y', $timestamp);
        $aba = $tipo === 'entrada' ? 'receitas' : 'despesas';
        header("Location: /movimentacoes/listar.php?mes={$mes}&ano={$ano}&aba={$aba}&sucesso=" . urlencode('Transação registrada com sucesso!'));
    } else {
        pg_query($conexao, "ROLLBACK");
        header('Location: /movimentacoes/listar.php?erro=' . urlencode('Erro ao salvar transação.'));
    }
    exit;
} else {
    header('Location: /movimentacoes/listar.php');
    exit;
}
