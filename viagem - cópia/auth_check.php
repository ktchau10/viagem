<?php
function verificarAutenticacao() {
    session_start();
    
    // Verifica se existe um usuário logado
    if (!isset($_SESSION['usuario_id'])) {
        // Armazena a URL atual para redirecionamento após o login
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        
        // Redireciona para a página de login
        header('Location: login.php');
        exit;
    }

    // Renova o ID da sessão periodicamente para prevenir session fixation
    if (!isset($_SESSION['last_session_refresh']) || time() - $_SESSION['last_session_refresh'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_session_refresh'] = time();
    }

    // Define headers de segurança
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    
    return array(
        'usuario_id' => $_SESSION['usuario_id'],
        'usuario_nome' => $_SESSION['usuario_nome'],
        'usuario_email' => $_SESSION['usuario_email']
    );
}

// Função para fazer logout
function logout() {
    session_start();
    
    // Destrói todas as variáveis de sessão
    $_SESSION = array();
    
    // Destrói o cookie da sessão
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destrói a sessão
    session_destroy();
    
    // Redireciona para a página de login
    header('Location: login.php');
    exit;
}

// Função para verificar CSRF token
function verificarCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Função para gerar CSRF token
function gerarCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>
