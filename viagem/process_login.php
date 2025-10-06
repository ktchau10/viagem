<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = Database::getInstance();

    // Sanitização dos inputs
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha']; // Senha não deve ser sanitizada

    // Validações básicas
    if (empty($email) || empty($senha)) {
        header("Location: login.php?error=empty");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: login.php?error=invalid_email");
        exit;
    }

    // Busca o usuário
    $usuario = $db->buscarUsuarioPorEmail($email);

    if (!$usuario) {
        header("Location: login.php?error=user_not_found");
        exit;
    }

    if (!password_verify($senha, $usuario['senha'])) {
        header("Location: login.php?error=wrong_password");
        exit;
    }

    // Verifica se o usuário está ativo
    if ($usuario['status'] !== 'ativo') {
        header("Location: login.php?error=inactive");
        exit;
    }

    // Se chegou aqui, o login está correto
    // Inicia a sessão com os dados do usuário
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    
    // Atualiza último acesso
    $db->atualizarUltimoAcesso($usuario['id']);

    // Redireciona para o dashboard
    header("Location: dashboard.php");
    exit;
}
?>
