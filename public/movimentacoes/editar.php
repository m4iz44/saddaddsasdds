<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $descricao = $_POST['descricao'] ?? '';
    $valor = $_POST['valor'] ?? 0;
    $data_movimento = $_POST['data_movimento'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'pago';
    $categoria_id = $_POST['categoria_id'] ?? 0;

    if (!$id || empty($descricao) || !$categoria_id) {
        header('Location: /movimentacoes/listar.php?erro=' . urlencode('Preencha os campos obrigatórios.'));
        exit;
    }

    $q_orig = "SELECT * FROM movimentacoes WHERE id = $1 AND usuario_id = $2";
    $r_orig = pg_query_params($conexao, $q_orig, array($id, $usuario_id));
    $mov = pg_fetch_assoc($r_orig);

    if ($mov) {
        pg_query($conexao, "BEGIN");
        
        $q_up = "UPDATE movimentacoes 
                 SET descricao=$1, valor=$2, data_movimento=$3, status=$4, categoria_id=$5 
                 WHERE id=$6 AND usuario_id=$7";
        $r_up = pg_query_params($conexao, $q_up, array($descricao, $valor, $data_movimento, $status, $categoria_id, $id, $usuario_id));
        
        if ($r_up) {
            pg_query($conexao, "COMMIT");
            $timestamp = strtotime($data_movimento);
            $mes = (int)date('m', $timestamp);
            $ano = (int)date('Y', $timestamp);
            $aba = $mov['tipo'] === 'entrada' ? 'receitas' : 'despesas';
            header("Location: /movimentacoes/listar.php?mes={$mes}&ano={$ano}&aba={$aba}&sucesso=" . urlencode('Transação atualizada com sucesso!'));
            exit;
        } else {
            pg_query($conexao, "ROLLBACK");
        }
    }
    
    header('Location: /movimentacoes/listar.php?erro=' . urlencode('Erro ao atualizar transação.'));
    exit;
} else {
    header('Location: /movimentacoes/listar.php');
    exit;
}
