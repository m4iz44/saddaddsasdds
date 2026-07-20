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
$q_cat_ent = "SELECT * FROM categorias WHERE usuario_id = $1 AND tipo = 'entrada'";
$q_cat_sai = "SELECT * FROM categorias WHERE usuario_id = $1 AND tipo = 'saida'";
$cat_entradas = pg_fetch_all(pg_query_params($conexao, $q_cat_ent, array($usuario_id))) ?: [];
$cat_saidas = pg_fetch_all(pg_query_params($conexao, $q_cat_sai, array($usuario_id))) ?: [];

// Busca contas
$contas = getContas($usuario_id, $conexao);

$meses = ["", "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];

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
    <div style="text-align:center; margin-bottom: 20px; font-weight:700;">
        <?php echo $meses[$mes_atual] . ' / ' . $ano_atual; ?>
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
                                            foreach($cats as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo $mov['categoria_id'] == $c['id'] ? 'selected' : ''; ?>><?php echo protege($c['nome']); ?></option>
                                            <?php endforeach; ?>
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
                        <input type="date" name="data_movimento" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
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
                            <?php foreach($cat_entradas as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo protege($c['nome']); ?></option>
                            <?php endforeach; ?>
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
                        <input type="date" name="data_movimento" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
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
                            <?php foreach($cat_saidas as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo protege($c['nome']); ?></option>
                            <?php endforeach; ?>
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
                <button type="submit" class="btn" style="background:var(--danger); margin-top:10px;">Salvar Despesa</button>
            </form>
        </div>
    </div>

</div>

<?php require_once '../../src/footer.php'; ?>
