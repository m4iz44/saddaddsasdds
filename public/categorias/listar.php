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

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5 4.5-2.01 4.5-4.5-2.01-4.5-4.5-4.5zm0 7c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5-2.5zM3 21.5h8v-8H3v8zm2-6h4v4H5v-4z"/></svg>
        Minhas Categorias
    </h1>
    <a href="/categorias/adicionar.php" class="btn">
        <svg style="width:20px; height:20px; fill:currentColor;" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
        Nova Categoria
    </a>
</div>

<div class="content-area">

    <div class="card" style="padding: 0;">
        <?php if (empty($categorias)): ?>
            <p style="text-align:center; padding: 20px; color:var(--text-muted);">Você ainda não possui categorias cadastradas.</p>
        <?php else: ?>
            <?php foreach ($categorias as $cat): ?>
                <div class="list-item">
                    <div class="item-info">
                        <div class="item-title"><?php echo protege($cat['nome']); ?></div>
                        <div class="item-date" style="text-transform: capitalize;">Tipo: <?php echo $cat['tipo'] == 'entrada' ? 'Receita / Entrada' : 'Despesa / Saída'; ?></div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <a href="/categorias/editar.php?id=<?php echo $cat['id']; ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Editar</a>
                        <a href="/categorias/excluir.php?id=<?php echo $cat['id']; ?>" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="return confirm('Tem certeza que deseja excluir esta categoria? Isso pode falhar se já existirem movimentações associadas a ela.');">Excluir</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../src/footer.php'; ?>
