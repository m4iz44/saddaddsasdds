<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$contas = getContas($usuario_id, $conexao);

require_once '../../src/header.php';
?>

<main class="conteudo">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Minhas Contas</h2>
        <a href="/contas/adicionar.php" class="btn">Nova Conta</a>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="sucesso">Operação realizada com sucesso!</div>
    <?php endif; ?>

    <?php if (empty($contas)): ?>
        <p>Você ainda não possui contas cadastradas.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Saldo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contas as $conta): ?>
                <tr>
                    <td><?php echo protege($conta['nome']); ?></td>
                    <td style="text-transform: capitalize;"><?php echo protege($conta['tipo']); ?></td>
                    <td style="<?php echo $conta['saldo'] < 0 ? 'color: #e74c3c;' : 'color: #27ae60;'; ?>">
                        <?php echo formataReal($conta['saldo']); ?>
                    </td>
                    <td>
                        <a href="/contas/editar.php?id=<?php echo $conta['id']; ?>" class="btn" style="background: #f39c12; padding: 5px 10px; font-size: 12px; margin-right: 5px;">Editar</a>
                        <a href="/contas/excluir.php?id=<?php echo $conta['id']; ?>" class="btn" style="background: #e74c3c; padding: 5px 10px; font-size: 12px;" onclick="return confirm('Tem certeza que deseja excluir esta conta? Todas as movimentações nela também serão excluídas.');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php require_once '../../src/footer.php'; ?>
