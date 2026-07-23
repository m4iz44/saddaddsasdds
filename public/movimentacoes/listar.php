<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

// Filtro de mês igual à Dashboard
$mes_atual = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
$ano_atual = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
$primeiro_dia = sprintf('%04d-%02d-01', $ano_atual, $mes_atual);
$ultimo_dia = date('Y-m-t', strtotime($primeiro_dia));

$aba_ativa = isset($_GET['aba']) && $_GET['aba'] == 'despesas' ? 'despesas' : 'receitas';

// Busca Movimentações do Mês
$q_mov = "SELECT m.*, c.nome as categoria_nome 
          FROM movimentacoes m 
          LEFT JOIN categorias c ON m.categoria_id = c.id 
          WHERE m.usuario_id = $1 AND m.data_movimento BETWEEN $2 AND $3
          ORDER BY m.data_movimento ASC";
$r_mov = pg_query_params($conexao, $q_mov, array($usuario_id, $primeiro_dia, $ultimo_dia));
$movimentacoes = $r_mov ? pg_fetch_all($r_mov) : [];

// Busca categorias para os selects
$cat_entradas = getCategoriasPorTipo($usuario_id, 'entrada', $conexao);
$cat_saidas = getCategoriasPorTipo($usuario_id, 'saida', $conexao);

// Busca contas
$contas = getContas($usuario_id, $conexao);

// Mês anterior e próximo para navegação
$mes_ant = $mes_atual - 1;
$ano_ant = $ano_atual;
if ($mes_ant < 1) { $mes_ant = 12; $ano_ant--; }

$mes_prox = $mes_atual + 1;
$ano_prox = $ano_atual;
if ($mes_prox > 12) { $mes_prox = 1; $ano_prox++; }

$meses = ["", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

$dia_padrao = sprintf('%04d-%02d-%02d', $ano_atual, $mes_atual, min((int)date('d'), (int)date('t', strtotime($primeiro_dia))));

require_once '../../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M4 14h4v-4H4v4zm0 5h4v-4H4v4zM4 9h4V5H4v4zm5 5h12v-4H9v4zm0 5h12v-4H9v4zM9 5v4h12V5H9z"/></svg>
        Extrato
    </h1>
    <div>
        <a href="<?php echo $aba_ativa == 'receitas' ? '#modal-add-receita' : '#modal-add-despesa'; ?>" class="btn">
            <svg style="width:20px; height:20px; fill:currentColor;" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Nova Transação
        </a>
    </div>
</div>

<div class="content-area">
    <!-- Navegação de Mês -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
        <a href="?mes=<?php echo $mes_ant; ?>&ano=<?php echo $ano_ant; ?>&aba=<?php echo $aba_ativa; ?>" style="background:var(--surface); padding:8px 12px; border-radius:10px; box-shadow:var(--shadow-sm); border:1px solid var(--border); display:flex; align-items:center; justify-content:center;" title="Mês Anterior">
            <svg style="width:20px; height:20px; fill:var(--text-main);" viewBox="0 0 24 24"><path d="M15.41 16.59L10.83 12l4.58-4.59L14 6l-6 6 6 6 1.41-1.41z"/></svg>
        </a>
        <strong style="font-size:18px; color:var(--text-main);"><?php echo $meses[$mes_atual] . ' / ' . $ano_atual; ?></strong>
        <a href="?mes=<?php echo $mes_prox; ?>&ano=<?php echo $ano_prox; ?>&aba=<?php echo $aba_ativa; ?>" style="background:var(--surface); padding:8px 12px; border-radius:10px; box-shadow:var(--shadow-sm); border:1px solid var(--border); display:flex; align-items:center; justify-content:center;" title="Próximo Mês">
            <svg style="width:20px; height:20px; fill:var(--text-main);" viewBox="0 0 24 24"><path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z"/></svg>
        </a>
    </div>

    <!-- TABS em CSS Puro (Usando links para recarregar com a variavel aba) -->
    <div class="tabs">
        <a href="?mes=<?php echo $mes_atual; ?>&ano=<?php echo $ano_atual; ?>&aba=receitas" class="tab-btn <?php echo $aba_ativa == 'receitas' ? 'active' : ''; ?>">Receitas</a>
        <a href="?mes=<?php echo $mes_atual; ?>&ano=<?php echo $ano_atual; ?>&aba=despesas" class="tab-btn <?php echo $aba_ativa == 'despesas' ? 'active' : ''; ?>">Despesas</a>
    </div>

    <div class="card" style="padding: 0;">
        <?php 
        $tem_itens = false;
        $tipo_filtro = $aba_ativa == 'receitas' ? 'entrada' : 'saida';
        
        if ($movimentacoes) {
            foreach ($movimentacoes as $mov) {
                if ($mov['tipo'] == $tipo_filtro) {
                    $tem_itens = true;
                    $is_pago = $mov['status'] == 'pago';
                    $sinal = $mov['tipo'] == 'entrada' ? '+' : '-';
                    $cor_valor = $mov['tipo'] == 'entrada' ? 'positive' : 'negative';
                    ?>
                    <a href="#modal-edit-<?php echo $mov['id']; ?>" class="list-item" style="text-decoration:none; color:inherit;">
                        <div style="display:flex; align-items:center; flex:1;">
                            <div class="item-icon">
                                <?php if ($is_pago): ?>
                                    <svg style="fill:var(--success);" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                                <?php else: ?>
                                    <svg style="fill:var(--warning);" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
                                <?php endif; ?>
                            </div>
                            <div class="item-info">
                                <div class="item-title"><?php echo protege($mov['descricao']); ?></div>
                                <div class="item-date"><?php echo date('d/m/Y', strtotime($mov['data_movimento'])); ?> - <?php echo protege($mov['categoria_nome']); ?></div>
                            </div>
                        </div>
                        <div class="item-value <?php echo $cor_valor; ?>"><?php echo $sinal . formataReal($mov['valor']); ?></div>
                    </a>

                    <!-- MODAL DE EDIÇÃO/EXCLUSÃO PARA ESTE ITEM -->
                    <div id="modal-edit-<?php echo $mov['id']; ?>" class="modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h3>Editar Movimentação</h3>
                                <a href="#" class="modal-close">X</a>
                            </div>
                            <form action="editar.php" method="POST">
                                <input type="hidden" name="id" value="<?php echo $mov['id']; ?>">
                                <div class="form-group">
                                    <label>Descrição</label>
                                    <input type="text" name="descricao" class="form-control" value="<?php echo protege($mov['descricao']); ?>" required>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Valor</label>
                                        <input type="number" step="0.01" name="valor" class="form-control" value="<?php echo $mov['valor']; ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Data</label>
                                        <input type="date" name="data_movimento" class="form-control" value="<?php echo $mov['data_movimento']; ?>" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="pago" <?php echo $mov['status'] == 'pago' ? 'selected' : ''; ?>>Pago/Recebido</option>
                                            <option value="pendente" <?php echo $mov['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Categoria</label>
                                        <select name="categoria_id" class="form-control" required>
                                            <?php 
                                            $cats = $mov['tipo'] == 'entrada' ? $cat_entradas : $cat_saidas;
                                            if (empty($cats)): ?>
                                                <option value="">Nenhuma categoria cadastrada</option>
                                            <?php else:
                                                foreach($cats as $c): ?>
                                                    <option value="<?php echo $c['id']; ?>" <?php echo $mov['categoria_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo protege($c['nome']); ?></option>
                                                <?php endforeach;
                                            endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div style="display:flex; gap:10px; margin-top: 10px;">
                                    <button type="submit" class="btn" style="flex:1;">Salvar</button>
                                    <a href="excluir.php?id=<?php echo $mov['id']; ?>" class="btn btn-danger" style="flex:1; text-align:center;">Excluir</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        
        if (!$tem_itens) {
            echo '<p style="text-align:center; padding: 20px; color:var(--text-muted);">Nenhum registro encontrado neste mês.</p>';
        }
        ?>
    </div>

    <!-- MODAL ADICIONAR RECEITA -->
    <div id="modal-add-receita" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nova Receita</h3>
                <a href="#" class="modal-close">X</a>
            </div>
            <form action="adicionar.php" method="POST">
                <input type="hidden" name="tipo" value="entrada">
                <div class="form-group">
                    <label>Descrição (Ex: Salário)</label>
                    <input type="text" name="descricao" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Valor</label>
                        <input type="number" step="0.01" name="valor" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Data</label>
                        <input type="date" name="data_movimento" class="form-control" value="<?php echo $dia_padrao; ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="pago">Recebido (Pago)</option>
                            <option value="pendente">Pendente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria_id" class="form-control" required>
                            <?php if (empty($cat_entradas)): ?>
                                <option value="">Nenhuma categoria cadastrada</option>
                            <?php else: ?>
                                <?php foreach($cat_entradas as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo protege($c['nome']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Conta</label>
                    <select name="conta_id" class="form-control" required>
                        <?php foreach($contas as $cta): ?>
                            <option value="<?php echo $cta['id']; ?>"><?php echo protege($cta['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn" style="margin-top:10px;">Salvar Receita</button>
            </form>
        </div>
    </div>

    <!-- MODAL ADICIONAR DESPESA -->
    <div id="modal-add-despesa" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Nova Despesa</h3>
                <a href="#" class="modal-close">X</a>
            </div>
            <form action="adicionar.php" method="POST">
                <input type="hidden" name="tipo" value="saida">
                <div class="form-group">
                    <label>Descrição (Ex: Aluguel)</label>
                    <input type="text" name="descricao" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Valor</label>
                        <input type="number" step="0.01" name="valor" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Data</label>
                        <input type="date" name="data_movimento" class="form-control" value="<?php echo $dia_padrao; ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="pago">Pago</option>
                            <option value="pendente">Pendente</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria_id" class="form-control" required>
                            <?php if (empty($cat_saidas)): ?>
                                <option value="">Nenhuma categoria cadastrada</option>
                            <?php else: ?>
                                <?php foreach($cat_saidas as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo protege($c['nome']); ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Conta</label>
                    <select name="conta_id" class="form-control" required>
                        <?php foreach($contas as $cta): ?>
                            <option value="<?php echo $cta['id']; ?>"><?php echo protege($cta['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn" style="margin-top:10px;">Salvar Despesa</button>
            </form>
        </div>
    </div>

</div>

<?php require_once '../../src/footer.php'; ?>
