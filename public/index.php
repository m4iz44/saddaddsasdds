<?php
require_once '../src/config.php';
require_once '../src/conecta.php';
require_once '../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

// Definição do "Mês Ativo"
$mes_atual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano_atual = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');

// Cálculo de datas
$primeiro_dia = sprintf('%04d-%02d-01', $ano_atual, $mes_atual);
$ultimo_dia = date('Y-m-t', strtotime($primeiro_dia));
$hoje = date('Y-m-d');

// Saldo Inicial de todas as contas (Saldo cadastrado na criação da conta)
$q_saldo_inicial = "SELECT COALESCE(SUM(saldo), 0) as total FROM contas WHERE usuario_id = $1";
$r_saldo_inicial = pg_query_params($conexao, $q_saldo_inicial, array($usuario_id));
$saldo_inicial_contas = (float) pg_fetch_assoc($r_saldo_inicial)['total'];

// Receitas e Despesas ATÉ hoje (para o Saldo Atual)
// Inclui tudo de meses anteriores + o que já passou do mês atual, apenas se estiver "pago"
$q_saldo_hoje = "SELECT 
    COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END), 0) as entradas,
    COALESCE(SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END), 0) as saidas
    FROM movimentacoes 
    WHERE usuario_id = $1 AND data_movimento <= $2 AND status = 'pago'";
$r_saldo_hoje = pg_query_params($conexao, $q_saldo_hoje, array($usuario_id, $hoje));
$row_saldo_hoje = pg_fetch_assoc($r_saldo_hoje);
$saldo_hoje = $saldo_inicial_contas + $row_saldo_hoje['entradas'] - $row_saldo_hoje['saidas'];

// Receitas e Despesas do Mês Ativo (Visão Geral do Mês)
// Aqui incluímos pagas e pendentes que ocorrem no mês escolhido
$q_mes = "SELECT 
    COALESCE(SUM(CASE WHEN tipo = 'entrada' THEN valor ELSE 0 END), 0) as receitas,
    COALESCE(SUM(CASE WHEN tipo = 'saida' THEN valor ELSE 0 END), 0) as despesas
    FROM movimentacoes 
    WHERE usuario_id = $1 AND data_movimento BETWEEN $2 AND $3";
$r_mes = pg_query_params($conexao, $q_mes, array($usuario_id, $primeiro_dia, $ultimo_dia));
$row_mes = pg_fetch_assoc($r_mes);
$receitas_mes = (float)$row_mes['receitas'];
$despesas_mes = (float)$row_mes['despesas'];
$saldo_projetado_mes = $receitas_mes - $despesas_mes; 

// Próximas movimentações (3 próximas entradas e saídas do mês)
$q_prox_entradas = "SELECT m.*, c.nome as categoria_nome FROM movimentacoes m LEFT JOIN categorias c ON m.categoria_id = c.id WHERE m.usuario_id = $1 AND m.tipo = 'entrada' AND m.data_movimento >= $2 ORDER BY m.data_movimento ASC LIMIT 3";
$r_prox_entradas = pg_query_params($conexao, $q_prox_entradas, array($usuario_id, $hoje));
$prox_entradas = $r_prox_entradas ? pg_fetch_all($r_prox_entradas) : [];

$q_prox_saidas = "SELECT m.*, c.nome as categoria_nome FROM movimentacoes m LEFT JOIN categorias c ON m.categoria_id = c.id WHERE m.usuario_id = $1 AND m.tipo = 'saida' AND m.data_movimento >= $2 ORDER BY m.data_movimento ASC LIMIT 3";
$r_prox_saidas = pg_query_params($conexao, $q_prox_saidas, array($usuario_id, $hoje));
$prox_saidas = $r_prox_saidas ? pg_fetch_all($r_prox_saidas) : [];

// Mês anterior e próximo para navegação
$mes_ant = $mes_atual - 1;
$ano_ant = $ano_atual;
if ($mes_ant < 1) { $mes_ant = 12; $ano_ant--; }

$mes_prox = $mes_atual + 1;
$ano_prox = $ano_atual;
if ($mes_prox > 12) { $mes_prox = 1; $ano_prox++; }

$meses = ["", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

require_once '../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91 2.95.73 4.18 1.9 4.18 3.91-.01 1.83-1.38 2.83-3.12 3.16z"/></svg>
        Financinhas
    </h1>
    <a href="/logout.php">
        <svg style="width:24px; fill:var(--danger);" viewBox="0 0 24 24"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
    </a>
</div>

<div class="content-area">

    <!-- Navegação de Mês -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <a href="?mes=<?php echo $mes_ant; ?>&ano=<?php echo $ano_ant; ?>" style="background:var(--surface); padding:8px; border-radius:10px; box-shadow:var(--shadow-sm);"><svg style="width:20px;fill:var(--text-main);" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg></a>
        <strong style="font-size:18px; color:var(--text-main);"><?php echo $meses[$mes_atual] . ' / ' . $ano_atual; ?></strong>
        <a href="?mes=<?php echo $mes_prox; ?>&ano=<?php echo $ano_prox; ?>" style="background:var(--surface); padding:8px; border-radius:10px; box-shadow:var(--shadow-sm);"><svg style="width:20px;fill:var(--text-main);" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg></a>
    </div>

    <!-- Saldo de Hoje -->
    <div class="card balance-card" style="text-align:center;">
        <div class="card-header">Saldo de Hoje (Em conta)</div>
        <div class="card-value"><?php echo formataReal($saldo_hoje); ?></div>
        <div style="font-size:12px; margin-top:5px; opacity:0.8;">Atualizado até <?php echo date('d/m/Y'); ?></div>
    </div>

    <!-- Resumo do Mês -->
    <div class="dashboard-grid">
        <div class="card" style="text-align:center; padding:15px;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:700;">Receitas (Mês)</div>
            <div style="color:var(--success); font-weight:800; font-size:18px;"><?php echo formataReal($receitas_mes); ?></div>
        </div>
        <div class="card" style="margin-bottom:0; text-align:center; padding:15px;">
            <div style="font-size:12px; color:var(--text-muted); font-weight:700;">Despesas (Mês)</div>
            <div style="color:var(--danger); font-weight:800; font-size:18px;"><?php echo formataReal($despesas_mes); ?></div>
        </div>
    </div>

    <div class="card" style="text-align:center; padding:15px;">
        <div style="font-size:12px; color:var(--text-muted); font-weight:700;">Balanço Fim do Mês</div>
        <div style="color:<?php echo $saldo_projetado_mes >= 0 ? 'var(--success)' : 'var(--danger)'; ?>; font-weight:800; font-size:20px;">
            <?php echo formataReal($saldo_projetado_mes); ?>
        </div>
    </div>

    <!-- Gráfico Simples de Saldo CSS -->
    <div class="card">
        <h3 style="font-size:16px; margin-bottom:15px;">Variação Prevista (Receitas vs Despesas)</h3>
        <div style="display:flex; height:100px; align-items:flex-end; gap:5px; border-bottom: 2px solid var(--border); padding-bottom:5px;">
            <?php 
            $max_val = max($receitas_mes, $despesas_mes, 1); 
            $h_rec = ($receitas_mes / $max_val) * 100;
            $h_des = ($despesas_mes / $max_val) * 100;
            ?>
            <div style="flex:1; display:flex; justify-content:center; align-items:flex-end; height:100%;">
                <div style="background:var(--success); width:40px; border-radius:4px 4px 0 0; height:<?php echo $h_rec; ?>%; transition:1s;"></div>
            </div>
            <div style="flex:1; display:flex; justify-content:center; align-items:flex-end; height:100%;">
                <div style="background:var(--danger); width:40px; border-radius:4px 4px 0 0; height:<?php echo $h_des; ?>%; transition:1s;"></div>
            </div>
        </div>
        <div style="display:flex; margin-top:5px; text-align:center; font-size:12px; font-weight:700; color:var(--text-muted);">
            <div style="flex:1;">Total Entradas</div>
            <div style="flex:1;">Total Saídas</div>
        </div>
    </div>

    <!-- Próximas Entradas -->
    <div class="card" style="padding:15px;">
        <h3 style="font-size:16px; margin-bottom:10px;">Próximas Entradas</h3>
        <?php if (empty($prox_entradas)): ?>
            <p style="font-size:13px; color:var(--text-muted);">Nenhuma receita programada para o futuro.</p>
        <?php else: ?>
            <?php foreach($prox_entradas as $pe): ?>
                <div class="list-item" style="padding:10px 0;">
                    <div class="item-info">
                        <div class="item-title"><?php echo protege($pe['descricao']); ?></div>
                        <div class="item-date"><?php echo date('d/m/Y', strtotime($pe['data_movimento'])); ?> - <?php echo protege($pe['categoria_nome']); ?></div>
                    </div>
                    <div class="item-value positive">+<?php echo formataReal($pe['valor']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Próximas Saídas -->
    <div class="card" style="padding:15px; margin-bottom:30px;">
        <h3 style="font-size:16px; margin-bottom:10px;">Próximas Despesas</h3>
        <?php if (empty($prox_saidas)): ?>
            <p style="font-size:13px; color:var(--text-muted);">Ufa! Nenhuma despesa programada.</p>
        <?php else: ?>
            <?php foreach($prox_saidas as $ps): ?>
                <div class="list-item" style="padding:10px 0;">
                    <div class="item-info">
                        <div class="item-title"><?php echo protege($ps['descricao']); ?></div>
                        <div class="item-date"><?php echo date('d/m/Y', strtotime($ps['data_movimento'])); ?> - <?php echo protege($ps['categoria_nome']); ?></div>
                    </div>
                    <div class="item-value negative">-<?php echo formataReal($ps['valor']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once '../src/footer.php'; ?>
