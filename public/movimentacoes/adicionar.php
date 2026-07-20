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
        header('Location: /movimentacoes/listar.php?erro=Preencha todos os campos');
        exit;
    }

    pg_query($conexao, "BEGIN");

    // 1. Inserir movimentação com status
    $query_mov = "INSERT INTO movimentacoes (usuario_id, conta_id, categoria_id, descricao, valor, tipo, data_movimento, status) 
                  VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
    $result_mov = pg_query_params($conexao, $query_mov, array($usuario_id, $conta_id, $categoria_id, $descricao, $valor, $tipo, $data_movimento, $status));

    // 2. O saldo da conta será atualizado na "Dashboard" virtualmente baseada nas transações pagas.
    // O projeto original modificava a tabela `contas`. Se mantivermos isso, só alteramos se estiver pago.
    // Como a nova especificação usa "Saldo Projetado (considera datas)" somando movimentações on the fly na dashboard,
    // o campo "saldo" da tabela contas passa a representar o saldo inicial. 
    // Portanto, é melhor deixarmos o `saldo` em paz e usarmos apenas a soma das transações na Dashboard.
    // Mas, para não quebrar outras partes, vamos continuar atualizando se estiver "pago".
    $result_saldo = true;
    if ($status === 'pago') {
        $operador = $tipo === 'entrada' ? '+' : '-';
        $query_saldo = "UPDATE contas SET saldo = saldo $operador $1 WHERE id = $2 AND usuario_id = $3";
        $result_saldo = pg_query_params($conexao, $query_saldo, array($valor, $conta_id, $usuario_id));
    }

    if ($result_mov && $result_saldo) {
        pg_query($conexao, "COMMIT");
        header('Location: /movimentacoes/listar.php?sucesso=1');
    } else {
        pg_query($conexao, "ROLLBACK");
        header('Location: /movimentacoes/listar.php?erro=Erro ao salvar');
    }
    exit;
} else {
    header('Location: /movimentacoes/listar.php');
    exit;
}
