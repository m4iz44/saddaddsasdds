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
        header('Location: /movimentacoes/listar.php?erro=Campos invalidos');
        exit;
    }

    // Busca a original para reverter saldo se necessario (simplificado)
    $q_orig = "SELECT * FROM movimentacoes WHERE id = $1 AND usuario_id = $2";
    $r_orig = pg_query_params($conexao, $q_orig, array($id, $usuario_id));
    $mov = pg_fetch_assoc($r_orig);

    if ($mov) {
        pg_query($conexao, "BEGIN");
        
        $q_up = "UPDATE movimentacoes 
                 SET descricao=$1, valor=$2, data_movimento=$3, status=$4, categoria_id=$5 
                 WHERE id=$6 AND usuario_id=$7";
        $r_up = pg_query_params($conexao, $q_up, array($descricao, $valor, $data_movimento, $status, $categoria_id, $id, $usuario_id));
        
        // Reversão de saldo nas contas (caso tenha mudado de valor/status). 
        // Para um MVP sem JS, o saldo dinâmico no Dashboard não precisa disso, 
        // mas para manter compatibilidade com "conta.saldo", teríamos que desfazer a transação velha e refazer.
        // Como o foco agora é a Dashboard dinâmica, a alteração no `movimentacoes` já afeta a Dashboard.
        if ($r_up) {
            pg_query($conexao, "COMMIT");
        } else {
            pg_query($conexao, "ROLLBACK");
        }
    }
    
    header('Location: /movimentacoes/listar.php?sucesso=1');
    exit;
} else {
    header('Location: /movimentacoes/listar.php');
    exit;
}
