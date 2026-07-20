<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$erro = '';

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('Location: /contas/listar.php');
    exit;
}

$conta_id = $_GET['id'] ?? $_POST['id'];

// Verificar se a conta pertence ao usuario
$query_check = "SELECT * FROM contas WHERE id = $1 AND usuario_id = $2";
$result_check = pg_query_params($conexao, $query_check, array($conta_id, $usuario_id));
$conta_atual = pg_fetch_assoc($result_check);

if (!$conta_atual) {
    header('Location: /contas/listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $saldo = $_POST['saldo'] ?? 0;

    if (empty($nome) || empty($tipo)) {
        $erro = "Preencha o nome e o tipo da conta.";
    } else {
        $query = "UPDATE contas SET nome = $1, tipo = $2, saldo = $3 WHERE id = $4 AND usuario_id = $5";
        $result = pg_query_params($conexao, $query, array($nome, $tipo, $saldo, $conta_id, $usuario_id));

        if ($result) {
            header('Location: /contas/listar.php?sucesso=1');
            exit;
        } else {
            $erro = "Erro ao atualizar conta.";
        }
    }
}

require_once '../../src/header.php';
?>

<main class="conteudo">
    <h2>Editar Conta</h2>
    
    <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" action="/contas/editar.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($conta_id); ?>">
        
        <label for="nome">Nome da Conta (ex: Nubank, Carteira)</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($conta_atual['nome']); ?>" required>

        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo" required>
            <option value="fisico" <?php echo $conta_atual['tipo'] == 'fisico' ? 'selected' : ''; ?>>Físico (Dinheiro em espécie)</option>
            <option value="corrente" <?php echo $conta_atual['tipo'] == 'corrente' ? 'selected' : ''; ?>>Conta Corrente</option>
            <option value="poupanca" <?php echo $conta_atual['tipo'] == 'poupanca' ? 'selected' : ''; ?>>Conta Poupança</option>
        </select>

        <label for="saldo">Saldo Atual (R$)</label>
        <input type="number" id="saldo" name="saldo" step="0.01" value="<?php echo htmlspecialchars($conta_atual['saldo']); ?>" required>

        <input type="submit" value="Atualizar Conta">
        <a href="/contas/listar.php" class="btn" style="background: #95a5a6;">Cancelar</a>
    </form>
</main>

<?php require_once '../../src/footer.php'; ?>
