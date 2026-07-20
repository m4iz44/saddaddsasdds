<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_auth_page = strpos($_SERVER['PHP_SELF'], 'login.php') !== false || strpos($_SERVER['PHP_SELF'], 'cadastro.php') !== false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($nome_site) ? $nome_site : 'Financinhas'; ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="<?php echo $is_auth_page ? 'auth-page' : ''; ?>">

<?php if (isset($_SESSION['usuario_id']) && !$is_auth_page): ?>
    <!-- SIDEBAR DESKTOP -->
    <aside class="sidebar">
        <div class="brand">
            <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1.41 16.09V20h-2.67v-1.93c-1.71-.36-3.16-1.46-3.27-3.4h1.96c.1 1.05.82 1.87 2.65 1.87 1.96 0 2.4-.98 2.4-1.59 0-.83-.44-1.61-2.67-2.14-2.48-.6-4.18-1.62-4.18-3.67 0-1.72 1.39-2.84 3.11-3.21V4h2.67v1.95c1.86.45 2.79 1.86 2.85 3.39H14.3c-.05-1.11-.64-1.87-2.22-1.87-1.5 0-2.4.68-2.4 1.64 0 .84.65 1.39 2.67 1.91 2.95.73 4.18 1.9 4.18 3.91-.01 1.83-1.38 2.83-3.12 3.16z"/></svg>
            Financinhas
        </div>
        
        <nav class="nav-menu">
            <a href="/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                Início
            </a>
            
            <a href="/movimentacoes/listar.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'movimentacoes') !== false ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M4 14h4v-4H4v4zm0 5h4v-4H4v4zM4 9h4V5H4v4zm5 5h12v-4H9v4zm0 5h12v-4H9v4zM9 5v4h12V5H9z"/></svg>
                Transações
            </a>
            
            <a href="/investimentos/listar.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'investimentos') !== false ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M3.5 18.49l6-6.01 4 4L22 6.92l-1.41-1.41-7.09 7.97-4-4L2 16.99z"/></svg>
                Investimentos
            </a>
            
            <a href="/contas/listar.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'contas') !== false ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M21 18v1c0 1.1-.9 2-2 2H5c-1.11 0-2-.9-2-2V5c0-1.1.89-2 2-2h14c1.1 0 2 .9 2 2v1h-9c-1.11 0-2 .9-2 2v8c0 1.1.89 2 2 2h9zm-9-2h10V8H12v8zm4-2.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
                Contas
            </a>
            
            <a href="/categorias/listar.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'categorias') !== false ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M12 2l-5.5 9h11L12 2zm0 3.84L13.93 9h-3.87L12 5.84zM17.5 13c-2.49 0-4.5 2.01-4.5 4.5s2.01 4.5 4.5 4.5 4.5-2.01 4.5-4.5-2.01-4.5-4.5-4.5zm0 7c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5zM3 21.5h8v-8H3v8zm2-6h4v4H5v-4z"/></svg>
                Categorias
            </a>
            
            <a href="/perfil/index.php" class="nav-item <?php echo strpos($_SERVER['PHP_SELF'], 'perfil') !== false ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                Perfil
            </a>
        </nav>
        
        <div style="margin-top: auto;">
            <a href="/logout.php" class="nav-item" style="color: var(--danger);">
                <svg viewBox="0 0 24 24"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
                Sair
            </a>
        </div>
    </aside>

    <!-- CONTAINER PRINCIPAL -->
    <main class="main-content">
<?php endif; ?>
