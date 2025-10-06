<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = Database::getInstance();

    // Sanitização dos inputs
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha']; // Senha não deve ser sanitizada antes do hash
    $confirma_senha = $_POST['confirma_senha'];

    // Validações
    if (empty($nome) || empty($email) || empty($senha) || empty($confirma_senha)) {
        header("Location: cadastro.php?error=empty");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: cadastro.php?error=invalid_email");
        exit;
    }

    if (strlen($senha) < 8) {
        header("Location: cadastro.php?error=short_password");
        exit;
    }

    if ($senha !== $confirma_senha) {
        header("Location: cadastro.php?error=password_mismatch");
        exit;
    }

    if (!isset($_POST['termos']) || $_POST['termos'] != '1') {
        header("Location: cadastro.php?error=terms_required");
        exit;
    }

    // Verifica se o email já existe
    if ($db->buscarUsuarioPorEmail($email)) {
        header("Location: cadastro.php?error=email_exists");
        exit;
    }

    // Tenta inserir o novo usuário
    if ($db->inserirUsuario($nome, $email, $senha)) {
        // Sucesso no cadastro
        header("Location: login.php?success=1");
        exit;
    } else {
        // Erro na inserção
        header("Location: cadastro.php?error=db_error");
        exit;
    }
}
?>
