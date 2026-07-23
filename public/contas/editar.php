<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

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
        header('Location: /contas/editar.php?id=' . $conta_id . '&erro=' . urlencode('Preencha o nome e o tipo da conta.'));
        exit;
    } else {
        $query = "UPDATE contas SET nome = $1, tipo = $2, saldo = $3 WHERE id = $4 AND usuario_id = $5";
        $result = pg_query_params($conexao, $query, array($nome, $tipo, $saldo, $conta_id, $usuario_id));

        if ($result) {
            header('Location: /contas/listar.php?sucesso=' . urlencode('Conta atualizada com sucesso!'));
            exit;
        } else {
            header('Location: /contas/editar.php?id=' . $conta_id . '&erro=' . urlencode('Erro ao atualizar conta.'));
            exit;
        }
    }
}

require_once '../../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
        Editar Conta
    </h1>
</div>

<div class="content-area">
    <div class="card">
        <form method="POST" action="/contas/editar.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($conta_id); ?>">

            <div class="form-group">
                <label for="nome">Nome da Conta</label>
                <input type="text" id="nome" name="nome" class="form-control" value="<?php echo htmlspecialchars($conta_atual['nome']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" class="form-control" required>
                        <option value="fisico" <?php echo $conta_atual['tipo'] == 'fisico' ? 'selected' : ''; ?>>Físico (Dinheiro em espécie)</option>
                        <option value="corrente" <?php echo $conta_atual['tipo'] == 'corrente' ? 'selected' : ''; ?>>Conta Corrente</option>
                        <option value="poupanca" <?php echo $conta_atual['tipo'] == 'poupanca' ? 'selected' : ''; ?>>Conta Poupança</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="saldo">Saldo Inicial (R$)</label>
                    <input type="number" id="saldo" name="saldo" class="form-control" step="0.01" value="<?php echo htmlspecialchars($conta_atual['saldo']); ?>" required>
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn" style="flex: 1;">Atualizar Conta</button>
                <a href="/contas/listar.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../src/footer.php'; ?>
