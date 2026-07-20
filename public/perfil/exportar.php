<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

// Busca todas as movimentações do usuário
$q_mov = "SELECT m.data_movimento, c.nome as categoria, m.descricao, m.tipo, m.valor, m.status 
          FROM movimentacoes m 
          LEFT JOIN categorias c ON m.categoria_id = c.id 
          WHERE m.usuario_id = $1 
          ORDER BY m.data_movimento DESC";
$r_mov = pg_query_params($conexao, $q_mov, array($usuario_id));

// Define headers para download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=extrato_financas.csv');

// Cria o ponteiro para a saida do arquivo
$output = fopen('php://output', 'w');

// Escreve o BOM do UTF-8 para o Excel reconhecer os acentos
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos das colunas
fputcsv($output, array('Data', 'Categoria', 'Descricao', 'Tipo', 'Valor', 'Status'), ';');

if ($r_mov) {
    while ($row = pg_fetch_assoc($r_mov)) {
        // Formatar valor para o padrão BR Excel se precisar
        $valor = number_format($row['valor'], 2, ',', '');
        fputcsv($output, array(
            date('d/m/Y', strtotime($row['data_movimento'])),
            $row['categoria'],
            $row['descricao'],
            ucfirst($row['tipo']),
            $valor,
            ucfirst($row['status'])
        ), ';');
    }
}

fclose($output);
exit;
