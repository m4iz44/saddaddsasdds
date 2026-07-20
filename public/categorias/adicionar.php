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

    if (empty($nome) || empty($tipo)) {
        $erro = "Preencha todos os campos.";
    } else {
        $query = "INSERT INTO categorias (usuario_id, nome, tipo) VALUES ($1, $2, $3)";
        $result = pg_query_params($conexao, $query, array($usuario_id, $nome, $tipo));

        if ($result) {
            header('Location: /categorias/listar.php?sucesso=1');
            exit;
        } else {
            $erro = "Erro ao cadastrar categoria.";
        }
    }
}

require_once '../../src/header.php';
?>

<main class="conteudo">
    <h2>Nova Categoria</h2>
    
    <?php if (!empty($erro)): ?>
        <div class="erro"><?php echo $erro; ?></div>
    <?php endif; ?>

    <form method="POST" action="/categorias/adicionar.php">
        <label for="nome">Nome da Categoria</label>
        <input type="text" id="nome" name="nome" required>

        <label for="tipo">Tipo</label>
        <select id="tipo" name="tipo" required>
            <option value="entrada">Receita / Entrada</option>
            <option value="saida">Despesa / Saída</option>
        </select>

        <input type="submit" value="Salvar Categoria">
        <a href="/categorias/listar.php" class="btn" style="background: #95a5a6;">Cancelar</a>
    </form>
</main>

<?php require_once '../../src/footer.php'; ?>
