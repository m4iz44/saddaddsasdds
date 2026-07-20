<?php
require_once '../src/config.php';
require_once '../src/conecta.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $senha2 = $_POST['senha2'] ?? '';

    if (empty($nome) || empty($email) || empty($senha) || empty($senha2)) {
        $erro = "Preencha todos os campos.";
    } elseif ($senha !== $senha2) {
        $erro = "As senhas não coincidem.";
    } else {
        $q_check = "SELECT id FROM usuarios WHERE email = $1";
        $r_check = pg_query_params($conexao, $q_check, array($email));
        
        if (pg_num_rows($r_check) > 0) {
            $erro = "Este e-mail já está em uso.";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $q_ins = "INSERT INTO usuarios (nome, email, senha) VALUES ($1, $2, $3) RETURNING id";
            $r_ins = pg_query_params($conexao, $q_ins, array($nome, $email, $hash));
            
            if ($r_ins) {
                $novo_usuario_id = pg_fetch_result($r_ins, 0, 0);
                
                // Inserir Conta Padrão
                $q_conta = "INSERT INTO contas (usuario_id, nome, tipo, saldo) VALUES ($1, 'Carteira Principal', 'corrente', 0)";
                pg_query_params($conexao, $q_conta, array($novo_usuario_id));
                
                // Inserir Categorias Padrão
                $cats = [
                    ['Salário', 'entrada'],
                    ['Investimentos', 'entrada'],
                    ['Alimentação', 'saida'],
                    ['Moradia', 'saida'],
                    ['Transporte', 'saida'],
                    ['Lazer', 'saida']
                ];
                foreach ($cats as $cat) {
                    $q_cat = "INSERT INTO categorias (usuario_id, nome, tipo) VALUES ($1, $2, $3)";
                    pg_query_params($conexao, $q_cat, array($novo_usuario_id, $cat[0], $cat[1]));
                }
                
                header('Location: login.php?sucesso=1');
                exit;
            } else {
                $erro = "Erro ao cadastrar. Tente novamente.";
            }
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

    <h2 style="font-size: 20px; color: var(--text-muted); margin-bottom: 30px;">Crie sua conta!</h2>

    <?php if ($erro): ?>
        <div style="background: var(--danger); color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: bold;">
            <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="cadastro.php">
        <div class="form-group">
            <label>Nome Completo</label>
            <input type="text" name="nome" class="form-control" required placeholder="Seu nome">
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" class="form-control" required placeholder="seu@email.com">
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" name="senha" class="form-control" required placeholder="********">
        </div>
        <div class="form-group">
            <label>Confirme a Senha</label>
            <input type="password" name="senha2" class="form-control" required placeholder="********">
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 10px;">Cadastrar</button>
    </form>

    <div style="margin-top: 30px; color: var(--text-muted); font-weight: 600;">
        Já tem conta? <a href="login.php" style="color: var(--primary-dark);">Faça login</a>
    </div>
</div>

<?php require_once '../src/footer.php'; ?>
