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

function getContasComSaldoAtual($usuario_id, $conexao) {
    $query = "SELECT c.*, 
              COALESCE(SUM(CASE WHEN m.tipo = 'entrada' AND m.status = 'pago' THEN m.valor ELSE 0 END), 0) as total_entradas,
              COALESCE(SUM(CASE WHEN m.tipo = 'saida' AND m.status = 'pago' THEN m.valor ELSE 0 END), 0) as total_saidas
              FROM contas c
              LEFT JOIN movimentacoes m ON m.conta_id = c.id
              WHERE c.usuario_id = $1
              GROUP BY c.id
              ORDER BY c.id";
    $result = pg_query_params($conexao, $query, array($usuario_id));
    $contas = $result ? pg_fetch_all($result) : [];
    if (!is_array($contas)) return [];

    $contas_processadas = [];
    foreach ($contas as $cta) {
        $cta['saldo_inicial'] = (float)$cta['saldo'];
        $cta['saldo_atual'] = (float)$cta['saldo'] + (float)$cta['total_entradas'] - (float)$cta['total_saidas'];
        $contas_processadas[] = $cta;
    }
    return $contas_processadas;
}

function criaCategoriasPadrao($usuario_id, $conexao) {
    $cats = [
        ['Salário', 'entrada'],
        ['Outras Receitas', 'entrada'],
        ['Alimentação', 'saida'],
        ['Moradia', 'saida'],
        ['Transporte', 'saida'],
        ['Lazer', 'saida'],
        ['Outras Despesas', 'saida']
    ];
    foreach ($cats as $cat) {
        $q_cat = "INSERT INTO categorias (usuario_id, nome, tipo) VALUES ($1, $2, $3)";
        pg_query_params($conexao, $q_cat, array($usuario_id, $cat[0], $cat[1]));
    }
}

function getCategoriasPorTipo($usuario_id, $tipo, $conexao) {
    $query = "SELECT * FROM categorias WHERE usuario_id = $1 AND tipo = $2 ORDER BY nome ASC";
    $result = pg_query_params($conexao, $query, array($usuario_id, $tipo));
    $cats = $result ? pg_fetch_all($result) : [];
    if (!is_array($cats)) {
        $cats = [];
    }
    if (empty($cats)) {
        $check_any = pg_query_params($conexao, "SELECT id FROM categorias WHERE usuario_id = $1 LIMIT 1", array($usuario_id));
        if (!$check_any || pg_num_rows($check_any) === 0) {
            criaCategoriasPadrao($usuario_id, $conexao);
            $result = pg_query_params($conexao, $query, array($usuario_id, $tipo));
            $cats = $result ? pg_fetch_all($result) : [];
            if (!is_array($cats)) {
                $cats = [];
            }
        }
    }
    return $cats;
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
