<?php
session_start();

// Redireciona para o dashboard se já estiver logado
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPlanner - Planeje suas viagens</title>
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
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cadastro.php">
                                <i class="bi bi-person-plus me-1"></i>Cadastre-se
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main>
        <section class="hero py-5">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-12 col-md-6">
                        <h1 class="display-4">Planeje suas viagens de forma inteligente</h1>
                        <p class="lead">Organize roteiros, descubra lugares incríveis e guarde suas memórias em um só lugar.</p>
                        <a href="cadastro.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-right-circle me-2"></i>Comece Agora
                        </a>
                    </div>
                    <div class="col-12 col-md-6 mt-4 mt-md-0">
                        <img src="img/hero-image.jpg" alt="Planejamento de Viagem" class="img-fluid rounded shadow-lg">
                    </div>
                </div>
            </div>
        </section>

        <section class="features py-5 bg-light">
            <div class="container">
                <h2 class="text-center mb-5">Por que escolher o TravelPlanner?</h2>
                <div class="row g-4">
                    <div class="col-12 col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-calendar3 display-4 mb-3 text-primary"></i>
                                <h3 class="h5">Planejamento Simplificado</h3>
                                <p class="text-muted">Interface intuitiva para criar e organizar seus roteiros de viagem.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-people display-4 mb-3 text-primary"></i>
                                <h3 class="h5">Colaboração em Tempo Real</h3>
                                <p class="text-muted">Planeje viagens em grupo e compartilhe roteiros com amigos e família.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <i class="bi bi-geo-alt display-4 mb-3 text-primary"></i>
                                <h3 class="h5">Mapas Integrados</h3>
                                <p class="text-muted">Visualize seus destinos e calcule as melhores rotas entre pontos.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-8 text-center">
                        <h2 class="h3 mb-4">Pronto para começar a planejar sua próxima aventura?</h2>
                        <a href="cadastro.php" class="btn btn-primary btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Criar uma conta gratuita
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 text-center text-md-start">
                    <a href="index.php" class="text-decoration-none">
                        <h5 class="mb-0">TravelPlanner</h5>
                    </a>
                </div>
                <div class="col-md-4 my-3 my-md-0">
                    <ul class="list-inline text-center mb-0">
                        <li class="list-inline-item">
                            <a href="#" class="text-decoration-none">
                                <i class="bi bi-facebook"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="text-decoration-none">
                                <i class="bi bi-instagram"></i>
                            </a>
                        </li>
                        <li class="list-inline-item">
                            <a href="#" class="text-decoration-none">
                                <i class="bi bi-twitter"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4 text-center text-md-end">
                    <small class="text-muted">&copy; 2025 TravelPlanner. Todos os direitos reservados.</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
