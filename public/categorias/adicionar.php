<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if (empty($nome) || empty($tipo)) {
        header('Location: /categorias/adicionar.php?erro=' . urlencode('Preencha todos os campos.'));
        exit;
    } else {
        $query = "INSERT INTO categorias (usuario_id, nome, tipo) VALUES ($1, $2, $3)";
        $result = pg_query_params($conexao, $query, array($usuario_id, $nome, $tipo));

        if ($result) {
            header('Location: /categorias/listar.php?sucesso=' . urlencode('Categoria criada com sucesso!'));
            exit;
        } else {
            header('Location: /categorias/adicionar.php?erro=' . urlencode('Erro ao cadastrar categoria.'));
            exit;
        }
    }
}

require_once '../../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5 4.5-2.01 4.5-4.5-2.01-4.5-4.5-4.5zm0 7c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5-2.5zM3 21.5h8v-8H3v8zm2-6h4v4H5v-4z"/></svg>
        Nova Categoria
    </h1>
</div>

<div class="content-area">
    <div class="card">
        <form method="POST" action="/categorias/adicionar.php">
            <div class="form-group">
                <label for="nome">Nome da Categoria</label>
                <input type="text" id="nome" name="nome" class="form-control" placeholder="Ex: Mercado" required>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo" class="form-control" required>
                    <option value="entrada">Receita / Entrada</option>
                    <option value="saida" selected>Despesa / Saída</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn" style="flex: 1;">Salvar Categoria</button>
                <a href="/categorias/listar.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../src/footer.php'; ?>
