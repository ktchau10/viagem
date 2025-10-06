<?php
require_once 'auth_check.php';
require_once 'classes/GestaoViagens.php';

// Verifica autenticação
$usuario = verificarAutenticacao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Viagem - TravelPlanner</title>
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
    <header class="header fixed-header">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="dashboard.php">TravelPlanner</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['usuario_nome']) ?>&background=457B9D&color=fff" alt="Avatar" class="user-avatar">
                                <span><?= htmlspecialchars($usuario['usuario_nome']) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-collection me-2"></i>Minhas Viagens</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="process_logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="dashboard-content">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title mb-4">Nova Viagem</h2>
                            <div id="error-message" class="alert alert-danger d-none mb-3"></div>
                            <form id="formNovaViagem" method="POST" action="process_viagem.php" novalidate>
                                <input type="hidden" name="csrf_token" value="<?= gerarCSRFToken() ?>">
                                <div class="mb-3">
                                    <label for="titulo" class="form-label">Nome da Viagem</label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" required>
                                    <div class="invalid-feedback">Por favor, informe um nome para a viagem.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="descricao" class="form-label">Descrição</label>
                                    <textarea class="form-control" id="descricao" name="descricao" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="data_inicio" class="form-label">Data de Início</label>
                                        <input type="date" class="form-control" id="data_inicio" name="data_inicio" required>
                                        <div class="invalid-feedback">Por favor, selecione a data de início.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="data_fim" class="form-label">Data de Fim</label>
                                        <input type="date" class="form-control" id="data_fim" name="data_fim" required>
                                        <div class="invalid-feedback">Por favor, selecione a data de fim.</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="dashboard.php" class="btn btn-outline-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i>Criar Viagem
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('formNovaViagem');
        
        // Definir data mínima como hoje
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('data_inicio').min = today;
        document.getElementById('data_fim').min = today;
        
        // Atualizar data mínima de fim quando data início mudar
        document.getElementById('data_inicio').addEventListener('change', function() {
            document.getElementById('data_fim').min = this.value;
            if (document.getElementById('data_fim').value < this.value) {
                document.getElementById('data_fim').value = this.value;
            }
        });

        // Envio tradicional do formulário
        form.setAttribute('action', 'process_viagem.php');
        form.setAttribute('method', 'POST');
        form.addEventListener('submit', function(e) {
            // Validação do formulário
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }
            
            // O formulário será enviado normalmente para process_viagem.php
        });
    });
    </script>
</body>
</html>
