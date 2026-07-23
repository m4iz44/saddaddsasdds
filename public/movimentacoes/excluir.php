<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$id = $_GET['id'] ?? 0;

$redirect_url = '/movimentacoes/listar.php?sucesso=' . urlencode('Transação excluída com sucesso!');

if ($id) {
    $q_orig = "SELECT * FROM movimentacoes WHERE id = $1 AND usuario_id = $2";
    $r_orig = pg_query_params($conexao, $q_orig, array($id, $usuario_id));
    $mov = pg_fetch_assoc($r_orig);
    
    if ($mov) {
        $timestamp = strtotime($mov['data_movimento']);
        $mes = (int)date('m', $timestamp);
        $ano = (int)date('Y', $timestamp);
        $aba = $mov['tipo'] === 'entrada' ? 'receitas' : 'despesas';
        $redirect_url = "/movimentacoes/listar.php?mes={$mes}&ano={$ano}&aba={$aba}&sucesso=" . urlencode('Transação excluída com sucesso!');
        
        $q = "DELETE FROM movimentacoes WHERE id=$1 AND usuario_id=$2";
        pg_query_params($conexao, $q, array($id, $usuario_id));
    }
}

header("Location: {$redirect_url}");
exit;
