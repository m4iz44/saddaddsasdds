<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

// Busca dados do usuário
$q_usr = "SELECT * FROM usuarios WHERE id = $1";
$r_usr = pg_query_params($conexao, $q_usr, array($usuario_id));
$usuario = pg_fetch_assoc($r_usr);

require_once '../../src/header.php';
?>

<div class="top-header">
    <h1>
        <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
        Meu Perfil
    </h1>
</div>

<div class="content-area">

    <div class="card" style="text-align: center; margin-bottom: 20px;">
        <div style="width: 80px; height: 80px; border-radius: 40px; background: var(--secondary); margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 30px; color: white; font-weight: bold;">
            <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
        </div>
        <h2 style="font-size: 20px;"><?php echo protege($usuario['nome']); ?></h2>
        <p style="color: var(--text-muted); font-size: 14px;"><?php echo protege($usuario['email']); ?></p>
    </div>

    <div class="card">
        <h3 style="font-size: 16px; margin-bottom: 15px;">Configurações</h3>
        <form action="editar.php" method="POST">
            <div class="form-group">
                <label>Nome</label>
                <input type="text" name="nome" class="form-control" value="<?php echo protege($usuario['nome']); ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Moeda Padrão</label>
                    <select name="moeda" class="form-control">
                        <option value="R$" <?php echo $usuario['moeda'] == 'R$' ? 'selected' : ''; ?>>BRL (R$)</option>
                        <option value="USD" <?php echo $usuario['moeda'] == 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                        <option value="EUR" <?php echo $usuario['moeda'] == 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Início do Mês</label>
                    <input type="number" name="mes_fechamento" min="1" max="31" class="form-control" value="<?php echo $usuario['mes_fechamento'] ?? 1; ?>" required>
                </div>
            </div>
            <button type="submit" class="btn" style="margin-top: 10px;">Salvar Alterações</button>
        </form>
    </div>

    <div class="card">
        <h3 style="font-size: 16px; margin-bottom: 15px;">Meus Dados</h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 15px;">Faça o download do seu extrato mensal completo em formato de planilha (CSV).</p>
        <a href="exportar.php" class="btn btn-secondary">
            <svg style="width:20px; height:20px; fill:currentColor; margin-right:8px; vertical-align:middle;" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"/></svg>
            Exportar Dados (CSV)
        </a>
    </div>

    <div style="text-align:center; margin-top:30px; margin-bottom: 30px;">
        <a href="/logout.php" style="color:var(--danger); font-weight:700; font-size:14px; text-decoration:underline;">Sair do aplicativo</a>
    </div>

</div>

<?php require_once '../../src/footer.php'; ?>
