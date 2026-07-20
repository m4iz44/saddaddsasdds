<?php
// ============================================
// FINANCINHAS - FUNÇÕES AUXILIARES
// ============================================

function estaLogado() {
    return isset($_SESSION['usuario_id']);
}

function exigeLogin() {
    if (!estaLogado()) {
        header('Location: login.php');
        exit;
    }
}

function getSaldoTotal($usuario_id, $conexao) {
    $query = "SELECT COALESCE(SUM(saldo), 0) as total FROM contas WHERE usuario_id = $1";
    $result = pg_query_params($conexao, $query, array($usuario_id));
    if ($result) {
        $row = pg_fetch_assoc($result);
        return (float) $row['total'];
    }
    return 0;
}

function getContas($usuario_id, $conexao) {
    $query = "SELECT * FROM contas WHERE usuario_id = $1 ORDER BY id";
    $result = pg_query_params($conexao, $query, array($usuario_id));
    return $result ? pg_fetch_all($result) ?: [] : [];
}

function getMovimentacoes($usuario_id, $conexao, $limite = 100) {
    $query = "SELECT m.*, c.nome as conta_nome, cat.nome as categoria_nome 
              FROM movimentacoes m
              LEFT JOIN contas c ON m.conta_id = c.id
              LEFT JOIN categorias cat ON m.categoria_id = cat.id
              WHERE m.usuario_id = $1
              ORDER BY m.data_movimento DESC
              LIMIT $2";
    $result = pg_query_params($conexao, $query, array($usuario_id, $limite));
    return $result ? pg_fetch_all($result) ?: [] : [];
}

function formataReal($valor) {
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function protege($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
