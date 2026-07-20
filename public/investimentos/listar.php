<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

// Busca Investimentos
$q_inv = "SELECT * FROM investimentos WHERE usuario_id = $1 ORDER BY data_criacao DESC";
$r_inv = pg_query_params($conexao, $q_inv, array($usuario_id));
$investimentos = $r_inv ? pg_fetch_all($r_inv) : [];

$total_investido = 0;
$total_atual = 0;

if ($investimentos) {
    foreach ($investimentos as $inv) {
        $total_investido += $inv['valor_aplicado'];
        $total_atual += $inv['valor_atual'] > 0 ? $inv['valor_atual'] : $inv['valor_aplicado'];
    }
}

require_once '../../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
        Investimentos
    </h1>
    <div>
        <a href="#modal-add-investimento" class="btn" style="background: var(--tertiary);">
            <svg style="width:20px; height:20px; fill:currentColor;" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
            Novo Investimento
        </a>
    </div>
</div>

<div class="content-area">

    <div class="card" style="background: linear-gradient(135deg, var(--tertiary), #9D8DF1); color: white; text-align: center; border: none;">
        <div class="card-header" style="color:white; opacity:0.9;">Total Investido (Atual)</div>
        <div class="card-value" style="color:white;"><?php echo formataReal($total_atual); ?></div>
        <div style="font-size:12px; margin-top:5px; opacity:0.8;">Valor aplicado: <?php echo formataReal($total_investido); ?></div>
    </div>

    <h2 style="font-size: 18px; margin: 20px 0 10px;">Meus Ativos</h2>

    <div class="card" style="padding: 0;">
        <?php if (!$investimentos): ?>
            <p style="text-align:center; padding: 20px; color:var(--text-muted);">Nenhum investimento cadastrado.</p>
        <?php else: ?>
            <?php foreach ($investimentos as $inv): ?>
                <div class="list-item" style="padding:15px 20px;">
                    <div style="display:flex; align-items:center; flex:1;">
                        <div class="item-icon" style="background:var(--bg-app); border: 1px solid var(--border);">
                            <svg viewBox="0 0 24 24" style="fill:var(--tertiary);"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91 2.95.73 4.18 1.9 4.18 3.91-.01 1.83-1.38 2.83-3.12 3.16z"/></svg>
                        </div>
                        <div class="item-info">
                            <div class="item-title"><?php echo protege($inv['nome']); ?></div>
                            <div class="item-date"><?php echo ucfirst(protege($inv['tipo'])); ?></div>
                        </div>
                    </div>
                    <div class="item-value" style="color:var(--tertiary);">
                        <?php echo formataReal($inv['valor_atual'] > 0 ? $inv['valor_atual'] : $inv['valor_aplicado']); ?>
                    </div>
                    <a href="excluir.php?id=<?php echo $inv['id']; ?>" style="margin-left: 15px; color:var(--text-muted); transition:0.2s;" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-muted)'">
                        <svg style="width:20px; fill:currentColor;" viewBox="0 0 24 24"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- MODAL ADICIONAR INVESTIMENTO -->
    <div id="modal-add-investimento" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Novo Investimento</h3>
                <a href="#" class="modal-close">X</a>
            </div>
            <form action="adicionar.php" method="POST">
                <div class="form-group">
                    <label>Tipo de Investimento</label>
                    <select name="tipo" class="form-control" required>
                        <option value="caixinha">Caixinha / Reserva</option>
                        <option value="acao">Ações / Renda Variável</option>
                        <option value="rendafixa">Tesouro / Renda Fixa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Nome do Ativo ou Caixinha (Ex: Viagem 2027)</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Valor Aplicado</label>
                        <input type="number" step="0.01" name="valor_aplicado" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Valor Atual (Opcional)</label>
                        <input type="number" step="0.01" name="valor_atual" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn" style="background:var(--tertiary); margin-top:10px;">Salvar Investimento</button>
            </form>
        </div>
    </div>

</div>

<?php require_once '../../src/footer.php'; ?>
