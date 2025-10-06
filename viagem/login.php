<?php
session_start();

// Mensagens de erro
$error_messages = [
    'empty' => 'Por favor, preencha todos os campos.',
    'invalid_email' => 'Email inválido.',
    'user_not_found' => 'Email ou senha incorretos.',
    'wrong_password' => 'Email ou senha incorretos.',
    'inactive' => 'Conta inativa. Entre em contato com o suporte.'
];

// Mensagem de erro se houver
$error_message = '';
if (isset($_GET['error']) && isset($error_messages[$_GET['error']])) {
    $error_message = $error_messages[$_GET['error']];
}

// Mensagem de sucesso se vier do cadastro
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success_message = 'Cadastro realizado com sucesso! Por favor, faça login para continuar.';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TravelPlanner</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">TravelPlanner</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <div class="form-container">
                        <h2 class="text-center mb-4">Login</h2>
                        
                        <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="process_login.php">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="senha" class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="senha" name="senha" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="lembrar" name="lembrar">
                                <label class="form-check-label" for="lembrar">Lembrar de mim</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Entrar
                            </button>
                            <p class="text-center mt-3">
                                Não tem uma conta? <a href="cadastro.php">Cadastre-se</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; 2025 TravelPlanner. Todos os direitos reservados.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const senhaInput = document.getElementById('senha');
            const icon = this.querySelector('i');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                senhaInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>
</html>
