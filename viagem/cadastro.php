<?php
session_start();

// Mensagens de erro
$error_messages = [
    'empty' => 'Todos os campos são obrigatórios.',
    'invalid_email' => 'Email inválido.',
    'short_password' => 'A senha deve ter pelo menos 8 caracteres.',
    'password_mismatch' => 'As senhas não coincidem.',
    'terms_required' => 'Você precisa aceitar os termos de uso.',
    'email_exists' => 'Este email já está cadastrado.',
    'db_error' => 'Erro ao realizar cadastro. Tente novamente.'
];

// Mensagem de erro se houver
$error_message = '';
if (isset($_GET['error']) && isset($error_messages[$_GET['error']])) {
    $error_message = $error_messages[$_GET['error']];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - TravelPlanner</title>
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
                        <h2 class="text-center mb-4">Criar Conta</h2>
                        
                        <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" action="process_cadastro.php" id="formCadastro">
                            <div class="mb-3">
                                <label for="nome" class="form-label">Nome completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="nome" name="nome" required>
                                </div>
                            </div>
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
                                    <input type="password" class="form-control" id="senha" name="senha" required minlength="8">
                                    <button class="btn btn-outline-secondary" type="button" data-toggle-password="senha">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">A senha deve ter pelo menos 8 caracteres.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirma_senha" class="form-label">Confirmar senha</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" required>
                                    <button class="btn btn-outline-secondary" type="button" data-toggle-password="confirma_senha">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="termos" name="termos" value="1" required>
                                <label class="form-check-label" for="termos">
                                    Concordo com os <a href="#">termos de uso</a> e <a href="#">política de privacidade</a>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-person-plus me-2"></i>Criar conta
                            </button>
                            <p class="text-center mt-3">
                                Já tem uma conta? <a href="login.php">Fazer login</a>
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
        // Toggle password visibility for both password fields
        document.querySelectorAll('[data-toggle-password]').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-toggle-password');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });

        // Form validation
        document.getElementById('formCadastro').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha');
            const confirma_senha = document.getElementById('confirma_senha');
            
            if (senha.value !== confirma_senha.value) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                confirma_senha.focus();
            }
        });
    </script>
</body>
</html>
