<?php
require_once '../src/config.php';
require_once '../src/conecta.php';
require_once '../src/funcoes.php';

if (estaLogado()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = $_GET['sucesso'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        $query = "SELECT id, nome, senha FROM usuarios WHERE email = $1";
        $result = pg_query_params($conexao, $query, array($email));
        
        if ($result && pg_num_rows($result) > 0) {
            $usuario = pg_fetch_assoc($result);
            if (password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                header('Location: index.php');
                exit;
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
        } else {
            $erro = "E-mail ou senha incorretos.";
        }
    }
}
require_once '../src/header.php';
?>

<div class="auth-card">
    <div class="brand">
        <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91 2.95.73 4.18 1.9 4.18 3.91-.01 1.83-1.38 2.83-3.12 3.16z"/></svg>
        Financinhas
    </div>

    <h2 style="font-size: 20px; color: var(--text-muted); margin-bottom: 30px;">Bem-vinda de volta!</h2>

    <?php if ($erro): ?>
        <div style="background: var(--danger); color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: bold;">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <div style="background: var(--success); color: #2E5C2D; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: bold;">
            Cadastro realizado com sucesso! Faça login.
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" class="form-control" required placeholder="********">
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Entrar</button>
    </form>

    <div style="margin-top: 30px; color: var(--text-muted); font-weight: 600;">
        Ainda não tem conta? <a href="cadastro.php" style="color: var(--primary-dark);">Cadastre-se</a>
    </div>
</div>

<?php require_once '../src/footer.php'; ?>
