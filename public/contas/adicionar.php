<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $saldo = $_POST['saldo'] ?? 0;

    if (empty($nome) || empty($tipo)) {
        $erro = "Preencha o nome e o tipo da conta.";
    } else {
        $query = "INSERT INTO contas (usuario_id, nome, tipo, saldo) VALUES ($1, $2, $3, $4)";
        $result = pg_query_params($conexao, $query, array($usuario_id, $nome, $tipo, $saldo));

        if ($result) {
            header('Location: /contas/listar.php?sucesso=1');
            exit;
        } else {
            $erro = "Erro ao cadastrar conta.";
        }
    }
}

require_once '../../src/header.php';
?>

<main class="conteudo">
    <h2>Nova Conta</h2>
    
    <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" action="/contas/adicionar.php">
        <label for="nome">Nome da Conta (ex: Nubank, Carteira)</label>
        <input type="text" id="nome" name="nome" required>

        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo" required>
            <option value="fisico">Físico (Dinheiro em espécie)</option>
            <option value="corrente">Conta Corrente</option>
            <option value="poupanca">Conta Poupança</option>
        </select>

        <label for="saldo">Saldo Inicial (R$)</label>
        <input type="number" id="saldo" name="saldo" step="0.01" value="0.00" required>

        <input type="submit" value="Salvar Conta">
        <a href="/contas/listar.php" class="btn" style="background: #95a5a6;">Cancelar</a>
    </form>
</main>

<?php require_once '../../src/footer.php'; ?>
