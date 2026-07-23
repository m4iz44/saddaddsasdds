<?php
require_once '../../src/config.php';
require_once '../../src/conecta.php';
require_once '../../src/funcoes.php';

exigeLogin();
$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $moeda = $_POST['moeda'] ?? 'R$';
    $mes_fechamento = (int)($_POST['mes_fechamento'] ?? 1);

    if (!empty($nome)) {
        $q = "UPDATE usuarios SET nome=$1, moeda=$2, mes_fechamento=$3 WHERE id=$4";
        pg_query_params($conexao, $q, array($nome, $moeda, $mes_fechamento, $usuario_id));
        $_SESSION['usuario_nome'] = $nome;
        header('Location: /perfil/index.php?sucesso=' . urlencode('Perfil atualizado com sucesso!'));
    } else {
        header('Location: /perfil/index.php?erro=' . urlencode('Preencha o nome.'));
    }
    exit;
} else {
    header('Location: /perfil/index.php');
    exit;
}
