<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];
$erro = '';

if (!isset($_GET['id']) && !isset($_POST['id'])) {
    header('Location: /categorias/listar.php');
    exit;
}

$cat_id = $_GET['id'] ?? $_POST['id'];

// Verificar se a categoria pertence ao usuario
$query_check = "SELECT * FROM categorias WHERE id = $1 AND usuario_id = $2";
$result_check = pg_query_params($conexao, $query_check, array($cat_id, $usuario_id));
$cat_atual = pg_fetch_assoc($result_check);

if (!$cat_atual) {
    header('Location: /categorias/listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if (empty($nome) || empty($tipo)) {
        $erro = "Preencha todos os campos.";
    } else {
        $query = "UPDATE categorias SET nome = $1, tipo = $2 WHERE id = $3 AND usuario_id = $4";
        $result = pg_query_params($conexao, $query, array($nome, $tipo, $cat_id, $usuario_id));

        if ($result) {
            header('Location: /categorias/listar.php?sucesso=1');
            exit;
        } else {
            $erro = "Erro ao atualizar categoria.";
        }
    }
}

require_once '../../src/header.php';
?>

<main class="conteudo">
    <h2>Editar Categoria</h2>
    
    <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" action="/categorias/editar.php">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat_id); ?>">
        
        <label for="nome">Nome da Categoria</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($cat_atual['nome']); ?>" required>

        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo" required>
            <option value="entrada" <?php echo $cat_atual['tipo'] == 'entrada' ? 'selected' : ''; ?>>Receita / Entrada</option>
            <option value="saida" <?php echo $cat_atual['tipo'] == 'saida' ? 'selected' : ''; ?>>Despesa / Saída</option>
        </select>

        <input type="submit" value="Atualizar Categoria">
        <a href="/categorias/listar.php" class="btn" style="background: #95a5a6;">Cancelar</a>
    </form>
</main>

<?php require_once '../../src/footer.php'; ?>
