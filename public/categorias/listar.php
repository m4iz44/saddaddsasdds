<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

$query = "SELECT * FROM categorias WHERE usuario_id = $1 ORDER BY tipo, nome";
$result = pg_query_params($conexao, $query, array($usuario_id));
$categorias = $result ? pg_fetch_all($result) ?: [] : [];

require_once '../../src/header.php';
?>

<main class="conteudo">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Minhas Categorias</h2>
        <a href="/categorias/adicionar.php" class="btn">Nova Categoria</a>
    </div>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="sucesso">Operação realizada com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <div class="erro">Não foi possível excluir a categoria. Verifique se existem movimentações associadas a ela.</div>
    <?php endif; ?>

    <?php if (empty($categorias)): ?>
        <p>Você ainda não possui categorias cadastradas. Que tal cadastrar a primeira?</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                <tr>
                    <td><?php echo protege($cat['nome']); ?></td>
                    <td style="text-transform: capitalize;"><?php echo protege($cat['tipo']); ?></td>
                    <td>
                        <a href="/categorias/editar.php?id=<?php echo $cat['id']; ?>" class="btn" style="background: #f39c12; padding: 5px 10px; font-size: 12px; margin-right: 5px;">Editar</a>
                        <a href="/categorias/excluir.php?id=<?php echo $cat['id']; ?>" class="btn" style="background: #e74c3c; padding: 5px 10px; font-size: 12px;" onclick="return confirm('Tem certeza que deseja excluir esta categoria? Isso pode falhar se já existirem movimentações associadas a ela.');">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<?php require_once '../../src/footer.php'; ?>
