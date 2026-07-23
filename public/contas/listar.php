<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$contas = getContasComSaldoAtual($usuario_id, $conexao);

require_once '../../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
        Minhas Contas
    </h1>
    <a href="/contas/adicionar.php" class="btn">
        <svg style="width:20px; height:20px; fill:currentColor;" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Nova Conta
    </a>
</div>

<div class="content-area">

    <div class="card" style="padding: 0;">
        <?php if (empty($contas)): ?>
            <p style="text-align:center; padding: 20px; color:var(--text-muted);">Você ainda não possui contas cadastradas.</p>
        <?php else: ?>
            <?php foreach ($contas as $conta): ?>
                <div class="list-item">
                    <div class="item-info">
                        <div class="item-title"><?php echo protege($conta['nome']); ?></div>
                        <div class="item-date" style="text-transform: capitalize;">Tipo: <?php echo protege($conta['tipo']); ?></div>
                    </div>
                    <div class="item-value <?php echo $conta['saldo_atual'] < 0 ? 'negative' : 'positive'; ?>" style="margin-right: 20px;">
                        <?php echo formataReal($conta['saldo_atual']); ?>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="/contas/editar.php?id=<?php echo $conta['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Editar</a>
                        <a href="/contas/excluir.php?id=<?php echo $conta['id']; ?>" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Tem certeza que deseja excluir esta conta? Todas as movimentações nela também serão excluídas.');">Excluir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../src/footer.php'; ?>
