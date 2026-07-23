<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

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
        header('Location: /categorias/editar.php?id=' . $cat_id . '&erro=' . urlencode('Preencha todos os campos.'));
        exit;
    } else {
        $query = "UPDATE categorias SET nome = $1, tipo = $2 WHERE id = $3 AND usuario_id = $4";
        $result = pg_query_params($conexao, $query, array($nome, $tipo, $cat_id, $usuario_id));

        if ($result) {
            header('Location: /categorias/listar.php?sucesso=' . urlencode('Categoria atualizada com sucesso!'));
            exit;
        } else {
            header('Location: /categorias/editar.php?id=' . $cat_id . '&erro=' . urlencode('Erro ao atualizar categoria.'));
            exit;
        }
    }
}

require_once '../../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5 4.5-2.01 4.5-4.5-2.01-4.5-4.5-4.5zm0 7c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5-2.5zM3 21.5h8v-8H3v8zm2-6h4v4H5v-4z"/></svg>
        Editar Categoria
    </h1>
</div>

<div class="content-area">
    <div class="card">
        <form method="POST" action="/categorias/editar.php">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($cat_id); ?>">

            <div class="form-group">
                <label for="nome">Nome da Categoria</label>
                <input type="text" id="nome" name="nome" class="form-control" value="<?php echo htmlspecialchars($cat_atual['nome']); ?>" required>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-control" required>
                    <option value="entrada" <?php echo $cat_atual['tipo'] == 'entrada' ? 'selected' : ''; ?>>Receita / Entrada</option>
                    <option value="saida" <?php echo $cat_atual['tipo'] == 'saida' ? 'selected' : ''; ?>>Despesa / Saída</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn" style="flex: 1;">Atualizar Categoria</button>
                <a href="/categorias/listar.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../src/footer.php'; ?>
